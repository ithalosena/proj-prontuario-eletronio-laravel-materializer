@php
$configData     = Helper::appClasses();
$voltarUrl      = url()->previous('/agendamentos');
$profLogado     = $profissionalLogado;
$iniciaisProf   = $profLogado
    ? collect(explode(' ', $profLogado->nome ?? 'P'))
        ->filter()->map(fn($p) => strtoupper($p[0]))->take(2)->implode('')
    : null;
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Novo Agendamento')

{{-- Breadcrumb: Início > Agendamentos > Novo --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',            'url' => '/'],
      ['label' => 'Agendamentos',      'url' => '/agendamentos'],
      ['label' => 'Novo Agendamento',  'url' => null],
    ]
  ])
@endpush

@section('vendor-style')
<link rel="stylesheet" href="{{ asset(mix('assets/vendor/libs/select2/select2.css')) }}" />
<link rel="stylesheet" href="{{ asset(mix('assets/vendor/libs/flatpickr/flatpickr.css')) }}" />
@endsection

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================ --}}
  {{-- HERO HEADER                                                        --}}
  {{-- ================================================================ --}}
  <div class="card mb-4">
    <div class="card-body py-4">
      <div class="d-flex flex-wrap align-items-center gap-4">

        <div class="flex-shrink-0">
          <div class="avatar avatar-xl">
            @if($iniciaisProf)
              <span class="avatar-initial rounded-circle bg-label-primary"
                style="font-size:1.4rem; width:64px; height:64px; display:flex; align-items:center; justify-content:center;">
                {{ $iniciaisProf }}
              </span>
            @else
              <span class="avatar-initial rounded-circle bg-label-secondary"
                style="font-size:1.8rem; width:64px; height:64px; display:flex; align-items:center; justify-content:center;">
                <i class="mdi mdi-calendar-plus"></i>
              </span>
            @endif
          </div>
        </div>

        <div class="flex-grow-1">
          <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            @if($profLogado)
              <h4 class="mb-0">{{ $profLogado->nome }}</h4>
              @if($profLogado->especialidade)
                <span class="badge rounded-pill bg-label-primary">{{ $profLogado->especialidade }}</span>
              @endif
            @else
              <h4 class="mb-0">Novo Agendamento</h4>
            @endif
            <span class="badge rounded-pill bg-label-warning">Pendente</span>
          </div>
          <div class="d-flex gap-3 text-muted small">
            <span><i class="mdi mdi-calendar-today-outline me-1"></i>{{ now()->format('d/m/Y') }}</span>
          </div>
        </div>

        <div class="ms-auto">
          <a href="{{ $voltarUrl }}" class="btn btn-default btn-sm">
            <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
          </a>
        </div>

      </div>
    </div>
  </div>

  {{-- Flash / erros de validação --}}
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
  {{-- FORMULÁRIO                                                         --}}
  {{-- ================================================================ --}}
  <div class="card">
    <div class="card-body">
      <form action="/cadastrar-agendamento" method="POST" class="browser-default-validation">
        @csrf

        <div class="row g-4">

          {{-- Paciente (autocomplete via partial reutilizável) --}}
          <div class="col-md-6">
            @include('content.pages.partials._paciente_autocomplete', [
              'fieldName'   => 'paciente_id',
              'preSelected' => $pacienteAnterior,
            ])
          </div>

          {{-- Profissional --}}
          <div class="col-md-6">
            <label class="form-label" for="profissional_id">Profissional <span class="text-danger">*</span></label>
            <select name="profissional_id" id="profissional_id" class="form-select select2" required
                    {{ $profLogado ? 'disabled' : '' }}>
              <option value="">Selecione...</option>
              @foreach($profissionais as $prof)
                <option value="{{ $prof->id }}"
                  {{ old('profissional_id', $profLogado?->id) == $prof->id ? 'selected' : '' }}>
                  {{ $prof->nome }}{{ $prof->especialidade ? ' — ' . $prof->especialidade : '' }}
                </option>
              @endforeach
            </select>
            {{-- Hidden para enviar quando o campo está disabled (profissional logado) --}}
            @if($profLogado)
              <input type="hidden" name="profissional_id" value="{{ $profLogado->id }}">
            @endif
          </div>

          {{-- Tipo de Consulta --}}
          <div class="col-md-4">
            <label class="form-label" for="tipo">Tipo de Consulta <span class="text-danger">*</span></label>
            <select name="tipo" id="tipo" class="form-select" required>
              <option value="">Selecione...</option>
              @foreach($tipos as $tipo)
                <option value="{{ $tipo->nome }}" {{ old('tipo') == $tipo->nome ? 'selected' : '' }}>
                  {{ $tipo->nome }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Data --}}
          <div class="col-md-4">
            <label class="form-label" for="data_agendamento">Data <span class="text-danger">*</span></label>
            <input type="text" class="form-control flatpickr-date" id="data_agendamento"
                   placeholder="dd/mm/aaaa" autocomplete="off">
          </div>

          {{-- Hora (AJAX slots) --}}
          <div class="col-md-4">
            <label class="form-label" for="hora_agendamento">Horário <span class="text-danger">*</span></label>
            <select id="hora_agendamento" class="form-select" disabled>
              <option value="">Selecione data e profissional primeiro</option>
            </select>
            {{-- Campo oculto para submissão da data+hora combinada --}}
            <input type="hidden" name="data_hora" id="data_hora" value="{{ old('data_hora') }}" required>
          </div>

          {{-- Observação --}}
          <div class="col-12">
            <label class="form-label" for="observacao">Observação</label>
            <textarea name="observacao" id="observacao" class="form-control" rows="3"
                      placeholder="Informações adicionais sobre o agendamento...">{{ old('observacao') }}</textarea>
          </div>

        </div>

        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="mdi mdi-calendar-check-outline me-1"></i>Agendar
          </button>
          <a href="{{ $voltarUrl }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>

      </form>
    </div>
  </div>

