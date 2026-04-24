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
     * Lista todas as consultas em ordem decrescente de data.
     * Usa paginate(15) para não carregar todos os registros de uma vez,
     * o que seria problemático com muitas consultas no banco.
     */
    public function index()
    {
        $consultas = Consulta::with('paciente', 'profissional')
            ->orderBy('data_hora', 'desc')
            ->paginate(15);

        return view('content.pages.listagem_consultas', ['consultas' => $consultas]);
    }

    /*
     * Exibe o formulário de nova consulta.
     *
     * Se a query string contiver 'atendimento_id', carregamos o atendimento
     * para pré-preencher os campos de paciente e profissional automaticamente.
     * Isso acontece quando o usuário clica em "Nova Consulta" dentro de um atendimento.
     *
     * Também verificamos se o usuário logado tem perfil de profissional para travar
     * o campo de profissional — mesma lógica aplicada no AtendimentoController.
     */
    public function create()
    {
        $pacientes          = Paciente::orderBy('nome')->get();
        $profissionais      = Profissional::orderBy('nome')->get();
        $atendimento        = null;
        $profissionalLogado = null;

        // Carrega o atendimento se o ID foi passado na URL (?atendimento_id=X)
        if (request('atendimento_id')) {
            $atendimento = Atendimento::with('paciente', 'profissional')->find(request('atendimento_id'));
        }

        // null se o usuário não tiver um perfil de profissional vinculado
        $profissionalLogado = Auth::user()->profissional;

        return view('content.pages.cadastro-consulta', compact(
            'pacientes', 'profissionais', 'atendimento', 'profissionalLogado'
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
     */
    public function store(StoreConsultaRequest $request)
    {
        $consulta = DB::transaction(function () use ($request) {

            // Cria a consulta principal
            $consulta = Consulta::create([
                'atendimento_id'  => $request->atendimento_id,
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

        // Redireciona para os detalhes da consulta recém-criada para confirmar o registro
        return redirect('/consultas/' . $consulta->id)->with('success', 'Consulta registrada com sucesso!');
    }

    /*
     * Exibe o prontuário completo de uma consulta com todos os dados SOAP,
     * exames e prescrições vinculados.
     * findOrFail() retorna 404 automaticamente se a consulta não existir.
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

        return view('content.pages.detalhes_consulta', compact('consulta'));
    }

    /*
     * Exibe o formulário de edição de uma consulta existente.
     * Apenas consultas dentro de atendimentos abertos podem ser editadas
     * (a restrição de acesso é aplicada na view e deve ser implementada aqui também no ST-08).
     */
    public function edit($id)
    {
        $consulta      = Consulta::findOrFail($id);
        $pacientes     = Paciente::orderBy('nome')->get();
        $profissionais = Profissional::orderBy('nome')->get();

        return view('content.pages.editar_consulta', compact('consulta', 'pacientes', 'profissionais'));
    }

    /*
     * Atualiza os campos da consulta no banco de dados.
     * A validação dos dados é feita pelo UpdateConsultaRequest antes de chegar aqui.
     * Usamos atribuição campo a campo (em vez de update()) para ter controle
     * explícito sobre quais campos podem ser alterados na edição.
     */
    public function update(UpdateConsultaRequest $request, $id)
    {
        $consulta = Consulta::findOrFail($id);

        $consulta->profissional_id = $request->profissional_id;
        $consulta->paciente_id     = $request->paciente_id;
        $consulta->data_hora       = $request->data_hora;
        $consulta->tipo            = $request->tipo;
        $consulta->queixa          = $request->queixa;
        $consulta->anamnese        = $request->anamnese;
        $consulta->diagnostico     = $request->diagnostico;
        $consulta->conduta         = $request->conduta;
        $consulta->save();

        return redirect('/consultas')->with('success', 'Consulta atualizada com sucesso!');
    }

    /*
     * Remove a consulta do banco (soft delete — o registro não é apagado de verdade,
     * apenas marcado com deleted_at, preservando o histórico para auditoria).
     */
    public function destroy($id)
    {
        $consulta = Consulta::findOrFail($id);
        $consulta->delete();

        return redirect('/consultas')->with('success', 'Consulta removida com sucesso!');
    }
}
