<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="alumnos-page">

      <nav class="breadcrumb">
        <router-link to="/inicio" class="bc-link">Inicio</router-link>
        <span class="bc-sep">›</span>
        <router-link to="/inscripciones" class="bc-link">Inscripciones</router-link>
        <span class="bc-sep">›</span>
        <span class="bc-actual">{{ esEdicion ? 'Editar Inscripcion' : 'Nueva Inscripcion' }}</span>
      </nav>

      <div class="barra-carga-global" :class="{ visible: cargando }"><div class="barra-progreso"></div></div>

      <transition name="notif-fade">
        <div v-if="notificacion.visible" class="notificacion-ui" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo==='exito'" xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <div class="page-header">
        <h1 class="page-title">{{ esEdicion ? 'Editar Inscripcion' : 'Nueva Inscripcion' }}</h1>
      </div>

      <div class="form-card">

        <!-- SECCION 1: Alumno -->
        <div class="seccion">
          <div class="sec-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#1B396A" stroke-width="2" style="width:20px;height:20px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>Alumno</span>
          </div>
          <div class="sec-linea"></div>

          <div class="form-grupo">
            <label>Numero de Control *</label>
            <!-- En edicion alumno no cambia: UNIQUE(id_alumno, id_grupo) en BD -->
            <div v-if="esEdicion" class="f-readonly">{{ form.numero_control }}</div>
            <template v-else>
              <div style="display:flex;gap:8px">
                <input v-model="form.numero_control" type="text" class="f-input"
                       placeholder="Ej: 21456887 — presiona Enter o Buscar"
                       @keydown.enter="buscarAlumno" :disabled="guardando" style="flex:1"/>
                <button class="btn-buscar-modal" :class="{'btn-encontrado':alumnoEncontrado}"
                        @click="buscarAlumno" :disabled="buscandoAlumno||!!alumnoEncontrado">
                  <span v-if="buscandoAlumno" class="spinner-btn"></span>
                  <span v-else-if="alumnoEncontrado">✓ Encontrado</span>
                  <span v-else>Buscar</span>
                </button>
              </div>
            </template>
          </div>

          <transition name="notif-fade">
            <div v-if="alumnoEncontrado" class="bloque-alumno">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#16A34A" stroke-width="2" style="width:22px;height:22px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <div>
                <p style="margin:0 0 2px;font-weight:700;font-size:.92rem;color:#1A1A1A;font-family:'Montserrat',sans-serif">{{ alumnoEncontrado.nombre_alumno }}</p>
                <p style="margin:0;font-size:.8rem;color:#6B7280;font-family:'Montserrat',sans-serif">{{ alumnoEncontrado.nombre_carrera }} · {{ form.numero_control }}</p>
              </div>
            </div>
          </transition>
        </div>

        <!-- SECCION 2: Datos inscripcion -->
        <div class="seccion">
          <div class="sec-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#1B396A" stroke-width="2" style="width:20px;height:20px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span>Datos de la Inscripcion</span>
          </div>
          <div class="sec-linea"></div>

          <!-- grupo.id_grupo: FK que trae materia+periodo+aula. No editable en edicion -->
          <div class="form-grupo">
            <label>Grupo *</label>
            <div v-if="esEdicion" class="f-readonly">{{ grupoTexto }}</div>
            <template v-else>
              <select v-model="form.id_grupo" class="f-select" :disabled="guardando">
                <option value="">Seleccionar grupo disponible...</option>
                <option v-for="g in gruposDisponibles" :key="g.id_grupo" :value="g.id_grupo">
                  {{ g.clave_grupo }} — {{ g.nombre_materia }} | {{ g.nombre_periodo }}
                </option>
              </select>
              <p v-if="errores.id_grupo" class="error-campo">{{ errores.id_grupo }}</p>
            </template>
          </div>

          <!-- inscripcion.fecha_inscripcion DATE — no editable en edicion -->
          <div class="form-grupo">
            <label>Fecha de Inscripcion *</label>
            <input v-if="!esEdicion" v-model="form.fecha_inscripcion" type="date" class="f-input" :disabled="guardando"/>
            <div v-else class="f-readonly">{{ form.fecha_inscripcion }}</div>
            <p v-if="errores.fecha" class="error-campo">{{ errores.fecha }}</p>
          </div>

          <!-- inscripcion.estatus VARCHAR(50) — UNICO campo editable en edicion -->
          <div class="form-grupo">
            <label>Estatus *</label>
            <select v-model="form.estatus" class="f-select" :disabled="guardando">
              <option value="Activo">Activo</option>
              <option value="Baja Temporal">Baja Temporal</option>
              <option value="Baja Definitiva">Baja Definitiva</option>
            </select>
          </div>

          <div v-if="esEdicion" style="background:#FEF3C7;border:1px solid #FDE68A;border-radius:8px;padding:10px 14px;font-size:.85rem;color:#92400E;font-family:'Montserrat',sans-serif">
            En edicion solo puedes modificar el estatus. El alumno y el grupo no pueden cambiarse.
          </div>
        </div>

        <div class="form-footer">
          <button class="fbtn-cancel" @click="$router.push('/inscripciones')" :disabled="guardando">Cancelar</button>
          <button class="fbtn-save" @click="guardar" :disabled="!puedeGuardar||guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;stroke:#fff"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
          </button>
        </div>

      </div>

      <footer class="pie-pagina">© 2026 Tecnologico Nacional de Mexico · Todos los derechos reservados</footer>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const API    = `${import.meta.env.VITE_API_URL}/api`
