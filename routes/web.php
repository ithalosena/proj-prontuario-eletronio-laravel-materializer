<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
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

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// locale
Route::get('lang/{locale}', [LanguageController::class, 'swap']);

// ==========================================================================
// ROTAS PUBLICAS (sem autenticacao)
// ==========================================================================

Route::get('/login', function () {
    return view('content.authentications.auth-login-basic');
})->name('login');

Route::post('/fazer-login', function (Request $request) {
    $credentials = [
        'email'    => $request->email,
        'password' => $request->senha,
    ];

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect('/');
    }

    return back()->with('error', 'Email ou senha incorretos.');
});

Route::get('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
});

// authentication views (template Materialize)
Route::get('/auth/login-basic',    [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');

// ==========================================================================
// ROTAS PROTEGIDAS (exigem sessao ativa)
// ==========================================================================

Route::middleware('auth')->group(function () {

    // Dashboard e paginas internas (qualquer usuario autenticado)
    Route::get('/',                [HomePage::class, 'index'])->name('pages-home');
    Route::get('/page-2',          [Page2::class, 'index'])->name('pages-page-2');
    Route::get('/pages/misc-error',[MiscError::class, 'index'])->name('pages-misc-error');

    // ------------------------------------------------------------------
    // RELATORIOS (coordenador e acima: nivel <= 2)
    // ------------------------------------------------------------------
    Route::get('/relatorios', [RelatorioController::class, 'index'])->middleware('nivel:2');

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
