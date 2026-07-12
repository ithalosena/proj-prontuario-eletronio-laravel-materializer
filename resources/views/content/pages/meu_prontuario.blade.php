@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Meu Prontuário')

{{-- Breadcrumb: Início > Meu Prontuário --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',         'url' => '/'],
      ['label' => 'Meu Prontuário', 'url' => null],
    ]
  ])
@endpush

@section('page-style')
<style>
  /* Abas do prontuário em verde (padrão do paciente), no lugar do roxo do Materialize */
  #prontuario-tabs .nav-link { color: #237030; }
  #prontuario-tabs .nav-link.active { background-color: #3DAA4A !important; color: #fff !important; }
</style>
@endsection

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================ --}}
  {{-- HERO — título + ações LGPD (v0.10.3+: padrão hero, mobile-friendly) --}}
  {{-- L-06 Exportar (Art. 18 V) · Art. 8º §5º Revogar                    --}}
  {{-- ================================================================ --}}
  <div class="card mb-4 text-white border-0"
       style="background: linear-gradient(105deg, #237030 0%, #3DAA4A 50%, #56cf66 100%);">
    <div class="card-body py-4 px-4 position-relative overflow-hidden">
      <div class="position-absolute rounded-circle"
           style="right:-60px; top:-60px; width:200px; height:200px; border:40px solid rgba(255,255,255,0.07); pointer-events:none;"></div>

      <div class="d-flex flex-wrap align-items-center gap-3 position-relative">
        <div class="flex-shrink-0 rounded-3 d-flex align-items-center justify-content-center"
             style="width:56px; height:56px; background:rgba(255,255,255,0.18);">
          <i class="mdi mdi-folder-account-outline mdi-36px"></i>
        </div>
        <div class="flex-grow-1 min-width-0">
          <h4 class="mb-0 fw-bold text-white">Meu Prontuário</h4>
          <p class="mb-0" style="font-size:13px; opacity:.92;">
            Aqui ficam seus <strong>atendimentos</strong>, <strong>exames</strong> e <strong>prescrições</strong>. Consulte seu histórico e exporte seus dados quando precisar.
          </p>
        </div>
        {{-- Ações de privacidade (empilham no mobile) --}}
        <div class="d-flex flex-wrap gap-2">
          <a href="{{ url('/meu-prontuario/exportar') }}"
             class="btn btn-sm fw-semibold" style="background:#fff; color:#237030; border:none;"
             title="Exportar meus dados (LGPD Art. 18, V)">
            <i class="mdi mdi-download-outline me-1"></i>Exportar meus dados
          </a>
          <form method="POST" action="{{ url('/meu-prontuario/revogar-consentimento') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm fw-semibold"
                    style="background:rgba(255,255,255,.12); color:#fff; border:1px solid rgba(255,255,255,.6);"
                    onclick="return confirm('Ao revogar, você será desconectado e perderá o acesso ao prontuário até novo aceite. Confirma?')"
                    title="Revogar consentimento (LGPD Art. 8º §5º)">
              <i class="mdi mdi-shield-off-outline me-1"></i>Revogar consentimento
            </button>
          </form>
        </div>
      </div>
    </div>
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
  {{-- RESUMO DE SAÚDE (autorreferido) — traz os dados clínicos que o     --}}
  {{-- paciente declarou; edição fica em Meu Perfil › Dados de saúde.      --}}
  {{-- ================================================================ --}}
  @php
    $val = fn($v) => filled($v) ? $v : '—';
  @endphp
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header d-flex align-items-center gap-2">
      <i class="mdi mdi-heart-pulse text-primary"></i>
      <h6 class="mb-0">Resumo de saúde</h6>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-6 col-md-3">
          <p class="text-muted small mb-1">Data de nascimento</p>
          <p class="fw-semibold mb-0">{{ $paciente->data_nascimento ? \Carbon\Carbon::parse($paciente->data_nascimento)->format('d/m/Y') : '—' }}</p>
        </div>
        <div class="col-6 col-md-2">
          <p class="text-muted small mb-1">Sexo</p>
          <p class="fw-semibold mb-0">{{ $val($paciente->sexo) }}</p>
        </div>
        <div class="col-6 col-md-3">
          <p class="text-muted small mb-1">Tipo sanguíneo</p>
          <p class="fw-semibold mb-0">{{ $val($paciente->tipo_sanguineo) }}</p>
        </div>
        <div class="col-6 col-md-4">
          <p class="text-muted small mb-1">Peso / Altura</p>
          <p class="fw-semibold mb-0">{{ $paciente->peso_kg ? $paciente->peso_kg.' kg' : '—' }} · {{ $paciente->altura_cm ? $paciente->altura_cm.' cm' : '—' }}</p>
        </div>
        <div class="col-md-6">
          <p class="text-muted small mb-1">Alergias</p>
          <p class="mb-0">{{ $val($paciente->alergias) }}</p>
        </div>
        <div class="col-md-6">
          <p class="text-muted small mb-1">Medicamentos em uso</p>
          <p class="mb-0">{{ $val($paciente->medicamentos_uso_continuo) }}</p>
        </div>
        <div class="col-md-6">
          <p class="text-muted small mb-1">Condições crônicas</p>
          <p class="mb-0">{{ $val($paciente->condicoes_cronicas) }}</p>
        </div>
        <div class="col-md-6">
          <p class="text-muted small mb-1">Cirurgias prévias</p>
          <p class="mb-0">{{ $val($paciente->cirurgias_previas) }}</p>
        </div>
      </div>
      <p class="text-muted small mb-0 mt-3">
        <i class="mdi mdi-information-outline me-1"></i>Informações declaradas por você. Para atualizar, vá em <a href="/perfil">Meu Perfil › Dados de saúde</a>.
      </p>
    </div>
  </div>

  {{-- ================================================================ --}}
  {{-- ABAS: Atendimentos · Exames · Prescrições                          --}}
  {{-- ================================================================ --}}
  <ul class="nav nav-pills gap-1 flex-wrap mb-3" id="prontuario-tabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="tab-atend-btn" data-bs-toggle="pill" data-bs-target="#tab-atend" type="button" role="tab">
        <i class="mdi mdi-clipboard-text-outline me-1"></i>Meus Atendimentos ({{ $consultas->count() }})
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="tab-exames-btn" data-bs-toggle="pill" data-bs-target="#tab-exames" type="button" role="tab">
        <i class="mdi mdi-test-tube-outline me-1"></i>Meus Exames ({{ $exames->count() }})
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="tab-presc-btn" data-bs-toggle="pill" data-bs-target="#tab-presc" type="button" role="tab">
        <i class="mdi mdi-pill me-1"></i>Minhas Prescrições ({{ $prescricoes->count() }})
      </button>
    </li>
  </ul>

  <div class="tab-content">

    {{-- ---------------------------------------------------------- --}}
    {{-- ABA 1: Atendimentos (consultas com registro clínico)        --}}
    {{-- ---------------------------------------------------------- --}}
    <div class="tab-pane fade show active" id="tab-atend" role="tabpanel">
      @forelse($consultas as $consulta)
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
          <div>
            <span class="fw-semibold">{{ $consulta->data_hora->format('d/m/Y \à\s H:i') }}</span>
            <span class="badge rounded-pill bg-label-info ms-2">{{ $consulta->tipo }}</span>
          </div>
          <small class="text-muted"><i class="mdi mdi-doctor me-1"></i>{{ $consulta->profissional->nome ?? '-' }}</small>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              @if($consulta->queixa)
              <div class="mb-3"><p class="text-muted small mb-1">Queixa Principal</p><p class="mb-0">{{ $consulta->queixa }}</p></div>
              @endif
              @if($consulta->diagnostico)
              <div class="mb-3"><p class="text-muted small mb-1">Diagnóstico</p><p class="mb-0">{{ $consulta->diagnostico }}</p></div>
              @endif
              @if($consulta->conduta)
              <div class="mb-0"><p class="text-muted small mb-1">Conduta</p><p class="mb-0">{{ $consulta->conduta }}</p></div>
              @endif
              @if(!$consulta->queixa && !$consulta->diagnostico && !$consulta->conduta)
                <p class="text-muted fst-italic mb-0">Nenhum registro clínico disponível.</p>
              @endif
            </div>
            <div class="col-md-6">
              @if($consulta->exames->count() > 0)
              <div class="mb-3">
                <p class="text-muted small mb-2"><i class="mdi mdi-test-tube-outline me-1"></i>Exames ({{ $consulta->exames->count() }})</p>
                @foreach($consulta->exames as $exame)
                <div class="d-flex align-items-center justify-content-between mb-1 gap-2">
                  <span class="small">{{ $exame->tipo }}</span>
                  <span class="badge flex-shrink-0 {{ $exame->resultado ? 'bg-label-success' : 'bg-label-warning' }}">{{ $exame->resultado ? 'Com resultado' : 'Pendente' }}</span>
                </div>
                @endforeach
              </div>
              @endif
              @if($consulta->prescricoes->count() > 0)
              <div class="mb-0">
                <p class="text-muted small mb-2"><i class="mdi mdi-pill me-1"></i>Prescrições ({{ $consulta->prescricoes->count() }})</p>
                @foreach($consulta->prescricoes as $prescricao)
                <div class="mb-1"><span class="small fw-semibold">{{ $prescricao->nome_medicamento }}</span><span class="small text-muted"> — {{ $prescricao->dosagem }}, {{ $prescricao->frequencia }}</span></div>
                @endforeach
              </div>
              @endif
              @if($consulta->exames->count() === 0 && $consulta->prescricoes->count() === 0)
                <p class="text-muted fst-italic small mb-0">Nenhum exame ou prescrição vinculado.</p>
              @endif
            </div>
          </div>
        </div>
      </div>
      @empty
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center text-muted py-5">
          <i class="mdi mdi-calendar-remove-outline mdi-48px d-block mb-2 opacity-50"></i>
          <p class="mb-0">Nenhum atendimento registrado até o momento.</p>
        </div>
      </div>
      @endforelse
    </div>

    {{-- ---------------------------------------------------------- --}}
    {{-- ABA 2: Exames (achatados de todas as consultas)             --}}
    {{-- ---------------------------------------------------------- --}}
    <div class="tab-pane fade" id="tab-exames" role="tabpanel">
      @forelse($exames as $exame)
      <div class="card border-0 shadow-sm mb-2">
        <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
          <div class="min-width-0">
            <p class="mb-0 fw-semibold">{{ $exame->tipo ?? 'Exame' }}</p>
            <p class="mb-0 text-muted small">
              <i class="mdi mdi-calendar-outline me-1"></i>{{ optional(optional($exame->consulta)->data_hora)->format('d/m/Y') ?? '—' }}
              &nbsp;·&nbsp;<i class="mdi mdi-doctor me-1"></i>{{ optional(optional($exame->consulta)->profissional)->nome ?? '—' }}
            </p>
            @if($exame->resultado)
              <p class="mb-0 small mt-1">{{ $exame->resultado }}</p>
            @endif
          </div>
          <span class="badge flex-shrink-0 {{ $exame->resultado ? 'bg-label-success' : 'bg-label-warning' }}">{{ $exame->resultado ? 'Com resultado' : 'Pendente' }}</span>
        </div>
      </div>
      @empty
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center text-muted py-5">
          <i class="mdi mdi-test-tube-outline mdi-48px d-block mb-2 opacity-50"></i>
          <p class="mb-0">Nenhum exame registrado até o momento.</p>
        </div>
      </div>
      @endforelse
    </div>

    {{-- ---------------------------------------------------------- --}}
    {{-- ABA 3: Prescrições (achatadas de todas as consultas)        --}}
    {{-- ---------------------------------------------------------- --}}
    <div class="tab-pane fade" id="tab-presc" role="tabpanel">
      @forelse($prescricoes as $prescricao)
      <div class="card border-0 shadow-sm mb-2">
        <div class="card-body py-3">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <p class="mb-0 fw-semibold"><i class="mdi mdi-pill me-1 text-primary"></i>{{ $prescricao->nome_medicamento }}</p>
            <small class="text-muted">
              <i class="mdi mdi-calendar-outline me-1"></i>{{ optional(optional($prescricao->consulta)->data_hora)->format('d/m/Y') ?? '—' }}
              &nbsp;·&nbsp;<i class="mdi mdi-doctor me-1"></i>{{ optional(optional($prescricao->consulta)->profissional)->nome ?? '—' }}
            </small>
          </div>
          <p class="mb-0 text-muted small mt-1">
            {{ $prescricao->dosagem }}@if($prescricao->frequencia) · {{ $prescricao->frequencia }}@endif @if($prescricao->duracao) · {{ $prescricao->duracao }}@endif
          </p>
          @if($prescricao->observacao)
          <p class="mb-0 small mt-1">{{ $prescricao->observacao }}</p>
          @endif
        </div>
      </div>
      @empty
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center text-muted py-5">
          <i class="mdi mdi-pill mdi-48px d-block mb-2 opacity-50"></i>
          <p class="mb-0">Nenhuma prescrição registrada até o momento.</p>
        </div>
      </div>
      @endforelse
    </div>

  </div>

</div>

@endsection
