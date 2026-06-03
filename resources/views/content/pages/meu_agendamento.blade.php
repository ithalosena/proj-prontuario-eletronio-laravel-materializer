@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Agendar Consulta')

{{-- Breadcrumb: Início > Meus Agendamentos > Agendar --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',            'url' => '/'],
      ['label' => 'Meus Agendamentos', 'url' => '/meus-agendamentos'],
      ['label' => 'Agendar Consulta',  'url' => null],
    ]
  ])
@endpush

@section('page-style')
<style>
/* ================================================================
   Wizard de agendamento — mobile-first (ST-09C)
   Todos os containers são col-12 por padrão; desktop herda col-md-X
   ================================================================ */

/* Indicador de passos */
.wizard-steps {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}
.wizard-step-dot {
  flex: 1;
  height: 4px;
  border-radius: 2px;
  background: #e0e0e0;
  transition: background 0.3s;
}
.wizard-step-dot.active {
  background: var(--bs-primary);
}
.wizard-step-dot.done {
  background: #72e128;
}

/* Cards de tipo e profissional — touch-friendly */
.card-opcao {
  cursor: pointer;
  border: 2px solid transparent;
  transition: border-color 0.2s, background 0.2s;
  min-height: 64px;
  border-radius: 0.5rem;
}
.card-opcao:hover,
.card-opcao.selecionado {
  border-color: var(--bs-primary);
  background: rgba(61, 170, 74, .06);
}
.card-opcao.selecionado .check-icon {
  display: inline-block !important;
}

