<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConsultaRequest;
use App\Http\Requests\UpdateConsultaRequest;
use App\Models\Agendamento;
use App\Models\Atendimento;
use App\Models\Consulta;
use App\Models\Exame;
use App\Models\Prescricao;
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
     * E3b (v0.11.1, união): a tela standalone de nova consulta foi aposentada.
     * O formulário agora vive DENTRO da tela do atendimento (detalhes_atendimento),
     * então esta rota só redireciona para lá — links antigos continuam funcionando.
     * ?nova=1 abre o formulário; #nova-consulta rola a página até ele.
     */
    public function create()
    {
        // ANALISE-01 (v0.10.1): Admin (nivel 1) é somente leitura — não registra consultas.
        if (Auth::user()->nivelAcesso() === 1) {
            abort(403);
        }

        // E1/E3 (v0.11.1, container): a consulta nasce sempre dentro de um atendimento aberto.
        if (!request('atendimento_id')) {
            return redirect('/pacientes')
                ->with('error', 'Para registrar uma consulta, abra um atendimento pelo perfil do paciente.');
        }

        $atendimento = Atendimento::find(request('atendimento_id'));
        if (!$atendimento) {
            return redirect('/atendimentos')->with('error', 'Atendimento não encontrado.');
        }

        return redirect('/atendimentos/' . $atendimento->id . '?nova=1#nova-consulta');
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

        // E1/E3 (v0.11.1, container): consulta sempre dentro de um atendimento (fim da órfã).
        // No modelo container o atendimento já existe (o realizar/Iniciar Atendimento o criou);
        // aqui a consulta só é ANEXADA a ele.
        if (!$request->atendimento_id) {
            return redirect('/pacientes')
                ->with('error', 'Para registrar uma consulta, abra um atendimento pelo perfil do paciente.');
        }

        // Fonte da verdade para paciente/profissional é o atendimento, não o request.
        $atendimento = Atendimento::findOrFail($request->atendimento_id);

        // E3 (container): atendimento encerrado é imutável. A UI já esconde o form quando
        // fechado, mas o guard server-side impede um POST direto de anexar consulta a um
        // atendimento já encerrado (integridade — "após fechado, nada muda").
        if (!$atendimento->isAberto()) {
            return redirect('/atendimentos/' . $atendimento->id)
                ->with('error', 'Este atendimento está encerrado — não é possível registrar novas consultas.');
        }

        $consulta = DB::transaction(function () use ($request, $atendimento) {

            // Cria a consulta dentro do atendimento — registra quem criou (ST-08)
            $consulta = Consulta::create([
                'atendimento_id'  => $atendimento->id,
                'criado_por_id'   => Auth::id(),
                'profissional_id' => $atendimento->profissional_id,
                'paciente_id'     => $atendimento->paciente_id,
                'data_hora'       => $request->data_hora,
                'tipo'            => $request->tipo,
                'queixa'          => $request->queixa,
                'anamnese'        => $request->anamnese,
                'diagnostico'     => $request->diagnostico,
                'conduta'         => $request->conduta,
                'anotacoes'       => $request->anotacoes, // E3d: registro livre (outros perfis)
            ]);

            // Exames inline (ignora rows sem tipo). BUG-A01: data herda a data da consulta.
            foreach ($request->exames ?? [] as $e) {
                if (!empty($e['tipo'])) {
                    Exame::create([
                        'consulta_id'      => $consulta->id,
                        'criado_por_id'    => Auth::id(), // ST-08: autoria do exame inline
                        'tipo'             => $e['tipo'],
                        'data_solicitacao' => $consulta->data_hora,
                        'observacao'       => $e['observacao'] ?? null,
                    ]);
                }
            }

            // Prescrições inline (ignora rows sem medicamento)
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

            // E3 (container): se o atendimento veio de um agendamento, liga a 1ª consulta a ele
            // (AgendaLink — o "Ver consulta" em Meus Agendamentos). Só na primeira (whereNull).
            if ($atendimento->agendamento_id) {
                Agendamento::where('id', $atendimento->agendamento_id)
                    ->whereNull('consulta_id')
                    ->update(['consulta_id' => $consulta->id]);
            }

            return $consulta;
        });

        // UX-07 fix (ST-09): intent=schedule redireciona para agendar o retorno
        if ($request->input('intent') === 'schedule') {
            return redirect('/cadastro-agendamento?' . http_build_query([
                'paciente_id' => $consulta->paciente_id,
                'tipo'        => $consulta->tipo,
            ]))->with('success', 'Consulta registrada! Agende o retorno abaixo.');
        }

        // Container: a consulta sempre aterrissa de volta na tela do atendimento
        return redirect('/atendimentos/' . $atendimento->id)
            ->with('success', 'Consulta registrada com sucesso!');
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
        $consulta->anotacoes       = $request->anotacoes; // E3d: registro livre (outros perfis)
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
