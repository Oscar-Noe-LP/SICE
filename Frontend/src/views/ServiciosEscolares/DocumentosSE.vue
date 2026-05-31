<template>
  <MainLayout>
    <div class="doc-wrap">

      <!-- ═══ TOAST ═══ -->
      <Transition name="toast">
        <div v-if="toast.visible" :class="['toast', `toast--${toast.type}`]">
          <svg v-if="toast.type==='success'" viewBox="0 0 20 20" fill="none" width="16" height="16"><circle cx="10" cy="10" r="9" stroke="currentColor" stroke-width="1.5"/><path d="M6 10l3 3 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <svg v-else-if="toast.type==='error'" viewBox="0 0 20 20" fill="none" width="16" height="16"><circle cx="10" cy="10" r="9" stroke="currentColor" stroke-width="1.5"/><path d="M7 7l6 6M13 7l-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
          <svg v-else viewBox="0 0 20 20" fill="none" width="16" height="16"><circle cx="10" cy="10" r="9" stroke="currentColor" stroke-width="1.5"/><path d="M10 6v5M10 14v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
          {{ toast.message }}
        </div>
      </Transition>

      <!-- ═══ MODAL VISTA PREVIA ═══ -->
      <Transition name="modal">
        <div v-if="modalVisible" class="modal-overlay" @click.self="cerrarModal">
          <div class="modal">
            <div class="modal__hdr">
              <h3>VISTA PREVIA — {{ moduloActual.titulo }}</h3>
              <button class="icon-btn" @click="cerrarModal" aria-label="Cerrar">
                <svg viewBox="0 0 20 20" fill="none" width="18" height="18"><path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
              </button>
            </div>
            <div class="modal__body">
              <div v-if="cargandoPrevia" class="modal__spinner">
                <svg class="spin" viewBox="0 0 44 44" width="40" height="40"><circle cx="22" cy="22" r="18" fill="none" stroke="#1D52B7" stroke-width="3" stroke-dasharray="90 60" stroke-linecap="round"/></svg>
                <span>GENERANDO DOCUMENTO...</span>
              </div>
              <iframe v-else-if="urlPrevia" :src="urlPrevia" class="modal__iframe"/>
              <div v-else class="doc-mock">
                <div class="doc-mock__logo"></div>
                <div class="doc-mock__lines">
                  <div class="doc-mock__line" style="width:70%;height:14px"></div>
                  <div class="doc-mock__line" style="width:50%"></div>
                  <div class="doc-mock__line" style="width:100%"></div>
                  <div class="doc-mock__line" style="width:100%"></div>
                  <div class="doc-mock__line" style="width:80%"></div>
                </div>
                <div class="doc-mock__sello"></div>
              </div>
            </div>
            <div class="modal__ftr">
              <button class="btn btn--ghost" @click="cerrarModal">CERRAR</button>
              <button class="btn btn--secondary" @click="imprimirDocumento">
                <svg viewBox="0 0 20 20" fill="none" width="15" height="15"><rect x="4" y="7" width="12" height="8" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M7 7V4h6v3M7 13h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                IMPRIMIR
              </button>
              <button class="btn btn--primary" :style="{ background: moduloActual.color }" @click="descargarDocumento">
                <svg viewBox="0 0 20 20" fill="none" width="15" height="15"><path d="M10 3v9m0 0l-3-3m3 3l3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 14v1a2 2 0 002 2h8a2 2 0 002-2v-1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                DESCARGAR PDF
              </button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- ═══ PÁGINA ═══ -->
      <section class="documentos-page" :style="{ '--mod-color': moduloActual.color, '--mod-rgb': moduloActual.rgb }">

        <!-- BREADCRUMB -->
        <div class="page-breadcrumb">
          <span>SERVICIOS ESCOLARES</span>
          <svg viewBox="0 0 16 16" fill="none" width="12" height="12"><path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <span>DOCUMENTOS</span>
          <svg viewBox="0 0 16 16" fill="none" width="12" height="12"><path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <span class="breadcrumb-active">{{ moduloActual.titulo }}</span>
        </div>

        <!-- HEADER -->
        <div class="page-header">
          <div class="page-header__left">
            <h1 class="page-title">{{ moduloActual.titulo }}</h1>
            <p v-if="busquedaRealizada" class="page-subtitle">
              {{ resultadosFiltrados.length }} REGISTRO{{ resultadosFiltrados.length !== 1 ? 'S' : '' }} ENCONTRADO{{ resultadosFiltrados.length !== 1 ? 'S' : '' }}
            </p>
          </div>
        </div>

        <!-- ── BARRA BÚSQUEDA + FILTROS ── -->
        <div class="search-filters-bar">
          <div class="search-wrap">
            <svg viewBox="0 0 20 20" fill="none" width="16" height="16" class="search-wrap__icon">
              <circle cx="8.5" cy="8.5" r="5.5" stroke="currentColor" stroke-width="1.8"/>
              <path d="M16 16l-3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            <input
              ref="inputRef"
              v-model="textoBusqueda"
              class="search-input"
              :placeholder="moduloActual.placeholder"
              @keydown.enter="ejecutarBusqueda"
            />
            <button
              v-if="textoBusqueda"
              class="search-clear"
              @click="limpiarBusqueda"
              title="Limpiar"
            >
              <svg viewBox="0 0 20 20" fill="none" width="12" height="12"><path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
          </div>

          <!-- Filtros inline (client-side sobre resultados ya cargados) -->
          <div class="inline-filters">

            <!-- Filtro CARRERA (dinámico desde /api/carreras) -->
            <div v-if="getFiltrosDef().includes('carrera')" class="inline-filter">
              <div class="select-wrap">
                <select v-model="filtros.carrera" class="select-control" title="CARRERA">
                  <option value="">TODAS LAS CARRERAS</option>
                  <option
                    v-for="c in catalogoCarreras"
                    :key="c.id_carrera ?? c.clave_carrera ?? c.nombre_carrera"
                    :value="c.nombre_carrera"
                  >
                    {{ c.nombre_carrera }}
                  </option>
                </select>
                <svg viewBox="0 0 16 16" fill="none" width="11" height="11" class="select-wrap__arrow">
                  <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
            </div>

            <!-- Filtro SEMESTRE (estático 1-10) -->
            <div v-if="getFiltrosDef().includes('semestre')" class="inline-filter">
              <div class="select-wrap">
                <select v-model="filtros.semestre" class="select-control" title="SEMESTRE">
                  <option value="">TODOS LOS SEMESTRES</option>
                  <option v-for="n in 10" :key="n" :value="String(n)">{{ n }}° SEMESTRE</option>
                </select>
                <svg viewBox="0 0 16 16" fill="none" width="11" height="11" class="select-wrap__arrow">
                  <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
            </div>

            <!-- Filtro ESTATUS (estático) -->
            <div v-if="getFiltrosDef().includes('estatus')" class="inline-filter">
              <div class="select-wrap">
                <select v-model="filtros.estatus" class="select-control" title="ESTATUS">
                  <option value="">TODOS LOS ESTATUS</option>
                  <option value="ACTIVO">ACTIVO</option>
                  <option value="EGRESADO">EGRESADO</option>
                  <option value="BAJA">BAJA</option>
                  <option value="IRREGULAR">IRREGULAR</option>
                </select>
                <svg viewBox="0 0 16 16" fill="none" width="11" height="11" class="select-wrap__arrow">
                  <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
            </div>

          </div>

          <!-- Acciones -->
          <div class="search-actions">
            <button
              v-if="filtrosActivos > 0"
              class="btn btn--ghost btn--icon"
              @click="limpiarFiltros"
              title="Limpiar filtros"
            >
              <svg viewBox="0 0 20 20" fill="none" width="14" height="14"><path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </button>
            <button
              class="btn btn--primary"
              :style="{ background: moduloActual.color }"
              @click="ejecutarBusqueda"
              :disabled="buscando || textoBusqueda.trim().length < 2"
            >
              <svg v-if="buscando" class="spin" viewBox="0 0 44 44" width="13" height="13"><circle cx="22" cy="22" r="18" fill="none" stroke="white" stroke-width="3" stroke-dasharray="90 60" stroke-linecap="round"/></svg>
              <svg v-else viewBox="0 0 20 20" fill="none" width="14" height="14"><circle cx="8.5" cy="8.5" r="5.5" stroke="white" stroke-width="1.8"/><path d="M16 16l-3-3" stroke="white" stroke-width="1.8" stroke-linecap="round"/></svg>
              BUSCAR
            </button>
          </div>
        </div>

        <!-- ── EMISIÓN MASIVA (boletas) ── -->
        <div v-if="submodulo === 'boletas' && modoBoletaMasiva" class="selected-panel selected-panel--massive">
          <div class="selected-panel__main">
            <span class="selected-panel__eyebrow">EMISIÓN MASIVA</span>
            <h2 class="selected-panel__title">BOLETAS POR GRUPO</h2>
            <div class="selected-grid selected-grid--compact">
              <div><span>CARRERA</span><strong>{{ filtros.carrera || 'Todas' }}</strong></div>
              <div><span>SEMESTRE</span><strong>{{ filtros.semestre ? `${filtros.semestre}°` : 'Todos' }}</strong></div>
              <div>
                <span>PERIODO ESCOLAR</span>
                <div class="select-wrap selected-period">
                  <select v-model="periodoSeleccionado" class="select-control select-control--sm">
                    <option value="">SELECCIONAR PERIODO</option>
                    <option
                      v-for="p in catalogoPeriodos"
                      :key="p.id_periodo ?? p.clave_periodo ?? p.nombre_periodo"
                      :value="p.clave_periodo ?? String(p.id_periodo)"
                    >
                      {{ p.nombre_periodo }}
                    </option>
                  </select>
                  <svg viewBox="0 0 16 16" fill="none" width="11" height="11" class="select-wrap__arrow"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
              </div>
            </div>
          </div>
          <div class="selected-actions">
            <button class="btn btn--primary" :style="{ background: moduloActual.color }" @click="descargarDocumento" :disabled="procesando || !periodoSeleccionado">
              <svg v-if="procesando" class="spin" viewBox="0 0 44 44" width="14" height="14"><circle cx="22" cy="22" r="18" fill="none" stroke="white" stroke-width="3" stroke-dasharray="90 60" stroke-linecap="round"/></svg>
              GENERAR ZIP MASIVO
            </button>
          </div>
        </div>

        <template v-else>

          <!-- ESTADO: BUSCANDO -->
          <div v-if="buscando" class="spinner-row">
            <svg class="spin" viewBox="0 0 44 44" width="28" height="28">
              <circle cx="22" cy="22" r="18" fill="none" stroke="var(--mod-color)" stroke-width="3" stroke-dasharray="90 60" stroke-linecap="round"/>
            </svg>
            <span>BUSCANDO...</span>
          </div>

          <!-- ESTADO: SIN RESULTADOS -->
          <div v-else-if="busquedaRealizada && resultadosFiltrados.length === 0 && !buscando" class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" width="28" height="28" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <h3>SIN RESULTADOS</h3>
            <p>INTENTA CON OTRO NÚMERO DE CONTROL O NOMBRE.</p>
          </div>

          <!-- GRID DE CARDS DE ALUMNOS -->
          <div v-else-if="busquedaRealizada && resultadosFiltrados.length > 0" class="students-grid">
            <div
              v-for="al in resultadosFiltrados"
              :key="al.numero_control || al.noControl"
              class="student-card"
              :class="{ 'student-card--selected': alumnoSeleccionado && (alumnoSeleccionado.numero_control || alumnoSeleccionado.noControl) === (al.numero_control || al.noControl) }"
              :style="{ '--card-color': moduloActual.color }"
              @click="seleccionarAlumno(al)"
            >
              <div class="student-card__top">
                <div class="student-avatar" :style="{ background: moduloActual.color }">
                  {{ iniciales(al.nombre_completo || al.nombre || '') }}
                </div>
                <div class="student-meta">
                  <span class="student-control">{{ al.numero_control || al.noControl }}</span>
                  <span :class="`badge badge--${badgeColor(al.estatus)}`">{{ al.estatus || 'ACTIVO' }}</span>
                </div>
              </div>
              <div class="student-name">{{ al.nombre_completo || al.nombre }}</div>
              <div class="student-career">{{ al.carrera || '—' }}</div>
              <div class="student-footer">
                <div class="student-detail">
                  <svg viewBox="0 0 16 16" fill="none" width="12" height="12"><path d="M8 2C5.79 2 4 3.79 4 6c0 3 4 8 4 8s4-5 4-8c0-2.21-1.79-4-4-4zm0 5.5A1.5 1.5 0 118 5a1.5 1.5 0 010 3z" fill="currentColor" opacity=".5"/></svg>
                  Semestre {{ al.semestre_actual || al.semestre || '—' }}
                </div>
                <button
                  class="btn-select"
                  :style="{ background: moduloActual.color }"
                  @click.stop="seleccionarAlumno(al)"
                >
                  SELECCIONAR
                </button>
              </div>
            </div>
          </div>

          <!-- ── PANEL ALUMNO SELECCIONADO ── -->
          <div v-if="alumnoSeleccionado" class="selected-panel">
            <!-- Avatar + datos principales -->
            <div class="selected-panel__hero">
              <div class="selected-avatar" :style="{ background: moduloActual.color }">
                {{ iniciales(alumnoSeleccionado.nombre_completo || alumnoSeleccionado.nombre || '') }}
              </div>
              <div class="selected-info">
                <span class="selected-panel__eyebrow">ALUMNO SELECCIONADO</span>
                <h2 class="selected-panel__title">{{ alumnoSeleccionado.nombre_completo || alumnoSeleccionado.nombre }}</h2>
                <div class="selected-chips">
                  <span class="chip chip--control">
                    <svg viewBox="0 0 16 16" fill="none" width="10" height="10"><rect x="2" y="2" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M5 8h6M5 5h6M5 11h4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                    {{ alumnoSeleccionado.numero_control || alumnoSeleccionado.noControl }}
                  </span>
                  <span class="chip chip--carrera">{{ alumnoSeleccionado.carrera || '—' }}</span>
                  <span class="chip chip--semestre">{{ alumnoSeleccionado.semestre_actual || alumnoSeleccionado.semestre || '—' }}° SEM.</span>
                  <span :class="`badge badge--${badgeColor(alumnoSeleccionado.estatus)}`">
                    {{ alumnoSeleccionado.estatus || 'ACTIVO' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Selectores de periodo / tipo según submódulo -->
            <div
              class="selected-config"
              v-if="submodulo === 'constancias' || submodulo === 'boletas' || submodulo === 'cargas'"
            >
              <!-- PERIODO (dinámico desde /api/periodos) -->
              <div class="config-field">
                <label class="config-label">PERIODO ESCOLAR</label>
                <div class="select-wrap">
                  <select v-model="periodoSeleccionado" class="select-control">
                    <option value="">SELECCIONAR PERIODO</option>
                    <option
                      v-for="p in catalogoPeriodos"
                      :key="p.id_periodo ?? p.clave_periodo ?? p.nombre_periodo"
                      :value="p.clave_periodo ?? String(p.id_periodo)"
                    >
                      {{ p.nombre_periodo }}
                    </option>
                  </select>
                  <svg viewBox="0 0 16 16" fill="none" width="11" height="11" class="select-wrap__arrow"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
              </div>

              <!-- TIPO CONSTANCIA -->
              <div v-if="submodulo === 'constancias'" class="config-field">
                <label class="config-label">TIPO DE CONSTANCIA</label>
                <div class="select-wrap">
                  <select v-model="tipoConstancia" class="select-control">
                    <option v-for="t in TIPOS_CONSTANCIA" :key="t.value" :value="t.value">{{ t.label }}</option>
                  </select>
                  <svg viewBox="0 0 16 16" fill="none" width="11" height="11" class="select-wrap__arrow"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
              </div>

              <!-- MODALIDAD BOLETA -->
              <div v-if="submodulo === 'boletas'" class="config-field">
                <label class="config-label">MODALIDAD</label>
                <div class="select-wrap">
                  <select v-model="modoBoletaMasiva" class="select-control">
                    <option :value="false">INDIVIDUAL</option>
                    <option :value="true">MASIVA</option>
                  </select>
                  <svg viewBox="0 0 16 16" fill="none" width="11" height="11" class="select-wrap__arrow"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
              </div>
            </div>

            <!-- Datos residencia (submódulo residencia, no tiene ruta PDF en api.php) -->
            <div v-if="submodulo === 'residencia' && residencias.length > 0" class="residencia-box">
              <strong>PROYECTO DE RESIDENCIA</strong>
              <span v-for="r in residencias" :key="r.id">{{ r.empresa }} ({{ r.periodo }})</span>
            </div>

            <!-- Aviso si el submódulo no tiene generación PDF -->
            <div v-if="!tieneRutaPDF" class="info-banner">
              <svg viewBox="0 0 20 20" fill="none" width="14" height="14"><circle cx="10" cy="10" r="9" stroke="currentColor" stroke-width="1.5"/><path d="M10 6v5M10 14v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
              LA GENERACIÓN DE PDF PARA ESTE MÓDULO AÚN NO ESTÁ DISPONIBLE.
            </div>

            <!-- Toolbar de acciones -->
            <div class="selected-actions">
              <button
                v-if="tieneRutaPDF"
                class="btn btn--secondary"
                @click="verPrevia"
                :disabled="procesando || (requierePeriodo && !periodoSeleccionado)"
              >
                <svg viewBox="0 0 20 20" fill="none" width="14" height="14"><path d="M1 10s4-7 9-7 9 7 9 7-4 7-9 7-9-7-9-7z" stroke="currentColor" stroke-width="1.5"/><circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="1.5"/></svg>
                VISTA PREVIA
              </button>
              <button
                v-if="tieneRutaPDF"
                class="btn btn--secondary"
                @click="imprimirDocumento"
                :disabled="procesando || (requierePeriodo && !periodoSeleccionado)"
              >
                <svg viewBox="0 0 20 20" fill="none" width="14" height="14"><rect x="4" y="7" width="12" height="8" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M7 7V4h6v3M7 13h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                IMPRIMIR
              </button>
              <button
                v-if="tieneRutaPDF"
                class="btn btn--primary"
                :style="{ background: moduloActual.color }"
                @click="descargarDocumento"
                :disabled="procesando || (requierePeriodo && !periodoSeleccionado)"
              >
                <svg v-if="procesando" class="spin" viewBox="0 0 44 44" width="14" height="14"><circle cx="22" cy="22" r="18" fill="none" stroke="white" stroke-width="3" stroke-dasharray="90 60" stroke-linecap="round"/></svg>
                <svg v-else viewBox="0 0 20 20" fill="none" width="14" height="14"><path d="M10 3v9m0 0l-3-3m3 3l3-3" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 14v1a2 2 0 002 2h8a2 2 0 002-2v-1" stroke="white" stroke-width="1.5" stroke-linecap="round"/></svg>
                DESCARGAR PDF
              </button>
            </div>
          </div>

        </template>

      </section>
    </div>
      <!-- Agregar botón de exportación CSV en el panel de selección -->
  <div class="selected-actions">
    <button
      v-if="tieneRutaPDF"
      class="btn btn--secondary"
      @click="verPrevia"
      :disabled="procesando || (requierePeriodo && !periodoSeleccionado)"
    >
      <svg viewBox="0 0 20 20" fill="none" width="14" height="14"><path d="M1 10s4-7 9-7 9 7 9 7-4 7-9 7-9-7-9-7z" stroke="currentColor" stroke-width="1.5"/><circle cx="10" cy="10" r="3" stroke="currentColor" stroke-width="1.5"/></svg>
      VISTA PREVIA
    </button>
    
    <!-- NUEVO BOTÓN CSV -->
    <button
      v-if="resultadosFiltrados.length > 0 && submodulo !== 'actas'"
      class="btn btn--secondary"
      @click="descargarCSV(resultadosFiltrados, moduloActual.titulo)"
      :disabled="procesando"
    >
      <svg viewBox="0 0 20 20" fill="none" width="14" height="14">
        <rect x="3" y="3" width="14" height="14" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
        <path d="M7 8h6M7 12h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
      </svg>
      EXPORTAR CSV
    </button>
    
    <button
      v-if="tieneRutaPDF"
      class="btn btn--secondary"
      @click="imprimirDocumento"
      :disabled="procesando || (requierePeriodo && !periodoSeleccionado)"
    >
      <svg viewBox="0 0 20 20" fill="none" width="14" height="14"><rect x="4" y="7" width="12" height="8" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M7 7V4h6v3M7 13h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
      IMPRIMIR
    </button>
    <button
      v-if="tieneRutaPDF"
      class="btn btn--primary"
      :style="{ background: moduloActual.color }"
      @click="descargarDocumento"
      :disabled="procesando || (requierePeriodo && !periodoSeleccionado)"
    >
      <svg v-if="procesando" class="spin" viewBox="0 0 44 44" width="14" height="14"><circle cx="22" cy="22" r="18" fill="none" stroke="white" stroke-width="3" stroke-dasharray="90 60" stroke-linecap="round"/></svg>
      <svg v-else viewBox="0 0 20 20" fill="none" width="14" height="14"><path d="M10 3v9m0 0l-3-3m3 3l3-3" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 14v1a2 2 0 002 2h8a2 2 0 002-2v-1" stroke="white" stroke-width="1.5" stroke-linecap="round"/></svg>
      DESCARGAR PDF
    </button>
  </div>

  </MainLayout>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const route = useRoute()
const API = import.meta.env.VITE_API_URL || ''

// ─── Utilidades ─────────────────────────────────────────────────────────────

function badgeColor(estatus) {
  const map = { ACTIVO: 'green', EGRESADO: 'blue', BAJA: 'red', IRREGULAR: 'orange' }
  return map[(estatus || '').toUpperCase()] || 'gray'
}

function iniciales(nombre = '') {
  return nombre
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map(p => p[0])
    .join('')
    .toUpperCase()
}

function authHeaders() {
  // FIX BUG 2: si no hay token, NO incluir Authorization.
  // Antes devolvía "Bearer null" → 401 en /api/carreras → select vacío.
  const token = localStorage.getItem('auth_token') || localStorage.getItem('token')
  const headers = { 'Content-Type': 'application/json' }
  if (token) headers['Authorization'] = `Bearer ${token}`
  return headers
}

// ─── Catálogos estáticos ──────────────────────────────────────────────────────

const MODULOS = [
  {
    key: 'constancias',
    titulo: 'CONSTANCIAS',
    color: '#2563EB', rgb: '37,99,235',
    placeholder: 'BUSCAR POR NÚMERO DE CONTROL O NOMBRE',
  },
  {
    key: 'boletas',
    titulo: 'BOLETAS',
    color: '#16A34A', rgb: '22,163,74',
    placeholder: 'NÚMERO DE CONTROL O NOMBRE DEL ESTUDIANTE',
  },
  {
    key: 'certificados',
    titulo: 'CERTIFICADOS',
    color: '#EA580C', rgb: '234,88,12',
    placeholder: 'LOCALIZAR EGRESADO POR NÚMERO DE CONTROL',
  },
  {
    key: 'actas',
    titulo: 'ACTAS DE CALIFICACIÓN',
    color: '#7C3AED', rgb: '124,58,237',
    placeholder: 'BUSCAR POR CLAVE DE ASIGNATURA O GRUPO',
  },
  {
    key: 'cargas',
    titulo: 'CARGAS ACADÉMICAS',
    color: '#0EA5E9', rgb: '14,165,233',
    placeholder: 'MATRÍCULA CON INSCRIPCIÓN AUTORIZADA',
  },
  {
    key: 'residencia',
    titulo: 'ACTA DE RESIDENCIA',
    color: '#EF4444', rgb: '239,68,68',
    placeholder: 'NÚMERO DE CONTROL DEL RESIDENTE',
  },
]

const TIPOS_CONSTANCIA = [
  { value: 'estudios',    label: 'ESTUDIOS' },
  { value: 'inscripcion', label: 'INSCRIPCIÓN' },
  { value: 'promedio',    label: 'PROMEDIO' },
  { value: 'beca',        label: 'BECA' },
  { value: 'visa',        label: 'VISA' },
  { value: 'trabajo',     label: 'TRABAJO' },
  { value: 'imss',        label: 'IMSS' },
]

/**
 * Qué filtros client-side aplican por módulo.
 * Son strings que coinciden con las keys de `filtros`.
 */
const FILTROS_POR_MOD = {
  constancias:  ['carrera', 'semestre', 'estatus'],
  boletas:      ['carrera', 'semestre'],
  certificados: ['carrera'],
  actas:        ['carrera', 'semestre'],
  cargas:       ['carrera', 'semestre'],
  residencia:   ['carrera'],
}

/**
 * Mapa route.name → clave de submódulo.
 * Ajusta los nombres de ruta según tu router.
 */
const ROUTE_MAP = {
  Constancias:      'constancias',
  Boletas:          'boletas',
  Certificados:     'certificados',
  DocumentosActas:  'actas',
  DocumentosCargas: 'cargas',
  ActaResidencia:   'residencia',
}

/**
 * Submódulos que tienen ruta real en api.php:
 *   GET /api/documentos/constancia/{nc}
 *   GET /api/documentos/boleta/{nc}
 *   GET /api/documentos/certificado/{nc}
 *
 * actas / cargas / residencia NO tienen ruta PDF todavía.
 */
const SEGMENTO_PDF = {
  constancias:  'constancia',
  boletas:      'boleta',
  certificados: 'certificado',
}

// ─── Estado ──────────────────────────────────────────────────────────────────

const submodulo           = ref(ROUTE_MAP[route.name] || 'constancias')
const filtros             = reactive({ carrera: '', semestre: '', estatus: '' })
const textoBusqueda       = ref('')
const resultadosBrutos    = ref([])
const buscando            = ref(false)
const busquedaRealizada   = ref(false)
const alumnoSeleccionado  = ref(null)
const procesando          = ref(false)
const modoBoletaMasiva    = ref(false)
const tipoConstancia      = ref('estudios')
const periodoSeleccionado = ref('')
const inputRef            = ref(null)
const modalVisible        = ref(false)
const urlPrevia           = ref(null)
const cargandoPrevia      = ref(false)
const residencias         = ref([])
const toast               = reactive({ visible: false, message: '', type: 'info' })

// Catálogos dinámicos desde el backend
const catalogoPeriodos  = ref([])   // GET /api/periodos
const catalogoCarreras  = ref([])   // GET /api/carreras

let debounceTimer = null

// ─── Computados ──────────────────────────────────────────────────────────────

const moduloActual = computed(() =>
  MODULOS.find(m => m.key === submodulo.value) || MODULOS[0]
)

/**
 * FIX BUG 1: filtrosDef era un computed, y en el template
 * filtrosDef.includes(...) llamaba .includes() sobre el ComputedRef
 * (no sobre el array), devolviendo siempre false → selects nunca visibles.
 *
 * Solución: función pura que lee submodulo.value directamente.
 * El template la llama como getFiltrosDef().includes('carrera') — Vue
 * re-evalúa la expresión reactivamente porque submodulo es un ref.
 */
function getFiltrosDef() {
  return FILTROS_POR_MOD[submodulo.value] ?? []
}

const filtrosActivos = computed(() =>
  getFiltrosDef().filter(k => filtros[k] !== '').length
)

const requierePeriodo = computed(() =>
  ['constancias', 'boletas', 'cargas'].includes(submodulo.value)
)

/** ¿Este submódulo tiene ruta de generación PDF en api.php? */
const tieneRutaPDF = computed(() =>
  submodulo.value in SEGMENTO_PDF
)

/**
 * Filtrado CLIENT-SIDE sobre resultadosBrutos.
 * Los filtros NO disparan una nueva petición al backend.
 * El backend devuelve todos los coincidentes con q=; aquí refinamos.
 */
const resultadosFiltrados = computed(() => {
  let lista = resultadosBrutos.value

  if (filtros.carrera) {
    const f = filtros.carrera.toUpperCase().trim()
    lista = lista.filter(a =>
      String(a.carrera        ?? '').toUpperCase().trim() === f ||
      String(a.clave_carrera  ?? '').toUpperCase().trim() === f ||
      String(a.nombre_carrera ?? '').toUpperCase().trim() === f
    )
  }

  if (filtros.semestre) {
    lista = lista.filter(a =>
      String(a.semestre_actual || a.semestre || '') === filtros.semestre
    )
  }

  if (filtros.estatus) {
    lista = lista.filter(a =>
      String(a.estatus || '').toUpperCase() === filtros.estatus.toUpperCase()
    )
  }

  return lista
})

// ─── Watchers ────────────────────────────────────────────────────────────────

/** Cambio de ruta → resetear todo */
watch(() => route.name, (newName) => {
  if (ROUTE_MAP[newName]) {
    submodulo.value = ROUTE_MAP[newName]
    resetEstado()
  }
})

/**
 * Debounce de búsqueda automática mientras el usuario escribe.
 * - < 2 chars : no hace request, si es 0 limpia resultados
 * - ≥ 2 chars : lanza ejecutarBusqueda tras 450 ms de inactividad
 */
watch(textoBusqueda, (val) => {
  clearTimeout(debounceTimer)
  const texto = val.trim()
  if (texto.length >= 2) {
    debounceTimer = setTimeout(ejecutarBusqueda, 450)
  } else if (texto.length === 0) {
    resultadosBrutos.value   = []
    busquedaRealizada.value  = false
    alumnoSeleccionado.value = null
  }
})

// ─── Ciclo de vida ───────────────────────────────────────────────────────────

onMounted(() => {
  cargarCatalogos()
})

// ─── Funciones ───────────────────────────────────────────────────────────────

function mostrarToast(msg, type = 'info') {
  Object.assign(toast, { visible: true, message: msg, type })
  setTimeout(() => { toast.visible = false }, 3500)
}

function resetEstado() {
  alumnoSeleccionado.value  = null
  textoBusqueda.value       = ''
  resultadosBrutos.value    = []
  busquedaRealizada.value   = false
  modoBoletaMasiva.value    = false
  periodoSeleccionado.value = ''
  tipoConstancia.value      = 'estudios'
  residencias.value         = []
  Object.assign(filtros, { carrera: '', semestre: '', estatus: '' })
}

function limpiarBusqueda() {
  textoBusqueda.value      = ''
  resultadosBrutos.value   = []
  busquedaRealizada.value  = false
  alumnoSeleccionado.value = null
}

/** Limpiar filtros client-side; NO rehace el request al backend */
function limpiarFiltros() {
  // FIX: solo limpia las keys del módulo actual
  getFiltrosDef().forEach(k => { filtros[k] = '' })
}

/**
 * Carga catálogos dinámicos al montar el componente.
 * Rutas reales en api.php:
 *   GET /api/periodos  → PeriodoController::index
 *   GET /api/carreras  → CarreraController::index
 */
async function cargarCatalogos() {
  try {
    const [resPeriodos, resCarreras] = await Promise.all([
      fetch(`${API}/api/periodos`,  { headers: authHeaders() }),
      fetch(`${API}/api/carreras`,  { headers: authHeaders() }),
    ])

    if (resPeriodos.ok) {
      const data = await resPeriodos.json()
      // Soporta {data:[...]} o array directo
      catalogoPeriodos.value = Array.isArray(data) ? data : (data.data ?? [])
    }

    if (resCarreras.ok) {
      const data = await resCarreras.json()
      const raw = Array.isArray(data) ? data : (data.data ?? [])
      // Normalizar: acepta cualquier key que devuelva el CarreraController
      // (nombre_carrera | nombre | carrera | descripcion | clave_carrera)
      catalogoCarreras.value = raw.map(c => ({
        nombre_carrera:
          c.nombre_carrera ??
          c.nombre         ??
          c.carrera        ??
          c.descripcion    ??
          c.clave_carrera  ??
          String(c.id_carrera ?? c.id ?? ''),
        clave_carrera: c.clave_carrera ?? c.clave ?? '',
        id_carrera:    c.id_carrera    ?? c.id    ?? null,
      }))
      console.log('[SICE] catalogoCarreras:', catalogoCarreras.value)
    }
  } catch (err) {
    console.error('cargarCatalogos error:', err)
    // No bloquear la UI si los catálogos fallan
  }
}

/**
 * BÚSQUEDA PRINCIPAL
 * Ruta real en api.php:
 *   GET /api/buscar-alumno?q={texto}
 *   → ServiciosEscolaresController::buscarAlumnoInscripcion
 *
 * Regla: mínimo 2 caracteres para lanzar el request.
 */
async function ejecutarBusqueda() {
  const texto = textoBusqueda.value.trim()

  if (texto.length < 2) {
    if (texto.length > 0) {
      mostrarToast('INGRESA AL MENOS 2 CARACTERES PARA BUSCAR.', 'info')
    }
    return
  }

  buscando.value          = true
  busquedaRealizada.value = true

  try {
    const params = new URLSearchParams({ q: texto })
    const res = await fetch(`${API}/api/buscar-alumno?${params}`, {
      headers: authHeaders(),
    })

    if (!res.ok) {
      const err = await res.json().catch(() => ({}))
      throw new Error(err.error || err.message || `HTTP ${res.status}`)
    }

    const data = await res.json()
    // El backend puede devolver array directo o {data:[...]}
    resultadosBrutos.value   = Array.isArray(data) ? data : (data.data ?? [])
    alumnoSeleccionado.value = null

  } catch (error) {
    console.error('buscarAlumno error:', error)
    mostrarToast(error.message || 'ERROR AL BUSCAR ALUMNO', 'error')
    resultadosBrutos.value = []
  } finally {
    buscando.value = false
  }
}

function seleccionarAlumno(alumno) {
  alumnoSeleccionado.value = alumno
  if (submodulo.value === 'residencia') {
    nextTick(() => cargarResidencias())
  }
}

async function cargarResidencias() {
  if (!alumnoSeleccionado.value) return
  try {
    const nc  = alumnoSeleccionado.value.numero_control || alumnoSeleccionado.value.noControl
    // Nota: /api/residencias/{nc} no está en api.php; si se agrega en el futuro
    // esta llamada funcionará automáticamente.
    const res = await fetch(`${API}/api/residencias/${nc}`, { headers: authHeaders() })
    if (!res.ok) throw new Error()
    residencias.value = await res.json()
  } catch {
    residencias.value = []
  }
}

function cerrarModal() {
  modalVisible.value = false
  if (urlPrevia.value) {
    URL.revokeObjectURL(urlPrevia.value)
    urlPrevia.value = null
  }
}

/**
 * VISTA PREVIA
 * Abre el modal y descarga el PDF como blob para mostrarlo en un <iframe>.
 * Rutas reales en api.php (buildUrlDocumento):
 *   GET /api/documentos/constancia/{nc}?tipo=estudios&periodo=2026-1
 *   GET /api/documentos/boleta/{nc}?periodo=2026-1
 *   GET /api/documentos/certificado/{nc}
 */
async function verPrevia() {
  if (!alumnoSeleccionado.value || !tieneRutaPDF.value) return

  modalVisible.value   = true
  cargandoPrevia.value = true
  urlPrevia.value      = null

  try {
    const res = await fetch(buildUrlDocumento(), { headers: authHeaders() })

    if (!res.ok) {
      const err = await res.json().catch(() => ({}))
      throw new Error(err.message || `HTTP ${res.status}`)
    }

    const blob = await res.blob()
    urlPrevia.value = URL.createObjectURL(blob)

  } catch (error) {
    console.error('verPrevia error:', error)
    mostrarToast('NO SE PUDO GENERAR LA VISTA PREVIA', 'error')
    modalVisible.value = false
  } finally {
    cargandoPrevia.value = false
  }
}

async function imprimirDocumento() {
  if (!alumnoSeleccionado.value || !tieneRutaPDF.value) return
  procesando.value = true
  try {
    const res = await fetch(buildUrlDocumento(), { headers: authHeaders() })
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    const blob = await res.blob()
    const url  = URL.createObjectURL(blob)
    const win  = window.open(url, '_blank')
    if (win) {
      win.addEventListener('load', () => { win.focus(); win.print() })
    } else {
      mostrarToast('ACTIVE LAS VENTANAS EMERGENTES PARA IMPRIMIR.', 'error')
    }
    setTimeout(() => URL.revokeObjectURL(url), 10_000)
    if (modalVisible.value) cerrarModal()
  } catch (err) {
    console.error('imprimirDocumento error:', err)
    mostrarToast('ERROR AL GENERAR DOCUMENTO.', 'error')
  } finally {
    procesando.value = false
  }
}
/**
 * buildUrlDocumento
 * ─────────────────
 * Construye la URL GET correcta según las rutas definidas en api.php:
 *
 *   GET /api/documentos/constancia/{numero_control}?tipo=estudios&periodo=2026-1
 *   GET /api/documentos/boleta/{numero_control}?periodo=2026-1[&masivo=1&carrera=ISC&semestre=3]
 *   GET /api/documentos/certificado/{numero_control}
 */
function buildUrlDocumento() {
  const nc       = alumnoSeleccionado.value?.numero_control
                ?? alumnoSeleccionado.value?.noControl
                ?? ''
  const segmento = SEGMENTO_PDF[submodulo.value]   // constancia | boleta | certificado

  const params = new URLSearchParams()

  if (submodulo.value === 'constancias') {
    params.set('tipo', tipoConstancia.value)
    if (periodoSeleccionado.value) params.set('periodo', periodoSeleccionado.value)
  }

  if (submodulo.value === 'boletas') {
    if (periodoSeleccionado.value) params.set('periodo', periodoSeleccionado.value)
    if (modoBoletaMasiva.value) {
      params.set('masivo', '1')
      if (filtros.carrera)  params.set('carrera', filtros.carrera)
      if (filtros.semestre) params.set('semestre', filtros.semestre)
    }
  }

  const qs = params.toString()
  return `${API}/api/documentos/${segmento}/${nc}${qs ? '?' + qs : ''}`
}

function buildNombreArchivo() {
  const mod  = submodulo.value.toUpperCase()
  const nc   = alumnoSeleccionado.value?.numero_control
            ?? alumnoSeleccionado.value?.noControl
            ?? 'MASIVO'
  const per  = periodoSeleccionado.value || new Date().getFullYear()
  const tipo = submodulo.value === 'constancias' ? `_${tipoConstancia.value.toUpperCase()}` : ''
  return `${mod}${tipo}_${nc}_${per}.pdf`
}
// ─── NUEVAS FUNCIONES PARA EXPORTACIÓN ──────────────────────────────────────

/**
 * Genera y descarga un CSV
 * @param {Array} data - Datos a exportar
 * @param {string} tipoDocumento - Tipo de documento (CONSTANCIA/BOLETA/CERTIFICADO)
 */
async function descargarCSV(data, tipoDocumento) {
  if (!data || data.length === 0) {
    mostrarToast('NO HAY DATOS PARA EXPORTAR A CSV', 'error')
    return
  }

  try {
    // Construir el contenido del CSV con encabezados que incluyan el tipo de documento
    const headers = [
      `TIPO_DOCUMENTO:${tipoDocumento}`,
      `FECHA_GENERACION:${new Date().toLocaleDateString('es-MX')}`,
      'NUMERO_CONTROL',
      'NOMBRE_COMPLETO',
      'CARRERA',
      'SEMESTRE',
      'ESTATUS',
      'PERIODO',
      'TIPO_CONSTANCIA'
    ]

    // Filas de datos
    const rows = data.map(alumno => [
      alumno.numero_control || alumno.noControl || '',
      alumno.nombre_completo || alumno.nombre || '',
      alumno.carrera || '',
      alumno.semestre_actual || alumno.semestre || '',
      alumno.estatus || '',
      periodoSeleccionado.value || '',
      submodulo.value === 'constancias' ? tipoConstancia.value.toUpperCase() : ''
    ])

    // Crear el contenido CSV
    let csvContent = headers.join(',') + '\n'
    
    // Agregar filas de datos
    rows.forEach(row => {
      // Escapar comillas y caracteres especiales
      const escapedRow = row.map(cell => 
        `"${String(cell || '').replace(/"/g, '""')}"`
      ).join(',')
      csvContent += escapedRow + '\n'
    })

    // Agregar pie de página con tipo de documento
    csvContent += `\n"DOCUMENTO_GENERADO: ${tipoDocumentO}"\n`
    csvContent += `"TOTAL_REGISTROS: ${data.length}"`

    // Crear blob y descargar
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    const filename = `${tipoDocumento}_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.csv`
    a.href = url
    a.download = filename
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)
    
    mostrarToast(`CSV GENERADO: ${filename}`, 'success')
  } catch (error) {
    console.error('Error generando CSV:', error)
    mostrarToast('ERROR AL GENERAR CSV', 'error')
  }
}

/**
 * Descarga múltiples documentos en ZIP
 */
async function descargarZipMasivo(urls, nombres) {
  try {
    const JSZip = (await import('jszip')).default
    const zip = new JSZip()
    
    for (let i = 0; i < urls.length; i++) {
      const response = await fetch(urls[i], { headers: authHeaders() })
      if (response.ok) {
        const blob = await response.blob()
        zip.file(nombres[i], blob)
      }
    }
    
    const content = await zip.generateAsync({ type: 'blob' })
    const zipUrl = URL.createObjectURL(content)
    const a = document.createElement('a')
    a.href = zipUrl
    a.download = `${submodulo.value}_MASIVO_${new Date().toISOString().slice(0, 19)}.zip`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(zipUrl)
    
    mostrarToast('ZIP GENERADO CORRECTAMENTE', 'success')
  } catch (error) {
    console.error('Error generando ZIP:', error)
    mostrarToast('ERROR AL GENERAR ZIP', 'error')
  }
}

/**
 * Función mejorada para descargar documento con verificación de tipo
 */
async function descargarDocumento() {
  if (!alumnoSeleccionado.value && submodulo.value !== 'boletas') return
  if (!tieneRutaPDF.value) return
  
  procesando.value = true
  
  // Determinar si es exportación masiva o individual
  const isMasivo = submodulo.value === 'boletas' && modoBoletaMasiva.value && resultadosFiltrados.value.length > 0
  
  try {
    if (isMasivo && resultadosFiltrados.value.length > 0) {
      // Generación masiva con ZIP
      const urls = []
      const nombres = []
      
      for (const alumno of resultadosFiltrados.value) {
        const nc = alumno.numero_control || alumno.noControl
        const segmento = SEGMENTO_PDF[submodulo.value]
        const params = new URLSearchParams()
        
        if (submodulo.value === 'constancias') {
          params.set('tipo', tipoConstancia.value)
          if (periodoSeleccionado.value) params.set('periodo', periodoSeleccionado.value)
        }
        if (submodulo.value === 'boletas' && periodoSeleccionado.value) {
          params.set('periodo', periodoSeleccionado.value)
        }
        
        const qs = params.toString()
        const url = `${API}/api/documentos/${segmento}/${nc}${qs ? '?' + qs : ''}`
        urls.push(url)
        nombres.push(`${submodulo.value.toUpperCase()}_${nc}_${periodoSeleccionado.value || 'SIN_PERIODO'}.pdf`)
      }
      
      await descargarZipMasivo(urls, nombres)
    } else {
      // Generación individual
      const res = await fetch(buildUrlDocumento(), { headers: authHeaders() })
      if (!res.ok) throw new Error(`HTTP ${res.status}`)
      
      const blob = await res.blob()
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      const nombre = buildNombreArchivo()
      a.href = url
      a.download = nombre
      document.body.appendChild(a)
      a.click()
      document.body.removeChild(a)
      setTimeout(() => URL.revokeObjectURL(url), 10000)
      
      mostrarToast(`DOCUMENTO ${nombre} DESCARGADO.`, 'success')
    }
    
    if (modalVisible.value) cerrarModal()
  } catch (err) {
    console.error('descargarDocumento error:', err)
    mostrarToast('ERROR AL DESCARGAR DOCUMENTO.', 'error')
  } finally {
    procesando.value = false
  }
}
</script>

<style scoped>
/* ══════════════════════════════════════════════
   BASE
══════════════════════════════════════════════ */
.doc-wrap {
  width: 100%;
  min-height: calc(100vh - 130px);
  background: #F4F6F9;
  color: #333;
  font-family: 'Montserrat', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  text-transform: uppercase;
  box-sizing: border-box;
}

.documentos-page {
  display: flex;
  flex-direction: column;
  gap: 16px;
  width: 100%;
}

/* ══════════════════════════════════════════════
   BREADCRUMB
══════════════════════════════════════════════ */
.page-breadcrumb {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #6B7280;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .04em;
}
.breadcrumb-active { color: var(--mod-color, #2563EB); }

/* ══════════════════════════════════════════════
   HEADER
══════════════════════════════════════════════ */
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid #E5E7EB;
}
.page-title {
  margin: 0;
  color: #111827;
  font-size: 24px;
  font-weight: 700;
  line-height: 1.15;
  font-family: 'Montserrat', sans-serif;
}
.page-subtitle {
  margin: 4px 0 0;
  color: #6B7280;
  font-size: 13px;
  font-weight: 400;
  text-transform: none;
}

/* ══════════════════════════════════════════════
   BARRA BÚSQUEDA + FILTROS
══════════════════════════════════════════════ */
.search-filters-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  background: #FFFFFF;
  border: 1px solid #E5E7EB;
  border-radius: 10px;
  box-shadow: 0 1px 4px rgba(17,24,39,.05);
  flex-wrap: wrap;
}

.search-wrap {
  position: relative;
  display: flex;
  align-items: center;
  flex: 1;
  min-width: 200px;
}
.search-wrap__icon {
  position: absolute;
  left: 10px;
  color: #9CA3AF;
  pointer-events: none;
}
.search-input {
  width: 100%;
  height: 36px;
  padding: 0 32px 0 34px;
  border: 1px solid #E5E7EB;
  border-radius: 7px;
  background: #F9FAFB;
  color: #111827;
  font-family: inherit;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: .04em;
  outline: none;
  transition: border-color .15s, box-shadow .15s;
}
.search-input:focus {
  border-color: var(--mod-color, #2563EB);
  box-shadow: 0 0 0 3px rgba(var(--mod-rgb, 37,99,235), .12);
  background: #FFFFFF;
}
.search-clear {
  position: absolute;
  right: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  border: 0;
  border-radius: 50%;
  background: #E5E7EB;
  color: #6B7280;
  cursor: pointer;
}
.search-clear:hover { background: #D1D5DB; }

.inline-filters {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}
.inline-filter { display: flex; align-items: center; }

.select-wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.select-control {
  height: 34px;
  padding: 0 28px 0 10px;
  border: 1px solid #E5E7EB;
  border-radius: 7px;
  background: #F9FAFB;
  color: #374151;
  font-family: inherit;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .04em;
  appearance: none;
  outline: none;
  cursor: pointer;
  transition: border-color .15s;
}
.select-control:focus { border-color: var(--mod-color, #2563EB); }
.select-control--sm { height: 30px; font-size: 10.5px; }
.select-wrap__arrow {
  position: absolute;
  right: 8px;
  color: #9CA3AF;
  pointer-events: none;
}

.search-actions {
  display: flex;
  align-items: center;
  gap: 6px;
}

/* ══════════════════════════════════════════════
   BOTONES
══════════════════════════════════════════════ */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  height: 36px;
  padding: 0 14px;
  border: 0;
  border-radius: 7px;
  font-family: inherit;
  font-size: 11.5px;
  font-weight: 700;
  letter-spacing: .04em;
  cursor: pointer;
  transition: opacity .15s, filter .15s;
  white-space: nowrap;
}
.btn:disabled { opacity: .45; cursor: not-allowed; }
.btn:not(:disabled):hover { filter: brightness(.93); }

.btn--primary  { background: #2563EB; color: #FFFFFF; }
.btn--secondary { background: #F3F4F6; color: #374151; border: 1px solid #E5E7EB; }
.btn--ghost    { background: transparent; color: #6B7280; }
.btn--icon     { padding: 0 10px; }

.btn-select {
  height: 28px;
  padding: 0 10px;
  border: 0;
  border-radius: 5px;
  color: #FFFFFF;
  font-family: inherit;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: .04em;
  cursor: pointer;
  transition: filter .15s;
}
.btn-select:hover { filter: brightness(.88); }

/* ══════════════════════════════════════════════
   SPINNER / EMPTY STATE
══════════════════════════════════════════════ */
.spinner-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 40px 0;
  color: #9CA3AF;
  font-size: 12px;
  font-weight: 700;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 48px 0;
  color: #9CA3AF;
  text-align: center;
}
.empty-state h3 { font-size: 14px; margin: 0; }
.empty-state p  { font-size: 12px; margin: 0; font-weight: 400; text-transform: none; }

/* ══════════════════════════════════════════════
   GRID DE CARDS
══════════════════════════════════════════════ */
.students-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 12px;
}

.student-card {
  background: #FFFFFF;
  border: 1.5px solid #E5E7EB;
  border-radius: 10px;
  padding: 14px;
  cursor: pointer;
  transition: border-color .15s, box-shadow .15s, transform .1s;
}
.student-card:hover {
  border-color: var(--card-color, #2563EB);
  box-shadow: 0 4px 16px rgba(0,0,0,.08);
  transform: translateY(-1px);
}
.student-card--selected {
  border-color: var(--card-color, #2563EB);
  box-shadow: 0 0 0 3px rgba(var(--mod-rgb, 37,99,235), .15);
}

.student-card__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
}
.student-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #FFFFFF;
  font-size: 13px;
  font-weight: 700;
  flex-shrink: 0;
}
.student-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 3px;
}
.student-control {
  font-size: 11px;
  font-weight: 700;
  color: #374151;
  letter-spacing: .04em;
}
.student-name {
  font-size: 13px;
  font-weight: 700;
  color: #111827;
  line-height: 1.3;
  margin-bottom: 3px;
  text-transform: none;
}
.student-career {
  font-size: 10.5px;
  color: #6B7280;
  font-weight: 600;
  margin-bottom: 10px;
  text-transform: none;
}
.student-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.student-detail {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 10.5px;
  color: #6B7280;
  font-weight: 600;
}

/* ══════════════════════════════════════════════
   PANEL ALUMNO SELECCIONADO
══════════════════════════════════════════════ */
.selected-panel {
  background: #FFFFFF;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.selected-panel--massive {
  flex-direction: row;
  align-items: flex-start;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 20px;
}
.selected-panel__main { flex: 1; }

.selected-panel__hero {
  display: flex;
  align-items: flex-start;
  gap: 14px;
}
.selected-avatar {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #FFFFFF;
  font-size: 18px;
  font-weight: 700;
  flex-shrink: 0;
}
.selected-info { flex: 1; min-width: 0; }
.selected-panel__eyebrow {
  display: block;
  font-size: 10px;
  font-weight: 700;
  color: var(--mod-color, #2563EB);
  letter-spacing: .08em;
  margin-bottom: 4px;
}
.selected-panel__title {
  margin: 0 0 8px;
  font-size: 18px;
  font-weight: 700;
  color: #111827;
  line-height: 1.2;
  text-transform: none;
}

.selected-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}
.chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  height: 22px;
  padding: 0 8px;
  border-radius: 999px;
  font-size: 10.5px;
  font-weight: 700;
  letter-spacing: .03em;
}
.chip--control  { background: #EFF6FF; color: #1D4ED8; }
.chip--carrera  { background: #F0FDF4; color: #15803D; }
.chip--semestre { background: #FFF7ED; color: #C2410C; }

.selected-config {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  padding-top: 4px;
}
.config-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.config-label {
  font-size: 10px;
  font-weight: 700;
  color: #6B7280;
  letter-spacing: .05em;
}

.selected-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-top: 10px;
}
.selected-grid > div {
  display: flex;
  flex-direction: column;
  gap: 2px;
  font-size: 12px;
}
.selected-grid > div span { color: #6B7280; font-size: 10px; font-weight: 700; }
.selected-grid > div strong { color: #111827; }
.selected-grid--compact { gap: 12px; }
.selected-period { margin-top: 2px; }

.selected-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  padding-top: 4px;
}

.residencia-box {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 10px 12px;
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  font-size: 12px;
  color: #374151;
}

/* Banner informativo para módulos sin PDF */
.info-banner {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  background: #FFF7ED;
  border: 1px solid #FDE68A;
  border-radius: 8px;
  font-size: 11px;
  font-weight: 700;
  color: #92400E;
}

/* ══════════════════════════════════════════════
   BADGES
══════════════════════════════════════════════ */
.badge {
  display: inline-flex;
  align-items: center;
  height: 20px;
  padding: 0 8px;
  border-radius: 999px;
  font-size: 10.5px;
  font-weight: 700;
}
.badge--green  { background: #ECFDF3; color: #027A48; }
.badge--blue   { background: #EFF8FF; color: #175CD3; }
.badge--red    { background: #FEF3F2; color: #B42318; }
.badge--orange { background: #FFF7ED; color: #B45309; }
.badge--gray   { background: #F3F4F6; color: #374151; }

/* ══════════════════════════════════════════════
   MODAL
══════════════════════════════════════════════ */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: rgba(17,24,39,.40);
  backdrop-filter: blur(3px);
}
.modal {
  width: min(920px, 96vw);
  height: min(82vh, 760px);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background: #FFFFFF;
  border-radius: 12px;
  box-shadow: 0 24px 56px rgba(17,24,39,.20);
}
.modal__hdr {
  min-height: 50px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 16px;
  border-bottom: 1px solid #E5E7EB;
}
.modal__hdr h3 { margin: 0; font-size: 13px; font-weight: 700; color: #111827; }
.icon-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 0;
  border-radius: 6px;
  background: transparent;
  color: #6B7280;
  cursor: pointer;
}
.icon-btn:hover { background: #F3F4F6; }
.modal__body {
  flex: 1;
  min-height: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #F3F4F6;
}
.modal__iframe { width: 100%; height: 100%; border: 0; }
.modal__spinner {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  color: #6B7280;
  font-size: 12px;
  font-weight: 700;
}
.modal__ftr {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding: 10px 16px;
  border-top: 1px solid #E5E7EB;
}

.doc-mock {
  width: min(400px, 84%);
  height: 75%;
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 24px;
  background: #FFFFFF;
  border-radius: 6px;
  box-shadow: 0 2px 12px rgba(17,24,39,.08);
}
.doc-mock__logo  { width: 36px; height: 36px; background: #EEF2F7; border-radius: 6px; }
.doc-mock__lines { display: flex; flex-direction: column; gap: 7px; margin-top: 8px; }
.doc-mock__line  { height: 9px; background: #EEF2F7; border-radius: 3px; }
.doc-mock__sello { width: 48px; height: 48px; align-self: flex-end; margin-top: auto; border: 2px dashed #CBD5E1; border-radius: 50%; }

/* ══════════════════════════════════════════════
   TOAST
══════════════════════════════════════════════ */
.toast {
  position: fixed;
  right: 16px;
  bottom: 20px;
  z-index: 3000;
  display: flex;
  align-items: center;
  gap: 8px;
  max-width: min(420px, calc(100vw - 32px));
  padding: 10px 14px;
  background: #FFFFFF;
  border: 1px solid #E5E7EB;
  border-left: 4px solid #6B7280;
  border-radius: 8px;
  box-shadow: 0 12px 28px rgba(17,24,39,.12);
  color: #374151;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: .02em;
}
.toast--success { border-left-color: #16A34A; color: #166534; }
.toast--error   { border-left-color: #EF4444; color: #991B1B; }

/* ══════════════════════════════════════════════
   ANIMACIONES
══════════════════════════════════════════════ */
.spin { animation: spin-loader 1s linear infinite; }
@keyframes spin-loader { to { transform: rotate(360deg); } }

.toast-enter-active, .toast-leave-active,
.modal-enter-active, .modal-leave-active { transition: opacity .15s ease, transform .15s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(6px); }
.modal-enter-from, .modal-leave-to { opacity: 0; }

/* ══════════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════════ */
@media (max-width: 900px) {
  .students-grid { grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); }
}
@media (max-width: 700px) {
  .search-filters-bar { flex-direction: column; align-items: stretch; }
  .search-wrap { min-width: 100%; }
  .inline-filters { width: 100%; }
  .search-actions { width: 100%; justify-content: flex-end; }
  .selected-panel__hero { flex-direction: column; }
  .selected-actions { flex-direction: column; }
  .selected-actions .btn { width: 100%; justify-content: center; }
  .students-grid { grid-template-columns: 1fr; }
}
</style>
