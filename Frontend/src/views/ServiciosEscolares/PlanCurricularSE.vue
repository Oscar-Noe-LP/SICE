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

      <!-- ========== VISTA: LISTA (P1) ========== -->
      <template v-if="vista === 'lista'">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
          <span class="breadcrumb-link" @click="$router.push('/configuracion')">Configuración</span>
          <span class="breadcrumb-sep">›</span>
          <span class="breadcrumb-actual">Plan Curricular</span>
        </nav>

        <!-- Header -->
        <div class="page-header">
          <h1 class="page-title">Plan Curricular</h1>
          <p class="page-subtitle">{{ planesFiltrados.length }} plan(es) encontrado(s)</p>
        </div>

        <!-- KPIs -->
        <div class="kpis-grid">
          <div class="kpi-card kpi-total">
            <div class="kpi-icon">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div class="kpi-info">
              <span class="kpi-num">{{ kpis.cargando ? '…' : kpis.total }}</span>
              <span class="kpi-label">Total de planes</span>
            </div>
          </div>
          <div class="kpi-card kpi-activos">
            <div class="kpi-icon">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="kpi-info">
              <span class="kpi-num">{{ kpis.cargando ? '…' : kpis.activos }}</span>
              <span class="kpi-label">Planes activos</span>
            </div>
          </div>
          <div class="kpi-card kpi-revision">
            <div class="kpi-icon">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="kpi-info">
              <span class="kpi-num">{{ kpis.cargando ? '…' : kpis.en_revision }}</span>
              <span class="kpi-label">En revisión</span>
            </div>
          </div>
        </div>

        <!-- Barra de acciones -->
        <div class="barra-acciones">
          <div class="busqueda-grupo">
            <div class="input-con-icono">
              <svg class="icono-input" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input 
                type="text" 
                ref="inputBusqueda"
                v-model="busquedaPlan" 
                class="input-busqueda" 
                placeholder="Buscar por nombre del plan..."
                @input="currentPage = 1"
                @keydown.escape="limpiarBusqueda"
              />
              <button v-if="busquedaPlan" class="btn-limpiar-input" @click="limpiarBusqueda" title="Limpiar búsqueda">
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

          <button class="btn-nuevo" @click="abrirFormularioNuevo">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            + Nuevo Plan
          </button>
        </div>

        <!-- Panel de filtros -->
        <transition name="filtros-slide">
          <div v-if="mostrarFiltros" class="filtros-panel">
            <div class="filtros-grid">
              <div class="filtro-item">
                <label class="filtro-label">Carrera</label>
                <select v-model="filtroCarrera" class="filter-select" @change="currentPage = 1">
                  <option value="">Todas las carreras</option>
                  <option v-for="c in catalogos.carreras" :key="c.id_carrera" :value="c.id_carrera">{{ c.nombre }}</option>
                </select>
              </div>
              <div class="filtro-item">
                <label class="filtro-label">Vigencia</label>
                <select v-model="filtroVigencia" class="filter-select" @change="currentPage = 1">
                  <option value="">Todas</option>
                  <option value="vigente">Vigente</option>
                  <option value="proximo">Próximo a vencer</option>
                  <option value="vencido">Vencido</option>
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

        <!-- Tabla -->
        <div class="tabla-contenedor">
          <div v-if="cargando && planes.length === 0" class="estado-cargando">
            <div class="spinner-tabla"></div>
            <p>Cargando planes...</p>
          </div>

          <table v-else-if="paginatedPlanes.length > 0" class="data-table">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Carrera</th>
                <th class="th-centro">Vigencia</th>
                <th class="th-centro">Créditos totales</th>
                <th class="th-centro">Estatus</th>
                <th class="th-acciones">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="plan in paginatedPlanes" :key="plan.id">
                <td class="celda-nombre">{{ plan.nombre }}</td>
                <td class="celda-secundaria">{{ plan.carrera }}</td>
                <td class="td-centro">{{ plan.vigencia_inicio }} – {{ plan.vigencia_fin }}</td>
                <td class="td-centro">{{ plan.creditos_totales }}</td>
                <td class="td-centro">
                  <span class="estatus-badge" :class="claseEstatus(plan.estatus)">{{ plan.estatus }}</span>
                </td>
                <td class="celda-acciones">
                  <button class="btn-icono ver" @click="verDetalle(plan)" title="Ver detalle">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  </button>
                  <button class="btn-icono editar" @click="abrirFormularioEditar(plan)" title="Editar">
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
            <p>No se encontraron planes curriculares con los criterios aplicados.</p>
            <button class="btn-limpiar-vacio" @click="resetFilters">Limpiar filtros</button>
          </div>
        </div>

        <!-- Paginación -->
        <div class="paginacion">
          <div class="paginacion-izquierda" v-if="planesFiltrados.length > 5">
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
      </template>

      <!-- ========== VISTA: DETALLE (P3 + P4) ========== -->
      <template v-if="vista === 'detalle' && planSeleccionado">
        <nav class="breadcrumb">
          <span class="breadcrumb-link" @click="volverALista">Plan Curricular</span>
          <span class="breadcrumb-sep">›</span>
          <span class="breadcrumb-actual">{{ planSeleccionado.nombre }}</span>
        </nav>

        <div class="page-header">
          <h1 class="page-title">{{ planSeleccionado.nombre }}</h1>
          <p class="page-subtitle">{{ planSeleccionado.carrera }} · Vigencia: {{ planSeleccionado.vigencia_inicio }} – {{ planSeleccionado.vigencia_fin }}</p>
        </div>

        <!-- KPIs del plan -->
        <div class="kpis-grid kpis-detalle">
          <div class="kpi-card kpi-total">
            <div class="kpi-icon">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="kpi-info">
              <span class="kpi-num">{{ planSeleccionado.creditos_totales }}</span>
              <span class="kpi-label">Total créditos</span>
            </div>
          </div>
          <div class="kpi-card kpi-activos">
            <div class="kpi-icon">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
            <div class="kpi-info">
              <span class="kpi-num">{{ totalMaterias }}</span>
              <span class="kpi-label">Materias registradas</span>
            </div>
          </div>
          <div class="kpi-card kpi-revision">
            <div class="kpi-icon">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>
            </div>
            <div class="kpi-info">
              <span class="kpi-num">{{ semestres.length }}</span>
              <span class="kpi-label">Semestres configurados</span>
            </div>
          </div>
        </div>

        <!-- Barra de acciones del detalle -->
        <div class="barra-acciones">
          <div></div>
          <div style="display: flex; gap: 10px;">
            <button class="btn-secundario" @click="abrirFormularioEditar(planSeleccionado)">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              Editar plan
            </button>
            <button class="btn-secundario" @click="volverALista">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
              Volver a lista
            </button>
          </div>
        </div>

        <!-- Tabs de semestres -->
        <div class="semestres-tabs-contenedor">
          <div class="semestres-tabs">
            <button 
              v-for="sem in semestres" 
              :key="sem.numero" 
              class="semestre-tab" 
              :class="{ activo: semestreActivo === sem.numero }"
              @click="semestreActivo = sem.numero"
            >
              S{{ sem.numero }}
              <span class="semestre-creditos-badge">{{ creditosPorSemestre(sem.numero) }} cr.</span>
            </button>
          </div>
        </div>

        <!-- Tabla de materias del semestre activo -->
        <div class="tabla-contenedor">
          <div v-if="cargandoMaterias" class="estado-cargando">
            <div class="spinner-tabla"></div>
            <p>Cargando materias...</p>
          </div>
          <table v-else-if="materiasSemestreActivo.length > 0" class="data-table">
            <thead>
              <tr>
                <th>Materia</th>
                <th class="th-centro">Créditos</th>
                <th class="th-centro">Tipo</th>
                <th>Prerrequisitos</th>
                <th class="th-acciones">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="materia in materiasSemestreActivo" :key="materia.id">
                <td class="celda-nombre">{{ materia.nombre }}</td>
                <td class="td-centro">{{ materia.creditos }}</td>
                <td class="td-centro">
                  <span class="tipo-badge" :class="materia.tipo === 'Obligatoria' ? 'tipo-obligatoria' : 'tipo-optativa'">
                    {{ materia.tipo }}
                  </span>
                </td>
                <td class="celda-secundaria">
                  <span v-if="materia.prerequisitos?.length">
                    {{ materia.prerequisitos.map(p => p.nombre).join(', ') }}
                  </span>
                  <span v-else class="sin-prerequisitos">—</span>
                </td>
                <td class="celda-acciones">
                  <button class="btn-icono eliminar-materia" @click="confirmarEliminarMateria(materia)" title="Eliminar materia">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-else class="estado-vacio">
            <svg class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3>Sin materias</h3>
            <p>No hay materias registradas en este semestre.</p>
          </div>
        </div>

        <!-- Barra acumulada de créditos -->
        <div class="creditos-acumulados">
          <h3 class="creditos-acumulados-titulo">Créditos por semestre</h3>
          <div class="creditos-barras">
            <div v-for="sem in semestres" :key="sem.numero" class="creditos-barra-item">
              <span class="creditos-barra-label">S{{ sem.numero }}</span>
              <div class="creditos-barra-track">
                <div 
                  class="creditos-barra-fill" 
                  :style="{ width: (creditosPorSemestre(sem.numero) / maxCreditosSemestre) * 100 + '%' }"
                ></div>
              </div>
              <span class="creditos-barra-num">{{ creditosPorSemestre(sem.numero) }}</span>
            </div>
          </div>
        </div>

        <!-- Botón agregar materia + Tab de prerrequisitos -->
        <div class="detalle-acciones-bottom">
          <button class="btn-nuevo" @click="abrirModalAgregarMateria">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            + Agregar materia al semestre
          </button>
          <button class="btn-secundario" @click="mostrarPrerrequisitos = !mostrarPrerrequisitos" :class="{ activo: mostrarPrerrequisitos }">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            Gestionar prerrequisitos
          </button>
        </div>

        <!-- Sección prerrequisitos (P4) -->
        <div v-if="mostrarPrerrequisitos" class="prerrequisitos-panel">
          <h3 class="prerrequisitos-titulo">Prerrequisitos entre materias</h3>
          <div class="prerrequisitos-form">
            <div class="filtro-item">
              <label class="filtro-label">Materia origen</label>
              <select v-model="prerrequisitoNuevo.materia_origen_id" class="filter-select">
                <option value="">Seleccionar materia</option>
                <option v-for="m in todasLasMaterias" :key="m.id" :value="m.id">{{ m.nombre }} (S{{ m.semestre }})</option>
              </select>
            </div>
            <div class="filtro-item">
              <label class="filtro-label">Materia prerrequisito</label>
              <select v-model="prerrequisitoNuevo.materia_prerequisito_id" class="filter-select">
                <option value="">Seleccionar materia</option>
                <option v-for="m in todasLasMaterias" :key="m.id" :value="m.id">{{ m.nombre }} (S{{ m.semestre }})</option>
              </select>
            </div>
            <button class="btn-nuevo" @click="agregarPrerrequisito" :disabled="!prerrequisitoNuevo.materia_origen_id || !prerrequisitoNuevo.materia_prerequisito_id">
              + Agregar prerrequisito
            </button>
          </div>
          <div class="tabla-contenedor" style="margin-top: 1rem;" v-if="prerrequisitos.length > 0">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Materia</th>
                  <th>Requiere</th>
                  <th class="th-acciones">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="pre in prerrequisitos" :key="pre.id">
                  <td class="celda-nombre">{{ pre.materia_origen_nombre }}</td>
                  <td class="celda-nombre">{{ pre.materia_prerequisito_nombre }}</td>
                  <td class="celda-acciones">
                    <button class="btn-icono eliminar-materia" @click="eliminarPrerrequisito(pre.id)" title="Eliminar dependencia">
                      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="estado-vacio" style="padding: 1.5rem;">
            <p>No hay prerrequisitos configurados.</p>
          </div>
        </div>

        <footer class="pie-pagina">© 2026 Tecnológico Nacional de México | Todos los derechos reservados</footer>
      </template>

    </div>

    <!-- ==================== MODAL: FORMULARIO NUEVO/EDITAR PLAN (P2) ==================== -->
    <Teleport to="body">
      <transition name="modal-fade">
        <div v-if="mostrarFormulario" class="modal-overlay" @click.self="cerrarFormulario">
          <div class="modal-content">
            <div class="modal-header">
              <div class="modal-header-info">
                <span class="modal-header-tag">{{ planEditar.id ? 'Edición' : 'Nuevo' }}</span>
                <h3>{{ planEditar.id ? 'Editar Plan Curricular' : 'Nuevo Plan Curricular' }}</h3>
              </div>
              <button @click="cerrarFormulario" class="btn-cerrar-modal" title="Cerrar">×</button>
            </div>
            <div class="modal-body form-body">
              <div class="form-grupo">
                <label>Carrera <span class="obligatorio">*</span></label>
                <select v-model="planEditar.carrera_id" class="modal-select">
                  <option value="">Seleccionar carrera</option>
                  <option v-for="c in catalogos.carreras" :key="c.id_carrera" :value="c.id_carrera">{{ c.nombre }}</option>
                </select>
              </div>
              <div class="form-grupo">
                <label>Nombre del plan <span class="obligatorio">*</span></label>
                <input v-model="planEditar.nombre" type="text" class="modal-input" placeholder="Ej: Plan 2024 Ingeniería en Sistemas" />
              </div>
              <div class="form-grupo-doble">
                <div class="form-grupo">
                  <label>Fecha inicio vigencia <span class="obligatorio">*</span></label>
                  <input v-model="planEditar.vigencia_inicio" type="date" class="modal-input" />
                </div>
                <div class="form-grupo">
                  <label>Fecha fin vigencia <span class="obligatorio">*</span></label>
                  <input v-model="planEditar.vigencia_fin" type="date" class="modal-input" />
                </div>
              </div>
              <div class="form-grupo">
                <label>Créditos totales <span class="obligatorio">*</span></label>
                <input v-model="planEditar.creditos_totales" type="number" class="modal-input" placeholder="Ej: 260" min="1" />
              </div>
              <div class="form-grupo">
                <label>Estatus <span class="obligatorio">*</span></label>
                <select v-model="planEditar.estatus" class="modal-select">
                  <option value="">Seleccionar estatus</option>
                  <option value="Activo">Activo</option>
                  <option value="En revisión">En revisión</option>
                  <option value="Inactivo">Inactivo</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn-secundario" @click="cerrarFormulario" :disabled="guardando">Cancelar</button>
              <button class="btn-guardar" @click="guardarPlan" :disabled="guardando">
                <span v-if="guardando" class="spinner-btn"></span>
                {{ guardando ? 'Guardando...' : 'Guardar' }}
              </button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <!-- ==================== MODAL: AGREGAR MATERIA ==================== -->
    <Teleport to="body">
      <transition name="modal-fade">
        <div v-if="mostrarModalAgregarMateria" class="modal-overlay" @click.self="mostrarModalAgregarMateria = false">
          <div class="modal-content">
            <div class="modal-header">
              <div class="modal-header-info">
                <span class="modal-header-tag">Agregar</span>
                <h3>Agregar materia al semestre {{ semestreActivo }}</h3>
              </div>
              <button @click="mostrarModalAgregarMateria = false" class="btn-cerrar-modal" title="Cerrar">×</button>
            </div>
            <div class="modal-body form-body">
              <div class="form-grupo">
                <label>Nombre de la materia <span class="obligatorio">*</span></label>
                <input v-model="materiaNueva.nombre" type="text" class="modal-input" placeholder="Ej: Cálculo Diferencial" />
              </div>
              <div class="form-grupo">
                <label>Créditos <span class="obligatorio">*</span></label>
                <input v-model="materiaNueva.creditos" type="number" class="modal-input" placeholder="Ej: 5" min="1" />
              </div>
              <div class="form-grupo">
                <label>Tipo <span class="obligatorio">*</span></label>
                <select v-model="materiaNueva.tipo" class="modal-select">
                  <option value="">Seleccionar tipo</option>
                  <option value="Obligatoria">Obligatoria</option>
                  <option value="Optativa">Optativa</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn-secundario" @click="mostrarModalAgregarMateria = false">Cancelar</button>
              <button class="btn-guardar" @click="agregarMateria" :disabled="guardandoMateria">
                <span v-if="guardandoMateria" class="spinner-btn"></span>
                {{ guardandoMateria ? 'Agregando...' : 'Agregar' }}
              </button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const route = useRoute()
