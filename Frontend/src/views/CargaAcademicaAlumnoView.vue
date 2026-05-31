<template>
  <MainLayout>
    <div class="carga-alumno-page">
      <!-- BREADCRUMB -->
      <nav class="breadcrumb" aria-label="MIGA DE PAN">
        <router-link to="/servicios-escolares" class="breadcrumb-link">SERVICIOS ESCOLARES</router-link>
        <span class="breadcrumb-sep" aria-hidden="true">›</span>
        <router-link to="/inscripcion" class="breadcrumb-link">INSCRIPCIONES</router-link>
        <span class="breadcrumb-sep" aria-hidden="true">›</span>
        <span class="breadcrumb-actual" aria-current="page">CARGAS ACADÉMICAS</span>
      </nav>

      <!-- ENCABEZADO -->
      <div class="page-header">
        <div>
          <h1 class="page-title">CARGAS ACADÉMICAS</h1>
          <span class="page-subtitle">CONSULTA LAS MATERIAS INSCRITAS DE CADA ALUMNO POR PERIODO.</span>
        </div>

        <button
          v-if="alumnoSeleccionado && cargaAcademica.length > 0"
          class="btn-exportar"
          @click="exportarCarga"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="btn-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          EXPORTAR CARGA
        </button>
      </div>

      <!-- NOTIFICACIÓN -->
      <transition name="fade">
        <div v-if="notificacion.visible" class="notificacion" :class="notificacion.tipo">
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- BUSCADOR -->
      <div class="tarjeta-busqueda">
        <div class="busqueda-encabezado">
          <svg xmlns="http://www.w3.org/2000/svg" class="busqueda-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span>BUSCAR ALUMNO</span>
        </div>

        <div class="busqueda-grid">
          <div class="campo-grupo">
            <label>NÚMERO DE CONTROL O NOMBRE</label>
            <div class="input-con-icono">
              <svg xmlns="http://www.w3.org/2000/svg" class="input-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input
                type="text"
                v-model="busquedaAlumno"
                placeholder="EJ: 21456987 O SARA PÉREZ"
                @input="normalizarBusqueda"
                @keyup.enter="buscarAlumno"
              />
            </div>
          </div>

          <div class="campo-grupo campo-periodo">
            <label>PERIODO</label>
            <select v-model="periodo" class="select-periodo" :disabled="cargandoPeriodos">
              <option :value="null" disabled>
                {{ cargandoPeriodos ? 'CARGANDO PERIODOS...' : periodos.length === 0 ? 'SIN PERIODOS DISPONIBLES' : 'SELECCIONAR PERIODO' }}
              </option>
              <option v-for="p in periodos" :key="p.id" :value="p.id">
                {{ p.nombre?.toUpperCase() }}
              </option>
            </select>
          </div>

          <button class="btn-buscar" @click="buscarAlumno" :disabled="cargandoBusqueda || !busquedaAlumno.trim()">
            <span v-if="cargandoBusqueda" class="spinner-btn"></span>
            {{ cargandoBusqueda ? 'BUSCANDO...' : 'BUSCAR' }}
          </button>
        </div>

        <!-- RESULTADOS -->
        <div v-if="resultadosBusqueda.length > 0 && !alumnoSeleccionado" class="resultados-lista">
          <div
            v-for="alumno in resultadosBusqueda"
            :key="alumno.id_alumno || alumno.noControl"
            class="resultado-item"
            @click="seleccionarAlumno(alumno)"
          >
            <div class="resultado-avatar">{{ inicial(alumno.nombre) }}</div>
            <div class="resultado-info">
              <strong>{{ alumno.nombre?.toUpperCase() }}</strong>
              <span>{{ alumno.noControl }} · {{ alumno.carrera?.toUpperCase() }} · {{ alumno.semestre }}° SEMESTRE</span>
            </div>
            <button class="btn-seleccionar">SELECCIONAR</button>
          </div>
        </div>
      </div>

      <!-- ALUMNO SELECCIONADO -->
      <transition name="fade">
        <div v-if="alumnoSeleccionado" class="alumno-seleccionado">
          <div class="alumno-izquierda">
            <div class="alumno-avatar">{{ inicial(alumnoSeleccionado.nombre) }}</div>
            <div>
              <p class="alumno-nombre">{{ alumnoSeleccionado.nombre?.toUpperCase() }}</p>
              <p class="alumno-detalle">
                {{ alumnoSeleccionado.noControl }} · {{ alumnoSeleccionado.carrera?.toUpperCase() }} · {{ alumnoSeleccionado.semestre }}° SEMESTRE
              </p>
            </div>
          </div>

          <button class="btn-cambiar" @click="limpiarAlumno">
            CAMBIAR ALUMNO
          </button>
        </div>
      </transition>

      <!-- RESUMEN -->
      <div v-if="alumnoSeleccionado" class="resumen-grid">
        <div class="resumen-card">
          <span class="resumen-numero">{{ cargaAcademica.length }}</span>
          <span class="resumen-label">MATERIAS</span>
        </div>

        <div class="resumen-card">
          <span class="resumen-numero">{{ totalCreditos }}</span>
          <span class="resumen-label">CRÉDITOS</span>
        </div>

        <div class="resumen-card">
          <span class="resumen-numero">{{ totalHoras }}</span>
          <span class="resumen-label">HRS / SEMANA</span>
        </div>

        <div class="resumen-card">
          <span class="resumen-numero resumen-periodo">{{ nombrePeriodoSeleccionado }}</span>
          <span class="resumen-label">PERIODO</span>
        </div>
      </div>

      <!-- TABLA -->
      <div v-if="alumnoSeleccionado" class="table-container">
        <div v-if="cargandoCarga" class="loading-overlay">
          <div class="loading-spinner"></div>
          <span>CARGANDO CARGA ACADÉMICA...</span>
        </div>

        <table v-else-if="cargaAcademica.length > 0" class="tabla-carga">
          <thead>
            <tr>
              <th>CLAVE DEL GRUPO</th>
              <th>MATERIA</th>
              <th>DOCENTE</th>
              <th>AULA</th>
              <th>HORARIO</th>
              <th class="text-center">CRÉDITOS</th>
              <th class="text-center">ESTATUS</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="materia in cargaAcademica" :key="materia.id">
              <td class="celda-clave">{{ materia.claveGrupo }}</td>
              <td class="celda-materia">{{ materia.materia }}</td>
              <td>{{ materia.docente }}</td>
              <td>{{ materia.aula }}</td>
              <td>
                <span v-if="materia.dia || materia.horaInicio || materia.horaFin" class="horario-badge">
                  {{ materia.dia }} {{ materia.horaInicio }}–{{ materia.horaFin }}
                </span>
                <span v-else class="sin-dato">—</span>
              </td>
              <td class="text-center">
                <span class="creditos-badge">{{ materia.creditos }}</span>
              </td>
              <td class="text-center">
                <span class="estatus-badge">{{ materia.estatus }}</span>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <h3>SIN CARGA ACADÉMICA</h3>
          <p>EL ALUMNO NO TIENE MATERIAS INSCRITAS EN EL PERIODO SELECCIONADO.</p>
        </div>
      </div>

      <!-- ESTADO INICIAL -->
      <div v-else class="estado-inicial">
        <svg xmlns="http://www.w3.org/2000/svg" class="icono-inicial" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        <h3>SELECCIONE UN ALUMNO</h3>
        <p>USE EL BUSCADOR PARA CONSULTAR SU CARGA ACADÉMICA.</p>
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

