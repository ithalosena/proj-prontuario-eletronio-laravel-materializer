@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Pacientes')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <h3 class="align-text-bottom-2">Listagem de Pacientes</h3>
          <div class="card-header-elements ms-auto mt-3 mb-1 me-2">
            <a href="/cadastro-paciente" class="btn btn-primary"><i class="mdi mdi-account-multiple-plus-outline mdi-24px me-2"></i>Adicionar Paciente</a>
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

      <div class="table-responsive text-nowrap">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th>Nome</th>
              <th>Matrícula</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse($pacientes as $paciente)
            <tr>
              <td><span class="fw-medium">{{ $paciente->nome }}</span></td>
              <td>{{ $paciente->matricula ?? '-' }}</td>
              <td>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#detalhar-{{ $paciente->id }}">
                    <i class="mdi mdi-information-outline me-1"></i>Detalhar
                  </button>
                  <a href="/pacientes/{{ $paciente->id }}/historico" class="btn btn-sm btn-outline-secondary">
                    <i class="mdi mdi-history me-1"></i>Histórico
                  </a>
                  {{-- Botão Iniciar Consulta: apenas para profissional (nivel <= 3) --}}
                  @if(Auth::user()->nivelAcesso() <= 3)
                  <a href="/cadastro-consulta?paciente_id={{ $paciente->id }}" class="btn btn-sm btn-primary">
                    <i class="mdi mdi-stethoscope me-1"></i>Iniciar Consulta
                  </a>
                  @endif
                </div>

                {{-- Modal Detalhar --}}
                <div class="modal fade" id="detalhar-{{ $paciente->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title"><i class="mdi mdi-account-outline me-2"></i>{{ $paciente->nome }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                      </div>
                      <div class="modal-body">
                        <p class="text-muted small mb-0">Matrícula: <span class="fw-semibold text-body">{{ $paciente->matricula ?? '-' }}</span></p>
                        <p class="text-muted small mb-3">Curso: <span class="fw-semibold text-body text-break">{{ $paciente->curso ?? '-' }}</span></p>
                        <hr class="mt-0 mb-3">
                        <div class="row">
                          <div class="col-6 mb-3">
                            <p class="text-muted small mb-1">Data de Nascimento</p>
                            <p class="fw-semibold mb-0 text-break">{{ $paciente->data_nascimento ? \Carbon\Carbon::parse($paciente->data_nascimento)->format('d/m/Y') : '-' }}</p>
                          </div>
                          <div class="col-6 mb-3">
                            <p class="text-muted small mb-1">Sexo</p>
                            <p class="fw-semibold mb-0 text-break">{{ $paciente->sexo ?? '-' }}</p>
                          </div>
                          <div class="col-6 mb-3">
                            <p class="text-muted small mb-1">Contato</p>
                            <p class="fw-semibold mb-0 text-break">{{ $paciente->contato ?? '-' }}</p>
                          </div>
                          <div class="col-6 mb-3">
                            <p class="text-muted small mb-1">Documento</p>
                            <p class="fw-semibold mb-0 text-break">{{ $paciente->documento ?? '-' }}</p>
                          </div>
                          <div class="col-12 mb-0">
                            <p class="text-muted small mb-1">E-mail</p>
                            <p class="fw-semibold mb-0 text-break">{{ $paciente->user->email ?? '-' }}</p>
                          </div>
                          @if($paciente->endereco)
                          <div class="col-12 mt-3 mb-0">
                            <p class="text-muted small mb-1">Endereço</p>
                            <p class="fw-semibold mb-0 text-break">{{ $paciente->endereco }}</p>
                          </div>
                          @endif
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                        @if(Auth::user()->nivelAcesso() <= 4)
                        <a href="/editar-paciente/{{ $paciente->id }}" class="btn btn-outline-primary">
                          <i class="mdi mdi-pencil-outline me-1"></i>Editar
                        </a>
                        @endif
                        @if(Auth::user()->nivelAcesso() <= 2)
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#deletar-{{ $paciente->id }}">
                          <i class="mdi mdi-trash-can-outline me-1"></i>Deletar
                        </button>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>

                {{-- Modal Deletar --}}
                <div class="modal fade" id="deletar-{{ $paciente->id }}" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Deletar Paciente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                      </div>
                      <div class="modal-body">
                        <p>Deseja deletar o paciente <strong>{{ $paciente->nome }}</strong>?</p>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Voltar</button>
                        <form action="/deletar-paciente/{{ $paciente->id }}" method="POST" style="display:inline">
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
              <td colspan="3" class="text-center text-muted py-4">
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

@endsection

{{-- Debounce: submete o form de busca 400ms após o usuário parar de digitar --}}
@section('page-script')
<script>
(function () {
  var input = document.getElementById('input-busca');
  var form  = document.getElementById('busca-form');
  var timer = null;
  if (!input) return;
  input.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () { form.submit(); }, 400);
  });
})();
</script>
@endsection
