{{-- ================================================================
     Badge de ORIGEM do atendimento (DT-MOD-01 · Modelo A)

     Agendado   → atendimento nasceu de um agendamento (agendamento_id)
     Espontâneo → atendimento aberto direto (encaixe/emergência/avulso)
     Padrão SUS: demanda agendada × demanda espontânea.

     Uso: @include('content.pages.partials._badge_origem', ['atendimento' => $atendimento])
     Cores via tema: verde (success) = agendado · neutro (secondary) = espontâneo.
     ================================================================ --}}
@if($atendimento->isAgendado())
  <span class="badge rounded-pill bg-label-success">
    <i class="mdi mdi-calendar-check-outline me-1"></i>Agendado
  </span>
@else
  <span class="badge rounded-pill bg-label-secondary">
    <i class="mdi mdi-account-arrow-right-outline me-1"></i>Espontâneo
  </span>
@endif
