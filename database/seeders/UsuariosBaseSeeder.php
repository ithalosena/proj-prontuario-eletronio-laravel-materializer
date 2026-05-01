<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;
use App\Models\Profissional;

// Cria usuários não-pacientes: 2 admins, 2 recepcionistas e 8 profissionais de saúde.
// Os profissionais âncora (Dr. Silva, Dra. Ana, Dr. Pedro) têm credenciais fixas documentadas
// nos roteiros de teste — não alterar emails nem senhas.
// syncWithoutDetaching evita duplicar pivot role_user se o seeder rodar novamente.
class UsuariosBaseSeeder extends Seeder
{
    public function run(): void
    {
        $roleAdmin = Role::where('slug', 'admin')->firstOrFail();
        $roleRecep = Role::where('slug', 'recepcionista')->firstOrFail();
        $roleProf  = Role::where('slug', 'profissional_saude')->firstOrFail();

        // === ADMINISTRADORES ===
        // Admin principal — credencial documentada nos roteiros de teste (A.1.5)
        $admin1 = User::firstOrCreate(
            ['email' => 'admin@prontuif.com'],
            ['name' => 'Administrador', 'password' => 'senha123']
        );
        $admin1->roles()->syncWithoutDetaching([$roleAdmin->id]);

        // Admin secundário — para testes de RBAC com múltiplos usuários admin
        $admin2 = User::firstOrCreate(
            ['email' => 'admin2@prontuif.com'],
            ['name' => 'Administrador 2', 'password' => 'senha123']
        );
        $admin2->roles()->syncWithoutDetaching([$roleAdmin->id]);

        // === RECEPCIONISTAS ===
        // Resolve a inconsistência I-04 do roteiro: teste 17.8 estava como SKIP por falta de credencial
        $recep1 = User::firstOrCreate(
            ['email' => 'recepcao@ifnmg.edu.br'],
            ['name' => 'Ana Recepcionista', 'password' => 'senha123']
        );
        $recep1->roles()->syncWithoutDetaching([$roleRecep->id]);

        $recep2 = User::firstOrCreate(
            ['email' => 'recepcao2@ifnmg.edu.br'],
            ['name' => 'Carlos Recepcionista', 'password' => 'senha123']
        );
        $recep2->roles()->syncWithoutDetaching([$roleRecep->id]);

        // === PROFISSIONAIS ===
        // Âncoras: credenciais fixas dos roteiros de teste
        // Extras: pelo menos 2 profissionais por especialidade para cobrir testes de seleção/filtragem
        $profissionaisData = [
            // Âncoras documentadas — não alterar
            ['name' => 'Dr. Carlos Silva',    'email' => 'dr.silva@ifnmg.edu.br',    'esp' => 'Clínico Geral',  'reg' => 'CRM-MG 12345'],
            ['name' => 'Dra. Ana Oliveira',   'email' => 'dra.ana@ifnmg.edu.br',     'esp' => 'Odontologia',    'reg' => 'CRO-MG 67890'],
            ['name' => 'Dr. Pedro Santos',    'email' => 'dr.pedro@ifnmg.edu.br',    'esp' => 'Psicologia',     'reg' => 'CRP-04 11223'],
            // 2º Clínico Geral — permite testar seleção de profissional por especialidade
            ['name' => 'Dr. Rafael Mendes',   'email' => 'dr.mendes@ifnmg.edu.br',   'esp' => 'Clínico Geral',  'reg' => 'CRM-MG 54321'],
            // 2ª Odontologia — para reforçar cobertura da especialidade
            ['name' => 'Dra. Paula Ramos',    'email' => 'dra.paula@ifnmg.edu.br',   'esp' => 'Odontologia',    'reg' => 'CRO-MG 09876'],
            // Outras especialidades — necessárias para ST-12 (view-selection por profissional)
            ['name' => 'Dra. Camila Ferreira','email' => 'dra.camila@ifnmg.edu.br',  'esp' => 'Nutrição',       'reg' => 'CRN-4 33445'],
            ['name' => 'Dr. Bruno Vieira',    'email' => 'dr.bruno@ifnmg.edu.br',    'esp' => 'Fisioterapia',   'reg' => 'CREFITO-4 55667'],
            ['name' => 'Dra. Juliana Matos',  'email' => 'dra.juliana@ifnmg.edu.br', 'esp' => 'Serviço Social', 'reg' => 'CRESS-MG 77889'],
        ];

        foreach ($profissionaisData as $p) {
            DB::transaction(function () use ($p, $roleProf) {
                $user = User::firstOrCreate(
                    ['email' => $p['email']],
                    ['name' => $p['name'], 'password' => 'senha123']
                );
                $user->roles()->syncWithoutDetaching([$roleProf->id]);

                // Cria o perfil de profissional apenas se ainda não existir para este usuário
                if (!$user->profissional()->exists()) {
                    Profissional::create([
                        'user_id'               => $user->id,
                        'nome'                  => $p['name'],
                        'contato'               => $this->gerarTelefone($user->id),
                        'especialidade'         => $p['esp'],
                        'registro_profissional' => $p['reg'],
                    ]);
                }
            });
        }

        $this->command->info('UsuariosBaseSeeder: 2 admins, 2 recepcionistas, 8 profissionais criados/verificados.');
    }

    // Gera número de telefone celular no DDD 33 (Vale do Jequitinhonha/MG) de forma reprodutível
    private function gerarTelefone(int $seed): string
    {
        $parte1 = 9000 + ($seed * 137) % 1000;
        $parte2 = 1000 + ($seed * 73)  % 9000;
        return "(33) {$parte1}-{$parte2}";
    }
}