const router = useRouter()
const route  = useRoute()

const idParam = route.params.id
// Blindaje: solo modo edicion si el id es numerico real (no "nuevo", no "kpis")
const esEdicion = computed(() => !!idParam && idParam !== 'nuevo' && !isNaN(idParam))

const cargando        = ref(false)
const guardando       = ref(false)
const buscandoAlumno  = ref(false)
const alumnoEncontrado = ref(null)
const gruposDisponibles = ref([])
const grupoTexto = ref('')
const errores = ref({ id_grupo:'', fecha:'' })

// Campos tabla inscripcion: id_inscripcion, id_alumno, id_grupo, fecha_inscripcion, estatus VARCHAR(50)
const form = ref({
  id_inscripcion:    null,
  numero_control:    '',
  id_alumno:         null,
  id_grupo:          '',
  fecha_inscripcion: new Date().toISOString().split('T')[0],
  estatus:           'Activo'
})

const notificacion = ref({ visible:false, mensaje:'', tipo:'exito' })
let t=null
const mostrarNotificacion = (m,tipo='exito') => {
  if(t) clearTimeout(t)
  notificacion.value={visible:true,mensaje:m,tipo}
  t=setTimeout(()=>{notificacion.value.visible=false},3500)
}

const puedeGuardar = computed(()=>{
  if (esEdicion.value) return true
  return !!alumnoEncontrado.value && !!form.value.id_grupo && !!form.value.fecha_inscripcion
})

/*
 * GET /api/grupos/disponibles
 * Solo grupos con estatus=true y cupo disponible
 * Respuesta: [{ id_grupo, clave_grupo, nombre_materia, nombre_periodo }]
 */
const cargarGrupos = async () => {
  try { const r=await fetch(`${API}/grupos/disponibles`); if(r.ok) gruposDisponibles.value=await r.json() }
  catch { console.error('grupos') }
}

/*
 * GET /api/inscripciones/:id_inscripcion
 * JOIN: inscripcion -> alumno(numero_control) -> persona(nombre) -> carrera(nombre)
 *                   -> grupo(clave_grupo)    -> materia(nombre) -> periodo(nombre_periodo) -> docente -> aula
 * Respuesta: {
 *   id_inscripcion, id_alumno, numero_control, nombre_alumno, nombre_carrera,
 *   id_grupo, clave_grupo, nombre_materia, nombre_periodo,
 *   fecha_inscripcion, estatus
 * }
 */
const cargarInscripcion = async () => {
  cargando.value=true
  try {
    const r=await fetch(`${API}/inscripciones/${idParam}`)
    if(!r.ok) throw new Error()
    const d=await r.json()
    form.value={ id_inscripcion:d.id_inscripcion, numero_control:d.numero_control, id_alumno:d.id_alumno, id_grupo:d.id_grupo, fecha_inscripcion:d.fecha_inscripcion, estatus:d.estatus }
    alumnoEncontrado.value={ nombre_alumno:d.nombre_alumno, nombre_carrera:d.nombre_carrera }
    grupoTexto.value=`${d.clave_grupo} — ${d.nombre_materia} | ${d.nombre_periodo}`
    console.log('Inscripcion cargada')
  } catch { mostrarNotificacion('No se pudo cargar la inscripcion.','error') }
  finally { cargando.value=false }
}

