@php
$configData  = Helper::appClasses();
// Calcula iniciais do paciente para o avatar do hero header
$atendimento = $consulta->atendimento;
$atendAberto = $atendimento?->isAberto() ?? true;
$iniciais    = collect(explode(' ', $consulta->paciente->nome ?? 'P'))
    ->filter()->map(fn($p) => strtoupper($p[0]))->take(2)->implode('');
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Editar Consulta')

{{-- Breadcrumb: Início > Consultas > Editar --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',    'url' => '/'],
      ['label' => 'Consultas', 'url' => '/consultas'],
      ['label' => 'Editar',    'url' => null],
    ]
  ])
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Flash messages --}}
  @if(session('error'))
  <div class="alert alert-danger alert-dismissible mb-4" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
  </div>
  @endif

  {{-- ================================================================ --}}
  {{-- HERO HEADER — mesmo padrão do detalhes_consulta                  --}}
  {{-- Mostra o contexto completo: quem é o paciente, qual atendimento  --}}
  {{-- e o status atual. Badge "Editando" sinaliza o modo corrente.     --}}
  {{-- ================================================================ --}}
  <div class="card mb-4">
    <div class="card-body py-4">
      <div class="d-flex flex-wrap align-items-center gap-4">

        {{-- Avatar com iniciais do paciente --}}
        <div class="flex-shrink-0">
          <div class="avatar avatar-xl">
            <span class="avatar-initial rounded-circle bg-label-primary"
              style="font-size:1.4rem; width:64px; height:64px; display:flex; align-items:center; justify-content:center;">
              {{ $iniciais }}
            </span>
          </div>
        </div>

        {{-- Dados do paciente e contexto --}}
        <div class="flex-grow-1">
          <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <h4 class="mb-0">{{ $consulta->paciente->nome ?? '-' }}</h4>
            <span class="badge rounded-pill bg-label-primary">{{ $consulta->tipo }}</span>
            @if($atendAberto)
              <span class="badge rounded-pill bg-label-success">Atendimento aberto</span>
            @else
              <span class="badge rounded-pill bg-label-secondary">Atendimento encerrado</span>
            @endif
            <span class="badge rounded-pill bg-label-warning">Editando</span>
          </div>
          <div class="d-flex flex-wrap gap-3 text-muted small">
            @if($consulta->paciente->matricula)
              <span><i class="mdi mdi-card-account-details-outline me-1"></i>{{ $consulta->paciente->matricula }}</span>
            @endif
            @if($consulta->paciente->curso)
              <span><i class="mdi mdi-school-outline me-1"></i>{{ $consulta->paciente->curso }}</span>
            @endif
            <span>
              <i class="mdi mdi-doctor me-1"></i>{{ $consulta->profissional->nome ?? '-' }}
              @if($consulta->profissional->especialidade) · {{ $consulta->profissional->especialidade }} @endif
            </span>
            <span><i class="mdi mdi-calendar-clock-outline me-1"></i>{{ $consulta->data_hora->format('d/m/Y \à\s H:i') }}</span>
            @if($atendimento)
              <a href="/atendimentos/{{ $atendimento->id }}" class="text-primary text-decoration-none">
                <i class="mdi mdi-folder-open-outline me-1"></i>Atendimento #{{ $atendimento->id }}
              </a>
            @endif
          </div>
        </div>

        {{-- Botão Voltar --}}
        <div class="flex-shrink-0">
          <a href="/consultas/{{ $consulta->id }}" class="btn btn-default">
            <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
          </a>
        </div>

      </div>
    </div>
  </div>

  <form action="/atualizar-consulta/{{ $consulta->id }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">

      {{-- ================================================================ --}}
      {{-- COLUNA ESQUERDA: campos editáveis (data, tipo + SOAP)            --}}
      {{-- ================================================================ --}}
      <div class="col-md-7">

        {{-- Card: Data e Tipo --}}
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="mdi mdi-calendar-clock-outline me-2"></i>Data e Tipo
            </h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              {{-- Data e Hora --}}
              <div class="col-md-7">
                <div class="form-floating form-floating-outline">
                  <input name="data_hora" type="datetime-local" id="data_hora"
                    class="form-control @error('data_hora') is-invalid @enderror"
                    value="{{ old('data_hora', $consulta->data_hora->format('Y-m-d\TH:i')) }}"
                    required />
                  <label for="data_hora">Data e Hora</label>
                  @error('data_hora')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
              {{-- Tipo --}}
              <div class="col-md-5">
                <div class="form-floating form-floating-outline">
                  <select name="tipo" id="tipo"
                    class="form-select @error('tipo') is-invalid @enderror" required>
                    <option disabled value="">Tipo</option>
                    @foreach(['Clinico Geral' => 'Clínico Geral', 'Odontologia' => 'Odontologia', 'Psicologia' => 'Psicologia', 'Nutricionista' => 'Nutricionista', 'Fisioterapeuta' => 'Fisioterapeuta'] as $value => $label)
                    <option value="{{ $value }}"
                      {{ old('tipo', $consulta->tipo) == $value ? 'selected' : '' }}>
                      {{ $label }}
                    </option>
                    @endforeach
                  </select>
                  <label for="tipo">Tipo de Consulta</label>
                  @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Card: Registro Clínico (SOAP) --}}
        {{-- Bordas coloridas seguem o mesmo padrão visual do detalhes_consulta --}}
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="mdi mdi-stethoscope me-2"></i>Registro Clínico
            </h5>
          </div>
          <div class="card-body p-0">

            {{-- Queixa — borda azul (primary) --}}
            <div class="p-4 border-start border-4 border-primary">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="mdi mdi-chat-question-outline text-primary"></i>
                <span class="fw-semibold text-uppercase small text-muted">Queixa Principal</span>
              </div>
              <div class="form-floating form-floating-outline">
                <textarea name="queixa" id="queixa" style="height:100px"
                  class="form-control @error('queixa') is-invalid @enderror"
                  required>{{ old('queixa', $consulta->queixa) }}</textarea>
                <label for="queixa">Queixa principal do paciente</label>
                @error('queixa')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>

            <hr class="my-0">

            {{-- Anamnese — borda azul-claro (info) --}}
            <div class="p-4 border-start border-4 border-info">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="mdi mdi-clipboard-text-outline text-info"></i>
                <span class="fw-semibold text-uppercase small text-muted">Anamnese <span class="text-muted fw-normal">(opcional)</span></span>
              </div>
              <div class="form-floating form-floating-outline">
                <textarea name="anamnese" id="anamnese" style="height:100px"
                  class="form-control @error('anamnese') is-invalid @enderror">{{ old('anamnese', $consulta->anamnese) }}</textarea>
                <label for="anamnese">História clínica do paciente</label>
                @error('anamnese')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>

            <hr class="my-0">

            {{-- Diagnóstico — borda amarela (warning) --}}
            <div class="p-4 border-start border-4 border-warning">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="mdi mdi-microscope text-warning"></i>
                <span class="fw-semibold text-uppercase small text-muted">Diagnóstico <span class="text-muted fw-normal">(opcional)</span></span>
              </div>
              <div class="form-floating form-floating-outline">
                <textarea name="diagnostico" id="diagnostico" style="height:100px"
                  class="form-control @error('diagnostico') is-invalid @enderror">{{ old('diagnostico', $consulta->diagnostico) }}</textarea>
                <label for="diagnostico">Hipótese ou diagnóstico confirmado</label>
                @error('diagnostico')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>

            <hr class="my-0">

            {{-- Conduta — borda verde (success) --}}
            <div class="p-4 border-start border-4 border-success">
              <div class="d-flex align-items-center gap-2 mb-2">
                <i class="mdi mdi-list-box-outline text-success"></i>
                <span class="fw-semibold text-uppercase small text-muted">Conduta <span class="text-muted fw-normal">(opcional)</span></span>
              </div>
              <div class="form-floating form-floating-outline">
                <textarea name="conduta" id="conduta" style="height:100px"
                  class="form-control @error('conduta') is-invalid @enderror">{{ old('conduta', $consulta->conduta) }}</textarea>
                <label for="conduta">Orientações e conduta terapêutica</label>
                @error('conduta')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>

          </div>

          {{-- Botões de ação no rodapé do card SOAP --}}
          <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="mdi mdi-content-save-outline me-1"></i>Salvar Alterações
            </button>
            <a href="/consultas/{{ $consulta->id }}" class="btn btn-outline-secondary">Cancelar</a>
          </div>
        </div>

      </div>

      {{-- ================================================================ --}}
      {{-- COLUNA DIREITA: exames e prescrições (read-only)                --}}
      {{-- Adicionar novos é feito em detalhes_consulta                    --}}
      {{-- ================================================================ --}}
      <div class="col-md-5 d-flex flex-column gap-4">

        {{-- Exames vinculados --}}
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h6 class="card-title mb-0">
              <i class="mdi mdi-test-tube me-2"></i>Exames
              <span class="badge bg-label-secondary ms-1">{{ $consulta->exames->count() }}</span>
            </h6>
            @if($atendAberto && Auth::user()->nivelAcesso() <= 3)
            <a href="/cadastro-exame?consulta_id={{ $consulta->id }}" class="btn btn-sm btn-primary">
              <i class="mdi mdi-plus me-1"></i>Novo
            </a>
            @endif
          </div>
          <div class="card-body p-0">
            @forelse($consulta->exames as $exame)
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
              <div>
                <p class="fw-semibold mb-0 small">{{ $exame->tipo }}</p>
                <small class="text-muted">
                  {{ $exame->data_solicitacao ? $exame->data_solicitacao->format('d/m/Y') : 'Sem data' }}
                </small>
              </div>
              @if(Auth::id() == $exame->criado_por_id || Auth::user()->nivelAcesso() <= 1)
              <a href="/editar-exame/{{ $exame->id }}" class="btn btn-xs btn-outline-secondary">
                <i class="mdi mdi-pencil-outline me-1"></i>Editar
              </a>
              @endif
            </div>
            @empty
            <div class="p-4 text-center text-muted">
              <i class="mdi mdi-test-tube-empty mdi-36px d-block mb-2 opacity-50"></i>
              <p class="mb-0 small">Nenhum exame solicitado nesta consulta.</p>
              @if($atendAberto && Auth::user()->nivelAcesso() <= 3)
              <a href="/cadastro-exame?consulta_id={{ $consulta->id }}" class="btn btn-sm btn-outline-primary mt-2">
                <i class="mdi mdi-plus me-1"></i>Solicitar Exame
              </a>
              @endif
            </div>
            @endforelse
          </div>
        </div>

        {{-- Prescrições vinculadas --}}
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h6 class="card-title mb-0">
              <i class="mdi mdi-pill me-2"></i>Prescrições
              <span class="badge bg-label-secondary ms-1">{{ $consulta->prescricoes->count() }}</span>
            </h6>
            @if($atendAberto && Auth::user()->nivelAcesso() <= 3)
            <a href="/cadastro-prescricao?consulta_id={{ $consulta->id }}" class="btn btn-sm btn-primary">
              <i class="mdi mdi-plus me-1"></i>Nova
            </a>
            @endif
          </div>
          <div class="card-body p-0">
            @forelse($consulta->prescricoes as $prescricao)
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
              <div>
                <p class="fw-semibold mb-0 small">{{ $prescricao->nome_medicamento }}</p>
                <small class="text-muted">{{ $prescricao->dosagem }}</small>
              </div>
              @if(Auth::id() == $prescricao->criado_por_id || Auth::user()->nivelAcesso() <= 1)
              <a href="/editar-prescricao/{{ $prescricao->id }}" class="btn btn-xs btn-outline-secondary">
                <i class="mdi mdi-pencil-outline me-1"></i>Editar
              </a>
              @endif
            </div>
            @empty
            <div class="p-4 text-center text-muted">
              <i class="mdi mdi-pill-off mdi-36px d-block mb-2 opacity-50"></i>
              <p class="mb-0 small">Nenhuma prescrição emitida nesta consulta.</p>
              @if($atendAberto && Auth::user()->nivelAcesso() <= 3)
              <a href="/cadastro-prescricao?consulta_id={{ $consulta->id }}" class="btn btn-sm btn-outline-primary mt-2">
                <i class="mdi mdi-plus me-1"></i>Emitir Prescrição
              </a>
              @endif
            </div>
            @endforelse
          </div>
        </div>

      </div>

    </div>
  </form>

</div>

@endsection
