{{--
  Partial: _paciente_autocomplete.blade.php
  Uso: @include('content.pages.partials._paciente_autocomplete', [
         'fieldName'   => 'paciente_id',     // name do hidden input (obrigatório)
         'preSelected' => $pacienteAnterior, // ?Paciente (opcional)
         'label'       => 'Paciente',        // label do campo (opcional)
         'required'    => true,              // exibe asterisco (opcional)
         'placeholder' => '...',             // placeholder (opcional)
       ])

  Estratégia de isolamento:
  - @once garante que a função _initAutocompletePaciente() seja definida UMA VEZ
    por página mesmo que o partial seja incluído várias vezes.
  - Cada inclusão gera um $uid único e inicializa sua própria instância.
  - Endpoint: GET /pacientes/buscar?q= (rota do grupo nivel:3)
--}}

@php
  $uid         = 'pac_' . Str::random(6);
  $label       = $label       ?? 'Paciente';
  $required    = $required    ?? true;
  $preSelected = $preSelected ?? null;
  $placeholder = $placeholder ?? 'Digite o nome ou matrícula do paciente...';
  $fieldName   = $fieldName   ?? 'paciente_id';
@endphp

<div id="wrap_{{ $uid }}" class="position-relative">

  {{-- Label --}}
  <label class="form-label" for="search_{{ $uid }}">
    {{ $label }}@if($required)<span class="text-danger ms-1">*</span>@endif
  </label>

  {{-- Input com ícone de estado e botão limpar --}}
  <div class="input-group">
    <span class="input-group-text" id="icon-wrap_{{ $uid }}">
      <i class="mdi mdi-account-search-outline" id="icon_{{ $uid }}"></i>
    </span>
    <input
      type="text"
      id="search_{{ $uid }}"
      class="form-control @error($fieldName) is-invalid @enderror"
      placeholder="{{ $placeholder }}"
      autocomplete="off"
      value="{{ $preSelected
        ? $preSelected->nome . ($preSelected->matricula ? ' — ' . $preSelected->matricula : '')
        : '' }}">
    <button
      type="button"
      class="btn btn-outline-secondary {{ $preSelected ? '' : 'd-none' }}"
      id="clear_{{ $uid }}"
      title="Limpar seleção">
      <i class="mdi mdi-close"></i>
    </button>
  </div>

  {{-- Dropdown de sugestões --}}
  <div
    id="drop_{{ $uid }}"
    class="dropdown-menu w-100 shadow-sm"
    style="max-height:260px; overflow-y:auto; position:absolute; z-index:1050; top:100%; left:0;">
  </div>

  {{-- Hidden input com o id real --}}
  <input
    type="hidden"
    name="{{ $fieldName }}"
    id="hidden_{{ $uid }}"
    value="{{ old($fieldName, $preSelected?->id) }}">

  {{-- Erro de validação server-side --}}
  @error($fieldName)
    <div class="invalid-feedback d-block">{{ $message }}</div>
  @enderror

  {{-- Badge de confirmação (visível após seleção) --}}
  <div id="conf_{{ $uid }}" class="{{ $preSelected ? '' : 'd-none' }} mt-2">
    <span class="badge bg-label-success">
      <i class="mdi mdi-check-circle-outline me-1"></i>Paciente selecionado
    </span>
  </div>

  {{-- Dica de digitação (visível enquanto nada está selecionado) --}}
  <small id="hint_{{ $uid }}" class="{{ $preSelected ? 'd-none' : '' }} text-muted mt-1 d-block">
    <i class="mdi mdi-information-outline me-1"></i>Digite ao menos 2 caracteres para filtrar.
  </small>

</div>

{{--
  Definição da função de inicialização — renderiza UMA VEZ por página via @once.
  O endereço de arquivo:linha é usado como chave de @once, então múltiplas
  inclusões deste partial produzem apenas uma definição de função.
--}}
@once
<script>
/**
 * Inicializa o autocomplete de paciente para uma instância identificada por uid.
 * Todos os elementos são encontrados via getElementById(prefix + uid) para garantir
 * isolamento quando há múltiplas instâncias na mesma página.
 */
