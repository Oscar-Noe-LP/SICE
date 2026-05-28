<template>
  <div
    class="cc"
    :class="{ 'cc--activa': activa }"
    :style="activa ? { borderColor: color, boxShadow: `0 4px 20px ${hexRgba(color,0.18)}` } : {}"
    @click="$emit('click')"
    role="button"
    tabindex="0"
    :aria-label="`VER DETALLE DE ${carrera.nombre}`"
    @keydown.enter="$emit('click')"
    @keydown.space.prevent="$emit('click')"
  >
    <!-- Franja de color -->
    <div class="cc__barra" :style="{ background: color }" aria-hidden="true"></div>

    <!-- Header -->
    <div class="cc__header">
      <div class="cc__ico" :style="{ background: hexRgba(color,0.12), color }" aria-hidden="true">
        <!-- monitor - sistemas -->
        <svg v-if="tipoIcono==='monitor'" xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <!-- settings - industrial -->
        <svg v-else-if="tipoIcono==='settings'" xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <!-- zap - electrica -->
        <svg v-else-if="tipoIcono==='zap'" xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        <!-- wrench - mecanica -->
        <svg v-else-if="tipoIcono==='wrench'" xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
        </svg>
        <!-- briefcase - empresarial -->
        <svg v-else-if="tipoIcono==='briefcase'" xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <!-- flask - bioquimica/default -->
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
        </svg>
      </div>

      <div class="cc__titulo-wrap">
        <h3 class="cc__titulo">{{ carrera.nombre }}</h3>
        <span class="cc__grupos-pill">
          <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          {{ carrera.grupos }} GRUPO{{ carrera.grupos !== 1 ? 'S' : '' }}
        </span>
      </div>
    </div>

    <!-- Número grande: matrículas -->
    <div class="cc__matriculas">
      <span class="cc__num" :style="{ color }">{{ formatNum(carrera.matriculas) }}</span>
      <span class="cc__num-label">MATRICULAS TOTALES</span>
    </div>

    <!-- Barra regulares vs irregulares -->
    <div class="cc__barra-estado" role="img" :aria-label="`${pctReg}% regulares, ${pctIrreg}% irregulares`">
      <div class="cc__fill cc__fill--reg"   :style="{ width: pctReg   + '%' }"></div>
      <div class="cc__fill cc__fill--irreg" :style="{ width: pctIrreg + '%' }"></div>
    </div>

    <!-- Chips regulares / irregulares -->
    <div class="cc__chips">
      <div class="cc__chip">
        <span class="cc__dot cc__dot--reg" aria-hidden="true"></span>
        <div class="cc__chip-info">
          <span class="cc__chip-num">{{ formatNum(carrera.regulares) }}</span>
          <span class="cc__chip-label">REGULARES</span>
        </div>
        <span class="cc__chip-pct cc__chip-pct--verde">{{ pctReg }}%</span>
      </div>
      <div class="cc__chip">
        <span class="cc__dot cc__dot--irreg" aria-hidden="true"></span>
        <div class="cc__chip-info">
          <span class="cc__chip-num">{{ formatNum(carrera.irregulares) }}</span>
          <span class="cc__chip-label">IRREGULARES</span>
        </div>
        <span class="cc__chip-pct cc__chip-pct--rojo">{{ pctIrreg }}%</span>
      </div>
    </div>

    <!-- Footer -->
    <div class="cc__footer">
      <span class="cc__ver" :style="{ color }">
        VER GRUPOS
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
      </span>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatNum } from '@/stores/dashboardStore.js'

const props = defineProps({
  carrera: { type: Object,  required: true },
  color:   { type: String,  default: '#1D52B7' },
  icono:   { type: String,  default: 'flask' },
  activa:  { type: Boolean, default: false },
})

defineEmits(['click'])

const tipoIcono = computed(() => props.icono)

const pctReg   = computed(() => props.carrera.matriculas
  ? Math.round((props.carrera.regulares / props.carrera.matriculas) * 100) : 0)
const pctIrreg = computed(() => props.carrera.matriculas ? 100 - pctReg.value : 0)

