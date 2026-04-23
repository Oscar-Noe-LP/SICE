<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="bitacora-page">

      <!-- ══ Encabezado ══ -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Bitácora del Sistema</h1>
          <span class="page-subtitle">Registro de actividades — {{ registrosFiltrados.length }} evento(s)</span>
        </div>
        <button class="btn-exportar" @click="exportarCSV" title="Exportar a CSV">
          <svg xmlns="http://www.w3.org/2000/svg" class="exportar-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Exportar
        </button>
      </div>

      <!-- ══ Barra de carga global ══ -->
      <div class="barra-carga-global" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- ══ Notificación de error ══ -->
      <transition name="notif-fade">
        <div v-if="notificacion.visible" class="notificacion-ui error">
          <svg xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- ══ Tarjeta de filtros ══ -->
      <div class="filtros-card">
        <div class="filtros-fila">

          <!-- Buscador por usuario -->
          <div class="filtro-grupo filtro-busqueda">
            <label class="filtro-label">Buscar usuario</label>
            <div class="search-group">
              <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input
                type="text"
                placeholder="Nombre de usuario..."
                v-model="filtros.usuario"
                class="filtro-input"
                @keydown.escape="filtros.usuario = ''"
              >
            </div>
          </div>

          <!-- Selector de módulo -->
          <div class="filtro-grupo">
            <label class="filtro-label">Módulo</label>
            <select v-model="filtros.modulo" class="filtro-select" @change="currentPage = 1">
              <option value="">Todos los módulos</option>
              <option v-for="m in modulosDisponibles" :key="m" :value="m">{{ m }}</option>
            </select>
          </div>

          <!-- Selector de tipo de acción -->
          <div class="filtro-grupo">
            <label class="filtro-label">Tipo de acción</label>
            <select v-model="filtros.accion" class="filtro-select" @change="currentPage = 1">
              <option value="">Todas las acciones</option>
              <option value="Login">Login</option>
              <option value="Creación">Creación</option>
              <option value="Edición">Edición</option>
              <option value="Eliminación">Eliminación</option>
            </select>
          </div>

          <!-- Date picker: Fecha desde -->
          <div class="filtro-grupo">
            <label class="filtro-label">Fecha desde</label>
            <input
              type="date"
              v-model="filtros.fechaDesde"
              class="filtro-input filtro-fecha"
              :max="filtros.fechaHasta || hoyISO"
              @change="currentPage = 1"
            >
          </div>

          <!-- Date picker: Fecha hasta -->
          <div class="filtro-grupo">
            <label class="filtro-label">Fecha hasta</label>
            <input
              type="date"
              v-model="filtros.fechaHasta"
              class="filtro-input filtro-fecha"
              :min="filtros.fechaDesde"
              :max="hoyISO"
              @change="currentPage = 1"
            >
          </div>

          <!-- Botón limpiar filtros -->
          <div class="filtro-grupo filtro-accion">
            <label class="filtro-label">&nbsp;</label>
            <button class="btn-limpiar" @click="resetFiltros">
              <svg xmlns="http://www.w3.org/2000/svg" class="reset-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Limpiar
            </button>
          </div>

        </div>

        <!-- Resumen de filtros activos -->
        <div v-if="tieneFiltrosActivos" class="filtros-activos">
          <span class="filtros-activos-label">Filtros activos:</span>
          <span v-if="filtros.usuario"     class="filtro-chip">Usuario: {{ filtros.usuario }}</span>
          <span v-if="filtros.modulo"      class="filtro-chip">Módulo: {{ filtros.modulo }}</span>
          <span v-if="filtros.accion"      class="filtro-chip">Acción: {{ filtros.accion }}</span>
          <span v-if="filtros.fechaDesde"  class="filtro-chip">Desde: {{ filtros.fechaDesde }}</span>
          <span v-if="filtros.fechaHasta"  class="filtro-chip">Hasta: {{ filtros.fechaHasta }}</span>
        </div>
      </div>

      <!-- ══ Tabla de bitácora ══ -->
      <div class="table-container">

        <!-- Estado: cargando inicial -->
        <div v-if="cargando && bitacora.length === 0" class="estado-cargando">
          <div class="spinner-tabla"></div>
          <p>Cargando registros...</p>
        </div>

        <table
          v-else-if="paginatedRegistros.length > 0"
          class="bitacora-table"
          ref="tablaRef"
          @keydown="navegarTeclado"
          tabindex="0"
        >
          <thead>
            <tr>
              <th class="th-fecha">
                <div class="th-ordenable" @click="toggleOrden">
                  Fecha y Hora
                  <svg xmlns="http://www.w3.org/2000/svg" class="orden-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          :d="ordenAsc ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'" />
                  </svg>
                </div>
              </th>
              <th>Usuario</th>
              <th>Acción</th>
              <th>Módulo</th>
              <th>Descripción</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(registro, index) in paginatedRegistros"
              :key="registro.id_bitacora || registro.id"
              :class="{ 'fila-seleccionada': filaActiva === index }"
              @click="filaActiva = index; abrirDetalle(registro)"
              class="fila-registro"
            >
              <td class="celda-fecha">
                <div class="fecha-principal">{{ formatearFecha(registro.fecha_hora) }}</div>
                <div class="fecha-hora">{{ formatearHora(registro.fecha_hora) }}</div>
              </td>
              <td>
                <div class="celda-usuario">
                  <div class="avatar-mini">{{ inicial(registro.usuario) }}</div>
                  <span class="usuario-texto">{{ registro.usuario }}</span>
                </div>
              </td>
              <td>
                <span class="accion-badge" :class="claseAccion(registro.accion)">
                  {{ registro.accion }}
                </span>
              </td>
              <td class="celda-modulo">{{ registro.modulo }}</td>
              <td class="celda-descripcion">{{ registro.descripcion }}</td>
            </tr>
          </tbody>
        </table>

        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 12h6m-6 4h6" />
          </svg>
          <h3>Sin registros</h3>
          <p>No se encontraron eventos con los filtros aplicados.</p>
          <button class="btn-limpiar-vacio" @click="resetFiltros">Limpiar filtros</button>
        </div>
      </div>

      <!-- ══ Paginación ══ -->
      <div class="paginacion">
        <div class="paginacion-izquierda">
          Filas por página:
          <select v-model="filasPorPagina" @change="currentPage = 1" class="select-filas">
            <option :value="20">20</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
        </div>
        <div class="paginacion-centro">
          Mostrando {{ rangoInicio }}–{{ rangoFin }} de {{ registrosFiltrados.length }}
        </div>
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

    </div>

    <!-- ══════════════════════════════════════
         MODAL: DETALLE DE EVENTO
         Solo lectura — clic en fila lo abre
    ═══════════════════════════════════════ -->
    <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-content">
        <div class="modal-header" :class="claseHeaderModal(registroDetalle.accion)">
          <div class="modal-header-info">
            <span class="accion-badge accion-badge-grande" :class="claseAccion(registroDetalle.accion)">
              {{ registroDetalle.accion }}
            </span>
            <h3>Detalle del Evento</h3>
          </div>
          <button @click="cerrarModal" class="btn-cerrar-modal">×</button>
        </div>

        <div class="modal-body">
          <div class="detalle-fila">
            <span class="detalle-label">Fecha y Hora</span>
            <span class="detalle-valor">
              {{ formatearFecha(registroDetalle.fecha_hora) }}
              {{ formatearHora(registroDetalle.fecha_hora) }}
            </span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Usuario</span>
            <div class="celda-usuario">
              <div class="avatar-mini">{{ inicial(registroDetalle.usuario) }}</div>
              <span class="usuario-texto">{{ registroDetalle.usuario }}</span>
            </div>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Módulo</span>
            <span class="detalle-valor">{{ registroDetalle.modulo }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Acción</span>
            <span class="accion-badge" :class="claseAccion(registroDetalle.accion)">
              {{ registroDetalle.accion }}
            </span>
          </div>
          <div class="detalle-fila detalle-descripcion">
            <span class="detalle-label">Descripción</span>
            <span class="detalle-valor detalle-desc-texto">{{ registroDetalle.descripcion }}</span>
          </div>
          <!-- Campo de IP o información adicional cuando el backend la devuelva -->
          <div class="detalle-fila" v-if="registroDetalle.ip">
            <span class="detalle-label">Dirección IP</span>
            <code class="detalle-codigo">{{ registroDetalle.ip }}</code>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModal">Cerrar</button>
        </div>
      </div>
    </div>

  </MainLayout>
</template>


<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

// ── URL base del backend (variable de entorno) ──────────────────────
const API_URL = import.meta.env.VITE_API_URL

// ── Estado principal ────────────────────────────────────────────────
const bitacora   = ref([])
const cargando   = ref(false)
const filaActiva = ref(-1)
const tablaRef   = ref(null)
const ordenAsc   = ref(false)

// ── Paginación ─────────────────────────────────────────────────────
const filasPorPagina = ref(20)
const currentPage    = ref(1)

// ── Fecha de hoy ────────────────────────────────────────────────────
const hoyISO = new Date().toISOString().split('T')[0]

// ── Filtros ─────────────────────────────────────────────────────────
const filtros = ref({
  usuario:    '',
  modulo:     '',
  accion:     '',
  fechaDesde: '',
  fechaHasta: ''
})

const modulosDisponibles = computed(() => {
  const set = new Set(bitacora.value.map(r => r.modulo).filter(Boolean))
  return [...set].sort()
})

// ── Notificación de error ────────────────────────────────────────────
const notificacion = ref({ visible: false, mensaje: '' })
let timerNotif = null

const mostrarError = (mensaje) => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 4000)
}

