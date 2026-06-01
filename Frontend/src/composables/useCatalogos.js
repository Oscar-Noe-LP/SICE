// src/composables/useCatalogos.js
// Composable centralizado para cargar catálogos desde el backend.
// Uso: importar las funciones y listas que necesites en cada vista.

import { ref } from 'vue'

const API = `${import.meta.env.VITE_API_URL}/api`

export function useCatalogos() {

  // ── Listas reactivas ──────────────────────────────────────────
  const carreras       = ref([])
  const estatus        = ref([])
  const generos        = ref([])
  const materias       = ref([])
  const periodos       = ref([])
  const grupos         = ref([])
  const turnos         = ref([])
  const tiposSolicitud = ref([])
  /** Tipos de evaluación — reservado para uso futuro; el select del modal
   *  se muestra solo si hay elementos, así que el array vacío lo oculta. */
  const tiposEval      = ref([])

  // ── Estado de carga compartido ────────────────────────────────
  const cargandoCatalogos = ref(false)
  const errorCatalogos    = ref(null)

  // ── Helper interno ────────────────────────────────────────────
  async function fetchCatalogo(endpoint, lista) {
    // 30 s — mismo margen que axios para Railway cold starts
    const ctrl = new AbortController()
    const timer = setTimeout(() => ctrl.abort(), 30_000)
    try {
      const res = await fetch(`${API}/${endpoint}`, { signal: ctrl.signal })
      if (!res.ok) throw new Error(`Error al cargar ${endpoint} (${res.status})`)
      lista.value = await res.json()
    } finally {
      clearTimeout(timer)
    }
  }

  // ── Funciones individuales de carga ───────────────────────────
  const cargarCarreras       = () => fetchCatalogo('carreras',              carreras)
  const cargarEstatus        = () => fetchCatalogo('estatus-alumno',        estatus)
  const cargarGeneros        = () => fetchCatalogo('generos',               generos)
  const cargarMaterias       = () => fetchCatalogo('materias',              materias)
  const cargarPeriodos       = () => fetchCatalogo('periodos',              periodos)
  const cargarGrupos         = () => fetchCatalogo('grupos',                grupos)
  const cargarTurnos         = () => fetchCatalogo('turnos',                turnos)
  const cargarTiposSolicitud = () => fetchCatalogo('comite/tipos-solicitud',tiposSolicitud)

  /** Mapa de clave → función de carga para cargarCatalogos() */
  const LOADERS = {
    carreras:       cargarCarreras,
    estatus:        cargarEstatus,
    generos:        cargarGeneros,
    materias:       cargarMaterias,
    periodos:       cargarPeriodos,
    grupos:         cargarGrupos,
    turnos:         cargarTurnos,
    tiposSolicitud: cargarTiposSolicitud,
    // tiposEval no tiene endpoint todavía — se queda vacío y el UI lo oculta
    tiposEval:      () => Promise.resolve(),
  }

  /**
   * Carga varios catálogos en paralelo.
   * @param {string[]} keys  Claves del mapa LOADERS, p.ej. ['periodos','grupos']
   *                         Si se omite, carga todos los catálogos conocidos.
   */
  const cargarCatalogos = async (keys) => {
    const targets = keys ?? Object.keys(LOADERS)
    cargandoCatalogos.value = true
    errorCatalogos.value    = null
    try {
      await Promise.all(targets.map(k => {
        if (LOADERS[k]) return LOADERS[k]()
        console.warn(`[useCatalogos] Clave desconocida: "${k}"`)
        return Promise.resolve()
      }))
    } catch (err) {
      console.error('[useCatalogos] cargarCatalogos:', err.message)
      errorCatalogos.value = err.message
    } finally {
      cargandoCatalogos.value = false
    }
  }

  return {
    // Listas
    carreras,
    estatus,
    generos,
    materias,
    periodos,
    grupos,
    turnos,
    tiposSolicitud,
    tiposEval,

    // Estado compartido
    cargandoCatalogos,
    errorCatalogos,

    // Funciones individuales (retrocompatibilidad)
    cargarCarreras,
    cargarEstatus,
    cargarGeneros,
    cargarMaterias,
    cargarPeriodos,
    cargarGrupos,
    cargarTurnos,
    cargarTiposSolicitud,

    // Función unificada (nueva)
    cargarCatalogos,
  }
}
