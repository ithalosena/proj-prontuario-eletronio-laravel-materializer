<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    // Campos que nunca são re-preenchidos na sessão após erro de validação
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Intercepta o 429 do throttle na rota de login e redireciona com mensagem em português
        $this->renderable(function (ThrottleRequestsException $e) {
            $segundos = $e->getHeaders()['Retry-After'] ?? 60;
            return redirect()->route('login')
                ->with('error', "Muitas tentativas de login. Aguarde {$segundos} segundo(s) e tente novamente.");
        });

        // Intercepta CSRF expirado (419) — redireciona para login com mensagem amigável
        // em vez de exibir a tela de erro padrão do Laravel
        $this->renderable(function (TokenMismatchException $e) {
            return redirect()->route('login')
                ->with('error', 'Sua sessão expirou. Por favor, faça login novamente.');
        });
    }
}
