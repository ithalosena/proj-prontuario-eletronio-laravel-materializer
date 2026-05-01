<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;
use App\Models\Atendimento;
use App\Models\Paciente;
use App\Models\User;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PacienteController extends Controller
{
    public function index()
    {
        $busca = request('busca');

        $pacientes = Paciente::with(['user', 'ultimoAtendimento.profissional'])
            ->when($busca, fn($q) => $q
                ->where('nome', 'like', "%{$busca}%")
                ->orWhere('matricula', 'like', "%{$busca}%")
            )
            ->orderBy('nome')
            ->paginate(15)
            ->appends(['busca' => $busca]);

        // Indicadores para o topo da página
        $totalPacientes = Paciente::count();

        // Atendimentos abertos do profissional logado (apenas para nivel 3 com perfil)
        $atendimentosAbertos = null;
        $profissional = Auth::user()->profissional;
        if ($profissional && Auth::user()->nivelAcesso() == 3) {
            $atendimentosAbertos = Atendimento::where('profissional_id', $profissional->id)
                ->where('status', 'aberto')
                ->count();
        }

        return view('content.pages.listagem_pacientes', compact(
            'pacientes', 'busca', 'totalPacientes', 'atendimentosAbertos'
        ));
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

    /*
     * UX-14: Exibe o perfil do paciente com estatísticas agregadas.
     * Separa "quem é o paciente" (dados pessoais + stats) de "o que aconteceu" (historico completo).
     * Contadores usam queries diretas para evitar carregar todas as consultas em memória.
     */
    public function show($id)
    {
        $paciente = Paciente::with('user')->findOrFail($id);

        $totalConsultas   = $paciente->consultas()->count();
        $totalExames      = \App\Models\Exame::whereHas(
            'consulta', fn($q) => $q->where('paciente_id', $id)
        )->count();
        $totalPrescricoes = \App\Models\Prescricao::whereHas(
            'consulta', fn($q) => $q->where('paciente_id', $id)
        )->count();

        return view('content.pages.detalhes_paciente', compact(
            'paciente', 'totalConsultas', 'totalExames', 'totalPrescricoes'
        ));
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

    public function meuProntuario()
    {
        $paciente = Auth::user()->paciente;

        if (!$paciente) {
            return redirect('/')->with('error', 'Seu usuário não possui um perfil de paciente vinculado.');
        }

        $consultas = $paciente->consultas()
            ->with('profissional', 'exames', 'prescricoes')
            ->orderBy('data_hora', 'desc')
            ->get();

        return view('content.pages.meu_prontuario', compact('paciente', 'consultas'));
    }

    public function destroy($id)
    {
        $paciente = Paciente::findOrFail($id);
        $paciente->delete();

        return redirect('/pacientes')->with('success', 'Paciente removido com sucesso!');
    }

    /*
     * Endpoint AJAX de autocomplete de pacientes.
     * Consumido pelo componente de busca em cadastro_atendimento e cadastro-consulta.
     * Retorna até 10 pacientes cujo nome ou matrícula contenha o termo (?q=).
     */
    public function buscar(Request $request): JsonResponse
    {
        return response()->json(
            app(SearchService::class)->autocomplete(
                Paciente::class,
                $request->input('q', ''),
                ['nome', 'matricula'],
                ['id', 'nome', 'matricula']
            )
        );
    }
}
