<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAgendamentoRequest;
use App\Models\AgendaConfig;
use App\Models\Agendamento;
use App\Models\DisponibilidadeBloco;
use App\Models\DisponibilidadeExcecao;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Models\TipoConsulta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
 * Controller: AgendamentoController
 *
 * Gerencia o ciclo de vida dos agendamentos pelo staff (nivel <= 3).
 * Ciclo: pendente → confirmado → realizado (vincula consulta) | cancelado
 *
 * Rotas (grupo nivel:3):
 *   GET   /agendamentos                            → index()
 *   GET   /agendamentos/eventos                    → eventos()   [JSON FullCalendar]
 *   GET   /agendamentos/slots/{profissional}/{data} → slots()    [JSON horários livres]
 *   GET   /cadastro-agendamento                    → create()
 *   POST  /cadastrar-agendamento                   → store()
 *   GET   /agendamentos/{id}                       → show()
 *   PATCH /agendamentos/{id}/confirmar             → confirmar()
 *   PATCH /agendamentos/{id}/cancelar              → cancelar()
 *   PATCH /agendamentos/{id}/realizar              → realizar()
 */
class AgendamentoController extends Controller
{
    /*
     * Exibe o calendário principal com stats inline e filtros.
     * Profissional (nivel 3) vê só os seus; admin/recepcionista veem todos.
     */
    public function index()
    {
        $user  = Auth::user();
        $nivel = $user->nivelAcesso();

        // Stats por status (scoped ao profissional logado se nivel==3)
        $statsQuery = Agendamento::query();
        if ($nivel == 3 && $user->profissional) {
            $statsQuery->where('profissional_id', $user->profissional->id);
        }
        $pendentes   = (clone $statsQuery)->where('status', 'pendente')->count();
        $confirmados = (clone $statsQuery)->where('status', 'confirmado')->count();
        $realizados  = (clone $statsQuery)->where('status', 'realizado')->count();
        $cancelados  = (clone $statsQuery)->where('status', 'cancelado')->count();
        $total       = (clone $statsQuery)->count();

        // Lista de profissionais para o filtro (admin/coord/recepcionista habilitado; profissional vê só o próprio)
        $profissionalLogado = ($nivel == 3) ? $user->profissional : null;
        $profissionais      = ($nivel <= 2 || $nivel == 4)
            ? Profissional::orderBy('nome')->get()
            : collect();

        return view('content.pages.agendamentos', compact(
            'pendentes', 'confirmados', 'realizados', 'cancelados', 'total',
            'profissionais', 'profissionalLogado'
        ));
    }

    /*
     * Retorna eventos no formato JSON esperado pelo FullCalendar.
     * Profissional vê só os seus; admin/coordenador podem filtrar via ?profissional_id.
     * extendedProps.status permite filtro client-side por status na view.
     */
    public function eventos()
    {
        $user  = Auth::user();
        $nivel = $user->nivelAcesso();

        $query = Agendamento::with('paciente')
            ->whereNotNull('data_hora');

        if ($nivel == 3 && $user->profissional) {
            $query->where('profissional_id', $user->profissional->id);
        } elseif ($nivel <= 4 && request('profissional_id')) {
            // Admin, coordenador e recepcionista podem filtrar por profissional
            $query->where('profissional_id', request('profissional_id'));
        }

        $eventos = $query->get()->map(function (Agendamento $ag) {
            $primeiroNome = explode(' ', $ag->paciente->nome ?? '?')[0];
            return [
                'id'            => $ag->id,
                'title'         => $primeiroNome,
                'start'         => $ag->data_hora->toIso8601String(),
                'classNames'    => ['fc-event-' . $ag->status],
                'url'           => '/agendamentos/' . $ag->id,
                'extendedProps' => ['status' => $ag->status],
            ];
        });

        return response()->json($eventos);
    }

