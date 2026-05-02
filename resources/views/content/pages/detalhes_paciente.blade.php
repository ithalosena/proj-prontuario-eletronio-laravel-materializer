@php
$configData = Helper::appClasses();
$iniciais   = collect(explode(' ', $paciente->nome ?? 'P'))
    ->filter()->map(fn($p) => strtoupper($p[0]))->take(2)->implode('');
$idade      = $paciente->data_nascimento
    ? \Carbon\Carbon::parse($paciente->data_nascimento)->age
    : null;
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Perfil do Paciente')

{{-- Breadcrumb: Início > Pacientes > Nome do Paciente --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',           'url' => '/'],
      ['label' => 'Pacientes',        'url' => '/pacientes'],
      ['label' => $paciente->nome,    'url' => null],
    ]
  ])
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================
       HERO — Avatar, nome, curso e badges de identificação
       ================================================================ --}}
  <div class="card mb-4">
    <div class="card-body py-4">
      <div class="d-flex flex-wrap align-items-center gap-4">

        {{-- Avatar com iniciais --}}
        <div class="flex-shrink-0">
          <div class="avatar avatar-xl">
            <span class="avatar-initial rounded-circle bg-label-primary"
                  style="font-size:1.4rem; width:64px; height:64px; display:flex; align-items:center; justify-content:center;">
              {{ $iniciais }}
            </span>
          </div>
        </div>

        {{-- Dados de identificação --}}
        <div class="flex-grow-1">
          <h4 class="mb-1">{{ $paciente->nome }}</h4>
          <div class="d-flex flex-wrap gap-3 text-muted small mb-2">
            @if($paciente->matricula)
              <span><i class="mdi mdi-card-account-details-outline me-1"></i>{{ $paciente->matricula }}</span>
            @endif
            @if($paciente->curso)
              <span><i class="mdi mdi-school-outline me-1"></i>{{ $paciente->curso }}</span>
            @endif
            @if($idade !== null)
              <span><i class="mdi mdi-cake-variant-outline me-1"></i>{{ $idade }} anos</span>
            @endif
          </div>
          <div class="d-flex flex-wrap gap-2">
            @if($paciente->sexo === 'M')
              <span class="badge rounded-pill bg-label-info">Masculino</span>
            @elseif($paciente->sexo === 'F')
              <span class="badge rounded-pill bg-label-danger">Feminino</span>
            @else
              <span class="badge rounded-pill bg-label-secondary">{{ $paciente->sexo ?? 'Não informado' }}</span>
            @endif
          </div>
        </div>

        {{-- Botões de ação no hero --}}
        <div class="d-flex flex-column gap-2 ms-auto">
          <a href="/pacientes/{{ $paciente->id }}/historico" class="btn btn-outline-primary btn-sm">
            <i class="mdi mdi-history me-1"></i>Ver Histórico Completo
          </a>
          @if(Auth::user()->nivelAcesso() <= 2)
          <a href="/editar-paciente/{{ $paciente->id }}" class="btn btn-outline-secondary btn-sm">
            <i class="mdi mdi-pencil-outline me-1"></i>Editar Paciente
          </a>
          @endif
          <a href="{{ url()->previous('/pacientes') }}" class="btn btn-default btn-sm">
            <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
          </a>
        </div>

      </div>
    </div>
  </div>

  <div class="row">

    {{-- ================================================================
         Coluna esquerda: Dados Pessoais
         ================================================================ --}}
    <div class="col-md-7">
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="mdi mdi-account-outline me-2"></i>Dados Pessoais</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">

            <div class="col-sm-6">
              <p class="text-muted small mb-1">CPF / Documento</p>
              <p class="fw-semibold mb-0">{{ $paciente->documento ?? '-' }}</p>
            </div>

            <div class="col-sm-6">
              <p class="text-muted small mb-1">Data de Nascimento</p>
              <p class="fw-semibold mb-0">
                {{ $paciente->data_nascimento
                    ? \Carbon\Carbon::parse($paciente->data_nascimento)->format('d/m/Y')
                    : '-' }}
              </p>
            </div>

            <div class="col-sm-6">
              <p class="text-muted small mb-1">Contato</p>
              <p class="fw-semibold mb-0">{{ $paciente->contato ?? '-' }}</p>
            </div>

            <div class="col-sm-6">
              <p class="text-muted small mb-1">E-mail</p>
              <p class="fw-semibold mb-0">{{ $paciente->user->email ?? '-' }}</p>
            </div>

            <div class="col-12">
              <p class="text-muted small mb-1">Endereço</p>
              <p class="fw-semibold mb-0">{{ $paciente->endereco ?? '-' }}</p>
            </div>

            <div class="col-sm-6">
              <p class="text-muted small mb-1">Matrícula</p>
              <p class="fw-semibold mb-0">{{ $paciente->matricula ?? '-' }}</p>
            </div>

            <div class="col-sm-6">
              <p class="text-muted small mb-1">Curso</p>
              <p class="fw-semibold mb-0">{{ $paciente->curso ?? '-' }}</p>
            </div>

          </div>
        </div>
      </div>
    </div>

    {{-- ================================================================
         Coluna direita: Estatísticas de saúde
         ================================================================ --}}
    <div class="col-md-5">
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="mdi mdi-chart-box-outline me-2"></i>Estatísticas</h5>
        </div>
        <div class="card-body">

          <div class="d-flex align-items-center justify-content-between p-3 bg-label-primary rounded mb-3">
            <div>
              <p class="text-muted small mb-0">Total de Consultas</p>
              <h3 class="mb-0">{{ $totalConsultas }}</h3>
            </div>
            <i class="mdi mdi-stethoscope mdi-36px text-primary"></i>
          </div>

          <div class="d-flex align-items-center justify-content-between p-3 bg-label-warning rounded mb-3">
            <div>
              <p class="text-muted small mb-0">Total de Exames</p>
              <h3 class="mb-0">{{ $totalExames }}</h3>
            </div>
            <i class="mdi mdi-test-tube-outline mdi-36px text-warning"></i>
          </div>

          <div class="d-flex align-items-center justify-content-between p-3 bg-label-success rounded">
            <div>
              <p class="text-muted small mb-0">Total de Prescrições</p>
              <h3 class="mb-0">{{ $totalPrescricoes }}</h3>
            </div>
            <i class="mdi mdi-pill mdi-36px text-success"></i>
          </div>

        </div>
      </div>
    </div>

  </div>

</div>

@endsection