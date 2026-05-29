// src/stores/analyticsStore.js
// SICE — Equipo 4 — Sprint 2: Análisis Académico Visual
// Pinia store para datos de análisis académico

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

const API_URL = import.meta.env.VITE_API_URL

// ─────────────────────────────────────────────────────────────────────────────
// DATOS DE FALLBACK (mock) — se usan cuando el backend no está disponible
// ─────────────────────────────────────────────────────────────────────────────

const FALLBACK_APROBACION = [
  {
    id_grupo: 1,
    materia: 'CALCULO DIFERENCIAL',
    docente: 'DR. MARTINEZ LOPEZ',
    semestre: 1,
    carrera: 'SISTEMAS COMPUTACIONALES',
    parciales: [
      { numero: 1, aprobados: 28, reprobados: 12, total: 40, porcentaje_aprobacion: 70 },
      { numero: 2, aprobados: 25, reprobados: 15, total: 40, porcentaje_aprobacion: 62.5 },
      { numero: 3, aprobados: 30, reprobados: 10, total: 40, porcentaje_aprobacion: 75 },
    ],
  },
  {
    id_grupo: 2,
    materia: 'FUNDAMENTOS DE PROGRAMACION',
    docente: 'ING. GARCIA REYES',
    semestre: 1,
    carrera: 'SISTEMAS COMPUTACIONALES',
    parciales: [
      { numero: 1, aprobados: 35, reprobados: 5,  total: 40, porcentaje_aprobacion: 87.5 },
      { numero: 2, aprobados: 32, reprobados: 8,  total: 40, porcentaje_aprobacion: 80 },
      { numero: 3, aprobados: 36, reprobados: 4,  total: 40, porcentaje_aprobacion: 90 },
    ],
  },
  {
    id_grupo: 3,
    materia: 'ALGEBRA LINEAL',
    docente: 'DRA. HERNANDEZ CRUZ',
    semestre: 2,
    carrera: 'SISTEMAS COMPUTACIONALES',
    parciales: [
      { numero: 1, aprobados: 22, reprobados: 18, total: 40, porcentaje_aprobacion: 55 },
      { numero: 2, aprobados: 19, reprobados: 21, total: 40, porcentaje_aprobacion: 47.5 },
      { numero: 3, aprobados: 24, reprobados: 16, total: 40, porcentaje_aprobacion: 60 },
    ],
  },
  {
    id_grupo: 4,
    materia: 'ESTRUCTURA DE DATOS',
    docente: 'ING. RAMIREZ FLORES',
    semestre: 3,
    carrera: 'SISTEMAS COMPUTACIONALES',
    parciales: [
      { numero: 1, aprobados: 30, reprobados: 8,  total: 38, porcentaje_aprobacion: 78.9 },
      { numero: 2, aprobados: 27, reprobados: 11, total: 38, porcentaje_aprobacion: 71.1 },
      { numero: 3, aprobados: 33, reprobados: 5,  total: 38, porcentaje_aprobacion: 86.8 },
    ],
  },
  {
    id_grupo: 5,
    materia: 'TERMODINAMICA',
    docente: 'DR. JIMENEZ SOTO',
    semestre: 2,
    carrera: 'INGENIERIA MECANICA',
    parciales: [
      { numero: 1, aprobados: 20, reprobados: 15, total: 35, porcentaje_aprobacion: 57.1 },
      { numero: 2, aprobados: 18, reprobados: 17, total: 35, porcentaje_aprobacion: 51.4 },
      { numero: 3, aprobados: 22, reprobados: 13, total: 35, porcentaje_aprobacion: 62.9 },
    ],
  },
]

