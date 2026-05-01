<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\Atendimento;
use App\Models\Consulta;
use App\Models\Exame;
use App\Models\Prescricao;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Observers\AuditObserver;

class AppServiceProvider extends ServiceProvider
{
  public function register(): void
  {
    //
  }

  public function boot(): void
  {
    Paginator::useBootstrapFive();

    Atendimento::observe(AuditObserver::class);
    Consulta::observe(AuditObserver::class);
    Paciente::observe(AuditObserver::class);
    Profissional::observe(AuditObserver::class);
    Exame::observe(AuditObserver::class);
    Prescricao::observe(AuditObserver::class);
  }
}
