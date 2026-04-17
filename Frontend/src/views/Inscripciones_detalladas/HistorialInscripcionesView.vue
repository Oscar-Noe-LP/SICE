<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="alumnos-page">

      <div class="page-header">
        <h1 class="page-title">Historial de Inscripciones</h1>
        <button v-if="alumnoSeleccionado" class="btn-exportar" @click="exportarHistorial" :disabled="exportando">
          <span v-if="exportando" class="spinner-btn" style="border-top-color:#1B396A;border-color:rgba(27,57,106,.3)"></span>
          <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:15px;height:15px">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          {{ exportando ? 'Exportando...' : 'Exportar historial' }}
        </button>
      </div>

      <div class="barra-carga-global" :class="{ visible: buscando }"><div class="barra-progreso"></div></div>

      <transition name="notif-fade">
        <div v-if="notificacion.visible" class="notificacion-ui" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo==='exito'" xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- Buscador: solo aplica al presionar Enter o Buscar -->
      <div class="table-container" style="padding:1.6rem 2rem;margin-bottom:1.2rem">
        <div style="display:flex;align-items:center;gap:8px;font-size:1rem;font-weight:700;color:#1B396A;margin-bottom:6px;font-family:'Montserrat',sans-serif">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#1B396A" stroke-width="2" style="width:20px;height:20px;flex-shrink:0">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          Buscar Alumno
        </div>
        <div style="height:1px;background:#E5E7EB;margin-bottom:1.2rem"></div>

        <div class="filters-bar" style="margin-bottom:0">
          <div class="search-group" style="flex:1;max-width:500px">
            <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input v-model="busquedaInput" type="text" class="search-input"
                   placeholder="Número de control o nombre — presiona Enter para buscar"
                   @keydown.enter="buscarAlumno" @keydown.escape="limpiarBusqueda">
            <span v-if="alumnoSeleccionado" class="badge-busqueda-activa">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:12px;height:12px;stroke:#1B396A">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              </svg>
            </span>
          </div>
          <button class="btn-buscar-tabla" @click="buscarAlumno" :disabled="!busquedaInput.trim() || buscando">
            <span v-if="buscando" class="spinner-btn"></span>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:15px;height:15px;stroke:#fff">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Buscar
          </button>
          <button v-if="alumnoSeleccionado" class="btn-limpiar" @click="limpiarBusqueda">
            <svg xmlns="http://www.w3.org/2000/svg" class="reset-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Limpiar
          </button>
        </div>

        <transition name="notif-fade">
          <div v-if="alumnoSeleccionado" style="display:flex;align-items:center;gap:10px;background:#F0FDF4;border:1px solid #86EFAC;border-radius:8px;padding:10px 14px;margin-top:1rem">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#16A34A" stroke-width="2" style="width:22px;height:22px;flex-shrink:0">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
              <p style="margin:0 0 2px;font-weight:700;font-size:.92rem;color:#1A1A1A;font-family:'Montserrat',sans-serif">{{ alumnoSeleccionado.nombre }}</p>
              <p style="margin:0;font-size:.8rem;color:#6B7280;font-family:'Montserrat',sans-serif">{{ alumnoSeleccionado.carrera }} · {{ alumnoSeleccionado.numero_control }}</p>
            </div>
          </div>
        </transition>
      </div>

      <!-- Estado inicial -->
      <div v-if="!alumnoSeleccionado && !buscando" class="estado-vacio" style="padding:4rem 2rem">
        <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <h3>Consulta el historial de un alumno</h3>
        <p>Ingresa el número de control o nombre y presiona Enter para ver sus inscripciones</p>
      </div>

      <!-- Historial -->
      <transition name="notif-fade">
        <div v-if="alumnoSeleccionado && !buscando">
          <div v-if="periodos.length === 0" class="table-container">
            <div class="estado-vacio">
              <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
              <h3>Sin historial</h3>
              <p>No hay inscripciones registradas para este alumno.</p>
            </div>
          </div>

          <div v-else style="display:flex;flex-direction:column;gap:.75rem">
            <div v-for="per in periodos" :key="per.id_inscripcion" class="table-container">

              <!-- Header acordeón -->
              <div @click="togglePeriodo(per.id_inscripcion)"
                   style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;cursor:pointer;user-select:none;background:#F5F5F5;border-bottom:1px solid #E5E7EB">
                <div style="display:flex;align-items:center;gap:12px">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#1B396A" stroke-width="2" style="width:20px;height:20px;flex-shrink:0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  <div>
                    <p style="font-size:.95rem;font-weight:700;color:#1A1A1A;margin:0 0 2px;font-family:'Montserrat',sans-serif">{{ per.nombre_periodo }}</p>
                    <p style="font-size:.82rem;color:#6B7280;margin:0;font-family:'Montserrat',sans-serif">
                      {{ per.materias.length }} materias · {{ per.materias.filter(m=>m.estado==='acreditada').length }} acreditadas · {{ per.total_creditos }} créditos
                    </p>
                  </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                  <span v-if="per.es_activo" class="estatus-badge activo">Periodo activo</span>
                  <span class="estatus-badge" :class="claseEst(per.estado_inscripcion)">{{ etiqEst(per.estado_inscripcion) }}</span>
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#6B7280" stroke-width="2"
                       :style="{width:'18px',height:'18px',transition:'transform .25s',transform:abiertos.includes(per.id_inscripcion)?'rotate(180deg)':'rotate(0deg)'}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                  </svg>
                </div>
              </div>

              <!-- Tabla acordeón -->
              <transition name="notif-fade">
                <div v-if="abiertos.includes(per.id_inscripcion)" style="overflow-x:auto">
                  <table class="alumnos-table">
                    <thead>
                      <tr><th>Clave</th><th>Materia</th><th>Créditos</th><th>Grupo</th><th>Calificación</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                      <tr v-for="mat in per.materias" :key="mat.id_detalle">
                        <td style="font-family:monospace;font-size:.85rem;color:#6B7280">{{ mat.clave_materia }}</td>
                        <td style="font-weight:500">{{ mat.nombre_materia }}</td>
                        <td style="text-align:center">{{ mat.creditos }}</td>
                        <td style="font-size:.85rem;color:#6B7280">{{ mat.grupo }}</td>
                        <td style="text-align:center">
                          <span v-if="mat.calificacion!=null" class="estatus-badge" :style="estiloCalificacion(mat.calificacion)">{{ mat.calificacion }}</span>
                          <span v-else style="color:#9CA3AF;font-weight:600">—</span>
                        </td>
                        <td><span class="estatus-badge" :class="claseEstMat(mat.estado)">{{ etiqEstMat(mat.estado) }}</span></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </transition>

            </div>
          </div>
        </div>
      </transition>

      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