const FALLBACK_TRONCO_COMUN = [
  {
    materia: 'CALCULO DIFERENCIAL',
    semestre: 1,
    es_tronco_comun: true,
    rendimiento_por_carrera: [
      { carrera: 'SISTEMAS',    id_carrera: 1, promedio: 7.8, aprobacion: 72, total_alumnos: 312 },
      { carrera: 'INDUSTRIAL',  id_carrera: 2, promedio: 7.2, aprobacion: 65, total_alumnos: 268 },
      { carrera: 'ELECTRICA',   id_carrera: 3, promedio: 7.5, aprobacion: 69, total_alumnos: 198 },
      { carrera: 'MECANICA',    id_carrera: 4, promedio: 7.0, aprobacion: 62, total_alumnos: 174 },
      { carrera: 'EMPRESARIAL', id_carrera: 5, promedio: 8.1, aprobacion: 78, total_alumnos: 156 },
      { carrera: 'BIOQUIMICA',  id_carrera: 6, promedio: 8.3, aprobacion: 81, total_alumnos: 176 },
    ],
  },
  {
    materia: 'ALGEBRA LINEAL',
    semestre: 1,
    es_tronco_comun: true,
    rendimiento_por_carrera: [
      { carrera: 'SISTEMAS',    id_carrera: 1, promedio: 7.1, aprobacion: 60, total_alumnos: 312 },
      { carrera: 'INDUSTRIAL',  id_carrera: 2, promedio: 6.8, aprobacion: 55, total_alumnos: 268 },
      { carrera: 'ELECTRICA',   id_carrera: 3, promedio: 7.3, aprobacion: 64, total_alumnos: 198 },
      { carrera: 'MECANICA',    id_carrera: 4, promedio: 6.5, aprobacion: 52, total_alumnos: 174 },
      { carrera: 'EMPRESARIAL', id_carrera: 5, promedio: 7.6, aprobacion: 70, total_alumnos: 156 },
      { carrera: 'BIOQUIMICA',  id_carrera: 6, promedio: 7.9, aprobacion: 74, total_alumnos: 176 },
    ],
  },
  {
    materia: 'QUIMICA',
    semestre: 1,
    es_tronco_comun: true,
    rendimiento_por_carrera: [
      { carrera: 'SISTEMAS',    id_carrera: 1, promedio: 8.2, aprobacion: 82, total_alumnos: 312 },
      { carrera: 'INDUSTRIAL',  id_carrera: 2, promedio: 7.9, aprobacion: 76, total_alumnos: 268 },
      { carrera: 'ELECTRICA',   id_carrera: 3, promedio: 7.7, aprobacion: 73, total_alumnos: 198 },
      { carrera: 'MECANICA',    id_carrera: 4, promedio: 7.5, aprobacion: 70, total_alumnos: 174 },
      { carrera: 'EMPRESARIAL', id_carrera: 5, promedio: 8.0, aprobacion: 78, total_alumnos: 156 },
      { carrera: 'BIOQUIMICA',  id_carrera: 6, promedio: 9.1, aprobacion: 92, total_alumnos: 176 },
    ],
  },
  {
    materia: 'FISICA GENERAL',
    semestre: 1,
    es_tronco_comun: true,
    rendimiento_por_carrera: [
      { carrera: 'SISTEMAS',    id_carrera: 1, promedio: 7.4, aprobacion: 67, total_alumnos: 312 },
      { carrera: 'INDUSTRIAL',  id_carrera: 2, promedio: 7.6, aprobacion: 71, total_alumnos: 268 },
      { carrera: 'ELECTRICA',   id_carrera: 3, promedio: 8.0, aprobacion: 79, total_alumnos: 198 },
      { carrera: 'MECANICA',    id_carrera: 4, promedio: 7.8, aprobacion: 75, total_alumnos: 174 },
      { carrera: 'EMPRESARIAL', id_carrera: 5, promedio: 7.2, aprobacion: 64, total_alumnos: 156 },
      { carrera: 'BIOQUIMICA',  id_carrera: 6, promedio: 7.9, aprobacion: 77, total_alumnos: 176 },
    ],
  },
]

// ─────────────────────────────────────────────────────────────────────────────
// STORE
// ─────────────────────────────────────────────────────────────────────────────

