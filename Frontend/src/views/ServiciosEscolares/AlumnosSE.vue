<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="alumnos-page">
      <div class="barra-carga-global" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <transition name="notif-fade">
        <div
          v-if="notificacion.visible"
          class="notificacion-ui"
          :class="notificacion.tipo"
          role="status"
          aria-live="polite"
        >
          <svg v-if="notificacion.tipo === 'exito'" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/> <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
          </svg>
          <svg v-else class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/> <line x1="12" y1="8" x2="12" y2="12"/> <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <nav class="breadcrumb" aria-label="Ruta de navegación">
        <span class="breadcrumb-link" @click="$router.push('/gestion-academica')">GESTIÓN ACADÉMICA</span>
        <svg class="breadcrumb-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <span
          class="breadcrumb-link"
          :class="{ 'breadcrumb-actual': !alumnoSeleccionado }"
          @click="alumnoSeleccionado ? cerrarModalVer() : null"
        >ALUMNOS</span>
        <template v-if="alumnoSeleccionado">
          <svg class="breadcrumb-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
          </svg>
          <span class="breadcrumb-actual">
            EXPEDIENTE: {{ alumnoSeleccionado.numero_control || alumnoSeleccionado.noControl }}
          </span>
        </template>
      </nav>

      <div class="page-header">
        <div class="page-header-left">
          <h1 class="page-title">GESTIÓN DE ALUMNOS</h1>
          <p class="page-subtitle">
            {{ alumnosFiltrados.length }} REGISTRO{{ alumnosFiltrados.length !== 1 ? 'S' : '' }}
            ENCONTRADO{{ alumnosFiltrados.length !== 1 ? 'S' : '' }}
            <template v-if="selectedCount > 0">
              ·  <span class="subtitle-sel">{{ selectedCount }} SELECCIONADO{{ selectedCount !== 1 ? 'S' : '' }}</span>
            </template>
          </p>
        </div>
        <div class="page-header-btns">
          <button
            class="btn-select-all"
            :class="{ activo: selectedCount > 0 }"
            @click="toggleSelectAll"
            :title="selectedCount === paginatedAlumnos.length ? 'DESELECCIONAR TODOS' : 'SELECCIONAR PÁGINA'"
          >
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" stroke-width="2">
              <template v-if="selectedCount === paginatedAlumnos.length && paginatedAlumnos.length > 0">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
              </template>
              <template v-else>
                <rect x="3" y="3" width="18" height="18" rx="2"/>
              </template>
            </svg>
            {{ selectedCount > 0 ? `${selectedCount} SEL.` : 'SELECCIONAR' }}
          </button>
          <button class="btn-nuevo" @click="nuevoAlumno">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="17" height="17" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <line x1="19" y1="8" x2="19" y2="14"/> <line x1="22" y1="11" x2="16" y2="11"/>
            </svg>
            NUEVA INSCRIPCIÓN
          </button>
        </div>
      </div>

      <div class="kpis-grid">
        <div class="kpi-card kpi-total">
          <div class="kpi-icon-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M23 21v-2a4 4 0 0 0-3-3.87"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
          <div class="kpi-data">
            <span class="kpi-numero">{{ kpiTotal }}</span>
            <span class="kpi-label">TOTAL ALUMNOS</span>
          </div>
        </div>
        <div class="kpi-card kpi-activo">
          <div class="kpi-icon-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
            </svg>
          </div>
          <div class="kpi-data">
            <span class="kpi-numero">{{ kpiActivos }}</span>
            <span class="kpi-label">ACTIVOS</span>
          </div>
        </div>
        <div class="kpi-card kpi-temporal">
          <div class="kpi-icon-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <polyline stroke-linecap="round" stroke-linejoin="round" points="12 6 12 12 16 14"/>
            </svg>
          </div>
          <div class="kpi-data">
            <span class="kpi-numero">{{ kpiTemporales }}</span>
            <span class="kpi-label">BAJAS TEMPORALES</span>
          </div>
        </div>
        <div class="kpi-card kpi-definitiva">
          <div class="kpi-icon-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="15" y1="9" x2="9" y2="15"/> <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
          </div>
          <div class="kpi-data">
            <span class="kpi-numero">{{ kpiDefinitivas }}</span>
            <span class="kpi-label">BAJAS DEFINITIVAS</span>
          </div>
        </div>
        <div class="kpi-card kpi-egresado">
          <div class="kpi-icon-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M22 10v6M2 10l10-5 10 5-10 5z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 12v5c3 3 9 3 12 0v-5"/>
            </svg>
          </div>
          <div class="kpi-data">
            <span class="kpi-numero">{{ kpiEgresados }}</span>
            <span class="kpi-label">EGRESADOS</span>
          </div>
        </div>
      </div>

      <div class="barra-acciones">
        <div class="busqueda-grupo">
          <div class="input-con-icono">
            <svg class="icono-input" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18" stroke-width="2">
              <circle cx="11" cy="11" r="8"/> <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input
              type="text"
              ref="inputBusqueda"
              v-model="busquedaRaw"
              class="input-busqueda"
              placeholder="BUSCAR POR NO. CONTROL O NOMBRE..."
              @keydown.escape="limpiarBusqueda"
              autocomplete="off"
              spellcheck="false"
            />
            <button v-if="busquedaRaw" class="btn-limpiar-input" @click="limpiarBusqueda" title="LIMPIAR BÚSQUEDA">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" stroke-width="2.5">
                <line x1="18" y1="6" x2="6" y2="18"/> <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
        </div>

        <button
          class="btn-filtro"
          @click="mostrarFiltros = !mostrarFiltros"
          :class="{ activo: filtrosActivos, abierto: mostrarFiltros }"
          :aria-expanded="mostrarFiltros"
        >
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" stroke-width="2">
            <line x1="21" y1="4" x2="14" y2="4"/> <line x1="10" y1="4" x2="3" y2="4"/>
            <line x1="21" y1="12" x2="12" y2="12"/> <line x1="8" y1="12" x2="3" y2="12"/>
            <line x1="21" y1="20" x2="16" y2="20"/> <line x1="12" y1="20" x2="3" y2="20"/>
            <line x1="14" y1="2" x2="14" y2="6"/> <line x1="8" y1="10" x2="8" y2="14"/> <line x1="16" y1="18" x2="16" y2="22"/>
          </svg>
          FILTROS
          <span v-if="filtrosActivos" class="filtros-badge">{{ contadorFiltros }}</span>
        </button>
      </div>

      <transition name="filtros-slide">
        <div v-if="mostrarFiltros" class="filtros-panel">
          <div class="filtros-grid">
            <div class="filtro-item">
              <label class="filtro-label">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="13" height="13" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                </svg>
                CARRERA
              </label>
              <select v-model="filtroCarreraId" class="filter-select" @change="onFiltroChange">
                <option value="">TODAS LAS CARRERAS</option>
                <option v-for="c in catalogos.carreras" :key="c.id_carrera" :value="c.id_carrera">
                  {{ c.nombre?.toUpperCase() }}
                </option>
              </select>
            </div>
            <div class="filtro-item">
              <label class="filtro-label">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="13" height="13" stroke-width="2">
                  <polygon stroke-linecap="round" stroke-linejoin="round" points="12 2 2 7 12 12 22 7 12 2"/>
                  <polyline stroke-linecap="round" stroke-linejoin="round" points="2 17 12 22 22 17"/>
                  <polyline stroke-linecap="round" stroke-linejoin="round" points="2 12 12 17 22 12"/>
                </svg>
                SEMESTRE
              </label>
              <select v-model="filtroSemestre" class="filter-select" @change="onFiltroChange">
                <option value="">TODOS</option>
                <option v-for="n in 8" :key="n" :value="String(n)">{{ n }}° SEMESTRE</option>
              </select>
            </div>
            <div class="filtro-item">
              <label class="filtro-label">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="13" height="13" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                  <line x1="7" y1="7" x2="7.01" y2="7"/>
                </svg>
                ESTATUS
              </label>
              <select v-model="filtroEstatusId" class="filter-select" @change="onFiltroChange">
                <option value="">TODOS</option>
                <option v-for="e in catalogos.estatus_alumno" :key="e.id_estatus_alumno" :value="e.id_estatus_alumno">
                  {{ e.nombre?.toUpperCase() }}
                </option>
              </select>
            </div>
          </div>
          <div class="filtros-footer">
            <button class="btn-limpiar-filtros" @click="resetFilters">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" stroke-width="2">
                <polyline stroke-linecap="round" stroke-linejoin="round" points="1 4 1 10 7 10"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.51 15a9 9 0 1 0 .49-3.5"/>
              </svg>
              LIMPIAR FILTROS
            </button>
          </div>
        </div>
      </transition>

      <div v-if="cargando && alumnos.length === 0" class="estado-cargando">
        <div class="spinner-cards"></div>
        <p>CARGANDO REGISTROS...</p>
      </div>

      <div v-else-if="!cargando && alumnosFiltrados.length === 0" class="estado-vacio">
        <svg class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.3">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <line x1="17" y1="11" x2="23" y2="17"/> <line x1="23" y1="11" x2="17" y2="17"/>
        </svg>
        <h3>SIN RESULTADOS</h3>
        <p>NO SE ENCONTRARON ALUMNOS CON LOS CRITERIOS APLICADOS.</p>
        <button class="btn-limpiar-vacio" @click="resetFilters">LIMPIAR FILTROS</button>
      </div>

      <div
        v-else
        class="tabla-wrapper"
        ref="gridRef"
        @keydown="selectedCount === 0 ? navegarTeclado($event) : null"
        tabindex="0"
        role="list"
        aria-label="Lista de alumnos"
      >
        <table class="tabla-alumnos">
          <thead>
            <tr>
              <th class="col-check">
                <button
                  class="btn-select-all-table"
                  :class="{ activo: selectedCount > 0 }"
                  @click.stop="toggleSelectAll"
                  :title="selectedCount === paginatedAlumnos.length ? 'DESELECCIONAR TODOS' : 'SELECCIONAR PÁGINA'"
                  aria-label="Seleccionar todos"
                >
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" stroke-width="2">
                    <template v-if="selectedCount === paginatedAlumnos.length && paginatedAlumnos.length > 0">
                      <rect x="3" y="3" width="18" height="18" rx="2"/>
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
                    </template>
                    <template v-else>
                      <rect x="3" y="3" width="18" height="18" rx="2"/>
                    </template>
                  </svg>
                </button>
              </th>
              <th class="col-control">NO. CONTROL</th>
              <th class="col-nombre">NOMBRE COMPLETO</th>
              <th class="col-carrera">CARRERA</th>
              <th class="col-semestre">SEM.</th>
              <th class="col-estatus">ESTATUS</th>
              <th class="col-acciones">ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(alumno, index) in paginatedAlumnos"
              :key="alumno.id_alumno || alumno.id"
              v-memo="[alumno.id_alumno || alumno.id, filaActiva === index, seleccionados.has(alumno.id_alumno || alumno.id)]"
              class="fila-alumno"
              :class="{
                'fila-activa': filaActiva === index && selectedCount === 0,
                'fila-seleccionada': seleccionados.has(alumno.id_alumno || alumno.id)
              }"
              role="listitem"
              tabindex="-1"
              @click="handleRowClick(alumno, index, $event)"
              @keydown.enter.prevent="abrirModalVer(alumno)"
            >
              <td class="col-check">
                <div
                  class="bulk-checkbox"
                  :class="{ checked: seleccionados.has(alumno.id_alumno || alumno.id) }"
                  @click.stop="toggleSeleccion(alumno)"
                  role="checkbox"
                  :aria-checked="seleccionados.has(alumno.id_alumno || alumno.id)"
                  :aria-label="`SELECCIONAR ${resolverNombre(alumno).toUpperCase()}`"
                >
                  <svg v-if="seleccionados.has(alumno.id_alumno || alumno.id)" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="12" height="12" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                  </svg>
                </div>
              </td>
              <td class="col-control">{{ alumno.numero_control || alumno.noControl }}</td>
              <td class="col-nombre">{{ resolverNombre(alumno).toUpperCase() }}</td>
              <td class="col-carrera">{{ resolverCarrera(alumno).toUpperCase() }}</td>
              <td class="col-semestre">{{ alumno.semestre_actual || alumno.semestre }}°</td>
              <td class="col-estatus">
                <span class="estatus-badge" :class="`badge-${slugEstatus(alumno.estatus)}`">
                  {{ (alumno.estatus || 'N/D').toUpperCase() }}
                </span>
              </td>
              <td class="col-acciones">
                <button class="btn-fila-accion btn-ver" @click.stop="abrirModalVer(alumno)" title="VER EXPEDIENTE">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                </button>
                <button class="btn-fila-accion btn-editar" @click.stop="abrirModalEditar(alumno)" title="EDITAR">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                </button>
                <button class="btn-fila-accion btn-kardex" @click.stop="verKardex(alumno)" title="VER KARDEX">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline stroke-linecap="round" stroke-linejoin="round" points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/> <line x1="16" y1="17" x2="8" y2="17"/>
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="paginacion" v-if="alumnosFiltrados.length > 0">
        <div class="paginacion-izquierda">
          <span class="pag-label">REGISTROS POR PÁGINA:</span>
          <select v-model="filasPorPagina" @change="currentPage = 1" class="select-filas">
            <option :value="12">12</option>
            <option :value="24">24</option>
            <option :value="48">48</option>
          </select>
        </div>
        <div class="paginacion-centro">
          PÁGINA <strong>{{ currentPage }}</strong> DE <strong>{{ totalPages }}</strong>
        </div>
        <div class="paginacion-derecha">
          <button class="btn-pag" @click="prevPage" :disabled="currentPage === 1 || selectedCount > 0" aria-label="Página anterior">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" stroke-width="2">
              <polyline stroke-linecap="round" stroke-linejoin="round" points="15 18 9 12 15 6"/>
            </svg>
          </button>
          <button
            v-for="page in visiblePages" :key="page"
            class="btn-pag" :class="{ activo: page === currentPage }"
            @click="goToPage(page)"
            :disabled="selectedCount > 0"
          >{{ page }}</button>
          <button class="btn-pag" @click="nextPage" :disabled="currentPage === totalPages || selectedCount > 0" aria-label="Página siguiente">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" stroke-width="2">
              <polyline stroke-linecap="round" stroke-linejoin="round" points="9 18 15 12 9 6"/>
            </svg>
          </button>
        </div>
      </div>

      <footer class="pie-pagina">© 2026 TECNOLÓGICO NACIONAL DE MÉXICO | TODOS LOS DERECHOS RESERVADOS</footer>
    </div>

    <Teleport to="body">
      <transition name="bulk-slide">
        <div
          v-if="selectedCount > 0"
          class="bulk-bar"
          role="toolbar"
          aria-label="Acciones masivas"
        >
          <div class="bulk-bar-inner">
            <div class="bulk-bar-left">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
              </svg>
              <span class="bulk-count">{{ selectedCount }} SELECCIONADO{{ selectedCount !== 1 ? 'S' : '' }}</span>
            </div>
            <div class="bulk-bar-center">
              <select v-model="bulkAccion" class="bulk-select">
                <option value="">ELEGIR ACCIÓN...</option>
                <option value="estatus">CAMBIAR ESTATUS</option>
                <option value="carrera">ASIGNAR CARRERA</option>
                <option value="exportar">EXPORTAR CSV</option>
                <option value="eliminar">ELIMINAR SELECCIONADOS</option>
              </select>
              <button
                class="bulk-btn-aplicar"
                @click="aplicarBulkAccion"
                :disabled="!bulkAccion || ejecutandoBulk"
              >
                <span v-if="ejecutandoBulk" class="spinner-btn"></span>
                {{ ejecutandoBulk ? 'PROCESANDO...' : 'APLICAR' }}
              </button>
            </div>
            <div class="bulk-bar-right">
              <button class="bulk-btn-cancelar" @click="limpiarSeleccion">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/> <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                CANCELAR SELECCIÓN
              </button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <Teleport to="body">
      <transition name="modal-fade">
        <div v-if="showViewModal && alumnoSeleccionado" class="modal-overlay" @click.self="cerrarModalVer" role="dialog" aria-modal="true">
          <div class="modal-content modal-ver-alumno">
            <div class="modal-header">
              <div class="modal-header-avatar-group">
                <div class="detalle-avatar">
                  <img v-if="alumnoSeleccionado.foto" :src="alumnoSeleccionado.foto" class="avatar-img" :alt="resolverNombre(alumnoSeleccionado)"/>
                  <div v-else class="avatar-placeholder" :class="`avatar-${colorIndex(resolverNombre(alumnoSeleccionado))}`">
                    <span>{{ iniciales(resolverNombre(alumnoSeleccionado)) }}</span>
                  </div>
                </div>
                <div class="modal-header-info">
                  <span class="modal-header-tag">EXPEDIENTE ACADÉMICO v2.2</span>
                  <h3>{{ resolverNombre(alumnoSeleccionado).toUpperCase() }}</h3>
                  <span class="sub-header-control">{{ alumnoSeleccionado.numero_control || alumnoSeleccionado.noControl }}</span>
                </div>
              </div>
              <button @click="cerrarModalVer" class="btn-cerrar-modal" title="CERRAR" aria-label="Cerrar expediente">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/> <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>
            <div class="modal-body-tabs">
              <div class="detalle-tabs" role="tablist">
                <button v-for="tab in tabs" :key="tab.id"
                  class="tab-btn" :class="{ activo: tabActivo === tab.id }"
                  @click="tabActivo = tab.id"
                  role="tab" :aria-selected="tabActivo === tab.id"
                >
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="tab.icon"/>
                  </svg>
                  {{ tab.label }}
                </button>
              </div>
              <div class="tab-contenido-scroll">
                <div v-if="tabActivo === 'general'" class="tab-panel">
                  <div class="exp-seccion">
                    <div class="exp-seccion-titulo">
                      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                      </svg>
                      DATOS PERSONALES
                    </div>
                    <div class="detalle-grid">
                      <div class="detalle-campo">
                        <span class="detalle-label">NO. DE CONTROL</span>
                        <span class="detalle-valor mono-bold">{{ alumnoSeleccionado.numero_control || alumnoSeleccionado.noControl || '—' }}</span>
                      </div>
                      <div class="detalle-campo">
                        <span class="detalle-label">CURP</span>
                        <span class="detalle-valor mono-bold">{{ alumnoSeleccionado.curp || alumnoSeleccionado.persona?.curp || 'PENDIENTE DE REGISTRO' }}</span>
                      </div>
                      <div class="detalle-campo full-width">
                        <span class="detalle-label">NOMBRE COMPLETO</span>
                        <span class="detalle-valor">{{ resolverNombre(alumnoSeleccionado).toUpperCase() || '—' }}</span>
                      </div>
                      <div class="detalle-campo">
                        <span class="detalle-label">FECHA DE NACIMIENTO</span>
                        <span class="detalle-valor">{{ formatearFecha(alumnoSeleccionado.fecha_nacimiento || alumnoSeleccionado.persona?.fecha_nacimiento) || '—' }}</span>
                      </div>
                      <div class="detalle-campo">
                        <span class="detalle-label">GÉNERO</span>
                        <span class="detalle-valor">{{ (alumnoSeleccionado.genero || alumnoSeleccionado.persona?.genero || '—').toUpperCase() }}</span>
                      </div>
                      <div class="detalle-campo full-width">
                        <span class="detalle-label">CORREO INSTITUCIONAL</span>
                        <span class="detalle-valor">{{ alumnoSeleccionado.email || alumnoSeleccionado.persona?.email || '—' }}</span>
                      </div>
                    </div>
                  </div>
                  <div class="exp-seccion">
                    <div class="exp-seccion-titulo">
                      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12v5c3 3 9 3 12 0v-5"/>
                      </svg>
                      INFORMACIÓN ACADÉMICA
                    </div>
                    <div class="detalle-grid">
                      <div class="detalle-campo full-width">
                        <span class="detalle-label">CARRERA</span>
                        <span class="detalle-valor">{{ resolverCarrera(alumnoSeleccionado).toUpperCase() || '—' }}</span>
                      </div>
                      <div class="detalle-campo">
                        <span class="detalle-label">ESPECIALIDAD</span>
                        <span class="detalle-valor">{{ (alumnoSeleccionado.especialidad || '—').toUpperCase() }}</span>
                      </div>
                      <div class="detalle-campo">
                        <span class="detalle-label">SEMESTRE ACTUAL</span>
                        <span class="detalle-valor detalle-numero">{{ alumnoSeleccionado.semestre_actual || alumnoSeleccionado.semestre || '—' }}°</span>
                      </div>
                      <div class="detalle-campo">
                        <span class="detalle-label">ESTATUS ACADÉMICO</span>
                        <span class="estatus-badge-modal" :class="`badge-${slugEstatus(alumnoSeleccionado.estatus)}`">
                          {{ (alumnoSeleccionado.estatus || '—').toUpperCase() }}
                        </span>
                      </div>
                      <div class="detalle-campo">
                        <span class="detalle-label">FECHA DE INGRESO</span>
                        <span class="detalle-valor">{{ formatearFecha(alumnoSeleccionado.fecha_ingreso) || '—' }}</span>
                      </div>
                    </div>
                  </div>
                  <div class="exp-seccion">
                    <div class="exp-seccion-titulo">
                      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                      </svg>
                      BENEFICIOS Y CONTACTO DE EMERGENCIA
                    </div>
                    <div class="detalle-grid">
                      <div class="detalle-campo">
                        <span class="detalle-label">SEGURO MÉDICO</span>
                        <span class="detalle-valor">{{ (alumnoSeleccionado.seguro_medico || '—').toUpperCase() }}</span>
                      </div>
                      <div class="detalle-campo">
                        <span class="detalle-label">SUBES / BECA</span>
                        <span class="detalle-valor">{{ (alumnoSeleccionado.subes || alumnoSeleccionado.beca || '—').toUpperCase() }}</span>
                      </div>
                      <div class="detalle-campo full-width exp-contacto-titulo">
                        <span class="detalle-label">CONTACTO DE EMERGENCIA</span>
                      </div>
                      <div class="detalle-campo">
                        <span class="detalle-label">NOMBRE</span>
                        <span class="detalle-valor">{{ (alumnoSeleccionado.contacto_emergencia?.nombre || '—').toUpperCase() }}</span>
                      </div>
                      <div class="detalle-campo">
                        <span class="detalle-label">RELACIÓN</span>
                        <span class="detalle-valor">{{ (alumnoSeleccionado.contacto_emergencia?.relacion || '—').toUpperCase() }}</span>
                      </div>
                      <div class="detalle-campo">
                        <span class="detalle-label">TELÉFONO</span>
                        <span class="detalle-valor mono-bold">{{ alumnoSeleccionado.contacto_emergencia?.telefono || '—' }}</span>
                      </div>
                      <div class="detalle-campo">
                        <span class="detalle-label">CORREO</span>
                        <span class="detalle-valor">{{ alumnoSeleccionado.contacto_emergencia?.email || '—' }}</span>
                      </div>
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
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20" stroke-width="2">
                      <circle cx="12" cy="12" r="10"/> <line x1="12" y1="8" x2="12" y2="12"/> <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span>NO SE PUDO CARGAR EL KARDEX.</span>
                    <button @click="cargarKardexAlumno(alumnoSeleccionado)">REINTENTAR</button>
                  </div>
                  <div v-else-if="kardexData">
                    <div class="creditos-bloque" v-if="kardexData.alumno?.creditos_totales">
                      <div class="creditos-row">
                        <span class="creditos-label">AVANCE DE CRÉDITOS</span>
                        <span class="creditos-pct" :class="{ verde: porcentajeCreditos >= 80 }">
                          {{ kardexData.alumno.creditos_acumulados }}/{{ kardexData.alumno.creditos_totales }} ({{ porcentajeCreditos }}%)
                        </span>
                      </div>
                      <div class="creditos-barra-track">
                        <div class="creditos-barra-fill" :style="{ width: porcentajeCreditos + '%', background: porcentajeCreditos >= 80 ? '#16A34A' : '#1B396A' }"></div>
                      </div>
                    </div>
                    <div v-if="kardexData.kardex?.semestres?.length" class="kardex-semestres">
                      <div v-for="semestre in kardexData.kardex.semestres" :key="semestre.numero" class="semestre-bloque">
                        <button class="semestre-btn" @click="toggleSemestre(semestre.numero)" :class="{ abierto: semestresAbiertos.includes(semestre.numero) }">
                          <span class="semestre-titulo">SEMESTRE {{ semestre.numero }}</span>
                          <div class="semestre-badges">
                            <span class="badge-mini acreditadas">{{ semestre.acreditadas }} ACRED.</span>
                            <span v-if="semestre.reprobadas > 0" class="badge-mini reprobadas">{{ semestre.reprobadas }} REPR.</span>
                            <svg class="flecha-semestre" :class="{ girado: semestresAbiertos.includes(semestre.numero) }" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" stroke-width="2">
                              <polyline stroke-linecap="round" stroke-linejoin="round" points="6 9 12 15 18 9"/>
                            </svg>
                          </div>
                        </button>
                        <transition name="expand">
                          <div v-if="semestresAbiertos.includes(semestre.numero)" class="semestre-materias">
                            <table class="tabla-mini">
                              <thead> <tr> <th>CLAVE</th> <th>MATERIA</th> <th>CAL.</th> <th>ESTADO</th> </tr> </thead>
                              <tbody>
                                <tr v-for="m in semestre.materias" :key="m.clave" :class="{ 'fila-repr': m.estado === 'reprobada' }">
                                  <td class="clave-mono">{{ m.clave }}</td>
                                  <td>{{ m.nombre }}</td>
                                  <td class="centrado">
                                    <strong v-if="m.calificacion !== null" :class="{ 'text-verde': m.estado === 'acreditada', 'text-rojo': m.estado === 'reprobada' }">{{ m.calificacion }}</strong>
                                    <span v-else class="text-gris">—</span>
                                  </td>
                                  <td> <span class="badge-estado-mini" :style="estiloEstado(m.estado)">{{ etiquetaEstado(m.estado) }}</span> </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </transition>
                      </div>
                    </div>
                    <div v-else class="kardex-vacio"> <p>NO HAY MATERIAS REGISTRADAS EN EL KARDEX.</p> </div>
                  </div>
                  <div v-else class="kardex-vacio"> <p>KARDEX NO DISPONIBLE.</p> </div>
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
                        <span class="horario-aula">AULA: {{ materia.aula || 'N/D' }}</span>
                      </div>
                    </div>
                  </div>
                  <div v-else class="kardex-vacio">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="32" height="32" style="stroke:#9CA3AF; margin-bottom:8px" stroke-width="1.5">
                      <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                      <line x1="16" y1="2" x2="16" y2="6"/> <line x1="8" y1="2" x2="8" y2="6"/>
                      <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <p>NO HAY HORARIO REGISTRADO PARA ESTE ALUMNO.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn-secundario" @click="cerrarModalVer">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15" stroke-width="2">
                  <line x1="19" y1="12" x2="5" y2="12"/>
                  <polyline stroke-linecap="round" stroke-linejoin="round" points="12 19 5 12 12 5"/>
                </svg>
                VOLVER A LA LISTA
              </button>
              <button class="btn-accion-outline" @click="verKardex(alumnoSeleccionado); cerrarModalVer()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline stroke-linecap="round" stroke-linejoin="round" points="14 2 14 8 20 8"/>
                  <line x1="16" y1="13" x2="8" y2="13"/> <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                KARDEX COMPLETO
              </button>
              <button class="btn-guardar" @click="abrirModalEditar(alumnoSeleccionado); cerrarModalVer()">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                EDITAR
              </button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <Teleport to="body">
      <transition name="modal-fade">
        <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal" role="dialog" aria-modal="true">
          <div class="modal-content">
            <div class="modal-header">
              <div class="modal-header-info">
                <span class="modal-header-tag">{{ alumnoEditar.id_alumno ? 'EDICIÓN' : 'NUEVO' }}</span>
                <h3>{{ alumnoEditar.id_alumno ? 'EDITAR ALUMNO' : 'NUEVO ALUMNO' }}</h3>
              </div>
              <button @click="cerrarModal" class="btn-cerrar-modal" title="CERRAR">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/> <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>
            <div class="modal-body form-body">
              <div class="form-grupo" v-if="alumnoEditar.id_alumno">
                <label>NÚMERO DE CONTROL</label>
                <input v-model="alumnoEditar.noControl" type="text" readonly class="modal-input deshabilitado"/>
              </div>
              <div class="form-grupo">
                <label>NOMBRE COMPLETO <span class="obligatorio">*</span></label>
                <input v-model="alumnoEditar.nombre" type="text" class="modal-input" placeholder="NOMBRE COMPLETO" style="text-transform:uppercase"/>
              </div>
              <div class="form-grupo">
                <label>CARRERA <span class="obligatorio">*</span></label>
                <select v-model="alumnoEditar.id_carrera" class="modal-select">
                  <option value="">SELECCIONAR CARRERA</option>
                  <option v-for="c in catalogos.carreras" :key="c.id_carrera" :value="c.id_carrera">{{ c.nombre?.toUpperCase() }}</option>
                </select>
              </div>
              <div class="form-grupo-doble">
                <div class="form-grupo">
                  <label>SEMESTRE</label>
                  <select v-model="alumnoEditar.semestre" class="modal-select">
                    <option v-for="n in 8" :key="n" :value="n">{{ n }}° SEMESTRE</option>
                  </select>
                </div>
                <div class="form-grupo">
                  <label>ESTATUS</label>
                  <select v-model="alumnoEditar.id_estatus_alumno" class="modal-select">
                    <option value="">SELECCIONAR ESTATUS</option>
                    <option v-for="e in catalogos.estatus_alumno" :key="e.id_estatus_alumno" :value="e.id_estatus_alumno">{{ e.nombre?.toUpperCase() }}</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">CANCELAR</button>
              <button v-if="alumnoEditar.id_alumno" class="btn-eliminar" @click="solicitarEliminar" :disabled="guardando">ELIMINAR</button>
              <button class="btn-guardar" @click="guardarCambios" :disabled="guardando">
                <span v-if="guardando" class="spinner-btn"></span>
                {{ guardando ? 'GUARDANDO...' : 'GUARDAR CAMBIOS' }}
              </button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <Teleport to="body">
      <transition name="modal-fade">
        <div v-if="showModalEliminar" class="modal-overlay" @click.self="showModalEliminar = false" role="alertdialog" aria-modal="true">
          <div class="modal-content modal-confirmar">
            <div class="modal-header">
              <div class="modal-header-info">
                <span class="modal-header-tag">CONFIRMAR</span>
                <h3>ELIMINAR ALUMNO</h3>
              </div>
              <button @click="showModalEliminar = false" class="btn-cerrar-modal">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/> <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>
            <div class="modal-body confirmar-body">
              <svg class="confirmar-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/> <line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
              <p>¿ESTÁS SEGURO DE ELIMINAR A <strong>{{ alumnoEditar.nombre?.toUpperCase() }}</strong>? <br>ESTA ACCIÓN NO SE PUEDE DESHACER.</p>
            </div>
            <div class="modal-footer">
              <button class="btn-secundario" @click="showModalEliminar = false">CANCELAR</button>
              <button class="btn-eliminar" @click="confirmarEliminar" :disabled="guardando">
                <span v-if="guardando" class="spinner-btn"></span>
                {{ guardando ? 'ELIMINANDO...' : 'ELIMINAR' }}
              </button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <Teleport to="body">
      <transition name="modal-fade">
        <div v-if="showBulkConfirm" class="modal-overlay" @click.self="showBulkConfirm = false" role="alertdialog" aria-modal="true">
          <div class="modal-content modal-confirmar">
            <div class="modal-header">
              <div class="modal-header-info">
                <span class="modal-header-tag">ACCIÓN MASIVA</span>
                <h3>{{ labelBulkAccion }}</h3>
              </div>
              <button @click="showBulkConfirm = false" class="btn-cerrar-modal">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/> <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>
            <div class="modal-body confirmar-body">
              <svg v-if="bulkAccion === 'eliminar'" class="confirmar-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/> <line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
              <svg v-else class="confirmar-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="stroke:#1B396A">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
              </svg>
              <p>SE APLICARÁ LA ACCIÓN <strong>{{ labelBulkAccion }}</strong> A <strong>{{ selectedCount }}</strong> ALUMNO{{ selectedCount !== 1 ? 'S' : '' }}.</p>
              <div class="bulk-preview">
                <span v-for="id in [...seleccionados].slice(0, 8)" :key="id" class="bulk-id-chip">{{ id }}</span>
                <span v-if="selectedCount > 8" class="bulk-id-chip bulk-id-mas">+{{ selectedCount - 8 }} MÁS</span>
              </div>
              <div v-if="bulkAccion === 'estatus'" class="bulk-sub-opcion">
                <label class="filtro-label">NUEVO ESTATUS</label>
                <select v-model="bulkValor" class="filter-select">
                  <option value="">SELECCIONAR...</option>
                  <option v-for="e in catalogos.estatus_alumno" :key="e.id_estatus_alumno" :value="e.id_estatus_alumno">{{ e.nombre?.toUpperCase() }}</option>
                </select>
              </div>
              <div v-if="bulkAccion === 'carrera'" class="bulk-sub-opcion">
                <label class="filtro-label">NUEVA CARRERA</label>
                <select v-model="bulkValor" class="filter-select">
                  <option value="">SELECCIONAR...</option>
                  <option v-for="c in catalogos.carreras" :key="c.id_carrera" :value="c.id_carrera">{{ c.nombre?.toUpperCase() }}</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn-secundario" @click="showBulkConfirm = false">CANCELAR</button>
              <button
                :class="bulkAccion === 'eliminar' ? 'btn-eliminar' : 'btn-guardar'"
                @click="confirmarBulkAccion"
                :disabled="ejecutandoBulk || ((bulkAccion === 'estatus' || bulkAccion === 'carrera') && !bulkValor)"
              >
                <span v-if="ejecutandoBulk" class="spinner-btn"></span>
                {{ ejecutandoBulk ? 'PROCESANDO...' : 'CONFIRMAR' }}
              </button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>
  </MainLayout>
