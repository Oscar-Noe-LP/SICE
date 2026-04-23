<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="carga-docente-page">

      <!-- ══ Breadcrumb ══ -->
      <nav class="breadcrumb">
        <span class="breadcrumb-link" @click="$router.push('/asignacion-docente')">Asignación Docente</span>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-actual">Carga Académica</span>
      </nav>

      <!-- ══ Encabezado ══ -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Carga Académica por Docente</h1>
          <span class="page-subtitle">Consulta y gestión de grupos asignados a un docente</span>
        </div>
        <button
          v-if="docenteSeleccionado && gruposAsignados.length > 0"
          class="btn-exportar"
          @click="exportarCarga"
          title="Exportar carga académica"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="exportar-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Exportar carga
        </button>
      </div>

      <!-- ══ Barra de carga global ══ -->
      <div class="barra-carga-global" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- ══ Notificación UI ══ -->
      <transition name="notif-fade">
        <div v-if="notificacion.visible" class="notificacion-ui" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo === 'exito'" xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- ══ Tarjeta de búsqueda de docente ══ -->
      <div class="tarjeta-busqueda">
        <div class="busqueda-encabezado">
          <svg xmlns="http://www.w3.org/2000/svg" class="busqueda-icono-titulo" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span class="busqueda-titulo">Seleccionar Docente</span>
        </div>

        <div class="busqueda-fila">
          <div class="search-group busqueda-docente-input">
            <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              type="text"
              placeholder="Buscar docente por nombre o número de empleado..."
              v-model="busqueda"
              class="search-input"
              @input="onBusqueda"
              @keydown.escape="limpiarBusqueda"
              @keydown.enter="buscarDocentes"
            >
            <span v-if="cargandoBusqueda" class="spinner-busqueda"></span>
          </div>
          <button class="btn-buscar" @click="buscarDocentes" :disabled="cargandoBusqueda || !busqueda.trim()">
            <span v-if="cargandoBusqueda" class="spinner-btn-peq"></span>
            <span v-else>Buscar</span>
          </button>
        </div>

        <!-- Sugerencias desplegables -->
        <div v-if="sugerencias.length > 0" class="sugerencias-lista">
          <div
            v-for="(doc, i) in sugerencias"
            :key="doc.id_docente || i"
            class="sugerencia-item"
            @click="seleccionarDocente(doc)"
          >
            <div class="sugerencia-avatar">{{ inicial(doc.nombre) }}</div>
            <div class="sugerencia-info">
              <span class="sugerencia-nombre">{{ doc.nombre }}</span>
              <span class="sugerencia-detalle">
                Núm. empleado: {{ doc.numero_empleado }} · {{ doc.especialidad }}
              </span>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="sugerencia-flecha" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>
        </div>
      </div>

      <!-- ══ Bloque de confirmación del docente seleccionado ══ -->
      <transition name="confirmacion-fade">
        <div v-if="docenteSeleccionado" class="bloque-docente-seleccionado">
          <div class="docente-sel-izquierda">
            <div class="docente-sel-avatar">{{ inicial(docenteSeleccionado.nombre) }}</div>
            <div class="docente-sel-datos">
              <p class="docente-sel-nombre">{{ docenteSeleccionado.nombre }}</p>
              <p class="docente-sel-sub">
                {{ docenteSeleccionado.especialidad }}
                <span class="docente-sel-sep">·</span>
                {{ docenteSeleccionado.grado_academico }}
                <span class="docente-sel-sep">·</span>
                Núm. empleado: {{ docenteSeleccionado.numero_empleado }}
              </p>
            </div>
          </div>
          <button class="btn-cambiar-docente" @click="limpiarBusqueda" title="Buscar otro docente">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Cambiar docente
          </button>
        </div>
      </transition>

      <!-- ══ Tabla de grupos asignados ══ -->
      <template v-if="docenteSeleccionado">

        <div class="tabla-encabezado">
          <h2 class="tabla-titulo">Grupos Asignados</h2>
          <span class="tabla-contador">{{ gruposAsignados.length }} grupo(s)</span>
        </div>

        <div class="table-container">

          <!-- Skeleton mientras carga grupos -->
          <template v-if="cargandoGrupos">
            <div class="skeleton-tabla">
              <div v-for="i in 4" :key="i" class="skeleton-fila">
                <div class="skeleton-celda skeleton-celda-corta"></div>
                <div class="skeleton-celda skeleton-celda-larga"></div>
                <div class="skeleton-celda skeleton-celda-media"></div>
                <div class="skeleton-celda skeleton-celda-corta"></div>
                <div class="skeleton-celda skeleton-celda-media"></div>
                <div class="skeleton-celda skeleton-celda-corta"></div>
                <div class="skeleton-celda skeleton-celda-acciones"></div>
              </div>
            </div>
          </template>

          <table v-else-if="gruposAsignados.length > 0" class="alumnos-table">
            <thead>
              <tr>
                <th>Clave del Grupo</th>
                <th>Materia</th>
                <th>Carrera</th>
                <th>Semestre</th>
                <th>Horario</th>
                <th>Alumnos Inscritos</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(grupo, index) in gruposAsignados"
                :key="grupo.id_asignacion || grupo.id_grupo"
                :class="{ 'fila-seleccionada': filaActiva === index }"
                @click="filaActiva = index"
              >
                <td class="celda-clave">{{ grupo.clave_grupo }}</td>
                <td class="celda-materia">{{ grupo.materia }}</td>
                <td class="celda-carrera">{{ grupo.carrera }}</td>
                <td class="celda-semestre">{{ grupo.semestre }}°</td>
                <td class="celda-horario">
                  <div class="horario-linea">{{ grupo.dia }}</div>
                  <div class="horario-hora">{{ grupo.hora_inicio }} - {{ grupo.hora_fin }}</div>
                </td>
                <td class="celda-alumnos">
                  <div class="alumnos-wrap">
                    <span class="alumnos-numero">{{ grupo.inscritos }}</span>
                    <span class="alumnos-total">/ {{ grupo.capacidad }}</span>
                  </div>
                </td>
                <td class="celda-acciones">
                  <button
                    class="btn-accion desasignar"
                    @click.stop="abrirModalDesasignar(grupo)"
                    title="Desasignar docente de este grupo"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6" />
                    </svg>
                    Desasignar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Estado vacío: docente sin grupos -->
          <div v-else class="estado-vacio">
            <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <h3>Sin grupos asignados</h3>
            <p>Este docente no tiene grupos asignados en el periodo activo.</p>
          </div>
        </div>

        <!-- ══ Indicador de carga total ══ -->
        <div v-if="gruposAsignados.length > 0" class="indicador-carga">
          <div class="carga-total-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" class="carga-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="carga-texto">
              Total de horas asignadas:
              <strong class="carga-valor">{{ totalHoras }} hrs / semana</strong>
            </span>
          </div>
          <div class="carga-grupos-total">
            <span>{{ gruposAsignados.length }} grupo(s) en el periodo activo</span>
          </div>
        </div>

      </template>

      <!-- ══ Estado inicial: sin docente seleccionado ══ -->
      <div v-else class="estado-inicial">
        <svg xmlns="http://www.w3.org/2000/svg" class="icono-inicial" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        <h3>Selecciona un docente</h3>
        <p>Usa el buscador para encontrar un docente y consultar sus grupos asignados.</p>
      </div>

      <!-- ══ Footer institucional ══ -->
      <div class="footer-institucional">
        <svg xmlns="http://www.w3.org/2000/svg" class="footer-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>
          La carga académica máxima permitida por periodo es de <strong>20 horas semanales</strong>.
          Cambios en asignaciones quedan registrados en bitácora.
        </span>
      </div>

    </div>

    <!-- ══════════════════════════════════════
         MODAL: CONFIRMACIÓN DE DESASIGNACIÓN
    ═══════════════════════════════════════ -->
    <div v-if="showModalDesasignar" class="modal-overlay" @click.self="cerrarModalDesasignar">
      <div class="modal-content modal-confirmacion">
        <div class="modal-header modal-header-rojo">
          <h3>Confirmar Desasignación</h3>
          <button @click="cerrarModalDesasignar" class="btn-cerrar-modal">×</button>
        </div>

        <div class="modal-body">

          <!-- Ícono de advertencia -->
          <div class="confirmacion-icono-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" class="confirmacion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>

          <p class="confirmacion-mensaje">
            ¿Confirmas que deseas desasignar a
            <strong>{{ docenteSeleccionado?.nombre }}</strong>
            del grupo <strong>{{ grupoDesasignar?.clave_grupo }}</strong>?
          </p>
          <p class="confirmacion-submensaje">
            Esta acción dejará el grupo <strong>sin docente asignado</strong>.
            Podrás asignarlo nuevamente desde el módulo de Asignación Docente.
          </p>

          <!-- Resumen del grupo -->
          <div class="confirmacion-resumen">
            <div class="resumen-item">
              <span class="resumen-label">Materia</span>
              <span class="resumen-valor">{{ grupoDesasignar?.materia }}</span>
            </div>
            <div class="resumen-item">
              <span class="resumen-label">Horario</span>
              <span class="resumen-valor">{{ grupoDesasignar?.dia }} {{ grupoDesasignar?.hora_inicio }} - {{ grupoDesasignar?.hora_fin }}</span>
            </div>
            <div class="resumen-item">
              <span class="resumen-label">Alumnos inscritos</span>
              <span class="resumen-valor">{{ grupoDesasignar?.inscritos }} alumnos</span>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModalDesasignar" :disabled="desasignando">Cancelar</button>
          <button class="btn-desasignar-confirmar" @click="confirmarDesasignacion" :disabled="desasignando">
            <span v-if="desasignando" class="spinner-btn"></span>
            {{ desasignando ? 'Desasignando...' : 'Sí, desasignar' }}
          </button>
        </div>
      </div>
    </div>

  </MainLayout>
