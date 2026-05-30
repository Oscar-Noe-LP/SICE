<template>
  <div class="stats-grid">
    <div
      v-for="stat in stats"
      :key="stat.key"
      class="sg"
      :class="{ 'sg--featured': stat.featured }"
      @click="stat.ruta ? $emit('navegar', stat.ruta) : null"
      :role="stat.ruta ? 'button' : 'region'"
      :tabindex="stat.ruta ? 0 : -1"
      @keydown.enter="stat.ruta ? $emit('navegar', stat.ruta) : null"
    >
      <!-- Icono -->
      <div class="sg-ico" :class="!stat.featured ? stat.icoClass : 'sg-ico--white'" aria-hidden="true">
        <!-- users -->
        <svg v-if="stat.iconoTipo==='users'" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <!-- list -->
        <svg v-else-if="stat.iconoTipo==='list'" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
        <!-- grid -->
        <svg v-else-if="stat.iconoTipo==='grid'" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
        </svg>
        <!-- book -->
        <svg v-else-if="stat.iconoTipo==='book'" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <!-- graduation -->
        <svg v-else-if="stat.iconoTipo==='graduation'" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
        </svg>
        <!-- ban -->
        <svg v-else-if="stat.iconoTipo==='ban'" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
        </svg>
        <!-- alert -->
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
      </div>

      <!-- Cuerpo -->
      <div class="sg-body">
        <p class="sg-lbl">{{ stat.label }}</p>
        <div v-if="cargando" class="sg-sk" :class="{ 'sg-sk--dark': stat.featured }"></div>
        <p v-else class="sg-val">{{ formatNum(stat.valor) }}</p>
        <span v-if="stat.ruta" class="sg-link" :style="stat.featured ? { color:'rgba(255,255,255,0.7)' } : {}">
          {{ stat.linkLabel }} →
        </span>
      </div>

      <!-- Tendencia -->
      <div v-if="stat.cambio" class="sg-cambio" :class="stat.cambio.clase" :style="stat.featured ? { color:'rgba(255,255,255,0.55)' } : {}">
        <!-- up -->
        <svg v-if="stat.cambio.tipo==='up'" xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
        </svg>
        <!-- dn -->
        <svg v-else-if="stat.cambio.tipo==='dn'" xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
        </svg>
        <!-- ne -->
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/>
        </svg>
        {{ stat.cambio.texto }}
      </div>

    </div>
  </div>
</template>

<script setup>
import { formatNum } from '@/store/dashboardStore.js'

defineProps({
  stats:    { type: Array,   required: true },
  cargando: { type: Boolean, default: false },
})

defineEmits(['navegar'])
</script>

<style scoped>
.stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; }

.sg {
  background:#FFFFFF; border:1px solid #E0E0E0; border-radius:12px;
  padding:16px; display:flex; align-items:flex-start; gap:12px;
  box-shadow:0 2px 8px rgba(29,82,183,0.05);
  transition:all 0.15s ease; position:relative; overflow:hidden;
}
.sg[tabindex="0"] { cursor:pointer; }
.sg[tabindex="0"]:hover { border-color:#2F80ED; box-shadow:0 0 0 3px rgba(29,82,183,0.07); transform:translateY(-2px); }

.sg--featured { background:#0B2545; border-color:#0B2545; }
.sg--featured::after {
  content:''; position:absolute; right:-20px; top:-20px;
  width:80px; height:80px; border-radius:50%;
  background:rgba(47,128,237,0.08); pointer-events:none;
}

.sg-ico {
  width:40px; height:40px; border-radius:10px;
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.sg-ico svg { stroke:currentColor; }
.sg-ico--white { background:rgba(255,255,255,0.15); color:#FFFFFF; }
.ico-azul    { background:rgba(47,128,237,0.10);  color:#1D52B7; }
.ico-verde   { background:rgba(39,174,96,0.10);   color:#27AE60; }
.ico-naranja { background:rgba(242,153,74,0.10);  color:#F2994A; }
.ico-rojo    { background:rgba(235,87,87,0.10);   color:#EB5757; }
.ico-morado  { background:rgba(155,81,224,0.10);  color:#9B51E0; }

.sg-body { flex:1; min-width:0; display:flex; flex-direction:column; gap:2px; }

.sg-lbl {
  font-size:9px; font-weight:700; text-transform:uppercase;
  letter-spacing:.07em; color:#828282; margin:0 0 4px;
  font-family:'Montserrat',sans-serif; white-space:nowrap;
  overflow:hidden; text-overflow:ellipsis;
}
.sg--featured .sg-lbl { color:rgba(255,255,255,0.5); }

.sg-val {
  font-size:32px; font-weight:700; color:#333333;
  line-height:1; margin:0; font-family:'Montserrat',sans-serif;
}
.sg--featured .sg-val { color:#FFFFFF; }

.sg-link {
  font-size:10px; font-weight:600; color:#2F80ED;
  cursor:pointer; font-family:'Montserrat',sans-serif;
  margin-top:4px; transition:color 0.15s; letter-spacing:.03em;
}
.sg-link:hover { color:#1A4184; }

.sg-sk {
  width:80px; height:32px; border-radius:6px; margin:2px 0;
  background:linear-gradient(90deg,#E0E0E0 25%,#F2F4F7 50%,#E0E0E0 75%);
  background-size:200% 100%; animation:shimmer 1.4s infinite;
}
.sg-sk--dark {
  background:linear-gradient(90deg,rgba(255,255,255,.10) 25%,rgba(255,255,255,.20) 50%,rgba(255,255,255,.10) 75%);
  background-size:200% 100%;
}
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

.sg-cambio {
  position:absolute; bottom:9px; right:11px;
  font-size:9px; font-weight:700; font-family:'Montserrat',sans-serif;
  display:flex; align-items:center; gap:3px; letter-spacing:.03em;
}
.sg-cambio svg { stroke:currentColor; }
.cambio-up { color:#27AE60; }
.cambio-dn { color:#EB5757; }
.cambio-ne { color:#828282; }

@media (max-width:1100px) { .stats-grid { grid-template-columns:repeat(2,1fr); } }
@media (max-width:500px)  { .stats-grid { grid-template-columns:1fr; } .sg-val { font-size:28px; } }
</style>