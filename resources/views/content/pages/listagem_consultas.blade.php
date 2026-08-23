@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Consultas')

{{-- Breadcrumb: Início > Consultas --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',    'url' => '/'],
      ['label' => 'Consultas', 'url' => null],
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
      <h4 class="mb-0">Listagem de Consultas</h4>
      <p class="text-muted small mb-0 mt-1">Gerencie as consultas clínicas registradas</p>
    </div>
    {{-- E3b (v0.11.1, união): consulta nasce dentro do atendimento — a porta aqui é abrir um --}}
    @if(Auth::user()->nivelAcesso() <= 3 && Auth::user()->profissional)
    <a href="/cadastro-atendimento" class="btn btn-primary">
      <i class="mdi mdi-folder-plus-outline me-1"></i>Iniciar Atendimento
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
      <i class="mdi mdi-file-document-multiple-outline me-1"></i>
      {{ $totalConsultas }} {{ $totalConsultas == 1 ? 'consulta registrada' : 'consultas registradas' }}
    </span>
    @if(!is_null($minhasConsultas))
    <span class="badge bg-label-info fs-6 px-3 py-2">
      <i class="mdi mdi-stethoscope me-1"></i>
      {{ $minhasConsultas }} {{ $minhasConsultas == 1 ? 'consulta sua' : 'consultas suas' }}
    </span>
    @endif
  </div>

  <div class="card">
    <div class="card-body">

      {{-- Busca (debounce 400ms via JS) + filtros (UX-05: profissional e tipo) --}}
      <form method="GET" action="/consultas" id="busca-form" class="mb-3">
        <div class="row g-2">
          <div class="col-md">
            <div class="input-group">
              <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
              <input type="text" name="busca" id="input-busca" class="form-control"
                placeholder="Buscar por nome do paciente..."
                value="{{ $busca ?? '' }}" autocomplete="off">
            </div>
          </div>

          {{-- Filtro por profissional: irrelevante para o profissional (nivel 3, vê só as suas) --}}
          @if(Auth::user()->nivelAcesso() != 3)
          <div class="col-md-3">
            <select name="profissional_id" class="form-select" onchange="document.getElementById('busca-form').submit()">
              <option value="">Todos os profissionais</option>
              @foreach($profissionais as $p)
                <option value="{{ $p->id }}" {{ (string)$filtroProfissional === (string)$p->id ? 'selected' : '' }}>{{ $p->nome }}</option>
              @endforeach
            </select>
          </div>
          @endif

          {{-- Filtro por tipo de consulta --}}
          <div class="col-md-3">
            <select name="tipo" class="form-select" onchange="document.getElementById('busca-form').submit()">
              <option value="">Todos os tipos</option>
              @foreach($tipos as $t)
                <option value="{{ $t->nome }}" {{ $filtroTipo === $t->nome ? 'selected' : '' }}>{{ $t->nome }}</option>
              @endforeach
            </select>
          </div>

          @if($busca || $filtroProfissional || $filtroTipo)
          <div class="col-md-auto">
            <a href="/consultas" class="btn btn-outline-secondary" title="Limpar filtros">
              <i class="mdi mdi-close me-1"></i>Limpar
            </a>
          </div>
          @endif
        </div>
      </form>

      {{-- Contador de resultados --}}
      @if($consultas->total() > 0)
      <p class="text-muted small mb-3">
        Exibindo {{ $consultas->firstItem() }}–{{ $consultas->lastItem() }} de {{ $consultas->total() }}
        {{ $busca ? 'resultado(s) para "' . $busca . '"' : 'consulta(s)' }}
      </p>
      @endif

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Data / Hora</th>
              <th>Paciente</th>
              <th>Tipo</th>
              <th>Profissional</th>
              <th class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse($consultas as $consulta)
            <tr>
              <td>
                <span class="fw-medium text-nowrap">{{ $consulta->data_hora->format('d/m/Y') }}</span>
                <br><small class="text-muted">{{ $consulta->data_hora->format('H:i') }}</small>
              </td>

              {{-- Paciente: link para histórico (exceto para o próprio paciente) --}}
              <td>
                @if(Auth::user()->nivelAcesso() <= 4 && $consulta->paciente)
                  {{-- ANALISE-04 (v0.10.1): nome do paciente padronizado → perfil /pacientes/{id} --}}
                  <a href="/pacientes/{{ $consulta->paciente->id }}"
                     class="fw-medium text-body text-decoration-none"
                     style="border-bottom: 1px dashed currentColor;">
                    {{ $consulta->paciente->nome }}
                  </a>
                @else
                  <span class="fw-medium">{{ optional($consulta->paciente)->nome ?? '-' }}</span>
                @endif
              </td>

              <td><span class="badge rounded-pill bg-label-primary">{{ $consulta->tipo }}</span></td>

              <td class="text-muted">{{ optional($consulta->profissional)->nome ?? '-' }}</td>

              {{-- Ações: kebab --}}
              <td class="text-end">
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
                      <a class="dropdown-item" href="/consultas/{{ $consulta->id }}">
                        <i class="mdi mdi-file-document-outline me-2"></i>Ver prontuário
                      </a>
                    </li>
                    {{-- Excluir: autor com atendimento aberto, ou admin --}}
                    @if(Auth::user()->nivelAcesso() <= 1
                        || (Auth::user()->id == $consulta->criado_por_id
                            && optional($consulta->atendimento)->status === 'aberto'))
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <button type="button" class="dropdown-item text-danger"
                              data-bs-toggle="modal"
                              data-bs-target="#deletar-{{ $consulta->id }}">
                        <i class="mdi mdi-trash-can-outline me-2"></i>Excluir consulta
                      </button>
                    </li>
                    @endif
                  </ul>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-5">
                <i class="mdi mdi-file-search-outline mdi-48px d-block mb-2 opacity-25"></i>
                @if($busca)
                  Nenhuma consulta encontrada para "<strong>{{ $busca }}</strong>".
                @else
                  Nenhuma consulta registrada.
                @endif
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $consultas->links() }}
      </div>
    </div>
  </div>

</div>

{{-- Modais de confirmação de exclusão (fora da tabela para evitar problemas de z-index) --}}
@foreach($consultas as $consulta)
  @if(Auth::user()->nivelAcesso() <= 1
      || (Auth::user()->id == $consulta->criado_por_id
          && optional($consulta->atendimento)->status === 'aberto'))
  <div class="modal fade" id="deletar-{{ $consulta->id }}" tabindex="-1"
       data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Excluir Consulta</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <p>Deseja excluir a consulta de <strong>{{ optional($consulta->paciente)->nome ?? '-' }}</strong>
            em {{ $consulta->data_hora->format('d/m/Y') }}?</p>
          <p class="text-danger small mb-0">
            <i class="mdi mdi-alert-outline me-1"></i>
            Exames e prescrições vinculados também serão removidos.
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <form action="/deletar-consulta/{{ $consulta->id }}" method="POST" style="display:inline">
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
