@php
$configData = Helper::appClasses();
$voltarUrl  = url()->previous('/atendimentos');

// Iniciais do profissional para o avatar no hero
$iniciaisProf = $profissionalLogado
    ? collect(explode(' ', $profissionalLogado->nome ?? 'P'))
        ->filter()->map(fn($p) => strtoupper($p[0]))->take(2)->implode('')
    : null;

// $pacienteAnterior é passado pelo controller quando há old('paciente_id') após falha de validação
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Iniciar Atendimento')

{{-- Breadcrumb: Início > Atendimentos > Iniciar Atendimento --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',              'url' => '/'],
      ['label' => 'Atendimentos',        'url' => '/atendimentos'],
      ['label' => 'Iniciar Atendimento', 'url' => null],
    ]
  ])
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================ --}}
  {{-- HERO degradê verde (padrão visual do projeto)                     --}}
  {{-- Porta do atendimento ESPONTÂNEO — o agendado entra pelo Realizar. --}}
  {{-- UX-12: sem duplicidade — o profissional aparece APENAS aqui.      --}}
  {{-- ================================================================ --}}
  <div class="card mb-4 text-white border-0"
       style="background: linear-gradient(105deg, #237030 0%, #3DAA4A 50%, #56cf66 100%);">
    <div class="card-body py-4 px-4 position-relative overflow-hidden">
      <div class="position-absolute rounded-circle"
           style="right:-60px; top:-60px; width:200px; height:200px; border:40px solid rgba(255,255,255,0.07); pointer-events:none;"></div>

      <div class="d-flex flex-wrap align-items-center gap-3 position-relative">

        {{-- Iniciais do profissional (ou ícone genérico) no box translúcido --}}
        <div class="flex-shrink-0 rounded-3 d-flex align-items-center justify-content-center fw-bold"
             style="width:56px; height:56px; background:rgba(255,255,255,0.18); font-size:1.3rem;">
          @if($iniciaisProf)
            {{ $iniciaisProf }}
          @else
            <i class="mdi mdi-stethoscope mdi-24px"></i>
          @endif
        </div>

        {{-- Dados do profissional (quando logado) ou título genérico --}}
        <div class="flex-grow-1 min-width-0">
          <div class="d-flex flex-wrap align-items-center gap-2">
            @if($profissionalLogado)
              <h4 class="mb-0 fw-bold text-white">{{ $profissionalLogado->nome }}</h4>
              @if($profissionalLogado->especialidade)
                <span class="badge rounded-pill" style="background:rgba(255,255,255,.22); color:#fff;">
                  {{ $profissionalLogado->especialidade }}
                </span>
              @endif
            @else
              <h4 class="mb-0 fw-bold text-white">Iniciar Atendimento</h4>
            @endif
            <span class="badge rounded-pill" style="background:rgba(255,255,255,.22); color:#fff;">
              <i class="mdi mdi-account-arrow-right-outline me-1"></i>Espontâneo
            </span>
          </div>
          <div class="d-flex flex-wrap gap-3 mt-1" style="font-size:13px; opacity:.92;">
            @if($profissionalLogado && $profissionalLogado->registro_profissional)
              <span><i class="mdi mdi-card-account-details-outline me-1"></i>{{ $profissionalLogado->registro_profissional }}</span>
            @endif
            {{-- Data de abertura do atendimento (hoje) --}}
            <span><i class="mdi mdi-calendar-today-outline me-1"></i>{{ now()->format('d/m/Y') }}</span>
            <span><i class="mdi mdi-account-search-outline me-1"></i>Selecione o paciente para abrir o episódio</span>
          </div>
        </div>

        <div class="flex-shrink-0">
          <a href="{{ $voltarUrl }}" class="btn btn-sm btn-outline-light">
            <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
          </a>
        </div>

      </div>
    </div>
  </div>

  <form class="browser-default-validation" action="/cadastrar-atendimento" method="POST" id="form-atendimento">
    @csrf

    <div class="row g-4">
      <div class="col-md-7">

        {{-- ============================================================
             Card de Identificação do Paciente
             UX-11b: autocomplete AJAX — digitar ≥ 2 chars dispara fetch
             em /pacientes/buscar?q=. O paciente_id fica em hidden input.
             UX-12: quando profissionalLogado, o select de profissional
             fica hidden — profissional já está registrado no hero.
             ============================================================ --}}
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="mdi mdi-account-search-outline me-2"></i>Identificação do Paciente
              {{-- Especialidade como contexto do tipo de atendimento --}}
              @if($profissionalLogado && $profissionalLogado->especialidade)
                <small class="text-muted fw-normal fs-6 ms-2">· {{ $profissionalLogado->especialidade }}</small>
              @endif
            </h5>
          </div>
          <div class="card-body">

            {{-- Autocomplete AJAX de paciente (partial reutilizável) --}}
            <div class="mb-4">
              @include('content.pages.partials._paciente_autocomplete', [
                'fieldName'   => 'paciente_id',
                'preSelected' => $pacienteAnterior ?? null,
              ])
            </div>

            {{-- Profissional: hidden quando logado como profissional (já no hero); select para admin --}}
            @if($profissionalLogado)
              <input type="hidden" name="profissional_id" value="{{ $profissionalLogado->id }}">
            @else
              <div class="form-floating form-floating-outline">
                <select name="profissional_id" id="profissional_id"
                  class="form-select @error('profissional_id') is-invalid @enderror" required>
                  <option disabled {{ old('profissional_id') ? '' : 'selected' }} value="">Selecione o profissional</option>
                  @foreach($profissionais as $profissional)
                  <option value="{{ $profissional->id }}" {{ old('profissional_id') == $profissional->id ? 'selected' : '' }}>
                    {{ $profissional->nome }} — {{ $profissional->especialidade }}
                  </option>
                  @endforeach
                </select>
                <label for="profissional_id">Profissional Responsável *</label>
                @error('profissional_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            @endif

          </div>
        </div>

        {{-- Botões de ação --}}
        <div class="d-flex gap-2 mb-2">
          <button type="submit" class="btn btn-primary">
            <i class="mdi mdi-folder-open-outline me-1"></i>Abrir Atendimento
          </button>
          <a href="{{ $voltarUrl }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>

      </div>
    </div>

  </form>
</div>

@endsection