const API = 'http://localhost:8000/api'

const busquedaInput      = ref('')
const buscando           = ref(false)
const exportando         = ref(false)
const alumnoSeleccionado = ref(null)
const periodos           = ref([])
const abiertos           = ref([])

const notificacion = ref({ visible:false, mensaje:'', tipo:'exito' })
let t = null
const mostrarNotificacion = (msg, tipo='exito') => {
  if(t) clearTimeout(t)
  notificacion.value = { visible:true, mensaje:msg, tipo }
  t = setTimeout(()=>{ notificacion.value.visible=false },3500)
}

/* GET /api/alumnos/control/:q  —  { id_alumno, numero_control, nombre, carrera }
   GET /api/inscripciones/historial/:id_alumno  —  [{
     id_inscripcion, nombre_periodo, es_activo, estado_inscripcion, total_creditos,
     materias:[{ id_detalle, clave_materia, nombre_materia, creditos, grupo, calificacion, estado }]
   }] */
const buscarAlumno = async () => {
  const q = (busquedaInput.value||'').trim()
  if(!q) return
  buscando.value=true; alumnoSeleccionado.value=null; periodos.value=[]; abiertos.value=[]
  try {
    const rA = await fetch(`${API}/alumnos/control/${encodeURIComponent(q)}`)
    if(!rA.ok) throw new Error()
    const alumno = await rA.json()
    alumnoSeleccionado.value = alumno
    const rH = await fetch(`${API}/inscripciones/historial/${alumno.id_alumno}`)
    if(!rH.ok) throw new Error()
    const data = await rH.json()
    periodos.value = data
    if(data.length>0) abiertos.value=[data[0].id_inscripcion]
    mostrarNotificacion('Historial cargado correctamente','exito')
  } catch {
    mostrarNotificacion(`No se encontró ningún alumno con: ${q}`,'error')
    alumnoSeleccionado.value=null; periodos.value=[]
  } finally { buscando.value=false }
}

