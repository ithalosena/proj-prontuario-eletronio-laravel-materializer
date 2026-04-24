<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAtendimentoRequest;
use App\Models\Atendimento;
use App\Models\Paciente;
use App\Models\Profissional;
use Illuminate\Support\Facades\Auth;

/*
 * Controller: AtendimentoController
 *
 * Gerencia o ciclo de vida dos atendimentos: abertura, visualização e encerramento.
 * Um atendimento é a "ficha" que agrupa as consultas de um paciente com um profissional.
 * Fluxo principal: abrir atendimento → registrar consultas → encerrar atendimento.
 *
 * Rotas associadas (definidas em routes/web.php):
 *   GET   /atendimentos              → index()
 *   GET   /cadastro-atendimento      → create()
 *   POST  /cadastrar-atendimento     → store()
 *   GET   /atendimentos/{id}         → show()
 *   PATCH /atendimentos/{id}/fechar  → fechar()
 */
class AtendimentoController extends Controller
{
    /*
     * Lista todos os atendimentos com paginação.
     *
     * Regra de negócio: profissional_saude (nivel 3) só vê os seus próprios atendimentos.
     * Admin e recepcionista (niveis 1 e 2) veem todos.
     *
     * Usamos $query como variável intermediária para poder aplicar filtros
     * condicionalmente antes de executar a consulta no banco.
     * O with() faz eager loading: carrega as relações de uma vez só,
     * evitando N+1 queries (um problema clássico de performance no Laravel).
     */
    public function index()
    {
        $query = Atendimento::with('paciente', 'profissional', 'criadoPor')
            ->orderBy('created_at', 'desc');

        // Profissional de saúde só enxerga os atendimentos em que ele é o responsável
        if (Auth::user()->nivelAcesso() == 3) {
            $profissional = Auth::user()->profissional;
            if ($profissional) {
                $query->where('profissional_id', $profissional->id);
            }
        }

        $atendimentos = $query->paginate(15);

        return view('content.pages.listagem_atendimentos', compact('atendimentos'));
    }

    /*
     * Exibe o formulário de abertura de atendimento.
     *
     * Verificamos se o usuário logado tem um perfil de profissional vinculado
     * (Auth::user()->profissional retorna null se não tiver, ex: recepcionista ou admin).
     * A view usa essa informação para travar o campo de profissional automaticamente,
     * evitando que o profissional precise se selecionar na lista manualmente.
     */
    public function create()
    {
        $pacientes          = Paciente::orderBy('nome')->get();
        $profissionais      = Profissional::orderBy('nome')->get();
        $profissionalLogado = Auth::user()->profissional; // null se o usuário não for profissional

        return view('content.pages.cadastro_atendimento', compact('pacientes', 'profissionais', 'profissionalLogado'));
    }

    /*
     * Persiste o novo atendimento no banco de dados.
     *
     * O status inicial é sempre 'aberto' — o encerramento é feito pelo método fechar().
     * Registramos 'criado_por_id' para rastrear quem abriu o atendimento,
     * já que pode ser uma recepcionista abrindo em nome do profissional.
     * A validação dos dados já foi feita pelo StoreAtendimentoRequest antes de chegar aqui.
     */
    public function store(StoreAtendimentoRequest $request)
    {
        Atendimento::create([
            'paciente_id'     => $request->paciente_id,
            'profissional_id' => $request->profissional_id,
            'criado_por_id'   => Auth::id(),
            'status'          => 'aberto',
        ]);

        return redirect('/atendimentos')->with('success', 'Atendimento aberto com sucesso!');
    }

    /*
     * Exibe a página de detalhes de um atendimento com todas as consultas vinculadas.
     *
     * O with() carrega todas as relações necessárias de uma vez só (eager loading),
     * evitando que o Laravel faça uma query separada para cada consulta, exame e prescrição
     * (o que seria muito lento com muitos registros — o chamado problema N+1).
     * findOrFail() retorna automaticamente um erro 404 se o ID não existir no banco.
     */
    public function show($id)
    {
        $atendimento = Atendimento::with(
            'paciente',
            'profissional',
            'criadoPor',
            'fechadoPor',
            'consultas.exames',      // carrega os exames de cada consulta vinculada
            'consultas.prescricoes'  // carrega as prescrições de cada consulta vinculada
        )->findOrFail($id);

        return view('content.pages.detalhes_atendimento', compact('atendimento'));
    }

    /*
     * Encerra um atendimento aberto.
     *
     * Registra quem fechou (fechado_por_id) e o momento exato (fechado_em = now()).
     * Após fechado, as consultas vinculadas ficam em modo somente leitura —
     * essa restrição é aplicada nas views com a verificação $atendimento->isAberto().
     * Não usamos delete() porque o histórico do atendimento deve ser preservado.
     */
    public function fechar($id)
    {
        $atendimento = Atendimento::findOrFail($id);

        // Impede fechar um atendimento que já está fechado (duplo clique, link direto, etc.)
        if ($atendimento->status === 'fechado') {
            return back()->with('error', 'Este atendimento já está fechado.');
        }

        $atendimento->status         = 'fechado';
        $atendimento->fechado_por_id = Auth::id();
        $atendimento->fechado_em     = now();
        $atendimento->save();

        return redirect('/atendimentos/' . $id)->with('success', 'Atendimento encerrado com sucesso.');
    }
}
