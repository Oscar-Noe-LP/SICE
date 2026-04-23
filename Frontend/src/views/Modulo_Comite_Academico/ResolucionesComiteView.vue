<!-- ============================================= -->
<!-- src/views/ResolucionesComiteView.vue         -->
<!-- Módulo: Comité Académico — Resoluciones      -->
<!-- ============================================= -->
<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="resoluciones-page">

      <div class="barra-carga" :class="{ activa: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <div class="breadcrumb">
        <router-link to="/dashboard" class="breadcrumb-link">Inicio</router-link>
        <span class="sep">›</span>
        <router-link to="/comite" class="breadcrumb-link">Comité Académico</router-link>
        <span class="sep">›</span>
        <span class="activo">Resoluciones</span>
      </div>

      <div class="encabezado-seccion">
        <div>
          <h1 class="titulo-pagina">Resoluciones</h1>
          <p class="subtitulo">Registro de decisiones emitidas por el comité académico</p>
        </div>
        <button @click="abrirModalNueva" class="btn-primario">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Nueva Resolución
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
            placeholder="Buscar por folio o nombre..."
            class="input-busqueda"
            @input="filtrar"
          />
          <button v-if="busqueda" @click="busqueda = ''; filtrar()" class="btn-limpiar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        <select v-model="filtroSesion" @change="filtrar" class="filtro-select">
          <option value="">Todas las sesiones</option>
          <option v-for="s in sesiones" :key="s.id" :value="s.id">{{ formatearFechaCorta(s.fecha) }} — {{ s.descripcion }}</option>
        </select>
        <select v-model="filtroTipo" @change="filtrar" class="filtro-select">
          <option value="">Todos los tipos</option>
          <option v-for="t in tiposSolicitud" :key="t.id" :value="t.nombre">{{ t.nombre }}</option>
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
          <span class="tabla-contador">{{ resolucionesFiltradas.length }} resolución(es)</span>
        </div>
        <div class="tabla-scroll">
          <table class="tabla-principal">
            <thead>
              <tr>
                <th>Folio Solicitud</th>
                <th>Solicitante</th>
                <th>Tipo</th>
                <th>Sesión</th>
                <th>Decisión</th>
                <th>Fecha</th>
                <th class="centrado">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="res in resolucionesFiltradas" :key="res.id">
                <td>
                  <span class="folio-chip">{{ res.folio_solicitud }}</span>
                </td>
                <td>
                  <div class="persona-info">
                    <div class="persona-avatar">{{ iniciales(res.solicitante) }}</div>
                    <span class="texto-principal">{{ res.solicitante }}</span>
                  </div>
                </td>
                <td class="texto-secundario">{{ res.tipo }}</td>
                <td class="texto-secundario sesion-texto">{{ formatearFechaCorta(res.fecha_sesion) }}</td>
                <td>
                  <p class="decision-texto">{{ res.decision }}</p>
                </td>
                <td class="texto-secundario">{{ formatearFechaCorta(res.fecha) }}</td>
                <td class="centrado">
                  <button
                    @click="verDetalle(res)"
                    class="btn-accion ver"
                    title="Ver detalle"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                    Ver
                  </button>
                </td>
              </tr>
              <tr v-if="resolucionesFiltradas.length === 0">
                <td colspan="7" class="sin-resultados">
                  <svg viewBox="0 0 24 24" fill="none" stroke="#D6D6D6" stroke-width="1.5" width="40" height="40">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                  </svg>
                  <p>No se encontraron resoluciones</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>
    </div>
  </MainLayout>

  <!-- Modal nueva resolución -->
  <transition name="modal-fade">
    <div v-if="mostrarModal" class="modal-fondo" @click.self="cerrarModal">
      <div class="modal-caja">
        <div class="modal-cabecera">
          <h3>Nueva Resolución</h3>
          <button @click="cerrarModal" class="btn-cerrar-modal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        <div class="modal-cuerpo">

          <!-- Selector de sesión activa -->
          <div class="campo-form">
            <label class="campo-label">Sesión <span class="requerido">*</span></label>
            <select v-model="formModal.sesion_id" class="campo-input" :class="{ 'campo-error': errModal.sesion_id }" @change="errModal.sesion_id = ''">
              <option value="">Selecciona una sesión</option>
              <option v-for="s in sesiones" :key="s.id" :value="s.id">
                {{ formatearFechaCorta(s.fecha) }} — {{ s.descripcion }}
              </option>
            </select>
            <span v-if="errModal.sesion_id" class="mensaje-error">{{ errModal.sesion_id }}</span>
          </div>

          <!-- Selector de solicitud pendiente -->
          <div class="campo-form">
            <label class="campo-label">
              Solicitud Pendiente <span class="requerido">*</span>
              <span class="campo-nota">(solo solicitudes sin resolución)</span>
            </label>
            <select v-model="formModal.solicitud_id" class="campo-input" :class="{ 'campo-error': errModal.solicitud_id }" @change="errModal.solicitud_id = ''">
              <option value="">Selecciona una solicitud</option>
              <option v-for="s in solicitudesSinRes" :key="s.id" :value="s.id">
                {{ s.folio }} — {{ s.solicitante }}
              </option>
            </select>
            <span v-if="errModal.solicitud_id" class="mensaje-error">{{ errModal.solicitud_id }}</span>
            <span v-if="solicitudesSinRes.length === 0" class="campo-nota-aviso">
              No hay solicitudes pendientes sin resolución
            </span>
          </div>

          <!-- Decisión -->
          <div class="campo-form">
            <label class="campo-label">Decisión del Comité <span class="requerido">*</span></label>
            <textarea
              v-model="formModal.decision"
              rows="4"
              placeholder="Describe la decisión tomada por el comité..."
              class="campo-input campo-textarea"
              :class="{ 'campo-error': errModal.decision }"
              @input="errModal.decision = ''"
            ></textarea>
            <span v-if="errModal.decision" class="mensaje-error">{{ errModal.decision }}</span>
          </div>

        </div>
        <div class="modal-pie">
          <button @click="cerrarModal" class="btn-cancelar">Cancelar</button>
          <button @click="guardarResolucion" class="btn-guardar" :disabled="cargando">
            <span v-if="cargando" class="spinner"></span>
            {{ cargando ? 'Registrando...' : 'Registrar Resolución' }}
          </button>
        </div>
      </div>
    </div>
  </transition>

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

