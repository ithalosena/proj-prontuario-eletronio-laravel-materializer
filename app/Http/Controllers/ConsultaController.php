<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConsultaRequest;
use App\Http\Requests\UpdateConsultaRequest;
use App\Models\Agendamento;
use App\Models\Atendimento;
use App\Models\Consulta;
use App\Models\Exame;
use App\Models\Paciente;
use App\Models\Prescricao;
use App\Models\Profissional;
use App\Models\TipoConsulta;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/*
 * Controller: ConsultaController
 *
 * Responsável pelo CRUD de consultas clínicas.
 * Cada consulta segue o formato SOAP e pode ter exames e prescrições vinculados.
 * A partir da v0.3.3, consultas são sempre criadas dentro de um atendimento,
 * mas o campo atendimento_id é nullable para compatibilidade com registros antigos.
 *
 * ST-08 adicionou controle de autoria: só o criador ou um admin (nivel <= 1)
 * pode editar/deletar. Além disso, registros em atendimentos fechados são bloqueados.
 *
 * Rotas associadas (definidas em routes/web.php):
 *   GET   /consultas               → index()
 *   GET   /cadastro-consulta       → create()
 *   POST  /cadastrar-consulta      → store()
 *   GET   /consultas/{id}          → show()
 *   GET   /editar-consulta/{id}    → edit()
 *   PUT   /atualizar-consulta/{id} → update()
 *   DELETE /deletar-consulta/{id}  → destroy()
 */
class ConsultaController extends Controller
{
    /*
     * Lista consultas em ordem decrescente de data.
     * Profissional de saúde (nivel 3) só vê as suas próprias consultas,
     * igual ao comportamento de AtendimentoController::index().
     */
    public function index()
    {
        $busca = request('busca');
        // UX-05 (v0.10.1): filtros por profissional e tipo de consulta (GET, preserváveis na URL)
        $filtroProfissional = request('profissional_id');
        $filtroTipo         = request('tipo');

        $query = Consulta::with('paciente', 'profissional')
            ->orderBy('data_hora', 'desc');

        // Profissional de saúde só enxerga as suas próprias consultas
        if (Auth::user()->nivelAcesso() == 3) {
            $profissional = Auth::user()->profissional;
            if ($profissional) {
                $query->where('profissional_id', $profissional->id);
            }
        }

        // Filtro de busca por nome do paciente
        if ($busca) {
            $query->whereHas('paciente', function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%");
            });
        }

        // Filtros por profissional e tipo (selects populados do banco)
        $query->when($filtroProfissional, fn($q) => $q->where('profissional_id', $filtroProfissional))
              ->when($filtroTipo,         fn($q) => $q->where('tipo', $filtroTipo));

        // withQueryString preserva busca + filtros nos links de paginação
        $consultas       = $query->paginate(15)->withQueryString();
        $totalConsultas  = $consultas->total(); // total pós-filtro de nível
        $minhasConsultas = null;

        // Mini-indicador de consultas do profissional logado
        if (Auth::user()->nivelAcesso() == 3) {
            $prof = Auth::user()->profissional;
            if ($prof) {
                $minhasConsultas = Consulta::where('profissional_id', $prof->id)->count();
            }
        }

        // Listas para os selects de filtro
        $profissionais = \App\Models\Profissional::orderBy('nome')->get();
        $tipos         = \App\Models\TipoConsulta::where('ativo', true)->orderBy('ordem')->get();

