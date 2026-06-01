import api from './axios'

// ============================================
// EMPLEADOS
// ============================================

export const getEmpleados = async (filtros = {}) => {
  try {
    const params = new URLSearchParams()
    if (filtros.nombre) params.append('nombre', filtros.nombre)
    if (filtros.numero_empleado) params.append('numero_empleado', filtros.numero_empleado)
    if (filtros.id_puesto) params.append('id_puesto', filtros.id_puesto)
    if (filtros.estatus !== undefined) params.append('estatus', filtros.estatus)

    const response = await api.get(`/empleados?${params.toString()}`)
    return response.data
  } catch (error) {
    console.error('Error al obtener empleados:', error)
    throw error
  }
}

export const getEmpleadoDetalle = async (id) => {
  try {
    const response = await api.get(`/empleados/${id}`)
    return response.data
  } catch (error) {
    console.error('Error al obtener detalle del empleado:', error)
    throw error
  }
}

export const getDepartamentos = async () => {
  try {
    const response = await api.get('/empleados/departamentos')
    return response.data
  } catch (error) {
    console.error('Error al obtener departamentos:', error)
    throw error
  }
}

// ============================================
// PUESTOS
// ============================================

export const getPuestos = async () => {
  try {
    const response = await api.get('/puestos')
    return response.data
  } catch (error) {
    console.error('Error al obtener puestos:', error)
    throw error
  }
}

export const getPuestoDetalle = async (id) => {
  try {
    const response = await api.get(`/puestos/${id}`)
    return response.data
  } catch (error) {
    console.error('Error al obtener detalle del puesto:', error)
    throw error
  }
}

export const crearPuesto = async (datos) => {
  try {
    // Normalizar: frontend envía 'nombre' pero backend espera 'nombre_puesto'
    const datosEnvio = {
      nombre_puesto: datos.nombre_puesto || datos.nombre,
      descripcion: datos.descripcion
    }
    const response = await api.post('/puestos', datosEnvio)
    return response.data
  } catch (error) {
    console.error('Error al crear puesto:', error)
    throw error
  }
}

export const actualizarPuesto = async (id, datos) => {
  try {
    // Normalizar igual que en crear
    const datosEnvio = {
      nombre_puesto: datos.nombre_puesto || datos.nombre,
      descripcion: datos.descripcion
    }
    const response = await api.put(`/puestos/${id}`, datosEnvio)
    return response.data
  } catch (error) {
    console.error('Error al actualizar puesto:', error)
    throw error
  }
}

export const eliminarPuesto = async (id) => {
  try {
    const response = await api.delete(`/puestos/${id}`)
    return response.data
  } catch (error) {
    console.error('Error al eliminar puesto:', error)
    throw error
  }
}

// ============================================
// ADSCRIPCIONES
// ============================================

export const getAdscripciones = async (filtros = {}) => {
  try {
    const params = new URLSearchParams()
    if (filtros.id_empleado) params.append('id_empleado', filtros.id_empleado)
    if (filtros.id_departamento) params.append('id_departamento', filtros.id_departamento)
    if (filtros.activa !== undefined) params.append('activa', filtros.activa)

    const response = await api.get(`/adscripciones?${params.toString()}`)
    return response.data
  } catch (error) {
    console.error('Error al obtener adscripciones:', error)
    throw error
  }
}

export const getAdscripcionDetalle = async (id) => {
  try {
    const response = await api.get(`/adscripciones/${id}`)
    return response.data
  } catch (error) {
    console.error('Error al obtener detalle de adscripción:', error)
    throw error
  }
}

export const crearAdscripcion = async (datos) => {
  try {
    const response = await api.post('/adscripciones', datos)
    return response.data
  } catch (error) {
    console.error('Error al crear adscripción:', error)
    throw error
  }
}

export const actualizarAdscripcion = async (id, datos) => {
  try {
    const response = await api.put(`/adscripciones/${id}`, datos)
    return response.data
  } catch (error) {
    console.error('Error al actualizar adscripción:', error)
    throw error
  }
}

export const eliminarAdscripcion = async (id) => {
  try {
    const response = await api.delete(`/adscripciones/${id}`)
    return response.data
  } catch (error) {
    console.error('Error al eliminar adscripción:', error)
    throw error
  }
}

export const getAdscripcionActiva = async (idEmpleado) => {
  try {
    const response = await api.get(`/empleados/${idEmpleado}/adscripcion-activa`)
    return response.data
  } catch (error) {
    console.error('Error al verificar adscripción activa:', error)
    throw error
  }
}