</template>


<script setup>
import { ref, computed, watch } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

// ── URL base del backend (variable de entorno) ──────────────────────
const API_URL = import.meta.env.VITE_API_URL

// ── Estado principal ────────────────────────────────────────────────
const docentes         = ref([])
const gruposAsignados  = ref([])
const cargando         = ref(false)
const cargandoBusqueda = ref(false)
const cargandoGrupos   = ref(false)
const desasignando     = ref(false)
const filaActiva       = ref(-1)

// ── Búsqueda de docente ─────────────────────────────────────────────
const busqueda          = ref('')
const sugerencias       = ref([])
const docenteSeleccionado = ref(null)

// ── Modal desasignación ─────────────────────────────────────────────
const showModalDesasignar = ref(false)
const grupoDesasignar     = ref(null)

// ── Notificación UI ─────────────────────────────────────────────────
const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

// ── Total de horas calculado desde los grupos ───────────────────────
const totalHoras = computed(() => {
  if (gruposAsignados.value.length === 0) return 0
  return gruposAsignados.value.reduce((total, g) => {
    const horasPorGrupo = calcularHorasGrupo(g.hora_inicio, g.hora_fin)
    return total + horasPorGrupo
  }, 0)
})

const calcularHorasGrupo = (inicio, fin) => {
  if (!inicio || !fin) return 1
  try {
    const [hI, mI] = inicio.split(':').map(Number)
    const [hF, mF] = fin.split(':').map(Number)
    return Math.round(((hF * 60 + mF) - (hI * 60 + mI)) / 60)
  } catch { return 1 }
}

