<!-- ============================================= -->
<!-- src/views/EventosView.vue                    -->
<!-- Módulo: Eventos — Vista principal            -->
<!-- Rediseño visual SaaS moderno                 -->
<!-- ============================================= -->
<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="eventos-page">
      <!-- Barra de carga superior -->
      <div class="barra-carga" :class="{ activa: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- Breadcrumb -->
      <div class="breadcrumb">
        <router-link to="/dashboard" class="breadcrumb-link">INICIO</router-link>
        <span class="sep">›</span>
        <span class="activo">EVENTOS</span>
      </div>

      <!-- ─── ENCABEZADO ─── -->
      <div class="encabezado-seccion">
        <div>
          <h1 class="titulo-pagina">EVENTOS</h1>
          <p class="subtitulo">GESTIÓN DE EVENTOS INSTITUCIONALES Y CONTROL DE PARTICIPACIÓN</p>
        </div>
      </div>

      <!-- ─── KPIs ─── -->
      <div class="kpis-row">
        <div class="kpi-card kpi-azul">
          <div class="kpi-icono-wrap kpi-icono-azul">
            <svg viewBox="0 0 24 24" fill="none" stroke="#1D52B7" stroke-width="2" width="22" height="22"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <div class="kpi-datos">
            <span class="kpi-numero">{{ eventos.length }}</span>
            <span class="kpi-label">TOTAL EVENTOS</span>
          </div>
        </div>
        <div class="kpi-card kpi-verde">
          <div class="kpi-icono-wrap kpi-icono-verde">
            <svg viewBox="0 0 24 24" fill="none" stroke="#27AE60" stroke-width="2" width="22" height="22"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <div class="kpi-datos">
            <span class="kpi-numero">{{ eventosProximos.length }}</span>
            <span class="kpi-label">PRÓXIMOS</span>
          </div>
        </div>
        <div class="kpi-card kpi-gris">
          <div class="kpi-icono-wrap kpi-icono-gris">
            <svg viewBox="0 0 24 24" fill="none" stroke="#828282" stroke-width="2" width="22" height="22"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          </div>
          <div class="kpi-datos">
            <!-- CORRECCIÓN: eventosFinalizados ahora es un computed declarado en el script -->
            <span class="kpi-numero">{{ eventosFinalizados }}</span>
            <span class="kpi-label">FINALIZADOS</span>
          </div>
        </div>
      </div>

      <!-- ─── FILTROS (movido aquí: después de KPIs, antes de Eventos Próximos) ─── -->
      <div class="filtros-card">
        <div class="busqueda-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-busqueda">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input
            v-model="busquedaNombre"
            type="text"
            placeholder="BUSCAR POR NOMBRE DE EVENTO..."
            class="input-busqueda"
            @input="reiniciarPagina()"
          />
          <button v-if="busquedaNombre" @click="busquedaNombre = ''; reiniciarPagina()" class="btn-limpiar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        <button @click="mostrarFiltrosAvanzados = true" class="btn-filtros-premium">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
          </svg>
          FILTROS AVANZADOS
        </button>
      </div>

      <!-- ─── EVENTOS PRÓXIMOS ─── -->
      <div class="seccion-titulo-wrapper">
        <h2 class="seccion-titulo">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          EVENTOS PRÓXIMOS
        </h2>
        <span class="seccion-contador">{{ eventosProximos.length }} EVENTO(S)</span>
      </div>

      <div v-if="eventosProximos.length > 0" class="eventos-proximos-grid">
        <div
          v-for="evento in eventosProximos"
          :key="evento.id_evento"
          class="evento-card"
        >
          <div class="evento-card-izq">
            <div
              class="stat-icono-wrapper"
              :style="{ background: colorFondoTipo(evento.tipo_evento?.nombre_tipo) }"
            >
              <svg
                viewBox="0 0 24 24"
                fill="none"
                :stroke="colorTipo(evento.tipo_evento?.nombre_tipo)"
                stroke-width="2"
                width="24"
                height="24"
              >
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
            </div>
          </div>

          <div class="evento-card-cuerpo">
            <div class="evento-card-superior">
              <!-- CORRECCIÓN: Se usa nombre_evento (compatible con backend) -->
              <span class="evento-nombre">{{ evento.nombre_evento || evento.nombre }}</span>
              <span
                class="badge-tipo"
                :style="estiloBadgeTipo(evento.tipo_evento?.nombre_tipo)"
              >
                {{ evento.tipo_evento?.nombre_tipo || 'GENERAL' }}
              </span>
            </div>
            <div class="evento-card-meta">
              <span class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                  <rect x="3" y="4" width="18" height="18" rx="2"/>
                  <line x1="3" y1="10" x2="21" y2="10"/>
                  <line x1="16" y1="2" x2="16" y2="6"/>
                  <line x1="8" y1="2" x2="8" y2="6"/>
                </svg>
                {{ formatearFecha(evento.fecha) }}
              </span>
              <span v-if="evento.descripcion" class="meta-item descripcion-resumida">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                  <line x1="16" y1="13" x2="8" y2="13"/>
                  <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                {{ evento.descripcion.substring(0, 60) }}{{ evento.descripcion.length > 60 ? '...' : '' }}
              </span>
            </div>
          </div>

          <!-- Acciones de la card próxima -->
          <div class="evento-card-acciones">
            <button @click="abrirModalDetalle(evento)" class="btn-secundario btn-card-accion">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              VER DETALLE
            </button>
            <button
              @click="router.push(`/eventos/${evento.id_evento}/participantes`)"
              class="btn-secundario btn-card-accion"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
              PARTICIPANTES
            </button>
          </div>
        </div>
      </div>

      <!-- ─── EMPTY STATE REDISEÑADO ─── -->
      <div v-else class="estado-vacio">
        <div class="estado-vacio-decoracion">
          <div class="estado-vacio-circulo-exterior"></div>
          <div class="estado-vacio-circulo-interior"></div>
          <div class="estado-vacio-icono-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="#1D52B7" stroke-width="1.5" width="36" height="36">
              <rect x="3" y="4" width="18" height="18" rx="2"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
            </svg>
          </div>
        </div>
        <p class="vacio-titulo">SIN EVENTOS PRÓXIMOS</p>
        <p class="vacio-subtitulo">AÚN NO TIENES EVENTOS PROGRAMADOS EN EL CALENDARIO.</p>
        <div class="vacio-cta">
        </div>
      </div>

      <!-- ─── HISTORIAL DE EVENTOS ─── -->
      <div class="seccion-titulo-wrapper" style="margin-top: 2.5rem;">
        <h2 class="seccion-titulo">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
          HISTORIAL DE EVENTOS
        </h2>
      </div>

      <!-- ─── MODAL: Filtros Avanzados ─── -->
      <transition name="modal-fade">
        <div
          v-if="mostrarFiltrosAvanzados"
          class="modal-fondo"
          @click.self="mostrarFiltrosAvanzados = false"
          role="dialog"
          aria-modal="true"
          aria-labelledby="titulo-filtros"
        >
          <div class="modal-caja modal-filtros">
            <div class="modal-cabecera">
              <h3 id="titulo-filtros">FILTROS</h3>
              <button @click="mostrarFiltrosAvanzados = false" class="btn-cerrar-modal" aria-label="CERRAR FILTROS">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20">
                  <line x1="18" y1="6" x2="6" y2="18"/>
                  <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>
            <div class="modal-cuerpo filtros-grid">
              <div class="campo-form">
                <label class="campo-label">TIPO DE EVENTO</label>
                <select v-model="filtrosAvanzados.tipo" class="campo-input" @change="reiniciarPagina()">
                  <option value="">TODOS LOS TIPOS</option>
                  <option v-for="t in tiposEvento" :key="t.id_tipo_evento" :value="t.id_tipo_evento">
                    {{ t.nombre_tipo }}
                  </option>
                </select>
              </div>
              <div class="campo-form">
                <label class="campo-label">ESTATUS</label>
                <!-- CORRECCIÓN: Eliminada la opción "CANCELADO" que no existe en el backend
                     y no tenía lógica de filtrado asociada (código muerto) -->
                <select v-model="filtrosAvanzados.estatus" class="campo-input" @change="reiniciarPagina()">
                  <option value="">TODOS</option>
                  <option value="Próximo">PRÓXIMO</option>
                  <option value="Finalizado">FINALIZADO</option>
                </select>
              </div>
              <div class="campo-form">
                <label class="campo-label">FECHA DESDE</label>
                <input v-model="filtrosAvanzados.fechaInicio" type="date" class="campo-input" @change="reiniciarPagina()" />
              </div>
              <div class="campo-form">
                <label class="campo-label">FECHA HASTA</label>
                <input v-model="filtrosAvanzados.fechaFin" type="date" class="campo-input" @change="reiniciarPagina()" />
              </div>
            </div>
            <div class="modal-pie">
              <button @click="limpiarFiltros()" class="btn-cancelar">LIMPIAR FILTROS</button>
              <button @click="mostrarFiltrosAvanzados = false" class="btn-primario">APLICAR FILTROS</button>
            </div>
          </div>
        </div>
      </transition>

      <!-- ─── TABLA: Historial ─── -->
      <div class="tabla-card">
        <div class="tabla-encabezado">
          <span class="tabla-contador">
            {{ eventosFiltrados.length }} REGISTRO(S) · PÁGINA {{ paginaActual }} DE {{ totalPaginas }}
          </span>
        </div>
        <div class="tabla-scroll">
          <table class="tabla-principal">
            <thead>
              <tr>
                <th>NOMBRE DEL EVENTO</th>
                <th>TIPO</th>
                <th>FECHA</th>
                <th>DESCRIPCIÓN</th>
                <th class="centrado">ACCIONES</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="evento in eventosPaginados" :key="evento.id_evento">
                <!-- CORRECCIÓN: Fallback a nombre en caso de que nombre_evento no esté -->
                <td><span class="texto-principal">{{ evento.nombre_evento || evento.nombre }}</span></td>
                <td>
                  <span class="badge-estado" :style="estiloBadgeTipo(evento.tipo_evento?.nombre_tipo)">
                    {{ evento.tipo_evento?.nombre_tipo || 'GENERAL' }}
                  </span>
                </td>
                <td class="texto-secundario">{{ formatearFecha(evento.fecha) }}</td>
                <td class="texto-secundario texto-corto">{{ evento.descripcion || '—' }}</td>
                <td class="centrado">
                  <div class="acciones-fila">
                    <button
                      @click="abrirModalDetalle(evento)"
                      class="btn-accion ver"
                      title="VER DETALLE DEL EVENTO"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                      </svg>
                    </button>
                    <button
  @click="router.push({
    name: 'FormularioEvento',
    params: { id: evento.id_evento }
  })"
  class="btn-accion editar"
  title="EDITAR EVENTO"
