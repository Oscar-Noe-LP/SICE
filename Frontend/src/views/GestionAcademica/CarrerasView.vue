<!-- src/views/GestionAcademica/CarrerasView.vue -->
<template>
  <MainLayout>
    <div class="carreras-page">

      <!-- Breadcrumb -->
      <nav class="breadcrumb">
        <span class="breadcrumb-link" @click="$router.push('/gestion-academica')">GESTION ACADEMICA</span>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-actual">CARRERAS</span>
      </nav>

      <!-- Encabezado -->
      <div class="page-header">
        <h1 class="page-title">CARRERAS</h1>
        <span class="page-subtitle">{{ carrerasFiltradas.length }} REGISTRO(S) ENCONTRADO(S)</span>
      </div>

      <!-- Barra de carga -->
      <div class="barra-carga" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- Toast -->
      <transition name="toast-slide">
        <div v-if="notificacion.visible" class="toast" :class="notificacion.tipo">
          <CheckCircle v-if="notificacion.tipo === 'exito'" class="toast-icono" />
          <AlertCircle v-else class="toast-icono" />
          {{ notificacion.mensaje.toUpperCase() }}
        </div>
      </transition>

      <!-- Barra de acciones -->
      <div class="actions-bar">
        <div class="search-group">
          <Search class="search-icon-svg" />
          <input
            type="text"
            placeholder="BUSCAR POR NOMBRE DE CARRERA..."
            v-model="busquedaLocal"
            @keydown.escape="busquedaLocal = ''"
            class="search-input"
          >
        </div>

        <button
          class="btn-filtros"
          @click="mostrarFiltros = !mostrarFiltros"
          :class="{ activo: filtrosActivos }"
        >
          <Filter class="btn-icon-svg" />
          FILTROS
          <span v-if="filtrosActivos" class="filtros-badge">{{ contadorFiltros }}</span>
        </button>

        <button class="btn-nuevo" @click="abrirModalNuevo">
          <Plus class="btn-icon-svg" />
          NUEVA CARRERA
        </button>

        <!-- Toggle vista -->
        <div class="view-toggle">
          <button 
            class="toggle-btn" 
            :class="{ activo: vistaActual === 'cards' }"
            @click="vistaActual = 'cards'"
            title="VISTA TARJETAS"
          >
            <LayoutGrid class="toggle-icon" />
          </button>
          <button 
            class="toggle-btn" 
            :class="{ activo: vistaActual === 'tabla' }"
            @click="vistaActual = 'tabla'"
            title="VISTA TABLA"
          >
            <List class="toggle-icon" />
          </button>
        </div>
      </div>

      <!-- Panel de filtros secundarios -->
      <transition name="filtros-slide">
        <div v-if="mostrarFiltros" class="filtros-panel">
          <div class="filtros-grid">
            <div class="filtro-item">
              <label class="filtro-label">DEPARTAMENTO</label>
              <select v-model="filtroDepartamento" class="filter-select">
                <option value="">TODOS</option>
                <option v-for="dep in departamentos" :key="dep.id_departamento" :value="dep.id_departamento">
                  {{ dep.nombre.toUpperCase() }}
                </option>
              </select>
            </div>
            <div class="filtro-item">
              <label class="filtro-label">NIVEL</label>
              <select v-model="filtroNivel" class="filter-select">
                <option value="">TODOS</option>
                <option v-for="niv in niveles" :key="niv.id_nivel" :value="niv.id_nivel">
                  {{ niv.nombre_nivel.toUpperCase() }}
                </option>
              </select>
            </div>
            <div class="filtro-item">
              <label class="filtro-label">ESTATUS</label>
              <select v-model="filtroEstatus" class="filter-select">
                <option value="">TODOS</option>
                <option value="1">ACTIVO</option>
                <option value="0">INACTIVO</option>
              </select>
            </div>
          </div>
          <div class="filtros-footer">
            <button class="btn-limpiar" @click="limpiarFiltros">
              <X class="reset-icon" />
              LIMPIAR FILTROS
            </button>
          </div>
        </div>
      </transition>

      <!-- ══════════════════════════════════════════════════
           VISTA DE TARJETAS (DRILL-DOWN)
      ══════════════════════════════════════════════════ -->
      <div v-if="vistaActual === 'cards'">
        <div v-if="cargando && carrerasFiltradas.length === 0" class="cards-grid skeleton">
          <CareerCardDrill
            v-for="i in 6"
            :key="`sk-${i}`"
            :career="skeletonCareer"
            class="skeleton-card"
          />
        </div>

        <div v-else class="cards-grid">
          <CareerCardDrill
            v-for="carrera in carrerasPaginadasCards"
            :key="carrera.id_carrera"
            :career="mapToDrillFormat(carrera)"
            :active="carreraSeleccionada?.id_carrera === carrera.id_carrera"
            @click="seleccionarCarrera(carrera)"
          />
        </div>

        <!-- Expansión de grupos -->
        <Transition name="expand-grupos">
          <div v-if="carreraSeleccionada && gruposCarrera.length > 0" class="grupos-expandidos">
            <div class="grupos-header">
              <div class="grupos-header-info">
                <span class="grupos-header-tag">GRUPOS ASOCIADOS</span>
                <h3 class="grupos-header-title">{{ carreraSeleccionada.nombre.toUpperCase() }}</h3>
              </div>
              <button class="btn-cerrar-grupos" @click="carreraSeleccionada = null">
                <X class="close-icon" />
              </button>
            </div>
            
            <div v-if="cargandoGrupos" class="grupos-loading">
              <div class="loading-spinner-small"></div>
              <span>CARGANDO GRUPOS...</span>
            </div>

            <div v-else class="grupos-grid-cards">
              <div
                v-for="grupo in gruposCarrera"
                :key="grupo.id_grupo"
                class="grupo-card-small"
                @click="irADetalleGrupo(grupo)"
              >
                <div class="grupo-card-small-header">
                  <span class="grupo-chip-semestre">{{ grupo.semestre }}° SEM.</span>
                  <span class="grupo-chip-turno" :class="`turno-${(grupo.turno || 'MATUTINO').toLowerCase()}`">
                    {{ (grupo.turno || 'MATUTINO').toUpperCase() }}
                  </span>
                </div>
                <h4 class="grupo-card-small-nombre">{{ (grupo.nombre_grupo || `GRUPO ${grupo.id_grupo}`).toUpperCase() }}</h4>
                <p class="grupo-card-small-docente">
                  <User class="small-icon" />
                  {{ (grupo.docente_nombre || 'SIN ASIGNAR').toUpperCase() }}
                </p>
                <div class="grupo-card-small-stats">
                  <div class="mini-stat">
                    <span class="mini-stat-val">{{ grupo.total_alumnos || 0 }}</span>
                    <span class="mini-stat-lbl">INS.</span>
                  </div>
                  <div class="mini-stat">
                    <span class="mini-stat-val text-verde">{{ grupo.alumnos_regulares || 0 }}</span>
                    <span class="mini-stat-lbl">REG.</span>
                  </div>
                  <div class="mini-stat">
                    <span class="mini-stat-val text-rojo">{{ grupo.alumnos_irregulares || 0 }}</span>
                    <span class="mini-stat-lbl">IRR.</span>
                  </div>
                </div>
                <div class="grupo-card-small-footer">
                  <span class="grupo-aula-mini">
                    <MapPin class="tiny-icon" />
                    {{ (grupo.aula_nombre || 'SIN AULA').toUpperCase() }}
                  </span>
                  <span class="ver-detalle-link">VER DETALLE →</span>
                </div>
              </div>
            </div>

            <div v-if="!cargandoGrupos && gruposCarrera.length === 0" class="grupos-vacio">
              <p>NO HAY GRUPOS REGISTRADOS PARA ESTA CARRERA</p>
            </div>

            <div class="grupos-footer">
              <button class="btn-ver-todos" @click="irATodosLosGrupos(carreraSeleccionada)">
                VER TODOS LOS GRUPOS
              </button>
            </div>
          </div>
        </Transition>

        <!-- Paginación cards -->
        <div class="paginacion cards-paginacion" v-if="carrerasFiltradas.length > filasPorPagina">
          <div class="paginacion-centro">
            <button class="btn-pag" @click="prevPageCards" :disabled="currentPageCards === 1">
              <ChevronLeft class="pag-icon" />
            </button>
            <button
              v-for="page in visiblePagesCards" :key="page"
              class="btn-pag" :class="{ activo: page === currentPageCards }"
              @click="goToPageCards(page)"
            >{{ page }}</button>
            <button class="btn-pag" @click="nextPageCards" :disabled="currentPageCards === totalPagesCards">
              <ChevronRight class="pag-icon" />
            </button>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════
           VISTA DE TABLA
      ══════════════════════════════════════════════════ -->
      <div v-else class="table-container">
        <div v-if="cargando && carreras.length === 0" class="estado-cargando">
          <div class="spinner-tabla"></div>
          <p>CARGANDO REGISTROS...</p>
        </div>

        <table v-else-if="carrerasPaginadasTabla.length > 0" class="data-table">
          <thead>
            <tr>
              <th>NOMBRE DE LA CARRERA</th>
              <th>DEPARTAMENTO</th>
              <th>NIVEL</th>
              <th>ESTATUS</th>
              <th class="th-acciones">ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(carrera, index) in carrerasPaginadasTabla"
              :key="carrera.id_carrera"
              :class="{ 'fila-seleccionada': filaActiva === index }"
              @click="filaActiva = index"
            >
              <td class="celda-nombre">
                <span class="link-detalle" @click.stop="irADetalle(carrera)">
                  {{ carrera.nombre.toUpperCase() }}
                </span>
              </td>
              <td>{{ (carrera.departamento?.nombre || '—').toUpperCase() }}</td>
              <td>{{ (carrera.nivel?.nombre_nivel || '—').toUpperCase() }}</td>
              <td>
                <span class="estatus-badge" :class="carrera.estatus ? 'activo' : 'inactivo'">
                  {{ carrera.estatus ? 'ACTIVO' : 'INACTIVO' }}
                </span>
              </td>
              <td class="celda-acciones">
                <button class="btn-icono ver" @click.stop="abrirModalVer(carrera)" title="VER DETALLES">
                  <Eye class="btn-icon-svg-small" />
                </button>
                <button class="btn-icono editar" @click.stop="abrirModalEditar(carrera)" title="EDITAR">
                  <Pencil class="btn-icon-svg-small" />
                </button>
                <button class="btn-icono detalle" @click.stop="irADetalle(carrera)" title="VER DETALLE COMPLETO">
                  <ExternalLink class="btn-icon-svg-small" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="estado-vacio">
          <FolderOpen class="icono-vacio" />
          <h3>SIN RESULTADOS</h3>
          <p>NO SE ENCONTRARON CARRERAS CON LOS CRITERIOS APLICADOS</p>
          <button class="btn-limpiar-vacio" @click="limpiarFiltros">LIMPIAR FILTROS</button>
        </div>

        <!-- Paginación tabla -->
        <div class="paginacion">
          <div class="paginacion-izquierda" v-if="carrerasFiltradas.length > 5">
            FILAS POR PÁGINA:
            <select v-model="filasPorPaginaTabla" @change="currentPageTabla = 1" class="select-filas">
              <option :value="10">10</option>
              <option :value="20">20</option>
              <option :value="50">50</option>
            </select>
          </div>
          <div class="paginacion-centro">
            PÁGINA {{ currentPageTabla }} DE {{ totalPagesTabla }}
          </div>
          <div class="paginacion-derecha">
            <button class="btn-pag" @click="prevPageTabla" :disabled="currentPageTabla === 1">
              <ChevronLeft class="pag-icon" />
            </button>
            <button
              v-for="page in visiblePagesTabla" :key="page"
              class="btn-pag" :class="{ activo: page === currentPageTabla }"
              @click="goToPageTabla(page)"
            >{{ page }}</button>
            <button class="btn-pag" @click="nextPageTabla" :disabled="currentPageTabla === totalPagesTabla">
              <ChevronRight class="pag-icon" />
            </button>
          </div>
        </div>
      </div>

      <footer class="pie-pagina">
        © 2026 TECNOLOGICO NACIONAL DE MEXICO | TODOS LOS DERECHOS RESERVADOS
      </footer>

      <!-- ══════════════════════════════════════════════════
           MODALES
      ══════════════════════════════════════════════════ -->

      <!-- MODAL VER DETALLE -->
      <Teleport to="body">
        <transition name="modal-fade">
          <div v-if="showModalVer" class="modal-overlay" @click.self="showModalVer = false">
            <div class="modal-content modal-ver">
              <div class="modal-header">
                <div class="modal-header-info">
                  <span class="modal-header-tag">CARRERA</span>
                  <h3>{{ carreraVer.nombre?.toUpperCase() }}</h3>
                </div>
                <button @click="showModalVer = false" class="btn-cerrar-modal">
                  <X :size="20" />
                </button>
              </div>
              <div class="modal-body">
                <div class="detalle-grid">
                  <div class="detalle-campo">
                    <span class="detalle-label">DEPARTAMENTO</span>
                    <span class="detalle-valor">{{ (carreraVer.departamento?.nombre || '—').toUpperCase() }}</span>
                  </div>
                  <div class="detalle-campo">
                    <span class="detalle-label">NIVEL ACADEMICO</span>
                    <span class="detalle-valor">{{ (carreraVer.nivel?.nombre_nivel || '—').toUpperCase() }}</span>
                  </div>
                  <div class="detalle-campo">
                    <span class="detalle-label">ESTATUS</span>
                    <span class="estatus-badge" :class="carreraVer.estatus ? 'activo' : 'inactivo'">
                      {{ carreraVer.estatus ? 'ACTIVO' : 'INACTIVO' }}
                    </span>
                  </div>
                  <div v-if="carreraVer.id_carrera" class="detalle-campo">
                    <span class="detalle-label">ID INTERNO</span>
                    <span class="detalle-valor detalle-mono">{{ carreraVer.id_carrera }}</span>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn-secundario" @click="showModalVer = false">CERRAR</button>
                <button class="btn-guardar" @click="irADetalle(carreraVer); showModalVer = false">
                  <ExternalLink :size="14" />
                  VER DETALLE COMPLETO
                </button>
                <button class="btn-editar" @click="abrirModalEditar(carreraVer); showModalVer = false">
                  <Pencil :size="14" />
                  EDITAR
                </button>
              </div>
            </div>
          </div>
        </transition>
      </Teleport>

      <!-- MODAL CREAR / EDITAR -->
      <Teleport to="body">
        <transition name="modal-fade">
          <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
            <div class="modal-content">
              <div class="modal-header">
                <div class="modal-header-info">
                  <span class="modal-header-tag">{{ form.id_carrera ? 'EDICION' : 'NUEVA' }}</span>
                  <h3>{{ form.id_carrera ? 'EDITAR CARRERA' : 'NUEVA CARRERA' }}</h3>
                </div>
                <button @click="cerrarModal" class="btn-cerrar-modal">
                  <X :size="20" />
                </button>
              </div>
              <div class="modal-body">
                <div class="form-grupo">
                  <label>NOMBRE DE LA CARRERA <span class="obligatorio">*</span></label>
                  <input v-model.trim="form.nombre" type="text" maxlength="150"
                         class="modal-input" :class="{ 'borde-error': errors.nombre }"
                         placeholder="EJ. INGENIERIA EN SISTEMAS COMPUTACIONALES">
                  <small v-if="errors.nombre" class="mensaje-error">{{ errors.nombre }}</small>
                </div>
                <div class="form-grupo">
                  <label>DEPARTAMENTO <span class="obligatorio">*</span></label>
                  <select v-model="form.id_departamento" class="modal-select"
                          :class="{ 'borde-error': errors.id_departamento }">
                    <option value="">SELECCIONAR DEPARTAMENTO</option>
                    <option v-for="dep in departamentos" :key="dep.id_departamento" :value="dep.id_departamento">
                      {{ dep.nombre.toUpperCase() }}
                    </option>
                  </select>
                  <small v-if="errors.id_departamento" class="mensaje-error">{{ errors.id_departamento }}</small>
                </div>
                <div class="form-grupo">
                  <label>NIVEL ACADEMICO <span class="obligatorio">*</span></label>
                  <select v-model="form.id_nivel" class="modal-select"
                          :class="{ 'borde-error': errors.id_nivel }">
                    <option value="">SELECCIONAR NIVEL</option>
                    <option v-for="niv in niveles" :key="niv.id_nivel" :value="niv.id_nivel">
                      {{ niv.nombre_nivel.toUpperCase() }}
                    </option>
                  </select>
                  <small v-if="errors.id_nivel" class="mensaje-error">{{ errors.id_nivel }}</small>
                </div>
                <div class="form-grupo">
                  <label>ESTATUS</label>
                  <select v-model="form.estatus" class="modal-select">
                    <option :value="1">ACTIVO</option>
                    <option :value="0">INACTIVO</option>
                  </select>
                  <div class="indicador-estatus" :class="form.estatus ? 'activo' : 'inactivo'">
                    {{ form.estatus ? 'ACTIVO' : 'INACTIVO' }}
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">CANCELAR</button>
                <button v-if="form.id_carrera" class="btn-eliminar" @click="solicitarEliminar" :disabled="guardando">ELIMINAR</button>
                <button class="btn-guardar" @click="guardar" :disabled="guardando">
                  <Loader2 v-if="guardando" :size="16" class="spinner-animate" />
                  {{ guardando ? 'GUARDANDO...' : 'GUARDAR' }}
                </button>
              </div>
            </div>
          </div>
        </transition>
      </Teleport>

      <!-- MODAL CONFIRMAR ELIMINAR -->
      <Teleport to="body">
        <transition name="modal-fade">
          <div v-if="showModalEliminar" class="modal-overlay" @click.self="showModalEliminar = false">
            <div class="modal-content modal-confirmar">
              <div class="modal-header">
                <div class="modal-header-info">
                  <span class="modal-header-tag">CONFIRMAR</span>
                  <h3>ELIMINAR CARRERA</h3>
                </div>
                <button @click="showModalEliminar = false" class="btn-cerrar-modal">
                  <X :size="20" />
                </button>
              </div>
              <div class="modal-body confirmar-body">
                <AlertTriangle :size="40" class="confirmar-icono" />
                <p>¿ESTAS SEGURO DE ELIMINAR LA CARRERA <strong>{{ carreraAEliminar?.nombre?.toUpperCase() }}</strong>? ESTA ACCION NO SE PUEDE DESHACER.</p>
              </div>
              <div class="modal-footer">
                <button class="btn-secundario" @click="showModalEliminar = false">CANCELAR</button>
                <button class="btn-eliminar" @click="confirmarEliminar" :disabled="guardando">
                  <Loader2 v-if="guardando" :size="16" class="spinner-animate" />
                  {{ guardando ? 'ELIMINANDO...' : 'ELIMINAR' }}
                </button>
              </div>
            </div>
          </div>
        </transition>
      </Teleport>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, reactive, watch } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import CareerCardDrill from '@/components/drilldown/CareerCardDrill.vue'

