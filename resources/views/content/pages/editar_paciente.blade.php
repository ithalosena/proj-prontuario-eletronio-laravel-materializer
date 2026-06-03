@php $configData = Helper::appClasses(); @endphp

@extends('layouts/layoutMaster')

@section('title', 'Editar Paciente')

{{-- Breadcrumb: Início > Pacientes > Editar --}}
@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',    'url' => '/'],
      ['label' => 'Pacientes', 'url' => '/pacientes'],
      ['label' => 'Editar',    'url' => null],
    ]
  ])
@endpush

@section('content')
@php
  // Iniciais do paciente para o avatar do hero
  $iniciais = collect(explode(' ', $paciente->nome))->filter()->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('');
  $iniciais = mb_strtoupper($iniciais ?: 'P');
  // Idade e menoridade (controla a exibição do bloco de responsável legal)
  $idade   = $paciente->data_nascimento ? $paciente->data_nascimento->age : null;
  $isMenor = $idade !== null && $idade < 18;
  // $rOnly = campos sensíveis somente leitura (nivel > 2)
  $rOnly = !$podeEditarSensivel;

  $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
  $parentescos = ['pai'=>'Pai','mae'=>'Mãe','irmao'=>'Irmão/Irmã','tio'=>'Tio/Tia','avo'=>'Avô/Avó','conjuge'=>'Cônjuge/Companheiro(a)','amigo'=>'Amigo(a)','outro'=>'Outro'];
@endphp

