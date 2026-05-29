<template>
  <div class="ccp-wrapper">

    <!-- ══ BARRA DE CARGA ══ -->
    <div class="ccp-barra-carga" :class="{ activa: cargando }" aria-hidden="true">
      <div class="ccp-barra-progreso"></div>
    </div>

    <!-- ══ ENCABEZADO ══ -->
    <div class="ccp-header">
      <div class="ccp-header-izq">
        <div class="ccp-header-icono" aria-hidden="true">
          <BarChart2 :size="20" />
        </div>
        <div>
          <h2 class="ccp-titulo">RENDIMIENTO DE TRONCO COMUN</h2>
          <p class="ccp-subtitulo">MATERIAS COMPARTIDAS POR CARRERA · PERIODO {{ periodoActual }}</p>
        </div>
      </div>

      <div class="ccp-header-controles">
        <!-- Selector de carrera -->
        <div class="ccp-select-wrap">
          <BookOpen :size="14" class="ccp-select-icono" aria-hidden="true" />
          <select
            v-model="carreraSeleccionada"
            class="ccp-select"
            aria-label="Seleccionar carrera"
            @change="aplicarCarrera"
          >
            <option value="">TODAS LAS CARRERAS</option>
            <option v-for="c in carreras" :key="c.id" :value="c.id">
              {{ c.nombre }}
            </option>
          </select>
          <ChevronDown :size="13" class="ccp-chevron" aria-hidden="true" />
        </div>

        <!-- Selector de periodo -->
        <div class="ccp-select-wrap">
          <CalendarDays :size="14" class="ccp-select-icono" aria-hidden="true" />
          <select
            v-model="periodoSeleccionado"
            class="ccp-select"
            aria-label="Seleccionar periodo"
            @change="cargarDatos"
          >
            <option v-for="p in periodos" :key="p.valor" :value="p.valor">
              {{ p.etiqueta }}
            </option>
          </select>
          <ChevronDown :size="13" class="ccp-chevron" aria-hidden="true" />
        </div>

        <!-- Botón refrescar -->
        <button
          class="ccp-btn-refrescar"
          @click="cargarDatos"
          :disabled="cargando"
          aria-label="Actualizar datos"
          type="button"
        >
          <RefreshCw :size="15" :class="{ 'ccp-spinning': cargando }" />
        </button>
      </div>
    </div>

    <!-- ══ ALERTA DE ERROR ══ -->
    <Transition name="ccp-fade">
      <div v-if="error" class="ccp-alerta" role="alert">
        <AlertTriangle :size="16" aria-hidden="true" />
        {{ error }}
      </div>
    </Transition>

    <!-- ══ KPI CARDS ══ -->
    <div class="ccp-kpis">
      <div class="ccp-kpi-card ccp-kpi--total">
        <div class="ccp-kpi-icono-wrap ccp-kpi-icono--azul">
          <Users :size="18" aria-hidden="true" />
        </div>
        <div class="ccp-kpi-datos">
          <span class="ccp-kpi-valor">{{ totalAlumnos.toLocaleString('es-MX') }}</span>
          <span class="ccp-kpi-etiqueta">TOTAL ALUMNOS</span>
        </div>
      </div>

      <div class="ccp-kpi-card ccp-kpi--aprobados">
        <div class="ccp-kpi-icono-wrap ccp-kpi-icono--verde">
          <CheckCircle :size="18" aria-hidden="true" />
        </div>
        <div class="ccp-kpi-datos">
          <span class="ccp-kpi-valor">{{ porcentajeAprobacion }}%</span>
          <span class="ccp-kpi-etiqueta">APROBACION GENERAL</span>
        </div>
        <div class="ccp-kpi-tendencia ccp-tendencia--positiva">
          <TrendingUp :size="12" aria-hidden="true" />
          <span>+2.3%</span>
        </div>
      </div>

      <div class="ccp-kpi-card ccp-kpi--reprobados">
        <div class="ccp-kpi-icono-wrap ccp-kpi-icono--rojo">
          <XCircle :size="18" aria-hidden="true" />
        </div>
        <div class="ccp-kpi-datos">
          <span class="ccp-kpi-valor">{{ porcentajeReprobacion }}%</span>
          <span class="ccp-kpi-etiqueta">REPROBACION GENERAL</span>
        </div>
        <div class="ccp-kpi-tendencia ccp-tendencia--negativa">
          <TrendingDown :size="12" aria-hidden="true" />
          <span>-0.8%</span>
        </div>
      </div>

      <div class="ccp-kpi-card ccp-kpi--promedio">
        <div class="ccp-kpi-icono-wrap ccp-kpi-icono--amarillo">
          <Award :size="18" aria-hidden="true" />
        </div>
        <div class="ccp-kpi-datos">
          <span class="ccp-kpi-valor">{{ promedioGeneral }}</span>
          <span class="ccp-kpi-etiqueta">PROMEDIO GENERAL</span>
        </div>
      </div>
    </div>

    <!-- ══ GRAFICA PRINCIPAL + PANEL LATERAL ══ -->
    <div class="ccp-contenido-principal">

      <!-- Gráfica de barras apiladas -->
      <div class="ccp-card ccp-card--grafica">
        <div class="ccp-card-header">
          <h3 class="ccp-card-titulo">APROBACION POR MATERIA</h3>
          <!-- Leyenda -->
          <div class="ccp-leyenda">
            <span class="ccp-leyenda-item">
              <span class="ccp-leyenda-color ccp-leyenda-color--verde"></span>
              APROBADO
            </span>
            <span class="ccp-leyenda-item">
              <span class="ccp-leyenda-color ccp-leyenda-color--rojo"></span>
              REPROBADO
            </span>
            <span class="ccp-leyenda-item">
              <span class="ccp-leyenda-color ccp-leyenda-color--gris"></span>
              BAJA
            </span>
          </div>
        </div>

        <!-- Estado: cargando -->
        <div v-if="cargando" class="ccp-estado-carga" aria-busy="true">
          <div class="ccp-spinner" aria-hidden="true"></div>
          <span>CARGANDO DATOS...</span>
        </div>

        <!-- Barras apiladas -->
        <div v-else class="ccp-barras-container" role="list" aria-label="Rendimiento por materia">
          <div
            v-for="(materia, i) in materiasFiltradas"
            :key="materia.id"
            class="ccp-barra-fila"
            role="listitem"
          >
            <!-- Etiqueta -->
            <div class="ccp-barra-nombre" :title="materia.nombre">
              <span class="ccp-barra-num">{{ String(i + 1).padStart(2, '0') }}</span>
              {{ materia.nombre }}
            </div>

            <!-- Barra apilada -->
            <div class="ccp-barra-apilada-wrap">
              <div
                class="ccp-barra-apilada"
                role="group"
                :aria-label="`${materia.nombre}: ${materia.pAprobados}% aprobados, ${materia.pReprobados}% reprobados, ${materia.pBaja}% baja`"
              >
                <div
                  class="ccp-segmento ccp-segmento--verde"
                  :style="{ width: materia.pAprobados + '%' }"
                  :title="`Aprobados: ${materia.pAprobados}%`"
                ></div>
                <div
                  class="ccp-segmento ccp-segmento--rojo"
                  :style="{ width: materia.pReprobados + '%' }"
                  :title="`Reprobados: ${materia.pReprobados}%`"
                ></div>
                <div
                  class="ccp-segmento ccp-segmento--gris"
                  :style="{ width: materia.pBaja + '%' }"
                  :title="`Baja: ${materia.pBaja}%`"
                ></div>
              </div>

              <!-- Tooltip de valores -->
              <div class="ccp-barra-valores">
                <span class="ccp-val-verde">{{ materia.pAprobados }}%</span>
                <span class="ccp-val-rojo">{{ materia.pReprobados }}%</span>
                <span class="ccp-val-gris">{{ materia.pBaja }}%</span>
              </div>
            </div>

            <!-- Promedio badge -->
            <div
              class="ccp-promedio-badge"
              :class="claseBadgePromedio(materia.promedio)"
              :title="`Promedio: ${materia.promedio}`"
            >
              {{ materia.promedio }}
            </div>
          </div>
        </div>
      </div>

      <!-- Panel lateral: top críticas + top destacadas -->
      <div class="ccp-panel-lateral">

        <!-- Materias críticas -->
        <div class="ccp-card ccp-card--criticas">
          <div class="ccp-card-header">
            <h3 class="ccp-card-titulo ccp-titulo--rojo">
              <AlertTriangle :size="14" aria-hidden="true" />
              MATERIAS CRITICAS
            </h3>
          </div>
          <div class="ccp-lista-lateral" role="list" aria-label="Materias con mayor reprobación">
            <div
              v-for="(m, i) in materiasCriticas"
              :key="m.id"
              class="ccp-item-lateral ccp-item-lateral--critico"
              role="listitem"
            >
              <div class="ccp-item-rank ccp-item-rank--rojo">{{ i + 1 }}</div>
              <div class="ccp-item-info">
                <span class="ccp-item-nombre">{{ m.nombre }}</span>
                <div class="ccp-item-mini-barra">
                  <div
                    class="ccp-mini-relleno ccp-mini-relleno--rojo"
                    :style="{ width: m.pReprobados + '%' }"
                  ></div>
                </div>
              </div>
              <div class="ccp-item-pct ccp-item-pct--rojo">{{ m.pReprobados }}%</div>
            </div>
          </div>
        </div>

        <!-- Materias destacadas -->
        <div class="ccp-card ccp-card--destacadas">
          <div class="ccp-card-header">
            <h3 class="ccp-card-titulo ccp-titulo--verde">
              <TrendingUp :size="14" aria-hidden="true" />
              MATERIAS DESTACADAS
            </h3>
          </div>
          <div class="ccp-lista-lateral" role="list" aria-label="Materias con mayor aprobación">
            <div
              v-for="(m, i) in materiasDestacadas"
              :key="m.id"
              class="ccp-item-lateral ccp-item-lateral--destacado"
              role="listitem"
            >
              <div class="ccp-item-rank ccp-item-rank--verde">{{ i + 1 }}</div>
              <div class="ccp-item-info">
                <span class="ccp-item-nombre">{{ m.nombre }}</span>
                <div class="ccp-item-mini-barra">
                  <div
                    class="ccp-mini-relleno ccp-mini-relleno--verde"
                    :style="{ width: m.pAprobados + '%' }"
                  ></div>
                </div>
              </div>
              <div class="ccp-item-pct ccp-item-pct--verde">{{ m.pAprobados }}%</div>
            </div>
          </div>
        </div>

      </div><!-- /panel-lateral -->
    </div><!-- /contenido-principal -->

    <!-- ══ TABLA RESUMEN ══ -->
    <div class="ccp-card ccp-card--tabla">
      <div class="ccp-card-header ccp-card-header--tabla">
        <h3 class="ccp-card-titulo">
          <FileText :size="14" aria-hidden="true" />
          RESUMEN POR CARRERA
        </h3>
        <span class="ccp-badge-total">
          {{ materiasFiltradas.length }} MATERIAS
        </span>
      </div>

      <div class="ccp-tabla-wrap">
        <table class="ccp-tabla" aria-label="Resumen de rendimiento por carrera">
          <thead>
            <tr>
              <th scope="col">MATERIA</th>
              <th scope="col" class="ccp-th-center">ALUMNOS</th>
              <th scope="col" class="ccp-th-center">APROBADOS</th>
              <th scope="col" class="ccp-th-center">REPROBADOS</th>
              <th scope="col" class="ccp-th-center">BAJA</th>
              <th scope="col" class="ccp-th-center">PROMEDIO</th>
              <th scope="col" class="ccp-th-center">ESTATUS</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="materia in materiasFiltradas"
              :key="materia.id"
              class="ccp-tabla-fila"
            >
              <td class="ccp-td-materia">
                <span class="ccp-td-nombre">{{ materia.nombre }}</span>
                <span class="ccp-td-clave">{{ materia.clave }}</span>
              </td>
              <td class="ccp-td-center">{{ materia.totalAlumnos }}</td>
              <td class="ccp-td-center ccp-td--verde">{{ materia.aprobados }}</td>
              <td class="ccp-td-center ccp-td--rojo">{{ materia.reprobados }}</td>
              <td class="ccp-td-center ccp-td--gris">{{ materia.baja }}</td>
              <td class="ccp-td-center">
                <span class="ccp-promedio-chip" :class="claseBadgePromedio(materia.promedio)">
                  {{ materia.promedio }}
                </span>
              </td>
              <td class="ccp-td-center">
                <span class="ccp-estatus-chip" :class="claseEstatus(materia.pReprobados)">
                  <component :is="iconoEstatus(materia.pReprobados)" :size="11" aria-hidden="true" />
                  {{ textoEstatus(materia.pReprobados) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import {
  BarChart2,
  BookOpen,
  CalendarDays,
  RefreshCw,
  AlertTriangle,
  CheckCircle,
  XCircle,
  TrendingUp,
  TrendingDown,
  Award,
  Users,
  FileText
} from 'lucide-vue-next'

// ── Props opcionales ─────────────────────────────────────────────────────────
const props = defineProps({
  apiUrl: {
    type: String,
    default: () => import.meta.env.VITE_API_URL || ''
  }
})

// ── Estado ───────────────────────────────────────────────────────────────────
const cargando             = ref(false)
const error                = ref(null)
const carreraSeleccionada  = ref('')
const periodoSeleccionado  = ref('2024-2')

// ── Catálogos ────────────────────────────────────────────────────────────────
const carreras = ref([
  { id: 1, nombre: 'SISTEMAS COMPUTACIONALES' },
  { id: 2, nombre: 'INDUSTRIAL'               },
  { id: 3, nombre: 'ELECTRICA'                },
  { id: 4, nombre: 'MECANICA'                 },
  { id: 5, nombre: 'GESTION EMPRESARIAL'      },
  { id: 6, nombre: 'BIOQUIMICA'               }
])

const periodos = ref([
  { valor: '2024-2', etiqueta: 'AGO-DIC 2024' },
  { valor: '2025-1', etiqueta: 'ENE-JUN 2025' },
  { valor: '2025-2', etiqueta: 'AGO-DIC 2025' }
])

// ── Datos (fallback estático mientras no conecta backend) ────────────────────
const materiasDefault = [
  {
    id: 1,  clave: 'AEF-1031', nombre: 'CALCULO DIFERENCIAL',
    totalAlumnos: 892, aprobados: 623, reprobados: 214, baja: 55,
    pAprobados: 70, pReprobados: 24, pBaja: 6, promedio: 7.2
  },
  {
    id: 2,  clave: 'AEF-1041', nombre: 'ALGEBRA LINEAL',
    totalAlumnos: 886, aprobados: 531, reprobados: 301, baja: 54,
    pAprobados: 60, pReprobados: 34, pBaja: 6, promedio: 6.8
  },
  {
    id: 3,  clave: 'AEF-1051', nombre: 'FUNDAMENTOS DE INVESTIGACION',
    totalAlumnos: 890, aprobados: 749, reprobados: 98,  baja: 43,
    pAprobados: 84, pReprobados: 11, pBaja: 5, promedio: 8.1
  },
  {
    id: 4,  clave: 'AEF-1061', nombre: 'CALCULO INTEGRAL',
    totalAlumnos: 855, aprobados: 538, reprobados: 268, baja: 49,
    pAprobados: 63, pReprobados: 31, pBaja: 6, promedio: 6.9
  },
  {
    id: 5,  clave: 'AEF-1071', nombre: 'QUIMICA',
    totalAlumnos: 841, aprobados: 672, reprobados: 126, baja: 43,
    pAprobados: 80, pReprobados: 15, pBaja: 5, promedio: 7.8
  },
  {
    id: 6,  clave: 'AEF-1081', nombre: 'FISICA',
    totalAlumnos: 877, aprobados: 614, reprobados: 219, baja: 44,
    pAprobados: 70, pReprobados: 25, pBaja: 5, promedio: 7.1
  },
  {
    id: 7,  clave: 'AEF-1091', nombre: 'TALLER DE ETICA',
    totalAlumnos: 892, aprobados: 830, reprobados: 40,  baja: 22,
    pAprobados: 93, pReprobados:  4, pBaja: 3, promedio: 8.9
  },
  {
    id: 8,  clave: 'AEF-1101', nombre: 'CALCULO VECTORIAL',
    totalAlumnos: 798, aprobados: 439, reprobados: 311, baja: 48,
    pAprobados: 55, pReprobados: 39, pBaja: 6, promedio: 6.5
  },
  {
    id: 9,  clave: 'AEF-1111', nombre: 'PROBABILIDAD Y ESTADISTICA',
    totalAlumnos: 812, aprobados: 601, reprobados: 171, baja: 40,
    pAprobados: 74, pReprobados: 21, pBaja: 5, promedio: 7.5
  },
  {
    id: 10, clave: 'AEF-1121', nombre: 'INGLES I',
    totalAlumnos: 892, aprobados: 758, reprobados: 107, baja: 27,
    pAprobados: 85, pReprobados: 12, pBaja: 3, promedio: 8.3
  }
]

const materias = ref([...materiasDefault])

// ── Computed ─────────────────────────────────────────────────────────────────
const periodoActual = computed(() => {
  const p = periodos.value.find(p => p.valor === periodoSeleccionado.value)
  return p ? p.etiqueta : ''
})

const materiasFiltradas = computed(() => {
  // Si hay carrera seleccionada, en producción se filtraría por backend.
  // Aquí se usa el dataset local completo o simulado por carrera.
  return materias.value
})

const totalAlumnos = computed(() =>
  materiasFiltradas.value.reduce((s, m) => s + m.totalAlumnos, 0) / materiasFiltradas.value.length
    > Math.round
  // fallback si no está disponible el operador pipe:
)

// Reescritura compatible:
const _total = computed(() => {
  if (!materiasFiltradas.value.length) return 0
  return Math.round(materiasFiltradas.value.reduce((s, m) => s + m.totalAlumnos, 0) / materiasFiltradas.value.length)
})

const porcentajeAprobacion = computed(() => {
  if (!materiasFiltradas.value.length) return 0
  const suma = materiasFiltradas.value.reduce((s, m) => s + m.pAprobados, 0)
  return Math.round(suma / materiasFiltradas.value.length)
})

const porcentajeReprobacion = computed(() => {
  if (!materiasFiltradas.value.length) return 0
  const suma = materiasFiltradas.value.reduce((s, m) => s + m.pReprobados, 0)
  return Math.round(suma / materiasFiltradas.value.length)
})

const promedioGeneral = computed(() => {
  if (!materiasFiltradas.value.length) return '0.0'
  const suma = materiasFiltradas.value.reduce((s, m) => s + m.promedio, 0)
  return (suma / materiasFiltradas.value.length).toFixed(1)
})

const materiasCriticas = computed(() =>
  [...materiasFiltradas.value]
    .sort((a, b) => b.pReprobados - a.pReprobados)
    .slice(0, 4)
)

const materiasDestacadas = computed(() =>
  [...materiasFiltradas.value]
    .sort((a, b) => b.pAprobados - a.pAprobados)
    .slice(0, 4)
)

// ── Helpers de estilos ───────────────────────────────────────────────────────
const claseBadgePromedio = (prom) => {
  if (prom >= 8.0) return 'badge--verde'
  if (prom >= 7.0) return 'badge--amarillo'
  return 'badge--rojo'
}

const claseEstatus = (pRep) => {
  if (pRep >= 30) return 'estatus--critico'
  if (pRep >= 20) return 'estatus--alerta'
  return 'estatus--normal'
}

const textoEstatus = (pRep) => {
  if (pRep >= 30) return 'CRITICO'
  if (pRep >= 20) return 'ALERTA'
  return 'NORMAL'
}

const iconoEstatus = (pRep) => {
  if (pRep >= 30) return XCircle
  if (pRep >= 20) return AlertTriangle
  return CheckCircle
}

// ── Carga de datos ───────────────────────────────────────────────────────────
const cargarDatos = async () => {
  if (!props.apiUrl) return
  cargando.value = true
  error.value    = null
  try {
    const params = new URLSearchParams({
      periodo:  periodoSeleccionado.value,
      carrera:  carreraSeleccionada.value || ''
    })
    const res = await fetch(`${props.apiUrl}/api/dashboard/tronco-comun?${params}`)
    if (!res.ok) throw new Error(`Error ${res.status}`)
    const data = await res.json()
    if (Array.isArray(data.materias)) {
      materias.value = data.materias
    }
  } catch (err) {
    console.warn('[CommonCorePerformance] Backend no disponible, usando datos demo.', err)
    materias.value = [...materiasDefault]
  } finally {
    cargando.value = false
  }
}

const aplicarCarrera = () => {
  cargarDatos()
}

// ── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(() => {
  cargarDatos()
})
</script>

