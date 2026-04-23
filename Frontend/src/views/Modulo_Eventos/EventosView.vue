<!-- ============================================= -->
<!-- src/views/EventosView.vue                    -->
<!-- Módulo: Eventos — Vista principal            -->
<!-- ============================================= -->
<template>
<MainLayout v-slot="{ busquedaGlobal }">
<div class="eventos-page">
<div class="barra-carga" :class="{ activa: cargando }"><div class="barra-progreso"></div></div>
<div class="breadcrumb">
<router-link to="/dashboard" class="breadcrumb-link">Inicio</router-link>
<span class="sep">›</span>
<span class="activo">Eventos</span>
</div>
<div class="encabezado-seccion">
<div>
<h1 class="titulo-pagina">Eventos</h1>
<p class="subtitulo">Gestión de eventos institucionales y control de participación</p>
</div>
<button @click="router.push('/eventos/nuevo')" class="btn-primario">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
Nuevo Evento
</button>
</div>

<div class="seccion-titulo-wrapper">
<h2 class="seccion-titulo">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
Eventos Próximos
</h2>
<span class="seccion-contador">{{ eventosProximos.length }} evento(s)</span>
</div>
<div v-if="eventosProximos.length > 0" class="eventos-proximos-grid">
<div v-for="evento in eventosProximos" :key="evento.id_evento" class="evento-card">
<div class="evento-card-izq">
<div class="stat-icono-wrapper" :style="{ background: colorFondoTipo(evento.tipo_evento?.nombre_tipo) }">
<svg viewBox="0 0 24 24" fill="none" :stroke="colorTipo(evento.tipo_evento?.nombre_tipo)" stroke-width="2" width="24" height="24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
</div>
</div>
<div class="evento-card-cuerpo">
<div class="evento-card-superior">
<span class="evento-nombre">{{ evento.nombre_evento }}</span>
<span class="badge-tipo" :style="estiloBadgeTipo(evento.tipo_evento?.nombre_tipo)">{{ evento.tipo_evento?.nombre_tipo || 'General' }}</span>
</div>
<div class="evento-card-meta">
<span class="meta-item">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
{{ formatearFecha(evento.fecha) }}
</span>
<span v-if="evento.descripcion" class="meta-item descripcion-resumida">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
{{ evento.descripcion.substring(0, 60) }}{{ evento.descripcion.length > 60 ? '...' : '' }}
</span>
</div>
</div>
<div class="evento-card-acciones">
<button @click="router.push(`/eventos/${evento.id_evento}/participantes`)" class="btn-secundario">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
Participantes
</button>
</div>
</div>
</div>
<div v-else class="estado-vacio">
<svg viewBox="0 0 24 24" fill="none" stroke="#D6D6D6" stroke-width="1.5" width="56" height="56"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
<p class="vacio-titulo">Sin eventos próximos</p>
<p class="vacio-subtitulo">Crea un nuevo evento para que aparezca aquí</p>
</div>

<div class="seccion-titulo-wrapper" style="margin-top: 2.5rem;">
<h2 class="seccion-titulo">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
Historial de Eventos
</h2>
</div>
<div class="filtros-card">
<div class="filtros-titulo">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
Filtrar:
</div>
<div class="busqueda-wrap">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-busqueda"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
<input v-model="busquedaNombre" type="text" placeholder="Buscar por nombre..." class="input-busqueda" @input="filtrar" />
<button v-if="busquedaNombre" @click="busquedaNombre = ''; filtrar()" class="btn-limpiar">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
</button>
</div>
<select v-model="filtroTipo" @change="filtrar" class="filtro-select">
<option value="">Todos los tipos</option>
<option v-for="t in tiposEvento" :key="t.id_tipo_evento" :value="t.id_tipo_evento">{{ t.nombre_tipo }}</option>
</select>
<button @click="filtrar" class="btn-buscar" :disabled="cargando">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
{{ cargando ? 'Buscando...' : 'Buscar' }}
</button>
</div>
<div class="tabla-card">
<div class="tabla-encabezado">
<span class="tabla-contador">{{ eventosFiltrados.length }} registro(s)</span>
</div>
<div class="tabla-scroll">
<table class="tabla-principal">
<thead>
<tr>
<th>Nombre del Evento</th>
<th>Tipo</th>
<th>Fecha</th>
<th>Descripción</th>
<th class="centrado">Acciones</th>
</tr>
</thead>
<tbody>
<tr v-for="evento in eventosFiltrados" :key="evento.id_evento">
<td><span class="texto-principal">{{ evento.nombre_evento }}</span></td>
<td><span class="badge-estado" :style="estiloBadgeTipo(evento.tipo_evento?.nombre_tipo)">{{ evento.tipo_evento?.nombre_tipo || 'General' }}</span></td>
<td class="texto-secundario">{{ formatearFecha(evento.fecha) }}</td>
<td class="texto-secundario texto-corto">{{ evento.descripcion || '—' }}</td>
<td class="centrado">
<div class="acciones-fila">
<button @click="router.push(`/eventos/${evento.id_evento}/participantes`)" class="btn-accion ver" title="Ver participantes">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
</button>
<button @click="router.push(`/eventos/${evento.id_evento}/editar`)" class="btn-accion editar" title="Editar evento">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
</button>
</div>
</td>
</tr>
<tr v-if="eventosFiltrados.length === 0">
<td colspan="5" class="sin-resultados">
<svg viewBox="0 0 24 24" fill="none" stroke="#D6D6D6" stroke-width="1.5" width="40" height="40"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
<p>No se encontraron eventos con los filtros aplicados</p>
</td>
</tr>
</tbody>
</table>
</div>
</div>
<footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>
</div>
</MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
const router = useRouter()
const API = `${import.meta.env.VITE_API_URL}/api`

