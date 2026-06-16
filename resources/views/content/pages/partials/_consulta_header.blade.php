{{-- _consulta_header.blade.php
     ST-12: Partial compartilhado por todas as views de detalhe de consulta.
     Renderiza o hero card do paciente + flash messages, idênticos para todas as especialidades.
     Incluir com: @include('content.pages.partials._consulta_header')
     Variáveis esperadas do escopo pai: $consulta, $paciente, $profissional,
       $atendimento, $iniciais, $atendAberto, $autorizado
--}}

{{-- ================================================================ --}}
{{-- HERO HEADER                                                       --}}
{{-- ================================================================ --}}
<div class="card mb-4">
  <div class="card-body py-4">
    <div class="d-flex flex-wrap align-items-center gap-4">

      {{-- Avatar com iniciais --}}
      <div class="flex-shrink-0">
        <div class="avatar avatar-xl">
          <span class="avatar-initial rounded-circle bg-label-primary" style="font-size:1.4rem; width:64px; height:64px; display:flex; align-items:center; justify-content:center;">
            {{ $iniciais }}
          </span>
        </div>
      </div>

      {{-- Dados do paciente --}}
      <div class="flex-grow-1">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
          {{-- UX-14: link aponta para /pacientes/{id} (perfil), não mais para /historico --}}
          @if(Auth::user()->nivelAcesso() <= 4)
            <a href="/pacientes/{{ $paciente->id }}"
               class="text-body fw-bold text-decoration-none" style="font-size:1.5rem;">{{ $paciente->nome ?? '-' }}</a>
          @else
            <h4 class="mb-0">{{ $paciente->nome ?? '-' }}</h4>
          @endif
          <span class="badge rounded-pill bg-label-primary">{{ $consulta->tipo }}</span>
          @if($atendAberto)
            <span class="badge rounded-pill bg-label-success">Atendimento aberto</span>
          @else
            <span class="badge rounded-pill bg-label-secondary">Atendimento encerrado</span>
          @endif
        </div>
        <div class="d-flex flex-wrap gap-3 text-muted small">
          @if($paciente->matricula)
            <span><i class="mdi mdi-card-account-details-outline me-1"></i>{{ $paciente->matricula }}</span>
          @endif
          @if($paciente->curso)
            <span><i class="mdi mdi-school-outline me-1"></i>{{ $paciente->curso }}</span>
          @endif
          <span><i class="mdi mdi-doctor me-1"></i>{{ $profissional->nome ?? '-' }}
            @if($profissional->especialidade) · {{ $profissional->especialidade }} @endif
          </span>
          <span><i class="mdi mdi-calendar-clock-outline me-1"></i>{{ $consulta->data_hora->format('d/m/Y \à\s H:i') }}</span>
          @if($atendimento)
            <a href="/atendimentos/{{ $atendimento->id }}" class="text-primary text-decoration-none">
              <i class="mdi mdi-folder-open-outline me-1"></i>Atendimento #{{ $atendimento->id }}
            </a>
          @endif
        </div>
      </div>

      {{-- Ações --}}
      <div class="flex-shrink-0 d-flex gap-2">
        {{-- ST-08: $autorizado vem do ConsultaController::show() --}}
        @if($autorizado)
        <a href="/editar-consulta/{{ $consulta->id }}" class="btn btn-outline-primary">
          <i class="mdi mdi-pencil-outline me-1"></i>Editar
        </a>
        @endif
        {{-- UX-P05 (B.4.6, v0.10.2): botão fixo para o atendimento, separado do "Voltar" --}}
        @if($atendimento)
        <a href="/atendimentos/{{ $atendimento->id }}" class="btn btn-outline-secondary">
          <i class="mdi mdi-folder-open-outline me-1"></i>Ir para Atendimento
        </a>
        @endif
        {{-- UX-P05 (B.6.4, v0.10.2): "Voltar" usa o histórico de navegação (fallback: /consultas) --}}
        <a href="{{ url()->previous('/consultas') }}" class="btn btn-default">
          <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
        </a>
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