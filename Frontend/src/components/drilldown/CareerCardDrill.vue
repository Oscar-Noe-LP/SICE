<!-- src/components/drilldown/CareerCardDrill.vue -->
<template>
  <article
    class="career-card"
    :class="[`career-card--${colorKey}`, { 'career-card--active': active }]"
    :style="cardStyle"
    role="button"
    tabindex="0"
    :aria-label="`VER DETALLE DE ${careerNameUpper}`"
    @click="$emit('click', career)"
    @keydown.enter="$emit('click', career)"
    @keydown.space.prevent="$emit('click', career)"
  >
    <!-- Fondo decorativo -->
    <div class="career-card__bg-glow" aria-hidden="true" />
    <div class="career-card__bg-grid" aria-hidden="true" />

    <!-- Encabezado: icono + nombre -->
    <header class="career-card__header">
      <div class="career-card__icon-wrap" :style="iconWrapStyle">
        <component :is="resolvedIcon" class="career-card__icon" :style="iconStyle" />
      </div>

      <div class="career-card__title-area">
        <p class="career-card__label">CARRERA</p>
        <h3 class="career-card__name">{{ careerNameUpper }}</h3>
        <p class="career-card__abbr" v-if="career.abreviacion">
          {{ career.abreviacion.toUpperCase() }}
        </p>
      </div>

      <!-- Indicador de flecha -->
      <div class="career-card__arrow" :style="arrowStyle">
        <ChevronRight class="career-card__arrow-icon" />
      </div>
    </header>

    <!-- Separador -->
    <div class="career-card__divider" :style="dividerStyle" />

    <!-- Stats principales -->
    <div class="career-card__stats">
      <!-- Grupos -->
      <div class="career-card__stat">
        <div class="career-card__stat-icon-wrap" :style="statIconWrapStyle">
          <BookOpen class="career-card__stat-icon" :style="statIconStyle" />
        </div>
        <div class="career-card__stat-info">
          <span class="career-card__stat-value">{{ formatNumber(career.stats?.grupos ?? 0) }}</span>
          <span class="career-card__stat-label">GRUPOS</span>
        </div>
      </div>

      <!-- Total matrículas -->
      <div class="career-card__stat">
        <div class="career-card__stat-icon-wrap" :style="statIconWrapStyle">
          <Users class="career-card__stat-icon" :style="statIconStyle" />
        </div>
        <div class="career-card__stat-info">
          <span class="career-card__stat-value">{{ formatNumber(career.stats?.totalAlumnos ?? 0) }}</span>
          <span class="career-card__stat-label">MATRICULAS</span>
        </div>
      </div>
    </div>

    <!-- Barra de regulares / irregulares -->
    <div class="career-card__regularity">
      <div class="career-card__reg-labels">
        <span class="career-card__reg-tag career-card__reg-tag--regular">
          <CheckCircle2 class="career-card__reg-icon" />
          {{ formatNumber(career.stats?.regulares ?? 0) }} REGULARES
        </span>
        <span class="career-card__reg-tag career-card__reg-tag--irregular">
          <AlertTriangle class="career-card__reg-icon" />
          {{ formatNumber(career.stats?.irregulares ?? 0) }} IRREGULARES
        </span>
      </div>

      <!-- Barra de progreso -->
      <div class="career-card__bar-track" role="progressbar"
        :aria-valuenow="regularPct"
        :aria-valuemin="0"
        :aria-valuemax="100"
        :aria-label="`${regularPct}% ALUMNOS REGULARES`"
      >
        <div
          class="career-card__bar-fill career-card__bar-fill--regular"
          :style="{ width: regularPct + '%' }"
        />
        <div
          class="career-card__bar-fill career-card__bar-fill--irregular"
          :style="{ width: irregularPct + '%' }"
        />
      </div>

      <div class="career-card__reg-pcts">
        <span class="career-card__pct career-card__pct--regular">{{ regularPct }}%</span>
        <span class="career-card__pct career-card__pct--irregular">{{ irregularPct }}%</span>
      </div>
    </div>

    <!-- Badge de periodo -->
    <div v-if="career.stats?.periodo" class="career-card__period">
      <CalendarDays class="career-card__period-icon" />
      <span>{{ career.stats.periodo.toUpperCase() }}</span>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue'
