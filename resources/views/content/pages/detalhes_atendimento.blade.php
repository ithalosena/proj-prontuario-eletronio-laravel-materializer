@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Detalhes do Atendimento')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ============================================================
       Cabeçalho: título do atendimento + botão de encerramento
       O botão "Encerrar Atendimento" só aparece se:
         - O atendimento ainda está aberto (isAberto())
         - E o usuário é admin (nivel <= 1) OU é quem criou o atendimento
       UX-07-RETORNO: o confirm() JS foi substituído por um modal Bootstrap
       que apresenta as opções "Encerrar" e "Encerrar e Agendar Retorno".
       ============================================================ --}}
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-3">
        <div class="card-header header-elements">
          <div>
            <h3 class="align-text-bottom-2 mb-0">Atendimento #{{ $atendimento->id }}</h3>
            <small class="text-muted">Aberto em {{ $atendimento->created_at->format('d/m/Y \à\s H:i') }}</small>
          </div>
          <div class="card-header-elements ms-auto mt-2 mb-1 me-2 d-flex gap-2">
            {{-- Abre o modal de encerramento em vez de submeter direto com confirm() --}}
            @if($atendimento->isAberto() && (Auth::user()->nivelAcesso() <= 3 && Auth::id() === $atendimento->criado_por_id || Auth::user()->nivelAcesso() <= 1))
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modal-encerrar">
              <i class="mdi mdi-folder-lock-outline me-1"></i>Encerrar Atendimento
            </button>
            @endif
            <a href="/atendimentos" class="btn btn-default">
              <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Alertas de sessão (sucesso ou erro após encerramento ou outras ações) --}}
  @if(session('success'))
  <div class="alert alert-success alert-dismissible mb-3" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
  </div>
  @endif

  @if(session('error'))
  <div class="alert alert-danger alert-dismissible mb-3" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
  </div>
  @endif

  <div class="row">

    {{-- ============================================================
         Coluna esquerda: painel de informações do atendimento
         Exibe paciente, profissional, quem abriu e (se fechado) quem encerrou.
         O bloco de encerramento só é renderizado quando status = 'fechado'.
         ============================================================ --}}
    <div class="col-md-4">
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="mdi mdi-information-outline me-2"></i>Informações</h5>
        </div>
        <div class="card-body">

          {{-- Badge de status: verde = aberto, cinza = fechado --}}
          <div class="mb-3">
            <p class="text-muted small mb-1">Status</p>
            @if($atendimento->isAberto())
              <span class="badge rounded-pill bg-label-success fs-6">Aberto</span>
            @else
              <span class="badge rounded-pill bg-label-secondary fs-6">Fechado</span>
            @endif
          </div>

          <div class="mb-3">
            <p class="text-muted small mb-1">Paciente</p>
            {{-- UX-06: link para histórico — excluído para paciente (nivel 5), que usa Meu Prontuário --}}
            @if(Auth::user()->nivelAcesso() <= 4 && $atendimento->paciente)
              <a href="/pacientes/{{ $atendimento->paciente->id }}/historico"
                 class="fw-semibold text-body text-decoration-none d-block mb-0">{{ $atendimento->paciente->nome }}</a>
            @else
              <p class="fw-semibold mb-0">{{ $atendimento->paciente->nome ?? '-' }}</p>
            @endif
            <small class="text-muted">{{ $atendimento->paciente->matricula ?? '' }} — {{ $atendimento->paciente->curso ?? '' }}</small>
            @if(Auth::user()->nivelAcesso() <= 4 && $atendimento->paciente)
            <a href="/pacientes/{{ $atendimento->paciente->id }}/historico"
               class="btn btn-sm btn-outline-info w-100 mt-2">
              <i class="mdi mdi-history me-1"></i>Ver Histórico Completo
            </a>
            @endif
          </div>

          <div class="mb-3">
            <p class="text-muted small mb-1">Profissional</p>
            <p class="fw-semibold mb-0">{{ $atendimento->profissional->nome ?? '-' }}</p>
            <small class="text-muted">{{ $atendimento->profissional->especialidade ?? '' }}</small>
          </div>

          <div class="mb-3">
            <p class="text-muted small mb-1">Aberto por</p>
            <p class="fw-semibold mb-0">{{ $atendimento->criadoPor->name ?? '-' }}</p>
          </div>

          {{-- Dados de encerramento: visível apenas quando o atendimento está fechado --}}
          @if(!$atendimento->isAberto())
          <div class="mb-0">
            <p class="text-muted small mb-1">Fechado por</p>
            <p class="fw-semibold mb-0">{{ $atendimento->fechadoPor->name ?? '-' }}</p>
            <small class="text-muted">{{ $atendimento->fechado_em?->format('d/m/Y \à\s H:i') }}</small>
          </div>
          @endif

        </div>
      </div>

    </div>

    {{-- ============================================================
         Coluna direita: lista de consultas vinculadas ao atendimento
         O botão "Nova Consulta" no cabeçalho só aparece se o atendimento
         estiver aberto — passando o atendimento_id na query string para
         o formulário de consulta pré-preencher os dados automaticamente.
         ============================================================ --}}
    <div class="col-md-8">
      <div class="card mb-4">
        <div class="card-header header-elements">
          <h5 class="card-title mb-0">
            <i class="mdi mdi-calendar-check-outline me-2"></i>
            Consultas ({{ $atendimento->consultas->count() }})
          </h5>
          {{-- Botão só aparece se o atendimento ainda está aberto --}}
          @if($atendimento->isAberto())
          <div class="card-header-elements ms-auto">
            <a href="/cadastro-consulta?atendimento_id={{ $atendimento->id }}" class="btn btn-sm btn-primary">
              <i class="mdi mdi-plus me-1"></i>Nova Consulta
            </a>
          </div>
          @endif
        </div>
        <div class="card-body p-0">

          @forelse($atendimento->consultas as $consulta)
          <div class="p-3 border-bottom">
            <div class="d-flex align-items-start justify-content-between">
              <div>
                {{-- Data/hora + tipo da consulta --}}
                <p class="fw-semibold mb-1">
                  {{ $consulta->data_hora->format('d/m/Y H:i') }}
                  <span class="badge rounded-pill bg-label-primary ms-2">{{ $consulta->tipo }}</span>
                </p>
                {{-- Resumo da queixa: limitado a 100 caracteres para não quebrar o layout --}}
                @if($consulta->queixa)
                  <small class="text-muted d-block">{{ Str::limit($consulta->queixa, 100) }}</small>
                @endif
                {{-- Contadores de exames e prescrições (já carregados via eager loading) --}}
                <small class="text-muted d-block mt-1">
                  <i class="mdi mdi-test-tube-outline me-1"></i>{{ $consulta->exames->count() }} exame(s)
                  <span class="mx-2">·</span>
                  <i class="mdi mdi-pill me-1"></i>{{ $consulta->prescricoes->count() }} prescrição(ões)
                </small>
              </div>
              <a href="/consultas/{{ $consulta->id }}" class="btn btn-sm btn-outline-secondary ms-3 flex-shrink-0">
                <i class="mdi mdi-file-document-outline me-1"></i>Ver
              </a>
            </div>
          </div>

          {{-- Estado vazio: quando o atendimento existe mas ainda não tem consultas --}}
          @empty
          <div class="p-4 text-center text-muted">
            <i class="mdi mdi-calendar-blank-outline mdi-36px d-block mb-2"></i>
            <p class="mb-0">Nenhuma consulta vinculada a este atendimento.</p>
          </div>
          @endforelse

        </div>
      </div>
    </div>

  </div>
