<!-- ============================================= -->
<!-- src/views/ComiteView.vue                     -->
<!-- Módulo: Comité Académico — Dashboard         -->
<!-- ============================================= -->
<template>
<MainLayout v-slot="{ busquedaGlobal }">
<div class="comite-page">
<div class="barra-carga" :class="{ activa: cargando }"><div class="barra-progreso"></div></div>
<div class="breadcrumb">
<router-link to="/dashboard" class="breadcrumb-link">Inicio</router-link>
<span class="sep">›</span>
<span class="activo">Comité Académico</span>
</div>
<div class="encabezado-seccion">
<div>
<h1 class="titulo-pagina">Comité Académico</h1>
<p class="subtitulo">Gestión de solicitudes y resoluciones del comité institucional</p>
</div>
</div>

<div class="kpi-grid">
<div class="kpi-card azul">
<div class="kpi-icono"><svg viewBox="0 0 24 24" fill="none" stroke="#1B396A" stroke-width="2" width="22" height="22"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></div>
<div class="kpi-datos"><span class="kpi-numero">{{ kpis.pendientes }}</span><span class="kpi-etiqueta">Solicitudes Pendientes</span></div>
<button @click="router.push('/comite/solicitudes')" class="kpi-enlace">Ver solicitudes <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg></button>
</div>
<div class="kpi-card verde">
<div class="kpi-icono"><svg viewBox="0 0 24 24" fill="none" stroke="#16A34A" stroke-width="2" width="22" height="22"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
<div class="kpi-datos"><span class="kpi-numero">{{ kpis.resueltas }}</span><span class="kpi-etiqueta">Solicitudes Resueltas</span></div>
<button @click="router.push('/comite/resoluciones')" class="kpi-enlace">Ver resoluciones <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg></button>
</div>
<div class="kpi-card naranja">
<div class="kpi-icono"><svg viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2" width="22" height="22"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
<div class="kpi-datos"><span class="kpi-numero">{{ kpis.sesiones }}</span><span class="kpi-etiqueta">Sesiones Programadas</span></div>
<button @click="router.push('/comite/sesiones')" class="kpi-enlace">Ver sesiones <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg></button>
</div>
</div>

<div class="listas-grid">
<div class="lista-card">
<div class="lista-cabecera"><h3 class="lista-titulo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Solicitudes Pendientes Recientes</h3><button @click="router.push('/comite/solicitudes')" class="btn-ver-todo">Ver todas</button></div>
<div class="lista-cuerpo">
<div v-for="sol in solicitudesPendientes" :key="sol.id" class="item-actividad"><span class="punto-color" :style="{ background: colorEstado(sol.estatus) }"></span><div class="item-texto"><span class="item-titulo">{{ sol.folio }} — {{ sol.solicitante }}</span><span class="item-subtitulo">{{ sol.tipo }} · {{ formatearFecha(sol.fecha) }}</span></div><span class="badge-estado" :style="estiloBadgeEstado(sol.estatus)">{{ sol.estatus }}</span></div>
<div v-if="solicitudesPendientes.length === 0" class="lista-vacia">Sin solicitudes pendientes</div>
</div>
</div>
<div class="lista-card">
<div class="lista-cabecera"><h3 class="lista-titulo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Próximas Sesiones</h3><button @click="router.push('/comite/sesiones')" class="btn-ver-todo">Ver todas</button></div>
<div class="lista-cuerpo">
<div v-for="ses in sesionesRecientes" :key="ses.id" class="item-actividad"><span class="punto-color" style="background: #1B396A;"></span><div class="item-texto"><span class="item-titulo">{{ formatearFecha(ses.fecha) }}</span><span class="item-subtitulo">{{ ses.descripcion }} · {{ ses.resoluciones }} resolución(es)</span></div><button @click="router.push(`/comite/resoluciones?sesion=${ses.id}`)" class="btn-mini">Ver</button></div>
<div v-if="sesionesRecientes.length === 0" class="lista-vacia">Sin sesiones programadas</div>
</div>
</div>
</div>

