<template>
  <MainLayout>
    <div class="materias-page">

      <nav class="breadcrumb">
        <span class="breadcrumb-link" @click="$router.push('/gestion-academica')">Gestión Académica</span>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-actual">Materias</span>
      </nav>

      <div class="page-header">
        <h1 class="page-title">Materias</h1>
        <span class="page-subtitle">{{ materiasFiltradas.length }} registro(s) encontrado(s)</span>
      </div>

      <div class="barra-carga" :class="{ visible: cargando }"><div class="barra-progreso"></div></div>

      <transition name="toast-slide">
        <div v-if="notificacion.visible" class="toast" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo === 'exito'" xmlns="http://www.w3.org/2000/svg" class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- ══════════════════════════════════════════════════
           BARRA DE ACCIONES
           Cambio: Se eliminó el botón "Filtros" porque el único
           filtro secundario (Estatus) cabe perfectamente en línea
           junto al buscador. Esto simplifica la UX y evita un clic
           extra innecesario.
      ══════════════════════════════════════════════════ -->
      <div class="actions-bar">

        <!-- Búsqueda principal -->
        <div class="search-group">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Buscar por clave o nombre..."
            v-model="busqueda"
            class="search-input"
            @keydown.escape="busqueda = ''"
          >
        </div>

        <!-- Filtro de estatus — inline (no requiere botón Filtros aparte) -->
        <select v-model="filtroEstatus" class="select-estatus" :class="{ 'select-activo': filtroEstatus }">
          <option value="">Todos los estatus</option>
          <option value="1">Activo</option>
          <option value="0">Inactivo</option>
        </select>

        <button class="btn-nuevo" @click="abrirModalNuevo">+ Nueva materia</button>
      </div>

      <!-- Tabla compacta -->
      <div class="table-container">
        <div v-if="cargando && materias.length === 0" class="estado-cargando">
          <div class="spinner-tabla"></div><p>Cargando registros...</p>
        </div>
        <table v-else-if="materiasPaginadas.length > 0" class="data-table">
          <thead>
            <tr>
              <th>Clave</th>
              <th>Nombre</th>
              <th class="th-centro">Créd.</th>
              <th class="th-centro">T</th>
              <th class="th-centro">P</th>
              <th>Estatus</th>
              <th class="th-acciones">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(materia, index) in materiasPaginadas" :key="materia.id_materia"
                :class="{ 'fila-seleccionada': filaActiva === index }" @click="filaActiva = index">
              <td class="celda-clave">{{ materia.clave }}</td>
              <td class="celda-nombre">{{ materia.nombre }}</td>
              <td class="td-centro">{{ materia.creditos }}</td>
              <td class="td-centro">{{ materia.horas_teoria }}</td>
              <td class="td-centro">{{ materia.horas_practica }}</td>
              <td><span class="estatus-badge" :class="materia.estatus ? 'activo' : 'inactivo'">{{ materia.estatus ? 'Activo' : 'Inactivo' }}</span></td>
              <td class="celda-acciones">
                <button class="btn-icono ver" @click.stop="abrirModalVer(materia)" title="Ver detalles">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
                <button class="btn-icono editar" @click.stop="abrirModalEditar(materia)" title="Editar">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button class="btn-icono planes-btn" @click.stop="abrirModalPlanes(materia)" title="Ver planes">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <h3>Sin resultados</h3>
          <p>No se encontraron materias con los criterios aplicados.</p>
          <button class="btn-limpiar-vacio" @click="limpiarFiltros">Limpiar filtros</button>
        </div>
      </div>

      <div class="leyenda-cols">T = Horas Teoría &nbsp;·&nbsp; P = Horas Práctica</div>

      <div class="paginacion">
        <div class="paginacion-izquierda">Filas por página:
          <select v-model="filasPorPagina" @change="currentPage = 1" class="select-filas">
            <option :value="10">10</option><option :value="20">20</option><option :value="50">50</option>
          </select>
        </div>
        <div class="paginacion-centro">Página {{ currentPage }} de {{ totalPages }}</div>
        <div class="paginacion-derecha">
          <button class="btn-pag" @click="prevPage" :disabled="currentPage === 1">‹</button>
          <button v-for="page in visiblePages" :key="page" class="btn-pag" :class="{ activo: page === currentPage }" @click="goToPage(page)">{{ page }}</button>
          <button class="btn-pag" @click="nextPage" :disabled="currentPage === totalPages">›</button>
        </div>
      </div>

      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México | Todos los derechos reservados</footer>
    </div>

    <!-- ══════════════════════════════════════════════════
         MODAL VER DETALLE — mejorado con grid de datos
    ══════════════════════════════════════════════════ -->
    <transition name="modal-fade">
      <div v-if="showModalVer" class="modal-overlay" @click.self="showModalVer = false">
        <div class="modal-content modal-ver">
          <div class="modal-header">
            <div class="modal-header-info">
              <span class="modal-header-tag">Materia · {{ materiaVer.clave }}</span>
              <h3>{{ materiaVer.nombre }}</h3>
            </div>
            <button @click="showModalVer = false" class="btn-cerrar-modal">×</button>
          </div>
          <div class="modal-body">
            <!-- Datos numéricos -->
            <div class="detalle-chips">
              <div class="chip">
                <span class="chip-val">{{ materiaVer.creditos }}</span>
                <span class="chip-lbl">Créditos</span>
              </div>
              <div class="chip">
                <span class="chip-val">{{ materiaVer.horas_teoria }}</span>
                <span class="chip-lbl">Hrs. Teoría</span>
              </div>
              <div class="chip">
                <span class="chip-val">{{ materiaVer.horas_practica }}</span>
                <span class="chip-lbl">Hrs. Práctica</span>
              </div>
              <div class="chip">
                <span class="estatus-badge" :class="materiaVer.estatus ? 'activo' : 'inactivo'">
                  {{ materiaVer.estatus ? 'Activo' : 'Inactivo' }}
                </span>
                <span class="chip-lbl">Estatus</span>
              </div>
            </div>
            <!-- Descripción -->
            <div v-if="materiaVer.descripcion" class="detalle-descripcion">
              <span class="detalle-label">Descripción</span>
              <p>{{ materiaVer.descripcion }}</p>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secundario" @click="showModalVer = false">Cerrar</button>
            <button class="btn-guardar" @click="abrirModalEditar(materiaVer); showModalVer = false">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Editar
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Modal Crear/Editar -->
    <transition name="modal-fade">
      <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
        <div class="modal-content modal-grande">
          <div class="modal-header">
            <div class="modal-header-info">
              <span class="modal-header-tag">{{ form.id_materia ? 'Edición' : 'Nueva' }}</span>
              <h3>{{ form.id_materia ? 'Editar Materia' : 'Nueva Materia' }}</h3>
            </div>
            <button @click="cerrarModal" class="btn-cerrar-modal">×</button>
          </div>
          <div class="modal-body">
            <div class="form-grupo-doble">
              <div class="form-grupo">
                <label>Clave <span class="obligatorio">*</span></label>
                <input v-model.trim="form.clave" type="text" maxlength="20" class="modal-input" :class="{ 'borde-error': errors.clave }" placeholder="Ej. ISC-101" :readonly="!!form.id_materia">
                <small v-if="errors.clave" class="mensaje-error">{{ errors.clave }}</small>
              </div>
              <div class="form-grupo">
                <label>Créditos <span class="obligatorio">*</span></label>
                <input v-model.number="form.creditos" type="number" min="1" class="modal-input" :class="{ 'borde-error': errors.creditos }" placeholder="5">
                <small v-if="errors.creditos" class="mensaje-error">{{ errors.creditos }}</small>
              </div>
            </div>
            <div class="form-grupo">
              <label>Nombre de la materia <span class="obligatorio">*</span></label>
              <input v-model.trim="form.nombre" type="text" maxlength="150" class="modal-input" :class="{ 'borde-error': errors.nombre }" placeholder="Ej. Fundamentos de Bases de Datos">
              <small v-if="errors.nombre" class="mensaje-error">{{ errors.nombre }}</small>
            </div>
            <div class="form-grupo-doble">
              <div class="form-grupo">
                <label>Horas de teoría <span class="obligatorio">*</span></label>
                <input v-model.number="form.horas_teoria" type="number" min="0" class="modal-input" :class="{ 'borde-error': errors.horas_teoria }" placeholder="2">
                <small v-if="errors.horas_teoria" class="mensaje-error">{{ errors.horas_teoria }}</small>
              </div>
              <div class="form-grupo">
                <label>Horas de práctica <span class="obligatorio">*</span></label>
                <input v-model.number="form.horas_practica" type="number" min="0" class="modal-input" :class="{ 'borde-error': errors.horas_practica }" placeholder="2">
                <small v-if="errors.horas_practica" class="mensaje-error">{{ errors.horas_practica }}</small>
              </div>
            </div>
            <div class="form-grupo">
              <label>Descripción</label>
              <textarea v-model.trim="form.descripcion" class="modal-textarea" rows="3" placeholder="Descripción opcional de la materia..."></textarea>
            </div>
            <div class="form-grupo">
              <label>Estatus</label>
              <select v-model="form.estatus" class="modal-select">
                <option :value="1">Activo</option>
                <option :value="0">Inactivo</option>
              </select>
              <div class="indicador-estatus" :class="form.estatus ? 'activo' : 'inactivo'">{{ form.estatus ? 'Activo' : 'Inactivo' }}</div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">Cancelar</button>
            <button v-if="form.id_materia" class="btn-eliminar" @click="solicitarEliminar" :disabled="guardando">Eliminar</button>
            <button class="btn-guardar" @click="guardar" :disabled="guardando">
              <span v-if="guardando" class="spinner-btn"></span>
              {{ guardando ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Modal Planes asociados -->
    <transition name="modal-fade">
      <div v-if="showModalPlanes" class="modal-overlay" @click.self="showModalPlanes = false">
        <div class="modal-content modal-grande">
          <div class="modal-header">
            <div class="modal-header-info">
              <span class="modal-header-tag">Planes de estudio</span>
              <h3>{{ materiaPlanes?.nombre }}</h3>
            </div>
            <button @click="showModalPlanes = false" class="btn-cerrar-modal">×</button>
          </div>
          <div class="modal-body">
            <p class="planes-subtitulo">Esta materia está incluida en los siguientes planes de estudio:</p>
            <div v-if="planesAsociados.length > 0" class="planes-lista">
              <div v-for="pa in planesAsociados" :key="pa.id_plan" class="plan-item">
                <div class="plan-info">
                  <span class="plan-nombre">{{ pa.plan?.nombre_plan }}</span>
                  <span class="plan-carrera">{{ pa.plan?.carrera?.nombre }}</span>
                </div>
                <span class="semestre-badge">{{ pa.semestre }}° Semestre</span>
              </div>
            </div>
            <div v-else class="planes-vacio"><p>Esta materia no está asociada a ningún plan de estudio aún.</p></div>
            <div class="planes-agregar">
              <h4>Agregar a un plan</h4>
              <div class="form-grupo-doble">
                <div class="form-grupo">
                  <label>Plan de estudio <span class="obligatorio">*</span></label>
                  <select v-model="nuevoPlanForm.id_plan" class="modal-select">
                    <option value="">Seleccionar plan</option>
                    <option v-for="p in planesDisponibles" :key="p.id_plan" :value="p.id_plan">{{ p.nombre_plan }}</option>
                  </select>
                </div>
                <div class="form-grupo">
                  <label>Semestre <span class="obligatorio">*</span></label>
                  <select v-model="nuevoPlanForm.semestre" class="modal-select">
                    <option value="">Semestre</option>
                    <option v-for="n in 12" :key="n" :value="n">{{ n }}° Semestre</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secundario" @click="showModalPlanes = false">Cerrar</button>
            <button class="btn-guardar" @click="agregarAPlan" :disabled="guardando || !nuevoPlanForm.id_plan || !nuevoPlanForm.semestre">
              <span v-if="guardando" class="spinner-btn"></span>
              {{ guardando ? 'Agregando...' : 'Agregar a plan' }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Modal Confirmar Eliminar -->
    <transition name="modal-fade">
      <div v-if="showModalEliminar" class="modal-overlay" @click.self="showModalEliminar = false">
        <div class="modal-content modal-confirmar">
          <div class="modal-header">
            <div class="modal-header-info">
              <span class="modal-header-tag">Confirmar</span>
              <h3>Eliminar materia</h3>
            </div>
            <button @click="showModalEliminar = false" class="btn-cerrar-modal">×</button>
          </div>
          <div class="modal-body confirmar-body">
            <svg xmlns="http://www.w3.org/2000/svg" class="confirmar-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p>¿Estás seguro de eliminar la materia <strong>{{ materiaAEliminar?.nombre }}</strong>? Esta acción no se puede deshacer.</p>
          </div>
          <div class="modal-footer">
            <button class="btn-secundario" @click="showModalEliminar = false">Cancelar</button>
            <button class="btn-eliminar" @click="confirmarEliminar" :disabled="guardando">
              <span v-if="guardando" class="spinner-btn"></span>
              {{ guardando ? 'Eliminando...' : 'Eliminar' }}
            </button>
          </div>
        </div>
      </div>
    </transition>

  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`

// ── Estado ──────────────────────────────────────────────
const materias         = ref([])
const planesDisponibles = ref([])
const cargando         = ref(false)
const guardando        = ref(false)
const filaActiva       = ref(-1)
const currentPage      = ref(1)
const filasPorPagina   = ref(10)

const busqueda      = ref('')
// Filtro de estatus inline — no necesita panel colapsable
const filtroEstatus = ref('')

const showModal         = ref(false)
const showModalVer      = ref(false)
const showModalPlanes   = ref(false)
const showModalEliminar = ref(false)
const materiaVer        = ref({})
const materiaPlanes     = ref(null)
const planesAsociados   = ref([])
const materiaAEliminar  = ref(null)

const form = reactive({ id_materia: null, clave: '', nombre: '', creditos: '', horas_teoria: '', horas_practica: '', descripcion: '', estatus: 1 })
const errors = reactive({})
const nuevoPlanForm = reactive({ id_plan: '', semestre: '' })
const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

// ── Computadas ───────────────────────────────────────────
const normalize = (t) => t?.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '') ?? ''

const materiasFiltradas = computed(() =>
  materias.value.filter(m => {
    const coincideBusqueda = !busqueda.value || normalize(m.nombre).includes(normalize(busqueda.value)) || normalize(m.clave).includes(normalize(busqueda.value))
    const coincideEstatus  = !filtroEstatus.value || String(m.estatus) === filtroEstatus.value
    return coincideBusqueda && coincideEstatus
  })
)

const totalPages        = computed(() => Math.ceil(materiasFiltradas.value.length / filasPorPagina.value) || 1)
const materiasPaginadas = computed(() => { const s = (currentPage.value - 1) * filasPorPagina.value; return materiasFiltradas.value.slice(s, s + filasPorPagina.value) })
const visiblePages      = computed(() => { const t = totalPages.value, c = currentPage.value; if (t <= 7) return Array.from({ length: t }, (_, i) => i + 1); const p = new Set([1, t, c, c-1, c+1].filter(x => x >= 1 && x <= t)); return [...p].sort((a, b) => a - b) })

// ── Métodos ──────────────────────────────────────────────
const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

const cargarMaterias = async () => {
  cargando.value = true
  try {
    const res = await fetch(`${API}/materias`)
    if (!res.ok) throw new Error()
    materias.value = await res.json()
  } catch { mostrarNotificacion('No se pudieron cargar las materias.', 'error') }
  finally { cargando.value = false }
}

const cargarPlanes = async () => {
  try {
    const res = await fetch(`${API}/planes-estudio`)
    if (res.ok) planesDisponibles.value = await res.json()
  } catch {}
}

onMounted(() => { cargarMaterias(); cargarPlanes() })

const goToPage  = (p) => { currentPage.value = p; filaActiva.value = -1 }
const prevPage  = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage  = () => { if (currentPage.value < totalPages.value) currentPage.value++ }
const limpiarFiltros = () => { busqueda.value = ''; filtroEstatus.value = ''; currentPage.value = 1; filaActiva.value = -1 }

const resetForm = () => { form.id_materia = null; form.clave = ''; form.nombre = ''; form.creditos = ''; form.horas_teoria = ''; form.horas_practica = ''; form.descripcion = ''; form.estatus = 1; Object.keys(errors).forEach(k => delete errors[k]) }
const abrirModalNuevo  = () => { resetForm(); showModal.value = true }
const abrirModalVer    = (m) => { materiaVer.value = m; showModalVer.value = true }
const abrirModalEditar = (m) => { resetForm(); Object.assign(form, { id_materia: m.id_materia, clave: m.clave, nombre: m.nombre, creditos: m.creditos, horas_teoria: m.horas_teoria, horas_practica: m.horas_practica, descripcion: m.descripcion || '', estatus: m.estatus }); showModal.value = true }
const cerrarModal = () => { showModal.value = false; resetForm() }

const abrirModalPlanes = async (m) => {
  materiaPlanes.value = m
  nuevoPlanForm.id_plan = ''; nuevoPlanForm.semestre = ''
  try {
    const res = await fetch(`${API}/materias/${m.id_materia}/planes`)
    planesAsociados.value = res.ok ? await res.json() : []
  } catch { planesAsociados.value = [] }
  showModalPlanes.value = true
}

const solicitarEliminar = () => { materiaAEliminar.value = { id_materia: form.id_materia, nombre: form.nombre }; showModal.value = false; showModalEliminar.value = true }

const validar = () => {
  Object.keys(errors).forEach(k => delete errors[k])
  if (!form.clave.trim())                                    errors.clave          = 'La clave es obligatoria'
  if (!form.nombre.trim())                                   errors.nombre         = 'El nombre es obligatorio'
  if (!form.creditos || form.creditos < 1)                   errors.creditos       = 'Los créditos deben ser mayores a 0'
  if (form.horas_teoria === '' || form.horas_teoria < 0)     errors.horas_teoria   = 'Las horas de teoría son obligatorias'
  if (form.horas_practica === '' || form.horas_practica < 0) errors.horas_practica = 'Las horas de práctica son obligatorias'
  return Object.keys(errors).length === 0
}

const guardar = async () => {
  if (!validar()) return
  guardando.value = true
  const esEdicion = !!form.id_materia
  const url     = esEdicion ? `${API}/materias/${form.id_materia}` : `${API}/materias`
  const payload = { clave: form.clave.trim(), nombre: form.nombre.trim(), creditos: form.creditos, horas_teoria: form.horas_teoria, horas_practica: form.horas_practica, descripcion: form.descripcion.trim() || null, estatus: form.estatus }
  try {
    const res = await fetch(url, { method: esEdicion ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(payload) })
    if (!res.ok) throw new Error()
    await cargarMaterias(); cerrarModal()
    mostrarNotificacion(esEdicion ? 'Materia actualizada correctamente.' : 'Materia creada correctamente.')
  } catch { mostrarNotificacion('Ocurrió un error al guardar.', 'error') }
  finally { guardando.value = false }
}

const agregarAPlan = async () => {
  if (!nuevoPlanForm.id_plan || !nuevoPlanForm.semestre) return
  guardando.value = true
  try {
    const res = await fetch(`${API}/plan-materia`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ id_plan: nuevoPlanForm.id_plan, id_materia: materiaPlanes.value.id_materia, semestre: nuevoPlanForm.semestre }) })
    if (!res.ok) throw new Error()
    await abrirModalPlanes(materiaPlanes.value)
    mostrarNotificacion('Materia agregada al plan correctamente.')
  } catch { mostrarNotificacion('Ocurrió un error al agregar al plan.', 'error') }
  finally { guardando.value = false }
}

const confirmarEliminar = async () => {
  if (!materiaAEliminar.value) return
  guardando.value = true
  try {
    const res = await fetch(`${API}/materias/${materiaAEliminar.value.id_materia}`, { method: 'DELETE', headers: { 'Accept': 'application/json' } })
    if (!res.ok) throw new Error()
    await cargarMaterias(); showModalEliminar.value = false; materiaAEliminar.value = null
    mostrarNotificacion('Materia eliminada correctamente.')
  } catch { mostrarNotificacion('Ocurrió un error al eliminar.', 'error') }
  finally { guardando.value = false }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* ── Variables y base ─────────────────────────────────── */
.materias-page {
  --azul: #1B396A; --azul-hover: #1D4ED8; --azul-suave: #DBEAFE;
  --borde: #E5E7EB; --fondo: #F5F5F5; --texto: #1A1A1A;
  --gris: #6B7280; --verde: #16A34A; --rojo: #DC2626;
  width: 100%; background: var(--fondo); font-family: 'Montserrat', sans-serif; padding-bottom: 2rem;
}

/* ── Breadcrumb ──────────────────────────────────────── */
.breadcrumb{display:flex;align-items:center;gap:6px;color:var(--gris);font-size:0.88rem;margin-bottom:0.75rem}
.breadcrumb-link{color:var(--azul);font-weight:500;cursor:pointer;transition:color 0.15s}
.breadcrumb-link:hover{color:var(--azul-hover);text-decoration:underline}
.breadcrumb-sep{color:#9CA3AF}.breadcrumb-actual{color:var(--gris);font-weight:600}

/* ── Encabezado ──────────────────────────────────────── */
.page-header{display:flex;flex-direction:column;gap:4px;margin-bottom:1.2rem}
.page-title{color:var(--texto);font-size:1.75rem;font-weight:700;letter-spacing:-0.02em;margin:0}
.page-subtitle{font-size:0.9rem;color:var(--gris);font-weight:500}

/* ── Barra de carga ──────────────────────────────────── */
.barra-carga{height:3px;background:transparent;border-radius:2px;margin-bottom:1rem;overflow:hidden;opacity:0;transition:opacity 0.3s}
.barra-carga.visible{opacity:1}
.barra-progreso{height:100%;width:40%;background:var(--azul);border-radius:2px;animation:deslizar 1.2s ease-in-out infinite}
@keyframes deslizar{0%{transform:translateX(-100%)}100%{transform:translateX(350%)}}

/* ── Toast ───────────────────────────────────────────── */
.toast{position:fixed;bottom:2rem;right:2rem;display:flex;align-items:center;gap:10px;padding:12px 20px;border-radius:10px;color:white;font-weight:500;font-size:0.93rem;box-shadow:0 6px 20px rgba(0,0,0,0.18);z-index:3000;max-width:380px}
.toast.exito{background:var(--azul)}.toast.error{background:var(--rojo)}.toast-icono{width:20px;height:20px;flex-shrink:0}
.toast-slide-enter-active,.toast-slide-leave-active{transition:all 0.35s ease}
.toast-slide-enter-from,.toast-slide-leave-to{opacity:0;transform:translateX(110%)}

/* ── Barra de acciones ────────────────────────────────── */
.actions-bar{display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;flex-wrap:wrap}
.search-group{position:relative;flex:1 1 260px;min-width:200px}
.search-input{width:100%;padding:10px 14px 10px 42px;border:1px solid var(--borde);border-radius:8px;font-size:0.93rem;background:#FFFFFF;color:var(--texto);font-family:'Montserrat',sans-serif;outline:none;transition:border-color 0.2s,box-shadow 0.2s;box-sizing:border-box}
.search-input:focus{border-color:var(--azul);box-shadow:0 0 0 3px #DBEAFE}
.search-input::placeholder{color:#9CA3AF}
.search-icon-svg{position:absolute;left:13px;top:50%;transform:translateY(-50%);width:18px;height:18px;stroke:var(--gris);pointer-events:none}

/* Select estatus inline (reemplaza el panel de filtros colapsable) */
.select-estatus {
  padding: 10px 14px;
  border: 1px solid var(--borde);
  border-radius: 8px;
  font-size: 0.92rem;
  background: #FFFFFF;
  color: var(--texto);
  font-family: 'Montserrat', sans-serif;
  cursor: pointer;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  white-space: nowrap;
}
.select-estatus:focus { border-color: var(--azul); box-shadow: 0 0 0 3px #DBEAFE; }
.select-estatus.select-activo { border-color: var(--azul); color: var(--azul); background: var(--azul-suave); }

.btn-nuevo{background:var(--azul);color:white;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;white-space:nowrap;font-family:'Montserrat',sans-serif;font-size:0.92rem;transition:background 0.2s}
.btn-nuevo:hover{background:var(--azul-hover)}

/* ── Tabla ────────────────────────────────────────────── */
.table-container{background:#FFFFFF;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);border:1px solid var(--borde)}
.data-table{width:100%;border-collapse:collapse}
.data-table th{background:var(--fondo);padding:9px 12px;text-align:left;font-weight:600;font-size:0.82rem;color:var(--texto);border-bottom:1px solid var(--borde);font-family:'Montserrat',sans-serif;white-space:nowrap}
.th-centro,.td-centro{text-align:center}.th-acciones{text-align:center}
.data-table td{padding:8px 12px;border-bottom:1px solid var(--borde);color:var(--texto);font-size:0.88rem;font-family:'Montserrat',sans-serif}
.data-table tbody tr{transition:background 0.15s;cursor:pointer}
.data-table tbody tr:hover{background:#F8FAFC}
.data-table tbody tr:last-child td{border-bottom:none}
.fila-seleccionada{background:#DBEAFE!important}
.celda-clave{font-weight:700;color:var(--azul);font-size:0.83rem;font-family:monospace}
.celda-nombre{font-weight:600}
.estatus-badge{display:inline-block;padding:3px 9px;border-radius:20px;font-size:0.78rem;font-weight:600}
.estatus-badge.activo{background:#DCFCE7;color:#16A34A}
.estatus-badge.inactivo{background:#F3F4F6;color:#6B7280}
.celda-acciones{display:flex;gap:5px;align-items:center;justify-content:center}
.btn-icono{display:flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:7px;cursor:pointer;border:1px solid transparent;transition:background 0.15s,border-color 0.15s;flex-shrink:0}
.btn-icono svg{width:14px;height:14px}
.btn-icono.ver{background:#F3F4F6;border-color:#D1D5DB}.btn-icono.ver:hover{background:#E5E7EB}.btn-icono.ver svg{stroke:#374151}
.btn-icono.editar{background:var(--azul);border-color:var(--azul)}.btn-icono.editar:hover{background:var(--azul-hover);border-color:var(--azul-hover)}.btn-icono.editar svg{stroke:#FFFFFF}
.btn-icono.planes-btn{background:#DBEAFE;border-color:#BFDBFE}.btn-icono.planes-btn:hover{background:#BFDBFE}.btn-icono.planes-btn svg{stroke:var(--azul)}
.leyenda-cols{font-size:0.78rem;color:#9CA3AF;margin-top:0.5rem;text-align:right;font-family:'Montserrat',sans-serif}
.estado-vacio,.estado-cargando{text-align:center;padding:3rem 2rem;color:var(--gris)}
.icono-vacio{width:52px;height:52px;stroke:#9CA3AF;margin-bottom:12px}
.estado-vacio h3{font-size:1.1rem;color:var(--texto);margin:0 0 6px}
.estado-vacio p{font-size:0.9rem;margin:0 0 1rem}
.btn-limpiar-vacio{background:#FFFFFF;color:var(--texto);border:1px solid var(--borde);padding:8px 18px;border-radius:8px;font-weight:500;cursor:pointer;font-family:'Montserrat',sans-serif}
.spinner-tabla{display:inline-block;width:32px;height:32px;border:3px solid #E5E7EB;border-top-color:var(--azul);border-radius:50%;animation:girar 0.8s linear infinite;margin-bottom:12px}

/* ── Paginación ────────────────────────────────────────── */
.paginacion{margin-top:1.2rem;display:flex;justify-content:space-between;align-items:center;font-size:0.9rem;color:var(--gris);font-family:'Montserrat',sans-serif;flex-wrap:wrap;gap:0.5rem}
.paginacion-izquierda,.paginacion-centro,.paginacion-derecha{display:flex;align-items:center;gap:8px}
.select-filas{border:1px solid var(--borde);border-radius:6px;padding:4px 8px;font-size:0.9rem;background:#FFFFFF;font-family:'Montserrat',sans-serif}
.btn-pag{padding:5px 11px;border:1px solid var(--borde);background:#FFFFFF;border-radius:6px;cursor:pointer;color:var(--texto);font-family:'Montserrat',sans-serif;font-size:0.9rem;transition:background 0.15s}
.btn-pag:hover:not(:disabled){background:var(--fondo)}.btn-pag:disabled{opacity:0.4;cursor:not-allowed}
.btn-pag.activo{background:var(--azul);color:white;border-color:var(--azul)}

/* ── Footer ────────────────────────────────────────────── */
.pie-pagina{text-align:center;color:#9CA3AF;font-size:0.82rem;padding-top:2rem;border-top:1px solid #E5E7EB;margin-top:1rem}

/* ── Modales ────────────────────────────────────────────── */
.modal-fade-enter-active,.modal-fade-leave-active{transition:opacity 0.22s ease}
.modal-fade-enter-from,.modal-fade-leave-to{opacity:0}

.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:2000;padding:1rem}
.modal-content{background:#FFFFFF;width:540px;max-width:100%;max-height:90vh;border-radius:16px;box-shadow:0 24px 60px rgba(0,0,0,0.2);overflow:hidden;border:1px solid #E5E7EB;display:flex;flex-direction:column}
.modal-ver{width:520px}
.modal-grande{width:640px}
.modal-confirmar{width:440px}

.modal-header{background:#1B396A;color:white;padding:1rem 1.4rem;display:flex;justify-content:space-between;align-items:flex-start;flex-shrink:0}
.modal-header-info{display:flex;flex-direction:column;gap:2px}
.modal-header-tag{font-size:0.72rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;opacity:0.7}
.modal-header h3{margin:0;font-size:1.15rem;font-weight:700;font-family:'Montserrat',sans-serif}
.btn-cerrar-modal{background:none;border:none;color:white;font-size:1.7rem;cursor:pointer;line-height:1;opacity:0.8;padding:0;flex-shrink:0}
.btn-cerrar-modal:hover{opacity:1}
.modal-body{padding:1.5rem 1.6rem;overflow-y:auto}

/* Chips de datos numéricos en modal Ver */
.detalle-chips{display:flex;gap:0.75rem;flex-wrap:wrap;margin-bottom:1.2rem}
.chip{display:flex;flex-direction:column;align-items:center;gap:4px;padding:0.75rem 1rem;background:#F8FAFC;border-radius:10px;border:1px solid #E5E7EB;min-width:90px;text-align:center}
.chip-val{font-size:1.5rem;font-weight:700;color:var(--azul);line-height:1}
.chip-lbl{font-size:0.75rem;color:var(--gris);font-weight:600;text-transform:uppercase;letter-spacing:0.04em}
.detalle-descripcion{background:#F8FAFC;border-radius:8px;border:1px solid #E5E7EB;padding:0.9rem 1rem}
.detalle-descripcion p{margin:0.4rem 0 0;color:var(--texto);font-size:0.92rem;line-height:1.55}
.detalle-label{font-size:0.78rem;font-weight:600;color:var(--gris);text-transform:uppercase;letter-spacing:0.05em}

/* Formulario */
.form-grupo{margin-bottom:1.2rem}
.form-grupo-doble{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.form-grupo label{display:block;margin-bottom:6px;font-weight:600;font-size:0.9rem;color:#1A1A1A;font-family:'Montserrat',sans-serif}
.obligatorio{color:#DC2626}
.modal-input,.modal-select,.modal-textarea{width:100%;padding:10px 14px;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.95rem;background:#FFFFFF;color:#1A1A1A;font-family:'Montserrat',sans-serif;outline:none;transition:border-color 0.2s;box-sizing:border-box}
.modal-input:focus,.modal-select:focus,.modal-textarea:focus{border-color:#1B396A;box-shadow:0 0 0 3px #DBEAFE}
.modal-textarea{resize:vertical;min-height:80px}
.borde-error{border-color:#DC2626!important}.mensaje-error{display:block;color:#DC2626;font-size:0.82rem;margin-top:5px}
.indicador-estatus{display:inline-flex;align-items:center;margin-top:7px;padding:4px 12px;border-radius:20px;font-size:0.82rem;font-weight:600}
.indicador-estatus.activo{background:#DCFCE7;color:#16A34A}.indicador-estatus.inactivo{background:#F3F4F6;color:#6B7280}

/* Planes */
.planes-subtitulo{font-size:0.9rem;color:#6B7280;margin:0 0 1rem}
.planes-lista{display:flex;flex-direction:column;gap:8px;margin-bottom:1.4rem}
.plan-item{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:#F5F5F5;border-radius:8px;border:1px solid #E5E7EB}
.plan-info{display:flex;flex-direction:column}
.plan-nombre{font-weight:600;font-size:0.9rem;color:#1A1A1A}
.plan-carrera{font-size:0.82rem;color:#6B7280}
.semestre-badge{background:#DBEAFE;color:#1B396A;padding:3px 10px;border-radius:20px;font-size:0.8rem;font-weight:600;white-space:nowrap}
.planes-vacio{text-align:center;padding:1.5rem;color:#6B7280;font-size:0.9rem}
.planes-agregar{border-top:1px solid #E5E7EB;padding-top:1.2rem;margin-top:0.5rem}
.planes-agregar h4{font-size:0.95rem;font-weight:700;color:#1A1A1A;margin:0 0 1rem}

/* Footer del modal */
.modal-footer{padding:1rem 1.6rem;background:#F8FAFC;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid #E5E7EB;flex-shrink:0}
.btn-secundario{padding:9px 20px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;background:#FFFFFF;color:#1B396A;border:2px solid #1B396A;transition:background 0.15s}
.btn-secundario:hover{background:#DBEAFE}.btn-secundario:disabled{opacity:0.5;cursor:not-allowed}
.btn-eliminar{padding:9px 20px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;background:#DC2626;color:white;border:2px solid #DC2626;transition:background 0.15s}
.btn-eliminar:hover{background:#B91C1C}.btn-eliminar:disabled{opacity:0.5;cursor:not-allowed}
.btn-guardar{display:flex;align-items:center;gap:8px;padding:9px 20px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;background:#1B396A;color:#FFFFFF;border:2px solid #1B396A;transition:background 0.15s}
.btn-guardar:hover:not(:disabled){background:#1D4ED8}.btn-guardar:disabled{opacity:0.55;cursor:not-allowed}
.spinner-btn{display:inline-block;width:15px;height:15px;border:2px solid rgba(255,255,255,0.4);border-top-color:white;border-radius:50%;animation:girar 0.7s linear infinite;flex-shrink:0}
.confirmar-body{display:flex;flex-direction:column;align-items:center;gap:1rem;text-align:center;padding:2rem 1.6rem}
.confirmar-icono{width:52px;height:52px;stroke:#F59E0B}
.confirmar-body p{color:#1A1A1A;font-size:0.95rem;margin:0;line-height:1.5;font-family:'Montserrat',sans-serif}

@keyframes girar{to{transform:rotate(360deg)}}

/* ── Responsivo ───────────────────────────────────────── */
@media(max-width:600px){
  .page-title{font-size:1.4rem}
  .actions-bar{flex-direction:column;align-items:stretch}
  .select-estatus{width:100%}
  .form-grupo-doble{grid-template-columns:1fr}
  .detalle-chips{justify-content:center}
}
</style>