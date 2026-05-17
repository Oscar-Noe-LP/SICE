<template>
  <MainLayout>
    <div class="planes-page">

      <nav class="breadcrumb">
        <span class="breadcrumb-link" @click="$router.push('/gestion-academica')">Gestión Académica</span>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-actual">Planes de Estudio</span>
      </nav>

      <div class="page-header">
        <h1 class="page-title">Planes de Estudio</h1>
        <p class="page-subtitle">{{ planesFiltrados.length }} registro(s) encontrado(s)</p>
      </div>

      <div class="barra-carga" :class="{ visible: cargando }"><div class="barra-progreso"></div></div>

      <transition name="toast-slide">
        <div v-if="notificacion.visible" class="toast" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo === 'exito'" xmlns="http://www.w3.org/2000/svg" class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- ══════════════════════════════════════════════════
           BARRA DE ACCIONES
           Cambio: Búsqueda siempre visible. El botón "Filtros"
           agrupa Carrera y Estatus (filtros reales útiles),
           colapsado por defecto. Se resalta si hay filtros activos.
      ══════════════════════════════════════════════════ -->
      <div class="actions-bar">

        <!-- Búsqueda principal -->
        <div class="search-group">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Buscar por nombre del plan..."
            v-model="busqueda"
            class="search-input"
            @keydown.escape="busqueda = ''"
          >
        </div>

        <!-- Filtros secundarios (Carrera + Estatus) — colapsado por defecto -->
        <button
          class="btn-filtros"
          @click="mostrarFiltros = !mostrarFiltros"
          :class="{ activo: filtrosActivos }"
          :title="mostrarFiltros ? 'Ocultar filtros' : 'Mostrar filtros'"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
          </svg>
          Filtros
          <span v-if="filtrosActivos" class="filtros-badge">{{ contadorFiltros }}</span>
        </button>

        <button class="btn-nuevo" @click="abrirModalNuevo">+ Nuevo plan</button>
      </div>

      <!-- Panel filtros desplegable (colapsado por defecto) -->
      <transition name="filtros-slide">
        <div v-if="mostrarFiltros" class="filtros-panel">
          <div class="filtros-grid">
            <div class="filtro-item">
              <label class="filtro-label">Carrera</label>
              <select v-model="filtroCarrera" class="filter-select">
                <option value="">Todas</option>
                <option v-for="c in carreras" :key="c.id_carrera" :value="c.id_carrera">{{ c.nombre }}</option>
              </select>
            </div>
            <div class="filtro-item">
              <label class="filtro-label">Estatus</label>
              <select v-model="filtroEstatus" class="filter-select">
                <option value="">Todos</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
              </select>
            </div>
          </div>
          <div class="filtros-footer">
            <button class="btn-limpiar" @click="limpiarFiltros">
              <svg xmlns="http://www.w3.org/2000/svg" class="reset-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Limpiar filtros
            </button>
          </div>
        </div>
      </transition>

      <!-- Tabla compacta -->
      <div class="table-container">
        <div v-if="cargando && planes.length === 0" class="estado-cargando">
          <div class="spinner-tabla"></div><p>Cargando registros...</p>
        </div>
        <table v-else-if="planesPaginados.length > 0" class="data-table">
          <thead>
            <tr>
              <th>Nombre del Plan</th>
              <th>Carrera</th>
              <th class="th-centro">Año</th>
              <th class="th-centro">Créditos</th>
              <th>Estatus</th>
              <th class="th-acciones">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(plan, index) in planesPaginados" :key="plan.id_plan"
                :class="{ 'fila-seleccionada': filaActiva === index }" @click="filaActiva = index">
              <td class="celda-nombre">{{ plan.nombre_plan }}</td>
              <td class="celda-secundaria">{{ plan.carrera?.nombre || '—' }}</td>
              <td class="td-centro">{{ plan.anio_vigencia }}</td>
              <td class="td-centro">{{ plan.total_creditos }}</td>
              <td><span class="estatus-badge" :class="plan.estatus ? 'activo' : 'inactivo'">{{ plan.estatus ? 'Activo' : 'Inactivo' }}</span></td>
              <td class="celda-acciones">
                <button class="btn-icono ver" @click.stop="abrirModalVer(plan)" title="Ver detalles">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                </button>
                <button class="btn-icono editar" @click.stop="abrirModalEditar(plan)" title="Editar">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3>Sin resultados</h3>
          <p>No se encontraron planes con los criterios aplicados.</p>
          <button class="btn-limpiar-vacio" @click="limpiarFiltros">Limpiar filtros</button>
        </div>
      </div>

      <div class="paginacion">
        <div class="paginacion-izquierda">Filas por página:
          <select v-model="filasPorPagina" @change="currentPage = 1" class="select-filas">
            <option :value="10">10</option><option :value="20">20</option><option :value="50">50</option>
          </select>
        </div>
        <div class="paginacion-centro">Página {{ currentPage }} de {{ totalPages }}</div>
        <div class="paginacion-derecha">
          <button class="btn-pag" @click="prevPage" :disabled="currentPage === 1">‹</button>
          <button v-for="page in visiblePages" :key="page" class="btn-pag" :class="{ activo: page === currentPage }" @click="goToPage(page)">{{ page }}</button>
          <button class="btn-pag" @click="nextPage" :disabled="currentPage === totalPages">›</button>
        </div>
      </div>

      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México | Todos los derechos reservados</footer>
    </div>

    <!-- ══════════════════════════════════════════════════
         MODAL VER — mejorado con grid de datos
    ══════════════════════════════════════════════════ -->
    <transition name="modal-fade">
      <div v-if="showModalVer" class="modal-overlay" @click.self="showModalVer = false">
        <div class="modal-content modal-ver">
          <div class="modal-header">
            <div class="modal-header-info">
              <span class="modal-header-tag">Plan de Estudio</span>
              <h3>{{ planVer.nombre_plan }}</h3>
            </div>
            <button @click="showModalVer = false" class="btn-cerrar-modal">×</button>
          </div>
          <div class="modal-body">
            <div class="detalle-grid">
              <div class="detalle-campo">
                <span class="detalle-label">Carrera</span>
                <span class="detalle-valor">{{ planVer.carrera?.nombre || '—' }}</span>
              </div>
              <div class="detalle-campo">
                <span class="detalle-label">Año de vigencia</span>
                <span class="detalle-valor detalle-numero">{{ planVer.anio_vigencia }}</span>
              </div>
              <div class="detalle-campo">
                <span class="detalle-label">Total de créditos</span>
                <span class="detalle-valor detalle-numero">{{ planVer.total_creditos }}</span>
              </div>
              <div class="detalle-campo">
                <span class="detalle-label">Estatus</span>
                <span class="estatus-badge" :class="planVer.estatus ? 'activo' : 'inactivo'">
                  {{ planVer.estatus ? 'Activo' : 'Inactivo' }}
                </span>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secundario" @click="showModalVer = false">Cerrar</button>
            <button class="btn-guardar" @click="abrirModalEditar(planVer); showModalVer = false">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Editar
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Modal Crear/Editar -->
    <transition name="modal-fade">
      <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
        <div class="modal-content">
          <div class="modal-header">
            <div class="modal-header-info">
              <span class="modal-header-tag">{{ form.id_plan ? 'Edición' : 'Nuevo' }}</span>
              <h3>{{ form.id_plan ? 'Editar Plan' : 'Nuevo Plan de Estudio' }}</h3>
            </div>
            <button @click="cerrarModal" class="btn-cerrar-modal">×</button>
          </div>
          <div class="modal-body">
            <div class="form-grupo">
              <label>Carrera <span class="obligatorio">*</span></label>
              <select v-model="form.id_carrera" class="modal-select" :class="{ 'borde-error': errors.id_carrera }">
                <option value="">Seleccionar carrera</option>
                <option v-for="c in carreras" :key="c.id_carrera" :value="c.id_carrera">{{ c.nombre }}</option>
              </select>
              <small v-if="errors.id_carrera" class="mensaje-error">{{ errors.id_carrera }}</small>
            </div>
            <div class="form-grupo">
              <label>Nombre del plan <span class="obligatorio">*</span></label>
              <input v-model.trim="form.nombre_plan" type="text" maxlength="100" class="modal-input" :class="{ 'borde-error': errors.nombre_plan }" placeholder="Ej. Plan 2020">
              <small v-if="errors.nombre_plan" class="mensaje-error">{{ errors.nombre_plan }}</small>
            </div>
            <div class="form-grupo-doble">
              <div class="form-grupo">
                <label>Año de vigencia <span class="obligatorio">*</span></label>
                <input v-model.number="form.anio_vigencia" type="number" min="2000" max="2099" class="modal-input" :class="{ 'borde-error': errors.anio_vigencia }" placeholder="2026">
                <small v-if="errors.anio_vigencia" class="mensaje-error">{{ errors.anio_vigencia }}</small>
              </div>
              <div class="form-grupo">
                <label>Total de créditos <span class="obligatorio">*</span></label>
                <input v-model.number="form.total_creditos" type="number" min="1" class="modal-input" :class="{ 'borde-error': errors.total_creditos }" placeholder="240">
                <small v-if="errors.total_creditos" class="mensaje-error">{{ errors.total_creditos }}</small>
              </div>
            </div>
            <div class="form-grupo">
              <label>Estatus</label>
              <select v-model="form.estatus" class="modal-select">
                <option :value="1">Activo</option>
                <option :value="0">Inactivo</option>
              </select>
              <div class="indicador-estatus" :class="form.estatus ? 'activo' : 'inactivo'">{{ form.estatus ? 'Activo' : 'Inactivo' }}</div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">Cancelar</button>
            <button v-if="form.id_plan" class="btn-eliminar" @click="solicitarEliminar" :disabled="guardando">Eliminar</button>
            <button class="btn-guardar" @click="guardar" :disabled="guardando">
              <span v-if="guardando" class="spinner-btn"></span>
              {{ guardando ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Modal Confirmar Eliminar -->
    <transition name="modal-fade">
      <div v-if="showModalEliminar" class="modal-overlay" @click.self="showModalEliminar = false">
        <div class="modal-content modal-confirmar">
          <div class="modal-header">
            <div class="modal-header-info">
              <span class="modal-header-tag">Confirmar</span>
              <h3>Eliminar plan</h3>
            </div>
            <button @click="showModalEliminar = false" class="btn-cerrar-modal">×</button>
          </div>
          <div class="modal-body confirmar-body">
            <svg xmlns="http://www.w3.org/2000/svg" class="confirmar-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p>¿Estás seguro de eliminar el plan <strong>{{ planAEliminar?.nombre_plan }}</strong>? Esta acción no se puede deshacer.</p>
          </div>
          <div class="modal-footer">
            <button class="btn-secundario" @click="showModalEliminar = false">Cancelar</button>
            <button class="btn-eliminar" @click="confirmarEliminar" :disabled="guardando">
              <span v-if="guardando" class="spinner-btn"></span>
              {{ guardando ? 'Eliminando...' : 'Eliminar' }}
            </button>
          </div>
        </div>
      </div>
    </transition>

  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`

// ── Estado ──────────────────────────────────────────────
const planes         = ref([])
const carreras       = ref([])
const cargando       = ref(false)
const guardando      = ref(false)
const filaActiva     = ref(-1)
const currentPage    = ref(1)
const filasPorPagina = ref(10)

// Panel de filtros colapsado por defecto
const mostrarFiltros = ref(false)
const busqueda       = ref('')
const filtroCarrera  = ref('')
const filtroEstatus  = ref('')

const showModal         = ref(false)
const showModalVer      = ref(false)
const showModalEliminar = ref(false)
const planVer           = ref({})
const planAEliminar     = ref(null)

const form = reactive({ id_plan: null, id_carrera: '', nombre_plan: '', anio_vigencia: new Date().getFullYear(), total_creditos: '', estatus: 1 })
const errors = reactive({})
const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

// ── Computadas ───────────────────────────────────────────
const filtrosActivos  = computed(() => !!(filtroCarrera.value || filtroEstatus.value))
const contadorFiltros = computed(() => [filtroCarrera.value, filtroEstatus.value].filter(Boolean).length)
const normalize = (t) => t?.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '') ?? ''

const planesFiltrados = computed(() =>
  planes.value.filter(p => {
    const coincideBusqueda = !busqueda.value || normalize(p.nombre_plan).includes(normalize(busqueda.value))
    const coincideCarrera  = !filtroCarrera.value || p.id_carrera == filtroCarrera.value
    const coincideEstatus  = !filtroEstatus.value || String(p.estatus) === filtroEstatus.value
    return coincideBusqueda && coincideCarrera && coincideEstatus
  })
)

const totalPages      = computed(() => Math.ceil(planesFiltrados.value.length / filasPorPagina.value) || 1)
const planesPaginados = computed(() => { const s = (currentPage.value - 1) * filasPorPagina.value; return planesFiltrados.value.slice(s, s + filasPorPagina.value) })
const visiblePages    = computed(() => { const t = totalPages.value, c = currentPage.value; if (t <= 7) return Array.from({ length: t }, (_, i) => i + 1); const p = new Set([1, t, c, c-1, c+1].filter(x => x >= 1 && x <= t)); return [...p].sort((a, b) => a - b) })

// ── Métodos ──────────────────────────────────────────────
const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

const cargarPlanes = async () => {
  cargando.value = true
  try {
    const res = await fetch(`${API}/planes-estudio`)
    if (!res.ok) throw new Error()
    planes.value = await res.json()
  } catch { mostrarNotificacion('No se pudieron cargar los planes.', 'error') }
  finally { cargando.value = false }
}

const cargarCarreras = async () => {
  try {
    const res = await fetch(`${API}/carreras`)
    if (res.ok) carreras.value = await res.json()
  } catch {}
}

onMounted(() => { cargarPlanes(); cargarCarreras() })

const goToPage        = (p) => { currentPage.value = p; filaActiva.value = -1 }
const prevPage        = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage        = () => { if (currentPage.value < totalPages.value) currentPage.value++ }
const limpiarFiltros  = () => { busqueda.value = ''; filtroCarrera.value = ''; filtroEstatus.value = ''; currentPage.value = 1; filaActiva.value = -1 }

const resetForm = () => { form.id_plan = null; form.id_carrera = ''; form.nombre_plan = ''; form.anio_vigencia = new Date().getFullYear(); form.total_creditos = ''; form.estatus = 1; Object.keys(errors).forEach(k => delete errors[k]) }
const abrirModalNuevo  = () => { resetForm(); showModal.value = true }
const abrirModalVer    = (p) => { planVer.value = p; showModalVer.value = true }
const abrirModalEditar = (p) => { resetForm(); form.id_plan = p.id_plan; form.id_carrera = p.id_carrera; form.nombre_plan = p.nombre_plan; form.anio_vigencia = p.anio_vigencia; form.total_creditos = p.total_creditos; form.estatus = p.estatus; showModal.value = true }
const cerrarModal      = () => { showModal.value = false; resetForm() }
const solicitarEliminar = () => { planAEliminar.value = { id_plan: form.id_plan, nombre_plan: form.nombre_plan }; showModal.value = false; showModalEliminar.value = true }

const validar = () => {
  Object.keys(errors).forEach(k => delete errors[k])
  if (!form.id_carrera)                                errors.id_carrera     = 'Selecciona una carrera'
  if (!form.nombre_plan.trim())                        errors.nombre_plan    = 'El nombre es obligatorio'
  if (!form.anio_vigencia)                             errors.anio_vigencia  = 'El año de vigencia es obligatorio'
  if (!form.total_creditos || form.total_creditos < 1) errors.total_creditos = 'Los créditos deben ser mayores a 0'
  return Object.keys(errors).length === 0
}

const guardar = async () => {
  if (!validar()) return
  guardando.value = true
  const esEdicion = !!form.id_plan
  const url     = esEdicion ? `${API}/planes-estudio/${form.id_plan}` : `${API}/planes-estudio`
  const payload = { id_carrera: form.id_carrera, nombre_plan: form.nombre_plan.trim(), anio_vigencia: form.anio_vigencia, total_creditos: form.total_creditos, estatus: form.estatus }
  try {
    const res = await fetch(url, { method: esEdicion ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(payload) })
    if (!res.ok) throw new Error()
    await cargarPlanes(); cerrarModal()
    mostrarNotificacion(esEdicion ? 'Plan actualizado correctamente.' : 'Plan creado correctamente.')
  } catch { mostrarNotificacion('Ocurrió un error al guardar.', 'error') }
  finally { guardando.value = false }
}

const confirmarEliminar = async () => {
  if (!planAEliminar.value) return
  guardando.value = true
  try {
    const res = await fetch(`${API}/planes-estudio/${planAEliminar.value.id_plan}`, { method: 'DELETE', headers: { 'Accept': 'application/json' } })
    if (!res.ok) throw new Error()
    await cargarPlanes(); showModalEliminar.value = false; planAEliminar.value = null
    mostrarNotificacion('Plan eliminado correctamente.')
  } catch { mostrarNotificacion('Ocurrió un error al eliminar.', 'error') }
  finally { guardando.value = false }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.planes-page{--azul:#1B396A;--azul-hover:#1D4ED8;--azul-suave:#DBEAFE;--borde:#E5E7EB;--fondo:#F5F5F5;--texto:#1A1A1A;--gris:#6B7280;--verde:#16A34A;--rojo:#DC2626;width:100%;background:var(--fondo);font-family:'Montserrat',sans-serif;padding-bottom:2rem}

.breadcrumb{display:flex;align-items:center;gap:6px;color:var(--gris);font-size:0.88rem;margin-bottom:0.75rem}
.breadcrumb-link{color:var(--azul);font-weight:500;cursor:pointer;transition:color 0.15s}
.breadcrumb-link:hover{color:var(--azul-hover);text-decoration:underline}
.breadcrumb-sep{color:#9CA3AF}.breadcrumb-actual{color:var(--gris);font-weight:600}

.page-header{display:flex;flex-direction:column;gap:4px;margin-bottom:1.2rem}
.page-title{color:var(--texto);font-size:1.75rem;font-weight:700;letter-spacing:-0.02em;margin:0}
.page-subtitle{font-size:0.9rem;color:var(--gris);font-weight:500;margin:0}

.barra-carga{height:3px;background:transparent;border-radius:2px;margin-bottom:1rem;overflow:hidden;opacity:0;transition:opacity 0.3s}
.barra-carga.visible{opacity:1}
.barra-progreso{height:100%;width:40%;background:var(--azul);border-radius:2px;animation:deslizar 1.2s ease-in-out infinite}
@keyframes deslizar{0%{transform:translateX(-100%)}100%{transform:translateX(350%)}}

.toast{position:fixed;bottom:2rem;right:2rem;display:flex;align-items:center;gap:10px;padding:12px 20px;border-radius:10px;color:white;font-weight:500;font-size:0.93rem;box-shadow:0 6px 20px rgba(0,0,0,0.18);z-index:3000;max-width:380px}
.toast.exito{background:var(--azul)}.toast.error{background:var(--rojo)}.toast-icono{width:20px;height:20px;flex-shrink:0}
.toast-slide-enter-active,.toast-slide-leave-active{transition:all 0.35s ease}
.toast-slide-enter-from,.toast-slide-leave-to{opacity:0;transform:translateX(110%)}

/* Barra de acciones */
.actions-bar{display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;flex-wrap:wrap}
.search-group{position:relative;flex:1 1 260px;min-width:200px}
.search-input{width:100%;padding:10px 14px 10px 42px;border:1px solid var(--borde);border-radius:8px;font-size:0.93rem;background:#FFFFFF;color:var(--texto);font-family:'Montserrat',sans-serif;outline:none;transition:border-color 0.2s,box-shadow 0.2s;box-sizing:border-box}
.search-input:focus{border-color:var(--azul);box-shadow:0 0 0 3px #DBEAFE}
.search-input::placeholder{color:#9CA3AF}
.search-icon-svg{position:absolute;left:13px;top:50%;transform:translateY(-50%);width:18px;height:18px;stroke:var(--gris);pointer-events:none}
.btn-filtros{display:flex;align-items:center;gap:7px;background:#FFFFFF;color:var(--texto);border:1px solid var(--borde);padding:10px 16px;border-radius:8px;font-weight:600;cursor:pointer;font-size:0.92rem;font-family:'Montserrat',sans-serif;transition:background 0.15s,border-color 0.15s;white-space:nowrap;position:relative}
.btn-filtros svg{width:16px;height:16px;stroke:var(--gris)}
.btn-filtros:hover{background:var(--fondo)}
.btn-filtros.activo{border-color:var(--azul);color:var(--azul);background:var(--azul-suave)}
.btn-filtros.activo svg{stroke:var(--azul)}
.filtros-badge{background:var(--azul);color:white;font-size:0.72rem;font-weight:700;border-radius:10px;padding:1px 6px;margin-left:2px}
.btn-nuevo{background:var(--azul);color:white;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;white-space:nowrap;font-family:'Montserrat',sans-serif;font-size:0.92rem;transition:background 0.2s}
.btn-nuevo:hover{background:var(--azul-hover)}

/* Panel filtros */
.filtros-panel{background:#FFFFFF;border:1px solid var(--borde);border-radius:10px;padding:1.2rem 1.4rem 1rem;margin-bottom:1rem;box-shadow:0 4px 12px rgba(0,0,0,0.06)}
.filtros-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem}
.filtro-item{display:flex;flex-direction:column;gap:6px}
.filtro-label{font-size:0.82rem;font-weight:600;color:var(--gris);font-family:'Montserrat',sans-serif}
.filter-select{padding:9px 12px;border:1px solid var(--borde);border-radius:8px;font-size:0.9rem;background:#FFFFFF;color:var(--texto);font-family:'Montserrat',sans-serif;cursor:pointer;outline:none}
.filter-select:focus{border-color:var(--azul)}
.filtros-footer{display:flex;justify-content:flex-end;margin-top:0.9rem;padding-top:0.9rem;border-top:1px solid var(--borde)}
.btn-limpiar{display:flex;align-items:center;gap:6px;background:transparent;color:var(--gris);border:none;padding:6px 10px;border-radius:6px;font-weight:600;cursor:pointer;font-size:0.88rem;font-family:'Montserrat',sans-serif;transition:color 0.15s}
.btn-limpiar:hover{color:var(--rojo)}.reset-icon{width:14px;height:14px}
.filtros-slide-enter-active,.filtros-slide-leave-active{transition:all 0.25s ease;overflow:hidden}
.filtros-slide-enter-from,.filtros-slide-leave-to{opacity:0;transform:translateY(-8px)}

/* Tabla */
.table-container{background:#FFFFFF;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);border:1px solid var(--borde)}
.data-table{width:100%;border-collapse:collapse}
.data-table th{background:var(--fondo);padding:9px 12px;text-align:left;font-weight:600;font-size:0.82rem;color:var(--texto);border-bottom:1px solid var(--borde);font-family:'Montserrat',sans-serif;white-space:nowrap}
.th-centro,.td-centro{text-align:center}.th-acciones{text-align:center}
.data-table td{padding:8px 12px;border-bottom:1px solid var(--borde);color:var(--texto);font-size:0.88rem;font-family:'Montserrat',sans-serif}
.data-table tbody tr{transition:background 0.15s;cursor:pointer}
.data-table tbody tr:hover{background:#F8FAFC}
.data-table tbody tr:last-child td{border-bottom:none}
.fila-seleccionada{background:#DBEAFE!important}
.celda-nombre{font-weight:600;font-size:0.9rem}
.celda-secundaria{color:var(--gris);font-size:0.85rem}
.estatus-badge{display:inline-block;padding:3px 9px;border-radius:20px;font-size:0.78rem;font-weight:600}
.estatus-badge.activo{background:#DCFCE7;color:#16A34A}
.estatus-badge.inactivo{background:#F3F4F6;color:#6B7280}
.celda-acciones{display:flex;gap:5px;align-items:center;justify-content:center}
.btn-icono{display:flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:7px;cursor:pointer;border:1px solid transparent;transition:background 0.15s,border-color 0.15s;flex-shrink:0}
.btn-icono svg{width:14px;height:14px}
.btn-icono.ver{background:#F3F4F6;border-color:#D1D5DB}.btn-icono.ver:hover{background:#E5E7EB}.btn-icono.ver svg{stroke:#374151}
.btn-icono.editar{background:var(--azul);border-color:var(--azul)}.btn-icono.editar:hover{background:var(--azul-hover);border-color:var(--azul-hover)}.btn-icono.editar svg{stroke:#FFFFFF}
.estado-vacio,.estado-cargando{text-align:center;padding:3rem 2rem;color:var(--gris)}
.icono-vacio{width:52px;height:52px;stroke:#9CA3AF;margin-bottom:12px}
.estado-vacio h3{font-size:1.1rem;color:var(--texto);margin:0 0 6px}
.estado-vacio p{font-size:0.9rem;margin:0 0 1rem}
.btn-limpiar-vacio{background:#FFFFFF;color:var(--texto);border:1px solid var(--borde);padding:8px 18px;border-radius:8px;font-weight:500;cursor:pointer;font-family:'Montserrat',sans-serif}
.spinner-tabla{display:inline-block;width:32px;height:32px;border:3px solid #E5E7EB;border-top-color:var(--azul);border-radius:50%;animation:girar 0.8s linear infinite;margin-bottom:12px}

/* Paginación */
.paginacion{margin-top:1.2rem;display:flex;justify-content:space-between;align-items:center;font-size:0.9rem;color:var(--gris);font-family:'Montserrat',sans-serif;flex-wrap:wrap;gap:0.5rem}
.paginacion-izquierda,.paginacion-centro,.paginacion-derecha{display:flex;align-items:center;gap:8px}
.select-filas{border:1px solid var(--borde);border-radius:6px;padding:4px 8px;font-size:0.9rem;background:#FFFFFF;font-family:'Montserrat',sans-serif}
.btn-pag{padding:5px 11px;border:1px solid var(--borde);background:#FFFFFF;border-radius:6px;cursor:pointer;color:var(--texto);font-family:'Montserrat',sans-serif;font-size:0.9rem;transition:background 0.15s}
.btn-pag:hover:not(:disabled){background:var(--fondo)}.btn-pag:disabled{opacity:0.4;cursor:not-allowed}
.btn-pag.activo{background:var(--azul);color:white;border-color:var(--azul)}
.pie-pagina{text-align:center;color:#9CA3AF;font-size:0.82rem;padding-top:2rem;border-top:1px solid #E5E7EB;margin-top:1rem}

/* Modales */
.modal-fade-enter-active,.modal-fade-leave-active{transition:opacity 0.22s ease}
.modal-fade-enter-from,.modal-fade-leave-to{opacity:0}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:2000;padding:1rem}
.modal-content{background:#FFFFFF;width:540px;max-width:100%;max-height:90vh;border-radius:16px;box-shadow:0 24px 60px rgba(0,0,0,0.2);overflow:hidden;border:1px solid #E5E7EB;display:flex;flex-direction:column}
.modal-ver{width:560px}
.modal-confirmar{width:440px}
.modal-header{background:#1B396A;color:white;padding:1rem 1.4rem;display:flex;justify-content:space-between;align-items:flex-start;flex-shrink:0}
.modal-header-info{display:flex;flex-direction:column;gap:2px}
.modal-header-tag{font-size:0.72rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;opacity:0.7}
.modal-header h3{margin:0;font-size:1.15rem;font-weight:700;font-family:'Montserrat',sans-serif}
.btn-cerrar-modal{background:none;border:none;color:white;font-size:1.7rem;cursor:pointer;line-height:1;opacity:0.8;padding:0;flex-shrink:0}
.btn-cerrar-modal:hover{opacity:1}
.modal-body{padding:1.5rem 1.6rem;overflow-y:auto}

/* Grid de detalle modal Ver */
.detalle-grid{display:grid;grid-template-columns:1fr 1fr;gap:0.9rem}
.detalle-campo{display:flex;flex-direction:column;gap:4px;padding:0.75rem;background:#F8FAFC;border-radius:8px;border:1px solid #E5E7EB}
.detalle-label{font-size:0.78rem;font-weight:600;color:var(--gris);text-transform:uppercase;letter-spacing:0.05em}
.detalle-valor{font-size:0.95rem;font-weight:500;color:var(--texto)}
.detalle-numero{font-size:1.3rem;font-weight:700;color:var(--azul)}

/* Formulario */
.form-grupo{margin-bottom:1.2rem}
.form-grupo-doble{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.form-grupo label{display:block;margin-bottom:6px;font-weight:600;font-size:0.9rem;color:#1A1A1A;font-family:'Montserrat',sans-serif}
.obligatorio{color:#DC2626}
.modal-input,.modal-select{width:100%;padding:10px 14px;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.95rem;background:#FFFFFF;color:#1A1A1A;font-family:'Montserrat',sans-serif;outline:none;transition:border-color 0.2s;box-sizing:border-box}
.modal-input:focus,.modal-select:focus{border-color:#1B396A;box-shadow:0 0 0 3px #DBEAFE}
.borde-error{border-color:#DC2626!important}.mensaje-error{display:block;color:#DC2626;font-size:0.82rem;margin-top:5px}
.indicador-estatus{display:inline-flex;align-items:center;margin-top:7px;padding:4px 12px;border-radius:20px;font-size:0.82rem;font-weight:600}
.indicador-estatus.activo{background:#DCFCE7;color:#16A34A}.indicador-estatus.inactivo{background:#F3F4F6;color:#6B7280}
.modal-footer{padding:1rem 1.6rem;background:#F8FAFC;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid #E5E7EB;flex-shrink:0}
.btn-secundario{padding:9px 20px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;background:#FFFFFF;color:#1B396A;border:2px solid #1B396A;transition:background 0.15s}
.btn-secundario:hover{background:#DBEAFE}.btn-secundario:disabled{opacity:0.5;cursor:not-allowed}
.btn-eliminar{padding:9px 20px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;background:#DC2626;color:white;border:2px solid #DC2626;transition:background 0.15s}
.btn-eliminar:hover{background:#B91C1C}.btn-eliminar:disabled{opacity:0.5;cursor:not-allowed}
.btn-guardar{display:flex;align-items:center;gap:8px;padding:9px 20px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;background:#1B396A;color:#FFFFFF;border:2px solid #1B396A;transition:background 0.15s}
.btn-guardar:hover:not(:disabled){background:#1D4ED8}.btn-guardar:disabled{opacity:0.55;cursor:not-allowed}
.spinner-btn{display:inline-block;width:15px;height:15px;border:2px solid rgba(255,255,255,0.4);border-top-color:white;border-radius:50%;animation:girar 0.7s linear infinite;flex-shrink:0}
.confirmar-body{display:flex;flex-direction:column;align-items:center;gap:1rem;text-align:center;padding:2rem 1.6rem}
.confirmar-icono{width:52px;height:52px;stroke:#F59E0B}
.confirmar-body p{color:#1A1A1A;font-size:0.95rem;margin:0;line-height:1.5;font-family:'Montserrat',sans-serif}
@keyframes girar{to{transform:rotate(360deg)}}

@media(max-width:600px){
  .page-title{font-size:1.4rem}
  .detalle-grid{grid-template-columns:1fr}
  .form-grupo-doble{grid-template-columns:1fr}
}
</style>