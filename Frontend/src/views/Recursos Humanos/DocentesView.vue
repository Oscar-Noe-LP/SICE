<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="docentes-page">

      <!-- Breadcrumb -->
      <nav class="breadcrumb">
        <router-link to="/inicio" class="breadcrumb-link">Inicio</router-link>
        <span class="sep">›</span>
        <router-link to="/recursos-humanos" class="breadcrumb-link">Recursos Humanos</router-link>
        <span class="sep">›</span>
        <span class="breadcrumb-actual">Docentes</span>
      </nav>

      <!-- Encabezado -->
      <div class="page-header">
        <h1 class="page-title">Docentes</h1>
        <span class="page-subtitle">{{ docentesFiltrados.length }} registro(s) encontrado(s)</span>
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

      <!-- Filtros -->
      <div class="filters-bar">
        <div class="search-group">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Buscar por número de empleado o nombre..."
            v-model="busqueda"
            class="search-input"
            @keydown.enter="currentPage = 1"
            @keydown.escape="busqueda = ''"
          >
          <span v-if="cargandoBusqueda" class="spinner-busqueda"></span>
        </div>
        <button class="btn-limpiar" @click="resetFilters">
          <svg xmlns="http://www.w3.org/2000/svg" class="reset-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Limpiar
        </button>
      </div>

      <!-- Tabla -->
      <div class="table-container">
        <div v-if="cargando && docentes.length === 0" class="estado-cargando">
          <div class="spinner-tabla"></div>
          <p>Cargando registros...</p>
        </div>

        <table v-else-if="paginatedDocentes.length > 0" class="docentes-table" ref="tablaRef" @keydown="navegarTeclado" tabindex="0">
          <thead>
            <tr>
              <th>Núm. Empleado</th>
              <th>Nombre Completo</th>
              <th>Grado Académico</th>
              <th>Especialidad</th>
              <th>Estatus</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(docente, index) in paginatedDocentes"
              :key="docente.id_docente || docente.id"
              :class="{ 'fila-seleccionada': filaActiva === index }"
              @click="filaActiva = index"
            >
              <td class="celda-control">{{ docente.numero_empleado || '—' }}</td>
              <td>{{ docente.nombre }}</td>
              <td><span class="badge-grado">{{ docente.grado_academico || '—' }}</span></td>
              <td>{{ docente.especialidad || '—' }}</td>
              <td><span class="estatus-badge" :class="claseEstatus(docente.estatus)">{{ docente.estatus }}</span></td>
              <td class="celda-acciones">
                <!-- Editar -->
                <button class="btn-icono editar" @click.stop="abrirModalEditar(docente)" title="Editar docente">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
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
          <p>No se encontraron docentes con los criterios aplicados.</p>
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
        <div class="paginacion-centro">Página {{ currentPage }} de {{ totalPages }}</div>
        <div class="paginacion-derecha">
          <button class="btn-pag" @click="prevPage" :disabled="currentPage === 1">‹</button>
          <button v-for="page in visiblePages" :key="page" class="btn-pag" :class="{ activo: page === currentPage }" @click="goToPage(page)">{{ page }}</button>
          <button class="btn-pag" @click="nextPage" :disabled="currentPage === totalPages">›</button>
        </div>
      </div>

    </div>

    <!-- Modal Editar -->
    <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Editar Docente</h3>
          <button @click="cerrarModal" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">
          <p class="modal-nombre">{{ docenteEditar.nombre }}</p>
          <div class="form-grupo">
            <label>Grado Académico <span class="obligatorio">*</span></label>
            <select v-model="docenteEditar.grado_academico" class="modal-select">
              <option value="">Seleccionar</option>
              <option value="Licenciatura">Licenciatura</option>
              <option value="Maestría">Maestría</option>
              <option value="Doctorado">Doctorado</option>
            </select>
          </div>
          <div class="form-grupo">
            <label>Especialidad <span class="obligatorio">*</span></label>
            <input v-model="docenteEditar.especialidad" type="text" class="modal-input" placeholder="Ej. Ingeniería de Software" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">Cancelar</button>
          <button class="btn-guardar" @click="guardarDocente" :disabled="guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
          </button>
        </div>
      </div>
    </div>

  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`

const docentes        = ref([])
const cargando        = ref(false)
const cargandoBusqueda = ref(false)
const guardando       = ref(false)
const filaActiva      = ref(-1)
const tablaRef        = ref(null)
const busqueda        = ref('')
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

const claseEstatus = (estatus) => {
  if (!estatus) return ''
  return estatus.toLowerCase().replace(/\s/g, '-')
}

const normalizarDocente = (d) => ({
  id_docente:      d.id_docente || d.id,
  numero_empleado: d.numero_empleado || d.empleado?.numero_empleado || '',
  nombre:          d.nombre || d.empleado?.nombre || d.persona?.nombre_completo || '',
  grado_academico: d.grado_academico || '',
  especialidad:    d.especialidad || '',
  estatus:         d.estatus || 'Activo',
})

const cargarDocentes = async () => {
  cargando.value = true
  try {
    const res = await fetch(`${API}/docentes`)
    if (!res.ok) throw new Error()
    docentes.value = (await res.json()).map(normalizarDocente)
    console.log('✅ Docentes cargados:', docentes.value.length)
  } catch {
    mostrarNotificacion('No se pudo cargar la lista de docentes.', 'error')
  } finally {
    cargando.value = false
  }
}

onMounted(() => { cargarDocentes() })

let timerBusqueda = null
watch(busqueda, () => {
  cargandoBusqueda.value = true
  if (timerBusqueda) clearTimeout(timerBusqueda)
  timerBusqueda = setTimeout(() => { cargandoBusqueda.value = false; currentPage.value = 1 }, 350)
})

const docentesFiltrados = computed(() =>
  docentes.value.filter(d => {
    if (!busqueda.value) return true
    return normalize(d.nombre).includes(normalize(busqueda.value)) ||
           d.numero_empleado.includes(busqueda.value)
  })
)

const totalPages = computed(() => Math.ceil(docentesFiltrados.value.length / filasPorPagina.value) || 1)
const paginatedDocentes = computed(() => {
  const start = (currentPage.value - 1) * filasPorPagina.value
  return docentesFiltrados.value.slice(start, start + filasPorPagina.value)
})
const visiblePages = computed(() => {
  const total = totalPages.value, current = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = new Set([1, total, current, current - 1, current + 1].filter(p => p >= 1 && p <= total))
  return [...pages].sort((a, b) => a - b)
})

const goToPage = (p) => { currentPage.value = p; filaActiva.value = -1 }
const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }
const resetFilters = () => { busqueda.value = ''; currentPage.value = 1; filaActiva.value = -1 }

// Modal
const showModal      = ref(false)
const docenteEditar  = ref({})

const abrirModalEditar = (d) => { docenteEditar.value = { ...d }; showModal.value = true }
const cerrarModal = () => { showModal.value = false }

const guardarDocente = async () => {
  if (!docenteEditar.value.grado_academico || !docenteEditar.value.especialidad?.trim()) {
    mostrarNotificacion('Completa el grado académico y la especialidad.', 'error')
    return
  }
  guardando.value = true
  try {
    const res = await fetch(`${API}/docentes/${docenteEditar.value.id_docente}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ grado_academico: docenteEditar.value.grado_academico, especialidad: docenteEditar.value.especialidad })
    })
    if (!res.ok) throw new Error()
    await cargarDocentes()
    cerrarModal()
    mostrarNotificacion('Docente actualizado correctamente.', 'exito')
    console.log('✅ Docente actualizado')
  } catch {
    mostrarNotificacion('Error al actualizar el docente.', 'error')
  } finally {
    guardando.value = false
  }
}

