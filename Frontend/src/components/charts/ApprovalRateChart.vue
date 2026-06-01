<!-- src/components/charts/ApprovalRateChart.vue -->
<!-- SICE — Equipo 4 — Sprint 2: Gráfico de aprobación/reprobación por parciales -->
<template>
  <div class="arc-root">

    <!-- ── Encabezado de la card ── -->
    <div class="arc-header">
      <div class="arc-header-left">
        <div class="arc-icon-wrap">
          <TrendingUp :size="18" />
        </div>
        <div>
          <h2 class="arc-titulo">PORCENTAJE DE APROBACION POR PARCIALES</h2>
          <p class="arc-subtitulo">{{ subtituloFiltro }}</p>
        </div>
      </div>

      <!-- Badge: promedio global -->
      <div class="arc-badge-global" :class="badgeClase(promedioGlobal)">
        <span class="arc-badge-numero">{{ promedioGlobal }}%</span>
        <span class="arc-badge-label">PROMEDIO</span>
      </div>
    </div>

    <!-- ── Filtros ── -->
    <div class="arc-filtros">
      <div class="arc-filtro-wrap">
        <Search :size="14" class="arc-filtro-icono" />
        <input
          v-model="busqueda"
          type="text"
          placeholder="BUSCAR MATERIA..."
          class="arc-input"
          @input="store.setFiltro('materia', busqueda)"
        />
        <button v-if="busqueda" @click="limpiarBusqueda" class="arc-btn-limpiar">
          <X :size="12" />
        </button>
      </div>

      <select v-model="semestreLocal" class="arc-select" @change="store.setFiltro('semestre', semestreLocal)">
        <option value="">TODOS LOS SEMESTRES</option>
        <option v-for="s in store.semestresDisponibles" :key="s" :value="s">
          {{ s }}° SEMESTRE
        </option>
      </select>

      <button class="arc-btn-refrescar" @click="store.cargarAprobacion" :disabled="store.cargandoAprobacion" title="RECARGAR DATOS">
        <RefreshCw :size="14" :class="{ 'arc-spin': store.cargandoAprobacion }" />
      </button>
    </div>

    <!-- ── Estado: cargando ── -->
    <div v-if="store.cargandoAprobacion" class="arc-estado">
      <Loader2 :size="28" class="arc-spin arc-loader-icono" />
      <span>CARGANDO DATOS...</span>
    </div>

    <!-- ── Estado: sin datos ── -->
    <div v-else-if="!grupos.length" class="arc-estado">
      <BarChart3 :size="36" class="arc-vacio-icono" />
      <span>SIN DATOS DISPONIBLES</span>
      <p class="arc-vacio-hint">AJUSTA LOS FILTROS O VERIFICA LA CONEXION</p>
    </div>

    <!-- ── Contenido principal ── -->
    <div v-else class="arc-contenido">

      <!-- Selector de grupo activo (tabs scrollables) -->
      <div class="arc-tabs-wrap">
        <div class="arc-tabs">
          <button
            v-for="(g, i) in grupos"
            :key="g.id_grupo"
            class="arc-tab"
            :class="{ activo: grupoActivoIdx === i }"
            @click="grupoActivoIdx = i"
          >
            <BookOpen :size="12" />
            <span class="arc-tab-texto">{{ abreviar(g.materia) }}</span>
          </button>
        </div>
      </div>

      <!-- Panel del grupo activo -->
      <div v-if="grupoActivo" class="arc-panel">

        <!-- Info del grupo -->
        <div class="arc-grupo-info">
          <div class="arc-info-item">
            <GraduationCap :size="14" class="arc-info-icono" />
            <span>{{ grupoActivo.materia }}</span>
          </div>
          <div class="arc-info-item">
            <User :size="14" class="arc-info-icono" />
            <span>{{ grupoActivo.docente }}</span>
          </div>
          <div class="arc-info-item">
            <Layers :size="14" class="arc-info-icono" />
            <span>{{ grupoActivo.semestre }}° SEMESTRE</span>
          </div>
          <div class="arc-info-item">
            <Building2 :size="14" class="arc-info-icono" />
            <span>{{ grupoActivo.carrera }}</span>
          </div>
        </div>

        <!-- Gráfico de barras agrupadas por parcial -->
        <div class="arc-grafico-area">

          <!-- Eje Y (etiquetas de porcentaje) -->
          <div class="arc-eje-y">
            <span v-for="tick in yTicks" :key="tick" class="arc-eje-y-tick">{{ tick }}%</span>
          </div>

          <!-- Barras -->
          <div class="arc-barras-zona">
            <!-- Líneas de cuadrícula -->
            <div
              v-for="tick in yTicks"
              :key="'grid-'+tick"
              class="arc-gridline"
              :style="{ bottom: tick + '%' }"
            ></div>

            <!-- Línea de referencia 70% -->
            <div class="arc-ref-line" :style="{ bottom: '70%' }">
              <span class="arc-ref-label">META 70%</span>
            </div>

            <!-- Grupo por parcial -->
            <div
              v-for="parcial in grupoActivo.parciales"
              :key="parcial.numero"
              class="arc-parcial-grupo"
            >
              <!-- Barra: aprobados -->
              <div class="arc-barra-wrap" role="group" :aria-label="`Parcial ${parcial.numero}`">
                <div
                  class="arc-barra arc-barra-aprobado"
                  :style="{ height: parcial.porcentaje_aprobacion + '%' }"
                  :title="`APROBADOS: ${parcial.aprobados} (${parcial.porcentaje_aprobacion.toFixed(1)}%)`"
                >
                  <span v-if="parcial.porcentaje_aprobacion > 15" class="arc-barra-val">
                    {{ Math.round(parcial.porcentaje_aprobacion) }}%
                  </span>
                </div>

                <!-- Barra: reprobados (invertida, hacia arriba desde el fondo) -->
                <div
                  class="arc-barra arc-barra-reprobado"
                  :style="{ height: porcentajeReprobado(parcial) + '%' }"
                  :title="`REPROBADOS: ${parcial.reprobados} (${porcentajeReprobado(parcial).toFixed(1)}%)`"
                >
                  <span v-if="porcentajeReprobado(parcial) > 15" class="arc-barra-val arc-barra-val-rep">
                    {{ Math.round(porcentajeReprobado(parcial)) }}%
                  </span>
                </div>
              </div>

              <!-- Etiqueta de parcial -->
              <span class="arc-parcial-label">PARCIAL {{ parcial.numero }}</span>

              <!-- Tooltip -->
              <div class="arc-tooltip">
                <p class="arc-tooltip-titulo">PARCIAL {{ parcial.numero }}</p>
                <div class="arc-tooltip-fila">
                  <span class="arc-dot arc-dot-verde"></span>
                  <span>APROBADOS</span>
                  <strong>{{ parcial.aprobados }} ({{ parcial.porcentaje_aprobacion.toFixed(1) }}%)</strong>
                </div>
                <div class="arc-tooltip-fila">
                  <span class="arc-dot arc-dot-rojo"></span>
                  <span>REPROBADOS</span>
                  <strong>{{ parcial.reprobados }} ({{ porcentajeReprobado(parcial).toFixed(1) }}%)</strong>
                </div>
                <div class="arc-tooltip-fila arc-tooltip-total">
                  <span>TOTAL</span>
                  <strong>{{ parcial.total }}</strong>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- Leyenda -->
        <div class="arc-leyenda">
          <div class="arc-leyenda-item">
            <span class="arc-leyenda-punto arc-punto-verde"></span>
            APROBADOS
          </div>
          <div class="arc-leyenda-item">
            <span class="arc-leyenda-punto arc-punto-rojo"></span>
            REPROBADOS
          </div>
          <div class="arc-leyenda-item arc-leyenda-meta">
            <span class="arc-leyenda-linea"></span>
            META: 70%
          </div>
        </div>

        <!-- Resumen numérico de los 3 parciales -->
        <div class="arc-resumen">
          <div
            v-for="parcial in grupoActivo.parciales"
            :key="'res-'+parcial.numero"
            class="arc-resumen-card"
            :class="resumenClase(parcial.porcentaje_aprobacion)"
          >
            <div class="arc-resumen-header">
              <span class="arc-resumen-label">PARCIAL {{ parcial.numero }}</span>
              <component :is="iconoTendencia(parcial.numero)" :size="14" class="arc-resumen-trend" />
            </div>
            <div class="arc-resumen-numero">{{ parcial.porcentaje_aprobacion.toFixed(1) }}%</div>
            <div class="arc-resumen-detalle">
              <span class="arc-verde">↑ {{ parcial.aprobados }} APR</span>
              <span class="arc-rojo">↓ {{ parcial.reprobados }} REP</span>
            </div>
          </div>
        </div>

      </div><!-- /arc-panel -->
    </div><!-- /arc-contenido -->

    <!-- Indicador de datos de demostración -->
    <div v-if="store.usandoFallback" class="arc-fallback-banner">
      <AlertCircle :size="13" />
      DATOS DE DEMOSTRACION — CONECTA EL BACKEND PARA VER DATOS REALES
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import {
  TrendingUp, Search, X, RefreshCw, Loader2, BarChart3,
  BookOpen, GraduationCap, User, Layers, Building2,
  TrendingDown, Minus, AlertCircle,
} from 'lucide-vue-next'
import { useAnalyticsStore } from '@/store/analyticsStore'