const API_URL = import.meta.env.VITE_API_URL

// ── Navegación interna ──────────────────────────────────────────────
const vista = ref('lista')
const planSeleccionado = ref(null)
const planEditar = ref({})

// ── Datos principales ───────────────────────────────────────────────
const planes = ref([])
const materias = ref([])
const prerrequisitos = ref([])
const cargando = ref(false)
const cargandoMaterias = ref(false)
const guardando = ref(false)
const guardandoMateria = ref(false)

// ── KPIs ────────────────────────────────────────────────────────────
const kpis = ref({ cargando: false, total: 0, activos: 0, en_revision: 0 })

const cargarKpis = async () => {
  kpis.value.cargando = true
  try {
    const res = await fetch(`${API_URL}/api/planes-curriculares/kpis`)
    if (res.ok) {
      const data = await res.json()
      Object.assign(kpis.value, data)
    }
  } catch {
    // Silencioso
  } finally {
    kpis.value.cargando = false
  }
}

// ── Catálogos ───────────────────────────────────────────────────────
const catalogos = ref({ carreras: [] })

const cargarCatalogos = async () => {
  try {
    const res = await fetch(`${API_URL}/api/carreras`)
    if (res.ok) {
      const data = await res.json()
      catalogos.value.carreras = data || []
    }
  } catch {
    // Silencioso
  }
}

