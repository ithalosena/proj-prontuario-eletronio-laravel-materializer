@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Cadastrar Profissional')

{{-- Breadcrumb: Início > Profissionais > Cadastrar --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',        'url' => '/'],
      ['label' => 'Profissionais', 'url' => '/profissionais'],
      ['label' => 'Cadastrar',     'url' => null],
    ]
  ])
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Cadastro de Profissional</h3>
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
      <form class="browser-default-validation" action="/cadastrar-profissional" method="POST">
        @csrf

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input name="nome" type="text" class="form-control @error('nome') is-invalid @enderror"
            id="nome" placeholder="Nome completo" value="{{ old('nome') }}" required />
          <label for="nome">Nome</label>
          @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input name="email" type="email" class="form-control @error('email') is-invalid @enderror"
            id="email" placeholder="Email institucional" value="{{ old('email') }}" required />
          <label for="email">Email</label>
          @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4 form-password-toggle mt-3">
          <div class="input-group input-group-merge">
            <div class="form-floating form-floating-outline">
              <input name="senha" type="password"
                class="form-control @error('senha') is-invalid @enderror"
                id="senha" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required />
              <label for="senha">Senha</label>
            </div>
            <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline mdi-24px"></i></span>
          </div>
          @error('senha')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <h5 class="card-title">2. Dados Profissionais</h5>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input name="contato" type="text" class="form-control @error('contato') is-invalid @enderror"
            id="contato" placeholder="(00) 90000-0000" value="{{ old('contato') }}" required />
          <label for="contato">Contato</label>
          @error('contato')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <select name="especialidade" class="form-select @error('especialidade') is-invalid @enderror"
            id="especialidade" required>
            <option disabled {{ old('especialidade') ? '' : 'selected' }} value="">Selecione a Especialidade</option>
            <option value="Clinico Geral"  {{ old('especialidade') == 'Clinico Geral'  ? 'selected' : '' }}>Clínico Geral</option>
            <option value="Odontologia"    {{ old('especialidade') == 'Odontologia'    ? 'selected' : '' }}>Odontologia</option>
            <option value="Psicologia"     {{ old('especialidade') == 'Psicologia'     ? 'selected' : '' }}>Psicologia</option>
            <option value="Nutricionista"  {{ old('especialidade') == 'Nutricionista'  ? 'selected' : '' }}>Nutricionista</option>
            <option value="Fisioterapeuta" {{ old('especialidade') == 'Fisioterapeuta' ? 'selected' : '' }}>Fisioterapeuta</option>
          </select>
          <label for="especialidade">Especialidade</label>
          @error('especialidade')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input name="registro_profissional" type="text"
            class="form-control @error('registro_profissional') is-invalid @enderror"
            id="registro_profissional" placeholder="CRM-MG 12345"
            value="{{ old('registro_profissional') }}" required />
          <label for="registro_profissional">Registro Profissional</label>
          @error('registro_profissional')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary">Cadastrar</button>
          <a href="/profissionais" class="btn btn-outline-secondary ms-2">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