// ── Store ───────────────────────────────────────────────────────────────────
const store = useAnalyticsStore()

// ── Estado local ─────────────────────────────────────────────────────────────
const grupoActivoIdx = ref(0)
const busqueda       = ref('')
const semestreLocal  = ref('')

// ── Computed ─────────────────────────────────────────────────────────────────
const grupos = computed(() => store.aprobacionFiltrada)

const grupoActivo = computed(() => grupos.value[grupoActivoIdx.value] ?? null)

const promedioGlobal = computed(() => store.promedioAprobacionGlobal)

const subtituloFiltro = computed(() => {
  const partes = []
  if (store.filtros.semestre) partes.push(`${store.filtros.semestre}° SEM`)
  if (store.filtros.materia)  partes.push(store.filtros.materia.toUpperCase())
  return partes.length ? partes.join(' · ') : `${grupos.value.length} GRUPOS CARGADOS`
})

const yTicks = [0, 25, 50, 75, 100]

// ── Métodos ──────────────────────────────────────────────────────────────────

const porcentajeReprobado = (parcial) =>
  Math.round(((parcial.reprobados / parcial.total) * 100) * 10) / 10

const abreviar = (texto) => {
  if (texto.length <= 18) return texto
  return texto.slice(0, 16) + '…'
}

