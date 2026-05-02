@php $configData = Helper::appClasses(); @endphp

@extends('layouts/layoutMaster')

@section('title', 'Especialidades')

@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',          'url' => '/'],
      ['label' => 'Configurações',   'url' => null],
      ['label' => 'Especialidades',  'url' => null],
    ]
  ])
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Header --}}
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="mb-0">Especialidades</h4>
      <p class="text-muted small mb-0 mt-1">Gerencie as especialidades disponíveis para profissionais</p>
    </div>
    <span class="badge bg-label-primary fs-6">{{ $especialidades->count() }} cadastradas</span>
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
        <div class="card-header"><h5 class="mb-0">Adicionar Especialidade</h5></div>
        <div class="card-body">
          <form action="/configuracoes/especialidades" method="POST">
            @csrf
            <div class="mb-3">
              <label class="form-label">Nome <span class="text-danger">*</span></label>
              <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                     value="{{ old('nome') }}" placeholder="Ex: Enfermagem" required>
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
        <div class="card-header"><h5 class="mb-0">Especialidades Cadastradas</h5></div>
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
              @forelse($especialidades as $esp)
              <tr>
                <td class="align-middle fw-semibold">{{ $esp->nome }}</td>
                <td class="text-center align-middle">
                  @if($esp->ativo)
                    <span class="badge bg-label-success">Ativa</span>
                  @else
                    <span class="badge bg-label-secondary">Inativa</span>
                  @endif
                </td>
                <td class="text-center align-middle">
                  {{-- Editar --}}
                  <button class="btn btn-sm btn-outline-primary me-1"
                          data-bs-toggle="modal" data-bs-target="#modalEditar{{ $esp->id }}">
                    <i class="mdi mdi-pencil-outline"></i>
                  </button>
                  {{-- Toggle ativo/inativo --}}
                  <form action="/configuracoes/especialidades/{{ $esp->id }}/toggle" method="POST" class="d-inline">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm {{ $esp->ativo ? 'btn-outline-warning' : 'btn-outline-success' }}"
                            title="{{ $esp->ativo ? 'Inativar' : 'Ativar' }}">
                      <i class="mdi mdi-{{ $esp->ativo ? 'eye-off-outline' : 'eye-outline' }}"></i>
                    </button>
                  </form>
                </td>
              </tr>

              {{-- Modal de edição --}}
              <div class="modal fade" id="modalEditar{{ $esp->id }}" tabindex="-1">
                <div class="modal-dialog modal-sm">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Editar Especialidade</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="/configuracoes/especialidades/{{ $esp->id }}" method="POST">
                      @csrf @method('PUT')
                      <div class="modal-body">
                        <label class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" name="nome" class="form-control" value="{{ $esp->nome }}" required>
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
                <td colspan="3" class="text-center text-muted py-4">Nenhuma especialidade cadastrada.</td>
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
