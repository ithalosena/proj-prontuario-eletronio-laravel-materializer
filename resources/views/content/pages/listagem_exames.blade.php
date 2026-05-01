@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Exames')

{{-- Breadcrumb: Início > Exames --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início', 'url' => '/'],
      ['label' => 'Exames', 'url' => null],
    ]
  ])
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================ --}}
  {{-- HEADER DA PÁGINA                                                  --}}
  {{-- ================================================================ --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h4 class="mb-0">Listagem de Exames</h4>
      <p class="text-muted small mb-0 mt-1">Exames clínicos solicitados em consultas</p>
    </div>
    @if(Auth::user()->nivelAcesso() <= 3)
    <a href="/cadastro-exame" class="btn btn-primary">
      <i class="mdi mdi-plus-circle-outline me-1"></i>Adicionar Exame
    </a>
    @endif
  </div>

  {{-- Flash messages --}}
  @if(session('success'))
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
  </div>
  @endif

  {{-- Mini-indicador: total de exames --}}
  <div class="d-flex flex-wrap gap-2 mb-4">
    <span class="badge bg-label-primary fs-6 px-3 py-2">
      <i class="mdi mdi-test-tube-outline me-1"></i>
      {{ $totalExames }} {{ $totalExames == 1 ? 'exame cadastrado' : 'exames cadastrados' }}
    </span>
  </div>

  <div class="card">
    <div class="card-body">

      {{-- Contador de resultados --}}
      @if($exames->total() > 0)
      <p class="text-muted small mb-3">
        Exibindo {{ $exames->firstItem() }}–{{ $exames->lastItem() }} de {{ $exames->total() }} exame(s)
      </p>
      @endif

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Tipo</th>
              <th>Paciente</th>
              <th>Resultado</th>
              <th>Data Solicitação</th>
              <th class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse($exames as $exame)
            <tr>
              <td><span class="fw-medium">{{ $exame->tipo }}</span></td>
              <td class="text-muted">{{ optional($exame->consulta)->paciente->nome ?? '-' }}</td>
              <td>
                @if($exame->resultado)
                  <span class="badge rounded-pill bg-label-success">Com resultado</span>
                @else
                  <span class="badge rounded-pill bg-label-warning">Pendente</span>
                @endif
              </td>
              <td class="text-muted text-nowrap">
                {{ $exame->data_solicitacao ? $exame->data_solicitacao->format('d/m/Y') : '—' }}
              </td>
              <td class="text-end">
                <div class="d-flex align-items-center justify-content-end gap-2">
                  <button type="button" class="btn btn-sm btn-outline-secondary"
                          data-bs-toggle="modal" data-bs-target="#detalhar-{{ $exame->id }}">
                    <i class="mdi mdi-information-outline me-1"></i>Detalhar
                  </button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-5">
                <i class="mdi mdi-test-tube-empty mdi-48px d-block mb-2 opacity-25"></i>
                Nenhum exame cadastrado.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $exames->links() }}
      </div>
    </div>
  </div>

</div>

{{-- Modais de detalhar e deletar (fora da tabela para evitar problemas de z-index) --}}
@foreach($exames as $exame)

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
              <p class="fw-semibold mb-0">{{ optional($exame->consulta)->paciente->nome ?? '-' }}</p>
            </div>
            <div class="col-6 mb-3">
              <p class="text-muted small mb-1">Profissional</p>
              <p class="fw-semibold mb-0">{{ optional($exame->consulta)->profissional->nome ?? '-' }}</p>
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
          <button type="button" class="btn btn-danger"
                  data-bs-dismiss="modal"
                  data-bs-toggle="modal"
                  data-bs-target="#deletar-{{ $exame->id }}">
            <i class="mdi mdi-trash-can-outline me-1"></i>Deletar
          </button>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Modal Deletar (somente coordenador e acima) --}}
  @if(Auth::user()->nivelAcesso() <= 2)
  <div class="modal fade" id="deletar-{{ $exame->id }}" tabindex="-1"
       data-bs-backdrop="static" aria-hidden="true">
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
            <button type="submit" class="btn btn-danger">
              <i class="mdi mdi-trash-can-outline me-1"></i>Excluir
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
  @endif

@endforeach

@endsection
