@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Editar Paciente')

{{-- Breadcrumb: Início > Pacientes > Editar --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',    'url' => '/'],
      ['label' => 'Pacientes', 'url' => '/pacientes'],
      ['label' => 'Editar',    'url' => null],
    ]
  ])
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Editar Paciente</h3>
          <div class="card-header-elements ms-auto mt-3 mb-1 me-2">
            <a href="{{ url()->previous('/pacientes') }}" class="btn btn-default"><i class="mdi mdi-arrow-u-left-bottom mdi-24px me-2"></i>Voltar</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-8 card mt-1">
    <div class="card-body">

      @if(session('success'))
      <div class="alert alert-success alert-dismissible mb-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
      </div>
      @endif

      {{-- UX-24: aviso para roles sem acesso a dados sensíveis --}}
      @if(!$podeEditarSensivel)
      <div class="alert alert-info mb-4" role="alert">
        <i class="mdi mdi-information-outline me-2"></i>
        Você pode atualizar <strong>contato</strong> e <strong>endereço</strong>. Os demais dados são restritos a administradores.
      </div>
      @endif

      <h5 class="card-title">1. Dados Pessoais</h5>
      <form class="browser-default-validation" action="/atualizar-paciente/{{ $paciente->id }}" method="POST">
        @csrf
        @method("PUT")

        {{-- Nome — sensível --}}
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $paciente->nome }}" name="nome" type="text"
                 class="form-control {{ !$podeEditarSensivel ? 'bg-light' : '' }}"
                 id="nome" placeholder="Nome completo"
                 {{ !$podeEditarSensivel ? 'readonly' : 'required' }}>
          <label for="nome">
            Nome
            @if(!$podeEditarSensivel)<span class="badge bg-label-secondary ms-1 small">Restrito</span>@endif
          </label>
        </div>

        {{-- Contato — complementar, editável por todos --}}
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $paciente->contato }}" name="contato" type="text" class="form-control" id="contato" placeholder="(00) 90000-0000">
          <label for="contato">Contato</label>
        </div>

        {{-- Documento — sensível --}}
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $paciente->documento }}" name="documento" type="text"
                 class="form-control {{ !$podeEditarSensivel ? 'bg-light' : '' }}"
                 id="documento" placeholder="000.000.000-00"
                 {{ !$podeEditarSensivel ? 'readonly' : 'required' }}>
          <label for="documento">
            Documento (CPF)
            @if(!$podeEditarSensivel)<span class="badge bg-label-secondary ms-1 small">Restrito</span>@endif
          </label>
        </div>

        {{-- Data de nascimento — sensível --}}
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $paciente->data_nascimento ? $paciente->data_nascimento->format('Y-m-d') : '' }}"
                 name="data_nascimento" type="date"
                 class="form-control {{ !$podeEditarSensivel ? 'bg-light' : '' }}"
                 id="data_nascimento"
                 {{ !$podeEditarSensivel ? 'readonly' : 'required' }}>
          <label for="data_nascimento">
            Data de Nascimento
            @if(!$podeEditarSensivel)<span class="badge bg-label-secondary ms-1 small">Restrito</span>@endif
          </label>
        </div>

        {{-- Sexo — sensível; disabled + hidden para não perder o valor no submit --}}
        <div class="form-floating form-floating-outline mb-6 mt-3">
          @if(!$podeEditarSensivel)
            <input type="hidden" name="sexo" value="{{ $paciente->sexo }}">
          @endif
          <select name="{{ $podeEditarSensivel ? 'sexo' : '_sexo_display' }}"
                  class="form-select {{ !$podeEditarSensivel ? 'bg-light' : '' }}"
                  id="sexo"
                  {{ !$podeEditarSensivel ? 'disabled' : 'required' }}>
            <option value="F" {{ $paciente->sexo == 'F' ? 'selected' : '' }}>Feminino</option>
            <option value="M" {{ $paciente->sexo == 'M' ? 'selected' : '' }}>Masculino</option>
            <option value="outro" {{ $paciente->sexo == 'outro' ? 'selected' : '' }}>Outro</option>
          </select>
          <label for="sexo">
            Sexo
            @if(!$podeEditarSensivel)<span class="badge bg-label-secondary ms-1 small">Restrito</span>@endif
          </label>
        </div>

        {{-- Endereço — complementar, editável por todos --}}
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $paciente->endereco }}" name="endereco" type="text" class="form-control" id="endereco" placeholder="Rua, numero - Cidade/UF">
          <label for="endereco">Endereço</label>
        </div>

        <h5 class="card-title">2. Dados Acadêmicos</h5>

        {{-- Matrícula — sensível --}}
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $paciente->matricula }}" name="matricula" type="text"
                 class="form-control {{ !$podeEditarSensivel ? 'bg-light' : '' }}"
                 id="matricula" placeholder="2024001"
                 {{ !$podeEditarSensivel ? 'readonly' : 'required' }}>
          <label for="matricula">
            Matrícula
            @if(!$podeEditarSensivel)<span class="badge bg-label-secondary ms-1 small">Restrito</span>@endif
          </label>
        </div>

        {{-- Curso — sensível --}}
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $paciente->curso }}" name="curso" type="text"
                 class="form-control {{ !$podeEditarSensivel ? 'bg-light' : '' }}"
                 id="curso" placeholder="Analise e Desenvolvimento de Sistemas"
                 {{ !$podeEditarSensivel ? 'readonly' : 'required' }}>
          <label for="curso">
            Curso
            @if(!$podeEditarSensivel)<span class="badge bg-label-secondary ms-1 small">Restrito</span>@endif
          </label>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary">Atualizar</button>
          <a href="{{ url()->previous('/pacientes') }}" class="btn btn-outline-secondary ms-2">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
