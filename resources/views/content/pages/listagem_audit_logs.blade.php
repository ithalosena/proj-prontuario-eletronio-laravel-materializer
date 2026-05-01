@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Audit Logs')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Registro de Auditoria</h3>
        </div>
      </div>
    </div>
  </div>

  <div class="card mt-1">
    <div class="card-body">
      <div class="table-responsive text-nowrap">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th>Data / Hora</th>
              <th>Usuário</th>
              <th>Ação</th>
              <th>Entidade</th>
              <th>Detalhes</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse($logs as $log)
            <tr>
              <td><span class="fw-medium">{{ $log->created_at->format('d/m/Y H:i:s') }}</span></td>
              <td>{{ $log->user->name ?? '—' }}</td>
              <td>
                @php
                  $badgeMap = [
                    'login'   => 'bg-label-success',
                    'logout'  => 'bg-label-secondary',
                    'created' => 'bg-label-primary',
                    'updated' => 'bg-label-warning',
                    'deleted' => 'bg-label-danger',
                  ];
                  $badge = $badgeMap[$log->action] ?? 'bg-label-info';
                @endphp
                <span class="badge rounded-pill {{ $badge }}">{{ $log->action }}</span>
              </td>
              <td>
                @if($log->model_type)
                  {{ $log->model_type }}
                  @if($log->model_id)
                    <span class="text-muted small">#{{ $log->model_id }}</span>
                  @endif
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td>
                @if($log->old_values || $log->new_values)
                  <button type="button" class="btn btn-sm btn-outline-secondary"
                    data-bs-toggle="modal" data-bs-target="#log-{{ $log->id }}">
                    <i class="mdi mdi-information-outline me-1"></i>Ver
                  </button>

                  <div class="modal fade" id="log-{{ $log->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">
                            <i class="mdi mdi-shield-search me-2"></i>
                            {{ ucfirst($log->action) }} — {{ $log->model_type }} #{{ $log->model_id }}
                          </h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <p class="text-muted small mb-3">
                            <i class="mdi mdi-account-outline me-1"></i>{{ $log->user->name ?? '—' }}
                            &nbsp;·&nbsp;
                            <i class="mdi mdi-clock-outline me-1"></i>{{ $log->created_at->format('d/m/Y H:i:s') }}
                            &nbsp;·&nbsp;
                            <i class="mdi mdi-ip-outline me-1"></i>{{ $log->ip_address ?? '—' }}
                          </p>
                          <div class="row">
                            @if($log->old_values)
                            <div class="col-md-6 mb-3">
                              <p class="text-muted small fw-semibold mb-2">Valores anteriores</p>
                              <pre class="bg-light rounded p-3 small mb-0" style="white-space: pre-wrap; word-break: break-word;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            @endif
                            @if($log->new_values)
                            <div class="col-md-{{ $log->old_values ? '6' : '12' }} mb-3">
                              <p class="text-muted small fw-semibold mb-2">Valores novos</p>
                              <pre class="bg-light rounded p-3 small mb-0" style="white-space: pre-wrap; word-break: break-word;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            @endif
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                        </div>
                      </div>
                    </div>
                  </div>
                @else
                  <span class="text-muted small">—</span>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-4">Nenhum registro de auditoria encontrado.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">
        {{ $logs->links() }}
      </div>
    </div>
  </div>
</div>

@endsection
