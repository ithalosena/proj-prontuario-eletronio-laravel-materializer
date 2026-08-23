{{-- ================================================================
     Form de consulta embutido na tela do atendimento (E3b/E3d)

     Layout EMPILHADO (DEC-5, telas HD): Identificação → SOAP + Anotações
     → Exames | Prescrições lado a lado embaixo → botões alinhados à esquerda.

     Variáveis esperadas:
     - $atendimento        (obrigatório — container da consulta)
     - $profissionalLogado (usuário logado, pode ser null p/ coordenador sem perfil)
     - $tiposConsulta      (lista de tipos ativos)
     - $especialidade      (pré-seleção do tipo, opcional)
     - $cancelavel         (opcional, default true — mostra o botão que recolhe o form)
     ================================================================ --}}

@php
  $cancelavel = $cancelavel ?? true;
  // old() tem prioridade: se a validação falhou, o que o profissional digitou volta preenchido
  $dataDefault = old('data_hora', now()->format('Y-m-d\TH:i'));
  $tipoDefault = old('tipo', $especialidade ?? null);
@endphp

<form class="browser-default-validation" action="/cadastrar-consulta" method="POST">
  @csrf

  {{-- Hidden: IDs do contexto --}}
  @if($atendimento)
    <input type="hidden" name="atendimento_id"  value="{{ $atendimento->id }}">
    <input type="hidden" name="paciente_id"     value="{{ $atendimento->paciente_id }}">
    <input type="hidden" name="profissional_id" value="{{ $profissionalLogado->id ?? $atendimento->profissional_id }}">
  @endif

  {{-- ============================================================
       Identificação (full width)
       ============================================================ --}}
  <div class="card mb-4">
    <div class="card-header py-3">
      <h5 class="card-title mb-0"><i class="mdi mdi-account-outline me-2 text-primary"></i>Identificação</h5>
    </div>
    <div class="card-body">
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
          <div class="form-floating form-floating-outline">
            <select name="tipo" id="tipo"
              class="form-select @error('tipo') is-invalid @enderror" required>
              <option disabled {{ $tipoDefault ? '' : 'selected' }} value="">Selecione</option>
              @foreach($tiposConsulta as $t)
              <option value="{{ $t->nome }}"
                {{ $tipoDefault == $t->nome ? 'selected' : '' }}>
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
       Registro Clínico: SOAP + Anotações livres (full width)
       ============================================================ --}}
  <div class="card mb-4">
    <div class="card-header py-3">
      <h5 class="card-title mb-0"><i class="mdi mdi-clipboard-text-outline me-2 text-primary"></i>Registro Clínico (SOAP)</h5>
    </div>
    <div class="card-body">

      <div class="mb-4 ps-3" style="border-left: 4px solid var(--bs-primary)">
        <label for="queixa" class="form-label fw-semibold">S — Queixa Principal *</label>
        <textarea name="queixa" id="queixa" rows="3"
          class="form-control @error('queixa') is-invalid @enderror"
          placeholder="Motivo da consulta" required>{{ old('queixa') }}</textarea>
        @error('queixa')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="mb-4 ps-3" style="border-left: 4px solid var(--bs-info)">
        <label for="anamnese" class="form-label fw-semibold">A — Anamnese <span class="text-muted fw-normal">(opcional)</span></label>
        <textarea name="anamnese" id="anamnese" rows="3"
          class="form-control @error('anamnese') is-invalid @enderror"
          placeholder="História clínica">{{ old('anamnese') }}</textarea>
        @error('anamnese')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="mb-4 ps-3" style="border-left: 4px solid var(--bs-warning)">
        <label for="diagnostico" class="form-label fw-semibold">D — Diagnóstico <span class="text-muted fw-normal">(opcional)</span></label>
        <textarea name="diagnostico" id="diagnostico" rows="3"
          class="form-control @error('diagnostico') is-invalid @enderror"
          placeholder="Hipótese diagnóstica">{{ old('diagnostico') }}</textarea>
        @error('diagnostico')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="mb-4 ps-3" style="border-left: 4px solid var(--bs-success)">
        <label for="conduta" class="form-label fw-semibold">P — Conduta <span class="text-muted fw-normal">(opcional)</span></label>
        <textarea name="conduta" id="conduta" rows="3"
          class="form-control @error('conduta') is-invalid @enderror"
          placeholder="Condutas e orientações">{{ old('conduta') }}</textarea>
        @error('conduta')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      {{-- E3d: registro LIVRE — perfis não-médicos (psicologia, serviço social...)
           usam este espaço como evolução/anotações sem o rigor do SOAP --}}
      <div class="ps-3" style="border-left: 4px solid var(--bs-secondary)">
        <label for="anotacoes" class="form-label fw-semibold">Anotações <span class="text-muted fw-normal">(opcional — registro livre, ex.: psicologia)</span></label>
        <textarea name="anotacoes" id="anotacoes" rows="3"
          class="form-control @error('anotacoes') is-invalid @enderror"
          placeholder="Evolução, observações e anotações complementares">{{ old('anotacoes') }}</textarea>
        @error('anotacoes')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

    </div>
  </div>

  {{-- ============================================================
       Exames | Prescrições — lado a lado, ABAIXO do registro (DEC-5)
       ============================================================ --}}
  <div class="row g-4 mb-1">

    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0"><i class="mdi mdi-test-tube-outline me-2 text-info"></i>Exames</h5>
          <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-exame">
            <i class="mdi mdi-plus me-1"></i>Adicionar
          </button>
        </div>
        <div class="card-body p-3">
          <div id="exames-container">
            <p class="text-muted small text-center py-2 mb-0" id="exames-empty">
              <i class="mdi mdi-test-tube mdi-18px me-1 opacity-50"></i>Nenhum exame
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0"><i class="mdi mdi-pill me-2 text-warning"></i>Prescrições</h5>
          <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-prescricao">
            <i class="mdi mdi-plus me-1"></i>Adicionar
          </button>
        </div>
        <div class="card-body p-3">
          <div id="prescricoes-container">
            <p class="text-muted small text-center py-2 mb-0" id="prescricoes-empty">
              <i class="mdi mdi-pill mdi-18px me-1 opacity-50"></i>Nenhuma prescrição
            </p>
          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- Botões — juntos, alinhados à esquerda (DEC-5: sem ms-auto quebrando o alinhamento) --}}
  <input type="hidden" name="intent" id="form-intent" value="realize">
  <div class="d-flex flex-wrap gap-2 mt-4">
    <button type="submit" class="btn btn-primary">
      <i class="mdi mdi-check-circle-outline me-1"></i>Salvar Consulta
    </button>
    @if($cancelavel)
    {{-- Cancelar apenas recolhe o formulário — nada é criado nem perdido de navegação --}}
    <button type="button" class="btn btn-outline-secondary"
            data-bs-toggle="collapse" data-bs-target="#nova-consulta" aria-controls="nova-consulta">
      Cancelar
    </button>
    @endif
  </div>