<div class="accesos-rapidos">
<button @click="router.push('/comite/solicitudes/nueva')" class="acceso-card"><div class="acceso-icono"><svg viewBox="0 0 24 24" fill="none" stroke="#1B396A" stroke-width="2" width="22" height="22"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div><span class="acceso-texto">Nueva Solicitud</span></button>
<button @click="router.push('/comite/sesiones')" class="acceso-card"><div class="acceso-icono"><svg viewBox="0 0 24 24" fill="none" stroke="#1B396A" stroke-width="2" width="22" height="22"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div><span class="acceso-texto">Nueva Sesión</span></button>
<button @click="router.push('/comite/resoluciones')" class="acceso-card"><div class="acceso-icono"><svg viewBox="0 0 24 24" fill="none" stroke="#1B396A" stroke-width="2" width="22" height="22"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div><span class="acceso-texto">Registrar Resolución</span></button>
</div>
<footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>
</div>
</MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
const router = useRouter()
const cargando = ref(false)
const errorCarga = ref(false)
const kpis = ref({ pendientes: 0, resueltas: 0, sesiones: 0 })
const solicitudesPendientes = ref([])
const sesionesRecientes = ref([])

const API_BASE = `${import.meta.env.VITE_API_URL}/api/comite`

const cargarDatos = async () => {
  cargando.value = true; errorCarga.value = false
  try {
    const res = await fetch(`${API_BASE}/dashboard`)
    if (!res.ok) throw new Error('Error en la respuesta del servidor')
    const data = await res.json()
    kpis.value = { pendientes: data.kpis?.pendientes ?? 0, resueltas: data.kpis?.resueltas ?? 0, sesiones: data.kpis?.sesiones ?? 0 }
    solicitudesPendientes.value = data.solicitudes_pendientes ?? []
    sesionesRecientes.value = data.sesiones_recientes ?? []
  } catch (error) { console.error('Error cargando dashboard del comité:', error); errorCarga.value = true }
  finally { cargando.value = false }
}
onMounted(() => { cargarDatos() })

