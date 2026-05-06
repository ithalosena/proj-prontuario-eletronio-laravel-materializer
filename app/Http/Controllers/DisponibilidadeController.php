<?php

namespace App\Http\Controllers;

use App\Models\Disponibilidade;
use App\Models\Profissional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
 * Controller: DisponibilidadeController
 *
 * Gerencia a disponibilidade semanal dos profissionais (ST-09B).
 *
 * Rotas (grupo nivel:3):
 *   GET /disponibilidade                  → edit()   [página própria — menu item]
 *   PUT /disponibilidade/{profissional}   → update() [salva via form]
 */
class DisponibilidadeController extends Controller
{
    /*
     * Exibe a página de configuração de disponibilidade do profissional logado.
     */
    public function edit()
    {
        $user         = Auth::user();
        $profissional = $user->profissional;

        if (!$profissional) {
            return redirect('/agendamentos');
        }

        $diasSemana       = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        $disponibilidades = Disponibilidade::where('profissional_id', $profissional->id)
            ->orderBy('dia_semana')
            ->get()
            ->keyBy('dia_semana');

        return view('content.pages.disponibilidade', compact('profissional', 'diasSemana', 'disponibilidades'));
    }

    /*
     * Salva a disponibilidade semanal de um profissional via upsert.
     * Profissional só pode editar a própria disponibilidade; admin pode editar qualquer uma.
     */
    public function update(Request $request, Profissional $profissional)
    {
        // Profissional só pode alterar a própria disponibilidade
        $user = Auth::user();
        if ($user->nivelAcesso() == 3 && $user->profissional?->id !== $profissional->id) {
            abort(403);
        }

        $request->validate([
            'dias'                    => ['required', 'array'],
            'dias.*.dia_semana'       => ['required', 'integer', 'min:0', 'max:6'],
            'dias.*.hora_inicio'      => ['required', 'date_format:H:i'],
            'dias.*.hora_fim'         => ['required', 'date_format:H:i', 'after:dias.*.hora_inicio'],
            'dias.*.ativo'            => ['nullable', 'boolean'],
        ]);

        foreach ($request->dias as $dia) {
            Disponibilidade::updateOrCreate(
                [
                    'profissional_id' => $profissional->id,
                    'dia_semana'      => $dia['dia_semana'],
                ],
                [
                    'hora_inicio' => $dia['hora_inicio'],
                    'hora_fim'    => $dia['hora_fim'],
                    'ativo'       => isset($dia['ativo']) ? (bool) $dia['ativo'] : false,
                ]
            );
        }

        return redirect('/disponibilidade')->with('success', 'Disponibilidade atualizada com sucesso.');
    }
}
