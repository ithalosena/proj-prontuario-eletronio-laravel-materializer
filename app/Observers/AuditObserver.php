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

    // Remove campos de timestamp e senha do log
    private function filtrar(array $data): array
    {
        return collect($data)->except([
            'password', 'remember_token', 'updated_at', 'deleted_at',
        ])->toArray();
    }
}
