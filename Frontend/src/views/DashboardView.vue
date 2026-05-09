<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="inicio-page">
      <div class="inicio-header">
        <div>
          <h1 class="page-title">Inicio</h1>
          <p class="welcome-text">Bienvenido al Sistema Integral de Control Escolar</p>
        </div>
        <span class="fecha-actual">{{ fechaHoy }}</span>
      </div>

      <div class="barra-carga" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <div v-if="error" class="mensaje-error">
        {{ error }}
      </div>

      <div class="kpi-grid">
        <div class="kpi-card" v-for="(kpi, index) in kpis" :key="index">
          <div class="kpi-icono-wrapper" :style="{ background: kpi.fondo }">
            <svg xmlns="http://www.w3.org/2000/svg" class="kpi-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path :d="kpi.iconPath" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
            </svg>
          </div>
          <div class="kpi-contenido">
            <p class="kpi-etiqueta">{{ kpi.title }}</p>
            <p class="kpi-valor">{{ kpi.value }}</p>
            <p
              v-if="kpi.trend"
              class="kpi-tendencia"
              :class="{ positivo: kpi.trend.includes('+'), negativo: kpi.trend.includes('-') }"
            >
              {{ kpi.trend }}
            </p>
          </div>
        </div>
      </div>

      <div class="fila-graficas">
        <div class="grafica-card">
          <h3 class="grafica-titulo">Alumnos por carrera</h3>
          <div v-if="carreraData.length > 0" class="grafica-barras">
            <div v-for="(item, i) in carreraData" :key="i" class="barra-item">
              <div class="barra-etiqueta" :title="item.carrera">{{ item.carrera }}</div>
              <div class="barra-contenedor">
                <div class="barra-relleno" :style="{ width: item.porcentaje + '%' }"></div>
              </div>
              <div class="barra-valor">{{ item.porcentaje }}%</div>
            </div>
          </div>
          <div v-else class="estado-vacio-grafica">
            <p>Sin datos disponibles</p>
          </div>
        </div>

        <div class="grafica-card">
          <h3 class="grafica-titulo">Alumnos por semestre</h3>
          <div v-if="semestreData.length > 0" class="grafica-barras">
            <div v-for="(item, i) in semestreData" :key="i" class="barra-item">
              <div class="barra-etiqueta">{{ item.semestre }}° Semestre</div>
              <div class="barra-contenedor">
                <div
                  class="barra-relleno barra-acento"
                  :style="{ width: calcularPorcentajeSemestre(item.cantidad) + '%' }"
                ></div>
              </div>
              <div class="barra-valor">{{ item.cantidad }}</div>
            </div>
          </div>
          <div v-else class="estado-vacio-grafica">
            <p>Sin datos disponibles</p>
          </div>
        </div>
      </div>

      <div class="fila-inferior">
        <div class="panel-card">
          <h3 class="panel-titulo">Actividad reciente</h3>
          <div class="lista-actividad">
            <div
              v-for="(act, i) in filtrarActividad(recentActivity, busquedaGlobal)"
              :key="i"
              class="item-actividad"
            >
              <div class="punto-actividad"></div>
              <div class="info-actividad">
                <p class="desc-actividad">{{ act.descripcion }}</p>
                <p class="tiempo-actividad">{{ act.tiempo }}</p>
              </div>
            </div>
            <div v-if="filtrarActividad(recentActivity, busquedaGlobal).length === 0" class="estado-vacio-grafica">
              <p>{{ busquedaGlobal ? 'Sin coincidencias en actividad reciente' : 'Sin actividad reciente' }}</p>
            </div>
          </div>
        </div>

        <div class="panel-card">
          <h3 class="panel-titulo">Acciones rápidas</h3>
          <div class="grilla-acciones">
            <button @click="nuevaInscripcion" class="btn-accion btn-primario">
              <svg xmlns="http://www.w3.org/2000/svg" class="accion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
              Nueva inscripción
            </button>
            <button @click="irAAlumnos" class="btn-accion">
              <svg xmlns="http://www.w3.org/2000/svg" class="accion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              Lista de alumnos
            </button>
            <button @click="irAGrupos" class="btn-accion">
              <svg xmlns="http://www.w3.org/2000/svg" class="accion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Gestión de grupos
            </button>
            <button @click="irACalificaciones" class="btn-accion">
              <svg xmlns="http://www.w3.org/2000/svg" class="accion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              Cargar calificaciones
            </button>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const router = useRouter()
const cargando = ref(true)
const error = ref(null)
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000'

