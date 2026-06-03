<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class NotificacaoController extends Controller
{
    /*
     * Marca uma notificação específica como lida.
     */
    public function marcarLida($id)
    {
        Auth::user()->notifications()->findOrFail($id)->markAsRead();

        return back()->with('success', 'Notificação marcada como lida.');
    }

    /*
     * Marca todas as notificações não lidas como lidas.
     */
    public function marcarTodas()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Todas as notificações foram marcadas como lidas.');
    }
}
