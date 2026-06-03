@php
$configData = Helper::appClasses();
$iniciais   = collect(explode(' ', $paciente->nome ?? 'P'))
    ->filter()->map(fn($p) => strtoupper($p[0]))->take(2)->implode('');
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Histórico do Paciente')

{{-- Breadcrumb: Início > Pacientes > Nome > Histórico --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',                                   'url' => '/'],
      ['label' => 'Pacientes',                                'url' => '/pacientes'],
      ['label' => $paciente->nome,                            'url' => '/pacientes/' . $paciente->id],
      ['label' => 'Histórico',                                'url' => null],
    ]
  ])
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================
       HERO — Avatar, nome e identificação do paciente
       ================================================================ --}}
  <div class="card mb-4">
    <div class="card-body py-4">
      <div class="d-flex flex-wrap align-items-center gap-4">

        <div class="flex-shrink-0">
          <div class="avatar avatar-xl">
            <span class="avatar-initial rounded-circle bg-label-primary"
                  style="font-size:1.4rem; width:64px; height:64px; display:flex; align-items:center; justify-content:center;">
              {{ $iniciais }}
            </span>
          </div>
        </div>

        <div class="flex-grow-1">
          {{-- ANALISE-04 (v0.10.1): nome agora é link para o perfil (antes não clicava) --}}
          <h4 class="mb-1">
            <a href="/pacientes/{{ $paciente->id }}" class="text-body text-decoration-none">{{ $paciente->nome }}</a>
          </h4>
          <div class="d-flex flex-wrap gap-3 text-muted small">
            @if($paciente->matricula)
              <span><i class="mdi mdi-card-account-details-outline me-1"></i>{{ $paciente->matricula }}</span>
            @endif
            @if($paciente->curso)
              <span><i class="mdi mdi-school-outline me-1"></i>{{ $paciente->curso }}</span>
            @endif
          </div>
        </div>

        <div class="d-flex flex-column gap-2 ms-auto">
          @if(Auth::user()->nivelAcesso() <= 3)
          <a href="/cadastro-consulta?paciente_id={{ $paciente->id }}" class="btn btn-primary btn-sm">
            <i class="mdi mdi-plus me-1"></i>Nova Consulta
          </a>
          @endif
          <a href="/pacientes/{{ $paciente->id }}" class="btn btn-outline-secondary btn-sm">
            <i class="mdi mdi-account-outline me-1"></i>Ver Perfil
          </a>
          <a href="{{ url()->previous('/pacientes') }}" class="btn btn-default btn-sm">
            <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
          </a>
        </div>

      </div>
    </div>
  </div>

  {{-- Flash message --}}
  @if(session('success'))
  <div class="alert alert-success alert-dismissible mb-3" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
  </div>
  @endif

  {{-- ================================================================
       Lista de consultas
       ================================================================ --}}
  <div class="card mb-4">
    <div class="card-header header-elements">
      <h5 class="card-title mb-0">
        <i class="mdi mdi-history me-2"></i>Consultas Registradas
        <span class="badge rounded-pill bg-label-primary ms-2">{{ $consultas->count() }}</span>
      </h5>
    </div>
    <div class="card-body p-0">

      @forelse($consultas as $consulta)
      <div class="p-4 border-bottom">

        {{-- Cabeçalho da consulta: data, tipo, profissional e link --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
          <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="fw-semibold">{{ $consulta->data_hora->format('d/m/Y \à\s H:i') }}</span>
            <span class="badge rounded-pill bg-label-primary">{{ $consulta->tipo }}</span>
          </div>
          <div class="d-flex align-items-center gap-3">
            <span class="text-muted small">
              <i class="mdi mdi-doctor me-1"></i>{{ $consulta->profissional->nome ?? '-' }}
            </span>
            <a href="/consultas/{{ $consulta->id }}" class="btn btn-sm btn-outline-primary">
              <i class="mdi mdi-file-document-outline me-1"></i>Ver Prontuário
            </a>
          </div>
        </div>

        {{-- Conteúdo clínico + exames/prescrições --}}
        <div class="row g-3">

          <div class="col-md-6">
            @if($consulta->queixa)
            <div class="mb-3">
              <p class="text-muted small mb-1">Queixa Principal</p>
              <p class="mb-0">{{ $consulta->queixa }}</p>
            </div>
            @endif
            @if($consulta->diagnostico)
            <div class="mb-3">
              <p class="text-muted small mb-1">Diagnóstico</p>
              <p class="mb-0">{{ $consulta->diagnostico }}</p>
            </div>
            @endif
            @if($consulta->conduta)
            <div class="mb-0">
              <p class="text-muted small mb-1">Conduta</p>
              <p class="mb-0">{{ $consulta->conduta }}</p>
            </div>
            @endif
            @if(!$consulta->queixa && !$consulta->diagnostico && !$consulta->conduta)
              <p class="text-muted fst-italic mb-0">Nenhum registro clínico.</p>
            @endif
          </div>

          <div class="col-md-6">
            @if($consulta->exames->count() > 0)
            <div class="mb-3">
              <p class="text-muted small mb-2">
                <i class="mdi mdi-test-tube-outline me-1"></i>Exames ({{ $consulta->exames->count() }})
              </p>
              @foreach($consulta->exames as $exame)
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="small">{{ $exame->tipo }}</span>
                @if($exame->resultado)
                  <span class="badge bg-label-success">Com resultado</span>
                @else
                  <span class="badge bg-label-warning">Pendente</span>
                @endif
              </div>
              @endforeach
            </div>
            @endif

            @if($consulta->prescricoes->count() > 0)
            <div class="mb-0">
              <p class="text-muted small mb-2">
                <i class="mdi mdi-pill me-1"></i>Prescrições ({{ $consulta->prescricoes->count() }})
              </p>
              @foreach($consulta->prescricoes as $prescricao)
              <div class="mb-1">
                <span class="small fw-semibold">{{ $prescricao->nome_medicamento }}</span>
                @if($prescricao->dosagem || $prescricao->frequencia)
                <span class="small text-muted"> — {{ $prescricao->dosagem }}{{ $prescricao->dosagem && $prescricao->frequencia ? ', ' : '' }}{{ $prescricao->frequencia }}</span>
                @endif
              </div>
              @endforeach
            </div>
            @endif

            @if($consulta->exames->count() === 0 && $consulta->prescricoes->count() === 0)
              <p class="text-muted fst-italic small mb-0">Nenhum exame ou prescrição vinculado.</p>
            @endif
          </div>

        </div>
      </div>

      @empty
      <div class="text-center py-5 text-muted">
        <i class="mdi mdi-calendar-remove-outline d-block mb-2" style="font-size:2.5rem;"></i>
        <p class="mb-3">Nenhuma consulta registrada para este paciente.</p>
        @if(Auth::user()->nivelAcesso() <= 3)
        <a href="/cadastro-consulta?paciente_id={{ $paciente->id }}" class="btn btn-primary">
          <i class="mdi mdi-plus me-1"></i>Registrar Primeira Consulta
        </a>
        @endif
      </div>
      @endforelse

    </div>
  </div>

</div>

@endsection