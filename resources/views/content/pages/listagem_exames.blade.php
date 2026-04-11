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
              <th>Profissional</th>
              <th>Data Solicitacao</th>
              <th>Resultado</th>
              <th>Acoes</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @foreach($exames as $exame)
            <tr>
              <td><span class="fw-medium">{{ $exame->tipo }}</span></td>
              <td>{{ $exame->consulta->paciente->nome ?? '-' }}</td>
              <td>{{ $exame->consulta->profissional->nome ?? '-' }}</td>
              <td>{{ $exame->data_solicitacao ? $exame->data_solicitacao->format('d/m/Y') : '-' }}</td>
              <td>
                @if($exame->resultado)
                  <span class="badge rounded-pill bg-label-success">Com resultado</span>
                @else
                  <span class="badge rounded-pill bg-label-warning">Pendente</span>
                @endif
              </td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-arrow-down-drop-circle-outline mdi-24px"></i></button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="/editar-exame/{{ $exame->id }}"><i class="mdi mdi-pencil-outline mdi-24px me-1"></i>Editar</a>
                    <a class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deletar-{{ $exame->id }}"><i class="mdi mdi-trash-can-outline mdi-24px me-1"></i>Deletar</a>
                  </div>
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