// Lucide Icons
import { 
  CheckCircle, AlertCircle, Search, Filter, Plus, LayoutGrid, List,
  X, ChevronLeft, ChevronRight, Eye, Pencil, ExternalLink, 
  FolderOpen, User, MapPin, Loader2, AlertTriangle
} from 'lucide-vue-next'

const router = useRouter()
const API = `${import.meta.env.VITE_API_URL}/api`

// ── Props ──
const props = defineProps({
  busquedaGlobal: { type: String, default: '' }
})

// ── Estado ──
const carreras = ref([])
const departamentos = ref([])
const niveles = ref([])
const cargando = ref(false)
const guardando = ref(false)
const filaActiva = ref(-1)

// Vista actual
const vistaActual = ref('cards')

// Paginación cards
const currentPageCards = ref(1)
const filasPorPagina = ref(9)

// Paginación tabla
const currentPageTabla = ref(1)
const filasPorPaginaTabla = ref(10)

// Filtros
const mostrarFiltros = ref(false)
const busquedaLocal = ref('')
const filtroDepartamento = ref('')
const filtroNivel = ref('')
const filtroEstatus = ref('')

// Drill-down
const carreraSeleccionada = ref(null)
const gruposCarrera = ref([])
const cargandoGrupos = ref(false)

// Modales
const showModal = ref(false)
const showModalVer = ref(false)
const showModalEliminar = ref(false)
const carreraVer = ref({})
const carreraAEliminar = ref(null)

