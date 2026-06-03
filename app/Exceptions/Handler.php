<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

        // Intercepta CSRF expirado (419) — redireciona para login com mensagem amigável.
        // BUG-01 (v0.10.1): o Laravel converte TokenMismatchException em HttpException(419)
        // no prepareException() ANTES de checar os render callbacks, por isso um callback
        // tipado para TokenMismatchException nunca dispara. Capturamos HttpException e
        // filtramos pelo status 419. Além de redirecionar, invalidamos a sessão e regeneramos
        // o token para que o 419 não reincida ao reenviar o formulário de login.
        $this->renderable(function (HttpException $e, $request) {
            if ($e->getStatusCode() !== 419) {
                return null; // 403/404/500 etc. seguem para o tratamento padrão (errors/{code})
            }

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')
                ->with('error', 'Sua sessão expirou. Por favor, faça login novamente.');
        });
    }
}
