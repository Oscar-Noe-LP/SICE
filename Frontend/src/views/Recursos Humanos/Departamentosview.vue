<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="alumnos-page">

      <!-- ── Header ── -->
      <div class="page-header">
        <h1 class="page-title">Departamentos</h1>
        <span class="page-subtitle">{{ departamentosFiltrados.length }} registro(s) encontrado(s)</span>
      </div>

      <!-- ── Barra de carga ── -->
      <div class="barra-carga-global" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- ── Toast ── -->
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

      <!-- ── Filtros ── -->
      <div class="filters-bar">
        <div class="search-group">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Buscar por nombre — presiona Enter para buscar"
            v-model="busquedaInput"
            class="search-input"
            @keydown.enter="aplicarBusqueda"
            @keydown.escape="limpiarBusqueda"
          >
          <span v-if="busquedaActiva" class="badge-busqueda-activa" title="Búsqueda activa">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:12px;height:12px;stroke:#1B396A">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
          </span>
        </div>

        <button class="btn-buscar-tabla" @click="aplicarBusqueda" :disabled="!busquedaInput.trim()">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:15px;height:15px;stroke:#fff">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          Buscar
        </button>

        <button class="btn-limpiar" @click="resetFiltros">
          <svg xmlns="http://www.w3.org/2000/svg" class="reset-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Limpiar
        </button>

        <button class="btn-nuevo" @click="abrirModalNuevo">+ Nuevo Departamento</button>
      </div>

      <!-- ── Tabla ── -->
      <div class="table-container">

        <div v-if="cargando && departamentos.length === 0" class="estado-cargando">
          <div class="spinner-tabla"></div>
          <p>Cargando registros...</p>
        </div>

        <table v-else-if="departamentosPaginados.length > 0" class="alumnos-table" tabindex="0">
          <thead>
            <tr>
              <th>Nombre del Departamento</th>
              <th>Descripción</th>
              <th>Empleados</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(dep, index) in departamentosPaginados"
              :key="dep.id_departamento"
              :class="{ 'fila-seleccionada': filaActiva === index }"
              @click="filaActiva = index"
            >
              <td class="celda-control">{{ dep.nombre_departamento }}</td>
              <td class="celda-descripcion">{{ dep.descripcion || '—' }}</td>
              <td class="celda-semestre">{{ dep.total_empleados ?? '—' }}</td>
              <td class="celda-acciones">
                <button class="btn-accion ver" @click.stop="abrirModalVer(dep)" title="Ver detalles">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Ver
                </button>
                <button class="btn-accion editar" @click.stop="abrirModalEditar(dep)" title="Editar">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Editar
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          <h3>Sin resultados</h3>
          <p>No se encontraron departamentos con los criterios aplicados.</p>
          <button class="btn-limpiar-vacio" @click="resetFiltros">Limpiar filtros</button>
        </div>
      </div>

      <!-- ── Paginación ── -->
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
          <button
            v-for="page in visiblePages" :key="page"
            class="btn-pag" :class="{ activo: page === currentPage }"
            @click="goToPage(page)"
          >{{ page }}</button>
          <button class="btn-pag" @click="nextPage" :disabled="currentPage === totalPages">›</button>
        </div>
      </div>

      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>

    </div><!-- fin .alumnos-page -->

    <!-- ══════════════════════════════════════════
         MODAL VER
    ══════════════════════════════════════════ -->
    <div v-if="showModalVer" class="modal-overlay" @click.self="showModalVer = false">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Datos del Departamento</h3>
          <button @click="showModalVer = false" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body" v-if="departamentoVer">
          <div class="detalle-fila">
            <span class="detalle-label">Nombre</span>
            <span class="detalle-valor">{{ departamentoVer.nombre_departamento }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Descripción</span>
            <span class="detalle-valor">{{ departamentoVer.descripcion || 'Sin descripción' }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Total de empleados</span>
            <span class="detalle-valor">{{ departamentoVer.total_empleados ?? '—' }}</span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="showModalVer = false">Cerrar</button>
          <button class="btn-guardar" @click="abrirModalEditar(departamentoVer); showModalVer = false">Editar</button>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════
         MODAL CREAR / EDITAR
    ══════════════════════════════════════════ -->
    <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ form.id_departamento ? 'Editar Departamento' : 'Nuevo Departamento' }}</h3>
          <button @click="cerrarModal" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">

          <div class="form-grupo">
            <label>Nombre del Departamento *</label>
            <input
              v-model="form.nombre_departamento"
              type="text"
              class="modal-input"
              placeholder="Ej: Sistemas Computacionales"
              :disabled="guardando"
            />
            <p v-if="errores.nombre" class="error-campo">{{ errores.nombre }}</p>
          </div>

          <div class="form-grupo">
            <label>Descripción</label>
            <textarea
              v-model="form.descripcion"
              class="modal-textarea"
              rows="3"
              placeholder="Descripción opcional del departamento..."
              :disabled="guardando"
            ></textarea>
          </div>

        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">Cancelar</button>
          <button v-if="form.id_departamento" class="btn-eliminar" @click="pedirConfirmarEliminar" :disabled="guardando">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;stroke:#fff">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Eliminar
          </button>
          <button class="btn-guardar" @click="guardar" :disabled="guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;stroke:#fff">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════
         MODAL CONFIRMAR ELIMINAR
    ══════════════════════════════════════════ -->
    <div v-if="showModalEliminar" class="modal-overlay" @click.self="showModalEliminar = false">
      <div class="modal-content" style="max-width:460px">
        <div class="modal-header" style="background:#DC2626">
          <h3>Confirmar eliminación</h3>
          <button @click="showModalEliminar = false" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">
          <p style="font-size:.95rem;color:#1A1A1A;line-height:1.6;margin:0;font-family:'Montserrat',sans-serif">
            ¿Estás seguro de que deseas eliminar el departamento
            <strong>{{ departamentoAEliminar?.nombre_departamento }}</strong>?
            Esta acción no se puede deshacer. Los empleados y adscripciones relacionados perderán la referencia a este departamento.
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="showModalEliminar = false" :disabled="guardando">Cancelar</button>
          <button class="btn-eliminar" @click="confirmarEliminar" :disabled="guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;stroke:#fff">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            {{ guardando ? 'Eliminando...' : 'Sí, eliminar' }}
          </button>
        </div>
      </div>
    </div>

  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