const cargando = ref(false)
const busquedaNombre = ref('')
const filtroTipo = ref('')
const tiposEvento = ref([])
const eventos = ref([])

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  // Reutiliza sistema de toast global o localStorage si es necesario
  console.log(`[${tipo}] ${mensaje}`)
}

const cargarTipos = async () => {
  try {
    const res = await fetch(`${API}/tipo-evento`)
    if (!res.ok) throw new Error()
    tiposEvento.value = await res.json()
  } catch { tiposEvento.value = [] }
}

const cargarEventos = async () => {
  cargando.value = true
  try {
    const res = await fetch(`${API}/eventos`)
    if (!res.ok) throw new Error()
    eventos.value = await res.json()
  } catch (err) { console.error(err) }
  finally { cargando.value = false }
}

onMounted(() => { cargarTipos(); cargarEventos() })

const hoy = new Date().toISOString().split('T')[0]
const eventosProximos = computed(() =>
  eventos.value.filter(e => e.fecha >= hoy).sort((a,b) => a.fecha.localeCompare(b.fecha))
)
const eventosFiltrados = computed(() =>
  eventos.value
  .filter(e => e.fecha < hoy)
  .filter(e => {
    const matchNombre = !busquedaNombre.value || e.nombre_evento.toLowerCase().includes(busquedaNombre.value.toLowerCase())
    const matchTipo = !filtroTipo.value || e.id_tipo_evento === Number(filtroTipo.value)
    return matchNombre && matchTipo
  })
)

const filtrar = async () => {
  cargando.value = true
  try {
    const params = new URLSearchParams()
    if (busquedaNombre.value) params.append('nombre', busquedaNombre.value)
    if (filtroTipo.value) params.append('id_tipo_evento', filtroTipo.value)
    const res = await fetch(`${API}/eventos?${params}`)
    if (!res.ok) throw new Error()
    eventos.value = await res.json()
  } catch { console.error('Error filtrando') }
  finally { cargando.value = false }
}

