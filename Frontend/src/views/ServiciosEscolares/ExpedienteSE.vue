<template>
  <MainLayout>
    <div class="expediente-page">

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
      <nav class="breadcrumb" aria-label="Navegación">
        <router-link to="/servicios-escolares" class="breadcrumb-link">SERVICIOS ESCOLARES</router-link>
        <span class="breadcrumb-sep" aria-hidden="true">›</span>
        <router-link to="/alumnos/gestion" class="breadcrumb-link">ALUMNOS</router-link>
        <span class="breadcrumb-sep" aria-hidden="true">›</span>
        <span class="breadcrumb-actual">EXPEDIENTE ACADÉMICO</span>
      </nav>

      <!-- Buscador: se muestra cuando no hay alumno seleccionado -->
      <div v-if="!noControl" class="busqueda-expediente">
        <div class="busq-icono-wrap" aria-hidden="true">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="40" height="40">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <h2 class="busq-titulo">BUSCAR ALUMNO</h2>
        <p class="busq-subtitulo">Ingresa el número de control o nombre del alumno</p>

        <div class="busq-input-wrap">
          <div class="busq-input-group">
            <svg class="busq-input-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
              v-model="noControlBusqueda"
              class="busq-input"
              placeholder="Número de control o nombre..."
              autocomplete="off"
              @keyup.enter="buscarAlumno"
            />
            <button
              v-if="noControlBusqueda"
              class="busq-btn-limpiar"
              @click="noControlBusqueda = ''; resultadosBusqueda = []"
              type="button"
              aria-label="Limpiar"
            >
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <button class="btn-primario busq-btn-buscar" @click="buscarAlumno" :disabled="buscando" type="button">
            <svg v-if="!buscando" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <span v-if="buscando" class="busq-spinner" aria-hidden="true"></span>
            {{ buscando ? 'BUSCANDO...' : 'BUSCAR' }}
          </button>
        </div>

        <!-- Resultados de búsqueda -->
        <div v-if="resultadosBusqueda.length" class="busq-resultados">
          <p class="busq-resultados-titulo">{{ resultadosBusqueda.length }} resultado(s) encontrado(s)</p>
          <ul class="busq-lista" role="listbox">
            <li
              v-for="a in resultadosBusqueda"
              :key="a.numero_control"
              class="busq-item"
              role="option"
              @click="abrirExpediente(a.numero_control)"
            >
              <div class="busq-item-avatar" aria-hidden="true">
                {{ (a.nombre ?? '?').split(' ').slice(0,2).map(p => p[0]).join('').toUpperCase() }}
              </div>
              <div class="busq-item-info">
                <span class="busq-item-nombre">{{ a.nombre }}</span>
                <span class="busq-item-meta">{{ a.numero_control }} · {{ resolverCarrera(a) }} · {{ a.semestre_actual ?? a.semestre }}° SEM.</span>
              </div>
              <span class="estatus-chip" :class="(a.estatus ?? '').toLowerCase().replace(/\s/g,'-')">
                {{ (a.estatus ?? '').toUpperCase() }}
              </span>
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" class="busq-item-flecha" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </li>
          </ul>
        </div>

        <!-- Sin resultados -->
        <div v-else-if="noControlBusqueda.trim() && !buscando && resultadosBusqueda.length === 0" class="busq-sin-resultados">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="32" height="32"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <p>No se encontraron alumnos con ese criterio.</p>
        </div>
      </div>

      <!-- Estado cargando inicial -->
      <div v-else-if="cargandoAlumno" class="estado-cargando-pagina">
        <div class="spinner-grande" aria-hidden="true"></div>
        <p>CARGANDO EXPEDIENTE...</p>
      </div>

      <!-- Error: alumno no encontrado -->
      <div v-else-if="errorAlumno" class="error-pagina" role="alert">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="52" height="52"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <h2>ALUMNO NO ENCONTRADO</h2>
        <p>No se encontró ningún alumno con el número de control <strong>{{ noControl }}</strong>.</p>
        <button @click="$router.push('/alumnos/gestion')" class="btn-primario">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          VOLVER A ALUMNOS
        </button>
      </div>

      <!-- Contenido principal -->
      <template v-else-if="alumno">

        <!-- Header del alumno -->
        <div class="alumno-header">
          <div class="alumno-avatar" aria-hidden="true">{{ iniciales(alumno.nombre) }}</div>
          <div class="alumno-info">
            <div class="alumno-nombre-row">
              <h1 class="alumno-nombre">{{ alumno.nombre?.toUpperCase() }}</h1>
              <span class="estatus-chip" :class="claseEstatus(alumno.estatus)">{{ alumno.estatus?.toUpperCase() }}</span>
            </div>
            <div class="alumno-meta-grid">
              <span class="alumno-meta-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/></svg>
                N° CONTROL: <strong>{{ alumno.numero_control }}</strong>
              </span>
              <span class="alumno-meta-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
                {{ resolverCarrera(alumno).toUpperCase() }}
              </span>
              <span class="alumno-meta-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                SEMESTRE {{ alumno.semestre_actual ?? alumno.semestre }}
              </span>
              <span class="alumno-meta-item">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                {{ alumno.email }}
              </span>
            </div>
          </div>
          <div class="alumno-acciones">
            <button @click="$router.push('/alumnos/gestion')" class="btn-outline">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
              VOLVER
            </button>
            <button @click="imprimirExpediente" class="btn-primario">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
              IMPRIMIR
            </button>
          </div>
        </div>

        <!-- Tabs del Expediente -->
        <div class="tabs-bar" role="tablist" aria-label="Secciones del expediente">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            class="tab-btn"
            :class="{ 'tab-activo': tabActivo === tab.id }"
            @click="cambiarTab(tab.id)"
            role="tab"
            :aria-selected="tabActivo === tab.id"
            type="button"
          >
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon"/>
            </svg>
            {{ tab.label }}
            <span v-if="tab.badge" class="tab-badge">{{ tab.badge }}</span>
          </button>
        </div>

        <!-- ══════════════════════════════════
             TAB 1: DATOS GENERALES
        ══════════════════════════════════ -->
        <Transition name="tab-fade" mode="out-in">
        <div v-if="tabActivo === 'datos'" key="datos" class="tab-body" role="tabpanel">

          <div class="seccion-grid">

            <!-- Datos personales -->
            <div class="info-card">
              <div class="info-card-header">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <h3>DATOS PERSONALES</h3>
              </div>
              <dl class="datos-lista">
                <div class="dato-item"><dt>NOMBRE COMPLETO</dt><dd>{{ alumno.nombre ?? '—' }}</dd></div>
                <div class="dato-item"><dt>NÚMERO DE CONTROL</dt><dd class="mono">{{ alumno.numero_control ?? '—' }}</dd></div>
                <div class="dato-item"><dt>CURP</dt><dd class="mono">{{ alumno.curp ?? 'NO REGISTRADO' }}</dd></div>
                <div class="dato-item"><dt>FECHA DE NACIMIENTO</dt><dd>{{ fFecha(alumno.fecha_nacimiento) }}</dd></div>
                <div class="dato-item"><dt>SEXO / GÉNERO</dt><dd>{{ alumno.genero ?? 'NO REGISTRADO' }}</dd></div>
                <div class="dato-item"><dt>ESTADO CIVIL</dt><dd>{{ alumno.estado_civil ?? 'NO REGISTRADO' }}</dd></div>
                <div class="dato-item"><dt>TELÉFONO</dt><dd>{{ alumno.telefono ?? 'NO REGISTRADO' }}</dd></div>
                <div class="dato-item dato-full"><dt>CORREO INSTITUCIONAL</dt><dd>{{ alumno.email ?? '—' }}</dd></div>
                <div class="dato-item dato-full"><dt>DIRECCIÓN</dt><dd>{{ alumno.direccion ?? 'NO REGISTRADO' }}</dd></div>
              </dl>
            </div>

            <!-- Datos académicos -->
            <div class="info-card">
              <div class="info-card-header">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
                <h3>DATOS ACADÉMICOS</h3>
              </div>
              <dl class="datos-lista">
                <div class="dato-item dato-full"><dt>CARRERA</dt><dd>{{ resolverCarrera(alumno) || '—' }}</dd></div>
                <div class="dato-item"><dt>SEMESTRE ACTUAL</dt><dd>{{ alumno.semestre_actual ?? alumno.semestre }}° SEMESTRE</dd></div>
                <div class="dato-item"><dt>PERIODO DE INGRESO</dt><dd>{{ alumno.periodo_ingreso ?? alumno.fecha_ingreso ?? 'NO REGISTRADO' }}</dd></div>
                <div class="dato-item"><dt>PLAN DE ESTUDIOS</dt><dd>{{ alumno.plan_estudios ?? 'NO REGISTRADO' }}</dd></div>
                <div class="dato-item"><dt>MODALIDAD</dt><dd>{{ alumno.modalidad ?? 'ESCOLARIZADO' }}</dd></div>
                <div class="dato-item"><dt>ESPECIALIDAD</dt><dd>{{ alumno.especialidad ?? 'NO REGISTRADO' }}</dd></div>
                <div class="dato-item"><dt>ESTATUS</dt><dd><span class="estatus-chip" :class="claseEstatus(alumno.estatus)">{{ alumno.estatus?.toUpperCase() ?? '—' }}</span></dd></div>
              </dl>
            </div>

            <!-- Seguro / SUBES -->
            <div class="info-card">
              <div class="info-card-header">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <h3>SEGURO Y SUBES</h3>
              </div>
              <dl class="datos-lista">
                <div class="dato-item"><dt>N° SEGURO SOCIAL</dt><dd class="mono">{{ alumno.nss ?? 'NO REGISTRADO' }}</dd></div>
                <div class="dato-item"><dt>FOLIO SUBES</dt><dd class="mono">{{ alumno.folio_subes ?? 'NO REGISTRADO' }}</dd></div>
                <div class="dato-item"><dt>TIPO DE SANGRE</dt><dd>{{ alumno.tipo_sangre ?? 'NO REGISTRADO' }}</dd></div>
                <div class="dato-item"><dt>DISCAPACIDAD</dt><dd>{{ alumno.discapacidad ?? 'NINGUNA' }}</dd></div>
              </dl>
            </div>

            <!-- Contacto de emergencia -->
            <div class="info-card">
              <div class="info-card-header">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                <h3>CONTACTO DE EMERGENCIA</h3>
              </div>
              <dl class="datos-lista">
                <div class="dato-item dato-full"><dt>NOMBRE</dt><dd>{{ alumno.contacto_emergencia?.nombre ?? 'NO REGISTRADO' }}</dd></div>
                <div class="dato-item"><dt>PARENTESCO</dt><dd>{{ alumno.contacto_emergencia?.parentesco ?? 'NO REGISTRADO' }}</dd></div>
                <div class="dato-item"><dt>TELÉFONO</dt><dd>{{ alumno.contacto_emergencia?.telefono ?? 'NO REGISTRADO' }}</dd></div>
              </dl>
            </div>

          </div>
        </div>
        </Transition>

        <!-- ══════════════════════════════════
             TAB 2: KARDEX
        ══════════════════════════════════ -->
        <Transition name="tab-fade" mode="out-in">
        <div v-if="tabActivo === 'kardex'" key="kardex" class="tab-body" role="tabpanel">

          <!-- KPIs de kardex -->
          <div v-if="kardexData?.alumno" class="kpi-grid">
            <div class="kpi-card kpi-azul">
              <div class="kpi-valor">{{ kardexData.alumno.promedio_general ?? '—' }}</div>
              <div class="kpi-label">PROMEDIO GENERAL</div>
            </div>
            <div class="kpi-card kpi-verde">
              <div class="kpi-valor">{{ kardexData.alumno.creditos_acumulados ?? '—' }}</div>
              <div class="kpi-label">CRÉDITOS OBTENIDOS</div>
            </div>
            <div class="kpi-card kpi-naranja">
              <div class="kpi-valor">{{ kardexData.alumno.creditos_totales ?? '—' }}</div>
              <div class="kpi-label">CRÉDITOS TOTALES</div>
            </div>
            <div class="kpi-card" :class="(kardexData.alumno.materias_reprobadas ?? 0) > 0 ? 'kpi-rojo' : 'kpi-verde'">
              <div class="kpi-valor">{{ kardexData.alumno.materias_reprobadas ?? '0' }}</div>
              <div class="kpi-label">MATERIAS REPROBADAS</div>
            </div>
          </div>

          <!-- Barra de avance -->
          <div v-if="kardexData?.alumno?.creditos_totales" class="prog-card">
            <div class="prog-header">
              <span class="prog-label">AVANCE EN CRÉDITOS DE LA CARRERA</span>
              <span class="prog-pct">{{ porcentajeCreditos }}%</span>
            </div>
            <div class="prog-track">
              <div class="prog-fill" :style="{ width: porcentajeCreditos + '%' }"></div>
            </div>
            <p class="prog-detalle">{{ kardexData.alumno.creditos_acumulados }} DE {{ kardexData.alumno.creditos_totales }} CRÉDITOS</p>
          </div>

          <!-- Skeleton kardex cargando -->
          <div v-if="cargandoKardex" class="skeleton-wrap">
            <div class="skeleton-linea largo"></div>
            <div class="skeleton-linea medio"></div>
            <div v-for="i in 5" :key="i" class="skeleton-fila"></div>
          </div>

          <!-- Error kardex -->
          <div v-else-if="errorKardex" class="estado-error-inline" role="alert">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>NO SE PUDO CARGAR EL KARDEX.</span>
            <button @click="cargarKardex" class="btn-reintentar">REINTENTAR</button>
          </div>

          <!-- Semestres del kardex -->
          <div v-else-if="kardexData?.kardex?.semestres?.length">
            <div v-for="sem in kardexData.kardex.semestres" :key="sem.numero" class="semestre-bloque">
              <button
                class="semestre-btn"
                :class="{ abierto: semestresAbiertos.includes(sem.numero) }"
                @click="toggleSemestre(sem.numero)"
                type="button"
              >
                <span class="sem-titulo">SEMESTRE {{ sem.numero }}</span>
                <div class="sem-badges">
                  <span class="badge-mini verde">{{ sem.acreditadas ?? 0 }} ACRED.</span>
                  <span v-if="(sem.reprobadas ?? 0) > 0" class="badge-mini rojo">{{ sem.reprobadas }} REPR.</span>
                  <span class="badge-mini gris">PROM: {{ sem.promedio ?? '—' }}</span>
                  <svg class="sem-flecha" :class="{ girado: semestresAbiertos.includes(sem.numero) }" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
              </button>
              <Transition name="expand">
                <div v-if="semestresAbiertos.includes(sem.numero)" class="semestre-materias">
                  <div class="tabla-wrap">
                    <table class="tabla-kardex">
                      <thead>
                        <tr>
                          <th>CLAVE</th>
                          <th>MATERIA</th>
                          <th class="tc">CRÉD.</th>
                          <th class="tc">CALIF.</th>
                          <th class="tc">PERIODO</th>
                          <th class="tc">ESTATUS</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="m in sem.materias" :key="m.clave" :class="{ 'fila-repr': m.estado === 'reprobada' }">
                          <td class="mono sm">{{ m.clave }}</td>
                          <td>{{ m.nombre }}</td>
                          <td class="tc">{{ m.creditos }}</td>
                          <td class="tc">
                            <span class="chip-calif" :class="claseCalif(m.calificacion)">
                              {{ m.calificacion ?? '—' }}
                            </span>
                          </td>
                          <td class="tc sm">{{ m.periodo ?? '—' }}</td>
                          <td class="tc">
                            <span class="chip-estado" :style="estiloEstado(m.estado)">
                              {{ etiquetaEstado(m.estado) }}
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </Transition>
            </div>
          </div>

          <div v-else-if="!cargandoKardex" class="tab-vacio">
            <svg fill="none" viewBox="0 0 24 24" stroke="#BDBDBD" width="52" height="52"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p>NO HAY INFORMACIÓN DE KARDEX DISPONIBLE PARA ESTE ALUMNO.</p>
          </div>

        </div>
        </Transition>

        <!-- ══════════════════════════════════
             TAB 3: HISTORIAL ACADÉMICO
        ══════════════════════════════════ -->
        <Transition name="tab-fade" mode="out-in">
        <div v-if="tabActivo === 'historial'" key="historial" class="tab-body" role="tabpanel">

          <div class="filtros-historial">
            <select v-model="filtroHistorial" class="filtro-select">
              <option value="">TODOS LOS PERIODOS</option>
              <option value="cursadas">MATERIAS CURSADAS</option>
              <option value="reinscripciones">REINSCRIPCIONES</option>
              <option value="especiales">ESPECIALES</option>
            </select>
          </div>

          <div v-if="cargandoHistorial" class="skeleton-wrap">
            <div v-for="i in 6" :key="i" class="skeleton-fila"></div>
          </div>

          <div v-else-if="historialFiltrado.length > 0">
            <!-- Agrupado por semestre -->
            <div v-for="(grupo, periodo) in historialAgrupado" :key="periodo" class="historial-grupo">
              <div class="historial-periodo-header">
                <span class="historial-periodo-label">{{ periodo }}</span>
                <span class="historial-periodo-count">{{ grupo.length }} MATERIA(S)</span>
              </div>
              <div class="tabla-wrap">
                <table class="tabla-historial">
                  <thead>
                    <tr>
                      <th>MATERIA</th>
                      <th class="tc">CALIFICACIÓN</th>
                      <th class="tc">CRÉDITOS</th>
                      <th class="tc">TIPO</th>
                      <th class="tc">ESTATUS</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="h in grupo" :key="h.id ?? (h.periodo + h.materia)">
                      <td>{{ h.materia }}</td>
                      <td class="tc">
                        <span class="chip-calif" :class="claseCalif(h.calificacion)">{{ h.calificacion ?? '—' }}</span>
                      </td>
                      <td class="tc">{{ h.creditos ?? '—' }}</td>
                      <td class="tc">
                        <span class="tipo-badge" :class="h.tipo === 'especial' ? 'tipo-especial' : h.tipo === 'reinscripcion' ? 'tipo-reinscripcion' : 'tipo-normal'">
                          {{ h.tipo?.toUpperCase() ?? 'NORMAL' }}
                        </span>
                      </td>
                      <td class="tc">
                        <span class="chip-estado" :style="estiloEstado(h.calificacion >= 70 ? 'acreditada' : h.calificacion ? 'reprobada' : 'no_cursada')">
                          {{ !h.calificacion ? 'PENDIENTE' : h.calificacion >= 70 ? 'ACREDITADA' : 'REPROBADA' }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div v-else class="tab-vacio">
            <svg fill="none" viewBox="0 0 24 24" stroke="#BDBDBD" width="52" height="52"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <p>NO HAY HISTORIAL ACADÉMICO DISPONIBLE.</p>
          </div>

        </div>
        </Transition>

        <!-- ══════════════════════════════════
             TAB 4: HORARIO E INSCRIPCIONES
        ══════════════════════════════════ -->
        <Transition name="tab-fade" mode="out-in">
        <div v-if="tabActivo === 'horario'" key="horario" class="tab-body" role="tabpanel">

          <div class="subtabs-nav">
            <button class="subtab-btn" :class="{ activo: subtabHorario === 'horario' }" @click="subtabHorario = 'horario'" type="button">HORARIO ACTUAL</button>
            <button class="subtab-btn" :class="{ activo: subtabHorario === 'inscripciones' }" @click="subtabHorario = 'inscripciones'" type="button">HISTORIAL DE INSCRIPCIONES</button>
          </div>

          <!-- Horario actual -->
          <div v-if="subtabHorario === 'horario'">
            <div v-if="cargandoHorario" class="skeleton-wrap">
              <div v-for="i in 5" :key="i" class="skeleton-fila"></div>
            </div>
            <div v-else-if="horarioData?.length" class="horario-lista">
              <div v-for="c in horarioData" :key="c.id ?? c.materia" class="horario-card">
                <div class="hor-dia">{{ c.dia?.toUpperCase() ?? '—' }}</div>
                <div class="hor-info">
                  <p class="hor-materia">{{ c.materia?.toUpperCase() }}</p>
                  <p class="hor-detalle">{{ c.hora_inicio }} – {{ c.hora_fin }} · AULA {{ c.aula?.toUpperCase() ?? 'N/D' }}</p>
                </div>
                <div class="hor-docente">
                  <span class="hor-docente-label">DOCENTE</span>
                  <span class="hor-docente-nombre">{{ c.docente?.toUpperCase() ?? 'SIN ASIGNAR' }}</span>
                </div>
              </div>
            </div>
            <div v-else class="tab-vacio">
              <svg fill="none" viewBox="0 0 24 24" stroke="#BDBDBD" width="52" height="52"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              <p>NO HAY HORARIO REGISTRADO PARA EL PERIODO ACTUAL.</p>
            </div>
          </div>

          <!-- Historial de inscripciones -->
          <div v-if="subtabHorario === 'inscripciones'">
            <div v-if="cargandoInscripciones" class="skeleton-wrap">
              <div v-for="i in 4" :key="i" class="skeleton-fila"></div>
            </div>
            <div v-else-if="inscripcionesData?.length">
              <div class="tabla-wrap">
                <table class="tabla-historial">
                  <thead>
                    <tr>
                      <th>PERIODO</th>
                      <th>MATERIAS</th>
                      <th class="tc">CRÉDITOS</th>
                      <th class="tc">ESTATUS</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="ins in inscripcionesData" :key="ins.id_inscripcion ?? ins.periodo">
                      <td>{{ ins.periodo }}</td>
                      <td>{{ ins.total_materias ?? '—' }} MATERIA(S)</td>
                      <td class="tc">{{ ins.creditos ?? '—' }}</td>
                      <td class="tc">
                        <span class="chip-estado" :style="{ background: ins.estatus === 'activa' ? '#DCFCE7' : '#F3F4F6', color: ins.estatus === 'activa' ? '#16A34A' : '#6B7280' }">
                          {{ ins.estatus?.toUpperCase() ?? '—' }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div v-else class="tab-vacio">
              <svg fill="none" viewBox="0 0 24 24" stroke="#BDBDBD" width="52" height="52"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
              <p>NO HAY HISTORIAL DE INSCRIPCIONES DISPONIBLE.</p>
            </div>
          </div>

        </div>
        </Transition>

        <!-- ══════════════════════════════════
             TAB 5: BITÁCORA
        ══════════════════════════════════ -->
        <Transition name="tab-fade" mode="out-in">
        <div v-if="tabActivo === 'bitacora'" key="bitacora" class="tab-body" role="tabpanel">

          <div v-if="cargandoBitacora" class="skeleton-wrap">
            <div v-for="i in 5" :key="i" class="skeleton-fila"></div>
          </div>

          <div v-else-if="bitacoraData?.length">
            <div class="tabla-wrap">
              <table class="tabla-historial">
                <thead>
                  <tr>
                    <th>FECHA Y HORA</th>
                    <th>USUARIO</th>
                    <th>ACCIÓN</th>
                    <th>MÓDULO</th>
                    <th>DETALLE</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, i) in bitacoraData" :key="item.id_bitacora ?? i">
                    <td class="sm mono">{{ fFechaHora(item.fecha_hora) }}</td>
                    <td>
                      <div class="bit-usuario">
                        <div class="bit-avatar" aria-hidden="true">{{ (item.usuario || item.nombre_usuario || '?').slice(0,2).toUpperCase() }}</div>
                        <span>{{ (item.usuario || item.nombre_usuario || '—').toUpperCase() }}</span>
                      </div>
                    </td>
                    <td>
                      <span class="badge-accion" :class="claseBadge(item.accion)">{{ item.accion?.toUpperCase() ?? '—' }}</span>
                    </td>
                    <td class="sm">{{ (item.nombre_modulo || 'SISTEMA').toUpperCase() }}</td>
                    <td class="sm">{{ item.detalle ?? '—' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div v-else class="tab-vacio">
            <svg fill="none" viewBox="0 0 24 24" stroke="#BDBDBD" width="52" height="52"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            <p>NO HAY REGISTROS EN LA BITÁCORA PARA ESTE ALUMNO.</p>
          </div>

        </div>
        </Transition>

      </template>

      <!-- Footer -->
      <footer class="pie-pagina">© 2026 TECNOLÓGICO NACIONAL DE MÉXICO · TODOS LOS DERECHOS RESERVADOS</footer>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const route  = useRoute()
const router = useRouter()
const API_URL = import.meta.env.VITE_API_URL

// ── Parámetro de ruta ─────────────────────────────────────────────────
const noControl = computed(() => route.params.noControl ?? '')

// ── Búsqueda de alumno (cuando se entra sin parámetro) ────────────────
const noControlBusqueda  = ref('')
const resultadosBusqueda = ref([])
const buscando           = ref(false)

const normalize = (t) => !t ? '' : t.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')

const buscarAlumno = async () => {
  const q = noControlBusqueda.value.trim()
  if (!q) return

  // Si parece un número de control exacto, navegar directo
  const soloNumeros = /^\d+$/.test(q)
  if (soloNumeros) {
    router.push(`/alumnos/expediente/${encodeURIComponent(q)}`)
    return
  }

  // Búsqueda por nombre contra la API (con caché)
  buscando.value = true
  resultadosBusqueda.value = []
  try {
    if (!_cacheAlumnos) {
      const res = await fetch(`${API_URL}/api/alumnos-crud`)
      if (!res.ok) throw new Error()
      const data = await res.json()
      _cacheAlumnos = Array.isArray(data) ? data : (data.alumnos ?? [])
    }
    const lista = Array.isArray(data) ? data : (data.alumnos ?? [])
    const qNorm = normalize(q)
    resultadosBusqueda.value = _cacheAlumnos
      .filter(a => {
        const nombre = normalize(a.nombre ?? '')
        const nc     = (a.numero_control ?? '').toString()
        return nombre.includes(qNorm) || nc.includes(q)
      })
      .slice(0, 10)
  } catch {
    resultadosBusqueda.value = []
  } finally {
    buscando.value = false
  }
}

const abrirExpediente = (nc) => {
  router.push(`/alumnos/expediente/${encodeURIComponent(nc)}`)
}

// ── Estado general ────────────────────────────────────────────────────
const cargando        = ref(false)
const cargandoAlumno  = ref(false)
const errorAlumno     = ref(false)
const alumno          = ref(null)

// ── Tabs ──────────────────────────────────────────────────────────────
const tabActivo = ref('datos')

const tabs = [
  { id: 'datos',    label: 'DATOS GENERALES',     icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
  { id: 'kardex',   label: 'KARDEX',              icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
  { id: 'historial',label: 'HISTORIAL ACADÉMICO', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
  { id: 'horario',  label: 'HORARIO / INSCRIPCIONES', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
  { id: 'bitacora', label: 'BITÁCORA',            icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' },
]

// ── Kardex ────────────────────────────────────────────────────────────
const kardexData      = ref(null)
const cargandoKardex  = ref(false)
const errorKardex     = ref(false)
const semestresAbiertos = ref([])

// ── Historial ─────────────────────────────────────────────────────────
const historialData     = ref([])
const cargandoHistorial = ref(false)
const filtroHistorial   = ref('')

// ── Horario / Inscripciones ───────────────────────────────────────────
const horarioData           = ref([])
const cargandoHorario       = ref(false)
const inscripcionesData     = ref([])
const cargandoInscripciones = ref(false)
const subtabHorario         = ref('horario')

// ── Bitácora ──────────────────────────────────────────────────────────
const bitacoraData    = ref([])
const cargandoBitacora = ref(false)

// ── Toast ─────────────────────────────────────────────────────────────
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerToast = null
const mostrarToast = (mensaje, tipo = 'exito') => {
  if (timerToast) clearTimeout(timerToast)
  toast.value = { visible: true, mensaje, tipo }
  timerToast = setTimeout(() => { toast.value.visible = false }, 3500)
}

// ── Carga del alumno ──────────────────────────────────────────────────
// ── Caché de sesión para no repetir el fetch de toda la lista ─────────
let _cacheAlumnos = null

const cargarAlumno = async () => {
  if (!noControl.value) { return }
  cargandoAlumno.value = true
  errorAlumno.value    = false
  try {
    if (!_cacheAlumnos) {
      const res = await fetch(`${API_URL}/api/alumnos-crud`)
      if (!res.ok) throw new Error(`HTTP ${res.status}`)
      const data = await res.json()
      _cacheAlumnos = Array.isArray(data) ? data : (data.alumnos ?? [])
    }
    const encontrado = _cacheAlumnos.find(a =>
      String(a.numero_control ?? '').trim() === String(noControl.value).trim()
    )
    if (!encontrado) { errorAlumno.value = true; return }
    alumno.value = encontrado
  } catch (e) {
    console.error('[ExpedienteSE] cargarAlumno:', e)
    errorAlumno.value = true
  } finally {
    cargandoAlumno.value = false
  }
}

// ── Carga de kardex ───────────────────────────────────────────────────
const cargarKardex = async () => {
  if (!noControl.value) return
  cargandoKardex.value = true
  errorKardex.value    = false
  try {
    const res = await fetch(`${API_URL}/api/kardex/${encodeURIComponent(noControl.value)}`)
    if (!res.ok) throw new Error()
    kardexData.value = await res.json()
    if (kardexData.value?.kardex?.semestres?.length) {
      semestresAbiertos.value = [kardexData.value.kardex.semestres[0].numero]
    }
  } catch {
    errorKardex.value = true
    mostrarToast('NO SE PUDO CARGAR EL KARDEX.', 'error')
  } finally {
    cargandoKardex.value = false
  }
}

// ── Carga de historial académico ──────────────────────────────────────
const cargarHistorial = async () => {
  if (!noControl.value) return
  cargandoHistorial.value = true
  try {
    const res = await fetch(`${API_URL}/api/alumnos/${encodeURIComponent(noControl.value)}/historial`)
    if (!res.ok) throw new Error()
    const data = await res.json()
    historialData.value = Array.isArray(data) ? data : (data.historial ?? [])
  } catch {
    historialData.value = []
    mostrarToast('NO SE PUDO CARGAR EL HISTORIAL ACADÉMICO.', 'error')
  } finally {
    cargandoHistorial.value = false
  }
}

// ── Carga de horario ──────────────────────────────────────────────────
const cargarHorario = async () => {
  if (!noControl.value) return
  cargandoHorario.value = true
  try {
    const res = await fetch(`${API_URL}/api/horario/${encodeURIComponent(noControl.value)}`)
    if (!res.ok) throw new Error()
    const data = await res.json()
    horarioData.value = Array.isArray(data) ? data : (data.horario ?? [])
  } catch {
    horarioData.value = []
  } finally {
    cargandoHorario.value = false
  }
}

// ── Carga de historial de inscripciones ───────────────────────────────
const cargarInscripciones = async () => {
  if (!noControl.value) return
  cargandoInscripciones.value = true
  try {
    const res = await fetch(`${API_URL}/api/alumnos/${encodeURIComponent(noControl.value)}/inscripciones`)
    if (!res.ok) throw new Error()
    const data = await res.json()
    inscripcionesData.value = Array.isArray(data) ? data : (data.inscripciones ?? [])
  } catch {
    inscripcionesData.value = []
  } finally {
    cargandoInscripciones.value = false
  }
}

// ── Carga de bitácora del alumno ──────────────────────────────────────
const cargarBitacora = async () => {
  if (!noControl.value) return
  cargandoBitacora.value = true
  try {
    const res = await fetch(`${API_URL}/api/bitacora?numero_control=${encodeURIComponent(noControl.value)}&limit=50`)
    if (!res.ok) throw new Error()
    const data = await res.json()
    bitacoraData.value = Array.isArray(data) ? data : (data.bitacora ?? [])
  } catch {
    bitacoraData.value = []
  } finally {
    cargandoBitacora.value = false
  }
}

// ── Cambiar tab y cargar datos lazy ───────────────────────────────────
const cambiarTab = async (idTab) => {
  tabActivo.value = idTab
  if (idTab === 'kardex'    && !kardexData.value       && !cargandoKardex.value)       await cargarKardex()
  if (idTab === 'historial' && !historialData.value.length && !cargandoHistorial.value) await cargarHistorial()
  if (idTab === 'horario'   && !horarioData.value.length && !cargandoHorario.value)    { await cargarHorario(); await cargarInscripciones() }
  if (idTab === 'bitacora'  && !bitacoraData.value.length && !cargandoBitacora.value)  await cargarBitacora()
}

// ── Computed ──────────────────────────────────────────────────────────
const porcentajeCreditos = computed(() => {
  const a = kardexData.value?.alumno
  if (!a?.creditos_totales) return 0
  return Math.round((a.creditos_acumulados / a.creditos_totales) * 100)
})

const historialFiltrado = computed(() => {
  if (!filtroHistorial.value) return historialData.value
  return historialData.value.filter(h => h.tipo === filtroHistorial.value)
})

const historialAgrupado = computed(() => {
  const grupos = {}
  historialFiltrado.value.forEach(h => {
    const key = h.periodo ?? 'SIN PERIODO'
    if (!grupos[key]) grupos[key] = []
    grupos[key].push(h)
  })
  return grupos
})

// ── Helpers ───────────────────────────────────────────────────────────
const iniciales = (n = '') => n.split(' ').slice(0,2).map(p => p[0]).join('').toUpperCase()
const resolverCarrera = (a) => a?.carrera?.nombre_carrera || a?.carrera?.nombre || (typeof a?.carrera === 'string' ? a.carrera : '') || ''

const fFecha = (iso) => {
  if (!iso) return 'NO REGISTRADO'
  try { return new Date(iso).toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' }).toUpperCase() }
  catch { return iso }
}

const fFechaHora = (iso) => {
  if (!iso) return '—'
  try { return new Date(iso).toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }
  catch { return iso }
}

const claseEstatus = (e = '') => {
  const m = { 'Activo':'es-act', 'Inactivo':'es-ina', 'Egresado':'es-egr', 'Suspendido':'es-sus', 'Baja':'es-baj' }
  return m[e] ?? 'es-ina'
}

const claseCalif = (c) => {
  if (c === null || c === undefined) return 'ca-nd'
  if (c >= 80) return 'ca-v'
  if (c >= 70) return 'ca-a'
  return 'ca-r'
}

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
  const map = { acreditada: 'ACRED.', reprobada: 'REPR.', pendiente: 'EN CURSO', no_cursada: 'PENDIENTE' }
  return map[estado] ?? (estado?.toUpperCase() ?? '—')
}

const claseBadge = (a = '') => {
  const s = a.toLowerCase()
  if (s.includes('inscri') || s.includes('cre') || s.includes('alta')) return 'bg-g'
  if (s.includes('edit') || s.includes('actualiz'))                      return 'bg-a'
  if (s.includes('elim') || s.includes('baja'))                          return 'bg-r'
  return 'bg-b'
}

const toggleSemestre = (num) => {
  const idx = semestresAbiertos.value.indexOf(num)
  if (idx === -1) semestresAbiertos.value.push(num)
  else semestresAbiertos.value.splice(idx, 1)
}

const imprimirExpediente = () => window.print()

// ── Lifecycle ─────────────────────────────────────────────────────────
onMounted(async () => {
  await cargarAlumno()
  // El kardex se carga automáticamente porque datos es el tab inicial
  // Los demás tabs cargan lazy al hacer clic
})

// Si cambia el noControl (navegación entre expedientes)
watch(() => route.params.noControl, async (nuevo) => {
  if (!nuevo) return
  alumno.value          = null
  kardexData.value      = null
  historialData.value   = []
  horarioData.value     = []
  inscripcionesData.value = []
  bitacoraData.value    = []
  tabActivo.value       = 'datos'
  semestresAbiertos.value = []
  await cargarAlumno()
})
</script>

<style scoped>
.expediente-page {
  font-family: 'Montserrat', system-ui, sans-serif;
  background: #F4F6F9;
  display: flex;
  flex-direction: column;
  gap: 16px;
  min-height: 100%;
  padding-bottom: 2rem;
}

/* ── Barra de carga ── */
.barra-carga { position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 1001; opacity: 0; pointer-events: none; transition: opacity .2s; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: linear-gradient(90deg, #0B2545, #1D52B7, #0B2545); background-size: 200% 100%; animation: carga-anim 1.4s linear infinite; width: 100%; }
@keyframes carga-anim { 0%{background-position:200% 0}100%{background-position:-200% 0} }

/* ── Toast ── */
.toast { position: fixed; bottom: 2rem; right: 2rem; display: flex; align-items: center; gap: 10px; padding: .9rem 1.4rem; border-radius: 10px; color: #FFFFFF; font-weight: 700; font-size: .9rem; box-shadow: 0 8px 24px rgba(0,0,0,.15); z-index: 3000; max-width: 380px; letter-spacing: .03em; }
.toast.exito { background: #0B2545; }
.toast.error { background: #EB5757; }
.toast-slide-enter-active,.toast-slide-leave-active { transition: all .3s ease; }
.toast-slide-enter-from,.toast-slide-leave-to { opacity: 0; transform: translateX(100%); }

/* ── Breadcrumb ── */
.breadcrumb { display: flex; align-items: center; gap: 5px; font-size: 11px; color: #828282; letter-spacing: .04em; }
.breadcrumb-sep { color: #BDBDBD; }
.breadcrumb-link { color: #828282; text-decoration: none; transition: color .15s; }
.breadcrumb-link:hover { color: #1D52B7; }
.breadcrumb-actual { color: #1D52B7; font-weight: 700; letter-spacing: .04em; }

/* ── Estados de página ── */
.estado-cargando-pagina { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px; padding: 4rem; color: #828282; font-size: .9rem; letter-spacing: .04em; }
.spinner-grande { width: 44px; height: 44px; border: 3px solid #E0E0E0; border-top-color: #0B2545; border-radius: 50%; animation: girar .8s linear infinite; }
@keyframes girar { to { transform: rotate(360deg); } }
.error-pagina { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; padding: 4rem; color: #828282; text-align: center; }
.error-pagina h2 { font-size: 1.2rem; font-weight: 700; color: #333; letter-spacing: .03em; }
.error-pagina p { font-size: .9rem; letter-spacing: .03em; }

/* ── Header del alumno ── */
.alumno-header { background: #0B2545; border-radius: 14px; padding: 22px 26px; display: flex; align-items: flex-start; gap: 20px; flex-wrap: wrap; }
.alumno-avatar { width: 72px; height: 72px; border-radius: 50%; background: rgba(255,255,255,.12); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; font-weight: 700; flex-shrink: 0; border: 2px solid rgba(255,255,255,.25); }
.alumno-info { flex: 1; min-width: 0; }
.alumno-nombre-row { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 10px; }
.alumno-nombre { font-size: 1.4rem; font-weight: 700; color: #FFFFFF; margin: 0; letter-spacing: .02em;  font-family: 'Montserrat', sans-serif;}
.alumno-meta-grid { display: flex; flex-wrap: wrap; gap: 12px 24px; }
.alumno-meta-item { display: flex; align-items: center; gap: 5px; font-size: .82rem; color: rgba(255,255,255,.6); letter-spacing: .03em; }
.alumno-meta-item svg { stroke: rgba(255,255,255,.5); flex-shrink: 0; }
.alumno-meta-item strong { color: #FFFFFF; }
.alumno-acciones { display: flex; flex-direction: column; gap: 8px; flex-shrink: 0; }

/* ── Estatus chips ── */
.estatus-chip { font-size: .75rem; font-weight: 700; padding: 3px 12px; border-radius: 20px; white-space: nowrap; letter-spacing: .04em; }
.es-act { background: rgba(39,174,96,.15); color: #27AE60; border: 1px solid rgba(39,174,96,.3); }
.es-ina { background: rgba(255,255,255,.1); color: rgba(255,255,255,.6); border: 1px solid rgba(255,255,255,.2); }
.es-egr { background: rgba(47,128,237,.15); color: #2F80ED; border: 1px solid rgba(47,128,237,.3); }
.es-sus { background: rgba(242,153,74,.15); color: #F2994A; border: 1px solid rgba(242,153,74,.3); }
.es-baj { background: rgba(235,87,87,.15); color: #EB5757; border: 1px solid rgba(235,87,87,.3); }

/* ── Botones ── */
.btn-primario { display: flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 8px; font-family: 'Montserrat', sans-serif; font-size: .82rem; font-weight: 700; cursor: pointer; background: #1D52B7; color: #FFFFFF; border: none; transition: background .15s; letter-spacing: .04em; white-space: nowrap; }
.btn-primario:hover { background: #1A4184; }
.btn-outline { display: flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 8px; font-family: 'Montserrat', sans-serif; font-size: .82rem; font-weight: 700; cursor: pointer; background: rgba(255,255,255,.1); color: #FFFFFF; border: 1px solid rgba(255,255,255,.25); transition: background .15s; letter-spacing: .04em; white-space: nowrap; }
.btn-outline:hover { background: rgba(255,255,255,.18); }

/* ── Tabs ── */
.tabs-bar { display: flex; overflow-x: auto; background: #FFFFFF; border-radius: 12px; border: 1px solid #E0E0E0; box-shadow: 0 2px 8px rgba(29,82,183,.05); scrollbar-width: none; }
.tabs-bar::-webkit-scrollbar { display: none; }
.tab-btn { display: flex; align-items: center; gap: 6px; padding: 14px 18px; border: none; background: none; font-family: 'Montserrat', sans-serif; font-size: .82rem; font-weight: 700; color: #828282; cursor: pointer; white-space: nowrap; border-bottom: 3px solid transparent; margin-bottom: -1px; transition: color .15s, border-color .15s; letter-spacing: .04em; }
.tab-btn svg { stroke: currentColor; }
.tab-btn:hover { color: #1D52B7; }
.tab-activo { color: #1D52B7; border-bottom-color: #1D52B7; }
.tab-badge { background: #EB5757; color: #FFFFFF; font-size: 9px; font-weight: 700; border-radius: 10px; padding: 1px 6px; }

/* ── Tab body ── */
.tab-body { background: #FFFFFF; border-radius: 14px; border: 1px solid #E0E0E0; padding: 24px; box-shadow: 0 2px 8px rgba(29,82,183,.05); }
.tab-fade-enter-active,.tab-fade-leave-active { transition: opacity .18s ease, transform .18s ease; }
.tab-fade-enter-from { opacity: 0; transform: translateY(6px); }
.tab-fade-leave-to   { opacity: 0; transform: translateY(-4px); }

/* ── Sección grid (datos generales) ── */
.seccion-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
.info-card { background: #F4F6F9; border-radius: 12px; border: 1px solid #E0E0E0; padding: 16px 18px; }
.info-card-header { display: flex; align-items: center; gap: 8px; margin-bottom: 14px; }
.info-card-header svg { stroke: #1D52B7; flex-shrink: 0; }
.info-card-header h3 { font-size: .82rem; font-weight: 700; color: #1D52B7; margin: 0; letter-spacing: .05em;  font-family: 'Montserrat', sans-serif;}
.datos-lista { margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.dato-item { display: flex; justify-content: space-between; align-items: baseline; gap: 8px; }
.dato-full { flex-direction: column; align-items: flex-start; gap: 2px; }
.dato-item dt { font-size: .75rem; color: #828282; font-weight: 600; letter-spacing: .04em; flex-shrink: 0; }
.dato-item dd { font-size: .85rem; color: #333333; font-weight: 600; text-align: right; margin: 0; }
.dato-full dd { text-align: left; }
.mono { font-family: monospace; letter-spacing: .05em; }
.sm { font-size: .8rem; color: #828282; }

/* ── KPIs de kardex ── */
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 16px; }
.kpi-card { background: #F4F6F9; border-radius: 12px; border: 1px solid #E0E0E0; padding: 16px; text-align: center; border-left: 4px solid transparent; }
.kpi-valor { font-size: 2rem; font-weight: 700; line-height: 1; margin-bottom: 4px; }
.kpi-label { font-size: .72rem; font-weight: 700; color: #828282; letter-spacing: .04em; }
.kpi-azul { border-left-color: #1D52B7; } .kpi-azul .kpi-valor { color: #1D52B7; }
.kpi-verde { border-left-color: #27AE60; } .kpi-verde .kpi-valor { color: #27AE60; }
.kpi-naranja { border-left-color: #F2994A; } .kpi-naranja .kpi-valor { color: #F2994A; }
.kpi-rojo { border-left-color: #EB5757; } .kpi-rojo .kpi-valor { color: #EB5757; }

/* ── Barra progreso ── */
.prog-card { background: #F4F6F9; border: 1px solid #E0E0E0; border-radius: 12px; padding: 16px 18px; margin-bottom: 16px; }
.prog-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.prog-label { font-size: .82rem; font-weight: 700; color: #333333; letter-spacing: .03em; }
.prog-pct { font-size: 1.1rem; font-weight: 700; color: #1D52B7; }
.prog-track { height: 10px; background: #E0E0E0; border-radius: 99px; overflow: hidden; }
.prog-fill { height: 100%; background: linear-gradient(to right, #0B2545, #1D52B7); border-radius: 99px; transition: width .6s; }
.prog-detalle { font-size: .78rem; color: #828282; margin: 6px 0 0; letter-spacing: .03em; }

/* ── Semestres del kardex ── */
.semestre-bloque { border: 1px solid #E0E0E0; border-radius: 10px; overflow: hidden; background: #FFFFFF; margin-bottom: 8px; }
.semestre-btn { width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: none; border: none; cursor: pointer; font-family: 'Montserrat', sans-serif; transition: background .15s; }
.semestre-btn:hover { background: #F4F6F9; }
.semestre-btn.abierto { background: rgba(29,82,183,.06); }
.sem-titulo { font-size: .9rem; font-weight: 700; color: #333333; letter-spacing: .04em; }
.sem-badges { display: flex; align-items: center; gap: 6px; }
.badge-mini { font-size: .72rem; font-weight: 700; padding: 2px 8px; border-radius: 20px; letter-spacing: .03em; }
.badge-mini.verde { background: rgba(39,174,96,.1); color: #27AE60; }
.badge-mini.rojo  { background: rgba(235,87,87,.1); color: #EB5757; }
.badge-mini.gris  { background: #F2F4F7; color: #828282; }
.sem-flecha { stroke: #828282; transition: transform .2s; flex-shrink: 0; }
.sem-flecha.girado { transform: rotate(180deg); }
.expand-enter-active,.expand-leave-active { transition: all .2s ease; }
.expand-enter-from,.expand-leave-to { opacity: 0; transform: translateY(-4px); }
.semestre-materias { border-top: 1px solid #E0E0E0; }

/* ── Tablas ── */
.tabla-wrap { overflow-x: auto; }
.tabla-kardex, .tabla-historial { width: 100%; border-collapse: collapse; }
.tabla-kardex th, .tabla-historial th { background: #F4F6F9; padding: 10px 14px; font-size: .75rem; font-weight: 700; color: #828282; text-align: left; border-bottom: 1px solid #E0E0E0; letter-spacing: .04em; white-space: nowrap; }
.tabla-kardex td, .tabla-historial td { padding: 9px 14px; font-size: .85rem; color: #333333; border-bottom: 1px solid #F4F6F9; }
.tabla-kardex tr:last-child td, .tabla-historial tr:last-child td { border-bottom: none; }
.tabla-kardex tr:hover td, .tabla-historial tr:hover td { background: rgba(29,82,183,.03); }
.tabla-kardex tr.fila-repr td { background: #FFF5F5; }
.tc { text-align: center; }
.chip-calif { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; padding: 3px 8px; border-radius: 6px; font-size: .82rem; font-weight: 700; }
.ca-v { background: rgba(39,174,96,.1); color: #27AE60; }
.ca-a { background: rgba(242,153,74,.1); color: #F2994A; }
.ca-r { background: rgba(235,87,87,.1); color: #EB5757; }
.ca-nd { background: #F2F4F7; color: #828282; }
.chip-estado { display: inline-flex; padding: 2px 10px; border-radius: 20px; font-size: .72rem; font-weight: 700; letter-spacing: .03em; }
.tipo-badge { display: inline-flex; padding: 2px 10px; border-radius: 20px; font-size: .72rem; font-weight: 700; letter-spacing: .03em; }
.tipo-normal { background: #F2F4F7; color: #828282; }
.tipo-especial { background: #EDE9FE; color: #7C3AED; }
.tipo-reinscripcion { background: rgba(47,128,237,.1); color: #1D52B7; }

/* ── Historial ── */
.filtros-historial { margin-bottom: 16px; }
.filtro-select { padding: 8px 12px; border: 1px solid #E0E0E0; border-radius: 8px; font-size: .82rem; font-family: 'Montserrat', sans-serif; color: #333333; background: #FFFFFF; outline: none; letter-spacing: .03em; cursor: pointer; }
.filtro-select:focus { border-color: #1D52B7; }
.historial-grupo { margin-bottom: 20px; }
.historial-periodo-header { display: flex; align-items: center; justify-content: space-between; padding: 8px 14px; background: #F4F6F9; border-radius: 8px; margin-bottom: 8px; }
.historial-periodo-label { font-size: .85rem; font-weight: 700; color: #333333; letter-spacing: .04em; }
.historial-periodo-count { font-size: .75rem; color: #828282; letter-spacing: .03em; }

/* ── Horario ── */
.subtabs-nav { display: flex; gap: 8px; margin-bottom: 16px; }
.subtab-btn { padding: 8px 16px; border-radius: 8px; border: 1px solid #E0E0E0; background: #FFFFFF; font-family: 'Montserrat', sans-serif; font-size: .78rem; font-weight: 700; color: #828282; cursor: pointer; transition: all .15s; letter-spacing: .04em; }
.subtab-btn:hover { background: #F4F6F9; color: #333333; }
.subtab-btn.activo { background: #0B2545; color: #FFFFFF; border-color: #0B2545; }
.horario-lista { display: flex; flex-direction: column; gap: 8px; }
.horario-card { display: flex; align-items: center; gap: 16px; background: #F4F6F9; border: 1px solid #E0E0E0; border-radius: 10px; padding: 12px 16px; transition: border-color .15s; }
.horario-card:hover { border-color: #1D52B7; }
.hor-dia { font-size: .75rem; font-weight: 700; color: #1D52B7; background: rgba(29,82,183,.08); padding: 4px 10px; border-radius: 6px; white-space: nowrap; flex-shrink: 0; min-width: 72px; text-align: center; letter-spacing: .04em; }
.hor-info { flex: 1; min-width: 0; }
.hor-materia { font-size: .9rem; font-weight: 700; color: #333333; margin: 0 0 2px; letter-spacing: .02em; }
.hor-detalle { font-size: .78rem; color: #828282; margin: 0; letter-spacing: .03em; }
.hor-docente { display: flex; flex-direction: column; align-items: flex-end; text-align: right; flex-shrink: 0; }
.hor-docente-label { font-size: .7rem; color: #828282; letter-spacing: .04em; }
.hor-docente-nombre { font-size: .8rem; font-weight: 700; color: #333333; letter-spacing: .02em; }

/* ── Bitácora ── */
.bit-usuario { display: flex; align-items: center; gap: 8px; }
.bit-avatar { width: 26px; height: 26px; border-radius: 50%; background: #0B2545; color: #FFFFFF; font-size: .7rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.badge-accion { display: inline-flex; padding: 2px 8px; border-radius: 20px; font-size: .72rem; font-weight: 700; letter-spacing: .03em; }
.bg-g { background: rgba(39,174,96,.1); color: #27AE60; }
.bg-b { background: rgba(47,128,237,.1); color: #1D52B7; }
.bg-a { background: rgba(242,153,74,.1); color: #F2994A; }
.bg-r { background: rgba(235,87,87,.1); color: #EB5757; }

/* ── Skeletons ── */
.skeleton-wrap { display: flex; flex-direction: column; gap: 8px; }
.skeleton-linea { height: 14px; border-radius: 6px; background: linear-gradient(90deg, #E0E0E0 25%, #F2F4F7 50%, #E0E0E0 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }
.skeleton-linea.largo { width: 70%; }
.skeleton-linea.medio { width: 45%; }
.skeleton-fila { height: 44px; border-radius: 8px; background: linear-gradient(90deg, #F2F4F7 25%, #FFFFFF 50%, #F2F4F7 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }
@keyframes shimmer { 0%{background-position:200% 0}100%{background-position:-200% 0} }

/* ── Tab vacío ── */
.tab-vacio { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 3rem 1rem; color: #828282; text-align: center; }
.tab-vacio p { font-size: .88rem; margin: 0; letter-spacing: .03em; }

/* ── Error inline ── */
.estado-error-inline { display: flex; align-items: center; gap: 10px; background: #FFF0F0; border: 1px solid rgba(235,87,87,.2); border-radius: 10px; padding: 12px 16px; color: #EB5757; font-size: .88rem; font-weight: 600; letter-spacing: .03em; }
.estado-error-inline svg { stroke: #EB5757; flex-shrink: 0; }
.btn-reintentar { margin-left: auto; background: #EB5757; color: #FFFFFF; border: none; padding: 5px 14px; border-radius: 6px; font-weight: 700; font-size: .8rem; cursor: pointer; font-family: 'Montserrat', sans-serif; transition: background .15s; letter-spacing: .04em; }
.btn-reintentar:hover { background: #c0392b; }

/* ── Buscador de expediente (sin alumno) ── */
.busqueda-expediente {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 3rem 1rem 4rem;
  max-width: 560px;
  margin: 0 auto;
  text-align: center;
}
.busq-icono-wrap { color: #1D52B7; opacity: .5; }
.busq-titulo { font-size: 1.3rem; font-weight: 800; color: #0B2545; letter-spacing: .06em; margin: 0;  font-family: 'Montserrat', sans-serif;}
.busq-subtitulo { font-size: .85rem; color: #828282; letter-spacing: .03em; margin: 0; }
.busq-input-wrap { display: flex; gap: 10px; width: 100%; align-items: center; }
.busq-input-group {
  position: relative;
  flex: 1;
}
.busq-input-icono {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  stroke: #828282;
  pointer-events: none;
}
.busq-input {
  width: 100%;
  padding: 11px 36px 11px 40px;
  border: 1.5px solid #E0E0E0;
  border-radius: 10px;
  font-family: 'Montserrat', sans-serif;
  font-size: .9rem;
  color: #333333;
  outline: none;
  transition: border-color .15s;
  box-sizing: border-box;
  letter-spacing: .02em;
}
.busq-input:focus { border-color: #1D52B7; }
.busq-btn-limpiar {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: #828282;
  display: flex;
  align-items: center;
  padding: 2px;
}
.busq-btn-limpiar:hover { color: #333333; }
.busq-btn-buscar { white-space: nowrap; flex-shrink: 0; display: flex; align-items: center; gap: 6px; }
.busq-spinner {
  width: 14px; height: 14px;
  border: 2px solid rgba(255,255,255,.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: giro .7s linear infinite;
  flex-shrink: 0;
}
@keyframes giro { to { transform: rotate(360deg); } }
.busq-resultados { width: 100%; text-align: left; }
.busq-resultados-titulo { font-size: .78rem; font-weight: 700; color: #828282; letter-spacing: .04em; margin: 0 0 10px; }
.busq-lista { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 6px; }
.busq-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  border: 1.5px solid #E0E0E0;
  border-radius: 10px;
  background: #FFFFFF;
  cursor: pointer;
  transition: border-color .15s, background .15s;
}
.busq-item:hover { border-color: #1D52B7; background: rgba(29,82,183,.03); }
.busq-item-avatar {
  width: 36px; height: 36px; border-radius: 50%;
  background: #0B2545; color: #FFFFFF;
  font-size: .78rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; letter-spacing: .02em;
}
.busq-item-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.busq-item-nombre { font-size: .9rem; font-weight: 700; color: #333333; letter-spacing: .02em; }
.busq-item-meta { font-size: .75rem; color: #828282; letter-spacing: .03em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.busq-item-flecha { stroke: #BDBDBD; flex-shrink: 0; }
.busq-sin-resultados {
  display: flex; flex-direction: column; align-items: center;
  gap: 8px; color: #828282; font-size: .85rem; letter-spacing: .03em;
  padding: 1.5rem 0;
}
.busq-sin-resultados svg { stroke: #BDBDBD; }

/* ── Footer ── */
.pie-pagina { text-align: center; color: #BDBDBD; font-size: .78rem; padding: 2rem 0; border-top: 1px solid #E0E0E0; letter-spacing: .04em; }

/* ── Responsive ── */
@media (max-width: 1024px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } .seccion-grid { grid-template-columns: 1fr; } }
@media (max-width: 768px) { .alumno-header { flex-direction: column; gap: 14px; } .alumno-acciones { flex-direction: row; } .tabs-bar { border-radius: 8px; } .tab-body { padding: 16px; } .kpi-grid { grid-template-columns: repeat(2, 1fr); } .horario-card { flex-direction: column; align-items: flex-start; gap: 8px; } .hor-docente { align-items: flex-start; text-align: left; } }
@media (max-width: 480px) { .kpi-grid { grid-template-columns: 1fr 1fr; } .alumno-acciones { flex-direction: column; width: 100%; } .btn-primario, .btn-outline { width: 100%; justify-content: center; } }

/* ── Print ── */
@media print { .barra-carga, .toast, .btn-primario, .btn-outline, .tabs-bar, .alumno-acciones { display: none !important; } .tab-body { display: block !important; } .expediente-page { background: white; } }
</style>