onMounted(async () => {
  if (!esEdicion.value) await cargarGrupos()
  else await cargarInscripcion()
})

/*
 * GET /api/alumnos/control/:numero_control
 * Respuesta: { id_alumno, numero_control, nombre_alumno, nombre_carrera, id_carrera }
 */
const buscarAlumno = async () => {
  const nc=(form.value.numero_control||'').trim(); if(!nc) return
  buscandoAlumno.value=true; alumnoEncontrado.value=null; form.value.id_alumno=null
  try {
    const r=await fetch(`${API}/alumnos/control/${nc}`)
    if(!r.ok) throw new Error()
    const d=await r.json()
    alumnoEncontrado.value=d; form.value.id_alumno=d.id_alumno
    mostrarNotificacion('Alumno encontrado','exito')
  } catch { mostrarNotificacion(`No se encontro el No. de Control ${nc}`,'error') }
  finally { buscandoAlumno.value=false }
}

/*
 * POST /api/inscripciones
 * Body: { id_alumno, id_grupo, fecha_inscripcion, estatus }
 * El back valida UNIQUE(id_alumno, id_grupo) de la BD
 *
 * PUT /api/inscripciones/:id_inscripcion
 * Body: { estatus }  <- UNICO campo editable segun la BD
 */
const guardar = async () => {
  errores.value={id_grupo:'',fecha:''}
  if (!esEdicion.value) {
    if(!form.value.id_alumno)         { mostrarNotificacion('Busca un alumno primero.','error'); return }
    if(!form.value.id_grupo)          { errores.value.id_grupo='Selecciona un grupo.'; mostrarNotificacion('El grupo es requerido.','error'); return }
    if(!form.value.fecha_inscripcion) { errores.value.fecha='La fecha es requerida.'; mostrarNotificacion('La fecha es requerida.','error'); return }
  }
  guardando.value=true
  try {
    const url  = esEdicion.value ? `${API}/inscripciones/${form.value.id_inscripcion}` : `${API}/inscripciones`
    const meth = esEdicion.value ? 'PUT' : 'POST'
    const body = esEdicion.value
      ? { estatus:form.value.estatus }
      : { id_alumno:form.value.id_alumno, id_grupo:form.value.id_grupo, fecha_inscripcion:form.value.fecha_inscripcion, estatus:form.value.estatus }
    console.log(meth,url,body)
    const r=await fetch(url,{method:meth,headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(body)})
    const d=await r.json()
    if(!r.ok) throw new Error(JSON.stringify(d))
    mostrarNotificacion(esEdicion.value?'Inscripcion actualizada.':'Inscripcion creada.','exito')
    setTimeout(()=>router.push('/inscripciones'),1500)
  } catch(e) { console.error(e); mostrarNotificacion('Error al guardar. El alumno puede ya estar inscrito en ese grupo.','error') }
  finally { guardando.value=false }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');
.alumnos-page{--borde:#E5E7EB;--fondo:#F5F5F5;max-width:100%;background:var(--fondo);font-family:'Montserrat',sans-serif;padding-bottom:2rem}
.breadcrumb{display:flex;align-items:center;gap:.4rem;color:#6B7280;font-size:.88rem;margin-bottom:.75rem}
.bc-sep{color:#E5E7EB}.bc-link{color:#6B7280;text-decoration:none;transition:color .15s}.bc-link:hover{color:#1B396A}.bc-actual{color:#1B396A;font-weight:600}
.barra-carga-global{height:3px;background:transparent;border-radius:2px;margin-bottom:1rem;overflow:hidden;opacity:0;transition:opacity .3s}
.barra-carga-global.visible{opacity:1}
.barra-progreso{height:100%;width:40%;background:#1B396A;border-radius:2px;animation:deslizar 1.2s ease-in-out infinite}
@keyframes deslizar{0%{transform:translateX(-100%)}100%{transform:translateX(350%)}}
.notificacion-ui{display:flex;align-items:center;gap:10px;padding:12px 18px;border-radius:10px;font-size:.93rem;font-weight:500;margin-bottom:1rem;box-shadow:0 4px 12px rgba(0,0,0,.1)}
.notificacion-ui.exito{background:#DCFCE7;color:#16A34A;border:1px solid #86EFAC}
.notificacion-ui.error{background:#FEE2E2;color:#DC2626;border:1px solid #FCA5A5}
.notif-icono{width:20px;height:20px;flex-shrink:0}
.notif-fade-enter-active,.notif-fade-leave-active{transition:all .35s ease}
.notif-fade-enter-from,.notif-fade-leave-to{opacity:0;transform:translateY(-8px)}
.page-header{margin-bottom:1.4rem}
.page-title{color:#1A1A1A;font-size:1.75rem;font-weight:700;letter-spacing:-.02em;margin:0}
.form-card{background:#FFF;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.06);border:1px solid #E5E7EB;overflow:hidden;margin-bottom:1.5rem}
.seccion{padding:1.6rem 2rem}.seccion+.seccion{border-top:1px solid #E5E7EB}
.sec-titulo{display:flex;align-items:center;gap:8px;font-size:1rem;font-weight:700;color:#1B396A;margin-bottom:6px;font-family:'Montserrat',sans-serif}
.sec-linea{height:1px;background:#E5E7EB;margin-bottom:1.2rem}
.form-grupo{margin-bottom:1.2rem}
.form-grupo label{display:block;margin-bottom:6px;font-weight:600;font-size:.9rem;color:#1A1A1A;font-family:'Montserrat',sans-serif}
.f-input,.f-select{width:100%;padding:10px 14px;border:1.5px solid #E5E7EB;border-radius:8px;font-size:.93rem;background:#FFF;color:#1A1A1A;font-family:'Montserrat',sans-serif;outline:none;transition:border-color .2s,box-shadow .2s;box-sizing:border-box}
.f-input:focus,.f-select:focus{border-color:#1B396A;box-shadow:0 0 0 3px #DBEAFE}
.f-input::placeholder{color:#9CA3AF}
.f-readonly{padding:10px 14px;border:1.5px solid #E5E7EB;border-radius:8px;background:#F5F5F5;color:#6B7280;font-weight:600;font-size:.93rem;font-family:'Montserrat',sans-serif}
.error-campo{font-size:.78rem;color:#DC2626;font-weight:600;margin:4px 0 0;font-family:'Montserrat',sans-serif}
.bloque-alumno{display:flex;align-items:center;gap:10px;background:#F0FDF4;border:1px solid #86EFAC;border-radius:8px;padding:10px 14px;margin-top:8px}
.form-footer{padding:14px 2rem;background:#F5F5F5;border-top:1px solid #E5E7EB;display:flex;justify-content:flex-end;gap:10px}
.fbtn-cancel{padding:10px 22px;border-radius:8px;font-weight:600;font-size:.92rem;cursor:pointer;font-family:'Montserrat',sans-serif;background:#FFF;color:#1B396A;border:2px solid #1B396A;transition:background .15s}
.fbtn-cancel:hover:not(:disabled){background:#DBEAFE}.fbtn-cancel:disabled{opacity:.5;cursor:not-allowed}
.fbtn-save{display:inline-flex;align-items:center;gap:6px;padding:10px 28px;border-radius:8px;font-weight:600;font-size:.92rem;cursor:pointer;font-family:'Montserrat',sans-serif;background:#1B396A;color:#FFF;border:2px solid #1B396A;transition:background .15s;min-width:160px;justify-content:center}
.fbtn-save:hover:not(:disabled){background:#1D4ED8;border-color:#1D4ED8}.fbtn-save:disabled{opacity:.55;cursor:not-allowed}
.btn-buscar-modal{display:inline-flex;align-items:center;gap:6px;padding:10px 16px;border-radius:8px;font-weight:600;font-size:.9rem;cursor:pointer;font-family:'Montserrat',sans-serif;white-space:nowrap;background:#1B396A;color:#FFF;border:2px solid #1B396A;transition:background .15s}
.btn-buscar-modal:hover:not(:disabled){background:#1D4ED8}.btn-buscar-modal:disabled{opacity:.6;cursor:not-allowed}
.btn-encontrado{background:#16A34A!important;border-color:#16A34A!important}
.spinner-btn{display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,.4);border-top-color:#FFF;border-radius:50%;animation:girar .7s linear infinite;flex-shrink:0}
@keyframes girar{to{transform:rotate(360deg)}}
.pie-pagina{text-align:center;color:#9CA3AF;font-size:.82rem;padding-top:2rem;border-top:1px solid #E5E7EB;margin-top:1rem;font-family:'Montserrat',sans-serif}
@media(max-width:768px){.seccion{padding:1.2rem}.form-footer{padding:14px 1.2rem}}
</style>