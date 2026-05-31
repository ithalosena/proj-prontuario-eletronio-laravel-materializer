<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NavbarComposer
{
    public function compose(View $view): void
    {
        $user = Auth::user();

        if (!$user) {
            $view->with([
                'notificacoesCount'    => 0,
                'notificacoesRecentes' => collect(),
                'notificacoesLista'    => collect(),
            ]);
            return;
        }

        // COUNT(*) separado evita carregar todos os modelos em memória
        $count    = $user->unreadNotifications()->count();
        $recentes = $count > 0
            ? $user->unreadNotifications()->latest()->take(5)->get()
            : collect();
        $lista    = $user->notifications()->latest()->take(50)->get();

        $view->with([
            'notificacoesCount'    => $count,
            'notificacoesRecentes' => $recentes,
            'notificacoesLista'    => $lista,
        ]);
    }
}