const navegarTeclado = (e) => {
  const total = paginatedDocentes.value.length
  if (!total) return
  if (e.key === 'ArrowDown') { e.preventDefault(); filaActiva.value = Math.min(filaActiva.value + 1, total - 1) }
  else if (e.key === 'ArrowUp') { e.preventDefault(); filaActiva.value = Math.max(filaActiva.value - 1, 0) }
  else if (e.key === 'Enter' && filaActiva.value >= 0) { e.preventDefault(); abrirModalEditar(paginatedDocentes.value[filaActiva.value]) }
  else if (e.key === 'PageDown') { e.preventDefault(); nextPage() }
  else if (e.key === 'PageUp') { e.preventDefault(); prevPage() }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.docentes-page {
  --azul: #1B396A; --azul-hover: #1D4ED8; --borde: #E5E7EB;
  --fondo: #F5F5F5; --texto: #1A1A1A; --gris: #6B7280;
  max-width: 100%; background: var(--fondo); font-family: 'Montserrat', sans-serif;
}

.breadcrumb { display: flex; align-items: center; gap: 0.4rem; color: var(--gris); font-size: 0.82rem; margin-bottom: 0.5rem; }
.breadcrumb .sep { color: #E5E7EB; }
.breadcrumb-link { color: var(--gris); text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: var(--azul); }
.breadcrumb-actual { color: var(--azul); font-weight: 600; }

.page-header { display: flex; align-items: baseline; gap: 0.75rem; margin-bottom: 0.85rem; }
.page-title { color: var(--texto); font-size: 1.5rem; font-weight: 700; letter-spacing: -0.02em; margin: 0; }
.page-subtitle { font-size: 0.85rem; color: var(--gris); font-weight: 500; }

.barra-carga-global { height: 3px; background: transparent; border-radius: 2px; margin-bottom: 0.75rem; overflow: hidden; transition: opacity 0.3s; opacity: 0; }
.barra-carga-global.visible { opacity: 1; }
.barra-progreso { height: 100%; width: 40%; background: #1B396A; border-radius: 2px; animation: deslizar 1.2s ease-in-out infinite; }
@keyframes deslizar { 0% { transform: translateX(-100%); } 100% { transform: translateX(350%); } }

.notificacion-ui { display: flex; align-items: center; gap: 8px; padding: 9px 14px; border-radius: 8px; font-size: 0.88rem; font-weight: 500; margin-bottom: 0.75rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.notificacion-ui.exito { background: #DCFCE7; color: #16A34A; border: 1px solid #86EFAC; }
.notificacion-ui.error { background: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5; }
.notif-icono { width: 18px; height: 18px; flex-shrink: 0; }
.notif-fade-enter-active, .notif-fade-leave-active { transition: all 0.35s ease; }
.notif-fade-enter-from, .notif-fade-leave-to { opacity: 0; transform: translateY(-8px); }

.filters-bar { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.85rem; flex-wrap: wrap; }
.search-group { position: relative; flex: 0 0 320px; min-width: 200px; }
.search-input { width: 100%; padding: 7px 12px 7px 36px; border: 1px solid var(--borde); border-radius: 7px; font-size: 0.88rem; background: #FFFFFF; color: var(--texto); font-family: 'Montserrat', sans-serif; outline: none; transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box; }
.search-input:focus { border-color: #1B396A; box-shadow: 0 0 0 3px #DBEAFE; }
.search-input::placeholder { color: #9CA3AF; }
.search-icon-svg { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; stroke: var(--gris); pointer-events: none; }
.spinner-busqueda { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; border: 2px solid #E5E7EB; border-top-color: #1B396A; border-radius: 50%; animation: girar 0.7s linear infinite; }
.btn-limpiar { display: flex; align-items: center; gap: 5px; background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); padding: 7px 13px; border-radius: 7px; font-weight: 600; cursor: pointer; font-size: 0.86rem; white-space: nowrap; font-family: 'Montserrat', sans-serif; transition: background 0.15s; }
.btn-limpiar:hover { background: var(--fondo); }
.reset-icon { width: 14px; height: 14px; stroke: var(--gris); }

.table-container { background: #FFFFFF; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid var(--borde); }
.docentes-table { width: 100%; border-collapse: collapse; outline: none; }
.docentes-table th { background: var(--fondo); padding: 8px 12px; text-align: left; font-weight: 600; font-size: 0.8rem; color: var(--texto); border-bottom: 1px solid var(--borde); font-family: 'Montserrat', sans-serif; white-space: nowrap; }
.docentes-table td { padding: 7px 12px; border-bottom: 1px solid var(--borde); color: var(--texto); font-size: 0.86rem; font-family: 'Montserrat', sans-serif; }
.docentes-table tbody tr { transition: background 0.15s; cursor: pointer; }
.docentes-table tbody tr:hover { background: #F8FAFC; }
.docentes-table tbody tr:last-child td { border-bottom: none; }
.fila-seleccionada { background: #DBEAFE !important; }
.celda-control { font-weight: 600; color: #1B396A; font-size: 0.86rem; }

.badge-grado { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 0.76rem; font-weight: 600; background: #DBEAFE; color: #1B396A; }
.estatus-badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 0.76rem; font-weight: 600; }
.estatus-badge.activo   { background: #DCFCE7; color: #16A34A; }
.estatus-badge.inactivo { background: #FEF2F2; color: #DC2626; }

/* ── Botones de acción (solo ícono) ── */
.celda-acciones { display: flex; gap: 5px; align-items: center; }
.btn-icono {
  display: flex; align-items: center; justify-content: center;
  width: 30px; height: 30px;
  border-radius: 6px; cursor: pointer; border: 1px solid transparent;
  transition: background 0.15s, border-color 0.15s;
  flex-shrink: 0;
}
.btn-icono svg { width: 15px; height: 15px; }
.btn-icono.editar { background: #1B396A; color: white; border-color: #1B396A; }
.btn-icono.editar:hover { background: #1D4ED8; border-color: #1D4ED8; }

.estado-vacio, .estado-cargando { text-align: center; padding: 2.5rem 2rem; color: var(--gris); }
.icono-vacio { width: 48px; height: 48px; stroke: #9CA3AF; margin-bottom: 10px; }
.estado-vacio h3 { font-size: 1.1rem; color: var(--texto); margin: 0 0 4px; }
.estado-vacio p { font-size: 0.88rem; margin: 0 0 1rem; }
.btn-limpiar-vacio { background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); padding: 7px 18px; border-radius: 7px; font-weight: 500; cursor: pointer; font-family: 'Montserrat', sans-serif; }
.spinner-tabla { display: inline-block; width: 32px; height: 32px; border: 3px solid #E5E7EB; border-top-color: #1B396A; border-radius: 50%; animation: girar 0.8s linear infinite; margin-bottom: 10px; }

.paginacion { margin-top: 0.85rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.84rem; color: var(--gris); font-family: 'Montserrat', sans-serif; flex-wrap: wrap; gap: 0.4rem; }
.paginacion-izquierda, .paginacion-centro, .paginacion-derecha { display: flex; align-items: center; gap: 6px; }
.select-filas { border: 1px solid var(--borde); border-radius: 5px; padding: 3px 6px; font-size: 0.84rem; background: #FFFFFF; font-family: 'Montserrat', sans-serif; }
.btn-pag { padding: 3px 9px; border: 1px solid var(--borde); background: #FFFFFF; border-radius: 5px; cursor: pointer; color: var(--texto); font-family: 'Montserrat', sans-serif; font-size: 0.84rem; transition: background 0.15s; }
.btn-pag:hover:not(:disabled) { background: var(--fondo); }
.btn-pag:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-pag.activo { background: #1B396A; color: white; border-color: #1B396A; }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.modal-content { background: #FFFFFF; width: 440px; max-width: 92%; border-radius: 12px; box-shadow: 0 20px 50px rgba(0,0,0,0.18); overflow: hidden; border: 1px solid var(--borde); }
.modal-header { background: #1B396A; color: white; padding: 0.9rem 1.4rem; display: flex; justify-content: space-between; align-items: center; }
.modal-header h3 { margin: 0; font-size: 1.1rem; font-weight: 700; font-family: 'Montserrat', sans-serif; }
.btn-cerrar-modal { background: none; border: none; color: white; font-size: 1.6rem; cursor: pointer; line-height: 1; opacity: 0.85; }
.btn-cerrar-modal:hover { opacity: 1; }
.modal-body { padding: 1.3rem 1.4rem; }
.modal-nombre { font-weight: 700; color: var(--azul); font-size: 0.97rem; margin: 0 0 1rem; font-family: 'Montserrat', sans-serif; }
.form-grupo { margin-bottom: 1rem; }
.form-grupo label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.86rem; color: var(--texto); font-family: 'Montserrat', sans-serif; }
.obligatorio { color: #DC2626; }
.modal-input, .modal-select { width: 100%; padding: 8px 12px; border: 1.5px solid var(--borde); border-radius: 7px; font-size: 0.9rem; background: #FFFFFF; color: var(--texto); font-family: 'Montserrat', sans-serif; outline: none; transition: border-color 0.2s; box-sizing: border-box; }
.modal-input:focus, .modal-select:focus { border-color: #1B396A; }
.modal-footer { padding: 0.8rem 1.4rem; background: var(--fondo); display: flex; gap: 8px; justify-content: flex-end; border-top: 1px solid var(--borde); }
.btn-secundario { padding: 8px 18px; border-radius: 7px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); transition: background 0.15s; font-size: 0.88rem; }
.btn-secundario:hover { background: var(--fondo); }
.btn-secundario:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-guardar { display: flex; align-items: center; gap: 7px; padding: 8px 18px; border-radius: 7px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #1B396A; color: white; border: none; transition: background 0.15s; font-size: 0.88rem; }
.btn-guardar:hover:not(:disabled) { background: #1D4ED8; }
.btn-guardar:disabled { opacity: 0.65; cursor: not-allowed; }
.spinner-btn { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.4); border-top-color: white; border-radius: 50%; animation: girar 0.7s linear infinite; flex-shrink: 0; }

@keyframes girar { to { transform: rotate(360deg); } }
</style>