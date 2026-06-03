<?php

namespace App\Providers;

use App\Models\Agendamento;
use App\Models\Consulta;
use App\Models\Exame;
use App\Models\Prescricao;
use App\Policies\AgendamentoPolicy;
use App\Policies\ConsultaPolicy;
use App\Policies\ExamePolicy;
use App\Policies\PrescricaoPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    // Mapeamento model → policy (DT-03 + v0.7.6: controle de autoria por recurso)
    protected $policies = [
        Agendamento::class => AgendamentoPolicy::class, // v0.7.6: corrige IDOR S-02
        Consulta::class    => ConsultaPolicy::class,
        Exame::class       => ExamePolicy::class,
        Prescricao::class  => PrescricaoPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        //
    }
}
