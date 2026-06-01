<template>
  <MainLayout>
    <div class="detalle-page">

      <nav class="breadcrumb">
        <span class="breadcrumb-link" @click="router.push('/personas')">Personas</span>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-actual">Detalle</span>
      </nav>

      <div class="barra-carga-global" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <div v-if="cargando && !persona.id_persona" class="estado-cargando">
        <div class="spinner-tabla"></div>
        <p>Cargando datos...</p>
      </div>

      <div v-else-if="persona.id_persona || persona.id">

        <!-- Card principal -->
        <div class="card-persona">
          <div class="card-header">
            <div class="avatar-circulo">
              {{ iniciales }}
            </div>
            <div class="card-header-info">
              <h1 class="nombre-completo">{{ nombreCompleto }}</h1>
              <p class="curp-display">{{ persona.curp }}</p>
              <span class="tipo-badge" :class="claseTipo(persona.tipo)">
                {{ persona.tipo || 'Sin asignar' }}
              </span>
            </div>
            <button class="btn-editar-persona" @click="editarPersona">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Editar
            </button>
          </div>

          <div class="card-body">
            <div class="grid-datos">
              <div class="dato-item">
                <span class="dato-label">Apellido Paterno</span>
                <span class="dato-valor">{{ persona.apellido_paterno || '—' }}</span>
              </div>
              <div class="dato-item">
                <span class="dato-label">Apellido Materno</span>
                <span class="dato-valor">{{ persona.apellido_materno || '—' }}</span>
              </div>
              <div class="dato-item">
                <span class="dato-label">Nombre</span>
                <span class="dato-valor">{{ persona.nombre || '—' }}</span>
              </div>
              <div class="dato-item">
                <span class="dato-label">Fecha de Nacimiento</span>
                <span class="dato-valor">{{ formatFecha(persona.fecha_nacimiento) }}</span>
              </div>
              <div class="dato-item">
                <span class="dato-label">Género</span>
                <span class="dato-valor">{{ persona.genero || '—' }}</span>
              </div>
              <div class="dato-item">
                <span class="dato-label">Estado Civil</span>
                <span class="dato-valor">{{ persona.estado_civil || '—' }}</span>
              </div>
              <div class="dato-item">
                <span class="dato-label">Nacionalidad</span>
                <span class="dato-valor">{{ persona.nacionalidad || '—' }}</span>
              </div>
              <div class="dato-item">
                <span class="dato-label">Correo Electrónico</span>
                <span class="dato-valor">{{ persona.correo || '—' }}</span>
              </div>
              <div class="dato-item">
                <span class="dato-label">Teléfono</span>
                <span class="dato-valor">{{ persona.telefono || '—' }}</span>
              </div>
              <div class="dato-item dato-item-full">
                <span class="dato-label">Dirección</span>
                <span class="dato-valor">{{ persona.direccion || '—' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Sección: Vínculos del sistema -->
        <div class="seccion-vinculos">
          <h2 class="seccion-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" class="seccion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
            Vínculos del Sistema
          </h2>

          <div class="vinculos-grid">

            <!-- Vínculo Alumno -->
            <div v-if="persona.alumno_id || persona.tipo === 'Alumno'" class="vinculo-card vinculo-alumno">
              <div class="vinculo-icono-wrap alumno">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 14l9-5-9-5-9 5 9 5zM12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                </svg>
              </div>
              <div class="vinculo-info">
                <p class="vinculo-tipo">Alumno registrado</p>
                <p class="vinculo-detalle">{{ persona.alumno?.numero_control || 'N/C' }}</p>
              </div>
              <button class="btn-vinculo" @click="router.push(`/alumnos`)">
                Ver ficha
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>

            <!-- Vínculo Empleado -->
            <div v-if="persona.empleado_id || persona.tipo === 'Empleado'" class="vinculo-card vinculo-empleado">
              <div class="vinculo-icono-wrap empleado">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="vinculo-info">
                <p class="vinculo-tipo">Empleado registrado</p>
                <p class="vinculo-detalle">{{ persona.empleado?.puesto || 'Ver ficha' }}</p>
              </div>
              <button class="btn-vinculo btn-vinculo-verde" @click="router.push(`/recursos-humanos/empleados`)">
                Ver ficha
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>

            <!-- Vínculo Usuario -->
            <div v-if="persona.usuario_id || persona.usuario" class="vinculo-card vinculo-usuario">
              <div class="vinculo-icono-wrap usuario">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <div class="vinculo-info">
                <p class="vinculo-tipo">Usuario del sistema</p>
                <p class="vinculo-detalle">{{ persona.usuario?.username || '—' }}</p>
              </div>
              <button class="btn-vinculo btn-vinculo-gris" @click="router.push('/usuarios')">
                Ver usuario
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>

            <!-- Sin vínculo -->
            <div
              v-if="!persona.alumno_id && persona.tipo !== 'Alumno' && !persona.empleado_id && persona.tipo !== 'Empleado' && !persona.usuario_id && !persona.usuario"
              class="vinculo-card vinculo-sin-asignar"
            >
              <div class="sin-vinculo-content">
                <span class="badge-sin-asignar">Sin asignar</span>
                <p class="sin-vinculo-msg">Esta persona aún no está vinculada a ningún módulo del sistema.</p>
                <div class="sin-vinculo-acciones">
                  <button class="btn-asignar-alumno" @click="asignarComoAlumno">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    + Asignar como Alumno
                  </button>
                  <button class="btn-asignar-empleado" @click="asignarComoEmpleado">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    + Asignar como Empleado
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sección: Historial de cambios -->
        <div class="seccion-historial">
          <h2 class="seccion-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" class="seccion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Historial de Cambios
          </h2>

          <div class="historial-table-wrap">
            <table v-if="historial.length > 0" class="historial-table">
              <thead>
                <tr>
                  <th>Campo Modificado</th>
                  <th>Valor Anterior</th>
                  <th>Valor Nuevo</th>
                  <th>Fecha de Modificación</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, i) in historial" :key="i">
                  <td class="campo-modificado">{{ item.campo }}</td>
                  <td class="valor-anterior">{{ item.valor_anterior || '—' }}</td>
                  <td class="valor-nuevo">{{ item.valor_nuevo || '—' }}</td>
                  <td class="fecha-mod">{{ formatFechaHora(item.fecha) }}</td>
                </tr>
              </tbody>
            </table>
            <div v-else class="historial-vacio">
              <svg xmlns="http://www.w3.org/2000/svg" class="icono-hist-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              <p>No hay cambios registrados aún.</p>
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="!cargando" class="estado-vacio">
        <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        <h3>Persona no encontrada</h3>
        <button class="btn-volver" @click="router.push('/personas')">Volver al catálogo</button>
      </div>

      <footer class="footer-institucional">
        <span>Sistema Integral de Control Escolar — TecNM</span>
        <span>© {{ anioActual }}</span>
      </footer>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`

const router     = useRouter()
const route      = useRoute()
const anioActual = new Date().getFullYear()

const persona  = ref({})
const historial = ref([])
const cargando  = ref(false)

const nombreCompleto = computed(() => {
  const p = persona.value
  return [p.apellido_paterno, p.apellido_materno, p.nombre].filter(Boolean).join(' ')
})

const iniciales = computed(() => {
  const p = persona.value
  const a = (p.apellido_paterno || '').charAt(0)
  const n = (p.nombre || '').charAt(0)
  return (a + n).toUpperCase() || '?'
})

const claseTipo = (tipo) => {
  if (!tipo || tipo === 'Sin asignar') return 'sin-asignar'
  return tipo.toLowerCase()
}

const formatFecha = (fecha) => {
  if (!fecha) return '—'
  const d = new Date(fecha + 'T00:00:00')
  return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const formatFechaHora = (fecha) => {
  if (!fecha) return '—'
  return new Date(fecha).toLocaleString('es-MX', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

onMounted(async () => {
  cargando.value = true
  try {
    const res = await fetch(`${API}/personas/${route.params.id}`)
    const data = await res.json()
    persona.value = data

    // Historial (endpoint opcional)
    try {
      const resH = await fetch(`${API}/personas/${route.params.id}/historial`)
      const dataH = await resH.json()
      historial.value = Array.isArray(dataH) ? dataH : []
    } catch { historial.value = [] }

  } catch (e) {
    console.error(e)
  } finally {
    cargando.value = false
  }
})

const editarPersona    = () => router.push(`/personas/editar/${route.params.id}`)
const asignarComoAlumno   = () => router.push(`/formulario-alumno?persona_id=${route.params.id}`)
const asignarComoEmpleado = () => router.push(`/recursos-humanos/empleados/nuevo?persona_id=${route.params.id}`)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.detalle-page {
  --azul:       #1B396A;
  --azul-hover: #1D4ED8;
  --borde:      #E5E7EB;
  --fondo:      #F5F5F5;
  --texto:      #1A1A1A;
  --gris:       #6B7280;
  --verde:      #16A34A;

  max-width: 100%;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
}

.breadcrumb { display: flex; align-items: center; gap: 6px; color: var(--gris); font-size: 0.88rem; margin-bottom: 1.2rem; }
.breadcrumb-link { cursor: pointer; color: var(--azul); font-weight: 500; transition: color 0.15s; }
.breadcrumb-link:hover { color: var(--azul-hover); text-decoration: underline; }
.breadcrumb-sep { color: #9CA3AF; }
.breadcrumb-actual { color: var(--gris); font-weight: 600; }

.barra-carga-global {
  height: 3px; background: transparent; border-radius: 2px; margin-bottom: 1rem; overflow: hidden; opacity: 0; transition: opacity 0.3s;
}
.barra-carga-global.visible { opacity: 1; }
.barra-progreso { height: 100%; width: 40%; background: #1B396A; border-radius: 2px; animation: deslizar 1.2s ease-in-out infinite; }
@keyframes deslizar { 0% { transform: translateX(-100%); } 100% { transform: translateX(350%); } }

.estado-cargando { text-align: center; padding: 3.5rem; color: var(--gris); }
.spinner-tabla {
  display: inline-block; width: 36px; height: 36px; border: 3px solid #E5E7EB;
  border-top-color: #1B396A; border-radius: 50%; animation: girar 0.8s linear infinite; margin-bottom: 12px;
}

/* Card persona */
.card-persona {
  background: #FFF; border-radius: 14px; border: 1px solid var(--borde);
  box-shadow: 0 4px 16px rgba(0,0,0,0.07); margin-bottom: 1.5rem; overflow: hidden;
}
.card-header {
  background: linear-gradient(135deg, #1B396A 0%, #1D4ED8 100%);
  padding: 1.8rem 2rem; display: flex; align-items: center; gap: 1.4rem; position: relative;
}
.avatar-circulo {
  width: 68px; height: 68px; border-radius: 50%; background: rgba(255,255,255,0.2);
  border: 3px solid rgba(255,255,255,0.4); display: flex; align-items: center; justify-content: center;
  font-size: 1.6rem; font-weight: 700; color: white; flex-shrink: 0; letter-spacing: 0.02em;
}
.card-header-info { flex: 1; }
.nombre-completo { color: white; font-size: 1.5rem; font-weight: 700; margin: 0 0 0.25rem; letter-spacing: -0.01em; }
.curp-display { color: rgba(255,255,255,0.75); font-family: monospace; font-size: 0.95rem; letter-spacing: 0.06em; margin: 0 0 0.5rem; }

.tipo-badge {
  display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.83rem; font-weight: 600;
}
.tipo-badge.alumno      { background: #DBEAFE; color: #1B396A; }
.tipo-badge.empleado    { background: #DCFCE7; color: #16A34A; }
.tipo-badge.sin-asignar { background: rgba(255,255,255,0.2); color: white; }

.btn-editar-persona {
  display: flex; align-items: center; gap: 7px; padding: 9px 18px;
  background: rgba(255,255,255,0.15); color: white; border: 1.5px solid rgba(255,255,255,0.4);
  border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.9rem;
  font-family: 'Montserrat', sans-serif; transition: background 0.2s; white-space: nowrap;
}
.btn-editar-persona:hover { background: rgba(255,255,255,0.25); }
.btn-editar-persona svg { width: 16px; height: 16px; }

.card-body { padding: 1.6rem 2rem; }
.grid-datos { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem 1.5rem; }
.dato-item { display: flex; flex-direction: column; gap: 3px; }
.dato-item-full { grid-column: 1 / -1; }
.dato-label { font-size: 0.78rem; font-weight: 600; color: var(--gris); text-transform: uppercase; letter-spacing: 0.05em; }
.dato-valor { font-size: 0.95rem; font-weight: 500; color: var(--texto); }

/* Secciones comunes */
.seccion-vinculos, .seccion-historial {
  background: #FFF; border-radius: 14px; border: 1px solid var(--borde);
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 1.5rem; padding: 1.6rem 2rem;
}
.seccion-titulo {
  display: flex; align-items: center; gap: 8px; color: var(--azul); font-size: 1.05rem;
  font-weight: 700; margin: 0 0 1.2rem; padding-bottom: 0.6rem; border-bottom: 2px solid var(--borde);
}
.seccion-icono { width: 20px; height: 20px; stroke: var(--azul); flex-shrink: 0; }

/* Vínculos */
.vinculos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; }
.vinculo-card {
  border-radius: 10px; border: 1px solid var(--borde); padding: 1.2rem 1.4rem;
  display: flex; align-items: center; gap: 1rem;
}
.vinculo-icono-wrap {
  width: 46px; height: 46px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.vinculo-icono-wrap svg { width: 22px; height: 22px; }
.vinculo-icono-wrap.alumno   { background: #DBEAFE; }
.vinculo-icono-wrap.alumno svg   { stroke: #1B396A; }
.vinculo-icono-wrap.empleado { background: #DCFCE7; }
.vinculo-icono-wrap.empleado svg { stroke: #16A34A; }
.vinculo-icono-wrap.usuario  { background: #F3F4F6; }
.vinculo-icono-wrap.usuario svg  { stroke: #6B7280; }

.vinculo-info { flex: 1; }
.vinculo-tipo   { font-size: 0.88rem; font-weight: 600; color: var(--texto); margin: 0; }
.vinculo-detalle { font-size: 0.82rem; color: var(--gris); margin: 2px 0 0; font-family: monospace; }

.btn-vinculo {
  display: flex; align-items: center; gap: 4px; padding: 7px 14px; border-radius: 7px;
  font-weight: 600; font-size: 0.83rem; cursor: pointer; white-space: nowrap;
  font-family: 'Montserrat', sans-serif; transition: background 0.15s;
  background: #DBEAFE; color: #1B396A; border: none;
}
.btn-vinculo:hover { background: #BFDBFE; }
.btn-vinculo svg { width: 14px; height: 14px; stroke: #1B396A; }
.btn-vinculo-verde { background: #DCFCE7; color: #16A34A; }
.btn-vinculo-verde:hover { background: #BBF7D0; }
.btn-vinculo-verde svg { stroke: #16A34A; }
.btn-vinculo-gris { background: #F3F4F6; color: #6B7280; }
.btn-vinculo-gris:hover { background: #E5E7EB; }
.btn-vinculo-gris svg { stroke: #6B7280; }

/* Sin vínculo */
.vinculo-sin-asignar { grid-column: 1 / -1; justify-content: flex-start; }
.sin-vinculo-content { width: 100%; }
.badge-sin-asignar {
  display: inline-block; background: #F3F4F6; color: #6B7280; padding: 4px 12px;
  border-radius: 20px; font-size: 0.83rem; font-weight: 600; margin-bottom: 0.6rem;
}
.sin-vinculo-msg { font-size: 0.93rem; color: var(--gris); margin: 0 0 1rem; }
.sin-vinculo-acciones { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.btn-asignar-alumno, .btn-asignar-empleado {
  display: flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: 8px;
  font-weight: 600; font-size: 0.9rem; cursor: pointer; font-family: 'Montserrat', sans-serif;
  transition: background 0.15s;
}
.btn-asignar-alumno svg, .btn-asignar-empleado svg { width: 16px; height: 16px; }
.btn-asignar-alumno {
  background: #DBEAFE; color: #1B396A; border: 1px solid #BFDBFE;
}
.btn-asignar-alumno:hover { background: #BFDBFE; }
.btn-asignar-empleado {
  background: #DCFCE7; color: #16A34A; border: 1px solid #BBF7D0;
}
.btn-asignar-empleado:hover { background: #BBF7D0; }

/* Historial */
.historial-table-wrap { overflow-x: auto; }
.historial-table { width: 100%; border-collapse: collapse; }
.historial-table th {
  background: var(--fondo); padding: 10px 14px; text-align: left; font-weight: 600;
  font-size: 0.84rem; color: var(--texto); border-bottom: 1px solid var(--borde);
  font-family: 'Montserrat', sans-serif; white-space: nowrap;
}
.historial-table td {
  padding: 10px 14px; border-bottom: 1px solid var(--borde); font-size: 0.88rem;
  font-family: 'Montserrat', sans-serif; color: var(--texto);
}
.historial-table tbody tr:last-child td { border-bottom: none; }
.historial-table tbody tr:hover { background: #F8FAFC; }
.campo-modificado { font-weight: 600; color: #1B396A; }
.valor-anterior   { color: #9CA3AF; text-decoration: line-through; }
.valor-nuevo      { color: #16A34A; font-weight: 500; }
.fecha-mod        { color: var(--gris); font-size: 0.83rem; white-space: nowrap; }
.historial-vacio  { text-align: center; padding: 2rem; color: var(--gris); }
.icono-hist-vacio { width: 40px; height: 40px; stroke: #9CA3AF; margin-bottom: 8px; }
.historial-vacio p { font-size: 0.9rem; margin: 0; }

/* Estado vacío */
.estado-vacio { text-align: center; padding: 3.5rem 2rem; color: var(--gris); }
.icono-vacio { width: 56px; height: 56px; stroke: #9CA3AF; margin-bottom: 12px; }
.estado-vacio h3 { font-size: 1.2rem; color: var(--texto); margin: 0 0 1rem; }
.btn-volver {
  background: #1B396A; color: white; border: none; padding: 10px 22px;
  border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif;
}

/* Footer */
.footer-institucional {
  margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--borde);
  display: flex; justify-content: space-between; align-items: center;
  font-size: 0.82rem; color: #9CA3AF; font-family: 'Montserrat', sans-serif;
}

@keyframes girar { to { transform: rotate(360deg); } }
</style>