export const useAnalyticsStore = defineStore('analytics', () => {

  // ── Estado ─────────────────────────────────────────────────────────────────
  const aprobacionData     = ref([])
  const troncoComunData    = ref([])

  const cargandoAprobacion  = ref(false)
  const cargandoTroncoComun = ref(false)

  const errorAprobacion     = ref(null)
  const errorTroncoComun    = ref(null)

  const usandoFallback      = ref(false)

  // Filtros activos
  const filtros = ref({
    carrera:  '',
    semestre: '',
    materia:  '',
  })

  // ── Computed ───────────────────────────────────────────────────────────────

  /** Grupos filtrados según los filtros activos */
  const aprobacionFiltrada = computed(() => {
    return aprobacionData.value.filter(g => {
      const okCarrera  = !filtros.value.carrera  || g.carrera  === filtros.value.carrera
      const okSemestre = !filtros.value.semestre || g.semestre === Number(filtros.value.semestre)
      const okMateria  = !filtros.value.materia  ||
        g.materia.toLowerCase().includes(filtros.value.materia.toLowerCase())
      return okCarrera && okSemestre && okMateria
    })
  })

  /** Promedio general de aprobación en todos los grupos filtrados */
  const promedioAprobacionGlobal = computed(() => {
    const grupos = aprobacionFiltrada.value
    if (!grupos.length) return 0
    const suma = grupos.reduce((acc, g) => {
      const promGrupo = g.parciales.reduce((a, p) => a + p.porcentaje_aprobacion, 0) / g.parciales.length
      return acc + promGrupo
    }, 0)
    return Math.round(suma / grupos.length)
  })

  /** Materias de tronco común filtradas */
  const troncoComunFiltrado = computed(() => {
    if (!filtros.value.materia) return troncoComunData.value
    return troncoComunData.value.filter(m =>
      m.materia.toLowerCase().includes(filtros.value.materia.toLowerCase())
    )
  })

  /** Lista de carreras únicas presentes en los datos de aprobación */
  const carrerasDisponibles = computed(() => {
    const set = new Set(aprobacionData.value.map(g => g.carrera))
    return Array.from(set).sort()
  })

  /** Lista de semestres únicos presentes */
  const semestresDisponibles = computed(() => {
    const set = new Set(aprobacionData.value.map(g => g.semestre))
    return Array.from(set).sort((a, b) => a - b)
  })

  // ── Acciones ───────────────────────────────────────────────────────────────

  /**
   * Carga los datos de aprobación/reprobación por parciales desde el backend.
   * Si el endpoint falla, usa los datos de fallback.
   *
   * Endpoint esperado: GET /api/analytics/aprobacion
   * Respuesta esperada: array de objetos igual a FALLBACK_APROBACION
   */
  const cargarAprobacion = async () => {
    cargandoAprobacion.value = true
    errorAprobacion.value    = null
    try {
      const res = await fetch(`${API_URL}/api/analytics/aprobacion`)
      if (!res.ok) throw new Error(`HTTP ${res.status}`)
      const data = await res.json()
      aprobacionData.value = Array.isArray(data) ? data : (data.grupos || [])
      usandoFallback.value = false
    } catch (err) {
      console.warn('[analyticsStore] Usando fallback para aprobación:', err.message)
      aprobacionData.value = FALLBACK_APROBACION
      usandoFallback.value = true
      // No seteamos errorAprobacion para no bloquear la UI con fallback visible
    } finally {
      cargandoAprobacion.value = false
    }
  }

  /**
   * Carga el rendimiento de materias de tronco común por carrera.
   * Si el endpoint falla, usa los datos de fallback.
   *
   * Endpoint esperado: GET /api/analytics/tronco-comun
   * Respuesta esperada: array de objetos igual a FALLBACK_TRONCO_COMUN
   */
  const cargarTroncoComun = async () => {
    cargandoTroncoComun.value = true
    errorTroncoComun.value    = null
    try {
      const res = await fetch(`${API_URL}/api/analytics/tronco-comun`)
      if (!res.ok) throw new Error(`HTTP ${res.status}`)
      const data = await res.json()
      troncoComunData.value = Array.isArray(data) ? data : (data.materias || [])
      usandoFallback.value  = false
    } catch (err) {
      console.warn('[analyticsStore] Usando fallback para tronco común:', err.message)
      troncoComunData.value = FALLBACK_TRONCO_COMUN
      usandoFallback.value  = true
    } finally {
      cargandoTroncoComun.value = false
    }
  }

  /** Carga todos los datos de analytics en paralelo */
  const cargarTodo = async () => {
    await Promise.all([cargarAprobacion(), cargarTroncoComun()])
  }

  /** Actualiza uno o varios filtros y resetea paginación si es necesario */
  const setFiltro = (key, value) => {
    filtros.value[key] = value
  }

  const limpiarFiltros = () => {
    filtros.value = { carrera: '', semestre: '', materia: '' }
  }

  // ── Exponer ────────────────────────────────────────────────────────────────
  return {
    // Estado
    aprobacionData,
    troncoComunData,
    cargandoAprobacion,
    cargandoTroncoComun,
    errorAprobacion,
    errorTroncoComun,
    usandoFallback,
    filtros,

    // Computed
    aprobacionFiltrada,
    promedioAprobacionGlobal,
    troncoComunFiltrado,
    carrerasDisponibles,
    semestresDisponibles,

    // Acciones
    cargarAprobacion,
    cargarTroncoComun,
    cargarTodo,
    setFiltro,
    limpiarFiltros,
  }
})