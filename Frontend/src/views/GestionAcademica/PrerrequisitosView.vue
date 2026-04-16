<template>
  <MainLayout>
    <div class="pre-page">
      <nav class="breadcrumb">
        <span class="breadcrumb-link" @click="$router.push('/gestion-academica')">Gestión Académica</span>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-actual">Prerrequisitos</span>
      </nav>
      <div class="page-header">
        <h1 class="page-title">Prerrequisitos</h1>
        <span class="page-subtitle">{{ prerrequisitos.length }} relación(es) registrada(s)</span>
      </div>
      <div class="barra-carga" :class="{ visible: cargando }"><div class="barra-progreso"></div></div>
      <transition name="toast-slide">
        <div v-if="notificacion.visible" class="toast" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo === 'exito'" xmlns="http://www.w3.org/2000/svg" class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>
      <div class="filters-bar">
        <div class="search-group">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
          <input type="text" placeholder="Buscar por nombre de materia..." v-model="busqueda" class="search-input" @keydown.escape="busqueda = ''">
        </div>
        <button class="btn-nuevo" @click="showModal = true">+ Agregar prerrequisito</button>
      </div>
      <div class="table-container">
        <div v-if="cargando && prerrequisitos.length === 0" class="estado-cargando"><div class="spinner-tabla"></div><p>Cargando registros...</p></div>
        <table v-else-if="prerrequisitorsFiltrados.length > 0" class="data-table">
          <thead><tr><th>Materia</th><th>Prerrequisito de</th><th>Acciones</th></tr></thead>
          <tbody>
            <tr v-for="pre in prerrequisitorsFiltrados" :key="`${pre.id_materia}-${pre.id_materia_prerrequisito}`">
              <td class="celda-nombre">{{ pre.materia?.nombre || '—' }}</td>
              <td>{{ pre.prerrequisito?.nombre || '—' }}</td>
              <td class="celda-acciones">
                <button class="btn-accion eliminar-pre" @click="solicitarEliminar(pre)">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  Eliminar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <h3>Sin resultados</h3>
          <p>No se encontraron prerrequisitos con los criterios aplicados.</p>
        </div>
      </div>
      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México | Todos los derechos reservados</footer>
    </div>

    <!-- Modal Agregar -->
    <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-content">
        <div class="modal-header"><h3>Nuevo Prerrequisito</h3><button @click="cerrarModal" class="btn-cerrar-modal">×</button></div>
        <div class="modal-body">
          <p class="modal-desc">Define qué materia necesita otra materia como prerrequisito antes de cursarse.</p>
          <div class="form-grupo">
            <label>Materia <span class="obligatorio">*</span></label>
            <select v-model="form.id_materia" class="modal-select" :class="{ 'borde-error': errors.id_materia }">
              <option value="">Seleccionar materia</option>
              <option v-for="m in materias" :key="m.id_materia" :value="m.id_materia">{{ m.clave }} - {{ m.nombre }}</option>
            </select>
            <small v-if="errors.id_materia" class="mensaje-error">{{ errors.id_materia }}</small>
          </div>
          <div class="form-grupo">
            <label>Tiene como prerrequisito a <span class="obligatorio">*</span></label>
            <select v-model="form.id_materia_prerrequisito" class="modal-select" :class="{ 'borde-error': errors.id_materia_prerrequisito }">
              <option value="">Seleccionar materia prerrequisito</option>
              <option v-for="m in materiasFiltradas" :key="m.id_materia" :value="m.id_materia">{{ m.clave }} - {{ m.nombre }}</option>
            </select>
            <small v-if="errors.id_materia_prerrequisito" class="mensaje-error">{{ errors.id_materia_prerrequisito }}</small>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">Cancelar</button>
          <button class="btn-guardar" @click="guardar" :disabled="guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            {{ guardando ? 'Guardando...' : 'Guardar' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Confirmar Eliminaar -->
    <div v-if="showModalEliminar" class="modal-overlay" @click.self="showModalEliminar = false">
      <div class="modal-content modal-confirmar">
        <div class="modal-header"><h3>Confirmar eliminación</h3><button @click="showModalEliminar = false" class="btn-cerrar-modal">×</button></div>
        <div class="modal-body confirmar-body">
          <svg xmlns="http://www.w3.org/2000/svg" class="confirmar-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
          <p>¿Deseas eliminar la relación de prerrequisito entre <strong>{{ preAEliminar?.materia?.nombre }}</strong> y <strong>{{ preAEliminar?.prerrequisito?.nombre }}</strong>?</p>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="showModalEliminar = false">Cancelar</button>
          <button class="btn-eliminar" @click="confirmarEliminar" :disabled="guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            {{ guardando ? 'Eliminando...' : 'Eliminar' }}
          </button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

