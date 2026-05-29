<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="dashboard-page">

      <!-- ── Barra de carga top ── -->
      <div class="barra-carga" :class="{ activa: cargando }" aria-hidden="true">
        <div class="barra-progreso"></div>
      </div>

      <!-- ── Encabezado ── -->
      <div class="inicio-header">
        <div class="header-texto">
          <h1 class="page-title">Inicio</h1>
          <p class="welcome-text">
            Bienvenido al Sistema Integral de Control Escolar
          </p>
        </div>
        <time class="fecha-actual" :datetime="fechaISO">{{ fechaHoy }}</time>
      </div>

      <!-- ── Búsqueda rápida de alumno ── -->
      <div class="busqueda-rapida-card" role="search" aria-label="Búsqueda rápida de alumno">
        <div class="busqueda-rapida-texto">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          <div>
            <p class="busqueda-label">Consulta rápida de alumno</p>
            <p class="busqueda-sub">Ingresa el número de control para ver toda la información</p>
          </div>
        </div>
        <div class="busqueda-rapida-form">
          <div class="busqueda-input-wrap">
            <svg class="busqueda-icono-input" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="17" height="17" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
              type="text"
              v-model.trim="busquedaControl"
              class="busqueda-input"
              placeholder="Ej. 25000001"
              maxlength="8"
              inputmode="numeric"
              aria-label="Número de control del alumno"
              @keydown.enter="irAKardex"
              @input="busquedaControl = busquedaControl.replace(/\D/g, '')"
            />
          </div>
          <button
            class="btn-buscar"
            @click="irAKardex"
            :disabled="busquedaControl.length < 8"
            type="button"
            aria-label="Buscar alumno"
          >
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Buscar
          </button>
        </div>
      </div>

      <!-- ── Alerta de error general ── -->
      <transition name="fade">
        <div v-if="error" class="alerta-error" role="alert">
          <svg class="alerta-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ error }}
        </div>
      </transition>

      <!-- ── Gráficas ── -->
      <div class="fila-graficas">
        <!-- Alumnos por carrera -->
        <div class="grafica-card">
          <h3 class="grafica-titulo">Alumnos por Carrera</h3>
          <div v-if="carreraData.length > 0" class="grafica-barras">
            <div v-for="(item, i) in carreraData" :key="i" class="barra-item">
              <div class="barra-etiqueta" :title="item.carrera">{{ item.carrera }}</div>
              <div class="barra-contenedor" role="progressbar" :aria-valuenow="item.total" :aria-label="item.carrera">
                <div class="barra-relleno" :style="{ width: item.porcentaje + '%' }"></div>
              </div>
              <div class="barra-valor">{{ item.total }}</div>
            </div>
          </div>
          <div v-else class="estado-vacio-grafica" aria-live="polite">
            <p>Sin datos disponibles</p>
          </div>
        </div>

        <!-- Alumnos por semestre -->
        <div class="grafica-card">
          <h3 class="grafica-titulo">Alumnos por Semestre</h3>
          <div v-if="semestreData.length > 0" class="grafica-barras">
            <div v-for="(item, i) in semestreData" :key="i" class="barra-item">
              <div class="barra-etiqueta">{{ item.semestre }}°</div>
              <div class="barra-contenedor" role="progressbar" :aria-valuenow="item.cantidad" :aria-label="`${item.semestre}° semestre`">
                <div class="barra-relleno barra-acento" :style="{ width: calcularPorcentajeSemestre(item.cantidad) + '%' }"></div>
              </div>
              <div class="barra-valor">{{ item.cantidad }}</div>
            </div>
          </div>
          <div v-else class="estado-vacio-grafica">
            <p>Sin datos disponibles</p>
          </div>
        </div>
      </div>

      <!-- ── Fila inferior ── -->
      <div class="fila-inferior">

        <!-- Actividad reciente / Bitácora -->
        <div class="panel-card">
          <div class="bitacora-header">
            <h3 class="panel-titulo">Actividad Reciente</h3>
            <router-link to="/bitacora" class="btn-ver-bitacora" aria-label="Ver bitácora completa">
              Ver todo →
            </router-link>
          </div>

          <!-- Estado: cargando bitácora -->
          <div v-if="cargandoBitacora" class="bitacora-cargando" aria-busy="true">
            <div class="spinner-bitacora" aria-hidden="true"></div>
            <span>Cargando actividad...</span>
          </div>

          <!-- Estado: error de bitácora (placeholder) -->
          <div v-else-if="errorBitacora" class="lista-bitacora">
            <div v-for="i in 3" :key="'ph-'+i" class="item-bitacora item-placeholder">
              <div class="avatar-bitacora avatar-gris" aria-hidden="true">?</div>
              <div class="info-bitacora">
                <p class="bitacora-desc" style="color:#9CA3AF">Esperando conexión con el servidor...</p>
              </div>
            </div>
          </div>

          <!-- Estado: sin actividad -->
          <div v-else-if="bitacoraReciente.length === 0" class="estado-vacio-grafica" aria-live="polite">
            <p>Sin actividad reciente</p>
          </div>

          <!-- Lista de actividad — click abre modal -->
          <div v-else class="lista-bitacora" role="list">
            <div
              v-for="(item, i) in bitacoraReciente"
              :key="item.id_bitacora || i"
              class="item-bitacora item-bitacora-clickeable"
              role="listitem"
              @click="abrirModalBitacora(item)"
            >
              <div class="avatar-bitacora" aria-hidden="true">
                {{ item.usuario ? item.usuario.charAt(0).toUpperCase() : '?' }}
              </div>
              <div class="info-bitacora">
                <div class="bitacora-fila-superior">
                  <span class="bitacora-usuario">{{ item.usuario }}</span>
                  <span class="accion-badge-mini" :class="claseAccion(item.accion)">{{ item.accion }}</span>
                </div>
                <p class="bitacora-desc">{{ item.descripcion }}</p>
                <div class="bitacora-fila-inferior">
                  <span class="bitacora-modulo">{{ item.modulo }}</span>
                  <time class="bitacora-tiempo" :datetime="item.fecha_hora">{{ tiempoRelativo(item.fecha_hora) }}</time>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Acciones rápidas — click abre modal de confirmación -->
        <div class="panel-card">
          <h3 class="panel-titulo">Acciones Rápidas</h3>
          <div class="grilla-acciones" role="list">
            <button
              v-for="accion in accionesRapidas"
              :key="accion.label"
              class="btn-accion"
              :class="{ 'btn-primario-accion': accion.primario }"
              @click="abrirModalAccion(accion)"
              type="button"
              role="listitem"
              :aria-label="accion.label"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="accion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path :d="accion.iconPath" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
              </svg>
              {{ accion.label }}
            </button>
          </div>
        </div>

      </div><!-- /fila-inferior -->

      <!-- ── Modal detalle de registro de bitácora ── -->
      <Teleport to="body">
        <Transition name="fade">
          <div v-if="modalBitacora" class="modal-overlay" @click.self="cerrarModalBitacora">
            <div class="modal-caja">
              <div class="modal-header">
                <h3 class="modal-titulo">Detalle de actividad</h3>
                <button class="modal-cerrar" @click="cerrarModalBitacora" aria-label="Cerrar">✕</button>
              </div>
              <div class="modal-body">
                <div class="modal-fila">
                  <span class="modal-etiqueta">Usuario</span>
                  <span class="modal-valor">{{ modalBitacora.usuario || '—' }}</span>
                </div>
                <div class="modal-fila">
                  <span class="modal-etiqueta">Acción</span>
                  <span class="accion-badge-mini" :class="claseAccion(modalBitacora.accion)">{{ modalBitacora.accion }}</span>
                </div>
                <div class="modal-fila">
                  <span class="modal-etiqueta">Módulo</span>
                  <span class="modal-valor">{{ modalBitacora.modulo || '—' }}</span>
                </div>
                <div class="modal-fila">
                  <span class="modal-etiqueta">Descripción</span>
                  <span class="modal-valor">{{ modalBitacora.descripcion || '—' }}</span>
                </div>
                <div class="modal-fila">
                  <span class="modal-etiqueta">Fecha y hora</span>
                  <span class="modal-valor">{{ formatearFecha(modalBitacora.fecha_hora) }}</span>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn-modal-secundario" @click="cerrarModalBitacora">Cerrar</button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>

      <!-- ── Modal confirmación de acción rápida ── -->
      <Teleport to="body">
        <Transition name="fade">
          <div v-if="modalAccion" class="modal-overlay" @click.self="cerrarModalAccion">
            <div class="modal-caja">
              <div class="modal-header">
                <h3 class="modal-titulo">{{ modalAccion.label }}</h3>
                <button class="modal-cerrar" @click="cerrarModalAccion" aria-label="Cerrar">✕</button>
              </div>
              <div class="modal-body">
                <p class="modal-descripcion">{{ modalAccion.descripcion }}</p>
              </div>
              <div class="modal-footer">
                <button class="btn-modal-secundario" @click="cerrarModalAccion">Cancelar</button>
                <button class="btn-modal-primario" @click="ejecutarAccion(modalAccion)">
                  Ir ahora →
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const router  = useRouter()
const API_URL = import.meta.env.VITE_API_URL

