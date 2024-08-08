@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Pacientes - Editar Paciente')

@section('content')

<div class="container-xxll flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Editar Paciente</h3>
          <div class="card-header-elements ms-auto mt-3 mb-1 me-2">
            <a href="/pacientes" class="btn btn-default"><i class="mdi mdi-arrow-u-left-bottom mdi-24px me-2"></i>Voltar</a>
            <a href="/cadastro-paciente" class="btn btn-primary"><i class="mdi mdi-account-multiple-plus-outline mdi-24px me-2"></i>Adicionar Paciente</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-8 card mt-1">
    <div class="card-body">
      <form class="browser-default-validation" action="/atualizar-paciente/{{ $paciente->id }}" method="POST">
        @csrf
        @method("PUT")
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input required value="{{ $paciente->nome }}" name="nome" type="text" class="form-control" id="nome" placeholder="Digite seu nome">
          <label for="nome">Nome</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $paciente->email }}" name="email" type="email" class="form-control" id="email" placeholder="Digite seu email">
          <label for="email">Email</label>
        </div>
        <div class="mb-4 form-password-toggle mt-3">
          <div class="input-group input-group-merge">
            <div class="form-floating form-floating-outline">
              <input required value="{{ $paciente->senha }}" name="senha" type="password" class="form-control" id="senha" placeholder="Digite sua senha">
              <label for="senha">Senha</label>
            </div>
          </div>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input required value="{{ $paciente->contato }}" name="contato" type="text" class="form-control" id="contato" placeholder="Digite seu contato">
          <label for="contato">Contato</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input required value="{{ $paciente->idade }}" name="idade" type="text" class="form-control" id="idade" placeholder="Digite sua idade">
          <label for="idade">Idade</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <select name="sexo" class="form-select" id="sexo" placeholder="" required>
            <option disabled value="{{$paciente->sexo }}">{{$paciente->sexo }}</option>
            <option disabled>--- Selecione uma opção ---</option>
            <option value="Feminino(a)">Feminino</option>
            <option value="FemininoFeminino">Masculino</option>
            <option value="Prefiro não dizer">Prefiro não dizer</option>
            <option value="Outro">Outro</option>
          </select>
          <label for="sexo">Sexo</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $paciente->documento }}" name="documento" type="text" class="form-control" id="documento" placeholder="Digite seu documento">
          <label for="documento">Documento</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $paciente->endereco }}" name="endereco" type="text" class="form-control" id="endereco" placeholder="Digite seu endereço">
          <label for="endereco">Endereço</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $paciente->matricula }}" name="matricula" type="text" class="form-control" id="matricula" placeholder="Digite sua matrícula">
          <label for="matricula">Matrícula</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $paciente->curso }}" name="curso" type="text" class="form-control" id="curso" placeholder="Digite seu curso">
          <label for="curso">Curso</label>
        </div>

        <div class="card-header header-elements">
          <div class="card-header-elements ms-auto mb-1 me-0">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#confimacao">Atualizar</button>
          </div>

          <!-- Modal -->
          <div class="modal fade" id="confimacao" tabindex="-1" data-bs-backdrop="static" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Operação Bem Sucedida</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                  </button>
                </div>
                <div class="modal-body">
                  <p>Atualização realizada com sucesso.</p>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-default mt-4">Voltar</button>
                </div>
              </div>
            </div>
          </div>
      </form>
    </div>
  </div>
</div>

@endsection