const form = reactive({ id_carrera: null, nombre: '', id_departamento: '', id_nivel: '', estatus: 1 })
const errors = reactive({})

const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

// ── Skeleton ──
const skeletonCareer = {
  id: 0,
  nombre: 'CARGANDO...',
  abreviacion: '---',
  icono: 'graduation',
  colorKey: 'slate',
  stats: {
    grupos: 0,
    totalAlumnos: 0,
    regulares: 0,
    irregulares: 0,
    periodo: 'CARGANDO'
  }
}

// ── Mapeo a formato de drill ──
const mapToDrillFormat = (carrera) => {
  const stats = {
    grupos: carrera.grupos_count || Math.floor(Math.random() * 20) + 5,
    totalAlumnos: carrera.total_alumnos || Math.floor(Math.random() * 500) + 100,
    regulares: carrera.regulares_count || Math.floor(Math.random() * 400) + 50,
    irregulares: carrera.irregulares_count || Math.floor(Math.random() * 100) + 10,
    periodo: 'ENE-JUN 2025'
  }
  
  let icono = 'graduation'
  const nombre = (carrera.nombre || '').toLowerCase()
  if (nombre.includes('sistemas') || nombre.includes('computación')) icono = 'monitor'
  else if (nombre.includes('industrial') || nombre.includes('mecánica')) icono = 'factory'
  else if (nombre.includes('electr') || nombre.includes('eléctrica')) icono = 'zap'
  else if (nombre.includes('gestión') || nombre.includes('administración')) icono = 'settings'
  else if (nombre.includes('química')) icono = 'flask'
  else if (nombre.includes('arquitectura') || nombre.includes('civil')) icono = 'building'
  
  let colorKey = 'blue'
  const dept = (carrera.departamento?.nombre || '').toLowerCase()
  if (dept.includes('sistemas') || dept.includes('computación')) colorKey = 'blue'
  else if (dept.includes('industrial') || dept.includes('mecánica')) colorKey = 'orange'
  else if (dept.includes('electr') || dept.includes('eléctrica')) colorKey = 'teal'
  else if (dept.includes('gestión') || dept.includes('administración')) colorKey = 'purple'
  else if (dept.includes('química')) colorKey = 'green'
  else if (dept.includes('civil') || dept.includes('arquitectura')) colorKey = 'slate'
  
  return {
    id: carrera.id_carrera,
    nombre: carrera.nombre,
    abreviacion: carrera.clave || carrera.nombre?.substring(0, 4).toUpperCase(),
    icono,
    colorKey,
    stats
  }
}

