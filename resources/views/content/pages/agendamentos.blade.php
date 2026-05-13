@php
$configData = Helper::appClasses();
$nivelAtual = Auth::user()->nivelAcesso();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Agenda')

{{-- Breadcrumb: Início > Agenda --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início', 'url' => '/'],
      ['label' => 'Agenda', 'url' => null],
    ]
  ])
@endpush

{{-- FullCalendar CSS + estilos da view --}}
@section('vendor-style')
<link rel="stylesheet" href="{{ asset(mix('assets/vendor/libs/fullcalendar/fullcalendar.css')) }}" />
<style>
  /* Base dos eventos: sem borda padrão do FC, cantos suaves */
  .fc-event        { border-radius: 4px !important; font-size: 0.8rem !important; }
  .fc-event-title  { font-weight: 500 !important; }

  /* Cores por status via tokens Bootstrap — adapta dark mode automaticamente */
  .fc-event.fc-event-pendente {
    background: rgba(var(--bs-warning-rgb), 0.16) !important;
    border: none !important;
    border-left: 3px solid var(--bs-warning) !important;
    color: var(--bs-warning) !important;
  }
  .fc-event.fc-event-confirmado {
    background: rgba(var(--bs-primary-rgb), 0.16) !important;
    border: none !important;
    border-left: 3px solid var(--bs-primary) !important;
    color: var(--bs-primary) !important;
  }
  .fc-event.fc-event-realizado {
    background: rgba(var(--bs-success-rgb), 0.16) !important;
    border: none !important;
    border-left: 3px solid var(--bs-success) !important;
    color: var(--bs-success) !important;
  }
  .fc-event.fc-event-cancelado {
    background: rgba(var(--bs-danger-rgb), 0.16) !important;
    border: none !important;
    border-left: 3px solid var(--bs-danger) !important;
    color: var(--bs-danger) !important;
  }

  /* Dark mode: grade do calendário via variáveis Bootstrap */
  .fc .fc-daygrid-day-number,
  .fc .fc-col-header-cell-cushion  { color: var(--bs-body-color); }
  .fc-theme-standard td,
  .fc-theme-standard th            { border-color: var(--bs-border-color); }
  .fc .fc-daygrid-day.fc-day-today { background: rgba(var(--bs-primary-rgb), 0.08); }

  /* Overflow fix: scrollgrid não ultrapassa o container do card */
  .fc .fc-scrollgrid              { min-width: 0; }
  .fc .fc-scrollgrid-sync-table   { min-width: 0 !important; }

  /* Fix 1: evento single-line em Semana/Dia — hora + nome numa linha, sem corte */
  .fc-event-line {
    display: flex;
    gap: 0.35rem;
    align-items: center;
    padding: 2px 6px;
    font-size: 0.75rem;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-height: 0;
  }
  .fc-event-line .fc-event-time  { font-weight: 600; flex-shrink: 0; }
  .fc-event-line .fc-event-title { overflow: hidden; text-overflow: ellipsis; font-weight: 500; }
