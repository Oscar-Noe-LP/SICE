<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class NivelCarreraController extends Controller
{
    public function index()
    {
        return DB::table('nivel_carrera')
            ->select('id_nivel', 'nombre_nivel')
            ->get();
    }
}