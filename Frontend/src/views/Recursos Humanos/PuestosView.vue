<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="puestos-page">

      <nav class="breadcrumb">
        <router-link to="/inicio" class="breadcrumb-link">Inicio</router-link>
        <span class="sep">›</span>
        <router-link to="/recursos-humanos" class="breadcrumb-link">Recursos Humanos</router-link>
        <span class="sep">›</span>
        <span class="breadcrumb-actual">Puestos</span>
      </nav>

      <div class="page-header">
        <h1 class="page-title">Puestos</h1>
        <span class="page-subtitle">{{ puestosFiltrados.length }} registro(s) encontrado(s)</span>
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
            placeholder="Buscar por nombre de puesto..."
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
        <button class="btn-nuevo" @click="nuevoPuesto">+ Nuevo Puesto</button>
      </div>

      <div class="table-container">
        <div v-if="cargando && puestos.length === 0" class="estado-cargando">
          <div class="spinner-tabla"></div>
          <p>Cargando registros...</p>
        </div>

        <table v-else-if="paginatedPuestos.length > 0" class="puestos-table" ref="tablaRef" @keydown="navegarTeclado" tabindex="0">
          <thead>
            <tr>
              <th>Nombre del Puesto</th>
              <th>Descripción</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(puesto, index) in paginatedPuestos"
              :key="puesto.id_puesto || puesto.id"
              :class="{ 'fila-seleccionada': filaActiva === index }"
              @click="filaActiva = index"
            >
              <td class="celda-nombre">{{ puesto.nombre_puesto }}</td>
              <td class="celda-desc">{{ puesto.descripcion || '—' }}</td>
              <td class="celda-acciones">
                <button class="btn-accion ver" @click.stop="abrirModalVer(puesto)">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Ver
                </button>
                <button class="btn-accion editar" @click.stop="abrirModalEditar(puesto)">
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3>Sin resultados</h3>
          <p>No se encontraron puestos con ese criterio.</p>
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
        <div class="paginacion-centro">Página {{ currentPage }} de {{ totalPages }}</div>
        <div class="paginacion-derecha">
          <button class="btn-pag" @click="prevPage" :disabled="currentPage === 1">‹</button>
          <button v-for="page in visiblePages" :key="page" class="btn-pag" :class="{ activo: page === currentPage }" @click="goToPage(page)">{{ page }}</button>
          <button class="btn-pag" @click="nextPage" :disabled="currentPage === totalPages">›</button>
        </div>
      </div>

    </div>

    <!-- Modal Ver -->
    <div v-if="showViewModal" class="modal-overlay" @click.self="cerrarModalVer">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Detalle del Puesto</h3>
          <button @click="cerrarModalVer" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">
          <div class="detalle-fila"><span class="detalle-label">Nombre</span><span class="detalle-valor">{{ puestoVer.nombre_puesto }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Descripción</span><span class="detalle-valor">{{ puestoVer.descripcion || '—' }}</span></div>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModalVer">Cerrar</button>
        </div>
      </div>
    </div>

    <!-- Modal Crear / Editar -->
    <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ puestoEditar.id_puesto ? 'Editar Puesto' : 'Nuevo Puesto' }}</h3>
          <button @click="cerrarModal" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">
          <div class="form-grupo">
            <label>Nombre del Puesto <span class="obligatorio">*</span></label>
            <input v-model="puestoEditar.nombre_puesto" type="text" class="modal-input" placeholder="Ej. Docente de Tiempo Completo" />
          </div>
          <div class="form-grupo">
            <label>Descripción</label>
            <textarea v-model="puestoEditar.descripcion" class="modal-textarea" rows="4" placeholder="Descripción del puesto (opcional)..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">Cancelar</button>
          <button class="btn-guardar" @click="guardarPuesto" :disabled="guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            {{ guardando ? 'Guardando...' : 'Guardar' }}
          </button>
        </div>
      </div>
    </div>

  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'
import { crearPuesto, actualizarPuesto, eliminarPuesto as eliminarPuestoAPI } from '@/api/recursosHumanos'

const puestos         = ref([])
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

const normalizarPuesto = (p) => ({
  id_puesto:    p.id_puesto || p.id,
  nombre_puesto: p.nombre_puesto || p.nombre || '',
  descripcion:  p.descripcion || '',
})

