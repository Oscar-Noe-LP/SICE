<template>
  <MainLayout>
  <div class="egresados-se">

    <!-- ══ ENCABEZADO DEL MÓDULO ══ -->
    <div class="modulo-header">
      <div class="modulo-header__izq">
        
        <div>
          <h1 class="modulo-titulo">{{ seccionActual.titulo }}</h1>
          <p class="modulo-subtitulo">{{ seccionActual.descripcion }}</p>
        </div>
      </div>

      <!-- Acciones del encabezado según sección -->
      <div class="modulo-header__der">
        <button v-if="seccionActiva === 'registro'" class="btn-primary" @click="abrirModalRegistro">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
          </svg>
          Nuevo Registro
        </button>
        <button class="btn-secondary" @click="exportarDatos">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 7.414V19a2 2 0 01-2 2z"/>
          </svg>
          Exportar
        </button>
      </div>
    </div>

    <!-- ══ BARRA DE FILTROS Y BÚSQUEDA ══ -->
    <div class="filtros-bar">
      <div class="filtros-bar__busqueda">
        <input
          v-model="busqueda"
          type="text"
          :placeholder="seccionActual.placeholder"
          class="input-busqueda"
        />
        <button v-if="busqueda" class="btn-limpiar-busqueda" @click="busqueda = ''">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <div class="filtros-bar__controles">
        <select v-model="filtroCarrera" class="select-filtro">
          <option value="">Todas las carreras</option>
          <option value="SC">Ing. Sistemas Computacionales</option>
          <option value="II">Ing. Industrial</option>
          <option value="IE">Ing. En Gestión Empresarial</option>
          <option value="IM">Ing. Civil</option>
          <option value="GE">Contador Público</option>
        </select>

        <select v-if="seccionActiva !== 'posibles'" v-model="filtroAnio" class="select-filtro">
          <option value="">Todos los años</option>
          <option value="2025">2025</option>
          <option value="2024">2024</option>
          <option value="2023">2023</option>
          <option value="2022">2022</option>
        </select>

        <button class="btn-filtro-activo" :class="{ 'btn-filtro-activo--on': filtrosActivos }" @click="limpiarFiltros">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
          </svg>
          {{ filtrosActivos ? 'Limpiar filtros' : 'Filtros' }}
        </button>
      </div>
    </div>

    <!-- ══ KPIs DE RESUMEN (solo en Egresados y Titulados) ══ -->
    <div v-if="seccionActiva === 'lista' || seccionActiva === 'titulados'" class="kpi-row">
      <div v-for="kpi in kpisActuales" :key="kpi.label" class="kpi-card">
        <div class="kpi-card__icono" :style="{ background: kpi.bgColor }">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" :style="{ stroke: kpi.color }">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="kpi.icono"/>
          </svg>
        </div>
        <div class="kpi-card__datos">
          <span class="kpi-card__numero" :style="{ color: kpi.color }">{{ kpi.valor }}</span>
          <span class="kpi-card__label">{{ kpi.label }}</span>
        </div>
      </div>
    </div>

    <!-- ══ CONTENIDO PRINCIPAL POR SECCIÓN ══ -->

    <!-- ─── POSIBLES EGRESADOS ─── -->
    <div v-if="seccionActiva === 'posibles'" class="contenido-seccion">
      <div class="alerta-info">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Alumnos que han cubierto el 100% de créditos pero aún no han sido formalmente egresados. Verifica su situación académica antes de proceder.</span>
      </div>

      <div class="tabla-card">
        <div class="tabla-card__header">
          <span class="tabla-card__titulo">Candidatos a Egreso</span>
          <span class="tabla-card__conteo">{{ alumnosFiltrados.length }} registros</span>
        </div>
        <div class="tabla-scroll">
          <table class="tabla-datos">
            <thead>
              <tr>
                <th @click="ordenarPor('ctrl')" class="th-ordenable">
                  No. Control
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/></svg>
                </th>
                <th>Nombre Completo</th>
                <th>Carrera</th>
                <th @click="ordenarPor('creditos')" class="th-ordenable">
                  Créditos
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/></svg>
                </th>
                <th>Promedio</th>
                <th>Situación</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="alumno in alumnosFiltrados" :key="alumno.ctrl" class="fila-dato">
                <td class="td-ctrl">{{ alumno.ctrl }}</td>
                <td class="td-nombre">
                  <div class="avatar-nombre">
                    <div class="avatar-circulo" :style="{ background: generarColorAvatar(alumno.nombre) }">
                      {{ alumno.nombre.charAt(0) }}
                    </div>
                    <div>
                      <span class="nombre-principal">{{ alumno.nombre }}</span>
                      <span class="nombre-email">{{ alumno.email }}</span>
                    </div>
                  </div>
                </td>
                <td><span class="badge-carrera">{{ alumno.carrera }}</span></td>
                <td>
                  <div class="creditos-barra">
                    <div class="creditos-barra__fill" :style="{ width: (alumno.creditos / alumno.creditosTotales * 100) + '%' }"></div>
                    <span class="creditos-texto">{{ alumno.creditos }}/{{ alumno.creditosTotales }}</span>
                  </div>
                </td>
                <td>
                  <span class="promedio-num" :class="alumno.promedio >= 8 ? 'promedio-alto' : 'promedio-medio'">
                    {{ alumno.promedio.toFixed(1) }}
                  </span>
                </td>
                <td>
                  <span class="chip-estado" :class="'chip-' + alumno.situacion.tipo">
                    {{ alumno.situacion.texto }}
                  </span>
                </td>
                <td>
                  <div class="acciones-fila">
                    <button class="btn-accion btn-accion--ver" title="Ver expediente" @click="verExpediente(alumno)">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                    </button>
                    <button class="btn-accion btn-accion--aprobar" title="Confirmar egreso" @click="confirmarEgreso(alumno)">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="alumnosFiltrados.length === 0">
                <td colspan="7" class="tabla-vacia">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="40" height="40">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <p>No se encontraron candidatos con los filtros aplicados.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ─── EGRESADOS ─── -->
    <div v-else-if="seccionActiva === 'lista'" class="contenido-seccion">
      <div class="tabla-card">
        <div class="tabla-card__header">
          <span class="tabla-card__titulo">Padrón de Egresados</span>
          <span class="tabla-card__conteo">{{ egresadosFiltrados.length }} registros</span>
        </div>
        <div class="tabla-scroll">
          <table class="tabla-datos">
            <thead>
              <tr>
                <th>No. Control</th>
                <th>Nombre Completo</th>
                <th>Carrera</th>
                <th>Fecha de Egreso</th>
                <th>Promedio Final</th>
                <th>Estado Titulación</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="eg in egresadosFiltrados" :key="eg.ctrl" class="fila-dato">
                <td class="td-ctrl">{{ eg.ctrl }}</td>
                <td class="td-nombre">
                  <div class="avatar-nombre">
                    <div class="avatar-circulo" :style="{ background: generarColorAvatar(eg.nombre) }">
                      {{ eg.nombre.charAt(0) }}
                    </div>
                    <div>
                      <span class="nombre-principal">{{ eg.nombre }}</span>
                      <span class="nombre-email">{{ eg.email }}</span>
                    </div>
                  </div>
                </td>
                <td><span class="badge-carrera">{{ eg.carrera }}</span></td>
                <td class="td-fecha">{{ formatearFecha(eg.fechaEgreso) }}</td>
                <td>
                  <span class="promedio-num" :class="eg.promedio >= 9 ? 'promedio-alto' : eg.promedio >= 8 ? 'promedio-medio' : 'promedio-bajo'">
                    {{ eg.promedio.toFixed(1) }}
                  </span>
                </td>
                <td>
                  <span class="chip-estado" :class="'chip-' + eg.estadoTitulacion.tipo">
                    {{ eg.estadoTitulacion.texto }}
                  </span>
                </td>
                <td>
                  <div class="acciones-fila">
                    <button class="btn-accion btn-accion--ver" title="Ver expediente" @click="verExpediente(eg)">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                    </button>
                    <button class="btn-accion btn-accion--doc" title="Generar constancia" @click="generarConstancia(eg)">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="egresadosFiltrados.length === 0">
                <td colspan="7" class="tabla-vacia">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="40" height="40">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <p>No se encontraron egresados con los filtros aplicados.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ─── TITULADOS ─── -->
    <div v-else-if="seccionActiva === 'titulados'" class="contenido-seccion">
      <div class="tabla-card">
        <div class="tabla-card__header">
          <span class="tabla-card__titulo">Padrón de Titulados</span>
          <span class="tabla-card__conteo">{{ tituladosFiltrados.length }} registros</span>
        </div>
        <div class="tabla-scroll">
          <table class="tabla-datos">
            <thead>
              <tr>
                <th>No. Control</th>
                <th>Nombre Completo</th>
                <th>Carrera</th>
                <th>Modalidad de Titulación</th>
                <th>Fecha de Titulación</th>
                <th>No. Cédula</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="tit in tituladosFiltrados" :key="tit.ctrl" class="fila-dato">
                <td class="td-ctrl">{{ tit.ctrl }}</td>
                <td class="td-nombre">
                  <div class="avatar-nombre">
                    <div class="avatar-circulo" :style="{ background: generarColorAvatar(tit.nombre) }">
                      {{ tit.nombre.charAt(0) }}
                    </div>
                    <div>
                      <span class="nombre-principal">{{ tit.nombre }}</span>
                      <span class="nombre-email">{{ tit.email }}</span>
                    </div>
                  </div>
                </td>
                <td><span class="badge-carrera">{{ tit.carrera }}</span></td>
                <td>
                  <span class="chip-estado chip-info">{{ tit.modalidad }}</span>
                </td>
                <td class="td-fecha">{{ formatearFecha(tit.fechaTitulacion) }}</td>
                <td class="td-cedula">
                  <span v-if="tit.cedula" class="cedula-num">{{ tit.cedula }}</span>
                  <span v-else class="cedula-pendiente">Pendiente</span>
                </td>
                <td>
                  <div class="acciones-fila">
                    <button class="btn-accion btn-accion--ver" title="Ver expediente" @click="verExpediente(tit)">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                    </button>
                    <button class="btn-accion btn-accion--doc" title="Ver título" @click="verTitulo(tit)">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="tituladosFiltrados.length === 0">
                <td colspan="7" class="tabla-vacia">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="40" height="40">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <p>No se encontraron titulados con los filtros aplicados.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ─── REGISTRO DE TITULADOS ─── -->
    <div v-else-if="seccionActiva === 'registro'" class="contenido-seccion">
      <div class="alerta-info alerta-info--advertencia">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <span>Registra la titulación formal de egresados. Esta acción queda registrada en bitácora y no puede revertirse. Verifica todos los datos antes de guardar.</span>
      </div>

      <div class="registro-form-card">
        <div class="registro-form-card__titulo">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
          Nuevo Registro de Titulación
        </div>

        <div class="form-grid">
          <div class="form-grupo">
            <label class="form-label">Número de Control <span class="req">*</span></label>
            <div class="input-con-icono">
              <input v-model="formRegistro.ctrl" type="text" placeholder="Ej. 20310001" class="form-input" @blur="buscarEgresado"/>
            </div>
          </div>
          <div class="form-grupo">
            <label class="form-label">Nombre Completo</label>
            <input v-model="formRegistro.nombre" type="text" placeholder="Se llenará automáticamente" class="form-input" readonly/>
          </div>
          <div class="form-grupo">
            <label class="form-label">Modalidad de Titulación <span class="req">*</span></label>
            <select v-model="formRegistro.modalidad" class="form-input">
              <option value="">Seleccionar modalidad</option>
              <option value="Tesis">Tesis</option>
              <option value="Residencia Profesional">Residencia Profesional</option>
              <option value="Examen General de Egreso">Examen General de Egreso (EGEL)</option>
              <option value="Promedio Sobresaliente">Promedio Sobresaliente</option>
              <option value="Estudios de Posgrado">Estudios de Posgrado</option>
              <option value="Actividades de Investigación">Actividades de Investigación</option>
            </select>
          </div>
          <div class="form-grupo">
            <label class="form-label">Fecha de Titulación <span class="req">*</span></label>
            <input v-model="formRegistro.fecha" type="date" class="form-input"/>
          </div>
          <div class="form-grupo">
            <label class="form-label">Número de Cédula Profesional</label>
            <input v-model="formRegistro.cedula" type="text" placeholder="Ej. 12345678" class="form-input"/>
          </div>
          <div class="form-grupo">
            <label class="form-label">Observaciones</label>
            <input v-model="formRegistro.observaciones" type="text" placeholder="Notas adicionales (opcional)" class="form-input"/>
          </div>
        </div>

        <div class="form-acciones">
          <button class="btn-secondary" @click="limpiarFormulario">Limpiar</button>
          <button class="btn-primary" :disabled="!formValido" @click="guardarRegistro">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            Guardar Registro
          </button>
        </div>
      </div>

      <!-- Tabla de registros recientes -->
      <div class="tabla-card" style="margin-top:1.5rem">
        <div class="tabla-card__header">
          <span class="tabla-card__titulo">Registros Recientes</span>
        </div>
        <div class="tabla-scroll">
          <table class="tabla-datos">
            <thead>
              <tr>
                <th>No. Control</th>
                <th>Nombre</th>
                <th>Modalidad</th>
                <th>Fecha</th>
                <th>Cédula</th>
                <th>Registrado por</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in registrosRecientes" :key="r.ctrl" class="fila-dato">
                <td class="td-ctrl">{{ r.ctrl }}</td>
                <td class="td-nombre">{{ r.nombre }}</td>
                <td><span class="chip-estado chip-info">{{ r.modalidad }}</span></td>
                <td class="td-fecha">{{ formatearFecha(r.fecha) }}</td>
                <td class="td-cedula">{{ r.cedula || '—' }}</td>
                <td class="td-registro">{{ r.registradoPor }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ══ PAGINACIÓN ══ -->
    <div v-if="totalPaginas > 1" class="paginacion">
      <button class="pag-btn" :disabled="paginaActual === 1" @click="paginaActual--">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>
      <span class="pag-info">Página {{ paginaActual }} de {{ totalPaginas }}</span>
      <button class="pag-btn" :disabled="paginaActual === totalPaginas" @click="paginaActual++">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
      </button>
    </div>

    <!-- ══ MODAL CONFIRMACIÓN EGRESO ══ -->
    <Transition name="modal-fade">
      <div v-if="modalConfirmacion.visible" class="modal-overlay" @click.self="cerrarModal">
        <div class="modal-caja">
          <div class="modal-caja__header modal-caja__header--exito">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Confirmar Egreso
          </div>
          <div class="modal-caja__body">
            <p>¿Deseas confirmar el egreso de <strong>{{ modalConfirmacion.alumno?.nombre }}</strong>?</p>
            <p class="modal-detalle">No. Control: <strong>{{ modalConfirmacion.alumno?.ctrl }}</strong> · Carrera: <strong>{{ modalConfirmacion.alumno?.carrera }}</strong></p>
            <p class="modal-advertencia">Esta acción moverá al alumno al padrón de egresados.</p>
          </div>
          <div class="modal-caja__footer">
            <button class="btn-secondary" @click="cerrarModal">Cancelar</button>
            <button class="btn-primary" @click="procesarEgreso">Confirmar Egreso</button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ══ NOTIFICACIÓN TOAST ══ -->
    <Transition name="toast-slide">
      <div v-if="toast.visible" class="toast-notif" :class="'toast-notif--' + toast.tipo">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ toast.mensaje }}
      </div>
    </Transition>

  </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const route = useRoute()

