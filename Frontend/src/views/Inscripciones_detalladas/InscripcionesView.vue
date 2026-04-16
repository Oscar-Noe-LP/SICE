<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="inscripciones-page">

      <!-- ── Header ── -->
      <div class="page-header">
        <h1 class="page-title">Inscripciones</h1>
        <span class="page-subtitle">{{ inscripcionesFiltradas.length }} registros encontrados</span>
      </div>

      <!-- ── Barra de carga global ── -->
      <div class="barra-carga-global" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- ── Toast ── -->
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

      <!-- ── KPIs ── -->
      <div class="stats-grid">
        <div class="stat-card stat-azul">
          <div class="stat-icono-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="stat-icono">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <div class="stat-info">
            <p class="stat-etiqueta">Total Inscritos</p>
            <div v-if="cargandoKpis" class="stat-skeleton"></div>
            <p v-else class="stat-numero">{{ kpis.totalInscritos }}</p>
            <span class="stat-link">Periodo activo</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icono-wrapper stat-icono-amarillo">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="stat-icono-amarillo-svg">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
          </div>
          <div class="stat-info">
            <p class="stat-etiqueta">Cambios Solicitados</p>
            <div v-if="cargandoKpis" class="stat-skeleton"></div>
            <p v-else class="stat-numero">{{ kpis.cambiosSolicitados }}</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icono-wrapper stat-icono-rojo">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="stat-icono-rojo-svg">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
          </div>
          <div class="stat-info">
            <p class="stat-etiqueta">Bajas Registradas</p>
            <div v-if="cargandoKpis" class="stat-skeleton"></div>
            <p v-else class="stat-numero">{{ kpis.bajasRegistradas }}</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icono-wrapper stat-icono-verde">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="stat-icono-verde-svg">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </div>
          <div class="stat-info">
            <p class="stat-etiqueta">Reinscripciones</p>
            <div v-if="cargandoKpis" class="stat-skeleton"></div>
            <p v-else class="stat-numero">{{ kpis.reinscripciones }}</p>
          </div>
        </div>
      </div>

      <!-- ── Error ── -->
      <div v-if="errorCarga" class="alerta-error">
        <svg xmlns="http://www.w3.org/2000/svg" class="alerta-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>No se pudieron cargar los datos. Verifica la conexión con el servidor.</span>
        <button class="btn-reintentar" @click="cargarDatos">Reintentar</button>
      </div>

      <!-- ── Filtros ── -->
      <div class="filters-bar">
        <div class="search-group">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Buscar por número de control o nombre..."
            v-model="busquedaLocal"
            class="search-input"
            @keydown.escape="busquedaLocal = ''"
          >
          <span v-if="cargandoBusqueda" class="spinner-busqueda"></span>
        </div>

        <select v-model="filtroPeriodo" class="filter-select" @change="currentPage = 1">
          <option value="">Periodo</option>
          <option v-for="p in periodosDisponibles" :key="p" :value="p">{{ p }}</option>
        </select>

        <select v-model="filtroCarrera" class="filter-select" @change="currentPage = 1">
          <option value="">Carrera</option>
          <option value="Contador Publico">Contador Público</option>
          <option value="Ingenieria Civil">Ingeniería Civil</option>
          <option value="Ingenieria en Gestion empresarial">Ing. en Gestión Empresarial</option>
          <option value="Ingenieria en Sistemas Computacionales">Ing. en Sistemas Computacionales</option>
          <option value="Ingenieria Industrial">Ingeniería Industrial</option>
        </select>

        <select v-model="filtroEstado" class="filter-select" @change="currentPage = 1">
          <option value="">Estado</option>
          <option value="activa">Activa</option>
          <option value="baja">Baja</option>
          <option value="cambio_pendiente">Cambio pendiente</option>
        </select>

        <button class="btn-limpiar" @click="resetFiltros">
          <svg xmlns="http://www.w3.org/2000/svg" class="reset-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Limpiar
        </button>

        <button class="btn-nuevo" @click="abrirModalNueva">+ Nueva Inscripción</button>
      </div>

      <!-- ── Tabla ── -->
      <div class="table-container">
        <div v-if="cargando && inscripciones.length === 0" class="estado-cargando">
          <div class="spinner-tabla"></div>
          <p>Cargando registros...</p>
        </div>

        <table v-else-if="inscripcionesPaginadas.length > 0" class="data-table">
          <thead>
            <tr>
              <th>No. Control</th>
              <th>Alumno</th>
              <th>Carrera</th>
              <th>Periodo</th>
              <th>Materias</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(ins, index) in inscripcionesPaginadas"
              :key="ins.id_inscripcion"
              :class="{ 'fila-seleccionada': filaActiva === index }"
              @click="filaActiva = index"
            >
              <td class="celda-control">{{ ins.numero_control }}</td>
              <td>{{ ins.nombre_alumno }}</td>
              <td>{{ ins.carrera }}</td>
              <td>{{ ins.periodo }}</td>
              <td class="celda-center">{{ ins.total_materias }}</td>
              <td>
                <span class="estatus-badge" :class="claseEstado(ins.estado)">{{ etiquetaEstado(ins.estado) }}</span>
              </td>
              <td class="celda-acciones">
                <button class="btn-accion ver" @click.stop="abrirModalVer(ins)">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  Ver
                </button>
                <button class="btn-accion editar" @click.stop="abrirModalEditar(ins)">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                  Editar
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <h3>Sin resultados</h3>
          <p>{{ busquedaLocal || filtroPeriodo || filtroCarrera || filtroEstado ? 'No se encontraron inscripciones con los filtros aplicados.' : 'No hay inscripciones registradas.' }}</p>
          <button class="btn-limpiar-vacio" @click="resetFiltros">Limpiar filtros</button>
        </div>
      </div>

      <!-- ── Paginación ── -->
      <div class="paginacion">
        <div class="paginacion-izquierda">
          Filas por página:
          <select v-model="filasPorPagina" @change="currentPage = 1" class="select-filas">
            <option :value="10">10</option><option :value="15">15</option>
            <option :value="20">20</option><option :value="50">50</option>
          </select>
        </div>
        <div class="paginacion-centro">Página {{ currentPage }} de {{ totalPaginas }}</div>
        <div class="paginacion-derecha">
          <button class="btn-pag" @click="prevPage" :disabled="currentPage === 1">‹</button>
          <button v-for="page in paginasVisibles" :key="page" class="btn-pag" :class="{ activo: page === currentPage }" @click="goToPage(page)">{{ page }}</button>
          <button class="btn-pag" @click="nextPage" :disabled="currentPage === totalPaginas">›</button>
        </div>
      </div>

    </div>

    <!-- ══ MODAL VER ══ -->
    <div v-if="showModalVer" class="modal-overlay" @click.self="showModalVer = false">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Detalle de Inscripción</h3>
          <button @click="showModalVer = false" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body" v-if="inscripcionVer">
          <div class="detalle-fila"><span class="detalle-label">No. Control</span><span class="detalle-valor">{{ inscripcionVer.numero_control }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Alumno</span><span class="detalle-valor">{{ inscripcionVer.nombre_alumno }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Carrera</span><span class="detalle-valor">{{ inscripcionVer.carrera }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Periodo</span><span class="detalle-valor">{{ inscripcionVer.periodo }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Total Materias</span><span class="detalle-valor">{{ inscripcionVer.total_materias }}</span></div>
          <div class="detalle-fila">
            <span class="detalle-label">Estado</span>
            <span class="estatus-badge" :class="claseEstado(inscripcionVer.estado)">{{ etiquetaEstado(inscripcionVer.estado) }}</span>
          </div>
          <div v-if="inscripcionVer.materias && inscripcionVer.materias.length > 0" class="detalle-materias-wrapper">
            <p class="detalle-subtitulo">Materias inscritas</p>
            <div v-for="m in inscripcionVer.materias" :key="m.id_materia" class="detalle-materia">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="mat-icono">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ m.nombre_materia }} — Grupo {{ m.grupo }}
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="showModalVer = false">Cerrar</button>
          <button class="btn-guardar" @click="abrirModalEditar(inscripcionVer); showModalVer = false">Editar</button>
        </div>
      </div>
    </div>

    <!-- ══ MODAL CREAR / EDITAR ══ -->
    <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ form.id_inscripcion ? 'Editar Inscripción' : 'Nueva Inscripción' }}</h3>
          <button @click="cerrarModal" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">

          <div class="form-grupo">
            <label>Número de Control *</label>
            <template v-if="form.id_inscripcion">
              <div class="modal-input deshabilitado">{{ form.numero_control }}</div>
            </template>
            <template v-else>
              <div class="search-inline-modal">
                <input v-model="form.numero_control" type="text" class="modal-input" placeholder="Ej: 21456887" @keydown.enter="buscarAlumnoPorControl" />
                <button class="btn-buscar-modal" @click="buscarAlumnoPorControl" :disabled="buscandoAlumno">
                  <span v-if="buscandoAlumno" class="spinner-btn"></span>
                  <span v-else>Buscar</span>
                </button>
              </div>
            </template>
          </div>

          <transition name="slide-down">
            <div v-if="alumnoEncontrado" class="bloque-alumno-found">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="found-icono">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div>
                <p class="found-nombre">{{ alumnoEncontrado.nombre }}</p>
                <p class="found-detalle">{{ alumnoEncontrado.carrera }} · Semestre {{ alumnoEncontrado.semestre }}</p>
              </div>
            </div>
          </transition>

          <div class="form-grupo-doble">
            <div class="form-grupo">
              <label>Periodo *</label>
              <select v-model="form.id_periodo" class="modal-select">
                <option value="">Seleccionar...</option>
                <option v-for="p in periodosDB" :key="p.id_periodo" :value="p.id_periodo">{{ p.nombre_periodo }}</option>
              </select>
            </div>
            <div class="form-grupo">
              <label>Estado *</label>
              <select v-model="form.estado" class="modal-select">
                <option value="activa">Activa</option>
                <option value="baja">Baja</option>
                <option value="cambio_pendiente">Cambio pendiente</option>
              </select>
            </div>
          </div>

          <div class="form-grupo">
            <label>Observaciones</label>
            <textarea v-model="form.observaciones" class="modal-textarea" rows="3" placeholder="Observaciones opcionales..."></textarea>
          </div>

        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">Cancelar</button>
          <button v-if="form.id_inscripcion" class="btn-eliminar" @click="pedirConfirmarEliminar" :disabled="guardando">Eliminar</button>
          <button class="btn-guardar" @click="guardarInscripcion" :disabled="guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            <span>{{ guardando ? 'Guardando...' : 'Guardar cambios' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ══ MODAL CONFIRMAR ELIMINAR ══ -->
    <div v-if="showModalEliminar" class="modal-overlay" @click.self="showModalEliminar = false">
      <div class="modal-content modal-confirmar">
        <div class="modal-header modal-header-danger">
          <h3>Confirmar eliminación</h3>
          <button @click="showModalEliminar = false" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">
          <p class="confirmar-texto">
            ¿Estás seguro de que deseas eliminar la inscripción de
            <strong>{{ inscripcionAEliminar?.nombre_alumno }}</strong>?
            Esta acción no se puede deshacer.
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="showModalEliminar = false" :disabled="guardando">Cancelar</button>
          <button class="btn-eliminar" @click="confirmarEliminar" :disabled="guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            <span>{{ guardando ? 'Eliminando...' : 'Sí, eliminar' }}</span>
          </button>
        </div>
      </div>
    </div>

  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

