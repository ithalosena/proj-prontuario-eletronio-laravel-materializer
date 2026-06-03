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
  {{-- SAUDAÇÃO                                                          --}}
  {{-- ================================================================ --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h4 class="mb-0">Bom dia, {{ Auth::user()->name }}</h4>
      <p class="text-muted small mb-0 mt-1">
        {{ now()->isoFormat('D [de] MMMM [·] dddd') }}
        @if($consultasHoje > 0)
          · você tem <strong class="text-body">{{ $consultasHoje }} {{ Str::plural('consulta', $consultasHoje) }}</strong> registradas hoje.
        @else
          · nenhuma consulta registrada hoje.
        @endif
      </p>
    </div>
    <a href="/atendimentos/criar" class="btn btn-primary">
      <i class="mdi mdi-plus me-1"></i>Novo prontuário
    </a>
  </div>

  {{-- ================================================================ --}}
  {{-- BANNER: PRÓXIMA CONSULTA                                         --}}
  {{-- ================================================================ --}}
  @if($proximoAgendamento)
  <div class="card mb-4 text-white"
       style="background: linear-gradient(110deg, #3DAA4A 0%, #2e8a3a 60%, #3DAA4A 100%); border:none;">
    <div class="card-body py-4 px-4 position-relative overflow-hidden">
      {{-- Anel decorativo --}}
      <div class="position-absolute rounded-circle"
           style="right:-60px; top:-60px; width:200px; height:200px;
                  border:40px solid rgba(255,255,255,0.07); pointer-events:none;"></div>

      <div class="d-flex align-items-center gap-4 position-relative">
        {{-- Ícone --}}
        <div class="flex-shrink-0 rounded-3 d-flex align-items-center justify-content-center"
             style="width:56px; height:56px; background:rgba(255,255,255,0.18); font-size:1.8rem;">
          <i class="mdi mdi-clock-fast"></i>
        </div>

        {{-- Informações --}}
        <div class="flex-grow-1 min-width-0">
          <p class="mb-1 text-uppercase fw-semibold" style="font-size:11px; letter-spacing:.08em; opacity:.85;">
            Próxima consulta
          </p>
          <h4 class="mb-1 fw-bold text-white">
            {{ $proximoAgendamento->data_hora->format('H:i') }}
            · {{ $proximoAgendamento->paciente->nome ?? 'Paciente' }}
          </h4>
          <p class="mb-0" style="font-size:13px; opacity:.92;">
            {{ $proximoAgendamento->tipo ?? 'Consulta' }}
            · {{ $proximoAgendamento->data_hora->format('d/m/Y') }}
          </p>
        </div>

        {{-- CTA --}}
        <a href="/atendimentos?paciente={{ $proximoAgendamento->paciente_id }}"
           class="btn btn-sm fw-semibold flex-shrink-0"
           style="background:#fff; color:#3DAA4A; border:none;">
          <i class="mdi mdi-play-circle-outline me-1"></i>Iniciar atendimento
        </a>
      </div>
    </div>
  </div>
  @else
  {{-- Empty state banner --}}
  <div class="card mb-4 border-dashed">
    <div class="card-body d-flex align-items-center gap-3 py-3">
      <div class="avatar flex-shrink-0">
        <span class="avatar-initial rounded bg-label-success"><i class="mdi mdi-calendar-blank-outline mdi-24px"></i></span>
      </div>
      <p class="mb-0 text-muted">Nenhuma consulta confirmada nos próximos dias.</p>
    </div>
  </div>
  @endif

  {{-- ================================================================ --}}
  {{-- STATS                                                             --}}
  {{-- ================================================================ --}}
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-md flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary"><i class="mdi mdi-calendar-today mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 text-muted small">Consultas hoje</p>
            <h4 class="mb-0 fw-bold">{{ $consultasHoje }}</h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-md flex-shrink-0">
            <span class="avatar-initial rounded bg-label-info"><i class="mdi mdi-account-multiple-outline mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 text-muted small">Pacientes esta semana</p>
            <h4 class="mb-0 fw-bold">{{ $pacientesSemana }}</h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-4">
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
  </div>

  {{-- ================================================================ --}}
  {{-- AGENDA DA SEMANA + COLUNA LATERAL                                --}}
  {{-- ================================================================ --}}
  <div class="row g-4">

    {{-- Agenda da semana (lista simples — timeline visual fica pós-TCC) --}}
    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="mdi mdi-calendar-week text-primary"></i>
            <h5 class="card-title mb-0">Agenda da semana</h5>
          </div>
          <div class="d-flex align-items-center gap-3" style="font-size:12px;">
            <span class="d-flex align-items-center gap-1">
              <span class="rounded-circle bg-success d-inline-block" style="width:8px;height:8px;"></span>
              Confirmado
            </span>
            <span class="d-flex align-items-center gap-1">
              <span class="rounded-circle bg-warning d-inline-block" style="width:8px;height:8px;"></span>
              Pendente
            </span>
            <span class="d-flex align-items-center gap-1">
              <span class="rounded-circle bg-secondary d-inline-block" style="width:8px;height:8px;"></span>
              Realizado
            </span>
          </div>
        </div>

        <div class="card-body p-0">
          @forelse($agendaSemana as $item)

          @php
            $statusColor = match($item->status) {
              'confirmado' => 'success',
              'pendente'   => 'warning',
              'realizado'  => 'secondary',
              'cancelado'  => 'danger',
              default      => 'primary',
            };
            $statusLabel = match($item->status) {
              'confirmado' => 'Confirmado',
              'pendente'   => 'Pendente',
              'realizado'  => 'Realizado',
              'cancelado'  => 'Cancelado',
              default      => $item->status,
            };
          @endphp

          <div class="d-flex align-items-center gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
            {{-- Data/hora --}}
            <div class="text-center flex-shrink-0" style="min-width:52px;">
              <span class="fw-bold d-block lh-1" style="font-size:1.1rem;">{{ $item->data_hora->format('H:i') }}</span>
              <span class="text-muted small text-uppercase">{{ $item->data_hora->isoFormat('ddd D') }}</span>
            </div>
            <div class="vr mx-1"></div>
            {{-- Avatar paciente --}}
            <div class="avatar avatar-sm flex-shrink-0">
              <span class="avatar-initial rounded-circle bg-label-{{ $statusColor }}">
                {{ strtoupper(substr($item->paciente->nome ?? 'P', 0, 1)) }}
              </span>
            </div>
            {{-- Dados --}}
            <div class="flex-grow-1 min-width-0">
              <p class="mb-0 fw-medium small text-truncate">{{ $item->paciente->nome ?? '—' }}</p>
              <p class="mb-0 text-muted" style="font-size:11px;">{{ $item->tipo ?? 'Consulta' }}</p>
            </div>
            {{-- Badge status --}}
            <span class="badge bg-label-{{ $statusColor }}">{{ $statusLabel }}</span>
          </div>

          @empty
          <div class="d-flex flex-column align-items-center py-5 text-muted">
            <i class="mdi mdi-calendar-blank-outline fs-1 mb-2"></i>
            <p class="mb-0">Nenhum agendamento esta semana.</p>
          </div>
          @endforelse
        </div>

        <div class="card-footer text-center">
          <a href="/agendamentos" class="btn btn-sm btn-outline-primary">
            <i class="mdi mdi-calendar-month-outline me-1"></i>Ver agenda completa
          </a>
        </div>
      </div>
    </div>

    {{-- Coluna lateral: consultas recentes + ações rápidas --}}
    <div class="col-lg-5 d-flex flex-column gap-4">

      {{-- Consultas recentes --}}
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="mdi mdi-account-clock-outline text-primary"></i>
            <h5 class="card-title mb-0">Últimas consultas</h5>
          </div>
          <a href="/consultas" class="btn btn-sm btn-outline-primary">Ver todas</a>
        </div>
        <div class="card-body p-0">
          @forelse($ultimasConsultas as $consulta)
          <div class="d-flex align-items-center gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
            <div class="avatar avatar-sm flex-shrink-0">
              <span class="avatar-initial rounded-circle bg-label-success">
                {{ strtoupper(substr($consulta->paciente->nome ?? 'P', 0, 1)) }}
              </span>
            </div>
            <div class="flex-grow-1 min-width-0">
              <p class="mb-0 fw-medium small text-truncate">{{ $consulta->paciente->nome ?? '—' }}</p>
              <p class="mb-0 text-muted" style="font-size:11px;">
                {{ $consulta->tipo ?? 'Consulta' }} · {{ $consulta->data_hora->format('d/m/Y') }}
              </p>
            </div>
            <a href="/consultas/{{ $consulta->id }}" class="text-muted">
              <i class="mdi mdi-chevron-right fs-5"></i>
            </a>
          </div>
          @empty
          <div class="d-flex flex-column align-items-center py-4 text-muted">
            <i class="mdi mdi-clipboard-text-off-outline fs-1 mb-2"></i>
            <p class="mb-0 small">Nenhuma consulta registrada.</p>
          </div>
          @endforelse
        </div>
      </div>

      {{-- Ações rápidas --}}
      <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
          <i class="mdi mdi-lightning-bolt-outline text-warning"></i>
          <h5 class="card-title mb-0">Ações rápidas</h5>
        </div>
        <div class="card-body d-flex flex-column gap-2">
          <a href="/atendimentos/criar" class="btn btn-primary w-100 text-start">
            <i class="mdi mdi-clipboard-edit-outline me-2"></i>Novo prontuário
          </a>
          <a href="/agendamentos" class="btn btn-outline-info w-100 text-start">
            <i class="mdi mdi-calendar-month-outline me-2"></i>Ver agenda completa
          </a>
          <a href="/disponibilidade" class="btn btn-outline-warning w-100 text-start">
            <i class="mdi mdi-clock-outline me-2"></i>Minha disponibilidade
          </a>
        </div>
      </div>

    </div>
  </div>

</div>
@endsection
