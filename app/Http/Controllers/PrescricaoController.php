<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrescricaoRequest;
use App\Http\Requests\UpdatePrescricaoRequest;
use App\Models\Consulta;
use App\Models\Prescricao;

class PrescricaoController extends Controller
{
    public function index()
    {
        $prescricoes = Prescricao::with('consulta.paciente', 'consulta.profissional')->paginate(15);
        return view('content.pages.listagem_prescricoes', ['prescricoes' => $prescricoes]);
    }

    public function create()
    {
        $consultas  = Consulta::with('paciente', 'profissional')->orderBy('data_hora', 'desc')->get();
        $consultaId = request()->query('consulta_id');
        return view('content.pages.cadastro-prescricao', compact('consultas', 'consultaId'));
    }

    public function store(StorePrescricaoRequest $request)
    {
        Prescricao::create([
            'consulta_id'      => $request->consulta_id,
            'nome_medicamento' => $request->nome_medicamento,
            'dosagem'          => $request->dosagem,
            'frequencia'       => $request->frequencia,
            'duracao'          => $request->duracao,
            'observacao'       => $request->observacao,
        ]);

        if ($request->consulta_id_origem) {
            return redirect('/consultas/' . $request->consulta_id_origem)->with('success', 'Prescrição cadastrada com sucesso!');
        }

        return redirect('/prescricoes')->with('success', 'Prescricao cadastrada com sucesso!');
    }

    public function edit($id)
    {
        $prescricao = Prescricao::with('consulta')->findOrFail($id);
        $consultas  = Consulta::with('paciente', 'profissional')->orderBy('data_hora', 'desc')->get();
        return view('content.pages.editar_prescricao', ['prescricao' => $prescricao, 'consultas' => $consultas]);
    }

    public function update(UpdatePrescricaoRequest $request, $id)
    {
        $prescricao = Prescricao::findOrFail($id);

        $prescricao->consulta_id      = $request->consulta_id;
        $prescricao->nome_medicamento = $request->nome_medicamento;
        $prescricao->dosagem          = $request->dosagem;
        $prescricao->frequencia       = $request->frequencia;
        $prescricao->duracao          = $request->duracao;
        $prescricao->observacao       = $request->observacao;
        $prescricao->save();

        return redirect('/prescricoes')->with('success', 'Prescricao atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $prescricao = Prescricao::findOrFail($id);
        $prescricao->delete();

        return redirect('/prescricoes')->with('success', 'Prescricao removida com sucesso!');
    }
}
