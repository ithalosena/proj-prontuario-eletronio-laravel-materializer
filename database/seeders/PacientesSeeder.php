<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;
use App\Models\Paciente;

// Cria 55 pacientes organizados em grupos para cobrir os cenários de busca e paginação.
//
// Grupos criados:
//   - Âncoras (3):           credenciais fixas dos roteiros de teste
//   - Homônimos João (2):    mesmo primeiro nome, sobrenomes distintos
//   - Homônimos Maria (2):   mesmo primeiro nome, sobrenomes distintos
//   - Homônimos Lucas (2):   mesmo primeiro nome, sobrenomes distintos
//   - Sobrenome Silva (2):   mesmo sobrenome, nomes distintos
//   - Sobrenome Santos (3):  mesmo sobrenome, nomes distintos
//   - Sobrenome Oliveira(2): mesmo sobrenome, nomes distintos
//   - Acentuados/compostos(5): nomes com acento e nomes longos
//   - Histórico longo (2):   7+ atendimentos — cenário B.10.7
//   - Mix aberto+fechado (3):cenário B.10.4
//   - Só abertos (5):        cenário de atendimentos abertos
//   - Só fechados (5):       cenário de atendimentos fechados
//   - Multi-especialidade(2):atendimentos em especialidades diferentes
//   - Sem atendimentos (5):  cenários A.9.6 e B.10.6
//   - Variados (12):         distribuição geral para paginação e busca
//
// CPFs: âncoras mantêm os CPFs originais (fictícios); demais usam cpfValido($n).
// Endereços: cidades do Baixo Jequitinhonha (região do IFNMG Almenara).
class PacientesSeeder extends Seeder
{
    public function run(): void
    {
        $rolePaciente = Role::where('slug', 'paciente')->firstOrFail();

        // Cada entrada representa um paciente; cpf=null indica geração automática via cpfValido()
        $pacientes = [

            // =====================================================================
            // ÂNCORAS — credenciais fixas documentadas nos roteiros de teste
            // =====================================================================
            ['name' => 'Maria Fernanda Costa',   'email' => 'maria.costa@aluno.ifnmg.edu.br',   'cpf' => '123.456.789-00', 'nasc' => '2003-03-15', 'sexo' => 'F', 'mat' => '2023001', 'curso' => 'Análise e Desenvolvimento de Sistemas', 'cidade' => 'Almenara'],
            ['name' => 'João Victor Almeida',     'email' => 'joao.almeida@aluno.ifnmg.edu.br',  'cpf' => '987.654.321-00', 'nasc' => '2001-08-22', 'sexo' => 'M', 'mat' => '2022015', 'curso' => 'Engenharia Civil',                         'cidade' => 'Jequitinhonha'],
            ['name' => 'Lucas Henrique Souza',    'email' => 'lucas.souza@aluno.ifnmg.edu.br',   'cpf' => '456.789.123-00', 'nasc' => '2004-11-07', 'sexo' => 'M', 'mat' => '2024003', 'curso' => 'Administração',                            'cidade' => 'Rubim'],

            // =====================================================================
            // HOMÔNIMOS — mesmo primeiro nome, sobrenomes diferentes
            // =====================================================================

            // Grupo João (âncora João Victor + 2 novos)
            ['name' => 'João Carlos Silva',       'email' => 'joao.csilva@aluno.ifnmg.edu.br',   'cpf' => null, 'nasc' => '2002-05-14', 'sexo' => 'M', 'mat' => '2021001', 'curso' => 'Técnico em Informática',    'cidade' => 'Almenara'],
            ['name' => 'João Pedro Pereira',      'email' => 'joao.ppereira@aluno.ifnmg.edu.br', 'cpf' => null, 'nasc' => '2003-09-30', 'sexo' => 'M', 'mat' => '2021002', 'curso' => 'Técnico em Agropecuária',   'cidade' => 'Mata Verde'],

            // Grupo Maria (âncora Maria Fernanda + 2 novas)
            ['name' => 'Maria das Graças Souza',  'email' => 'maria.gsouza@aluno.ifnmg.edu.br',  'cpf' => null, 'nasc' => '2000-12-01', 'sexo' => 'F', 'mat' => '2021003', 'curso' => 'Licenciatura em Pedagogia', 'cidade' => 'Almenara'],
            ['name' => 'Maria Oliveira',          'email' => 'maria.oliveira@aluno.ifnmg.edu.br','cpf' => null, 'nasc' => '2004-06-18', 'sexo' => 'F', 'mat' => '2021004', 'curso' => 'Administração',             'cidade' => 'Felisburgo'],

            // Grupo Lucas (âncora Lucas Henrique + 2 novos)
            ['name' => 'Lucas Fernando Lima',     'email' => 'lucas.flima@aluno.ifnmg.edu.br',   'cpf' => null, 'nasc' => '2002-03-25', 'sexo' => 'M', 'mat' => '2021005', 'curso' => 'Técnico em Mineração',     'cidade' => 'Bandeira'],
            ['name' => 'Lucas Rodrigues Martins', 'email' => 'lucas.rmartins@aluno.ifnmg.edu.br','cpf' => null, 'nasc' => '2003-07-11', 'sexo' => 'M', 'mat' => '2021006', 'curso' => 'Gestão Pública',           'cidade' => 'Jordânia'],

            // =====================================================================
            // SOBRENOMES IGUAIS — nomes distintos, mesmo sobrenome
            // =====================================================================

            // Grupo Silva (João Carlos Silva já está acima)
            ['name' => 'Mariana Silva',           'email' => 'mariana.silva@aluno.ifnmg.edu.br', 'cpf' => null, 'nasc' => '2001-02-17', 'sexo' => 'F', 'mat' => '2021007', 'curso' => 'Administração',             'cidade' => 'Almenara'],
            ['name' => 'Roberto Silva',           'email' => 'roberto.silva@aluno.ifnmg.edu.br', 'cpf' => null, 'nasc' => '1999-11-03', 'sexo' => 'M', 'mat' => '2021008', 'curso' => 'Engenharia Civil',         'cidade' => 'Jequitinhonha'],

            // Grupo Santos
            ['name' => 'Carla Santos',            'email' => 'carla.santos@aluno.ifnmg.edu.br',  'cpf' => null, 'nasc' => '2002-08-22', 'sexo' => 'F', 'mat' => '2021009', 'curso' => 'Técnico em Administração', 'cidade' => 'Almenara'],
            ['name' => 'Beatriz Santos',          'email' => 'beatriz.santos@aluno.ifnmg.edu.br','cpf' => null, 'nasc' => '2003-04-09', 'sexo' => 'F', 'mat' => '2021010', 'curso' => 'Análise e Desenvolvimento de Sistemas', 'cidade' => 'Mata Verde'],
            ['name' => 'Thiago Santos',           'email' => 'thiago.santos@aluno.ifnmg.edu.br', 'cpf' => null, 'nasc' => '2001-10-15', 'sexo' => 'M', 'mat' => '2021011', 'curso' => 'Técnico em Informática',   'cidade' => 'Rubim'],

            // Grupo Oliveira (Maria Oliveira já está acima)
            ['name' => 'Felipe Oliveira',         'email' => 'felipe.oliveira@aluno.ifnmg.edu.br','cpf' => null,'nasc' => '2000-05-28', 'sexo' => 'M', 'mat' => '2021012', 'curso' => 'Engenharia Civil',         'cidade' => 'Almenara'],
            ['name' => 'Ana Paula Oliveira',      'email' => 'ana.oliveira@aluno.ifnmg.edu.br',  'cpf' => null, 'nasc' => '2002-01-14', 'sexo' => 'F', 'mat' => '2021013', 'curso' => 'Licenciatura em Pedagogia', 'cidade' => 'Felisburgo'],

            // =====================================================================
            // ACENTUAÇÃO E NOMES COMPOSTOS — valida busca com acentos e truncamento
            // =====================================================================
            ['name' => 'André Luís Barbosa',                         'email' => 'andre.barbosa@aluno.ifnmg.edu.br',  'cpf' => null, 'nasc' => '2001-06-20', 'sexo' => 'M', 'mat' => '2021014', 'curso' => 'Técnico em Agropecuária',  'cidade' => 'Almenara'],
            ['name' => 'Mônica Aparecida Nunes',                     'email' => 'monica.nunes@aluno.ifnmg.edu.br',   'cpf' => null, 'nasc' => '2000-09-07', 'sexo' => 'F', 'mat' => '2021015', 'curso' => 'Gestão Pública',           'cidade' => 'Bandeira'],
            ['name' => 'Vitória Cristina Peixoto',                   'email' => 'vitoria.peixoto@aluno.ifnmg.edu.br','cpf' => null, 'nasc' => '2003-11-25', 'sexo' => 'F', 'mat' => '2022001', 'curso' => 'Técnico em Meio Ambiente',  'cidade' => 'Joaíma'],
            ['name' => 'Maria das Graças Aparecida do Nascimento',   'email' => 'm.nascimento@aluno.ifnmg.edu.br',   'cpf' => null, 'nasc' => '1999-07-13', 'sexo' => 'F', 'mat' => '2022002', 'curso' => 'Licenciatura em Pedagogia', 'cidade' => 'Almenara'],
            ['name' => 'Ágatha Ferreira Lima',                       'email' => 'agatha.lima@aluno.ifnmg.edu.br',   'cpf' => null, 'nasc' => '2004-02-08', 'sexo' => 'F', 'mat' => '2022003', 'curso' => 'Análise e Desenvolvimento de Sistemas', 'cidade' => 'Palmópolis'],

            // =====================================================================
            // HISTÓRICO LONGO — 7+ atendimentos para testar bloco B.10.7
            // (mini-card deve exibir apenas os 5 mais recentes)
            // =====================================================================
            ['name' => 'Fernanda Cristina Rocha', 'email' => 'fernanda.rocha@aluno.ifnmg.edu.br','cpf' => null, 'nasc' => '2001-04-16', 'sexo' => 'F', 'mat' => '2022004', 'curso' => 'Administração',             'cidade' => 'Almenara'],
            ['name' => 'Gabriel Henrique Neves',  'email' => 'gabriel.neves@aluno.ifnmg.edu.br', 'cpf' => null, 'nasc' => '2002-08-31', 'sexo' => 'M', 'mat' => '2022005', 'curso' => 'Técnico em Informática',   'cidade' => 'Jequitinhonha'],

            // =====================================================================
            // MIX ABERTO + FECHADO — 1 aberto + 2 fechados para testar bloco B.10.4
            // =====================================================================
            ['name' => 'Isabela Martins Costa',   'email' => 'isabela.costa@aluno.ifnmg.edu.br', 'cpf' => null, 'nasc' => '2003-01-22', 'sexo' => 'F', 'mat' => '2022006', 'curso' => 'Análise e Desenvolvimento de Sistemas', 'cidade' => 'Rubim'],
            ['name' => 'Mateus Alves Cunha',      'email' => 'mateus.cunha@aluno.ifnmg.edu.br',  'cpf' => null, 'nasc' => '2001-07-05', 'sexo' => 'M', 'mat' => '2022007', 'curso' => 'Engenharia Civil',         'cidade' => 'Almenara'],
            ['name' => 'Larissa Fernandes Gomes', 'email' => 'larissa.gomes@aluno.ifnmg.edu.br', 'cpf' => null, 'nasc' => '2002-12-19', 'sexo' => 'F', 'mat' => '2022008', 'curso' => 'Técnico em Administração', 'cidade' => 'Mata Verde'],

            // =====================================================================
            // SÓ ATENDIMENTOS ABERTOS — 1 atendimento aberto por paciente
            // =====================================================================
            ['name' => 'Rafael Torres Sousa',     'email' => 'rafael.sousa@aluno.ifnmg.edu.br',  'cpf' => null, 'nasc' => '2000-03-14', 'sexo' => 'M', 'mat' => '2022009', 'curso' => 'Técnico em Mineração',     'cidade' => 'Almenara'],
            ['name' => 'Camila Borges Freitas',   'email' => 'camila.freitas@aluno.ifnmg.edu.br','cpf' => null, 'nasc' => '2002-10-27', 'sexo' => 'F', 'mat' => '2022010', 'curso' => 'Gestão Pública',           'cidade' => 'Felisburgo'],
            ['name' => 'Henrique Melo Cardoso',   'email' => 'henrique.cardoso@aluno.ifnmg.edu.br','cpf'=>null,  'nasc' => '2003-05-09', 'sexo' => 'M', 'mat' => '2022011', 'curso' => 'Administração',             'cidade' => 'Almenara'],
            ['name' => 'Priscila Castro Moreira', 'email' => 'priscila.moreira@aluno.ifnmg.edu.br','cpf'=>null, 'nasc' => '2001-09-23', 'sexo' => 'F', 'mat' => '2022012', 'curso' => 'Técnico em Informática',   'cidade' => 'Bandeira'],
            ['name' => 'Daniel Ribeiro Lopes',    'email' => 'daniel.lopes@aluno.ifnmg.edu.br',  'cpf' => null, 'nasc' => '2004-03-07', 'sexo' => 'M', 'mat' => '2022013', 'curso' => 'Técnico em Agropecuária',  'cidade' => 'Jordânia'],

            // =====================================================================
            // SÓ ATENDIMENTOS FECHADOS — 2-3 fechados por paciente
            // =====================================================================
            ['name' => 'Juliana Carvalho Dias',   'email' => 'juliana.dias@aluno.ifnmg.edu.br',  'cpf' => null, 'nasc' => '2002-07-14', 'sexo' => 'F', 'mat' => '2022014', 'curso' => 'Licenciatura em Pedagogia', 'cidade' => 'Almenara'],
            ['name' => 'Eduardo Monteiro Pinto',  'email' => 'eduardo.pinto@aluno.ifnmg.edu.br', 'cpf' => null, 'nasc' => '2000-11-28', 'sexo' => 'M', 'mat' => '2023002', 'curso' => 'Engenharia Civil',         'cidade' => 'Rio do Prado'],
            ['name' => 'Aline Ferreira Medeiros', 'email' => 'aline.medeiros@aluno.ifnmg.edu.br','cpf' => null, 'nasc' => '2003-02-11', 'sexo' => 'F', 'mat' => '2023003', 'curso' => 'Administração',             'cidade' => 'Almenara'],
            ['name' => 'Bruno Macedo Guimarães',  'email' => 'bruno.guimaraes@aluno.ifnmg.edu.br','cpf'=>null,  'nasc' => '2001-06-04', 'sexo' => 'M', 'mat' => '2023004', 'curso' => 'Técnico em Meio Ambiente',  'cidade' => 'Jacinto'],
            ['name' => 'Tatiana Moura Abreu',     'email' => 'tatiana.abreu@aluno.ifnmg.edu.br', 'cpf' => null, 'nasc' => '2002-09-17', 'sexo' => 'F', 'mat' => '2023005', 'curso' => 'Técnico em Administração', 'cidade' => 'Salto da Divisa'],

            // =====================================================================
            // MULTI-ESPECIALIDADE — atendimentos com profissionais de especialidades distintas
            // Paulo: Clínico Geral + Psicologia; Sabrina: Odontologia + Nutrição
            // =====================================================================
            ['name' => 'Paulo Victor Fonseca',    'email' => 'paulo.fonseca@aluno.ifnmg.edu.br', 'cpf' => null, 'nasc' => '2001-01-30', 'sexo' => 'M', 'mat' => '2023006', 'curso' => 'Análise e Desenvolvimento de Sistemas', 'cidade' => 'Almenara'],
            ['name' => 'Sabrina Lima Corrêa',     'email' => 'sabrina.correa@aluno.ifnmg.edu.br','cpf' => null, 'nasc' => '2003-08-13', 'sexo' => 'F', 'mat' => '2023007', 'curso' => 'Técnico em Informática',   'cidade' => 'Almenara'],

            // =====================================================================
            // SEM ATENDIMENTOS — valida cenário "Sem atendimentos" (A.9.6, B.10.6)
            // =====================================================================
            ['name' => 'Gustavo Araújo Teixeira', 'email' => 'gustavo.teixeira@aluno.ifnmg.edu.br','cpf'=>null, 'nasc' => '2002-04-25', 'sexo' => 'M', 'mat' => '2023008', 'curso' => 'Técnico em Agropecuária',  'cidade' => 'Almenara'],
            ['name' => 'Natália Brito Magalhães', 'email' => 'natalia.magalhaes@aluno.ifnmg.edu.br','cpf'=>null,'nasc' => '2004-10-08', 'sexo' => 'F', 'mat' => '2023009', 'curso' => 'Gestão Pública',           'cidade' => 'Jequitinhonha'],
            ['name' => 'Felipe Rangel Andrade',   'email' => 'felipe.andrade@aluno.ifnmg.edu.br','cpf' => null, 'nasc' => '2001-12-21', 'sexo' => 'M', 'mat' => '2023010', 'curso' => 'Administração',            'cidade' => 'Rubim'],
            ['name' => 'Adriana Costa Mendes',    'email' => 'adriana.mendes@aluno.ifnmg.edu.br','cpf' => null, 'nasc' => '2000-07-06', 'sexo' => 'F', 'mat' => '2024001', 'curso' => 'Licenciatura em Pedagogia','cidade' => 'Mata Verde'],
            ['name' => 'Rodrigo Barbosa Vieira',  'email' => 'rodrigo.vieira@aluno.ifnmg.edu.br','cpf' => null, 'nasc' => '2003-03-19', 'sexo' => 'M', 'mat' => '2024002', 'curso' => 'Técnico em Mineração',     'cidade' => 'Felisburgo'],

            // =====================================================================
            // VARIADOS — distribuição geral para cobrir paginação (mínimo 4 páginas a 15/pág)
            // =====================================================================
            ['name' => 'Simone Almeida Batista',  'email' => 'simone.batista@aluno.ifnmg.edu.br','cpf' => null, 'nasc' => '2001-05-12', 'sexo' => 'F', 'mat' => '2024004', 'curso' => 'Análise e Desenvolvimento de Sistemas', 'cidade' => 'Almenara'],
            ['name' => 'Leonardo Figueiredo Cruz','email' => 'leonardo.cruz@aluno.ifnmg.edu.br', 'cpf' => null, 'nasc' => '2002-11-07', 'sexo' => 'M', 'mat' => '2024005', 'curso' => 'Engenharia Civil',         'cidade' => 'Bandeira'],
            ['name' => 'Patrícia Duarte Assis',   'email' => 'patricia.assis@aluno.ifnmg.edu.br','cpf' => null, 'nasc' => '2003-06-24', 'sexo' => 'F', 'mat' => '2024006', 'curso' => 'Técnico em Informática',   'cidade' => 'Jordânia'],
            ['name' => 'Marcelo Leite Souza',     'email' => 'marcelo.leite@aluno.ifnmg.edu.br', 'cpf' => null, 'nasc' => '2000-02-15', 'sexo' => 'M', 'mat' => '2024007', 'curso' => 'Técnico em Agropecuária',  'cidade' => 'Almenara'],
            ['name' => 'Vanessa Tavares Nogueira','email' => 'vanessa.nogueira@aluno.ifnmg.edu.br','cpf'=>null,  'nasc' => '2002-08-03', 'sexo' => 'F', 'mat' => '2024008', 'curso' => 'Gestão Pública',           'cidade' => 'Joaíma'],
            ['name' => 'Diego Campos Esteves',    'email' => 'diego.esteves@aluno.ifnmg.edu.br', 'cpf' => null, 'nasc' => '2004-01-28', 'sexo' => 'M', 'mat' => '2024009', 'curso' => 'Administração',             'cidade' => 'Almenara'],
            ['name' => 'Renata Siqueira Viana',   'email' => 'renata.viana@aluno.ifnmg.edu.br',  'cpf' => null, 'nasc' => '2001-10-14', 'sexo' => 'F', 'mat' => '2024010', 'curso' => 'Técnico em Meio Ambiente',  'cidade' => 'Palmópolis'],
            ['name' => 'Leandro Pires Rezende',   'email' => 'leandro.rezende@aluno.ifnmg.edu.br','cpf'=>null,  'nasc' => '2003-04-02', 'sexo' => 'M', 'mat' => '2024011', 'curso' => 'Técnico em Mineração',     'cidade' => 'Salto da Divisa'],
            ['name' => 'Claudia Torres Farias',   'email' => 'claudia.farias@aluno.ifnmg.edu.br','cpf' => null, 'nasc' => '2000-09-20', 'sexo' => 'F', 'mat' => '2024012', 'curso' => 'Técnico em Administração', 'cidade' => 'Almenara'],
            ['name' => 'Marcos Pereira Ramos',    'email' => 'marcos.ramos@aluno.ifnmg.edu.br',  'cpf' => null, 'nasc' => '2002-06-08', 'sexo' => 'M', 'mat' => '2025001', 'curso' => 'Análise e Desenvolvimento de Sistemas', 'cidade' => 'Almenara'],
            ['name' => 'Débora Nascimento Luz',   'email' => 'debora.luz@aluno.ifnmg.edu.br',    'cpf' => null, 'nasc' => '2004-12-31', 'sexo' => 'F', 'mat' => '2025002', 'curso' => 'Licenciatura em Pedagogia', 'cidade' => 'Jequitinhonha'],
            ['name' => 'Álvaro Queiroz Paes',     'email' => 'alvaro.paes@aluno.ifnmg.edu.br',   'cpf' => null, 'nasc' => '2001-03-16', 'sexo' => 'M', 'mat' => '2025003', 'curso' => 'Engenharia Civil',         'cidade' => 'Almenara'],
        ];

        // Índice para geração de CPFs válidos (índice 0 = 1º paciente sem CPF fixo)
        $cpfIdx = 0;

        foreach ($pacientes as $i => $p) {
            $cpf = $p['cpf'] ?? $this->cpfValido($cpfIdx++);

            DB::transaction(function () use ($p, $cpf, $i, $rolePaciente) {
                $user = User::firstOrCreate(
                    ['email' => $p['email']],
                    ['name' => $p['name'], 'password' => 'senha123']
                );
                $user->roles()->syncWithoutDetaching([$rolePaciente->id]);

                if (!$user->paciente()->exists()) {
                    Paciente::create([
                        'user_id'         => $user->id,
                        'nome'            => $p['name'],
                        'contato'         => $this->gerarTelefone($i),
                        'documento'       => $cpf,
                        'data_nascimento' => $p['nasc'],
                        'sexo'            => $p['sexo'],
                        'endereco'        => $this->gerarEndereco($p['cidade'], $i),
                        'matricula'       => $p['mat'],
                        'curso'           => $p['curso'],
                    ]);
                }
            });
        }

        $this->command->info('PacientesSeeder: 55 pacientes criados/verificados.');
    }

