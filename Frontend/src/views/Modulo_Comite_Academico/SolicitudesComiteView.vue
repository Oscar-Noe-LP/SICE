<!-- ============================================= -->
<!-- src/views/SolicitudesComiteView.vue          -->
<!-- Módulo: Comité Académico — Solicitudes       -->
<!-- ============================================= -->
<template>
<MainLayout v-slot="{ busquedaGlobal }">
<div class="solicitudes-page">
<div class="barra-carga" :class="{ activa: cargando }"><div class="barra-progreso"></div></div>
<div class="breadcrumb">
<router-link to="/dashboard" class="breadcrumb-link">Inicio</router-link>
<span class="sep">›</span>
<router-link to="/comite" class="breadcrumb-link">Comité Académico</router-link>
<span class="sep">›</span>
<span class="activo">Solicitudes</span>
</div>
<div class="encabezado-seccion">
<div>
<h1 class="titulo-pagina">Solicitudes</h1>
<p class="subtitulo">Gestión de solicitudes académicas recibidas por el comité</p>
</div>
<button @click="router.push('/comite/solicitudes/nueva')" class="btn-primario">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
Nueva Solicitud
</button>
</div>

<div class="filtros-card">
<div class="busqueda-wrap">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-busqueda"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
<input v-model="busqueda" type="text" placeholder="Buscar por nombre o folio..." class="input-busqueda" @input="reiniciarPagina()" />
<button v-if="busqueda" @click="busqueda = ''; reiniciarPagina()" class="btn-limpiar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
</div>
<button @click="mostrarFiltros = true" class="btn-icono" title="Filtros Avanzados">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
Filtros
</button>
</div>

<!-- Modal Filtros -->
<transition name="modal-fade">
<div v-if="mostrarFiltros" class="modal-fondo" @click.self="mostrarFiltros = false">
<div class="modal-caja">
<div class="modal-cabecera"><h3>Filtros de Solicitudes</h3><button @click="mostrarFiltros = false" class="btn-cerrar-modal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div>
<div class="modal-cuerpo filtros-grid">
<div class="campo-form"><label class="campo-label">Tipo de Solicitud</label>
<select v-model="filtroTipo" class="campo-input" @change="aplicarFiltros()"><option value="">Todos</option><option v-for="t in tiposSolicitud" :key="t.id" :value="t.nombre">{{ t.nombre }}</option></select>
</div>
<div class="campo-form"><label class="campo-label">Estatus de Resolución</label>
<select v-model="filtroEstatus" class="campo-input" @change="aplicarFiltros()"><option value="">Todos</option><option>Pendiente</option><option>En revisión</option><option>Aprobada</option><option>Rechazada</option></select>
</div>
</div>
<div class="modal-pie"><button @click="limpiarFiltros()" class="btn-cancelar">Limpiar</button><button @click="mostrarFiltros = false" class="btn-guardar">Aplicar</button></div>
</div>
</div>
</transition>

<div class="tabla-card">
<div class="tabla-encabezado"><span class="tabla-contador">{{ solicitudesFiltradas.length }} solicitud(es) · Página {{ paginaActual }} de {{ totalPaginas }}</span></div>
<div class="tabla-scroll">
<table class="tabla-principal tabla-densa">
<thead><tr><th>Folio</th><th>Solicitante</th><th>Tipo</th><th>Fecha</th><th class="centrado">Estatus</th><th class="centrado">Acciones</th></tr></thead>
<tbody>
<tr v-for="sol in solicitudesPaginadas" :key="sol.id">
<td><span class="folio-chip">{{ sol.folio }}</span></td>
<td><div class="persona-info"><div class="persona-avatar">{{ iniciales(sol.solicitante) }}</div><span class="texto-principal">{{ sol.solicitante }}</span></div></td>
<td class="texto-secundario">{{ sol.tipo }}</td>
<td class="texto-secundario">{{ formatearFecha(sol.fecha) }}</td>
<td class="centrado"><span class="badge-estado" :style="estiloBadgeEstado(sol.estatus)">{{ sol.estatus }}</span></td>
<td class="centrado">
<div class="acciones-fila">
<button @click="abrirDetalle(sol)" class="btn-accion ver" title="Ver detalles"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
<button @click="resolverSolicitud(sol)" class="btn-accion resolver" :disabled="tieneResolucion(sol)" :title="tieneResolucion(sol) ? 'Resuelta' : 'Registrar resolución'"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></button>
</div>
</td>
</tr>
<tr v-if="solicitudesPaginadas.length === 0"><td colspan="6" class="sin-resultados"><svg viewBox="0 0 24 24" fill="none" stroke="#D6D6D6" stroke-width="1.5" width="40" height="40"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><p>No se encontraron solicitudes</p></td></tr>
</tbody>
</table>
</div>
<div v-if="totalPaginas > 1" class="paginacion-wrap">
<button @click="cambiarPagina(paginaActual - 1)" :disabled="paginaActual === 1" class="btn-pag">Anterior</button>
<div class="pag-numeros"><button v-for="p in totalPaginas" :key="p" @click="cambiarPagina(p)" class="btn-pag-num" :class="{ activa: paginaActual === p }">{{ p }}</button></div>
<button @click="cambiarPagina(paginaActual + 1)" :disabled="paginaActual === totalPaginas" class="btn-pag">Siguiente</button>
</div>
</div>