const prerrequisitos = ref([])
const materias       = ref([])
const cargando       = ref(false)
const guardando      = ref(false)
const busqueda       = ref('')
const showModal      = ref(false)
const showModalEliminar = ref(false)
const preAEliminar   = ref(null)
const form = reactive({ id_materia: '', id_materia_prerrequisito: '' })
const errors = reactive({})
const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

const cargarDatos = async () => {
  cargando.value = true
  try {
    const [resP, resM] = await Promise.all([fetch('http://localhost:8000/api/prerrequisitos'), fetch('http://localhost:8000/api/materias')])
    if (resP.ok) prerrequisitos.value = await resP.json()
    if (resM.ok) materias.value = await resM.json()
  } catch { mostrarNotificacion('No se pudieron cargar los datos.', 'error') }
  finally { cargando.value = false }
}

onMounted(() => { cargarDatos() })

const normalize = (t) => t?.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '') ?? ''

const prerrequisitorsFiltrados = computed(() =>
  !busqueda.value ? prerrequisitos.value : prerrequisitos.value.filter(p =>
    normalize(p.materia?.nombre).includes(normalize(busqueda.value)) ||
    normalize(p.prerrequisito?.nombre).includes(normalize(busqueda.value))
  )
)

// Filtra materias disponibles para prerrequisito (excluye la materia ya seleccionada)
const materiasFiltradas = computed(() =>
  materias.value.filter(m => m.id_materia != form.id_materia)
)

const cerrarModal = () => { showModal.value = false; form.id_materia = ''; form.id_materia_prerrequisito = ''; Object.keys(errors).forEach(k => delete errors[k]) }
const solicitarEliminar = (pre) => { preAEliminar.value = pre; showModalEliminar.value = true }

const validar = () => {
  Object.keys(errors).forEach(k => delete errors[k])
  if (!form.id_materia)               errors.id_materia               = 'Selecciona una materia'
  if (!form.id_materia_prerrequisito) errors.id_materia_prerrequisito = 'Selecciona el prerrequisito'
  if (form.id_materia && form.id_materia_prerrequisito && form.id_materia == form.id_materia_prerrequisito) errors.id_materia_prerrequisito = 'Una materia no puede ser su propio prerrequisito'
  return Object.keys(errors).length === 0
}

const guardar = async () => {
  if (!validar()) return
  guardando.value = true
  try {
    const res = await fetch('http://localhost:8000/api/prerrequisitos', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ id_materia: form.id_materia, id_materia_prerrequisito: form.id_materia_prerrequisito })
    })
    if (!res.ok) throw new Error()
    await cargarDatos(); cerrarModal()
    mostrarNotificacion('Prerrequisito registrado correctamente.')
  } catch { mostrarNotificacion('Ocurrió un error al guardar.', 'error') }
  finally { guardando.value = false }
}

