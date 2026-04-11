@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Prontuário da Consulta')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Header --}}
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <div>
            <h3 class="align-text-bottom-2 mb-0">Prontuário da Consulta</h3>
            <small class="text-muted">{{ $consulta->data_hora->format('d/m/Y \à\s H:i') }}</small>
          </div>
          <div class="card-header-elements ms-auto mt-2 mb-1 me-2 d-flex gap-2">
            <a href="/editar-consulta/{{ $consulta->id }}" class="btn btn-outline-primary">
              <i class="mdi mdi-pencil-outline me-1"></i>Editar
            </a>
            <a href="/consultas" class="btn btn-default">
              <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">

    {{-- Coluna principal --}}
    <div class="col-md-8">

      {{-- Seção 1: Identificação --}}
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="mdi mdi-account-details-outline me-2"></i>Identificação</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <p class="text-muted small mb-1">Paciente</p>
              <p class="fw-semibold mb-0">{{ $consulta->paciente->nome ?? '-' }}</p>
              <small class="text-muted">Matrícula: {{ $consulta->paciente->matricula ?? '-' }} | {{ $consulta->paciente->curso ?? '-' }}</small>
            </div>
            <div class="col-md-6 mb-3">
              <p class="text-muted small mb-1">Profissional Responsável</p>
              <p class="fw-semibold mb-0">{{ $consulta->profissional->nome ?? '-' }}</p>
              <small class="text-muted">{{ $consulta->profissional->especialidade ?? '-' }} | {{ $consulta->profissional->registro_profissional ?? '-' }}</small>
            </div>
            <div class="col-md-6 mb-0">
              <p class="text-muted small mb-1">Data e Hora</p>
              <p class="fw-semibold mb-0">{{ $consulta->data_hora->format('d/m/Y \à\s H:i') }}</p>
            </div>
            <div class="col-md-6 mb-0">
              <p class="text-muted small mb-1">Tipo de Atendimento</p>
              <span class="badge rounded-pill bg-label-primary fs-6">{{ $consulta->tipo }}</span>
            </div>
          </div>
        </div>
      </div>

      {{-- Seção 2: Registro Clínico --}}
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="mdi mdi-stethoscope me-2"></i>Registro Clínico</h5>
        </div>
        <div class="card-body">

          <div class="mb-4">
            <p class="text-muted small mb-1">Queixa Principal</p>
            <p class="mb-0">{{ $consulta->queixa ?? '-' }}</p>
          </div>

          <div class="mb-4">
            <p class="text-muted small mb-1">Anamnese</p>
            @if($consulta->anamnese)
              <p class="mb-0">{{ $consulta->anamnese }}</p>
            @else
              <p class="text-muted fst-italic mb-0">Não registrado.</p>
            @endif
          </div>

          <div class="mb-4">
            <p class="text-muted small mb-1">Diagnóstico</p>
            @if($consulta->diagnostico)
              <p class="mb-0">{{ $consulta->diagnostico }}</p>
            @else
              <p class="text-muted fst-italic mb-0">Não registrado.</p>
            @endif
          </div>

          <div class="mb-0">
            <p class="text-muted small mb-1">Conduta</p>
            @if($consulta->conduta)
              <p class="mb-0">{{ $consulta->conduta }}</p>
            @else
              <p class="text-muted fst-italic mb-0">Não registrado.</p>
            @endif
          </div>

        </div>
      </div>

    </div>

    {{-- Coluna lateral: Exames e Prescrições --}}
    <div class="col-md-4">

      {{-- Exames --}}
      <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0"><i class="mdi mdi-test-tube me-2"></i>Exames</h5>
          <a href="/cadastro-exame?consulta_id={{ $consulta->id }}" class="btn btn-sm btn-primary">
            <i class="mdi mdi-plus me-1"></i>Novo
          </a>
        </div>
        <div class="card-body p-0">
          @forelse($consulta->exames as $exame)
          <div class="d-flex align-items-start p-3 border-bottom">
            <div class="flex-grow-1">
              <p class="fw-semibold mb-1">{{ $exame->tipo }}</p>
              @if($exame->observacao)
                <small class="text-muted d-block">{{ $exame->observacao }}</small>
              @endif
              <small class="text-muted">Solicitado: {{ $exame->data_solicitacao ? $exame->data_solicitacao->format('d/m/Y') : '-' }}</small>
            </div>
            <div class="ms-2 text-end">
              @if($exame->resultado)
                <span class="badge bg-label-success">Com resultado</span>
              @else
                <span class="badge bg-label-warning">Pendente</span>
              @endif
              <div class="mt-1">
                <a href="/editar-exame/{{ $exame->id }}" class="text-muted" title="Editar">
                  <i class="mdi mdi-pencil-outline"></i>
                </a>
              </div>
            </div>
          </div>
          @empty
          <div class="p-3 text-center text-muted">
            <i class="mdi mdi-test-tube-empty mdi-24px d-block mb-1"></i>
            <small>Nenhum exame solicitado.</small>
          </div>
          @endforelse
        </div>
      </div>

      {{-- Prescrições --}}
      <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0"><i class="mdi mdi-pill me-2"></i>Prescrições</h5>
          <a href="/cadastro-prescricao?consulta_id={{ $consulta->id }}" class="btn btn-sm btn-primary">
            <i class="mdi mdi-plus me-1"></i>Nova
          </a>
        </div>
        <div class="card-body p-0">
          @forelse($consulta->prescricoes as $prescricao)
          <div class="d-flex align-items-start p-3 border-bottom">
            <div class="flex-grow-1">
              <p class="fw-semibold mb-1">{{ $prescricao->nome_medicamento }}</p>
              <small class="text-muted d-block">{{ $prescricao->dosagem }} — {{ $prescricao->frequencia }}</small>
              <small class="text-muted d-block">Duração: {{ $prescricao->duracao }}</small>
              @if($prescricao->observacao)
                <small class="text-muted fst-italic d-block">{{ $prescricao->observacao }}</small>
              @endif
            </div>
            <div class="ms-2">
              <a href="/editar-prescricao/{{ $prescricao->id }}" class="text-muted" title="Editar">
                <i class="mdi mdi-pencil-outline"></i>
              </a>
            </div>
          </div>
          @empty
          <div class="p-3 text-center text-muted">
            <i class="mdi mdi-pill-off mdi-24px d-block mb-1"></i>
            <small>Nenhuma prescrição emitida.</small>
          </div>
          @endforelse
        </div>
      </div>

    </div>
  </div>
</div>

@endsection