// ── Detectar sección activa según ruta ────────────────────────────────
const seccionActiva = computed(() => {
  const p = route.path
  if (p.includes('/posibles'))  return 'posibles'
  if (p.includes('/titulados')) return 'titulados'
  if (p.includes('/registro'))  return 'registro'
  return 'lista'
})

// ── Metadatos por sección ─────────────────────────────────────────────
const secciones = {
  posibles: {
    titulo: 'Posibles Egresados',
    descripcion: 'Alumnos con 100% de créditos cubiertos pendientes de egreso formal.',
    placeholder: 'Buscar por nombre, No. Control o carrera...'
  },
  lista: {
    titulo: 'Egresados',
    descripcion: 'Padrón oficial de alumnos egresados del instituto.',
    placeholder: 'Buscar por nombre, No. Control o carrera...'
  },
  titulados: {
    titulo: 'Titulados',
    descripcion: 'Alumnos que han obtenido su título profesional.',
    placeholder: 'Buscar por nombre, No. Control, carrera o cédula...'
  },
  registro: {
    titulo: 'Registro de Titulados',
    descripcion: 'Captura y gestión de titulaciones formales.',
    placeholder: 'Buscar en registros...'
  }
}
const seccionActual = computed(() => secciones[seccionActiva.value])

// ── Filtros ───────────────────────────────────────────────────────────
const busqueda     = ref('')
const filtroCarrera = ref('')
const filtroAnio   = ref('')
const paginaActual = ref(1)