const badgeClase = (pct) => {
  if (pct >= 75) return 'arc-badge-verde'
  if (pct >= 60) return 'arc-badge-amarillo'
  return 'arc-badge-rojo'
}

const resumenClase = (pct) => {
  if (pct >= 75) return 'arc-resumen-verde'
  if (pct >= 60) return 'arc-resumen-amarillo'
  return 'arc-resumen-rojo'
}

const iconoTendencia = (numeroParcial) => {
  if (!grupoActivo.value) return Minus
  const parciales = grupoActivo.value.parciales
  if (numeroParcial === 1) return Minus
  const actual   = parciales[numeroParcial - 1]?.porcentaje_aprobacion ?? 0
  const anterior = parciales[numeroParcial - 2]?.porcentaje_aprobacion ?? 0
  if (actual > anterior) return TrendingUp
  if (actual < anterior) return TrendingDown
  return Minus
}

const limpiarBusqueda = () => {
  busqueda.value = ''
  store.setFiltro('materia', '')
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
  if (!store.aprobacionData.length) store.cargarAprobacion()
})
</script>

<style scoped>
/* ══════════════════════════════════════════════════════
   VARIABLES (consistentes con DashboardView / GestionGruposView)
══════════════════════════════════════════════════════ */
.arc-root {
  --azul:       #1B396A;
  --azul-hover: #15305A;
  --azul-claro: #DBEAFE;
  --verde:      #16A34A;
  --verde-bg:   #DCFCE7;
  --verde-borde:#86EFAC;
  --rojo:       #DC2626;
  --rojo-bg:    #FEF2F2;
  --rojo-borde: #FECACA;
  --amarillo:   #D97706;
  --amarillo-bg:#FEF3C7;
  --borde:      #E5E7EB;
  --fondo:      #F9FAFB;
  --texto:      #111827;
  --gris:       #6B7280;
  --radio:      12px;

  font-family: 'Montserrat', sans-serif;
  background: #FFFFFF;
  border-radius: var(--radio);
  border: 1px solid var(--borde);
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  padding: 1.4rem 1.6rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* ── Encabezado ── */
.arc-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}
.arc-header-left {
  display: flex;
  align-items: center;
  gap: 10px;
}
.arc-icon-wrap {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: var(--azul-claro);
  color: var(--azul);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.arc-titulo {
  font-size: 0.92rem;
  font-weight: 700;
  color: var(--texto);
  margin: 0 0 2px;
  line-height: 1.2;
}
.arc-subtitulo {
  font-size: 0.76rem;
  color: var(--gris);
  margin: 0;
  font-weight: 500;
}

/* Badge promedio global */
.arc-badge-global {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 8px 16px;
  border-radius: 10px;
  min-width: 70px;
  flex-shrink: 0;
}
.arc-badge-verde   { background: var(--verde-bg);   border: 1px solid var(--verde-borde); }
.arc-badge-amarillo{ background: var(--amarillo-bg); border: 1px solid #FDE68A; }
.arc-badge-rojo    { background: var(--rojo-bg);    border: 1px solid var(--rojo-borde); }
.arc-badge-numero  { font-size: 1.5rem; font-weight: 800; line-height: 1; }
.arc-badge-verde   .arc-badge-numero { color: var(--verde); }
.arc-badge-amarillo .arc-badge-numero { color: var(--amarillo); }
.arc-badge-rojo    .arc-badge-numero { color: var(--rojo); }
.arc-badge-label   { font-size: 0.68rem; font-weight: 700; color: var(--gris); margin-top: 2px; }

/* ── Filtros ── */
.arc-filtros {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.arc-filtro-wrap {
  display: flex;
  align-items: center;
  gap: 6px;
  background: var(--fondo);
  border: 1px solid var(--borde);
  border-radius: 8px;
  padding: 0 10px;
  flex: 1;
  min-width: 160px;
}
.arc-filtro-icono { color: var(--gris); flex-shrink: 0; }
.arc-input {
  border: none;
  background: transparent;
  padding: 8px 0;
  font-size: 0.8rem;
  font-family: inherit;
  font-weight: 600;
  outline: none;
  flex: 1;
  color: var(--texto);
}
.arc-input::placeholder { color: #9CA3AF; font-weight: 400; }
.arc-btn-limpiar {
  background: none;
  border: none;
  color: var(--gris);
  cursor: pointer;
  padding: 2px;
  display: flex;
  align-items: center;
}
.arc-select {
  padding: 8px 10px;
  border: 1px solid var(--borde);
  border-radius: 8px;
  font-size: 0.78rem;
  font-family: inherit;
  font-weight: 600;
  color: var(--texto);
  background: var(--fondo);
  outline: none;
  cursor: pointer;
}
.arc-select:focus { border-color: var(--azul); }
.arc-btn-refrescar {
  width: 34px;
  height: 34px;
  border: 1px solid var(--borde);
  border-radius: 8px;
  background: var(--fondo);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--gris);
  transition: background 0.15s;
  flex-shrink: 0;
}
.arc-btn-refrescar:hover:not(:disabled) { background: var(--azul-claro); color: var(--azul); }
.arc-btn-refrescar:disabled { opacity: 0.5; cursor: not-allowed; }

/* ── Estados (cargando / vacío) ── */
.arc-estado {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 3rem 1rem;
  color: var(--gris);
  font-size: 0.85rem;
  font-weight: 600;
}
.arc-loader-icono { color: var(--azul); }
.arc-vacio-icono  { color: #D1D5DB; }
.arc-vacio-hint   { font-size: 0.76rem; font-weight: 400; margin: 0; color: #9CA3AF; }
.arc-spin { animation: arc-girar 0.8s linear infinite; }
@keyframes arc-girar { to { transform: rotate(360deg); } }

/* ── Tabs de grupos ── */
.arc-tabs-wrap {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
  margin: 0 -1.6rem;
  padding: 0 1.6rem;
}
.arc-tabs-wrap::-webkit-scrollbar { display: none; }
.arc-tabs {
  display: flex;
  gap: 4px;
  border-bottom: 1px solid var(--borde);
  padding-bottom: 0;
  width: max-content;
  min-width: 100%;
}
.arc-tab {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 8px 14px;
  border: none;
  background: transparent;
  font-size: 0.75rem;
  font-weight: 600;
  font-family: inherit;
  color: var(--gris);
  cursor: pointer;
  border-radius: 8px 8px 0 0;
  white-space: nowrap;
  transition: background 0.15s, color 0.15s;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
}
.arc-tab:hover { background: var(--fondo); color: var(--texto); }
.arc-tab.activo {
  color: var(--azul);
  border-bottom-color: var(--azul);
  background: var(--azul-claro);
}
.arc-tab-texto { max-width: 120px; overflow: hidden; text-overflow: ellipsis; }

/* ── Panel del grupo activo ── */
.arc-panel { display: flex; flex-direction: column; gap: 1rem; }

.arc-grupo-info {
  display: flex;
  flex-wrap: wrap;
  gap: 8px 18px;
}
.arc-info-item {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--texto);
}
.arc-info-icono { color: var(--gris); flex-shrink: 0; }

/* ── Gráfico ── */
.arc-grafico-area {
  display: flex;
  align-items: flex-end;
  gap: 10px;
  height: 200px;
  padding-bottom: 26px; /* espacio para etiquetas de parciales */
  position: relative;
}

.arc-eje-y {
  display: flex;
  flex-direction: column-reverse;
  justify-content: space-between;
  height: 100%;
  width: 32px;
  flex-shrink: 0;
}
.arc-eje-y-tick {
  font-size: 0.68rem;
  font-weight: 600;
  color: #9CA3AF;
  text-align: right;
  line-height: 1;
}

.arc-barras-zona {
  flex: 1;
  height: 100%;
  position: relative;
  display: flex;
  align-items: flex-end;
  gap: 0;
}

/* Líneas de cuadrícula */
.arc-gridline {
  position: absolute;
  left: 0;
  right: 0;
  height: 1px;
  background: #F3F4F6;
  pointer-events: none;
}

/* Línea de referencia 70% */
.arc-ref-line {
  position: absolute;
  left: 0;
  right: 0;
  height: 1px;
  background: rgba(217,119,6,0.45);
  border-top: 1.5px dashed #F59E0B;
  pointer-events: none;
  z-index: 2;
}
.arc-ref-label {
  position: absolute;
  right: 0;
  top: -16px;
  font-size: 0.65rem;
  font-weight: 700;
  color: #D97706;
  background: #FFFBEB;
  padding: 1px 5px;
  border-radius: 4px;
  white-space: nowrap;
}

/* Grupo de barras por parcial */
.arc-parcial-grupo {
  flex: 1;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  gap: 3px;
  position: relative;
  padding-bottom: 22px;
}

/* Tooltip */
.arc-tooltip {
  display: none;
  position: absolute;
  bottom: calc(100% + 8px);
  left: 50%;
  transform: translateX(-50%);
  background: #1F2937;
  color: white;
  padding: 10px 12px;
  border-radius: 8px;
  font-size: 0.75rem;
  min-width: 170px;
  z-index: 100;
  box-shadow: 0 8px 20px rgba(0,0,0,0.2);
  pointer-events: none;
}
.arc-parcial-grupo:hover .arc-tooltip { display: flex; flex-direction: column; gap: 5px; }
.arc-tooltip::after {
  content: '';
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  border: 5px solid transparent;
  border-top-color: #1F2937;
}
.arc-tooltip-titulo { font-weight: 700; font-size: 0.78rem; margin: 0 0 4px; border-bottom: 1px solid rgba(255,255,255,0.15); padding-bottom: 4px; }
.arc-tooltip-fila { display: flex; align-items: center; gap: 6px; }
.arc-tooltip-fila strong { margin-left: auto; font-size: 0.8rem; }
.arc-tooltip-total { border-top: 1px solid rgba(255,255,255,0.15); margin-top: 3px; padding-top: 3px; font-weight: 700; }
.arc-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.arc-dot-verde { background: #22C55E; }
.arc-dot-rojo  { background: #EF4444; }

/* Contenedor de barras aprobado + reprobado */
.arc-barra-wrap {
  display: flex;
  align-items: flex-end;
  gap: 3px;
  height: calc(100% - 22px);
  width: 68%;
}

.arc-barra {
  flex: 1;
  border-radius: 4px 4px 0 0;
  position: relative;
  transition: height 0.5s cubic-bezier(0.34,1.56,0.64,1);
  min-height: 2px;
  display: flex;
  align-items: flex-start;
  justify-content: center;
}
.arc-barra-aprobado { background: linear-gradient(180deg, #22C55E 0%, #16A34A 100%); }
.arc-barra-reprobado { background: linear-gradient(180deg, #F87171 0%, #DC2626 100%); }
.arc-barra-val {
  font-size: 0.62rem;
  font-weight: 700;
  color: white;
  padding-top: 3px;
  line-height: 1;
  text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}
.arc-barra-val-rep { color: white; }

/* Etiqueta de parcial bajo las barras */
.arc-parcial-label {
  position: absolute;
  bottom: 0;
  font-size: 0.68rem;
  font-weight: 700;
  color: var(--gris);
  white-space: nowrap;
}

/* ── Leyenda ── */
.arc-leyenda {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}
.arc-leyenda-item {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.76rem;
  font-weight: 600;
  color: var(--gris);
}
.arc-leyenda-punto { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
.arc-punto-verde  { background: #16A34A; }
.arc-punto-rojo   { background: #DC2626; }
.arc-leyenda-meta { color: #D97706; }
.arc-leyenda-linea {
  width: 18px;
  height: 2px;
  border-top: 2px dashed #F59E0B;
  flex-shrink: 0;
}

/* ── Resumen numérico ── */
.arc-resumen {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
}
.arc-resumen-card {
  border-radius: 10px;
  padding: 10px 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.arc-resumen-verde   { background: var(--verde-bg);   border: 1px solid var(--verde-borde); }
.arc-resumen-amarillo{ background: var(--amarillo-bg); border: 1px solid #FDE68A; }
.arc-resumen-rojo    { background: var(--rojo-bg);    border: 1px solid var(--rojo-borde); }

.arc-resumen-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.arc-resumen-label {
  font-size: 0.7rem;
  font-weight: 700;
  color: var(--gris);
}
.arc-resumen-trend { color: var(--gris); }
.arc-resumen-verde   .arc-resumen-trend { color: var(--verde); }
.arc-resumen-rojo    .arc-resumen-trend { color: var(--rojo); }
.arc-resumen-amarillo .arc-resumen-trend { color: var(--amarillo); }
.arc-resumen-numero {
  font-size: 1.35rem;
  font-weight: 800;
  line-height: 1.1;
}
.arc-resumen-verde   .arc-resumen-numero { color: var(--verde); }
.arc-resumen-amarillo .arc-resumen-numero { color: var(--amarillo); }
.arc-resumen-rojo    .arc-resumen-numero { color: var(--rojo); }
.arc-resumen-detalle {
  display: flex;
  gap: 8px;
  font-size: 0.72rem;
  font-weight: 600;
}
.arc-verde { color: var(--verde); }
.arc-rojo  { color: var(--rojo); }

/* ── Banner fallback ── */
.arc-fallback-banner {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.72rem;
  font-weight: 600;
  color: #D97706;
  background: #FFFBEB;
  border: 1px solid #FDE68A;
  border-radius: 8px;
  padding: 6px 12px;
}

/* ── Responsive ── */
@media (max-width: 640px) {
  .arc-root { padding: 1rem 1.1rem; }
  .arc-grafico-area { height: 160px; }
  .arc-resumen { grid-template-columns: 1fr 1fr; }
  .arc-tabs { gap: 2px; }
  .arc-tab { padding: 6px 10px; font-size: 0.7rem; }
  .arc-titulo { font-size: 0.82rem; }
}
@media (max-width: 400px) {
  .arc-resumen { grid-template-columns: 1fr; }
  .arc-header { flex-direction: column; }
}
</style>