</template>

<script setup>
/**
* AlumnosView.vue — Expediente Académico v2.2
* Sprint 1 — Rediseño Front-End SICE
*
* Cambios v2.2 vs v1:
* - Datos Generales expandidos (3 secciones: personal / académico / beneficios+contacto)
* - Debounce (300ms) en búsqueda para evitar recomputación excesiva
* - Filtros sincronizados con URLSearchParams (?carrera=&semestre=&estatus=&q=)
* - Acciones masivas (bulk): checkbox por fila, barra flotante, modal de confirmación
* - v-memo en filas de tabla para evitar re-renders innecesarios al seleccionar
* - Breadcrumb dinámico: ALUMNOS → EXPEDIENTE: [NO_CONTROL]
* - Botón "← VOLVER A LA LISTA" en el modal preserva scroll/filtros/página
*/
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useRouter, useRoute }                       from 'vue-router'
import MainLayout                                    from '@/layouts/MainLayout.vue'

const router  = useRouter()
const route   = useRoute()
const API_URL = import.meta.env.VITE_API_URL

const props = defineProps({
  busquedaGlobal: { type: String, default: '' }
})

const alumnos   = ref([])
const cargando  = ref(false)
const guardando = ref(false)
const gridRef   = ref(null)
const inputBusqueda = ref(null)
const filaActiva    = ref(-1)

