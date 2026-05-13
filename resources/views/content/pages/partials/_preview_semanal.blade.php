{{--
  Partial: _preview_semanal.blade.php
  Parâmetros esperados:
    $blocos             — collection de DisponibilidadeBloco agrupados por dia_semana
    $slotsPreview       — array de strings 'HH:MM' (30min, 07:00–20:00)
    $ordemExibicao      — [1,2,3,4,5,6,0] (Seg → Dom)
    $diasNomes          — array indexado por dia_semana (Dom,Seg,…)
    $inicioSemana       — Carbon (Monday desta semana)
    $agendamentosSemana — collection de Agendamento da semana
    $excecoesSemana     — collection de DisponibilidadeExcecao que afetam a semana
--}}
@php
  // Abreviações curtas para o cabeçalho (dia_semana → label)
  $abrevs = [0=>'DOM',1=>'SEG',2=>'TER',3=>'QUA',4=>'QUI',5=>'SEX',6=>'SÁB'];

  // Data de cada dia (offset do inicioSemana: Seg=0, Ter=1, ..., Dom=6)
  $datas = [];
  foreach ($ordemExibicao as $diaSem) {
      $offset = $diaSem === 0 ? 6 : $diaSem - 1;
      $datas[$diaSem] = $inicioSemana->copy()->addDays($offset);
  }

  // Pré-computa bloqueios por dia: ['Y-m-d' => [[ini,fim],...] | 'all' para dia inteiro]
  $bloqueiosPorData = [];
  foreach ($excecoesSemana->where('tipo', 'bloqueio') as $exc) {
      $cursor = $exc->data_inicio->copy();
      while ($cursor->lte($exc->data_fim)) {
          $key = $cursor->format('Y-m-d');
          if (!isset($bloqueiosPorData[$key])) $bloqueiosPorData[$key] = [];
          if ($exc->hora_inicio === null) {
              $bloqueiosPorData[$key][] = 'all';
          } else {
              $bloqueiosPorData[$key][] = [substr($exc->hora_inicio,0,5), substr($exc->hora_fim,0,5)];
          }
          $cursor->addDay();
      }
  }

  // Pré-computa agendamentos por data+slot: 'Y-m-d H:i' → true
  $agSlots = [];
  foreach ($agendamentosSemana as $ag) {
      $key = \Carbon\Carbon::parse($ag->data_hora)->format('Y-m-d H:i');
      $agSlots[$key] = $ag->status;
  }

  // Contagens para a legenda
  $totalDisponivelSlots = 0;
  $totalAgendados       = $agendamentosSemana->count();
  $excecaoLabels        = $excecoesSemana->where('tipo','bloqueio')
                              ->map(fn($e) => $e->motivo ?: 'Bloqueio')
                              ->unique()->values();
@endphp

<style>
.preview-table { border-collapse: collapse; width: 100%; min-width: 420px; }
.preview-table th, .preview-table td { padding: 0; border: none; }
.preview-th-hora {
  width: 40px; font-size: 0.6rem; color: var(--bs-secondary-color);
  text-align: right; padding-right: 6px; vertical-align: top; font-weight: 400;
  white-space: nowrap;
}
.preview-th-dia {
  text-align: center; font-size: 0.68rem; font-weight: 600;
  color: var(--bs-secondary-color); padding-bottom: 4px;
}
.preview-th-dia span { display: block; font-size: 1rem; font-weight: 700; color: var(--bs-body-color); line-height: 1.1; }
.preview-cell {
  height: 13px;
  border-left: 1px solid var(--bs-border-color);
  border-bottom: 1px solid transparent;
  box-sizing: border-box;
}
.preview-cell.hora-border { border-bottom: 1px solid var(--bs-border-color); }
.preview-cell.disponivel  { background: rgba(var(--bs-success-rgb), 0.18); }
.preview-cell.agendado    { background: rgba(var(--bs-primary-rgb), 0.75); }
.preview-cell.bloqueio    { background: rgba(255,180,0,0.25); }
.preview-cell.indisponivel { background: transparent; }
.preview-legend {
  display: flex; flex-wrap: wrap; gap: 0.5rem 1rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.72rem;
  color: var(--bs-secondary-color);
  border-top: 1px solid var(--bs-border-color);
}
.preview-legend-dot {
  display: inline-block; width: 10px; height: 10px;
  border-radius: 2px; vertical-align: middle; margin-right: 4px;
}
</style>

<table class="preview-table">
  <thead>
    <tr>
      <th class="preview-th-hora"></th>
      @foreach($ordemExibicao as $d)
      <th class="preview-th-dia">
        {{ $abrevs[$d] }}
        <span>{{ $datas[$d]->format('d') }}</span>
      </th>
      @endforeach
    </tr>
  </thead>
  <tbody>
    @foreach($slotsPreview as $slot)
    @php
      $ehHora = str_ends_with($slot, ':00');
    @endphp
    <tr>
      <td class="preview-th-hora" style="vertical-align:top; padding-top:0;">
        {{ $ehHora ? $slot : '' }}
      </td>
      @foreach($ordemExibicao as $d)
      @php
        $dataStr  = $datas[$d]->format('Y-m-d');
        $slotKey  = $dataStr . ' ' . $slot;
        $bDia     = $blocos->get($d, collect());

        // 1. Verifica bloqueio
        $temBloqueio = false;
        if (isset($bloqueiosPorData[$dataStr])) {
            foreach ($bloqueiosPorData[$dataStr] as $bl) {
                if ($bl === 'all') { $temBloqueio = true; break; }
                if ($slot >= $bl[0] && $slot < $bl[1]) { $temBloqueio = true; break; }
            }
        }

        // 2. Verifica disponibilidade recorrente
        $eDisponivel = $bDia->contains(fn($b) =>
            $slot >= substr($b->hora_inicio,0,5) && $slot < substr($b->hora_fim,0,5)
        );

        // 3. Verifica agendamento
        $statusAg = $agSlots[$slotKey] ?? null;
        if ($eDisponivel && !$temBloqueio) $totalDisponivelSlots++;

        // Determina classe CSS
        if ($temBloqueio) {
            $classe = 'bloqueio';
        } elseif ($statusAg !== null) {
            $classe = 'agendado';
        } elseif ($eDisponivel) {
            $classe = 'disponivel';
        } else {
            $classe = 'indisponivel';
        }
      @endphp
      <td>
        <div class="preview-cell {{ $classe }} {{ $ehHora ? 'hora-border' : '' }}"
             title="{{ $classe !== 'indisponivel' ? ($abrevs[$d].' '.$slot) : '' }}"></div>
      </td>
      @endforeach
    </tr>
    @endforeach
  </tbody>
</table>

{{-- Legenda --}}
<div class="preview-legend">
  <span>
    <span class="preview-legend-dot" style="background:rgba(var(--bs-success-rgb),0.4)"></span>
    Disponível
  </span>
  @if($totalAgendados > 0)
  <span>
    <span class="preview-legend-dot" style="background:rgba(var(--bs-primary-rgb),0.75)"></span>
    Agendada · {{ $totalAgendados }} consulta{{ $totalAgendados!=1?'s':'' }}
  </span>
  @endif
  @foreach($excecaoLabels as $lbl)
  <span>
    <span class="preview-legend-dot" style="background:rgba(255,180,0,0.4)"></span>
    Exceção · {{ $lbl }}
  </span>
  @endforeach
  <span>
    <span class="preview-legend-dot" style="background:var(--bs-tertiary-bg); border:1px solid var(--bs-border-color)"></span>
    Indisponível
  </span>
</div>
