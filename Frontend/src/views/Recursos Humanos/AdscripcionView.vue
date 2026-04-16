<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="adscripcion-page">

      <nav class="breadcrumb">
        <router-link to="/inicio" class="breadcrumb-link">Inicio</router-link>
        <span class="sep">›</span>
        <router-link to="/recursos-humanos" class="breadcrumb-link">Recursos Humanos</router-link>
        <span class="sep">›</span>
        <span class="breadcrumb-actual">Adscripciones</span>
      </nav>

      <div class="page-header">
        <h1 class="page-title">Adscripciones</h1>
        <span class="page-subtitle">{{ adscripcionesFiltradas.length }} registro(s) encontrado(s)</span>
      </div>

      <div class="barra-carga-global" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <transition name="notif-fade">
        <div v-if="notificacion.visible" class="notificacion-ui" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo === 'exito'" xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- Filtros -->
      <div class="filters-bar">
        <div class="search-group">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Buscar por número de empleado o nombre..."
            v-model="busqueda"
            class="search-input"
            @keydown.enter="currentPage = 1"
            @keydown.escape="busqueda = ''"
          >
          <span v-if="cargandoBusqueda" class="spinner-busqueda"></span>
        </div>

        <select v-model="filtroDepartamento" class="filter-select" @change="currentPage = 1">
          <option value="">Departamento</option>
          <option v-for="d in departamentosDisponibles" :key="d" :value="d">{{ d }}</option>
        </select>

        <button class="btn-limpiar" @click="resetFilters">
          <svg xmlns="http://www.w3.org/2000/svg" class="reset-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Limpiar
        </button>
        <button class="btn-nuevo" @click="nuevaAdscripcion">+ Nueva Adscripción</button>
      </div>

      <!-- Tabla -->
      <div class="table-container">
        <div v-if="cargando && adscripciones.length === 0" class="estado-cargando">
          <div class="spinner-tabla"></div>
          <p>Cargando registros...</p>
        </div>

        <table v-else-if="paginatedAdscripciones.length > 0" class="adscripcion-table" ref="tablaRef" @keydown="navegarTeclado" tabindex="0">
          <thead>
            <tr>
              <th>Núm. Empleado</th>
              <th>Nombre del Empleado</th>
              <th>Departamento</th>
              <th>Fecha Inicio</th>
              <th>Fecha Fin</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(ads, index) in paginatedAdscripciones"
              :key="ads.id_adscripcion || ads.id"
              :class="{ 'fila-seleccionada': filaActiva === index }"
              @click="filaActiva = index"
            >
              <td class="celda-control">{{ ads.numero_empleado || '—' }}</td>
              <td>{{ ads.nombre_empleado }}</td>
              <td><span class="badge-departamento">{{ ads.departamento }}</span></td>
              <td>{{ formatearFecha(ads.fecha_inicio) }}</td>
              <td>
                <span v-if="ads.fecha_fin" class="fecha-fin">{{ formatearFecha(ads.fecha_fin) }}</span>
                <span v-else class="badge-vigente">Vigente</span>
              </td>
              <td class="celda-acciones">
                <button class="btn-accion ver" @click.stop="abrirModalVer(ads)">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Ver
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3>Sin resultados</h3>
          <p>No se encontraron adscripciones con los criterios aplicados.</p>
          <button class="btn-limpiar-vacio" @click="resetFilters">Limpiar filtros</button>
        </div>
      </div>

      <!-- Paginación -->
      <div class="paginacion">
        <div class="paginacion-izquierda">
          Filas por página:
          <select v-model="filasPorPagina" @change="currentPage = 1" class="select-filas">
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
        </div>
        <div class="paginacion-centro">Página {{ currentPage }} de {{ totalPages }}</div>
        <div class="paginacion-derecha">
          <button class="btn-pag" @click="prevPage" :disabled="currentPage === 1">‹</button>
          <button v-for="page in visiblePages" :key="page" class="btn-pag" :class="{ activo: page === currentPage }" @click="goToPage(page)">{{ page }}</button>
          <button class="btn-pag" @click="nextPage" :disabled="currentPage === totalPages">›</button>
        </div>
      </div>

    </div>

    <!-- Modal Ver -->
    <div v-if="showViewModal" class="modal-overlay" @click.self="cerrarModalVer">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Detalle de Adscripción</h3>
          <button @click="cerrarModalVer" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">
          <div class="detalle-fila"><span class="detalle-label">Núm. Empleado</span><span class="detalle-valor celda-control">{{ adscripcionVer.numero_empleado }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Empleado</span><span class="detalle-valor">{{ adscripcionVer.nombre_empleado }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Departamento</span><span class="badge-departamento">{{ adscripcionVer.departamento }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Fecha de inicio</span><span class="detalle-valor">{{ formatearFecha(adscripcionVer.fecha_inicio) }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Fecha de fin</span><span class="detalle-valor">{{ adscripcionVer.fecha_fin ? formatearFecha(adscripcionVer.fecha_fin) : 'Adscripción vigente' }}</span></div>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModalVer">Cerrar</button>
        </div>
      </div>
    </div>

    <!-- Modal Nueva Adscripción -->
    <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-content modal-adscripcion">
        <div class="modal-header">
          <h3>Nueva Adscripción</h3>
          <button @click="cerrarModal" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">

          <!-- Aviso amarillo si empleado ya tiene adscripción activa — igual a ServiciosEscolaresView -->
          <div v-if="avisoAdscripcionActiva" class="alerta-advertencia">
            <svg xmlns="http://www.w3.org/2000/svg" class="alerta-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Este empleado ya tiene una adscripción vigente. Al guardar, la adscripción actual quedará como histórica.</span>
          </div>

          <!-- Buscador de empleado — igual al de FormularioEmpleadoView -->
          <div v-if="!empleadoSeleccionado" class="form-grupo">
            <label class="form-label">Empleado <span class="obligatorio">*</span></label>
            <div class="search-group">
              <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input
                type="text"
                v-model="busquedaEmpleadoModal"
                placeholder="Buscar empleado por nombre o número..."
                class="search-input"
                @input="buscarEmpleadoModal"
                @keydown.escape="busquedaEmpleadoModal = ''; resultadosEmpleado = []"
              >
              <span v-if="buscandoEmpleado" class="spinner-busqueda"></span>
            </div>
            <div v-if="resultadosEmpleado.length > 0" class="resultados-empleado">
              <div v-for="emp in resultadosEmpleado" :key="emp.id_empleado" class="resultado-empleado-item" @click="elegirEmpleado(emp)">
                <div class="resultado-avatar">{{ emp.nombre.charAt(0).toUpperCase() }}</div>
                <div class="resultado-info">
                  <strong>{{ emp.nombre }}</strong>
                  <span>{{ emp.numero_empleado }}</span>
                </div>
                <button class="btn-seleccionar-empleado">Seleccionar</button>
              </div>
            </div>
            <div v-else-if="busquedaEmpleadoModal.length >= 3 && !buscandoEmpleado" class="sin-resultados-empleado">
              No se encontraron empleados.
            </div>
          </div>

          <!-- Empleado seleccionado -->
          <div v-if="empleadoSeleccionado" class="empleado-seleccionado">
            <div class="empleado-avatar">{{ empleadoSeleccionado.nombre.charAt(0).toUpperCase() }}</div>
            <div class="empleado-datos">
              <strong>{{ empleadoSeleccionado.nombre }}</strong>
              <span>{{ empleadoSeleccionado.numero_empleado }}</span>
            </div>
            <button class="btn-cambiar-empleado" @click="cambiarEmpleado">Cambiar</button>
          </div>

          <!-- Selector de departamento -->
          <div class="form-grupo">
            <label class="form-label">Departamento <span class="obligatorio">*</span></label>
            <select v-model="form.id_departamento" class="modal-select" @change="verificarAdscripcionActiva">
              <option value="">Seleccionar departamento</option>
              <option v-for="d in departamentos" :key="d.id_departamento" :value="d.id_departamento">{{ d.nombre }}</option>
            </select>
          </div>

          <!-- Fechas -->
          <div class="form-grupo-doble">
            <div class="form-grupo">
              <label class="form-label">Fecha de inicio <span class="obligatorio">*</span></label>
              <input type="date" v-model="form.fecha_inicio" class="modal-input" :max="hoyISO" />
            </div>
            <div class="form-grupo">
              <label class="form-label">Fecha de fin <span class="etiqueta-opcional">(opcional)</span></label>
              <input type="date" v-model="form.fecha_fin" class="modal-input" :min="form.fecha_inicio || undefined" />
              <small class="hint-fecha">Si se deja vacío, la adscripción se considera vigente.</small>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">Cancelar</button>
          <button class="btn-guardar" @click="guardarAdscripcion" :disabled="guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            {{ guardando ? 'Guardando...' : 'Guardar' }}
          </button>
        </div>
      </div>
    </div>

  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'
import { 
  getAdscripciones, 
  crearAdscripcion, 
  actualizarAdscripcion as actualizarAdscripcionAPI, 
  eliminarAdscripcion as eliminarAdscripcionAPI
} from '@/api/recursosHumanos'


const adscripciones   = ref([])
const departamentos   = ref([])
const cargando        = ref(false)
const cargandoBusqueda = ref(false)
const guardando       = ref(false)
const filaActiva      = ref(-1)
const tablaRef        = ref(null)
const busqueda        = ref('')
const filtroDepartamento = ref('')
const filasPorPagina  = ref(10)
const currentPage     = ref(1)
const hoyISO          = new Date().toISOString().split('T')[0]

const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null
const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}


const eliminarAdscripcion = async (id) => {
  if (!confirm('¿Eliminar adscripción?')) return
  try {
    const res = await eliminarAdscripcionAPI(id)
    if (res.success) {
      mostrarNotificacion('Adscripción eliminada', 'exito')
      cargarAdscripciones()
    }
  } catch (error) {
    mostrarNotificacion('Error al eliminar', 'error')
  }
}

const actualizarAdscripcion = async (id, datos) => {
    try {
      const res = await actualizarAdscripcionAPI(id, {
        id_departamento: datos.id_departamento,
        fecha_inicio: datos.fecha_inicio,
        fecha_fin: datos.fecha_fin || null
      })
      
      if (res.success) {
        mostrarNotificacion('Adscripción actualizada', 'exito')
        cargarAdscripciones()
      }
    } catch (error) {
      mostrarNotificacion('Error al actualizar', 'error')
    }
  }

  const cargarAdscripciones = async () => {
    cargando.value = true
    try {
      const res = await getAdscripciones()
      if (!res.success) throw new Error()
      adscripciones.value = res.data.map(normalizarAdscripcion)
    } catch (error) {
      mostrarNotificacion('Error cargando adscripciones', 'error')
    } finally {
      cargando.value = false
    }
  }

const normalize = (text) => {
  if (!text) return ''
  return text.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
}

const formatearFecha = (fecha) => {
  if (!fecha) return '—'
  try { return new Date(fecha).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' }) }
  catch { return fecha }
}

const normalizarAdscripcion = (a) => ({
  id_adscripcion:  a.id_adscripcion || a.id,
  numero_empleado: a.numero_empleado || a.empleado?.numero_empleado || '',
  nombre_empleado: a.nombre_completo || a.nombre_empleado || a.empleado?.nombre || '',
  departamento:    a.departamento || a.departamento?.nombre_departamento || '',
  id_departamento: a.id_departamento || a.departamento?.id_departamento,
  fecha_inicio:    a.fecha_inicio || '',
  fecha_fin:       a.fecha_fin || null,
})

const departamentosDisponibles = computed(() =>
  [...new Set(adscripciones.value.map(a => a.departamento).filter(Boolean))]
)


  const handleCrearAdscripcion = async (datos) => {
    try {
      const res = await crearAdscripcion({
        id_empleado: datos.id_empleado,
        id_departamento: datos.id_departamento,
        fecha_inicio: datos.fecha_inicio,
        fecha_fin: datos.fecha_fin || null
      })
      
      if (res.success) {
        mostrarNotificacion('Adscripción creada', 'exito')
        cargarAdscripciones()
      }
    } catch (error) {
      // ⚠️ VALIDACIÓN ESPECIAL PARA ADSCRIPCIÓN ACTIVA
      if (error.response?.status === 409) {
        // MOSTRAR EN AMARILLO
        mostrarNotificacion(
          error.response.data.error,
          'warning'  // ← Color amarillo
        )
        console.log('Adscripción activa:', error.response.data.adscripcion_activa)
        // Opcional: mostrar modal con datos de adscripción existente
      } else {
        mostrarNotificacion('Error al crear', 'error')
      }
    }
  }

const cargarDepartamentos = async () => {
  try {
    const res = await fetch('http://localhost:8000/api/departamentos')
    if (!res.ok) throw new Error()
    departamentos.value = await res.json()
  } catch {
    console.error('❌ Error cargando departamentos')
  }
}

onMounted(async () => {
  await Promise.all([cargarAdscripciones(), cargarDepartamentos()])
})

let timerBusqueda = null
watch(busqueda, () => {
  cargandoBusqueda.value = true
  if (timerBusqueda) clearTimeout(timerBusqueda)
  timerBusqueda = setTimeout(() => { cargandoBusqueda.value = false; currentPage.value = 1 }, 350)
})

const adscripcionesFiltradas = computed(() =>
  adscripciones.value.filter(a => {
    const coincideBusqueda = !busqueda.value ||
      normalize(a.nombre_empleado).includes(normalize(busqueda.value)) ||
      a.numero_empleado.includes(busqueda.value)
    const coincideDep = !filtroDepartamento.value || a.departamento === filtroDepartamento.value
    return coincideBusqueda && coincideDep
  })
)

const totalPages = computed(() => Math.ceil(adscripcionesFiltradas.value.length / filasPorPagina.value) || 1)
const paginatedAdscripciones = computed(() => {
  const start = (currentPage.value - 1) * filasPorPagina.value
  return adscripcionesFiltradas.value.slice(start, start + filasPorPagina.value)
})
const visiblePages = computed(() => {
  const total = totalPages.value, current = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = new Set([1, total, current, current - 1, current + 1].filter(p => p >= 1 && p <= total))
  return [...pages].sort((a, b) => a - b)
})

const goToPage = (p) => { currentPage.value = p; filaActiva.value = -1 }
const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }
const resetFilters = () => { busqueda.value = ''; filtroDepartamento.value = ''; currentPage.value = 1; filaActiva.value = -1 }

// Modal Ver
const showViewModal    = ref(false)
const adscripcionVer   = ref({})
const abrirModalVer    = (a) => { adscripcionVer.value = { ...a }; showViewModal.value = true }
const cerrarModalVer   = () => { showViewModal.value = false }

// Modal Nueva Adscripción
const showModal              = ref(false)
const avisoAdscripcionActiva = ref(false)
const empleadoSeleccionado   = ref(null)
const busquedaEmpleadoModal  = ref('')
const resultadosEmpleado     = ref([])
const buscandoEmpleado       = ref(false)
let debounceEmpleado = null

const form = ref({ id_departamento: '', fecha_inicio: '', fecha_fin: '' })

const nuevaAdscripcion = () => {
  form.value = { id_departamento: '', fecha_inicio: '', fecha_fin: '' }
  empleadoSeleccionado.value = null
  busquedaEmpleadoModal.value = ''
  resultadosEmpleado.value = []
  avisoAdscripcionActiva.value = false
  showModal.value = true
}
const cerrarModal = () => { showModal.value = false }

const buscarEmpleadoModal = () => {
  clearTimeout(debounceEmpleado)
  if (busquedaEmpleadoModal.value.trim().length < 3) { resultadosEmpleado.value = []; return }
  buscandoEmpleado.value = true
  debounceEmpleado = setTimeout(async () => {
    try {
      const params = new URLSearchParams({ q: busquedaEmpleadoModal.value.trim() })
      const res = await fetch(`http://localhost:8000/api/empleados?${params}`)
      if (!res.ok) throw new Error()
      const json = await res.json()
      const lista = json.data || json
      resultadosEmpleado.value = lista.map(e => ({
        id_empleado:     e.id_empleado || e.id,
        numero_empleado: e.numero_empleado || '',
        nombre:          e.nombre_completo || e.nombre || '',
      }))
    } catch { resultadosEmpleado.value = [] }
    finally { buscandoEmpleado.value = false }
  }, 350)
}

const elegirEmpleado = (emp) => {
  empleadoSeleccionado.value = emp
  resultadosEmpleado.value   = []
  busquedaEmpleadoModal.value = ''
  verificarAdscripcionActiva()
}

const cambiarEmpleado = () => {
  empleadoSeleccionado.value  = null
  avisoAdscripcionActiva.value = false
  busquedaEmpleadoModal.value  = ''
}

const verificarAdscripcionActiva = () => {
  if (!empleadoSeleccionado.value) return
  const tieneActiva = adscripciones.value.some(
    a => a.numero_empleado === empleadoSeleccionado.value.numero_empleado && !a.fecha_fin
  )
  avisoAdscripcionActiva.value = tieneActiva
}

const guardarAdscripcion = async () => {
  if (!empleadoSeleccionado.value) {
    mostrarNotificacion('Seleccione un empleado.', 'error'); return
  }
  if (!form.value.id_departamento) {
    mostrarNotificacion('Seleccione un departamento.', 'error'); return
  }
  if (!form.value.fecha_inicio) {
    mostrarNotificacion('La fecha de inicio es obligatoria.', 'error'); return
  }
  guardando.value = true
  const payload = {
    id_empleado:     empleadoSeleccionado.value.id_empleado,
    id_departamento: form.value.id_departamento,
    fecha_inicio:    form.value.fecha_inicio,
    fecha_fin:       form.value.fecha_fin || null,
  }
  try {
    const res = await fetch('http://localhost:8000/api/adscripciones', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    })
    if (!res.ok) throw new Error()
    await cargarAdscripciones()
    cerrarModal()
    mostrarNotificacion('Adscripción registrada correctamente.', 'exito')
    console.log('✅ Adscripción creada')
  } catch {
    mostrarNotificacion('Error al guardar la adscripción.', 'error')
  } finally {
    guardando.value = false
  }
}

const navegarTeclado = (e) => {
  const total = paginatedAdscripciones.value.length
  if (!total) return
  if (e.key === 'ArrowDown') { e.preventDefault(); filaActiva.value = Math.min(filaActiva.value + 1, total - 1) }
  else if (e.key === 'ArrowUp') { e.preventDefault(); filaActiva.value = Math.max(filaActiva.value - 1, 0) }
  else if (e.key === 'Enter' && filaActiva.value >= 0) { e.preventDefault(); abrirModalVer(paginatedAdscripciones.value[filaActiva.value]) }
  else if (e.key === 'PageDown') { e.preventDefault(); nextPage() }
  else if (e.key === 'PageUp') { e.preventDefault(); prevPage() }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.adscripcion-page {
  --azul: #1B396A; --azul-hover: #1D4ED8; --borde: #E5E7EB;
  --fondo: #F5F5F5; --texto: #1A1A1A; --gris: #6B7280;
  max-width: 100%; background: var(--fondo); font-family: 'Montserrat', sans-serif;
}

.breadcrumb { display: flex; align-items: center; gap: 0.4rem; color: var(--gris); font-size: 0.88rem; margin-bottom: 0.75rem; }
.breadcrumb .sep { color: #E5E7EB; }
.breadcrumb-link { color: var(--gris); text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: var(--azul); }
.breadcrumb-actual { color: var(--azul); font-weight: 600; }

.page-header { display: flex; align-items: baseline; gap: 1rem; margin-bottom: 1.2rem; }
.page-title { color: var(--texto); font-size: 1.75rem; font-weight: 700; letter-spacing: -0.02em; margin: 0; }
.page-subtitle { font-size: 0.9rem; color: var(--gris); font-weight: 500; }

.barra-carga-global { height: 3px; background: transparent; border-radius: 2px; margin-bottom: 1rem; overflow: hidden; transition: opacity 0.3s; opacity: 0; }
.barra-carga-global.visible { opacity: 1; }
.barra-progreso { height: 100%; width: 40%; background: #1B396A; border-radius: 2px; animation: deslizar 1.2s ease-in-out infinite; }
@keyframes deslizar { 0% { transform: translateX(-100%); } 100% { transform: translateX(350%); } }

.notificacion-ui { display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 10px; font-size: 0.93rem; font-weight: 500; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.notificacion-ui.exito { background: #DCFCE7; color: #16A34A; border: 1px solid #86EFAC; }
.notificacion-ui.error { background: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5; }
.notif-icono { width: 20px; height: 20px; flex-shrink: 0; }
.notif-fade-enter-active, .notif-fade-leave-active { transition: all 0.35s ease; }
.notif-fade-enter-from, .notif-fade-leave-to { opacity: 0; transform: translateY(-8px); }

.filters-bar { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.2rem; flex-wrap: wrap; }
.search-group { position: relative; flex: 0 0 300px; min-width: 220px; }
.search-input { width: 100%; padding: 10px 14px 10px 42px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.93rem; background: #FFFFFF; color: var(--texto); font-family: 'Montserrat', sans-serif; outline: none; transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box; }
.search-input:focus { border-color: #1B396A; box-shadow: 0 0 0 3px #DBEAFE; }
.search-input::placeholder { color: #9CA3AF; }
.search-icon-svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; stroke: var(--gris); pointer-events: none; }
.spinner-busqueda { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; border: 2px solid #E5E7EB; border-top-color: #1B396A; border-radius: 50%; animation: girar 0.7s linear infinite; }
.filter-select { padding: 10px 12px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.92rem; flex: 1 1 160px; min-width: 140px; background: #FFFFFF; color: var(--texto); font-family: 'Montserrat', sans-serif; cursor: pointer; outline: none; }
.filter-select:focus { border-color: #1B396A; }
.btn-limpiar { display: flex; align-items: center; gap: 6px; background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); padding: 10px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.92rem; white-space: nowrap; font-family: 'Montserrat', sans-serif; transition: background 0.15s; }
.btn-limpiar:hover { background: var(--fondo); }
.reset-icon { width: 16px; height: 16px; stroke: var(--gris); }
.btn-nuevo { background: #1B396A; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; white-space: nowrap; font-family: 'Montserrat', sans-serif; font-size: 0.92rem; transition: background 0.2s; margin-left: auto; }
.btn-nuevo:hover { background: #1D4ED8; }

.table-container { background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid var(--borde); }
.adscripcion-table { width: 100%; border-collapse: collapse; outline: none; }
.adscripcion-table th { background: var(--fondo); padding: 12px 16px; text-align: left; font-weight: 600; font-size: 0.88rem; color: var(--texto); border-bottom: 1px solid var(--borde); font-family: 'Montserrat', sans-serif; white-space: nowrap; }
.adscripcion-table td { padding: 11px 16px; border-bottom: 1px solid var(--borde); color: var(--texto); font-size: 0.93rem; font-family: 'Montserrat', sans-serif; }
.adscripcion-table tbody tr { transition: background 0.15s; cursor: pointer; }
.adscripcion-table tbody tr:hover { background: #F8FAFC; }
.adscripcion-table tbody tr:last-child td { border-bottom: none; }
.fila-seleccionada { background: #DBEAFE !important; }
.celda-control { font-weight: 600; color: #1B396A; font-size: 0.92rem; }

.badge-departamento { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; background: #FEF3C7; color: #F59E0B; }
.badge-vigente { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; background: #DCFCE7; color: #16A34A; }
.fecha-fin { color: var(--gris); font-size: 0.9rem; }

.celda-acciones { display: flex; gap: 7px; align-items: center; }
.btn-accion { display: flex; align-items: center; gap: 5px; padding: 6px 13px; border-radius: 6px; font-size: 0.85rem; cursor: pointer; font-weight: 600; font-family: 'Montserrat', sans-serif; transition: background 0.15s; white-space: nowrap; border: none; }
.btn-accion svg { width: 14px; height: 14px; }
.btn-accion.ver { background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); }
.btn-accion.ver:hover { background: var(--fondo); }

.estado-vacio, .estado-cargando { text-align: center; padding: 3.5rem 2rem; color: var(--gris); }
.icono-vacio { width: 56px; height: 56px; stroke: #9CA3AF; margin-bottom: 12px; }
.estado-vacio h3 { font-size: 1.2rem; color: var(--texto); margin: 0 0 6px; }
.estado-vacio p { font-size: 0.93rem; margin: 0 0 1.2rem; }
.btn-limpiar-vacio { background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); padding: 9px 20px; border-radius: 8px; font-weight: 500; cursor: pointer; font-family: 'Montserrat', sans-serif; }
.spinner-tabla { display: inline-block; width: 36px; height: 36px; border: 3px solid #E5E7EB; border-top-color: #1B396A; border-radius: 50%; animation: girar 0.8s linear infinite; margin-bottom: 12px; }

.paginacion { margin-top: 1.2rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; color: var(--gris); font-family: 'Montserrat', sans-serif; flex-wrap: wrap; gap: 0.5rem; }
.paginacion-izquierda, .paginacion-centro, .paginacion-derecha { display: flex; align-items: center; gap: 8px; }
.select-filas { border: 1px solid var(--borde); border-radius: 6px; padding: 4px 8px; font-size: 0.9rem; background: #FFFFFF; font-family: 'Montserrat', sans-serif; }
.btn-pag { padding: 5px 11px; border: 1px solid var(--borde); background: #FFFFFF; border-radius: 6px; cursor: pointer; color: var(--texto); font-family: 'Montserrat', sans-serif; font-size: 0.9rem; transition: background 0.15s; }
.btn-pag:hover:not(:disabled) { background: var(--fondo); }
.btn-pag:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-pag.activo { background: #1B396A; color: white; border-color: #1B396A; }

/* ── Modales ── */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.modal-content { background: #FFFFFF; width: 520px; max-width: 92%; border-radius: 14px; box-shadow: 0 20px 50px rgba(0,0,0,0.18); overflow: hidden; border: 1px solid var(--borde); }
.modal-adscripcion { width: 580px; }
.modal-header { background: #1B396A; color: white; padding: 1.1rem 1.6rem; display: flex; justify-content: space-between; align-items: center; }
.modal-header h3 { margin: 0; font-size: 1.2rem; font-weight: 700; font-family: 'Montserrat', sans-serif; }
.btn-cerrar-modal { background: none; border: none; color: white; font-size: 1.7rem; cursor: pointer; line-height: 1; opacity: 0.85; }
.btn-cerrar-modal:hover { opacity: 1; }
.modal-body { padding: 1.6rem; }
.modal-footer { padding: 1rem 1.6rem; background: var(--fondo); display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid var(--borde); }

/* Aviso amarillo — igual a alerta-error de ServiciosEscolaresView pero en amarillo */
.alerta-advertencia {
  display: flex; align-items: flex-start; gap: 10px;
  background: #FEF3C7; border: 1px solid #FCD34D; border-radius: 10px;
  padding: 12px 16px; margin-bottom: 1.2rem;
  font-size: 0.9rem; color: #F59E0B; font-weight: 500; font-family: 'Montserrat', sans-serif;
}
.alerta-icono { width: 20px; height: 20px; flex-shrink: 0; stroke: #F59E0B; margin-top: 1px; }

/* Buscador de empleado en modal */
.form-grupo { margin-bottom: 1.2rem; }
.form-grupo-doble { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.2rem; }
.form-label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: var(--texto); font-family: 'Montserrat', sans-serif; }
.obligatorio { color: #DC2626; }
.etiqueta-opcional { font-weight: 400; font-size: 0.78rem; color: var(--gris); }
.modal-input, .modal-select { width: 100%; padding: 10px 14px; border: 1.5px solid var(--borde); border-radius: 8px; font-size: 0.95rem; background: #FFFFFF; color: var(--texto); font-family: 'Montserrat', sans-serif; outline: none; transition: border-color 0.2s; box-sizing: border-box; }
.modal-input:focus, .modal-select:focus { border-color: #1B396A; }
.hint-fecha { display: block; margin-top: 4px; font-size: 0.78rem; color: var(--gris); font-family: 'Montserrat', sans-serif; }

.resultados-empleado { margin-top: 8px; border: 1px solid var(--borde); border-radius: 10px; overflow: hidden; background: #FFFFFF; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.resultado-empleado-item { display: flex; align-items: center; gap: 12px; padding: 10px 14px; cursor: pointer; transition: background 0.15s; border-bottom: 1px solid var(--borde); }
.resultado-empleado-item:last-child { border-bottom: none; }
.resultado-empleado-item:hover { background: #F8FAFF; }
.resultado-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--azul); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; flex-shrink: 0; }
.resultado-info { flex: 1; display: flex; flex-direction: column; }
.resultado-info strong { font-size: 0.93rem; color: var(--texto); }
.resultado-info span { font-size: 0.82rem; color: var(--gris); margin-top: 1px; }
.btn-seleccionar-empleado { background: #EFF6FF; color: var(--azul); border: 1px solid #BFDBFE; padding: 6px 14px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; cursor: pointer; white-space: nowrap; font-family: 'Montserrat', sans-serif; }
.sin-resultados-empleado { margin-top: 8px; padding: 12px 14px; border-radius: 8px; background: #F5F5F5; color: var(--gris); font-size: 0.88rem; border: 1px solid var(--borde); }

/* Empleado seleccionado — igual a persona-seleccionada de FormularioEmpleadoView */
.empleado-seleccionado { display: flex; align-items: center; gap: 16px; background: #F0FDF4; border: 1.5px solid #86EFAC; border-radius: 12px; padding: 14px 18px; margin-bottom: 1.2rem; }
.empleado-avatar { width: 40px; height: 40px; border-radius: 50%; background: #16A34A; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; flex-shrink: 0; }
.empleado-datos { flex: 1; }
.empleado-datos strong { display: block; color: var(--texto); font-size: 0.97rem; }
.empleado-datos span { color: var(--gris); font-size: 0.85rem; }
.btn-cambiar-empleado { background: white; color: var(--gris); border: 1px solid var(--borde); padding: 7px 14px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.88rem; font-family: 'Montserrat', sans-serif; transition: background 0.15s; }
.btn-cambiar-empleado:hover { background: var(--fondo); }

.btn-secundario { padding: 10px 22px; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); transition: background 0.15s; }
.btn-secundario:hover { background: var(--fondo); }
.btn-secundario:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-guardar { display: flex; align-items: center; gap: 8px; padding: 10px 22px; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #1B396A; color: white; border: none; transition: background 0.15s; }
.btn-guardar:hover:not(:disabled) { background: #1D4ED8; }
.btn-guardar:disabled { opacity: 0.65; cursor: not-allowed; }
.spinner-btn { display: inline-block; width: 15px; height: 15px; border: 2px solid rgba(255,255,255,0.4); border-top-color: white; border-radius: 50%; animation: girar 0.7s linear infinite; flex-shrink: 0; }

.detalle-fila { display: flex; justify-content: space-between; align-items: center; padding: 11px 0; border-bottom: 1px solid var(--borde); font-size: 0.95rem; color: var(--texto); font-family: 'Montserrat', sans-serif; }
.detalle-fila:last-child { border-bottom: none; }
.detalle-label { font-weight: 600; color: var(--gris); }
.detalle-valor { font-weight: 500; }

@keyframes girar { to { transform: rotate(360deg); } }
</style>