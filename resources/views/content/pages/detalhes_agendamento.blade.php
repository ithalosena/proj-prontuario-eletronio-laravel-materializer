@php
$configData = Helper::appClasses();
$iniciais   = collect(explode(' ', $agendamento->paciente->nome ?? 'P'))
    ->filter()->map(fn($p) => strtoupper($p[0]))->take(2)->implode('');

// Badge de status
$statusMap = [
    'pendente'   => ['label' => 'Pendente',   'class' => 'bg-label-warning'],
    'confirmado' => ['label' => 'Confirmado',  'class' => 'bg-label-primary'],
    'realizado'  => ['label' => 'Realizado',   'class' => 'bg-label-success'],
    'cancelado'  => ['label' => 'Cancelado',   'class' => 'bg-label-danger'],
];
$statusInfo = $statusMap[$agendamento->status] ?? ['label' => $agendamento->status, 'class' => 'bg-label-secondary'];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Agendamento #' . $agendamento->id)

{{-- Breadcrumb: Início > Agendamentos > #ID --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',                    'url' => '/'],
      ['label' => 'Agendamentos',              'url' => '/agendamentos'],
      ['label' => 'Agendamento #' . $agendamento->id, 'url' => null],
    ]
  ])
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Flash messages --}}
  @if(session('success'))
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
  </div>
  @endif
  @if(session('error'))
  <div class="alert alert-danger alert-dismissible mb-4" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
  </div>
  @endif

  {{-- ================================================================ --}}
  {{-- HERO CARD                                                          --}}
  {{-- ================================================================ --}}
  <div class="card mb-4">
    <div class="card-body py-4">
      <div class="d-flex flex-wrap align-items-center gap-4">

        {{-- Avatar com iniciais do paciente --}}
        <div class="flex-shrink-0">
          <div class="avatar avatar-xl">
            <span class="avatar-initial rounded-circle bg-label-primary"
                  style="font-size:1.4rem; width:64px; height:64px; display:flex; align-items:center; justify-content:center;">
              {{ $iniciais }}
            </span>
          </div>
        </div>

        {{-- Dados do agendamento --}}
        <div class="flex-grow-1">
          <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <h4 class="mb-0">{{ $agendamento->paciente->nome ?? '-' }}</h4>
            <span class="badge rounded-pill {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
          </div>
          <div class="d-flex flex-wrap gap-3 text-muted small">
            <span><i class="mdi mdi-calendar-outline me-1"></i>{{ $agendamento->data_hora->format('d/m/Y H:i') }}</span>
            <span><i class="mdi mdi-stethoscope me-1"></i>{{ $agendamento->profissional->nome ?? '-' }}</span>
            <span><i class="mdi mdi-clipboard-text-outline me-1"></i>{{ $agendamento->tipo }}</span>
          </div>
        </div>

        {{-- Botões de ação por status --}}
        <div class="d-flex flex-column flex-sm-row gap-2 ms-auto">

          @if($agendamento->isPendente())
            {{-- Confirmar --}}
            <form action="/agendamentos/{{ $agendamento->id }}/confirmar" method="POST">
              @csrf @method('PATCH')
              <button type="submit" class="btn btn-primary btn-sm w-100">
                <i class="mdi mdi-check-circle-outline me-1"></i>Confirmar
              </button>
            </form>

          @elseif($agendamento->isConfirmado())
            {{-- Realizar → redireciona para criar consulta --}}
            <form action="/agendamentos/{{ $agendamento->id }}/realizar" method="POST">
              @csrf @method('PATCH')
              <button type="submit" class="btn btn-success btn-sm w-100">
                <i class="mdi mdi-stethoscope me-1"></i>Realizar
              </button>
            </form>
          @endif

          @if(!$agendamento->isRealizado() && !$agendamento->isCancelado())
            {{-- Cancelar (abre modal) --}}
            <button type="button" class="btn btn-outline-danger btn-sm"
                    data-bs-toggle="modal" data-bs-target="#modalCancelar">
              <i class="mdi mdi-cancel me-1"></i>Cancelar
            </button>
          @endif

          <a href="/agendamentos" class="btn btn-default btn-sm">
            <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
          </a>

        </div>

      </div>
    </div>
  </div>

  <div class="row">

    {{-- ================================================================ --}}
    {{-- Dados do Agendamento                                              --}}
    {{-- ================================================================ --}}
    <div class="col-md-7">
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="mdi mdi-calendar-clock me-2"></i>Dados do Agendamento</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">

            <div class="col-sm-6">
              <p class="text-muted small mb-1">Data e Hora</p>
              <p class="fw-semibold mb-0">{{ $agendamento->data_hora->format('d/m/Y H:i') }}</p>
            </div>

            <div class="col-sm-6">
              <p class="text-muted small mb-1">Tipo</p>
              <p class="fw-semibold mb-0">{{ $agendamento->tipo }}</p>
            </div>

            <div class="col-sm-6">
              <p class="text-muted small mb-1">Profissional</p>
              <p class="fw-semibold mb-0">
                {{ $agendamento->profissional->nome ?? '-' }}
                @if($agendamento->profissional?->especialidade)
                  <br><small class="text-muted">{{ $agendamento->profissional->especialidade }}</small>
                @endif
              </p>
            </div>

            <div class="col-sm-6">
              <p class="text-muted small mb-1">Criado por</p>
              <p class="fw-semibold mb-0">{{ $agendamento->criadoPor->name ?? '-' }}</p>
            </div>

            @if($agendamento->observacao)
            <div class="col-12">
              <p class="text-muted small mb-1">Observação</p>
              <p class="fw-semibold mb-0">{{ $agendamento->observacao }}</p>
            </div>
            @endif

            @if($agendamento->isCancelado())
            <div class="col-12">
              <p class="text-muted small mb-1">Motivo do Cancelamento</p>
              <p class="fw-semibold mb-0 text-danger">{{ $agendamento->motivo_cancelamento ?? '-' }}</p>
              <small class="text-muted">
                Por {{ $agendamento->canceladoPor->name ?? '-' }}
                @if($agendamento->cancelado_em)
                  em {{ $agendamento->cancelado_em->format('d/m/Y H:i') }}
                @endif
              </small>
            </div>
            @endif

            @if($agendamento->isRealizado() && $agendamento->consulta)
            <div class="col-12">
              <p class="text-muted small mb-1">Consulta vinculada</p>
              <a href="/consultas/{{ $agendamento->consulta_id }}" class="fw-semibold">
                <i class="mdi mdi-stethoscope me-1"></i>Ver consulta #{{ $agendamento->consulta_id }}
              </a>
            </div>
            @endif

          </div>
        </div>
      </div>
    </div>

    {{-- ================================================================ --}}
    {{-- Dados do Paciente                                                 --}}
    {{-- ================================================================ --}}
    <div class="col-md-5">
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="mdi mdi-account-outline me-2"></i>Paciente</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12">
              <p class="text-muted small mb-1">Nome</p>
              <p class="fw-semibold mb-0">{{ $agendamento->paciente->nome ?? '-' }}</p>
            </div>
            <div class="col-sm-6">
              <p class="text-muted small mb-1">Matrícula</p>
              <p class="fw-semibold mb-0">{{ $agendamento->paciente->matricula ?? '-' }}</p>
            </div>
            <div class="col-sm-6">
              <p class="text-muted small mb-1">Curso</p>
              <p class="fw-semibold mb-0">{{ $agendamento->paciente->curso ?? '-' }}</p>
            </div>
            <div class="col-12">
              <p class="text-muted small mb-1">Contato</p>
              <p class="fw-semibold mb-0">{{ $agendamento->paciente->contato ?? '-' }}</p>
            </div>
            <div class="col-12 mt-2">
              <a href="/pacientes/{{ $agendamento->paciente_id }}" class="btn btn-sm btn-outline-primary">
                <i class="mdi mdi-account-details-outline me-1"></i>Ver perfil completo
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

</div>

{{-- ================================================================ --}}
{{-- MODAL: Cancelar Agendamento                                       --}}
{{-- ================================================================ --}}
@if(!$agendamento->isRealizado() && !$agendamento->isCancelado())
<div class="modal fade" id="modalCancelar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="/agendamentos/{{ $agendamento->id }}/cancelar" method="POST">
        @csrf @method('PATCH')
        <div class="modal-header">
          <h5 class="modal-title text-danger"><i class="mdi mdi-cancel me-2"></i>Cancelar Agendamento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted">Informe o motivo do cancelamento. Esta ação não pode ser desfeita.</p>
          <div class="mb-3">
            <label class="form-label">Motivo <span class="text-danger">*</span></label>
            <textarea name="motivo_cancelamento" class="form-control" rows="3"
                      placeholder="Descreva o motivo do cancelamento..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-bs-dismiss="modal">Voltar</button>
          <button type="submit" class="btn btn-danger">
            <i class="mdi mdi-cancel me-1"></i>Confirmar Cancelamento
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

@endsection