    // Gera CPF válido pelo algoritmo oficial a partir de um índice reprodutível.
    // O índice garante unicidade — índices distintos sempre geram CPFs distintos.
    private function cpfValido(int $n): string
    {
        // Base numérica derivada do índice: afastada o suficiente do zero para evitar padrões ruins
        $num  = ($n + 100) * 98761 + 100000000;
        $base = substr((string) $num, -9); // 9 dígitos finais
        $d    = array_map('intval', str_split($base));

        // 1º dígito verificador
        $soma = 0;
        for ($i = 0; $i < 9; $i++) $soma += $d[$i] * (10 - $i);
        $r    = $soma % 11;
        $d[9] = $r < 2 ? 0 : 11 - $r;

        // 2º dígito verificador
        $soma = 0;
        for ($i = 0; $i < 10; $i++) $soma += $d[$i] * (11 - $i);
        $r     = $soma % 11;
        $d[10] = $r < 2 ? 0 : 11 - $r;

        return "{$d[0]}{$d[1]}{$d[2]}.{$d[3]}{$d[4]}{$d[5]}.{$d[6]}{$d[7]}{$d[8]}-{$d[9]}{$d[10]}";
    }

    // Formata endereço com rua e número determinísticos + cidade do Baixo Jequitinhonha
    private function gerarEndereco(string $cidade, int $idx): string
    {
        $ruas = [
            'Rua Coronel Belarmino', 'Rua das Acácias',        'Av. Jequitinhonha',
            'Rua Sete de Setembro',  'Rua Padre Serafim',      'Rua José Honório',
            'Rua Barão do Rio Branco','Rua da Saudade',         'Av. Brasil',
            'Rua Santa Cruz',        'Rua São Francisco',      'Rua Tiradentes',
            'Rua Visconde do Rio Branco','Rua Prefeito Hugo Cota','Rua Lauro Müller',
        ];
        $rua    = $ruas[$idx % count($ruas)];
        $numero = 100 + ($idx * 17) % 900;
        return "{$rua}, {$numero} - {$cidade}/MG";
    }

    // Gera número de telefone celular no DDD 33 (Almenara/Vale do Jequitinhonha)
    private function gerarTelefone(int $idx): string
    {
        $p1 = 9000 + ($idx * 137) % 1000;
        $p2 = 1000 + ($idx * 73)  % 9000;
        return "(33) {$p1}-{$p2}";
    }
}
