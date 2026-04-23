<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="roles-page">
      
      <!-- ══ Encabezado de página ══ -->
      <div class="page-header">
        <h1 class="page-title">Roles del Sistema</h1>
        <span class="page-subtitle">{{ rolesFiltrados.length }} registro(s) encontrado(s)</span>
      </div>

      <!-- ══ Barra de carga global ══ -->
      <div class="barra-carga-global" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- ══ Notificación UI ══ -->
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

      <!-- ══ Barra de filtros ══ -->
      <div class="filters-bar">
        <div class="search-group">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Buscar por nombre de rol..."
            v-model="busquedaRol"
            class="search-input"
            @keydown.escape="busquedaRol = ''"
          >
          <span v-if="cargandoBusqueda" class="spinner-busqueda"></span>
        </div>

        <select v-model="filtroEstatus" class="filter-select" @change="currentPage = 1">
          <option value="">Todos los estatus</option>
          <option value="Activo">Activo</option>
          <option value="Inactivo">Inactivo</option>
        </select>

        <button class="btn-limpiar" @click="resetFiltros">
          <svg xmlns="http://www.w3.org/2000/svg" class="reset-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Limpiar
        </button>

        <button class="btn-nuevo" @click="abrirModalNuevo">
          + Nuevo Rol
        </button>
      </div>

      <!-- ══ Tabla de roles ══ -->
      <div class="table-container">

        <!-- Estado: cargando inicial -->
        <div v-if="cargando && roles.length === 0" class="estado-cargando">
          <div class="spinner-tabla"></div>
          <p>Cargando registros...</p>
        </div>

        <table
          v-else-if="paginatedRoles.length > 0"
          class="alumnos-table"
          ref="tablaRef"
          @keydown="navegarTeclado"
          tabindex="0"
        >
          <thead>
            <tr>
              <th>Nombre del Rol</th>
              <th>Descripción</th>
              <th>Estatus</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(rol, index) in paginatedRoles"
              :key="rol.id_rol || rol.id"
              :class="{ 'fila-seleccionada': filaActiva === index }"
              @click="filaActiva = index"
            >
              <td class="celda-nombre">{{ rol.nombre }}</td>
              <td class="celda-descripcion">{{ rol.descripcion || '—' }}</td>
              <td>
                <span class="estatus-badge" :class="claseEstatus(rol.estatus)">
                  {{ rol.estatus }}
                </span>
              </td>
              <td class="celda-acciones">
                <button class="btn-accion ver" @click.stop="abrirModalVer(rol)" title="Ver detalles">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Ver
                </button>
                <button class="btn-accion editar" @click.stop="abrirModalEditar(rol)" title="Editar rol">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Editar
                </button>
                <button class="btn-accion permisos" @click.stop="abrirModalPermisos(rol)" title="Gestionar permisos">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                  </svg>
                  Permisos
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
          <h3>Sin resultados</h3>
          <p>No se encontraron roles con los criterios aplicados.</p>
          <button class="btn-limpiar-vacio" @click="resetFiltros">Limpiar filtros</button>
        </div>
      </div>

      <!-- ══ Paginación ══ -->
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

    <!-- ══════════════════════════════════════
         MODAL: VER DETALLES DEL ROL
    ═══════════════════════════════════════ -->
    <div v-if="showModalVer" class="modal-overlay" @click.self="cerrarModalVer">
      <div class="modal-content modal-ver">
        <div class="modal-header">
          <h3>Detalles del Rol</h3>
          <button @click="cerrarModalVer" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">
          <div class="detalle-fila">
            <span class="detalle-label">Nombre del Rol</span>
            <span class="detalle-valor">{{ rolVer.nombre }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Descripción</span>
            <span class="detalle-valor">{{ rolVer.descripcion || '—' }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Estatus</span>
            <span class="estatus-badge" :class="claseEstatus(rolVer.estatus)">{{ rolVer.estatus }}</span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModalVer">Cerrar</button>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════
         MODAL: CREAR / EDITAR ROL
    ═══════════════════════════════════════ -->
    <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ rolEditar.id_rol ? 'Editar Rol' : 'Nuevo Rol' }}</h3>
          <button @click="cerrarModal" class="btn-cerrar-modal">×</button>
        </div>

        <div class="modal-body">
          <div class="form-grupo">
            <label>Nombre del Rol <span class="obligatorio">*</span></label>
            <input
              v-model="rolEditar.nombre"
              type="text"
              class="modal-input"
              placeholder="Ej. Administrador, Docente..."
              :class="{ 'input-error': erroresModal.nombre }"
            >
            <small v-if="erroresModal.nombre" class="mensaje-error">{{ erroresModal.nombre }}</small>
          </div>

          <div class="form-grupo">
            <label>Descripción</label>
            <textarea
              v-model="rolEditar.descripcion"
              class="modal-input modal-textarea"
              placeholder="Describe brevemente las funciones de este rol..."
              rows="3"
            ></textarea>
          </div>

          <div class="form-grupo">
            <label>Estatus <span class="obligatorio">*</span></label>
            <select v-model="rolEditar.estatus" class="modal-select">
              <option value="Activo">Activo</option>
              <option value="Inactivo">Inactivo</option>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">Cancelar</button>
          <button v-if="rolEditar.id_rol" class="btn-eliminar" @click="eliminarRol" :disabled="guardando">
            Eliminar
          </button>
          <button class="btn-guardar" @click="guardarRol" :disabled="guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════
         MODAL: GESTIÓN DE PERMISOS
         Modal más grande con permisos agrupados por módulo
    ═══════════════════════════════════════ -->
    <div v-if="showModalPermisos" class="modal-overlay" @click.self="cerrarModalPermisos">
      <div class="modal-content modal-permisos">
        <div class="modal-header">
          <div class="permisos-header-info">
            <h3>Gestión de Permisos</h3>
            <span class="permisos-rol-nombre">{{ rolPermisos.nombre }}</span>
          </div>
          <button @click="cerrarModalPermisos" class="btn-cerrar-modal">×</button>
        </div>

        <div class="modal-body modal-body-permisos">

          <!-- Bloque por módulo -->
          <div
            v-for="(modulo, indexModulo) in modulosPermisos"
            :key="indexModulo"
            class="bloque-modulo"
          >
            <!-- Separador de módulo (estilo sección FormularioAlumnoView) -->
            <div class="separador-modulo">
              <svg xmlns="http://www.w3.org/2000/svg" class="separador-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="modulo.icono" />
              </svg>
              {{ modulo.nombre }}
            </div>

            <!-- Checkboxes de permisos del módulo -->
            <div class="grilla-permisos">
              <label
                v-for="(permiso, indexPermiso) in modulo.permisos"
                :key="indexPermiso"
                class="permiso-item"
                :class="{ 'permiso-activo': permisosSeleccionados[permiso.id] }"
              >
                <input
                  type="checkbox"
                  class="permiso-checkbox"
                  v-model="permisosSeleccionados[permiso.id]"
                >
                <div class="permiso-info">
                  <span class="permiso-nombre">{{ permiso.nombre }}</span>
                  <span class="permiso-desc">{{ permiso.descripcion }}</span>
                </div>
              </label>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModalPermisos" :disabled="guardandoPermisos">
            Cancelar
          </button>
          <button class="btn-guardar" @click="guardarPermisos" :disabled="guardandoPermisos">
            <span v-if="guardandoPermisos" class="spinner-btn"></span>
            {{ guardandoPermisos ? 'Guardando...' : 'Guardar permisos' }}
          </button>
        </div>
      </div>
    </div>

  </MainLayout>