// ── Notificaciones ──────────────────────────────────────────────────
const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

// ── Filtros y búsqueda ──────────────────────────────────────────────
const busquedaPlan = ref('')
const mostrarFiltros = ref(false)
const filtroCarrera = ref('')
const filtroVigencia = ref('')
const inputBusqueda = ref(null)

const filtrosActivos = computed(() => !!(filtroCarrera.value || filtroVigencia.value))
const contadorFiltros = computed(() => [filtroCarrera.value, filtroVigencia.value].filter(Boolean).length)

const normalize = (t) => !t ? '' : t.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')

const planesFiltrados = computed(() => {
  return planes.value.filter(plan => {
    const busq = !busquedaPlan.value || normalize(plan.nombre).includes(normalize(busquedaPlan.value))
    const filtCarrera = !filtroCarrera.value || plan.carrera_id == filtroCarrera.value
    let filtVigencia = true
    if (filtroVigencia.value === 'vigente') {
      const hoy = new Date().toISOString().split('T')[0]
      filtVigencia = plan.vigencia_inicio <= hoy && plan.vigencia_fin >= hoy
    } else if (filtroVigencia.value === 'vencido') {
      const hoy = new Date().toISOString().split('T')[0]
      filtVigencia = plan.vigencia_fin < hoy
    } else if (filtroVigencia.value === 'proximo') {
      const hoy = new Date()
      const fin = new Date(plan.vigencia_fin)
      const diff = Math.ceil((fin - hoy) / (1000 * 60 * 60 * 24))
      filtVigencia = diff >= 0 && diff <= 180
    }
    return busq && filtCarrera && filtVigencia
  })
})

