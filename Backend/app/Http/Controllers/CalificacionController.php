namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalificacionController extends Controller
{
    public function index(Request $request)
    {
        // puedes recibir filtros después (grupo, materia, etc)
        $grupoId = $request->query('grupo_id');

        $datos = DB::table('inscripcion as i')
            ->join('alumno as a', 'i.id_alumno', '=', 'a.id_alumno')
            ->join('persona as p', 'a.id_persona', '=', 'p.id_persona')
            ->join('calificacion as c', 'i.id_inscripcion', '=', 'c.id_inscripcion')
            ->join('evaluacion as e', 'c.id_evaluacion', '=', 'e.id_evaluacion')
            ->when($grupoId, function ($q) use ($grupoId) {
                $q->where('i.id_grupo', $grupoId);
            })
            ->select(
                'a.numero_control',
                DB::raw("CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) as nombre"),
                'e.nombre as evaluacion',
                'c.calificacion'
            )
            ->get();

        // transformar a formato que usa Vue
        $resultado = [];

        foreach ($datos as $d) {
            $key = $d->numero_control;

            if (!isset($resultado[$key])) {
                $resultado[$key] = [
                    'control' => $d->numero_control,
                    'nombre' => $d->nombre,
                    'p1' => null,
                    'p2' => null,
                    'proy' => null
                ];
            }

            if ($d->evaluacion == 'Parcial 1') {
                $resultado[$key]['p1'] = $d->calificacion;
            }

            if ($d->evaluacion == 'Parcial 2') {
                $resultado[$key]['p2'] = $d->calificacion;
            }

            if ($d->evaluacion == 'Proyecto') {
                $resultado[$key]['proy'] = $d->calificacion;
            }
        }

        return response()->json(array_values($resultado));
    }
}