const formatearFecha = (f) => {
  if (!f) return '—'
  const [a, m, d] = f.split('-')
  const meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre']
  return `${parseInt(d)} de ${meses[parseInt(m) - 1]} de ${a}`
}
const colorTipo = (t) => ({'Académico':'#1B396A','Cultural':'#F59E0B','Deportivo':'#16A34A','Institucional':'#2563EB'}[t] || '#6B7280')
const colorFondoTipo = (t) => ({'Académico':'#DBEAFE','Cultural':'#FEF3C7','Deportivo':'#DCFCE7','Institucional':'#EDE9FE'}[t] || '#F3F4F6')
const estiloBadgeTipo = (t) => ({ background: colorFondoTipo(t), color: colorTipo(t) })
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');
.eventos-page { width: 100%; font-family: 'Montserrat', sans-serif; padding-bottom: 2rem; }
.barra-carga { position: fixed; top: 74px; left: 0; right: 0; height: 3px; z-index: 1001; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: linear-gradient(90deg, #1B396A, #1D4ED8, #1B396A); background-size: 200% 100%; animation: carga-anim 1.4s linear infinite; }
@keyframes carga-anim { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
.breadcrumb { color: #6B7280; font-size: 0.875rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem; }
.breadcrumb .sep { color: #E5E7EB; }
.breadcrumb .activo { color: #1B396A; font-weight: 600; }
.breadcrumb-link { color: #6B7280; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #1B396A; }
.encabezado-seccion { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.titulo-pagina { color: #1A1A1A; font-size: 1.9rem; font-weight: 800; margin: 0 0 0.25rem; }
.subtitulo { color: #6B7280; font-size: 0.9rem; margin: 0; }
.btn-primario { background: #1B396A; color: #FFFFFF; border: none; padding: 10px 18px; border-radius: 10px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 6px; cursor: pointer; font-family: inherit; transition: background 0.2s; }
.btn-primario:hover { background: #1D4ED8; }
.btn-secundario { background: #DBEAFE; color: #1B396A; border: none; padding: 8px 14px; border-radius: 8px; font-weight: 600; font-size: 0.82rem; display: flex; align-items: center; gap: 5px; cursor: pointer; font-family: inherit; transition: background 0.2s; white-space: nowrap; }
.btn-secundario:hover { background: #bfdbfe; }
.seccion-titulo-wrapper { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.seccion-titulo { display: flex; align-items: center; gap: 8px; font-size: 1rem; font-weight: 700; color: #1A1A1A; margin: 0; }
.seccion-contador { font-size: 0.8rem; color: #6B7280; background: #F5F5F5; border: 1px solid #E5E7EB; padding: 4px 10px; border-radius: 20px; }
.eventos-proximos-grid { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1rem; }
.evento-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.4rem 1.6rem; display: flex; align-items: center; gap: 1.4rem; transition: box-shadow 0.2s, transform 0.2s; }
.evento-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.09); transform: translateY(-1px); }
.evento-card-izq { flex-shrink: 0; }
.stat-icono-wrapper { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.evento-card-cuerpo { flex: 1; min-width: 0; }
.evento-card-superior { display: flex; align-items: center; gap: 10px; margin-bottom: 0.6rem; flex-wrap: wrap; }
.evento-nombre { font-size: 1rem; font-weight: 700; color: #1A1A1A; }
.badge-tipo { font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.evento-card-meta { display: flex; align-items: center; gap: 1.2rem; flex-wrap: wrap; }
.meta-item { display: flex; align-items: center; gap: 5px; font-size: 0.82rem; color: #6B7280; }
.descripcion-resumida { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.evento-card-acciones { flex-shrink: 0; }
.estado-vacio { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; padding: 3rem; text-align: center; margin-bottom: 1rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; }
.vacio-titulo { font-size: 0.95rem; font-weight: 700; color: #6B7280; margin: 0; }
.vacio-subtitulo { font-size: 0.82rem; color: #9CA3AF; margin: 0; }
.filtros-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 0.9rem 1.4rem; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.filtros-titulo { display: flex; align-items: center; gap: 5px; font-size: 0.82rem; font-weight: 700; color: #6B7280; white-space: nowrap; }
.busqueda-wrap { display: flex; align-items: center; gap: 8px; background: #F5F5F5; border: 1px solid #E5E7EB; border-radius: 8px; padding: 0 12px; flex: 1; min-width: 220px; }
.busqueda-wrap:focus-within { border-color: #1B396A; background: #DBEAFE; }
.icono-busqueda { color: #6B7280; flex-shrink: 0; }
.input-busqueda { border: none; background: transparent; padding: 9px 0; font-size: 0.875rem; font-family: inherit; outline: none; flex: 1; color: #1A1A1A; }
.input-busqueda::placeholder { color: #9CA3AF; }
.btn-limpiar { background: none; border: none; color: #6B7280; cursor: pointer; padding: 2px; display: flex; align-items: center; }
.filtro-select { padding: 9px 10px; border: 1px solid #E5E7EB; border-radius: 8px; font-size: 0.82rem; font-family: inherit; color: #1A1A1A; background: #F5F5F5; outline: none; }
.filtro-select:focus { border-color: #1B396A; }
.btn-buscar { background: #1B396A; color: #FFFFFF; padding: 9px 1rem; border-radius: 8px; font-weight: 600; font-size: 0.82rem; display: flex; align-items: center; gap: 5px; border: none; cursor: pointer; font-family: inherit; transition: background 0.2s; }
.btn-buscar:hover { background: #1D4ED8; }
.btn-buscar:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }
.tabla-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 1.5rem; }
.tabla-encabezado { padding: 0.9rem 1.6rem; border-bottom: 1px solid #E5E7EB; display: flex; align-items: center; justify-content: flex-end; }
.tabla-contador { font-size: 0.8rem; color: #6B7280; background: #F5F5F5; border: 1px solid #E5E7EB; padding: 4px 10px; border-radius: 20px; }
.tabla-scroll { overflow-x: auto; }
.tabla-principal { width: 100%; border-collapse: collapse; }
.tabla-principal th { background: #F5F5F5; padding: 10px 14px; font-size: 0.78rem; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E5E7EB; text-align: left; }
.tabla-principal th.centrado { text-align: center; }
.tabla-principal td { padding: 10px 14px; border-bottom: 1px solid #E5E7EB; vertical-align: middle; }
.tabla-principal td.centrado { text-align: center; }
.tabla-principal tr:last-child td { border-bottom: none; }
.tabla-principal tr:hover { background: #F5F5F5; }
.texto-principal { font-weight: 600; color: #1A1A1A; font-size: 0.875rem; }
.texto-secundario { color: #6B7280; font-size: 0.875rem; }
.texto-corto { max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.badge-estado { font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.acciones-fila { display: flex; gap: 6px; justify-content: center; }
.btn-accion { width: 32px; height: 32px; border-radius: 8px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.15s; }
.btn-accion:hover { transform: scale(1.1); }
.btn-accion.ver { background: #DBEAFE; color: #1B396A; }
.btn-accion.editar { background: #F5F5F5; color: #6B7280; }
.sin-resultados { padding: 3rem; text-align: center; color: #9CA3AF; font-size: 0.875rem; display: flex; flex-direction: column; align-items: center; gap: 0.75rem; }
.sin-resultados p { margin: 0; }
.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem; }
</style>