// ── Paginación ──────────────────────────────────────────────────────
const currentPage = ref(1)
const filasPorPagina = ref(10)

const totalPages = computed(() => Math.ceil(planesFiltrados.value.length / filasPorPagina.value) || 1)

const paginatedPlanes = computed(() => {
  const start = (currentPage.value - 1) * filasPorPagina.value
  return planesFiltrados.value.slice(start, start + filasPorPagina.value)
})

const visiblePages = computed(() => {
  const total = totalPages.value
  const cur = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = new Set([1, total, cur, cur - 1, cur + 1].filter(p => p >= 1 && p <= total))
  return [...pages].sort((a, b) => a - b)
})

const goToPage = (p) => { currentPage.value = p }
const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

const resetFilters = () => {
  busquedaPlan.value = ''
  filtroCarrera.value = ''
  filtroVigencia.value = ''
  currentPage.value = 1
}

const limpiarBusqueda = () => {
  busquedaPlan.value = ''
  currentPage.value = 1
}

// ── Carga de planes ─────────────────────────────────────────────────
const cargarPlanes = async () => {
  cargando.value = true
  try {
    const params = new URLSearchParams()
    if (filtroCarrera.value) params.append('carrera', filtroCarrera.value)
    if (filtroVigencia.value) params.append('vigencia', filtroVigencia.value)
    const res = await fetch(`${API_URL}/api/planes-curriculares?${params.toString()}`)
    if (res.ok) {
      const data = await res.json()
      planes.value = data.planes || []
    }
  } catch {
    mostrarNotificacion('No se pudieron cargar los planes curriculares.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Clases de estatus ───────────────────────────────────────────────
const claseEstatus = (estatus) => {
  if (!estatus) return ''
  const e = estatus.toLowerCase()
  if (e === 'activo') return 'activo'
  if (e === 'en revisión' || e === 'en revision') return 'revision'
  if (e === 'inactivo') return 'inactivo'
  return ''
}

// ── Navegación a detalle ────────────────────────────────────────────
const verDetalle = async (plan) => {
  planSeleccionado.value = plan
  vista.value = 'detalle'
  await cargarMateriasYPrerrequisitos(plan.id)
}

const volverALista = () => {
  vista.value = 'lista'
  planSeleccionado.value = null
  materias.value = []
  prerrequisitos.value = []
  semestreActivo.value = 1
}

// ── Lógica de materias y semestres ──────────────────────────────────
const semestreActivo = ref(1)
const mostrarPrerrequisitos = ref(false)

const semestres = computed(() => {
  const nums = [...new Set(materias.value.map(m => m.semestre))].sort((a, b) => a - b)
  return nums.map(n => ({ numero: n }))
})

const materiasSemestreActivo = computed(() => {
  return materias.value.filter(m => m.semestre === semestreActivo.value)
})

const totalMaterias = computed(() => materias.value.length)

const todasLasMaterias = computed(() => {
  return materias.value.map(m => ({ id: m.id, nombre: m.nombre, semestre: m.semestre }))
})

const creditosPorSemestre = (num) => {
  return materias.value.filter(m => m.semestre === num).reduce((sum, m) => sum + (m.creditos || 0), 0)
}

const maxCreditosSemestre = computed(() => {
  return Math.max(...semestres.value.map(s => creditosPorSemestre(s.numero)), 1)
})

const cargarMateriasYPrerrequisitos = async (planId) => {
  cargandoMaterias.value = true
  try {
    const [resMaterias, resPrerreq] = await Promise.all([
      fetch(`${API_URL}/api/planes-curriculares/${planId}/materias`),
      fetch(`${API_URL}/api/planes-curriculares/${planId}/prerequisitos`)
    ])
    if (resMaterias.ok) {
      const data = await resMaterias.json()
      materias.value = data.materias || []
      if (materias.value.length > 0) {
        const nums = [...new Set(materias.value.map(m => m.semestre))].sort((a, b) => a - b)
        semestreActivo.value = nums[0] || 1
      }
    }
    if (resPrerreq.ok) {
      const data = await resPrerreq.json()
      prerrequisitos.value = data.prerequisitos || []
    }
  } catch {
    mostrarNotificacion('No se pudieron cargar las materias.', 'error')
  } finally {
    cargandoMaterias.value = false
  }
}

// ── Modal: Agregar materia ──────────────────────────────────────────
const mostrarModalAgregarMateria = ref(false)
const materiaNueva = ref({ nombre: '', creditos: '', tipo: '' })

const abrirModalAgregarMateria = () => {
  materiaNueva.value = { nombre: '', creditos: '', tipo: '' }
  mostrarModalAgregarMateria.value = true
}

const agregarMateria = async () => {
  if (!materiaNueva.value.nombre || !materiaNueva.value.creditos || !materiaNueva.value.tipo) {
    mostrarNotificacion('Completa todos los campos.', 'error')
    return
  }
  guardandoMateria.value = true
  try {
    const res = await fetch(`${API_URL}/api/planes-curriculares/${planSeleccionado.value.id}/materias`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        nombre: materiaNueva.value.nombre,
        creditos: parseInt(materiaNueva.value.creditos),
        tipo: materiaNueva.value.tipo,
        semestre: semestreActivo.value
      })
    })
    if (res.ok) {
      await cargarMateriasYPrerrequisitos(planSeleccionado.value.id)
      mostrarModalAgregarMateria.value = false
      mostrarNotificacion('Materia agregada correctamente.', 'exito')
    } else {
      const data = await res.json().catch(() => ({}))
      mostrarNotificacion(data.message || 'Error al agregar la materia.', 'error')
    }
  } catch {
    mostrarNotificacion('Error de conexión.', 'error')
  } finally {
    guardandoMateria.value = false
  }
}

