<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// Orquestrador principal do seeder de dados de teste.
// Estratégia: usar sempre com "php artisan migrate:fresh --seed" para banco limpo.
// Os sub-seeders respeitam a ordem de integridade referencial:
// roles → users/profissionais/pacientes → atendimentos → consultas/exames/prescrições
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EspecialidadesSeeder::class,     // 6 especialidades canônicas
            TiposConsultaSeeder::class,      // 7 tipos de consulta canônicos
            RolesSeeder::class,              // 6 papéis RBAC
            UsuariosBaseSeeder::class,       // 2 admins + 2 recepcionistas + 8 profissionais
            PacientesSeeder::class,          // 55 pacientes com grupos curados para testes
            AtendimentosSeeder::class,       // ~100 atendimentos em cenários específicos
            ConsultasSeeder::class,          // ~120 consultas + exames + prescrições
            DisponibilidadesSeeder::class,   // horários padrão dos 8 profissionais (ST-09)
        ]);

        $this->command->info('');
        $this->command->info('✓ Seed completo. Resumo no final de cada sub-seeder acima.');
        $this->command->info('  Credenciais: admin@prontuif.com / senha123');
        $this->command->info('  Recepcionista: recepcao@ifnmg.edu.br / senha123');
        $this->command->info('  Profissional: dr.silva@ifnmg.edu.br / senha123');
        $this->command->info('  Paciente:     maria.costa@aluno.ifnmg.edu.br / senha123');
    }
}
