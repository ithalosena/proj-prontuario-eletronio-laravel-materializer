@php $configData = Helper::appClasses(); @endphp

@extends('layouts/layoutMaster')

@section('title', 'Início')

@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [['label' => 'Início', 'url' => null]]
  ])
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================ --}}
  {{-- SAUDAÇÃO                                                          --}}
  {{-- ================================================================ --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h4 class="mb-0">Olá, {{ Auth::user()->name }}</h4>
      <p class="text-muted small mb-0 mt-1">
        {{ now()->isoFormat('D [de] MMMM [·] dddd') }}
        @if($proximosCheckins->isNotEmpty())
          · <strong class="text-body">{{ $proximosCheckins->count() }} check-in(s)</strong> nos próximos horários.
        @endif
      </p>
    </div>
    <div class="d-flex gap-2">
      <a href="/cadastro-paciente" class="btn btn-outline-secondary">
        <i class="mdi mdi-account-plus-outline me-1"></i>Novo paciente
      </a>
      <a href="/agendar-consulta" class="btn btn-primary">
        <i class="mdi mdi-plus me-1"></i>Agendar
      </a>
    </div>
  </div>

  {{-- ================================================================ --}}
  {{-- PRÓXIMOS CHECK-INS (card destaque)                                --}}
  {{-- ================================================================ --}}
  <div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between border-bottom">
      <div class="d-flex align-items-center gap-2">
        <i class="mdi mdi-account-arrow-right-outline text-primary fs-5"></i>
        <h5 class="card-title mb-0">Próximos check-ins</h5>
        <span class="badge bg-label-primary ms-1">Confirmados hoje</span>
      </div>
      <a href="/agendamentos" class="btn btn-sm btn-outline-primary">Ver agenda completa</a>
    </div>

    @if($proximosCheckins->isNotEmpty())
    <div class="row g-0">
      @foreach($proximosCheckins as $checkin)
      <div class="col-6 col-md-3 {{ !$loop->last ? 'border-end' : '' }} p-4">
        <p class="fw-bold mb-1" style="font-size:1.4rem; line-height:1;">
          {{ $checkin->data_hora->format('H:i') }}
        </p>
        <p class="text-muted small mb-3">{{ $checkin->data_hora->isoFormat('ddd D/MM') }}</p>
        <div class="d-flex align-items-center gap-2">
          <div class="avatar avatar-sm flex-shrink-0">
            <span class="avatar-initial rounded-circle bg-label-primary">
              {{ strtoupper(substr($checkin->paciente->nome ?? 'P', 0, 1)) }}
            </span>
          </div>
          <div class="min-width-0">
            <p class="mb-0 fw-semibold small text-truncate">{{ $checkin->paciente->nome ?? '—' }}</p>
            <p class="mb-0 text-muted" style="font-size:10px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
              {{ $checkin->profissional->nome ?? '—' }}
            </p>
          </div>
        </div>
      </div>
      @endforeach
    </div>
    @else
    <div class="card-body d-flex align-items-center gap-3 py-3">
      <i class="mdi mdi-calendar-blank-outline fs-3 text-muted"></i>
      <p class="mb-0 text-muted">Nenhum check-in confirmado para as próximas horas.</p>
    </div>
    @endif
  </div>

  {{-- ================================================================ --}}
  {{-- STATS                                                             --}}
  {{-- ================================================================ --}}
  <div class="row g-4 mb-4">
    <div class="col-sm-4">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-md flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary"><i class="mdi mdi-calendar-today mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 text-muted small">Agendamentos hoje</p>
            <h4 class="mb-0 fw-bold">{{ $agendamentosHoje }}</h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-md flex-shrink-0">
            <span class="avatar-initial rounded bg-label-warning"><i class="mdi mdi-phone-outline mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 text-muted small">Confirmações pendentes</p>
            <h4 class="mb-0 fw-bold">{{ $confirmacoesPendentes }}</h4>
            <p class="mb-0 text-muted" style="font-size:10px;">Para amanhã</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-md flex-shrink-0">
            <span class="avatar-initial rounded bg-label-danger"><i class="mdi mdi-account-cancel-outline mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 text-muted small">Cancelamentos hoje</p>
            <h4 class="mb-0 fw-bold">{{ $cancelamentosHoje }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ================================================================ --}}
  {{-- CHEGADAS DO DIA + CONFIRMAÇÕES A FAZER                            --}}
  {{-- ================================================================ --}}
  <div class="row g-4">

    {{-- Chegadas do dia --}}
    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="mdi mdi-account-clock-outline text-primary"></i>
            <h5 class="card-title mb-0">Chegadas do dia</h5>
          </div>
          @if($chegadasDoDia->isNotEmpty())
          <span class="text-muted small">
            {{ $chegadasDoDia->where('status', 'realizado')->count() }} de {{ $chegadasDoDia->count() }} realizados
          </span>
          @endif
        </div>
        <div class="card-body p-0">
          @forelse($chegadasDoDia as $ag)
          @php
            $sc = match($ag->status) {
              'realizado'  => ['color' => 'success',   'label' => 'Realizado'],
              'confirmado' => ['color' => 'primary',   'label' => 'Confirmado'],
              'pendente'   => ['color' => 'warning',   'label' => 'Aguardando'],
              'cancelado'  => ['color' => 'danger',    'label' => 'Cancelado'],
              default      => ['color' => 'secondary', 'label' => $ag->status],
            };
          @endphp
          <div class="d-flex align-items-center gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}"
               style="{{ $ag->status === 'realizado' ? 'opacity:.55;' : '' }}">
            <span class="text-muted fw-semibold flex-shrink-0" style="font-size:12px; min-width:40px;">
              {{ $ag->data_hora->format('H:i') }}
            </span>
            <div class="flex-grow-1 min-width-0">
              <p class="mb-0 fw-medium small text-truncate">{{ $ag->paciente->nome ?? '—' }}</p>
              <p class="mb-0 text-muted" style="font-size:11px;">com {{ $ag->profissional->nome ?? '—' }}</p>
            </div>
            <span class="badge bg-label-{{ $sc['color'] }}">{{ $sc['label'] }}</span>
          </div>
          @empty
          <div class="d-flex flex-column align-items-center py-5 text-muted">
            <i class="mdi mdi-calendar-blank-outline fs-1 mb-2"></i>
            <p class="mb-0">Nenhum agendamento para hoje.</p>
          </div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- Confirmações a fazer + ações rápidas --}}
    <div class="col-lg-5 d-flex flex-column gap-4">

      {{-- Confirmações a fazer (agendamentos pendentes de amanhã) --}}
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="mdi mdi-phone-outline text-warning"></i>
            <h5 class="card-title mb-0">Confirmações a fazer</h5>
          </div>
          @if($confirmacoesFazer->isNotEmpty())
            <span class="badge bg-label-warning">{{ $confirmacoesFazer->count() }}</span>
          @endif
        </div>
        <div class="card-body p-0">
          @forelse($confirmacoesFazer as $ag)
          <div class="d-flex align-items-center gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
            <div class="avatar avatar-sm flex-shrink-0">
              <span class="avatar-initial rounded-circle bg-label-warning">
                {{ strtoupper(substr($ag->paciente->nome ?? 'P', 0, 1)) }}
              </span>
            </div>
            <div class="flex-grow-1 min-width-0">
              <p class="mb-0 fw-medium small text-truncate">{{ $ag->paciente->nome ?? '—' }}</p>
              <p class="mb-0 text-muted" style="font-size:11px;">
                {{ $ag->data_hora->format('H:i') }} · {{ $ag->profissional->nome ?? '—' }}
              </p>
            </div>
            <a href="/agendamentos/{{ $ag->id }}" class="text-muted">
              <i class="mdi mdi-chevron-right fs-5"></i>
            </a>
          </div>
          @empty
          <div class="d-flex flex-column align-items-center py-4 text-muted">
            <i class="mdi mdi-check-all fs-1 mb-2"></i>
            <p class="mb-0 small">Nada pendente para amanhã.</p>
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
          <a href="/agendar-consulta" class="btn btn-primary w-100 text-start">
            <i class="mdi mdi-calendar-plus me-2"></i>Novo agendamento
          </a>
          <a href="/cadastro-paciente" class="btn btn-outline-info w-100 text-start">
            <i class="mdi mdi-account-plus-outline me-2"></i>Cadastrar paciente
          </a>
          <a href="/agendamentos" class="btn btn-outline-success w-100 text-start">
            <i class="mdi mdi-calendar-month-outline me-2"></i>Ver agenda completa
          </a>
        </div>
      </div>

    </div>
  </div>

</div>
@endsection
