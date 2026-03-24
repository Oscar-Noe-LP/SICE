<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumnos; // Tu modelo enviado
use Illuminate\Http\Request;

class AlumnoController extends Controller
{
    public function index() {
        // Obtenemos todos los alumnos con su información
        return response()->json(Alumnos::all());
    }
}
