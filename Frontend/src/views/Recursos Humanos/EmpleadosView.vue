<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="empleados-page">

      <!-- Breadcrumb -->
      <nav class="breadcrumb">
        <router-link to="/inicio" class="breadcrumb-link">Inicio</router-link>
        <span class="sep">›</span>
        <router-link to="/recursos-humanos" class="breadcrumb-link">Recursos Humanos</router-link>
        <span class="sep">›</span>
        <span class="breadcrumb-actual">Empleados</span>
      </nav>

      <!-- Encabezado -->
      <div class="page-header">
        <h1 class="page-title">Empleados</h1>
        <span class="page-subtitle">{{ empleadosFiltrados.length }} registro(s) encontrado(s)</span>
      </div>

      <!-- Barra de carga -->
      <div class="barra-carga-global" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- Notificación -->
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

      <!-- Barra de filtros -->
      <div class="filters-bar">
        <div class="search-group">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Buscar por número de empleado o nombre..."
            v-model="busquedaEmpleado"
            class="search-input"
            @keydown.enter="aplicarBusqueda"
            @keydown.escape="busquedaEmpleado = ''"
          >
          <span v-if="cargandoBusqueda" class="spinner-busqueda"></span>
        </div>

        <select v-model="filtroPuesto" class="filter-select" @change="currentPage = 1">
          <option value="">Puesto</option>
          <option v-for="p in puestosDisponibles" :key="p" :value="p">{{ p }}</option>
        </select>

        <select v-model="filtroDepartamento" class="filter-select" @change="currentPage = 1">
          <option value="">Departamento</option>
          <option v-for="d in departamentosDisponibles" :key="d" :value="d">{{ d }}</option>
        </select>

        <select v-model="filtroEstatus" class="filter-select" @change="currentPage = 1">
          <option value="">Estatus</option>
          <option value="Activo">Activo</option>
          <option value="Inactivo">Inactivo</option>
        </select>

        <button class="btn-limpiar" @click="resetFilters">
          <svg xmlns="http://www.w3.org/2000/svg" class="reset-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Limpiar
        </button>

        <button class="btn-nuevo" @click="nuevoEmpleado">+ Nuevo Empleado</button>
      </div>

      <!-- Tabla -->
      <div class="table-container">

        <div v-if="cargando && empleados.length === 0" class="estado-cargando">
          <div class="spinner-tabla"></div>
          <p>Cargando registros...</p>
        </div>

        <table
          v-else-if="paginatedEmpleados.length > 0"
          class="empleados-table"
          ref="tablaRef"
          @keydown="navegarTeclado"
          tabindex="0"
        >
          <thead>
            <tr>
              <th>Núm. Empleado</th>
              <th>Nombre Completo</th>
              <th>Puesto</th>
              <th>Departamento</th>
              <th>Fecha Contratación</th>
              <th>Estatus</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(empleado, index) in paginatedEmpleados"
              :key="empleado.id_empleado || empleado.id"
              :class="{ 'fila-seleccionada': filaActiva === index }"
              @click="filaActiva = index"
            >
              <td class="celda-control">{{ empleado.numero_empleado || empleado.noEmpleado }}</td>
              <td>{{ empleado.nombre || empleado.persona?.nombre_completo || '' }}</td>
              <td>
                <span class="badge-puesto">{{ empleado.puesto?.nombre_puesto || empleado.puesto || '—' }}</span>
              </td>
              <td>
                <span class="badge-departamento">{{ empleado.departamento?.nombre_departamento || empleado.departamento || '—' }}</span>
              </td>
              <td>{{ formatearFecha(empleado.fecha_contratacion) }}</td>
              <td>
                <span class="estatus-badge" :class="claseEstatus(empleado.estatus)">
                  {{ empleado.estatus }}
                </span>
              </td>
              <td class="celda-acciones">
                <button class="btn-accion ver" @click.stop="abrirModalVer(empleado)" title="Ver detalles">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Ver
                </button>
                <button class="btn-accion editar" @click.stop="editarEmpleado(empleado)" title="Editar registro">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Editar
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
          <p>No se encontraron empleados con los criterios aplicados.</p>
          <button class="btn-limpiar-vacio" @click="resetFilters">Limpiar filtros</button>
        </div>
      </div>

      <!-- Paginación -->
      <div class="paginacion">
        <div class="paginacion-izquierda">
          Filas por página:
          <select v-model="filasPorPagina" @change="currentPage = 1" class="select-filas">
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
        </div>
        <div class="paginacion-centro">
          Página {{ currentPage }} de {{ totalPages }}
        </div>
        <div class="paginacion-derecha">
          <button class="btn-pag" @click="prevPage" :disabled="currentPage === 1">‹</button>
          <button
            v-for="page in visiblePages"
            :key="page"
            class="btn-pag"
            :class="{ activo: page === currentPage }"
            @click="goToPage(page)"
          >{{ page }}</button>
          <button class="btn-pag" @click="nextPage" :disabled="currentPage === totalPages">›</button>
        </div>
      </div>

    </div>

    <!-- ── Modal Ver ── -->
    <div v-if="showViewModal" class="modal-overlay" @click.self="cerrarModalVer">
      <div class="modal-content modal-ver">
        <div class="modal-header">
          <h3>Datos del Empleado</h3>
          <button @click="cerrarModalVer" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">
          <div class="detalle-fila">
            <span class="detalle-label">Núm. de Empleado</span>
            <span class="detalle-valor celda-control">{{ empleadoVer.numero_empleado }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Nombre completo</span>
            <span class="detalle-valor">{{ empleadoVer.nombre }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Puesto</span>
            <span class="badge-puesto">{{ empleadoVer.puesto || '—' }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Departamento</span>
            <span class="badge-departamento">{{ empleadoVer.departamento || '—' }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Fecha de contratación</span>
            <span class="detalle-valor">{{ formatearFecha(empleadoVer.fecha_contratacion) }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Estatus</span>
            <span class="estatus-badge" :class="claseEstatus(empleadoVer.estatus)">{{ empleadoVer.estatus }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Registro como docente</span>
            <span v-if="empleadoVer.es_docente" class="badge-docente">Docente</span>
            <span v-else class="detalle-valor" style="color: #9CA3AF;">No registrado</span>
          </div>
          <div v-if="empleadoVer.es_docente" class="detalle-fila">
            <span class="detalle-label">Grado académico</span>
            <span class="detalle-valor">{{ empleadoVer.grado_academico || '—' }}</span>
          </div>
          <div v-if="empleadoVer.es_docente" class="detalle-fila">
            <span class="detalle-label">Especialidad</span>
            <span class="detalle-valor">{{ empleadoVer.especialidad || '—' }}</span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModalVer">Cerrar</button>
        </div>
      </div>
    </div>

  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import { getEmpleados } from '@/api/recursosHumanos'

const router = useRouter()

// ── Estado ──
const empleados        = ref([])
const cargando         = ref(false)
const cargandoBusqueda = ref(false)
const filaActiva       = ref(-1)
const tablaRef         = ref(null)

// ── Filtros ──
const busquedaEmpleado  = ref('')
const filtroPuesto      = ref('')
const filtroDepartamento = ref('')
const filtroEstatus     = ref('')
const filasPorPagina    = ref(10)
const currentPage       = ref(1)

// ── Listas dinámicas para los selects ──
const puestosDisponibles      = computed(() => [...new Set(empleados.value.map(e => e.puesto).filter(Boolean))])
const departamentosDisponibles = computed(() => [...new Set(empleados.value.map(e => e.departamento).filter(Boolean))])

// ── Notificación ──
const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null
const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

// ── Helpers ──
const normalize = (text) => {
  if (!text) return ''
  return text.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
}
 
const claseEstatus = (estatus) => {
  if (estatus === null || estatus === undefined) return ''
  return String(estatus).toLowerCase().replace(/\s/g, '-')
}
 
const formatearFecha = (fecha) => {
  if (!fecha) return '—'
  try {
    return new Date(fecha).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })
  } catch { return fecha }
}

// ── Normalizar datos del backend ──
const normalizarEmpleado = (e) => ({
  id_empleado:        e.id_empleado,
  numero_empleado:    e.numero_empleado,
  nombre:             e.nombre_completo,
  puesto:             e.puesto,
  fecha_contratacion: e.fecha_contratacion,
  estatus:            e.estatus ? 'Activo' : 'Inactivo',
  es_docente:         e.es_docente || false,
})

// ── Fetch ──
const cargarEmpleados = async () => {
  cargando.value = true
  try {
    const res = await getEmpleados()  // ← USA recursosHumanos.js
    
    if (!res.success) throw new Error('Error en respuesta del servidor')
    
    // Normalizar y eliminar duplicados
    const empleadosUnicos = []
    const ids = new Set()
    
    res.data.forEach(emp => {
      if (!ids.has(emp.id_empleado)) {
        ids.add(emp.id_empleado)
        empleadosUnicos.push(normalizarEmpleado(emp))
      }
    })
    
    empleados.value = empleadosUnicos
    console.log('✅ Empleados cargados:', empleados.value.length, 'registros')
  } catch (error) {
    console.error('❌ Error cargando empleados:', error)
    mostrarNotificacion('No se pudo cargar la lista de empleados. Verifica que el servidor esté activo.', 'error')
  } finally {
    cargando.value = false
  }
}
 
onMounted(() => { cargarEmpleados() })

// ── Debounce búsqueda ──
let timerBusqueda = null
watch(busquedaEmpleado, () => {
  cargandoBusqueda.value = true
  if (timerBusqueda) clearTimeout(timerBusqueda)
  timerBusqueda = setTimeout(() => {
    cargandoBusqueda.value = false
    currentPage.value = 1
  }, 350)
})
 
// ── Modal Ver ──
const showViewModal = ref(false)
const empleadoVer   = ref({})
 
const abrirModalVer = (empleado) => {
  empleadoVer.value = { ...empleado }
  showViewModal.value = true
}
const cerrarModalVer = () => { showViewModal.value = false }
 
// ── Navegación ──
const nuevoEmpleado   = () => router.push('/recursos-humanos/empleados/nuevo')
const editarEmpleado  = (empleado) => router.push(`/recursos-humanos/empleados/${empleado.id_empleado}/editar`)

// ── Filtrado ──
const empleadosFiltrados = computed(() => {
  return empleados.value.filter(emp => {
    const nombre      = emp.nombre || ''
    const noEmpleado  = (emp.numero_empleado || '').toString()

    const coincideGlobal = !busquedaEmpleado.value ||
      normalize(nombre).includes(normalize(busquedaEmpleado.value)) ||
      noEmpleado.includes(busquedaEmpleado.value)

    const coincidePuesto = !filtroPuesto.value ||
      normalize(emp.puesto).includes(normalize(filtroPuesto.value))

    const coincideDepartamento = !filtroDepartamento.value ||
      normalize(emp.departamento).includes(normalize(filtroDepartamento.value))

    const coincideEstatus = !filtroEstatus.value ||
      normalize(emp.estatus) === normalize(filtroEstatus.value)

    return coincideGlobal && coincidePuesto && coincideDepartamento && coincideEstatus
  })
})

const totalPages = computed(() =>
  Math.ceil(empleadosFiltrados.value.length / filasPorPagina.value) || 1
)

const paginatedEmpleados = computed(() => {
  const start = (currentPage.value - 1) * filasPorPagina.value
  return empleadosFiltrados.value.slice(start, start + filasPorPagina.value)
})

const visiblePages = computed(() => {
  const total   = totalPages.value
  const current = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = new Set([1, total, current, current - 1, current + 1].filter(p => p >= 1 && p <= total))
  return [...pages].sort((a, b) => a - b)
})

const goToPage = (page) => { currentPage.value = page; filaActiva.value = -1 }
const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

const resetFilters = () => {
  busquedaEmpleado.value   = ''
  filtroPuesto.value       = ''
  filtroDepartamento.value = ''
  filtroEstatus.value      = ''
  currentPage.value        = 1
  filaActiva.value         = -1
}

const aplicarBusqueda = () => { currentPage.value = 1 }

const navegarTeclado = (e) => {
  const total = paginatedEmpleados.value.length
  if (total === 0) return
  if (e.key === 'ArrowDown') {
    e.preventDefault()
    filaActiva.value = Math.min(filaActiva.value + 1, total - 1)
  } else if (e.key === 'ArrowUp') {
    e.preventDefault()
    filaActiva.value = Math.max(filaActiva.value - 1, 0)
  } else if (e.key === 'Enter' && filaActiva.value >= 0) {
    e.preventDefault()
    abrirModalVer(paginatedEmpleados.value[filaActiva.value])
  } else if (e.key === 'PageDown') {
    e.preventDefault(); nextPage()
  } else if (e.key === 'PageUp') {
    e.preventDefault(); prevPage()
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.empleados-page {
  --azul:       #1B396A;
  --azul-hover: #1D4ED8;
  --borde:      #E5E7EB;
  --fondo:      #F5F5F5;
  --texto:      #1A1A1A;
  --gris:       #6B7280;
  --verde:      #16A34A;
  --amarillo:   #F59E0B;
  --morado:     #7C3AED;

  max-width: 100%;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
}

/* ── Breadcrumb ── */
.breadcrumb {
  display: flex; align-items: center; gap: 0.4rem;
  color: var(--gris); font-size: 0.88rem; margin-bottom: 0.75rem;
}
.breadcrumb .sep { color: #E5E7EB; }
.breadcrumb-link { color: var(--gris); text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: var(--azul); }
.breadcrumb-actual { color: var(--azul); font-weight: 600; }

/* ── Encabezado ── */
.page-header { display: flex; align-items: baseline; gap: 1rem; margin-bottom: 1.2rem; }
.page-title { color: var(--texto); font-size: 1.75rem; font-weight: 700; letter-spacing: -0.02em; margin: 0; }
.page-subtitle { font-size: 0.9rem; color: var(--gris); font-weight: 500; }

/* ── Barra de carga ── */
.barra-carga-global { height: 3px; background: transparent; border-radius: 2px; margin-bottom: 1rem; overflow: hidden; transition: opacity 0.3s; opacity: 0; }
.barra-carga-global.visible { opacity: 1; }
.barra-progreso { height: 100%; width: 40%; background: #1B396A; border-radius: 2px; animation: deslizar 1.2s ease-in-out infinite; }
@keyframes deslizar { 0% { transform: translateX(-100%); } 100% { transform: translateX(350%); } }

/* ── Notificación ── */
.notificacion-ui { display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 10px; font-size: 0.93rem; font-weight: 500; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.notificacion-ui.exito { background: #DCFCE7; color: #16A34A; border: 1px solid #86EFAC; }
.notificacion-ui.error { background: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5; }
.notif-icono { width: 20px; height: 20px; flex-shrink: 0; }
.notif-fade-enter-active, .notif-fade-leave-active { transition: all 0.35s ease; }
.notif-fade-enter-from, .notif-fade-leave-to { opacity: 0; transform: translateY(-8px); }

/* ── Filtros ── */
.filters-bar { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.2rem; flex-wrap: wrap; }
.search-group { position: relative; flex: 0 0 300px; min-width: 220px; }
.search-input { width: 100%; padding: 10px 14px 10px 42px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.93rem; background: #FFFFFF; color: var(--texto); font-family: 'Montserrat', sans-serif; outline: none; transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box; }
.search-input:focus { border-color: #1B396A; box-shadow: 0 0 0 3px #DBEAFE; }
.search-input::placeholder { color: #9CA3AF; }
.search-icon-svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; stroke: var(--gris); pointer-events: none; }
.spinner-busqueda { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; border: 2px solid #E5E7EB; border-top-color: #1B396A; border-radius: 50%; animation: girar 0.7s linear infinite; }
.filter-select { padding: 10px 12px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.92rem; flex: 1 1 150px; min-width: 130px; background: #FFFFFF; color: var(--texto); font-family: 'Montserrat', sans-serif; cursor: pointer; outline: none; }
.filter-select:focus { border-color: #1B396A; }
.btn-limpiar { display: flex; align-items: center; gap: 6px; background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); padding: 10px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.92rem; white-space: nowrap; font-family: 'Montserrat', sans-serif; transition: background 0.15s; }
.btn-limpiar:hover { background: var(--fondo); }
.reset-icon { width: 16px; height: 16px; stroke: var(--gris); }
.btn-nuevo { background: #1B396A; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; white-space: nowrap; font-family: 'Montserrat', sans-serif; font-size: 0.92rem; transition: background 0.2s; margin-left: auto; }
.btn-nuevo:hover { background: #1D4ED8; }

/* ── Tabla ── */
.table-container { background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid var(--borde); }
.empleados-table { width: 100%; border-collapse: collapse; outline: none; }
.empleados-table th { background: var(--fondo); padding: 12px 16px; text-align: left; font-weight: 600; font-size: 0.88rem; color: var(--texto); border-bottom: 1px solid var(--borde); font-family: 'Montserrat', sans-serif; white-space: nowrap; }
.empleados-table td { padding: 11px 16px; border-bottom: 1px solid var(--borde); color: var(--texto); font-size: 0.93rem; font-family: 'Montserrat', sans-serif; }
.empleados-table tbody tr { transition: background 0.15s; cursor: pointer; }
.empleados-table tbody tr:hover { background: #F8FAFC; }
.empleados-table tbody tr:last-child td { border-bottom: none; }
.fila-seleccionada { background: #DBEAFE !important; }
.celda-control { font-weight: 600; color: #1B396A; font-size: 0.92rem; }

/* ── Badges del módulo ── */
.badge-puesto {
  display: inline-block; padding: 4px 10px; border-radius: 20px;
  font-size: 0.8rem; font-weight: 600;
  background: #EDE9FE; color: #7C3AED;
}
.badge-departamento {
  display: inline-block; padding: 4px 10px; border-radius: 20px;
  font-size: 0.8rem; font-weight: 600;
  background: #FEF3C7; color: #F59E0B;
}
.badge-docente {
  display: inline-block; padding: 4px 10px; border-radius: 20px;
  font-size: 0.8rem; font-weight: 600;
  background: #DBEAFE; color: #1B396A;
}
.estatus-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.83rem; font-weight: 600; }
.estatus-badge.activo   { background: #DCFCE7; color: #16A34A; }
.estatus-badge.inactivo { background: #FEF2F2; color: #DC2626; }

/* ── Acciones ── */
.celda-acciones { display: flex; gap: 7px; align-items: center; }
.btn-accion { display: flex; align-items: center; gap: 5px; padding: 6px 13px; border-radius: 6px; font-size: 0.85rem; cursor: pointer; font-weight: 600; font-family: 'Montserrat', sans-serif; transition: background 0.15s, border-color 0.15s; white-space: nowrap; border: none; }
.btn-accion svg { width: 14px; height: 14px; }
.btn-accion.ver { background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); }
.btn-accion.ver:hover { background: var(--fondo); }
.btn-accion.editar { background: #1B396A; color: white; border: 1px solid #1B396A; }
.btn-accion.editar:hover { background: #1D4ED8; border-color: #1D4ED8; }

/* ── Estados vacío / cargando ── */
.estado-vacio, .estado-cargando { text-align: center; padding: 3.5rem 2rem; color: var(--gris); }
.icono-vacio { width: 56px; height: 56px; stroke: #9CA3AF; margin-bottom: 12px; }
.estado-vacio h3 { font-size: 1.2rem; color: var(--texto); margin: 0 0 6px; }
.estado-vacio p  { font-size: 0.93rem; margin: 0 0 1.2rem; }
.btn-limpiar-vacio { background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); padding: 9px 20px; border-radius: 8px; font-weight: 500; cursor: pointer; font-family: 'Montserrat', sans-serif; }
.spinner-tabla { display: inline-block; width: 36px; height: 36px; border: 3px solid #E5E7EB; border-top-color: #1B396A; border-radius: 50%; animation: girar 0.8s linear infinite; margin-bottom: 12px; }

/* ── Paginación ── */
.paginacion { margin-top: 1.2rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; color: var(--gris); font-family: 'Montserrat', sans-serif; flex-wrap: wrap; gap: 0.5rem; }
.paginacion-izquierda, .paginacion-centro, .paginacion-derecha { display: flex; align-items: center; gap: 8px; }
.select-filas { border: 1px solid var(--borde); border-radius: 6px; padding: 4px 8px; font-size: 0.9rem; background: #FFFFFF; font-family: 'Montserrat', sans-serif; }
.btn-pag { padding: 5px 11px; border: 1px solid var(--borde); background: #FFFFFF; border-radius: 6px; cursor: pointer; color: var(--texto); font-family: 'Montserrat', sans-serif; font-size: 0.9rem; transition: background 0.15s; }
.btn-pag:hover:not(:disabled) { background: var(--fondo); }
.btn-pag:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-pag.activo { background: #1B396A; color: white; border-color: #1B396A; }

/* ── Modal ── */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.modal-content { background: #FFFFFF; width: 540px; max-width: 92%; border-radius: 14px; box-shadow: 0 20px 50px rgba(0,0,0,0.18); overflow: hidden; border: 1px solid var(--borde); }
.modal-header { background: #1B396A; color: white; padding: 1.1rem 1.6rem; display: flex; justify-content: space-between; align-items: center; }
.modal-header h3 { margin: 0; font-size: 1.2rem; font-weight: 700; font-family: 'Montserrat', sans-serif; }
.btn-cerrar-modal { background: none; border: none; color: white; font-size: 1.7rem; cursor: pointer; line-height: 1; opacity: 0.85; transition: opacity 0.15s; }
.btn-cerrar-modal:hover { opacity: 1; }
.modal-body { padding: 1.6rem; }
.modal-footer { padding: 1rem 1.6rem; background: var(--fondo); display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid var(--borde); }
.btn-secundario { padding: 10px 22px; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); transition: background 0.15s; }
.btn-secundario:hover { background: var(--fondo); }

.detalle-fila { display: flex; justify-content: space-between; align-items: center; padding: 11px 0; border-bottom: 1px solid var(--borde); font-size: 0.95rem; color: var(--texto); font-family: 'Montserrat', sans-serif; }
.detalle-fila:last-child { border-bottom: none; }
.detalle-label { font-weight: 600; color: var(--gris); }
.detalle-valor { font-weight: 500; }

@keyframes girar { to { transform: rotate(360deg); } }
</style>