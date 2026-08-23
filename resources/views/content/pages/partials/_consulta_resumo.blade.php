{{-- _consulta_resumo.blade.php
     UX-P06 + UX-P08 (v0.10.2): resumo padronizado de UMA consulta, usado tanto no card de
     consultas do atendimento quanto no modal de histórico recente — para os dois ficarem idênticos.
     Conjunto único de campos: data/hora · tipo (badge) · queixa resumida · diagnóstico resumido ·
     badges de exames/prescrições.
     Variável esperada: $consulta (com exames e prescricoes já carregados para evitar N+1).
--}}

{{-- Linha 1: data/hora + tipo --}}
<div class="d-flex flex-wrap align-items-center gap-2 mb-1">
  <span class="fw-semibold">{{ $consulta->data_hora->format('d/m/Y H:i') }}</span>
  <span class="badge rounded-pill bg-label-primary">{{ $consulta->tipo }}</span>
</div>

{{-- Queixa resumida (limite p/ não quebrar o layout) --}}
@if($consulta->queixa)
<p class="mb-1 small"><span class="text-muted">Queixa:</span> {{ Str::limit($consulta->queixa, 100) }}</p>
@endif

{{-- Diagnóstico resumido --}}
@if($consulta->diagnostico)
<p class="mb-1 small"><span class="text-muted">Diagnóstico:</span> {{ Str::limit($consulta->diagnostico, 100) }}</p>
@endif

{{-- E3d: Anotações livres resumidas (psicologia e outros perfis) --}}
@if($consulta->anotacoes)
<p class="mb-1 small"><span class="text-muted">Anotações:</span> {{ Str::limit($consulta->anotacoes, 100) }}</p>
@endif

{{-- Badges de exames e prescrições (contadores em formato de etiqueta) --}}
<div class="d-flex flex-wrap gap-1 mt-1">
  <span class="badge bg-label-info">
    <i class="mdi mdi-test-tube-outline me-1"></i>{{ $consulta->exames->count() }} exame(s)
  </span>
  <span class="badge bg-label-warning">
    <i class="mdi mdi-pill me-1"></i>{{ $consulta->prescricoes->count() }} prescrição(ões)
  </span>
</div>
