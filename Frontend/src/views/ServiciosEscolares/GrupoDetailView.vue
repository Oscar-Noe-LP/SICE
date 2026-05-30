<!-- src/views/ServiciosEscolares/GrupoDetailView.vue -->
<template>
  <MainLayout>
    <div class="grupo-detail-page">

      <!-- Barra de carga -->
      <div class="barra-carga" :class="{ activa: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- Breadcrumb -->
      <div class="nav-top">
        <button class="btn-back" @click="router.back()">
          <ArrowLeft :size="14" />
          VOLVER
        </button>
        <div class="breadcrumb-trail">
          <span>SERVICIOS ESCOLARES</span>
          <ChevronRight :size="10" />
          <router-link to="/gestion-grupos" class="bc-link">GESTION DE GRUPOS</router-link>
          <ChevronRight :size="10" />
          <span class="bc-active">{{ grupo.nombre || 'CARGANDO...' }}</span>
        </div>
      </div>

      <!-- Header del grupo -->
      <div class="grupo-header">
        <div class="header-left">
          <div class="header-icon">
            <Users :size="28" />
          </div>
          <div class="header-info">
            <h1 class="grupo-nombre">{{ (grupo.nombre || 'CARGANDO...').toUpperCase() }}</h1>
            <div class="header-tags">
              <span class="tag">{{ grupo.semestre }}° SEMESTRE</span>
              <span class="tag" :class="`tag-${(grupo.turno || 'MATUTINO').toLowerCase()}`">
                {{ (grupo.turno || 'MATUTINO').toUpperCase() }}
              </span>
              <span class="tag">{{ (grupo.carrera || 'CARRERA').toUpperCase() }}</span>
            </div>
          </div>
        </div>
        <div class="header-stats">
          <div class="stat">
            <span class="stat-val">{{ grupo.inscritos || 0 }}</span>
            <span class="stat-lbl">INSCRITOS</span>
          </div>
          <div class="stat-sep"></div>
          <div class="stat">
            <span class="stat-val text-verde">{{ grupo.regulares || 0 }}</span>
            <span class="stat-lbl">REGULARES</span>
          </div>
          <div class="stat-sep"></div>
          <div class="stat">
            <span class="stat-val text-rojo">{{ grupo.irregulares || 0 }}</span>
            <span class="stat-lbl">IRREGULARES</span>
          </div>
        </div>
      </div>

      <!-- Info del grupo -->
      <div class="info-grid">
        <div class="info-card">
          <Calendar :size="16" />
          <div class="info-content">
            <span class="info-label">HORARIO</span>
            <span class="info-value">{{ (grupo.horario || 'SIN HORARIO').toUpperCase() }}</span>
          </div>
        </div>
        <div class="info-card">
          <MapPin :size="16" />
          <div class="info-content">
            <span class="info-label">AULA</span>
            <span class="info-value">{{ (grupo.aula || 'SIN AULA').toUpperCase() }}</span>
          </div>
        </div>
        <div class="info-card">
          <User :size="16" />
          <div class="info-content">
            <span class="info-label">DOCENTE</span>
            <span class="info-value">{{ (grupo.docente || 'SIN ASIGNAR').toUpperCase() }}</span>
          </div>
        </div>
        <div class="info-card">
          <GraduationCap :size="16" />
          <div class="info-content">
            <span class="info-label">CAPACIDAD</span>
            <span class="info-value">{{ grupo.capacidad || 0 }} ALUMNOS</span>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="tabs-bar">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          class="tab-btn"
          :class="{ activo: tabActivo === tab.key }"
          @click="tabActivo = tab.key"
        >
          <component :is="tab.icono" :size="14" />
          {{ tab.label }}
          <span v-if="tab.count" class="tab-count">{{ tab.count }}</span>
        </button>
      </div>

      <!-- TAB: ALUMNOS -->
      <div v-if="tabActivo === 'alumnos'">
        <div class="alumnos-toolbar">
          <div class="search-group">
            <Search :size="14" class="search-icon" />
            <input
              type="text"
              v-model="busquedaAlumnos"
              placeholder="BUSCAR POR NOMBRE O NO. CONTROL..."
              class="search-input"
            />
          </div>
          <button class="btn-agregar" @click="agregarAlumno">
            <UserPlus :size="14" />
            AGREGAR ALUMNO
          </button>
          <button class="btn-exportar" @click="exportarLista">
            <Download :size="14" />
            EXPORTAR
          </button>
        </div>

        <div v-if="cargandoAlumnos" class="loading-state">
          <div class="spinner"></div>
          <span>CARGANDO ALUMNOS...</span>
        </div>

        <div v-else class="alumnos-table-container">
          <table class="alumnos-table">
            <thead>
              <tr>
                <th>NO. CONTROL</th>
                <th>NOMBRE COMPLETO</th>
                <th>ESTATUS</th>
                <th>PROMEDIO</th>
                <th class="text-center">ACCIONES</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="alumno in alumnosFiltrados" :key="alumno.id">
                <td class="celda-control">{{ alumno.noControl }}</td>
                <td class="celda-nombre">
                  <span class="link-alumno" @click="verAlumno(alumno)">
                    {{ alumno.nombre.toUpperCase() }}
                  </span>
                </td>
                <td>
                  <span class="estatus-badge" :class="alumno.estatus === 'REGULAR' ? 'activo' : 'inactivo'">
                    {{ alumno.estatus }}
                  </span>
                </td>
                <td :class="alumno.promedio >= 70 ? 'text-verde' : 'text-rojo'">
                  {{ alumno.promedio || 'N/A' }}
                </td>
                <td class="acciones">
                  <button class="btn-icono ver" @click="verAlumno(alumno)" title="VER DETALLE">
                    <Eye :size="14" />
                  </button>
                  <button class="btn-icono editar" @click="editarAlumno(alumno)" title="EDITAR">
                    <Pencil :size="14" />
                  </button>
                  <button class="btn-icono eliminar" @click="eliminarAlumno(alumno)" title="ELIMINAR">
                    <Trash2 :size="14" />
                  </button>
                </td>
              </tr>
            </tbody>
           </table>

          <div v-if="alumnosFiltrados.length === 0" class="estado-vacio">
            <Inbox :size="40" />
            <h3>SIN ALUMNOS</h3>
            <p>NO HAY ALUMNOS INSCRITOS EN ESTE GRUPO</p>
            <button class="btn-secondary" @click="agregarAlumno">AGREGAR ALUMNO</button>
          </div>
        </div>
      </div>

      <!-- TAB: CALIFICACIONES -->
      <div v-if="tabActivo === 'calificaciones'">
        <div class="calificaciones-toolbar">
          <div class="search-group">
            <Search :size="14" class="search-icon" />
            <input
              type="text"
              v-model="busquedaCalificaciones"
              placeholder="BUSCAR POR NOMBRE O NO. CONTROL..."
              class="search-input"
            />
          </div>
          <button class="btn-primary" @click="exportarCalificaciones">
            <Download :size="14" />
            EXPORTAR
          </button>
        </div>

        <div v-if="cargandoCalificaciones" class="loading-state">
          <div class="spinner"></div>
          <span>CARGANDO CALIFICACIONES...</span>
        </div>

        <div v-else class="calificaciones-table-container">
          <table class="calificaciones-table">
            <thead>
              <tr>
                <th>NO. CONTROL</th>
                <th>NOMBRE COMPLETO</th>
                <th>UNIDAD 1</th>
                <th>UNIDAD 2</th>
                <th>UNIDAD 3</th>
                <th>UNIDAD 4</th>
                <th>PROMEDIO</th>
                <th>ESTATUS</th>
                <th class="text-center">ACCIONES</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="calif in calificacionesFiltradas" :key="calif.id">
                <td>{{ calif.noControl }}</td>
                <td class="celda-nombre">{{ calif.nombre.toUpperCase() }}</td>
                <td :class="calif.unidad1 >= 70 ? 'text-verde' : 'text-rojo'">
                  <span class="calif-edit" @click="editarCalificacion(calif, 'unidad1')">{{ calif.unidad1 || '-' }}</span>
                </td>
                <td :class="calif.unidad2 >= 70 ? 'text-verde' : 'text-rojo'">
                  <span class="calif-edit" @click="editarCalificacion(calif, 'unidad2')">{{ calif.unidad2 || '-' }}</span>
                </td>
                <td :class="calif.unidad3 >= 70 ? 'text-verde' : 'text-rojo'">
                  <span class="calif-edit" @click="editarCalificacion(calif, 'unidad3')">{{ calif.unidad3 || '-' }}</span>
                </td>
                <td :class="calif.unidad4 >= 70 ? 'text-verde' : 'text-rojo'">
                  <span class="calif-edit" @click="editarCalificacion(calif, 'unidad4')">{{ calif.unidad4 || '-' }}</span>
                </td>
                <td class="text-bold">{{ calif.promedio || '-' }}</td>
                <td>
                  <span class="estatus-badge" :class="calif.promedio >= 70 ? 'aprobado' : 'reprobado'">
                    {{ calif.promedio >= 70 ? 'APROBADO' : 'REPROBADO' }}
                  </span>
                </td>
                <td class="acciones">
                  <button class="btn-icono editar" @click="editarTodasCalificaciones(calif)" title="EDITAR CALIFICACIONES">
                    <Pencil :size="14" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="calificacionesFiltradas.length === 0" class="estado-vacio">
            <Inbox :size="40" />
            <h3>SIN CALIFICACIONES</h3>
            <p>NO HAY CALIFICACIONES REGISTRADAS PARA ESTE GRUPO</p>
          </div>
        </div>
      </div>

      <!-- Error -->
      <div v-if="errorCarga" class="error-container">
        <AlertCircle :size="40" class="error-icon" />
        <h3>ERROR AL CARGAR EL GRUPO</h3>
        <p>{{ errorCarga }}</p>
        <button class="btn-secondary" @click="cargarDatos">REINTENTAR</button>
      </div>

      <!-- Modal Editar Calificación -->
      <div v-if="showModalCalif" class="modal-overlay" @click.self="showModalCalif = false">
        <div class="modal-content modal-small">
          <div class="modal-header">
            <h3>EDITAR CALIFICACION</h3>
            <button @click="showModalCalif = false" class="close-btn">
              <X :size="18" />
            </button>
          </div>
          <div class="modal-body">
            <p class="modal-alumno">{{ califEditando?.nombre?.toUpperCase() }}</p>
            <div class="form-group">
              <label>{{ califUnidadEditando?.toUpperCase() }}</label>
              <input 
                v-model.number="califValorEditando" 
                type="number" 
                min="0" 
                max="100" 
                class="modal-input"
                @keyup.enter="guardarCalificacion"
              />
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-cancelar" @click="showModalCalif = false">CANCELAR</button>
            <button class="btn-guardar" @click="guardarCalificacion">GUARDAR</button>
          </div>
        </div>
      </div>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

