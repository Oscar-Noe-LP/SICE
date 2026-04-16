<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// controllers
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\ServiciosEscolaresController;
use App\Http\Controllers\Api\GrupoController;
use App\Http\Controllers\Api\AlumnoController;
use App\Http\Controllers\Api\EvaluacionController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\Api\InscripcionController;

// DASHBOARD
Route::get('/dashboard', [DashboardController::class, 'index']);

// 🔹 CALIFICACIONES
Route::get('/calificaciones-grupo', [ServiciosEscolaresController::class, 'getCalificacionesGrupo']);
Route::post('/guardar-calificaciones', [ServiciosEscolaresController::class, 'guardarCalificaciones']);
Route::put('/calificaciones/{id}', [ServiciosEscolaresController::class, 'actualizarCalificacion']);
Route::delete('/calificaciones/{id}', [ServiciosEscolaresController::class, 'eliminarCalificacion']);

// 🔹 ALUMNOS
Route::get('/alumnos-full', [ServiciosEscolaresController::class, 'getAlumnos']);
Route::post('/alumnos', [ServiciosEscolaresController::class, 'store']);
Route::get('/buscar-alumno', [ServiciosEscolaresController::class, 'buscarAlumnoInscripcion']);

// === CRUD COMPLETO DE ALUMNOS ===
Route::apiResource('alumnos', AlumnoController::class);
Route::get('/alumnos-crud', [AlumnoController::class, 'index']);
Route::put('/alumnos/{id}', [AlumnoController::class, 'update']);
Route::delete('/alumnos/{id}', [AlumnoController::class, 'destroy']);

// 🔹 GRUPOS / INSCRIPCIÓN
Route::get('/grupos-disponibles', [ServiciosEscolaresController::class, 'getGruposDisponibles']);
Route::post('/inscribir', [ServiciosEscolaresController::class, 'inscribirAlumno']);
Route::get('/grupos', [GrupoController::class, 'index']);
Route::post('/grupos', [GrupoController::class, 'store']);
Route::put('/grupos/{id}', [GrupoController::class, 'update']);
Route::delete('/grupos/{id}', [GrupoController::class, 'destroy']);

// 🔹 EVALUACIONES
Route::post('/evaluaciones/guardar', [ServiciosEscolaresController::class, 'guardarEvaluaciones']);
Route::get('/evaluaciones/{id_grupo}', [ServiciosEscolaresController::class, 'getEvaluaciones']);
Route::put('/evaluaciones/{id}', [ServiciosEscolaresController::class, 'actualizarEvaluacion']);
Route::delete('/evaluaciones/{id}', [ServiciosEscolaresController::class, 'eliminarEvaluacion']);

// 🔹 RESUMEN ESCOLAR
Route::get('/resumen-escolar', [ServiciosEscolaresController::class, 'getResumen']);


// INSCRIPCIÓN
Route::prefix('inscripcion')->group(function () {
    Route::get('/buscar-alumno', [InscripcionController::class, 'buscarAlumno']);
    Route::get('/grupos', [InscripcionController::class, 'gruposDisponibles']);
    Route::post('/registrar', [InscripcionController::class, 'inscribirAlumno']);
});


// 🔹 FILTROS DINÁMICOS
Route::get('/filtros', function () {
    return response()->json([
        'periodos' => DB::table('periodo')->get(),
        'carreras' => DB::table('carrera')->get(),
        'materias' => DB::table('materia')->get(),
        'grupos'   => DB::table('grupo')->get(),
    ]);
});

// CARRERAS, DEPARTAMENTOS Y NIVELES
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\NivelCarreraController;

Route::get('/carreras', [CarreraController::class, 'index']);
Route::post('/carreras', [CarreraController::class, 'store']);
Route::put('/carreras/{id}', [CarreraController::class, 'update']);
Route::delete('/carreras/{id}', [CarreraController::class, 'destroy']);

Route::get('/departamentos', [DepartamentoController::class, 'index']);
Route::get('/niveles-carrera', [NivelCarreraController::class, 'index']);

// EDIFICIOS Y AULAS
use App\Http\Controllers\EdificioController;
use App\Http\Controllers\AulaController;

// EDIFICIOS
Route::get('/edificios', [EdificioController::class, 'index']);
Route::post('/edificios', [EdificioController::class, 'store']);
Route::put('/edificios/{id}', [EdificioController::class, 'update']);
Route::delete('/edificios/{id}', [EdificioController::class, 'destroy']);

// AULAS
Route::get('/aulas', [AulaController::class, 'index']);
Route::post('/aulas', [AulaController::class, 'store']);
Route::put('/aulas/{id}', [AulaController::class, 'update']);
Route::delete('/aulas/{id}', [AulaController::class, 'destroy']);

