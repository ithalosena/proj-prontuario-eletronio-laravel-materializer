@php
$configData  = Helper::appClasses();
$paciente    = $consulta->paciente;
$profissional = $consulta->profissional;
$atendimento = $consulta->atendimento;
$iniciais    = collect(explode(' ', $paciente->nome ?? 'P'))
    ->filter()->map(fn($p) => strtoupper($p[0]))->take(2)->implode('');
$atendAberto = $atendimento?->isAberto() ?? true;
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Prontuário da Consulta')

{{-- Breadcrumb: Início > Consultas > Consulta #ID --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',                    'url' => '/'],
      ['label' => 'Consultas',                 'url' => '/consultas'],
      ['label' => 'Consulta #' . $consulta->id, 'url' => null],
    ]
  ])
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ST-12: hero card + flash messages via partial compartilhado por todas as especialidades --}}
  @include('content.pages.partials._consulta_header')

  <div class="row g-4">

    {{-- ================================================================ --}}
    {{-- ZONA 2: REGISTRO CLÍNICO (SOAP)                                  --}}
    {{-- ================================================================ --}}
    <div class="col-md-7">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title mb-0">
            <i class="mdi mdi-stethoscope me-2"></i>Registro Clínico
          </h5>
        </div>
        <div class="card-body p-0">

          {{-- Queixa --}}
          <div class="p-4 border-start border-4 border-primary">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="mdi mdi-chat-question-outline text-primary"></i>
              <span class="fw-semibold text-uppercase small tracking-wide text-muted">Queixa Principal</span>
            </div>
            @if($consulta->queixa)
              <p class="mb-0 lh-lg">{{ $consulta->queixa }}</p>
            @else
              <p class="mb-0 text-muted fst-italic small">Não registrado nesta consulta.</p>
            @endif
          </div>

          <hr class="my-0">

          {{-- Anamnese --}}
          <div class="p-4 border-start border-4 border-info">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="mdi mdi-clipboard-text-outline text-info"></i>
              <span class="fw-semibold text-uppercase small text-muted">Anamnese</span>
            </div>
            @if($consulta->anamnese)
              <p class="mb-0 lh-lg">{{ $consulta->anamnese }}</p>
            @else
              <p class="mb-0 text-muted fst-italic small">Não registrado nesta consulta.</p>
            @endif
          </div>

          <hr class="my-0">

          {{-- Diagnóstico --}}
          <div class="p-4 border-start border-4 border-warning">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="mdi mdi-microscope text-warning"></i>
              <span class="fw-semibold text-uppercase small text-muted">Diagnóstico</span>
            </div>
            @if($consulta->diagnostico)
              <p class="mb-0 lh-lg">{{ $consulta->diagnostico }}</p>
            @else
              <p class="mb-0 text-muted fst-italic small">Não registrado nesta consulta.</p>
            @endif
          </div>

          <hr class="my-0">

          {{-- Conduta --}}
          <div class="p-4 border-start border-4 border-success">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="mdi mdi-list-box-outline text-success"></i>
              <span class="fw-semibold text-uppercase small text-muted">Conduta</span>
            </div>
            @if($consulta->conduta)
              <p class="mb-0 lh-lg">{{ $consulta->conduta }}</p>
            @else
              <p class="mb-0 text-muted fst-italic small">Não registrado nesta consulta.</p>
            @endif
          </div>

        </div>
      </div>
    </div>

    {{-- ================================================================ --}}
    {{-- ZONA 3: EXAMES E PRESCRIÇÕES                                      --}}
    {{-- ================================================================ --}}
    <div class="col-md-5 d-flex flex-column gap-4">

      {{-- Exames --}}
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">
            <i class="mdi mdi-test-tube me-2"></i>Exames
            <span class="badge bg-label-secondary ms-1">{{ $consulta->exames->count() }}</span>
          </h5>
          @if($atendAberto && Auth::user()->nivelAcesso() <= 3)
          <a href="/cadastro-exame?consulta_id={{ $consulta->id }}" class="btn btn-sm btn-primary">
            <i class="mdi mdi-plus me-1"></i>Novo
          </a>
          @endif
        </div>
        <div class="card-body p-0">
          @forelse($consulta->exames as $exame)
          <div class="d-flex align-items-start p-3 border-bottom border-start border-3
            {{ $exame->resultado ? 'border-success' : 'border-warning' }}">
            <div class="flex-grow-1 min-width-0">
              <p class="fw-semibold mb-1">{{ $exame->tipo }}</p>
              @if($exame->observacao)
                <small class="text-muted d-block text-truncate">{{ $exame->observacao }}</small>
              @endif
              <small class="text-muted">
                <i class="mdi mdi-calendar-outline me-1"></i>
                {{ $exame->data_solicitacao ? $exame->data_solicitacao->format('d/m/Y') : '-' }}
              </small>
            </div>
            <div class="ms-3 d-flex flex-column align-items-end gap-1 flex-shrink-0">
              @if($exame->resultado)
                <span class="badge bg-label-success">Resultado</span>
              @else
                <span class="badge bg-label-warning">Pendente</span>
              @endif
              {{-- DT-03: ExamePolicy — admin sempre pode; outros: só autor com atendimento aberto --}}
              @can('update', $exame)
              <a href="/editar-exame/{{ $exame->id }}" class="btn btn-xs btn-outline-secondary">
                <i class="mdi mdi-pencil-outline me-1"></i>Editar
              </a>
              @endcan
            </div>
          </div>
          @empty
          <div class="p-4 text-center text-muted">
            <i class="mdi mdi-test-tube-empty mdi-36px d-block mb-2 text-muted opacity-50"></i>
            <p class="mb-0 small">Nenhum exame solicitado nesta consulta.</p>
            @if($atendAberto && Auth::user()->nivelAcesso() <= 3)
            <a href="/cadastro-exame?consulta_id={{ $consulta->id }}" class="btn btn-sm btn-outline-primary mt-2">
              <i class="mdi mdi-plus me-1"></i>Solicitar Exame
            </a>
            @endif
          </div>
          @endforelse
        </div>
      </div>

      {{-- Prescrições --}}
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">
            <i class="mdi mdi-pill me-2"></i>Prescrições
            <span class="badge bg-label-secondary ms-1">{{ $consulta->prescricoes->count() }}</span>
          </h5>
          @if($atendAberto && Auth::user()->nivelAcesso() <= 3)
          <a href="/cadastro-prescricao?consulta_id={{ $consulta->id }}" class="btn btn-sm btn-primary">
            <i class="mdi mdi-plus me-1"></i>Nova
          </a>
          @endif
        </div>
        <div class="card-body p-0">
          @forelse($consulta->prescricoes as $prescricao)
          <div class="d-flex align-items-start p-3 border-bottom border-start border-3 border-primary">
            <div class="flex-grow-1 min-width-0">
              <p class="fw-semibold mb-1">{{ $prescricao->nome_medicamento }}</p>
              <small class="text-muted d-block">
                <i class="mdi mdi-pill me-1"></i>{{ $prescricao->dosagem }}
                @if($prescricao->frequencia) · {{ $prescricao->frequencia }} @endif
              </small>
              @if($prescricao->duracao)
              <small class="text-muted d-block">
                <i class="mdi mdi-clock-outline me-1"></i>{{ $prescricao->duracao }}
              </small>
              @endif
              @if($prescricao->observacao)
                <small class="text-muted fst-italic d-block mt-1">{{ $prescricao->observacao }}</small>
              @endif
            </div>
            {{-- DT-03: PrescricaoPolicy — admin sempre pode; outros: só autor com atendimento aberto --}}
            @can('update', $prescricao)
            <div class="ms-3 flex-shrink-0">
              <a href="/editar-prescricao/{{ $prescricao->id }}" class="btn btn-xs btn-outline-secondary">
                <i class="mdi mdi-pencil-outline me-1"></i>Editar
              </a>
            </div>
            @endcan
          </div>
          @empty
          <div class="p-4 text-center text-muted">
            <i class="mdi mdi-pill-off mdi-36px d-block mb-2 text-muted opacity-50"></i>
            <p class="mb-0 small">Nenhuma prescrição emitida nesta consulta.</p>
            @if($atendAberto && Auth::user()->nivelAcesso() <= 3)
            <a href="/cadastro-prescricao?consulta_id={{ $consulta->id }}" class="btn btn-sm btn-outline-primary mt-2">
              <i class="mdi mdi-plus me-1"></i>Emitir Prescrição
            </a>
            @endif
          </div>
          @endforelse
        </div>
      </div>

    </div>
  </div>
</div>

@endsection
