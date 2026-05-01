<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

// Popula os seis papéis do sistema RBAC.
// Usa firstOrCreate para garantir idempotência — pode rodar em banco semi-populado sem duplicar.
class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nome' => 'Super Administrador', 'slug' => 'superadmin',         'nivel' => 0, 'descricao' => 'Acesso total ao sistema'],
            ['nome' => 'Administrador',        'slug' => 'admin',              'nivel' => 1, 'descricao' => 'Gerenciamento de usuários e cadastros'],
            ['nome' => 'Coordenador de Saúde', 'slug' => 'coordenador_saude', 'nivel' => 2, 'descricao' => 'Visão gerencial do setor de saúde'],
            ['nome' => 'Profissional de Saúde','slug' => 'profissional_saude', 'nivel' => 3, 'descricao' => 'Atendimento clínico e registro de consultas'],
            ['nome' => 'Recepcionista',        'slug' => 'recepcionista',      'nivel' => 4, 'descricao' => 'Agendamento, triagem e cadastros básicos'],
            ['nome' => 'Paciente',             'slug' => 'paciente',           'nivel' => 5, 'descricao' => 'Visualização do próprio histórico'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }

        $this->command->info('RolesSeeder: 6 papéis criados/verificados.');
    }
}
