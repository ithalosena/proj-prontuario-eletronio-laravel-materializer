<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Profissional;
use Illuminate\Http\Request;

class Page2 extends Controller
{
  public function index()
  {
    $profissionais = Profissional::all();
    return view('content.pages.listagem_profissionais', ["profissionais" => $profissionais]);
    //return view('content.pages.pages-page2');
  }
}