// ── Recibir búsqueda global del layout ───────────────────────────────────
const props = defineProps({
  busquedaGlobal: {
    type: String,
    default: ''
  }
})

// ── Estado general ──────────────────────────────────────────────────────
const cargando = ref(true)
const error    = ref(null)

// ── Búsqueda rápida de alumno ───────────────────────────────────────────
const busquedaControl = ref('')

const irAKardex = () => {
  const nc = busquedaControl.value.trim()
  if (nc.length < 8) return
  router.push(`/kardex/${nc}`)
}

// ── Sincronizar búsqueda global con el componente (si se necesita) ──────
// Por ahora no hacemos nada con busquedaGlobal, pero está disponible
watch(() => props.busquedaGlobal, (nuevaBusqueda) => {
  // Aquí puedes agregar lógica si quieres que la búsqueda global afecte al dashboard
  // Por ejemplo, filtrar algo en el dashboard si es necesario
  console.log('[Dashboard] Búsqueda global:', nuevaBusqueda)
})

// ── Fechas ──────────────────────────────────────────────────────────────
const fechaHoy = computed(() =>
  new Date().toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
)
const fechaISO = computed(() => new Date().toISOString().split('T')[0])

// ── Datos de gráficas (fallback si backend no responde) ─────────────────
const carreraDataDefault = [
  { carrera: 'Sist. Computacional', total: 312, porcentaje: 100 },
  { carrera: 'Industrial',          total: 268, porcentaje: 86  },
  { carrera: 'Eléctrica',           total: 198, porcentaje: 63  },
  { carrera: 'Mecánica',            total: 174, porcentaje: 56  },
  { carrera: 'Gest. Empresarial',   total: 156, porcentaje: 50  },
  { carrera: 'Bioquímica',          total: 176, porcentaje: 56  }
]
const semestreDataDefault = [
  { semestre: '1', cantidad: 180 },
  { semestre: '2', cantidad: 165 },
  { semestre: '3', cantidad: 158 },
  { semestre: '4', cantidad: 144 },
  { semestre: '5', cantidad: 152 },
  { semestre: '6', cantidad: 138 },
  { semestre: '7', cantidad: 175 },
  { semestre: '8', cantidad: 172 }
]

