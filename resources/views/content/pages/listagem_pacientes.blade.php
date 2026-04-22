@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Pacientes')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Listagem de Pacientes</h3>
          <div class="card-header-elements ms-auto mt-3 mb-1 me-2">
            <a href="/cadastro-paciente" class="btn btn-primary"><i class="mdi mdi-account-multiple-plus-outline mdi-24px me-2"></i>Adicionar Paciente</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible mb-3" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
  </div>
  @endif

  <div class="card mt-1">
    <div class="card-body">
      <div class="table-responsive text-nowrap">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th>Nome</th>
              <th>Matrícula</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @foreach($pacientes as $paciente)
            <tr>
              <td><span class="fw-medium">{{ $paciente->nome }}</span></td>
              <td>{{ $paciente->matricula ?? '-' }}</td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#detalhar-{{ $paciente->id }}">
                    <i class="mdi mdi-information-outline me-1"></i>Detalhar
                  </button>
                  <a href="/pacientes/{{ $paciente->id }}/historico" class="btn btn-sm btn-outline-secondary">
                    <i class="mdi mdi-history me-1"></i>Histórico
                  </a>
                </div>

                {{-- Modal Detalhar --}}
                <div class="modal fade" id="detalhar-{{ $paciente->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title"><i class="mdi mdi-account-outline me-2"></i>{{ $paciente->nome }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                      </div>
                      <div class="modal-body">
                        <p class="text-muted small mb-0">Matrícula: <span class="fw-semibold text-body">{{ $paciente->matricula ?? '-' }}</span></p>
                        <p class="text-muted small mb-3">Curso: <span class="fw-semibold text-body text-break">{{ $paciente->curso ?? '-' }}</span></p>
                        <hr class="mt-0 mb-3">
                        <div class="row">
                          <div class="col-6 mb-3">
                            <p class="text-muted small mb-1">Data de Nascimento</p>
                            <p class="fw-semibold mb-0 text-break">{{ $paciente->data_nascimento ? \Carbon\Carbon::parse($paciente->data_nascimento)->format('d/m/Y') : '-' }}</p>
                          </div>
                          <div class="col-6 mb-3">
                            <p class="text-muted small mb-1">Sexo</p>
                            <p class="fw-semibold mb-0 text-break">{{ $paciente->sexo ?? '-' }}</p>
                          </div>
                          <div class="col-6 mb-3">
                            <p class="text-muted small mb-1">Contato</p>
                            <p class="fw-semibold mb-0 text-break">{{ $paciente->contato ?? '-' }}</p>
                          </div>
                          <div class="col-6 mb-3">
                            <p class="text-muted small mb-1">Documento</p>
                            <p class="fw-semibold mb-0 text-break">{{ $paciente->documento ?? '-' }}</p>
                          </div>
                          <div class="col-12 mb-0">
                            <p class="text-muted small mb-1">E-mail</p>
                            <p class="fw-semibold mb-0 text-break">{{ $paciente->user->email ?? '-' }}</p>
                          </div>
                          @if($paciente->endereco)
                          <div class="col-12 mt-3 mb-0">
                            <p class="text-muted small mb-1">Endereço</p>
                            <p class="fw-semibold mb-0 text-break">{{ $paciente->endereco }}</p>
                          </div>
                          @endif
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                        @if(Auth::user()->nivelAcesso() <= 4)
                        <a href="/editar-paciente/{{ $paciente->id }}" class="btn btn-outline-primary">
                          <i class="mdi mdi-pencil-outline me-1"></i>Editar
                        </a>
                        @endif
                        @if(Auth::user()->nivelAcesso() <= 2)
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#deletar-{{ $paciente->id }}">
                          <i class="mdi mdi-trash-can-outline me-1"></i>Deletar
                        </button>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>

                {{-- Modal Deletar --}}
                <div class="modal fade" id="deletar-{{ $paciente->id }}" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Deletar Paciente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                      </div>
                      <div class="modal-body">
                        <p>Deseja deletar o paciente <strong>{{ $paciente->nome }}</strong>?</p>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Voltar</button>
                        <form action="/deletar-paciente/{{ $paciente->id }}" method="POST" style="display:inline">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-danger">Excluir</button>
                        </form>
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
      <div class="mt-3">
        {{ $pacientes->links() }}
      </div>
    </div>
  </div>
</div>

@endsection