const hexRgba = (hex, a) => {
  if (!hex || hex.length < 7) return `rgba(29,82,183,${a})`
  return `rgba(${parseInt(hex.slice(1,3),16)},${parseInt(hex.slice(3,5),16)},${parseInt(hex.slice(5,7),16)},${a})`
}
</script>

<style scoped>
.cc {
  background:#FFFFFF; border:1.5px solid #E0E0E0; border-radius:14px;
  overflow:hidden; cursor:pointer; display:flex; flex-direction:column;
  transition:all 0.2s ease; box-shadow:0 2px 8px rgba(29,82,183,0.05);
}
.cc:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,0.10); border-color:#BDBDBD; }
.cc--activa { transform:translateY(-2px); }

.cc__barra { height:4px; width:100%; flex-shrink:0; }

.cc__header { display:flex; align-items:flex-start; gap:11px; padding:15px 15px 10px; }

.cc__ico {
  width:40px; height:40px; border-radius:10px;
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.cc__ico svg { stroke:currentColor; }

.cc__titulo-wrap { flex:1; min-width:0; display:flex; flex-direction:column; gap:5px; }

.cc__titulo {
  font-size:11px; font-weight:700; color:#333333;
  font-family:'Montserrat',sans-serif; line-height:1.35; margin:0; letter-spacing:.02em;
}

.cc__grupos-pill {
  display:inline-flex; align-items:center; gap:4px;
  background:#F2F4F7; border-radius:20px; padding:2px 8px;
  font-size:9px; font-weight:700; color:#828282;
  font-family:'Montserrat',sans-serif; width:fit-content; letter-spacing:.04em;
}
.cc__grupos-pill svg { stroke:#828282; }

.cc__matriculas { display:flex; align-items:baseline; gap:6px; padding:0 15px 11px; }
.cc__num { font-size:36px; font-weight:700; line-height:1; font-family:'Montserrat',sans-serif; letter-spacing:-.5px; }
.cc__num-label { font-size:10px; color:#828282; font-weight:600; font-family:'Montserrat',sans-serif; letter-spacing:.04em; margin-bottom:2px; }

.cc__barra-estado {
  display:flex; height:7px; margin:0 15px 13px;
  border-radius:99px; overflow:hidden; background:#F2F4F7; gap:2px;
}
.cc__fill { height:100%; border-radius:99px; transition:width .6s cubic-bezier(0.16,1,0.3,1); }
.cc__fill--reg   { background:#27AE60; }
.cc__fill--irreg { background:#EB5757; }

.cc__chips { display:grid; grid-template-columns:1fr 1fr; gap:7px; padding:0 15px 13px; }
.cc__chip  { display:flex; align-items:center; gap:7px; background:#F4F6F9; border-radius:8px; padding:8px 9px; }
.cc__dot   { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
.cc__dot--reg   { background:#27AE60; }
.cc__dot--irreg { background:#EB5757; }
.cc__chip-info  { flex:1; display:flex; flex-direction:column; gap:1px; min-width:0; }
.cc__chip-num   { font-size:15px; font-weight:700; color:#333333; font-family:'Montserrat',sans-serif; line-height:1; }
.cc__chip-label { font-size:8px; font-weight:700; color:#828282; font-family:'Montserrat',sans-serif; text-transform:uppercase; letter-spacing:.05em; }
.cc__chip-pct   { font-size:11px; font-weight:700; font-family:'Montserrat',sans-serif; flex-shrink:0; }
.cc__chip-pct--verde { color:#27AE60; }
.cc__chip-pct--rojo  { color:#EB5757; }

.cc__footer { padding:9px 15px 13px; border-top:1px solid #F2F4F7; margin-top:auto; }
.cc__ver {
  font-size:10px; font-weight:700; font-family:'Montserrat',sans-serif;
  display:flex; align-items:center; gap:4px; letter-spacing:.04em; transition:gap .15s;
}
.cc:hover .cc__ver { gap:7px; }
.cc__ver svg { stroke:currentColor; }
</style>