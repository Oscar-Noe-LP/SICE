<!-- src/views/ServiciosEscolares/GrupoDetailView.vue -->
<template>
  <MainLayout>
    <div class="grupo-detail-page">
      
      <div class="page-header">
        <div style="display:flex;align-items:center;gap:12px">
          <button class="btn-back" @click="volverAGrupos">
            <ArrowLeft :size="16" />
            VOLVER A GRUPOS
          </button>
          <h1 class="page-title">Detalle de grupo</h1>
        </div>
      </div>

      <!-- Barra de carga -->
      <div class="barra-carga" :class="{ activa: cargando }">
        <div class="barra-progreso"></div>
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

      <!-- ==================== TAB: INFORMACIÓN ==================== -->
      <div v-if="tabActivo === 'informacion'" class="info-completa-tab">
        <div class="info-grid-completa">
          <div class="info-section">
            <h3 class="section-title">DATOS GENERALES</h3>
            <div class="info-row">
              <span class="info-row-label">Clave del grupo:</span>
              <span class="info-row-value">{{ grupo.clave || grupo.nombre || 'N/A' }}</span>
            </div>
            <div class="info-row">
              <span class="info-row-label">Semestre:</span>
              <span class="info-row-value">{{ grupo.semestre }}° Semestre</span>
            </div>
            <div class="info-row">
              <span class="info-row-label">Turno:</span>
              <span class="info-row-value">{{ grupo.turno || 'MATUTINO' }}</span>
            </div>
            <div class="info-row">
              <span class="info-row-label">Carrera:</span>
              <span class="info-row-value">{{ grupo.carrera }}</span>
            </div>
            <div class="info-row">
              <span class="info-row-label">Plan de estudios:</span>
              <span class="info-row-value">{{ grupo.planEstudios || '2020' }}</span>
            </div>
          </div>

          <div class="info-section">
            <h3 class="section-title">UBICACIÓN Y HORARIO</h3>
            <div class="info-row">
              <span class="info-row-label">Aula:</span>
              <span class="info-row-value">{{ grupo.aula || 'SIN ASIGNAR' }}</span>
            </div>
            <div class="info-row">
              <span class="info-row-label">Edificio:</span>
              <span class="info-row-value">{{ grupo.edificio || 'A' }}</span>
            </div>
            <div class="info-row">
              <span class="info-row-label">Horario:</span>
              <span class="info-row-value">{{ grupo.horario || 'SIN HORARIO' }}</span>
            </div>
            <div class="info-row">
              <span class="info-row-label">Días de clase:</span>
              <span class="info-row-value">{{ grupo.diasClase || 'LUNES, MIÉRCOLES, VIERNES' }}</span>
            </div>
          </div>

          <div class="info-section">
            <h3 class="section-title">CAPACIDAD Y ESTADÍSTICAS</h3>
            <div class="info-row">
              <span class="info-row-label">Capacidad máxima:</span>
              <span class="info-row-value">{{ grupo.capacidad || 40 }} alumnos</span>
            </div>
            <div class="info-row">
              <span class="info-row-label">Inscritos actuales:</span>
              <span class="info-row-value">{{ grupo.inscritos }} alumnos</span>
            </div>
            <div class="info-row">
              <span class="info-row-label">Disponibilidad:</span>
              <span class="info-row-value" :class="disponibilidadClass">{{ disponibilidad }}%</span>
            </div>
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: disponibilidad + '%' }"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- ==================== TAB: TUTOR ==================== -->
      <div v-if="tabActivo === 'tutor'" class="tutor-tab">
        <div class="tutor-card">
          <div class="tutor-avatar">
            <User :size="48" />
          </div>
          <div class="tutor-info">
            <h3 class="tutor-nombre">{{ grupo.docente || 'SIN ASIGNAR' }}</h3>
            <div class="tutor-detalles">
              <div class="tutor-item">
                <Mail :size="14" />
                <span>{{ grupo.tutorCorreo || 'docente@tecnm.mx' }}</span>
              </div>
              <div class="tutor-item">
                <Phone :size="14" />
                <span>{{ grupo.tutorTelefono || 'N/A' }}</span>
              </div>
              <div class="tutor-item">
                <Award :size="14" />
                <span>{{ grupo.tutorGrado || 'MAESTRÍA' }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="tutor-horario">
          <h3 class="section-title">HORARIO DE TUTORÍAS</h3>
          <div class="horario-tutorias">
            <div class="dia-tutoria" v-for="tutoria in tutorias" :key="tutoria.dia">
              <span class="dia">{{ tutoria.dia }}</span>
              <span class="hora">{{ tutoria.hora }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ==================== TAB: MATERIAS ==================== -->
      <div v-if="tabActivo === 'materias'" class="materias-tab">
        <div class="materias-grid">
          <div v-for="materia in materias" :key="materia.id" class="materia-card">
            <div class="materia-header">
              <BookOpen :size="20" />
              <h4>{{ materia.nombre }}</h4>
            </div>
            <div class="materia-body">
              <div class="materia-info">
                <span class="label">CLAVE:</span>
                <span class="value">{{ materia.clave }}</span>
              </div>
              <div class="materia-info">
                <span class="label">HORAS:</span>
                <span class="value">{{ materia.horas }} hrs/sem</span>
              </div>
              <div class="materia-info">
                <span class="label">DOCENTE:</span>
                <span class="value">{{ materia.docente || grupo.docente }}</span>
              </div>
            </div>
            <div class="materia-footer">
              <span class="materia-estatus" :class="materia.estatus === 'ACTIVA' ? 'activa' : 'inactiva'">
                {{ materia.estatus || 'ACTIVA' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- ==================== TAB: ALUMNOS ==================== -->
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

        <div v-else>
          <div class="alumnos-table-container">
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
          </div>

          <div v-if="alumnosFiltrados.length === 0" class="estado-vacio">
            <Inbox :size="40" />
            <h3>SIN ALUMNOS</h3>
            <p>NO HAY ALUMNOS INSCRITOS EN ESTE GRUPO</p>
            <button class="btn-secondary" @click="agregarAlumno">AGREGAR ALUMNO</button>
          </div>
        </div>
      </div>

      <!-- ==================== TAB: CALIFICACIONES ==================== -->
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

        <div v-else>
          <div class="calificaciones-table-container">
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
          </div>

          <div v-if="calificacionesFiltradas.length === 0" class="estado-vacio">
            <Inbox :size="40" />
            <h3>SIN CALIFICACIONES</h3>
            <p>NO HAY CALIFICACIONES REGISTRADAS PARA ESTE GRUPO</p>
          </div>
        </div>
      </div>

      <!-- ==================== TAB: HORARIO VISUAL ==================== -->
      <div v-if="tabActivo === 'horario'" class="horario-visual-tab">
        <div class="horario-grid">
          <div class="horario-header"></div>
          <div class="horario-header">LUNES</div>
          <div class="horario-header">MARTES</div>
          <div class="horario-header">MIÉRCOLES</div>
          <div class="horario-header">JUEVES</div>
          <div class="horario-header">VIERNES</div>
          
          <template v-for="hora in horasDisponibles" :key="hora.hora">
            <div class="horario-hora">{{ hora.label }}</div>
            <div 
              v-for="dia in ['LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES']" 
              :key="dia"
              class="horario-celda"
              :class="{ materia: obtenerMateriaEnHorario(dia, hora.hora) }"
            >
              {{ obtenerMateriaEnHorario(dia, hora.hora) || '' }}
            </div>
          </template>
        </div>
        <div class="horario-leyenda">
          <div class="leyenda-item"><span class="color-materia"></span> MATERIA ASIGNADA</div>
          <div class="leyenda-item"><span class="color-vacio"></span> SIN CLASE</div>
        </div>
      </div>

      <!-- ==================== TAB: ESTADÍSTICAS Y KPIs ==================== -->
      <div v-if="tabActivo === 'estadisticas'" class="estadisticas-tab">
        <div class="kpis-grid">
          <div class="kpi-card">
            <div class="kpi-valor" :class="(grupo.promedioGeneral || 78) >= 70 ? 'text-verde' : 'text-rojo'">
              {{ grupo.promedioGeneral || 78 }}
            </div>
            <div class="kpi-label">PROMEDIO GENERAL</div>
          </div>
          <div class="kpi-card">
            <div class="kpi-valor text-verde">{{ tasaAprobacion }}%</div>
            <div class="kpi-label">TASA DE APROBACIÓN</div>
          </div>
          <div class="kpi-card">
            <div class="kpi-valor text-rojo">{{ tasaReprobacion }}%</div>
            <div class="kpi-label">TASA DE REPROBACIÓN</div>
          </div>
          <div class="kpi-card">
            <div class="kpi-valor">{{ grupo.asistenciaPromedio || 92 }}%</div>
            <div class="kpi-label">ASISTENCIA PROMEDIO</div>
          </div>
          <div class="kpi-card">
            <div class="kpi-valor">{{ grupo.inscritos || 0 }}</div>
            <div class="kpi-label">TOTAL ALUMNOS</div>
          </div>
          <div class="kpi-card">
            <div class="kpi-valor text-verde">{{ grupo.regulares || 0 }}</div>
            <div class="kpi-label">ALUMNOS REGULARES</div>
          </div>
        </div>

        <div class="graficas-container">
          <div class="grafica-card">
            <h4>DISTRIBUCIÓN DE CALIFICACIONES</h4>
            <div class="bar-chart">
              <div class="bar-item">
                <div class="bar-label">0-59</div>
                <div class="bar-bg"><div class="bar-fill" style="width: 15%; background: #EB5757;"></div></div>
                <div class="bar-value">15%</div>
              </div>
              <div class="bar-item">
                <div class="bar-label">60-69</div>
                <div class="bar-bg"><div class="bar-fill" style="width: 20%; background: #F2994A;"></div></div>
                <div class="bar-value">20%</div>
              </div>
              <div class="bar-item">
                <div class="bar-label">70-79</div>
                <div class="bar-bg"><div class="bar-fill" style="width: 35%; background: #F4F6F9;"></div></div>
                <div class="bar-value">35%</div>
              </div>
              <div class="bar-item">
                <div class="bar-label">80-89</div>
                <div class="bar-bg"><div class="bar-fill" style="width: 20%; background: #27AE60;"></div></div>
                <div class="bar-value">20%</div>
              </div>
              <div class="bar-item">
                <div class="bar-label">90-100</div>
                <div class="bar-bg"><div class="bar-fill" style="width: 10%; background: #2F80ED;"></div></div>
                <div class="bar-value">10%</div>
              </div>
            </div>
          </div>

          <div class="grafica-card">
            <h4>RENDIMIENTO POR MATERIA</h4>
            <div class="materias-rendimiento">
              <div v-for="materia in materiasRendimiento" :key="materia.nombre" class="rendimiento-item">
                <span class="rendimiento-nombre">{{ materia.nombre }}</span>
                <div class="rendimiento-bar">
                  <div class="rendimiento-fill" :style="{ width: materia.promedio + '%', background: materia.promedio >= 70 ? '#27AE60' : '#EB5757' }"></div>
                </div>
                <span class="rendimiento-valor">{{ materia.promedio }}</span>
              </div>
            </div>
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
  X,
  Mail,
  Phone,
  Award,
  BookOpen,
  Info,
  TrendingUp
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const API_URL = import.meta.env.VITE_API_URL

// Estado
const cargando = ref(true)
const cargandoAlumnos = ref(false)
const cargandoCalificaciones = ref(false)
const errorCarga = ref(null)
const tabActivo = ref('informacion')
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
  clave: '',
  semestre: '',
  turno: '',
  carrera: '',
  planEstudios: '2020',
  inscritos: 0,
  regulares: 0,
  irregulares: 0,
  horario: '',
  aula: '',
  edificio: 'A',
  diasClase: 'LUNES, MIÉRCOLES, VIERNES',
  docente: '',
  capacidad: 0,
  promedioGeneral: 78,
  asistenciaPromedio: 92,
  tutorCorreo: 'docente@tecnm.mx',
  tutorTelefono: 'N/A',
  tutorGrado: 'MAESTRÍA'
})

const alumnos = ref([])
const calificaciones = ref([])

// Datos de materias
const materias = ref([
  { id: 1, clave: 'SCA-1001', nombre: 'PROGRAMACIÓN WEB', horas: 5, docente: 'DR. JUAN CARLOS PEREZ', estatus: 'ACTIVA' },
  { id: 2, clave: 'SCA-1002', nombre: 'BASES DE DATOS', horas: 4, docente: 'MTRA. MARIA LOPEZ', estatus: 'ACTIVA' },
  { id: 3, clave: 'SCA-1003', nombre: 'INGENIERÍA DE SOFTWARE', horas: 4, docente: 'DR. CARLOS RAMIREZ', estatus: 'ACTIVA' },
  { id: 4, clave: 'SCA-1004', nombre: 'REDES DE COMPUTADORAS', horas: 3, docente: 'MTRO. LUIS FERNANDEZ', estatus: 'ACTIVA' }
])

// Horario visual
const horasDisponibles = ref([
  { hora: 7, label: '07:00 - 08:00' },
  { hora: 8, label: '08:00 - 09:00' },
  { hora: 9, label: '09:00 - 10:00' },
  { hora: 10, label: '10:00 - 11:00' },
  { hora: 11, label: '11:00 - 12:00' },
  { hora: 12, label: '12:00 - 13:00' },
  { hora: 13, label: '13:00 - 14:00' },
  { hora: 14, label: '14:00 - 15:00' },
  { hora: 15, label: '15:00 - 16:00' },
  { hora: 16, label: '16:00 - 17:00' }
])

const horarioData = ref([
  { dia: 'LUNES', hora: 7, materia: 'MATEMÁTICAS' },
  { dia: 'LUNES', hora: 9, materia: 'PROGRAMACIÓN' },
  { dia: 'MARTES', hora: 8, materia: 'FÍSICA' },
  { dia: 'MARTES', hora: 10, materia: 'BASES DE DATOS' },
  { dia: 'MIÉRCOLES', hora: 7, materia: 'MATEMÁTICAS' },
  { dia: 'MIÉRCOLES', hora: 11, materia: 'REDES' },
  { dia: 'JUEVES', hora: 8, materia: 'FÍSICA' },
  { dia: 'JUEVES', hora: 9, materia: 'PROGRAMACIÓN' },
  { dia: 'VIERNES', hora: 10, materia: 'BASES DE DATOS' }
])

const tutorias = ref([
  { dia: 'LUNES', hora: '13:00 - 14:00' },
  { dia: 'MIÉRCOLES', hora: '13:00 - 14:00' },
  { dia: 'VIERNES', hora: '12:00 - 13:00' }
])

const materiasRendimiento = ref([
  { nombre: 'PROGRAMACIÓN WEB', promedio: 85 },
  { nombre: 'BASES DE DATOS', promedio: 78 },
  { nombre: 'INGENIERÍA DE SOFTWARE', promedio: 82 },
  { nombre: 'REDES DE COMPUTADORAS', promedio: 74 },
  { nombre: 'MATEMÁTICAS', promedio: 68 }
])

// Tabs
const tabs = computed(() => [
  { key: 'informacion', label: 'INFORMACIÓN', icono: Info, count: null },
  { key: 'tutor', label: 'TUTOR', icono: User, count: null },
  { key: 'materias', label: 'MATERIAS', icono: BookOpen, count: materias.value.length },
  { key: 'alumnos', label: 'ALUMNOS', icono: Users, count: alumnos.value.length },
  { key: 'calificaciones', label: 'CALIFICACIONES', icono: GraduationCap, count: null },
  { key: 'horario', label: 'HORARIO', icono: Calendar, count: null },
  { key: 'estadisticas', label: 'ESTADÍSTICAS', icono: TrendingUp, count: null }
])

// Computed
const disponibilidad = computed(() => {
  if (!grupo.value.capacidad) return 100
  return Math.round(((grupo.value.capacidad - (grupo.value.inscritos || 0)) / grupo.value.capacidad) * 100)
})

const disponibilidadClass = computed(() => {
  if (disponibilidad.value >= 30) return 'text-verde'
  if (disponibilidad.value >= 10) return 'text-amarillo'
  return 'text-rojo'
})

const tasaAprobacion = computed(() => {
  const aprobados = calificaciones.value.filter(c => c.promedio >= 70).length
  return calificaciones.value.length ? Math.round((aprobados / calificaciones.value.length) * 100) : 0
})

const tasaReprobacion = computed(() => {
  const reprobados = calificaciones.value.filter(c => c.promedio < 70).length
  return calificaciones.value.length ? Math.round((reprobados / calificaciones.value.length) * 100) : 0
})

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

// Métodos
const obtenerMateriaEnHorario = (dia, hora) => {
  const clase = horarioData.value.find(h => h.dia === dia && h.hora === hora)
  return clase?.materia || ''
}

// Después:
const volverAGrupos = () => {
  router.push('/gestion-grupos/lista')  // ✅
}

const cargarDatos = async () => {
  cargando.value = true
  errorCarga.value = null

  try {
    const id = route.params.id
    if (!id) throw new Error('ID DE GRUPO NO PROPORCIONADO')

    const grupoRes = await fetch(`${API_URL}/api/grupos/${id}`)
    if (grupoRes.ok) {
      const data = await grupoRes.json()
      grupo.value = {
        id: data.id_grupo,
        nombre: data.nombre_grupo,
        clave: data.clave_grupo,
        semestre: data.semestre,
        turno: data.turno || 'MATUTINO',
        carrera: data.carrera_nombre,
        planEstudios: data.plan_estudios || '2020',
        inscritos: data.total_alumnos || 0,
        regulares: data.alumnos_regulares || 0,
        irregulares: data.alumnos_irregulares || 0,
        horario: `${data.dia || ''} ${data.hora_inicio || ''} - ${data.hora_fin || ''}`.trim() || 'SIN HORARIO',
        aula: data.aula_nombre,
        edificio: data.edificio_nombre || 'A',
        diasClase: data.dias_clase || 'LUNES, MIÉRCOLES, VIERNES',
        docente: data.docente_nombre,
        capacidad: data.capacidad,
        promedioGeneral: data.promedio_general || 78,
        asistenciaPromedio: data.asistencia_promedio || 92,
        tutorCorreo: data.tutor_correo,
        tutorTelefono: data.tutor_telefono,
        tutorGrado: data.tutor_grado
      }
    } else {
      grupo.value = {
        id: id,
        nombre: `GRUPO 101`,
        clave: `G-101`,
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

// Acciones
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
    console.log('Eliminar alumno:', alumno)
  }
}

const editarCalificacion = (calif, unidad) => {
  califEditando.value = calif
  califUnidadEditando.value = unidad
  califValorEditando.value = calif[unidad] || 0
  showModalCalif.value = true
}

const editarTodasCalificaciones = (calif) => {
  router.push(`/calificaciones/${route.params.id}/alumno/${calif.id}`)
}

const guardarCalificacion = () => {
  if (califEditando.value && califUnidadEditando.value) {
    califEditando.value[califUnidadEditando.value] = califValorEditando.value
    
    const { unidad1, unidad2, unidad3, unidad4 } = califEditando.value
    const califs = [unidad1, unidad2, unidad3, unidad4].filter(c => c !== null && c !== undefined)
    const promedio = califs.length > 0 ? Math.round(califs.reduce((a, b) => a + b, 0) / califs.length) : 0
    califEditando.value.promedio = promedio
    
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

.grupo-detail-page * {
  font-family: 'Montserrat', sans-serif;
}

.page-header { margin-bottom: 1rem; }
.page-title { color: #1A1A1A; font-size: 1.75rem; font-weight: 700; letter-spacing: -.02em; margin: 0; font-family: 'Montserrat', sans-serif; }

.btn-back {
  display: flex;
  align-items: center;
  gap: 6px;
  background: white;
  border: 1px solid var(--borde);
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 0.7rem;
  font-weight: 700;
  color: #4F4F4F;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-back:hover {
  border-color: var(--azul);
  color: var(--azul);
}

/* Barra de carga */
.barra-carga { position: fixed; top: 0; left: 0; right: 0; height: 2px; z-index: 9999; opacity: 0; transition: opacity 0.2s; pointer-events: none; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: var(--azul); animation: progreso 1.5s infinite; }
@keyframes progreso { 0% { width: 0%; } 70% { width: 85%; } 100% { width: 100%; opacity: 0; } }

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
.text-amarillo { color: var(--amarillo); }

/* Tabs */
.tabs-bar { display: flex; gap: 2px; margin-bottom: 1rem; border-bottom: 1px solid var(--borde); overflow-x: auto; }
.tab-btn { display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: none; border: none; border-bottom: 2px solid transparent; margin-bottom: -1px; font-size: 0.7rem; font-weight: 700; color: var(--gris); cursor: pointer; letter-spacing: 0.05em; white-space: nowrap; }
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

/* Estados de carga */
.loading-state { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 2rem; color: var(--gris); }
.spinner { width: 30px; height: 30px; border: 2px solid var(--borde); border-top-color: var(--azul); border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.estado-vacio { text-align: center; padding: 2rem; color: var(--gris); }
.btn-secondary { background: white; border: 1px solid var(--borde); padding: 7px 16px; border-radius: 8px; font-weight: 600; font-size: 0.7rem; cursor: pointer; margin-top: 0.5rem; }
.btn-secondary:hover { border-color: var(--azul); color: var(--azul); }
.error-container { text-align: center; padding: 2rem; background: white; border-radius: 12px; border: 1px solid var(--borde); }
.error-icon { stroke: var(--rojo); margin-bottom: 0.5rem; }

/* Modal */
.modal-small { width: 380px; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.modal-content { background: white; width: 500px; max-width: 90%; border-radius: 12px; overflow: hidden; }
.modal-header { background: var(--azul); color: white; padding: 0.8rem 1.2rem; display: flex; justify-content: space-between; align-items: center; }
.modal-header h3 { margin: 0; font-size: 0.85rem; font-weight: 700; letter-spacing: 0.05em; }
.close-btn { background: none; border: none; color: white; cursor: pointer; }
.modal-body { padding: 1.2rem; }
.modal-footer { padding: 0.8rem 1.2rem; background: var(--fondo); display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid var(--borde); }
.modal-alumno { font-size: 0.8rem; font-weight: 700; text-align: center; margin-bottom: 1rem; color: var(--azul); }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 4px; font-size: 0.7rem; font-weight: 700; color: var(--gris); letter-spacing: 0.05em; }
.modal-input { width: 100%; padding: 8px 12px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.8rem; font-family: inherit; }
.btn-cancelar { background: white; border: 1px solid var(--borde); padding: 7px 16px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; cursor: pointer; }
.btn-guardar { background: var(--azul); color: white; border: none; padding: 7px 16px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; cursor: pointer; }

/* ========== NUEVOS ESTILOS PARA TABS ADICIONALES ========== */

/* Información completa */
.info-grid-completa {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 1rem;
}

.info-section {
  background: white;
  border: 1px solid var(--borde);
  border-radius: 12px;
  padding: 1rem;
}

.section-title {
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--texto);
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid var(--azul);
  letter-spacing: 0.05em;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid var(--borde);
}

.info-row:last-child {
  border-bottom: none;
}

.info-row-label {
  font-size: 0.7rem;
  font-weight: 600;
  color: var(--gris);
}

.info-row-value {
  font-size: 0.7rem;
  font-weight: 700;
  color: var(--texto);
}

.progress-bar {
  height: 8px;
  background: var(--borde);
  border-radius: 4px;
  margin-top: 0.5rem;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: var(--verde);
  border-radius: 4px;
  transition: width 0.3s;
}

/* Tutor tab */
.tutor-card {
  background: white;
  border: 1px solid var(--borde);
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  gap: 1.5rem;
  align-items: center;
  margin-bottom: 1rem;
}

.tutor-avatar {
  width: 80px;
  height: 80px;
  background: var(--azul-suave);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--azul);
}

.tutor-nombre {
  font-size: 1.2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.tutor-detalles {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.tutor-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.75rem;
  color: var(--gris);
}

.tutor-horario {
  background: white;
  border: 1px solid var(--borde);
  border-radius: 12px;
  padding: 1rem;
}

.horario-tutorias {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.dia-tutoria {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem;
  border-bottom: 1px solid var(--borde);
}

.dia {
  font-weight: 700;
  color: var(--texto);
}

.hora {
  color: var(--gris);
}

/* Materias tab */
.materias-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.materia-card {
  background: white;
  border: 1px solid var(--borde);
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.2s;
}

.materia-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(29, 82, 183, 0.1);
}

.materia-header {
  background: var(--azul-suave);
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--azul);
}

.materia-header h4 {
  margin: 0;
  font-size: 0.9rem;
  font-weight: 700;
}

.materia-body {
  padding: 1rem;
}

.materia-info {
  display: flex;
  justify-content: space-between;
  padding: 0.3rem 0;
}

.materia-info .label {
  font-size: 0.65rem;
  font-weight: 600;
  color: var(--gris);
}

.materia-info .value {
  font-size: 0.65rem;
  font-weight: 700;
  color: var(--texto);
}

.materia-footer {
  padding: 0.75rem 1rem;
  border-top: 1px solid var(--borde);
}

.materia-estatus {
  font-size: 0.6rem;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 20px;
}

.materia-estatus.activa {
  background: #DCFCE7;
  color: var(--verde);
}

.materia-estatus.inactiva {
  background: #FEE2E2;
  color: var(--rojo);
}

/* Horario visual */
.horario-visual-tab {
  background: white;
  border: 1px solid var(--borde);
  border-radius: 12px;
  padding: 1rem;
  overflow-x: auto;
}

.horario-grid {
  display: grid;
  grid-template-columns: 100px repeat(5, 1fr);
  gap: 1px;
  background: var(--borde);
  border: 1px solid var(--borde);
  border-radius: 8px;
  overflow: hidden;
}

.horario-header {
  background: var(--azul);
  color: white;
  padding: 10px;
  font-weight: 700;
  font-size: 0.7rem;
  text-align: center;
}

.horario-hora {
  background: var(--fondo);
  padding: 10px;
  font-size: 0.65rem;
  font-weight: 700;
  text-align: center;
}

.horario-celda {
  background: white;
  padding: 10px;
  font-size: 0.65rem;
  text-align: center;
  min-height: 50px;
}

.horario-celda.materia {
  background: #E8F0FE;
  font-weight: 600;
  color: var(--azul);
}

.horario-leyenda {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--borde);
}

.leyenda-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.65rem;
}

.color-materia {
  width: 16px;
  height: 16px;
  background: #E8F0FE;
  border-radius: 4px;
}

.color-vacio {
  width: 16px;
  height: 16px;
  background: white;
  border: 1px solid var(--borde);
  border-radius: 4px;
}

/* Estadísticas y KPIs */
.estadisticas-tab {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.kpis-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
}

.kpi-card {
  background: white;
  border: 1px solid var(--borde);
  border-radius: 12px;
  padding: 1rem;
  text-align: center;
  transition: all 0.2s;
}

.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(29, 82, 183, 0.1);
}

.kpi-valor {
  font-size: 2rem;
  font-weight: 800;
  font-family: 'Montserrat', sans-serif;
  margin-bottom: 0.25rem;
}

.kpi-label {
  font-size: 0.6rem;
  font-weight: 700;
  color: var(--gris);
  letter-spacing: 0.05em;
}

.graficas-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1rem;
}

.grafica-card {
  background: white;
  border: 1px solid var(--borde);
  border-radius: 12px;
  padding: 1rem;
}

.grafica-card h4 {
  font-size: 0.8rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: var(--texto);
}

.bar-chart {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.bar-item {
  display: flex;
  align-items: center;
  gap: 10px;
}

.bar-label {
  width: 45px;
  font-size: 0.65rem;
  font-weight: 600;
  color: var(--gris);
}

.bar-bg {
  flex: 1;
  height: 24px;
  background: var(--borde);
  border-radius: 4px;
  overflow: hidden;
}

.bar-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.3s;
}

.bar-value {
  width: 40px;
  font-size: 0.65rem;
  font-weight: 700;
  text-align: right;
}

.materias-rendimiento {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.rendimiento-item {
  display: flex;
  align-items: center;
  gap: 10px;
}

.rendimiento-nombre {
  width: 160px;
  font-size: 0.65rem;
  font-weight: 600;
  color: var(--texto);
}

.rendimiento-bar {
  flex: 1;
  height: 20px;
  background: var(--borde);
  border-radius: 4px;
  overflow: hidden;
}

.rendimiento-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.3s;
}

.rendimiento-valor {
  width: 40px;
  font-size: 0.65rem;
  font-weight: 700;
  text-align: right;
}

/* Responsive */
@media (max-width: 768px) {
  .grupo-header { flex-direction: column; align-items: flex-start; }
  .header-stats { width: 100%; justify-content: space-around; }
  .alumnos-toolbar, .calificaciones-toolbar { flex-direction: column; }
  .search-group { max-width: 100%; width: 100%; }
  .btn-agregar, .btn-exportar, .btn-primary { width: 100%; justify-content: center; }
  .acciones { flex-wrap: wrap; }
  .kpis-grid { grid-template-columns: repeat(2, 1fr); }
  .graficas-container { grid-template-columns: 1fr; }
  .materias-grid { grid-template-columns: 1fr; }
  .info-grid-completa { grid-template-columns: 1fr; }
}

@media (max-width: 480px) {
  .kpis-grid { grid-template-columns: 1fr; }
  .rendimiento-nombre { width: 120px; }
}
</style>