// ─────────────────────────────────────────────────────────────────────────────
// BASE URL — el equipo de back solo cambia esta constante
// ─────────────────────────────────────────────────────────────────────────────
const API = 'http://localhost:8000/api'

// ─────────────────────────────────────────────────────────────────────────────
// ESTADOS
// ─────────────────────────────────────────────────────────────────────────────
const inscripciones    = ref([])
const periodosDB       = ref([])
const cargando         = ref(false)
const cargandoKpis     = ref(false)
const cargandoBusqueda = ref(false)
const guardando        = ref(false)
const errorCarga       = ref(false)
const filaActiva       = ref(-1)

const kpis = ref({ totalInscritos: 0, cambiosSolicitados: 0, bajasRegistradas: 0, reinscripciones: 0 })

// Filtros
const busquedaLocal  = ref('')
const filtroPeriodo  = ref('')
const filtroCarrera  = ref('')
const filtroEstado   = ref('')
const filasPorPagina = ref(15)
const currentPage    = ref(1)

// Modales
const showModalVer       = ref(false)
const showModal          = ref(false)
const showModalEliminar  = ref(false)
const inscripcionVer      = ref(null)
const inscripcionAEliminar = ref(null)
const alumnoEncontrado    = ref(null)
const buscandoAlumno      = ref(false)

