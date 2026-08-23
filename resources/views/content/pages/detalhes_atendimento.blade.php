@php
$configData = Helper::appClasses();

// E3b/E3c/E3d (v0.11.1): tela-container do atendimento. O form de consulta vive AQUI
// (partial _form_consulta) — registro em tempo real, sem trocar de página.
// Layout (DEC-4/DEC-5): esquerda = timeline de consultas (some quando vazia) + form;
// direita = Dados de Saúde (colapsável, aberto) + Histórico Recente (colapsável, aberto).
$pac = $atendimento->paciente;

// DEC-5: nome de exibição segue a regra v0.10.3 — profissional vê "Social (Registro)"
$nomeHero = $pac->nome_profissional ?? ($pac->nome ?? '-');
$iniciais = collect(explode(' ', $pac->nome_exibicao ?? $pac->nome ?? 'P'))
    ->filter()->map(fn($p) => strtoupper($p[0]))->take(2)->implode('');

// DEC-5: foto de perfil do paciente (users.avatar), com fallback para as iniciais.
// null-safe: paciente pode não ter user vinculado (ex.: importado via CSV).
$avatarUser = $pac?->user?->avatar;
$avatarPac  = ($avatarUser && Storage::disk('public')->exists($avatarUser))
    ? Storage::url($avatarUser)
    : null;

$atendAberto  = $atendimento->isAberto();
$podeEncerrar = $atendAberto
    && ((Auth::user()->nivelAcesso() <= 3 && Auth::id() === $atendimento->criado_por_id)
        || Auth::user()->nivelAcesso() <= 1);

// Quem pode registrar consulta: nivel 2/3 com o atendimento aberto (Admin nivel 1 é só leitura)
$mostrarForm = $atendAberto
    && Auth::user()->nivelAcesso() !== 1
    && Auth::user()->nivelAcesso() <= 3;

// Linha do tempo: mais recente primeiro (feed). DEC-5: o card de consultas só aparece
// quando HÁ consultas (ou em modo leitura) — vazio + form aberto era redundante.
$consultasOrdenadas = $atendimento->consultas->sortByDesc('data_hora')->values();
$temConsultas       = $consultasOrdenadas->isNotEmpty();

// Form começa ABERTO quando: validação falhou, veio de ?nova=1, ou não há consulta
// ainda (realizar/espontâneo aterrissam direto no form pronto — e sem consultas o
// form não tem toggle visível, ou seja, fica efetivamente sempre aberto).
$formAberto = $mostrarForm
    && ($errors->any() || request()->boolean('nova') || !$temConsultas);

// Especialidade pré-seleciona o tipo da consulta no form
$especialidade = $profissionalLogado->especialidade
    ?? ($atendimento->profissional->especialidade ?? null);

// Dados de saúde do paciente (ST-15) — só os CRÍTICOS para a hora do atendimento
$alergias     = trim((string) ($pac->alergias ?? ''));
$medicamentos = trim((string) ($pac->medicamentos_uso_continuo ?? ''));
$cronicas     = trim((string) ($pac->condicoes_cronicas ?? ''));
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Atendimento #' . $atendimento->id)

{{-- Chevron dos cards colapsáveis gira quando recolhido --}}
@section('page-style')
<style>
  [data-bs-toggle="collapse"] .chevron-colapso { transition: transform .2s ease; }
  [data-bs-toggle="collapse"].collapsed .chevron-colapso { transform: rotate(180deg); }
</style>
@endsection