const colorEstado = (est) => {
  const m = { Pendiente: '#1B396A', 'En revisión': '#F59E0B', Aprobada: '#16A34A', Rechazada: '#DC2626', Resuelta: '#0F766E' }
  return m[est] || '#9CA3AF'
}
const estiloBadgeEstado = (est) => {
  const fondos = { Pendiente: '#DBEAFE', 'En revisión': '#FEF3C7', Aprobada: '#DCFCE7', Rechazada: '#FEF2F2', Resuelta: '#CCFBF1' }
  const textos = { Pendiente: '#1B396A', 'En revisión': '#F59E0B', Aprobada: '#16A34A', Rechazada: '#DC2626', Resuelta: '#0F766E' }
  return { background: fondos[est] || '#F3F4F6', color: textos[est] || '#6B7280' }
}
const formatearFecha = (f) => {
  if (!f) return '—'
  const partes = f.split('-')
  if (partes.length !== 3) return f
  const [a, m, d] = partes
  const meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic']
  return `${parseInt(d)} ${meses[parseInt(m) - 1]} ${a}`
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');
.comite-page { width: 100%; font-family: 'Montserrat', sans-serif; padding-bottom: 2rem; }
.barra-carga { position: fixed; top: 74px; left: 0; right: 0; height: 3px; z-index: 1001; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: linear-gradient(90deg, #1B396A, #1D4ED8, #1B396A); background-size: 200% 100%; animation: carga-anim 1.4s linear infinite; }
@keyframes carga-anim { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
.breadcrumb { color: #6B7280; font-size: 0.875rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem; }
.breadcrumb .sep { color: #E5E7EB; } .breadcrumb .activo { color: #1B396A; font-weight: 600; }
.breadcrumb-link { color: #6B7280; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #1B396A; }
.encabezado-seccion { margin-bottom: 1.5rem; }
.titulo-pagina { color: #1A1A1A; font-size: 1.9rem; font-weight: 800; margin: 0 0 0.25rem; }
.subtitulo { color: #6B7280; font-size: 0.9rem; margin: 0; }

/* KPI Compactados */
.kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
.kpi-card {
background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB;
box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1rem 1.2rem;
display: flex; flex-direction: column; gap: 0.5rem;
border-left: 4px solid transparent; transition: transform 0.2s, box-shadow 0.2s;
}
.kpi-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.09); }
.kpi-card.azul    { border-left-color: #1B396A; }
.kpi-card.verde   { border-left-color: #16A34A; }
.kpi-card.naranja { border-left-color: #F59E0B; }
.kpi-icono { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
.kpi-card.azul    .kpi-icono { background: #DBEAFE; }
.kpi-card.verde   .kpi-icono { background: #DCFCE7; }
.kpi-card.naranja .kpi-icono { background: #FEF3C7; }
.kpi-datos { display: flex; flex-direction: column; }
.kpi-numero  { font-size: 1.8rem; font-weight: 800; color: #1A1A1A; line-height: 1; }
.kpi-etiqueta { font-size: 0.78rem; color: #6B7280; font-weight: 600; margin-top: 2px; }
.kpi-enlace { background: none; border: none; color: #1B396A; font-weight: 600; font-size: 0.78rem; font-family: inherit; cursor: pointer; display: flex; align-items: center; gap: 4px; padding: 0; transition: opacity 0.2s; }
.kpi-enlace:hover { opacity: 0.75; }

.listas-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
.lista-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; }
.lista-cabecera { padding: 0.8rem 1.2rem; border-bottom: 1px solid #E5E7EB; display: flex; justify-content: space-between; align-items: center; }
.lista-titulo { display: flex; align-items: center; gap: 8px; font-size: 0.875rem; font-weight: 700; color: #1A1A1A; margin: 0; }
.btn-ver-todo { background: none; border: none; color: #1B396A; font-weight: 600; font-size: 0.78rem; font-family: inherit; cursor: pointer; transition: opacity 0.2s; }
.btn-ver-todo:hover { opacity: 0.7; }
.lista-cuerpo { padding: 0.5rem 0; }
.item-actividad { display: flex; align-items: center; gap: 10px; padding: 0.6rem 1.2rem; border-bottom: 1px solid #F5F5F5; transition: background 0.15s; }
.item-actividad:last-child { border-bottom: none; }
.item-actividad:hover { background: #F5F5F5; }
.punto-color { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.item-texto { flex: 1; min-width: 0; }
.item-titulo { display: block; font-size: 0.8rem; font-weight: 700; color: #1A1A1A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.item-subtitulo { display: block; font-size: 0.72rem; color: #6B7280; margin-top: 2px; }
.badge-estado { font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 20px; white-space: nowrap; flex-shrink: 0; }
.btn-mini { background: #DBEAFE; color: #1B396A; border: none; padding: 3px 8px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; cursor: pointer; font-family: inherit; flex-shrink: 0; }
.lista-vacia { padding: 1.2rem; text-align: center; color: #9CA3AF; font-size: 0.8rem; }
.accesos-rapidos { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
.acceso-card { background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1rem 1.4rem; display: flex; align-items: center; gap: 10px; cursor: pointer; font-family: inherit; transition: box-shadow 0.2s, transform 0.2s; }
.acceso-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.09); transform: translateY(-1px); }
.acceso-icono { width: 36px; height: 36px; background: #DBEAFE; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.acceso-texto { font-size: 0.85rem; font-weight: 700; color: #1A1A1A; }
.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem; }
@media (max-width: 900px) { .kpi-grid { grid-template-columns: 1fr 1fr; } .listas-grid { grid-template-columns: 1fr; } }
@media (max-width: 640px) { .kpi-grid { grid-template-columns: 1fr; } .accesos-rapidos { flex-direction: column; } }
</style>