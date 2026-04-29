<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConsultaRequest;
use App\Http\Requests\UpdateConsultaRequest;
use App\Models\Atendimento;
use App\Models\Consulta;
use App\Models\Exame;
use App\Models\Paciente;
use App\Models\Prescricao;
use App\Models\Profissional;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $query = Consulta::with('paciente', 'profissional')
            ->orderBy('data_hora', 'desc');

        if (Auth::user()->nivelAcesso() == 3) {
            $profissional = Auth::user()->profissional;
            if ($profissional) {
                $query->where('profissional_id', $profissional->id);
            }
        }

        $consultas = $query->paginate(15);

        return view('content.pages.listagem_consultas', ['consultas' => $consultas]);
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
        $pacientes          = Paciente::orderBy('nome')->get();
        $profissionais      = Profissional::orderBy('nome')->get();
        $atendimento        = null;
        $pacientePreSelecionado = null;
        $profissionalLogado = Auth::user()->profissional;

        if (request('atendimento_id')) {
            $atendimento = Atendimento::with('paciente', 'profissional')->find(request('atendimento_id'));
        } elseif (request('paciente_id')) {
            // Modo livre com paciente pré-selecionado: vem do botão "Iniciar Consulta" na listagem
            $pacientePreSelecionado = Paciente::find(request('paciente_id'));
        }

        return view('content.pages.cadastro-consulta', compact(
            'pacientes', 'profissionais', 'atendimento', 'profissionalLogado', 'pacientePreSelecionado'
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
        $consulta = DB::transaction(function () use ($request) {

            // Cria a consulta principal — registra quem criou (ST-08)
            $consulta = Consulta::create([
                'atendimento_id'  => $request->atendimento_id,
                'criado_por_id'   => Auth::id(),
                'profissional_id' => $request->profissional_id,
                'paciente_id'     => $request->paciente_id,
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
                        'data_solicitacao' => $e['data_solicitacao'] ?? null,
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

            return $consulta;
        });

        // intent=realize → detalhes da consulta
        // intent=schedule → mesmo destino por enquanto (ST-09 ainda não implementado);
        //   quando ST-09 existir, substituir pelo redirect para /cadastro-agendamento
        if ($request->input('intent') === 'schedule') {
            return redirect('/consultas/' . $consulta->id)
                ->with('success', 'Consulta registrada. O módulo de Agendamentos será implementado em breve (ST-09).');
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

        // Calcula se o usuário logado pode editar/deletar esta consulta
        $autorizado = $this->podeModificar($consulta, $consulta->atendimento ?? null);

        return view('content.pages.detalhes_consulta', compact('consulta', 'autorizado'));
    }

    /*
     * Exibe o formulário de edição de uma consulta existente.
     * ST-08: Bloqueia o acesso se o usuário não for o autor ou se o atendimento estiver fechado.
     */
    public function edit($id)
    {
        // Carrega paciente e profissional para o header read-only + exames/prescrições para a sidebar
        $consulta    = Consulta::with('paciente', 'profissional', 'atendimento', 'exames', 'prescricoes')->findOrFail($id);
        $atendimento = $consulta->atendimento ?? null;

        // Verifica autoria e status do atendimento antes de mostrar o formulário
        if (!$this->podeModificar($consulta, $atendimento)) {
            return redirect()->back()->with('error', 'Você não tem permissão para editar esta consulta.');
        }

        return view('content.pages.editar_consulta', compact('consulta'));
    }

    /*
     * Atualiza os campos da consulta no banco de dados.
     * A validação dos dados é feita pelo UpdateConsultaRequest antes de chegar aqui.
     * ST-08: Verifica autoria e status do atendimento antes de salvar.
     */
    public function update(UpdateConsultaRequest $request, $id)
    {
        $consulta    = Consulta::with('atendimento')->findOrFail($id);
        $atendimento = $consulta->atendimento ?? null;

        if (!$this->podeModificar($consulta, $atendimento)) {
            return redirect()->back()->with('error', 'Você não tem permissão para editar esta consulta.');
        }

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
        $consulta    = Consulta::with('atendimento')->findOrFail($id);
        $atendimento = $consulta->atendimento ?? null;

        if (!$this->podeModificar($consulta, $atendimento)) {
            return redirect()->back()->with('error', 'Você não tem permissão para excluir esta consulta.');
        }

        $consulta->delete();

        return redirect('/consultas')->with('success', 'Consulta removida com sucesso!');
    }

    // =========================================================
    // Helpers privados
    // =========================================================

    /*
     * Verifica se o usuário logado pode editar ou deletar um registro.
     *
     * Regras (ST-08):
     * 1. Admin (nivel <= 1) sempre pode — sem restrição
     * 2. Para qualquer outro nível: só o criador pode mexer no registro
     * 3. Se o registro está em um atendimento fechado, ninguém além do admin pode editar
     * 4. Se não há atendimento vinculado (registro legado), somente autoria importa
     *
     * @param  mixed       $registro     Model com campo criado_por_id
     * @param  Atendimento|null $atendimento  Atendimento vinculado (ou null para legados)
     * @return bool
     */
    private function podeModificar($registro, $atendimento = null): bool
    {
        $user = Auth::user();

        // Admin sempre pode — auditoria captura a ação de qualquer forma
        if ($user->nivelAcesso() <= 1) {
            return true;
        }

        // Para outros níveis: só o criador original pode mexer no registro
        if ($user->id !== $registro->criado_por_id) {
            return false;
        }

        // Sem atendimento vinculado (consulta legada criada antes do ST-06)?
        // Permitimos que o autor edite — não queremos bloquear registros antigos
        if (is_null($atendimento)) {
            return true;
        }

        // Com atendimento vinculado: só pode editar se o atendimento ainda estiver aberto
        return $atendimento->isAberto();
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