const carreraData  = ref([...carreraDataDefault])
const semestreData = ref([...semestreDataDefault])

const calcularPorcentajeSemestre = (cant) => {
  const max = Math.max(...semestreData.value.map(s => s.cantidad), 1)
  return Math.round((cant / max) * 100)
}

// ── Bitácora reciente ────────────────────────────────────────────────────
const bitacoraReciente  = ref([])
const cargandoBitacora  = ref(false)
const errorBitacora     = ref(false)

const cargarBitacoraReciente = async () => {
  cargandoBitacora.value = true
  errorBitacora.value    = false
  try {
    const res  = await fetch(`${API_URL}/api/bitacora?limit=5`)
    if (!res.ok) throw new Error()
    const data = await res.json()
    bitacoraReciente.value = Array.isArray(data) ? data : (data.bitacora || [])
  } catch {
    errorBitacora.value = true
  } finally {
    cargandoBitacora.value = false
  }
}

const tiempoRelativo = (fechaStr) => {
  if (!fechaStr) return ''
  const diff = Date.now() - new Date(fechaStr).getTime()
  const min  = Math.floor(diff / 60000)
  if (min < 1)   return 'Ahora'
  if (min < 60)  return `Hace ${min} min`
  const hrs = Math.floor(min / 60)
  if (hrs < 24)  return `Hace ${hrs} h`
  const dias = Math.floor(hrs / 24)
  return `Hace ${dias} día${dias > 1 ? 's' : ''}`
}