/* Botões mínimo 44px para facilitar toque */
.btn-wizard {
  min-height: 44px;
  font-size: 1rem;
}
</style>
@endsection

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================ --}}
  {{-- CABEÇALHO                                                         --}}
  {{-- ================================================================ --}}
  <div class="card mb-4">
    <div class="card-body py-4">
      <div class="d-flex flex-wrap align-items-center gap-4">
        <div class="flex-shrink-0">
          <span class="avatar-initial rounded-circle bg-label-primary d-flex align-items-center justify-content-center"
                style="width:56px; height:56px; font-size:1.5rem;">
            <i class="mdi mdi-calendar-plus"></i>
          </span>
        </div>
        <div class="flex-grow-1">
          <h4 class="mb-0">Agendar Consulta</h4>
          <p class="text-muted small mb-0">{{ $paciente->nome }}</p>
        </div>
        <a href="/meus-agendamentos" class="btn btn-default btn-sm ms-auto">
          <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
        </a>
      </div>
    </div>
  </div>

  {{-- Flash / erros --}}
  @if($errors->any())
  <div class="alert alert-danger alert-dismissible mb-4" role="alert">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
  </div>
  @endif

  {{-- ================================================================ --}}
  {{-- WIZARD (4 passos)                                                 --}}
  {{-- ================================================================ --}}
  <div class="card">
    <div class="card-body">

      {{-- Indicador visual de passos --}}
      <div class="d-flex justify-content-between align-items-center mb-1">
        <small class="text-muted" id="wizard-label">Passo 1 de 4</small>
        <small class="text-muted" id="wizard-step-title">Tipo de Consulta</small>
      </div>
      <div class="wizard-steps mb-4">
        <div class="wizard-step-dot active" id="dot-1"></div>
        <div class="wizard-step-dot" id="dot-2"></div>
        <div class="wizard-step-dot" id="dot-3"></div>
        <div class="wizard-step-dot" id="dot-4"></div>
      </div>

      {{-- Formulário (submissão apenas no passo 4) --}}
      <form action="/agendar-consulta" method="POST" id="wizardForm">
        @csrf
        <input type="hidden" name="paciente_id"     value="{{ $paciente->id }}">
        <input type="hidden" name="profissional_id" id="wiz_profissional_id" value="">
        <input type="hidden" name="tipo"            id="wiz_tipo"            value="">
        <input type="hidden" name="data_hora"       id="wiz_data_hora"       value="">
        <input type="hidden" name="observacao"      id="wiz_observacao"      value="">

        {{-- ---------------------------------------------------------- --}}
        {{-- PASSO 1: Tipo de consulta                                   --}}
        {{-- ---------------------------------------------------------- --}}
        <div id="step-1">
          <h5 class="mb-3">Qual tipo de consulta você precisa?</h5>
          <div class="row g-3">
            @foreach($tipos as $tipo)
            <div class="col-12 col-sm-6 col-md-4">
              <div class="card card-opcao p-3 d-flex flex-row align-items-center gap-3"
                   onclick="selecionarTipo('{{ $tipo->nome }}', this)">
                <i class="mdi mdi-stethoscope mdi-24px text-primary"></i>
                <div class="flex-grow-1">
                  <p class="fw-semibold mb-0 small">{{ $tipo->nome }}</p>
                </div>
                <i class="mdi mdi-check-circle text-primary check-icon" style="display:none"></i>
              </div>
            </div>
            @endforeach
          </div>
        </div>

        {{-- ---------------------------------------------------------- --}}
        {{-- PASSO 2: Profissional                                        --}}
        {{-- ---------------------------------------------------------- --}}
        <div id="step-2" style="display:none">
          <h5 class="mb-3">Escolha o profissional</h5>
          <div class="row g-3" id="lista-profissionais">
            @foreach($profissionais as $prof)
            @php
              $inics = collect(explode(' ', $prof->nome ?? 'P'))
                  ->filter()->map(fn($p) => strtoupper($p[0]))->take(2)->implode('');
            @endphp
            <div class="col-12 col-sm-6 col-md-4">
              <div class="card card-opcao p-3 d-flex flex-row align-items-center gap-3"
                   data-id="{{ $prof->id }}"
                   data-especialidade="{{ $prof->especialidade }}"
                   onclick="selecionarProfissional({{ $prof->id }}, this)">
                <span class="avatar-initial rounded-circle bg-label-info d-flex align-items-center justify-content-center"
                      style="width:40px; height:40px; font-size:0.85rem; flex-shrink:0">
                  {{ $inics }}
                </span>
                <div class="flex-grow-1 overflow-hidden">
                  <p class="fw-semibold mb-0 small text-truncate">{{ $prof->nome }}</p>
                  <p class="text-muted mb-0" style="font-size:0.78rem">{{ $prof->especialidade ?? '-' }}</p>
                </div>
                <i class="mdi mdi-check-circle text-primary check-icon" style="display:none; flex-shrink:0"></i>
              </div>
            </div>
            @endforeach
          </div>
        </div>

        {{-- ---------------------------------------------------------- --}}
        {{-- PASSO 3: Data e hora                                         --}}
        {{-- ---------------------------------------------------------- --}}
        <div id="step-3" style="display:none">
          <h5 class="mb-3">Escolha a data e o horário</h5>
          <div class="row g-4">
            <div class="col-12 col-sm-6">
              <label class="form-label" for="wiz_data">Data <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="wiz_data"
                     min="{{ now()->addDay()->format('Y-m-d') }}"
                     onchange="carregarSlotsWizard()">
            </div>
            <div class="col-12 col-sm-6">
              <label class="form-label" for="wiz_hora">Horário <span class="text-danger">*</span></label>
              <select id="wiz_hora" class="form-select" disabled onchange="sincronizarDataHora()">
                <option value="">Selecione a data primeiro</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label" for="wiz_obs">Observação (opcional)</label>
              <textarea id="wiz_obs" class="form-control" rows="3"
                        placeholder="Descreva brevemente o motivo da consulta..."></textarea>
            </div>
          </div>
        </div>

        {{-- ---------------------------------------------------------- --}}
        {{-- PASSO 4: Confirmação                                         --}}
        {{-- ---------------------------------------------------------- --}}
        <div id="step-4" style="display:none">
          <h5 class="mb-3">Confirme os dados do agendamento</h5>
          <div class="card bg-label-primary rounded mb-4">
            <div class="card-body">
              <div class="row g-3">
                <div class="col-12 col-sm-6">
                  <p class="text-muted small mb-1">Tipo de Consulta</p>
                  <p class="fw-semibold mb-0" id="resumo-tipo">—</p>
                </div>
                <div class="col-12 col-sm-6">
                  <p class="text-muted small mb-1">Profissional</p>
                  <p class="fw-semibold mb-0" id="resumo-profissional">—</p>
                </div>
                <div class="col-12 col-sm-6">
                  <p class="text-muted small mb-1">Data e Hora</p>
                  <p class="fw-semibold mb-0" id="resumo-data-hora">—</p>
                </div>
                <div class="col-12" id="resumo-obs-row" style="display:none">
                  <p class="text-muted small mb-1">Observação</p>
                  <p class="fw-semibold mb-0" id="resumo-obs">—</p>
                </div>
              </div>
            </div>
          </div>
          <p class="text-muted small mb-0">
            <i class="mdi mdi-information-outline me-1"></i>
            Seu agendamento ficará como <strong>Pendente</strong> até ser confirmado pela equipe de saúde.
          </p>
        </div>

        {{-- ---------------------------------------------------------- --}}
        {{-- NAVEGAÇÃO DO WIZARD                                          --}}
        {{-- ---------------------------------------------------------- --}}
        <div class="d-flex justify-content-between mt-4 gap-2">
          <button type="button" class="btn btn-outline-secondary btn-wizard" id="btn-voltar"
                  style="display:none" onclick="wizardVoltar()">
            <i class="mdi mdi-arrow-left me-1"></i>Voltar
          </button>
          <button type="button" class="btn btn-primary btn-wizard ms-auto" id="btn-avancar"
                  style="display:none" onclick="wizardAvancar()">
            Próximo<i class="mdi mdi-arrow-right ms-1"></i>
          </button>
          <button type="submit" class="btn btn-success btn-wizard ms-auto" id="btn-confirmar"
                  style="display:none" disabled>
            <i class="mdi mdi-calendar-check me-1"></i>Confirmar Agendamento
          </button>
        </div>

      </form>
    </div>
  </div>

