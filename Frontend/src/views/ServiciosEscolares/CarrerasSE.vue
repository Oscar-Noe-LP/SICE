<template>
  <MainLayout>
    <div class="carreras-page">

      <!-- ═══════════════════════ BARRA DE CARGA GLOBAL ═══════════════════════ -->
      <div class="barra-carga-global" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- ═══════════════════════ NOTIFICACIÓN ═══════════════════════ -->
      <transition name="notif-fade">
        <div
          v-if="notificacion.visible"
          class="notificacion-ui"
          :class="notificacion.tipo"
          role="status"
          aria-live="polite"
        >
          <svg v-if="notificacion.tipo === 'exito'" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
          </svg>
          <svg v-else class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- ═══════════════════════ BREADCRUMB ═══════════════════════ -->
      <nav class="breadcrumb" aria-label="RUTA DE NAVEGACIÓN">
        <span class="breadcrumb-link" @click="$router.push('/configuracion')">CONFIGURACIÓN</span>
        <svg class="breadcrumb-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="breadcrumb-actual">CARRERAS</span>
      </nav>

      <!-- ═══════════════════════ HEADER ═══════════════════════ -->
      <div class="page-header">
        <div class="page-header-left">
          <h1 class="page-title">GESTIÓN DE CARRERAS</h1>
          <p class="page-subtitle">
            {{ carrerasFiltradas.length }} REGISTRO{{ carrerasFiltradas.length !== 1 ? 'S' : '' }}
            ENCONTRADO{{ carrerasFiltradas.length !== 1 ? 'S' : '' }}
            <template v-if="selectedCount > 0">
              · <span class="subtitle-sel">{{ selectedCount }} SELECCIONADO{{ selectedCount !== 1 ? 'S' : '' }}</span>
            </template>
          </p>
        </div>
        <div class="page-header-btns">
          <button
            class="btn-select-all"
            :class="{ activo: selectedCount > 0 }"
            @click="toggleSelectAll"
            :title="selectedCount === paginatedCarreras.length ? 'DESELECCIONAR TODOS' : 'SELECCIONAR PÁGINA'"
          >
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" stroke-width="2">
              <template v-if="selectedCount === paginatedCarreras.length && paginatedCarreras.length > 0">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
              </template>
              <template v-else>
                <rect x="3" y="3" width="18" height="18" rx="2"/>
              </template>
            </svg>
            {{ selectedCount > 0 ? `${selectedCount} SEL.` : 'SELECCIONAR' }}
          </button>
          <button class="btn-nuevo" @click="abrirModalNueva">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="17" height="17" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            NUEVA CARRERA
          </button>
        </div>
      </div>

      <!-- ═══════════════════════ KPIs ═══════════════════════ -->
      <div class="kpis-grid">
        <div class="kpi-card kpi-total">
          <div class="kpi-icon-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
            </svg>
          </div>
          <div class="kpi-data">
            <span class="kpi-numero">{{ kpiTotal }}</span>
            <span class="kpi-label">TOTAL CARRERAS</span>
          </div>
        </div>
        <div class="kpi-card kpi-activo">
          <div class="kpi-icon-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
            </svg>
          </div>
          <div class="kpi-data">
            <span class="kpi-numero">{{ kpiActivas }}</span>
            <span class="kpi-label">ACTIVAS</span>
          </div>
        </div>
        <div class="kpi-card kpi-ingenieria">
          <div class="kpi-icon-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
            </svg>
          </div>
          <div class="kpi-data">
            <span class="kpi-numero">{{ kpiIngenieria }}</span>
            <span class="kpi-label">INGENIERÍAS</span>
          </div>
        </div>
        <div class="kpi-card kpi-tsu">
          <div class="kpi-icon-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22" stroke-width="2">
              <rect x="2" y="3" width="20" height="14" rx="2"/><path stroke-linecap="round" d="M8 21h8M12 17v4"/>
            </svg>
          </div>
          <div class="kpi-data">
            <span class="kpi-numero">{{ kpiTsu }}</span>
            <span class="kpi-label">TSU</span>
          </div>
        </div>
        <div class="kpi-card kpi-depto">
          <div class="kpi-icon-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
          </div>
          <div class="kpi-data">
            <span class="kpi-numero">{{ kpiDepartamentos }}</span>
            <span class="kpi-label">DEPARTAMENTOS</span>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════ BARRA DE ACCIONES ═══════════════════════ -->
      <div class="barra-acciones">
        <div class="busqueda-grupo">
          <div class="input-con-icono">
            <svg class="icono-input" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="17" height="17" stroke-width="2">
              <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/>
            </svg>
            <input
              v-model="busqueda"
              type="text"
              class="input-busqueda"
              placeholder="BUSCAR POR NOMBRE O CLAVE..."
            />
            <button v-if="busqueda" class="btn-limpiar-input" @click="busqueda = ''">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" stroke-width="2.5">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
        </div>
        <button
          class="btn-filtro"
          :class="{ activo: filtrosActivos > 0, abierto: mostrarFiltros }"
          @click="mostrarFiltros = !mostrarFiltros"
        >
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
          </svg>
          FILTROS
          <span v-if="filtrosActivos > 0" class="filtros-badge">{{ filtrosActivos }}</span>
        </button>
      </div>

      <!-- ═══════════════════════ PANEL DE FILTROS ═══════════════════════ -->
      <transition name="filtros-slide">
        <div v-if="mostrarFiltros" class="filtros-panel">
          <div class="filtros-grid">
            <div class="filtro-item">
              <label class="filtro-label">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="13" height="13" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                </svg>
                DEPARTAMENTO
              </label>
              <select v-model="filtros.departamento" class="filter-select">
                <option value="">TODOS</option>
                <option v-for="d in catalogos.departamentos" :key="d.id_departamento" :value="d.id_departamento">
                  {{ d.nombre?.toUpperCase() }}
                </option>
              </select>
            </div>
            <div class="filtro-item">
              <label class="filtro-label">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="13" height="13" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                </svg>
                NIVEL
              </label>
              <select v-model="filtros.nivel" class="filter-select">
                <option value="">TODOS</option>
                <option v-for="n in catalogos.niveles" :key="n.id_nivel_carrera" :value="n.id_nivel_carrera">
                  {{ n.nombre?.toUpperCase() }}
                </option>
              </select>
            </div>
            <div class="filtro-item">
              <label class="filtro-label">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="13" height="13" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                </svg>
                ESTADO
              </label>
              <select v-model="filtros.estado" class="filter-select">
                <option value="">TODOS</option>
                <option value="activo">ACTIVO</option>
                <option value="inactivo">INACTIVO</option>
              </select>
            </div>
          </div>
          <div class="filtros-footer">
            <button class="btn-limpiar-filtros" @click="limpiarFiltros">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="13" height="13" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4l16 16M4 20L20 4"/>
              </svg>
              LIMPIAR FILTROS
            </button>
          </div>
        </div>
      </transition>

      <!-- ═══════════════════════ TABLA ═══════════════════════ -->
      <div class="tabla-wrapper" tabindex="0">
        <!-- Estado cargando -->
        <div v-if="cargando" class="estado-cargando">
          <div class="spinner-cards"></div>
          CARGANDO CARRERAS...
        </div>

        <!-- Estado vacío -->
        <div v-else-if="carrerasFiltradas.length === 0" class="estado-vacio">
          <svg class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zM12 14v7M3 9v7l9 5 9-5V9"/>
          </svg>
          <h3>SIN RESULTADOS</h3>
          <p>NO SE ENCONTRARON CARRERAS CON LOS FILTROS APLICADOS.</p>
          <button class="btn-limpiar-vacio" @click="limpiarFiltros">LIMPIAR FILTROS</button>
        </div>

        <!-- Tabla principal -->
        <table v-else class="tabla-carreras">
          <thead>
            <tr>
              <th class="col-check">
                <button
                  class="btn-select-all-table"
                  :class="{ activo: selectedCount > 0 }"
                  @click="toggleSelectAll"
                  title="SELECCIONAR PÁGINA"
                >
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" stroke-width="2.5">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path v-if="selectedCount === paginatedCarreras.length && paginatedCarreras.length > 0" stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
                  </svg>
                </button>
              </th>
              <th>CLAVE</th>
              <th>NOMBRE DE LA CARRERA</th>
              <th>DEPARTAMENTO</th>
              <th>NIVEL</th>
              <th class="col-estatus">ESTATUS</th>
              <th class="col-acciones">ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(carrera, index) in paginatedCarreras"
              :key="carrera.id_carrera"
              v-memo="[
                carrera.id_carrera,
                filaActiva === index,
                seleccionados.has(carrera.id_carrera),
                carrera.activo,
                carrera.nombre,
                carrera.clave,
                carrera.id_departamento,
                carrera.id_nivel_carrera,
              ]"
              class="fila-carrera"
              :class="{
                'fila-activa': filaActiva === index,
                'fila-seleccionada': seleccionados.has(carrera.id_carrera),
              }"
              @click="manejarClickFila(carrera, index)"
            >
              <!-- Checkbox -->
              <td class="col-check" @click.stop>
                <div
                  class="bulk-checkbox"
                  :class="{ checked: seleccionados.has(carrera.id_carrera) }"
                  @click="toggleSeleccion(carrera)"
                >
                  <svg v-if="seleccionados.has(carrera.id_carrera)" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="12" height="12" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                  </svg>
                </div>
              </td>

              <!-- Clave -->
              <td class="col-clave">{{ carrera.clave?.toUpperCase() || '—' }}</td>

              <!-- Nombre -->
              <td class="col-nombre">{{ carrera.nombre?.toUpperCase() || '—' }}</td>

              <!-- Departamento -->
              <td class="col-depto">{{ resolverDepartamento(carrera) }}</td>

              <!-- Nivel -->
              <td class="col-nivel">{{ resolverNivel(carrera) }}</td>

              <!-- Estatus -->
              <td class="col-estatus">
                <span class="estatus-badge" :class="carrera.activo ? 'badge-activo' : 'badge-inactivo'">
                  {{ carrera.activo ? 'ACTIVO' : 'INACTIVO' }}
                </span>
              </td>

              <!-- Acciones -->
              <td class="col-acciones" @click.stop>
                <button class="btn-fila-accion btn-ver" @click="abrirModalVer(carrera)" title="VER DETALLE">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                </button>
                <button class="btn-fila-accion btn-editar" @click="abrirModalEditar(carrera)" title="EDITAR">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </button>
                <button class="btn-fila-accion btn-eliminar-fila" @click="solicitarEliminarDirecto(carrera)" title="ELIMINAR">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ═══════════════════════ PAGINACIÓN ═══════════════════════ -->
      <div v-if="!cargando && carrerasFiltradas.length > 0" class="paginacion">
        <div class="paginacion-izquierda">
          <span class="pag-label">FILAS POR PÁGINA</span>
          <select v-model="filasPorPagina" class="select-filas" @change="paginaActual = 1">
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
        </div>
        <div class="paginacion-centro">
          {{ (paginaActual - 1) * filasPorPagina + 1 }}–{{ Math.min(paginaActual * filasPorPagina, carrerasFiltradas.length) }}
          DE {{ carrerasFiltradas.length }}
        </div>
        <div class="paginacion-derecha">
          <button class="btn-pag" :disabled="paginaActual === 1" @click="paginaActual = 1">«</button>
          <button class="btn-pag" :disabled="paginaActual === 1" @click="paginaActual--">‹</button>
          <template v-for="p in paginasVisibles" :key="p">
            <button
              v-if="p !== '...'"
              class="btn-pag"
              :class="{ activo: p === paginaActual }"
              @click="paginaActual = p"
            >{{ p }}</button>
            <span v-else class="btn-pag" style="border:none;cursor:default;color:#9CA3AF">…</span>
          </template>
          <button class="btn-pag" :disabled="paginaActual === totalPaginas" @click="paginaActual++">›</button>
          <button class="btn-pag" :disabled="paginaActual === totalPaginas" @click="paginaActual = totalPaginas">»</button>
        </div>
      </div>

      <!-- ═══════════════════════ BULK BAR ═══════════════════════ -->
      <transition name="bulk-slide">
        <div v-if="selectedCount > 0" class="bulk-bar">
          <div class="bulk-bar-inner">
            <div class="bulk-bar-left">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
              </svg>
              <span class="bulk-count">{{ selectedCount }}</span>
              <span>CARRERA{{ selectedCount !== 1 ? 'S' : '' }} SELECCIONADA{{ selectedCount !== 1 ? 'S' : '' }}</span>
            </div>
            <div class="bulk-bar-center">
              <select v-model="bulkAccion" class="bulk-select">
                <option value="">ACCIÓN MASIVA...</option>
                <option value="activar">ACTIVAR</option>
                <option value="desactivar">DESACTIVAR</option>
                <option value="eliminar">ELIMINAR</option>
              </select>
              <button
                class="bulk-btn-aplicar"
                :disabled="!bulkAccion"
                @click="mostrarConfirmBulk"
              >
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                APLICAR
              </button>
            </div>
            <div class="bulk-bar-right">
              <button class="bulk-btn-cancelar" @click="limpiarSeleccion">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" stroke-width="2.5">
                  <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                CANCELAR
              </button>
            </div>
          </div>
        </div>
      </transition>

    </div><!-- /carreras-page -->

    <!-- ════════════════════════════════════════════════════════════════
    MODAL VER DETALLE
    ════════════════════════════════════════════════════════════════ -->
    <Teleport to="body">
      <transition name="modal-fade">
        <div v-if="showModalVer" class="modal-overlay" @click.self="cerrarModalVer" role="dialog" aria-modal="true">
          <div class="modal-content modal-ver-carrera">
            <div class="modal-header">
              <div class="modal-header-info">
                <span class="modal-header-tag">DETALLE DE CARRERA</span>
                <h3>{{ carreraVer?.nombre?.toUpperCase() || 'CARRERA' }}</h3>
                <span class="sub-header-control">{{ carreraVer?.clave?.toUpperCase() }}</span>
              </div>
              <button @click="cerrarModalVer" class="btn-cerrar-modal" title="CERRAR">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>
            <div class="modal-body tab-contenido-scroll" v-if="carreraVer">
              <!-- Estatus badge centrado -->
              <div style="display:flex;justify-content:center;margin-bottom:1rem">
                <span class="estatus-badge estatus-badge-modal" :class="carreraVer.activo ? 'badge-activo' : 'badge-inactivo'">
                  {{ carreraVer.activo ? '● ACTIVO' : '● INACTIVO' }}
                </span>
              </div>
              <!-- Grid de datos -->
              <div class="exp-seccion">
                <div class="exp-seccion-titulo">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  INFORMACIÓN GENERAL
                </div>
                <div class="detalle-grid" style="padding:14px">
                  <div class="detalle-campo">
                    <span class="detalle-label">CLAVE</span>
                    <span class="detalle-valor mono-bold">{{ carreraVer.clave?.toUpperCase() || '—' }}</span>
                  </div>
                  <div class="detalle-campo">
                    <span class="detalle-label">NIVEL</span>
                    <span class="detalle-valor">{{ resolverNivel(carreraVer) }}</span>
                  </div>
                  <div class="detalle-campo full-width">
                    <span class="detalle-label">NOMBRE DE LA CARRERA</span>
                    <span class="detalle-valor">{{ carreraVer.nombre?.toUpperCase() || '—' }}</span>
                  </div>
                  <div class="detalle-campo full-width">
                    <span class="detalle-label">DEPARTAMENTO</span>
                    <span class="detalle-valor">{{ resolverDepartamento(carreraVer) }}</span>
                  </div>
                  <div v-if="carreraVer.descripcion" class="detalle-campo full-width">
                    <span class="detalle-label">DESCRIPCIÓN</span>
                    <span class="detalle-valor" style="white-space:pre-line;font-weight:500;font-size:0.87rem">{{ carreraVer.descripcion }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn-secundario" @click="cerrarModalVer">CERRAR</button>
              <button class="btn-accion-outline" @click="() => { cerrarModalVer(); abrirModalEditar(carreraVer) }">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                EDITAR
              </button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <!-- ════════════════════════════════════════════════════════════════
    MODAL NUEVA / EDITAR CARRERA
    ════════════════════════════════════════════════════════════════ -->
    <Teleport to="body">
      <transition name="modal-fade">
        <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal" role="dialog" aria-modal="true">
          <div class="modal-content">
            <div class="modal-header">
              <div class="modal-header-info">
                <span class="modal-header-tag">{{ carreraEditar.id_carrera ? 'EDICIÓN' : 'NUEVA CARRERA' }}</span>
                <h3>{{ carreraEditar.id_carrera ? 'EDITAR CARRERA' : 'NUEVA CARRERA' }}</h3>
              </div>
              <button @click="cerrarModal" class="btn-cerrar-modal" title="CERRAR">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>
            <div class="modal-body form-body">
              <!-- Clave -->
              <div class="form-grupo">
                <label>CLAVE <span class="obligatorio">*</span></label>
                <input
                  v-model="carreraEditar.clave"
                  type="text"
                  class="modal-input"
                  placeholder="EJ: ISIC-2010-224"
                  style="text-transform:uppercase"
                />
              </div>
              <!-- Nombre -->
              <div class="form-grupo">
                <label>NOMBRE DE LA CARRERA <span class="obligatorio">*</span></label>
                <input
                  v-model="carreraEditar.nombre"
                  type="text"
                  class="modal-input"
                  placeholder="NOMBRE COMPLETO DE LA CARRERA"
                  style="text-transform:uppercase"
                />
              </div>
              <!-- Departamento y Nivel -->
              <div class="form-grupo-doble">
                <div class="form-grupo">
                  <label>DEPARTAMENTO <span class="obligatorio">*</span></label>
                  <select v-model="carreraEditar.id_departamento" class="modal-select">
                    <option value="">SELECCIONAR DEPARTAMENTO</option>
                    <option v-for="d in catalogos.departamentos" :key="d.id_departamento" :value="Number(d.id_departamento)">
                      {{ d.nombre?.toUpperCase() }}
                    </option>
                  </select>
                </div>
                <div class="form-grupo">
                  <label>NIVEL <span class="obligatorio">*</span></label>
                  <select v-model="carreraEditar.id_nivel_carrera" class="modal-select">
                    <option value="">SELECCIONAR NIVEL</option>
                    <option v-for="n in catalogos.niveles" :key="n.id_nivel_carrera" :value="Number(n.id_nivel_carrera)">
                      {{ n.nombre?.toUpperCase() }}
                    </option>
                  </select>
                </div>
              </div>
              <!-- Descripción -->
              <div class="form-grupo">
                <label>DESCRIPCIÓN <span style="color:#9CA3AF;font-size:0.72rem">(OPCIONAL)</span></label>
                <textarea
                  v-model="carreraEditar.descripcion"
                  class="modal-input modal-textarea"
                  placeholder="DESCRIPCIÓN BREVE DE LA CARRERA..."
                  rows="3"
                ></textarea>
              </div>
              <!-- Estado -->
              <div class="form-grupo">
                <label>ESTADO</label>
                <div class="toggle-estado-wrap">
                  <button
                    type="button"
                    class="toggle-estado"
                    :class="{ activo: carreraEditar.activo }"
                    @click="carreraEditar.activo = !carreraEditar.activo"
                  >
                    <span class="toggle-knob"></span>
                  </button>
                  <span class="toggle-label">{{ carreraEditar.activo ? 'ACTIVO' : 'INACTIVO' }}</span>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">CANCELAR</button>
              <button v-if="carreraEditar.id_carrera" class="btn-eliminar" @click="solicitarEliminar" :disabled="guardando">ELIMINAR</button>
              <button class="btn-guardar" @click="guardarCambios" :disabled="guardando">
                <span v-if="guardando" class="spinner-btn"></span>
                {{ guardando ? 'GUARDANDO...' : (carreraEditar.id_carrera ? 'GUARDAR CAMBIOS' : 'CREAR CARRERA') }}
              </button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <!-- ════════════════════════════════════════════════════════════════
    MODAL CONFIRMAR ELIMINAR
    ════════════════════════════════════════════════════════════════ -->
    <Teleport to="body">
      <transition name="modal-fade">
        <div v-if="showModalEliminar" class="modal-overlay" @click.self="showModalEliminar = false" role="alertdialog" aria-modal="true">
          <div class="modal-content modal-confirmar">
            <div class="modal-header">
              <div class="modal-header-info">
                <span class="modal-header-tag">CONFIRMAR</span>
                <h3>ELIMINAR CARRERA</h3>
              </div>
              <button @click="showModalEliminar = false" class="btn-cerrar-modal">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>
            <div class="modal-body confirmar-body">
              <svg class="confirmar-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
              <p>
                ¿ESTÁS SEGURO DE ELIMINAR LA CARRERA
                <strong>{{ carreraEditar.nombre?.toUpperCase() }}</strong>?
                <br>ESTA ACCIÓN NO SE PUEDE DESHACER.
              </p>
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

    <!-- ════════════════════════════════════════════════════════════════
    MODAL CONFIRMAR ACCIÓN MASIVA
    ════════════════════════════════════════════════════════════════ -->
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
                  <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>
            <div class="modal-body confirmar-body">
              <svg v-if="bulkAccion === 'eliminar'" class="confirmar-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
              <svg v-else class="confirmar-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="stroke:#1B396A">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
              </svg>
              <p>
                SE APLICARÁ LA ACCIÓN <strong>{{ labelBulkAccion }}</strong> A
                <strong>{{ selectedCount }}</strong> CARRERA{{ selectedCount !== 1 ? 'S' : '' }}.
              </p>
              <div class="bulk-preview">
                <span v-for="id in [...seleccionados].slice(0, 8)" :key="id" class="bulk-id-chip">{{ id }}</span>
                <span v-if="selectedCount > 8" class="bulk-id-chip bulk-id-mas">+{{ selectedCount - 8 }} MÁS</span>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn-secundario" @click="showBulkConfirm = false">CANCELAR</button>
              <button
                :class="bulkAccion === 'eliminar' ? 'btn-eliminar' : 'btn-guardar'"
                @click="confirmarBulkAccion"
                :disabled="ejecutandoBulk"
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
import { ref, computed, watch, toRaw } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const router = useRouter()
const API_URL = import.meta.env.VITE_API_URL || ''

// ════════════════════════════════════════════════════════════════
// ESTADO REACTIVO
// ════════════════════════════════════════════════════════════════
const carreras      = ref([])
const cargando      = ref(false)
const guardando     = ref(false)
const ejecutandoBulk = ref(false)
const filaActiva    = ref(null)

// Catálogos
const catalogos = ref({
  departamentos: [],
  niveles:       [],
})

// Búsqueda y filtros
const busqueda       = ref('')
const mostrarFiltros = ref(false)
const filtros = ref({
  departamento: '',
  nivel:        '',
  estado:       '',
})

// Paginación
const paginaActual    = ref(1)
const filasPorPagina  = ref(20)

// Selección masiva
const seleccionados  = ref(new Set())
const bulkAccion     = ref('')
const showBulkConfirm = ref(false)

// Modales
const showModal        = ref(false)
const showModalEliminar = ref(false)
const showModalVer     = ref(false)
const carreraVer       = ref(null)

// Formulario de edición/creación
const carreraEditar = ref({
  id_carrera:       null,
  clave:            '',
  nombre:           '',
  id_departamento:  null,
  id_nivel_carrera: null,
  descripcion:      '',
  activo:           true,
})

// Notificación
const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let _notifTimer = null

// ════════════════════════════════════════════════════════════════
// COMPUTED
// ════════════════════════════════════════════════════════════════
const carrerasFiltradas = computed(() => {
  let lista = carreras.value

  if (busqueda.value.trim()) {
    const q = busqueda.value.trim().toLowerCase()
    lista = lista.filter(c =>
      c.nombre?.toLowerCase().includes(q) ||
      c.clave?.toLowerCase().includes(q)
    )
  }
  if (filtros.value.departamento) {
    lista = lista.filter(c => Number(c.id_departamento) === Number(filtros.value.departamento))
  }
  if (filtros.value.nivel) {
    lista = lista.filter(c => Number(c.id_nivel_carrera) === Number(filtros.value.nivel))
  }
  if (filtros.value.estado === 'activo')   lista = lista.filter(c => c.activo)
  if (filtros.value.estado === 'inactivo') lista = lista.filter(c => !c.activo)

  return lista
})

const totalPaginas = computed(() =>
  Math.max(1, Math.ceil(carrerasFiltradas.value.length / filasPorPagina.value))
)

const paginatedCarreras = computed(() => {
  const start = (paginaActual.value - 1) * filasPorPagina.value
  return carrerasFiltradas.value.slice(start, start + filasPorPagina.value)
})

const paginasVisibles = computed(() => {
  const total = totalPaginas.value
  const actual = paginaActual.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = []
  if (actual <= 4) {
    pages.push(1, 2, 3, 4, 5, '...', total)
  } else if (actual >= total - 3) {
    pages.push(1, '...', total - 4, total - 3, total - 2, total - 1, total)
  } else {
    pages.push(1, '...', actual - 1, actual, actual + 1, '...', total)
  }
  return pages
})

// KPIs — reactivos al array carreras
const kpiTotal        = computed(() => carreras.value.length)
const kpiActivas      = computed(() => carreras.value.filter(c => c.activo).length)
const kpiIngenieria   = computed(() => carreras.value.filter(c =>
  resolverNivel(c).toLowerCase().includes('ingeniería') ||
  resolverNivel(c).toLowerCase().includes('ingenieria')
).length)
const kpiTsu          = computed(() => carreras.value.filter(c =>
  resolverNivel(c).toLowerCase().includes('tsu') ||
  resolverNivel(c).toLowerCase().includes('técnico')
).length)
const kpiDepartamentos = computed(() => {
  const ids = new Set(carreras.value.map(c => c.id_departamento).filter(Boolean))
  return ids.size
})

// Filtros activos (badge)
const filtrosActivos = computed(() =>
  [filtros.value.departamento, filtros.value.nivel, filtros.value.estado].filter(Boolean).length
)

// Selección
const selectedCount = computed(() => seleccionados.value.size)

// Label bulk
const labelBulkAccion = computed(() => {
  if (bulkAccion.value === 'activar')    return 'ACTIVAR CARRERAS'
  if (bulkAccion.value === 'desactivar') return 'DESACTIVAR CARRERAS'
  if (bulkAccion.value === 'eliminar')   return 'ELIMINAR CARRERAS'
  return ''
})

// ════════════════════════════════════════════════════════════════
// WATCHES
// ════════════════════════════════════════════════════════════════
watch([busqueda, filtros], () => { paginaActual.value = 1 }, { deep: true })

// ════════════════════════════════════════════════════════════════
// HELPERS
// ════════════════════════════════════════════════════════════════
const resolverDepartamento = (carrera) => {
  if (carrera.departamento?.nombre) return carrera.departamento.nombre.toUpperCase()
  const d = catalogos.value.departamentos.find(
    dep => Number(dep.id_departamento) === Number(carrera.id_departamento)
  )
  return d?.nombre?.toUpperCase() || '—'
}

const resolverNivel = (carrera) => {
  if (carrera.nivel_carrera?.nombre) return carrera.nivel_carrera.nombre.toUpperCase()
  if (carrera.nivel?.nombre)         return carrera.nivel.nombre.toUpperCase()
  const n = catalogos.value.niveles.find(
    niv => Number(niv.id_nivel_carrera) === Number(carrera.id_nivel_carrera)
  )
  return n?.nombre?.toUpperCase() || '—'
}

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (_notifTimer) clearTimeout(_notifTimer)
  notificacion.value = { visible: true, mensaje, tipo }
  _notifTimer = setTimeout(() => { notificacion.value.visible = false }, 4000)
}

