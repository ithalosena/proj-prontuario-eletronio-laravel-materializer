<?php

namespace App\Http\Controllers;

use App\Models\TipoConsulta;
use Illuminate\Http\Request;

// Gerencia o CRUD de tipos de consulta.
// Acesso restrito a coordenador e acima (nivel <= 2).
class TipoConsultaController extends Controller
{
    public function index()
    {
        $tipos = TipoConsulta::ordenado()->get();
        return view('content.pages.tipos_consulta', compact('tipos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:100|unique:tipos_consulta,nome',
        ], [
            'nome.required' => 'O nome é obrigatório.',
            'nome.unique'   => 'Já existe um tipo de consulta com este nome.',
        ]);

        TipoConsulta::create([
            'nome'  => $request->nome,
            'ativo' => true,
            'ordem' => TipoConsulta::max('ordem') + 1,
        ]);

        return redirect('/configuracoes/tipos-consulta')->with('success', 'Tipo de consulta adicionado com sucesso!');
    }

    public function update(Request $request, TipoConsulta $tipoConsulta)
    {
        $request->validate([
            'nome' => 'required|string|max:100|unique:tipos_consulta,nome,' . $tipoConsulta->id,
        ], [
            'nome.required' => 'O nome é obrigatório.',
            'nome.unique'   => 'Já existe um tipo de consulta com este nome.',
        ]);

        $tipoConsulta->update(['nome' => $request->nome]);

        return redirect('/configuracoes/tipos-consulta')->with('success', 'Tipo de consulta atualizado com sucesso!');
    }

    // Ativa ou inativa um tipo de consulta sem excluir do banco
    public function toggleAtivo(TipoConsulta $tipoConsulta)
    {
        $tipoConsulta->update(['ativo' => !$tipoConsulta->ativo]);

        $msg = $tipoConsulta->ativo ? 'Tipo de consulta ativado.' : 'Tipo de consulta inativado.';
        return redirect('/configuracoes/tipos-consulta')->with('success', $msg);
    }
}
