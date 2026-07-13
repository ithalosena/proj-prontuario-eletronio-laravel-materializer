<?php

use App\Models\AuditLog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;

/**
 * SF-01 (v0.10.6) — Sanitização dos dados clínicos já acumulados em `audit_logs`.
 *
 * O `AuditObserver::filtrar()` passou a excluir os 10 campos clínicos autorreferidos do
 * paciente (ST-15). Esta migration limpa os registros ANTIGOS que já haviam gravado esses
 * campos em texto claro (LGPD Art. 11 + Art. 46), removendo-os de `old_values`/`new_values`.
 *
 * Idempotente: rodar de novo não acha mais nada para remover. Em base nova (sem histórico),
 * é um no-op.
 */
return new class extends Migration
{
    /** Campos clínicos do ST-15 que não devem persistir em audit_logs. */
    private array $campos = [
        'tipo_sanguineo', 'peso_kg', 'altura_cm',
        'alergias', 'medicamentos_uso_continuo', 'condicoes_cronicas', 'cirurgias_previas',
        'tabagismo', 'etilismo', 'atividade_fisica',
    ];

    public function up(): void
    {
        // Só registros de Paciente carregam esses campos (class_basename => 'Paciente').
        AuditLog::where('model_type', 'Paciente')->chunkById(200, function ($logs) {
            foreach ($logs as $log) {
                $mudou = false;

                foreach (['old_values', 'new_values'] as $col) {
                    $valores = $log->{$col};
                    if (is_array($valores) && count(Arr::only($valores, $this->campos)) > 0) {
                        $log->{$col} = Arr::except($valores, $this->campos);
                        $mudou = true;
                    }
                }

                if ($mudou) {
                    $log->save(); // AuditLog tem $timestamps=false → não altera created_at
                }
            }
        });
    }

    public function down(): void
    {
        // Irreversível por design: os dados clínicos removidos não devem ser restaurados
        // no log de auditoria (é justamente o vazamento que estamos corrigindo).
    }
};