// Lucide Icons
import {
  ArrowLeft,
  ChevronRight,
  Users,
  Calendar,
  MapPin,
  User,
  GraduationCap,
  Search,
  UserPlus,
  Download,
  Eye,
  Pencil,
  Trash2,
  Inbox,
  AlertCircle,
  X
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const API_URL = import.meta.env.VITE_API_URL

// Estado
const cargando = ref(true)
const cargandoAlumnos = ref(false)
const cargandoCalificaciones = ref(false)
const errorCarga = ref(null)
const tabActivo = ref('alumnos')
const busquedaAlumnos = ref('')
const busquedaCalificaciones = ref('')

// Modal calificaciones
const showModalCalif = ref(false)
const califEditando = ref(null)
const califUnidadEditando = ref('')
const califValorEditando = ref(0)

// Datos del grupo
const grupo = ref({
  id: null,
  nombre: '',
  semestre: '',
  turno: '',
  carrera: '',
  inscritos: 0,
  regulares: 0,
  irregulares: 0,
  horario: '',
  aula: '',
  docente: '',
  capacidad: 0
})

const alumnos = ref([])
const calificaciones = ref([])

// Tabs
const tabs = computed(() => [
  { key: 'alumnos', label: 'ALUMNOS', icono: Users, count: alumnos.value.length },
  { key: 'calificaciones', label: 'CALIFICACIONES', icono: GraduationCap, count: null }
])

// Filtros
const alumnosFiltrados = computed(() => {
  if (!busquedaAlumnos.value) return alumnos.value
  const busqueda = busquedaAlumnos.value.toLowerCase()
  return alumnos.value.filter(a =>
    a.nombre.toLowerCase().includes(busqueda) ||
    a.noControl?.toLowerCase().includes(busqueda)
  )
})

const calificacionesFiltradas = computed(() => {
  if (!busquedaCalificaciones.value) return calificaciones.value
  const busqueda = busquedaCalificaciones.value.toLowerCase()
  return calificaciones.value.filter(c =>
    c.nombre.toLowerCase().includes(busqueda) ||
    c.noControl?.toLowerCase().includes(busqueda)
  )
})

// Métodos principales
const cargarDatos = async () => {
  cargando.value = true
  errorCarga.value = null

  try {
    const id = route.params.id
    if (!id) throw new Error('ID DE GRUPO NO PROPORCIONADO')

    // Cargar datos del grupo
    const grupoRes = await fetch(`${API_URL}/api/grupos/${id}`)
    if (grupoRes.ok) {
      const data = await grupoRes.json()
      grupo.value = {
        id: data.id_grupo,
        nombre: data.nombre_grupo,
        semestre: data.semestre,
        turno: data.turno || 'MATUTINO',
        carrera: data.carrera_nombre,
        inscritos: data.total_alumnos || 0,
        regulares: data.alumnos_regulares || 0,
        irregulares: data.alumnos_irregulares || 0,
        horario: `${data.dia || ''} ${data.hora_inicio || ''} - ${data.hora_fin || ''}`.trim() || 'SIN HORARIO',
        aula: data.aula_nombre,
        docente: data.docente_nombre,
        capacidad: data.capacidad
      }
    } else {
      // Datos de ejemplo para demo
      grupo.value = {
        id: id,
        nombre: `GRUPO 101 - SEMESTRE 5`,
        semestre: 5,
        turno: 'MATUTINO',
        carrera: 'INGENIERIA EN SISTEMAS COMPUTACIONALES',
        inscritos: 35,
        regulares: 28,
        irregulares: 7,
        horario: 'LUNES Y MIERCOLES 08:00 - 10:00',
        aula: 'A-101',
        docente: 'DR. JUAN CARLOS PEREZ',
        capacidad: 40
      }
    }

    await cargarAlumnos()
    await cargarCalificaciones()

  } catch (e) {
    console.error('Error cargando grupo:', e)
    errorCarga.value = e.message || 'ERROR AL CARGAR LOS DATOS'
  } finally {
    cargando.value = false
  }
}

const cargarAlumnos = async () => {
  cargandoAlumnos.value = true
  try {
    const id = route.params.id
    const res = await fetch(`${API_URL}/api/grupos/${id}/alumnos`)
    if (res.ok) {
      alumnos.value = await res.json()
    } else {
      // Datos de ejemplo
      alumnos.value = [
        { id: 1, noControl: '20230001', nombre: 'JUAN PEREZ GONZALEZ', estatus: 'REGULAR', promedio: 85 },
        { id: 2, noControl: '20230002', nombre: 'MARIA LOPEZ MARTINEZ', estatus: 'REGULAR', promedio: 92 },
        { id: 3, noControl: '20230003', nombre: 'CARLOS RAMIREZ SANCHEZ', estatus: 'IRREGULAR', promedio: 65 },
        { id: 4, noControl: '20230004', nombre: 'ANA GARCIA RODRIGUEZ', estatus: 'REGULAR', promedio: 88 },
        { id: 5, noControl: '20230005', nombre: 'LUIS FERNANDEZ CASTRO', estatus: 'IRREGULAR', promedio: 58 }
      ]
    }
  } catch (error) {
    console.error('Error cargando alumnos:', error)
  } finally {
    cargandoAlumnos.value = false
  }
}

const cargarCalificaciones = async () => {
  cargandoCalificaciones.value = true
  try {
    const id = route.params.id
    const res = await fetch(`${API_URL}/api/grupos/${id}/calificaciones`)
    if (res.ok) {
      calificaciones.value = await res.json()
    } else {
      // Datos de ejemplo
      calificaciones.value = [
        { id: 1, noControl: '20230001', nombre: 'JUAN PEREZ GONZALEZ', unidad1: 85, unidad2: 88, unidad3: 82, unidad4: 90, promedio: 86 },
        { id: 2, noControl: '20230002', nombre: 'MARIA LOPEZ MARTINEZ', unidad1: 92, unidad2: 95, unidad3: 88, unidad4: 93, promedio: 92 },
        { id: 3, noControl: '20230003', nombre: 'CARLOS RAMIREZ SANCHEZ', unidad1: 60, unidad2: 65, unidad3: 70, unidad4: 65, promedio: 65 },
        { id: 4, noControl: '20230004', nombre: 'ANA GARCIA RODRIGUEZ', unidad1: 85, unidad2: 90, unidad3: 87, unidad4: 89, promedio: 88 },
        { id: 5, noControl: '20230005', nombre: 'LUIS FERNANDEZ CASTRO', unidad1: 55, unidad2: 60, unidad3: 58, unidad4: 62, promedio: 59 }
      ]
    }
  } catch (error) {
    console.error('Error cargando calificaciones:', error)
  } finally {
    cargandoCalificaciones.value = false
  }
}

// Acciones de alumnos
const agregarAlumno = () => {
  router.push(`/formulario-alumno?grupo=${route.params.id}`)
}

const verAlumno = (alumno) => {
  router.push(`/alumnos/${alumno.id}`)
}

const editarAlumno = (alumno) => {
  router.push(`/formulario-alumno/${alumno.id}`)
}

const eliminarAlumno = (alumno) => {
  if (confirm(`¿ELIMINAR A ${alumno.nombre.toUpperCase()} DEL GRUPO?`)) {
    // Implementar eliminación
    console.log('Eliminar alumno:', alumno)
  }
}

// Acciones de calificaciones
const editarCalificacion = (calif, unidad) => {
  califEditando.value = calif
  califUnidadEditando.value = unidad
  califValorEditando.value = calif[unidad] || 0
  showModalCalif.value = true
}

const editarTodasCalificaciones = (calif) => {
  // Navegar a página de edición completa de calificaciones
  router.push(`/calificaciones/${route.params.id}/alumno/${calif.id}`)
}

const guardarCalificacion = () => {
  if (califEditando.value && califUnidadEditando.value) {
    // Actualizar valor local
    califEditando.value[califUnidadEditando.value] = califValorEditando.value
    
    // Recalcular promedio
    const { unidad1, unidad2, unidad3, unidad4 } = califEditando.value
    const califs = [unidad1, unidad2, unidad3, unidad4].filter(c => c !== null && c !== undefined)
    const promedio = califs.length > 0 ? Math.round(califs.reduce((a, b) => a + b, 0) / califs.length) : 0
    califEditando.value.promedio = promedio
    
    // Aquí iría la llamada a la API para guardar
    console.log('Guardar calificación:', califEditando.value)
    
    showModalCalif.value = false
  }
}

const exportarLista = () => {
  console.log('Exportar lista de alumnos')
}

const exportarCalificaciones = () => {
  console.log('Exportar calificaciones')
}

onMounted(() => {
  cargarDatos()
})
</script>

<style scoped>
/* ═══════════════════════════════════════════════
   GRUPO DETAIL - DESIGN SYSTEM SICE
   Fuente: Montserrat, Lucide Icons, MAYÚSCULAS
═══════════════════════════════════════════════ */

.grupo-detail-page {
  --azul: #1B396A;
  --azul-hover: #15305A;
  --azul-suave: #DBEAFE;
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

/* Barra de carga */
.barra-carga { position: fixed; top: 0; left: 0; right: 0; height: 2px; z-index: 9999; opacity: 0; transition: opacity 0.2s; pointer-events: none; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: var(--azul); animation: progreso 1.5s infinite; }
@keyframes progreso { 0% { width: 0%; } 70% { width: 85%; } 100% { width: 100%; opacity: 0; } }

/* Navegación */
.nav-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem; }
.btn-back { display: flex; align-items: center; gap: 6px; background: white; border: 1px solid var(--borde); border-radius: 6px; padding: 6px 12px; font-size: 0.7rem; font-weight: 700; cursor: pointer; letter-spacing: 0.05em; }
.btn-back:hover { border-color: var(--azul); color: var(--azul); }
.breadcrumb-trail { display: flex; align-items: center; gap: 4px; font-size: 0.65rem; font-weight: 600; color: var(--gris); letter-spacing: 0.05em; }
.bc-link { color: var(--gris); text-decoration: none; }
.bc-link:hover { color: var(--azul); }
.bc-active { color: var(--azul); }

/* Header grupo */
.grupo-header {
  background: white;
  border: 1px solid var(--borde);
  border-left: 4px solid var(--azul);
  border-radius: 12px;
  padding: 1rem 1.2rem;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
}
.header-left { display: flex; align-items: center; gap: 1rem; }
.header-icon { width: 52px; height: 52px; background: var(--azul-suave); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--azul); }
.grupo-nombre { font-size: 1.1rem; font-weight: 800; margin: 0 0 6px; letter-spacing: -0.01em; }
.header-tags { display: flex; gap: 6px; flex-wrap: wrap; }
.tag { font-size: 0.6rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; background: var(--fondo); color: var(--gris); letter-spacing: 0.05em; }
.tag-matutino { background: #FEF3C7; color: #D97706; }
.tag-vespertino { background: #EDE9FE; color: #7C3AED; }
.header-stats { display: flex; align-items: center; background: var(--fondo); border-radius: 10px; padding: 0.5rem 1rem; }
.stat { display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 0 1rem; }
.stat-val { font-size: 1.1rem; font-weight: 800; }
.stat-lbl { font-size: 0.55rem; font-weight: 700; color: var(--gris); letter-spacing: 0.08em; }
.stat-sep { width: 1px; height: 30px; background: var(--borde); }
.text-verde { color: var(--verde); }
.text-rojo { color: var(--rojo); }

/* Info grid */
.info-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.8rem; margin-bottom: 1rem; }
@media (max-width: 768px) { .info-grid { grid-template-columns: repeat(2, 1fr); } }
.info-card { display: flex; align-items: center; gap: 0.8rem; background: white; border: 1px solid var(--borde); border-radius: 10px; padding: 0.7rem; }
.info-content { display: flex; flex-direction: column; }
.info-label { font-size: 0.6rem; font-weight: 700; color: var(--gris); letter-spacing: 0.08em; }
.info-value { font-size: 0.75rem; font-weight: 700; color: var(--texto); }

/* Tabs */
.tabs-bar { display: flex; gap: 2px; margin-bottom: 1rem; border-bottom: 1px solid var(--borde); }
.tab-btn { display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: none; border: none; border-bottom: 2px solid transparent; margin-bottom: -1px; font-size: 0.7rem; font-weight: 700; color: var(--gris); cursor: pointer; letter-spacing: 0.05em; }
.tab-btn:hover { color: var(--azul); }
.tab-btn.activo { color: var(--azul); border-bottom-color: var(--azul); }
.tab-count { background: var(--borde); color: var(--gris); font-size: 0.55rem; font-weight: 800; padding: 1px 6px; border-radius: 20px; }

/* Toolbars */
.alumnos-toolbar, .calificaciones-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem; }
.search-group { position: relative; flex: 1; max-width: 300px; }
.search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); stroke: var(--gris); }
.search-input { width: 100%; padding: 7px 12px 7px 34px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.7rem; font-family: inherit; }
.btn-agregar, .btn-exportar, .btn-primary { display: flex; align-items: center; gap: 6px; background: var(--azul); color: white; border: none; padding: 7px 14px; border-radius: 8px; font-size: 0.65rem; font-weight: 700; cursor: pointer; letter-spacing: 0.05em; }
.btn-exportar { background: var(--gris); }
.btn-primary { background: var(--azul); }
.btn-primary:hover, .btn-agregar:hover { background: var(--azul-hover); }
.btn-exportar:hover { background: #4B5563; }

/* Tablas */
.alumnos-table-container, .calificaciones-table-container { overflow-x: auto; background: white; border-radius: 12px; border: 1px solid var(--borde); }
.alumnos-table, .calificaciones-table { width: 100%; border-collapse: collapse; font-size: 0.7rem; }
.alumnos-table th, .calificaciones-table th { background: var(--fondo); padding: 10px 12px; text-align: left; font-weight: 700; color: var(--texto); border-bottom: 1px solid var(--borde); letter-spacing: 0.05em; }
.alumnos-table td, .calificaciones-table td { padding: 8px 12px; border-bottom: 1px solid var(--borde); }
.celda-control { font-family: monospace; font-weight: 600; }
.celda-nombre .link-alumno { color: var(--azul); cursor: pointer; font-weight: 600; }
.celda-nombre .link-alumno:hover { text-decoration: underline; }
.calif-edit { cursor: pointer; display: inline-block; padding: 2px 6px; border-radius: 4px; transition: background 0.15s; }
.calif-edit:hover { background: var(--azul-suave); }
.text-bold { font-weight: 700; }
.text-center { text-align: center; }

/* Estatus badges */
.estatus-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 0.65rem; font-weight: 700; }
.estatus-badge.activo, .estatus-badge.aprobado { background: #DCFCE7; color: var(--verde); }
.estatus-badge.inactivo, .estatus-badge.reprobado { background: #FEE2E2; color: var(--rojo); }

/* Acciones botones */
.acciones { display: flex; gap: 4px; justify-content: center; }
.btn-icono { display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; border: none; cursor: pointer; background: var(--fondo); transition: all 0.15s; }
.btn-icono.ver:hover { background: var(--azul-suave); }
.btn-icono.editar:hover { background: #DCFCE7; }
.btn-icono.eliminar:hover { background: #FEE2E2; }

/* Estados de carga y vacío */
.loading-state { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 2rem; color: var(--gris); }
.spinner { width: 30px; height: 30px; border: 2px solid var(--borde); border-top-color: var(--azul); border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.estado-vacio { text-align: center; padding: 2rem; color: var(--gris); }
.btn-secondary { background: white; border: 1px solid var(--borde); padding: 7px 16px; border-radius: 8px; font-weight: 600; font-size: 0.7rem; cursor: pointer; margin-top: 0.5rem; }
.btn-secondary:hover { border-color: var(--azul); color: var(--azul); }
.error-container { text-align: center; padding: 2rem; background: white; border-radius: 12px; border: 1px solid var(--borde); }
.error-icon { stroke: var(--rojo); margin-bottom: 0.5rem; }

/* Modal pequeño */
.modal-small { width: 380px; }
.modal-alumno { font-size: 0.8rem; font-weight: 700; text-align: center; margin-bottom: 1rem; color: var(--azul); }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.modal-content { background: white; width: 500px; max-width: 90%; border-radius: 12px; overflow: hidden; }
.modal-header { background: var(--azul); color: white; padding: 0.8rem 1.2rem; display: flex; justify-content: space-between; align-items: center; }
.modal-header h3 { margin: 0; font-size: 0.85rem; font-weight: 700; letter-spacing: 0.05em; }
.close-btn { background: none; border: none; color: white; cursor: pointer; display: flex; }
.modal-body { padding: 1.2rem; }
.modal-footer { padding: 0.8rem 1.2rem; background: var(--fondo); display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid var(--borde); }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 4px; font-size: 0.7rem; font-weight: 700; color: var(--gris); letter-spacing: 0.05em; }
.modal-input { width: 100%; padding: 8px 12px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.8rem; font-family: inherit; }
.btn-cancelar { background: white; border: 1px solid var(--borde); padding: 7px 16px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; cursor: pointer; }
.btn-guardar { background: var(--azul); color: white; border: none; padding: 7px 16px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; cursor: pointer; }

/* Responsive */
@media (max-width: 768px) {
  .grupo-header { flex-direction: column; align-items: flex-start; }
  .header-stats { width: 100%; justify-content: space-around; }
  .info-grid { grid-template-columns: 1fr; }
  .alumnos-toolbar, .calificaciones-toolbar { flex-direction: column; }
  .search-group { max-width: 100%; width: 100%; }
  .btn-agregar, .btn-exportar, .btn-primary { width: 100%; justify-content: center; }
  .acciones { flex-wrap: wrap; }
}
</style>