// ════════════════════════════════════════════════════════════════
// CARGA DE DATOS
// ════════════════════════════════════════════════════════════════
const cargarCarreras = async () => {
  cargando.value = true
  try {
    const res  = await fetch(`${API_URL}/api/carreras`)
    if (!res.ok) throw new Error()
    const data = await res.json()
    carreras.value = Array.isArray(data) ? data : (data.data ?? [])
  } catch {
    mostrarNotificacion('NO SE PUDO CARGAR LA LISTA DE CARRERAS.', 'error')
  } finally {
    cargando.value = false
  }
}

const cargarCatalogos = async () => {
  try {
    const [resDep, resNiv] = await Promise.all([
      fetch(`${API_URL}/api/departamentos`),
      fetch(`${API_URL}/api/niveles-carrera`),
    ])
    if (resDep.ok) {
      const d = await resDep.json()
      catalogos.value.departamentos = Array.isArray(d) ? d : (d.data ?? [])
    }
    if (resNiv.ok) {
      const n = await resNiv.json()
      catalogos.value.niveles = Array.isArray(n) ? n : (n.data ?? [])
    }
  } catch {
    mostrarNotificacion('NO SE PUDIERON CARGAR LOS CATÁLOGOS.', 'error')
  }
}

// ════════════════════════════════════════════════════════════════
// ACTUALIZACIÓN REACTIVA INMEDIATA (sin refresh)
// ════════════════════════════════════════════════════════════════
const actualizarCarreraEnLista = (datosActualizados) => {
  const idBuscado = Number(datosActualizados.id_carrera)
  if (!idBuscado) return

  const index = carreras.value.findIndex(
    c => Number(c.id_carrera) === idBuscado
  )
  if (index === -1) return

  const original = toRaw(carreras.value[index])

  // Resolver objetos relacionados para que los helpers funcionen sin catálogos
  const deptoEnCatalogo  = catalogos.value.departamentos.find(
    d => Number(d.id_departamento) === Number(datosActualizados.id_departamento)
  )
  const nivelEnCatalogo  = catalogos.value.niveles.find(
    n => Number(n.id_nivel_carrera) === Number(datosActualizados.id_nivel_carrera)
  )

  const carreraActualizada = {
    ...original,
    clave:            datosActualizados.clave            ?? original.clave,
    nombre:           datosActualizados.nombre           ?? original.nombre,
    id_departamento:  datosActualizados.id_departamento  ?? original.id_departamento,
    id_nivel_carrera: datosActualizados.id_nivel_carrera ?? original.id_nivel_carrera,
    descripcion:      datosActualizados.descripcion      ?? original.descripcion,
    activo:           datosActualizados.activo            ?? original.activo,
    departamento: {
      ...(original.departamento ?? {}),
      id_departamento: datosActualizados.id_departamento,
      nombre: deptoEnCatalogo?.nombre ?? original.departamento?.nombre,
    },
    nivel_carrera: {
      ...(original.nivel_carrera ?? {}),
      id_nivel_carrera: datosActualizados.id_nivel_carrera,
      nombre: nivelEnCatalogo?.nombre ?? original.nivel_carrera?.nombre,
    },
  }

  // splice + nueva referencia para que v-memo invalide el caché
  carreras.value.splice(index, 1, carreraActualizada)
  carreras.value = [...carreras.value]

  // Si el modal de detalle está abierto con esta carrera, sincronizarlo
  if (carreraVer.value && Number(carreraVer.value.id_carrera) === idBuscado) {
    carreraVer.value = { ...carreraActualizada }
  }
}

