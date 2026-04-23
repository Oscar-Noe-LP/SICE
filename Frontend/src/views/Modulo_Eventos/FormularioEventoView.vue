<!-- ============================================= -->
<!-- src/views/FormularioEventoView.vue           -->
<!-- Módulo: Eventos — Crear / Editar evento      -->
<!-- ============================================= -->
<template>
<MainLayout v-slot="{ busquedaGlobal }">
<div class="formulario-evento-page">
<div class="barra-carga" :class="{ activa: cargando }"><div class="barra-progreso"></div></div>
<div class="breadcrumb">
<router-link to="/dashboard" class="breadcrumb-link">Inicio</router-link>
<span class="sep">›</span>
<router-link to="/eventos" class="breadcrumb-link">Eventos</router-link>
<span class="sep">›</span>
<span class="activo">{{ modoEdicion ? 'Editar Evento' : 'Nuevo Evento' }}</span>
</div>
<div class="encabezado-seccion">
<div>
<h1 class="titulo-pagina">{{ modoEdicion ? 'Editar Evento' : 'Nuevo Evento' }}</h1>
<p class="subtitulo">{{ modoEdicion ? 'Modifica la información del evento' : 'Completa la información para registrar un nuevo evento' }}</p>
</div>
</div>
<form @submit.prevent="guardar" novalidate>
<div class="seccion-card">
<div class="seccion-titulo">
<div class="seccion-icono">
<svg viewBox="0 0 24 24" fill="none" stroke="#1B396A" stroke-width="2" width="20" height="20"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
</div>
<div>
<h2 class="seccion-nombre">Información del Evento</h2>
<p class="seccion-desc">Datos generales del evento institucional</p>
</div>
</div>
<div class="divisor"></div>
<div class="campos-grid">
<div class="campo-form campo-ancho">
<label class="campo-label">Nombre del Evento <span class="requerido">*</span></label>
<input v-model="form.nombre_evento" type="text" placeholder="Ej: Semana de Ingeniería 2026" class="campo-input" :class="{ 'campo-error': errores.nombre_evento }" @input="validarCampo('nombre_evento')" />
<span v-if="errores.nombre_evento" class="mensaje-error">{{ errores.nombre_evento }}</span>
</div>
<div class="campo-form">
<label class="campo-label">Tipo de Evento <span class="requerido">*</span></label>
<select v-model="form.id_tipo_evento" class="campo-input" :class="{ 'campo-error': errores.id_tipo_evento }" @change="validarCampo('id_tipo_evento')">
<option value="">Selecciona un tipo</option>
<option v-for="t in tiposEvento" :key="t.id_tipo_evento" :value="t.id_tipo_evento">{{ t.nombre_tipo }}</option>
</select>
<span v-if="errores.id_tipo_evento" class="mensaje-error">{{ errores.id_tipo_evento }}</span>
</div>
<div class="campo-form">
<label class="campo-label">Fecha del Evento <span class="requerido">*</span></label>
<div class="campo-fecha-wrap">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-campo"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
<input v-model="form.fecha" type="date" :min="fechaMinima" class="campo-input campo-input-fecha" :class="{ 'campo-error': errores.fecha }" @change="validarCampo('fecha')" />
</div>
<span v-if="errores.fecha" class="mensaje-error">{{ errores.fecha }}</span>
</div>
<div class="campo-form campo-ancho">
<label class="campo-label">Descripción</label>
<textarea v-model="form.descripcion" rows="3" placeholder="Describe brevemente el objetivo o contenido del evento..." class="campo-input campo-textarea"></textarea>
</div>
</div>
</div>
<div class="acciones-form">
<button type="button" @click="router.push('/eventos')" class="btn-cancelar">Cancelar</button>
<button type="submit" class="btn-guardar" :disabled="cargando">
<span v-if="cargando" class="spinner"></span>
<svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
{{ cargando ? 'Guardando...' : (modoEdicion ? 'Actualizar Evento' : 'Guardar Evento') }}
</button>
</div>
</form>
<footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>
</div>
</MainLayout>
<transition name="toast-slide">
<div v-if="toast.visible" class="toast" :class="toast.tipo">
<span class="toast-icono">
<svg v-if="toast.tipo === 'exito'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg>
<svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
</span>
{{ toast.mensaje }}
</div>
</transition>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
const router = useRouter()
const route = useRoute()
const API = `${import.meta.env.VITE_API_URL}/api`