// ── Modal de detalle ─────────────────────────────────────────────────
const showModal       = ref(false)
const registroDetalle = ref({})

const abrirDetalle = (registro) => {
  registroDetalle.value = { ...registro }
  showModal.value = true
}
const cerrarModal = () => { showModal.value = false }

// ── Carga de bitácora desde backend ─────────────────────────────────
// Endpoint: GET /api/bitacora
const cargarBitacora = async () => {
  cargando.value = true
  try {
    const response = await fetch(`${API_URL}/api/bitacora`)
    if (!response.ok) throw new Error('Error del servidor')
    const data = await response.json()
    bitacora.value = data
    console.log('✅ Bitácora cargada:', data.length, 'registros')
  } catch (error) {
    console.error('❌ Error cargando bitácora:', error)
    mostrarError('No se pudo cargar la bitácora. Verifica que el servidor esté activo.')
  } finally {
    cargando.value = false
  }
}

onMounted(() => { cargarBitacora() })

watch(() => filtros.value.usuario, () => { currentPage.value = 1 })

// ── Helpers ─────────────────────────────────────────────────────────
const normalize = (text) => {
  if (!text) return ''
  return text.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
}

const inicial = (nombre) => {
  if (!nombre) return '?'
  return nombre.charAt(0).toUpperCase()
}

