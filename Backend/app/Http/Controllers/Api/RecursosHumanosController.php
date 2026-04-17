<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class RecursosHumanosController extends Controller
{
    public function dashboard()
    {
        try {
            // KPI 1: Total de empleados activos
            $totalEmpleados = DB::table('empleado')
                ->where('estatus', true)
                ->count();

            // KPI 2: Total de docentes
            $totalDocentes = DB::table('docente')
                ->join('empleado', 'docente.id_empleado', '=', 'empleado.id_empleado')
                ->where('empleado.estatus', true)
                ->count();

            // KPI 3: Total de departamentos activos
            $totalDepartamentos = DB::table('departamento')
                ->where('estatus', true)
                ->count();

            // KPI 4: Empleados contratados este año
            $anioActual = now()->year;
            $contratadosAnio = DB::table('empleado')
                ->whereYear('fecha_contratacion', $anioActual)
                ->count();

            $totalPuestos = DB::table('puesto')->count();

            return response()->json([
                'kpis' => [
                    'empleados'     => $totalEmpleados,
                    'docentes'      => $totalDocentes,
                    'departamentos' => $totalDepartamentos,
                    'puestos'       => $totalPuestos,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Error en servidor',
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }
}
