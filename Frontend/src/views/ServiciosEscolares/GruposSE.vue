<template>
  <MainLayout>
    <div class="grupos-se">

      <!-- ══════════════════════════════════════════
           NIVEL 1: Selección de Carrera
      ══════════════════════════════════════════ -->
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

      <!-- ══════════════════════════════════════════
           NIVEL 2: Grupos agrupados por Semestre
      ══════════════════════════════════════════ -->
      <div v-else-if="nivel === 2" class="nivel-container">
        <div class="nivel-header">
          <button class="btn-back" @click="volverACarreras">
            <ArrowLeft :size="16" />
            VOLVER
          </button>
          <div>
            <h2 class="nivel-titulo">{{ carreraActual?.nombre }}</h2>
            <p class="nivel-subtitulo">GRUPOS POR SEMESTRE</p>
          </div>
        </div>

        <!-- Bloque por semestre -->
        <div
          v-for="bloque in semestresAgrupados"
          :key="bloque.semestre"
          class="semestre-bloque"
        >
          <!-- Encabezado del semestre -->
          <div class="semestre-encabezado">
            <div class="semestre-badge">{{ bloque.semestre }}°</div>
            <span class="semestre-label">SEMESTRE {{ bloque.semestre }}</span>
            <span class="semestre-resumen">
              {{ bloque.grupos.length }} grupo{{ bloque.grupos.length !== 1 ? 's' : '' }}
              ·
              {{ bloque.grupos.reduce((s, g) => s + g.inscritos, 0) }} inscritos
            </span>
          </div>

          <!-- Tarjetas de grupos del semestre -->
          <div class="grupos-grid">
            <div
              v-for="grupo in bloque.grupos"
              :key="grupo.clave_grupo"
              class="card grupo-card"
              @click="irADetalleGrupo(grupo, bloque.semestre)"
            >
              <!-- Nombre del grupo -->
              <div class="grupo-clave">{{ grupo.clave_grupo }}</div>

              <!-- Barra de ocupación -->
              <div class="ocupacion-wrap">
                <div class="ocupacion-bar-bg">
                  <div
                    class="ocupacion-bar-fill"
                    :class="ocupacionClase(grupo.ocupacion)"
                    :style="{ width: grupo.ocupacion + '%' }"
                  ></div>
                </div>
                <span class="ocupacion-pct" :class="ocupacionClase(grupo.ocupacion)">
                  {{ grupo.ocupacion }}%
                </span>
              </div>

              <!-- Métricas -->
              <div class="grupo-metricas">
                <div class="metrica">
                  <Users :size="13" />
                  <span>{{ grupo.inscritos }} / {{ grupo.capacidad }}</span>
                  <span class="metrica-label">INSCRITOS</span>
                </div>
                <div class="metrica">
                  <BookOpen :size="13" />
                  <span>{{ grupo.total_materias }}</span>
                  <span class="metrica-label">MATERIAS</span>
                </div>
              </div>

              <div class="card-footer">
                <span class="card-badge">VER DETALLE</span>
                <ChevronRight :size="16" class="card-arrow" />
              </div>
            </div>
          </div>
        </div>

        <!-- Sin datos -->
        <div v-if="!cargando && semestresAgrupados.length === 0" class="empty-state">
          <BookOpen :size="40" />
          <p>Esta carrera no tiene grupos activos</p>
        </div>
      </div>

      <!-- ══════════════════════════════════════════
           NIVEL 3: Detalle del Grupo (ruta directa)
      ══════════════════════════════════════════ -->
      <div v-else-if="nivel === 3" class="nivel-container">
        <div class="nivel-header">
          <button class="btn-back" @click="volverAGrupos">
            <ArrowLeft :size="16" />
            VOLVER
          </button>
          <div>
            <h2 class="nivel-titulo">
              {{ carreraActual?.nombre }} — {{ grupoActual?.clave_grupo }}
            </h2>
            <p class="nivel-subtitulo">{{ semestreActual }}° SEMESTRE</p>
          </div>
        </div>
        <!-- El detalle real vive en GrupoDetailView; aquí sólo navegamos -->
      </div>

      <!-- Loading global -->
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
  Users,
  BookOpen,
} from 'lucide-vue-next'

const router  = useRouter()
const API_URL = import.meta.env.VITE_API_URL

// ── Estado de navegación ──────────────────────────
const nivel           = ref(1)   // 1=carreras | 2=grupos agrupados | 3=detalle
const cargando        = ref(false)

// ── Datos ─────────────────────────────────────────
const carreras          = ref([])
const carreraActual     = ref(null)
const semestresAgrupados = ref([])   // [{ semestre, grupos: [...] }]
const semestreActual    = ref(null)
const grupoActual       = ref(null)