const formVacio = () => ({
  id_inscripcion: null,
  numero_control: '',
  id_alumno:      null,
  id_periodo:     '',
  estado:         'activa',
  observaciones:  ''
})
const form = ref(formVacio())

// Notificación
const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null
const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

// ─────────────────────────────────────────────────────────────────────────────
// CARGA DE DATOS
// ─────────────────────────────────────────────────────────────────────────────

/*
 * GET /api/inscripciones
 * Respuesta esperada (array):
 * [{
 *   id_inscripcion: number,
 *   id_alumno:      number,
 *   numero_control: string,
 *   nombre_alumno:  string,
 *   carrera:        string,
 *   id_periodo:     number,
 *   periodo:        string,
 *   total_materias: number,
 *   estado:         "activa" | "baja" | "cambio_pendiente",
 *   observaciones:  string | null,
 *   materias: [{    <-- opcional, se muestra en modal Ver
 *     id_materia:     number,
 *     nombre_materia: string,
 *     grupo:          string
 *   }]
 * }]
 */
const cargarInscripciones = async () => {
  cargando.value   = true
  errorCarga.value = false
  try {
    const res  = await fetch(`${API}/inscripciones`)
    if (!res.ok) throw new Error(`Error ${res.status}`)
    inscripciones.value = await res.json()
    console.log('✅ Inscripciones cargadas:', inscripciones.value.length)
  } catch (err) {
    console.error('❌ cargarInscripciones:', err)
    errorCarga.value = true
    mostrarNotificacion('No se pudo cargar la lista de inscripciones.', 'error')
  } finally {
    cargando.value = false
  }
}

/*
 * GET /api/inscripciones/kpis
 * Respuesta esperada:
 * { totalInscritos: number, cambiosSolicitados: number, bajasRegistradas: number, reinscripciones: number }
 */