{{-- Breadcrumb: Início > Atendimentos > Atendimento #ID --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',                           'url' => '/'],
      ['label' => 'Atendimentos',                     'url' => '/atendimentos'],
      ['label' => 'Atendimento #' . $atendimento->id, 'url' => null],
    ]
  ])
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================ --}}
  {{-- HERO degradê verde — identidade do episódio (DEC-5: realinhado:   --}}
  {{-- ações no topo-direito, meta em duas linhas, foto + nome social)   --}}
  {{-- ================================================================ --}}
  <div class="card mb-4 text-white border-0"
       style="background: linear-gradient(105deg, #237030 0%, #3DAA4A 50%, #56cf66 100%);">
    <div class="card-body py-4 px-4 position-relative overflow-hidden">
      <div class="position-absolute rounded-circle"
           style="right:-60px; top:-60px; width:200px; height:200px; border:40px solid rgba(255,255,255,0.07); pointer-events:none;"></div>

      <div class="d-flex flex-wrap align-items-start gap-3 position-relative">

        {{-- Foto de perfil do paciente (ou iniciais no box translúcido) --}}
        @if($avatarPac)
          <img src="{{ $avatarPac }}" alt="Foto de {{ $pac->nome_exibicao }}"
               class="rounded-3 flex-shrink-0"
               style="width:56px; height:56px; object-fit:cover; border:2px solid rgba(255,255,255,.35);">
        @else
          <div class="flex-shrink-0 rounded-3 d-flex align-items-center justify-content-center fw-bold"
               style="width:56px; height:56px; background:rgba(255,255,255,0.18); font-size:1.3rem;">
            {{ $iniciais }}
          </div>
        @endif

        {{-- Nome (social) + badges + meta em duas linhas --}}
        <div class="flex-grow-1 min-width-0">
          <div class="d-flex flex-wrap align-items-center gap-2">
            @if(Auth::user()->nivelAcesso() <= 4 && $pac)
              <a href="/pacientes/{{ $pac->id }}"
                 class="fw-bold text-white text-decoration-none"
                 style="font-size:1.35rem;">{{ $nomeHero }}</a>
            @else
              <h4 class="mb-0 fw-bold text-white">{{ $nomeHero }}</h4>
            @endif

            <span class="badge rounded-pill" style="background:rgba(255,255,255,.22); color:#fff;">
              <i class="mdi {{ $atendAberto ? 'mdi-folder-open-outline' : 'mdi-folder-lock-outline' }} me-1"></i>
              {{ $atendAberto ? 'Aberto' : 'Fechado' }}
            </span>

            @if($atendimento->isAgendado())
              <span class="badge rounded-pill" style="background:rgba(255,255,255,.22); color:#fff;">
                <i class="mdi mdi-calendar-check-outline me-1"></i>Agendado
              </span>
            @else
              <span class="badge rounded-pill" style="background:rgba(255,255,255,.22); color:#fff;">
                <i class="mdi mdi-account-arrow-right-outline me-1"></i>Espontâneo
              </span>
            @endif
          </div>

          {{-- Linha 1: identificação do episódio e do paciente --}}
          <div class="d-flex flex-wrap column-gap-3 row-gap-1 mt-1" style="font-size:13px; opacity:.92;">
            <span><i class="mdi mdi-folder-open-outline me-1"></i>Atendimento #{{ $atendimento->id }}</span>
            @if($pac->matricula ?? null)
              <span><i class="mdi mdi-card-account-details-outline me-1"></i>{{ $pac->matricula }}</span>
            @endif
            @if($pac->curso ?? null)
              <span><i class="mdi mdi-school-outline me-1"></i>{{ $pac->curso }}</span>
            @endif
            @if($atendimento->isAgendado())
              <a href="/agendamentos/{{ $atendimento->agendamento_id }}" class="text-white"
                 style="text-decoration:underline; text-underline-offset:3px;">
                <i class="mdi mdi-calendar-check-outline me-1"></i>Agendamento #{{ $atendimento->agendamento_id }}
              </a>
            @endif
          </div>

          {{-- Linha 2: quem cuida e quando (absorve o antigo card "Informações") --}}
          <div class="d-flex flex-wrap column-gap-3 row-gap-1 mt-1" style="font-size:13px; opacity:.92;">
            <span><i class="mdi mdi-doctor me-1"></i>{{ $atendimento->profissional->nome ?? '-' }}
              @if($atendimento->profissional->especialidade ?? null) · {{ $atendimento->profissional->especialidade }} @endif
            </span>
            <span><i class="mdi mdi-clock-outline me-1"></i>Aberto em {{ $atendimento->created_at->format('d/m/Y \à\s H:i') }}</span>
            {{-- UX-P07 (v0.10.2): rótulo neutro "Registrado por" (recepcionista pode abrir em nome do profissional) --}}
            @if($atendimento->criadoPor)
              <span><i class="mdi mdi-account-edit-outline me-1"></i>Registrado por {{ $atendimento->criadoPor->name }}</span>
            @endif
            @if(!$atendAberto)
              <span><i class="mdi mdi-folder-lock-outline me-1"></i>Fechado em {{ $atendimento->fechado_em?->format('d/m/Y \à\s H:i') }}
                @if($atendimento->fechadoPor) por {{ $atendimento->fechadoPor->name }} @endif
              </span>
            @endif
          </div>
        </div>

        {{-- Ações do episódio — topo-direito (align-items-start do container) --}}
        <div class="flex-shrink-0 d-flex flex-wrap gap-2 ms-auto">
          @if($podeEncerrar)
          <button type="button" class="btn btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modal-encerrar"
                  style="background:#fff; color:#237030; border:none;">
            <i class="mdi mdi-folder-lock-outline me-1"></i>Encerrar
          </button>
          @endif
          <a href="/atendimentos" class="btn btn-sm btn-outline-light">
            <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
          </a>
        </div>

      </div>
    </div>
  </div>

  {{-- DEC-5: no mobile a coluna de saúde fica abaixo do form — alergia é dado de
       segurança e precisa ser vista ANTES de prescrever, então vira faixa no topo --}}
  @if($alergias !== '')
  <div class="alert alert-danger d-lg-none d-flex align-items-start gap-2 mb-4 py-2" role="alert">
    <i class="mdi mdi-alert-outline mt-1"></i>
    <div><strong>Alergias:</strong> {{ $alergias }}</div>
  </div>
  @endif

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

  <div class="row g-4">

    {{-- ============================================================
         COLUNA PRINCIPAL (esquerda):
         1) Timeline das consultas — SÓ quando existe consulta (DEC-5)
            ou em modo leitura (fechado/admin, com estado vazio)
         2) Form de nova consulta logo abaixo
         ============================================================ --}}
    <div class="col-lg-8">

      @if($temConsultas || !$mostrarForm)
      <div class="card mb-4">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
          <h5 class="card-title mb-0 d-flex align-items-center">
            <i class="mdi mdi-clipboard-text-outline me-2 text-primary"></i>Consultas do atendimento
            <span class="badge bg-label-primary ms-2">{{ $atendimento->consultas->count() }}</span>
          </h5>
          @if($mostrarForm)
          <button type="button" class="btn btn-sm btn-primary"
                  data-bs-toggle="collapse" data-bs-target="#nova-consulta"
                  aria-expanded="{{ $formAberto ? 'true' : 'false' }}" aria-controls="nova-consulta">
            <i class="mdi mdi-plus me-1"></i>Nova Consulta
          </button>
          @endif
        </div>

        <div class="card-body">
          @forelse($consultasOrdenadas as $consulta)
          <div class="border rounded p-3 {{ !$loop->last ? 'mb-3' : '' }}">
            <div class="d-flex align-items-start justify-content-between gap-3">
              <div class="flex-grow-1 min-width-0">
                @include('content.pages.partials._consulta_resumo', ['consulta' => $consulta])
              </div>
              <a href="/consultas/{{ $consulta->id }}" class="btn btn-sm btn-outline-secondary flex-shrink-0">
                <i class="mdi mdi-file-document-outline me-1"></i>Abrir
              </a>
            </div>
          </div>
          @empty
          {{-- Só chega aqui em modo leitura (fechado/admin) — com form, o card nem renderiza --}}
          <div class="text-center py-5 text-muted">
            <i class="mdi mdi-clipboard-remove-outline d-block mb-2" style="font-size:2.5rem;"></i>
            <p class="mb-0">Nenhuma consulta foi registrada neste atendimento.</p>
          </div>
          @endforelse
        </div>
      </div>
      @endif

      {{-- Form de nova consulta — collapse (sem consultas: sempre aberto, sem toggles) --}}
      @if($mostrarForm)
      <div class="collapse {{ $formAberto ? 'show' : '' }}" id="nova-consulta">
        <div class="d-flex align-items-center gap-2 mb-3">
          <div class="avatar avatar-sm flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary"><i class="mdi mdi-clipboard-plus-outline"></i></span>
          </div>
          <h5 class="mb-0">Registrar Consulta</h5>
          <span class="text-muted small d-none d-sm-inline">— salva direto neste atendimento</span>
        </div>

        @include('content.pages.partials._form_consulta', [
          'atendimento'        => $atendimento,
          'profissionalLogado' => $profissionalLogado,
          'tiposConsulta'      => $tiposConsulta,
          'especialidade'      => $especialidade,
          'cancelavel'         => $temConsultas,
        ])
      </div>
      @endif

    </div>

    {{-- ============================================================
         COLUNA LATERAL (direita):
         1) Dados de Saúde (colapsável, nasce aberto)
         2) Histórico Recente (colapsável, nasce aberto — DEC-5)
         ============================================================ --}}
    <div class="col-lg-4">

      {{-- (1) Dados de Saúde — os críticos para o atendimento (ST-15) --}}
      <div class="card mb-4">
        <a class="card-header d-flex justify-content-between align-items-center text-decoration-none text-body"
           data-bs-toggle="collapse" href="#dados-saude" role="button"
           aria-expanded="true" aria-controls="dados-saude">
          <h5 class="card-title mb-0"><i class="mdi mdi-heart-pulse me-2 text-danger"></i>Dados de Saúde</h5>
          <i class="mdi mdi-chevron-up chevron-colapso"></i>
        </a>
        {{-- "colapsável mas sempre vem aberto": nasce com .show --}}
        <div class="collapse show" id="dados-saude">
          <div class="card-body pt-2">

            {{-- Alergias: o dado mais crítico — destaque em alerta quando houver --}}
            @if($alergias !== '')
            <div class="alert alert-danger d-flex align-items-start gap-2 mb-3 py-2" role="alert">
              <i class="mdi mdi-alert-outline mt-1"></i>
              <div>
                <p class="mb-0 fw-semibold small text-uppercase" style="letter-spacing:.03em;">Alergias</p>
                <p class="mb-0">{{ $alergias }}</p>
              </div>
            </div>
            @else
            <div class="mb-3">
              <p class="text-muted small mb-1"><i class="mdi mdi-alert-outline me-1"></i>Alergias</p>
              <p class="mb-0 text-muted">Nenhuma registrada</p>
            </div>
            @endif

            {{-- Medicamentos de uso contínuo --}}
            <div class="mb-3">
              <p class="text-muted small mb-1"><i class="mdi mdi-pill me-1"></i>Medicamentos de uso contínuo</p>
              <p class="mb-0 {{ $medicamentos === '' ? 'text-muted' : 'fw-medium' }}">
                {{ $medicamentos !== '' ? $medicamentos : 'Nenhum registrado' }}
              </p>
            </div>

            {{-- Condições crônicas --}}
            <div class="mb-0">
              <p class="text-muted small mb-1"><i class="mdi mdi-heart-pulse me-1"></i>Condições crônicas</p>
              <p class="mb-0 {{ $cronicas === '' ? 'text-muted' : 'fw-medium' }}">
                {{ $cronicas !== '' ? $cronicas : 'Nenhuma registrada' }}
              </p>
            </div>

            {{-- Ponte para o perfil clínico completo do paciente --}}
            @if(Auth::user()->nivelAcesso() <= 4 && $pac)
            <hr class="my-3">
            <a href="/pacientes/{{ $pac->id }}" class="btn btn-sm btn-outline-secondary w-100">
              <i class="mdi mdi-account-details-outline me-1"></i>Perfil clínico completo
            </a>
            @endif

          </div>
        </div>
      </div>

      {{-- (2) Histórico Recente — colapsável (DEC-5); "Ver todos" fora do toggler --}}
      <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
          <a class="d-flex align-items-center flex-grow-1 gap-2 text-decoration-none text-body"
             data-bs-toggle="collapse" href="#historico-recente" role="button"
             aria-expanded="true" aria-controls="historico-recente">
            <h5 class="card-title mb-0 text-nowrap"><i class="mdi mdi-history me-2 text-primary"></i>Histórico Recente</h5>
            <i class="mdi mdi-chevron-up chevron-colapso ms-auto"></i>
          </a>
          @if($pac)
          <a href="/pacientes/{{ $pac->id }}/historico"
             class="btn btn-sm btn-outline-secondary flex-shrink-0">Todos</a>
          @endif
        </div>
        <div class="collapse show" id="historico-recente">
          <div class="card-body p-0">
            @forelse($ultimosAtendimentos as $ant)
            <div class="p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div class="flex-grow-1">
                  @if($ant->isAberto())
                    <p class="mb-0 small fw-semibold">Aberto em {{ $ant->created_at->format('d/m/Y') }}</p>
                  @else
                    <p class="mb-0 small fw-semibold">Fechado em {{ ($ant->fechado_em ?? $ant->created_at)->format('d/m/Y') }}</p>
                  @endif
                  <p class="mb-0 small text-muted">{{ $ant->profissional->nome ?? '-' }}</p>
                  @if($ant->consultas->isNotEmpty())
                  <div class="mt-1 d-flex flex-wrap gap-1">
                    @foreach($ant->consultas->pluck('tipo')->filter()->unique() as $tipoConsulta)
                      <span class="badge bg-label-primary" style="font-size:.65rem">{{ $tipoConsulta }}</span>
                    @endforeach
                  </div>
                  @endif
                </div>
                <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                  <span class="badge rounded-pill bg-label-{{ $ant->isAberto() ? 'success' : 'secondary' }} small">
                    {{ $ant->isAberto() ? 'Aberto' : 'Fechado' }}
                  </span>
                  <a href="/atendimentos/{{ $ant->id }}" class="btn btn-xs btn-outline-primary">Abrir</a>
                </div>
              </div>
            </div>
            @empty
            <div class="p-3 text-muted small text-center">
              <i class="mdi mdi-clipboard-text-clock-outline d-block mb-1" style="font-size:1.5rem;"></i>
              Primeiro atendimento deste paciente.
            </div>
            @endforelse
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