const busquedaAlumno = ref('')
const resultadosBusqueda = ref([])
const alumnoSeleccionado = ref(null)

const periodos = ref([])
const periodo = ref(null)

const cargaAcademica = ref([])
const cargandoBusqueda = ref(false)
const cargandoCarga = ref(false)
const cargandoPeriodos = ref(false)

const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje: mensaje.toUpperCase(), tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

const inicial = (nombre) => {
  if (!nombre) return '?'
  return nombre.charAt(0).toUpperCase()
}

const normalizarBusqueda = () => {
  busquedaAlumno.value = busquedaAlumno.value.toUpperCase()
  resultadosBusqueda.value = []
  alumnoSeleccionado.value = null
  cargaAcademica.value = []
}

const normalizarAlumno = (a) => ({
  id_alumno: a.id_alumno ?? a.id,
  noControl: a.noControl ?? a.numero_control ?? a.no_control ?? '',
  nombre: a.nombre ?? '',
  carrera: a.carrera ?? '',
  semestre: a.semestre ?? a.semestre_actual ?? 1
})

const normalizarCarga = (g) => ({
  id: g.id_inscripcion ?? g.id_asignacion ?? g.id_grupo ?? g.id,
  claveGrupo: (g.clave_grupo ?? g.claveGrupo ?? g.grupo ?? '—').toString().toUpperCase(),
  materia: (g.materia ?? g.nombre_materia ?? 'SIN MATERIA').toString().toUpperCase(),
  docente: (g.docente ?? g.nombre_docente ?? 'SIN DOCENTE').toString().toUpperCase(),
  aula: (g.aula ?? g.nombre_aula ?? '—').toString().toUpperCase(),
  dia: (g.dia ?? g.horario?.dia ?? '').toString().toUpperCase(),
  horaInicio: g.hora_inicio ?? g.horaInicio ?? g.horario?.horaInicio ?? '',
  horaFin: g.hora_fin ?? g.horaFin ?? g.horario?.horaFin ?? '',
  creditos: Number(g.creditos ?? g.materia_creditos ?? 0),
  estatus: (g.estatus ?? g.estado ?? 'INSCRITO').toString().toUpperCase()
})

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

    if (!periodo.value && periodos.value.length > 0) {
      const activo = periodos.value.find(p =>
        p.nombre?.toLowerCase().includes('actual') ||
        p.nombre?.toLowerCase().includes('activo')
      )

      periodo.value = activo?.id ?? periodos.value[0].id
    }
  } catch (error) {
    console.error('ERROR CARGANDO PERIODOS:', error)
    periodos.value = []
  } finally {
    cargandoPeriodos.value = false
  }
}

