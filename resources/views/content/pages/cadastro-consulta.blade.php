@php
$configData = Helper::appClasses();
// $voltarUrl é usado apenas no modo livre (sem atendimento)
$voltarUrl  = url()->previous('/consultas');

// Hero contextual: iniciais do paciente para o avatar
$iniciais = $atendimento
    ? collect(explode(' ', $atendimento->paciente->nome ?? 'P'))
        ->filter()->map(fn($p) => strtoupper($p[0]))->take(2)->implode('')
    : null;
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Nova Consulta')

{{-- Breadcrumb: Início > Consultas > Nova Consulta --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',        'url' => '/'],
      ['label' => 'Consultas',     'url' => '/consultas'],
      ['label' => 'Nova Consulta', 'url' => null],
    ]
  ])
@endpush

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================ --}}
  {{-- CABEÇALHO                                                         --}}
  {{-- Modo contextual (de um atendimento): hero igual ao detalhes_consulta --}}
  {{-- Modo livre: card header simples com título e botão Voltar         --}}
  {{-- ================================================================ --}}
  @if($atendimento)

  {{-- Hero header contextual: mesma estrutura do detalhes_consulta --}}
  @php
    $atendAberto = $atendimento->isAberto();
    $nomePaciente = $atendimento->paciente->nome ?? '-';
    $nomeProfissional = $profissionalLogado->nome ?? $atendimento->profissional->nome ?? '-';
    $especialidade = $profissionalLogado->especialidade
        ?? ($atendimento->profissional->especialidade ?? null);
  @endphp
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

        {{-- Dados do paciente e contexto do atendimento --}}
        <div class="flex-grow-1">
          <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <h4 class="mb-0">{{ $nomePaciente }}</h4>
            @if($atendAberto)
              <span class="badge rounded-pill bg-label-success">Atendimento aberto</span>
            @else
              <span class="badge rounded-pill bg-label-secondary">Atendimento encerrado</span>
            @endif
            <span class="badge rounded-pill bg-label-info">Nova Consulta</span>
          </div>
          <div class="d-flex flex-wrap gap-3 text-muted small">
            @if($atendimento->paciente->matricula ?? null)
              <span><i class="mdi mdi-card-account-details-outline me-1"></i>{{ $atendimento->paciente->matricula }}</span>
            @endif
            @if($atendimento->paciente->curso ?? null)
              <span><i class="mdi mdi-school-outline me-1"></i>{{ $atendimento->paciente->curso }}</span>
            @endif
            <span>
              <i class="mdi mdi-doctor me-1"></i>{{ $nomeProfissional }}
              @if($especialidade) · {{ $especialidade }} @endif
            </span>
            <a href="/atendimentos/{{ $atendimento->id }}" class="text-primary text-decoration-none">
              <i class="mdi mdi-folder-open-outline me-1"></i>Atendimento #{{ $atendimento->id }}
            </a>
          </div>
        </div>

        {{-- Botão Voltar --}}
        <div class="flex-shrink-0">
          <a href="/atendimentos/{{ $atendimento->id }}" class="btn btn-default">
            <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
          </a>
        </div>

      </div>
    </div>
  </div>

  @else

  {{-- Hero modo livre: exibe dados do profissional responsável --}}
  @php
    $iniciaisProf = $profissionalLogado
        ? collect(explode(' ', $profissionalLogado->nome ?? 'P'))
            ->filter()->map(fn($p) => strtoupper($p[0]))->take(2)->implode('')
        : null;
  @endphp
  <div class="card mb-4">
    <div class="card-body py-4">
      <div class="d-flex flex-wrap align-items-center gap-4">

        {{-- Avatar: iniciais do profissional ou ícone genérico para admin --}}
        <div class="flex-shrink-0">
          <div class="avatar avatar-xl">
            @if($iniciaisProf)
              <span class="avatar-initial rounded-circle bg-label-success"
                style="font-size:1.4rem; width:64px; height:64px; display:flex; align-items:center; justify-content:center;">
                {{ $iniciaisProf }}
              </span>
            @else
              <span class="avatar-initial rounded-circle bg-label-secondary"
                style="font-size:1.8rem; width:64px; height:64px; display:flex; align-items:center; justify-content:center;">
                <i class="mdi mdi-stethoscope"></i>
              </span>
            @endif
          </div>
        </div>

        {{-- Dados do profissional e contexto --}}
        <div class="flex-grow-1">
          <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            @if($profissionalLogado)
              <h4 class="mb-0">{{ $profissionalLogado->nome }}</h4>
              @if($profissionalLogado->especialidade)
                <span class="badge rounded-pill bg-label-primary">{{ $profissionalLogado->especialidade }}</span>
              @endif
            @else
              <h4 class="mb-0">Nova Consulta</h4>
            @endif
            <span class="badge rounded-pill bg-label-info">Modo Livre</span>
          </div>
          <div class="d-flex flex-wrap gap-3 text-muted small">
            @if($profissionalLogado && $profissionalLogado->registro_profissional)
              <span><i class="mdi mdi-card-account-details-outline me-1"></i>{{ $profissionalLogado->registro_profissional }}</span>
            @endif
            <span><i class="mdi mdi-account-search-outline me-1"></i>Selecione o paciente abaixo</span>
          </div>
        </div>

        {{-- Botão Voltar --}}
        <div class="flex-shrink-0">
          <a href="{{ $voltarUrl }}" class="btn btn-default">
            <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
          </a>
        </div>

      </div>
    </div>
  </div>

  @endif

  <form class="browser-default-validation" action="/cadastrar-consulta" method="POST">
    @csrf

    {{-- Campos hidden para modo contextual: IDs de paciente/profissional/atendimento --}}
    @if($atendimento)
      <input type="hidden" name="atendimento_id"  value="{{ $atendimento->id }}">
      <input type="hidden" name="paciente_id"     value="{{ $atendimento->paciente_id }}">
      <input type="hidden" name="profissional_id" value="{{ $profissionalLogado->id ?? $atendimento->profissional_id }}">
    @endif

    @php
      // Pré-seleciona tipo pela especialidade do profissional (se disponível)
      $especialidade = $especialidade ?? $profissionalLogado->especialidade
          ?? ($atendimento->profissional->especialidade ?? null);

      // Modo contextual: data começa com o momento atual
      // Modo livre: usa old() em caso de resubmissão com erro de validação
      $dataDefault = $atendimento
          ? now()->format('Y-m-d\TH:i')
          : old('data_hora');
    @endphp

    <div class="row g-4">

      {{-- ============================================================
           Coluna esquerda (col-7): Identificação e campos SOAP
           ============================================================ --}}
      <div class="col-md-7">

        {{-- Card de identificação --}}
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0"><i class="mdi mdi-account-outline me-2"></i>Identificação</h5>
          </div>
          <div class="card-body">

            {{-- Modo livre: selects completos de paciente e profissional --}}
            @if(!$atendimento)

              {{-- Select de paciente: pré-selecionado quando vem de ?paciente_id=X --}}
              <div class="form-floating form-floating-outline mb-4">
                <select name="paciente_id" id="paciente_id"
                  class="form-select @error('paciente_id') is-invalid @enderror" required>
                  <option disabled {{ (old('paciente_id') || $pacientePreSelecionado) ? '' : 'selected' }} value="">Selecione o paciente</option>
                  @foreach($pacientes as $paciente)
                  @php $selecionado = old('paciente_id', $pacientePreSelecionado?->id) == $paciente->id; @endphp
                  <option value="{{ $paciente->id }}" {{ $selecionado ? 'selected' : '' }}>
                    {{ $paciente->nome }} — {{ $paciente->matricula }}
                  </option>
                  @endforeach
                </select>
                <label for="paciente_id">Paciente *</label>
                @error('paciente_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              {{-- Profissional: travado se logado tem perfil de profissional, select caso contrário --}}
              @if($profissionalLogado)
                <input type="hidden" name="profissional_id" value="{{ $profissionalLogado->id }}">
                <div class="mb-4">
                  <label class="form-label text-muted small">Profissional Responsável</label>
                  <div class="form-control bg-light d-flex align-items-center gap-2">
                    <i class="mdi mdi-doctor text-primary"></i>
                    <div>
                      <strong>{{ $profissionalLogado->nome }}</strong>
                      @if($profissionalLogado->especialidade)
                        <span class="text-muted ms-2">· {{ $profissionalLogado->especialidade }}</span>
                      @endif
                    </div>
                  </div>
                </div>
              @else
                <div class="form-floating form-floating-outline mb-4">
                  <select name="profissional_id" id="profissional_id"
                    class="form-select @error('profissional_id') is-invalid @enderror" required>
                    <option disabled {{ old('profissional_id') ? '' : 'selected' }} value="">Selecione o profissional</option>
                    @foreach($profissionais as $profissional)
                    <option value="{{ $profissional->id }}" {{ old('profissional_id') == $profissional->id ? 'selected' : '' }}>
                      {{ $profissional->nome }} — {{ $profissional->especialidade }}
                    </option>
                    @endforeach
                  </select>
                  <label for="profissional_id">Profissional Responsável *</label>
                  @error('profissional_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              @endif

            @endif
            {{-- Fim do bloco modo livre --}}

            {{-- Data/hora e tipo: aparecem nos dois modos --}}
            <div class="row g-3">
              <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                  <input name="data_hora" type="datetime-local"
                    class="form-control @error('data_hora') is-invalid @enderror"
                    id="data_hora" value="{{ $dataDefault }}" required />
                  <label for="data_hora">Data e Hora *</label>
                  @error('data_hora')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
              <div class="col-md-6">
                {{-- Tipo pré-selecionado pela especialidade do profissional, se disponível --}}
                <div class="form-floating form-floating-outline">
                  <select name="tipo" id="tipo"
                    class="form-select @error('tipo') is-invalid @enderror" required>
                    <option disabled {{ (old('tipo') || $especialidade) ? '' : 'selected' }} value="">Selecione</option>
                    @foreach($tiposConsulta as $t)
                    <option value="{{ $t->nome }}"
                      {{ (old('tipo') ?? $especialidade) == $t->nome ? 'selected' : '' }}>
                      {{ $t->nome }}
                    </option>
                    @endforeach
                  </select>
                  <label for="tipo">Tipo *</label>
                  @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
            </div>

          </div>
        </div>

        {{-- ============================================================
             Card SOAP: Registro Clínico
             Cada seção tem borda colorida à esquerda para identificação visual.
             S = Subjetivo (queixa do paciente)
             A = Anamnese (história clínica)
             D = Diagnóstico (hipótese ou confirmado)
             P = Plano/Conduta (tratamento e orientações)
             ============================================================ --}}
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0"><i class="mdi mdi-clipboard-text-outline me-2"></i>Registro Clínico (SOAP)</h5>
          </div>
          <div class="card-body">

            {{-- S: Queixa principal — único campo obrigatório do SOAP --}}
            <div class="mb-4 ps-3" style="border-left: 4px solid var(--bs-primary)">
              <label for="queixa" class="form-label fw-semibold">S — Queixa Principal *</label>
              <textarea name="queixa" id="queixa" rows="3"
                class="form-control @error('queixa') is-invalid @enderror"
                placeholder="Motivo da consulta relatado pelo paciente"
                required>{{ old('queixa') }}</textarea>
              @error('queixa')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- A: Anamnese — histórico do paciente, opcional --}}
            <div class="mb-4 ps-3" style="border-left: 4px solid var(--bs-info)">
              <label for="anamnese" class="form-label fw-semibold">A — Anamnese <span class="text-muted fw-normal">(opcional)</span></label>
              <textarea name="anamnese" id="anamnese" rows="3"
                class="form-control @error('anamnese') is-invalid @enderror"
                placeholder="História clínica detalhada">{{ old('anamnese') }}</textarea>
              @error('anamnese')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- D: Diagnóstico — hipótese ou diagnóstico confirmado, opcional --}}
            <div class="mb-4 ps-3" style="border-left: 4px solid var(--bs-warning)">
              <label for="diagnostico" class="form-label fw-semibold">D — Diagnóstico <span class="text-muted fw-normal">(opcional)</span></label>
              <textarea name="diagnostico" id="diagnostico" rows="3"
                class="form-control @error('diagnostico') is-invalid @enderror"
                placeholder="Hipótese diagnóstica ou diagnóstico confirmado">{{ old('diagnostico') }}</textarea>
              @error('diagnostico')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- P: Conduta — plano de tratamento e orientações, opcional --}}
            <div class="ps-3" style="border-left: 4px solid var(--bs-success)">
              <label for="conduta" class="form-label fw-semibold">P — Conduta <span class="text-muted fw-normal">(opcional)</span></label>
              <textarea name="conduta" id="conduta" rows="3"
                class="form-control @error('conduta') is-invalid @enderror"
                placeholder="Condutas, orientações e encaminhamentos">{{ old('conduta') }}</textarea>
              @error('conduta')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

          </div>
        </div>

        {{-- Botões de ação — UX-P05 (B.4.8, v0.10.2): "Realizar Agora" virou "Salvar Consulta"
             e o botão "Agendar para depois" (stub do ST-09) foi removido. Mantemos intent=realize. --}}
        <input type="hidden" name="intent" id="form-intent" value="realize">
        <div class="d-flex flex-wrap gap-2 mt-4 mb-2">
          <button type="submit" class="btn btn-primary">
            <i class="mdi mdi-check-circle-outline me-1"></i>Salvar Consulta
          </button>
          <a href="{{ $atendimento ? '/atendimentos/' . $atendimento->id : $voltarUrl }}"
            class="btn btn-outline-secondary ms-auto">Cancelar</a>
        </div>

      </div>

      {{-- ============================================================
           Coluna direita (col-5): Exames e Prescrições inline
           Os itens são adicionados dinamicamente via JavaScript (abaixo).
           Cada row criado gera campos com nome no formato exames[N][campo],
           que o PHP/Laravel reconhece como array ao fazer o parse do POST.
           ============================================================ --}}
      <div class="col-md-5 d-flex flex-column gap-4">

        {{-- Card de exames: container vazio + botão "Adicionar" --}}
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="mdi mdi-test-tube-outline me-2"></i>Exames</h5>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-exame">
              <i class="mdi mdi-plus me-1"></i>Adicionar
            </button>
          </div>
          <div class="card-body p-3">
            {{-- Container onde o JS vai inserir as linhas de exame --}}
            <div id="exames-container">
              <p class="text-muted small text-center py-2 mb-0" id="exames-empty">
                <i class="mdi mdi-test-tube mdi-18px me-1 opacity-50"></i>Nenhum exame adicionado
              </p>
            </div>
          </div>
        </div>

        {{-- Card de prescrições: mesma lógica dos exames --}}
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="mdi mdi-pill me-2"></i>Prescrições</h5>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-prescricao">
              <i class="mdi mdi-plus me-1"></i>Adicionar
            </button>
          </div>
          <div class="card-body p-3">
            {{-- Container onde o JS vai inserir as linhas de prescrição --}}
            <div id="prescricoes-container">
              <p class="text-muted small text-center py-2 mb-0" id="prescricoes-empty">
                <i class="mdi mdi-pill mdi-18px me-1 opacity-50"></i>Nenhuma prescrição adicionada
              </p>
            </div>
          </div>
        </div>

      </div>
    </div>

  </form>
