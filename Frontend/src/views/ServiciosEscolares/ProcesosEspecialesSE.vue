<template>
  <MainLayout>
    <div class="procesos-especiales-page">
      
      <div class="barra-carga" :class="{ activa: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <div class="page-header">
        <h1 class="page-title">
          <Settings :size="28" class="title-icon" />
          PROCESOS ESPECIALES
        </h1>
        <p class="page-subtitle">GESTIÓN DE PROCESOS ACADÉMICO-ADMINISTRATIVOS</p>
      </div>

      <!-- ==================== PROCESOS ESPECIALES ==================== -->
      
      <!-- Paso 1: Buscar alumno -->
      <div class="buscador-card">
        <h3 class="card-title">
          <Search :size="18" />
          BUSCAR ALUMNO
        </h3>
        <div class="buscador-container">
          <input 
            v-model="busquedaAlumno" 
            type="text"
            placeholder="Número de control o nombre completo"
            class="buscador-input"
            @keyup.enter="buscarAlumno"
          />
          <button class="btn-buscar" @click="buscarAlumno" :disabled="buscandoAlumno">
            <Search :size="16" />
            BUSCAR
          </button>
        </div>
        
        <div v-if="buscandoAlumno" class="loading-state">
          <div class="spinner-small"></div>
          <span>BUSCANDO...</span>
        </div>
        
        <div v-if="resultadosBusqueda.length > 0" class="resultados-lista">
          <div 
            v-for="alumno in resultadosBusqueda" 
            :key="alumno.id"
            class="resultado-item"
            @click="seleccionarAlumno(alumno)"
          >
            <div class="resultado-control">{{ alumno.noControl }}</div>
            <div class="resultado-nombre">{{ alumno.nombre }}</div>
            <div class="resultado-carrera">{{ alumno.carrera }}</div>
            <ChevronRight :size="16" class="resultado-arrow" />
          </div>
        </div>
      </div>

      <!-- Paso 2: Expediente resumido -->
      <div v-if="alumnoSeleccionado" class="expediente-resumido-card">
        <h3 class="card-title">
          <User :size="18" />
          EXPEDIENTE RESUMIDO
        </h3>
        <div class="expediente-grid">
          <div class="expediente-item">
            <span class="expediente-label">NO. CONTROL</span>
            <span class="expediente-valor">{{ alumnoSeleccionado.noControl }}</span>
          </div>
          <div class="expediente-item">
            <span class="expediente-label">NOMBRE</span>
            <span class="expediente-valor">{{ alumnoSeleccionado.nombre }}</span>
          </div>
          <div class="expediente-item">
            <span class="expediente-label">CARRERA</span>
            <span class="expediente-valor">{{ alumnoSeleccionado.carrera }}</span>
          </div>
          <div class="expediente-item">
            <span class="expediente-label">SEMESTRE</span>
            <span class="expediente-valor">{{ alumnoSeleccionado.semestre }}° SEMESTRE</span>
          </div>
          <div class="expediente-item">
            <span class="expediente-label">PROMEDIO</span>
            <span class="expediente-valor" :class="clasePromedio(alumnoSeleccionado.promedio)">
              {{ alumnoSeleccionado.promedio }}
            </span>
          </div>
          <div class="expediente-item">
            <span class="expediente-label">ESTATUS</span>
            <span class="expediente-valor" :class="claseEstatus(alumnoSeleccionado.estatus)">
              {{ alumnoSeleccionado.estatus }}
            </span>
          </div>
        </div>
      </div>

      <!-- Paso 3: Seleccionar tipo de proceso -->
      <div v-if="alumnoSeleccionado && !procesoSeleccionado" class="tipos-procesos-card">
        <h3 class="card-title">
          <Settings :size="18" />
          SELECCIONAR PROCESO
        </h3>
        <div class="procesos-grid">
          <div 
            v-for="proceso in tiposProceso" 
            :key="proceso.id"
            class="proceso-card"
            @click="seleccionarTipoProceso(proceso)"
          >
            <div class="proceso-icono" :style="{ background: proceso.color + '20', color: proceso.color }">
              <component :is="proceso.icono" :size="24" />
            </div>
            <div class="proceso-nombre">{{ proceso.nombre }}</div>
            <div class="proceso-descripcion">{{ proceso.descripcion }}</div>
          </div>
        </div>
      </div>

      <!-- Paso 4: Formulario según proceso -->
      <div v-if="procesoSeleccionado" class="formulario-proceso-card">
        <h3 class="card-title">
          <FileText :size="18" />
          {{ procesoSeleccionado.nombre }}
        </h3>
        
        <!-- Formulario Cambio de Carrera -->
        <div v-if="procesoSeleccionado.id === 'cambio-carrera'" class="formulario">
          <div class="form-group">
            <label>CARRERA DESTINO</label>
            <select v-model="formData.carreraDestino" class="form-select">
              <option value="">SELECCIONAR CARRERA</option>
              <option v-for="carrera in carrerasDisponibles" :key="carrera.id" :value="carrera.id">
                {{ carrera.nombre }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>SEMESTRE DE REINGRESO</label>
            <select v-model="formData.semestreReingreso" class="form-select">
              <option value="">SELECCIONAR SEMESTRE</option>
              <option v-for="n in 8" :key="n" :value="n">{{ n }}° SEMESTRE</option>
            </select>
          </div>
          <div class="form-group">
            <label>MOTIVO DEL CAMBIO</label>
            <textarea v-model="formData.motivo" class="form-textarea" rows="3" placeholder="Describa el motivo del cambio de carrera"></textarea>
          </div>
        </div>

        <!-- Formulario Baja Temporal / Definitiva -->
        <div v-if="procesoSeleccionado.id === 'baja-temporal' || procesoSeleccionado.id === 'baja-definitiva'" class="formulario">
          <div class="form-group">
            <label>FECHA DE BAJA</label>
            <input type="date" v-model="formData.fechaBaja" class="form-input" />
          </div>
          <div class="form-group" v-if="procesoSeleccionado.id === 'baja-temporal'">
            <label>FECHA ESTIMADA DE REINGRESO</label>
            <input type="date" v-model="formData.fechaReingreso" class="form-input" />
          </div>
          <div class="form-group">
            <label>MOTIVO DE BAJA</label>
            <select v-model="formData.motivoBaja" class="form-select">
              <option value="">SELECCIONAR MOTIVO</option>
              <option value="economico">ECONÓMICO</option>
              <option value="salud">SALUD</option>
              <option value="laboral">LABORAL</option>
              <option value="personal">PERSONAL</option>
              <option value="academico">ACADÉMICO</option>
            </select>
          </div>
          <div class="form-group">
            <label>OBSERVACIONES</label>
            <textarea v-model="formData.observaciones" class="form-textarea" rows="3" placeholder="Observaciones adicionales"></textarea>
          </div>
        </div>

        <!-- Formulario Reactivación -->
        <div v-if="procesoSeleccionado.id === 'reactivacion'" class="formulario">
          <div class="form-group">
            <label>FECHA DE REACTIVACIÓN</label>
            <input type="date" v-model="formData.fechaReactivacion" class="form-input" />
          </div>
          <div class="form-group">
            <label>SEMESTRE DE REINGRESO</label>
            <select v-model="formData.semestreReingreso" class="form-select">
              <option value="">SELECCIONAR SEMESTRE</option>
              <option v-for="n in 8" :key="n" :value="n">{{ n }}° SEMESTRE</option>
            </select>
          </div>
        </div>

        <!-- Formulario Corrección Académica -->
        <div v-if="procesoSeleccionado.id === 'correccion-academica'" class="formulario">
          <div class="form-group">
            <label>MATERIA A CORREGIR</label>
            <select v-model="formData.materiaId" class="form-select">
              <option value="">SELECCIONAR MATERIA</option>
              <option v-for="materia in materiasAlumno" :key="materia.id" :value="materia.id">
                {{ materia.nombre }} (Calificación actual: {{ materia.calificacion }})
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>NUEVA CALIFICACIÓN</label>
            <input type="number" v-model.number="formData.nuevaCalificacion" min="0" max="100" class="form-input" />
          </div>
          <div class="form-group">
            <label>JUSTIFICACIÓN</label>
            <textarea v-model="formData.justificacion" class="form-textarea" rows="3" placeholder="Justificación de la corrección académica"></textarea>
          </div>
        </div>

        <div class="form-actions">
          <button class="btn-cancelar" @click="cancelarProceso">CANCELAR</button>
          <button class="btn-continuar" @click="mostrarConfirmacion = true" :disabled="!formularioValido">
            CONTINUAR
          </button>
        </div>
      </div>

      <!-- Paso 5: Confirmación -->
      <div v-if="mostrarConfirmacion" class="confirmacion-card">
        <h3 class="card-title">
          <CheckCircle :size="18" />
          CONFIRMAR PROCESO
        </h3>
        
        <div class="resumen-proceso">
          <div class="resumen-item">
            <span class="resumen-label">ALUMNO:</span>
            <span class="resumen-valor">{{ alumnoSeleccionado?.nombre }} ({{ alumnoSeleccionado?.noControl }})</span>
          </div>
          <div class="resumen-item">
            <span class="resumen-label">PROCESO:</span>
            <span class="resumen-valor">{{ procesoSeleccionado?.nombre }}</span>
          </div>
          <div class="resumen-item" v-if="formData.carreraDestino">
            <span class="resumen-label">CARRERA DESTINO:</span>
            <span class="resumen-valor">{{ obtenerNombreCarrera(formData.carreraDestino) }}</span>
          </div>
          <div class="resumen-item" v-if="formData.nuevaCalificacion">
            <span class="resumen-label">NUEVA CALIFICACIÓN:</span>
            <span class="resumen-valor">{{ formData.nuevaCalificacion }}</span>
          </div>
          <div class="resumen-item" v-if="formData.motivo">
            <span class="resumen-label">MOTIVO:</span>
            <span class="resumen-valor">{{ formData.motivo }}</span>
          </div>
        </div>

        <div class="confirmacion-actions">
          <button class="btn-volver" @click="mostrarConfirmacion = false">VOLVER</button>
          <button class="btn-ejecutar-proceso" @click="ejecutarProceso" :disabled="ejecutandoProceso">
            {{ ejecutandoProceso ? 'PROCESANDO...' : 'CONFIRMAR Y EJECUTAR' }}
          </button>
        </div>
      </div>

      <!-- Resultado del proceso -->
      <div v-if="resultadoProceso" class="resultado-card" :class="resultadoProceso.exito ? 'exito' : 'error'">
        <div class="resultado-icono">
          <component :is="resultadoProceso.exito ? 'CheckCircle' : 'AlertCircle'" :size="32" />
        </div>
        <div class="resultado-mensaje">{{ resultadoProceso.mensaje }}</div>
        <button class="btn-cerrar-resultado" @click="cerrarResultado">ACEPTAR</button>
      </div>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'
import {
  Settings, Search, User, FileText, CheckCircle, AlertCircle,
  ChevronRight, BookOpen, UserMinus, RefreshCw, Edit
} from 'lucide-vue-next'

// Importar API
import { 
  buscarAlumno as apiBuscarAlumno,
  obtenerMateriasAlumno as apiObtenerMaterias,
  ejecutarCambioCarrera,
  ejecutarBajaTemporal,
  ejecutarBajaDefinitiva,
  ejecutarReactivacion,
  ejecutarCorreccionAcademica
} from '@/api/procesosEspeciales.js'

// ==================== ESTADO ====================
const cargando = ref(false)
const busquedaAlumno = ref('')
const buscandoAlumno = ref(false)
const resultadosBusqueda = ref([])
const alumnoSeleccionado = ref(null)
const procesoSeleccionado = ref(null)
const mostrarConfirmacion = ref(false)
const ejecutandoProceso = ref(false)
const resultadoProceso = ref(null)

// Tipos de proceso
const tiposProceso = ref([
  { id: 'cambio-carrera', nombre: 'CAMBIO DE CARRERA', descripcion: 'Cambiar al alumno a otra carrera', icono: BookOpen, color: '#1D52B7' },
  { id: 'baja-temporal', nombre: 'BAJA TEMPORAL', descripcion: 'Dar de baja al alumno por un periodo', icono: UserMinus, color: '#F2994A' },
  { id: 'baja-definitiva', nombre: 'BAJA DEFINITIVA', descripcion: 'Dar de baja definitiva al alumno', icono: AlertCircle, color: '#EB5757' },
  { id: 'reactivacion', nombre: 'REACTIVACIÓN', descripcion: 'Reactivar a un alumno con baja temporal', icono: RefreshCw, color: '#27AE60' },
  { id: 'correccion-academica', nombre: 'CORRECCIÓN ACADÉMICA', descripcion: 'Corregir calificaciones o kardex', icono: Edit, color: '#2F80ED' }
])

// Catálogos
const carrerasDisponibles = ref([
  { id: 1, nombre: 'INGENIERÍA EN SISTEMAS COMPUTACIONALES' },
  { id: 2, nombre: 'INGENIERÍA INDUSTRIAL' },
  { id: 3, nombre: 'INGENIERÍA EN GESTIÓN EMPRESARIAL' },
  { id: 4, nombre: 'INGENIERÍA CIVIL' },
  { id: 5, nombre: 'INGENIERÍA ELÉCTRICA' }
])

const materiasAlumno = ref([])

// Formulario
const formData = ref({
  carreraDestino: '',
  semestreReingreso: '',
  motivo: '',
  fechaBaja: '',
  fechaReingreso: '',
  motivoBaja: '',
  observaciones: '',
  fechaReactivacion: '',
  materiaId: '',
  nuevaCalificacion: '',
  justificacion: ''
})

// Validación del formulario
const formularioValido = computed(() => {
  if (!procesoSeleccionado.value) return false
  
  switch (procesoSeleccionado.value.id) {
    case 'cambio-carrera':
      return formData.value.carreraDestino && formData.value.semestreReingreso && formData.value.motivo
    case 'baja-temporal':
      return formData.value.fechaBaja && formData.value.motivoBaja
    case 'baja-definitiva':
      return formData.value.fechaBaja && formData.value.motivoBaja
    case 'reactivacion':
      return formData.value.fechaReactivacion && formData.value.semestreReingreso
    case 'correccion-academica':
      return formData.value.materiaId && formData.value.nuevaCalificacion && formData.value.justificacion
    default:
      return false
  }
})

// ==================== MÉTODOS ====================

// Buscar alumno
const buscarAlumno = async () => {
  if (!busquedaAlumno.value.trim()) return
  
  buscandoAlumno.value = true
  try {
    resultadosBusqueda.value = await apiBuscarAlumno(busquedaAlumno.value)
  } catch (error) {
    console.error('Error:', error)
    resultadosBusqueda.value = []
  } finally {
    buscandoAlumno.value = false
  }
}

// Seleccionar alumno
const seleccionarAlumno = async (alumno) => {
  alumnoSeleccionado.value = alumno
  resultadosBusqueda.value = []
  busquedaAlumno.value = ''
  
  try {
    materiasAlumno.value = await apiObtenerMaterias(alumno.id)
  } catch (error) {
    console.error('Error al cargar materias:', error)
    materiasAlumno.value = []
  }
}

// Seleccionar tipo de proceso
const seleccionarTipoProceso = (proceso) => {
  procesoSeleccionado.value = proceso
  mostrarConfirmacion.value = false
  formData.value = {
    carreraDestino: '',
    semestreReingreso: '',
    motivo: '',
    fechaBaja: '',
    fechaReingreso: '',
    motivoBaja: '',
    observaciones: '',
    fechaReactivacion: '',
    materiaId: '',
    nuevaCalificacion: '',
    justificacion: ''
  }
}

// Cancelar proceso
const cancelarProceso = () => {
  procesoSeleccionado.value = null
  mostrarConfirmacion.value = false
}

// Ejecutar proceso según el tipo
const ejecutarProceso = async () => {
  ejecutandoProceso.value = true
  
  try {
    let resultado
    const datosProceso = {
      alumnoId: alumnoSeleccionado.value.id,
      alumnoNoControl: alumnoSeleccionado.value.noControl,
      ...formData.value
    }
    
    switch (procesoSeleccionado.value.id) {
      case 'cambio-carrera':
        resultado = await ejecutarCambioCarrera(datosProceso)
        break
      case 'baja-temporal':
        resultado = await ejecutarBajaTemporal(datosProceso)
        break
      case 'baja-definitiva':
        resultado = await ejecutarBajaDefinitiva(datosProceso)
        break
      case 'reactivacion':
        resultado = await ejecutarReactivacion(datosProceso)
        break
      case 'correccion-academica':
        resultado = await ejecutarCorreccionAcademica(datosProceso)
        break
      default:
        throw new Error('Proceso no válido')
    }
    
    resultadoProceso.value = {
      exito: true,
      mensaje: resultado.mensaje || `${procesoSeleccionado.value.nombre} ejecutado correctamente`
    }
    
    setTimeout(() => cerrarResultado(), 3000)
  } catch (error) {
    resultadoProceso.value = {
      exito: false,
      mensaje: error.message || `Error al ejecutar ${procesoSeleccionado.value.nombre}`
    }
  } finally {
    ejecutandoProceso.value = false
    mostrarConfirmacion.value = false
  }
}

// Cerrar resultado
const cerrarResultado = () => {
  resultadoProceso.value = null
  procesoSeleccionado.value = null
  alumnoSeleccionado.value = null
}

// Obtener nombre de carrera
const obtenerNombreCarrera = (id) => {
  const carrera = carrerasDisponibles.value.find(c => c.id == id)
  return carrera ? carrera.nombre : ''
}

// Clases para estilos
const clasePromedio = (promedio) => {
  if (promedio >= 85) return 'promedio-alto'
  if (promedio >= 70) return 'promedio-regular'
  return 'promedio-bajo'
}

const claseEstatus = (estatus) => {
  if (estatus === 'REGULAR') return 'estatus-regular'
  return 'estatus-irregular'
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.procesos-especiales-page {
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

.procesos-especiales-page * {
  font-family: 'Montserrat', sans-serif;
}

/* Barra de carga */
.barra-carga { position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 9999; opacity: 0; transition: opacity .2s; pointer-events: none; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: var(--azul); animation: progreso-carga 1.5s ease-in-out infinite; }
@keyframes progreso-carga { 0%{width:0%} 70%{width:85%} 100%{width:100%;opacity:0} }

/* Header */
.page-header { margin-bottom: 1.5rem; }
.page-title { display: flex; align-items: center; gap: 10px; font-size: 1.5rem; font-weight: 700; color: var(--texto); margin: 0; }
.title-icon { stroke: var(--azul); }
.page-subtitle { font-size: 0.75rem; color: var(--gris); margin: 4px 0 0 0; letter-spacing: 0.05em; }

/* Cards */
.buscador-card, .expediente-resumido-card, .tipos-procesos-card,
.formulario-proceso-card, .confirmacion-card, .resultado-card {
  background: white;
  border: 1px solid var(--borde);
  border-radius: 16px;
  padding: 1.25rem;
  margin-bottom: 1rem;
  box-shadow: 0 4px 12px rgba(29, 82, 183, 0.05);
}

.card-title { display: flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 700; color: var(--texto); margin: 0 0 1rem 0; }

/* Buscador */
.buscador-container { display: flex; gap: 10px; }
.buscador-input { flex: 1; padding: 10px 14px; border: 1px solid var(--borde); border-radius: 10px; font-size: 0.85rem; font-family: 'Montserrat', sans-serif; outline: none; }
.buscador-input:focus { border-color: var(--azul); }
.btn-buscar { background: var(--azul); color: white; border: none; border-radius: 10px; padding: 0 20px; font-size: 0.75rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.btn-buscar:hover:not(:disabled) { background: var(--azul-hover); }

.resultados-lista { margin-top: 1rem; border: 1px solid var(--borde); border-radius: 12px; overflow: hidden; }
.resultado-item { display: flex; align-items: center; gap: 1rem; padding: 12px 16px; border-bottom: 1px solid var(--borde); cursor: pointer; transition: background 0.15s; }
.resultado-item:last-child { border-bottom: none; }
.resultado-item:hover { background: var(--azul-claro); }
.resultado-control { font-weight: 700; color: var(--azul); min-width: 100px; font-family: monospace; }
.resultado-nombre { flex: 1; font-weight: 600; }
.resultado-carrera { font-size: 0.7rem; color: var(--gris); }
.resultado-arrow { color: var(--gris); margin-left: auto; }

/* Expediente */
.expediente-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
.expediente-item { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid var(--borde); }
.expediente-label { font-size: 0.7rem; font-weight: 600; color: var(--gris); }
.expediente-valor { font-size: 0.8rem; font-weight: 700; color: var(--texto); }
.promedio-alto { color: var(--verde); }
.promedio-regular { color: var(--azul); }
.promedio-bajo { color: var(--rojo); }
.estatus-regular { color: var(--verde); }
.estatus-irregular { color: var(--rojo); }

/* Procesos grid */
.procesos-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
.proceso-card { background: var(--fondo); border: 1px solid var(--borde); border-radius: 12px; padding: 1rem; text-align: center; cursor: pointer; transition: all 0.2s; }
.proceso-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(29, 82, 183, 0.1); border-color: var(--azul); }
.proceso-icono { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; }
.proceso-nombre { font-size: 0.85rem; font-weight: 800; color: var(--texto); margin-bottom: 4px; }
.proceso-descripcion { font-size: 0.65rem; color: var(--gris); }

/* Formulario */
.formulario { display: flex; flex-direction: column; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 4px; }
.form-group label { font-size: 0.7rem; font-weight: 700; color: var(--gris); letter-spacing: 0.05em; }
.form-input, .form-select, .form-textarea { padding: 8px 12px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.8rem; font-family: 'Montserrat', sans-serif; outline: none; }
.form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--azul); }

.form-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 1rem; }
.btn-cancelar { background: white; border: 1px solid var(--borde); border-radius: 8px; padding: 8px 20px; font-size: 0.75rem; font-weight: 700; cursor: pointer; }
.btn-cancelar:hover { border-color: var(--rojo); color: var(--rojo); }
.btn-continuar { background: var(--azul); color: white; border: none; border-radius: 8px; padding: 8px 20px; font-size: 0.75rem; font-weight: 700; cursor: pointer; }
.btn-continuar:hover:not(:disabled) { background: var(--azul-hover); }
.btn-continuar:disabled { opacity: 0.5; cursor: not-allowed; }