// ── Carga de lista de docentes (para búsqueda) ──────────────────────
// Endpoint: GET /api/docentes
const cargarDocentes = async (query = '') => {
  cargandoBusqueda.value = true
  try {
    const url = query
      ? `${API_URL}/api/docentes?q=${encodeURIComponent(query)}`
      : `${API_URL}/api/docentes`
    const response = await fetch(url)
    if (!response.ok) throw new Error('Error del servidor')
    const data = await response.json()
    docentes.value    = data
    sugerencias.value = data
    console.log('✅ Docentes cargados:', data.length, 'resultados')
  } catch (error) {
    console.error('❌ Error cargando docentes:', error)
    mostrarNotificacion('No se pudo buscar docentes. Verifica que el servidor esté activo.', 'error')
  } finally {
    cargandoBusqueda.value = false
  }
}

// ── Carga de grupos por docente ──────────────────────────────────────
// Endpoint: GET /api/carga-docente/{id_docente}
const cargarGruposPorDocente = async (idDocente) => {
  cargandoGrupos.value = true
  gruposAsignados.value = []
  try {
    const response = await fetch(`${API_URL}/api/carga-docente/${idDocente}`)
    if (!response.ok) throw new Error('Error del servidor')
    const data = await response.json()
    gruposAsignados.value = data
    console.log('✅ Grupos del docente cargados:', data.length, 'registros')
  } catch (error) {
    console.error('❌ Error cargando grupos:', error)
    mostrarNotificacion('No se pudo cargar la carga académica del docente.', 'error')
  } finally {
    cargandoGrupos.value = false
  }
}