const showViewModal      = ref(false)
const showModal          = ref(false)
const showModalEliminar  = ref(false)
const alumnoSeleccionado = ref(null)
const alumnoEditar       = ref({})

const tabActivo         = ref('general')
const kardexData        = ref(null)
const cargandoKardex    = ref(false)
const kardexError       = ref(false)
const horarioData       = ref(null)
const cargandoHorario   = ref(false)
const semestresAbiertos = ref([])

const tabs = [
  { id: 'general', label: 'DATOS',   icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
  { id: 'kardex',  label: 'KARDEX',  icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.58 6a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z' },
  { id: 'horario', label: 'HORARIO', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2  2v12a2 2 0 002 2z' },
]

const catalogos = ref({ carreras: [], estatus_alumno: [] })

const busquedaRaw    = ref('')
const busquedaAlumno = ref('')
let debounceTimer = null

watch(busquedaRaw, (val) => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    busquedaAlumno.value = val
    currentPage.value = 1
    sincronizarURL()
  }, 300)
})

const mostrarFiltros  = ref(false)
const filtroCarreraId = ref('')
const filtroSemestre  = ref('')
const filtroEstatusId = ref('')
const filasPorPagina  = ref(12)
const currentPage     = ref(1)

const sincronizarURL = () => {
  const params = new URLSearchParams()
  if (busquedaAlumno.value)  params.set('q',        busquedaAlumno.value)
  if (filtroCarreraId.value) params.set('carrera',  String(filtroCarreraId.value))
  if (filtroSemestre.value)  params.set('semestre', filtroSemestre.value)
  if (filtroEstatusId.value) params.set('estatus',  String(filtroEstatusId.value))
  router.replace({ query: Object.fromEntries(params) })
}

