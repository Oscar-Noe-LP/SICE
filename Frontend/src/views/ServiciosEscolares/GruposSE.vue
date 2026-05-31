<template>
  <MainLayout>
    <div class="grupos-se">
      <!-- Nivel 1: Carreras -->
      <div v-if="nivel === 1" class="nivel-container">
        <div class="nivel-header">
          <h2 class="nivel-titulo">SELECCIONA UNA CARRERA</h2>
          <p class="nivel-subtitulo">{{ carreras.length }} carreras disponibles</p>
        </div>
        <div class="cards-grid">
          <div 
            v-for="carrera in carreras" 
            :key="carrera.id"
            class="card carrera-card"
            @click="seleccionarCarrera(carrera)"
          >
            <div class="card-icon">
              <GraduationCap :size="28" />
            </div>
            <h3 class="card-titulo">{{ carrera.nombre }}</h3>
            <p class="card-clave">{{ carrera.clave }}</p>
            <div class="card-footer">
              <span class="card-badge">{{ carrera.totalGrupos || 0 }} GRUPOS</span>
              <ChevronRight :size="16" class="card-arrow" />
            </div>
          </div>
        </div>
      </div>

      <!-- Nivel 2: Semestres -->
      <div v-else-if="nivel === 2" class="nivel-container">
        <div class="nivel-header">
          <button class="btn-back" @click="volverACarreras">
            <ArrowLeft :size="16" />
            VOLVER
          </button>
          <div>
            <h2 class="nivel-titulo">{{ carreraActual?.nombre }}</h2>
            <p class="nivel-subtitulo">SELECCIONA UN SEMESTRE</p>
          </div>
        </div>
        <div class="cards-grid">
          <div 
            v-for="semestre in semestres" 
            :key="semestre.numero"
            class="card semestre-card"
            @click="seleccionarSemestre(semestre)"
          >
            <div class="semestre-numero">{{ semestre.numero }}°</div>
            <h3 class="card-titulo">SEMESTRE {{ semestre.numero }}</h3>
            <p class="card-info">{{ semestre.gruposCount || 0 }} grupos</p>
            <ChevronRight :size="16" class="card-arrow" />
          </div>
        </div>
      </div>

      <!-- Nivel 3: Grupos -->
      <div v-else-if="nivel === 3" class="nivel-container">
        <div class="nivel-header">
          <button class="btn-back" @click="volverASemestres">
            <ArrowLeft :size="16" />
            VOLVER
          </button>
          <div>
            <h2 class="nivel-titulo">{{ carreraActual?.nombre }} - {{ semestreActual?.numero }}° SEMESTRE</h2>
            <p class="nivel-subtitulo">SELECCIONA UN GRUPO</p>
          </div>
        </div>
        <div class="cards-grid">
          <div 
            v-for="grupo in grupos" 
            :key="grupo.id"
            class="card grupo-card"
            @click="irADetalleGrupo(grupo)"
          >
            <div class="grupo-nombre">{{ grupo.nombre }}</div>
            <div class="grupo-info">
              <div class="info-item">
                <User :size="14" />
                <span>{{ grupo.tutor || 'SIN TUTOR' }}</span>
              </div>
              <div class="info-item">
                <Users :size="14" />
                <span>{{ grupo.inscritos || 0 }} inscritos</span>
              </div>
              <div class="info-item">
                <TrendingUp :size="14" />
                <span :class="grupo.promedio >= 70 ? 'text-verde' : 'text-rojo'">
                  {{ grupo.promedio || 0 }} promedio
                </span>
              </div>
            </div>
            <div class="card-footer">
              <span class="card-badge">VER DETALLE</span>
              <ChevronRight :size="16" class="card-arrow" />
            </div>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="cargando" class="loading-state">
        <div class="spinner"></div>
        <span>CARGANDO...</span>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import { 
  GraduationCap, 
  ChevronRight, 
  ArrowLeft, 
  User, 
  Users, 
  TrendingUp 
} from 'lucide-vue-next'

const router = useRouter()
const API_URL = import.meta.env.VITE_API_URL

// Estado de navegación
const nivel = ref(1) // 1=carreras, 2=semestres, 3=grupos
const cargando = ref(false)

// Datos
const carreras = ref([])
const carreraActual = ref(null)
const semestres = ref([])
const semestreActual = ref(null)
const grupos = ref([])

// Cargar carreras
const cargarCarreras = async () => {
  cargando.value = true
  try {
    const res = await fetch(`${API_URL}/api/carreras`)
    if (res.ok) {
      carreras.value = await res.json()
    } else {
      // Datos mock para demo
      carreras.value = [
        { id: 1, clave: 'ISC', nombre: 'INGENIERÍA EN SISTEMAS COMPUTACIONALES', totalGrupos: 12 },
        { id: 2, clave: 'IIA', nombre: 'INGENIERÍA INDUSTRIAL', totalGrupos: 10 },
        { id: 3, clave: 'IGE', nombre: 'INGENIERÍA EN GESTIÓN EMPRESARIAL', totalGrupos: 8 },
        { id: 4, clave: 'IC', nombre: 'INGENIERÍA CIVIL', totalGrupos: 6 }
      ]
    }
  } catch (error) {
    console.error('Error cargando carreras:', error)
  } finally {
    cargando.value = false
  }
}

