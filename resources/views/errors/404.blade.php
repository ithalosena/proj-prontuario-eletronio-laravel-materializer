{{-- Página de erro 404 — Página não encontrada (UX-01, v0.10.1) --}}
{{-- Ativada automaticamente pelo Laravel quando NotFoundHttpException é lançada. --}}
{{-- Autocontida: não estende layout (context de erro não garante $configData). --}}
{{-- Análoga à errors/403.blade.php, mas o timer redireciona para / (home) em vez de history.back(). --}}
@php
    /* Reutiliza cookie de tema salvo pelo sistema (mesmo mecanismo do Helpers.php/S-07) */
    $temaErro = (isset($_COOKIE['style']) && in_array($_COOKIE['style'], ['light', 'dark', 'system'], true))
        ? $_COOKIE['style']
        : 'light';
    $temaErro = $temaErro === 'dark' ? 'dark' : 'light';
@endphp
<!DOCTYPE html>
<html lang="pt-BR" class="{{ $temaErro }}-style layout-navbar-fixed">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
    <title>Página não encontrada | Prontu IF</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    {{-- Fontes --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- CSS do tema (caminhos sem versionamento — funcionam no contexto de erro) --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/materialdesignicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />

    {{-- Layout da página de erro (misc-wrapper, misc-bg, misc-object) --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-misc.css') }}" />

    {{-- Paleta Prontu IF por último — sem ela o botão herda o roxo do tema base (BUG-04, v0.10.1) --}}
    <link rel="stylesheet" href="{{ asset('assets/css/prontuif-theme.css') }}?v={{ filemtime(public_path('assets/css/prontuif-theme.css')) }}" />

    <style>
        /* ---- Layout lateral: conteúdo à esquerda, ilustração à direita ---- */
        .err-layout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3rem;
            min-height: 100vh;
            padding: 2rem;
        }
        .err-content {
            flex: 0 0 auto;
            max-width: 380px;
            z-index: 2;
        }
        .err-illustration {
            flex: 0 0 auto;
            position: relative;
            z-index: 2;
        }
        /* Em telas pequenas: empilha verticalmente --*/
        @media (max-width: 767px) {
            .err-layout     { flex-direction: column; gap: 1.5rem; text-align: center; }
            .err-illustration { order: -1; }
        }

        /* ---- Barra de redirecionamento ---- */
        .redirect-track {
            height: 5px;
            border-radius: 99px;
            background: rgba(61, 170, 74, 0.15);
            overflow: hidden;
            margin-bottom: 0.6rem;
        }
        .redirect-fill {
            height: 100%;
            width: 0%;
            border-radius: 99px;
            background: linear-gradient(90deg, #3DAA4A, #72c97d);
            transition: width 1s linear;
        }
        .redirect-text {
            font-size: 0.8rem;
            color: var(--bs-secondary-color, #6b6893);
        }
        .redirect-text strong { color: #3DAA4A; }

        /* ---- Número 404 ---- */
        .err-num {
            font-size: 5.5rem;
            font-weight: 700;
            color: #3DAA4A;
            line-height: 1;
        }
    </style>
</head>

<body>

{{-- Fundo gráfico absoluto (só desktop) — mesmo padrão do pages-misc-error --}}
<img src="{{ asset('assets/img/illustrations/misc-bg-' . $temaErro . '.png') }}"
     alt=""
     class="misc-bg d-none d-lg-block"
     data-app-light-img="illustrations/misc-bg-light.png"
     data-app-dark-img="illustrations/misc-bg-dark.png">

{{-- Layout lateral: texto à esquerda, ilustração à direita --}}
<div class="err-layout">

    {{-- Coluna de conteúdo --}}
    <div class="err-content">
        <p class="err-num mb-1">404</p>
        <h4 class="mb-2">Página não encontrada</h4>
        <p class="mb-1">A página que você procura não existe ou foi movida.</p>
        <p class="text-muted mb-4">
            Verifique o endereço ou volte para o início do sistema.
        </p>

        {{-- Barra de redirecionamento --}}
        <div class="mb-4">
            <div class="redirect-track">
                <div class="redirect-fill" id="prog"></div>
            </div>
            <p class="redirect-text mt-2" id="rtext">
                Voltando ao início em <strong id="rsec">15</strong> segundo<span id="rplural">s</span>…
            </p>
        </div>

        <a href="/" class="btn btn-primary">
            <i class="mdi mdi-home-outline me-1"></i>
            Voltar para o início
        </a>
    </div>

    {{-- Coluna da ilustração --}}
    <div class="err-illustration">
        {{-- Objeto decorativo (só desktop) --}}
        <img src="{{ asset('assets/img/illustrations/misc-error-object.png') }}"
             alt=""
             class="misc-object d-none d-lg-block"
             width="130">

        <img src="{{ asset('assets/img/illustrations/misc-error-illustration.png') }}"
             alt="Ilustração de página não encontrada"
             class="img-fluid"
             width="280">
    </div>

</div>

<script>
    /* Contagem regressiva: 15s → redireciona para / (home) */
    (function () {
        var total = 15, left = total;
        var fill = document.getElementById('prog');
        var sec  = document.getElementById('rsec');
        var plur = document.getElementById('rplural');

        var tick = setInterval(function () {
            left--;
            fill.style.width = ((total - left) / total * 100) + '%';
            sec.textContent  = left;
            plur.textContent = left === 1 ? '' : 's';

            if (left <= 0) {
                clearInterval(tick);
                window.location.href = '/';
            }
        }, 1000);
    }());
</script>

</body>
</html>