</template>



<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

// ── URL base del backend (variable de entorno) ──────────────────────
const API_URL = import.meta.env.VITE_API_URL

// ── Estado principal ────────────────────────────────────────────────
const roles            = ref([])
const cargando         = ref(false)
const cargandoBusqueda = ref(false)
const guardando        = ref(false)
const guardandoPermisos = ref(false)
const filaActiva       = ref(-1)
const tablaRef         = ref(null)

// ── Filtros y paginación ────────────────────────────────────────────
const busquedaRol    = ref('')
const filtroEstatus  = ref('')
const filasPorPagina = ref(10)
const currentPage    = ref(1)

// ── Notificación ────────────────────────────────────────────────────
const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  notificacion.value = { visible: true, mensaje, tipo }
  setTimeout(() => { notificacion.value.visible = false }, 3500)
}

// ── Módulos y permisos ───────────────────────────────────────────────
const modulosPermisos      = ref([])
const permisosSeleccionados = ref({})

// Endpoint: GET /api/permisos
const cargarPermisos = async () => {
  try {
    const response = await fetch(`${API_URL}/api/permisos`)
    const data = await response.json()

    const agrupados = {}
    data.forEach(p => {
      if (!agrupados[p.modulo]) {
        agrupados[p.modulo] = { nombre: p.modulo, permisos: [] }
      }
      agrupados[p.modulo].permisos.push({
        id:          p.id_permiso,
        nombre:      p.nombre,
        descripcion: p.descripcion
      })
    })

    modulosPermisos.value = Object.values(agrupados)
  } catch (error) {
    console.error('Error cargando permisos:', error)
  }
}