const buscarAlumno = async () => {
  const termino = busquedaAlumno.value.trim()

  if (!termino) {
    mostrarNotificacion('INGRESE UN NÚMERO DE CONTROL O NOMBRE.', 'error')
    return
  }

  cargandoBusqueda.value = true
  resultadosBusqueda.value = []
  alumnoSeleccionado.value = null
  cargaAcademica.value = []

  try {
    const response = await fetch(`${API}/inscripcion/buscar-alumno?q=${encodeURIComponent(termino)}`, {
      headers: { 'Accept': 'application/json' }
    })

    const data = await response.json()

    if (!response.ok) throw new Error(data.error || 'ERROR DEL SERVIDOR')

    const lista = Array.isArray(data) ? data : [data]
    resultadosBusqueda.value = lista.map(normalizarAlumno)

    if (resultadosBusqueda.value.length === 0) {
      mostrarNotificacion('NO SE ENCONTRÓ NINGÚN ALUMNO.', 'error')
    }
  } catch (error) {
    console.error('ERROR BUSCANDO ALUMNO:', error)
    mostrarNotificacion(error.message || 'NO SE PUDO BUSCAR EL ALUMNO.', 'error')
  } finally {
    cargandoBusqueda.value = false
  }
}

const obtenerRutasCargaAlumno = (alumno) => {
  const idAlumno = alumno?.id_alumno
  const noControl = alumno?.noControl
  const queryPeriodo = periodo.value ? `?id_periodo=${encodeURIComponent(periodo.value)}` : ''

  return [
    `${API}/carga-academica/alumno/${idAlumno}${queryPeriodo}`,
    `${API}/inscripcion/carga-alumno/${idAlumno}${queryPeriodo}`,
    `${API}/alumnos/${idAlumno}/carga-academica${queryPeriodo}`,
    `${API}/carga-academica/alumno/control/${encodeURIComponent(noControl)}${queryPeriodo}`
  ].filter(ruta => !ruta.includes('/undefined') && !ruta.includes('/null'))
}

const cargarCargaAcademica = async () => {
  if (!alumnoSeleccionado.value) return

  cargandoCarga.value = true
  cargaAcademica.value = []

  try {
    const rutas = obtenerRutasCargaAlumno(alumnoSeleccionado.value)
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
          : data.carga_academica ?? data.cargaAcademica ?? data.grupos ?? data.inscripciones ?? []

        cargaAcademica.value = lista.map(normalizarCarga)
        return
      } catch (error) {
        ultimoError = error
      }
    }

    throw ultimoError || new Error('NO SE ENCONTRÓ ENDPOINT DE CARGA ACADÉMICA')
  } catch (error) {
    console.error('ERROR CARGANDO CARGA ACADÉMICA:', error)
    mostrarNotificacion('NO SE PUDO CARGAR LA CARGA ACADÉMICA DEL ALUMNO.', 'error')
  } finally {
    cargandoCarga.value = false
  }
}

