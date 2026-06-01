import api from './axios'

/**
 * GET /api/evaluaciones/{id_grupo}
 * Acepta un id_grupo (number|string) o un objeto { grupoId, periodoId, materiaId }.
 * Cuando se pasa un objeto, usa grupoId como parámetro de ruta.
 */
export const getEvaluaciones = async (idGrupoOrFiltros) => {
  const esFiltros = typeof idGrupoOrFiltros === 'object' && idGrupoOrFiltros !== null
  const id = esFiltros ? idGrupoOrFiltros.grupoId : idGrupoOrFiltros

  if (!id) return []

  const params = {}
  if (esFiltros && idGrupoOrFiltros.periodoId) params.periodo_id = idGrupoOrFiltros.periodoId
  if (esFiltros && idGrupoOrFiltros.materiaId) params.materia_id = idGrupoOrFiltros.materiaId

  const { data } = await api.get(`/evaluaciones/${id}`, { params })
  return data
}

export const eliminarEvaluacion = async (id) => {
  const { data } = await api.delete(`/evaluaciones/${id}`)
  return data
}

/**
 * Guarda una o varias evaluaciones.
 * @param {Object|Array} evaluacion  - Evaluación o lista de evaluaciones
 * @param {number|null}  id_grupo    - ID del grupo (requerido para nuevas evaluaciones)
 */
export const guardarEvaluaciones = async (evaluacion, id_grupo = null) => {
  const lista = Array.isArray(evaluacion) ? evaluacion : [evaluacion]
  const promesas = lista.map(e => {
    if (e.id_evaluacion) {
      // Edición: PUT por ID
      return api.put(`/evaluaciones/${e.id_evaluacion}`, {
        nombre:     e.nombre,
        porcentaje: e.porcentaje,
      })
    } else {
      // Creación: POST — requiere id_grupo
      const grupoId = id_grupo ?? e.id_grupo
      if (!grupoId) {
        console.warn('[evaluaciones] guardarEvaluaciones: id_grupo no proporcionado')
        return Promise.resolve(null)
      }
      return api.post('/evaluaciones/guardar', {
        id_grupo:   grupoId,
        nombre:     e.nombre,
        porcentaje: e.porcentaje,
      })
    }
  })
  return Promise.all(promesas)
}