const agregarCarreraEnLista = (nuevaCarrera) => {
  const deptoEnCatalogo = catalogos.value.departamentos.find(
    d => Number(d.id_departamento) === Number(nuevaCarrera.id_departamento)
  )
  const nivelEnCatalogo = catalogos.value.niveles.find(
    n => Number(n.id_nivel_carrera) === Number(nuevaCarrera.id_nivel_carrera)
  )
  const carreraCompleta = {
    ...nuevaCarrera,
    departamento:  { id_departamento: nuevaCarrera.id_departamento, nombre: deptoEnCatalogo?.nombre ?? '' },
    nivel_carrera: { id_nivel_carrera: nuevaCarrera.id_nivel_carrera, nombre: nivelEnCatalogo?.nombre ?? '' },
  }
  carreras.value = [carreraCompleta, ...carreras.value]
}

const eliminarCarreraDeLista = (id) => {
  const idNum = Number(id)
  carreras.value = carreras.value.filter(c => Number(c.id_carrera) !== idNum)
}

// ════════════════════════════════════════════════════════════════
// MODALES — ABRIR / CERRAR
// ════════════════════════════════════════════════════════════════
const abrirModalVer = (carrera) => {
  carreraVer.value = carrera
  showModalVer.value = true
}
const cerrarModalVer = () => { showModalVer.value = false }

