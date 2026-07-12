<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMeuAgendamentoRequest;
use App\Models\Agendamento;
use App\Models\Especialidade;
use App\Models\Profissional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
 * Controller: MeuAgendamentoController
 *
 * Gerencia o agendamento pelo próprio paciente (nivel 5).
 * O paciente só pode criar e cancelar os próprios agendamentos.
 *
 * Rotas (grupo nivel:5):
 *   GET   /meus-agendamentos               → index()
 *   GET   /agendar-consulta                → create()
 *   POST  /agendar-consulta                → store()
 *   PATCH /meus-agendamentos/{id}/cancelar → cancelar()
 */
class MeuAgendamentoController extends Controller
{
    /*
     * Lista os agendamentos do paciente logado, do mais recente para o mais antigo.
     */
    public function index(Request $request)
    {
        $paciente = Auth::user()->paciente;

        if (!$paciente) {
            return redirect('/')->with('error', 'Perfil de paciente não encontrado.');
        }

        // C.6.3 (v0.10.3): filtro por status + ordenação por data (GET, preserváveis na URL)
        $filtroStatus = $request->query('status');
        $ordenar      = $request->query('ordenar') === 'data_asc' ? 'data_asc' : 'data_desc';

        $query = Agendamento::with('profissional')->where('paciente_id', $paciente->id);

        if (in_array($filtroStatus, ['pendente', 'confirmado', 'realizado', 'cancelado'], true)) {
            $query->where('status', $filtroStatus);
        }

        $query->orderBy('data_hora', $ordenar === 'data_asc' ? 'asc' : 'desc');

        $agendamentos = $query->paginate(15)->withQueryString();

        return view('content.pages.meus_agendamentos_paciente', compact('agendamentos', 'paciente', 'filtroStatus', 'ordenar'));
    }

    /*
     * Exibe o wizard de agendamento (4 passos, single page, mobile-first).
     * Passa profissionais e tipos para os steps 1 e 2.
     */
    public function create()
    {
        $paciente = Auth::user()->paciente;

        if (!$paciente) {
            return redirect('/')->with('error', 'Perfil de paciente não encontrado.');
        }

        $profissionais  = Profissional::orderBy('especialidade')->orderBy('nome')->get();
        // v0.10.3+: passo 1 do wizard = especialidade (não mais tipo de consulta)
        $especialidades = Especialidade::ativo()->ordenado()->get();

        return view('content.pages.meu_agendamento', compact('paciente', 'profissionais', 'especialidades'));
    }

    /*
     * Persiste o agendamento criado pelo paciente com status=pendente.
     */
    public function store(StoreMeuAgendamentoRequest $request)
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

        // Notifica o profissional sobre o novo agendamento
        $agendamento->profissional->user?->notify(new \App\Notifications\NovoAgendamentoNotification($agendamento));

        return redirect('/meus-agendamentos')
            ->with('success', 'Agendamento solicitado! Aguarde a confirmação do setor de saúde ou pelo profissional de saúde responsável.');
    }

    /*
     * Cancela um agendamento do próprio paciente.
     * Só permite cancelar agendamentos futuros com status pendente ou confirmado.
     */
    public function cancelar(Request $request, $id)
    {
        $paciente = Auth::user()->paciente;

        // S-08: guard explícito — se o usuário não tiver perfil de paciente, a query retornaria
        // WHERE paciente_id = NULL, que pode não lançar 404 corretamente dependendo do driver
        if (!$paciente) {
            return redirect('/')->with('error', 'Perfil de paciente não encontrado.');
        }

        $agendamento = Agendamento::where('paciente_id', $paciente->id)->findOrFail($id);

        if ($agendamento->isRealizado() || $agendamento->isCancelado()) {
            return back()->with('error', 'Este agendamento não pode ser cancelado.');
        }

        if ($agendamento->data_hora->isPast()) {
            return back()->with('error', 'Não é possível cancelar um agendamento passado.');
        }

        $agendamento->update([
            'status'              => 'cancelado',
            'cancelado_por_id'    => Auth::id(),
            'motivo_cancelamento' => 'Cancelado pelo paciente',
            'cancelado_em'        => now(),
        ]);

        // Notifica o profissional que o paciente cancelou
        $agendamento->profissional->user?->notify(new \App\Notifications\AgendamentoCanceladoNotification($agendamento, Auth::user()));

        return back()->with('success', 'Agendamento cancelado.');
    }
}