/* Confirmación */
.resumen-proceso { background: var(--fondo); border-radius: 12px; padding: 1rem; margin-bottom: 1rem; }
.resumen-item { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--borde); }
.resumen-item:last-child { border-bottom: none; }
.resumen-label { font-size: 0.7rem; font-weight: 600; color: var(--gris); }
.resumen-valor { font-size: 0.8rem; font-weight: 700; color: var(--texto); }

.confirmacion-actions { display: flex; justify-content: flex-end; gap: 12px; }
.btn-volver { background: white; border: 1px solid var(--borde); border-radius: 8px; padding: 8px 20px; font-size: 0.75rem; font-weight: 700; cursor: pointer; }
.btn-ejecutar-proceso { background: var(--verde); color: white; border: none; border-radius: 8px; padding: 8px 20px; font-size: 0.75rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
.btn-ejecutar-proceso:hover:not(:disabled) { background: #128A3A; }

/* Resultado */
.resultado-card { text-align: center; border-left: 4px solid; }
.resultado-card.exito { border-left-color: var(--verde); }
.resultado-card.error { border-left-color: var(--rojo); }
.resultado-icono { margin-bottom: 0.5rem; }
.resultado-card.exito .resultado-icono { color: var(--verde); }
.resultado-card.error .resultado-icono { color: var(--rojo); }
.resultado-mensaje { font-size: 0.85rem; font-weight: 600; margin-bottom: 1rem; }
.btn-cerrar-resultado { background: var(--azul); color: white; border: none; border-radius: 8px; padding: 8px 24px; font-size: 0.75rem; font-weight: 700; cursor: pointer; }

/* Loading */
.loading-state { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 2rem; color: var(--gris); }
.spinner-small { width: 24px; height: 24px; border: 2px solid var(--borde); border-top-color: var(--azul); border-radius: 50%; animation: spin 0.6s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Responsive */
@media (max-width: 768px) {
  .expediente-grid { grid-template-columns: 1fr; }
  .procesos-grid { grid-template-columns: 1fr; }
  .buscador-container { flex-direction: column; }
  .btn-buscar { padding: 10px; justify-content: center; }
  .resultado-item { flex-direction: column; align-items: flex-start; gap: 4px; }
  .resultado-arrow { display: none; }
}
</style>