@php
$configData    = Helper::appClasses();
$ordemExibicao = [1, 2, 3, 4, 5, 6, 0];

$diasNomesCompletos = [0=>'Domingo',1=>'Segunda',2=>'Terça',3=>'Quarta',4=>'Quinta',5=>'Sexta',6=>'Sábado'];
$diasAbrev          = [0=>'DOM',1=>'SEG',2=>'TER',3=>'QUA',4=>'QUI',5=>'SEX',6=>'SÁB'];

// Slots de preview: 30min, 07:00–20:00
$slotsPreview = [];
for ($h = 7; $h < 20; $h++) {
    $slotsPreview[] = sprintf('%02d:00', $h);
    $slotsPreview[] = sprintf('%02d:30', $h);
}

$usarOld = session()->hasOldInput('dias');

// Turnos: label, ícone, classe Bootstrap, horas padrão vindas da config
$turnosDef = [
    'manha' => ['label'=>'Manhã', 'icon'=>'mdi-weather-sunset-up', 'cor'=>'warning',
                'ini'=>substr($config->turno_manha_inicio,0,5),
                'fim'=>substr($config->turno_manha_fim,   0,5)],
    'tarde' => ['label'=>'Tarde', 'icon'=>'mdi-weather-sunny',     'cor'=>'primary',
                'ini'=>substr($config->turno_tarde_inicio,0,5),
                'fim'=>substr($config->turno_tarde_fim,   0,5)],
    'noite' => ['label'=>'Noite', 'icon'=>'mdi-weather-night',     'cor'=>'info',
                'ini'=>substr($config->turno_noite_inicio,0,5),
                'fim'=>substr($config->turno_noite_fim,   0,5)],
];

// Mapa de estado por dia: ativo + horas salvas por turno
$estadoDias = [];
foreach ($ordemExibicao as $dia) {
    if ($usarOld) {
        $dAtivo = old("dias.{$dia}.ativo") ? true : false;
        $iOld   = old("dias.{$dia}.blocos.hora_inicio", []);
        $fOld   = old("dias.{$dia}.blocos.hora_fim",    []);
        $pares  = collect(array_map(null, $iOld, $fOld))->filter(fn($p)=>$p[0]&&$p[1])->values();
    } else {
        $bDia  = $blocos->get($dia, collect());
        $dAtivo = $bDia->isNotEmpty();
        $pares  = $bDia->map(fn($b)=>[substr($b->hora_inicio,0,5),substr($b->hora_fim,0,5)])->values();
    }
    $estadoDias[$dia] = [
        'ativo' => $dAtivo,
        'manha' => $pares->first(fn($p)=>$p[0]<'12:00'),
        'tarde' => $pares->first(fn($p)=>$p[0]>='12:00'&&$p[0]<'18:00'),
        'noite' => $pares->first(fn($p)=>$p[0]>='18:00'),
    ];
}

// Ícone por tipo de exceção
$excIcons = [
    'bloqueio'        => ['icon'=>'mdi-briefcase-outline',      'cor'=>'danger'],
    'disponivel_extra'=> ['icon'=>'mdi-clock-plus-outline',     'cor'=>'success'],
];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Minha Disponibilidade')

@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label'=>'Início',               'url'=>'/'],
      ['label'=>'Minha Disponibilidade','url'=>null],
    ]
  ])
@endpush

@section('content')

<style>
/* ── Grid de disponibilidade ─────────────────────────────────── */
.disp-row {
  display: grid;
  grid-template-columns: 190px repeat(3, 1fr);
  border-bottom: 1px solid var(--bs-border-color);
}
.disp-row:last-child { border-bottom: none; }
@media (max-width: 575px) {
  .disp-row { grid-template-columns: 120px repeat(3, 1fr); }
}
.disp-col {
  padding: 0.55rem 0.65rem;
  border-right: 1px solid var(--bs-border-color);
}
.disp-col:last-child { border-right: none; }

/* Cabeçalho do turno */
.disp-turno-hdr {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  border-radius: 0.5rem;
  padding: 0.45rem 0.5rem;
  font-weight: 600;
  font-size: 0.82rem;
  gap: 0.1rem;
}
.disp-turno-hdr small { font-weight: 400; font-size: 0.68rem; opacity: 0.8; }

/* Coluna do dia (toggle + nome) */
.disp-col-dia { display: flex; align-items: center; gap: 0.45rem; }
.disp-dia-nome strong { font-size: 0.82rem; display: block; line-height: 1.2; }
.disp-dia-nome small  { font-size: 0.67rem; color: var(--bs-secondary-color); }
.dia-off .disp-col-dia { opacity: 0.45; }