// ── Cargar roles ─────────────────────────────────────────────────────
// Endpoint: GET /api/roles
const cargarRoles = async () => {
  cargando.value = true
  try {
    const response = await fetch(`${API_URL}/api/roles`)
    if (!response.ok) throw new Error('Error del servidor')
    roles.value = await response.json()
    console.log('✅ Roles cargados:', roles.value.length, 'registros')
  } catch (error) {
    console.error('❌ Error cargando roles:', error)
    mostrarNotificacion('No se pudo cargar la lista de roles. Verifica que el servidor esté activo.', 'error')
  } finally {
    cargando.value = false
  }
}

onMounted(() => {
  cargarRoles()
  cargarPermisos()
})

// ── Modales ──────────────────────────────────────────────────────────
const showModalVer     = ref(false)
const showModal        = ref(false)
const showModalPermisos = ref(false)

const rolVer      = ref({})
const rolEditar   = ref({})
const rolPermisos = ref({})
const erroresModal = ref({})

const abrirModalVer = (rol) => {
  rolVer.value = { ...rol }
  showModalVer.value = true
}
const cerrarModalVer = () => { showModalVer.value = false }

const abrirModalNuevo = () => {
  rolEditar.value = { nombre: '', descripcion: '', estatus: 'Activo' }
  erroresModal.value = {}
  showModal.value = true
}

const abrirModalEditar = (rol) => {
  rolEditar.value = { ...rol }
  erroresModal.value = {}
  showModal.value = true
}
const cerrarModal = () => { showModal.value = false }

// Endpoint: GET /api/roles/{id}/permisos
const abrirModalPermisos = async (rol) => {
  rolPermisos.value = { ...rol }
  try {
    const res = await fetch(`${API_URL}/api/roles/${rol.id_rol}/permisos`)
    const data = await res.json()

    const estado = {}
    modulosPermisos.value.forEach(modulo => {
      modulo.permisos.forEach(permiso => {
        estado[permiso.id] = data.permisos.includes(permiso.id)
      })
    })

    permisosSeleccionados.value = estado
    showModalPermisos.value = true
  } catch (error) {
    console.error(error)
    mostrarNotificacion('Error al cargar permisos', 'error')
  }
}
const cerrarModalPermisos = () => { showModalPermisos.value = false }