// ── Desasignar docente de un grupo ───────────────────────────────────
// Endpoint: DELETE /api/asignacion-docente/{id_asignacion}
const confirmarDesasignacion = async () => {
  if (!grupoDesasignar.value) return

  const id = grupoDesasignar.value.id_asignacion || grupoDesasignar.value.id_grupo
  desasignando.value = true

  try {
    console.log('🔵 Desasignando grupo:', grupoDesasignar.value.clave_grupo)
    const response = await fetch(`${API_URL}/api/asignacion-docente/${id}`, {
      method:  'DELETE',
      headers: { 'Accept': 'application/json' }
    })
    const data = await response.json()
    console.log('🟢 Respuesta backend:', data)

    if (response.ok) {
      await cargarGruposPorDocente(docenteSeleccionado.value.id_docente)
      cerrarModalDesasignar()
      mostrarNotificacion(
        `Docente desasignado del grupo ${grupoDesasignar.value.clave_grupo} correctamente.`,
        'exito'
      )
    } else {
      throw new Error(JSON.stringify(data))
    }
  } catch (error) {
    console.error('❌ ERROR:', error)
    mostrarNotificacion('Ocurrió un error al desasignar el docente.', 'error')
  } finally {
    desasignando.value = false
  }
}

// ── Handlers de búsqueda ─────────────────────────────────────────────
let timerBusqueda = null
const onBusqueda = () => {
  sugerencias.value = []
  if (!busqueda.value.trim()) return
  if (timerBusqueda) clearTimeout(timerBusqueda)
  timerBusqueda = setTimeout(() => cargarDocentes(busqueda.value), 400)
}

const buscarDocentes = () => {
  if (!busqueda.value.trim()) return
  cargarDocentes(busqueda.value)
}

const seleccionarDocente = (docente) => {
  docenteSeleccionado.value = docente
  busqueda.value = docente.nombre
  sugerencias.value = []
  cargarGruposPorDocente(docente.id_docente)
}

const limpiarBusqueda = () => {
  busqueda.value = ''
  sugerencias.value = []
  docenteSeleccionado.value = null
  gruposAsignados.value = []
  filaActiva.value = -1
}

// ── Modal desasignación ──────────────────────────────────────────────
const abrirModalDesasignar = (grupo) => {
  grupoDesasignar.value = { ...grupo }
  showModalDesasignar.value = true
}
const cerrarModalDesasignar = () => { showModalDesasignar.value = false }

// ── Exportar a CSV ────────────────────────────────────────────────────
const exportarCarga = () => {
  if (!docenteSeleccionado.value || gruposAsignados.value.length === 0) return
  const hoy = new Date().toISOString().split('T')[0]
  const cabeceras = ['Clave Grupo', 'Materia', 'Carrera', 'Semestre', 'Día', 'Hora Inicio', 'Hora Fin', 'Alumnos Inscritos', 'Capacidad']
  const filas = gruposAsignados.value.map(g => [
    g.clave_grupo, g.materia, g.carrera, g.semestre,
    g.dia, g.hora_inicio, g.hora_fin, g.inscritos, g.capacidad
  ])
  const contenido = [cabeceras, ...filas].map(f => f.join(',')).join('\n')
  const nombreDocente = (docenteSeleccionado.value.nombre || 'docente').replace(/\s+/g, '_').toLowerCase()
  const blob = new Blob(['\uFEFF' + contenido], { type: 'text/csv;charset=utf-8;' })
  const url  = URL.createObjectURL(blob)
  const a    = document.createElement('a')
  a.href     = url
  a.download = `carga_${nombreDocente}_${hoy}.csv`
  a.click()
  URL.revokeObjectURL(url)
}

