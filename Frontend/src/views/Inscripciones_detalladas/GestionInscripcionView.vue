<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="gestion-page">

      <!-- ── Breadcrumb ── -->
      <nav class="breadcrumb">
        <router-link to="/inicio"        class="breadcrumb-link">Inicio</router-link>
        <span class="sep">›</span>
        <router-link to="/inscripciones" class="breadcrumb-link">Inscripciones</router-link>
        <span class="sep">›</span>
        <span class="breadcrumb-actual">{{ esEdicion ? 'Gestionar Inscripción' : 'Nueva Inscripción' }}</span>
      </nav>

      <!-- ── Barra de carga ── -->
      <div class="barra-carga-global" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- ── Toast ── -->
      <transition name="notif-fade">
        <div v-if="notificacion.visible" class="notificacion-ui" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo === 'exito'" xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- ── Header ── -->
      <div class="page-header">
        <h1 class="page-title">{{ esEdicion ? 'Gestionar Inscripción' : 'Nueva Inscripción' }}</h1>
      </div>

      <div class="formulario-card">

        <!-- SECCIÓN 1 — Alumno -->
        <div class="seccion">
          <div class="seccion-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="seccion-icono">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>Alumno</span>
          </div>
          <div class="seccion-linea"></div>

          <div class="campo-grupo">
            <label class="campo-label">Número de Control *</label>
            <template v-if="esEdicion">
              <div class="campo-readonly">{{ form.numero_control }}</div>
            </template>
            <template v-else>
              <div class="search-inline">
                <div class="search-wrapper-field">
                  <svg xmlns="http://www.w3.org/2000/svg" class="search-icono-field" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  <input
                    v-model="form.numero_control"
                    type="text"
                    class="campo-input"
                    :class="{ 'input-encontrado': alumnoEncontrado }"
                    placeholder="Ej: 21456887"
                    @keydown.enter="buscarAlumno"
                    :disabled="guardando"
                  >
                </div>
                <button
                  class="btn-buscar"
                  :class="{ 'btn-encontrado': alumnoEncontrado }"
                  @click="buscarAlumno"
                  :disabled="buscandoAlumno || !!alumnoEncontrado"
                >
                  <span v-if="buscandoAlumno" class="spinner-btn"></span>
                  <span v-else-if="alumnoEncontrado">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Alumno encontrado
                  </span>
                  <span v-else>Buscar alumno</span>
                </button>
              </div>
            </template>
          </div>

          <!-- Bloque alumno encontrado -->
          <transition name="slide-down">
            <div v-if="alumnoEncontrado" class="bloque-alumno">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="bloque-icono">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div class="bloque-info">
                <p class="bloque-nombre">{{ alumnoEncontrado.nombre }}</p>
                <p class="bloque-detalle">{{ alumnoEncontrado.carrera }} · Semestre {{ alumnoEncontrado.semestre }} · {{ form.numero_control }}</p>
              </div>
            </div>
          </transition>
        </div>

        <!-- SECCIÓN 2 — Datos de la Inscripción -->
        <div class="seccion">
          <div class="seccion-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="seccion-icono">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span>Datos de la Inscripción</span>
          </div>
          <div class="seccion-linea"></div>

          <div class="campos-dobles">
            <div class="campo-grupo">
              <label class="campo-label">Periodo *</label>
              <select v-model="form.id_periodo" class="campo-select" :disabled="guardando">
                <option value="">Seleccionar periodo...</option>
                <option v-for="p in periodosDB" :key="p.id_periodo" :value="p.id_periodo">
                  {{ p.nombre_periodo }}
                </option>
              </select>
              <p v-if="errores.id_periodo" class="error-campo">{{ errores.id_periodo }}</p>
            </div>

            <div class="campo-grupo">
              <label class="campo-label">Estado</label>
              <select v-model="form.estado" class="campo-select" :disabled="guardando">
                <option value="activa">Activa</option>
                <option value="baja">Baja</option>
                <option value="cambio_pendiente">Cambio pendiente</option>
              </select>
            </div>
          </div>

          <div class="campo-grupo">
            <label class="campo-label">Observaciones</label>
            <textarea v-model="form.observaciones" class="campo-textarea" rows="3"
                      placeholder="Observaciones opcionales..." :disabled="guardando"></textarea>
          </div>
        </div>

        <!-- SECCIÓN 3 — Materias (solo edición) -->
        <div v-if="esEdicion && form.materias && form.materias.length > 0" class="seccion">
          <div class="seccion-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="seccion-icono">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span>Materias Inscritas</span>
          </div>
          <div class="seccion-linea"></div>

          <!-- Alerta si todas tienen baja -->
          <transition name="slide-down">
            <div v-if="todasConBaja" class="alerta-amarilla">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="alerta-icono">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <span>El alumno quedará sin materias activas en este periodo. Confirma que es correcto antes de guardar.</span>
            </div>
          </transition>

          <div class="tabla-materias-wrapper">
            <table class="tabla-materias">
              <thead>
                <tr>
                  <th>Materia</th>
                  <th>Grupo actual</th>
                  <th>Acción</th>
                  <th>Detalle</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(mat, idx) in form.materias" :key="mat.id_detalle" class="materia-fila">
                  <td>
                    <p class="materia-nombre">{{ mat.nombre_materia }}</p>
                    <p class="materia-clave">{{ mat.clave_materia }}</p>
                  </td>
                  <td class="td-gris">{{ mat.grupo }}</td>
                  <td>
                    <select class="select-accion" v-model="mat.accion" @change="mat.id_grupo_nuevo = ''; mat.motivo_baja = ''" :disabled="guardando">
                      <option value="sin_cambio">Sin cambio</option>
                      <option value="cambio_grupo">Cambio de grupo</option>
                      <option value="baja">Baja de materia</option>
                    </select>
                  </td>
                  <td>
                    <transition name="slide-down">
                      <div v-if="mat.accion === 'cambio_grupo'" class="detalle-accion">
                        <select class="select-grupo" v-model="mat.id_grupo_nuevo" :disabled="guardando">
                          <option value="">Seleccionar grupo disponible...</option>
                          <option v-for="g in gruposDisponibles" :key="g.id_grupo" :value="g.id_grupo">
                            {{ g.nombre_grupo }} — {{ g.horario }} ({{ g.cupo_disponible }} lugares)
                          </option>
                        </select>
                        <p v-if="intentoGuardar && mat.accion === 'cambio_grupo' && !mat.id_grupo_nuevo" class="error-campo">Selecciona el nuevo grupo.</p>
                      </div>
                      <div v-else-if="mat.accion === 'baja'" class="detalle-accion">
                        <textarea class="textarea-motivo" v-model="mat.motivo_baja" rows="2"
                                  placeholder="Motivo de baja (requerido)..." :disabled="guardando"></textarea>
                        <p v-if="intentoGuardar && mat.accion === 'baja' && !mat.motivo_baja.trim()" class="error-campo">El motivo de baja es requerido.</p>
                      </div>
                    </transition>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Footer del formulario -->
        <div class="form-footer">
          <button class="btn-cancelar" @click="$router.push('/inscripciones')" :disabled="guardando">Cancelar</button>
          <button class="btn-guardar" @click="guardar" :disabled="!puedeGuardar || guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            <span>{{ guardando ? 'Guardando...' : 'Guardar cambios' }}</span>
          </button>
        </div>

      </div>

      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