const formatearFecha = (fechaHora) => {
  if (!fechaHora) return '—'
  const d = new Date(fechaHora)
  return d.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })
}

const formatearHora = (fechaHora) => {
  if (!fechaHora) return ''
  const d = new Date(fechaHora)
  return d.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}

const claseAccion = (accion) => {
  if (!accion) return ''
  const a = normalize(accion)
  if (a === 'login')      return 'accion-login'
  if (a === 'creación' || a === 'creacion') return 'accion-creacion'
  if (a === 'edición'  || a === 'edicion')  return 'accion-edicion'
  if (a === 'eliminación' || a === 'eliminacion') return 'accion-eliminacion'
  return 'accion-default'
}

const claseHeaderModal = (accion) => {
  const a = normalize(accion || '')
  if (a === 'eliminación' || a === 'eliminacion') return 'header-rojo'
  if (a === 'edición'     || a === 'edicion')     return 'header-amarillo'
  if (a === 'creación'    || a === 'creacion')     return 'header-verde'
  return 'header-azul'
}

// ── Filtrado ─────────────────────────────────────────────────────────
const registrosFiltrados = computed(() => {
  let lista = [...bitacora.value]

  if (filtros.value.usuario) {
    lista = lista.filter(r =>
      normalize(r.usuario).includes(normalize(filtros.value.usuario))
    )
  }
  if (filtros.value.modulo) {
    lista = lista.filter(r =>
      normalize(r.modulo) === normalize(filtros.value.modulo)
    )
  }
  if (filtros.value.accion) {
    lista = lista.filter(r =>
      normalize(r.accion) === normalize(filtros.value.accion)
    )
  }
  if (filtros.value.fechaDesde) {
    lista = lista.filter(r =>
      r.fecha_hora && r.fecha_hora.split('T')[0] >= filtros.value.fechaDesde
    )
  }
  if (filtros.value.fechaHasta) {
    lista = lista.filter(r =>
      r.fecha_hora && r.fecha_hora.split('T')[0] <= filtros.value.fechaHasta
    )
  }

  lista.sort((a, b) => {
    const da = new Date(a.fecha_hora), db = new Date(b.fecha_hora)
    return ordenAsc.value ? da - db : db - da
  })

  return lista
})