    /*
     * Retorna horários livres para um profissional em uma data (JSON para AJAX).
     * Usa DisponibilidadeBloco (múltiplos por dia), DisponibilidadeExcecao e AgendaConfig.
     */
    public function slots($profissionalId, $data)
    {
        $profissional = Profissional::findOrFail($profissionalId);
        $dataCarbon   = Carbon::parse($data);
        $diaSemana    = $dataCarbon->dayOfWeek; // 0=Dom ... 6=Sáb

        // Configuração de agenda (duracao, buffer, antecedência)
        $config = AgendaConfig::firstOrNew(
            ['profissional_id' => $profissional->id],
            ['duracao_minutos' => 30, 'buffer_minutos' => 0, 'antecedencia_minima_horas' => 1, 'antecedencia_maxima_dias' => 60]
        );

        // Rejeita datas passadas
        if ($dataCarbon->lt(now()->startOfDay())) {
            return response()->json([]);
        }

        // Rejeita datas além da antecedência máxima
        if ($dataCarbon->gt(now()->addDays($config->antecedencia_maxima_dias)->endOfDay())) {
            return response()->json([]);
        }

        // Busca todos os blocos do dia da semana
        $blocos = DisponibilidadeBloco::where('profissional_id', $profissional->id)
            ->where('dia_semana', $diaSemana)
            ->get();

        if ($blocos->isEmpty()) {
            return response()->json([]);
        }

        // Exceções que afetam esta data
        $excecoes = DisponibilidadeExcecao::where('profissional_id', $profissional->id)
            ->where('data_inicio', '<=', $data)
            ->where('data_fim',    '>=', $data)
            ->get();

        $excBloqueios = $excecoes->where('tipo', 'bloqueio');
        $excExtras    = $excecoes->where('tipo', 'disponivel_extra');

        $duracao   = $config->duracao_minutos;
        $intervalo = $duracao + $config->buffer_minutos;

        $todos = collect();

        // Gera slots a partir dos blocos recorrentes
        foreach ($blocos as $bloco) {
            // Bloqueio de dia inteiro cancela o bloco por completo
            if ($excBloqueios->first(fn ($e) => $e->hora_inicio === null)) {
                continue;
            }

            $cursor = Carbon::parse($data . ' ' . $bloco->hora_inicio);
            $fim    = Carbon::parse($data . ' ' . $bloco->hora_fim);

            while ($cursor->copy()->addMinutes($duracao)->lte($fim)) {
                // Verifica se o slot cai dentro de algum bloqueio parcial
                $bloqueado = $excBloqueios->contains(function ($exc) use ($cursor, $duracao, $data) {
                    if ($exc->hora_inicio === null) {
                        return false; // dia inteiro já tratado antes
                    }
                    $excInicio = Carbon::parse($data . ' ' . $exc->hora_inicio);
                    $excFim    = Carbon::parse($data . ' ' . $exc->hora_fim);
                    return $cursor->lt($excFim) && $cursor->copy()->addMinutes($duracao)->gt($excInicio);
                });

                if (!$bloqueado) {
                    $todos->push($cursor->copy());
                }
                $cursor->addMinutes($intervalo);
            }
        }

        // Adiciona slots extras (disponivel_extra com hora definida)
        foreach ($excExtras as $exc) {
            if ($exc->hora_inicio === null) {
                continue; // extra de dia inteiro não gera slots individuais
            }
            $cursor = Carbon::parse($data . ' ' . $exc->hora_inicio);
            $fim    = Carbon::parse($data . ' ' . $exc->hora_fim);

            while ($cursor->copy()->addMinutes($duracao)->lte($fim)) {
                $horaCursor = $cursor->format('H:i');
                if (!$todos->contains(fn ($t) => $t->format('H:i') === $horaCursor)) {
                    $todos->push($cursor->copy());
                }
                $cursor->addMinutes($intervalo);
            }
        }

        // Rejeita slots antes da antecedência mínima
        $minimo = now()->addHours($config->antecedencia_minima_horas);
        $todos  = $todos->filter(fn ($s) => $s->gt($minimo))->sortBy(fn ($s) => $s->timestamp);

        // Remove slots já ocupados (pendente ou confirmado)
        $ocupados = Agendamento::where('profissional_id', $profissional->id)
            ->whereDate('data_hora', $data)
            ->whereIn('status', ['pendente', 'confirmado'])
            ->pluck('data_hora')
            ->map(fn ($dt) => Carbon::parse($dt)->format('H:i'))
            ->toArray();

        $livres = $todos
            ->filter(fn ($slot) => !in_array($slot->format('H:i'), $ocupados))
            ->map(fn ($slot) => [
                'value' => $slot->format('Y-m-d H:i:s'),
                'label' => $slot->format('H:i'),
            ])
            ->values();

        return response()->json($livres);
    }

