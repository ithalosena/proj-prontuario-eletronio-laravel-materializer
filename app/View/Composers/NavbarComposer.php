<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NavbarComposer
{
    public function compose(View $view): void
    {
        $user = Auth::user();

        $view->with([
            'notificacoesCount'    => $user ? $user->unreadNotifications->count() : 0,
            'notificacoesRecentes' => $user ? $user->unreadNotifications->take(5) : collect(),
            'notificacoesLista'    => $user ? $user->notifications()->latest()->take(50)->get() : collect(),
        ]);
    }
}