const confirmarEliminarMateria = async (materia) => {
  if (!confirm(`¿Eliminar "${materia.nombre}"? Esta acción no se puede deshacer.`)) return
  try {
    const res = await fetch(`${API_URL}/api/planes-curriculares/${planSeleccionado.value.id}/materias/${materia.id}`, {
      method: 'DELETE'
    })
    if (res.ok) {
      await cargarMateriasYPrerrequisitos(planSeleccionado.value.id)
      mostrarNotificacion('Materia eliminada correctamente.', 'exito')
    } else {
      mostrarNotificacion('Error al eliminar la materia.', 'error')
    }
  } catch {
    mostrarNotificacion('Error de conexión.', 'error')
  }
}

// ── Prerrequisitos ──────────────────────────────────────────────────
const prerrequisitoNuevo = ref({ materia_origen_id: '', materia_prerequisito_id: '' })

const agregarPrerrequisito = async () => {
  if (!prerrequisitoNuevo.value.materia_origen_id || !prerrequisitoNuevo.value.materia_prerequisito_id) return
  try {
    const res = await fetch(`${API_URL}/api/planes-curriculares/${planSeleccionado.value.id}/prerequisitos`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        materia_origen_id: prerrequisitoNuevo.value.materia_origen_id,
        materia_prerequisito_id: prerrequisitoNuevo.value.materia_prerequisito_id
      })
    })
    if (res.ok) {
      await cargarMateriasYPrerrequisitos(planSeleccionado.value.id)
      prerrequisitoNuevo.value = { materia_origen_id: '', materia_prerequisito_id: '' }
      mostrarNotificacion('Prerrequisito agregado correctamente.', 'exito')
    } else {
      mostrarNotificacion('Error al agregar el prerrequisito.', 'error')
    }
  } catch {
    mostrarNotificacion('Error de conexión.', 'error')
  }
}

