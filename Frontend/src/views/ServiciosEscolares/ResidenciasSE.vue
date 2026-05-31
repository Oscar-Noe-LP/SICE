<template>
  <MainLayout>
    <div class="residencias-page">
      <!-- Breadcrumb -->
      <div class="breadcrumb">
        <router-link to="/dashboard" class="breadcrumb-link">Inicio</router-link>
        <span class="sep">›</span>
        <router-link to="/servicios-escolares" class="breadcrumb-link">Servicios Escolares</router-link>
        <span class="sep">›</span>
        <span class="activo">Residencias</span>
      </div>

      <!-- Encabezado -->
      <div class="encabezado-seccion">
        <div>
          <h1 class="titulo-pagina">Residencias</h1>
          <p class="subtitulo">Gestión de residencias profesionales</p>
        </div>
        <button @click="abrirModalNuevo" class="btn-primario">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <path d="M12 4v16m8-8H4"/>
          </svg>
          Nueva Residencia
        </button>
      </div>

      <!-- Barra de carga -->
      <div class="barra-carga" :class="{ activa: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- Error de carga -->
      <div v-if="errorCarga" class="alerta-error-catalogos">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        {{ errorCarga }}
        <button @click="cargarResidencias" class="btn-reintentar">Reintentar</button>
      </div>

      <!-- Tabla de residencias -->
      <div class="tabla-card">
        <div class="tabla-encabezado">
          <h3 class="seccion-titulo sin-margen">Listado de Residencias</h3>
          <span class="tabla-contador">{{ residencias.length }} registro(s)</span>
        </div>
        <div class="tabla-scroll">
          <table class="tabla-califs">
            <thead>
              <tr>
                <th>Alumno</th>
                <th>Empresa</th>
                <th>Asesor</th>
                <th class="centrado">Estado</th>
                <th class="centrado">Fecha Inicio</th>
                <th class="centrado">Fecha Fin</th>
                <th class="centrado">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="residencia in residencias" :key="residencia.id">
                <td>
                  <div class="alumno-info">
                    <div class="alumno-avatar">{{ iniciales(residencia.alumno) }}</div>
                    <span class="alumno-nombre">{{ residencia.alumno }}</span>
                  </div>
                </td>
                <td>{{ residencia.empresa }}</td>
                <td>{{ residencia.asesor || 'Sin asignar' }}</td>
                <td class="centrado">
                  <span class="badge-estado" :class="claseEstado(residencia.estado)">
                    {{ residencia.estado }}
                  </span>
                </td>
                <td class="centrado">{{ residencia.fecha_inicio }}</td>
                <td class="centrado">{{ residencia.fecha_fin || '—' }}</td>
                <td class="centrado acciones-cell">
                  <button @click="verDetalle(residencia)" class="btn-accion ver" title="Ver detalle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                  </button>
                </td>
              </tr>
              <tr v-if="!cargando && residencias.length === 0">
                <td colspan="7" class="sin-resultados">
                  <div class="sin-resultados-inner">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#E5E7EB" stroke-width="1.5" width="48" height="48">
                      <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <p>No se encontraron residencias</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Nueva Residencia -->
    <Teleport to="body">
      <div v-if="showModalNuevo" class="modal-overlay" @click.self="cerrarModalNuevo">
        <div class="modal-content">
          <div class="modal-header">
            <h3>Nueva Residencia</h3>
            <button @click="cerrarModalNuevo" class="modal-close-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-grupo">
              <label>Alumno <span class="obligatorio">*</span></label>
              <input v-model="formNuevo.alumno" type="text" class="modal-input" placeholder="Nombre o No. Control">
            </div>
            <div class="form-grupo">
              <label>Empresa <span class="obligatorio">*</span></label>
              <input v-model="formNuevo.empresa" type="text" class="modal-input" placeholder="Nombre de la empresa">
            </div>
            <div class="form-grupo">
              <label>Asesor</label>
              <input v-model="formNuevo.asesor" type="text" class="modal-input" placeholder="Nombre del asesor">
            </div>
            <div class="form-grupo-doble">
              <div class="form-grupo">
                <label>Fecha Inicio <span class="obligatorio">*</span></label>
                <input v-model="formNuevo.fecha_inicio" type="date" class="modal-input">
              </div>
              <div class="form-grupo">
                <label>Fecha Fin</label>
                <input v-model="formNuevo.fecha_fin" type="date" class="modal-input">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="cerrarModalNuevo" class="btn-cancelar">Cancelar</button>
            <button @click="guardarResidencia" class="btn-primario" :disabled="cargando">
              {{ cargando ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modal Detalle -->
    <Teleport to="body">
      <div v-if="showModalDetalle && residenciaDetalle" class="modal-overlay" @click.self="cerrarModalDetalle">
        <div class="modal-content modal-detalle">
          <div class="modal-header">
            <h3>Detalle de Residencia</h3>
            <button @click="cerrarModalDetalle" class="modal-close-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <div class="modal-body">
            <div class="detalle-fila">
              <span class="detalle-etiqueta">Alumno</span>
              <span class="detalle-valor">{{ residenciaDetalle.alumno }}</span>
            </div>
            <div class="detalle-fila">
              <span class="detalle-etiqueta">Empresa</span>
              <span class="detalle-valor">{{ residenciaDetalle.empresa }}</span>
            </div>
            <div class="detalle-fila">
              <span class="detalle-etiqueta">Asesor</span>
              <span class="detalle-valor">{{ residenciaDetalle.asesor || 'Sin asignar' }}</span>
            </div>
            <div class="detalle-fila">
              <span class="detalle-etiqueta">Estado</span>
              <span class="detalle-valor">
                <span class="badge-estado" :class="claseEstado(residenciaDetalle.estado)">
                  {{ residenciaDetalle.estado }}
                </span>
              </span>
            </div>
            <div class="detalle-fila">
              <span class="detalle-etiqueta">Fecha Inicio</span>
              <span class="detalle-valor">{{ residenciaDetalle.fecha_inicio }}</span>
            </div>
            <div class="detalle-fila">
              <span class="detalle-etiqueta">Fecha Fin</span>
              <span class="detalle-valor">{{ residenciaDetalle.fecha_fin || '—' }}</span>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="cerrarModalDetalle" class="btn-cancelar">Cerrar</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Toast -->
    <transition name="toast-slide">
      <div v-if="toast.visible" class="toast" :class="toast.tipo">
        <span class="toast-icono">
          <svg v-if="toast.tipo === 'exito'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg>
          <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </span>
        {{ toast.mensaje }}
      </div>
    </transition>
  </MainLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'
import api from '@/api/axios'

// Estado
const residencias = ref([])
const cargando = ref(false)
const errorCarga = ref('')

// Modal nuevo
const showModalNuevo = ref(false)
const formNuevo = reactive({
  alumno: '',
  empresa: '',
  asesor: '',
  fecha_inicio: '',
  fecha_fin: ''
})

// Modal detalle
const showModalDetalle = ref(false)
const residenciaDetalle = ref(null)

// Toast
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerToast = null
const mostrarToast = (mensaje, tipo = 'exito') => {
  if (timerToast) clearTimeout(timerToast)
  toast.value = { visible: true, mensaje, tipo }
  timerToast = setTimeout(() => { toast.value.visible = false }, 3500)
}

// Utilidades
const iniciales = (nombre) => {
  if (!nombre) return '?'
  return nombre.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase()
}

const claseEstado = (estado) => {
  if (!estado) return ''
  const e = estado.toLowerCase()
  if (e === 'activo') return 'excelente'
  if (e === 'finalizado') return 'bien'
  if (e === 'pendiente') return 'regular'
  return ''
}

// Cargar residencias
const cargarResidencias = async () => {
  cargando.value = true
  errorCarga.value = ''
  try {
    const { data } = await api.get('/residencias')
    residencias.value = data.residencias ?? data
  } catch (e) {
    console.error(e)
    errorCarga.value = 'No se pudieron cargar las residencias.'
    residencias.value = []
  } finally {
    cargando.value = false
  }
}

// Nueva residencia
const abrirModalNuevo = () => {
  formNuevo.alumno = ''
  formNuevo.empresa = ''
  formNuevo.asesor = ''
  formNuevo.fecha_inicio = ''
  formNuevo.fecha_fin = ''
  showModalNuevo.value = true
}

const cerrarModalNuevo = () => {
  showModalNuevo.value = false
}

const guardarResidencia = async () => {
  if (!formNuevo.alumno || !formNuevo.empresa || !formNuevo.fecha_inicio) {
    mostrarToast('Completa los campos obligatorios', 'error')
    return
  }
  cargando.value = true
  try {
    await api.post('/residencias', formNuevo)
    mostrarToast('Residencia registrada correctamente')
    cerrarModalNuevo()
    await cargarResidencias()
  } catch (e) {
    console.error(e)
    mostrarToast('Error al registrar residencia', 'error')
  } finally {
    cargando.value = false
  }
}

// Detalle
const verDetalle = async (residencia) => {
  try {
    const { data } = await api.get(`/residencias/${residencia.id}`)
    residenciaDetalle.value = data.residencia ?? data
    showModalDetalle.value = true
  } catch (e) {
    console.error(e)
    mostrarToast('Error al cargar el detalle', 'error')
  }
}

const cerrarModalDetalle = () => {
  showModalDetalle.value = false
  residenciaDetalle.value = null
}

onMounted(() => {
  cargarResidencias()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.residencias-page {
  width: 100%;
  max-width: 100%;
  font-family: 'Montserrat', sans-serif;
  box-sizing: border-box;
  padding: 1rem 1rem 2rem;
  text-transform: uppercase; 
   

}

/* Breadcrumb */
.breadcrumb { color: #6B7280; font-size: 0.875rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem; }
.breadcrumb .sep { color: #E5E7EB; }
.breadcrumb .activo { color: #1B396A; font-weight: 600; }
.breadcrumb-link { color: #6B7280; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #1B396A; }

/* Encabezado */
.encabezado-seccion { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.titulo-pagina { color: #1A1A1A; font-size: 1.9rem; font-weight: 800; margin: 0 0 0.25rem; font-family: 'Montserrat', sans-serif;}
.subtitulo { color: #6B7280; font-size: 0.9rem; margin: 0; }

/* Barra de carga */
.barra-carga { position: fixed; top: 74px; left: 0; right: 0; height: 3px; z-index: 1001; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: linear-gradient(90deg, #1B396A, #1D4ED8, #1B396A); background-size: 200% 100%; animation: carga-anim 1.4s linear infinite; width: 100%; }
@keyframes carga-anim { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* Error */
.alerta-error-catalogos { display: flex; align-items: center; gap: 0.6rem; background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; border-radius: 10px; padding: 0.8rem 1.2rem; margin-bottom: 1rem; font-size: 0.875rem; font-weight: 600; }
.btn-reintentar { margin-left: auto; background: #DC2626; color: #fff; border: none; border-radius: 6px; padding: 4px 12px; font-size: 0.8rem; font-weight: 700; cursor: pointer; }

/* Tabla */
.tabla-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 1.5rem; width: 100%; box-sizing: border-box; }
.tabla-encabezado { padding: 1rem 1.4rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #E5E7EB; }
.tabla-contador { font-size: 0.8rem; color: #6B7280; background: #F5F5F5; border: 1px solid #E5E7EB; padding: 4px 10px; border-radius: 20px; }
.tabla-scroll { overflow-x: auto; width: 100%; }
.tabla-califs { width: 100%; border-collapse: collapse; }
.tabla-califs th { background: #F5F5F5; padding: 10px 14px; font-size: 0.78rem; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E5E7EB; text-align: left; white-space: nowrap; }
.tabla-califs th.centrado { text-align: center; }
.tabla-califs td { padding: 8px 14px; border-bottom: 1px solid #E5E7EB; vertical-align: middle; font-size: 0.875rem; color: #1A1A1A; }
.tabla-califs td.centrado { text-align: center; }
.tabla-califs tr:hover { background: #F5F5F5; }
.tabla-califs tr:last-child td { border-bottom: none; }

/* Sección título (para alinear con AnaliticaView) */
.seccion-titulo.sin-margen { margin: 0; font-size: 1rem; font-weight: 700; color: #1A1A1A; font-family: 'Montserrat', sans-serif;}

/* Badges */
.badge-estado { padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
.badge-estado.excelente { background: #DCFCE7; color: #16A34A; }
.badge-estado.bien { background: #DBEAFE; color: #1B396A; }
.badge-estado.regular { background: #FEF3C7; color: #F59E0B; }
.badge-estado.reprobado { background: #FEF2F2; color: #DC2626; }
.badge-estado.sin-calificar { background: #F3F4F6; color: #6B7280; }

/* Alumno info */
.alumno-info { display: flex; align-items: center; gap: 0.6rem; }
.alumno-avatar { width: 30px; height: 30px; border-radius: 50%; background: #DBEAFE; color: #1B396A; font-weight: 800; font-size: 0.72rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.alumno-nombre { font-weight: 600; color: #1A1A1A; }

/* Acciones */
.acciones-cell { text-align: center; }
.btn-accion { width: 30px; height: 30px; border-radius: 7px; border: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.15s; }
.btn-accion.ver { background: #DBEAFE; color: #1B396A; }
.btn-accion.ver:hover { background: #BFDBFE; transform: scale(1.1); }

.sin-resultados { padding: 3rem; text-align: center; }
.sin-resultados-inner { display: flex; flex-direction: column; align-items: center; gap: 0.75rem; }
.sin-resultados-inner p { color: #9CA3AF; font-size: 0.9rem; margin: 0; }

/* Modales */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 2000; backdrop-filter: blur(4px); }
.modal-content { background: #FFFFFF; border-radius: 20px; width: 560px; max-width: 90%; max-height: 85vh; overflow-y: auto; box-shadow: 0 25px 50px rgba(0,0,0,0.25); border: 1px solid #E5E7EB; }
.modal-header { background: #1B396A; color: white; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-radius: 20px 20px 0 0; }
.modal-header h3 { margin: 0; font-size: 1.1rem; font-weight: 700; }
.modal-close-btn { background: rgba(255,255,255,0.2); border: none; color: white; cursor: pointer; padding: 5px; border-radius: 8px; }
.modal-close-btn:hover { background: rgba(255,255,255,0.4); }
.modal-body { padding: 1.5rem; }
.modal-footer { padding: 1rem 1.5rem; background: #F9FAFB; display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid #E5E7EB; border-radius: 0 0 20px 20px; }

.form-grupo { margin-bottom: 1rem; }
.form-grupo label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.85rem; color: #1A1A1A; }
.obligatorio { color: #DC2626; }
.modal-input { width: 100%; padding: 9px 12px; border: 1.5px solid #E5E7EB; border-radius: 8px; font-size: 0.9rem; font-family: inherit; background: #FFFFFF; box-sizing: border-box; }
.modal-input:focus { border-color: #1B396A; outline: none; box-shadow: 0 0 0 3px #DBEAFE; }
.form-grupo-doble { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

.detalle-fila { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #E5E7EB; }
.detalle-etiqueta { font-weight: 600; color: #6B7280; }
.detalle-valor { font-weight: 500; color: #1A1A1A; }

/* Botones */
.btn-primario { background: #1B396A; color: #FFFFFF; border: none; padding: 10px 18px; border-radius: 10px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 6px; cursor: pointer; transition: background 0.2s; white-space: nowrap; font-family: 'Montserrat', sans-serif;}
.btn-primario:hover:not(:disabled) { background: #1D4ED8; }
.btn-primario:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }
.btn-cancelar { background: #FFFFFF; color: #6B7280; border: 1px solid #E5E7EB; padding: 10px 1.4rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; cursor: pointer; }
.btn-cancelar:hover { background: #F5F5F5; }

/* Toast */
.toast { position: fixed; bottom: 2rem; right: 2rem; padding: 0.9rem 1.4rem; border-radius: 10px; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 0.6rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 3000; max-width: 380px; color: #FFFFFF; }
.toast.exito { background: #1B396A; }
.toast.error { background: #DC2626; }
.toast-slide-enter-active { transition: all 0.3s ease; }
.toast-slide-leave-active { transition: all 0.25s ease; }
.toast-slide-enter-from { transform: translateY(20px); opacity: 0; }
.toast-slide-leave-to { transform: translateX(100%); opacity: 0; }
</style>