<style scoped>
/* ══════════════════════════════════════════════════════════════════
   PALETA SICE — Consistente con DashboardView y GestionGruposView
══════════════════════════════════════════════════════════════════ */
.ccp-wrapper {
  --azul:        #1B396A;
  --azul-claro:  #DBEAFE;
  --verde:       #16A34A;
  --verde-claro: #DCFCE7;
  --rojo:        #DC2626;
  --rojo-claro:  #FEE2E2;
  --amarillo:    #F59E0B;
  --amr-claro:   #FEF3C7;
  --gris:        #6B7280;
  --gris-claro:  #F1F5F9;
  --borde:       #E5E7EB;
  --texto:       #111827;
  --radio:       12px;

  font-family: 'Montserrat', sans-serif;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* ── Barra de carga ── */
.ccp-barra-carga {
  position: fixed; top: 0; left: 0; right: 0;
  height: 3px; z-index: 9999;
  opacity: 0; transition: opacity 0.2s; pointer-events: none;
}
.ccp-barra-carga.activa { opacity: 1; }
.ccp-barra-progreso {
  height: 100%; background: var(--azul);
  animation: ccp-progreso 1.5s ease-in-out infinite;
}
@keyframes ccp-progreso {
  0%   { width: 0%;   opacity: 1; }
  70%  { width: 85%;  opacity: 1; }
  100% { width: 100%; opacity: 0; }
}

/* ── Encabezado ── */
.ccp-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}
.ccp-header-izq {
  display: flex;
  align-items: center;
  gap: 12px;
}
.ccp-header-icono {
  width: 40px; height: 40px;
  background: var(--azul);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  color: #fff;
  flex-shrink: 0;
}
.ccp-titulo {
  font-size: 1.05rem; font-weight: 700;
  color: var(--texto); margin: 0 0 2px;
  letter-spacing: 0.01em;
}
.ccp-subtitulo {
  font-size: 0.75rem; color: var(--gris);
  margin: 0; font-weight: 500;
}

