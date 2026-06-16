<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExameRequest;
use App\Http\Requests\UpdateExameRequest;
use App\Models\Atendimento;
use App\Models\Consulta;
use App\Models\Exame;
use Illuminate\Support\Facades\Auth;

/*
 * Controller: ExameController
 *
 * Responsável pelo CRUD de exames clínicos.
 * Exames são vinculados a uma consulta e indiretamente a um atendimento.
 *
 * ST-08 adicionou controle de autoria: só o criador ou um admin (nivel <= 1)
 * pode editar/deletar. Registros em atendimentos fechados também são bloqueados.
 *
 * Rotas associadas (definidas em routes/web.php):
 *   GET   /exames             → index()
 *   GET   /cadastro-exame     → create()
 *   POST  /cadastrar-exame    → store()
 *   GET   /editar-exame/{id}  → edit()
 *   PUT   /atualizar-exame/{id} → update()
 *   DELETE /deletar-exame/{id}  → destroy()
 */
class ExameController extends Controller
{
    /*
     * Lista todos os exames com eager loading de consulta → paciente e profissional.
     * Sem o with(), cada linha da tabela faria queries extras (problema N+1).
     */
    public function index()
    {
        $exames      = Exame::with('consulta.paciente', 'consulta.profissional')->paginate(15);
        $totalExames = Exame::count();
        return view('content.pages.listagem_exames', compact('exames', 'totalExames'));
    }

    /*
     * Exibe o formulário de novo exame.
     * Recebe 'consulta_id' via query string para pré-selecionar a consulta no select,
     * o que acontece quando o usuário clica em "Novo Exame" dentro de uma consulta.
     */
    public function create()
    {
        // UX-P09 (v0.10.2): escopo — nível 3 vê só as próprias; nunca de atendimento fechado
        $consultas  = Consulta::with('paciente', 'profissional')
            ->anexaveisPor(Auth::user())
            ->orderBy('data_hora', 'desc')->get();
        $consultaId = request()->query('consulta_id');
        return view('content.pages.cadastro-exame', compact('consultas', 'consultaId'));
    }

    /*
     * Persiste o exame no banco de dados.
     * ST-08: 'criado_por_id' é preenchido aqui com o ID do usuário logado.
     *
     * Se a request tiver 'consulta_id_origem', redireciona de volta para o prontuário
     * da consulta em vez da listagem geral — melhor experiência quando vem de uma consulta.
     */
    public function store(StoreExameRequest $request)
    {
        Exame::create([
            'consulta_id'      => $request->consulta_id,
            'criado_por_id'    => Auth::id(), // ST-08: registra o autor do exame
            'tipo'             => $request->tipo,
            'observacao'       => $request->observacao,
            'data_solicitacao' => $request->data_solicitacao,
        ]);

        if ($request->consulta_id_origem) {
            return redirect('/consultas/' . $request->consulta_id_origem)->with('success', 'Exame cadastrado com sucesso!');
        }

        return redirect('/exames')->with('success', 'Exame cadastrado com sucesso!');
    }

    /*
     * Exibe o formulário de edição de um exame existente.
     * ST-08: Bloqueia o acesso se o usuário não for o autor ou se o atendimento estiver fechado.
     *
     * Para chegar ao atendimento a partir do exame, navegamos pela cadeia:
     * exame → consulta → atendimento. Usamos eager loading para evitar queries extras.
     */
    public function edit($id)
    {
        $exame = Exame::with('consulta.atendimento')->findOrFail($id);

        $this->authorize('update', $exame);

        // UX-P09 (v0.10.2): mesmo escopo do create
        $consultas = Consulta::with('paciente', 'profissional')
            ->anexaveisPor(Auth::user())
            ->orderBy('data_hora', 'desc')->get();
        return view('content.pages.editar_exame', ['exame' => $exame, 'consultas' => $consultas]);
    }

    /*
     * Atualiza o exame no banco de dados.
     * ST-08: Verifica autoria e status do atendimento antes de salvar.
     */
    public function update(UpdateExameRequest $request, $id)
    {
        $exame = Exame::with('consulta.atendimento')->findOrFail($id);

        $this->authorize('update', $exame);

        $exame->consulta_id      = $request->consulta_id;
        $exame->tipo             = $request->tipo;
        $exame->observacao       = $request->observacao;
        $exame->data_solicitacao = $request->data_solicitacao;
        $exame->data_resultado   = $request->data_resultado;
        $exame->resultado        = $request->resultado;
        $exame->save();

        return redirect('/exames')->with('success', 'Exame atualizado com sucesso!');
    }

    /*
     * Remove o exame do banco (soft delete — registro preservado para auditoria).
     * ST-08: Verifica autoria e status do atendimento antes de deletar.
     */
    public function destroy($id)
    {
        $exame = Exame::with('consulta.atendimento')->findOrFail($id);

        $this->authorize('delete', $exame);

        $exame->delete();

        return redirect('/exames')->with('success', 'Exame removido com sucesso!');
    }

}
