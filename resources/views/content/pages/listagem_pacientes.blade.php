@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Pacientes')

{{-- Breadcrumb: Início > Pacientes --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',    'url' => '/'],
      ['label' => 'Pacientes', 'url' => null],
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
      <h4 class="mb-0">Listagem de Pacientes</h4>
      <p class="text-muted small mb-0 mt-1">Gerencie os pacientes cadastrados no sistema</p>
    </div>
    @if(Auth::user()->nivelAcesso() <= 2)
    <a href="/cadastro-paciente" class="btn btn-primary">
      <i class="mdi mdi-account-multiple-plus-outline me-1"></i>Adicionar Paciente
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
      <i class="mdi mdi-account-group-outline me-1"></i>
      {{ $totalPacientes }} {{ $totalPacientes == 1 ? 'paciente cadastrado' : 'pacientes cadastrados' }}
    </span>
    @if(!is_null($atendimentosAbertos))
    <span class="badge bg-label-success fs-6 px-3 py-2">
      <i class="mdi mdi-folder-open-outline me-1"></i>
      {{ $atendimentosAbertos }} {{ $atendimentosAbertos == 1 ? 'atendimento aberto' : 'atendimentos abertos' }} (seus)
    </span>
    @endif
  </div>

  <div class="card">
    <div class="card-body">

      {{-- Campo de busca: submete automaticamente ao digitar (debounce 400ms via JS) --}}
      <form method="GET" action="/pacientes" id="busca-form" class="mb-3">
        <div class="input-group">
          <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
          <input type="text" name="busca" id="input-busca" class="form-control"
            placeholder="Buscar por nome ou matrícula..."
            value="{{ $busca ?? '' }}" autocomplete="off">
          @if($busca)
          <a href="/pacientes" class="btn btn-outline-secondary" title="Limpar busca">
            <i class="mdi mdi-close"></i>
          </a>
          @endif
        </div>
      </form>

      {{-- Contador de resultados --}}
      @if($pacientes->total() > 0)
      <p class="text-muted small mb-3">
        Exibindo {{ $pacientes->firstItem() }}–{{ $pacientes->lastItem() }} de {{ $pacientes->total() }}
        {{ $busca ? 'resultado(s) para "' . $busca . '"' : 'paciente(s)' }}
      </p>
      @endif

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Nome</th>
              <th>Matrícula</th>
              <th>Último Atendimento</th>
              <th class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse($pacientes as $paciente)
            <tr>
              {{-- Nome como link clicável para a página de perfil do paciente --}}
              <td>
                <a href="/pacientes/{{ $paciente->id }}"
                   class="fw-medium text-body text-decoration-none"
                   style="border-bottom: 1px dashed currentColor;">
                  {{ $paciente->nome }}
                </a>
              </td>

              <td class="text-muted">{{ $paciente->matricula ?? '—' }}</td>

              {{-- Coluna Último Atendimento --}}
              <td>
                @if($paciente->ultimoAtendimento)
                  <div>
                    <span class="fw-medium small">
                      {{ $paciente->ultimoAtendimento->created_at->format('d/m/Y') }}
                    </span>
                    @if(optional($paciente->ultimoAtendimento->profissional)->especialidade)
                      <span class="text-muted small d-block">
                        {{ $paciente->ultimoAtendimento->profissional->especialidade }}
                      </span>
                    @endif
                  </div>
                  @if($paciente->ultimoAtendimento->status === 'aberto')
                    <span class="badge bg-label-success mt-1">Aberto</span>
                  @else
                    <span class="badge bg-label-secondary mt-1">Fechado</span>
                  @endif
                @else
                  <span class="badge bg-label-warning">Sem atendimentos</span>
                @endif
              </td>

              {{-- Ações: botão primário + menu kebab --}}
              <td class="text-end">
                <div class="d-flex align-items-center justify-content-end gap-2">

                  {{-- "Iniciar Consulta" apenas para profissional de saúde com perfil vinculado --}}
                  @if(Auth::user()->nivelAcesso() <= 3 && Auth::user()->profissional)
                  <a href="/cadastro-consulta?paciente_id={{ $paciente->id }}"
                     class="btn btn-sm btn-primary">
                    <i class="mdi mdi-stethoscope me-1"></i>Iniciar Consulta
                  </a>
                  @endif

                  {{-- Menu kebab: ações secundárias --}}
                  <div class="dropdown">
                    <button type="button"
                            class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            data-bs-placement="left"
                            title="Mais ações">
                      <i class="mdi mdi-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li>
                        <a class="dropdown-item" href="/pacientes/{{ $paciente->id }}">
                          <i class="mdi mdi-account-outline me-2"></i>Ver perfil
                        </a>
                      </li>
                      @if(Auth::user()->nivelAcesso() <= 4)
                      <li>
                        <a class="dropdown-item" href="/editar-paciente/{{ $paciente->id }}">
                          <i class="mdi mdi-pencil-outline me-2"></i>Atualizar dados
                        </a>
                      </li>
                      @endif
                      @if(Auth::user()->nivelAcesso() <= 2)
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <button type="button" class="dropdown-item text-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#deletar-{{ $paciente->id }}">
                          <i class="mdi mdi-trash-can-outline me-2"></i>Excluir paciente
                        </button>
                      </li>
                      @endif
                    </ul>
                  </div>

                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="text-center text-muted py-5">
                <i class="mdi mdi-account-search-outline mdi-48px d-block mb-2 opacity-25"></i>
                @if($busca)
                  Nenhum paciente encontrado para "<strong>{{ $busca }}</strong>".
                @else
                  Nenhum paciente cadastrado.
                @endif
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $pacientes->links() }}
      </div>
    </div>
  </div>

</div>

{{-- Modais de confirmação de exclusão (fora da tabela para evitar problemas de z-index) --}}
@foreach($pacientes as $paciente)
  @if(Auth::user()->nivelAcesso() <= 2)
  <div class="modal fade" id="deletar-{{ $paciente->id }}" tabindex="-1"
       data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Excluir Paciente</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <p>Deseja excluir o paciente <strong>{{ $paciente->nome }}</strong>?</p>
          <p class="text-muted small mb-0">Esta ação pode ser revertida por um administrador.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <form action="/deletar-paciente/{{ $paciente->id }}" method="POST" style="display:inline">
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