<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrescricaoRequest;
use App\Http\Requests\UpdatePrescricaoRequest;
use App\Models\Atendimento;
use App\Models\Consulta;
use App\Models\Prescricao;
use Illuminate\Support\Facades\Auth;

/*
 * Controller: PrescricaoController
 *
 * Responsável pelo CRUD de prescrições médicas.
 * Prescrições são vinculadas a uma consulta e indiretamente a um atendimento.
 *
 * ST-08 adicionou controle de autoria: só o criador ou um admin (nivel <= 1)
 * pode editar/deletar. Registros em atendimentos fechados também são bloqueados.
 *
 * Rotas associadas (definidas em routes/web.php):
 *   GET   /prescricoes               → index()
 *   GET   /cadastro-prescricao       → create()
 *   POST  /cadastrar-prescricao      → store()
 *   GET   /editar-prescricao/{id}    → edit()
 *   PUT   /atualizar-prescricao/{id} → update()
 *   DELETE /deletar-prescricao/{id}  → destroy()
 */
class PrescricaoController extends Controller
{
    /*
     * Lista todas as prescrições com eager loading de consulta → paciente e profissional.
     * Sem o with(), cada linha faria queries extras (problema N+1).
     */
    public function index()
    {
        $prescricoes      = Prescricao::with('consulta.paciente', 'consulta.profissional')->paginate(15);
        $totalPrescricoes = Prescricao::count();
        return view('content.pages.listagem_prescricoes', compact('prescricoes', 'totalPrescricoes'));
    }

    /*
     * Exibe o formulário de nova prescrição.
     * Recebe 'consulta_id' via query string para pré-selecionar a consulta no select,
     * o que acontece quando o usuário clica em "Nova Prescrição" dentro de uma consulta.
     */
    public function create()
    {
        $consultas  = Consulta::with('paciente', 'profissional')->orderBy('data_hora', 'desc')->get();
        $consultaId = request()->query('consulta_id');
        return view('content.pages.cadastro-prescricao', compact('consultas', 'consultaId'));
    }

    /*
     * Persiste a prescrição no banco de dados.
     * ST-08: 'criado_por_id' é preenchido aqui com o ID do usuário logado.
     *
     * Se a request tiver 'consulta_id_origem', redireciona de volta para o prontuário
     * da consulta em vez da listagem geral.
     */
    public function store(StorePrescricaoRequest $request)
    {
        Prescricao::create([
            'consulta_id'      => $request->consulta_id,
            'criado_por_id'    => Auth::id(), // ST-08: registra o autor da prescrição
            'nome_medicamento' => $request->nome_medicamento,
            'dosagem'          => $request->dosagem,
            'frequencia'       => $request->frequencia,
            'duracao'          => $request->duracao,
            'observacao'       => $request->observacao,
        ]);

        if ($request->consulta_id_origem) {
            return redirect('/consultas/' . $request->consulta_id_origem)->with('success', 'Prescrição cadastrada com sucesso!');
        }

        return redirect('/prescricoes')->with('success', 'Prescrição cadastrada com sucesso!');
    }

    /*
     * Exibe o formulário de edição de uma prescrição existente.
     * ST-08: Bloqueia o acesso se o usuário não for o autor ou se o atendimento estiver fechado.
     *
     * Navegamos pela cadeia: prescricao → consulta → atendimento para verificar o status.
     */
    public function edit($id)
    {
        $prescricao  = Prescricao::with('consulta.atendimento')->findOrFail($id);
        $atendimento = $prescricao->consulta->atendimento ?? null;

        if (!$this->podeModificar($prescricao, $atendimento)) {
            return redirect()->back()->with('error', 'Você não tem permissão para editar esta prescrição.');
        }

        $consultas = Consulta::with('paciente', 'profissional')->orderBy('data_hora', 'desc')->get();
        return view('content.pages.editar_prescricao', ['prescricao' => $prescricao, 'consultas' => $consultas]);
    }

    /*
     * Atualiza a prescrição no banco de dados.
     * ST-08: Verifica autoria e status do atendimento antes de salvar.
     */
    public function update(UpdatePrescricaoRequest $request, $id)
    {
        $prescricao  = Prescricao::with('consulta.atendimento')->findOrFail($id);
        $atendimento = $prescricao->consulta->atendimento ?? null;

        if (!$this->podeModificar($prescricao, $atendimento)) {
            return redirect()->back()->with('error', 'Você não tem permissão para editar esta prescrição.');
        }

        $prescricao->consulta_id      = $request->consulta_id;
        $prescricao->nome_medicamento = $request->nome_medicamento;
        $prescricao->dosagem          = $request->dosagem;
        $prescricao->frequencia       = $request->frequencia;
        $prescricao->duracao          = $request->duracao;
        $prescricao->observacao       = $request->observacao;
        $prescricao->save();

        return redirect('/prescricoes')->with('success', 'Prescrição atualizada com sucesso!');
    }

    /*
     * Remove a prescrição do banco (soft delete — registro preservado para auditoria).
     * ST-08: Verifica autoria e status do atendimento antes de deletar.
     */
    public function destroy($id)
    {
        $prescricao  = Prescricao::with('consulta.atendimento')->findOrFail($id);
        $atendimento = $prescricao->consulta->atendimento ?? null;

        if (!$this->podeModificar($prescricao, $atendimento)) {
            return redirect()->back()->with('error', 'Você não tem permissão para excluir esta prescrição.');
        }

        $prescricao->delete();

        return redirect('/prescricoes')->with('success', 'Prescrição removida com sucesso!');
    }

    // =========================================================
    // Helpers privados
    // =========================================================

    /*
     * Verifica se o usuário logado pode editar ou deletar esta prescrição.
     *
     * Regras (ST-08):
     * 1. Admin (nivel <= 1) sempre pode — sem restrição
     * 2. Para outros níveis: só o criador original pode mexer
     * 3. Com atendimento fechado: bloqueado para todos exceto admin
     * 4. Sem atendimento (prescrição legada): somente autoria importa
     *
     * @param  Prescricao       $prescricao
     * @param  Atendimento|null $atendimento
     * @return bool
     */
    private function podeModificar($prescricao, $atendimento = null): bool
    {
        $user = Auth::user();

        // Admin sempre pode — auditoria captura a ação de qualquer forma
        if ($user->nivelAcesso() <= 1) {
            return true;
        }

        // Para outros níveis: somente o criador original pode mexer
        if ($user->id !== $prescricao->criado_por_id) {
            return false;
        }

        // Prescrição sem atendimento vinculado (legada): autor pode editar
        if (is_null($atendimento)) {
            return true;
        }

        // Com atendimento: só edita se ainda estiver aberto
        return $atendimento->isAberto();
    }
}
