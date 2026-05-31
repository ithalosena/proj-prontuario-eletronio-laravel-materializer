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
    {{-- PASSO 0 — Dados básicos do cadastro                              --}}
    {{-- ================================================================ --}}
    <div id="step-0" class="onb-body">
        <h6 class="fw-semibold mb-3">Seus dados cadastrais</h6>
        <p class="text-muted small mb-4">
            Revise os dados abaixo. Nome, documento, data de nascimento e sexo são editáveis.
            <strong>E-mail, matrícula e curso</strong> são definidos pelo administrador e não podem ser alterados aqui.
        </p>

        <div class="onb-section">
            <p class="onb-section-title">Identidade</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nome completo <span class="text-danger">*</span></label>
                    <input type="text" name="nome" class="form-control" required
                           value="{{ old('nome', $paciente->nome ?? '') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">CPF / Documento <span class="text-danger">*</span></label>
                    <input type="text" name="documento" class="form-control" required
                           value="{{ old('documento', $paciente->documento ?? '') }}">
                </div>
                <div class="col-md-3">
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
                <div class="col-md-4">
                    <label class="form-label">Data de nascimento <span class="text-danger">*</span></label>
                    <input type="date" name="data_nascimento" id="input-data-nasc" class="form-control" required
                           value="{{ old('data_nascimento', $dataNascStr) }}">
                </div>
            </div>
        </div>

        <div class="onb-section">
            <p class="onb-section-title">Dados institucionais (somente leitura)</p>
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
    {{-- PASSO 1 — Endereço                                               --}}
    {{-- ================================================================ --}}
    <div id="step-1" class="onb-body" style="display:none;">
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
    {{-- PASSO 2 — Contatos pessoais                                       --}}
    {{-- ================================================================ --}}
    <div id="step-2" class="onb-body" style="display:none;">
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
    {{-- PASSO 3 — Dados complementares                                    --}}
    {{-- ================================================================ --}}
    <div id="step-3" class="onb-body" style="display:none;">
        <h6 class="fw-semibold mb-1">Dados complementares</h6>
        <p class="text-muted small mb-4">Todos os campos são opcionais. Ajudam na identificação e no atendimento.</p>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nome social</label>
                <input type="text" name="nome_social" class="form-control" maxlength="255"
                       value="{{ old('nome_social', $paciente->nome_social ?? '') }}">
                <div class="form-text">Nome pelo qual prefere ser chamado(a).</div>
            </div>
            <div class="col-md-4">
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
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Nome completo <span class="text-danger">*</span></label>
                    <input type="text" name="responsavel_nome" class="form-control" maxlength="255"
                           value="{{ old('responsavel_nome', $paciente->responsavel_nome ?? '') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">CPF <span class="text-danger">*</span></label>
                    <input type="text" name="responsavel_cpf" class="form-control" maxlength="14"
                           value="{{ old('responsavel_cpf', $paciente->responsavel_cpf ?? '') }}">
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
                <div class="col-md-4">
                    <label class="form-label">Telefone <span class="text-danger">*</span></label>
                    <input type="text" name="responsavel_telefone" class="form-control" maxlength="20"
                           placeholder="(33) 99999-9999"
                           value="{{ old('responsavel_telefone', $paciente->responsavel_telefone ?? '') }}"
                           oninput="mascararTelefone(this)">
                </div>
                <div class="col-md-4">
                    <label class="form-label">E-mail <span class="text-danger">*</span></label>
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

    {{-- Footer com botões de navegação --}}
    <div class="onb-footer d-flex justify-content-between">
        <button type="button" id="btn-voltar" class="btn btn-outline-secondary" style="display:none;" onclick="wizardVoltar()">
            <i class="mdi mdi-arrow-left me-1"></i>Anterior
        </button>
        <div class="ms-auto d-flex gap-2">
            <button type="button" id="btn-avancar" class="btn btn-primary" onclick="wizardAvancar()">
                Próximo <i class="mdi mdi-arrow-right ms-1"></i>
            </button>
            <button type="submit" id="btn-confirmar" class="btn btn-success" style="display:none;" disabled>
                <i class="mdi mdi-check me-1"></i>Salvar e acessar o sistema
            </button>
        </div>
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
    'Dados cadastrais', 'Endereço', 'Contatos pessoais',
    'Dados complementares', 'Emergência', 'Dados de saúde', 'Confirmação'
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

    // Botão voltar: oculto no passo 0
    document.getElementById('btn-voltar').style.display = step > 0 ? '' : 'none';
    // Botão avançar: oculto no passo final
    document.getElementById('btn-avancar').style.display = step < TOTAL_STEPS - 1 ? '' : 'none';
    // Botão confirmar: visível apenas no passo final
    const btnConf = document.getElementById('btn-confirmar');
    btnConf.style.display = step === TOTAL_STEPS - 1 ? '' : 'none';

    // Ao entrar no passo 4: recalcula menoridade e exibe/oculta blocos condicionais
    if (step === 4) {
        atualizarMenoridade();
        const blocoResp = document.getElementById('bloco-responsavel');
        if (blocoResp) blocoResp.classList.toggle('d-none', !isMenor);
    }
    // Ao entrar no passo 5: exibe/oculta bloco de hábitos
    if (step === 5) {
        atualizarMenoridade();
        const blocoHab = document.getElementById('bloco-habitos');
        if (blocoHab) blocoHab.classList.toggle('d-none', isMenor);
    }
    // Ao entrar no passo 6: gera resumo e ativa botão de confirmar
    if (step === TOTAL_STEPS - 1) {
        atualizarResumo();
        document.getElementById('btn-confirmar').disabled = false;
    }
}

