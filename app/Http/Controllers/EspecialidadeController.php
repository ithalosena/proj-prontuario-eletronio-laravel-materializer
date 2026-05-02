<?php

namespace App\Http\Controllers;

use App\Models\Especialidade;
use Illuminate\Http\Request;

// Gerencia o CRUD de especialidades de profissionais.
// Acesso restrito a coordenador e acima (nivel <= 2).
class EspecialidadeController extends Controller
{
    public function index()
    {
        $especialidades = Especialidade::ordenado()->get();
        return view('content.pages.especialidades', compact('especialidades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:100|unique:especialidades,nome',
        ], [
            'nome.required' => 'O nome é obrigatório.',
            'nome.unique'   => 'Já existe uma especialidade com este nome.',
        ]);

        Especialidade::create([
            'nome'  => $request->nome,
            'ativo' => true,
            'ordem' => Especialidade::max('ordem') + 1,
        ]);

        return redirect('/configuracoes/especialidades')->with('success', 'Especialidade adicionada com sucesso!');
    }

    public function update(Request $request, Especialidade $especialidade)
    {
        $request->validate([
            'nome' => 'required|string|max:100|unique:especialidades,nome,' . $especialidade->id,
        ], [
            'nome.required' => 'O nome é obrigatório.',
            'nome.unique'   => 'Já existe uma especialidade com este nome.',
        ]);

        $especialidade->update(['nome' => $request->nome]);

        return redirect('/configuracoes/especialidades')->with('success', 'Especialidade atualizada com sucesso!');
    }

    // Ativa ou inativa uma especialidade sem excluir do banco
    public function toggleAtivo(Especialidade $especialidade)
    {
        $especialidade->update(['ativo' => !$especialidade->ativo]);

        $msg = $especialidade->ativo ? 'Especialidade ativada.' : 'Especialidade inativada.';
        return redirect('/configuracoes/especialidades')->with('success', $msg);
    }
}