/* Célula de turno */
.disp-celula {
  position: relative;
  border-radius: 0.45rem;
  border: 1.5px solid var(--bs-border-color);
  min-height: 62px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 0.3rem 0.4rem 0.3rem 0.45rem;
  transition: background 0.12s, border-color 0.12s;
  cursor: default;
}
/* checkbox canto superior direito */
.cel-check {
  position: absolute;
  top: 0.35rem;
  right: 0.35rem;
  width: 1rem;
  height: 1rem;
  margin: 0;
  cursor: pointer;
}
/* Inativo (dia ativo, turno off) */
.cel-inativa { border-style: dashed; background: transparent; cursor: pointer; }
.cel-inativa:hover { border-color: rgba(var(--bs-primary-rgb),0.4); }
.cel-inativa .cel-label { font-size: 0.75rem; color: var(--bs-secondary-color); }
/* Disabled (dia desativado) */
.cel-disabled { background: var(--bs-tertiary-bg); opacity: 0.4; cursor: not-allowed; }
.cel-disabled .cel-label { font-size: 0.75rem; color: var(--bs-secondary-color); }
/* Ativo compacto */
.cel-ativa { cursor: default; }
.cel-range {
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  display: inline-block;
  padding-right: 1.4rem; /* espaço para o checkbox */
}
.cel-range:hover { text-decoration: underline dotted; }
.cel-slots { font-size: 0.68rem; color: var(--bs-secondary-color); margin-top: 0.1rem; }
/* Cores por turno */
.cel-manha.cel-ativa { background: rgba(255,152,0,0.08); border-color: rgba(255,152,0,0.35); }
.cel-tarde.cel-ativa { background: rgba(var(--bs-primary-rgb),0.07); border-color: rgba(var(--bs-primary-rgb),0.3); }
.cel-noite.cel-ativa { background: rgba(0,150,136,0.08); border-color: rgba(0,150,136,0.35); }
.cel-manha .cel-range { color: #e65100; }
.cel-tarde .cel-range { color: var(--bs-primary); }
.cel-noite .cel-range { color: #00796b; }
/* Modo edição (inputs in-place) */
.cel-edit-row {
  display: none;
  align-items: center;
  gap: 0.2rem;
  margin-top: 0.25rem;
  flex-wrap: wrap;
}
.disp-celula.editando .cel-compact { display: none; }
.disp-celula.editando .cel-edit-row { display: flex; }
.cel-time-input { width: 5.3rem; font-size: 0.78rem; padding: 0.15rem 0.3rem; }

/* Footer da grid */
.disp-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.55rem 0.75rem;
  background: var(--bs-tertiary-bg);
  font-size: 0.8rem;
  flex-wrap: wrap;
  gap: 0.5rem;
}

/* Card de slots */
.card-slots-count { border-color: rgba(var(--bs-success-rgb),0.3) !important; }
.card-slots-count .slots-num {
  font-size: 1.6rem;
  font-weight: 700;
  color: var(--bs-success);
  line-height: 1;
}
.card-slots-count .slots-sub { font-size: 0.78rem; color: var(--bs-secondary-color); margin-top: 0.2rem; }

/* Botão group config */
.btn-group-config .btn { font-size: 0.8rem; padding: 0.3rem 0.65rem; }

/* Exceção na lista */
.exc-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.55rem 0;
  border-bottom: 1px solid var(--bs-border-color);
}
.exc-item:last-child { border-bottom: none; }
.exc-icon { width: 2rem; height: 2rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1rem; margin-top: 0.1rem; }
</style>

