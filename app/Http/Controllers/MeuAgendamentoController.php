<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMeuAgendamentoRequest;
use App\Models\Agendamento;
use App\Models\Profissional;
use App\Models\TipoConsulta;
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
    public function index()
    {
        $paciente = Auth::user()->paciente;

        if (!$paciente) {
            return redirect('/')->with('error', 'Perfil de paciente não encontrado.');
        }

        $agendamentos = Agendamento::with('profissional')
            ->where('paciente_id', $paciente->id)
            ->orderBy('data_hora', 'desc')
            ->paginate(15);

        return view('content.pages.meus_agendamentos_paciente', compact('agendamentos', 'paciente'));
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

        $profissionais = Profissional::orderBy('especialidade')->orderBy('nome')->get();
        $tipos         = TipoConsulta::where('ativo', true)->orderBy('ordem')->get();

        return view('content.pages.meu_agendamento', compact('paciente', 'profissionais', 'tipos'));
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

        return redirect('/meus-agendamentos')
            ->with('success', 'Agendamento solicitado! Aguarde a confirmação do setor de saúde.');
    }

    /*
     * Cancela um agendamento do próprio paciente.
     * Só permite cancelar agendamentos futuros com status pendente ou confirmado.
     */
    public function cancelar(Request $request, $id)
    {
        $paciente    = Auth::user()->paciente;
        $agendamento = Agendamento::where('paciente_id', $paciente?->id)->findOrFail($id);

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

        return back()->with('success', 'Agendamento cancelado.');
    }
}