const cargarKpis = async () => {
  cargandoKpis.value = true
  try {
    const res = await fetch(`${API}/inscripciones/kpis`)
    if (!res.ok) throw new Error(`Error ${res.status}`)
    kpis.value = await res.json()
  } catch (err) {
    console.error('❌ cargarKpis:', err)
  } finally {
    cargandoKpis.value = false
  }
}

/*
 * GET /api/periodos
 * Respuesta esperada (array):
 * [{ id_periodo: number, nombre_periodo: string }]
 */
const cargarPeriodos = async () => {
  try {
    const res = await fetch(`${API}/periodos`)
    if (!res.ok) throw new Error(`Error ${res.status}`)
    periodosDB.value = await res.json()
  } catch (err) {
    console.error('❌ cargarPeriodos:', err)
  }
}

const cargarDatos = () => { cargarInscripciones(); cargarKpis(); cargarPeriodos() }
onMounted(cargarDatos)

// ─────────────────────────────────────────────────────────────────────────────
// BUSCAR ALUMNO POR No. CONTROL
// GET /api/alumnos/control/:numero_control
// Respuesta: { id_alumno: number, nombre: string, carrera: string, semestre: number }
// ─────────────────────────────────────────────────────────────────────────────
const buscarAlumnoPorControl = async () => {
  const nc = (form.value.numero_control || '').trim()
  if (!nc) return
  buscandoAlumno.value   = true
  alumnoEncontrado.value = null
  try {
    const res  = await fetch(`${API}/alumnos/control/${nc}`)
    if (!res.ok) throw new Error('No encontrado')
    const data = await res.json()
    alumnoEncontrado.value = data   // { id_alumno, nombre, carrera, semestre }
    form.value.id_alumno   = data.id_alumno
    mostrarNotificacion('Alumno encontrado', 'exito')
  } catch {
    mostrarNotificacion(`No se encontró ningún alumno con el No. de Control ${nc}`, 'error')
    form.value.id_alumno = null
  } finally {
    buscandoAlumno.value = false
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// CRUD
// ─────────────────────────────────────────────────────────────────────────────

/*
 * POST /api/inscripciones
 * Body:    { id_alumno: number, id_periodo: number, estado: string, observaciones: string }
 * Retorna: { id_inscripcion: number, ...resto de campos }
 *
 * PUT /api/inscripciones/:id_inscripcion
 * Body:    { id_periodo: number, estado: string, observaciones: string }
 * Retorna: { id_inscripcion: number, ...campos actualizados }
 */
const guardarInscripcion = async () => {
  if (!form.value.id_periodo) {
    mostrarNotificacion('El periodo es requerido.', 'error'); return
  }
  if (!form.value.id_inscripcion && !form.value.id_alumno) {
    mostrarNotificacion('Debes buscar y seleccionar un alumno primero.', 'error'); return
  }

  guardando.value = true
  const esEdicion = !!form.value.id_inscripcion
  try {
    const url    = esEdicion ? `${API}/inscripciones/${form.value.id_inscripcion}` : `${API}/inscripciones`
    const method = esEdicion ? 'PUT' : 'POST'
    const body   = esEdicion
      ? { id_periodo: form.value.id_periodo, estado: form.value.estado, observaciones: form.value.observaciones }
      : { id_alumno: form.value.id_alumno,   id_periodo: form.value.id_periodo, estado: form.value.estado, observaciones: form.value.observaciones }

    console.log(`🔵 ${method} ${url}`, body)
    const res  = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(body) })
    const data = await res.json()
    console.log('🟢 Respuesta:', data)
    if (!res.ok) throw new Error(JSON.stringify(data))

    await cargarInscripciones()
    await cargarKpis()
    cerrarModal()
    mostrarNotificacion(esEdicion ? 'Inscripción actualizada correctamente.' : 'Inscripción creada correctamente.', 'exito')
  } catch (err) {
    console.error('❌ guardarInscripcion:', err)
    mostrarNotificacion('Ocurrió un error al guardar la inscripción.', 'error')
  } finally {
    guardando.value = false
  }
}

/*
 * DELETE /api/inscripciones/:id_inscripcion
 * Retorna: { message: "Eliminado correctamente" }
 */
const pedirConfirmarEliminar = () => {
  inscripcionAEliminar.value = { ...form.value, nombre_alumno: alumnoEncontrado.value?.nombre || form.value.numero_control }
  showModal.value         = false
  showModalEliminar.value = true
}