>
  <svg
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    width="15"
    height="15"
  >
    <path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
  </svg>
</button>
                    <button
                      @click="eliminarEvento(evento)"
                      class="btn-accion eliminar"
                      title="ELIMINAR EVENTO"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="eventosPaginados.length === 0">
                <td colspan="5" class="sin-resultados">
                  <svg viewBox="0 0 24 24" fill="none" stroke="#E0E0E0" stroke-width="1.5" width="40" height="40">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                  </svg>
                  <p>NO SE ENCONTRARON EVENTOS CON LOS FILTROS APLICADOS</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div v-if="totalPaginas > 1" class="paginacion-container">
          <div class="paginacion-info">
            <span>MOSTRANDO {{ mostrandoDesde }}-{{ mostrandoHasta }} DE {{ eventosFiltrados.length }}</span>
          </div>
          <div class="paginacion-controles">
            <button
              @click="cambiarPagina(paginaActual - 1)"
              :disabled="paginaActual === 1"
              class="btn-pag"
              title="ANTERIOR"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <polyline points="15 18 9 12 15 6"/>
              </svg>
            </button>
            <div class="paginacion-numeros">
              <button
                v-for="p in totalPaginas"
                :key="p"
                @click="cambiarPagina(p)"
                class="btn-num"
                :class="{ activa: paginaActual === p }"
              >{{ p }}</button>
            </div>
            <button
              @click="cambiarPagina(paginaActual + 1)"
              :disabled="paginaActual === totalPaginas"
              class="btn-pag"
              title="SIGUIENTE"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <polyline points="9 18 15 12 9 6"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════ -->
      <!-- MODAL: DETALLE DEL EVENTO                              -->
      <!-- ═══════════════════════════════════════════════════════ -->
      <transition name="modal-fade">
        <div
          v-if="mostrarModalDetalle && eventoDetalle"
          class="modal-fondo"
          @click.self="cerrarModalDetalle()"
          role="dialog"
          aria-modal="true"
          aria-labelledby="titulo-detalle-evento"
        >
          <div class="modal-caja modal-detalle">

            <div class="modal-cabecera modal-detalle-cabecera">
              <div class="detalle-cabecera-izq">
                <div
                  class="detalle-icono-grande"
                  :style="{ background: colorFondoTipo(eventoDetalle.tipo_evento?.nombre_tipo) }"
                >
                  <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    :stroke="colorTipo(eventoDetalle.tipo_evento?.nombre_tipo)"
                    stroke-width="2"
                    width="28"
                    height="28"
                  >
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                  </svg>
                </div>
                <div>
                  <!-- CORRECCIÓN: Fallback a nombre -->
                  <h3 id="titulo-detalle-evento" class="detalle-titulo">{{ eventoDetalle.nombre_evento || eventoDetalle.nombre }}</h3>
                  <span
                    class="badge-tipo detalle-badge"
                    :style="estiloBadgeTipo(eventoDetalle.tipo_evento?.nombre_tipo)"
                  >
                    {{ eventoDetalle.tipo_evento?.nombre_tipo || 'GENERAL' }}
                  </span>
                </div>
              </div>
              <button
                @click="cerrarModalDetalle()"
                class="btn-cerrar-modal"
                aria-label="CERRAR DETALLE DEL EVENTO"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="22" height="22">
                  <line x1="18" y1="6" x2="6" y2="18"/>
                  <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>

            <div class="modal-cuerpo detalle-cuerpo">

              <div class="detalle-meta-grid">
                <div class="detalle-meta-item">
                  <div class="detalle-meta-icono">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#0B2545" stroke-width="2" width="18" height="18">
                      <rect x="3" y="4" width="18" height="18" rx="2"/>
                      <line x1="3" y1="10" x2="21" y2="10"/>
                      <line x1="16" y1="2" x2="16" y2="6"/>
                      <line x1="8" y1="2" x2="8" y2="6"/>
                    </svg>
                  </div>
                  <div>
                    <p class="detalle-meta-etiqueta">FECHA DEL EVENTO</p>
                    <p class="detalle-meta-valor">{{ formatearFecha(eventoDetalle.fecha) }}</p>
                  </div>
                </div>

                <div class="detalle-meta-item">
                  <div class="detalle-meta-icono">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#0B2545" stroke-width="2" width="18" height="18">
                      <circle cx="12" cy="12" r="10"/>
                      <polyline points="12 6 12 12 16 14"/>
                    </svg>
                  </div>
                  <div>
                    <p class="detalle-meta-etiqueta">ESTATUS</p>
                    <span class="badge-estatus" :class="classBadgeEstatus(eventoDetalle.fecha)">
                      {{ eventoDetalle.fecha >= hoy ? 'PRÓXIMO' : 'FINALIZADO' }}
                    </span>
                  </div>
                </div>

                <!-- hora y lugar se mantienen con v-if ya que podrían existir en versiones futuras del backend -->
                <div v-if="eventoDetalle.hora" class="detalle-meta-item">
                  <div class="detalle-meta-icono">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#0B2545" stroke-width="2" width="18" height="18">
                      <circle cx="12" cy="12" r="10"/>
                      <polyline points="12 6 12 12 16 14"/>
                    </svg>
                  </div>
                  <div>
                    <p class="detalle-meta-etiqueta">HORA</p>
                    <p class="detalle-meta-valor">{{ eventoDetalle.hora }}</p>
                  </div>
                </div>

                <div v-if="eventoDetalle.lugar" class="detalle-meta-item">
                  <div class="detalle-meta-icono">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#0B2545" stroke-width="2" width="18" height="18">
                      <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                      <circle cx="12" cy="10" r="3"/>
                    </svg>
                  </div>
                  <div>
                    <p class="detalle-meta-etiqueta">LUGAR</p>
                    <p class="detalle-meta-valor">{{ eventoDetalle.lugar }}</p>
                  </div>
                </div>

                <!-- CORRECCIÓN: total_participantes → participantes (campo real del backend) -->
                <div v-if="eventoDetalle.participantes !== undefined && eventoDetalle.participantes !== null" class="detalle-meta-item">
                  <div class="detalle-meta-icono">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#0B2545" stroke-width="2" width="18" height="18">
                      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                      <circle cx="9" cy="7" r="4"/>
                      <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                  </div>
                  <div>
                    <p class="detalle-meta-etiqueta">PARTICIPANTES</p>
                    <p class="detalle-meta-valor">{{ eventoDetalle.participantes }}</p>
                  </div>
                </div>
              </div>

              <div class="detalle-separador"></div>

              <div class="detalle-descripcion-bloque">
                <p class="detalle-seccion-label">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                  </svg>
                  DESCRIPCIÓN
                </p>
                <p v-if="eventoDetalle.descripcion" class="detalle-descripcion-texto">
                  {{ eventoDetalle.descripcion }}
                </p>
                <p v-else class="detalle-descripcion-vacio">SIN DESCRIPCIÓN REGISTRADA.</p>
              </div>

            </div>

            <div class="modal-pie detalle-pie">
              <button
                @click="router.push(`/eventos/${eventoDetalle.id_evento}/participantes`); cerrarModalDetalle()"
                class="btn-secundario"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                  <circle cx="9" cy="7" r="4"/>
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                PARTICIPANTES
              </button>

              <div class="detalle-pie-derecha">
                <button
  @click="router.push(`/eventos/${eventoDetalle.id_evento}/editar`)"
  class="btn-secundario"
