@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Home')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Banner informativo: exibido para perfis ainda sem dashboard contextual (v0.9.1b) --}}
  @if($avisoMigracao ?? false)
  <div class="alert alert-info d-flex align-items-center gap-2 mb-4" role="alert">
    <i class="mdi mdi-information-outline fs-5 flex-shrink-0"></i>
    <span>Sua visão personalizada está em desenvolvimento — versão completa disponível na próxima atualização.</span>
  </div>
  @endif

  {{-- Saudação --}}
  <div class="row mb-2">
    <div class="col-md-12">
      <h4 class="fw-semibold">Bem-vindo, {{ Auth::user()->name }}</h4>
      <p class="text-muted">Aqui está um resumo do sistema.</p>
    </div>
  </div>

  {{-- Contadores --}}
  <div class="row mb-4">
    <div class="col-sm-6 col-lg-3 mb-4">
      <div class="card">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary"><i class="mdi mdi-account-group mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 text-muted small">Pacientes</p>
            <h4 class="mb-0 fw-bold">{{ $totalPacientes }}</h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-4">
      <div class="card">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-success"><i class="mdi mdi-doctor mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 text-muted small">Profissionais</p>
            <h4 class="mb-0 fw-bold">{{ $totalProfissionais }}</h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-4">
      <div class="card">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-warning"><i class="mdi mdi-clipboard-pulse-outline mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 text-muted small">Consultas</p>
            <h4 class="mb-0 fw-bold">{{ $totalConsultas }}</h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-4">
      <div class="card">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-info"><i class="mdi mdi-pill mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 text-muted small">Prescrições</p>
            <h4 class="mb-0 fw-bold">{{ $totalPrescricoes }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Cards de navegação --}}
  <div class="row ms-1">

    <div class="card me-4 mt-3" style="width: 16rem;">
      <img src="{{ asset('assets/img/illustrations/home-profissionais.png') }}" class="card-img-top">
      <div class="card-body align-self-center">
        <a href="/profissionais" type="button" class="btn btn-primary">Profissionais</a>
      </div>
    </div>

    <div class="card me-4 mt-3" style="width: 16rem;">
      <img src="{{ asset('assets/img/illustrations/home-pacientes.png') }}" class="card-img-top">
      <div class="card-body align-self-center">
        <a href="/pacientes" type="button" class="btn btn-primary btn-block">Pacientes</a>
      </div>
    </div>

    <div class="card me-4 mt-3" style="width: 16rem;">
      <img src="{{ asset('assets/img/illustrations/home-prontuario.png') }}" class="card-img-top">
      <div class="card-body align-self-center">
        <a href="/exames" type="button" class="btn btn-primary btn-block">Prontuário</a>
      </div>
    </div>

    <div class="card me-4 mt-3" style="width: 16rem;">
      <img src="{{ asset('assets/img/illustrations/home-prescricao.png') }}" class="card-img-top">
      <div class="card-body align-self-center">
        <a href="/prescricoes" type="button" class="btn btn-primary btn-block">Prescrições</a>
      </div>
    </div>

    <div class="card me-3 mt-3" style="width: 16rem;">
      <img src="{{ asset('assets/img/illustrations/home-relatorio.png') }}" class="card-img-top">
      <div class="card-body align-self-center">
        <a href="relatorios" type="button" class="btn btn-primary btn-block">Relatórios</a>
      </div>
    </div>

  </div>

</div>
@endsection