// Seleccionar carrera y cargar semestres
const seleccionarCarrera = async (carrera) => {
  carreraActual.value = carrera
  cargando.value = true
  
  try {
    const res = await fetch(`${API_URL}/api/carreras/${carrera.id}/semestres`)
    if (res.ok) {
      semestres.value = await res.json()
    } else {
      // Datos mock
      semestres.value = [
        { numero: 1, gruposCount: 3 },
        { numero: 2, gruposCount: 4 },
        { numero: 3, gruposCount: 4 },
        { numero: 4, gruposCount: 3 },
        { numero: 5, gruposCount: 3 },
        { numero: 6, gruposCount: 3 },
        { numero: 7, gruposCount: 2 },
        { numero: 8, gruposCount: 2 }
      ]
    }
    nivel.value = 2
  } catch (error) {
    console.error('Error cargando semestres:', error)
  } finally {
    cargando.value = false
  }
}

// Seleccionar semestre y cargar grupos
const seleccionarSemestre = async (semestre) => {
  semestreActual.value = semestre
  cargando.value = true
  
  try {
    const res = await fetch(`${API_URL}/api/carreras/${carreraActual.value.id}/semestres/${semestre.numero}/grupos`)
    if (res.ok) {
      grupos.value = await res.json()
    } else {
      // Datos mock
      grupos.value = [
        { id: 1, nombre: 'A', tutor: 'MTRO. JUAN PEREZ', inscritos: 35, promedio: 82 },
        { id: 2, nombre: 'B', tutor: 'DRA. MARIA LOPEZ', inscritos: 32, promedio: 78 },
        { id: 3, nombre: 'C', tutor: 'MTRO. CARLOS RAMIREZ', inscritos: 30, promedio: 85 },
        { id: 4, nombre: 'D', tutor: 'DRA. ANA GARCIA', inscritos: 28, promedio: 74 }
      ]
    }
    nivel.value = 3
  } catch (error) {
    console.error('Error cargando grupos:', error)
  } finally {
    cargando.value = false
  }
}

const irADetalleGrupo = (grupo) => {
  router.push(`/gestion-grupos/${grupo.id}`)
}

// Navegación hacia atrás
const volverACarreras = () => {
  nivel.value = 1
  carreraActual.value = null
  semestres.value = []
}

const volverASemestres = () => {
  nivel.value = 2
  semestreActual.value = null
  grupos.value = []
}

onMounted(() => {
  cargarCarreras()
})
</script>

<style scoped>
.grupos-se {
  font-family: 'Montserrat', sans-serif;
  padding: 1.5rem;
  background: #F4F6F9;
  min-height: 100vh;
}

* {
  font-family: 'Montserrat', sans-serif;
}

.nivel-container {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.nivel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.nivel-titulo {
  font-size: 1.5rem;
  font-weight: 700;
  color: #333333;
  margin: 0;
  font-family: 'Montserrat', sans-serif;
}

.nivel-subtitulo {
  font-size: 0.75rem;
  font-weight: 500;
  color: #828282;
  letter-spacing: 0.05em;
  margin: 0;
  font-family: 'Montserrat', sans-serif;
}

.btn-back {
  display: flex;
  align-items: center;
  gap: 6px;
  background: white;
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  padding: 8px 16px;
  font-size: 0.7rem;
  font-weight: 700;
  color: #4F4F4F;
  cursor: pointer;
  transition: all 0.2s;
  font-family: 'Montserrat', sans-serif;
}

.btn-back:hover {
  border-color: #1D52B7;
  color: #1D52B7;
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.25rem;
}

.card {
  background: white;
  border-radius: 12px;
  padding: 1.25rem;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(29, 82, 183, 0.05);
}

.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(29, 82, 183, 0.12);
}

.carrera-card .card-icon {
  width: 48px;
  height: 48px;
  background: rgba(29, 82, 183, 0.1);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #1D52B7;
  margin-bottom: 1rem;
}

.card-titulo {
  font-size: 1rem;
  font-weight: 700;
  color: #333333;
  margin: 0 0 0.5rem 0;
  font-family: 'Montserrat', sans-serif;
}

.card-clave {
  font-size: 0.7rem;
  font-weight: 600;
  color: #828282;
  letter-spacing: 0.05em;
  margin-bottom: 1rem;
  font-family: 'Montserrat', sans-serif;
}

.card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #E0E0E0;
}

.card-badge {
  font-size: 0.6rem;
  font-weight: 700;
  color: #1D52B7;
  background: rgba(29, 82, 183, 0.1);
  padding: 4px 10px;
  border-radius: 20px;
  letter-spacing: 0.05em;
  font-family: 'Montserrat', sans-serif;
}

.card-arrow {
  color: #828282;
  transition: transform 0.2s;
}

.card:hover .card-arrow {
  transform: translateX(4px);
  color: #1D52B7;
}

.semestre-card {
  text-align: center;
}

.semestre-numero {
  font-size: 2.5rem;
  font-weight: 800;
  color: #1D52B7;
  margin-bottom: 0.5rem;
  font-family: 'Montserrat', sans-serif;
}

.grupo-card .grupo-nombre {
  font-size: 1.5rem;
  font-weight: 800;
  color: #0B2545;
  margin-bottom: 1rem;
  font-family: 'Montserrat', sans-serif;
}

.grupo-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.7rem;
  font-weight: 600;
  color: #4F4F4F;
  font-family: 'Montserrat', sans-serif;
}

.text-verde {
  color: #27AE60;
}

.text-rojo {
  color: #EB5757;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  gap: 1rem;
  color: #828282;
  font-family: 'Montserrat', sans-serif;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #E0E0E0;
  border-top-color: #1D52B7;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
  .grupos-se {
    padding: 1rem;
  }
  
  .cards-grid {
    grid-template-columns: 1fr;
  }
  
  .nivel-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>