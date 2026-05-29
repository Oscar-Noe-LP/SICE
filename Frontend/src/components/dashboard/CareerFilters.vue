<template>
  <div class="cf" role="toolbar" aria-label="FILTROS POR CARRERA">

    <!-- Todas -->
    <button
      class="cf-btn"
      :class="{ 'cf-btn--activo': carreraActiva === null }"
      @click="$emit('filtrar', null)"
      type="button"
    >
      <span class="cf-ico cf-ico--all" aria-hidden="true">
        <!-- grid icon -->
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
        </svg>
      </span>
      <span class="cf-label">TODAS</span>
      <span class="cf-count" :class="{ 'cf-count--activo': carreraActiva === null }">{{ formatNum(totalAlumnos) }}</span>
    </button>

    <!-- Una por carrera -->
    <button
      v-for="(carrera, i) in carreras"
      :key="carrera.id_carrera"
      class="cf-btn"
      :class="{ 'cf-btn--activo': carreraActiva === carrera.id_carrera }"
      :style="carreraActiva === carrera.id_carrera
        ? { borderColor: colores[i % colores.length], background: hexRgba(colores[i % colores.length], 0.07) }
        : {}"
      @click="$emit('filtrar', carrera.id_carrera)"
      type="button"
      :aria-pressed="carreraActiva === carrera.id_carrera"
    >
      <span
        class="cf-ico"
        :style="{ background: hexRgba(colores[i % colores.length], 0.12), color: colores[i % colores.length] }"
        aria-hidden="true"
      >
        <!-- monitor - sistemas -->
        <svg v-if="icono(carrera.nombre)==='monitor'" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <!-- settings - industrial -->
        <svg v-else-if="icono(carrera.nombre)==='settings'" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <!-- zap - electrica -->
        <svg v-else-if="icono(carrera.nombre)==='zap'" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        <!-- wrench - mecanica -->
        <svg v-else-if="icono(carrera.nombre)==='wrench'" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
        </svg>
        <!-- briefcase - empresarial -->
        <svg v-else-if="icono(carrera.nombre)==='briefcase'" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <!-- flask - bioquimica -->
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
        </svg>
      </span>
      <span class="cf-label">{{ abreviar(carrera.nombre) }}</span>
      <span
        class="cf-count"
        :style="carreraActiva === carrera.id_carrera
          ? { background: colores[i % colores.length], color: '#FFFFFF' }
          : {}"
      >{{ carrera.matriculas }}</span>
    </button>

  </div>
</template>

<script setup>
import { COLORES_CARRERA, formatNum } from '@/store/dashboardStore.js'

defineProps({
  carreras:      { type: Array,  default: () => [] },
  carreraActiva: { type: Number, default: null },
  totalAlumnos:  { type: Number, default: 0 },
})

defineEmits(['filtrar'])

const colores = COLORES_CARRERA

const abreviar = (n = '') =>
  n.replace('ING. SISTEMAS COMPUTACIONALES', 'SISTEMAS')
   .replace('ING. INDUSTRIAL',               'INDUSTRIAL')
   .replace('ING. ELECTRICA',                'ELECTRICA')
   .replace('ING. MECANICA',                 'MECANICA')
   .replace('ING. BIOQUIMICA',               'BIOQUIMICA')
   .replace('LIC. GESTION EMPRESARIAL',      'G. EMPRESARIAL')
   .replace('ING. ', '').replace('LIC. ', '')

const icono = (n = '') => {
  const s = n.toLowerCase()
  if (s.includes('sistem') || s.includes('comput')) return 'monitor'
  if (s.includes('industrial'))                      return 'settings'
  if (s.includes('electr'))                          return 'zap'
  if (s.includes('mecani'))                          return 'wrench'
  if (s.includes('empresa') || s.includes('gesti')) return 'briefcase'
  return 'flask'
}

const hexRgba = (hex, a) => {
  if (!hex || hex.length < 7) return `rgba(29,82,183,${a})`
  return `rgba(${parseInt(hex.slice(1,3),16)},${parseInt(hex.slice(3,5),16)},${parseInt(hex.slice(5,7),16)},${a})`
}
</script>

<style scoped>
.cf { display:flex; align-items:center; gap:7px; flex-wrap:wrap; padding:2px 0; }

.cf-btn {
  display:flex; align-items:center; gap:6px;
  padding:5px 10px 5px 6px; border-radius:10px;
  border:1.5px solid #E0E0E0; background:#FFFFFF;
  cursor:pointer; font-family:'Montserrat',sans-serif;
  font-size:10px; font-weight:500; color:#828282;
  transition:all 0.15s; white-space:nowrap; line-height:1;
}
.cf-btn:hover { border-color:#BDBDBD; color:#333333; background:#F4F6F9; }
.cf-btn--activo { color:#333333; font-weight:700; box-shadow:0 2px 8px rgba(0,0,0,0.08); }

.cf-ico {
  width:22px; height:22px; border-radius:6px;
  display:flex; align-items:center; justify-content:center;
  flex-shrink:0; background:#F2F4F7; color:#828282;
}
.cf-ico--all { background:rgba(29,82,183,0.10); color:#1D52B7; }
.cf-btn--activo .cf-ico--all { background:#1D52B7; color:#FFFFFF; }
.cf-ico svg { stroke:currentColor; }

.cf-label {
  font-family:'Montserrat',sans-serif; font-size:10px;
  font-weight:inherit; color:inherit; letter-spacing:.03em;
  max-width:90px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
}

.cf-count {
  font-size:9px; font-weight:700; color:#828282;
  background:#F2F4F7; border-radius:20px;
  padding:2px 6px; flex-shrink:0;
  font-family:'Montserrat',sans-serif; transition:all 0.15s;
  min-width:22px; text-align:center;
}
.cf-count--activo { background:#1D52B7; color:#FFFFFF; }

@media (max-width:768px) {
  .cf { overflow-x:auto; flex-wrap:nowrap; padding-bottom:4px; scrollbar-width:none; }
  .cf::-webkit-scrollbar { display:none; }
  .cf-btn { flex-shrink:0; }
}
</style>