const tieneFiltrosActivos = computed(() =>
  filtros.value.usuario || filtros.value.modulo ||
  filtros.value.accion  || filtros.value.fechaDesde || filtros.value.fechaHasta
)

// ── Paginación ───────────────────────────────────────────────────────
const totalPages = computed(() =>
  Math.ceil(registrosFiltrados.value.length / filasPorPagina.value) || 1
)
const paginatedRegistros = computed(() => {
  const start = (currentPage.value - 1) * filasPorPagina.value
  return registrosFiltrados.value.slice(start, start + filasPorPagina.value)
})
const rangoInicio = computed(() =>
  registrosFiltrados.value.length === 0 ? 0 : (currentPage.value - 1) * filasPorPagina.value + 1
)
const rangoFin = computed(() =>
  Math.min(currentPage.value * filasPorPagina.value, registrosFiltrados.value.length)
)
const visiblePages = computed(() => {
  const total = totalPages.value, current = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = new Set([1, total, current, current - 1, current + 1].filter(p => p >= 1 && p <= total))
  return [...pages].sort((a, b) => a - b)
})

const goToPage  = (page) => { currentPage.value = page; filaActiva.value = -1 }
const prevPage  = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage  = () => { if (currentPage.value < totalPages.value) currentPage.value++ }
const toggleOrden = () => { ordenAsc.value = !ordenAsc.value; currentPage.value = 1 }

const resetFiltros = () => {
  filtros.value  = { usuario: '', modulo: '', accion: '', fechaDesde: '', fechaHasta: '' }
  currentPage.value = 1
  filaActiva.value  = -1
}

// ── Exportar a CSV ────────────────────────────────────────────────────
const exportarCSV = () => {
  const cabeceras = ['Fecha y Hora', 'Usuario', 'Acción', 'Módulo', 'Descripción']
  const filas = registrosFiltrados.value.map(r => [
    `${formatearFecha(r.fecha_hora)} ${formatearHora(r.fecha_hora)}`,
    r.usuario || '',
    r.accion  || '',
    r.modulo  || '',
    (r.descripcion || '').replace(/,/g, ';')
  ])
  const contenido = [cabeceras, ...filas].map(f => f.join(',')).join('\n')
  const blob = new Blob(['\uFEFF' + contenido], { type: 'text/csv;charset=utf-8;' })
  const url  = URL.createObjectURL(blob)
  const a    = document.createElement('a')
  a.href     = url
  a.download = `bitacora_${hoyISO}.csv`
  a.click()
  URL.revokeObjectURL(url)
}

// ── Navegación por teclado ───────────────────────────────────────────
const navegarTeclado = (e) => {
  const total = paginatedRegistros.value.length
  if (total === 0) return
  if (e.key === 'ArrowDown') { e.preventDefault(); filaActiva.value = Math.min(filaActiva.value + 1, total - 1) }
  else if (e.key === 'ArrowUp') { e.preventDefault(); filaActiva.value = Math.max(filaActiva.value - 1, 0) }
  else if (e.key === 'Enter' && filaActiva.value >= 0) { e.preventDefault(); abrirDetalle(paginatedRegistros.value[filaActiva.value]) }
  else if (e.key === 'PageDown') { e.preventDefault(); nextPage() }
  else if (e.key === 'PageUp')   { e.preventDefault(); prevPage() }
  else if (e.key === 'Escape')   { cerrarModal() }
}
</script>