// ── Navegación ──
const irADetalle = (carrera) => {
  if (!carrera?.id_carrera) return
  router.push(`/gestion-academica/carreras/${carrera.id_carrera}`)
}

const irADetalleGrupo = (grupo) => {
  router.push(`/servicios-escolares/grupos/${grupo.id_grupo}`)
}

const irATodosLosGrupos = (carrera) => {
  router.push(`/servicios-escolares/grupos?carrera=${carrera.id_carrera}`)
}

// ── Cargar grupos ──
const cargarGruposDeCarrera = async (carreraId) => {
  cargandoGrupos.value = true
  try {
    const res = await fetch(`${API}/carreras/${carreraId}/grupos`)
    if (res.ok) {
      gruposCarrera.value = await res.json()
    } else {
      gruposCarrera.value = []
    }
  } catch (error) {
    console.error('Error cargando grupos:', error)
    gruposCarrera.value = []
  } finally {
    cargandoGrupos.value = false
  }
}

const seleccionarCarrera = async (carrera) => {
  if (carreraSeleccionada.value?.id_carrera === carrera.id_carrera) {
    carreraSeleccionada.value = null
    gruposCarrera.value = []
    return
  }
  carreraSeleccionada.value = carrera
  await cargarGruposDeCarrera(carrera.id_carrera)
}