const onFiltroChange = () => {
  currentPage.value = 1
  sincronizarURL()
}

const restaurarFiltrosDesdeURL = () => {
  const q = route.query.q       || ''
  const c = route.query.carrera || ''
  const s = route.query.semestre|| ''
  const e = route.query.estatus || ''
  busquedaRaw.value    = q
  busquedaAlumno.value = q
  filtroCarreraId.value = c ? Number(c) : ''
  filtroSemestre.value  = s
  filtroEstatusId.value = e ? Number(e) : ''
}

watch(() => catalogos.value.carreras, (carreras) => {
  if (filtroCarreraId.value && !carreras.find(c => c.id_carrera == filtroCarreraId.value)) {
    filtroCarreraId.value = ''
  }
})
watch(() => catalogos.value.estatus_alumno, (estatus) => {
  if (filtroEstatusId.value && !estatus.find(e => e.id_estatus_alumno == filtroEstatusId.value)) {
    filtroEstatusId.value = ''
  }
})

const seleccionados     = ref(new Set())
const bulkAccion        = ref('')
const bulkValor         = ref('')
const showBulkConfirm   = ref(false)
const ejecutandoBulk    = ref(false)

const selectedCount = computed(() => seleccionados.value.size)

const labelBulkAccion = computed(() => ({
  estatus:  'CAMBIAR ESTATUS',
  carrera:  'ASIGNAR CARRERA',
  exportar: 'EXPORTAR CSV',
  eliminar: 'ELIMINAR SELECCIONADOS',
}[bulkAccion.value] || ''))

const toggleSeleccion = (alumno) => {
  const id = alumno.id_alumno || alumno.id
  const s = new Set(seleccionados.value)
  if (s.has(id)) s.delete(id)
  else           s.add(id)
  seleccionados.value = s
}

const toggleSelectAll = () => {
  const todosIds = paginatedAlumnos.value.map(a => a.id_alumno || a.id)
  const todosSeleccionados = todosIds.every(id => seleccionados.value.has(id))
  const s = new Set(seleccionados.value)
  if (todosSeleccionados) {
    todosIds.forEach(id => s.delete(id))
  } else {
    todosIds.forEach(id => s.add(id))
  }
  seleccionados.value = s
}

const limpiarSeleccion = () => {
  seleccionados.value = new Set()
  bulkAccion.value    = ''
  bulkValor.value     = ''
}

const aplicarBulkAccion = () => {
  if (!bulkAccion.value) return
  if (bulkAccion.value === 'exportar') {
    exportarCSV()
    return
  }
  bulkValor.value   = ''
  showBulkConfirm.value = true
}

const confirmarBulkAccion = async () => {
  ejecutandoBulk.value = true
  const ids = [...seleccionados.value]
  let exitosos = 0
  let fallidos = 0

  try {
    if (bulkAccion.value === 'eliminar') {
      const resultados = await Promise.allSettled(
        ids.map(id => fetch(`${API_URL}/api/alumnos/${id}`, {
          method: 'DELETE',
          headers: { 'Accept': 'application/json' }
        }))
      )
      resultados.forEach(r => r.status === 'fulfilled' && r.value.ok ? exitosos++ : fallidos++)

    } else if (bulkAccion.value === 'estatus') {
      if (!bulkValor.value) { mostrarNotificacion('SELECCIONA UN ESTATUS.', 'error'); return }
      const nombreEstatus = catalogos.value.estatus_alumno
      .find(e => e.id_estatus_alumno == bulkValor.value)?.nombre || ''
      const resultados = await Promise.allSettled(
        ids.map(id => fetch(`${API_URL}/api/alumnos/${id}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ estatus: nombreEstatus, id_estatus_alumno: Number(bulkValor.value) })
        }))
      )
      resultados.forEach(r => r.status === 'fulfilled' && r.value.ok ? exitosos++ : fallidos++)

    } else if (bulkAccion.value === 'carrera') {
      if (!bulkValor.value) { mostrarNotificacion('SELECCIONA UNA CARRERA.', 'error'); return }
      const resultados = await Promise.allSettled(
        ids.map(id => fetch(`${API_URL}/api/alumnos/${id}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ id_carrera: Number(bulkValor.value) })
        }))
      )
      resultados.forEach(r => r.status === 'fulfilled' && r.value.ok ? exitosos++ : fallidos++)
    }

    if (fallidos === 0) {
      mostrarNotificacion(`${exitosos} REGISTRO${exitosos !== 1 ? 'S' : ''} ACTUALIZADO${exitosos !== 1 ? 'S' : ''} CORRECTAMENTE.`, 'exito')
    } else if (exitosos > 0) {
      mostrarNotificacion(`${exitosos} OK / ${fallidos} FALLIDO${fallidos !== 1 ? 'S' : ''}. REVISA EL LOG.`, 'error')
    } else {
      mostrarNotificacion('NO SE PUDO COMPLETAR LA ACCIÓN MASIVA.', 'error')
    }

    await cargarAlumnosDesdeBD()
    showBulkConfirm.value = false
    limpiarSeleccion()
  } catch {
    mostrarNotificacion('ERROR DE CONEXIÓN EN ACCIÓN MASIVA.', 'error')
  } finally {
    ejecutandoBulk.value = false
  }
}

const exportarCSV = () => {
  const ids = [...seleccionados.value]
  const alumnosExport = alumnos.value.filter(a => ids.includes(a.id_alumno || a.id))
  const headers = ['NO_CONTROL', 'NOMBRE', 'CARRERA', 'SEMESTRE', 'ESTATUS']
  const rows = alumnosExport.map(a => [
    a.numero_control || a.noControl,
    resolverNombre(a).toUpperCase(),
    resolverCarrera(a).toUpperCase(),
    a.semestre_actual || a.semestre,
    (a.estatus || '').toUpperCase()
  ])
  const csv = [headers, ...rows].map(r => r.join(',')).join('\n')
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
  const url  = URL.createObjectURL(blob)
  const a    = document.createElement('a')
  a.href     = url
  a.download = `alumnos_export_${Date.now()}.csv`
  a.click()
  URL.revokeObjectURL(url)
  mostrarNotificacion(`CSV EXPORTADO CON ${alumnosExport.length} REGISTROS.`, 'exito')
  limpiarSeleccion()
}

const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif     = null

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje: mensaje.toUpperCase(), tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

const normalize = (t) =>
  !t ? '' : t.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')

const slugEstatus = (estatus) => {
  if (!estatus) return 'desconocido'
  const e = estatus.toLowerCase()
  if (e === 'activo')           return 'activo'
  if (e.includes('temporal'))   return 'temporal'
  if (e.includes('definitiva')) return 'definitiva'
  if (e === 'titulado')         return 'titulado'
  if (e === 'egresado')         return 'egresado'
  return 'desconocido'
}

const colorIndex = (nombre) => {
  if (!nombre) return 0
  let h = 0
  for (let i = 0; i < nombre.length; i++) h += nombre.charCodeAt(i)
  return h % 5
}

