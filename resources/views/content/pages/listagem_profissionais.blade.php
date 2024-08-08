@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Profissionais')

@section('content')

<div class="container-xxll flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Listagem de Profissionais</h3>
          <div class="card-header-elements ms-auto mt-3 mb-1 me-2">
            <a href="/cadastro-profissional" class="btn btn-primary"><i class="mdi mdi-account-multiple-plus-outline mdi-24px me-2"></i>Adicionar Profissional</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="card mt-1">
    <div class="card-body">
      <div class="table-responsive text-nowrap">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th>Nome</th>
              <th>Email</th>
              <th>Contato</th>
              <th>Especialidade</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @foreach($profissionais as $profissional)
            <tr>
              <td><span class="fw-medium">{{ $profissional->nome }}</span></td>
              <td>{{ $profissional->email }}</td>
              <td>{{ $profissional->contato }}</td>
              <td><span class="badge rounded-pill bg-label-primary me-2">{{ $profissional->especialidade }}</span></td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-arrow-down-drop-circle-outline mdi-24px"></i></button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="/editar-profissional/{{ $profissional->id }}"><i class="mdi mdi-account-edit mdi-24px me-1"></i>Editar</a>
                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#confimacao"><i class="mdi mdi-trash-can-outline mdi-24px me-1"></i>Deletar</a>
                  </div>
                  <!-- Modal -->
                  <div class="modal fade" id="confimacao" tabindex="-1" data-bs-backdrop="static" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="exampleModalLabel">Deletar Proficional</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                          </button>
                        </div>
                        <div class="modal-body">
                          <p>Deseja deletar o Profissional?</p>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Voltar</button>
                          <a href="/deletar-profissional/{{ $profissional->id }}" class=" btn btn-danger">Deletar</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>


@endsection