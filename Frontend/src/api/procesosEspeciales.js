// API para Procesos Especiales
const API_URL = import.meta.env.VITE_API_URL

/**
 * Buscar alumno por nombre o número de control
 * @param {string} termino - Texto a buscar
 * @returns {Promise<Array>} Lista de alumnos encontrados
 */
export const buscarAlumno = async (termino) => {
  const response = await fetch(`${API_URL}/api/alumnos/buscar?q=${encodeURIComponent(termino)}`)
  if (!response.ok) throw new Error('Error al buscar alumno')
  return await response.json()
}

/**
 * Obtener materias de un alumno (para corrección académica)
 * @param {number} alumnoId - ID del alumno
 * @returns {Promise<Array>} Lista de materias del alumno
 */
export const obtenerMateriasAlumno = async (alumnoId) => {
  const response = await fetch(`${API_URL}/api/alumnos/${alumnoId}/materias`)
  if (!response.ok) throw new Error('Error al obtener materias')
  return await response.json()
}

/**
 * Ejecutar cambio de carrera
 * @param {Object} data - Datos del proceso
 * @returns {Promise<Object>} Resultado del proceso
 */
export const ejecutarCambioCarrera = async (data) => {
  const response = await fetch(`${API_URL}/api/procesos/cambio-carrera`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
  if (!response.ok) throw new Error('Error al ejecutar cambio de carrera')
  return await response.json()
}

/**
 * Ejecutar baja temporal
 * @param {Object} data - Datos del proceso
 * @returns {Promise<Object>} Resultado del proceso
 */
export const ejecutarBajaTemporal = async (data) => {
  const response = await fetch(`${API_URL}/api/procesos/baja-temporal`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
  if (!response.ok) throw new Error('Error al ejecutar baja temporal')
  return await response.json()
}

/**
 * Ejecutar baja definitiva
 * @param {Object} data - Datos del proceso
 * @returns {Promise<Object>} Resultado del proceso
 */
export const ejecutarBajaDefinitiva = async (data) => {
  const response = await fetch(`${API_URL}/api/procesos/baja-definitiva`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
  if (!response.ok) throw new Error('Error al ejecutar baja definitiva')
  return await response.json()
}

/**
 * Ejecutar reactivación
 * @param {Object} data - Datos del proceso
 * @returns {Promise<Object>} Resultado del proceso
 */
export const ejecutarReactivacion = async (data) => {
  const response = await fetch(`${API_URL}/api/procesos/reactivacion`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
  if (!response.ok) throw new Error('Error al ejecutar reactivación')
  return await response.json()
}

/**
 * Ejecutar corrección académica
 * @param {Object} data - Datos del proceso
 * @returns {Promise<Object>} Resultado del proceso
 */
export const ejecutarCorreccionAcademica = async (data) => {
  const response = await fetch(`${API_URL}/api/procesos/correccion-academica`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
  if (!response.ok) throw new Error('Error al ejecutar corrección académica')
  return await response.json()
}