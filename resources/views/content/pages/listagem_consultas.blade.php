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
              <th>Tipo</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse($consultas as $consulta)
            <tr>
              <td><span class="fw-medium">{{ $consulta->data_hora->format('d/m/Y H:i') }}</span></td>
              <td>{{ $consulta->paciente->nome ?? '-' }}</td>
              <td><span class="badge rounded-pill bg-label-primary">{{ $consulta->tipo }}</span></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <a href="/consultas/{{ $consulta->id }}" class="btn btn-sm btn-outline-secondary">
                    <i class="mdi mdi-file-document-outline me-1"></i>Ver Prontuário
                  </a>
                  @if(Auth::user()->nivelAcesso() <= 2)
                  <button type="button" class="btn btn-sm btn-outline-danger ms-3" data-bs-toggle="modal" data-bs-target="#deletar-{{ $consulta->id }}">
                    <i class="mdi mdi-trash-can-outline me-1"></i>Deletar
                  </button>
                  @endif
                </div>

                {{-- Modal Deletar --}}
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

              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="text-center text-muted py-4">Nenhuma consulta registrada.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@endsection
