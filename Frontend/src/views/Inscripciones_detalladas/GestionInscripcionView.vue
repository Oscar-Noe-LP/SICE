<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="alumnos-page">

      <!-- ── Breadcrumb ── -->
      <nav class="breadcrumb">
        <router-link to="/inicio"        class="bc-link">Inicio</router-link>
        <span class="bc-sep">›</span>
        <router-link to="/inscripciones" class="bc-link">Inscripciones</router-link>
        <span class="bc-sep">›</span>
        <span class="bc-actual">{{ esEdicion ? 'Gestionar' : 'Nueva Inscripción' }}</span>
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

      <!-- ══ FORMULARIO ══ -->
      <div class="form-card">

        <!-- SECCIÓN 1 — Alumno -->
        <div class="seccion">
          <div class="sec-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#1B396A" stroke-width="2" style="width:20px;height:20px;flex-shrink:0">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>Alumno</span>
          </div>
          <div class="sec-linea"></div>

          <div class="form-grupo">
            <label>Número de Control *</label>
            <div v-if="esEdicion" class="modal-input deshabilitado">{{ form.numero_control }}</div>
            <template v-else>
              <div style="display:flex;gap:8px">
                <input
                  v-model="form.numero_control"
                  type="text"
                  class="modal-input"
                  placeholder="Ej: 21456887 — presiona Enter o Buscar"
                  @keydown.enter="buscarAlumno"
                  :disabled="guardando"
                  style="flex:1"
                />
                <button class="btn-buscar-modal" @click="buscarAlumno"
                        :class="{ 'btn-encontrado': alumnoEncontrado }"
                        :disabled="buscandoAlumno || !!alumnoEncontrado">
                  <span v-if="buscandoAlumno" class="spinner-btn"></span>
                  <span v-else-if="alumnoEncontrado">✓ Encontrado</span>
                  <span v-else>Buscar</span>
                </button>
              </div>
            </template>
          </div>

          <transition name="notif-fade">
            <div v-if="alumnoEncontrado" class="bloque-alumno-found">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#16A34A" stroke-width="2" style="width:22px;height:22px;flex-shrink:0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div>
                <p style="margin:0 0 2px;font-weight:700;font-size:.92rem;color:#1A1A1A;font-family:'Montserrat',sans-serif">{{ alumnoEncontrado.nombre }}</p>
                <p style="margin:0;font-size:.8rem;color:#6B7280;font-family:'Montserrat',sans-serif">{{ alumnoEncontrado.carrera }} · Semestre {{ alumnoEncontrado.semestre }} · {{ form.numero_control }}</p>
              </div>
            </div>
          </transition>
        </div>

        <!-- SECCIÓN 2 — Datos de inscripción -->
        <div class="seccion">
          <div class="sec-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#1B396A" stroke-width="2" style="width:20px;height:20px;flex-shrink:0">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span>Datos de la Inscripción</span>
          </div>
          <div class="sec-linea"></div>

          <div class="form-grupo-doble">
            <div class="form-grupo">
              <label>Periodo *</label>
              <select v-model="form.id_periodo" class="modal-select" :disabled="guardando">
                <option value="">Seleccionar periodo...</option>
                <option v-for="p in periodosDB" :key="p.id_periodo" :value="p.id_periodo">{{ p.nombre_periodo }}</option>
              </select>
              <p v-if="errores.id_periodo" style="font-size:.78rem;color:#DC2626;font-weight:600;margin:4px 0 0;font-family:'Montserrat',sans-serif">{{ errores.id_periodo }}</p>
            </div>
            <div class="form-grupo">
              <label>Estado</label>
              <select v-model="form.estado" class="modal-select" :disabled="guardando">
                <option value="activa">Activa</option>
                <option value="baja">Baja</option>
                <option value="cambio_pendiente">Cambio pendiente</option>
              </select>
            </div>
          </div>

          <div class="form-grupo">
            <label>Observaciones</label>
            <textarea v-model="form.observaciones" class="modal-textarea" rows="3"
                      placeholder="Observaciones opcionales..." :disabled="guardando"></textarea>
          </div>
        </div>

        <!-- SECCIÓN 3 — Materias (solo edición) -->
        <div v-if="esEdicion && form.materias && form.materias.length > 0" class="seccion">
          <div class="sec-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#1B396A" stroke-width="2" style="width:20px;height:20px;flex-shrink:0">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span>Materias Inscritas</span>
          </div>
          <div class="sec-linea"></div>

          <transition name="notif-fade">
            <div v-if="todasConBaja" class="alerta-amarilla">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#F59E0B" stroke-width="2" style="width:20px;height:20px;flex-shrink:0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              <span>El alumno quedará sin materias activas en este periodo.</span>
            </div>
          </transition>

          <div class="table-container" style="margin-top:0">
            <table class="alumnos-table">
              <thead>
                <tr>
                  <th>Materia</th>
                  <th>Grupo actual</th>
                  <th>Acción</th>
                  <th>Detalle</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="mat in form.materias" :key="mat.id_detalle">
                  <td>
                    <p style="font-weight:600;margin:0 0 2px;font-size:.9rem">{{ mat.nombre_materia }}</p>
                    <p style="font-size:.8rem;color:#6B7280;margin:0;font-family:monospace">{{ mat.clave_materia }}</p>
                  </td>
                  <td style="font-size:.85rem;color:#6B7280;white-space:nowrap">{{ mat.grupo }}</td>
                  <td>
                    <select class="modal-select" style="padding:7px 10px;font-size:.85rem"
                            v-model="mat.accion"
                            @change="mat.id_grupo_nuevo=''; mat.motivo_baja=''"
                            :disabled="guardando">
                      <option value="sin_cambio">Sin cambio</option>
                      <option value="cambio_grupo">Cambio de grupo</option>
                      <option value="baja">Baja de materia</option>
                    </select>
                  </td>
                  <td>
                    <transition name="notif-fade">
                      <div v-if="mat.accion === 'cambio_grupo'">
                        <select class="modal-select" style="padding:7px 10px;font-size:.85rem"
                                v-model="mat.id_grupo_nuevo" :disabled="guardando">
                          <option value="">Seleccionar grupo disponible...</option>
                          <option v-for="g in gruposDisponibles" :key="g.id_grupo" :value="g.id_grupo">
                            {{ g.nombre_grupo }} — {{ g.horario }} ({{ g.cupo_disponible }} lugares)
                          </option>
                        </select>
                        <p v-if="intentoGuardar && !mat.id_grupo_nuevo"
                           style="font-size:.78rem;color:#DC2626;font-weight:600;margin:4px 0 0;font-family:'Montserrat',sans-serif">
                          Selecciona el nuevo grupo.
                        </p>
                      </div>
                      <div v-else-if="mat.accion === 'baja'">
                        <textarea class="modal-textarea" style="padding:8px 10px;font-size:.85rem"
                                  v-model="mat.motivo_baja" rows="2"
                                  placeholder="Motivo de baja (requerido)..."
                                  :disabled="guardando"></textarea>
                        <p v-if="intentoGuardar && !mat.motivo_baja.trim()"
                           style="font-size:.78rem;color:#DC2626;font-weight:600;margin:4px 0 0;font-family:'Montserrat',sans-serif">
                          El motivo es requerido.
                        </p>
                      </div>
                    </transition>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Footer del form -->
        <div class="form-footer">
          <button class="btn-secundario" @click="$router.push('/inscripciones')" :disabled="guardando">Cancelar</button>
          <button class="btn-guardar" @click="guardar" :disabled="!puedeGuardar || guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;stroke:#fff">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
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

