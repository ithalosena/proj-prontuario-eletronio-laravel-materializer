@php
$configData = Helper::appClasses();
$statusMap = [
    'pendente'   => ['label' => 'Pendente',   'class' => 'bg-label-warning'],
    'confirmado' => ['label' => 'Confirmado',  'class' => 'bg-label-info'],
    'realizado'  => ['label' => 'Realizado',   'class' => 'bg-label-success'],
    'cancelado'  => ['label' => 'Cancelado',   'class' => 'bg-label-danger'],
];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Meus Agendamentos')

@section('page-style')
<style>
  /* Pills ativas em verde (padrão do paciente), no lugar do roxo do Materialize */
  #ag-filtros .nav-link.active {
    background-color: #3DAA4A !important;
    color: #fff !important;
  }
  #ag-filtros .nav-link {
    color: #237030;
  }
</style>
@endsection

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

  {{-- Hero verde (padrão do paciente) --}}
  <div class="card mb-4 text-white border-0"
       style="background: linear-gradient(105deg, #237030 0%, #3DAA4A 50%, #56cf66 100%);">
    <div class="card-body py-4 px-4 position-relative overflow-hidden">
      <div class="position-absolute rounded-circle"
           style="right:-60px; top:-60px; width:200px; height:200px; border:40px solid rgba(255,255,255,0.07); pointer-events:none;"></div>
      <div class="d-flex flex-wrap align-items-center gap-3 position-relative">
        <div class="flex-shrink-0 rounded-3 d-flex align-items-center justify-content-center"
             style="width:56px; height:56px; background:rgba(255,255,255,0.18);">
          <i class="mdi mdi-calendar-check mdi-36px"></i>
        </div>
        <div class="flex-grow-1 min-width-0">
          <h4 class="mb-0 fw-bold text-white">Meus Agendamentos</h4>
          <p class="mb-0" style="font-size:13px; opacity:.92;">Consulte e gerencie seus agendamentos</p>
        </div>
        <a href="/agendar-consulta" class="btn btn-sm fw-semibold flex-shrink-0"
           style="background:#fff; color:#237030; border:none;">
          <i class="mdi mdi-calendar-plus me-1"></i>Agendar Consulta
        </a>
      </div>
    </div>
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

  {{-- C.6.3 (v0.10.3): filtros por status (pills) + ordenação (GET preservável) --}}
  <div id="ag-filtros" class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <ul class="nav nav-pills gap-1 flex-wrap mb-0">
      @php $statusTabs = ['' => 'Todos', 'pendente' => 'Pendentes', 'confirmado' => 'Confirmados', 'realizado' => 'Realizados', 'cancelado' => 'Cancelados']; @endphp
      @foreach($statusTabs as $val => $lbl)
        @php
          $q = http_build_query(array_filter(['status' => $val, 'ordenar' => $ordenar]));
          $href = '/meus-agendamentos' . ($q ? '?' . $q : '');
        @endphp
        <li class="nav-item">
          <a class="nav-link py-1 px-3 {{ (string)($filtroStatus ?? '') === (string)$val ? 'active' : '' }}" href="{{ $href }}">{{ $lbl }}</a>
        </li>
      @endforeach
    </ul>
    <form method="GET" action="/meus-agendamentos" class="d-flex align-items-center gap-2">
      @if($filtroStatus)<input type="hidden" name="status" value="{{ $filtroStatus }}">@endif
      <label class="form-label mb-0 small text-muted text-nowrap" for="ordenar">Ordenar:</label>
      <select name="ordenar" id="ordenar" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
        <option value="data_desc" {{ $ordenar === 'data_desc' ? 'selected' : '' }}>Mais recentes</option>
        <option value="data_asc"  {{ $ordenar === 'data_asc'  ? 'selected' : '' }}>Mais antigos</option>
      </select>
    </form>
  </div>

  {{-- Lista de agendamentos --}}
  @if($agendamentos->isEmpty())
    <div class="card">
      <div class="card-body text-center py-5">
        <i class="mdi mdi-calendar-check-outline mdi-48px d-block mb-3 text-muted opacity-50"></i>
        @if($filtroStatus)
          <h5 class="text-muted">Nenhum agendamento neste filtro</h5>
          <p class="text-muted small mb-4">Não há agendamentos com o status selecionado.</p>
          <a href="/meus-agendamentos" class="btn btn-outline-secondary">
            <i class="mdi mdi-close me-1"></i>Limpar filtro
          </a>
        @else
          <h5 class="text-muted">Nenhum agendamento encontrado</h5>
          <p class="text-muted small mb-4">Você ainda não possui agendamentos registrados.</p>
          <a href="/agendar-consulta" class="btn text-white fw-semibold" style="background:#3DAA4A; border:none;">
            <i class="mdi mdi-calendar-plus me-1"></i>Agendar minha primeira consulta
          </a>
        @endif
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
