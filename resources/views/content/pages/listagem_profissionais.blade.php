@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Profissionais')

{{-- Breadcrumb: Início > Profissionais --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',        'url' => '/'],
      ['label' => 'Profissionais', 'url' => null],
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
      <h4 class="mb-0">Listagem de Profissionais</h4>
      <p class="text-muted small mb-0 mt-1">Gerencie os profissionais de saúde cadastrados</p>
    </div>
    @if(Auth::user()->nivelAcesso() <= 1)
    <a href="/cadastro-profissional" class="btn btn-primary">
      <i class="mdi mdi-account-multiple-plus-outline me-1"></i>Novo Profissional
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

  {{-- ================================================================ --}}
  {{-- MINI-INDICADORES                                                  --}}
  {{-- ================================================================ --}}
  <div class="d-flex flex-wrap gap-2 mb-4">
    <span class="badge bg-label-primary fs-6 px-3 py-2">
      <i class="mdi mdi-doctor me-1"></i>
      {{ $totalProfissionais }} {{ $totalProfissionais == 1 ? 'profissional cadastrado' : 'profissionais cadastrados' }}
    </span>
  </div>

  <div class="card">
    <div class="card-body">

      {{-- Campo de busca: debounce 400ms via JS --}}
      <form method="GET" action="/profissionais" id="busca-form" class="mb-3">
        <div class="input-group">
          <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
          <input type="text" name="busca" id="input-busca" class="form-control"
            placeholder="Buscar por nome ou especialidade..."
            value="{{ $busca ?? '' }}" autocomplete="off">
          @if($busca)
          <a href="/profissionais" class="btn btn-outline-secondary" title="Limpar busca">
            <i class="mdi mdi-close"></i>
          </a>
          @endif
        </div>
      </form>

      {{-- Contador de resultados --}}
      @if($profissionais->total() > 0)
      <p class="text-muted small mb-3">
        Exibindo {{ $profissionais->firstItem() }}–{{ $profissionais->lastItem() }} de {{ $profissionais->total() }}
        {{ $busca ? 'resultado(s) para "' . $busca . '"' : 'profissional(is)' }}
      </p>
      @endif

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Nome</th>
              <th>Especialidade</th>
              <th>Registro</th>
              <th class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse($profissionais as $profissional)
            <tr>
              <td><span class="fw-medium">{{ $profissional->nome }}</span></td>
              <td><span class="badge rounded-pill bg-label-primary">{{ $profissional->especialidade }}</span></td>
              <td class="text-muted">{{ $profissional->registro_profissional ?? '—' }}</td>

              {{-- Ações: botão detalhar + kebab para admin --}}
              <td class="text-end">
                <div class="d-flex align-items-center justify-content-end gap-2">

                  {{-- Botão Detalhar: abre modal com dados completos --}}
                  <button type="button"
                          class="btn btn-sm btn-outline-secondary"
                          data-bs-toggle="modal"
                          data-bs-target="#detalhar-{{ $profissional->id }}">
                    <i class="mdi mdi-information-outline me-1"></i>Detalhar
                  </button>

                  {{-- Menu kebab: editar/excluir (somente admin) --}}
                  @if(Auth::user()->nivelAcesso() <= 1)
                  <div class="dropdown">
                    <button type="button"
                            class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            title="Mais ações">
                      <i class="mdi mdi-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li>
                        <a class="dropdown-item" href="/editar-profissional/{{ $profissional->id }}">
                          <i class="mdi mdi-pencil-outline me-2"></i>Editar
                        </a>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <button type="button" class="dropdown-item text-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#deletar-{{ $profissional->id }}">
                          <i class="mdi mdi-trash-can-outline me-2"></i>Excluir profissional
                        </button>
                      </li>
                    </ul>
                  </div>
                  @endif

                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="text-center text-muted py-5">
                <i class="mdi mdi-account-search-outline mdi-48px d-block mb-2 opacity-25"></i>
                @if($busca)
                  Nenhum profissional encontrado para "<strong>{{ $busca }}</strong>".
                @else
                  Nenhum profissional cadastrado.
                @endif
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $profissionais->links() }}
      </div>
    </div>
  </div>

</div>

{{-- Modais de detalhar e deletar (fora da tabela para evitar problemas de z-index) --}}
@foreach($profissionais as $profissional)

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
              <p class="fw-semibold mb-0">{{ optional($profissional->user)->email ?? '-' }}</p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal Deletar (somente admin) --}}
  @if(Auth::user()->nivelAcesso() <= 1)
  <div class="modal fade" id="deletar-{{ $profissional->id }}" tabindex="-1"
       data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Excluir Profissional</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <p>Deseja excluir o profissional <strong>{{ $profissional->nome }}</strong>?</p>
          <p class="text-muted small mb-0">Esta ação pode ser revertida por um administrador.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <form action="/deletar-profissional/{{ $profissional->id }}" method="POST" style="display:inline">
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

@section('page-script')
<script>
(function () {
  // Debounce: submete o form de busca 400ms após o usuário parar de digitar
  var input = document.getElementById('input-busca');
  var form  = document.getElementById('busca-form');
  var timer = null;
  if (!input) return;
  input.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () { form.submit(); }, 400);
  });

  // Ativa tooltips do Bootstrap nos botões de kebab
  document.querySelectorAll('[title="Mais ações"]').forEach(function (el) {
    new bootstrap.Tooltip(el, { trigger: 'hover' });
  });
})();
</script>
@endsection