const limpiarBusqueda = () => {
  busquedaInput.value='' ; alumnoSeleccionado.value=null ; periodos.value=[] ; abiertos.value=[]
}

/* GET /api/inscripciones/historial/:id/exportar  —  blob PDF */
const exportarHistorial = async () => {
  if(!alumnoSeleccionado.value) return
  exportando.value=true
  try {
    const r = await fetch(`${API}/inscripciones/historial/${alumnoSeleccionado.value.id_alumno}/exportar`)
    if(!r.ok) throw new Error()
    const blob=await r.blob(), url=URL.createObjectURL(blob)
    const a=document.createElement('a'); a.href=url; a.download=`historial_${alumnoSeleccionado.value.numero_control}.pdf`; a.click()
    URL.revokeObjectURL(url)
    mostrarNotificacion('Historial exportado','exito')
  } catch { mostrarNotificacion('No se pudo exportar.','error') }
  finally { exportando.value=false }
}

const togglePeriodo = id => {
  const i=abiertos.value.indexOf(id)
  if(i===-1) abiertos.value.push(id) ; else abiertos.value.splice(i,1)
}

const claseEst    = e => ({ activo:e==='activa','baja-temporal':e==='baja','baja-definitiva':e==='cambio_pendiente' })
const etiqEst     = e => ({activa:'Activa',baja:'Baja',cambio_pendiente:'Cambio pendiente'}[e]||e)
const claseEstMat = e => ({ activo:e==='acreditada','baja-temporal':e==='reprobada','baja-definitiva':e==='baja',pendiente:e==='inscrita' })
const etiqEstMat  = e => ({acreditada:'Acreditada',reprobada:'Reprobada',inscrita:'En curso',baja:'Baja'}[e]||e)
const estiloCalificacion = c => {
  if(c>=9) return 'background:#DCFCE7;color:#16A34A'
  if(c>=7) return 'background:#DBEAFE;color:#1B396A'
  if(c>=6) return 'background:#FEF3C7;color:#F59E0B'
  return 'background:#FEE2E2;color:#DC2626'
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');
.alumnos-page { --azul:#1B396A;--azul-hover:#1D4ED8;--borde:#E5E7EB;--fondo:#F5F5F5;--texto:#1A1A1A;--gris:#6B7280; max-width:100%;background:var(--fondo);font-family:'Montserrat',sans-serif;padding-bottom:2rem; }
.page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;flex-wrap:wrap;gap:.75rem; }
.page-title  { color:#1A1A1A;font-size:1.75rem;font-weight:700;letter-spacing:-.02em;margin:0; }
.barra-carga-global { height:3px;background:transparent;border-radius:2px;margin-bottom:1rem;overflow:hidden;transition:opacity .3s;opacity:0; }
.barra-carga-global.visible { opacity:1; }
.barra-progreso { height:100%;width:40%;background:#1B396A;border-radius:2px;animation:deslizar 1.2s ease-in-out infinite; }
@keyframes deslizar { 0%{transform:translateX(-100%)} 100%{transform:translateX(350%)} }
.notificacion-ui { display:flex;align-items:center;gap:10px;padding:12px 18px;border-radius:10px;font-size:.93rem;font-weight:500;margin-bottom:1rem;box-shadow:0 4px 12px rgba(0,0,0,.1); }
.notificacion-ui.exito { background:#DCFCE7;color:#16A34A;border:1px solid #86EFAC; }
.notificacion-ui.error { background:#FEE2E2;color:#DC2626;border:1px solid #FCA5A5; }
.notif-icono { width:20px;height:20px;flex-shrink:0; }
.notif-fade-enter-active,.notif-fade-leave-active { transition:all .35s ease; }
.notif-fade-enter-from,.notif-fade-leave-to { opacity:0;transform:translateY(-8px); }
.filters-bar { display:flex;align-items:center;gap:.75rem;flex-wrap:wrap; }
.search-group { position:relative;flex:0 0 340px;min-width:240px; }
.search-input { width:100%;padding:10px 14px 10px 42px;border:1px solid #E5E7EB;border-radius:8px;font-size:.93rem;background:#FFFFFF;color:#1A1A1A;font-family:'Montserrat',sans-serif;outline:none;transition:border-color .2s,box-shadow .2s;box-sizing:border-box; }
.search-input:focus { border-color:#1B396A;box-shadow:0 0 0 3px #DBEAFE; }
.search-input::placeholder { color:#9CA3AF; }
.search-icon-svg { position:absolute;left:13px;top:50%;transform:translateY(-50%);width:18px;height:18px;stroke:#6B7280;pointer-events:none; }
.badge-busqueda-activa { position:absolute;right:10px;top:50%;transform:translateY(-50%);background:#DBEAFE;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center; }
.btn-buscar-tabla { display:inline-flex;align-items:center;gap:6px;padding:10px 16px;border-radius:8px;font-weight:600;font-size:.92rem;cursor:pointer;font-family:'Montserrat',sans-serif;white-space:nowrap;background:#1B396A;color:#FFFFFF;border:2px solid #1B396A;transition:background .15s; }
.btn-buscar-tabla:hover:not(:disabled) { background:#1D4ED8;border-color:#1D4ED8; }
.btn-buscar-tabla:disabled { opacity:.45;cursor:not-allowed; }
.btn-limpiar { display:flex;align-items:center;gap:6px;background:#FFFFFF;color:#1A1A1A;border:1px solid #E5E7EB;padding:10px 16px;border-radius:8px;font-weight:600;cursor:pointer;font-size:.92rem;white-space:nowrap;font-family:'Montserrat',sans-serif;transition:background .15s; }
.btn-limpiar:hover { background:#F5F5F5; }
.reset-icon { width:16px;height:16px;stroke:#6B7280; }
.table-container { background:#FFFFFF;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.05);border:1px solid #E5E7EB; }
.alumnos-table { width:100%;border-collapse:collapse; }
.alumnos-table th { background:#F5F5F5;padding:12px 16px;text-align:left;font-weight:600;font-size:.88rem;color:#1A1A1A;border-bottom:1px solid #E5E7EB;font-family:'Montserrat',sans-serif;white-space:nowrap; }
.alumnos-table td { padding:11px 16px;border-bottom:1px solid #E5E7EB;color:#1A1A1A;font-size:.93rem;font-family:'Montserrat',sans-serif; }
.alumnos-table tbody tr:hover { background:#F8FAFC; }
.alumnos-table tbody tr:last-child td { border-bottom:none; }
.estado-vacio { text-align:center;padding:3.5rem 2rem;color:#6B7280; }
.icono-vacio  { width:56px;height:56px;stroke:#9CA3AF;margin-bottom:12px; }
.estado-vacio h3 { font-size:1.2rem;color:#1A1A1A;margin:0 0 6px;font-family:'Montserrat',sans-serif; }
.estado-vacio p  { font-size:.93rem;margin:0;font-family:'Montserrat',sans-serif; }
.estatus-badge { display:inline-block;padding:4px 12px;border-radius:20px;font-size:.83rem;font-weight:600;font-family:'Montserrat',sans-serif; }
.estatus-badge.activo          { background:#DCFCE7;color:#16A34A; }
.estatus-badge.baja-temporal   { background:#FEE2E2;color:#DC2626; }
.estatus-badge.baja-definitiva { background:#FEF3C7;color:#F59E0B; }
.estatus-badge.pendiente       { background:#FEF3C7;color:#F59E0B; }
.btn-exportar { display:inline-flex;align-items:center;gap:8px;background:#DBEAFE;color:#1B396A;border:2px solid #DBEAFE;padding:10px 18px;border-radius:8px;font-weight:600;font-size:.9rem;cursor:pointer;font-family:'Montserrat',sans-serif;transition:background .2s; }
.btn-exportar:hover:not(:disabled) { background:#BFDBFE;border-color:#BFDBFE; }
.btn-exportar:disabled { opacity:.6;cursor:not-allowed; }
.spinner-btn { display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,.4);border-top-color:#FFFFFF;border-radius:50%;animation:girar .7s linear infinite;flex-shrink:0; }
@keyframes girar { to{transform:rotate(360deg)} }
.pie-pagina { text-align:center;color:#9CA3AF;font-size:.82rem;padding-top:2rem;border-top:1px solid #E5E7EB;margin-top:1.5rem;font-family:'Montserrat',sans-serif; }
@media(max-width:640px){ .page-header{flex-direction:column;align-items:flex-start;} .filters-bar{flex-direction:column;} .search-group{width:100%;flex:none;} }
</style>