// ─────────────────────────────────────────────────────────────────────────────
// BASE URL — el equipo de back solo cambia esta constante
// ─────────────────────────────────────────────────────────────────────────────
const API = 'http://localhost:8000/api'

const router = useRouter()
const route  = useRoute()

// Si la ruta es /inscripciones/gestionar/:id con un id numérico → modo edición
const idParam  = route.params.id
const esEdicion = computed(() => !!idParam && idParam !== 'nuevo')

// ─────────────────────────────────────────────────────────────────────────────
// ESTADOS
// ─────────────────────────────────────────────────────────────────────────────
const cargando       = ref(false)
const guardando      = ref(false)
const buscandoAlumno = ref(false)
const intentoGuardar = ref(false)

const periodosDB       = ref([])
const gruposDisponibles = ref([])
const alumnoEncontrado  = ref(null)

const form = ref({
  id_inscripcion: null,
  numero_control: '',
  id_alumno:      null,
  id_periodo:     '',
  estado:         'activa',
  observaciones:  '',
  materias:       []   // [{ id_detalle, id_materia, clave_materia, nombre_materia, grupo, accion, id_grupo_nuevo, motivo_baja }]
})

const errores = ref({ id_periodo: '' })

const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null
const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

// ─────────────────────────────────────────────────────────────────────────────
// COMPUTED
// ─────────────────────────────────────────────────────────────────────────────
const todasConBaja = computed(() =>
  form.value.materias.length > 0 && form.value.materias.every(m => m.accion === 'baja')
)

