<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="alumnos-page">


      
      <div class="page-header">
        <h1 class="page-title">Lista de Alumnos</h1>
        <span class="page-subtitle">{{ alumnosFiltrados.length }} registro(s) encontrado(s)</span>
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


      <div class="filters-bar">


        <div class="search-group search-group-compacto">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Buscar por número de control o nombre..."
            v-model="busquedaAlumno"
            class="search-input"
            @keydown.enter="aplicarBusqueda"
            @keydown.escape="busquedaAlumno = ''"
          >

          <span v-if="cargandoBusqueda" class="spinner-busqueda"></span>
        </div>

        <!-- Filtro carrera dinámico -->
        <select v-model="filtroCarreraId" class="filter-select" @change="currentPage = 1">
          <option value="">Carrera</option>
          <option v-for="c in catalogos.carreras" :key="c.id_carrera" :value="c.id_carrera">
            {{ c.nombre }}
          </option>
        </select>

        <select v-model="filtroSemestre" class="filter-select" @change="currentPage = 1">
          <option value="">Semestre</option>
          <option v-for="n in 8" :key="n" :value="String(n)">{{ n }}° Semestre</option>
        </select>

        <!-- Filtro estatus dinámico -->
        <select v-model="filtroEstatusId" class="filter-select" @change="currentPage = 1">
          <option value="">Estatus</option>
          <option v-for="e in catalogos.estatus_alumno" :key="e.id_estatus_alumno" :value="e.id_estatus_alumno">
            {{ e.nombre }}
          </option>
        </select>

        <button class="btn-limpiar" @click="resetFilters">
          <svg xmlns="http://www.w3.org/2000/svg" class="reset-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Limpiar
        </button>

        <button class="btn-nuevo" @click="nuevoAlumno">
          + Nueva inscripción
        </button>
      </div>


      <div class="table-container">


        <div v-if="cargando && alumnos.length === 0" class="estado-cargando">
          <div class="spinner-tabla"></div>
          <p>Cargando registros...</p>
        </div>

        <table
          v-else-if="paginatedAlumnos.length > 0"
          class="alumnos-table"
          ref="tablaRef"
          @keydown="navegarTeclado"
          tabindex="0"
        >
          <thead>
            <tr>
              <th>Núm. de Control</th>
              <th>Nombre</th>
              <th>Carrera</th>
              <th>Semestre</th>
              <th>Estatus</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(alumno, index) in paginatedAlumnos"
              :key="alumno.id_alumno || alumno.id"
              :class="{ 'fila-seleccionada': filaActiva === index }"
              @click="filaActiva = index"
              :data-index="index"
            >
              <td class="celda-control">{{ alumno.numero_control || alumno.noControl }}</td>
              <td>{{ alumno.nombre || (alumno.persona?.nombre_completo) || (alumno.persona?.nombre) }}</td>
              <td>{{ alumno.carrera?.nombre_carrera || alumno.carrera }}</td>
              <td class="celda-semestre">{{ alumno.semestre_actual || alumno.semestre }}°</td>
              <td>
                <span class="estatus-badge" :class="claseEstatus(alumno.estatus)">
                  {{ alumno.estatus }}
                </span>
              </td>
              <td class="celda-acciones">
                <button class="btn-icono ver" @click.stop="abrirModalVer(alumno)" title="Ver detalles">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
                <button class="btn-icono editar" @click.stop="abrirModalEditar(alumno)" title="Editar registro">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3>Sin resultados</h3>
          <p>No se encontraron alumnos con los criterios aplicados.</p>
          <button class="btn-limpiar-vacio" @click="resetFilters">Limpiar filtros</button>
        </div>
      </div>

      <div class="paginacion">
        <div class="paginacion-izquierda">
          Filas por página:
          <select v-model="filasPorPagina" @change="currentPage = 1" class="select-filas">
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
        </div>
        <div class="paginacion-centro">
          Página {{ currentPage }} de {{ totalPages }}
        </div>
        <div class="paginacion-derecha">
          <button class="btn-pag" @click="prevPage" :disabled="currentPage === 1">‹</button>
          <button
            v-for="page in visiblePages"
            :key="page"
            class="btn-pag"
            :class="{ activo: page === currentPage }"
            @click="goToPage(page)"
          >{{ page }}</button>
          <button class="btn-pag" @click="nextPage" :disabled="currentPage === totalPages">›</button>
        </div>
      </div>
    </div>




    <div v-if="showViewModal" class="modal-overlay" @click.self="cerrarModalVer">
      <div class="modal-content modal-ver">
        <div class="modal-header">
          <h3>Datos del Alumno</h3>
          <button @click="cerrarModalVer" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">
          <div class="detalle-fila"><span class="detalle-label">Núm. de Control</span><span class="detalle-valor">{{ alumnoVer.noControl }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Nombre completo</span><span class="detalle-valor">{{ alumnoVer.nombre }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Carrera</span><span class="detalle-valor">{{ alumnoVer.carrera }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Semestre</span><span class="detalle-valor">{{ alumnoVer.semestre }}°</span></div>
          <div class="detalle-fila">
            <span class="detalle-label">Estatus</span>
            <span class="estatus-badge" :class="claseEstatus(alumnoVer.estatus)">{{ alumnoVer.estatus }}</span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModalVer">Cerrar</button>
        </div>
      </div>
    </div>



    <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ alumnoEditar.id_alumno ? 'Editar Alumno' : 'Nuevo Alumno' }}</h3>
          <button @click="cerrarModal" class="btn-cerrar-modal">×</button>
        </div>

        <div class="modal-body">
          <div class="form-grupo">
            <label>Número de Control</label>
            <input v-model="alumnoEditar.noControl" type="text" readonly class="modal-input deshabilitado" />
          </div>
          <div class="form-grupo">
            <label>Nombre completo</label>
            <input v-model="alumnoEditar.nombre" type="text" class="modal-input" required />
          </div>
          <div class="form-grupo">
            <label>Carrera</label>
            <!-- Dinámico: carga carreras desde /api/alumnos/catalogos igual que el formulario de alta -->
            <select v-model="alumnoEditar.id_carrera" class="modal-select">
              <option value="">Seleccionar carrera</option>
              <option v-for="c in catalogos.carreras" :key="c.id_carrera" :value="c.id_carrera">
                {{ c.nombre }}
              </option>
            </select>
          </div>
          <div class="form-grupo-doble">
            <div class="form-grupo">
              <label>Semestre</label>
              <select v-model="alumnoEditar.semestre" class="modal-select">
                <option v-for="n in 8" :key="n" :value="n">{{ n }}° Semestre</option>
              </select>
            </div>
            <div class="form-grupo">
              <label>Estatus</label>
              <!-- Dinámico: carga estatus desde /api/alumnos/catalogos -->
              <select v-model="alumnoEditar.id_estatus_alumno" class="modal-select">
                <option value="">Seleccionar estatus</option>
                <option v-for="e in catalogos.estatus_alumno" :key="e.id_estatus_alumno" :value="e.id_estatus_alumno">
                  {{ e.nombre }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">Cancelar</button>
          <button v-if="alumnoEditar.id_alumno" class="btn-eliminar" @click="eliminarAlumno" :disabled="guardando">
            Eliminar
          </button>
          <!-- Botón guardar con barra de carga -->
          <button class="btn-guardar" @click="guardarCambios" :disabled="guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            <span>{{ guardando ? 'Guardando...' : 'Guardar cambios' }}</span>
          </button>
        </div>
      </div>
    </div>

  </MainLayout>
</template>



<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const router = useRouter()

// ── URL base del backend (variable de entorno) ──────────────────────
const API_URL = import.meta.env.VITE_API_URL

// ── Estado principal ────────────────────────────────────────────────
const alumnos          = ref([])
const cargando         = ref(false)
const cargandoBusqueda = ref(false)
const guardando        = ref(false)
const filaActiva       = ref(-1)
const tablaRef         = ref(null)

// ── Catálogos dinámicos (carreras, estatus) ─────────────────────────
const catalogos = ref({ carreras: [], estatus_alumno: [] })

const cargarCatalogos = async () => {
  try {
    const res = await fetch(`${API_URL}/api/alumnos/catalogos`)
    if (!res.ok) throw new Error('Error al cargar catálogos')
    const data = await res.json()
    catalogos.value.carreras       = data.carreras       || []
    catalogos.value.estatus_alumno = data.estatus_alumno || []
  } catch (e) {
    console.error('❌ Error catálogos:', e)
    mostrarNotificacion('No se pudieron cargar los catálogos de carrera/estatus.', 'error')
  }
}

// ── Filtros y paginación ────────────────────────────────────────────
const busquedaAlumno = ref('')
// Filtramos por ID numérico para ser consistentes con los datos del backend
const filtroCarreraId  = ref('')
const filtroSemestre   = ref('')
const filtroEstatusId  = ref('')
const filasPorPagina   = ref(10)
const currentPage      = ref(1)

// ── Notificación UI ─────────────────────────────────────────────────
const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

// ── Props ────────────────────────────────────────────────────────────
const props = defineProps({
  busquedaGlobal: { type: String, default: '' }
})

// ── Helpers ──────────────────────────────────────────────────────────
const normalize = (text) => {
  if (!text) return ''
  return text.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
}

const claseEstatus = (estatus) => {
  if (!estatus) return ''
  return estatus.toLowerCase().replace(/\s/g, '-')
}

// Resuelve el id_carrera desde el registro del alumno.
// Primero busca el campo directo, luego el objeto relacionado.
// No usa mapas hardcodeados — si el backend devuelve la relación correctamente
// siempre habrá un id_carrera disponible.
const resolverIdCarrera = (alumno) => {
  if (alumno.id_carrera) return alumno.id_carrera
  if (alumno.carrera?.id_carrera) return alumno.carrera.id_carrera
  return null
}

// ── Carga de alumnos ─────────────────────────────────────────────────
// Endpoint: GET /api/alumnos-crud  (usa AlumnoController@index que devuelve id_estatus_alumno)
const cargarAlumnosDesdeBD = async () => {
  cargando.value = true
  try {
    const response = await fetch(`${API_URL}/api/alumnos-crud`)
    if (!response.ok) throw new Error('Error del servidor')
    const data = await response.json()
    alumnos.value = data
    console.log('✅ Alumnos cargados:', data.length, 'registros')
  } catch (error) {
    console.error('❌ Error cargando alumnos:', error)
    mostrarNotificacion('No se pudo cargar la lista de alumnos. Verifica que el servidor esté activo.', 'error')
  } finally {
    cargando.value = false
  }
}

onMounted(() => { cargarAlumnosDesdeBD(); cargarCatalogos() })

// Debounce de búsqueda
let timerBusqueda = null
watch(busquedaAlumno, () => {
  cargandoBusqueda.value = true
  if (timerBusqueda) clearTimeout(timerBusqueda)
  timerBusqueda = setTimeout(() => {
    cargandoBusqueda.value = false
    currentPage.value = 1
  }, 350)
})

// ── Modal Ver ────────────────────────────────────────────────────────
const showViewModal = ref(false)
const alumnoVer     = ref({})

const abrirModalVer = (alumno) => {
  alumnoVer.value = {
    noControl: alumno.numero_control || alumno.noControl || '',
    nombre:    alumno.nombre || alumno.persona?.nombre_completo || alumno.persona?.nombre || '',
    carrera:   alumno.carrera?.nombre_carrera || alumno.carrera || '',
    semestre:  alumno.semestre_actual || alumno.semestre || '',
    estatus:   alumno.estatus || ''
  }
  showViewModal.value = true
}
const cerrarModalVer = () => { showViewModal.value = false }

// ── Modal Editar ─────────────────────────────────────────────────────
const showModal    = ref(false)
const alumnoEditar = ref({})

const abrirModalEditar = (alumno) => {
  console.log('🟡 Alumno para editar:', alumno)

  alumnoEditar.value = {
    id_alumno:        alumno.id_alumno || alumno.id,
    noControl:        alumno.numero_control || alumno.noControl || '',
    nombre:           alumno.nombre || alumno.persona?.nombre_completo || alumno.persona?.nombre || '',
    id_carrera:       resolverIdCarrera(alumno),
    semestre:         alumno.semestre_actual || alumno.semestre || 1,
    // Resolver id_estatus_alumno:
    // 1° El campo directo del backend (alumnos-crud ya lo incluye)
    // 2° Buscar por nombre exacto en el catálogo
    // 3° Si el campo booleano estatus=1 → Activo (id 1), de lo contrario no se asume nada
    id_estatus_alumno: alumno.id_estatus_alumno ||
      catalogos.value.estatus_alumno.find(e => e.nombre === alumno.estatus)?.id_estatus_alumno ||
      (alumno.estatus === true || alumno.estatus === 1 || alumno.estatus === 'Activo' ? 1 : null)
  }

  console.log('🟢 Datos preparados:', alumnoEditar.value)
  showModal.value = true
}

const cerrarModal = () => { showModal.value = false }

// ── Guardar cambios (edición) ─────────────────────────────────────────
// Endpoint: PUT /api/alumnos/{id}
const guardarCambios = async () => {
  const id = alumnoEditar.value.id_alumno
  if (!id) {
    mostrarNotificacion('No se encontró el identificador del alumno.', 'error')
    return
  }

  if (!alumnoEditar.value.id_carrera) {
    mostrarNotificacion('Selecciona una carrera antes de guardar.', 'error')
    return
  }
  if (!alumnoEditar.value.id_estatus_alumno) {
    mostrarNotificacion('Selecciona un estatus antes de guardar.', 'error')
    return
  }

  // Resolvemos el nombre del estatus para mandarlo también al backend
  // (el controller update() espera el texto para el match() de booleano)
  const nombreEstatus = catalogos.value.estatus_alumno
    .find(e => e.id_estatus_alumno === alumnoEditar.value.id_estatus_alumno)?.nombre || 'Activo'

  const payload = {
    nombre:             alumnoEditar.value.nombre,
    id_carrera:         alumnoEditar.value.id_carrera,
    semestre_actual:    parseInt(alumnoEditar.value.semestre),
    estatus:            nombreEstatus,        // texto para el match() del controller
    id_estatus_alumno:  alumnoEditar.value.id_estatus_alumno  // ID del catálogo
  }

  guardando.value = true
  try {
    console.log('🔵 Enviando PUT:', payload)
    const response = await fetch(`${API_URL}/api/alumnos/${id}`, {
      method:  'PUT',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body:    JSON.stringify(payload)
    })
    const data = await response.json()
    console.log('🟢 Respuesta backend:', data)

    if (response.ok) {
      await cargarAlumnosDesdeBD()
      cerrarModal()
      mostrarNotificacion('Alumno actualizado correctamente.', 'exito')
    } else {
      // Mostrar el mensaje de error del backend directamente al usuario
      const mensajeError = data.message || data.error || 'Error al actualizar el alumno.'
      mostrarNotificacion(mensajeError, 'error')
      console.error('❌ Error backend:', data)
    }
  } catch (error) {
    console.error('❌ ERROR:', error)
    mostrarNotificacion('Ocurrió un error de conexión al actualizar el alumno.', 'error')
  } finally {
    guardando.value = false
  }
}

// ── Eliminar alumno ───────────────────────────────────────────────────
// Endpoint: DELETE /api/alumnos/{id}
const eliminarAlumno = async () => {
  const id = alumnoEditar.value.id_alumno
  if (!id) {
    mostrarNotificacion('No se encontró el identificador del alumno.', 'error')
    return
  }

  // Usar ventana de confirmación nativa — funciona en todos los entornos
  // Si el equipo decide usar un modal propio en el futuro, reemplazar este bloque
  const confirmado = window.confirm(
    `¿Confirma que desea eliminar al alumno "${alumnoEditar.value.nombre}"?\n\nEsta acción no se puede deshacer.`
  )
  if (!confirmado) return

  guardando.value = true
  try {
    const response = await fetch(`${API_URL}/api/alumnos/${id}`, {
      method:  'DELETE',
      headers: { 'Accept': 'application/json' }
    })

    // DELETE puede devolver 204 sin body — hay que manejarlo
    let data = {}
    if (response.status !== 204) {
      data = await response.json().catch(() => ({}))
    }

    console.log('🗑️ Respuesta DELETE:', response.status, data)

    if (response.ok) {
      await cargarAlumnosDesdeBD()
      cerrarModal()
      mostrarNotificacion('Alumno eliminado correctamente.', 'exito')
    } else {
      const mensajeError = data.message || data.error || 'No se pudo eliminar el alumno.'
      mostrarNotificacion(mensajeError, 'error')
      console.error('❌ Error DELETE:', data)
    }
  } catch (error) {
    console.error('❌ ERROR DELETE:', error)
    mostrarNotificacion('Ocurrió un error de conexión al eliminar el alumno.', 'error')
  } finally {
    guardando.value = false
  }
}

// ── Filtrado ──────────────────────────────────────────────────────────
const alumnosFiltrados = computed(() => {
  return alumnos.value.filter(alumno => {
    const nombre    = alumno.nombre || alumno.persona?.nombre_completo || ''
    const noControl = (alumno.numero_control || alumno.noControl || '').toString()

    const coincideGlobal = !props.busquedaGlobal ||
      normalize(nombre).includes(normalize(props.busquedaGlobal)) ||
      noControl.includes(props.busquedaGlobal)

    const coincideLocal = !busquedaAlumno.value ||
      normalize(nombre).includes(normalize(busquedaAlumno.value)) ||
      noControl.includes(busquedaAlumno.value)

    // Comparamos por ID numérico para no depender del texto del nombre
    const coincideCarrera = !filtroCarreraId.value ||
      Number(alumno.id_carrera || alumno.carrera?.id_carrera) === Number(filtroCarreraId.value)

    const coincideSemestre = !filtroSemestre.value ||
      String(alumno.semestre_actual || alumno.semestre) === String(filtroSemestre.value)

    const coincideEstatus = !filtroEstatusId.value ||
      Number(alumno.id_estatus_alumno) === Number(filtroEstatusId.value)

    return coincideGlobal && coincideLocal && coincideCarrera && coincideSemestre && coincideEstatus
  })
})

// ── Paginación ────────────────────────────────────────────────────────
const totalPages = computed(() =>
  Math.ceil(alumnosFiltrados.value.length / filasPorPagina.value) || 1
)
const paginatedAlumnos = computed(() => {
  const start = (currentPage.value - 1) * filasPorPagina.value
  return alumnosFiltrados.value.slice(start, start + filasPorPagina.value)
})
const visiblePages = computed(() => {
  const total = totalPages.value, current = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = new Set([1, total, current, current - 1, current + 1].filter(p => p >= 1 && p <= total))
  return [...pages].sort((a, b) => a - b)
})

const goToPage  = (page) => { currentPage.value = page; filaActiva.value = -1 }
const prevPage  = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage  = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

const resetFilters = () => {
  busquedaAlumno.value  = ''
  filtroCarreraId.value = ''
  filtroSemestre.value  = ''
  filtroEstatusId.value = ''
  currentPage.value     = 1
  filaActiva.value      = -1
}

const aplicarBusqueda = () => { currentPage.value = 1 }
const nuevoAlumno     = () => router.push('/formulario-alumno')

// ── Navegación por teclado ────────────────────────────────────────────
const navegarTeclado = (e) => {
  const total = paginatedAlumnos.value.length
  if (total === 0) return
  if (e.key === 'ArrowDown')      { e.preventDefault(); filaActiva.value = Math.min(filaActiva.value + 1, total - 1) }
  else if (e.key === 'ArrowUp')   { e.preventDefault(); filaActiva.value = Math.max(filaActiva.value - 1, 0) }
  else if (e.key === 'Enter' && filaActiva.value >= 0) { e.preventDefault(); abrirModalVer(paginatedAlumnos.value[filaActiva.value]) }
  else if (e.key === 'PageDown')  { e.preventDefault(); nextPage() }
  else if (e.key === 'PageUp')    { e.preventDefault(); prevPage() }
}
</script>



<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.alumnos-page {
  --azul:        #1B396A;
  --azul-hover:  #1D4ED8;
  --borde:       #E5E7EB;
  --fondo:       #F5F5F5;
  --texto:       #1A1A1A;
  --gris:        #6B7280;

  max-width: 100%;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
}

.page-header {
  display: flex;
  align-items: baseline;
  gap: 1rem;
  margin-bottom: 1.2rem;
}
.page-title {
  color: var(--texto);
  font-size: 1.75rem;
  font-weight: 700;
  letter-spacing: -0.02em;
  margin: 0;
}
.page-subtitle {
  font-size: 0.9rem;
  color: var(--gris);
  font-weight: 500;
}

.barra-carga-global {
  height: 3px;
  background: transparent;
  border-radius: 2px;
  margin-bottom: 1rem;
  overflow: hidden;
  transition: opacity 0.3s;
  opacity: 0;
}
.barra-carga-global.visible { opacity: 1; }
.barra-progreso {
  height: 100%;
  width: 40%;
  background: #1B396A;
  border-radius: 2px;
  animation: deslizar 1.2s ease-in-out infinite;
}
@keyframes deslizar {
  0%   { transform: translateX(-100%); }
  100% { transform: translateX(350%); }
}

.notificacion-ui {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 18px;
  border-radius: 10px;
  font-size: 0.93rem;
  font-weight: 500;
  margin-bottom: 1rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.notificacion-ui.exito {
  background: #DCFCE7;
  color: #16A34A;
  border: 1px solid #86EFAC;
}
.notificacion-ui.error {
  background: #FEE2E2;
  color: #DC2626;
  border: 1px solid #FCA5A5;
}
.notif-icono { width: 20px; height: 20px; flex-shrink: 0; }

.notif-fade-enter-active, .notif-fade-leave-active { transition: all 0.35s ease; }
.notif-fade-enter-from, .notif-fade-leave-to { opacity: 0; transform: translateY(-8px); }

.filters-bar {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.2rem;
  flex-wrap: wrap;
}

/* SE1 — buscador más compacto */
.search-group-compacto {
  flex: 0 0 240px;
  min-width: 180px;
}

.search-group {
  position: relative;
  flex: 0 0 300px;
  min-width: 220px;
}
.search-input {
  width: 100%;
  padding: 10px 14px 10px 42px;
  border: 1px solid var(--borde);
  border-radius: 8px;
  font-size: 0.93rem;
  background: #FFFFFF;
  color: var(--texto);
  font-family: 'Montserrat', sans-serif;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box;
}
.search-input:focus {
  border-color: #1B396A;
  box-shadow: 0 0 0 3px #DBEAFE;
}
.search-input::placeholder { color: #9CA3AF; }
.search-icon-svg {
  position: absolute;
  left: 13px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px; height: 18px;
  stroke: var(--gris);
  pointer-events: none;
}

.spinner-busqueda {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 16px; height: 16px;
  border: 2px solid #E5E7EB;
  border-top-color: #1B396A;
  border-radius: 50%;
  animation: girar 0.7s linear infinite;
}

/* SE1 — filtros más compactos */
.filter-select {
  padding: 8px 10px;
  border: 1px solid var(--borde);
  border-radius: 8px;
  font-size: 0.88rem;
  flex: 1 1 130px;
  min-width: 120px;
  background: #FFFFFF;
  color: var(--texto);
  font-family: 'Montserrat', sans-serif;
  cursor: pointer;
  outline: none;
}
.filter-select:focus { border-color: #1B396A; }

/* SE1 — botones de la barra más compactos */
.btn-limpiar {
  display: flex;
  align-items: center;
  gap: 5px;
  background: #FFFFFF;
  color: var(--texto);
  border: 1px solid var(--borde);
  padding: 8px 12px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.88rem;
  white-space: nowrap;
  font-family: 'Montserrat', sans-serif;
  transition: background 0.15s;
}
.btn-limpiar:hover { background: var(--fondo); }
.reset-icon { width: 16px; height: 16px; stroke: var(--gris); }

.btn-nuevo {
  background: #1B396A;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  font-family: 'Montserrat', sans-serif;
  font-size: 0.88rem;
  transition: background 0.2s;
  margin-left: auto;
}
.btn-nuevo:hover { background: #1D4ED8; }

.table-container {
  background: #FFFFFF;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border: 1px solid var(--borde);
}

/* SE2 — filas más compactas */
.alumnos-table {
  width: 100%;
  border-collapse: collapse;
  outline: none;
}
.alumnos-table th {
  background: var(--fondo);
  padding: 9px 14px;
  text-align: left;
  font-weight: 600;
  font-size: 0.85rem;
  color: var(--texto);
  border-bottom: 1px solid var(--borde);
  font-family: 'Montserrat', sans-serif;
  white-space: nowrap;
}
.alumnos-table td {
  padding: 7px 14px;
  border-bottom: 1px solid var(--borde);
  color: var(--texto);
  font-size: 0.9rem;
  font-family: 'Montserrat', sans-serif;
}
.alumnos-table tbody tr {
  transition: background 0.15s;
  cursor: pointer;
}
.alumnos-table tbody tr:hover { background: #F8FAFC; }
.alumnos-table tbody tr:last-child td { border-bottom: none; }

.fila-seleccionada { background: #DBEAFE !important; }

.celda-control { font-weight: 600; color: #1B396A; font-size: 0.92rem; }
.celda-semestre { text-align: center; }

.estatus-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.83rem;
  font-weight: 600;
  font-family: 'Montserrat', sans-serif;
}
.estatus-badge.activo        { background: #DCFCE7; color: #16A34A; }
.estatus-badge.baja-temporal { background: #FEF3C7; color: #F59E0B; }
.estatus-badge.baja-definitiva { background: #FEE2E2; color: #DC2626; }
.estatus-badge.titulado  { background: #EDE9FE; color: #7C3AED; }
.estatus-badge.egresado  { background: #DBEAFE; color: #1B396A; }

/* SE3 — botones solo icono */
.btn-icono {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 6px;
  cursor: pointer;
  border: 1px solid var(--borde);
  transition: background 0.15s, border-color 0.15s;
  flex-shrink: 0;
}
.btn-icono svg { width: 15px; height: 15px; }
.btn-icono.ver {
  background: #FFFFFF;
  color: var(--texto);
}
.btn-icono.ver svg { stroke: var(--gris); }
.btn-icono.ver:hover { background: var(--fondo); }
.btn-icono.editar {
  background: #1B396A;
  border-color: #1B396A;
}
.btn-icono.editar svg { stroke: white; }
.btn-icono.editar:hover { background: #1D4ED8; border-color: #1D4ED8; }

/* Acciones (mantengo la versión original por compatibilidad) */
.celda-acciones { display: flex; gap: 7px; align-items: center; }

.btn-accion {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 6px 13px;
  border-radius: 6px;
  font-size: 0.85rem;
  cursor: pointer;
  font-weight: 600;
  font-family: 'Montserrat', sans-serif;
  transition: background 0.15s, border-color 0.15s;
  white-space: nowrap;
}
.btn-accion svg { width: 14px; height: 14px; }
.btn-accion.ver {
  background: #FFFFFF;
  color: var(--texto);
  border: 1px solid var(--borde);
}
.btn-accion.ver:hover { background: var(--fondo); }
.btn-accion.editar {
  background: #1B396A;
  color: white;
  border: 1px solid #1B396A;
}
.btn-accion.editar:hover { background: #1D4ED8; border-color: #1D4ED8; }

.estado-vacio, .estado-cargando {
  text-align: center;
  padding: 3.5rem 2rem;
  color: var(--gris);
}
.icono-vacio { width: 56px; height: 56px; stroke: #9CA3AF; margin-bottom: 12px; }
.estado-vacio h3 { font-size: 1.2rem; color: var(--texto); margin: 0 0 6px; }
.estado-vacio p  { font-size: 0.93rem; margin: 0 0 1.2rem; }
.btn-limpiar-vacio {
  background: #FFFFFF;
  color: var(--texto);
  border: 1px solid var(--borde);
  padding: 9px 20px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
}

.spinner-tabla {
  display: inline-block;
  width: 36px; height: 36px;
  border: 3px solid #E5E7EB;
  border-top-color: #1B396A;
  border-radius: 50%;
  animation: girar 0.8s linear infinite;
  margin-bottom: 12px;
}

.paginacion {
  margin-top: 1.2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
  color: var(--gris);
  font-family: 'Montserrat', sans-serif;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.paginacion-izquierda,
.paginacion-centro,
.paginacion-derecha {
  display: flex;
  align-items: center;
  gap: 8px;
}
.select-filas {
  border: 1px solid var(--borde);
  border-radius: 6px;
  padding: 4px 8px;
  font-size: 0.9rem;
  background: #FFFFFF;
  font-family: 'Montserrat', sans-serif;
}
.btn-pag {
  padding: 5px 11px;
  border: 1px solid var(--borde);
  background: #FFFFFF;
  border-radius: 6px;
  cursor: pointer;
  color: var(--texto);
  font-family: 'Montserrat', sans-serif;
  font-size: 0.9rem;
  transition: background 0.15s;
}
.btn-pag:hover:not(:disabled) { background: var(--fondo); }
.btn-pag:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-pag.activo {
  background: #1B396A;
  color: white;
  border-color: #1B396A;
}

/* ══ Modales ══ */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}
.modal-content {
  background: #FFFFFF;
  width: 520px;
  max-width: 92%;
  border-radius: 14px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.18);
  overflow: hidden;
  border: 1px solid var(--borde);
}
.modal-header {
  background: #1B396A;
  color: white;
  padding: 1.1rem 1.6rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.modal-header h3 {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 700;
  font-family: 'Montserrat', sans-serif;
}
.btn-cerrar-modal {
  background: none;
  border: none;
  color: white;
  font-size: 1.7rem;
  cursor: pointer;
  line-height: 1;
  opacity: 0.85;
  transition: opacity 0.15s;
}
.btn-cerrar-modal:hover { opacity: 1; }

.modal-body { padding: 1.6rem; }

.form-grupo { margin-bottom: 1.2rem; }
.form-grupo-doble {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}
.form-grupo label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
  font-size: 0.9rem;
  color: var(--texto);
  font-family: 'Montserrat', sans-serif;
}
.modal-input, .modal-select {
  width: 100%;
  padding: 10px 14px;
  border: 1.5px solid var(--borde);
  border-radius: 8px;
  font-size: 0.95rem;
  background: #FFFFFF;
  color: var(--texto);
  font-family: 'Montserrat', sans-serif;
  outline: none;
  transition: border-color 0.2s;
  box-sizing: border-box;
}
.modal-input:focus, .modal-select:focus { border-color: #1B396A; }
.modal-input.deshabilitado {
  background: var(--fondo);
  cursor: not-allowed;
  color: var(--gris);
}

.modal-footer {
  padding: 1rem 1.6rem;
  background: var(--fondo);
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  border-top: 1px solid var(--borde);
}

.btn-secundario {
  padding: 10px 22px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  background: #FFFFFF;
  color: var(--texto);
  border: 1px solid var(--borde);
  transition: background 0.15s;
}
.btn-secundario:hover { background: var(--fondo); }
.btn-secundario:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-eliminar {
  padding: 10px 22px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  background: #DC2626;
  color: white;
  border: none;
  transition: background 0.15s;
}
.btn-eliminar:hover { background: #B91C1C; }
.btn-eliminar:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-guardar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 22px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  background: #1B396A;
  color: white;
  border: none;
  transition: background 0.15s;
}
.btn-guardar:hover:not(:disabled) { background: #1D4ED8; }
.btn-guardar:disabled { opacity: 0.65; cursor: not-allowed; }

.spinner-btn {
  display: inline-block;
  width: 15px; height: 15px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: white;
  border-radius: 50%;
  animation: girar 0.7s linear infinite;
  flex-shrink: 0;
}

.detalle-fila {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 11px 0;
  border-bottom: 1px solid var(--borde);
  font-size: 0.95rem;
  color: var(--texto);
  font-family: 'Montserrat', sans-serif;
}
.detalle-fila:last-child { border-bottom: none; }
.detalle-label { font-weight: 600; color: var(--gris); }
.detalle-valor { font-weight: 500; }

/* ══ Animación girar ══ */
@keyframes girar {
  to { transform: rotate(360deg); }
}

/* ══════════════════════════════════════
   RESPONSIVE — AlumnosView
   Santiago Acosta — Modificaciones SICE
══════════════════════════════════════ */

/* ── Tablet (≤1024px) ── */
@media (max-width: 1024px) {
  .page-title { font-size: 1.5rem; }

  .search-group-compacto {
    flex: 0 0 200px;
    min-width: 160px;
  }

  .filter-select {
    flex: 1 1 110px;
    min-width: 100px;
    font-size: 0.82rem;
  }
}

/* ── Tablet pequeña / móvil grande (≤768px) ── */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    gap: 0.2rem;
    margin-bottom: 1rem;
  }

  .page-title { font-size: 1.35rem; }

  /* Barra de filtros en 2 filas */
  .filters-bar {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.6rem;
  }

  /* Buscador ocupa todo el ancho */
  .search-group-compacto {
    grid-column: 1 / -1;
    flex: none;
    min-width: unset;
    width: 100%;
  }

  /* Filtros en 2 columnas */
  .filter-select {
    flex: none;
    width: 100%;
    min-width: unset;
  }

  /* Botones en la misma fila */
  .btn-limpiar {
    justify-content: center;
  }

  .btn-nuevo {
    margin-left: 0;
    text-align: center;
    justify-content: center;
  }

  /* Tabla con scroll horizontal */
  .table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  /* Ocultar columna Carrera en tablet — mucha info */
  .alumnos-table th:nth-child(3),
  .alumnos-table td:nth-child(3) {
    display: none;
  }

  /* Paginación en columna */
  .paginacion {
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
  }

  .paginacion-izquierda,
  .paginacion-centro,
  .paginacion-derecha {
    width: 100%;
    justify-content: center;
  }

  /* Modal más compacto */
  .modal-content {
    max-width: 96%;
    margin: 0 0.75rem;
  }

  .modal-body { padding: 1.2rem; }
  .modal-footer { padding: 0.8rem 1.2rem; }
}

/* ── Móvil pequeño (≤480px) ── */
@media (max-width: 480px) {
  .page-title { font-size: 1.2rem; }
  .page-subtitle { font-size: 0.8rem; }

  /* Filtros en 1 columna */
  .filters-bar {
    grid-template-columns: 1fr;
  }

  .search-group-compacto {
    grid-column: 1;
  }

  /* Ocultar también columna Semestre en móvil pequeño */
  .alumnos-table th:nth-child(4),
  .alumnos-table td:nth-child(4) {
    display: none;
  }

  /* Celda acciones más compacta */
  .celda-acciones { gap: 5px; }

  .alumnos-table th,
  .alumnos-table td {
    padding: 6px 10px;
    font-size: 0.82rem;
  }

  .celda-control { font-size: 0.82rem; }

  .estatus-badge {
    padding: 3px 8px;
    font-size: 0.75rem;
  }

  /* Modal footer en columna */
  .modal-footer {
    flex-direction: column;
    gap: 0.5rem;
  }

  .modal-footer button {
    width: 100%;
    justify-content: center;
  }

  /* form-grupo-doble en columna */
  .form-grupo-doble {
    grid-template-columns: 1fr;
  }

  .modal-input,
  .modal-select {
    font-size: 16px; /* evita zoom en iOS */
  }
}

</style>
