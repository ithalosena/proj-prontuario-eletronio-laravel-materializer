<?php

namespace App\Http\Controllers;

use App\Models\AgendaConfig;
use App\Models\Agendamento;
use App\Models\DisponibilidadeBloco;
use App\Models\DisponibilidadeExcecao;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/*
 * Controller: DisponibilidadeController
 *
 * Gerencia a disponibilidade semanal, exceções e configurações de agenda do profissional.
 *
 * Rotas (grupo nivel:3):
 *   GET    /disponibilidade                    → index()         [página principal]
 *   POST   /disponibilidade                    → store()         [salva config + blocos]
 *   POST   /disponibilidade/excecoes           → storeExcecao()  [adiciona exceção via modal]
 *   DELETE /disponibilidade/excecoes/{id}      → destroyExcecao()[remove exceção]
 */
class DisponibilidadeController extends Controller
{
    /*
     * Exibe a página de disponibilidade com config, blocos por dia, preview e exceções.
     */
    public function index()
    {
        $user         = Auth::user();
        $profissional = $user->profissional;

        if (!$profissional) {
            return redirect('/agendamentos');
        }

        $diasNomes = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

        // Blocos agrupados por dia_semana, ordenados por hora
        $blocos = DisponibilidadeBloco::where('profissional_id', $profissional->id)
            ->orderBy('hora_inicio')
            ->get()
            ->groupBy('dia_semana');

        // Config de agenda (ou objeto com defaults para o form)
        $config = AgendaConfig::firstOrNew(
            ['profissional_id' => $profissional->id],
            [
                'duracao_minutos'            => 30,
                'buffer_minutos'             => 0,
                'antecedencia_minima_horas'  => 1,
                'antecedencia_maxima_dias'   => 60,
                'aceitar_agendamentos_online' => true,
                'reservar_horarios_encaixe'  => false,
                'turno_manha_inicio'         => '08:00',
                'turno_manha_fim'            => '12:00',
                'turno_tarde_inicio'         => '14:00',
                'turno_tarde_fim'            => '18:00',
                'turno_noite_inicio'         => '19:00',
                'turno_noite_fim'            => '22:00',
            ]
        );

        // Exceções futuras (a partir de hoje)
        $excecoes = DisponibilidadeExcecao::where('profissional_id', $profissional->id)
            ->where('data_fim', '>=', now()->toDateString())
            ->orderBy('data_inicio')
            ->get();

        // Dados da semana atual para o preview
        $inicioSemana = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $fimSemana    = $inicioSemana->copy()->endOfWeek(Carbon::SUNDAY);

        $agendamentosSemana = Agendamento::where('profissional_id', $profissional->id)
            ->whereBetween('data_hora', [$inicioSemana, $fimSemana])
            ->whereIn('status', ['pendente', 'confirmado', 'realizado'])
            ->get();

        $excecoesSemana = DisponibilidadeExcecao::where('profissional_id', $profissional->id)
            ->where('data_inicio', '<=', $fimSemana->toDateString())
            ->where('data_fim',    '>=', $inicioSemana->toDateString())
            ->get();

        return view('content.pages.disponibilidade', compact(
            'profissional', 'diasNomes', 'blocos', 'config', 'excecoes',
            'agendamentosSemana', 'excecoesSemana', 'inicioSemana'
        ));
    }

