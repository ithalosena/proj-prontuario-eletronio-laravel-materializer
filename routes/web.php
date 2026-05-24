<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\Page2;
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

Route::get('/login',      [LoginController::class, 'showLogin'])->name('login');
Route::post('/fazer-login', [LoginController::class, 'login'])->middleware('throttle:5,1');

// authentication views (template Materialize)
Route::get('/auth/login-basic',    [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');

// ==========================================================================
// ROTAS PROTEGIDAS (exigem sessao ativa)
// ==========================================================================

Route::middleware('auth')->group(function () {

    // S-04: logout via POST com CSRF — impede logout forçado por link externo (CSRF logout attack)
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard e paginas internas (qualquer usuario autenticado)
    Route::get('/',                [HomePage::class, 'index'])->name('pages-home');
    Route::get('/page-2',          [Page2::class, 'index'])->name('pages-page-2');
    Route::get('/pages/misc-error',[MiscError::class, 'index'])->name('pages-misc-error');

    // ------------------------------------------------------------------
    // CONSENTIMENTO LGPD (paciente — antes do primeiro acesso ao prontuário)
    // Rotas isentas do middleware CheckConsentimento por definição no próprio middleware
    // ------------------------------------------------------------------
    Route::get('/consentimento',         [ConsentimentoController::class, 'show']);
    Route::post('/consentimento/aceitar', [ConsentimentoController::class, 'aceitar']);

    // ------------------------------------------------------------------
    // MEU PRONTUARIO (qualquer usuario autenticado com perfil de paciente)
    // Middleware consentimento: redireciona pacientes sem aceite para /consentimento
    // L-06: /exportar implementa Art. 18, V — portabilidade dos dados
    // IMPORTANTE: rota /exportar deve vir ANTES de possíveis rotas com {id}
    // ------------------------------------------------------------------
    Route::middleware('consentimento')->group(function () {
        Route::get('/meu-prontuario',          [PacienteController::class, 'meuProntuario']);
        Route::get('/meu-prontuario/exportar', [PacienteController::class, 'exportarDados']);
    });

    // ------------------------------------------------------------------
    // RELATORIOS (coordenador e acima: nivel <= 2)
    // ------------------------------------------------------------------
    Route::get('/relatorios', [RelatorioController::class, 'index'])->middleware('nivel:2');

    // ------------------------------------------------------------------
    // AUDIT LOGS (admin e acima: nivel <= 1)
    // ------------------------------------------------------------------
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->middleware('nivel:1');

    // ------------------------------------------------------------------
    // CONFIGURAÇÕES — Especialidades e Tipos de Consulta (coordenador e acima: nivel <= 2)
    // ------------------------------------------------------------------
    Route::middleware('nivel:2')->group(function () {
        Route::get('/configuracoes/especialidades',                           [EspecialidadeController::class, 'index']);
        Route::post('/configuracoes/especialidades',                          [EspecialidadeController::class, 'store']);
        Route::put('/configuracoes/especialidades/{especialidade}',           [EspecialidadeController::class, 'update']);
        Route::patch('/configuracoes/especialidades/{especialidade}/toggle',  [EspecialidadeController::class, 'toggleAtivo']);

        Route::get('/configuracoes/tipos-consulta',                          [TipoConsultaController::class, 'index']);
        Route::post('/configuracoes/tipos-consulta',                         [TipoConsultaController::class, 'store']);
        Route::put('/configuracoes/tipos-consulta/{tipoConsulta}',           [TipoConsultaController::class, 'update']);
        Route::patch('/configuracoes/tipos-consulta/{tipoConsulta}/toggle',  [TipoConsultaController::class, 'toggleAtivo']);
    });

    // ------------------------------------------------------------------
    // AUTOCOMPLETE AJAX (profissional e acima: nivel <= 3)
    // Endpoints consumidos pelos componentes de busca nas telas de cadastro.
    // IMPORTANTE: devem vir ANTES das rotas com {id} para evitar conflito de parâmetro.
    // ------------------------------------------------------------------
    Route::middleware('nivel:3')->group(function () {
        Route::get('/pacientes/buscar',    [PacienteController::class,    'buscar']);
        Route::get('/profissionais/buscar',[ProfissionalController::class,'buscar']);
        Route::get('/consultas/buscar',   [ConsultaController::class,    'buscar']);
    });

    // ------------------------------------------------------------------
    // AGENDAMENTOS — staff (profissional e acima: nivel <= 3)
    // IMPORTANTE: rotas estáticas (eventos, slots) ANTES de {id}
    // ------------------------------------------------------------------
    Route::middleware('nivel:3')->group(function () {
        Route::get('/agendamentos',                                          [AgendamentoController::class, 'index']);
        Route::get('/agendamentos/eventos',                                  [AgendamentoController::class, 'eventos']);
        Route::get('/agendamentos/slots/{profissional}/{data}',              [AgendamentoController::class, 'slots']);
        Route::get('/cadastro-agendamento',                                  [AgendamentoController::class, 'create']);
        Route::post('/cadastrar-agendamento',                                [AgendamentoController::class, 'store']);
        Route::get('/agendamentos/{id}',                                     [AgendamentoController::class, 'show']);
        Route::patch('/agendamentos/{id}/confirmar',                         [AgendamentoController::class, 'confirmar']);
        Route::patch('/agendamentos/{id}/cancelar',                          [AgendamentoController::class, 'cancelar']);
        Route::patch('/agendamentos/{id}/realizar',                          [AgendamentoController::class, 'realizar']);
        Route::get('/disponibilidade',                                       [DisponibilidadeController::class, 'index']);
        Route::post('/disponibilidade',                                      [DisponibilidadeController::class, 'store']);
        Route::post('/disponibilidade/excecoes',                             [DisponibilidadeController::class, 'storeExcecao']);
        Route::patch('/disponibilidade/excecoes/{id}',                       [DisponibilidadeController::class, 'updateExcecao']);
        Route::delete('/disponibilidade/excecoes/{id}',                      [DisponibilidadeController::class, 'destroyExcecao']);
    });

    // ------------------------------------------------------------------
    // AGENDAMENTOS — paciente (nivel 5)
    // Middleware consentimento: garante que o paciente consentiu antes de agendar
    // ------------------------------------------------------------------
    Route::middleware(['nivel:5', 'consentimento'])->group(function () {
        Route::get('/meus-agendamentos',                                     [MeuAgendamentoController::class, 'index']);
        Route::get('/agendar-consulta',                                      [MeuAgendamentoController::class, 'create']);
        Route::post('/agendar-consulta',                                     [MeuAgendamentoController::class, 'store']);
        Route::patch('/meus-agendamentos/{id}/cancelar',                     [MeuAgendamentoController::class, 'cancelar']);
    });

    // ------------------------------------------------------------------
    // ATENDIMENTOS (profissional e acima: nivel <= 3)
    // ------------------------------------------------------------------
    Route::middleware('nivel:3')->group(function () {
        Route::get('/atendimentos',                       [AtendimentoController::class, 'index']);
        Route::get('/cadastro-atendimento',               [AtendimentoController::class, 'create']);
        Route::post('/cadastrar-atendimento',             [AtendimentoController::class, 'store']);
        Route::get('/atendimentos/{id}',                  [AtendimentoController::class, 'show']);
        Route::patch('/atendimentos/{id}/fechar',         [AtendimentoController::class, 'fechar']);
    });

    // ------------------------------------------------------------------
    // CRUD CONSULTA (profissional e acima: nivel <= 3)
    // ------------------------------------------------------------------
    Route::middleware('nivel:3')->group(function () {
        Route::get('/consultas',               [ConsultaController::class, 'index']);
        Route::get('/consultas/{id}',          [ConsultaController::class, 'show']);
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
    // ------------------------------------------------------------------
    Route::middleware('nivel:4')->group(function () {
        Route::get('/pacientes',                    [PacienteController::class, 'index']);
        // UX-14: rota de perfil DEVE vir antes de /historico para evitar conflito de pattern
        Route::get('/pacientes/{id}',               [PacienteController::class, 'show']);
        Route::get('/pacientes/{id}/historico',     [PacienteController::class, 'historico']);
        Route::get('/cadastro-paciente',            [PacienteController::class, 'create']);
        Route::post('/cadastrar-paciente',          [PacienteController::class, 'store']);
        Route::get('/editar-paciente/{id}',         [PacienteController::class, 'edit']);
        Route::put('/atualizar-paciente/{id}',      [PacienteController::class, 'update']);
        Route::delete('/deletar-paciente/{id}',     [PacienteController::class, 'destroy']);
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
