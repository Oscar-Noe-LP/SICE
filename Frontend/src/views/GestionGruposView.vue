<!-- src/views/ServiciosEscolares/GestionGruposView.vue -->
<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="grupos-page" @keydown="manejarTeclado" tabindex="-1" ref="paginaRef">

      <!-- Breadcrumb -->
      <div class="breadcrumb">
        <router-link to="/servicios-escolares" class="breadcrumb-link">SERVICIOS ESCOLARES</router-link>
        <ChevronRight :size="12" class="sep-icon" />
        <span class="activo">GESTION DE GRUPOS</span>
      </div>

      <h1 class="page-title">GESTION DE GRUPOS</h1>

      <!-- Notificación -->
      <transition name="notif-fade">
        <div v-if="notificacion.visible" class="notificacion-ui" :class="notificacion.tipo">
          <CheckCircle v-if="notificacion.tipo === 'exito'" class="notif-icono" />
          <AlertCircle v-else class="notif-icono" />
          {{ notificacion.mensaje.toUpperCase() }}
        </div>
      </transition>

      <!-- Filtros -->
      <div class="filtros-card">
        <div class="filtros-fila-principal">
          <div class="busqueda-control-wrap">
            <Search :size="16" class="icono-lupa" />
            <input
              type="text"
              placeholder="NO. DE CONTROL DEL ALUMNO..."
              v-model="busquedaControl"
              class="input-busqueda-control"
              ref="inputControlRef"
              @keyup.enter="aplicarFiltros"
            />
            <button v-if="busquedaControl" @click="busquedaControl = ''" class="btn-limpiar-busqueda">
              <X :size="12" />
            </button>
          </div>

          <button class="btn-toggle-filtros" @click="mostrarFiltros = !mostrarFiltros">
            <Filter :size="15" />
            FILTROS
            <span class="filtros-contador" v-if="filtrosActivos > 0">{{ filtrosActivos }}</span>
            <ChevronRight :size="14" class="filtros-chevron" :class="{ abierto: mostrarFiltros }" />
          </button>

          <button class="btn-nuevo" @click="nuevoGrupo">
            <Plus :size="16" />
            NUEVO GRUPO
          </button>
        </div>

        <!-- Filtros expandidos -->
        <div v-if="mostrarFiltros" class="filtros-expandidos">
          <div class="busqueda-secundaria-wrap">
            <Search :size="16" class="icono-lupa-gris" />
            <input
              type="text"
              placeholder="MATERIA O DOCENTE..."
              v-model="busquedaGrupo"
              class="input-busqueda-secundaria"
              ref="inputBusquedaRef"
              @keyup.enter="aplicarFiltros"
            />
          </div>

          <select v-model="filtroCarrera" class="filtro-select" @change="aplicarFiltros">
            <option value="">TODAS LAS CARRERAS</option>
            <option v-for="c in carreras" :key="c.id" :value="c.id">{{ c.nombre.toUpperCase() }}</option>
          </select>

          <select v-model="filtroSemestre" class="filtro-select" @change="aplicarFiltros">
            <option value="">TODOS LOS SEMESTRES</option>
            <option v-for="n in 9" :key="n" :value="n">{{ n }}° SEMESTRE</option>
          </select>

          <button class="btn-filtrar" @click="aplicarFiltros">
            <Filter :size="14" />
            APLICAR
          </button>
          <button class="btn-limpiar" @click="limpiarFiltros">
            <X :size="14" />
            LIMPIAR
          </button>
        </div>
      </div>

      <!-- Aviso de filtro por alumno -->
      <div v-if="busquedaControlAplicada" class="control-aviso">
        <UserCheck :size="14" />
        MOSTRANDO GRUPOS DONDE ESTA INSCRITO EL ALUMNO: <strong>{{ busquedaControlAplicada }}</strong>
        <button class="btn-close-aviso" @click="limpiarFiltros">
          <X :size="12" />
        </button>
      </div>

      <!-- Aviso de filtro por carrera (drill-down) -->
      <div v-if="carreraFiltroNombre && !mostrarFiltros" class="carrera-aviso">
        <GraduationCap :size="14" />
        FILTRADO POR CARRERA: <strong>{{ carreraFiltroNombre.toUpperCase() }}</strong>
        <button class="btn-close-aviso" @click="limpiarFiltros">
          <X :size="12" />
        </button>
      </div>

      <!-- Error de carga -->
      <div v-if="errorCarga" class="error-carga">
        <AlertCircle :size="16" />
        {{ errorCarga }}
      </div>

      <!-- Tabla de grupos -->
      <div class="table-container" :class="{ 'loading-state': cargando }">
        <div v-if="cargando" class="loading-overlay">
          <div class="loading-spinner"></div>
          <span>{{ mensajeCarga.toUpperCase() }}</span>
        </div>

        <div v-if="!cargando && gruposFiltrados.length === 0" class="estado-vacio">
          <Inbox :size="48" class="vacio-icon" />
          <h3>SIN RESULTADOS</h3>
          <p>NO SE ENCONTRARON GRUPOS CON LOS CRITERIOS APLICADOS</p>
          <button class="btn-limpiar-vacio" @click="limpiarFiltros">LIMPIAR FILTROS</button>
        </div>

        <table v-else class="grupos-table">
          <thead>
            <tr>
              <th>MATERIA</th>
              <th>DOCENTE</th>
              <th>AULA</th>
              <th>HORARIO</th>
              <th class="text-center">CAPACIDAD</th>
              <th class="text-center">INSCRITOS</th>
              <th class="text-center">ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="(grupo, idx) in paginatedGrupos" 
              :key="grupo.id" 
              :class="{ 'fila-activa': filaActiva === idx }" 
              @click="filaActiva = idx" 
              :tabindex="0" 
              @keydown.enter="editarGrupo(grupo)"
            >
              <td class="celda-materia">
                <span class="link-detalle" @click.stop="irADetalleGrupo(grupo)">
                  {{ grupo.materia.toUpperCase() }}
                </span>
              </td>
              <td>{{ grupo.docente.toUpperCase() }}</td>
              <td>{{ grupo.aula.toUpperCase() }}</td>
              <td>
                <span v-if="grupo.horario?.dia" class="horario-badge">
                  {{ grupo.horario.dia.toUpperCase() }} {{ grupo.horario.horaInicio }} - {{ grupo.horario.horaFin }}
                </span>
                <span v-else class="sin-horario">SIN HORARIO</span>
              </td>
              <td class="text-center">{{ grupo.capacidad }}</td>
              <td class="text-center">
                <span class="inscritos-badge" :class="{ lleno: grupo.inscritos >= grupo.capacidad }">
                  {{ grupo.inscritos }} / {{ grupo.capacidad }}
                </span>
              </td>
              <td class="actions">
                <button class="btn-icono ver" @click.stop="verDetalle(grupo)" title="VER DETALLES">
                  <Eye :size="14" />
                </button>
                <button class="btn-icono editar" @click.stop="editarGrupo(grupo)" title="EDITAR">
                  <Pencil :size="14" />
                </button>
                <button class="btn-accion evaluaciones" @click.stop="irAEvaluaciones(grupo)" title="EVALUACIONES">
                  <ClipboardList :size="14" />
                  <span>EVALUACIONES</span>
                </button>
                <button class="btn-accion calificaciones" @click.stop="irACalificaciones(grupo)" title="CALIFICACIONES">
                  <BarChart3 :size="14" />
                  <span>CALIFICACIONES</span>
                </button>
                <button class="btn-icono eliminar" @click.stop="eliminarGrupo(grupo)" title="ELIMINAR">
                  <Trash2 :size="14" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="pagination">
        <div class="pagination-left">
          MOSTRANDO {{ gruposFiltrados.length }} DE {{ grupos.length }} GRUPOS
        </div>
        <div class="pagination-center">
          <button class="btn-pag" @click="prevPage" :disabled="currentPage === 1">
            <ChevronLeft :size="14" />
          </button>
          <button 
            v-for="page in visiblePages" 
            :key="page" 
            @click="goToPage(page)" 
            :class="{ active: page === currentPage }"
            class="btn-pag"
          >{{ page }}</button>
          <button class="btn-pag" @click="nextPage" :disabled="currentPage === totalPages">
            <ChevronRight :size="14" />
          </button>
        </div>
      </div>

      <!-- MODALES (actualizados con Lucide y mayúsculas) -->
      <!-- Modal Nuevo/Editar -->
      <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
        <div class="modal-content">
          <div class="modal-header">
            <h3>{{ grupoEditar.id ? 'EDITAR GRUPO' : 'NUEVO GRUPO' }}</h3>
            <button @click="cerrarModal" class="close-btn">
              <X :size="20" />
            </button>
          </div>
          <div class="modal-body">
            <div class="form-row">
              <div class="form-group">
                <label>MATERIA <span class="obligatorio">*</span></label>
                <input v-model="grupoEditar.materia" type="text" class="modal-input" placeholder="EJ. MATEMATICAS I">
              </div>
              <div class="form-group">
                <label>DOCENTE <span class="obligatorio">*</span></label>
                <input v-model="grupoEditar.docente" type="text" class="modal-input" placeholder="EJ. DR. JUAN PEREZ">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>AULA <span class="obligatorio">*</span></label>
                <input v-model="grupoEditar.aula" type="text" class="modal-input" placeholder="EJ. A-101">
              </div>
              <div class="form-group">
                <label>CARRERA <span class="obligatorio">*</span></label>
                <select v-model="grupoEditar.id_carrera" class="modal-select">
                  <option :value="null">SELECCIONAR</option>
                  <option v-for="c in carreras" :key="c.id" :value="c.id">
                    {{ c.nombre.toUpperCase() }}
                  </option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>SEMESTRE <span class="obligatorio">*</span></label>
                <select v-model="grupoEditar.semestre" class="modal-select">
                  <option v-for="n in 9" :key="n" :value="n">{{ n }}° SEMESTRE</option>
                </select>
              </div>
              <div class="form-group">
                <label>CAPACIDAD <span class="obligatorio">*</span></label>
                <input v-model="grupoEditar.capacidad" type="number" min="1" class="modal-input">
              </div>
            </div>

            <div class="form-section-title">HORARIO</div>
            <div class="form-row horario-row">
              <div class="form-group">
                <label>DIA <span class="obligatorio">*</span></label>
                <select v-model="grupoEditar.horario.dia" class="modal-select">
                  <option value="">SELECCIONAR</option>
                  <option value="Lunes">LUNES</option>
                  <option value="Martes">MARTES</option>
                  <option value="Miércoles">MIERCOLES</option>
                  <option value="Jueves">JUEVES</option>
                  <option value="Viernes">VIERNES</option>
                  <option value="Sábado">SABADO</option>
                  <option value="Lunes y Miércoles">LUNES Y MIERCOLES</option>
                  <option value="Martes y Jueves">MARTES Y JUEVES</option>
                </select>
              </div>
              <div class="form-group">
                <label>HORA INICIO <span class="obligatorio">*</span></label>
                <input v-model="grupoEditar.horario.horaInicio" type="time" class="modal-input">
              </div>
              <div class="form-group">
                <label>HORA FIN <span class="obligatorio">*</span></label>
                <input v-model="grupoEditar.horario.horaFin" type="time" class="modal-input">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-cancelar" @click="cerrarModal" :disabled="cargando">CANCELAR</button>
            <button v-if="grupoEditar.id" class="btn-eliminar" @click="eliminarGrupoDesdeModal" :disabled="cargando">ELIMINAR</button>
            <button class="btn-guardar" @click="guardarGrupo" :disabled="cargando">
              <Loader2 v-if="cargando" :size="16" class="spinner-animate" />
              {{ cargando ? 'GUARDANDO...' : 'GUARDAR' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Modal Confirmar Eliminación -->
      <div v-if="showModalEliminar" class="modal-overlay" @click.self="cancelarEliminar">
        <div class="modal-content modal-confirm">
          <div class="modal-header">
            <h3>CONFIRMAR ELIMINACION</h3>
            <button @click="cancelarEliminar" class="close-btn">
              <X :size="20" />
            </button>
          </div>
          <div class="modal-body confirmar-body">
            <AlertTriangle :size="40" class="confirmar-icono" />
            <p>¿ESTA SEGURO DE ELIMINAR EL GRUPO DE <strong>{{ grupoAEliminar?.materia?.toUpperCase() }}</strong>?</p>
            <p class="confirm-sub">ESTA ACCION NO SE PUEDE DESHACER</p>
          </div>
          <div class="modal-footer">
            <button class="btn-cancelar" @click="cancelarEliminar">CANCELAR</button>
            <button class="btn-eliminar" @click="confirmarEliminar">
              <Loader2 v-if="cargando" :size="16" class="spinner-animate" />
              {{ cargando ? 'ELIMINANDO...' : 'ELIMINAR' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Modal Ver Detalle -->
      <div v-if="showModalDetalle" class="modal-overlay" @click.self="cerrarDetalle">
        <div class="modal-content modal-confirm">
          <div class="modal-header">
            <h3>DETALLE DEL GRUPO</h3>
            <button @click="cerrarDetalle" class="close-btn">
              <X :size="20" />
            </button>
          </div>
          <div class="modal-body">
            <div class="detalle-fila">
              <span class="detalle-etiqueta">MATERIA</span>
              <span class="detalle-valor">{{ grupoDetalle?.materia?.toUpperCase() }}</span>
            </div>
            <div class="detalle-fila">
              <span class="detalle-etiqueta">DOCENTE</span>
              <span class="detalle-valor">{{ grupoDetalle?.docente?.toUpperCase() }}</span>
            </div>
            <div class="detalle-fila">
              <span class="detalle-etiqueta">AULA</span>
              <span class="detalle-valor">{{ grupoDetalle?.aula?.toUpperCase() }}</span>
            </div>
            <div class="detalle-fila">
              <span class="detalle-etiqueta">CARRERA</span>
              <span class="detalle-valor">{{ obtenerNombreCarrera(grupoDetalle?.id_carrera)?.toUpperCase() }}</span>
            </div>
            <div class="detalle-fila">
              <span class="detalle-etiqueta">SEMESTRE</span>
              <span class="detalle-valor">{{ grupoDetalle?.semestre }}° SEMESTRE</span>
            </div>
            <div class="detalle-fila">
              <span class="detalle-etiqueta">CAPACIDAD</span>
              <span class="detalle-valor">{{ grupoDetalle?.inscritos }} / {{ grupoDetalle?.capacidad }} INSCRITOS</span>
            </div>
            <div class="detalle-fila">
              <span class="detalle-etiqueta">HORARIO</span>
              <span class="detalle-valor">
                {{ (grupoDetalle?.horario?.dia || 'SIN DIA').toUpperCase() }}
                {{ grupoDetalle?.horario?.horaInicio }} - {{ grupoDetalle?.horario?.horaFin }}
              </span>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-cancelar" @click="cerrarDetalle">CERRAR</button>
            <button class="btn-guardar" @click="editarGrupo(grupoDetalle); cerrarDetalle()">EDITAR</button>
          </div>
        </div>
      </div>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import { useCatalogos } from '@/composables/useCatalogos'

// Lucide Icons
import {
  ChevronRight,
  ChevronLeft,
  Search,
  X,
  Filter,
  Plus,
  Eye,
  Pencil,
  Trash2,
  CheckCircle,
  AlertCircle,
  AlertTriangle,
  Loader2,
  Inbox,
  UserCheck,
  GraduationCap,
  ClipboardList,
  BarChart3
} from 'lucide-vue-next'

const API = `${import.meta.env.VITE_API_URL}/api`
const router = useRouter()
const route = useRoute()
const { carreras, cargarCarreras } = useCatalogos()

// ── Estado ──
const mostrarFiltros = ref(false)
const busquedaControl = ref('')
const busquedaControlAplicada = ref('')
const busquedaGrupo = ref('')
const filtroCarrera = ref('')
const filtroSemestre = ref('')
const filasPorPagina = ref(10)
const currentPage = ref(1)
const cargando = ref(false)
const mensajeCarga = ref('')
const errorCarga = ref('')
const carreraFiltroNombre = ref('')

const filaActiva = ref(-1)
const inputControlRef = ref(null)
const inputBusquedaRef = ref(null)
const paginaRef = ref(null)

// ── Computed ──
const filtrosActivos = computed(() =>
  [busquedaGrupo.value, filtroCarrera.value, filtroSemestre.value]
    .filter(v => v !== '' && v !== null && v !== undefined).length
)

// ── Notificaciones ──
const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje: mensaje.toUpperCase(), tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

// ── Datos ──
const grupos = ref([])

const normalizarGrupo = (g) => ({
  id: g.id_grupo || g.id,
  materia: g.materia || g.nombre_materia || '',
  docente: g.docente || g.nombre_docente || '',
  aula: g.aula || '',
  capacidad: g.capacidad || 30,
  inscritos: g.inscritos ?? 0,
  carrera: g.carrera || '',
  id_carrera: g.id_carrera ?? null,
  semestre: g.semestre || 0,
  horario: {
    dia: g.dia || g.horario?.dia || '',
    horaInicio: g.hora_inicio || g.horario?.horaInicio || '',
    horaFin: g.hora_fin || g.horario?.horaFin || ''
  },
  alumnos: g.alumnos || []
})

// ── Cargar grupos ──
const cargarGrupos = async () => {
  cargando.value = true
  errorCarga.value = ''
  mensajeCarga.value = 'CARGANDO GRUPOS...'
  try {
    const response = await fetch(`${API}/grupos`)
    if (!response.ok) throw new Error('Error del servidor')
    const data = await response.json()
    grupos.value = data.map(normalizarGrupo)
  } catch (error) {
    console.error('Error cargando grupos:', error)
    errorCarga.value = 'NO SE PUDO CARGAR LA LISTA DE GRUPOS'
  } finally {
    cargando.value = false
    mensajeCarga.value = ''
  }
}

// ── Obtener nombre de carrera ──
const obtenerNombreCarrera = (id) => {
  const carrera = carreras.value.find(c => c.id === id)
  return carrera?.nombre || 'SIN CARRERA'
}

// ── Navegación a detalle de grupo ──
const irADetalleGrupo = (grupo) => {
  router.push(`/servicios-escolares/grupos/${grupo.id}`)
}

// ── Filtrado y paginación ──
const gruposFiltrados = computed(() => {
  return grupos.value.filter(g => {
    const coincideControl = !busquedaControlAplicada.value ||
      g.alumnos.some(a => a.noControl === busquedaControlAplicada.value.trim())
    const coincideBusqueda = !busquedaGrupo.value ||
      g.materia.toLowerCase().includes(busquedaGrupo.value.toLowerCase()) ||
      g.docente.toLowerCase().includes(busquedaGrupo.value.toLowerCase())
    const coincideCarrera = !filtroCarrera.value || g.id_carrera === filtroCarrera.value
    const coincideSemestre = !filtroSemestre.value || g.semestre === parseInt(filtroSemestre.value)
    return coincideControl && coincideBusqueda && coincideCarrera && coincideSemestre
  })
})

const totalPages = computed(() => Math.ceil(gruposFiltrados.value.length / filasPorPagina.value) || 1)
const startIndex = computed(() => (currentPage.value - 1) * filasPorPagina.value)
const paginatedGrupos = computed(() => gruposFiltrados.value.slice(startIndex.value, startIndex.value + filasPorPagina.value))
const visiblePages = computed(() => {
  const pages = []
  for (let i = 1; i <= totalPages.value; i++) pages.push(i)
  return pages
})

// ── Aplicar filtros (con lectura de query param) ──
const aplicarFiltros = () => {
  simularCarga('APLICANDO FILTROS...', () => {
    busquedaControlAplicada.value = busquedaControl.value
    currentPage.value = 1
    filaActiva.value = -1
  })
}

const limpiarFiltros = () => {
  simularCarga('LIMPIANDO FILTROS...', () => {
    busquedaControl.value = ''
    busquedaControlAplicada.value = ''
    busquedaGrupo.value = ''
    filtroCarrera.value = ''
    filtroSemestre.value = ''
    carreraFiltroNombre.value = ''
    currentPage.value = 1
    filaActiva.value = -1
    mostrarFiltros.value = false
    
    // Limpiar query param de la URL
    router.replace({ query: {} })
  })
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

const prevPage = () => { if (currentPage.value > 1) { currentPage.value--; filaActiva.value = -1 } }
const nextPage = () => { if (currentPage.value < totalPages.value) { currentPage.value++; filaActiva.value = -1 } }
const goToPage = (page) => { currentPage.value = page; filaActiva.value = -1 }

// ── Modales ──
const showModal = ref(false)
const grupoEditar = ref({})

const nuevoGrupo = () => {
  grupoEditar.value = {
    id: null, materia: '', docente: '', aula: '',
    capacidad: 30, inscritos: 0,
    id_carrera: null,
    semestre: 5,
    horario: { dia: '', horaInicio: '', horaFin: '' }
  }
  showModal.value = true
}

const editarGrupo = (grupo) => {
  grupoEditar.value = {
    ...grupo,
    id_carrera: grupo.id_carrera,
    horario: { ...grupo.horario }
  }
  showModal.value = true
}

const cerrarModal = () => {
  showModal.value = false
  grupoEditar.value = {}
}

// ── Guardar grupo ──
const guardarGrupo = async () => {
  const esEdicion = !!grupoEditar.value.id
  cargando.value = true
  mensajeCarga.value = esEdicion ? 'GUARDANDO CAMBIOS...' : 'CREANDO GRUPO...'
  try {
    const payload = {
      nombre_materia: grupoEditar.value.materia,
      nombre_docente: grupoEditar.value.docente,
      aula: grupoEditar.value.aula,
      id_carrera: grupoEditar.value.id_carrera,
      semestre: grupoEditar.value.semestre,
      capacidad: grupoEditar.value.capacidad,
      dia: grupoEditar.value.horario.dia,
      hora_inicio: grupoEditar.value.horario.horaInicio,
      hora_fin: grupoEditar.value.horario.horaFin
    }
    const url = esEdicion ? `${API}/grupos/${grupoEditar.value.id}` : `${API}/grupos`
    const method = esEdicion ? 'PUT' : 'POST'

    const response = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    })

    if (response.ok) {
      await cargarGrupos()
      cerrarModal()
      mostrarNotificacion(esEdicion ? 'GRUPO ACTUALIZADO CORRECTAMENTE.' : 'GRUPO CREADO CORRECTAMENTE.')
    } else {
      const data = await response.json().catch(() => ({}))
      mostrarNotificacion(data.message || data.error || 'ERROR AL GUARDAR EL GRUPO.', 'error')
    }
  } catch (error) {
    mostrarNotificacion('ERROR DE CONEXION AL GUARDAR EL GRUPO.', 'error')
  } finally {
    cargando.value = false
    mensajeCarga.value = ''
  }
}

// ── Eliminar grupo ──
const showModalEliminar = ref(false)
const grupoAEliminar = ref(null)

const eliminarGrupo = (grupo) => {
  grupoAEliminar.value = grupo
  showModalEliminar.value = true
}

const eliminarGrupoDesdeModal = () => {
  grupoAEliminar.value = { ...grupoEditar.value }
  cerrarModal()
  showModalEliminar.value = true
}

const cancelarEliminar = () => {
  showModalEliminar.value = false
  grupoAEliminar.value = null
}

const confirmarEliminar = async () => {
  cargando.value = true
  mensajeCarga.value = 'ELIMINANDO GRUPO...'
  try {
    const response = await fetch(`${API}/grupos/${grupoAEliminar.value.id}`, {
      method: 'DELETE',
      headers: { 'Accept': 'application/json' }
    })

    if (response.ok) {
      await cargarGrupos()
      showModalEliminar.value = false
      grupoAEliminar.value = null
      mostrarNotificacion('GRUPO ELIMINADO CORRECTAMENTE.')
    } else {
      const data = await response.json().catch(() => ({}))
      mostrarNotificacion(data.message || data.error || 'NO SE PUDO ELIMINAR EL GRUPO.', 'error')
    }
  } catch (error) {
    mostrarNotificacion('ERROR DE CONEXION AL ELIMINAR EL GRUPO.', 'error')
  } finally {
    cargando.value = false
    mensajeCarga.value = ''
  }
}

// ── Detalle ──
const showModalDetalle = ref(false)
const grupoDetalle = ref(null)

const verDetalle = (grupo) => {
  grupoDetalle.value = grupo
  showModalDetalle.value = true
}

const cerrarDetalle = () => {
  showModalDetalle.value = false
  grupoDetalle.value = null
}

const irAEvaluaciones = (grupo) => router.push(`/evaluaciones/${grupo.id}`)
const irACalificaciones = (grupo) => router.push(`/calificaciones/${grupo.id}`)

// ── Navegación por teclado ──
const manejarTeclado = (e) => {
  const tag = document.activeElement?.tagName
  const enCampo = ['INPUT', 'SELECT', 'TEXTAREA'].includes(tag)

  if (e.key === 'Escape') {
    if (showModal.value) cerrarModal()
    return
  }

  if (e.ctrlKey) {
    switch (e.key.toLowerCase()) {
      case 'n': e.preventDefault(); nuevoGrupo(); break
      case 'f': e.preventDefault(); nextTick(() => inputControlRef.value?.focus()); break
      case 'l': e.preventDefault(); limpiarFiltros(); break
    }
    return
  }

  if (!enCampo && !showModal.value) {
    const total = paginatedGrupos.value.length
    if (e.key === 'ArrowDown') { e.preventDefault(); filaActiva.value = Math.min(filaActiva.value + 1, total - 1) }
    else if (e.key === 'ArrowUp') { e.preventDefault(); filaActiva.value = Math.max(filaActiva.value - 1, 0) }
    else if (e.key === 'ArrowRight') { e.preventDefault(); nextPage(); filaActiva.value = 0 }
    else if (e.key === 'ArrowLeft') { e.preventDefault(); prevPage(); filaActiva.value = 0 }
  }
}

// ── Leer query param al montar ──
const leerQueryParams = () => {
  const carreraId = route.query.carrera
  if (carreraId) {
    filtroCarrera.value = carreraId
    const carrera = carreras.value.find(c => c.id === parseInt(carreraId))
    if (carrera) {
      carreraFiltroNombre.value = carrera.nombre
    }
    aplicarFiltros()
  }
}

// ── Lifecycle ──
onMounted(async () => {
  await cargarGrupos()
  await cargarCarreras()
  leerQueryParams()
  window.addEventListener('keydown', manejarTeclado)
  nextTick(() => paginaRef.value?.focus())
})

onUnmounted(() => {
  window.removeEventListener('keydown', manejarTeclado)
})
</script>

<style scoped>
/* Mantener estilos existentes pero actualizar a mayúsculas y espacios reducidos */
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap');

.grupos-page {
  width: 100%;
  background: #F5F5F5;
  font-family: 'Montserrat', sans-serif;
}

.page-title {
  color: #111827;
  font-size: 1.5rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  margin-bottom: 0.5rem;
}

/* Breadcrumb */
.breadcrumb {
  color: #6B7280;
  font-size: 0.7rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}
.breadcrumb .activo { color: #1B396A; }
.breadcrumb-link { color: #6B7280; text-decoration: none; letter-spacing: 0.05em; }
.breadcrumb-link:hover { color: #1B396A; }
.sep-icon { stroke: #9CA3AF; }

/* Filtros card - más compacto */
.filtros-card {
  background: white;
  border-radius: 12px;
  border: 1px solid #E5E7EB;
  padding: 0.75rem 1rem;
  margin-bottom: 1rem;
}

.filtros-fila-principal {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.busqueda-control-wrap {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #DBEAFE;
  border: 1px solid #1B396A;
  border-radius: 8px;
  padding: 0 10px;
  flex: 1;
  min-width: 200px;
}
.input-busqueda-control {
  border: none;
  background: transparent;
  padding: 7px 0;
  font-size: 0.75rem;
  font-weight: 500;
  font-family: inherit;
  outline: none;
  flex: 1;
}

.btn-toggle-filtros {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 7px 12px;
  background: #F5F5F5;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  font-size: 0.7rem;
  font-weight: 700;
  cursor: pointer;
}
.btn-nuevo {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #1B396A;
  color: white;
  border: none;
  padding: 7px 14px;
  border-radius: 8px;
  font-size: 0.7rem;
  font-weight: 700;
  cursor: pointer;
}

/* Avisos */
.control-aviso, .carrera-aviso {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #EFF6FF;
  border: 1px solid #BFDBFE;
  border-radius: 8px;
  padding: 6px 12px;
  margin-bottom: 0.75rem;
  font-size: 0.7rem;
  font-weight: 600;
  color: #1B396A;
}
.btn-close-aviso {
  background: none;
  border: none;
  cursor: pointer;
  margin-left: auto;
  display: flex;
  align-items: center;
  color: #6B7280;
}

/* Tabla */
.table-container {
  background: white;
  border-radius: 12px;
  overflow-x: auto;
  border: 1px solid #E5E7EB;
}
.grupos-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.7rem;
}
.grupos-table th {
  background: #F9FAFB;
  padding: 10px 12px;
  text-align: left;
  font-weight: 700;
  color: #111827;
  border-bottom: 1px solid #E5E7EB;
  letter-spacing: 0.05em;
}
.grupos-table td {
  padding: 8px 12px;
  border-bottom: 1px solid #E5E7EB;
}
.celda-materia .link-detalle {
  color: #1B396A;
  cursor: pointer;
  font-weight: 700;
}
.celda-materia .link-detalle:hover { text-decoration: underline; }

.horario-badge {
  display: inline-block;
  background: #EFF6FF;
  color: #1B396A;
  border: 1px solid #BFDBFE;
  border-radius: 6px;
  padding: 3px 8px;
  font-size: 0.65rem;
  font-weight: 600;
}
.sin-horario { color: #9CA3AF; font-style: italic; }

.inscritos-badge {
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 700;
}
.inscritos-badge.lleno { background: #FEF3C7; color: #D97706; }

/* Botones de acción */
.actions { display: flex; gap: 4px; flex-wrap: wrap; justify-content: center; }
.btn-icono {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  cursor: pointer;
  border: none;
  background: #F3F4F6;
}
.btn-icono.ver:hover { background: #DBEAFE; }
.btn-icono.editar { background: #1B396A; }
.btn-icono.editar svg { stroke: white; }
.btn-icono.editar:hover { background: #1D4ED8; }
.btn-icono.eliminar { background: #FEE2E2; }
.btn-icono.eliminar svg { stroke: #DC2626; }
.btn-icono.eliminar:hover { background: #DC2626; }
.btn-icono.eliminar:hover svg { stroke: white; }

.btn-accion {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 5px 10px;
  border-radius: 6px;
  font-size: 0.65rem;
  font-weight: 700;
  cursor: pointer;
  border: none;
}
.btn-accion.evaluaciones { background: #1B396A; color: white; }
.btn-accion.evaluaciones svg { stroke: white; }
.btn-accion.calificaciones { background: #DBEAFE; color: #1B396A; }
.btn-accion.calificaciones svg { stroke: #1B396A; }

/* Paginación */
.pagination {
  margin-top: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.7rem;
  font-weight: 600;
  color: #6B7280;
}
.btn-pag {
  padding: 4px 8px;
  border: 1px solid #E5E7EB;
  background: white;
  border-radius: 4px;
  cursor: pointer;
}
.btn-pag.active { background: #1B396A; color: white; border-color: #1B396A; }

/* Estados vacíos */
.estado-vacio { text-align: center; padding: 2rem; color: #6B7280; }
.vacio-icon { stroke: #9CA3AF; margin-bottom: 0.5rem; }

/* Modales */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}
.modal-content {
  background: white;
  width: 560px;
  max-width: 90%;
  max-height: 85vh;
  border-radius: 16px;
  overflow: hidden;
}
.modal-header {
  background: #1B396A;
  color: white;
  padding: 0.8rem 1.2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.modal-header h3 { margin: 0; font-size: 0.9rem; font-weight: 700; letter-spacing: 0.05em; }
.close-btn { background: none; border: none; color: white; cursor: pointer; display: flex; }
.modal-body { padding: 1.2rem; overflow-y: auto; }
.modal-footer {
  padding: 0.8rem 1.2rem;
  background: #F9FAFB;
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  border-top: 1px solid #E5E7EB;
}
.form-row { display: flex; gap: 1rem; margin-bottom: 1rem; }
.form-group { flex: 1; }
.form-group label { display: block; margin-bottom: 4px; font-size: 0.7rem; font-weight: 700; color: #374151; letter-spacing: 0.05em; }
.modal-input, .modal-select {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  font-size: 0.75rem;
  font-family: inherit;
}
.form-section-title {
  font-size: 0.7rem;
  font-weight: 800;
  color: #6B7280;
  letter-spacing: 0.08em;
  margin-bottom: 0.75rem;
  padding-bottom: 0.4rem;
  border-bottom: 1px solid #E5E7EB;
}
.btn-cancelar, .btn-eliminar, .btn-guardar {
  padding: 8px 20px;
  border-radius: 8px;
  font-size: 0.7rem;
  font-weight: 700;
  cursor: pointer;
}
.btn-cancelar { background: white; border: 1px solid #E5E7EB; }
.btn-eliminar { background: #DC2626; color: white; border: none; }
.btn-guardar { background: #1B396A; color: white; border: none; }
.detalle-fila {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #E5E7EB;
}
.detalle-etiqueta { font-size: 0.65rem; font-weight: 700; color: #6B7280; letter-spacing: 0.05em; }
.detalle-valor { font-size: 0.75rem; font-weight: 600; color: #111827; }
.confirmar-body { text-align: center; }
.confirmar-icono { stroke: #F59E0B; margin-bottom: 0.5rem; }
.confirm-sub { color: #DC2626; font-size: 0.7rem; }

/* Responsive */
@media (max-width: 768px) {
  .form-row { flex-direction: column; gap: 0.75rem; }
  .actions { flex-wrap: wrap; }
  .btn-accion span { display: none; }
  .btn-accion { padding: 5px; }
}
</style>