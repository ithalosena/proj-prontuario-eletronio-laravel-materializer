@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Editar Exame')

{{-- Breadcrumb: Início > Exames > Editar --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início', 'url' => '/'],
      ['label' => 'Exames', 'url' => '/exames'],
      ['label' => 'Editar', 'url' => null],
    ]
  ])
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Editar Exame</h3>
          <div class="card-header-elements ms-auto mt-3 mb-1 me-2">
            <a href="{{ url()->previous('/exames') }}" class="btn btn-default"><i class="mdi mdi-arrow-u-left-bottom mdi-24px me-2"></i>Voltar</a>
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

      <form class="browser-default-validation" action="/atualizar-exame/{{ $exame->id }}" method="POST">
        @csrf
        @method("PUT")
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <select name="consulta_id" class="form-select" id="consulta_id" required>
            @foreach($consultas as $consulta)
            <option value="{{ $consulta->id }}" {{ $exame->consulta_id == $consulta->id ? 'selected' : '' }}>
              {{ $consulta->data_hora->format('d/m/Y H:i') }} - {{ $consulta->paciente->nome ?? '?' }} ({{ $consulta->profissional->nome ?? '?' }})
            </option>
            @endforeach
          </select>
          <label for="consulta_id">Consulta Vinculada</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $exame->tipo }}" name="tipo" type="text" class="form-control" id="tipo" placeholder="Hemograma, Raio-X, etc." required />
          <label for="tipo">Tipo de Exame</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <textarea name="observacao" class="form-control" id="observacao" placeholder="Observacoes" style="height: 80px">{{ $exame->observacao }}</textarea>
          <label for="observacao">Observacao</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $exame->data_solicitacao ? $exame->data_solicitacao->format('Y-m-d') : '' }}" name="data_solicitacao" type="date" class="form-control" id="data_solicitacao" required />
          <label for="data_solicitacao">Data de Solicitacao</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $exame->data_resultado ? $exame->data_resultado->format('Y-m-d') : '' }}" name="data_resultado" type="date" class="form-control" id="data_resultado" />
          <label for="data_resultado">Data do Resultado</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <textarea name="resultado" class="form-control" id="resultado" placeholder="Resultado do exame" style="height: 80px">{{ $exame->resultado }}</textarea>
          <label for="resultado">Resultado</label>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary">Atualizar</button>
          <a href="{{ url()->previous('/exames') }}" class="btn btn-outline-secondary ms-2">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
