<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="permisos-page">

      <!-- ══ Encabezado de página ══ -->
      <div class="page-header">
        <h1 class="page-title">Permisos del Sistema</h1>
        <span class="page-subtitle">{{ permisosFiltrados.length }} registro(s) encontrado(s)</span>
      </div>

      <!-- ══ Barra de carga global ══ -->
      <div class="barra-carga-global" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- ══ Notificación UI (solo errores, ya que es vista de consulta) ══ -->
      <transition name="notif-fade">
        <div v-if="notificacion.visible" class="notificacion-ui" :class="notificacion.tipo">
          <svg xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- ══ Barra de búsqueda (sin filtros adicionales, solo consulta) ══ -->
      <div class="filters-bar">
        <div class="search-group">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Buscar por nombre de permiso o módulo..."
            v-model="busqueda"
            class="search-input"
            @keydown.escape="busqueda = ''"
          >
          <span v-if="cargandoBusqueda" class="spinner-busqueda"></span>
        </div>

        <!-- Nota: esta vista es de solo consulta, no tiene botón de nuevo permiso.
             Los permisos son gestionados directamente por el equipo de backend. -->
      </div>

      <!-- ══ Tabla de permisos ══ -->
      <div class="table-container">

        <!-- Estado: cargando inicial -->
        <div v-if="cargando && permisos.length === 0" class="estado-cargando">
          <div class="spinner-tabla"></div>
          <p>Cargando registros...</p>
        </div>

        <table
          v-else-if="paginatedPermisos.length > 0"
          class="alumnos-table"
          ref="tablaRef"
          @keydown="navegarTeclado"
          tabindex="0"
        >
          <thead>
            <tr>
              <th>Nombre del Permiso</th>
              <th>Descripción</th>
              <th>Módulo</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(permiso, index) in paginatedPermisos"
              :key="permiso.id_permiso || permiso.id"
              :class="{ 'fila-seleccionada': filaActiva === index }"
              @click="filaActiva = index"
            >
              <td class="celda-nombre">{{ permiso.nombre }}</td>
              <td class="celda-descripcion">{{ permiso.descripcion || '—' }}</td>
              <td>
                <!-- Badge del módulo al que pertenece el permiso -->
                <span class="modulo-badge" :class="claseModulo(permiso.modulo)">
                  {{ permiso.modulo || '—' }}
                </span>
              </td>
              <td class="celda-acciones">
                <!-- Solo acción Ver, sin Editar ni Eliminar -->
                <button
                  class="btn-accion ver"
                  @click.stop="abrirModalVer(permiso)"
                  title="Ver detalles del permiso"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Ver
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
          <h3>Sin resultados</h3>
          <p>No se encontraron permisos con los criterios aplicados.</p>
          <button class="btn-limpiar-vacio" @click="busqueda = ''">Limpiar búsqueda</button>
        </div>
      </div>

      <!-- ══ Paginación ══ -->
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

    <!-- ══════════════════════════════════════
         MODAL: VER DETALLE DEL PERMISO
         Solo lectura — sin campos editables
    ═══════════════════════════════════════ -->
    <div v-if="showModalVer" class="modal-overlay" @click.self="cerrarModalVer">
      <div class="modal-content modal-ver">
        <div class="modal-header">
          <h3>Detalle del Permiso</h3>
          <button @click="cerrarModalVer" class="btn-cerrar-modal">×</button>
        </div>

        <div class="modal-body">
          <div class="detalle-fila">
            <span class="detalle-label">Nombre del Permiso</span>
            <span class="detalle-valor">{{ permisoVer.nombre }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Descripción</span>
            <span class="detalle-valor">{{ permisoVer.descripcion || '—' }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Módulo</span>
            <span class="modulo-badge" :class="claseModulo(permisoVer.modulo)">
              {{ permisoVer.modulo || '—' }}
            </span>
          </div>
          <!-- Campo clave técnica: útil para que el backend la muestre cuando conecte -->
          <div class="detalle-fila" v-if="permisoVer.clave">
            <span class="detalle-label">Clave técnica</span>
            <code class="detalle-clave">{{ permisoVer.clave }}</code>
          </div>
        </div>

        <!-- Nota informativa de solo lectura -->
        <div class="modal-aviso">
          <svg xmlns="http://www.w3.org/2000/svg" class="aviso-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Los permisos son administrados exclusivamente por el equipo de desarrollo.
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
import MainLayout from '@/layouts/MainLayout.vue'

// ── URL base del backend (variable de entorno) ──────────────────────
const API_URL = import.meta.env.VITE_API_URL

// ── Estado principal ────────────────────────────────────────────────
const permisos         = ref([])
const cargando         = ref(false)
const cargandoBusqueda = ref(false)
const filaActiva       = ref(-1)
const tablaRef         = ref(null)

// ── Búsqueda y paginación ───────────────────────────────────────────
const busqueda       = ref('')
const filasPorPagina = ref(10)
const currentPage    = ref(1)

// ── Notificación UI ────────────────────────────────────────────────
const notificacion = ref({ visible: false, mensaje: '', tipo: 'error' })
let timerNotif = null

const mostrarNotificacion = (mensaje, tipo = 'error') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

// ── Modal de solo lectura ───────────────────────────────────────────
const showModalVer = ref(false)
const permisoVer   = ref({})

const abrirModalVer = (permiso) => {
  permisoVer.value = { ...permiso }
  showModalVer.value = true
}
const cerrarModalVer = () => { showModalVer.value = false }

// ── Carga de permisos desde backend ────────────────────────────────
// Endpoint: GET /api/permisos
// Estructura esperada: { id_permiso, nombre, descripcion, modulo, clave }
const cargarPermisos = async () => {
  cargando.value = true
  try {
    const response = await fetch(`${API_URL}/api/permisos`)
    if (!response.ok) throw new Error('Error del servidor')
    const data = await response.json()
    permisos.value = data
    console.log('✅ Permisos cargados:', data.length, 'registros')
  } catch (error) {
    console.error('❌ Error cargando permisos:', error)
    mostrarNotificacion('No se pudo cargar la lista de permisos. Verifica que el servidor esté activo.')
  } finally {
    cargando.value = false
  }
}

onMounted(() => { cargarPermisos() })

// Indicador de búsqueda activa al escribir
let timerBusqueda = null
watch(busqueda, () => {
  cargandoBusqueda.value = true
  if (timerBusqueda) clearTimeout(timerBusqueda)
  timerBusqueda = setTimeout(() => {
    cargandoBusqueda.value = false
    currentPage.value = 1
  }, 350)
})

// ── Helpers ─────────────────────────────────────────────────────────
const normalize = (text) => {
  if (!text) return ''
  return text.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
}

const claseModulo = (modulo) => {
  if (!modulo) return ''
  const m = normalize(modulo)
  if (m.includes('alumn'))        return 'modulo-alumnos'
  if (m.includes('evaluac'))      return 'modulo-evaluaciones'
  if (m.includes('calific'))      return 'modulo-calificaciones'
  if (m.includes('inscrip') || m.includes('grup')) return 'modulo-inscripcion'
  if (m.includes('segur') || m.includes('usuar') || m.includes('rol')) return 'modulo-seguridad'
  return 'modulo-default'
}

// ── Filtrado ────────────────────────────────────────────────────────
const permisosFiltrados = computed(() => {
  if (!busqueda.value) return permisos.value
  return permisos.value.filter(p =>
    normalize(p.nombre).includes(normalize(busqueda.value)) ||
    normalize(p.descripcion).includes(normalize(busqueda.value)) ||
    normalize(p.modulo).includes(normalize(busqueda.value)) ||
    normalize(p.clave).includes(normalize(busqueda.value))
  )
})

// ── Paginación ───────────────────────────────────────────────────────
const totalPages = computed(() =>
  Math.ceil(permisosFiltrados.value.length / filasPorPagina.value) || 1
)
const paginatedPermisos = computed(() => {
  const start = (currentPage.value - 1) * filasPorPagina.value
  return permisosFiltrados.value.slice(start, start + filasPorPagina.value)
})
const visiblePages = computed(() => {
  const total = totalPages.value, current = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = new Set([1, total, current, current - 1, current + 1].filter(p => p >= 1 && p <= total))
  return [...pages].sort((a, b) => a - b)
})

const goToPage = (page) => { currentPage.value = page; filaActiva.value = -1 }
const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

// ── Navegación por teclado ───────────────────────────────────────────
const navegarTeclado = (e) => {
  const total = paginatedPermisos.value.length
  if (total === 0) return
  if (e.key === 'ArrowDown') { e.preventDefault(); filaActiva.value = Math.min(filaActiva.value + 1, total - 1) }
  else if (e.key === 'ArrowUp') { e.preventDefault(); filaActiva.value = Math.max(filaActiva.value - 1, 0) }
  else if (e.key === 'Enter' && filaActiva.value >= 0) { e.preventDefault(); abrirModalVer(paginatedPermisos.value[filaActiva.value]) }
  else if (e.key === 'PageDown') { e.preventDefault(); nextPage() }
  else if (e.key === 'PageUp')   { e.preventDefault(); prevPage() }
}
</script>


<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* ══ Variables locales ══ */
.permisos-page {
  --azul:        #1B396A;
  --azul-hover:  #1D4ED8;
  --borde:       #E5E7EB;
  --fondo:       #F5F5F5;
  --texto:       #1A1A1A;
  --gris:        #6B7280;

  max-width: 100%;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
}

/* ══ Encabezado ══ */
.page-header {
  display: flex;
  align-items: baseline;
  gap: 1rem;
  margin-bottom: 1.2rem;
}
.page-title {
  color: var(--texto);
  font-size: 1.75rem;
  font-weight: 700;
  letter-spacing: -0.02em;
  margin: 0;
}
.page-subtitle {
  font-size: 0.9rem;
  color: var(--gris);
  font-weight: 500;
}

/* ══ Barra de carga global ══ */
.barra-carga-global {
  height: 3px;
  background: transparent;
  border-radius: 2px;
  margin-bottom: 1rem;
  overflow: hidden;
  opacity: 0;
  transition: opacity 0.3s;
}
.barra-carga-global.visible { opacity: 1; }
.barra-progreso {
  height: 100%;
  width: 40%;
  background: #1B396A;
  border-radius: 2px;
  animation: deslizar 1.2s ease-in-out infinite;
}
@keyframes deslizar {
  0%   { transform: translateX(-100%); }
  100% { transform: translateX(350%); }
}

/* ══ Notificación UI ══ */
.notificacion-ui {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 18px;
  border-radius: 10px;
  font-size: 0.93rem;
  font-weight: 500;
  margin-bottom: 1rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  background: #FEE2E2;
  color: #DC2626;
  border: 1px solid #FCA5A5;
}
.notif-icono { width: 20px; height: 20px; flex-shrink: 0; }
.notif-fade-enter-active, .notif-fade-leave-active { transition: all 0.35s ease; }
.notif-fade-enter-from, .notif-fade-leave-to { opacity: 0; transform: translateY(-8px); }

/* ══ Barra de búsqueda ══ */
.filters-bar {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.2rem;
}
.search-group {
  position: relative;
  flex: 0 0 380px;
  min-width: 220px;
}
.search-input {
  width: 100%;
  padding: 10px 14px 10px 42px;
  border: 1px solid var(--borde);
  border-radius: 8px;
  font-size: 0.93rem;
  background: #FFFFFF;
  color: var(--texto);
  font-family: 'Montserrat', sans-serif;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box;
}
.search-input:focus { border-color: #1B396A; box-shadow: 0 0 0 3px #DBEAFE; }
.search-input::placeholder { color: #9CA3AF; }
.search-icon-svg {
  position: absolute; left: 13px; top: 50%;
  transform: translateY(-50%);
  width: 18px; height: 18px;
  stroke: var(--gris); pointer-events: none;
}
.spinner-busqueda {
  position: absolute; right: 12px; top: 50%;
  transform: translateY(-50%);
  width: 16px; height: 16px;
  border: 2px solid #E5E7EB;
  border-top-color: #1B396A;
  border-radius: 50%;
  animation: girar 0.7s linear infinite;
}

/* ══ Tabla ══ */
.table-container {
  background: #FFFFFF;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border: 1px solid var(--borde);
}
.alumnos-table { width: 100%; border-collapse: collapse; outline: none; }
.alumnos-table th {
  background: var(--fondo);
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  font-size: 0.88rem;
  color: var(--texto);
  border-bottom: 1px solid var(--borde);
  font-family: 'Montserrat', sans-serif;
  white-space: nowrap;
}
.alumnos-table td {
  padding: 11px 16px;
  border-bottom: 1px solid var(--borde);
  color: var(--texto);
  font-size: 0.93rem;
  font-family: 'Montserrat', sans-serif;
}
.alumnos-table tbody tr { transition: background 0.15s; cursor: pointer; }
.alumnos-table tbody tr:hover { background: #F8FAFC; }
.alumnos-table tbody tr:last-child td { border-bottom: none; }
.fila-seleccionada { background: #DBEAFE !important; }

.celda-nombre      { font-weight: 600; color: #1B396A; font-size: 0.92rem; }
.celda-descripcion { color: var(--gris); font-size: 0.88rem; max-width: 340px; }
.celda-acciones    { display: flex; gap: 6px; align-items: center; }

/* ══ Badge del módulo ══ */
.modulo-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.82rem;
  font-weight: 600;
  font-family: 'Montserrat', sans-serif;
  white-space: nowrap;
}
.modulo-alumnos       { background: #DBEAFE; color: #1B396A; }
.modulo-evaluaciones  { background: #DCFCE7; color: #16A34A; }
.modulo-calificaciones { background: #FEF3C7; color: #F59E0B; }
.modulo-inscripcion   { background: #F3E8FF; color: #7C3AED; }
.modulo-seguridad     { background: #FEE2E2; color: #DC2626; }
.modulo-default       { background: #F5F5F5; color: #6B7280; }

/* ══ Botón de acción Ver ══ */
.btn-accion {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 6px 13px;
  border-radius: 6px;
  font-size: 0.85rem;
  cursor: pointer;
  font-weight: 600;
  font-family: 'Montserrat', sans-serif;
  transition: background 0.15s;
  white-space: nowrap;
}
.btn-accion svg { width: 14px; height: 14px; }
.btn-accion.ver {
  background: #FFFFFF;
  color: var(--texto);
  border: 1px solid var(--borde);
}
.btn-accion.ver:hover { background: var(--fondo); }

/* ══ Estados vacío y cargando ══ */
.estado-vacio, .estado-cargando {
  text-align: center;
  padding: 3.5rem 2rem;
  color: var(--gris);
}
.icono-vacio { width: 56px; height: 56px; stroke: #9CA3AF; margin-bottom: 12px; }
.estado-vacio h3 { font-size: 1.2rem; color: var(--texto); margin: 0 0 6px; }
.estado-vacio p  { font-size: 0.93rem; margin: 0 0 1.2rem; }
.btn-limpiar-vacio {
  background: #FFFFFF; color: var(--texto);
  border: 1px solid var(--borde); padding: 9px 20px;
  border-radius: 8px; font-weight: 500; cursor: pointer;
  font-family: 'Montserrat', sans-serif;
}
.spinner-tabla {
  display: inline-block;
  width: 36px; height: 36px;
  border: 3px solid #E5E7EB;
  border-top-color: #1B396A;
  border-radius: 50%;
  animation: girar 0.8s linear infinite;
  margin-bottom: 12px;
}

/* ══ Paginación ══ */
.paginacion {
  margin-top: 1.2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
  color: var(--gris);
  font-family: 'Montserrat', sans-serif;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.paginacion-izquierda,
.paginacion-centro,
.paginacion-derecha { display: flex; align-items: center; gap: 8px; }
.select-filas {
  border: 1px solid var(--borde); border-radius: 6px;
  padding: 4px 8px; font-size: 0.9rem;
  background: #FFFFFF; font-family: 'Montserrat', sans-serif;
}
.btn-pag {
  padding: 5px 11px;
  border: 1px solid var(--borde);
  background: #FFFFFF; border-radius: 6px;
  cursor: pointer; color: var(--texto);
  font-family: 'Montserrat', sans-serif;
  font-size: 0.9rem; transition: background 0.15s;
}
.btn-pag:hover:not(:disabled) { background: var(--fondo); }
.btn-pag:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-pag.activo { background: #1B396A; color: white; border-color: #1B396A; }

/* ══ Modal de solo lectura ══ */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.55);
  display: flex; align-items: center; justify-content: center;
  z-index: 2000;
}
.modal-content {
  background: #FFFFFF;
  width: 520px; max-width: 92%;
  border-radius: 14px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.18);
  overflow: hidden;
  border: 1px solid var(--borde);
}
.modal-header {
  background: #1B396A;
  color: white;
  padding: 1.1rem 1.6rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.modal-header h3 {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 700;
  font-family: 'Montserrat', sans-serif;
}
.btn-cerrar-modal {
  background: none; border: none; color: white;
  font-size: 1.7rem; cursor: pointer; line-height: 1;
  opacity: 0.85; transition: opacity 0.15s;
}
.btn-cerrar-modal:hover { opacity: 1; }

.modal-body { padding: 1.6rem; }

.detalle-fila {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 11px 0;
  border-bottom: 1px solid var(--borde);
  font-size: 0.95rem;
  color: var(--texto);
  font-family: 'Montserrat', sans-serif;
}
.detalle-fila:last-child { border-bottom: none; }
.detalle-label { font-weight: 600; color: var(--gris); }
.detalle-valor { font-weight: 500; text-align: right; max-width: 60%; }

/* Clave técnica del permiso (ej: alumnos.ver) */
.detalle-clave {
  background: #F5F5F5;
  color: #1B396A;
  font-family: 'Courier New', Courier, monospace;
  font-size: 0.88rem;
  padding: 3px 10px;
  border-radius: 6px;
  border: 1px solid var(--borde);
  font-weight: 600;
}

/* Aviso de solo lectura en el modal */
.modal-aviso {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0 1.6rem 0;
  padding: 10px 14px;
  background: #DBEAFE;
  border-radius: 8px;
  font-size: 0.83rem;
  color: #1B396A;
  font-family: 'Montserrat', sans-serif;
  font-weight: 500;
}
.aviso-icono { width: 18px; height: 18px; stroke: #1B396A; flex-shrink: 0; }

.modal-footer {
  padding: 1rem 1.6rem;
  background: var(--fondo);
  display: flex;
  justify-content: flex-end;
  border-top: 1px solid var(--borde);
  margin-top: 1rem;
}
.btn-secundario {
  padding: 10px 22px; border-radius: 8px;
  font-weight: 600; cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  background: #FFFFFF; color: var(--texto);
  border: 1px solid var(--borde);
  transition: background 0.15s;
}
.btn-secundario:hover { background: var(--fondo); }

@keyframes girar { to { transform: rotate(360deg); } }
</style>
