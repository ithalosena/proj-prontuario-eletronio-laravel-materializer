@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Atendimentos')

{{-- Breadcrumb: Início > Atendimentos --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',       'url' => '/'],
      ['label' => 'Atendimentos', 'url' => null],
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
      <h4 class="mb-0">Listagem de Atendimentos</h4>
      <p class="text-muted small mb-0 mt-1">Gerencie os atendimentos do sistema</p>
    </div>
    {{-- ANALISE-01 (v0.10.1): Admin (nivel 1) é somente leitura — coordenador (2) e profissional (3) criam --}}
    @if(Auth::user()->nivelAcesso() >= 2 && Auth::user()->nivelAcesso() <= 3)
    <a href="/cadastro-atendimento" class="btn btn-primary">
      <i class="mdi mdi-plus-circle-outline me-1"></i>Novo Atendimento
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
  @if(session('error'))
  <div class="alert alert-danger alert-dismissible mb-4" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
  </div>
  @endif

  {{-- ================================================================ --}}
  {{-- MINI-INDICADORES                                                  --}}
  {{-- ================================================================ --}}
  <div class="d-flex flex-wrap gap-2 mb-4">
    <span class="badge bg-label-primary fs-6 px-3 py-2">
      <i class="mdi mdi-folder-multiple-outline me-1"></i>
      {{ $totalAtendimentos }} {{ $totalAtendimentos == 1 ? 'atendimento cadastrado' : 'atendimentos cadastrados' }}
    </span>
    @if(!is_null($abertosDoUsuario))
    <span class="badge bg-label-success fs-6 px-3 py-2">
      <i class="mdi mdi-folder-open-outline me-1"></i>
      {{ $abertosDoUsuario }} {{ $abertosDoUsuario == 1 ? 'atendimento aberto' : 'atendimentos abertos' }} (seus)
    </span>
    @endif
  </div>

  <div class="card">
    <div class="card-body">

      {{-- Busca (debounce 400ms via JS) + filtros (UX-05: profissional e status) --}}
      <form method="GET" action="/atendimentos" id="busca-form" class="mb-3">
        <div class="row g-2">
          <div class="col-md">
            <div class="input-group">
              <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
              <input type="text" name="busca" id="input-busca" class="form-control"
                placeholder="Buscar por nome ou matrícula do paciente..."
                value="{{ $busca ?? '' }}" autocomplete="off">
            </div>
          </div>

          {{-- Filtro por profissional: irrelevante para o profissional (nivel 3, vê só os seus) --}}
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

          {{-- Filtro por status --}}
          <div class="col-md-2">
            <select name="status" class="form-select" onchange="document.getElementById('busca-form').submit()">
              <option value="">Todos os status</option>
              <option value="aberto"  {{ $filtroStatus === 'aberto'  ? 'selected' : '' }}>Aberto</option>
              <option value="fechado" {{ $filtroStatus === 'fechado' ? 'selected' : '' }}>Fechado</option>
            </select>
          </div>

          @if($busca || $filtroProfissional || $filtroStatus)
          <div class="col-md-auto">
            <a href="/atendimentos" class="btn btn-outline-secondary" title="Limpar filtros">
              <i class="mdi mdi-close me-1"></i>Limpar
            </a>
          </div>
          @endif
        </div>
      </form>

      {{-- Contador de resultados --}}
      @if($atendimentos->total() > 0)
      <p class="text-muted small mb-3">
        Exibindo {{ $atendimentos->firstItem() }}–{{ $atendimentos->lastItem() }} de {{ $atendimentos->total() }}
        {{ $busca ? 'resultado(s) para "' . $busca . '"' : 'atendimento(s)' }}
      </p>
      @endif

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Paciente</th>
              <th>Profissional</th>
              <th>Aberto por</th>
              <th>Status</th>
              <th>Data</th>
              <th class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">

            @forelse($atendimentos as $atendimento)
            <tr>
              {{-- Paciente: nome e matrícula --}}
              <td>
                @if(Auth::user()->nivelAcesso() <= 4 && $atendimento->paciente)
                  {{-- ANALISE-04 (v0.10.1): nome do paciente padronizado → perfil /pacientes/{id} --}}
                  <a href="/pacientes/{{ $atendimento->paciente->id }}"
                     class="fw-medium text-body text-decoration-none"
                     style="border-bottom: 1px dashed currentColor;">
                    {{ $atendimento->paciente->nome }}
                  </a>
                @else
                  <span class="fw-medium">{{ $atendimento->paciente->nome ?? '-' }}</span>
                @endif
                @if(optional($atendimento->paciente)->matricula)
                  <br><small class="text-muted">{{ $atendimento->paciente->matricula }}</small>
                @endif
              </td>

              {{-- Profissional e especialidade --}}
              <td>
                {{ $atendimento->profissional->nome ?? '-' }}
                @if(optional($atendimento->profissional)->especialidade)
                  <br><small class="text-muted">{{ $atendimento->profissional->especialidade }}</small>
                @endif
              </td>

              {{-- Aberto por: só exibe quando diferente do próprio profissional --}}
              <td>
                @if($atendimento->criadoPor && $atendimento->criadoPor->id !== optional($atendimento->profissional)->user_id)
                  <span class="text-muted small">{{ $atendimento->criadoPor->name }}</span>
                @else
                  <span class="text-muted small">—</span>
                @endif
              </td>

              {{-- Status --}}
              <td>
                @if($atendimento->status === 'aberto')
                  <span class="badge rounded-pill bg-label-success">Aberto</span>
                @else
                  <span class="badge rounded-pill bg-label-secondary">Fechado</span>
                @endif
              </td>

              {{-- Data de abertura --}}
              <td>
                <span class="text-nowrap">{{ $atendimento->created_at->format('d/m/Y') }}</span>
                <br><small class="text-muted">{{ $atendimento->created_at->format('H:i') }}</small>
              </td>

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
                      <a class="dropdown-item" href="/atendimentos/{{ $atendimento->id }}">
                        <i class="mdi mdi-eye-outline me-2"></i>Ver detalhes
                      </a>
                    </li>
                    {{-- Encerrar: dono do atendimento (nivel 3) com status aberto --}}
                    @if(Auth::user()->nivelAcesso() <= 3 && $atendimento->status === 'aberto'
                        && optional(Auth::user()->profissional)->id == $atendimento->profissional_id)
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <button type="button" class="dropdown-item text-warning"
                              data-bs-toggle="modal"
                              data-bs-target="#fechar-{{ $atendimento->id }}">
                        <i class="mdi mdi-folder-lock-outline me-2"></i>Encerrar atendimento
                      </button>
                    </li>
                    @endif
                  </ul>
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-5">
                <i class="mdi mdi-folder-search-outline mdi-48px d-block mb-2 opacity-25"></i>
                @if($busca)
                  Nenhum atendimento encontrado para "<strong>{{ $busca }}</strong>".
                @else
                  Nenhum atendimento cadastrado.
                @endif
              </td>
            </tr>
            @endforelse

          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $atendimentos->links() }}
      </div>
    </div>
  </div>

</div>

{{-- Modais de confirmação de encerramento (fora da tabela para evitar problemas de z-index) --}}
@foreach($atendimentos as $atendimento)
  @if(Auth::user()->nivelAcesso() <= 3 && $atendimento->status === 'aberto'
      && optional(Auth::user()->profissional)->id == $atendimento->profissional_id)
  <div class="modal fade" id="fechar-{{ $atendimento->id }}" tabindex="-1"
       data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Encerrar Atendimento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <p>Deseja encerrar o atendimento de <strong>{{ optional($atendimento->paciente)->nome }}</strong>?</p>
          <p class="text-muted small mb-0">As consultas vinculadas ficam em modo somente leitura após o encerramento.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <form action="/atendimentos/{{ $atendimento->id }}/fechar" method="POST" style="display:inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-warning">
              <i class="mdi mdi-folder-lock-outline me-1"></i>Encerrar
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