// ── Helpers ──────────────────────────────────────────────────────────
const inicial = (nombre) => {
  if (!nombre) return '?'
  return nombre.charAt(0).toUpperCase()
}
</script>


<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* ══ Variables locales ══ */
.carga-docente-page {
  --azul:       #1B396A;
  --azul-hover: #1D4ED8;
  --azul-suave: #DBEAFE;
  --borde:      #E5E7EB;
  --fondo:      #F5F5F5;
  --texto:      #1A1A1A;
  --gris:       #6B7280;
  --verde:      #16A34A;
  --rojo:       #DC2626;

  max-width: 100%;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
}

/* ══ Breadcrumb ══ */
.breadcrumb { display: flex; align-items: center; gap: 6px; color: var(--gris); font-size: 0.88rem; margin-bottom: 1rem; }
.breadcrumb-link { cursor: pointer; color: var(--azul); font-weight: 500; transition: color 0.15s; }
.breadcrumb-link:hover { color: var(--azul-hover); text-decoration: underline; }
.breadcrumb-sep { color: #9CA3AF; }
.breadcrumb-actual { color: var(--gris); font-weight: 600; }

/* ══ Encabezado ══ */
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.2rem; }
.page-title  { color: var(--texto); font-size: 1.75rem; font-weight: 700; letter-spacing: -0.02em; margin: 0; }
.page-subtitle { font-size: 0.9rem; color: var(--gris); display: block; margin-top: 3px; }

