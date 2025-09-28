<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AkunKasController extends Controller
{
    public function index()
    {
        return view('akun-kas.index');
    }
}
