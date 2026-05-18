<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="gestion-page">

      <!-- Barra de carga global -->
      <div class="barra-carga-global" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- Notificaciones -->
      <transition name="notif-fade">
        <div v-if="notificacion.visible" class="notificacion-ui" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo === 'exito'" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- Breadcrumb -->
      <nav class="breadcrumb">
        <span class="breadcrumb-link" @click="$router.push('/gestion-academica')">Gestión Académica</span>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-actual">Alumnos</span>
      </nav>

      <!-- Header -->
      <div class="page-header">
        <h1 class="page-title">Alumnos</h1>
        <p class="page-subtitle">{{ alumnosFiltrados.length }} registro(s) encontrado(s)</p>
      </div>

      <!-- Barra de acciones: búsqueda + botón filtros -->
      <div class="barra-acciones">
        <div class="busqueda-grupo">
          <div class="input-con-icono">
            <svg class="icono-input" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input 
              type="text" 
              ref="inputBusqueda"
              v-model="busquedaAlumno" 
              class="input-busqueda" 
              placeholder="Buscar por número de control o nombre..."
              @input="currentPage = 1"
              @keydown.escape="limpiarBusqueda"
            />
            <button v-if="busquedaAlumno" class="btn-limpiar-input" @click="limpiarBusqueda" title="Limpiar búsqueda">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <button class="btn-buscar" @click="currentPage = 1" :disabled="cargando">Buscar</button>
        </div>

        <button class="btn-filtro" @click="mostrarFiltros = !mostrarFiltros" :class="{ activo: filtrosActivos }">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-btn" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
          </svg>
          Filtros
          <span v-if="filtrosActivos" class="filtros-badge">{{ contadorFiltros }}</span>
        </button>

        <button class="btn-nuevo" @click="nuevoAlumno">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Nueva inscripción
        </button>
      </div>

      <!-- Panel de filtros (colapsable) -->
      <transition name="filtros-slide">
        <div v-if="mostrarFiltros" class="filtros-panel">
          <div class="filtros-grid">
            <div class="filtro-item">
              <label class="filtro-label">Carrera</label>
              <select v-model="filtroCarreraId" class="filter-select" @change="currentPage = 1">
                <option value="">Todas las carreras</option>
                <option v-for="c in catalogos.carreras" :key="c.id_carrera" :value="c.id_carrera">{{ c.nombre }}</option>
              </select>
            </div>
            <div class="filtro-item">
              <label class="filtro-label">Semestre</label>
              <select v-model="filtroSemestre" class="filter-select" @change="currentPage = 1">
                <option value="">Todos</option>
                <option v-for="n in 8" :key="n" :value="String(n)">{{ n }}° Semestre</option>
              </select>
            </div>
            <div class="filtro-item">
              <label class="filtro-label">Estatus</label>
              <select v-model="filtroEstatusId" class="filter-select" @change="currentPage = 1">
                <option value="">Todos</option>
                <option v-for="e in catalogos.estatus_alumno" :key="e.id_estatus_alumno" :value="e.id_estatus_alumno">{{ e.nombre }}</option>
              </select>
            </div>
          </div>
          <div class="filtros-footer">
            <button class="btn-limpiar" @click="resetFilters">
              <svg class="reset-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Limpiar filtros
            </button>
          </div>
        </div>
      </transition>

      <!-- Contenedor de la tabla -->
      <div class="tabla-contenedor">
        <div v-if="cargando && alumnos.length === 0" class="estado-cargando">
          <div class="spinner-tabla"></div>
          <p>Cargando registros...</p>
        </div>

        <table v-else-if="paginatedAlumnos.length > 0" class="data-table" ref="tablaRef" @keydown="navegarTeclado" tabindex="0">
          <thead>
            <tr>
              <th>No. Control</th>
              <th>Nombre Completo</th>
              <th>Carrera</th>
              <th class="th-centro">Sem.</th>
              <th class="th-centro">Estatus</th>
              <th class="th-acciones">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(alumno, index) in paginatedAlumnos"
              :key="alumno.id_alumno || alumno.id"
              :class="{ 'fila-seleccionada': filaActiva === index }"
              @click="abrirModalVer(alumno)"
            >
              <td class="celda-control">{{ alumno.numero_control || alumno.noControl }}</td>
              <td class="celda-nombre">{{ resolverNombre(alumno) }}</td>
              <td class="celda-secundaria">{{ resolverCarrera(alumno) }}</td>
              <td class="td-centro">{{ alumno.semestre_actual || alumno.semestre }}°</td>
              <td class="td-centro">
                <span class="estatus-badge" :class="claseEstatus(alumno.estatus)">{{ alumno.estatus }}</span>
              </td>
              <td class="celda-acciones">
                <button class="btn-icono ver" @click.stop="abrirModalVer(alumno)" title="Ver detalles">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
                <button class="btn-icono editar" @click.stop="abrirModalEditar(alumno)" title="Editar">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="estado-vacio">
          <svg class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3>Sin resultados</h3>
          <p>No se encontraron alumnos con los criterios aplicados.</p>
          <button class="btn-limpiar-vacio" @click="resetFilters">Limpiar filtros</button>
        </div>
      </div>

      <!-- Paginación -->
      <div class="paginacion">
        <div class="paginacion-izquierda" v-if="alumnosFiltrados.length > 5">
          Filas por página:
          <select v-model="filasPorPagina" @change="currentPage = 1" class="select-filas">
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
        </div>
        <div class="paginacion-izquierda" v-else></div>
        <div class="paginacion-centro">Página {{ currentPage }} de {{ totalPages }}</div>
        <div class="paginacion-derecha">
          <button class="btn-pag" @click="prevPage" :disabled="currentPage === 1">‹</button>
          <button v-for="page in visiblePages" :key="page" class="btn-pag" :class="{ activo: page === currentPage }" @click="goToPage(page)">{{ page }}</button>
          <button class="btn-pag" @click="nextPage" :disabled="currentPage === totalPages">›</button>
        </div>
      </div>

      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México | Todos los derechos reservados</footer>
    </div>

    <!-- ==================== MODALES (sin cambios) ==================== -->
    <Teleport to="body">
      <transition name="modal-fade">
        <div v-if="showViewModal && alumnoSeleccionado" class="modal-overlay" @click.self="cerrarModalVer">
          <div class="modal-content modal-ver-alumno">
            <div class="modal-header">
              <div class="modal-header-avatar-group">
                <div class="detalle-avatar">
                  <img v-if="alumnoSeleccionado.foto" :src="alumnoSeleccionado.foto" class="avatar-img" :alt="resolverNombre(alumnoSeleccionado)" />
                  <div v-else class="avatar-placeholder">
                    <span>{{ iniciales(resolverNombre(alumnoSeleccionado)) }}</span>
                  </div>
                </div>
                <div class="modal-header-info">
                  <span class="modal-header-tag">Ficha de Alumno</span>
                  <h3>{{ resolverNombre(alumnoSeleccionado) }}</h3>
                  <span class="sub-header-control">{{ alumnoSeleccionado.numero_control || alumnoSeleccionado.noControl }}</span>
                </div>
              </div>
              <button @click="cerrarModalVer" class="btn-cerrar-modal" title="Cerrar">×</button>
            </div>

            <div class="modal-body-tabs">
              <div class="detalle-tabs">
                <button v-for="tab in tabs" :key="tab.id" class="tab-btn" :class="{ activo: tabActivo === tab.id }" @click="tabActivo = tab.id">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon" />
                  </svg>
                  {{ tab.label }}
                </button>
              </div>

              <div class="tab-contenido-scroll">
                <div v-if="tabActivo === 'general'" class="tab-panel">
                  <div class="detalle-grid">
                    <div class="detalle-campo">
                      <span class="detalle-label">No. de Control</span>
                      <span class="detalle-valor mono-bold">{{ alumnoSeleccionado.numero_control || alumnoSeleccionado.noControl }}</span>
                    </div>
                    <div class="detalle-campo">
                      <span class="detalle-label">Semestre actual</span>
                      <span class="detalle-valor detalle-numero">{{ alumnoSeleccionado.semestre_actual || alumnoSeleccionado.semestre }}°</span>
                    </div>
                    <div class="detalle-campo full-width">
                      <span class="detalle-label">Carrera</span>
                      <span class="detalle-valor">{{ resolverCarrera(alumnoSeleccionado) }}</span>
                    </div>
                    <div class="detalle-campo">
                      <span class="detalle-label">Estatus académico</span>
                      <span class="estatus-badge-modal" :class="claseEstatusModal(alumnoSeleccionado.estatus)">{{ alumnoSeleccionado.estatus }}</span>
                    </div>
                    <div class="detalle-campo" v-if="alumnoSeleccionado.fecha_ingreso">
                      <span class="detalle-label">Fecha de ingreso</span>
                      <span class="detalle-valor">{{ formatearFecha(alumnoSeleccionado.fecha_ingreso) }}</span>
                    </div>
                    <div class="detalle-campo full-width" v-if="alumnoSeleccionado.email || alumnoSeleccionado.persona?.email">
                      <span class="detalle-label">Correo electrónico</span>
                      <span class="detalle-valor">{{ alumnoSeleccionado.email || alumnoSeleccionado.persona?.email }}</span>
                    </div>
                  </div>
                </div>

                <div v-if="tabActivo === 'kardex'" class="tab-panel">
                  <div v-if="cargandoKardex" class="kardex-cargando">
                    <div class="skeleton-linea largo"></div>
                    <div class="skeleton-linea medio"></div>
                    <div v-for="i in 4" :key="i" class="skeleton-fila"></div>
                  </div>
                  <div v-else-if="kardexError" class="kardex-error">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>No se pudo cargar el kardex.</span>
                    <button @click="cargarKardexAlumno(alumnoSeleccionado)">Reintentar</button>
                  </div>
                  <div v-else-if="kardexData">
                    <div class="creditos-bloque" v-if="kardexData.alumno?.creditos_totales">
                      <div class="creditos-row">
                        <span class="creditos-label">Avance de créditos</span>
                        <span class="creditos-pct" :class="{ verde: porcentajeCreditos >= 80 }">{{ kardexData.alumno.creditos_acumulados }}/{{ kardexData.alumno.creditos_totales }} ({{ porcentajeCreditos }}%)</span>
                      </div>
                      <div class="creditos-barra-track">
                        <div class="creditos-barra-fill" :style="{ width: porcentajeCreditos + '%', background: porcentajeCreditos >= 80 ? '#16A34A' : '#1B396A' }"></div>
                      </div>
                    </div>
                    <div v-if="kardexData.kardex?.semestres?.length" class="kardex-semestres">
                      <div v-for="semestre in kardexData.kardex.semestres" :key="semestre.numero" class="semestre-bloque">
                        <button class="semestre-btn" @click="toggleSemestre(semestre.numero)" :class="{ abierto: semestresAbiertos.includes(semestre.numero) }">
                          <span class="semestre-titulo">Semestre {{ semestre.numero }}</span>
                          <div class="semestre-badges">
                            <span class="badge-mini acreditadas">{{ semestre.acreditadas }} acred.</span>
                            <span v-if="semestre.reprobadas > 0" class="badge-mini reprobadas">{{ semestre.reprobadas }} repr.</span>
                            <svg class="flecha-semestre" :class="{ girado: semestresAbiertos.includes(semestre.numero) }" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                          </div>
                        </button>
                        <transition name="expand">
                          <div v-if="semestresAbiertos.includes(semestre.numero)" class="semestre-materias">
                            <table class="tabla-mini">
                              <thead><tr><th>Clave</th><th>Materia</th><th>Cal.</th><th>Estado</th></tr></thead>
                              <tbody>
                                <tr v-for="m in semestre.materias" :key="m.clave" :class="{ 'fila-repr': m.estado === 'reprobada' }">
                                  <td class="clave-mono">{{ m.clave }}</td>
                                  <td>{{ m.nombre }}</td>
                                  <td class="centrado"><strong v-if="m.calificacion !== null" :class="{ 'text-verde': m.estado === 'acreditada', 'text-rojo': m.estado === 'reprobada' }">{{ m.calificacion }}</strong><span v-else class="text-gris">—</span></td>
                                  <td><span class="badge-estado-mini" :style="estiloEstado(m.estado)">{{ etiquetaEstado(m.estado) }}</span></td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </transition>
                      </div>
                    </div>
                    <div v-else class="kardex-vacio"><p>No hay materias registradas en el kardex.</p></div>
                  </div>
                  <div v-else class="kardex-vacio"><p>Kardex no disponible.</p></div>
                </div>

                <div v-if="tabActivo === 'horario'" class="tab-panel">
                  <div v-if="cargandoHorario" class="kardex-cargando">
                    <div v-for="i in 5" :key="i" class="skeleton-fila"></div>
                  </div>
                  <div v-else-if="horarioData?.length" class="horario-lista">
                    <div v-for="materia in horarioData" :key="materia.id" class="horario-item">
                      <div class="horario-color" :style="{ background: colorMateria(materia.nombre) }"></div>
                      <div class="horario-info">
                        <span class="horario-nombre">{{ materia.nombre }}</span>
                        <span class="horario-meta">{{ materia.dias }} · {{ materia.hora_inicio }}–{{ materia.hora_fin }}</span>
                        <span class="horario-aula">Aula: {{ materia.aula || 'N/D' }}</span>
                      </div>
                    </div>
                  </div>
                  <div v-else class="kardex-vacio">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="32" height="32" style="stroke:#9CA3AF; margin-bottom:8px">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p>No hay horario registrado para este alumno.</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button class="btn-secundario" @click="cerrarModalVer">Cerrar</button>
              <button class="btn-accion-outline" @click="verKardex(alumnoSeleccionado); cerrarModalVer()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>
                Ver Kardex Completo
              </button>
              <button class="btn-guardar" @click="abrirModalEditar(alumnoSeleccionado); cerrarModalVer()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Editar
              </button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <Teleport to="body">
      <transition name="modal-fade">
        <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
          <div class="modal-content">
            <div class="modal-header">
              <div class="modal-header-info">
                <span class="modal-header-tag">{{ alumnoEditar.id_alumno ? 'Edición' : 'Nuevo' }}</span>
                <h3>{{ alumnoEditar.id_alumno ? 'Editar Alumno' : 'Nuevo Alumno' }}</h3>
              </div>
              <button @click="cerrarModal" class="btn-cerrar-modal" title="Cerrar">×</button>
            </div>
            <div class="modal-body form-body">
              <div class="form-grupo" v-if="alumnoEditar.id_alumno">
                <label>Número de Control</label>
                <input v-model="alumnoEditar.noControl" type="text" readonly class="modal-input deshabilitado" />
              </div>
              <div class="form-grupo">
                <label>Nombre completo <span class="obligatorio">*</span></label>
                <input v-model="alumnoEditar.nombre" type="text" class="modal-input" placeholder="Nombre completo" />
              </div>
              <div class="form-grupo">
                <label>Carrera <span class="obligatorio">*</span></label>
                <select v-model="alumnoEditar.id_carrera" class="modal-select">
                  <option value="">Seleccionar carrera</option>
                  <option v-for="c in catalogos.carreras" :key="c.id_carrera" :value="c.id_carrera">{{ c.nombre }}</option>
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
                  <select v-model="alumnoEditar.id_estatus_alumno" class="modal-select">
                    <option value="">Seleccionar estatus</option>
                    <option v-for="e in catalogos.estatus_alumno" :key="e.id_estatus_alumno" :value="e.id_estatus_alumno">{{ e.nombre }}</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">Cancelar</button>
              <button v-if="alumnoEditar.id_alumno" class="btn-eliminar" @click="solicitarEliminar" :disabled="guardando">Eliminar</button>
              <button class="btn-guardar" @click="guardarCambios" :disabled="guardando">
                <span v-if="guardando" class="spinner-btn"></span>
                {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
              </button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <Teleport to="body">
      <transition name="modal-fade">
        <div v-if="showModalEliminar" class="modal-overlay" @click.self="showModalEliminar = false">
          <div class="modal-content modal-confirmar">
            <div class="modal-header">
              <div class="modal-header-info">
                <span class="modal-header-tag">Confirmar</span>
                <h3>Eliminar Alumno</h3>
              </div>
              <button @click="showModalEliminar = false" class="btn-cerrar-modal">×</button>
            </div>
            <div class="modal-body confirmar-body">
              <svg class="confirmar-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <p>¿Estás seguro de eliminar a <strong>{{ alumnoEditar.nombre }}</strong>? Esta acción no se puede deshacer y borrará permanentemente sus registros.</p>
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
    </Teleport>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const router = useRouter()
const API_URL = import.meta.env.VITE_API_URL

// ── Estado principal ─────────────────────────────────────────────────
const alumnos          = ref([])
const cargando         = ref(false)
const guardando        = ref(false)
const filaActiva       = ref(-1)
const tablaRef         = ref(null)
const inputBusqueda    = ref(null)

// ── Modales y Selección ──────────────────────────────────────────────
const showViewModal      = ref(false)
const showModal          = ref(false)
const showModalEliminar  = ref(false)
const alumnoSeleccionado = ref(null)
const alumnoEditar       = ref({})

// ── Pestañas (Kardex y Horario) ──────────────────────────────────────
const tabActivo          = ref('general')
const kardexData         = ref(null)
const cargandoKardex     = ref(false)
const kardexError        = ref(false)
const horarioData        = ref(null)
const cargandoHorario    = ref(false)
const semestresAbiertos  = ref([])

const tabs = [
  { id: 'general', label: 'Datos',    icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
  { id: 'kardex',  label: 'Kardex',   icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z' },
  { id: 'horario', label: 'Horario',  icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
]

// ── Catálogos ────────────────────────────────────────────────────────
const catalogos = ref({ carreras: [], estatus_alumno: [] })

const cargarCatalogos = async () => {
  try {
    const res = await fetch(`${API_URL}/api/alumnos/catalogos`)
    if (!res.ok) throw new Error()
    const data = await res.json()
    catalogos.value.carreras       = data.carreras       || []
    catalogos.value.estatus_alumno = data.estatus_alumno || []
  } catch {
    mostrarNotificacion('No se pudieron cargar los catálogos.', 'error')
  }
}

// ── Filtros y paginación ─────────────────────────────────────────────
const busquedaAlumno  = ref('')
const mostrarFiltros  = ref(false)
const filtroCarreraId = ref('')
const filtroSemestre  = ref('')
const filtroEstatusId = ref('')
const filasPorPagina  = ref(10)
const currentPage     = ref(1)

// ── Computadas de Filtros ────────────────────────────────────────────
const filtrosActivos  = computed(() => !!(filtroCarreraId.value || filtroSemestre.value || filtroEstatusId.value))
const contadorFiltros = computed(() => [filtroCarreraId.value, filtroSemestre.value, filtroEstatusId.value].filter(Boolean).length)

// ── Notificación (Toast) ─────────────────────────────────────────────
const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null
const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

// ── Props ────────────────────────────────────────────────────────────
const props = defineProps({ busquedaGlobal: { type: String, default: '' } })

// ── Helpers ──────────────────────────────────────────────────────────
const normalize = (t) => !t ? '' : t.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
const claseEstatus = (estatus) => !estatus ? '' : estatus.toLowerCase().replace(/\s/g, '-')
const resolverNombre = (a) => a?.nombre || a?.persona?.nombre_completo || a?.persona?.nombre || ''
const resolverCarrera = (a) => a?.carrera?.nombre_carrera || a?.carrera || ''
const resolverIdCarrera = (a) => a?.id_carrera || a?.carrera?.id_carrera || null
const iniciales = (nombre) => !nombre ? '?' : nombre.split(' ').slice(0, 2).map(p => p[0]).join('').toUpperCase()
const formatearFecha = (f) => !f ? '' : new Date(f).toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' })

const colorMateria = (nombre) => {
  const colors = ['#1B396A', '#7C3AED', '#0891B2', '#059669', '#DC2626', '#D97706']
  let h = 0
  for (let i = 0; i < (nombre || '').length; i++) h += nombre.charCodeAt(i)
  return colors[h % colors.length]
}

const corregirNombreAlumno = (nombre) => {
  if (!nombre) return nombre
  return nombre.replace(/\bWilfido\b/gi, 'Wilfredo')
}

// ── Carga de alumnos ─────────────────────────────────────────────────
const cargarAlumnosDesdeBD = async () => {
  cargando.value = true
  try {
    const res = await fetch(`${API_URL}/api/alumnos-crud`)
    if (!res.ok) throw new Error()
    const data = await res.json()
    alumnos.value = data.map(a => {
      const nombreOriginal = a.nombre || a.persona?.nombre_completo || a.persona?.nombre || ''
      const nombreCorregido = corregirNombreAlumno(nombreOriginal)
      const alumnoCorregido = { ...a, nombre: nombreCorregido }
      if (a.persona) {
        alumnoCorregido.persona = {
          ...a.persona,
          nombre_completo: corregirNombreAlumno(a.persona.nombre_completo || ''),
          nombre: corregirNombreAlumno(a.persona.nombre || '')
        }
      }
      return alumnoCorregido
    })
  } catch {
    mostrarNotificacion('No se pudo cargar la lista de alumnos.', 'error')
  } finally {
    cargando.value = false
  }
}

onMounted(() => { cargarAlumnosDesdeBD(); cargarCatalogos() })

// ── Lógica Modal VER Alumno (con pestañas) ──────────────────────────
const abrirModalVer = (alumno) => {
  alumnoSeleccionado.value = alumno
  tabActivo.value = 'general'
  kardexData.value = null
  horarioData.value = null
  showViewModal.value = true
}
const cerrarModalVer = () => {
  showViewModal.value = false
  setTimeout(() => { alumnoSeleccionado.value = null }, 300)
}

watch(tabActivo, async (tab) => {
  if (!alumnoSeleccionado.value) return
  if (tab === 'kardex' && !kardexData.value) await cargarKardexAlumno(alumnoSeleccionado.value)
  if (tab === 'horario' && !horarioData.value) await cargarHorarioAlumno(alumnoSeleccionado.value)
})

const cargarKardexAlumno = async (alumno) => {
  const nc = alumno.numero_control || alumno.noControl
  if (!nc) return
  cargandoKardex.value = true
  kardexError.value = false
  try {
    const res = await fetch(`${API_URL}/api/kardex/${nc}`)
    if (!res.ok) throw new Error()
    kardexData.value = await res.json()
    if (kardexData.value?.kardex?.semestres?.length) {
      semestresAbiertos.value = [kardexData.value.kardex.semestres[0].numero]
    }
  } catch {
    kardexError.value = true
  } finally {
    cargandoKardex.value = false
  }
}

const cargarHorarioAlumno = async (alumno) => {
  const nc = alumno.numero_control || alumno.noControl
  if (!nc) return
  cargandoHorario.value = true
  try {
    const res = await fetch(`${API_URL}/api/horario/${nc}`)
    if (!res.ok) throw new Error()
    horarioData.value = await res.json()
  } catch {
    horarioData.value = []
  } finally {
    cargandoHorario.value = false
  }
}

const toggleSemestre = (num) => {
  const idx = semestresAbiertos.value.indexOf(num)
  if (idx === -1) semestresAbiertos.value.push(num)
  else semestresAbiertos.value.splice(idx, 1)
}

const porcentajeCreditos = computed(() => {
  const a = kardexData.value?.alumno
  if (!a?.creditos_totales) return 0
  return Math.round((a.creditos_acumulados / a.creditos_totales) * 100)
})

const estiloEstado = (estado) => {
  const map = {
    acreditada: { background: '#DCFCE7', color: '#16A34A' },
    reprobada:  { background: '#FEF2F2', color: '#DC2626' },
    pendiente:  { background: '#FEF3C7', color: '#F59E0B' },
    no_cursada: { background: '#F3F4F6', color: '#6B7280' },
  }
  return map[estado] ?? map.no_cursada
}
const etiquetaEstado = (estado) => {
  const map = { acreditada: 'Acred.', reprobada: 'Repr.', pendiente: 'En curso', no_cursada: 'Pendiente' }
  return map[estado] ?? estado
}

const verKardex = (alumno) => {
  const nc = alumno.numero_control || alumno.noControl
  if (nc) router.push(`/kardex/${nc}`)
}

const claseEstatusModal = (estatus) => {
  if (!estatus) return ''
  const e = estatus.toLowerCase()
  if (e === 'activo')           return 'badge-activo'
  if (e.includes('temporal'))   return 'badge-temporal'
  if (e.includes('definitiva')) return 'badge-definitiva'
  if (e === 'titulado')         return 'badge-titulado'
  if (e === 'egresado')         return 'badge-egresado'
  return 'badge-activo'
}

// ── Modal Editar / Crear ──────────────────────────────────────────────
const abrirModalEditar = (alumno) => {
  alumnoEditar.value = {
    id_alumno:         alumno.id_alumno || alumno.id,
    noControl:         alumno.numero_control || alumno.noControl || '',
    nombre:            resolverNombre(alumno),
    id_carrera:        resolverIdCarrera(alumno),
    semestre:          alumno.semestre_actual || alumno.semestre || 1,
    id_estatus_alumno: alumno.id_estatus_alumno ||
      catalogos.value.estatus_alumno.find(e => e.nombre === alumno.estatus)?.id_estatus_alumno || null
  }
  showModal.value = true
}

const cerrarModal = () => { showModal.value = false }
const nuevoAlumno = () => router.push('/formulario-alumno')

const guardarCambios = async () => {
  const id = alumnoEditar.value.id_alumno
  if (!id) { mostrarNotificacion('No se encontró el identificador.', 'error'); return }
  if (!alumnoEditar.value.id_carrera) { mostrarNotificacion('Selecciona una carrera.', 'error'); return }
  if (!alumnoEditar.value.id_estatus_alumno) { mostrarNotificacion('Selecciona un estatus.', 'error'); return }

  const nombreEstatus = catalogos.value.estatus_alumno.find(e => e.id_estatus_alumno === alumnoEditar.value.id_estatus_alumno)?.nombre || 'Activo'

  guardando.value = true
  try {
    const res = await fetch(`${API_URL}/api/alumnos/${id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({
        nombre:            alumnoEditar.value.nombre,
        id_carrera:        alumnoEditar.value.id_carrera,
        semestre_actual:   parseInt(alumnoEditar.value.semestre),
        estatus:           nombreEstatus,
        id_estatus_alumno: alumnoEditar.value.id_estatus_alumno
      })
    })
    const data = await res.json()
    if (res.ok) {
      await cargarAlumnosDesdeBD()
      cerrarModal()
      mostrarNotificacion('Alumno actualizado correctamente.', 'exito')
    } else {
      mostrarNotificacion(data.message || data.error || 'Error al actualizar.', 'error')
    }
  } catch {
    mostrarNotificacion('Error de conexión.', 'error')
  } finally {
    guardando.value = false
  }
}

const solicitarEliminar = () => {
  showModal.value = false
  showModalEliminar.value = true
}

const confirmarEliminar = async () => {
  const id = alumnoEditar.value.id_alumno
  if (!id) return
  guardando.value = true
  try {
    const res = await fetch(`${API_URL}/api/alumnos/${id}`, {
      method: 'DELETE',
      headers: { 'Accept': 'application/json' }
    })
    let data = {}
    if (res.status !== 204) data = await res.json().catch(() => ({}))
    if (res.ok) {
      await cargarAlumnosDesdeBD()
      showModalEliminar.value = false
      mostrarNotificacion('Alumno eliminado correctamente.', 'exito')
    } else {
      mostrarNotificacion(data.message || 'No se pudo eliminar el alumno.', 'error')
    }
  } catch {
    mostrarNotificacion('Error de conexión al eliminar.', 'error')
  } finally {
    guardando.value = false
  }
}

// ── Filtrado y Paginación ───────────────────────────────────────────
const alumnosFiltrados = computed(() => {
  return alumnos.value.filter(alumno => {
    const nombre    = resolverNombre(alumno)
    const noControl = (alumno.numero_control || alumno.noControl || '').toString()
    const busqGlobal = !props.busquedaGlobal ||
      normalize(nombre).includes(normalize(props.busquedaGlobal)) ||
      noControl.includes(props.busquedaGlobal)
    const busqLocal = !busquedaAlumno.value ||
      normalize(nombre).includes(normalize(busquedaAlumno.value)) ||
      noControl.includes(busquedaAlumno.value)
    const filtCarrera  = !filtroCarreraId.value || Number(resolverIdCarrera(alumno)) === Number(filtroCarreraId.value)
    const filtSemestre = !filtroSemestre.value  || String(alumno.semestre_actual || alumno.semestre) === String(filtroSemestre.value)
    const filtEstatus  = !filtroEstatusId.value || Number(alumno.id_estatus_alumno) === Number(filtroEstatusId.value)
    return busqGlobal && busqLocal && filtCarrera && filtSemestre && filtEstatus
  })
})

const totalPages = computed(() => Math.ceil(alumnosFiltrados.value.length / filasPorPagina.value) || 1)
const paginatedAlumnos = computed(() => {
  const start = (currentPage.value - 1) * filasPorPagina.value
  return alumnosFiltrados.value.slice(start, start + filasPorPagina.value)
})
const visiblePages = computed(() => {
  const total = totalPages.value, cur = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = new Set([1, total, cur, cur - 1, cur + 1].filter(p => p >= 1 && p <= total))
  return [...pages].sort((a, b) => a - b)
})

const goToPage = (p) => { currentPage.value = p; filaActiva.value = -1 }
const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

const resetFilters = () => {
  busquedaAlumno.value = ''
  filtroCarreraId.value = filtroSemestre.value = filtroEstatusId.value = ''
  currentPage.value = 1; filaActiva.value = -1
}

const limpiarBusqueda = () => { busquedaAlumno.value = ''; currentPage.value = 1; nextTick(() => inputBusqueda.value?.focus()) }

// ── Navegación por teclado ───────────────────────────────────────────
const navegarTeclado = (e) => {
  const total = paginatedAlumnos.value.length
  if (total === 0) return
  if (e.key === 'ArrowDown')     { e.preventDefault(); filaActiva.value = Math.min(filaActiva.value + 1, total - 1) }
  else if (e.key === 'ArrowUp')  { e.preventDefault(); filaActiva.value = Math.max(filaActiva.value - 1, 0) }
  else if (e.key === 'Enter' && filaActiva.value >= 0) { e.preventDefault(); abrirModalVer(paginatedAlumnos.value[filaActiva.value]) }
  else if (e.key === 'PageDown') { e.preventDefault(); nextPage() }
  else if (e.key === 'PageUp')   { e.preventDefault(); prevPage() }
}
</script>

<style scoped>
/* ========== ESTILOS NUEVOS (diseño solicitado) ========== */
.gestion-page {
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
  background: #F5F5F5;
  font-family: 'Montserrat', sans-serif;
}

/* Breadcrumb */
.breadcrumb {
  margin-bottom: 15px;
  font-size: 0.9rem;
  color: #6B7280;
}
.breadcrumb-link { cursor: pointer; color: #1B396A; font-weight: 500; }
.breadcrumb-link:hover { text-decoration: underline; }
.breadcrumb-sep { margin: 0 8px; }
.breadcrumb-actual { color: #374151; }

/* Page header */
.page-header { margin-bottom: 25px; }
.page-title { font-size: 1.8rem; font-weight: 700; color: #111827; margin: 0; }
.page-subtitle { color: #6B7280; margin-top: 5px; font-size: 0.95rem; }

/* Barra de acciones */
.barra-acciones {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  gap: 16px;
  flex-wrap: wrap;
}
.busqueda-grupo {
  display: flex;
  gap: 12px;
  flex: 1;
  max-width: 600px;
  align-items: center;
}
.input-con-icono {
  position: relative;
  flex: 1;
}
.icono-input {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
  color: #9CA3AF;
}
.input-busqueda {
  width: 100%;
  padding: 10px 14px 10px 42px;
  border: 1px solid #D1D5DB;
  border-radius: 8px;
  font-family: 'Montserrat', sans-serif;
  font-size: 0.95rem;
  transition: all 0.2s;
  box-sizing: border-box;
  background: #FFFFFF;
}
.input-busqueda:focus {
  outline: none;
  border-color: #1B396A;
  box-shadow: 0 0 0 3px rgba(27, 57, 106, 0.15);
}
.btn-limpiar-input {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #6B7280;
  cursor: pointer;
  display: flex;
  align-items: center;
}

.btn-buscar {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 10px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  background: #1B396A;
  color: white;
  border: 2px solid #1B396A;
  transition: background 0.15s, border-color 0.15s;
  font-size: 0.95rem;
}
.btn-buscar:hover:not(:disabled) {
  background: #1D4ED8;
  border-color: #1D4ED8;
}
.btn-buscar:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.btn-filtro {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  background: #FFFFFF;
  color: #374151;
  border: 1px solid #D1D5DB;
  transition: all 0.2s;
  font-size: 0.95rem;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.btn-filtro:hover {
  background: #F9FAFB;
  border-color: #9CA3AF;
  color: #111827;
}
.btn-filtro.activo {
  border-color: #1B396A;
  color: #1B396A;
  background: #DBEAFE;
}
.btn-filtro.activo .icono-btn {
  color: #1B396A;
}
.icono-btn {
  width: 18px;
  height: 18px;
  color: #6B7280;
}
.filtros-badge {
  background: #1B396A;
  color: white;
  font-size: 0.72rem;
  font-weight: 700;
  border-radius: 10px;
  padding: 1px 6px;
  margin-left: 2px;
}

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
  display: flex;
  align-items: center;
  gap: 6px;
}
.btn-nuevo:hover {
  background: #1D4ED8;
}

/* Panel de filtros */
.filtros-panel {
  background: #FFFFFF;
  border: 1px solid #E5E7EB;
  border-radius: 10px;
  padding: 1.2rem 1.4rem 1rem;
  margin-bottom: 1rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.filtros-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
}
.filtro-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.filtro-label {
  font-size: 0.82rem;
  font-weight: 600;
  color: #6B7280;
  font-family: 'Montserrat', sans-serif;
}
.filter-select {
  padding: 9px 12px;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  font-size: 0.9rem;
  background: #FFFFFF;
  color: #1A1A1A;
  font-family: 'Montserrat', sans-serif;
  cursor: pointer;
  outline: none;
}
.filter-select:focus {
  border-color: #1B396A;
}
.filtros-footer {
  display: flex;
  justify-content: flex-end;
  margin-top: 0.9rem;
  padding-top: 0.9rem;
  border-top: 1px solid #E5E7EB;
}
.btn-limpiar {
  display: flex;
  align-items: center;
  gap: 6px;
  background: transparent;
  color: #6B7280;
  border: none;
  padding: 6px 10px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.88rem;
  font-family: 'Montserrat', sans-serif;
  transition: color 0.15s;
}
.btn-limpiar:hover {
  color: #DC2626;
}
.reset-icon {
  width: 16px;
  height: 16px;
}

.filtros-slide-enter-active, .filtros-slide-leave-active {
  transition: all 0.25s ease;
  overflow: hidden;
}
.filtros-slide-enter-from, .filtros-slide-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* Tabla contenedor */
.tabla-contenedor {
  background: #FFFFFF;
  border-radius: 12px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  overflow: hidden;
  margin-top: 10px;
}

/* Tabla (estilos originales adaptados) */
.data-table {
  width: 100%;
  border-collapse: collapse;
  outline: none;
}
.data-table th {
  background: #F8FAFC;
  padding: 12px 14px;
  text-align: left;
  font-weight: 600;
  font-size: 0.85rem;
  color: #1A1A1A;
  border-bottom: 1px solid #E5E7EB;
  font-family: 'Montserrat', sans-serif;
  white-space: nowrap;
}
.th-centro, .td-centro { text-align: center; }
.th-acciones { text-align: center; }
.data-table td {
  padding: 10px 14px;
  border-bottom: 1px solid #E5E7EB;
  color: #1A1A1A;
  font-size: 0.88rem;
  font-family: 'Montserrat', sans-serif;
}
.data-table tbody tr { transition: background 0.15s; cursor: pointer; }
.data-table tbody tr:hover { background: #F8FAFC; }
.data-table tbody tr:last-child td { border-bottom: none; }
.fila-seleccionada { background: #DBEAFE !important; }

.celda-control { font-weight: 700; color: #1B396A; font-size: 0.88rem; }
.celda-nombre { font-weight: 600; font-size: 0.9rem; }
.celda-secundaria { color: #6B7280; font-size: 0.85rem; }

.estatus-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.78rem;
  font-weight: 600;
  white-space: nowrap;
}
.estatus-badge.activo           { background: #DCFCE7; color: #16A34A; }
.estatus-badge.baja-temporal    { background: #FEF3C7; color: #F59E0B; }
.estatus-badge.baja-definitiva  { background: #FEE2E2; color: #DC2626; }
.estatus-badge.titulado         { background: #EDE9FE; color: #7C3AED; }
.estatus-badge.egresado         { background: #DBEAFE; color: #1B396A; }

.celda-acciones {
  display: flex;
  gap: 5px;
  align-items: center;
  justify-content: center;
}
.btn-icono {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 7px;
  cursor: pointer;
  border: 1px solid transparent;
  transition: background 0.15s, border-color 0.15s;
  flex-shrink: 0;
}
.btn-icono svg { width: 15px; height: 15px; }
.btn-icono.ver { background: #F3F4F6; border-color: #D1D5DB; }
.btn-icono.ver:hover { background: #E5E7EB; }
.btn-icono.ver svg { stroke: #374151; }
.btn-icono.editar { background: #1B396A; border-color: #1B396A; }
.btn-icono.editar:hover { background: #1D4ED8; border-color: #1D4ED8; }
.btn-icono.editar svg { stroke: #FFFFFF; }

/* Estados vacío/cargando */
.estado-vacio, .estado-cargando {
  text-align: center;
  padding: 3rem 2rem;
  color: #6B7280;
}
.icono-vacio {
  width: 52px;
  height: 52px;
  stroke: #9CA3AF;
  margin-bottom: 12px;
}
.estado-vacio h3 {
  font-size: 1.1rem;
  color: #1A1A1A;
  margin: 0 0 6px;
}
.estado-vacio p {
  font-size: 0.9rem;
  margin: 0 0 1rem;
}
.btn-limpiar-vacio {
  background: #FFFFFF;
  color: #1A1A1A;
  border: 1px solid #E5E7EB;
  padding: 8px 18px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
}
.spinner-tabla {
  display: inline-block;
  width: 32px;
  height: 32px;
  border: 3px solid #E5E7EB;
  border-top-color: #1B396A;
  border-radius: 50%;
  animation: girar 0.8s linear infinite;
  margin-bottom: 12px;
}
@keyframes girar { to { transform: rotate(360deg); } }

/* Paginación */
.paginacion {
  margin-top: 1.2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
  color: #6B7280;
  font-family: 'Montserrat', sans-serif;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.paginacion-izquierda, .paginacion-centro, .paginacion-derecha {
  display: flex;
  align-items: center;
  gap: 8px;
}
.select-filas {
  border: 1px solid #E5E7EB;
  border-radius: 6px;
  padding: 4px 8px;
  font-size: 0.9rem;
  background: #FFFFFF;
  font-family: 'Montserrat', sans-serif;
}
.btn-pag {
  padding: 5px 11px;
  border: 1px solid #E5E7EB;
  background: #FFFFFF;
  border-radius: 6px;
  cursor: pointer;
  color: #1A1A1A;
  font-family: 'Montserrat', sans-serif;
  font-size: 0.9rem;
  transition: background 0.15s;
}
.btn-pag:hover:not(:disabled) { background: #F8FAFC; }
.btn-pag:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-pag.activo { background: #1B396A; color: white; border-color: #1B396A; }

.pie-pagina {
  text-align: center;
  color: #9CA3AF;
  font-size: 0.82rem;
  padding-top: 2rem;
  border-top: 1px solid #E5E7EB;
  margin-top: 1rem;
}

/* Barra de carga global */
.barra-carga-global {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background: transparent;
  z-index: 9999;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s ease;
}
.barra-carga-global.visible { opacity: 1; }
.barra-progreso {
  height: 100%;
  background: #1D4ED8;
  width: 30%;
  animation: carga 1.5s infinite ease-in-out;
}
@keyframes carga {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(400%); }
}

/* Notificación UI */
.notificacion-ui {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 12px 20px;
  border-radius: 8px;
  color: white;
  display: flex;
  align-items: center;
  gap: 10px;
  font-family: 'Montserrat', sans-serif;
  font-weight: 500;
  z-index: 1000;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.notificacion-ui.exito { background: #10B981; }
.notificacion-ui.error { background: #EF4444; }
.notif-icono { width: 20px; height: 20px; }
.notif-fade-enter-active, .notif-fade-leave-active { transition: all 0.3s ease; }
.notif-fade-enter-from, .notif-fade-leave-to { opacity: 0; transform: translateY(-10px); }

/* Responsive */
@media (max-width: 768px) {
  .barra-acciones {
    flex-direction: column;
    align-items: stretch;
  }
  .busqueda-grupo {
    max-width: 100%;
  }
  .btn-nuevo {
    justify-content: center;
  }
  .filtros-grid {
    grid-template-columns: 1fr;
  }
  .paginacion {
    flex-direction: column;
    gap: 1rem;
  }
}
</style>

<style>
/* ========== ESTILOS GLOBALES PARA MODALES (sin scoped) ========== */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 1rem;
}
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.22s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.expand-enter-active, .expand-leave-active { transition: all 0.2s ease; }
.expand-enter-from, .expand-leave-to { opacity: 0; transform: translateY(-4px); }

.modal-content {
  background: #FFFFFF;
  width: 540px;
  max-width: 100%;
  max-height: 90vh;
  border-radius: 16px;
  box-shadow: 0 24px 60px rgba(0,0,0,0.2);
  overflow: hidden;
  border: 1px solid #E5E7EB;
  display: flex;
  flex-direction: column;
  font-family: 'Montserrat', sans-serif;
}
.modal-ver-alumno { width: 750px; }
.modal-confirmar { width: 440px; }

.modal-header {
  background: #1B396A;
  color: white;
  padding: 1.2rem 1.6rem;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-shrink: 0;
}
.modal-header-avatar-group { display: flex; gap: 1rem; align-items: center; }
.detalle-avatar { flex-shrink: 0; }
.avatar-img { width: 48px; height: 48px; border-radius: 10px; object-fit: cover; border: 2px solid rgba(255,255,255,0.3); }
.avatar-placeholder { width: 48px; height: 48px; border-radius: 10px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; border: 2px solid rgba(255,255,255,0.3); }
.avatar-placeholder span { color: white; font-weight: 700; font-size: 1.1rem; }

.modal-header-info { display: flex; flex-direction: column; gap: 3px; }
.modal-header-tag { font-size: 0.72rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; opacity: 0.75; }
.modal-header h3 { margin: 0; font-size: 1.2rem; font-weight: 700; }
.sub-header-control { font-size: 0.85rem; font-weight: 500; font-family: monospace; opacity: 0.85; }

.btn-cerrar-modal { background: none; border: none; color: white; font-size: 1.8rem; cursor: pointer; line-height: 1; opacity: 0.8; padding: 0; flex-shrink: 0; transition: opacity 0.15s; }
.btn-cerrar-modal:hover { opacity: 1; }

.modal-body-tabs { display: flex; flex-direction: column; flex: 1; overflow: hidden; }
.detalle-tabs { display: flex; background: #F8FAFC; border-bottom: 1px solid #E5E7EB; flex-shrink: 0; }
.tab-btn { flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 12px 8px; background: none; border: none; border-bottom: 2px solid transparent; cursor: pointer; font-size: 0.85rem; font-weight: 600; color: #6B7280; font-family: 'Montserrat', sans-serif; transition: all 0.15s; }
.tab-btn svg { stroke: currentColor; }
.tab-btn:hover { color: #1B396A; }
.tab-btn.activo { color: #1B396A; border-bottom-color: #1B396A; background: #FFFFFF; }
.tab-contenido-scroll { overflow-y: auto; padding: 1.4rem 1.6rem; flex: 1; }
.tab-panel { display: flex; flex-direction: column; gap: 1rem; }

.detalle-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.9rem; }
.detalle-campo { display: flex; flex-direction: column; gap: 4px; padding: 0.8rem 1rem; background: #F8FAFC; border-radius: 8px; border: 1px solid #E5E7EB; }
.detalle-campo.full-width { grid-column: 1 / -1; }
.detalle-label { font-size: 0.72rem; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.05em; }
.detalle-valor { font-size: 0.95rem; font-weight: 500; color: #1A1A1A; }
.detalle-numero { font-size: 1.15rem; font-weight: 700; color: #1B396A; }
.mono-bold { font-family: 'Courier New', monospace; font-weight: 700; font-size: 1.05rem; color: #1B396A; }

.creditos-bloque { background: #F8FAFC; border-radius: 10px; padding: 12px 14px; border: 1px solid #E5E7EB; }
.creditos-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 0.85rem; }
.creditos-label { font-weight: 700; color: #1A1A1A; }
.creditos-pct { font-weight: 700; color: #6B7280; font-size: 0.82rem; }
.creditos-pct.verde { color: #16A34A; }
.creditos-barra-track { height: 10px; background: #E5E7EB; border-radius: 5px; overflow: hidden; }
.creditos-barra-fill { height: 100%; border-radius: 5px; transition: width 0.8s ease; }
.kardex-semestres { display: flex; flex-direction: column; gap: 0.6rem; }
.semestre-bloque { border: 1px solid #E5E7EB; border-radius: 10px; overflow: hidden; background: white; }
.semestre-btn { width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: none; border: none; cursor: pointer; font-family: 'Montserrat', sans-serif; transition: background 0.15s; }
.semestre-btn:hover { background: #F8FAFC; }
.semestre-btn.abierto { background: #EFF6FF; }
.semestre-titulo { font-size: 0.9rem; font-weight: 700; color: #1A1A1A; }
.semestre-badges { display: flex; align-items: center; gap: 5px; }
.badge-mini { font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 20px; }
.badge-mini.acreditadas { background: #DCFCE7; color: #16A34A; }
.badge-mini.reprobadas  { background: #FEE2E2; color: #DC2626; }
.flecha-semestre { stroke: #6B7280; transition: transform 0.2s; flex-shrink: 0; }
.flecha-semestre.girado { transform: rotate(180deg); }
.semestre-materias { border-top: 1px solid #E5E7EB; }
.tabla-mini { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.tabla-mini th { background: #F8FAFC; padding: 8px 12px; text-align: left; font-size: 0.75rem; font-weight: 700; color: #6B7280; text-transform: uppercase; border-bottom: 1px solid #E5E7EB; }
.tabla-mini td { padding: 8px 12px; border-bottom: 1px solid #F9FAFB; vertical-align: middle; color: #1A1A1A; }
.tabla-mini tr.fila-repr { background: #FEF2F2; }
.tabla-mini td.centrado { text-align: center; }
.clave-mono { font-family: monospace; font-size: 0.8rem; font-weight: 700; color: #1A1A1A; }
.badge-estado-mini { font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 10px; display: inline-block; }
.text-verde { color: #16A34A; }
.text-rojo { color: #DC2626; }
.text-gris { color: #6B7280; }

.horario-lista { display: flex; flex-direction: column; gap: 0.6rem; }
.horario-item { display: flex; align-items: stretch; border: 1px solid #E5E7EB; border-radius: 8px; overflow: hidden; background: white; }
.horario-color { width: 5px; flex-shrink: 0; }
.horario-info { padding: 10px 12px; display: flex; flex-direction: column; gap: 2px; }
.horario-nombre { font-weight: 700; font-size: 0.88rem; color: #1A1A1A; }
.horario-meta { font-size: 0.78rem; color: #6B7280; }
.horario-aula { font-size: 0.75rem; color: #9CA3AF; }

.kardex-cargando { display: flex; flex-direction: column; gap: 8px; }
.skeleton-linea { height: 14px; border-radius: 6px; background: linear-gradient(90deg, #E5E7EB 25%, #F3F4F6 50%, #E5E7EB 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }
.skeleton-linea.largo { width: 70%; }
.skeleton-linea.medio { width: 45%; }
.skeleton-fila { height: 40px; border-radius: 8px; background: linear-gradient(90deg, #F3F4F6 25%, #FFFFFF 50%, #F3F4F6 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

.form-body { padding: 1.5rem 1.6rem; overflow-y: auto; }
.form-grupo { margin-bottom: 1.2rem; }
.form-grupo-doble { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-grupo label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: #1A1A1A; }
.obligatorio { color: #DC2626; }
.modal-input, .modal-select { width: 100%; padding: 10px 14px; border: 1.5px solid #E5E7EB; border-radius: 8px; font-size: 0.95rem; background: #FFFFFF; color: #1A1A1A; font-family: 'Montserrat', sans-serif; outline: none; transition: border-color 0.2s; box-sizing: border-box; }
.modal-input:focus, .modal-select:focus { border-color: #1B396A; box-shadow: 0 0 0 3px #DBEAFE; }
.modal-input.deshabilitado { background: #F5F5F5; cursor: not-allowed; color: #6B7280; }

.modal-footer { padding: 1rem 1.6rem; background: #F8FAFC; display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid #E5E7EB; flex-shrink: 0; }
.btn-secundario { padding: 9px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #FFFFFF; color: #1B396A; border: 2px solid #1B396A; transition: background 0.15s; font-size: 0.9rem; }
.btn-secundario:hover { background: #DBEAFE; }
.btn-secundario:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-eliminar { padding: 9px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #DC2626; color: white; border: 2px solid #DC2626; transition: background 0.15s; font-size: 0.9rem; }
.btn-eliminar:hover { background: #B91C1C; }
.btn-eliminar:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-guardar { display: flex; align-items: center; gap: 8px; padding: 9px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #1B396A; color: white; border: 2px solid #1B396A; transition: background 0.15s; font-size: 0.9rem; }
.btn-guardar:hover:not(:disabled) { background: #1D4ED8; }
.btn-guardar:disabled { opacity: 0.55; cursor: not-allowed; }

.btn-accion-outline { display: flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #FFFFFF; color: #1B396A; border: 2px solid #1B396A; transition: background 0.15s; font-size: 0.9rem; }
.btn-accion-outline:hover { background: #DBEAFE; }

.confirmar-body { display: flex; flex-direction: column; align-items: center; gap: 1rem; text-align: center; padding: 2rem 1.6rem; }
.confirmar-icono { width: 52px; height: 52px; stroke: #F59E0B; }
.confirmar-body p { color: #1A1A1A; font-size: 0.95rem; margin: 0; line-height: 1.5; }

.estatus-badge-modal { display: inline-flex; align-items: center; gap: 5px; padding: 5px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 700; font-family: 'Montserrat', sans-serif; }
.estatus-badge-modal.badge-activo    { background: #DCFCE7; color: #16A34A; }
.estatus-badge-modal.badge-temporal  { background: #FEF3C7; color: #B45309; }
.estatus-badge-modal.badge-definitiva{ background: #FEE2E2; color: #DC2626; }
.estatus-badge-modal.badge-titulado  { background: #EDE9FE; color: #7C3AED; }
.estatus-badge-modal.badge-egresado  { background: #DBEAFE; color: #1B396A; }

@media (max-width: 600px) {
  .detalle-grid { grid-template-columns: 1fr; }
  .form-grupo-doble { grid-template-columns: 1fr; }
  .modal-footer { flex-direction: column; }
  .modal-footer button { width: 100%; justify-content: center; }
}
</style>