const fechaHoy = computed(() => {
  return new Date().toLocaleDateString('es-MX', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

const kpis = ref([
  {
    title: 'Alumnos activos',
    value: '0',
    iconPath: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    fondo: '#DBEAFE',
    trend: ''
  },
  {
    title: 'Inscripciones',
    value: '0',
    iconPath: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
    fondo: '#DCFCE7',
    trend: ''
  },
  {
    title: 'Baja temporal',
    value: '0',
    iconPath: 'M13 10V3L4 14h7v7l9-11h-7z',
    fondo: '#FEF3C7',
    trend: ''
  },
  {
    title: 'Baja definitiva',
    value: '0',
    iconPath: 'M6 18L18 6M6 6l12 12',
    fondo: '#FEE2E2',
    trend: ''
  },
  {
    title: 'Grupos activos',
    value: '0',
    iconPath: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    fondo: '#F3E8FF',
    trend: ''
  },
  {
    title: 'Promedio general',
    value: '0',
    iconPath: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    fondo: '#E0F2FE',
    trend: ''
  }
])

const carreraData = ref([])
const semestreData = ref([])
const recentActivity = ref([])

const maxSemestre = computed(() =>
  semestreData.value.reduce((max, s) => Math.max(max, Number(s.cantidad) || 0), 1)
)

const calcularPorcentajeSemestre = (cantidad) =>
  Math.round(((Number(cantidad) || 0) / maxSemestre.value) * 100)

const normalizar = (valor) =>
  String(valor || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')

const filtrarActividad = (actividad, busqueda) => {
  const termino = normalizar(busqueda)
  if (!termino) return actividad

  return actividad.filter((item) =>
    normalizar(`${item.descripcion} ${item.tiempo}`).includes(termino)
  )
}

const formatearNumero = (valor) =>
  new Intl.NumberFormat('es-MX').format(Number(valor) || 0)

const tiempoRelativo = (fecha) => {
  if (!fecha) return 'Ahora'

  const date = new Date(fecha)
  if (Number.isNaN(date.getTime())) return 'Ahora'

  const segundos = Math.max(0, Math.floor((Date.now() - date.getTime()) / 1000))
  if (segundos < 60) return 'Hace unos segundos'

  const minutos = Math.floor(segundos / 60)
  if (minutos < 60) return `Hace ${minutos} min`

  const horas = Math.floor(minutos / 60)
  if (horas < 24) return `Hace ${horas} h`

  const dias = Math.floor(horas / 24)
  if (dias < 7) return `Hace ${dias} d`

  return date.toLocaleDateString('es-MX', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const cargarDashboard = async () => {
  cargando.value = true
  error.value = null

  try {
    const res = await fetch(`${API_URL}/api/dashboard`, {
      headers: { Accept: 'application/json' }
    })

    const data = await res.json().catch(() => ({}))
    if (!res.ok) throw new Error(data.mensaje || data.error || 'Error al cargar el dashboard')

    kpis.value[0].value = formatearNumero(data.kpis?.alumnos)
    kpis.value[1].value = formatearNumero(data.kpis?.inscripciones)
    kpis.value[2].value = formatearNumero(data.kpis?.bajas_temporales ?? data.kpis?.baja_temporal)
    kpis.value[3].value = formatearNumero(data.kpis?.bajas_definitivas ?? data.kpis?.baja_definitiva)
    kpis.value[4].value = formatearNumero(data.kpis?.grupos)
    kpis.value[5].value = Number(data.kpis?.promedio || 0).toFixed(2)

    const carreras = data.carreras || []
    const totalCarreras = carreras.reduce((acc, c) => acc + (Number(c.total) || 0), 0) || 1
    carreraData.value = carreras.map((c) => ({
      carrera: c.nombre || 'Sin carrera',
      porcentaje: Math.round(((Number(c.total) || 0) / totalCarreras) * 100)
    }))

    semestreData.value = (data.semestres || []).map((s) => ({
      semestre: s.semestre_actual || 'N/D',
      cantidad: Number(s.total) || 0
    }))

    recentActivity.value = (data.actividad_reciente || []).map((actividad) => ({
      descripcion: actividad.descripcion || 'Actividad del sistema',
      tiempo: tiempoRelativo(actividad.fecha)
    }))
  } catch (err) {
    console.error(err)
    error.value = err.message || 'No se pudieron cargar los datos del dashboard'
    recentActivity.value = []
  } finally {
    cargando.value = false
  }
}

onMounted(cargarDashboard)

const nuevaInscripcion = () => router.push('/inscripcion')
const irAAlumnos = () => router.push('/alumnos')
const irAGrupos = () => router.push('/gestion-grupos')
const irACalificaciones = () => router.push('/calificaciones')
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.inicio-page {
  max-width: 100%;
  background: #F5F5F5;
  font-family: 'Montserrat', sans-serif;
  padding-bottom: 2rem;
}

.inicio-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 1.4rem;
}

.page-title {
  color: #1A1A1A;
  font-size: 1.75rem;
  font-weight: 700;
  margin: 0 0 4px;
}

.welcome-text {
  color: #6B7280;
  font-size: 0.95rem;
  margin: 0;
}

.fecha-actual {
  font-size: 0.88rem;
  color: #6B7280;
  font-weight: 500;
  text-transform: capitalize;
  white-space: nowrap;
  padding-top: 4px;
}

.barra-carga {
  height: 3px;
  background: transparent;
  border-radius: 2px;
  margin-bottom: 1.2rem;
  overflow: hidden;
  opacity: 0;
  transition: opacity 0.3s;
}

.barra-carga.visible { opacity: 1; }

.barra-progreso {
  height: 100%;
  width: 40%;
  background: #1B396A;
  border-radius: 2px;
  animation: deslizar 1.2s ease-in-out infinite;
}

@keyframes deslizar {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(350%); }
}

.mensaje-error {
  background: #FEF2F2;
  border: 1px solid #FECACA;
  color: #B91C1C;
  border-radius: 8px;
  padding: 0.85rem 1rem;
  margin-bottom: 1rem;
  font-size: 0.9rem;
  font-weight: 600;
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.kpi-card,
.grafica-card,
.panel-card {
  background: #FFFFFF;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  border: 1px solid #E5E7EB;
}

.kpi-card {
  padding: 1.2rem 1.4rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.2s, box-shadow 0.2s;
}

.kpi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.09);
}

.kpi-icono-wrapper {
  width: 46px;
  height: 46px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.kpi-icono {
  width: 24px;
  height: 24px;
  stroke: #1B396A;
}

.kpi-etiqueta {
  font-size: 0.85rem;
  color: #6B7280;
  margin: 0;
}

.kpi-valor {
  font-size: 1.7rem;
  font-weight: 700;
  color: #1A1A1A;
  margin: 2px 0;
}

.kpi-tendencia {
  font-size: 0.82rem;
  font-weight: 600;
  margin: 0;
}

.kpi-tendencia.positivo { color: #16A34A; }
.kpi-tendencia.negativo { color: #DC2626; }

.fila-graficas {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.grafica-card,
.panel-card {
  padding: 1.4rem 1.6rem;
}

.grafica-titulo,
.panel-titulo {
  margin: 0 0 1rem;
  font-size: 1rem;
  font-weight: 600;
  color: #1A1A1A;
}

.grafica-barras {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.barra-item {
  display: flex;
  align-items: center;
  gap: 10px;
}

.barra-etiqueta {
  width: 120px;
  font-size: 0.85rem;
  color: #1A1A1A;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex-shrink: 0;
}

.barra-contenedor {
  flex: 1;
  height: 10px;
  background: #E5E7EB;
  border-radius: 9999px;
  overflow: hidden;
}

.barra-relleno {
  height: 100%;
  background: #1B396A;
  border-radius: 9999px;
  transition: width 0.9s ease;
}

.barra-acento { background: #B38E5D; }

.barra-valor {
  width: 36px;
  text-align: right;
  font-weight: 600;
  font-size: 0.88rem;
  color: #1B396A;
  flex-shrink: 0;
}

.estado-vacio-grafica {
  text-align: center;
  padding: 2rem 0;
  color: #9CA3AF;
  font-size: 0.9rem;
}

.fila-inferior {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1rem;
}

.lista-actividad {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.item-actividad {
  display: flex;
  gap: 10px;
  align-items: flex-start;
}

.punto-actividad {
  width: 8px;
  height: 8px;
  background: #1B396A;
  border-radius: 50%;
  margin-top: 6px;
  flex-shrink: 0;
}

.desc-actividad {
  margin: 0;
  color: #1A1A1A;
  font-size: 0.93rem;
}

.tiempo-actividad {
  margin: 2px 0 0;
  color: #6B7280;
  font-size: 0.82rem;
}

.grilla-acciones {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.btn-accion {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  min-height: 86px;
  padding: 14px 10px;
  background: #F5F5F5;
  color: #1A1A1A;
  border: 1px solid #E5E7EB;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.82rem;
  text-align: center;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  transition: background 0.15s, border-color 0.15s;
  line-height: 1.3;
}

.btn-accion:hover { background: #E5E7EB; }

.btn-accion.btn-primario {
  background: #1B396A;
  color: white;
  border-color: #1B396A;
}

.btn-accion.btn-primario:hover {
  background: #1D4ED8;
  border-color: #1D4ED8;
}

.accion-icono {
  width: 22px;
  height: 22px;
  stroke: currentColor;
  flex-shrink: 0;
}

@media (max-width: 900px) {
  .fila-graficas,
  .fila-inferior {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .inicio-header {
    flex-direction: column;
    gap: 0.5rem;
  }

  .fecha-actual {
    white-space: normal;
  }

  .grilla-acciones {
    grid-template-columns: 1fr;
  }
}
</style>
