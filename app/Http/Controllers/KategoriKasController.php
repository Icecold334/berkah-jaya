<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KategoriKasController extends Controller
{
    public function index()
    {
        return view('kategori-kas.index');
    }
}