// ── Sincronización búsqueda ──
watch(() => props.busquedaGlobal, (nuevaBusqueda) => {
  busquedaLocal.value = nuevaBusqueda
  currentPageCards.value = 1
  currentPageTabla.value = 1
})

watch(busquedaLocal, () => {
  currentPageCards.value = 1
  currentPageTabla.value = 1
})

// ── Computadas ──
const filtrosActivos = computed(() => !!(filtroDepartamento.value || filtroNivel.value || filtroEstatus.value))
const contadorFiltros = computed(() => [filtroDepartamento.value, filtroNivel.value, filtroEstatus.value].filter(Boolean).length)

const carrerasFiltradas = computed(() =>
  carreras.value.filter(c => {
    const n = (t) => t?.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '') ?? ''
    const coincideBusqueda = !busquedaLocal.value || n(c.nombre).includes(n(busquedaLocal.value))
    const coincideDep = !filtroDepartamento.value || c.id_departamento == filtroDepartamento.value
    const coincideNivel = !filtroNivel.value || c.id_nivel == filtroNivel.value
    const coincideEstatus = !filtroEstatus.value || String(c.estatus) === filtroEstatus.value
    return coincideBusqueda && coincideDep && coincideNivel && coincideEstatus
  })
)

// Paginación Cards
const totalPagesCards = computed(() => Math.ceil(carrerasFiltradas.value.length / filasPorPagina.value) || 1)
const carrerasPaginadasCards = computed(() => {
  const start = (currentPageCards.value - 1) * filasPorPagina.value
  return carrerasFiltradas.value.slice(start, start + filasPorPagina.value)
})
const visiblePagesCards = computed(() => {
  const total = totalPagesCards.value
  const cur = currentPageCards.value
  if (total <= 5) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = new Set([1, total, cur, cur - 1, cur + 1].filter(p => p >= 1 && p <= total))
  return [...pages].sort((a, b) => a - b)
})

const prevPageCards = () => { if (currentPageCards.value > 1) currentPageCards.value--; cerrarGruposExpandidos() }
const nextPageCards = () => { if (currentPageCards.value < totalPagesCards.value) currentPageCards.value++; cerrarGruposExpandidos() }
const goToPageCards = (p) => { currentPageCards.value = p; cerrarGruposExpandidos() }

// Paginación Tabla
const totalPagesTabla = computed(() => Math.ceil(carrerasFiltradas.value.length / filasPorPaginaTabla.value) || 1)
const carrerasPaginadasTabla = computed(() => {
  const start = (currentPageTabla.value - 1) * filasPorPaginaTabla.value
  return carrerasFiltradas.value.slice(start, start + filasPorPaginaTabla.value)
})
const visiblePagesTabla = computed(() => {
  const total = totalPagesTabla.value
  const cur = currentPageTabla.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = new Set([1, total, cur, cur - 1, cur + 1].filter(p => p >= 1 && p <= total))
  return [...pages].sort((a, b) => a - b)
})

const prevPageTabla = () => { if (currentPageTabla.value > 1) currentPageTabla.value-- }
const nextPageTabla = () => { if (currentPageTabla.value < totalPagesTabla.value) currentPageTabla.value++ }
const goToPageTabla = (p) => { currentPageTabla.value = p }

const cerrarGruposExpandidos = () => {
  carreraSeleccionada.value = null
  gruposCarrera.value = []
}

// ── Notificaciones ──
const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje: mensaje.toUpperCase(), tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

// ── Carga de datos ──
const cargarCarreras = async () => {
  cargando.value = true
  try {
    const res = await fetch(`${API}/carreras`)
    if (!res.ok) throw new Error('Error del servidor')
    carreras.value = await res.json()
  } catch {
    mostrarNotificacion('NO SE PUDIERON CARGAR LAS CARRERAS.', 'error')
  } finally {
    cargando.value = false
  }
}

const cargarCatalogos = async () => {
  try {
    const [resDep, resNiv] = await Promise.all([
      fetch(`${API}/departamentos`),
      fetch(`${API}/niveles-carrera`)
    ])
    departamentos.value = await resDep.json()
    niveles.value = await resNiv.json()
  } catch (e) {
    console.error('Error cargando catálogos:', e)
  }
}

const limpiarFiltros = () => {
  busquedaLocal.value = ''
  filtroDepartamento.value = ''
  filtroNivel.value = ''
  filtroEstatus.value = ''
  currentPageCards.value = 1
  currentPageTabla.value = 1
  filaActiva.value = -1
  carreraSeleccionada.value = null
}

onMounted(() => {
  cargarCarreras()
  cargarCatalogos()
})

// ── Métodos de modales ──
const resetForm = () => {
  form.id_carrera = null
  form.nombre = ''
  form.id_departamento = ''
  form.id_nivel = ''
  form.estatus = 1
  Object.keys(errors).forEach(k => delete errors[k])
}

const abrirModalNuevo = () => { resetForm(); showModal.value = true }
const abrirModalVer = (c) => { carreraVer.value = c; showModalVer.value = true }
const abrirModalEditar = (c) => {
  resetForm()
  form.id_carrera = c.id_carrera
  form.nombre = c.nombre
  form.id_departamento = c.id_departamento
  form.id_nivel = c.id_nivel
  form.estatus = c.estatus
  showModal.value = true
}
const cerrarModal = () => { showModal.value = false; resetForm() }

const validar = () => {
  Object.keys(errors).forEach(k => delete errors[k])
  if (!form.nombre.trim()) errors.nombre = 'EL NOMBRE ES OBLIGATORIO'
  if (!form.id_departamento) errors.id_departamento = 'SELECCIONA UN DEPARTAMENTO'
  if (!form.id_nivel) errors.id_nivel = 'SELECCIONA UN NIVEL'
  return Object.keys(errors).length === 0
}

