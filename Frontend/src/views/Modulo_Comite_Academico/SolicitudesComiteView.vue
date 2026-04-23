<!-- ============================================= -->
<!-- src/views/SolicitudesComiteView.vue          -->
<!-- Módulo: Comité Académico — Solicitudes       -->
<!-- ============================================= -->
<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="solicitudes-page">

      <div class="barra-carga" :class="{ activa: cargando }">
        <div class="barra-progreso"></div>
      </div>

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
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Nueva Solicitud
        </button>
      </div>

      <!-- Filtros -->
      <div class="filtros-card">
        <div class="filtros-titulo">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
          </svg>
          Filtrar:
        </div>
        <div class="busqueda-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-busqueda">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input
            v-model="busqueda"
            type="text"
            placeholder="Buscar por nombre o folio..."
            class="input-busqueda"
            @input="filtrar"
          />
          <button v-if="busqueda" @click="busqueda = ''; filtrar()" class="btn-limpiar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        <select v-model="filtroTipo" @change="filtrar" class="filtro-select">
          <option value="">Todos los tipos</option>
          <option v-for="t in tiposSolicitud" :key="t.id" :value="t.nombre">{{ t.nombre }}</option>
        </select>
        <select v-model="filtroEstatus" @change="filtrar" class="filtro-select">
          <option value="">Todos los estatus</option>
          <option>Pendiente</option>
          <option>En revisión</option>
          <option>Aprobada</option>
          <option>Rechazada</option>
        </select>
        <button @click="filtrar" class="btn-buscar" :disabled="cargando">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          {{ cargando ? 'Buscando...' : 'Buscar' }}
        </button>
      </div>

      <!-- Tabla -->
      <div class="tabla-card">
        <div class="tabla-encabezado">
          <span class="tabla-contador">{{ solicitudesFiltradas.length }} solicitud(es)</span>
        </div>
        <div class="tabla-scroll">
          <table class="tabla-principal">
            <thead>
              <tr>
                <th>Folio</th>
                <th>Solicitante</th>
                <th>Tipo de Solicitud</th>
                <th>Fecha de Registro</th>
                <th class="centrado">Estatus</th>
                <th class="centrado">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="sol in solicitudesFiltradas" :key="sol.id">
                <td>
                  <span class="folio-chip">{{ sol.folio }}</span>
                </td>
                <td>
                  <div class="persona-info">
                    <div class="persona-avatar">{{ iniciales(sol.solicitante) }}</div>
                    <span class="texto-principal">{{ sol.solicitante }}</span>
                  </div>
                </td>
                <td class="texto-secundario">{{ sol.tipo }}</td>
                <td class="texto-secundario">{{ formatearFecha(sol.fecha) }}</td>
                <td class="centrado">
                  <span class="badge-estado" :style="estiloBadgeEstado(sol.estatus)">
                    {{ sol.estatus }}
                  </span>
                </td>
                <td class="centrado">
                  <div class="acciones-fila">
                    <button
                      @click="router.push(`/comite/solicitudes/${sol.id}`)"
                      class="btn-accion ver"
                      title="Ver solicitud"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                      </svg>
                      Ver
                    </button>
                    <button
                      @click="resolverSolicitud(sol)"
                      class="btn-accion"
                      :class="tieneResolucion(sol) ? 'resuelto' : 'resolver'"
                      :title="tieneResolucion(sol) ? 'Ya tiene resolución' : 'Registrar resolución'"
                      :disabled="tieneResolucion(sol)"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                      </svg>
                      Resolver
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="solicitudesFiltradas.length === 0">
                <td colspan="6" class="sin-resultados">
                  <svg viewBox="0 0 24 24" fill="none" stroke="#D6D6D6" stroke-width="1.5" width="40" height="40">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                  </svg>
                  <p>No se encontraron solicitudes</p>
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

const API = `${import.meta.env.VITE_API_URL}/api`

const router = useRouter()

const cargando       = ref(false)
const errorCarga     = ref(false)
const busqueda       = ref('')
const filtroTipo     = ref('')
const filtroEstatus  = ref('')

const tiposSolicitud = ref([])
const solicitudes    = ref([])

// ── Carga inicial ─────────────────────────────────────────────
const cargarTipos = async () => {
  try {
    const res = await fetch(`${API}/comite/tipos-solicitud`)
    if (!res.ok) throw new Error()
    tiposSolicitud.value = await res.json()
  } catch {
    // Sin tipos el selector queda vacío
  }
}

