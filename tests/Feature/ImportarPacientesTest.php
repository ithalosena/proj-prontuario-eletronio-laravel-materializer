<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportarPacientesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // O serviço busca a Role real por slug — os helpers de teste criam roles fake
        // (slug "nivel-N"), então garantimos aqui a Role 'paciente' de verdade.
        Role::firstOrCreate(
            ['slug' => 'paciente'],
            ['nome' => 'Paciente', 'nivel' => 5, 'descricao' => '']
        );
    }

    private function csv(string $conteudo): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('pacientes.csv', $conteudo);
    }

    public function test_admin_acessa_formulario_de_importacao()
    {
        $admin = $this->criarAdmin();

        $this->actingAs($admin)
             ->get('/pacientes/importar')
             ->assertOk();
    }

    public function test_paciente_nao_acessa_importacao()
    {
        [$user] = $this->criarPacienteUser();

        // CheckNivel bloqueia com redirect()->back()->with('error'), não 403 (gotcha conhecido)
        $this->actingAs($user)
             ->get('/pacientes/importar')
             ->assertRedirect();
    }

    public function test_importa_pacientes_validos_do_csv()
    {
        $admin = $this->criarAdmin();

        $conteudo = "nome,matricula,curso,email,data_nascimento,sexo,documento\n"
            . "Maria Silva,2024001,ADS,maria.import@teste.com,2000-01-01,F,11122233344\n"
            . "João Souza,2024002,ADS,joao.import@teste.com,1999-05-10,M,55566677788\n";

        $this->actingAs($admin)
             ->post('/pacientes/importar', ['arquivo' => $this->csv($conteudo)])
             ->assertOk();

        $this->assertDatabaseHas('pacientes', ['matricula' => '2024001', 'nome' => 'Maria Silva']);
        $this->assertDatabaseHas('pacientes', ['matricula' => '2024002', 'nome' => 'João Souza']);
        $this->assertDatabaseHas('users', ['email' => 'maria.import@teste.com']);

        $user = User::where('email', 'maria.import@teste.com')->first();
        $this->assertTrue($user->hasRole('paciente'));
        $this->assertFalse((bool) $user->onboarding_completo);
    }

    public function test_linha_duplicada_e_ignorada_sem_abortar_as_demais()
    {
        $admin = $this->criarAdmin();
        [, $pacienteExistente] = $this->criarPacienteUser();
        $pacienteExistente->update(['matricula' => 'DUP001']);

        $conteudo = "nome,matricula,curso,email,data_nascimento,sexo,documento\n"
            . "Duplicado,DUP001,ADS,duplicado@teste.com,2000-01-01,F,99988877766\n"
            . "Valido Novo,2024099,ADS,valido.novo@teste.com,2000-01-01,M,11223344556\n";

        $this->actingAs($admin)
             ->post('/pacientes/importar', ['arquivo' => $this->csv($conteudo)]);

        $this->assertDatabaseMissing('users', ['email' => 'duplicado@teste.com']);
        $this->assertDatabaseHas('pacientes', ['matricula' => '2024099']);
    }

    public function test_linha_invalida_vira_erro_sem_abortar_as_demais()
    {
        $admin = $this->criarAdmin();

        $conteudo = "nome,matricula,curso,email,data_nascimento,sexo,documento\n"
            . ",2024050,ADS,semnome@teste.com,2000-01-01,F,44455566677\n" // nome vazio => erro
            . "Valido Ok,2024051,ADS,valido.ok@teste.com,2000-01-01,M,77788899911\n";

        $this->actingAs($admin)
             ->post('/pacientes/importar', ['arquivo' => $this->csv($conteudo)]);

        $this->assertDatabaseMissing('users', ['email' => 'semnome@teste.com']);
        $this->assertDatabaseHas('pacientes', ['matricula' => '2024051']);
    }

    public function test_cabecalho_sem_coluna_obrigatoria_nao_quebra_com_500()
    {
        $admin = $this->criarAdmin();

        // Sem a coluna 'documento' (obrigatória: NOT NULL + UNIQUE no banco)
        $conteudo = "nome,matricula,curso,email,data_nascimento,sexo\n"
            . "Sem Documento,2024060,ADS,semdoc@teste.com,2000-01-01,F\n";

        $this->actingAs($admin)
             ->post('/pacientes/importar', ['arquivo' => $this->csv($conteudo)])
             ->assertRedirect('/pacientes/importar')
             ->assertSessionHas('error');

        $this->assertDatabaseMissing('users', ['email' => 'semdoc@teste.com']);
    }

    public function test_arquivo_nao_csv_e_rejeitado_pela_validacao()
    {
        $admin = $this->criarAdmin();
        $arquivo = UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf');

        $this->actingAs($admin)
             ->post('/pacientes/importar', ['arquivo' => $arquivo])
             ->assertSessionHasErrors('arquivo');
    }

    // Prova de vida: o CSV de exemplo baixável na tela (public/exemplos/pacientes_exemplo.csv)
    // precisa importar limpo — se este teste quebrar, o arquivo de exemplo ficou desatualizado
    // em relação ao formato exigido pelo PacienteImportService.
    public function test_csv_de_exemplo_importa_limpo()
    {
        $admin = $this->criarAdmin();
        $caminho = public_path('exemplos/pacientes_exemplo.csv');
        $this->assertFileExists($caminho, 'CSV de exemplo não encontrado em public/exemplos/.');

        $arquivo = new UploadedFile($caminho, 'pacientes_exemplo.csv', 'text/csv', null, true);

        $this->actingAs($admin)
             ->post('/pacientes/importar', ['arquivo' => $arquivo])
             ->assertOk()
             ->assertViewHas('resultado', function ($resultado) {
                 return $resultado['criados'] === 3
                     && $resultado['ignorados'] === 0
                     && $resultado['erros'] === 0;
             });

        $this->assertDatabaseHas('pacientes', ['matricula' => '2024100', 'nome' => 'Maria Exemplo Silva']);
        $this->assertDatabaseHas('pacientes', ['matricula' => '2024101', 'nome' => 'João Exemplo Souza']);
        $this->assertDatabaseHas('pacientes', ['matricula' => '2024102', 'nome' => 'Ana Exemplo Costa']);
    }
}
