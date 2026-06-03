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

  @php
    // UX-06: mapeia cada action para [categoria, cor do badge, rótulo legível]
    $mapAcao = function ($action) {
      return match (true) {
        $action === 'login'                       => ['Autenticação', 'info',      'Login'],
        $action === 'logout'                      => ['Autenticação', 'secondary', 'Logout'],
        str_contains($action, 'created')          => ['Dados',        'success',   'Criou'],
        str_contains($action, 'updated')          => ['Dados',        'warning',   'Editou'],
        str_contains($action, 'deleted')          => ['Dados',        'danger',    'Excluiu'],
        str_contains($action, 'consentimento')    => ['Consentimento','primary',   ucfirst(str_replace('_', ' ', $action))],
        default                                   => ['Sistema',      'info',      ucfirst(str_replace('_', ' ', $action))],
      };
    };
  @endphp

  {{-- ================================================================ --}}
  {{-- FILTROS (UX-06): ação, usuário, entidade, intervalo de datas      --}}
  {{-- ================================================================ --}}
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" action="/audit-logs" class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label small mb-1">Ação</label>
          <select name="action" class="form-select form-select-sm">
            <option value="">Todas</option>
            @foreach($acoes as $a)
              <option value="{{ $a }}" {{ $filtroAction === $a ? 'selected' : '' }}>{{ $mapAcao($a)[2] }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Usuário</label>
          <select name="user_id" class="form-select form-select-sm">
            <option value="">Todos</option>
            @foreach($usuarios as $u)
              <option value="{{ $u->id }}" {{ (string)$filtroUser === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Entidade</label>
          <select name="model_type" class="form-select form-select-sm">
            <option value="">Todas</option>
            @foreach($entidades as $e)
              <option value="{{ $e }}" {{ $filtroModel === $e ? 'selected' : '' }}>{{ class_basename($e) }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">De</label>
          <input type="date" name="data_de" class="form-control form-control-sm" value="{{ $dataDe }}">
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Até</label>
          <input type="date" name="data_ate" class="form-control form-control-sm" value="{{ $dataAte }}">
        </div>
        <div class="col-12 d-flex gap-2 mt-2">
          <button type="submit" class="btn btn-sm btn-primary"><i class="mdi mdi-filter-outline me-1"></i>Filtrar</button>
          @if($filtroAction || $filtroUser || $filtroModel || $dataDe || $dataAte)
          <a href="/audit-logs" class="btn btn-sm btn-outline-secondary"><i class="mdi mdi-close me-1"></i>Limpar</a>
          @endif
        </div>
      </form>
    </div>
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
              <th>IP</th>
              <th>Detalhes</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse($logs as $log)
            @php [$categoria, $cor, $rotulo] = $mapAcao($log->action); @endphp
            <tr>
              <td>
                <span class="fw-medium text-nowrap">{{ $log->created_at->format('d/m/Y') }}</span>
                <br><small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
              </td>
              <td class="text-muted">{{ optional($log->user)->name ?? '—' }}</td>
              <td>
                <span class="badge rounded-pill bg-label-{{ $cor }}">{{ $rotulo }}</span>
                <br><small class="text-muted">{{ $categoria }}</small>
              </td>
              <td>
                @if($log->model_type)
                  <span class="fw-medium small">{{ class_basename($log->model_type) }}</span>
                  @if($log->model_id)<span class="text-muted small">#{{ $log->model_id }}</span>@endif
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td><span class="text-muted small">{{ $log->ip_address ?? '—' }}</span></td>
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
              <td colspan="6" class="text-center text-muted py-5">
                <i class="mdi mdi-shield-search mdi-48px d-block mb-2 opacity-25"></i>
                Nenhum registro de auditoria encontrado para os filtros aplicados.
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
            {{ ucfirst(str_replace('_', ' ', $log->action)) }} — {{ class_basename($log->model_type) }} #{{ $log->model_id }}
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
