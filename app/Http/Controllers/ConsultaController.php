<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConsultaRequest;
use App\Http\Requests\UpdateConsultaRequest;
use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Profissional;

class ConsultaController extends Controller
{
    public function index()
    {
        $consultas = Consulta::with('paciente', 'profissional')
            ->orderBy('data_hora', 'desc')
            ->get();

        return view('content.pages.listagem_consultas', ['consultas' => $consultas]);
    }

    public function create()
    {
        $pacientes     = Paciente::orderBy('nome')->get();
        $profissionais = Profissional::orderBy('nome')->get();

        return view('content.pages.cadastro-consulta', compact('pacientes', 'profissionais'));
    }

    public function store(StoreConsultaRequest $request)
    {
        Consulta::create([
            'profissional_id' => $request->profissional_id,
            'paciente_id'     => $request->paciente_id,
            'data_hora'       => $request->data_hora,
            'tipo'            => $request->tipo,
            'queixa'          => $request->queixa,
            'anamnese'        => $request->anamnese,
            'diagnostico'     => $request->diagnostico,
            'conduta'         => $request->conduta,
        ]);

        return redirect('/consultas')->with('success', 'Consulta registrada com sucesso!');
    }

    public function show($id)
    {
        $consulta = Consulta::with(
            'paciente',
            'profissional',
            'exames',
            'prescricoes'
        )->findOrFail($id);

        return view('content.pages.detalhes_consulta', compact('consulta'));
    }

    public function edit($id)
    {
        $consulta      = Consulta::findOrFail($id);
        $pacientes     = Paciente::orderBy('nome')->get();
        $profissionais = Profissional::orderBy('nome')->get();

        return view('content.pages.editar_consulta', compact('consulta', 'pacientes', 'profissionais'));
    }

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

    public function destroy($id)
    {
        $consulta = Consulta::findOrFail($id);
        $consulta->delete();

        return redirect('/consultas')->with('success', 'Consulta removida com sucesso!');
    }
}
