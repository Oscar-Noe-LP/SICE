<!-- ============================================= -->
<!-- src/views/NuevaSolicitudView.vue             -->
<!-- Módulo: Comité Académico — Nueva Solicitud   -->
<!-- ============================================= -->
<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="nueva-solicitud-page">

      <div class="barra-carga" :class="{ activa: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <div class="breadcrumb">
        <router-link to="/dashboard" class="breadcrumb-link">Inicio</router-link>
        <span class="sep">›</span>
        <router-link to="/comite" class="breadcrumb-link">Comité Académico</router-link>
        <span class="sep">›</span>
        <router-link to="/comite/solicitudes" class="breadcrumb-link">Solicitudes</router-link>
        <span class="sep">›</span>
        <span class="activo">Nueva Solicitud</span>
      </div>

      <div class="encabezado-seccion">
        <div>
          <h1 class="titulo-pagina">Nueva Solicitud</h1>
          <p class="subtitulo">Registra una nueva solicitud para revisión del comité</p>
        </div>
      </div>

      <form @submit.prevent="guardar" novalidate>

        <!-- ── SECCIÓN 1: DATOS DE LA SOLICITUD ── -->
        <div class="seccion-card">
          <div class="seccion-titulo">
            <div class="seccion-icono">
              <svg viewBox="0 0 24 24" fill="none" stroke="#1B396A" stroke-width="2" width="20" height="20">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
              </svg>
            </div>
            <div>
              <h2 class="seccion-nombre">Datos de la Solicitud</h2>
              <p class="seccion-desc">Tipo de solicitud y persona que la presenta</p>
            </div>
          </div>
          <div class="divisor"></div>
          <div class="campos-grid">

            <!-- Tipo de solicitud -->
            <div class="campo-form">
              <label class="campo-label">
                Tipo de Solicitud <span class="requerido">*</span>
              </label>
              <select
                v-model="form.tipo_solicitud_id"
                class="campo-input"
                :class="{ 'campo-error': errores.tipo_solicitud_id }"
                @change="validarCampo('tipo_solicitud_id')"
              >
                <option value="">Selecciona un tipo</option>
                <option v-for="t in tiposSolicitud" :key="t.id" :value="t.id">{{ t.nombre }}</option>
              </select>
              <span v-if="errores.tipo_solicitud_id" class="mensaje-error">{{ errores.tipo_solicitud_id }}</span>
            </div>

            <!-- Buscador de persona solicitante -->
            <div class="campo-form">
              <label class="campo-label">
                Persona Solicitante <span class="requerido">*</span>
              </label>
              <div class="busqueda-persona-wrap" :class="{ activo: busquedaPersona.length > 0 }">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-campo">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                  <circle cx="9" cy="7" r="4"/>
                </svg>
                <input
                  v-model="busquedaPersona"
                  type="text"
                  placeholder="Buscar por nombre o número de control..."
                  class="campo-input-inline"
                  :class="{ 'campo-error': errores.persona_id }"
                  @input="buscarPersona"
                />
              </div>
              <span v-if="errores.persona_id" class="mensaje-error">{{ errores.persona_id }}</span>

              <!-- Persona seleccionada -->
              <div v-if="personaSeleccionada" class="persona-seleccionada">
                <div class="persona-avatar">{{ iniciales(personaSeleccionada.nombre) }}</div>
                <div class="persona-datos">
                  <span class="persona-nombre">{{ personaSeleccionada.nombre }}</span>
                  <span class="persona-info">{{ personaSeleccionada.control }} · {{ personaSeleccionada.carrera }}</span>
                </div>
                <button type="button" @click="personaSeleccionada = null; busquedaPersona = ''" class="btn-quitar">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                  </svg>
                </button>
              </div>

              <!-- Resultados búsqueda -->
              <div v-if="resultadosPersona.length > 0 && !personaSeleccionada" class="resultados-busqueda">
                <div
                  v-for="p in resultadosPersona"
                  :key="p.id"
                  class="resultado-item"
                  @click="seleccionarPersona(p)"
                >
                  <div class="resultado-avatar">{{ iniciales(p.nombre) }}</div>
                  <div>
                    <span class="resultado-nombre">{{ p.nombre }}</span>
                    <span class="resultado-info">{{ p.control }}</span>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- ── SECCIÓN 2: DESCRIPCIÓN ── -->
        <div class="seccion-card">
          <div class="seccion-titulo">
            <div class="seccion-icono">
              <svg viewBox="0 0 24 24" fill="none" stroke="#1B396A" stroke-width="2" width="20" height="20">
                <line x1="17" y1="10" x2="3" y2="10"/>
                <line x1="21" y1="6" x2="3" y2="6"/>
                <line x1="21" y1="14" x2="3" y2="14"/>
                <line x1="17" y1="18" x2="3" y2="18"/>
              </svg>
            </div>
            <div>
              <h2 class="seccion-nombre">Descripción</h2>
              <p class="seccion-desc">Describe los motivos y detalles de la solicitud</p>
            </div>
          </div>
          <div class="divisor"></div>
          <div class="campos-grid">
            <div class="campo-form campo-ancho">
              <label class="campo-label">
                Descripción de la Solicitud <span class="requerido">*</span>
              </label>
              <textarea
                v-model="form.descripcion"
                rows="6"
                placeholder="Describe con detalle los motivos, antecedentes y lo que se solicita al comité..."
                class="campo-input campo-textarea"
                :class="{ 'campo-error': errores.descripcion }"
                @input="validarCampo('descripcion')"
                maxlength="1000"
              ></textarea>
              <!-- Contador de caracteres (mismo estilo de vista previa) -->
              <div class="contador-wrap">
                <span
                  class="contador-chars"
                  :class="{ 'contador-error': form.descripcion.length < 50 && form.descripcion.length > 0 }"
                >
                  {{ form.descripcion.length }} / 1000 caracteres
                  <span v-if="form.descripcion.length < 50 && form.descripcion.length > 0">
                    — mínimo 50 caracteres
                  </span>
                </span>
              </div>
              <span v-if="errores.descripcion" class="mensaje-error">{{ errores.descripcion }}</span>
            </div>
          </div>
        </div>

        <!-- Acciones -->
        <div class="acciones-form">
          <button type="button" @click="router.push('/comite/solicitudes')" class="btn-cancelar">
            Cancelar
          </button>
          <button type="submit" class="btn-guardar" :disabled="cargando">
            <span v-if="cargando" class="spinner"></span>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
              <polyline points="17 21 17 13 7 13 7 21"/>
              <polyline points="7 3 7 8 15 8"/>
            </svg>
            {{ cargando ? 'Registrando...' : 'Registrar Solicitud' }}
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`

const router = useRouter()

//const API_COMITE = 'http://127.0.0.1:8000/api/comite'
//const API_PERSONAS = 'http://127.0.0.1:8000/api/personas'

const cargando = ref(false)
const errorCarga = ref(false)

const busquedaPersona = ref('')
const personaSeleccionada = ref(null)
const resultadosPersona = ref([])
const buscandoPersona = ref(false)

const tiposSolicitud = ref([])

const form = ref({
  id_tipo_solicitud: '',
  tipo_solicitud_id: '',
  id_persona: null,
  descripcion: ''
})

const errores = ref({
  id_tipo_solicitud: '',
  id_persona: '',
  descripcion: ''
})

const toast = ref({
  visible: false,
  mensaje: '',
  tipo: 'exito'
})

let timerNotif = null
const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  toast.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => {
    toast.value.visible = false
  }, 3500)
}

// ── Carga de tipos ────────────────────────────────────────────
const cargarTipos = async () => {
  errorCarga.value = false

  try {
    const res = await fetch(`${API}/tipos-solicitud`)
    if (!res.ok) throw new Error('No se pudieron cargar los tipos')

    const data = await res.json()
    console.log('TIPOS CARGADOS:', data)

    tiposSolicitud.value = Array.isArray(data) ? data : []
    console.log('tiposSolicitud.value:', tiposSolicitud.value)
  } catch (error) {
    console.error('Error cargando tipos de solicitud:', error)
    errorCarga.value = true
    mostrarNotificacion('No se pudieron cargar los tipos de solicitud.', 'error')
  }
}

onMounted(() => {
  cargarTipos()
})

// ── Búsqueda de persona con debounce ─────────────────────────
let debounce = null

const buscarPersona = () => {
  resultadosPersona.value = []

  if (busquedaPersona.value.trim().length < 2) return

  clearTimeout(debounce)

  debounce = setTimeout(async () => {
    buscandoPersona.value = true

    try {
      const res = await fetch(
        `${API_PERSONAS}/buscar?q=${encodeURIComponent(busquedaPersona.value.trim())}`
      )

      if (!res.ok) throw new Error('No se pudieron buscar personas')

      const data = await res.json()
      resultadosPersona.value = Array.isArray(data) ? data : data.data ?? []
    } catch (error) {
      console.error('Error buscando persona:', error)
      resultadosPersona.value = []
    } finally {
      buscandoPersona.value = false
    }
  }, 400)
}

const seleccionarPersona = (p) => {
  personaSeleccionada.value = p
  form.value.id_persona = p.id_persona ?? p.id ?? null
  busquedaPersona.value = ''
  resultadosPersona.value = []
  errores.value.id_persona = ''
}

const limpiarPersonaSeleccionada = () => {
  personaSeleccionada.value = null
  form.value.id_persona = null
  busquedaPersona.value = ''
  resultadosPersona.value = []
}

const nombreCompletoPersona = (p) => {
  if (!p) return ''
  return [p.nombre, p.apellido_paterno, p.apellido_materno]
    .filter(Boolean)
    .join(' ')
    .trim()
}

const iniciales = (nombre) => {
  return nombre
    ? nombre
        .split(' ')
        .slice(0, 2)
        .map(x => x[0])
        .join('')
        .toUpperCase()
    : '?'
}

// ── Validaciones ──────────────────────────────────────────────
const validarCampo = (campo) => {
  errores.value[campo] = ''

  if (campo === 'id_tipo_solicitud') {
    const val = form.value.id_tipo_solicitud || form.value.tipo_solicitud_id
    if (!val) {
      errores.value.id_tipo_solicitud = 'Selecciona un tipo de solicitud'
    }
  }

  if (campo === 'descripcion') {
    if (!form.value.descripcion.trim()) {
      errores.value.descripcion = 'La descripción es requerida'
    } else if (form.value.descripcion.trim().length < 50) {
      errores.value.descripcion = 'La descripción debe tener al menos 50 caracteres'
    }
  }

  if (campo === 'id_persona' && !form.value.id_persona) {
    errores.value.id_persona = 'Selecciona la persona solicitante'
  }
}

const validarTodo = () => {
  console.log('form al guardar:', JSON.stringify(form.value)) // ← agrega esto
  validarCampo('id_tipo_solicitud')
  validarCampo('id_persona')
  validarCampo('descripcion')
  return Object.values(errores.value).every(e => !e)
}

// ── Guardar ───────────────────────────────────────────────────
const guardar = async () => {
  if (!validarTodo()) {
    mostrarNotificacion('Revisa los campos con error', 'error')
    return
  }

  cargando.value = true

  try {
    const payload = {
      id_tipo_solicitud: Number(form.value.id_tipo_solicitud || form.value.tipo_solicitud_id),
      id_persona: Number(form.value.id_persona),
      descripcion: form.value.descripcion.trim()
    }

    const res = await fetch(`${API}/solicitudes`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(payload)
    })

    const data = await res.json().catch(() => ({}))

    if (!res.ok) {
      if (data.errors) {
        errores.value.id_tipo_solicitud = data.errors.id_tipo_solicitud?.[0] ?? ''
        errores.value.id_persona = data.errors.id_persona?.[0] ?? ''
        errores.value.descripcion = data.errors.descripcion?.[0] ?? ''
      }

      throw new Error(data.message || 'Error del servidor')
    }

    mostrarNotificacion('Solicitud registrada correctamente')

    setTimeout(() => {
      router.push('/comite/solicitudes')
    }, 1200)
  } catch (error) {
    console.error('Error guardando solicitud:', error)
    mostrarNotificacion(
      error.message || 'No se pudo registrar la solicitud. Intenta de nuevo.',
      'error'
    )
  } finally {
    cargando.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.nueva-solicitud-page { width: 100%; font-family: 'Montserrat', sans-serif; padding-bottom: 2rem; }
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

.seccion-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.6rem; margin-bottom: 1.5rem; }
.seccion-titulo { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 1rem; }
.seccion-icono { width: 40px; height: 40px; background: #DBEAFE; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.seccion-nombre { font-size: 1rem; font-weight: 700; color: #1A1A1A; margin: 0 0 2px; }
.seccion-desc   { font-size: 0.82rem; color: #6B7280; margin: 0; }
.divisor { height: 1px; background: #E5E7EB; margin-bottom: 1.4rem; }

.campos-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; }
.campo-form { display: flex; flex-direction: column; gap: 6px; position: relative; }
.campo-ancho { grid-column: 1 / -1; }
.campo-label { font-size: 0.82rem; font-weight: 700; color: #1A1A1A; }
.requerido { color: #DC2626; }

.campo-input {
  padding: 10px 14px; border: 1.5px solid #E5E7EB; border-radius: 8px;
  font-size: 0.875rem; font-family: inherit; color: #1A1A1A; outline: none;
  background: #FFFFFF; transition: border-color 0.2s;
}
.campo-input:focus { border-color: #1B396A; background: #DBEAFE; }
.campo-input::placeholder { color: #9CA3AF; }
.campo-input.campo-error { border-color: #DC2626; }
.campo-textarea { resize: vertical; min-height: 130px; }
.mensaje-error { font-size: 0.78rem; color: #DC2626; font-weight: 500; }

/* Buscador persona */
.busqueda-persona-wrap {
  display: flex; align-items: center; gap: 8px;
  border: 1.5px solid #E5E7EB; border-radius: 8px; padding: 0 12px; background: #FFFFFF;
  transition: border-color 0.2s;
}
.busqueda-persona-wrap.activo { border-color: #1B396A; background: #DBEAFE; }
.icono-campo { color: #6B7280; flex-shrink: 0; }
.campo-input-inline {
  border: none; background: transparent; padding: 10px 0;
  font-size: 0.875rem; font-family: inherit; color: #1A1A1A; outline: none; flex: 1;
}
.campo-input-inline::placeholder { color: #9CA3AF; }
.campo-input-inline.campo-error { color: #DC2626; }

.persona-seleccionada {
  display: flex; align-items: center; gap: 10px;
  background: #DCFCE7; border: 1px solid #16A34A; border-radius: 8px; padding: 0.75rem 1rem;
}
.persona-avatar { width: 36px; height: 36px; background: #1B396A; color: #FFFFFF; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.78rem; flex-shrink: 0; }
.persona-nombre { font-weight: 700; color: #1A1A1A; font-size: 0.875rem; display: block; }
.persona-info   { font-size: 0.78rem; color: #6B7280; }
.persona-datos  { flex: 1; }
.btn-quitar { background: none; border: none; color: #6B7280; cursor: pointer; padding: 4px; display: flex; align-items: center; }

.resultados-busqueda {
  position: absolute; top: 100%; left: 0; right: 0; z-index: 100;
  background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 8px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.1); overflow: hidden; margin-top: 4px;
}
.resultado-item {
  display: flex; align-items: center; gap: 10px; padding: 0.75rem 1rem;
  cursor: pointer; border-bottom: 1px solid #F5F5F5; transition: background 0.15s;
}
.resultado-item:last-child { border-bottom: none; }
.resultado-item:hover { background: #DBEAFE; }
.resultado-avatar { width: 30px; height: 30px; background: #DBEAFE; color: #1B396A; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.72rem; flex-shrink: 0; }
.resultado-nombre { font-weight: 700; color: #1A1A1A; font-size: 0.82rem; display: block; }
.resultado-info   { font-size: 0.75rem; color: #6B7280; }

/* Contador caracteres */
.contador-wrap { display: flex; justify-content: flex-end; margin-top: 2px; }
.contador-chars { font-size: 0.78rem; color: #6B7280; }
.contador-error { color: #DC2626; font-weight: 600; }

/* ACCIONES */
.acciones-form { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem; }
.btn-cancelar { background: #FFFFFF; color: #6B7280; border: 1px solid #E5E7EB; padding: 10px 1.4rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; cursor: pointer; font-family: inherit; }
.btn-guardar {
  background: #1B396A; color: #FFFFFF; border: none; padding: 10px 1.6rem; border-radius: 8px;
  font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 8px;
  cursor: pointer; font-family: inherit; transition: background 0.2s;
}
.btn-guardar:hover:not(:disabled) { background: #1D4ED8; }
.btn-guardar:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }
.spinner { width: 16px; height: 16px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.3); border-top-color: #FFFFFF; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.toast { position: fixed; bottom: 2rem; right: 2rem; padding: 0.9rem 1.4rem; border-radius: 10px; font-weight: 700; font-size: 0.9rem; font-family: 'Montserrat', sans-serif; display: flex; align-items: center; gap: 0.6rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 3000; }
.toast.exito { background: #1B396A; color: #FFFFFF; }
.toast.error { background: #DC2626; color: #FFFFFF; }
.toast-icono { display: flex; align-items: center; }
.toast-slide-enter-active { transition: all 0.3s ease; }
.toast-slide-leave-active { transition: all 0.25s ease; }
.toast-slide-enter-from   { transform: translateY(20px); opacity: 0; }
.toast-slide-leave-to     { transform: translateX(100%); opacity: 0; }
.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem; }

@media (max-width: 640px) { .campos-grid { grid-template-columns: 1fr; } }
</style>