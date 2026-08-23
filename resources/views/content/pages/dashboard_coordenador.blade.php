@php $configData = Helper::appClasses(); @endphp

@extends('layouts/layoutMaster')

@section('title', 'Início')

@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [['label' => 'Início', 'url' => null]]
  ])
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================ --}}
  {{-- SAUDAÇÃO                                                          --}}
  {{-- ================================================================ --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h4 class="mb-0">Bom dia, {{ Auth::user()->name }}</h4>
      <p class="text-muted small mb-0 mt-1">
        Visão geral da operação clínica — {{ now()->isoFormat('MMMM [de] YYYY') }}.
      </p>
    </div>
    <a href="/relatorios" class="btn btn-primary">
      <i class="mdi mdi-file-chart-outline me-1"></i>Relatórios
    </a>
  </div>

  {{-- ================================================================ --}}
  {{-- KPIs                                                              --}}
  {{-- ================================================================ --}}
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-md flex-shrink-0">
            <span class="avatar-initial rounded bg-label-success"><i class="mdi mdi-doctor mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 text-muted small">Profissionais</p>
            <h4 class="mb-0 fw-bold">{{ $totalProfissionais }}</h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-md flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary"><i class="mdi mdi-calendar-check-outline mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 text-muted small">Consultas hoje</p>
            <h4 class="mb-0 fw-bold">{{ $consultasHoje }}</h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-md flex-shrink-0">
            <span class="avatar-initial rounded bg-label-info"><i class="mdi mdi-account-multiple-outline mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 text-muted small">Pacientes no mês</p>
            <h4 class="mb-0 fw-bold">{{ $pacientesNoMes }}</h4>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <div class="avatar avatar-md flex-shrink-0">
            <span class="avatar-initial rounded bg-label-warning"><i class="mdi mdi-gauge mdi-24px"></i></span>
          </div>
          <div>
            <p class="mb-0 text-muted small">Taxa de ocupação</p>
            <h4 class="mb-0 fw-bold">{{ $taxaOcupacao }}%</h4>
            <div class="progress mt-1" style="height:4px;">
              <div class="progress-bar bg-warning" style="width:{{ $taxaOcupacao }}%;"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ================================================================ --}}
  {{-- DISTRIBUIÇÃO POR STATUS + TOP 5 PRODUTIVIDADE                    --}}
  {{-- ================================================================ --}}
  <div class="row g-4 mb-4">

    {{-- Contadores por status (substitui donut — sem biblioteca de gráficos) --}}
    <div class="col-lg-5">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center gap-2">
          <i class="mdi mdi-chart-pie text-primary"></i>
          <h5 class="card-title mb-0">Agendamentos por status</h5>
          <span class="text-muted small ms-1">({{ now()->isoFormat('MMMM') }})</span>
        </div>
        <div class="card-body">
          @php
            $statusDef = [
              'realizado'  => ['label' => 'Realizados',  'color' => 'success'],
              'confirmado' => ['label' => 'Confirmados', 'color' => 'primary'],
              'pendente'   => ['label' => 'Pendentes',   'color' => 'warning'],
              'cancelado'  => ['label' => 'Cancelados',  'color' => 'danger'],
              'nao_compareceu' => ['label' => 'Faltas',   'color' => 'secondary'],
            ];
            $total = $statusCounts->sum();
          @endphp

          @foreach($statusDef as $key => $def)
          @php $count = $statusCounts->get($key, 0); @endphp
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="flex-shrink-0" style="width:90px;">
              <span class="badge bg-label-{{ $def['color'] }} w-100 text-start ps-2">{{ $def['label'] }}</span>
            </div>
            <div class="flex-grow-1">
              <div class="progress" style="height:8px;">
                <div class="progress-bar bg-{{ $def['color'] }}"
                     style="width:{{ $total > 0 ? round(($count/$total)*100) : 0 }}%;"></div>
              </div>
            </div>
            <span class="fw-bold text-body" style="min-width:32px; text-align:right;">{{ $count }}</span>
          </div>
          @endforeach

          <p class="text-muted small mb-0 mt-3">
            <i class="mdi mdi-information-outline me-1"></i>
            Total no mês: <strong>{{ $total }}</strong> agendamentos
          </p>
        </div>
      </div>
    </div>

    {{-- Top 5 produtividade — barras CSS --}}
    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="mdi mdi-trophy-outline text-warning"></i>
            <h5 class="card-title mb-0">Produtividade — top 5</h5>
            <span class="text-muted small">({{ now()->isoFormat('MMMM') }})</span>
          </div>
          <a href="/profissionais" class="btn btn-sm btn-outline-primary">Ver equipe</a>
        </div>
        <div class="card-body">
          @php $maxConsultas = $top5Profissionais->max('consultas_count') ?: 1; @endphp

          @forelse($top5Profissionais as $i => $prof)
          <div class="d-flex align-items-center gap-3 mb-3">
            <span class="text-muted fw-bold flex-shrink-0" style="width:18px;">{{ $i + 1 }}</span>
            <div class="avatar avatar-sm flex-shrink-0">
              <span class="avatar-initial rounded-circle bg-label-primary">
                {{ strtoupper(substr($prof->nome, 0, 1)) }}
              </span>
            </div>
            <div class="flex-grow-1 min-width-0">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-medium text-truncate">{{ $prof->nome }}</span>
                <span class="fw-bold small ms-2 flex-shrink-0">{{ $prof->consultas_count }}</span>
              </div>
              <div class="progress" style="height:6px;">
                <div class="progress-bar" style="width:{{ round(($prof->consultas_count/$maxConsultas)*100) }}%; background:#3DAA4A;"></div>
              </div>
              <p class="text-muted mb-0" style="font-size:10px;">{{ $prof->especialidade ?? 'Especialidade não informada' }}</p>
            </div>
          </div>
          @empty
          <div class="d-flex flex-column align-items-center py-4 text-muted">
            <i class="mdi mdi-chart-bar fs-1 mb-2"></i>
            <p class="mb-0 small">Nenhuma consulta registrada este mês.</p>
          </div>
          @endforelse
        </div>
      </div>
    </div>

  </div>

  {{-- ================================================================ --}}
  {{-- PENDENTES DE CONFIRMAÇÃO + AÇÕES RÁPIDAS                         --}}
  {{-- ================================================================ --}}
  <div class="row g-4">

    {{-- Agendamentos pendentes (substitui bloco "Aprovações" do mockup) --}}
    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="mdi mdi-bell-ring-outline text-warning"></i>
            <h5 class="card-title mb-0">Aguardando confirmação</h5>
          </div>
          @if($pendentesConfirmacao->isNotEmpty())
            <span class="badge bg-label-warning">{{ $pendentesConfirmacao->count() }} pendentes</span>
          @endif
        </div>
        <div class="card-body p-0">
          @forelse($pendentesConfirmacao as $ag)
          <div class="d-flex align-items-center gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
            <div class="avatar avatar-sm flex-shrink-0">
              <span class="avatar-initial rounded-circle bg-label-warning">
                {{ strtoupper(substr($ag->paciente->nome ?? 'P', 0, 1)) }}
              </span>
            </div>
            <div class="flex-grow-1 min-width-0">
              <p class="mb-0 fw-medium small text-truncate">{{ $ag->paciente->nome ?? '—' }}</p>
              <p class="mb-0 text-muted" style="font-size:11px;">
                {{ $ag->data_hora->format('d/m/Y H:i') }} · {{ $ag->profissional->nome ?? '—' }}
              </p>
            </div>
            <a href="/agendamentos/{{ $ag->id }}" class="btn btn-sm btn-outline-success">
              <i class="mdi mdi-check me-1"></i>Ver
            </a>
          </div>
          @empty
          <div class="d-flex flex-column align-items-center py-5 text-muted">
            <i class="mdi mdi-calendar-check fs-1 mb-2"></i>
            <p class="mb-0">Nenhum agendamento pendente.</p>
          </div>
          @endforelse
        </div>
        @if($pendentesConfirmacao->isNotEmpty())
        <div class="card-footer text-center">
          <a href="/agendamentos?status=pendente" class="btn btn-sm btn-outline-warning">
            Ver todos os pendentes
          </a>
        </div>
        @endif
      </div>
    </div>

    {{-- Ações rápidas --}}
    <div class="col-lg-5">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center gap-2">
          <i class="mdi mdi-lightning-bolt-outline text-warning"></i>
          <h5 class="card-title mb-0">Ações rápidas</h5>
        </div>
        <div class="card-body d-flex flex-column gap-2">
          <a href="/relatorios" class="btn btn-outline-primary w-100 text-start">
            <i class="mdi mdi-file-chart-outline me-2"></i>Relatórios mensais
          </a>
          <a href="/profissionais" class="btn btn-outline-info w-100 text-start">
            <i class="mdi mdi-account-group-outline me-2"></i>Gerenciar equipe
          </a>
          <a href="/agendamentos" class="btn btn-outline-success w-100 text-start">
            <i class="mdi mdi-calendar-month-outline me-2"></i>Agenda geral
          </a>
          <a href="/configuracoes/especialidades" class="btn btn-outline-secondary w-100 text-start">
            <i class="mdi mdi-cog-outline me-2"></i>Configurações
          </a>
        </div>
      </div>
    </div>

  </div>

</div>
@endsection
