@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Editar Profissional')

{{-- Breadcrumb: Início > Profissionais > Editar --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',        'url' => '/'],
      ['label' => 'Profissionais', 'url' => '/profissionais'],
      ['label' => 'Editar',        'url' => null],
    ]
  ])
@endpush

@section('content')
@php
  // Iniciais do profissional para o avatar do hero (mesmo padrão de editar_paciente.blade.php)
  $iniciaisProf = collect(explode(' ', $prof->nome))->filter()->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('');
  $iniciaisProf = mb_strtoupper($iniciaisProf ?: 'P');
@endphp

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================ --}}
  {{-- HERO — avatar + identificação (padrão de editar_paciente.blade.php) --}}
  {{-- ================================================================ --}}
  <div class="card mb-4">
    <div class="card-body py-4">
      <div class="d-flex flex-wrap align-items-center gap-4">
        <div class="flex-shrink-0">
          <div class="avatar avatar-xl">
            <span class="avatar-initial rounded-circle bg-label-primary"
                  style="font-size:1.4rem; width:64px; height:64px; display:flex; align-items:center; justify-content:center;">
              {{ $iniciaisProf }}
            </span>
          </div>
        </div>
        <div class="flex-grow-1">
          <h4 class="mb-1">Editar — {{ $prof->nome }}</h4>
          <div class="d-flex flex-wrap gap-3 text-muted small">
            @if($prof->especialidade)<span><i class="mdi mdi-stethoscope me-1"></i>{{ $prof->especialidade }}</span>@endif
            @if($prof->registro_profissional)<span><i class="mdi mdi-card-account-details-outline me-1"></i>{{ $prof->registro_profissional }}</span>@endif
          </div>
        </div>
        <div class="d-flex flex-column gap-2 ms-auto">
          <a href="{{ url()->previous('/profissionais') }}" class="btn btn-outline-secondary btn-sm">
            <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-8 card mt-1">
    <div class="card-body">

      @if(session('success'))
      <div class="alert alert-success alert-dismissible mb-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
      </div>
      @endif

      <h5 class="card-title">1. Dados de Acesso</h5>
      <form class="browser-default-validation" action="/atualizar-profissional/{{ $prof->id }}" method="POST">
        @csrf
        @method("PUT")
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $prof->nome }}" name="nome" type="text" class="form-control" id="nome" placeholder="Nome completo" required>
          <label for="nome">Nome</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $prof->user->email ?? '' }}" name="email" type="email" class="form-control" id="email" placeholder="Email institucional">
          <label for="email">Email</label>
        </div>

        <h5 class="card-title">2. Dados Profissionais</h5>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $prof->contato }}" name="contato" type="text" class="form-control" id="contato" placeholder="(00) 90000-0000">
          <label for="contato">Contato</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <select name="especialidade" class="form-select @error('especialidade') is-invalid @enderror" id="especialidade" required>
            <option disabled value="">Selecione a Especialidade</option>
            @foreach($especialidades as $e)
            <option value="{{ $e->nome }}" {{ old('especialidade', $prof->especialidade) == $e->nome ? 'selected' : '' }}>{{ $e->nome }}</option>
            @endforeach
          </select>
          @error('especialidade')<div class="invalid-feedback">{{ $message }}</div>@enderror
          <label for="especialidade">Especialidade</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $prof->registro_profissional }}" name="registro_profissional" type="text" class="form-control" id="registro_profissional" placeholder="CRM-MG 12345">
          <label for="registro_profissional">Registro Profissional</label>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary">Atualizar</button>
          <a href="{{ url()->previous('/profissionais') }}" class="btn btn-outline-secondary ms-2">Cancelar</a>
        </div>
      </form>
    </div>
    </div>
  </div>
</div>
@endsection