</style>
@endsection

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
  {{-- CARD PRINCIPAL — controles + calendário + legenda                 --}}
  {{-- ================================================================ --}}
  <div class="card mb-4">

    {{-- ---- HEADER: barra de controles + counters ---- --}}
    <div class="card-header py-3">

      {{-- Linha de controles --}}
      <div class="d-flex flex-wrap align-items-center gap-2">

        {{-- Navegação: prev / next / título / hoje --}}
        <div class="d-flex align-items-center gap-1">
          <button type="button" id="btn-prev"
            class="btn btn-icon btn-sm btn-outline-secondary rounded-pill"
            title="Período anterior">
            <i class="mdi mdi-chevron-left"></i>
          </button>
          <button type="button" id="btn-next"
            class="btn btn-icon btn-sm btn-outline-secondary rounded-pill"
            title="Próximo período">
            <i class="mdi mdi-chevron-right"></i>
          </button>
          <span id="cal-title" class="fw-semibold mx-2 text-nowrap" style="min-width:130px;"></span>
          <button type="button" id="btn-today" class="btn btn-sm btn-outline-secondary">Hoje</button>
        </div>

        <div class="vr d-none d-md-block mx-1" style="height:24px;"></div>

        {{-- Switcher de visualização --}}
        <div class="btn-group btn-group-sm" role="group" aria-label="Visualização">
          <button type="button" id="btn-month" class="btn btn-outline-secondary active">Mês</button>
          <button type="button" id="btn-week"  class="btn btn-outline-secondary">Semana</button>
          <button type="button" id="btn-day"   class="btn btn-outline-secondary">Dia</button>
        </div>

        <div class="vr d-none d-md-block mx-1" style="height:24px;"></div>

        {{-- Filtro por profissional --}}
        {{-- nivel 3 (profissional): select desabilitado, pré-selecionado com o próprio nome --}}
        @if($nivelAtual == 3 && $profissionalLogado)
        <select id="filtro-profissional" class="form-select form-select-sm"
          disabled style="width:auto; min-width:185px;"
          title="Você visualiza apenas sua própria agenda">
          <option selected>{{ $profissionalLogado->nome }}</option>
        </select>
        {{-- nivel 1, 2 e 4 (admin / coordenador / recepcionista): select habilitado --}}
        @elseif($nivelAtual <= 2 || $nivelAtual == 4)
        <select id="filtro-profissional" class="form-select form-select-sm" style="width:auto; min-width:185px;">
          <option value="">Todos os profissionais</option>
          @foreach($profissionais as $prof)
          <option value="{{ $prof->id }}">{{ $prof->nome }}</option>
          @endforeach
        </select>
        @endif

        {{-- Filtro por status — client-side --}}
        <select id="filtro-status" class="form-select form-select-sm" style="width:auto; min-width:160px;">
          <option value="">Todos os status</option>
          <option value="pendente">Pendente</option>
          <option value="confirmado">Confirmado</option>
          <option value="realizado">Realizado</option>
          <option value="cancelado">Cancelado</option>
        </select>

        {{-- Novo Agendamento — texto completo em xxl+ (≥1400px); ícone em telas menores --}}
        <a href="/cadastro-agendamento" class="btn btn-primary btn-sm ms-auto flex-shrink-0"
           title="Novo Agendamento">
          <i class="mdi mdi-plus-circle-outline"></i>
          <span class="d-none d-xxl-inline ms-1">Novo Agendamento</span>
        </a>

      </div>

      {{-- Counters row — dots via tokens Bootstrap --}}
      <div class="d-flex flex-wrap gap-3 small mt-3 pt-3 border-top text-muted">
        <span><i class="mdi mdi-circle text-warning me-1"></i>Pendentes: <strong class="text-body">{{ $pendentes }}</strong></span>
        <span><i class="mdi mdi-circle text-primary me-1"></i>Confirmados: <strong class="text-body">{{ $confirmados }}</strong></span>
        <span><i class="mdi mdi-circle text-success me-1"></i>Realizados: <strong class="text-body">{{ $realizados }}</strong></span>
        <span><i class="mdi mdi-circle text-danger me-1"></i>Cancelados: <strong class="text-body">{{ $cancelados }}</strong></span>
        <span>| Total: <strong class="text-body">{{ $total }}</strong></span>
      </div>

    </div>
    {{-- /card-header --}}

    {{-- ---- BODY: calendário ---- --}}
    {{-- overflow: hidden remove o scroll externo; FC gerencia seu próprio scroll interno --}}
    <div class="card-body p-0" style="overflow: hidden;">
      <div id="calendar" style="padding: 1rem;"></div>
    </div>

    {{-- ---- FOOTER: legenda de cores via tokens Bootstrap ---- --}}
    <div class="card-footer d-flex flex-wrap gap-3 small text-muted py-2 border-top">
      <span><i class="mdi mdi-circle text-warning"></i> Pendente</span>
      <span><i class="mdi mdi-circle text-primary"></i> Confirmado</span>
      <span><i class="mdi mdi-circle text-success"></i> Realizado</span>
      <span><i class="mdi mdi-circle text-danger"></i> Cancelado</span>
    </div>

  </div>
  {{-- /card --}}

</div>

@endsection

{{-- FullCalendar JS (compilado via npm) --}}
@section('vendor-script')
<script src="{{ asset(mix('assets/vendor/libs/fullcalendar/fullcalendar.js')) }}"></script>
@endsection

