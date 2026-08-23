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
    // No-show (DEC-2): falta distinta de cancelamento
    'nao_compareceu' => ['label' => 'Não compareceu', 'class' => 'bg-label-secondary'],
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
  {{-- HERO degradê verde (padrão visual do projeto)                     --}}
  {{-- ================================================================ --}}
  <div class="card mb-4 text-white border-0"
       style="background: linear-gradient(105deg, #237030 0%, #3DAA4A 50%, #56cf66 100%);">
    <div class="card-body py-4 px-4 position-relative overflow-hidden">
      <div class="position-absolute rounded-circle"
           style="right:-60px; top:-60px; width:200px; height:200px; border:40px solid rgba(255,255,255,0.07); pointer-events:none;"></div>

      <div class="d-flex flex-wrap align-items-center gap-3 position-relative">

        {{-- Iniciais do paciente no box translúcido (padrão dos heros) --}}
        <div class="flex-shrink-0 rounded-3 d-flex align-items-center justify-content-center fw-bold"
             style="width:56px; height:56px; background:rgba(255,255,255,0.18); font-size:1.3rem;">
          {{ $iniciais }}
        </div>

        {{-- Dados do agendamento --}}
        <div class="flex-grow-1 min-width-0">
          <div class="d-flex flex-wrap align-items-center gap-2">
            <h4 class="mb-0 fw-bold text-white">{{ $agendamento->paciente->nome ?? '-' }}</h4>
            <span class="badge rounded-pill" style="background:rgba(255,255,255,.22); color:#fff;">
              {{ $statusInfo['label'] }}
            </span>
          </div>
          <div class="d-flex flex-wrap gap-3 mt-1" style="font-size:13px; opacity:.92;">
            <span><i class="mdi mdi-calendar-outline me-1"></i>{{ $agendamento->data_hora->format('d/m/Y H:i') }}</span>
            <span><i class="mdi mdi-stethoscope me-1"></i>{{ $agendamento->profissional->nome ?? '-' }}</span>
            <span><i class="mdi mdi-clipboard-text-outline me-1"></i>{{ $agendamento->tipo }}</span>
          </div>
        </div>

        {{-- Botões de ação por status: ação primária em branco, secundárias em outline --}}
        <div class="d-flex flex-column flex-sm-row gap-2 ms-auto">

          @if($agendamento->isPendente())
            {{-- Confirmar --}}
            <form action="/agendamentos/{{ $agendamento->id }}/confirmar" method="POST">
              @csrf @method('PATCH')
              <button type="submit" class="btn btn-sm w-100 fw-semibold"
                      style="background:#fff; color:#237030; border:none;">
                <i class="mdi mdi-check-circle-outline me-1"></i>Confirmar
              </button>
            </form>

          @elseif($agendamento->isConfirmado())
            {{-- Realizar → E3 (container): abre o atendimento e cai na tela dele com o form pronto --}}
            <form action="/agendamentos/{{ $agendamento->id }}/realizar" method="POST">
              @csrf @method('PATCH')
              <button type="submit" class="btn btn-sm w-100 fw-semibold"
                      style="background:#fff; color:#237030; border:none;">
                <i class="mdi mdi-stethoscope me-1"></i>Realizar
              </button>
            </form>
            {{-- No-show (DEC-2): paciente não compareceu → fecha sem consulta, com justificativa --}}
            <button type="button" class="btn btn-outline-light btn-sm"
                    data-bs-toggle="modal" data-bs-target="#modalNaoCompareceu">
              <i class="mdi mdi-calendar-remove-outline me-1"></i>Não compareceu
            </button>

          @elseif($agendamento->isRealizado() && $agendamento->atendimento)
            {{-- Realizado: atalho para o atendimento que nasceu deste agendamento --}}
            <a href="/atendimentos/{{ $agendamento->atendimento->id }}"
               class="btn btn-sm fw-semibold" style="background:#fff; color:#237030; border:none;">
              <i class="mdi mdi-folder-open-outline me-1"></i>Abrir Atendimento
            </a>
          @endif

          @if(!$agendamento->isRealizado() && !$agendamento->isCancelado() && !$agendamento->isNaoCompareceu())
            {{-- Cancelar (abre modal) --}}
            <button type="button" class="btn btn-outline-light btn-sm"
                    data-bs-toggle="modal" data-bs-target="#modalCancelar">
              <i class="mdi mdi-cancel me-1"></i>Cancelar
            </button>
          @endif

          <a href="/agendamentos" class="btn btn-outline-light btn-sm">
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

            {{-- No-show (DEC-2): a justificativa da falta fica em motivo_cancelamento --}}
            @if($agendamento->isNaoCompareceu())
            <div class="col-12">
              <p class="text-muted small mb-1">Justificativa da falta</p>
              <p class="fw-semibold mb-0 text-warning">{{ $agendamento->motivo_cancelamento ?? '-' }}</p>
              <small class="text-muted">
                Registrada por {{ $agendamento->canceladoPor->name ?? '-' }}
                @if($agendamento->cancelado_em)
                  em {{ $agendamento->cancelado_em->format('d/m/Y H:i') }}
                @endif
              </small>
            </div>
            @endif

            @if($agendamento->isRealizado() && $agendamento->atendimento)
            <div class="col-12">
              <p class="text-muted small mb-1">Atendimento vinculado</p>
              <a href="/atendimentos/{{ $agendamento->atendimento->id }}" class="fw-semibold">
                <i class="mdi mdi-folder-open-outline me-1"></i>Abrir atendimento #{{ $agendamento->atendimento->id }}
              </a>
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
{{-- No-show (DEC-2): registrar que o paciente faltou, com justificativa --}}
<div class="modal fade" id="modalNaoCompareceu" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="/agendamentos/{{ $agendamento->id }}/nao-compareceu" method="POST">
        @csrf @method('PATCH')
        <div class="modal-header">
          <h5 class="modal-title text-warning"><i class="mdi mdi-calendar-remove-outline me-2"></i>Paciente não compareceu</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <p class="mb-3">Registrar que <strong>{{ $agendamento->paciente->nome ?? 'o paciente' }}</strong> não compareceu. O agendamento é fechado <strong>sem criar consulta</strong> e o horário é liberado.</p>
          <div class="form-floating form-floating-outline">
            <textarea name="justificativa" id="justificativa" class="form-control @error('justificativa') is-invalid @enderror"
                      style="height:100px" maxlength="500" placeholder="Ex.: não compareceu e não avisou" required>{{ old('justificativa') }}</textarea>
            <label for="justificativa">Justificativa *</label>
            @error('justificativa')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Voltar</button>
          <button type="submit" class="btn btn-warning"><i class="mdi mdi-check me-1"></i>Confirmar falta</button>
        </div>
      </form>
    </div>
  </div>
</div>

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
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Voltar</button>
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
