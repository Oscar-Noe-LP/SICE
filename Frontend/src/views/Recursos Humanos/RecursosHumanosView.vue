<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="rrhh-page">

      <!-- Breadcrumb -->
      <nav class="breadcrumb">
        <router-link to="/inicio" class="breadcrumb-link">Inicio</router-link>
        <span class="sep">›</span>
        <span class="breadcrumb-actual">Recursos Humanos</span>
      </nav>

      <!-- Encabezado -->
      <div class="page-header">
        <h1 class="page-title">Recursos Humanos</h1>
      </div>

      <!-- Toast -->
      <transition name="toast-slide">
        <div v-if="notificacion.visible" class="toast" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo === 'exito'" xmlns="http://www.w3.org/2000/svg"
               class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg"
               class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- Tarjetas KPI -->
      <div class="stats-grid">

        <!-- Total Empleados -->
        <div class="stat-card stat-azul">
          <div class="stat-icono-wrapper">
            <svg xmlns="http://www.w3.org/2000/svg" class="stat-icono" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
          <div class="stat-info">
            <p class="stat-etiqueta">Total de Empleados</p>
            <div v-if="cargando" class="stat-skeleton"></div>
            <p v-else class="stat-numero">{{ totalEmpleados }}</p>
            <span class="stat-link" @click="irAEmpleados">Ver empleados →</span>
          </div>
        </div>

        <!-- Docentes Activos -->
        <div class="stat-card">
          <div class="stat-icono-wrapper stat-icono-verde">
            <svg xmlns="http://www.w3.org/2000/svg" class="stat-icono-verde-svg" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 14l9-5-9-5-9 5 9 5z" />
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
            </svg>
          </div>
          <div class="stat-info">
            <p class="stat-etiqueta">Docentes Activos</p>
            <div v-if="cargando" class="stat-skeleton"></div>
            <p v-else class="stat-numero">{{ totalDocentes }}</p>
            <span class="stat-link" @click="irADocentes">Ver docentes →</span>
          </div>
        </div>

        <!-- Departamentos -->
        <div class="stat-card">
          <div class="stat-icono-wrapper stat-icono-naranja">
            <svg xmlns="http://www.w3.org/2000/svg" class="stat-icono-naranja-svg" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <div class="stat-info">
            <p class="stat-etiqueta">Departamentos</p>
            <div v-if="cargando" class="stat-skeleton"></div>
            <p v-else class="stat-numero">{{ totalDepartamentos }}</p>
            <span class="stat-link" @click="irAAdscripciones">Ver adscripciones →</span>
          </div>
        </div>

        <!-- Puestos Registrados -->
        <div class="stat-card">
          <div class="stat-icono-wrapper stat-icono-azul-suave">
            <svg xmlns="http://www.w3.org/2000/svg" class="stat-icono-azul-suave-svg" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <div class="stat-info">
            <p class="stat-etiqueta">Puestos Registrados</p>
            <div v-if="cargando" class="stat-skeleton"></div>
            <p v-else class="stat-numero">{{ totalPuestos }}</p>
            <span class="stat-link" @click="irAPuestos">Ver puestos →</span>
          </div>
        </div>

      </div>

      <!-- Alerta de error -->
      <div v-if="errorCarga" class="alerta-error">
        <svg xmlns="http://www.w3.org/2000/svg" class="alerta-icono" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>No se pudieron cargar los datos. Verifica la conexión con el servidor.</span>
        <button class="btn-reintentar" @click="cargarDatos">Reintentar</button>
      </div>

      <!-- Accesos Rápidos -->
      <div class="accesos-seccion">
        <h2 class="seccion-titulo">Accesos Rápidos</h2>
        <div class="accesos-grid">

          <div class="acceso-card" @click="irAEmpleados">
            <div class="acceso-icono acceso-azul">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                   stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <div class="acceso-contenido">
              <h4>Gestionar Empleados</h4>
              <p>Consulta y edita el personal</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="acceso-flecha" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>

          <div class="acceso-card" @click="irADocentes">
            <div class="acceso-icono acceso-verde">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                   stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 14l9-5-9-5-9 5 9 5zM12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
              </svg>
            </div>
            <div class="acceso-contenido">
              <h4>Consultar Docentes</h4>
              <p>Revisa el registro de docentes</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="acceso-flecha" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>

          <div class="acceso-card" @click="irAAdscripciones">
            <div class="acceso-icono acceso-amarillo">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                   stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <div class="acceso-contenido">
              <h4>Adscripciones</h4>
              <p>Asignación de personal por departamento</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="acceso-flecha" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>

          <div class="acceso-card" @click="irAPuestos">
            <div class="acceso-icono acceso-morado">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                   stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <div class="acceso-contenido">
              <h4>Puestos</h4>
              <p>Catálogo de puestos de trabajo</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" class="acceso-flecha" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </div>

        </div>
      </div>

      <!-- Footer -->
      <footer class="pie-pagina">
        © 2026 Tecnológico Nacional de México · Todos los derechos reservados
      </footer>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`

const router = useRouter()

const cargando           = ref(false)
const errorCarga         = ref(false)
const totalEmpleados     = ref(0)
const totalDocentes      = ref(0)
const totalDepartamentos = ref(0)
const totalPuestos       = ref(0)

const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

const cargarDatos = async () => {
  cargando.value   = true
  errorCarga.value = false
  try {
    const res = await fetch(`${API}/recursos-humanos/dashboard`)
    if (!res.ok) throw new Error('Error en la respuesta del servidor')
    const data = await res.json()

    totalEmpleados.value     = data.kpis?.empleados     ?? 0
    totalDocentes.value      = data.kpis?.docentes      ?? 0
    totalDepartamentos.value = data.kpis?.departamentos ?? 0
    totalPuestos.value       = data.kpis?.puestos       ?? 0

    console.log('✅ Dashboard RR.HH. cargado:', data)
  } catch (error) {
    console.error('❌ Error cargando dashboard RR.HH.:', error)
    errorCarga.value = true
  } finally {
    cargando.value = false
  }
}

onMounted(() => { cargarDatos() })

const irAEmpleados     = () => router.push('/recursos-humanos/empleados')
const irADocentes      = () => router.push('/recursos-humanos/docentes')
const irAAdscripciones = () => router.push('/recursos-humanos/adscripciones')
const irAPuestos       = () => router.push('/recursos-humanos/puestos')
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.rrhh-page {
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
  --morado:      #7C3AED;

  width: 100%;
  
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
  padding-bottom: 2rem;
}

/* ── Breadcrumb ── */
.breadcrumb {
  display: flex; align-items: center; gap: 0.4rem;
  color: var(--gris); font-size: 0.88rem; margin-bottom: 0.75rem;
}
.breadcrumb .sep { color: #E5E7EB; }
.breadcrumb-link { color: var(--gris); text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: var(--azul); }
.breadcrumb-actual { color: var(--azul); font-weight: 600; }

/* ── Encabezadoo ── */
.page-header { margin-bottom: 1.4rem; }
.page-title { color: var(--texto); font-size: 1.75rem; font-weight: 700; letter-spacing: -0.02em; margin: 0; }

/* ── Toast ── */
.toast {
  position: fixed; bottom: 2rem; right: 2rem;
  display: flex; align-items: center; gap: 10px;
  padding: 0.9rem 1.4rem; border-radius: 10px;
  color: white; font-weight: 700; font-size: 0.9rem;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 3000;
  font-family: 'Montserrat', sans-serif; max-width: 380px;
}
.toast.exito { background: var(--azul); }
.toast.error { background: var(--rojo); }
.toast-icono { width: 20px; height: 20px; flex-shrink: 0; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all 0.3s ease; }
.toast-slide-enter-from, .toast-slide-leave-to { opacity: 0; transform: translateX(100%); }

/* ── Tarjetas KPI ── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem; margin-bottom: 1.5rem;
}
.stat-card {
  background: #FFFFFF; border-radius: 12px;
  padding: 1.3rem 1.4rem; display: flex; align-items: center; gap: 1rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid var(--borde);
  transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.09); }

.stat-azul { background: var(--azul); border-color: var(--azul); }
.stat-azul .stat-etiqueta  { color: rgba(255,255,255,0.8); }
.stat-azul .stat-numero    { color: #FFFFFF; }
.stat-azul .stat-link      { color: rgba(255,255,255,0.85); }
.stat-azul .stat-icono-wrapper { background: rgba(255,255,255,0.2); }
.stat-azul .stat-icono-wrapper svg { stroke: #FFFFFF; }

.stat-icono-wrapper {
  width: 46px; height: 46px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; background: var(--azul-suave);
}
.stat-icono-wrapper svg { width: 22px; height: 22px; stroke: var(--azul); }

.stat-icono-verde        { background: #DCFCE7; }
.stat-icono-verde-svg    { width: 22px; height: 22px; stroke: var(--verde); }
.stat-icono-naranja      { background: #FEF3C7; }
.stat-icono-naranja-svg  { width: 22px; height: 22px; stroke: var(--amarillo); }
.stat-icono-azul-suave     { background: var(--azul-suave); }
.stat-icono-azul-suave-svg { width: 22px; height: 22px; stroke: var(--azul); }

.stat-info { display: flex; flex-direction: column; gap: 2px; }
.stat-etiqueta { font-size: 0.83rem; color: var(--gris); font-weight: 500; margin: 0; }
.stat-numero { font-size: 2rem; font-weight: 700; color: var(--texto); margin: 2px 0; line-height: 1; }
.stat-link { font-size: 0.82rem; color: var(--azul); font-weight: 600; cursor: pointer; transition: color 0.15s; }
.stat-link:hover { color: var(--azul-hover); }

.stat-skeleton {
  width: 80px; height: 32px;
  background: linear-gradient(90deg, #E5E7EB 25%, #F3F4F6 50%, #E5E7EB 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite; border-radius: 6px; margin: 2px 0;
}
.stat-azul .stat-skeleton {
  background: linear-gradient(90deg, rgba(255,255,255,0.2) 25%, rgba(255,255,255,0.35) 50%, rgba(255,255,255,0.2) 75%);
  background-size: 200% 100%;
}
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* ── Alerta de error ── */
.alerta-error {
  display: flex; align-items: center; gap: 10px;
  background: #FEF2F2; border: 1px solid #FECACA; border-radius: 10px;
  padding: 12px 16px; margin-bottom: 1.4rem;
  font-size: 0.9rem; color: var(--rojo); font-weight: 500;
}
.alerta-icono { width: 20px; height: 20px; flex-shrink: 0; stroke: var(--rojo); }
.btn-reintentar {
  margin-left: auto; background: var(--rojo); color: white;
  border: none; padding: 6px 16px; border-radius: 6px;
  font-weight: 600; font-size: 0.85rem; cursor: pointer;
  font-family: 'Montserrat', sans-serif; transition: background 0.15s; white-space: nowrap;
}
.btn-reintentar:hover { background: #B91C1C; }

/* ── Accesos Rápidos ── */
.accesos-seccion { margin-bottom: 2rem; }
.seccion-titulo { font-size: 1.05rem; font-weight: 700; color: var(--texto); margin: 0 0 1rem; }
.accesos-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1rem;
}
.acceso-card {
  background: #FFFFFF; border-radius: 12px; padding: 1.2rem 1.4rem;
  display: flex; align-items: center; gap: 1rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid var(--borde);
  cursor: pointer; transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
}
.acceso-card:hover {
  transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); border-color: var(--azul);
}
.acceso-icono {
  width: 44px; height: 44px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.acceso-icono svg { width: 22px; height: 22px; }
.acceso-azul     { background: var(--azul-suave); }
.acceso-azul svg     { stroke: var(--azul); }
.acceso-verde    { background: #DCFCE7; }
.acceso-verde svg    { stroke: var(--verde); }
.acceso-amarillo { background: #FEF3C7; }
.acceso-amarillo svg { stroke: var(--amarillo); }
.acceso-morado   { background: #EDE9FE; }
.acceso-morado svg   { stroke: var(--morado); }

.acceso-contenido { flex: 1; }
.acceso-contenido h4 { font-size: 0.93rem; font-weight: 700; color: var(--texto); margin: 0 0 3px; }
.acceso-contenido p  { font-size: 0.82rem; color: var(--gris); margin: 0; }
.acceso-flecha { width: 18px; height: 18px; stroke: #9CA3AF; flex-shrink: 0; transition: stroke 0.15s; }
.acceso-card:hover .acceso-flecha { stroke: var(--azul); }

/* ── Footer ── */
.pie-pagina {
  text-align: center; color: #9CA3AF; font-size: 0.82rem;
  padding-top: 2rem; border-top: 1px solid var(--borde);
  margin-top: 1rem; font-family: 'Montserrat', sans-serif;
}
</style>