const API    = 'http://localhost:8000/api'
const router = useRouter()
const route  = useRoute()

const idParam   = route.params.id
const esEdicion = computed(() => !!idParam && idParam !== 'nuevo')

const cargando       = ref(false)
const guardando      = ref(false)
const buscandoAlumno = ref(false)
const intentoGuardar = ref(false)
const periodosDB         = ref([])
const gruposDisponibles  = ref([])
const alumnoEncontrado   = ref(null)
const errores = ref({ id_periodo: '' })

const form = ref({
  id_inscripcion: null, numero_control: '', id_alumno: null,
  id_periodo: '', estado: 'activa', observaciones: '', materias: []
})

const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null
const mostrarNotificacion = (msg, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje: msg, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

const todasConBaja = computed(() => form.value.materias.length > 0 && form.value.materias.every(m => m.accion === 'baja'))
const puedeGuardar = computed(() => esEdicion.value ? !!form.value.id_periodo : (!!alumnoEncontrado.value && !!form.value.id_periodo))

/* GET /api/periodos — [{ id_periodo, nombre_periodo }] */
const cargarPeriodos = async () => {
  try { const r = await fetch(`${API}/periodos`); if (r.ok) periodosDB.value = await r.json() }
  catch { console.error('❌ periodos') }
}

/* GET /api/grupos/disponibles?id_periodo=X — [{ id_grupo, nombre_grupo, horario, cupo_disponible }] */
const cargarGrupos = async (id_periodo = '') => {
  try {
    const q = id_periodo ? `?id_periodo=${id_periodo}` : ''
    const r = await fetch(`${API}/grupos/disponibles${q}`)
    if (r.ok) gruposDisponibles.value = await r.json()
  } catch { console.error('❌ grupos') }
}

/* GET /api/inscripciones/:id
   Respuesta: { id_inscripcion, id_alumno, numero_control, nombre_alumno, carrera, semestre,
                id_periodo, periodo, estado, observaciones,
                materias:[{ id_detalle, id_materia, clave_materia, nombre_materia, grupo }] } */
const cargarInscripcion = async () => {
  cargando.value = true
  try {
    const r = await fetch(`${API}/inscripciones/${idParam}`)
    if (!r.ok) throw new Error()
    const d = await r.json()
    form.value = {
      id_inscripcion: d.id_inscripcion, numero_control: d.numero_control, id_alumno: d.id_alumno,
      id_periodo: d.id_periodo, estado: d.estado, observaciones: d.observaciones ?? '',
      materias: (d.materias || []).map(m => ({ ...m, accion: 'sin_cambio', id_grupo_nuevo: '', motivo_baja: '' }))
    }
    alumnoEncontrado.value = { nombre: d.nombre_alumno, carrera: d.carrera, semestre: d.semestre }
    await cargarGrupos(d.id_periodo)
    console.log('✅ Inscripción cargada')
  } catch { mostrarNotificacion('No se pudo cargar la inscripción.', 'error') }
  finally { cargando.value = false }
}

onMounted(async () => { await cargarPeriodos(); if (esEdicion.value) await cargarInscripcion() })

/* GET /api/alumnos/control/:nc — { id_alumno, nombre, carrera, semestre } */
const buscarAlumno = async () => {
  const nc = (form.value.numero_control || '').trim()
  if (!nc) return
  buscandoAlumno.value = true; alumnoEncontrado.value = null; form.value.id_alumno = null
  try {
    const r = await fetch(`${API}/alumnos/control/${nc}`)
    if (!r.ok) throw new Error()
    const d = await r.json()
    alumnoEncontrado.value = d; form.value.id_alumno = d.id_alumno
    mostrarNotificacion('Alumno encontrado', 'exito')
  } catch { mostrarNotificacion(`No se encontró el No. de Control ${nc}`, 'error') }
  finally { buscandoAlumno.value = false }
}

/* POST /api/inscripciones — body: { id_alumno, id_periodo, estado, observaciones }
   PUT  /api/inscripciones/:id — body: { id_periodo, estado, observaciones,
     cambios_materias:[{ id_detalle, accion, id_grupo_nuevo, motivo_baja }] } */
const guardar = async () => {
  intentoGuardar.value = true; errores.value.id_periodo = ''
  if (!form.value.id_periodo) { errores.value.id_periodo = 'El periodo es requerido.'; mostrarNotificacion('El periodo es requerido.', 'error'); return }
  const bajasInv = form.value.materias.some(m => m.accion === 'baja' && !m.motivo_baja.trim())
  const cambInv  = form.value.materias.some(m => m.accion === 'cambio_grupo' && !m.id_grupo_nuevo)
  if (bajasInv)  { mostrarNotificacion('Completa el motivo de cada baja.', 'error'); return }
  if (cambInv)   { mostrarNotificacion('Selecciona el nuevo grupo para cada cambio.', 'error'); return }

  guardando.value = true
  try {
    const esEd = esEdicion.value
    const url  = esEd ? `${API}/inscripciones/${form.value.id_inscripcion}` : `${API}/inscripciones`
    const meth = esEd ? 'PUT' : 'POST'
    const body = esEd
      ? { id_periodo: form.value.id_periodo, estado: form.value.estado, observaciones: form.value.observaciones,
          cambios_materias: form.value.materias.filter(m => m.accion !== 'sin_cambio').map(m => ({
            id_detalle: m.id_detalle, accion: m.accion,
            id_grupo_nuevo: m.id_grupo_nuevo || null, motivo_baja: m.motivo_baja || null
          })) }
      : { id_alumno: form.value.id_alumno, id_periodo: form.value.id_periodo, estado: form.value.estado, observaciones: form.value.observaciones }
    console.log(`🔵 ${meth}`, url, body)
    const r = await fetch(url, { method: meth, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(body) })
    const d = await r.json()
    if (!r.ok) throw new Error(JSON.stringify(d))
    mostrarNotificacion(esEd ? 'Inscripción actualizada.' : 'Inscripción creada.', 'exito')
    setTimeout(() => router.push('/inscripciones'), 1500)
  } catch (e) { console.error('❌', e); mostrarNotificacion('Error al guardar. Revisa los datos.', 'error') }
  finally { guardando.value = false }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.alumnos-page {
  --azul: #1B396A; --azul-hover: #1D4ED8; --borde: #E5E7EB; --fondo: #F5F5F5; --texto: #1A1A1A; --gris: #6B7280;
  max-width: 100%; background: var(--fondo); font-family: 'Montserrat', sans-serif; padding-bottom: 2rem;
}

/* breadcrumb */
.breadcrumb { display: flex; align-items: center; gap: .4rem; color: #6B7280; font-size: .88rem; margin-bottom: .75rem; }
.bc-sep    { color: #E5E7EB; }
.bc-link   { color: #6B7280; text-decoration: none; transition: color .15s; }
.bc-link:hover { color: #1B396A; }
.bc-actual { color: #1B396A; font-weight: 600; }

.barra-carga-global  { height: 3px; background: transparent; border-radius: 2px; margin-bottom: 1rem; overflow: hidden; transition: opacity .3s; opacity: 0; }
.barra-carga-global.visible { opacity: 1; }
.barra-progreso { height: 100%; width: 40%; background: #1B396A; border-radius: 2px; animation: deslizar 1.2s ease-in-out infinite; }
@keyframes deslizar { 0%{transform:translateX(-100%)} 100%{transform:translateX(350%)} }

.notificacion-ui { display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 10px; font-size: .93rem; font-weight: 500; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.notificacion-ui.exito { background: #DCFCE7; color: #16A34A; border: 1px solid #86EFAC; }
.notificacion-ui.error { background: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5; }
.notif-icono { width: 20px; height: 20px; flex-shrink: 0; }
.notif-fade-enter-active, .notif-fade-leave-active { transition: all .35s ease; }
.notif-fade-enter-from, .notif-fade-leave-to { opacity: 0; transform: translateY(-8px); }

.page-header { margin-bottom: 1.4rem; }
.page-title  { color: #1A1A1A; font-size: 1.75rem; font-weight: 700; letter-spacing: -.02em; margin: 0; }

/* ── Form card (mismo estilo que table-container) ── */
.form-card {
  background: #FFFFFF; border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,.05); border: 1px solid #E5E7EB;
  overflow: hidden; margin-bottom: 1.5rem;
}
.seccion { padding: 1.6rem 2rem; }
.seccion + .seccion { border-top: 1px solid #E5E7EB; }

.sec-titulo {
  display: flex; align-items: center; gap: 8px;
  font-size: 1rem; font-weight: 700; color: #1B396A;
  margin-bottom: 6px; font-family: 'Montserrat', sans-serif;
}
.sec-linea { height: 1px; background: #E5E7EB; margin-bottom: 1.2rem; }

/* ── Campos (mismos estilos que modal) ── */
.form-grupo { margin-bottom: 1.2rem; }
.form-grupo-doble { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-grupo label {
  display: block; margin-bottom: 6px; font-weight: 600;
  font-size: .9rem; color: #1A1A1A; font-family: 'Montserrat', sans-serif;
}
.modal-input, .modal-select, .modal-textarea {
  width: 100%; padding: 10px 14px; border: 1.5px solid #E5E7EB; border-radius: 8px;
  font-size: .95rem; background: #FFFFFF; color: #1A1A1A;
  font-family: 'Montserrat', sans-serif; outline: none;
  transition: border-color .2s, box-shadow .2s; box-sizing: border-box;
}
.modal-input:focus, .modal-select:focus, .modal-textarea:focus { border-color: #1B396A; box-shadow: 0 0 0 3px #DBEAFE; }
.modal-input::placeholder, .modal-textarea::placeholder { color: #9CA3AF; }
.modal-textarea { resize: vertical; }
.modal-input.deshabilitado {
  background: #F5F5F5; cursor: not-allowed; color: #6B7280; font-weight: 600;
  display: flex; align-items: center; border: 1.5px solid #E5E7EB; border-radius: 8px; padding: 10px 14px;
}

/* tabla de materias usa los mismos estilos de alumnos-table */
.table-container { background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,.05); border: 1px solid #E5E7EB; }
.alumnos-table   { width: 100%; border-collapse: collapse; outline: none; }
.alumnos-table th { background: #F5F5F5; padding: 12px 16px; text-align: left; font-weight: 600; font-size: .88rem; color: #1A1A1A; border-bottom: 1px solid #E5E7EB; font-family: 'Montserrat', sans-serif; white-space: nowrap; }
.alumnos-table td { padding: 11px 16px; border-bottom: 1px solid #E5E7EB; color: #1A1A1A; font-size: .93rem; font-family: 'Montserrat', sans-serif; }
.alumnos-table tbody tr:last-child td { border-bottom: none; }

/* Alerta amarilla */
.alerta-amarilla {
  display: flex; align-items: flex-start; gap: 10px;
  background: #FEF3C7; border: 1px solid #FDE68A; border-radius: 10px;
  padding: 12px 16px; margin-bottom: 1rem; font-size: .88rem; color: #92400E;
  font-weight: 500; font-family: 'Montserrat', sans-serif;
}

/* Bloque alumno encontrado */
.bloque-alumno-found {
  display: flex; align-items: center; gap: 10px;
  background: #F0FDF4; border: 1px solid #86EFAC;
  border-radius: 8px; padding: 10px 14px; margin-bottom: 1.2rem;
}

/* Footer del form */
.form-footer {
  padding: 1rem 2rem; background: #F5F5F5;
  border-top: 1px solid #E5E7EB;
  display: flex; justify-content: flex-end; gap: 10px;
}

/* Botón buscar dentro del form */
.btn-buscar-modal {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: .9rem;
  cursor: pointer; font-family: 'Montserrat', sans-serif; white-space: nowrap;
  background: #1B396A; color: #FFFFFF; border: 2px solid #1B396A;
  transition: background .15s;
}
.btn-buscar-modal:hover:not(:disabled) { background: #1D4ED8; border-color: #1D4ED8; }
.btn-buscar-modal:disabled { opacity: .6; cursor: not-allowed; }
.btn-encontrado { background: #16A34A !important; border-color: #16A34A !important; }

/* ── Botones del footer del form ── */
.btn-secundario {
  padding: 10px 22px; border-radius: 8px; font-weight: 600; font-size: .92rem;
  cursor: pointer; font-family: 'Montserrat', sans-serif;
  background: #FFFFFF; color: #1B396A; border: 2px solid #1B396A;
  transition: background .15s;
}
.btn-secundario:hover:not(:disabled) { background: #DBEAFE; }
.btn-secundario:disabled { opacity: .5; cursor: not-allowed; }

.btn-guardar {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 10px 28px; border-radius: 8px; font-weight: 600; font-size: .92rem;
  cursor: pointer; font-family: 'Montserrat', sans-serif;
  background: #1B396A; color: #FFFFFF; border: 2px solid #1B396A;
  transition: background .15s; min-width: 160px; justify-content: center;
}
.btn-guardar:hover:not(:disabled) { background: #1D4ED8; border-color: #1D4ED8; }
.btn-guardar:disabled { opacity: .55; cursor: not-allowed; }

.spinner-btn { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.4); border-top-color: #FFFFFF; border-radius: 50%; animation: girar .7s linear infinite; flex-shrink: 0; }
@keyframes girar { to{transform:rotate(360deg)} }

.pie-pagina { text-align: center; color: #9CA3AF; font-size: .82rem; padding-top: 2rem; border-top: 1px solid #E5E7EB; margin-top: 1rem; font-family: 'Montserrat', sans-serif; }
@media(max-width:768px) { .form-grupo-doble{grid-template-columns:1fr;} .seccion{padding:1.2rem;} .form-footer{padding:1rem 1.2rem;} }
</style>