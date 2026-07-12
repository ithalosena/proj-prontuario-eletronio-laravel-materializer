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
      <h4 class="mb-0">Olá, {{ $paciente->nome_exibicao ?? Auth::user()->name }}</h4>
      @if($proximaConsulta)
        <p class="text-muted small mb-0 mt-1">
          Sua próxima consulta está marcada para
          <strong class="text-body">{{ $proximaConsulta->data_hora->format('d/m/Y \à\s H:i') }}</strong>.
        </p>
      @else
        <p class="text-muted small mb-0 mt-1">Acompanhe seu histórico de consultas e prescrições.</p>
      @endif
    </div>
    {{-- Agendar consulta vive no banner (card do topo), não no header --}}
  </div>

  {{-- ================================================================ --}}
  {{-- CARD: PRÓXIMA CONSULTA                                           --}}
  {{-- ================================================================ --}}
  @if($proximaConsulta)
  <div class="card mb-4 overflow-hidden border-0 shadow-sm">
    <div class="row g-0">

      {{-- Bloco da data — degradê da marca (escuro→claro, v0.10.3) --}}
      <div class="col-md-4 d-flex flex-column justify-content-center align-items-center p-4 text-white"
           style="background: linear-gradient(160deg, #237030 0%, #3DAA4A 55%, #56cf66 100%); min-height: 170px;">
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

        {{-- Ações — grupo enxuto: CTA principal + secundário (full-width no mobile, natural no desktop) --}}
        <div class="d-flex flex-column flex-sm-row gap-2 mt-3">
          <a href="/agendar-consulta" class="btn btn-sm btn-primary">
            <i class="mdi mdi-calendar-plus me-1"></i>Agendar nova consulta
          </a>
          <a href="/meus-agendamentos" class="btn btn-sm btn-outline-secondary">
            <i class="mdi mdi-calendar-check-outline me-1"></i>Meus agendamentos
          </a>
        </div>
      </div>

    </div>
  </div>
  @else
  {{-- Empty state — degradê âmbar em dois tons (espelha o verde da próxima consulta) --}}
  <div class="card mb-4 border-0 shadow-sm text-white overflow-hidden"
       style="background: linear-gradient(160deg, #b9760f 0%, #f0a020 55%, #ffce6b 100%);">
    <div class="card-body d-flex flex-wrap align-items-center gap-3 py-4 position-relative">
      <div class="position-absolute rounded-circle"
           style="right:-50px; top:-50px; width:160px; height:160px; border:32px solid rgba(255,255,255,0.10); pointer-events:none;"></div>
      <div class="flex-shrink-0 rounded-3 d-flex align-items-center justify-content-center position-relative"
           style="width:56px; height:56px; background:rgba(255,255,255,0.22); font-size:1.7rem;">
        <i class="mdi mdi-calendar-blank-outline"></i>
      </div>
      <div class="flex-grow-1 min-width-0 position-relative">
        <h5 class="mb-1 fw-bold text-white">Nenhuma consulta agendada</h5>
        <p class="mb-0" style="opacity:.92; font-size:13px;">Você não possui consultas confirmadas no momento.</p>
      </div>
      <a href="/agendar-consulta" class="btn btn-sm fw-semibold flex-shrink-0 position-relative"
         style="background:#fff; color:#a86a0c; border:none;">
        <i class="mdi mdi-calendar-plus me-1"></i>Agendar agora
      </a>
    </div>
  </div>
  @endif

  {{-- ================================================================ --}}
  {{-- HISTÓRICO + PRESCRIÇÕES                                           --}}
  {{-- ================================================================ --}}
  <div class="row g-4 mb-4">

    {{-- Consultas recentes --}}
    <div class="col-md-7">
      <div class="card h-100 border-0 shadow-sm">
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
            {{-- Paciente não tem acesso a /consultas/{id} (rota nivel:3 → 403).
                 O detalhe clínico do paciente vive no /meu-prontuario. --}}
            <a href="/meu-prontuario" class="text-primary" title="Ver no meu prontuário">
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

    {{-- Atividade clínica recente (exames + prescrições) --}}
    <div class="col-md-5">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-header d-flex align-items-center gap-2">
          <i class="mdi mdi-heart-pulse text-primary"></i>
          <h5 class="card-title mb-0">Atividade clínica recente</h5>
        </div>
        <div class="card-body">
          @if($examesRecentes->isEmpty() && $prescricoesRecentes->isEmpty())
            <div class="d-flex flex-column align-items-center py-4 text-muted">
              <i class="mdi mdi-clipboard-text-off-outline fs-1 mb-2"></i>
              <p class="mb-0">Nenhum exame ou prescrição registrado.</p>
            </div>
          @else
            {{-- Exames com status real --}}
            @if($examesRecentes->isNotEmpty())
            <p class="fw-semibold small text-uppercase text-muted mb-2" style="letter-spacing:.04em">
              <i class="mdi mdi-test-tube-outline me-1"></i>Exames
            </p>
            @foreach($examesRecentes as $exame)
            <div class="d-flex align-items-center justify-content-between mb-2 gap-2">
              <div class="min-width-0">
                <p class="mb-0 small fw-medium text-truncate">{{ $exame->tipo ?? 'Exame' }}</p>
                <p class="mb-0 text-muted" style="font-size:11px;">{{ optional(optional($exame->consulta)->data_hora)->format('d/m/Y') }}</p>
              </div>
              @if($exame->resultado)
                <span class="badge bg-label-success flex-shrink-0">Com resultado</span>
              @else
                <span class="badge bg-label-warning flex-shrink-0">Pendente</span>
              @endif
            </div>
            @endforeach
            @endif

            {{-- Prescrições (sem badge "ATIVA" — não há status no schema) --}}
            @if($prescricoesRecentes->isNotEmpty())
            <p class="fw-semibold small text-uppercase text-muted mb-2 {{ $examesRecentes->isNotEmpty() ? 'mt-3 pt-2 border-top' : '' }}" style="letter-spacing:.04em">
              <i class="mdi mdi-pill me-1"></i>Prescrições
            </p>
            @foreach($prescricoesRecentes as $prescricao)
            <div class="mb-2">
              <p class="mb-0 small fw-semibold">{{ $prescricao->nome_medicamento }}</p>
              <p class="mb-0 text-muted" style="font-size:11px;">
                {{ $prescricao->dosagem }} · {{ optional(optional($prescricao->consulta)->profissional)->nome ?? '—' }}
              </p>
            </div>
            @endforeach
            @endif
          @endif
        </div>
      </div>
    </div>

  </div>

</div>
@endsection
