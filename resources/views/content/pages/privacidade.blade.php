{{-- Política de Privacidade — Prontu IF --}}
{{-- Rota pública: acessível sem autenticação (LGPD Art. 9º) --}}
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
    <title>Política de Privacidade | Prontu IF</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    {{-- Fontes --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- CSS do tema --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/materialdesignicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/prontuif-theme.css') }}" />

    <style>
        body { font-family: 'Inter', sans-serif; }
        .privacy-wrapper {
            max-width: 820px;
            margin: 0 auto;
            padding: 2.5rem 1.5rem 4rem;
        }
        .privacy-header {
            border-bottom: 2px solid var(--bs-border-color, #e0e0e0);
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }
        .privacy-section { margin-bottom: 2rem; }
        .privacy-section h2 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--bs-body-color);
            margin-bottom: .75rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .privacy-section p, .privacy-section li {
            font-size: .9rem;
            line-height: 1.7;
            color: var(--bs-secondary-color, #6c757d);
        }
        .badge-lgpd {
            font-size: .65rem;
            font-weight: 600;
            padding: .2rem .5rem;
            border-radius: 4px;
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .dark-style .badge-lgpd { background: #1b3a1e; color: #81c784; border-color: #2e5430; }
        table.dados-table { font-size: .82rem; width: 100%; border-collapse: collapse; margin-top: .5rem; }
        table.dados-table th { background: var(--bs-tertiary-bg, #f5f5f5); font-weight: 600; padding: .4rem .75rem; text-align: left; }
        table.dados-table td { padding: .35rem .75rem; border-top: 1px solid var(--bs-border-color, #e0e0e0); }
        .back-link { font-size: .85rem; }
    </style>
</head>
<body>
<div class="privacy-wrapper">

    {{-- Cabeçalho --}}
    <div class="privacy-header">
        <div class="d-flex align-items-center gap-3 mb-3">
            <a href="{{ url('/') }}" class="back-link text-muted d-flex align-items-center gap-1">
                <i class="mdi mdi-arrow-left"></i> Início
            </a>
        </div>
        <h1 class="fw-bold mb-1" style="font-size: 1.6rem;">
            <i class="mdi mdi-shield-check-outline text-success me-2"></i>
            Política de Privacidade
        </h1>
        <p class="text-muted mb-1" style="font-size:.85rem;">
            Sistema Prontu IF — Setor de Saúde do IFNMG<br>
            Última atualização: <strong>22 de maio de 2026</strong> &nbsp;|&nbsp;
            <span class="badge-lgpd">Conforme Lei nº 13.709/2018 — LGPD</span>
        </p>
    </div>

    {{-- 1. Controlador --}}
    <div class="privacy-section">
        <h2><i class="mdi mdi-domain text-primary"></i> 1. Quem é o Controlador dos seus dados?</h2>
        <p>
            O <strong>Instituto Federal do Norte de Minas Gerais (IFNMG)</strong>, por meio do Setor de Saúde do campus,
            é o controlador responsável pelo tratamento dos seus dados pessoais no âmbito do sistema Prontu IF.
        </p>
        <p>
            <strong>Encarregado de Dados (DPO):</strong> Secretaria-Geral do IFNMG<br>
            <strong>Contato:</strong> <a href="mailto:saude@ifnmg.edu.br">saude@ifnmg.edu.br</a>
        </p>
    </div>

    {{-- 2. Finalidade --}}
    <div class="privacy-section">
        <h2><i class="mdi mdi-clipboard-text-outline text-primary"></i> 2. Para que seus dados são usados?</h2>
        <p>O Prontu IF trata seus dados exclusivamente para as seguintes finalidades:</p>
        <ul>
            <li>Registro e gestão de atendimentos clínicos de saúde no IFNMG;</li>
            <li>Elaboração e manutenção do prontuário eletrônico do estudante;</li>
            <li>Agendamento e acompanhamento de consultas com profissionais de saúde do campus;</li>
            <li>Emissão de relatórios institucionais para gestão do setor de saúde;</li>
            <li>Garantia de auditoria e segurança das ações realizadas no sistema.</li>
        </ul>
    </div>

    {{-- 3. Dados coletados --}}
    <div class="privacy-section">
        <h2><i class="mdi mdi-database-outline text-primary"></i> 3. Quais dados são coletados?</h2>
        <table class="dados-table">
            <thead>
                <tr>
                    <th>Dado</th>
                    <th>Finalidade</th>
                    <th>Classificação</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Nome completo, e-mail, matrícula</td><td>Identificação e autenticação</td><td>Pessoal comum</td></tr>
                <tr><td>CPF, data de nascimento, sexo</td><td>Identificação e prontuário</td><td>Pessoal comum</td></tr>
                <tr><td>Endereço, telefone de contato</td><td>Comunicação e cadastro</td><td>Pessoal comum</td></tr>
                <tr><td>Curso e turma</td><td>Gestão institucional de saúde</td><td>Pessoal comum</td></tr>
                <tr><td>Queixa, anamnese, diagnóstico, conduta</td><td>Prontuário clínico</td><td><strong>Sensível — saúde</strong></td></tr>
                <tr><td>Exames solicitados e resultados</td><td>Prontuário clínico</td><td><strong>Sensível — saúde</strong></td></tr>
                <tr><td>Medicamentos prescritos</td><td>Prontuário clínico</td><td><strong>Sensível — saúde</strong></td></tr>
                <tr><td>IP de acesso e histórico de ações</td><td>Segurança e auditoria</td><td>Pessoal comum</td></tr>
            </tbody>
        </table>
    </div>

    {{-- 4. Base Legal --}}
    <div class="privacy-section">
        <h2><i class="mdi mdi-gavel text-primary"></i> 4. Qual é a base legal do tratamento?</h2>
        <p>O tratamento dos seus dados está fundamentado nas seguintes hipóteses legais da LGPD:</p>
        <ul>
            <li>
                <strong>Dados pessoais comuns:</strong> Execução de contrato ou de políticas públicas (Art. 7º, incisos V e III)
                e obrigação legal (Art. 7º, inciso II).
            </li>
            <li>
                <strong>Dados sensíveis de saúde:</strong> Consentimento explícito do titular (Art. 11, inciso I) e
                tutela da saúde por profissional habilitado (Art. 11, inciso II, alínea f).
            </li>
            <li>
                <strong>Audit logs:</strong> Legítimo interesse do controlador para fins de segurança e prestação de contas (Art. 7º, inciso IX).
            </li>
        </ul>
    </div>

    {{-- 5. Retenção --}}
    <div class="privacy-section">
        <h2><i class="mdi mdi-clock-outline text-primary"></i> 5. Por quanto tempo os dados são retidos?</h2>
        <p>
            Os dados de prontuário clínico são retidos pelo prazo mínimo de <strong>20 (vinte) anos</strong>,
            conforme exigido pela Resolução CFM nº 1.638/2002 e pelos princípios de preservação
            do histórico de saúde do paciente.
        </p>
        <p>
            Dados de acesso (logs de autenticação, IP) são retidos por <strong>12 meses</strong> para fins
            de segurança e auditoria, podendo ser excluídos após esse prazo.
        </p>
        <p class="text-muted" style="font-size:.82rem;">
            <i class="mdi mdi-information-outline"></i>
            O direito à eliminação de dados previsto no Art. 18, VI da LGPD é limitado pela obrigação
            legal de retenção de prontuários médicos. Solicitações de eliminação de dados de saúde serão
            avaliadas individualmente, podendo ser negadas quando houver conflito com essa obrigação legal
            (Art. 16, inciso I da LGPD).
        </p>
    </div>

    {{-- 6. Direitos do titular --}}
    <div class="privacy-section">
        <h2><i class="mdi mdi-account-check-outline text-primary"></i> 6. Quais são seus direitos como titular?</h2>
        <p>De acordo com o Art. 18 da LGPD, você tem direito a:</p>
        <ul>
            <li><strong>Acesso</strong> aos dados que o sistema mantém sobre você (via <em>Meu Prontuário</em>);</li>
            <li><strong>Correção</strong> de dados incompletos ou desatualizados (solicitar à recepção do setor de saúde);</li>
            <li><strong>Portabilidade</strong> dos seus dados em formato estruturado (disponível em <em>Meu Prontuário → Exportar Dados</em>);</li>
            <li><strong>Revogação do consentimento</strong> para dados sensíveis (mediante solicitação formal ao DPO);</li>
            <li><strong>Informações</strong> sobre o tratamento de dados a qualquer momento nesta página.</li>
        </ul>
        <p>
            Para exercer qualquer um desses direitos, entre em contato pelo e-mail
            <a href="mailto:saude@ifnmg.edu.br">saude@ifnmg.edu.br</a>.
        </p>
    </div>

    {{-- 7. Segurança --}}
    <div class="privacy-section">
        <h2><i class="mdi mdi-lock-outline text-primary"></i> 7. Como seus dados são protegidos?</h2>
        <p>O sistema adota as seguintes medidas técnicas de segurança (Art. 46 da LGPD):</p>
        <ul>
            <li>Autenticação com senha hasheada (bcrypt) e sessões criptografadas;</li>
            <li>Controle de acesso por papel (RBAC): cada profissional acessa apenas os dados necessários para o atendimento;</li>
            <li>Proteção contra CSRF em todas as ações que modificam dados;</li>
            <li>Log de auditoria completo de todas as ações sobre dados clínicos;</li>
            <li>Dados sensíveis de saúde não são registrados em logs de auditoria (filtro por campo);</li>
            <li>Rate limiting nas rotas de autenticação para prevenir ataques de força bruta.</li>
        </ul>
    </div>

    {{-- 8. Contato --}}
    <div class="privacy-section">
        <h2><i class="mdi mdi-email-outline text-primary"></i> 8. Dúvidas e contato</h2>
        <p>
            Em caso de dúvidas sobre o tratamento dos seus dados ou para exercer seus direitos como titular,
            entre em contato com o Setor de Saúde do IFNMG:
        </p>
        <ul>
            <li><strong>E-mail:</strong> <a href="mailto:saude@ifnmg.edu.br">saude@ifnmg.edu.br</a></li>
            <li><strong>Local:</strong> Setor de Saúde — Campus [Nome do Campus], IFNMG</li>
        </ul>
    </div>

    {{-- Rodapé da página --}}
    <div class="text-center text-muted pt-3" style="border-top: 1px solid var(--bs-border-color, #e0e0e0); font-size: .8rem;">
        Prontu IF · Sistema de Prontuário Eletrônico — IFNMG · TCC ADS 2026<br>
        Esta política segue a <a href="https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm" target="_blank">Lei nº 13.709/2018 (LGPD)</a>
        e a Resolução CFM nº 1.638/2002.
    </div>

</div>
</body>
</html>