const API = `${import.meta.env.VITE_API_URL}/api`

const router = useRouter()
const route  = useRoute()

const cargando     = ref(false)
const errorCarga   = ref(false)
const busqueda     = ref('')
const filtroSesion = ref('')
const filtroTipo   = ref('')
const mostrarModal = ref(false)

const tiposSolicitud    = ref([])
const sesiones          = ref([])
const solicitudesSinRes = ref([])
const resoluciones      = ref([])

const formModal = ref({ sesion_id: '', solicitud_id: '', decision: '' })
const errModal  = ref({ sesion_id: '', solicitud_id: '', decision: '' })

// ── Toast (corregido: era "notificacion") ─────────────────────
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null
const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  toast.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { toast.value.visible = false }, 3500)
}

// ── Carga inicial ─────────────────────────────────────────────
const cargarTipos = async () => {
  try {
    const res = await fetch(`${API}/comite/tipos-solicitud`)
    if (!res.ok) throw new Error()
    tiposSolicitud.value = await res.json()
  } catch { /* silencioso */ }
}

const cargarSesiones = async () => {
  try {
    const res = await fetch(`${API}/comite/sesiones`)
    if (!res.ok) throw new Error()
    const data = await res.json()
    sesiones.value = Array.isArray(data) ? data : data.data ?? []
  } catch { /* silencioso */ }
}

const cargarSolicitudesSinResolucion = async () => {
  try {
    const res = await fetch(`${API}/comite/solicitudes?sin_resolucion=1`)
    if (!res.ok) throw new Error()
    const data = await res.json()
    solicitudesSinRes.value = Array.isArray(data) ? data : data.data ?? []
  } catch { /* silencioso */ }
}

const cargarResoluciones = async () => {
  cargando.value   = true
  errorCarga.value = false
  try {
    const params = new URLSearchParams()
    if (filtroSesion.value) params.append('sesion_id', filtroSesion.value)
    if (filtroTipo.value)   params.append('tipo',      filtroTipo.value)
    const url = `${API}/comite/resoluciones` + (params.toString() ? '?' + params : '')
    const res = await fetch(url)
    if (!res.ok) throw new Error('Error en la respuesta del servidor')
    const data = await res.json()
    resoluciones.value = Array.isArray(data) ? data : data.data ?? []
  } catch (error) {
    console.error('Error cargando resoluciones:', error)
    errorCarga.value = true
    mostrarNotificacion('No se pudieron cargar las resoluciones.', 'error')
  } finally {
    cargando.value = false
  }
}

