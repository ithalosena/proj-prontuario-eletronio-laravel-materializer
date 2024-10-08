@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Profissionais - Editar Proficional')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Editar Profissional/Usuário</h3>
          <div class="card-header-elements ms-auto mt-3 mb-1 me-2">
            <a href="/profissionais" class="btn btn-default"><i class="mdi mdi-arrow-u-left-bottom mdi-24px me-2"></i>Voltar</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-8 card mt-1">
    <div class="card-body">

      <h5 class="card-title">1. Login</h5>
      <form class="browser-default-validation" action="/atualizar-profissional/{{ $prof->id }}" method="POST">
        @csrf
        @method("PUT")
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $prof->nome }}" name="nome" type="text" class="form-control" id="nome" placeholder="Digite seu nome">
          <label for="nome">Nome</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $prof->email }}" name="email" type="email" class="form-control" id="email" placeholder="Digite seu email">
          <label for="email">Email</label>
        </div>
        <div class="mb-4 form-password-toggle  mt-3">
          <div class="input-group input-group-merge">
            <div class="form-floating form-floating-outline">
              <input value="{{ $prof->senha }}" name="senha" type="password" class="form-control" id="senha" placeholder="Digite sua senha">
              <label for="senha">Senha</label>
            </div>
            <span class="input-group-text cursor-pointer" id="basic-default-password3"><i class="mdi mdi-eye-off-outline mdi-24px"></i></span>
          </div>
        </div>

        <h5 class="card-title">2. Dados Pessoais</h5>

        <div class="form-floating form-floating-outline mb-6 mt-3">
          <input value="{{ $prof->contato }}" name="contato" type="text" class="form-control" id="contato" placeholder="Digite seu contato">
          <label for="contato">Contato</label>
        </div>
        <div class="form-floating form-floating-outline mb-6 mt-3">
          <select name="especialidade" class="form-select" id="especialidade" placeholder="Digite sua especialidade" required>
            <option disabled value="{{ $prof->especialidade }}">{{ $prof->especialidade }}</option>
            <option disabled>--- Selecione uma opção ---</option>
            <option value="Piscólogo(a)">Piscólogo</option>
            <option value="Clinico Geral">Clinico Geral</option>
            <option value="Dentista">Dentista</option>
            <option value="Nutricionista">Nutricionista</option>
            <option value="Fisioterapeuta">Fisioterapeuta</option>
          </select>
          <label for="especialidade">Especialidade</label>
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
        </div>
      </form>
    </div>
  </div>
</div>
@endsection