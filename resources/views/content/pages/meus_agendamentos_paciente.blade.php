@php
$configData = Helper::appClasses();
$statusMap = [
    'pendente'   => ['label' => 'Pendente',   'class' => 'bg-label-warning'],
    'confirmado' => ['label' => 'Confirmado',  'class' => 'bg-label-primary'],
    'realizado'  => ['label' => 'Realizado',   'class' => 'bg-label-success'],
    'cancelado'  => ['label' => 'Cancelado',   'class' => 'bg-label-danger'],
];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Meus Agendamentos')

{{-- Breadcrumb: Início > Meus Agendamentos --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',             'url' => '/'],
      ['label' => 'Meus Agendamentos',  'url' => null],
    ]
  ])
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Header --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h4 class="mb-0">Meus Agendamentos</h4>
      <p class="text-muted small mb-0 mt-1">Consulte e gerencie seus agendamentos</p>
    </div>
    <a href="/agendar-consulta" class="btn btn-primary">
      <i class="mdi mdi-calendar-plus me-1"></i>Agendar Consulta
    </a>
  </div>

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

  {{-- Lista de agendamentos --}}
  @if($agendamentos->isEmpty())
    <div class="card">
      <div class="card-body text-center py-5">
        <i class="mdi mdi-calendar-check-outline mdi-48px d-block mb-3 text-muted opacity-50"></i>
        <h5 class="text-muted">Nenhum agendamento encontrado</h5>
        <p class="text-muted small mb-4">Você ainda não possui agendamentos registrados.</p>
        <a href="/agendar-consulta" class="btn btn-primary">
          <i class="mdi mdi-calendar-plus me-1"></i>Agendar minha primeira consulta
        </a>
      </div>
    </div>
  @else
    <div class="row g-3">
      @foreach($agendamentos as $ag)
      @php
        $info     = $statusMap[$ag->status] ?? ['label' => $ag->status, 'class' => 'bg-label-secondary'];
        $podeCancelar = !$ag->isRealizado() && !$ag->isCancelado() && $ag->data_hora->isFuture();
      @endphp
      <div class="col-12 col-sm-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body">

            {{-- Status badge --}}
            <div class="d-flex justify-content-between align-items-start mb-3">
              <span class="badge rounded-pill {{ $info['class'] }}">{{ $info['label'] }}</span>
              <small class="text-muted">{{ $ag->data_hora->format('d/m/Y') }}</small>
            </div>

            {{-- Dados principais --}}
            <h6 class="fw-semibold mb-1">{{ $ag->profissional->nome ?? '-' }}</h6>
            <p class="text-muted small mb-1">
              <i class="mdi mdi-stethoscope me-1"></i>{{ $ag->tipo }}
            </p>
            <p class="text-muted small mb-3">
              <i class="mdi mdi-clock-outline me-1"></i>{{ $ag->data_hora->format('H:i') }}
              @if($ag->profissional?->especialidade)
                &nbsp;·&nbsp;{{ $ag->profissional->especialidade }}
              @endif
            </p>

            {{-- Botão cancelar (se possível) --}}
            @if($podeCancelar)
            <form action="/meus-agendamentos/{{ $ag->id }}/cancelar" method="POST"
                  onsubmit="return confirm('Confirmar cancelamento deste agendamento?')">
              @csrf @method('PATCH')
              <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                <i class="mdi mdi-cancel me-1"></i>Cancelar Agendamento
              </button>
            </form>
            @endif

          </div>
        </div>
      </div>
      @endforeach
    </div>

    {{-- Paginação --}}
    <div class="mt-4">
      {{ $agendamentos->links() }}
    </div>
  @endif

</div>
@endsection
