<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Profissional;
use Illuminate\Http\Request;

class Profissionais extends Controller
{
  public function index()
  {
    $profissionais = Profissional::all();
    return view('content.pages.listagem_profissionais', ["profissionais" => $profissionais]);
    //return view('content.pages.pages-page2');
  }
}