import { getPuestos } from '@/api/recursosHumanos'

  const cargarPuestos = async () => {
    cargando.value = true
    try {
      const res = await getPuestos()
      if (!res.success) throw new Error()
      puestos.value = res.data.map(normalizarPuesto)
      console.log('✅ Puestos cargados:', puestos.value.length)
    } catch (error) {
      console.error(error)
      mostrarNotificacion('No se pudo cargar la lista de puestos.', 'error')
    } finally {
      cargando.value = false
    }
  }

onMounted(() => { cargarPuestos() })

let timerBusqueda = null
watch(busqueda, () => {
  cargandoBusqueda.value = true
  if (timerBusqueda) clearTimeout(timerBusqueda)
  timerBusqueda = setTimeout(() => { cargandoBusqueda.value = false; currentPage.value = 1 }, 350)
})

const puestosFiltrados = computed(() =>
  !busqueda.value ? puestos.value
    : puestos.value.filter(p => normalize(p.nombre_puesto).includes(normalize(busqueda.value)))
)

const totalPages = computed(() => Math.ceil(puestosFiltrados.value.length / filasPorPagina.value) || 1)
const paginatedPuestos = computed(() => {
  const start = (currentPage.value - 1) * filasPorPagina.value
  return puestosFiltrados.value.slice(start, start + filasPorPagina.value)
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

// Modales
const showViewModal = ref(false)
const puestoVer    = ref({})
const showModal    = ref(false)
const puestoEditar = ref({})

const abrirModalVer    = (p) => { puestoVer.value = { ...p }; showViewModal.value = true }
const cerrarModalVer   = () => { showViewModal.value = false }
const abrirModalEditar = (p) => { puestoEditar.value = { ...p }; showModal.value = true }
const nuevoPuesto      = () => { puestoEditar.value = { id_puesto: null, nombre_puesto: '', descripcion: '' }; showModal.value = true }
const cerrarModal      = () => { showModal.value = false }

  const guardarPuesto = async () => {
    const puesto = puestoEditar.value
    guardando.value = true
    try {
      const datos = {
        nombre_puesto: puesto.nombre_puesto,
        descripcion: puesto.descripcion
      }
      if (puesto.id_puesto) {
        const res = await actualizarPuesto(puesto.id_puesto, datos)
        if (res.success) {
          mostrarNotificacion('Puesto actualizado', 'exito')
          cerrarModal()
          cargarPuestos()
        }
      } else {
        const res = await crearPuesto(datos)
        if (res.success) {
          mostrarNotificacion('Puesto creado', 'exito')
          cerrarModal()
          cargarPuestos()
        }
      }
    } catch (error) {
      console.error(error)
      mostrarNotificacion('Error al guardar', 'error')
    } finally {
      guardando.value = false
    }
  }

    const eliminarPuesto = async (id) => {
    if (!confirm('¿Eliminar puesto?')) return
    try {
      const res = await eliminarPuestoAPI(id)
      if (res.success) {
        mostrarNotificacion('Puesto eliminado', 'exito')
        cargarPuestos()
      } else if (res.error?.includes('empleado')) {
        mostrarNotificacion('No se puede eliminar: tiene empleados asignados', 'error')
      }
    } catch (error) {
      console.error(error)
      mostrarNotificacion('Error al eliminar', 'error')
    }
  }

const navegarTeclado = (e) => {
  const total = paginatedPuestos.value.length
  if (!total) return
  if (e.key === 'ArrowDown') { e.preventDefault(); filaActiva.value = Math.min(filaActiva.value + 1, total - 1) }
  else if (e.key === 'ArrowUp') { e.preventDefault(); filaActiva.value = Math.max(filaActiva.value - 1, 0) }
  else if (e.key === 'Enter' && filaActiva.value >= 0) { e.preventDefault(); abrirModalVer(paginatedPuestos.value[filaActiva.value]) }
  else if (e.key === 'PageDown') { e.preventDefault(); nextPage() }
  else if (e.key === 'PageUp') { e.preventDefault(); prevPage() }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.puestos-page {
  --azul: #1B396A; --azul-hover: #1D4ED8; --borde: #E5E7EB;
  --fondo: #F5F5F5; --texto: #1A1A1A; --gris: #6B7280;
  max-width: 100%; background: var(--fondo); font-family: 'Montserrat', sans-serif;
}

.breadcrumb { display: flex; align-items: center; gap: 0.4rem; color: var(--gris); font-size: 0.88rem; margin-bottom: 0.75rem; }
.breadcrumb .sep { color: #E5E7EB; }
.breadcrumb-link { color: var(--gris); text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: var(--azul); }
.breadcrumb-actual { color: var(--azul); font-weight: 600; }

.page-header { display: flex; align-items: baseline; gap: 1rem; margin-bottom: 1.2rem; }
.page-title { color: var(--texto); font-size: 1.75rem; font-weight: 700; letter-spacing: -0.02em; margin: 0; }
.page-subtitle { font-size: 0.9rem; color: var(--gris); font-weight: 500; }

.barra-carga-global { height: 3px; background: transparent; border-radius: 2px; margin-bottom: 1rem; overflow: hidden; transition: opacity 0.3s; opacity: 0; }
.barra-carga-global.visible { opacity: 1; }
.barra-progreso { height: 100%; width: 40%; background: #1B396A; border-radius: 2px; animation: deslizar 1.2s ease-in-out infinite; }
@keyframes deslizar { 0% { transform: translateX(-100%); } 100% { transform: translateX(350%); } }

.notificacion-ui { display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 10px; font-size: 0.93rem; font-weight: 500; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.notificacion-ui.exito { background: #DCFCE7; color: #16A34A; border: 1px solid #86EFAC; }
.notificacion-ui.error { background: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5; }
.notif-icono { width: 20px; height: 20px; flex-shrink: 0; }
.notif-fade-enter-active, .notif-fade-leave-active { transition: all 0.35s ease; }
.notif-fade-enter-from, .notif-fade-leave-to { opacity: 0; transform: translateY(-8px); }

.filters-bar { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.2rem; flex-wrap: wrap; }
.search-group { position: relative; flex: 0 0 320px; min-width: 220px; }
.search-input { width: 100%; padding: 10px 14px 10px 42px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.93rem; background: #FFFFFF; color: var(--texto); font-family: 'Montserrat', sans-serif; outline: none; transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box; }
.search-input:focus { border-color: #1B396A; box-shadow: 0 0 0 3px #DBEAFE; }
.search-input::placeholder { color: #9CA3AF; }
.search-icon-svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; stroke: var(--gris); pointer-events: none; }
.spinner-busqueda { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; border: 2px solid #E5E7EB; border-top-color: #1B396A; border-radius: 50%; animation: girar 0.7s linear infinite; }
.btn-limpiar { display: flex; align-items: center; gap: 6px; background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); padding: 10px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.92rem; white-space: nowrap; font-family: 'Montserrat', sans-serif; transition: background 0.15s; }
.btn-limpiar:hover { background: var(--fondo); }
.reset-icon { width: 16px; height: 16px; stroke: var(--gris); }
.btn-nuevo { background: #1B396A; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; white-space: nowrap; font-family: 'Montserrat', sans-serif; font-size: 0.92rem; transition: background 0.2s; margin-left: auto; }
.btn-nuevo:hover { background: #1D4ED8; }

.table-container { background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid var(--borde); }
.puestos-table { width: 100%; border-collapse: collapse; outline: none; }
.puestos-table th { background: var(--fondo); padding: 12px 16px; text-align: left; font-weight: 600; font-size: 0.88rem; color: var(--texto); border-bottom: 1px solid var(--borde); font-family: 'Montserrat', sans-serif; white-space: nowrap; }
.puestos-table td { padding: 11px 16px; border-bottom: 1px solid var(--borde); color: var(--texto); font-size: 0.93rem; font-family: 'Montserrat', sans-serif; }
.puestos-table tbody tr { transition: background 0.15s; cursor: pointer; }
.puestos-table tbody tr:hover { background: #F8FAFC; }
.puestos-table tbody tr:last-child td { border-bottom: none; }
.fila-seleccionada { background: #DBEAFE !important; }
.celda-nombre { font-weight: 600; color: var(--texto); }
.celda-desc { color: var(--gris); font-size: 0.9rem; max-width: 400px; }

.celda-acciones { display: flex; gap: 7px; align-items: center; }
.btn-accion { display: flex; align-items: center; gap: 5px; padding: 6px 13px; border-radius: 6px; font-size: 0.85rem; cursor: pointer; font-weight: 600; font-family: 'Montserrat', sans-serif; transition: background 0.15s; white-space: nowrap; border: none; }
.btn-accion svg { width: 14px; height: 14px; }
.btn-accion.ver { background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); }
.btn-accion.ver:hover { background: var(--fondo); }
.btn-accion.editar { background: #1B396A; color: white; }
.btn-accion.editar:hover { background: #1D4ED8; }

.estado-vacio, .estado-cargando { text-align: center; padding: 3.5rem 2rem; color: var(--gris); }
.icono-vacio { width: 56px; height: 56px; stroke: #9CA3AF; margin-bottom: 12px; }
.estado-vacio h3 { font-size: 1.2rem; color: var(--texto); margin: 0 0 6px; }
.estado-vacio p { font-size: 0.93rem; margin: 0 0 1.2rem; }
.btn-limpiar-vacio { background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); padding: 9px 20px; border-radius: 8px; font-weight: 500; cursor: pointer; font-family: 'Montserrat', sans-serif; }
.spinner-tabla { display: inline-block; width: 36px; height: 36px; border: 3px solid #E5E7EB; border-top-color: #1B396A; border-radius: 50%; animation: girar 0.8s linear infinite; margin-bottom: 12px; }

.paginacion { margin-top: 1.2rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; color: var(--gris); font-family: 'Montserrat', sans-serif; flex-wrap: wrap; gap: 0.5rem; }
.paginacion-izquierda, .paginacion-centro, .paginacion-derecha { display: flex; align-items: center; gap: 8px; }
.select-filas { border: 1px solid var(--borde); border-radius: 6px; padding: 4px 8px; font-size: 0.9rem; background: #FFFFFF; font-family: 'Montserrat', sans-serif; }
.btn-pag { padding: 5px 11px; border: 1px solid var(--borde); background: #FFFFFF; border-radius: 6px; cursor: pointer; color: var(--texto); font-family: 'Montserrat', sans-serif; font-size: 0.9rem; transition: background 0.15s; }
.btn-pag:hover:not(:disabled) { background: var(--fondo); }
.btn-pag:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-pag.activo { background: #1B396A; color: white; border-color: #1B396A; }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.modal-content { background: #FFFFFF; width: 480px; max-width: 92%; border-radius: 14px; box-shadow: 0 20px 50px rgba(0,0,0,0.18); overflow: hidden; border: 1px solid var(--borde); }
.modal-header { background: #1B396A; color: white; padding: 1.1rem 1.6rem; display: flex; justify-content: space-between; align-items: center; }
.modal-header h3 { margin: 0; font-size: 1.2rem; font-weight: 700; font-family: 'Montserrat', sans-serif; }
.btn-cerrar-modal { background: none; border: none; color: white; font-size: 1.7rem; cursor: pointer; line-height: 1; opacity: 0.85; }
.btn-cerrar-modal:hover { opacity: 1; }
.modal-body { padding: 1.6rem; }
.form-grupo { margin-bottom: 1.2rem; }
.form-grupo label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: var(--texto); font-family: 'Montserrat', sans-serif; }
.obligatorio { color: #DC2626; }
.modal-input { width: 100%; padding: 10px 14px; border: 1.5px solid var(--borde); border-radius: 8px; font-size: 0.95rem; background: #FFFFFF; color: var(--texto); font-family: 'Montserrat', sans-serif; outline: none; transition: border-color 0.2s; box-sizing: border-box; }
.modal-input:focus { border-color: #1B396A; }
.modal-textarea { width: 100%; padding: 10px 14px; border: 1.5px solid var(--borde); border-radius: 8px; font-size: 0.95rem; background: #FFFFFF; color: var(--texto); font-family: 'Montserrat', sans-serif; outline: none; transition: border-color 0.2s; box-sizing: border-box; resize: vertical; min-height: 90px; }
.modal-textarea:focus { border-color: #1B396A; }
.modal-footer { padding: 1rem 1.6rem; background: var(--fondo); display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid var(--borde); }
.btn-secundario { padding: 10px 22px; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); transition: background 0.15s; }
.btn-secundario:hover { background: var(--fondo); }
.btn-secundario:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-guardar { display: flex; align-items: center; gap: 8px; padding: 10px 22px; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #1B396A; color: white; border: none; transition: background 0.15s; }
.btn-guardar:hover:not(:disabled) { background: #1D4ED8; }
.btn-guardar:disabled { opacity: 0.65; cursor: not-allowed; }
.spinner-btn { display: inline-block; width: 15px; height: 15px; border: 2px solid rgba(255,255,255,0.4); border-top-color: white; border-radius: 50%; animation: girar 0.7s linear infinite; flex-shrink: 0; }

.detalle-fila { display: flex; justify-content: space-between; align-items: flex-start; padding: 11px 0; border-bottom: 1px solid var(--borde); font-size: 0.95rem; color: var(--texto); font-family: 'Montserrat', sans-serif; gap: 1rem; }
.detalle-fila:last-child { border-bottom: none; }
.detalle-label { font-weight: 600; color: var(--gris); flex-shrink: 0; }
.detalle-valor { font-weight: 500; text-align: right; }

@keyframes girar { to { transform: rotate(360deg); } }
</style>