<!-- Modal Detalle Solicitud (Solo Lectura) -->
<transition name="modal-fade">
<div v-if="mostrarDetalle && solicitudDetalle" class="modal-fondo" @click.self="mostrarDetalle = false">
<div class="modal-caja modal-ancho">
<div class="modal-cabecera"><h3>Detalle de Solicitud</h3><button @click="mostrarDetalle = false" class="btn-cerrar-modal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div>
<div class="modal-cuerpo detalle-grid">
<div class="detalle-item"><span class="detalle-label">Folio</span><span class="detalle-valor">{{ solicitudDetalle.folio }}</span></div>
<div class="detalle-item"><span class="detalle-label">Solicitante</span><span class="detalle-valor">{{ solicitudDetalle.solicitante }}</span></div>
<div class="detalle-item"><span class="detalle-label">Tipo</span><span class="detalle-valor">{{ solicitudDetalle.tipo }}</span></div>
<div class="detalle-item"><span class="detalle-label">Fecha de Registro</span><span class="detalle-valor">{{ formatearFecha(solicitudDetalle.fecha) }}</span></div>
<div class="detalle-item"><span class="detalle-label">Estatus</span><span class="badge-estado" :style="estiloBadgeEstado(solicitudDetalle.estatus)">{{ solicitudDetalle.estatus }}</span></div>
<div class="detalle-item ancho"><span class="detalle-label">Descripción</span><p class="detalle-desc">{{ solicitudDetalle.descripcion || 'Sin descripción proporcionada.' }}</p></div>
</div>
<div class="modal-pie"><button @click="mostrarDetalle = false" class="btn-cerrar-simple">Cerrar</button></div>
</div>
</div>
</transition>

<transition name="toast-slide">
<div v-if="toast.visible" class="toast" :class="toast.tipo"><span class="toast-icono"><svg v-if="toast.tipo==='exito'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg><svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></span>{{ toast.mensaje }}</div>
</transition>

<footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>
</div>
</MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
const router = useRouter()
const cargando       = ref(false)
const errorCarga     = ref(false)
const busqueda       = ref('')
const filtroTipo     = ref('')
const filtroEstatus  = ref('')
const mostrarFiltros = ref(false)
const tiposSolicitud = ref([])
const solicitudes    = ref([])

// ── Carga inicial ─────────────────────────────────────────────
const BASE = `${import.meta.env.VITE_API_URL}/api`

const cargarTipos = async () => {
  try {
    const res = await fetch(`${BASE}/comite/tipos-solicitud`)
    if (!res.ok) throw new Error()
    tiposSolicitud.value = await res.json()
  } catch {
    // Sin tipos el selector queda vacío
  }
}

const cargarSolicitudes = async () => {
  cargando.value = true; errorCarga.value = false
  try {
    const res = await fetch(`${BASE}/comite/solicitudes`)
    if (!res.ok) throw new Error('Error en la respuesta del servidor')
    const data = await res.json(); solicitudes.value = Array.isArray(data) ? data : data.data ?? []
  } catch (error) { console.error('Error cargando solicitudes:', error); errorCarga.value = true }
  finally { cargando.value = false }
}
onMounted(() => { cargarTipos(); cargarSolicitudes() })

