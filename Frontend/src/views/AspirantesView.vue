<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="aspirantes-page">

      <!-- Breadcrumb -->
      <nav class="breadcrumb">
        <router-link to="/inicio" class="breadcrumb-link">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          Inicio
        </router-link>
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" class="sep-icon"><path d="m9 18 6-6-6-6"/></svg>
        <span class="breadcrumb-actual">ASPIRANTES</span>
      </nav>

      <!-- Encabezado -->
      <div class="page-header">
        <div class="header-left">
          <div>
            <h1 class="page-title">ASPIRANTES</h1>
            <p class="page-subtitle"> GESTIÓN DE ASPIRANTES DE NUEVO INGRESO</p>
          </div>
        </div>
        <button class="btn-primary" @click="goTab('aspirantes'); subAsp='nuevo'; limpiarForm()">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
          Nuevo Aspirante
        </button>
      </div>

      <!-- KPIs -->
      <div class="kpi-row">
        <div class="kpi-card">
          <div class="kpi-icon" style="background:rgba(29,82,183,0.10)">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#1D52B7" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
          <div><span class="kpi-num">{{ aspirantes.length }}</span><span class="kpi-label">TOTAL ASPIRANTES</span></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon" style="background:rgba(39,174,96,0.10)">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#27AE60" stroke-width="1.8"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          </div>
          <div><span class="kpi-num" style="color:#27AE60">{{ aspirantes.filter(a=>a.estatus==='ACEPTADO').length }}</span><span class="kpi-label">ACEPTADOS</span></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon" style="background:rgba(242,153,74,0.10)">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#F2994A" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
          </div>
          <div><span class="kpi-num" style="color:#F2994A">{{ aspirantes.filter(a=>a.estatus==='EN REVISIÓN').length }}</span><span class="kpi-label">EN REVISIÓN</span></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon" style="background:rgba(29,82,183,0.08)">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#1D52B7" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          </div>
          <div><span class="kpi-num">{{ fichas.length }}</span><span class="kpi-label">FICHAS GENERADAS</span></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-icon" style="background:rgba(235,87,87,0.10)">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#EB5757" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
          </div>
          <div><span class="kpi-num" style="color:#EB5757">{{ solicitudes.filter(s=>s.estatus==='REGISTRADA'||s.estatus==='EN REVISIÓN').length }}</span><span class="kpi-label">SOL. PENDIENTES</span></div>
        </div>
      </div>

      <!-- TABS PRINCIPALES -->
      <div class="tabs-wrap">
        <button v-for="t in tabs" :key="t.id" class="tab-btn" :class="{active: tabActivo===t.id}" @click="goTab(t.id)">
          <component :is="'span'" class="tab-icon" v-html="t.icon"></component>
          {{ t.label }}
        </button>
      </div>

      <!-- =========================================================
           8.1 CONFIGURACIÓN INICIAL
      ========================================================= -->
      <div v-show="tabActivo==='config'">
        <div class="subtabs-wrap">
          <button class="subtab-btn" :class="{active: subConfig==='info'}" @click="subConfig='info'">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z"/><path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9"/><path d="M12 3v6"/></svg>
            Información General
          </button>
          <button class="subtab-btn" :class="{active: subConfig==='fechas'}" @click="subConfig='fechas'">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
            Fechas
          </button>
        </div>

        <div v-show="subConfig==='info'" class="card">
          <div class="card-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            Información General del Proceso de Admisión
          </div>
          <div class="card-body">
            <div class="form-grid-2">
              <div class="form-group"><label>NOMBRE DEL PROCESO</label><input v-model="config.nombreProceso" class="form-input" placeholder="EJ: ADMISIÓN 2026-2"/></div>
              <div class="form-group"><label>CICLO ESCOLAR</label><input v-model="config.ciclo" class="form-input" placeholder="2026-2"/></div>
              <div class="form-group"><label>INSTITUCIÓN</label><input v-model="config.institucion" class="form-input"/></div>
              <div class="form-group"><label>CORREO DE CONTACTO</label><input v-model="config.correo" type="email" class="form-input" placeholder="ADMISIONES@SLP.TECNM.MX"/></div>
              <div class="form-group"><label>TELÉFONO DE CONTACTO</label><input v-model="config.telefono" type="tel" class="form-input" placeholder="444 000 0000"/></div>
              <div class="form-group"><label>COSTO DE FICHA</label><input v-model="config.costo" class="form-input" placeholder="$250.00 MXN"/></div>
              <div class="form-group full-width"><label>MENSAJE INFORMATIVO</label>
                <textarea v-model="config.mensaje" class="form-input" rows="3" placeholder="MENSAJE PARA LOS ASPIRANTES..."></textarea>
              </div>
            </div>
            <div class="form-actions">
              <button class="btn-secondary" @click="resetConfig">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                Cancelar
              </button>
              <button class="btn-primary" @click="guardarConfig">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Guardar Cambios
              </button>
            </div>
          </div>
        </div>

        <div v-show="subConfig==='fechas'" class="card">
          <div class="card-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
            Fechas Importantes del Proceso
          </div>
          <div class="card-body">
            <div class="form-grid-2">
              <div class="form-group"><label>INICIO DE REGISTRO</label><input v-model="fechas.inicioReg" type="date" class="form-input"/></div>
              <div class="form-group"><label>FIN DE REGISTRO</label><input v-model="fechas.finReg" type="date" class="form-input"/></div>
              <div class="form-group"><label>INICIO ENTREGA DE FICHAS</label><input v-model="fechas.inicioFicha" type="date" class="form-input"/></div>
              <div class="form-group"><label>FIN ENTREGA DE FICHAS</label><input v-model="fechas.finFicha" type="date" class="form-input"/></div>
              <div class="form-group"><label>FECHA DE EXAMEN</label><input v-model="fechas.examen" type="date" class="form-input"/></div>
              <div class="form-group"><label>FECHA DE RESULTADOS</label><input v-model="fechas.resultados" type="date" class="form-input"/></div>
            </div>
            <div class="form-actions">
              <button class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                Cancelar
              </button>
              <button class="btn-primary" @click="guardarFechas">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Guardar Fechas
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- =========================================================
           8.2 ASPIRANTES
      ========================================================= -->
      <div v-show="tabActivo==='aspirantes'">
        <div class="subtabs-wrap">
          <button class="subtab-btn" :class="{active: subAsp==='consultar'}" @click="subAsp='consultar'">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            Consultar
          </button>
          <button class="subtab-btn" :class="{active: subAsp==='nuevo'}" @click="subAsp='nuevo'; limpiarForm()">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Nuevo Aspirante
          </button>
        </div>

        <!-- CONSULTAR -->
        <div v-show="subAsp==='consultar'">
          <div class="filters-bar">
            <div class="search-wrap">
              <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
              <input v-model="busquedaAsp" class="search-input" placeholder="BUSCAR POR NOMBRE, CURP O FOLIO..."/>
            </div>
            <select v-model="filtroCarrera" class="filter-select">
              <option value="">TODAS LAS CARRERAS</option>
              <option v-for="c in carreras" :key="c" :value="c">{{ c }}</option>
            </select>
            <select v-model="filtroEstatus" class="filter-select">
              <option value="">TODOS LOS ESTATUS</option>
              <option>REGISTRADO</option><option>EN REVISIÓN</option>
              <option>ACEPTADO</option><option>RECHAZADO</option>
            </select>
            <button class="btn-secondary-sm" @click="limpiarFiltros">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
              Limpiar
            </button>
          </div>

          <div class="table-card">
            <table class="data-table">
              <thead>
                <tr>
                  <th>FOLIO</th><th>NOMBRE</th><th>CURP</th><th>CARRERA</th><th>ESTATUS</th><th>ACCIONES</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="a in aspirantesFiltrados" :key="a.folio">
                  <td><span class="badge-folio">{{ a.folio }}</span></td>
                  <td class="td-bold">{{ a.nombre }}</td>
                  <td class="td-mono">{{ a.curp }}</td>
                  <td>{{ a.carrera }}</td>
                  <td><span class="badge-status" :class="clsBadge(a.estatus)">{{ a.estatus }}</span></td>
                  <td>
                    <div class="action-btns">
                      <button class="action-btn btn-ver" title="Ver detalle" @click="verDetalle(a)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                      </button>
                      <button class="action-btn btn-edit" title="Editar" @click="editarAspirante(a)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="aspirantesFiltrados.length===0">
                  <td colspan="6" class="td-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="color:#BDBDBD;display:block;margin:0 auto 8px"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    No se encontraron aspirantes con los filtros aplicados.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="pagination">
            <span class="pag-info">Mostrando {{ aspirantesFiltrados.length }} de {{ aspirantes.length }} registros</span>
            <div class="pag-btns">
              <button class="pag-btn active">1</button>
              <button class="pag-btn">›</button>
            </div>
          </div>
        </div>

        <!-- NUEVO / EDITAR -->
        <div v-show="subAsp==='nuevo'">
          <div class="card">
            <div class="card-header">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              Datos Personales
            </div>
            <div class="card-body">
              <div class="form-grid-3">
                <div class="form-group"><label>NOMBRE(S) *</label><input v-model="form.nombre" class="form-input" placeholder="NOMBRE(S)"/></div>
                <div class="form-group"><label>APELLIDO PATERNO *</label><input v-model="form.apellidoP" class="form-input" placeholder="APELLIDO PATERNO"/></div>
                <div class="form-group"><label>APELLIDO MATERNO</label><input v-model="form.apellidoM" class="form-input" placeholder="APELLIDO MATERNO"/></div>
                <div class="form-group"><label>CURP *</label><input v-model="form.curp" class="form-input" placeholder="18 CARACTERES" maxlength="18" style="text-transform:uppercase"/></div>
                <div class="form-group"><label>FECHA DE NACIMIENTO</label><input v-model="form.fechaNac" type="date" class="form-input"/></div>
                <div class="form-group"><label>SEXO</label>
                  <select v-model="form.sexo" class="form-input"><option value="">SELECCIONAR...</option><option>MASCULINO</option><option>FEMENINO</option><option>OTRO</option></select>
                </div>
                <div class="form-group"><label>ESTADO CIVIL</label>
                  <select v-model="form.estadoCivil" class="form-input"><option value="">SELECCIONAR...</option><option>SOLTERO/A</option><option>CASADO/A</option><option>OTRO</option></select>
                </div>
                <div class="form-group"><label>CORREO ELECTRÓNICO *</label><input v-model="form.correo" type="email" class="form-input" placeholder="CORREO@EJEMPLO.COM"/></div>
                <div class="form-group"><label>TELÉFONO</label><input v-model="form.telefono" type="tel" class="form-input" placeholder="10 DÍGITOS"/></div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
              Domicilio
            </div>
            <div class="card-body">
              <div class="form-grid-3">
                <div class="form-group"><label>CALLE</label><input v-model="form.calle" class="form-input" placeholder="NOMBRE DE LA CALLE"/></div>
                <div class="form-group"><label>NÚMERO</label><input v-model="form.numero" class="form-input" placeholder="EXT. / INT."/></div>
                <div class="form-group"><label>COLONIA</label><input v-model="form.colonia" class="form-input" placeholder="COLONIA"/></div>
                <div class="form-group"><label>MUNICIPIO</label><input v-model="form.municipio" class="form-input" placeholder="MUNICIPIO"/></div>
                <div class="form-group"><label>ESTADO</label>
                  <select v-model="form.estado" class="form-input"><option value="">SELECCIONAR...</option><option>AGUASCALIENTES</option><option>BAJA CALIFORNIA</option><option>CDMX</option><option>JALISCO</option><option>NUEVO LEÓN</option><option>SAN LUIS POTOSÍ</option><option>SONORA</option><option>VERACRUZ</option></select>
                </div>
                <div class="form-group"><label>CÓDIGO POSTAL</label><input v-model="form.cp" class="form-input" placeholder="CP" maxlength="5"/></div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
              Datos Académicos
            </div>
            <div class="card-body">
              <div class="form-grid-3">
                <div class="form-group"><label>ESCUELA DE PROCEDENCIA *</label><input v-model="form.escuela" class="form-input" placeholder="NOMBRE DEL PLANTEL"/></div>
                <div class="form-group"><label>PROMEDIO</label><input v-model="form.promedio" type="number" min="0" max="10" step="0.1" class="form-input" placeholder="0.0 – 10.0"/></div>
                <div class="form-group"><label>CARRERA SOLICITADA *</label>
                  <select v-model="form.carrera" class="form-input"><option value="">SELECCIONAR...</option><option v-for="c in carreras" :key="c" :value="c">{{ c }}</option></select>
                </div>
              </div>
              <div v-if="modoEdicion" class="form-group" style="margin-top:14px;max-width:220px;">
                <label>ESTATUS</label>
                <select v-model="form.estatus" class="form-input"><option>REGISTRADO</option><option>EN REVISIÓN</option><option>ACEPTADO</option><option>RECHAZADO</option></select>
              </div>
              <div class="form-actions">
                <button class="btn-secondary" @click="subAsp='consultar'">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                  Cancelar
                </button>
                <button class="btn-primary" @click="guardarAspirante">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                  {{ modoEdicion ? 'Actualizar Aspirante' : 'Guardar Aspirante' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- =========================================================
           8.3 SOLICITUDES
      ========================================================= -->
      <div v-show="tabActivo==='solicitudes'">
        <div class="filters-bar">
          <div class="search-wrap">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input v-model="busquedaSol" class="search-input" placeholder="BUSCAR SOLICITUD..."/>
          </div>
          <select v-model="filtroEstSol" class="filter-select">
            <option value="">TODOS LOS ESTADOS</option>
            <option>REGISTRADA</option><option>EN REVISIÓN</option><option>APROBADA</option><option>RECHAZADA</option>
          </select>
          <button class="btn-secondary-sm" @click="busquedaSol=''; filtroEstSol=''">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            Limpiar
          </button>
        </div>
        <div class="table-card">
          <table class="data-table">
            <thead><tr><th>FOLIO</th><th>ASPIRANTE</th><th>CARRERA</th><th>FECHA</th><th>ESTATUS</th><th>ACCIONES</th></tr></thead>
            <tbody>
              <tr v-for="s in solicitudesFiltradas" :key="s.id">
                <td><span class="badge-folio">#{{ String(s.id).padStart(4,'0') }}</span></td>
                <td class="td-bold">{{ s.aspirante }}</td>
                <td>{{ s.carrera }}</td>
                <td>{{ s.fecha }}</td>
                <td><span class="badge-status" :class="clsBadgeSol(s.estatus)">{{ s.estatus }}</span></td>
                <td>
                  <div class="action-btns">
                    <button class="action-btn btn-ver" title="Consultar" @click="verSolicitud(s)">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                    <button class="action-btn btn-success" title="Aprobar" :disabled="s.estatus!=='REGISTRADA'&&s.estatus!=='EN REVISIÓN'" @click="aprobar(s)">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    </button>
                    <button class="action-btn btn-danger" title="Rechazar" :disabled="s.estatus!=='REGISTRADA'&&s.estatus!=='EN REVISIÓN'" @click="rechazar(s)">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                    <button class="action-btn btn-print" title="Imprimir" @click="mostrarToast('Imprimiendo solicitud...')">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="solicitudesFiltradas.length===0"><td colspan="6" class="td-empty">NO HAY SOLICITUDES QUE COINCIDAN.</td></tr>
            </tbody>
          </table>
        </div>
        <div class="pagination">
          <span class="pag-info">{{ solicitudesFiltradas.length }} solicitudes</span>
          <div class="pag-btns"><button class="pag-btn active">1</button><button class="pag-btn">›</button></div>
        </div>
      </div>

      <!-- =========================================================
           8.4 FICHAS
      ========================================================= -->
      <div v-show="tabActivo==='fichas'">
        <div class="subtabs-wrap">
          <button class="subtab-btn" :class="{active: subFicha==='generar'}" @click="subFicha='generar'">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" x2="12" y1="18" y2="12"/><line x1="9" x2="15" y1="15" y2="15"/></svg>
            Generar Ficha
          </button>
          <button class="subtab-btn" :class="{active: subFicha==='listado'}" @click="subFicha='listado'">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg>
            Fichas Generadas
          </button>
        </div>

        <div v-show="subFicha==='generar'" class="card">
          <div class="card-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" x2="12" y1="18" y2="12"/><line x1="9" x2="15" y1="15" y2="15"/></svg>
            Generar Nueva Ficha
          </div>
          <div class="card-body">
            <div class="form-grid-2">
              <div class="form-group"><label>SELECCIONAR ASPIRANTE *</label>
                <select v-model="fichaForm.folioAsp" class="form-input" @change="autoFicha">
                  <option value="">SELECCIONAR ASPIRANTE...</option>
                  <option v-for="a in aspirantesAceptados" :key="a.folio" :value="a.folio">{{ a.folio }} — {{ a.nombre }}</option>
                </select>
              </div>
              <div class="form-group"><label>CARRERA SOLICITADA</label>
                <input :value="fichaForm.carrera" class="form-input" readonly style="background:#F4F6F9;"/>
              </div>
              <div class="form-group"><label>FECHA DE EXAMEN *</label><input v-model="fichaForm.fechaExamen" type="date" class="form-input"/></div>
              <div class="form-group"><label>HORA DEL EXAMEN</label><input v-model="fichaForm.horaExamen" type="time" class="form-input"/></div>
              <div class="form-group full-width"><label>LUGAR DE EXAMEN</label><input v-model="fichaForm.lugar" class="form-input" placeholder="EJ: AULA MAGNA — EDIFICIO CENTRAL"/></div>
              <div class="form-group"><label>NÚMERO DE FICHA (AUTOMÁTICO)</label>
                <input :value="'FIC-2026-' + String(fichas.length+1).padStart(4,'0')" class="form-input" readonly style="background:#F4F6F9;font-family:monospace;font-weight:700;color:#1D52B7;"/>
              </div>
            </div>
            <div class="form-actions">
              <button class="btn-secondary" @click="fichaForm={folioAsp:'',carrera:'',fechaExamen:'',horaExamen:'09:00',lugar:''}">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                Cancelar
              </button>
              <button class="btn-primary" @click="generarFicha">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" x2="12" y1="18" y2="12"/><line x1="9" x2="15" y1="15" y2="15"/></svg>
                Generar Ficha
              </button>
            </div>
          </div>
        </div>

        <div v-show="subFicha==='listado'">
          <div class="filters-bar">
            <div class="search-wrap">
              <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
              <input v-model="busquedaFicha" class="search-input" placeholder="BUSCAR POR FOLIO O NOMBRE..."/>
            </div>
            <select v-model="filtroPeriodoFicha" class="filter-select">
              <option value="">TODOS LOS PERIODOS</option>
              <option>2026-1</option><option>2026-2</option>
            </select>
          </div>

          <div v-if="fichasFiltradas.length===0" class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="#BDBDBD" stroke-width="1.5" style="display:block;margin:0 auto 12px"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <p>NO HAY FICHAS GENERADAS. USA LA PESTAÑA "GENERAR FICHA" PARA CREAR UNA.</p>
          </div>

          <div class="fichas-grid">
            <div v-for="f in fichasFiltradas" :key="f.numFicha" class="ficha-card">
              <div class="ficha-top">
                <div class="ficha-top-left">
                  <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                  <span>{{ f.numFicha }}</span>
                </div>
                <span class="ficha-periodo">Periodo {{ f.periodo }}</span>
              </div>
              <div class="ficha-body">
                <div class="ficha-avatar">{{ iniciales(f.nombre) }}</div>
                <div class="ficha-datos">
                  <div class="ficha-nombre">{{ f.nombre }}</div>
                  <div class="ficha-carrera">{{ f.carrera }}</div>
                  <div class="ficha-curp">{{ f.curp }}</div>
                </div>
              </div>
              <div class="ficha-info-row">
                <div class="ficha-info-item">
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#828282" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                  <span class="info-label">Fecha y hora:</span>
                  <span class="info-val">{{ f.fechaExamen }} · {{ f.horaExamen }}</span>
                </div>
                <div class="ficha-info-item">
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#828282" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                  <span class="info-label">Lugar:</span>
                  <span class="info-val">{{ f.lugar }}</span>
                </div>
              </div>
              <div class="ficha-footer">
                <span class="badge-status badge-success">
                  <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  Generada
                </span>
                <div class="action-btns">
                  <button class="action-btn btn-print" title="Imprimir" @click="mostrarToast('Imprimiendo ficha ' + f.numFicha + '...')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                  </button>
                  <button class="action-btn btn-download" title="Descargar PDF" @click="mostrarToast('Descargando PDF ' + f.numFicha + '...')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- MODAL -->
      <div v-if="modalDetalle" class="modal-overlay" @click.self="modalDetalle=false">
        <div class="modal-box">
          <div class="modal-header">
            <div style="display:flex;align-items:center;gap:8px;">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              Detalle del Aspirante
            </div>
            <button class="modal-close" @click="modalDetalle=false">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
          </div>
          <div class="modal-body" v-if="aspiranteVista">
            <div class="detalle-grid">
              <div class="detalle-item"><span class="detalle-label">FOLIO</span><span class="detalle-val">{{ aspiranteVista.folio }}</span></div>
              <div class="detalle-item"><span class="detalle-label">ESTATUS</span><span class="badge-status" :class="clsBadge(aspiranteVista.estatus)">{{ aspiranteVista.estatus }}</span></div>
              <div class="detalle-item"><span class="detalle-label">NOMBRE COMPLETO</span><span class="detalle-val">{{ aspiranteVista.nombre }}</span></div>
              <div class="detalle-item"><span class="detalle-label">CURP</span><span class="detalle-val td-mono">{{ aspiranteVista.curp }}</span></div>
              <div class="detalle-item"><span class="detalle-label">CARRERA SOLICITADA</span><span class="detalle-val">{{ aspiranteVista.carrera }}</span></div>
              <div class="detalle-item"><span class="detalle-label">FECHA DE REGISTRO</span><span class="detalle-val">{{ aspiranteVista.fechaReg }}</span></div>
              <div class="detalle-item"><span class="detalle-label">CORREO</span><span class="detalle-val">{{ aspiranteVista.correo || '—' }}</span></div>
              <div class="detalle-item"><span class="detalle-label">PROMEDIO</span><span class="detalle-val">{{ aspiranteVista.promedio || '—' }}</span></div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secondary" @click="modalDetalle=false">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
              Cerrar
            </button>
            <button class="btn-primary" @click="editarAspirante(aspiranteVista); modalDetalle=false">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
              Editar
            </button>
          </div>
        </div>
      </div>

      <!-- TOAST -->
      <transition name="toast-fade">
        <div v-if="toast.visible" class="toast-msg" :class="'toast-'+toast.tipo">
          <svg v-if="toast.tipo==='ok'"   xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          <svg v-if="toast.tipo==='warn'" xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
          <svg v-if="toast.tipo==='info'" xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
          {{ toast.msg }}
        </div>
      </transition>

    </div>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/layouts/MainLayout.vue'
import { ref, computed } from 'vue'

const tabs = [
  { id: 'config',      label: 'Configuración Inicial' },
  { id: 'aspirantes',  label: 'Aspirantes' },
  { id: 'solicitudes', label: 'Solicitudes' },
  { id: 'fichas',      label: 'Fichas' },
]
const tabActivo = ref('config')
const subConfig = ref('info')
const subAsp    = ref('consultar')
const subFicha  = ref('generar')
function goTab(id) { tabActivo.value = id }

const config = ref({ nombreProceso:'Admisión 2026-2', ciclo:'2026-2', institucion:'TecNM Campus San Luis Potosí', correo:'admisiones@slp.tecnm.mx', telefono:'444 834 0000', costo:'$250.00 MXN', mensaje:'Bienvenido al proceso de admisión del Tecnológico Nacional de México.' })
const resetConfig  = () => config.value.nombreProceso = 'Admisión 2026-2'
const guardarConfig = () => mostrarToast('Configuración guardada correctamente', 'ok')
const fechas = ref({ inicioReg:'2026-06-01', finReg:'2026-07-15', inicioFicha:'2026-07-01', finFicha:'2026-07-20', examen:'2026-07-25', resultados:'2026-07-30' })
const guardarFechas = () => mostrarToast('Fechas guardadas correctamente', 'ok')

const carreras = ['Ing. en Sistemas Computacionales','Ing. Industrial','Ing. Civil','Lic. en Administración']
const aspirantes = ref([
  { folio:'ASP-001', nombre:'Karla Mendoza Ruiz',       curp:'MERK020315MSLNDRA4', carrera:'Ing. en Sistemas Computacionales', estatus:'ACEPTADO',    fechaReg:'2026-03-10', correo:'karla@mail.com',  promedio:'9.2' },
  { folio:'ASP-002', nombre:'Luis Hernández Torres',     curp:'HETL031120HSLRRSA8', carrera:'Ing. Industrial',                  estatus:'EN REVISIÓN', fechaReg:'2026-03-12', correo:'luis@mail.com',   promedio:'8.5' },
  { folio:'ASP-003', nombre:'Daniela Castillo Vega',     curp:'CAVD040820MSLSGA1',  carrera:'Ing. Civil',                       estatus:'REGISTRADO',  fechaReg:'2026-03-15', correo:'dani@mail.com',   promedio:'7.8' },
  { folio:'ASP-004', nombre:'Miguel Ángel Flores Pérez', curp:'FOPM021215HSLRRA5',  carrera:'Lic. en Administración',           estatus:'RECHAZADO',   fechaReg:'2026-03-18', correo:'migue@mail.com',  promedio:'6.1' },
  { folio:'ASP-005', nombre:'Sofía López Guerrero',      curp:'LOGS030502MSLPZA9',  carrera:'Ing. en Sistemas Computacionales', estatus:'ACEPTADO',    fechaReg:'2026-03-20', correo:'sofi@mail.com',   promedio:'9.5' },
])
const busquedaAsp = ref(''); const filtroCarrera = ref(''); const filtroEstatus = ref('')
const aspirantesFiltrados = computed(() => {
  let r = aspirantes.value
  if (busquedaAsp.value) { const t = busquedaAsp.value.toLowerCase(); r = r.filter(a => a.nombre.toLowerCase().includes(t)||a.curp.toLowerCase().includes(t)||a.folio.toLowerCase().includes(t)) }
  if (filtroCarrera.value) r = r.filter(a => a.carrera===filtroCarrera.value)
  if (filtroEstatus.value) r = r.filter(a => a.estatus===filtroEstatus.value)
  return r
})
const aspirantesAceptados = computed(() => aspirantes.value.filter(a => a.estatus==='ACEPTADO'))
const limpiarFiltros = () => { busquedaAsp.value=''; filtroCarrera.value=''; filtroEstatus.value='' }
const modoEdicion = ref(false); const folioEdicion = ref(''); const form = ref(formVacio())
function formVacio() { return { nombre:'',apellidoP:'',apellidoM:'',curp:'',fechaNac:'',sexo:'',estadoCivil:'',correo:'',telefono:'',calle:'',numero:'',colonia:'',municipio:'',estado:'',cp:'',escuela:'',promedio:'',carrera:'',estatus:'REGISTRADO' } }
const limpiarForm = () => { modoEdicion.value=false; folioEdicion.value=''; form.value=formVacio() }
const editarAspirante = (a) => {
  modoEdicion.value=true; folioEdicion.value=a.folio; const p=a.nombre.split(' ')
  form.value={nombre:p[0]||'',apellidoP:p[1]||'',apellidoM:p[2]||'',curp:a.curp,fechaNac:'',sexo:'',estadoCivil:'',correo:a.correo||'',telefono:'',calle:'',numero:'',colonia:'',municipio:'',estado:'',cp:'',escuela:'',promedio:a.promedio||'',carrera:a.carrera,estatus:a.estatus}
  subAsp.value='nuevo'; tabActivo.value='aspirantes'
}
const guardarAspirante = () => {
  if (!form.value.nombre||!form.value.curp||!form.value.carrera) { mostrarToast('Completa los campos obligatorios (*)','warn'); return }
  const nombre=`${form.value.nombre} ${form.value.apellidoP} ${form.value.apellidoM}`.trim()
  if (modoEdicion.value) { const idx=aspirantes.value.findIndex(a=>a.folio===folioEdicion.value); if(idx>-1) aspirantes.value[idx]={...aspirantes.value[idx],nombre,curp:form.value.curp.toUpperCase(),carrera:form.value.carrera,correo:form.value.correo,promedio:form.value.promedio,estatus:form.value.estatus}; mostrarToast('Aspirante actualizado correctamente','ok') }
  else { aspirantes.value.push({folio:`ASP-${String(aspirantes.value.length+1).padStart(3,'0')}`,nombre,curp:form.value.curp.toUpperCase(),carrera:form.value.carrera,estatus:'REGISTRADO',fechaReg:new Date().toISOString().split('T')[0],correo:form.value.correo,promedio:form.value.promedio}); mostrarToast('Aspirante registrado correctamente','ok') }
  limpiarForm(); subAsp.value='consultar'
}
const modalDetalle=ref(false); const aspiranteVista=ref(null)
const verDetalle=(a)=>{aspiranteVista.value=a;modalDetalle.value=true}
const solicitudes=ref([{id:1,aspirante:'Miguel Ángel Flores Pérez',carrera:'Lic. en Administración',fecha:'2026-03-25',estatus:'REGISTRADA'},{id:2,aspirante:'Daniela Castillo Vega',carrera:'Ing. Civil',fecha:'2026-03-28',estatus:'EN REVISIÓN'},{id:3,aspirante:'Luis Hernández Torres',carrera:'Ing. Industrial',fecha:'2026-04-01',estatus:'APROBADA'},{id:4,aspirante:'Karla Mendoza Ruiz',carrera:'Ing. en Sistemas Computacionales',fecha:'2026-04-05',estatus:'RECHAZADA'}])
const busquedaSol=ref(''); const filtroEstSol=ref('')
const solicitudesFiltradas=computed(()=>{let r=solicitudes.value;if(busquedaSol.value){const t=busquedaSol.value.toLowerCase();r=r.filter(s=>s.aspirante.toLowerCase().includes(t))}if(filtroEstSol.value)r=r.filter(s=>s.estatus===filtroEstSol.value);return r})
const aprobar=(s)=>{if(s.estatus==='APROBADA'||s.estatus==='RECHAZADA')return;s.estatus='APROBADA';mostrarToast('Solicitud aprobada','ok')}
const rechazar=(s)=>{if(s.estatus==='APROBADA'||s.estatus==='RECHAZADA')return;s.estatus='RECHAZADA';mostrarToast('Solicitud rechazada','warn')}
const verSolicitud=(s)=>mostrarToast(`Solicitud #${s.id} — ${s.aspirante}`,'info')
const fichas=ref([{numFicha:'FIC-2026-0001',nombre:'Karla Mendoza Ruiz',carrera:'Ing. en Sistemas Computacionales',curp:'MERK020315MSLNDRA4',fechaExamen:'2026-07-25',horaExamen:'09:00',lugar:'Aula Magna — Edificio Central',periodo:'2026-2'},{numFicha:'FIC-2026-0002',nombre:'Sofía López Guerrero',carrera:'Ing. en Sistemas Computacionales',curp:'LOGS030502MSLPZA9',fechaExamen:'2026-07-25',horaExamen:'09:00',lugar:'Aula Magna — Edificio Central',periodo:'2026-2'}])
const fichaForm=ref({folioAsp:'',carrera:'',fechaExamen:'',horaExamen:'09:00',lugar:''}); const busquedaFicha=ref(''); const filtroPeriodoFicha=ref('')
const fichasFiltradas=computed(()=>{let r=fichas.value;if(busquedaFicha.value){const t=busquedaFicha.value.toLowerCase();r=r.filter(f=>f.nombre.toLowerCase().includes(t)||f.numFicha.toLowerCase().includes(t))}if(filtroPeriodoFicha.value)r=r.filter(f=>f.periodo===filtroPeriodoFicha.value);return r})
const autoFicha=()=>{const asp=aspirantes.value.find(a=>a.folio===fichaForm.value.folioAsp);fichaForm.value.carrera=asp?asp.carrera:''}
const generarFicha=()=>{if(!fichaForm.value.folioAsp||!fichaForm.value.fechaExamen){mostrarToast('Selecciona un aspirante y una fecha de examen','warn');return};const asp=aspirantes.value.find(a=>a.folio===fichaForm.value.folioAsp);if(!asp)return;const numFicha=`FIC-2026-${String(fichas.value.length+1).padStart(4,'0')}`;fichas.value.push({numFicha,nombre:asp.nombre,carrera:asp.carrera,curp:asp.curp,fechaExamen:fichaForm.value.fechaExamen,horaExamen:fichaForm.value.horaExamen,lugar:fichaForm.value.lugar||'Aula Magna — Edificio Central',periodo:'2026-2'});fichaForm.value={folioAsp:'',carrera:'',fechaExamen:'',horaExamen:'09:00',lugar:''};mostrarToast(`Ficha ${numFicha} generada correctamente`,'ok');subFicha.value='listado'}
const iniciales=(n)=>n.split(' ').map(x=>x[0]).slice(0,2).join('').toUpperCase()
const clsBadge=(e)=>({'ACEPTADO':'badge-success','RECHAZADO':'badge-danger','EN REVISIÓN':'badge-warning','REGISTRADO':'badge-info'}[e]||'')
const clsBadgeSol=(e)=>({'APROBADA':'badge-success','RECHAZADA':'badge-danger','EN REVISIÓN':'badge-warning','REGISTRADA':'badge-info'}[e]||'')
const toast=ref({visible:false,msg:'',tipo:'ok'})
const mostrarToast=(msg,tipo='ok')=>{toast.value={visible:true,msg,tipo};setTimeout(()=>{toast.value.visible=false},3200)}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');

.aspirantes-page { font-family:'Montserrat',sans-serif; color:#333333; text-transform:uppercase; }

/* ── Breadcrumb ── */
.breadcrumb { display:flex; align-items:center; gap:6px; font-size:13px; color:#828282; margin-bottom:16px; }
.breadcrumb-link { color:#2F80ED; text-decoration:none; font-weight:500; display:flex; align-items:center; gap:4px; }
.breadcrumb-link:hover { text-decoration:underline; }
.sep-icon { color:#BDBDBD; }
.breadcrumb-actual { color:#333333; font-weight:600; }

/* ── Page header ── */
.page-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; }
.header-left { display:flex; align-items:center; gap:14px; }
.header-icon-wrap { width:52px; height:52px; border-radius:12px; background:rgba(29,82,183,0.10); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.header-icon-wrap svg { stroke:#1D52B7; }
.page-title { font-size:26px; font-weight:700; color:#333333; margin:0;  font-family: 'Montserrat', sans-serif;}
.page-subtitle { font-size:13px; color:#4F4F4F; margin:4px 0 0; font-weight:400; }

/* ── Botones ── */
.btn-primary { background:#1D52B7; color:#fff; border:none; padding:10px 18px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; font-family:'Montserrat',sans-serif; transition:background .2s; display:flex; align-items:center; gap:6px; }
.btn-primary:hover { background:#0B2545; }
.btn-secondary { background:#F2F4F7; color:#333333; border:1px solid #E0E0E0; padding:10px 18px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; font-family:'Montserrat',sans-serif; display:flex; align-items:center; gap:6px; }
.btn-secondary:hover { background:#E0E0E0; }
.btn-secondary-sm { background:#F2F4F7; color:#4F4F4F; border:1px solid #E0E0E0; padding:8px 13px; border-radius:7px; font-size:12px; font-weight:600; cursor:pointer; font-family:'Montserrat',sans-serif; display:flex; align-items:center; gap:5px; white-space:nowrap; }

/* ── KPIs ── */
.kpi-row { display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap; }
.kpi-card { background:#fff; border-radius:10px; border:1px solid #E0E0E0; padding:14px 18px; flex:1; min-width:130px; display:flex; align-items:center; gap:12px; box-shadow:0 2px 8px rgba(29,82,183,0.05); }
.kpi-icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.kpi-num { display:block; font-size:24px; font-weight:700; color:#333333; line-height:1.1; }
.kpi-label { display:block; font-size:11px; color:#828282; font-weight:500; margin-top:2px; }

/* ── Tabs ── */
.tabs-wrap { display:flex; border-bottom:2px solid #E0E0E0; margin-bottom:20px; gap:2px; flex-wrap:wrap; }
.tab-btn { padding:10px 16px; font-size:13px; font-weight:600; color:#828282; background:none; border:none; border-bottom:3px solid transparent; margin-bottom:-2px; cursor:pointer; font-family:'Montserrat',sans-serif; transition:all .15s; border-radius:6px 6px 0 0; display:flex; align-items:center; gap:6px; }
.tab-btn:hover { color:#1D52B7; background:rgba(29,82,183,0.05); }
.tab-btn.active { color:#1D52B7; border-bottom-color:#1D52B7; background:rgba(29,82,183,0.05); }

/* ── Subtabs ── */
.subtabs-wrap { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
.subtab-btn { padding:7px 14px; border-radius:7px; font-size:12px; font-weight:600; cursor:pointer; border:1px solid #E0E0E0; color:#828282; background:#fff; font-family:'Montserrat',sans-serif; transition:all .15s; display:flex; align-items:center; gap:6px; }
.subtab-btn:hover { border-color:#1D52B7; color:#1D52B7; }
.subtab-btn.active { background:#1D52B7; color:#fff; border-color:#1D52B7; }
.subtab-btn.active svg { stroke:#fff; }

/* ── Cards ── */
.card { background:#fff; border-radius:10px; border:1px solid #E0E0E0; overflow:hidden; margin-bottom:16px; box-shadow:0 2px 8px rgba(29,82,183,0.05); }
.card-header { background:#F4F6F9; padding:12px 18px; font-size:14px; font-weight:600; color:#0B2545; border-bottom:1px solid #E0E0E0; display:flex; align-items:center; gap:8px; }
.card-header svg { stroke:#1A4184; flex-shrink:0; }
.card-body { padding:18px; }

/* ── Formularios ── */
.form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.form-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; }
.form-group { display:flex; flex-direction:column; gap:5px; }
.form-group.full-width { grid-column:1/-1; }
.form-group label { font-size:12px; font-weight:600; color:#4F4F4F; }
.form-input { padding:9px 12px; border:1px solid #E0E0E0; border-radius:7px; font-size:13px; color:#333333; background:#fff; font-family:'Montserrat',sans-serif; outline:none; width:100%; transition:border-color .2s, box-shadow .2s; text-transform:none; }
.form-input:focus { border-color:#1D52B7; box-shadow:0 0 0 3px rgba(29,82,183,0.15); }
.form-input::placeholder { color:#828282; }
textarea.form-input { resize:vertical; min-height:72px; }
.form-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:16px; }

/* ── Filtros ── */
.filters-bar { display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap; align-items:center; }
.search-wrap { position:relative; display:flex; align-items:center; }
.search-icon { position:absolute; left:10px; width:15px; height:15px; stroke:#828282; pointer-events:none; }
.search-input { padding:8px 12px 8px 32px; border:1px solid #E0E0E0; border-radius:7px; font-size:13px; color:#333333; background:#fff; font-family:'Montserrat',sans-serif; outline:none; min-width:220px; }
.search-input:focus { border-color:#1D52B7; box-shadow:0 0 0 2px rgba(29,82,183,0.15); }
.search-input::placeholder { color:#828282; }
.filter-select { padding:8px 12px; border:1px solid #E0E0E0; border-radius:7px; font-size:13px; color:#333333; background:#fff; font-family:'Montserrat',sans-serif; outline:none; cursor:pointer; }
.filter-select:focus { border-color:#1D52B7; }

/* ── Tabla ── */
.table-card { background:#fff; border-radius:10px; border:1px solid #E0E0E0; overflow:hidden; margin-bottom:10px; box-shadow:0 2px 8px rgba(29,82,183,0.05); }
table { width:100%; border-collapse:collapse; table-layout:fixed; }
th { background:#F4F6F9; padding:11px 14px; text-align:left; font-size:12px; font-weight:600; color:#0B2545; border-bottom:2px solid #E0E0E0; }
td { padding:11px 14px; border-bottom:1px solid #F4F6F9; font-size:13px; color:#333333; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
tr:last-child td { border-bottom:none; }
tr:hover td { background:#F4F6F9; }
.td-empty { text-align:center; color:#828282; padding:2.5rem; font-size:13px; white-space:normal; }
.td-bold { font-weight:600; }
.td-mono { font-family:monospace; font-size:12px; letter-spacing:.4px; color:#4F4F4F; }

/* ── Badges ── */
.badge-folio { background:rgba(29,82,183,0.10); color:#1A4184; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; font-family:monospace; }
.badge-status { padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; display:inline-flex; align-items:center; gap:4px; }
.badge-success { background:rgba(39,174,96,0.10);  color:#27AE60; }
.badge-danger  { background:rgba(235,87,87,0.10);  color:#EB5757; }
.badge-warning { background:rgba(242,153,74,0.10); color:#F2994A; }
.badge-info    { background:rgba(29,82,183,0.10);  color:#1D52B7; }

/* ── Acciones ── */
.action-btns { display:flex; gap:5px; }
.action-btn { border:none; border-radius:6px; padding:6px 8px; cursor:pointer; display:flex; align-items:center; transition:opacity .15s, transform .1s; }
.action-btn:hover { transform:scale(1.08); }
.action-btn:disabled { opacity:.35; cursor:not-allowed; transform:none; }
.btn-ver      { background:rgba(47,128,237,0.12);  } .btn-ver svg      { stroke:#2F80ED; }
.btn-edit     { background:rgba(242,153,74,0.12);  } .btn-edit svg     { stroke:#F2994A; }
.btn-success  { background:rgba(39,174,96,0.12);   } .btn-success svg  { stroke:#27AE60; }
.btn-danger   { background:rgba(235,87,87,0.12);   } .btn-danger svg   { stroke:#EB5757; }
.btn-print    { background:#F2F4F7;                } .btn-print svg    { stroke:#4F4F4F; }
.btn-download { background:rgba(29,82,183,0.12);   } .btn-download svg { stroke:#1D52B7; }

/* ── Paginación ── */
.pagination { display:flex; justify-content:space-between; align-items:center; font-size:13px; color:#828282; font-weight:500; margin-top:8px; }
.pag-btns { display:flex; gap:5px; }
.pag-btn { padding:6px 12px; border:1px solid #E0E0E0; background:#fff; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; color:#333333; font-family:'Montserrat',sans-serif; transition:all .15s; }
.pag-btn:hover { border-color:#1D52B7; color:#1D52B7; }
.pag-btn.active { background:#1D52B7; color:#fff; border-color:#1D52B7; }

/* ── Fichas ── */
.empty-state { background:#fff; border-radius:10px; padding:3rem; text-align:center; color:#828282; font-size:13px; border:1px solid #E0E0E0; margin-bottom:14px; }
.fichas-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:16px; }
.ficha-card { background:#fff; border-radius:10px; border:1px solid #E0E0E0; overflow:hidden; border-top:3px solid #1D52B7; box-shadow:0 2px 8px rgba(29,82,183,0.07); transition:box-shadow .2s; }
.ficha-card:hover { box-shadow:0 6px 20px rgba(29,82,183,0.12); }
.ficha-top { background:#F4F6F9; padding:9px 14px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #E0E0E0; }
.ficha-top-left { display:flex; align-items:center; gap:5px; font-size:11px; font-weight:700; color:#1A4184; }
.ficha-top-left svg { stroke:#1A4184; }
.ficha-periodo { font-size:11px; color:#828282; font-weight:500; background:#fff; padding:2px 8px; border-radius:20px; border:1px solid #E0E0E0; }
.ficha-body { padding:14px; display:flex; gap:12px; align-items:center; }
.ficha-avatar { width:44px; height:44px; border-radius:50%; background:#1D52B7; color:#fff; display:flex; align-items:center; justify-content:center; font-size:15px; font-weight:700; flex-shrink:0; }
.ficha-nombre { font-size:14px; font-weight:700; color:#333333; margin-bottom:3px; }
.ficha-carrera { font-size:12px; color:#4F4F4F; margin-bottom:2px; }
.ficha-curp { font-size:11px; color:#828282; font-family:monospace; }
.ficha-info-row { display:flex; flex-direction:column; gap:5px; margin:0 14px 10px; padding:10px; background:#F4F6F9; border-radius:7px; }
.ficha-info-item { display:flex; align-items:center; gap:5px; }
.ficha-info-item svg { flex-shrink:0; }
.info-label { font-size:11px; color:#828282; font-weight:600; }
.info-val { font-size:12px; color:#333333; font-weight:600; }
.ficha-footer { padding:10px 14px; border-top:1px solid #F4F6F9; display:flex; justify-content:space-between; align-items:center; }

/* ── Modal ── */
.modal-overlay { position:fixed; inset:0; background:rgba(11,37,69,0.55); z-index:2000; display:flex; align-items:center; justify-content:center; }
.modal-box { background:#fff; border-radius:12px; width:600px; max-width:95vw; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
.modal-header { background:#0B2545; padding:16px 22px; display:flex; justify-content:space-between; align-items:center; border-radius:12px 12px 0 0; color:#fff; font-size:15px; font-weight:600; }
.modal-close { background:none; border:none; color:rgba(255,255,255,.8); cursor:pointer; display:flex; align-items:center; padding:4px; border-radius:6px; transition:background .15s; }
.modal-close:hover { background:rgba(255,255,255,.15); }
.modal-body { padding:22px; }
.detalle-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.detalle-item { display:flex; flex-direction:column; gap:4px; }
.detalle-label { font-size:11px; font-weight:600; color:#828282; text-transform:uppercase; letter-spacing:.06em; }
.detalle-val { font-size:14px; font-weight:600; color:#333333; }
.modal-footer { padding:14px 22px; border-top:1px solid #E0E0E0; display:flex; justify-content:flex-end; gap:10px; }

/* ── Toast ── */
.toast-msg { position:fixed; bottom:2rem; right:2rem; padding:12px 20px; border-radius:8px; font-weight:600; font-size:13px; z-index:9999; box-shadow:0 8px 24px rgba(0,0,0,0.15); font-family:'Montserrat',sans-serif; display:flex; align-items:center; gap:8px; }
.toast-ok   { background:#27AE60; color:#fff; }
.toast-warn { background:#F2994A; color:#fff; }
.toast-info { background:#1D52B7; color:#fff; }
.toast-fade-enter-active,.toast-fade-leave-active { transition:all .3s; }
.toast-fade-enter-from,.toast-fade-leave-to { opacity:0; transform:translateY(10px); }
</style>