const seleccionarAlumno = async (alumno) => {
  alumnoSeleccionado.value = alumno
  resultadosBusqueda.value = []
  busquedaAlumno.value = `${alumno.noControl} - ${alumno.nombre}`.toUpperCase()
  await cargarCargaAcademica()
}

const limpiarAlumno = () => {
  busquedaAlumno.value = ''
  resultadosBusqueda.value = []
  alumnoSeleccionado.value = null
  cargaAcademica.value = []
}

const calcularHoras = (inicio, fin) => {
  if (!inicio || !fin) return 1

  try {
    const [hI, mI] = inicio.split(':').map(Number)
    const [hF, mF] = fin.split(':').map(Number)
    const minutos = (hF * 60 + mF) - (hI * 60 + mI)
    return minutos > 0 ? Math.max(1, Math.round(minutos / 60)) : 1
  } catch {
    return 1
  }
}

const totalHoras = computed(() => {
  return cargaAcademica.value.reduce((total, materia) => {
    return total + calcularHoras(materia.horaInicio, materia.horaFin)
  }, 0)
})

const totalCreditos = computed(() => {
  return cargaAcademica.value.reduce((total, materia) => total + Number(materia.creditos || 0), 0)
})

const nombrePeriodoSeleccionado = computed(() => {
  return periodos.value.find(p => p.id === periodo.value)?.nombre?.toUpperCase() ?? '—'
})

