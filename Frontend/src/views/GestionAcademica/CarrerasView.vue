<template>
  <MainLayout>
    <div class="carreras-page">

      <!-- Breadcrumb -->
      <nav class="breadcrumb">
        <span class="breadcrumb-link" @click="$router.push('/gestion-academica')">Gestión Académica</span>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-actual">Carreras</span>
      </nav>

      <!-- Encabezado -->
      <div class="page-header">
        <h1 class="page-title">Carreras</h1>
        <span class="page-subtitle">{{ carrerasFiltradas.length }} registro(s) encontrado(s)</span>
      </div>

      <!-- Barra de carga -->
      <div class="barra-carga" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- Toast -->
      <transition name="toast-slide">
        <div v-if="notificacion.visible" class="toast" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo === 'exito'" xmlns="http://www.w3.org/2000/svg" class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- ══════════════════════════════════════════════════
           BARRA DE ACCIONES
           Cambio: El campo de búsqueda es visible directamente.
           El botón "Filtros" (embudo) solo agrupa los filtros
           secundarios (Departamento, Nivel, Estatus) y está
           colapsado por defecto. Si no hay filtros activos,
           el ícono se muestra discreto; si hay activos, se resalta.
      ══════════════════════════════════════════════════ -->
      <div class="actions-bar">

        <!-- Búsqueda principal — siempre visible -->
        <div class="search-group">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Buscar por nombre de carrera..."
            v-model="busqueda"
            class="search-input"
            @keydown.escape="busqueda = ''"
          >
        </div>

        <!-- Botón embudo: agrupa filtros secundarios, colapsado por defecto -->
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

        <button class="btn-nuevo" @click="abrirModalNuevo">+ Nueva carrera</button>
      </div>

      <!-- Panel de filtros secundarios (colapsado por defecto) -->
      <transition name="filtros-slide">
        <div v-if="mostrarFiltros" class="filtros-panel">
          <div class="filtros-grid">
            <div class="filtro-item">
              <label class="filtro-label">Departamento</label>
              <select v-model="filtroDepartamento" class="filter-select">
                <option value="">Todos</option>
                <option v-for="dep in departamentos" :key="dep.id_departamento" :value="dep.id_departamento">
                  {{ dep.nombre }}
                </option>
              </select>
            </div>
            <div class="filtro-item">
              <label class="filtro-label">Nivel</label>
              <select v-model="filtroNivel" class="filter-select">
                <option value="">Todos</option>
                <option v-for="niv in niveles" :key="niv.id_nivel" :value="niv.id_nivel">
                  {{ niv.nombre_nivel }}
                </option>
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

      <!-- Tabla -->
      <div class="table-container">
        <div v-if="cargando && carreras.length === 0" class="estado-cargando">
          <div class="spinner-tabla"></div>
          <p>Cargando registros...</p>
        </div>

        <table v-else-if="carrerasPaginadas.length > 0" class="data-table">
          <thead>
            <tr>
              <th>Nombre de la Carrera</th>
              <th>Departamento</th>
              <th>Nivel</th>
              <th>Estatus</th>
              <th class="th-acciones">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(carrera, index) in carrerasPaginadas"
              :key="carrera.id_carrera"
              :class="{ 'fila-seleccionada': filaActiva === index }"
              @click="filaActiva = index"
            >
              <td class="celda-nombre">{{ carrera.nombre }}</td>
              <td>{{ carrera.departamento?.nombre || '—' }}</td>
              <td>{{ carrera.nivel?.nombre_nivel || '—' }}</td>
              <td>
                <span class="estatus-badge" :class="carrera.estatus ? 'activo' : 'inactivo'">
                  {{ carrera.estatus ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td class="celda-acciones">
                <button class="btn-icono ver" @click.stop="abrirModalVer(carrera)" title="Ver detalles">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
                <button class="btn-icono editar" @click.stop="abrirModalEditar(carrera)" title="Editar">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3>Sin resultados</h3>
          <p>No se encontraron carreras con los criterios aplicados.</p>
          <button class="btn-limpiar-vacio" @click="limpiarFiltros">Limpiar filtros</button>
        </div>
      </div>

      <!-- Paginación -->
      <div class="paginacion">
        <!-- ── CORRECCIÓN: Ocultar selector "Filas por página" cuando hay ≤ 5 registros ── -->
        <div class="paginacion-izquierda" v-if="carrerasFiltradas.length > 5">
          Filas por página:
          <select v-model="filasPorPagina" @change="currentPage = 1" class="select-filas">
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
        </div>
        <div class="paginacion-izquierda" v-else></div>
        <div class="paginacion-centro">Página {{ currentPage }} de {{ totalPages }}</div>
        <div class="paginacion-derecha">
          <button class="btn-pag" @click="prevPage" :disabled="currentPage === 1">‹</button>
          <button
            v-for="page in visiblePages" :key="page"
            class="btn-pag" :class="{ activo: page === currentPage }"
            @click="goToPage(page)"
          >{{ page }}</button>
          <button class="btn-pag" @click="nextPage" :disabled="currentPage === totalPages">›</button>
        </div>
      </div>

      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México | Todos los derechos reservados</footer>
    </div>

  </MainLayout>

  <!-- Modales con Teleport para centrado garantizado independientemente del layout -->

  <!-- MODAL VER DETALLE -->
  <Teleport to="body">
    <transition name="modal-fade">
      <div v-if="showModalVer" class="modal-overlay" @click.self="showModalVer = false">
        <div class="modal-content modal-ver">
          <div class="modal-header">
            <div class="modal-header-info">
              <span class="modal-header-tag">Carrera</span>
              <h3>{{ carreraVer.nombre }}</h3>
            </div>
            <button @click="showModalVer = false" class="btn-cerrar-modal" aria-label="Cerrar">×</button>
          </div>
          <div class="modal-body">
            <div class="detalle-grid">
              <div class="detalle-campo">
                <span class="detalle-label">Departamento</span>
                <span class="detalle-valor">{{ carreraVer.departamento?.nombre || '—' }}</span>
              </div>
              <div class="detalle-campo">
                <span class="detalle-label">Nivel académico</span>
                <span class="detalle-valor">{{ carreraVer.nivel?.nombre_nivel || '—' }}</span>
              </div>
              <div class="detalle-campo">
                <span class="detalle-label">Estatus</span>
                <span class="estatus-badge" :class="carreraVer.estatus ? 'activo' : 'inactivo'">
                  {{ carreraVer.estatus ? 'Activo' : 'Inactivo' }}
                </span>
              </div>
              <div v-if="carreraVer.id_carrera" class="detalle-campo">
                <span class="detalle-label">ID interno</span>
                <span class="detalle-valor detalle-mono">{{ carreraVer.id_carrera }}</span>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secundario" @click="showModalVer = false">Cerrar</button>
            <button class="btn-guardar" @click="abrirModalEditar(carreraVer); showModalVer = false">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Editar
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
              <span class="modal-header-tag">{{ form.id_carrera ? 'Edición' : 'Nueva' }}</span>
              <h3>{{ form.id_carrera ? 'Editar Carrera' : 'Nueva Carrera' }}</h3>
            </div>
            <button @click="cerrarModal" class="btn-cerrar-modal">×</button>
          </div>
          <div class="modal-body">
            <div class="form-grupo">
              <label>Nombre de la carrera <span class="obligatorio">*</span></label>
              <input v-model.trim="form.nombre" type="text" maxlength="150"
                     class="modal-input" :class="{ 'borde-error': errors.nombre }"
                     placeholder="Ej. Ingeniería en Sistemas Computacionales">
              <small v-if="errors.nombre" class="mensaje-error">{{ errors.nombre }}</small>
            </div>
            <div class="form-grupo">
              <label>Departamento <span class="obligatorio">*</span></label>
              <select v-model="form.id_departamento" class="modal-select"
                      :class="{ 'borde-error': errors.id_departamento }">
                <option value="">Seleccionar departamento</option>
                <option v-for="dep in departamentos" :key="dep.id_departamento" :value="dep.id_departamento">
                  {{ dep.nombre }}
                </option>
              </select>
              <small v-if="errors.id_departamento" class="mensaje-error">{{ errors.id_departamento }}</small>
            </div>
            <div class="form-grupo">
              <label>Nivel académico <span class="obligatorio">*</span></label>
              <select v-model="form.id_nivel" class="modal-select"
                      :class="{ 'borde-error': errors.id_nivel }">
                <option value="">Seleccionar nivel</option>
                <option v-for="niv in niveles" :key="niv.id_nivel" :value="niv.id_nivel">
                  {{ niv.nombre_nivel }}
                </option>
              </select>
              <small v-if="errors.id_nivel" class="mensaje-error">{{ errors.id_nivel }}</small>
            </div>
            <div class="form-grupo">
              <label>Estatus</label>
              <select v-model="form.estatus" class="modal-select">
                <option :value="1">Activo</option>
                <option :value="0">Inactivo</option>
              </select>
              <div class="indicador-estatus" :class="form.estatus ? 'activo' : 'inactivo'">
                {{ form.estatus ? 'Activo' : 'Inactivo' }}
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">Cancelar</button>
            <button v-if="form.id_carrera" class="btn-eliminar" @click="solicitarEliminar" :disabled="guardando">Eliminar</button>
            <button class="btn-guardar" @click="guardar" :disabled="guardando">
              <span v-if="guardando" class="spinner-btn"></span>
              {{ guardando ? 'Guardando...' : 'Guardar' }}
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
              <span class="modal-header-tag">Confirmar</span>
              <h3>Eliminar carrera</h3>
            </div>
            <button @click="showModalEliminar = false" class="btn-cerrar-modal">×</button>
          </div>
          <div class="modal-body confirmar-body">
            <svg xmlns="http://www.w3.org/2000/svg" class="confirmar-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p>¿Estás seguro de eliminar la carrera <strong>{{ carreraAEliminar?.nombre }}</strong>? Esta acción no se puede deshacer.</p>
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
  </Teleport>

</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`

// ── Estado ──────────────────────────────────────────────
const carreras        = ref([])
const departamentos   = ref([])
const niveles         = ref([])
const cargando        = ref(false)
const guardando       = ref(false)
const filaActiva      = ref(-1)
const currentPage     = ref(1)
const filasPorPagina  = ref(10)

// El panel de filtros secundarios está colapsado por defecto
const mostrarFiltros  = ref(false)

const busqueda           = ref('')
const filtroDepartamento = ref('')
const filtroNivel        = ref('')
const filtroEstatus      = ref('')

const showModal         = ref(false)
const showModalVer      = ref(false)
const showModalEliminar = ref(false)
const carreraVer        = ref({})
const carreraAEliminar  = ref(null)

const form   = reactive({ id_carrera: null, nombre: '', id_departamento: '', id_nivel: '', estatus: 1 })
const errors = reactive({})

const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

// ── Computadas ───────────────────────────────────────────
const filtrosActivos  = computed(() => !!(filtroDepartamento.value || filtroNivel.value || filtroEstatus.value))
const contadorFiltros = computed(() => [filtroDepartamento.value, filtroNivel.value, filtroEstatus.value].filter(Boolean).length)

const carrerasFiltradas = computed(() =>
  carreras.value.filter(c => {
    const n = (t) => t?.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '') ?? ''
    const coincideBusqueda = !busqueda.value || n(c.nombre).includes(n(busqueda.value))
    const coincideDep      = !filtroDepartamento.value || c.id_departamento == filtroDepartamento.value
    const coincideNivel    = !filtroNivel.value || c.id_nivel == filtroNivel.value
    const coincideEstatus  = !filtroEstatus.value || String(c.estatus) === filtroEstatus.value
    return coincideBusqueda && coincideDep && coincideNivel && coincideEstatus
  })
)

const totalPages      = computed(() => Math.ceil(carrerasFiltradas.value.length / filasPorPagina.value) || 1)
const carrerasPaginadas = computed(() => {
  const start = (currentPage.value - 1) * filasPorPagina.value
  return carrerasFiltradas.value.slice(start, start + filasPorPagina.value)
})
const visiblePages = computed(() => {
  const total = totalPages.value
  const cur   = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = new Set([1, total, cur, cur - 1, cur + 1].filter(p => p >= 1 && p <= total))
  return [...pages].sort((a, b) => a - b)
})

// ── Métodos ──────────────────────────────────────────────
const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

const cargarCarreras = async () => {
  cargando.value = true
  try {
    const res = await fetch(`${API}/carreras`)
    if (!res.ok) throw new Error('Error del servidor')
    carreras.value = await res.json()
  } catch {
    mostrarNotificacion('No se pudieron cargar las carreras.', 'error')
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
    niveles.value       = await resNiv.json()
  } catch (e) {
    console.error('Error cargando catálogos:', e)
  }
}

onMounted(() => { cargarCarreras(); cargarCatalogos() })

const goToPage     = (p) => { currentPage.value = p; filaActiva.value = -1 }
const prevPage     = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage     = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

const limpiarFiltros = () => {
  busqueda.value = ''
  filtroDepartamento.value = ''
  filtroNivel.value = ''
  filtroEstatus.value = ''
  currentPage.value = 1
  filaActiva.value = -1
}

const resetForm = () => {
  form.id_carrera = null; form.nombre = ''; form.id_departamento = ''; form.id_nivel = ''; form.estatus = 1
  Object.keys(errors).forEach(k => delete errors[k])
}

const abrirModalNuevo  = () => { resetForm(); showModal.value = true }
const abrirModalVer    = (c) => { carreraVer.value = c; showModalVer.value = true }
const abrirModalEditar = (c) => {
  resetForm()
  form.id_carrera      = c.id_carrera
  form.nombre          = c.nombre
  form.id_departamento = c.id_departamento
  form.id_nivel        = c.id_nivel
  form.estatus         = c.estatus
  showModal.value      = true
}
const cerrarModal = () => { showModal.value = false; resetForm() }

const solicitarEliminar = () => {
  carreraAEliminar.value  = { id_carrera: form.id_carrera, nombre: form.nombre }
  showModal.value         = false
  showModalEliminar.value = true
}

const validar = () => {
  Object.keys(errors).forEach(k => delete errors[k])
  if (!form.nombre.trim())   errors.nombre          = 'El nombre es obligatorio'
  if (!form.id_departamento) errors.id_departamento = 'Selecciona un departamento'
  if (!form.id_nivel)        errors.id_nivel        = 'Selecciona un nivel'
  return Object.keys(errors).length === 0
}

const guardar = async () => {
  if (!validar()) return
  guardando.value = true
  const esEdicion = !!form.id_carrera
  const url    = esEdicion ? `${API}/carreras/${form.id_carrera}` : `${API}/carreras`
  const method = esEdicion ? 'PUT' : 'POST'
  const payload = { nombre: form.nombre.trim(), id_departamento: form.id_departamento, id_nivel: form.id_nivel, estatus: form.estatus }
  try {
    const res = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(payload) })
    if (!res.ok) throw new Error('Error del servidor')
    await cargarCarreras(); cerrarModal()
    mostrarNotificacion(esEdicion ? 'Carrera actualizada correctamente.' : 'Carrera creada correctamente.')
  } catch {
    mostrarNotificacion('Ocurrió un error al guardar.', 'error')
  } finally {
    guardando.value = false
  }
}

const confirmarEliminar = async () => {
  if (!carreraAEliminar.value) return
  guardando.value = true
  try {
    const res = await fetch(`${API}/carreras/${carreraAEliminar.value.id_carrera}`, { method: 'DELETE', headers: { 'Accept': 'application/json' } })
    if (!res.ok) throw new Error('Error del servidor')
    await cargarCarreras(); showModalEliminar.value = false; carreraAEliminar.value = null
    mostrarNotificacion('Carrera eliminada correctamente.')
  } catch {
    mostrarNotificacion('Ocurrió un error al eliminar.', 'error')
  } finally {
    guardando.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* ── Variables y base ─────────────────────────────────── */
.carreras-page {
  --azul:       #1B396A;
  --azul-hover: #1D4ED8;
  --azul-suave: #DBEAFE;
  --borde:      #E5E7EB;
  --fondo:      #F5F5F5;
  --texto:      #1A1A1A;
  --gris:       #6B7280;
  --verde:      #16A34A;
  --rojo:       #DC2626;
  width: 100%;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
  padding-bottom: 2rem;
}

/* ── Breadcrumb ──────────────────────────────────────── */
.breadcrumb { display: flex; align-items: center; gap: 6px; color: var(--gris); font-size: 0.88rem; margin-bottom: 0.75rem; }
.breadcrumb-link { color: var(--azul); font-weight: 500; cursor: pointer; transition: color 0.15s; }
.breadcrumb-link:hover { color: var(--azul-hover); text-decoration: underline; }
.breadcrumb-sep { color: #9CA3AF; }
.breadcrumb-actual { color: var(--gris); font-weight: 600; }

/* ── Encabezado ──────────────────────────────────────── */
.page-header { display: flex; flex-direction: column; gap: 4px; margin-bottom: 1.2rem; }
.page-title { color: var(--texto); font-size: 1.75rem; font-weight: 700; letter-spacing: -0.02em; margin: 0; }
.page-subtitle { font-size: 0.9rem; color: var(--gris); font-weight: 500; margin: 0; }

/* ── Barra de carga ──────────────────────────────────── */
.barra-carga { height: 3px; background: transparent; border-radius: 2px; margin-bottom: 1rem; overflow: hidden; opacity: 0; transition: opacity 0.3s; }
.barra-carga.visible { opacity: 1; }
.barra-progreso { height: 100%; width: 40%; background: var(--azul); border-radius: 2px; animation: deslizar 1.2s ease-in-out infinite; }
@keyframes deslizar { 0% { transform: translateX(-100%); } 100% { transform: translateX(350%); } }

/* ── Toast ───────────────────────────────────────────── */
.toast { position: fixed; bottom: 2rem; right: 2rem; display: flex; align-items: center; gap: 10px; padding: 12px 20px; border-radius: 10px; color: white; font-weight: 500; font-size: 0.93rem; box-shadow: 0 6px 20px rgba(0,0,0,0.18); z-index: 3000; max-width: 380px; }
.toast.exito { background: var(--azul); }
.toast.error { background: var(--rojo); }
.toast-icono { width: 20px; height: 20px; flex-shrink: 0; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all 0.35s ease; }
.toast-slide-enter-from, .toast-slide-leave-to { opacity: 0; transform: translateX(110%); }

/* ── Barra de acciones ────────────────────────────────── */
.actions-bar { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; flex-wrap: wrap; }

/* Búsqueda: ocupa el espacio disponible */
.search-group { position: relative; flex: 1 1 260px; min-width: 200px; }
.search-input { width: 100%; padding: 10px 14px 10px 42px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.93rem; background: #FFFFFF; color: var(--texto); font-family: 'Montserrat', sans-serif; outline: none; transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box; }
.search-input:focus { border-color: var(--azul); box-shadow: 0 0 0 3px #DBEAFE; }
.search-input::placeholder { color: #9CA3AF; }
.search-icon-svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; stroke: var(--gris); pointer-events: none; }

/* Botón filtros (embudo) */
.btn-filtros { display: flex; align-items: center; gap: 7px; background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); padding: 10px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.92rem; font-family: 'Montserrat', sans-serif; transition: background 0.15s, border-color 0.15s; white-space: nowrap; position: relative; }
.btn-filtros svg { width: 16px; height: 16px; stroke: var(--gris); }
.btn-filtros:hover { background: var(--fondo); }
.btn-filtros.activo { border-color: var(--azul); color: var(--azul); background: var(--azul-suave); }
.btn-filtros.activo svg { stroke: var(--azul); }
.filtros-badge { background: var(--azul); color: white; font-size: 0.72rem; font-weight: 700; border-radius: 10px; padding: 1px 6px; margin-left: 2px; }

/* Botón nuevo */
.btn-nuevo { background: var(--azul); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; white-space: nowrap; font-family: 'Montserrat', sans-serif; font-size: 0.92rem; transition: background 0.2s; }
.btn-nuevo:hover { background: var(--azul-hover); }

/* ── Panel filtros desplegable ─────────────────────────── */
.filtros-panel { background: #FFFFFF; border: 1px solid var(--borde); border-radius: 10px; padding: 1.2rem 1.4rem 1rem; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
.filtros-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; }
.filtro-item { display: flex; flex-direction: column; gap: 6px; }
.filtro-label { font-size: 0.82rem; font-weight: 600; color: var(--gris); font-family: 'Montserrat', sans-serif; }
.filter-select { padding: 9px 12px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.9rem; background: #FFFFFF; color: var(--texto); font-family: 'Montserrat', sans-serif; cursor: pointer; outline: none; }
.filter-select:focus { border-color: var(--azul); }
.filtros-footer { display: flex; justify-content: flex-end; margin-top: 0.9rem; padding-top: 0.9rem; border-top: 1px solid var(--borde); }
.btn-limpiar { display: flex; align-items: center; gap: 6px; background: transparent; color: var(--gris); border: none; padding: 6px 10px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.88rem; font-family: 'Montserrat', sans-serif; transition: color 0.15s; }
.btn-limpiar:hover { color: var(--rojo); }
.reset-icon { width: 14px; height: 14px; }
.filtros-slide-enter-active, .filtros-slide-leave-active { transition: all 0.25s ease; overflow: hidden; }
.filtros-slide-enter-from, .filtros-slide-leave-to { opacity: 0; transform: translateY(-8px); }

/* ── Tabla ────────────────────────────────────────────── */
.table-container { background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid var(--borde); }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: var(--fondo); padding: 11px 14px; text-align: left; font-weight: 600; font-size: 0.85rem; color: var(--texto); border-bottom: 1px solid var(--borde); font-family: 'Montserrat', sans-serif; white-space: nowrap; }
.th-acciones { text-align: center; }
.data-table td { padding: 10px 14px; border-bottom: 1px solid var(--borde); color: var(--texto); font-size: 0.92rem; font-family: 'Montserrat', sans-serif; }
.data-table tbody tr { transition: background 0.15s; cursor: pointer; }
.data-table tbody tr:hover { background: #F8FAFC; }
.data-table tbody tr:last-child td { border-bottom: none; }
.fila-seleccionada { background: #DBEAFE !important; }
.celda-nombre { font-weight: 600; }

.estatus-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
.estatus-badge.activo   { background: #DCFCE7; color: #16A34A; }
.estatus-badge.inactivo { background: #F3F4F6; color: #6B7280; }

/* Botones ícono en tabla */
.celda-acciones { display: flex; gap: 6px; align-items: center; justify-content: center; }
.btn-icono { display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 7px; cursor: pointer; border: 1px solid transparent; transition: background 0.15s, border-color 0.15s; flex-shrink: 0; }
.btn-icono svg { width: 15px; height: 15px; }
.btn-icono.ver { background: #F3F4F6; border-color: #D1D5DB; }
.btn-icono.ver:hover { background: #E5E7EB; }
.btn-icono.ver svg { stroke: #374151; }
.btn-icono.editar { background: var(--azul); border-color: var(--azul); }
.btn-icono.editar:hover { background: var(--azul-hover); border-color: var(--azul-hover); }
.btn-icono.editar svg { stroke: #FFFFFF; }

.estado-vacio, .estado-cargando { text-align: center; padding: 3.5rem 2rem; color: var(--gris); }
.icono-vacio { width: 56px; height: 56px; stroke: #9CA3AF; margin-bottom: 12px; }
.estado-vacio h3 { font-size: 1.2rem; color: var(--texto); margin: 0 0 6px; }
.estado-vacio p  { font-size: 0.93rem; margin: 0 0 1.2rem; }
.btn-limpiar-vacio { background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); padding: 9px 20px; border-radius: 8px; font-weight: 500; cursor: pointer; font-family: 'Montserrat', sans-serif; }
.spinner-tabla { display: inline-block; width: 36px; height: 36px; border: 3px solid #E5E7EB; border-top-color: var(--azul); border-radius: 50%; animation: girar 0.8s linear infinite; margin-bottom: 12px; }

/* ── Paginación ────────────────────────────────────────── */
.paginacion { margin-top: 1.2rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; color: var(--gris); font-family: 'Montserrat', sans-serif; flex-wrap: wrap; gap: 0.5rem; }
.paginacion-izquierda, .paginacion-centro, .paginacion-derecha { display: flex; align-items: center; gap: 8px; }
.select-filas { border: 1px solid var(--borde); border-radius: 6px; padding: 4px 8px; font-size: 0.9rem; background: #FFFFFF; font-family: 'Montserrat', sans-serif; }
.btn-pag { padding: 5px 11px; border: 1px solid var(--borde); background: #FFFFFF; border-radius: 6px; cursor: pointer; color: var(--texto); font-family: 'Montserrat', sans-serif; font-size: 0.9rem; transition: background 0.15s; }
.btn-pag:hover:not(:disabled) { background: var(--fondo); }
.btn-pag:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-pag.activo { background: var(--azul); color: white; border-color: var(--azul); }

/* ── Footer ────────────────────────────────────────────── */
.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem; border-top: 1px solid var(--borde); margin-top: 1rem; }

/* ── Modales ────────────────────────────────────────────── */

/* Animación de entrada/salida de modales */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.22s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center;
  z-index: 2000;
  padding: 1rem; /* margen en móvil */
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

/* Modal de ver: un poco más ancho para mostrar datos en grid */
.modal-ver { width: 580px; }

/* Modal de confirmación: más estrecho */
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
.modal-header h3 { margin: 0; font-size: 1.15rem; font-weight: 700; font-family: 'Montserrat', sans-serif; }
.btn-cerrar-modal { background: none; border: none; color: white; font-size: 1.7rem; cursor: pointer; line-height: 1; opacity: 0.8; padding: 0; flex-shrink: 0; }
.btn-cerrar-modal:hover { opacity: 1; }

.modal-body { padding: 1.5rem 1.6rem; overflow-y: auto; }

/* Grid de detalle (modal Ver) */
.detalle-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.detalle-campo { display: flex; flex-direction: column; gap: 4px; padding: 0.75rem; background: #F8FAFC; border-radius: 8px; border: 1px solid #E5E7EB; }
.detalle-label { font-size: 0.78rem; font-weight: 600; color: var(--gris); text-transform: uppercase; letter-spacing: 0.05em; }
.detalle-valor { font-size: 0.95rem; font-weight: 500; color: var(--texto); }
.detalle-mono  { font-family: monospace; font-size: 0.88rem; color: var(--azul); }

/* Formulario */
.form-grupo { margin-bottom: 1.2rem; }
.form-grupo label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: #1A1A1A; font-family: 'Montserrat', sans-serif; }
.obligatorio { color: #DC2626; }
.modal-input, .modal-select { width: 100%; padding: 10px 14px; border: 1.5px solid #E5E7EB; border-radius: 8px; font-size: 0.95rem; background: #FFFFFF; color: #1A1A1A; font-family: 'Montserrat', sans-serif; outline: none; transition: border-color 0.2s; box-sizing: border-box; }
.modal-input:focus, .modal-select:focus { border-color: #1B396A; box-shadow: 0 0 0 3px #DBEAFE; }
.borde-error { border-color: #DC2626 !important; }
.mensaje-error { display: block; color: #DC2626; font-size: 0.82rem; margin-top: 5px; }

.indicador-estatus { display: inline-flex; align-items: center; margin-top: 7px; padding: 4px 12px; border-radius: 20px; font-size: 0.82rem; font-weight: 600; }
.indicador-estatus.activo   { background: #DCFCE7; color: #16A34A; }
.indicador-estatus.inactivo { background: #F3F4F6; color: #6B7280; }

/* Footer del modal */
.modal-footer { padding: 1rem 1.6rem; background: #F8FAFC; display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid #E5E7EB; flex-shrink: 0; }

.btn-secundario { padding: 9px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #FFFFFF; color: #1B396A; border: 2px solid #1B396A; transition: background 0.15s; }
.btn-secundario:hover { background: #DBEAFE; }
.btn-secundario:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-eliminar { padding: 9px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #DC2626; color: white; border: 2px solid #DC2626; transition: background 0.15s; }
.btn-eliminar:hover { background: #B91C1C; }
.btn-eliminar:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-guardar { display: flex; align-items: center; gap: 8px; padding: 9px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #1B396A; color: #FFFFFF; border: 2px solid #1B396A; transition: background 0.15s; }
.btn-guardar:hover:not(:disabled) { background: #1D4ED8; }
.btn-guardar:disabled { opacity: 0.55; cursor: not-allowed; }

.spinner-btn { display: inline-block; width: 15px; height: 15px; border: 2px solid rgba(255,255,255,0.4); border-top-color: white; border-radius: 50%; animation: girar 0.7s linear infinite; flex-shrink: 0; }

/* Modal confirmar */
.confirmar-body { display: flex; flex-direction: column; align-items: center; gap: 1rem; text-align: center; padding: 2rem 1.6rem; }
.confirmar-icono { width: 52px; height: 52px; stroke: #F59E0B; }
.confirmar-body p { color: #1A1A1A; font-size: 0.95rem; margin: 0; line-height: 1.5; font-family: 'Montserrat', sans-serif; }

@keyframes girar { to { transform: rotate(360deg); } }

/* ── Responsivo ───────────────────────────────────────── */
@media (max-width: 600px) {
  .page-title { font-size: 1.4rem; }
  .actions-bar { flex-wrap: wrap; }
  .detalle-grid { grid-template-columns: 1fr; }
  .modal-content { border-radius: 12px; }
}
</style>