const modoEdicion = computed(() => !!route.params.id)
const cargando = ref(false)
const tiposEvento = ref([])
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null
const mostrarNotificacion = (m, t = 'exito') => { clearTimeout(timerNotif); toast.value = { visible: true, mensaje: m, tipo: t }; timerNotif = setTimeout(() => toast.value.visible = false, 3500) }
const fechaMinima = computed(() => new Date().toISOString().split('T')[0])

const form = ref({ nombre_evento: '', id_tipo_evento: '', fecha: '', descripcion: '' })
const errores = ref({ nombre_evento: '', id_tipo_evento: '', fecha: '', descripcion: '' })

const cargarTipos = async () => { try { const r = await fetch(`${API}/tipo-evento`); if(!r.ok) throw new Error(); tiposEvento.value = await r.json() } catch { mostrarNotificacion('No se pudieron cargar los tipos de evento.', 'error') } }
const cargarEvento = async () => {
  cargando.value = true
  try {
    const r = await fetch(`${API}/eventos/${route.params.id}`)
    if (!r.ok) throw new Error()
    const d = await r.json()
    form.value = { nombre_evento: d.nombre_evento, id_tipo_evento: d.id_tipo_evento, fecha: d.fecha, descripcion: d.descripcion || '' }
  } catch (e) { console.error(e); mostrarNotificacion('No se pudo cargar el evento', 'error') }
  finally { cargando.value = false }
}
onMounted(() => { cargarTipos(); if (modoEdicion.value) cargarEvento() })

const validarCampo = (c) => {
  errores.value[c] = ''
  if (c === 'nombre_evento' && !form.value.nombre_evento.trim()) errores.value.nombre_evento = 'Requerido'
  if (c === 'id_tipo_evento' && !form.value.id_tipo_evento) errores.value.id_tipo_evento = 'Selecciona un tipo'
  if (c === 'fecha') {
    if (!form.value.fecha) errores.value.fecha = 'Requerida'
    else if (form.value.fecha < fechaMinima.value) errores.value.fecha = 'Fecha no puede ser pasada'
  }
}
const validarTodo = () => { ['nombre_evento','id_tipo_evento','fecha'].forEach(validarCampo); return !Object.values(errores.value).some(Boolean) }

const guardar = async () => {
  if (!validarTodo()) return mostrarNotificacion('Revisa los campos marcados', 'error')
  cargando.value = true
  try {
    const url = modoEdicion.value ? `${API}/eventos/${route.params.id}` : `${API}/eventos`
    const method = modoEdicion.value ? 'PUT' : 'POST'
    const payload = { ...form.value, nombre_evento: form.value.nombre_evento.trim(), descripcion: form.value.descripcion.trim() }
    const res = await fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) })
    if (!res.ok) throw new Error((await res.json()).message || 'Error del servidor')
    mostrarNotificacion(modoEdicion.value ? 'Evento actualizado' : 'Evento creado')
    setTimeout(() => router.push('/eventos'), 800)
  } catch (e) { mostrarNotificacion(e.message, 'error') }
  finally { cargando.value = false }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');