const guardar = async () => {
  if (!validar()) return
  guardando.value = true
  const esEdicion = !!form.id_carrera
  const url = esEdicion ? `${API}/carreras/${form.id_carrera}` : `${API}/carreras`
  const method = esEdicion ? 'PUT' : 'POST'
  const payload = {
    nombre: form.nombre.trim(),
    id_departamento: form.id_departamento,
    id_nivel: form.id_nivel,
    estatus: form.estatus
  }
  try {
    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    })
    if (!res.ok) throw new Error('Error del servidor')
    await cargarCarreras()
    cerrarModal()
    mostrarNotificacion(esEdicion ? 'CARRERA ACTUALIZADA CORRECTAMENTE.' : 'CARRERA CREADA CORRECTAMENTE.')
  } catch {
    mostrarNotificacion('OCURRIO UN ERROR AL GUARDAR.', 'error')
  } finally {
    guardando.value = false
  }
}

const solicitarEliminar = () => {
  carreraAEliminar.value = { id_carrera: form.id_carrera, nombre: form.nombre }
  showModal.value = false
  showModalEliminar.value = true
}

const confirmarEliminar = async () => {
  if (!carreraAEliminar.value) return
  guardando.value = true
  try {
    const res = await fetch(`${API}/carreras/${carreraAEliminar.value.id_carrera}`, {
      method: 'DELETE',
      headers: { 'Accept': 'application/json' }
    })
    if (!res.ok) throw new Error('Error del servidor')
    await cargarCarreras()
    showModalEliminar.value = false
    carreraAEliminar.value = null
    mostrarNotificacion('CARRERA ELIMINADA CORRECTAMENTE.')
  } catch {
    mostrarNotificacion('OCURRIO UN ERROR AL ELIMINAR.', 'error')
  } finally {
    guardando.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap');

/* ═══════════════════════════════════════════════
   VARIABLES - DESIGN SYSTEM SICE
═══════════════════════════════════════════════ */
.carreras-page {
  --azul: #1B396A;
  --azul-hover: #1D4ED8;
  --azul-suave: #DBEAFE;
  --borde: #E5E7EB;
  --fondo: #F5F5F5;
  --texto: #111827;
  --gris: #6B7280;
  --verde: #16A34A;
  --rojo: #DC2626;
  --amarillo: #D97706;
  width: 100%;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
  padding-bottom: 2rem;
}

/* ── Espaciado reducido ── */
.breadcrumb { display: flex; align-items: center; gap: 6px; color: var(--gris); font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
.breadcrumb-link { color: var(--azul); cursor: pointer; }
.breadcrumb-link:hover { color: var(--azul-hover); text-decoration: underline; }
.breadcrumb-actual { color: var(--azul); }

.page-header { margin-bottom: 0.75rem; }
.page-title { color: var(--texto); font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; margin: 0; }
.page-subtitle { font-size: 0.75rem; color: var(--gris); font-weight: 600; margin: 0; letter-spacing: 0.05em; }

/* ── Barra de carga ── */
.barra-carga { height: 2px; margin-bottom: 0.5rem; overflow: hidden; opacity: 0; transition: opacity 0.2s; }
.barra-carga.visible { opacity: 1; }
.barra-progreso { height: 100%; width: 40%; background: var(--azul); animation: deslizar 1.2s ease-in-out infinite; }
@keyframes deslizar { 0% { transform: translateX(-100%); } 100% { transform: translateX(350%); } }

/* ── Toast ── */
.toast { position: fixed; bottom: 2rem; right: 2rem; display: flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; color: white; font-weight: 600; font-size: 0.8rem; letter-spacing: 0.05em; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 3000; }
.toast.exito { background: var(--azul); }
.toast.error { background: var(--rojo); }
.toast-icono { width: 18px; height: 18px; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all 0.3s ease; }
.toast-slide-enter-from, .toast-slide-leave-to { opacity: 0; transform: translateX(110%); }

/* ── Barra de acciones ── */
.actions-bar { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; flex-wrap: wrap; }
.search-group { position: relative; flex: 1; min-width: 200px; }
.search-input { width: 100%; padding: 8px 12px 8px 36px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.8rem; background: white; font-family: 'Montserrat', sans-serif; outline: none; }
.search-input:focus { border-color: var(--azul); box-shadow: 0 0 0 2px var(--azul-suave); }
.search-icon-svg { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; stroke: var(--gris); }

.btn-filtros, .btn-nuevo { display: flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.05em; cursor: pointer; font-family: 'Montserrat', sans-serif; }
.btn-filtros { background: white; color: var(--texto); border: 1px solid var(--borde); }
.btn-filtros:hover { background: var(--fondo); }
.btn-filtros.activo { border-color: var(--azul); color: var(--azul); background: var(--azul-suave); }
.btn-nuevo { background: var(--azul); color: white; border: none; }
.btn-nuevo:hover { background: var(--azul-hover); }
.btn-icon-svg, .btn-icon-svg-small { width: 14px; height: 14px; }
.filtros-badge { background: var(--azul); color: white; font-size: 0.6rem; border-radius: 10px; padding: 1px 5px; margin-left: 4px; }

/* ── Toggle vista ── */
.view-toggle { display: flex; gap: 2px; background: white; border: 1px solid var(--borde); border-radius: 8px; padding: 3px; }
.toggle-btn { display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; border: none; background: transparent; border-radius: 6px; cursor: pointer; }
.toggle-btn svg { width: 16px; height: 16px; stroke: var(--gris); }
.toggle-btn:hover { background: var(--fondo); }
.toggle-btn.activo { background: var(--azul); }
.toggle-btn.activo svg { stroke: white; }

/* ── Panel filtros ── */
.filtros-panel { background: white; border: 1px solid var(--borde); border-radius: 10px; padding: 0.8rem 1rem; margin-bottom: 0.75rem; }
.filtros-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; }
.filtro-label { font-size: 0.7rem; font-weight: 700; color: var(--gris); letter-spacing: 0.08em; margin-bottom: 4px; display: block; }
.filter-select { width: 100%; padding: 7px 10px; border: 1px solid var(--borde); border-radius: 6px; font-size: 0.75rem; font-weight: 500; background: white; }
.filtros-footer { display: flex; justify-content: flex-end; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--borde); }
.btn-limpiar { display: flex; align-items: center; gap: 5px; background: none; border: none; color: var(--gris); font-size: 0.7rem; font-weight: 600; cursor: pointer; }
.btn-limpiar:hover { color: var(--rojo); }
.reset-icon { width: 12px; height: 12px; }
.filtros-slide-enter-active, .filtros-slide-leave-active { transition: all 0.2s ease; }
.filtros-slide-enter-from, .filtros-slide-leave-to { opacity: 0; transform: translateY(-5px); }

/* ── Cards grid ── */
.cards-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1rem; }
@media (max-width: 1024px) { .cards-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 640px) { .cards-grid { grid-template-columns: 1fr; } }

