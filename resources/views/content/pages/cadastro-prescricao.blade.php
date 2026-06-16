@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Cadastrar Prescrição')

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

  {{-- HERO gradiente — VERDE = cadastro (UX-P09 v0.10.2) --}}
  <div class="card mb-4 text-white border-0"
       style="background: linear-gradient(105deg, #237030 0%, #3DAA4A 50%, #56cf66 100%);">
    <div class="card-body py-4 px-4 position-relative overflow-hidden">
      <div class="position-absolute rounded-circle"
           style="right:-60px; top:-60px; width:200px; height:200px;
                  border:40px solid rgba(255,255,255,0.07); pointer-events:none;"></div>

      <div class="d-flex flex-wrap align-items-center gap-3 position-relative">
        <div class="flex-shrink-0 rounded-3 d-flex align-items-center justify-content-center"
             style="width:56px; height:56px; background:rgba(255,255,255,0.18);">
          <i class="mdi mdi-clipboard-pulse-outline mdi-36px"></i>
        </div>
        <div class="flex-grow-1 min-width-0">
          <p class="mb-1 text-uppercase fw-semibold" style="font-size:11px; letter-spacing:.08em; opacity:.85;">
            Prontuário · Prescrições
          </p>
          <h4 class="mb-1 fw-bold text-white">Cadastro de Prescrição</h4>
          <p class="mb-0" style="font-size:13px; opacity:.92;">Vincule um medicamento prescrito a uma consulta do prontuário</p>
        </div>
        <a href="{{ $consultaId ? '/consultas/' . $consultaId : '/prescricoes' }}"
           class="btn btn-sm fw-semibold flex-shrink-0"
           style="background:#fff; color:#237030; border:none;">
          <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
        </a>
      </div>
    </div>
  </div>

  {{-- Flash + erros --}}
  @if(session('success'))
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
  </div>
  @endif
  @if($errors->any())
  <div class="alert alert-danger alert-dismissible mb-4" role="alert">
    <i class="mdi mdi-alert-circle-outline me-1"></i>Corrija os campos abaixo:
    <ul class="mb-0 mt-1 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
  </div>
  @endif

  {{-- Layout: formulário (esq) + painel da consulta (dir) --}}
  <div class="row g-4">

    <div class="col-lg-8">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="mdi mdi-pill me-2"></i>Dados da Prescrição</h5>
        </div>
        <div class="card-body">

          <form class="browser-default-validation" action="/cadastrar-prescricao" method="POST">
            @csrf
            @if($consultaId)
              <input type="hidden" name="consulta_id_origem" value="{{ $consultaId }}">
            @endif

            {{-- Consulta vinculada (data-* alimentam o painel via JS) --}}
            <div class="form-floating form-floating-outline mb-4">
              <select name="consulta_id" class="form-select" id="consulta_id" required>
                <option disabled value="" {{ old('consulta_id', $consultaId) ? '' : 'selected' }}>Selecione a Consulta</option>
                @foreach($consultas as $consulta)
                <option value="{{ $consulta->id }}"
                  data-paciente="{{ $consulta->paciente->nome ?? '?' }}"
                  data-data="{{ $consulta->data_hora->format('d/m/Y H:i') }}"
                  data-profissional="{{ $consulta->profissional->nome ?? '?' }}"
                  data-tipo="{{ $consulta->tipo }}"
                  {{ old('consulta_id', $consultaId) == $consulta->id ? 'selected' : '' }}>
                  {{ $consulta->data_hora->format('d/m/Y H:i') }} - {{ $consulta->paciente->nome ?? '?' }} ({{ $consulta->profissional->nome ?? '?' }})
                </option>
                @endforeach
              </select>
              <label for="consulta_id">Consulta Vinculada</label>
            </div>

            {{-- Medicamento --}}
            <div class="form-floating form-floating-outline mb-4">
              <input name="nome_medicamento" type="text" class="form-control" id="nome_medicamento" placeholder="Paracetamol 750mg" value="{{ old('nome_medicamento') }}" required />
              <label for="nome_medicamento">Nome do Medicamento</label>
            </div>

            {{-- Posologia: dosagem + frequência + duração --}}
            <div class="row">
              <div class="col-md-4">
                <div class="form-floating form-floating-outline mb-4">
                  <input name="dosagem" type="text" class="form-control" id="dosagem" placeholder="1 comprimido" value="{{ old('dosagem') }}" required />
                  <label for="dosagem">Dosagem</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-floating form-floating-outline mb-4">
                  <input name="frequencia" type="text" class="form-control" id="frequencia" placeholder="8 em 8 horas" value="{{ old('frequencia') }}" required />
                  <label for="frequencia">Frequência</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-floating form-floating-outline mb-4">
                  <input name="duracao" type="text" class="form-control" id="duracao" placeholder="5 dias" value="{{ old('duracao') }}" required />
                  <label for="duracao">Duração</label>
                </div>
              </div>
            </div>

            {{-- Observação --}}
            <div class="form-floating form-floating-outline mb-2">
              <textarea name="observacao" class="form-control" id="observacao" placeholder="Observações" style="height: 100px">{{ old('observacao') }}</textarea>
              <label for="observacao">Observação</label>
            </div>

            <div class="mt-4 d-flex flex-wrap gap-2">
              <button type="submit" class="btn btn-primary"><i class="mdi mdi-check me-1"></i>Cadastrar</button>
              <a href="{{ $consultaId ? '/consultas/' . $consultaId : '/prescricoes' }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
          </form>

        </div>
      </div>
    </div>

    {{-- Painel lateral: resumo da consulta selecionada --}}
    <div class="col-lg-4">
      @include('content.pages.partials._consulta_sidebar')
    </div>

  </div>

</div>
@endsection

@section('page-script')
@include('content.pages.partials._consulta_sidebar_script')
@endsection