const resolverNombre    = (a) => a?.nombre || a?.persona?.nombre_completo || a?.persona?.nombre || ''
const resolverCarrera   = (a) => a?.carrera?.nombre_carrera || a?.carrera || ''
const resolverIdCarrera = (a) => a?.id_carrera || a?.carrera?.id_carrera || null
const iniciales         = (nombre) => !nombre ? '?' : nombre.split(' ').filter(Boolean).slice(0, 2).map(p => p[0]).join('').toUpperCase()
const formatearFecha    = (f) => !f ? '' : new Date(f).toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' })
const colorMateria      = (nombre) => {
  const colors = ['#1B396A','#7C3AED','#0891B2','#059669','#DC2626','#D97706']
  let h = 0
  for (let i = 0; i < (nombre || '').length; i++) h += nombre.charCodeAt(i)
  return colors[h % colors.length]
}
const corregirNombreAlumno = (nombre) => !nombre ? nombre : nombre.replace(/\bWilfido\b/gi, 'Wilfredo')

const kpiTotal       = computed(() => alumnos.value.length)
const kpiActivos     = computed(() => alumnos.value.filter(a => slugEstatus(a.estatus) === 'activo').length)
const kpiTemporales  = computed(() => alumnos.value.filter(a => slugEstatus(a.estatus) === 'temporal').length)
const kpiDefinitivas = computed(() => alumnos.value.filter(a => slugEstatus(a.estatus) === 'definitiva').length)
const kpiEgresados   = computed(() => alumnos.value.filter(a => ['egresado','titulado'].includes(slugEstatus(a.estatus))).length)

const filtradosPorTexto = computed(() => {
  const termGlobal = props.busquedaGlobal
  const termLocal  = busquedaAlumno.value.trim()
  if (!termGlobal && !termLocal) return alumnos.value
  return alumnos.value.filter(alumno => {
    const nombre    = resolverNombre(alumno)
    const noControl = (alumno.numero_control || alumno.noControl || '').toString()
    const passGlobal = !termGlobal ||
      normalize(nombre).includes(normalize(termGlobal)) ||
      noControl.includes(termGlobal)
    const passLocal = !termLocal ||
      normalize(nombre).includes(normalize(termLocal)) ||
      noControl.toLowerCase().includes(termLocal.toLowerCase())
    return passGlobal && passLocal
  })
})

const alumnosFiltrados = computed(() => {
  return filtradosPorTexto.value.filter(alumno => {
    const filtCarrera  = !filtroCarreraId.value || Number(resolverIdCarrera(alumno)) === Number(filtroCarreraId.value)
    const filtSemestre = !filtroSemestre.value  || String(alumno.semestre_actual || alumno.semestre) === String(filtroSemestre.value)
    const filtEstatus  = !filtroEstatusId.value || Number(alumno.id_estatus_alumno) === Number(filtroEstatusId.value)
    return filtCarrera && filtSemestre && filtEstatus
  })
})

const filtrosActivos  = computed(() => !!(filtroCarreraId.value || filtroSemestre.value || filtroEstatusId.value))
const contadorFiltros = computed(() =>
  [filtroCarreraId.value, filtroSemestre.value, filtroEstatusId.value].filter(Boolean).length
)

const totalPages = computed(() =>
  Math.ceil(alumnosFiltrados.value.length / filasPorPagina.value) || 1
)
const paginatedAlumnos = computed(() => {
  const start = (currentPage.value - 1) * filasPorPagina.value
  return alumnosFiltrados.value.slice(start, start + filasPorPagina.value)
})
const visiblePages = computed(() => {
  const total = totalPages.value
  const cur   = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = new Set([1, total, cur, cur - 1, cur + 1].filter(p => p >= 1 && p <= total))
  return [...pages].sort((a, b) => a - b)
})

const goToPage = (p) => { currentPage.value = p; filaActiva.value = -1 }
const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

const resetFilters = () => {
  busquedaRaw.value     = ''
  busquedaAlumno.value  = ''
  filtroCarreraId.value = ''
  filtroSemestre.value  = ''
  filtroEstatusId.value = ''
  currentPage.value     = 1
  filaActiva.value      = -1
  router.replace({ query: {} })
}

const limpiarBusqueda = () => {
  busquedaRaw.value    = ''
  busquedaAlumno.value = ''
  currentPage.value    = 1
  sincronizarURL()
  nextTick(() => inputBusqueda.value?.focus())
}

const navegarTeclado = (e) => {
  const total = paginatedAlumnos.value.length
  if (total === 0) return
  if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
    e.preventDefault(); filaActiva.value = Math.min(filaActiva.value + 1, total - 1)
  } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
    e.preventDefault(); filaActiva.value = Math.max(filaActiva.value - 1, 0)
  } else if (e.key === 'Enter' && filaActiva.value >= 0) {
    e.preventDefault(); abrirModalVer(paginatedAlumnos.value[filaActiva.value])
  } else if (e.key === 'PageDown') { e.preventDefault(); nextPage() }
  else if (e.key === 'PageUp')    { e.preventDefault(); prevPage() }
}

const handleRowClick = (alumno, index, event) => {
  if (selectedCount.value > 0) {
    toggleSeleccion(alumno)
  } else {
    filaActiva.value = index
    abrirModalVer(alumno)
  }
}

const cargarAlumnosDesdeBD = async () => {
  cargando.value = true
  try {
    const res  = await fetch(`${API_URL}/api/alumnos-crud`)
    if (!res.ok) throw new Error()
    const data = await res.json()
    alumnos.value = data.map(a => {
      const nombreCorregido = corregirNombreAlumno(a.nombre || a.persona?.nombre_completo || a.persona?.nombre || '')
      const c = { ...a, nombre: nombreCorregido }
      if (a.persona) {
        c.persona = {
          ...a.persona,
          nombre_completo: corregirNombreAlumno(a.persona.nombre_completo || ''),
          nombre:          corregirNombreAlumno(a.persona.nombre || ''),
        }
      }
      return c
    })
    const verControl = route.query.ver
    if (verControl) {
      const encontrado = alumnos.value.find(a => String(a.numero_control) === String(verControl))
      if (encontrado) abrirModalVer(encontrado)
    }
  } catch {
    mostrarNotificacion('No se pudo cargar la lista de alumnos.', 'error')
  } finally {
    cargando.value = false
  }
}

const cargarCatalogos = async () => {
  try {
    const res  = await fetch(`${API_URL}/api/alumnos/catalogos`)
    if (!res.ok) throw new Error()
    const data = await res.json()
    catalogos.value.carreras       = data.carreras        || []
    catalogos.value.estatus_alumno = data.estatus_alumno || []
  } catch {
    mostrarNotificacion('No se pudieron cargar los catálogos.', 'error')
  }
}

onMounted(async () => {
  restaurarFiltrosDesdeURL()
  await Promise.all([cargarAlumnosDesdeBD(), cargarCatalogos()])
})

watch(() => route.query.ver, (verControl) => {
  if (!verControl) return
  const encontrado = alumnos.value.find(a => String(a.numero_control) === String(verControl))
  if (encontrado) abrirModalVer(encontrado)
})

const abrirModalVer = (alumno) => {
  alumnoSeleccionado.value = alumno
  tabActivo.value   = 'general'
  kardexData.value  = null
  horarioData.value = null
  showViewModal.value = true
  router.replace({ query: { ...route.query, ver: alumno.numero_control || alumno.noControl } })
}

const cerrarModalVer = () => {
  showViewModal.value = false
  const q = { ...route.query }
  delete q.ver
  router.replace({ query: q })
  setTimeout(() => { alumnoSeleccionado.value = null }, 300)
}

watch(tabActivo, async (tab) => {
  if (!alumnoSeleccionado.value) return
  if (tab === 'kardex'   && !kardexData.value)  await cargarKardexAlumno(alumnoSeleccionado.value)
  if (tab === 'horario'  && !horarioData.value) await cargarHorarioAlumno(alumnoSeleccionado.value)
})

const cargarKardexAlumno = async (alumno) => {
  const nc = alumno.numero_control || alumno.noControl
  if (!nc) return
  cargandoKardex.value = true
  kardexError.value    = false
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
  else            semestresAbiertos.value.splice(idx, 1)
}

const porcentajeCreditos = computed(() => {
  const a = kardexData.value?.alumno
  if (!a?.creditos_totales) return 0
  return Math.round((a.creditos_acumulados / a.creditos_totales) * 100)
})

const estiloEstado = (estado) => ({
  acreditada: { background: '#DCFCE7', color: '#16A34A' },
  reprobada:  { background: '#FEF2F2', color: '#DC2626' },
  pendiente:  { background: '#FEF3C7', color: '#F59E0B' },
  no_cursada: { background: '#F3F4F6', color: '#6B7280' },
}[estado] ?? { background: '#F3F4F6', color: '#6B7280' })

const etiquetaEstado = (estado) => ({
  acreditada: 'ACRED.',
  reprobada:  'REPR.',
  pendiente:  'EN CURSO',
  no_cursada: 'PENDIENTE',
}[estado] ?? (estado || '').toUpperCase())

