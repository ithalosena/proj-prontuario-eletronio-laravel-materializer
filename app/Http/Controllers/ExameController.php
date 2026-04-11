<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExameRequest;
use App\Http\Requests\UpdateExameRequest;
use App\Models\Consulta;
use App\Models\Exame;

class ExameController extends Controller
{
    public function index()
    {
        $exames = Exame::with('consulta.paciente', 'consulta.profissional')->get();
        return view('content.pages.listagem_exames', ['exames' => $exames]);
    }

    public function create()
    {
        $consultas = Consulta::with('paciente', 'profissional')->orderBy('data_hora', 'desc')->get();
        return view('content.pages.cadastro-exame', ['consultas' => $consultas]);
    }

    public function store(StoreExameRequest $request)
    {
        Exame::create([
            'consulta_id'      => $request->consulta_id,
            'tipo'             => $request->tipo,
            'observacao'       => $request->observacao,
            'data_solicitacao' => $request->data_solicitacao,
        ]);

        return redirect('/exames')->with('success', 'Exame cadastrado com sucesso!');
    }

    public function edit($id)
    {
        $exame     = Exame::with('consulta')->findOrFail($id);
        $consultas = Consulta::with('paciente', 'profissional')->orderBy('data_hora', 'desc')->get();
        return view('content.pages.editar_exame', ['exame' => $exame, 'consultas' => $consultas]);
    }

    public function update(UpdateExameRequest $request, $id)
    {
        $exame = Exame::findOrFail($id);

        $exame->consulta_id      = $request->consulta_id;
        $exame->tipo             = $request->tipo;
        $exame->observacao       = $request->observacao;
        $exame->data_solicitacao = $request->data_solicitacao;
        $exame->data_resultado   = $request->data_resultado;
        $exame->resultado        = $request->resultado;
        $exame->save();

        return redirect('/exames')->with('success', 'Exame atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $exame = Exame::findOrFail($id);
        $exame->delete();

        return redirect('/exames')->with('success', 'Exame removido com sucesso!');
    }
}