</div>
@endsection

@section('page-script')
<script>
// ================================================================
// Wizard de agendamento — ST-09C
// Estado: tipo, profissional_id, profissionalNome, data, hora, obs
// ================================================================

var wizardState = {
  step:            1,
  tipo:            '',
  profissionalId:  '',
  profissionalNome:'',
  data:            '',
  hora:            '',
  obs:             ''
};

var stepTitles = ['Tipo de Consulta', 'Profissional', 'Data e Horário', 'Confirmação'];

// Exibe o passo correto e atualiza dots e botões
function renderStep() {
  var s = wizardState.step;

  // Mostra/oculta passos
  for (var i = 1; i <= 4; i++) {
    document.getElementById('step-' + i).style.display = (i === s) ? '' : 'none';
  }

  // Atualiza dots
  for (var j = 1; j <= 4; j++) {
    var dot = document.getElementById('dot-' + j);
    dot.className = 'wizard-step-dot';
    if (j < s)       dot.classList.add('done');
    else if (j === s) dot.classList.add('active');
  }

  // Label
  document.getElementById('wizard-label').textContent = 'Passo ' + s + ' de 4';
  document.getElementById('wizard-step-title').textContent = stepTitles[s - 1];

  // Botões — exclusão mútua: PRÓXIMO em 1–3, CONFIRMAR exclusivamente no 4
  document.getElementById('btn-voltar').style.display    = s > 1     ? '' : 'none';
  document.getElementById('btn-avancar').style.display   = s < 4     ? '' : 'none';
  document.getElementById('btn-confirmar').style.display = s === 4   ? '' : 'none';
  document.getElementById('btn-confirmar').disabled      = s !== 4;
}

// Avança para o próximo passo com validação básica
function wizardAvancar() {
  var s = wizardState.step;
  if (s >= 4) return;   // Bug A: impede avançar além do último passo

  if (s === 1 && !wizardState.tipo) {
    alert('Selecione o tipo de consulta.');
    return;
  }
  if (s === 2 && !wizardState.profissionalId) {
    alert('Selecione um profissional.');
    return;
  }
  if (s === 3) {
    if (!wizardState.data || !wizardState.hora) {
      alert('Selecione a data e o horário.');
      return;
    }
    // Preenche campos ocultos antes de mostrar resumo
    document.getElementById('wiz_profissional_id').value = wizardState.profissionalId;
    document.getElementById('wiz_tipo').value            = wizardState.tipo;
    document.getElementById('wiz_data_hora').value       = wizardState.hora; // Y-m-d H:i:s
    wizardState.obs = document.getElementById('wiz_obs').value;
    document.getElementById('wiz_observacao').value      = wizardState.obs;
    preencherResumo();
  }

  wizardState.step++;
  renderStep();
}

