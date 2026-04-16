<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Profissional;
use App\Models\Paciente;
use App\Models\Consulta;
use App\Models\Exame;
use App\Models\Prescricao;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // === ROLES ===
        $roles = [
            ['nome' => 'Super Administrador', 'slug' => 'superadmin', 'nivel' => 0, 'descricao' => 'Acesso total ao sistema'],
            ['nome' => 'Administrador', 'slug' => 'admin', 'nivel' => 1, 'descricao' => 'Gerenciamento de usuarios e cadastros'],
            ['nome' => 'Coordenador de Saude', 'slug' => 'coordenador_saude', 'nivel' => 2, 'descricao' => 'Visao gerencial do setor de saude'],
            ['nome' => 'Profissional de Saude', 'slug' => 'profissional_saude', 'nivel' => 3, 'descricao' => 'Atendimento clinico e registro de consultas'],
            ['nome' => 'Recepcionista', 'slug' => 'recepcionista', 'nivel' => 4, 'descricao' => 'Agendamento, triagem e cadastros basicos'],
            ['nome' => 'Paciente', 'slug' => 'paciente', 'nivel' => 5, 'descricao' => 'Visualizacao do proprio historico'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }

        $roleProfissional = Role::where('slug', 'profissional_saude')->first();
        $roleAdmin = Role::where('slug', 'admin')->first();
        $rolePaciente = Role::where('slug', 'paciente')->first();

        // === ADMIN ===
        $adminUser = User::create([
            'name' => 'Administrador',
            'email' => 'admin@prontuif.com',
            'password' => 'senha123',
        ]);
        $adminUser->roles()->attach($roleAdmin);

        // === PROFISSIONAIS ===
        $profData = [
            ['name' => 'Dr. Carlos Silva', 'email' => 'dr.silva@ifnmg.edu.br', 'especialidade' => 'Clinico Geral', 'registro' => 'CRM-MG 12345'],
            ['name' => 'Dra. Ana Oliveira', 'email' => 'dra.ana@ifnmg.edu.br', 'especialidade' => 'Odontologia', 'registro' => 'CRO-MG 67890'],
            ['name' => 'Dr. Pedro Santos', 'email' => 'dr.pedro@ifnmg.edu.br', 'especialidade' => 'Psicologia', 'registro' => 'CRP-04 11223'],
        ];

        $profissionais = [];
        foreach ($profData as $p) {
            $user = User::create([
                'name' => $p['name'],
                'email' => $p['email'],
                'password' => 'senha123',
            ]);
            $user->roles()->attach($roleProfissional);

            $profissionais[] = Profissional::create([
                'user_id' => $user->id,
                'nome' => $p['name'],
                'contato' => '(38) 9' . rand(1000, 9999) . '-' . rand(1000, 9999),
                'especialidade' => $p['especialidade'],
                'registro_profissional' => $p['registro'],
            ]);
        }

        // === PACIENTES ===
        $pacData = [
            ['name' => 'Maria Fernanda Costa', 'email' => 'maria.costa@aluno.ifnmg.edu.br', 'doc' => '123.456.789-00', 'nasc' => '2003-03-15', 'sexo' => 'F', 'mat' => '2023001', 'curso' => 'Analise e Desenvolvimento de Sistemas'],
            ['name' => 'Joao Victor Almeida', 'email' => 'joao.almeida@aluno.ifnmg.edu.br', 'doc' => '987.654.321-00', 'nasc' => '2001-08-22', 'sexo' => 'M', 'mat' => '2022015', 'curso' => 'Engenharia Civil'],
            ['name' => 'Lucas Henrique Souza', 'email' => 'lucas.souza@aluno.ifnmg.edu.br', 'doc' => '456.789.123-00', 'nasc' => '2004-11-07', 'sexo' => 'M', 'mat' => '2024003', 'curso' => 'Administracao'],
        ];

        $pacientes = [];
        foreach ($pacData as $p) {
            $user = User::create([
                'name' => $p['name'],
                'email' => $p['email'],
                'password' => 'senha123',
            ]);
            $user->roles()->attach($rolePaciente);

            $pacientes[] = Paciente::create([
                'user_id' => $user->id,
                'nome' => $p['name'],
                'contato' => '(38) 9' . rand(1000, 9999) . '-' . rand(1000, 9999),
                'documento' => $p['doc'],
                'data_nascimento' => $p['nasc'],
                'sexo' => $p['sexo'],
                'endereco' => 'Rua das Flores, ' . rand(100, 999) . ' - Montes Claros/MG',
                'matricula' => $p['mat'],
                'curso' => $p['curso'],
            ]);
        }

        // === CONSULTAS ===
        $consultas = [];

        $consultas[] = Consulta::create([
            'profissional_id' => $profissionais[0]->id,
            'paciente_id' => $pacientes[0]->id,
            'data_hora' => '2026-02-03 09:00:00',
            'tipo' => 'Clinico Geral',
            'queixa' => 'Dor de cabeca persistente ha 3 dias',
            'anamnese' => 'Paciente relata cefaleia frontal, sem febre. Nega uso de medicamentos.',
            'diagnostico' => 'Cefaleia tensional',
            'conduta' => 'Prescrito analgesico e repouso. Retorno em 7 dias se persistir.',
        ]);

        $consultas[] = Consulta::create([
            'profissional_id' => $profissionais[1]->id,
            'paciente_id' => $pacientes[1]->id,
            'data_hora' => '2026-02-04 14:30:00',
            'tipo' => 'Odontologia',
            'queixa' => 'Dor no dente molar inferior esquerdo',
            'anamnese' => 'Paciente relata dor ao mastigar. Ultima consulta odontologica ha 1 ano.',
            'diagnostico' => 'Carie profunda no dente 36',
            'conduta' => 'Realizada restauracao. Orientacao de higiene bucal.',
        ]);

        $consultas[] = Consulta::create([
            'profissional_id' => $profissionais[2]->id,
            'paciente_id' => $pacientes[0]->id,
            'data_hora' => '2026-02-05 10:00:00',
            'tipo' => 'Psicologia',
            'queixa' => 'Ansiedade e dificuldade de concentracao nos estudos',
            'anamnese' => 'Paciente relata episodios de ansiedade antes de provas. Sono irregular.',
            'diagnostico' => 'Ansiedade situacional relacionada a desempenho academico',
            'conduta' => 'Inicio de acompanhamento psicologico semanal. Tecnicas de respiracao.',
        ]);

        $consultas[] = Consulta::create([
            'profissional_id' => $profissionais[0]->id,
            'paciente_id' => $pacientes[2]->id,
            'data_hora' => '2026-02-06 08:30:00',
            'tipo' => 'Clinico Geral',
            'queixa' => 'Dor de garganta e febre baixa',
            'anamnese' => 'Paciente com odinofagia ha 2 dias, febre de 37.8C. Nega alergias.',
            'diagnostico' => 'Faringite viral',
            'conduta' => 'Prescrito antipiretico e anti-inflamatorio. Hidratacao abundante.',
        ]);

        $consultas[] = Consulta::create([
            'profissional_id' => $profissionais[1]->id,
            'paciente_id' => $pacientes[2]->id,
            'data_hora' => '2026-02-07 11:00:00',
            'tipo' => 'Odontologia',
            'queixa' => 'Revisao odontologica de rotina',
            'anamnese' => 'Paciente sem queixas. Ultima consulta ha 6 meses.',
            'diagnostico' => 'Saude bucal satisfatoria',
            'conduta' => 'Profilaxia realizada. Retorno em 6 meses.',
        ]);

        // === EXAMES ===
        Exame::create([
            'consulta_id' => $consultas[0]->id,
            'tipo' => 'Hemograma Completo',
            'observacao' => 'Solicitar para investigar cefaleia persistente',
            'data_solicitacao' => '2026-02-03',
            'data_resultado' => '2026-02-05',
            'resultado' => 'Hemograma dentro dos parametros normais. Sem alteracoes significativas.',
        ]);

        Exame::create([
            'consulta_id' => $consultas[3]->id,
            'tipo' => 'Teste Rapido Strep',
            'observacao' => 'Descartar faringite bacteriana',
            'data_solicitacao' => '2026-02-06',
            'data_resultado' => '2026-02-06',
            'resultado' => 'Negativo para Streptococcus do grupo A.',
        ]);

        Exame::create([
            'consulta_id' => $consultas[3]->id,
            'tipo' => 'Raio-X Torax',
            'observacao' => 'Avaliar vias aereas superiores',
            'data_solicitacao' => '2026-02-06',
        ]);

        // === PRESCRICOES ===
        Prescricao::create([
            'consulta_id' => $consultas[0]->id,
            'nome_medicamento' => 'Paracetamol 750mg',
            'dosagem' => '1 comprimido',
            'frequencia' => 'De 8 em 8 horas',
            'duracao' => '5 dias',
            'observacao' => 'Tomar preferencialmente apos refeicoes.',
        ]);

        Prescricao::create([
            'consulta_id' => $consultas[3]->id,
            'nome_medicamento' => 'Ibuprofeno 400mg',
            'dosagem' => '1 comprimido',
            'frequencia' => 'De 12 em 12 horas',
            'duracao' => '3 dias',
            'observacao' => 'Tomar com estomago cheio. Suspender se houver desconforto gastrico.',
        ]);

        Prescricao::create([
            'consulta_id' => $consultas[3]->id,
            'nome_medicamento' => 'Dipirona 500mg',
            'dosagem' => '1 comprimido',
            'frequencia' => 'De 6 em 6 horas se febre acima de 38C',
            'duracao' => '3 dias',
        ]);

        Prescricao::create([
            'consulta_id' => $consultas[1]->id,
            'nome_medicamento' => 'Amoxicilina 500mg',
            'dosagem' => '1 capsula',
            'frequencia' => 'De 8 em 8 horas',
            'duracao' => '7 dias',
            'observacao' => 'Uso profilatico pos-procedimento odontologico.',
        ]);
    }
}
