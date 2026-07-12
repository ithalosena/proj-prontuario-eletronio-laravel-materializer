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
            'responsavel_nome', 'responsavel_telefone', 'responsavel_email', 'responsavel_parentesco',
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

        // UX-P04 (v0.10.2): se o usuário logado é profissional e já tem um atendimento
        // ABERTO com este paciente, o hero mostra "Continuar Atendimento" em vez de "Iniciar".
        $atendimentoAbertoDoProfissional = null;
        if (Auth::user()->profissional) {
            $atendimentoAbertoDoProfissional = $paciente->atendimentos()
                ->where('profissional_id', Auth::user()->profissional->id)
                ->where('status', 'aberto')
                ->latest()
                ->first();
        }

        return view('content.pages.detalhes_paciente', compact(
            'paciente', 'totalConsultas', 'totalExames', 'totalPrescricoes',
            'atendimentosRecentes', 'atendimentoAbertoDoProfissional'
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

        // v0.10.5: abas separadas de Exames e Prescrições no Meu Prontuário.
        // hasManyThrough faz JOIN com consultas → qualificamos created_at (coluna ambígua no MySQL).
        $exames = $paciente->exames()
            ->with('consulta.profissional')
            ->orderByDesc('exames.created_at')
            ->get();

        $prescricoes = $paciente->prescricoes()
            ->with('consulta.profissional')
            ->orderByDesc('prescricoes.created_at')
            ->get();

        return view('content.pages.meu_prontuario', compact('paciente', 'consultas', 'exames', 'prescricoes'));
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
            // Art. 18, V — portabilidade exige TODOS os dados do titular. Inclui os campos
            // estruturados do ST-15 (identidade, endereço, complementares, contatos, emergência,
            // responsável) além dos autorrelatados de saúde (bloco 'dados_de_saude' abaixo).
            'titular'       => [
                // Identidade
                'nome'            => $paciente->nome,
                'nome_social'     => $paciente->nome_social,
                'email'           => $paciente->user->email,
                'email_alternativo' => $paciente->email_alternativo,
                'documento'       => $paciente->documento,
                'data_nascimento' => $paciente->data_nascimento,
                'sexo'            => $paciente->sexo,
                'matricula'       => $paciente->matricula,
                'curso'           => $paciente->curso,
                'cadastrado_em'   => $paciente->created_at,
                // Complementares
                'naturalidade_cidade' => $paciente->naturalidade_cidade,
                'naturalidade_uf'     => $paciente->naturalidade_uf,
                'raca_cor'            => $paciente->raca_cor,
                'estado_civil'        => $paciente->estado_civil,
                'nome_mae'            => $paciente->nome_mae,
                // Contatos
                'contato'              => $paciente->contato,
                'telefone_alternativo' => $paciente->telefone_alternativo,
                // Endereço estruturado (ST-15) + campo legado
                'endereco' => [
                    'cep'              => $paciente->cep,
                    'logradouro'       => $paciente->logradouro,
                    'numero'           => $paciente->numero,
                    'complemento'      => $paciente->complemento,
                    'bairro'           => $paciente->bairro,
                    'cidade'           => $paciente->cidade,
                    'uf'               => $paciente->uf,
                    'ponto_referencia' => $paciente->ponto_referencia,
                    'endereco_legado'  => $paciente->endereco,
                ],
                // Contatos de emergência
                'contato_emergencia' => [
                    'nome'       => $paciente->contato_emergencia_nome,
                    'telefone'   => $paciente->contato_emergencia_telefone,
                    'parentesco' => $paciente->contato_emergencia_parentesco,
                ],
                'contato_emergencia_secundario' => [
                    'nome'       => $paciente->contato_emergencia2_nome,
                    'telefone'   => $paciente->contato_emergencia2_telefone,
                    'parentesco' => $paciente->contato_emergencia2_parentesco,
                ],
                'responsavel_legal' => [
                    'nome'       => $paciente->responsavel_nome,
                    'telefone'   => $paciente->responsavel_telefone,
                    'email'      => $paciente->responsavel_email,
                    'parentesco' => $paciente->responsavel_parentesco,
                ],
            ],
            // Dados de saúde autorrelatados (ST-15) — parte dos dados pessoais do titular
            'dados_de_saude' => [
                'tipo_sanguineo'            => $paciente->tipo_sanguineo,
                'peso_kg'                   => $paciente->peso_kg,
                'altura_cm'                 => $paciente->altura_cm,
                'alergias'                  => $paciente->alergias,
                'medicamentos_uso_continuo' => $paciente->medicamentos_uso_continuo,
                'condicoes_cronicas'        => $paciente->condicoes_cronicas,
                'cirurgias_previas'         => $paciente->cirurgias_previas,
                'tabagismo'                 => $paciente->tabagismo,
                'etilismo'                  => $paciente->etilismo,
                'atividade_fisica'          => $paciente->atividade_fisica,
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
