// ====================== MÓDULO DE RECURSOS HUMANOS ======================

use App\Http\Controllers\Api\RecursosHumanosController;
use App\Http\Controllers\Api\FormularioEmpleadoController;
use App\Http\Controllers\Api\DocentesController;

// Dashboard RH - 4 KPIs
Route::get('/rh/dashboard', [RecursosHumanosController::class, 'dashboard']);

// Formulario de empleado
Route::get('/rh/catalogos',            [FormularioEmpleadoController::class, 'catalogos']);
Route::get('/rh/buscar-persona',       [FormularioEmpleadoController::class, 'buscarPersona']);
Route::post('/rh/empleados',           [FormularioEmpleadoController::class, 'store']);
Route::post('/rh/empleados/{id}/toggle-docente', [FormularioEmpleadoController::class, 'toggleDocente']);

// Docentes - lista y edición
Route::get('/rh/docentes',             [DocentesController::class, 'index']);
Route::put('/rh/docentes/{id}',        [DocentesController::class, 'update']);