<div class="container-xxl flex-grow-1 container-p-y pb-5">

  {{-- Cabeçalho + botões de ação --}}
  <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
    <div>
      <h4 class="mb-1">Minha disponibilidade</h4>
      <p class="text-muted mb-0 small">
        Defina seus horários de atendimento.
        As mudanças passam a valer a partir de <strong>amanhã, {{ now()->addDay()->translatedFormat('d \d\e F') }}</strong>.
      </p>
    </div>
    <div class="d-flex gap-2 flex-shrink-0">
      <a href="/disponibilidade" class="btn btn-outline-secondary">Cancelar</a>
      <button form="form-disponibilidade" type="submit" class="btn btn-primary">
        <i class="mdi mdi-check me-1"></i>Salvar alterações
      </button>
    </div>
  </div>

  {{-- Alertas --}}
  @if(session('success'))
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    <i class="mdi mdi-check-circle-outline me-1"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif
  @if(session('warning'))
  <div class="alert alert-warning alert-dismissible mb-4" role="alert">
    <i class="mdi mdi-alert-outline me-1"></i>{{ session('warning') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif
  @if(session('error'))
  <div class="alert alert-danger alert-dismissible mb-4" role="alert">
    <i class="mdi mdi-close-circle-outline me-1"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  {{-- ================================================================ --}}
  {{-- FORM PRINCIPAL                                                    --}}
  {{-- ================================================================ --}}
  <form id="form-disponibilidade" method="POST" action="/disponibilidade">
    @csrf

    {{-- Inputs ocultos: horas padrão dos turnos (modificados pelo modal "Editar períodos") --}}
    <input type="hidden" name="config[turno_manha_inicio]" id="f-manha-ini" value="{{ old('config.turno_manha_inicio', $config->turno_manha_inicio) }}">
    <input type="hidden" name="config[turno_manha_fim]"   id="f-manha-fim" value="{{ old('config.turno_manha_fim',    $config->turno_manha_fim) }}">
    <input type="hidden" name="config[turno_tarde_inicio]" id="f-tarde-ini" value="{{ old('config.turno_tarde_inicio', $config->turno_tarde_inicio) }}">
    <input type="hidden" name="config[turno_tarde_fim]"   id="f-tarde-fim" value="{{ old('config.turno_tarde_fim',    $config->turno_tarde_fim) }}">
    <input type="hidden" name="config[turno_noite_inicio]" id="f-noite-ini" value="{{ old('config.turno_noite_inicio', $config->turno_noite_inicio) }}">
    <input type="hidden" name="config[turno_noite_fim]"   id="f-noite-fim" value="{{ old('config.turno_noite_fim',    $config->turno_noite_fim) }}">

    {{-- Antecedências mantidas como hidden (não exibidas no protótipo atual) --}}
    <input type="hidden" name="config[antecedencia_minima_horas]" value="{{ old('config.antecedencia_minima_horas', $config->antecedencia_minima_horas) }}">
    <input type="hidden" name="config[antecedencia_maxima_dias]"  value="{{ old('config.antecedencia_maxima_dias',  $config->antecedencia_maxima_dias) }}">

    <div class="row g-4">

      {{-- ── COL ESQUERDA: Horários semanais ─────────────────────────── --}}
      <div class="col-12 col-lg-7 order-2 order-lg-1">

        <div class="card">
          <div class="card-header d-flex align-items-center gap-2 py-3 px-4">
            <h5 class="card-title mb-0">
              <i class="mdi mdi-calendar-week-outline me-2"></i>Horários semanais
            </h5>
            {{-- Popover de ajuda --}}
            <button type="button"
                    class="btn btn-icon btn-sm btn-text-secondary rounded-circle p-0"
                    tabindex="0"
                    data-bs-toggle="popover"
                    data-bs-trigger="focus"
                    data-bs-placement="bottom"
                    data-bs-html="true"
                    title="<strong>Horários semanais</strong>"
                    data-bs-content="Selecione os turnos em que você atende em cada dia. Clique em <strong>Manhã</strong>, <strong>Tarde</strong> ou <strong>Noite</strong> para ativar o turno. Ao ativar, clique nas horas para personalizar o intervalo. Use <strong>Editar períodos</strong> para ajustar os padrões globais de cada turno.">
              <i class="mdi mdi-information-outline" style="font-size:1rem"></i>
            </button>
            <a href="#" class="small text-primary ms-auto"
               data-bs-toggle="modal" data-bs-target="#modal-periodos">
              <i class="mdi mdi-pencil-outline me-1"></i>Editar períodos
            </a>
          </div>

          <div class="card-body p-0" style="overflow-x:auto;">
            <div style="min-width:460px;">

              {{-- Linha de cabeçalho dos turnos --}}
              <div class="disp-row">
                <div class="disp-col"></div>
                @foreach($turnosDef as $tKey => $t)
                <div class="disp-col">
                  <div class="disp-turno-hdr bg-label-{{ $t['cor'] }}">
                    <div><i class="mdi {{ $t['icon'] }} me-1"></i>{{ $t['label'] }}</div>
                    <small class="turno-hdr-range" id="hdr-{{ $tKey }}">{{ $t['ini'] }}–{{ $t['fim'] }}</small>
                  </div>
                </div>
                @endforeach
              </div>

              {{-- Linhas dos dias --}}
              @foreach($ordemExibicao as $dia)
              @php
                $dAtivo = $estadoDias[$dia]['ativo'];
              @endphp

              <div class="disp-row disp-day-row {{ !$dAtivo ? 'dia-off' : '' }}" id="row-dia-{{ $dia }}">

                {{-- Coluna: toggle + nome do dia --}}
                <div class="disp-col disp-col-dia">
                  <div class="form-check form-switch mb-0 flex-shrink-0">
                    <input class="form-check-input" type="checkbox" role="switch"
                           id="toggle-dia-{{ $dia }}"
                           name="dias[{{ $dia }}][ativo]" value="1"
                           {{ $dAtivo ? 'checked' : '' }}
                           onchange="toggleDia({{ $dia }}, this.checked)">
                  </div>
                  <label for="toggle-dia-{{ $dia }}" class="disp-dia-nome mb-0" style="cursor:pointer">
                    <strong>{{ $diasNomesCompletos[$dia] }}</strong>
                    <small>{{ $diasAbrev[$dia] }}</small>
                  </label>
                </div>

                {{-- Células de turno --}}
                @foreach($turnosDef as $tKey => $t)
                @php
                  $celAtiva = $estadoDias[$dia][$tKey] !== null;
                  $celIni   = $celAtiva ? $estadoDias[$dia][$tKey][0] : $t['ini'];
                  $celFim   = $celAtiva ? $estadoDias[$dia][$tKey][1] : $t['fim'];
                  $celClass = !$dAtivo ? 'cel-disabled' : ($celAtiva ? 'cel-ativa' : 'cel-inativa');
                @endphp

                <div class="disp-col">
                  <div class="disp-celula cel-{{ $tKey }} {{ $celClass }}"
                       id="cel-{{ $dia }}-{{ $tKey }}"
                       data-dia="{{ $dia }}"
                       data-turno="{{ $tKey }}"
                       data-padrao-ini="{{ $t['ini'] }}"
                       data-padrao-fim="{{ $t['fim'] }}">

                    {{-- Checkbox no canto superior direito --}}
                    <input type="checkbox" class="cel-check form-check-input"
                           {{ $celAtiva ? 'checked' : '' }}
                           {{ !$dAtivo ? 'disabled' : '' }}
                           onchange="toggleCelula(this)">

                    {{-- Modo compacto --}}
                    <div class="cel-compact">
                      @if(!$dAtivo)
                        <span class="cel-label">Indisponível</span>
                      @elseif(!$celAtiva)
                        <span class="cel-label">Não atende</span>
                      @else
                        <div class="cel-range" onclick="entrarEdit(this.closest('.disp-celula'))">
                          {{ $celIni }} – {{ $celFim }}
                        </div>
                        <div class="cel-slots" id="slots-{{ $dia }}-{{ $tKey }}"></div>
                      @endif
                    </div>

                    {{-- Modo edição (aparece ao clicar no range) --}}
                    <div class="cel-edit-row">
                      <input type="time"
                             name="dias[{{ $dia }}][blocos][hora_inicio][]"
                             class="form-control form-control-sm cel-time-input cel-ini"
                             value="{{ $celIni }}"
                             {{ !$celAtiva ? 'disabled' : '' }}
                             onblur="sairEdit(this.closest('.disp-celula'))"
                             onchange="sincronizarRange(this.closest('.disp-celula'))">
                      <span class="text-muted small">–</span>
                      <input type="time"
                             name="dias[{{ $dia }}][blocos][hora_fim][]"
                             class="form-control form-control-sm cel-time-input cel-fim"
                             value="{{ $celFim }}"
                             {{ !$celAtiva ? 'disabled' : '' }}
                             onblur="sairEdit(this.closest('.disp-celula'))"
                             onchange="sincronizarRange(this.closest('.disp-celula'))">
                    </div>

                  </div>
                </div>
                @endforeach

              </div>
              @endforeach

              {{-- Footer da grid --}}
              <div class="disp-footer">
                <span id="resumo-periodos" class="text-muted"></span>
                <a href="#" class="text-primary small"
                   onclick="aplicarPadraoSegSex(); return false;">
                  <i class="mdi mdi-content-copy me-1"></i>Aplicar padrão seg-sex
                </a>
              </div>

            </div>{{-- /min-width --}}
          </div>{{-- /card-body --}}
        </div>{{-- /card horários --}}

      </div>{{-- /col esquerda --}}

      {{-- ── COL DIREITA: Config + Exceções + Slot count ──────────────── --}}
      <div class="col-12 col-lg-5 order-1 order-lg-2">
        <div style="position:sticky; top:5.5rem;">

          {{-- Configuração de agenda (colapsável) --}}
          <div class="card mb-4">
            <div class="card-header py-3 d-flex align-items-center gap-2">
              {{-- Área clicável: título + chevron (somente esta parte abre/fecha) --}}
              <div class="d-flex align-items-center flex-grow-1 gap-2"
                   style="cursor:pointer"
                   data-bs-toggle="collapse"
                   data-bs-target="#collapse-config"
                   aria-expanded="true"
                   aria-controls="collapse-config">
                <h5 class="card-title mb-0 flex-grow-1">
                  <i class="mdi mdi-timer-cog-outline me-2"></i>Configuração de agenda
                </h5>
                <i class="mdi mdi-chevron-up text-muted" id="icon-collapse-config" style="transition:transform 0.2s;"></i>
              </div>
              {{-- Botão de ajuda fora da área de colapso --}}
              <button type="button"
                      class="btn btn-icon btn-sm btn-text-secondary rounded-circle p-0"
                      tabindex="0"
                      data-bs-toggle="popover"
                      data-bs-trigger="focus"
                      data-bs-placement="left"
                      data-bs-html="true"
                      title="<strong>Configuração de agenda</strong>"
                      data-bs-content="<b>Duração:</b> tempo de cada consulta (15, 30, 45 ou 60 min).<br><b>Intervalo:</b> pausa entre consultas consecutivas (evita sobreposição).<br><b>Agendamentos online:</b> permite que pacientes agendem pelo portal.<br><b>Encaixe:</b> reserva 2 vagas por turno para atendimentos de urgência.">
                <i class="mdi mdi-information-outline" style="font-size:1rem"></i>
              </button>
            </div>
            <div id="collapse-config" class="collapse show">
            <div class="card-body">

              {{-- Duração da consulta --}}
              <p class="small fw-semibold text-uppercase text-muted mb-2" style="letter-spacing:.05em">Duração da consulta</p>
              <div class="btn-group btn-group-config w-100 mb-4" role="group" aria-label="Duração">
                @foreach([15=>'15 min',30=>'30 min',45=>'45 min',60=>'60 min'] as $v=>$lbl)
                <input type="radio" class="btn-check" name="config[duracao_minutos]"
                       id="dur-{{ $v }}" value="{{ $v }}"
                       {{ old('config.duracao_minutos', $config->duracao_minutos)==$v ? 'checked' : '' }}
                       onchange="recalcularTudo()">
                <label class="btn btn-outline-secondary" for="dur-{{ $v }}">{{ $lbl }}</label>
                @endforeach
              </div>

              {{-- Intervalo entre consultas --}}
              <p class="small fw-semibold text-uppercase text-muted mb-2" style="letter-spacing:.05em">Intervalo entre consultas</p>
              <div class="btn-group btn-group-config w-100 mb-4" role="group" aria-label="Intervalo">
                @foreach([0=>'Sem intervalo',5=>'5 min',10=>'10 min',15=>'15 min'] as $v=>$lbl)
                <input type="radio" class="btn-check" name="config[buffer_minutos]"
                       id="buf-{{ $v }}" value="{{ $v }}"
                       {{ old('config.buffer_minutos', $config->buffer_minutos)==$v ? 'checked' : '' }}>
                <label class="btn btn-outline-secondary" for="buf-{{ $v }}">{{ $lbl }}</label>
                @endforeach
              </div>

              {{-- Toggle: aceitar agendamentos online --}}
              <div class="d-flex align-items-center justify-content-between py-2 border-top">
                <div class="pe-3">
                  <div class="fw-semibold small">Aceitar agendamentos online</div>
                  <div class="text-muted" style="font-size:0.73rem">Pacientes podem agendar pelo portal</div>
                </div>
                <div class="form-check form-switch mb-0 flex-shrink-0">
                  <input class="form-check-input" type="checkbox"
                         name="config[aceitar_agendamentos_online]" value="1"
                         {{ old('config.aceitar_agendamentos_online', $config->aceitar_agendamentos_online) ? 'checked' : '' }}>
                </div>
              </div>

              {{-- Toggle: reservar para encaixe --}}
              <div class="d-flex align-items-center justify-content-between py-2 border-top">
                <div class="pe-3">
                  <div class="fw-semibold small">Reservar horários para encaixe</div>
                  <div class="text-muted" style="font-size:0.73rem">Reserva 2 consultas por turno</div>
                </div>
                <div class="form-check form-switch mb-0 flex-shrink-0">
                  <input class="form-check-input" type="checkbox"
                         name="config[reservar_horarios_encaixe]" value="1"
                         {{ old('config.reservar_horarios_encaixe', $config->reservar_horarios_encaixe) ? 'checked' : '' }}>
                </div>
              </div>

            </div>
            </div>{{-- /collapse-config --}}
          </div>

          {{-- Datas com exceções --}}
          <div class="card mb-4">
            <div class="card-header d-flex align-items-center gap-2 py-3">
              <h6 class="card-title mb-0">
                <i class="mdi mdi-calendar-remove-outline me-2"></i>Datas com exceções
              </h6>
              {{-- Popover de ajuda --}}
              <button type="button"
                      class="btn btn-icon btn-sm btn-text-secondary rounded-circle p-0"
                      tabindex="0"
                      data-bs-toggle="popover"
                      data-bs-trigger="focus"
                      data-bs-placement="left"
                      data-bs-html="true"
                      title="<strong>Exceções e Bloqueios</strong>"
                      data-bs-content="Exceções sobrepõem seus horários recorrentes em datas específicas. Use para <strong>bloquear</strong> períodos — como férias, feriados ou congressos. Ou para <strong>abrir disponibilidade extra</strong> em uma data atípica, como um sábado pontual.<br><br>Podem cobrir o dia inteiro ou apenas um intervalo de horário.">
                <i class="mdi mdi-information-outline" style="font-size:1rem"></i>
              </button>
              <button type="button" class="btn btn-link btn-sm p-0 text-primary ms-auto"
                      data-bs-toggle="modal" data-bs-target="#modal-add-excecao">
                <i class="mdi mdi-plus me-1"></i>Adicionar
              </button>
            </div>
            <div class="card-body py-2">
              @if($excecoes->isEmpty())
              <p class="text-muted small mb-0 py-1">Nenhuma exceção cadastrada.</p>
              @else
              @foreach($excecoes as $exc)
              @php
                $ei = $excIcons[$exc->tipo] ?? ['icon'=>'mdi-calendar-outline','cor'=>'secondary'];
                $dataFmt = $exc->data_inicio->format('d M Y');
                if (!$exc->data_inicio->eq($exc->data_fim)) $dataFmt .= ' – '.$exc->data_fim->format('d M Y');
                $horaFmt = $exc->hora_inicio ? substr($exc->hora_inicio,0,5).'–'.substr($exc->hora_fim,0,5) : 'Dia inteiro';
              @endphp
              <div class="exc-item">
                <div class="exc-icon bg-label-{{ $ei['cor'] }}">
                  <i class="mdi {{ $ei['icon'] }} text-{{ $ei['cor'] }}"></i>
                </div>
                <div class="flex-grow-1 small">
                  <div class="fw-semibold">{{ $exc->motivo ?: ($exc->tipo==='bloqueio' ? 'Bloqueio' : 'Disponibilidade extra') }}</div>
                  <div class="text-muted">{{ $dataFmt }} &middot; {{ $horaFmt }}</div>
                </div>
                <div class="d-flex gap-1 flex-shrink-0">
                  <button type="button"
                          class="btn btn-icon btn-sm btn-text-secondary"
                          data-bs-toggle="modal"
                          data-bs-target="#modal-edit-exc-{{ $exc->id }}"
                          title="Editar">
                    <i class="mdi mdi-pencil-outline"></i>
                  </button>
                  <button type="submit"
                          form="del-exc-{{ $exc->id }}"
                          class="btn btn-icon btn-sm btn-text-danger"
                          onclick="return confirm('Remover esta exceção?')"
                          title="Remover">
                    <i class="mdi mdi-close"></i>
                  </button>
                </div>
              </div>
              @endforeach
              @endif
            </div>
          </div>

          {{-- Card: total de consultas --}}
          <div class="card card-slots-count mb-4">
            <div class="card-body d-flex align-items-center gap-3 py-3">
              <div class="flex-shrink-0">
                <i class="mdi mdi-calendar-check mdi-36px text-success"></i>
              </div>
              <div>
                <div class="slots-num"><span id="total-slots">--</span> <small style="font-size:1rem">consultas / semana</small></div>
                <div class="slots-sub" id="slots-sub-texto">calculando...</div>
              </div>
            </div>
          </div>

          {{-- Pré-visualização da semana --}}
          <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between py-3">
              <h6 class="card-title mb-0 fw-semibold">
                <i class="mdi mdi-calendar-month-outline me-2"></i>Pré-visualização da semana
              </h6>
              <span class="text-muted small">
                {{ $inicioSemana->translatedFormat('d M') }} – {{ $inicioSemana->copy()->endOfWeek()->translatedFormat('d M') }}
              </span>
            </div>
            <div class="card-body p-0" style="overflow-x:auto;">
              @include('content.pages.partials._preview_semanal', [
                'blocos'             => $blocos,
                'slotsPreview'       => $slotsPreview,
                'ordemExibicao'      => $ordemExibicao,
                'diasNomes'          => $diasNomes,
                'inicioSemana'       => $inicioSemana,
                'agendamentosSemana' => $agendamentosSemana,
                'excecoesSemana'     => $excecoesSemana,
              ])
            </div>
          </div>

        </div>{{-- /sticky --}}
      </div>{{-- /col direita --}}

    </div>{{-- /row principal --}}
  </form>

  {{-- Forms de DELETE para cada exceção (referenciados via form= nos botões acima) --}}
  @foreach($excecoes as $exc)
  <form id="del-exc-{{ $exc->id }}"
        method="POST"
        action="/disponibilidade/excecoes/{{ $exc->id }}">
    @csrf @method('DELETE')
  </form>
  @endforeach

</div>{{-- /container --}}

{{-- ================================================================ --}}
{{-- MODAL: Editar Períodos (horas padrão dos turnos)                  --}}
{{-- ================================================================ --}}
<div class="modal fade" id="modal-periodos" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="mdi mdi-pencil-outline me-2"></i>Editar períodos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-3">
          Define os horários padrão de cada turno. Células já personalizadas não são alteradas.
        </p>
        @foreach($turnosDef as $tKey => $t)
        <label class="form-label small fw-semibold">
          <i class="mdi {{ $t['icon'] }} me-1 text-{{ $t['cor'] }}"></i>{{ $t['label'] }}
        </label>
        <div class="d-flex align-items-center gap-2 mb-3">
          <input type="time" class="form-control form-control-sm" id="per-{{ $tKey }}-ini" value="{{ $t['ini'] }}">
          <span class="text-muted small">–</span>
          <input type="time" class="form-control form-control-sm" id="per-{{ $tKey }}-fim" value="{{ $t['fim'] }}">
        </div>
        @endforeach
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="salvarPeriodos()">
          <i class="mdi mdi-check me-1"></i>Aplicar
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- MODAL: Adicionar Exceção                                          --}}
{{-- ================================================================ --}}
<div class="modal fade" id="modal-add-excecao" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="mdi mdi-calendar-plus-outline me-2"></i>Adicionar exceção</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="/disponibilidade/excecoes">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Tipo <span class="text-danger">*</span></label>
            <select name="tipo" class="form-select" required>
              <option value="bloqueio">Bloqueio — não estarei disponível</option>
              <option value="disponivel_extra">Disponibilidade extra — atenderei nesta data</option>
            </select>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label">Data início <span class="text-danger">*</span></label>
              <input type="date" name="data_inicio" id="add-exc-ini"
                     class="form-control" required min="{{ date('Y-m-d') }}">
            </div>
            <div class="col-6">
              <label class="form-label">Data fim <span class="text-danger">*</span></label>
              <input type="date" name="data_fim" id="add-exc-fim"
                     class="form-control" required min="{{ date('Y-m-d') }}">
            </div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label">Hora início <small class="text-muted">(opcional)</small></label>
              <input type="time" name="hora_inicio" class="form-control">
            </div>
            <div class="col-6">
              <label class="form-label">Hora fim <small class="text-muted">(opcional)</small></label>
              <input type="time" name="hora_fim" class="form-control">
            </div>
          </div>
          <div>
            <label class="form-label">Motivo <small class="text-muted">(opcional)</small></label>
            <input type="text" name="motivo" class="form-control" maxlength="255"
                   placeholder="Ex: Férias, congresso, feriado local…">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">
            <i class="mdi mdi-content-save-outline me-1"></i>Salvar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- MODAIS: Editar cada Exceção                                       --}}
{{-- ================================================================ --}}
@foreach($excecoes as $exc)
<div class="modal fade" id="modal-edit-exc-{{ $exc->id }}" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="mdi mdi-calendar-edit-outline me-2"></i>Editar exceção</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="/disponibilidade/excecoes/{{ $exc->id }}">
        @csrf @method('PATCH')
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Tipo <span class="text-danger">*</span></label>
            <select name="tipo" class="form-select" required>
              <option value="bloqueio"         {{ $exc->tipo==='bloqueio'         ? 'selected':'' }}>Bloqueio</option>
              <option value="disponivel_extra" {{ $exc->tipo==='disponivel_extra' ? 'selected':'' }}>Disponibilidade extra</option>
            </select>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label">Data início <span class="text-danger">*</span></label>
              <input type="date" name="data_inicio" class="form-control"
                     required value="{{ $exc->data_inicio->format('Y-m-d') }}">
            </div>
            <div class="col-6">
              <label class="form-label">Data fim <span class="text-danger">*</span></label>
              <input type="date" name="data_fim" class="form-control"
                     required value="{{ $exc->data_fim->format('Y-m-d') }}">
            </div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label">Hora início <small class="text-muted">(opcional)</small></label>
              <input type="time" name="hora_inicio" class="form-control"
                     value="{{ $exc->hora_inicio ? substr($exc->hora_inicio,0,5) : '' }}">
            </div>
            <div class="col-6">
              <label class="form-label">Hora fim <small class="text-muted">(opcional)</small></label>
              <input type="time" name="hora_fim" class="form-control"
                     value="{{ $exc->hora_fim ? substr($exc->hora_fim,0,5) : '' }}">
            </div>
          </div>
          <div>
            <label class="form-label">Motivo <small class="text-muted">(opcional)</small></label>
            <input type="text" name="motivo" class="form-control" maxlength="255"
                   value="{{ $exc->motivo }}">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">
            <i class="mdi mdi-content-save-outline me-1"></i>Salvar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach

