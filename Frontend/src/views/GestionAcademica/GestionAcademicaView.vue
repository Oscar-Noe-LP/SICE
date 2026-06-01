<template>
  <MainLayout>
    <div class="ga-page">

      <!-- Breadcrumb -->
      <nav class="breadcrumb">
        <span class="breadcrumb-actual">Gestión Académica</span>
      </nav>

      <!-- Encabezado -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Gestión Académica</h1>
          <p class="page-subtitle">Administra la estructura curricular de la institución</p>
        </div>
      </div>

      <!-- Barra de carga -->
      <div class="barra-carga" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- Alerta de error -->
      <div v-if="errorCarga" class="alerta-error">
        <svg xmlns="http://www.w3.org/2000/svg" class="alerta-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>No se pudieron cargar los datos. Verifica la conexión con el servidor.</span>
        <button class="btn-reintentar" @click="cargarResumen">Reintentar</button>
      </div>

      <!-- KPIs -->
      <div class="kpi-grid">

        <!-- Carreras activas -->
        <div class="kpi-card">
          <div class="kpi-icono-wrapper kpi-azul">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <div class="kpi-info">
            <p class="kpi-etiqueta">Carreras activas</p>
            <div v-if="cargando" class="kpi-skeleton"></div>
            <p v-else class="kpi-valor">{{ resumen.carreras }}</p>
          </div>
        </div>

        <!-- Materias registradas -->
        <div class="kpi-card">
          <div class="kpi-icono-wrapper kpi-verde">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
          <div class="kpi-info">
            <p class="kpi-etiqueta">Materias registradas</p>
            <div v-if="cargando" class="kpi-skeleton"></div>
            <p v-else class="kpi-valor">{{ resumen.materias }}</p>
          </div>
        </div>

        <!-- Periodos activos -->
        <div class="kpi-card">
          <div class="kpi-icono-wrapper kpi-amarillo">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          <div class="kpi-info">
            <p class="kpi-etiqueta">Periodos activos</p>
            <div v-if="cargando" class="kpi-skeleton"></div>
            <p v-else class="kpi-valor">{{ resumen.periodos }}</p>
          </div>
        </div>

        <!-- Aulas disponibles -->
        <div class="kpi-card">
          <div class="kpi-icono-wrapper kpi-morado">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
          </div>
          <div class="kpi-info">
            <p class="kpi-etiqueta">Aulas disponibles</p>
            <div v-if="cargando" class="kpi-skeleton"></div>
            <p v-else class="kpi-valor">{{ resumen.aulas }}</p>
          </div>
        </div>

      </div>

      <!-- Secciones del módulo -->
      <div class="accesos-seccion">
        <h2 class="seccion-titulo">Secciones del módulo</h2>

        <div class="accesos-grid">

          <!-- Carreras -->
          <div class="acceso-card" @click="$router.push('/gestion-academica/carreras')">
            <div class="acceso-icono acceso-azul">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <div class="acceso-contenido">
              <h4>Carreras</h4>
              <p>Gestiona las carreras de la institución</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="acceso-flecha" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>

          <!-- Planes de Estudio -->
          <div class="acceso-card" @click="$router.push('/gestion-academica/planes')">
            <div class="acceso-icono acceso-verde">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <div class="acceso-contenido">
              <h4>Planes de Estudio</h4>
              <p>Administra los planes curriculares por carrera</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="acceso-flecha" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>

          <!-- Materias -->
          <div class="acceso-card" @click="$router.push('/gestion-academica/materias')">
            <div class="acceso-icono acceso-amarillo">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
            <div class="acceso-contenido">
              <h4>Materias</h4>
              <p>Catálogo completo de materias del sistema</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="acceso-flecha" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>

          <!-- Prerrequisitos -->
          <div class="acceso-card" @click="$router.push('/gestion-academica/prerrequisitos')">
            <div class="acceso-icono acceso-rojo">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <div class="acceso-contenido">
              <h4>Prerrequisitos</h4>
              <p>Define las dependencias entre materias</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="acceso-flecha" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>

          <!-- Periodos Académicos -->
          <div class="acceso-card" @click="$router.push('/gestion-academica/periodos')">
            <div class="acceso-icono acceso-morado">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <div class="acceso-contenido">
              <h4>Periodos Académicos</h4>
              <p>Gestiona los periodos escolares vigentes</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="acceso-flecha" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>

          <!-- Edificios y Aulas -->
          <div class="acceso-card" @click="$router.push('/gestion-academica/edificios-aulas')">
            <div class="acceso-icono acceso-gris">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
            </div>
            <div class="acceso-contenido">
              <h4>Edificios y Aulas</h4>
              <p>Administra la infraestructura del campus</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="acceso-flecha" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>

        </div>
      </div>

      <!-- Footer -->
      <footer class="pie-pagina">
        © 2026 Tecnológico Nacional de México | Todos los derechos reservados
      </footer>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`

const cargando   = ref(false)
const errorCarga = ref(false)

const resumen = ref({
  carreras: 0,
  materias: 0,
  periodos: 0,
  aulas:    0
})

const cargarResumen = async () => {
  cargando.value   = true
  errorCarga.value = false
  try {
    const res = await fetch(`${API}/gestion-academica/resumen`)
    if (!res.ok) throw new Error('Error del servidor')
    const data = await res.json()
    resumen.value.carreras = data.carreras ?? 0
    resumen.value.materias = data.materias ?? 0
    resumen.value.periodos = data.periodos ?? 0
    resumen.value.aulas    = data.aulas    ?? 0
  } catch (error) {
    console.error('Error cargando resumen de gestión académica:', error)
    errorCarga.value = true
  } finally {
    cargando.value = false
  }
}

onMounted(() => { cargarResumen() })
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* ── Variables ─────────────────────────────────────────────────── */
.ga-page {
  --azul:        #1B396A;
  --azul-hover:  #1D4ED8;
  --azul-suave:  #DBEAFE;
  --borde:       #E5E7EB;
  --fondo:       #F5F5F5;
  --texto:       #1A1A1A;
  --gris:        #6B7280;
  --verde:       #16A34A;
  --rojo:        #DC2626;
  --amarillo:    #F59E0B;

  width: 100%;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
  padding-bottom: 2rem;
}

/* ── Breadcrumb ─────────────────────────────────────────────────── */
.breadcrumb {
  color: var(--gris);
  font-size: 0.88rem;
  margin-bottom: 0.75rem;
}
.breadcrumb-actual {
  color: var(--azul);
  font-weight: 600;
}

/* ── Encabezado ─────────────────────────────────────────────────── */
.page-header {
  margin-bottom: 1.4rem;
}
.page-title {
  color: var(--texto);
  font-size: 1.75rem;
  font-weight: 700;
  letter-spacing: -0.02em;
  margin: 0 0 4px;
}
.page-subtitle {
  color: var(--gris);
  font-size: 0.93rem;
  margin: 0;
}

/* ── Barra de carga ─────────────────────────────────────────────── */
.barra-carga {
  height: 3px;
  background: transparent;
  border-radius: 2px;
  margin-bottom: 1rem;
  overflow: hidden;
  opacity: 0;
  transition: opacity 0.3s;
}
.barra-carga.visible { opacity: 1; }
.barra-progreso {
  height: 100%;
  width: 40%;
  background: var(--azul);
  border-radius: 2px;
  animation: deslizar 1.2s ease-in-out infinite;
}
@keyframes deslizar {
  0%   { transform: translateX(-100%); }
  100% { transform: translateX(350%); }
}

/* ── Alerta de error ─────────────────────────────────────────────── */
.alerta-error {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #FEF2F2;
  border: 1px solid #FECACA;
  border-radius: 10px;
  padding: 12px 16px;
  margin-bottom: 1.4rem;
  font-size: 0.9rem;
  color: var(--rojo);
  font-weight: 500;
  font-family: 'Montserrat', sans-serif;
}
.alerta-icono {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  stroke: var(--rojo);
}
.btn-reintentar {
  margin-left: auto;
  background: var(--rojo);
  color: white;
  border: none;
  padding: 6px 16px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  transition: background 0.15s;
  white-space: nowrap;
}
.btn-reintentar:hover { background: #B91C1C; }

/* ── KPIs ────────────────────────────────────────────────────────── */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}
.kpi-card {
  background: #FFFFFF;
  border-radius: 12px;
  padding: 1.2rem 1.4rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border: 1px solid var(--borde);
  transition: transform 0.2s, box-shadow 0.2s;
  cursor: default;
}
.kpi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.09);
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
.kpi-icono-wrapper svg { width: 22px; height: 22px; }

.kpi-azul     { background: #DBEAFE; }
.kpi-azul     svg { stroke: var(--azul); }
.kpi-verde    { background: #DCFCE7; }
.kpi-verde    svg { stroke: var(--verde); }
.kpi-amarillo { background: #FEF3C7; }
.kpi-amarillo svg { stroke: var(--amarillo); }
.kpi-morado   { background: #EDE9FE; }
.kpi-morado   svg { stroke: #7C3AED; }

.kpi-info { display: flex; flex-direction: column; gap: 2px; }
.kpi-etiqueta {
  font-size: 0.83rem;
  color: var(--gris);
  font-weight: 500;
  margin: 0;
}
.kpi-valor {
  font-size: 2rem;
  font-weight: 700;
  color: var(--texto);
  margin: 2px 0;
  line-height: 1;
}

/* Skeleton mientras carga */
.kpi-skeleton {
  width: 70px;
  height: 32px;
  background: linear-gradient(90deg, #E5E7EB 25%, #F3F4F6 50%, #E5E7EB 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  border-radius: 6px;
  margin: 2px 0;
}
@keyframes shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ── Secciones del módulo ────────────────────────────────────────── */
.accesos-seccion { margin-bottom: 2rem; }
.seccion-titulo {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--texto);
  margin: 0 0 1rem;
}
.accesos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
}
.acceso-card {
  background: #FFFFFF;
  border-radius: 12px;
  padding: 1.2rem 1.4rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border: 1px solid var(--borde);
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
}
.acceso-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  border-color: var(--azul);
}
.acceso-icono {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.acceso-icono svg { width: 22px; height: 22px; }

.acceso-azul     { background: #DBEAFE; }
.acceso-azul     svg { stroke: var(--azul); }
.acceso-verde    { background: #DCFCE7; }
.acceso-verde    svg { stroke: var(--verde); }
.acceso-amarillo { background: #FEF3C7; }
.acceso-amarillo svg { stroke: var(--amarillo); }
.acceso-rojo     { background: #FEF2F2; }
.acceso-rojo     svg { stroke: var(--rojo); }
.acceso-morado   { background: #EDE9FE; }
.acceso-morado   svg { stroke: #7C3AED; }
.acceso-gris     { background: #F3F4F6; }
.acceso-gris     svg { stroke: var(--gris); }

.acceso-contenido { flex: 1; }
.acceso-contenido h4 {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--texto);
  margin: 0 0 4px;
}
.acceso-contenido p {
  font-size: 0.82rem;
  color: var(--gris);
  margin: 0;
  line-height: 1.4;
}
.acceso-flecha {
  width: 18px;
  height: 18px;
  stroke: #9CA3AF;
  flex-shrink: 0;
  transition: stroke 0.15s, transform 0.2s;
}
.acceso-card:hover .acceso-flecha {
  stroke: var(--azul);
  transform: translateX(3px);
}

/* ── Footer ─────────────────────────────────────────────────────── */
.pie-pagina {
  text-align: center;
  color: #9CA3AF;
  font-size: 0.82rem;
  padding-top: 2rem;
  border-top: 1px solid var(--borde);
  margin-top: 1rem;
  font-family: 'Montserrat', sans-serif;
}

@media (max-width: 900px) {
  .kpi-grid     { grid-template-columns: repeat(2, 1fr); }
  .accesos-grid { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
  .kpi-grid   { grid-template-columns: 1fr 1fr; }
  .page-title { font-size: 1.5rem; }
}
</style>