const puedeGuardar = computed(() => {
  if (!esEdicion.value) return !!alumnoEncontrado.value && !!form.value.id_periodo
  return !!form.value.id_periodo
})

// ─────────────────────────────────────────────────────────────────────────────
// CARGA INICIAL
// ─────────────────────────────────────────────────────────────────────────────

/*
 * GET /api/periodos
 * Respuesta: [{ id_periodo, nombre_periodo }]
 */
const cargarPeriodos = async () => {
  try {
    const res = await fetch(`${API}/periodos`)
    if (!res.ok) throw new Error()
    periodosDB.value = await res.json()
  } catch { console.error('❌ cargarPeriodos') }
}

/*
 * GET /api/grupos/disponibles?id_periodo=X&id_materia=Y
 * Respuesta: [{ id_grupo, nombre_grupo, horario, cupo_disponible }]
 * Se llama cuando el usuario selecciona "Cambio de grupo" en una materia.
 * Por simplicidad se carga una vez al montar con los grupos del periodo activo.
 */
const cargarGruposDisponibles = async (id_periodo = '') => {
  const query = id_periodo ? `?id_periodo=${id_periodo}` : ''
  try {
    const res = await fetch(`${API}/grupos/disponibles${query}`)
    if (!res.ok) throw new Error()
    gruposDisponibles.value = await res.json()
  } catch { console.error('❌ cargarGruposDisponibles') }
}

/*
 * GET /api/inscripciones/:id_inscripcion
 * Respuesta:
 * {
 *   id_inscripcion, id_alumno, numero_control, nombre_alumno,
 *   carrera, semestre, id_periodo, periodo, estado, observaciones,
 *   materias: [{ id_detalle, id_materia, clave_materia, nombre_materia, grupo }]
 * }
 */
const cargarInscripcion = async () => {
  cargando.value = true
  try {
    const res  = await fetch(`${API}/inscripciones/${idParam}`)
    if (!res.ok) throw new Error(`Error ${res.status}`)
    const data = await res.json()
    form.value = {
      id_inscripcion: data.id_inscripcion,
      numero_control: data.numero_control,
      id_alumno:      data.id_alumno,
      id_periodo:     data.id_periodo,
      estado:         data.estado,
      observaciones:  data.observaciones ?? '',
      materias: (data.materias || []).map(m => ({
        ...m,
        accion:         'sin_cambio',
        id_grupo_nuevo: '',
        motivo_baja:    ''
      }))
    }
    alumnoEncontrado.value = { nombre: data.nombre_alumno, carrera: data.carrera, semestre: data.semestre }
    await cargarGruposDisponibles(data.id_periodo)
    console.log('✅ Inscripción cargada:', data)
  } catch (err) {
    console.error('❌ cargarInscripcion:', err)
    mostrarNotificacion('No se pudo cargar la inscripción.', 'error')
  } finally {
    cargando.value = false
  }
}

onMounted(async () => {
  await cargarPeriodos()
  if (esEdicion.value) await cargarInscripcion()
})