// ─────────────────────────────────────────────────────────────────────────────
// BASE URL — el equipo de back solo cambia esta constante
// ─────────────────────────────────────────────────────────────────────────────
const API = `${import.meta.env.VITE_API_URL}/api`

// ─────────────────────────────────────────────────────────────────────────────
// ESTADOS
// ─────────────────────────────────────────────────────────────────────────────
const departamentos  = ref([])
const cargando       = ref(false)
const guardando      = ref(false)
const filaActiva     = ref(-1)

// Búsqueda: solo aplica al presionar Enter o el botón Buscar
const busquedaInput  = ref('')
const busquedaActiva = ref('')

const filasPorPagina = ref(10)
const currentPage    = ref(1)

const showModalVer      = ref(false)
const showModal         = ref(false)
const showModalEliminar = ref(false)
const departamentoVer      = ref(null)
const departamentoAEliminar = ref(null)

const errores = ref({ nombre: '' })

const formVacio = () => ({
  id_departamento:    null,
  nombre_departamento: '',
  descripcion:        ''
})
const form = ref(formVacio())

const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null
const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

// ─────────────────────────────────────────────────────────────────────────────
// CARGA DE DATOS
// ─────────────────────────────────────────────────────────────────────────────

/*
 * GET /api/departamentos
 * Respuesta esperada (array):
 * [{
 *   id_departamento:     number,
 *   nombre_departamento: string,
 *   descripcion:         string | null,
 *   total_empleados:     number   <- cuántos empleados pertenecen a este depto
 * }]
 */
const cargarDepartamentos = async () => {
  cargando.value = true
  try {
    const res = await fetch(`${API}/departamentos`)
    if (!res.ok) throw new Error(`Error ${res.status}`)
    departamentos.value = await res.json()
    console.log('✅ Departamentos cargados:', departamentos.value.length)
  } catch (err) {
    console.error('❌ cargarDepartamentos:', err)
    mostrarNotificacion('No se pudo cargar la lista de departamentos.', 'error')
  } finally {
    cargando.value = false
  }
}

onMounted(cargarDepartamentos)

// ─────────────────────────────────────────────────────────────────────────────
// CRUD
// ─────────────────────────────────────────────────────────────────────────────

/*
 * POST /api/departamentos
 * Body:    { nombre_departamento, descripcion }
 * Retorna: { id_departamento, nombre_departamento, descripcion, total_empleados }
 *
 * PUT /api/departamentos/:id_departamento
 * Body:    { nombre_departamento, descripcion }
 * Retorna: { id_departamento, ...campos actualizados }
 */
