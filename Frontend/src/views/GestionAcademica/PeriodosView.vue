<template>
  <MainLayout>
    <div class="periodos-page">
      <nav class="breadcrumb">
        <span class="breadcrumb-link" @click="$router.push('/gestion-academica')">Gestión Académica</span>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-actual">Periodos Académicos</span>
      </nav>
      <div class="page-header">
        <h1 class="page-title">Periodos Académicos</h1>
        <span class="page-subtitle">{{ periodosFiltrados.length }} registro(s) encontrado(s)</span>
      </div>
      <div class="barra-carga" :class="{ visible: cargando }"><div class="barra-progreso"></div></div>
      <transition name="toast-slide">
        <div v-if="notificacion.visible" class="toast" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo === 'exito'" xmlns="http://www.w3.org/2000/svg" class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- Barra de herramientas: buscador siempre visible + filtros colapsables -->
      <div class="filters-bar">
        <!-- Buscador principal siempre visible -->
        <div class="search-group">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Buscar periodo..."
            v-model="busqueda"
            class="search-input"
            @keydown.escape="busqueda = ''"
          >
          <button v-if="busqueda" class="search-clear" @click="busqueda = ''" title="Limpiar búsqueda">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- Botón Filtros solo para filtros adicionales (Estatus) -->
        <button
          class="btn-filtro"
          @click="panelFiltros = !panelFiltros"
          :class="{ activo: panelFiltros || filtroEstatus }"
          title="Filtros adicionales"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
          </svg>
          Filtros
          <span v-if="filtroEstatus" class="filtro-badge">1</span>
        </button>

        <button class="btn-nuevo" @click="abrirModalNuevo">+ Nuevo periodo</button>
      </div>

      <!-- Panel colapsable: solo filtros adicionales (Estatus) -->
      <transition name="panel-slide">
        <div v-if="panelFiltros" class="panel-filtros">
          <div class="panel-filtros-inner">
            <div class="filtro-grupo filtro-grupo-sm">
              <label class="filtro-label">Estatus</label>
              <select v-model="filtroEstatus" class="filter-select">
                <option value="">Todos</option>
                <option value="ACTIVO">Activo</option>
                <option value="PROGRAMADO">Programado</option>
                <option value="INACTIVO">Inactivo</option>
              </select>
            </div>
            <button class="btn-limpiar-filtros" @click="limpiarFiltros">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
              Limpiar filtros
            </button>
          </div>
        </div>
      </transition>

      <div class="table-container">
        <div v-if="cargando && periodos.length === 0" class="estado-cargando"><div class="spinner-tabla"></div><p>Cargando registros...</p></div>
        <table v-else-if="periodosFiltrados.length > 0" class="data-table">
          <thead><tr><th>Nombre del Periodo</th><th>Fecha de Inicio</th><th>Fecha de Fin</th><th>Estatus</th><th class="th-centro">Acciones</th></tr></thead>
          <tbody>
            <tr v-for="(periodo, index) in paginados" :key="periodo.id_periodo" :class="{ 'fila-seleccionada': filaActiva === index }" @click="filaActiva = index">
              <td class="celda-nombre">{{ periodo.nombre_periodo }}</td>
              <td>{{ formatearFecha(periodo.fecha_inicio) }}</td>
              <td>{{ formatearFecha(periodo.fecha_fin) }}</td>
              <td><span class="estatus-badge" :class="periodo.activo ? 'activo' : 'inactivo'">{{ etiquetaEstatus(periodo) }}</span></td>
              <td class="celda-acciones">
                <button class="btn-icono ver" @click.stop="abrirModalVer(periodo)" title="Ver detalle">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
                <button class="btn-icono editar" @click.stop="abrirModalEditar(periodo)" title="Editar">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <h3>Sin resultados</h3>
          <p>No se encontraron periodos con los criterios aplicados.</p>
          <button class="btn-limpiar-vacio" @click="limpiarFiltros">Limpiar filtros</button>
        </div>

        <!-- Paginación -->
        <div v-if="totalPaginas > 1" class="paginacion">
          <button class="btn-pag" @click="paginaActual--" :disabled="paginaActual === 1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <span class="pag-info">Página {{ paginaActual }} de {{ totalPaginas }}</span>
          <button class="btn-pag" @click="paginaActual++" :disabled="paginaActual === totalPaginas">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>
      </div>
      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México | Todos los derechos reservados</footer>
    </div>

    <!-- Modal Ver -->
    <div v-if="showModalVer" class="modal-overlay" @click.self="showModalVer = false">
      <div class="modal-content">
        <div class="modal-header"><h3>Datos del Periodo</h3><button @click="showModalVer = false" class="btn-cerrar-modal">×</button></div>
        <div class="modal-body">
          <div class="detalle-fila"><span class="detalle-label">Nombre</span><span class="detalle-valor">{{ periodoVer.nombre_periodo }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Fecha de inicio</span><span class="detalle-valor">{{ formatearFecha(periodoVer.fecha_inicio) }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Fecha de fin</span><span class="detalle-valor">{{ formatearFecha(periodoVer.fecha_fin) }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Estatus</span><span class="estatus-badge" :class="periodoVer.activo ? 'activo' : 'inactivo'">{{ etiquetaEstatus(periodoVer) }}</span></div>
        </div>
        <div class="modal-footer"><button class="btn-secundario" @click="showModalVer = false">Cerrar</button></div>
      </div>
    </div>

    <!-- Modal Crear/Editar -->
    <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-content">
        <div class="modal-header"><h3>{{ form.id_periodo ? 'Editar Periodo' : 'Nuevo Periodo Académico' }}</h3><button @click="cerrarModal" class="btn-cerrar-modal">×</button></div>
        <div class="modal-body">
          <div v-if="form.activo && periodoActivoExistente && !form.id_periodo" class="aviso-amarillo">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="aviso-icono"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <span>El periodo actualmente activo quedará inactivo al guardar este nuevo periodo.</span>
          </div>
          <div class="form-grupo">
            <label>Nombre del periodo <span class="obligatorio">*</span></label>
            <input v-model.trim="form.nombre_periodo" type="text" maxlength="50" class="modal-input" :class="{ 'borde-error': errors.nombre_periodo }" placeholder="Ej. Agosto - Diciembre 2026">
            <small v-if="errors.nombre_periodo" class="mensaje-error">{{ errors.nombre_periodo }}</small>
          </div>
          <div class="form-grupo-doble">
            <div class="form-grupo">
              <label>Fecha de inicio <span class="obligatorio">*</span></label>
              <input v-model="form.fecha_inicio" type="date" class="modal-input" :class="{ 'borde-error': errors.fecha_inicio }">
              <small v-if="errors.fecha_inicio" class="mensaje-error">{{ errors.fecha_inicio }}</small>
            </div>
            <div class="form-grupo">
              <label>Fecha de fin <span class="obligatorio">*</span></label>
              <input v-model="form.fecha_fin" type="date" class="modal-input" :class="{ 'borde-error': errors.fecha_fin }">
              <small v-if="errors.fecha_fin" class="mensaje-error">{{ errors.fecha_fin }}</small>
            </div>
          </div>
          <div class="form-grupo">
            <label>Estatus</label>
            <select v-model="form.estatus" class="modal-select">
              <option value="ACTIVO">Activo</option>
              <option value="PROGRAMADO">Programado</option>
              <option value="INACTIVO">Inactivo</option>
            </select>
            <div class="indicador-estatus" :class="form.activo ? 'activo' : 'inactivo'">{{ etiquetaEstatus(form) }}</div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">Cancelar</button>
          <button v-if="form.id_periodo" class="btn-eliminar" @click="solicitarEliminar" :disabled="guardando">Eliminar</button>
          <button class="btn-guardar" @click="guardar" :disabled="guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            {{ guardando ? 'Guardando...' : 'Guardar' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Confirmar Eliminar -->
    <div v-if="showModalEliminar" class="modal-overlay" @click.self="showModalEliminar = false">
      <div class="modal-content modal-confirmar">
        <div class="modal-header"><h3>Confirmar eliminación</h3><button @click="showModalEliminar = false" class="btn-cerrar-modal">×</button></div>
        <div class="modal-body confirmar-body">
          <svg xmlns="http://www.w3.org/2000/svg" class="confirmar-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
          <p>¿Deseas eliminar el periodo <strong>{{ periodoAEliminar?.nombre_periodo }}</strong>? Esta acción no se puede deshacer.</p>
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
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, reactive, watch } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`

const periodos          = ref([])
const cargando          = ref(false)
const guardando         = ref(false)
const filaActiva        = ref(-1)
const busqueda          = ref('')
const filtroEstatus     = ref('')
const panelFiltros      = ref(false)  // colapsado por defecto
const showModal         = ref(false)
const showModalVer      = ref(false)
const showModalEliminar = ref(false)
const periodoVer        = ref({})
const periodoAEliminar  = ref(null)
const paginaActual      = ref(1)
const porPagina         = 10

const form   = reactive({ id_periodo: null, nombre_periodo: '', fecha_inicio: '', fecha_fin: '', estatus: 'PROGRAMADO', activo: false })
const errors = reactive({})
const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

const periodoActivoExistente = computed(() => periodos.value.some(p => p.activo && p.id_periodo !== form.id_periodo))

const cargarPeriodos = async () => {
  cargando.value = true
  try {
    const res = await fetch(`${API}/periodos`)
    if (!res.ok) throw new Error()
    const json = await res.json()
    periodos.value = Array.isArray(json) ? json : (json.data ?? [])
  } catch { mostrarNotificacion('No se pudieron cargar los periodos.', 'error') }
  finally { cargando.value = false }
}

onMounted(() => { cargarPeriodos() })

const normalize = (t) => t?.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '') ?? ''
const etiquetaEstatus = (periodo) => {
  if (periodo?.estatus === 'ACTIVO') return 'Activo'
  if (periodo?.estatus === 'PROGRAMADO') return 'Programado'
  return 'Inactivo'
}

const periodosFiltrados = computed(() =>
  periodos.value.filter(p => {
    const coincideBusqueda = !busqueda.value || normalize(p.nombre_periodo).includes(normalize(busqueda.value))
    const coincideEstatus  = !filtroEstatus.value || p.estatus === filtroEstatus.value
    return coincideBusqueda && coincideEstatus
  })
)

const totalPaginas = computed(() => Math.ceil(periodosFiltrados.value.length / porPagina))
const paginados    = computed(() => {
  const inicio = (paginaActual.value - 1) * porPagina
  return periodosFiltrados.value.slice(inicio, inicio + porPagina)
})

watch([busqueda, filtroEstatus], () => { paginaActual.value = 1 })
watch(() => form.estatus, (estatus) => { form.activo = estatus === 'ACTIVO' })

const formatearFecha = (fecha) => {
  if (!fecha) return '—'
  return new Date(fecha + 'T00:00:00').toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' })
}

const limpiarFiltros  = () => { busqueda.value = ''; filtroEstatus.value = ''; filaActiva.value = -1; paginaActual.value = 1 }
const resetForm       = () => { form.id_periodo = null; form.nombre_periodo = ''; form.fecha_inicio = ''; form.fecha_fin = ''; form.estatus = 'PROGRAMADO'; form.activo = false; Object.keys(errors).forEach(k => delete errors[k]) }
const abrirModalNuevo  = () => { resetForm(); showModal.value = true }
const abrirModalVer    = (p) => { periodoVer.value = p; showModalVer.value = true }
const abrirModalEditar = (p) => { resetForm(); form.id_periodo = p.id_periodo; form.nombre_periodo = p.nombre_periodo; form.fecha_inicio = p.fecha_inicio; form.fecha_fin = p.fecha_fin; form.estatus = p.estatus; form.activo = !!p.activo; showModal.value = true }
const cerrarModal      = () => { showModal.value = false; resetForm() }
const solicitarEliminar = () => { periodoAEliminar.value = { id_periodo: form.id_periodo, nombre_periodo: form.nombre_periodo }; showModal.value = false; showModalEliminar.value = true }

const validar = () => {
  Object.keys(errors).forEach(k => delete errors[k])
  if (!form.nombre_periodo.trim()) errors.nombre_periodo = 'El nombre es obligatorio'
  if (!form.fecha_inicio)          errors.fecha_inicio   = 'La fecha de inicio es obligatoria'
  if (!form.fecha_fin)             errors.fecha_fin      = 'La fecha de fin es obligatoria'
  if (form.fecha_inicio && form.fecha_fin && form.fecha_fin <= form.fecha_inicio) errors.fecha_fin = 'La fecha de fin debe ser posterior a la de inicio'
  return Object.keys(errors).length === 0
}

const guardar = async () => {
  if (!validar()) return
  guardando.value = true
  const esEdicion = !!form.id_periodo
  const url = esEdicion ? `${API}/periodos/${form.id_periodo}` : `${API}/periodos`
  const payload = { nombre_periodo: form.nombre_periodo.trim(), fecha_inicio: form.fecha_inicio, fecha_fin: form.fecha_fin, estatus: form.estatus, activo: form.activo }
  try {
    const res = await fetch(url, { method: esEdicion ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(payload) })
    if (!res.ok) throw new Error()
    await cargarPeriodos(); cerrarModal()
    mostrarNotificacion(esEdicion ? 'Periodo actualizado correctamente.' : 'Periodo creado correctamente.')
  } catch { mostrarNotificacion('Ocurrió un error al guardar.', 'error') }
  finally { guardando.value = false }
}

const confirmarEliminar = async () => {
  if (!periodoAEliminar.value) return
  guardando.value = true
  try {
    const res = await fetch(`${API}/periodos/${periodoAEliminar.value.id_periodo}`, { method: 'DELETE', headers: { 'Accept': 'application/json' } })
    if (!res.ok) throw new Error()
    await cargarPeriodos(); showModalEliminar.value = false; periodoAEliminar.value = null
    mostrarNotificacion('Periodo eliminado correctamente.')
  } catch { mostrarNotificacion('Ocurrió un error al eliminar.', 'error') }
  finally { guardando.value = false }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.periodos-page{--azul:#1B396A;--azul-hover:#1D4ED8;--azul-suave:#DBEAFE;--borde:#E5E7EB;--fondo:#F5F5F5;--texto:#1A1A1A;--gris:#6B7280;--verde:#16A34A;--rojo:#DC2626;width:100%;background:var(--fondo);font-family:'Montserrat',sans-serif;padding-bottom:2rem}
.breadcrumb{display:flex;align-items:center;gap:6px;color:var(--gris);font-size:0.88rem;margin-bottom:0.75rem}.breadcrumb-link{color:var(--azul);font-weight:500;cursor:pointer;transition:color 0.15s}.breadcrumb-link:hover{color:var(--azul-hover);text-decoration:underline}.breadcrumb-sep{color:#9CA3AF}.breadcrumb-actual{color:var(--gris);font-weight:600}
.page-header{display:flex;flex-direction:column;gap:4px;margin-bottom:1.2rem}.page-title{color:var(--texto);font-size:1.75rem;font-weight:700;letter-spacing:-0.02em;margin:0}.page-subtitle{font-size:0.9rem;color:var(--gris);font-weight:500}
.barra-carga{height:3px;background:transparent;border-radius:2px;margin-bottom:1rem;overflow:hidden;opacity:0;transition:opacity 0.3s}.barra-carga.visible{opacity:1}.barra-progreso{height:100%;width:40%;background:var(--azul);border-radius:2px;animation:deslizar 1.2s ease-in-out infinite}
@keyframes deslizar{0%{transform:translateX(-100%)}100%{transform:translateX(350%)}}
.toast{position:fixed;bottom:2rem;right:2rem;display:flex;align-items:center;gap:10px;padding:12px 20px;border-radius:10px;color:white;font-weight:500;font-size:0.93rem;box-shadow:0 6px 20px rgba(0,0,0,0.18);z-index:3000;max-width:380px}.toast.exito{background:var(--azul)}.toast.error{background:var(--rojo)}.toast-icono{width:20px;height:20px;flex-shrink:0}
.toast-slide-enter-active,.toast-slide-leave-active{transition:all 0.35s ease}.toast-slide-enter-from,.toast-slide-leave-to{opacity:0;transform:translateX(110%)}

/* ── Barra de herramientas ─────────────────────────────────────── */
.filters-bar{display:flex;align-items:center;gap:0.75rem;margin-bottom:0;flex-wrap:wrap}

/* Buscador siempre visible */
.search-group{position:relative;flex:1;min-width:200px;max-width:360px}
.search-input{width:100%;padding:9px 36px 9px 38px;border:1.5px solid var(--borde);border-radius:8px;font-size:0.9rem;background:#FFFFFF;color:var(--texto);font-family:'Montserrat',sans-serif;outline:none;transition:border-color 0.2s,box-shadow 0.2s;box-sizing:border-box}
.search-input:focus{border-color:var(--azul);box-shadow:0 0 0 3px #DBEAFE}
.search-input::placeholder{color:#9CA3AF}
.search-icon-svg{position:absolute;left:11px;top:50%;transform:translateY(-50%);width:16px;height:16px;stroke:var(--gris);pointer-events:none}
.search-clear{position:absolute;right:9px;top:50%;transform:translateY(-50%);display:flex;align-items:center;justify-content:center;width:20px;height:20px;background:none;border:none;cursor:pointer;padding:0;color:#9CA3AF;transition:color 0.15s}
.search-clear:hover{color:var(--texto)}
.search-clear svg{width:14px;height:14px;stroke:currentColor}

/* Botón Filtros para filtros adicionales */
.btn-filtro{display:flex;align-items:center;gap:7px;padding:9px 16px;border-radius:8px;border:1.5px solid var(--borde);background:#FFFFFF;color:var(--texto);font-weight:600;font-size:0.88rem;font-family:'Montserrat',sans-serif;cursor:pointer;transition:all 0.2s;position:relative;white-space:nowrap}
.btn-filtro svg{width:16px;height:16px;stroke:var(--gris)}
.btn-filtro:hover,.btn-filtro.activo{border-color:var(--azul);color:var(--azul);background:var(--azul-suave)}
.btn-filtro.activo svg{stroke:var(--azul)}
.filtro-badge{display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:var(--azul);color:white;font-size:0.7rem;font-weight:700}
.btn-nuevo{background:var(--azul);color:white;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;white-space:nowrap;font-family:'Montserrat',sans-serif;font-size:0.92rem;transition:background 0.2s;margin-left:auto}.btn-nuevo:hover{background:var(--azul-hover)}

/* Panel de filtros adicionales colapsable */
.panel-filtros{background:#FFFFFF;border:1.5px solid var(--borde);border-radius:10px;margin:0.6rem 0 1rem;overflow:hidden}
.panel-filtros-inner{display:flex;align-items:flex-end;gap:1rem;padding:1rem 1.2rem;flex-wrap:wrap}
.filtro-grupo{display:flex;flex-direction:column;gap:5px;flex:0 0 280px;min-width:180px}
.filtro-grupo-sm{flex:0 0 160px;min-width:130px}
.filtro-label{font-size:0.82rem;font-weight:600;color:var(--gris)}
.filter-select{width:100%;padding:9px 12px;border:1.5px solid var(--borde);border-radius:8px;font-size:0.9rem;background:#FFFFFF;color:var(--texto);font-family:'Montserrat',sans-serif;cursor:pointer;outline:none}.filter-select:focus{border-color:var(--azul)}
.btn-limpiar-filtros{display:flex;align-items:center;gap:6px;padding:9px 14px;border-radius:8px;border:1px solid var(--borde);background:#FFFFFF;color:var(--gris);font-size:0.88rem;font-weight:600;font-family:'Montserrat',sans-serif;cursor:pointer;white-space:nowrap;transition:all 0.15s;align-self:flex-end}
.btn-limpiar-filtros svg{width:14px;height:14px}.btn-limpiar-filtros:hover{background:#FEF2F2;color:var(--rojo);border-color:#FECACA}

/* Transición panel */
.panel-slide-enter-active,.panel-slide-leave-active{transition:all 0.25s ease}
.panel-slide-enter-from,.panel-slide-leave-to{opacity:0;transform:translateY(-8px)}

/* Tabla */
.table-container{background:#FFFFFF;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);border:1px solid var(--borde);margin-top:1.2rem}
.data-table{width:100%;border-collapse:collapse}.data-table th{background:var(--fondo);padding:12px 16px;text-align:left;font-weight:600;font-size:0.88rem;color:var(--texto);border-bottom:1px solid var(--borde);font-family:'Montserrat',sans-serif}.th-centro{text-align:center}.data-table td{padding:11px 16px;border-bottom:1px solid var(--borde);color:var(--texto);font-size:0.93rem;font-family:'Montserrat',sans-serif}.data-table tbody tr{transition:background 0.15s;cursor:pointer}.data-table tbody tr:hover{background:#F8FAFC}.data-table tbody tr:last-child td{border-bottom:none}.fila-seleccionada{background:#DBEAFE!important}.celda-nombre{font-weight:600}
.estatus-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:0.83rem;font-weight:600}.estatus-badge.activo{background:#DCFCE7;color:#16A34A}.estatus-badge.inactivo{background:#F3F4F6;color:#6B7280}

/* Acciones */
.celda-acciones{text-align:center}
.btn-icono{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:7px;cursor:pointer;transition:all 0.15s;position:relative;margin:0 2px}
.btn-icono svg{width:15px;height:15px}
.btn-icono.ver{background:#F3F4F6;color:#1A1A1A;border:1px solid #D1D5DB}.btn-icono.ver:hover{background:#E5E7EB}
.btn-icono.editar{background:#1B396A;color:#FFFFFF;border:1px solid #1B396A}.btn-icono.editar:hover{background:#1D4ED8}
.btn-icono[title]:hover::after{content:attr(title);position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);background:#1A1A1A;color:white;font-size:0.72rem;font-weight:600;padding:3px 8px;border-radius:5px;white-space:nowrap;pointer-events:none;font-family:'Montserrat',sans-serif;z-index:10}
.btn-icono[title]:hover::before{content:'';position:absolute;bottom:calc(100% + 2px);left:50%;transform:translateX(-50%);border:4px solid transparent;border-top-color:#1A1A1A;pointer-events:none;z-index:10}

.estado-vacio,.estado-cargando{text-align:center;padding:3.5rem 2rem;color:var(--gris)}.icono-vacio{width:56px;height:56px;stroke:#9CA3AF;margin-bottom:12px}.estado-vacio h3{font-size:1.2rem;color:var(--texto);margin:0 0 6px}.estado-vacio p{font-size:0.93rem;margin:0 0 1.2rem}.btn-limpiar-vacio{background:#FFFFFF;color:var(--texto);border:1px solid var(--borde);padding:9px 20px;border-radius:8px;font-weight:500;cursor:pointer;font-family:'Montserrat',sans-serif}.spinner-tabla{display:inline-block;width:36px;height:36px;border:3px solid #E5E7EB;border-top-color:var(--azul);border-radius:50%;animation:girar 0.8s linear infinite;margin-bottom:12px}

/* Paginación */
.paginacion{display:flex;align-items:center;justify-content:center;gap:12px;padding:14px 16px;border-top:1px solid var(--borde);background:#FAFAFA}
.btn-pag{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;border:1.5px solid var(--borde);background:#FFFFFF;color:var(--texto);cursor:pointer;transition:all 0.15s}
.btn-pag svg{width:16px;height:16px;stroke:currentColor}
.btn-pag:hover:not(:disabled){border-color:var(--azul);color:var(--azul);background:var(--azul-suave)}
.btn-pag:disabled{opacity:0.35;cursor:not-allowed}
.pag-info{font-size:0.85rem;font-weight:600;color:var(--gris);font-family:'Montserrat',sans-serif}

/* Modales */
.aviso-amarillo{display:flex;align-items:flex-start;gap:10px;background:#FEF3C7;border:1px solid #FCD34D;border-radius:8px;padding:12px 14px;margin-bottom:1.2rem;font-size:0.88rem;color:#92400E;font-weight:500}.aviso-icono{width:18px;height:18px;stroke:#D97706;flex-shrink:0;margin-top:1px}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.55);display:flex;align-items:center;justify-content:center;z-index:2000}.modal-content{background:#FFFFFF;width:520px;max-width:92%;border-radius:14px;box-shadow:0 20px 50px rgba(0,0,0,0.18);overflow:hidden;border:1px solid #E5E7EB}.modal-confirmar{width:440px}.modal-header{background:#1B396A;color:white;padding:1.1rem 1.6rem;display:flex;justify-content:space-between;align-items:center}.modal-header h3{margin:0;font-size:1.2rem;font-weight:700;font-family:'Montserrat',sans-serif}.btn-cerrar-modal{background:none;border:none;color:white;font-size:1.7rem;cursor:pointer;line-height:1;opacity:0.85}.btn-cerrar-modal:hover{opacity:1}
.modal-body{padding:1.6rem}.form-grupo{margin-bottom:1.2rem}.form-grupo-doble{display:grid;grid-template-columns:1fr 1fr;gap:1rem}.form-grupo label{display:block;margin-bottom:6px;font-weight:600;font-size:0.9rem;color:#1A1A1A;font-family:'Montserrat',sans-serif}.obligatorio{color:#DC2626}.modal-input,.modal-select{width:100%;padding:10px 14px;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.95rem;background:#FFFFFF;color:#1A1A1A;font-family:'Montserrat',sans-serif;outline:none;transition:border-color 0.2s;box-sizing:border-box}.modal-input:focus,.modal-select:focus{border-color:#1B396A;box-shadow:0 0 0 3px #DBEAFE}.borde-error{border-color:#DC2626!important}.mensaje-error{display:block;color:#DC2626;font-size:0.82rem;margin-top:5px}
.indicador-estatus{display:inline-flex;align-items:center;margin-top:7px;padding:4px 12px;border-radius:20px;font-size:0.82rem;font-weight:600}.indicador-estatus.activo{background:#DCFCE7;color:#16A34A}.indicador-estatus.inactivo{background:#F3F4F6;color:#6B7280}
.modal-footer{padding:1rem 1.6rem;background:#F5F5F5;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid #E5E7EB}.btn-secundario{padding:10px 22px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;background:#FFFFFF;color:#1B396A;border:2px solid #1B396A;transition:background 0.15s}.btn-secundario:hover{background:#DBEAFE}.btn-secundario:disabled{opacity:0.5;cursor:not-allowed}.btn-eliminar{padding:10px 22px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;background:#DC2626;color:white;border:2px solid #DC2626;transition:background 0.15s}.btn-eliminar:hover{background:#B91C1C}.btn-eliminar:disabled{opacity:0.5;cursor:not-allowed}.btn-guardar{display:flex;align-items:center;gap:8px;padding:10px 22px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;background:#1B396A;color:#FFFFFF;border:2px solid #1B396A;transition:background 0.15s}.btn-guardar:hover:not(:disabled){background:#1D4ED8}.btn-guardar:disabled{opacity:0.55;cursor:not-allowed}.spinner-btn{display:inline-block;width:15px;height:15px;border:2px solid rgba(255,255,255,0.4);border-top-color:white;border-radius:50%;animation:girar 0.7s linear infinite;flex-shrink:0}
.detalle-fila{display:flex;justify-content:space-between;align-items:center;padding:11px 0;border-bottom:1px solid #E5E7EB;font-size:0.95rem;font-family:'Montserrat',sans-serif}.detalle-fila:last-child{border-bottom:none}.detalle-label{font-weight:600;color:#6B7280}.detalle-valor{font-weight:500;color:#1A1A1A}
.confirmar-body{display:flex;flex-direction:column;align-items:center;gap:1rem;text-align:center;padding:2rem 1.6rem}.confirmar-icono{width:52px;height:52px;stroke:#F59E0B}.confirmar-body p{color:#1A1A1A;font-size:0.95rem;margin:0;line-height:1.5;font-family:'Montserrat',sans-serif}
@keyframes girar{to{transform:rotate(360deg)}}
.pie-pagina{text-align:center;color:#9CA3AF;font-size:0.82rem;padding-top:2rem;border-top:1px solid #E5E7EB;margin-top:1rem}
</style>
