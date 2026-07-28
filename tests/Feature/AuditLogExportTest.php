<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_exporta_csv_com_cabecalho_e_content_type_corretos()
    {
        $admin = $this->criarAdmin();

        AuditLog::create([
            'user_id'    => $admin->id,
            'action'     => 'criou',
            'model_type' => 'Paciente',
            'model_id'   => 1,
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/audit-logs/exportar');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $conteudo = $response->streamedContent();
        $this->assertStringContainsString('Data/Hora,Ação,Usuário,Entidade', $conteudo);
        $this->assertStringContainsString('criou', $conteudo);
        $this->assertStringContainsString('Paciente', $conteudo);
    }

    public function test_exportacao_respeita_filtro_de_acao()
    {
        $admin = $this->criarAdmin();

        AuditLog::create(['user_id' => $admin->id, 'action' => 'criou',    'model_type' => 'Paciente',  'model_id' => 1, 'created_at' => now()]);
        AuditLog::create(['user_id' => $admin->id, 'action' => 'atualizou', 'model_type' => 'Profissional', 'model_id' => 2, 'created_at' => now()]);

        $conteudo = $this->actingAs($admin)
            ->get('/audit-logs/exportar?action=criou')
            ->streamedContent();

        $this->assertStringContainsString('Paciente', $conteudo);
        $this->assertStringNotContainsString('Profissional', $conteudo);
    }

    public function test_exportacao_respeita_filtro_de_data()
    {
        $admin = $this->criarAdmin();

        AuditLog::create(['user_id' => $admin->id, 'action' => 'criou', 'model_type' => 'Antigo', 'model_id' => 1, 'created_at' => now()->subDays(10)]);
        AuditLog::create(['user_id' => $admin->id, 'action' => 'criou', 'model_type' => 'Recente', 'model_id' => 2, 'created_at' => now()]);

        $conteudo = $this->actingAs($admin)
            ->get('/audit-logs/exportar?data_de=' . now()->subDay()->toDateString())
            ->streamedContent();

        $this->assertStringContainsString('Recente', $conteudo);
        $this->assertStringNotContainsString('Antigo', $conteudo);
    }

    public function test_nao_admin_nao_acessa_exportacao()
    {
        [$user] = $this->criarPacienteUser();

        // CheckNivel bloqueia com redirect()->back()->with('error'), não 403 (gotcha conhecido)
        $this->actingAs($user)
             ->get('/audit-logs/exportar')
             ->assertRedirect();
    }

    public function test_exportacao_sem_registros_ainda_gera_csv_valido_so_com_cabecalho()
    {
        $admin = $this->criarAdmin();

        $conteudo = $this->actingAs($admin)
            ->get('/audit-logs/exportar')
            ->streamedContent();

        $this->assertStringContainsString('Data/Hora,Ação,Usuário,Entidade', $conteudo);
    }
}