const confirmarEliminar = async () => {
  if (!preAEliminar.value) return
  guardando.value = true
  try {
    const res = await fetch(`http://localhost:8000/api/prerrequisitos/${preAEliminar.value.id_materia}/${preAEliminar.value.id_materia_prerrequisito}`, { method: 'DELETE', headers: { 'Accept': 'application/json' } })
    if (!res.ok) throw new Error()
    await cargarDatos(); showModalEliminar.value = false; preAEliminar.value = null
    mostrarNotificacion('Prerrequisito eliminado correctamente.')
  } catch { mostrarNotificacion('Ocurrió un error al eliminar.', 'error') }
  finally { guardando.value = false }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');
:root {
  --azul:        #1B396A;
  --azul-hover:  #1D4ED8;
  --azul-suave:  #DBEAFE;
  --borde:       #E5E7EB;
  --fondo:       #F5F5F5;
  --texto:       #1A1A1A;
  --gris:        #6B7280;
  --verde:       #16A34A;
  --rojo:        #DC2626;
  --amarillo:    #F59E0B;
}

.pre-page{--azul:#1B396A;--azul-hover:#1D4ED8;--azul-suave:#DBEAFE;--borde:#E5E7EB;--fondo:#F5F5F5;--texto:#1A1A1A;--gris:#6B7280;--verde:#16A34A;--rojo:#DC2626;width:100%;background:var(--fondo);font-family:'Montserrat',sans-serif;padding-bottom:2rem}
.breadcrumb{display:flex;align-items:center;gap:6px;color:var(--gris);font-size:0.88rem;margin-bottom:0.75rem}.breadcrumb-link{color:var(--azul);font-weight:500;cursor:pointer;transition:color 0.15s}.breadcrumb-link:hover{color:var(--azul-hover);text-decoration:underline}.breadcrumb-sep{color:#9CA3AF}.breadcrumb-actual{color:var(--gris);font-weight:600}
.page-header{display:flex;align-items:baseline;gap:1rem;margin-bottom:1.2rem}.page-title{color:var(--texto);font-size:1.75rem;font-weight:700;letter-spacing:-0.02em;margin:0}.page-subtitle{font-size:0.9rem;color:var(--gris);font-weight:500}
.barra-carga{height:3px;background:transparent;border-radius:2px;margin-bottom:1rem;overflow:hidden;opacity:0;transition:opacity 0.3s}.barra-carga.visible{opacity:1}.barra-progreso{height:100%;width:40%;background:var(--azul);border-radius:2px;animation:deslizar 1.2s ease-in-out infinite}
@keyframes deslizar{0%{transform:translateX(-100%)}100%{transform:translateX(350%)}}
.toast{position:fixed;bottom:2rem;right:2rem;display:flex;align-items:center;gap:10px;padding:12px 20px;border-radius:10px;color:white;font-weight:500;font-size:0.93rem;box-shadow:0 6px 20px rgba(0,0,0,0.18);z-index:3000;max-width:380px}.toast.exito{background:var(--azul)}.toast.error{background:var(--rojo)}.toast-icono{width:20px;height:20px;flex-shrink:0}
.toast-slide-enter-active,.toast-slide-leave-active{transition:all 0.35s ease}.toast-slide-enter-from,.toast-slide-leave-to{opacity:0;transform:translateX(110%)}
.filters-bar{display:flex;align-items:center;gap:0.75rem;margin-bottom:1.2rem;flex-wrap:wrap}
.search-group{position:relative;flex:0 0 320px;min-width:220px}.search-input{width:100%;padding:10px 14px 10px 42px;border:1px solid var(--borde);border-radius:8px;font-size:0.93rem;background:#FFFFFF;color:var(--texto);font-family:'Montserrat',sans-serif;outline:none;transition:border-color 0.2s,box-shadow 0.2s;box-sizing:border-box}.search-input:focus{border-color:var(--azul);box-shadow:0 0 0 3px #DBEAFE}.search-input::placeholder{color:#9CA3AF}.search-icon-svg{position:absolute;left:13px;top:50%;transform:translateY(-50%);width:18px;height:18px;stroke:var(--gris);pointer-events:none}
.btn-nuevo{background:var(--azul);color:white;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;white-space:nowrap;font-family:'Montserrat',sans-serif;font-size:0.92rem;transition:background 0.2s;margin-left:auto}.btn-nuevo:hover{background:var(--azul-hover)}
.table-container{background:#FFFFFF;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);border:1px solid var(--borde)}
.data-table{width:100%;border-collapse:collapse}.data-table th{background:var(--fondo);padding:12px 16px;text-align:left;font-weight:600;font-size:0.88rem;color:var(--texto);border-bottom:1px solid var(--borde);font-family:'Montserrat',sans-serif}.data-table td{padding:11px 16px;border-bottom:1px solid var(--borde);color:var(--texto);font-size:0.93rem;font-family:'Montserrat',sans-serif}.data-table tbody tr:hover{background:#F8FAFC}.data-table tbody tr:last-child td{border-bottom:none}.celda-nombre{font-weight:600}
.celda-acciones{display:flex;gap:7px;align-items:center}.btn-accion{display:flex;align-items:center;gap:5px;padding:6px 13px;border-radius:6px;font-size:0.85rem;cursor:pointer;font-weight:600;font-family:'Montserrat',sans-serif;transition:background 0.15s;white-space:nowrap}.btn-accion svg{width:14px;height:14px}.btn-accion.eliminar-pre{background:#FEF2F2;color:var(--rojo);border:1px solid #FECACA}.btn-accion.eliminar-pre:hover{background:#FEE2E2}
.estado-vacio,.estado-cargando{text-align:center;padding:3.5rem 2rem;color:var(--gris)}.icono-vacio{width:56px;height:56px;stroke:#9CA3AF;margin-bottom:12px}.estado-vacio h3{font-size:1.2rem;color:var(--texto);margin:0 0 6px}.estado-vacio p{font-size:0.93rem;margin:0}.spinner-tabla{display:inline-block;width:36px;height:36px;border:3px solid #E5E7EB;border-top-color:var(--azul);border-radius:50%;animation:girar 0.8s linear infinite;margin-bottom:12px}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.55);display:flex;align-items:center;justify-content:center;z-index:2000}.modal-content{background:#FFFFFF;width:520px;max-width:92%;border-radius:14px;box-shadow:0 20px 50px rgba(0,0,0,0.18);overflow:hidden;border:1px solid var(--borde)}.modal-confirmar{width:440px}.modal-header{background:var(--azul);color:white;padding:1.1rem 1.6rem;display:flex;justify-content:space-between;align-items:center}.modal-header h3{margin:0;font-size:1.2rem;font-weight:700;font-family:'Montserrat',sans-serif}.btn-cerrar-modal{background:none;border:none;color:white;font-size:1.7rem;cursor:pointer;line-height:1;opacity:0.85}.btn-cerrar-modal:hover{opacity:1}
.modal-body{padding:1.6rem}.modal-desc{font-size:0.88rem;color:var(--gris);margin:0 0 1.2rem;line-height:1.4}.form-grupo{margin-bottom:1.2rem}.form-grupo label{display:block;margin-bottom:6px;font-weight:600;font-size:0.9rem;color:var(--texto);font-family:'Montserrat',sans-serif}.obligatorio{color:var(--rojo)}.modal-select{width:100%;padding:10px 14px;border:1.5px solid var(--borde);border-radius:8px;font-size:0.95rem;background:#FFFFFF;color:var(--texto);font-family:'Montserrat',sans-serif;outline:none;transition:border-color 0.2s;box-sizing:border-box}.modal-select:focus{border-color:var(--azul)}.borde-error{border-color:var(--rojo)!important}.mensaje-error{display:block;color:var(--rojo);font-size:0.82rem;margin-top:5px}
.modal-footer{padding:1rem 1.6rem;background:var(--fondo);display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--borde)}.btn-secundario{padding:10px 22px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;background:#FFFFFF;color:var(--texto);border:1px solid var(--borde);transition:background 0.15s}.btn-secundario:hover{background:var(--fondo)}.btn-secundario:disabled{opacity:0.5;cursor:not-allowed}.btn-eliminar{padding:10px 22px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;background:var(--rojo);color:white;border:none}.btn-eliminar:hover{background:#B91C1C}.btn-eliminar:disabled{opacity:0.5;cursor:not-allowed}.btn-guardar{display:flex;align-items:center;gap:8px;padding:10px 22px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;background:#1B396A;color:#FFFFFF;border:none;transition:background 0.15s}.btn-guardar:hover:not(:disabled){background:#1D4ED8}.btn-guardar:disabled{background:#E5E7EB;color:#9CA3AF;cursor:not-allowed}.spinner-btn{display:inline-block;width:15px;height:15px;border:2px solid rgba(255,255,255,0.4);border-top-color:white;border-radius:50%;animation:girar 0.7s linear infinite;flex-shrink:0}
.confirmar-body{display:flex;flex-direction:column;align-items:center;gap:1rem;text-align:center;padding:2rem 1.6rem}.confirmar-icono{width:52px;height:52px;stroke:#F59E0B}.confirmar-body p{color:var(--texto);font-size:0.95rem;margin:0;line-height:1.5}
@keyframes girar{to{transform:rotate(360deg)}}
.pie-pagina{text-align:center;color:#9CA3AF;font-size:0.82rem;padding-top:2rem;border-top:1px solid var(--borde);margin-top:1rem}
</style>