const resetearFormulario = () => {
  carreraEditar.value = {
    id_carrera:       null,
    clave:            '',
    nombre:           '',
    id_departamento:  null,
    id_nivel_carrera: null,
    descripcion:      '',
    activo:           true,
  }
}

const abrirModalNueva = () => {
  resetearFormulario()
  showModal.value = true
}

const abrirModalEditar = (carrera) => {
  carreraEditar.value = {
    id_carrera:       Number(carrera.id_carrera),
    clave:            carrera.clave || '',
    nombre:           carrera.nombre || '',
    id_departamento:  Number(carrera.id_departamento) || null,
    id_nivel_carrera: Number(carrera.id_nivel_carrera) || null,
    descripcion:      carrera.descripcion || '',
    activo:           carrera.activo ?? true,
  }
  showModal.value = true
}

const cerrarModal = () => { showModal.value = false }

// ════════════════════════════════════════════════════════════════
// CRUD — GUARDAR (CREAR / EDITAR)
// ════════════════════════════════════════════════════════════════
const guardarCambios = async () => {
  const { id_carrera, clave, nombre, id_departamento, id_nivel_carrera } = carreraEditar.value

  if (!clave.trim())          { mostrarNotificacion('LA CLAVE ES OBLIGATORIA.', 'error');               return }
  if (!nombre.trim())         { mostrarNotificacion('EL NOMBRE ES OBLIGATORIO.', 'error');              return }
  if (!id_departamento)       { mostrarNotificacion('SELECCIONA UN DEPARTAMENTO.', 'error');            return }
  if (!id_nivel_carrera)      { mostrarNotificacion('SELECCIONA UN NIVEL.', 'error');                   return }

  guardando.value = true

  const payload = {
    clave:            clave.trim().toUpperCase(),
    nombre:           nombre.trim().toUpperCase(),
    id_departamento:  Number(id_departamento),
    id_nivel_carrera: Number(id_nivel_carrera),
    descripcion:      carreraEditar.value.descripcion?.trim() || null,
    activo:           carreraEditar.value.activo,
  }

  const esEdicion = Boolean(id_carrera)
  const url    = esEdicion ? `${API_URL}/api/carreras/${id_carrera}` : `${API_URL}/api/carreras`
  const method = esEdicion ? 'PUT' : 'POST'

  try {
    const res  = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    })
    const data = await res.json().catch(() => ({}))

    if (res.ok) {
      if (esEdicion) {
        actualizarCarreraEnLista({ id_carrera, ...payload })
        mostrarNotificacion('CARRERA ACTUALIZADA CORRECTAMENTE.', 'exito')
      } else {
        const nuevaId = data.id_carrera ?? data.id ?? data.data?.id_carrera
        agregarCarreraEnLista({ id_carrera: nuevaId, ...payload })
        mostrarNotificacion('CARRERA CREADA CORRECTAMENTE.', 'exito')
      }
      cerrarModal()
    } else {
      mostrarNotificacion(data.message || 'NO SE PUDO GUARDAR LA CARRERA.', 'error')
    }
  } catch {
    mostrarNotificacion('ERROR AL GUARDAR. INTENTA DE NUEVO.', 'error')
  } finally {
    guardando.value = false
  }
}

