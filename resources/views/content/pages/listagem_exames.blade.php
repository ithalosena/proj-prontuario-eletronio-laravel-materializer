@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Exames')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Listagem de Exames</h3>
          <div class="card-header-elements ms-auto mt-3 mb-1 me-2">
            <a href="/cadastro-exame" class="btn btn-primary"><i class="mdi mdi-plus-circle-outline mdi-24px me-2"></i>Adicionar Exame</a>
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
              <th>Tipo</th>
              <th>Paciente</th>
              <th>Resultado</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @foreach($exames as $exame)
            <tr>
              <td><span class="fw-medium">{{ $exame->tipo }}</span></td>
              <td>{{ $exame->consulta->paciente->nome ?? '-' }}</td>
              <td>
                @if($exame->resultado)
                  <span class="badge rounded-pill bg-label-success">Com resultado</span>
                @else
                  <span class="badge rounded-pill bg-label-warning">Pendente</span>
                @endif
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#detalhar-{{ $exame->id }}">
                    <i class="mdi mdi-information-outline me-1"></i>Detalhar
                  </button>
                </div>

                {{-- Modal Detalhar --}}
                <div class="modal fade" id="detalhar-{{ $exame->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title"><i class="mdi mdi-test-tube me-2"></i>{{ $exame->tipo }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                      </div>
                      <div class="modal-body">
                        <div class="row">
                          <div class="col-6 mb-3">
                            <p class="text-muted small mb-1">Paciente</p>
                            <p class="fw-semibold mb-0">{{ $exame->consulta->paciente->nome ?? '-' }}</p>
                          </div>
                          <div class="col-6 mb-3">
                            <p class="text-muted small mb-1">Profissional</p>
                            <p class="fw-semibold mb-0">{{ $exame->consulta->profissional->nome ?? '-' }}</p>
                          </div>
                          <div class="col-6 mb-3">
                            <p class="text-muted small mb-1">Data de Solicitação</p>
                            <p class="fw-semibold mb-0">{{ $exame->data_solicitacao ? $exame->data_solicitacao->format('d/m/Y') : '-' }}</p>
                          </div>
                          <div class="col-6 mb-3">
                            <p class="text-muted small mb-1">Resultado</p>
                            @if($exame->resultado)
                              <span class="badge bg-label-success">Com resultado</span>
                            @else
                              <span class="badge bg-label-warning">Pendente</span>
                            @endif
                          </div>
                          @if($exame->observacao)
                          <div class="col-12 mb-0">
                            <p class="text-muted small mb-1">Observação</p>
                            <p class="fw-semibold mb-0">{{ $exame->observacao }}</p>
                          </div>
                          @endif
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                        @if(Auth::user()->nivelAcesso() <= 3)
                        <a href="/editar-exame/{{ $exame->id }}" class="btn btn-outline-primary">
                          <i class="mdi mdi-pencil-outline me-1"></i>Editar
                        </a>
                        @endif
                        @if(Auth::user()->nivelAcesso() <= 2)
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#deletar-{{ $exame->id }}">
                          <i class="mdi mdi-trash-can-outline me-1"></i>Deletar
                        </button>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>

                {{-- Modal Deletar --}}
                <div class="modal fade" id="deletar-{{ $exame->id }}" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Deletar Exame</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                      </div>
                      <div class="modal-body">
                        <p>Deseja deletar o exame <strong>{{ $exame->tipo }}</strong>?</p>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Voltar</button>
                        <form action="/deletar-exame/{{ $exame->id }}" method="POST" style="display:inline">
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
        {{ $exames->links() }}
      </div>
    </div>
  </div>
</div>

@endsection