// ── Guardar Rol ───────────────────────────────────────────────────────
// Endpoint: POST /api/roles  |  PUT /api/roles/{id}
const validarModal = () => {
  erroresModal.value = {}
  if (!rolEditar.value.nombre?.trim()) {
    erroresModal.value.nombre = 'El nombre del rol es obligatorio'
  }
  return Object.keys(erroresModal.value).length === 0
}

const guardarRol = async () => {
  if (!validarModal()) return

  const esEdicion = !!rolEditar.value.id_rol
  const url    = esEdicion
    ? `${API_URL}/api/roles/${rolEditar.value.id_rol}`
    : `${API_URL}/api/roles`
  const metodo = esEdicion ? 'PUT' : 'POST'

  const payload = {
    nombre:      rolEditar.value.nombre.trim(),
    descripcion: rolEditar.value.descripcion?.trim() || null,
    estatus:     rolEditar.value.estatus
  }

  guardando.value = true
  try {
    const response = await fetch(url, {
      method:  metodo,
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(payload)
    })

    if (response.ok) {
      await cargarRoles()
      cerrarModal()
      mostrarNotificacion(
        esEdicion ? 'Rol actualizado correctamente.' : 'Rol creado correctamente.',
        'exito'
      )
    } else {
      const data = await response.json()
      mostrarNotificacion(data.error || 'Error al guardar el rol', 'error')
    }
  } catch (error) {
    console.error(error)
    mostrarNotificacion('Ocurrió un error al guardar el rol.', 'error')
  } finally {
    guardando.value = false
  }
}

// Endpoint: DELETE /api/roles/{id}
const eliminarRol = async () => {
  if (!rolEditar.value.id_rol) return
  if (!confirm('¿Estás seguro de eliminar este rol?')) return

  guardando.value = true
  try {
    const response = await fetch(`${API_URL}/api/roles/${rolEditar.value.id_rol}`, {
      method: 'DELETE'
    })
    if (response.ok) {
      await cargarRoles()
      cerrarModal()
      mostrarNotificacion('Rol eliminado correctamente.', 'exito')
    } else {
      mostrarNotificacion('No se pudo eliminar el rol.', 'error')
    }
  } catch (error) {
    mostrarNotificacion('Error al eliminar el rol.', 'error')
  } finally {
    guardando.value = false
  }
}

// Endpoint: PUT /api/roles/{id}/permisos
const guardarPermisos = async () => {
  const id = rolPermisos.value.id_rol
  if (!id) return

  const permisosActivos = Object.entries(permisosSeleccionados.value)
    .filter(([, activo]) => activo)
    .map(([id]) => parseInt(id))

  guardandoPermisos.value = true
  try {
    const response = await fetch(`${API_URL}/api/roles/${id}/permisos`, {
      method:  'PUT',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ permisos: permisosActivos })
    })

    if (response.ok) {
      cerrarModalPermisos()
      mostrarNotificacion('Permisos actualizados correctamente.', 'exito')
    } else {
      mostrarNotificacion('Error al guardar los permisos.', 'error')
    }
  } catch (error) {
    mostrarNotificacion('Error al guardar los permisos.', 'error')
  } finally {
    guardandoPermisos.value = false
  }
}

// ── Helpers ───────────────────────────────────────────────────────────
const claseEstatus = (estatus) => {
  if (!estatus) return 'inactivo'
  return String(estatus).toLowerCase()
}

const normalize = (text) => {
  if (!text) return ''
  return String(text).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
}

// ── Filtrado y paginación ─────────────────────────────────────────────
const rolesFiltrados = computed(() => {
  return roles.value.filter(rol => {
    const coincideBusqueda = !busquedaRol.value ||
      normalize(rol.nombre).includes(normalize(busquedaRol.value)) ||
      normalize(rol.descripcion).includes(normalize(busquedaRol.value))

    const coincideEstatus = !filtroEstatus.value ||
      normalize(rol.estatus) === normalize(filtroEstatus.value)

    return coincideBusqueda && coincideEstatus
  })
})

