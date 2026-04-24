@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Histórico do Paciente')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Header --}}
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <div>
            <h3 class="align-text-bottom-2 mb-0">Histórico do Paciente</h3>
            <small class="text-muted">{{ $paciente->nome }} — {{ $paciente->matricula ?? 'Sem matrícula' }} | {{ $paciente->curso ?? '-' }}</small>
          </div>
          <div class="card-header-elements ms-auto mt-2 mb-1 me-2 d-flex gap-2">
            <a href="/cadastro-consulta" class="btn btn-primary">
              <i class="mdi mdi-plus me-1"></i>Nova Consulta
            </a>
            <a href="{{ url()->previous('/pacientes') }}" class="btn btn-default">
              <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
            </a>
          </div>
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

  {{-- Dados resumidos do paciente --}}
  <div class="card mb-4">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3 mb-2 mb-md-0">
          <p class="text-muted small mb-1">Data de Nascimento</p>
          <p class="fw-semibold mb-0">{{ $paciente->data_nascimento ? \Carbon\Carbon::parse($paciente->data_nascimento)->format('d/m/Y') : '-' }}</p>
        </div>
        <div class="col-md-2 mb-2 mb-md-0">
          <p class="text-muted small mb-1">Sexo</p>
          <p class="fw-semibold mb-0">{{ $paciente->sexo ?? '-' }}</p>
        </div>
        <div class="col-md-3 mb-2 mb-md-0">
          <p class="text-muted small mb-1">Contato</p>
          <p class="fw-semibold mb-0">{{ $paciente->contato ?? '-' }}</p>
        </div>
        <div class="col-md-4 mb-2 mb-md-0">
          <p class="text-muted small mb-1">Documento</p>
          <p class="fw-semibold mb-0">{{ $paciente->documento ?? '-' }}</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Contador de consultas --}}
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">
      <i class="mdi mdi-history me-1"></i>
      Consultas Registradas
      <span class="badge bg-label-primary ms-1">{{ $consultas->count() }}</span>
    </h5>
  </div>

  {{-- Lista de consultas --}}
  @forelse($consultas as $consulta)
  <div class="card mb-3">
    <div class="card-header d-flex align-items-center justify-content-between py-3">
      <div>
        <span class="fw-semibold">{{ $consulta->data_hora->format('d/m/Y \à\s H:i') }}</span>
        <span class="badge rounded-pill bg-label-primary ms-2">{{ $consulta->tipo }}</span>
      </div>
      <div class="d-flex align-items-center gap-2">
        <small class="text-muted">{{ $consulta->profissional->nome ?? '-' }}</small>
        <a href="/consultas/{{ $consulta->id }}" class="btn btn-sm btn-outline-primary">
          <i class="mdi mdi-file-document-outline me-1"></i>Ver Prontuário
        </a>
      </div>
    </div>
    <div class="card-body">
      <div class="row">

        {{-- Registro clínico --}}
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

        {{-- Exames e Prescrições --}}
        <div class="col-md-6">

          {{-- Exames --}}
          @if($consulta->exames->count() > 0)
          <div class="mb-3">
            <p class="text-muted small mb-2"><i class="mdi mdi-test-tube me-1"></i>Exames ({{ $consulta->exames->count() }})</p>
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

          {{-- Prescrições --}}
          @if($consulta->prescricoes->count() > 0)
          <div class="mb-0">
            <p class="text-muted small mb-2"><i class="mdi mdi-pill me-1"></i>Prescrições ({{ $consulta->prescricoes->count() }})</p>
            @foreach($consulta->prescricoes as $prescricao)
            <div class="mb-1">
              <span class="small fw-semibold">{{ $prescricao->nome_medicamento }}</span>
              <span class="small text-muted"> — {{ $prescricao->dosagem }}, {{ $prescricao->frequencia }}</span>
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
  </div>
  @empty
  <div class="card">
    <div class="card-body text-center text-muted py-5">
      <i class="mdi mdi-calendar-remove mdi-48px d-block mb-2"></i>
      <p class="mb-0">Nenhuma consulta registrada para este paciente.</p>
      <a href="/cadastro-consulta" class="btn btn-primary mt-3">Registrar primeira consulta</a>
    </div>
  </div>
  @endforelse

</div>

@endsection
