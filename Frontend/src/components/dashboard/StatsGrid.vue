<template>
  <div class="stats-grid">
    <div
      v-for="stat in stats"
      :key="stat.key"
      class="sg-card"
      :class="{ 'sg-card--featured': stat.featured }"
      @click="stat.ruta ? $emit('navegar', stat.ruta) : null"
      :role="stat.ruta ? 'button' : 'region'"
      :tabindex="stat.ruta ? 0 : -1"
      :aria-label="stat.label"
      @keydown.enter="stat.ruta ? $emit('navegar', stat.ruta) : null"
    >
      <!-- Icono -->
      <div
        class="sg-ico"
        :class="!stat.featured ? stat.icoClass : ''"
        :style="stat.featured ? { background: 'rgba(255,255,255,0.15)', color: '#FFFFFF' } : {}"
        aria-hidden="true"
      >
        <component :is="stat.icono" :size="22" />
      </div>

      <!-- Cuerpo -->
      <div class="sg-body">
        <p class="sg-lbl">{{ stat.label }}</p>

        <!-- Skeleton -->
        <div
          v-if="cargando"
          class="sg-skeleton"
          :class="{ 'sg-skeleton--dark': stat.featured }"
        ></div>
        <p v-else class="sg-val">{{ formatNum(stat.valor) }}</p>

        <span
          v-if="stat.ruta"
          class="sg-link"
          :style="stat.featured ? { color: 'rgba(255,255,255,0.7)' } : {}"
        >
          {{ stat.linkLabel }} →
        </span>
      </div>

      <!-- Tendencia -->
      <div
        v-if="stat.cambio"
        class="sg-cambio"
        :class="stat.cambio.clase"
        :style="stat.featured ? { color: 'rgba(255,255,255,0.6)' } : {}"
      >
        <TrendingUp  v-if="stat.cambio.tipo === 'up'" :size="11" />
        <TrendingDown v-else-if="stat.cambio.tipo === 'dn'" :size="11" />
        <Minus        v-else :size="11" />
        {{ stat.cambio.texto }}
      </div>

    </div>
  </div>
</template>

<script setup>
import { TrendingUp, TrendingDown, Minus } from 'lucide-vue-next'
import { formatNum } from '@/stores/dashboardStore.js'

defineProps({
  stats:    { type: Array,   required: true },
  cargando: { type: Boolean, default: false },
})

defineEmits(['navegar'])
</script>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
}

/* ── Card base ── */
.sg-card {
  background: #FFFFFF;
  border: 1px solid #E0E0E0;
  border-radius: 12px;
  padding: 16px;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  box-shadow: 0 2px 8px rgba(29,82,183,0.05);
  transition: all 0.15s ease;
  position: relative;
  overflow: hidden;
}

.sg-card[tabindex="0"] { cursor: pointer; }

.sg-card[tabindex="0"]:hover {
  border-color: #2F80ED;
  box-shadow: 0 0 0 3px rgba(29,82,183,0.07);
  transform: translateY(-2px);
}

/* Card destacada (primera) */
.sg-card--featured {
  background: #0B2545;
  border-color: #0B2545;
}

.sg-card--featured::after {
  content: '';
  position: absolute;
  right: -20px;
  top: -20px;
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: rgba(47,128,237,0.08);
  pointer-events: none;
}

/* ── Icono ── */
.sg-ico {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.ico-azul    { background: rgba(47,128,237,0.10);  color: #1D52B7; }
.ico-verde   { background: rgba(39,174,96,0.10);   color: #27AE60; }
.ico-naranja { background: rgba(242,153,74,0.10);  color: #F2994A; }
.ico-rojo    { background: rgba(235,87,87,0.10);   color: #EB5757; }

/* ── Cuerpo ── */
.sg-body {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.sg-lbl {
  font-size: 9px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  color: #828282;
  margin: 0 0 4px;
  font-family: 'Montserrat', sans-serif;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sg-card--featured .sg-lbl { color: rgba(255,255,255,0.5); }

.sg-val {
  font-size: 32px;
  font-weight: 700;
  color: #333333;
  line-height: 1;
  margin: 0;
  font-family: 'Montserrat', sans-serif;
}

.sg-card--featured .sg-val { color: #FFFFFF; }

.sg-link {
  font-size: 10px;
  font-weight: 600;
  color: #2F80ED;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  margin-top: 4px;
  transition: color 0.15s;
  letter-spacing: 0.03em;
}

.sg-link:hover { color: #1A4184; }

/* ── Skeleton ── */
.sg-skeleton {
  width: 80px;
  height: 32px;
  border-radius: 6px;
  margin: 2px 0;
  background: linear-gradient(90deg, #E0E0E0 25%, #F2F4F7 50%, #E0E0E0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}

.sg-skeleton--dark {
  background: linear-gradient(90deg,
    rgba(255,255,255,0.10) 25%,
    rgba(255,255,255,0.20) 50%,
    rgba(255,255,255,0.10) 75%);
  background-size: 200% 100%;
}

@keyframes shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ── Tendencia ── */
.sg-cambio {
  position: absolute;
  bottom: 10px;
  right: 12px;
  font-size: 9px;
  font-weight: 700;
  font-family: 'Montserrat', sans-serif;
  display: flex;
  align-items: center;
  gap: 3px;
  letter-spacing: 0.03em;
}

.cambio-up { color: #27AE60; }
.cambio-dn { color: #EB5757; }
.cambio-ne { color: #828282; }

/* ── Responsive ── */
@media (max-width: 1100px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 500px)  { .stats-grid { grid-template-columns: 1fr; } .sg-val { font-size: 28px; } }
</style>