// ─────────────────────────────────────────────────────────────────────────────
// BUSCAR ALUMNO
// GET /api/alumnos/control/:numero_control
// Respuesta: { id_alumno, nombre, carrera, semestre }
// ─────────────────────────────────────────────────────────────────────────────
const buscarAlumno = async () => {
  const nc = (form.value.numero_control || '').trim()
  if (!nc) return
  buscandoAlumno.value   = true
  alumnoEncontrado.value = null
  form.value.id_alumno   = null
  try {
    const res  = await fetch(`${API}/alumnos/control/${nc}`)
    if (!res.ok) throw new Error('No encontrado')
    const data = await res.json()
    alumnoEncontrado.value = data
    form.value.id_alumno   = data.id_alumno
    mostrarNotificacion('Alumno encontrado', 'exito')
  } catch {
    mostrarNotificacion(`No se encontró ningún alumno con el No. de Control ${nc}`, 'error')
  } finally {
    buscandoAlumno.value = false
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// GUARDAR
// ─────────────────────────────────────────────────────────────────────────────

/*
 * POST /api/inscripciones
 * Body: { id_alumno, id_periodo, estado, observaciones }
 * Retorna: { id_inscripcion, ... }
 *
 * PUT /api/inscripciones/:id_inscripcion
 * Body:
 * {
 *   id_periodo,
 *   estado,
 *   observaciones,
 *   cambios_materias: [{   <-- solo las que tienen acción != sin_cambio
 *     id_detalle:     number,
 *     accion:         "cambio_grupo" | "baja",
 *     id_grupo_nuevo: number | null,
 *     motivo_baja:    string | null
 *   }]
 * }
 * Retorna: { id_inscripcion, ...campos actualizados }
 */
const guardar = async () => {
  intentoGuardar.value = true
  errores.value.id_periodo = ''

  if (!form.value.id_periodo) {
    errores.value.id_periodo = 'El periodo es requerido.'
    mostrarNotificacion('El periodo es requerido.', 'error')
    return
  }

  // Validar materias si hay cambios
  const bajasInvalidas  = form.value.materias.some(m => m.accion === 'baja'        && !m.motivo_baja.trim())
  const cambiosInvalidos = form.value.materias.some(m => m.accion === 'cambio_grupo' && !m.id_grupo_nuevo)
  if (bajasInvalidas)   { mostrarNotificacion('Completa el motivo de cada baja solicitada.',   'error'); return }
  if (cambiosInvalidos) { mostrarNotificacion('Selecciona el nuevo grupo para cada cambio solicitado.', 'error'); return }

  guardando.value = true
  try {
    let body, url, method
    if (esEdicion.value) {
      url    = `${API}/inscripciones/${form.value.id_inscripcion}`
      method = 'PUT'
      const cambiosMaterias = form.value.materias
        .filter(m => m.accion !== 'sin_cambio')
        .map(m => ({ id_detalle: m.id_detalle, accion: m.accion, id_grupo_nuevo: m.id_grupo_nuevo || null, motivo_baja: m.motivo_baja || null }))
      body = { id_periodo: form.value.id_periodo, estado: form.value.estado, observaciones: form.value.observaciones, cambios_materias: cambiosMaterias }
    } else {
      url    = `${API}/inscripciones`
      method = 'POST'
      body   = { id_alumno: form.value.id_alumno, id_periodo: form.value.id_periodo, estado: form.value.estado, observaciones: form.value.observaciones }
    }

    console.log(`🔵 ${method} ${url}`, body)
    const res  = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(body) })
    const data = await res.json()
    console.log('🟢 Respuesta:', data)
    if (!res.ok) throw new Error(JSON.stringify(data))

    mostrarNotificacion(esEdicion.value ? 'Inscripción actualizada correctamente.' : 'Inscripción creada correctamente.', 'exito')
    setTimeout(() => router.push('/inscripciones'), 1500)
  } catch (err) {
    console.error('❌ guardar:', err)
    mostrarNotificacion('Ocurrió un error al guardar. Revisa los datos e intenta de nuevo.', 'error')
  } finally {
    guardando.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');
.gestion-page {
  --azul:#1B396A;--azul-hover:#1D4ED8;--azul-suave:#DBEAFE;
  --borde:#E5E7EB;--fondo:#F5F5F5;--texto:#1A1A1A;--gris:#6B7280;
  --verde:#16A34A;--rojo:#DC2626;--amarillo:#F59E0B;
  max-width:100%;background:var(--fondo);font-family:'Montserrat',sans-serif;padding-bottom:2rem;
}
.breadcrumb{display:flex;align-items:center;gap:.4rem;color:var(--gris);font-size:.88rem;margin-bottom:.75rem;}
.breadcrumb .sep{color:#E5E7EB;}
.breadcrumb-link{color:var(--gris);text-decoration:none;transition:color .15s;}
.breadcrumb-link:hover{color:var(--azul);}
.breadcrumb-actual{color:var(--azul);font-weight:600;}
.barra-carga-global{height:3px;background:transparent;border-radius:2px;margin-bottom:1rem;overflow:hidden;opacity:0;transition:opacity .3s;}
.barra-carga-global.visible{opacity:1;}
.barra-progreso{height:100%;width:40%;background:#1B396A;border-radius:2px;animation:deslizar 1.2s ease-in-out infinite;}
@keyframes deslizar{0%{transform:translateX(-100%)}100%{transform:translateX(350%)}}
.notificacion-ui{display:flex;align-items:center;gap:10px;padding:12px 18px;border-radius:10px;font-size:.93rem;font-weight:500;margin-bottom:1rem;box-shadow:0 4px 12px rgba(0,0,0,.1);}
.notificacion-ui.exito{background:#DCFCE7;color:#16A34A;border:1px solid #86EFAC;}
.notificacion-ui.error{background:#FEE2E2;color:#DC2626;border:1px solid #FCA5A5;}
.notif-icono{width:20px;height:20px;flex-shrink:0;}
.notif-fade-enter-active,.notif-fade-leave-active{transition:all .35s ease;}
.notif-fade-enter-from,.notif-fade-leave-to{opacity:0;transform:translateY(-8px);}
.page-header{margin-bottom:1.4rem;}
.page-title{color:var(--texto);font-size:1.75rem;font-weight:700;letter-spacing:-.02em;margin:0;}
.formulario-card{background:#fff;border-radius:14px;box-shadow:0 4px 12px rgba(0,0,0,.06);border:1px solid var(--borde);overflow:hidden;margin-bottom:1.5rem;}
.seccion{padding:1.6rem 2rem;}
.seccion+.seccion{border-top:1px solid var(--borde);}
.seccion-titulo{display:flex;align-items:center;gap:8px;font-size:1rem;font-weight:700;color:var(--azul);margin-bottom:6px;}
.seccion-icono{width:20px;height:20px;stroke:var(--azul);flex-shrink:0;}
.seccion-linea{height:1px;background:var(--borde);margin-bottom:1.2rem;}
.campo-grupo{display:flex;flex-direction:column;gap:6px;margin-bottom:1rem;}
.campo-label{font-size:.85rem;font-weight:600;color:var(--texto);}
.campos-dobles{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
.campo-input,.campo-select,.campo-textarea{width:100%;padding:10px 14px;border:1.5px solid var(--borde);border-radius:8px;font-size:.93rem;background:#fff;color:var(--texto);font-family:'Montserrat',sans-serif;outline:none;transition:border-color .2s,box-shadow .2s;box-sizing:border-box;}
.campo-input:focus,.campo-select:focus,.campo-textarea:focus{border-color:var(--azul);box-shadow:0 0 0 3px var(--azul-suave);}
.campo-input::placeholder,.campo-textarea::placeholder{color:#9CA3AF;}
.campo-input.input-encontrado{border-color:var(--verde);background:#F0FDF4;}
.campo-input:disabled,.campo-select:disabled,.campo-textarea:disabled{background:#F9FAFB;color:var(--gris);cursor:not-allowed;}
.campo-textarea{resize:vertical;}
.campo-readonly{padding:10px 14px;border:1.5px solid var(--borde);border-radius:8px;background:var(--fondo);color:var(--gris);font-size:.93rem;font-weight:600;font-family:'Montserrat',sans-serif;}
.search-inline{display:flex;gap:8px;align-items:stretch;}
.search-wrapper-field{position:relative;flex:1;}
.search-icono-field{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;stroke:var(--gris);pointer-events:none;}
.search-wrapper-field .campo-input{padding-left:38px;}
.btn-buscar{background:var(--azul);color:#fff;border:none;padding:10px 18px;border-radius:8px;font-weight:600;font-size:.88rem;cursor:pointer;font-family:'Montserrat',sans-serif;transition:background .2s;white-space:nowrap;display:flex;align-items:center;gap:6px;}
.btn-buscar:hover:not(:disabled){background:var(--azul-hover);}
.btn-buscar:disabled{opacity:.6;cursor:not-allowed;}
.btn-buscar.btn-encontrado{background:var(--verde);}
.bloque-alumno{display:flex;align-items:center;gap:12px;background:#F0FDF4;border:1px solid #86EFAC;border-radius:10px;padding:12px 16px;margin-top:8px;}
.bloque-icono{width:24px;height:24px;stroke:var(--verde);flex-shrink:0;}
.bloque-info{display:flex;flex-direction:column;gap:2px;}
.bloque-nombre{font-size:.95rem;font-weight:700;color:var(--texto);margin:0;}
.bloque-detalle{font-size:.82rem;color:var(--gris);margin:0;}
.alerta-amarilla{display:flex;align-items:flex-start;gap:10px;background:#FEF3C7;border:1px solid #FDE68A;border-radius:10px;padding:12px 16px;margin-bottom:1rem;font-size:.88rem;color:#92400E;font-weight:500;}
.alerta-icono{width:20px;height:20px;stroke:var(--amarillo);flex-shrink:0;margin-top:1px;}
.tabla-materias-wrapper{border-radius:10px;overflow:hidden;border:1px solid var(--borde);}
.tabla-materias{width:100%;border-collapse:collapse;font-size:.88rem;}
.tabla-materias thead tr{background:var(--fondo);}
.tabla-materias th{padding:10px 14px;text-align:left;font-weight:600;font-size:.82rem;color:var(--texto);border-bottom:1px solid var(--borde);white-space:nowrap;}
.materia-fila{border-bottom:1px solid var(--borde);transition:background .15s;}
.materia-fila:last-child{border-bottom:none;}
.materia-fila:hover{background:#F8FAFC;}
.materia-fila td{padding:12px 14px;vertical-align:top;}
.materia-nombre{font-weight:600;color:var(--texto);margin:0 0 2px;}
.materia-clave{font-size:.8rem;color:var(--gris);margin:0;font-family:monospace;}
.td-gris{font-size:.85rem;color:var(--gris);white-space:nowrap;}
.select-accion{padding:7px 10px;border:1px solid var(--borde);border-radius:6px;font-size:.85rem;font-family:'Montserrat',sans-serif;color:var(--texto);background:#fff;outline:none;cursor:pointer;width:100%;transition:border-color .2s;}
.select-accion:focus{border-color:var(--azul);}
.detalle-accion{padding-top:4px;}
.select-grupo{padding:7px 10px;border:1px solid var(--borde);border-radius:6px;font-size:.85rem;font-family:'Montserrat',sans-serif;color:var(--texto);background:#fff;outline:none;cursor:pointer;width:100%;transition:border-color .2s;}
.select-grupo:focus{border-color:var(--azul);}
.textarea-motivo{width:100%;padding:8px 10px;border:1px solid var(--borde);border-radius:6px;font-size:.85rem;font-family:'Montserrat',sans-serif;color:var(--texto);background:#fff;outline:none;resize:vertical;transition:border-color .2s;box-sizing:border-box;}
.textarea-motivo:focus{border-color:var(--azul);box-shadow:0 0 0 3px var(--azul-suave);}
.error-campo{font-size:.78rem;color:var(--rojo);font-weight:600;margin:4px 0 0;}
.slide-down-enter-active{transition:all .25s ease;}
.slide-down-enter-from{opacity:0;transform:translateY(-8px);}
.form-footer{padding:14px 2rem;background:var(--fondo);border-top:1px solid var(--borde);display:flex;justify-content:flex-end;gap:10px;}
.btn-cancelar{background:#fff;color:var(--azul);border:1px solid var(--azul);padding:10px 22px;border-radius:8px;font-weight:600;font-size:.9rem;cursor:pointer;font-family:'Montserrat',sans-serif;transition:background .15s;}
.btn-cancelar:hover:not(:disabled){background:var(--azul-suave);}
.btn-cancelar:disabled{opacity:.5;cursor:not-allowed;}
.btn-guardar{display:flex;align-items:center;gap:8px;padding:10px 24px;border-radius:8px;font-weight:600;font-size:.9rem;cursor:pointer;font-family:'Montserrat',sans-serif;background:var(--azul);color:#fff;border:none;transition:background .2s;min-width:160px;justify-content:center;}
.btn-guardar:hover:not(:disabled){background:var(--azul-hover);}
.btn-guardar:disabled{opacity:.6;cursor:not-allowed;}
.spinner-btn{display:inline-block;width:15px;height:15px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:girar .7s linear infinite;flex-shrink:0;}
@keyframes girar{to{transform:rotate(360deg)}}
.pie-pagina{text-align:center;color:#9CA3AF;font-size:.82rem;padding-top:2rem;border-top:1px solid var(--borde);margin-top:1rem;font-family:'Montserrat',sans-serif;}
@media(max-width:768px){.campos-dobles{grid-template-columns:1fr;}.seccion{padding:1.2rem;}.form-footer{padding:14px 1.2rem;}}
</style>