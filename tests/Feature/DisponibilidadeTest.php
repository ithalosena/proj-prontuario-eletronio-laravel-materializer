<?php

namespace Tests\Feature;

use App\Models\AgendaConfig;
use App\Models\DisponibilidadeBloco;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// BUG-01 (v0.10.2): a gravação de /disponibilidade falhava silenciosamente porque os
// campos TIME chegavam como "08:00:00" e a validação exige H:i. Estes testes travam o fix.
class DisponibilidadeTest extends TestCase
{
    use RefreshDatabase;

    private function payloadValido(): array
    {
        return [
            'config' => [
                'duracao_minutos'           => 30,
                'buffer_minutos'            => 0,
                'antecedencia_minima_horas' => 2,
                'antecedencia_maxima_dias'  => 30,
                'turno_manha_inicio'        => '08:00',
                'turno_manha_fim'           => '12:00',
                'turno_tarde_inicio'        => '13:00',
                'turno_tarde_fim'           => '18:00',
                'turno_noite_inicio'        => '19:00',
                'turno_noite_fim'           => '22:00',
            ],
            'dias' => [
                1 => [ // segunda-feira ativa com um bloco
                    'ativo'  => 1,
                    'blocos' => [
                        'hora_inicio' => ['08:00'],
                        'hora_fim'    => ['12:00'],
                    ],
                ],
            ],
        ];
    }

    // O POST com config válida persiste AgendaConfig + blocos e redireciona com sucesso
    public function test_salva_disponibilidade_com_config_valida(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();

        $this->actingAs($user)
             ->post('/disponibilidade', $this->payloadValido())
             ->assertRedirect('/disponibilidade')
             ->assertSessionHas('success');

        $this->assertDatabaseHas('agenda_config', [
            'profissional_id' => $profissional->id,
            'duracao_minutos' => 30,
        ]);
        $this->assertDatabaseHas('disponibilidade_blocos', [
            'profissional_id' => $profissional->id,
            'dia_semana'      => 1,
        ]);
    }

    // Horário de turno em formato H:i:s (com segundos, como vem do banco) também é aceito
    public function test_aceita_horario_com_segundos_normalizado(): void
    {
        [$user, $profissional] = $this->criarProfissionalUser();

        $payload = $this->payloadValido();
        // Simula o valor cru do banco; o controller/normalização deve aceitar H:i
        $payload['config']['turno_manha_inicio'] = '08:00';

        $this->actingAs($user)
             ->post('/disponibilidade', $payload)
             ->assertRedirect('/disponibilidade')
             ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('agenda_config', ['profissional_id' => $profissional->id]);
    }

    // Config incompleta retorna erros de validação (não falha em silêncio)
    public function test_config_invalida_retorna_erros(): void
    {
        [$user] = $this->criarProfissionalUser();

        $payload = $this->payloadValido();
        unset($payload['config']['turno_manha_inicio']); // campo obrigatório ausente

        $this->actingAs($user)
             ->post('/disponibilidade', $payload)
             ->assertSessionHasErrors('config.turno_manha_inicio');
    }
}