const filtrosActivos = computed(() =>
  busqueda.value !== '' || filtroCarrera.value !== '' || filtroAnio.value !== ''
)

const limpiarFiltros = () => {
  busqueda.value      = ''
  filtroCarrera.value = ''
  filtroAnio.value    = ''
  paginaActual.value  = 1
}

watch(seccionActiva, () => {
  busqueda.value = ''
  filtroCarrera.value = ''
  filtroAnio.value = ''
  paginaActual.value = 1
})

// ── Datos de muestra ──────────────────────────────────────────────────
const alumnosPosibles = ref([
  { ctrl: '20310001', nombre: 'María García López',     email: 'maria.garcia@ittepic.edu.mx',    carrera: 'Sist. Comp.',  creditos: 240, creditosTotales: 240, promedio: 9.2, situacion: { tipo: 'listo',     texto: 'Listo para egreso' } },
  { ctrl: '20310042', nombre: 'Carlos Jiménez Ruiz',   email: 'carlos.jimenez@ittepic.edu.mx',  carrera: 'Ing. Ind.',    creditos: 240, creditosTotales: 240, promedio: 8.7, situacion: { tipo: 'pendiente', texto: 'Doc. pendiente' } },
  { ctrl: '20310078', nombre: 'Ana Rodríguez Pérez',   email: 'ana.rodriguez@ittepic.edu.mx',   carrera: 'Ing. Eléct.',  creditos: 240, creditosTotales: 240, promedio: 9.5, situacion: { tipo: 'listo',     texto: 'Listo para egreso' } },
  { ctrl: '20310115', nombre: 'Luis Martínez Torres',  email: 'luis.martinez@ittepic.edu.mx',   carrera: 'Sist. Comp.',  creditos: 238, creditosTotales: 240, promedio: 7.8, situacion: { tipo: 'pendiente', texto: 'Faltan créditos' } },
  { ctrl: '20310163', nombre: 'Sofía Hernández Díaz',  email: 'sofia.hernandez@ittepic.edu.mx', carrera: 'Gest. Emp.',   creditos: 240, creditosTotales: 240, promedio: 9.8, situacion: { tipo: 'listo',     texto: 'Listo para egreso' } },
  { ctrl: '20310201', nombre: 'Pablo Flores Gómez',    email: 'pablo.flores@ittepic.edu.mx',    carrera: 'Ing. Mec.',    creditos: 240, creditosTotales: 240, promedio: 8.1, situacion: { tipo: 'revision',  texto: 'En revisión' } },
])

