@php $configData = Helper::appClasses(); @endphp

@extends('layouts/layoutMaster')

@section('title', 'Tipos de Consulta')

@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',           'url' => '/'],
      ['label' => 'Configurações',    'url' => null],
      ['label' => 'Tipos de Consulta','url' => null],
    ]
  ])
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Header --}}
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="mb-0">Tipos de Consulta</h4>
      <p class="text-muted small mb-0 mt-1">Gerencie os tipos disponíveis no formulário de consulta</p>
    </div>
    <span class="badge bg-label-primary fs-6">{{ $tipos->count() }} cadastrados</span>
  </div>

  {{-- Flash --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible mb-3" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible mb-3" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="row">

    {{-- Formulário de adição --}}
    <div class="col-md-4 mb-4">
      <div class="card">
        <div class="card-header"><h5 class="mb-0">Adicionar Tipo</h5></div>
        <div class="card-body">
          <form action="/configuracoes/tipos-consulta" method="POST">
            @csrf
            <div class="mb-3">
              <label class="form-label">Nome <span class="text-danger">*</span></label>
              <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                     value="{{ old('nome') }}" placeholder="Ex: Urgência" required>
              @error('nome')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <button type="submit" class="btn btn-primary w-100">
              <i class="mdi mdi-plus me-1"></i> Adicionar
            </button>
          </form>
        </div>
      </div>
    </div>

    {{-- Listagem --}}
    <div class="col-md-8">
      <div class="card">
        <div class="card-header"><h5 class="mb-0">Tipos Cadastrados</h5></div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Nome</th>
                <th class="text-center">Status</th>
                <th class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              @forelse($tipos as $tipo)
              <tr>
                <td class="align-middle fw-semibold">{{ $tipo->nome }}</td>
                <td class="text-center align-middle">
                  @if($tipo->ativo)
                    <span class="badge bg-label-success">Ativo</span>
                  @else
                    <span class="badge bg-label-secondary">Inativo</span>
                  @endif
                </td>
                <td class="text-center align-middle">
                  <button class="btn btn-sm btn-outline-primary me-1"
                          data-bs-toggle="modal" data-bs-target="#modalEditar{{ $tipo->id }}">
                    <i class="mdi mdi-pencil-outline"></i>
                  </button>
                  <form action="/configuracoes/tipos-consulta/{{ $tipo->id }}/toggle" method="POST" class="d-inline">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm {{ $tipo->ativo ? 'btn-outline-warning' : 'btn-outline-success' }}"
                            title="{{ $tipo->ativo ? 'Inativar' : 'Ativar' }}">
                      <i class="mdi mdi-{{ $tipo->ativo ? 'eye-off-outline' : 'eye-outline' }}"></i>
                    </button>
                  </form>
                </td>
              </tr>

              {{-- Modal de edição --}}
              <div class="modal fade" id="modalEditar{{ $tipo->id }}" tabindex="-1">
                <div class="modal-dialog modal-sm">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Editar Tipo</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="/configuracoes/tipos-consulta/{{ $tipo->id }}" method="POST">
                      @csrf @method('PUT')
                      <div class="modal-body">
                        <label class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" name="nome" class="form-control" value="{{ $tipo->nome }}" required>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm">Salvar</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              @empty
              <tr>
                <td colspan="3" class="text-center text-muted py-4">Nenhum tipo cadastrado.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