import {
  BookOpen,
  Users,
  CheckCircle2,
  AlertTriangle,
  ChevronRight,
  CalendarDays,
  Monitor,
  Factory,
  Zap,
  Settings,
  TrendingUp,
  FlaskConical,
  GraduationCap,
  Building2,
} from 'lucide-vue-next'

// PROPS
const props = defineProps({
  career: {
    type: Object,
    required: true,
  },
  active: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['click'])

// MAPAS DE ICONOS Y COLORES
const ICON_MAP = {
  monitor:      Monitor,
  factory:      Factory,
  zap:          Zap,
  settings:     Settings,
  trending:     TrendingUp,
  flask:        FlaskConical,
  graduation:   GraduationCap,
  building:     Building2,
}

const COLOR_MAP = {
  blue: {
    accent:     '#1B396A',
    accentMid:  '#1D4ED8',
    iconBg:     'rgba(27,57,106,0.12)',
    iconColor:  '#1B396A',
    gradient:   'linear-gradient(145deg, #FFFFFF 0%, #EFF6FF 100%)',
    glow:       'rgba(29,78,216,0.08)',
    divider:    '#BFDBFE',
    arrow:      '#1B396A',
  },
  green: {
    accent:     '#166534',
    accentMid:  '#16A34A',
    iconBg:     'rgba(22,163,74,0.10)',
    iconColor:  '#16A34A',
    gradient:   'linear-gradient(145deg, #FFFFFF 0%, #F0FDF4 100%)',
    glow:       'rgba(22,163,74,0.08)',
    divider:    '#BBF7D0',
    arrow:      '#166534',
  },
  orange: {
    accent:     '#9A3412',
    accentMid:  '#EA580C',
    iconBg:     'rgba(234,88,12,0.10)',
    iconColor:  '#EA580C',
    gradient:   'linear-gradient(145deg, #FFFFFF 0%, #FFF7ED 100%)',
    glow:       'rgba(234,88,12,0.08)',
    divider:    '#FED7AA',
    arrow:      '#9A3412',
  },
  purple: {
    accent:     '#581C87',
    accentMid:  '#9333EA',
    iconBg:     'rgba(147,51,234,0.10)',
    iconColor:  '#9333EA',
    gradient:   'linear-gradient(145deg, #FFFFFF 0%, #FAF5FF 100%)',
    glow:       'rgba(147,51,234,0.08)',
    divider:    '#E9D5FF',
    arrow:      '#581C87',
  },
  red: {
    accent:     '#991B1B',
    accentMid:  '#DC2626',
    iconBg:     'rgba(220,38,38,0.10)',
    iconColor:  '#DC2626',
    gradient:   'linear-gradient(145deg, #FFFFFF 0%, #FFF1F2 100%)',
    glow:       'rgba(220,38,38,0.08)',
    divider:    '#FECDD3',
    arrow:      '#991B1B',
  },
  teal: {
    accent:     '#134E4A',
    accentMid:  '#0D9488',
    iconBg:     'rgba(13,148,136,0.10)',
    iconColor:  '#0D9488',
    gradient:   'linear-gradient(145deg, #FFFFFF 0%, #F0FDFA 100%)',
    glow:       'rgba(13,148,136,0.08)',
    divider:    '#99F6E4',
    arrow:      '#134E4A',
  },
  slate: {
    accent:     '#1E293B',
    accentMid:  '#475569',
    iconBg:     'rgba(71,85,105,0.10)',
    iconColor:  '#475569',
    gradient:   'linear-gradient(145deg, #FFFFFF 0%, #F8FAFC 100%)',
    glow:       'rgba(71,85,105,0.08)',
    divider:    '#CBD5E1',
    arrow:      '#1E293B',
  },
}

// COMPUTED
const colorKey = computed(() =>
  COLOR_MAP[props.career.colorKey] ? props.career.colorKey : 'blue'
)

const palette = computed(() => COLOR_MAP[colorKey.value])

const resolvedIcon = computed(() =>
  ICON_MAP[props.career.icono] ?? GraduationCap
)

const careerNameUpper = computed(() => {
  const map = { á:'A', é:'E', í:'I', ó:'O', ú:'U', Á:'A', É:'E', Í:'I', Ó:'O', Ú:'U' }
  return props.career.nombre
    .replace(/[áéíóúÁÉÍÓÚ]/g, c => map[c] ?? c)
    .toUpperCase()
})

const totalAlumnos = computed(() => props.career.stats?.totalAlumnos ?? 0)

const regularPct = computed(() => {
  if (!totalAlumnos.value) return 0
  return Math.round(((props.career.stats?.regulares ?? 0) / totalAlumnos.value) * 100)
})

const irregularPct = computed(() => 100 - regularPct.value)

// Estilos dinámicos
const cardStyle = computed(() => ({
  background: palette.value.gradient,
  '--card-accent':    palette.value.accent,
  '--card-mid':       palette.value.accentMid,
  '--card-glow':      palette.value.glow,
  '--card-divider':   palette.value.divider,
}))

const iconWrapStyle = computed(() => ({
  background: palette.value.iconBg,
  border: `1.5px solid ${palette.value.divider}`,
}))

const iconStyle = computed(() => ({
  color: palette.value.iconColor,
}))

const statIconWrapStyle = computed(() => ({
  background: palette.value.iconBg,
}))

const statIconStyle = computed(() => ({
  color: palette.value.iconColor,
}))

const dividerStyle = computed(() => ({
  background: palette.value.divider,
}))

const arrowStyle = computed(() => ({
  color: palette.value.arrow,
  background: palette.value.iconBg,
}))

// UTILS
const formatNumber = (n) =>
  typeof n === 'number' ? n.toLocaleString('es-MX') : '0'
</script>

<style scoped>
/* ═══════════════════════════════════════════════
   CAREER CARD DRILL - SICE
   Design System: Montserrat, colores institucionales
═══════════════════════════════════════════════ */

.career-card {
  position: relative;
  border-radius: 16px;
  border: 1.5px solid #E5E7EB;
  padding: 1.2rem 1.2rem 1rem;
  cursor: pointer;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1),
              box-shadow 0.2s ease,
              border-color 0.2s ease;
  outline: none;
  font-family: 'Montserrat', sans-serif;
  user-select: none;
}

