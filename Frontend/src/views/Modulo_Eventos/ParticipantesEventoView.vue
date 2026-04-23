<!-- ============================================= -->
<!-- src/views/ParticipantesEventoView.vue        -->
<!-- Módulo: Eventos — Participantes del evento   -->
<!-- ============================================= -->
<template>
<MainLayout v-slot="{ busquedaGlobal }">
<div class="participantes-page">
<div class="barra-carga" :class="{ activa: cargando }"><div class="barra-progreso"></div></div>
<div class="breadcrumb">
<router-link to="/dashboard" class="breadcrumb-link">Inicio</router-link>
<span class="sep">›</span>
<router-link to="/eventos" class="breadcrumb-link">Eventos</router-link>
<span class="sep">›</span>
<span class="activo">Participantes</span>
</div>
<div class="evento-resumen-card">
<div class="resumen-info">
<div class="resumen-icono-wrap">
<svg viewBox="0 0 24 24" fill="none" stroke="#1B396A" stroke-width="2" width="28" height="28"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
</div>
<div class="resumen-datos">
<h1 class="resumen-nombre">{{ evento.nombre_evento }}</h1>
<div class="resumen-meta">
<span class="meta-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg> {{ formatearFecha(evento.fecha) }}</span>
<span v-if="evento.descripcion" class="meta-item descripcion-truncada">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
{{ evento.descripcion.substring(0, 80) }}{{ evento.descripcion.length > 80 ? '...' : '' }}
</span>
</div>
</div>
</div>
</div>

<div class="tabla-header-acciones">
<div>
<h2 class="seccion-titulo">Listado de Participantes</h2>
<p class="subtitulo">Alumnos registrados en este evento</p>
</div>
<button @click="mostrarModalRegistro = true" class="btn-primario">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
Registrar Participante
</button>
</div>

<div class="tabla-card">
<div class="tabla-scroll">
<table class="tabla-principal">
<thead>
<tr>
<th>No. de Control</th>
<th>Nombre Completo</th>
<th>Carrera</th>
<th class="centrado">Semestre</th>
<th class="centrado">Constancia</th>
<th class="centrado">Acciones</th>
</tr>
</thead>
<tbody>
<tr v-for="p in participantes" :key="p.id_participacion">
<td><span class="control-chip">{{ p.alumno?.no_control || '—' }}</span></td>
<td>
<div class="alumno-info">
<div class="alumno-avatar">{{ iniciales(p.alumno?.nombre) }}</div>
<span class="texto-principal">{{ p.alumno?.nombre || 'Sin nombre' }}</span>
</div>
</td>
<td class="texto-secundario">{{ p.alumno?.carrera || '—' }}</td>
<td class="centrado texto-secundario">{{ p.alumno?.semestre || '—' }}°</td>
<td class="centrado">
<span class="badge-estado" :style="p.constancia_emitida ? { background: '#DCFCE7', color: '#16A34A' } : { background: '#F3F4F6', color: '#6B7280' }">
{{ p.constancia_emitida ? 'Emitida' : 'Pendiente' }}
</span>
</td>
<td class="centrado">
<div class="acciones-fila">
<button v-if="!p.constancia_emitida" @click="emitirConstancia(p)" class="btn-constancia" title="Emitir constancia" :disabled="cargando">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
Emitir
</button>
<button @click="eliminarParticipante(p)" class="btn-accion eliminar" title="Eliminar participante">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
</button>
</div>
</td>
</tr>
<tr v-if="participantes.length === 0">
<td colspan="6" class="sin-resultados">
<svg viewBox="0 0 24 24" fill="none" stroke="#D6D6D6" stroke-width="1.5" width="40" height="40"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
<p>Sin participantes registrados</p>
</td>
</tr>
</tbody>
</table>
</div>
</div>
<footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>
</div>
</MainLayout>
<transition name="modal-fade">
<div v-if="mostrarModalRegistro" class="modal-fondo" @click.self="mostrarModalRegistro = false">
<div class="modal-caja">
<div class="modal-cabecera"><h3>Registrar Participante</h3><button @click="mostrarModalRegistro = false" class="btn-cerrar-modal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div>
<div class="modal-cuerpo">
<div class="campo-form">
<label class="campo-label">Buscar por Número de Control <span class="requerido">*</span></label>
<div class="busqueda-modal-wrap">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-busqueda"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
<input v-model="busquedaModal" type="text" placeholder="Ingresa el número de control..." class="campo-input" @input="buscarAlumnoModal" />
</div>
</div>
<div v-if="alumnoEncontrado" class="alumno-encontrado">
<div class="alumno-found-avatar">{{ iniciales(alumnoEncontrado.nombre) }}</div>
<div class="alumno-found-datos">
<span class="alumno-found-nombre">{{ alumnoEncontrado.nombre }}</span>
<span class="alumno-found-info">{{ alumnoEncontrado.no_control }} · {{ alumnoEncontrado.carrera }}</span>
</div>
</div>
<div v-else-if="busquedaModal.length > 2 && !alumnoEncontrado" class="alumno-no-encontrado">No se encontró ningún alumno con ese número de control</div>
</div>
<div class="modal-pie">
<button @click="mostrarModalRegistro = false" class="btn-cancelar">Cancelar</button>
<button @click="registrarParticipante" class="btn-guardar" :disabled="!alumnoEncontrado || cargando">
<span v-if="cargando" class="spinner"></span>
{{ cargando ? 'Registrando...' : 'Registrar' }}
</button>
</div>
</div>
</div>
</transition>
<transition name="toast-slide">
<div v-if="toast.visible" class="toast" :class="toast.tipo">
<span class="toast-icono"><svg v-if="toast.tipo==='exito'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg><svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></span>
{{ toast.mensaje }}
</div>
</transition>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
const router = useRouter()
const route = useRoute()
const API = `${import.meta.env.VITE_API_URL}/api`

