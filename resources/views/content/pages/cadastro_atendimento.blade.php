@php
$configData = Helper::appClasses();
$voltarUrl  = url()->previous('/atendimentos');

// Iniciais do profissional para o avatar no hero
$iniciaisProf = $profissionalLogado
    ? collect(explode(' ', $profissionalLogado->nome ?? 'P'))
        ->filter()->map(fn($p) => strtoupper($p[0]))->take(2)->implode('')
    : null;

// $pacienteAnterior é passado pelo controller quando há old('paciente_id') após falha de validação
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Novo Atendimento')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================ --}}
  {{-- HERO HEADER                                                        --}}
  {{-- Quando profissional logado: exibe dados do profissional + data.   --}}
  {{-- Quando admin/recepcionista: exibe ícone genérico + título.        --}}
  {{-- UX-12: sem duplicidade — o profissional aparece APENAS aqui.      --}}
  {{-- ================================================================ --}}
  <div class="card mb-4">
    <div class="card-body py-4">
      <div class="d-flex flex-wrap align-items-center gap-4">

        {{-- Avatar: iniciais do profissional (verde) ou ícone genérico (cinza para admin) --}}
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

        {{-- Dados do profissional (quando logado) ou título genérico --}}
        <div class="flex-grow-1">
          <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            @if($profissionalLogado)
              <h4 class="mb-0">{{ $profissionalLogado->nome }}</h4>
              @if($profissionalLogado->especialidade)
                <span class="badge rounded-pill bg-label-primary">{{ $profissionalLogado->especialidade }}</span>
              @endif
            @else
              <h4 class="mb-0">Novo Atendimento</h4>
            @endif
            <span class="badge rounded-pill bg-label-info">Novo Atendimento</span>
          </div>
          <div class="d-flex flex-wrap gap-3 text-muted small">
            @if($profissionalLogado && $profissionalLogado->registro_profissional)
              <span><i class="mdi mdi-card-account-details-outline me-1"></i>{{ $profissionalLogado->registro_profissional }}</span>
            @endif
            {{-- Data de abertura do atendimento (hoje) --}}
            <span><i class="mdi mdi-calendar-today-outline me-1"></i>{{ now()->format('d/m/Y') }}</span>
          </div>
        </div>

        <div class="flex-shrink-0">
          <a href="{{ $voltarUrl }}" class="btn btn-default">
            <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
          </a>
        </div>

      </div>
    </div>
  </div>

  <form class="browser-default-validation" action="/cadastrar-atendimento" method="POST" id="form-atendimento">
    @csrf

    <div class="row g-4">
      <div class="col-md-7">

        {{-- ============================================================
             Card de Identificação do Paciente
             UX-11b: autocomplete AJAX — digitar ≥ 2 chars dispara fetch
             em /pacientes/buscar?q=. O paciente_id fica em hidden input.
             UX-12: quando profissionalLogado, o select de profissional
             fica hidden — profissional já está registrado no hero.
             ============================================================ --}}
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="mdi mdi-account-search-outline me-2"></i>Identificação do Paciente
              {{-- Especialidade como contexto do tipo de atendimento --}}
              @if($profissionalLogado && $profissionalLogado->especialidade)
                <small class="text-muted fw-normal fs-6 ms-2">· {{ $profissionalLogado->especialidade }}</small>
              @endif
            </h5>
          </div>
          <div class="card-body">

            {{-- Autocomplete AJAX de paciente (UX-11b) --}}
            <div class="position-relative mb-4" id="autocomplete-wrapper">

              {{-- Input visível de busca --}}
              <div class="input-group">
                <span class="input-group-text" id="autocomplete-icon">
                  <i class="mdi mdi-account-search-outline" id="icon-search"></i>
                </span>
                <input type="text" id="paciente-search"
                  class="form-control @error('paciente_id') is-invalid @enderror"
                  placeholder="Digite o nome ou matrícula do paciente..."
                  value="{{ $pacienteAnterior ? $pacienteAnterior->nome . ' — ' . ($pacienteAnterior->matricula ?? '') : '' }}"
                  autocomplete="off">
                {{-- Botão de limpar: aparece quando há um paciente selecionado --}}
                <button type="button" class="btn btn-outline-secondary d-none" id="btn-limpar-paciente"
                  title="Limpar seleção">
                  <i class="mdi mdi-close"></i>
                </button>
              </div>

              {{-- Dropdown de sugestões --}}
              {{-- Nota: NÃO usar d-none aqui — .dropdown-menu já tem display:none no CSS do tema.
                   A visibilidade é controlada pela classe .show (Bootstrap: .dropdown-menu.show { display:block }) --}}
              <div id="autocomplete-dropdown"
                class="dropdown-menu w-100 shadow-sm"
                style="max-height:260px; overflow-y:auto; position:absolute; z-index:1050; top:100%; left:0;">
              </div>

              {{-- Campo hidden que o form de fato envia --}}
              <input type="hidden" name="paciente_id" id="paciente_id"
                value="{{ old('paciente_id', '') }}">

              {{-- Erro de validação server-side --}}
              @error('paciente_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror

              {{-- Indicador de paciente confirmado --}}
              <div id="paciente-confirmado" class="{{ $pacienteAnterior ? '' : 'd-none' }} mt-2">
                <span class="badge bg-label-success">
                  <i class="mdi mdi-check-circle-outline me-1"></i>Paciente selecionado
                </span>
              </div>

              {{-- Dica inicial --}}
              <small id="paciente-hint"
                class="{{ $pacienteAnterior ? 'd-none' : '' }} text-muted mt-1 d-block">
                <i class="mdi mdi-information-outline me-1"></i>Digite ao menos 2 caracteres para filtrar.
              </small>

            </div>

            {{-- Profissional: hidden quando logado como profissional (já no hero); select para admin --}}
            @if($profissionalLogado)
              <input type="hidden" name="profissional_id" value="{{ $profissionalLogado->id }}">
            @else
              <div class="form-floating form-floating-outline">
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

          </div>
        </div>

        {{-- Botões de ação --}}
        <div class="d-flex gap-2 mb-2">
          <button type="submit" class="btn btn-primary">
            <i class="mdi mdi-folder-open-outline me-1"></i>Abrir Atendimento
          </button>
          <a href="{{ $voltarUrl }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>

      </div>
    </div>

  </form>
</div>

@endsection

{{-- ============================================================
     UX-11b: Autocomplete AJAX de paciente.

     Fluxo:
     1. Usuário digita ≥ 2 chars → debounce 300ms → fetch /pacientes/buscar?q=
     2. Servidor retorna JSON [{id, nome, matricula}] via SearchService
     3. Dropdown renderiza itens; clique → paciente_id hidden preenchido
     4. Botão X → limpa seleção, reabre campo vazio
     5. Submit sem seleção → valida client-side e exibe erro
     ============================================================ --}}
@section('page-script')
<script>
(function () {
  var searchInput  = document.getElementById('paciente-search');
  var dropdown     = document.getElementById('autocomplete-dropdown');
  var hiddenInput  = document.getElementById('paciente_id');
  var btnLimpar    = document.getElementById('btn-limpar-paciente');
  var confirmado   = document.getElementById('paciente-confirmado');
  var hint         = document.getElementById('paciente-hint');
  var iconSearch   = document.getElementById('icon-search');
  var debounceTimer = null;

  if (!searchInput) return;

  // Se o formulário foi re-renderizado com old() (erro de validação), o hidden já tem valor
  if (hiddenInput.value !== '') {
    btnLimpar.classList.remove('d-none');
    iconSearch.className = 'mdi mdi-check text-success';
  }

  // Monta cada item do dropdown a partir do objeto retornado pela API
  function criarItem(p) {
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'dropdown-item d-flex flex-column py-2 px-3';
    btn.innerHTML =
      '<span class="fw-semibold">' + p.nome + '</span>' +
      '<small class="text-muted">' + (p.matricula || 'Sem matrícula') + '</small>';
    btn.addEventListener('click', function () { selecionarPaciente(p); });
    return btn;
  }

  // Aplica a seleção e atualiza toda a UI
  function selecionarPaciente(p) {
    searchInput.value = p.nome + (p.matricula ? ' — ' + p.matricula : '');
    hiddenInput.value = p.id;
    dropdown.classList.remove('show'); // fecha dropdown após seleção
    btnLimpar.classList.remove('d-none');
    confirmado.classList.remove('d-none');
    hint.classList.add('d-none');
    iconSearch.className = 'mdi mdi-check text-success';
    searchInput.classList.remove('is-invalid');
  }

  // Limpa a seleção e devolve o foco ao campo de busca
  function limparSelecao() {
    searchInput.value = '';
    hiddenInput.value = '';
    btnLimpar.classList.add('d-none');
    confirmado.classList.add('d-none');
    hint.classList.remove('d-none');
    iconSearch.className = 'mdi mdi-account-search-outline';
    dropdown.classList.remove('show'); // fecha dropdown ao limpar
    searchInput.focus();
  }

  // Exibe estado de carregamento — .show é o mecanismo correto do Bootstrap
  // (.dropdown-menu.show { display:block } sobrepõe .dropdown-menu { display:none })
  function mostrarCarregando() {
    dropdown.innerHTML =
      '<span class="dropdown-item-text text-muted small py-2 px-3 d-block">' +
      '<i class="mdi mdi-loading mdi-spin me-1"></i>Buscando...</span>';
    dropdown.classList.add('show');
  }

  // Busca pacientes via AJAX e renderiza o dropdown com os resultados
  function buscarPacientes(termo) {
    mostrarCarregando();

    fetch('/pacientes/buscar?q=' + encodeURIComponent(termo), {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'  // garante resposta JSON mesmo em erros PHP/Laravel
      }
    })
    .then(function (res) {
      if (!res.ok) { throw new Error('HTTP ' + res.status); }
      return res.json();
    })
    .then(function (pacientes) {
      dropdown.innerHTML = '';
      if (!pacientes.length) {
        var vazio = document.createElement('span');
        vazio.className = 'dropdown-item-text text-muted small py-2 px-3 d-block';
        vazio.textContent = 'Nenhum paciente encontrado para "' + searchInput.value + '".';
        dropdown.appendChild(vazio);
      } else {
        pacientes.forEach(function (p) { dropdown.appendChild(criarItem(p)); });
      }
      dropdown.classList.add('show'); // garante visibilidade após renderizar resultados
    })
    .catch(function (err) {
      dropdown.innerHTML =
        '<span class="dropdown-item-text text-danger small py-2 px-3 d-block">' +
        '<i class="mdi mdi-alert-circle-outline me-1"></i>Erro na busca. Tente novamente.</span>';
      dropdown.classList.add('show'); // mantém visível para o usuário ver o erro
      console.error('[Autocomplete] Falha na busca de paciente:', err);
    });
  }

  // Input com debounce de 300ms antes de disparar o fetch
  searchInput.addEventListener('input', function () {
    hiddenInput.value = '';
    btnLimpar.classList.add('d-none');
    confirmado.classList.add('d-none');
    iconSearch.className = 'mdi mdi-account-search-outline';
    hint.classList.remove('d-none');

    var termo = this.value.trim();
    if (termo.length < 2) {
      dropdown.classList.remove('show'); // fecha dropdown se termo muito curto
      clearTimeout(debounceTimer);
      return;
    }

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(function () { buscarPacientes(termo); }, 300);
  });

  btnLimpar.addEventListener('click', limparSelecao);

  // Fecha dropdown ao clicar fora do componente
  document.addEventListener('click', function (e) {
    if (!e.target.closest('#autocomplete-wrapper')) {
      dropdown.classList.remove('show');
    }
  });

  // Validação client-side: impede submit sem paciente selecionado
  document.getElementById('form-atendimento').addEventListener('submit', function (e) {
    if (!hiddenInput.value) {
      e.preventDefault();
      searchInput.classList.add('is-invalid');
      searchInput.focus();
    }
  });
})();
</script>
@endsection