{{-- ============================================================
     Modal de encerramento do atendimento
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
        {{-- UX-P09 (v0.10.2): alerta de exame pendente --}}
        @if($examesPendentes > 0)
        <div class="alert alert-warning d-flex align-items-start gap-2 mb-3" role="alert">
          <i class="mdi mdi-flask-empty-outline mt-1"></i>
          <div>
            Há <strong>{{ $examesPendentes }} exame(s) sem resultado</strong> neste atendimento.
            Considere <strong>agendar um retorno</strong> para avaliar os resultados antes de encerrar.
          </div>
        </div>
        @endif
        {{-- Container: encerrar sem consulta é permitido, mas o modal avisa --}}
        @if($atendimento->consultas->isEmpty())
        <div class="alert alert-info d-flex align-items-start gap-2 mb-3" role="alert">
          <i class="mdi mdi-information-outline mt-1"></i>
          <div>Este atendimento <strong>não tem nenhuma consulta registrada</strong>. Encerrá-lo assim registra um episódio vazio.</div>
        </div>
        @endif
        <p class="mb-1">Após encerrado, as consultas vinculadas não poderão ser editadas.</p>
        <p class="mb-0">
          Deseja agendar um <strong>retorno</strong> para
          <strong>{{ $pac->nome_exibicao ?? 'este paciente' }}</strong>?
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
        {{-- Encerrar e agendar retorno: ST-Retorno (E7 v0.11.2) usará este campo para redirecionar --}}
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
