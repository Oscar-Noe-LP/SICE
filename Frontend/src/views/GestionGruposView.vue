<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="grupos-page" @keydown="manejarTeclado" tabindex="-1" ref="paginaRef">

      <div class="breadcrumb">
        <router-link to="/servicios-escolares" class="breadcrumb-link">Servicios Escolares</router-link>
        <span class="sep">›</span>
        <span class="activo">Gestión de Grupos</span>
      </div>
      <h1 class="page-title">Gestión de Grupos</h1>

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

      <div class="filtros-card">
        <div class="filtros-label">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
          </svg>
          Filtrar:
        </div>

        <!-- Búsqueda principal: número de control -->
        <div class="busqueda-control-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" class="icono-lupa">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input
            type="text"
            placeholder="No. de Control del alumno (principal)..."
            v-model="busquedaControl"
            class="input-busqueda-control"
            ref="inputControlRef"
            @keyup.enter="aplicarFiltros"
          />
          <button v-if="busquedaControl" @click="busquedaControl = ''" class="btn-limpiar-busqueda">✕</button>
        </div>

        <!-- Búsqueda secundaria: materia o docente -->
        <div class="busqueda-secundaria-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" class="icono-lupa-gris">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input
            type="text"
            placeholder="Materia o docente..."
            v-model="busquedaGrupo"
            class="input-busqueda-secundaria"
            ref="inputBusquedaRef"
            @keyup.enter="aplicarFiltros"
          />
        </div>

        <!-- ── CORRECCIÓN: carreras cargadas desde API ── -->
        <select v-model="filtroCarrera" class="filtro-select" @change="aplicarFiltros">
          <option value="">Carrera</option>
          <option
            v-for="c in carreras"
            :key="c.id"
            :value="c.id"
          >
            {{ c.nombre }}
          </option>
        </select>

        <select v-model="filtroSemestre" class="filtro-select" @change="aplicarFiltros">
          <option value="">Semestre</option>
          <option v-for="n in 8" :key="n" :value="n">{{ n }}° Semestre</option>
        </select>

        <button class="btn-filtrar" @click="aplicarFiltros">Filtrar</button>
        <button class="btn-limpiar" @click="limpiarFiltros">Limpiar</button>
        <button class="btn-nuevo" @click="nuevoGrupo">+ Nuevo Grupo</button>
      </div>

      <div v-if="errorCarga" class="error-carga">
        {{ errorCarga }}
      </div>

      <div v-if="busquedaControlAplicada" class="control-aviso">
        Mostrando grupos donde está inscrito el alumno con número de control: <strong>{{ busquedaControlAplicada }}</strong>
      </div>

      <div class="table-container" :class="{ 'loading-state': cargando }">
        <div v-if="cargando" class="loading-overlay">
          <div class="loading-spinner"></div>
          <span>{{ mensajeCarga }}</span>
        </div>
        <table class="grupos-table">
          <thead>
            <tr>
              <th>Materia</th>
              <th>Docente</th>
              <th>Aula</th>
              <th>Horario</th>
              <th class="text-center">Capacidad</th>
              <th class="text-center">Inscritos</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(grupo, idx) in paginatedGrupos" :key="grupo.id" :class="{ 'fila-activa': filaActiva === idx }" @click="filaActiva = idx" :tabindex="0" @keydown.enter="editarGrupo(grupo)">
              <td>{{ grupo.materia }}</td>
              <td>{{ grupo.docente }}</td>
              <td>{{ grupo.aula }}</td>
              <td>
                <span v-if="grupo.horario && grupo.horario.dia" class="horario-badge">
                  {{ grupo.horario.dia }} {{ grupo.horario.horaInicio }} - {{ grupo.horario.horaFin }}
                </span>
                <span v-else class="sin-horario">Sin horario</span>
              </td>
              <td class="text-center">{{ grupo.capacidad }}</td>
              <td class="text-center">
                <span class="inscritos-badge" :class="{ lleno: grupo.inscritos >= grupo.capacidad }">
                  {{ grupo.inscritos }} / {{ grupo.capacidad }}
                </span>
              </td>
              <td class="actions">
                <!-- Ver -->
                <button class="btn-icono ver" @click="verDetalle(grupo)" title="Ver detalles">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
                <!-- Editar -->
                <button class="btn-icono editar" @click="editarGrupo(grupo)" title="Editar grupo">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <!-- Evaluaciones — mantiene texto porque es acción de navegación específica -->
                <button class="btn-accion evaluaciones" @click="irAEvaluaciones(grupo)" title="Evaluaciones">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                  </svg>
                  Evaluaciones
                </button>
                <!-- Calificaciones — mantiene texto porque es acción de navegación específica -->
                <button class="btn-accion calificaciones" @click="irACalificaciones(grupo)" title="Calificaciones">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                  Calificaciones
                </button>
                <!-- Eliminar -->
                <button class="btn-icono eliminar" @click="eliminarGrupo(grupo)" title="Eliminar grupo">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="pagination">
        <div class="pagination-left">
          Mostrando {{ gruposFiltrados.length }} de {{ grupos.length }} grupos disponibles
        </div>
        <div class="pagination-center">
          <button @click="prevPage" :disabled="currentPage === 1">‹</button>
          <button v-for="page in visiblePages" :key="page" @click="goToPage(page)" :class="{ active: page === currentPage }">{{ page }}</button>
          <button @click="nextPage" :disabled="currentPage === totalPages">›</button>
        </div>
      </div>

      <!-- Modal Nuevo / Editar -->
      <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
        <div class="modal-content">
          <div class="modal-header">
            <h3>{{ grupoEditar.id ? 'Editar Grupo' : 'Nuevo Grupo' }}</h3>
            <button @click="cerrarModal" class="close-btn">×</button>
          </div>
          <div class="modal-body">

            <!-- Fila 1: Materia y Docente -->
            <div class="form-row">
              <div class="form-group">
                <label>Materia <span class="obligatorio">*</span></label>
                <input v-model="grupoEditar.materia" type="text" class="modal-input">
              </div>
              <div class="form-group">
                <label>Docente <span class="obligatorio">*</span></label>
                <input v-model="grupoEditar.docente" type="text" class="modal-input">
              </div>
            </div>

            <!-- Fila 2: Aula y Carrera -->
            <div class="form-row">
              <div class="form-group">
                <label>Aula <span class="obligatorio">*</span></label>
                <input v-model="grupoEditar.aula" type="text" class="modal-input">
              </div>
              <div class="form-group">
                <label>Carrera <span class="obligatorio">*</span></label>
                <!-- ── CORRECCIÓN: carreras cargadas desde API ── -->
                <select v-model="grupoEditar.id_carrera" class="modal-select">
                  <option :value="null">Seleccionar</option>
                  <option
                    v-for="c in carreras"
                    :key="c.id"
                    :value="c.id"
                  >
                    {{ c.nombre }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Fila 3: Semestre y Capacidad -->
            <div class="form-row">
              <div class="form-group">
                <label>Semestre <span class="obligatorio">*</span></label>
                <select v-model="grupoEditar.semestre" class="modal-select">
                  <option v-for="n in 8" :key="n" :value="n">{{ n }}° Semestre</option>
                </select>
              </div>
              <div class="form-group">
                <label>Capacidad <span class="obligatorio">*</span></label>
                <input v-model="grupoEditar.capacidad" type="number" min="1" class="modal-input">
              </div>
            </div>

            <!-- Fila 4: Horario -->
            <div class="form-section-title">Horario</div>
            <div class="form-row horario-row">
              <div class="form-group">
                <label>Día <span class="obligatorio">*</span></label>
                <select v-model="grupoEditar.horario.dia" class="modal-select">
                  <option value="">Seleccionar</option>
                  <option value="Lunes">Lunes</option>
                  <option value="Martes">Martes</option>
                  <option value="Miércoles">Miércoles</option>
                  <option value="Jueves">Jueves</option>
                  <option value="Viernes">Viernes</option>
                  <option value="Sábado">Sábado</option>
                  <option value="Lunes y Miércoles">Lunes y Miércoles</option>
                  <option value="Martes y Jueves">Martes y Jueves</option>
                  <option value="Lunes, Miércoles y Viernes">Lunes, Miércoles y Viernes</option>
                </select>
              </div>
              <div class="form-group">
                <label>Hora inicio <span class="obligatorio">*</span></label>
                <input v-model="grupoEditar.horario.horaInicio" type="time" class="modal-input">
              </div>
              <div class="form-group">
                <label>Hora fin <span class="obligatorio">*</span></label>
                <input v-model="grupoEditar.horario.horaFin" type="time" class="modal-input">
              </div>
            </div>

          </div>
          <div class="modal-footer">
            <button class="btn-cancelar" @click="cerrarModal" :disabled="cargando">Cancelar</button>
            <button v-if="grupoEditar.id" class="btn-eliminar" @click="eliminarGrupoDesdeModal" :disabled="cargando">Eliminar</button>
            <button class="btn-guardar" @click="guardarGrupo" :disabled="cargando">
              <span v-if="cargando" class="spinner-btn"></span>
              {{ cargando ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Modal de confirmación de eliminación -->
      <div v-if="showModalEliminar" class="modal-overlay" @click.self="cancelarEliminar">
        <div class="modal-content modal-confirm">
          <div class="modal-header">
            <h3>Confirmar eliminación</h3>
            <button @click="cancelarEliminar" class="close-btn">×</button>
          </div>
          <div class="modal-body">
            <p class="confirm-texto">¿Está seguro de que desea eliminar el grupo de <strong>{{ grupoAEliminar?.materia }}</strong>?</p>
            <p class="confirm-sub">Esta acción no se puede deshacer.</p>
          </div>
          <div class="modal-footer">
            <button class="btn-cancelar" @click="cancelarEliminar">Cancelar</button>
            <button class="btn-eliminar" @click="confirmarEliminar">
              <span v-if="cargando" class="spinner-btn"></span>
              {{ cargando ? 'Eliminando...' : 'Eliminar' }}
            </button>
          </div>
        </div>
      </div>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
// ── CORRECCIÓN: importar useCatalogos ──
import { useCatalogos } from '@/composables/useCatalogos'

const API = `${import.meta.env.VITE_API_URL}/api`

const router = useRouter()

// ── CORRECCIÓN: obtener carreras y su función de carga ──
const { carreras, cargarCarreras } = useCatalogos()

const busquedaControl         = ref('')
const busquedaControlAplicada = ref('')
const busquedaGrupo           = ref('')
// ── CORRECCIÓN: filtroCarrera ahora guarda un ID (número) en vez de un string de nombre ──
const filtroCarrera           = ref('')
const filtroSemestre          = ref('')
const filasPorPagina          = ref(10)
const currentPage             = ref(1)
const cargando                = ref(false)
const mensajeCarga            = ref('')
const errorCarga              = ref('')

const filaActiva       = ref(-1)
const inputControlRef  = ref(null)
const inputBusquedaRef = ref(null)
const paginaRef        = ref(null)

// ── Notificación UI ──────────────────────────────────────────────────
const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

const simularCarga = (mensaje, fn, ms = 600) => {
  cargando.value = true
  mensajeCarga.value = mensaje
  setTimeout(() => {
    fn()
    cargando.value = false
    mensajeCarga.value = ''
  }, ms)
}

// ── Navegación por teclado ──────────────────────────────────────────
const manejarTeclado = (e) => {
  const tag = document.activeElement?.tagName
  const enCampo = ['INPUT', 'SELECT', 'TEXTAREA'].includes(tag)

  if (e.key === 'Escape') {
    if (showModal.value) cerrarModal()
    return
  }

  if (e.ctrlKey) {
    switch (e.key.toLowerCase()) {
      case 'm': e.preventDefault(); nuevoGrupo(); break
      case 'f': e.preventDefault(); nextTick(() => inputControlRef.value?.focus()); break
      case 'b': e.preventDefault(); nextTick(() => inputBusquedaRef.value?.focus()); break
      case 'l': e.preventDefault(); limpiarFiltros(); break
    }
    return
  }

  if (!enCampo && !showModal.value) {
    const total = paginatedGrupos.value.length
    if (e.key === 'ArrowDown')       { e.preventDefault(); filaActiva.value = Math.min(filaActiva.value + 1, total - 1) }
    else if (e.key === 'ArrowUp')    { e.preventDefault(); filaActiva.value = Math.max(filaActiva.value - 1, 0) }
    else if (e.key === 'ArrowRight') { e.preventDefault(); nextPage(); filaActiva.value = 0 }
    else if (e.key === 'ArrowLeft')  { e.preventDefault(); prevPage(); filaActiva.value = 0 }
  }
}

onMounted(() => {
  cargarGrupos()
  // ── CORRECCIÓN: cargar carreras desde la API al montar ──
  cargarCarreras()
  window.addEventListener('keydown', manejarTeclado)
  nextTick(() => paginaRef.value?.focus())
})
onUnmounted(() => {
  window.removeEventListener('keydown', manejarTeclado)
})

// ── Datos ──────────────────────────────────────────────────────────
const grupos = ref([])

const normalizarGrupo = (g) => ({
  id:         g.id_grupo || g.id,
  materia:    g.materia || '',
  docente:    g.docente || '',
  aula:       g.aula || '',
  capacidad:  g.capacidad || 30,
  inscritos:  g.inscritos ?? 0,
  carrera:    g.carrera || '',
  // ── CORRECCIÓN: mapear id_carrera desde el backend ──
  id_carrera: g.id_carrera ?? null,
  semestre:   g.semestre || 0,
  horario: {
    dia:        g.dia || g.horario?.dia || '',
    horaInicio: g.hora_inicio || g.horario?.horaInicio || '',
    horaFin:    g.hora_fin || g.horario?.horaFin || ''
  },
  alumnos: []
})

// Endpoint: GET /api/grupos
const cargarGrupos = async () => {
  cargando.value = true
  errorCarga.value = ''
  mensajeCarga.value = 'Cargando grupos...'
  try {
    const response = await fetch(`${API}/grupos`)
    if (!response.ok) throw new Error('Error del servidor')
    const data = await response.json()
    grupos.value = data.map(normalizarGrupo)
    console.log('✅ Grupos cargados:', grupos.value.length, 'registros')
  } catch (error) {
    console.error('❌ Error cargando grupos:', error)
    errorCarga.value = 'No se pudo cargar la lista de grupos. Verifica que el servidor esté activo.'
  } finally {
    cargando.value = false
    mensajeCarga.value = ''
  }
}

// ── Filtrado y paginación ──────────────────────────────────────────
const gruposFiltrados = computed(() => {
  return grupos.value.filter(g => {
    const coincideControl  = !busquedaControlAplicada.value ||
      g.alumnos.some(a => a.noControl === busquedaControlAplicada.value.trim())
    const coincideBusqueda = !busquedaGrupo.value ||
      g.materia.toLowerCase().includes(busquedaGrupo.value.toLowerCase()) ||
      g.docente.toLowerCase().includes(busquedaGrupo.value.toLowerCase())
    // ── CORRECCIÓN: comparar por id_carrera (ID numérico) en vez del nombre ──
    const coincideCarrera  = !filtroCarrera.value || g.id_carrera === filtroCarrera.value
    const coincideSemestre = !filtroSemestre.value || g.semestre === parseInt(filtroSemestre.value)
    return coincideControl && coincideBusqueda && coincideCarrera && coincideSemestre
  })
})

const totalPages      = computed(() => Math.ceil(gruposFiltrados.value.length / filasPorPagina.value) || 1)
const startIndex      = computed(() => (currentPage.value - 1) * filasPorPagina.value)
const paginatedGrupos = computed(() => gruposFiltrados.value.slice(startIndex.value, startIndex.value + filasPorPagina.value))
const visiblePages    = computed(() => {
  const pages = []
  for (let i = 1; i <= totalPages.value; i++) pages.push(i)
  return pages
})

const aplicarFiltros = () => {
  simularCarga('Aplicando filtros...', () => {
    busquedaControlAplicada.value = busquedaControl.value
    currentPage.value = 1
    filaActiva.value  = -1
  })
}
const limpiarFiltros = () => {
  simularCarga('Limpiando filtros...', () => {
    busquedaControl.value         = ''
    busquedaControlAplicada.value = ''
    busquedaGrupo.value           = ''
    filtroCarrera.value           = ''
    filtroSemestre.value          = ''
    currentPage.value             = 1
    filaActiva.value              = -1
  })
}

const prevPage  = () => { if (currentPage.value > 1) { currentPage.value--; filaActiva.value = -1 } }
const nextPage  = () => { if (currentPage.value < totalPages.value) { currentPage.value++; filaActiva.value = -1 } }
const goToPage  = (page) => { currentPage.value = page }

// ── Modales ────────────────────────────────────────────────────────
const showModal   = ref(false)
const grupoEditar = ref({})

const nuevoGrupo = () => {
  grupoEditar.value = {
    id: null, materia: '', docente: '', aula: '',
    capacidad: 30, inscritos: 0,
    // ── CORRECCIÓN: id_carrera en lugar de carrera como string ──
    id_carrera: null,
    semestre: 5,
    horario: { dia: '', horaInicio: '', horaFin: '' }
  }
  showModal.value = true
}

const editarGrupo = (grupo) => {
  grupoEditar.value = {
    ...grupo,
    // ── CORRECCIÓN: preservar id_carrera al editar ──
    id_carrera: grupo.id_carrera,
    horario: { ...grupo.horario }
  }
  showModal.value = true
}

const cerrarModal = () => {
  showModal.value = false
  grupoEditar.value = {}
}

// Endpoint: POST /api/grupos  |  PUT /api/grupos/{id}
const guardarGrupo = async () => {
  const esEdicion = !!grupoEditar.value.id
  cargando.value = true
  mensajeCarga.value = esEdicion ? 'Guardando cambios...' : 'Creando grupo...'
  try {
    // ── CORRECCIÓN: payload usa id_carrera (ID real del backend) ──
    const payload = {
      nombre_materia: grupoEditar.value.materia,
      nombre_docente: grupoEditar.value.docente,
      aula:           grupoEditar.value.aula,
      id_carrera:     grupoEditar.value.id_carrera,
      semestre:       grupoEditar.value.semestre,
      capacidad:      grupoEditar.value.capacidad,
      dia:            grupoEditar.value.horario.dia,
      hora_inicio:    grupoEditar.value.horario.horaInicio,
      hora_fin:       grupoEditar.value.horario.horaFin
    }
    const url    = esEdicion ? `${API}/grupos/${grupoEditar.value.id}` : `${API}/grupos`
    const method = esEdicion ? 'PUT' : 'POST'

    const response = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    })

    if (response.ok) {
      await cargarGrupos()
      cerrarModal()
      mostrarNotificacion(
        esEdicion ? 'Grupo actualizado correctamente.' : 'Grupo creado correctamente.',
        'exito'
      )
    } else {
      const data = await response.json().catch(() => ({}))
      const mensajeError = data.message || data.error || 'Error al guardar el grupo.'
      mostrarNotificacion(mensajeError, 'error')
      console.error('❌ Error guardando grupo:', data)
    }
  } catch (error) {
    console.error('❌ Error guardando grupo:', error)
    mostrarNotificacion('Ocurrió un error de conexión al guardar el grupo.', 'error')
  } finally {
    cargando.value     = false
    mensajeCarga.value = ''
  }
}

const showModalEliminar = ref(false)
const grupoAEliminar    = ref(null)

const eliminarGrupo = (grupo) => {
  grupoAEliminar.value    = grupo
  showModalEliminar.value = true
}

const eliminarGrupoDesdeModal = () => {
  grupoAEliminar.value    = { ...grupoEditar.value }
  cerrarModal()
  showModalEliminar.value = true
}

const cancelarEliminar = () => {
  showModalEliminar.value = false
  grupoAEliminar.value    = null
}

// Endpoint: DELETE /api/grupos/{id}
const confirmarEliminar = async () => {
  cargando.value     = true
  mensajeCarga.value = 'Eliminando grupo...'
  try {
    const response = await fetch(`${API}/grupos/${grupoAEliminar.value.id}`, {
      method:  'DELETE',
      headers: { 'Accept': 'application/json' }
    })

    let data = {}
    if (response.status !== 204) {
      data = await response.json().catch(() => ({}))
    }

    if (response.ok) {
      await cargarGrupos()
      showModalEliminar.value = false
      grupoAEliminar.value    = null
      mostrarNotificacion('Grupo eliminado correctamente.', 'exito')
    } else {
      const mensajeError = data.message || data.error || 'No se pudo eliminar el grupo.'
      mostrarNotificacion(mensajeError, 'error')
      console.error('❌ Error eliminando grupo:', data)
    }
  } catch (error) {
    console.error('❌ Error eliminando grupo:', error)
    mostrarNotificacion('Ocurrió un error de conexión al eliminar el grupo.', 'error')
  } finally {
    cargando.value     = false
    mensajeCarga.value = ''
  }
}

const verDetalle        = (grupo) => {}
const irAEvaluaciones   = (grupo) => router.push(`/evaluaciones/${grupo.id}`)
const irACalificaciones = (grupo) => router.push(`/calificaciones/${grupo.id}`)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.grupos-page { width: 100%; background: #F5F5F5; }

.page-title { color: #1A1A1A; font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem; }

/* ── Breadcrumb ── */
.breadcrumb {
  color: #6B7280; font-size: 0.875rem;
  margin-bottom: 0.6rem;
  display: flex; align-items: center; gap: 0.4rem;
}
.breadcrumb .sep { color: #E5E7EB; }
.breadcrumb .activo { color: #1B396A; font-weight: 600; }
.breadcrumb-link { color: #6B7280; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #1B396A; }

/* ── Barra de filtros ── */
.filtros-card {
  background: #FFFFFF;
  border-radius: 12px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  padding: 0.9rem 1.2rem;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex-wrap: wrap;
  margin-bottom: 1.5rem;
}
.filtros-label {
  display: flex; align-items: center; gap: 5px;
  font-size: 0.82rem; font-weight: 700; color: #6B7280; white-space: nowrap;
}
.filtros-label svg { stroke: #6B7280; }

.busqueda-control-wrap {
  display: flex; align-items: center; gap: 8px;
  background: #DBEAFE; border: 1.5px solid #1B396A;
  border-radius: 8px; padding: 0 12px;
  flex: 1; min-width: 200px;
}
.icono-lupa { stroke: #1B396A; flex-shrink: 0; }
.input-busqueda-control {
  border: none; background: transparent;
  padding: 9px 0; font-size: 0.875rem;
  font-family: inherit; outline: none; flex: 1; color: #1A1A1A;
}
.input-busqueda-control::placeholder { color: #9CA3AF; }
.btn-limpiar-busqueda {
  background: none; border: none; color: #6B7280;
  cursor: pointer; font-size: 0.9rem; padding: 2px; line-height: 1;
}

.busqueda-secundaria-wrap {
  display: flex; align-items: center; gap: 8px;
  background: #FFFFFF; border: 1px solid #E5E7EB;
  border-radius: 8px; padding: 0 12px;
  flex: 1; min-width: 180px;
}
.icono-lupa-gris { stroke: #6B7280; flex-shrink: 0; }
.input-busqueda-secundaria {
  border: none; background: transparent;
  padding: 9px 0; font-size: 0.875rem;
  font-family: inherit; outline: none; flex: 1; color: #1A1A1A;
}
.input-busqueda-secundaria::placeholder { color: #9CA3AF; }

.filtro-select {
  padding: 9px 10px; border: 1px solid #E5E7EB;
  border-radius: 8px; font-size: 0.82rem;
  font-family: inherit; color: #1A1A1A;
  background: #F5F5F5; outline: none;
}
.filtro-select:focus { border-color: #1B396A; }

.btn-filtrar { background: #1B396A; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.875rem; white-space: nowrap; transition: background 0.2s; }
.btn-filtrar:hover { background: #1D4ED8; }
.btn-limpiar { background: #F5F5F5; color: #1A1A1A; border: 1px solid #E5E7EB; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.875rem; white-space: nowrap; }
.btn-nuevo { background: #1B396A; color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.875rem; white-space: nowrap; transition: background 0.2s; }
.btn-nuevo:hover { background: #1D4ED8; }

.table-container { background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 25px rgba(0,0,0,0.07); border: 1px solid #E5E7EB; }

/* SE5 — filas más compactas */
.grupos-table {
  width: 100%;
  border-collapse: collapse;
}
.grupos-table th {
  background: #F5F5F5;
  padding: 9px 14px;
  text-align: left;
  font-weight: 600;
  font-size: 0.85rem;
  color: #1A1A1A;
  border-bottom: 1px solid #E5E7EB;
  white-space: nowrap;
}
.grupos-table td {
  padding: 7px 14px;
  border-bottom: 1px solid #E5E7EB;
  color: #1A1A1A;
  font-size: 0.9rem;
}

.horario-badge {
  display: inline-block;
  background: #EFF6FF;
  color: #1B396A;
  border: 1px solid #BFDBFE;
  border-radius: 6px;
  padding: 4px 10px;
  font-size: 0.85rem;
  font-weight: 500;
  white-space: nowrap;
}
.sin-horario {
  color: #9CA3AF;
  font-size: 0.85rem;
  font-style: italic;
}

.inscritos-badge { padding: 5px 14px; border-radius: 20px; font-size: 0.88rem; font-weight: 500; }
.inscritos-badge.lleno { background: #FEF3C7; color: #F59E0B; }

/* SE6 — botones solo icono */
.btn-icono {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 6px;
  cursor: pointer;
  border: 1px solid #E5E7EB;
  transition: background 0.15s, border-color 0.15s;
  flex-shrink: 0;
  background: none;
}
.btn-icono svg { width: 15px; height: 15px; }
.btn-icono.ver { background: #F5F5F5; }
.btn-icono.ver svg { stroke: #6B7280; }
.btn-icono.ver:hover { background: #E5E7EB; }
.btn-icono.editar { background: #1B396A; border-color: #1B396A; }
.btn-icono.editar svg { stroke: white; }
.btn-icono.editar:hover { background: #1D4ED8; border-color: #1D4ED8; }
.btn-icono.eliminar { background: #FEE2E2; border-color: #FECACA; }
.btn-icono.eliminar svg { stroke: #DC2626; }
.btn-icono.eliminar:hover { background: #DC2626; border-color: #DC2626; }
.btn-icono.eliminar:hover svg { stroke: white; }

/* SE6 — botones con texto (Evaluaciones y Calificaciones) */
.btn-accion {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 0.82rem;
  cursor: pointer;
  font-weight: 600;
  border: none;
  white-space: nowrap;
  font-family: inherit;
}
.btn-accion svg { width: 14px; height: 14px; flex-shrink: 0; }
.btn-accion.evaluaciones { background: #1B396A; color: white; }
.btn-accion.evaluaciones svg { stroke: white; }
.btn-accion.evaluaciones:hover { background: #1D4ED8; }
.btn-accion.calificaciones { background: #DBEAFE; color: #1B396A; }
.btn-accion.calificaciones svg { stroke: #1B396A; }
.btn-accion.calificaciones:hover { background: #BFDBFE; }

.pagination { margin-top: 2rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.95rem; color: #6B7280; }
.pagination-center button { padding: 6px 12px; border: 1px solid #E5E7EB; background: #FFFFFF; border-radius: 6px; cursor: pointer; }
.pagination-center .active { background: #1B396A; color: white; border-color: #1B396A; }

.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.modal-content { background: #FFFFFF; width: 580px; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.15); overflow: hidden; }
.modal-header { background: #1B396A; color: white; padding: 1.2rem 1.8rem; display: flex; justify-content: space-between; align-items: center; }
.modal-header h3 { margin: 0; font-size: 1.1rem; }
.close-btn { background: transparent; border: none; color: white; font-size: 1.6rem; cursor: pointer; line-height: 1; }
.modal-body { padding: 2rem 1.8rem; }

.form-section-title {
  font-size: 0.85rem;
  font-weight: 700;
  color: #6B7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #E5E7EB;
}

.form-row { display: flex; gap: 1.5rem; margin-bottom: 1.6rem; }
.horario-row { margin-bottom: 0; }
.form-group { flex: 1; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #1A1A1A; font-size: 0.95rem; }
.obligatorio { color: #DC2626; }
.modal-input, .modal-select { width: 100%; padding: 12px 16px; border: 1.5px solid #E5E7EB; border-radius: 10px; font-size: 1rem; background: #FFFFFF; box-sizing: border-box; }

.modal-footer { padding: 1.2rem 1.8rem; background: #F5F5F5; display: flex; gap: 12px; justify-content: flex-end; }
.btn-cancelar, .btn-eliminar, .btn-guardar { padding: 12px 28px; border-radius: 10px; font-weight: 600; cursor: pointer; }
.btn-cancelar { background: #FFFFFF; color: #1A1A1A; border: 1px solid #E5E7EB; }
.btn-eliminar { background: #DC2626; color: white; border: none; }
.btn-guardar { background: #1B396A; color: white; border: none; }

.control-aviso {
  background: #EFF6FF;
  border: 1px solid #BFDBFE;
  border-radius: 8px;
  padding: 10px 16px;
  margin-bottom: 1rem;
  font-size: 0.92rem;
  color: #1B396A;
}

.table-container { position: relative; }
.loading-state { pointer-events: none; opacity: 0.7; }

.loading-overlay {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(255,255,255,0.82);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  z-index: 10;
  border-radius: 12px;
  font-weight: 600;
  color: #1B396A;
  font-size: 0.95rem;
}

.loading-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #E5E7EB;
  border-top-color: #1B396A;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

.spinner-btn {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  margin-right: 6px;
  vertical-align: middle;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* ── Navegación por teclado ── */
.fila-activa {
  background: #EFF6FF !important;
  outline: 2px solid #1B396A;
  outline-offset: -2px;
}
.grupos-table tbody tr:focus {
  background: #EFF6FF;
  outline: 2px solid #1B396A;
  outline-offset: -2px;
}
.grupos-page:focus { outline: none; }

/* ── Modal de confirmación ── */
.modal-confirm { width: 440px; }
.confirm-texto { font-size: 1rem; color: #1A1A1A; margin: 0 0 8px; }
.confirm-sub { font-size: 0.88rem; color: #DC2626; margin: 0; }

/* ── Error de carga ── */
.error-carga {
  background: #FEF2F2; border: 1px solid #FECACA;
  color: #DC2626; border-radius: 8px;
  padding: 10px 16px; margin-bottom: 1rem;
  font-size: 0.9rem; font-weight: 500;
}

/* ── Notificación UI ── */
.notificacion-ui {
  display: flex; align-items: center; gap: 10px;
  padding: 12px 18px; border-radius: 10px;
  font-size: 0.93rem; font-weight: 500;
  margin-bottom: 1rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.notificacion-ui.exito { background: #DCFCE7; color: #16A34A; border: 1px solid #86EFAC; }
.notificacion-ui.error { background: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5; }
.notif-icono { width: 20px; height: 20px; flex-shrink: 0; }
.notif-fade-enter-active, .notif-fade-leave-active { transition: all 0.35s ease; }
.notif-fade-enter-from, .notif-fade-leave-to { opacity: 0; transform: translateY(-8px); }

.text-center { text-align: center; }

/* ══════════════════════════════════════
   RESPONSIVE — GestionGruposView
   Santiago Acosta — Modificaciones SICE
══════════════════════════════════════ */

/* ── Tablet (≤1024px) ── */
@media (max-width: 1024px) {
  .page-title { font-size: 1.5rem; }

  .filtros-card { gap: 0.5rem; padding: 0.8rem 1rem; }

  /* Búsquedas más angostas */
  .busqueda-control-wrap   { min-width: 160px; }
  .busqueda-secundaria-wrap { min-width: 140px; }

  /* Ocultar texto de botones de acción, solo icono */
  .btn-accion.evaluaciones span,
  .btn-accion.calificaciones span { display: none; }

  .btn-accion.evaluaciones,
  .btn-accion.calificaciones {
    width: 32px;
    height: 32px;
    padding: 0;
    justify-content: center;
  }

  /* Modal más angosto */
  .modal-content { width: 520px; }
}

/* ── Tablet pequeña / móvil grande (≤768px) ── */
@media (max-width: 768px) {
  .page-title { font-size: 1.35rem; margin-bottom: 0.4rem; }

  /* Filtros en grid 2 columnas */
  .filtros-card {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.6rem;
  }

  /* Etiqueta "Filtrar" ocupa todo el ancho */
  .filtros-label { grid-column: 1 / -1; }

  /* Búsqueda principal ocupa todo el ancho */
  .busqueda-control-wrap {
    grid-column: 1 / -1;
    min-width: unset;
  }

  /* Búsqueda secundaria ocupa todo el ancho */
  .busqueda-secundaria-wrap {
    grid-column: 1 / -1;
    min-width: unset;
  }

  /* Selects en 2 columnas */
  .filtro-select { width: 100%; }

  /* Botones en 2 columnas */
  .btn-filtrar,
  .btn-limpiar,
  .btn-nuevo { width: 100%; justify-content: center; }

  /* Tabla con scroll horizontal */
  .table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  /* Ocultar columnas Aula y Capacidad en tablet */
  .grupos-table th:nth-child(3),
  .grupos-table td:nth-child(3),
  .grupos-table th:nth-child(5),
  .grupos-table td:nth-child(5) {
    display: none;
  }

  /* Acciones solo iconos — ocultar texto */
  .btn-accion { gap: 0; padding: 0; width: 32px; height: 32px; justify-content: center; }
  .btn-accion.evaluaciones span,
  .btn-accion.calificaciones span { display: none; }

  /* Paginación compacta */
  .pagination {
    flex-direction: column;
    gap: 0.75rem;
    align-items: center;
  }

  /* Modal casi full width */
  .modal-content {
    width: 96%;
    max-width: 520px;
    margin: 0 0.75rem;
  }

  .modal-body { padding: 1.4rem 1.2rem; }
  .modal-footer { padding: 0.9rem 1.2rem; }

  /* Filas del modal en columna */
  .form-row {
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .horario-row { flex-direction: column; }
}

/* ── Móvil pequeño (≤480px) ── */
@media (max-width: 480px) {
  .page-title { font-size: 1.2rem; }
  .breadcrumb { font-size: 0.8rem; }

  /* Filtros en 1 columna */
  .filtros-card { grid-template-columns: 1fr; }
  .filtros-label { grid-column: 1; }

  /* Ocultar también columna Horario en móvil pequeño */
  .grupos-table th:nth-child(4),
  .grupos-table td:nth-child(4) {
    display: none;
  }

  .grupos-table th,
  .grupos-table td {
    padding: 6px 8px;
    font-size: 0.82rem;
  }

  /* Acciones en columna */
  .actions {
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: center;
  }

  /* Modal footer en columna */
  .modal-footer {
    flex-direction: column-reverse;
    gap: 0.5rem;
  }

  .modal-footer button {
    width: 100%;
    justify-content: center;
  }

  /* Inputs sin zoom iOS */
  .modal-input,
  .modal-select,
  .input-busqueda-control,
  .input-busqueda-secundaria {
    font-size: 16px !important;
  }

  .modal-confirm { width: 94%; }
}

</style>