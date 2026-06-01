<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="personas-page">

      <div class="page-header">
        <div class="breadcrumb">
          <span class="breadcrumb-actual">Personas</span>
        </div>
        <h1 class="page-title">Catálogo de Personas</h1>
        <span class="page-subtitle">{{ personasFiltradas.length }} registro(s) encontrado(s)</span>
      </div>

      <div class="barra-carga-global" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

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

      <div class="filters-bar">
        <div class="search-group">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Buscar por nombre, apellido o CURP..."
            v-model="busquedaPersona"
            class="search-input"
            @keydown.escape="busquedaPersona = ''"
          >
          <span v-if="cargandoBusqueda" class="spinner-busqueda"></span>
        </div>

        <button class="btn-toggle-filtros" @click="filtrosExpandidos = !filtrosExpandidos">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
          </svg>
          Filtros
          <span v-if="filtroTipo || filtroEstatus" class="filtros-badge">{{ [filtroTipo, filtroEstatus].filter(Boolean).length }}</span>
        </button>

        <button v-if="filtroTipo || filtroEstatus" class="btn-limpiar" @click="resetFilters">
          <svg xmlns="http://www.w3.org/2000/svg" class="reset-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <button class="btn-nuevo" @click="nuevaPersona">
          + Nueva Persona
        </button>
      </div>

      <!-- Filtros colapsables -->
      <transition name="filtros-expand">
        <div v-if="filtrosExpandidos" class="filtros-panel">
          <select v-model="filtroTipo" class="filter-select" @change="currentPage = 1">
            <option value="">Tipo</option>
            <option value="Alumno">Alumno</option>
            <option value="Empleado">Empleado</option>
            <option value="Sin asignar">Sin asignar</option>
          </select>
          <select v-model="filtroEstatus" class="filter-select" @change="currentPage = 1">
            <option value="">Estatus</option>
            <option value="Activo">Activo</option>
            <option value="Inactivo">Inactivo</option>
          </select>
        </div>
      </transition>

      <div class="table-container">
        <div v-if="cargando && personas.length === 0" class="estado-cargando">
          <div class="spinner-tabla"></div>
          <p>Cargando registros...</p>
        </div>

        <table
          v-else-if="paginatedPersonas.length > 0"
          class="personas-table"
          ref="tablaRef"
          @keydown="navegarTeclado"
          tabindex="0"
        >
          <thead>
            <tr>
              <th>CURP</th>
              <th>Nombre Completo</th>
              <th>Fecha de Nacimiento</th>
              <th>Tipo</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(persona, index) in paginatedPersonas"
              :key="persona.id_persona || persona.id"
              :class="{ 'fila-seleccionada': filaActiva === index }"
              @click="filaActiva = index"
            >
              <td class="celda-curp">{{ persona.curp }}</td>
              <td>{{ nombreCompleto(persona) }}</td>
              <td>{{ formatFecha(persona.fecha_nacimiento) }}</td>
              <td>
                <span class="tipo-badge" :class="claseTipo(persona.tipo)">
                  {{ persona.tipo || 'Sin asignar' }}
                </span>
              </td>
              <td class="celda-acciones">
                <button class="btn-accion ver" @click.stop="abrirModalVer(persona)" title="Ver detalle">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Ver
                </button>
                <button class="btn-accion editar" @click.stop="editarPersona(persona)" title="Editar">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Editar
                </button>

                <!-- Botón Asignar como (solo Sin asignar) -->
                <div
                  v-if="!persona.tipo || persona.tipo === 'Sin asignar'"
                  class="dropdown-asignar"
                  @click.stop
                >
                  <button
                    class="btn-accion asignar"
                    @click="toggleDropdown(persona.id_persona || persona.id)"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Asignar como
                    <span class="chevron">▾</span>
                  </button>
                  <div
                    v-if="dropdownAbierto === (persona.id_persona || persona.id)"
                    class="dropdown-menu"
                  >
                    <div class="dropdown-item" @click="asignarComo(persona, 'alumno')">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                      </svg>
                      Registrar como Alumno
                    </div>
                    <div class="dropdown-item" @click="asignarComo(persona, 'empleado')">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                      </svg>
                      Registrar como Empleado
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <h3>Sin resultados</h3>
          <p>No se encontraron personas con los criterios aplicados.</p>
          <button class="btn-limpiar-vacio" @click="resetFilters">Limpiar filtros</button>
        </div>
      </div>

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

      <footer class="footer-institucional">
        <span>Sistema Integral de Control Escolar — TecNM</span>
        <span>© {{ anioActual }}</span>
      </footer>
    </div>

    <!-- Modal Ver -->
    <div v-if="showViewModal" class="modal-overlay" @click.self="cerrarModalVer">
      <div class="modal-content modal-ver">
        <div class="modal-header">
          <h3>Datos de la Persona</h3>
          <button @click="cerrarModalVer" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">
          <div class="detalle-fila"><span class="detalle-label">CURP</span><span class="detalle-valor curp-valor">{{ personaVer.curp }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Nombre</span><span class="detalle-valor">{{ personaVer.nombre }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Apellido Paterno</span><span class="detalle-valor">{{ personaVer.apellido_paterno }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Apellido Materno</span><span class="detalle-valor">{{ personaVer.apellido_materno || '—' }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Fecha de Nacimiento</span><span class="detalle-valor">{{ formatFecha(personaVer.fecha_nacimiento) }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Género</span><span class="detalle-valor">{{ personaVer.genero || '—' }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Estado Civil</span><span class="detalle-valor">{{ personaVer.estado_civil || '—' }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Nacionalidad</span><span class="detalle-valor">{{ personaVer.nacionalidad || '—' }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Correo</span><span class="detalle-valor">{{ personaVer.correo || '—' }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Teléfono</span><span class="detalle-valor">{{ personaVer.telefono || '—' }}</span></div>
          <div class="detalle-fila">
            <span class="detalle-label">Tipo</span>
            <span class="tipo-badge" :class="claseTipo(personaVer.tipo)">{{ personaVer.tipo || 'Sin asignar' }}</span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModalVer">Cerrar</button>
          <button class="btn-detalle" @click="irDetalle(personaVer)">Ver Detalle Completo</button>
        </div>
      </div>
    </div>

  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`

const router = useRouter()
const anioActual = new Date().getFullYear()

const personas         = ref([])
const cargando         = ref(false)
const cargandoBusqueda = ref(false)
const filaActiva       = ref(-1)
const tablaRef         = ref(null)
const dropdownAbierto  = ref(null)

const busquedaPersona = ref('')
const filtroTipo      = ref('')
const filtrosExpandidos = ref(false)
const filtroEstatus   = ref('')
const filasPorPagina  = ref(10)
const currentPage     = ref(1)

const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

const normalize = (text) => {
  if (!text) return ''
  return text.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
}

const nombreCompleto = (p) => {
  return [p.apellido_paterno, p.apellido_materno, p.nombre].filter(Boolean).join(' ')
}

const formatFecha = (fecha) => {
  if (!fecha) return '—'
  const d = new Date(fecha + 'T00:00:00')
  return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const claseTipo = (tipo) => {
  if (!tipo || tipo === 'Sin asignar') return 'sin-asignar'
  return tipo.toLowerCase()
}

const cargarPersonas = async () => {
  cargando.value = true
  try {
    const response = await fetch(`${API}/personas`)
    if (!response.ok) throw new Error('Error del servidor')
    const data = await response.json()
    personas.value = data
  } catch (error) {
    console.error('Error cargando personas:', error)
    mostrarNotificacion('No se pudo cargar el catálogo de personas.', 'error')
  } finally {
    cargando.value = false
  }
}

onMounted(() => { cargarPersonas() })

let timerBusqueda = null
watch(busquedaPersona, () => {
  cargandoBusqueda.value = true
  if (timerBusqueda) clearTimeout(timerBusqueda)
  timerBusqueda = setTimeout(() => {
    cargandoBusqueda.value = false
    currentPage.value = 1
  }, 350)
})

const showViewModal = ref(false)
const personaVer    = ref({})

const abrirModalVer = async (persona) => {
  personaVer.value = { ...persona }
  showViewModal.value = true
  try {
    const res = await fetch(`${API}/personas/${persona.id_persona || persona.id}`)
    if (res.ok) {
      const data = await res.json()
      personaVer.value = data
    }
  } catch { /* mantiene los datos básicos de la fila */ }
}
const cerrarModalVer = () => { showViewModal.value = false }

const editarPersona = (persona) => {
  router.push(`/personas/editar/${persona.id_persona || persona.id}`)
}

const nuevaPersona = () => router.push('/personas/nueva')

const irDetalle = (persona) => {
  cerrarModalVer()
  router.push(`/personas/${persona.id_persona || persona.id}`)
}

const toggleDropdown = (id) => {
  dropdownAbierto.value = dropdownAbierto.value === id ? null : id
}

const asignarComo = (persona, tipo) => {
  dropdownAbierto.value = null
  const id = persona.id_persona || persona.id
  if (tipo === 'alumno') {
    router.push(`/formulario-alumno?persona_id=${id}`)
  } else {
    router.push(`/recursos-humanos/empleados/nuevo?persona_id=${id}`)
  }
}

const personasFiltradas = computed(() => {
  return personas.value.filter(p => {
    const nombre    = normalize(nombreCompleto(p))
    const curp      = normalize(p.curp || '')
    const busqueda  = normalize(busquedaPersona.value)

    const coincideBusqueda = !busquedaPersona.value ||
      nombre.includes(busqueda) || curp.includes(busqueda)

    const tipoReal = p.tipo || 'Sin asignar'
    const coincideTipo    = !filtroTipo.value || tipoReal === filtroTipo.value
    const coincideEstatus = !filtroEstatus.value ||
      normalize(p.estatus) === normalize(filtroEstatus.value)

    return coincideBusqueda && coincideTipo && coincideEstatus
  })
})

const totalPages = computed(() =>
  Math.ceil(personasFiltradas.value.length / filasPorPagina.value) || 1
)

const paginatedPersonas = computed(() => {
  const start = (currentPage.value - 1) * filasPorPagina.value
  return personasFiltradas.value.slice(start, start + filasPorPagina.value)
})

const visiblePages = computed(() => {
  const total   = totalPages.value
  const current = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = new Set([1, total, current, current - 1, current + 1].filter(p => p >= 1 && p <= total))
  return [...pages].sort((a, b) => a - b)
})

const goToPage  = (page) => { currentPage.value = page; filaActiva.value = -1 }
const prevPage  = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage  = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

const resetFilters = () => {
  busquedaPersona.value = ''
  filtroTipo.value      = ''
  filtroEstatus.value   = ''
  currentPage.value     = 1
  filaActiva.value      = -1
}

const navegarTeclado = (e) => {
  const total = paginatedPersonas.value.length
  if (total === 0) return
  if (e.key === 'ArrowDown') { e.preventDefault(); filaActiva.value = Math.min(filaActiva.value + 1, total - 1) }
  else if (e.key === 'ArrowUp') { e.preventDefault(); filaActiva.value = Math.max(filaActiva.value - 1, 0) }
  else if (e.key === 'Enter' && filaActiva.value >= 0) { e.preventDefault(); abrirModalVer(paginatedPersonas.value[filaActiva.value]) }
  else if (e.key === 'PageDown') { e.preventDefault(); nextPage() }
  else if (e.key === 'PageUp')   { e.preventDefault(); prevPage() }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.personas-page {
  --azul:       #1B396A;
  --azul-hover: #1D4ED8;
  --borde:      #E5E7EB;
  --fondo:      #F5F5F5;
  --texto:      #1A1A1A;
  --gris:       #6B7280;

  max-width: 100%;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
}

.breadcrumb { margin-bottom: 0.4rem; font-size: 0.88rem; color: var(--gris); }
.breadcrumb-actual { font-weight: 600; }

.page-header {
  margin-bottom: 1.2rem;
}
.page-title {
  color: var(--texto);
  font-size: 1.75rem;
  font-weight: 700;
  letter-spacing: -0.02em;
  margin: 0 0 0.2rem;
}
.page-subtitle { font-size: 0.9rem; color: var(--gris); font-weight: 500; }

.barra-carga-global {
  height: 3px; background: transparent; border-radius: 2px;
  margin-bottom: 1rem; overflow: hidden; transition: opacity 0.3s; opacity: 0;
}
.barra-carga-global.visible { opacity: 1; }
.barra-progreso {
  height: 100%; width: 40%; background: #1B396A; border-radius: 2px;
  animation: deslizar 1.2s ease-in-out infinite;
}
@keyframes deslizar { 0% { transform: translateX(-100%); } 100% { transform: translateX(350%); } }

.notificacion-ui {
  display: flex; align-items: center; gap: 10px;
  padding: 12px 18px; border-radius: 10px; font-size: 0.93rem; font-weight: 500;
  margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.notificacion-ui.exito { background: #DCFCE7; color: #16A34A; border: 1px solid #86EFAC; }
.notificacion-ui.error { background: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5; }
.notif-icono { width: 20px; height: 20px; flex-shrink: 0; }
.notif-fade-enter-active, .notif-fade-leave-active { transition: all 0.35s ease; }
.notif-fade-enter-from, .notif-fade-leave-to { opacity: 0; transform: translateY(-8px); }

.filters-bar {
  display: flex; align-items: center; gap: 0.75rem;
  margin-bottom: 1.2rem; flex-wrap: wrap;
}
.search-group { position: relative; flex: 0 0 320px; min-width: 220px; }
.search-input {
  width: 100%; padding: 10px 14px 10px 42px;
  border: 1px solid var(--borde); border-radius: 8px; font-size: 0.93rem;
  background: #FFF; color: var(--texto); font-family: 'Montserrat', sans-serif;
  outline: none; transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box;
}
.search-input:focus { border-color: #1B396A; box-shadow: 0 0 0 3px #DBEAFE; }
.search-input::placeholder { color: #9CA3AF; }
.search-icon-svg {
  position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
  width: 18px; height: 18px; stroke: var(--gris); pointer-events: none;
}
.spinner-busqueda {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  width: 16px; height: 16px; border: 2px solid #E5E7EB; border-top-color: #1B396A;
  border-radius: 50%; animation: girar 0.7s linear infinite;
}
.filter-select {
  padding: 10px 12px; border: 1px solid var(--borde); border-radius: 8px;
  font-size: 0.92rem; flex: 1 1 150px; min-width: 130px; background: #FFF;
  color: var(--texto); font-family: 'Montserrat', sans-serif; cursor: pointer; outline: none;
}
.filter-select:focus { border-color: #1B396A; }
.btn-limpiar {
  display: flex; align-items: center; gap: 6px; background: #FFF; color: var(--texto);
  border: 1px solid var(--borde); padding: 10px 16px; border-radius: 8px; font-weight: 600;
  cursor: pointer; font-size: 0.92rem; white-space: nowrap; font-family: 'Montserrat', sans-serif;
  transition: background 0.15s;
}
.btn-limpiar:hover { background: var(--fondo); }
.reset-icon { width: 16px; height: 16px; stroke: var(--gris); }
.btn-nuevo {
  background: #1B396A; color: white; border: none; padding: 10px 20px; border-radius: 8px;
  font-weight: 600; cursor: pointer; white-space: nowrap; font-family: 'Montserrat', sans-serif;
  font-size: 0.92rem; transition: background 0.2s; margin-left: auto;
}
.btn-nuevo:hover { background: #1D4ED8; }

.table-container {
  background: #FFF; border-radius: 12px; overflow: visible;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid var(--borde);
}
.personas-table { width: 100%; border-collapse: collapse; outline: none; }
.personas-table th {
  background: var(--fondo); padding: 12px 16px; text-align: left;
  font-weight: 600; font-size: 0.88rem; color: var(--texto);
  border-bottom: 1px solid var(--borde); font-family: 'Montserrat', sans-serif; white-space: nowrap;
}
.personas-table td {
  padding: 11px 16px; border-bottom: 1px solid var(--borde);
  color: var(--texto); font-size: 0.93rem; font-family: 'Montserrat', sans-serif;
}
.personas-table tbody tr { transition: background 0.15s; cursor: pointer; }
.personas-table tbody tr:hover { background: #F8FAFC; }
.personas-table tbody tr:last-child td { border-bottom: none; }
.fila-seleccionada { background: #DBEAFE !important; }
.celda-curp { font-weight: 600; color: #1B396A; font-size: 0.88rem; letter-spacing: 0.03em; }

/* Tipo badge */
.tipo-badge {
  display: inline-block; padding: 4px 12px; border-radius: 20px;
  font-size: 0.83rem; font-weight: 600; font-family: 'Montserrat', sans-serif;
}
.tipo-badge.alumno      { background: #DBEAFE; color: #1B396A; }
.tipo-badge.empleado    { background: #DCFCE7; color: #16A34A; }
.tipo-badge.sin-asignar { background: #F3F4F6; color: #6B7280; }

/* Acciones */
.celda-acciones {
  display: flex; gap: 6px; align-items: center; flex-wrap: nowrap; position: relative;
}
.btn-accion {
  display: flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 6px;
  font-size: 0.83rem; cursor: pointer; font-weight: 600; font-family: 'Montserrat', sans-serif;
  transition: background 0.15s, border-color 0.15s; white-space: nowrap;
}
.btn-accion svg { width: 14px; height: 14px; }
.btn-accion.ver {
  background: #FFF; color: var(--texto); border: 1px solid var(--borde);
}
.btn-accion.ver:hover { background: var(--fondo); }
.btn-accion.editar {
  background: #1B396A; color: white; border: 1px solid #1B396A;
}
.btn-accion.editar:hover { background: #1D4ED8; border-color: #1D4ED8; }
.btn-accion.asignar {
  background: #FFF; color: #1B396A; border: 1px solid #BFDBFE;
  gap: 4px;
}
.btn-accion.asignar:hover { background: #EFF6FF; }
.chevron { font-size: 0.75rem; margin-left: 2px; }

/* Dropdown asignar */
.dropdown-asignar { position: relative; }
.dropdown-menu {
  position: absolute; top: calc(100% + 4px); right: 0;
  background: #FFF; border: 1px solid var(--borde); border-radius: 10px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 500; min-width: 210px; overflow: hidden;
}
.dropdown-item {
  display: flex; align-items: center; gap: 9px; padding: 11px 16px;
  font-size: 0.9rem; cursor: pointer; color: var(--texto); font-family: 'Montserrat', sans-serif;
  font-weight: 500; transition: background 0.15s;
}
.dropdown-item:hover { background: #F0F9FF; color: #1B396A; }
.dropdown-item svg { width: 16px; height: 16px; stroke: #6B7280; }

/* Estado vacío / cargando */
.estado-vacio, .estado-cargando { text-align: center; padding: 3.5rem 2rem; color: var(--gris); }
.icono-vacio { width: 56px; height: 56px; stroke: #9CA3AF; margin-bottom: 12px; }
.estado-vacio h3 { font-size: 1.2rem; color: var(--texto); margin: 0 0 6px; }
.estado-vacio p  { font-size: 0.93rem; margin: 0 0 1.2rem; }
.btn-limpiar-vacio {
  background: #FFF; color: var(--texto); border: 1px solid var(--borde);
  padding: 9px 20px; border-radius: 8px; font-weight: 500; cursor: pointer;
  font-family: 'Montserrat', sans-serif;
}
.spinner-tabla {
  display: inline-block; width: 36px; height: 36px;
  border: 3px solid #E5E7EB; border-top-color: #1B396A; border-radius: 50%;
  animation: girar 0.8s linear infinite; margin-bottom: 12px;
}

/* Paginación */
.paginacion {
  margin-top: 1.2rem; display: flex; justify-content: space-between; align-items: center;
  font-size: 0.9rem; color: var(--gris); font-family: 'Montserrat', sans-serif; flex-wrap: wrap; gap: 0.5rem;
}
.paginacion-izquierda, .paginacion-centro, .paginacion-derecha { display: flex; align-items: center; gap: 8px; }
.select-filas {
  border: 1px solid var(--borde); border-radius: 6px; padding: 4px 8px;
  font-size: 0.9rem; background: #FFF; font-family: 'Montserrat', sans-serif;
}
.btn-pag {
  padding: 5px 11px; border: 1px solid var(--borde); background: #FFF; border-radius: 6px;
  cursor: pointer; color: var(--texto); font-family: 'Montserrat', sans-serif;
  font-size: 0.9rem; transition: background 0.15s;
}
.btn-pag:hover:not(:disabled) { background: var(--fondo); }
.btn-pag:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-pag.activo { background: #1B396A; color: white; border-color: #1B396A; }

/* Footer */
.footer-institucional {
  margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--borde);
  display: flex; justify-content: space-between; align-items: center;
  font-size: 0.82rem; color: #9CA3AF; font-family: 'Montserrat', sans-serif;
}

/* Modal */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.55);
  display: flex; align-items: center; justify-content: center; z-index: 2000;
}
.modal-content {
  background: #FFF; width: 540px; max-width: 94%; border-radius: 14px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.18); overflow: hidden; border: 1px solid var(--borde);
}
.modal-header {
  background: #1B396A; color: white; padding: 1.1rem 1.6rem;
  display: flex; justify-content: space-between; align-items: center;
}
.modal-header h3 { margin: 0; font-size: 1.2rem; font-weight: 700; font-family: 'Montserrat', sans-serif; }
.btn-cerrar-modal {
  background: none; border: none; color: white; font-size: 1.7rem;
  cursor: pointer; line-height: 1; opacity: 0.85; transition: opacity 0.15s;
}
.btn-cerrar-modal:hover { opacity: 1; }
.modal-body { padding: 1.4rem 1.6rem; max-height: 60vh; overflow-y: auto; }
.detalle-fila {
  display: flex; justify-content: space-between; align-items: center;
  padding: 10px 0; border-bottom: 1px solid var(--borde);
  font-size: 0.93rem; color: var(--texto); font-family: 'Montserrat', sans-serif;
}
.detalle-fila:last-child { border-bottom: none; }
.detalle-label { font-weight: 600; color: var(--gris); }
.detalle-valor { font-weight: 500; }
.curp-valor { font-family: monospace; font-size: 0.95rem; letter-spacing: 0.05em; color: #1B396A; }
.modal-footer {
  padding: 1rem 1.6rem; background: var(--fondo); display: flex; gap: 10px;
  justify-content: flex-end; border-top: 1px solid var(--borde);
}
.btn-secundario {
  padding: 10px 22px; border-radius: 8px; font-weight: 600; cursor: pointer;
  font-family: 'Montserrat', sans-serif; background: #FFF; color: var(--texto);
  border: 1px solid var(--borde); transition: background 0.15s;
}
.btn-secundario:hover { background: var(--fondo); }
.btn-detalle {
  padding: 10px 22px; border-radius: 8px; font-weight: 600; cursor: pointer;
  font-family: 'Montserrat', sans-serif; background: #1B396A; color: white;
  border: none; transition: background 0.15s;
}
.btn-detalle:hover { background: #1D4ED8; }

@keyframes girar { to { transform: rotate(360deg); } }

.btn-toggle-filtros {
  display: flex; align-items: center; gap: 6px;
  background: #DBEAFE; color: #1B396A; border: none;
  padding: 9px 14px; border-radius: 8px; font-weight: 600;
  font-size: 0.875rem; cursor: pointer; font-family: 'Montserrat', sans-serif;
  white-space: nowrap; transition: background 0.2s;
}
.btn-toggle-filtros:hover { background: #BFDBFE; }
.filtros-badge {
  background: #1B396A; color: #fff; border-radius: 50%;
  width: 18px; height: 18px; font-size: 0.7rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}
.filtros-panel {
  display: flex; gap: 0.75rem; flex-wrap: wrap;
  background: #fff; border: 1px solid #E5E7EB; border-radius: 10px;
  padding: 0.9rem 1.2rem; margin-bottom: 1rem;
}
.filtros-expand-enter-active, .filtros-expand-leave-active { transition: all 0.25s ease; }
.filtros-expand-enter-from, .filtros-expand-leave-to { opacity: 0; transform: translateY(-6px); }
</style>