const cargarSolicitudes = async () => {
  cargando.value   = true
  errorCarga.value = false
  try {
    const res = await fetch(`${API}/comite/solicitudes`)
    if (!res.ok) throw new Error('Error en la respuesta del servidor')
    const data = await res.json()
    solicitudes.value = Array.isArray(data) ? data : data.data ?? []
  } catch (error) {
    console.error('Error cargando solicitudes:', error)
    errorCarga.value = true
  } finally {
    cargando.value = false
  }
}

onMounted(() => {
  cargarTipos()
  cargarSolicitudes()
})

// ── Filtrado local reactivo ────────────────────────────────────
const solicitudesFiltradas = computed(() =>
  solicitudes.value.filter(s => {
    const q       = busqueda.value.toLowerCase()
    const coincide = !q || s.folio?.toLowerCase().includes(q) || s.solicitante?.toLowerCase().includes(q)
    const tipo    = !filtroTipo.value    || s.tipo    === filtroTipo.value
    const estatus = !filtroEstatus.value || s.estatus === filtroEstatus.value
    return coincide && tipo && estatus
  })
)

// ── Buscar en el backend con parámetros ────────────────────────
const filtrar = async () => {
  cargando.value   = true
  errorCarga.value = false
  try {
    const params = new URLSearchParams()
    if (busqueda.value)      params.append('q',       busqueda.value)
    if (filtroTipo.value)    params.append('tipo',    filtroTipo.value)
    if (filtroEstatus.value) params.append('estatus', filtroEstatus.value)
    const url = `${API}/comite/solicitudes` + (params.toString() ? '?' + params : '')
    const res = await fetch(url)
    if (!res.ok) throw new Error('Error en la respuesta del servidor')
    const data = await res.json()
    solicitudes.value = Array.isArray(data) ? data : data.data ?? []
  } catch (error) {
    console.error('Error filtrando solicitudes:', error)
    errorCarga.value = true
  } finally {
    cargando.value = false
  }
}

// ── Helpers ───────────────────────────────────────────────────
const tieneResolucion = (sol) => !!sol.resolucion_id || ['Aprobada','Rechazada'].includes(sol.estatus)

