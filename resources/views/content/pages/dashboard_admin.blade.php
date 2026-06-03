@php $configData = Helper::appClasses(); @endphp

@extends('layouts/layoutMaster')

@section('title', 'Início')

{{-- Breadcrumb --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início', 'url' => null],
    ]
  ])
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================ --}}
  {{-- SAUDAÇÃO + AÇÕES PRIMÁRIAS                                        --}}
  {{-- ================================================================ --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h4 class="mb-0">Bem-vindo, {{ Auth::user()->name }}</h4>
      <p class="text-muted small mb-0 mt-1">Painel administrativo do sistema Prontu IF.</p>
    </div>
    <div class="d-flex gap-2">
      <a href="/configuracoes/especialidades" class="btn btn-outline-secondary">
        <i class="mdi mdi-cog-outline me-1"></i>Configurações
      </a>
      <a href="/cadastro-profissional" class="btn btn-primary">
        <i class="mdi mdi-account-plus-outline me-1"></i>Novo usuário
      </a>
    </div>
  </div>

  {{-- ================================================================ --}}
  {{-- KPIs GLOBAIS                                                      --}}
  {{-- ================================================================ --}}
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-md flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary"><i class="mdi mdi-account-group-outline mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 text-muted small">Total de pacientes</p>
            <h4 class="mb-0 fw-bold">{{ $totalPacientes }}</h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-md flex-shrink-0">
            <span class="avatar-initial rounded bg-label-success"><i class="mdi mdi-doctor mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 text-muted small">Profissionais</p>
            <h4 class="mb-0 fw-bold">{{ $totalProfissionais }}</h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-md flex-shrink-0">
            <span class="avatar-initial rounded bg-label-warning"><i class="mdi mdi-clipboard-edit-outline mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 text-muted small">Atendimentos abertos</p>
            <h4 class="mb-0 fw-bold">{{ $atendimentosAbertos }}</h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-md flex-shrink-0">
            <span class="avatar-initial rounded bg-label-info"><i class="mdi mdi-calendar-month-outline mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 text-muted small">Consultas no mês</p>
            <h4 class="mb-0 fw-bold">{{ $consultasNoMes }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ================================================================ --}}
  {{-- AUDIT LOG + COLUNA LATERAL (Widget LGPD + Atalhos)               --}}
  {{-- ================================================================ --}}
  <div class="row g-4 mb-4">

    {{-- Audit log recente --}}
    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="mdi mdi-history text-primary"></i>
            <h5 class="card-title mb-0">Atividades recentes</h5>
          </div>
          <a href="/audit-logs" class="btn btn-sm btn-outline-primary">
            Auditoria completa <i class="mdi mdi-arrow-right ms-1"></i>
          </a>
        </div>
        <div class="card-body p-0">
          @forelse($auditLogs as $log)

          @php
            $iniciais = '?';
            $variant  = 'secondary';
            if ($log->user) {
              $parts   = explode(' ', trim($log->user->name ?? ''));
              $iniciais = strtoupper(($parts[0][0] ?? '') . (isset($parts[1]) ? $parts[1][0] : ''));
              $variant  = 'primary';
            }
            // Mapeamento de actions para labels legíveis em PT-BR
            $labelAction = match(true) {
              str_contains($log->action, 'criar')      || str_contains($log->action, 'created')   => ['Criou', 'success'],
              str_contains($log->action, 'editar')     || str_contains($log->action, 'updated')   => ['Editou', 'warning'],
              str_contains($log->action, 'excluir')    || str_contains($log->action, 'deleted')   => ['Excluiu', 'danger'],
              str_contains($log->action, 'login')                                                  => ['Login', 'info'],
              str_contains($log->action, 'logout')                                                 => ['Logout', 'secondary'],
              str_contains($log->action, 'consentimento')                                          => ['Consentimento', 'primary'],
              default => [ucfirst($log->action), 'secondary'],
            };
          @endphp

          <div class="d-flex align-items-center gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
            <div class="avatar avatar-sm flex-shrink-0">
              <span class="avatar-initial rounded-circle bg-label-{{ $variant }}">{{ $iniciais }}</span>
            </div>
            <div class="flex-grow-1 min-width-0">
              <p class="mb-0 small">
                <strong>{{ $log->user->name ?? 'Sistema' }}</strong>
                <span class="text-muted"> — {{ $log->action }}</span>
              </p>
              @if($log->model_type)
              <p class="mb-0 text-muted" style="font-size:11px;">{{ class_basename($log->model_type) }} #{{ $log->model_id }}</p>
              @endif
            </div>
            <span class="text-muted flex-shrink-0" style="font-size:11px;">
              {{ $log->created_at->format('d/m H:i') }}
            </span>
          </div>
          @empty
          <div class="d-flex flex-column align-items-center py-5 text-muted">
            <i class="mdi mdi-clipboard-text-off-outline fs-1 mb-2"></i>
            <p class="mb-0">Nenhuma atividade registrada.</p>
          </div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- Coluna lateral: Widget LGPD + Atalhos --}}
    <div class="col-lg-5 d-flex flex-column gap-4">

      {{-- Widget LGPD conformidade --}}
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="mdi mdi-shield-check-outline text-success"></i>
            <h5 class="card-title mb-0">Conformidade LGPD</h5>
          </div>
          <span class="badge bg-label-secondary" style="font-size:10px;">Pacientes · v{{ '1.0' }}</span>
        </div>
        <div class="card-body">
          <div class="row g-3">

            {{-- Bucket 1 — Aceitaram --}}
            <div class="col-6">
              <div class="p-3 rounded text-center" style="background: var(--bs-success-bg-subtle, #d1f7de);">
                <h3 class="fw-bold mb-1" style="color: #3DAA4A;">{{ $conformidade['aceitaram'] }}</h3>
                <p class="mb-0 text-muted small fw-medium">Aceitaram</p>
                <p class="mb-0" style="font-size:10px; color:#3DAA4A;">Termo vigente</p>
              </div>
            </div>

            {{-- Bucket 2 — Recusaram --}}
            <div class="col-6">
              <div class="p-3 rounded text-center bg-label-danger">
                <h3 class="fw-bold mb-1 text-danger">{{ $conformidade['recusaram'] }}</h3>
                <p class="mb-0 text-muted small fw-medium">Recusaram</p>
                <p class="mb-0 text-danger" style="font-size:10px;">Sem aceite vigente</p>
              </div>
            </div>

            {{-- Bucket 3 — Nunca acessaram --}}
            <div class="col-6">
              <div class="p-3 rounded text-center bg-label-warning">
                <h3 class="fw-bold mb-1 text-warning">{{ $conformidade['nunca_acessaram'] }}</h3>
                <p class="mb-0 text-muted small fw-medium">Nunca acessaram</p>
                <p class="mb-0 text-warning" style="font-size:10px;">Sem login registrado</p>
              </div>
            </div>

            {{-- Bucket 4 — Versão antiga --}}
            <div class="col-6">
              <div class="p-3 rounded text-center bg-label-info">
                <h3 class="fw-bold mb-1 text-info">{{ $conformidade['versao_antiga'] }}</h3>
                <p class="mb-0 text-muted small fw-medium">Versão antiga</p>
                <p class="mb-0 text-info" style="font-size:10px;">Precisa renovar</p>
              </div>
            </div>

          </div>

          {{-- Nota legal --}}
          <p class="text-muted mt-3 mb-0" style="font-size:10px;">
            <i class="mdi mdi-information-outline me-1"></i>
            Dados agregados — sem exposição de dados pessoais (Art. 6º VIII LGPD).
          </p>
        </div>
      </div>

      {{-- Atalhos de gestão --}}
      <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
          <i class="mdi mdi-lightning-bolt-outline text-warning"></i>
          <h5 class="card-title mb-0">Atalhos de gestão</h5>
        </div>
        <div class="card-body d-flex flex-column gap-2">
          <a href="/profissionais" class="btn btn-outline-primary w-100 text-start">
            <i class="mdi mdi-doctor me-2"></i>Gerenciar profissionais
          </a>
          <a href="/pacientes" class="btn btn-outline-info w-100 text-start">
            <i class="mdi mdi-account-group-outline me-2"></i>Gerenciar pacientes
          </a>
          <a href="/audit-logs" class="btn btn-outline-secondary w-100 text-start">
            <i class="mdi mdi-history me-2"></i>Logs de auditoria
          </a>
          <a href="/configuracoes/especialidades" class="btn btn-outline-success w-100 text-start">
            <i class="mdi mdi-cog-outline me-2"></i>Especialidades e tipos
          </a>
        </div>
      </div>

    </div>
  </div>

</div>
@endsection