@endsection

@section('page-script')
<script>
(function () {
  'use strict';

  var ORDEM = [1,2,3,4,5,6,0];
  var TURNOS = ['manha','tarde','noite'];

  /* ── Obtém duração selecionada ──────────────────────────────── */
  function getDuracao() {
    var el = document.querySelector('[name="config[duracao_minutos]"]:checked');
    return el ? parseInt(el.value, 10) : 30;
  }

  /* ── Calcula slots de uma célula ativa ──────────────────────── */
  function calcSlotsCelula(celEl) {
    var ini = celEl.querySelector('.cel-ini');
    var fim = celEl.querySelector('.cel-fim');
    if (!ini || !fim || !ini.value || !fim.value) return 0;
    var duracao = getDuracao();
    var iniMin = parseInt(ini.value.split(':')[0])*60 + parseInt(ini.value.split(':')[1]);
    var fimMin = parseInt(fim.value.split(':')[0])*60 + parseInt(fim.value.split(':')[1]);
    var diff = fimMin - iniMin;
    return diff > 0 ? Math.floor(diff / duracao) : 0;
  }

  /* ── Atualiza contagem de slots de uma célula ───────────────── */
  function atualizarSlotsCelula(celEl) {
    var dia   = celEl.dataset.dia;
    var turno = celEl.dataset.turno;
    var el    = document.getElementById('slots-' + dia + '-' + turno);
    if (!el) return;
    var n = calcSlotsCelula(celEl);
    var duracao = getDuracao();
    el.textContent = n + ' consulta' + (n!==1?'s':'') + ' de ' + duracao + ' min';
  }

  /* ── Recalcula totais (card verde + footer) ─────────────────── */
  window.recalcularTudo = function () {
    var totalSlots = 0, totalMin = 0;
    var diasAtivos = [], diasAbrevs = {1:'seg',2:'ter',3:'qua',4:'qui',5:'sex',6:'sab',0:'dom'};

    ORDEM.forEach(function (dia) {
      var toggle = document.getElementById('toggle-dia-' + dia);
      if (!toggle || !toggle.checked) return;

      var diaTemSlot = false;
      TURNOS.forEach(function (turno) {
        var cel = document.getElementById('cel-' + dia + '-' + turno);
        if (!cel || !cel.classList.contains('cel-ativa')) return;
        var n = calcSlotsCelula(cel);
        totalSlots += n;
        atualizarSlotsCelula(cel);
        var ini = cel.querySelector('.cel-ini');
        var fim = cel.querySelector('.cel-fim');
        if (ini && fim && ini.value && fim.value) {
          var iniMin = parseInt(ini.value.split(':')[0])*60 + parseInt(ini.value.split(':')[1]);
          var fimMin = parseInt(fim.value.split(':')[0])*60 + parseInt(fim.value.split(':')[1]);
          if (fimMin > iniMin) { totalMin += (fimMin - iniMin); diaTemSlot = true; }
        }
      });
      if (diaTemSlot) diasAtivos.push(dia);
    });

    /* Card verde */
    document.getElementById('total-slots').textContent = totalSlots + ' consultas';
    var horas = (totalMin / 60).toFixed(0);
    var diasStr = diasAtivos.map(function(d){ return diasAbrevs[d]; }).join(', ');
    document.getElementById('slots-sub-texto').textContent =
      horas + 'h disponíveis' + (diasStr ? ' · ' + diasStr : '');

    /* Footer */
    var totalCelulas = 0;
    ORDEM.forEach(function(dia) {
      var toggle = document.getElementById('toggle-dia-' + dia);
      if (!toggle || !toggle.checked) return;
      TURNOS.forEach(function(turno) {
        var cel = document.getElementById('cel-' + dia + '-' + turno);
        if (cel && cel.classList.contains('cel-ativa')) totalCelulas++;
      });
    });
    var resumo = document.getElementById('resumo-periodos');
    if (resumo) {
      resumo.innerHTML =
        '<strong>' + totalCelulas + '</strong> período' + (totalCelulas!==1?'s':'') + ' ativo' + (totalCelulas!==1?'s':'') +
        ' &middot; <strong>' + diasAtivos.length + '</strong> dia' + (diasAtivos.length!==1?'s':'') + ' de atendimento';
    }
  };

  /* ── Sincroniza range display após edição de hora ───────────── */
  window.sincronizarRange = function (celEl) {
    var ini = celEl.querySelector('.cel-ini');
    var fim = celEl.querySelector('.cel-fim');
    var rangeEl = celEl.querySelector('.cel-range');
    if (rangeEl && ini && fim) {
      rangeEl.textContent = (ini.value || '--') + ' – ' + (fim.value || '--');
    }
    recalcularTudo();
  };

  /* ── Entra no modo edição de uma célula ─────────────────────── */
  window.entrarEdit = function (celEl) {
    if (celEl.classList.contains('cel-disabled') || celEl.classList.contains('cel-inativa')) return;
    celEl.classList.add('editando');
    var input = celEl.querySelector('.cel-ini');
    if (input) setTimeout(function () { input.focus(); }, 50);
  };

  /* ── Sai do modo edição (onblur com delay para tab entre inputs) */
  window.sairEdit = function (celEl) {
    setTimeout(function () {
      if (!celEl.contains(document.activeElement)) {
        celEl.classList.remove('editando');
        sincronizarRange(celEl);
      }
    }, 150);
  };

  /* ── Ativa/desativa célula via checkbox ─────────────────────── */
  window.toggleCelula = function (chkEl) {
    var celEl = chkEl.closest('.disp-celula');
    var ini   = celEl.querySelector('.cel-ini');
    var fim   = celEl.querySelector('.cel-fim');

    if (chkEl.checked) {
      /* Ativar: preenche com padrão se vazio, habilita inputs */
      if (!ini.value) ini.value = celEl.dataset.padraoIni;
      if (!fim.value) fim.value = celEl.dataset.padraoFim;
      ini.disabled = false;
      fim.disabled = false;
      celEl.classList.remove('cel-inativa');
      celEl.classList.add('cel-ativa');
      /* Atualiza display compacto */
      var compact = celEl.querySelector('.cel-compact');
      compact.innerHTML =
        '<div class="cel-range" onclick="entrarEdit(this.closest(\'.disp-celula\'))">' +
          ini.value + ' – ' + fim.value +
        '</div>' +
        '<div class="cel-slots" id="slots-' + celEl.dataset.dia + '-' + celEl.dataset.turno + '"></div>';
    } else {
      /* Desativar */
      ini.disabled = true;
      fim.disabled = true;
      celEl.classList.remove('cel-ativa', 'editando');
      celEl.classList.add('cel-inativa');
      celEl.querySelector('.cel-compact').innerHTML =
        '<span class="cel-label">Não atende</span>';
    }
    recalcularTudo();
  };

  /* ── Toggle de dia inteiro ──────────────────────────────────── */
  window.toggleDia = function (dia, ativo) {
    var rowEl = document.getElementById('row-dia-' + dia);
    if (!rowEl) return;
    rowEl.classList.toggle('dia-off', !ativo);

    TURNOS.forEach(function (turno) {
      var celEl  = document.getElementById('cel-' + dia + '-' + turno);
      var chkEl  = celEl ? celEl.querySelector('.cel-check') : null;
      if (!celEl || !chkEl) return;

      if (ativo) {
        /* Reabilita conforme estado anterior do checkbox */
        chkEl.disabled = false;
        celEl.classList.remove('cel-disabled');
        if (chkEl.checked) {
          celEl.classList.add('cel-ativa');
          celEl.querySelectorAll('.cel-ini,.cel-fim').forEach(function(i){ i.disabled=false; });
        } else {
          celEl.classList.add('cel-inativa');
        }
      } else {
        /* Desabilita tudo */
        celEl.classList.remove('cel-ativa','cel-inativa','editando');
        celEl.classList.add('cel-disabled');
        chkEl.disabled = true;
        celEl.querySelectorAll('.cel-ini,.cel-fim').forEach(function(i){ i.disabled=true; });
      }
    });
    recalcularTudo();
  };

  /* ── Lê estado de turnos de um dia ─────────────────────────── */
  function getTurnosDia(dia) {
    return TURNOS.map(function (turno) {
      var cel = document.getElementById('cel-' + dia + '-' + turno);
      if (!cel || !cel.classList.contains('cel-ativa')) return null;
      return {
        turno: turno,
        ini: cel.querySelector('.cel-ini').value,
        fim: cel.querySelector('.cel-fim').value,
      };
    }).filter(Boolean);
  }

  /* ── Aplica lista de turnos a um dia ───────────────────────── */
  function setTurnosDia(dia, turnosData) {
    /* Ativa o toggle do dia */
    var toggle = document.getElementById('toggle-dia-' + dia);
    if (toggle && !toggle.checked) {
      toggle.checked = true;
      toggleDia(dia, true);
    }

    TURNOS.forEach(function (turno) {
      var celEl = document.getElementById('cel-' + dia + '-' + turno);
      if (!celEl) return;
      var chkEl = celEl.querySelector('.cel-check');
      var data  = turnosData.find(function(t){ return t.turno===turno; });

      if (data) {
        /* Atualiza horas nos inputs ocultos */
        var ini = celEl.querySelector('.cel-ini');
        var fim = celEl.querySelector('.cel-fim');
        if (ini) ini.value = data.ini;
        if (fim) fim.value = data.fim;
        /* Ativa célula se ainda não estava */
        if (!celEl.classList.contains('cel-ativa')) {
          chkEl.checked = true;
          toggleCelula(chkEl);
        } else {
          sincronizarRange(celEl);
        }
      } else {
        /* Desativa se estava ativa */
        if (celEl.classList.contains('cel-ativa')) {
          chkEl.checked = false;
          toggleCelula(chkEl);
        }
      }
    });
  }

  /* ── Aplicar padrão seg-sex (copia segunda para ter–sex) ────── */
  window.aplicarPadraoSegSex = function () {
    var turnos = getTurnosDia(1); // Segunda-feira
    if (!turnos.length) {
      var primeiroComDados = [2,3,4,5].find(function(d){
        return getTurnosDia(d).length > 0;
      });
      if (primeiroComDados !== undefined) {
        turnos = getTurnosDia(primeiroComDados);
      }
    }
    if (!turnos.length) {
      alert('Configure ao menos um turno de um dia útil primeiro.');
      return;
    }
    [1,2,3,4,5].forEach(function (d) { setTurnosDia(d, turnos); });
    recalcularTudo();
  };

  /* ── Salvar períodos padrão (modal "Editar períodos") ────────── */
  window.salvarPeriodos = function () {
    var keys = ['manha','tarde','noite'];
    keys.forEach(function (k) {
      var ini = document.getElementById('per-' + k + '-ini').value;
      var fim = document.getElementById('per-' + k + '-fim').value;
      /* Atualiza inputs hidden do form */
      document.getElementById('f-' + k + '-ini').value = ini;
      document.getElementById('f-' + k + '-fim').value = fim;
      /* Atualiza cabeçalho da coluna */
      var hdr = document.getElementById('hdr-' + k);
      if (hdr) hdr.textContent = ini + '–' + fim;
      /* Atualiza data-padrao-ini/fim nas células inativas */
      document.querySelectorAll('.disp-celula[data-turno="'+k+'"]').forEach(function (cel) {
        cel.dataset.padraoIni = ini;
        cel.dataset.padraoFim = fim;
        if (cel.classList.contains('cel-inativa')) {
          var iniI = cel.querySelector('.cel-ini');
          var fimI = cel.querySelector('.cel-fim');
          if (iniI) iniI.value = ini;
          if (fimI) fimI.value = fim;
        }
      });
    });
    bootstrap.Modal.getInstance(document.getElementById('modal-periodos')).hide();
  };

  /* ── Sincroniza data_fim min no modal adicionar ─────────────── */
  var addIni = document.getElementById('add-exc-ini');
  var addFim = document.getElementById('add-exc-fim');
  if (addIni && addFim) {
    addIni.addEventListener('change', function () {
      addFim.min = this.value;
      if (addFim.value && addFim.value < this.value) addFim.value = this.value;
    });
  }

  /* ── Inicializa popovers ────────────────────────────────────── */
  document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function (el) {
    new bootstrap.Popover(el, { container: 'body' });
  });

  /* ── Gira ícone do collapse de config ───────────────────────── */
  var collapseConfig = document.getElementById('collapse-config');
  var iconCollapse   = document.getElementById('icon-collapse-config');
  if (collapseConfig && iconCollapse) {
    collapseConfig.addEventListener('hide.bs.collapse', function () {
      iconCollapse.style.transform = 'rotate(180deg)';
    });
    collapseConfig.addEventListener('show.bs.collapse', function () {
      iconCollapse.style.transform = 'rotate(0deg)';
    });
  }

  /* ── Cálculo inicial ao carregar ────────────────────────────── */
  recalcularTudo();

})();
</script>
@endsection