const egresados = ref([
  { ctrl: '19310001', nombre: 'Elena Morales Castro',  email: 'elena.morales@gmail.com',       carrera: 'Sist. Comp.',  fechaEgreso: '2024-06-15', promedio: 9.1, estadoTitulacion: { tipo: 'listo',     texto: 'Titulado' } },
  { ctrl: '19310022', nombre: 'Roberto Sánchez Luna',  email: 'roberto.sanchez@gmail.com',     carrera: 'Ing. Ind.',    fechaEgreso: '2024-06-15', promedio: 8.4, estadoTitulacion: { tipo: 'pendiente', texto: 'En proceso' } },
  { ctrl: '19310047', nombre: 'Daniela Cruz Vargas',   email: 'daniela.cruz@gmail.com',        carrera: 'Bioquímica',   fechaEgreso: '2024-06-15', promedio: 9.6, estadoTitulacion: { tipo: 'listo',     texto: 'Titulado' } },
  { ctrl: '18310011', nombre: 'Andrés López Reyes',    email: 'andres.lopez@gmail.com',        carrera: 'Ing. Eléct.',  fechaEgreso: '2023-12-10', promedio: 7.9, estadoTitulacion: { tipo: 'pendiente', texto: 'Sin trámite' } },
  { ctrl: '18310035', nombre: 'Valentina Gómez Ríos',  email: 'valentina.gomez@gmail.com',     carrera: 'Sist. Comp.',  fechaEgreso: '2023-12-10', promedio: 9.3, estadoTitulacion: { tipo: 'listo',     texto: 'Titulado' } },
  { ctrl: '18310072', nombre: 'Miguel Ángel Ramos',    email: 'miguel.ramos@gmail.com',        carrera: 'Gest. Emp.',   fechaEgreso: '2023-06-20', promedio: 8.8, estadoTitulacion: { tipo: 'revision',  texto: 'En revisión' } },
])

const titulados = ref([
  { ctrl: '19310001', nombre: 'Elena Morales Castro',   email: 'elena.morales@gmail.com',     carrera: 'Sist. Comp.', modalidad: 'Tesis',                     fechaTitulacion: '2024-10-15', cedula: '12345678' },
  { ctrl: '19310047', nombre: 'Daniela Cruz Vargas',    email: 'daniela.cruz@gmail.com',      carrera: 'Bioquímica',  modalidad: 'Residencia Profesional',    fechaTitulacion: '2024-09-22', cedula: '12345699' },
  { ctrl: '18310035', nombre: 'Valentina Gómez Ríos',   email: 'valentina.gomez@gmail.com',   carrera: 'Sist. Comp.', modalidad: 'Promedio Sobresaliente',    fechaTitulacion: '2024-05-10', cedula: '12345701' },
  { ctrl: '17310008', nombre: 'Fernanda Ibarra Moreno', email: 'fernanda.ibarra@gmail.com',   carrera: 'Ing. Ind.',   modalidad: 'Examen General de Egreso',  fechaTitulacion: '2023-11-30', cedula: '11234567' },
  { ctrl: '17310031', nombre: 'Gustavo Peña Salinas',   email: 'gustavo.pena@gmail.com',      carrera: 'Ing. Mec.',   modalidad: 'Tesis',                     fechaTitulacion: '2023-10-05', cedula: null       },
])

const registrosRecientes = ref([
  { ctrl: '18310035', nombre: 'Valentina Gómez Ríos',   modalidad: 'Promedio Sobresaliente',   fecha: '2024-05-10', cedula: '12345701', registradoPor: 'Servicios Escolares' },
  { ctrl: '19310047', nombre: 'Daniela Cruz Vargas',    modalidad: 'Residencia Profesional',   fecha: '2024-09-22', cedula: '12345699', registradoPor: 'Servicios Escolares' },
  { ctrl: '19310001', nombre: 'Elena Morales Castro',   modalidad: 'Tesis',                    fecha: '2024-10-15', cedula: '12345678', registradoPor: 'Admin' },
])

// ── Filtros aplicados ─────────────────────────────────────────────────
const alumnosFiltrados = computed(() =>
  alumnosPosibles.value.filter(a =>
    (!busqueda.value || a.nombre.toLowerCase().includes(busqueda.value.toLowerCase()) || a.ctrl.includes(busqueda.value)) &&
    (!filtroCarrera.value || a.carrera.toLowerCase().includes(filtroCarrera.value.toLowerCase()))
  )
)