// ── Helpers ───────────────────────────────────────
const ocupacionClase = (pct) => {
  if (pct >= 90) return 'ocupacion-alta'
  if (pct >= 60) return 'ocupacion-media'
  return 'ocupacion-baja'
}

// ── Cargar carreras ───────────────────────────────
const cargarCarreras = async () => {
  cargando.value = true
  try {
    const res = await fetch(`${API_URL}/api/carreras`)
    if (res.ok) {
      carreras.value = await res.json()
    } else {
      // Mock de emergencia
      carreras.value = [
        { id: 1, clave: 'ISC', nombre: 'INGENIERÍA EN SISTEMAS COMPUTACIONALES', totalGrupos: 12 },
        { id: 2, clave: 'IIA', nombre: 'INGENIERÍA INDUSTRIAL',                   totalGrupos: 10 },
        { id: 3, clave: 'IGE', nombre: 'INGENIERÍA EN GESTIÓN EMPRESARIAL',        totalGrupos: 8  },
        { id: 4, clave: 'IC',  nombre: 'INGENIERÍA CIVIL',                         totalGrupos: 6  },
      ]
    }
  } catch (e) {
    console.error('Error cargando carreras:', e)
  } finally {
    cargando.value = false
  }
}

// ── Seleccionar carrera → carga grupos agrupados ──
const seleccionarCarrera = async (carrera) => {
  carreraActual.value    = carrera
  semestresAgrupados.value = []
  cargando.value         = true

  try {
    const res = await fetch(`${API_URL}/api/carreras/${carrera.id}/grupos-agrupados`)
    if (res.ok) {
      const data = await res.json()
      semestresAgrupados.value = data.semestres   // [{ semestre, grupos:[...] }]
    } else {
      // Mock alineado con los datos de ejemplo del enunciado
      semestresAgrupados.value = [
        { semestre: 2, grupos: [
          { clave_grupo: '2A', total_materias: 120, inscritos: 0,  capacidad: 25, ocupacion: 0  },
          { clave_grupo: '2B', total_materias: 120, inscritos: 0,  capacidad: 25, ocupacion: 0  },
        ]},
        { semestre: 4, grupos: [
          { clave_grupo: '4A', total_materias: 120, inscritos: 0,  capacidad: 25, ocupacion: 0  },
          { clave_grupo: '4B', total_materias: 120, inscritos: 0,  capacidad: 25, ocupacion: 0  },
        ]},
        { semestre: 6, grupos: [
          { clave_grupo: '6A', total_materias: 121, inscritos: 1,  capacidad: 25, ocupacion: 4  },
        ]},
        { semestre: 7, grupos: [
          { clave_grupo: '8A', total_materias: 1,   inscritos: 3,  capacidad: 25, ocupacion: 12 },
        ]},
        { semestre: 8, grupos: [
          { clave_grupo: '8A', total_materias: 6,   inscritos: 3,  capacidad: 25, ocupacion: 12 },
        ]},
      ]
    }
    nivel.value = 2
  } catch (e) {
    console.error('Error cargando grupos agrupados:', e)
  } finally {
    cargando.value = false
  }
}

// ── Ir al detalle del grupo ───────────────────────
const irADetalleGrupo = (grupo, semestre) => {
  semestreActual.value = semestre
  grupoActual.value    = grupo
  // GrupoDetailView espera el id_grupo; aquí sólo tenemos clave_grupo.
  // Si el backend devuelve id_grupo inclúyelo en el SELECT y pásalo aquí.
  router.push({
    path: `/gestion-grupos/${grupo.id_grupo ?? grupo.clave_grupo}`,
    query: {
      carrera:  carreraActual.value?.id,
      semestre: semestre,
      clave:    grupo.clave_grupo,
    }
  })
}

// ── Navegación hacia atrás ────────────────────────
const volverACarreras = () => {
  nivel.value              = 1
  carreraActual.value      = null
  semestresAgrupados.value = []
}

const volverAGrupos = () => {
  nivel.value      = 2
  grupoActual.value = null
}

onMounted(cargarCarreras)
</script>

<style scoped>
.grupos-se {
  font-family: 'Montserrat', sans-serif;
  padding: 1.5rem;
  background: #F4F6F9;
  min-height: 100vh;
}

* { font-family: 'Montserrat', sans-serif; }

/* ── Animación ── */
.nivel-container { animation: fadeIn 0.3s ease; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ── Encabezado de nivel ── */
.nivel-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.nivel-titulo {
  font-size: 1.4rem;
  font-weight: 700;
  color: #1B2A4A;
  margin: 0;
}
.nivel-subtitulo {
  font-size: 0.7rem;
  font-weight: 600;
  color: #828282;
  letter-spacing: 0.06em;
  margin: 0;
  text-transform: uppercase;
}

/* ── Botón volver ── */
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
  white-space: nowrap;
}
.btn-back:hover { border-color: #1D52B7; color: #1D52B7; }

/* ── Grid genérico ── */
.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.25rem;
}