>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
    <path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
  </svg>
  EDITAR
</button>
                <button
                  @click="cerrarModalDetalle(); eliminarEvento(eventoDetalle)"
                  class="btn-eliminar"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                  </svg>
                  ELIMINAR
                </button>
              </div>
            </div>

          </div>
        </div>
      </transition>

      <footer class="pie-pagina">© 2026 TECNOLÓGICO NACIONAL DE MÉXICO · TODOS LOS DERECHOS RESERVADOS</footer>
    </div>
  </MainLayout>
</template>

<script setup>
// ──────────────────────────────────────────────────
// Importaciones
// ──────────────────────────────────────────────────
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const router = useRouter()
const API = `${import.meta.env.VITE_API_URL}/api`

// ──────────────────────────────────────────────────
// Auth headers
// ──────────────────────────────────────────────────
const token = localStorage.getItem('auth_token')
const headers    = { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` }
const headersGet = { 'Authorization': `Bearer ${token}` }

// ──────────────────────────────────────────────────
// Estado general
// ──────────────────────────────────────────────────
const cargando       = ref(false)
const cargandoForm   = ref(false)
const busquedaNombre = ref('')
const tiposEvento    = ref([])
const eventos        = ref([])
const paginaActual   = ref(1)
const registrosPorPagina = 10

// ──────────────────────────────────────────────────
// Filtros avanzados
// ──────────────────────────────────────────────────
const mostrarFiltrosAvanzados = ref(false)
const filtrosAvanzados = ref({ tipo: '', estatus: '', fechaInicio: '', fechaFin: '' })

// ──────────────────────────────────────────────────
// Modal: Crear / Editar evento
// CORRECCIÓN: Las claves del form coinciden exactamente con el payload del backend:
//   { nombre, tipo_evento_id, fecha, descripcion }
// ──────────────────────────────────────────────────

// ──────────────────────────────────────────────────
// Modal: Detalle del evento
// ──────────────────────────────────────────────────
const mostrarModalDetalle = ref(false)
const eventoDetalle       = ref(null)

const abrirModalDetalle = (evento) => {
  eventoDetalle.value = evento
  mostrarModalDetalle.value = true
}

const cerrarModalDetalle = () => {
  mostrarModalDetalle.value = false
  eventoDetalle.value = null
}

// ──────────────────────────────────────────────────
// Toast
// ──────────────────────────────────────────────────
const fechaMinima = computed(() => new Date().toISOString().split('T')[0])
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerToast = null

const mostrarToast = (mensaje, tipo = 'exito') => {
  if (timerToast) clearTimeout(timerToast)
  toast.value = { visible: true, mensaje, tipo }
  timerToast = setTimeout(() => toast.value.visible = false, 3500)
}

// ──────────────────────────────────────────────────
// Carga de datos
// CORRECCIÓN: Manejo defensivo para respuestas tipo { data: [] } (Laravel pagination)
// ──────────────────────────────────────────────────
const cargarTipos = async () => {
  try {
    const res = await fetch(`${API}/tipos-evento`, { headers: headersGet })
    if (!res.ok) throw new Error()
    const data = await res.json()
    tiposEvento.value = Array.isArray(data) ? data : (Array.isArray(data.data) ? data.data : [])
  } catch {
    tiposEvento.value = []
  }
}

const cargarEventos = async () => {
  cargando.value = true
  try {
    const res = await fetch(`${API}/eventos`, { headers: headersGet })
    if (!res.ok) throw new Error()
    const data = await res.json()
    eventos.value = Array.isArray(data) ? data : (Array.isArray(data.data) ? data.data : [])
  } catch (err) {
    console.error(err)
    eventos.value = []
  } finally {
    cargando.value = false
  }
}

onMounted(() => { cargarTipos(); cargarEventos() })

// ──────────────────────────────────────────────────
// Filtrado y Paginación
// ──────────────────────────────────────────────────
const hoy = new Date().toISOString().split('T')[0]

const eventosFiltrados = computed(() => {
  return eventos.value.filter(e => {
    // CORRECCIÓN: Búsqueda por nombre usando ambos campos que devuelve el backend
    const nombreEvento = (e.nombre_evento || e.nombre || '').toLowerCase()
    const matchNombre = !busquedaNombre.value || nombreEvento.includes(busquedaNombre.value.toLowerCase())

    // CORRECCIÓN: Filtro por tipo usando tipo_evento_id (campo real del backend)
    const matchTipo =
  !filtrosAvanzados.value.tipo ||
  String(e.tipo_evento?.id_tipo_evento) ===
  String(filtrosAvanzados.value.tipo)

    const esProximo    = e.fecha >= hoy
    const esFinalizado = e.fecha < hoy
    let matchEstatus   = true
    if (filtrosAvanzados.value.estatus === 'Próximo')    matchEstatus = esProximo
    if (filtrosAvanzados.value.estatus === 'Finalizado') matchEstatus = esFinalizado

    let matchFecha = true
    if (filtrosAvanzados.value.fechaInicio && e.fecha < filtrosAvanzados.value.fechaInicio) matchFecha = false
    if (filtrosAvanzados.value.fechaFin    && e.fecha > filtrosAvanzados.value.fechaFin)    matchFecha = false

    return matchNombre && matchTipo && matchEstatus && matchFecha
  })
})

// KPI: Eventos próximos — siempre sobre todos los eventos (no filtrados)
const eventosProximos = computed(() =>
  eventosFiltrados.value.filter(e => e.fecha >= hoy)
)

// CORRECCIÓN: eventosFinalizados no estaba declarado — bug crítico de runtime
const eventosFinalizados = computed(() =>
  eventos.value.filter(e => e.fecha < hoy).length
)

const eventosPaginados = computed(() => {
  const inicio = (paginaActual.value - 1) * registrosPorPagina
  return eventosFiltrados.value.slice(inicio, inicio + registrosPorPagina)
})

const totalPaginas   = computed(() => Math.max(1, Math.ceil(eventosFiltrados.value.length / registrosPorPagina)))
const mostrandoDesde = computed(() => eventosFiltrados.value.length === 0 ? 0 : (paginaActual.value - 1) * registrosPorPagina + 1)
const mostrandoHasta = computed(() => Math.min(paginaActual.value * registrosPorPagina, eventosFiltrados.value.length))

const reiniciarPagina = () => { paginaActual.value = 1 }
const cambiarPagina   = (p) => { if (p >= 1 && p <= totalPaginas.value) paginaActual.value = p }
const limpiarFiltros  = () => { filtrosAvanzados.value = { tipo: '', estatus: '', fechaInicio: '', fechaFin: '' }; reiniciarPagina() }

// ──────────────────────────────────────────────────
// Modal: Crear / Editar
// CORRECCIÓN: El form usa claves que coinciden directamente con el payload del backend.
//   Al cargar para editar, se mapea tipo_evento_id desde el evento.
// ──────────────────────────────────────────────────
// ──────────────────────────────────────────────────
// Eliminar evento
// CORRECCIÓN: Usa id_evento (campo real del backend) y headers con Authorization
// ──────────────────────────────────────────────────
const eliminarEvento = async (e) => {
  // CORRECCIÓN: Nombre del evento con fallback doble para compatibilidad
  const nombreEvento = e.nombre_evento || e.nombre || 'este evento'
  if (!confirm(`¿Eliminar el evento "${nombreEvento}"?`)) return
  cargando.value = true
  try {
    // CORRECCIÓN: Usa e.id_evento (id correcto del backend)
    const res = await fetch(`${API}/eventos/${e.id_evento}`, {
      method: 'DELETE',
      headers: headersGet
    })
    if (!res.ok) throw new Error('No se pudo eliminar')
    mostrarToast('Evento eliminado correctamente')
    await cargarEventos()
  } catch (err) {
    mostrarToast(err.message, 'error')
  } finally {
    cargando.value = false
  }
}

// ──────────────────────────────────────────────────
// Helpers de formato y color
// ──────────────────────────────────────────────────
const formatearFecha = (f) => {
  if (!f) return '—'
  const [a, m, d] = f.split('-')
  const meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre']
  return `${parseInt(d)} de ${meses[parseInt(m) - 1]} de ${a}`
}

const colorTipo      = (t) => ({ 'Académico':'#0B2545','Cultural':'#F59E0B','Deportivo':'#27AE60','Institucional':'#2563EB' }[t] || '#4F4F4F')
const colorFondoTipo = (t) => ({ 'Académico':'rgba(29,82,183,0.10)','Cultural':'#FEF3C7','Deportivo':'#DCFCE7','Institucional':'#EDE9FE' }[t] || '#F3F4F6')
const estiloBadgeTipo = (t) => ({ background: colorFondoTipo(t), color: colorTipo(t) })

const classBadgeEstatus = (fecha) => fecha >= hoy ? 'estatus-proximo' : 'estatus-finalizado'
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap');

/* ─────────────────────────────────────────────── */
/* BASE                                            */
/* ─────────────────────────────────────────────── */
.eventos-page {
  width: 100%;
  font-family: 'Montserrat', sans-serif;
  padding-bottom: 2rem;
  background: #F4F6F9;
  min-height: 100vh;
}

/* Barra de carga */
.barra-carga { position: fixed; top: 74px; left: 0; right: 0; height: 3px; z-index: 1001; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: linear-gradient(90deg, #0B2545, #1D52B7, #2F80ED, #1D52B7, #0B2545); background-size: 200% 100%; animation: carga-anim 1.4s linear infinite; }
@keyframes carga-anim { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* Breadcrumb */
.breadcrumb { color: #4F4F4F; font-size: 0.875rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem; }
.breadcrumb .sep { color: #C8D0DC; }
.breadcrumb .activo { color: #1D52B7; font-weight: 600; }
.breadcrumb-link { color: #4F4F4F; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #1D52B7; }

/* Encabezado */
.encabezado-seccion { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; }
.titulo-pagina {
  color: #0B2545;
  font-size: 1.85rem;
  font-weight: 800;
  margin: 0 0 0.3rem;
  letter-spacing: -0.02em;
  font-family: 'Montserrat', sans-serif;
}
.subtitulo { color: #6B7280; font-size: 0.9rem; margin: 0; }

/* ─────────────────────────────────────────────── */
/* KPI CARDS — Premium SaaS                        */
/* ─────────────────────────────────────────────── */
.kpis-row { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }

.kpi-card {
  background: linear-gradient(135deg, #ffffff 0%, rgba(29,82,183,.03) 100%);
  border-radius: 14px;
  border: 1px solid rgba(29,82,183,.1);
  box-shadow: 0 10px 30px rgba(29,82,183,.08);
  padding: 1.3rem 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.1rem;
  flex: 1;
  min-width: 160px;
  border-left: 4px solid transparent;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
  cursor: default;
}
.kpi-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 18px 40px rgba(29,82,183,.14);
}
.kpi-azul  { border-left-color: #1D52B7; }
.kpi-verde { border-left-color: #27AE60; }
.kpi-gris  { border-left-color: #9CA3AF; }

.kpi-icono-wrap {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.kpi-icono-azul  { background: rgba(29,82,183,.10); }
.kpi-icono-verde { background: rgba(39,174,96,.10); }
.kpi-icono-gris  { background: rgba(130,130,130,.10); }

.kpi-datos { display: flex; flex-direction: column; gap: 3px; }
.kpi-numero {
  font-size: 2.2rem;
  font-weight: 700;
  color: #0B2545;
  line-height: 1;
  letter-spacing: -0.03em;
}
.kpi-label {
  font-size: 0.68rem;
  font-weight: 700;
  color: #9CA3AF;
  text-transform: uppercase;
  letter-spacing: 0.07em;
}

/* ─────────────────────────────────────────────── */
/* BARRA DE BÚSQUEDA — Protagonista                */
/* ─────────────────────────────────────────────── */
.filtros-card {
  background: #FFFFFF;
  border-radius: 14px;
  border: 1px solid rgba(29,82,183,.1);
  box-shadow: 0 4px 16px rgba(29,82,183,.07);
  padding: 1rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 1.75rem;
  flex-wrap: wrap;
}

.busqueda-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #F4F6F9;
  border: 1.5px solid #E4E9F0;
  border-radius: 10px;
  padding: 0 14px;
  flex: 1;
  min-width: 280px;
  height: 46px;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}
.busqueda-wrap:focus-within {
  border-color: #2F80ED;
  background: #FFFFFF;
  box-shadow: 0 0 0 4px rgba(47,128,237,.12);
}
.icono-busqueda { color: #9CA3AF; flex-shrink: 0; }
.input-busqueda {
  border: none;
  background: transparent;
  padding: 0;
  font-size: 0.9rem;
  font-family: inherit;
  outline: none;
  flex: 1;
  color: #0B2545;
  font-weight: 500;
}
.input-busqueda::placeholder { color: #9CA3AF; font-weight: 400; }
.btn-limpiar { background: none; border: none; color: #9CA3AF; cursor: pointer; padding: 4px; border-radius: 4px; display: flex; align-items: center; }
.btn-limpiar:hover { background: #E4E9F0; color: #374151; }

.btn-filtros-premium {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #FFFFFF;
  color: #0B2545;
  border: 1.5px solid #C8D3E8;
  padding: 0 18px;
  height: 46px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.875rem;
  font-family: inherit;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s ease;
  box-shadow: 0 2px 6px rgba(11,37,69,.06);
}
.btn-filtros-premium:hover {
  background: #F4F6F9;
  border-color: #1D52B7;
  color: #1D52B7;
  box-shadow: 0 4px 12px rgba(29,82,183,.12);
}

/* ─────────────────────────────────────────────── */
/* SECCIÓN TÍTULOS                                 */
/* ─────────────────────────────────────────────── */
.seccion-titulo-wrapper { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.seccion-titulo {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 1rem;
  font-weight: 700;
  color: #0B2545;
  margin: 0;
  letter-spacing: -0.01em;
  font-family: 'Montserrat', sans-serif;
}
.seccion-contador {
  font-size: 0.78rem;
  color: #6B7280;
  background: #F4F6F9;
  border: 1px solid #E4E9F0;
  padding: 4px 12px;
  border-radius: 20px;
  font-weight: 600;
}

/* ─────────────────────────────────────────────── */
/* EVENTO CARDS PRÓXIMOS                           */
/* ─────────────────────────────────────────────── */
.eventos-proximos-grid { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1rem; }

.evento-card {
  background: #FFFFFF;
  border-radius: 14px;
  border: 1px solid rgba(29,82,183,.08);
  box-shadow: 0 4px 16px rgba(29,82,183,.07);
  padding: 1.4rem 1.6rem;
  display: flex;
  align-items: center;
  gap: 1.4rem;
  transition: all .25s ease;
}
.evento-card:hover {
  box-shadow: 0 12px 32px rgba(29,82,183,.12);
  transform: translateY(-4px);
  border-color: rgba(29,82,183,.15);
}

.evento-card-izq { flex-shrink: 0; }
.stat-icono-wrapper {
  width: 52px;
  height: 52px;
  border-radius: 13px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.evento-card-cuerpo { flex: 1; min-width: 0; }
.evento-card-superior { display: flex; align-items: center; gap: 10px; margin-bottom: 0.6rem; flex-wrap: wrap; }
.evento-nombre { font-size: 1rem; font-weight: 700; color: #0B2545; }
.badge-tipo { font-size: 0.73rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.evento-card-meta { display: flex; align-items: center; gap: 1.2rem; flex-wrap: wrap; }
.meta-item { display: flex; align-items: center; gap: 5px; font-size: 0.82rem; color: #6B7280; }
.descripcion-resumida { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.evento-card-acciones { flex-shrink: 0; display: flex; flex-direction: column; gap: 8px; }
.btn-card-accion { width: 100%; justify-content: center; }

/* ─────────────────────────────────────────────── */
/* EMPTY STATE — Rediseño moderno                  */
/* ─────────────────────────────────────────────── */
.estado-vacio {
  background: linear-gradient(135deg, #ffffff 0%, rgba(47,128,237,.04) 100%);
  border-radius: 20px;
  border: 1.5px dashed rgba(29,82,183,.2);
  padding: 4rem 2rem;
  text-align: center;
  margin-bottom: 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0;
  position: relative;
  overflow: hidden;
}
.estado-vacio-decoracion {
  position: relative;
  width: 100px;
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
}
.estado-vacio-circulo-exterior {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  background: rgba(29,82,183,.06);
}
.estado-vacio-circulo-interior {
  position: absolute;
  inset: 16px;
  border-radius: 50%;
  background: rgba(29,82,183,.08);
}
.estado-vacio-icono-wrap {
  position: relative;
  z-index: 1;
  width: 68px;
  height: 68px;
  border-radius: 50%;
  background: #FFFFFF;
  border: 1.5px solid rgba(29,82,183,.15);
  box-shadow: 0 8px 24px rgba(29,82,183,.12);
  display: flex;
  align-items: center;
  justify-content: center;
}
.vacio-titulo { font-size: 1.1rem; font-weight: 800; color: #0B2545; margin: 0 0 0.5rem; }
.vacio-subtitulo { font-size: 0.88rem; color: #6B7280; margin: 0 0 1.5rem; }
.vacio-cta { display: flex; gap: 0.75rem; flex-wrap: wrap; justify-content: center; }

/* ─────────────────────────────────────────────── */
/* TABLA                                           */
/* ─────────────────────────────────────────────── */
.tabla-card {
  background: #FFFFFF;
  border-radius: 14px;
  border: 1px solid rgba(29,82,183,.08);
  box-shadow: 0 4px 16px rgba(29,82,183,.07);
  overflow: hidden;
  margin-bottom: 1.5rem;
}
.tabla-encabezado {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #EEF1F6;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.tabla-contador { font-size: 0.8rem; color: #6B7280; font-weight: 600; }
.tabla-scroll { overflow-x: auto; }
.tabla-principal { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.tabla-principal thead tr { background: #F8FAFC; }
.tabla-principal th {
  padding: 0.85rem 1rem;
  text-align: left;
  font-size: 0.72rem;
  font-weight: 700;
  color: #9CA3AF;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  border-bottom: 1px solid #EEF1F6;
  white-space: nowrap;
}
.tabla-principal td {
  padding: 0.9rem 1rem;
  border-bottom: 1px solid #F4F6F9;
  vertical-align: middle;
}
.tabla-principal tbody tr:hover { background: #F8FAFC; }
.tabla-principal tbody tr:last-child td { border-bottom: none; }
.texto-principal { font-weight: 600; color: #0B2545; }
.texto-secundario { color: #6B7280; }
.texto-corto { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.centrado { text-align: center; }
.badge-estado { font-size: 0.73rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; white-space: nowrap; }
.sin-resultados { text-align: center; padding: 3rem; color: #9CA3AF; font-size: 0.9rem; }
.sin-resultados p { margin: 0.75rem 0 0; }

/* Acciones fila */
.acciones-fila { display: flex; align-items: center; justify-content: center; gap: 6px; }
.btn-accion {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s, transform 0.15s;
  flex-shrink: 0;
}
.btn-accion:hover { transform: scale(1.1); }
.btn-accion.ver     { background: rgba(29,82,183,.08);  color: #1D52B7; }
.btn-accion.ver:hover     { background: rgba(29,82,183,.15); }
.btn-accion.editar  { background: rgba(39,174,96,.08);  color: #27AE60; }
.btn-accion.editar:hover  { background: rgba(39,174,96,.15); }
.btn-accion.eliminar { background: rgba(235,87,87,.08); color: #EB5757; }
.btn-accion.eliminar:hover { background: rgba(235,87,87,.15); }

/* Paginación */
.paginacion-container {
  padding: 1rem 1.25rem;
  border-top: 1px solid #EEF1F6;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 0.75rem;
}
.paginacion-info { font-size: 0.8rem; color: #6B7280; font-weight: 600; }
.paginacion-controles { display: flex; align-items: center; gap: 6px; }
.btn-pag {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  border: 1px solid #E4E9F0;
  background: #FFFFFF;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4F4F4F;
  transition: all 0.2s;
}
.btn-pag:hover:not(:disabled) { background: #F4F6F9; border-color: #C8D3E8; }
.btn-pag:disabled { opacity: 0.35; cursor: not-allowed; }
.paginacion-numeros { display: flex; align-items: center; gap: 4px; }
.btn-num {
  min-width: 34px;
  height: 34px;
  border-radius: 8px;
  border: 1px solid #E4E9F0;
  background: #FFFFFF;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 600;
  font-family: inherit;
  color: #4F4F4F;
  transition: all 0.2s;
  padding: 0 8px;
}
.btn-num:hover { background: #F4F6F9; }
.btn-num.activa { background: linear-gradient(135deg, #0B2545, #1D52B7); color: #FFFFFF; border-color: #1D52B7; }

/* ─────────────────────────────────────────────── */
/* MODAL BASE                                      */
/* ─────────────────────────────────────────────── */
.modal-fondo {
  position: fixed;
  inset: 0;
  background: rgba(11,37,69,.45);
  backdrop-filter: blur(4px);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}
.modal-caja {
  background: #FFFFFF;
  border-radius: 18px;
  box-shadow: 0 24px 64px rgba(11,37,69,.22);
  width: 520px;
  max-width: 96vw;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.modal-caja.modal-ancho { width: 620px; }
.modal-cabecera {
  padding: 1.4rem 1.6rem 1.1rem;
  border-bottom: 1px solid #EEF1F6;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}
.modal-cabecera h3 {
  font-size: 1.05rem;
  font-weight: 800;
  color: #0B2545;
  margin: 0;
  letter-spacing: -0.01em;
}
.btn-cerrar-modal {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  border: 1px solid #E4E9F0;
  background: #F8FAFC;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6B7280;
  transition: all 0.2s;
  flex-shrink: 0;
}
.btn-cerrar-modal:hover { background: #FEF2F2; border-color: #FCA5A5; color: #EB5757; }
.modal-cuerpo {
  padding: 1.4rem 1.6rem;
  overflow-y: auto;
  flex: 1;
}
.modal-pie {
  padding: 1rem 1.6rem;
  border-top: 1px solid #EEF1F6;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.75rem;
  flex-shrink: 0;
  flex-wrap: wrap;
}

/* ─────────────────────────────────────────────── */
/* MODAL: DETALLE DEL EVENTO                       */
/* ─────────────────────────────────────────────── */
.modal-detalle { width: 600px; max-width: 96vw; }
.modal-detalle-cabecera {
  background: linear-gradient(135deg, #0B2545 0%, #1A4184 100%);
  border-bottom: none;
  border-radius: 18px 18px 0 0;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 1.6rem;
}
.detalle-cabecera-izq { display: flex; align-items: center; gap: 1rem; }
.detalle-icono-grande {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  backdrop-filter: blur(4px);
}
.modal-detalle-cabecera .btn-cerrar-modal {
  background: rgba(255,255,255,.15);
  border-color: rgba(255,255,255,.2);
  color: #FFFFFF;
}
.modal-detalle-cabecera .btn-cerrar-modal:hover { background: rgba(255,255,255,.25); }
.detalle-titulo {
  font-size: 1.1rem;
  font-weight: 800;
  color: #FFFFFF;
  line-height: 1.3;
  word-break: break-word;
}
.detalle-badge { font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.detalle-cuerpo { gap: 1.2rem; }
.detalle-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.detalle-meta-item { display: flex; align-items: flex-start; gap: 10px; background: #F8FAFC; border: 1px solid #EEF1F6; border-radius: 10px; padding: 0.85rem 1rem; }
.detalle-meta-icono { width: 32px; height: 32px; background: rgba(29,82,183,.10); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.detalle-meta-etiqueta { margin: 0 0 3px; font-size: 0.72rem; font-weight: 700; color: #9CA3AF; text-transform: uppercase; letter-spacing: 0.04em; }
.detalle-meta-valor { margin: 0; font-size: 0.88rem; font-weight: 600; color: #0B2545; }
.badge-estatus { display: inline-block; font-size: 0.78rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.estatus-proximo    { background: #DCFCE7; color: #27AE60; }
.estatus-finalizado { background: #F3F4F6; color: #4F4F4F; }
.detalle-separador { height: 1px; background: #EEF1F6; margin: 0.25rem 0; }
.detalle-descripcion-bloque { display: flex; flex-direction: column; gap: 6px; }
.detalle-seccion-label { display: flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 700; color: #0B2545; margin: 0; text-transform: uppercase; letter-spacing: 0.04em; }
.detalle-descripcion-texto { margin: 0; font-size: 0.88rem; color: #374151; line-height: 1.6; background: #F8FAFC; border: 1px solid #EEF1F6; border-radius: 10px; padding: 1rem; }
.detalle-descripcion-vacio { margin: 0; font-size: 0.85rem; color: #9CA3AF; font-style: italic; }
.detalle-pie { justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; }
.detalle-pie-derecha { display: flex; gap: 0.5rem; align-items: center; }

/* Botón eliminar */
.btn-eliminar {
  background: #FEF2F2;
  color: #EB5757;
  border: 1px solid #FCA5A5;
  padding: 10px 16px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.2s;
  white-space: nowrap;
}
.btn-eliminar:hover { background: #FEE2E2; }

/* ─────────────────────────────────────────────── */
/* MODAL: FILTROS AVANZADOS                        */
/* ─────────────────────────────────────────────── */
.modal-filtros { width: 520px; max-width: 92vw; }
.filtros-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }

/* ─────────────────────────────────────────────── */
/* CAMPOS DE FORMULARIO                            */
/* ─────────────────────────────────────────────── */
.campos-grid-modal { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; }
.campo-form { display: flex; flex-direction: column; gap: 6px; }
.campo-ancho { grid-column: 1 / -1; }
.campo-label { font-size: 0.82rem; font-weight: 700; color: #374151; }
.requerido { color: #EB5757; }
.campo-input { padding: 10px 14px; border: 1.5px solid #E4E9F0; border-radius: 9px; font-size: 0.875rem; font-family: inherit; color: #0B2545; outline: none; background: #FFFFFF; transition: border-color 0.2s, box-shadow 0.2s; }
.campo-input:focus { border-color: #2F80ED; background: #FFFFFF; box-shadow: 0 0 0 4px rgba(47,128,237,.12); }
.campo-input.campo-error { border-color: #EB5757; }
.campo-textarea { resize: vertical; min-height: 80px; }
.mensaje-error { font-size: 0.78rem; color: #EB5757; font-weight: 500; }

/* ─────────────────────────────────────────────── */
/* BOTONES ESTANDARIZADOS                          */
/* ─────────────────────────────────────────────── */
.btn-primario {
  background: linear-gradient(135deg, #0B2545, #1D52B7);
  color: #FFFFFF;
  border: none;
  padding: 10px 20px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  font-family: inherit;
  transition: opacity 0.2s, box-shadow 0.2s;
  white-space: nowrap;
  box-shadow: 0 4px 12px rgba(29,82,183,.25);
}
.btn-primario:hover:not(:disabled) { opacity: 0.9; box-shadow: 0 6px 18px rgba(29,82,183,.35); }
.btn-primario:disabled { background: #E0E0E0; color: #9CA3AF; cursor: not-allowed; box-shadow: none; }

.btn-secundario {
  background: rgba(29,82,183,.08);
  color: #0B2545;
  border: 1px solid rgba(29,82,183,.15);
  padding: 10px 16px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.2s, border-color 0.2s;
  white-space: nowrap;
}
.btn-secundario:hover:not(:disabled) { background: rgba(29,82,183,.14); border-color: rgba(29,82,183,.25); }
.btn-secundario:disabled { opacity: 0.45; cursor: not-allowed; }

.btn-cancelar {
  background: #FFFFFF;
  color: #4F4F4F;
  border: 1.5px solid #E4E9F0;
  padding: 10px 1.4rem;
  border-radius: 9px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.2s, border-color 0.2s;
}
.btn-cancelar:hover { background: #F4F6F9; border-color: #C8D3E8; }

.spinner { width: 16px; height: 16px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.3); border-top-color: #FFFFFF; animation: spin 0.7s linear infinite; flex-shrink: 0; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ─────────────────────────────────────────────── */
/* TOAST                                           */
/* ─────────────────────────────────────────────── */
.toast { position: fixed; bottom: 2rem; right: 2rem; padding: 0.9rem 1.4rem; border-radius: 12px; font-weight: 700; font-size: 0.9rem; font-family: 'Montserrat', sans-serif; display: flex; align-items: center; gap: 0.6rem; box-shadow: 0 12px 30px rgba(0,0,0,.18); z-index: 3000; max-width: 380px; color: #FFFFFF; }
.toast.exito { background: linear-gradient(135deg, #0B2545, #1A4184); }
.toast.error { background: #EB5757; }
.toast.info  { background: #2563EB; }
.toast-slide-enter-active { transition: all 0.3s ease; }
.toast-slide-leave-active { transition: all 0.25s ease; }
.toast-slide-enter-from { transform: translateY(20px); opacity: 0; }
.toast-slide-leave-to   { transform: translateX(100%); opacity: 0; }

/* Transiciones de modal */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

/* Pie de página */
.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem; }

/* ─────────────────────────────────────────────── */
/* RESPONSIVE                                      */
/* ─────────────────────────────────────────────── */
@media (max-width: 640px) {
  .filtros-card { flex-direction: column; align-items: stretch; }
  .busqueda-wrap { min-width: auto; }
  .btn-filtros-premium { width: 100%; justify-content: center; }

  .evento-card { flex-direction: column; align-items: flex-start; }
  .evento-card-acciones { width: 100%; flex-direction: row; }
  .btn-card-accion { flex: 1; }

  .modal-caja,
  .modal-caja.modal-ancho,
  .modal-detalle { width: 100%; max-width: 100%; margin: 0; border-radius: 16px; }

  .modal-cabecera,
  .modal-cuerpo,
  .modal-pie { padding: 1rem; }

  .campos-grid-modal,
  .filtros-grid { grid-template-columns: 1fr; gap: 1rem; }

  .modal-filtros { width: 92vw; }

  .detalle-meta-grid { grid-template-columns: 1fr; }
  .modal-detalle-cabecera { flex-direction: column; gap: 0.75rem; }
  .detalle-pie { flex-direction: column; align-items: stretch; }
  .detalle-pie-derecha { justify-content: flex-end; }

  .kpis-row { gap: 0.75rem; }
  .kpi-card { min-width: 140px; }

  .encabezado-seccion { flex-direction: column; gap: 1rem; align-items: flex-start; }
}
</style>