        return view('content.pages.listagem_consultas', compact(
            'consultas', 'busca', 'totalConsultas', 'minhasConsultas',
            'profissionais', 'tipos', 'filtroProfissional', 'filtroTipo'
        ));
    }

    /*
     * Exibe o formulário de nova consulta.
     *
     * Três modos de entrada:
     * - ?atendimento_id=X  → modo contextual: paciente/profissional travados (vêm do atendimento)
     * - ?paciente_id=X     → modo livre com paciente pré-selecionado (vem da listagem de pacientes)
     * - sem parâmetro      → modo livre completo (selects abertos)
     */
    public function create()
    {
        // ANALISE-01 (v0.10.1): Admin (nivel 1) é somente leitura — não registra consultas.
        if (Auth::user()->nivelAcesso() === 1) {
            abort(403);
        }

        // E1 (v0.11.1): a consulta nasce sempre dentro de um atendimento. Sem contexto
        // (atendimento aberto ou agendamento a realizar) não há como registrar consulta
        // solta — o "modo livre" foi removido para evitar consultas órfãs (bug-02).
        if (!request('atendimento_id') && !request('agendamento_id')) {
            return redirect('/pacientes')
                ->with('error', 'Para registrar uma consulta, abra um atendimento pelo perfil do paciente.');
        }

        $pacientes          = Paciente::orderBy('nome')->get();
        $profissionais      = Profissional::orderBy('nome')->get();
        $tiposConsulta      = TipoConsulta::ativo()->ordenado()->get();
        $atendimento        = null;
        $pacientePreSelecionado = null;
        $profissionalLogado = Auth::user()->profissional;

        // Origem via agendamento realizado (DT-MOD-01, Modelo A): modo contextual —
        // paciente/profissional travados pelo agendamento. NADA é criado aqui:
        // o atendimento só nasce quando a consulta for salva (store atômico).
        // O tipo NÃO é pré-preenchido pelo agendamento (agendamento.tipo é a
        // especialidade desde a v0.10.4 — o profissional escolhe o tipo).
        $agendamentoOrigem = null;
        if (request('agendamento_id')) {
            $agendamentoOrigem = Agendamento::with('paciente', 'profissional')
                ->find(request('agendamento_id'));

            if ($agendamentoOrigem) {
                // Mesmos guards do realizar(): S-02 (só o dono) + apenas confirmados
                $this->authorize('update', $agendamentoOrigem);

                if (!$agendamentoOrigem->isConfirmado()) {
                    return redirect('/agendamentos')
                        ->with('error', 'Apenas agendamentos confirmados podem ser realizados.');
                }
            }
        }

        if (request('atendimento_id')) {
            $atendimento = Atendimento::with('paciente', 'profissional')->find(request('atendimento_id'));
        } elseif (request('paciente_id')) {
            // Modo livre com paciente pré-selecionado: vem do botão "Iniciar Consulta" na listagem
            $pacientePreSelecionado = Paciente::find(request('paciente_id'));
        }

        return view('content.pages.cadastro-consulta', compact(
            'pacientes', 'profissionais', 'tiposConsulta', 'atendimento', 'profissionalLogado',
            'pacientePreSelecionado', 'agendamentoOrigem'
        ));
    }

    /*
     * Persiste a consulta, os exames e as prescrições em uma transação atômica.
     *
     * Usamos DB::transaction() para garantir que, se qualquer Insert falhar,
     * todos os outros sejam revertidos automaticamente (rollback).
     * Sem isso, poderíamos ter uma consulta salva mas sem os exames, por exemplo.
     *
     * Os exames e prescrições chegam como arrays (ex: exames[0][tipo], exames[1][tipo])
     * enviados pelo formulário com campos dinâmicos via JavaScript.
     * Filtramos linhas com 'tipo' vazio para ignorar rows adicionados mas não preenchidos.
     *
     * ST-08: 'criado_por_id' é preenchido aqui com o ID do usuário logado.
     * Esse campo é usado depois para verificar se alguém tem direito de editar.
     */
    public function store(StoreConsultaRequest $request)
    {
        // ANALISE-01 (v0.10.1): guard server-side — Admin não registra consultas
        if (Auth::user()->nivelAcesso() === 1) {
            abort(403);
        }

        // E1 (v0.11.1): guard server-side — bloqueia consulta órfã (sem atendimento nem
        // agendamento de origem). Toda consulta pertence a um atendimento.
        if (!$request->atendimento_id && !$request->agendamento_id) {
            return redirect('/pacientes')
                ->with('error', 'Para registrar uma consulta, abra um atendimento pelo perfil do paciente.');
        }

        /*
         * DT-MOD-01 (Modelo A): consulta vinda de um agendamento (sem atendimento
         * aberto) — os guards rodam ANTES da transação: S-02 (só o dono do
         * agendamento), apenas confirmados, e nunca um 2º atendimento para o
         * mesmo agendamento (1 agendamento = 1 atendimento).
         */
        $agendamento = null;
        if ($request->agendamento_id && !$request->atendimento_id) {
            $agendamento = Agendamento::findOrFail($request->agendamento_id);

            $this->authorize('update', $agendamento);

            if (!$agendamento->isConfirmado() || $agendamento->atendimento()->exists()) {
                return redirect('/agendamentos')
                    ->with('error', 'Apenas agendamentos confirmados podem ser realizados.');
            }
        }

        $consulta = DB::transaction(function () use ($request, $agendamento) {

            $atendimentoId = $request->atendimento_id;

            /*
             * DT-MOD-01: o atendimento nasce AQUI, junto da 1ª consulta — nunca
             * antes. Se o profissional abandonar o formulário, nada é criado e o
             * agendamento segue 'confirmado' (nunca sobra atendimento vazio, por
             * isso o status nasce direto como 'aberto', sem estado 'agendado').
             * Paciente e profissional são herdados do agendamento (fonte da verdade).
             */
            if ($agendamento) {
                $atendimentoId = Atendimento::create([
                    'paciente_id'     => $agendamento->paciente_id,
                    'profissional_id' => $agendamento->profissional_id,
                    'agendamento_id'  => $agendamento->id,
                    'criado_por_id'   => Auth::id(),
                    'status'          => 'aberto',
                ])->id;
            }

            // Cria a consulta principal — registra quem criou (ST-08)
            $consulta = Consulta::create([
                'atendimento_id'  => $atendimentoId,
                'criado_por_id'   => Auth::id(),
                'profissional_id' => $agendamento->profissional_id ?? $request->profissional_id,
                'paciente_id'     => $agendamento->paciente_id ?? $request->paciente_id,
                'data_hora'       => $request->data_hora,
                'tipo'            => $request->tipo,
                'queixa'          => $request->queixa,
                'anamnese'        => $request->anamnese,
                'diagnostico'     => $request->diagnostico,
                'conduta'         => $request->conduta,
            ]);

            // Cria cada exame vinculado à consulta (ignora rows sem tipo preenchido)
            foreach ($request->exames ?? [] as $e) {
                if (!empty($e['tipo'])) {
                    Exame::create([
                        'consulta_id'      => $consulta->id,
                        'criado_por_id'    => Auth::id(), // ST-08: autoria do exame inline
                        'tipo'             => $e['tipo'],
                        // BUG-A01 (v0.11.1): exame inline herda a data da consulta — a coluna
                        // data_solicitacao é NOT NULL; antes gravava NULL e derrubava a request.
                        'data_solicitacao' => $consulta->data_hora,
                        'observacao'       => $e['observacao'] ?? null,
                    ]);
                }
            }

            // Cria cada prescrição vinculada à consulta (ignora rows sem medicamento)
            foreach ($request->prescricoes ?? [] as $p) {
                if (!empty($p['nome_medicamento'])) {
                    Prescricao::create([
                        'consulta_id'      => $consulta->id,
                        'criado_por_id'    => Auth::id(), // ST-08: autoria da prescrição inline
                        'nome_medicamento' => $p['nome_medicamento'],
                        'dosagem'          => $p['dosagem'] ?? null,
                        'frequencia'       => $p['frequencia'] ?? null,
                        'duracao'          => $p['duracao'] ?? null,
                        'observacao'       => $p['observacao'] ?? null,
                    ]);
                }
            }

            // ST-09: marca o agendamento como realizado DENTRO da transação —
            // ou grava tudo (atendimento + consulta + agenda) ou nada (rollback)
            if ($agendamento) {
                $agendamento->update([
                    'consulta_id' => $consulta->id,
                    'status'      => 'realizado',
                ]);
            }

            return $consulta;
        });

        // UX-07 fix (ST-09): intent=schedule agora redireciona para criar novo agendamento
        if ($request->input('intent') === 'schedule') {
            return redirect('/cadastro-agendamento?' . http_build_query([
                'paciente_id' => $consulta->paciente_id,
                'tipo'        => $consulta->tipo,
            ]))->with('success', 'Consulta registrada! Agende o retorno abaixo.');
        }

        // DT-MOD-01: fluxo agendado aterrissa nos detalhes do atendimento recém-criado
        if ($agendamento) {
            return redirect('/atendimentos/' . $consulta->atendimento_id)
                ->with('success', 'Consulta registrada e atendimento aberto!');
        }

        return redirect('/consultas/' . $consulta->id)->with('success', 'Consulta registrada com sucesso!');
    }

    /*
     * Exibe o prontuário completo de uma consulta com todos os dados SOAP,
     * exames e prescrições vinculados.
     * findOrFail() retorna 404 automaticamente se a consulta não existir.
     *
     * ST-08: Passamos $autorizado para a view controlar quais botões aparecem.
     * Os botões de editar/deletar da consulta só aparecem se $autorizado = true.
     * Para exames/prescrições, a view verifica autoria inline (cada item tem seu autor).
     */
    public function show($id)
    {
        $consulta = Consulta::with(
            'paciente',
            'profissional',
            'atendimento', // necessário para verificar se o atendimento está aberto (controle de edição)
            'exames',
            'prescricoes'
        )->findOrFail($id);

        // S-01: bloqueia IDOR — profissional só vê consultas onde é o responsável (ConsultaPolicy::view)
        $this->authorize('view', $consulta);

        $autorizado = Auth::user()->can('update', $consulta);

        // ST-12: seleciona view especializada pelo slug da especialidade do profissional.
        // view()->exists() garante degradação graciosa: especialidade sem view implementada
        // cai no padrão sem erro 500 (Convention Over Configuration).
        $especialidade = Str::slug(optional($consulta->profissional)->especialidade ?? '', '_');
        $viewEspec     = 'content.pages.detalhes_consulta_' . $especialidade;
        $view          = view()->exists($viewEspec) ? $viewEspec : 'content.pages.detalhes_consulta';

        return view($view, compact('consulta', 'autorizado'));
    }

    /*
     * Exibe o formulário de edição de uma consulta existente.
     * ST-08: Bloqueia o acesso se o usuário não for o autor ou se o atendimento estiver fechado.
     */
    public function edit($id)
    {
        // Carrega paciente e profissional para o header read-only + exames/prescrições para a sidebar
        $consulta = Consulta::with('paciente', 'profissional', 'atendimento', 'exames', 'prescricoes')->findOrFail($id);

        $this->authorize('update', $consulta);

        $tiposConsulta = TipoConsulta::ativo()->ordenado()->get();
        return view('content.pages.editar_consulta', compact('consulta', 'tiposConsulta'));
    }

    /*
     * Atualiza os campos da consulta no banco de dados.
     * A validação dos dados é feita pelo UpdateConsultaRequest antes de chegar aqui.
     * ST-08: Verifica autoria e status do atendimento antes de salvar.
     */
    public function update(UpdateConsultaRequest $request, $id)
    {
        $consulta = Consulta::with('atendimento')->findOrFail($id);

        $this->authorize('update', $consulta);

        $consulta->data_hora       = $request->data_hora;
        $consulta->tipo            = $request->tipo;
        $consulta->queixa          = $request->queixa;
        $consulta->anamnese        = $request->anamnese;
        $consulta->diagnostico     = $request->diagnostico;
        $consulta->conduta         = $request->conduta;
        $consulta->save();

        // Volta para o atendimento se a consulta tiver um; caso contrário, para os detalhes da consulta.
        $destino = $consulta->atendimento_id
            ? '/atendimentos/' . $consulta->atendimento_id
            : '/consultas/' . $id;
        return redirect($destino)->with('success', 'Consulta atualizada com sucesso!');
    }

    /*
     * Remove a consulta do banco (soft delete — o registro não é apagado de verdade,
     * apenas marcado com deleted_at, preservando o histórico para auditoria).
     * ST-08: Verifica autoria e status do atendimento antes de deletar.
     */
    public function destroy($id)
    {
        $consulta = Consulta::with('atendimento')->findOrFail($id);

        $this->authorize('delete', $consulta);

        $consulta->delete();

        return redirect('/consultas')->with('success', 'Consulta removida com sucesso!');
    }

    /*
     * Endpoint AJAX de autocomplete de consultas.
     * Retorna até 10 consultas cujo tipo ou queixa contenha o termo (?q=).
     * Útil para busca contextual em relatórios e filtros.
     */
    public function buscar(Request $request): JsonResponse
    {
        return response()->json(
            app(SearchService::class)->autocomplete(
                Consulta::class,
                $request->input('q', ''),
                ['tipo', 'queixa'],
                ['id', 'tipo', 'queixa', 'data_hora']
            )
        );
    }
}
