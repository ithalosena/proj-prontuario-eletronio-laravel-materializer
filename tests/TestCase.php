<?php

namespace Tests;

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

    // Cria usuário com a role do nível informado
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

    // Admin (nivel 1) — acesso total
    protected function criarAdmin(): User
    {
        return $this->criarUsuarioComNivel(1);
    }

    // Profissional (nivel 3) — retorna [$user, $profissional]
    protected function criarProfissionalUser(): array
    {
        $user = $this->criarUsuarioComNivel(3);
        $profissional = Profissional::factory()->create(['user_id' => $user->id]);
        return [$user, $profissional];
    }

    // Recepcionista (nivel 4)
    protected function criarRecepcionista(): User
    {
        return $this->criarUsuarioComNivel(4);
    }

    // Paciente (nivel 5) — retorna [$user, $paciente]
    protected function criarPacienteUser(): array
    {
        $user = $this->criarUsuarioComNivel(5);
        $paciente = Paciente::factory()->create(['user_id' => $user->id]);
        return [$user, $paciente];
    }
}
