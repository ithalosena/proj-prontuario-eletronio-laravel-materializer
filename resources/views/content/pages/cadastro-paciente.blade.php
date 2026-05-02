@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Pacientes - Cadastrar Paciente')

{{-- Breadcrumb: Início > Pacientes > Cadastrar --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',    'url' => '/'],
      ['label' => 'Pacientes', 'url' => '/pacientes'],
      ['label' => 'Cadastrar', 'url' => null],
    ]
  ])
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Cadastro de Paciente</h3>
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

      <h5 class="card-title">1. Dados de Acesso</h5>
      <form class="browser-default-validation" action="/cadastrar-paciente" method="POST">
        @csrf

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input name="nome" type="text" class="form-control @error('nome') is-invalid @enderror"
            id="nome" placeholder="Nome completo" value="{{ old('nome') }}" required />
          <label for="nome">Nome</label>
          @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input name="email" type="email" class="form-control @error('email') is-invalid @enderror"
            id="email" placeholder="email@aluno.ifnmg.edu.br" value="{{ old('email') }}" required />
          <label for="email">Email</label>
          @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-6 form-password-toggle mt-3">
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

        <h5 class="card-title mt-4">2. Dados Pessoais</h5>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input name="contato" type="text" class="form-control @error('contato') is-invalid @enderror"
            id="contato" placeholder="(38) 90000-0000" value="{{ old('contato') }}" />
          <label for="contato">Contato</label>
          @error('contato')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input name="documento" type="text" class="form-control @error('documento') is-invalid @enderror"
            id="documento" placeholder="000.000.000-00" value="{{ old('documento') }}" required />
          <label for="documento">Documento (CPF)</label>
          @error('documento')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input name="data_nascimento" type="date"
            class="form-control @error('data_nascimento') is-invalid @enderror"
            id="data_nascimento" value="{{ old('data_nascimento') }}" required />
          <label for="data_nascimento">Data de Nascimento</label>
          @error('data_nascimento')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <select name="sexo" class="form-select @error('sexo') is-invalid @enderror" id="sexo" required>
            <option disabled {{ old('sexo') ? '' : 'selected' }} value="">Selecione</option>
            <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Feminino</option>
            <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
            <option value="outro" {{ old('sexo') == 'outro' ? 'selected' : '' }}>Outro</option>
          </select>
          <label for="sexo">Sexo</label>
          @error('sexo')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input name="endereco" type="text" class="form-control @error('endereco') is-invalid @enderror"
            id="endereco" placeholder="Rua, número - Cidade/UF" value="{{ old('endereco') }}" />
          <label for="endereco">Endereço</label>
          @error('endereco')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <h5 class="card-title mt-4">3. Dados Acadêmicos</h5>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input name="matricula" type="text" class="form-control @error('matricula') is-invalid @enderror"
            id="matricula" placeholder="2024001" value="{{ old('matricula') }}" required />
          <label for="matricula">Matrícula</label>
          @error('matricula')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input name="curso" type="text" class="form-control @error('curso') is-invalid @enderror"
            id="curso" placeholder="Análise e Desenvolvimento de Sistemas" value="{{ old('curso') }}" required />
          <label for="curso">Curso</label>
          @error('curso')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary">Cadastrar</button>
          <a href="/pacientes" class="btn btn-outline-secondary ms-2">Cancelar</a>
        </div>

      </form>
    </div>
  </div>
</div>
@endsection