/* Hover / Focus */
.career-card:hover,
.career-card:focus-visible {
  transform: translateY(-3px) scale(1.01);
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.1);
  border-color: var(--card-mid);
}

.career-card:active {
  transform: translateY(-1px) scale(1.005);
}

.career-card--active {
  border-color: var(--card-accent) !important;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1), 0 0 0 3px var(--card-glow) !important;
}

.career-card:focus-visible {
  outline: 3px solid var(--card-mid);
  outline-offset: 2px;
}

/* Fondos decorativos */
.career-card__bg-glow {
  position: absolute;
  inset: -60px -60px auto auto;
  width: 160px;
  height: 160px;
  border-radius: 50%;
  background: var(--card-glow);
  filter: blur(40px);
  pointer-events: none;
  z-index: 0;
}

.career-card__bg-grid {
  position: absolute;
  inset: 0;
  background-image: linear-gradient(var(--card-divider) 1px, transparent 1px),
                    linear-gradient(90deg, var(--card-divider) 1px, transparent 1px);
  background-size: 24px 24px;
  opacity: 0.2;
  pointer-events: none;
  z-index: 0;
}

.career-card > *:not(.career-card__bg-glow):not(.career-card__bg-grid) {
  position: relative;
  z-index: 1;
}

/* Encabezado */
.career-card__header {
  display: flex;
  align-items: flex-start;
  gap: 0.8rem;
  margin-bottom: 0.8rem;
}

.career-card__icon-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  flex-shrink: 0;
  transition: transform 0.2s ease;
}

.career-card:hover .career-card__icon-wrap {
  transform: scale(1.06) rotate(-2deg);
}

.career-card__icon {
  width: 24px;
  height: 24px;
  stroke-width: 1.75;
}

.career-card__title-area {
  flex: 1;
  min-width: 0;
}

