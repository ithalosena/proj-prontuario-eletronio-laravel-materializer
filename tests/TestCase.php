<?php

namespace Tests;

use App\Models\Consentimento;
use App\Http\Middleware\CheckConsentimento;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    // Garante que as tabelas de lookup (especialidades, tipos_consulta) existam antes de cada teste,
    // pois os FormRequests usam Rule::in() contra essas tabelas.
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\EspecialidadesSeeder::class);
        $this->seed(\Database\Seeders\TiposConsultaSeeder::class);
    }

    // Cria usuário com a role do nível informado — sem consentimento automático
    protected function criarUsuarioComNivel(int $nivel, array $attrs = []): User
    {
        $role = Role::firstOrCreate(
            ['nivel' => $nivel],
            ['nome' => "Nível $nivel", 'slug' => "nivel-$nivel", 'descricao' => '']
        );
        $user = User::factory()->create($attrs);
        $user->roles()->attach($role);
        return $user;
    }

    // Cria registro de consentimento para o usuário (usado internamente pelos helpers abaixo)
    protected function criarConsentimento(User $user, string $tipo): void
    {
        Consentimento::firstOrCreate(
            [
                'user_id'      => $user->id,
                'versao_termo' => CheckConsentimento::VERSAO_ATUAL,
                'tipo_termo'   => $tipo,
            ],
            [
                'ip_address' => '127.0.0.1',
                'user_agent' => 'phpunit',
            ]
        );
    }

    // Admin (nivel 1) — com aceite de operador por padrão (v0.8.2: middleware exige para todos)
    protected function criarAdmin(bool $withConsent = true): User
    {
        $user = $this->criarUsuarioComNivel(1);
        if ($withConsent) {
            $this->criarConsentimento($user, 'operador');
        }
        return $user;
    }

    // Profissional (nivel 3) — retorna [$user, $profissional]; com aceite de operador por padrão
    protected function criarProfissionalUser(bool $withConsent = true): array
    {
        $user = $this->criarUsuarioComNivel(3);
        $profissional = Profissional::factory()->create(['user_id' => $user->id]);
        if ($withConsent) {
            $this->criarConsentimento($user, 'operador');
        }
        return [$user, $profissional];
    }

    // Recepcionista (nivel 4) — com aceite de operador por padrão
    protected function criarRecepcionista(bool $withConsent = true): User
    {
        $user = $this->criarUsuarioComNivel(4);
        if ($withConsent) {
            $this->criarConsentimento($user, 'operador');
        }
        return $user;
    }

    // Paciente (nivel 5) — retorna [$user, $paciente]; com aceite de titular por padrão
    protected function criarPacienteUser(bool $withConsent = true): array
    {
        $user = $this->criarUsuarioComNivel(5);
        $paciente = Paciente::factory()->create(['user_id' => $user->id]);
        if ($withConsent) {
            $this->criarConsentimento($user, 'titular');
        }
        return [$user, $paciente];
    }
}