@section('page-script')
<script>
// Calendário de agendamentos — controles unificados, cores via tokens Bootstrap
document.addEventListener('DOMContentLoaded', function () {
  var calendarEl = document.getElementById('calendar');
  if (!calendarEl) return;

  // Capitaliza apenas o primeiro caractere (resolve "maio" → "Maio" na view mensal;
  // views de semana/dia começam com dígito e não são alteradas)
  function capitalizarTitulo(titulo) {
    if (!titulo) return titulo;
    return titulo.charAt(0).toUpperCase() + titulo.slice(1);
  }

  // URL base dos eventos — pode receber ?profissional_id= via filtro server-side
  var eventosUrl = '/agendamentos/eventos';

  // Mapeamento view FC → ID do botão ativo no switcher
  var viewBtnMap = {
    'dayGridMonth': 'btn-month',
    'timeGridWeek':  'btn-week',
    'timeGridDay':   'btn-day',
  };

  function setViewActive(viewType) {
    ['btn-month', 'btn-week', 'btn-day'].forEach(function (id) {
      document.getElementById(id).classList.remove('active');
    });
    var activeId = viewBtnMap[viewType];
    if (activeId) document.getElementById(activeId).classList.add('active');
  }

  // FullCalendar sem headerToolbar nativo; eventDisplay:'block' garante pílula em todas as views
  var calendar = new Calendar(calendarEl, {
    plugins:       [dayGridPlugin, timegridPlugin, listPlugin],
    initialView:   'dayGridMonth',
    locale:        'pt-br',
    timeZone:      'America/Sao_Paulo',
    headerToolbar: false,
    eventDisplay:  'block',
    events:        eventosUrl,
    // eventContent sempre retorna conteúdo explícito — retornar undefined em FC v6
    // renderiza o container colorido sem texto (linha fina). Cada view tem seu layout:
    eventContent: function (arg) {
      var hora   = arg.timeText || '';
      var titulo = arg.event.title || '';
      if (arg.view.type === 'dayGridMonth') {
        // Mês: reconstitui o default do FC (hora curta + nome)
        return {
          html: (hora ? '<span class="fc-event-time">' + hora + '</span> ' : '') +
                '<span class="fc-event-title">' + titulo + '</span>'
        };
      }
      // Semana e Dia: linha única "hora nome" — evita corte em slots de 30min
      return {
        html: '<div class="fc-event-line">' +
              (hora ? '<span class="fc-event-time">' + hora + '</span>' : '') +
              '<span class="fc-event-title">' + titulo + '</span>' +
              '</div>'
      };
    },
    eventClick: function (info) {
      window.location.href = info.event.url;
      info.jsEvent.preventDefault();
    },
    // Atualiza título e botão ativo a cada navegação ou troca de view
    datesSet: function (info) {
      document.getElementById('cal-title').textContent = capitalizarTitulo(info.view.title);
      setViewActive(info.view.type);
    },
    noEventsContent: 'Nenhum agendamento neste período.',
    contentHeight:   600
  });

  calendar.render();

  // Título inicial (antes do primeiro disparo de datesSet)
  document.getElementById('cal-title').textContent = capitalizarTitulo(calendar.view.title);

  // -----------------------------------------------------------------------
  // Navegação
  // -----------------------------------------------------------------------
  document.getElementById('btn-prev').addEventListener('click',  function () { calendar.prev(); });
  document.getElementById('btn-next').addEventListener('click',  function () { calendar.next(); });
  document.getElementById('btn-today').addEventListener('click', function () { calendar.today(); });

  // -----------------------------------------------------------------------
  // Switcher de view
  // -----------------------------------------------------------------------
  document.getElementById('btn-month').addEventListener('click', function () { calendar.changeView('dayGridMonth'); });
  document.getElementById('btn-week').addEventListener('click',  function () { calendar.changeView('timeGridWeek'); });
  document.getElementById('btn-day').addEventListener('click',   function () { calendar.changeView('timeGridDay'); });

  // -----------------------------------------------------------------------
  // Filtro de status — client-side (esconde/mostra eventos sem reload)
  // -----------------------------------------------------------------------
  var filtroStatus = document.getElementById('filtro-status');
  if (filtroStatus) {
    filtroStatus.addEventListener('change', function () {
      var sel = this.value;
      calendar.getEvents().forEach(function (event) {
        if (!sel) {
          event.setProp('display', '');
        } else {
          event.setProp('display', event.extendedProps.status === sel ? '' : 'none');
        }
      });
    });
  }

  // -----------------------------------------------------------------------
  // Filtro de profissional — server-side (recarrega eventos via AJAX)
  // -----------------------------------------------------------------------
  var filtroProfissional = document.getElementById('filtro-profissional');
  if (filtroProfissional && !filtroProfissional.disabled) {
    filtroProfissional.addEventListener('change', function () {
      var profId = this.value;
      var url    = profId ? (eventosUrl + '?profissional_id=' + profId) : eventosUrl;
      calendar.setOption('events', url);
      calendar.refetchEvents();
      if (filtroStatus) filtroStatus.value = ''; // Zera filtro de status ao mudar profissional
    });
  }
});
</script>
@endsection
