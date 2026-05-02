@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Audit Logs')

{{-- Breadcrumb: Início > Auditoria --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',     'url' => '/'],
      ['label' => 'Auditoria',  'url' => null],
    ]
  ])
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================ --}}
  {{-- HEADER DA PÁGINA                                                  --}}
  {{-- ================================================================ --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h4 class="mb-0">Registro de Auditoria</h4>
      <p class="text-muted small mb-0 mt-1">Histórico de ações realizadas no sistema</p>
    </div>
  </div>

  {{-- Mini-indicador: total de registros --}}
  <div class="d-flex flex-wrap gap-2 mb-4">
    <span class="badge bg-label-primary fs-6 px-3 py-2">
      <i class="mdi mdi-shield-check-outline me-1"></i>
      {{ $totalLogs }} {{ $totalLogs == 1 ? 'evento registrado' : 'eventos registrados' }}
    </span>
  </div>

  <div class="card">
    <div class="card-body">

      {{-- Contador de resultados --}}
      @if($logs->total() > 0)
      <p class="text-muted small mb-3">
        Exibindo {{ $logs->firstItem() }}–{{ $logs->lastItem() }} de {{ $logs->total() }} evento(s)
      </p>
      @endif

      <div class="table-responsive">
        <table class="table table-hover align-middle">
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
              <td>
                <span class="fw-medium text-nowrap">{{ $log->created_at->format('d/m/Y') }}</span>
                <br><small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
              </td>
              <td class="text-muted">{{ optional($log->user)->name ?? '—' }}</td>
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
                  <span class="text-muted small">{{ $log->model_type }}</span>
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
                @else
                  <span class="text-muted small">—</span>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-5">
                <i class="mdi mdi-shield-search mdi-48px d-block mb-2 opacity-25"></i>
                Nenhum registro de auditoria encontrado.
              </td>
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

{{-- Modais de detalhes de audit log (fora da tabela para evitar problemas de z-index) --}}
@foreach($logs as $log)
  @if($log->old_values || $log->new_values)
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
            <i class="mdi mdi-account-outline me-1"></i>{{ optional($log->user)->name ?? '—' }}
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
  @endif
@endforeach

@endsection
