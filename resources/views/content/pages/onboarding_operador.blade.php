{{-- Wizard de Onboarding — Operador (ST-15) --}}
{{-- Admin/Coord/Recep: 1 passo (confirmação de dados) --}}
{{-- Profissional: 2 passos (dados pessoais + especialidade) --}}
@php
    $tema = (isset($_COOKIE['style']) && in_array($_COOKIE['style'], ['light','dark','system'], true))
        ? $_COOKIE['style'] : 'light';
    $tema = $tema === 'dark' ? 'dark' : 'light';
    $totalSteps = $nivel === 3 ? 2 : 1;
@endphp
<!DOCTYPE html>
<html lang="pt-BR" class="{{ $tema }}-style">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Confirmar Dados | Prontu IF</title>
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
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,.55);
            backdrop-filter: blur(4px);
            padding: 1.5rem 1rem;
        }
        .onb-card { width:100%; max-width:600px; border-radius:12px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.35); }
        .onb-header { background:linear-gradient(135deg,#3DAA4A 0%,#1C6B2A 100%); padding:1.5rem 2rem 1rem; }
        .onb-body  { padding:1.75rem 2rem; }
        .onb-footer{ padding:1rem 2rem 1.5rem; border-top:1px solid var(--bs-border-color); }
        .wizard-dots { display:flex; gap:.4rem; margin-top:.75rem; }
        .w-dot { flex:1; height:4px; border-radius:2px; background:rgba(255,255,255,.35); transition:background .3s; }
        .w-dot.active { background:#fff; }
        .w-dot.done   { background:rgba(255,255,255,.7); }
    </style>
</head>
<body>

<div class="onb-card card">

    <div class="onb-header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h5 class="text-white fw-bold mb-0">
                    <i class="mdi mdi-account-check-outline me-2"></i>Confirmar Dados
                </h5>
                <p class="text-white-50 small mb-0 mt-1">
                    Olá, <strong class="text-white">{{ $user->name }}</strong>! Verifique seus dados antes de acessar o sistema.
                </p>
            </div>
            @if($totalSteps > 1)
            <span id="lbl-step" class="badge bg-white text-success fw-semibold px-3 py-2" style="font-size:.85rem;">
                Passo 1 de {{ $totalSteps }}
            </span>
            @endif
        </div>
        @if($totalSteps > 1)
        <div class="wizard-dots mt-2">
            @for($i = 0; $i < $totalSteps; $i++)
                <div class="w-dot {{ $i === 0 ? 'active' : '' }}" id="dot-{{ $i }}"></div>
            @endfor
        </div>
        @endif
    </div>

    @if($errors->any())
    <div class="alert alert-danger m-3 mb-0">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="/onboarding/salvar/operador">
        @csrf

        {{-- ============================================================ --}}
        {{-- PASSO 0 — Dados pessoais (todos os operadores)               --}}
        {{-- ============================================================ --}}
        <div id="step-0" class="onb-body">
            <h6 class="fw-semibold mb-3">Dados de acesso</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nome completo <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required
                           value="{{ old('name', $user->name) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">E-mail <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" required
                           value="{{ old('email', $user->email) }}">
                </div>

                @if($nivel === 3 && $profissional)
                {{-- Profissional: telefone no passo 0 --}}
                <div class="col-md-6">
                    <label class="form-label">Telefone de contato <span class="text-danger">*</span></label>
                    <input type="text" name="contato_profissional" class="form-control" required maxlength="20"
                           placeholder="(33) 99999-9999"
                           value="{{ old('contato_profissional', $profissional->contato ?? '') }}">
                </div>
                @endif
            </div>

            @if($totalSteps === 1)
            {{-- Sem passo 2: campos ocultos para profissional não aparecerem --}}
            <div class="alert alert-info d-flex gap-2 py-2 mt-4">
                <i class="mdi mdi-information-outline mt-1 flex-shrink-0"></i>
                <span class="small">Confirme que os dados acima estão corretos antes de acessar o sistema.</span>
            </div>
            @endif
        </div>

        {{-- ============================================================ --}}
        {{-- PASSO 1 — Dados profissionais (somente nivel 3)              --}}
        {{-- ============================================================ --}}
        @if($nivel === 3)
        <div id="step-1" class="onb-body" style="display:none;">
            <h6 class="fw-semibold mb-3">Dados profissionais</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Especialidade <span class="text-danger">*</span></label>
                    <input type="text" name="especialidade" class="form-control" required maxlength="255"
                           value="{{ old('especialidade', $profissional->especialidade ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Registro profissional (CRM, COREN, etc.) <span class="text-danger">*</span></label>
                    <input type="text" name="registro_profissional" class="form-control" required maxlength="100"
                           value="{{ old('registro_profissional', $profissional->registro_profissional ?? '') }}">
                </div>
            </div>
            <div class="alert alert-info d-flex gap-2 py-2 mt-4">
                <i class="mdi mdi-information-outline mt-1 flex-shrink-0"></i>
                <span class="small">Confirme seus dados profissionais. Eles aparecem nos prontuários que você assina.</span>
            </div>
        </div>
        @endif

        <div class="onb-footer d-flex justify-content-between">
            @if($nivel === 3)
            <button type="button" id="btn-voltar" class="btn btn-outline-secondary" style="display:none;" onclick="wizardVoltar()">
                <i class="mdi mdi-arrow-left me-1"></i>Anterior
            </button>
            @else
            <div></div>
            @endif

            <div class="ms-auto d-flex gap-2">
                @if($nivel === 3)
                <button type="button" id="btn-avancar" class="btn btn-primary" onclick="wizardAvancar()">
                    Próximo <i class="mdi mdi-arrow-right ms-1"></i>
                </button>
                @endif
                <button type="submit" id="btn-confirmar" class="{{ $nivel === 3 ? 'd-none' : '' }} btn btn-success">
                    <i class="mdi mdi-check me-1"></i>Confirmar e acessar
                </button>
            </div>
        </div>

    </form>
</div>

@if($nivel === 3)
<script>
const TOTAL_STEPS = 2;
let currentStep = 0;

function renderStep(step) {
    document.getElementById('step-0').style.display = step === 0 ? '' : 'none';
    document.getElementById('step-1').style.display = step === 1 ? '' : 'none';

    for (let i = 0; i < TOTAL_STEPS; i++) {
        const dot = document.getElementById('dot-' + i);
        if (dot) dot.className = 'w-dot' + (i < step ? ' done' : i === step ? ' active' : '');
    }
    document.getElementById('lbl-step').textContent = 'Passo ' + (step + 1) + ' de ' + TOTAL_STEPS;
    document.getElementById('btn-voltar').style.display = step > 0 ? '' : 'none';
    document.getElementById('btn-avancar').style.display = step < TOTAL_STEPS - 1 ? '' : 'none';
    document.getElementById('btn-confirmar').classList.toggle('d-none', step < TOTAL_STEPS - 1);
}

function validarPasso(step) {
    const div = document.getElementById('step-' + step);
    const obrig = div ? div.querySelectorAll('[required]') : [];
    let ok = true;
    obrig.forEach(el => {
        el.classList.remove('is-invalid');
        if (!el.value.trim()) { el.classList.add('is-invalid'); ok = false; }
    });
    if (!ok) obrig[0]?.focus();
    return ok;
}

function wizardAvancar() {
    if (!validarPasso(currentStep)) return;
    if (currentStep < TOTAL_STEPS - 1) { currentStep++; renderStep(currentStep); }
}

function wizardVoltar() {
    if (currentStep > 0) { currentStep--; renderStep(currentStep); }
}

document.addEventListener('DOMContentLoaded', () => renderStep(0));
</script>
@endif

</body>
</html>
