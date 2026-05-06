@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Gestão de Agenda')

{{-- Breadcrumb: Início > Gestão de Agenda --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',          'url' => '/'],
      ['label' => 'Gestão de Agenda','url' => null],
    ]
  ])
@endpush

{{-- FullCalendar CSS --}}
@section('vendor-style')
<link rel="stylesheet" href="{{ asset(mix('assets/vendor/libs/fullcalendar/fullcalendar.css')) }}" />
@endsection

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================ --}}
  {{-- HEADER DA PÁGINA                                                  --}}
  {{-- ================================================================ --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h4 class="mb-0">Gestão de Agenda</h4>
      <p class="text-muted small mb-0 mt-1">Calendário e gerenciamento de agendamentos</p>
    </div>
    <div class="d-flex gap-2">
      <a href="/cadastro-agendamento" class="btn btn-primary">
        <i class="mdi mdi-plus-circle-outline me-1"></i>Novo Agendamento
      </a>
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

  {{-- ================================================================ --}}
  {{-- FILTROS (Etapa 3)                                                 --}}
  {{-- ================================================================ --}}
  <div class="d-flex flex-wrap gap-2 mb-3">
    {{-- Filtro por status — client-side, visível para todos --}}
    <select id="filtro-status" class="form-select form-select-sm" style="width:auto; min-width:170px;">
      <option value="">Todos os status</option>
      <option value="pendente">Pendente</option>
      <option value="confirmado">Confirmado</option>
      <option value="realizado">Realizado</option>
      <option value="cancelado">Cancelado</option>
    </select>

    {{-- Filtro por profissional — server-side, apenas para admin/coordenador --}}
    @if(Auth::user()->nivelAcesso() <= 2)
    <select id="filtro-profissional" class="form-select form-select-sm" style="width:auto; min-width:200px;">
      <option value="">Todos os profissionais</option>
      @foreach($profissionais as $prof)
      <option value="{{ $prof->id }}">{{ $prof->nome }}</option>
      @endforeach
    </select>
    @endif
  </div>

  {{-- ================================================================ --}}
  {{-- CALENDÁRIO FULLCALENDAR                                           --}}
  {{-- ================================================================ --}}
  <div class="card mb-4">

    {{-- Stats inline no header do card --}}
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
      <div class="d-flex flex-wrap gap-3 small">
        <span>
          <i class="mdi mdi-circle me-1" style="color:#fdb528"></i>
          Pendentes: <strong>{{ $pendentes }}</strong>
        </span>
        <span>
          <i class="mdi mdi-circle me-1" style="color:#666cff"></i>
          Confirmados: <strong>{{ $confirmados }}</strong>
        </span>
        <span>
          <i class="mdi mdi-circle me-1" style="color:#72e128"></i>
          Realizados: <strong>{{ $realizados }}</strong>
        </span>
        <span>
          <i class="mdi mdi-circle me-1" style="color:#ff4d49"></i>
          Cancelados: <strong>{{ $cancelados }}</strong>
        </span>
        <span class="text-muted">| Total: <strong>{{ $total }}</strong></span>
      </div>
    </div>

    <div class="card-body p-0">
      <div id="calendar" style="padding: 1rem;"></div>
    </div>

    {{-- Legenda de cores no footer do card --}}
    <div class="card-footer d-flex flex-wrap gap-3 small text-muted py-2">
      <span><i class="mdi mdi-circle" style="color:#fdb528"></i> Pendente</span>
      <span><i class="mdi mdi-circle" style="color:#666cff"></i> Confirmado</span>
      <span><i class="mdi mdi-circle" style="color:#72e128"></i> Realizado</span>
      <span><i class="mdi mdi-circle" style="color:#ff4d49"></i> Cancelado</span>
    </div>

  </div>

</div>

@endsection

{{-- FullCalendar JS (compilado via npm, não CDN) --}}
@section('vendor-script')
<script src="{{ asset(mix('assets/vendor/libs/fullcalendar/fullcalendar.js')) }}"></script>
@endsection

@section('page-script')
<script>
// Calendário de agendamentos (ST-09A + alinhamento visual)
document.addEventListener('DOMContentLoaded', function () {
  var calendarEl = document.getElementById('calendar');
  if (!calendarEl) return;

  // URL base dos eventos — pode receber ?profissional_id= via filtro server-side
  var eventosUrl = '/agendamentos/eventos';

  var calendar = new Calendar(calendarEl, {
    plugins: [dayGridPlugin, timeGridPlugin, listPlugin],
    initialView: 'dayGridMonth',
    locale: 'pt-br',
    headerToolbar: {
      left:   'prev,next today',
      center: 'title',
      right:  'dayGridMonth,timeGridWeek,timeGridDay'
    },
    buttonText: {
      today: 'Hoje',
      month: 'Mês',
      week:  'Semana',
      day:   'Dia',
      list:  'Lista'
    },
    events: eventosUrl,
    eventClick: function (info) {
      window.location.href = info.event.url;
      info.jsEvent.preventDefault();
    },
    noEventsContent: 'Nenhum agendamento neste período.',
    height: 650
  });

  calendar.render();

  // -----------------------------------------------------------------------
  // Filtro de status — client-side (esconde/mostra eventos sem reload)
  // -----------------------------------------------------------------------
  var filtroStatus = document.getElementById('filtro-status');
  if (filtroStatus) {
    filtroStatus.addEventListener('change', function () {
      var statusSelecionado = this.value;
      calendar.getEvents().forEach(function (event) {
        if (!statusSelecionado) {
          event.setProp('display', '');
        } else {
          var statusEvento = event.extendedProps.status;
          event.setProp('display', statusEvento === statusSelecionado ? '' : 'none');
        }
      });
    });
  }

  // -----------------------------------------------------------------------
  // Filtro de profissional — server-side (recarrega eventos via AJAX)
  // -----------------------------------------------------------------------
  var filtroProfissional = document.getElementById('filtro-profissional');
  if (filtroProfissional) {
    filtroProfissional.addEventListener('change', function () {
      var profId = this.value;
      var url    = profId ? (eventosUrl + '?profissional_id=' + profId) : eventosUrl;
      calendar.setOption('events', url);
      calendar.refetchEvents();
      // Zera filtro de status ao mudar profissional
      if (filtroStatus) filtroStatus.value = '';
    });
  }
});
</script>
@endsection
