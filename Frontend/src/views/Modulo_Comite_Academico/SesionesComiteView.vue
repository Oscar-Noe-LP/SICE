<!-- ============================================= -->
<!-- src/views/SesionesComiteView.vue             -->
<!-- Módulo: Comité Académico — Sesiones          -->
<!-- ============================================= -->
<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="sesiones-page">

      <div class="barra-carga" :class="{ activa: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <div class="breadcrumb">
        <router-link to="/dashboard" class="breadcrumb-link">Inicio</router-link>
        <span class="sep">›</span>
        <router-link to="/comite" class="breadcrumb-link">Comité Académico</router-link>
        <span class="sep">›</span>
        <span class="activo">Sesiones</span>
      </div>

      <div class="encabezado-seccion">
        <div>
          <h1 class="titulo-pagina">Sesiones del Comité</h1>
          <p class="subtitulo">Registro y seguimiento de sesiones del comité académico</p>
        </div>
        <button @click="abrirModalNueva" class="btn-primario">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Nueva Sesión
        </button>
      </div>

      <!-- Tabla -->
      <div class="tabla-card">
        <div class="tabla-encabezado">
          <span class="tabla-contador">{{ sesiones.length }} sesión(es)</span>
        </div>
        <div class="tabla-scroll">
          <table class="tabla-principal">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Descripción</th>
                <th class="centrado">Resoluciones</th>
                <th class="centrado">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="ses in sesiones" :key="ses.id">
                <td>
                  <span class="texto-principal">{{ formatearFechaLarga(ses.fecha) }}</span>
                </td>
                <td class="texto-secundario">{{ ses.descripcion }}</td>
                <td class="centrado">
                  <span
                    class="badge-resoluciones"
                    :style="ses.resoluciones > 0
                      ? { background: '#DCFCE7', color: '#16A34A' }
                      : { background: '#F3F4F6', color: '#6B7280' }"
                  >
                    {{ ses.resoluciones }} resolución(es)
                  </span>
                </td>
                <td class="centrado">
                  <div class="acciones-fila">
                    <button
                      @click="router.push(`/comite/resoluciones?sesion=${ses.id}`)"
                      class="btn-accion ver"
                      title="Ver resoluciones de esta sesión"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                      </svg>
                      Ver resoluciones
                    </button>
                    <button
                      @click="abrirModalEditar(ses)"
                      class="btn-accion editar"
                      title="Editar sesión"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                        <path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                      </svg>
                      Editar
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="sesiones.length === 0">
                <td colspan="4" class="sin-resultados">
                  <svg viewBox="0 0 24 24" fill="none" stroke="#D6D6D6" stroke-width="1.5" width="40" height="40">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/>
                  </svg>
                  <p>Sin sesiones registradas</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>
    </div>
  </MainLayout>

  <!-- Modal nueva / editar sesión -->
  <transition name="modal-fade">
    <div v-if="mostrarModal" class="modal-fondo" @click.self="cerrarModal">
      <div class="modal-caja">
        <div class="modal-cabecera">
          <h3>{{ modoEdicion ? 'Editar Sesión' : 'Nueva Sesión' }}</h3>
          <button @click="cerrarModal" class="btn-cerrar-modal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        <div class="modal-cuerpo">
          <div class="campo-form">
            <label class="campo-label">Fecha de la Sesión <span class="requerido">*</span></label>
            <div class="campo-fecha-wrap">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-campo">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
              <input v-model="formModal.fecha" type="date" class="campo-input campo-input-fecha" :class="{ 'campo-error': errModal.fecha }" />
            </div>
            <span v-if="errModal.fecha" class="mensaje-error">{{ errModal.fecha }}</span>
          </div>
          <div class="campo-form">
            <label class="campo-label">Descripción <span class="requerido">*</span></label>
            <textarea
              v-model="formModal.descripcion"
              rows="3"
              placeholder="Ej: Sesión ordinaria de abril 2026..."
              class="campo-input campo-textarea"
              :class="{ 'campo-error': errModal.descripcion }"
            ></textarea>
            <span v-if="errModal.descripcion" class="mensaje-error">{{ errModal.descripcion }}</span>
          </div>
        </div>
        <div class="modal-pie">
          <button @click="cerrarModal" class="btn-cancelar">Cancelar</button>
          <button @click="guardarSesion" class="btn-guardar" :disabled="cargando">
            <span v-if="cargando" class="spinner"></span>
            {{ cargando ? 'Guardando...' : (modoEdicion ? 'Actualizar' : 'Registrar Sesión') }}
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`

const router = useRouter()

const cargando       = ref(false)
const errorCarga     = ref(false)
const mostrarModal   = ref(false)
const modoEdicion    = ref(false)
const sesionEditando = ref(null)

const sesiones  = ref([])
const formModal = ref({ fecha: '', descripcion: '' })
const errModal  = ref({ fecha: '', descripcion: '' })

// ── Toast (corregido: era "notificacion") ─────────────────────
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null
const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  toast.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { toast.value.visible = false }, 3500)
}

// ── Carga de sesiones ─────────────────────────────────────────
const cargarSesiones = async () => {
  cargando.value   = true
  errorCarga.value = false
  try {
    const res = await fetch(`${API}/comite/sesiones`)
    if (!res.ok) throw new Error('Error en la respuesta del servidor')
    const data = await res.json()
    sesiones.value = Array.isArray(data) ? data : data.data ?? []
  } catch (error) {
    console.error('Error cargando sesiones:', error)
    errorCarga.value = true
    mostrarNotificacion('No se pudieron cargar las sesiones.', 'error')
  } finally {
    cargando.value = false
  }
}

onMounted(() => { cargarSesiones() })

// ── Modal ─────────────────────────────────────────────────────
const abrirModalNueva = () => {
  modoEdicion.value    = false
  sesionEditando.value = null
  formModal.value      = { fecha: '', descripcion: '' }
  errModal.value       = { fecha: '', descripcion: '' }
  mostrarModal.value   = true
}

const abrirModalEditar = (ses) => {
  modoEdicion.value    = true
  sesionEditando.value = ses
  formModal.value      = { fecha: ses.fecha, descripcion: ses.descripcion }
  errModal.value       = { fecha: '', descripcion: '' }
  mostrarModal.value   = true
}

const cerrarModal = () => { mostrarModal.value = false }

// ── Guardar sesión ────────────────────────────────────────────
const guardarSesion = async () => {
  errModal.value = { fecha: '', descripcion: '' }
  if (!formModal.value.fecha)              errModal.value.fecha       = 'La fecha es requerida'
  if (!formModal.value.descripcion.trim()) errModal.value.descripcion = 'La descripción es requerida'
  if (Object.values(errModal.value).some(e => e)) return

  cargando.value = true
  try {
    const payload = {
      fecha:       formModal.value.fecha,
      descripcion: formModal.value.descripcion.trim()
    }

    if (modoEdicion.value && sesionEditando.value) {
      // PUT /api/comite/sesiones/{id}
      const res = await fetch(`${API}/comite/sesiones/${sesionEditando.value.id}`, {
        method:  'PUT',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload),
      })
      if (!res.ok) {
        const data = await res.json().catch(() => ({}))
        throw new Error(data.message || 'Error al actualizar')
      }
      // Actualiza la fila en la tabla sin recargar
      sesionEditando.value.fecha       = payload.fecha
      sesionEditando.value.descripcion = payload.descripcion
      mostrarNotificacion('Sesión actualizada correctamente')

    } else {
      // POST /api/comite/sesiones
      const res = await fetch(`${API}/comite/sesiones`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload),
      })
      if (!res.ok) {
        const data = await res.json().catch(() => ({}))
        throw new Error(data.message || 'Error al registrar')
      }
      const respuesta = await res.json()
      // El back responde { message, data: { id, fecha, descripcion, resoluciones } }
      sesiones.value.unshift({
        id:          respuesta.data?.id          ?? respuesta.id,
        fecha:       respuesta.data?.fecha       ?? payload.fecha,
        descripcion: respuesta.data?.descripcion ?? payload.descripcion,
        resoluciones: 0
      })
      mostrarNotificacion('Sesión registrada correctamente')
    }

    cerrarModal()
  } catch (error) {
    console.error('Error guardando sesión:', error)
    mostrarNotificacion(error.message || 'No se pudo guardar la sesión.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Helpers ───────────────────────────────────────────────────
const formatearFechaLarga = (f) => {
  if (!f) return '—'
  const [a, m, d] = f.split('-')
  const meses = ['enero','febrero','marzo','abril','mayo','junio',
                 'julio','agosto','septiembre','octubre','noviembre','diciembre']
  return `${parseInt(d)} de ${meses[parseInt(m) - 1]} de ${a}`
}
</script>
<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.sesiones-page { width: 100%; font-family: 'Montserrat', sans-serif; padding-bottom: 2rem; }
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
.texto-principal  { font-weight: 600; color: #1A1A1A; font-size: 0.875rem; }
.texto-secundario { color: #6B7280; font-size: 0.875rem; }
.badge-resoluciones { font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }

.acciones-fila { display: flex; gap: 6px; justify-content: center; }
.btn-accion { padding: 5px 10px; border-radius: 7px; border: none; font-size: 0.78rem; font-weight: 700; display: flex; align-items: center; gap: 5px; cursor: pointer; font-family: inherit; transition: background 0.2s; }
.btn-accion.ver    { background: #DBEAFE; color: #1B396A; }
.btn-accion.editar { background: #F5F5F5; color: #6B7280; }

.sin-resultados { padding: 3rem; text-align: center; color: #9CA3AF; font-size: 0.875rem; display: flex; flex-direction: column; align-items: center; gap: 0.75rem; }
.sin-resultados p { margin: 0; }

/* MODAL */
.modal-fondo { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 2000; backdrop-filter: blur(3px); }
.modal-caja  { background: #FFFFFF; width: 480px; border-radius: 16px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,0.25); }
.modal-cabecera { background: #1B396A; color: #FFFFFF; padding: 1.1rem 1.6rem; display: flex; justify-content: space-between; align-items: center; }
.modal-cabecera h3 { margin: 0; font-size: 1.1rem; font-weight: 800; }
.btn-cerrar-modal { background: none; border: none; color: rgba(255,255,255,0.8); cursor: pointer; display: flex; align-items: center; }
.modal-cuerpo { padding: 1.6rem; display: flex; flex-direction: column; gap: 1.2rem; }
.campo-form { display: flex; flex-direction: column; gap: 6px; }
.campo-label { font-size: 0.82rem; font-weight: 700; color: #1A1A1A; }
.requerido { color: #DC2626; }
.campo-fecha-wrap { position: relative; display: flex; align-items: center; }
.icono-campo { position: absolute; left: 12px; color: #6B7280; pointer-events: none; }
.campo-input { padding: 10px 14px; border: 1.5px solid #E5E7EB; border-radius: 8px; font-size: 0.875rem; font-family: inherit; color: #1A1A1A; outline: none; background: #FFFFFF; transition: border-color 0.2s; }
.campo-input:focus { border-color: #1B396A; background: #DBEAFE; }
.campo-input.campo-error { border-color: #DC2626; }
.campo-input-fecha { padding-left: 38px; }
.campo-textarea { resize: vertical; min-height: 80px; }
.mensaje-error { font-size: 0.78rem; color: #DC2626; font-weight: 500; }
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
.toast-icono { display: flex; align-items: center; }
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.toast-slide-enter-active { transition: all 0.3s ease; }
.toast-slide-leave-active { transition: all 0.25s ease; }
.toast-slide-enter-from   { transform: translateY(20px); opacity: 0; }
.toast-slide-leave-to     { transform: translateX(100%); opacity: 0; }
.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem; }
</style>