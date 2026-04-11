@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Prescricoes')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Listagem de Prescricoes</h3>
          <div class="card-header-elements ms-auto mt-3 mb-1 me-2">
            <a href="/cadastro-prescricao" class="btn btn-primary"><i class="mdi mdi-plus-circle-outline mdi-24px me-2"></i>Adicionar Prescricao</a>
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
              <th>Medicamento</th>
              <th>Dosagem</th>
              <th>Frequencia</th>
              <th>Duracao</th>
              <th>Paciente</th>
              <th>Profissional</th>
              <th>Acoes</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @foreach($prescricoes as $prescricao)
            <tr>
              <td><span class="fw-medium">{{ $prescricao->nome_medicamento }}</span></td>
              <td>{{ $prescricao->dosagem }}</td>
              <td>{{ $prescricao->frequencia }}</td>
              <td>{{ $prescricao->duracao }}</td>
              <td>{{ $prescricao->consulta->paciente->nome ?? '-' }}</td>
              <td>{{ $prescricao->consulta->profissional->nome ?? '-' }}</td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-arrow-down-drop-circle-outline mdi-24px"></i></button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="/editar-prescricao/{{ $prescricao->id }}"><i class="mdi mdi-pencil-outline mdi-24px me-1"></i>Editar</a>
                    <a class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deletar-{{ $prescricao->id }}"><i class="mdi mdi-trash-can-outline mdi-24px me-1"></i>Deletar</a>
                  </div>
                  <div class="modal fade" id="deletar-{{ $prescricao->id }}" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">Deletar Prescricao</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                          <p>Deseja deletar a prescricao de <strong>{{ $prescricao->nome_medicamento }}</strong>?</p>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Voltar</button>
                          <form action="/deletar-prescricao/{{ $prescricao->id }}" method="POST" style="display:inline">
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