    /*
     * Exibe o formulário de novo agendamento.
     * Aceita ?paciente_id= para pré-selecionar o paciente (vindo de detalhes do paciente).
     */
    public function create()
    {
        $profissionais      = Profissional::orderBy('nome')->get();
        $tipos              = TipoConsulta::where('ativo', true)->orderBy('ordem')->get();
        $profissionalLogado = Auth::user()->profissional;

        $pacienteAnterior = old('paciente_id')
            ? Paciente::find(old('paciente_id'))
            : (request('paciente_id') ? Paciente::find(request('paciente_id')) : null);

        return view('content.pages.cadastro_agendamento', compact(
            'profissionais', 'tipos', 'profissionalLogado', 'pacienteAnterior'
        ));
    }

    /*
     * Persiste o novo agendamento com status=pendente.
     */
    public function store(StoreAgendamentoRequest $request)
    {
        $agendamento = Agendamento::create([
            'paciente_id'     => $request->paciente_id,
            'profissional_id' => $request->profissional_id,
            'criado_por_id'   => Auth::id(),
            'data_hora'       => $request->data_hora,
            'tipo'            => $request->tipo,
            'status'          => 'pendente',
            'observacao'      => $request->observacao,
        ]);

        return redirect('/agendamentos/' . $agendamento->id)
            ->with('success', 'Agendamento criado! Confirme quando o paciente comparecer.');
    }

    /*
     * Exibe os detalhes de um agendamento com botões de ação condicionais ao status.
     */
    public function show($id)
    {
        $agendamento = Agendamento::with('paciente', 'profissional', 'criadoPor', 'canceladoPor', 'consulta')
            ->findOrFail($id);

        // S-02: bloqueia IDOR — profissional só vê agendamentos onde é o responsável
        $this->authorize('view', $agendamento);

        return view('content.pages.detalhes_agendamento', compact('agendamento'));
    }

    /*
     * Transição: pendente → confirmado.
     */
    public function confirmar($id)
    {
        $agendamento = Agendamento::findOrFail($id);

        // S-02: profissional só confirma os próprios agendamentos
        $this->authorize('update', $agendamento);

        if (!$agendamento->isPendente()) {
            return back()->with('error', 'Apenas agendamentos pendentes podem ser confirmados.');
        }

        $agendamento->update(['status' => 'confirmado']);

        // Notifica o paciente sobre a confirmação
        $agendamento->paciente->user?->notify(new \App\Notifications\AgendamentoConfirmadoNotification($agendamento));

        return back()->with('success', 'Agendamento confirmado.');
    }

    /*
     * Transição: qualquer status → cancelado (requer motivo).
     */
    public function cancelar(Request $request, $id)
    {
        $request->validate([
            'motivo_cancelamento' => ['required', 'string', 'max:500'],
        ]);

        $agendamento = Agendamento::findOrFail($id);

        // S-02: profissional só cancela os próprios agendamentos
        $this->authorize('cancelar', $agendamento);

        if ($agendamento->isRealizado() || $agendamento->isCancelado()) {
            return back()->with('error', 'Este agendamento não pode ser cancelado.');
        }

        $agendamento->update([
            'status'              => 'cancelado',
            'cancelado_por_id'    => Auth::id(),
            'motivo_cancelamento' => $request->motivo_cancelamento,
            'cancelado_em'        => now(),
        ]);

        // Notifica a outra parte: profissional notifica paciente, outros notificam o profissional
        $ehProfissional = Auth::user()->nivelAcesso() === 3;
        $outraParte = $ehProfissional ? $agendamento->paciente->user : $agendamento->profissional->user;
        $outraParte?->notify(new \App\Notifications\AgendamentoCanceladoNotification($agendamento, Auth::user()));

        return redirect('/agendamentos')->with('success', 'Agendamento cancelado.');
    }

    /*
     * Transição: confirmado → redirecionamento para criação de consulta.
     * O ConsultaController::store() marcará o agendamento como realizado ao criar a consulta.
     */
    public function realizar($id)
    {
        $agendamento = Agendamento::findOrFail($id);

        // S-02: profissional só realiza os próprios agendamentos
        $this->authorize('update', $agendamento);

        if (!$agendamento->isConfirmado()) {
            return back()->with('error', 'Apenas agendamentos confirmados podem ser realizados.');
        }

        // DT-MOD-01: só o id do agendamento — o create() da consulta deriva
        // paciente/profissional dele. NADA é criado neste passo: o atendimento
        // nasce ao salvar a consulta (store atômico). O tipo não vai na URL
        // (agendamento.tipo é a especialidade desde a v0.10.4 — decisão §8).
        return redirect('/cadastro-consulta?' . http_build_query([
            'agendamento_id' => $agendamento->id,
        ]));
    }
}
