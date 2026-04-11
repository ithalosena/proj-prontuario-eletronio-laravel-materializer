<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PacienteController extends Controller
{
    public function index()
    {
        $pacientes = Paciente::with('user')->get();
        return view('content.pages.listagem_pacientes', ['pacientes' => $pacientes]);
    }

    public function create()
    {
        return view('content.pages.cadastro-paciente');
    }

    public function store(StorePacienteRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->nome,
                'email'    => $request->email,
                'password' => $request->senha,
            ]);

            Paciente::create([
                'user_id'         => $user->id,
                'nome'            => $request->nome,
                'contato'         => $request->contato,
                'documento'       => $request->documento,
                'data_nascimento' => $request->data_nascimento,
                'sexo'            => $request->sexo,
                'endereco'        => $request->endereco,
                'matricula'       => $request->matricula,
                'curso'           => $request->curso,
            ]);
        });

        return redirect('/pacientes')->with('success', 'Paciente cadastrado com sucesso!');
    }

    public function edit($id)
    {
        $paciente = Paciente::with('user')->findOrFail($id);
        return view('content.pages.editar_paciente', ['paciente' => $paciente]);
    }

    public function update(UpdatePacienteRequest $request, $id)
    {
        $paciente = Paciente::findOrFail($id);

        $paciente->nome            = $request->nome;
        $paciente->contato         = $request->contato;
        $paciente->documento       = $request->documento;
        $paciente->data_nascimento = $request->data_nascimento;
        $paciente->sexo            = $request->sexo;
        $paciente->endereco        = $request->endereco;
        $paciente->matricula       = $request->matricula;
        $paciente->curso           = $request->curso;
        $paciente->save();

        if ($paciente->user) {
            $paciente->user->name = $request->nome;
            $paciente->user->save();
        }

        return redirect('/pacientes')->with('success', 'Paciente atualizado com sucesso!');
    }

    public function historico($id)
    {
        $paciente = Paciente::findOrFail($id);
        $consultas = $paciente->consultas()
            ->with('profissional', 'exames', 'prescricoes')
            ->orderBy('data_hora', 'desc')
            ->get();

        return view('content.pages.historico_paciente', compact('paciente', 'consultas'));
    }

    public function destroy($id)
    {
        $paciente = Paciente::findOrFail($id);
        $paciente->delete();

        return redirect('/pacientes')->with('success', 'Paciente removido com sucesso!');
    }
}