const solicitudesFiltradas = computed(() =>
  solicitudes.value.filter(s => {
    const q = busqueda.value.toLowerCase()
    const coincide = !q || s.folio?.toLowerCase().includes(q) || s.solicitante?.toLowerCase().includes(q)
    const tipo = !filtroTipo.value || s.tipo === filtroTipo.value
    const estatus = !filtroEstatus.value || s.estatus === filtroEstatus.value
    return coincide && tipo && estatus
  })
)

const paginaActual = ref(1)
const registrosPorPagina = 10
const totalPaginas = computed(() => Math.max(1, Math.ceil(solicitudesFiltradas.value.length / registrosPorPagina)))
const solicitudesPaginadas = computed(() => {
  const inicio = (paginaActual.value - 1) * registrosPorPagina
  return solicitudesFiltradas.value.slice(inicio, inicio + registrosPorPagina)
})
const reiniciarPagina = () => { paginaActual.value = 1 }
const cambiarPagina = (p) => { if (p >= 1 && p <= totalPaginas.value) paginaActual.value = p }
const aplicarFiltros = () => { reiniciarPagina(); filtrarBackend() }
const limpiarFiltros = () => { filtroTipo.value = ''; filtroEstatus.value = ''; reiniciarPagina(); filtrarBackend() }

const filtrarBackend = async () => {
  cargando.value = true
  try {
    const params = new URLSearchParams()
    if (busqueda.value) params.append('q', busqueda.value)
    if (filtroTipo.value) params.append('tipo', filtroTipo.value)
    if (filtroEstatus.value) params.append('estatus', filtroEstatus.value)
    const url = `${BASE}/comite/solicitudes` + (params.toString() ? '?' + params : '')
    const res = await fetch(url)
    if (!res.ok) throw new Error('Error en la respuesta del servidor')
    const data = await res.json(); solicitudes.value = Array.isArray(data) ? data : data.data ?? []
  } catch (error) { console.error('Error filtrando:', error); errorCarga.value = true }
  finally { cargando.value = false }
}

const mostrarDetalle = ref(false)
const solicitudDetalle = ref(null)
const abrirDetalle = (sol) => { solicitudDetalle.value = sol; mostrarDetalle.value = true }

