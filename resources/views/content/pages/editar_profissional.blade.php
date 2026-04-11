@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Editar Profissional')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Editar Profissional</h3>
          <div class="card-header-elements ms-auto mt-3 mb-1 me-2">
            <a href="/profissionais" class="btn btn-default"><i class="mdi mdi-arrow-u-left-bottom mdi-24px me-2"></i>Voltar</a>
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
          <select name="especialidade" class="form-select" id="especialidade" required>
            <option value="Clinico Geral" {{ $prof->especialidade == 'Clinico Geral' ? 'selected' : '' }}>Clinico Geral</option>
            <option value="Odontologia" {{ $prof->especialidade == 'Odontologia' ? 'selected' : '' }}>Odontologia</option>
            <option value="Psicologia" {{ $prof->especialidade == 'Psicologia' ? 'selected' : '' }}>Psicologia</option>
            <option value="Nutricionista" {{ $prof->especialidade == 'Nutricionista' ? 'selected' : '' }}>Nutricionista</option>
            <option value="Fisioterapeuta" {{ $prof->especialidade == 'Fisioterapeuta' ? 'selected' : '' }}>Fisioterapeuta</option>
          </select>
          <label for="especialidade">Especialidade</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $prof->registro_profissional }}" name="registro_profissional" type="text" class="form-control" id="registro_profissional" placeholder="CRM-MG 12345">
          <label for="registro_profissional">Registro Profissional</label>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary">Atualizar</button>
          <a href="/profissionais" class="btn btn-outline-secondary ms-2">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
