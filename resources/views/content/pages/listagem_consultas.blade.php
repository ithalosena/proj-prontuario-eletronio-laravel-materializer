@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Consultas')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Listagem de Consultas</h3>
          <div class="card-header-elements ms-auto mt-3 mb-1 me-2">
            <a href="/cadastro-consulta" class="btn btn-primary">
              <i class="mdi mdi-plus-circle-outline mdi-24px me-2"></i>Nova Consulta
            </a>
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
              <th>Data / Hora</th>
              <th>Paciente</th>
              <th>Profissional</th>
              <th>Tipo</th>
              <th>Queixa</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse($consultas as $consulta)
            <tr>
              <td><span class="fw-medium">{{ $consulta->data_hora->format('d/m/Y H:i') }}</span></td>
              <td>{{ $consulta->paciente->nome ?? '-' }}</td>
              <td>{{ $consulta->profissional->nome ?? '-' }}</td>
              <td><span class="badge rounded-pill bg-label-primary">{{ $consulta->tipo }}</span></td>
              <td class="text-wrap" style="max-width: 250px; white-space: normal;">
                {{ Str::limit($consulta->queixa, 60) }}
              </td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="mdi mdi-arrow-down-drop-circle-outline mdi-24px"></i>
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="/editar-consulta/{{ $consulta->id }}">
                      <i class="mdi mdi-pencil-outline mdi-24px me-1"></i>Editar
                    </a>
                    <a class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deletar-{{ $consulta->id }}">
                      <i class="mdi mdi-trash-can-outline mdi-24px me-1"></i>Deletar
                    </a>
                  </div>

                  <div class="modal fade" id="deletar-{{ $consulta->id }}" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">Deletar Consulta</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                          <p>Deseja deletar a consulta de <strong>{{ $consulta->paciente->nome ?? '-' }}</strong>
                            em {{ $consulta->data_hora->format('d/m/Y') }}?</p>
                          <p class="text-danger small mb-0">
                            <i class="mdi mdi-alert-outline me-1"></i>
                            Exames e prescrições vinculados também serão removidos.
                          </p>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Voltar</button>
                          <form action="/deletar-consulta/{{ $consulta->id }}" method="POST" style="display:inline">
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
            @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">Nenhuma consulta registrada.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@endsection
