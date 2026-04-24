@php
$configData = Helper::appClasses();
// url()->previous() retorna a URL da página anterior (via HTTP Referer).
// O fallback '/atendimentos' é usado caso o Referer não esteja disponível
// (ex: usuário acessou a URL diretamente).
$voltarUrl  = url()->previous('/atendimentos');
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Novo Atendimento')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Cabeçalho com título e botão Voltar dinâmico --}}
  <div class="card mb-4">
    <div class="card-header header-elements">
      <h4 class="mb-0">Abrir Novo Atendimento</h4>
      <div class="card-header-elements ms-auto">
        <a href="{{ $voltarUrl }}" class="btn btn-default">
          <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
        </a>
      </div>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-md-7">
      <div class="card">
        <div class="card-body p-4">

          <form class="browser-default-validation" action="/cadastrar-atendimento" method="POST">
            @csrf

            {{-- Campo de seleção de paciente --}}
            <div class="mb-4">
              <div class="form-floating form-floating-outline">
                <select name="paciente_id" id="paciente_id"
                  class="form-select @error('paciente_id') is-invalid @enderror" required>
                  <option disabled {{ old('paciente_id') ? '' : 'selected' }} value="">Selecione o paciente</option>
                  @foreach($pacientes as $paciente)
                  <option value="{{ $paciente->id }}" {{ old('paciente_id') == $paciente->id ? 'selected' : '' }}>
                    {{ $paciente->nome }} — {{ $paciente->matricula }}
                  </option>
                  @endforeach
                </select>
                <label for="paciente_id">Paciente *</label>
                @error('paciente_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>

            {{-- Campo de profissional: comportamento condicional
                 - Se o usuário logado tem perfil de profissional ($profissionalLogado != null):
                   exibe o nome travado + input hidden com o ID, sem precisar selecionar.
                 - Caso contrário (admin, recepcionista):
                   exibe o select completo para escolher o profissional responsável. --}}
            @if($profissionalLogado)
              {{-- Input hidden garante que o profissional_id seja enviado no POST --}}
              <input type="hidden" name="profissional_id" value="{{ $profissionalLogado->id }}">
              <div class="mb-4">
                <label class="form-label text-muted small">Profissional Responsável</label>
                <div class="form-control bg-light d-flex align-items-center gap-2">
                  <i class="mdi mdi-doctor text-primary"></i>
                  <div>
                    <strong>{{ $profissionalLogado->nome }}</strong>
                    @if($profissionalLogado->especialidade)
                      <span class="text-muted ms-2">· {{ $profissionalLogado->especialidade }}</span>
                    @endif
                  </div>
                </div>
                <small class="text-muted">Atribuído automaticamente ao profissional logado.</small>
              </div>
            @else
              {{-- Admin/recepcionista: seleciona o profissional manualmente --}}
              <div class="mb-4">
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
              </div>
            @endif

            {{-- Botões de ação: submeter ou cancelar voltando para a página anterior --}}
            <div class="d-flex gap-2 mt-2">
              <button type="submit" class="btn btn-primary">
                <i class="mdi mdi-folder-open-outline me-1"></i>Abrir Atendimento
              </button>
              <a href="{{ $voltarUrl }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
