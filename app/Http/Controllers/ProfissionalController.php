<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfissionalRequest;
use App\Http\Requests\UpdateProfissionalRequest;
use App\Models\Profissional;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProfissionalController extends Controller
{
    public function index()
    {
        $profissionais = Profissional::with('user')->get();
        return view('content.pages.listagem_profissionais', ['profissionais' => $profissionais]);
    }

    public function create()
    {
        return view('content.pages.cadastro-profissional');
    }

    public function store(StoreProfissionalRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->nome,
                'email'    => $request->email,
                'password' => $request->senha,
            ]);

            Profissional::create([
                'user_id'               => $user->id,
                'nome'                  => $request->nome,
                'contato'               => $request->contato,
                'especialidade'         => $request->especialidade,
                'registro_profissional' => $request->registro_profissional,
            ]);
        });

        return redirect('/profissionais')->with('success', 'Profissional cadastrado com sucesso!');
    }

    public function edit($id)
    {
        $prof = Profissional::with('user')->findOrFail($id);
        return view('content.pages.editar_profissional', ['prof' => $prof]);
    }

    public function update(UpdateProfissionalRequest $request, $id)
    {
        $prof = Profissional::findOrFail($id);

        $prof->nome                  = $request->nome;
        $prof->contato               = $request->contato;
        $prof->especialidade         = $request->especialidade;
        $prof->registro_profissional = $request->registro_profissional;
        $prof->save();

        if ($prof->user) {
            $prof->user->name = $request->nome;
            if ($request->email) {
                $prof->user->email = $request->email;
            }
            $prof->user->save();
        }

        return redirect('/profissionais')->with('success', 'Profissional atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $prof = Profissional::findOrFail($id);
        $prof->delete();

        return redirect('/profissionais')->with('success', 'Profissional removido com sucesso!');
    }
}
