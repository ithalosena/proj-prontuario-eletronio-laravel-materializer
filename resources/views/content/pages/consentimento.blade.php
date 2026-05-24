{{-- Tela de consentimento LGPD — primeiro acesso de pacientes --}}
{{-- LGPD Art. 11, I: consentimento específico e destacado para dados de saúde --}}
@php $configData = Helper::appClasses(); @endphp

@extends('layouts/layoutMaster')

@section('title', 'Termo de Consentimento')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y d-flex align-items-center justify-content-center" style="min-height: 80vh;">
  <div class="col-md-8 col-lg-7 col-xl-6">

    <div class="card shadow-sm">
      <div class="card-header text-center py-4" style="background: linear-gradient(135deg, #3DAA4A 0%, #1C6B2A 100%);">
        <i class="mdi mdi-shield-check text-white" style="font-size: 2.5rem;"></i>
        <h4 class="text-white fw-bold mb-1 mt-2">Termo de Consentimento</h4>
        <p class="text-white-50 mb-0" style="font-size: .85rem;">
          Lei nº 13.709/2018 — LGPD · Art. 11, Inciso I
        </p>
      </div>

      <div class="card-body p-4">

        {{-- Introdução --}}
        <div class="alert alert-warning d-flex gap-2 mb-4" role="alert">
          <i class="mdi mdi-information-outline mt-1"></i>
          <div>
            <strong>Leia com atenção antes de continuar.</strong><br>
            Para usar o Prontu IF, precisamos do seu consentimento explícito para
            tratar seus dados de saúde, conforme exige a Lei Geral de Proteção de Dados Pessoais.
          </div>
        </div>

        {{-- Conteúdo do termo --}}
        <div class="mb-4" style="max-height: 320px; overflow-y: auto; font-size: .875rem; line-height: 1.7; border: 1px solid var(--bs-border-color); border-radius: 6px; padding: 1rem 1.25rem;">

          <h6 class="fw-bold mb-2">1. Quem coleta seus dados?</h6>
          <p class="text-muted">
            O <strong>Instituto Federal do Norte de Minas Gerais (IFNMG)</strong>, por meio do Setor de Saúde do campus,
            é responsável pelo tratamento dos seus dados pessoais no sistema Prontu IF.
          </p>

          <h6 class="fw-bold mb-2">2. Quais dados de saúde são tratados?</h6>
          <p class="text-muted">
            Ao utilizar o Prontu IF, o sistema registrará seus dados de saúde, incluindo:
            queixa principal, anamnese, diagnósticos, conduta terapêutica, exames solicitados e
            medicamentos prescritos. Esses dados são classificados como <strong>dados pessoais sensíveis</strong>
            conforme o Art. 5º, II da LGPD.
          </p>

          <h6 class="fw-bold mb-2">3. Para que são usados?</h6>
          <p class="text-muted">
            Seus dados de saúde são usados exclusivamente para fins clínicos: registro do prontuário eletrônico,
            acompanhamento do atendimento, agendamento de consultas e emissão de relatórios institucionais de saúde.
          </p>

          <h6 class="fw-bold mb-2">4. Por quanto tempo?</h6>
          <p class="text-muted">
            Os dados de prontuário clínico são retidos por, no mínimo, <strong>20 anos</strong>,
            conforme a Resolução CFM nº 1.638/2002. Isso significa que a solicitação de eliminação
            desses dados poderá ser recusada com base no Art. 16, I da LGPD (obrigação legal de retenção).
          </p>

          <h6 class="fw-bold mb-2">5. Seus direitos</h6>
          <p class="text-muted mb-0">
            Você tem direito a acessar seus dados (via <em>Meu Prontuário</em>), solicitar correções,
            exportar seus dados em formato portável e revogar este consentimento a qualquer momento
            — entrando em contato com o Setor de Saúde pelo e-mail <a href="mailto:saude@ifnmg.edu.br">saude@ifnmg.edu.br</a>.
            A revogação não afeta atendimentos já realizados.
            Para mais informações, consulte nossa
            <a href="{{ url('/privacidade') }}" target="_blank">Política de Privacidade</a>.
          </p>
        </div>

        {{-- Aceite --}}
        <form method="POST" action="{{ url('/consentimento/aceitar') }}">
          @csrf

          <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="chk-consentimento" required>
            <label class="form-check-label fw-medium" for="chk-consentimento" style="font-size:.875rem;">
              Li e concordo com o tratamento dos meus dados pessoais de saúde pelo IFNMG,
              nos termos descritos acima e na
              <a href="{{ url('/privacidade') }}" target="_blank">Política de Privacidade</a>,
              conforme o Art. 11, I da LGPD.
            </label>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-success btn-lg fw-bold">
              <i class="mdi mdi-check-circle-outline me-2"></i>
              Aceito e quero continuar
            </button>
          </div>
        </form>

        {{-- Versão do termo --}}
        <p class="text-center text-muted mt-3 mb-0" style="font-size:.75rem;">
          Versão do termo: <strong>{{ $versaoTermo }}</strong> · Aceite registrado com IP e data/hora.
        </p>

      </div>{{-- /card-body --}}
    </div>{{-- /card --}}

    <p class="text-center text-muted mt-3" style="font-size: .8rem;">
      Ao não consentir, você não poderá acessar o sistema de prontuário.<br>
      Em caso de dúvidas, contate o Setor de Saúde: <a href="mailto:saude@ifnmg.edu.br">saude@ifnmg.edu.br</a>
    </p>

  </div>
</div>
@endsection
