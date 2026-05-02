@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Prescrições')

{{-- Breadcrumb: Início > Prescrições --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',      'url' => '/'],
      ['label' => 'Prescrições', 'url' => null],
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
      <h4 class="mb-0">Listagem de Prescrições</h4>
      <p class="text-muted small mb-0 mt-1">Prescrições médicas vinculadas a consultas</p>
    </div>
    @if(Auth::user()->nivelAcesso() <= 3)
    <a href="/cadastro-prescricao" class="btn btn-primary">
      <i class="mdi mdi-plus-circle-outline me-1"></i>Adicionar Prescrição
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

  {{-- Mini-indicador: total de prescrições --}}
  <div class="d-flex flex-wrap gap-2 mb-4">
    <span class="badge bg-label-primary fs-6 px-3 py-2">
      <i class="mdi mdi-pill me-1"></i>
      {{ $totalPrescricoes }} {{ $totalPrescricoes == 1 ? 'prescrição cadastrada' : 'prescrições cadastradas' }}
    </span>
  </div>

  <div class="card">
    <div class="card-body">

      {{-- Contador de resultados --}}
      @if($prescricoes->total() > 0)
      <p class="text-muted small mb-3">
        Exibindo {{ $prescricoes->firstItem() }}–{{ $prescricoes->lastItem() }} de {{ $prescricoes->total() }} prescrição(ões)
      </p>
      @endif

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Medicamento</th>
              <th>Paciente</th>
              <th>Dosagem</th>
              <th>Frequência</th>
              <th class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse($prescricoes as $prescricao)
            <tr>
              <td><span class="fw-medium">{{ $prescricao->nome_medicamento }}</span></td>
              <td class="text-muted">{{ optional($prescricao->consulta)->paciente->nome ?? '-' }}</td>
              <td class="text-muted">{{ $prescricao->dosagem ?? '—' }}</td>
              <td class="text-muted">{{ $prescricao->frequencia ?? '—' }}</td>
              <td class="text-end">
                <div class="d-flex align-items-center justify-content-end gap-2">
                  <button type="button" class="btn btn-sm btn-outline-secondary"
                          data-bs-toggle="modal" data-bs-target="#detalhar-{{ $prescricao->id }}">
                    <i class="mdi mdi-information-outline me-1"></i>Detalhar
                  </button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-5">
                <i class="mdi mdi-pill-off mdi-48px d-block mb-2 opacity-25"></i>
                Nenhuma prescrição cadastrada.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $prescricoes->links() }}
      </div>
    </div>
  </div>

</div>

{{-- Modais de detalhar e deletar (fora da tabela para evitar problemas de z-index) --}}
@foreach($prescricoes as $prescricao)

  {{-- Modal Detalhar --}}
  <div class="modal fade" id="detalhar-{{ $prescricao->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="mdi mdi-pill me-2"></i>{{ $prescricao->nome_medicamento }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-6 mb-3">
              <p class="text-muted small mb-1">Paciente</p>
              <p class="fw-semibold mb-0">{{ optional($prescricao->consulta)->paciente->nome ?? '-' }}</p>
            </div>
            <div class="col-6 mb-3">
              <p class="text-muted small mb-1">Profissional</p>
              <p class="fw-semibold mb-0">{{ optional($prescricao->consulta)->profissional->nome ?? '-' }}</p>
            </div>
            <div class="col-4 mb-3">
              <p class="text-muted small mb-1">Dosagem</p>
              <p class="fw-semibold mb-0">{{ $prescricao->dosagem ?? '-' }}</p>
            </div>
            <div class="col-4 mb-3">
              <p class="text-muted small mb-1">Frequência</p>
              <p class="fw-semibold mb-0">{{ $prescricao->frequencia ?? '-' }}</p>
            </div>
            <div class="col-4 mb-3">
              <p class="text-muted small mb-1">Duração</p>
              <p class="fw-semibold mb-0">{{ $prescricao->duracao ?? '-' }}</p>
            </div>
            @if($prescricao->observacao)
            <div class="col-12 mb-0">
              <p class="text-muted small mb-1">Observação</p>
              <p class="fw-semibold mb-0">{{ $prescricao->observacao }}</p>
            </div>
            @endif
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
          @if(Auth::user()->nivelAcesso() <= 3)
          <a href="/editar-prescricao/{{ $prescricao->id }}" class="btn btn-outline-primary">
            <i class="mdi mdi-pencil-outline me-1"></i>Editar
          </a>
          @endif
          @if(Auth::user()->nivelAcesso() <= 2)
          <button type="button" class="btn btn-danger"
                  data-bs-dismiss="modal"
                  data-bs-toggle="modal"
                  data-bs-target="#deletar-{{ $prescricao->id }}">
            <i class="mdi mdi-trash-can-outline me-1"></i>Deletar
          </button>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Modal Deletar (somente coordenador e acima) --}}
  @if(Auth::user()->nivelAcesso() <= 2)
  <div class="modal fade" id="deletar-{{ $prescricao->id }}" tabindex="-1"
       data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Deletar Prescrição</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <p>Deseja deletar a prescrição de <strong>{{ $prescricao->nome_medicamento }}</strong>?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Voltar</button>
          <form action="/deletar-prescricao/{{ $prescricao->id }}" method="POST" style="display:inline">
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
