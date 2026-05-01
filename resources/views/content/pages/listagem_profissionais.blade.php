@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Profissionais')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
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
              <th>Especialidade</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @foreach($profissionais as $profissional)
            <tr>
              <td><span class="fw-medium">{{ $profissional->nome }}</span></td>
              <td><span class="badge rounded-pill bg-label-primary">{{ $profissional->especialidade }}</span></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#detalhar-{{ $profissional->id }}">
                    <i class="mdi mdi-information-outline me-1"></i>Detalhar
                  </button>
                </div>

                {{-- Modal Detalhar --}}
                <div class="modal fade" id="detalhar-{{ $profissional->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title"><i class="mdi mdi-doctor me-2"></i>{{ $profissional->nome }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                      </div>
                      <div class="modal-body">
                        <div class="row">
                          <div class="col-6 mb-3">
                            <p class="text-muted small mb-1">Especialidade</p>
                            <span class="badge rounded-pill bg-label-primary">{{ $profissional->especialidade ?? '-' }}</span>
                          </div>
                          <div class="col-6 mb-3">
                            <p class="text-muted small mb-1">Registro Profissional</p>
                            <p class="fw-semibold mb-0">{{ $profissional->registro_profissional ?? '-' }}</p>
                          </div>
                          <div class="col-6 mb-3">
                            <p class="text-muted small mb-1">Contato</p>
                            <p class="fw-semibold mb-0">{{ $profissional->contato ?? '-' }}</p>
                          </div>
                          <div class="col-6 mb-0">
                            <p class="text-muted small mb-1">E-mail</p>
                            <p class="fw-semibold mb-0">{{ $profissional->user->email ?? '-' }}</p>
                          </div>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                        @if(Auth::user()->nivelAcesso() <= 1)
                        <a href="/editar-profissional/{{ $profissional->id }}" class="btn btn-outline-primary">
                          <i class="mdi mdi-pencil-outline me-1"></i>Editar
                        </a>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#deletar-{{ $profissional->id }}">
                          <i class="mdi mdi-trash-can-outline me-1"></i>Deletar
                        </button>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>

                {{-- Modal Deletar --}}
                <div class="modal fade" id="deletar-{{ $profissional->id }}" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Deletar Profissional</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                      </div>
                      <div class="modal-body">
                        <p>Deseja deletar o profissional <strong>{{ $profissional->nome }}</strong>?</p>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Voltar</button>
                        <form action="/deletar-profissional/{{ $profissional->id }}" method="POST" style="display:inline">
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
        {{ $profissionais->links() }}
      </div>
    </div>
  </div>
</div>

@endsection