<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.bitacora-page {
  --azul:       #1B396A;
  --azul-hover: #1D4ED8;
  --azul-suave: #DBEAFE;
  --borde:      #E5E7EB;
  --fondo:      #F5F5F5;
  --texto:      #1A1A1A;
  --gris:       #6B7280;

  max-width: 100%;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
}

/* ══ Encabezado ══ */
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 1.2rem;
}
.page-title    { color: var(--texto); font-size: 1.75rem; font-weight: 700; letter-spacing: -0.02em; margin: 0; }
.page-subtitle { font-size: 0.88rem; color: var(--gris); font-weight: 500; display: block; margin-top: 3px; }

.btn-exportar {
  display: flex; align-items: center; gap: 7px;
  background: #FFFFFF; color: var(--azul);
  border: 1px solid #BFDBFE;
  padding: 9px 18px; border-radius: 8px;
  font-weight: 600; font-size: 0.88rem;
  cursor: pointer; font-family: 'Montserrat', sans-serif;
  transition: background 0.15s; flex-shrink: 0;
}
.btn-exportar:hover { background: var(--azul-suave); }
.exportar-icono { width: 17px; height: 17px; stroke: var(--azul); }

/* ══ Barra de carga ══ */
.barra-carga-global { height: 3px; background: transparent; border-radius: 2px; margin-bottom: 1rem; overflow: hidden; opacity: 0; transition: opacity 0.3s; }
.barra-carga-global.visible { opacity: 1; }
.barra-progreso { height: 100%; width: 40%; background: var(--azul); border-radius: 2px; animation: deslizar 1.2s ease-in-out infinite; }
@keyframes deslizar { 0% { transform: translateX(-100%); } 100% { transform: translateX(350%); } }

/* ══ Notificación ══ */
.notificacion-ui { display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 10px; font-size: 0.93rem; font-weight: 500; margin-bottom: 1rem; background: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5; }
.notif-icono { width: 20px; height: 20px; flex-shrink: 0; }
.notif-fade-enter-active, .notif-fade-leave-active { transition: all 0.35s ease; }
.notif-fade-enter-from, .notif-fade-leave-to { opacity: 0; transform: translateY(-8px); }

/* ══ Tarjeta de filtros ══ */
.filtros-card {
  background: #FFFFFF;
  border-radius: 12px;
  padding: 1.2rem 1.4rem;
  margin-bottom: 1.2rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border: 1px solid var(--borde);
}
.filtros-fila {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  align-items: flex-end;
}
.filtro-grupo { display: flex; flex-direction: column; gap: 5px; flex: 1 1 160px; min-width: 140px; }
.filtro-busqueda { flex: 1 1 240px; min-width: 200px; }
.filtro-accion   { flex: 0 0 auto; }

.filtro-label {
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--gris);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-family: 'Montserrat', sans-serif;
}

/* Input de búsqueda dentro de filtros */
.search-group { position: relative; }
.search-group .filtro-input { padding-left: 38px; }
.search-icon-svg { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; stroke: var(--gris); pointer-events: none; }