// ════════════════════════════════════════════════════════════════
// CRUD — ELIMINAR
// ════════════════════════════════════════════════════════════════
const solicitarEliminar = () => {
  showModal.value = false
  showModalEliminar.value = true
}

const solicitarEliminarDirecto = (carrera) => {
  carreraEditar.value = { ...carrera, id_carrera: Number(carrera.id_carrera) }
  showModalEliminar.value = true
}

const confirmarEliminar = async () => {
  const id = carreraEditar.value.id_carrera
  if (!id) return
  guardando.value = true
  try {
    const res = await fetch(`${API_URL}/api/carreras/${id}`, {
      method: 'DELETE',
      headers: { 'Accept': 'application/json' },
    })
    if (res.ok || res.status === 204) {
      eliminarCarreraDeLista(id)
      showModalEliminar.value = false
      limpiarSeleccion()
      mostrarNotificacion('CARRERA ELIMINADA CORRECTAMENTE.', 'exito')
    } else {
      const data = await res.json().catch(() => ({}))
      mostrarNotificacion(data.message || 'NO SE PUDO ELIMINAR LA CARRERA.', 'error')
    }
  } catch {
    mostrarNotificacion('ERROR AL ELIMINAR. INTENTA DE NUEVO.', 'error')
  } finally {
    guardando.value = false
  }
}

// ════════════════════════════════════════════════════════════════
// SELECCIÓN Y BULK ACTIONS
// ════════════════════════════════════════════════════════════════
const toggleSeleccion = (carrera) => {
  const id = Number(carrera.id_carrera)
  const nuevo = new Set(seleccionados.value)
  nuevo.has(id) ? nuevo.delete(id) : nuevo.add(id)
  seleccionados.value = nuevo
}

const toggleSelectAll = () => {
  if (selectedCount.value === paginatedCarreras.value.length) {
    limpiarSeleccion()
  } else {
    seleccionados.value = new Set(paginatedCarreras.value.map(c => Number(c.id_carrera)))
  }
}

const limpiarSeleccion = () => {
  seleccionados.value = new Set()
  bulkAccion.value = ''
}

const mostrarConfirmBulk = () => {
  if (!bulkAccion.value) return
  showBulkConfirm.value = true
}

