<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="calificaciones-page">
      <!-- Sincronizar búsqueda global -->
      <div v-if="busquedaGlobal && sincronizarBusquedaGlobal(busquedaGlobal)" style="display:none"></div>
      
      <!-- Barra de carga -->
      <div class="barra-carga" :class="{ activa: cargando || cargandoCatalogos }">
        <div class="barra-progreso"></div>
      </div>
      
      <!-- Breadcrumb dinámico -->
      <div class="breadcrumb">
        <router-link to="/dashboard" class="breadcrumb-link">Inicio</router-link>
        <span class="sep">›</span>
        <router-link to="/servicios-escolares" class="breadcrumb-link">Servicios Escolares</router-link>
        <span class="sep">›</span>
        <template v-if="vista === 'carreras'">
          <span class="activo">Calificaciones</span>
        </template>
        <template v-else-if="vista === 'semestres' && carreraSeleccionada">
          <router-link to="/calificaciones" class="breadcrumb-link">Calificaciones</router-link>
          <span class="sep">›</span>
          <span class="activo">{{ carreraSeleccionada.nombre }}</span>
        </template>
        <template v-else-if="vista === 'grupos' && carreraSeleccionada && semestreSeleccionado">
          <router-link to="/calificaciones" class="breadcrumb-link">Calificaciones</router-link>
          <span class="sep">›</span>
          <span class="breadcrumb-link" @click="vista='semestres'" style="cursor:pointer;">{{ carreraSeleccionada.nombre }}</span>
          <span class="sep">›</span>
          <span class="activo">{{ semestreSeleccionado.numero }}° Semestre</span>
        </template>
        <template v-else-if="vista === 'materias' && grupoSeleccionado && carreraSeleccionada">
          <router-link to="/calificaciones" class="breadcrumb-link">Calificaciones</router-link>
          <span class="sep">›</span>
          <span class="breadcrumb-link" @click="vista='semestres'" style="cursor:pointer;">{{ carreraSeleccionada.nombre }}</span>
          <span class="sep">›</span>
          <span class="breadcrumb-link" @click="vista='grupos'" style="cursor:pointer;">{{ semestreSeleccionado.numero }}° Semestre</span>
          <span class="sep">›</span>
          <span class="activo">{{ grupoSeleccionado.clave_grupo }}</span>
        </template>
        <template v-else-if="vista === 'tabla' && materiaSeleccionada && grupoSeleccionado && carreraSeleccionada">
          <router-link to="/calificaciones" class="breadcrumb-link">Calificaciones</router-link>
          <span class="sep">›</span>
          <span class="breadcrumb-link" @click="vista='semestres'" style="cursor:pointer;">{{ carreraSeleccionada.nombre }}</span>
          <span class="sep">›</span>
          <span class="breadcrumb-link" @click="vista='grupos'" style="cursor:pointer;">{{ semestreSeleccionado.numero }}° Semestre</span>
          <span class="sep">›</span>
          <span class="breadcrumb-link" @click="vista='materias'" style="cursor:pointer;">{{ grupoSeleccionado.clave_grupo }}</span>
          <span class="sep">›</span>
          <span class="activo">{{ materiaSeleccionada.nombre }}</span>
        </template>
      </div>

      <!-- Mensaje de error de catálogos -->
      <div v-if="errorCatalogos" class="alerta-error-catalogos">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        No se pudieron cargar algunos catálogos: {{ errorCatalogos }}.
        <button @click="reintentarCatalogos" class="btn-reintentar">Reintentar</button>
      </div>

      <!-- ============================================================ -->
      <!-- PANTALLA 1: CARDS DE CARRERAS                                -->
      <!-- ============================================================ -->
      <div v-if="vista === 'carreras'">
        <div class="encabezado-seccion">
          <div>
            <h1 class="titulo-pagina">Calificaciones</h1>
            <p class="subtitulo">Selecciona una carrera para gestionar sus calificaciones</p>
          </div>
        </div>

        <!-- Búsqueda de carreras -->
        <div class="filtros-card" style="margin-bottom: 1.5rem;">
          <div class="busqueda-wrapper" style="max-width: 400px;">
            <div class="busqueda-control">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-lupa">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
              </svg>
              <input
                v-model="busquedaCarrera"
                type="text"
                placeholder="Buscar carrera..."
                class="input-busqueda-control"
              />
            </div>
          </div>
        </div>

        <!-- Grid de carreras estilo Liverpool -->
        <div class="carreras-grid">
          <div
            v-for="carrera in carrerasFiltradas"
            :key="carrera.id_carrera"
            class="carrera-card"
            :style="{ backgroundImage: `linear-gradient(135deg, ${carrera.color || '#1B396A'} 0%, ${carrera.colorSecundario || '#3B82F6'} 100%)` }"
            @click="seleccionarCarrera(carrera)"
          >
            <div class="carrera-card-badge" v-if="carrera.activo !== false">Activo</div>
            <div class="carrera-card-body">
              <div class="carrera-card-icono">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="28" height="28">
                  <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                  <path d="M6 12v5c0 1.1 2.7 2 6 2s6-.9 6-2v-5"/>
                </svg>
              </div>
              <div class="carrera-card-info">
                <h3 class="carrera-card-nombre">{{ carrera.nombre }}</h3>
                <div class="carrera-card-stats">
                  <span><strong>{{ carrera.total_alumnos || 0 }}</strong> Alumnos</span>
                  <span class="stat-sep">·</span>
                  <span><strong>{{ carrera.total_grupos || 0 }}</strong> Grupos</span>
                  <span class="stat-sep">·</span>
                  <span><strong>{{ carrera.total_docentes || 0 }}</strong> Docentes</span>
                </div>
              </div>
            </div>
            <div class="carrera-card-footer">
              <span class="ver-detalles">Ver detalles →</span>
            </div>
          </div>
          <div v-if="carrerasFiltradas.length === 0 && !cargandoCatalogos" class="sin-resultados-carreras">
            <p>No se encontraron carreras</p>
          </div>
        </div>
      </div>

      <!-- ============================================================ -->
      <!-- PANTALLA 2: SEMESTRES                                        -->
      <!-- ============================================================ -->
      <div v-if="vista === 'semestres' && carreraSeleccionada">
        <div class="encabezado-seccion">
          <div>
            <h1 class="titulo-pagina">{{ carreraSeleccionada.nombre }}</h1>
            <p class="subtitulo">Selecciona un semestre</p>
          </div>
          <button @click="volverACarreras" class="btn-secundario">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
              <polyline points="15 18 9 12 15 6"/>
            </svg>
            Volver
          </button>
        </div>

        <!-- Mini resumen de la carrera -->
        <div class="mini-resumen-grid">
          <div class="mini-resumen-item">
            <span class="mini-resumen-numero">{{ carreraSeleccionada.total_alumnos || 0 }}</span>
            <span class="mini-resumen-etiqueta">Alumnos</span>
          </div>
          <div class="mini-resumen-item">
            <span class="mini-resumen-numero">{{ carreraSeleccionada.total_grupos || 0 }}</span>
            <span class="mini-resumen-etiqueta">Grupos</span>
          </div>
          <div class="mini-resumen-item">
            <span class="mini-resumen-numero">{{ promedioCarrera }}</span>
            <span class="mini-resumen-etiqueta">Promedio General</span>
          </div>
          <div class="mini-resumen-item">
            <span class="mini-resumen-numero color-rojo">{{ reprobadosCarrera }}</span>
            <span class="mini-resumen-etiqueta">Reprobados</span>
          </div>
        </div>

        <!-- Cards de Semestres -->
        <div class="seccion-titulo-wrap" style="margin-bottom: 1rem;">
          <h3 class="seccion-titulo">Semestres</h3>
        </div>
        <div class="cards-grid semestres-grid">
          <div
            v-for="semestre in semestresDisponibles"
            :key="semestre.numero"
            class="card semestre-card"
            @click="seleccionarSemestre(semestre)"
          >
            <div class="semestre-numero">{{ semestre.numero }}°</div>
            <h3 class="card-titulo">SEMESTRE {{ semestre.numero }}</h3>
            <p class="card-info">{{ semestre.totalGrupos }} grupo(s)</p>
            <div class="card-footer">
              <span class="card-badge">VER GRUPOS</span>
              <ChevronRight :size="16" class="card-arrow" />
            </div>
          </div>
        </div>

        <!-- Sección Actas (conservada) -->
        <div class="actas-seccion" style="margin-top: 2rem;">
          <h3 class="seccion-titulo">Actas</h3>
          <div class="mini-resumen-grid" style="margin-bottom: 1rem;">
            <div class="mini-resumen-item">
              <span class="mini-resumen-numero color-verde">{{ kpisActas.generadas }}</span>
              <span class="mini-resumen-etiqueta">Generadas</span>
            </div>
            <div class="mini-resumen-item">
              <span class="mini-resumen-numero" style="color:#F59E0B;">{{ kpisActas.pendientes }}</span>
              <span class="mini-resumen-etiqueta">Pendientes</span>
            </div>
            <div class="mini-resumen-item">
              <span class="mini-resumen-numero color-rojo">{{ kpisActas.sinCalificacion }}</span>
              <span class="mini-resumen-etiqueta">Sin calificación</span>
            </div>
            <div class="mini-resumen-item">
              <span class="mini-resumen-numero">{{ actas.length }}</span>
              <span class="mini-resumen-etiqueta">Total</span>
            </div>
          </div>

          <div class="tabla-card">
            <div class="tabla-scroll">
              <table class="tabla-califs">
                <thead>
                  <tr>
                    <th>Grupo</th>
                    <th>Materia</th>
                    <th>Docente</th>
                    <th class="centrado">Estatus</th>
                    <th class="centrado">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="cargandoActas">
                    <td colspan="5" class="sin-resultados"><p>Cargando actas...</p></td>
                  </tr>
                  <tr v-else-if="actas.length === 0">
                    <td colspan="5" class="sin-resultados"><p>No hay actas disponibles</p></td>
                  </tr>
                  <tr v-for="acta in actas" :key="acta.id_acta">
                    <td><span class="control-chip">{{ acta.clave_grupo }}</span></td>
                    <td>{{ acta.materia }}</td>
                    <td>{{ acta.docente || 'Sin asignar' }}</td>
                    <td class="centrado">
                      <span class="badge-estado" :class="{'bien': acta.estatus === 'generada', 'regular': acta.estatus === 'pendiente', 'sin-calificar': acta.estatus === 'sin_calificacion'}">
                        {{ acta.estatus === 'generada' ? 'Generada' : acta.estatus === 'pendiente' ? 'Pendiente' : 'Sin calificación' }}
                      </span>
                    </td>
                    <td class="centrado acciones-cell">
                      <div class="acciones-fila">
                        <button @click="generarActa(acta)" class="btn-accion guardar" title="Generar acta" :disabled="acta.estatus === 'sin_calificacion'">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        </button>
                        <button @click="descargarActa(acta)" class="btn-accion ver" title="Descargar PDF" :disabled="acta.estatus !== 'generada'">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        </button>
                        <button @click="imprimirActa(acta)" class="btn-accion ver" title="Imprimir" :disabled="acta.estatus !== 'generada'" style="background:#EDE9FE;color:#7C3AED;">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Tarjetas de Docentes -->
        <div class="docentes-seccion" style="margin-top: 2rem;">
          <h3 class="seccion-titulo">Docentes</h3>
          <div class="docentes-grid">
            <div v-for="docente in docentesCarreraFiltrados" :key="docente.nombre" class="docente-card">
              <div class="docente-card-body">
                <div class="docente-avatar">{{ iniciales(docente.nombre) }}</div>
                <h4 class="docente-nombre">{{ docente.nombre }}</h4>
                <div class="docente-materias">
                  <span v-for="materia in docente.materias" :key="materia" class="materia-tag">{{ materia }}</span>
                </div>
                <div class="docente-stats"><span><strong>{{ docente.grupos.length }}</strong> grupo(s)</span></div>
              </div>
              <div class="docente-card-footer">
                <button @click="abrirModalDocente(docente)" class="btn-accion ver grande" title="Ver detalles del docente">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  <span>Ver detalles</span>
                </button>
              </div>
            </div>
            <div v-if="docentesCarreraFiltrados.length === 0" class="sin-resultados-carreras"><p>No se encontraron docentes</p></div>
          </div>
        </div>
      </div>

      <!-- ============================================================ -->
      <!-- PANTALLA 3: GRUPOS DEL SEMESTRE                             -->
      <!-- ============================================================ -->
      <div v-else-if="vista === 'grupos' && semestreSeleccionado">
        <div class="encabezado-seccion">
          <div>
            <h1 class="titulo-pagina">{{ carreraSeleccionada.nombre }} - {{ semestreSeleccionado.numero }}° Semestre</h1>
            <p class="subtitulo">Selecciona un grupo</p>
          </div>
          <button @click="vista='semestres'" class="btn-secundario">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
              <polyline points="15 18 9 12 15 6"/>
            </svg>
            Volver
          </button>
        </div>

        <div class="cards-grid grupos-grid">
          <div v-for="grupo in gruposPorSemestre" :key="grupo.id_grupo" class="card grupo-card" @click="seleccionarGrupoParaMaterias(grupo)">
            <div class="grupo-nombre">{{ grupo.clave_grupo }}</div>
            <div class="grupo-info">
              <div class="info-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>{{ grupo.docente || 'Sin tutor' }}</span>
              </div>
              <div class="info-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>{{ grupo.inscritos || 0 }} inscritos</span>
              </div>
            </div>
            <div class="card-footer">
              <span class="card-badge">VER MATERIAS</span>
              <ChevronRight :size="16" class="card-arrow" />
            </div>
          </div>
        </div>
        <div v-if="gruposPorSemestre.length === 0" class="sin-resultados-carreras">
          <p>No hay grupos en este semestre</p>
        </div>
      </div>

      <!-- ============================================================ -->
      <!-- PANTALLA 4: MATERIAS DEL GRUPO                               -->
      <!-- ============================================================ -->
      <div v-else-if="vista === 'materias' && grupoSeleccionado">
        <div class="encabezado-seccion">
          <div>
            <h1 class="titulo-pagina">{{ grupoSeleccionado.clave_grupo }}</h1>
            <p class="subtitulo">Selecciona una materia</p>
          </div>
          <button @click="vista='grupos'" class="btn-secundario">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
              <polyline points="15 18 9 12 15 6"/>
            </svg>
            Volver
          </button>
        </div>

        <div class="cards-grid materias-grid">
          <div v-for="materia in materiasGrupo" :key="materia.id_materia" class="card materia-card" @click="seleccionarMateria(materia)">
            <div class="materia-nombre">{{ materia.nombre }}</div>
            <p class="materia-docente">{{ materia.docente || 'Sin docente' }}</p>
            <div class="card-footer">
              <span class="card-badge">{{ materia.inscritos || 0 }} alumnos</span>
              <ChevronRight :size="16" class="card-arrow" />
            </div>
          </div>
        </div>
        <div v-if="materiasGrupo.length === 0 && !cargando" class="sin-resultados-carreras">
          <p>No se encontraron materias para este grupo</p>
        </div>
      </div>

      <!-- ============================================================ -->
      <!-- PANTALLA 3: TABLA DE CALIFICACIONES DEL GRUPO                -->
      <!-- ============================================================ -->
      <div v-if="vista === 'tabla' && grupoSeleccionado && materiaSeleccionada">
        <div class="encabezado-seccion">
          <div>
            <h1 class="titulo-pagina">{{ grupoSeleccionado.clave_grupo }} — {{ materiaSeleccionada.nombre }}</h1>
            <p class="subtitulo">Captura y consulta de calificaciones</p>
          </div>
          <button @click="vista='materias'" class="btn-secundario">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
              <polyline points="15 18 9 12 15 6"/>
            </svg>
            Volver
          </button>
        </div>

        <!-- Filtros (versión compacta) -->
        <div class="filtros-card">
          <div class="filtros-container">
            <div class="busqueda-wrapper">
              <div class="busqueda-control">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-lupa">
                  <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input
                  v-model="busquedaControl"
                  type="text"
                  placeholder="Buscar por nombre o control..."
                  class="input-busqueda-control"
                  @input="buscarEnTiempoReal"
                />
              </div>
            </div>
            <div class="filtros-acciones">
              <button v-if="busquedaControl" @click="busquedaControl = ''" class="btn-limpiar-filtros">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                  <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Limpiar
              </button>
              <button @click="exportar" class="btn-secundario" :disabled="cargando">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                  <polyline points="7 10 12 15 17 10"/>
                  <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Exportar
              </button>
            </div>
          </div>
        </div>

        <!-- KPIs del grupo -->
        <div class="kpis-grupo-grid">
          <div class="kpi-item">
            <span class="kpi-valor" :class="clasePromedio(promedioGeneral)">{{ promedioGeneral }}</span>
            <span class="kpi-label">Promedio grupo</span>
          </div>
          <div class="kpi-item">
            <span class="kpi-valor kpi-verde">{{ alumnos.length - totalReprobados - totalNC }}</span>
            <span class="kpi-label">Aprobados</span>
          </div>
          <div class="kpi-item">
            <span class="kpi-valor kpi-rojo">{{ totalReprobados }}</span>
            <span class="kpi-label">Reprobados</span>
          </div>
          <div class="kpi-item">
            <span class="kpi-valor kpi-gris">{{ totalNC }}</span>
            <span class="kpi-label">Sin calificar</span>
          </div>
        </div>


        <!-- Sub‑pestañas de la tabla del grupo -->
        <div class="subtab-nav">
          <button 
            class="subtab-btn" 
            :class="{ activo: vistaTabla === 'calificaciones' }"
            @click="vistaTabla = 'calificaciones'"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
              <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
            </svg>
            Calificaciones
          </button>
          <button 
            class="subtab-btn" 
            :class="{ activo: vistaTabla === 'especiales' }"
            @click="vistaTabla = 'especiales'"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
              <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/>
            </svg>
            Especiales / Globales
          </button>
        </div>

        <!-- Tabla principal (limpia) -->
        <div v-if="vistaTabla === 'calificaciones'" class="tabla-card">
          <div class="tabla-encabezado">
            <h3 class="seccion-titulo sin-margen">
              Registro de Calificaciones
            </h3>
            <div class="tabla-acciones-top">
              <span class="tabla-contador">{{ alumnosFiltrados.length }} alumno(s)</span>
              <button @click="guardarTodo" class="btn-primario" :disabled="cargando">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                  <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                  <polyline points="17 21 17 13 7 13 7 21"/>
                  <polyline points="7 3 7 8 15 8"/>
                </svg>
                {{ cargando ? 'Guardando...' : 'Guardar Todo' }}
              </button>
            </div>
          </div>

                    <!-- Barra de guardado por unidad -->
          <div class="guardar-unidades-bar">
            <span class="guardar-unidades-label">Guardar por unidad:</span>
            <button @click="guardarUnidad(1)" class="btn-unidad" :disabled="cargando">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
              U1
            </button>
            <button @click="guardarUnidad(2)" class="btn-unidad" :disabled="cargando">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
              U2
            </button>
            <button @click="guardarUnidad(3)" class="btn-unidad" :disabled="cargando">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
              U3
            </button>
            <button @click="guardarUnidad(4)" class="btn-unidad" :disabled="cargando">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
              U4
            </button>
            <button @click="guardarUnidad(5)" class="btn-unidad" :disabled="cargando">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
              U5
            </button>
          </div>
          
          <div class="tabla-scroll">
            <table class="tabla-califs" @keydown="navegarTeclado">
              <thead>
                <tr>
                  <th>No. de Control</th>
                  <th>Nombre del Alumno</th>
                  <th class="centrado">U1</th>
                  <th class="centrado">U2</th>
                  <th class="centrado">U3</th>
                  <th class="centrado">U4</th>
                  <th class="centrado">U5</th>
                  <th class="centrado">Final</th>
                  <th class="centrado">Estado</th>
                  <th class="centrado">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(alumno, index) in alumnosPaginados"
                  :key="alumno.control"
                  :class="[
                    { 'fila-activa': filaActiva === index && !esNC(alumno) },
                    { 'fila-reprobado': Number(calcularFinal(alumno)) < 60 && !esNC(alumno) },
                    { 'fila-sin-calificar': esNC(alumno) }
                  ]"
                  :ref="el => { if (el) filasRef[index] = el }"
                  tabindex="0"
                >
                  <td><div class="control-chip">{{ alumno.control }}</div></td>
                  <td>
                    <div class="alumno-info">
                      <div class="alumno-avatar">{{ iniciales(alumno.nombre) }}</div>
                      <span class="alumno-nombre">{{ alumno.nombre }}</span>
                    </div>
                  </td>
                  <td class="centrado">
                    <div class="input-nota-wrap">
                      <input
                        v-model="alumno.u1"
                        type="number" step="1" min="0" max="100"
                        class="input-nota"
                        :class="claseNota(alumno.u1)"
                        data-unidad="1"
                        @focus="filaActiva = index"
                        @keydown.enter.prevent="guardarUnidadDesdeTeclado(1)"
                      />
                    </div>
                  </td>
                  <td class="centrado">
                    <div class="input-nota-wrap">
                      <input
                        v-model="alumno.u2"
                        type="number" step="1" min="0" max="100"
                        class="input-nota"
                        :class="claseNota(alumno.u2)"
                        data-unidad="2"
                        @focus="filaActiva = index"
                        @keydown.enter.prevent="guardarUnidadDesdeTeclado(2)"
                      />
                    </div>
                  </td>
                  <td class="centrado">
                    <div class="input-nota-wrap">
                      <input
                        v-model="alumno.u3"
                        type="number" step="1" min="0" max="100"
                        class="input-nota"
                        :class="claseNota(alumno.u3)"
                        data-unidad="3"
                        @focus="filaActiva = index"
                        @keydown.enter.prevent="guardarUnidadDesdeTeclado(3)"
                      />
                    </div>
                  </td>
                  <td class="centrado">
                    <div class="input-nota-wrap">
                      <input
                        v-model="alumno.u4"
                        type="number" step="1" min="0" max="100"
                        class="input-nota"
                        :class="claseNota(alumno.u4)"
                        data-unidad="4"
                        @focus="filaActiva = index"
                        @keydown.enter.prevent="guardarUnidadDesdeTeclado(4)"
                      />
                    </div>
                  </td>
                  <td class="centrado">
                    <div class="input-nota-wrap">
                      <input
                        v-model="alumno.u5"
                        type="number" step="1" min="0" max="100"
                        class="input-nota"
                        :class="claseNota(alumno.u5)"
                        data-unidad="5"
                        @focus="filaActiva = index"
                        @keydown.enter.prevent="guardarUnidadDesdeTeclado(5)"
                      />
                    </div>
                  </td>
                  <td class="centrado">
                    <div class="final-chip" :class="calcularFinal(alumno) === null ? 'promedio-sin-calificar' : clasePromedio(calcularFinal(alumno))">
                      {{ calcularFinal(alumno) ?? '–' }}
                    </div>
                  </td>
                  <td class="centrado">
                    <span v-if="calcularFinal(alumno) === null" class="badge-estado sin-calificar">S/C</span>
                    <span v-else-if="Number(calcularFinal(alumno)) >= 90" class="badge-estado excelente">Exc</span>
                    <span v-else-if="Number(calcularFinal(alumno)) >= 80" class="badge-estado bien">Bien</span>
                    <span v-else-if="Number(calcularFinal(alumno)) >= 60" class="badge-estado regular">Reg</span>
                    <span v-else class="badge-estado reprobado">Rep</span>
                  </td>
                  <td class="centrado acciones-cell">
                    <div class="acciones-fila">
                      <button @click.stop="guardarFila(alumno)" class="btn-accion guardar" title="Guardar" :disabled="cargando">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
                      </button>
                      <button @click.stop="abrirModalAlumno(alumno)" class="btn-accion ver" title="Ver detalle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                          <circle cx="12" cy="12" r="3"/>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="alumnosPaginados.length === 0">
                  <td colspan="8" class="sin-resultados">
                    <div class="sin-resultados-inner">
                      <svg viewBox="0 0 24 24" fill="none" stroke="#E5E7EB" stroke-width="1.5" width="48" height="48"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                      <p>No se encontraron alumnos</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Paginación -->
          <div class="paginacion-container" v-if="alumnosFiltrados.length > 0">
            <div class="paginacion-izquierda">
              <span class="paginacion-filas-label">Filas por página:</span>
              <select v-model.number="itemsPorPagina" @change="cambiarItemsPorPagina(itemsPorPagina)" class="paginacion-select">
                <option :value="10">10</option>
                <option :value="20">20</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
              </select>
            </div>
            <div class="paginacion-centro">
              <span class="paginacion-pagina-label">Página {{ paginaActual }} de {{ totalPaginas }}</span>
            </div>
            <div class="paginacion-controles">
              <button @click="cambiarPagina(paginaActual - 1)" :disabled="paginaActual === 1" class="btn-pag" title="Anterior">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
              </button>
              <template v-for="(item, idx) in paginasVisibles" :key="idx">
                <span v-if="item === '...'" class="paginacion-ellipsis">…</span>
                <button
                  v-else
                  @click="cambiarPagina(item)"
                  :class="['btn-num', { activa: paginaActual === item }]"
                >{{ item }}</button>
              </template>
              <button @click="cambiarPagina(paginaActual + 1)" :disabled="paginaActual === totalPaginas" class="btn-pag" title="Siguiente">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
              </button>
            </div>
          </div>

          <!-- Resumen inferior de la tabla -->
          <div class="tabla-resumen">
            <div class="resumen-item">
              <span class="ri-label">Promedio grupo:</span>
              <strong :class="clasePromedio(promedioGeneral)">{{ promedioGeneral }}</strong>
            </div>
            <div class="resumen-separador"></div>
            <div class="resumen-item">
              <span class="ri-label">Reprobados:</span>
              <strong class="color-rojo">{{ totalReprobados }} / {{ alumnos.length }}</strong>
            </div>
            <div class="resumen-separador"></div>
            <div class="resumen-item">
              <span class="ri-label">Sin calificar:</span>
              <strong>{{ totalNC }}</strong>
            </div>
            <div class="resumen-separador"></div>
            <div class="resumen-item">
              <span class="ri-label">Aprobados:</span>
              <strong class="color-verde">{{ alumnos.length - totalReprobados - totalNC }}</strong>
            </div>
          </div>
        </div>

            <!-- Tabla de Especiales / Globales -->
            <div v-if="vistaTabla === 'especiales'" class="tabla-card">
              <div class="tabla-encabezado">
                <h3 class="seccion-titulo sin-margen">Solicitudes de Exámenes Especiales / Globales</h3>
                <span class="tabla-contador">{{ especiales.length }} solicitud(es)</span>
              </div>
              <div class="tabla-scroll">
                <table class="tabla-califs">
                  <thead>
                    <tr>
                      <th>Alumno</th>
                      <th class="centrado">Tipo</th>
                      <th class="centrado">Calificación</th>
                      <th class="centrado">Resultado</th>
                      <th class="centrado">Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="e in especiales" :key="e.id">
                      <td>{{ e.alumno }}</td>
                      <td class="centrado">
                        <span class="tipo-badge" :class="e.tipo === 'especial' ? 'tipo-especial' : 'tipo-global'">
                          {{ e.tipo === 'especial' ? 'Especial' : 'Global' }}
                        </span>
                      </td>
                      <td class="centrado">
                        <div class="final-chip" :class="e.calificacion ? clasePromedio(e.calificacion) : 'promedio-sin-calificar'">
                          {{ e.calificacion ?? '–' }}
                        </div>
                      </td>
                      <td class="centrado">
                        <span class="badge-estado" :class="e.calificacion >= 60 ? 'excelente' : 'reprobado'">
                          {{ e.calificacion >= 60 ? 'Aprobado' : 'Reprobado' }}
                        </span>
                      </td>
                      <td class="centrado acciones-cell">
                        <button class="btn-accion ver" title="Ver detalle">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                          </svg>
                        </button>
                      </td>
                    </tr>
                    <tr v-if="especiales.length === 0">
                      <td colspan="5" class="sin-resultados">
                        <div class="sin-resultados-inner">
                          <svg viewBox="0 0 24 24" fill="none" stroke="#E5E7EB" stroke-width="1.5" width="48" height="48"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                          <p>No hay solicitudes de exámenes especiales o globales</p>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
      </div>

      <!-- Atajos de teclado -->
      <div class="atajos-info" v-if="vista === 'tabla'">
        <span>⌨ Atajos: <kbd>↑ ↓</kbd> navegar filas · <kbd>Enter</kbd> guardar unidad actual · <kbd>Ctrl+S</kbd> guardar todo · <kbd>Tab</kbd> saltar celdas</span>
      </div>
    </div>

    <!-- ============================================================ -->
    <!-- MODAL FLOTANTE DE ALUMNO (REDISEÑADO)                        -->
    <!-- ============================================================ -->
    <Teleport to="body">
      <div v-if="showModalAlumno" class="modal-overlay" @click.self="cerrarModalAlumno">
        <div class="modal-content modal-alumno-rediseno">
          <!-- Encabezado del modal -->
          <div class="modal-alumno-header">
            <div class="modal-alumno-avatar">
              {{ iniciales(alumnoSeleccionado?.nombre) }}
            </div>
            <div class="modal-alumno-titulo">
              <h2>{{ alumnoSeleccionado?.nombre }}</h2>
              <span class="modal-alumno-control">{{ alumnoSeleccionado?.control }}</span>
            </div>
            <button @click="cerrarModalAlumno" class="modal-close-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <!-- Pestañas -->
          <div class="modal-tabs">
            <button 
              :class="['modal-tab', { activa: modalTab === 'general' }]" 
              @click="modalTab = 'general'"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
              </svg>
              General
            </button>
            <button 
              :class="['modal-tab', { activa: modalTab === 'calificaciones' }]" 
              @click="modalTab = 'calificaciones'"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
              </svg>
              Calificaciones
            </button>
            <button 
              :class="['modal-tab', { activa: modalTab === 'historial' }]" 
              @click="modalTab = 'historial'"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
              </svg>
              Historial
            </button>
          </div>

          <div class="modal-body">
            <!-- Tab General -->
            <div v-if="modalTab === 'general' && alumnoSeleccionado" class="tab-general">
              <div class="info-grid">
                <div class="info-card">
                  <div class="info-icono azul">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                  </div>
                  <div class="info-texto">
                    <span class="info-label">Nombre</span>
                    <span class="info-valor">{{ alumnoSeleccionado.nombre }}</span>
                  </div>
                </div>
                <div class="info-card">
                  <div class="info-icono verde">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                      <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                  </div>
                  <div class="info-texto">
                    <span class="info-label">No. Control</span>
                    <span class="info-valor">{{ alumnoSeleccionado.control }}</span>
                  </div>
                </div>
                <div class="info-card">
                  <div class="info-icono morado">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                      <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.1 2.7 2 6 2s6-.9 6-2v-5"/>
                    </svg>
                  </div>
                  <div class="info-texto">
                    <span class="info-label">Carrera</span>
                    <span class="info-valor">{{ carreraSeleccionada?.nombre || 'No definida' }}</span>
                  </div>
                </div>
                <div class="info-card">
                  <div class="info-icono naranja">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                  </div>
                  <div class="info-texto">
                    <span class="info-label">Semestre</span>
                    <span class="info-valor">{{ alumnoSeleccionado.semestre || 'N/A' }}</span>
                  </div>
                </div>
              </div>
              <div class="estatus-card" :class="esNC(alumnoSeleccionado) ? 'sin-calificar' : (Number(calcularFinal(alumnoSeleccionado)) >= 60 ? 'aprobado' : 'reprobado')">
                <div class="estatus-icono">
                  <svg v-if="esNC(alumnoSeleccionado)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                  <svg v-else-if="Number(calcularFinal(alumnoSeleccionado)) >= 60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="24" height="24"><polyline points="20 6 9 17 4 12"/></svg>
                  <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </div>
                <div class="estatus-texto">
                  <span>Estatus actual</span>
                  <strong>{{ esNC(alumnoSeleccionado) ? 'Sin calificar' : (Number(calcularFinal(alumnoSeleccionado)) >= 60 ? 'Aprobado' : 'Reprobado') }}</strong>
                </div>
              </div>
            </div>

            <!-- Tab Calificaciones -->
            <div v-if="modalTab === 'calificaciones' && alumnoSeleccionado" class="tab-calificaciones">
              <div class="calificaciones-grid">
                <div class="cal-card">
                  <div class="cal-header">
                    <span class="cal-titulo">Unidad 1</span>
                  </div>
                  <input v-model="alumnoSeleccionado.u1" type="number" step="1" min="0" max="100" class="input-nota grande" :class="claseNota(alumnoSeleccionado.u1)" />
                  <div class="cal-barra">
                    <div class="cal-barra-fill" :style="{ width: (Number(alumnoSeleccionado.u1 || 0)) + '%', background: colorNota(Number(alumnoSeleccionado.u1 || 0)) }"></div>
                  </div>
                </div>
                <div class="cal-card">
                  <div class="cal-header">
                    <span class="cal-titulo">Unidad 2</span>
                  </div>
                  <input v-model="alumnoSeleccionado.u2" type="number" step="1" min="0" max="100" class="input-nota grande" :class="claseNota(alumnoSeleccionado.u2)" />
                  <div class="cal-barra">
                    <div class="cal-barra-fill" :style="{ width: (Number(alumnoSeleccionado.u2 || 0)) + '%', background: colorNota(Number(alumnoSeleccionado.u2 || 0)) }"></div>
                  </div>
                </div>
                <div class="cal-card">
                  <div class="cal-header">
                    <span class="cal-titulo">Unidad 3</span>
                  </div>
                  <input v-model="alumnoSeleccionado.u3" type="number" step="1" min="0" max="100" class="input-nota grande" :class="claseNota(alumnoSeleccionado.u3)" />
                  <div class="cal-barra">
                    <div class="cal-barra-fill" :style="{ width: (Number(alumnoSeleccionado.u3 || 0)) + '%', background: colorNota(Number(alumnoSeleccionado.u3 || 0)) }"></div>
                  </div>
                </div>
                <div class="cal-card">
                  <div class="cal-header">
                    <span class="cal-titulo">Unidad 4</span>
                  </div>
                  <input v-model="alumnoSeleccionado.u4" type="number" step="1" min="0" max="100" class="input-nota grande" :class="claseNota(alumnoSeleccionado.u4)" />
                  <div class="cal-barra">
                    <div class="cal-barra-fill" :style="{ width: (Number(alumnoSeleccionado.u4 || 0)) + '%', background: colorNota(Number(alumnoSeleccionado.u4 || 0)) }"></div>
                  </div>
                </div>
                <div class="cal-card">
                  <div class="cal-header">
                    <span class="cal-titulo">Unidad 5</span>
                  </div>
                  <input v-model="alumnoSeleccionado.u5" type="number" step="1" min="0" max="100" class="input-nota grande" :class="claseNota(alumnoSeleccionado.u5)" />
                  <div class="cal-barra">
                    <div class="cal-barra-fill" :style="{ width: (Number(alumnoSeleccionado.u5 || 0)) + '%', background: colorNota(Number(alumnoSeleccionado.u5 || 0)) }"></div>
                  </div>
                </div>
              </div>

              <div class="final-display">
                <div class="final-label">Calificación Final</div>
                <div class="final-valor" :class="calcularFinal(alumnoSeleccionado) === null ? 'sin-calificar' : clasePromedio(calcularFinal(alumnoSeleccionado))">
                  {{ calcularFinal(alumnoSeleccionado) ?? '–' }}
                </div>
              </div>

              <div class="modal-acciones">
                <button @click="guardarDesdeModal" class="btn-primario" :disabled="cargando">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  Guardar calificaciones
                </button>
              </div>
            </div>

           <!-- Tab Historial -->
          <div v-if="modalTab === 'historial'" class="tab-historial">
            <div v-if="cargandoHistorial" class="sin-resultados-inner" style="padding: 2rem;">
              <p style="color:#6B7280;font-size:0.9rem;">Cargando historial...</p>
            </div>
            <div v-else-if="historialAlumno.length === 0" class="sin-resultados-inner" style="padding: 2rem;">
              <svg viewBox="0 0 24 24" fill="none" stroke="#E5E7EB" stroke-width="1.5" width="48" height="48">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
              </svg>
              <p>No hay historial registrado para este alumno.</p>
            </div>
            <div v-else class="tabla-scroll">
              <table class="tabla-califs">
                <thead>
                  <tr>
                    <th>Periodo</th>
                    <th>Materia</th>
                    <th class="centrado">Calificación</th>
                    <th class="centrado">Estatus</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="h in historialAlumno" :key="h.id ?? (h.periodo + h.materia)">
                    <td>{{ h.periodo }}</td>
                    <td>{{ h.materia }}</td>
                    <td class="centrado">
                      <div class="final-chip" :class="h.calificacion ? clasePromedio(h.calificacion) : 'promedio-sin-calificar'">
                        {{ h.calificacion ?? '–' }}
                      </div>
                    </td>
                    <td class="centrado">
                      <span
                        class="badge-estado"
                        :class="!h.calificacion ? 'sin-calificar' : Number(h.calificacion) >= 60 ? 'bien' : 'reprobado'"
                      >
                        {{ !h.calificacion ? 'S/C' : Number(h.calificacion) >= 60 ? 'Aprobado' : 'Reprobado' }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ============================================================ -->
    <!-- MODAL FLOTANTE DE DOCENTE (Teleported)                       -->
    <!-- ============================================================ -->
    <Teleport to="body">
      <div v-if="showModalDocente && docenteSeleccionado" class="modal-overlay" @click.self="cerrarModalDocente">
        <div class="modal-content modal-ver-docente">
          <div class="modal-header">
            <h3 class="modal-titulo">Detalles del Docente</h3>
            <button @click="cerrarModalDocente" class="modal-close-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <div class="modal-body">
            <div class="docente-modal-header">
              <div class="docente-modal-avatar">
                {{ iniciales(docenteSeleccionado.nombre) }}
              </div>
              <div class="docente-modal-info">
                <h4>{{ docenteSeleccionado.nombre }}</h4>
                <span class="docente-modal-carrera">{{ carreraSeleccionada.nombre }}</span>
              </div>
            </div>
            <div class="docente-modal-detalles">
              <div class="campo">
                <span class="campo-label">Materias impartidas</span>
                <span class="campo-valor">{{ docenteSeleccionado.materias.join(', ') }}</span>
              </div>
              <div class="campo">
                <span class="campo-label">Grupos asignados</span>
                <span class="campo-valor">{{ docenteSeleccionado.grupos.join(', ') }}</span>
              </div>
              <div class="campo">
                <span class="campo-label">Total de grupos</span>
                <span class="campo-valor">{{ docenteSeleccionado.grupos.length }}</span>
              </div>
            </div>
            <div class="docente-grupos" v-if="docenteSeleccionado.grupos.length > 0">
              <h4>Grupos que imparte</h4>
              <div class="tabla-scroll">
                <table class="tabla-califs">
                  <thead>
                    <tr>
                      <th>Clave</th>
                      <th>Materia</th>
                      <th class="centrado">Alumnos</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="grupo in getGruposPorDocente(docenteSeleccionado)" :key="grupo.id_grupo">
                      <td><span class="control-chip">{{ grupo.clave_grupo }}</span></td>
                      <td>{{ grupo.materia }}</td>
                      <td class="centrado">{{ grupo.inscritos ?? 0 }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Toast unificado -->
    <transition name="toast-slide">
      <div v-if="toast.visible" class="toast" :class="toast.tipo">
        <span class="toast-icono">
          <svg v-if="toast.tipo === 'exito'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg>
          <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </span>
        {{ toast.mensaje }}
      </div>
    </transition>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'
import { useCatalogos } from '@/composables/useCatalogos'
import { getCalificacionesGrupo, guardarCalificaciones } from '../api/calificaciones'
import api from '../api/axios'
import * as XLSX from 'xlsx'
import { ChevronRight } from 'lucide-vue-next'

// --- VISTA CARRERA: GRUPOS CARGADOS DESDE EL BACKEND ---
const gruposDeCarrera = ref([])
const cargandoGruposCarrera = ref(false)

async function cargarGruposCarrera(idCarrera) {
  cargandoGruposCarrera.value = true
  gruposDeCarrera.value = []
  try {
    const { data } = await api.get(`/carreras/${idCarrera}/grupos`)
    console.log('Respuesta backend:', data)          // ← temporal para verificar
    gruposDeCarrera.value = data.grupos ?? []
    console.log('Grupos cargados:', gruposDeCarrera.value.length)
  } catch (err) {
    const msg = err.response?.data?.message ?? 'Error al cargar grupos de la carrera'
    mostrarToast(msg, 'error')
  } finally {
    cargandoGruposCarrera.value = false
  }
}

// Catálogos dinámicos
const { periodos, carreras, materias, grupos, cargandoCatalogos, errorCatalogos, cargarCatalogos } = useCatalogos()

// --- ESTADO DE NAVEGACIÓN ---
// --- ESTADO DE NAVEGACIÓN ---
const vista = ref('carreras') // 'carreras' | 'semestres' | 'grupos' | 'materias' | 'tabla'
const carreraSeleccionada = ref(null)
const semestreSeleccionado = ref(null)   // { numero: X, totalGrupos: N }
const grupoSeleccionado = ref(null)      // objeto grupo
const materiaSeleccionada = ref(null)    // objeto materia
const materiasGrupo = ref([])            // materias del grupo actual

// --- CARDS DE CARRERAS ---
const busquedaCarrera = ref('')
const carrerasFiltradas = computed(() => {
  const fuente = carreras.value
  if (!busquedaCarrera.value.trim()) return fuente
  const term = busquedaCarrera.value.toLowerCase()
  return fuente.filter(c => c.nombre.toLowerCase().includes(term))
})

// --- VISTA CARRERA: GRUPOS Y DOCENTES ---
const busquedaCarreraDetalle = ref('')

const gruposCarrera = computed(() => gruposDeCarrera.value)

const semestresDisponibles = computed(() => {
  if (!gruposDeCarrera.value.length) return []
  const semestres = gruposDeCarrera.value.map(g => g.semestre).filter(Boolean)
  const unique = [...new Set(semestres)].sort((a, b) => a - b)
  return unique.map(num => ({
    numero: num,
    totalGrupos: gruposDeCarrera.value.filter(g => g.semestre == num).length
  }))
})

const gruposPorSemestre = computed(() => {
  if (!semestreSeleccionado.value) return []
  return gruposDeCarrera.value.filter(g => g.semestre == semestreSeleccionado.value.numero)
})

const gruposCarreraFiltrados = computed(() => {
  const term = busquedaCarreraDetalle.value.toLowerCase()
  if (!term) return gruposCarrera.value
  return gruposCarrera.value.filter(g =>
    g.materia.toLowerCase().includes(term) ||
    (g.docente && g.docente.toLowerCase().includes(term))
  )
})

const docentesCarrera = computed(() => {
  const mapDocentes = new Map()
  gruposCarrera.value.forEach(g => {
    const nombre = g.docente || 'Sin asignar'
    if (!mapDocentes.has(nombre)) {
      mapDocentes.set(nombre, { materias: [], grupos: [] })
    }
    mapDocentes.get(nombre).materias.push(g.materia)
    mapDocentes.get(nombre).grupos.push(g.clave_grupo)
  })
  return Array.from(mapDocentes, ([nombre, datos]) => ({
    nombre,
    materias: datos.materias,
    grupos: datos.grupos,
  }))
})

const docentesCarreraFiltrados = computed(() => {
  const term = busquedaCarreraDetalle.value.toLowerCase()
  if (!term) return docentesCarrera.value
  return docentesCarrera.value.filter(d =>
    d.nombre.toLowerCase().includes(term) ||
    d.materias.some(m => m.toLowerCase().includes(term))
  )
})

const promedioCarrera = computed(() => {
  // Aquí deberías calcularlo con datos reales del backend
  return '—'
})
const reprobadosCarrera = computed(() => {
  // Aquí deberías calcularlo con datos reales del backend
  return '—'
})

// --- MÓDULO ACTAS ---
const actas = ref([])
const cargandoActas = ref(false)

const kpisActas = computed(() => ({
  generadas: actas.value.filter(a => a.estatus === 'generada').length,
  pendientes: actas.value.filter(a => a.estatus === 'pendiente').length,
  sinCalificacion: actas.value.filter(a => a.estatus === 'sin_calificacion').length,
}))

async function cargarActas(idCarrera) {
  cargandoActas.value = true
  try {
    const { data } = await api.get(`/carreras/${idCarrera}/actas`)
    actas.value = data.actas ?? []
  } catch {
    actas.value = []
    mostrarToast('Error al cargar actas', 'error')
  } finally {
    cargandoActas.value = false
  }
}

async function generarActa(acta) {
  try {
    await api.post(`/actas/${acta.id_acta}/generar`)
    mostrarToast('Acta generada correctamente')
    await cargarActas(carreraSeleccionada.value.id_carrera)
  } catch {
    mostrarToast('Error al generar acta', 'error')
  }
}

function descargarActa(acta) {
  window.open(`/api/actas/${acta.id_acta}/pdf`, '_blank')
}

function imprimirActa(acta) {
  window.open(`/api/actas/${acta.id_acta}/pdf?print=1`, '_blank')
}

// --- MODAL DOCENTE ---
const showModalDocente = ref(false)
const docenteSeleccionado = ref(null)

const abrirModalDocente = (docente) => {
  docenteSeleccionado.value = docente
  showModalDocente.value = true
}

const cerrarModalDocente = () => {
  showModalDocente.value = false
  docenteSeleccionado.value = null
}

const getGruposPorDocente = (docente) => {
  return gruposCarrera.value.filter(g => docente.grupos.includes(g.clave_grupo))
}

// --- TABLA DE CALIFICACIONES ---
const cargando = ref(false)
const alumnos = ref([])
const busquedaControl = ref('')
const paginaActual = ref(1)
const itemsPorPagina = ref(10)
const filaActiva = ref(null)
const filasRef = ref([])

// --- ESPECIALES Y GLOBALES ---
const especiales = ref([])
const vistaTabla = ref('calificaciones') // 'calificaciones' | 'especiales'

async function cargarEspeciales(grupoId) {
  try {
    const { data } = await api.get(`/grupos/${grupoId}/especiales`)
    especiales.value = data.solicitudes ?? []
  } catch {
    mostrarToast('Error al cargar especiales', 'error')
  }
}

// --- HISTORIAL ALUMNO ---
const historialAlumno = ref([])
const cargandoHistorial = ref(false)

async function cargarHistorial(control) {
  cargandoHistorial.value = true
  historialAlumno.value = []
  try {
    const { data } = await api.get(`/alumnos/${control}/historial`)
    historialAlumno.value = data.historial ?? []
  } catch {
    historialAlumno.value = []
  } finally {
    cargandoHistorial.value = false
  }
}

// Modal alumno
const showModalAlumno = ref(false)
const alumnoSeleccionado = ref(null)
const modalTab = ref('general')

// Toast
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerToast = null
const mostrarToast = (mensaje, tipo = 'exito') => {
  if (timerToast) clearTimeout(timerToast)
  toast.value = { visible: true, mensaje, tipo }
  timerToast = setTimeout(() => { toast.value.visible = false }, 3500)
}

const sincronizarBusquedaGlobal = (valorGlobal) => {
  if (valorGlobal?.trim() && vista.value === 'tabla') {
    busquedaControl.value = valorGlobal.trim()
  }
}

const alumnosFiltrados = computed(() => {
  if (!busquedaControl.value.trim()) return alumnos.value
  return alumnos.value.filter(a =>
    a.control?.toLowerCase().includes(busquedaControl.value.toLowerCase()) ||
    a.nombre?.toLowerCase().includes(busquedaControl.value.toLowerCase())
  )
})
const totalPaginas = computed(() => Math.max(1, Math.ceil(alumnosFiltrados.value.length / itemsPorPagina.value)))
const alumnosPaginados = computed(() => {
  const inicio = (paginaActual.value - 1) * itemsPorPagina.value
  return alumnosFiltrados.value.slice(inicio, inicio + itemsPorPagina.value)
})
const paginasVisibles = computed(() => {
  const total = totalPaginas.value
  const actual = paginaActual.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const paginas = []
  const agregar = (p) => { if (!paginas.includes(p) && p >= 1 && p <= total) paginas.push(p) }
  agregar(1); agregar(actual - 1); agregar(actual); agregar(actual + 1); agregar(total)
  paginas.sort((a, b) => a - b)
  const resultado = []
  for (let i = 0; i < paginas.length; i++) {
    if (i > 0 && paginas[i] - paginas[i - 1] > 1) resultado.push('...')
    resultado.push(paginas[i])
  }
  return resultado
})
const promedioGeneral = computed(() => {
  const completos = alumnos.value.filter(a => calcularFinal(a) !== null)
  if (!completos.length) return '0.0'
  return (completos.reduce((acc, a) => acc + Number(calcularFinal(a)), 0) / completos.length).toFixed(1)
})
const totalReprobados = computed(() => alumnos.value.filter(a => { const f = calcularFinal(a); return f !== null && Number(f) < 60 }).length)
const totalNC = computed(() => alumnos.value.filter(a => esNC(a)).length)

// Helpers
const calcularFinal = (a) => {
  const unidades = [a.u1, a.u2, a.u3, a.u4, a.u5]
  if (unidades.every(v => v !== null && v !== '' && !isNaN(v))) {
    return (unidades.reduce((acc, val) => acc + Number(val), 0) / 5).toFixed(1)
  }
  return null
}


const esNC = (a) => {
  return [a.u1, a.u2, a.u3, a.u4, a.u5].every(v => !Number(v))
}

const iniciales = (nombre) => nombre ? nombre.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase() : '?'
const claseNota = (val) => {
  const n = Number(val)
  if (!val && val !== 0) return ''
  if (n >= 90) return 'nota-excelente'
  if (n >= 70) return 'nota-bien'
  if (n >= 60) return 'nota-regular'
  return 'nota-baja'
}
const clasePromedio = (val) => {
  const n = Number(val)
  if (n >= 90) return 'promedio-excelente'
  if (n >= 70) return 'promedio-bien'
  if (n >= 60) return 'promedio-regular'
  return 'promedio-bajo'
}
const colorNota = (n) => {
  if (n >= 90) return '#16A34A'
  if (n >= 70) return '#1B396A'
  if (n >= 60) return '#F59E0B'
  return '#DC2626'
}

// Navegación entre vistas
const seleccionarCarrera = async (carrera) => {
  carreraSeleccionada.value = carrera
  cargandoGruposCarrera.value = true
  gruposDeCarrera.value = []
  try {
    const { data } = await api.get(`/carreras/${carrera.id_carrera}/grupos`)
    gruposDeCarrera.value = data.grupos ?? []
    vista.value = 'semestres'
    // Cargar actas en segundo plano
    cargarActas(carrera.id_carrera)
  } catch (err) {
    mostrarToast('Error al cargar grupos de la carrera', 'error')
    vista.value = 'carreras'
  } finally {
    cargandoGruposCarrera.value = false
  }
}


const seleccionarSemestre = (semestre) => {
  semestreSeleccionado.value = semestre
  vista.value = 'grupos'
}

const seleccionarGrupoParaMaterias = async (grupo) => {
  grupoSeleccionado.value = grupo
  cargandoGruposCarrera.value = true
  try {
    const { data } = await api.get(`/grupos/${grupo.id_grupo}/materias`)
    materiasGrupo.value = data.materias ?? data
    vista.value = 'materias'
  } catch (err) {
    mostrarToast('Error al cargar materias del grupo', 'error')
    vista.value = 'grupos'
  } finally {
    cargandoGruposCarrera.value = false
  }
}

const seleccionarMateria = async (materia) => {
  materiaSeleccionada.value = materia
  vista.value = 'tabla'
  paginaActual.value = 1
  // Cargar calificaciones de la materia en el grupo actual
  await cargarCalificacionesGrupo(grupoSeleccionado.value.id_grupo, materia.id_materia)
  cargarEspeciales(grupoSeleccionado.value.id_grupo)
}

const volverACarreras = () => {
  carreraSeleccionada.value = null
  semestreSeleccionado.value = null
  grupoSeleccionado.value = null
  materiaSeleccionada.value = null
  gruposDeCarrera.value = []
  actas.value = []
  vista.value = 'carreras'
}




async function cargarCalificacionesGrupo(grupoId, materiaId = null) {
  cargando.value = true
  try {
    const params = { grupo: grupoId }
    if (materiaId) params.materia = materiaId
    const data = await getCalificacionesGrupo(params)
    alumnos.value = data.alumnos ?? data
  } catch {
    alumnos.value = []
    mostrarToast('Error al cargar calificaciones', 'error')
  } finally {
    cargando.value = false
  }
}

// Modal Alumno
const abrirModalAlumno = (alumno) => {
  alumnoSeleccionado.value = { ...alumno }
  modalTab.value = 'general'
  showModalAlumno.value = true
  cargarHistorial(alumno.control)
}

const cerrarModalAlumno = () => {
  showModalAlumno.value = false
  alumnoSeleccionado.value = null
}
const guardarDesdeModal = async () => {
  if (!alumnoSeleccionado.value) return
  cargando.value = true
  try {
    const idx = alumnos.value.findIndex(a => a.control === alumnoSeleccionado.value.control)
    if (idx !== -1) {
      alumnos.value[idx].u1 = alumnoSeleccionado.value.u1
      alumnos.value[idx].u2 = alumnoSeleccionado.value.u2
      alumnos.value[idx].u3 = alumnoSeleccionado.value.u3
      alumnos.value[idx].u4 = alumnoSeleccionado.value.u4
      alumnos.value[idx].u5 = alumnoSeleccionado.value.u5
    }
    await guardarCalificaciones([alumnoSeleccionado.value])
    mostrarToast('Calificaciones guardadas correctamente')
  } catch {
    mostrarToast('Error al guardar', 'error')
  } finally {
    cargando.value = false
  }
}

// Acciones tabla
const guardarFila = async (alumno) => {
  cargando.value = true
  try { await guardarCalificaciones([alumno]); mostrarToast('Calificaciones guardadas') }
  catch { mostrarToast('Error al guardar.', 'error') }
  finally { cargando.value = false }
}
const guardarTodo = async () => {
  cargando.value = true
  try { await guardarCalificaciones(alumnos.value); mostrarToast('Todas las calificaciones guardadas') }
  catch { mostrarToast('Error al guardar.', 'error') }
  finally { cargando.value = false }
}

// Guardado por unidad
const guardarUnidad = async (unidad) => {
  if (!materiaSeleccionada.value) return
  const campo = `u${unidad}`
  const payload = {
    materia_id: materiaSeleccionada.value.id_materia,
    unidad: unidad,
    calificaciones: alumnos.value
      .filter(a => a[campo] !== null && a[campo] !== '' && !isNaN(a[campo]))
      .map(a => ({
        alumno_id: a.id_alumno, // Ajusta si tu backend usa otro identificador
        calificacion: Number(a[campo])
      }))
  }

  if (payload.calificaciones.length === 0) {
    mostrarToast('No hay calificaciones para guardar en esta unidad', 'error')
    return
  }

  cargando.value = true
  try {
    await api.post('/calificaciones/unidad', payload)
    mostrarToast(`Unidad ${unidad} guardada correctamente`)
  } catch (e) {
    console.error(e)
    mostrarToast(`Error al guardar unidad ${unidad}`, 'error')
  } finally {
    cargando.value = false
  }
}

// Atajo de teclado: Enter en una celda guarda esa unidad
const guardarUnidadDesdeTeclado = (unidad) => {
  guardarUnidad(unidad)
}

const exportar = () => {
  const datos = alumnosFiltrados.value
  if (!datos.length) return mostrarToast('No hay datos para exportar', 'error')

  const clave = grupoSeleccionado.value?.clave_grupo ?? 'Grupo'
  const materia = materiaSeleccionada.value?.nombre ?? ''
  const fecha = new Date().toLocaleDateString('es-MX')

  // Fila de encabezado informativo
  const info = [[`Calificaciones — ${clave} ${materia}`, '', '', '', '', '', ''], [`Fecha: ${fecha}`, '', '', '', '', '', ''], []]

  const filas = datos.map(a => {
    const final = calcularFinal(a)
    return {
      'No. Control': a.control,
      'Nombre': a.nombre,
      'U1': a.u1 ?? '',
      'U2': a.u2 ?? '',
      'U3': a.u3 ?? '',
      'U4': a.u4 ?? '',
      'U5': a.u5 ?? '',
      'Final': final ?? '',
      'Estado': final === null ? 'Sin calificar'
               : Number(final) >= 90 ? 'Excelente'
               : Number(final) >= 80 ? 'Bien'
               : Number(final) >= 60 ? 'Regular'
               : 'Reprobado'
    }
  })

  const ws = XLSX.utils.aoa_to_sheet(info)
  XLSX.utils.sheet_add_json(ws, filas, { origin: 'A4' })

  // Ancho de columnas
  ws['!cols'] = [
    { wch: 14 }, { wch: 30 }, { wch: 14 },
    { wch: 14 }, { wch: 14 }, { wch: 8 }, { wch: 14 }
  ]

  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, clave.slice(0, 31))
  XLSX.writeFile(wb, `calificaciones_${clave}_${new Date().toISOString().slice(0,10)}.xlsx`)
  mostrarToast('Excel exportado correctamente')
}

// Paginación
const cambiarPagina = (nuevaPagina) => {
  if (nuevaPagina < 1 || nuevaPagina > totalPaginas.value) return
  paginaActual.value = nuevaPagina
  filaActiva.value = null
  filasRef.value = []
}
const cambiarItemsPorPagina = (nuevoValor) => {
  itemsPorPagina.value = Number(nuevoValor)
  paginaActual.value = 1
  filaActiva.value = null
  filasRef.value = []
}
let debounceTimer = null
const buscarEnTiempoReal = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => { paginaActual.value = 1 }, 300)
}
const navegarTeclado = (e) => {
  if (vista.value !== 'tabla') return
  const max = alumnosPaginados.value.length - 1
  if (e.key === 'ArrowDown') {
    e.preventDefault()
    filaActiva.value = Math.min((filaActiva.value ?? -1) + 1, max)
    nextTick(() => filasRef.value[filaActiva.value]?.focus())
  } else if (e.key === 'ArrowUp') {
    e.preventDefault()
    filaActiva.value = Math.max((filaActiva.value ?? 1) - 1, 0)
    nextTick(() => filasRef.value[filaActiva.value]?.focus())
  }
}
const atajoGlobal = (e) => {
  if (e.ctrlKey && e.key === 's' && vista.value === 'tabla') {
    e.preventDefault()
    guardarTodo()
  }
}

