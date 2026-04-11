@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Editar Consulta')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Editar Consulta</h3>
          <div class="card-header-elements ms-auto mt-3 mb-1 me-2">
            <a href="/consultas" class="btn btn-default">
              <i class="mdi mdi-arrow-u-left-bottom mdi-24px me-2"></i>Voltar
            </a>
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

      <h5 class="card-title">1. Identificação</h5>
      <form class="browser-default-validation" action="/atualizar-consulta/{{ $consulta->id }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <select name="paciente_id" class="form-select @error('paciente_id') is-invalid @enderror"
            id="paciente_id" required>
            <option disabled value="">Selecione o Paciente</option>
            @foreach($pacientes as $paciente)
            <option value="{{ $paciente->id }}"
              {{ (old('paciente_id', $consulta->paciente_id) == $paciente->id) ? 'selected' : '' }}>
              {{ $paciente->nome }} — {{ $paciente->matricula }}
            </option>
            @endforeach
          </select>
          <label for="paciente_id">Paciente</label>
          @error('paciente_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <select name="profissional_id" class="form-select @error('profissional_id') is-invalid @enderror"
            id="profissional_id" required>
            <option disabled value="">Selecione o Profissional</option>
            @foreach($profissionais as $profissional)
            <option value="{{ $profissional->id }}"
              {{ (old('profissional_id', $consulta->profissional_id) == $profissional->id) ? 'selected' : '' }}>
              {{ $profissional->nome }} — {{ $profissional->especialidade }}
            </option>
            @endforeach
          </select>
          <label for="profissional_id">Profissional</label>
          @error('profissional_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input name="data_hora" type="datetime-local"
            class="form-control @error('data_hora') is-invalid @enderror"
            id="data_hora"
            value="{{ old('data_hora', $consulta->data_hora->format('Y-m-d\TH:i')) }}"
            required />
          <label for="data_hora">Data e Hora</label>
          @error('data_hora')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <select name="tipo" class="form-select @error('tipo') is-invalid @enderror" id="tipo" required>
            <option disabled value="">Selecione o Tipo</option>
            @foreach(['Clinico Geral' => 'Clínico Geral', 'Odontologia' => 'Odontologia', 'Psicologia' => 'Psicologia', 'Nutricionista' => 'Nutricionista', 'Fisioterapeuta' => 'Fisioterapeuta'] as $value => $label)
            <option value="{{ $value }}" {{ old('tipo', $consulta->tipo) == $value ? 'selected' : '' }}>
              {{ $label }}
            </option>
            @endforeach
          </select>
          <label for="tipo">Tipo de Consulta</label>
          @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <h5 class="card-title mt-4">2. Registro Clínico</h5>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <textarea name="queixa" class="form-control @error('queixa') is-invalid @enderror"
            id="queixa" placeholder="Queixa principal" style="height: 90px"
            required>{{ old('queixa', $consulta->queixa) }}</textarea>
          <label for="queixa">Queixa Principal</label>
          @error('queixa')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <textarea name="anamnese" class="form-control @error('anamnese') is-invalid @enderror"
            id="anamnese" placeholder="História clínica" style="height: 90px">{{ old('anamnese', $consulta->anamnese) }}</textarea>
          <label for="anamnese">Anamnese <span class="text-muted">(opcional)</span></label>
          @error('anamnese')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <textarea name="diagnostico" class="form-control @error('diagnostico') is-invalid @enderror"
            id="diagnostico" placeholder="Diagnóstico" style="height: 90px">{{ old('diagnostico', $consulta->diagnostico) }}</textarea>
          <label for="diagnostico">Diagnóstico <span class="text-muted">(opcional)</span></label>
          @error('diagnostico')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <textarea name="conduta" class="form-control @error('conduta') is-invalid @enderror"
            id="conduta" placeholder="Conduta e orientações" style="height: 90px">{{ old('conduta', $consulta->conduta) }}</textarea>
          <label for="conduta">Conduta <span class="text-muted">(opcional)</span></label>
          @error('conduta')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary">Atualizar</button>
          <a href="/consultas" class="btn btn-outline-secondary ms-2">Cancelar</a>
        </div>

      </form>
    </div>
  </div>
</div>

@endsection
