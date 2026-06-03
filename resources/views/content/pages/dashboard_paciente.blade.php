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
      <h4 class="mb-0">Olá, {{ Auth::user()->name }}</h4>
      @if($proximaConsulta)
        <p class="text-muted small mb-0 mt-1">
          Sua próxima consulta está marcada para
          <strong class="text-body">{{ $proximaConsulta->data_hora->format('d/m/Y \à\s H:i') }}</strong>.
        </p>
      @else
        <p class="text-muted small mb-0 mt-1">Acompanhe seu histórico de consultas e prescrições.</p>
      @endif
    </div>
    <a href="/agendar-consulta" class="btn btn-primary">
      <i class="mdi mdi-calendar-plus me-1"></i>Agendar consulta
    </a>
  </div>

  {{-- ================================================================ --}}
  {{-- CARD: PRÓXIMA CONSULTA                                           --}}
  {{-- ================================================================ --}}
  @if($proximaConsulta)
  <div class="card mb-4 overflow-hidden">
    <div class="row g-0">

      {{-- Bloco da data com cor primária (verde IFNMG) --}}
      <div class="col-md-4 d-flex flex-column justify-content-center align-items-center p-4 text-white"
           style="background: linear-gradient(135deg, #3DAA4A 0%, #2e8a3a 100%); min-height: 160px;">
        <span class="text-uppercase fw-semibold small mb-2" style="letter-spacing:.07em; opacity:.85;">Próxima consulta</span>
        <div class="d-flex align-items-baseline gap-2 mb-1">
          <span class="display-4 fw-bold lh-1">{{ $proximaConsulta->data_hora->format('d') }}</span>
          <span class="fs-4 fw-semibold">{{ $proximaConsulta->data_hora->isoFormat('MMM') }}</span>
        </div>
        <span class="fs-5 fw-semibold">{{ $proximaConsulta->data_hora->format('H:i') }}</span>
        <span class="small mt-1" style="opacity:.85;">{{ $proximaConsulta->data_hora->isoFormat('dddd') }}</span>
      </div>

      {{-- Detalhes da consulta --}}
      <div class="col-md-8 d-flex flex-column justify-content-between p-4">
        <div>
          {{-- Avatar + profissional --}}
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="avatar avatar-md flex-shrink-0">
              <span class="avatar-initial rounded-circle bg-label-success fw-bold">
                {{ strtoupper(substr($proximaConsulta->profissional->nome ?? 'P', 0, 1)) }}
              </span>
            </div>
            <div>
              <p class="mb-0 fw-semibold">{{ $proximaConsulta->profissional->nome ?? '—' }}</p>
              <p class="mb-0 text-muted small">{{ $proximaConsulta->profissional->especialidade ?? 'Especialidade não informada' }}</p>
            </div>
          </div>

          {{-- Tipo e informações adicionais --}}
          <div class="d-flex flex-wrap gap-4 mb-3">
            <div class="d-flex align-items-center gap-2">
              <i class="mdi mdi-stethoscope text-primary"></i>
              <div>
                <p class="text-muted mb-0" style="font-size:10px; text-transform:uppercase; letter-spacing:.06em;">Tipo</p>
                <p class="mb-0 fw-medium small">{{ $proximaConsulta->tipo ?? 'Consulta' }}</p>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <i class="mdi mdi-calendar-clock text-primary"></i>
              <div>
                <p class="text-muted mb-0" style="font-size:10px; text-transform:uppercase; letter-spacing:.06em;">Data</p>
                <p class="mb-0 fw-medium small">{{ $proximaConsulta->data_hora->format('d/m/Y') }}</p>
              </div>
            </div>
          </div>
        </div>

        {{-- Ações --}}
        <div class="d-flex gap-2 flex-wrap">
          <a href="/meus-agendamentos" class="btn btn-sm btn-primary">
            <i class="mdi mdi-calendar-check-outline me-1"></i>Ver meus agendamentos
          </a>
          <a href="/meu-prontuario" class="btn btn-sm btn-outline-secondary">
            <i class="mdi mdi-folder-account-outline me-1"></i>Meu prontuário
          </a>
        </div>
      </div>

    </div>
  </div>
  @else
  {{-- Empty state — sem próxima consulta --}}
  <div class="card mb-4">
    <div class="card-body d-flex align-items-center gap-4 py-4">
      <div class="avatar avatar-xl flex-shrink-0">
        <span class="avatar-initial rounded-circle bg-label-warning" style="font-size:1.6rem;">
          <i class="mdi mdi-calendar-blank-outline"></i>
        </span>
      </div>
      <div>
        <h5 class="mb-1">Nenhuma consulta agendada</h5>
        <p class="text-muted mb-2">Você não possui consultas confirmadas no momento.</p>
        <a href="/agendar-consulta" class="btn btn-sm btn-primary">
          <i class="mdi mdi-calendar-plus me-1"></i>Agendar agora
        </a>
      </div>
    </div>
  </div>
  @endif

  {{-- ================================================================ --}}
  {{-- HISTÓRICO + PRESCRIÇÕES                                           --}}
  {{-- ================================================================ --}}
  <div class="row g-4 mb-4">

    {{-- Consultas recentes --}}
    <div class="col-md-7">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="mdi mdi-history text-primary"></i>
            <h5 class="card-title mb-0">Consultas recentes</h5>
          </div>
          <a href="/meu-prontuario" class="btn btn-sm btn-outline-primary">Ver prontuário completo</a>
        </div>
        <div class="card-body p-0">
          @forelse($historicoConsultas as $consulta)
          <div class="d-flex align-items-center gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
            {{-- Data --}}
            <div class="text-center flex-shrink-0" style="min-width:52px;">
              <span class="fw-bold d-block lh-1" style="font-size:1.2rem;">{{ $consulta->data_hora->format('d') }}</span>
              <span class="text-muted small text-uppercase">{{ $consulta->data_hora->isoFormat('MMM') }}</span>
            </div>
            <div class="vr mx-1"></div>
            {{-- Avatar profissional --}}
            <div class="avatar avatar-sm flex-shrink-0">
              <span class="avatar-initial rounded-circle bg-label-primary">
                {{ strtoupper(substr($consulta->profissional->nome ?? 'P', 0, 1)) }}
              </span>
            </div>
            <div class="flex-grow-1 min-width-0">
              <p class="mb-0 fw-medium small">{{ $consulta->tipo ?? 'Consulta' }}</p>
              <p class="mb-0 text-muted" style="font-size:11px;">{{ $consulta->profissional->nome ?? '—' }}</p>
            </div>
            <a href="/consultas/{{ $consulta->id }}" class="text-primary" title="Ver consulta">
              <i class="mdi mdi-file-document-outline fs-5"></i>
            </a>
          </div>
          @empty
          <div class="d-flex flex-column align-items-center py-5 text-muted">
            <i class="mdi mdi-clipboard-text-off-outline fs-1 mb-2"></i>
            <p class="mb-0">Nenhuma consulta registrada ainda.</p>
          </div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- Prescrições recentes --}}
    <div class="col-md-5">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="mdi mdi-pill text-warning"></i>
            <h5 class="card-title mb-0">Prescrições recentes</h5>
          </div>
          @if($prescricoesRecentes->isNotEmpty())
            <span class="badge bg-label-success">{{ $prescricoesRecentes->count() }}</span>
          @endif
        </div>
        <div class="card-body">
          @forelse($prescricoesRecentes as $prescricao)
          <div class="p-3 rounded mb-3 border border-start border-3"
               style="border-left-color: #3DAA4A !important;">
            <div class="d-flex justify-content-between align-items-start mb-1">
              <p class="mb-0 fw-semibold small">{{ $prescricao->nome_medicamento }}</p>
              <span class="badge bg-label-success" style="font-size:10px;">ATIVA</span>
            </div>
            <p class="text-muted mb-1" style="font-size:12px;">{{ $prescricao->dosagem }}</p>
            @if($prescricao->duracao)
              <p class="text-muted mb-1" style="font-size:11px;">Duração: {{ $prescricao->duracao }}</p>
            @endif
            <p class="text-muted mb-0" style="font-size:11px;">
              <i class="mdi mdi-account-outline me-1"></i>{{ $prescricao->consulta->profissional->nome ?? '—' }}
            </p>
          </div>
          @empty
          <div class="d-flex flex-column align-items-center py-4 text-muted">
            <i class="mdi mdi-pill-off-outline fs-1 mb-2"></i>
            <p class="mb-0">Nenhuma prescrição registrada.</p>
          </div>
          @endforelse
        </div>
      </div>
    </div>

  </div>

  {{-- ================================================================ --}}
  {{-- AÇÕES RÁPIDAS                                                     --}}
  {{-- ================================================================ --}}
  <div class="row g-3">
    <div class="col-sm-6 col-md-4">
      <a href="/agendar-consulta" class="card text-decoration-none h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary"><i class="mdi mdi-calendar-plus mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 fw-semibold small">Agendar consulta</p>
            <p class="mb-0 text-muted" style="font-size:11px;">Solicitar novo agendamento</p>
          </div>
          <i class="mdi mdi-chevron-right ms-auto text-muted"></i>
        </div>
      </a>
    </div>
    <div class="col-sm-6 col-md-4">
      <a href="/meu-prontuario" class="card text-decoration-none h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-info"><i class="mdi mdi-folder-account-outline mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 fw-semibold small">Meu prontuário</p>
            <p class="mb-0 text-muted" style="font-size:11px;">Ver histórico completo</p>
          </div>
          <i class="mdi mdi-chevron-right ms-auto text-muted"></i>
        </div>
      </a>
    </div>
    <div class="col-sm-6 col-md-4">
      <a href="/meus-agendamentos" class="card text-decoration-none h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-success"><i class="mdi mdi-calendar-check-outline mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 fw-semibold small">Meus agendamentos</p>
            <p class="mb-0 text-muted" style="font-size:11px;">Ver e gerenciar consultas</p>
          </div>
          <i class="mdi mdi-chevron-right ms-auto text-muted"></i>
        </div>
      </a>
    </div>
  </div>

</div>
@endsection