    /*
     * Salva as configurações de agenda e os blocos semanais num único submit.
     * Antes de persistir, verifica conflitos com agendamentos futuros.
     */
    public function store(Request $request)
    {
        $profissional = Auth::user()->profissional;
        if (!$profissional) {
            abort(403);
        }

        $request->validate([
            'config.duracao_minutos'           => ['required', 'integer', 'in:15,30,45,60'],
            'config.buffer_minutos'            => ['required', 'integer', 'in:0,5,10,15'],
            'config.antecedencia_minima_horas' => ['required', 'integer', 'in:1,2,4,8,24,48'],
            'config.antecedencia_maxima_dias'  => ['required', 'integer', 'in:30,60,90'],
            'config.turno_manha_inicio'        => ['required', 'date_format:H:i'],
            'config.turno_manha_fim'           => ['required', 'date_format:H:i', 'after:config.turno_manha_inicio'],
            'config.turno_tarde_inicio'        => ['required', 'date_format:H:i'],
            'config.turno_tarde_fim'           => ['required', 'date_format:H:i', 'after:config.turno_tarde_inicio'],
            'config.turno_noite_inicio'        => ['required', 'date_format:H:i'],
            'config.turno_noite_fim'           => ['required', 'date_format:H:i', 'after:config.turno_noite_inicio'],
        ]);

        // Monta array de novos blocos a partir do POST
        $novosBlocos = [];
        for ($dia = 0; $dia <= 6; $dia++) {
            // Dia sem toggle ativo não gera blocos
            if (!$request->input("dias.{$dia}.ativo")) {
                continue;
            }
            $inicios = $request->input("dias.{$dia}.blocos.hora_inicio", []);
            $fins    = $request->input("dias.{$dia}.blocos.hora_fim",    []);

            foreach ($inicios as $i => $inicio) {
                $fim = $fins[$i] ?? null;
                if (!$inicio || !$fim) {
                    continue;
                }
                if ($inicio >= $fim) {
                    continue; // bloco inválido — início >= fim, ignorado
                }
                $novosBlocos[] = [
                    'profissional_id' => $profissional->id,
                    'dia_semana'      => $dia,
                    'hora_inicio'     => $inicio,
                    'hora_fim'        => $fim,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
        }

        // Detecção de conflitos: agendamentos futuros fora da nova grade
        $conflitos = $this->detectarConflitos($profissional->id, $novosBlocos);
        if ($conflitos->isNotEmpty()) {
            $lista = $conflitos
                ->map(fn ($ag) => Carbon::parse($ag->data_hora)->format('d/m H:i') . ' – ' . ($ag->paciente->nome ?? '?'))
                ->implode(', ');
            return back()
                ->with('warning', "Existem {$conflitos->count()} agendamento(s) que ficariam fora da nova disponibilidade: {$lista}. Ajuste ou cancele-os antes de salvar.")
                ->withInput();
        }

        DB::transaction(function () use ($request, $profissional, $novosBlocos) {
            // Salva configuração de agenda (inclui toggles e horas padrão dos turnos)
            AgendaConfig::updateOrCreate(
                ['profissional_id' => $profissional->id],
                [
                    'duracao_minutos'            => $request->input('config.duracao_minutos'),
                    'buffer_minutos'             => $request->input('config.buffer_minutos'),
                    'antecedencia_minima_horas'  => $request->input('config.antecedencia_minima_horas'),
                    'antecedencia_maxima_dias'   => $request->input('config.antecedencia_maxima_dias'),
                    'aceitar_agendamentos_online' => $request->boolean('config.aceitar_agendamentos_online'),
                    'reservar_horarios_encaixe'  => $request->boolean('config.reservar_horarios_encaixe'),
                    'turno_manha_inicio'         => $request->input('config.turno_manha_inicio'),
                    'turno_manha_fim'            => $request->input('config.turno_manha_fim'),
                    'turno_tarde_inicio'         => $request->input('config.turno_tarde_inicio'),
                    'turno_tarde_fim'            => $request->input('config.turno_tarde_fim'),
                    'turno_noite_inicio'         => $request->input('config.turno_noite_inicio'),
                    'turno_noite_fim'            => $request->input('config.turno_noite_fim'),
                ]
            );

            // Substitui todos os blocos do profissional
            DisponibilidadeBloco::where('profissional_id', $profissional->id)->delete();
            if (!empty($novosBlocos)) {
                DisponibilidadeBloco::insert($novosBlocos);
            }
        });

        return redirect('/disponibilidade')->with('success', 'Disponibilidade atualizada com sucesso.');
    }

    /*
     * Adiciona uma exceção pontual (via modal na página de disponibilidade).
     */
    public function storeExcecao(Request $request)
    {
        $profissional = Auth::user()->profissional;
        if (!$profissional) {
            abort(403);
        }

        $request->validate([
            'tipo'        => ['required', 'in:bloqueio,disponivel_extra'],
            'data_inicio' => ['required', 'date', 'after_or_equal:today'],
            'data_fim'    => ['required', 'date', 'gte:data_inicio'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fim'    => ['nullable', 'date_format:H:i', 'after:hora_inicio'],
            'motivo'      => ['nullable', 'string', 'max:255'],
        ]);

        DisponibilidadeExcecao::create([
            'profissional_id' => $profissional->id,
            'tipo'            => $request->tipo,
            'data_inicio'     => $request->data_inicio,
            'data_fim'        => $request->data_fim,
            'hora_inicio'     => $request->hora_inicio ?: null,
            'hora_fim'        => $request->hora_fim    ?: null,
            'motivo'          => $request->motivo,
        ]);

        return redirect('/disponibilidade')->with('success', 'Exceção adicionada.');
    }

    /*
     * Atualiza uma exceção existente (via modal de edição).
     */
    public function updateExcecao(Request $request, int $id)
    {
        $profissional = Auth::user()->profissional;
        if (!$profissional) {
            abort(403);
        }

        $request->validate([
            'tipo'        => ['required', 'in:bloqueio,disponivel_extra'],
            'data_inicio' => ['required', 'date'],
            'data_fim'    => ['required', 'date', 'gte:data_inicio'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fim'    => ['nullable', 'date_format:H:i', 'after:hora_inicio'],
            'motivo'      => ['nullable', 'string', 'max:255'],
        ]);

        DisponibilidadeExcecao::where('profissional_id', $profissional->id)
            ->findOrFail($id)
            ->update([
                'tipo'        => $request->tipo,
                'data_inicio' => $request->data_inicio,
                'data_fim'    => $request->data_fim,
                'hora_inicio' => $request->hora_inicio ?: null,
                'hora_fim'    => $request->hora_fim    ?: null,
                'motivo'      => $request->motivo,
            ]);

        return redirect('/disponibilidade')->with('success', 'Exceção atualizada.');
    }

    /*
     * Remove uma exceção pontual (profissional só remove as suas).
     */
    public function destroyExcecao(int $id)
    {
        $profissional = Auth::user()->profissional;
        if (!$profissional) {
            abort(403);
        }

        DisponibilidadeExcecao::where('profissional_id', $profissional->id)
            ->findOrFail($id)
            ->delete();

        return redirect('/disponibilidade')->with('success', 'Exceção removida.');
    }

    /*
     * Verifica agendamentos futuros (pendente/confirmado) que ficariam descobertos
     * pela nova grade de blocos. Retorna a collection dos conflitantes.
     */
    private function detectarConflitos(int $profissionalId, array $novosBlocos): \Illuminate\Support\Collection
    {
        $agendamentos = Agendamento::with('paciente')
            ->where('profissional_id', $profissionalId)
            ->whereIn('status', ['pendente', 'confirmado'])
            ->where('data_hora', '>', now())
            ->get();

        return $agendamentos->filter(function ($ag) use ($novosBlocos) {
            $diaSemana = Carbon::parse($ag->data_hora)->dayOfWeek;
            $horaAg    = Carbon::parse($ag->data_hora)->format('H:i');

            foreach ($novosBlocos as $bloco) {
                if ((int) $bloco['dia_semana'] === $diaSemana
                    && $horaAg >= substr($bloco['hora_inicio'], 0, 5)
                    && $horaAg <  substr($bloco['hora_fim'],    0, 5)
                ) {
                    return false; // coberto — não é conflito
                }
            }
            return true; // não coberto por nenhum bloco → conflito
        });
    }
}
