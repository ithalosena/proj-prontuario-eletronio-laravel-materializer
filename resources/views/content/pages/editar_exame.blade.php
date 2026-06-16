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

  {{-- HERO gradiente — ÂMBAR = editar (UX-P09 v0.10.2) --}}
  <div class="card mb-4 text-white border-0"
       style="background: linear-gradient(105deg, #1b5e8a 0%, #2f86c5 50%, #63b3ed 100%);">
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
            Prontuário · Exames
          </p>
          <h4 class="mb-1 fw-bold text-white">Editar Exame</h4>
          <p class="mb-0" style="font-size:13px; opacity:.92;">Atualize os dados ou registre o resultado do exame</p>
        </div>
        <a href="{{ url()->previous('/exames') }}"
           class="btn btn-sm fw-semibold flex-shrink-0"
           style="background:#fff; color:#1b5e8a; border:none;">
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
          <h5 class="card-title mb-0"><i class="mdi mdi-file-document-edit-outline me-2"></i>Dados do Exame</h5>
        </div>
        <div class="card-body">

          <form class="browser-default-validation" action="/atualizar-exame/{{ $exame->id }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Consulta vinculada (data-* alimentam o painel via JS) --}}
            <div class="form-floating form-floating-outline mb-4">
              <select name="consulta_id" class="form-select" id="consulta_id" required>
                @foreach($consultas as $consulta)
                <option value="{{ $consulta->id }}"
                  data-paciente="{{ $consulta->paciente->nome ?? '?' }}"
                  data-data="{{ $consulta->data_hora->format('d/m/Y H:i') }}"
                  data-profissional="{{ $consulta->profissional->nome ?? '?' }}"
                  data-tipo="{{ $consulta->tipo }}"
                  {{ $exame->consulta_id == $consulta->id ? 'selected' : '' }}>
                  {{ $consulta->data_hora->format('d/m/Y H:i') }} - {{ $consulta->paciente->nome ?? '?' }} ({{ $consulta->profissional->nome ?? '?' }})
                </option>
                @endforeach
              </select>
              <label for="consulta_id">Consulta Vinculada</label>
            </div>

            {{-- Tipo --}}
            <div class="form-floating form-floating-outline mb-4">
              <input value="{{ old('tipo', $exame->tipo) }}" name="tipo" type="text" class="form-control" id="tipo" placeholder="Hemograma, Raio-X, etc." required />
              <label for="tipo">Tipo de Exame</label>
            </div>

            {{-- Datas: solicitação + resultado lado a lado --}}
            <div class="row">
              <div class="col-md-6">
                <div class="form-floating form-floating-outline mb-4">
                  <input value="{{ old('data_solicitacao', $exame->data_solicitacao ? $exame->data_solicitacao->format('Y-m-d') : '') }}" name="data_solicitacao" type="date" class="form-control" id="data_solicitacao" required />
                  <label for="data_solicitacao">Data de Solicitação</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating form-floating-outline mb-4">
                  <input value="{{ old('data_resultado', $exame->data_resultado ? $exame->data_resultado->format('Y-m-d') : '') }}" name="data_resultado" type="date" class="form-control" id="data_resultado" />
                  <label for="data_resultado">Data do Resultado</label>
                </div>
              </div>
            </div>

            {{-- Observação --}}
            <div class="form-floating form-floating-outline mb-4">
              <textarea name="observacao" class="form-control" id="observacao" placeholder="Observações" style="height: 90px">{{ old('observacao', $exame->observacao) }}</textarea>
              <label for="observacao">Observação</label>
            </div>

            {{-- Resultado --}}
            <div class="form-floating form-floating-outline mb-2">
              <textarea name="resultado" class="form-control" id="resultado" placeholder="Resultado do exame" style="height: 90px">{{ old('resultado', $exame->resultado) }}</textarea>
              <label for="resultado">Resultado</label>
            </div>

            <div class="mt-4 d-flex flex-wrap gap-2">
              <button type="submit" class="btn btn-primary"><i class="mdi mdi-check me-1"></i>Atualizar</button>
              <a href="{{ url()->previous('/exames') }}" class="btn btn-outline-secondary">Cancelar</a>
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