.filtro-input, .filtro-select {
  width: 100%;
  padding: 9px 12px;
  border: 1.5px solid var(--borde);
  border-radius: 8px;
  font-size: 0.9rem;
  background: #FFFFFF;
  color: var(--texto);
  font-family: 'Montserrat', sans-serif;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box;
}
.filtro-input:focus, .filtro-select:focus { border-color: var(--azul); box-shadow: 0 0 0 3px var(--azul-suave); }
.filtro-input::placeholder { color: #9CA3AF; }
.filtro-fecha { cursor: pointer; }

.btn-limpiar {
  display: flex; align-items: center; gap: 6px;
  background: #FFFFFF; color: var(--texto);
  border: 1px solid var(--borde);
  padding: 9px 16px; border-radius: 8px;
  font-weight: 600; cursor: pointer;
  font-size: 0.9rem; white-space: nowrap;
  font-family: 'Montserrat', sans-serif;
  transition: background 0.15s;
}
.btn-limpiar:hover { background: var(--fondo); }
.reset-icon { width: 15px; height: 15px; stroke: var(--gris); }

/* Chips de filtros activos */
.filtros-activos {
  display: flex; flex-wrap: wrap; align-items: center;
  gap: 6px; margin-top: 0.9rem;
  padding-top: 0.9rem;
  border-top: 1px solid var(--borde);
}
.filtros-activos-label { font-size: 0.78rem; font-weight: 600; color: var(--gris); }
.filtro-chip {
  display: inline-block;
  background: var(--azul-suave);
  color: var(--azul);
  font-size: 0.78rem;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 20px;
  font-family: 'Montserrat', sans-serif;
}

/* ══ Tabla ══ */
.table-container {
  background: #FFFFFF;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border: 1px solid var(--borde);
}
.bitacora-table { width: 100%; border-collapse: collapse; outline: none; }
.bitacora-table th {
  background: var(--fondo);
  padding: 11px 14px;
  text-align: left;
  font-weight: 600;
  font-size: 0.83rem;
  color: var(--texto);
  border-bottom: 1px solid var(--borde);
  font-family: 'Montserrat', sans-serif;
  white-space: nowrap;
}
.bitacora-table td {
  padding: 10px 14px;
  border-bottom: 1px solid var(--borde);
  color: var(--texto);
  font-size: 0.88rem;
  font-family: 'Montserrat', sans-serif;
  vertical-align: middle;
}
.bitacora-table tbody tr { transition: background 0.15s; cursor: pointer; }
.bitacora-table tbody tr:hover { background: #F8FAFC; }
.bitacora-table tbody tr:last-child td { border-bottom: none; }
.fila-seleccionada { background: var(--azul-suave) !important; }

/* Encabezado con orden de fecha */
.th-ordenable {
  display: flex; align-items: center; gap: 5px;
  cursor: pointer; user-select: none;
  transition: color 0.15s;
}
.th-ordenable:hover { color: var(--azul); }
.orden-icono { width: 14px; height: 14px; stroke: var(--gris); }

/* Celda de fecha ══ */
.celda-fecha { white-space: nowrap; }
.fecha-principal { font-weight: 600; font-size: 0.87rem; color: var(--texto); }
.fecha-hora      { font-size: 0.78rem; color: var(--gris); margin-top: 1px; }

/* Celda de usuario ══ */
.celda-usuario { display: flex; align-items: center; gap: 8px; }
.avatar-mini {
  width: 28px; height: 28px;
  border-radius: 50%;
  background: var(--azul);
  color: white;
  font-size: 0.78rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  font-family: 'Montserrat', sans-serif;
}
.usuario-texto { font-weight: 500; font-size: 0.88rem; }

/* Celda módulo y descripción ══ */
.celda-modulo { font-weight: 500; color: var(--gris); white-space: nowrap; }
.celda-descripcion {
  max-width: 340px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: var(--gris);
  font-size: 0.85rem;
}

/* ══ Badges de acción ══ */
.accion-badge {
  display: inline-block;
  padding: 4px 11px;
  border-radius: 20px;
  font-size: 0.78rem;
  font-weight: 700;
  font-family: 'Montserrat', sans-serif;
  white-space: nowrap;
  letter-spacing: 0.02em;
}
.accion-badge-grande { font-size: 0.85rem; padding: 5px 14px; }

.accion-login      { background: #DBEAFE; color: #1B396A; }
.accion-creacion   { background: #DCFCE7; color: #16A34A; }
.accion-edicion    { background: #FEF3C7; color: #F59E0B; }
.accion-eliminacion { background: #FEF2F2; color: #DC2626; }
.accion-default    { background: #F5F5F5; color: #6B7280; }

/* ══ Estados vacío y cargando ══ */
.estado-vacio, .estado-cargando { text-align: center; padding: 3.5rem 2rem; color: var(--gris); }
.icono-vacio { width: 56px; height: 56px; stroke: #9CA3AF; margin-bottom: 12px; }
.estado-vacio h3 { font-size: 1.2rem; color: var(--texto); margin: 0 0 6px; }
.estado-vacio p  { font-size: 0.93rem; margin: 0 0 1.2rem; }
.btn-limpiar-vacio { background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); padding: 9px 20px; border-radius: 8px; font-weight: 500; cursor: pointer; font-family: 'Montserrat', sans-serif; }
.spinner-tabla { display: inline-block; width: 36px; height: 36px; border: 3px solid var(--borde); border-top-color: var(--azul); border-radius: 50%; animation: girar 0.8s linear infinite; margin-bottom: 12px; }

/* ══ Paginación ══ */
.paginacion { margin-top: 1.2rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; color: var(--gris); font-family: 'Montserrat', sans-serif; flex-wrap: wrap; gap: 0.5rem; }
.paginacion-izquierda, .paginacion-centro, .paginacion-derecha { display: flex; align-items: center; gap: 8px; }
.select-filas { border: 1px solid var(--borde); border-radius: 6px; padding: 4px 8px; font-size: 0.9rem; background: #FFFFFF; font-family: 'Montserrat', sans-serif; }
.btn-pag { padding: 5px 11px; border: 1px solid var(--borde); background: #FFFFFF; border-radius: 6px; cursor: pointer; color: var(--texto); font-family: 'Montserrat', sans-serif; font-size: 0.9rem; transition: background 0.15s; }
.btn-pag:hover:not(:disabled) { background: var(--fondo); }
.btn-pag:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-pag.activo { background: var(--azul); color: white; border-color: var(--azul); }

/* ══ Modal de detalle ══ */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.modal-content { background: #FFFFFF; width: 540px; max-width: 92%; border-radius: 14px; box-shadow: 0 20px 50px rgba(0,0,0,0.18); overflow: hidden; border: 1px solid var(--borde); }

.modal-header { color: white; padding: 1.1rem 1.6rem; display: flex; justify-content: space-between; align-items: center; }
.modal-header h3 { margin: 0; font-size: 1.1rem; font-weight: 700; font-family: 'Montserrat', sans-serif; }
.modal-header-info { display: flex; flex-direction: column; gap: 5px; }

/* Colores del header según acción */
.header-azul     { background: #1B396A; }
.header-verde    { background: #16A34A; }
.header-amarillo { background: #D97706; }
.header-rojo     { background: #DC2626; }

.btn-cerrar-modal { background: none; border: none; color: white; font-size: 1.7rem; cursor: pointer; line-height: 1; opacity: 0.85; transition: opacity 0.15s; }
.btn-cerrar-modal:hover { opacity: 1; }

.modal-body { padding: 1.6rem; }
.detalle-fila { display: flex; justify-content: space-between; align-items: flex-start; padding: 10px 0; border-bottom: 1px solid var(--borde); font-size: 0.93rem; color: var(--texto); font-family: 'Montserrat', sans-serif; gap: 1.2rem; }
.detalle-fila:last-child { border-bottom: none; }
.detalle-descripcion { align-items: flex-start; }
.detalle-label { font-weight: 600; color: var(--gris); white-space: nowrap; flex-shrink: 0; }
.detalle-valor { font-weight: 500; text-align: right; }
.detalle-desc-texto { text-align: right; line-height: 1.5; }
.detalle-codigo { background: var(--fondo); color: var(--azul); font-family: 'Courier New', monospace; font-size: 0.85rem; padding: 2px 8px; border-radius: 5px; border: 1px solid var(--borde); }

.modal-footer { padding: 1rem 1.6rem; background: var(--fondo); display: flex; justify-content: flex-end; border-top: 1px solid var(--borde); }
.btn-secundario { padding: 10px 22px; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); transition: background 0.15s; }
.btn-secundario:hover { background: var(--fondo); }

@keyframes girar { to { transform: rotate(360deg); } }
</style>