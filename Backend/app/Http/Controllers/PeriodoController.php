<?php

namespace App\Http\Controllers;

use App\Models\Periodo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PeriodoController extends Controller
{
    public function index()
    {
        $periodos = Periodo::query()
            ->orderBy('fecha_inicio', 'desc')
            ->get()
            ->map(fn (Periodo $periodo) => $this->formatPeriodo($periodo));

        return response()->json($periodos);
    }

    public function activo()
    {
        $periodo = Periodo::whereIn('estatus', ['ACTIVO', 'activo', '1', 1, true])
            ->orderByDesc('id_periodo')
            ->first();

        if (!$periodo) {
            return response()->json(['error' => 'No hay periodo activo'], 404);
        }

        return response()->json($this->formatPeriodo($periodo));
    }

    public function store(Request $request)
    {
        $validation = $this->validatePeriodoRequest($request);

        if ($validation) {
            return $validation;
        }

        if ($this->hasOverlappingDates($request->fecha_inicio, $request->fecha_fin)) {
            return $this->overlapResponse();
        }

        return DB::transaction(function () use ($request) {
            $activo = $request->boolean('activo');

            if ($activo) {
                $this->deactivateOtherActivePeriods();
            }

            $periodo = Periodo::create([
                'nombre_periodo' => $request->nombre_periodo,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'estatus' => $activo ? 'ACTIVO' : $request->estatus,
                'activo' => $activo,
            ]);

            $message = 'PERIODO ESCOLAR REGISTRADO CORRECTAMENTE.';

            if ($activo) {
                $message .= ' EL PERIODO ACTIVO FUE ACTUALIZADO.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $this->formatPeriodo($periodo->fresh()),
            ], 201);
        });
    }

    public function update(Request $request, $id)
    {
        $periodo = Periodo::find($id);

        if (!$periodo) {
            return $this->notFoundResponse();
        }

        $validation = $this->validatePeriodoRequest($request);

        if ($validation) {
            return $validation;
        }

        if ($this->hasOverlappingDates($request->fecha_inicio, $request->fecha_fin, $id)) {
            return $this->overlapResponse();
        }

        return DB::transaction(function () use ($request, $periodo) {
            $activo = $request->boolean('activo');

            if ($activo) {
                $this->deactivateOtherActivePeriods($periodo->id_periodo);
            }

            $periodo->update([
                'nombre_periodo' => $request->nombre_periodo,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'estatus' => $activo ? 'ACTIVO' : $request->estatus,
                'activo' => $activo,
            ]);

            $message = 'PERIODO ESCOLAR ACTUALIZADO CORRECTAMENTE.';

            if ($activo) {
                $message .= ' EL PERIODO ACTIVO FUE ACTUALIZADO.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $this->formatPeriodo($periodo->fresh()),
            ]);
        });
    }

    public function activar($id)
    {
        $periodo = Periodo::find($id);

        if (!$periodo) {
            return $this->notFoundResponse();
        }

        return DB::transaction(function () use ($periodo) {
            $this->deactivateOtherActivePeriods($periodo->id_periodo);

            $periodo->update([
                'estatus' => 'ACTIVO',
                'activo' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'PERIODO ACTIVO ACTUALIZADO CORRECTAMENTE.',
                'data' => $this->formatPeriodo($periodo->fresh()),
            ]);
        });
    }

    public function desactivar($id)
    {
        $periodo = Periodo::find($id);

        if (!$periodo) {
            return $this->notFoundResponse();
        }

        $periodo->update([
            'estatus' => 'INACTIVO',
            'activo' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PERIODO ESCOLAR DESACTIVADO CORRECTAMENTE.',
            'data' => $this->formatPeriodo($periodo->fresh()),
        ]);
    }

    public function destroy($id)
    {
        $periodo = Periodo::find($id);

        if (!$periodo) {
            return $this->notFoundResponse();
        }

        $periodo->delete();

        return response()->json([
            'success' => true,
            'message' => 'PERIODO ESCOLAR ELIMINADO CORRECTAMENTE.',
        ]);
    }

    private function validatePeriodoRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_periodo' => ['required', 'string', 'max:50'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date'],
            'estatus' => ['required', Rule::in(['ACTIVO', 'PROGRAMADO', 'INACTIVO'])],
            'activo' => ['sometimes', 'boolean'],
        ], [
            'nombre_periodo.required' => 'EL NOMBRE DEL PERIODO ES OBLIGATORIO.',
            'fecha_inicio.required' => 'LA FECHA DE INICIO ES OBLIGATORIA.',
            'fecha_fin.required' => 'LA FECHA DE FIN ES OBLIGATORIA.',
            'estatus.required' => 'EL ESTATUS DEL PERIODO ES OBLIGATORIO.',
            'nombre_periodo.max' => 'EL NOMBRE DEL PERIODO NO PUEDE SER MAYOR A 50 CARACTERES.',
            'fecha_inicio.date' => 'LA FECHA DE INICIO NO ES VÁLIDA.',
            'fecha_fin.date' => 'LA FECHA DE FIN NO ES VÁLIDA.',
            'estatus.in' => 'EL ESTATUS DEL PERIODO NO ES VÁLIDO.',
            'activo.boolean' => 'EL CAMPO ACTIVO DEBE SER VERDADERO O FALSO.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'LOS DATOS ENVIADOS NO SON VÁLIDOS.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (Carbon::parse($request->fecha_inicio)->gt(Carbon::parse($request->fecha_fin))) {
            return response()->json([
                'success' => false,
                'message' => 'LA FECHA DE INICIO NO PUEDE SER MAYOR A LA FECHA DE FIN.',
                'errors' => [
                    'fecha_inicio' => [
                        'LA FECHA DE INICIO NO PUEDE SER MAYOR A LA FECHA DE FIN.',
                    ],
                    'fecha_fin' => [
                        'LA FECHA DE FIN NO PUEDE SER MENOR A LA FECHA DE INICIO.',
                    ],
                ],
            ], 422);
        }

        return null;
    }

    private function hasOverlappingDates(string $fechaInicio, string $fechaFin, ?int $ignoreId = null): bool
    {
        return Periodo::query()
            ->when($ignoreId, fn ($query) => $query->where('id_periodo', '!=', $ignoreId))
            ->whereDate('fecha_inicio', '<=', $fechaFin)
            ->whereDate('fecha_fin', '>=', $fechaInicio)
            ->exists();
    }

    private function overlapResponse()
    {
        return response()->json([
            'success' => false,
            'message' => 'LAS FECHAS DEL PERIODO SE TRASLAPAN CON OTRO PERIODO EXISTENTE.',
            'errors' => [
                'fecha_inicio' => [
                    'EL RANGO DE FECHAS SE TRASLAPA CON OTRO PERIODO ESCOLAR.',
                ],
                'fecha_fin' => [
                    'EL RANGO DE FECHAS SE TRASLAPA CON OTRO PERIODO ESCOLAR.',
                ],
            ],
        ], 422);
    }

    private function notFoundResponse()
    {
        return response()->json([
            'success' => false,
            'message' => 'PERIODO ESCOLAR NO ENCONTRADO.',
        ], 404);
    }

    private function deactivateOtherActivePeriods(?int $exceptId = null): void
    {
        Periodo::query()
            ->where('activo', true)
            ->when($exceptId, fn ($query) => $query->where('id_periodo', '!=', $exceptId))
            ->update([
                'estatus' => 'INACTIVO',
                'activo' => false,
            ]);
    }

    private function formatPeriodo(Periodo $periodo): array
    {
        return [
            'id_periodo' => $periodo->id_periodo,
            'nombre_periodo' => $periodo->nombre_periodo,
            'fecha_inicio' => $this->formatDate($periodo->fecha_inicio),
            'fecha_fin' => $this->formatDate($periodo->fecha_fin),
            'estatus' => $periodo->estatus,
            'activo' => (bool) $periodo->activo,
            'created_at' => optional($periodo->created_at)->toJSON(),
            'updated_at' => optional($periodo->updated_at)->toJSON(),
        ];
    }

    private function formatDate($date): ?string
    {
        return $date ? Carbon::parse($date)->format('Y-m-d') : null;
    }
}
