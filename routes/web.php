<?php

use App\Http\Controllers\pages\Profissionais;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\Page2;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;

use App\Models\Profissional;
use App\Models\Paciente;
use App\Models\Exame;
use App\Models\Prescricao;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Main Page Route
Route::get('/', [HomePage::class, 'index'])->name('pages-home');
Route::get('/page-2', [Page2::class, 'index'])->name('pages-page-2');

// locale
Route::get('lang/{locale}', [LanguageController::class, 'swap']);

// pages
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');

//Rotas Profissional

// authentication
Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');




//Route::get('/', function () {
//  return view('menu');
//});

// AUTENTICAÇÃO

Route::get('/logout', function () {
  //Session::forget('logged_in');
  return redirect('/login');
});

Route::get('/login', function () {
  return view('content.pages.login');
});

Route::post('/fazer-login', function (Request $infos) {
  $email = $infos->email;
  $senha = $infos->senha;

  $profissionais = Profissional::all();
  $pacientes = Paciente::all();

  foreach ($profissionais as $profissional) {
    if ($profissional->email == $email) {
      if ($profissional->senha == $senha) {
        return view('menu');
      }
    }
  }

  foreach ($pacientes as $paciente) {
    if ($paciente->email == $email) {
      if ($paciente->senha == $senha) {
        return view('menu');
      }
    }

    return view('content.pages.problema-login');
  }
});

// RELATORIO

Route::get('/relatorios', function () {

  $exames = Exame::all();
  $pacientes = Paciente::all();
  $prescricoes = Prescricao::all();

  return view('content.pages.relatorios', ['exames' => $exames, 'pacientes' => $pacientes, 'prescricoes' => $prescricoes]);
});

// CRUD PRESCRICAO

Route::get('/prescricoes', function () {
  $prescricoes = Prescricao::all();
  return view("content.pages.listagem_prescricoes", ["prescricoes" => $prescricoes]);
});

Route::get('/cadastro-prescricao', function () {
  return view('content.pages.cadastro-prescricao');
});

Route::post('/cadastrar-prescricao', function (Request $infos) {

  Prescricao::create([
    'nome_medicamento' => $infos->nome_medicamento,
    'dosagem' => $infos->dosagem,
    'frequencia' => $infos->frequencia,
    'duracao' => $infos->duracao
  ]);

  return view("content.pages.menu_voltar", ["url" => "/prescricoes"]);
});

Route::get('/editar-prescricao/{id}', function ($id) {
  $prescricao = Prescricao::findOrFail($id);
  return view('content.pages.editar_prescricao', ['prescricao' => $prescricao]);
});

Route::put('/atualizar-prescricao/{id}', function (Request $infos,  $id) {
  $prescricao = Prescricao::findOrFail($id);

  $prescricao->nome_medicamento = $infos->nome_medicamento;
  $prescricao->dosagem = $infos->dosagem;
  $prescricao->frequencia = $infos->frequencia;
  $prescricao->duracao = $infos->duracao;

  $prescricao->save();

  return view("content.pages.menu_voltar", ["url" => "/prescricoes"]);
});

Route::get('/deletar-prescricao/{id}', function ($id) {
  $prescricao = Prescricao::findOrFail($id);
  $prescricao->delete();

  return view("content.pages.menu_voltar", ["url" => "/prescricoes"]);
});

// CRUD EXAMES
Route::get('/exames', function () {
  $exames = Exame::all();
  return view("content.pages.listagem_exames", ["exames" => $exames]);
});

Route::get('/cadastro-exame', function () {
  return view('content.pages.cadastro-exame');
});

Route::post('/cadastrar-exame', function (Request $infos) {

  Exame::create([
    'observacao' => $infos->observacao,
    'nome_paciente' => $infos->nome_paciente,
    'nome_profissional' => $infos->nome_profissional,
    'data' => $infos->data,
    'especialidade' => $infos->especialidade
  ]);

  return view("content.pages.menu_voltar", ["url" => "/exames"]);
});

Route::get('/editar-exame/{id}', function ($id) {
  $exame = Exame::findOrFail($id);
  return view('content.pages.editar_exame', ['exame' => $exame]);
});