</div>

{{-- ============================================================
     Modal de encerramento do atendimento (UX-07-RETORNO)
     Substitui o confirm() JS por duas ações claras:
     - "Encerrar sem agendar": submit normal
     - "Encerrar e Agendar Retorno": submit com hidden agendar_retorno=1
       → quando ST-09 (Agendamentos) for implementado, o controller
         redirecionará para /cadastro-agendamento com os dados do paciente
     ============================================================ --}}
<div class="modal fade" id="modal-encerrar" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="mdi mdi-folder-lock-outline me-2 text-warning"></i>Encerrar Atendimento
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <p class="mb-1">
          Após encerrado, as consultas vinculadas não poderão ser editadas.
        </p>
        <p class="mb-0">
          Deseja agendar um <strong>retorno</strong> para
          <strong>{{ $atendimento->paciente->nome ?? 'este paciente' }}</strong>?
        </p>
      </div>
      <div class="modal-footer flex-wrap gap-2">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="mdi mdi-close me-1"></i>Cancelar
        </button>
        {{-- Encerrar sem agendar retorno --}}
        <form action="/atendimentos/{{ $atendimento->id }}/fechar" method="POST" class="d-inline">
          @csrf
          @method('PATCH')
          <button type="submit" class="btn btn-warning">
            <i class="mdi mdi-folder-lock-outline me-1"></i>Encerrar sem agendar
          </button>
        </form>
        {{-- Encerrar e agendar retorno: ST-09 usará este campo para redirecionar --}}
        <form action="/atendimentos/{{ $atendimento->id }}/fechar" method="POST" class="d-inline">
          @csrf
          @method('PATCH')
          <input type="hidden" name="agendar_retorno" value="1">
          <button type="submit" class="btn btn-primary">
            <i class="mdi mdi-calendar-plus-outline me-1"></i>Encerrar e Agendar Retorno
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection
