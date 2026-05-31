<!-- ============================================= -->
<!-- src/views/FormularioEventoView.vue           -->
<!-- Módulo: Eventos — Crear / Editar evento      -->
<!-- Rediseño visual SaaS moderno                 -->
<!-- ============================================= -->
<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="formulario-evento-page">
      <div class="barra-carga" :class="{ activa: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- Breadcrumb -->
      <div class="breadcrumb">
        <router-link to="/dashboard" class="breadcrumb-link">INICIO</router-link>
        <span class="sep">›</span>
        <router-link to="/eventos" class="breadcrumb-link">EVENTOS</router-link>
        <span class="sep">›</span>
        <span class="activo">{{ modoEdicion ? 'EDITAR EVENTO' : 'NUEVO EVENTO' }}</span>
      </div>

      <!-- Encabezado -->
      <div class="encabezado-seccion">
        <div class="encabezado-icono-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="#1D52B7" stroke-width="2" width="24" height="24">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
        </div>
        <div>
          <h1 class="titulo-pagina">{{ modoEdicion ? 'EDITAR EVENTO' : 'NUEVO EVENTO' }}</h1>
          <p class="subtitulo">{{ modoEdicion ? 'MODIFICA LA INFORMACIÓN DEL EVENTO' : 'COMPLETA LA INFORMACIÓN PARA REGISTRAR UN NUEVO EVENTO' }}</p>
        </div>
      </div>

      <form @submit.prevent="guardar" novalidate>
        <div class="seccion-card">
          <!-- Cabecera de sección con acento visual -->
          <div class="seccion-titulo">
            <div class="seccion-icono">
              <svg viewBox="0 0 24 24" fill="none" stroke="#1D52B7" stroke-width="2" width="20" height="20">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
            </div>
            <div>
              <h2 class="seccion-nombre">INFORMACIÓN DEL EVENTO</h2>
              <p class="seccion-desc">DATOS GENERALES DEL EVENTO INSTITUCIONAL</p>
            </div>
          </div>
          <div class="divisor"></div>

          <div class="campos-grid">
            <!-- Nombre -->
            <div class="campo-form campo-ancho">
              <label class="campo-label">NOMBRE DEL EVENTO <span class="requerido">*</span></label>
              <input v-model="form.nombre_evento" type="text" placeholder="EJ: SEMANA DE INGENIERÍA 2026" class="campo-input" :class="{ 'campo-error': errores.nombre_evento }" @input="validarCampo('nombre_evento')" />
              <span v-if="errores.nombre_evento" class="mensaje-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ errores.nombre_evento }}
              </span>
            </div>

            <!-- Tipo -->
            <div class="campo-form">
              <label class="campo-label">TIPO DE EVENTO <span class="requerido">*</span></label>
              <div class="select-wrap">
                <select v-model="form.id_tipo_evento" class="campo-input campo-select" :class="{ 'campo-error': errores.id_tipo_evento }" @change="validarCampo('id_tipo_evento')">
                  <option value="">SELECCIONA UN TIPO</option>
                  <option v-for="t in tiposEvento" :key="t.id_tipo_evento" :value="t.id_tipo_evento">{{ t.nombre_tipo }}</option>
                </select>
                <svg class="select-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="6 9 12 15 18 9"/></svg>
              </div>
              <span v-if="errores.id_tipo_evento" class="mensaje-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ errores.id_tipo_evento }}
              </span>
            </div>

            <!-- Fecha -->
            <div class="campo-form">
              <label class="campo-label">FECHA DEL EVENTO <span class="requerido">*</span></label>
              <div class="campo-fecha-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-campo">
                  <rect x="3" y="4" width="18" height="18" rx="2"/>
                  <line x1="16" y1="2" x2="16" y2="6"/>
                  <line x1="8" y1="2" x2="8" y2="6"/>
                  <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <input v-model="form.fecha" type="date" :min="fechaMinima" class="campo-input campo-input-fecha" :class="{ 'campo-error': errores.fecha }" @change="validarCampo('fecha')" />
              </div>
              <span v-if="errores.fecha" class="mensaje-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ errores.fecha }}
              </span>
            </div>

            <!-- Hora Inicio -->
            <div class="campo-form">
              <label class="campo-label">HORA DE INICIO <span class="requerido">*</span></label>
              <div class="campo-hora-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-campo">
                  <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                <input v-model="form.hora_inicio" type="time" class="campo-input campo-input-hora" :class="{ 'campo-error': errores.hora_inicio }" @change="validarCampo('hora_inicio')" />
              </div>
              <span v-if="errores.hora_inicio" class="mensaje-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ errores.hora_inicio }}
              </span>
            </div>

            <!-- Hora Fin -->
            <div class="campo-form">
              <label class="campo-label">HORA DE FIN <span class="requerido">*</span></label>
              <div class="campo-hora-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-campo">
                  <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                <input v-model="form.hora_fin" type="time" class="campo-input campo-input-hora" :class="{ 'campo-error': errores.hora_fin }" @change="validarCampo('hora_fin')" />
              </div>
              <span v-if="errores.hora_fin" class="mensaje-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ errores.hora_fin }}
              </span>
            </div>

            <!-- Lugar -->
            <div class="campo-form campo-ancho">
              <label class="campo-label">LUGAR <span class="requerido">*</span></label>
              <div class="campo-icono-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-campo">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                  <circle cx="12" cy="10" r="3"/>
                </svg>
                <input v-model="form.lugar" type="text" placeholder="EJ: AUDITORIO PRINCIPAL, SALA DE USOS MÚLTIPLES..." class="campo-input campo-input-icon" :class="{ 'campo-error': errores.lugar }" @input="validarCampo('lugar')" />
              </div>
              <span v-if="errores.lugar" class="mensaje-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ errores.lugar }}
              </span>
            </div>

            <!-- Cupo -->
            <div class="campo-form">
              <label class="campo-label">CUPO MÁXIMO <span class="requerido">*</span></label>
              <div class="campo-icono-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-campo">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                  <circle cx="9" cy="7" r="4"/>
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <input v-model.number="form.cupo" type="number" min="1" placeholder="EJ: 100" class="campo-input campo-input-icon" :class="{ 'campo-error': errores.cupo }" @input="validarCampo('cupo')" />
              </div>
              <span v-if="errores.cupo" class="mensaje-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ errores.cupo }}
              </span>
            </div>

            <!-- Responsable -->
            <div class="campo-form">
              <label class="campo-label">RESPONSABLE <span class="requerido">*</span></label>
              <div class="campo-icono-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-campo">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
                <input v-model="form.responsable" type="text" placeholder="NOMBRE DEL RESPONSABLE DEL EVENTO" class="campo-input campo-input-icon" :class="{ 'campo-error': errores.responsable }" @input="validarCampo('responsable')" />
              </div>
              <span v-if="errores.responsable" class="mensaje-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ errores.responsable }}
              </span>
            </div>

            <!-- Constancia -->
            <div class="campo-form campo-ancho">
              <label class="campo-label">GENERA CONSTANCIA</label>
              <div class="campo-toggle-card" :class="{ 'toggle-activo': form.genera_constancia }">
                <div class="toggle-card-left">
                  <div class="toggle-card-icono" :class="{ 'icono-activo': form.genera_constancia }">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                      <polyline points="14 2 14 8 20 8"/>
                      <line x1="16" y1="13" x2="8" y2="13"/>
                      <line x1="16" y1="17" x2="8" y2="17"/>
                      <polyline points="10 9 9 9 8 9"/>
                    </svg>
                  </div>
                  <div>
                    <p class="toggle-card-titulo">CONSTANCIA DE PARTICIPACIÓN</p>
                    <p class="toggle-card-desc">{{ form.genera_constancia ? 'SE GENERARÁ CONSTANCIA PARA LOS PARTICIPANTES' : 'NO SE GENERARÁ CONSTANCIA DE PARTICIPACIÓN' }}</p>
                  </div>
                </div>
                <label class="toggle">
                  <input v-model="form.genera_constancia" type="checkbox" />
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>

            <!-- Descripción -->
            <div class="campo-form campo-ancho">
              <label class="campo-label">DESCRIPCIÓN</label>
              <textarea v-model="form.descripcion" rows="4" placeholder="DESCRIBE BREVEMENTE EL OBJETIVO O CONTENIDO DEL EVENTO..." class="campo-input campo-textarea"></textarea>
              <span class="campo-hint">OPCIONAL · MÁX. RECOMENDADO 300 CARACTERES</span>
            </div>
          </div>
        </div>

        <!-- Barra de acciones sticky -->
        <div class="acciones-form">
          <button type="button" @click="router.push('/eventos')" class="btn-cancelar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
            CANCELAR
          </button>
          <button type="submit" class="btn-primario" :disabled="cargando">
            <span v-if="cargando" class="spinner"></span>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="17" height="17">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
              <polyline points="17 21 17 13 7 13 7 21"/>
              <polyline points="7 3 7 8 15 8"/>
            </svg>
            {{ cargando ? 'GUARDANDO...' : (modoEdicion ? 'ACTUALIZAR EVENTO' : 'GUARDAR EVENTO') }}
          </button>
        </div>
      </form>

      <footer class="pie-pagina">© 2026 TECNOLÓGICO NACIONAL DE MÉXICO · TODOS LOS DERECHOS RESERVADOS</footer>
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

// Token de autenticación
const token = localStorage.getItem('auth_token')
const headers = { 'Content-Type': 'application/json', ...(token && { 'Authorization': `Bearer ${token}` }) }
const headersGet = token ? { 'Authorization': `Bearer ${token}` } : {}

const modoEdicion = computed(() => !!route.params.id)
const cargando = ref(false)
const tiposEvento = ref([])
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerToast = null

const mostrarToast = (m, t = 'exito') => {
  if (timerToast) clearTimeout(timerToast)
  toast.value = { visible: true, mensaje: m, tipo: t }
  timerToast = setTimeout(() => toast.value.visible = false, 3500)
}

const fechaMinima = computed(() => new Date().toISOString().split('T')[0])
const form = ref({
  nombre_evento:     '',
  id_tipo_evento:    '',
  fecha:             '',
  hora_inicio:       '',
  hora_fin:          '',
  lugar:             '',
  cupo:              '',
  responsable:       '',
  genera_constancia: false,
  descripcion:       '',
})
const errores = ref({
  nombre_evento:  '',
  id_tipo_evento: '',
  fecha:          '',
  hora_inicio:    '',
  hora_fin:       '',
  lugar:          '',
  cupo:           '',
  responsable:    '',
})

const cargarTipos = async () => {
  try {
    const r = await fetch(`${API}/tipos-evento`, { headers: headersGet })
    if (!r.ok) throw new Error()
    tiposEvento.value = await r.json()
  } catch {
    mostrarToast('No se pudieron cargar los tipos de evento.', 'error')
  }
}

const cargarEvento = async () => {
  cargando.value = true
  try {
    const r = await fetch(`${API}/eventos/${route.params.id}`, { headers: headersGet })
    if (!r.ok) throw new Error()
    const d = await r.json()
    form.value = {
      nombre_evento:     d.nombre_evento,
      id_tipo_evento:    d.id_tipo_evento,
      fecha:             d.fecha,
      hora_inicio:       d.hora_inicio    || '',
      hora_fin:          d.hora_fin       || '',
      lugar:             d.lugar          || '',
      cupo:              d.cupo           || '',
      responsable:       d.responsable    || '',
      genera_constancia: !!d.genera_constancia,
      descripcion:       d.descripcion    || '',
    }
  } catch (e) {
    console.error(e)
    mostrarToast('No se pudo cargar el evento', 'error')
  } finally {
    cargando.value = false
  }
}

onMounted(() => {
  cargarTipos()
  if (modoEdicion.value) cargarEvento()
})

const validarCampo = (c) => {
  errores.value[c] = ''
  if (c === 'nombre_evento'  && !form.value.nombre_evento.trim())  errores.value.nombre_evento  = 'Requerido'
  if (c === 'id_tipo_evento' && !form.value.id_tipo_evento)        errores.value.id_tipo_evento = 'Selecciona un tipo'
  if (c === 'fecha') {
    if (!form.value.fecha)                               errores.value.fecha = 'Requerida'
    else if (form.value.fecha < fechaMinima.value)       errores.value.fecha = 'La fecha no puede ser pasada'
  }
  if (c === 'hora_inicio' && !form.value.hora_inicio)   errores.value.hora_inicio  = 'Requerida'
  if (c === 'hora_fin') {
    if (!form.value.hora_fin)                            errores.value.hora_fin = 'Requerida'
    else if (form.value.hora_inicio && form.value.hora_fin <= form.value.hora_inicio)
                                                         errores.value.hora_fin = 'Debe ser después de la hora de inicio'
  }
  if (c === 'lugar'       && !form.value.lugar.trim())   errores.value.lugar       = 'Requerido'
  if (c === 'cupo'        && (!form.value.cupo || form.value.cupo < 1)) errores.value.cupo = 'Debe ser mayor a 0'
  if (c === 'responsable' && !form.value.responsable.trim()) errores.value.responsable = 'Requerido'
}