.career-card__label {
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  color: #9CA3AF;
  margin: 0 0 2px;
}

.career-card__name {
  font-size: 0.92rem;
  font-weight: 800;
  color: #111827;
  margin: 0 0 2px;
  line-height: 1.25;
  max-height: calc(1.25em * 2); /* 2 líneas */
  overflow: hidden;
  word-break: break-word;
}

.career-card__abbr {
  font-size: 0.68rem;
  font-weight: 700;
  color: var(--card-mid);
  margin: 0;
  letter-spacing: 0.06em;
}

/* Flecha */
.career-card__arrow {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 8px;
  flex-shrink: 0;
  transition: transform 0.2s ease;
}

.career-card:hover .career-card__arrow {
  transform: translateX(3px);
}

.career-card__arrow-icon {
  width: 14px;
  height: 14px;
  stroke-width: 2.5;
}

/* Separador */
.career-card__divider {
  height: 1px;
  margin-bottom: 0.8rem;
  opacity: 0.5;
}

/* Stats */
.career-card__stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.6rem;
  margin-bottom: 0.8rem;
}

.career-card__stat {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.7);
  border: 1px solid rgba(229, 231, 235, 0.8);
  border-radius: 10px;
  padding: 0.5rem 0.6rem;
}

.career-card__stat-icon-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 8px;
  flex-shrink: 0;
}

.career-card__stat-icon {
  width: 14px;
  height: 14px;
  stroke-width: 2;
}

.career-card__stat-info {
  display: flex;
  flex-direction: column;
}

.career-card__stat-value {
  font-size: 1rem;
  font-weight: 800;
  color: #111827;
  line-height: 1.1;
}

.career-card__stat-label {
  font-size: 0.58rem;
  font-weight: 700;
  color: #9CA3AF;
  letter-spacing: 0.07em;
}

/* Regularidad */
.career-card__regularity {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.career-card__reg-labels {
  display: flex;
  justify-content: space-between;
  gap: 0.3rem;
}

.career-card__reg-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  padding: 2px 8px;
  border-radius: 20px;
}

.career-card__reg-tag--regular {
  background: #DCFCE7;
  color: #16A34A;
}

.career-card__reg-tag--irregular {
  background: #FEF2F2;
  color: #DC2626;
}

.career-card__reg-icon {
  width: 10px;
  height: 10px;
  stroke-width: 2.5;
}

/* Barra de progreso */
.career-card__bar-track {
  display: flex;
  height: 6px;
  border-radius: 99px;
  overflow: hidden;
  background: #F3F4F6;
  gap: 1px;
}

.career-card__bar-fill {
  height: 100%;
  transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.career-card__bar-fill--regular {
  background: linear-gradient(90deg, #16A34A, #22C55E);
  border-radius: 99px 0 0 99px;
}

.career-card__bar-fill--irregular {
  background: linear-gradient(90deg, #EF4444, #DC2626);
  border-radius: 0 99px 99px 0;
}

.career-card__reg-pcts {
  display: flex;
  justify-content: space-between;
}

.career-card__pct {
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.04em;
}

.career-card__pct--regular   { color: #16A34A; }
.career-card__pct--irregular { color: #DC2626; }

/* Badge periodo */
.career-card__period {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  margin-top: 0.6rem;
  background: rgba(255, 255, 255, 0.8);
  border: 1px solid var(--card-divider);
  border-radius: 6px;
  padding: 2px 8px;
  font-size: 0.65rem;
  font-weight: 700;
  color: #6B7280;
  letter-spacing: 0.05em;
  width: fit-content;
}

.career-card__period-icon {
  width: 10px;
  height: 10px;
  stroke-width: 2;
  color: var(--card-mid);
}

/* RESPONSIVE */
@media (max-width: 640px) {
  .career-card {
    padding: 1rem 1rem 0.9rem;
  }
  .career-card__icon-wrap {
    width: 42px;
    height: 42px;
  }
  .career-card__icon {
    width: 20px;
    height: 20px;
  }
  .career-card__name {
    font-size: 0.82rem;
  }
  .career-card__stat-value {
    font-size: 0.9rem;
  }
}
</style>