const totalPages = computed(() =>
  Math.ceil(rolesFiltrados.value.length / filasPorPagina.value) || 1
)
const paginatedRoles = computed(() => {
  const start = (currentPage.value - 1) * filasPorPagina.value
  return rolesFiltrados.value.slice(start, start + filasPorPagina.value)
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

const resetFiltros = () => {
  busquedaRol.value   = ''
  filtroEstatus.value = ''
  currentPage.value   = 1
  filaActiva.value    = -1
}

// ── Navegación por teclado ────────────────────────────────────────────
const navegarTeclado = (e) => {
  const total = paginatedRoles.value.length
  if (total === 0) return
  if (e.key === 'ArrowDown') { e.preventDefault(); filaActiva.value = Math.min(filaActiva.value + 1, total - 1) }
  else if (e.key === 'ArrowUp') { e.preventDefault(); filaActiva.value = Math.max(filaActiva.value - 1, 0) }
  else if (e.key === 'Enter' && filaActiva.value >= 0) { e.preventDefault(); abrirModalVer(paginatedRoles.value[filaActiva.value]) }
}

watch(busquedaRol, () => {
  cargandoBusqueda.value = true
  setTimeout(() => {
    cargandoBusqueda.value = false
    currentPage.value = 1
  }, 300)
})
</script>




<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* ══ Variables locales ══ */
.roles-page {
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

/* ══ Encabezado ══ */
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

/* ══ Barra de carga global ══ */
.barra-carga-global {
  height: 3px;
  background: transparent;
  border-radius: 2px;
  margin-bottom: 1rem;
  overflow: hidden;
  opacity: 0;
  transition: opacity 0.3s;
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

/* ══ Notificación UI ══ */
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
.notificacion-ui.exito { background: #DCFCE7; color: #16A34A; border: 1px solid #86EFAC; }
.notificacion-ui.error { background: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5; }
.notif-icono { width: 20px; height: 20px; flex-shrink: 0; }

.notif-fade-enter-active, .notif-fade-leave-active { transition: all 0.35s ease; }
.notif-fade-enter-from, .notif-fade-leave-to { opacity: 0; transform: translateY(-8px); }

/* ══ Barra de filtros ══ */
.filters-bar {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.2rem;
  flex-wrap: wrap;
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
.search-input:focus { border-color: #1B396A; box-shadow: 0 0 0 3px #DBEAFE; }
.search-input::placeholder { color: #9CA3AF; }
.search-icon-svg {
  position: absolute;
  left: 13px; top: 50%;
  transform: translateY(-50%);
  width: 18px; height: 18px;
  stroke: var(--gris);
  pointer-events: none;
}
.spinner-busqueda {
  position: absolute;
  right: 12px; top: 50%;
  transform: translateY(-50%);
  width: 16px; height: 16px;
  border: 2px solid #E5E7EB;
  border-top-color: #1B396A;
  border-radius: 50%;
  animation: girar 0.7s linear infinite;
}
.filter-select {
  padding: 10px 12px;
  border: 1px solid var(--borde);
  border-radius: 8px;
  font-size: 0.92rem;
  flex: 1 1 160px;
  min-width: 140px;
  background: #FFFFFF;
  color: var(--texto);
  font-family: 'Montserrat', sans-serif;
  cursor: pointer;
  outline: none;
}
.filter-select:focus { border-color: #1B396A; }
.btn-limpiar {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #FFFFFF;
  color: var(--texto);
  border: 1px solid var(--borde);
  padding: 10px 16px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.92rem;
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
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  font-family: 'Montserrat', sans-serif;
  font-size: 0.92rem;
  transition: background 0.2s;
  margin-left: auto;
}
.btn-nuevo:hover { background: #1D4ED8; }

/* ══ Tabla ══ */
.table-container {
  background: #FFFFFF;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border: 1px solid var(--borde);
}
.alumnos-table { width: 100%; border-collapse: collapse; outline: none; }
.alumnos-table th {
  background: var(--fondo);
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  font-size: 0.88rem;
  color: var(--texto);
  border-bottom: 1px solid var(--borde);
  font-family: 'Montserrat', sans-serif;
  white-space: nowrap;
}
.alumnos-table td {
  padding: 11px 16px;
  border-bottom: 1px solid var(--borde);
  color: var(--texto);
  font-size: 0.93rem;
  font-family: 'Montserrat', sans-serif;
}
.alumnos-table tbody tr { transition: background 0.15s; cursor: pointer; }
.alumnos-table tbody tr:hover { background: #F8FAFC; }
.alumnos-table tbody tr:last-child td { border-bottom: none; }
.fila-seleccionada { background: #DBEAFE !important; }

.celda-nombre      { font-weight: 600; color: #1B396A; }
.celda-descripcion { color: var(--gris); font-size: 0.88rem; max-width: 320px; }

/* Badges de estatus */
.estatus-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.83rem;
  font-weight: 600;
  font-family: 'Montserrat', sans-serif;
}
.estatus-badge.activo   { background: #DCFCE7; color: #16A34A; }
.estatus-badge.inactivo { background: #FEE2E2; color: #DC2626; }

/* Botones de acción */
.celda-acciones { display: flex; gap: 6px; align-items: center; }
.btn-accion {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 0.83rem;
  cursor: pointer;
  font-weight: 600;
  font-family: 'Montserrat', sans-serif;
  transition: background 0.15s;
  white-space: nowrap;
}
.btn-accion svg { width: 14px; height: 14px; }
.btn-accion.ver    { background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); }
.btn-accion.ver:hover { background: var(--fondo); }
.btn-accion.editar { background: #1B396A; color: white; border: 1px solid #1B396A; }
.btn-accion.editar:hover { background: #1D4ED8; border-color: #1D4ED8; }
.btn-accion.permisos { background: #DBEAFE; color: #1B396A; border: 1px solid #BFDBFE; }
.btn-accion.permisos:hover { background: #BFDBFE; }

/* Estados vacíos / carga */
.estado-vacio, .estado-cargando {
  text-align: center;
  padding: 3.5rem 2rem;
  color: var(--gris);
}
.icono-vacio { width: 56px; height: 56px; stroke: #9CA3AF; margin-bottom: 12px; }
.estado-vacio h3 { font-size: 1.2rem; color: var(--texto); margin: 0 0 6px; }
.estado-vacio p  { font-size: 0.93rem; margin: 0 0 1.2rem; }
.btn-limpiar-vacio {
  background: #FFFFFF; color: var(--texto);
  border: 1px solid var(--borde); padding: 9px 20px;
  border-radius: 8px; font-weight: 500; cursor: pointer;
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

/* ══ Paginación ══ */
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
.paginacion-derecha { display: flex; align-items: center; gap: 8px; }
.select-filas {
  border: 1px solid var(--borde); border-radius: 6px;
  padding: 4px 8px; font-size: 0.9rem;
  background: #FFFFFF; font-family: 'Montserrat', sans-serif;
}
.btn-pag {
  padding: 5px 11px;
  border: 1px solid var(--borde);
  background: #FFFFFF; border-radius: 6px;
  cursor: pointer; color: var(--texto);
  font-family: 'Montserrat', sans-serif;
  font-size: 0.9rem; transition: background 0.15s;
}
.btn-pag:hover:not(:disabled) { background: var(--fondo); }
.btn-pag:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-pag.activo { background: #1B396A; color: white; border-color: #1B396A; }

/* ══ Modales ══ */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.55);
  display: flex; align-items: center; justify-content: center;
  z-index: 2000;
}
.modal-content {
  background: #FFFFFF;
  width: 520px; max-width: 92%;
  border-radius: 14px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.18);
  overflow: hidden;
  border: 1px solid var(--borde);
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

/* Modal de permisos más grande */
.modal-permisos {
  width: 780px;
  max-width: 95%;
}

.modal-header {
  background: #1B396A;
  color: white;
  padding: 1.1rem 1.6rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}
.modal-header h3 {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 700;
  font-family: 'Montserrat', sans-serif;
}
.permisos-header-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.permisos-rol-nombre {
  font-size: 0.85rem;
  opacity: 0.85;
  font-weight: 400;
}
.btn-cerrar-modal {
  background: none; border: none;
  color: white; font-size: 1.7rem;
  cursor: pointer; line-height: 1;
  opacity: 0.85; transition: opacity 0.15s;
}
.btn-cerrar-modal:hover { opacity: 1; }

.modal-body {
  padding: 1.6rem;
  overflow-y: auto;
  flex: 1;
}
.modal-body-permisos { padding: 1.2rem 1.6rem; }

/* Grupos de formulario en modales */
.form-grupo { margin-bottom: 1.2rem; }
.form-grupo label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
  font-size: 0.9rem;
  color: var(--texto);
  font-family: 'Montserrat', sans-serif;
}
.obligatorio { color: #DC2626; }
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
.modal-textarea { resize: vertical; min-height: 80px; }
.input-error { border-color: #DC2626 !important; }
.mensaje-error {
  color: #DC2626; font-size: 0.82rem;
  margin-top: 4px; display: block;
  font-family: 'Montserrat', sans-serif;
}

/* Detalles del modal ver */
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

/* ══ Permisos por módulo ══ */
.bloque-modulo { margin-bottom: 1.4rem; }

.separador-modulo {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #6B7280;
  padding: 6px 0;
  border-bottom: 1px solid var(--borde);
  margin-bottom: 0.8rem;
  font-family: 'Montserrat', sans-serif;
}
.separador-icono { width: 16px; height: 16px; stroke: #6B7280; flex-shrink: 0; }

.grilla-permisos {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.6rem;
}

.permiso-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 8px;
  border: 1px solid var(--borde);
  cursor: pointer;
  transition: background 0.15s, border-color 0.15s;
  background: #FFFFFF;
}
.permiso-item:hover { background: #F8FAFC; }
.permiso-activo {
  background: #EFF6FF !important;
  border-color: #BFDBFE !important;
}

.permiso-checkbox {
  width: 16px; height: 16px;
  margin-top: 2px;
  cursor: pointer;
  accent-color: #1B396A;
  flex-shrink: 0;
}

.permiso-info { display: flex; flex-direction: column; gap: 2px; }
.permiso-nombre {
  font-size: 0.88rem;
  font-weight: 600;
  color: var(--texto);
  font-family: 'Montserrat', sans-serif;
}
.permiso-desc {
  font-size: 0.78rem;
  color: var(--gris);
  font-family: 'Montserrat', sans-serif;
}

/* ══ Footer de modales ══ */
.modal-footer {
  padding: 1rem 1.6rem;
  background: var(--fondo);
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  border-top: 1px solid var(--borde);
  flex-shrink: 0;
}
.btn-secundario {
  padding: 10px 22px; border-radius: 8px;
  font-weight: 600; cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  background: #FFFFFF; color: var(--texto);
  border: 1px solid var(--borde);
  transition: background 0.15s;
}
.btn-secundario:hover { background: var(--fondo); }
.btn-secundario:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-eliminar {
  padding: 10px 22px; border-radius: 8px;
  font-weight: 600; cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  background: #DC2626; color: white; border: none;
  transition: background 0.15s;
}
.btn-eliminar:hover { background: #B91C1C; }
.btn-eliminar:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-guardar {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 22px; border-radius: 8px;
  font-weight: 600; cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  background: #1B396A; color: white; border: none;
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

@keyframes girar { to { transform: rotate(360deg); } }
</style>
