<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Atendimento;
use App\Models\AuditLog;
use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Prescricao;
use App\Models\Profissional;
use App\Services\RelatorioConformidadeService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * Controller principal de dashboard.
 * Despacha para a view correta com base no nível de acesso do usuário autenticado.
 * O dispatch evita 5 rotas separadas mantendo toda a lógica de seleção em um lugar só.
 */
class DashboardController extends Controller
{
    // =========================================================
    // Dispatch — rota GET /
    // =========================================================

    public function index(): View|\Illuminate\Http\RedirectResponse
    {
        $nivel = Auth::user()->nivelAcesso();

        return match ($nivel) {
            1 => $this->admin(),
            2 => $this->coordenador(),
            3 => $this->profissional(),
            4 => $this->recepcionista(),
            5 => $this->paciente(),
            default => redirect('/'),
        };
    }

    // =========================================================
    // Dashboard Admin (nivel 1)
    // =========================================================

    private function admin(): View
    {
        // KPIs globais
        $totalPacientes      = Paciente::count();
        $totalProfissionais  = Profissional::count();
        $atendimentosAbertos = Atendimento::where('status', 'aberto')->count();
        $consultasNoMes      = Consulta::whereMonth('data_hora', now()->month)
                                       ->whereYear('data_hora', now()->year)
                                       ->count();

        // Audit log recente — eager load user para evitar N+1
        $auditLogs = AuditLog::with('user')->latest()->take(5)->get();

        // Widget LGPD — 4 buckets agregados sem PII
        /** @var RelatorioConformidadeService $service */
        $service       = app(RelatorioConformidadeService::class);
        $conformidade  = $service->calcular();
        // TODO produção: Cache::remember('lgpd_conformidade', 300, fn() => $service->calcular())
        // quando CACHE_DRIVER for redis ou database (atualmente array não persiste entre requests)

        return view('content.pages.dashboard_admin', compact(
            'totalPacientes',
            'totalProfissionais',
            'atendimentosAbertos',
            'consultasNoMes',
            'auditLogs',
            'conformidade',
        ));
    }

    // =========================================================
    // Dashboard Coordenador (nivel 2)
    // =========================================================

    private function coordenador(): View
    {
        // KPIs de operação clínica
        $totalProfissionais = Profissional::count();

        $consultasHoje = Consulta::whereDate('data_hora', today())->count();

        $pacientesNoMes = Agendamento::whereMonth('data_hora', now()->month)
                                     ->whereYear('data_hora', now()->year)
                                     ->distinct()
                                     ->count('paciente_id');

        // Taxa de ocupação: (confirmados + realizados) / (pendentes + confirmados + realizados) no mês
        $agendamentosMes = Agendamento::whereMonth('data_hora', now()->month)
                                      ->whereYear('data_hora', now()->year)
                                      ->whereNotIn('status', ['cancelado'])
                                      ->count();
        $agendamentosAtivos = Agendamento::whereMonth('data_hora', now()->month)
                                         ->whereYear('data_hora', now()->year)
                                         ->whereIn('status', ['confirmado', 'realizado'])
                                         ->count();
        $taxaOcupacao = $agendamentosMes > 0
            ? round(($agendamentosAtivos / $agendamentosMes) * 100)
            : 0;

        // Distribuição de agendamentos por status no mês — 1 query com GROUP BY
        $statusCounts = Agendamento::whereMonth('data_hora', now()->month)
            ->whereYear('data_hora', now()->year)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Top 5 profissionais por consultas no mês
        $top5Profissionais = Profissional::withCount([
            'consultas' => fn($q) => $q->whereMonth('data_hora', now()->month)
                                       ->whereYear('data_hora', now()->year),
        ])->orderByDesc('consultas_count')->take(5)->get();

        // Agendamentos pendentes (aguardando confirmação) — substitui bloco "Aprovações" do mockup
        $pendentesConfirmacao = Agendamento::where('status', 'pendente')
            ->where('data_hora', '>=', now())
            ->with('paciente', 'profissional')
            ->orderBy('data_hora')
            ->take(5)
            ->get();

        return view('content.pages.dashboard_coordenador', compact(
            'totalProfissionais',
            'consultasHoje',
            'pacientesNoMes',
            'taxaOcupacao',
            'statusCounts',
            'top5Profissionais',
            'pendentesConfirmacao',
        ));
    }

    // =========================================================
    // Dashboard Profissional (nivel 3)
    // =========================================================

