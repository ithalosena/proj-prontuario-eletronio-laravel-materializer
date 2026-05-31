<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Models\Consulta;
use App\Models\Exame;
use App\Models\Prescricao;

/**
 * @deprecated Substituído por DashboardController (v0.9.1a).
 * Mantido como fallback para Coordenador (nivel 2) e Recepcionista (nivel 4)
 * enquanto seus dashboards contextuais não são implementados.
 * Remover após v0.9.1b (Coordenador + Recepcionista) e v0.9.7 (limpeza final).
 */
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