const confirmarBulkAccion = async () => {
  ejecutandoBulk.value = true
  const ids = [...seleccionados.value]

  try {
    if (bulkAccion.value === 'eliminar') {
      await Promise.all(ids.map(id =>
        fetch(`${API_URL}/api/carreras/${id}`, { method: 'DELETE' })
      ))
      ids.forEach(id => eliminarCarreraDeLista(id))
      mostrarNotificacion(`${ids.length} CARRERA${ids.length !== 1 ? 'S' : ''} ELIMINADA${ids.length !== 1 ? 'S' : ''}.`, 'exito')
    } else {
      const nuevoActivo = bulkAccion.value === 'activar'
      await Promise.all(ids.map(id => {
        const c = carreras.value.find(x => Number(x.id_carrera) === id)
        if (!c) return
        return fetch(`${API_URL}/api/carreras/${id}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ ...c, activo: nuevoActivo }),
        })
      }))
      ids.forEach(id => actualizarCarreraEnLista({ id_carrera: id, activo: nuevoActivo }))
      const accionLabel = nuevoActivo ? 'ACTIVADAS' : 'DESACTIVADAS'
      mostrarNotificacion(`${ids.length} CARRERA${ids.length !== 1 ? 'S' : ''} ${accionLabel}.`, 'exito')
    }
  } catch {
    mostrarNotificacion('ERROR AL EJECUTAR LA ACCIÓN MASIVA.', 'error')
  } finally {
    ejecutandoBulk.value = false
    showBulkConfirm.value = false
    limpiarSeleccion()
  }
}

// ════════════════════════════════════════════════════════════════
// NAVEGACIÓN DE FILAS
// ════════════════════════════════════════════════════════════════
const manejarClickFila = (carrera, index) => {
  if (selectedCount.value > 0) {
    toggleSeleccion(carrera)
  } else {
    filaActiva.value = index
    abrirModalVer(carrera)
  }
}

// ════════════════════════════════════════════════════════════════
// FILTROS
// ════════════════════════════════════════════════════════════════
const limpiarFiltros = () => {
  filtros.value = { departamento: '', nivel: '', estado: '' }
  busqueda.value = ''
  paginaActual.value = 1
}

// ════════════════════════════════════════════════════════════════
// INICIALIZACIÓN
// ════════════════════════════════════════════════════════════════
;(async () => {
  await Promise.all([cargarCarreras(), cargarCatalogos()])
})()
</script>

<style scoped>
/* ══════════════════════════════════════════════════════
BASE — idéntica a AlumnosSE
══════════════════════════════════════════════════════ */
.carreras-page {
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

/* ══════════════════════════════════════════════════════
BREADCRUMB
══════════════════════════════════════════════════════ */
.breadcrumb {
  display: flex; align-items: center; gap: 6px;
  margin-bottom: 16px; font-size: 0.78rem;
  font-weight: 600; letter-spacing: 0.05em; color: #9CA3AF;
}
.breadcrumb-link { cursor: pointer; color: #1B396A; transition: opacity 0.15s; }
.breadcrumb-link:hover { opacity: 0.75; text-decoration: underline; }
.breadcrumb-chevron { stroke: #D1D5DB; flex-shrink: 0; }
.breadcrumb-actual  { color: #374151; cursor: default; }

/* ══════════════════════════════════════════════════════
HEADER
══════════════════════════════════════════════════════ */
.page-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  gap: 16px; margin-bottom: 20px; flex-wrap: wrap; width: 100%;
}
.page-header-left { display: flex; flex-direction: column; gap: 4px; }
.page-header-btns { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.page-title    { font-size: 1.6rem; font-weight: 800; color: #0F172A; letter-spacing: -0.01em; margin: 0; }
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
KPIs
══════════════════════════════════════════════════════ */
.kpis-grid {
  display: grid; grid-template-columns: repeat(5, 1fr);
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
.kpi-card::before {
  content: ''; position: absolute; top: 0; left: 0;
  width: 4px; height: 100%; border-radius: 12px 0 0 12px;
}
.kpi-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.1); transform: translateY(-1px); }

.kpi-total::before      { background: #1B396A; }
.kpi-activo::before     { background: #16A34A; }
.kpi-ingenieria::before { background: #0369A1; }
.kpi-tsu::before        { background: #D97706; }
.kpi-depto::before      { background: #7C3AED; }

.kpi-icon-wrap {
  display: flex; align-items: center; justify-content: center;
  width: 36px; height: 36px; border-radius: 8px; flex-shrink: 0;
}
.kpi-total      .kpi-icon-wrap { background: #EFF6FF; color: #1B396A; }
.kpi-activo     .kpi-icon-wrap { background: #F0FDF4; color: #16A34A; }
.kpi-ingenieria .kpi-icon-wrap { background: #E0F2FE; color: #0369A1; }
.kpi-tsu        .kpi-icon-wrap { background: #FFFBEB; color: #D97706; }
.kpi-depto      .kpi-icon-wrap { background: #F5F3FF; color: #7C3AED; }

.kpi-data   { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.kpi-numero { font-size: 1.5rem; font-weight: 800; color: #0F172A; line-height: 1; letter-spacing: -0.02em; }
.kpi-label  { font-size: 0.65rem; font-weight: 700; color: #6B7280; letter-spacing: 0.06em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ══════════════════════════════════════════════════════
BARRA DE ACCIONES
══════════════════════════════════════════════════════ */
.barra-acciones {
  display: flex; align-items: center; gap: 12px;
  margin-bottom: 14px; flex-wrap: wrap; width: 100%;
}
.busqueda-grupo { flex: 1; min-width: 260px; }
.input-con-icono { position: relative; width: 100%; }
.icono-input {
  position: absolute; left: 13px; top: 50%;
  transform: translateY(-50%); color: #9CA3AF; pointer-events: none;
}
.input-busqueda {
  width: 100%; padding: 11px 40px 11px 42px;
  border: 1.5px solid #D1D5DB; border-radius: 9px;
  font-family: 'Montserrat', sans-serif; font-size: 0.85rem;
  font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase;
  transition: border-color .2s, box-shadow .2s; box-sizing: border-box; background: #fff; color: #111827;
}
.input-busqueda::placeholder { color: #9CA3AF; font-weight: 600; }
.input-busqueda:focus { outline: none; border-color: #1B396A; box-shadow: 0 0 0 3px rgba(27,57,106,.12); }
.btn-limpiar-input {
  position: absolute; right: 11px; top: 50%; transform: translateY(-50%);
  background: none; border: none; color: #9CA3AF; cursor: pointer;
  display: flex; align-items: center; padding: 4px; border-radius: 4px; transition: color .15s;
}
.btn-limpiar-input:hover { color: #374151; }

.btn-filtro {
  display: flex; align-items: center; gap: 8px; padding: 11px 18px;
  border: 1.5px solid #D1D5DB; border-radius: 9px; background: #fff;
  font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 0.82rem;
  letter-spacing: 0.06em; color: #374151; cursor: pointer; transition: all .18s;
  position: relative; white-space: nowrap;
}
.btn-filtro:hover   { border-color: #1B396A; color: #1B396A; }
.btn-filtro.activo  { border-color: #1B396A; color: #1B396A; background: #EFF6FF; }
.btn-filtro.abierto { background: #1B396A; color: #fff; border-color: #1B396A; }
.filtros-badge {
  display: inline-flex; align-items: center; justify-content: center;
  width: 18px; height: 18px; border-radius: 50%;
  background: #DC2626; color: #fff; font-size: 0.68rem; font-weight: 800;
}

/* ══════════════════════════════════════════════════════
PANEL DE FILTROS
══════════════════════════════════════════════════════ */
.filtros-panel {
  background: #fff; border: 1.5px solid #E5E7EB; border-radius: 12px;
  padding: 16px 20px 14px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,.06);
}
.filtros-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.filtro-item  { display: flex; flex-direction: column; gap: 6px; }
.filtro-label {
  display: flex; align-items: center; gap: 5px;
  font-size: 0.72rem; font-weight: 700; color: #6B7280; letter-spacing: 0.06em;
}
.filter-select {
  padding: 9px 12px; border: 1.5px solid #D1D5DB; border-radius: 8px;
  font-family: 'Montserrat', sans-serif; font-size: 0.82rem; font-weight: 600;
  color: #111827; background: #F9FAFB; outline: none; cursor: pointer;
  transition: border-color .18s, box-shadow .18s;
}
.filter-select:focus { border-color: #1B396A; box-shadow: 0 0 0 3px rgba(27,57,106,.1); background: #fff; }
.filtros-footer {
  display: flex; justify-content: flex-end;
  margin-top: 12px; padding-top: 10px; border-top: 1px solid #F3F4F6;
}
.btn-limpiar-filtros {
  display: flex; align-items: center; gap: 7px; padding: 7px 14px;
  background: none; border: 1.5px solid #D1D5DB; border-radius: 7px;
  font-family: 'Montserrat', sans-serif; font-size: 0.78rem; font-weight: 700;
  letter-spacing: 0.04em; color: #6B7280; cursor: pointer; transition: all .15s;
}
.btn-limpiar-filtros:hover { border-color: #DC2626; color: #DC2626; }
.filtros-slide-enter-active,.filtros-slide-leave-active{transition:all .25s cubic-bezier(.4,0,.2,1)}
.filtros-slide-enter-from,.filtros-slide-leave-to{opacity:0;transform:translateY(-10px)}

/* ══════════════════════════════════════════════════════
ESTADOS CARGANDO / VACÍO
══════════════════════════════════════════════════════ */
.estado-cargando {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 60px 20px; gap: 16px; color: #6B7280; font-weight: 600;
  font-size: 0.85rem; letter-spacing: 0.05em;
}
.spinner-cards { width: 32px; height: 32px; border: 3px solid #E5E7EB; border-top-color: #1B396A; border-radius: 50%; animation: spin .8s linear infinite; }
@keyframes spin { to{ transform: rotate(360deg) } }
.estado-vacio {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 60px 20px; gap: 12px; text-align: center;
}
.icono-vacio { width: 48px; height: 48px; stroke: #D1D5DB; }
.estado-vacio h3 { font-size: 1rem; font-weight: 800; color: #374151; letter-spacing: 0.05em; margin: 0; }
.estado-vacio p  { font-size: 0.82rem; color: #9CA3AF; font-weight: 600; margin: 0; letter-spacing: 0.03em; }
.btn-limpiar-vacio {
  margin-top: 8px; padding: 9px 22px; border-radius: 8px;
  border: 1.5px solid #1B396A; background: none; color: #1B396A;
  font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 0.82rem;
  letter-spacing: 0.05em; cursor: pointer; transition: background .15s;
}
.btn-limpiar-vacio:hover { background: #EFF6FF; }

/* ══════════════════════════════════════════════════════
TABLA
══════════════════════════════════════════════════════ */
.tabla-wrapper {
  overflow-x: auto; border-radius: 12px; background: #fff;
  border: 1px solid #E5E7EB; box-shadow: 0 1px 4px rgba(0,0,0,.05);
  margin-bottom: 24px; outline: none;
}
.tabla-carreras {
  width: 100%; border-collapse: collapse; font-family: 'Montserrat', sans-serif;
  font-size: 0.85rem; min-width: 720px;
}
.tabla-carreras th,
.tabla-carreras td {
  padding: 10px 12px; text-align: left; border-bottom: 1px solid #F3F4F6;
  vertical-align: middle; white-space: nowrap;
}
.tabla-carreras th {
  background: #F8FAFC; color: #374151; font-weight: 700;
  letter-spacing: 0.04em; text-transform: uppercase; font-size: 0.75rem;
  position: sticky; top: 0; z-index: 2;
}
.fila-carrera { transition: background 0.15s; cursor: pointer; }
.fila-carrera:hover { background: #F9FAFB; }
.fila-activa      { outline: 2px solid #1B396A; outline-offset: -2px; background: #EFF6FF; }
.fila-seleccionada { background: #EFF6FF; }

.col-check { width: 50px; text-align: center; padding: 10px 8px !important; }
.btn-select-all-table {
  background: none; border: none; cursor: pointer; color: #6B7280;
  display: inline-flex; align-items: center; justify-content: center;
  padding: 4px; border-radius: 4px; transition: color 0.15s;
}
.btn-select-all-table:hover, .btn-select-all-table.activo { color: #1B396A; }

.col-clave  { font-family: 'Courier New', monospace; font-weight: 700; color: #1B396A; font-size: 0.82rem; }
.col-nombre { font-weight: 700; color: #0F172A; max-width: 300px; overflow: hidden; text-overflow: ellipsis; }
.col-depto  { color: #6B7280; font-size: 0.83rem; }
.col-nivel  { color: #374151; font-weight: 600; font-size: 0.83rem; }
.col-estatus { text-align: center; }
.col-acciones { width: 140px; text-align: right; }

.bulk-checkbox {
  display: inline-flex; align-items: center; justify-content: center;
  width: 20px; height: 20px; border-radius: 5px; border: 2px solid #D1D5DB; background: #fff;
  cursor: pointer; transition: all .15s;
}
.bulk-checkbox:hover { border-color: #1B396A; }
.bulk-checkbox.checked { background: #1B396A; border-color: #1B396A; }
.bulk-checkbox.checked svg { stroke: #fff; }

/* Badges */
.estatus-badge {
  display: inline-flex; align-items: center; padding: 3px 8px;
  border-radius: 20px; font-size: 0.65rem; font-weight: 800;
  letter-spacing: 0.05em; width: fit-content;
}
.badge-activo   { background: #DCFCE7; color: #15803D; }
.badge-inactivo { background: #F3F4F6; color: #6B7280; }

/* Botones de fila */
.btn-fila-accion {
  display: inline-flex; align-items: center; justify-content: center;
  width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid transparent;
  cursor: pointer; transition: all .15s; background: #F8FAFC; margin-left: 4px;
}
.btn-ver:hover          { background: #EFF6FF; border-color: #BFDBFE; color: #1B396A; }
.btn-editar:hover       { background: #FFFBEB; border-color: #FDE68A; color: #D97706; }
.btn-eliminar-fila:hover{ background: #FEF2F2; border-color: #FCA5A5; color: #DC2626; }
.btn-ver    { color: #1B396A; }
.btn-editar { color: #D97706; }
.btn-eliminar-fila { color: #DC2626; }

/* ══════════════════════════════════════════════════════
PAGINACIÓN
══════════════════════════════════════════════════════ */
.paginacion {
  display: flex; align-items: center; justify-content: space-between;
  gap: 12px; flex-wrap: wrap; padding: 14px 16px; background: #fff;
  border: 1.5px solid #E5E7EB; border-radius: 12px;
  box-shadow: 0 1px 4px rgba(0,0,0,.05); width: 100%; box-sizing: border-box;
}
.paginacion-izquierda { display: flex; align-items: center; gap: 8px; }
.pag-label   { font-size: 0.75rem; font-weight: 700; color: #6B7280; letter-spacing: 0.04em; }
.select-filas {
  padding: 5px 10px; border: 1.5px solid #E5E7EB; border-radius: 7px;
  font-family: 'Montserrat', sans-serif; font-size: 0.8rem; font-weight: 700; color: #374151; cursor: pointer;
}
.paginacion-centro { font-size: 0.78rem; font-weight: 600; color: #6B7280; letter-spacing: 0.04em; }
.paginacion-derecha { display: flex; gap: 4px; }
.btn-pag {
  display: flex; align-items: center; justify-content: center; min-width: 34px; height: 34px; padding: 0 4px;
  border: 1.5px solid #E5E7EB; border-radius: 8px; background: #fff;
  font-family: 'Montserrat', sans-serif; font-size: 0.82rem; font-weight: 700;
  color: #374151; cursor: pointer; transition: all .15s;
}
.btn-pag:hover:not(:disabled) { border-color: #1B396A; color: #1B396A; background: #EFF6FF; }
.btn-pag.activo { background: #1B396A; color: #fff; border-color: #1B396A; }
.btn-pag:disabled { opacity: 0.35; cursor: not-allowed; }

/* ══════════════════════════════════════════════════════
BULK BAR
══════════════════════════════════════════════════════ */
.bulk-bar {
  position: fixed; bottom: 0; left: 0; right: 0; z-index: 500;
  background: #1B396A; box-shadow: 0 -4px 24px rgba(27,57,106,.35); border-top: 2px solid #152D57;
}
.bulk-bar-inner {
  max-width: 100%; margin: 0 auto; display: flex; align-items: center;
  justify-content: space-between; padding: 12px 16px; gap: 12px; flex-wrap: wrap;
}
.bulk-bar-left { display: flex; align-items: center; gap: 10px; color: #fff; font-weight: 700; font-size: 0.9rem; letter-spacing: 0.04em; }
.bulk-count { font-weight: 800; }
.bulk-bar-center { display: flex; align-items: center; gap: 8px; }
.bulk-select {
  padding: 8px 14px; border-radius: 8px; border: none;
  font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 0.82rem;
  letter-spacing: 0.04em; background: rgba(255,255,255,.15); color: #fff; outline: none; cursor: pointer;
}
.bulk-select option { background: #1B396A; color: #fff; }
.bulk-btn-aplicar {
  display: flex; align-items: center; gap: 7px; padding: 8px 20px; border-radius: 8px; border: none;
  background: #fff; color: #1B396A; font-family: 'Montserrat', sans-serif;
  font-weight: 800; font-size: 0.82rem; letter-spacing: 0.05em; cursor: pointer; transition: opacity .15s;
}
.bulk-btn-aplicar:disabled { opacity: 0.5; cursor: not-allowed; }
.bulk-btn-aplicar:not(:disabled):hover { opacity: 0.9; }
.bulk-bar-right { display: flex; align-items: center; }
.bulk-btn-cancelar {
  display: flex; align-items: center; gap: 7px; padding: 8px 16px; border-radius: 8px;
  border: 1.5px solid rgba(255,255,255,.4); background: transparent; color: rgba(255,255,255,.9);
  font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 0.8rem;
  letter-spacing: 0.04em; cursor: pointer; transition: border-color .15s, background .15s;
}
.bulk-btn-cancelar:hover { border-color: #fff; background: rgba(255,255,255,.1); }
.bulk-slide-enter-active,.bulk-slide-leave-active{transition:transform .3s cubic-bezier(.4,0,.2,1)}
.bulk-slide-enter-from,.bulk-slide-leave-to{transform:translateY(100%)}

/* ══════════════════════════════════════════════════════
MODALES
══════════════════════════════════════════════════════ */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(15,23,42,.55);
  backdrop-filter: blur(3px); display: flex; align-items: center;
  justify-content: center; z-index: 1000; padding: 16px;
}
.modal-content {
  background: #fff; border-radius: 16px; width: 100%; max-width: 560px;
  max-height: 90vh; display: flex; flex-direction: column;
  box-shadow: 0 20px 60px rgba(0,0,0,.2); overflow: hidden;
}
.modal-ver-carrera { max-width: 620px; }
.modal-confirmar   { max-width: 460px; }
.modal-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 20px 22px 18px;
  background: linear-gradient(135deg, #1B396A 0%, #1D4ED8 100%); flex-shrink: 0;
}
.modal-header-info { display: flex; flex-direction: column; gap: 3px; }
.modal-header-tag  { font-size: 0.68rem; font-weight: 700; letter-spacing: 0.1em; color: rgba(255,255,255,.7); }
.modal-header h3   { margin: 0; font-size: 1.05rem; font-weight: 800; color: #fff; letter-spacing: -0.01em; }
.sub-header-control{ font-size: 0.82rem; font-weight: 700; font-family: monospace; color: rgba(255,255,255,.8); }
.btn-cerrar-modal {
  background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2);
  color: #fff; cursor: pointer; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  width: 36px; height: 36px; flex-shrink: 0; transition: background .15s;
}
.btn-cerrar-modal:hover { background: rgba(255,255,255,.2); }

.modal-body { padding: 0; }
.tab-contenido-scroll { overflow-y: auto; padding: 1.4rem 1.6rem; flex: 1; }
.form-body { padding: 1.4rem 1.6rem; overflow-y: auto; }
.modal-footer {
  padding: 14px 20px; background: #F8FAFC; display: flex; gap: 8px;
  justify-content: flex-end; border-top: 1px solid #E5E7EB; flex-shrink: 0; flex-wrap: wrap;
}

/* Expediente */
.exp-seccion { border: 1.5px solid #E5E7EB; border-radius: 12px; overflow: hidden; background: #fff; }
.exp-seccion-titulo {
  display: flex; align-items: center; gap: 7px; background: #F0F5FF;
  padding: 10px 14px; font-size: 0.72rem; font-weight: 800;
  letter-spacing: 0.08em; color: #1B396A; border-bottom: 1px solid #DBEAFE;
}
.exp-seccion-titulo svg { stroke: #1B396A; flex-shrink: 0; }
.detalle-grid  { display: grid; grid-template-columns: 1fr 1fr; gap: 0.9rem; }
.detalle-campo { display: flex; flex-direction: column; gap: 4px; padding: 0.8rem 1rem; background: #F8FAFC; border-radius: 9px; border: 1px solid #E5E7EB; }
.detalle-campo.full-width { grid-column: 1 / -1; }
.detalle-label { font-size: 0.68rem; font-weight: 700; color: #9CA3AF; letter-spacing: 0.08em; }
.detalle-valor { font-size: 0.92rem; font-weight: 600; color: #1A1A1A; }
.mono-bold     { font-family: 'Courier New', monospace; font-weight: 800; font-size: 1rem; color: #1B396A; }
.estatus-badge-modal {
  display: inline-flex; align-items: center; padding: 5px 14px;
  border-radius: 20px; font-size: 0.78rem; font-weight: 800;
  letter-spacing: 0.06em; width: fit-content;
}

/* Formulario */
.form-grupo       { margin-bottom: 1.1rem; }
.form-grupo-doble { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-grupo label { display: block; margin-bottom: 6px; font-weight: 700; font-size: 0.78rem; letter-spacing: 0.06em; color: #374151; }
.obligatorio { color: #DC2626; }
.modal-input,
.modal-select {
  width: 100%; padding: 10px 14px; border: 1.5px solid #E5E7EB; border-radius: 9px;
  font-size: 0.9rem; font-weight: 600; background: #fff; color: #1A1A1A;
  font-family: 'Montserrat', sans-serif; outline: none;
  transition: border-color .2s, box-shadow .2s; box-sizing: border-box;
}
.modal-input:focus, .modal-select:focus { border-color: #1B396A; box-shadow: 0 0 0 3px rgba(27,57,106,.12); }
.modal-textarea { resize: vertical; min-height: 80px; line-height: 1.5; }

/* Toggle estado */
.toggle-estado-wrap { display: flex; align-items: center; gap: 10px; }
.toggle-estado {
  position: relative; width: 44px; height: 24px; border-radius: 12px;
  border: none; cursor: pointer; transition: background .2s;
  background: #D1D5DB; padding: 0;
}
.toggle-estado.activo { background: #16A34A; }
.toggle-knob {
  position: absolute; top: 3px; left: 3px;
  width: 18px; height: 18px; background: #fff; border-radius: 50%;
  transition: transform .2s; box-shadow: 0 1px 4px rgba(0,0,0,.2);
}
.toggle-estado.activo .toggle-knob { transform: translateX(20px); }
.toggle-label { font-size: 0.82rem; font-weight: 700; letter-spacing: 0.06em; color: #374151; }

/* Botones modal */
.btn-secundario {
  display: flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 9px;
  font-weight: 700; cursor: pointer; font-family: 'Montserrat', sans-serif;
  background: #fff; color: #374151; border: 1.5px solid #D1D5DB;
  transition: background .15s; font-size: 0.82rem; letter-spacing: 0.04em;
}
.btn-secundario:hover:not(:disabled) { background: #F3F4F6; }
.btn-secundario:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-eliminar {
  display: flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 9px;
  font-weight: 700; cursor: pointer; font-family: 'Montserrat', sans-serif;
  background: #DC2626; color: #fff; border: 1.5px solid #DC2626;
  transition: background .15s; font-size: 0.82rem; letter-spacing: 0.04em;
}
.btn-eliminar:hover:not(:disabled) { background: #B91C1C; }
.btn-eliminar:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-guardar {
  display: flex; align-items: center; gap: 8px; padding: 9px 20px; border-radius: 9px;
  font-weight: 700; cursor: pointer; font-family: 'Montserrat', sans-serif;
  background: #1B396A; color: #fff; border: 1.5px solid #1B396A;
  transition: background .15s; font-size: 0.82rem; letter-spacing: 0.04em;
}
.btn-guardar:hover:not(:disabled) { background: #152D57; }
.btn-guardar:disabled { opacity: 0.55; cursor: not-allowed; }
.btn-accion-outline {
  display: flex; align-items: center; gap: 7px; padding: 9px 16px; border-radius: 9px;
  font-weight: 700; cursor: pointer; font-family: 'Montserrat', sans-serif;
  background: #fff; color: #1B396A; border: 1.5px solid #1B396A;
  transition: background .15s; font-size: 0.82rem; letter-spacing: 0.04em;
}
.btn-accion-outline:hover { background: #EFF6FF; }
.spinner-btn {
  display: inline-block; width: 13px; height: 13px;
  border: 2px solid rgba(255,255,255,.4); border-top-color: #fff;
  border-radius: 50%; animation: spin .7s linear infinite; flex-shrink: 0;
}
.confirmar-body {
  display: flex; flex-direction: column; align-items: center; gap: 12px;
  text-align: center; padding: 2rem 1.6rem;
}
.confirmar-icono { width: 52px; height: 52px; stroke: #F59E0B; }
.confirmar-body p { color: #374151; font-size: 0.9rem; font-weight: 600; margin: 0; line-height: 1.5; letter-spacing: 0.02em; }
.bulk-preview { display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; max-height: 80px; overflow-y: auto; margin-top: 8px; }
.bulk-id-chip { background: #EFF6FF; color: #1B396A; border: 1px solid #BFDBFE; padding: 2px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 800; font-family: monospace; letter-spacing: 0.04em; }
.bulk-id-mas  { background: #F3F4F6; color: #6B7280; border-color: #E5E7EB; }

.modal-fade-enter-active,.modal-fade-leave-active{transition:all .25s cubic-bezier(.4,0,.2,1)}
.modal-fade-enter-from,.modal-fade-leave-to{opacity:0}
.modal-fade-enter-from .modal-content,.modal-fade-leave-to .modal-content{transform:scale(0.95) translateY(10px)}

/* ══════════════════════════════════════════════════════
RESPONSIVE
══════════════════════════════════════════════════════ */
@media (max-width: 1200px) { .kpis-grid { grid-template-columns: repeat(3, 1fr) } }
@media (max-width: 900px) {
  .kpis-grid { grid-template-columns: repeat(2, 1fr) }
  .filtros-grid { grid-template-columns: 1fr 1fr }
  .col-nombre { max-width: 200px }
}
@media (max-width: 768px) {
  .carreras-page { padding: 14px 8px 90px }
  .kpis-grid { grid-template-columns: 1fr 1fr }
  .filtros-grid { grid-template-columns: 1fr }
  .detalle-grid { grid-template-columns: 1fr }
  .form-grupo-doble { grid-template-columns: 1fr }
  .modal-footer { flex-direction: column }
  .modal-footer button { width: 100%; justify-content: center }
  .paginacion { flex-direction: column; gap: 10px }
  .page-header { flex-direction: column; align-items: flex-start }
  .page-header-btns { width: 100%; flex-direction: column }
  .btn-nuevo, .btn-select-all { width: 100%; justify-content: center }
  .bulk-bar-inner { flex-direction: column; align-items: flex-start; gap: 10px }
  .bulk-bar-center { width: 100%; flex-wrap: wrap }
  .tabla-wrapper { overflow-x: auto; margin-bottom: 20px }
  .tabla-carreras { min-width: 720px }
}
</style>