Route::put('/atualizar-exame/{id}', function (Request $infos,  $id) {
  $exame = Exame::findOrFail($id);

  $exame->observacao = $infos->observacao;
  $exame->nome_paciente = $infos->nome_paciente;
  $exame->nome_profissional = $infos->nome_profissional;
  $exame->data = $infos->data;
  $exame->especialidade = $infos->especialidade;

  $exame->save();

  return view("content.pages.menu_voltar", ["url" => "/exames"]);
});

Route::get('/deletar-exame/{id}', function ($id) {
  $exame = Exame::findOrFail($id);
  $exame->delete();

  return view("content.pages.menu_voltar", ["url" => "/exames"]);
});

// CRUD PACIENTE

Route::get('/pacientes', function () {
  $pacientes = Paciente::all();
  return view("content.pages.listagem_pacientes", ["pacientes" => $pacientes]);
});

Route::get('/cadastro-paciente', function () {
  return view('content.pages.cadastro-paciente');
});

Route::post('/cadastrar-paciente', function (Request $infos) {

  Paciente::create([
    'nome' => $infos->nome,
    'email' => $infos->email,
    'senha' => $infos->senha,
    'contato' => $infos->contato,
    'idade' => $infos->idade,
    'sexo' => $infos->sexo,
    'documento' => $infos->documento,
    'endereco' => $infos->endereco,
    'matricula' => $infos->matricula,
    'curso' => $infos->curso
  ]);

  $pacientes = Paciente::all();
  return view("content.pages.listagem_pacientes", ["pacientes" => $pacientes]);
});

Route::get('/editar-paciente/{id}', function ($id) {
  $paciente = Paciente::findOrFail($id);
  return view('content.pages.editar_paciente', ['paciente' => $paciente]);
});

Route::put('/atualizar-paciente/{id}', function (Request $infos,  $id) {
  $paciente = Paciente::findOrFail($id);

  $paciente->nome = $infos->nome;
  $paciente->email = $infos->email;
  $paciente->senha = $infos->senha;
  $paciente->contato = $infos->contato;
  $paciente->idade = $infos->idade;
  $paciente->sexo = $infos->sexo;
  $paciente->documento = $infos->documento;
  $paciente->matricula = $infos->matricula;
  $paciente->curso = $infos->curso;

  $paciente->save();

  $pacientes = Paciente::all();
  return view("content.pages.listagem_pacientes", ["pacientes" => $pacientes]);
});

Route::get('/deletar-paciente/{id}', function ($id) {
  $paciente = Paciente::findOrFail($id);
  $paciente->delete();

  $pacientes = Paciente::all();
  return view("content.pages.listagem_pacientes", ["pacientes" => $pacientes]);
});

// CRUD PROFISSIONAL

Route::get('/profissionais', function () {
  $profissionais = Profissional::all();
  return view("content.pages.listagem_profissionais", ["profissionais" => $profissionais]);
});
Route::get('/cadastro-profissional', function () {
  return view('content.pages.cadastro-profissional');
});

Route::post('/cadastrar-profissional', function (Request $infos) {

  Profissional::create([
    'nome' => $infos->nome,
    'email' => $infos->email,
    'senha' => $infos->senha,
    'contato' => $infos->contato,
    'especialidade' => $infos->especialidade
  ]);

  $profissionais = Profissional::all();
  return view("content.pages.listagem_profissionais", ["profissionais" => $profissionais]);
});

Route::get('/buscar-profissional/{id}', function ($id) {
  $prof = Profissional::findOrFail($id);
  echo $prof->nome;
});

Route::get('/editar-profissional/{id}', function ($id) {
  $prof = Profissional::findOrFail($id);
  return view('content.pages.editar_profissional', ['prof' => $prof]);
});

Route::put('/atualizar-profissional/{id}', function (Request $infos,  $id) {
  $prof = Profissional::findOrFail($id);

  $prof->nome = $infos->nome;
  $prof->email = $infos->email;
  $prof->senha = $infos->senha;
  $prof->contato = $infos->contato;
  $prof->especialidade = $infos->especialidade;

  $prof->save();

  $profissionais = Profissional::all();
  return view("content.pages.listagem_profissionais", ["profissionais" => $profissionais]);
});

Route::get('/deletar-profissional/{id}', function ($id) {
  $prof = Profissional::findOrFail($id);
  $prof->delete();


  $profissionais = Profissional::all();
  return view("content.pages.listagem_profissionais", ["profissionais" => $profissionais]);
});
