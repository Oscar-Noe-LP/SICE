// API para Especialidades
const API_URL = import.meta.env.VITE_API_URL

/**
 * Obtener todas las especialidades
 * @returns {Promise<Array>} Lista de especialidades
 */
export const obtenerEspecialidades = async () => {
  try {
    const response = await fetch(`${API_URL}/api/especialidades`)
    if (!response.ok) throw new Error('Error al obtener especialidades')
    return await response.json()
  } catch (error) {
    console.error('Error en obtenerEspecialidades:', error)
    return []
  }
}

/**
 * Obtener una especialidad por ID
 * @param {number} id - ID de la especialidad
 * @returns {Promise<Object>} Datos de la especialidad
 */
export const obtenerEspecialidad = async (id) => {
  try {
    const response = await fetch(`${API_URL}/api/especialidades/${id}`)
    if (!response.ok) throw new Error('Error al obtener especialidad')
    return await response.json()
  } catch (error) {
    console.error('Error en obtenerEspecialidad:', error)
    throw error
  }
}

/**
 * Crear nueva especialidad
 * @param {Object} data - Datos de la especialidad
 * @returns {Promise<Object>} Especialidad creada
 */
export const crearEspecialidad = async (data) => {
  try {
    const response = await fetch(`${API_URL}/api/especialidades`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    })
    if (!response.ok) throw new Error('Error al crear especialidad')
    return await response.json()
  } catch (error) {
    console.error('Error en crearEspecialidad:', error)
    throw error
  }
}

/**
 * Actualizar especialidad
 * @param {number} id - ID de la especialidad
 * @param {Object} data - Datos a actualizar
 * @returns {Promise<Object>} Especialidad actualizada
 */
export const actualizarEspecialidad = async (id, data) => {
  try {
    const response = await fetch(`${API_URL}/api/especialidades/${id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    })
    if (!response.ok) throw new Error('Error al actualizar especialidad')
    return await response.json()
  } catch (error) {
    console.error('Error en actualizarEspecialidad:', error)
    throw error
  }
}

/**
 * Eliminar especialidad
 * @param {number} id - ID de la especialidad
 * @returns {Promise<Object>} Resultado de la operación
 */
export const eliminarEspecialidad = async (id) => {
  try {
    const response = await fetch(`${API_URL}/api/especialidades/${id}`, {
      method: 'DELETE'
    })
    if (!response.ok) throw new Error('Error al eliminar especialidad')
    return await response.json()
  } catch (error) {
    console.error('Error en eliminarEspecialidad:', error)
    throw error
  }
}

/**
 * Obtener carreras para el selector
 * @returns {Promise<Array>} Lista de carreras
 */
export const obtenerCarreras = async () => {
  try {
    const response = await fetch(`${API_URL}/api/carreras`)
    if (!response.ok) throw new Error('Error al obtener carreras')
    return await response.json()
  } catch (error) {
    console.error('Error en obtenerCarreras:', error)
    // Datos mock para demo
    return [
      { id: 1, nombre: 'INGENIERÍA EN SISTEMAS COMPUTACIONALES' },
      { id: 2, nombre: 'INGENIERÍA INDUSTRIAL' },
      { id: 3, nombre: 'INGENIERÍA EN GESTIÓN EMPRESARIAL' },
      { id: 4, nombre: 'INGENIERÍA CIVIL' },
      { id: 5, nombre: 'INGENIERÍA ELÉCTRICA' }
    ]
  }
}