const exportarCarga = () => {
  if (!alumnoSeleccionado.value || cargaAcademica.value.length === 0) return

  const hoy = new Date().toISOString().split('T')[0]
  const encabezados = ['CLAVE GRUPO', 'MATERIA', 'DOCENTE', 'AULA', 'DÍA', 'HORA INICIO', 'HORA FIN', 'CRÉDITOS', 'ESTATUS']
  const filas = cargaAcademica.value.map(m => [
    m.claveGrupo,
    m.materia,
    m.docente,
    m.aula,
    m.dia,
    m.horaInicio,
    m.horaFin,
    m.creditos,
    m.estatus
  ])

  const contenido = [encabezados, ...filas]
    .map(fila => fila.map(valor => `"${String(valor ?? '').replace(/"/g, '""')}"`).join(','))
    .join('\n')

  const nombreAlumno = (alumnoSeleccionado.value.nombre || 'alumno').replace(/\s+/g, '_').toLowerCase()
  const blob = new Blob(['\uFEFF' + contenido], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')

  a.href = url
  a.download = `carga_academica_${nombreAlumno}_${hoy}.csv`
  a.click()

  URL.revokeObjectURL(url)
}

watch(periodo, () => {
  if (alumnoSeleccionado.value) cargarCargaAcademica()
})

onMounted(() => {
  cargarPeriodos()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap');

/* ============================================= */
/* BASE GENERAL - CARGAS ACADÉMICAS              */
/* ============================================= */
.carga-alumno-page { width: 100%; min-width: 0; background: #F4F6F9; font-family: 'Montserrat', sans-serif; font-weight: 400; color: #333333; text-transform: uppercase; }
.carga-alumno-page * { font-family: 'Montserrat', sans-serif; box-sizing: border-box; }

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
/* BOTÓN EXPORTAR                                */
/* ============================================= */
.btn-exportar { display: flex; align-items: center; justify-content: center; gap: 7px; background: rgba(47,128,237,0.10); color: #1D52B7; border: 1px solid rgba(47,128,237,0.25); padding: 9px 18px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; line-height: 1.2; letter-spacing: 0.01em; cursor: pointer; transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease; white-space: nowrap; text-transform: uppercase; }
.btn-exportar:hover { background: rgba(47,128,237,0.15); border-color: #2F80ED; }
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
/* TARJETA DE BÚSQUEDA                           */
/* ============================================= */
.tarjeta-busqueda { background: #FFFFFF; border-radius: 12px; padding: 1.3rem 1.6rem; margin-bottom: 1.2rem; box-shadow: 0 4px 14px rgba(29,82,183,0.05); border: 1px solid #E0E0E0; position: relative; }
.busqueda-encabezado { display: flex; align-items: center; gap: 8px; margin-bottom: 1rem; color: #0B2545; font-size: 1rem; font-weight: 600; line-height: 1.3; }
.busqueda-icono { width: 20px; height: 20px; stroke: #1D52B7; }
.busqueda-grid { display: grid; grid-template-columns: minmax(260px, 1fr) 220px auto; gap: 1rem; align-items: end; }

/* ============================================= */
/* FORMULARIOS                                   */
/* ============================================= */
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
/* BOTONES                                       */
/* ============================================= */
.btn-buscar, .btn-cambiar, .btn-seleccionar { font-family: 'Montserrat', sans-serif; font-size: 0.85rem; font-weight: 600; line-height: 1.2; letter-spacing: 0.01em; text-transform: uppercase; cursor: pointer; transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease; }
.btn-buscar { height: 43px; display: flex; align-items: center; justify-content: center; gap: 7px; background: #1D52B7; color: #FFFFFF; border: none; padding: 0 24px; border-radius: 9px; white-space: nowrap; }
.btn-buscar:hover:not(:disabled) { background: #2F80ED; }
.btn-buscar:disabled { opacity: 0.55; cursor: not-allowed; }
.btn-seleccionar { background: rgba(47,128,237,0.10); color: #1D52B7; border: 1px solid rgba(47,128,237,0.25); padding: 7px 16px; border-radius: 8px; }
.btn-seleccionar:hover { background: rgba(47,128,237,0.15); border-color: #2F80ED; }
.btn-cambiar { background: #FFFFFF; color: #4F4F4F; border: 1px solid #E0E0E0; padding: 8px 15px; border-radius: 8px; white-space: nowrap; }
.btn-cambiar:hover { background: #F2F4F7; }

/* ============================================= */
/* SPINNER                                       */
/* ============================================= */
.spinner-btn { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.4); border-top-color: #FFFFFF; border-radius: 50%; animation: girar 0.7s linear infinite; }
@keyframes girar { to { transform: rotate(360deg); } }

/* ============================================= */
/* RESULTADOS DE BÚSQUEDA                        */
/* ============================================= */
.resultados-lista { margin-top: 1rem; display: flex; flex-direction: column; gap: 8px; }
.resultado-item { display: flex; align-items: center; gap: 14px; border: 1px solid #E0E0E0; border-radius: 10px; padding: 12px 16px; cursor: pointer; transition: border-color 0.2s ease, background 0.2s ease; }
.resultado-item:hover { border-color: #1D52B7; background: rgba(29,82,183,0.05); }
.resultado-avatar, .alumno-avatar { width: 42px; height: 42px; border-radius: 50%; background: #1D52B7; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: 700; flex-shrink: 0; }
.resultado-info { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.resultado-info strong { color: #333333; font-size: 0.95rem; font-weight: 600; line-height: 1.3; }
.resultado-info span { color: #4F4F4F; font-size: 0.85rem; font-weight: 400; line-height: 1.45; margin-top: 2px; }

/* ============================================= */
/* ALUMNO SELECCIONADO                           */
/* ============================================= */
.alumno-seleccionado { display: flex; align-items: center; justify-content: space-between; gap: 1rem; background: rgba(39,174,96,0.10); border: 1.5px solid rgba(39,174,96,0.35); border-radius: 12px; padding: 14px 18px; margin-bottom: 1.2rem; }
.alumno-izquierda { display: flex; align-items: center; gap: 14px; min-width: 0; }
.alumno-seleccionado .alumno-avatar { background: #27AE60; }
.alumno-nombre { margin: 0; color: #333333; font-size: 1rem; font-weight: 600; line-height: 1.3; }
.alumno-detalle { margin: 3px 0 0; color: #4F4F4F; font-size: 0.85rem; font-weight: 400; line-height: 1.45; }

/* ============================================= */
/* RESUMEN KPIS                                  */
/* ============================================= */
.resumen-grid { display: grid; grid-template-columns: repeat(4, minmax(130px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
.resumen-card { background: #FFFFFF; border: 1px solid #E0E0E0; border-radius: 12px; padding: 1rem; text-align: center; box-shadow: 0 4px 14px rgba(29,82,183,0.05); }
.resumen-numero { display: block; color: #333333; font-size: 2rem; font-weight: 700; line-height: 1.1; }
.resumen-periodo { font-size: 0.95rem; min-height: 35px; display: flex; align-items: center; justify-content: center; }
.resumen-label { display: block; color: #828282; font-size: 0.72rem; font-weight: 600; letter-spacing: 0.05em; margin-top: 4px; }

/* ============================================= */
/* TABLA                                         */
/* ============================================= */
.table-container { position: relative; background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 14px rgba(29,82,183,0.05); border: 1px solid #E0E0E0; }
.loading-overlay { position: absolute; inset: 0; min-height: 220px; background: rgba(255,255,255,0.88); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; z-index: 10; color: #1D52B7; font-size: 0.875rem; font-weight: 600; }
.loading-spinner { width: 32px; height: 32px; border: 3px solid #E0E0E0; border-top-color: #1D52B7; border-radius: 50%; animation: girar 0.7s linear infinite; }
.tabla-carga { width: 100%; border-collapse: collapse; }
.tabla-carga th { background: #F2F4F7; padding: 13px 14px; text-align: left; color: #333333; font-size: 0.78rem; font-weight: 600; line-height: 1.3; letter-spacing: 0.02em; border-bottom: 1px solid #E0E0E0; white-space: nowrap; }
.tabla-carga td { padding: 13px 14px; border-bottom: 1px solid #E0E0E0; color: #333333; font-size: 0.85rem; font-weight: 400; line-height: 1.4; vertical-align: middle; }
.tabla-carga tbody tr:hover { background: rgba(29,82,183,0.05); }
.tabla-carga tbody tr:last-child td { border-bottom: none; }
.celda-clave { color: #1D52B7; font-weight: 700; white-space: nowrap; }
.celda-materia { font-weight: 600; min-width: 170px; }
.horario-badge { display: inline-block; background: rgba(47,128,237,0.10); color: #1D52B7; border: 1px solid rgba(47,128,237,0.25); border-radius: 6px; padding: 3px 9px; font-size: 0.8rem; font-weight: 500; white-space: nowrap; }
.sin-dato { color: #828282; font-size: 0.85rem; }
.creditos-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 34px; background: rgba(47,128,237,0.10); color: #1D52B7; border: 1px solid rgba(47,128,237,0.25); border-radius: 999px; padding: 4px 10px; font-weight: 700; }
.estatus-badge { display: inline-flex; align-items: center; justify-content: center; background: rgba(39,174,96,0.10); color: #27AE60; border: 1px solid rgba(39,174,96,0.35); border-radius: 999px; padding: 4px 10px; font-weight: 700; font-size: 0.76rem; }
.text-center { text-align: center; }

/* ============================================= */
/* ESTADOS VACÍOS                                */
/* ============================================= */
.estado-vacio, .estado-inicial { text-align: center; padding: 3.5rem 2rem; color: #4F4F4F; background: #FFFFFF; border-radius: 12px; border: 1px dashed #E0E0E0; }
.icono-vacio, .icono-inicial { width: 60px; height: 60px; stroke: #BDBDBD; margin-bottom: 14px; }
.estado-vacio h3, .estado-inicial h3 { color: #333333; font-size: 1rem; font-weight: 600; margin: 0 0 6px; }
.estado-vacio p, .estado-inicial p { color: #4F4F4F; font-size: 0.875rem; font-weight: 400; margin: 0; }

/* ============================================= */
/* FOOTER                                        */
/* ============================================= */
.footer-institucional { text-align: center; color: #828282; font-size: 0.82rem; font-weight: 400; padding-top: 2rem; margin-top: 1rem; }

/* ============================================= */
/* RESPONSIVE                                    */
/* ============================================= */
@media (max-width: 800px) {
  .page-header { flex-direction: column; }
  .busqueda-grid { grid-template-columns: 1fr; }
  .resumen-grid { grid-template-columns: 1fr 1fr; }
  .alumno-seleccionado { flex-direction: column; align-items: stretch; }
  .btn-cambiar, .btn-exportar { width: 100%; justify-content: center; }
}
</style>