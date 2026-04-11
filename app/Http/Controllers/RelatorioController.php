<?php

namespace App\Http\Controllers;

use App\Models\Exame;
use App\Models\Paciente;
use App\Models\Prescricao;

class RelatorioController extends Controller
{
    public function index()
    {
        $exames      = Exame::with('consulta.profissional')->get();
        $pacientes   = Paciente::all();
        $prescricoes = Prescricao::with('consulta')->get();

        return view('content.pages.relatorios', [
            'exames'      => $exames,
            'pacientes'   => $pacientes,
            'prescricoes' => $prescricoes,
        ]);
    }
}