const eliminarPrerrequisito = async (id) => {
  if (!confirm('¿Eliminar esta dependencia?')) return
  try {
    const res = await fetch(`${API_URL}/api/planes-curriculares/${planSeleccionado.value.id}/prerequisitos/${id}`, {
      method: 'DELETE'
    })
    if (res.ok) {
      await cargarMateriasYPrerrequisitos(planSeleccionado.value.id)
      mostrarNotificacion('Prerrequisito eliminado.', 'exito')
    } else {
      mostrarNotificacion('Error al eliminar.', 'error')
    }
  } catch {
    mostrarNotificacion('Error de conexión.', 'error')
  }
}

// ── Modal: Formulario nuevo/editar plan ─────────────────────────────
const mostrarFormulario = ref(false)

const abrirFormularioNuevo = () => {
  planEditar.value = {
    id: null,
    carrera_id: '',
    nombre: '',
    vigencia_inicio: '',
    vigencia_fin: '',
    creditos_totales: '',
    estatus: ''
  }
  mostrarFormulario.value = true
}

const abrirFormularioEditar = (plan) => {
  planEditar.value = {
    id: plan.id,
    carrera_id: plan.carrera_id || '',
    nombre: plan.nombre || '',
    vigencia_inicio: plan.vigencia_inicio || '',
    vigencia_fin: plan.vigencia_fin || '',
    creditos_totales: plan.creditos_totales || '',
    estatus: plan.estatus || ''
  }
  mostrarFormulario.value = true
}

const cerrarFormulario = () => {
  mostrarFormulario.value = false
}

const guardarPlan = async () => {
  if (!planEditar.value.carrera_id || !planEditar.value.nombre || !planEditar.value.vigencia_inicio || !planEditar.value.vigencia_fin || !planEditar.value.creditos_totales || !planEditar.value.estatus) {
    mostrarNotificacion('Completa todos los campos obligatorios.', 'error')
    return
  }
  guardando.value = true
  try {
    const url = planEditar.value.id
      ? `${API_URL}/api/planes-curriculares/${planEditar.value.id}`
      : `${API_URL}/api/planes-curriculares`
    const method = planEditar.value.id ? 'PUT' : 'POST'

    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        carrera_id: planEditar.value.carrera_id,
        nombre: planEditar.value.nombre,
        vigencia_inicio: planEditar.value.vigencia_inicio,
        vigencia_fin: planEditar.value.vigencia_fin,
        creditos_totales: parseInt(planEditar.value.creditos_totales),
        estatus: planEditar.value.estatus
      })
    })
    if (res.ok) {
      await cargarPlanes()
      await cargarKpis()
      cerrarFormulario()
      mostrarNotificacion(planEditar.value.id ? 'Plan actualizado correctamente.' : 'Plan creado correctamente.', 'exito')
      if (planSeleccionado.value && planEditar.value.id === planSeleccionado.value.id) {
        planSeleccionado.value = { ...planSeleccionado.value, ...planEditar.value }
      }
    } else {
      const data = await res.json().catch(() => ({}))
      mostrarNotificacion(data.message || 'Error al guardar el plan.', 'error')
    }
  } catch {
    mostrarNotificacion('Error de conexión.', 'error')
  } finally {
    guardando.value = false
  }
}

// ── Ciclo de vida ───────────────────────────────────────────────────
onMounted(async () => {
  await Promise.all([cargarPlanes(), cargarCatalogos(), cargarKpis()])
})
</script>

<style scoped>
/* ========== Mismos estilos que Alumnos ========== */
.gestion-page {
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
  background: #F5F5F5;
  font-family: 'Montserrat', sans-serif;
}

