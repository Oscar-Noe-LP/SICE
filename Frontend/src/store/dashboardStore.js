import { reactive } from 'vue'

const API_URL = import.meta.env.VITE_API_URL

export const dashboardState = reactive({
  cargando:        false,
  error:           null,
  carreraActiva:   null,

  kpis: {
    totalAlumnos:             0,
    nuevosAlumnos:            0,
    inscripciones:            0,
    inscripcionesCompletas:   0,
    inscripcionesPendientes:  0,
    pctInscripciones:         0,
    gruposActivos:            0,
    numCarreras:              0,
    adeudosPendientes:        0,
    consultasHoy:             0,
    periodoActivo:            'ENE – JUN 2025',
  },

  carreras:    [],
  carreraData: [],
  semestreData:[],

  bitacora:         [],
  cargandoBitacora: false,
  errorBitacora:    false,
})

export const COLORES_CARRERA = [
  '#132B4F',
  '#1A4184',
  '#1D52B7',
  '#2F80ED',
  '#27AE60',
  '#F2994A',
  '#0B2545',
]

const FALLBACK_CARRERAS = [
  { id_carrera: 1, nombre: 'ING. SISTEMAS COMPUTACIONALES', grupos: 12, matriculas: 312, regulares: 268, irregulares: 44  },
  { id_carrera: 2, nombre: 'ING. INDUSTRIAL',               grupos: 10, matriculas: 268, regulares: 231, irregulares: 37  },
  { id_carrera: 3, nombre: 'ING. ELECTRICA',                grupos:  8, matriculas: 198, regulares: 165, irregulares: 33  },
  { id_carrera: 4, nombre: 'ING. MECANICA',                 grupos:  7, matriculas: 174, regulares: 148, irregulares: 26  },
  { id_carrera: 5, nombre: 'LIC. GESTION EMPRESARIAL',      grupos:  6, matriculas: 156, regulares: 134, irregulares: 22  },
  { id_carrera: 6, nombre: 'ING. BIOQUIMICA',               grupos:  9, matriculas: 176, regulares: 152, irregulares: 24  },
]

const FALLBACK_SEMESTRES = [
  { semestre: '1', cantidad: 180 },
  { semestre: '2', cantidad: 165 },
  { semestre: '3', cantidad: 158 },
  { semestre: '4', cantidad: 144 },
  { semestre: '5', cantidad: 152 },
  { semestre: '6', cantidad: 138 },
  { semestre: '7', cantidad: 175 },
  { semestre: '8', cantidad: 172 },
]

export async function cargarDashboard() {
  dashboardState.cargando = true
  dashboardState.error    = null
  try {
    const res  = await fetch(`${API_URL}/api/dashboard/kpis`)
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    const data = await res.json()
    if (data.kpis)          Object.assign(dashboardState.kpis, data.kpis)
    if (data.carreras)      dashboardState.carreras     = data.carreras
    if (data.carrera_data)  dashboardState.carreraData  = data.carrera_data
    if (data.semestre_data) dashboardState.semestreData = data.semestre_data
  } catch (e) {
    console.warn('[dashboardStore] Fallback activado:', e.message)
    _fallback()
  } finally {
    dashboardState.cargando = false
  }
}

function _fallback() {
  dashboardState.carreras     = FALLBACK_CARRERAS
  dashboardState.carreraData  = FALLBACK_CARRERAS.map((c) => ({
    carrera:    c.nombre,
    total:      c.matriculas,
    porcentaje: Math.round((c.matriculas / FALLBACK_CARRERAS[0].matriculas) * 100),
  }))
  dashboardState.semestreData = FALLBACK_SEMESTRES
  const total = FALLBACK_CARRERAS.reduce((s, c) => s + c.matriculas, 0)
  Object.assign(dashboardState.kpis, {
    totalAlumnos:             total,
    inscripciones:            1147,
    inscripcionesCompletas:   1147,
    inscripcionesPendientes:  137,
    pctInscripciones:         89,
    gruposActivos:            FALLBACK_CARRERAS.reduce((s, c) => s + c.grupos, 0),
    numCarreras:              FALLBACK_CARRERAS.length,
    nuevosAlumnos:            38,
    adeudosPendientes:        23,
    consultasHoy:             47,
  })
}

export async function cargarBitacora() {
  dashboardState.cargandoBitacora = true
  dashboardState.errorBitacora    = false
  try {
    const res  = await fetch(`${API_URL}/api/bitacora?limit=4`)
    if (!res.ok) throw new Error()
    const data = await res.json()
    dashboardState.bitacora = Array.isArray(data) ? data : (data.bitacora ?? [])
  } catch {
    dashboardState.errorBitacora = true
  } finally {
    dashboardState.cargandoBitacora = false
  }
}

export function setCarreraActiva(id) {
  dashboardState.carreraActiva = id === dashboardState.carreraActiva ? null : id
}

export const formatNum = (n) => n?.toLocaleString('es-MX') ?? '0'

export const tiempoRelativo = (f) => {
  if (!f) return ''
  const m = Math.floor((Date.now() - new Date(f).getTime()) / 60000)
  if (m < 1)  return 'AHORA'
  if (m < 60) return `HACE ${m} MIN`
  const h = Math.floor(m / 60)
  if (h < 24) return `HACE ${h} H`
  return `HACE ${Math.floor(h / 24)} DIA(S)`
}

export const claseBadge = (a = '') => {
  const s = a.toLowerCase()
  if (s.includes('login') || s.includes('acceso') || s.includes('consulta')) return 'bg-b'
  if (s.includes('cre') || s.includes('registr') || s.includes('inscri'))    return 'bg-g'
  if (s.includes('edit') || s.includes('actualiz'))                           return 'bg-a'
  if (s.includes('elim') || s.includes('bor') || s.includes('baja'))         return 'bg-r'
  return 'bg-b'
}