const verKardex = (alumno) => {
  const nc = alumno.numero_control || alumno.noControl
  if (nc) router.push(`/kardex/${nc}`)
}

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
  if (!id)                                   { mostrarNotificacion('No se encontró el identificador.', 'error'); return }
  if (!alumnoEditar.value.id_carrera)        { mostrarNotificacion('Selecciona una carrera.', 'error');          return }
  if (!alumnoEditar.value.id_estatus_alumno) { mostrarNotificacion('Selecciona un estatus.', 'error');           return }

  const nombreEstatus = catalogos.value.estatus_alumno
  .find(e => e.id_estatus_alumno === alumnoEditar.value.id_estatus_alumno)?.nombre || 'Activo'

  guardando.value = true
  try {
    const res = await fetch(`${API_URL}/api/alumnos/${id}`, {
      method:   'PUT',
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

const solicitarEliminar = () => { showModal.value = false; showModalEliminar.value = true }

const confirmarEliminar = async () => {
  const id = alumnoEditar.value.id_alumno
  if (!id) return
  guardando.value = true
  try {
    const res = await fetch(`${API_URL}/api/alumnos/${id}`, {
      method: 'DELETE', headers: { 'Accept': 'application/json' }
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
</script>

<style scoped>
/* ══════════════════════════════════════════════════════
BASE & OPTIMIZACIÓN DE ESPACIO
══════════════════════════════════════════════════════ */
.alumnos-page {
  padding: 20px 12px 80px;
  width: 100%;
  max-width: 100%;
  margin: 0 auto;
  background: #F4F6FA;
  font-family: 'Montserrat', sans-serif;
  min-height: 100vh;
  box-sizing: border-box;
}

.barra-carga-global {
  position: fixed; top: 0; left: 0; right: 0;
  height: 3px; z-index: 9999;
  pointer-events: none; opacity: 0; transition: opacity 0.2s;
}
.barra-carga-global.visible { opacity: 1; }
.barra-progreso {
  height: 100%;
  background: linear-gradient(90deg, #1B396A, #3B82F6, #1B396A);
  background-size: 200% 100%;
  animation: barraAnim 1.2s linear infinite;
}
@keyframes barraAnim { 0%{background-position:100% 0} 100%{background-position:-100% 0} }

.notificacion-ui {
  position: fixed; bottom: 80px; right: 24px;
  display: flex; align-items: center; gap: 10px;
  padding: 12px 20px; border-radius: 10px;
  font-weight: 600; font-size: 0.88rem;
  z-index: 10001; box-shadow: 0 8px 24px rgba(0,0,0,.14);
  max-width: 380px; letter-spacing: 0.02em;
}
.notificacion-ui.exito { background: #1B396A; color: #fff; }
.notificacion-ui.error { background: #DC2626; color: #fff; }
.notif-icono { width: 18px; height: 18px; flex-shrink: 0; }
.notif-fade-enter-active,.notif-fade-leave-active{transition:all .3s cubic-bezier(.4,0,.2,1)}
.notif-fade-enter-from,.notif-fade-leave-to{opacity:0;transform:translateY(20px)}

.breadcrumb {
  display: flex; align-items: center; gap: 6px;
  margin-bottom: 16px; font-size: 0.78rem;
  font-weight: 600; letter-spacing: 0.05em; color: #9CA3AF;
}
.breadcrumb-link { cursor: pointer; color: #1B396A; transition: opacity 0.15s; }
.breadcrumb-link:hover { opacity: 0.75; text-decoration: underline; }
.breadcrumb-chevron  { stroke: #D1D5DB; flex-shrink: 0; }
.breadcrumb-actual   { color: #374151; cursor: default; }

.page-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  gap: 16px; margin-bottom: 20px; flex-wrap: wrap;
  width: 100%;
}
.page-header-left { display: flex; flex-direction: column; gap: 4px; }
.page-header-btns  { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.page-title { font-size: 1.6rem; font-weight: 800; color: #0F172A; letter-spacing: -0.01em; margin: 0; }
.page-subtitle { font-size: 0.8rem; font-weight: 600; color: #6B7280; letter-spacing: 0.04em; margin: 0; }
.subtitle-sel  { color: #1B396A; }

.btn-nuevo {
  display: flex; align-items: center; gap: 8px; padding: 10px 20px;
  background: #1B396A; color: #fff; border: none; border-radius: 9px;
  font-family: 'Montserrat', sans-serif; font-weight: 700;
  font-size: 0.82rem; letter-spacing: 0.06em; cursor: pointer;
  transition: background 0.18s, transform 0.1s, box-shadow 0.18s;
  box-shadow: 0 2px 8px rgba(27,57,106,.25); white-space: nowrap;
}
.btn-nuevo:hover  { background: #152D57; box-shadow: 0 4px 16px rgba(27,57,106,.35); }
.btn-nuevo:active { transform: scale(0.97); }

.btn-select-all {
  display: flex; align-items: center; gap: 7px; padding: 10px 16px; border-radius: 9px;
  border: 1.5px solid #D1D5DB; background: #fff;
  font-family: 'Montserrat', sans-serif; font-weight: 700;
  font-size: 0.8rem; letter-spacing: 0.05em; color: #6B7280;
  cursor: pointer; transition: all 0.15s; white-space: nowrap;
}
.btn-select-all:hover  { border-color: #1B396A; color: #1B396A; }
.btn-select-all.activo { background: #EFF6FF; border-color: #1B396A; color: #1B396A; }

/* ══════════════════════════════════════════════════════
KPIs COMPACTOS
══════════════════════════════════════════════════════ */
.kpis-grid {
  display: grid; grid-template-columns: repeat(5,1fr);
  gap: 10px; margin-bottom: 20px;
}
.kpi-card {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 14px; border-radius: 10px;
  background: #fff; border: 1px solid #E5E7EB;
  box-shadow: 0 1px 4px rgba(0,0,0,.06);
  transition: box-shadow 0.2s, transform 0.15s;
  position: relative; overflow: hidden;
}
.kpi-card::before { content:''; position:absolute; top:0; left:0; width:4px; height:100%; border-radius:12px 0 0 12px; }
.kpi-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.1); transform: translateY(-1px); }
.kpi-total::before     { background: #1B396A; }
.kpi-activo::before    { background: #16A34A; }
.kpi-temporal::before  { background: #D97706; }
.kpi-definitiva::before{ background: #DC2626; }
.kpi-egresado::before  { background: #7C3AED; }
.kpi-icon-wrap { display:flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:8px; flex-shrink:0; }
.kpi-total     .kpi-icon-wrap { background:#EFF6FF; color:#1B396A; }
.kpi-activo    .kpi-icon-wrap { background:#F0FDF4; color:#16A34A; }
.kpi-temporal  .kpi-icon-wrap { background:#FFFBEB; color:#D97706; }
.kpi-definitiva .kpi-icon-wrap{ background:#FEF2F2; color:#DC2626; }
.kpi-egresado  .kpi-icon-wrap { background:#F5F3FF; color:#7C3AED; }
.kpi-data   { display:flex; flex-direction:column; gap:2px; min-width:0; }
.kpi-numero { font-size:1.5rem; font-weight:800; color:#0F172A; line-height:1; letter-spacing:-0.02em; }
.kpi-label  { font-size:0.65rem; font-weight:700; color:#6B7280; letter-spacing:0.06em; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

/* ══════════════════════════════════════════════════════
BARRA DE ACCIONES
══════════════════════════════════════════════════════ */
.barra-acciones {
  display:flex; align-items:center; gap:12px;
  margin-bottom:14px; flex-wrap:wrap; width: 100%;
}
.busqueda-grupo { flex:1; min-width:260px; }
.input-con-icono { position:relative; width:100%; }
.icono-input { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#9CA3AF; pointer-events:none; }
.input-busqueda {
  width:100%; padding:11px 40px 11px 42px;
  border: 1.5px solid #D1D5DB; border-radius:9px;
  font-family:'Montserrat',sans-serif; font-size:0.85rem;
  font-weight:600; letter-spacing:0.04em; text-transform:uppercase;
  transition:border-color .2s,box-shadow .2s; box-sizing:border-box; background:#fff; color:#111827;
}
.input-busqueda::placeholder { color:#9CA3AF; font-weight:600; }
.input-busqueda:focus { outline: none; border-color:#1B396A; box-shadow:0 0 0 3px rgba(27,57,106,.12); }
.btn-limpiar-input { position:absolute; right:11px; top:50%; transform:translateY(-50%); background:none; border:none; color:#9CA3AF; cursor:pointer; display:flex; align-items:center; padding:4px; border-radius:4px; transition:color .15s; }
.btn-limpiar-input:hover { color:#374151; }

.btn-filtro {
  display:flex; align-items:center; gap:8px; padding:11px 18px; border:1.5px solid #D1D5DB; border-radius:9px; background:#fff;
  font-family:'Montserrat',sans-serif; font-weight:700; font-size:0.82rem; letter-spacing:0.06em; color:#374151;
  cursor:pointer; transition:all .18s; position:relative; white-space:nowrap;
}
.btn-filtro:hover   { border-color:#1B396A; color:#1B396A; }
.btn-filtro.activo  { border-color:#1B396A; color:#1B396A; background:#EFF6FF; }
.btn-filtro.abierto { background:#1B396A; color:#fff; border-color:#1B396A; }
.filtros-badge {
  display:inline-flex; align-items:center; justify-content:center; width:18px; height:18px; border-radius:50%;
  background:#DC2626; color:#fff; font-size:0.68rem; font-weight:800;
}

.filtros-panel {
  background:#fff; border:1.5px solid #E5E7EB; border-radius:12px; padding:16px 20px 14px; margin-bottom:16px; box-shadow:0 2px 8px rgba(0,0,0,.06);
}
.filtros-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
.filtro-item  { display:flex; flex-direction:column; gap:6px; }
.filtro-label { display:flex; align-items:center; gap:5px; font-size:0.72rem; font-weight:700; color:#6B7280; letter-spacing:0.06em; }
.filter-select {
  padding:9px 12px; border:1.5px solid #D1D5DB; border-radius:8px;
  font-family:'Montserrat',sans-serif; font-size:0.82rem; font-weight:600; color:#111827; background:#F9FAFB; outline:none; cursor:pointer; transition:border-color .18s,box-shadow .18s;
}
.filter-select:focus { border-color:#1B396A; box-shadow:0 0 0 3px rgba(27,57,106,.1); background:#fff; }
.filtros-footer { display:flex; justify-content:flex-end; margin-top:12px; padding-top:10px; border-top:1px solid #F3F4F6; }
.btn-limpiar-filtros {
  display:flex; align-items:center; gap:7px; padding:7px 14px; background:none; border:1.5px solid #D1D5DB; border-radius:7px;
  font-family:'Montserrat',sans-serif; font-size:0.78rem; font-weight:700; letter-spacing:0.04em; color:#6B7280; cursor:pointer; transition:all .15s;
}
.btn-limpiar-filtros:hover { border-color:#DC2626; color:#DC2626; }
.filtros-slide-enter-active,.filtros-slide-leave-active{transition:all .25s cubic-bezier(.4,0,.2,1)}
.filtros-slide-enter-from,.filtros-slide-leave-to{opacity:0;transform:translateY(-10px)}

/* ══════════════════════════════════════════════════════
ESTADOS CARGANDO / VACÍO
══════════════════════════════════════════════════════ */
.estado-cargando { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:60px 20px; gap:16px; color:#6B7280; font-weight:600; font-size:0.85rem; letter-spacing:0.05em; }
.spinner-cards { width:32px; height:32px; border:3px solid #E5E7EB; border-top-color:#1B396A; border-radius:50%; animation:spin .8s linear infinite; }
@keyframes spin { to{transform:rotate(360deg)} }
.estado-vacio { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:60px 20px; gap:12px; text-align:center; }
.icono-vacio { width:48px; height:48px; stroke:#D1D5DB; }
.estado-vacio h3 { font-size:1rem; font-weight:800; color:#374151; letter-spacing:0.05em; margin:0; }
.estado-vacio p  { font-size:0.82rem; color:#9CA3AF; font-weight:600; margin:0; letter-spacing:0.03em; }
.btn-limpiar-vacio { margin-top:8px; padding:9px 22px; border-radius:8px; border:1.5px solid #1B396A; background:none; color:#1B396A; font-family:'Montserrat',sans-serif; font-weight:700; font-size:0.82rem; letter-spacing:0.05em; cursor:pointer; transition:background .15s; }
.btn-limpiar-vacio:hover { background:#EFF6FF; }

/* ══════════════════════════════════════════════════════
TABLA DE ALUMNOS
══════════════════════════════════════════════════════ */
.tabla-wrapper {
  overflow-x: auto; border-radius: 12px; background: #fff;
  border: 1px solid #E5E7EB; box-shadow: 0 1px 4px rgba(0,0,0,.05);
  margin-bottom: 24px; outline: none;
}
.tabla-alumnos {
  width: 100%; border-collapse: collapse; font-family: 'Montserrat', sans-serif;
  font-size: 0.85rem; min-width: 650px;
}
.tabla-alumnos th,
.tabla-alumnos td {
  padding: 10px 12px; text-align: left; border-bottom: 1px solid #F3F4F6;
  vertical-align: middle; white-space: nowrap;
}
.tabla-alumnos th {
  background: #F8FAFC; color: #374151; font-weight: 700;
  letter-spacing: 0.04em; text-transform: uppercase; font-size: 0.75rem;
  position: sticky; top: 0; z-index: 2;
}
.fila-alumno { transition: background 0.15s; cursor: pointer; }
.fila-alumno:hover { background: #F9FAFB; }
.fila-activa { outline: 2px solid #1B396A; outline-offset: -2px; background: #EFF6FF; }
.fila-seleccionada { background: #EFF6FF; }

.col-check { width: 50px; text-align: center; padding: 10px 8px !important; }
.btn-select-all-table {
  background: none; border: none; cursor: pointer; color: #6B7280;
  display: inline-flex; align-items: center; justify-content: center; padding: 4px; border-radius: 4px; transition: color 0.15s;
}
.btn-select-all-table:hover, .btn-select-all-table.activo { color: #1B396A; }
.col-control { font-family: 'Courier New', monospace; font-weight: 700; color: #1B396A; }
.col-nombre { font-weight: 700; color: #0F172A; max-width: 280px; overflow: hidden; text-overflow: ellipsis; }
.col-carrera { color: #6B7280; }
.col-semestre { text-align: center; font-weight: 700; }
.col-estatus { text-align: center; }
.col-acciones { width: 140px; text-align: right; }

/* Checkbox en tabla */
.bulk-checkbox {
  display: inline-flex; align-items: center; justify-content: center;
  width: 20px; height: 20px; border-radius: 5px; border: 2px solid #D1D5DB; background: #fff;
  cursor: pointer; transition: all .15s;
}
.bulk-checkbox:hover { border-color: #1B396A; }
.bulk-checkbox.checked { background: #1B396A; border-color: #1B396A; }
.bulk-checkbox.checked svg { stroke: #fff; }

/* Badges */
.estatus-badge { display: inline-flex; align-items: center; padding: 3px 8px; border-radius: 20px; font-size: 0.65rem; font-weight: 800; letter-spacing: 0.05em; width: fit-content; }
.badge-activo     { background: #DCFCE7; color: #15803D; }
.badge-temporal   { background: #FEF3C7; color: #B45309; }
.badge-definitiva { background: #FEE2E2; color: #B91C1C; }
.badge-titulado   { background: #EDE9FE; color: #6D28D9; }
.badge-egresado   { background: #E0F2FE; color: #0369A1; }
.badge-desconocido{ background: #F3F4F6; color: #6B7280; }

.btn-fila-accion { display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid transparent; cursor: pointer; transition: all .15s; background: #F8FAFC; display: inline-flex; margin-left: 4px; }
.btn-ver:hover    { background: #EFF6FF; border-color: #BFDBFE; color: #1B396A; }
.btn-editar:hover { background: #FFFBEB; border-color: #FDE68A; color: #D97706; }
.btn-kardex:hover { background: #F5F3FF; border-color: #DDD6FE; color: #7C3AED; }
.btn-ver    { color: #1B396A; }
.btn-editar { color: #D97706; }
.btn-kardex { color: #7C3AED; }

/* ══════════════════════════════════════════════════════
BARRA BULK (sticky bottom)
══════════════════════════════════════════════════════ */
.bulk-bar { position: fixed; bottom: 0; left: 0; right: 0; z-index: 500; background: #1B396A; box-shadow: 0 -4px 24px rgba(27,57,106,.35); border-top: 2px solid #152D57; }
.bulk-bar-inner { max-width: 100%; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; gap: 12px; flex-wrap: wrap; }
.bulk-bar-left { display: flex; align-items: center; gap: 10px; color: #fff; font-weight: 700; font-size: 0.9rem; letter-spacing: 0.04em; }
.bulk-count { font-weight: 800; }
.bulk-bar-center { display: flex; align-items: center; gap: 8px; }
.bulk-select { padding: 8px 14px; border-radius: 8px; border: none; font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 0.82rem; letter-spacing: 0.04em; background: rgba(255,255,255,.15); color: #fff; outline: none; cursor: pointer; }
.bulk-select option { background: #1B396A; color: #fff; }
.bulk-btn-aplicar { display: flex; align-items: center; gap: 7px; padding: 8px 20px; border-radius: 8px; border: none; background: #fff; color: #1B396A; font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 0.82rem; letter-spacing: 0.05em; cursor: pointer; transition: opacity .15s; }
.bulk-btn-aplicar:disabled { opacity: 0.5; cursor: not-allowed; }
.bulk-btn-aplicar:not(:disabled):hover { opacity: 0.9; }
.bulk-bar-right { display: flex; align-items: center; }
.bulk-btn-cancelar { display: flex; align-items: center; gap: 7px; padding: 8px 16px; border-radius: 8px; border: 1.5px solid rgba(255,255,255,.4); background: transparent; color: rgba(255,255,255,.9); font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 0.8rem; letter-spacing: 0.04em; cursor: pointer; transition: border-color .15s, background .15s; }
.bulk-btn-cancelar:hover { border-color: #fff; background: rgba(255,255,255,.1); }
.bulk-slide-enter-active,.bulk-slide-leave-active{transition:transform .3s cubic-bezier(.4,0,.2,1)}
.bulk-slide-enter-from,.bulk-slide-leave-to{transform:translateY(100%)}

/* ══════════════════════════════════════════════════════
PAGINACIÓN
══════════════════════════════════════════════════════ */
.paginacion { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding:14px 16px; background:#fff; border:1.5px solid #E5E7EB; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.05); width: 100%; box-sizing: border-box; }
.paginacion-izquierda { display:flex; align-items:center; gap:8px; }
.pag-label  { font-size:0.75rem; font-weight:700; color:#6B7280; letter-spacing:0.04em; }
.select-filas { padding:5px 10px; border:1.5px solid #E5E7EB; border-radius:7px; font-family:'Montserrat',sans-serif; font-size:0.8rem; font-weight:700; color:#374151; cursor:pointer; }
.paginacion-centro { font-size:0.78rem; font-weight:600; color:#6B7280; letter-spacing:0.04em; }
.paginacion-derecha { display:flex; gap:4px; }
.btn-pag { display:flex; align-items:center; justify-content:center; min-width:34px; height:34px; padding:0 4px; border:1.5px solid #E5E7EB; border-radius:8px; background:#fff; font-family:'Montserrat',sans-serif; font-size:0.82rem; font-weight:700; color:#374151; cursor:pointer; transition:all .15s; }
.btn-pag:hover:not(:disabled) { border-color:#1B396A; color:#1B396A; background:#EFF6FF; }
.btn-pag.activo { background:#1B396A; color:#fff; border-color:#1B396A; }
.btn-pag:disabled { opacity:0.35; cursor:not-allowed; }
.pie-pagina { text-align:center; margin-top:24px; font-size:0.72rem; font-weight:600; letter-spacing:0.06em; color:#9CA3AF; }

/* ══════════════════════════════════════════════════════
MODALES — BASE
══════════════════════════════════════════════════════ */
.modal-overlay { position:fixed; inset:0; background:rgba(15,23,42,.55); backdrop-filter:blur(3px); display:flex; align-items:center; justify-content:center; z-index:1000; padding:16px; }
.modal-content { background:#fff; border-radius:16px; width:100%; max-width:560px; max-height:90vh; display:flex; flex-direction:column; box-shadow:0 20px 60px rgba(0,0,0,.2); overflow:hidden; }
.modal-ver-alumno { max-width:660px; }
.modal-confirmar  { max-width:460px; }
.modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 22px 18px; background:linear-gradient(135deg,#1B396A 0%,#1D4ED8 100%); flex-shrink:0; }
.modal-header-avatar-group { display:flex; gap:14px; align-items:center; }
.detalle-avatar { flex-shrink:0; }
.avatar-img     { width:50px; height:50px; border-radius:12px; object-fit:cover; border:2px solid rgba(255,255,255,.35); }
.avatar-placeholder { width:50px; height:50px; border-radius:12px; display:flex; align-items:center; justify-content:center; border:2px solid rgba(255,255,255,.3); }
.avatar-placeholder span { color:#fff; font-weight:800; font-size:1.1rem; }
.modal-header-info { display:flex; flex-direction:column; gap:3px; }
.modal-header-tag  { font-size:0.68rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:rgba(255,255,255,.7); }
.modal-header h3   { margin:0; font-size:1.05rem; font-weight:800; color:#fff; letter-spacing:-0.01em; }
.sub-header-control{ font-size:0.82rem; font-weight:700; font-family:monospace; color:rgba(255,255,255,.8); }
.btn-cerrar-modal { background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2); color:#fff; cursor:pointer; border-radius:8px; display:flex; align-items:center; justify-content:center; width:36px; height:36px; flex-shrink:0; transition:background .15s; }
.btn-cerrar-modal:hover { background:rgba(255,255,255,.2); }
.modal-body-tabs { display:flex; flex-direction:column; flex:1; overflow:hidden; }
.detalle-tabs     { display:flex; background:#F8FAFC; border-bottom:1px solid #E5E7EB; flex-shrink:0; }
.tab-btn { flex:1; display:flex; align-items:center; justify-content:center; gap:6px; padding:12px 8px; background:none; border:none; border-bottom:2px solid transparent; cursor:pointer; font-size:0.78rem; font-weight:700; letter-spacing:0.05em; color:#9CA3AF; font-family:'Montserrat',sans-serif; transition:all .15s; }
.tab-btn:hover  { color:#1B396A; }
.tab-btn.activo { color:#1B396A; border-bottom-color:#1B396A; background:#fff; }
.tab-contenido-scroll { overflow-y:auto; padding:1.4rem 1.6rem; flex:1; }
.tab-panel { display:flex; flex-direction:column; gap:1rem; }

/* ══════════════════════════════════════════════════════
EXPEDIENTE v2.2
══════════════════════════════════════════════════════ */
.exp-seccion { border:1.5px solid #E5E7EB; border-radius:12px; overflow:hidden; background:#fff; }
.exp-seccion-titulo { display:flex; align-items:center; gap:7px; background:#F0F5FF; padding:10px 14px; font-size:0.72rem; font-weight:800; letter-spacing:0.08em; color:#1B396A; border-bottom:1px solid #DBEAFE; }
.exp-seccion-titulo svg { stroke:#1B396A; flex-shrink:0; }
.exp-seccion .detalle-grid { padding:14px; }
.exp-contacto-titulo        { margin-top:4px; }
.detalle-grid { display:grid; grid-template-columns:1fr 1fr; gap:0.9rem; }
.detalle-campo { display:flex; flex-direction:column; gap:4px; padding:0.8rem 1rem; background:#F8FAFC; border-radius:9px; border:1px solid #E5E7EB; }
.detalle-campo.full-width { grid-column:1 / -1; }
.detalle-label { font-size:0.68rem; font-weight:700; color:#9CA3AF; letter-spacing:0.08em; }
.detalle-valor { font-size:0.92rem; font-weight:600; color:#1A1A1A; }
.detalle-numero{ font-size:1.1rem; font-weight:800; color:#1B396A; }
.mono-bold { font-family:'Courier New',monospace; font-weight:800; font-size:1rem; color:#1B396A; }
.estatus-badge-modal { display:inline-flex; align-items:center; padding:5px 14px; border-radius:20px; font-size:0.78rem; font-weight:800; letter-spacing:0.06em; width:fit-content; }
.creditos-bloque { background:#F8FAFC; border-radius:10px; padding:12px 14px; border:1px solid #E5E7EB; margin-bottom:12px; }
.creditos-row   { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
.creditos-label { font-weight:700; color:#1A1A1A; letter-spacing:0.04em; font-size:0.8rem; }
.creditos-pct    { font-weight:700; color:#6B7280; font-size:0.8rem; }
.creditos-pct.verde { color:#16A34A; }
.creditos-barra-track { height:8px; background:#E5E7EB; border-radius:4px; overflow:hidden; }
.creditos-barra-fill  { height:100%; border-radius:4px; transition:width .8s ease; }
.kardex-semestres { display:flex; flex-direction:column; gap:.5rem; }
.semestre-bloque   { border:1px solid #E5E7EB; border-radius:10px; overflow:hidden; background:#fff; }
.semestre-btn { width:100%; display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:none; border:none; cursor:pointer; font-family:'Montserrat',sans-serif; transition:background .15s; }
.semestre-btn:hover  { background:#F8FAFC; }
.semestre-btn.abierto{ background:#EFF6FF; }
.semestre-titulo { font-size:0.85rem; font-weight:800; color:#1A1A1A; letter-spacing:0.04em; }
.semestre-badges { display:flex; align-items:center; gap:5px; }
.badge-mini { font-size:0.68rem; font-weight:800; padding:2px 8px; border-radius:20px; letter-spacing:0.04em; }
.badge-mini.acreditadas{ background:#DCFCE7; color:#16A34A; }
.badge-mini.reprobadas { background:#FEE2E2; color:#DC2626; }
.flecha-semestre { stroke:#6B7280; transition:transform .2s; flex-shrink:0; }
.flecha-semestre.girado { transform:rotate(180deg); }
.semestre-materias { border-top:1px solid #E5E7EB; }
.tabla-mini { width:100%; border-collapse:collapse; font-size:0.8rem; }
.tabla-mini th { background:#F8FAFC; padding:7px 12px; text-align:left; font-size:0.68rem; font-weight:800; color:#9CA3AF; text-transform:uppercase; letter-spacing:0.05em; border-bottom:1px solid #E5E7EB; }
.tabla-mini td       { padding:7px 12px; border-bottom:.5px solid #F9FAFB; vertical-align:middle; color:#1A1A1A; font-weight:500; }
.tabla-mini tr.fila-repr { background:#FEF2F2; }
.tabla-mini td.centrado  { text-align:center; }
.clave-mono { font-family:monospace; font-size:0.78rem; font-weight:800; color:#1B396A; }
.badge-estado-mini { font-size:0.68rem; font-weight:800; padding:2px 8px; border-radius:10px; display:inline-block; letter-spacing:0.03em; }
.text-verde { color:#16A34A; } .text-rojo { color:#DC2626; } .text-gris { color: #9CA3AF; }
.horario-lista  { display:flex; flex-direction:column; gap:.6rem; }
.horario-item   { display:flex; align-items:stretch; border:1px solid #E5E7EB; border-radius:9px; overflow:hidden; background:#fff; }
.horario-color  { width:5px; flex-shrink:0; }
.horario-info   { padding:10px 12px; display:flex; flex-direction:column; gap:2px; }
.horario-nombre { font-weight:800; font-size:0.86rem; color:#1A1A1A; }
.horario-meta   { font-size:0.75rem; color:#6B7280; font-weight:600; }
.horario-aula   { font-size:0.72rem; color:#9CA3AF; font-weight:600; letter-spacing:0.03em; }
.kardex-cargando { display:flex; flex-direction:column; gap:8px; }
.skeleton-linea  { height:14px; border-radius:6px; background:linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
.skeleton-linea.largo{ width:70%; } .skeleton-linea.medio{ width:45%; }
.skeleton-fila { height:40px; border-radius:8px; background:linear-gradient(90deg,#F3F4F6 25%,#FFF 50%,#F3F4F6 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
.kardex-error { display:flex; align-items:center; gap:8px; padding:14px; background:#FEF2F2; border-radius:8px; font-size:0.85rem; font-weight:600; color:#DC2626; }
.kardex-error button { margin-left:auto; padding:5px 12px; border-radius:6px; border:1.5px solid #DC2626; background:none; color:#DC2626; font-family:'Montserrat',sans-serif; font-weight:700; font-size:0.78rem; letter-spacing:0.04em; cursor:pointer; }
.kardex-vacio { display:flex; flex-direction:column; align-items:center; padding:30px; text-align:center; color:#9CA3AF; font-size:0.85rem; font-weight:600; letter-spacing:0.04em; gap:8px; }
.bulk-preview { display:flex; flex-wrap:wrap; gap:6px; justify-content:center; max-height:80px; overflow-y:auto; margin-top:8px; }
.bulk-id-chip { background:#EFF6FF; color:#1B396A; border:1px solid #BFDBFE; padding:2px 10px; border-radius:20px; font-size:0.72rem; font-weight:800; font-family:monospace; letter-spacing:0.04em; }
.bulk-id-mas { background:#F3F4F6; color:#6B7280; border-color:#E5E7EB; }
.bulk-sub-opcion { margin-top:14px; display:flex; flex-direction:column; gap:6px; width:100%; }
.form-body       { padding:1.4rem 1.6rem; overflow-y:auto; }
.form-grupo      { margin-bottom:1.1rem; }
.form-grupo-doble{ display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
.form-grupo label{ display:block; margin-bottom:6px; font-weight:700; font-size:0.78rem; letter-spacing:0.06em; color:#374151; }
.obligatorio     { color:#DC2626; }
.modal-input, .modal-select { width:100%; padding:10px 14px; border:1.5px solid #E5E7EB; border-radius:9px; font-size:0.9rem; font-weight:600; background:#fff; color:#1A1A1A; font-family:'Montserrat',sans-serif; outline:none; transition:border-color .2s,box-shadow .2s; box-sizing:border-box; }
.modal-input:focus,.modal-select:focus { border-color:#1B396A; box-shadow:0 0 0 3px rgba(27,57,106,.12); }
.modal-input.deshabilitado { background:#F5F7FA; cursor:not-allowed; color:#9CA3AF; }
.modal-footer { padding:14px 20px; background:#F8FAFC; display:flex; gap:8px; justify-content:flex-end; border-top:1px solid #E5E7EB; flex-shrink:0; flex-wrap:wrap; }
.btn-secundario { display:flex; align-items:center; gap:6px; padding:9px 18px; border-radius:9px; font-weight:700; cursor:pointer; font-family:'Montserrat',sans-serif; background:#fff; color:#374151; border:1.5px solid #D1D5DB; transition:background .15s; font-size:0.82rem; letter-spacing:0.04em; }
.btn-secundario:hover:not(:disabled) { background:#F3F4F6; }
.btn-secundario:disabled { opacity:0.5; cursor:not-allowed; }
.btn-eliminar { display:flex; align-items:center; gap:6px; padding:9px 18px; border-radius:9px; font-weight:700; cursor:pointer; font-family:'Montserrat',sans-serif; background:#DC2626; color:#fff; border:1.5px solid #DC2626; transition:background .15s; font-size:0.82rem; letter-spacing:0.04em; }
.btn-eliminar:hover:not(:disabled) { background:#B91C1C; }
.btn-eliminar:disabled { opacity:0.5; cursor:not-allowed; }
.btn-guardar { display:flex; align-items:center; gap:8px; padding:9px 20px; border-radius:9px; font-weight:700; cursor:pointer; font-family:'Montserrat',sans-serif; background:#1B396A; color:#fff; border:1.5px solid #1B396A; transition:background .15s; font-size:0.82rem; letter-spacing:0.04em; }
.btn-guardar:hover:not(:disabled) { background:#152D57; }
.btn-guardar:disabled { opacity:0.55; cursor:not-allowed; }
.btn-accion-outline { display:flex; align-items:center; gap:7px; padding:9px 16px; border-radius:9px; font-weight:700; cursor:pointer; font-family:'Montserrat',sans-serif; background:#fff; color:#1B396A; border:1.5px solid #1B396A; transition:background .15s; font-size:0.82rem; letter-spacing:0.04em; }
.btn-accion-outline:hover { background:#EFF6FF; }
.spinner-btn { display:inline-block; width:13px; height:13px; border:2px solid rgba(255,255,255,.4); border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; flex-shrink:0; }
.confirmar-body { display:flex; flex-direction:column; align-items:center; gap:12px; text-align:center; padding:2rem 1.6rem; }
.confirmar-icono { width:52px; height:52px; stroke:#F59E0B; }
.confirmar-body p { color:#374151; font-size:0.9rem; font-weight:600; margin:0; line-height:1.5; letter-spacing:0.02em; }
.expand-enter-active,.expand-leave-active{transition:all .25s ease;overflow:hidden}
.expand-enter-from,.expand-leave-to{max-height:0;opacity:0}
.expand-enter-to,.expand-leave-from{max-height:500px;opacity:1}
.modal-fade-enter-active,.modal-fade-leave-active{transition:all .25s cubic-bezier(.4,0,.2,1)}
.modal-fade-enter-from,.modal-fade-leave-to{opacity:0}
.modal-fade-enter-from .modal-content,.modal-fade-leave-to .modal-content{transform:scale(0.95) translateY(10px)}

/* ══════════════════════════════════════════════════════
RESPONSIVE
══════════════════════════════════════════════════════ */
@media (max-width:1200px) { .kpis-grid{grid-template-columns:repeat(3,1fr)} }
@media (max-width:900px) {
  .kpis-grid{grid-template-columns:repeat(2,1fr)}
  .filtros-grid{grid-template-columns:1fr 1fr}
  .col-nombre{max-width:180px}
}
@media (max-width:768px) {
  .alumnos-page{padding:14px 8px 90px}
  .kpis-grid{grid-template-columns:1fr 1fr}
  .filtros-grid{grid-template-columns:1fr}
  .detalle-grid{grid-template-columns:1fr}
  .form-grupo-doble{grid-template-columns:1fr}
  .modal-footer{flex-direction:column}
  .modal-footer button{width:100%;justify-content:center}
  .paginacion{flex-direction:column;gap:10px}
  .page-header{flex-direction:column;align-items:flex-start}
  .page-header-btns{width:100%;flex-direction:column}
  .btn-nuevo,.btn-select-all{width:100%;justify-content:center}
  .bulk-bar-inner{flex-direction:column;align-items:flex-start;gap:10px}
  .bulk-bar-center{width:100%;flex-wrap:wrap}
  .tabla-wrapper{overflow-x:auto; margin-bottom: 20px;}
  .tabla-alumnos{min-width: 620px;}
}
</style>
