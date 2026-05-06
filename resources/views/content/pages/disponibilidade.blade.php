@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Minha Disponibilidade')

{{-- Breadcrumb: Início > Minha Disponibilidade --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',               'url' => '/'],
      ['label' => 'Minha Disponibilidade','url' => null],
    ]
  ])
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================ --}}
  {{-- HEADER DA PÁGINA                                                  --}}
  {{-- ================================================================ --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h4 class="mb-0">Minha Disponibilidade</h4>
      <p class="text-muted small mb-0 mt-1">Configure seus horários de atendimento por dia da semana</p>
    </div>
    <a href="/agendamentos" class="btn btn-outline-secondary">
      <i class="mdi mdi-arrow-left me-1"></i>Voltar para Agenda
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

  {{-- ================================================================ --}}
  {{-- FORMULÁRIO DE DISPONIBILIDADE                                     --}}
  {{-- ================================================================ --}}
  <div class="row justify-content-center">
    <div class="col-12 col-lg-7">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">
            <i class="mdi mdi-clock-outline me-2"></i>{{ $profissional->nome }}
          </h5>
          <p class="text-muted small mb-0 mt-1">
            Os horários configurados como ativos serão exibidos como opções ao agendar uma consulta com você.
          </p>
        </div>
        <div class="card-body">

          <form action="/disponibilidade/{{ $profissional->id }}" method="POST">
            @csrf
            @method('PUT')

            @for($dia = 0; $dia <= 6; $dia++)
            @php
              $disp = $disponibilidades->get($dia);
              $ativo = $disp ? $disp->ativo : false;
            @endphp
            <div class="border rounded p-3 mb-3 {{ $ativo ? '' : 'bg-light' }}">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <strong>{{ $diasSemana[$dia] }}</strong>
                <div class="form-check form-switch mb-0">
                  <input class="form-check-input disp-ativo-toggle" type="checkbox"
                         name="dias[{{ $dia }}][ativo]" value="1"
                         id="ativo_{{ $dia }}"
                         data-dia="{{ $dia }}"
                         {{ $ativo ? 'checked' : '' }}>
                  <label class="form-check-label" for="ativo_{{ $dia }}">
                    {{ $ativo ? 'Ativo' : 'Inativo' }}
                  </label>
                </div>
              </div>
              <input type="hidden" name="dias[{{ $dia }}][dia_semana]" value="{{ $dia }}">
              <div class="row g-2 disp-horarios-{{ $dia }}" style="{{ !$ativo ? 'display:none' : '' }}">
                <div class="col-6">
                  <label class="form-label small mb-1">Início</label>
                  <input type="time" class="form-control"
                         name="dias[{{ $dia }}][hora_inicio]"
                         value="{{ $disp ? substr($disp->hora_inicio, 0, 5) : '08:00' }}">
                </div>
                <div class="col-6">
                  <label class="form-label small mb-1">Fim</label>
                  <input type="time" class="form-control"
                         name="dias[{{ $dia }}][hora_fim]"
                         value="{{ $disp ? substr($disp->hora_fim, 0, 5) : '17:00' }}">
                </div>
              </div>
            </div>
            @endfor

            <button type="submit" class="btn btn-primary w-100 mt-2">
              <i class="mdi mdi-content-save-outline me-1"></i>Salvar Disponibilidade
            </button>
          </form>

        </div>
      </div>
    </div>
  </div>

</div>

@endsection

@section('page-script')
<script>
// Toggle visibilidade dos horários ao ativar/desativar o dia
document.querySelectorAll('.disp-ativo-toggle').forEach(function (toggle) {
  toggle.addEventListener('change', function () {
    var dia      = this.dataset.dia;
    var horarios = document.querySelector('.disp-horarios-' + dia);
    var label    = this.nextElementSibling;
    if (horarios) {
      horarios.style.display = this.checked ? '' : 'none';
    }
    if (label) {
      label.textContent = this.checked ? 'Ativo' : 'Inativo';
    }
    this.closest('.border').classList.toggle('bg-light', !this.checked);
  });
});
</script>
@endsection