.btn-exportar {
  display: flex; align-items: center; gap: 7px;
  background: var(--azul-suave); color: var(--azul);
  border: 1px solid #BFDBFE; padding: 9px 18px;
  border-radius: 8px; font-weight: 600; font-size: 0.88rem;
  cursor: pointer; font-family: 'Montserrat', sans-serif;
  transition: background 0.15s; flex-shrink: 0;
}
.btn-exportar:hover { background: #BFDBFE; }
.exportar-icono { width: 17px; height: 17px; stroke: var(--azul); }

/* ══ Barra de carga ══ */
.barra-carga-global { height: 3px; background: transparent; border-radius: 2px; margin-bottom: 1rem; overflow: hidden; opacity: 0; transition: opacity 0.3s; }
.barra-carga-global.visible { opacity: 1; }
.barra-progreso { height: 100%; width: 40%; background: var(--azul); border-radius: 2px; animation: deslizar 1.2s ease-in-out infinite; }
@keyframes deslizar { 0% { transform: translateX(-100%); } 100% { transform: translateX(350%); } }

/* ══ Notificación ══ */
.notificacion-ui { display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 10px; font-size: 0.93rem; font-weight: 500; margin-bottom: 1rem; }
.notificacion-ui.exito { background: #DCFCE7; color: var(--verde); border: 1px solid #86EFAC; }
.notificacion-ui.error { background: #FEE2E2; color: var(--rojo); border: 1px solid #FCA5A5; }
.notif-icono { width: 20px; height: 20px; flex-shrink: 0; }
.notif-fade-enter-active, .notif-fade-leave-active { transition: all 0.35s ease; }
.notif-fade-enter-from, .notif-fade-leave-to { opacity: 0; transform: translateY(-8px); }

/* ══ Tarjeta de búsqueda ══ */
.tarjeta-busqueda {
  background: #FFFFFF; border-radius: 12px;
  padding: 1.3rem 1.6rem; margin-bottom: 1.2rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border: 1px solid var(--borde); position: relative;
}
.busqueda-encabezado { display: flex; align-items: center; gap: 8px; margin-bottom: 1rem; }
.busqueda-icono-titulo { width: 20px; height: 20px; stroke: var(--azul); }
.busqueda-titulo { font-size: 1rem; font-weight: 700; color: var(--azul); }

.busqueda-fila { display: flex; gap: 0.75rem; align-items: stretch; }
.busqueda-docente-input { flex: 1; }
.search-group { position: relative; }
.search-input { width: 100%; padding: 10px 14px 10px 42px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.93rem; background: #FFFFFF; color: var(--texto); font-family: 'Montserrat', sans-serif; outline: none; transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box; }
.search-input:focus { border-color: var(--azul); box-shadow: 0 0 0 3px var(--azul-suave); }
.search-input::placeholder { color: #9CA3AF; }
.search-icon-svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; stroke: var(--gris); pointer-events: none; }
.spinner-busqueda { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; border: 2px solid var(--borde); border-top-color: var(--azul); border-radius: 50%; animation: girar 0.7s linear infinite; }

.btn-buscar {
  background: var(--azul); color: white; border: none;
  padding: 0 22px; border-radius: 8px; font-weight: 600;
  font-size: 0.92rem; cursor: pointer; white-space: nowrap;
  font-family: 'Montserrat', sans-serif; transition: background 0.15s;
  display: flex; align-items: center; gap: 6px;
}
.btn-buscar:hover:not(:disabled) { background: var(--azul-hover); }
.btn-buscar:disabled { opacity: 0.5; cursor: not-allowed; }
.spinner-btn-peq { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.4); border-top-color: white; border-radius: 50%; animation: girar 0.7s linear infinite; }

/* Sugerencias ══ */
.sugerencias-lista {
  position: absolute; left: 1.6rem; right: 1.6rem; top: calc(100% - 0.8rem);
  background: #FFFFFF; border-radius: 10px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.14);
  border: 1px solid var(--borde); z-index: 100; overflow: hidden;
}
.sugerencia-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; cursor: pointer; transition: background 0.15s; border-bottom: 1px solid var(--borde); }
.sugerencia-item:last-child { border-bottom: none; }
.sugerencia-item:hover { background: #F8FAFC; }
.sugerencia-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--azul); color: white; font-size: 0.95rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sugerencia-info { flex: 1; }
.sugerencia-nombre  { display: block; font-weight: 600; font-size: 0.92rem; color: var(--texto); }
.sugerencia-detalle { display: block; font-size: 0.78rem; color: var(--gris); margin-top: 2px; }
.sugerencia-flecha  { width: 16px; height: 16px; stroke: #9CA3AF; }

/* ══ Bloque docente seleccionado ══ */
.bloque-docente-seleccionado {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 18px; margin-bottom: 1.2rem;
  background: #F0FDF4;
  border: 1.5px solid #86EFAC;
  border-radius: 12px;
}
.docente-sel-izquierda { display: flex; align-items: center; gap: 14px; }
.docente-sel-avatar { width: 46px; height: 46px; border-radius: 50%; background: var(--verde); color: white; font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.docente-sel-nombre { margin: 0; font-size: 1rem; font-weight: 700; color: var(--texto); }
.docente-sel-sub    { margin: 4px 0 0; font-size: 0.82rem; color: var(--gris); }
.docente-sel-sep    { margin: 0 5px; }

.btn-cambiar-docente {
  display: flex; align-items: center; gap: 6px;
  background: #FFFFFF; color: var(--gris);
  border: 1px solid var(--borde); padding: 7px 14px;
  border-radius: 8px; font-size: 0.85rem; font-weight: 600;
  cursor: pointer; font-family: 'Montserrat', sans-serif;
  transition: background 0.15s; white-space: nowrap; flex-shrink: 0;
}
.btn-cambiar-docente:hover { background: var(--fondo); }
.btn-cambiar-docente svg { width: 15px; height: 15px; stroke: var(--gris); }

.confirmacion-fade-enter-active, .confirmacion-fade-leave-active { transition: all 0.3s ease; }
.confirmacion-fade-enter-from, .confirmacion-fade-leave-to { opacity: 0; transform: translateY(-6px); }

/* ══ Encabezado de tabla ══ */
.tabla-encabezado { display: flex; align-items: baseline; gap: 0.8rem; margin-bottom: 0.8rem; }
.tabla-titulo { font-size: 1.1rem; font-weight: 700; color: var(--texto); margin: 0; }
.tabla-contador { font-size: 0.88rem; color: var(--gris); font-weight: 500; }

/* ══ Tabla ══ */
.table-container { background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid var(--borde); }
.alumnos-table { width: 100%; border-collapse: collapse; }
.alumnos-table th { background: var(--fondo); padding: 12px 14px; text-align: left; font-weight: 600; font-size: 0.83rem; color: var(--texto); border-bottom: 1px solid var(--borde); white-space: nowrap; }
.alumnos-table td { padding: 11px 14px; border-bottom: 1px solid var(--borde); color: var(--texto); font-size: 0.88rem; font-family: 'Montserrat', sans-serif; vertical-align: middle; }
.alumnos-table tbody tr { transition: background 0.15s; cursor: pointer; }
.alumnos-table tbody tr:hover { background: #F8FAFC; }
.alumnos-table tbody tr:last-child td { border-bottom: none; }
.fila-seleccionada { background: var(--azul-suave) !important; }

.celda-clave    { font-weight: 700; color: var(--azul); font-size: 0.85rem; white-space: nowrap; }
.celda-materia  { font-weight: 500; max-width: 200px; }
.celda-carrera  { color: var(--gris); font-size: 0.83rem; max-width: 160px; }
.celda-semestre { text-align: center; font-weight: 600; }
.celda-horario  { white-space: nowrap; }
.horario-linea  { font-weight: 600; font-size: 0.85rem; }
.horario-hora   { font-size: 0.78rem; color: var(--gris); }
.celda-alumnos  { white-space: nowrap; }
.alumnos-wrap   { display: flex; align-items: baseline; gap: 3px; }
.alumnos-numero { font-weight: 700; color: var(--texto); font-size: 0.95rem; }
.alumnos-total  { font-size: 0.8rem; color: var(--gris); }

/* Acciones ══ */
.celda-acciones { white-space: nowrap; }
.btn-accion { display: flex; align-items: center; gap: 5px; padding: 7px 13px; border-radius: 6px; font-size: 0.83rem; cursor: pointer; font-weight: 600; font-family: 'Montserrat', sans-serif; transition: background 0.15s; border: none; }
.btn-accion svg { width: 15px; height: 15px; }
.btn-accion.desasignar { background: #FEE2E2; color: var(--rojo); border: 1px solid #FECACA; }
.btn-accion.desasignar:hover { background: #FECACA; }

/* Skeleton tabla ══ */
.skeleton-tabla { padding: 0; }
.skeleton-fila { display: flex; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--borde); animation: pulso 1.5s ease-in-out infinite; }
.skeleton-celda { background: #E5E7EB; border-radius: 4px; height: 12px; }
.skeleton-celda-corta   { width: 60px; }
.skeleton-celda-media   { width: 120px; }
.skeleton-celda-larga   { flex: 1; }
.skeleton-celda-acciones { width: 100px; }
@keyframes pulso { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }

/* Estado vacío ══ */
.estado-vacio { text-align: center; padding: 3rem 2rem; color: var(--gris); }
.icono-vacio { width: 52px; height: 52px; stroke: #9CA3AF; margin-bottom: 12px; }
.estado-vacio h3 { font-size: 1.1rem; color: var(--texto); margin: 0 0 6px; }
.estado-vacio p  { font-size: 0.9rem; margin: 0; }

/* Estado inicial (sin docente) ══ */
.estado-inicial {
  text-align: center; padding: 4rem 2rem;
  background: #FFFFFF; border-radius: 12px;
  border: 1px dashed var(--borde);
  color: var(--gris); margin-top: 0.5rem;
}
.icono-inicial { width: 60px; height: 60px; stroke: #D1D5DB; margin-bottom: 14px; }
.estado-inicial h3 { font-size: 1.15rem; color: var(--texto); margin: 0 0 6px; }
.estado-inicial p  { font-size: 0.9rem; margin: 0; }

/* ══ Indicador de carga total ══ */
.indicador-carga {
  display: flex; align-items: center; justify-content: space-between;
  margin-top: 1rem; padding: 12px 18px;
  background: var(--azul-suave); border-radius: 10px;
  border: 1px solid #BFDBFE;
}
.carga-total-wrap { display: flex; align-items: center; gap: 10px; }
.carga-icono { width: 20px; height: 20px; stroke: var(--azul); flex-shrink: 0; }
.carga-texto { font-size: 0.93rem; color: var(--texto); }
.carga-valor { color: var(--azul); font-weight: 700; font-size: 1.05rem; }
.carga-grupos-total { font-size: 0.85rem; color: var(--gris); }

/* ══ Footer institucional ══ */
.footer-institucional {
  display: flex; align-items: flex-start; gap: 8px;
  margin-top: 1.4rem; padding: 10px 14px;
  background: #F8FAFC; border-radius: 8px;
  border: 1px solid var(--borde);
  font-size: 0.83rem; color: var(--gris);
  font-family: 'Montserrat', sans-serif;
}
.footer-icono { width: 16px; height: 16px; stroke: var(--gris); flex-shrink: 0; margin-top: 1px; }
.footer-institucional strong { color: var(--texto); }

/* ══ Modales ══ */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.modal-content { background: #FFFFFF; width: 480px; max-width: 92%; border-radius: 14px; box-shadow: 0 20px 50px rgba(0,0,0,0.18); overflow: hidden; border: 1px solid var(--borde); }
.modal-confirmacion { width: 460px; }

.modal-header { background: var(--azul); color: white; padding: 1.1rem 1.6rem; display: flex; justify-content: space-between; align-items: center; }
.modal-header-rojo { background: var(--rojo); }
.modal-header h3 { margin: 0; font-size: 1.2rem; font-weight: 700; font-family: 'Montserrat', sans-serif; }
.btn-cerrar-modal { background: none; border: none; color: white; font-size: 1.7rem; cursor: pointer; line-height: 1; opacity: 0.85; }
.btn-cerrar-modal:hover { opacity: 1; }

.modal-body { padding: 1.6rem; }

/* Confirmación ══ */
.confirmacion-icono-wrap { display: flex; justify-content: center; margin-bottom: 1rem; }
.confirmacion-icono { width: 52px; height: 52px; stroke: #D97706; }
.confirmacion-mensaje { text-align: center; font-size: 0.95rem; color: var(--texto); margin: 0 0 0.6rem; line-height: 1.5; font-family: 'Montserrat', sans-serif; }
.confirmacion-submensaje { text-align: center; font-size: 0.85rem; color: var(--gris); margin: 0 0 1.2rem; line-height: 1.4; }

.confirmacion-resumen {
  background: var(--fondo); border-radius: 8px;
  padding: 12px 14px; border: 1px solid var(--borde);
  display: flex; flex-direction: column; gap: 8px;
}
.resumen-item { display: flex; justify-content: space-between; font-size: 0.88rem; }
.resumen-label { font-weight: 600; color: var(--gris); }
.resumen-valor { font-weight: 500; color: var(--texto); }

.modal-footer { padding: 1rem 1.6rem; background: var(--fondo); display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid var(--borde); }
.btn-secundario { padding: 10px 22px; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); transition: background 0.15s; }
.btn-secundario:hover { background: var(--fondo); }
.btn-secundario:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-desasignar-confirmar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 26px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  background: #DC2626;           /* Rojo fuerte visible desde el inicio */
  color: white;
  border: none;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
  transition: all 0.2s ease;
}

.btn-desasignar-confirmar:hover:not(:disabled) {
  background: #B91C1C;
  box-shadow: 0 6px 16px rgba(220, 38, 38, 0.4);
  transform: translateY(-1px);
}

.btn-desasignar-confirmar:disabled {
  background: #9AA3AF;
  color: white;
  opacity: 0.85;
  cursor: not-allowed;
  box-shadow: none;
}

.spinner-btn { display: inline-block; width: 15px; height: 15px; border: 2px solid rgba(255,255,255,0.4); border-top-color: white; border-radius: 50%; animation: girar 0.7s linear infinite; }

@keyframes girar { to { transform: rotate(360deg); } }
</style>