.ccp-header-controles {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

/* ── Select ── */
.ccp-select-wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.ccp-select-icono {
  position: absolute;
  left: 10px;
  color: var(--gris);
  pointer-events: none;
  flex-shrink: 0;
}
.ccp-chevron {
  position: absolute;
  right: 10px;
  color: var(--gris);
  pointer-events: none;
}
.ccp-select {
  padding: 8px 32px 8px 30px;
  border: 1.5px solid var(--borde);
  border-radius: 8px;
  font-size: 0.78rem;
  font-weight: 600;
  font-family: inherit;
  color: var(--texto);
  background: #fff;
  appearance: none;
  cursor: pointer;
  outline: none;
  transition: border-color 0.15s;
  white-space: nowrap;
}
.ccp-select:focus { border-color: var(--azul); }

/* ── Botón refrescar ── */
.ccp-btn-refrescar {
  width: 36px; height: 36px;
  display: flex; align-items: center; justify-content: center;
  border: 1.5px solid var(--borde);
  border-radius: 8px;
  background: #fff;
  color: var(--gris);
  cursor: pointer;
  transition: background 0.15s, color 0.15s, border-color 0.15s;
  flex-shrink: 0;
}
.ccp-btn-refrescar:hover:not(:disabled) {
  background: var(--azul-claro);
  border-color: var(--azul);
  color: var(--azul);
}
.ccp-btn-refrescar:disabled { opacity: 0.5; cursor: not-allowed; }
.ccp-spinning { animation: ccp-girar 0.8s linear infinite; }
@keyframes ccp-girar { to { transform: rotate(360deg); } }

/* ── Alerta ── */
.ccp-alerta {
  display: flex; align-items: center; gap: 8px;
  background: var(--rojo-claro);
  border: 1px solid #FECACA;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 0.82rem;
  font-weight: 500;
  color: var(--rojo);
}

/* ── KPIs ── */
.ccp-kpis {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.75rem;
}
.ccp-kpi-card {
  background: #fff;
  border: 1px solid var(--borde);
  border-radius: var(--radio);
  padding: 1rem 1.2rem;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  position: relative;
  overflow: hidden;
}
.ccp-kpi-card::before {
  content: '';
  position: absolute;
  left: 0; top: 0; bottom: 0;
  width: 4px;
  border-radius: 0 2px 2px 0;
}
.ccp-kpi--total::before   { background: var(--azul); }
.ccp-kpi--aprobados::before  { background: var(--verde); }
.ccp-kpi--reprobados::before { background: var(--rojo); }
.ccp-kpi--promedio::before   { background: var(--amarillo); }

.ccp-kpi-icono-wrap {
  width: 38px; height: 38px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.ccp-kpi-icono--azul    { background: var(--azul-claro);  color: var(--azul);    }
.ccp-kpi-icono--verde   { background: var(--verde-claro); color: var(--verde);   }
.ccp-kpi-icono--rojo    { background: var(--rojo-claro);  color: var(--rojo);    }
.ccp-kpi-icono--amarillo { background: var(--amr-claro);  color: var(--amarillo); }

.ccp-kpi-datos {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-width: 0;
}
.ccp-kpi-valor {
  font-size: 1.4rem; font-weight: 700;
  color: var(--texto); line-height: 1.1;
}
.ccp-kpi-etiqueta {
  font-size: 0.68rem; font-weight: 600;
  color: var(--gris); margin-top: 2px;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.ccp-kpi-tendencia {
  position: absolute; top: 10px; right: 10px;
  display: flex; align-items: center; gap: 3px;
  font-size: 0.68rem; font-weight: 700;
  padding: 2px 7px; border-radius: 20px;
}
.ccp-tendencia--positiva { background: var(--verde-claro); color: var(--verde); }
.ccp-tendencia--negativa { background: var(--rojo-claro);  color: var(--rojo);  }

/* ══ CONTENIDO PRINCIPAL ══ */
.ccp-contenido-principal {
  display: grid;
  grid-template-columns: 1fr 280px;
  gap: 1rem;
  align-items: start;
}

/* ── Card genérica ── */
.ccp-card {
  background: #fff;
  border: 1px solid var(--borde);
  border-radius: var(--radio);
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  overflow: hidden;
}
.ccp-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 1rem 1.4rem 0.9rem;
  border-bottom: 1px solid var(--borde);
  flex-wrap: wrap;
}
.ccp-card-titulo {
  font-size: 0.82rem; font-weight: 700;
  color: var(--texto); margin: 0;
  display: flex; align-items: center; gap: 6px;
  text-transform: uppercase;
}
.ccp-titulo--rojo  { color: var(--rojo);  }
.ccp-titulo--verde { color: var(--verde); }

/* ── Leyenda ── */
.ccp-leyenda {
  display: flex; align-items: center; gap: 14px;
}
.ccp-leyenda-item {
  display: flex; align-items: center; gap: 5px;
  font-size: 0.72rem; font-weight: 600; color: var(--gris);
}
.ccp-leyenda-color {
  width: 10px; height: 10px; border-radius: 2px; flex-shrink: 0;
}
.ccp-leyenda-color--verde { background: var(--verde); }
.ccp-leyenda-color--rojo  { background: var(--rojo);  }
.ccp-leyenda-color--gris  { background: #D1D5DB;      }

/* ── Estado cargando ── */
.ccp-estado-carga {
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  gap: 10px; padding: 3rem;
  font-size: 0.8rem; font-weight: 600; color: var(--gris);
}
.ccp-spinner {
  width: 24px; height: 24px;
  border: 2.5px solid var(--borde);
  border-top-color: var(--azul);
  border-radius: 50%;
  animation: ccp-girar 0.75s linear infinite;
}

/* ── Barras apiladas ── */
.ccp-barras-container {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 1rem 1.4rem 1.2rem;
}
.ccp-barra-fila {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 4px 0;
}
.ccp-barra-nombre {
  width: 190px;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--texto);
  display: flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex-shrink: 0;
}
.ccp-barra-num {
  font-size: 0.65rem;
  font-weight: 700;
  color: #9CA3AF;
  background: var(--gris-claro);
  padding: 1px 5px;
  border-radius: 4px;
  flex-shrink: 0;
}

.ccp-barra-apilada-wrap {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 10px;
}
.ccp-barra-apilada {
  flex: 1;
  height: 20px;
  border-radius: 5px;
  overflow: hidden;
  background: #F3F4F6;
  display: flex;
}
.ccp-segmento {
  height: 100%;
  transition: width 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
}
.ccp-segmento--verde { background: var(--verde); }
.ccp-segmento--rojo  { background: var(--rojo);  }
.ccp-segmento--gris  { background: #D1D5DB;      }

.ccp-barra-valores {
  display: flex; gap: 6px;
  font-size: 0.68rem; font-weight: 700;
  flex-shrink: 0; white-space: nowrap;
}
.ccp-val-verde { color: var(--verde); }
.ccp-val-rojo  { color: var(--rojo);  }
.ccp-val-gris  { color: #9CA3AF;      }

/* ── Badge promedio en barra ── */
.ccp-promedio-badge {
  width: 34px; height: 22px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 6px;
  font-size: 0.72rem; font-weight: 700;
  flex-shrink: 0;
}
.badge--verde   { background: var(--verde-claro); color: var(--verde); }
.badge--amarillo { background: var(--amr-claro);  color: #B45309;     }
.badge--rojo    { background: var(--rojo-claro);  color: var(--rojo); }

/* ── Panel lateral ── */
.ccp-panel-lateral {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.ccp-card--criticas,
.ccp-card--destacadas { border-radius: var(--radio); }

.ccp-lista-lateral {
  display: flex; flex-direction: column;
  gap: 2px;
  padding: 0.7rem 1rem 0.9rem;
}
.ccp-item-lateral {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 8px;
  border-radius: 8px;
  transition: background 0.15s;
}
.ccp-item-lateral--critico:hover { background: var(--rojo-claro); }
.ccp-item-lateral--destacado:hover { background: var(--verde-claro); }

.ccp-item-rank {
  width: 20px; height: 20px;
  border-radius: 50%;
  font-size: 0.7rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.ccp-item-rank--rojo  { background: var(--rojo-claro);  color: var(--rojo);  }
.ccp-item-rank--verde { background: var(--verde-claro); color: var(--verde); }

.ccp-item-info {
  flex: 1; min-width: 0;
  display: flex; flex-direction: column; gap: 3px;
}
.ccp-item-nombre {
  font-size: 0.72rem; font-weight: 600;
  color: var(--texto);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.ccp-item-mini-barra {
  height: 4px;
  background: #F3F4F6;
  border-radius: 2px;
  overflow: hidden;
}
.ccp-mini-relleno {
  height: 100%;
  border-radius: 2px;
  transition: width 0.5s ease;
}
.ccp-mini-relleno--rojo  { background: var(--rojo);  }
.ccp-mini-relleno--verde { background: var(--verde); }

.ccp-item-pct {
  font-size: 0.78rem; font-weight: 700; flex-shrink: 0;
}
.ccp-item-pct--rojo  { color: var(--rojo);  }
.ccp-item-pct--verde { color: var(--verde); }

/* ── Tabla resumen ── */
.ccp-card--tabla { margin-top: 0; }
.ccp-card-header--tabla { padding: 0.9rem 1.4rem; }
.ccp-badge-total {
  font-size: 0.72rem; font-weight: 700;
  background: var(--azul-claro);
  color: var(--azul);
  padding: 3px 10px;
  border-radius: 20px;
}

.ccp-tabla-wrap { overflow-x: auto; }
.ccp-tabla {
  width: 100%;
  border-collapse: collapse;
  font-family: inherit;
}
.ccp-tabla thead th {
  background: #F9FAFB;
  padding: 8px 14px;
  text-align: left;
  font-size: 0.72rem; font-weight: 700;
  color: var(--gris);
  border-bottom: 1.5px solid var(--borde);
  white-space: nowrap;
  letter-spacing: 0.02em;
}
.ccp-th-center { text-align: center; }
.ccp-tabla-fila td {
  padding: 9px 14px;
  border-bottom: 1px solid var(--borde);
  font-size: 0.82rem; color: var(--texto);
  vertical-align: middle;
}
.ccp-tabla-fila:last-child td { border-bottom: none; }
.ccp-tabla-fila:hover td { background: #F9FAFB; }

.ccp-td-materia { min-width: 200px; }
.ccp-td-nombre {
  display: block;
  font-weight: 600; font-size: 0.8rem; color: var(--texto);
}
.ccp-td-clave {
  display: block;
  font-size: 0.68rem; color: var(--gris); margin-top: 1px;
}
.ccp-td-center { text-align: center; font-weight: 500; }
.ccp-td--verde { color: var(--verde); font-weight: 700; }
.ccp-td--rojo  { color: var(--rojo);  font-weight: 700; }
.ccp-td--gris  { color: var(--gris);  }

.ccp-promedio-chip {
  display: inline-flex; align-items: center; justify-content: center;
  padding: 3px 10px; border-radius: 20px;
  font-size: 0.75rem; font-weight: 700;
}

.ccp-estatus-chip {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 3px 9px; border-radius: 20px;
  font-size: 0.68rem; font-weight: 700; white-space: nowrap;
}
.estatus--critico { background: var(--rojo-claro);  color: var(--rojo);    }
.estatus--alerta  { background: var(--amr-claro);   color: #B45309;        }
.estatus--normal  { background: var(--verde-claro); color: var(--verde);   }

/* ── Transición ── */
.ccp-fade-enter-active, .ccp-fade-leave-active { transition: opacity 0.25s; }
.ccp-fade-enter-from, .ccp-fade-leave-to { opacity: 0; }

/* ══ RESPONSIVE ══ */

/* Tablet grande (≤1200px) */
@media (max-width: 1200px) {
  .ccp-kpis { grid-template-columns: repeat(2, 1fr); }
}

/* Tablet (≤900px) */
@media (max-width: 900px) {
  .ccp-contenido-principal {
    grid-template-columns: 1fr;
  }
  .ccp-panel-lateral {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
  }
  .ccp-barra-nombre { width: 140px; }
}

/* Móvil grande (≤640px) */
@media (max-width: 640px) {
  .ccp-header         { flex-direction: column; align-items: flex-start; }
  .ccp-kpis           { grid-template-columns: 1fr 1fr; }
  .ccp-panel-lateral  { grid-template-columns: 1fr; }
  .ccp-barra-nombre   { width: 110px; font-size: 0.68rem; }
  .ccp-barra-valores  { display: none; }
  .ccp-select         { font-size: 0.72rem; }
  .ccp-header-controles { width: 100%; }
}

/* Móvil pequeño (≤480px) */
@media (max-width: 480px) {
  .ccp-kpis           { grid-template-columns: 1fr; }
  .ccp-kpi-tendencia  { display: none; }
  .ccp-barra-nombre   { width: 90px; }
  .ccp-barra-fila     { gap: 6px; }
}
</style>