/* ── Grupos expandidos ── */
.grupos-expandidos { background: white; border-radius: 14px; border: 1px solid var(--borde); margin: 0.75rem 0 1rem; overflow: hidden; }
.expand-grupos-enter-active, .expand-grupos-leave-active { transition: all 0.25s ease; }
.expand-grupos-enter-from, .expand-grupos-leave-to { opacity: 0; transform: translateY(-8px); }
.grupos-header { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: var(--azul); color: white; }
.grupos-header-tag { font-size: 0.6rem; font-weight: 700; letter-spacing: 0.1em; opacity: 0.8; }
.grupos-header-title { margin: 0; font-size: 0.85rem; font-weight: 700; }
.btn-cerrar-grupos { background: rgba(255,255,255,0.15); border: none; border-radius: 6px; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
.close-icon { width: 14px; height: 14px; stroke: white; }
.grupos-grid-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 0.75rem; padding: 1rem; }
.grupo-card-small { background: white; border: 1px solid var(--borde); border-radius: 10px; padding: 0.8rem; cursor: pointer; transition: all 0.15s; }
.grupo-card-small:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); border-color: var(--azul); }
.grupo-card-small-header { display: flex; justify-content: space-between; margin-bottom: 0.5rem; }
.grupo-chip-semestre { font-size: 0.6rem; font-weight: 700; background: var(--azul-suave); color: var(--azul); padding: 2px 8px; border-radius: 20px; }
.grupo-chip-turno { font-size: 0.6rem; font-weight: 700; padding: 2px 8px; border-radius: 20px; }
.turno-matutino { background: #FEF3C7; color: #D97706; }
.turno-vespertino { background: #EDE9FE; color: #7C3AED; }
.grupo-card-small-nombre { margin: 0 0 4px; font-size: 0.8rem; font-weight: 800; }
.grupo-card-small-docente { display: flex; align-items: center; gap: 4px; font-size: 0.65rem; color: var(--gris); margin-bottom: 0.6rem; }
.small-icon { width: 10px; height: 10px; }
.grupo-card-small-stats { display: flex; gap: 0.5rem; margin-bottom: 0.6rem; padding-bottom: 0.6rem; border-bottom: 1px solid var(--borde); }
.mini-stat { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 2px; }
.mini-stat-val { font-size: 0.8rem; font-weight: 800; }
.mini-stat-lbl { font-size: 0.55rem; font-weight: 700; color: var(--gris); }
.text-verde { color: var(--verde); }
.text-rojo { color: var(--rojo); }
.grupo-card-small-footer { display: flex; justify-content: space-between; align-items: center; }
.grupo-aula-mini { display: flex; align-items: center; gap: 3px; font-size: 0.6rem; color: var(--gris); }
.tiny-icon { width: 9px; height: 9px; }
.ver-detalle-link { font-size: 0.6rem; font-weight: 700; color: var(--azul); }
.grupos-vacio { text-align: center; padding: 1.5rem; color: var(--gris); font-size: 0.75rem; }
.grupos-footer { padding: 0.75rem 1rem; background: var(--fondo); border-top: 1px solid var(--borde); text-align: center; }
.btn-ver-todos { background: transparent; border: 1px solid var(--azul); color: var(--azul); padding: 6px 16px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; cursor: pointer; }
.btn-ver-todos:hover { background: var(--azul); color: white; }

/* ── Tabla ── */
.table-container { background: white; border-radius: 12px; overflow: auto; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid var(--borde); }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.75rem; }
.data-table th { background: var(--fondo); padding: 10px 12px; text-align: left; font-weight: 700; color: var(--texto); border-bottom: 1px solid var(--borde); letter-spacing: 0.05em; }
.data-table td { padding: 8px 12px; border-bottom: 1px solid var(--borde); color: var(--texto); }
.data-table tbody tr:hover { background: #F8FAFC; }
.fila-seleccionada { background: var(--azul-suave) !important; }
.link-detalle { color: var(--azul); cursor: pointer; font-weight: 600; }
.link-detalle:hover { text-decoration: underline; }
.estatus-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; }
.estatus-badge.activo { background: #DCFCE7; color: var(--verde); }
.estatus-badge.inactivo { background: #F3F4F6; color: var(--gris); }
.celda-acciones { display: flex; gap: 4px; justify-content: center; }
.btn-icono { display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; cursor: pointer; border: none; background: transparent; }
.btn-icono.ver { background: #F3F4F6; }
.btn-icono.ver:hover { background: #E5E7EB; }
.btn-icono.editar { background: var(--azul); }
.btn-icono.editar svg { stroke: white; }
.btn-icono.editar:hover { background: var(--azul-hover); }
.btn-icono.detalle { background: #E0F2FE; }
.btn-icono.detalle svg { stroke: #0284C7; }

/* ── Paginación ── */
.paginacion { margin-top: 1rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.7rem; font-weight: 600; color: var(--gris); flex-wrap: wrap; gap: 0.5rem; }
.paginacion-centro, .paginacion-derecha, .paginacion-izquierda { display: flex; align-items: center; gap: 6px; }
.select-filas { border: 1px solid var(--borde); border-radius: 4px; padding: 3px 6px; font-size: 0.7rem; }
.btn-pag { padding: 4px 8px; border: 1px solid var(--borde); background: white; border-radius: 4px; cursor: pointer; font-size: 0.7rem; font-weight: 600; }
.btn-pag:hover:not(:disabled) { background: var(--fondo); }
.btn-pag:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-pag.activo { background: var(--azul); color: white; border-color: var(--azul); }
.pag-icon { width: 12px; height: 12px; }
.cards-paginacion { justify-content: center; }

/* ── Estados vacío y carga ── */
.estado-vacio, .estado-cargando { text-align: center; padding: 2rem; color: var(--gris); }
.icono-vacio { width: 40px; height: 40px; stroke: #9CA3AF; margin-bottom: 0.75rem; }
.spinner-tabla { display: inline-block; width: 30px; height: 30px; border: 2px solid var(--borde); border-top-color: var(--azul); border-radius: 50%; animation: girar 0.8s linear infinite; margin-bottom: 0.75rem; }
.grupos-loading { display: flex; align-items: center; justify-content: center; gap: 0.6rem; padding: 1.5rem; }
.loading-spinner-small { width: 20px; height: 20px; border: 2px solid var(--borde); border-top-color: var(--azul); border-radius: 50%; animation: girar 0.8s linear infinite; }
@keyframes girar { to { transform: rotate(360deg); } }

/* ── Footer ── */
.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.7rem; font-weight: 600; padding-top: 1.5rem; border-top: 1px solid var(--borde); margin-top: 1rem; letter-spacing: 0.05em; }

/* ── Modales ── */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.22s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 1rem;
}

.modal-content {
  background: #FFFFFF;
  width: 540px;
  max-width: 100%;
  max-height: 90vh;
  border-radius: 16px;
  box-shadow: 0 24px 60px rgba(0,0,0,0.2);
  overflow: hidden;
  border: 1px solid #E5E7EB;
  display: flex;
  flex-direction: column;
}

.modal-ver { width: 580px; }
.modal-confirmar { width: 440px; }

.modal-header {
  background: #1B396A;
  color: white;
  padding: 1rem 1.4rem;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-shrink: 0;
}
.modal-header-info { display: flex; flex-direction: column; gap: 2px; }
.modal-header-tag { font-size: 0.72rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; opacity: 0.7; }
.modal-header h3 { margin: 0; font-size: 1.15rem; font-weight: 700; }
.btn-cerrar-modal { background: none; border: none; color: white; cursor: pointer; display: flex; align-items: center; padding: 0; }
.btn-cerrar-modal:hover { opacity: 0.8; }

.modal-body { padding: 1.5rem 1.6rem; overflow-y: auto; }

.detalle-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.detalle-campo { display: flex; flex-direction: column; gap: 4px; padding: 0.75rem; background: #F8FAFC; border-radius: 8px; border: 1px solid #E5E7EB; }
.detalle-label { font-size: 0.78rem; font-weight: 600; color: var(--gris); text-transform: uppercase; letter-spacing: 0.05em; }
.detalle-valor { font-size: 0.95rem; font-weight: 500; color: var(--texto); }
.detalle-mono { font-family: monospace; font-size: 0.88rem; color: var(--azul); }

.form-grupo { margin-bottom: 1.2rem; }
.form-grupo label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: #1A1A1A; }
.obligatorio { color: #DC2626; }

.modal-input, .modal-select {
  width: 100%;
  padding: 10px 14px;
  border: 1.5px solid #E5E7EB;
  border-radius: 8px;
  font-size: 0.95rem;
  background: #FFFFFF;
  font-family: 'Montserrat', sans-serif;
  outline: none;
  transition: border-color 0.2s;
  box-sizing: border-box;
}
.modal-input:focus, .modal-select:focus { border-color: #1B396A; box-shadow: 0 0 0 3px #DBEAFE; }
.borde-error { border-color: #DC2626 !important; }
.mensaje-error { display: block; color: #DC2626; font-size: 0.82rem; margin-top: 5px; }

.indicador-estatus { display: inline-flex; align-items: center; margin-top: 7px; padding: 4px 12px; border-radius: 20px; font-size: 0.82rem; font-weight: 600; }
.indicador-estatus.activo { background: #DCFCE7; color: #16A34A; }
.indicador-estatus.inactivo { background: #F3F4F6; color: #6B7280; }

.modal-footer {
  padding: 1rem 1.6rem;
  background: #F8FAFC;
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  border-top: 1px solid #E5E7EB;
  flex-shrink: 0;
}

.btn-secundario, .btn-editar, .btn-eliminar, .btn-guardar {
  padding: 9px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  font-size: 0.8rem;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.btn-secundario { background: #FFFFFF; color: #1B396A; border: 2px solid #1B396A; }
.btn-secundario:hover { background: #DBEAFE; }
.btn-editar { background: #E0F2FE; color: #0284C7; border: 2px solid #BAE6FD; }
.btn-editar:hover { background: #BAE6FD; }
.btn-eliminar { background: #DC2626; color: white; border: none; }
.btn-eliminar:hover { background: #B91C1C; }
.btn-guardar { background: #1B396A; color: white; border: none; }
.btn-guardar:hover { background: #1D4ED8; }
.btn-guardar:disabled, .btn-eliminar:disabled, .btn-secundario:disabled { opacity: 0.6; cursor: not-allowed; }

.confirmar-body { display: flex; flex-direction: column; align-items: center; gap: 1rem; text-align: center; padding: 2rem 1.6rem; }
.confirmar-icono { stroke: #F59E0B; }

.spinner-animate { animation: girar 0.7s linear infinite; }

@media (max-width: 600px) {
  .detalle-grid { grid-template-columns: 1fr; }
  .modal-content { border-radius: 12px; }
  .modal-footer { flex-wrap: wrap; }
  .modal-footer button { flex: 1; justify-content: center; }
}
</style>