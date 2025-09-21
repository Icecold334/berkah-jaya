<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index($type)
    {
        return view('laporan.index', compact('type'));
    }
}