const egresadosFiltrados = computed(() =>
  egresados.value.filter(e =>
    (!busqueda.value || e.nombre.toLowerCase().includes(busqueda.value.toLowerCase()) || e.ctrl.includes(busqueda.value)) &&
    (!filtroCarrera.value || e.carrera.toLowerCase().includes(filtroCarrera.value.toLowerCase())) &&
    (!filtroAnio.value || e.fechaEgreso.startsWith(filtroAnio.value))
  )
)

const tituladosFiltrados = computed(() =>
  titulados.value.filter(t =>
    (!busqueda.value || t.nombre.toLowerCase().includes(busqueda.value.toLowerCase()) || t.ctrl.includes(busqueda.value)) &&
    (!filtroCarrera.value || t.carrera.toLowerCase().includes(filtroCarrera.value.toLowerCase())) &&
    (!filtroAnio.value || t.fechaTitulacion.startsWith(filtroAnio.value))
  )
)

const totalPaginas = computed(() => {
  const total = seccionActiva.value === 'posibles' ? alumnosFiltrados.value.length
    : seccionActiva.value === 'lista' ? egresadosFiltrados.value.length
    : tituladosFiltrados.value.length
  return Math.max(1, Math.ceil(total / 10))
})

// ── KPIs ──────────────────────────────────────────────────────────────
const kpisEgresados = [
  { label: 'Total Egresados',  valor: 248, color: '#1D52B7', bgColor: 'rgba(29,82,183,0.10)', icono: 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z' },
  { label: 'Titulados',        valor: 187, color: '#27AE60', bgColor: 'rgba(39,174,96,0.10)',  icono: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
  { label: 'Sin Titulación',   valor: 61,  color: '#F2994A', bgColor: 'rgba(242,153,74,0.10)', icono: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
  { label: '% Titulación',     valor: '75.4%', color: '#132B4F', bgColor: 'rgba(19,43,79,0.10)', icono: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
]
const kpisTitulados = [
  { label: 'Total Titulados',  valor: 187, color: '#27AE60', bgColor: 'rgba(39,174,96,0.10)',  icono: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
  { label: 'Tesis',            valor: 89,  color: '#1D52B7', bgColor: 'rgba(29,82,183,0.10)', icono: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
  { label: 'Residencia Prof.', valor: 62,  color: '#132B4F', bgColor: 'rgba(19,43,79,0.10)', icono: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
  { label: 'Con Cédula',       valor: 165, color: '#F2994A', bgColor: 'rgba(242,153,74,0.10)', icono: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
]
const kpisActuales = computed(() =>
  seccionActiva.value === 'titulados' ? kpisTitulados : kpisEgresados
)

// ── Formulario de registro ────────────────────────────────────────────
const formRegistro = ref({ ctrl: '', nombre: '', modalidad: '', fecha: '', cedula: '', observaciones: '' })
const formValido = computed(() =>
  formRegistro.value.ctrl && formRegistro.value.modalidad && formRegistro.value.fecha
)
const buscarEgresado = () => {
  const found = egresados.value.find(e => e.ctrl === formRegistro.value.ctrl)
  formRegistro.value.nombre = found ? found.nombre : ''
}
const limpiarFormulario = () => {
  formRegistro.value = { ctrl: '', nombre: '', modalidad: '', fecha: '', cedula: '', observaciones: '' }
}
const guardarRegistro = () => {
  mostrarToast('Titulación registrada correctamente.', 'exito')
  registrosRecientes.value.unshift({
    ctrl: formRegistro.value.ctrl,
    nombre: formRegistro.value.nombre || 'Desconocido',
    modalidad: formRegistro.value.modalidad,
    fecha: formRegistro.value.fecha,
    cedula: formRegistro.value.cedula,
    registradoPor: 'Servicios Escolares'
  })
  limpiarFormulario()
}
const abrirModalRegistro = () => {}

// ── Modal confirmación ────────────────────────────────────────────────
const modalConfirmacion = ref({ visible: false, alumno: null })
const confirmarEgreso = (alumno) => { modalConfirmacion.value = { visible: true, alumno } }
const cerrarModal = () => { modalConfirmacion.value.visible = false }
const procesarEgreso = () => {
  mostrarToast(`Egreso de ${modalConfirmacion.value.alumno?.nombre} registrado.`, 'exito')
  alumnosPosibles.value = alumnosPosibles.value.filter(a => a.ctrl !== modalConfirmacion.value.alumno?.ctrl)
  cerrarModal()
}

// ── Ordenamiento ──────────────────────────────────────────────────────
const columnaOrden = ref('')
const ordenarPor = (col) => { columnaOrden.value = columnaOrden.value === col ? '' : col }

// ── Acciones ──────────────────────────────────────────────────────────
const verExpediente  = (al) => mostrarToast(`Abriendo expediente de ${al.nombre}...`, 'info')
const generarConstancia = (al) => mostrarToast(`Generando constancia de ${al.nombre}...`, 'info')
const verTitulo      = (t)  => mostrarToast(`Cargando título de ${t.nombre}...`, 'info')
const exportarDatos  = ()   => mostrarToast('Exportando datos...', 'info')

// ── Toast ─────────────────────────────────────────────────────────────
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let toastTimer = null
const mostrarToast = (mensaje, tipo = 'exito') => {
  if (toastTimer) clearTimeout(toastTimer)
  toast.value = { visible: true, mensaje, tipo }
  toastTimer = setTimeout(() => { toast.value.visible = false }, 3200)
}

// ── Utilidades ────────────────────────────────────────────────────────
const formatearFecha = (fechaStr) => {
  if (!fechaStr) return '—'
  const [y, m, d] = fechaStr.split('-')
  const meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic']
  return `${d} ${meses[parseInt(m)-1]} ${y}`
}

const COLORES_AVATAR = ['#1D52B7','#27AE60','#F2994A','#132B4F','#2F80ED','#0B2545','#1A4184']
const generarColorAvatar = (nombre) => COLORES_AVATAR[nombre.charCodeAt(0) % COLORES_AVATAR.length]
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* ══ VARIABLES ══ */
.egresados-se {
  --azul-marino:    #0B2545;
  --azul-rey:       #1D52B7;
  --azul-medio:     #1A4184;
  --azul-cyan:      #2F80ED;
  --azul-datos:     #132B4F;
  --verde:          #27AE60;
  --rojo:           #EB5757;
  --naranja:        #F2994A;
  --fondo-app:      #F4F6F9;
  --fondo-sec:      #F2F4F7;
  --blanco:         #FFFFFF;
  --borde:          #E0E0E0;
  --texto-1:        #333333;
  --texto-2:        #4F4F4F;
  --texto-3:        #828282;
  --texto-4:        #BDBDBD;

  font-family: 'Montserrat', sans-serif;
  padding: 1.5rem 2rem 3rem;
  background: var(--fondo-app);
  min-height: 100%;
  text-transform: uppercase;
}

/* ══ ENCABEZADO DEL MÓDULO ══ */
.modulo-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  gap: 1rem;
  flex-wrap: wrap;
}
.modulo-header__izq {
  display: flex;
  align-items: center;
  gap: 1rem;
}
.modulo-icono {
  width: 48px; height: 48px; border-radius: 14px;
  background: var(--azul-marino);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(11,37,69,0.22);
}
.modulo-icono svg { width: 26px; height: 26px; stroke: #fff; }
.modulo-titulo {
  font-size: 1.35rem; font-weight: 700;
  color: var(--texto-1); margin: 0 0 3px;
   font-family: 'Montserrat', sans-serif;
}
.modulo-subtitulo {
  font-size: 0.82rem; color: var(--texto-2);
  margin: 0; font-weight: 400;
}
.modulo-header__der { display: flex; gap: 0.75rem; flex-wrap: wrap; }

/* ══ BOTONES ══ */
.btn-primary {
  display: inline-flex; align-items: center; gap: 7px;
  background: var(--azul-rey); color: #fff;
  border: none; border-radius: 9px; cursor: pointer;
  padding: 9px 18px; font-size: 0.83rem; font-weight: 600;
  font-family: 'Montserrat', sans-serif;
  transition: background 0.18s, box-shadow 0.18s, transform 0.12s;
  box-shadow: 0 3px 10px rgba(29,82,183,0.28);
  text-transform: uppercase;
  letter-spacing: .04em;
}
.btn-primary:hover { background: var(--azul-marino); box-shadow: 0 5px 16px rgba(11,37,69,0.35); }
.btn-primary:active { transform: scale(0.97); }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-secondary {
  display: inline-flex; align-items: center; gap: 7px;
  background: var(--blanco); color: var(--texto-2);
  border: 1.5px solid var(--borde); border-radius: 9px; cursor: pointer;
  padding: 9px 16px; font-size: 0.83rem; font-weight: 500;
  font-family: 'Montserrat', sans-serif;
  text-transform: uppercase;
  letter-spacing: .04em;
  
}
.btn-secondary:hover { background: var(--fondo-sec); border-color: var(--azul-rey); color: var(--azul-rey); }

/* ══ FILTROS BAR ══ */
.filtros-bar {
  display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;
  background: var(--blanco); border-radius: 12px;
  padding: 10px 14px; margin-bottom: 1.25rem;
  border: 1px solid var(--borde);
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.filtros-bar__busqueda {
  position: relative; flex: 1; min-width: 200px;
}
.input-busqueda {
  width: 100%; padding: 8px 34px 8px 12px;
  border: 1.5px solid var(--borde); border-radius: 8px;
  font-size: 0.83rem; font-family: 'Montserrat', sans-serif;
  color: var(--texto-1); outline: none;
  transition: border-color 0.18s, box-shadow 0.18s;
  box-sizing: border-box;
  text-transform: none;
}
.input-busqueda:focus { border-color: var(--azul-rey); box-shadow: 0 0 0 3px rgba(29,82,183,0.12); }
.input-busqueda::placeholder { color: var(--texto-4); text-transform: none; font-family: 'Montserrat', sans-serif; }
.btn-limpiar-busqueda {
  position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer; padding: 2px;
  color: var(--texto-3); display: flex; align-items: center;
}
.btn-limpiar-busqueda:hover { color: var(--rojo); }
.filtros-bar__controles { display: flex; gap: 0.6rem; flex-wrap: wrap; }
.select-filtro {
  padding: 7px 28px 7px 10px; border: 1.5px solid var(--borde); border-radius: 8px;
  font-size: 0.8rem; font-family: 'Montserrat', sans-serif; color: var(--texto-2);
  background: var(--blanco); outline: none; cursor: pointer;
  transition: border-color 0.18s;
  appearance: none;
  text-transform: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23828282'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 7px center; background-size: 14px;
}
.select-filtro:focus { border-color: var(--azul-rey); }
.btn-filtro-activo {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 7px 13px; border-radius: 8px; cursor: pointer;
  font-size: 0.78rem; font-weight: 500; font-family: 'Montserrat', sans-serif;
  border: 1.5px solid var(--borde); background: var(--blanco); color: var(--texto-3);
  transition: all 0.18s;
  text-transform: uppercase;
  letter-spacing: .04em;
}
.btn-filtro-activo:hover { border-color: var(--azul-rey); color: var(--azul-rey); }
.btn-filtro-activo--on { background: rgba(235,87,87,0.07); border-color: var(--rojo); color: var(--rojo); }

/* ══ KPIs ══ */
.kpi-row {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;
  margin-bottom: 1.25rem;
}
.kpi-card {
  background: var(--blanco); border-radius: 12px; padding: 16px 18px;
  border: 1px solid var(--borde);
  display: flex; align-items: center; gap: 14px;
  box-shadow: 0 1px 5px rgba(0,0,0,0.05);
  transition: box-shadow 0.18s, transform 0.18s;
}
.kpi-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.09); transform: translateY(-1px); }
.kpi-card__icono {
  width: 44px; height: 44px; border-radius: 11px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
}
.kpi-card__icono svg { width: 22px; height: 22px; }
.kpi-card__datos { display: flex; flex-direction: column; min-width: 0; }
.kpi-card__numero { font-size: 2rem; font-weight: 700; line-height: 1.1; }
.kpi-card__label  { font-size: 0.73rem; color: var(--texto-3); margin-top: 2px; font-weight: 400; }

/* ══ ALERTA INFO ══ */
.alerta-info {
  display: flex; align-items: flex-start; gap: 10px;
  background: rgba(29,82,183,0.07); border: 1px solid rgba(29,82,183,0.18);
  border-radius: 10px; padding: 11px 15px; margin-bottom: 1.25rem;
  font-size: 0.82rem; color: var(--azul-datos); font-weight: 400; line-height: 1.55;
}
.alerta-info svg { flex-shrink: 0; stroke: var(--azul-rey); margin-top: 1px; }
.alerta-info--advertencia {
  background: rgba(242,153,74,0.09); border-color: rgba(242,153,74,0.3);
  color: #7a4a0a;
}
.alerta-info--advertencia svg { stroke: var(--naranja); }

/* ══ TABLA CARD ══ */
.tabla-card {
  background: var(--blanco); border-radius: 14px;
  border: 1px solid var(--borde);
  box-shadow: 0 1px 6px rgba(0,0,0,0.05);
  overflow: hidden;
}
.tabla-card__header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 18px; border-bottom: 1px solid var(--borde);
}
.tabla-card__titulo { font-size: 0.92rem; font-weight: 600; color: var(--texto-1); }
.tabla-card__conteo { font-size: 0.78rem; color: var(--texto-3); background: var(--fondo-sec); padding: 3px 10px; border-radius: 999px; }
.tabla-scroll { overflow-x: auto; }
.tabla-datos {
  width: 100%; border-collapse: collapse;
  font-size: 0.82rem;
}
.tabla-datos thead tr { background: var(--fondo-sec); }
.tabla-datos th {
  padding: 10px 14px; text-align: left;
  font-size: 0.73rem; font-weight: 600;
  color: var(--texto-3); text-transform: uppercase; letter-spacing: 0.04em;
  white-space: nowrap;
}
.th-ordenable { cursor: pointer; user-select: none; }
.th-ordenable:hover { color: var(--azul-rey); }
.th-ordenable { display: flex; align-items: center; gap: 4px; }
.fila-dato { border-bottom: 1px solid var(--fondo-sec); transition: background 0.13s; }
.fila-dato:hover { background: rgba(29,82,183,0.04); }
.fila-dato td { padding: 11px 14px; vertical-align: middle; color: var(--texto-2); }
.tabla-vacia {
  text-align: center; padding: 50px 20px;
  color: var(--texto-4);
}
.tabla-vacia svg { stroke: var(--texto-4); margin-bottom: 10px; }
.tabla-vacia p { font-size: 0.84rem; margin: 0; }

/* ══ CELDAS ESPECIALES ══ */
.td-ctrl { font-family: monospace; font-size: 0.8rem; color: var(--texto-3); font-weight: 600; white-space: nowrap; text-transform: none; }
.td-fecha { white-space: nowrap; color: var(--texto-2); text-transform: none; }
.td-cedula { font-family: monospace; font-size: 0.8rem; text-transform: none; }
.td-registro { font-size: 0.78rem; color: var(--texto-3); }

.avatar-nombre { display: flex; align-items: center; gap: 10px; }
.avatar-circulo {
  width: 33px; height: 33px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-weight: 700; font-size: 0.88rem;
}
.nombre-principal { display: block; font-weight: 600; color: var(--texto-1); font-size: 0.83rem; }
.nombre-email { display: block; font-size: 0.72rem; color: var(--texto-3); text-transform: none; }

.badge-carrera {
  display: inline-block; padding: 3px 9px;
  background: rgba(19,43,79,0.09); color: var(--azul-datos);
  border-radius: 6px; font-size: 0.73rem; font-weight: 600; white-space: nowrap;
}

.creditos-barra { position: relative; width: 100px; }
.creditos-barra > div {
  position: absolute; top: 50%; transform: translateY(-50%);
  height: 6px; border-radius: 3px;
  background: var(--verde); min-width: 4px; max-width: 100%;
  z-index: 0;
  transition: width 0.5s;
}
.creditos-barra {
  background: rgba(39,174,96,0.12); height: 6px; border-radius: 3px;
  overflow: hidden;
}
.creditos-texto {
  display: block; text-align: center;
  font-size: 0.72rem; color: var(--texto-2); margin-top: 7px; white-space: nowrap;
}

.promedio-num { font-weight: 700; font-size: 0.9rem; }
.promedio-alto   { color: var(--verde); }
.promedio-medio  { color: var(--naranja); }
.promedio-bajo   { color: var(--rojo); }

.cedula-num { font-family: monospace; font-weight: 600; color: var(--texto-1); text-transform: none; }
.cedula-pendiente { font-size: 0.75rem; color: var(--texto-4); font-style: italic; text-transform: none; }

/* ══ CHIPS DE ESTADO ══ */
.chip-estado {
  display: inline-block; padding: 3px 9px; border-radius: 6px;
  font-size: 0.72rem; font-weight: 600; white-space: nowrap;
}
.chip-listo    { background: rgba(39,174,96,0.12);  color: #1a7a43; }
.chip-pendiente { background: rgba(242,153,74,0.14); color: #8a4d00; }
.chip-revision  { background: rgba(47,128,237,0.12); color: var(--azul-rey); }
.chip-error     { background: rgba(235,87,87,0.12); color: #b33; }
.chip-info      { background: rgba(19,43,79,0.09);  color: var(--azul-datos); }

/* ══ BOTONES DE ACCIÓN ══ */
.acciones-fila { display: flex; gap: 5px; }
.btn-accion {
  width: 30px; height: 30px; border-radius: 7px; border: 1.5px solid;
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  transition: background 0.14s, border-color 0.14s, transform 0.1s;
  background: transparent;
}
.btn-accion:hover { transform: scale(1.08); }
.btn-accion--ver    { border-color: var(--azul-cyan); color: var(--azul-cyan); }
.btn-accion--ver:hover { background: rgba(47,128,237,0.1); }
.btn-accion--aprobar { border-color: var(--verde); color: var(--verde); }
.btn-accion--aprobar:hover { background: rgba(39,174,96,0.1); }
.btn-accion--doc    { border-color: var(--azul-datos); color: var(--azul-datos); }
.btn-accion--doc:hover { background: rgba(19,43,79,0.08); }

/* ══ FORMULARIO REGISTRO ══ */
.registro-form-card {
  background: var(--blanco); border-radius: 14px;
  border: 1px solid var(--borde);
  box-shadow: 0 1px 6px rgba(0,0,0,0.05);
  padding: 20px 22px;
}
.registro-form-card__titulo {
  display: flex; align-items: center; gap: 8px;
  font-size: 0.92rem; font-weight: 600; color: var(--texto-1);
  margin-bottom: 1.2rem; padding-bottom: 12px;
  border-bottom: 1px solid var(--borde);
}
.registro-form-card__titulo svg { stroke: var(--azul-rey); }

.form-grid {
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem 1.25rem;
  margin-bottom: 1.25rem;
}
.form-grupo { display: flex; flex-direction: column; gap: 5px; }
.form-label { font-size: 0.78rem; font-weight: 600; color: var(--texto-2); font-weight: 600; }
.req { color: var(--rojo); margin-left: 2px; }
.form-input {
  padding: 9px 12px; border: 1.5px solid var(--borde); border-radius: 8px;
  font-size: 0.83rem; font-family: 'Montserrat', sans-serif;
  color: var(--texto-1); outline: none;
  transition: border-color 0.18s, box-shadow 0.18s;
  text-transform: none;
}
.form-input:focus { border-color: var(--azul-rey); box-shadow: 0 0 0 3px rgba(29,82,183,0.12); }
.form-input::placeholder { color: var(--texto-4); text-transform: none; font-family: 'Montserrat', sans-serif; }
.form-input[readonly] { background: var(--fondo-sec); color: var(--texto-3); }

.input-con-icono { position: relative; }
.input-con-icono svg {
  position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
  stroke: var(--texto-3); pointer-events: none;
}
.input-con-icono .form-input { padding-left: 34px; }

.form-acciones { display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 6px; }

/* ══ PAGINACIÓN ══ */
.paginacion {
  display: flex; align-items: center; justify-content: center; gap: 12px;
  margin-top: 1.25rem;
}
.pag-btn {
  width: 34px; height: 34px; border-radius: 8px;
  border: 1.5px solid var(--borde); background: var(--blanco); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  color: var(--texto-2); transition: all 0.16s;
}
.pag-btn:hover:not(:disabled) { border-color: var(--azul-rey); color: var(--azul-rey); background: rgba(29,82,183,0.07); }
.pag-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.pag-info { font-size: 0.82rem; color: var(--texto-3); font-weight: 500; }

/* ══ MODAL ══ */
.modal-overlay {
  position: fixed; inset: 0; z-index: 2000;
  background: rgba(11,37,69,0.48); backdrop-filter: blur(3px);
  display: flex; align-items: center; justify-content: center; padding: 20px;
}
.modal-caja {
  background: var(--blanco); border-radius: 16px; width: 100%; max-width: 440px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.2); overflow: hidden;
}
.modal-caja__header {
  display: flex; align-items: center; gap: 10px;
  padding: 16px 20px; font-weight: 700; font-size: 0.95rem;
}
.modal-caja__header--exito { background: rgba(39,174,96,0.1); color: #1a7a43; }
.modal-caja__header svg { flex-shrink: 0; stroke: var(--verde); }
.modal-caja__body { padding: 20px; font-size: 0.85rem; color: var(--texto-2); line-height: 1.6; }
.modal-caja__body p { margin: 0 0 8px; }
.modal-detalle { color: var(--texto-3); font-size: 0.8rem; }
.modal-advertencia { color: var(--naranja); font-size: 0.79rem; font-weight: 500; margin-top: 8px !important; }
.modal-caja__footer {
  display: flex; justify-content: flex-end; gap: 0.7rem;
  padding: 14px 20px; border-top: 1px solid var(--borde);
}
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s, transform 0.2s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; transform: scale(0.95); }

/* ══ TOAST ══ */
.toast-notif {
  position: fixed; bottom: 1.6rem; right: 1.6rem; z-index: 3000;
  display: flex; align-items: center; gap: 9px;
  padding: 11px 18px; border-radius: 10px;
  font-size: 0.83rem; font-weight: 600; font-family: 'Montserrat', sans-serif;
  box-shadow: 0 6px 20px rgba(0,0,0,0.16);
  pointer-events: none;
}
.toast-notif--exito { background: var(--verde); color: #fff; }
.toast-notif--info  { background: var(--azul-rey); color: #fff; }
.toast-notif--error { background: var(--rojo); color: #fff; }
.toast-notif svg { flex-shrink: 0; stroke: #fff; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: opacity 0.22s, transform 0.22s; }
.toast-slide-enter-from, .toast-slide-leave-to { opacity: 0; transform: translateY(14px); }

/* ══ CONTENIDO ══ */
.contenido-seccion { display: flex; flex-direction: column; }

/* ══ RESPONSIVE ══ */
@media (max-width: 1100px) {
  .kpi-row { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .egresados-se { padding: 1rem; }
  .modulo-header { flex-direction: column; align-items: flex-start; }
  .kpi-row { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
  .form-grid { grid-template-columns: 1fr; }
  .filtros-bar { flex-direction: column; align-items: stretch; }
  .filtros-bar__busqueda { min-width: 0; }
  .filtros-bar__controles { flex-wrap: wrap; }
  .tabla-datos th, .tabla-datos td { padding: 8px 10px; }
}
@media (max-width: 480px) {
  .kpi-row { grid-template-columns: 1fr; }
  .modulo-header__der { width: 100%; }
  .btn-primary, .btn-secondary { flex: 1; justify-content: center; }
}
</style>