</form>

@section('page-script')
<script>
(function () {
  var exameIdx = 0;
  var prescricaoIdx = 0;

  function toggleEmpty(containerId, emptyId) {
    var container = document.getElementById(containerId);
    var empty = document.getElementById(emptyId);
    if (!empty) return;
    empty.style.display = container.querySelectorAll('.dynamic-row').length > 0 ? 'none' : '';
  }

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
        '<input type="text" name="exames[' + idx + '][tipo]"' +
          ' class="form-control form-control-sm" placeholder="Tipo de exame">' +
      '</div>' +
      '<div class="mb-0">' +
        '<input type="text" name="exames[' + idx + '][observacao]"' +
          ' class="form-control form-control-sm" placeholder="Observação">' +
      '</div>';
    container.appendChild(row);
    toggleEmpty('exames-container', 'exames-empty');
  }

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

  window.removeRow = function (btn) {
    var row = btn.closest('.dynamic-row');
    var containerId = row.parentElement.id;
    var emptyId = containerId === 'exames-container' ? 'exames-empty' : 'prescricoes-empty';
    row.remove();
    toggleEmpty(containerId, emptyId);
  };

  var btnAddExame = document.getElementById('btn-add-exame');
  var btnAddPrescricao = document.getElementById('btn-add-prescricao');
  if (btnAddExame) btnAddExame.addEventListener('click', addExame);
  if (btnAddPrescricao) btnAddPrescricao.addEventListener('click', addPrescricao);

  // UX: ao abrir o form (collapse), rola até ele e foca a queixa — pronto pra digitar.
  // Se a página já carrega com o form aberto (atendimento vazio), só foca, sem roubar o scroll.
  var collapseEl = document.getElementById('nova-consulta');
  var queixa = document.getElementById('queixa');
  if (collapseEl && queixa) {
    if (collapseEl.classList.contains('show')) {
      try { queixa.focus({ preventScroll: true }); } catch (e) {}
    }
    collapseEl.addEventListener('shown.bs.collapse', function () {
      collapseEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
      try { queixa.focus({ preventScroll: true }); } catch (e) {}
    });
  }
})();
</script>
@endsection