</div>

@endsection

{{-- ============================================================
     Script de página: gerencia as linhas dinâmicas de exames e prescrições.
     Injetado via @yield('page-script') no layout após jQuery e Bootstrap,
     portanto pode usar document.getElementById sem problemas de ordem de carregamento.

     Funcionamento:
     - exameIdx e prescricaoIdx são contadores que nunca retrocedem,
       mesmo que o usuário remova uma linha. Isso garante que os índices
       do array (exames[0], exames[1]...) nunca se repitam no POST,
       evitando colisão de dados ao submeter o formulário.
     - toggleEmpty() mostra/esconde a mensagem "nenhum item" conforme
       existem ou não linhas com a classe .dynamic-row no container.
     - removeRow() é exposto no window para ser acessível via onclick inline.
     ============================================================ --}}
@section('page-script')
<script>
(function () {
  var exameIdx = 0;       // contador global de exames — nunca é decrementado
  var prescricaoIdx = 0;  // contador global de prescrições — nunca é decrementado

  // Mostra ou esconde a mensagem de "nenhum item" conforme o container estiver vazio
  function toggleEmpty(containerId, emptyId) {
    var container = document.getElementById(containerId);
    var empty = document.getElementById(emptyId);
    if (!empty) return;
    empty.style.display = container.querySelectorAll('.dynamic-row').length > 0 ? 'none' : '';
  }

  // Cria e insere uma nova linha de exame no container
  function addExame() {
    var idx = exameIdx++;
    var container = document.getElementById('exames-container');
    var row = document.createElement('div');
    row.className = 'dynamic-row border rounded p-3 mb-2';
    row.innerHTML =
      '<div class="d-flex justify-content-between align-items-center mb-2">' +
        '<small class="fw-semibold text-muted">Exame #' + (idx + 1) + '</small>' +
        '<button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeRow(this)">' +
          '<i class="mdi mdi-close"></i>' +
        '</button>' +
      '</div>' +
      '<div class="mb-2">' +
        // Nome do campo segue o padrão de array: exames[N][tipo]
        '<input type="text" name="exames[' + idx + '][tipo]"' +
          ' class="form-control form-control-sm"' +
          ' placeholder="Tipo de exame (ex: Hemograma, RX Tórax)">' +
      '</div>' +
      '<div class="row g-2">' +
        '<div class="col-6">' +
          '<input type="date" name="exames[' + idx + '][data_solicitacao]"' +
            ' class="form-control form-control-sm" title="Data de solicitação">' +
        '</div>' +
        '<div class="col-6">' +
          '<input type="text" name="exames[' + idx + '][observacao]"' +
            ' class="form-control form-control-sm" placeholder="Observação">' +
        '</div>' +
      '</div>';
    container.appendChild(row);
    toggleEmpty('exames-container', 'exames-empty');
  }

  // Cria e insere uma nova linha de prescrição no container
  function addPrescricao() {
    var idx = prescricaoIdx++;
    var container = document.getElementById('prescricoes-container');
    var row = document.createElement('div');
    row.className = 'dynamic-row border rounded p-3 mb-2';
    row.innerHTML =
      '<div class="d-flex justify-content-between align-items-center mb-2">' +
        '<small class="fw-semibold text-muted">Prescrição #' + (idx + 1) + '</small>' +
        '<button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeRow(this)">' +
          '<i class="mdi mdi-close"></i>' +
        '</button>' +
      '</div>' +
      '<div class="mb-2">' +
        '<input type="text" name="prescricoes[' + idx + '][nome_medicamento]"' +
          ' class="form-control form-control-sm" placeholder="Nome do medicamento">' +
      '</div>' +
      '<div class="row g-2">' +
        '<div class="col-6">' +
          '<input type="text" name="prescricoes[' + idx + '][dosagem]"' +
            ' class="form-control form-control-sm" placeholder="Dosagem">' +
        '</div>' +
        '<div class="col-6">' +
          '<input type="text" name="prescricoes[' + idx + '][frequencia]"' +
            ' class="form-control form-control-sm" placeholder="Frequência">' +
        '</div>' +
        '<div class="col-6">' +
          '<input type="text" name="prescricoes[' + idx + '][duracao]"' +
            ' class="form-control form-control-sm" placeholder="Duração">' +
        '</div>' +
        '<div class="col-6">' +
          '<input type="text" name="prescricoes[' + idx + '][observacao]"' +
            ' class="form-control form-control-sm" placeholder="Observação">' +
        '</div>' +
      '</div>';
    container.appendChild(row);
    toggleEmpty('prescricoes-container', 'prescricoes-empty');
  }

  // Remove a linha clicada e atualiza o estado de "vazio" do container
  window.removeRow = function (btn) {
    var row = btn.closest('.dynamic-row');
    var containerId = row.parentElement.id;
    var emptyId = containerId === 'exames-container' ? 'exames-empty' : 'prescricoes-empty';
    row.remove();
    toggleEmpty(containerId, emptyId);
  };

  // Vincula os botões "Adicionar" às funções correspondentes
  document.getElementById('btn-add-exame').addEventListener('click', addExame);
  document.getElementById('btn-add-prescricao').addEventListener('click', addPrescricao);
})();
</script>
@endsection
