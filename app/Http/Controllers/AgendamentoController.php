<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAgendamentoRequest;
use App\Models\Agendamento;
use App\Models\Disponibilidade;
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

        // Lista de profissionais para o filtro server-side (admin/coordenador apenas)
        $profissionais = collect();
        if ($nivel <= 2) {
            $profissionais = Profissional::orderBy('nome')->get();
        }

        return view('content.pages.agendamentos', compact(
            'pendentes', 'confirmados', 'realizados', 'cancelados', 'total', 'profissionais'
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
        } elseif ($nivel <= 2 && request('profissional_id')) {
            $query->where('profissional_id', request('profissional_id'));
        }

        $cores = [
            'pendente'   => '#fdb528',
            'confirmado' => '#666cff',
            'realizado'  => '#72e128',
            'cancelado'  => '#ff4d49',
        ];

        $eventos = $query->get()->map(function (Agendamento $ag) use ($cores) {
            $primeiroNome = explode(' ', $ag->paciente->nome ?? '?')[0];
            return [
                'id'            => $ag->id,
                'title'         => $ag->data_hora->format('H:i') . ' ' . $primeiroNome,
                'start'         => $ag->data_hora->toIso8601String(),
                'color'         => $cores[$ag->status] ?? '#aaa',
                'url'           => '/agendamentos/' . $ag->id,
                'extendedProps' => ['status' => $ag->status],
            ];
        });

        return response()->json($eventos);
    }

    /*
     * Retorna horários livres para um profissional em uma data (JSON para AJAX).
     * Slots de 30 em 30 minutos, removendo os já ocupados (pendente/confirmado).
     */
    public function slots($profissionalId, $data)
    {
        $profissional = Profissional::findOrFail($profissionalId);
        $dataCarbon   = Carbon::parse($data);
        $diaSemana    = $dataCarbon->dayOfWeek; // 0=Dom...6=Sab

        $disponibilidade = Disponibilidade::where('profissional_id', $profissional->id)
            ->where('dia_semana', $diaSemana)
            ->where('ativo', true)
            ->first();

        if (!$disponibilidade) {
            return response()->json([]);
        }

        // Gera todos os slots de 30 minutos no intervalo configurado
        $inicio  = Carbon::parse($data . ' ' . $disponibilidade->hora_inicio);
        $fim     = Carbon::parse($data . ' ' . $disponibilidade->hora_fim);
        $todos   = [];

        while ($inicio->lt($fim)) {
            $todos[] = $inicio->copy();
            $inicio->addMinutes(30);
        }

        // Remove slots já agendados (pendente ou confirmado) nessa data/profissional
        $ocupados = Agendamento::where('profissional_id', $profissional->id)
            ->whereDate('data_hora', $data)
            ->whereIn('status', ['pendente', 'confirmado'])
            ->pluck('data_hora')
            ->map(fn ($dt) => Carbon::parse($dt)->format('H:i'))
            ->toArray();

        $livres = collect($todos)
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

        return view('content.pages.detalhes_agendamento', compact('agendamento'));
    }

    /*
     * Transição: pendente → confirmado.
     */
    public function confirmar($id)
    {
        $agendamento = Agendamento::findOrFail($id);

        if (!$agendamento->isPendente()) {
            return back()->with('error', 'Apenas agendamentos pendentes podem ser confirmados.');
        }

        $agendamento->update(['status' => 'confirmado']);

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

        if ($agendamento->isRealizado() || $agendamento->isCancelado()) {
            return back()->with('error', 'Este agendamento não pode ser cancelado.');
        }

        $agendamento->update([
            'status'              => 'cancelado',
            'cancelado_por_id'    => Auth::id(),
            'motivo_cancelamento' => $request->motivo_cancelamento,
            'cancelado_em'        => now(),
        ]);

        return redirect('/agendamentos')->with('success', 'Agendamento cancelado.');
    }

    /*
     * Transição: confirmado → redirecionamento para criação de consulta.
     * O ConsultaController::store() marcará o agendamento como realizado ao criar a consulta.
     */
    public function realizar($id)
    {
        $agendamento = Agendamento::findOrFail($id);

        if (!$agendamento->isConfirmado()) {
            return back()->with('error', 'Apenas agendamentos confirmados podem ser realizados.');
        }

        return redirect('/cadastro-consulta?' . http_build_query([
            'agendamento_id'  => $agendamento->id,
            'paciente_id'     => $agendamento->paciente_id,
            'profissional_id' => $agendamento->profissional_id,
            'tipo'            => $agendamento->tipo,
        ]));
    }
}