const validarTodo = () => {
  ['nombre_evento', 'id_tipo_evento', 'fecha', 'hora_inicio', 'hora_fin', 'lugar', 'cupo', 'responsable'].forEach(validarCampo)
  return !Object.values(errores.value).some(Boolean)
}

const guardar = async () => {
  if (!validarTodo()) return mostrarToast('Revisa los campos marcados', 'error')
  cargando.value = true
  try {
    const url = modoEdicion.value ? `${API}/eventos/${route.params.id}` : `${API}/eventos`
    const method = modoEdicion.value ? 'PUT' : 'POST'
    const payload = {
      nombre:            form.value.nombre_evento.trim(),
      tipo_evento_id:    Number(form.value.id_tipo_evento),
      fecha:             form.value.fecha,
      hora_inicio:       form.value.hora_inicio,
      hora_fin:          form.value.hora_fin,
      lugar:             form.value.lugar.trim(),
      cupo:              Number(form.value.cupo),
      responsable:       form.value.responsable.trim(),
      genera_constancia: form.value.genera_constancia ? 1 : 0,
      descripcion:       form.value.descripcion.trim() || null,
    }
    const res = await fetch(url, { method, headers, body: JSON.stringify(payload) })
    if (!res.ok) throw new Error((await res.json()).message || 'Error del servidor')
    mostrarToast(modoEdicion.value ? 'Evento actualizado' : 'Evento creado')
    setTimeout(() => router.push('/eventos'), 800)
  } catch (e) {
    mostrarToast(e.message, 'error')
  } finally {
    cargando.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap');

/* ─────────────────────────────────────────────── */
/* BASE                                            */
/* ─────────────────────────────────────────────── */
.formulario-evento-page {
  width: 100%;
  font-family: 'Montserrat', sans-serif;
  padding-bottom: 3rem;
  background: #F4F6F9;
  min-height: 100vh;
}

/* Barra de carga */
.barra-carga { position: fixed; top: 74px; left: 0; right: 0; height: 3px; z-index: 1001; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: linear-gradient(90deg, #0B2545, #1D52B7, #2F80ED, #1D52B7, #0B2545); background-size: 200% 100%; animation: carga-anim 1.4s linear infinite; }
@keyframes carga-anim { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* Breadcrumb */
.breadcrumb { color: #6B7280; font-size: 0.875rem; margin-bottom: 0.85rem; display: flex; align-items: center; gap: 0.4rem; }
.breadcrumb .sep { color: #C8D0DC; }
.breadcrumb .activo { color: #1D52B7; font-weight: 600; }
.breadcrumb-link { color: #6B7280; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #1D52B7; }

/* ─────────────────────────────────────────────── */
/* ENCABEZADO                                      */
/* ─────────────────────────────────────────────── */
.encabezado-seccion {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
}
.encabezado-icono-wrap {
  width: 52px;
  height: 52px;
  background: linear-gradient(135deg, rgba(29,82,183,.1) 0%, rgba(47,128,237,.08) 100%);
  border: 1.5px solid rgba(29,82,183,.15);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.titulo-pagina {
  color: #0B2545;
  font-size: 1.85rem;
  font-weight: 800;
  margin: 0 0 0.25rem;
  letter-spacing: -0.02em;
}
.subtitulo { color: #6B7280; font-size: 0.9rem; margin: 0; font-weight: 500; }

/* ─────────────────────────────────────────────── */
/* CARD DE SECCIÓN                                 */
/* ─────────────────────────────────────────────── */
.seccion-card {
  background: #FFFFFF;
  border-radius: 20px;
  border: 1px solid rgba(29,82,183,.08);
  box-shadow: 0 10px 30px rgba(29,82,183,.07);
  padding: 2rem;
  margin-bottom: 1.5rem;
}

.seccion-titulo {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  margin-bottom: 1.25rem;
}
.seccion-icono {
  width: 44px;
  height: 44px;
  background: rgba(29,82,183,.08);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border: 1px solid rgba(29,82,183,.12);
}
.seccion-nombre { font-size: 1.05rem; font-weight: 700; color: #0B2545; margin: 0 0 3px; letter-spacing: -0.01em; }
.seccion-desc { font-size: 0.82rem; color: #9CA3AF; margin: 0; font-weight: 500; }
.divisor {
  height: 1px;
  background: linear-gradient(90deg, rgba(29,82,183,.15) 0%, transparent 100%);
  margin-bottom: 1.75rem;
}

/* ─────────────────────────────────────────────── */
/* CAMPOS DE FORMULARIO                            */
/* ─────────────────────────────────────────────── */
.campos-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.4rem; }
.campo-form { display: flex; flex-direction: column; gap: 7px; }
.campo-ancho { grid-column: 1 / -1; }

.campo-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: #374151;
  letter-spacing: 0.01em;
}
.requerido { color: #EB5757; margin-left: 1px; }

.campo-input {
  padding: 11px 14px;
  border: 1.5px solid #E4E9F0;
  border-radius: 10px;
  font-size: 0.875rem;
  font-family: inherit;
  color: #0B2545;
  outline: none;
  background: #FAFBFC;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
  font-weight: 500;
  width: 100%;
  box-sizing: border-box;
}
.campo-input:focus {
  border-color: #2F80ED;
  background: #FFFFFF;
  box-shadow: 0 0 0 4px rgba(47,128,237,.12);
}
.campo-input::placeholder { color: #9CA3AF; font-weight: 400; }
.campo-input.campo-error {
  border-color: #EB5757;
  background: #FFF8F8;
}
.campo-input.campo-error:focus {
  box-shadow: 0 0 0 4px rgba(235,87,87,.10);
}
.campo-textarea {
  resize: vertical;
  min-height: 100px;
  line-height: 1.6;
}
.campo-hint { font-size: 0.75rem; color: #9CA3AF; font-weight: 500; }

/* Select personalizado */
.select-wrap { position: relative; }
.campo-select { appearance: none; padding-right: 40px; }
.select-icono { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #6B7280; pointer-events: none; }

/* Campos con ícono interior */
.campo-fecha-wrap,
.campo-hora-wrap,
.campo-icono-wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.icono-campo { position: absolute; left: 13px; color: #9CA3AF; pointer-events: none; flex-shrink: 0; }
.campo-input-fecha,
.campo-input-hora,
.campo-input-icon { padding-left: 40px; }

/* Mensaje de error */
.mensaje-error {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.76rem;
  color: #EB5757;
  font-weight: 600;
}

/* ─────────────────────────────────────────────── */
/* TOGGLE CARD — Genera Constancia                 */
/* ─────────────────────────────────────────────── */
.campo-toggle-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #F8FAFC;
  border: 1.5px solid #E4E9F0;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  gap: 1rem;
  transition: border-color 0.2s, background 0.2s;
  cursor: pointer;
}
.campo-toggle-card.toggle-activo {
  background: rgba(29,82,183,.04);
  border-color: rgba(29,82,183,.25);
}
.toggle-card-left { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; }
.toggle-card-icono {
  width: 40px;
  height: 40px;
  background: #EEF1F6;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: #6B7280;
  transition: background 0.2s, color 0.2s;
}
.toggle-card-icono.icono-activo { background: rgba(29,82,183,.12); color: #1D52B7; }
.toggle-card-titulo { font-size: 0.875rem; font-weight: 700; color: #0B2545; margin: 0 0 2px; }
.toggle-card-desc { font-size: 0.78rem; color: #9CA3AF; margin: 0; font-weight: 500; }

/* Toggle switch */
.toggle { position: relative; display: inline-block; width: 46px; height: 26px; flex-shrink: 0; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-slider {
  position: absolute;
  inset: 0;
  background: #D1D5DB;
  border-radius: 26px;
  cursor: pointer;
  transition: background 0.25s;
}
.toggle-slider::before {
  content: '';
  position: absolute;
  width: 20px;
  height: 20px;
  left: 3px;
  bottom: 3px;
  background: #FFFFFF;
  border-radius: 50%;
  transition: transform 0.25s;
  box-shadow: 0 2px 5px rgba(0,0,0,.15);
}
.toggle input:checked + .toggle-slider { background: #1D52B7; }
.toggle input:checked + .toggle-slider::before { transform: translateX(20px); }

/* ─────────────────────────────────────────────── */
/* BARRA DE ACCIONES                               */
/* ─────────────────────────────────────────────── */
.acciones-form {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 0.75rem;
  background: #FFFFFF;
  border: 1px solid rgba(29,82,183,.08);
  border-radius: 14px;
  padding: 1rem 1.5rem;
  box-shadow: 0 4px 16px rgba(29,82,183,.06);
}

/* ─────────────────────────────────────────────── */
/* BOTONES                                         */
/* ─────────────────────────────────────────────── */
.btn-primario {
  background: linear-gradient(135deg, #0B2545, #1D52B7);
  color: #FFFFFF;
  border: none;
  padding: 11px 22px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 7px;
  cursor: pointer;
  font-family: inherit;
  transition: opacity 0.2s, box-shadow 0.2s;
  white-space: nowrap;
  box-shadow: 0 4px 14px rgba(29,82,183,.28);
}
.btn-primario:hover:not(:disabled) {
  opacity: 0.92;
  box-shadow: 0 6px 20px rgba(29,82,183,.38);
}
.btn-primario:disabled { background: #D1D5DB; color: #9CA3AF; cursor: not-allowed; box-shadow: none; }

.btn-cancelar {
  background: #FFFFFF;
  color: #4B5563;
  border: 1.5px solid #E4E9F0;
  padding: 11px 20px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.2s, border-color 0.2s;
  white-space: nowrap;
}
.btn-cancelar:hover { background: #F4F6F9; border-color: #C8D3E8; }

.spinner {
  width: 16px; height: 16px; border-radius: 50%;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #FFFFFF;
  animation: spin 0.7s linear infinite;
  flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ─────────────────────────────────────────────── */
/* TOAST                                           */
/* ─────────────────────────────────────────────── */
.toast {
  position: fixed; bottom: 2rem; right: 2rem;
  padding: 0.9rem 1.4rem;
  border-radius: 12px;
  font-weight: 700; font-size: 0.9rem;
  font-family: 'Montserrat', sans-serif;
  display: flex; align-items: center; gap: 0.6rem;
  box-shadow: 0 12px 30px rgba(0,0,0,.18);
  z-index: 3000; max-width: 380px; color: #FFFFFF;
}
.toast.exito { background: linear-gradient(135deg, #0B2545, #1A4184); }
.toast.error { background: #EB5757; }
.toast.info  { background: #2563EB; }
.toast-slide-enter-active { transition: all 0.3s ease; }
.toast-slide-leave-active { transition: all 0.25s ease; }
.toast-slide-enter-from { transform: translateY(20px); opacity: 0; }
.toast-slide-leave-to   { transform: translateX(100%); opacity: 0; }

/* Pie */
.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem; }

/* ─────────────────────────────────────────────── */
/* RESPONSIVE                                      */
/* ─────────────────────────────────────────────── */
@media (max-width: 640px) {
  .campos-grid { grid-template-columns: 1fr; gap: 1.1rem; }
  .seccion-card { padding: 1.25rem; border-radius: 14px; }
  .encabezado-seccion { gap: 0.75rem; }
  .titulo-pagina { font-size: 1.5rem; }
  .acciones-form { flex-direction: column; align-items: stretch; }
  .btn-primario, .btn-cancelar { width: 100%; justify-content: center; }
}
</style>