const claseAccion = (accion = '') => {
  const a = accion.toLowerCase()
  if (a.includes('login') || a.includes('acceso'))  return 'accion-login'
  if (a.includes('cre') || a.includes('registr'))   return 'accion-creacion'
  if (a.includes('edit') || a.includes('actualiz')) return 'accion-edicion'
  if (a.includes('elim') || a.includes('bor'))      return 'accion-eliminacion'
  return 'accion-default'
}

// ── Modal de bitácora ────────────────────────────────────────────────────
const modalBitacora = ref(null)

const abrirModalBitacora = (item) => {
  modalBitacora.value = item
}
const cerrarModalBitacora = () => {
  modalBitacora.value = null
}
const formatearFecha = (fechaStr) => {
  if (!fechaStr) return '—'
  return new Date(fechaStr).toLocaleString('es-MX', {
    year: 'numeric', month: 'long', day: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

// ── Modal de acción rápida ───────────────────────────────────────────────
const modalAccion = ref(null)

const abrirModalAccion = (accion) => {
  modalAccion.value = accion
}
const cerrarModalAccion = () => {
  modalAccion.value = null
}
const ejecutarAccion = (accion) => {
  cerrarModalAccion()
  accion.handler()
}

// ── Carga de datos desde el backend ─────────────────────────────────────
const cargarKPIs = async () => {
  cargando.value = true
  error.value    = null
  try {
    const res  = await fetch(`${API_URL}/api/dashboard/kpis`)
    if (!res.ok) throw new Error('Error al cargar datos')
    const data = await res.json()
    if (data.carrera_data)  carreraData.value  = data.carrera_data
    if (data.semestre_data) semestreData.value = data.semestre_data
  } catch (err) {
    console.error('[Dashboard] Error:', err)
  } finally {
    cargando.value = false
  }
}

// ── Acciones rápidas (ACTUALIZADO con Lista de Carreras) ─────────────────
const accionesRapidas = [
  {
    label:       'Nueva inscripción',
    descripcion: 'Registrar un nuevo alumno en el sistema.',
    primario:    true,
    iconPath:    'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
    handler:     () => router.push('/formulario-alumno')
  },
  {
    label:       'Lista de alumnos',
    descripcion: 'Ver el listado completo de alumnos registrados.',
    iconPath:    'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    handler:     () => router.push('/alumnos')
  },
  {
    label:       'Lista de Carreras',
    descripcion: 'Ver y gestionar todas las carreras del sistema.',
    iconPath:    'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z',
    handler:     () => router.push('/gestion-academica/carreras')
  },
  {
    label:       'Gestión de grupos',
    descripcion: 'Administrar grupos y horarios del periodo actual.',
    iconPath:    'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
    handler:     () => router.push('/gestion-grupos')
  },
  {
    label:       'Cargar calificaciones',
    descripcion: 'Registrar o actualizar calificaciones de alumnos.',
    iconPath:    'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    handler:     () => router.push('/calificaciones')
  }
]

// ── Lifecycle ────────────────────────────────────────────────────────────
onMounted(() => {
  cargarKPIs()
  cargarBitacoraReciente()
})
</script>

<style scoped>
/* ══════════════════════════════════════════════════════
   VARIABLES — paleta SICE
══════════════════════════════════════════════════════ */
.dashboard-page {
  --azul:       #1B396A;
  --azul-hover: #15305A;
  --azul-suave: #DBEAFE;
  --verde:      #16A34A;
  --rojo:       #DC2626;
  --amarillo:   #F59E0B;
  --borde:      #E5E7EB;
  --fondo:      #F9FAFB;
  --texto:      #111827;
  --gris:       #6B7280;
  --radio:      12px;

  font-family: 'Montserrat', sans-serif;
  padding-bottom: 2rem;
}

/* ── Barra de carga ── */
.barra-carga { position:fixed; top:0; left:0; right:0; height:3px; z-index:9999; opacity:0; transition:opacity 0.2s; pointer-events:none; }
.barra-carga.activa { opacity:1; }
.barra-progreso { height:100%; background:#1B396A; animation:progreso-carga 1.5s ease-in-out infinite; }
@keyframes progreso-carga { 0%{width:0%;opacity:1} 70%{width:85%;opacity:1} 100%{width:100%;opacity:0} }

/* ── Encabezado ── */
.inicio-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.4rem;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.page-title { font-size: 1.75rem; font-weight: 700; color: #111827; margin: 0 0 4px; }
.welcome-text { font-size: 0.9rem; color: #6B7280; margin: 0; }
.fecha-actual {
  font-size: 0.85rem;
  color: #6B7280;
  font-weight: 500;
  text-transform: capitalize;
  white-space: nowrap;
  background: #F9FAFB;
  padding: 6px 14px;
  border-radius: 20px;
  border: 1px solid #E5E7EB;
}

/* ── Búsqueda rápida ── */
.busqueda-rapida-card {
  background: linear-gradient(135deg, #1B396A 0%, #1D4ED8 100%);
  border-radius: 12px;
  padding: 1.2rem 1.6rem;
  margin-bottom: 1.4rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
  box-shadow: 0 6px 20px rgba(27,57,106,0.25);
}
.busqueda-rapida-texto {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
  min-width: 200px;
}
.busqueda-rapida-texto svg { stroke: rgba(255,255,255,0.8); flex-shrink: 0; }
.busqueda-label { font-size: 0.95rem; font-weight: 700; color: #FFFFFF; margin: 0 0 2px; }
.busqueda-sub   { font-size: 0.8rem; color: rgba(255,255,255,0.7); margin: 0; }
.busqueda-rapida-form { display: flex; gap: 8px; align-items: center; }
.busqueda-input-wrap { position: relative; display: flex; align-items: center; }
.busqueda-icono-input { position: absolute; left: 12px; stroke: #9CA3AF; pointer-events: none; }
.busqueda-input {
  padding: 10px 14px 10px 38px;
  border-radius: 8px;
  border: 1.5px solid rgba(255,255,255,0.3);
  background: rgba(255,255,255,0.12);
  color: white;
  font-family: 'Montserrat', monospace;
  font-size: 0.9rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  width: 180px;
  outline: none;
  transition: border-color 0.2s, background 0.2s;
  backdrop-filter: blur(4px);
}
.busqueda-input::placeholder { color: rgba(255,255,255,0.5); font-weight: 400; letter-spacing: 0; }
.busqueda-input:focus { border-color: rgba(255,255,255,0.7); background: rgba(255,255,255,0.18); }
.btn-buscar {
  display: flex; align-items: center; gap: 6px;
  padding: 10px 18px;
  background: #FFFFFF; color: #1B396A;
  border: none; border-radius: 8px;
  font-weight: 700; font-size: 0.875rem;
  cursor: pointer; font-family: inherit;
  transition: background 0.15s, transform 0.1s;
  white-space: nowrap;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
.btn-buscar:hover:not(:disabled) { background: #F0F7FF; transform: translateY(-1px); }
.btn-buscar:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
.btn-buscar svg { stroke: #1B396A; }

/* ── Alerta de error ── */
.alerta-error {
  display: flex; align-items: center; gap: 10px;
  background: #FEF2F2; border: 1px solid #FECACA;
  border-radius: 12px; padding: 12px 16px;
  margin-bottom: 1.2rem; font-size: 0.875rem;
  color: #DC2626; font-weight: 500;
}
.alerta-icono { width: 20px; height: 20px; flex-shrink: 0; stroke: #DC2626; }

/* ── Gráficas ── */
.fila-graficas { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
.grafica-card {
  background: #FFFFFF; border-radius: 12px;
  border: 1px solid #E5E7EB; padding: 1.4rem 1.6rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.grafica-titulo { font-size: 0.95rem; font-weight: 700; color: #111827; margin: 0 0 1.2rem; }
.grafica-barras { display: flex; flex-direction: column; gap: 10px; }
.barra-item { display: flex; align-items: center; gap: 10px; }
.barra-etiqueta { width: 130px; font-size: 0.8rem; color: #6B7280; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex-shrink: 0; }
.barra-contenedor { flex: 1; background: #F9FAFB; border-radius: 4px; height: 8px; overflow: hidden; }
.barra-relleno { height: 100%; background: #1B396A; border-radius: 4px; transition: width 0.6s ease; }
.barra-acento  { background: #16A34A; }
.barra-valor   { font-size: 0.8rem; font-weight: 700; color: #111827; min-width: 30px; text-align: right; flex-shrink: 0; }
.estado-vacio-grafica { display: flex; align-items: center; justify-content: center; padding: 2rem; color: #6B7280; font-size: 0.875rem; }

/* ── Fila inferior ── */
.fila-inferior { display: grid; grid-template-columns: 1fr 380px; gap: 1rem; }
.panel-card {
  background: #FFFFFF; border-radius: 12px;
  border: 1px solid #E5E7EB; padding: 1.4rem 1.6rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.panel-titulo { font-size: 0.95rem; font-weight: 700; color: #111827; margin: 0; }

/* ── Bitácora ── */
.bitacora-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.btn-ver-bitacora { font-size: 0.82rem; font-weight: 600; color: #1B396A; text-decoration: none; transition: color 0.15s; }
.btn-ver-bitacora:hover { color: #1D4ED8; }

.bitacora-cargando { display: flex; align-items: center; gap: 10px; padding: 1.5rem 0; color: #6B7280; font-size: 0.875rem; }
.spinner-bitacora { width: 18px; height: 18px; border: 2px solid #E5E7EB; border-top-color: #1B396A; border-radius: 50%; animation: girar 0.75s linear infinite; flex-shrink: 0; }
@keyframes girar { to { transform: rotate(360deg); } }

.lista-bitacora { display: flex; flex-direction: column; gap: 2px; }
.item-bitacora { display: flex; gap: 10px; align-items: flex-start; padding: 8px; border-radius: 8px; transition: background 0.15s; }
.item-bitacora-clickeable { cursor: pointer; }
.item-bitacora-clickeable:hover { background: #DBEAFE !important; }
.item-placeholder { opacity: 0.5; }
.avatar-bitacora { width: 32px; height: 32px; border-radius: 50%; background: #1B396A; color: white; font-size: 0.8rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.avatar-gris { background: #9CA3AF; }
.info-bitacora { flex: 1; min-width: 0; }
.bitacora-fila-superior { display: flex; align-items: center; gap: 6px; margin-bottom: 2px; flex-wrap: wrap; }
.bitacora-usuario { font-weight: 600; font-size: 0.85rem; color: #111827; }
.bitacora-desc { margin: 0 0 3px; font-size: 0.8rem; color: #6B7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.bitacora-fila-inferior { display: flex; align-items: center; justify-content: space-between; gap: 6px; }
.bitacora-modulo { font-size: 0.75rem; color: #9CA3AF; font-weight: 500; }
.bitacora-tiempo { font-size: 0.75rem; color: #9CA3AF; white-space: nowrap; }
.accion-badge-mini { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; white-space: nowrap; flex-shrink: 0; }
.accion-login       { background: #DBEAFE; color: #1B396A; }
.accion-creacion    { background: #DCFCE7; color: #16A34A; }
.accion-edicion     { background: #FEF3C7; color: #F59E0B; }
.accion-eliminacion { background: #FEF2F2; color: #DC2626; }
.accion-default     { background: #F9FAFB; color: #6B7280; }

/* ── Acciones rápidas ── */
.grilla-acciones { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 1rem; }
.btn-accion {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 8px; padding: 1rem; min-height: 80px;
  background: #F9FAFB; border: 1.5px solid #E5E7EB;
  border-radius: 12px; font-size: 0.82rem; font-weight: 600;
  color: #111827; cursor: pointer; font-family: inherit;
  transition: all 0.2s; text-align: center;
}
.btn-accion:hover { background: #DBEAFE; border-color: #1B396A; color: #1B396A; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(27,57,106,0.1); }
.btn-accion.btn-primario-accion { background: #1B396A; color: white; border-color: #1B396A; }
.btn-accion.btn-primario-accion:hover { background: #15305A; }
.accion-icono { width: 22px; height: 22px; stroke: currentColor; }

/* ══ MODALES (con valores hexadecimales directos) ══ */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.45);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}
.modal-caja {
  background: #FFFFFF;
  border-radius: 14px;
  width: 100%;
  max-width: 480px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
  overflow: hidden;
}
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.2rem 1.5rem;
  border-bottom: 1px solid #E5E7EB;
  background: #F8FAFC;
}
.modal-titulo {
  font-size: 1rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
}
.modal-cerrar {
  background: none;
  border: none;
  font-size: 1.1rem;
  cursor: pointer;
  color: #6B7280;
  padding: 4px 8px;
  border-radius: 6px;
  transition: background 0.15s;
  min-height: unset;
  line-height: 1;
}
.modal-cerrar:hover { background: #F1F5F9; }
.modal-body {
  padding: 1.4rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.modal-descripcion {
  color: #6B7280;
  font-size: 0.9rem;
  margin: 0;
  line-height: 1.6;
}
.modal-fila {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.modal-etiqueta {
  font-size: 0.75rem;
  font-weight: 600;
  color: #6B7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.modal-valor {
  font-size: 0.9rem;
  color: #111827;
  font-weight: 500;
}
.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #E5E7EB;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 0.75rem;
}

/* Botones del modal con valores directos */
.btn-modal-primario {
  padding: 8px 20px;
  background: #1B396A;
  color: #FFFFFF;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  transition: background 0.15s;
}
.btn-modal-primario:hover { background: #15305A; }

.btn-modal-secundario {
  padding: 8px 20px;
  background: #F1F5F9;
  color: #111827;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  transition: background 0.15s;
}
.btn-modal-secundario:hover { background: #E2E8F0; }

/* ── Transición fade ── */
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ══ RESPONSIVE ══ */

/* ── Tablet grande (≤1200px) ── */
@media (max-width: 1200px) {
  .fila-graficas { grid-template-columns: 1fr; }
}

/* ── Tablet (≤900px) ── */
@media (max-width: 900px) {
  .fila-graficas { grid-template-columns: 1fr; }
  .fila-inferior { grid-template-columns: 1fr; }
  .barra-etiqueta { width: 90px; }
}

/* ── Móvil grande (≤640px) ── */
@media (max-width: 640px) {
  .inicio-header { flex-direction: column; gap: 0.4rem; margin-bottom: 1rem; }
  .page-title { font-size: 1.35rem; }
  .welcome-text { font-size: 0.82rem; }
  .fecha-actual { font-size: 0.78rem; white-space: normal; align-self: flex-start; }

  .busqueda-rapida-card { flex-direction: column; align-items: flex-start; gap: 1rem; padding: 1rem 1.2rem; }
  .busqueda-rapida-form { width: 100%; flex-wrap: wrap; gap: 8px; }
  .busqueda-input-wrap { flex: 1; }
  .busqueda-input { width: 100%; font-size: 16px; }
  .btn-buscar { width: 100%; justify-content: center; }

  .grafica-card { padding: 1rem 1.2rem; }
  .barra-etiqueta { width: 80px; font-size: 0.75rem; }

  .grilla-acciones { grid-template-columns: 1fr 1fr; gap: 0.6rem; }
  .btn-accion { min-height: 72px; font-size: 0.78rem; padding: 0.8rem; }
  .accion-icono { width: 18px; height: 18px; }

  .panel-card { padding: 1rem 1.2rem; }
  .bitacora-desc {
    white-space: normal;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
}

/* ── Móvil pequeño (≤480px) ── */
@media (max-width: 480px) {
  .page-title { font-size: 1.2rem; }
  .grilla-acciones { grid-template-columns: 1fr; }
  .btn-accion { flex-direction: row; min-height: 52px; justify-content: flex-start; padding: 12px 14px; gap: 10px; }
  .busqueda-label { font-size: 0.88rem; }
  .busqueda-sub { display: none; }
  .bitacora-fila-inferior { flex-direction: column; align-items: flex-start; gap: 2px; }
  .modal-footer { flex-direction: column; gap: 0.5rem; }
  .btn-modal-secundario, .btn-modal-primario { width: 100%; justify-content: center; }
}
</style>