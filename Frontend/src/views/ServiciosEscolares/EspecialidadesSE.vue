<template>
  <MainLayout>
    <div class="especialidades-page">
      
      <div class="barra-carga" :class="{ activa: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <div class="page-header">
        <div class="header-left">
          <h1 class="page-title">
            <Layers :size="28" class="title-icon" />
            ESPECIALIDADES
          </h1>
          <p class="page-subtitle">GESTIÓN DE ESPECIALIDADES ACADÉMICAS</p>
        </div>
        <div class="header-right">
          <button class="btn-nuevo" @click="abrirModalNuevo">
            <Plus :size="16" />
            NUEVA ESPECIALIDAD
          </button>
        </div>
      </div>

      <!-- KPIs -->
      <div class="kpis-grid">
        <div class="kpi-card">
          <div class="kpi-valor">{{ especialidades.length }}</div>
          <div class="kpi-label">TOTAL ESPECIALIDADES</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-valor">{{ especialidadesActivas }}</div>
          <div class="kpi-label">ESPECIALIDADES ACTIVAS</div>
        </div>
        <div class="kpi-card">
          <div class="kpi-valor">{{ carrerasConEspecialidad }}</div>
          <div class="kpi-label">CARRERAS CON ESPECIALIDAD</div>
        </div>
      </div>

      <!-- Filtros -->
      <div class="filtros-card">
        <div class="filtro-grupo">
          <label class="filtro-label">CARRERA</label>
          <select v-model="filtroCarrera" class="filtro-select" @change="aplicarFiltros">
            <option value="">TODAS LAS CARRERAS</option>
            <option v-for="carrera in carreras" :key="carrera.id" :value="carrera.id">
              {{ carrera.nombre }}
            </option>
          </select>
        </div>
        <div class="filtro-grupo">
          <label class="filtro-label">ESTATUS</label>
          <select v-model="filtroEstatus" class="filtro-select" @change="aplicarFiltros">
            <option value="">TODOS</option>
            <option value="activo">ACTIVOS</option>
            <option value="inactivo">INACTIVOS</option>
          </select>
        </div>
        <div class="filtro-busqueda">
          <Search :size="16" class="search-icon" />
          <input 
            v-model="filtroBusqueda" 
            type="text"
            placeholder="BUSCAR ESPECIALIDAD..."
            class="search-input"
            @input="aplicarFiltros"
          />
        </div>
        <button v-if="hayFiltrosActivos" class="btn-limpiar" @click="limpiarFiltros">
          <X :size="14" />
          LIMPIAR
        </button>
      </div>

      <!-- Tabla de especialidades -->
      <div class="tabla-container">
        <div v-if="cargandoEspecialidades" class="loading-state">
          <div class="spinner"></div>
          <span>CARGANDO ESPECIALIDADES...</span>
        </div>

        <div v-else-if="especialidadesFiltradas.length === 0" class="estado-vacio">
          <Inbox :size="48" />
          <h3>NO HAY ESPECIALIDADES</h3>
          <p>No se encontraron especialidades con los filtros seleccionados</p>
          <button class="btn-secondary" @click="abrirModalNuevo">CREAR ESPECIALIDAD</button>
        </div>

        <div v-else class="tabla-scroll">
          <table class="especialidades-table">
            <thead>
              <tr>
                <th>CLAVE</th>
                <th>NOMBRE</th>
                <th>CARRERA</th>
                <th>CRÉDITOS</th>
                <th>ESTATUS</th>
                <th class="text-center">ACCIONES</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="especialidad in especialidadesFiltradas" :key="especialidad.id">
                <td class="celda-clave">{{ especialidad.clave }}</td>
                <td class="celda-nombre">{{ especialidad.nombre }}</td>
                <td>{{ obtenerNombreCarrera(especialidad.carreraId) }}</td>
                <td class="text-center">{{ especialidad.creditos }}</td>
                <td>
                  <span class="estatus-badge" :class="especialidad.activo ? 'activo' : 'inactivo'">
                    {{ especialidad.activo ? 'ACTIVO' : 'INACTIVO' }}
                  </span>
                </td>
                <td class="acciones">
                  <button class="btn-icono ver" @click="verDetalle(especialidad)" title="VER DETALLE">
                    <Eye :size="16" />
                  </button>
                  <button class="btn-icono editar" @click="editarEspecialidad(especialidad)" title="EDITAR">
                    <Pencil :size="16" />
                  </button>
                  <button class="btn-icono eliminar" @click="confirmarEliminar(especialidad)" title="ELIMINAR">
                    <Trash2 :size="16" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal Nuevo/Editar Especialidad -->
      <div v-if="mostrarModal" class="modal-overlay" @click.self="cerrarModal">
        <div class="modal-content">
          <div class="modal-header">
            <h3>{{ modoEdicion ? 'EDITAR ESPECIALIDAD' : 'NUEVA ESPECIALIDAD' }}</h3>
            <button @click="cerrarModal" class="close-btn">
              <X :size="18" />
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>CLAVE *</label>
              <input 
                v-model="formData.clave" 
                type="text"
                placeholder="Ej: ESP-001"
                class="form-input"
                :class="{ error: errores.clave }"
              />
              <span v-if="errores.clave" class="error-msg">{{ errores.clave }}</span>
            </div>
            <div class="form-group">
              <label>NOMBRE *</label>
              <input 
                v-model="formData.nombre" 
                type="text"
                placeholder="Nombre de la especialidad"
                class="form-input"
                :class="{ error: errores.nombre }"
              />
              <span v-if="errores.nombre" class="error-msg">{{ errores.nombre }}</span>
            </div>
            <div class="form-group">
              <label>CARRERA *</label>
              <select v-model="formData.carreraId" class="form-select" :class="{ error: errores.carreraId }">
                <option value="">SELECCIONAR CARRERA</option>
                <option v-for="carrera in carreras" :key="carrera.id" :value="carrera.id">
                  {{ carrera.nombre }}
                </option>
              </select>
              <span v-if="errores.carreraId" class="error-msg">{{ errores.carreraId }}</span>
            </div>
            <div class="form-group">
              <label>CRÉDITOS *</label>
              <input 
                v-model.number="formData.creditos" 
                type="number"
                min="0"
                max="100"
                placeholder="Créditos de la especialidad"
                class="form-input"
                :class="{ error: errores.creditos }"
              />
              <span v-if="errores.creditos" class="error-msg">{{ errores.creditos }}</span>
            </div>
            <div class="form-group">
              <label>DESCRIPCIÓN</label>
              <textarea 
                v-model="formData.descripcion" 
                rows="3"
                placeholder="Descripción de la especialidad (opcional)"
                class="form-textarea"
              ></textarea>
            </div>
            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="formData.activo" />
                <span>ESPECIALIDAD ACTIVA</span>
              </label>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-cancelar" @click="cerrarModal">CANCELAR</button>
            <button class="btn-guardar" @click="guardarEspecialidad" :disabled="guardando">
              {{ guardando ? 'GUARDANDO...' : 'GUARDAR' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Modal Detalle -->
      <div v-if="mostrarDetalle" class="modal-overlay" @click.self="cerrarDetalle">
        <div class="modal-content modal-detalle">
          <div class="modal-header">
            <h3>DETALLE DE ESPECIALIDAD</h3>
            <button @click="cerrarDetalle" class="close-btn">
              <X :size="18" />
            </button>
          </div>
          <div class="modal-body" v-if="especialidadDetalle">
            <div class="detalle-grid">
              <div class="detalle-item">
                <span class="detalle-label">CLAVE:</span>
                <span class="detalle-valor">{{ especialidadDetalle.clave }}</span>
              </div>
              <div class="detalle-item">
                <span class="detalle-label">NOMBRE:</span>
                <span class="detalle-valor">{{ especialidadDetalle.nombre }}</span>
              </div>
              <div class="detalle-item">
                <span class="detalle-label">CARRERA:</span>
                <span class="detalle-valor">{{ obtenerNombreCarrera(especialidadDetalle.carreraId) }}</span>
              </div>
              <div class="detalle-item">
                <span class="detalle-label">CRÉDITOS:</span>
                <span class="detalle-valor">{{ especialidadDetalle.creditos }}</span>
              </div>
              <div class="detalle-item">
                <span class="detalle-label">ESTATUS:</span>
                <span class="detalle-valor">
                  <span class="estatus-badge" :class="especialidadDetalle.activo ? 'activo' : 'inactivo'">
                    {{ especialidadDetalle.activo ? 'ACTIVO' : 'INACTIVO' }}
                  </span>
                </span>
              </div>
              <div class="detalle-item full-width" v-if="especialidadDetalle.descripcion">
                <span class="detalle-label">DESCRIPCIÓN:</span>
                <span class="detalle-valor">{{ especialidadDetalle.descripcion }}</span>
              </div>
              <div class="detalle-item">
                <span class="detalle-label">FECHA CREACIÓN:</span>
                <span class="detalle-valor">{{ especialidadDetalle.createdAt || 'N/A' }}</span>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-editar" @click="editarDesdeDetalle">EDITAR</button>
            <button class="btn-cerrar" @click="cerrarDetalle">CERRAR</button>
          </div>
        </div>
      </div>

      <!-- Modal Confirmación Eliminar -->
      <div v-if="mostrarConfirmacion" class="modal-overlay" @click.self="mostrarConfirmacion = false">
        <div class="modal-content modal-small">
          <div class="modal-header">
            <h3>CONFIRMAR ELIMINACIÓN</h3>
            <button @click="mostrarConfirmacion = false" class="close-btn">
              <X :size="18" />
            </button>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de eliminar la especialidad <strong>{{ especialidadAEliminar?.nombre }}</strong>?</p>
            <p class="text-warning">Esta acción no se puede deshacer.</p>
          </div>
          <div class="modal-footer">
            <button class="btn-cancelar" @click="mostrarConfirmacion = false">CANCELAR</button>
            <button class="btn-eliminar" @click="eliminarEspecialidad" :disabled="eliminando">
              {{ eliminando ? 'ELIMINANDO...' : 'ELIMINAR' }}
            </button>
          </div>
        </div>
      </div>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'
import {
  Layers, Plus, Search, X, Eye, Pencil, Trash2, Inbox
} from 'lucide-vue-next'

import {
  obtenerEspecialidades,
  crearEspecialidad,
  actualizarEspecialidad,
  eliminarEspecialidad as apiEliminarEspecialidad,
  obtenerCarreras
} from '@/api/especialidades.js'

// ==================== ESTADO ====================
const cargando = ref(false)
const cargandoEspecialidades = ref(false)
const especialidades = ref([])
const carreras = ref([])

// Filtros
const filtroCarrera = ref('')
const filtroEstatus = ref('')
const filtroBusqueda = ref('')

// Modal
const mostrarModal = ref(false)
const modoEdicion = ref(false)
const especialidadEditando = ref(null)
const guardando = ref(false)

// Detalle
const mostrarDetalle = ref(false)
const especialidadDetalle = ref(null)

// Eliminar
const mostrarConfirmacion = ref(false)
const especialidadAEliminar = ref(null)
const eliminando = ref(false)

// Formulario
const formData = ref({
  clave: '',
  nombre: '',
  carreraId: '',
  creditos: '',
  descripcion: '',
  activo: true
})

const errores = ref({
  clave: '',
  nombre: '',
  carreraId: '',
  creditos: ''
})

// ==================== COMPUTED ====================
const especialidadesActivas = computed(() => {
  return especialidades.value.filter(e => e.activo).length
})

const carrerasConEspecialidad = computed(() => {
  const carrerasIds = new Set(especialidades.value.map(e => e.carreraId))
  return carrerasIds.size
})

const hayFiltrosActivos = computed(() => {
  return filtroCarrera.value !== '' || filtroEstatus.value !== '' || filtroBusqueda.value !== ''
})

const especialidadesFiltradas = computed(() => {
  let resultado = [...especialidades.value]

  if (filtroCarrera.value) {
    resultado = resultado.filter(e => e.carreraId == filtroCarrera.value)
  }

  if (filtroEstatus.value) {
    resultado = resultado.filter(e => e.activo === (filtroEstatus.value === 'activo'))
  }

  if (filtroBusqueda.value) {
    const busqueda = filtroBusqueda.value.toLowerCase()
    resultado = resultado.filter(e =>
      e.nombre.toLowerCase().includes(busqueda) ||
      e.clave.toLowerCase().includes(busqueda)
    )
  }

  return resultado
})

// ==================== MÉTODOS ====================

const obtenerNombreCarrera = (carreraId) => {
  const carrera = carreras.value.find(c => c.id == carreraId)
  return carrera ? carrera.nombre : 'N/A'
}

const cargarEspecialidades = async () => {
  cargandoEspecialidades.value = true
  try {
    especialidades.value = await obtenerEspecialidades()
    if (especialidades.value.length === 0) {
      // Datos mock para demo
      especialidades.value = [
        { id: 1, clave: 'ESP-ISC-01', nombre: 'INTELIGENCIA ARTIFICIAL', carreraId: 1, creditos: 45, activo: true, descripcion: 'Especialidad en IA y Machine Learning' },
        { id: 2, clave: 'ESP-ISC-02', nombre: 'CIENCIA DE DATOS', carreraId: 1, creditos: 48, activo: true, descripcion: 'Análisis de grandes volúmenes de datos' },
        { id: 3, clave: 'ESP-IND-01', nombre: 'LOGÍSTICA Y CADENA DE SUMINISTRO', carreraId: 2, creditos: 42, activo: true, descripcion: 'Optimización de procesos logísticos' },
        { id: 4, clave: 'ESP-GES-01', nombre: 'FINANZAS CORPORATIVAS', carreraId: 3, creditos: 40, activo: false, descripcion: 'Gestión financiera empresarial' }
      ]
    }
  } catch (error) {
    console.error('Error:', error)
  } finally {
    cargandoEspecialidades.value = false
  }
}

const cargarCarreras = async () => {
  try {
    carreras.value = await obtenerCarreras()
  } catch (error) {
    console.error('Error:', error)
  }
}

const aplicarFiltros = () => {
  // Los filtros son reactivos, no necesita hacer nada adicional
}

const limpiarFiltros = () => {
  filtroCarrera.value = ''
  filtroEstatus.value = ''
  filtroBusqueda.value = ''
}

const validarFormulario = () => {
  let valido = true
  errores.value = { clave: '', nombre: '', carreraId: '', creditos: '' }

  if (!formData.value.clave.trim()) {
    errores.value.clave = 'La clave es requerida'
    valido = false
  }
  if (!formData.value.nombre.trim()) {
    errores.value.nombre = 'El nombre es requerido'
    valido = false
  }
  if (!formData.value.carreraId) {
    errores.value.carreraId = 'La carrera es requerida'
    valido = false
  }
  if (!formData.value.creditos || formData.value.creditos <= 0) {
    errores.value.creditos = 'Los créditos son requeridos y deben ser mayores a 0'
    valido = false
  }

  return valido
}

const resetFormulario = () => {
  formData.value = {
    clave: '',
    nombre: '',
    carreraId: '',
    creditos: '',
    descripcion: '',
    activo: true
  }
  errores.value = { clave: '', nombre: '', carreraId: '', creditos: '' }
}

const abrirModalNuevo = () => {
  modoEdicion.value = false
  especialidadEditando.value = null
  resetFormulario()
  mostrarModal.value = true
}

const editarEspecialidad = (especialidad) => {
  modoEdicion.value = true
  especialidadEditando.value = especialidad
  formData.value = {
    clave: especialidad.clave,
    nombre: especialidad.nombre,
    carreraId: especialidad.carreraId,
    creditos: especialidad.creditos,
    descripcion: especialidad.descripcion || '',
    activo: especialidad.activo
  }
  mostrarModal.value = true
}

const guardarEspecialidad = async () => {
  if (!validarFormulario()) return

  guardando.value = true
  try {
    if (modoEdicion.value) {
      await actualizarEspecialidad(especialidadEditando.value.id, formData.value)
    } else {
      await crearEspecialidad(formData.value)
    }
    await cargarEspecialidades()
    cerrarModal()
  } catch (error) {
    console.error('Error al guardar:', error)
    alert('Error al guardar la especialidad')
  } finally {
    guardando.value = false
  }
}

const cerrarModal = () => {
  mostrarModal.value = false
  modoEdicion.value = false
  especialidadEditando.value = null
  resetFormulario()
}

const verDetalle = (especialidad) => {
  especialidadDetalle.value = especialidad
  mostrarDetalle.value = true
}

const cerrarDetalle = () => {
  mostrarDetalle.value = false
  especialidadDetalle.value = null
}

const editarDesdeDetalle = () => {
  cerrarDetalle()
  editarEspecialidad(especialidadDetalle.value)
}

const confirmarEliminar = (especialidad) => {
  especialidadAEliminar.value = especialidad
  mostrarConfirmacion.value = true
}

const eliminarEspecialidad = async () => {
  eliminando.value = true
  try {
    await apiEliminarEspecialidad(especialidadAEliminar.value.id)
    await cargarEspecialidades()
    mostrarConfirmacion.value = false
    especialidadAEliminar.value = null
  } catch (error) {
    console.error('Error al eliminar:', error)
    alert('Error al eliminar la especialidad')
  } finally {
    eliminando.value = false
  }
}

onMounted(() => {
  cargarEspecialidades()
  cargarCarreras()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.especialidades-page {
  --azul: #1B396A;
  --azul-hover: #15305A;
  --azul-claro: #DBEAFE;
  --verde: #16A34A;
  --rojo: #DC2626;
  --amarillo: #D97706;
  --borde: #E5E7EB;
  --fondo: #F9FAFB;
  --texto: #111827;
  --gris: #6B7280;
  
  font-family: 'Montserrat', sans-serif;
  padding-bottom: 2rem;
}

.especialidades-page * {
  font-family: 'Montserrat', sans-serif;
}

/* Barra de carga */
.barra-carga { position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 9999; opacity: 0; transition: opacity .2s; pointer-events: none; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: var(--azul); animation: progreso-carga 1.5s ease-in-out infinite; }
@keyframes progreso-carga { 0%{width:0%} 70%{width:85%} 100%{width:100%;opacity:0} }

/* Header */
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.page-title { display: flex; align-items: center; gap: 10px; font-size: 1.5rem; font-weight: 700; color: var(--texto); margin: 0; }
.title-icon { stroke: var(--azul); }
.page-subtitle { font-size: 0.75rem; color: var(--gris); margin: 4px 0 0 0; letter-spacing: 0.05em; }
.btn-nuevo { background: var(--azul); color: white; border: none; border-radius: 10px; padding: 10px 20px; font-size: 0.75rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: background 0.2s; }
.btn-nuevo:hover { background: var(--azul-hover); }

/* KPIs */
.kpis-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1rem; }
.kpi-card { background: white; border: 1px solid var(--borde); border-radius: 16px; padding: 1rem; text-align: center; box-shadow: 0 4px 12px rgba(29, 82, 183, 0.05); }
.kpi-valor { font-size: 2rem; font-weight: 800; color: var(--azul); }
.kpi-label { font-size: 0.65rem; font-weight: 700; color: var(--gris); letter-spacing: 0.05em; margin-top: 4px; }

/* Filtros */
.filtros-card { background: white; border: 1px solid var(--borde); border-radius: 16px; padding: 1rem; margin-bottom: 1rem; display: flex; align-items: flex-end; gap: 1rem; flex-wrap: wrap; box-shadow: 0 4px 12px rgba(29, 82, 183, 0.05); }
.filtro-grupo { display: flex; flex-direction: column; gap: 4px; }
.filtro-label { font-size: 0.7rem; font-weight: 700; color: var(--gris); letter-spacing: 0.05em; }
.filtro-select { padding: 8px 12px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.8rem; font-family: 'Montserrat', sans-serif; outline: none; background: white; }
.filtro-select:focus { border-color: var(--azul); }
.filtro-busqueda { position: relative; flex: 1; min-width: 200px; }
.search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); stroke: var(--gris); }
.search-input { width: 100%; padding: 8px 12px 8px 34px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.8rem; font-family: 'Montserrat', sans-serif; outline: none; }
.search-input:focus { border-color: var(--azul); }
.btn-limpiar { background: var(--fondo); border: 1px solid var(--borde); border-radius: 8px; padding: 8px 16px; font-size: 0.7rem; font-weight: 700; color: var(--gris); cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s; }
.btn-limpiar:hover { border-color: var(--rojo); color: var(--rojo); }

/* Tabla */
.tabla-container { background: white; border: 1px solid var(--borde); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(29, 82, 183, 0.05); }
.tabla-scroll { overflow-x: auto; }
.especialidades-table { width: 100%; border-collapse: collapse; }
.especialidades-table th { background: var(--fondo); padding: 12px 16px; text-align: left; font-size: 0.7rem; font-weight: 700; color: var(--texto); border-bottom: 1px solid var(--borde); letter-spacing: 0.05em; }
.especialidades-table td { padding: 12px 16px; font-size: 0.75rem; border-bottom: 1px solid var(--borde); }
.celda-clave { font-weight: 700; color: var(--azul); font-family: monospace; }
.celda-nombre { font-weight: 600; }
.text-center { text-align: center; }

/* Estatus badge */
.estatus-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.65rem; font-weight: 700; }
.estatus-badge.activo { background: #DCFCE7; color: var(--verde); }
.estatus-badge.inactivo { background: #FEE2E2; color: var(--rojo); }

/* Acciones */
.acciones { display: flex; gap: 8px; justify-content: center; }
.btn-icono { display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; border: none; cursor: pointer; background: var(--fondo); transition: all 0.2s; }
.btn-icono.ver:hover { background: var(--azul-claro); color: var(--azul); }
.btn-icono.editar:hover { background: #DCFCE7; color: var(--verde); }
.btn-icono.eliminar:hover { background: #FEE2E2; color: var(--rojo); }

/* Loading y vacío */
.loading-state { display: flex; flex-direction: column; align-items: center; gap: 1rem; padding: 3rem; color: var(--gris); }
.spinner { width: 40px; height: 40px; border: 3px solid var(--borde); border-top-color: var(--azul); border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.estado-vacio { text-align: center; padding: 3rem; color: var(--gris); }
.estado-vacio h3 { font-size: 1rem; margin: 1rem 0 0.5rem; }
.btn-secondary { background: white; border: 1px solid var(--borde); border-radius: 8px; padding: 8px 20px; font-size: 0.75rem; font-weight: 700; cursor: pointer; margin-top: 1rem; }
.btn-secondary:hover { border-color: var(--azul); color: var(--azul); }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.modal-content { background: white; width: 550px; max-width: 90%; border-radius: 16px; overflow: hidden; max-height: 90vh; overflow-y: auto; }
.modal-small { width: 400px; }
.modal-detalle { width: 500px; }
.modal-header { background: var(--azul); color: white; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
.modal-header h3 { margin: 0; font-size: 1rem; font-weight: 700; letter-spacing: 0.05em; }
.close-btn { background: none; border: none; color: white; cursor: pointer; display: flex; align-items: center; }
.modal-body { padding: 1.5rem; }
.modal-footer { padding: 1rem 1.5rem; background: var(--fondo); display: flex; gap: 12px; justify-content: flex-end; border-top: 1px solid var(--borde); }

/* Formulario */
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 4px; font-size: 0.7rem; font-weight: 700; color: var(--gris); letter-spacing: 0.05em; }
.form-input, .form-select, .form-textarea { width: 100%; padding: 8px 12px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.8rem; font-family: 'Montserrat', sans-serif; outline: none; transition: border-color 0.2s; }
.form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--azul); }
.form-input.error, .form-select.error { border-color: var(--rojo); }
.error-msg { font-size: 0.65rem; color: var(--rojo); margin-top: 4px; display: block; }
.checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
.checkbox-label span { font-size: 0.8rem; font-weight: 500; color: var(--texto); }

/* Botones modal */
.btn-cancelar { background: white; border: 1px solid var(--borde); border-radius: 8px; padding: 8px 20px; font-size: 0.75rem; font-weight: 700; cursor: pointer; }
.btn-cancelar:hover { border-color: var(--rojo); color: var(--rojo); }
.btn-guardar, .btn-editar { background: var(--azul); color: white; border: none; border-radius: 8px; padding: 8px 20px; font-size: 0.75rem; font-weight: 700; cursor: pointer; }
.btn-guardar:hover, .btn-editar:hover { background: var(--azul-hover); }
.btn-eliminar { background: var(--rojo); color: white; border: none; border-radius: 8px; padding: 8px 20px; font-size: 0.75rem; font-weight: 700; cursor: pointer; }
.btn-eliminar:hover { background: #B91C1C; }
.btn-cerrar { background: var(--gris); color: white; border: none; border-radius: 8px; padding: 8px 20px; font-size: 0.75rem; font-weight: 700; cursor: pointer; }
.btn-cerrar:hover { background: #4B5563; }

/* Detalle */
.detalle-grid { display: flex; flex-direction: column; gap: 12px; }
.detalle-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--borde); }
.detalle-item.full-width { flex-direction: column; gap: 4px; }
.detalle-label { font-size: 0.7rem; font-weight: 600; color: var(--gris); }
.detalle-valor { font-size: 0.8rem; font-weight: 600; color: var(--texto); }
.text-warning { color: var(--amarillo); font-size: 0.7rem; margin-top: 8px; }

/* Responsive */
@media (max-width: 768px) {
  .kpis-grid { grid-template-columns: 1fr; }
  .filtros-card { flex-direction: column; align-items: stretch; }
  .filtro-busqueda { width: 100%; }
  .acciones { flex-wrap: wrap; }
  .especialidades-table th, .especialidades-table td { padding: 8px 12px; }
}
</style>