onMounted(() => {
  if (route.query.sesion)    filtroSesion.value           = route.query.sesion
  if (route.query.solicitud) formModal.value.solicitud_id = route.query.solicitud
  if (route.query.solicitud) mostrarModal.value           = true
  cargarTipos()
  cargarSesiones()
  cargarSolicitudesSinResolucion()
  cargarResoluciones()
})

// ── Filtrado local reactivo ────────────────────────────────────
const resolucionesFiltradas = computed(() =>
  resoluciones.value.filter(r => {
    const q        = busqueda.value.toLowerCase()
    const coincide = !q || r.folio_solicitud?.toLowerCase().includes(q) || r.solicitante?.toLowerCase().includes(q)
    const sesion   = !filtroSesion.value || String(r.sesion_id) === String(filtroSesion.value)
    const tipo     = !filtroTipo.value   || r.tipo === filtroTipo.value
    return coincide && sesion && tipo
  })
)

// ── Buscar en el backend ───────────────────────────────────────
const filtrar = async () => {
  cargando.value   = true
  errorCarga.value = false
  try {
    const params = new URLSearchParams()
    if (busqueda.value)     params.append('q',         busqueda.value)
    if (filtroSesion.value) params.append('sesion_id', filtroSesion.value)
    if (filtroTipo.value)   params.append('tipo',      filtroTipo.value)
    const url = `${API}/comite/resoluciones` + (params.toString() ? '?' + params : '')
    const res = await fetch(url)
    if (!res.ok) throw new Error()
    const data = await res.json()
    resoluciones.value = Array.isArray(data) ? data : data.data ?? []
  } catch (error) {
    console.error('Error filtrando resoluciones:', error)
    errorCarga.value = true
    mostrarNotificacion('Error al filtrar resoluciones.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Modal ─────────────────────────────────────────────────────
const abrirModalNueva = () => {
  formModal.value    = { sesion_id: '', solicitud_id: '', decision: '' }
  errModal.value     = { sesion_id: '', solicitud_id: '', decision: '' }
  mostrarModal.value = true
}
const cerrarModal = () => { mostrarModal.value = false }

// ── Guardar resolución ────────────────────────────────────────
const guardarResolucion = async () => {
  errModal.value = { sesion_id: '', solicitud_id: '', decision: '' }
  if (!formModal.value.sesion_id)       errModal.value.sesion_id    = 'Selecciona una sesión'
  if (!formModal.value.solicitud_id)    errModal.value.solicitud_id = 'Selecciona una solicitud'
  if (!formModal.value.decision.trim()) errModal.value.decision     = 'La decisión es requerida'
  if (Object.values(errModal.value).some(e => e)) return

  cargando.value = true
  try {
    // POST /api/comite/resoluciones
    // Corregido: el back espera id_sesion e id_solicitud
    const res = await fetch(`${API}/comite/resoluciones`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        id_sesion:    Number(formModal.value.sesion_id),
        id_solicitud: Number(formModal.value.solicitud_id),
        decision:     formModal.value.decision.trim(),
      }),
    })
    if (!res.ok) {
      const err = await res.json().catch(() => ({}))
      throw new Error(err.message || 'Error del servidor')
    }
    mostrarNotificacion('Resolución registrada correctamente')
    cerrarModal()
    // Recargar listas para reflejar el nuevo estado
    await Promise.all([cargarResoluciones(), cargarSolicitudesSinResolucion()])
  } catch (error) {
    console.error('Error guardando resolución:', error)
    mostrarNotificacion(error.message || 'No se pudo registrar la resolución.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Helpers ───────────────────────────────────────────────────
const formatearFechaCorta = (f) => {
  if (!f) return '—'
  const [a, m, d] = f.split('-')
  const meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic']
  return `${parseInt(d)} ${meses[parseInt(m) - 1]} ${a}`
}
const iniciales = (n) => n ? n.split(' ').slice(0, 2).map(x => x[0]).join('').toUpperCase() : '?'
const verDetalle = (res) => router.push(`/comite/resoluciones/${res.id}`)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.resoluciones-page { width: 100%; font-family: 'Montserrat', sans-serif; padding-bottom: 2rem; }
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
.btn-primario { background: #1B396A; color: #FFFFFF; border: none; padding: 10px 18px; border-radius: 10px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 6px; cursor: pointer; font-family: inherit; transition: background 0.2s; }
.btn-primario:hover { background: #1D4ED8; }

/* FILTROS */
.filtros-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 0.9rem 1.4rem; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.filtros-titulo { display: flex; align-items: center; gap: 5px; font-size: 0.82rem; font-weight: 700; color: #6B7280; white-space: nowrap; }
.busqueda-wrap { display: flex; align-items: center; gap: 8px; background: #F5F5F5; border: 1px solid #E5E7EB; border-radius: 8px; padding: 0 12px; flex: 1; min-width: 200px; }
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

/* TABLA */
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
.persona-avatar { width: 30px; height: 30px; background: #DBEAFE; color: #1B396A; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.72rem; flex-shrink: 0; }
.texto-principal  { font-weight: 600; color: #1A1A1A; font-size: 0.875rem; }
.texto-secundario { color: #6B7280; font-size: 0.875rem; }
.sesion-texto { white-space: nowrap; }
.decision-texto {
  font-size: 0.82rem; color: #1A1A1A; margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  max-width: 280px;
}

.btn-accion { padding: 5px 10px; border-radius: 7px; border: none; font-size: 0.78rem; font-weight: 700; display: flex; align-items: center; gap: 5px; cursor: pointer; font-family: inherit; transition: background 0.2s; }
.btn-accion.ver { background: #DBEAFE; color: #1B396A; }

.sin-resultados { padding: 3rem; text-align: center; color: #9CA3AF; font-size: 0.875rem; display: flex; flex-direction: column; align-items: center; gap: 0.75rem; }
.sin-resultados p { margin: 0; }

/* MODAL */
.modal-fondo { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 2000; backdrop-filter: blur(3px); }
.modal-caja  { background: #FFFFFF; width: 520px; border-radius: 16px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,0.25); max-height: 90vh; overflow-y: auto; }
.modal-cabecera { background: #1B396A; color: #FFFFFF; padding: 1.1rem 1.6rem; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; }
.modal-cabecera h3 { margin: 0; font-size: 1.1rem; font-weight: 800; }
.btn-cerrar-modal { background: none; border: none; color: rgba(255,255,255,0.8); cursor: pointer; display: flex; align-items: center; }
.modal-cuerpo { padding: 1.6rem; display: flex; flex-direction: column; gap: 1.2rem; }
.campo-form { display: flex; flex-direction: column; gap: 6px; }
.campo-label { font-size: 0.82rem; font-weight: 700; color: #1A1A1A; display: flex; align-items: center; gap: 6px; }
.requerido { color: #DC2626; }
.campo-nota { font-size: 0.75rem; font-weight: 400; color: #6B7280; }
.campo-nota-aviso { font-size: 0.78rem; color: #F59E0B; font-weight: 600; }
.campo-input { padding: 10px 14px; border: 1.5px solid #E5E7EB; border-radius: 8px; font-size: 0.875rem; font-family: inherit; color: #1A1A1A; outline: none; background: #FFFFFF; transition: border-color 0.2s; width: 100%; box-sizing: border-box; }
.campo-input:focus { border-color: #1B396A; background: #DBEAFE; }
.campo-input.campo-error { border-color: #DC2626; }
.campo-textarea { resize: vertical; min-height: 100px; }
.mensaje-error { font-size: 0.78rem; color: #DC2626; font-weight: 500; }
.modal-pie { padding: 1rem 1.6rem; background: #F5F5F5; border-top: 1px solid #E5E7EB; display: flex; justify-content: flex-end; gap: 0.75rem; position: sticky; bottom: 0; }
.btn-cancelar { background: #FFFFFF; color: #6B7280; border: 1px solid #E5E7EB; padding: 9px 1.2rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; cursor: pointer; font-family: inherit; }
.btn-guardar { background: #1B396A; color: #FFFFFF; border: none; padding: 9px 1.4rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 6px; cursor: pointer; font-family: inherit; transition: background 0.2s; }
.btn-guardar:hover:not(:disabled) { background: #1D4ED8; }
.btn-guardar:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }
.spinner { width: 14px; height: 14px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.3); border-top-color: #FFFFFF; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.toast { position: fixed; bottom: 2rem; right: 2rem; padding: 0.9rem 1.4rem; border-radius: 10px; font-weight: 700; font-size: 0.9rem; font-family: 'Montserrat', sans-serif; display: flex; align-items: center; gap: 0.6rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 3000; }
.toast.exito { background: #1B396A; color: #FFFFFF; }
.toast.error { background: #DC2626; color: #FFFFFF; }
.toast-icono { display: flex; align-items: center; }
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.toast-slide-enter-active { transition: all 0.3s ease; }
.toast-slide-leave-active { transition: all 0.25s ease; }
.toast-slide-enter-from   { transform: translateY(20px); opacity: 0; }
.toast-slide-leave-to     { transform: translateX(100%); opacity: 0; }
.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem; }
</style>