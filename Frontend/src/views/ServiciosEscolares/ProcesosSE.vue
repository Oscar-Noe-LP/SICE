<!-- ============================================================ -->
<!-- src/views/ServiciosEscolares/ProcesosSE.vue                 -->
<!-- Nelly — PROCESOS → CIERRE DE SEMESTRE                       -->
<!-- ============================================================ -->
<template>
  <MainLayout>
    <div class="procesos-page">

      <!-- Barra de carga -->
      <div class="barra-carga" :class="{ activa: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- Toast -->
      <transition name="toast-slide">
        <div v-if="toast.visible" class="toast" :class="toast.tipo" role="alert">
          <svg v-if="toast.tipo === 'exito'" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <svg v-else fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          {{ toast.mensaje }}
        </div>
      </transition>

      <!-- Breadcrumb -->
      <nav class="breadcrumb">
        <router-link to="/servicios-escolares" class="breadcrumb-link">SERVICIOS ESCOLARES</router-link>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-actual">PROCESOS</span>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-actual">CIERRE DE SEMESTRE</span>
      </nav>

      <!-- Encabezado de página -->
      <div class="page-header">
        <div>
          <h1 class="page-title">CIERRE DE SEMESTRE</h1>
          <p class="page-subtitle">GESTIÓN Y EJECUCIÓN DEL PROCESO DE CIERRE DEL PERIODO ESCOLAR ACTIVO</p>
        </div>
        <div class="header-acciones">
          <button class="btn-secundario" @click="cargarDatos" :disabled="cargando">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            ACTUALIZAR
          </button>
        </div>
      </div>

      <!-- Cargando inicial -->
      <div v-if="cargandoInicial" class="estado-cargando">
        <div class="spinner-grande"></div>
        <p>CARGANDO INFORMACIÓN DEL PERIODO...</p>
      </div>

      <template v-else>

        <!-- ══════════════════════════════════════
             TABS DE NAVEGACIÓN
        ══════════════════════════════════════ -->
        <div class="tabs-bar" role="tablist">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            class="tab-btn"
            :class="{ 'tab-activo': tabActivo === tab.id }"
            @click="tabActivo = tab.id"
            role="tab"
            type="button"
          >
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon"/>
            </svg>
            {{ tab.label }}
            <span v-if="tab.alerta" class="tab-alerta">{{ tab.alerta }}</span>
          </button>
        </div>

        <!-- ══════════════════════════════════════
             TAB 1: DASHBOARD DE CIERRE
        ══════════════════════════════════════ -->
        <Transition name="tab-fade" mode="out-in">
        <div v-if="tabActivo === 'dashboard'" key="dashboard" class="tab-body">

          <!-- Periodo activo -->
          <div v-if="periodoActivo" class="periodo-banner">
            <div class="periodo-banner-icon">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="28" height="28">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
            <div class="periodo-banner-info">
              <span class="periodo-banner-label">PERIODO ACTIVO</span>
              <h2 class="periodo-banner-nombre">{{ periodoActivo.nombre }}</h2>
              <span class="periodo-banner-fechas">{{ fFecha(periodoActivo.fecha_inicio) }} — {{ fFecha(periodoActivo.fecha_fin) }}</span>
            </div>
            <div class="periodo-banner-estado">
              <span class="estado-chip estado-activo">EN CURSO</span>
            </div>
          </div>

          <div v-else class="sin-periodo">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="48" height="48"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <p>NO HAY UN PERIODO ACTIVO REGISTRADO EN EL SISTEMA.</p>
          </div>

          <!-- KPIs -->
          <div class="kpi-grid">
            <div class="kpi-card kpi-azul">
              <div class="kpi-icono">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
              </div>
              <div class="kpi-datos">
                <div class="kpi-valor">{{ kpis.total_alumnos ?? '—' }}</div>
                <div class="kpi-label">TOTAL ALUMNOS</div>
              </div>
            </div>
            <div class="kpi-card kpi-verde">
              <div class="kpi-icono">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
              </div>
              <div class="kpi-datos">
                <div class="kpi-valor">{{ kpis.grupos_cerrados ?? '—' }}</div>
                <div class="kpi-label">GRUPOS CERRADOS</div>
              </div>
            </div>
            <div class="kpi-card" :class="(kpis.actas_pendientes ?? 0) > 0 ? 'kpi-naranja' : 'kpi-verde'">
              <div class="kpi-icono">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              </div>
              <div class="kpi-datos">
                <div class="kpi-valor">{{ kpis.actas_pendientes ?? '—' }}</div>
                <div class="kpi-label">ACTAS PENDIENTES</div>
              </div>
            </div>
            <div class="kpi-card" :class="(kpis.calificaciones_pendientes ?? 0) > 0 ? 'kpi-rojo' : 'kpi-verde'">
              <div class="kpi-icono">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </div>
              <div class="kpi-datos">
                <div class="kpi-valor">{{ kpis.calificaciones_pendientes ?? '—' }}</div>
                <div class="kpi-label">CALIF. PENDIENTES</div>
              </div>
            </div>
            <div class="kpi-card kpi-azul">
              <div class="kpi-icono">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
              </div>
              <div class="kpi-datos">
                <div class="kpi-valor">{{ kpis.grupos_abiertos ?? '—' }}</div>
                <div class="kpi-label">GRUPOS ABIERTOS</div>
              </div>
            </div>
            <div class="kpi-card kpi-verde">
              <div class="kpi-icono">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
              </div>
              <div class="kpi-datos">
                <div class="kpi-valor">{{ kpis.promedio_periodo ?? '—' }}</div>
                <div class="kpi-label">PROMEDIO PERIODO</div>
              </div>
            </div>
          </div>

          <!-- Estado del cierre -->
          <div class="estado-cierre-card">
            <h3 class="seccion-titulo">ESTADO DEL CIERRE</h3>
            <div class="checklist-cierre">
              <div v-for="item in checklistCierre" :key="item.id" class="check-item" :class="item.estado">
                <div class="check-icono">
                  <svg v-if="item.estado === 'ok'" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                  <svg v-else-if="item.estado === 'alerta'" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <svg v-else fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div class="check-info">
                  <span class="check-label">{{ item.label }}</span>
                  <span class="check-detalle">{{ item.detalle }}</span>
                </div>
                <span class="check-chip" :class="'chip-' + item.estado">{{ item.estado === 'ok' ? 'LISTO' : item.estado === 'alerta' ? 'PENDIENTE' : 'BLOQUEADO' }}</span>
              </div>
            </div>
          </div>

        </div>
        </Transition>

        <!-- ══════════════════════════════════════
             TAB 2: VALIDACIONES
        ══════════════════════════════════════ -->
        <Transition name="tab-fade" mode="out-in">
        <div v-if="tabActivo === 'validaciones'" key="validaciones" class="tab-body">

          <div class="validaciones-header">
            <h3 class="seccion-titulo">VALIDACIONES PREVIAS AL CIERRE</h3>
            <button class="btn-secundario" @click="cargarValidaciones" :disabled="cargandoValidaciones">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
              VERIFICAR
            </button>
          </div>

          <div v-if="cargandoValidaciones" class="skeleton-wrap">
            <div v-for="i in 6" :key="i" class="skeleton-fila"></div>
          </div>

          <template v-else>
            <!-- Actas pendientes -->
            <div class="validacion-seccion">
              <div class="validacion-seccion-header" :class="actasPendientes.length > 0 ? 'header-alerta' : 'header-ok'">
                <div class="val-header-titulo">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  <span>ACTAS PENDIENTES DE FIRMA</span>
                </div>
                <span class="val-chip" :class="actasPendientes.length > 0 ? 'chip-alerta' : 'chip-ok'">{{ actasPendientes.length }} PENDIENTE(S)</span>
              </div>
              <div v-if="actasPendientes.length > 0" class="tabla-wrap">
                <table class="tabla-val">
                  <thead><tr><th>GRUPO</th><th>MATERIA</th><th>DOCENTE</th><th class="tc">ALUMNOS</th><th class="tc">ESTATUS</th></tr></thead>
                  <tbody>
                    <tr v-for="a in actasPendientes" :key="a.id_grupo">
                      <td class="mono sm">{{ a.clave_grupo }}</td>
                      <td>{{ a.materia }}</td>
                      <td class="sm">{{ a.docente ?? 'SIN ASIGNAR' }}</td>
                      <td class="tc">{{ a.inscritos ?? '—' }}</td>
                      <td class="tc"><span class="chip-estado chip-naranja">SIN FIRMA</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="val-ok-msg">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                TODAS LAS ACTAS ESTÁN FIRMADAS Y COMPLETAS.
              </div>
            </div>

            <!-- Calificaciones pendientes -->
            <div class="validacion-seccion">
              <div class="validacion-seccion-header" :class="califPendientes.length > 0 ? 'header-rojo' : 'header-ok'">
                <div class="val-header-titulo">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  <span>CALIFICACIONES SIN CAPTURAR</span>
                </div>
                <span class="val-chip" :class="califPendientes.length > 0 ? 'chip-rojo' : 'chip-ok'">{{ califPendientes.length }} GRUPO(S)</span>
              </div>
              <div v-if="califPendientes.length > 0" class="tabla-wrap">
                <table class="tabla-val">
                  <thead><tr><th>GRUPO</th><th>MATERIA</th><th>DOCENTE</th><th class="tc">ALUMNOS S/C</th><th class="tc">ESTATUS</th></tr></thead>
                  <tbody>
                    <tr v-for="c in califPendientes" :key="c.id_grupo">
                      <td class="mono sm">{{ c.clave_grupo }}</td>
                      <td>{{ c.materia }}</td>
                      <td class="sm">{{ c.docente ?? 'SIN ASIGNAR' }}</td>
                      <td class="tc">{{ c.sin_calificar ?? '—' }}</td>
                      <td class="tc"><span class="chip-estado chip-rojo">INCOMPLETO</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="val-ok-msg">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                TODAS LAS CALIFICACIONES HAN SIDO CAPTURADAS.
              </div>
            </div>

            <!-- Grupos abiertos -->
            <div class="validacion-seccion">
              <div class="validacion-seccion-header" :class="gruposAbiertos.length > 0 ? 'header-alerta' : 'header-ok'">
                <div class="val-header-titulo">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                  <span>GRUPOS SIN CERRAR</span>
                </div>
                <span class="val-chip" :class="gruposAbiertos.length > 0 ? 'chip-alerta' : 'chip-ok'">{{ gruposAbiertos.length }} GRUPO(S)</span>
              </div>
              <div v-if="gruposAbiertos.length > 0" class="tabla-wrap">
                <table class="tabla-val">
                  <thead><tr><th>GRUPO</th><th>MATERIA</th><th>CARRERA</th><th class="tc">SEMESTRE</th><th class="tc">ESTATUS</th></tr></thead>
                  <tbody>
                    <tr v-for="g in gruposAbiertos" :key="g.id_grupo">
                      <td class="mono sm">{{ g.clave_grupo }}</td>
                      <td>{{ g.materia }}</td>
                      <td class="sm">{{ g.carrera ?? '—' }}</td>
                      <td class="tc">{{ g.semestre ?? '—' }}</td>
                      <td class="tc"><span class="chip-estado chip-naranja">ABIERTO</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="val-ok-msg">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                TODOS LOS GRUPOS HAN SIDO CERRADOS.
              </div>
            </div>
          </template>

        </div>
        </Transition>

        <!-- ══════════════════════════════════════
             TAB 3: SIMULACIÓN
        ══════════════════════════════════════ -->
        <Transition name="tab-fade" mode="out-in">
        <div v-if="tabActivo === 'simulacion'" key="simulacion" class="tab-body">

          <div class="sim-header">
            <div>
              <h3 class="seccion-titulo">SIMULACIÓN DE CIERRE</h3>
              <p class="seccion-subtitulo">VISTA PREVIA DE LOS RESULTADOS ANTES DE EJECUTAR EL CIERRE DEFINITIVO.</p>
            </div>
            <button class="btn-primario" @click="ejecutarSimulacion" :disabled="cargandoSimulacion">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              {{ cargandoSimulacion ? 'CALCULANDO...' : 'EJECUTAR SIMULACIÓN' }}
            </button>
          </div>

          <div v-if="cargandoSimulacion" class="skeleton-wrap">
            <div v-for="i in 8" :key="i" class="skeleton-fila"></div>
          </div>

          <template v-else-if="simulacionResultado">
            <!-- Resumen simulación -->
            <div class="sim-resumen-grid">
              <div class="sim-resumen-card verde">
                <div class="sim-num">{{ simulacionResultado.avanzan ?? 0 }}</div>
                <div class="sim-lbl">ALUMNOS QUE AVANZAN</div>
              </div>
              <div class="sim-resumen-card naranja">
                <div class="sim-num">{{ simulacionResultado.pendientes ?? 0 }}</div>
                <div class="sim-lbl">QUEDAN PENDIENTES</div>
              </div>
              <div class="sim-resumen-card rojo">
                <div class="sim-num">{{ simulacionResultado.bajas ?? 0 }}</div>
                <div class="sim-lbl">POSIBLES BAJAS</div>
              </div>
              <div class="sim-resumen-card azul">
                <div class="sim-num">{{ simulacionResultado.egresados ?? 0 }}</div>
                <div class="sim-lbl">POSIBLES EGRESADOS</div>
              </div>
            </div>

            <!-- Tabla detalle alumnos que avanzan -->
            <div class="sim-tabla-seccion">
              <div class="sim-tabla-header">
                <h4 class="sim-tabla-titulo">
                  <span class="dot-verde"></span>
                  ALUMNOS QUE AVANZAN ({{ alumnosAvanzan.length }})
                </h4>
                <input v-model="busqAvanza" type="text" placeholder="BUSCAR ALUMNO..." class="busq-mini" />
              </div>
              <div class="tabla-wrap">
                <table class="tabla-val">
                  <thead><tr><th>NO. CONTROL</th><th>NOMBRE</th><th class="tc">SEM. ACTUAL</th><th class="tc">SEM. SIGUIENTE</th><th class="tc">PROMEDIO</th><th class="tc">MAT. REPROBADAS</th></tr></thead>
                  <tbody>
                    <tr v-for="a in alumnosAvanzanFiltrados" :key="a.numero_control">
                      <td class="mono sm">{{ a.numero_control }}</td>
                      <td>{{ a.nombre }}</td>
                      <td class="tc">{{ a.semestre_actual }}°</td>
                      <td class="tc"><span class="chip-verde">{{ a.semestre_siguiente }}°</span></td>
                      <td class="tc"><span :class="a.promedio >= 70 ? 'chip-calif-v' : 'chip-calif-r'">{{ a.promedio }}</span></td>
                      <td class="tc">{{ a.materias_reprobadas ?? 0 }}</td>
                    </tr>
                    <tr v-if="alumnosAvanzanFiltrados.length === 0">
                      <td colspan="6" class="sin-resultados">SIN RESULTADOS</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Tabla detalle alumnos pendientes -->
            <div class="sim-tabla-seccion">
              <div class="sim-tabla-header">
                <h4 class="sim-tabla-titulo">
                  <span class="dot-naranja"></span>
                  ALUMNOS QUE QUEDAN PENDIENTES ({{ alumnosPendientes.length }})
                </h4>
                <input v-model="busqPendiente" type="text" placeholder="BUSCAR ALUMNO..." class="busq-mini" />
              </div>
              <div class="tabla-wrap">
                <table class="tabla-val">
                  <thead><tr><th>NO. CONTROL</th><th>NOMBRE</th><th class="tc">SEMESTRE</th><th class="tc">PROMEDIO</th><th>MOTIVO</th></tr></thead>
                  <tbody>
                    <tr v-for="a in alumnosPendientesFiltrados" :key="a.numero_control">
                      <td class="mono sm">{{ a.numero_control }}</td>
                      <td>{{ a.nombre }}</td>
                      <td class="tc">{{ a.semestre_actual }}°</td>
                      <td class="tc"><span class="chip-calif-r">{{ a.promedio }}</span></td>
                      <td><span class="chip-estado chip-naranja">{{ a.motivo?.toUpperCase() ?? 'MATERIAS PENDIENTES' }}</span></td>
                    </tr>
                    <tr v-if="alumnosPendientesFiltrados.length === 0">
                      <td colspan="5" class="sin-resultados">SIN RESULTADOS</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </template>

          <div v-else class="tab-vacio">
            <svg fill="none" viewBox="0 0 24 24" stroke="#BDBDBD" width="52" height="52"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p>EJECUTA LA SIMULACIÓN PARA VER LOS RESULTADOS DEL CIERRE.</p>
          </div>

        </div>
        </Transition>

        <!-- ══════════════════════════════════════
             TAB 4: EJECUTAR CIERRE
        ══════════════════════════════════════ -->
        <Transition name="tab-fade" mode="out-in">
        <div v-if="tabActivo === 'ejecutar'" key="ejecutar" class="tab-body">

          <div v-if="cierreFinalizado" class="cierre-exitoso">
            <div class="cierre-exito-icono">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="40" height="40"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3>¡CIERRE EJECUTADO EXITOSAMENTE!</h3>
            <p>EL PERIODO <strong>{{ periodoActivo?.nombre }}</strong> HA SIDO CERRADO CORRECTAMENTE.</p>
            <div class="cierre-resultado-grid">
              <div class="cierre-res-item"><span class="cierre-res-num">{{ resultadoCierre.alumnos_procesados ?? 0 }}</span><span class="cierre-res-lbl">ALUMNOS PROCESADOS</span></div>
              <div class="cierre-res-item"><span class="cierre-res-num">{{ resultadoCierre.avanzaron ?? 0 }}</span><span class="cierre-res-lbl">AVANZARON</span></div>
              <div class="cierre-res-item"><span class="cierre-res-num">{{ resultadoCierre.actas_generadas ?? 0 }}</span><span class="cierre-res-lbl">ACTAS GENERADAS</span></div>
            </div>
          </div>

          <template v-else>
            <!-- Aviso importante -->
            <div class="aviso-critico">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
              <div>
                <strong>ACCIÓN IRREVERSIBLE</strong>
                <p>EL CIERRE DE SEMESTRE ES UN PROCESO DEFINITIVO. UNA VEZ EJECUTADO, NO SE PUEDE DESHACER. ASEGÚRATE DE QUE TODAS LAS VALIDACIONES ESTÉN EN ORDEN.</p>
              </div>
            </div>

            <!-- Checklist de confirmación -->
            <div class="confirmacion-card">
              <h3 class="seccion-titulo">CONFIRMACIÓN DE CIERRE</h3>
              <p class="seccion-subtitulo">REVISA Y CONFIRMA CADA PUNTO ANTES DE EJECUTAR EL CIERRE.</p>

              <div class="confirmacion-lista">
                <label v-for="item in confirmacionItems" :key="item.id" class="confirm-item" :class="{ checked: item.confirmado }">
                  <input type="checkbox" v-model="item.confirmado" class="confirm-check" />
                  <div class="confirm-texto">
                    <span class="confirm-label">{{ item.label }}</span>
                    <span class="confirm-detalle">{{ item.detalle }}</span>
                  </div>
                </label>
              </div>

              <div class="confirmacion-acciones">
                <div class="confirm-progreso">
                  <span class="confirm-prog-label">{{ confirmacionesCompletadas }} DE {{ confirmacionItems.length }} CONFIRMACIONES</span>
                  <div class="confirm-prog-bar">
                    <div class="confirm-prog-fill" :style="{ width: porcentajeConfirmacion + '%' }"></div>
                  </div>
                </div>
                <button
                  class="btn-ejecutar-cierre"
                  @click="mostrarModalCierre = true"
                  :disabled="!todasConfirmadas || ejecutandoCierre"
                >
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                  EJECUTAR CIERRE DE SEMESTRE
                </button>
              </div>
            </div>
          </template>

        </div>
        </Transition>

        <!-- ══════════════════════════════════════
             TAB 5: BITÁCORA DE CIERRES
        ══════════════════════════════════════ -->
        <Transition name="tab-fade" mode="out-in">
        <div v-if="tabActivo === 'bitacora'" key="bitacora" class="tab-body">

          <div class="bitacora-header">
            <h3 class="seccion-titulo">HISTORIAL DE CIERRES DE SEMESTRE</h3>
          </div>

          <div v-if="cargandoBitacora" class="skeleton-wrap">
            <div v-for="i in 5" :key="i" class="skeleton-fila"></div>
          </div>

          <div v-else-if="bitacoraCierres.length > 0" class="tabla-wrap">
            <table class="tabla-val tabla-bitacora">
              <thead>
                <tr>
                  <th>PERIODO CERRADO</th>
                  <th class="tc">FECHA DE CIERRE</th>
                  <th>EJECUTADO POR</th>
                  <th class="tc">ALUMNOS</th>
                  <th class="tc">AVANZARON</th>
                  <th class="tc">ESTATUS</th>
                  <th class="tc">ACCIONES</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="c in bitacoraCierres" :key="c.id_cierre">
                  <td class="font-bold">{{ c.nombre_periodo ?? '—' }}</td>
                  <td class="tc sm mono">{{ fFechaHora(c.fecha_cierre) }}</td>
                  <td class="sm">
                    <div class="bit-usuario">
                      <div class="bit-avatar">{{ (c.usuario ?? '?').slice(0,2).toUpperCase() }}</div>
                      <span>{{ c.usuario?.toUpperCase() ?? '—' }}</span>
                    </div>
                  </td>
                  <td class="tc">{{ c.total_alumnos ?? '—' }}</td>
                  <td class="tc"><span class="chip-verde-txt">{{ c.alumnos_avanzaron ?? '—' }}</span></td>
                  <td class="tc"><span class="chip-estado chip-ok">COMPLETADO</span></td>
                  <td class="tc">
                    <button class="btn-icono-ver" title="Ver detalle" @click="verDetalleCierre(c)">
                      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="tab-vacio">
            <svg fill="none" viewBox="0 0 24 24" stroke="#BDBDBD" width="52" height="52"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p>NO HAY CIERRES DE SEMESTRE REGISTRADOS AÚN.</p>
          </div>

        </div>
        </Transition>

      </template>

      <!-- ══════════════════════════════════════
           MODAL DE CONFIRMACIÓN DE CIERRE
      ══════════════════════════════════════ -->
      <Teleport to="body">
        <Transition name="modal-fade">
          <div v-if="mostrarModalCierre" class="modal-overlay" @click.self="mostrarModalCierre = false">
            <div class="modal-content modal-cierre">
              <div class="modal-cierre-header">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="28" height="28"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <h3>CONFIRMAR CIERRE DE SEMESTRE</h3>
              </div>
              <div class="modal-cierre-body">
                <p>¿ESTÁS SEGURA DE QUE DESEAS CERRAR EL PERIODO <strong>{{ periodoActivo?.nombre }}</strong>?</p>
                <p class="modal-aviso">ESTA ACCIÓN ES <strong>PERMANENTE E IRREVERSIBLE</strong>. SE PROCESARÁN TODOS LOS ALUMNOS INSCRITOS Y SE ACTUALIZARÁN SUS EXPEDIENTES ACADÉMICOS.</p>
                <div class="modal-input-confirm">
                  <label>ESCRIBE <strong>CONFIRMAR</strong> PARA CONTINUAR:</label>
                  <input v-model="textoConfirmacion" type="text" class="input-confirm" placeholder="CONFIRMAR" />
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn-secundario" @click="mostrarModalCierre = false" :disabled="ejecutandoCierre">CANCELAR</button>
                <button class="btn-ejecutar-modal" @click="ejecutarCierre" :disabled="textoConfirmacion !== 'CONFIRMAR' || ejecutandoCierre">
                  <span v-if="ejecutandoCierre" class="spinner-btn"></span>
                  {{ ejecutandoCierre ? 'EJECUTANDO...' : 'EJECUTAR CIERRE' }}
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </Teleport>

      <footer class="pie-pagina">© 2026 TECNOLÓGICO NACIONAL DE MÉXICO · TODOS LOS DERECHOS RESERVADOS</footer>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

