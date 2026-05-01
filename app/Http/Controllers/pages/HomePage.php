<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Models\Consulta;
use App\Models\Exame;
use App\Models\Prescricao;

class HomePage extends Controller
{
  public function index()
  {
    return view('content.pages.pages-home', [
      'totalPacientes'    => Paciente::count(),
      'totalProfissionais' => Profissional::count(),
      'totalConsultas'    => Consulta::count(),
      'totalExames'       => Exame::count(),
      'totalPrescricoes'  => Prescricao::count(),
    ]);
  }
}
