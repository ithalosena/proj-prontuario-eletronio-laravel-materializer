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
        $podeEditarSensivel = Auth::user()->nivelAcesso() <= 2;
        return view('content.pages.editar_paciente', compact('paciente', 'podeEditarSensivel'));
    }

    public function update(UpdatePacienteRequest $request, $id)
    {
        $paciente = Paciente::findOrFail($id);
        $podeEditarSensivel = Auth::user()->nivelAcesso() <= 2;

        // Campos complementares — editáveis por qualquer role autenticada (UX-24, nivel ≤ 4)
        // UX-03 (v0.10.1): inclui endereço estruturado, contatos extras, dados complementares,
        // emergência e responsável legal (campos adicionados no onboarding ST-15).
        // Dados CLÍNICOS (tipo_sanguineo, alergias, etc.) NÃO entram aqui — são read-only nesta
        // tela e serão editados pelo profissional na consulta (ST-17).
        $paciente->fill($request->only([
            'contato', 'telefone_alternativo', 'email_alternativo',
            'endereco', 'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf', 'ponto_referencia',
            'nome_social', 'naturalidade_cidade', 'naturalidade_uf', 'raca_cor', 'estado_civil', 'nome_mae',
            'contato_emergencia_nome', 'contato_emergencia_telefone', 'contato_emergencia_parentesco',
            'contato_emergencia2_nome', 'contato_emergencia2_telefone', 'contato_emergencia2_parentesco',
            'responsavel_nome', 'responsavel_cpf', 'responsavel_telefone', 'responsavel_email', 'responsavel_parentesco',
        ]));

        // Campos sensíveis — somente admin/gerente (nivel <= 2)
        if ($podeEditarSensivel) {
            $paciente->nome            = $request->nome;
            $paciente->documento       = $request->documento;
            $paciente->data_nascimento = $request->data_nascimento;
            $paciente->sexo            = $request->sexo;
            $paciente->matricula       = $request->matricula;
            $paciente->curso           = $request->curso;

            if ($paciente->user) {
                $paciente->user->name = $request->nome;
                $paciente->user->save();
            }
        }

        $paciente->save();

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

        // UX-04 (v0.10.1): 5 atendimentos mais recentes do paciente para o mini-card
        // (eager load do profissional para evitar N+1 ao exibir nome/especialidade)
        $atendimentosRecentes = $paciente->atendimentos()
            ->with('profissional')
            ->latest()
            ->take(5)
            ->get();

        return view('content.pages.detalhes_paciente', compact(
            'paciente', 'totalConsultas', 'totalExames', 'totalPrescricoes', 'atendimentosRecentes'
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

    /*
     * L-06 (LGPD Art. 18, V): exporta todos os dados do paciente autenticado em JSON.
     * Retorna download direto — sem view, sem banco adicional.
     */
    public function exportarDados()
    {
        $paciente = Auth::user()->paciente;

        if (!$paciente) {
            return redirect('/')->with('error', 'Seu usuário não possui um perfil de paciente vinculado.');
        }

        $paciente->load([
            'user',
            'atendimentos.profissional',
            'atendimentos.consultas.exames',
            'atendimentos.consultas.prescricoes',
            'agendamentos.profissional',
            'consentimentos',
        ]);

        // Monta estrutura de exportação por seção (Art. 18, V exige formato interoperável)
        $payload = [
            'exportado_em'  => now()->toIso8601String(),
            'sistema'       => 'Prontu IF — IFNMG',
            'versao_lgpd'   => 'Lei nº 13.709/2018, Art. 18, V',
            'titular'       => [
                'nome'            => $paciente->nome,
                'email'           => $paciente->user->email,
                'documento'       => $paciente->documento,
                'data_nascimento' => $paciente->data_nascimento,
                'sexo'            => $paciente->sexo,
                'matricula'       => $paciente->matricula,
                'curso'           => $paciente->curso,
                'contato'         => $paciente->contato,
                'endereco'        => $paciente->endereco,
                'cadastrado_em'   => $paciente->created_at,
            ],
            'consentimentos' => $paciente->consentimentos->map(fn($c) => [
                'versao_termo' => $c->versao_termo,
                'aceito_em'    => $c->aceito_em,
                'ip_address'   => $c->ip_address,
            ])->toArray(),
            'atendimentos' => $paciente->atendimentos->map(fn($a) => [
                'id'             => $a->id,
                'status'         => $a->status,
                'profissional'   => $a->profissional->nome ?? null,
                'especialidade'  => $a->profissional->especialidade ?? null,
                'created_at'     => $a->created_at,
                'fechado_em'     => $a->fechado_em,
                'consultas'      => $a->consultas->map(fn($c) => [
                    'id'          => $c->id,
                    'data_hora'   => $c->data_hora,
                    'tipo'        => $c->tipo,
                    'queixa'      => $c->queixa,
                    'anamnese'    => $c->anamnese,
                    'diagnostico' => $c->diagnostico,
                    'conduta'     => $c->conduta,
                    'exames'      => $c->exames->map(fn($e) => [
                        'tipo'              => $e->tipo,
                        'data_solicitacao'  => $e->data_solicitacao,
                        'data_resultado'    => $e->data_resultado,
                        'resultado'         => $e->resultado,
                        'observacao'        => $e->observacao,
                    ])->toArray(),
                    'prescricoes' => $c->prescricoes->map(fn($p) => [
                        'nome_medicamento' => $p->nome_medicamento,
                        'dosagem'          => $p->dosagem,
                        'frequencia'       => $p->frequencia,
                        'duracao'          => $p->duracao,
                        'observacao'       => $p->observacao,
                    ])->toArray(),
                ])->toArray(),
            ])->toArray(),
            'agendamentos' => $paciente->agendamentos->map(fn($ag) => [
                'id'                  => $ag->id,
                'data_hora'           => $ag->data_hora,
                'tipo'                => $ag->tipo,
                'status'              => $ag->status,
                'profissional'        => $ag->profissional->nome ?? null,
                'observacao'          => $ag->observacao,
                'motivo_cancelamento' => $ag->motivo_cancelamento,
            ])->toArray(),
        ];

        $nomeArquivo = 'prontuif_meus_dados_' . now()->format('Ymd_His') . '.json';

        return response()->json($payload, 200, [
            'Content-Disposition' => "attachment; filename=\"{$nomeArquivo}\"",
            'Content-Type'        => 'application/json; charset=utf-8',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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