.formulario-evento-page { width: 100%; font-family: 'Montserrat', sans-serif; padding-bottom: 2rem; }
.barra-carga { position: fixed; top: 74px; left: 0; right: 0; height: 3px; z-index: 1001; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: linear-gradient(90deg, #1B396A, #1D4ED8, #1B396A); background-size: 200% 100%; animation: carga-anim 1.4s linear infinite; }
@keyframes carga-anim { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
.breadcrumb { color: #6B7280; font-size: 0.875rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem; }
.breadcrumb .sep { color: #E5E7EB; }
.breadcrumb .activo { color: #1B396A; font-weight: 600; }
.breadcrumb-link { color: #6B7280; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #1B396A; }
.encabezado-seccion { margin-bottom: 1.5rem; }
.titulo-pagina { color: #1A1A1A; font-size: 1.9rem; font-weight: 800; margin: 0 0 0.25rem; }
.subtitulo { color: #6B7280; font-size: 0.9rem; margin: 0; }
.seccion-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.6rem; margin-bottom: 1.5rem; }
.seccion-titulo { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 1rem; }
.seccion-icono { width: 40px; height: 40px; background: #DBEAFE; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.seccion-nombre { font-size: 1rem; font-weight: 700; color: #1A1A1A; margin: 0 0 2px; }
.seccion-desc { font-size: 0.82rem; color: #6B7280; margin: 0; }
.divisor { height: 1px; background: #E5E7EB; margin-bottom: 1.4rem; }
.campos-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; }
.campo-form { display: flex; flex-direction: column; gap: 6px; }
.campo-ancho { grid-column: 1 / -1; }
.campo-label { font-size: 0.82rem; font-weight: 700; color: #1A1A1A; }
.requerido { color: #DC2626; }
.campo-input { padding: 10px 14px; border: 1.5px solid #E5E7EB; border-radius: 8px; font-size: 0.875rem; font-family: inherit; color: #1A1A1A; outline: none; background: #FFFFFF; transition: border-color 0.2s; }
.campo-input:focus { border-color: #1B396A; background: #DBEAFE; }
.campo-input::placeholder { color: #9CA3AF; }
.campo-input.campo-error { border-color: #DC2626; }
.campo-textarea { resize: vertical; min-height: 80px; }
.campo-fecha-wrap { position: relative; display: flex; align-items: center; }
.icono-campo { position: absolute; left: 12px; color: #6B7280; pointer-events: none; }
.campo-input-fecha { padding-left: 38px; }
.mensaje-error { font-size: 0.78rem; color: #DC2626; font-weight: 500; }
.acciones-form { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem; }
.btn-cancelar { background: #FFFFFF; color: #6B7280; border: 1px solid #E5E7EB; padding: 10px 1.4rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; cursor: pointer; font-family: inherit; transition: background 0.2s; }
.btn-cancelar:hover { background: #F5F5F5; }
.btn-guardar { background: #1B396A; color: #FFFFFF; border: none; padding: 10px 1.6rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 8px; cursor: pointer; font-family: inherit; transition: background 0.2s; }
.btn-guardar:hover:not(:disabled) { background: #1D4ED8; }
.btn-guardar:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }
.spinner { width: 16px; height: 16px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.3); border-top-color: #FFFFFF; animation: spin 0.7s linear infinite; flex-shrink: 0; }
@keyframes spin { to { transform: rotate(360deg); } }
.toast { position: fixed; bottom: 2rem; right: 2rem; padding: 0.9rem 1.4rem; border-radius: 10px; font-weight: 700; font-size: 0.9rem; font-family: 'Montserrat', sans-serif; display: flex; align-items: center; gap: 0.6rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 3000; }
.toast.exito { background: #1B396A; color: #FFFFFF; }
.toast.error { background: #DC2626; color: #FFFFFF; }
.toast-slide-enter-active { transition: all 0.3s ease; }
.toast-slide-leave-active { transition: all 0.25s ease; }
.toast-slide-enter-from { transform: translateY(20px); opacity: 0; }
.toast-slide-leave-to { transform: translateX(100%); opacity: 0; }
.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem; }
@media (max-width: 640px) { .campos-grid { grid-template-columns: 1fr; } }
</style>