onMounted(async () => {
  try { await cargarCatalogos(['periodos', 'carreras', 'materias', 'grupos']) }
  catch (err) { console.error(err) }
  window.addEventListener('keydown', atajoGlobal)
})
onUnmounted(() => window.removeEventListener('keydown', atajoGlobal))
const reintentarCatalogos = () => cargarCatalogos(['periodos', 'carreras', 'materias', 'grupos'])

watch(busquedaControl, () => { paginaActual.value = 1 })
watch(totalPaginas, (nuevoTotal) => { if (paginaActual.value > nuevoTotal) paginaActual.value = nuevoTotal })
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* ============================================= */
/* ESTILOS GLOBALES - PALETA OFICIAL SICE       */
/* ============================================= */
.calificaciones-page {
  width: 100%;
  max-width: 100%;
  font-family: 'Montserrat', sans-serif;
  box-sizing: border-box;
  padding: 1rem 1rem 2rem;
}

.semestre-badge {
  display: inline-block;
  background: #EDE9FE;
  color: #7C3AED;
  font-weight: 700;
  font-size: 0.78rem;
  padding: 2px 10px;
  border-radius: 12px;
  min-width: 24px;
  text-align: center;
}

.alerta-error-catalogos { display: flex; align-items: center; gap: 0.6rem; background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; border-radius: 10px; padding: 0.8rem 1.2rem; margin-bottom: 1rem; font-size: 0.875rem; font-weight: 600; }
.btn-reintentar { margin-left: auto; background: #DC2626; color: #fff; border: none; border-radius: 6px; padding: 4px 12px; font-size: 0.8rem; font-weight: 700; cursor: pointer; }
.btn-reintentar:hover { background: #b91c1c; }
.barra-carga { position: fixed; top: 74px; left: 0; right: 0; height: 3px; z-index: 1001; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: linear-gradient(90deg, #1B396A, #1D4ED8, #1B396A); background-size: 200% 100%; animation: carga-anim 1.4s linear infinite; width: 100%; }
@keyframes carga-anim { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
.breadcrumb { color: #6B7280; font-size: 0.875rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem; }
.breadcrumb .sep { color: #E5E7EB; }
.breadcrumb .activo { color: #1B396A; font-weight: 600; }
.breadcrumb-link { color: #6B7280; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #1B396A; }
.encabezado-seccion { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.titulo-pagina { color: #1A1A1A; font-size: 1.9rem; font-weight: 800; margin: 0 0 0.25rem; font-family: 'Montserrat', sans-serif;}
.subtitulo { color: #6B7280; font-size: 0.9rem; margin: 0; }

/* ============================================ */
/* CARDS DE CARRERAS (ESTILO LIVERPOOL)         */
/* ============================================ */
.carreras-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
.carrera-card { position: relative; border-radius: 20px; overflow: hidden; color: white; height: 220px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 10px 25px rgba(0,0,0,0.2); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; background-size: cover; background-position: center; }
.carrera-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.3); }
.carrera-card-badge { position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border-radius: 30px; padding: 4px 14px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; }
.carrera-card-body { padding: 25px 20px 0; display: flex; align-items: flex-start; gap: 15px; }
.carrera-card-icono { width: 50px; height: 50px; border-radius: 50%; background: rgba(255,255,255,0.25); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.carrera-card-info { flex: 1; }
.carrera-card-nombre { font-size: 1.2rem; font-weight: 700; margin: 0 0 8px; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
.carrera-card-stats { font-size: 0.85rem; display: flex; gap: 8px; flex-wrap: wrap; opacity: 0.9; }
.stat-sep { opacity: 0.5; }
.carrera-card-footer { background: rgba(0,0,0,0.15); padding: 12px 20px; font-weight: 600; font-size: 0.9rem; backdrop-filter: blur(5px); }
.ver-detalles { transition: margin-left 0.2s; }
.carrera-card:hover .ver-detalles { margin-left: 5px; }
.sin-resultados-carreras { grid-column: 1 / -1; text-align: center; padding: 3rem; color: #9CA3AF; }

/* ============================================ */
/* VISTA CARRERA - MINI RESUMEN                */
/* ============================================ */
.mini-resumen-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
.mini-resumen-item { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 1rem; text-align: center; }
.mini-resumen-numero { display: block; font-size: 2rem; font-weight: 800; color: #1A1A1A; }
.mini-resumen-etiqueta { font-size: 0.8rem; color: #6B7280; font-weight: 600; }
.grupos-seccion { margin-top: 0.5rem; }

/* ============================================ */
/* TABLA DE GRUPOS (ESTILO DIFERENCIADO)        */
/* ============================================ */
.filtros-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 0.9rem 1.4rem; margin-bottom: 1.5rem; width: 100%; box-sizing: border-box; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
.filtros-container { display: flex; align-items: center; justify-content: space-between; gap: 1rem; width: 100%; flex-wrap: wrap; }
.busqueda-wrapper { position: relative; display: flex; align-items: center; flex: 1; max-width: 450px; }
.busqueda-control { display: flex; align-items: center; flex: 1; gap: 8px; background: #F5F5F5; border: 1px solid #E5E7EB; border-radius: 8px; padding: 0 8px 0 12px; transition: border-color 0.2s; }
.busqueda-control:focus-within { border-color: #1B396A; background: #DBEAFE; }
.icono-lupa { color: #6B7280; flex-shrink: 0; }
.input-busqueda-control { border: none; background: transparent; padding: 10px 0; font-size: 0.875rem; font-family: inherit; outline: none; width: 100%; color: #1A1A1A; }
.btn-limpiar-filtros { display: flex; align-items: center; gap: 6px; background: #FEF2F2; color: #DC2626; border: none; padding: 8px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
.btn-limpiar-filtros:hover { background: #FECACA; }
.filtros-acciones { display: flex; gap: 0.75rem; margin-left: auto; }

.tabla-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 1.5rem; width: 100%; box-sizing: border-box; }
.tabla-encabezado { padding: 1rem 1.4rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #E5E7EB; flex-wrap: wrap; gap: 0.75rem; }
.tabla-acciones-top { display: flex; align-items: center; gap: 0.75rem; }
.tabla-contador { font-size: 0.8rem; color: #6B7280; background: #F5F5F5; border: 1px solid #E5E7EB; padding: 4px 10px; border-radius: 20px; }
.tabla-scroll { overflow-x: auto; width: 100%; }
.tabla-califs { width: 100%; border-collapse: collapse; }
.tabla-califs th { background: #F5F5F5; padding: 10px 14px; font-size: 0.78rem; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E5E7EB; text-align: left; white-space: nowrap; }
.tabla-califs th.centrado { text-align: center; }
.tabla-califs td { padding: 8px 14px; border-bottom: 1px solid #E5E7EB; vertical-align: middle; font-size: 0.875rem; color: #1A1A1A; }
.tabla-califs td.centrado { text-align: center; }
.tabla-califs tr:hover { background: #F5F5F5; }
.tabla-califs tr.fila-activa { background: #DBEAFE; }
.tabla-califs tr.fila-reprobado { background: #FEF2F2; }
.tabla-califs tr.fila-sin-calificar { background: #F9FAFB; }
.tabla-califs tr:last-child td { border-bottom: none; }
.peso { font-weight: 400; color: #9CA3AF; font-size: 0.7rem; }
.control-chip { background: #F5F5F5; border: 1px solid #E5E7EB; border-radius: 6px; padding: 3px 8px; font-size: 0.82rem; font-weight: 700; font-family: monospace; color: #1A1A1A; display: inline-block; }
.alumno-info { display: flex; align-items: center; gap: 0.6rem; }
.alumno-avatar { width: 30px; height: 30px; border-radius: 50%; background: #DBEAFE; color: #1B396A; font-weight: 800; font-size: 0.72rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.alumno-nombre { font-weight: 600; color: #1A1A1A; }
.input-nota-wrap { display: flex; justify-content: center; }
.input-nota { width: 55px; padding: 4px 6px; border: 1.5px solid #E5E7EB; border-radius: 6px; font-size: 0.85rem; font-weight: 700; text-align: center; outline: none; background: #FFFFFF; transition: border-color 0.2s, background 0.2s; }
.input-nota:focus { border-color: #1B396A; background: #DBEAFE; }
.input-nota.nota-excelente { border-color: #16A34A; color: #16A34A; } .input-nota.nota-bien { border-color: #1B396A; color: #1B396A; } .input-nota.nota-regular { border-color: #F59E0B; color: #F59E0B; } .input-nota.nota-baja { border-color: #DC2626; color: #DC2626; }
.final-chip { display: inline-flex; align-items: center; justify-content: center; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 0.9rem; }
.promedio-excelente { background: #DCFCE7; color: #16A34A; } .promedio-bien { background: #DBEAFE; color: #1B396A; } .promedio-regular { background: #FEF3C7; color: #F59E0B; } .promedio-bajo { background: #FEF2F2; color: #DC2626; } .promedio-sin-calificar { background: #F3F4F6; color: #6B7280; }
.badge-estado { padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
.badge-estado.excelente { background: #DCFCE7; color: #16A34A; } .badge-estado.bien { background: #DBEAFE; color: #1B396A; } .badge-estado.regular { background: #FEF3C7; color: #F59E0B; } .badge-estado.reprobado { background: #FEF2F2; color: #DC2626; } .badge-estado.sin-calificar { background: #F3F4F6; color: #6B7280; }

.acciones-fila { display: flex; gap: 5px; justify-content: center; }
.btn-accion { width: 30px; height: 30px; border-radius: 7px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.15s, opacity 0.2s; flex-shrink: 0; }
.btn-accion:hover:not(:disabled) { transform: scale(1.1); }
.btn-accion:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }
.btn-accion.guardar { background: #DCFCE7; color: #16A34A; } .btn-accion.guardar:hover { background: #bbf7d0; }
.btn-accion.ver { background: #DBEAFE; color: #1B396A; } .btn-accion.ver:hover { background: #BFDBFE; }
.btn-accion.ver.grande { width: auto; padding: 0.5rem 1rem; gap: 8px; font-size: 0.9rem; font-weight: 600; border-radius: 10px; }
.acciones-cell { text-align: center; }
.acciones-cell .btn-accion { display: inline-flex; }

.sin-resultados { padding: 3rem; text-align: center; }
.sin-resultados-inner { display: flex; flex-direction: column; align-items: center; gap: 0.75rem; }
.sin-resultados-inner p { color: #9CA3AF; font-size: 0.9rem; margin: 0; }

/* PAGINACIÓN */
.paginacion-container { padding: 0.85rem 1.4rem; border-top: 1px solid #E5E7EB; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; background: #FAFAFA; }
.paginacion-izquierda { display: flex; align-items: center; gap: 8px; }
.paginacion-filas-label { font-size: 0.82rem; color: #6B7280; white-space: nowrap; }
.paginacion-centro { font-size: 0.82rem; color: #6B7280; font-weight: 500; }
.paginacion-pagina-label { white-space: nowrap; }
.paginacion-select { padding: 5px 8px; border: 1px solid #E5E7EB; border-radius: 6px; font-size: 0.82rem; font-family: inherit; color: #1A1A1A; background: #FFFFFF; outline: none; cursor: pointer; }
.paginacion-select:focus { border-color: #1B396A; }
.paginacion-controles { display: flex; align-items: center; gap: 3px; }
.btn-pag { width: 32px; height: 32px; border-radius: 7px; border: 1px solid #E5E7EB; background: #FFFFFF; color: #6B7280; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s; }
.btn-pag:hover:not(:disabled) { background: #F0F4FF; border-color: #BFDBFE; color: #1B396A; }
.btn-pag:disabled { opacity: 0.35; cursor: not-allowed; }
.paginacion-ellipsis { width: 28px; height: 32px; display: flex; align-items: center; justify-content: center; color: #9CA3AF; font-size: 0.9rem; user-select: none; }
.btn-num { min-width: 32px; height: 32px; padding: 0 6px; border-radius: 7px; border: 1px solid #E5E7EB; background: #FFFFFF; color: #374151; font-weight: 600; font-size: 0.82rem; font-family: inherit; cursor: pointer; transition: all 0.15s; display: flex; align-items: center; justify-content: center; }
.btn-num:hover { background: #F0F4FF; border-color: #BFDBFE; color: #1B396A; }
.btn-num.activa { background: #1B396A; color: #FFFFFF; border-color: #1B396A; box-shadow: 0 2px 6px rgba(27,57,106,0.3); }

/* RESUMEN TABLA */
.tabla-resumen { padding: 0.75rem 1.4rem; background: #F5F5F5; border-top: 1px solid #E5E7EB; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
.resumen-item { display: flex; align-items: center; gap: 6px; font-size: 0.8rem; }
.ri-label { color: #6B7280; } .resumen-separador { width: 1px; height: 16px; background: #E5E7EB; }
.color-rojo { color: #DC2626; font-weight: 700; } .color-verde { color: #16A34A; font-weight: 700; }

/* ATAJOS */
.atajos-info { text-align: center; color: #9CA3AF; font-size: 0.78rem; margin-bottom: 1.5rem; }
kbd { background: #E5E7EB; border-radius: 4px; padding: 1px 6px; font-family: monospace; font-size: 0.8rem; color: #1A1A1A; }

/* TOAST */
.toast { position: fixed; bottom: 2rem; right: 2rem; padding: 0.9rem 1.4rem; border-radius: 10px; font-weight: 700; font-size: 0.9rem; font-family: 'Montserrat', sans-serif; display: flex; align-items: center; gap: 0.6rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 3000; max-width: 380px; color: #FFFFFF; }
.toast.exito { background: #1B396A; } .toast.error { background: #DC2626; } .toast.info { background: #2563EB; }
.toast-slide-enter-active { transition: all 0.3s ease; } .toast-slide-leave-active { transition: all 0.25s ease; }
.toast-slide-enter-from { transform: translateY(20px); opacity: 0; } .toast-slide-leave-to { transform: translateX(100%); opacity: 0; }

/* BOTONES ESTANDARIZADOS */
.btn-primario { background: #1B396A; color: #FFFFFF; border: none; padding: 10px 18px; border-radius: 10px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 6px; cursor: pointer; font-family: inherit; transition: background 0.2s; white-space: nowrap; }
.btn-primario:hover:not(:disabled) { background: #1D4ED8; } .btn-primario:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }
.btn-secundario { background: #DBEAFE; color: #1B396A; border: none; padding: 10px 16px; border-radius: 10px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 6px; cursor: pointer; font-family: inherit; transition: background 0.2s; white-space: nowrap; }
.btn-secundario:hover:not(:disabled) { background: #BFDBFE; } .btn-secundario:disabled { opacity: 0.45; cursor: not-allowed; }
.btn-cancelar { background: #FFFFFF; color: #6B7280; border: 1px solid #E5E7EB; padding: 10px 1.4rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; cursor: pointer; font-family: inherit; transition: background 0.2s; }
.btn-cancelar:hover { background: #F5F5F5; }

/* ============================================ */
/* MODAL ALUMNO REDISEÑADO                      */
/* ============================================ */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 2000; backdrop-filter: blur(4px); }
.modal-content { background: #FFFFFF; border-radius: 20px; width: 90%; max-width: 650px; max-height: 85vh; overflow-y: auto; box-shadow: 0 25px 50px rgba(0,0,0,0.25); }

.modal-alumno-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem 1.5rem 1rem;
  background: linear-gradient(135deg, #1B396A 0%, #1D4ED8 100%);
  color: white;
  border-radius: 20px 20px 0 0;
}
.modal-alumno-avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: rgba(255,255,255,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
  font-weight: 800;
  flex-shrink: 0;
  border: 2px solid rgba(255,255,255,0.4);
}
.modal-alumno-titulo {
  flex: 1;
}
.modal-alumno-titulo h2 {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 700;
}
.modal-alumno-control {
  font-size: 0.85rem;
  opacity: 0.9;
  font-family: monospace;
}
.modal-tabs {
  display: flex;
  background: #F9FAFB;
  border-bottom: 1px solid #E5E7EB;
}
.modal-tab {
  flex: 1;
  background: none;
  border: none;
  padding: 0.75rem 1rem;
  font-size: 0.85rem;
  font-weight: 600;
  color: #6B7280;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}
.modal-tab.activa {
  color: #1B396A;
  border-bottom-color: #1B396A;
  background: #FFFFFF;
}
.modal-close-btn {
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  cursor: pointer;
  padding: 5px;
  border-radius: 8px;
  transition: background 0.2s;
}
.modal-close-btn:hover {
  background: rgba(255,255,255,0.4);
}
.modal-body {
  padding: 1.5rem;
}

/* Tab General */
.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}
.info-card {
  background: #FFFFFF;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  transition: box-shadow 0.2s;
}
.info-card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.info-icono {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.info-icono.azul { background: #DBEAFE; color: #1B396A; }
.info-icono.verde { background: #DCFCE7; color: #16A34A; }
.info-icono.morado { background: #EDE9FE; color: #7C3AED; }
.info-icono.naranja { background: #FFF7ED; color: #F59E0B; }
.info-texto {
  display: flex;
  flex-direction: column;
}
.info-label {
  font-size: 0.75rem;
  color: #6B7280;
  font-weight: 600;
}
.info-valor {
  font-weight: 700;
  color: #1A1A1A;
  font-size: 0.95rem;
}

.estatus-card {
  background: #FFFFFF;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 0.8rem;
}
.estatus-card.aprobado { border-left: 4px solid #16A34A; }
.estatus-card.reprobado { border-left: 4px solid #DC2626; }
.estatus-card.sin-calificar { border-left: 4px solid #9CA3AF; }
.estatus-icono {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.estatus-card.aprobado .estatus-icono { background: #DCFCE7; color: #16A34A; }
.estatus-card.reprobado .estatus-icono { background: #FEF2F2; color: #DC2626; }
.estatus-card.sin-calificar .estatus-icono { background: #F3F4F6; color: #9CA3AF; }
.estatus-texto {
  display: flex;
  flex-direction: column;
}
.estatus-texto span {
  font-size: 0.75rem;
  color: #6B7280;
  font-weight: 600;
}
.estatus-texto strong {
  font-size: 1rem;
  color: #1A1A1A;
}

/* Tab Calificaciones */
.calificaciones-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}
.cal-card {
  background: #FFFFFF;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  padding: 1rem;
  text-align: center;
}
.cal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}
.cal-titulo {
  font-weight: 600;
  font-size: 0.85rem;
  color: #1A1A1A;
}
.cal-peso {
  font-size: 0.75rem;
  color: #6B7280;
  font-weight: 600;
}
.input-nota.grande {
  width: 80px;
  padding: 8px;
  font-size: 1rem;
  margin: 0.5rem auto;
}
.cal-barra {
  height: 8px;
  background: #E5E7EB;
  border-radius: 4px;
  overflow: hidden;
  margin-top: 0.5rem;
}
.cal-barra-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.4s ease;
}
.final-display {
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  padding: 1rem;
  text-align: center;
  margin-bottom: 1rem;
}
.final-label {
  font-size: 0.8rem;
  color: #6B7280;
  font-weight: 600;
  margin-bottom: 0.3rem;
}
.final-valor {
  font-size: 2rem;
  font-weight: 800;
  color: #1A1A1A;
}
.final-valor.sin-calificar {
  color: #9CA3AF;
}

/* ============================================ */
/* TARJETAS DE DOCENTES Y MODAL DOCENTE         */
/* ============================================ */
.docentes-seccion { margin-top: 2rem; }
.docentes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.2rem; }
.docente-card { background: #FFFFFF; border-radius: 16px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.04); transition: transform 0.2s, box-shadow 0.2s; display: flex; flex-direction: column; overflow: hidden; }
.docente-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
.docente-card-body { padding: 1.4rem 1.4rem 0.8rem; display: flex; flex-direction: column; align-items: center; text-align: center; flex: 1; }
.docente-avatar { width: 56px; height: 56px; border-radius: 50%; background: #1B396A; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; margin-bottom: 0.75rem; }
.docente-nombre { font-size: 1.05rem; font-weight: 700; color: #1A1A1A; margin: 0 0 0.5rem; }
.docente-materias { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.4rem; margin-bottom: 0.8rem; }
.materia-tag { background: #DBEAFE; color: #1B396A; padding: 2px 8px; border-radius: 12px; font-size: 0.72rem; font-weight: 600; }
.docente-stats { font-size: 0.85rem; color: #6B7280; margin-bottom: 0.5rem; }
.docente-card-footer { background: #F9FAFB; padding: 0.8rem 1.4rem; text-align: center; border-top: 1px solid #E5E7EB; }
.docente-card-footer .btn-accion.ver.grande { width: 100%; justify-content: center; }

.modal-ver-docente { max-width: 550px; }
.modal-titulo { font-size: 1.2rem; font-weight: 700; color: #1A1A1A; margin: 0; }
.docente-modal-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
.docente-modal-avatar { width: 60px; height: 60px; border-radius: 50%; background: #1B396A; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 800; flex-shrink: 0; }
.docente-modal-info h4 { margin: 0 0 4px; font-size: 1.1rem; color: #1A1A1A; }
.docente-modal-carrera { font-size: 0.85rem; color: #6B7280; }
.docente-modal-detalles { margin-bottom: 1.5rem; }
.docente-grupos h4 { font-size: 0.95rem; font-weight: 700; color: #1A1A1A; margin: 1rem 0 0.5rem; }

/* Responsive */
@media (max-width: 900px) {
  .mini-resumen-grid { grid-template-columns: repeat(2, 1fr); }
  .docentes-grid { grid-template-columns: repeat(2, 1fr); }
  .calificaciones-grid { grid-template-columns: 1fr; }
  .info-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
  .carreras-grid { grid-template-columns: 1fr; }
  .mini-resumen-grid { grid-template-columns: 1fr; }
  .filtros-container { flex-direction: column; align-items: stretch; }
  .tabla-encabezado { flex-direction: column; align-items: stretch; }
  .paginacion-container { flex-direction: column; align-items: center; }
  .modal-content { width: 95%; }
  .docentes-grid { grid-template-columns: 1fr; }
}

/* KPIs PANTALLA 3 */
.kpis-grupo-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}
.kpi-item {
  background: #FFFFFF;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  padding: 1rem;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.kpi-valor {
  display: block;
  font-size: 1.8rem;
  font-weight: 800;
  color: #1A1A1A;
}
.kpi-label {
  font-size: 0.78rem;
  color: #6B7280;
  font-weight: 600;
  margin-top: 4px;
  display: block;
}
.kpi-verde { color: #16A34A; }
.kpi-rojo  { color: #DC2626; }
.kpi-gris  { color: #6B7280; }

@media (max-width: 640px) {
  .kpis-grupo-grid { grid-template-columns: repeat(2, 1fr); }
}

/* Sub‑pestañas de la pantalla 3 */
.subtab-nav {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}
.subtab-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 8px;
  border: 1px solid #E5E7EB;
  background: #FFFFFF;
  color: #6B7280;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.subtab-btn:hover {
  background: #F0F4FF;
  color: #1B396A;
  border-color: #BFDBFE;
}
.subtab-btn.activo {
  background: #1B396A;
  color: white;
  border-color: #1B396A;
}

/* Badges para tipo de examen */
.tipo-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.72rem;
  font-weight: 700;
}
.tipo-especial {
  background: #EDE9FE;
  color: #7C3AED;
}
.tipo-global {
  background: #FEF3C7;
  color: #B45309;
}

/* Cards de semestres, grupos, materias */
.semestres-grid, .grupos-grid, .materias-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.card {
  background: white;
  border-radius: 12px;
  padding: 1.25rem;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border: 1px solid #E5E7EB;
}

.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.semestre-card {
  text-align: center;
}

.semestre-numero {
  font-size: 2.5rem;
  font-weight: 800;
  color: #1B396A;
  margin-bottom: 0.5rem;
}

.card-titulo {
  font-size: 1rem;
  font-weight: 700;
  color: #333;
  margin: 0 0 0.5rem 0;
}

.card-info {
  font-size: 0.8rem;
  color: #6B7280;
  margin-bottom: 1rem;
}

.card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #E5E7EB;
}

.card-badge {
  font-size: 0.65rem;
  font-weight: 700;
  color: #1B396A;
  background: #DBEAFE;
  padding: 4px 10px;
  border-radius: 20px;
}

.card-arrow {
  color: #6B7280;
}

.card:hover .card-arrow {
  color: #1B396A;
}

.grupo-nombre {
  font-size: 1.8rem;
  font-weight: 800;
  color: #1A1A1A;
  margin-bottom: 0.5rem;
}

.grupo-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.8rem;
  color: #4F4F4F;
}

.materia-nombre {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1A1A1A;
  margin-bottom: 0.25rem;
}

.materia-docente {
  font-size: 0.8rem;
  color: #6B7280;
  margin-bottom: 0.5rem;
}

/* Para que las secciones de título se vean bien */
.seccion-titulo-wrap {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
}
.seccion-titulo {
  font-size: 1rem;
  font-weight: 700;
  color: #1A1A1A;
  margin: 0;
}

/* Barra de guardado por unidad */
.guardar-unidades-bar {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1.4rem;
  background: #F9FAFB;
  border-bottom: 1px solid #E5E7EB;
  flex-wrap: wrap;
}
.guardar-unidades-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #6B7280;
  margin-right: 0.25rem;
}
.btn-unidad {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 5px 12px;
  border-radius: 6px;
  border: 1px solid #E5E7EB;
  background: #FFFFFF;
  color: #1B396A;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-family: 'Montserrat', sans-serif;
}
.btn-unidad:hover:not(:disabled) {
  background: #DBEAFE;
  border-color: #BFDBFE;
}
.btn-unidad:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

</style>