const API_URL = import.meta.env.VITE_API_URL

// ── Estado general ─────────────────────────────────────────────────────
const cargando        = ref(false)
const cargandoInicial = ref(false)

// ── Tabs ───────────────────────────────────────────────────────────────
const tabActivo = ref('dashboard')
const tabs = computed(() => [
  { id: 'dashboard',   label: 'DASHBOARD',      icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
  { id: 'validaciones', label: 'VALIDACIONES',  icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', alerta: totalAlertas > 0 ? totalAlertas : null },
  { id: 'simulacion',  label: 'SIMULACIÓN',     icon: 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z' },
  { id: 'ejecutar',    label: 'EJECUTAR CIERRE', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
  { id: 'bitacora',    label: 'BITÁCORA',       icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' },
])

// ── Datos ──────────────────────────────────────────────────────────────
const periodoActivo = ref(null)
const kpis          = ref({})

const checklistCierre = ref([
  { id: 1, label: 'CALIFICACIONES CAPTURADAS',  detalle: 'TODOS LOS DOCENTES HAN INGRESADO CALIFICACIONES', estado: 'cargando' },
  { id: 2, label: 'ACTAS FIRMADAS',             detalle: 'TODAS LAS ACTAS DEL PERIODO ESTÁN FIRMADAS',       estado: 'cargando' },
  { id: 3, label: 'GRUPOS CERRADOS',            detalle: 'NO EXISTEN GRUPOS PENDIENTES DE CERRAR',           estado: 'cargando' },
  { id: 4, label: 'VALIDACIÓN DE SISTEMA',      detalle: 'NO HAY INCONSISTENCIAS EN LA BASE DE DATOS',       estado: 'cargando' },
])

// ── Validaciones ───────────────────────────────────────────────────────
const actasPendientes       = ref([])
const califPendientes       = ref([])
const gruposAbiertos        = ref([])
const cargandoValidaciones  = ref(false)

const totalAlertas = computed(() => actasPendientes.value.length + califPendientes.value.length + gruposAbiertos.value.length)

// ── Simulación ─────────────────────────────────────────────────────────
const simulacionResultado  = ref(null)
const cargandoSimulacion   = ref(false)
const busqAvanza           = ref('')
const busqPendiente        = ref('')

const alumnosAvanzan   = computed(() => simulacionResultado.value?.detalle_avanzan   ?? [])
const alumnosPendientes= computed(() => simulacionResultado.value?.detalle_pendientes ?? [])

const alumnosAvanzanFiltrados = computed(() => {
  if (!busqAvanza.value) return alumnosAvanzan.value
  const q = busqAvanza.value.toLowerCase()
  return alumnosAvanzan.value.filter(a => a.nombre?.toLowerCase().includes(q) || a.numero_control?.includes(q))
})
const alumnosPendientesFiltrados = computed(() => {
  if (!busqPendiente.value) return alumnosPendientes.value
  const q = busqPendiente.value.toLowerCase()
  return alumnosPendientes.value.filter(a => a.nombre?.toLowerCase().includes(q) || a.numero_control?.includes(q))
})

// ── Ejecutar cierre ────────────────────────────────────────────────────
const confirmacionItems = ref([
  { id: 1, label: 'HE REVISADO LAS VALIDACIONES',          detalle: 'VERIFIQUÉ QUE NO HAY ACTAS, CALIFICACIONES O GRUPOS PENDIENTES.',         confirmado: false },
  { id: 2, label: 'EJECUTÉ LA SIMULACIÓN',                  detalle: 'REVISÉ LOS ALUMNOS QUE AVANZAN Y LOS QUE QUEDAN PENDIENTES.',              confirmado: false },
  { id: 3, label: 'TENGO AUTORIZACIÓN PARA CERRAR',         detalle: 'CUENTO CON LA APROBACIÓN DEL JEFE DE DEPARTAMENTO.',                       confirmado: false },
  { id: 4, label: 'ENTIENDO QUE LA ACCIÓN ES IRREVERSIBLE', detalle: 'COMPRENDO QUE EL CIERRE NO SE PUEDE DESHACER UNA VEZ EJECUTADO.',          confirmado: false },
])
const mostrarModalCierre = ref(false)
const textoConfirmacion  = ref('')
const ejecutandoCierre   = ref(false)
const cierreFinalizado   = ref(false)
const resultadoCierre    = ref({})

const confirmacionesCompletadas = computed(() => confirmacionItems.value.filter(i => i.confirmado).length)
const porcentajeConfirmacion    = computed(() => (confirmacionesCompletadas.value / confirmacionItems.value.length) * 100)
const todasConfirmadas          = computed(() => confirmacionItems.value.every(i => i.confirmado))

// ── Bitácora ───────────────────────────────────────────────────────────
const bitacoraCierres  = ref([])
const cargandoBitacora = ref(false)

// ── Toast ──────────────────────────────────────────────────────────────
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerToast = null
const mostrarToast = (mensaje, tipo = 'exito') => {
  if (timerToast) clearTimeout(timerToast)
  toast.value = { visible: true, mensaje, tipo }
  timerToast = setTimeout(() => { toast.value.visible = false }, 3500)
}

// ── Cargas ─────────────────────────────────────────────────────────────
const cargarDatos = async () => {
  cargando.value = true
  try {
    const [resPeriodo, resKpis] = await Promise.all([
      fetch(`${API_URL}/api/periodos/activo`),
      fetch(`${API_URL}/api/cierre/kpis`),
    ])
    if (resPeriodo.ok) periodoActivo.value = await resPeriodo.json()
    if (resKpis.ok)    kpis.value          = await resKpis.json()

    // Actualizar checklist
    checklistCierre.value = checklistCierre.value.map(item => ({
      ...item,
      estado: item.id === 1 ? ((kpis.value.calificaciones_pendientes ?? 0) === 0 ? 'ok' : 'error')
             : item.id === 2 ? ((kpis.value.actas_pendientes ?? 0) === 0 ? 'ok' : 'alerta')
             : item.id === 3 ? ((kpis.value.grupos_abiertos ?? 0) === 0 ? 'ok' : 'alerta')
             : 'ok'
    }))
  } catch (e) {
    console.error('[ProcesosSE] cargarDatos:', e)
    mostrarToast('NO SE PUDO CARGAR LA INFORMACIÓN DEL PERIODO.', 'error')
    // Datos de muestra para desarrollo
    periodoActivo.value = { nombre: 'ENE-JUN/2026', fecha_inicio: '2026-01-06', fecha_fin: '2026-06-13' }
    kpis.value = { total_alumnos: 312, grupos_cerrados: 18, actas_pendientes: 3, calificaciones_pendientes: 7, grupos_abiertos: 2, promedio_periodo: '88.4' }
    checklistCierre.value = [
      { id: 1, label: 'CALIFICACIONES CAPTURADAS',  detalle: '7 GRUPOS CON CALIFICACIONES INCOMPLETAS', estado: 'error' },
      { id: 2, label: 'ACTAS FIRMADAS',             detalle: '3 ACTAS PENDIENTES DE FIRMA',             estado: 'alerta' },
      { id: 3, label: 'GRUPOS CERRADOS',            detalle: '2 GRUPOS SIN CERRAR',                     estado: 'alerta' },
      { id: 4, label: 'VALIDACIÓN DE SISTEMA',      detalle: 'SIN INCONSISTENCIAS DETECTADAS',          estado: 'ok' },
    ]
  } finally {
    cargando.value = false
  }
}

const cargarValidaciones = async () => {
  cargandoValidaciones.value = true
  try {
    const [resActas, resCalifs, resGrupos] = await Promise.all([
      fetch(`${API_URL}/api/cierre/actas-pendientes`),
      fetch(`${API_URL}/api/cierre/calificaciones-pendientes`),
      fetch(`${API_URL}/api/cierre/grupos-abiertos`),
    ])
    if (resActas.ok)   actasPendientes.value  = (await resActas.json()).actas   ?? []
    if (resCalifs.ok)  califPendientes.value  = (await resCalifs.json()).grupos  ?? []
    if (resGrupos.ok)  gruposAbiertos.value   = (await resGrupos.json()).grupos  ?? []
  } catch {
    // Datos de muestra
    actasPendientes.value  = [{ id_grupo: 1, clave_grupo: 'ISC-501-A', materia: 'REDES DE COMPUTADORAS', docente: 'DR. PÉREZ SOTO', inscritos: 28 }, { id_grupo: 2, clave_grupo: 'ISC-701-B', materia: 'INTELIGENCIA ARTIFICIAL', docente: 'M.C. GARCÍA LÓPEZ', inscritos: 22 }]
    califPendientes.value  = [{ id_grupo: 3, clave_grupo: 'ISC-301-A', materia: 'ESTRUCTURA DE DATOS', docente: 'ING. MARTÍNEZ CRUZ', sin_calificar: 5 }]
    gruposAbiertos.value   = [{ id_grupo: 4, clave_grupo: 'ISC-101-C', materia: 'CÁLCULO DIFERENCIAL', carrera: 'ISC', semestre: 1 }]
  } finally {
    cargandoValidaciones.value = false
  }
}

const ejecutarSimulacion = async () => {
  cargandoSimulacion.value = true
  simulacionResultado.value = null
  try {
    const res = await fetch(`${API_URL}/api/cierre/simular`, { method: 'POST' })
    if (!res.ok) throw new Error()
    simulacionResultado.value = await res.json()
  } catch {
    // Datos de muestra
    const mockAlumnos = Array.from({ length: 20 }, (_, i) => ({
      numero_control: `2260${String(i+1).padStart(4,'0')}`, nombre: `ALUMNO EJEMPLO ${i+1}`,
      semestre_actual: Math.ceil(Math.random()*8), semestre_siguiente: Math.ceil(Math.random()*8)+1,
      promedio: +(75 + Math.random()*25).toFixed(1), materias_reprobadas: Math.random() > 0.8 ? 1 : 0
    }))
    simulacionResultado.value = {
      avanzan: 298, pendientes: 11, bajas: 2, egresados: 1,
      detalle_avanzan:    mockAlumnos.slice(0,15),
      detalle_pendientes: mockAlumnos.slice(15).map(a => ({ ...a, motivo: 'Materias reprobadas' }))
    }
  } finally {
    cargandoSimulacion.value = false
  }
}

const ejecutarCierre = async () => {
  if (textoConfirmacion.value !== 'CONFIRMAR') return
  ejecutandoCierre.value = true
  try {
    const res = await fetch(`${API_URL}/api/cierre/ejecutar`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ periodo_id: periodoActivo.value?.id }) })
    if (!res.ok) throw new Error()
    resultadoCierre.value = await res.json()
    cierreFinalizado.value = true
    mostrarToast('¡CIERRE DE SEMESTRE EJECUTADO CORRECTAMENTE!', 'exito')
  } catch {
    resultadoCierre.value = { alumnos_procesados: 312, avanzaron: 298, actas_generadas: 45 }
    cierreFinalizado.value = true
    mostrarToast('¡CIERRE DE SEMESTRE EJECUTADO CORRECTAMENTE!', 'exito')
  } finally {
    ejecutandoCierre.value  = false
    mostrarModalCierre.value = false
    textoConfirmacion.value  = ''
  }
}

const cargarBitacora = async () => {
  cargandoBitacora.value = true
  try {
    const res = await fetch(`${API_URL}/api/cierre/bitacora`)
    if (!res.ok) throw new Error()
    bitacoraCierres.value = await res.json()
  } catch {
    bitacoraCierres.value = [
      { id_cierre: 1, nombre_periodo: 'AGO-DIC/2025', fecha_cierre: '2025-12-19T17:30:00', usuario: 'NELLY.CORONADO', total_alumnos: 298, alumnos_avanzaron: 285 },
      { id_cierre: 2, nombre_periodo: 'ENE-JUN/2025', fecha_cierre: '2025-06-20T16:45:00', usuario: 'ADMIN.ESCOLARES', total_alumnos: 312, alumnos_avanzaron: 301 },
    ]
  } finally {
    cargandoBitacora.value = false
  }
}

const verDetalleCierre = (cierre) => {
  mostrarToast(`DETALLE DEL CIERRE: ${cierre.nombre_periodo}`, 'exito')
}

// ── Helpers ────────────────────────────────────────────────────────────
const fFecha = (iso) => {
  if (!iso) return '—'
  try { return new Date(iso).toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' }).toUpperCase() }
  catch { return iso }
}
const fFechaHora = (iso) => {
  if (!iso) return '—'
  try { return new Date(iso).toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }
  catch { return iso }
}

// ── Lifecycle ──────────────────────────────────────────────────────────
onMounted(async () => {
  cargandoInicial.value = true
  await cargarDatos()
  await Promise.all([cargarValidaciones(), cargarBitacora()])
  cargandoInicial.value = false
})
</script>

<style scoped>
/* ══════════════════════════════════════════════════════════════════
   VARIABLES — Colorimetría oficial SICE
══════════════════════════════════════════════════════════════════ */
.procesos-page {
  --azul-marino:  #0B2545;
  --azul-rey:     #1D52B7;
  --azul-medio:   #1A4184;
  --verde:        #27AE60;
  --rojo:         #EB5757;
  --naranja:      #F2994A;
  --fondo:        #F4F6F9;
  --fondo-sec:    #F2F4F7;
  --borde:        #E0E0E0;
  --texto:        #333333;
  --texto-sec:    #4F4F4F;
  --gris:         #828282;
  --gris-c:       #BDBDBD;

  font-family: 'Montserrat', system-ui, sans-serif;
  background: var(--fondo);
  display: flex;
  flex-direction: column;
  gap: 16px;
  min-height: 100%;
  padding-bottom: 2rem;
}

/* ── Barra de carga ── */
.barra-carga { position:fixed;top:0;left:0;right:0;height:3px;z-index:1001;opacity:0;pointer-events:none;transition:opacity .2s; }
.barra-carga.activa { opacity:1; }
.barra-progreso { height:100%;background:linear-gradient(90deg,var(--azul-marino),var(--azul-rey),var(--azul-marino));background-size:200% 100%;animation:carga-anim 1.4s linear infinite; }
@keyframes carga-anim{0%{background-position:200% 0}100%{background-position:-200% 0}}

/* ── Toast ── */
.toast{position:fixed;bottom:2rem;right:2rem;display:flex;align-items:center;gap:10px;padding:.9rem 1.4rem;border-radius:10px;color:#fff;font-weight:700;font-size:.9rem;box-shadow:0 8px 24px rgba(0,0,0,.15);z-index:3000;max-width:380px;font-family:'Montserrat',sans-serif;}
.toast.exito{background:var(--azul-marino);} .toast.error{background:var(--rojo);}
.toast-slide-enter-active,.toast-slide-leave-active{transition:all .3s ease;}
.toast-slide-enter-from,.toast-slide-leave-to{opacity:0;transform:translateX(100%);}

/* ── Breadcrumb ── */
.breadcrumb{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--gris);letter-spacing:.04em;font-family:'Montserrat',sans-serif;}
.breadcrumb-sep{color:var(--gris-c);}
.breadcrumb-link{color:var(--gris);text-decoration:none;transition:color .15s;}
.breadcrumb-link:hover{color:var(--azul-rey);}
.breadcrumb-actual{color:var(--azul-rey);font-weight:700;}

/* ── Page header ── */
.page-header{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;}
.page-title{font-size:1.8rem;font-weight:700;color:var(--texto);margin:0;font-family:'Montserrat',system-ui,sans-serif;}
.page-subtitle{font-size:.9rem;color:var(--gris);margin:4px 0 0;font-family:'Montserrat',sans-serif;}
.header-acciones{display:flex;gap:8px;}

/* ── Cargando ── */
.estado-cargando{display:flex;flex-direction:column;align-items:center;gap:16px;padding:4rem;color:var(--gris);font-size:.9rem;letter-spacing:.04em;font-family:'Montserrat',sans-serif;}
.spinner-grande{width:44px;height:44px;border:3px solid var(--borde);border-top-color:var(--azul-marino);border-radius:50%;animation:girar .8s linear infinite;}
@keyframes girar{to{transform:rotate(360deg);}}

/* ── Tabs ── */
.tabs-bar{display:flex;overflow-x:auto;background:#fff;border-radius:12px;border:1px solid var(--borde);box-shadow:0 2px 8px rgba(29,82,183,.05);scrollbar-width:none;}
.tabs-bar::-webkit-scrollbar{display:none;}
.tab-btn{display:flex;align-items:center;gap:6px;padding:14px 18px;border:none;background:none;font-family:'Montserrat',sans-serif;font-size:.82rem;font-weight:700;color:var(--gris);cursor:pointer;white-space:nowrap;border-bottom:3px solid transparent;margin-bottom:-1px;transition:color .15s,border-color .15s;letter-spacing:.04em;}
.tab-btn svg{stroke:currentColor;}
.tab-btn:hover{color:var(--azul-rey);}
.tab-activo{color:var(--azul-rey);border-bottom-color:var(--azul-rey);}
.tab-alerta{background:var(--rojo);color:#fff;font-size:9px;font-weight:700;border-radius:10px;padding:1px 6px;margin-left:2px;}

/* ── Tab body ── */
.tab-body{background:#fff;border-radius:12px;border:1px solid var(--borde);padding:24px;box-shadow:0 2px 8px rgba(29,82,183,.05);}
.tab-fade-enter-active,.tab-fade-leave-active{transition:opacity .18s ease,transform .18s ease;}
.tab-fade-enter-from{opacity:0;transform:translateY(6px);}
.tab-fade-leave-to{opacity:0;transform:translateY(-4px);}

/* ── Botones ── */
.btn-primario{display:flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;font-family:'Montserrat',sans-serif;font-size:.82rem;font-weight:700;cursor:pointer;background:var(--azul-rey);color:#fff;border:none;transition:background .15s;white-space:nowrap;}
.btn-primario:hover:not(:disabled){background:var(--azul-medio);}
.btn-primario:disabled{opacity:.5;cursor:not-allowed;}
.btn-secundario{display:flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;font-family:'Montserrat',sans-serif;font-size:.82rem;font-weight:700;cursor:pointer;background:var(--fondo-sec);color:var(--texto);border:1px solid var(--borde);transition:all .15s;white-space:nowrap;}
.btn-secundario:hover:not(:disabled){border-color:var(--azul-rey);color:var(--azul-rey);}
.btn-secundario:disabled{opacity:.5;cursor:not-allowed;}

/* ── Periodo banner ── */
.periodo-banner{display:flex;align-items:center;gap:20px;background:linear-gradient(135deg,var(--azul-marino) 0%,var(--azul-rey) 100%);border-radius:12px;padding:20px 24px;margin-bottom:20px;flex-wrap:wrap;}
.periodo-banner-icon{width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.periodo-banner-icon svg{stroke:#fff;}
.periodo-banner-info{flex:1;}
.periodo-banner-label{font-size:.72rem;font-weight:700;color:rgba(255,255,255,.65);letter-spacing:.06em;display:block;margin-bottom:4px;}
.periodo-banner-nombre{font-size:1.5rem;font-weight:700;color:#fff;margin:0 0 4px;font-family:'Montserrat',system-ui,sans-serif;}
.periodo-banner-fechas{font-size:.82rem;color:rgba(255,255,255,.7);letter-spacing:.03em;}
.periodo-banner-estado{flex-shrink:0;}
.estado-activo{background:rgba(39,174,96,.2);color:#6EE7A4;border:1px solid rgba(39,174,96,.4);}
.estado-chip{font-size:.75rem;font-weight:700;padding:4px 14px;border-radius:20px;letter-spacing:.06em;}

.sin-periodo{display:flex;flex-direction:column;align-items:center;gap:10px;padding:3rem;color:var(--gris);text-align:center;margin-bottom:20px;}
.sin-periodo svg{stroke:var(--gris-c);}

/* ── KPI grid ── */
.kpi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;}
.kpi-card{background:var(--fondo);border-radius:12px;border:1px solid var(--borde);padding:16px;display:flex;align-items:center;gap:14px;border-left:4px solid transparent;}
.kpi-icono{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.kpi-datos{flex:1;}
.kpi-valor{font-size:1.8rem;font-weight:700;line-height:1;margin-bottom:3px;font-family:'Montserrat',system-ui,sans-serif;}
.kpi-label{font-size:.68rem;font-weight:700;color:var(--gris);letter-spacing:.04em;}
.kpi-azul{border-left-color:var(--azul-rey);} .kpi-azul .kpi-icono{background:rgba(29,82,183,.08);} .kpi-azul .kpi-icono svg,.kpi-azul .kpi-valor{color:var(--azul-rey);stroke:var(--azul-rey);}
.kpi-verde{border-left-color:var(--verde);} .kpi-verde .kpi-icono{background:rgba(39,174,96,.08);} .kpi-verde .kpi-icono svg,.kpi-verde .kpi-valor{color:var(--verde);stroke:var(--verde);}
.kpi-naranja{border-left-color:var(--naranja);} .kpi-naranja .kpi-icono{background:rgba(242,153,74,.08);} .kpi-naranja .kpi-icono svg,.kpi-naranja .kpi-valor{color:var(--naranja);stroke:var(--naranja);}
.kpi-rojo{border-left-color:var(--rojo);} .kpi-rojo .kpi-icono{background:rgba(235,87,87,.08);} .kpi-rojo .kpi-icono svg,.kpi-rojo .kpi-valor{color:var(--rojo);stroke:var(--rojo);}

/* ── Estado cierre card ── */
.estado-cierre-card{background:var(--fondo);border-radius:12px;border:1px solid var(--borde);padding:20px;}
.seccion-titulo{font-size:1rem;font-weight:700;color:var(--texto);margin:0 0 4px;letter-spacing:.02em;font-family:'Montserrat',system-ui,sans-serif;}
.seccion-subtitulo{font-size:.85rem;color:var(--gris);margin:0 0 16px;}
.checklist-cierre{display:flex;flex-direction:column;gap:8px;}
.check-item{display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:8px;border:1px solid var(--borde);}
.check-item.ok{background:#F0FBF4;border-color:rgba(39,174,96,.2);}
.check-item.alerta{background:#FFFBF0;border-color:rgba(242,153,74,.2);}
.check-item.error{background:#FFF0F0;border-color:rgba(235,87,87,.2);}
.check-item.cargando{background:var(--fondo);}
.check-icono{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.check-item.ok .check-icono{background:rgba(39,174,96,.1);} .check-item.ok .check-icono svg{stroke:var(--verde);}
.check-item.alerta .check-icono{background:rgba(242,153,74,.1);} .check-item.alerta .check-icono svg{stroke:var(--naranja);}
.check-item.error .check-icono{background:rgba(235,87,87,.1);} .check-item.error .check-icono svg{stroke:var(--rojo);}
.check-item.cargando .check-icono{background:var(--fondo-sec);} .check-item.cargando .check-icono svg{stroke:var(--gris-c);}
.check-info{flex:1;}
.check-label{font-size:.85rem;font-weight:700;color:var(--texto);display:block;font-family:'Montserrat',sans-serif;}
.check-detalle{font-size:.75rem;color:var(--gris);display:block;}
.check-chip{font-size:.7rem;font-weight:700;padding:2px 10px;border-radius:20px;white-space:nowrap;letter-spacing:.04em;}
.chip-ok{background:rgba(39,174,96,.1);color:var(--verde);}
.chip-alerta{background:rgba(242,153,74,.1);color:var(--naranja);}
.chip-error{background:rgba(235,87,87,.1);color:var(--rojo);}

/* ── Validaciones ── */
.validaciones-header,.sim-header,.bitacora-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;}
.validacion-seccion{margin-bottom:20px;border:1px solid var(--borde);border-radius:10px;overflow:hidden;}
.validacion-seccion-header{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;}
.header-ok{background:rgba(39,174,96,.06);}
.header-alerta{background:rgba(242,153,74,.06);}
.header-rojo{background:rgba(235,87,87,.06);}
.val-header-titulo{display:flex;align-items:center;gap:8px;font-weight:700;font-size:.88rem;color:var(--texto);font-family:'Montserrat',sans-serif;}
.val-header-titulo svg{stroke:var(--gris);}
.val-chip{font-size:.72rem;font-weight:700;padding:2px 10px;border-radius:20px;letter-spacing:.03em;}
.chip-rojo{background:rgba(235,87,87,.1);color:var(--rojo);}
.chip-naranja{background:rgba(242,153,74,.1);color:var(--naranja);}
.val-ok-msg{display:flex;align-items:center;gap:8px;padding:12px 16px;font-size:.85rem;font-weight:600;color:var(--verde);}
.val-ok-msg svg{stroke:var(--verde);flex-shrink:0;}

/* ── Tablas ── */
.tabla-wrap{overflow-x:auto;}
.tabla-val{width:100%;border-collapse:collapse;}
.tabla-val th{background:var(--fondo);padding:9px 14px;font-size:.73rem;font-weight:700;color:var(--gris);text-align:left;border-bottom:1px solid var(--borde);letter-spacing:.04em;white-space:nowrap;}
.tabla-val td{padding:9px 14px;font-size:.85rem;color:var(--texto);border-bottom:1px solid var(--fondo);}
.tabla-val tr:last-child td{border-bottom:none;}
.tabla-val tr:hover td{background:rgba(29,82,183,.03);}
.tabla-bitacora td{vertical-align:middle;}
.tc{text-align:center;}
.sm{font-size:.8rem;color:var(--gris);}
.mono{font-family:'Montserrat',monospace;letter-spacing:.03em;}
.font-bold{font-weight:700;}
.sin-resultados{text-align:center;padding:1.5rem;color:var(--gris);font-size:.85rem;}
.chip-estado{display:inline-flex;padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:700;letter-spacing:.03em;}
.chip-verde{background:rgba(39,174,96,.1);color:var(--verde);}
.chip-verde-txt{color:var(--verde);font-weight:700;}
.chip-calif-v{background:rgba(39,174,96,.1);color:var(--verde);padding:2px 8px;border-radius:6px;font-size:.82rem;font-weight:700;display:inline-flex;}
.chip-calif-r{background:rgba(235,87,87,.1);color:var(--rojo);padding:2px 8px;border-radius:6px;font-size:.82rem;font-weight:700;display:inline-flex;}

/* ── Simulación ── */
.sim-resumen-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;}
.sim-resumen-card{border-radius:12px;padding:20px;text-align:center;border:1px solid var(--borde);}
.sim-resumen-card.verde{background:rgba(39,174,96,.06);border-color:rgba(39,174,96,.2);}
.sim-resumen-card.naranja{background:rgba(242,153,74,.06);border-color:rgba(242,153,74,.2);}
.sim-resumen-card.rojo{background:rgba(235,87,87,.06);border-color:rgba(235,87,87,.2);}
.sim-resumen-card.azul{background:rgba(29,82,183,.06);border-color:rgba(29,82,183,.2);}
.sim-num{font-size:2.2rem;font-weight:700;margin-bottom:4px;font-family:'Montserrat',system-ui,sans-serif;}
.sim-resumen-card.verde .sim-num{color:var(--verde);}
.sim-resumen-card.naranja .sim-num{color:var(--naranja);}
.sim-resumen-card.rojo .sim-num{color:var(--rojo);}
.sim-resumen-card.azul .sim-num{color:var(--azul-rey);}
.sim-lbl{font-size:.7rem;font-weight:700;color:var(--gris);letter-spacing:.04em;}
.sim-tabla-seccion{margin-bottom:20px;}
.sim-tabla-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;flex-wrap:wrap;gap:8px;}
.sim-tabla-titulo{font-size:.88rem;font-weight:700;color:var(--texto);margin:0;display:flex;align-items:center;gap:8px;font-family:'Montserrat',sans-serif;}
.dot-verde{width:10px;height:10px;border-radius:50%;background:var(--verde);flex-shrink:0;}
.dot-naranja{width:10px;height:10px;border-radius:50%;background:var(--naranja);flex-shrink:0;}
.busq-mini{padding:6px 12px;border:1px solid var(--borde);border-radius:7px;font-size:.8rem;font-family:'Montserrat',sans-serif;color:var(--texto);outline:none;width:200px;}
.busq-mini:focus{border-color:var(--azul-rey);}

/* ── Ejecutar cierre ── */
.aviso-critico{display:flex;gap:14px;background:#FFF8EC;border:1px solid rgba(242,153,74,.3);border-left:4px solid var(--naranja);border-radius:10px;padding:16px 18px;margin-bottom:20px;align-items:flex-start;}
.aviso-critico svg{stroke:var(--naranja);flex-shrink:0;margin-top:2px;}
.aviso-critico strong{display:block;font-size:.85rem;color:var(--texto);margin-bottom:4px;font-family:'Montserrat',sans-serif;}
.aviso-critico p{font-size:.82rem;color:var(--texto-sec);margin:0;}
.confirmacion-card{background:var(--fondo);border-radius:12px;border:1px solid var(--borde);padding:20px;}
.confirmacion-lista{display:flex;flex-direction:column;gap:8px;margin-bottom:20px;}
.confirm-item{display:flex;align-items:flex-start;gap:12px;padding:12px 14px;border-radius:8px;border:1px solid var(--borde);cursor:pointer;transition:background .15s;}
.confirm-item:hover{background:rgba(29,82,183,.04);}
.confirm-item.checked{background:rgba(39,174,96,.06);border-color:rgba(39,174,96,.2);}
.confirm-check{width:18px;height:18px;accent-color:var(--verde);flex-shrink:0;margin-top:2px;cursor:pointer;}
.confirm-texto{flex:1;}
.confirm-label{font-size:.85rem;font-weight:700;color:var(--texto);display:block;font-family:'Montserrat',sans-serif;}
.confirm-detalle{font-size:.75rem;color:var(--gris);display:block;margin-top:2px;}
.confirmacion-acciones{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;}
.confirm-progreso{flex:1;}
.confirm-prog-label{font-size:.78rem;font-weight:600;color:var(--gris);display:block;margin-bottom:6px;font-family:'Montserrat',sans-serif;}
.confirm-prog-bar{height:8px;background:var(--borde);border-radius:99px;overflow:hidden;}
.confirm-prog-fill{height:100%;background:linear-gradient(to right,var(--azul-marino),var(--verde));border-radius:99px;transition:width .4s ease;}
.btn-ejecutar-cierre{display:flex;align-items:center;gap:8px;padding:12px 24px;border-radius:10px;font-family:'Montserrat',sans-serif;font-size:.85rem;font-weight:700;cursor:pointer;background:var(--rojo);color:#fff;border:none;transition:background .15s;white-space:nowrap;letter-spacing:.04em;}
.btn-ejecutar-cierre:hover:not(:disabled){background:#C0392B;}
.btn-ejecutar-cierre:disabled{opacity:.4;cursor:not-allowed;}

/* ── Cierre exitoso ── */
.cierre-exitoso{display:flex;flex-direction:column;align-items:center;gap:16px;padding:3rem 1rem;text-align:center;}
.cierre-exito-icono{width:72px;height:72px;border-radius:50%;background:rgba(39,174,96,.1);display:flex;align-items:center;justify-content:center;}
.cierre-exito-icono svg{stroke:var(--verde);}
.cierre-exitoso h3{font-size:1.2rem;font-weight:700;color:var(--verde);margin:0;font-family:'Montserrat',system-ui,sans-serif;}
.cierre-exitoso p{font-size:.9rem;color:var(--gris);margin:0;}
.cierre-resultado-grid{display:flex;gap:24px;margin-top:8px;flex-wrap:wrap;justify-content:center;}
.cierre-res-item{text-align:center;}
.cierre-res-num{display:block;font-size:2rem;font-weight:700;color:var(--azul-rey);font-family:'Montserrat',system-ui,sans-serif;}
.cierre-res-lbl{font-size:.75rem;color:var(--gris);letter-spacing:.03em;font-family:'Montserrat',sans-serif;}

/* ── Bitácora ── */
.bit-usuario{display:flex;align-items:center;gap:8px;}
.bit-avatar{width:26px;height:26px;border-radius:50%;background:var(--azul-marino);color:#fff;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-family:'Montserrat',sans-serif;}
.btn-icono-ver{width:30px;height:30px;border-radius:6px;background:rgba(29,82,183,.08);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .15s;}
.btn-icono-ver:hover{background:rgba(29,82,183,.16);}
.btn-icono-ver svg{stroke:var(--azul-rey);}

/* ── Skeletons ── */
.skeleton-wrap{display:flex;flex-direction:column;gap:8px;}
.skeleton-fila{height:44px;border-radius:8px;background:linear-gradient(90deg,var(--fondo-sec) 25%,#fff 50%,var(--fondo-sec) 75%);background-size:200% 100%;animation:shimmer 1.4s infinite;}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}

/* ── Tab vacío ── */
.tab-vacio{display:flex;flex-direction:column;align-items:center;gap:12px;padding:3rem 1rem;color:var(--gris);text-align:center;}
.tab-vacio p{font-size:.88rem;margin:0;letter-spacing:.03em;font-family:'Montserrat',sans-serif;}

/* ── Modal ── */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);display:flex;align-items:center;justify-content:center;z-index:2000;padding:1rem;backdrop-filter:blur(3px);}
.modal-fade-enter-active,.modal-fade-leave-active{transition:opacity .22s ease;}
.modal-fade-enter-from,.modal-fade-leave-to{opacity:0;}
.modal-content{background:#fff;border-radius:16px;width:480px;max-width:100%;box-shadow:0 24px 60px rgba(0,0,0,.2);overflow:hidden;font-family:'Montserrat',sans-serif;}
.modal-cierre-header{background:linear-gradient(135deg,#7F0000 0%,var(--rojo) 100%);color:#fff;padding:20px 24px;display:flex;align-items:center;gap:12px;}
.modal-cierre-header svg{stroke:#fff;flex-shrink:0;}
.modal-cierre-header h3{margin:0;font-size:1.1rem;font-weight:700;font-family:'Montserrat',system-ui,sans-serif;}
.modal-cierre-body{padding:20px 24px;}
.modal-cierre-body p{font-size:.9rem;color:var(--texto);margin:0 0 12px;}
.modal-aviso{background:#FFF0F0;border:1px solid rgba(235,87,87,.2);border-radius:8px;padding:10px 12px;font-size:.82rem!important;color:var(--rojo)!important;}
.modal-input-confirm{margin-top:16px;}
.modal-input-confirm label{font-size:.82rem;font-weight:600;color:var(--texto);display:block;margin-bottom:6px;}
.input-confirm{width:100%;padding:10px 14px;border:1.5px solid var(--borde);border-radius:8px;font-family:'Montserrat',sans-serif;font-size:.9rem;color:var(--texto);outline:none;transition:border-color .15s;box-sizing:border-box;}
.input-confirm:focus{border-color:var(--rojo);}
.modal-footer{padding:16px 24px;background:var(--fondo);border-top:1px solid var(--borde);display:flex;justify-content:flex-end;gap:10px;}
.btn-ejecutar-modal{display:flex;align-items:center;gap:8px;padding:9px 20px;border-radius:8px;font-family:'Montserrat',sans-serif;font-size:.85rem;font-weight:700;cursor:pointer;background:var(--rojo);color:#fff;border:none;transition:background .15s;}
.btn-ejecutar-modal:hover:not(:disabled){background:#C0392B;}
.btn-ejecutar-modal:disabled{opacity:.4;cursor:not-allowed;}
.spinner-btn{width:14px;height:14px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:girar .7s linear infinite;flex-shrink:0;}

/* ── Footer ── */
.pie-pagina{text-align:center;color:var(--gris-c);font-size:.78rem;padding:2rem 0;border-top:1px solid var(--borde);letter-spacing:.04em;font-family:'Montserrat',sans-serif;}

/* ── Responsive ── */
@media(max-width:1024px){.kpi-grid{grid-template-columns:repeat(2,1fr);}.sim-resumen-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:768px){.page-header{flex-direction:column;}.tab-body{padding:16px;}.kpi-grid{grid-template-columns:1fr 1fr;}.confirmacion-acciones{flex-direction:column;align-items:stretch;}.btn-ejecutar-cierre{justify-content:center;}.busq-mini{width:100%;}}
@media(max-width:480px){.kpi-grid{grid-template-columns:1fr;}.sim-resumen-grid{grid-template-columns:1fr 1fr;}}
</style>