    private function profissional(): View
    {
        $profissional = Auth::user()->profissional;

        // Próximo agendamento confirmado — eager load paciente para evitar N+1
        $proximoAgendamento = Agendamento::where('profissional_id', $profissional->id)
            ->where('status', 'confirmado')
            ->where('data_hora', '>=', now())
            ->with('paciente')
            ->orderBy('data_hora')
            ->first();

        // Stats do período
        $consultasHoje     = Consulta::where('profissional_id', $profissional->id)
                                     ->whereDate('data_hora', today())
                                     ->count();

        $pacientesSemana   = Agendamento::where('profissional_id', $profissional->id)
                                        ->whereBetween('data_hora', [
                                            now()->startOfWeek(),
                                            now()->endOfWeek(),
                                        ])
                                        ->distinct()
                                        ->count('paciente_id');

        $atendimentosAbertos = Atendimento::where('profissional_id', $profissional->id)
                                          ->where('status', 'aberto')
                                          ->count();

        // Agenda da semana — lista simples com eager load (N+1 eliminado)
        $agendaSemana = Agendamento::where('profissional_id', $profissional->id)
            ->whereBetween('data_hora', [now()->startOfWeek(), now()->endOfWeek()])
            ->with('paciente')
            ->orderBy('data_hora')
            ->get();

        // Últimas 5 consultas registradas pelo profissional — eager load paciente
        $ultimasConsultas = Consulta::where('profissional_id', $profissional->id)
            ->with('paciente')
            ->latest('data_hora')
            ->take(5)
            ->get();

        return view('content.pages.dashboard_profissional', compact(
            'profissional',
            'proximoAgendamento',
            'consultasHoje',
            'pacientesSemana',
            'atendimentosAbertos',
            'agendaSemana',
            'ultimasConsultas',
        ));
    }

    // =========================================================
    // Dashboard Recepcionista (nivel 4)
    // =========================================================

    private function recepcionista(): View
    {
        // Próximos check-ins: confirmados de hoje a partir de agora — eager load para evitar N+1
        $proximosCheckins = Agendamento::whereDate('data_hora', today())
            ->where('data_hora', '>=', now())
            ->where('status', 'confirmado')
            ->with('paciente', 'profissional')
            ->orderBy('data_hora')
            ->take(4)
            ->get();

        // Stats do balcão
        $agendamentosHoje       = Agendamento::whereDate('data_hora', today())->count();
        $confirmacoesPendentes  = Agendamento::where('status', 'pendente')
                                             ->whereDate('data_hora', now()->addDay())
                                             ->count();
        $cancelamentosHoje      = Agendamento::whereDate('data_hora', today())
                                             ->where('status', 'cancelado')
                                             ->count();

        // Chegadas do dia completo — ordenado por horário
        $chegadasDoDia = Agendamento::whereDate('data_hora', today())
            ->with('paciente', 'profissional')
            ->orderBy('data_hora')
            ->get();

        // Agendamentos pendentes para amanhã ("confirmações a fazer")
        $confirmacoesFazer = Agendamento::where('status', 'pendente')
            ->whereDate('data_hora', now()->addDay())
            ->with('paciente', 'profissional')
            ->orderBy('data_hora')
            ->take(5)
            ->get();

        return view('content.pages.dashboard_recepcionista', compact(
            'proximosCheckins',
            'agendamentosHoje',
            'confirmacoesPendentes',
            'cancelamentosHoje',
            'chegadasDoDia',
            'confirmacoesFazer',
        ));
    }

    // =========================================================
    // Dashboard Paciente (nivel 5)
    // =========================================================

    private function paciente(): View
    {
        $paciente = Auth::user()->paciente;

        // Próxima consulta confirmada — eager load profissional
        $proximaConsulta = Agendamento::where('paciente_id', $paciente->id)
            ->where('status', 'confirmado')
            ->where('data_hora', '>=', now())
            ->with('profissional')
            ->orderBy('data_hora')
            ->first();

        // Histórico — últimas 5 consultas com nome do profissional
        $historicoConsultas = Consulta::where('paciente_id', $paciente->id)
            ->with('profissional')
            ->latest('data_hora')
            ->take(5)
            ->get();

        // Prescrições recentes — 5 mais recentes via hasManyThrough (JOIN consultas)
        // Sem campo status no schema atual; exibe as mais recentes como "recentes"
        $prescricoesRecentes = $paciente->prescricoes()
            ->with('consulta.profissional')
            ->latest()
            ->take(5)
            ->get();

        return view('content.pages.dashboard_paciente', compact(
            'paciente',
            'proximaConsulta',
            'historicoConsultas',
            'prescricoesRecentes',
        ));
    }
}
