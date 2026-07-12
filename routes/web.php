<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\Page2;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\ProfissionalController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\ExameController;
use App\Http\Controllers\PrescricaoController;
use App\Http\Controllers\ConsultaController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AtendimentoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AgendamentoController;
use App\Http\Controllers\DisponibilidadeController;
use App\Http\Controllers\EspecialidadeController;
use App\Http\Controllers\MeuAgendamentoController;
use App\Http\Controllers\TipoConsultaController;
use App\Http\Controllers\PrivacidadeController;
use App\Http\Controllers\ConsentimentoController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OnboardingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// locale
Route::get('lang/{locale}', [LanguageController::class, 'swap']);

// ==========================================================================
// ROTA PÚBLICA — Política de Privacidade (LGPD Art. 9º)
// Acessível sem autenticação para que qualquer titular possa consultar
// ==========================================================================
Route::get('/privacidade', [PrivacidadeController::class, 'index']);

// ==========================================================================
// ROTAS PUBLICAS (sem autenticacao)
// ==========================================================================

Route::get('/login',       [LoginController::class, 'showLogin'])->name('login');
Route::post('/fazer-login', [LoginController::class, 'login'])->middleware('throttle:5,1');

// authentication views (template Materialize)
Route::get('/auth/login-basic',    [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');

// ==========================================================================
// ROTAS PROTEGIDAS (exigem sessão ativa + consentimento LGPD)
// v0.8.2: middleware 'consentimento' adicionado ao grupo externo — cobre todos os perfis (1–5)
// O próprio middleware gerencia as rotas isentas (ROTAS_ISENTAS) — /consentimento, /logout etc.
// ==========================================================================

Route::middleware(['auth', 'consentimento', 'onboarding'])->group(function () {

    // S-04: logout via POST com CSRF — impede logout forçado por link externo
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Ping de sessão — renova a sessão via AJAX sem recarregar a página (usado pelo timer de inatividade)
    Route::get('/session/ping', fn () => response()->json(['ok' => true]))->name('session.ping');

    // Slots de disponibilidade — AJAX acessível por todos os perfis (paciente usa no wizard)
    // IMPORTANTE: fora do grupo nivel:4 — pacientes (nivel 5) precisam desta rota
    // S-06: throttle 60 req/min por IP — proteção contra scraping de disponibilidade
    Route::get('/agendamentos/slots/{profissional}/{data}', [AgendamentoController::class, 'slots'])
        ->middleware('throttle:60,1');

    // ------------------------------------------------------------------
    // NOTIFICAÇÕES IN-APP (todos os perfis autenticados 1–5)
    // IMPORTANTE: ler-todas ANTES de {id}/ler para evitar captura do parâmetro
    // ------------------------------------------------------------------
    Route::patch('/notificacoes/ler-todas',    [NotificacaoController::class, 'marcarTodas']);
    Route::redirect('/notificacoes', '/', 301);
    Route::patch('/notificacoes/{id}/ler',     [NotificacaoController::class, 'marcarLida']);

    // Dashboard e páginas internas (qualquer usuário autenticado com consentimento válido)
    Route::get('/',         [DashboardController::class, 'index'])->name('pages-home');
    Route::get('/page-2',   [Page2::class,     'index'])->name('pages-page-2');
    Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');

    // ------------------------------------------------------------------
    // PERFIL DO USUÁRIO (ST-10) — qualquer perfil autenticado
    // ------------------------------------------------------------------
    Route::get   ('/perfil',          [ProfileController::class, 'edit'])->name('perfil');
    Route::put   ('/perfil',          [ProfileController::class, 'update']);
    Route::post  ('/perfil/avatar',   [ProfileController::class, 'uploadAvatar']);
    Route::delete('/perfil/avatar/remover', [ProfileController::class, 'deleteAvatar']);

    // MEUS DADOS (C.5, v0.10.3) — dados complementares editáveis (paciente/profissional)
    Route::get('/meus-dados', [\App\Http\Controllers\MeusDadosController::class, 'edit'])->name('meus-dados');
    Route::put('/meus-dados', [\App\Http\Controllers\MeusDadosController::class, 'update']);

    // ------------------------------------------------------------------
    // CONSENTIMENTO LGPD
    // Rotas isentas do CheckConsentimento (declaradas no próprio middleware)
    // ------------------------------------------------------------------
    Route::get('/consentimento',          [ConsentimentoController::class, 'show']);
    Route::post('/consentimento/aceitar', [ConsentimentoController::class, 'aceitar']);
    Route::post('/consentimento/recusar', [ConsentimentoController::class, 'recusar']);

    // ------------------------------------------------------------------
    // ONBOARDING DE PRIMEIRO ACESSO (ST-15)
    // Isentas do CheckOnboarding (declaradas no próprio middleware)
    // ------------------------------------------------------------------
    Route::get ('/onboarding',                  [OnboardingController::class, 'show']);
    Route::post('/onboarding/salvar/paciente',   [OnboardingController::class, 'salvarPaciente']);
    Route::post('/onboarding/salvar/operador',   [OnboardingController::class, 'salvarOperador']);

    // ------------------------------------------------------------------
    // MEU PRONTUARIO (paciente com perfil vinculado)
    // Cache-Control: private, no-store — dados clínicos não devem ser cacheados (Art. 46 LGPD)
    // L-06: /exportar implementa Art. 18, V — portabilidade dos dados
    // IMPORTANTE: /exportar antes de possíveis rotas com {id}
    // ------------------------------------------------------------------
    Route::middleware('cache.headers:private;no_store')->group(function () {
        Route::get('/meu-prontuario',          [PacienteController::class, 'meuProntuario'])->name('meu-prontuario');
        Route::get('/meu-prontuario/exportar', [PacienteController::class, 'exportarDados']);
    });

    // Revogação do consentimento do titular (Art. 8º §5º — "a qualquer momento")
    Route::post('/meu-prontuario/revogar-consentimento', [ConsentimentoController::class, 'revogar']);

    // ------------------------------------------------------------------
    // RELATORIOS (coordenador e acima: nivel <= 2)
    // ------------------------------------------------------------------
    Route::get('/relatorios', [RelatorioController::class, 'index'])->middleware('nivel:2');

    // ------------------------------------------------------------------
    // AUDIT LOGS (admin: nivel <= 1) — Cache-Control: private para dados de auditoria
    // ------------------------------------------------------------------
    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->middleware(['nivel:1', 'cache.headers:private;no_store']);

    // ------------------------------------------------------------------
    // CONFIGURAÇÕES — Especialidades e Tipos de Consulta (coordenador e acima: nivel <= 2)
    // ------------------------------------------------------------------
    Route::middleware('nivel:2')->group(function () {
        // UX-02 (v0.10.1): rotas GET nomeadas para o highlight do item de menu "Configurações"
        Route::get('/configuracoes/especialidades',                          [EspecialidadeController::class, 'index'])->name('configuracoes-especialidades');
        Route::post('/configuracoes/especialidades',                         [EspecialidadeController::class, 'store']);
        Route::put('/configuracoes/especialidades/{especialidade}',          [EspecialidadeController::class, 'update']);
        Route::patch('/configuracoes/especialidades/{especialidade}/toggle', [EspecialidadeController::class, 'toggleAtivo']);

        Route::get('/configuracoes/tipos-consulta',                         [TipoConsultaController::class, 'index'])->name('configuracoes-tipos-consulta');
        Route::post('/configuracoes/tipos-consulta',                        [TipoConsultaController::class, 'store']);
        Route::put('/configuracoes/tipos-consulta/{tipoConsulta}',          [TipoConsultaController::class, 'update']);
        Route::patch('/configuracoes/tipos-consulta/{tipoConsulta}/toggle', [TipoConsultaController::class, 'toggleAtivo']);
    });

    // ------------------------------------------------------------------
    // AUTOCOMPLETE AJAX (profissional e acima: nivel <= 3)
    // IMPORTANTE: antes das rotas com {id} para evitar conflito de parâmetro
    // ------------------------------------------------------------------
    Route::middleware('nivel:3')->group(function () {
        // S-06: throttle 60 req/min por IP nos endpoints de autocomplete AJAX
        Route::get('/pacientes/buscar',    [PacienteController::class,    'buscar'])->middleware('throttle:60,1');
        Route::get('/profissionais/buscar',[ProfissionalController::class,'buscar'])->middleware('throttle:60,1');
        Route::get('/consultas/buscar',    [ConsultaController::class,    'buscar'])->middleware('throttle:60,1');
    });

    // ------------------------------------------------------------------
    // AGENDAMENTOS — recepcionista e acima (nivel <= 4): visualização, criação e cancelamento
    // IMPORTANTE: rotas estáticas (eventos, slots) ANTES de {id}
    // ------------------------------------------------------------------
    Route::middleware('nivel:4')->group(function () {
        Route::get('/agendamentos',                             [AgendamentoController::class, 'index']);
        // S-06: throttle 60 req/min por IP — feed FullCalendar AJAX
        Route::get('/agendamentos/eventos',                     [AgendamentoController::class, 'eventos'])
            ->middleware(['cache.headers:private;no_store', 'throttle:60,1']);
        Route::get('/cadastro-agendamento',                     [AgendamentoController::class, 'create']);
        Route::post('/cadastrar-agendamento',                   [AgendamentoController::class, 'store']);
        Route::get('/agendamentos/{id}',                        [AgendamentoController::class, 'show']);
        Route::patch('/agendamentos/{id}/cancelar',             [AgendamentoController::class, 'cancelar']);
    });

    // Confirmar e realizar — profissional e acima (nivel <= 3): ações clínicas exclusivas
    Route::middleware('nivel:3')->group(function () {
        Route::patch('/agendamentos/{id}/confirmar',            [AgendamentoController::class, 'confirmar']);
        Route::patch('/agendamentos/{id}/realizar',             [AgendamentoController::class, 'realizar']);
    });

    // Disponibilidade — profissional e acima (nivel <= 3)
    Route::middleware('nivel:3')->group(function () {
        Route::get('/disponibilidade',                          [DisponibilidadeController::class, 'index']);
        Route::post('/disponibilidade',                         [DisponibilidadeController::class, 'store']);
        Route::post('/disponibilidade/excecoes',                [DisponibilidadeController::class, 'storeExcecao']);
        Route::patch('/disponibilidade/excecoes/{id}',          [DisponibilidadeController::class, 'updateExcecao']);
        Route::delete('/disponibilidade/excecoes/{id}',         [DisponibilidadeController::class, 'destroyExcecao']);
    });

    // ------------------------------------------------------------------
    // AGENDAMENTOS — paciente (nivel 5)
    // ------------------------------------------------------------------
    Route::middleware('nivel:5')->group(function () {
        // Rotas nomeadas para o highlight do menu vertical (slug === currentRouteName)
        Route::get('/meus-agendamentos',               [MeuAgendamentoController::class, 'index'])->name('meus-agendamentos');
        Route::get('/agendar-consulta',                [MeuAgendamentoController::class, 'create'])->name('agendar-consulta');
        Route::post('/agendar-consulta',               [MeuAgendamentoController::class, 'store']);
        Route::patch('/meus-agendamentos/{id}/cancelar',[MeuAgendamentoController::class, 'cancelar']);
    });

    // ------------------------------------------------------------------
    // ATENDIMENTOS (profissional e acima: nivel <= 3)
    // ------------------------------------------------------------------
    Route::middleware('nivel:3')->group(function () {
        Route::get('/atendimentos',               [AtendimentoController::class, 'index']);
        Route::get('/cadastro-atendimento',       [AtendimentoController::class, 'create']);
        Route::post('/cadastrar-atendimento',     [AtendimentoController::class, 'store']);
        Route::get('/atendimentos/{id}',          [AtendimentoController::class, 'show']);
        Route::patch('/atendimentos/{id}/fechar', [AtendimentoController::class, 'fechar']);
    });

    // ------------------------------------------------------------------
    // CRUD CONSULTA (profissional e acima: nivel <= 3)
    // Cache-Control: private em /consultas/{id} — dado clínico sensível
    // ------------------------------------------------------------------
    Route::middleware('nivel:3')->group(function () {
        Route::get('/consultas',               [ConsultaController::class, 'index']);
        Route::get('/consultas/{id}',          [ConsultaController::class, 'show'])
            ->middleware('cache.headers:private;no_store');
        Route::get('/cadastro-consulta',       [ConsultaController::class, 'create']);
        Route::post('/cadastrar-consulta',     [ConsultaController::class, 'store']);
        Route::get('/editar-consulta/{id}',    [ConsultaController::class, 'edit']);
        Route::put('/atualizar-consulta/{id}', [ConsultaController::class, 'update']);
        Route::delete('/deletar-consulta/{id}',[ConsultaController::class, 'destroy']);
    });

    // ------------------------------------------------------------------
    // CRUD PROFISSIONAL (admin e acima: nivel <= 1)
    // ------------------------------------------------------------------
    Route::middleware('nivel:1')->group(function () {
        Route::get('/profissionais',               [ProfissionalController::class, 'index']);
        Route::get('/cadastro-profissional',       [ProfissionalController::class, 'create']);
        Route::post('/cadastrar-profissional',     [ProfissionalController::class, 'store']);
        Route::get('/editar-profissional/{id}',    [ProfissionalController::class, 'edit']);
        Route::put('/atualizar-profissional/{id}', [ProfissionalController::class, 'update']);
        Route::delete('/deletar-profissional/{id}',[ProfissionalController::class, 'destroy']);
    });

    // ------------------------------------------------------------------
    // CRUD PACIENTE (recepcionista e acima: nivel <= 4)
    // Cache-Control: private em /pacientes/{id} — perfil com dados pessoais
    // ------------------------------------------------------------------
    Route::middleware('nivel:4')->group(function () {
        Route::get('/pacientes',                [PacienteController::class, 'index']);
        // UX-14: rota de perfil DEVE vir antes de /historico para evitar conflito de pattern
        Route::get('/pacientes/{id}',           [PacienteController::class, 'show'])
            ->middleware('cache.headers:private;no_store');
        Route::get('/pacientes/{id}/historico', [PacienteController::class, 'historico']);
        Route::get('/cadastro-paciente',        [PacienteController::class, 'create']);
        Route::post('/cadastrar-paciente',      [PacienteController::class, 'store']);
        Route::get('/editar-paciente/{id}',     [PacienteController::class, 'edit']);
        Route::put('/atualizar-paciente/{id}',  [PacienteController::class, 'update']);
        Route::delete('/deletar-paciente/{id}', [PacienteController::class, 'destroy']);
    });

    // ------------------------------------------------------------------
    // CRUD EXAME (profissional e acima: nivel <= 3)
    // ------------------------------------------------------------------
    Route::middleware('nivel:3')->group(function () {
        Route::get('/exames',               [ExameController::class, 'index']);
        Route::get('/cadastro-exame',       [ExameController::class, 'create']);
        Route::post('/cadastrar-exame',     [ExameController::class, 'store']);
        Route::get('/editar-exame/{id}',    [ExameController::class, 'edit']);
        Route::put('/atualizar-exame/{id}', [ExameController::class, 'update']);
        Route::delete('/deletar-exame/{id}',[ExameController::class, 'destroy']);
    });

    // ------------------------------------------------------------------
    // CRUD PRESCRICAO (profissional e acima: nivel <= 3)
    // ------------------------------------------------------------------
    Route::middleware('nivel:3')->group(function () {
        Route::get('/prescricoes',               [PrescricaoController::class, 'index']);
        Route::get('/cadastro-prescricao',       [PrescricaoController::class, 'create']);
        Route::post('/cadastrar-prescricao',     [PrescricaoController::class, 'store']);
        Route::get('/editar-prescricao/{id}',    [PrescricaoController::class, 'edit']);
        Route::put('/atualizar-prescricao/{id}', [PrescricaoController::class, 'update']);
        Route::delete('/deletar-prescricao/{id}',[PrescricaoController::class, 'destroy']);
    });
});
