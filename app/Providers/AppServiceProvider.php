<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\Agendamento;
use App\Models\Atendimento;
use App\Models\Consulta;
use App\Models\Exame;
use App\Models\Prescricao;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Observers\AuditObserver;
use App\View\Composers\NavbarComposer;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
  public function register(): void
  {
    //
  }

  public function boot(): void
  {
    Paginator::useBootstrapFive();

    View::composer([
        'layouts.sections.navbar.navbar',
        'layouts.contentNavbarLayout',
    ], NavbarComposer::class);

    Agendamento::observe(AuditObserver::class);
    Atendimento::observe(AuditObserver::class);
    Consulta::observe(AuditObserver::class);
    Paciente::observe(AuditObserver::class);
    Profissional::observe(AuditObserver::class);
    Exame::observe(AuditObserver::class);
    Prescricao::observe(AuditObserver::class);
  }
}