/* ── Tarjeta base ── */
.card {
  background: white;
  border-radius: 12px;
  padding: 1.25rem;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(29, 82, 183, 0.05);
  border: 1px solid transparent;
}
.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(29, 82, 183, 0.13);
  border-color: rgba(29, 82, 183, 0.18);
}

/* ── Carrera card ── */
.carrera-card .card-icon {
  width: 48px; height: 48px;
  background: rgba(29, 82, 183, 0.1);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  color: #1D52B7;
  margin-bottom: 1rem;
}
.card-titulo { font-size: 0.95rem; font-weight: 700; color: #1B2A4A; margin: 0 0 0.4rem 0; }
.card-clave  { font-size: 0.65rem; font-weight: 700; color: #828282; letter-spacing: 0.06em; margin-bottom: 1rem; }
.card-footer {
  display: flex; align-items: center; justify-content: space-between;
  margin-top: 1rem; padding-top: 1rem;
  border-top: 1px solid #F0F0F0;
}
.card-badge {
  font-size: 0.6rem; font-weight: 700;
  color: #1D52B7;
  background: rgba(29, 82, 183, 0.1);
  padding: 4px 10px; border-radius: 20px;
  letter-spacing: 0.05em;
}
.card-arrow { color: #BDBDBD; transition: transform 0.2s; }
.card:hover .card-arrow { transform: translateX(4px); color: #1D52B7; }

/* ══ BLOQUE DE SEMESTRE ══════════════════════════ */
.semestre-bloque {
  margin-bottom: 2rem;
}

.semestre-encabezado {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.9rem;
  padding-bottom: 0.6rem;
  border-bottom: 2px solid #E8EDF5;
}

.semestre-badge {
  width: 38px; height: 38px;
  background: #1D52B7;
  color: white;
  font-size: 1rem;
  font-weight: 800;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}

.semestre-label {
  font-size: 0.85rem;
  font-weight: 700;
  color: #1B2A4A;
  letter-spacing: 0.04em;
}

.semestre-resumen {
  margin-left: auto;
  font-size: 0.65rem;
  font-weight: 600;
  color: #828282;
  letter-spacing: 0.04em;
}

/* ── Grid de grupos del semestre ── */
.grupos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
}

/* ── Grupo card ── */
.grupo-card {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.grupo-clave {
  font-size: 1.8rem;
  font-weight: 800;
  color: #0B2545;
  line-height: 1;
}

/* ── Barra de ocupación ── */
.ocupacion-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}
.ocupacion-bar-bg {
  flex: 1;
  height: 7px;
  background: #EEF1F7;
  border-radius: 10px;
  overflow: hidden;
}
.ocupacion-bar-fill {
  height: 100%;
  border-radius: 10px;
  transition: width 0.4s ease;
}
.ocupacion-baja  .ocupacion-bar-fill, .ocupacion-bar-fill.ocupacion-baja  { background: #27AE60; }
.ocupacion-media .ocupacion-bar-fill, .ocupacion-bar-fill.ocupacion-media { background: #F2994A; }
.ocupacion-alta  .ocupacion-bar-fill, .ocupacion-bar-fill.ocupacion-alta  { background: #EB5757; }

.ocupacion-pct {
  font-size: 0.65rem;
  font-weight: 700;
  width: 34px;
  text-align: right;
  flex-shrink: 0;
}
.ocupacion-baja  { color: #27AE60; }
.ocupacion-media { color: #F2994A; }
.ocupacion-alta  { color: #EB5757; }

/* ── Métricas del grupo ── */
.grupo-metricas {
  display: flex;
  gap: 1rem;
}
.metrica {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.72rem;
  font-weight: 700;
  color: #333;
}
.metrica-label {
  font-size: 0.58rem;
  font-weight: 600;
  color: #BDBDBD;
  letter-spacing: 0.04em;
}

/* ── Empty state ── */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  gap: 1rem;
  color: #BDBDBD;
  font-size: 0.8rem;
  font-weight: 600;
  letter-spacing: 0.05em;
}

/* ── Loading ── */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  gap: 1rem;
  color: #828282;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.05em;
}
.spinner {
  width: 40px; height: 40px;
  border: 3px solid #E0E0E0;
  border-top-color: #1D52B7;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Responsive ── */
@media (max-width: 768px) {
  .grupos-se { padding: 1rem; }
  .cards-grid, .grupos-grid { grid-template-columns: 1fr; }
  .nivel-header { flex-direction: column; align-items: flex-start; }
  .semestre-resumen { display: none; }
}
</style>