<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Flash de sucesso --}}
  @if(session('success'))
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    <i class="mdi mdi-check-circle-outline me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
  </div>
  @endif

  {{-- Erros de validação --}}
  @if($errors->any())
  <div class="alert alert-danger mb-4" role="alert">
    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
  @endif

  {{-- ================================================================ --}}
  {{-- HERO — avatar + identificação                                    --}}
  {{-- ================================================================ --}}
  <div class="card mb-4">
    <div class="card-body py-4">
      <div class="d-flex flex-wrap align-items-center gap-4">
        <div class="flex-shrink-0">
          <div class="avatar avatar-xl">
            <span class="avatar-initial rounded-circle bg-label-primary"
                  style="font-size:1.4rem; width:64px; height:64px; display:flex; align-items:center; justify-content:center;">
              {{ $iniciais }}
            </span>
          </div>
        </div>
        <div class="flex-grow-1">
          <h4 class="mb-1">Editar — {{ $paciente->nome }}</h4>
          <div class="d-flex flex-wrap gap-3 text-muted small">
            @if($paciente->matricula)<span><i class="mdi mdi-card-account-details-outline me-1"></i>{{ $paciente->matricula }}</span>@endif
            @if($paciente->curso)<span><i class="mdi mdi-school-outline me-1"></i>{{ $paciente->curso }}</span>@endif
            @if($idade !== null)<span><i class="mdi mdi-cake-variant-outline me-1"></i>{{ $idade }} anos</span>@endif
          </div>
        </div>
        <div class="d-flex flex-column gap-2 ms-auto">
          <a href="/pacientes/{{ $paciente->id }}" class="btn btn-outline-primary btn-sm">
            <i class="mdi mdi-account-outline me-1"></i>Ver Perfil
          </a>
          <a href="{{ url()->previous('/pacientes') }}" class="btn btn-default btn-sm">
            <i class="mdi mdi-arrow-u-left-bottom me-1"></i>Voltar
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- UX-24: aviso para roles sem acesso a dados sensíveis --}}
  @if($rOnly)
  <div class="alert alert-info mb-4" role="alert">
    <i class="mdi mdi-information-outline me-2"></i>
    Você pode atualizar contatos, endereço, dados complementares e contatos de emergência.
    Os <strong>dados cadastrais</strong> (nome, CPF, nascimento, matrícula, curso) são restritos a administradores.
  </div>
  @endif

  <form action="/atualizar-paciente/{{ $paciente->id }}" method="POST">
    @csrf
    @method('PUT')

    {{-- ============================================================ --}}
    {{-- CARD 1 — Dados cadastrais (sensíveis, UX-24)                 --}}
    {{-- ============================================================ --}}
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center gap-2">
        <i class="mdi mdi-account-outline text-primary"></i>
        <h5 class="card-title mb-0">Dados cadastrais</h5>
        @if($rOnly)<span class="badge bg-label-secondary ms-1">Somente leitura</span>@endif
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label" for="nome">Nome completo @if($rOnly)<span class="badge bg-label-secondary small">Restrito</span>@endif</label>
            <input value="{{ old('nome', $paciente->nome) }}" name="nome" id="nome" type="text"
                   class="form-control {{ $rOnly ? 'bg-light' : '' }}" {{ $rOnly ? 'readonly' : 'required' }}>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="documento">CPF / Documento @if($rOnly)<span class="badge bg-label-secondary small">Restrito</span>@endif</label>
            <input value="{{ old('documento', $paciente->documento) }}" name="documento" id="documento" type="text"
                   class="form-control {{ $rOnly ? 'bg-light' : '' }}" {{ $rOnly ? 'readonly' : 'required' }}>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="sexo">Sexo @if($rOnly)<span class="badge bg-label-secondary small">Restrito</span>@endif</label>
            @if($rOnly)<input type="hidden" name="sexo" value="{{ $paciente->sexo }}">@endif
            <select name="{{ $rOnly ? '_sexo_display' : 'sexo' }}" id="sexo"
                    class="form-select {{ $rOnly ? 'bg-light' : '' }}" {{ $rOnly ? 'disabled' : 'required' }}>
              <option value="F" {{ $paciente->sexo == 'F' ? 'selected' : '' }}>Feminino</option>
              <option value="M" {{ $paciente->sexo == 'M' ? 'selected' : '' }}>Masculino</option>
              <option value="outro" {{ $paciente->sexo == 'outro' ? 'selected' : '' }}>Outro</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label" for="data_nascimento">Data de nascimento @if($rOnly)<span class="badge bg-label-secondary small">Restrito</span>@endif</label>
            <input value="{{ old('data_nascimento', $paciente->data_nascimento ? $paciente->data_nascimento->format('Y-m-d') : '') }}"
                   name="data_nascimento" id="data_nascimento" type="date"
                   class="form-control {{ $rOnly ? 'bg-light' : '' }}" {{ $rOnly ? 'readonly' : 'required' }}>
          </div>
          <div class="col-md-4">
            <label class="form-label" for="matricula">Matrícula @if($rOnly)<span class="badge bg-label-secondary small">Restrito</span>@endif</label>
            <input value="{{ old('matricula', $paciente->matricula) }}" name="matricula" id="matricula" type="text"
                   class="form-control {{ $rOnly ? 'bg-light' : '' }}" {{ $rOnly ? 'readonly' : 'required' }}>
          </div>
          <div class="col-md-4">
            <label class="form-label" for="curso">Curso @if($rOnly)<span class="badge bg-label-secondary small">Restrito</span>@endif</label>
            <input value="{{ old('curso', $paciente->curso) }}" name="curso" id="curso" type="text"
                   class="form-control {{ $rOnly ? 'bg-light' : '' }}" {{ $rOnly ? 'readonly' : 'required' }}>
          </div>
          {{-- Nome social — complementar, editável ≤4 --}}
          <div class="col-md-6">
            <label class="form-label" for="nome_social">Nome social</label>
            <input value="{{ old('nome_social', $paciente->nome_social) }}" name="nome_social" id="nome_social" type="text" class="form-control" maxlength="255">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="nome_mae">Nome da mãe</label>
            <input value="{{ old('nome_mae', $paciente->nome_mae) }}" name="nome_mae" id="nome_mae" type="text" class="form-control" maxlength="255">
          </div>
        </div>
      </div>
    </div>

    {{-- ============================================================ --}}
    {{-- CARD 2 — Contatos                                            --}}
    {{-- ============================================================ --}}
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center gap-2">
        <i class="mdi mdi-phone-outline text-primary"></i>
        <h5 class="card-title mb-0">Contatos</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label" for="contato">Telefone principal</label>
            <input value="{{ old('contato', $paciente->contato) }}" name="contato" id="contato" type="text" class="form-control" placeholder="(33) 99999-9999" maxlength="20">
          </div>
          <div class="col-md-4">
            <label class="form-label" for="telefone_alternativo">Telefone alternativo</label>
            <input value="{{ old('telefone_alternativo', $paciente->telefone_alternativo) }}" name="telefone_alternativo" id="telefone_alternativo" type="text" class="form-control" maxlength="20">
          </div>
          <div class="col-md-4">
            <label class="form-label" for="email_alternativo">E-mail alternativo</label>
            <input value="{{ old('email_alternativo', $paciente->email_alternativo) }}" name="email_alternativo" id="email_alternativo" type="email" class="form-control" maxlength="255">
          </div>
        </div>
      </div>
    </div>

    {{-- ============================================================ --}}
    {{-- CARD 3 — Endereço                                            --}}
    {{-- ============================================================ --}}
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center gap-2">
        <i class="mdi mdi-map-marker-outline text-primary"></i>
        <h5 class="card-title mb-0">Endereço</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label" for="cep">CEP</label>
            <input value="{{ old('cep', $paciente->cep) }}" name="cep" id="cep" type="text" class="form-control" placeholder="00000-000" maxlength="9">
          </div>
          <div class="col-md-7">
            <label class="form-label" for="logradouro">Logradouro</label>
            <input value="{{ old('logradouro', $paciente->logradouro) }}" name="logradouro" id="logradouro" type="text" class="form-control" maxlength="255">
          </div>
          <div class="col-md-2">
            <label class="form-label" for="numero">Número</label>
            <input value="{{ old('numero', $paciente->numero) }}" name="numero" id="numero" type="text" class="form-control" maxlength="20">
          </div>
          <div class="col-md-4">
            <label class="form-label" for="complemento">Complemento</label>
            <input value="{{ old('complemento', $paciente->complemento) }}" name="complemento" id="complemento" type="text" class="form-control" maxlength="100">
          </div>
          <div class="col-md-4">
            <label class="form-label" for="bairro">Bairro</label>
            <input value="{{ old('bairro', $paciente->bairro) }}" name="bairro" id="bairro" type="text" class="form-control" maxlength="100">
          </div>
          <div class="col-md-3">
            <label class="form-label" for="cidade">Cidade</label>
            <input value="{{ old('cidade', $paciente->cidade) }}" name="cidade" id="cidade" type="text" class="form-control" maxlength="100">
          </div>
          <div class="col-md-1">
            <label class="form-label" for="uf">UF</label>
            <select name="uf" id="uf" class="form-select">
              <option value="">—</option>
              @foreach($ufs as $uf)
                <option value="{{ $uf }}" {{ old('uf', $paciente->uf) === $uf ? 'selected' : '' }}>{{ $uf }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-8">
            <label class="form-label" for="ponto_referencia">Ponto de referência</label>
            <input value="{{ old('ponto_referencia', $paciente->ponto_referencia) }}" name="ponto_referencia" id="ponto_referencia" type="text" class="form-control" maxlength="255">
          </div>
          {{-- Endereço legado (formato antigo de linha única) — preservado como fallback --}}
          <div class="col-md-4">
            <label class="form-label" for="endereco">Endereço (formato antigo)</label>
            <input value="{{ old('endereco', $paciente->endereco) }}" name="endereco" id="endereco" type="text" class="form-control" maxlength="255">
          </div>
        </div>
      </div>
    </div>

    {{-- ============================================================ --}}
    {{-- CARD 4 — Dados complementares                                --}}
    {{-- ============================================================ --}}
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center gap-2">
        <i class="mdi mdi-card-account-details-outline text-primary"></i>
        <h5 class="card-title mb-0">Dados complementares</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label" for="naturalidade_cidade">Naturalidade — Cidade</label>
            <input value="{{ old('naturalidade_cidade', $paciente->naturalidade_cidade) }}" name="naturalidade_cidade" id="naturalidade_cidade" type="text" class="form-control" maxlength="100">
          </div>
          <div class="col-md-2">
            <label class="form-label" for="naturalidade_uf">UF</label>
            <select name="naturalidade_uf" id="naturalidade_uf" class="form-select">
              <option value="">—</option>
              @foreach($ufs as $uf)
                <option value="{{ $uf }}" {{ old('naturalidade_uf', $paciente->naturalidade_uf) === $uf ? 'selected' : '' }}>{{ $uf }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="raca_cor">Raça/Cor</label>
            <select name="raca_cor" id="raca_cor" class="form-select">
              <option value="">Não informar</option>
              @foreach(['branca'=>'Branca','preta'=>'Preta','parda'=>'Parda','amarela'=>'Amarela','indigena'=>'Indígena','nao_declarado'=>'Prefiro não declarar'] as $val => $label)
                <option value="{{ $val }}" {{ old('raca_cor', $paciente->raca_cor) === $val ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label" for="estado_civil">Estado civil</label>
            <select name="estado_civil" id="estado_civil" class="form-select">
              <option value="">Não informar</option>
              @foreach(['solteiro'=>'Solteiro(a)','casado'=>'Casado(a)','divorciado'=>'Divorciado(a)','viuvo'=>'Viúvo(a)','uniao_estavel'=>'União estável'] as $val => $label)
                <option value="{{ $val }}" {{ old('estado_civil', $paciente->estado_civil) === $val ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </div>

    {{-- ============================================================ --}}
    {{-- CARD 5 — Contatos de emergência                              --}}
    {{-- ============================================================ --}}
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center gap-2">
        <i class="mdi mdi-phone-alert-outline text-primary"></i>
        <h5 class="card-title mb-0">Contatos de emergência</h5>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-3">Contato principal acionado pelo setor de saúde em emergências.</p>
        <div class="row g-3 mb-2">
          <div class="col-md-5">
            <label class="form-label" for="contato_emergencia_nome">Nome (principal)</label>
            <input value="{{ old('contato_emergencia_nome', $paciente->contato_emergencia_nome) }}" name="contato_emergencia_nome" id="contato_emergencia_nome" type="text" class="form-control" maxlength="255">
          </div>
          <div class="col-md-3">
            <label class="form-label" for="contato_emergencia_telefone">Telefone</label>
            <input value="{{ old('contato_emergencia_telefone', $paciente->contato_emergencia_telefone) }}" name="contato_emergencia_telefone" id="contato_emergencia_telefone" type="text" class="form-control" maxlength="20">
          </div>
          <div class="col-md-4">
            <label class="form-label" for="contato_emergencia_parentesco">Parentesco</label>
            <select name="contato_emergencia_parentesco" id="contato_emergencia_parentesco" class="form-select">
              <option value="">Selecione</option>
              @foreach($parentescos as $val => $label)
                <option value="{{ $val }}" {{ old('contato_emergencia_parentesco', $paciente->contato_emergencia_parentesco) === $val ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <hr class="my-3">
        <p class="text-muted small mb-3">Segundo contato (opcional).</p>
        <div class="row g-3">
          <div class="col-md-5">
            <label class="form-label" for="contato_emergencia2_nome">Nome (secundário)</label>
            <input value="{{ old('contato_emergencia2_nome', $paciente->contato_emergencia2_nome) }}" name="contato_emergencia2_nome" id="contato_emergencia2_nome" type="text" class="form-control" maxlength="255">
          </div>
          <div class="col-md-3">
            <label class="form-label" for="contato_emergencia2_telefone">Telefone</label>
            <input value="{{ old('contato_emergencia2_telefone', $paciente->contato_emergencia2_telefone) }}" name="contato_emergencia2_telefone" id="contato_emergencia2_telefone" type="text" class="form-control" maxlength="20">
          </div>
          <div class="col-md-4">
            <label class="form-label" for="contato_emergencia2_parentesco">Parentesco</label>
            <select name="contato_emergencia2_parentesco" id="contato_emergencia2_parentesco" class="form-select">
              <option value="">Selecione</option>
              @foreach($parentescos as $val => $label)
                <option value="{{ $val }}" {{ old('contato_emergencia2_parentesco', $paciente->contato_emergencia2_parentesco) === $val ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </div>

    {{-- ============================================================ --}}
    {{-- CARD 6 — Responsável legal (apenas para menores de 18)        --}}
    {{-- ============================================================ --}}
    @if($isMenor)
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center gap-2">
        <i class="mdi mdi-account-child-outline text-primary"></i>
        <h5 class="card-title mb-0">Responsável legal</h5>
        <span class="badge bg-label-warning ms-1">Menor de 18 anos</span>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-5">
            <label class="form-label" for="responsavel_nome">Nome completo</label>
            <input value="{{ old('responsavel_nome', $paciente->responsavel_nome) }}" name="responsavel_nome" id="responsavel_nome" type="text" class="form-control" maxlength="255">
          </div>
          <div class="col-md-3">
            <label class="form-label" for="responsavel_cpf">CPF</label>
            <input value="{{ old('responsavel_cpf', $paciente->responsavel_cpf) }}" name="responsavel_cpf" id="responsavel_cpf" type="text" class="form-control" maxlength="14">
          </div>
          <div class="col-md-4">
            <label class="form-label" for="responsavel_parentesco">Parentesco</label>
            <select name="responsavel_parentesco" id="responsavel_parentesco" class="form-select">
              <option value="">Selecione</option>
              @foreach($parentescos as $val => $label)
                <option value="{{ $val }}" {{ old('responsavel_parentesco', $paciente->responsavel_parentesco) === $val ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label" for="responsavel_telefone">Telefone</label>
            <input value="{{ old('responsavel_telefone', $paciente->responsavel_telefone) }}" name="responsavel_telefone" id="responsavel_telefone" type="text" class="form-control" maxlength="20">
          </div>
          <div class="col-md-5">
            <label class="form-label" for="responsavel_email">E-mail</label>
            <input value="{{ old('responsavel_email', $paciente->responsavel_email) }}" name="responsavel_email" id="responsavel_email" type="email" class="form-control" maxlength="255">
          </div>
        </div>
      </div>
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- CARD 7 — Dados clínicos (READ-ONLY — editáveis via ST-17)     --}}
    {{-- ============================================================ --}}
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center gap-2">
        <i class="mdi mdi-heart-pulse text-danger"></i>
        <h5 class="card-title mb-0">Dados clínicos</h5>
        <span class="badge bg-label-secondary ms-1">Somente leitura</span>
      </div>
      <div class="card-body">
        <div class="alert alert-info d-flex gap-2 py-2 mb-3" role="alert">
          <i class="mdi mdi-information-outline mt-1 flex-shrink-0"></i>
          <span class="small">Dados clínicos são preenchidos/atualizados pelo profissional de saúde durante a consulta. Edição direta será disponibilizada na tela clínica (ST-17).</span>
        </div>
        @php
          $semClinico = !$paciente->tipo_sanguineo && !$paciente->peso_kg && !$paciente->altura_cm
            && !$paciente->alergias && !$paciente->medicamentos_uso_continuo
            && !$paciente->condicoes_cronicas && !$paciente->cirurgias_previas;
          $rotuloHabito = fn($v) => $v ? ucfirst(str_replace('_', ' ', $v)) : '—';
        @endphp
        @if($semClinico)
          <p class="text-muted mb-0">Nenhum dado clínico registrado até o momento.</p>
        @else
          <div class="row g-3">
            <div class="col-md-3"><span class="text-muted small d-block">Tipo sanguíneo</span><strong>{{ $paciente->tipo_sanguineo ?: '—' }}</strong></div>
            <div class="col-md-3"><span class="text-muted small d-block">Peso</span><strong>{{ $paciente->peso_kg ? $paciente->peso_kg.' kg' : '—' }}</strong></div>
            <div class="col-md-3"><span class="text-muted small d-block">Altura</span><strong>{{ $paciente->altura_cm ? $paciente->altura_cm.' cm' : '—' }}</strong></div>
            <div class="col-md-3"><span class="text-muted small d-block">Tabagismo</span><strong>{{ $rotuloHabito($paciente->tabagismo) }}</strong></div>
            <div class="col-md-6"><span class="text-muted small d-block">Alergias</span><span>{{ $paciente->alergias ?: '—' }}</span></div>
            <div class="col-md-6"><span class="text-muted small d-block">Medicamentos em uso contínuo</span><span>{{ $paciente->medicamentos_uso_continuo ?: '—' }}</span></div>
            <div class="col-md-6"><span class="text-muted small d-block">Condições crônicas</span><span>{{ $paciente->condicoes_cronicas ?: '—' }}</span></div>
            <div class="col-md-6"><span class="text-muted small d-block">Cirurgias prévias</span><span>{{ $paciente->cirurgias_previas ?: '—' }}</span></div>
          </div>
        @endif
      </div>
    </div>

    {{-- Ações --}}
    <div class="d-flex gap-2 mb-4">
      <button type="submit" class="btn btn-primary">
        <i class="mdi mdi-content-save-outline me-1"></i>Atualizar
      </button>
      <a href="{{ url()->previous('/pacientes') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>

  </form>
</div>
@endsection
