@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Nova Consulta')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Nova Consulta</h3>
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

      <h5 class="card-title">1. Identificação</h5>
      <form class="browser-default-validation" action="/cadastrar-consulta" method="POST">
        @csrf

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <select name="paciente_id" class="form-select @error('paciente_id') is-invalid @enderror"
            id="paciente_id" required>
            <option disabled {{ old('paciente_id') ? '' : 'selected' }} value="">Selecione o Paciente</option>
            @foreach($pacientes as $paciente)
            <option value="{{ $paciente->id }}" {{ old('paciente_id') == $paciente->id ? 'selected' : '' }}>
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
            <option disabled {{ old('profissional_id') ? '' : 'selected' }} value="">Selecione o Profissional</option>
            @foreach($profissionais as $profissional)
            <option value="{{ $profissional->id }}" {{ old('profissional_id') == $profissional->id ? 'selected' : '' }}>
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
            id="data_hora" value="{{ old('data_hora') }}" required />
          <label for="data_hora">Data e Hora</label>
          @error('data_hora')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <select name="tipo" class="form-select @error('tipo') is-invalid @enderror" id="tipo" required>
            <option disabled {{ old('tipo') ? '' : 'selected' }} value="">Selecione o Tipo</option>
            <option value="Clinico Geral"  {{ old('tipo') == 'Clinico Geral'  ? 'selected' : '' }}>Clínico Geral</option>
            <option value="Odontologia"    {{ old('tipo') == 'Odontologia'    ? 'selected' : '' }}>Odontologia</option>
            <option value="Psicologia"     {{ old('tipo') == 'Psicologia'     ? 'selected' : '' }}>Psicologia</option>
            <option value="Nutricionista"  {{ old('tipo') == 'Nutricionista'  ? 'selected' : '' }}>Nutricionista</option>
            <option value="Fisioterapeuta" {{ old('tipo') == 'Fisioterapeuta' ? 'selected' : '' }}>Fisioterapeuta</option>
          </select>
          <label for="tipo">Tipo de Consulta</label>
          @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <h5 class="card-title mt-4">2. Registro Clínico</h5>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <textarea name="queixa" class="form-control @error('queixa') is-invalid @enderror"
            id="queixa" placeholder="Descreva a queixa principal" style="height: 90px"
            required>{{ old('queixa') }}</textarea>
          <label for="queixa">Queixa Principal</label>
          @error('queixa')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <textarea name="anamnese" class="form-control @error('anamnese') is-invalid @enderror"
            id="anamnese" placeholder="História clínica detalhada" style="height: 90px">{{ old('anamnese') }}</textarea>
          <label for="anamnese">Anamnese <span class="text-muted">(opcional)</span></label>
          @error('anamnese')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <textarea name="diagnostico" class="form-control @error('diagnostico') is-invalid @enderror"
            id="diagnostico" placeholder="Hipótese ou diagnóstico confirmado" style="height: 90px">{{ old('diagnostico') }}</textarea>
          <label for="diagnostico">Diagnóstico <span class="text-muted">(opcional)</span></label>
          @error('diagnostico')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <textarea name="conduta" class="form-control @error('conduta') is-invalid @enderror"
            id="conduta" placeholder="Condutas, orientações e encaminhamentos" style="height: 90px">{{ old('conduta') }}</textarea>
          <label for="conduta">Conduta <span class="text-muted">(opcional)</span></label>
          @error('conduta')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary">Registrar Consulta</button>
          <a href="/consultas" class="btn btn-outline-secondary ms-2">Cancelar</a>
        </div>

      </form>
    </div>
  </div>
</div>

@endsection