</div>
@endsection

@section('vendor-script')
<script src="{{ asset(mix('assets/vendor/libs/select2/select2.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/libs/flatpickr/flatpickr.js')) }}"></script>
@endsection

@section('page-script')
<script>
// Inicializa Select2 no campo de profissional
$(document).ready(function () {
  $('.select2').select2({ placeholder: 'Selecione...', width: '100%' });
});

// Flatpickr na data — somente datas futuras
var fpData = flatpickr('#data_agendamento', {
  dateFormat: 'd/m/Y',
  altInput: false,
  minDate: 'today',
  locale: {
    firstDayOfWeek: 0,
    weekdays: {
      shorthand: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'],
      longhand:  ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado']
    },
    months: {
      shorthand: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
      longhand:  ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro']
    }
  },
  onChange: function (selectedDates, dateStr) {
    // Ao mudar a data, reconverte para Y-m-d e recarrega slots
    if (selectedDates.length) {
      carregarSlots();
    }
  }
});

// Recarrega slots quando profissional muda
document.getElementById('profissional_id').addEventListener('change', carregarSlots);

function carregarSlots() {
  var profId = document.getElementById('profissional_id').value;
  var dataSel = fpData.selectedDates[0];

  var horaSelect  = document.getElementById('hora_agendamento');
  var dataHoraHid = document.getElementById('data_hora');

  if (!profId || !dataSel) {
    horaSelect.innerHTML = '<option value="">Selecione data e profissional primeiro</option>';
    horaSelect.disabled  = true;
    return;
  }

  // Formata data como Y-m-d para a URL
  var ano  = dataSel.getFullYear();
  var mes  = String(dataSel.getMonth() + 1).padStart(2, '0');
  var dia  = String(dataSel.getDate()).padStart(2, '0');
  var dataFmt = ano + '-' + mes + '-' + dia;

  horaSelect.innerHTML = '<option value="">Carregando...</option>';
  horaSelect.disabled  = true;

  fetch('/agendamentos/slots/' + profId + '/' + dataFmt)
    .then(function (r) { return r.json(); })
    .then(function (slots) {
      horaSelect.innerHTML = '';
      if (!slots.length) {
        horaSelect.innerHTML = '<option value="">Sem horários disponíveis</option>';
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

      // Preenche data_hora ao selecionar horário
      horaSelect.addEventListener('change', function () {
        dataHoraHid.value = this.value;
      });
    })
    .catch(function () {
      horaSelect.innerHTML = '<option value="">Erro ao carregar horários</option>';
    });
}
</script>
@endsection
