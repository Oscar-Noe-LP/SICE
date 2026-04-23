<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="alumnos-page">

      <div class="page-header">
        <h1 class="page-title">Inscripciones</h1>
        <p class="page-subtitle">{{ inscripcionesFiltradas.length }} registro(s) encontrado(s)</p>
      </div>

      <div class="barra-carga-global" :class="{ visible: cargando }"><div class="barra-progreso"></div></div>

      <transition name="notif-fade">
        <div v-if="notificacion.visible" class="notificacion-ui" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo==='exito'" xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- KPIs basados en inscripcion.estatus VARCHAR(50) -->
      <div class="stats-grid">
        <div class="stat-card stat-azul">
          <div class="stat-ico-wrap"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2" style="width:22px;height:22px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
          <div class="stat-info"><p class="stat-label">Total Inscritos</p><div v-if="cargandoKpis" class="stat-skel stat-skel-white"></div><p v-else class="stat-num" style="color:#fff">{{ kpis.totalInscritos }}</p></div>
        </div>
        <div class="stat-card">
          <div class="stat-ico-wrap" style="background:#DCFCE7"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#16A34A" stroke-width="2" style="width:22px;height:22px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
          <div class="stat-info"><p class="stat-label">Activos</p><div v-if="cargandoKpis" class="stat-skel"></div><p v-else class="stat-num">{{ kpis.activos }}</p></div>
        </div>
        <div class="stat-card">
          <div class="stat-ico-wrap" style="background:#FEF3C7"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#F59E0B" stroke-width="2" style="width:22px;height:22px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
          <div class="stat-info"><p class="stat-label">Baja Temporal</p><div v-if="cargandoKpis" class="stat-skel"></div><p v-else class="stat-num">{{ kpis.bajaTemporal }}</p></div>
        </div>
        <div class="stat-card">
          <div class="stat-ico-wrap" style="background:#FEF2F2"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#DC2626" stroke-width="2" style="width:22px;height:22px"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></div>
          <div class="stat-info"><p class="stat-label">Baja Definitiva</p><div v-if="cargandoKpis" class="stat-skel"></div><p v-else class="stat-num">{{ kpis.bajaDefinitiva }}</p></div>
        </div>
      </div>

      <div v-if="errorCarga" class="alerta-error">
        <svg xmlns="http://www.w3.org/2000/svg" class="ae-ico" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>No se pudieron cargar los datos.</span>
        <button class="btn-reintentar" @click="cargarDatos">Reintentar</button>
      </div>

      <div class="filters-bar">
        <div class="search-group">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input type="text" placeholder="Numero de control o nombre - presiona Enter para buscar" v-model="busquedaInput" class="search-input" @keydown.enter="aplicarBusqueda" @keydown.escape="limpiarBusqueda">
          <span v-if="busquedaActiva" class="badge-busqueda-activa"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:12px;height:12px;stroke:#1B396A"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></span>
        </div>
        <button class="btn-buscar-tabla" @click="aplicarBusqueda" :disabled="!busquedaInput.trim()">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:15px;height:15px;stroke:#fff"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          Buscar
        </button>
        <!-- GET /api/form/periodos -> [{ id_periodo, nombre_periodo }] -->
        <select v-model="filtroPeriodo" class="filter-select" @change="currentPage=1">
          <option value="">Periodo</option>
          <option v-for="p in periodosDisponibles" :key="p.id_periodo" :value="p.id_periodo">{{ p.nombre_periodo }}</option>
        </select>
        <!-- GET /api/form/carreras -> [{ id_carrera, nombre }] -->
        <select v-model="filtroCarrera" class="filter-select" @change="currentPage=1">
          <option value="">Carrera</option>
          <option v-for="c in carrerasDisponibles" :key="c.id_carrera" :value="c.id_carrera">{{ c.nombre }}</option>
        </select>
        <!-- inscripcion.estatus VARCHAR(50) -->
        <select v-model="filtroEstatus" class="filter-select" @change="currentPage=1">
          <option value="">Estatus</option>
          <option value="Activo">Activo</option>
          <option value="Baja Temporal">Baja Temporal</option>
          <option value="Baja Definitiva">Baja Definitiva</option>
        </select>
        <button class="btn-limpiar" @click="resetFiltros"><svg xmlns="http://www.w3.org/2000/svg" class="reset-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>Limpiar</button>
        <button class="btn-nuevo" @click="abrirModalNueva">+ Nueva Inscripcion</button>
      </div>

      <div class="table-container">
        <div v-if="cargando && inscripciones.length===0" class="estado-cargando"><div class="spinner-tabla"></div><p>Cargando registros...</p></div>
        <table v-else-if="inscripcionesPaginadas.length>0" class="alumnos-table" tabindex="0">
          <thead><tr><th>No. Control</th><th>Alumno</th><th>Carrera</th><th>Grupo</th><th>Materia</th><th>Periodo</th><th>Fecha Inscripcion</th><th>Estatus</th><th>Acciones</th></tr></thead>
          <tbody>
            <tr v-for="(ins,i) in inscripcionesPaginadas" :key="ins.id_inscripcion" :class="{'fila-seleccionada':filaActiva===i}" @click="filaActiva=i">
              <td class="celda-control">{{ ins.numero_control }}</td>
              <td>{{ ins.nombre_alumno }}</td>
              <td>{{ ins.nombre_carrera }}</td>
              <td>{{ ins.clave_grupo }}</td>
              <td>{{ ins.nombre_materia }}</td>
              <td>{{ ins.nombre_periodo }}</td>
              <td>{{ ins.fecha_inscripcion }}</td>
              <td><span class="estatus-badge" :class="claseEstatus(ins.estatus)">{{ ins.estatus }}</span></td>
              <td class="celda-acciones">
                <button class="btn-accion ver" @click.stop="abrirModalVer(ins)"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>Ver</button>
                <button class="btn-accion editar" @click.stop="abrirModalEditar(ins)"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Editar</button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          <h3>Sin resultados</h3><p>No se encontraron inscripciones con los criterios aplicados.</p>
          <button class="btn-limpiar-vacio" @click="resetFiltros">Limpiar filtros</button>
        </div>
      </div>

      <div class="paginacion">
        <div class="paginacion-izquierda">Filas por pagina:<select v-model="filasPorPagina" @change="currentPage=1" class="select-filas"><option :value="10">10</option><option :value="15">15</option><option :value="20">20</option><option :value="50">50</option></select></div>
        <div class="paginacion-centro">Pagina {{ currentPage }} de {{ totalPages }}</div>
        <div class="paginacion-derecha"><button class="btn-pag" @click="prevPage" :disabled="currentPage===1">&#8249;</button><button v-for="p in visiblePages" :key="p" class="btn-pag" :class="{activo:p===currentPage}" @click="goToPage(p)">{{ p }}</button><button class="btn-pag" @click="nextPage" :disabled="currentPage===totalPages">&#8250;</button></div>
      </div>

      <footer class="pie-pagina">© 2026 Tecnologico Nacional de Mexico · Todos los derechos reservados</footer>
    </div>

    <!-- MODAL VER -->
    <div v-if="showModalVer" class="modal-overlay" @click.self="showModalVer=false">
      <div class="modal-content">
        <div class="modal-header"><h3>Detalle de Inscripcion</h3><button @click="showModalVer=false" class="btn-cerrar-modal">×</button></div>
        <div class="modal-body" v-if="inscripcionVer">
          <div class="detalle-fila"><span class="detalle-label">No. Control</span><span class="detalle-valor">{{ inscripcionVer.numero_control }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Alumno</span><span class="detalle-valor">{{ inscripcionVer.nombre_alumno }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Carrera</span><span class="detalle-valor">{{ inscripcionVer.nombre_carrera }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Grupo</span><span class="detalle-valor">{{ inscripcionVer.clave_grupo }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Materia</span><span class="detalle-valor">{{ inscripcionVer.nombre_materia }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Periodo</span><span class="detalle-valor">{{ inscripcionVer.nombre_periodo }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Docente</span><span class="detalle-valor">{{ inscripcionVer.nombre_docente || '—' }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Aula</span><span class="detalle-valor">{{ inscripcionVer.nombre_aula || '—' }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Fecha Inscripcion</span><span class="detalle-valor">{{ inscripcionVer.fecha_inscripcion }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Estatus</span><span class="estatus-badge" :class="claseEstatus(inscripcionVer.estatus)">{{ inscripcionVer.estatus }}</span></div>
        </div>
        <div class="modal-footer"><button class="btn-secundario" @click="showModalVer=false">Cerrar</button><button class="btn-guardar" @click="abrirModalEditar(inscripcionVer);showModalVer=false">Editar</button></div>
      </div>
    </div>

    <!-- MODAL CREAR / EDITAR -->
    <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-content">
        <div class="modal-header"><h3>{{ form.id_inscripcion ? 'Editar Inscripcion' : 'Nueva Inscripcion' }}</h3><button @click="cerrarModal" class="btn-cerrar-modal">×</button></div>
        <div class="modal-body">

          <div class="form-grupo">
            <label>Numero de Control *</label>
            <div v-if="form.id_inscripcion" class="modal-input deshabilitado">{{ form.numero_control }}</div>
            <template v-else>
              <div style="display:flex;gap:8px">
                <input v-model="form.numero_control" type="text" class="modal-input" placeholder="Ej: 21456887" @keydown.enter="buscarAlumno" style="flex:1"/>
                <button class="btn-buscar-modal" @click="buscarAlumno" :disabled="buscandoAlumno"><span v-if="buscandoAlumno" class="spinner-btn"></span><span v-else>Buscar</span></button>
              </div>
            </template>
          </div>

          <transition name="notif-fade">
            <div v-if="alumnoEncontrado" class="bloque-alumno-found">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#16A34A" stroke-width="2" style="width:22px;height:22px;flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <div><p style="margin:0 0 2px;font-weight:700;font-size:.92rem;color:#1A1A1A;font-family:'Montserrat',sans-serif">{{ alumnoEncontrado.nombre_alumno }}</p><p style="margin:0;font-size:.8rem;color:#6B7280;font-family:'Montserrat',sans-serif">{{ alumnoEncontrado.nombre_carrera }}</p></div>
            </div>
          </transition>

          <!-- GET /api/form/grupos/disponibles -->
          <div class="form-grupo">
            <label>Grupo *</label>
            <div v-if="form.id_inscripcion" class="modal-input deshabilitado">{{ grupoActual }}</div>
            <select v-else v-model="form.id_grupo" class="modal-select">
              <option value="">Seleccionar grupo...</option>
              <option v-for="g in gruposDisponibles" :key="g.id_grupo" :value="g.id_grupo">{{ g.clave_grupo }} — {{ g.nombre_materia }} | {{ g.nombre_periodo }}</option>
            </select>
            <p v-if="errores.id_grupo" class="error-campo">{{ errores.id_grupo }}</p>
          </div>

          <div class="form-grupo">
            <label>Fecha de Inscripcion *</label>
            <input v-if="!form.id_inscripcion" v-model="form.fecha_inscripcion" type="date" class="modal-input"/>
            <div v-else class="modal-input deshabilitado">{{ form.fecha_inscripcion }}</div>
            <p v-if="errores.fecha" class="error-campo">{{ errores.fecha }}</p>
          </div>

          <!-- inscripcion.estatus VARCHAR(50) — unico campo editable en edicion -->
          <div class="form-grupo">
            <label>Estatus *</label>
            <select v-model="form.estatus" class="modal-select">
              <option value="Activo">Activo</option>
              <option value="Baja Temporal">Baja Temporal</option>
              <option value="Baja Definitiva">Baja Definitiva</option>
            </select>
          </div>

          <div v-if="form.id_inscripcion" style="background:#FEF3C7;border:1px solid #FDE68A;border-radius:8px;padding:10px 14px;font-size:.85rem;color:#92400E;font-family:'Montserrat',sans-serif">
            En edicion solo puedes modificar el estatus. El alumno y el grupo no cambian.
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">Cancelar</button>
          <button v-if="form.id_inscripcion" class="btn-eliminar" @click="pedirConfirmarEliminar" :disabled="guardando"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;stroke:#fff"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Eliminar</button>
          <button class="btn-guardar" @click="guardar" :disabled="guardando"><span v-if="guardando" class="spinner-btn"></span><svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;stroke:#fff"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>{{ guardando ? 'Guardando...' : 'Guardar cambios' }}</button>
        </div>
      </div>
    </div>

    <!-- MODAL CONFIRMAR ELIMINAR -->
    <div v-if="showModalEliminar" class="modal-overlay" @click.self="showModalEliminar=false">
      <div class="modal-content" style="max-width:460px">
        <div class="modal-header" style="background:#DC2626"><h3>Confirmar eliminacion</h3><button @click="showModalEliminar=false" class="btn-cerrar-modal">×</button></div>
        <div class="modal-body"><p style="font-size:.95rem;color:#1A1A1A;line-height:1.6;margin:0;font-family:'Montserrat',sans-serif">¿Estas seguro de eliminar la inscripcion de <strong>{{ inscripcionAEliminar?.nombre_alumno }}</strong> del grupo <strong>{{ inscripcionAEliminar?.clave_grupo }}</strong>? Esta accion no se puede deshacer.</p></div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="showModalEliminar=false" :disabled="guardando">Cancelar</button>
          <button class="btn-eliminar" @click="confirmarEliminar" :disabled="guardando"><span v-if="guardando" class="spinner-btn"></span><svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;stroke:#fff"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>{{ guardando ? 'Eliminando...' : 'Si, eliminar' }}</button>
        </div>
      </div>
    </div>

  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

// ✅ Base correcta: todos los endpoints del módulo de inscripciones usan /api/form
const API = `${import.meta.env.VITE_API_URL}/api/form`

const inscripciones       = ref([])
const gruposDisponibles   = ref([])
const periodosDisponibles = ref([])
const carrerasDisponibles = ref([])
const cargando            = ref(false)
const cargandoKpis        = ref(false)
const guardando           = ref(false)
const errorCarga          = ref(false)
const filaActiva          = ref(-1)
const buscandoAlumno      = ref(false)
const alumnoEncontrado    = ref(null)
const grupoActual         = ref('')

// KPIs: SELECT estatus, COUNT(*) FROM inscripcion GROUP BY estatus
const kpis = ref({ totalInscritos: 0, activos: 0, bajaTemporal: 0, bajaDefinitiva: 0 })

const busquedaInput  = ref('')
const busquedaActiva = ref('')
const filtroPeriodo  = ref('')
const filtroCarrera  = ref('')
const filtroEstatus  = ref('')
const filasPorPagina = ref(10)
const currentPage    = ref(1)

const showModalVer       = ref(false)
const showModal          = ref(false)
const showModalEliminar  = ref(false)
const inscripcionVer       = ref(null)
const inscripcionAEliminar = ref(null)
const errores = ref({ id_grupo: '', fecha: '' })

// Campos tabla inscripcion: id_inscripcion, id_alumno, id_grupo, fecha_inscripcion, estatus VARCHAR(50)
const formVacio = () => ({
  id_inscripcion:    null,
  numero_control:    '',
  id_alumno:         null,
  id_grupo:          '',
  fecha_inscripcion: new Date().toISOString().split('T')[0],
  estatus:           'Activo'
})
const form = ref(formVacio())

const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null
const mostrarNotificacion = (m, t='exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible:true, mensaje:m, tipo:t }
  timerNotif = setTimeout(() => { notificacion.value.visible=false }, 3500)
}

/*
 * GET /api/form/inscripciones
 * JOIN: inscripcion -> alumno(numero_control) -> persona(nombre+apellidos) -> carrera(nombre)
 *                  -> grupo(clave_grupo)      -> materia(nombre)
 *                                             -> periodo(nombre_periodo)
 *                                             -> docente -> empleado -> persona(nombre)
 *                                             -> aula(nombre)
 * Respuesta: [{
 *   id_inscripcion, id_alumno, id_grupo,
 *   numero_control, nombre_alumno,
 *   id_carrera, nombre_carrera,
 *   clave_grupo, nombre_materia,
 *   id_periodo, nombre_periodo,
 *   nombre_docente, nombre_aula,
 *   fecha_inscripcion, estatus
 * }]
 */
const cargarInscripciones = async () => {
  cargando.value=true; errorCarga.value=false
  try {
    const res = await fetch(`${API}/inscripciones`)
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    inscripciones.value = await res.json()
  } catch (err) {
    console.error('cargarInscripciones:', err)
    errorCarga.value=true
    mostrarNotificacion('No se pudo cargar la lista de inscripciones.', 'error')
  } finally { cargando.value=false }
}

/*
 * GET /api/form/kpis
 * Respuesta: { totalInscritos, activos, bajaTemporal, bajaDefinitiva }
 * Query: SELECT estatus, COUNT(*) FROM inscripcion GROUP BY estatus
 */
const cargarKpis = async () => {
  cargandoKpis.value=true
  try {
    const res = await fetch(`${API}/kpis`)
    if (res.ok) kpis.value = await res.json()
  } catch { console.error('kpis') }
  finally { cargandoKpis.value=false }
}

/*
 * GET /api/form/grupos/disponibles
 * Solo grupos con estatus=true y capacidad > COUNT(inscripciones activas)
 * Respuesta: [{ id_grupo, clave_grupo, nombre_materia, nombre_periodo }]
 */
const cargarGrupos = async () => {
  try {
    const r = await fetch(`${API}/grupos/disponibles`)
    if (r.ok) gruposDisponibles.value = await r.json()
  } catch { console.error('grupos') }
}

/*
 * GET /api/form/periodos  -> [{ id_periodo, nombre_periodo }]
 *   FROM periodo WHERE estatus = true
 * GET /api/form/carreras  -> [{ id_carrera, nombre }]
 *   FROM carrera WHERE estatus = true
 */
const cargarFiltros = async () => {
  try {
    const [rP, rC] = await Promise.all([
      fetch(`${API}/periodos`),
      fetch(`${API}/carreras`)
    ])
    if (rP.ok) periodosDisponibles.value = await rP.json()
    if (rC.ok) carrerasDisponibles.value = await rC.json()
  } catch { console.error('filtros') }
}

const cargarDatos = () => {
  cargarInscripciones()
  cargarKpis()
  cargarGrupos()
  cargarFiltros()
}
onMounted(cargarDatos)

/*
 * GET /api/form/alumnos/control/:numero_control
 * JOIN: alumno -> persona (nombre + apellido_paterno + apellido_materno) -> carrera(nombre)
 * Respuesta: { id_alumno, numero_control, nombre_alumno, nombre_carrera, id_carrera }
 */
const buscarAlumno = async () => {
  const nc = (form.value.numero_control || '').trim()
  if (!nc) return
  buscandoAlumno.value=true; alumnoEncontrado.value=null; form.value.id_alumno=null
  try {
    const res = await fetch(`${API}/alumnos/control/${encodeURIComponent(nc)}`)
    if (!res.ok) throw new Error()
    const d = await res.json()
    alumnoEncontrado.value = d
    form.value.id_alumno = d.id_alumno
    mostrarNotificacion('Alumno encontrado', 'exito')
  } catch {
    mostrarNotificacion(`No se encontro el No. de Control ${nc}`, 'error')
  } finally { buscandoAlumno.value=false }
}

/*
 * POST /api/form/inscripciones
 * Body: { id_alumno, id_grupo, fecha_inscripcion, estatus }
 * Valida UNIQUE(id_alumno, id_grupo) en BD
 *
 * PUT /api/form/inscripciones/:id_inscripcion
 * Body: { estatus }  <- unico campo editable (alumno e id_grupo no cambian)
 */
const guardar = async () => {
  errores.value = { id_grupo:'', fecha:'' }
  if (!form.value.id_inscripcion) {
    if (!form.value.id_alumno)         { mostrarNotificacion('Busca un alumno primero.','error'); return }
    if (!form.value.id_grupo)          { errores.value.id_grupo='Selecciona un grupo.'; mostrarNotificacion('El grupo es requerido.','error'); return }
    if (!form.value.fecha_inscripcion) { errores.value.fecha='La fecha es requerida.'; mostrarNotificacion('La fecha es requerida.','error'); return }
  }
  guardando.value=true
  const esEd = !!form.value.id_inscripcion
  try {
    const url  = esEd ? `${API}/inscripciones/${form.value.id_inscripcion}` : `${API}/inscripciones`
    const meth = esEd ? 'PUT' : 'POST'
    const body = esEd
      ? { estatus: form.value.estatus }
      : { id_alumno: form.value.id_alumno, id_grupo: form.value.id_grupo, fecha_inscripcion: form.value.fecha_inscripcion, estatus: form.value.estatus }
    const res = await fetch(url, { method:meth, headers:{ 'Content-Type':'application/json', 'Accept':'application/json' }, body: JSON.stringify(body) })
    const data = await res.json()
    if (!res.ok) throw new Error(JSON.stringify(data))
    await cargarInscripciones()
    await cargarKpis()
    cerrarModal()
    mostrarNotificacion(esEd ? 'Inscripcion actualizada.' : 'Inscripcion creada.', 'exito')
  } catch(e) {
    console.error(e)
    mostrarNotificacion('Error al guardar. El alumno puede ya estar inscrito en ese grupo.', 'error')
  } finally { guardando.value=false }
}

/*
 * DELETE /api/form/inscripciones/:id_inscripcion
 * Respuesta: { message: "Eliminado correctamente" }
 */
const pedirConfirmarEliminar = () => {
  inscripcionAEliminar.value = {
    ...form.value,
    nombre_alumno: alumnoEncontrado.value?.nombre_alumno || form.value.numero_control
  }
  showModal.value=false
  showModalEliminar.value=true
}

const confirmarEliminar = async () => {
  guardando.value=true
  try {
    const res = await fetch(`${API}/inscripciones/${inscripcionAEliminar.value.id_inscripcion}`, {
      method: 'DELETE',
      headers: { 'Accept': 'application/json' }
    })
    const data = await res.json()
    if (!res.ok) throw new Error(JSON.stringify(data))
    await cargarInscripciones()
    await cargarKpis()
    showModalEliminar.value=false
    mostrarNotificacion('Inscripcion eliminada.', 'exito')
  } catch(e) {
    console.error(e)
    mostrarNotificacion('Error al eliminar.', 'error')
  } finally { guardando.value=false }
}

const abrirModalNueva  = () => { form.value=formVacio(); alumnoEncontrado.value=null; grupoActual.value=''; errores.value={id_grupo:'',fecha:''}; showModal.value=true }
const abrirModalVer    = (ins) => { inscripcionVer.value=ins; showModalVer.value=true }
const abrirModalEditar = (ins) => {
  form.value = { id_inscripcion:ins.id_inscripcion, numero_control:ins.numero_control, id_alumno:ins.id_alumno, id_grupo:ins.id_grupo, fecha_inscripcion:ins.fecha_inscripcion, estatus:ins.estatus }
  alumnoEncontrado.value = { nombre_alumno:ins.nombre_alumno, nombre_carrera:ins.nombre_carrera }
  grupoActual.value = `${ins.clave_grupo} — ${ins.nombre_materia} | ${ins.nombre_periodo}`
  errores.value = { id_grupo:'', fecha:'' }
  showModal.value=true
}
const cerrarModal = () => { showModal.value=false }

const normalize = t => (t||'').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'')
const aplicarBusqueda = () => { if(!busquedaInput.value.trim()) return; busquedaActiva.value=busquedaInput.value.trim(); currentPage.value=1 }
const limpiarBusqueda = () => { busquedaInput.value=''; busquedaActiva.value=''; currentPage.value=1 }

const inscripcionesFiltradas = computed(() => inscripciones.value.filter(i => {
  const q = normalize(busquedaActiva.value)
  return (!q || normalize(i.numero_control).includes(q) || normalize(i.nombre_alumno).includes(q))
      && (!filtroPeriodo.value || i.id_periodo == filtroPeriodo.value)
      && (!filtroCarrera.value || i.id_carrera == filtroCarrera.value)
      && (!filtroEstatus.value || i.estatus === filtroEstatus.value)
}))

const totalPages         = computed(() => Math.ceil(inscripcionesFiltradas.value.length / filasPorPagina.value) || 1)
const inscripcionesPaginadas = computed(() => { const s=(currentPage.value-1)*filasPorPagina.value; return inscripcionesFiltradas.value.slice(s, s+filasPorPagina.value) })
const visiblePages       = computed(() => { const t=totalPages.value,c=currentPage.value; if(t<=7) return Array.from({length:t},(_,i)=>i+1); const p=new Set([1,t,c,c-1,c+1].filter(x=>x>=1&&x<=t)); return [...p].sort((a,b)=>a-b) })
const goToPage  = p => { currentPage.value=p; filaActiva.value=-1 }
const prevPage  = () => { if(currentPage.value>1) currentPage.value-- }
const nextPage  = () => { if(currentPage.value<totalPages.value) currentPage.value++ }
const resetFiltros = () => { limpiarBusqueda(); filtroPeriodo.value=''; filtroCarrera.value=''; filtroEstatus.value=''; filaActiva.value=-1 }

const claseEstatus = e => ({ 'activo':e==='Activo', 'baja-temporal':e==='Baja Temporal', 'baja-definitiva':e==='Baja Definitiva' })
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');
.alumnos-page{--borde:#E5E7EB;--fondo:#F5F5F5;max-width:100%;background:var(--fondo);font-family:'Montserrat',sans-serif;padding-bottom:2rem}
.page-header{display:flex;flex-direction:column;gap:4px;margin-bottom:1.2rem}
.page-title{color:#1A1A1A;font-size:1.75rem;font-weight:700;letter-spacing:-.02em;margin:0}
.page-subtitle{font-size:.9rem;color:#6B7280;font-weight:500;margin:0}
.barra-carga-global{height:3px;background:transparent;border-radius:2px;margin-bottom:1rem;overflow:hidden;opacity:0;transition:opacity .3s}
.barra-carga-global.visible{opacity:1}
.barra-progreso{height:100%;width:40%;background:#1B396A;border-radius:2px;animation:deslizar 1.2s ease-in-out infinite}
@keyframes deslizar{0%{transform:translateX(-100%)}100%{transform:translateX(350%)}}
.notificacion-ui{display:flex;align-items:center;gap:10px;padding:12px 18px;border-radius:10px;font-size:.93rem;font-weight:500;margin-bottom:1rem;box-shadow:0 4px 12px rgba(0,0,0,.1)}
.notificacion-ui.exito{background:#DCFCE7;color:#16A34A;border:1px solid #86EFAC}
.notificacion-ui.error{background:#FEE2E2;color:#DC2626;border:1px solid #FCA5A5}
.notif-icono{width:20px;height:20px;flex-shrink:0}
.notif-fade-enter-active,.notif-fade-leave-active{transition:all .35s ease}
.notif-fade-enter-from,.notif-fade-leave-to{opacity:0;transform:translateY(-8px)}
.stats-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin-bottom:1.5rem}
.stat-card{background:#FFFFFF;border-radius:12px;padding:1.3rem 1.4rem;display:flex;align-items:center;gap:1rem;box-shadow:0 4px 12px rgba(0,0,0,.05);border:1px solid #E5E7EB;transition:transform .2s,box-shadow .2s;min-width:0}
.stat-card:hover{transform:translateY(-3px);box-shadow:0 8px 20px rgba(0,0,0,.09)}
.stat-azul{background:#1B396A!important;border-color:#1B396A!important}
.stat-azul .stat-label{color:rgba(255,255,255,.8)!important}
.stat-ico-wrap{width:46px;height:46px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:#DBEAFE}
.stat-azul .stat-ico-wrap{background:rgba(255,255,255,.2)!important}
.stat-info{display:flex;flex-direction:column;gap:2px;min-width:0}
.stat-label{font-size:.83rem;color:#6B7280;font-weight:500;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.stat-num{font-size:2rem;font-weight:700;color:#1A1A1A;margin:2px 0;line-height:1}
.stat-skel{width:80px;height:32px;background:linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%);background-size:200% 100%;animation:shimmer 1.4s infinite;border-radius:6px;margin:2px 0}
.stat-skel-white{background:linear-gradient(90deg,rgba(255,255,255,.2) 25%,rgba(255,255,255,.35) 50%,rgba(255,255,255,.2) 75%)!important;background-size:200% 100%!important}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
.alerta-error{display:flex;align-items:center;gap:10px;background:#FEF2F2;border:1px solid #FECACA;border-radius:10px;padding:12px 16px;margin-bottom:1.4rem;font-size:.9rem;color:#DC2626;font-weight:500}
.ae-ico{width:20px;height:20px;flex-shrink:0;stroke:#DC2626}
.btn-reintentar{margin-left:auto;background:#1B396A;color:#FFF;border:none;padding:7px 16px;border-radius:6px;font-weight:600;font-size:.85rem;cursor:pointer;font-family:'Montserrat',sans-serif;transition:background .15s;white-space:nowrap}
.btn-reintentar:hover{background:#1D4ED8}
.filters-bar{display:flex;align-items:center;gap:.75rem;margin-bottom:1.2rem;flex-wrap:wrap}
.search-group{position:relative;flex:0 0 300px;min-width:220px}
.search-input{width:100%;padding:10px 14px 10px 42px;border:1px solid #E5E7EB;border-radius:8px;font-size:.93rem;background:#FFF;color:#1A1A1A;font-family:'Montserrat',sans-serif;outline:none;transition:border-color .2s,box-shadow .2s;box-sizing:border-box}
.search-input:focus{border-color:#1B396A;box-shadow:0 0 0 3px #DBEAFE}
.search-input::placeholder{color:#9CA3AF}
.search-icon-svg{position:absolute;left:13px;top:50%;transform:translateY(-50%);width:18px;height:18px;stroke:#6B7280;pointer-events:none}
.badge-busqueda-activa{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:#DBEAFE;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center}
.btn-buscar-tabla{display:inline-flex;align-items:center;gap:6px;padding:10px 16px;border-radius:8px;font-weight:600;font-size:.92rem;cursor:pointer;font-family:'Montserrat',sans-serif;white-space:nowrap;background:#1B396A;color:#FFF;border:2px solid #1B396A;transition:background .15s}
.btn-buscar-tabla:hover:not(:disabled){background:#1D4ED8;border-color:#1D4ED8}
.btn-buscar-tabla:disabled{opacity:.45;cursor:not-allowed}
.filter-select{padding:10px 12px;border:1px solid #E5E7EB;border-radius:8px;font-size:.92rem;flex:1 1 150px;min-width:130px;background:#FFF;color:#1A1A1A;font-family:'Montserrat',sans-serif;cursor:pointer;outline:none}
.filter-select:focus{border-color:#1B396A}
.btn-limpiar{display:flex;align-items:center;gap:6px;background:#FFF;color:#1A1A1A;border:1px solid #E5E7EB;padding:10px 16px;border-radius:8px;font-weight:600;cursor:pointer;font-size:.92rem;white-space:nowrap;font-family:'Montserrat',sans-serif;transition:background .15s}
.btn-limpiar:hover{background:#F5F5F5}
.reset-icon{width:16px;height:16px;stroke:#6B7280}
.btn-nuevo{background:#1B396A;color:#FFF;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer;white-space:nowrap;font-family:'Montserrat',sans-serif;font-size:.92rem;transition:background .2s;margin-left:auto}
.btn-nuevo:hover{background:#1D4ED8}
.table-container{background:#FFF;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.05);border:1px solid #E5E7EB}
.alumnos-table{width:100%;border-collapse:collapse;outline:none}
.alumnos-table th{background:#F5F5F5;padding:12px 16px;text-align:left;font-weight:600;font-size:.88rem;color:#1A1A1A;border-bottom:1px solid #E5E7EB;font-family:'Montserrat',sans-serif;white-space:nowrap}
.alumnos-table td{padding:11px 16px;border-bottom:1px solid #E5E7EB;color:#1A1A1A;font-size:.93rem;font-family:'Montserrat',sans-serif}
.alumnos-table tbody tr{transition:background .15s;cursor:pointer}
.alumnos-table tbody tr:hover{background:#F8FAFC}
.alumnos-table tbody tr:last-child td{border-bottom:none}
.fila-seleccionada{background:#DBEAFE!important}
.celda-control{font-weight:600;color:#1B396A}
.celda-acciones{display:flex;gap:7px;align-items:center}
.btn-accion{display:flex;align-items:center;gap:5px;padding:6px 13px;border-radius:6px;font-size:.85rem;cursor:pointer;font-weight:600;font-family:'Montserrat',sans-serif;transition:background .15s;white-space:nowrap}
.btn-accion svg{width:14px;height:14px}
.btn-accion.ver{background:#FFF;color:#1A1A1A;border:1px solid #E5E7EB}
.btn-accion.ver:hover{background:#F5F5F5}
.btn-accion.editar{background:#1B396A;color:#FFF;border:1px solid #1B396A}
.btn-accion.editar svg{stroke:#FFF}
.btn-accion.editar:hover{background:#1D4ED8;border-color:#1D4ED8}
.estatus-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:.83rem;font-weight:600;font-family:'Montserrat',sans-serif}
.estatus-badge.activo{background:#DCFCE7;color:#16A34A}
.estatus-badge.baja-temporal{background:#FEF3C7;color:#F59E0B}
.estatus-badge.baja-definitiva{background:#FEE2E2;color:#DC2626}
.estado-vacio,.estado-cargando{text-align:center;padding:3.5rem 2rem;color:#6B7280}
.icono-vacio{width:56px;height:56px;stroke:#9CA3AF;margin-bottom:12px}
.estado-vacio h3{font-size:1.2rem;color:#1A1A1A;margin:0 0 6px;font-family:'Montserrat',sans-serif}
.estado-vacio p{font-size:.93rem;margin:0 0 1.2rem;font-family:'Montserrat',sans-serif}
.spinner-tabla{display:inline-block;width:36px;height:36px;border:3px solid #E5E7EB;border-top-color:#1B396A;border-radius:50%;animation:girar .8s linear infinite;margin-bottom:12px}
.btn-limpiar-vacio{background:#FFF;color:#1A1A1A;border:1px solid #E5E7EB;padding:9px 20px;border-radius:8px;font-weight:500;cursor:pointer;font-family:'Montserrat',sans-serif}
.paginacion{margin-top:1.2rem;display:flex;justify-content:space-between;align-items:center;font-size:.9rem;color:#6B7280;font-family:'Montserrat',sans-serif;flex-wrap:wrap;gap:.5rem}
.paginacion-izquierda,.paginacion-centro,.paginacion-derecha{display:flex;align-items:center;gap:8px}
.select-filas{border:1px solid #E5E7EB;border-radius:6px;padding:4px 8px;font-size:.9rem;background:#FFF;font-family:'Montserrat',sans-serif}
.btn-pag{padding:5px 11px;border:1px solid #E5E7EB;background:#FFF;border-radius:6px;cursor:pointer;color:#1A1A1A;font-family:'Montserrat',sans-serif;font-size:.9rem;transition:background .15s}
.btn-pag:hover:not(:disabled){background:#F5F5F5}
.btn-pag:disabled{opacity:.4;cursor:not-allowed}
.btn-pag.activo{background:#1B396A;color:#FFF;border-color:#1B396A}
.pie-pagina{text-align:center;color:#9CA3AF;font-size:.82rem;padding-top:2rem;border-top:1px solid #E5E7EB;margin-top:1rem;font-family:'Montserrat',sans-serif}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);display:flex;align-items:center;justify-content:center;z-index:2000;padding:1rem}
.modal-content{background:#FFF;width:540px;max-width:95%;border-radius:14px;box-shadow:0 20px 50px rgba(0,0,0,.22);overflow:hidden;border:1px solid #E5E7EB;max-height:90vh;display:flex;flex-direction:column;font-family:'Montserrat',sans-serif}
.modal-header{background:#1B396A;padding:1.1rem 1.6rem;display:flex;justify-content:space-between;align-items:center;flex-shrink:0}
.modal-header h3{margin:0;font-size:1.1rem;font-weight:700;color:#FFF;font-family:'Montserrat',sans-serif}
.btn-cerrar-modal{background:none;border:none;color:#FFF;font-size:1.8rem;cursor:pointer;line-height:1;opacity:.85;padding:0}
.btn-cerrar-modal:hover{opacity:1}
.modal-body{padding:1.6rem;overflow-y:auto;flex:1}
.modal-footer{padding:1rem 1.6rem;background:#F5F5F5;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid #E5E7EB;flex-shrink:0}
.form-grupo{margin-bottom:1.2rem}
.form-grupo label{display:block;margin-bottom:6px;font-weight:600;font-size:.9rem;color:#1A1A1A;font-family:'Montserrat',sans-serif}
.modal-input,.modal-select{width:100%;padding:10px 14px;border:1.5px solid #E5E7EB;border-radius:8px;font-size:.95rem;background:#FFF;color:#1A1A1A;font-family:'Montserrat',sans-serif;outline:none;transition:border-color .2s,box-shadow .2s;box-sizing:border-box}
.modal-input:focus,.modal-select:focus{border-color:#1B396A;box-shadow:0 0 0 3px #DBEAFE}
.modal-input::placeholder{color:#9CA3AF}
.modal-input.deshabilitado{background:#F5F5F5;color:#6B7280;font-weight:600;cursor:not-allowed;display:flex;align-items:center}
.error-campo{font-size:.78rem;color:#DC2626;font-weight:600;margin:4px 0 0;font-family:'Montserrat',sans-serif}
.bloque-alumno-found{display:flex;align-items:center;gap:10px;background:#F0FDF4;border:1px solid #86EFAC;border-radius:8px;padding:10px 14px;margin-bottom:1.2rem}
.detalle-fila{display:flex;justify-content:space-between;align-items:center;padding:11px 0;border-bottom:1px solid #E5E7EB;font-size:.95rem;font-family:'Montserrat',sans-serif;gap:1rem}
.detalle-fila:last-child{border-bottom:none}
.detalle-label{font-weight:600;color:#6B7280;flex-shrink:0}
.detalle-valor{font-weight:500;color:#1A1A1A;text-align:right}
.btn-secundario{padding:10px 22px;border-radius:8px;font-weight:600;font-size:.92rem;cursor:pointer;font-family:'Montserrat',sans-serif;background:#FFF;color:#1B396A;border:2px solid #1B396A;transition:background .15s}
.btn-secundario:hover:not(:disabled){background:#DBEAFE}
.btn-secundario:disabled{opacity:.5;cursor:not-allowed}
.btn-eliminar{display:inline-flex;align-items:center;gap:6px;padding:10px 22px;border-radius:8px;font-weight:600;font-size:.92rem;cursor:pointer;font-family:'Montserrat',sans-serif;background:#DC2626;color:#FFF;border:2px solid #DC2626;transition:background .15s}
.btn-eliminar:hover:not(:disabled){background:#B91C1C;border-color:#B91C1C}
.btn-eliminar:disabled{opacity:.5;cursor:not-allowed}
.btn-guardar{display:inline-flex;align-items:center;gap:6px;padding:10px 22px;border-radius:8px;font-weight:600;font-size:.92rem;cursor:pointer;font-family:'Montserrat',sans-serif;background:#1B396A;color:#FFF;border:2px solid #1B396A;transition:background .15s}
.btn-guardar:hover:not(:disabled){background:#1D4ED8;border-color:#1D4ED8}
.btn-guardar:disabled{opacity:.55;cursor:not-allowed}
.btn-buscar-modal{display:inline-flex;align-items:center;gap:6px;padding:10px 16px;border-radius:8px;font-weight:600;font-size:.9rem;cursor:pointer;font-family:'Montserrat',sans-serif;white-space:nowrap;background:#1B396A;color:#FFF;border:2px solid #1B396A;transition:background .15s}
.btn-buscar-modal:hover:not(:disabled){background:#1D4ED8}
.btn-buscar-modal:disabled{opacity:.6;cursor:not-allowed}
.spinner-btn{display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,.4);border-top-color:#FFF;border-radius:50%;animation:girar .7s linear infinite;flex-shrink:0}
@keyframes girar{to{transform:rotate(360deg)}}
@media(max-width:1024px){.stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:640px){.stats-grid{grid-template-columns:1fr}.filters-bar{flex-direction:column}.filter-select,.search-group{width:100%;flex:none}}
</style>