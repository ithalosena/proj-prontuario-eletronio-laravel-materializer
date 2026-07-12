@php
$configData = Helper::appClasses();
$roleLabels = [1 => 'Administrador', 2 => 'Coordenador', 3 => 'Profissional de Saúde', 4 => 'Recepcionista', 5 => 'Paciente'];
$roleLabel  = $roleLabels[$user->nivelAcesso()] ?? 'Usuário';
$displayName = $paciente ? $paciente->nome_exibicao : $user->name;
$iniciais    = collect(explode(' ', $displayName))->filter()->map(fn($p) => strtoupper($p[0]))->take(2)->implode('');
$temFoto     = $user->avatar && Storage::disk('public')->exists($user->avatar);
$isMenor     = $paciente && $paciente->data_nascimento && $paciente->data_nascimento->age < 18;
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Meu Perfil')

@push('breadcrumbs')
  @include('content.pages.partials._breadcrumb', [
    'breadcrumbs' => [
      ['label' => 'Início',     'url' => '/'],
      ['label' => 'Meu Perfil', 'url' => null],
    ]
  ])
@endpush

@section('page-style')
<style>
  .collapse-toggle { cursor: pointer; }
  .collapse-toggle .chevron { transition: transform .2s ease; }
  .collapse-toggle[aria-expanded="false"] .chevron { transform: rotate(-90deg); }
  .fld-help { display:inline-flex; align-items:center; justify-content:center; width:16px; height:16px;
              margin-left:.3rem; border-radius:50%; background:#3DAA4A; color:#fff; font-size:11px;
              font-weight:700; cursor:pointer; user-select:none; line-height:1; }
  .fld-help-pop { position:absolute; z-index:2000; max-width:260px; background:#212121; color:#fff;
                  padding:.5rem .7rem; border-radius:6px; font-size:12px; line-height:1.4; box-shadow:0 6px 20px rgba(0,0,0,.3); }
  .mdi.text-primary { color:#3DAA4A !important; }
  .subsec { border:1px solid var(--bs-border-color); border-radius:.5rem; }
  .subsec + .subsec { margin-top:.75rem; }
  .subsec-head { cursor:pointer; padding:.6rem .9rem; display:flex; align-items:center; gap:.5rem; }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- ================================================================ --}}
  {{-- HERO — identidade (nome de exibição) + Senha (modal) + foto        --}}
  {{-- ================================================================ --}}
  <div class="card mb-4 text-white border-0"
       style="background: linear-gradient(105deg, #237030 0%, #3DAA4A 50%, #56cf66 100%);">
    <div class="card-body py-4 px-4 position-relative overflow-hidden">
      <div class="position-absolute rounded-circle"
           style="right:-60px; top:-60px; width:200px; height:200px; border:40px solid rgba(255,255,255,0.07); pointer-events:none;"></div>

      <div class="d-flex flex-wrap align-items-center gap-3 position-relative">
        <div class="flex-shrink-0 position-relative">
          @if($temFoto)
            <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" class="rounded-circle"
                 style="width:76px; height:76px; object-fit:cover; border:3px solid rgba(255,255,255,.7);">
          @else
            <span class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold"
                  style="width:76px; height:76px; background:rgba(255,255,255,0.2); font-size:1.7rem;">{{ $iniciais }}</span>
          @endif
          <button type="button" class="rounded-circle position-absolute d-flex align-items-center justify-content-center p-0"
                  data-bs-toggle="modal" data-bs-target="#modal-foto"
                  style="width:28px; height:28px; bottom:0; right:0; background:#fff; color:#237030; border:2px solid #3DAA4A;"
                  title="Alterar foto">
            <i class="mdi mdi-camera-outline" style="font-size:14px;"></i>
          </button>
        </div>
        <div class="flex-grow-1 min-width-0">
          <h4 class="mb-1 fw-bold text-white">{{ $displayName }}</h4>
          {{-- Nome de registro só quando há nome social (senão seria duplicata do nome de exibição).
               Guardado por $paciente — operadores (admin/coord/recep) não têm perfil de paciente. --}}
          @if($paciente && trim($paciente->nome_social ?? '') !== '')
          <p class="mb-0 mt-1 small" style="opacity:.9;">{{ $paciente->nome }}</p>
          @endif
          <span class="badge bg-white text-success fw-semibold">{{ $roleLabel }}</span>
          <p class="mb-0 mt-1 small" style="opacity:.9;">
            <i class="mdi mdi-email-outline me-1"></i>{{ $user->email }}
            <span class="d-block d-sm-inline mt-1 mt-sm-0"><span class="d-none d-sm-inline mx-2">·</span><i class="mdi mdi-calendar-account-outline me-1"></i>conta desde {{ optional($user->created_at)->format('m/Y') ?? '—' }}</span>
          </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <button type="button" class="btn btn-sm fw-semibold"
                  style="background:rgba(255,255,255,.12); color:#fff; border:1px solid rgba(255,255,255,.6);"
                  data-bs-toggle="modal" data-bs-target="#modal-senha">
            <i class="mdi mdi-lock-outline me-1"></i>Alterar senha
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Flash --}}
  @if(session('success'))
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    <i class="mdi mdi-check-circle-outline me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  {{-- ================================================================ --}}
  {{-- MEUS DADOS — por role. Cada seção salva individualmente (AJAX).   --}}
  {{-- ================================================================ --}}
  @if($paciente)
    <div class="mb-3">
      <h5 class="mb-0"><i class="mdi mdi-clipboard-account-outline me-1 text-primary"></i>Meus Dados</h5>
      <p class="text-muted small mb-0">Cada seção é salva separadamente. Toque para abrir e editar.</p>
    </div>

    {{-- 1) Sobre você --}}
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header d-flex align-items-center gap-2 collapse-toggle collapsed" role="button"
           data-bs-toggle="collapse" data-bs-target="#sec-sobre" aria-expanded="false" aria-controls="sec-sobre">
        <i class="mdi mdi-account-circle-outline text-primary"></i>
        <div class="flex-grow-1"><h6 class="mb-0">Sobre você</h6><small class="text-muted">Como você quer ser chamado(a) e alguns dados pessoais.</small></div>
        <i class="mdi mdi-chevron-down chevron"></i>
      </div>
      <div id="sec-sobre" class="collapse">
        <form class="js-secao" method="POST" action="/meus-dados">
          @csrf @method('PUT')
          <input type="hidden" name="secao" value="sobre">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nome social <span class="fld-help" data-help="É o nome pelo qual você prefere ser chamado(a) e que aparece pra você aqui no sistema. Não altera seus documentos.">?</span></label>
                <input type="text" name="nome_social" class="form-control" maxlength="255" value="{{ old('nome_social', $paciente->nome_social) }}">
              </div>
              <div class="col-md-6">
                <label class="form-label small text-muted">Nome de registro</label>
                <input class="form-control" value="{{ $paciente->nome }}" readonly disabled>
                <div class="form-text"><i class="mdi mdi-lock-outline me-1"></i>Do seu cadastro, para corrigir, fale com a secretaria.</div>
              </div>
              <div class="col-8 col-md-4"><label class="form-label">Naturalidade — Cidade</label><input type="text" name="naturalidade_cidade" class="form-control" maxlength="100" value="{{ old('naturalidade_cidade', $paciente->naturalidade_cidade) }}"></div>
              <div class="col-4 col-md-2">
                <label class="form-label">UF</label>
                <select name="naturalidade_uf" class="form-select">
                  <option value="">—</option>
                  @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                    <option value="{{ $uf }}" {{ old('naturalidade_uf', $paciente->naturalidade_uf) === $uf ? 'selected' : '' }}>{{ $uf }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Raça/Cor</label>
                <select name="raca_cor" class="form-select">
                  <option value="">Prefiro não informar</option>
                  @foreach(['branca'=>'Branca','preta'=>'Preta','parda'=>'Parda','amarela'=>'Amarela','indigena'=>'Indígena','nao_declarado'=>'Prefiro não declarar'] as $val => $label)
                    <option value="{{ $val }}" {{ old('raca_cor', $paciente->raca_cor) === $val ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Estado civil</label>
                <select name="estado_civil" class="form-select">
                  <option value="">Não informar</option>
                  @foreach(['solteiro'=>'Solteiro(a)','casado'=>'Casado(a)','divorciado'=>'Divorciado(a)','viuvo'=>'Viúvo(a)','uniao_estavel'=>'União estável'] as $val => $label)
                    <option value="{{ $val }}" {{ old('estado_civil', $paciente->estado_civil) === $val ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6"><label class="form-label">Nome da mãe</label><input type="text" name="nome_mae" class="form-control" maxlength="255" value="{{ old('nome_mae', $paciente->nome_mae) }}"></div>
            </div>
            <div class="js-feedback mt-3"></div>
            <div class="d-flex justify-content-end mt-3"><button type="submit" class="btn btn-sm btn-primary"><i class="mdi mdi-content-save-outline me-1"></i>Salvar</button></div>
          </div>
        </form>
      </div>
    </div>

    {{-- 2) Endereço --}}
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header d-flex align-items-center gap-2 collapse-toggle collapsed" role="button"
           data-bs-toggle="collapse" data-bs-target="#sec-endereco" aria-expanded="false" aria-controls="sec-endereco">
        <i class="mdi mdi-map-marker-outline text-primary"></i>
        <div class="flex-grow-1"><h6 class="mb-0">Endereço</h6><small class="text-muted">Onde você mora — usamos no seu cadastro.</small></div>
        <i class="mdi mdi-chevron-down chevron"></i>
      </div>
      <div id="sec-endereco" class="collapse">
        <form class="js-secao" method="POST" action="/meus-dados">
          @csrf @method('PUT')
          <input type="hidden" name="secao" value="endereco">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-6 col-md-3">
                <label class="form-label">CEP <span class="fld-help" data-help="Digite o CEP que preenchemos o resto do endereço pra você.">?</span></label>
                <input type="text" name="cep" id="input-cep" class="form-control" maxlength="9" placeholder="00000-000" value="{{ old('cep', $paciente->cep) }}" oninput="mascararCep(this)" onblur="buscarCep()">
                <div id="cep-status" class="form-text"></div>
              </div>
              <div class="col-md-7"><label class="form-label">Logradouro</label><input type="text" name="logradouro" id="input-logradouro" class="form-control" value="{{ old('logradouro', $paciente->logradouro) }}"></div>
              <div class="col-6 col-md-2"><label class="form-label">Número</label><input type="text" name="numero" class="form-control" maxlength="20" value="{{ old('numero', $paciente->numero) }}"></div>
              <div class="col-md-4"><label class="form-label">Complemento</label><input type="text" name="complemento" class="form-control" maxlength="100" value="{{ old('complemento', $paciente->complemento) }}"></div>
              <div class="col-md-4"><label class="form-label">Bairro</label><input type="text" name="bairro" id="input-bairro" class="form-control" value="{{ old('bairro', $paciente->bairro) }}"></div>
              <div class="col-8 col-md-3"><label class="form-label">Cidade</label><input type="text" name="cidade" id="input-cidade" class="form-control" value="{{ old('cidade', $paciente->cidade) }}"></div>
              <div class="col-4 col-md-1">
                <label class="form-label">UF</label>
                <select name="uf" id="input-uf" class="form-select">
                  <option value="">—</option>
                  @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                    <option value="{{ $uf }}" {{ old('uf', $paciente->uf) === $uf ? 'selected' : '' }}>{{ $uf }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-12"><label class="form-label">Ponto de referência</label><input type="text" name="ponto_referencia" class="form-control" maxlength="255" value="{{ old('ponto_referencia', $paciente->ponto_referencia) }}"></div>
            </div>
            <div class="js-feedback mt-3"></div>
            <div class="d-flex justify-content-end mt-3"><button type="submit" class="btn btn-sm btn-primary"><i class="mdi mdi-content-save-outline me-1"></i>Salvar</button></div>
          </div>
        </form>
      </div>
    </div>

    {{-- 3) Contatos (unificado: pessoais + emergência, com collapse dentro de collapse) --}}
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header d-flex align-items-center gap-2 collapse-toggle collapsed" role="button"
           data-bs-toggle="collapse" data-bs-target="#sec-contatos" aria-expanded="false" aria-controls="sec-contatos">
        <i class="mdi mdi-phone-outline text-primary"></i>
        <div class="flex-grow-1"><h6 class="mb-0">Contatos</h6><small class="text-muted">Seus telefones e quem acionar em emergências.</small></div>
        <i class="mdi mdi-chevron-down chevron"></i>
      </div>
      <div id="sec-contatos" class="collapse">
        <form class="js-secao" method="POST" action="/meus-dados">
          @csrf @method('PUT')
          <input type="hidden" name="secao" value="contatos">
          <div class="card-body">

            {{-- Sub-seção: Pessoais --}}
            <div class="subsec">
              <div class="subsec-head collapse-toggle collapsed" role="button"
                   data-bs-toggle="collapse" data-bs-target="#sub-pessoais" aria-expanded="false" aria-controls="sub-pessoais">
                <i class="mdi mdi-cellphone text-primary"></i>
                <span class="fw-semibold flex-grow-1">Contatos pessoais</span>
                <i class="mdi mdi-chevron-down chevron"></i>
              </div>
              <div id="sub-pessoais" class="collapse">
                <div class="px-3 pb-3">
                  <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Telefone principal</label><input type="text" name="contato" class="form-control" maxlength="20" placeholder="(33) 99999-9999" value="{{ old('contato', $paciente->contato) }}" oninput="mascararTelefone(this)"></div>
                    <div class="col-md-4"><label class="form-label">Telefone alternativo</label><input type="text" name="telefone_alternativo" class="form-control" maxlength="20" value="{{ old('telefone_alternativo', $paciente->telefone_alternativo) }}" oninput="mascararTelefone(this)"></div>
                    <div class="col-md-4"><label class="form-label">E-mail alternativo <span class="fld-help" data-help="Um e-mail pessoal, diferente do institucional, para contato.">?</span></label><input type="email" name="email_alternativo" class="form-control" maxlength="255" value="{{ old('email_alternativo', $paciente->email_alternativo) }}"></div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Sub-seção: Emergência --}}
            <div class="subsec">
              <div class="subsec-head collapse-toggle collapsed" role="button"
                   data-bs-toggle="collapse" data-bs-target="#sub-emergencia" aria-expanded="false" aria-controls="sub-emergencia">
                <i class="mdi mdi-phone-alert-outline text-primary"></i>
                <span class="fw-semibold flex-grow-1">Emergência @if($isMenor)+ responsável @endif</span>
                <i class="mdi mdi-chevron-down chevron"></i>
              </div>
              <div id="sub-emergencia" class="collapse">
                <div class="px-3 pb-3">
                  <p class="fw-semibold small text-uppercase text-muted mb-2">Principal</p>
                  <div class="row g-3 mb-2">
                    <div class="col-md-5"><label class="form-label">Nome</label><input type="text" name="contato_emergencia_nome" class="form-control" maxlength="255" value="{{ old('contato_emergencia_nome', $paciente->contato_emergencia_nome) }}"></div>
                    <div class="col-md-3"><label class="form-label">Telefone</label><input type="text" name="contato_emergencia_telefone" class="form-control" maxlength="20" value="{{ old('contato_emergencia_telefone', $paciente->contato_emergencia_telefone) }}" oninput="mascararTelefone(this)"></div>
                    <div class="col-md-4">
                      <label class="form-label">Parentesco</label>
                      <select name="contato_emergencia_parentesco" class="form-select">
                        <option value="">Selecione</option>
                        @foreach(['pai'=>'Pai','mae'=>'Mãe','irmao'=>'Irmão/Irmã','tio'=>'Tio/Tia','avo'=>'Avô/Avó','conjuge'=>'Cônjuge/Companheiro(a)','amigo'=>'Amigo(a)','outro'=>'Outro'] as $val => $label)
                          <option value="{{ $val }}" {{ old('contato_emergencia_parentesco', $paciente->contato_emergencia_parentesco) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  @if($isMenor)
                  {{-- v0.10.5: checkbox junto do contato principal — usa este contato como responsável legal --}}
                  <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="resp-mesmo" onchange="toggleRespMesmo(this.checked)">
                    <label class="form-check-label small" for="resp-mesmo">Este contato principal também é o <strong>responsável legal</strong> do(a) menor (copiamos nome, telefone e parentesco — você só completa o e-mail abaixo).</label>
                  </div>
                  @endif
                  <p class="fw-semibold small text-uppercase text-muted mb-2 mt-3">Secundário <span class="fw-normal text-lowercase">(opcional)</span></p>
                  <div class="row g-3">
                    <div class="col-md-5"><label class="form-label">Nome</label><input type="text" name="contato_emergencia2_nome" class="form-control" maxlength="255" value="{{ old('contato_emergencia2_nome', $paciente->contato_emergencia2_nome) }}"></div>
                    <div class="col-md-3"><label class="form-label">Telefone</label><input type="text" name="contato_emergencia2_telefone" class="form-control" maxlength="20" value="{{ old('contato_emergencia2_telefone', $paciente->contato_emergencia2_telefone) }}" oninput="mascararTelefone(this)"></div>
                    <div class="col-md-4">
                      <label class="form-label">Parentesco</label>
                      <select name="contato_emergencia2_parentesco" class="form-select">
                        <option value="">Selecione</option>
                        @foreach(['pai'=>'Pai','mae'=>'Mãe','irmao'=>'Irmão/Irmã','tio'=>'Tio/Tia','avo'=>'Avô/Avó','conjuge'=>'Cônjuge/Companheiro(a)','amigo'=>'Amigo(a)','outro'=>'Outro'] as $val => $label)
                          <option value="{{ $val }}" {{ old('contato_emergencia2_parentesco', $paciente->contato_emergencia2_parentesco) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  @if($isMenor)
                  <p class="fw-semibold small text-uppercase text-muted mb-2 mt-3"><i class="mdi mdi-account-child me-1"></i>Responsável legal</p>
                  {{-- Contato do responsável — só aparece quando NÃO é o mesmo do contato principal (checkbox acima) --}}
                  <div class="row g-3" id="resp-contato">
                    <div class="col-md-5"><label class="form-label">Nome</label><input type="text" name="responsavel_nome" class="form-control" maxlength="255" value="{{ old('responsavel_nome', $paciente->responsavel_nome) }}"></div>
                    <div class="col-md-4">
                      <label class="form-label">Parentesco</label>
                      <select name="responsavel_parentesco" class="form-select">
                        <option value="">Selecione</option>
                        @foreach(['pai'=>'Pai','mae'=>'Mãe','irmao'=>'Irmão/Irmã','tio'=>'Tio/Tia','avo'=>'Avô/Avó','outro'=>'Outro'] as $val => $label)
                          <option value="{{ $val }}" {{ old('responsavel_parentesco', $paciente->responsavel_parentesco) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Telefone</label><input type="text" name="responsavel_telefone" class="form-control" maxlength="20" value="{{ old('responsavel_telefone', $paciente->responsavel_telefone) }}" oninput="mascararTelefone(this)"></div>
                  </div>
                  <div class="row g-3 mt-0">
                    <div class="col-md-6"><label class="form-label">E-mail do responsável</label><input type="email" name="responsavel_email" class="form-control" maxlength="255" value="{{ old('responsavel_email', $paciente->responsavel_email) }}"></div>
                  </div>
                  @endif
                </div>
              </div>
            </div>

            <div class="js-feedback mt-3"></div>
            <div class="d-flex justify-content-end mt-3"><button type="submit" class="btn btn-sm btn-primary"><i class="mdi mdi-content-save-outline me-1"></i>Salvar</button></div>
          </div>
        </form>
      </div>
    </div>

    {{-- 4) Saúde --}}
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header d-flex align-items-center gap-2 collapse-toggle collapsed" role="button"
           data-bs-toggle="collapse" data-bs-target="#sec-saude" aria-expanded="false" aria-controls="sec-saude">
        <i class="mdi mdi-heart-pulse text-primary"></i>
        <div class="flex-grow-1"><h6 class="mb-0">Dados de saúde</h6><small class="text-muted">O que você contar aqui ajuda o profissional a te atender melhor. Preencha o que souber.</small></div>
        <i class="mdi mdi-chevron-down chevron"></i>
      </div>
      <div id="sec-saude" class="collapse">
        <form class="js-secao" method="POST" action="/meus-dados">
          @csrf @method('PUT')
          <input type="hidden" name="secao" value="saude">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-6 col-md-4">
                <label class="form-label">Tipo sanguíneo <span class="fld-help" data-help="Importante em emergências e transfusões. Se não souber, escolha 'Não sei'.">?</span></label>
                <select name="tipo_sanguineo" class="form-select">
                  <option value="">Não sei / Não informar</option>
                  @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-','NS'] as $ts)
                    <option value="{{ $ts }}" {{ old('tipo_sanguineo', $paciente->tipo_sanguineo) === $ts ? 'selected' : '' }}>{{ $ts === 'NS' ? 'Não sei' : $ts }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-6 col-md-4"><label class="form-label">Peso (kg)</label><input type="number" name="peso_kg" class="form-control" step="0.1" min="1" max="300" value="{{ old('peso_kg', $paciente->peso_kg) }}"></div>
              <div class="col-6 col-md-4"><label class="form-label">Altura (cm)</label><input type="number" name="altura_cm" class="form-control" min="30" max="250" value="{{ old('altura_cm', $paciente->altura_cm) }}"></div>
              <div class="col-md-6"><label class="form-label">Alergias <span class="fld-help" data-help="Remédios, alimentos ou outras coisas que te fazem mal — e o que acontece, se souber.">?</span></label><textarea name="alergias" class="form-control" rows="3">{{ old('alergias', $paciente->alergias) }}</textarea></div>
              <div class="col-md-6"><label class="form-label">Medicamentos em uso <span class="fld-help" data-help="Remédios que você toma de forma contínua (nome e, se souber, a dose).">?</span></label><textarea name="medicamentos_uso_continuo" class="form-control" rows="3">{{ old('medicamentos_uso_continuo', $paciente->medicamentos_uso_continuo) }}</textarea></div>
              <div class="col-md-6"><label class="form-label">Condições crônicas <span class="fld-help" data-help="Doenças que você tem há bastante tempo: diabetes, asma, etc.">?</span></label><textarea name="condicoes_cronicas" class="form-control" rows="3">{{ old('condicoes_cronicas', $paciente->condicoes_cronicas) }}</textarea></div>
              <div class="col-md-6"><label class="form-label">Cirurgias prévias <span class="fld-help" data-help="Cirurgias que você já fez e, se lembrar, o ano.">?</span></label><textarea name="cirurgias_previas" class="form-control" rows="3">{{ old('cirurgias_previas', $paciente->cirurgias_previas) }}</textarea></div>
              @unless($isMenor)
              <div class="col-md-4">
                <label class="form-label">Tabagismo</label>
                <select name="tabagismo" class="form-select">
                  <option value="">Não informar</option>
                  @foreach(['nao'=>'Não fumo','ex_fumante'=>'Ex-fumante','sim'=>'Fumante atual'] as $val => $label)
                    <option value="{{ $val }}" {{ old('tabagismo', $paciente->tabagismo) === $val ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Etilismo</label>
                <select name="etilismo" class="form-select">
                  <option value="">Não informar</option>
                  @foreach(['nao'=>'Não bebo','ocasional'=>'Ocasional','frequente'=>'Frequente'] as $val => $label)
                    <option value="{{ $val }}" {{ old('etilismo', $paciente->etilismo) === $val ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Atividade física</label>
                <select name="atividade_fisica" class="form-select">
                  <option value="">Não informar</option>
                  @foreach(['sedentario'=>'Sedentário','leve'=>'1–2x por semana','moderada'=>'3+ vezes por semana'] as $val => $label)
                    <option value="{{ $val }}" {{ old('atividade_fisica', $paciente->atividade_fisica) === $val ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
              @endunless
            </div>
            <div class="js-feedback mt-3"></div>
            <div class="d-flex justify-content-end mt-3"><button type="submit" class="btn btn-sm btn-primary"><i class="mdi mdi-content-save-outline me-1"></i>Salvar</button></div>
          </div>
        </form>
      </div>
    </div>

  @elseif($profissional)
    <h5 class="mb-3"><i class="mdi mdi-doctor me-1 text-primary"></i>Meus Dados profissionais</h5>
    <form method="POST" action="/meus-dados">
      @csrf @method('PUT')
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label small text-muted">Nome</label><input class="form-control" value="{{ $profissional->nome }}" readonly disabled></div>
            <div class="col-md-6"><label class="form-label small text-muted">Especialidade</label><input class="form-control" value="{{ $profissional->especialidade }}" readonly disabled></div>
            <div class="col-md-6"><label class="form-label">Contato</label><input type="text" name="contato" class="form-control" maxlength="20" value="{{ old('contato', $profissional->contato) }}"></div>
            <div class="col-md-6"><label class="form-label">Registro profissional</label><input type="text" name="registro_profissional" class="form-control" maxlength="100" value="{{ old('registro_profissional', $profissional->registro_profissional) }}"></div>
          </div>
          <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save-outline me-1"></i>Salvar</button>
          </div>
        </div>
      </div>
    </form>
  @endif

</div>

{{-- ================================================================ --}}
{{-- MODAIS — Senha e Foto                                            --}}
{{-- ================================================================ --}}
<div class="modal fade" id="modal-senha" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="/perfil" class="modal-content">
      @csrf @method('PUT')
      <div class="modal-header">
        <h5 class="modal-title"><i class="mdi mdi-lock-outline me-2"></i>Alterar senha</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label" for="m-current">Senha atual</label>
          <input type="password" id="m-current" name="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password">
          @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label class="form-label" for="m-new">Nova senha</label>
          <input type="password" id="m-new" name="new_password" class="form-control @error('new_password') is-invalid @enderror" autocomplete="new-password">
          @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-1">
          <label class="form-label" for="m-confirm">Confirmar nova senha</label>
          <input type="password" id="m-confirm" name="new_password_confirmation" class="form-control" autocomplete="new-password">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-warning"><i class="mdi mdi-lock-reset me-1"></i>Alterar senha</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="modal-foto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="mdi mdi-camera-outline me-2"></i>Foto de perfil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="/perfil/avatar" enctype="multipart/form-data">
          @csrf
          <label class="form-label" for="m-avatar">Selecione uma imagem</label>
          <input type="file" id="m-avatar" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp">
          @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
          <div class="form-text mb-3">JPG, PNG ou WebP · máx. 2 MB</div>
          <button type="submit" class="btn btn-primary w-100"><i class="mdi mdi-camera-outline me-1"></i>Enviar foto</button>
        </form>
        @if($user->avatar)
        <form method="POST" action="/perfil/avatar/remover" class="mt-2">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Remover foto de perfil?')">
            <i class="mdi mdi-delete-outline me-1"></i>Remover foto atual
          </button>
        </form>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script>
function mascararCep(i){let v=i.value.replace(/\D/g,'').slice(0,8);if(v.length>5)v=v.slice(0,5)+'-'+v.slice(5);i.value=v;}
function mascararTelefone(i){let v=i.value.replace(/\D/g,'').slice(0,11);if(v.length>6)v='('+v.slice(0,2)+') '+v.slice(2,7)+'-'+v.slice(7);else if(v.length>2)v='('+v.slice(0,2)+') '+v.slice(2);i.value=v;}
function buscarCep(){
  var el=document.getElementById('input-cep'); if(!el) return;
  var cep=el.value.replace(/\D/g,''); var s=document.getElementById('cep-status');
  if(cep.length!==8) return;
  s.textContent='Buscando CEP...'; s.className='form-text text-muted';
  var c=new AbortController(); var t=setTimeout(function(){c.abort();},5000);
  fetch('https://viacep.com.br/ws/'+cep+'/json/',{signal:c.signal}).then(function(r){return r.json();}).then(function(d){
    clearTimeout(t); if(d.erro) throw new Error();
    document.getElementById('input-logradouro').value=d.logradouro||'';
    document.getElementById('input-bairro').value=d.bairro||'';
    document.getElementById('input-cidade').value=d.localidade||'';
    var uf=document.getElementById('input-uf'); if(uf) Array.from(uf.options).forEach(function(o){o.selected=o.value===d.uf;});
    s.textContent='Endereço preenchido.'; s.className='form-text text-success';
  }).catch(function(){clearTimeout(t); s.textContent='CEP não encontrado. Preencha manualmente.'; s.className='form-text text-warning';});
}

// Balão de ajuda "?" (clique)
(function(){
  var aberto=null;
  document.addEventListener('click', function(e){
    if(aberto){ aberto.remove(); aberto=null; }
    var h=e.target.closest('.fld-help'); if(!h) return;
    e.preventDefault(); e.stopPropagation();
    var p=document.createElement('div'); p.className='fld-help-pop'; p.textContent=h.dataset.help;
    document.body.appendChild(p);
    var r=h.getBoundingClientRect();
    var left=Math.min(window.scrollX+r.left-4, window.scrollX+window.innerWidth-280);
    p.style.top=(window.scrollY+r.bottom+6)+'px'; p.style.left=Math.max(8,left)+'px';
    aberto=p;
  });
})();

// Responsável = mesmo do contato de emergência (menores): copia nome/telefone/parentesco
function copiaResp(){
  var g=function(n){return document.querySelector('[name="'+n+'"]');};
  if(g('responsavel_nome'))     g('responsavel_nome').value     = (g('contato_emergencia_nome')||{}).value || '';
  if(g('responsavel_telefone')) g('responsavel_telefone').value = (g('contato_emergencia_telefone')||{}).value || '';
  var rp=g('responsavel_parentesco'); var pv=(g('contato_emergencia_parentesco')||{}).value || '';
  if(rp && Array.prototype.some.call(rp.options, function(o){return o.value===pv;})) rp.value=pv;
}
// Marcado: esconde o contato do responsável (fica igual ao principal, copiado nos bastidores).
// Desmarcado: mostra os campos para preencher um responsável diferente.
function toggleRespMesmo(checked){
  var cont=document.getElementById('resp-contato');
  if(cont){ cont.style.display = checked ? 'none' : ''; }
  if(checked){ copiaResp(); }
}
// Mantém o responsável sincronizado enquanto o checkbox estiver marcado
['contato_emergencia_nome','contato_emergencia_telefone','contato_emergencia_parentesco'].forEach(function(n){
  var el=document.querySelector('[name="'+n+'"]'); if(!el) return;
  var sync=function(){ var cb=document.getElementById('resp-mesmo'); if(cb && cb.checked){ copiaResp(); } };
  el.addEventListener('input', sync); el.addEventListener('change', sync);
});
// Inferência no load: se o responsável já é igual ao contato principal, marca o checkbox e esconde os campos
(function(){
  var cb=document.getElementById('resp-mesmo'); if(!cb) return;
  var g=function(n){ var e=document.querySelector('[name="'+n+'"]'); return e?(e.value||'').trim():''; };
  var same = g('responsavel_nome')!=='' &&
             g('responsavel_nome')===g('contato_emergencia_nome') &&
             g('responsavel_telefone')===g('contato_emergencia_telefone') &&
             g('responsavel_parentesco')===g('contato_emergencia_parentesco');
  cb.checked = same;
  toggleRespMesmo(same);
})();

// Salvar cada seção de Meus Dados via AJAX (feedback inline, sem recarregar)
(function(){
  document.querySelectorAll('form.js-secao').forEach(function(f){
    f.addEventListener('submit', function(e){
      e.preventDefault();
      var btn=f.querySelector('button[type="submit"]');
      var fb=f.querySelector('.js-feedback');
      if(btn){ btn.disabled=true; }
      if(fb){ fb.innerHTML=''; }
      fetch('/meus-dados', {
        method:'POST',
        headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
        body:new FormData(f)
      }).then(function(r){ return r.json().then(function(j){ return {s:r.status, j:j}; }); })
        .then(function(res){
          if(btn){ btn.disabled=false; }
          if(!fb) return;
          if(res.s===200 && res.j.ok){
            fb.innerHTML='<div class="alert alert-success py-2 mb-0"><i class="mdi mdi-check-circle-outline me-1"></i>Salvo!</div>';
            // v0.10.5: soft refresh — recarrega a página para o hero (nome de exibição) e os dados
            // refletirem na hora o que foi salvo.
            setTimeout(function(){ window.location.reload(); }, 700);
          } else if(res.s===422){
            var errs=res.j.errors||{}; var li='';
            Object.keys(errs).forEach(function(k){ li+='<li>'+errs[k][0]+'</li>'; });
            fb.innerHTML='<div class="alert alert-danger py-2 mb-0"><ul class="mb-0 ps-3">'+li+'</ul></div>';
          } else {
            fb.innerHTML='<div class="alert alert-danger py-2 mb-0">Não foi possível salvar. Tente novamente.</div>';
          }
        }).catch(function(){
          if(btn){ btn.disabled=false; }
          if(fb){ fb.innerHTML='<div class="alert alert-danger py-2 mb-0">Erro de conexão.</div>'; }
        });
    });
  });
})();

// Reabre o modal certo se a validação (senha/foto) falhar no reload
document.addEventListener('DOMContentLoaded', function(){
  @if($errors->has('current_password') || $errors->has('new_password'))
    new bootstrap.Modal(document.getElementById('modal-senha')).show();
  @elseif($errors->has('avatar'))
    new bootstrap.Modal(document.getElementById('modal-foto')).show();
  @endif
});
</script>
@endsection