const cargando = ref(false)
const mostrarModalRegistro = ref(false)
const busquedaModal = ref('')
const alumnoEncontrado = ref(null)
const evento = ref({ id_evento: null, nombre_evento: '', fecha: '', descripcion: '' })
const participantes = ref([])
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null, debounce = null

const mostrarNotificacion = (m, t = 'exito') => { clearTimeout(timerNotif); toast.value = { visible: true, mensaje: m, tipo: t }; timerNotif = setTimeout(() => toast.value.visible = false, 3500) }

const cargarEvento = async () => {
  try { const r = await fetch(`${API}/eventos/${route.params.id}`); if(!r.ok) throw new Error(); evento.value = await r.json() }
  catch (e) { console.error(e); mostrarNotificacion('No se pudo cargar el evento', 'error') }
}
const cargarParticipantes = async () => {
  cargando.value = true
  try { const r = await fetch(`${API}/eventos/${route.params.id}/participantes`); if(!r.ok) throw new Error(); participantes.value = await r.json() }
  catch (e) { console.error(e); participantes.value = []; mostrarNotificacion('Error cargando participantes', 'error') }
  finally { cargando.value = false }
}
onMounted(async () => { await cargarEvento(); await cargarParticipantes() })

const buscarAlumnoModal = () => {
  alumnoEncontrado.value = null
  if (busquedaModal.value.trim().length < 3) return
  clearTimeout(debounce)
  debounce = setTimeout(async () => {
    try {
      const r = await fetch(`${API}/alumnos/buscar-control?no_control=${encodeURIComponent(busquedaModal.value.trim())}`)
      if (!r.ok) throw new Error()
      alumnoEncontrado.value = await r.json()
    } catch { alumnoEncontrado.value = null }
  }, 400)
}

const registrarParticipante = async () => {
  if (!alumnoEncontrado.value) return
  cargando.value = true
  try {
    const r = await fetch(`${API}/eventos/${route.params.id}/participantes`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id_alumno: alumnoEncontrado.value.id_alumno })
    })
    if (!r.ok) throw new Error((await r.json()).message || 'Error')
    mostrarModalRegistro.value = false; busquedaModal.value = ''; alumnoEncontrado.value = null
    await cargarParticipantes(); mostrarNotificacion('Registrado correctamente')
  } catch (e) { mostrarNotificacion(e.message, 'error') }
  finally { cargando.value = false }
}

const emitirConstancia = async (p) => {
  cargando.value = true
  try {
    const r = await fetch(`${API}/eventos/${route.params.id}/participantes/${p.id_participacion}/constancia`, { method: 'PATCH' })
    if (!r.ok) throw new Error('No se pudo emitir')
    p.constancia_emitida = true; mostrarNotificacion('Constancia emitida')
  } catch (e) { mostrarNotificacion(e.message, 'error') }
  finally { cargando.value = false }
}