const confirmarEliminar = async () => {
  guardando.value = true
  try {
    const res  = await fetch(`${API}/inscripciones/${inscripcionAEliminar.value.id_inscripcion}`, {
      method: 'DELETE', headers: { 'Accept': 'application/json' }
    })
    const data = await res.json()
    console.log('🗑️ DELETE inscripción:', data)
    if (!res.ok) throw new Error(JSON.stringify(data))

    await cargarInscripciones()
    await cargarKpis()
    showModalEliminar.value = false
    mostrarNotificacion('Inscripción eliminada correctamente.', 'exito')
  } catch (err) {
    console.error('❌ confirmarEliminar:', err)
    mostrarNotificacion('Ocurrió un error al eliminar la inscripción.', 'error')
  } finally {
    guardando.value = false
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// MODALES
// ─────────────────────────────────────────────────────────────────────────────
const abrirModalNueva = () => {
  form.value             = formVacio()
  alumnoEncontrado.value = null
  showModal.value        = true
}
const abrirModalVer = (ins) => { inscripcionVer.value = ins; showModalVer.value = true }
const abrirModalEditar = (ins) => {
  form.value = {
    id_inscripcion: ins.id_inscripcion,
    numero_control: ins.numero_control,
    id_alumno:      ins.id_alumno ?? null,
    id_periodo:     ins.id_periodo ?? '',
    estado:         ins.estado,
    observaciones:  ins.observaciones ?? ''
  }
  alumnoEncontrado.value = { nombre: ins.nombre_alumno, carrera: ins.carrera, semestre: ins.semestre ?? '' }
  showModal.value = true
}
const cerrarModal = () => { showModal.value = false }

// ─────────────────────────────────────────────────────────────────────────────
// FILTROS Y PAGINACIÓN
// ─────────────────────────────────────────────────────────────────────────────
const normalize = (t) => (t || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')

let timerBusqueda = null
watch(busquedaLocal, () => {
  cargandoBusqueda.value = true
  clearTimeout(timerBusqueda)
  timerBusqueda = setTimeout(() => { cargandoBusqueda.value = false; currentPage.value = 1 }, 350)
})

const inscripcionesFiltradas = computed(() =>
  inscripciones.value.filter(i => {
    const q = normalize(busquedaLocal.value)
    return (!q || normalize(i.numero_control).includes(q) || normalize(i.nombre_alumno).includes(q))
        && (!filtroPeriodo.value || normalize(i.periodo).includes(normalize(filtroPeriodo.value)))
        && (!filtroCarrera.value || normalize(i.carrera).includes(normalize(filtroCarrera.value)))
        && (!filtroEstado.value  || i.estado === filtroEstado.value)
  })
)

const periodosDisponibles = computed(() => [...new Set(inscripciones.value.map(i => i.periodo).filter(Boolean))])
const totalPaginas = computed(() => Math.ceil(inscripcionesFiltradas.value.length / filasPorPagina.value) || 1)
const inscripcionesPaginadas = computed(() => {
  const s = (currentPage.value - 1) * filasPorPagina.value
  return inscripcionesFiltradas.value.slice(s, s + filasPorPagina.value)
})
const paginasVisibles = computed(() => {
  const t = totalPaginas.value, c = currentPage.value
  if (t <= 7) return Array.from({ length: t }, (_, i) => i + 1)
  const p = new Set([1, t, c, c - 1, c + 1].filter(x => x >= 1 && x <= t))
  return [...p].sort((a, b) => a - b)
})
const goToPage  = (p) => { currentPage.value = p; filaActiva.value = -1 }
const prevPage  = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage  = () => { if (currentPage.value < totalPaginas.value) currentPage.value++ }
const resetFiltros = () => { busquedaLocal.value = ''; filtroPeriodo.value = ''; filtroCarrera.value = ''; filtroEstado.value = ''; currentPage.value = 1; filaActiva.value = -1 }

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS BADGE
// ─────────────────────────────────────────────────────────────────────────────
const claseEstado    = (e) => ({ 'badge-activa': e === 'activa', 'badge-baja': e === 'baja', 'badge-cambio': e === 'cambio_pendiente' })
const etiquetaEstado = (e) => ({ activa: 'Activa', baja: 'Baja', cambio_pendiente: 'Cambio pendiente' }[e] || e)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');
.inscripciones-page {
  --azul:#1B396A;--azul-hover:#1D4ED8;--azul-suave:#DBEAFE;
  --borde:#E5E7EB;--fondo:#F5F5F5;--texto:#1A1A1A;--gris:#6B7280;
  --verde:#16A34A;--rojo:#DC2626;--amarillo:#F59E0B;
  max-width:100%;background:var(--fondo);font-family:'Montserrat',sans-serif;
}
.page-header{display:flex;align-items:baseline;gap:1rem;margin-bottom:1.2rem;}
.page-title{color:var(--texto);font-size:1.75rem;font-weight:700;letter-spacing:-.02em;margin:0;}
.page-subtitle{font-size:.9rem;color:var(--gris);font-weight:500;}
.barra-carga-global{height:3px;background:transparent;border-radius:2px;margin-bottom:1rem;overflow:hidden;opacity:0;transition:opacity .3s;}
.barra-carga-global.visible{opacity:1;}
.barra-progreso{height:100%;width:40%;background:#1B396A;border-radius:2px;animation:deslizar 1.2s ease-in-out infinite;}
@keyframes deslizar{0%{transform:translateX(-100%)}100%{transform:translateX(350%)}}
.notificacion-ui{display:flex;align-items:center;gap:10px;padding:12px 18px;border-radius:10px;font-size:.93rem;font-weight:500;margin-bottom:1rem;box-shadow:0 4px 12px rgba(0,0,0,.1);}
.notificacion-ui.exito{background:#DCFCE7;color:#16A34A;border:1px solid #86EFAC;}
.notificacion-ui.error{background:#FEE2E2;color:#DC2626;border:1px solid #FCA5A5;}
.notif-icono{width:20px;height:20px;flex-shrink:0;}
.notif-fade-enter-active,.notif-fade-leave-active{transition:all .35s ease;}
.notif-fade-enter-from,.notif-fade-leave-to{opacity:0;transform:translateY(-8px);}
.stats-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin-bottom:1.5rem;}
.stat-card{background:#fff;border-radius:12px;padding:1.3rem 1.4rem;display:flex;align-items:center;gap:1rem;box-shadow:0 4px 12px rgba(0,0,0,.05);border:1px solid var(--borde);transition:transform .2s,box-shadow .2s;min-width:0;}
.stat-card:hover{transform:translateY(-3px);box-shadow:0 8px 20px rgba(0,0,0,.09);}
.stat-azul{background:var(--azul);border-color:var(--azul);}
.stat-azul .stat-etiqueta{color:rgba(255,255,255,.8);}.stat-azul .stat-numero{color:#fff;}.stat-azul .stat-link{color:rgba(255,255,255,.85);}
.stat-azul .stat-icono-wrapper{background:rgba(255,255,255,.2);}.stat-azul .stat-icono{stroke:#fff;}
.stat-icono-wrapper{width:46px;height:46px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:var(--azul-suave);}
.stat-icono{width:22px;height:22px;stroke:var(--azul);}
.stat-icono-amarillo{background:#FEF3C7;}.stat-icono-amarillo-svg{width:22px;height:22px;stroke:var(--amarillo);}
.stat-icono-rojo{background:#FEF2F2;}.stat-icono-rojo-svg{width:22px;height:22px;stroke:var(--rojo);}
.stat-icono-verde{background:#DCFCE7;}.stat-icono-verde-svg{width:22px;height:22px;stroke:var(--verde);}
.stat-info{display:flex;flex-direction:column;gap:2px;min-width:0;}
.stat-etiqueta{font-size:.83rem;color:var(--gris);font-weight:500;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.stat-numero{font-size:2rem;font-weight:700;color:var(--texto);margin:2px 0;line-height:1;}
.stat-link{font-size:.82rem;color:var(--azul);font-weight:600;white-space:nowrap;}
.stat-skeleton{width:80px;height:32px;background:linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%);background-size:200% 100%;animation:shimmer 1.4s infinite;border-radius:6px;margin:2px 0;}
.stat-azul .stat-skeleton{background:linear-gradient(90deg,rgba(255,255,255,.2) 25%,rgba(255,255,255,.35) 50%,rgba(255,255,255,.2) 75%);background-size:200% 100%;}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
.alerta-error{display:flex;align-items:center;gap:10px;background:#FEF2F2;border:1px solid #FECACA;border-radius:10px;padding:12px 16px;margin-bottom:1.4rem;font-size:.9rem;color:var(--rojo);font-weight:500;}
.alerta-icono{width:20px;height:20px;flex-shrink:0;stroke:var(--rojo);}
.btn-reintentar{margin-left:auto;background:var(--azul);color:#fff;border:none;padding:6px 16px;border-radius:6px;font-weight:600;font-size:.85rem;cursor:pointer;font-family:'Montserrat',sans-serif;transition:background .15s;white-space:nowrap;}
.btn-reintentar:hover{background:var(--azul-hover);}
.filters-bar{display:flex;align-items:center;gap:.75rem;margin-bottom:1.2rem;flex-wrap:wrap;}
.search-group{position:relative;flex:0 0 300px;min-width:220px;}
.search-input{width:100%;padding:10px 14px 10px 42px;border:1px solid var(--borde);border-radius:8px;font-size:.93rem;background:#fff;color:var(--texto);font-family:'Montserrat',sans-serif;outline:none;transition:border-color .2s,box-shadow .2s;box-sizing:border-box;}
.search-input:focus{border-color:var(--azul);box-shadow:0 0 0 3px var(--azul-suave);}
.search-input::placeholder{color:#9CA3AF;}
.search-icon-svg{position:absolute;left:13px;top:50%;transform:translateY(-50%);width:18px;height:18px;stroke:var(--gris);pointer-events:none;}
.spinner-busqueda{position:absolute;right:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;border:2px solid #E5E7EB;border-top-color:var(--azul);border-radius:50%;animation:girar .7s linear infinite;}
.filter-select{padding:10px 12px;border:1px solid var(--borde);border-radius:8px;font-size:.92rem;flex:1 1 150px;min-width:130px;background:#fff;color:var(--texto);font-family:'Montserrat',sans-serif;cursor:pointer;outline:none;}
.filter-select:focus{border-color:var(--azul);}
.btn-limpiar{display:flex;align-items:center;gap:6px;background:#fff;color:var(--texto);border:1px solid var(--borde);padding:10px 16px;border-radius:8px;font-weight:600;cursor:pointer;font-size:.92rem;white-space:nowrap;font-family:'Montserrat',sans-serif;transition:background .15s;}
.btn-limpiar:hover{background:var(--fondo);}
.reset-icon{width:16px;height:16px;stroke:var(--gris);}
.btn-nuevo{background:var(--azul);color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;white-space:nowrap;font-family:'Montserrat',sans-serif;font-size:.92rem;transition:background .2s;margin-left:auto;}
.btn-nuevo:hover{background:var(--azul-hover);}
.table-container{background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.05);border:1px solid var(--borde);}
.data-table{width:100%;border-collapse:collapse;outline:none;}
.data-table th{background:var(--fondo);padding:12px 16px;text-align:left;font-weight:600;font-size:.88rem;color:var(--texto);border-bottom:1px solid var(--borde);white-space:nowrap;font-family:'Montserrat',sans-serif;}
.data-table td{padding:11px 16px;border-bottom:1px solid var(--borde);color:var(--texto);font-size:.93rem;font-family:'Montserrat',sans-serif;}
.data-table tbody tr{transition:background .15s;cursor:pointer;}
.data-table tbody tr:hover{background:#F8FAFC;}
.data-table tbody tr:last-child td{border-bottom:none;}
.fila-seleccionada{background:var(--azul-suave)!important;}
.celda-control{font-weight:600;color:var(--azul);}
.celda-center{text-align:center;}
.estatus-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:.83rem;font-weight:600;font-family:'Montserrat',sans-serif;}
.badge-activa{background:#DCFCE7;color:#16A34A;}.badge-baja{background:#FEE2E2;color:#DC2626;}.badge-cambio{background:#FEF3C7;color:#F59E0B;}
.celda-acciones{display:flex;gap:7px;align-items:center;}
.btn-accion{display:flex;align-items:center;gap:5px;padding:6px 13px;border-radius:6px;font-size:.85rem;cursor:pointer;font-weight:600;font-family:'Montserrat',sans-serif;transition:background .15s;white-space:nowrap;}
.btn-accion svg{width:14px;height:14px;}
.btn-accion.ver{background:#fff;color:var(--texto);border:1px solid var(--borde);}
.btn-accion.ver:hover{background:var(--fondo);}
.btn-accion.editar{background:var(--azul);color:#fff;border:1px solid var(--azul);}
.btn-accion.editar:hover{background:var(--azul-hover);border-color:var(--azul-hover);}
.estado-vacio,.estado-cargando{text-align:center;padding:3.5rem 2rem;color:var(--gris);}
.icono-vacio{width:56px;height:56px;stroke:#9CA3AF;margin-bottom:12px;}
.estado-vacio h3{font-size:1.2rem;color:var(--texto);margin:0 0 6px;}
.estado-vacio p{font-size:.93rem;margin:0 0 1.2rem;}
.spinner-tabla{display:inline-block;width:36px;height:36px;border:3px solid #E5E7EB;border-top-color:var(--azul);border-radius:50%;animation:girar .8s linear infinite;margin-bottom:12px;}
.btn-limpiar-vacio{background:#fff;color:var(--texto);border:1px solid var(--borde);padding:9px 20px;border-radius:8px;font-weight:500;cursor:pointer;font-family:'Montserrat',sans-serif;}
.paginacion{margin-top:1.2rem;display:flex;justify-content:space-between;align-items:center;font-size:.9rem;color:var(--gris);font-family:'Montserrat',sans-serif;flex-wrap:wrap;gap:.5rem;}
.paginacion-izquierda,.paginacion-centro,.paginacion-derecha{display:flex;align-items:center;gap:8px;}
.select-filas{border:1px solid var(--borde);border-radius:6px;padding:4px 8px;font-size:.9rem;background:#fff;font-family:'Montserrat',sans-serif;}
.btn-pag{padding:5px 11px;border:1px solid var(--borde);background:#fff;border-radius:6px;cursor:pointer;color:var(--texto);font-family:'Montserrat',sans-serif;font-size:.9rem;transition:background .15s;}
.btn-pag:hover:not(:disabled){background:var(--fondo);}
.btn-pag:disabled{opacity:.4;cursor:not-allowed;}
.btn-pag.activo{background:var(--azul);color:#fff;border-color:var(--azul);}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);display:flex;align-items:center;justify-content:center;z-index:2000;padding:1rem;}
.modal-content{background:#fff;width:540px;max-width:95%;border-radius:14px;box-shadow:0 20px 50px rgba(0,0,0,.18);overflow:hidden;border:1px solid var(--borde);max-height:90vh;display:flex;flex-direction:column;}
.modal-confirmar{width:460px;}
.modal-header{background:var(--azul);color:#fff;padding:1.1rem 1.6rem;display:flex;justify-content:space-between;align-items:center;flex-shrink:0;}
.modal-header h3{margin:0;font-size:1.2rem;font-weight:700;font-family:'Montserrat',sans-serif;}
.modal-header-danger{background:var(--rojo);}
.btn-cerrar-modal{background:none;border:none;color:#fff;font-size:1.7rem;cursor:pointer;line-height:1;opacity:.85;}
.btn-cerrar-modal:hover{opacity:1;}
.modal-body{padding:1.6rem;overflow-y:auto;flex:1;}
.modal-footer{padding:1rem 1.6rem;background:var(--fondo);display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--borde);flex-shrink:0;}
.form-grupo{margin-bottom:1.2rem;}
.form-grupo-doble{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
.form-grupo label{display:block;margin-bottom:6px;font-weight:600;font-size:.9rem;color:var(--texto);font-family:'Montserrat',sans-serif;}
.modal-input,.modal-select,.modal-textarea{width:100%;padding:10px 14px;border:1.5px solid var(--borde);border-radius:8px;font-size:.95rem;background:#fff;color:var(--texto);font-family:'Montserrat',sans-serif;outline:none;transition:border-color .2s;box-sizing:border-box;}
.modal-input:focus,.modal-select:focus,.modal-textarea:focus{border-color:var(--azul);}
.modal-input.deshabilitado{background:var(--fondo);color:var(--gris);font-weight:600;display:flex;align-items:center;border:1.5px solid var(--borde);border-radius:8px;padding:10px 14px;}
.modal-textarea{resize:vertical;}
.search-inline-modal{display:flex;gap:8px;}
.search-inline-modal .modal-input{flex:1;}
.btn-buscar-modal{background:var(--azul);color:#fff;border:none;padding:10px 16px;border-radius:8px;font-weight:600;font-size:.88rem;cursor:pointer;font-family:'Montserrat',sans-serif;transition:background .2s;white-space:nowrap;display:flex;align-items:center;gap:6px;}
.btn-buscar-modal:hover:not(:disabled){background:var(--azul-hover);}
.btn-buscar-modal:disabled{opacity:.6;cursor:not-allowed;}
.bloque-alumno-found{display:flex;align-items:center;gap:10px;background:#F0FDF4;border:1px solid #86EFAC;border-radius:8px;padding:10px 14px;margin-bottom:1.2rem;}
.found-icono{width:22px;height:22px;stroke:var(--verde);flex-shrink:0;}
.found-nombre{font-size:.92rem;font-weight:700;color:var(--texto);margin:0 0 2px;}
.found-detalle{font-size:.8rem;color:var(--gris);margin:0;}
.slide-down-enter-active{transition:all .25s ease;}
.slide-down-enter-from{opacity:0;transform:translateY(-6px);}
.btn-secundario{padding:10px 22px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;background:#fff;color:var(--texto);border:1px solid var(--borde);transition:background .15s;}
.btn-secundario:hover{background:var(--fondo);}
.btn-secundario:disabled{opacity:.5;cursor:not-allowed;}
.btn-eliminar{padding:10px 22px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;background:var(--rojo);color:#fff;border:none;transition:background .15s;display:flex;align-items:center;gap:8px;}
.btn-eliminar:hover:not(:disabled){background:#B91C1C;}
.btn-eliminar:disabled{opacity:.5;cursor:not-allowed;}
.btn-guardar{display:flex;align-items:center;gap:8px;padding:10px 22px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;background:var(--azul);color:#fff;border:none;transition:background .15s;}
.btn-guardar:hover:not(:disabled){background:var(--azul-hover);}
.btn-guardar:disabled{opacity:.65;cursor:not-allowed;}
.spinner-btn{display:inline-block;width:15px;height:15px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:girar .7s linear infinite;flex-shrink:0;}
.detalle-fila{display:flex;justify-content:space-between;align-items:center;padding:11px 0;border-bottom:1px solid var(--borde);font-size:.95rem;font-family:'Montserrat',sans-serif;}
.detalle-fila:last-child{border-bottom:none;}
.detalle-label{font-weight:600;color:var(--gris);}
.detalle-valor{font-weight:500;color:var(--texto);}
.detalle-materias-wrapper{margin-top:12px;}
.detalle-subtitulo{font-size:.88rem;font-weight:700;color:var(--azul);margin:0 0 8px;}
.detalle-materia{display:flex;align-items:center;gap:8px;font-size:.88rem;color:var(--texto);padding:5px 0;border-bottom:1px solid #F3F4F6;}
.mat-icono{width:16px;height:16px;stroke:var(--verde);flex-shrink:0;}
.confirmar-texto{font-size:.95rem;color:var(--texto);line-height:1.6;margin:0;font-family:'Montserrat',sans-serif;}
@keyframes girar{to{transform:rotate(360deg)}}
@media(max-width:1024px){.stats-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media(max-width:640px){.stats-grid{grid-template-columns:1fr;}.filters-bar{flex-direction:column;}.filter-select,.search-group{width:100%;flex:none;}}
</style>