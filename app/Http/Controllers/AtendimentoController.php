<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAtendimentoRequest;
use App\Models\Atendimento;
use App\Models\Paciente;
use App\Models\Profissional;
use Illuminate\Http\Request;
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
        $busca = request('busca');
        // UX-05 (v0.10.1): filtros por profissional e status (GET, preserváveis na URL)
        $filtroProfissional = request('profissional_id');
        $filtroStatus       = request('status');

        $query = Atendimento::with('paciente', 'profissional', 'criadoPor')
            ->orderBy('created_at', 'desc');

        // Profissional de saúde só enxerga os atendimentos em que ele é o responsável
        if (Auth::user()->nivelAcesso() == 3) {
            $profissional = Auth::user()->profissional;
            if ($profissional) {
                $query->where('profissional_id', $profissional->id);
            }
        }

        // Filtro de busca por nome ou matrícula do paciente
        if ($busca) {
            $query->whereHas('paciente', function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('matricula', 'like', "%{$busca}%");
            });
        }

        // Filtros por profissional e status (selects populados do banco)
        $query->when($filtroProfissional, fn($q) => $q->where('profissional_id', $filtroProfissional))
              ->when($filtroStatus,       fn($q) => $q->where('status', $filtroStatus));

        // withQueryString preserva busca + filtros nos links de paginação
        $atendimentos      = $query->paginate(15)->withQueryString();
        $totalAtendimentos = Atendimento::count();
        $abertosDoUsuario  = null;

        // Mini-indicador de atendimentos abertos exibido apenas para o profissional logado
        if (Auth::user()->nivelAcesso() == 3) {
            $prof = Auth::user()->profissional;
            if ($prof) {
                $abertosDoUsuario = Atendimento::where('profissional_id', $prof->id)
                    ->where('status', 'aberto')
                    ->count();
            }
        }

        // Lista de profissionais para o select de filtro (relevante para nivel != 3)
        $profissionais = \App\Models\Profissional::orderBy('nome')->get();

        return view('content.pages.listagem_atendimentos', compact(
            'atendimentos', 'busca', 'totalAtendimentos', 'abertosDoUsuario',
            'profissionais', 'filtroProfissional', 'filtroStatus'
        ));
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
        // ANALISE-01 (v0.10.1): Admin (nivel 1) é somente leitura — não abre atendimentos.
        if (Auth::user()->nivelAcesso() === 1) {
            abort(403);
        }

        $profissionais      = Profissional::orderBy('nome')->get();
        $profissionalLogado = Auth::user()->profissional; // null se o usuário não for profissional

        // Quando a validação falha e o Laravel volta com old(), precisamos dos dados do
        // paciente previamente selecionado para repopular o campo de busca (UX-11b).
        // Buscamos apenas esse registro — não mais todos os pacientes (autocomplete via AJAX).
        // UX-P04 (v0.10.2): aceita ?paciente_id= para pré-selecionar o paciente
        // (vindo do hero do perfil do paciente e do banner do dashboard). old() tem prioridade na revalidação.
        $pacienteAnterior = old('paciente_id')
            ? Paciente::find(old('paciente_id'))
            : (request('paciente_id') ? Paciente::find(request('paciente_id')) : null);

        return view('content.pages.cadastro_atendimento', compact('profissionais', 'profissionalLogado', 'pacienteAnterior'));
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
        // ANALISE-01 (v0.10.1): guard server-side — Admin não cria atendimentos
        if (Auth::user()->nivelAcesso() === 1) {
            abort(403);
        }

        $atendimento = Atendimento::create([
            'paciente_id'     => $request->paciente_id,
            'profissional_id' => $request->profissional_id,
            'criado_por_id'   => Auth::id(),
            'status'          => 'aberto',
        ]);

        // UX-13: redireciona para os detalhes do atendimento recém-criado (PRG pattern),
        // orientando o próximo passo em vez de voltar para a listagem.
        return redirect('/atendimentos/' . $atendimento->id)
            ->with('success', 'Atendimento aberto! Registre a primeira consulta abaixo.');
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

        // UX-14: últimos 5 atendimentos do mesmo paciente para o mini-card de histórico.
        // Dados já em memória após esse eager load — o modal não faz queries extras.
        $ultimosAtendimentos = $atendimento->paciente_id
            // UX-P06+P08 (v0.10.2): carrega exames/prescrições das consultas p/ os badges do modal (sem N+1)
            ? Atendimento::with('profissional', 'consultas.exames', 'consultas.prescricoes')
                ->where('paciente_id', $atendimento->paciente_id)
                ->where('id', '!=', $id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
            : collect();

        // UX-P09 (v0.10.2): conta exames sem resultado no atendimento — alimenta o alerta
        // de "exame pendente" no modal de encerramento (reforça a sugestão de retorno).
        $examesPendentes = $atendimento->consultas
            ->flatMap(fn($c) => $c->exames)
            ->filter(fn($e) => blank($e->resultado))
            ->count();

        return view('content.pages.detalhes_atendimento', compact('atendimento', 'ultimosAtendimentos', 'examesPendentes'));
    }

    /*
     * Encerra um atendimento aberto.
     *
     * Registra quem fechou (fechado_por_id) e o momento exato (fechado_em = now()).
     * Após fechado, as consultas vinculadas ficam em modo somente leitura —
     * essa restrição é aplicada nas views com a verificação $atendimento->isAberto().
     * Não usamos delete() porque o histórico do atendimento deve ser preservado.
     */
    /*
     * Encerra um atendimento aberto.
     *
     * UX-07-RETORNO: se o formulário enviar agendar_retorno=1 (botão "Encerrar e Agendar Retorno"),
     * o redirect aponta para /cadastro-agendamento com os dados do paciente e profissional.
     * Enquanto o módulo ST-09 não existir, exibe flash informativo e fica na mesma página.
     */
    public function fechar(Request $request, $id)
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

        // Quando ST-09 (Agendamentos) for implementado, substituir pelo redirect abaixo:
        // return redirect('/cadastro-agendamento?paciente_id=' . $atendimento->paciente_id
        //     . '&profissional_id=' . $atendimento->profissional_id);
        if ($request->boolean('agendar_retorno')) {
            return redirect('/atendimentos/' . $id)
                ->with('success', 'Atendimento encerrado. O módulo de Agendamento de Retorno estará disponível em breve.');
        }

        return redirect('/atendimentos/' . $id)->with('success', 'Atendimento encerrado com sucesso.');
    }
}