const guardar = async () => {
  errores.value.nombre = ''

  if (!form.value.nombre_departamento.trim()) {
    errores.value.nombre = 'El nombre del departamento es requerido.'
    mostrarNotificacion('El nombre es requerido.', 'error')
    return
  }

  guardando.value = true
  const esEdicion = !!form.value.id_departamento
  try {
    const url  = esEdicion
      ? `${API}/departamentos/${form.value.id_departamento}`
      : `${API}/departamentos`
    const meth = esEdicion ? 'PUT' : 'POST'
    const body = {
      nombre_departamento: form.value.nombre_departamento.trim(),
      descripcion:         form.value.descripcion.trim() || null
    }

    console.log(`🔵 ${meth}`, url, body)
    const res  = await fetch(url, {
      method: meth,
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(body)
    })
    const data = await res.json()
    console.log('🟢 Respuesta:', data)

    if (!res.ok) throw new Error(JSON.stringify(data))

    await cargarDepartamentos()
    cerrarModal()
    mostrarNotificacion(
      esEdicion ? 'Departamento actualizado correctamente.' : 'Departamento creado correctamente.',
      'exito'
    )
  } catch (err) {
    console.error('❌ guardar:', err)
    mostrarNotificacion('Ocurrió un error al guardar el departamento.', 'error')
  } finally {
    guardando.value = false
  }
}

/*
 * DELETE /api/departamentos/:id_departamento
 * Retorna: { message: "Eliminado correctamente" }
 */
const pedirConfirmarEliminar = () => {
  departamentoAEliminar.value = { ...form.value }
  showModal.value         = false
  showModalEliminar.value = true
}

