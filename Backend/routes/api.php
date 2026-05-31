<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// controllers
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\ServiciosEscolaresController;
use App\Http\Controllers\Api\GrupoController;
use App\Http\Controllers\Api\AlumnoController;
use App\Http\Controllers\Api\EvaluacionController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\Api\InscripcionController;
use App\Http\Controllers\Api\EventoController;
use App\Http\Controllers\Api\ComiteAcademicoController;
use App\Http\Controllers\Docentes\AsignacionDocenteController;
use App\Http\Controllers\Docentes\CargaDocenteController;
use App\Http\Controllers\Api\DocumentoController;


// ====================== AUTENTICACIÓN ======================

// Rutas públicas — no requieren token
Route::post('/login',  [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);


// DASHBOARD
Route::get('/dashboard',           [DashboardController::class, 'index']);
Route::get('/dashboard/kpis',      [DashboardController::class, 'kpis']);
Route::get('/dashboard/carreras',  [DashboardController::class, 'carreras']);
Route::get('/dashboard/semestres', [DashboardController::class, 'semestres']);

// 🔹 CALIFICACIONES
Route::get('/calificaciones-grupo', [ServiciosEscolaresController::class, 'getCalificacionesGrupo']);
Route::post('/guardar-calificaciones', [ServiciosEscolaresController::class, 'guardarCalificaciones']);
Route::put('/calificaciones/{id}', [ServiciosEscolaresController::class, 'actualizarCalificacion']);
Route::delete('/calificaciones/{id}', [ServiciosEscolaresController::class, 'eliminarCalificacion']);

// 🔹 ALUMNOS
Route::get('/alumnos-full', [ServiciosEscolaresController::class, 'getAlumnos']);
Route::post('/alumnos', [ServiciosEscolaresController::class, 'store']);
Route::get('/buscar-alumno', [ServiciosEscolaresController::class, 'buscarAlumnoInscripcion']);
Route::get('/horario/{numero_control}', [AlumnoController::class, 'horario']);
Route::get('/alumnos/buscar-control', [EventoController::class, 'buscarAlumno']);

// === CRUD COMPLETO DE ALUMNOS ===
Route::get('/alumnos/catalogos', [AlumnoController::class, 'catalogos']);
Route::get('/alumnos-crud', [AlumnoController::class, 'index']);
Route::apiResource('alumnos', AlumnoController::class);
Route::put('/alumnos/{id}', [AlumnoController::class, 'update']);
Route::delete('/alumnos/{id}', [AlumnoController::class, 'destroy']);

// 🔹 GRUPOS / INSCRIPCIÓN
Route::get('/grupos-disponibles', [ServiciosEscolaresController::class, 'getGruposDisponibles']);
Route::post('/inscribir', [ServiciosEscolaresController::class, 'inscribirAlumno']);
Route::get('/grupos', [GrupoController::class, 'index']);
Route::post('/grupos', [GrupoController::class, 'store']);
Route::put('/grupos/{id}', [GrupoController::class, 'update']);
Route::delete('/grupos/{id}', [GrupoController::class, 'destroy']);
Route::post('/grupos/{id}/cerrar-acta', [GrupoController::class, 'cerrarActa']);
Route::post('/grupos/{id}/abrir-acta',  [GrupoController::class, 'abrirActa']);
Route::get('/grupos/{id}/materias', [GrupoController::class, 'materias']);

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
Route::get('/carreras/{id}/grupos', [CarreraController::class, 'grupos']);

Route::get('/departamentos',           [DepartamentoController::class, 'index']);
Route::post('/departamentos',          [DepartamentoController::class, 'store']);
Route::put('/departamentos/{id}',      [DepartamentoController::class, 'update']);
Route::delete('/departamentos/{id}',   [DepartamentoController::class, 'destroy']);
Route::get('/niveles-carrera', [NivelCarreraController::class, 'index']);

Route::get('/carreras/{id}/grupos', [CarreraController::class, 'grupos']);

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

// PERÍODOS
use App\Http\Controllers\PeriodoController;

Route::get('/periodos', [PeriodoController::class, 'index']);
Route::post('/periodos', [PeriodoController::class, 'store']);
Route::put('/periodos/{id}', [PeriodoController::class, 'update']);
Route::delete('/periodos/{id}', [PeriodoController::class, 'destroy']);

// PLANES DE ESTUDIO
Route::get('/planes-estudio', [PlanEstudioController::class, 'index']);
Route::post('/planes-estudio', [PlanEstudioController::class, 'store']);
Route::put('/planes-estudio/{id}', [PlanEstudioController::class, 'update']);
Route::delete('/planes-estudio/{id}', [PlanEstudioController::class, 'destroy']);

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


// ====================== Modulo Eventos ======================
Route::get('/eventos', [EventoController::class, 'index']);
Route::get('/tipos-evento', [EventoController::class, 'tiposEvento']);
Route::get('/eventos/{id}', [EventoController::class, 'show']);
Route::post('/eventos', [EventoController::class, 'store']);
Route::put('/eventos/{id}', [EventoController::class, 'update']);

Route::get('/eventos/{id}/participantes', [EventoController::class, 'participantes']);
Route::post('/eventos/{id}/participantes', [EventoController::class, 'registrarParticipante']);
Route::patch('/eventos/{id}/participantes/{control}/constancia', [EventoController::class, 'emitirConstancia']);
Route::delete('/eventos/{id}/participantes/{control}', [EventoController::class, 'eliminarParticipante']);

Route::delete('/eventos/{id}', [EventoController::class, 'destroy']);


// ====================== MÓDULO DE RECURSOS HUMANOS ======================

use App\Http\Controllers\Api\RecursosHumanosController;
use App\Http\Controllers\Api\FormularioEmpleadoController;
use App\Http\Controllers\Api\DocentesController;
use App\Http\Controllers\RH\EmpleadoController;
use App\Http\Controllers\RH\PuestoController;
use App\Http\Controllers\RH\AdscripcionController;

// Dashboard RH
Route::get('/recursos-humanos/dashboard', [RecursosHumanosController::class, 'dashboard']);

// Empleados (GET usa RH\EmpleadoController con filtros y formato {success,data})
Route::get('/empleados/departamentos',         [EmpleadoController::class, 'getDepartamentos']);
Route::get('/empleados',                       [EmpleadoController::class, 'index']);
Route::get('/empleados/{id}',                  [EmpleadoController::class, 'show']);

// Empleados (escritura usa Api\FormularioEmpleadoController)
Route::post('/empleados',                      [FormularioEmpleadoController::class, 'store']);
Route::put('/empleados/{id}',                  [FormularioEmpleadoController::class, 'update']);
Route::post('/empleados/{id}/toggle-docente',  [FormularioEmpleadoController::class, 'toggleDocente']);

// Personas
use App\Http\Controllers\PersonaController;

Route::get('/personas',           [PersonaController::class, 'index']);
Route::post('/personas',          [PersonaController::class, 'store']);
Route::get('/personas/{id}',      [PersonaController::class, 'show']);
Route::put('/personas/{id}',      [PersonaController::class, 'update']);
Route::get('/empleado-catalogos', [FormularioEmpleadoController::class, 'catalogos']);

// Puestos
Route::get('/puestos',           [PuestoController::class, 'index']);
Route::get('/puestos/{id}',      [PuestoController::class, 'show']);
Route::post('/puestos',          [PuestoController::class, 'store']);
Route::put('/puestos/{id}',      [PuestoController::class, 'update']);
Route::delete('/puestos/{id}',   [PuestoController::class, 'destroy']);

// Adscripciones
Route::get('/adscripciones',                              [AdscripcionController::class, 'index']);
Route::get('/adscripciones/{id}',                         [AdscripcionController::class, 'show']);
Route::post('/adscripciones',                             [AdscripcionController::class, 'store']);
Route::put('/adscripciones/{id}',                         [AdscripcionController::class, 'update']);
Route::delete('/adscripciones/{id}',                      [AdscripcionController::class, 'destroy']);
Route::get('/empleados/{idEmpleado}/adscripcion-activa',  [AdscripcionController::class, 'getAdscripcionActiva']);

// Docentes (vista RH detallada con puesto/departamento)
Route::get('/docentes-rh',   [DocentesController::class, 'index']);
Route::put('/docentes/{id}', [DocentesController::class, 'update']);




Route::prefix('comite')->group(function () {
    Route::get('/dashboard', [ComiteAcademicoController::class, 'dashboard']);
    Route::get('/tipos-solicitud', [ComiteAcademicoController::class, 'tiposSolicitud']);

    Route::get('/solicitudes', [ComiteAcademicoController::class, 'indexSolicitudes']);
    Route::post('/solicitudes', [ComiteAcademicoController::class, 'storeSolicitud']);

    Route::get('/sesiones', [ComiteAcademicoController::class, 'indexSesiones']);
    Route::post('/sesiones', [ComiteAcademicoController::class, 'storeSesion']);
    Route::put('/sesiones/{id}', [ComiteAcademicoController::class, 'updateSesion']);

    Route::get('/resoluciones', [ComiteAcademicoController::class, 'indexResoluciones']);
    Route::post('/resoluciones', [ComiteAcademicoController::class, 'storeResolucion']);

    Route::get('/personas/buscar', [ComiteAcademicoController::class, 'buscarPersonas']);
});

// ====================== ASIGNACIÓN DOCENTE A GRUPOS ======================


Route::get('/asignacion-docente/grupos', [AsignacionDocenteController::class, 'grupos']);
Route::get('/docentes/disponibles', [AsignacionDocenteController::class, 'docentesDisponibles']);
Route::post('/asignacion-docente', [AsignacionDocenteController::class, 'store']);

// --- CargaDocenteView ---
Route::get('/docentes', [CargaDocenteController::class, 'buscarDocentes']);
Route::get('/carga-docente/{id_docente}', [CargaDocenteController::class, 'cargaPorDocente']);
Route::delete('/asignacion-docente/{id}', [CargaDocenteController::class, 'desasignar']);

// ====================== MÓDULO DE INSCRIPCIÓN DETALLADA ======================

Route::prefix('form')->group(function () {
    Route::get('/inscripciones', [InscripcionController::class, 'index']);
    Route::get('/kpis', [InscripcionController::class, 'kpis']);
    Route::get('/grupos/disponibles', [InscripcionController::class, 'gruposDisponibles']);
    Route::get('/periodos', [InscripcionController::class, 'periodos']);
    Route::get('/carreras', [InscripcionController::class, 'carreras']);
    Route::get('/alumnos/control/{numero}', [InscripcionController::class, 'buscarAlumno']);

    Route::post('/inscripciones', [InscripcionController::class, 'store']);
    Route::put('/inscripciones/{id}', [InscripcionController::class, 'update']);
    Route::delete('/inscripciones/{id}', [InscripcionController::class, 'destroy']);
});

// ====================== REPORTES ======================

use App\Http\Controllers\Api\ReporteController;

Route::get('/reportes/resumen',       [ReporteController::class, 'resumen']);
Route::get('/reportes/alumnos',       [ReporteController::class, 'alumnos']);
Route::get('/reportes/calificaciones', [ReporteController::class, 'calificaciones']);
Route::get('/reportes/inscripciones', [ReporteController::class, 'inscripciones']);
Route::get('/reportes/grupos',        [ReporteController::class, 'grupos']);

// ====================== CATÁLOGOS SIMPLES ======================

use App\Http\Controllers\Api\CatalogosController;

Route::get('/generos',       [CatalogosController::class, 'generos']);
Route::get('/estatus-alumno', [CatalogosController::class, 'estatusAlumno']);
Route::get('/turnos',        [CatalogosController::class, 'turnos']);

// ====================== KARDEX ======================

use App\Http\Controllers\Api\KardexController;

Route::get('/kardex/buscar-por-nombre', [KardexController::class, 'buscarPorNombre']);
Route::get('/kardex/{numero_control}/pdf', [KardexController::class, 'exportarPDF']);
Route::get('/kardex/{numero_control}', [KardexController::class, 'show']);

// ====================== SEGUIMIENTO ACADÉMICO ======================

use App\Http\Controllers\Api\SeguimientoController;

Route::get('/seguimiento/{id_alumno}', [SeguimientoController::class, 'show']);
// Avance curricular por número de control (AvanceCurricularView.vue)
Route::get('/kardex/{numero_control}/avance-curricular', [SeguimientoController::class, 'showByControl']);

// ====================== ADMISIÓN ======================

use App\Http\Controllers\Api\AdmisionController;

Route::get('/admision',                        [AdmisionController::class, 'index']);
Route::post('/admision/{id}/cambiar-estatus',  [AdmisionController::class, 'cambiarEstatus']);

// ====================== EGRESO ======================

use App\Http\Controllers\Api\EgresoController;

Route::get('/egresos',            [EgresoController::class, 'index']);
Route::post('/egresos',           [EgresoController::class, 'store']);
Route::put('/egresos/{id}/titular', [EgresoController::class, 'titular']);

// ====================== TRÁMITES ======================

use App\Http\Controllers\Api\TramiteController;

Route::get('/tramites',      [TramiteController::class, 'index']);
Route::post('/tramites',     [TramiteController::class, 'store']);
Route::put('/tramites/{id}', [TramiteController::class, 'update']);

// ====================== ADEUDOS ======================

use App\Http\Controllers\Api\AdeudoController;

Route::get('/adeudos',                       [AdeudoController::class, 'index']);
Route::post('/adeudos',                      [AdeudoController::class, 'store']);
Route::put('/adeudos/{id}',                  [AdeudoController::class, 'update']);
Route::put('/adeudos/{id}/marcar-pagado',    [AdeudoController::class, 'marcarPagado']);
Route::delete('/adeudos/{id}',               [AdeudoController::class, 'destroy']);

// HISTORIAL DE INSCRIPCIÓN

use App\Http\Controllers\HistorialInscripcionController;

Route::prefix('form')->group(function () {

    Route::get('historial/alumno/{numero_control}', [HistorialInscripcionController::class, 'buscarPorControl']);

    Route::get('inscripciones/historial/{id_alumno}/exportar', [HistorialInscripcionController::class, 'exportar']);

    Route::get('inscripciones/historial/{id_alumno}', [HistorialInscripcionController::class, 'historial']);
});

use App\Http\Controllers\Api\AnaliticaController;

Route::prefix('analitica')->group(function () {
    Route::get('/resumen', [AnaliticaController::class, 'resumen']);
    Route::get('/carreras', [AnaliticaController::class, 'rendimientoPorCarreras']);
    Route::get('/materias-criticas', [AnaliticaController::class, 'materiasCriticas']);
});
// =============== Resumen de cacrreras =====================================
use App\Http\Controllers\Api\CarreraResumenController;

Route::get('/carreras/resumen', [CarreraResumenController::class, 'index']);

//======================= DOCUMENTOS OFICIALES EN PDF ======================
Route::get('/documentos/constancia/{numero_control}',  [DocumentoController::class, 'constancia']);
Route::get('/documentos/boleta/{numero_control}',      [DocumentoController::class, 'boleta']);
Route::get('/documentos/certificado/{numero_control}', [DocumentoController::class, 'certificado']);