const tieneResolucion = (sol) => !!sol.resolucion_id || ['Aprobada','Rechazada'].includes(sol.estatus)
const estiloBadgeEstado = (est) => {
  const fondos = { 'Pendiente': '#DBEAFE', 'En revisión': '#FEF3C7', 'Aprobada': '#DCFCE7', 'Rechazada': '#FEF2F2' }
  const textos = { 'Pendiente': '#1B396A', 'En revisión': '#F59E0B', 'Aprobada': '#16A34A',  'Rechazada': '#DC2626'  }
  return { background: fondos[est] || '#F3F4F6', color: textos[est] || '#6B7280' }
}
const formatearFecha = (f) => { if (!f) return '—'; const [a, m, d] = f.split('-'); const meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic']; return `${parseInt(d)} ${meses[parseInt(m)-1]} ${a}` }
const iniciales = (nombre) => { if (!nombre) return '?'; return nombre.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase() }
const resolverSolicitud = (sol) => router.push(`/comite/resoluciones/nueva?solicitud=${sol.id}`)

const toast = ref({ visible: false, mensaje: '', tipo: 'exito' }); let timerNotif = null
const mostrarNotificacion = (m, t = 'exito') => { clearTimeout(timerNotif); toast.value = { visible: true, mensaje: m, tipo: t }; timerNotif = setTimeout(() => toast.value.visible = false, 3500) }
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');
.solicitudes-page { width: 100%; font-family: 'Montserrat', sans-serif; padding-bottom: 2rem; }
.barra-carga { position: fixed; top: 74px; left: 0; right: 0; height: 3px; z-index: 1001; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
.barra-carga.activa { opacity: 1; } .barra-progreso { height: 100%; background: linear-gradient(90deg, #1B396A, #1D4ED8, #1B396A); background-size: 200% 100%; animation: carga-anim 1.4s linear infinite; }
@keyframes carga-anim { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
.breadcrumb { color: #6B7280; font-size: 0.875rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem; }
.breadcrumb .sep { color: #E5E7EB; } .breadcrumb .activo { color: #1B396A; font-weight: 600; }
.breadcrumb-link { color: #6B7280; text-decoration: none; transition: color 0.15s; } .breadcrumb-link:hover { color: #1B396A; }
.encabezado-seccion { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.titulo-pagina { color: #1A1A1A; font-size: 1.9rem; font-weight: 800; margin: 0 0 0.25rem; } .subtitulo { color: #6B7280; font-size: 0.9rem; margin: 0; }
.btn-primario { background: #1B396A; color: #FFFFFF; border: none; padding: 10px 18px; border-radius: 10px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 6px; cursor: pointer; font-family: inherit; transition: background 0.2s; }
.btn-primario:hover { background: #1D4ED8; }
.filtros-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 0.9rem 1.4rem; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.busqueda-wrap { display: flex; align-items: center; gap: 8px; background: #F5F5F5; border: 1px solid #E5E7EB; border-radius: 8px; padding: 0 12px; flex: 1; min-width: 220px; }
.busqueda-wrap:focus-within { border-color: #1B396A; background: #DBEAFE; }
.icono-busqueda { color: #6B7280; flex-shrink: 0; }
.input-busqueda { border: none; background: transparent; padding: 9px 0; font-size: 0.875rem; font-family: inherit; outline: none; flex: 1; color: #1A1A1A; }
.input-busqueda::placeholder { color: #9CA3AF; }
.btn-limpiar { background: none; border: none; color: #6B7280; cursor: pointer; padding: 2px; display: flex; align-items: center; }
.btn-icono { background: #FFFFFF; color: #6B7280; border: 1px solid #E5E7EB; padding: 9px 14px; border-radius: 8px; font-weight: 600; font-size: 0.82rem; display: flex; align-items: center; gap: 6px; cursor: pointer; font-family: inherit; transition: all 0.2s; }
.btn-icono:hover { background: #F5F5F5; color: #1B396A; border-color: #1B396A; }

.modal-fondo { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 2000; backdrop-filter: blur(3px); }
.modal-caja { background: #FFFFFF; width: 480px; border-radius: 16px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,0.25); }
.modal-caja.modal-ancho { width: 560px; }
.modal-cabecera { background: #1B396A; color: #FFFFFF; padding: 1.1rem 1.6rem; display: flex; justify-content: space-between; align-items: center; }
.modal-cabecera h3 { margin: 0; font-size: 1.1rem; font-weight: 800; }
.btn-cerrar-modal { background: none; border: none; color: rgba(255,255,255,0.8); cursor: pointer; display: flex; align-items: center; transition: color 0.2s; }
.btn-cerrar-modal:hover { color: #FFFFFF; }
.modal-cuerpo { padding: 1.6rem; display: flex; flex-direction: column; gap: 1rem; }
.filtros-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.campo-form { display: flex; flex-direction: column; gap: 6px; }
.campo-label { font-size: 0.82rem; font-weight: 700; color: #1A1A1A; }
.campo-input { padding: 10px 14px; border: 1.5px solid #E5E7EB; border-radius: 8px; font-size: 0.875rem; font-family: inherit; color: #1A1A1A; outline: none; background: #FFFFFF; }
.modal-pie { padding: 1rem 1.6rem; background: #F5F5F5; border-top: 1px solid #E5E7EB; display: flex; justify-content: flex-end; gap: 0.75rem; }
.btn-cancelar { background: #FFFFFF; color: #6B7280; border: 1px solid #E5E7EB; padding: 9px 1.2rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; cursor: pointer; font-family: inherit; }
.btn-guardar { background: #1B396A; color: #FFFFFF; border: none; padding: 9px 1.4rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 6px; cursor: pointer; font-family: inherit; transition: background 0.2s; }
.btn-guardar:hover { background: #1D4ED8; }

.detalle-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; }
.detalle-item { display: flex; flex-direction: column; gap: 4px; }
.detalle-item.ancho { grid-column: 1 / -1; }
.detalle-label { font-size: 0.75rem; font-weight: 700; color: #6B7280; text-transform: uppercase; }
.detalle-valor { font-size: 0.9rem; font-weight: 600; color: #1A1A1A; }
.detalle-desc { margin: 0; font-size: 0.85rem; color: #4B5563; line-height: 1.5; background: #F9FAFB; padding: 1rem; border-radius: 8px; }
.btn-cerrar-simple { background: #FFFFFF; color: #6B7280; border: 1px solid #E5E7EB; padding: 8px 1.2rem; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: inherit; }

.tabla-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 1.5rem; }
.tabla-encabezado { padding: 0.8rem 1.4rem; border-bottom: 1px solid #E5E7EB; display: flex; align-items: center; justify-content: flex-end; }
.tabla-contador { font-size: 0.8rem; color: #6B7280; background: #F5F5F5; border: 1px solid #E5E7EB; padding: 4px 10px; border-radius: 20px; }
.tabla-scroll { overflow-x: auto; }
.tabla-principal { width: 100%; border-collapse: collapse; }
.tabla-principal th { background: #F5F5F5; padding: 8px 12px; font-size: 0.75rem; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E5E7EB; text-align: left; }
.tabla-principal th.centrado { text-align: center; }
.tabla-principal td { padding: 6px 12px; border-bottom: 1px solid #E5E7EB; vertical-align: middle; }
.tabla-principal td.centrado { text-align: center; }
.tabla-principal tr:last-child td { border-bottom: none; }
.tabla-principal tr:hover { background: #F9FAFB; }
.folio-chip { background: #F5F5F5; border: 1px solid #E5E7EB; padding: 3px 8px; border-radius: 6px; font-family: monospace; font-size: 0.8rem; font-weight: 700; color: #1A1A1A; }
.persona-info { display: flex; align-items: center; gap: 8px; }
.persona-avatar { width: 28px; height: 28px; background: #DBEAFE; color: #1B396A; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.7rem; flex-shrink: 0; }
.texto-principal { font-weight: 600; color: #1A1A1A; font-size: 0.82rem; }
.texto-secundario { color: #6B7280; font-size: 0.8rem; }
.badge-estado { font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 20px; }
.acciones-fila { display: flex; gap: 4px; justify-content: center; align-items: center; }
.btn-accion { width: 26px; height: 26px; border-radius: 5px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.15s, background 0.15s; }
.btn-accion:hover:not(:disabled) { transform: scale(1.1); }
.btn-accion.ver { background: #DBEAFE; color: #1B396A; }
.btn-accion.resolver { background: #DCFCE7; color: #16A34A; }
.btn-accion:disabled { background: #F3F4F6; color: #9CA3AF; cursor: not-allowed; }
.sin-resultados { padding: 2.5rem; text-align: center; color: #9CA3AF; font-size: 0.85rem; display: flex; flex-direction: column; align-items: center; gap: 0.75rem; }
.paginacion-wrap { padding: 0.8rem 1.4rem; border-top: 1px solid #E5E7EB; display: flex; align-items: center; justify-content: space-between; background: #F9FAFB; }
.btn-pag { padding: 6px 12px; border: 1px solid #E5E7EB; background: #FFFFFF; border-radius: 6px; font-size: 0.8rem; cursor: pointer; color: #4B5563; }
.btn-pag:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-pag:hover:not(:disabled) { background: #F3F4F6; }
.pag-numeros { display: flex; gap: 4px; }
.btn-pag-num { width: 28px; height: 28px; border-radius: 6px; border: 1px solid #E5E7EB; background: #FFFFFF; font-size: 0.8rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.btn-pag-num.activa { background: #1B396A; color: #FFFFFF; border-color: #1B396A; }
.btn-pag-num:hover:not(.activa) { background: #F3F4F6; }

.toast { position: fixed; bottom: 2rem; right: 2rem; padding: 0.9rem 1.4rem; border-radius: 10px; font-weight: 700; font-size: 0.9rem; font-family: 'Montserrat', sans-serif; display: flex; align-items: center; gap: 0.6rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 3000; }
.toast.exito { background: #1B396A; color: #FFFFFF; } .toast.error { background: #DC2626; color: #FFFFFF; }
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s; } .modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.toast-slide-enter-active { transition: all 0.3s ease; } .toast-slide-leave-active { transition: all 0.25s ease; } .toast-slide-enter-from { transform: translateY(20px); opacity: 0; } .toast-slide-leave-to { transform: translateX(100%); opacity: 0; }
.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem; }
@media (max-width: 640px) { .filtros-grid, .detalle-grid { grid-template-columns: 1fr; } .paginacion-wrap { flex-direction: column; gap: 0.5rem; } }
</style>