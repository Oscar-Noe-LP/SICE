<template>
  <MainLayout>
    <div class="historial-page">
      <!-- BREADCRUMB -->
      <nav class="breadcrumb" aria-label="MIGA DE PAN">
        <router-link to="/servicios-escolares" class="breadcrumb-link">SERVICIOS ESCOLARES</router-link>
        <span class="breadcrumb-sep" aria-hidden="true">›</span>
        <router-link to="/inscripcion" class="breadcrumb-link">INSCRIPCIONES</router-link>
        <span class="breadcrumb-sep" aria-hidden="true">›</span>
        <span class="breadcrumb-actual" aria-current="page">HISTORIAL</span>
      </nav>

      <!-- ENCABEZADO -->
      <div class="page-header">
        <div>
          <h1 class="page-title">HISTORIAL DE INSCRIPCIONES</h1>
          <span class="page-subtitle">CONSULTA LAS ÚLTIMAS INSCRIPCIONES REALIZADAS EN EL SISTEMA.</span>
        </div>

        <button class="btn-recargar" @click="cargarHistorial">
          <svg xmlns="http://www.w3.org/2000/svg" class="btn-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          RECARGAR
        </button>
      </div>

      <!-- NOTIFICACIÓN -->
      <transition name="fade">
        <div v-if="notificacion.visible" class="notificacion" :class="notificacion.tipo">
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- FILTROS -->
      <div class="filtros-card">
        <div class="campo-grupo">
          <label>BUSCAR</label>
          <div class="input-con-icono">
            <svg xmlns="http://www.w3.org/2000/svg" class="input-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              type="text"
              v-model="busqueda"
              placeholder="ALUMNO, NÚMERO DE CONTROL, MATERIA O DOCENTE..."
              @input="normalizarBusqueda"
              @keyup.enter="cargarHistorial"
            />
          </div>
        </div>

        <div class="campo-grupo campo-periodo">
          <label>PERIODO</label>
          <select v-model="periodo" class="select-periodo" :disabled="cargandoPeriodos">
            <option :value="null">TODOS LOS PERIODOS</option>
            <option v-for="p in periodos" :key="p.id" :value="p.id">
              {{ p.nombre?.toUpperCase() }}
            </option>
          </select>
        </div>

        <div class="campo-grupo campo-limite">
          <label>LÍMITE</label>
          <select v-model="limite" class="select-periodo">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
        </div>

        <button class="btn-filtrar" @click="cargarHistorial" :disabled="cargando">
          <span v-if="cargando" class="spinner-btn"></span>
          {{ cargando ? 'CARGANDO...' : 'FILTRAR' }}
        </button>
      </div>

      <!-- KPIS -->
      <div class="kpis-grid">
        <div class="kpi-card">
          <span class="kpi-numero">{{ historial.length }}</span>
          <span class="kpi-label">REGISTROS MOSTRADOS</span>
        </div>

        <div class="kpi-card">
          <span class="kpi-numero">{{ totalAlumnosUnicos }}</span>
          <span class="kpi-label">ALUMNOS</span>
        </div>

        <div class="kpi-card">
          <span class="kpi-numero">{{ totalMateriasUnicas }}</span>
          <span class="kpi-label">MATERIAS</span>
        </div>

        <div class="kpi-card">
          <span class="kpi-numero">{{ ultimoMovimiento }}</span>
          <span class="kpi-label">ÚLTIMO MOVIMIENTO</span>
        </div>
      </div>

      <!-- TABLA -->
      <div class="table-container">
        <div v-if="cargando" class="loading-overlay">
          <div class="loading-spinner"></div>
          <span>CARGANDO HISTORIAL...</span>
        </div>

        <table v-else-if="historial.length > 0" class="tabla-historial">
          <thead>
            <tr>
              <th>FECHA</th>
              <th>ALUMNO</th>
              <th>NÚMERO DE CONTROL</th>
              <th>MATERIA</th>
              <th>GRUPO</th>
              <th>DOCENTE</th>
              <th>PERIODO</th>
              <th class="text-center">ESTATUS</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="registro in historial" :key="registro.id">
              <td class="celda-fecha">
                <span>{{ registro.fecha }}</span>
                <small>{{ registro.hora }}</small>
              </td>
              <td class="celda-alumno">{{ registro.alumno }}</td>
              <td>{{ registro.noControl }}</td>
              <td class="celda-materia">{{ registro.materia }}</td>
              <td class="celda-grupo">{{ registro.claveGrupo }}</td>
              <td>{{ registro.docente }}</td>
              <td>{{ registro.periodo }}</td>
              <td class="text-center">
                <span class="estatus-badge" :class="registro.estatusClase">
                  {{ registro.estatus }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <h3>SIN REGISTROS</h3>
          <p>NO SE ENCONTRARON INSCRIPCIONES CON LOS FILTROS SELECCIONADOS.</p>
        </div>
      </div>

      <footer class="footer-institucional">
        © {{ new Date().getFullYear() }} TECNOLÓGICO NACIONAL DE MÉXICO · TODOS LOS DERECHOS RESERVADOS
      </footer>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`

const historial = ref([])
const periodos = ref([])
const busqueda = ref('')
const periodo = ref(null)
const limite = ref(25)

const cargando = ref(false)
const cargandoPeriodos = ref(false)

const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje: mensaje.toUpperCase(), tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

const normalizarBusqueda = () => {
  busqueda.value = busqueda.value.toUpperCase()
}

const formatearFecha = (valor) => {
  if (!valor) return { fecha: '—', hora: '—' }

  try {
    const fecha = new Date(valor)

    return {
      fecha: fecha.toLocaleDateString('es-MX', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
      }),
      hora: fecha.toLocaleTimeString('es-MX', {
        hour: '2-digit',
        minute: '2-digit'
      })
    }
  } catch {
    return { fecha: valor, hora: '—' }
  }
}

const normalizarRegistro = (r) => {
  const fechaObj = formatearFecha(r.fecha_inscripcion ?? r.created_at ?? r.fecha ?? r.updated_at)
  const estatus = (r.estatus ?? r.estado ?? 'INSCRITO').toString().toUpperCase()

  return {
    id: r.id_inscripcion ?? r.id ?? `${r.id_alumno}-${r.id_grupo}-${r.created_at}`,
    fecha: fechaObj.fecha,
    hora: fechaObj.hora,
    alumno: (r.alumno ?? r.nombre_alumno ?? r.nombre ?? 'SIN ALUMNO').toString().toUpperCase(),
    noControl: (r.numero_control ?? r.no_control ?? r.noControl ?? '—').toString().toUpperCase(),
    materia: (r.materia ?? r.nombre_materia ?? 'SIN MATERIA').toString().toUpperCase(),
    claveGrupo: (r.clave_grupo ?? r.grupo ?? '—').toString().toUpperCase(),
    docente: (r.docente ?? r.nombre_docente ?? 'SIN DOCENTE').toString().toUpperCase(),
    periodo: (r.periodo ?? r.nombre_periodo ?? '—').toString().toUpperCase(),
    estatus,
    estatusClase: estatus.includes('CANCEL') ? 'cancelado' : 'activo'
  }
}

const cargarPeriodos = async () => {
  cargandoPeriodos.value = true

  try {
    const response = await fetch(`${API}/periodos`, {
      headers: { 'Accept': 'application/json' }
    })

    if (!response.ok) throw new Error(`HTTP ${response.status}`)

    const data = await response.json()
    const lista = Array.isArray(data) ? data : data.periodos ?? []

    periodos.value = lista.map(p => ({
      id: p.id_periodo ?? p.id,
      nombre: p.nombre ?? p.descripcion ?? `PERIODO ${p.id_periodo ?? p.id}`
    }))
  } catch (error) {
    console.error('ERROR CARGANDO PERIODOS:', error)
    periodos.value = []
  } finally {
    cargandoPeriodos.value = false
  }
}

const obtenerRutasHistorial = () => {
  const params = new URLSearchParams()

  params.set('limit', String(limite.value))

  if (busqueda.value.trim()) params.set('q', busqueda.value.trim())
  if (periodo.value) params.set('id_periodo', String(periodo.value))

  const query = params.toString()

  return [
    `${API}/inscripcion/historial?${query}`,
    `${API}/inscripciones/historial?${query}`,
    `${API}/historial-inscripciones?${query}`
  ]
}

const cargarHistorial = async () => {
  cargando.value = true
  historial.value = []

  try {
    const rutas = obtenerRutasHistorial()
    let ultimoError = null

    for (const ruta of rutas) {
      try {
        const response = await fetch(ruta, {
          headers: { 'Accept': 'application/json' }
        })

        if (!response.ok) {
          ultimoError = new Error(`HTTP ${response.status}`)
          continue
        }

        const data = await response.json()
        const lista = Array.isArray(data)
          ? data
          : data.historial ?? data.inscripciones ?? data.data ?? []

        historial.value = lista.map(normalizarRegistro)
        return
      } catch (error) {
        ultimoError = error
      }
    }

    throw ultimoError || new Error('NO SE ENCONTRÓ ENDPOINT DE HISTORIAL')
  } catch (error) {
    console.error('ERROR CARGANDO HISTORIAL:', error)
    mostrarNotificacion('NO SE PUDO CARGAR EL HISTORIAL DE INSCRIPCIONES.', 'error')
  } finally {
    cargando.value = false
  }
}

const totalAlumnosUnicos = computed(() => {
  return new Set(historial.value.map(r => r.noControl)).size
})

const totalMateriasUnicas = computed(() => {
  return new Set(historial.value.map(r => r.materia)).size
})

const ultimoMovimiento = computed(() => {
  return historial.value[0]?.fecha ?? '—'
})

watch([periodo, limite], () => {
  cargarHistorial()
})

onMounted(async () => {
  await cargarPeriodos()
  await cargarHistorial()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap');

/* ============================================= */
/* BASE GENERAL - HISTORIAL DE INSCRIPCIONES     */
/* ============================================= */
.historial-page { width: 100%; min-width: 0; background: #F4F6F9; font-family: 'Montserrat', sans-serif; font-weight: 400; color: #333333; text-transform: uppercase; }
.historial-page * { font-family: 'Montserrat', sans-serif; box-sizing: border-box; }

/* ============================================= */
/* BREADCRUMB                                    */
/* ============================================= */
.breadcrumb { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 1rem; color: #828282; font-size: 0.875rem; line-height: 1; }
.breadcrumb-link { display: inline-flex; align-items: center; color: #828282; font-size: 0.875rem; font-weight: 500; line-height: 1; text-decoration: none; white-space: nowrap; transition: color 0.2s ease; cursor: pointer; }
.breadcrumb-link:hover { color: #2F80ED; text-decoration: underline; }
.breadcrumb-sep { display: inline-flex; align-items: center; justify-content: center; width: 10px; min-width: 10px; height: 1em; color: #BDBDBD; font-size: 1rem; font-weight: 500; line-height: 1; transform: translateY(-1px); user-select: none; }
.breadcrumb-actual { display: inline-flex; align-items: center; color: #2F80ED; font-size: 0.875rem; font-weight: 600; line-height: 1; white-space: nowrap; }

/* ============================================= */
/* ENCABEZADO                                    */
/* ============================================= */
.page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1.2rem; }
.page-title { color: #333333; font-size: 1.75rem; font-weight: 700; line-height: 1.2; letter-spacing: -0.02em; margin: 0; }
.page-subtitle { display: block; color: #4F4F4F; font-size: 0.875rem; font-weight: 400; line-height: 1.45; margin-top: 4px; }

/* ============================================= */
/* BOTÓN RECARGAR                                */
/* ============================================= */
.btn-recargar { display: flex; align-items: center; justify-content: center; gap: 7px; background: rgba(47,128,237,0.10); color: #1D52B7; border: 1px solid rgba(47,128,237,0.25); padding: 9px 18px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; line-height: 1.2; letter-spacing: 0.01em; cursor: pointer; transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease; white-space: nowrap; text-transform: uppercase; }
.btn-recargar:hover { background: rgba(47,128,237,0.15); border-color: #2F80ED; }
.btn-icono { width: 17px; height: 17px; stroke: #1D52B7; }

/* ============================================= */
/* NOTIFICACIÓN                                  */
/* ============================================= */
.notificacion { padding: 12px 18px; border-radius: 10px; font-size: 0.875rem; font-weight: 600; line-height: 1.4; margin-bottom: 1rem; }
.notificacion.exito { background: rgba(39,174,96,0.10); color: #27AE60; border: 1px solid rgba(39,174,96,0.35); }
.notificacion.error { background: #FFF0F0; color: #EB5757; border: 1px solid rgba(235,87,87,0.35); }
.fade-enter-active, .fade-leave-active { transition: all 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-6px); }

/* ============================================= */
/* FILTROS                                       */
/* ============================================= */
.filtros-card { display: grid; grid-template-columns: minmax(260px, 1fr) 220px 120px auto; gap: 1rem; align-items: end; background: #FFFFFF; border-radius: 12px; padding: 1.3rem 1.6rem; margin-bottom: 1.2rem; box-shadow: 0 4px 14px rgba(29,82,183,0.05); border: 1px solid #E0E0E0; }
.campo-grupo { display: flex; flex-direction: column; gap: 6px; }
.campo-grupo label { color: #333333; font-size: 0.8rem; font-weight: 600; line-height: 1.3; letter-spacing: 0.02em; }

.input-con-icono { position: relative; }
.input-icono { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; stroke: #828282; pointer-events: none; }
.input-con-icono input { width: 100%; padding: 11px 14px 11px 42px; border: 1.5px solid #E0E0E0; border-radius: 9px; background: #FFFFFF; color: #333333; font-size: 0.875rem; font-weight: 400; line-height: 1.4; text-transform: uppercase; outline: none; transition: border-color 0.2s ease, box-shadow 0.2s ease; }
.input-con-icono input::placeholder { color: #828282; font-size: 0.875rem; font-weight: 400; }
.input-con-icono input:focus { border-color: #1D52B7; box-shadow: 0 0 0 3px rgba(29,82,183,0.15); }

.select-periodo { width: 100%; padding: 11px 14px; border: 1.5px solid #E0E0E0; border-radius: 9px; background: #FFFFFF; color: #333333; font-size: 0.875rem; font-weight: 400; line-height: 1.4; text-transform: uppercase; outline: none; }
.select-periodo:focus { border-color: #1D52B7; box-shadow: 0 0 0 3px rgba(29,82,183,0.15); }

/* ============================================= */
/* BOTÓN FILTRAR                                 */
/* ============================================= */
.btn-filtrar { height: 43px; display: flex; align-items: center; justify-content: center; gap: 7px; background: #1D52B7; color: #FFFFFF; border: none; padding: 0 24px; border-radius: 9px; font-size: 0.85rem; font-weight: 600; line-height: 1.2; letter-spacing: 0.01em; cursor: pointer; white-space: nowrap; text-transform: uppercase; transition: background 0.2s ease; }
.btn-filtrar:hover:not(:disabled) { background: #2F80ED; }
.btn-filtrar:disabled { opacity: 0.55; cursor: not-allowed; }

/* ============================================= */
/* SPINNER                                       */
/* ============================================= */
.spinner-btn { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.4); border-top-color: #FFFFFF; border-radius: 50%; animation: girar 0.7s linear infinite; }
@keyframes girar { to { transform: rotate(360deg); } }

/* ============================================= */
/* KPIS                                          */
/* ============================================= */
.kpis-grid { display: grid; grid-template-columns: repeat(4, minmax(130px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
.kpi-card { background: #FFFFFF; border: 1px solid #E0E0E0; border-radius: 12px; padding: 1rem; text-align: center; box-shadow: 0 4px 14px rgba(29,82,183,0.05); }
.kpi-numero { display: block; color: #333333; font-size: 2rem; font-weight: 700; line-height: 1.1; }
.kpi-label { display: block; color: #828282; font-size: 0.72rem; font-weight: 600; letter-spacing: 0.05em; margin-top: 4px; }

/* ============================================= */
/* TABLA                                         */
/* ============================================= */
.table-container { position: relative; background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 14px rgba(29,82,183,0.05); border: 1px solid #E0E0E0; }
.loading-overlay { position: absolute; inset: 0; min-height: 220px; background: rgba(255,255,255,0.88); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; z-index: 10; color: #1D52B7; font-size: 0.875rem; font-weight: 600; }
.loading-spinner { width: 32px; height: 32px; border: 3px solid #E0E0E0; border-top-color: #1D52B7; border-radius: 50%; animation: girar 0.7s linear infinite; }

.tabla-historial { width: 100%; border-collapse: collapse; }
.tabla-historial th { background: #F2F4F7; padding: 13px 14px; text-align: left; color: #333333; font-size: 0.78rem; font-weight: 600; line-height: 1.3; letter-spacing: 0.02em; border-bottom: 1px solid #E0E0E0; white-space: nowrap; }
.tabla-historial td { padding: 13px 14px; border-bottom: 1px solid #E0E0E0; color: #333333; font-size: 0.85rem; font-weight: 400; line-height: 1.4; vertical-align: middle; }
.tabla-historial tbody tr:hover { background: rgba(29,82,183,0.05); }
.tabla-historial tbody tr:last-child td { border-bottom: none; }

.celda-fecha { white-space: nowrap; font-weight: 600; color: #1D52B7; }
.celda-fecha small { display: block; color: #4F4F4F; font-size: 0.72rem; font-weight: 400; line-height: 1.3; margin-top: 2px; }
.celda-alumno { color: #333333; font-weight: 600; min-width: 170px; }
.celda-materia { color: #333333; font-weight: 600; min-width: 170px; }
.celda-grupo { color: #1D52B7; font-weight: 700; white-space: nowrap; }

.estatus-badge { display: inline-flex; align-items: center; justify-content: center; background: rgba(39,174,96,0.10); color: #27AE60; border: 1px solid rgba(39,174,96,0.35); border-radius: 999px; padding: 4px 10px; font-weight: 700; font-size: 0.76rem; white-space: nowrap; }
.estatus-badge.cancelado { background: #FFF0F0; color: #EB5757; border-color: rgba(235,87,87,0.35); }
.text-center { text-align: center; }

/* ============================================= */
/* ESTADO VACÍO                                  */
/* ============================================= */
.estado-vacio { text-align: center; padding: 3.5rem 2rem; color: #4F4F4F; background: #FFFFFF; border-radius: 12px; border: 1px dashed #E0E0E0; }
.icono-vacio { width: 60px; height: 60px; stroke: #BDBDBD; margin-bottom: 14px; }
.estado-vacio h3 { color: #333333; font-size: 1rem; font-weight: 600; margin: 0 0 6px; }
.estado-vacio p { color: #4F4F4F; font-size: 0.875rem; font-weight: 400; margin: 0; }

/* ============================================= */
/* FOOTER                                        */
/* ============================================= */
.footer-institucional { text-align: center; color: #828282; font-size: 0.82rem; font-weight: 400; padding-top: 2rem; margin-top: 1rem; }

/* ============================================= */
/* RESPONSIVE                                    */
/* ============================================= */
@media (max-width: 900px) {
  .page-header { flex-direction: column; }
  .filtros-card { grid-template-columns: 1fr; }
  .kpis-grid { grid-template-columns: 1fr 1fr; }
  .btn-recargar, .btn-filtrar { width: 100%; justify-content: center; }
}
</style>