const eliminarParticipante = async (p) => {
  if (!confirm(`¿Eliminar a ${p.alumno?.nombre}?`)) return
  cargando.value = true
  try {
    const r = await fetch(`${API}/eventos/${route.params.id}/participantes/${p.id_participacion}`, { method: 'DELETE' })
    if (!r.ok) throw new Error('No se pudo eliminar')
    participantes.value = participantes.value.filter(x => x.id_participacion !== p.id_participacion)
    mostrarNotificacion('Participante eliminado')
  } catch (e) { mostrarNotificacion(e.message, 'error') }
  finally { cargando.value = false }
}

const formatearFecha = (f) => { if(!f) return '—'; const [a,m,d]=f.split('-'); const mes=['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre']; return `${parseInt(d)} de ${mes[parseInt(m)-1]} de ${a}` }
const iniciales = (n) => { if(!n) return '?'; return n.split(' ').filter(Boolean).slice(0,2).map(x=>x[0]).join('').toUpperCase() }
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');
.participantes-page { width: 100%; font-family: 'Montserrat', sans-serif; padding-bottom: 2rem; }
.barra-carga { position: fixed; top: 74px; left: 0; right: 0; height: 3px; z-index: 1001; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: linear-gradient(90deg, #1B396A, #1D4ED8, #1B396A); background-size: 200% 100%; animation: carga-anim 1.4s linear infinite; }
@keyframes carga-anim { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
.breadcrumb { color: #6B7280; font-size: 0.875rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem; }
.breadcrumb .sep { color: #E5E7EB; }
.breadcrumb .activo { color: #1B396A; font-weight: 600; }
.breadcrumb-link { color: #6B7280; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #1B396A; }
.evento-resumen-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.6rem; margin-bottom: 1.5rem; display: flex; gap: 1.5rem; align-items: flex-start; }
.resumen-info { display: flex; align-items: flex-start; gap: 14px; flex: 1; }
.resumen-icono-wrap { width: 56px; height: 56px; background: #DBEAFE; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.resumen-nombre { font-size: 1.2rem; font-weight: 800; color: #1A1A1A; margin: 0 0 0.4rem; }
.resumen-meta { display: flex; gap: 1.2rem; flex-wrap: wrap; align-items: center; }
.meta-item { display: flex; align-items: center; gap: 5px; font-size: 0.82rem; color: #6B7280; }
.descripcion-truncada { max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.tabla-header-acciones { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem; }
.seccion-titulo { font-size: 1rem; font-weight: 700; color: #1A1A1A; margin: 0 0 2px; }
.subtitulo { color: #6B7280; font-size: 0.82rem; margin: 0; }
.btn-primario { background: #1B396A; color: #FFFFFF; border: none; padding: 10px 18px; border-radius: 10px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 6px; cursor: pointer; font-family: inherit; transition: background 0.2s; }
.btn-primario:hover:not(:disabled) { background: #1D4ED8; }
.tabla-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 1.5rem; }
.tabla-scroll { overflow-x: auto; }
.tabla-principal { width: 100%; border-collapse: collapse; }
.tabla-principal th { background: #F5F5F5; padding: 10px 14px; font-size: 0.78rem; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E5E7EB; text-align: left; }
.tabla-principal th.centrado { text-align: center; }
.tabla-principal td { padding: 10px 14px; border-bottom: 1px solid #E5E7EB; vertical-align: middle; }
.tabla-principal td.centrado { text-align: center; }
.tabla-principal tr:last-child td { border-bottom: none; }
.tabla-principal tr:hover { background: #F5F5F5; }
.control-chip { background: #F5F5F5; border: 1px solid #E5E7EB; padding: 3px 8px; border-radius: 6px; font-family: monospace; font-size: 0.82rem; font-weight: 700; color: #1A1A1A; }
.alumno-info { display: flex; align-items: center; gap: 8px; }
.alumno-avatar { width: 32px; height: 32px; background: #DBEAFE; color: #1B396A; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.75rem; flex-shrink: 0; }
.texto-principal { font-weight: 600; color: #1A1A1A; font-size: 0.875rem; }
.texto-secundario { color: #6B7280; font-size: 0.875rem; }
.badge-estado { font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.acciones-fila { display: flex; gap: 6px; justify-content: center; align-items: center; }
.btn-constancia { background: #DBEAFE; color: #1B396A; border: none; padding: 5px 10px; border-radius: 7px; font-size: 0.78rem; font-weight: 700; display: flex; align-items: center; gap: 5px; cursor: pointer; font-family: inherit; transition: background 0.2s; }
.btn-constancia:hover:not(:disabled) { background: #bfdbfe; }
.btn-constancia:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-accion { width: 30px; height: 30px; border-radius: 7px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.15s; }
.btn-accion:hover { transform: scale(1.1); }
.btn-accion.eliminar { background: #FEF2F2; color: #DC2626; }
.sin-resultados { padding: 3rem; text-align: center; color: #9CA3AF; font-size: 0.875rem; display: flex; flex-direction: column; align-items: center; gap: 0.75rem; }
.sin-resultados p { margin: 0; }
.modal-fondo { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 2000; backdrop-filter: blur(3px); }
.modal-caja { background: #FFFFFF; width: 480px; border-radius: 16px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,0.25); }
.modal-cabecera { background: #1B396A; color: #FFFFFF; padding: 1.1rem 1.6rem; display: flex; justify-content: space-between; align-items: center; }
.modal-cabecera h3 { margin: 0; font-size: 1.1rem; font-weight: 800; }
.btn-cerrar-modal { background: none; border: none; color: rgba(255,255,255,0.8); cursor: pointer; display: flex; align-items: center; transition: color 0.2s; }
.btn-cerrar-modal:hover { color: #FFFFFF; }
.modal-cuerpo { padding: 1.6rem; display: flex; flex-direction: column; gap: 1rem; }
.campo-form { display: flex; flex-direction: column; gap: 6px; }
.campo-label { font-size: 0.82rem; font-weight: 700; color: #1A1A1A; }
.requerido { color: #DC2626; }
.busqueda-modal-wrap { display: flex; align-items: center; gap: 8px; background: #F5F5F5; border: 1.5px solid #E5E7EB; border-radius: 8px; padding: 0 12px; }
.busqueda-modal-wrap:focus-within { border-color: #1B396A; background: #DBEAFE; }
.icono-busqueda { color: #6B7280; flex-shrink: 0; }
.campo-input { width: 100%; padding: 10px 0; border: none; background: transparent; font-size: 0.875rem; font-family: inherit; color: #1A1A1A; outline: none; }
.campo-input::placeholder { color: #9CA3AF; }
.alumno-encontrado { display: flex; align-items: center; gap: 12px; background: #DCFCE7; border: 1px solid #16A34A; border-radius: 10px; padding: 1rem; }
.alumno-found-avatar { width: 40px; height: 40px; background: #1B396A; color: #FFFFFF; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.875rem; flex-shrink: 0; }
.alumno-found-nombre { font-weight: 700; color: #1A1A1A; font-size: 0.875rem; display: block; }
.alumno-found-info { font-size: 0.78rem; color: #6B7280; }
.alumno-no-encontrado { background: #FEF2F2; color: #DC2626; padding: 0.9rem 1rem; border-radius: 8px; font-size: 0.82rem; font-weight: 600; text-align: center; }
.modal-pie { padding: 1rem 1.6rem; background: #F5F5F5; border-top: 1px solid #E5E7EB; display: flex; justify-content: flex-end; gap: 0.75rem; }
.btn-cancelar { background: #FFFFFF; color: #6B7280; border: 1px solid #E5E7EB; padding: 9px 1.2rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; cursor: pointer; font-family: inherit; }
.btn-guardar { background: #1B396A; color: #FFFFFF; border: none; padding: 9px 1.4rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 6px; cursor: pointer; font-family: inherit; transition: background 0.2s; }
.btn-guardar:hover:not(:disabled) { background: #1D4ED8; }
.btn-guardar:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }
.spinner { width: 14px; height: 14px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.3); border-top-color: #FFFFFF; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.toast { position: fixed; bottom: 2rem; right: 2rem; padding: 0.9rem 1.4rem; border-radius: 10px; font-weight: 700; font-size: 0.9rem; font-family: 'Montserrat', sans-serif; display: flex; align-items: center; gap: 0.6rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 3000; }
.toast.exito { background: #1B396A; color: #FFFFFF; }
.toast.error { background: #DC2626; color: #FFFFFF; }
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.toast-slide-enter-active { transition: all 0.3s ease; }
.toast-slide-leave-active { transition: all 0.25s ease; }
.toast-slide-enter-from { transform: translateY(20px); opacity: 0; }
.toast-slide-leave-to { transform: translateX(100%); opacity: 0; }
.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem; }
</style>