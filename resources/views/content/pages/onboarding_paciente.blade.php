{{-- Wizard de Onboarding — Paciente (ST-15) --}}
{{-- HTML autocontido (sem @extends) — mesma arquitetura do consentimento.blade.php --}}
@php
    $tema = (isset($_COOKIE['style']) && in_array($_COOKIE['style'], ['light','dark','system'], true))
        ? $_COOKIE['style'] : 'light';
    $tema = $tema === 'dark' ? 'dark' : 'light';
    // Calcula menoridade no servidor para uso inicial no JS
    $isMenorServidor = $paciente && $paciente->data_nascimento->age < 18;
    $dataNascStr = $paciente ? $paciente->data_nascimento->format('Y-m-d') : '';
@endphp
<!DOCTYPE html>
<html lang="pt-BR" class="{{ $tema }}-style">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Completar Cadastro | Prontu IF</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/materialdesignicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/prontuif-theme.css') }}" />
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            background: rgba(0,0,0,.55);
            backdrop-filter: blur(4px);
            padding: 1.5rem 1rem;
        }
        .onb-card {
            width: 100%;
            max-width: 820px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.35);
        }
        .onb-header {
            background: linear-gradient(135deg, #3DAA4A 0%, #1C6B2A 100%);
            padding: 1.5rem 2rem 1rem;
        }
        .onb-body { padding: 1.75rem 2rem; }
        .onb-footer { padding: 1rem 2rem 1.5rem; border-top: 1px solid var(--bs-border-color); }

        /* Dots de progresso */
        .wizard-dots { display: flex; gap: .4rem; margin-top: .75rem; }
        .w-dot {
            flex: 1; height: 4px; border-radius: 2px;
            background: rgba(255,255,255,.35); transition: background .3s;
        }
        .w-dot.active  { background: #fff; }
        .w-dot.done    { background: rgba(255,255,255,.7); }

        /* Bloco de seção */
        .onb-section { border-left: 3px solid #3DAA4A; padding-left: 1rem; margin-bottom: 1.5rem; }
        .onb-section-title { font-size: .75rem; text-transform: uppercase; letter-spacing: .07em; color: #3DAA4A; font-weight: 600; margin-bottom: .75rem; }

        /* Campo leitura apenas */
        .field-readonly { background: var(--bs-secondary-bg, #f5f5f5) !important; cursor: default; }

        /* C.0 (v0.10.3): balão de ajuda "?" por clique (touch-friendly, sem depender do Bootstrap JS) */
        .onb-help {
            display: inline-flex; align-items: center; justify-content: center;
            width: 16px; height: 16px; margin-left: .35rem; border-radius: 50%;
            background: #3DAA4A; color: #fff; font-size: 11px; font-weight: 700;
            cursor: pointer; user-select: none; line-height: 1;
        }
        .onb-help-pop {
            position: absolute; z-index: 2000; max-width: 260px;
            background: #212121; color: #fff; padding: .5rem .7rem; border-radius: 6px;
            font-size: 12px; line-height: 1.4; box-shadow: 0 6px 20px rgba(0,0,0,.3);
        }

        /* ============================================================ */
        /* Mobile-first do wizard (foco paciente) — telas ≤ 576px       */
        /* ============================================================ */
        @media (max-width: 576px) {
            /* Sem fundo escuro/blur ocupando espaço; wizard usa a tela inteira */
            body { padding: 0; background: #fff; align-items: stretch; }
            .onb-card { border-radius: 0; box-shadow: none; min-height: 100vh; }

            /* Paddings enxutos para não estourar a largura */
            .onb-header { padding: 1.25rem 1rem 1rem; }
            .onb-body   { padding: 1.25rem 1rem; }
            .onb-footer { padding: 1rem; }

            /* Header: título e badge empilham em vez de disputar a linha */
            .onb-header > .d-flex { flex-wrap: wrap; gap: .5rem; }
            #lbl-step { align-self: flex-start; }

            /* Seções: menos recuo lateral */
            .onb-section { padding-left: .75rem; }

            /* Footer: botões full-width e empilhados (ação principal no topo) */
            .onb-footer { flex-direction: column-reverse; gap: .5rem; }
            .onb-footer > .ms-auto { margin-left: 0 !important; width: 100%; }
            .onb-footer .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

<div class="onb-card card">

    {{-- Cabeçalho --}}
    <div class="onb-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h5 class="text-white fw-bold mb-0">
                    <i class="mdi mdi-clipboard-account-outline me-2"></i>Completar Cadastro
                </h5>
                <p class="text-white-50 mb-0 small mt-1">
                    Olá, <strong class="text-white">{{ $paciente->nome ?? $user->name }}</strong>!
                    Precisamos de mais algumas informações para liberar seu acesso.
                </p>
            </div>
            <span id="lbl-step" class="badge bg-white text-success fw-semibold px-3 py-2" style="font-size:.85rem;">
                Passo 1 de 7
            </span>
        </div>
        <div class="wizard-dots mt-2">
            @for($i = 0; $i < 7; $i++)
                <div class="w-dot {{ $i === 0 ? 'active' : '' }}" id="dot-{{ $i }}"></div>
            @endfor
        </div>
    </div>

    {{-- Erros de validação --}}
    @if($errors->any())
    <div class="alert alert-danger m-3 mb-0">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Formulário único — submit só no passo 6 --}}
    <form id="form-onboarding" method="POST" action="/onboarding/salvar/paciente" novalidate>
        @csrf

    {{-- ================================================================ --}}
    {{-- PASSO 1 (step-0) — Identidade                                    --}}
    {{-- ================================================================ --}}
    <div id="step-0" class="onb-body">
        <h6 class="fw-semibold mb-3">Sua identidade</h6>
        <p class="text-muted small mb-4">
            Confira seus dados. O <strong>nome</strong>, o <strong>e-mail</strong>, a <strong>matrícula</strong> e o <strong>curso</strong>
            são definidos pela instituição e não podem ser alterados aqui.
        </p>

        <div class="onb-section">
            <p class="onb-section-title">Identidade</p>
            <div class="row g-3">
                <div class="col-md-6">
                    {{-- Nome é imutável (definido pela instituição) — read-only, mas ainda é enviado no POST --}}
                    <label class="form-label">Nome completo</label>
                    <input type="text" name="nome" class="form-control field-readonly" readonly
                           value="{{ old('nome', $paciente->nome ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nome social</label>
                    <input type="text" name="nome_social" class="form-control" maxlength="255"
                           value="{{ old('nome_social', $paciente->nome_social ?? '') }}">
                    <div class="form-text">Nome pelo qual você prefere ser chamado(a) — é o que aparece pra você no sistema.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">CPF / Documento <span class="text-danger">*</span></label>
                    <input type="text" name="documento" class="form-control" required
                           value="{{ old('documento', $paciente->documento ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Data de nascimento <span class="text-danger">*</span></label>
                    <input type="date" name="data_nascimento" id="input-data-nasc" class="form-control" required
                           value="{{ old('data_nascimento', $dataNascStr) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sexo <span class="text-danger">*</span></label>
                    <select name="sexo" class="form-select" required>
                        <option value="">Selecione</option>
                        @foreach(['M' => 'Masculino', 'F' => 'Feminino', 'outro' => 'Outro'] as $val => $label)
                            <option value="{{ $val }}" {{ old('sexo', $paciente->sexo ?? '') === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="onb-section">
            <p class="onb-section-title">Dados institucionais</p>
            <p class="text-muted small mb-3"><i class="mdi mdi-information-outline me-1"></i>Se algum destes dados estiver errado, procure a secretaria.</p>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">E-mail institucional</label>
                    <input type="text" class="form-control field-readonly" readonly value="{{ $user->email }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Matrícula</label>
                    <input type="text" class="form-control field-readonly" readonly value="{{ $paciente->matricula ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Curso</label>
                    <input type="text" class="form-control field-readonly" readonly value="{{ $paciente->curso ?? '' }}">
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- PASSO 3 (step-2) — Endereço                                      --}}
    {{-- ================================================================ --}}
    <div id="step-2" class="onb-body" style="display:none;">
        <h6 class="fw-semibold mb-1">Endereço</h6>
        <p class="text-muted small mb-4">Digite o CEP para preenchimento automático, ou preencha os campos manualmente.</p>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">CEP <span class="text-danger">*</span></label>
                <input type="text" name="cep" id="input-cep" class="form-control" required maxlength="9"
                       placeholder="00000-000" value="{{ old('cep', $paciente->cep ?? '') }}"
                       oninput="mascararCep(this)" onblur="buscarCep()">
                <div id="cep-status" class="form-text"></div>
            </div>
            <div class="col-md-7">
                <label class="form-label">Logradouro <span class="text-danger">*</span></label>
                <input type="text" name="logradouro" id="input-logradouro" class="form-control" required
                       value="{{ old('logradouro', $paciente->logradouro ?? '') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Número <span class="text-danger">*</span></label>
                <input type="text" name="numero" id="input-numero" class="form-control" required maxlength="20"
                       value="{{ old('numero', $paciente->numero ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Complemento</label>
                <input type="text" name="complemento" class="form-control" maxlength="100"
                       value="{{ old('complemento', $paciente->complemento ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Bairro <span class="text-danger">*</span></label>
                <input type="text" name="bairro" id="input-bairro" class="form-control" required
                       value="{{ old('bairro', $paciente->bairro ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Cidade <span class="text-danger">*</span></label>
                <input type="text" name="cidade" id="input-cidade" class="form-control" required
                       value="{{ old('cidade', $paciente->cidade ?? '') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">UF <span class="text-danger">*</span></label>
                <select name="uf" id="input-uf" class="form-select" required>
                    <option value="">UF</option>
                    @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                        <option value="{{ $uf }}" {{ old('uf', $paciente->uf ?? '') === $uf ? 'selected' : '' }}>{{ $uf }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-10">
                <label class="form-label">Ponto de referência</label>
                <input type="text" name="ponto_referencia" class="form-control" maxlength="255"
                       value="{{ old('ponto_referencia', $paciente->ponto_referencia ?? '') }}">
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- PASSO 4 (step-3) — Contatos pessoais                             --}}
    {{-- ================================================================ --}}
    <div id="step-3" class="onb-body" style="display:none;">
        <h6 class="fw-semibold mb-1">Contatos pessoais</h6>
        <p class="text-muted small mb-4">Telefone principal é obrigatório para que a equipe de saúde possa entrar em contato.</p>

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Telefone principal <span class="text-danger">*</span></label>
                <input type="text" name="contato" class="form-control" required maxlength="20"
                       placeholder="(33) 99999-9999"
                       value="{{ old('contato', $paciente->contato ?? '') }}"
                       oninput="mascararTelefone(this)">
            </div>
            <div class="col-md-4">
                <label class="form-label">Telefone alternativo</label>
                <input type="text" name="telefone_alternativo" class="form-control" maxlength="20"
                       placeholder="(33) 99999-9999"
                       value="{{ old('telefone_alternativo', $paciente->telefone_alternativo ?? '') }}"
                       oninput="mascararTelefone(this)">
            </div>
            <div class="col-md-4">
                <label class="form-label">E-mail alternativo</label>
                <input type="email" name="email_alternativo" class="form-control" maxlength="255"
                       placeholder="outro@email.com"
                       value="{{ old('email_alternativo', $paciente->email_alternativo ?? '') }}">
                <div class="form-text">Não pode ser igual ao e-mail institucional.</div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- PASSO 2 (step-1) — Dados complementares                          --}}
    {{-- ================================================================ --}}
    <div id="step-1" class="onb-body" style="display:none;">
        <h6 class="fw-semibold mb-1">Dados complementares</h6>
        <p class="text-muted small mb-4">Todos os campos são opcionais. Ajudam na identificação e no atendimento.</p>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Naturalidade — Cidade</label>
                <input type="text" name="naturalidade_cidade" class="form-control" maxlength="100"
                       value="{{ old('naturalidade_cidade', $paciente->naturalidade_cidade ?? '') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">UF</label>
                <select name="naturalidade_uf" class="form-select">
                    <option value="">—</option>
                    @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                        <option value="{{ $uf }}" {{ old('naturalidade_uf', $paciente->naturalidade_uf ?? '') === $uf ? 'selected' : '' }}>{{ $uf }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Raça/Cor</label>
                <select name="raca_cor" class="form-select">
                    <option value="">Prefiro não informar</option>
                    @foreach(['branca'=>'Branca','preta'=>'Preta','parda'=>'Parda','amarela'=>'Amarela','indigena'=>'Indígena','nao_declarado'=>'Prefiro não declarar'] as $val => $label)
                        <option value="{{ $val }}" {{ old('raca_cor', $paciente->raca_cor ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Estado civil</label>
                <select name="estado_civil" class="form-select">
                    <option value="">Não informar</option>
                    @foreach(['solteiro'=>'Solteiro(a)','casado'=>'Casado(a)','divorciado'=>'Divorciado(a)','viuvo'=>'Viúvo(a)','uniao_estavel'=>'União estável'] as $val => $label)
                        <option value="{{ $val }}" {{ old('estado_civil', $paciente->estado_civil ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nome da mãe</label>
                <input type="text" name="nome_mae" class="form-control" maxlength="255"
                       value="{{ old('nome_mae', $paciente->nome_mae ?? '') }}">
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- PASSO 4 — Contatos de emergência + Responsável legal              --}}
    {{-- ================================================================ --}}
    <div id="step-4" class="onb-body" style="display:none;">
        <h6 class="fw-semibold mb-1">Contatos de emergência</h6>
        <p class="text-muted small mb-4">Em caso de emergência, esses contatos serão acionados pelo setor de saúde.</p>

        {{-- Bloco A — Emergência primário (obrigatório) --}}
        <div class="onb-section">
            <p class="onb-section-title"><i class="mdi mdi-phone-alert me-1"></i>Contato de emergência principal <span class="text-danger">*</span></p>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Nome completo <span class="text-danger">*</span></label>
                    <input type="text" name="contato_emergencia_nome" class="form-control" required maxlength="255"
                           value="{{ old('contato_emergencia_nome', $paciente->contato_emergencia_nome ?? '') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Telefone <span class="text-danger">*</span></label>
                    <input type="text" name="contato_emergencia_telefone" class="form-control" required maxlength="20"
                           placeholder="(33) 99999-9999"
                           value="{{ old('contato_emergencia_telefone', $paciente->contato_emergencia_telefone ?? '') }}"
                           oninput="mascararTelefone(this)">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Parentesco <span class="text-danger">*</span></label>
                    <select name="contato_emergencia_parentesco" class="form-select" required>
                        <option value="">Selecione</option>
                        @foreach(['pai'=>'Pai','mae'=>'Mãe','irmao'=>'Irmão/Irmã','tio'=>'Tio/Tia','avo'=>'Avô/Avó','conjuge'=>'Cônjuge/Companheiro(a)','amigo'=>'Amigo(a)','outro'=>'Outro'] as $val => $label)
                            <option value="{{ $val }}" {{ old('contato_emergencia_parentesco', $paciente->contato_emergencia_parentesco ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Bloco B — Emergência secundário (opcional) --}}
        <div class="onb-section">
            <p class="onb-section-title"><i class="mdi mdi-phone-plus me-1"></i>Segundo contato de emergência <span class="text-muted fw-normal">(opcional)</span></p>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Nome completo</label>
                    <input type="text" name="contato_emergencia2_nome" class="form-control" maxlength="255"
                           value="{{ old('contato_emergencia2_nome', $paciente->contato_emergencia2_nome ?? '') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="contato_emergencia2_telefone" class="form-control" maxlength="20"
                           placeholder="(33) 99999-9999"
                           value="{{ old('contato_emergencia2_telefone', $paciente->contato_emergencia2_telefone ?? '') }}"
                           oninput="mascararTelefone(this)">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Parentesco</label>
                    <select name="contato_emergencia2_parentesco" class="form-select">
                        <option value="">Selecione</option>
                        @foreach(['pai'=>'Pai','mae'=>'Mãe','irmao'=>'Irmão/Irmã','tio'=>'Tio/Tia','avo'=>'Avô/Avó','conjuge'=>'Cônjuge/Companheiro(a)','amigo'=>'Amigo(a)','outro'=>'Outro'] as $val => $label)
                            <option value="{{ $val }}" {{ old('contato_emergencia2_parentesco', $paciente->contato_emergencia2_parentesco ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Bloco C — Responsável legal (condicional: idade < 18) --}}
        <div id="bloco-responsavel" class="onb-section {{ $isMenorServidor ? '' : 'd-none' }}">
            <p class="onb-section-title"><i class="mdi mdi-account-child me-1"></i>Responsável legal <span class="text-danger">*</span></p>
            <p class="text-muted small mb-3">Obrigatório para pacientes menores de 18 anos.</p>
            {{-- Alinhado ao /perfil: "mesmo do contato de emergência principal" copia nome/telefone/parentesco
                 e mantém CPF + e-mail editáveis (campos legais próprios do responsável). --}}
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="resp-mesmo" onchange="toggleRespMesmo(this.checked)">
              <label class="form-check-label small" for="resp-mesmo">O responsável é a mesma pessoa do contato de emergência principal (copiamos nome, telefone e parentesco — você só completa o e-mail abaixo).</label>
            </div>
            {{-- Contato do responsável — some quando o checkbox está marcado (= contato principal) --}}
            <div class="row g-3" id="resp-contato">
                <div class="col-md-5">
                    <label class="form-label">Nome completo <span class="text-danger">*</span></label>
                    <input type="text" name="responsavel_nome" class="form-control" maxlength="255"
                           value="{{ old('responsavel_nome', $paciente->responsavel_nome ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Parentesco <span class="text-danger">*</span></label>
                    <select name="responsavel_parentesco" class="form-select">
                        <option value="">Selecione</option>
                        @foreach(['pai'=>'Pai','mae'=>'Mãe','irmao'=>'Irmão/Irmã','tio'=>'Tio/Tia','avo'=>'Avô/Avó','outro'=>'Outro'] as $val => $label)
                            <option value="{{ $val }}" {{ old('responsavel_parentesco', $paciente->responsavel_parentesco ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Telefone <span class="text-danger">*</span></label>
                    <input type="text" name="responsavel_telefone" class="form-control" maxlength="20"
                           placeholder="(33) 99999-9999"
                           value="{{ old('responsavel_telefone', $paciente->responsavel_telefone ?? '') }}"
                           oninput="mascararTelefone(this)">
                </div>
            </div>
            <div class="row g-3 mt-0">
                <div class="col-md-6">
                    <label class="form-label">E-mail do responsável <span class="text-danger">*</span></label>
                    <input type="email" name="responsavel_email" class="form-control" maxlength="255"
                           value="{{ old('responsavel_email', $paciente->responsavel_email ?? '') }}">
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- PASSO 5 — Dados de saúde (opcionais)                              --}}
    {{-- ================================================================ --}}
    <div id="step-5" class="onb-body" style="display:none;">
        <h6 class="fw-semibold mb-1">Dados de saúde</h6>
        <div class="alert alert-info d-flex gap-2 py-2 mb-4" role="alert">
            <i class="mdi mdi-information-outline mt-1 flex-shrink-0"></i>
            <span class="small">Todos os campos são opcionais. Podem ser completados pelo profissional de saúde na sua primeira consulta.</span>
        </div>

        <div class="onb-section">
            <p class="onb-section-title">Dados vitais</p>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tipo sanguíneo</label>
                    <select name="tipo_sanguineo" class="form-select">
                        <option value="">Não sei / Não informar</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-','NS'] as $ts)
                            <option value="{{ $ts }}" {{ old('tipo_sanguineo', $paciente->tipo_sanguineo ?? '') === $ts ? 'selected' : '' }}>
                                {{ $ts === 'NS' ? 'Não sei' : $ts }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Peso (kg)</label>
                    <input type="number" name="peso_kg" class="form-control" step="0.1" min="1" max="300"
                           value="{{ old('peso_kg', $paciente->peso_kg ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Altura (cm)</label>
                    <input type="number" name="altura_cm" class="form-control" min="30" max="250"
                           value="{{ old('altura_cm', $paciente->altura_cm ?? '') }}">
                </div>
            </div>
        </div>

        <div class="onb-section">
            <p class="onb-section-title">Histórico clínico</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Alergias conhecidas</label>
                    <textarea name="alergias" class="form-control" rows="3"
                              placeholder="Medicamentos, alimentos, picadas... Descreva a reação se souber.">{{ old('alergias', $paciente->alergias ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Medicamentos em uso contínuo</label>
                    <textarea name="medicamentos_uso_continuo" class="form-control" rows="3"
                              placeholder="Nome, dose, frequência (ex.: Losartana 50mg, 1x ao dia)">{{ old('medicamentos_uso_continuo', $paciente->medicamentos_uso_continuo ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Condições crônicas / doenças preexistentes</label>
                    <textarea name="condicoes_cronicas" class="form-control" rows="3"
                              placeholder="Diabetes, asma, hipertensão, epilepsia, etc.">{{ old('condicoes_cronicas', $paciente->condicoes_cronicas ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cirurgias prévias</label>
                    <textarea name="cirurgias_previas" class="form-control" rows="3"
                              placeholder="Tipo de cirurgia e ano aproximado">{{ old('cirurgias_previas', $paciente->cirurgias_previas ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Hábitos — apenas para maiores de 18 --}}
        <div id="bloco-habitos" class="{{ $isMenorServidor ? 'd-none' : '' }}">
            <div class="onb-section">
                <p class="onb-section-title">Hábitos de vida</p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tabagismo</label>
                        <select name="tabagismo" class="form-select">
                            <option value="">Não informar</option>
                            @foreach(['nao'=>'Não fumo','ex_fumante'=>'Ex-fumante','sim'=>'Fumante atual'] as $val => $label)
                                <option value="{{ $val }}" {{ old('tabagismo', $paciente->tabagismo ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Etilismo</label>
                        <select name="etilismo" class="form-select">
                            <option value="">Não informar</option>
                            @foreach(['nao'=>'Não bebo','ocasional'=>'Ocasional','frequente'=>'Frequente'] as $val => $label)
                                <option value="{{ $val }}" {{ old('etilismo', $paciente->etilismo ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Atividade física</label>
                        <select name="atividade_fisica" class="form-select">
                            <option value="">Não informar</option>
                            @foreach(['sedentario'=>'Sedentário','leve'=>'1–2x por semana','moderada'=>'3+ vezes por semana'] as $val => $label)
                                <option value="{{ $val }}" {{ old('atividade_fisica', $paciente->atividade_fisica ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- PASSO 6 — Confirmação                                             --}}
    {{-- ================================================================ --}}
    <div id="step-6" class="onb-body" style="display:none;">
        <h6 class="fw-semibold mb-3">Confirme seus dados</h6>
        <p class="text-muted small mb-4">
            Revise as informações abaixo. Para corrigir qualquer campo, use o botão <strong>Anterior</strong>.
        </p>
        <div id="resumo-dados">
            {{-- Preenchido pelo JS via atualizarResumo() --}}
        </div>
        <div class="alert alert-success d-flex gap-2 py-2 mt-3">
            <i class="mdi mdi-check-circle-outline flex-shrink-0 mt-1"></i>
            <span class="small">Ao confirmar, seus dados serão salvos e você terá acesso ao sistema.</span>
        </div>
    </div>

    {{-- C.0.4/C.0.5 (v0.10.3): mensagem de erro por passo (além da borda vermelha) --}}
    <div id="wizard-erro" class="alert alert-danger mx-4 mb-0 py-2 d-none" role="alert">
        <i class="mdi mdi-alert-circle-outline me-1"></i>Preencha os campos obrigatórios destacados para continuar.
    </div>

    {{-- Footer — 2 botões (padrão do wizard de agendar): Anterior + principal (Próximo → Salvar).
         Anterior oculto no passo 1; no último passo o principal vira "Salvar e acessar o sistema". --}}
    <div class="onb-footer d-flex justify-content-between gap-2">
        <button type="button" id="btn-voltar" class="btn btn-outline-secondary" style="display:none;" onclick="wizardVoltar()">
            <i class="mdi mdi-arrow-left me-1"></i>Anterior
        </button>
        <button type="button" id="btn-principal" class="btn btn-primary ms-auto" onclick="wizardPrincipal()">
            Próximo <i class="mdi mdi-arrow-right ms-1"></i>
        </button>
    </div>

    </form>
</div>{{-- /onb-card --}}

<script>
// ============================================================
// Wizard de Onboarding — Paciente
// Padrão de meu_agendamento.blade.php adaptado para 7 passos
// ============================================================
const TOTAL_STEPS = 7;
let currentStep = 0;

// Títulos por passo para o label no header
const STEP_TITLES = [
    'Identidade', 'Dados complementares', 'Endereço', 'Contatos pessoais',
    'Emergência', 'Dados de saúde', 'Confirmação'
];

// Menoridade calculada inicialmente pelo servidor, atualizada pelo JS se o usuário editar data_nascimento
let isMenor = {{ $isMenorServidor ? 'true' : 'false' }};

function calcularIdade(dataStr) {
    if (!dataStr) return 99;
    const hoje = new Date();
    const nasc  = new Date(dataStr + 'T00:00:00');
    let idade = hoje.getFullYear() - nasc.getFullYear();
    const m = hoje.getMonth() - nasc.getMonth();
    if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) idade--;
    return idade;
}

function atualizarMenoridade() {
    const dataNasc = document.getElementById('input-data-nasc')?.value;
    isMenor = dataNasc ? calcularIdade(dataNasc) < 18 : {{ $isMenorServidor ? 'true' : 'false' }};
}

function renderStep(step) {
    // Mostra/oculta divs de passo
    for (let i = 0; i < TOTAL_STEPS; i++) {
        const el = document.getElementById('step-' + i);
        if (el) el.style.display = i === step ? '' : 'none';
    }
    // Atualiza dots
    for (let i = 0; i < TOTAL_STEPS; i++) {
        const dot = document.getElementById('dot-' + i);
        if (!dot) continue;
        dot.className = 'w-dot' + (i < step ? ' done' : i === step ? ' active' : '');
    }
    // Atualiza label de passo
    document.getElementById('lbl-step').textContent = 'Passo ' + (step + 1) + ' de ' + TOTAL_STEPS;

    // Botão Anterior: oculto no passo 0
    document.getElementById('btn-voltar').style.display = step > 0 ? '' : 'none';
    // Botão principal único: "Próximo" nos passos 1..n-1, "Salvar e acessar o sistema" no último
    const btnP = document.getElementById('btn-principal');
    if (step < TOTAL_STEPS - 1) {
        btnP.className = 'btn btn-primary ms-auto';
        btnP.innerHTML = 'Próximo <i class="mdi mdi-arrow-right ms-1"></i>';
    } else {
        btnP.className = 'btn btn-success ms-auto';
        btnP.innerHTML = '<i class="mdi mdi-check me-1"></i>Salvar e acessar o sistema';
    }

    // Ao entrar no passo 4: recalcula menoridade e exibe/oculta blocos condicionais
    if (step === 4) {
        atualizarMenoridade();
        const blocoResp = document.getElementById('bloco-responsavel');
        if (blocoResp) blocoResp.classList.toggle('d-none', !isMenor);
        // C.0.4 (v0.10.3): quando menor, os campos do responsável passam a ser obrigatórios
        // (assim validarPasso os cobre); quando maior, remove o required para não travar.
        document.querySelectorAll('#bloco-responsavel [name^="responsavel_"]').forEach(el => {
            if (isMenor) el.setAttribute('required', 'required');
            else el.removeAttribute('required');
        });
    }
    // Ao entrar no passo 5: exibe/oculta bloco de hábitos
    if (step === 5) {
        atualizarMenoridade();
        const blocoHab = document.getElementById('bloco-habitos');
        if (blocoHab) blocoHab.classList.toggle('d-none', isMenor);
    }
    // Ao entrar no último passo: gera o resumo da confirmação
    if (step === TOTAL_STEPS - 1) {
        atualizarResumo();
    }
}

// Ação do botão principal: avança nos passos intermediários, submete no último
function wizardPrincipal() {
    if (currentStep < TOTAL_STEPS - 1) {
        wizardAvancar();
    } else {
        if (!validarPasso(currentStep)) return;
        document.getElementById('form-onboarding').submit();
    }
}

function validarPasso(step) {
    const divStep = document.getElementById('step-' + step);
    const erro = document.getElementById('wizard-erro');
    if (!divStep) return true;
    // Considera apenas os campos required VISÍVEIS (ex.: responsável só quando o bloco aparece)
    const obrigatorios = Array.from(divStep.querySelectorAll('[required]'))
        .filter(el => el.offsetParent !== null);
    let valido = true;
    let primeiroInvalido = null;
    obrigatorios.forEach(el => {
        el.classList.remove('is-invalid');
        if (!el.value.trim()) {
            el.classList.add('is-invalid');
            if (!primeiroInvalido) primeiroInvalido = el;
            valido = false;
        }
    });
    // C.0.4/C.0.5 (v0.10.3): mostra/oculta a mensagem de erro do passo
    if (erro) erro.classList.toggle('d-none', valido);
    if (!valido && primeiroInvalido) {
        primeiroInvalido.focus();
        primeiroInvalido.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return valido;
}

function wizardAvancar() {
    if (!validarPasso(currentStep)) return;
    if (currentStep < TOTAL_STEPS - 1) {
        currentStep++;
        renderStep(currentStep);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function wizardVoltar() {
    if (currentStep > 0) {
        currentStep--;
        renderStep(currentStep);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// Resumo da confirmação — TODOS os dados informados
function atualizarResumo() {
    const get = name => {
        const el = document.querySelector('[name="' + name + '"]');
        if (!el) return '—';
        if (el.tagName === 'SELECT') return el.options[el.selectedIndex]?.text || '—';
        return el.value.trim() || '—';
    };
    const linha = (rot, val) => `<li class="list-group-item py-1">${rot}: <strong>${val}</strong></li>`;

    // Bloco secundário de emergência só aparece se algo foi preenchido
    const temSec = (document.querySelector('[name="contato_emergencia2_nome"]')?.value || '').trim() !== '';
    const secundario = temSec
        ? linha('2º contato', `${get('contato_emergencia2_nome')} (${get('contato_emergencia2_parentesco')}) — ${get('contato_emergencia2_telefone')}`)
        : '';

    // Bloco do responsável só aparece para menores
    const responsavel = isMenor ? `
      <div class="col-md-6">
        <div class="card border">
          <div class="card-header py-2 fw-semibold small">Responsável legal</div>
          <ul class="list-group list-group-flush small">
            ${linha('Nome', get('responsavel_nome'))}
            ${linha('Parentesco', get('responsavel_parentesco'))}
            ${linha('Telefone', get('responsavel_telefone'))}
            ${linha('E-mail', get('responsavel_email'))}
          </ul>
        </div>
      </div>` : '';

    document.getElementById('resumo-dados').innerHTML = `
    <div class="row g-3">
      <div class="col-md-6">
        <div class="card border">
          <div class="card-header py-2 fw-semibold small">Identidade</div>
          <ul class="list-group list-group-flush small">
            ${linha('Nome', get('nome'))}
            ${linha('Nome social', get('nome_social'))}
            ${linha('Documento', get('documento'))}
            ${linha('Data nasc.', get('data_nascimento'))}
            ${linha('Sexo', get('sexo'))}
          </ul>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card border">
          <div class="card-header py-2 fw-semibold small">Dados complementares</div>
          <ul class="list-group list-group-flush small">
            ${linha('Naturalidade', `${get('naturalidade_cidade')}/${get('naturalidade_uf')}`)}
            ${linha('Raça/Cor', get('raca_cor'))}
            ${linha('Estado civil', get('estado_civil'))}
            ${linha('Nome da mãe', get('nome_mae'))}
          </ul>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card border">
          <div class="card-header py-2 fw-semibold small">Endereço</div>
          <ul class="list-group list-group-flush small">
            <li class="list-group-item py-1">${get('logradouro')}, ${get('numero')} ${get('complemento') !== '—' ? '— ' + get('complemento') : ''}</li>
            <li class="list-group-item py-1">${get('bairro')} — ${get('cidade')}/${get('uf')}</li>
            ${linha('CEP', get('cep'))}
          </ul>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card border">
          <div class="card-header py-2 fw-semibold small">Contatos pessoais</div>
          <ul class="list-group list-group-flush small">
            ${linha('Telefone', get('contato'))}
            ${linha('Telefone alt.', get('telefone_alternativo'))}
            ${linha('E-mail alt.', get('email_alternativo'))}
          </ul>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card border">
          <div class="card-header py-2 fw-semibold small">Contatos de emergência</div>
          <ul class="list-group list-group-flush small">
            ${linha('Principal', `${get('contato_emergencia_nome')} (${get('contato_emergencia_parentesco')}) — ${get('contato_emergencia_telefone')}`)}
            ${secundario}
          </ul>
        </div>
      </div>
      ${responsavel}
      <div class="col-md-6">
        <div class="card border">
          <div class="card-header py-2 fw-semibold small">Dados de saúde</div>
          <ul class="list-group list-group-flush small">
            ${linha('Tipo sanguíneo', get('tipo_sanguineo'))}
            <li class="list-group-item py-1">Peso: <strong>${get('peso_kg')} kg</strong> · Altura: <strong>${get('altura_cm')} cm</strong></li>
            ${linha('Alergias', get('alergias'))}
            ${linha('Medicamentos', get('medicamentos_uso_continuo'))}
          </ul>
        </div>
      </div>
    </div>`;
}

// ViaCEP — busca não-bloqueante
function mascararCep(input) {
    let v = input.value.replace(/\D/g, '').slice(0, 8);
    if (v.length > 5) v = v.slice(0, 5) + '-' + v.slice(5);
    input.value = v;
}

function mascararTelefone(input) {
    let v = input.value.replace(/\D/g, '').slice(0, 11);
    if (v.length > 6) v = '(' + v.slice(0,2) + ') ' + v.slice(2,7) + '-' + v.slice(7);
    else if (v.length > 2) v = '(' + v.slice(0,2) + ') ' + v.slice(2);
    input.value = v;
}

function buscarCep() {
    const cep = document.getElementById('input-cep').value.replace(/\D/g, '');
    const status = document.getElementById('cep-status');
    if (cep.length !== 8) return;

    status.textContent = 'Buscando CEP...';
    status.className = 'form-text text-muted';

    const ctrl = new AbortController();
    const timeout = setTimeout(() => ctrl.abort(), 5000);

    fetch('https://viacep.com.br/ws/' + cep + '/json/', { signal: ctrl.signal })
        .then(r => r.json())
        .then(data => {
            clearTimeout(timeout);
            if (data.erro) throw new Error('CEP não encontrado');
            document.getElementById('input-logradouro').value = data.logradouro || '';
            document.getElementById('input-bairro').value     = data.bairro     || '';
            document.getElementById('input-cidade').value     = data.localidade  || '';
            const ufSel = document.getElementById('input-uf');
            if (ufSel) Array.from(ufSel.options).forEach(o => { o.selected = o.value === data.uf; });
            status.textContent = 'Endereço preenchido automaticamente.';
            status.className = 'form-text text-success';
            document.getElementById('input-numero')?.focus();
        })
        .catch(() => {
            clearTimeout(timeout);
            status.textContent = 'CEP não encontrado. Preencha os campos manualmente.';
            status.className = 'form-text text-warning';
        });
}

// ============================================================
// C.0 (v0.10.3): balões de ajuda "?" por clique (touch-friendly)
// ============================================================
const HELP = {
    'documento': 'Informe seu CPF (apenas números). Usado para identificação única no prontuário.',
    'data_nascimento': 'Define se você é menor de 18 anos — nesse caso, será pedido um responsável legal.',
    'cep': 'Digite o CEP para preencher o endereço automaticamente. Se não encontrar, preencha à mão.',
    'contato': 'Telefone principal — é por ele que a equipe de saúde entra em contato. Obrigatório.',
    'email_alternativo': 'Um e-mail pessoal, diferente do institucional, para contato alternativo.',
    'nome_social': 'Nome pelo qual você prefere ser chamado(a), se diferente do nome de registro.',
    'contato_emergencia_nome': 'Pessoa a ser acionada pelo setor de saúde em caso de emergência.',
    'responsavel_nome': 'Obrigatório para pacientes menores de 18 anos.',
    'tipo_sanguineo': 'Ajuda em emergências e eventuais transfusões. Se não souber, escolha "Não sei".',
    'alergias': 'Liste alergias a medicamentos, alimentos ou outros — e a reação, se souber.',
    'medicamentos_uso_continuo': 'Remédios que você toma regularmente (nome, dose e frequência).',
    'condicoes_cronicas': 'Doenças de longa duração: diabetes, asma, hipertensão, epilepsia, etc.',
};

function initHelp() {
    Object.keys(HELP).forEach(name => {
        const input = document.querySelector('[name="' + name + '"]');
        if (!input) return;
        const group = input.closest('[class*="col-"]') || input.parentElement;
        const label = group ? group.querySelector('label.form-label') : null;
        if (!label || label.querySelector('.onb-help')) return;
        const btn = document.createElement('span');
        btn.className = 'onb-help';
        btn.textContent = '?';
        btn.setAttribute('role', 'button');
        btn.setAttribute('aria-label', 'Ajuda');
        btn.dataset.help = HELP[name];
        label.appendChild(btn);
    });
}

// Popover: abre ao clicar no "?", fecha ao clicar fora
let helpPopAberto = null;
document.addEventListener('click', e => {
    if (helpPopAberto) { helpPopAberto.remove(); helpPopAberto = null; }
    const help = e.target.closest('.onb-help');
    if (!help) return;
    e.preventDefault();
    e.stopPropagation();
    const pop = document.createElement('div');
    pop.className = 'onb-help-pop';
    pop.textContent = help.dataset.help;
    document.body.appendChild(pop);
    const r = help.getBoundingClientRect();
    // Mantém dentro da largura da viewport
    const left = Math.min(window.scrollX + r.left - 4, window.scrollX + window.innerWidth - 280);
    pop.style.top  = (window.scrollY + r.bottom + 6) + 'px';
    pop.style.left = Math.max(8, left) + 'px';
    helpPopAberto = pop;
});

// ============================================================
// Responsável = mesmo do contato de emergência principal (menores)
// MESMA UX do /perfil: marcado esconde o contato do responsável (copiado do principal
// nos bastidores); só o e-mail continua visível. Desmarcado mostra os campos.
// ============================================================
function copiaResp() {
    var g = function (n) { return document.querySelector('[name="' + n + '"]'); };
    if (g('responsavel_nome'))     g('responsavel_nome').value     = (g('contato_emergencia_nome')     || {}).value || '';
    if (g('responsavel_telefone')) g('responsavel_telefone').value = (g('contato_emergencia_telefone') || {}).value || '';
    var rp = g('responsavel_parentesco');
    var pv = (g('contato_emergencia_parentesco') || {}).value || '';
    if (rp && Array.prototype.some.call(rp.options, function (o) { return o.value === pv; })) rp.value = pv;
}
function toggleRespMesmo(checked) {
    var cont = document.getElementById('resp-contato');
    if (cont) { cont.style.display = checked ? 'none' : ''; }
    if (checked) { copiaResp(); }
}
// Mantém o responsável sincronizado enquanto o checkbox estiver marcado
['contato_emergencia_nome', 'contato_emergencia_telefone', 'contato_emergencia_parentesco'].forEach(function (n) {
    var el = document.querySelector('[name="' + n + '"]'); if (!el) return;
    var sync = function () { var cb = document.getElementById('resp-mesmo'); if (cb && cb.checked) { copiaResp(); } };
    el.addEventListener('input', sync); el.addEventListener('change', sync);
});
// Inferência: se o responsável já é igual ao contato principal, marca o checkbox e esconde os campos
function inferirRespMesmo() {
    var cb = document.getElementById('resp-mesmo'); if (!cb) return;
    var g = function (n) { var e = document.querySelector('[name="' + n + '"]'); return e ? (e.value || '').trim() : ''; };
    var same = g('responsavel_nome') !== '' &&
               g('responsavel_nome') === g('contato_emergencia_nome') &&
               g('responsavel_telefone') === g('contato_emergencia_telefone') &&
               g('responsavel_parentesco') === g('contato_emergencia_parentesco');
    cb.checked = same;
    toggleRespMesmo(same);
}

// Inicializa
document.addEventListener('DOMContentLoaded', () => {
    renderStep(0);
    initHelp();
    inferirRespMesmo(); // marca o checkbox se responsável já == contato principal (ex.: old() após erro)
    // Se validação server-side falhou (old()), volta ao passo 0 com campos preenchidos
});
</script>

</body>
</html>