function validarPasso(step) {
    const divStep = document.getElementById('step-' + step);
    if (!divStep) return true;
    const obrigatorios = divStep.querySelectorAll('[required]');
    let valido = true;
    obrigatorios.forEach(el => {
        el.classList.remove('is-invalid');
        if (!el.value.trim()) {
            el.classList.add('is-invalid');
            valido = false;
        }
    });
    if (!valido) {
        obrigatorios[0]?.focus();
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

// Resumo do passo 6
function atualizarResumo() {
    const get = name => {
        const el = document.querySelector('[name="' + name + '"]');
        if (!el) return '—';
        if (el.tagName === 'SELECT') return el.options[el.selectedIndex]?.text || '—';
        return el.value || '—';
    };

    document.getElementById('resumo-dados').innerHTML = `
    <div class="row g-3">
      <div class="col-md-6">
        <div class="card border">
          <div class="card-header py-2 fw-semibold small">Dados básicos</div>
          <ul class="list-group list-group-flush small">
            <li class="list-group-item py-1">Nome: <strong>${get('nome')}</strong></li>
            <li class="list-group-item py-1">Data nasc.: <strong>${get('data_nascimento')}</strong></li>
            <li class="list-group-item py-1">Sexo: <strong>${get('sexo')}</strong></li>
          </ul>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card border">
          <div class="card-header py-2 fw-semibold small">Endereço</div>
          <ul class="list-group list-group-flush small">
            <li class="list-group-item py-1">${get('logradouro')}, ${get('numero')}</li>
            <li class="list-group-item py-1">${get('bairro')} — ${get('cidade')}/${get('uf')}</li>
            <li class="list-group-item py-1">CEP: <strong>${get('cep')}</strong></li>
          </ul>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card border">
          <div class="card-header py-2 fw-semibold small">Contato de emergência</div>
          <ul class="list-group list-group-flush small">
            <li class="list-group-item py-1">${get('contato_emergencia_nome')} (${get('contato_emergencia_parentesco')})</li>
            <li class="list-group-item py-1">Tel: <strong>${get('contato_emergencia_telefone')}</strong></li>
          </ul>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card border">
          <div class="card-header py-2 fw-semibold small">Dados de saúde</div>
          <ul class="list-group list-group-flush small">
            <li class="list-group-item py-1">Tipo sanguíneo: <strong>${get('tipo_sanguineo')}</strong></li>
            <li class="list-group-item py-1">Peso: <strong>${get('peso_kg')} kg</strong> · Altura: <strong>${get('altura_cm')} cm</strong></li>
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

// Inicializa
document.addEventListener('DOMContentLoaded', () => {
    renderStep(0);
    // Se validação server-side falhou (old()), volta ao passo 0 com campos preenchidos
});
</script>

</body>
</html>
