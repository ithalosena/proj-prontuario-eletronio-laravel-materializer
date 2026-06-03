<?php

namespace App\Http\Controllers;

class PrivacidadeController extends Controller
{
    // Página pública — sem autenticação (LGPD Art. 9º: informação acessível ao titular)
    public function index()
    {
        return view('content.pages.privacidade');
    }
}
