@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Cadastrar Prescricao')

{{-- Breadcrumb: Início > Prescrições > Cadastrar --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',      'url' => '/'],
      ['label' => 'Prescrições', 'url' => '/prescricoes'],
      ['label' => 'Cadastrar',   'url' => null],
    ]
  ])
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Cadastro de Prescricao</h3>
          <div class="card-header-elements ms-auto mt-3 mb-1 me-2">
            <a href="{{ $consultaId ? '/consultas/' . $consultaId : '/prescricoes' }}" class="btn btn-default">
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

      <form class="browser-default-validation" action="/cadastrar-prescricao" method="POST">
        @csrf
        @if($consultaId)
          <input type="hidden" name="consulta_id_origem" value="{{ $consultaId }}">
        @endif
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <select name="consulta_id" class="form-select" id="consulta_id" required>
            <option disabled value="" {{ old('consulta_id', $consultaId) ? '' : 'selected' }}>Selecione a Consulta</option>
            @foreach($consultas as $consulta)
            <option value="{{ $consulta->id }}" {{ old('consulta_id', $consultaId) == $consulta->id ? 'selected' : '' }}>
              {{ $consulta->data_hora->format('d/m/Y H:i') }} - {{ $consulta->paciente->nome ?? '?' }} ({{ $consulta->profissional->nome ?? '?' }})
            </option>
            @endforeach
          </select>
          <label for="consulta_id">Consulta Vinculada</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input name="nome_medicamento" type="text" class="form-control" id="nome_medicamento" placeholder="Paracetamol 750mg" required />
          <label for="nome_medicamento">Nome do Medicamento</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input name="dosagem" type="text" class="form-control" id="dosagem" placeholder="1 comprimido" required />
          <label for="dosagem">Dosagem</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input name="frequencia" type="text" class="form-control" id="frequencia" placeholder="De 8 em 8 horas" required />
          <label for="frequencia">Frequencia</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input name="duracao" type="text" class="form-control" id="duracao" placeholder="5 dias" required />
          <label for="duracao">Duracao</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <textarea name="observacao" class="form-control" id="observacao" placeholder="Observacoes" style="height: 80px"></textarea>
          <label for="observacao">Observacao</label>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary">Cadastrar</button>
          <a href="{{ $consultaId ? '/consultas/' . $consultaId : '/prescricoes' }}" class="btn btn-outline-secondary ms-2">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
