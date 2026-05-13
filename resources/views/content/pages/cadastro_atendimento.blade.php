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

@section('title', 'Novo Atendimento')

{{-- Breadcrumb: Início > Atendimentos > Novo Atendimento --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',           'url' => '/'],
      ['label' => 'Atendimentos',     'url' => '/atendimentos'],
      ['label' => 'Novo Atendimento', 'url' => null],
    ]
  ])
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================ --}}
  {{-- HERO HEADER                                                        --}}
  {{-- Quando profissional logado: exibe dados do profissional + data.   --}}
  {{-- Quando admin/recepcionista: exibe ícone genérico + título.        --}}
  {{-- UX-12: sem duplicidade — o profissional aparece APENAS aqui.      --}}
  {{-- ================================================================ --}}
  <div class="card mb-4">
    <div class="card-body py-4">
      <div class="d-flex flex-wrap align-items-center gap-4">

        {{-- Avatar: iniciais do profissional (verde) ou ícone genérico (cinza para admin) --}}
        <div class="flex-shrink-0">
          <div class="avatar avatar-xl">
            @if($iniciaisProf)
              <span class="avatar-initial rounded-circle bg-label-success"
                style="font-size:1.4rem; width:64px; height:64px; display:flex; align-items:center; justify-content:center;">
                {{ $iniciaisProf }}
              </span>
            @else
              <span class="avatar-initial rounded-circle bg-label-secondary"
                style="font-size:1.8rem; width:64px; height:64px; display:flex; align-items:center; justify-content:center;">
                <i class="mdi mdi-stethoscope"></i>
              </span>
            @endif
          </div>
        </div>

        {{-- Dados do profissional (quando logado) ou título genérico --}}
        <div class="flex-grow-1">
          <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            @if($profissionalLogado)
              <h4 class="mb-0">{{ $profissionalLogado->nome }}</h4>
              @if($profissionalLogado->especialidade)
                <span class="badge rounded-pill bg-label-primary">{{ $profissionalLogado->especialidade }}</span>
              @endif
            @else
              <h4 class="mb-0">Novo Atendimento</h4>
            @endif
            <span class="badge rounded-pill bg-label-info">Novo Atendimento</span>
          </div>
          <div class="d-flex flex-wrap gap-3 text-muted small">
            @if($profissionalLogado && $profissionalLogado->registro_profissional)
              <span><i class="mdi mdi-card-account-details-outline me-1"></i>{{ $profissionalLogado->registro_profissional }}</span>
            @endif
            {{-- Data de abertura do atendimento (hoje) --}}
            <span><i class="mdi mdi-calendar-today-outline me-1"></i>{{ now()->format('d/m/Y') }}</span>
          </div>
        </div>

        <div class="flex-shrink-0">
          <a href="{{ $voltarUrl }}" class="btn btn-default">
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