function _initAutocompletePaciente(uid) {
  var searchEl = document.getElementById('search_' + uid);
  var hiddenEl = document.getElementById('hidden_' + uid);
  var dropEl   = document.getElementById('drop_'   + uid);
  var clearBtn = document.getElementById('clear_'  + uid);
  var iconEl   = document.getElementById('icon_'   + uid);
  var confEl   = document.getElementById('conf_'   + uid);
  var hintEl   = document.getElementById('hint_'   + uid);

  if (!searchEl) return;

  var debounceTimer;

  // Aplica o estado "selecionado" nos elementos visuais
  function setSelected(id, label) {
    hiddenEl.value = id;
    searchEl.value = label;
    dropEl.innerHTML = '';
    dropEl.classList.remove('show');
    if (clearBtn) clearBtn.classList.remove('d-none');
    if (iconEl)   iconEl.className = 'mdi mdi-check-circle-outline text-success';
    if (confEl)   confEl.classList.remove('d-none');
    if (hintEl)   hintEl.classList.add('d-none');
    searchEl.classList.remove('is-invalid');
  }

  // Limpa a seleção e devolve o foco ao campo
  function clearSelection() {
    hiddenEl.value = '';
    searchEl.value = '';
    dropEl.innerHTML = '';
    dropEl.classList.remove('show');
    if (clearBtn) clearBtn.classList.add('d-none');
    if (iconEl)   iconEl.className = 'mdi mdi-account-search-outline';
    if (confEl)   confEl.classList.add('d-none');
    if (hintEl)   hintEl.classList.remove('d-none');
    searchEl.focus();
  }

  // Busca pacientes via AJAX e renderiza o dropdown
  function buscar(termo) {
    if (iconEl) iconEl.className = 'mdi mdi-loading mdi-spin';

    fetch('/pacientes/buscar?q=' + encodeURIComponent(termo), {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
    .then(function(r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(function(data) {
      if (iconEl) iconEl.className = 'mdi mdi-account-search-outline';
      dropEl.innerHTML = '';

      if (!data.length) {
        dropEl.innerHTML =
          '<span class="dropdown-item-text text-muted small py-2 px-3 d-block">' +
          '<i class="mdi mdi-account-off-outline me-1"></i>Nenhum paciente encontrado.</span>';
      } else {
        data.forEach(function(p) {
          var btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'dropdown-item py-2';
          btn.innerHTML =
            '<i class="mdi mdi-account-outline me-2 text-muted"></i>' +
            '<strong>' + p.nome + '</strong>' +
            (p.matricula
              ? '<small class="text-muted ms-2">— ' + p.matricula + '</small>'
              : '');
          btn.addEventListener('click', function() {
            setSelected(p.id, p.nome);
          });
          dropEl.appendChild(btn);
        });
      }
      dropEl.classList.add('show');
    })
    .catch(function(err) {
      if (iconEl) iconEl.className = 'mdi mdi-alert-circle-outline text-danger';
      dropEl.innerHTML =
        '<span class="dropdown-item-text text-danger small py-2 px-3 d-block">' +
        '<i class="mdi mdi-alert-circle-outline me-1"></i>Erro na busca. Tente novamente.</span>';
      dropEl.classList.add('show');
      console.error('[Autocomplete] Falha na busca de paciente:', err);
    });
  }

  // Listener principal: dispara busca com debounce de 300ms
  searchEl.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    var q = this.value.trim();

    // Ao digitar novamente, reseta estado de seleção
    hiddenEl.value = '';
    if (clearBtn) clearBtn.classList.add('d-none');
    if (iconEl)   iconEl.className = 'mdi mdi-account-search-outline';
    if (confEl)   confEl.classList.add('d-none');
    if (hintEl)   hintEl.classList.remove('d-none');

    if (q.length < 2) {
      dropEl.innerHTML = '';
      dropEl.classList.remove('show');
      return;
    }

    debounceTimer = setTimeout(function() { buscar(q); }, 300);
  });

  // Botão limpar
  if (clearBtn) {
    clearBtn.addEventListener('click', clearSelection);
  }

  // Fecha dropdown ao clicar fora
  document.addEventListener('click', function(e) {
    if (!searchEl.contains(e.target) && !dropEl.contains(e.target)) {
      dropEl.classList.remove('show');
    }
  });

  // Validação client-side: impede submit sem paciente selecionado
  var form = searchEl.closest('form');
  if (form) {
    form.addEventListener('submit', function(e) {
      if (!hiddenEl.value) {
        e.preventDefault();
        searchEl.classList.add('is-invalid');
        searchEl.focus();
      }
    });
  }
}
</script>
@endonce

{{-- Inicializa esta instância específica após o DOM estar pronto --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
  _initAutocompletePaciente('{{ $uid }}');
});
</script>
