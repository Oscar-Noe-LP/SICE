import api from './axios'

export const getCalificacionesGrupo = async (filtros = {}) => {
  const params = {}
  if (filtros.grupo) params.grupo_id = filtros.grupo
  const { data } = await api.get('/calificaciones-grupo', { params })
  return data
}

export const guardarCalificaciones = async (alumnos) => {
  const lista = Array.isArray(alumnos) ? alumnos : [alumnos]
  const peticiones = []

  for (const alumno of lista) {
    const parciales = [
      { id_evaluacion: alumno.id_evaluacion_parcial_1, valor: alumno.p1 },
      { id_evaluacion: alumno.id_evaluacion_parcial_2, valor: alumno.p2 },
      { id_evaluacion: alumno.id_evaluacion_proyecto,  valor: alumno.proy },
    ]
    for (const p of parciales) {
      if (p.id_evaluacion && alumno.id_inscripcion && p.valor !== null && p.valor !== '') {
        peticiones.push({
          id_inscripcion: alumno.id_inscripcion,
          id_evaluacion:  p.id_evaluacion,
          calificacion:   Number(p.valor),
        })
      }
    }
  }

  if (peticiones.length === 0) {
    throw new Error('No hay calificaciones válidas para guardar (faltan IDs de evaluación)')
  }

  const LOTE = 5
  const resultados = []

  for (let i = 0; i < peticiones.length; i += LOTE) {
    const lote = peticiones.slice(i, i + LOTE)
    const res = await Promise.all(
      lote.map(p => api.post('/guardar-calificaciones', p))
    )
    resultados.push(...res)
  }

  return resultados
}