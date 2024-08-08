@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Home')

@section('content')


<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Prontu IF - Sistema de Prontuário Eletrônico</h3>
        </div>
      </div>
    </div>
  </div>



  <div class="row ms-1">

    <div class="card me-4 mt-3" style="width: 16rem;">
      <img src="assets/img/illustrations/home-profissionais.png" class="card-img-top">
      <div class="card-body align-self-center">
        <a href="/profissionais" type="button" class="btn btn-primary">Profissionais</a>
      </div>
    </div>

    <div class="card me-4  mt-3" style="width: 16rem;">
      <img src="assets/img/illustrations/home-pacientes.png" class="card-img-top">
      <div class="card-body align-self-center">
        <a href="/pacientes" type="button" class="btn btn-primary btn-block">Pacientes</a>
      </div>
    </div>

    <div class="card me-4  mt-3" style="width: 16rem;">
      <img src="assets/img/illustrations/home-prontuario.png" class="card-img-top">
      <div class="card-body align-self-center">
        <a href="/exame" type="button" class="btn btn-primary btn-block">Prontuário</a>
      </div>
    </div>

    <div class="card me-4  mt-3" style="width: 16rem;">
      <img src="assets/img/illustrations/home-prescricao.png" class="card-img-top">
      <div class="card-body align-self-center">
        <a href="/prescricoes" type="button" class="btn btn-primary btn-block">Prescrições</a>
      </div>
    </div>


    <div class="card me-3 mt-3" style="width: 16rem;">
      <img src="assets/img/illustrations/home-relatorio.png" class="card-img-top">
      <div class="card-body align-self-center">
        <a href="relatorios" type="button" class="btn btn-primary btn-block">Relatórios</a>
      </div>
    </div>


  </div>




</div>
</div>
@endsection