/* Breadcrumb */
.breadcrumb { margin-bottom: 15px; font-size: 0.9rem; color: #6B7280; }
.breadcrumb-link { cursor: pointer; color: #1B396A; font-weight: 500; }
.breadcrumb-link:hover { text-decoration: underline; }
.breadcrumb-sep { margin: 0 8px; }
.breadcrumb-actual { color: #374151; }

/* Page header */
.page-header { margin-bottom: 25px; }
.page-title { font-size: 1.8rem; font-weight: 700; color: #111827; margin: 0; font-family: 'Montserrat', sans-serif; }
.page-subtitle { color: #6B7280; margin-top: 5px; font-size: 0.95rem; }

/* KPIs */
.kpis-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 24px; }
.kpis-detalle { grid-template-columns: repeat(3, 1fr); }
@media (max-width: 900px) { .kpis-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 600px) { .kpis-grid { grid-template-columns: 1fr; } }

.kpi-card {
  background: white;
  border-radius: 10px;
  border: 1px solid #E5E7EB;
  padding: 14px 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
  transition: box-shadow 0.2s;
}
.kpi-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.kpi-icon { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.kpi-info { display: flex; flex-direction: column; gap: 2px; }
.kpi-num { font-size: 1.4rem; font-weight: 700; line-height: 1; font-family: 'Montserrat', sans-serif; }
.kpi-label { font-size: 0.75rem; font-weight: 500; color: #6B7280; }

.kpi-total .kpi-icon { background: #EFF6FF; }
.kpi-total .kpi-icon svg { stroke: #1B396A; }
.kpi-total .kpi-num { color: #1B396A; }

.kpi-activos .kpi-icon { background: #DCFCE7; }
.kpi-activos .kpi-icon svg { stroke: #16A34A; }
.kpi-activos .kpi-num { color: #16A34A; }

.kpi-revision .kpi-icon { background: #FEF3C7; }
.kpi-revision .kpi-icon svg { stroke: #B45309; }
.kpi-revision .kpi-num { color: #B45309; }

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
.input-con-icono { position: relative; flex: 1; }
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
  transition: background 0.15s;
  font-size: 0.95rem;
}
.btn-buscar:hover:not(:disabled) { background: #1D4ED8; border-color: #1D4ED8; }
.btn-buscar:disabled { opacity: 0.65; cursor: not-allowed; }

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
.btn-filtro:hover { background: #F9FAFB; border-color: #9CA3AF; color: #111827; }
.btn-filtro.activo { border-color: #1B396A; color: #1B396A; background: #DBEAFE; }
.icono-btn { width: 18px; height: 18px; color: #6B7280; }
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
.btn-nuevo:hover { background: #1D4ED8; }
.btn-nuevo:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-secundario {
  padding: 9px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  background: #FFFFFF;
  color: #1B396A;
  border: 2px solid #1B396A;
  transition: background 0.15s;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 6px;
}
.btn-secundario:hover { background: #DBEAFE; }
.btn-secundario.activo { background: #DBEAFE; }

/* Filtros panel */
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
.filter-select:focus { border-color: #1B396A; }
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
.btn-limpiar:hover { color: #DC2626; }
.reset-icon { width: 16px; height: 16px; }

.filtros-slide-enter-active, .filtros-slide-leave-active {
  transition: all 0.25s ease;
  overflow: hidden;
}
.filtros-slide-enter-from, .filtros-slide-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* Tabla */
.tabla-contenedor {
  background: #FFFFFF;
  border-radius: 12px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  overflow: hidden;
  margin-top: 10px;
}
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
.data-table tbody tr { transition: background 0.15s; cursor: default; }
.data-table tbody tr:hover { background: #F8FAFC; }
.data-table tbody tr:last-child td { border-bottom: none; }

.celda-nombre { font-weight: 600; font-size: 0.9rem; }
.celda-secundaria { color: #6B7280; font-size: 0.85rem; }
.sin-prerequisitos { color: #9CA3AF; font-style: italic; }

.estatus-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.78rem;
  font-weight: 600;
  white-space: nowrap;
}
.estatus-badge.activo { background: #DCFCE7; color: #16A34A; }
.estatus-badge.revision { background: #FEF3C7; color: #B45309; }
.estatus-badge.inactivo { background: #FEE2E2; color: #DC2626; }

.tipo-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.78rem;
  font-weight: 600;
}
.tipo-obligatoria { background: #EFF6FF; color: #1B396A; }
.tipo-optativa { background: #EDE9FE; color: #7C3AED; }

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
  background: none;
}
.btn-icono svg { width: 15px; height: 15px; }
.btn-icono.ver { background: #F3F4F6; border-color: #D1D5DB; }
.btn-icono.ver:hover { background: #E5E7EB; }
.btn-icono.ver svg { stroke: #374151; }
.btn-icono.editar { background: #1B396A; border-color: #1B396A; }
.btn-icono.editar:hover { background: #1D4ED8; border-color: #1D4ED8; }
.btn-icono.editar svg { stroke: #FFFFFF; }
.btn-icono.eliminar-materia { background: #FEE2E2; border-color: #FECACA; }
.btn-icono.eliminar-materia:hover { background: #FECACA; }
.btn-icono.eliminar-materia svg { stroke: #DC2626; }

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
.estado-vacio h3 { font-size: 1.1rem; color: #1A1A1A; margin: 0 0 6px; }
.estado-vacio p { font-size: 0.9rem; margin: 0 0 1rem; }
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

/* Semestres tabs */
.semestres-tabs-contenedor {
  margin-bottom: 0;
}
.semestres-tabs {
  display: flex;
  gap: 4px;
  flex-wrap: wrap;
  margin-bottom: -1px;
  position: relative;
  z-index: 1;
}
.semestre-tab {
  padding: 10px 18px;
  background: #FFFFFF;
  border: 1px solid #E5E7EB;
  border-bottom: none;
  border-radius: 8px 8px 0 0;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  font-weight: 600;
  font-size: 0.88rem;
  color: #6B7280;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.15s;
}
.semestre-tab:hover { background: #F8FAFC; color: #1B396A; }
.semestre-tab.activo {
  background: #FFFFFF;
  color: #1B396A;
  border-color: #E5E7EB;
  border-bottom-color: #FFFFFF;
}
.semestre-creditos-badge {
  background: #EFF6FF;
  color: #1B396A;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 10px;
}

/* Barra acumulada de créditos */
.creditos-acumulados {
  margin-top: 24px;
  background: #FFFFFF;
  border-radius: 10px;
  border: 1px solid #E5E7EB;
  padding: 1.2rem 1.4rem;
}
.creditos-acumulados-titulo {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1A1A1A;
  margin: 0 0 1rem;
}
.creditos-barras {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.creditos-barra-item {
  display: flex;
  align-items: center;
  gap: 10px;
}
.creditos-barra-label {
  font-weight: 600;
  font-size: 0.82rem;
  color: #1B396A;
  width: 30px;
}
.creditos-barra-track {
  flex: 1;
  height: 14px;
  background: #F3F4F6;
  border-radius: 7px;
  overflow: hidden;
}
.creditos-barra-fill {
  height: 100%;
  background: #1B396A;
  border-radius: 7px;
  transition: width 0.6s ease;
}
.creditos-barra-num {
  font-weight: 600;
  font-size: 0.82rem;
  color: #6B7280;
  width: 25px;
  text-align: right;
}

/* Acciones bottom */
.detalle-acciones-bottom {
  display: flex;
  gap: 12px;
  margin-top: 24px;
  flex-wrap: wrap;
}

/* Prerrequisitos panel */
.prerrequisitos-panel {
  background: #FFFFFF;
  border-radius: 10px;
  border: 1px solid #E5E7EB;
  padding: 1.2rem 1.4rem;
  margin-top: 16px;
}
.prerrequisitos-titulo {
  font-size: 1rem;
  font-weight: 700;
  color: #1A1A1A;
  margin: 0 0 1rem;
}
.prerrequisitos-form {
  display: flex;
  gap: 12px;
  align-items: flex-end;
  flex-wrap: wrap;
}
.prerrequisitos-form .filtro-item {
  flex: 1;
  min-width: 200px;
}
.prerrequisitos-form .btn-nuevo {
  height: fit-content;
  align-self: flex-end;
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
  .busqueda-grupo { max-width: 100%; }
  .btn-nuevo { justify-content: center; }
  .filtros-grid { grid-template-columns: 1fr; }
  .paginacion { flex-direction: column; gap: 1rem; }
  .prerrequisitos-form { flex-direction: column; }
  .prerrequisitos-form .filtro-item { min-width: 100%; }
}
</style>

<style>
/* ========== ESTILOS GLOBALES PARA MODALES ========== */
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

.modal-header {
  background: #1B396A;
  color: white;
  padding: 1.2rem 1.6rem;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-shrink: 0;
}
.modal-header-info { display: flex; flex-direction: column; gap: 3px; }
.modal-header-tag { font-size: 0.72rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; opacity: 0.75; }
.modal-header h3 { margin: 0; font-size: 1.2rem; font-weight: 700; }

.btn-cerrar-modal {
  background: none;
  border: none;
  color: white;
  font-size: 1.8rem;
  cursor: pointer;
  line-height: 1;
  opacity: 0.8;
  padding: 0;
  flex-shrink: 0;
  transition: opacity 0.15s;
}
.btn-cerrar-modal:hover { opacity: 1; }

.form-body { padding: 1.5rem 1.6rem; overflow-y: auto; }
.form-grupo { margin-bottom: 1.2rem; }
.form-grupo-doble { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-grupo label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: #1A1A1A; }
.obligatorio { color: #DC2626; }
.modal-input, .modal-select {
  width: 100%;
  padding: 10px 14px;
  border: 1.5px solid #E5E7EB;
  border-radius: 8px;
  font-size: 0.95rem;
  background: #FFFFFF;
  color: #1A1A1A;
  font-family: 'Montserrat', sans-serif;
  outline: none;
  transition: border-color 0.2s;
  box-sizing: border-box;
}
.modal-input:focus, .modal-select:focus { border-color: #1B396A; box-shadow: 0 0 0 3px #DBEAFE; }

.modal-footer {
  padding: 1rem 1.6rem;
  background: #F8FAFC;
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  border-top: 1px solid #E5E7EB;
  flex-shrink: 0;
}
.btn-guardar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 9px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  background: #1B396A;
  color: white;
  border: 2px solid #1B396A;
  transition: background 0.15s;
  font-size: 0.9rem;
}
.btn-guardar:hover:not(:disabled) { background: #1D4ED8; }
.btn-guardar:disabled { opacity: 0.55; cursor: not-allowed; }

@media (max-width: 600px) {
  .form-grupo-doble { grid-template-columns: 1fr; }
  .modal-footer { flex-direction: column; }
  .modal-footer button { width: 100%; justify-content: center; }
}
</style>