// Volta um passo
function wizardVoltar() {
  if (wizardState.step > 1) {
    wizardState.step--;
    renderStep();
  }
}

// Seleção de tipo
function selecionarTipo(nome, el) {
  wizardState.tipo = nome;
  document.querySelectorAll('#step-1 .card-opcao').forEach(function (c) {
    c.classList.remove('selecionado');
  });
  el.classList.add('selecionado');

  // Filtra profissionais que têm a especialidade compatível
  filtrarProfissionais(nome);
}

// Filtra a lista de profissionais (passo 2) por especialidade compatível com o tipo selecionado.
// Usa correspondência parcial de string (data-especialidade × tipo). Fallback: exibe todos
// quando nenhum profissional bate — garante que o wizard nunca fique sem opção.
function filtrarProfissionais(tipo) {
  var tipoNorm    = (tipo || '').toLowerCase().trim();
  var cards       = document.querySelectorAll('#lista-profissionais .card-opcao');
  var algumVisivel = false;

  cards.forEach(function (card) {
    var esp    = (card.getAttribute('data-especialidade') || '').toLowerCase().trim();
    var exibir = !tipoNorm || esp.includes(tipoNorm) || tipoNorm.includes(esp);
    card.closest('[class*=col]').style.display = exibir ? '' : 'none';
    if (exibir) algumVisivel = true;
  });

  // Fallback: se nenhum profissional tiver especialidade compatível, exibe todos
  if (!algumVisivel) {
    cards.forEach(function (card) {
      card.closest('[class*=col]').style.display = '';
    });
  }
}

// Seleção de profissional
function selecionarProfissional(id, el) {
  wizardState.profissionalId   = id;
  wizardState.profissionalNome = el.querySelector('.fw-semibold').textContent.trim();

  document.querySelectorAll('#step-2 .card-opcao').forEach(function (c) {
    c.classList.remove('selecionado');
  });
  el.classList.add('selecionado');
}

// Carrega slots AJAX quando profissional e data estão preenchidos
function carregarSlotsWizard() {
  wizardState.data = document.getElementById('wiz_data').value;
  var horaSelect   = document.getElementById('wiz_hora');

  if (!wizardState.profissionalId || !wizardState.data) {
    horaSelect.innerHTML = '<option value="">Selecione a data primeiro</option>';
    horaSelect.disabled  = true;
    return;
  }

  horaSelect.innerHTML = '<option value="">Buscando horários disponíveis...</option>';
  horaSelect.disabled  = true;
  wizardState.hora     = '';

  fetch('/agendamentos/slots/' + wizardState.profissionalId + '/' + wizardState.data)
    .then(function (r) { return r.json(); })
    .then(function (slots) {
      horaSelect.innerHTML = '';
      if (!slots.length) {
        horaSelect.innerHTML = '<option value="">Sem horários disponíveis nesta data</option>';
        return;
      }
      horaSelect.innerHTML = '<option value="">Selecione o horário</option>';
      slots.forEach(function (s) {
        var opt = document.createElement('option');
        opt.value       = s.value;
        opt.textContent = s.label;
        horaSelect.appendChild(opt);
      });
      horaSelect.disabled = false;
    })
    .catch(function () {
      horaSelect.innerHTML = '<option value="">Erro ao buscar horários. Tente novamente.</option>';
      horaSelect.disabled  = false;
    });
}

// Sincroniza a hora selecionada no estado
function sincronizarDataHora() {
  wizardState.hora = document.getElementById('wiz_hora').value;
}

// Preenche o resumo do passo 4
function preencherResumo() {
  document.getElementById('resumo-tipo').textContent = wizardState.tipo;
  document.getElementById('resumo-profissional').textContent = wizardState.profissionalNome;

  // Formata data/hora para exibição
  if (wizardState.hora) {
    var dt = new Date(wizardState.hora);
    document.getElementById('resumo-data-hora').textContent =
      dt.toLocaleDateString('pt-BR') + ' às ' + dt.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
  }

  if (wizardState.obs) {
    document.getElementById('resumo-obs-row').style.display = '';
    document.getElementById('resumo-obs').textContent       = wizardState.obs;
  } else {
    document.getElementById('resumo-obs-row').style.display = 'none';
  }
}

// Inicializa no passo 1
renderStep();
</script>
@endsection
