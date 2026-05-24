{{-- Tela de consentimento LGPD — v0.8.2 --}}
{{-- Overlay autocontida (sem @extends) — bloqueio real é server-side via middleware --}}
{{-- Titular (paciente): LGPD Art. 11, I — consentimento para dados sensíveis de saúde --}}
{{-- Operador (profissional/admin/recepção): LGPD Art. 47 — ciência das responsabilidades --}}
@php
    /* Detecta tema salvo pelo sistema para manter consistência visual */
    $tema = (isset($_COOKIE['style']) && in_array($_COOKIE['style'], ['light', 'dark', 'system'], true))
        ? $_COOKIE['style']
        : 'light';
    $tema = $tema === 'dark' ? 'dark' : 'light';
@endphp
<!DOCTYPE html>
<html lang="pt-BR" class="{{ $tema }}-style">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
    <title>Termo de Consentimento | Prontu IF</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/materialdesignicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/prontuif-theme.css') }}" />

    <style>
        /* Overlay full-screen — o bloqueio efetivo é server-side (curl/DevTools não contornam o middleware) */
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(4px);
            padding: 1rem;
        }
        .consent-card {
            width: 100%;
            max-width: 660px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
        }
        .consent-header {
            background: linear-gradient(135deg, #3DAA4A 0%, #1C6B2A 100%);
            padding: 2rem;
            text-align: center;
        }
        .consent-body {
            padding: 2rem;
        }
        .termo-scroll {
            max-height: 300px;
            overflow-y: auto;
            font-size: .875rem;
            line-height: 1.7;
            border: 1px solid var(--bs-border-color);
            border-radius: 6px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>

<div class="consent-card card">

    {{-- Cabeçalho --}}
    <div class="consent-header">
        <i class="mdi mdi-shield-check text-white" style="font-size: 2.5rem;"></i>
        <h4 class="text-white fw-bold mb-1 mt-2">
            @if($tipoTermo === 'operador')
                Termo de Responsabilidade — Agente de Tratamento
            @else
                Termo de Consentimento
            @endif
        </h4>
        <p class="text-white-50 mb-0" style="font-size: .85rem;">
            @if($tipoTermo === 'operador')
                Lei nº 13.709/2018 — LGPD · Art. 47 · Agente de tratamento de dados de saúde
            @else
                Lei nº 13.709/2018 — LGPD · Art. 11, Inciso I · Dados pessoais sensíveis de saúde
            @endif
        </p>
    </div>

    <div class="consent-body">

        {{-- Alerta de atenção --}}
        <div class="alert alert-warning d-flex gap-2 mb-4" role="alert">
            <i class="mdi mdi-information-outline mt-1 flex-shrink-0"></i>
            <div>
                <strong>Leia com atenção antes de continuar.</strong><br>
                @if($tipoTermo === 'operador')
                    Para acessar o Prontu IF como operador do sistema, você precisa declarar ciência
                    das suas responsabilidades como agente de tratamento de dados pessoais de saúde.
                @else
                    Para usar o Prontu IF, precisamos do seu consentimento explícito para tratar
                    seus dados de saúde, conforme exige a Lei Geral de Proteção de Dados Pessoais.
                @endif
            </div>
        </div>

        {{-- Conteúdo do termo --}}
        <div class="termo-scroll">

            @if($tipoTermo === 'operador')

                {{-- TERMO DO OPERADOR — Art. 47 LGPD --}}
                <h6 class="fw-bold mb-2">1. Identificação do controlador e do operador</h6>
                <p class="text-muted">
                    O <strong>Instituto Federal do Norte de Minas Gerais (IFNMG)</strong> é o controlador
                    dos dados pessoais tratados no sistema Prontu IF. Você, como usuário operador
                    (profissional de saúde, recepcionista ou administrador), é um
                    <strong>agente de tratamento</strong> conforme o Art. 5º, VII da LGPD — atuando
                    em nome do controlador.
                </p>

                <h6 class="fw-bold mb-2">2. Dados a que você terá acesso</h6>
                <p class="text-muted">
                    Conforme seu nível de acesso, você poderá tratar dados pessoais sensíveis de saúde
                    dos pacientes (Art. 5º, II LGPD): dados cadastrais, histórico clínico (queixa,
                    anamnese, diagnóstico, conduta), exames, prescrições e agendamentos.
                </p>

                <h6 class="fw-bold mb-2">3. Suas responsabilidades (Art. 47 LGPD)</h6>
                <p class="text-muted">
                    A LGPD estabelece que "os agentes de tratamento ou qualquer outra pessoa que
                    intervenha em uma das fases do tratamento obriga-se a garantir a segurança da
                    informação". Isso inclui:
                </p>
                <ul class="text-muted" style="font-size:.875rem;">
                    <li>Usar os dados exclusivamente para fins institucionais e clínicos;</li>
                    <li>Manter sigilo profissional sobre informações dos pacientes;</li>
                    <li>Não compartilhar credenciais de acesso ao sistema;</li>
                    <li>Reportar imediatamente qualquer incidente de segurança ao responsável institucional.</li>
                </ul>

                <h6 class="fw-bold mb-2">4. Monitoramento e auditoria</h6>
                <p class="text-muted">
                    Todas as ações realizadas no sistema são registradas em log de auditoria (criação,
                    alteração e exclusão de registros), conforme Art. 6º, VIII da LGPD (prestação de contas).
                    O acesso a dados sensíveis é monitorado de forma contínua.
                </p>

                <h6 class="fw-bold mb-2">5. Base legal</h6>
                <p class="text-muted mb-0">
                    Este termo documenta sua ciência e aceitação das responsabilidades estabelecidas pelo
                    Art. 47 da LGPD. Não constitui consentimento no sentido do Art. 7º, I — o consentimento
                    dos pacientes titulares é obtido separadamente. Em caso de dúvidas, consulte o
                    responsável institucional pela proteção de dados do IFNMG.
                </p>

            @else

                {{-- TERMO DO TITULAR — Art. 11, I LGPD --}}
                <h6 class="fw-bold mb-2">1. Quem coleta seus dados?</h6>
                <p class="text-muted">
                    O <strong>Instituto Federal do Norte de Minas Gerais (IFNMG)</strong>, por meio do
                    Setor de Saúde do campus, é responsável pelo tratamento dos seus dados pessoais
                    no sistema Prontu IF.
                </p>

                <h6 class="fw-bold mb-2">2. Quais dados de saúde são tratados?</h6>
                <p class="text-muted">
                    Ao utilizar o Prontu IF, o sistema registrará seus dados de saúde, incluindo:
                    queixa principal, anamnese, diagnósticos, conduta terapêutica, exames solicitados e
                    medicamentos prescritos. Esses dados são classificados como
                    <strong>dados pessoais sensíveis</strong> conforme o Art. 5º, II da LGPD.
                </p>

                <h6 class="fw-bold mb-2">3. Para que são usados?</h6>
                <p class="text-muted">
                    Seus dados de saúde são usados exclusivamente para fins clínicos: registro do
                    prontuário eletrônico, acompanhamento do atendimento, agendamento de consultas
                    e emissão de relatórios institucionais de saúde.
                </p>

                <h6 class="fw-bold mb-2">4. Por quanto tempo?</h6>
                <p class="text-muted">
                    Os dados de prontuário clínico são retidos por, no mínimo, <strong>20 anos</strong>,
                    conforme a Resolução CFM nº 1.821/2007 (Art. 1º), que estabelece os prazos de guarda
                    de registros médicos eletrônicos. A solicitação de eliminação desses dados poderá ser
                    recusada com base no Art. 16, I da LGPD (obrigação legal de retenção).
                </p>

                <h6 class="fw-bold mb-2">5. Seus direitos</h6>
                <p class="text-muted mb-0">
                    Você tem direito a acessar seus dados (via <em>Meu Prontuário</em>), solicitar
                    correções, exportar seus dados em formato portável e revogar este consentimento
                    a qualquer momento diretamente no sistema. A revogação não afeta atendimentos já
                    realizados, mas encerrará seu acesso ao prontuário eletrônico até novo aceite.
                    Para mais informações, consulte nossa
                    <a href="{{ url('/privacidade') }}" target="_blank">Política de Privacidade</a>.
                </p>

            @endif

        </div>{{-- /termo-scroll --}}

        {{-- Formulário de aceite --}}
        <form method="POST" action="{{ url('/consentimento/aceitar') }}">
            @csrf
            {{-- Tipo do termo transmitido como campo oculto para o controller --}}
            <input type="hidden" name="tipo_termo" value="{{ $tipoTermo }}">

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="chk-consentimento" required>
                <label class="form-check-label fw-medium" for="chk-consentimento" style="font-size:.875rem;">
                    @if($tipoTermo === 'operador')
                        Li e declaro ciência das minhas responsabilidades como agente de tratamento de
                        dados pessoais de saúde no IFNMG, nos termos descritos acima e da LGPD Art. 47.
                    @else
                        Li e concordo com o tratamento dos meus dados pessoais de saúde pelo IFNMG,
                        nos termos descritos acima e na
                        <a href="{{ url('/privacidade') }}" target="_blank">Política de Privacidade</a>,
                        conforme o Art. 11, I da LGPD.
                    @endif
                </label>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-success btn-lg fw-bold">
                    <i class="mdi mdi-check-circle-outline me-2"></i>
                    @if($tipoTermo === 'operador')
                        Confirmo e quero continuar
                    @else
                        Aceito e quero continuar
                    @endif
                </button>
            </div>
        </form>

        {{-- Botão "Não aceito" — POST com CSRF (mesma proteção que o logout, S-04) --}}
        <form method="POST" action="{{ url('/consentimento/recusar') }}" class="mt-3">
            @csrf
            <div class="d-grid">
                <button type="submit" class="btn btn-outline-secondary"
                    onclick="return confirm('Ao recusar, você será desconectado e não poderá acessar o sistema. Confirma?')">
                    <i class="mdi mdi-close-circle-outline me-2"></i>
                    Não aceito — sair do sistema
                </button>
            </div>
        </form>

        {{-- Versão do termo --}}
        <p class="text-center text-muted mt-3 mb-0" style="font-size:.75rem;">
            Versão do termo: <strong>{{ $versaoTermo }}</strong> · Aceite registrado com IP e data/hora.
        </p>

    </div>{{-- /consent-body --}}
</div>{{-- /consent-card --}}

</body>
</html>
