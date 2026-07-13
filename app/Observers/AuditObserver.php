<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditObserver
{
    public function created(Model $model): void
    {
        AuditLog::registrar(
            action: 'created',
            userId: Auth::id(),
            modelType: class_basename($model),
            modelId: $model->id,
            newValues: $this->filtrar($model->toArray()),
        );
    }

    public function updated(Model $model): void
    {
        $dirty = $model->getDirty();

        if (empty($dirty)) {
            return;
        }

        $old = collect($model->getOriginal())->only(array_keys($dirty))->toArray();

        AuditLog::registrar(
            action: 'updated',
            userId: Auth::id(),
            modelType: class_basename($model),
            modelId: $model->id,
            oldValues: $this->filtrar($old),
            newValues: $this->filtrar($dirty),
        );
    }

    public function deleted(Model $model): void
    {
        AuditLog::registrar(
            action: 'deleted',
            userId: Auth::id(),
            modelType: class_basename($model),
            modelId: $model->id,
            oldValues: $this->filtrar($model->toArray()),
        );
    }

    // Remove credenciais, timestamps e dados sensíveis de saúde (LGPD Art. 46 / L-03)
    private function filtrar(array $data): array
    {
        return collect($data)->except([
            'password', 'remember_token', 'updated_at', 'deleted_at',
            // Dados sensíveis de saúde da CONSULTA — não devem ser duplicados em audit_logs
            'queixa', 'anamnese', 'diagnostico', 'conduta',
            // SF-01 (v0.10.6): dados clínicos autorreferidos do PACIENTE (ST-15) — LGPD Art. 11 + 46.
            // Sem isso, cada onboarding/edição gravava esses campos em texto claro no audit_logs.
            'tipo_sanguineo', 'peso_kg', 'altura_cm',
            'alergias', 'medicamentos_uso_continuo', 'condicoes_cronicas', 'cirurgias_previas',
            'tabagismo', 'etilismo', 'atividade_fisica',
        ])->toArray();
    }
}