const estiloBadgeEstado = (est) => {
  const fondos = { 'Pendiente': '#DBEAFE', 'En revisión': '#FEF3C7', 'Aprobada': '#DCFCE7', 'Rechazada': '#FEF2F2' }
  const textos = { 'Pendiente': '#1B396A', 'En revisión': '#F59E0B', 'Aprobada': '#16A34A',  'Rechazada': '#DC2626'  }
  return { background: fondos[est] || '#F3F4F6', color: textos[est] || '#6B7280' }
}
const formatearFecha = (f) => {
  if (!f) return '—'
  const [a, m, d] = f.split('-')
  const meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic']
  return `${parseInt(d)} ${meses[parseInt(m)-1]} ${a}`
}
const iniciales = (nombre) => {
  if (!nombre) return '?'
  return nombre.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase()
}
const resolverSolicitud = (sol) => router.push(`/comite/resoluciones/nueva?solicitud=${sol.id}`)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.solicitudes-page { width: 100%; font-family: 'Montserrat', sans-serif; padding-bottom: 2rem; }
.barra-carga { position: fixed; top: 74px; left: 0; right: 0; height: 3px; z-index: 1001; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: linear-gradient(90deg, #1B396A, #1D4ED8, #1B396A); background-size: 200% 100%; animation: carga-anim 1.4s linear infinite; }
@keyframes carga-anim { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

.breadcrumb { color: #6B7280; font-size: 0.875rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem; }
.breadcrumb .sep { color: #E5E7EB; } .breadcrumb .activo { color: #1B396A; font-weight: 600; }
.breadcrumb-link { color: #6B7280; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #1B396A; }

.encabezado-seccion { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.titulo-pagina { color: #1A1A1A; font-size: 1.9rem; font-weight: 800; margin: 0 0 0.25rem; }
.subtitulo { color: #6B7280; font-size: 0.9rem; margin: 0; }

.btn-primario {
  background: #1B396A; color: #FFFFFF; border: none; padding: 10px 18px; border-radius: 10px;
  font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 6px;
  cursor: pointer; font-family: inherit; transition: background 0.2s;
}
.btn-primario:hover { background: #1D4ED8; }

.filtros-card {
  background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 0.9rem 1.4rem;
  display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem;
}
.filtros-titulo { display: flex; align-items: center; gap: 5px; font-size: 0.82rem; font-weight: 700; color: #6B7280; white-space: nowrap; }
.busqueda-wrap {
  display: flex; align-items: center; gap: 8px; background: #F5F5F5; border: 1px solid #E5E7EB;
  border-radius: 8px; padding: 0 12px; flex: 1; min-width: 220px;
}
.busqueda-wrap:focus-within { border-color: #1B396A; background: #DBEAFE; }
.icono-busqueda { color: #6B7280; flex-shrink: 0; }
.input-busqueda { border: none; background: transparent; padding: 9px 0; font-size: 0.875rem; font-family: inherit; outline: none; flex: 1; color: #1A1A1A; }
.input-busqueda::placeholder { color: #9CA3AF; }
.btn-limpiar { background: none; border: none; color: #6B7280; cursor: pointer; padding: 2px; display: flex; align-items: center; }
.filtro-select { padding: 9px 10px; border: 1px solid #E5E7EB; border-radius: 8px; font-size: 0.82rem; font-family: inherit; color: #1A1A1A; background: #F5F5F5; outline: none; }
.filtro-select:focus { border-color: #1B396A; }
.btn-buscar {
  background: #1B396A; color: #FFFFFF; padding: 9px 1rem; border-radius: 8px;
  font-weight: 600; font-size: 0.82rem; display: flex; align-items: center; gap: 5px;
  border: none; cursor: pointer; font-family: inherit; transition: background 0.2s;
}
.btn-buscar:hover { background: #1D4ED8; }
.btn-buscar:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }

.tabla-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 1.5rem; }
.tabla-encabezado { padding: 0.9rem 1.6rem; border-bottom: 1px solid #E5E7EB; display: flex; justify-content: flex-end; }
.tabla-contador { font-size: 0.8rem; color: #6B7280; background: #F5F5F5; border: 1px solid #E5E7EB; padding: 4px 10px; border-radius: 20px; }
.tabla-scroll { overflow-x: auto; }
.tabla-principal { width: 100%; border-collapse: collapse; }
.tabla-principal th { background: #F5F5F5; padding: 10px 14px; font-size: 0.78rem; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E5E7EB; text-align: left; }
.tabla-principal th.centrado { text-align: center; }
.tabla-principal td { padding: 10px 14px; border-bottom: 1px solid #E5E7EB; vertical-align: middle; }
.tabla-principal td.centrado { text-align: center; }
.tabla-principal tr:last-child td { border-bottom: none; }
.tabla-principal tr:hover { background: #F5F5F5; }

.folio-chip { background: #DBEAFE; color: #1B396A; padding: 3px 10px; border-radius: 6px; font-family: monospace; font-size: 0.82rem; font-weight: 700; }
.persona-info { display: flex; align-items: center; gap: 8px; }
.persona-avatar { width: 32px; height: 32px; background: #DBEAFE; color: #1B396A; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.75rem; flex-shrink: 0; }
.texto-principal  { font-weight: 600; color: #1A1A1A; font-size: 0.875rem; }
.texto-secundario { color: #6B7280; font-size: 0.875rem; }
.badge-estado { font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }

.acciones-fila { display: flex; gap: 6px; justify-content: center; }
.btn-accion {
  padding: 5px 10px; border-radius: 7px; border: none; font-size: 0.78rem; font-weight: 700;
  display: flex; align-items: center; gap: 5px; cursor: pointer; font-family: inherit; transition: background 0.2s, transform 0.15s;
}
.btn-accion:hover:not(:disabled) { transform: scale(1.04); }
.btn-accion.ver     { background: #DBEAFE; color: #1B396A; }
.btn-accion.resolver { background: #DCFCE7; color: #16A34A; }
/* Botón resolver deshabilitado: gris — mismo patrón que grupo lleno */
.btn-accion.resuelto { background: #F3F4F6; color: #9CA3AF; cursor: not-allowed; }
.btn-accion:disabled { cursor: not-allowed; transform: none; }

.sin-resultados { padding: 3rem; text-align: center; color: #9CA3AF; font-size: 0.875rem; display: flex; flex-direction: column; align-items: center; gap: 0.75rem; }
.sin-resultados p { margin: 0; }
.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem; }
</style>