@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Editar Paciente')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Editar Paciente</h3>
          <div class="card-header-elements ms-auto mt-3 mb-1 me-2">
            <a href="/pacientes" class="btn btn-default"><i class="mdi mdi-arrow-u-left-bottom mdi-24px me-2"></i>Voltar</a>
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

      <h5 class="card-title">1. Dados Pessoais</h5>
      <form class="browser-default-validation" action="/atualizar-paciente/{{ $paciente->id }}" method="POST">
        @csrf
        @method("PUT")
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input required value="{{ $paciente->nome }}" name="nome" type="text" class="form-control" id="nome" placeholder="Nome completo">
          <label for="nome">Nome</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input required value="{{ $paciente->contato }}" name="contato" type="text" class="form-control" id="contato" placeholder="(00) 90000-0000">
          <label for="contato">Contato</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input required value="{{ $paciente->documento }}" name="documento" type="text" class="form-control" id="documento" placeholder="000.000.000-00">
          <label for="documento">Documento (CPF)</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input required value="{{ $paciente->data_nascimento ? $paciente->data_nascimento->format('Y-m-d') : '' }}" name="data_nascimento" type="date" class="form-control" id="data_nascimento">
          <label for="data_nascimento">Data de Nascimento</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <select name="sexo" class="form-select" id="sexo" required>
            <option value="F" {{ $paciente->sexo == 'F' ? 'selected' : '' }}>Feminino</option>
            <option value="M" {{ $paciente->sexo == 'M' ? 'selected' : '' }}>Masculino</option>
            <option value="outro" {{ $paciente->sexo == 'outro' ? 'selected' : '' }}>Outro</option>
          </select>
          <label for="sexo">Sexo</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $paciente->endereco }}" name="endereco" type="text" class="form-control" id="endereco" placeholder="Rua, numero - Cidade/UF">
          <label for="endereco">Endereco</label>
        </div>

        <h5 class="card-title">2. Dados Academicos</h5>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input required value="{{ $paciente->matricula }}" name="matricula" type="text" class="form-control" id="matricula" placeholder="2024001">
          <label for="matricula">Matricula</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input required value="{{ $paciente->curso }}" name="curso" type="text" class="form-control" id="curso" placeholder="Analise e Desenvolvimento de Sistemas">
          <label for="curso">Curso</label>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary">Atualizar</button>
          <a href="/pacientes" class="btn btn-outline-secondary ms-2">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