// GESTIÓN ACADÉMICA - RESUMEN
use App\Http\Controllers\GestionAcademicaController;

Route::get('/gestion-academica/resumen', [GestionAcademicaController::class, 'resumen']);

// MATERIAS Y PLANES DE ESTUDIO
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\PlanMateriaController;
use App\Http\Controllers\PlanEstudioController;

// Materias
Route::get('/materias', [MateriaController::class, 'index']);
Route::post('/materias', [MateriaController::class, 'store']);
Route::put('/materias/{id}', [MateriaController::class, 'update']);
Route::delete('/materias/{id}', [MateriaController::class, 'destroy']);
Route::get('/materias/{id}/planes', [MateriaController::class, 'planes']);

// Plan - Materia
Route::post('/plan-materia', [PlanMateriaController::class, 'store']);

// Planes de estudio
Route::get('/planes-estudio', [PlanEstudioController::class, 'index']);

// PERÍODOS
use App\Http\Controllers\PeriodoController;

Route::get('/periodos', [PeriodoController::class, 'index']);
Route::post('/periodos', [PeriodoController::class, 'store']);
Route::put('/periodos/{id}', [PeriodoController::class, 'update']);
Route::delete('/periodos/{id}', [PeriodoController::class, 'destroy']);

// PLANES DE ESTUDIO Y CARRERAS
// PLANES DE ESTUDIO
Route::get('/planes-estudio', [PlanEstudioController::class, 'index']);
Route::post('/planes-estudio', [PlanEstudioController::class, 'store']);
Route::put('/planes-estudio/{id}', [PlanEstudioController::class, 'update']);
Route::delete('/planes-estudio/{id}', [PlanEstudioController::class, 'destroy']);

// CARRERAS
Route::get('/carreras', [CarreraController::class, 'index']);

// PRERREQUISITOS
use App\Http\Controllers\PrerrequisitoController;

Route::get('/prerrequisitos', [PrerrequisitoController::class, 'index']);
Route::post('/prerrequisitos', [PrerrequisitoController::class, 'store']);
Route::delete('/prerrequisitos/{id_materia}/{id_materia_prerrequisito}', [PrerrequisitoController::class, 'destroy']);


// ====================== MÓDULO DE SEGURIDAD ======================

use App\Http\Controllers\Seguridad\UsuarioController;
use App\Http\Controllers\Seguridad\RolController;
use App\Http\Controllers\Seguridad\PermisoController;
use App\Http\Controllers\Seguridad\BitacoraController;

// Usuarios
Route::apiResource('usuarios', UsuarioController::class);
Route::put('usuarios/{id}/contrasena', [UsuarioController::class, 'cambiarContrasena']);
Route::get('/personas/buscar', [UsuarioController::class, 'buscarPersonas']);

// Roles
Route::apiResource('roles', RolController::class);
Route::put('roles/{id}/permisos', [RolController::class, 'actualizarPermisos']);
Route::get('roles/{id}/permisos', [RolController::class, 'permisos']);
Route::get('/roles-simple', [RolController::class, 'simpleList']);

// Permisos
Route::get('/permisos', [PermisoController::class, 'index']);

// Bitácora
Route::get('/bitacora', [BitacoraController::class, 'index']);


// ====================== MÓDULO DE RECURSOS HUMANOS ======================

use App\Http\Controllers\Api\RecursosHumanosController;
use App\Http\Controllers\Api\FormularioEmpleadoController;
use App\Http\Controllers\Api\DocentesController;

// Dashboard RH
Route::get('/recursos-humanos/dashboard', [RecursosHumanosController::class, 'dashboard']);

// Empleados
Route::get('/empleados',           [FormularioEmpleadoController::class, 'index']);
Route::get('/empleados/{id}',      [FormularioEmpleadoController::class, 'show']);
Route::post('/empleados',          [FormularioEmpleadoController::class, 'store']);
Route::put('/empleados/{id}',      [FormularioEmpleadoController::class, 'update']);

// Búsqueda de personas y catálogos para formulario de empleado
Route::get('/personas',            [FormularioEmpleadoController::class, 'buscarPersona']);
Route::get('/puestos',             function () {
    return response()->json(\Illuminate\Support\Facades\DB::table('puesto')->get());
});
Route::get('/empleado-catalogos',  [FormularioEmpleadoController::class, 'catalogos']);
Route::post('/empleados/{id}/toggle-docente', [FormularioEmpleadoController::class, 'toggleDocente']);

// Docentes
Route::get('/docentes',            [DocentesController::class, 'index']);
Route::put('/docentes/{id}',       [DocentesController::class, 'update']);