const confirmarEliminar = async () => {
  guardando.value = true
  try {
    const res  = await fetch(`${API}/departamentos/${departamentoAEliminar.value.id_departamento}`, {
      method: 'DELETE',
      headers: { 'Accept': 'application/json' }
    })
    const data = await res.json()
    console.log('🗑️ DELETE departamento:', data)

    if (!res.ok) throw new Error(JSON.stringify(data))

    await cargarDepartamentos()
    showModalEliminar.value = false
    mostrarNotificacion('Departamento eliminado correctamente.', 'exito')
  } catch (err) {
    console.error('❌ confirmarEliminar:', err)
    mostrarNotificacion('Ocurrió un error al eliminar el departamento.', 'error')
  } finally {
    guardando.value = false
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// MODALES
// ─────────────────────────────────────────────────────────────────────────────
const abrirModalNuevo  = () => { form.value = formVacio(); errores.value.nombre = ''; showModal.value = true }
const abrirModalVer    = (dep) => { departamentoVer.value = dep; showModalVer.value = true }
const abrirModalEditar = (dep) => {
  form.value = {
    id_departamento:     dep.id_departamento,
    nombre_departamento: dep.nombre_departamento,
    descripcion:         dep.descripcion ?? ''
  }
  errores.value.nombre = ''
  showModal.value = true
}
const cerrarModal = () => { showModal.value = false }

// ─────────────────────────────────────────────────────────────────────────────
// BÚSQUEDA Y FILTROS
// ─────────────────────────────────────────────────────────────────────────────
const normalize = t => (t || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')

const aplicarBusqueda = () => {
  const q = busquedaInput.value.trim()
  if (!q) return
  busquedaActiva.value = q
  currentPage.value    = 1
}

const limpiarBusqueda = () => {
  busquedaInput.value  = ''
  busquedaActiva.value = ''
  currentPage.value    = 1
}

const resetFiltros = () => {
  limpiarBusqueda()
  filaActiva.value = -1
}

const departamentosFiltrados = computed(() => {
  const q = normalize(busquedaActiva.value)
  if (!q) return departamentos.value
  return departamentos.value.filter(d =>
    normalize(d.nombre_departamento).includes(q) ||
    normalize(d.descripcion || '').includes(q)
  )
})

const totalPages = computed(() =>
  Math.ceil(departamentosFiltrados.value.length / filasPorPagina.value) || 1
)

const departamentosPaginados = computed(() => {
  const s = (currentPage.value - 1) * filasPorPagina.value
  return departamentosFiltrados.value.slice(s, s + filasPorPagina.value)
})

const visiblePages = computed(() => {
  const t = totalPages.value, c = currentPage.value
  if (t <= 7) return Array.from({ length: t }, (_, i) => i + 1)
  const p = new Set([1, t, c, c - 1, c + 1].filter(x => x >= 1 && x <= t))
  return [...p].sort((a, b) => a - b)
})

const goToPage  = (p) => { currentPage.value = p; filaActiva.value = -1 }
const prevPage  = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage  = () => { if (currentPage.value < totalPages.value) currentPage.value++ }
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* ════════════════════════════════════════════
   VARIABLES — dentro de .alumnos-page
   Los modales usan colores hardcoded
════════════════════════════════════════════ */
.alumnos-page {
  --azul:       #1B396A;
  --azul-hover: #1D4ED8;
  --borde:      #E5E7EB;
  --fondo:      #F5F5F5;
  --texto:      #1A1A1A;
  --gris:       #6B7280;
  max-width: 100%;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
  padding-bottom: 2rem;
}

/* ── Header ── */
.page-header   { display: flex; align-items: baseline; gap: 1rem; margin-bottom: 1.2rem; }
.page-title    { color: #1A1A1A; font-size: 1.75rem; font-weight: 700; letter-spacing: -.02em; margin: 0; }
.page-subtitle { font-size: .9rem; color: #6B7280; font-weight: 500; }

/* ── Barra carga ── */
.barra-carga-global { height: 3px; background: transparent; border-radius: 2px; margin-bottom: 1rem; overflow: hidden; opacity: 0; transition: opacity .3s; }
.barra-carga-global.visible { opacity: 1; }
.barra-progreso { height: 100%; width: 40%; background: #1B396A; border-radius: 2px; animation: deslizar 1.2s ease-in-out infinite; }
@keyframes deslizar { 0%{transform:translateX(-100%)} 100%{transform:translateX(350%)} }

/* ── Toast ── */
.notificacion-ui { display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 10px; font-size: .93rem; font-weight: 500; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.notificacion-ui.exito { background: #DCFCE7; color: #16A34A; border: 1px solid #86EFAC; }
.notificacion-ui.error { background: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5; }
.notif-icono { width: 20px; height: 20px; flex-shrink: 0; }
.notif-fade-enter-active, .notif-fade-leave-active { transition: all .35s ease; }
.notif-fade-enter-from, .notif-fade-leave-to { opacity: 0; transform: translateY(-8px); }

/* ── Filtros ── */
.filters-bar { display: flex; align-items: center; gap: .75rem; margin-bottom: 1.2rem; flex-wrap: wrap; }

.search-group { position: relative; flex: 0 0 340px; min-width: 220px; }
.search-input {
  width: 100%; padding: 10px 14px 10px 42px;
  border: 1px solid #E5E7EB; border-radius: 8px;
  font-size: .93rem; background: #FFFFFF; color: #1A1A1A;
  font-family: 'Montserrat', sans-serif; outline: none;
  transition: border-color .2s, box-shadow .2s; box-sizing: border-box;
}
.search-input:focus { border-color: #1B396A; box-shadow: 0 0 0 3px #DBEAFE; }
.search-input::placeholder { color: #9CA3AF; }
.search-icon-svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; stroke: #6B7280; pointer-events: none; }
.badge-busqueda-activa { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: #DBEAFE; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; }

.btn-buscar-tabla {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: .92rem;
  cursor: pointer; font-family: 'Montserrat', sans-serif; white-space: nowrap;
  background: #1B396A; color: #FFFFFF; border: 2px solid #1B396A;
  transition: background .15s;
}
.btn-buscar-tabla:hover:not(:disabled) { background: #1D4ED8; border-color: #1D4ED8; }
.btn-buscar-tabla:disabled { opacity: .45; cursor: not-allowed; }

.btn-limpiar { display: flex; align-items: center; gap: 6px; background: #FFFFFF; color: #1A1A1A; border: 1px solid #E5E7EB; padding: 10px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: .92rem; white-space: nowrap; font-family: 'Montserrat', sans-serif; transition: background .15s; }
.btn-limpiar:hover { background: #F5F5F5; }
.reset-icon { width: 16px; height: 16px; stroke: #6B7280; }

.btn-nuevo { background: #1B396A; color: #FFFFFF; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; white-space: nowrap; font-family: 'Montserrat', sans-serif; font-size: .92rem; transition: background .2s; margin-left: auto; }
.btn-nuevo:hover { background: #1D4ED8; }

/* ── Tabla ── */
.table-container { background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,.05); border: 1px solid #E5E7EB; }
.alumnos-table   { width: 100%; border-collapse: collapse; outline: none; }
.alumnos-table th { background: #F5F5F5; padding: 12px 16px; text-align: left; font-weight: 600; font-size: .88rem; color: #1A1A1A; border-bottom: 1px solid #E5E7EB; font-family: 'Montserrat', sans-serif; white-space: nowrap; }
.alumnos-table td { padding: 11px 16px; border-bottom: 1px solid #E5E7EB; color: #1A1A1A; font-size: .93rem; font-family: 'Montserrat', sans-serif; }
.alumnos-table tbody tr { transition: background .15s; cursor: pointer; }
.alumnos-table tbody tr:hover { background: #F8FAFC; }
.alumnos-table tbody tr:last-child td { border-bottom: none; }
.fila-seleccionada { background: #DBEAFE !important; }

.celda-control     { font-weight: 600; color: #1B396A; }
.celda-descripcion { color: #6B7280; font-size: .88rem; max-width: 360px; }
.celda-semestre    { text-align: center; }

/* ── Botones tabla ── */
.celda-acciones { display: flex; gap: 7px; align-items: center; }
.btn-accion { display: flex; align-items: center; gap: 5px; padding: 6px 13px; border-radius: 6px; font-size: .85rem; cursor: pointer; font-weight: 600; font-family: 'Montserrat', sans-serif; transition: background .15s; white-space: nowrap; }
.btn-accion svg { width: 14px; height: 14px; }
.btn-accion.ver    { background: #FFFFFF; color: #1A1A1A; border: 1px solid #E5E7EB; }
.btn-accion.ver:hover { background: #F5F5F5; }
.btn-accion.editar { background: #1B396A; color: #FFFFFF; border: 1px solid #1B396A; }
.btn-accion.editar svg { stroke: #FFFFFF; }
.btn-accion.editar:hover { background: #1D4ED8; border-color: #1D4ED8; }

/* ── Estado vacío / cargando ── */
.estado-vacio, .estado-cargando { text-align: center; padding: 3.5rem 2rem; color: #6B7280; }
.icono-vacio { width: 56px; height: 56px; stroke: #9CA3AF; margin-bottom: 12px; }
.estado-vacio h3 { font-size: 1.2rem; color: #1A1A1A; margin: 0 0 6px; font-family: 'Montserrat', sans-serif; }
.estado-vacio p  { font-size: .93rem; margin: 0 0 1.2rem; font-family: 'Montserrat', sans-serif; }
.spinner-tabla { display: inline-block; width: 36px; height: 36px; border: 3px solid #E5E7EB; border-top-color: #1B396A; border-radius: 50%; animation: girar .8s linear infinite; margin-bottom: 12px; }
.btn-limpiar-vacio { background: #FFFFFF; color: #1A1A1A; border: 1px solid #E5E7EB; padding: 9px 20px; border-radius: 8px; font-weight: 500; cursor: pointer; font-family: 'Montserrat', sans-serif; transition: background .15s; }
.btn-limpiar-vacio:hover { background: #F5F5F5; }

/* ── Paginación ── */
.paginacion { margin-top: 1.2rem; display: flex; justify-content: space-between; align-items: center; font-size: .9rem; color: #6B7280; font-family: 'Montserrat', sans-serif; flex-wrap: wrap; gap: .5rem; }
.paginacion-izquierda, .paginacion-centro, .paginacion-derecha { display: flex; align-items: center; gap: 8px; }
.select-filas { border: 1px solid #E5E7EB; border-radius: 6px; padding: 4px 8px; font-size: .9rem; background: #FFFFFF; font-family: 'Montserrat', sans-serif; }
.btn-pag { padding: 5px 11px; border: 1px solid #E5E7EB; background: #FFFFFF; border-radius: 6px; cursor: pointer; color: #1A1A1A; font-family: 'Montserrat', sans-serif; font-size: .9rem; transition: background .15s; }
.btn-pag:hover:not(:disabled) { background: #F5F5F5; }
.btn-pag:disabled { opacity: .4; cursor: not-allowed; }
.btn-pag.activo { background: #1B396A; color: #FFFFFF; border-color: #1B396A; }

/* ── Footer ── */
.pie-pagina { text-align: center; color: #9CA3AF; font-size: .82rem; padding-top: 2rem; border-top: 1px solid #E5E7EB; margin-top: 1rem; font-family: 'Montserrat', sans-serif; }

/* ════════════════════════════════════════════
   MODALES — colores hardcoded
════════════════════════════════════════════ */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.55); display: flex; align-items: center; justify-content: center; z-index: 2000; padding: 1rem; }
.modal-content { background: #FFFFFF; width: 520px; max-width: 95%; border-radius: 14px; box-shadow: 0 20px 50px rgba(0,0,0,.22); overflow: hidden; border: 1px solid #E5E7EB; max-height: 90vh; display: flex; flex-direction: column; font-family: 'Montserrat', sans-serif; }
.modal-header { background: #1B396A; padding: 1.1rem 1.6rem; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
.modal-header h3 { margin: 0; font-size: 1.1rem; font-weight: 700; color: #FFFFFF; font-family: 'Montserrat', sans-serif; }
.btn-cerrar-modal { background: none; border: none; color: #FFFFFF; font-size: 1.8rem; cursor: pointer; line-height: 1; opacity: .85; padding: 0; }
.btn-cerrar-modal:hover { opacity: 1; }
.modal-body   { padding: 1.6rem; overflow-y: auto; flex: 1; }
.modal-footer { padding: 1rem 1.6rem; background: #F5F5F5; display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid #E5E7EB; flex-shrink: 0; }

.form-grupo { margin-bottom: 1.2rem; }
.form-grupo label { display: block; margin-bottom: 6px; font-weight: 600; font-size: .9rem; color: #1A1A1A; font-family: 'Montserrat', sans-serif; }
.modal-input, .modal-textarea {
  width: 100%; padding: 10px 14px; border: 1.5px solid #E5E7EB; border-radius: 8px;
  font-size: .95rem; background: #FFFFFF; color: #1A1A1A;
  font-family: 'Montserrat', sans-serif; outline: none;
  transition: border-color .2s, box-shadow .2s; box-sizing: border-box;
}
.modal-input:focus, .modal-textarea:focus { border-color: #1B396A; box-shadow: 0 0 0 3px #DBEAFE; }
.modal-input::placeholder, .modal-textarea::placeholder { color: #9CA3AF; }
.modal-textarea { resize: vertical; }
.error-campo { font-size: .78rem; color: #DC2626; font-weight: 600; margin: 4px 0 0; font-family: 'Montserrat', sans-serif; }

/* Detalle en modal Ver */
.detalle-fila { display: flex; justify-content: space-between; align-items: flex-start; padding: 11px 0; border-bottom: 1px solid #E5E7EB; font-size: .95rem; font-family: 'Montserrat', sans-serif; gap: 1rem; }
.detalle-fila:last-child { border-bottom: none; }
.detalle-label { font-weight: 600; color: #6B7280; flex-shrink: 0; }
.detalle-valor { font-weight: 500; color: #1A1A1A; text-align: right; }

/* ── Botones modal ── */
.btn-secundario {
  padding: 10px 22px; border-radius: 8px; font-weight: 600; font-size: .92rem;
  cursor: pointer; font-family: 'Montserrat', sans-serif;
  background: #FFFFFF; color: #1B396A; border: 2px solid #1B396A;
  transition: background .15s;
}
.btn-secundario:hover:not(:disabled) { background: #DBEAFE; }
.btn-secundario:disabled { opacity: .5; cursor: not-allowed; }

.btn-eliminar {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 10px 22px; border-radius: 8px; font-weight: 600; font-size: .92rem;
  cursor: pointer; font-family: 'Montserrat', sans-serif;
  background: #DC2626; color: #FFFFFF; border: 2px solid #DC2626;
  transition: background .15s;
}
.btn-eliminar:hover:not(:disabled) { background: #B91C1C; border-color: #B91C1C; }
.btn-eliminar:disabled { opacity: .5; cursor: not-allowed; }

.btn-guardar {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 10px 22px; border-radius: 8px; font-weight: 600; font-size: .92rem;
  cursor: pointer; font-family: 'Montserrat', sans-serif;
  background: #1B396A; color: #FFFFFF; border: 2px solid #1B396A;
  transition: background .15s;
}
.btn-guardar:hover:not(:disabled) { background: #1D4ED8; border-color: #1D4ED8; }
.btn-guardar:disabled { opacity: .55; cursor: not-allowed; }

.spinner-btn { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.4); border-top-color: #FFFFFF; border-radius: 50%; animation: girar .7s linear infinite; flex-shrink: 0; }
@keyframes girar { to{transform:rotate(360deg)} }

@media(max-width:640px) { .filters-bar{flex-direction:column;} .search-group{width:100%;flex:none;} }
</style>