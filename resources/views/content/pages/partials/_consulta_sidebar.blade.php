{{-- _consulta_sidebar.blade.php
     UX-P09 (v0.10.2): painel lateral "Consulta selecionada" usado nas telas de cadastro/edição
     de exame e prescrição. Preenchido via JS (_consulta_sidebar_script) a partir dos data-* da
     opção selecionada no <select id="consulta_id">. Sem parâmetros (IDs únicos por página). --}}
<div class="card h-100">
  <div class="card-header">
    <h6 class="card-title mb-0"><i class="mdi mdi-clipboard-pulse-outline me-2"></i>Consulta selecionada</h6>
  </div>
  <div class="card-body">

    {{-- Estado vazio (nada selecionado) --}}
    <div id="resumo-vazio" class="text-center text-muted py-5">
      <i class="mdi mdi-cursor-default-click-outline d-block mb-2" style="font-size:2.5rem; opacity:.35"></i>
      <p class="small mb-0">Selecione uma consulta para ver os detalhes aqui.</p>
    </div>

    {{-- Resumo (preenchido via JS) --}}
    <div id="resumo-conteudo" class="d-none">
      <div class="d-flex align-items-center gap-2 mb-3">
        <div class="avatar avatar-sm">
          <span class="avatar-initial rounded-circle bg-label-primary"><i class="mdi mdi-account-outline"></i></span>
        </div>
        <div>
          <span class="text-muted small d-block">Paciente</span>
          <span class="fw-semibold" id="resumo-paciente"></span>
        </div>
      </div>
      <hr class="my-3">
      <div class="mb-3">
        <span class="text-muted small d-block"><i class="mdi mdi-calendar-clock-outline me-1"></i>Data / Hora</span>
        <span id="resumo-data"></span>
      </div>
      <div class="mb-3">
        <span class="text-muted small d-block"><i class="mdi mdi-doctor me-1"></i>Profissional</span>
        <span id="resumo-profissional"></span>
      </div>
      <div>
        <span class="text-muted small d-block mb-1">Tipo da consulta</span>
        <span class="badge bg-label-primary" id="resumo-tipo"></span>
      </div>
    </div>

  </div>
</div>
