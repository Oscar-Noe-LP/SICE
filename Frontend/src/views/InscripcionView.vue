<template>
  <MainLayout>
    <div class="inscripcion-page" ref="paginaRef" @keydown="manejarTeclado" tabindex="-1">

      <!-- BREADCRUMB -->
      <nav class="breadcrumb" aria-label="MIGA DE PAN">
        <router-link to="/servicios-escolares" class="breadcrumb-link">
          SERVICIOS ESCOLARES
        </router-link>

        <span class="breadcrumb-sep" aria-hidden="true">
          ›
        </span>

        <span class="breadcrumb-actual" aria-current="page">
          INSCRIPCIÓN
        </span>
      </nav>

      <h1 class="page-title">INSCRIPCIÓN</h1>
      <p class="subtitle">
        BUSQUE AL ALUMNO POR NÚMERO DE CONTROL O NOMBRE, Y ASÍGNELO A UNO O MÁS GRUPOS DISPONIBLES.
      </p>

      <!-- TOAST -->
      <div v-if="notification.message" class="toast" :class="notification.type">
        {{ notification.message?.toUpperCase() }}
      </div>

      <!-- BARRA DE PASOS -->
      <div class="pasos-barra">
        <div class="paso" :class="{ activo: paso >= 1, completado: paso > 1 }">
          <div class="paso-circulo">
            <span v-if="paso > 1">✓</span>
            <span v-else>1</span>
          </div>
          <span class="paso-label">BUSCAR ALUMNO</span>
        </div>

        <div class="paso-linea" :class="{ completado: paso > 1 }"></div>

        <div class="paso" :class="{ activo: paso >= 2, completado: paso > 2 }">
          <div class="paso-circulo">
            <span v-if="paso > 2">✓</span>
            <span v-else>2</span>
          </div>
          <span class="paso-label">SELECCIONAR MATERIAS</span>
        </div>

        <div class="paso-linea" :class="{ completado: paso > 2 }"></div>

        <div class="paso" :class="{ activo: paso >= 3 }">
          <div class="paso-circulo">3</div>
          <span class="paso-label">CONFIRMAR</span>
        </div>
      </div>

      <!-- CARD DE CONTENIDO -->
      <div class="content-card">

        <!-- ── PASO 1 ── -->
        <div v-if="paso === 1">
          <div class="paso-header">
            <div class="paso-icono">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>

            <div>
              <h3>BUSCAR ALUMNO</h3>
              <p class="paso-desc">INGRESE EL NÚMERO DE CONTROL O EL NOMBRE DEL ALUMNO A INSCRIBIR.</p>
            </div>
          </div>

          <div class="busqueda-row">
            <div class="campo-grupo campo-prioritario">
              <label>
                NÚMERO DE CONTROL
                <span class="etiqueta-principal">PRINCIPAL</span>
              </label>

              <div class="input-con-icono">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>

                <input
                  type="text"
                  v-model="busquedaControl"
                  placeholder="EJ: 21456987"
                  ref="inputControlRef"
                  @keyup.enter="buscarAlumno"
                  @input="normalizarBusqueda('control')"
                />
              </div>
            </div>

            <div class="campo-grupo">
              <label>NOMBRE DEL ALUMNO</label>

              <div class="input-con-icono">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>

                <input
                  type="text"
                  v-model="busquedaNombre"
                  placeholder="EJ: SARA PÉREZ"
                  @keyup.enter="buscarAlumno"
                  @input="normalizarBusqueda('nombre')"
                />
              </div>
            </div>

            <div class="campo-grupo campo-periodo">
              <label>PERIODO</label>

              <select v-model="periodo" class="select-periodo" :disabled="cargandoPeriodos">
                <option :value="null" disabled>
                  {{ cargandoPeriodos ? 'CARGANDO PERIODOS...' : periodos.length === 0 ? 'SIN PERIODOS DISPONIBLES' : 'SELECCIONAR PERIODO' }}
                </option>

                <option
                  v-for="p in periodos"
                  :key="p.id"
                  :value="p.id"
                >
                  {{ p.nombre?.toUpperCase() }}
                </option>
              </select>
            </div>
          </div>

          <div class="busqueda-acciones">
            <button class="btn-buscar" @click="buscarAlumno" :disabled="cargando">
              <span v-if="cargando" class="spinner-btn"></span>
              {{ cargando ? 'BUSCANDO...' : 'BUSCAR ALUMNO' }}
            </button>
          </div>

          <div v-if="resultadosBusqueda.length > 0 && !alumnoSeleccionado" class="resultados-lista">
            <p class="resultados-titulo">SELECCIONE AL ALUMNO CORRESPONDIENTE:</p>

            <div
              v-for="alumno in resultadosBusqueda"
              :key="alumno.noControl"
              class="resultado-item"
              @click="elegirAlumno(alumno)"
            >
              <div class="resultado-avatar">
                {{ alumno.nombre.charAt(0).toUpperCase() }}
              </div>

              <div class="resultado-info">
                <strong>{{ alumno.nombre?.toUpperCase() }}</strong>
                <span>
                  {{ alumno.noControl }} · {{ alumno.carrera?.toUpperCase() }} · {{ alumno.semestre }}° SEMESTRE
                </span>
              </div>

              <button class="btn-seleccionar">SELECCIONAR</button>
            </div>
          </div>

          <div v-if="alumnoSeleccionado" class="alumno-seleccionado">
            <div class="alumno-avatar">
              {{ alumnoSeleccionado.nombre.charAt(0).toUpperCase() }}
            </div>

            <div class="alumno-datos">
              <strong>{{ alumnoSeleccionado.nombre?.toUpperCase() }}</strong>
              <span>
                {{ alumnoSeleccionado.noControl }} · {{ alumnoSeleccionado.carrera?.toUpperCase() }} · {{ alumnoSeleccionado.semestre }}° SEMESTRE
              </span>
            </div>

            <div class="alumno-acciones">
              <button class="btn-carga-academica" @click="abrirModalCargaAlumno(alumnoSeleccionado)">
                <svg xmlns="http://www.w3.org/2000/svg" class="btn-carga-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                VER CARGA ACADÉMICA
              </button>

              <button class="btn-cambiar" @click="limpiarAlumno">
                CAMBIAR ALUMNO
              </button>

              <button class="btn-siguiente" @click="paso = 2">
                CONTINUAR
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- ── PASO 2 ── -->
        <div v-if="paso === 2">
          <div class="paso-header">
            <div class="paso-icono">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
              </svg>
            </div>

            <div>
              <h3>SELECCIONAR MATERIAS</h3>
              <p class="paso-desc">
                ASIGNANDO A:
                <strong>{{ alumnoSeleccionado?.nombre?.toUpperCase() }}</strong>
                ({{ alumnoSeleccionado?.noControl }})
              </p>
            </div>
          </div>

          <div class="filtros-card-inline">
            <div class="filtros-label">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
              </svg>
              FILTRAR:
            </div>

            <div class="busqueda-grupos-wrap">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" class="icono-lupa-gris">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
              </svg>

              <input
                type="text"
                v-model="busquedaGrupo"
                placeholder="MATERIA, DOCENTE O AULA..."
                class="input-busqueda-grupos"
                @input="normalizarBusqueda('grupo')"
              />

              <button v-if="busquedaGrupo" @click="busquedaGrupo = ''" class="btn-limpiar-busqueda-g">
                ✕
              </button>
            </div>

            <button class="btn-filtrar-inline" @click="filtrarGrupos">
              FILTRAR
            </button>
          </div>

          <div v-if="gruposSeleccionados.length > 0" class="seleccion-multiple-resumen">
            <div>
              <strong>{{ gruposSeleccionados.length }} MATERIA(S) SELECCIONADA(S)</strong>
              <span>REVISA TU SELECCIÓN ANTES DE CONTINUAR.</span>
            </div>

            <button class="btn-limpiar-seleccion" @click="limpiarSeleccionGrupos">
              LIMPIAR SELECCIÓN
            </button>
          </div>

          <div class="table-container" :class="{ 'loading-state': cargando }">
            <div v-if="cargando" class="loading-overlay">
              <div class="loading-spinner"></div>
              <span>{{ mensajeCarga?.toUpperCase() }}</span>
            </div>

            <table class="inscripcion-table">
              <thead>
                <tr>
                  <th class="text-center">SEL.</th>
                  <th>MATERIA</th>
                  <th>DOCENTE</th>
                  <th>AULA</th>
                  <th>HORARIO</th>
                  <th class="text-center">LUGARES</th>
                  <th class="text-center">ACCIÓN</th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="(grupo, idx) in gruposFiltrados"
                  :key="grupo.id"
                  :class="{
                    'fila-activa': filaActiva === idx,
                    'fila-seleccionada-multiple': grupoEstaSeleccionado(grupo)
                  }"
                  @click="filaActiva = idx"
                >
                  <td class="text-center">
                    <input
                      type="checkbox"
                      class="checkbox-grupo"
                      :checked="grupoEstaSeleccionado(grupo)"
                      :disabled="grupo.inscritos >= grupo.capacidad"
                      @change.stop="alternarGrupoSeleccionado(grupo)"
                    />
                  </td>

                  <td>{{ grupo.materia?.toUpperCase() }}</td>
                  <td>{{ grupo.docente?.toUpperCase() }}</td>
                  <td>{{ grupo.aula?.toUpperCase() }}</td>

                  <td>
                    <span v-if="grupo.horario?.dia" class="horario-badge">
                      {{ grupo.horario.dia?.toUpperCase() }} {{ grupo.horario.horaInicio }}–{{ grupo.horario.horaFin }}
                    </span>
                    <span v-else class="sin-dato">—</span>
                  </td>

                  <td class="text-center">
                    <span class="lugares-badge" :class="{ lleno: grupo.inscritos >= grupo.capacidad, casi: grupo.inscritos >= grupo.capacidad * 0.9 }">
                      {{ grupo.capacidad - grupo.inscritos }} DISPONIBLES
                    </span>
                  </td>

                  <td class="text-center">
                    <button
                      v-if="grupo.inscritos < grupo.capacidad"
                      class="btn-elegir"
                      :class="{ seleccionado: grupoEstaSeleccionado(grupo) }"
                      @click.stop="alternarGrupoSeleccionado(grupo)"
                    >
                      {{ grupoEstaSeleccionado(grupo) ? 'QUITAR' : 'AGREGAR' }}
                    </button>

                    <span v-else class="badge-lleno">SIN LUGARES</span>
                  </td>
                </tr>

                <tr v-if="gruposFiltrados.length === 0">
                  <td colspan="7" class="sin-resultados">
                    NO SE ENCONTRARON GRUPOS DISPONIBLES.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="pagination">
            <span>MOSTRANDO {{ gruposFiltrados.length }} DE {{ grupos.length }} GRUPOS</span>

            <div class="pagination-buttons">
              <button class="page-btn" @click="prevPage" :disabled="currentPage === 1">
                ‹
              </button>

              <button
                v-for="page in totalPages"
                :key="page"
                class="page-btn"
                :class="{ active: page === currentPage }"
                @click="currentPage = page"
              >
                {{ page }}
              </button>

              <button class="page-btn" @click="nextPage" :disabled="currentPage === totalPages">
                ›
              </button>
            </div>
          </div>

          <div class="paso-navegacion">
            <button class="btn-atras" @click="paso = 1">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
              REGRESAR
            </button>

            <button
              class="btn-siguiente btn-continuar-materias"
              @click="continuarAConfirmacion"
              :disabled="gruposSeleccionados.length === 0"
            >
              CONTINUAR CON {{ gruposSeleccionados.length }} MATERIA(S)
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- ── PASO 3 ── -->
        <div v-if="paso === 3">
          <div class="paso-header">
            <div class="paso-icono icono-verde">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>

            <div>
              <h3>CONFIRMAR INSCRIPCIÓN</h3>
              <p class="paso-desc">VERIFIQUE LOS DATOS ANTES DE CONFIRMAR.</p>
            </div>
          </div>

          <div class="confirmacion-grid confirmacion-grid-multiple">
            <div class="confirmacion-bloque">
              <p class="bloque-titulo">ALUMNO</p>
              <p class="bloque-valor">{{ alumnoSeleccionado?.nombre?.toUpperCase() }}</p>
              <p class="bloque-sub">{{ alumnoSeleccionado?.noControl }} · {{ alumnoSeleccionado?.semestre }}° SEMESTRE</p>
              <p class="bloque-sub">{{ alumnoSeleccionado?.carrera?.toUpperCase() }}</p>
            </div>

            <div class="confirmacion-bloque">
              <p class="bloque-titulo">PERIODO</p>
              <p class="bloque-valor">
                {{ periodos.find(p => p.id === periodo)?.nombre?.toUpperCase() ?? '—' }}
              </p>
              <p class="bloque-sub">{{ gruposSeleccionados.length }} MATERIA(S) SELECCIONADA(S)</p>
            </div>
          </div>

          <div class="materias-confirmacion-card">
            <div class="materias-confirmacion-header">
              <h4>MATERIAS A INSCRIBIR</h4>
              <span>{{ gruposSeleccionados.length }} REGISTRO(S)</span>
            </div>

            <div class="table-container">
              <table class="inscripcion-table">
                <thead>
                  <tr>
                    <th>MATERIA</th>
                    <th>DOCENTE</th>
                    <th>AULA</th>
                    <th>HORARIO</th>
                    <th class="text-center">LUGARES</th>
                    <th class="text-center">ACCIÓN</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-for="grupo in gruposSeleccionados" :key="grupo.id">
                    <td>{{ grupo.materia?.toUpperCase() }}</td>
                    <td>{{ grupo.docente?.toUpperCase() }}</td>
                    <td>{{ grupo.aula?.toUpperCase() }}</td>
                    <td>
                      <span v-if="grupo.horario?.dia" class="horario-badge">
                        {{ grupo.horario.dia?.toUpperCase() }} {{ grupo.horario.horaInicio }}–{{ grupo.horario.horaFin }}
                      </span>
                      <span v-else class="sin-dato">—</span>
                    </td>
                    <td class="text-center">
                      <span class="lugares-badge">
                        {{ grupo.capacidad - grupo.inscritos }} DISPONIBLES
                      </span>
                    </td>
                    <td class="text-center">
                      <button class="btn-quitar-confirmacion" @click="alternarGrupoSeleccionado(grupo)">
                        QUITAR
                      </button>
                    </td>
                  </tr>

                  <tr v-if="gruposSeleccionados.length === 0">
                    <td colspan="6" class="sin-resultados">
                      NO HAY MATERIAS SELECCIONADAS.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="paso-navegacion">
            <button class="btn-atras" @click="paso = 2" :disabled="cargando">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
              REGRESAR
            </button>

            <button class="btn-confirmar" @click="confirmarInscripcion" :disabled="cargando || gruposSeleccionados.length === 0">
              <span v-if="cargando" class="spinner-btn"></span>
              {{ cargando ? 'INSCRIBIENDO...' : `CONFIRMAR ${gruposSeleccionados.length} MATERIA(S)` }}
            </button>
          </div>
        </div>
      </div>

      <!-- MODAL: CARGA ACADÉMICA DEL ALUMNO -->
      <div v-if="showModalCargaAlumno" class="modal-carga-overlay" @click.self="cerrarModalCargaAlumno">
        <div class="modal-carga-content">
          <div class="modal-carga-header">
            <div class="modal-carga-alumno">
              <div class="modal-carga-avatar">
                {{ alumnoCargaAcademica?.nombre?.charAt(0)?.toUpperCase() || '?' }}
              </div>

              <div>
                <span class="modal-carga-tag">CARGA ACADÉMICA DEL ALUMNO</span>
                <h3>{{ alumnoCargaAcademica?.nombre?.toUpperCase() }}</h3>
                <p>
                  {{ alumnoCargaAcademica?.noControl }} · {{ alumnoCargaAcademica?.carrera?.toUpperCase() }} · {{ alumnoCargaAcademica?.semestre }}° SEMESTRE
                </p>
              </div>
            </div>

            <button class="btn-cerrar-carga" @click="cerrarModalCargaAlumno" title="CERRAR">
              ×
            </button>
          </div>

          <div class="modal-carga-body">
            <div class="carga-resumen-grid">
              <div class="carga-resumen-card">
                <span class="carga-resumen-numero">{{ cargaAcademicaAlumno.length }}</span>
                <span class="carga-resumen-label">MATERIAS</span>
              </div>

              <div class="carga-resumen-card">
                <span class="carga-resumen-numero">{{ totalCreditosAlumno }}</span>
                <span class="carga-resumen-label">CRÉDITOS</span>
              </div>

              <div class="carga-resumen-card">
                <span class="carga-resumen-numero">{{ totalHorasAlumno }}</span>
                <span class="carga-resumen-label">HRS / SEMANA</span>
              </div>

              <div class="carga-resumen-card">
                <span class="carga-resumen-numero">
                  {{ periodos.find(p => p.id === periodo)?.nombre?.toUpperCase() ?? '—' }}
                </span>
                <span class="carga-resumen-label">PERIODO</span>
              </div>
            </div>

            <div class="modal-carga-acciones">
              <button
                class="btn-exportar-carga"
                @click="exportarCargaAlumno"
                :disabled="cargaAcademicaAlumno.length === 0"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="btn-exportar-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                EXPORTAR CARGA
              </button>
            </div>

            <div class="table-container carga-alumno-table-container">
              <div v-if="cargandoCargaAlumno" class="loading-overlay carga-modal-loading">
                <div class="loading-spinner"></div>
                <span>CARGANDO CARGA ACADÉMICA...</span>
              </div>

              <table v-else-if="cargaAcademicaAlumno.length > 0" class="inscripcion-table carga-alumno-table">
                <thead>
                  <tr>
                    <th>CLAVE DEL GRUPO</th>
                    <th>MATERIA</th>
                    <th>DOCENTE</th>
                    <th>AULA</th>
                    <th>HORARIO</th>
                    <th class="text-center">CRÉDITOS</th>
                    <th class="text-center">ESTATUS</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-for="grupo in cargaAcademicaAlumno" :key="grupo.id">
                    <td class="celda-clave-carga">{{ grupo.claveGrupo }}</td>
                    <td class="celda-materia-carga">{{ grupo.materia }}</td>
                    <td>{{ grupo.docente }}</td>
                    <td>{{ grupo.aula }}</td>
                    <td>
                      <span v-if="grupo.dia || grupo.horaInicio || grupo.horaFin" class="horario-badge">
                        {{ grupo.dia }} {{ grupo.horaInicio }}–{{ grupo.horaFin }}
                      </span>
                      <span v-else class="sin-dato">—</span>
                    </td>
                    <td class="text-center">
                      <span class="creditos-badge">{{ grupo.creditos }}</span>
                    </td>
                    <td class="text-center">
                      <span class="estatus-carga-badge">{{ grupo.estatus }}</span>
                    </td>
                  </tr>
                </tbody>
              </table>

              <div v-else class="estado-vacio-carga">
                <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio-carga" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3>SIN CARGA ACADÉMICA</h3>
                <p>EL ALUMNO NO TIENE MATERIAS INSCRITAS EN EL PERIODO SELECCIONADO.</p>
              </div>
            </div>
          </div>

          <div class="modal-carga-footer">
            <span>
              TOTAL DE HORAS ASIGNADAS:
              <strong>{{ totalHorasAlumno }} HRS / SEMANA</strong>
            </span>

            <button class="btn-cerrar-carga-footer" @click="cerrarModalCargaAlumno">
              CERRAR
            </button>
          </div>
        </div>
      </div>

      <footer class="footer-institucional">
        © {{ new Date().getFullYear() }} TECNOLÓGICO NACIONAL DE MÉXICO · TODOS LOS DERECHOS RESERVADOS
      </footer>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`
const API_BASE = `${API}/inscripcion`

// ── CORRECCIÓN #14: Periodos cargados directamente desde el backend ──
// Se eliminó la dependencia de useCatalogos (que no era confiable).
// Se hace fetch a /api/periodos al montar el componente.
const periodos = ref([])
const cargandoPeriodos = ref(false)

const cargarPeriodos = async () => {
  cargandoPeriodos.value = true
  try {
    // Intentar el endpoint estándar de periodos
    const res = await fetch(`${API}/periodos`, { headers: { 'Accept': 'application/json' } })
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    const data = await res.json()
    // Normalizar: el backend puede devolver { id_periodo, nombre } o variantes
    periodos.value = data.map(p => ({
      id:     p.id_periodo ?? p.id,
      nombre: p.nombre    ?? p.descripcion ?? `Periodo ${p.id_periodo ?? p.id}`
    }))
    // Auto-seleccionar el periodo activo si existe
    if (!periodo.value) {
      const activo = periodos.value.find(p =>
        p.activo === true ||
        p.nombre?.toLowerCase().includes('actual') ||
        p.nombre?.toLowerCase().includes('activo')
      ) ?? periodos.value[0]
      if (activo) periodo.value = activo.id
    }
  } catch (err) {
    console.error('Error cargando periodos:', err)
    // Intentar ruta alternativa si la principal falla
    try {
      const res2 = await fetch(`${API}/catalogos/periodos`, { headers: { 'Accept': 'application/json' } })
      if (res2.ok) {
        const data2 = await res2.json()
        periodos.value = (Array.isArray(data2) ? data2 : data2.periodos ?? []).map(p => ({
          id:     p.id_periodo ?? p.id,
          nombre: p.nombre    ?? p.descripcion ?? `Periodo ${p.id_periodo ?? p.id}`
        }))
      }
    } catch {}
  } finally {
    cargandoPeriodos.value = false
  }
}

const paso = ref(1)
// ── CORRECCIÓN: periodo guarda el ID (null por defecto hasta cargar catálogo) ──
const periodo = ref(null)
const cargando = ref(false)
const mensajeCarga = ref('')
const filaActiva = ref(-1)
const paginaRef = ref(null)
const inputControlRef = ref(null)
const notification = ref({ message: '', type: '' })
const normalizarBusqueda = (campo) => {
  if (campo === 'control') {
    busquedaControl.value = busquedaControl.value.toUpperCase()
  }

  if (campo === 'nombre') {
    busquedaNombre.value = busquedaNombre.value.toUpperCase()
  }

  if (campo === 'grupo') {
    busquedaGrupo.value = busquedaGrupo.value.toUpperCase()
  }

  limpiarAlumnoSiBuscaAlumno(campo)
}

const limpiarAlumnoSiBuscaAlumno = (campo) => {
  if (campo === 'control' || campo === 'nombre') {
    alumnoSeleccionado.value = null
    resultadosBusqueda.value = []
    gruposSeleccionados.value = []
    grupoSeleccionado.value = null
  }
}
const showNotification = (message, type) => {
  notification.value = { message, type }
  setTimeout(() => { notification.value = { message: '', type: '' } }, 3500)
}

const busquedaControl = ref('')
const busquedaNombre = ref('')
const resultadosBusqueda = ref([])
const alumnoSeleccionado = ref(null)

let debounceTimer = null
watch(busquedaControl, (val) => {
  clearTimeout(debounceTimer)
  if (val.trim().length >= 3) debounceTimer = setTimeout(() => buscarAlumno(), 500)
})

const normalizarAlumno = (a) => ({
  noControl: a.noControl || a.numero_control || '',
  nombre: a.nombre || '',
  carrera: a.carrera || '',
  semestre: a.semestre || a.semestre_actual || 1,
  id_alumno: a.id_alumno || a.id
})

const buscarAlumno = async () => {
  const ctrl = busquedaControl.value.trim()
  const nombre = busquedaNombre.value.trim()
  if (!ctrl && !nombre) return
  cargando.value = true
  mensajeCarga.value = 'Buscando alumno...'
  try {
    const termino = ctrl || nombre
    const response = await fetch(`${API_BASE}/buscar-alumno?q=${encodeURIComponent(termino)}`, { headers: { 'Accept': 'application/json' } })
    const data = await response.json()
    if (!response.ok) throw new Error(data.error || 'Error del servidor')
    resultadosBusqueda.value = [normalizarAlumno(data)]
    if (resultadosBusqueda.value.length === 0) showNotification('No se encontró ningún alumno.', 'error')
  } catch (error) {
    resultadosBusqueda.value = []
    showNotification(error.message || 'No se pudo conectar con el servidor.', 'error')
  } finally { cargando.value = false; mensajeCarga.value = '' }
}

const elegirAlumno = (alumno) => { alumnoSeleccionado.value = alumno; resultadosBusqueda.value = []; paso.value = 2 }
const limpiarAlumno = () => {
  alumnoSeleccionado.value = null
  resultadosBusqueda.value = []
  grupoSeleccionado.value = null
  gruposSeleccionados.value = []
  alumnoCargaAcademica.value = null
  cargaAcademicaAlumno.value = []
  showModalCargaAlumno.value = false
}

const busquedaGrupo = ref('')
const currentPage = ref(1)
const porPagina = 5
const grupoSeleccionado = ref(null)
const gruposSeleccionados = ref([])
const grupos = ref([])

// ── MODAL: CARGA ACADÉMICA DEL ALUMNO ─────────────────────────────
const showModalCargaAlumno = ref(false)
const cargandoCargaAlumno = ref(false)
const cargaAcademicaAlumno = ref([])
const alumnoCargaAcademica = ref(null)

const normalizarCargaAlumno = (g) => ({
  id: g.id_inscripcion ?? g.id_asignacion ?? g.id_grupo ?? g.id,
  claveGrupo: g.clave_grupo ?? g.claveGrupo ?? g.grupo ?? '—',
  materia: (g.materia ?? g.nombre_materia ?? 'SIN MATERIA').toString().toUpperCase(),
  docente: (g.docente ?? g.nombre_docente ?? 'SIN DOCENTE').toString().toUpperCase(),
  aula: (g.aula ?? g.nombre_aula ?? '—').toString().toUpperCase(),
  dia: (g.dia ?? g.horario?.dia ?? '').toString().toUpperCase(),
  horaInicio: g.hora_inicio ?? g.horaInicio ?? g.horario?.horaInicio ?? '',
  horaFin: g.hora_fin ?? g.horaFin ?? g.horario?.horaFin ?? '',
  creditos: Number(g.creditos ?? g.materia_creditos ?? 0),
  estatus: (g.estatus ?? g.estado ?? 'INSCRITO').toString().toUpperCase()
})

const calcularHorasCarga = (inicio, fin) => {
  if (!inicio || !fin) return 1

  try {
    const [hI, mI] = inicio.split(':').map(Number)
    const [hF, mF] = fin.split(':').map(Number)
    const minutos = (hF * 60 + mF) - (hI * 60 + mI)

    if (minutos <= 0) return 1

    return Math.max(1, Math.round(minutos / 60))
  } catch {
    return 1
  }
}

const totalHorasAlumno = computed(() => {
  return cargaAcademicaAlumno.value.reduce((total, grupo) => {
    return total + calcularHorasCarga(grupo.horaInicio, grupo.horaFin)
  }, 0)
})

const totalCreditosAlumno = computed(() => {
  return cargaAcademicaAlumno.value.reduce((total, grupo) => total + Number(grupo.creditos || 0), 0)
})

const obtenerRutasCargaAlumno = (alumno) => {
  const idAlumno = alumno?.id_alumno
  const noControl = alumno?.noControl
  const queryPeriodo = periodo.value ? `?id_periodo=${encodeURIComponent(periodo.value)}` : ''

  return [
    `${API}/carga-academica/alumno/${idAlumno}${queryPeriodo}`,
    `${API}/inscripcion/carga-alumno/${idAlumno}${queryPeriodo}`,
    `${API}/alumnos/${idAlumno}/carga-academica${queryPeriodo}`,
    `${API}/carga-academica/alumno/control/${encodeURIComponent(noControl)}${queryPeriodo}`
  ].filter(ruta => !ruta.includes('/undefined') && !ruta.includes('/null'))
}

const cargarCargaAcademicaAlumno = async (alumno) => {
  cargandoCargaAlumno.value = true
  cargaAcademicaAlumno.value = []

  try {
    const rutas = obtenerRutasCargaAlumno(alumno)
    let ultimoError = null

    for (const ruta of rutas) {
      try {
        const response = await fetch(ruta, {
          headers: {
            'Accept': 'application/json'
          }
        })

        if (!response.ok) {
          ultimoError = new Error(`HTTP ${response.status}`)
          continue
        }

        const data = await response.json()
        const lista = Array.isArray(data)
          ? data
          : data.carga_academica ?? data.cargaAcademica ?? data.grupos ?? data.inscripciones ?? []

        cargaAcademicaAlumno.value = lista.map(normalizarCargaAlumno)
        return
      } catch (error) {
        ultimoError = error
      }
    }

    throw ultimoError || new Error('NO SE ENCONTRÓ ENDPOINT DE CARGA ACADÉMICA')
  } catch (error) {
    console.error('Error cargando carga académica del alumno:', error)
    cargaAcademicaAlumno.value = []
    showNotification('NO SE PUDO CARGAR LA CARGA ACADÉMICA DEL ALUMNO.', 'error')
  } finally {
    cargandoCargaAlumno.value = false
  }
}

const abrirModalCargaAlumno = async (alumno = alumnoSeleccionado.value) => {
  if (!alumno) {
    showNotification('SELECCIONE UN ALUMNO PARA CONSULTAR SU CARGA ACADÉMICA.', 'error')
    return
  }

  alumnoCargaAcademica.value = { ...alumno }
  showModalCargaAlumno.value = true

  await cargarCargaAcademicaAlumno(alumno)
}

const cerrarModalCargaAlumno = () => {
  showModalCargaAlumno.value = false
}

const exportarCargaAlumno = () => {
  if (!alumnoCargaAcademica.value || cargaAcademicaAlumno.value.length === 0) return

  const hoy = new Date().toISOString().split('T')[0]
  const encabezados = ['CLAVE GRUPO', 'MATERIA', 'DOCENTE', 'AULA', 'DÍA', 'HORA INICIO', 'HORA FIN', 'CRÉDITOS', 'ESTATUS']
  const filas = cargaAcademicaAlumno.value.map(g => [
    g.claveGrupo,
    g.materia,
    g.docente,
    g.aula,
    g.dia,
    g.horaInicio,
    g.horaFin,
    g.creditos,
    g.estatus
  ])

  const contenido = [encabezados, ...filas]
    .map(fila => fila.map(valor => `"${String(valor ?? '').replace(/"/g, '""')}"`).join(','))
    .join('\n')

  const nombreAlumno = (alumnoCargaAcademica.value.nombre || 'alumno').replace(/\s+/g, '_').toLowerCase()
  const blob = new Blob(['\uFEFF' + contenido], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')

  a.href = url
  a.download = `carga_academica_${nombreAlumno}_${hoy}.csv`
  a.click()

  URL.revokeObjectURL(url)
}

const normalizarGrupo = (g) => ({
  id: g.id, materia: g.materia || '', docente: g.docente || '', aula: g.aula || '',
  capacidad: g.capacidad || 0, inscritos: g.inscritos ?? 0,
  horario: { dia: g.dia || '', horaInicio: g.hora_inicio || '', horaFin: g.hora_fin || '' }
})

const cargarGruposDisponibles = async () => {
  cargando.value = true
  mensajeCarga.value = 'Cargando grupos disponibles...'
  try {
    const response = await fetch(`${API_BASE}/grupos`, { headers: { 'Accept': 'application/json' } })
    const data = await response.json()
    if (!response.ok) throw new Error(data.error || 'Error del servidor')
    grupos.value = data.map(normalizarGrupo)
  } catch (error) {
    grupos.value = []
    showNotification(error.message || 'No se pudieron cargar los grupos.', 'error')
  } finally { cargando.value = false; mensajeCarga.value = '' }
}

const gruposBaseFiltrados = computed(() => {
  const q = busquedaGrupo.value.toLowerCase().trim()
  if (!q) return grupos.value
  return grupos.value.filter(g => g.materia.toLowerCase().includes(q) || g.docente.toLowerCase().includes(q) || g.aula.toLowerCase().includes(q))
})

const gruposFiltrados = computed(() => {
  const inicio = (currentPage.value - 1) * porPagina
  return gruposBaseFiltrados.value.slice(inicio, inicio + porPagina)
})

const totalPages = computed(() => Math.ceil(gruposBaseFiltrados.value.length / porPagina) || 1)

const filtrarGrupos = () => {
  currentPage.value = 1
  filaActiva.value = -1
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    filaActiva.value = -1
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    filaActiva.value = -1
  }
}

const grupoEstaSeleccionado = (grupo) => {
  return gruposSeleccionados.value.some(g => g.id === grupo.id)
}

const alternarGrupoSeleccionado = (grupo) => {
  if (!grupo || grupo.inscritos >= grupo.capacidad) return

  if (grupoEstaSeleccionado(grupo)) {
    gruposSeleccionados.value = gruposSeleccionados.value.filter(g => g.id !== grupo.id)
    return
  }

  gruposSeleccionados.value.push(grupo)
}

const limpiarSeleccionGrupos = () => {
  gruposSeleccionados.value = []
  grupoSeleccionado.value = null
}

const continuarAConfirmacion = () => {
  if (gruposSeleccionados.value.length === 0) {
    showNotification('SELECCIONE AL MENOS UNA MATERIA.', 'error')
    return
  }

  grupoSeleccionado.value = gruposSeleccionados.value[0] ?? null
  paso.value = 3
}

const confirmarInscripcion = async () => {
  if (!alumnoSeleccionado.value || gruposSeleccionados.value.length === 0) {
    showNotification('FALTAN DATOS.', 'error')
    return
  }

  cargando.value = true
  mensajeCarga.value = 'REGISTRANDO INSCRIPCIÓN...'

  try {
    const alumnoActual = { ...alumnoSeleccionado.value }

    for (const grupo of gruposSeleccionados.value) {
      const payload = {
        id_alumno: alumnoSeleccionado.value.id_alumno,
        id_grupo: grupo.id,
        id_periodo: periodo.value
      }

      const response = await fetch(`${API_BASE}/registrar`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.error || `NO SE PUDO INSCRIBIR LA MATERIA ${grupo.materia?.toUpperCase()}`)
      }
    }

    showNotification(`INSCRIPCIÓN CONFIRMADA: ${alumnoActual.nombre?.toUpperCase()} EN ${gruposSeleccionados.value.length} MATERIA(S).`, 'success')

    await cargarGruposDisponibles()
    await abrirModalCargaAlumno(alumnoActual)

    paso.value = 1
    alumnoSeleccionado.value = null
    grupoSeleccionado.value = null
    gruposSeleccionados.value = []
    resultadosBusqueda.value = []
    busquedaControl.value = ''
    busquedaNombre.value = ''
    busquedaGrupo.value = ''
    currentPage.value = 1
    filaActiva.value = -1
  } catch (error) {
    showNotification(error.message || 'NO SE PUDO REGISTRAR LA INSCRIPCIÓN.', 'error')
  } finally {
    cargando.value = false
    mensajeCarga.value = ''
  }
}

const manejarTeclado = (e) => {
  const tag = document.activeElement?.tagName
  const enCampo = ['INPUT', 'SELECT', 'TEXTAREA'].includes(tag)
  if (e.key === 'Escape') { if (paso.value > 1) paso.value--; return }
  if (e.ctrlKey) {
    switch (e.key.toLowerCase()) {
      case 'f': e.preventDefault(); nextTick(() => inputControlRef.value?.focus()); break
      case 'b': e.preventDefault(); if (paso.value === 1) buscarAlumno(); break
      case 'l': e.preventDefault(); if (paso.value === 1) { busquedaControl.value = ''; busquedaNombre.value = ''; limpiarAlumno() } break
    }
    return
  }
  if (!enCampo && paso.value === 2) {
    const total = gruposFiltrados.value.length
    if (e.key === 'ArrowDown') { e.preventDefault(); filaActiva.value = Math.min(filaActiva.value + 1, total - 1) }
    else if (e.key === 'ArrowUp') { e.preventDefault(); filaActiva.value = Math.max(filaActiva.value - 1, 0) }
    else if (e.key === 'ArrowRight') { e.preventDefault(); nextPage() }
    else if (e.key === 'ArrowLeft') { e.preventDefault(); prevPage() }
    else if (e.key === 'Enter' && filaActiva.value >= 0) {
      const grupo = gruposFiltrados.value[filaActiva.value]
      if (grupo && grupo.inscritos < grupo.capacidad) alternarGrupoSeleccionado(grupo)
    }
  }
}

onMounted(() => {
  cargarGruposDisponibles()
  // ── CORRECCIÓN: cargar periodos desde la API al montar ──
  cargarPeriodos()
  window.addEventListener('keydown', manejarTeclado)
  nextTick(() => paginaRef.value?.focus())
})

watch(paso, (val) => { if (val === 2) cargarGruposDisponibles() })
onUnmounted(() => { window.removeEventListener('keydown', manejarTeclado) })
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap');

/* ============================================= */
/* BASE GENERAL - INSCRIPCIÓN                    */
/* ============================================= */
.inscripcion-page { width: 100%; min-width: 0; box-sizing: border-box; background: #F4F6F9; font-family: 'Montserrat', sans-serif; font-weight: 400; color: #333333; outline: none; text-transform: uppercase; }
.inscripcion-page * { font-family: 'Montserrat', sans-serif; box-sizing: border-box; }

/* ============================================= */
/* BREADCRUMB                                    */
/* ============================================= */
.breadcrumb { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 1rem; color: #828282; font-size: 0.875rem; line-height: 1; }
.breadcrumb-link { display: inline-flex; align-items: center; justify-content: center; color: #828282; font-size: 0.875rem; font-weight: 500; line-height: 1; text-decoration: none; white-space: nowrap; transition: color 0.2s ease; }
.breadcrumb-link:hover { color: #2F80ED; text-decoration: underline; }
.breadcrumb-sep { display: inline-flex; align-items: center; justify-content: center; width: 10px; min-width: 10px; height: 1em; color: #BDBDBD; font-size: 1rem; font-weight: 500; line-height: 1; transform: translateY(-1px); user-select: none; }
.breadcrumb-actual { display: inline-flex; align-items: center; color: #2F80ED; font-size: 0.875rem; font-weight: 600; line-height: 1; white-space: nowrap; }

/* ============================================= */
/* TÍTULOS Y TEXTO                               */
/* ============================================= */
.page-title { color: #333333; font-size: 1.75rem; font-weight: 700; line-height: 1.2; letter-spacing: -0.02em; margin: 0 0 0.4rem; }
.subtitle { color: #4F4F4F; margin: 0 0 1.8rem; font-size: 0.875rem; font-weight: 400; line-height: 1.45; }
.paso-header h3 { color: #333333; font-size: 1.125rem; font-weight: 600; line-height: 1.3; margin: 0 0 4px; }
.paso-desc { color: #4F4F4F; font-size: 0.875rem; font-weight: 400; line-height: 1.45; margin: 0; }

/* ============================================= */
/* TOAST                                         */
/* ============================================= */
.toast { position: fixed; bottom: 2rem; right: 2rem; display: flex; align-items: center; gap: 0.6rem; padding: 0.9rem 1.4rem; border-radius: 10px; color: #FFFFFF; font-size: 0.875rem; font-weight: 600; line-height: 1.4; box-shadow: 0 8px 24px rgba(29,82,183,0.15); z-index: 9999; animation: slideInToast 0.3s ease; }
.toast.success { background: #27AE60; }
.toast.error { background: #EB5757; }
@keyframes slideInToast { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

/* ============================================= */
/* BARRA DE PASOS                                */
/* ============================================= */
.pasos-barra { display: flex; align-items: center; width: 100%; margin-bottom: 1.5rem; padding: 1.2rem 2rem; background: #FFFFFF; border: 1px solid #E0E0E0; border-radius: 12px; box-shadow: 0 4px 14px rgba(29,82,183,0.05); }
.paso { display: flex; align-items: center; gap: 10px; }
.paso-circulo { width: 34px; height: 34px; border-radius: 50%; background: #F2F4F7; color: #828282; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem; transition: all 0.3s ease; }
.paso.activo .paso-circulo { background: #1D52B7; color: #FFFFFF; }
.paso.completado .paso-circulo { background: #27AE60; color: #FFFFFF; }
.paso-label { color: #828282; font-size: 0.8rem; font-weight: 600; line-height: 1.3; letter-spacing: 0.02em; transition: color 0.3s ease; }
.paso.activo .paso-label { color: #1D52B7; }
.paso.completado .paso-label { color: #27AE60; }
.paso-linea { flex: 1; height: 2px; margin: 0 1rem; background: #E0E0E0; transition: background 0.3s ease; }
.paso-linea.completado { background: #27AE60; }

/* ============================================= */
/* CARD DE CONTENIDO                             */
/* ============================================= */
.content-card { max-width: 980px; margin: 0 auto; padding: 2.2rem; background: #FFFFFF; border: 1px solid #E0E0E0; border-radius: 16px; box-shadow: 0 8px 25px rgba(29,82,183,0.05); }

/* ============================================= */
/* CABECERA DE PASOS                             */
/* ============================================= */
.paso-header { display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid #E0E0E0; }
.paso-icono { width: 48px; height: 48px; border-radius: 12px; background: rgba(47,128,237,0.10); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.paso-icono svg { width: 24px; height: 24px; stroke: #1D52B7; }
.icono-verde { background: rgba(39,174,96,0.10); }
.icono-verde svg { stroke: #27AE60; }

/* ============================================= */
/* FORMULARIOS                                   */
/* ============================================= */
.busqueda-row { display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end; margin-bottom: 1.2rem; }
.campo-grupo { display: flex; flex-direction: column; gap: 6px; }
.campo-periodo { min-width: 160px; }
.campo-grupo label { display: flex; align-items: center; gap: 8px; color: #333333; font-size: 0.8rem; font-weight: 600; line-height: 1.3; letter-spacing: 0.02em; }
.etiqueta-principal { background: #0B2545; color: #FFFFFF; font-size: 0.68rem; padding: 2px 8px; border-radius: 10px; font-weight: 600; }
.input-con-icono { position: relative; }
.input-con-icono svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; stroke: #828282; pointer-events: none; }
.input-con-icono input { width: 100%; padding: 12px 14px 12px 40px; border: 1.5px solid #E0E0E0; border-radius: 10px; background: #FFFFFF; color: #333333; font-size: 0.875rem; font-weight: 400; line-height: 1.4; text-transform: uppercase; transition: border-color 0.2s ease, box-shadow 0.2s ease; }
.input-con-icono input::placeholder { color: #828282; font-size: 0.875rem; font-weight: 400; }
.campo-prioritario .input-con-icono input { border-color: #1D52B7; }
.input-con-icono input:focus { outline: none; border-color: #1D52B7; box-shadow: 0 0 0 3px rgba(29,82,183,0.15); }
.select-periodo { width: 100%; padding: 12px 14px; border: 1.5px solid #E0E0E0; border-radius: 10px; background: #FFFFFF; color: #333333; font-size: 0.875rem; font-weight: 400; line-height: 1.4; text-transform: uppercase; }
.select-periodo:focus { outline: none; border-color: #1D52B7; box-shadow: 0 0 0 3px rgba(29,82,183,0.15); }

/* ============================================= */
/* BOTONES                                       */
/* ============================================= */
.busqueda-acciones { display: flex; justify-content: flex-end; margin-bottom: 1.5rem; }
.btn-buscar, .btn-filtrar-inline, .btn-siguiente, .btn-confirmar, .btn-atras, .btn-cambiar, .btn-seleccionar, .btn-elegir, .btn-carga-academica, .btn-exportar-carga, .btn-cerrar-carga-footer, .btn-limpiar-seleccion, .btn-quitar-confirmacion { font-family: 'Montserrat', sans-serif; font-size: 0.85rem; font-weight: 600; line-height: 1.2; letter-spacing: 0.01em; text-transform: uppercase; cursor: pointer; transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease, transform 0.2s ease; }
.btn-buscar, .btn-filtrar-inline, .btn-siguiente { display: flex; align-items: center; justify-content: center; gap: 8px; background: #1D52B7; color: #FFFFFF; border: none; border-radius: 10px; }
.btn-buscar { padding: 12px 32px; }
.btn-filtrar-inline { padding: 9px 20px; white-space: nowrap; }
.btn-siguiente { padding: 9px 22px; }
.btn-buscar:hover:not(:disabled), .btn-filtrar-inline:hover, .btn-siguiente:hover:not(:disabled) { background: #2F80ED; }
.btn-buscar:disabled, .btn-siguiente:disabled, .btn-confirmar:disabled { opacity: 0.55; cursor: not-allowed; }
.btn-cambiar, .btn-atras { display: flex; align-items: center; gap: 6px; background: #FFFFFF; color: #4F4F4F; border: 1px solid #E0E0E0; border-radius: 8px; }
.btn-cambiar { padding: 9px 18px; }
.btn-atras { padding: 11px 22px; border-radius: 10px; }
.btn-cambiar:hover, .btn-atras:hover:not(:disabled) { background: #F2F4F7; }
.btn-atras svg { width: 16px; height: 16px; stroke: #4F4F4F; }
.btn-siguiente svg { width: 16px; height: 16px; stroke: #FFFFFF; }
.btn-seleccionar { background: rgba(47,128,237,0.10); color: #1D52B7; border: 1px solid rgba(47,128,237,0.25); padding: 7px 18px; border-radius: 8px; }
.btn-seleccionar:hover { background: rgba(47,128,237,0.15); }
.btn-confirmar { display: flex; align-items: center; gap: 8px; background: #27AE60; color: #FFFFFF; border: none; padding: 13px 36px; border-radius: 10px; font-weight: 700; }
.btn-confirmar:hover:not(:disabled) { background: #219653; }
.btn-elegir { background: #1D52B7; color: #FFFFFF; border: none; padding: 8px 20px; border-radius: 8px; }
.btn-elegir:hover { background: #2F80ED; }
.btn-elegir.seleccionado { background: #27AE60; color: #FFFFFF; }
.btn-elegir.seleccionado:hover { background: #219653; }

/* ============================================= */
/* RESULTADOS Y ALUMNO SELECCIONADO              */
/* ============================================= */
.resultados-titulo { color: #333333; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.8rem; }
.resultados-lista { margin-top: 0.5rem; display: flex; flex-direction: column; gap: 8px; }
.resultado-item { display: flex; align-items: center; gap: 14px; border: 1px solid #E0E0E0; border-radius: 10px; padding: 12px 16px; cursor: pointer; transition: border-color 0.2s ease, background 0.2s ease; }
.resultado-item:hover { border-color: #1D52B7; background: rgba(29,82,183,0.05); }
.resultado-avatar { width: 40px; height: 40px; border-radius: 50%; background: #1D52B7; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: 700; flex-shrink: 0; }
.resultado-info { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.resultado-info strong { color: #333333; font-size: 0.95rem; font-weight: 600; line-height: 1.3; }
.resultado-info span { color: #4F4F4F; font-size: 0.85rem; font-weight: 400; line-height: 1.45; margin-top: 2px; }
.alumno-seleccionado { display: flex; align-items: center; gap: 16px; background: rgba(39,174,96,0.10); border: 1.5px solid rgba(39,174,96,0.35); border-radius: 12px; padding: 16px 20px; margin-top: 1rem; flex-wrap: wrap; }
.alumno-avatar { width: 46px; height: 46px; border-radius: 50%; background: #27AE60; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 700; flex-shrink: 0; }
.alumno-datos { flex: 1; min-width: 180px; }
.alumno-datos strong { display: block; color: #333333; font-size: 1rem; font-weight: 600; line-height: 1.3; }
.alumno-datos span { color: #4F4F4F; font-size: 0.85rem; font-weight: 400; line-height: 1.45; }
.alumno-acciones { display: flex; gap: 10px; flex-wrap: wrap; }
.btn-carga-academica { display: flex; align-items: center; gap: 6px; background: rgba(47,128,237,0.10); color: #1D52B7; border: 1px solid rgba(47,128,237,0.25); padding: 9px 16px; border-radius: 8px; font-weight: 600; }
.btn-carga-academica:hover { background: rgba(47,128,237,0.15); }
.btn-carga-icono { width: 16px; height: 16px; stroke: #1D52B7; }

/* ============================================= */
/* FILTROS Y TABLA                               */
/* ============================================= */
.filtros-card-inline { display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 1.2rem; padding: 0.75rem 1.1rem; background: #FFFFFF; border: 1px solid #E0E0E0; border-radius: 12px; box-shadow: 0 4px 12px rgba(29,82,183,0.05); }
.filtros-label { display: flex; align-items: center; gap: 5px; color: #4F4F4F; font-size: 0.8rem; font-weight: 600; white-space: nowrap; }
.filtros-label svg { stroke: #4F4F4F; }
.busqueda-grupos-wrap { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 160px; background: #FFFFFF; border: 1px solid #E0E0E0; border-radius: 8px; padding: 0 12px; }
.icono-lupa-gris { stroke: #828282; flex-shrink: 0; }
.input-busqueda-grupos { flex: 1; border: none; background: transparent; color: #333333; padding: 8px 0; font-size: 0.875rem; font-weight: 400; outline: none; text-transform: uppercase; }
.input-busqueda-grupos::placeholder { color: #828282; font-size: 0.875rem; font-weight: 400; }
.btn-limpiar-busqueda-g { background: transparent; border: none; color: #828282; cursor: pointer; font-size: 0.9rem; padding: 2px; line-height: 1; }
.table-container { position: relative; border-radius: 12px; overflow: hidden; border: 1px solid #E0E0E0; background: #FFFFFF; }
.loading-state { opacity: 0.75; pointer-events: none; }
.loading-overlay { position: absolute; inset: 0; z-index: 10; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; min-height: 160px; background: rgba(255,255,255,0.88); border-radius: 12px; color: #1D52B7; font-size: 0.875rem; font-weight: 600; }
.loading-spinner { width: 30px; height: 30px; border: 3px solid #E0E0E0; border-top-color: #1D52B7; border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.inscripcion-table { width: 100%; border-collapse: collapse; }
.inscripcion-table th { background: #F2F4F7; color: #333333; padding: 14px 16px; border-bottom: 1px solid #E0E0E0; text-align: left; font-size: 0.78rem; font-weight: 600; line-height: 1.3; letter-spacing: 0.02em; white-space: nowrap; }
.inscripcion-table td { padding: 14px 16px; border-bottom: 1px solid #E0E0E0; color: #333333; font-size: 0.85rem; font-weight: 400; line-height: 1.4; vertical-align: middle; }
.inscripcion-table tbody tr:hover { background: rgba(29,82,183,0.05); }
.fila-activa { background: rgba(47,128,237,0.10) !important; outline: 2px solid #1D52B7; outline-offset: -2px; }
.fila-seleccionada-multiple { background: rgba(29,82,183,0.15) !important; outline: 2px solid #1D52B7; outline-offset: -2px; }
.text-center { text-align: center; }
.checkbox-grupo { width: 17px; height: 17px; cursor: pointer; accent-color: #1D52B7; }
.horario-badge { display: inline-block; background: rgba(47,128,237,0.10); color: #1D52B7; border: 1px solid rgba(47,128,237,0.25); border-radius: 6px; padding: 3px 9px; font-size: 0.8rem; font-weight: 500; white-space: nowrap; }
.sin-dato { color: #828282; font-size: 0.85rem; }
.lugares-badge { display: inline-block; background: rgba(39,174,96,0.10); color: #27AE60; border: 1px solid rgba(39,174,96,0.35); border-radius: 20px; padding: 4px 12px; font-size: 0.8rem; font-weight: 600; }
.lugares-badge.casi { background: rgba(242,153,74,0.10); color: #F2994A; border-color: rgba(242,153,74,0.35); }
.lugares-badge.lleno { background: #FFF0F0; color: #EB5757; border-color: rgba(235,87,87,0.35); }
.badge-lleno { display: inline-block; background: #F2F4F7; color: #828282; border: 1px solid #E0E0E0; border-radius: 8px; padding: 7px 14px; font-size: 0.82rem; font-weight: 600; }
.sin-resultados { text-align: center; padding: 2rem; color: #4F4F4F; font-size: 0.875rem; }

/* ============================================= */
/* SELECCIÓN MÚLTIPLE                            */
/* ============================================= */
.seleccion-multiple-resumen { display: flex; align-items: center; justify-content: space-between; gap: 1rem; background: rgba(47,128,237,0.10); border: 1px solid rgba(47,128,237,0.25); border-radius: 12px; padding: 12px 16px; margin-bottom: 1rem; }
.seleccion-multiple-resumen strong { display: block; color: #1D52B7; font-size: 0.875rem; font-weight: 600; }
.seleccion-multiple-resumen span { display: block; color: #4F4F4F; font-size: 0.8rem; font-weight: 400; margin-top: 2px; }
.btn-limpiar-seleccion { background: #FFFFFF; color: #1D52B7; border: 1px solid rgba(47,128,237,0.25); padding: 8px 14px; border-radius: 8px; white-space: nowrap; }
.btn-limpiar-seleccion:hover { background: rgba(47,128,237,0.10); }

/* ============================================= */
/* PAGINACIÓN                                    */
/* ============================================= */
.pagination { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-top: 1.2rem; color: #4F4F4F; font-size: 0.875rem; font-weight: 400; }
.pagination-buttons { display: flex; gap: 4px; }
.page-btn { padding: 6px 12px; border: 1px solid #E0E0E0; background: #FFFFFF; border-radius: 6px; cursor: pointer; color: #333333; font-size: 0.85rem; font-weight: 500; }
.page-btn.active { background: #1D52B7; color: #FFFFFF; border-color: #1D52B7; }
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* ============================================= */
/* CONFIRMACIÓN                                  */
/* ============================================= */
.confirmacion-grid { display: flex; align-items: stretch; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; padding: 1.5rem; background: rgba(29,82,183,0.05); border: 1px solid #E0E0E0; border-radius: 12px; }
.confirmacion-grid-multiple { justify-content: space-between; }
.confirmacion-bloque { flex: 1; min-width: 220px; }
.bloque-titulo { color: #828282; font-size: 0.78rem; font-weight: 600; letter-spacing: 0.05em; margin: 0 0 6px; }
.bloque-valor { color: #333333; font-size: 1rem; font-weight: 600; margin: 0 0 4px; }
.bloque-sub { color: #4F4F4F; font-size: 0.85rem; font-weight: 400; margin: 0 0 2px; }
.materias-confirmacion-card { margin-bottom: 1.5rem; background: #FFFFFF; border: 1px solid #E0E0E0; border-radius: 12px; overflow: hidden; }
.materias-confirmacion-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 14px 16px; background: #F2F4F7; border-bottom: 1px solid #E0E0E0; }
.materias-confirmacion-header h4 { margin: 0; color: #333333; font-size: 1rem; font-weight: 600; }
.materias-confirmacion-header span { color: #4F4F4F; font-size: 0.8rem; font-weight: 600; }
.btn-quitar-confirmacion { background: #FFF0F0; color: #EB5757; border: 1px solid rgba(235,87,87,0.35); padding: 7px 12px; border-radius: 8px; }
.btn-quitar-confirmacion:hover { background: rgba(235,87,87,0.10); }
.paso-navegacion { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #E0E0E0; }

/* ============================================= */
/* MODAL CARGA ACADÉMICA                         */
/* ============================================= */
.modal-carga-overlay { position: fixed; inset: 0; background: rgba(11,37,69,0.85); display: flex; align-items: center; justify-content: center; z-index: 2500; padding: 1rem; }
.modal-carga-content { width: min(1050px, 96vw); max-height: 90vh; background: #FFFFFF; border: 1px solid #E0E0E0; border-radius: 16px; box-shadow: 0 25px 60px rgba(11,37,69,0.28); overflow: hidden; display: flex; flex-direction: column; }
.modal-carga-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1.25rem 1.5rem; background: #0B2545; color: #FFFFFF; }
.modal-carga-alumno { display: flex; align-items: center; gap: 1rem; min-width: 0; }
.modal-carga-avatar { width: 52px; height: 52px; border-radius: 50%; background: rgba(255,255,255,0.18); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700; border: 2px solid rgba(255,255,255,0.35); flex-shrink: 0; }
.modal-carga-tag { display: block; margin-bottom: 3px; font-size: 0.72rem; font-weight: 600; letter-spacing: 0.06em; opacity: 0.85; }
.modal-carga-header h3 { margin: 0; font-size: 1.15rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.modal-carga-header p { margin: 3px 0 0; font-size: 0.85rem; font-weight: 400; opacity: 0.9; }
.btn-cerrar-carga { background: rgba(255,255,255,0.15); color: #FFFFFF; border: none; width: 34px; height: 34px; border-radius: 8px; font-size: 1.6rem; line-height: 1; cursor: pointer; transition: background 0.2s ease; flex-shrink: 0; }
.btn-cerrar-carga:hover { background: rgba(255,255,255,0.28); }
.modal-carga-body { padding: 1.25rem 1.5rem; overflow-y: auto; }
.carga-resumen-grid { display: grid; grid-template-columns: repeat(4, minmax(140px, 1fr)); gap: 0.9rem; margin-bottom: 1rem; }
.carga-resumen-card { background: #F4F6F9; border: 1px solid #E0E0E0; border-radius: 12px; padding: 1rem; text-align: center; }
.carga-resumen-numero { display: block; color: #333333; font-size: 2rem; font-weight: 700; line-height: 1.1; }
.carga-resumen-label { display: block; color: #828282; font-size: 0.72rem; font-weight: 600; letter-spacing: 0.05em; margin-top: 4px; }
.modal-carga-acciones { display: flex; justify-content: flex-end; margin-bottom: 1rem; }
.btn-exportar-carga { display: flex; align-items: center; gap: 7px; background: rgba(47,128,237,0.10); color: #1D52B7; border: 1px solid rgba(47,128,237,0.25); padding: 9px 16px; border-radius: 8px; }
.btn-exportar-carga:hover:not(:disabled) { background: rgba(47,128,237,0.15); }
.btn-exportar-carga:disabled { opacity: 0.45; cursor: not-allowed; }
.btn-exportar-icono { width: 17px; height: 17px; stroke: #1D52B7; }
.carga-alumno-table-container { position: relative; max-height: 430px; overflow: auto; }
.carga-modal-loading { min-height: 220px; }
.carga-alumno-table th { white-space: nowrap; }
.carga-alumno-table td { vertical-align: middle; }
.celda-clave-carga { color: #1D52B7; font-weight: 700; white-space: nowrap; }
.celda-materia-carga { font-weight: 600; min-width: 180px; }
.creditos-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 34px; background: rgba(47,128,237,0.10); color: #1D52B7; border: 1px solid rgba(47,128,237,0.25); border-radius: 999px; padding: 4px 10px; font-weight: 700; }
.estatus-carga-badge { display: inline-flex; align-items: center; justify-content: center; background: rgba(39,174,96,0.10); color: #27AE60; border: 1px solid rgba(39,174,96,0.35); border-radius: 999px; padding: 4px 10px; font-weight: 700; font-size: 0.78rem; }
.estado-vacio-carga { text-align: center; padding: 3rem 2rem; color: #4F4F4F; }
.icono-vacio-carga { width: 52px; height: 52px; stroke: #BDBDBD; margin-bottom: 12px; }
.estado-vacio-carga h3 { color: #333333; font-size: 1rem; font-weight: 600; margin: 0 0 6px; }
.estado-vacio-carga p { color: #4F4F4F; font-size: 0.875rem; margin: 0; }
.modal-carga-footer { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.5rem; background: #F4F6F9; border-top: 1px solid #E0E0E0; color: #4F4F4F; font-size: 0.85rem; font-weight: 500; }
.modal-carga-footer strong { color: #1D52B7; font-weight: 700; }
.btn-cerrar-carga-footer { background: #1D52B7; color: #FFFFFF; border: none; padding: 9px 18px; border-radius: 8px; }
.btn-cerrar-carga-footer:hover { background: #2F80ED; }

/* ============================================= */
/* SPINNERS Y TRANSICIONES                       */
/* ============================================= */
.spinner-btn { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.4); border-top-color: #FFFFFF; border-radius: 50%; animation: spin 0.7s linear infinite; }
.content-card > div { animation: fadeSlide 0.25s ease; }
@keyframes fadeSlide { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

/* ============================================= */
/* FOOTER                                        */
/* ============================================= */
.footer-institucional { text-align: center; color: #828282; font-size: 0.82rem; font-weight: 400; padding-top: 2rem; margin-top: 1rem; }

/* ============================================= */
/* RESPONSIVE                                    */
/* ============================================= */
@media (max-width: 700px) {
  .busqueda-row { grid-template-columns: 1fr; }
  .content-card { padding: 1.4rem 1rem; }
  .pasos-barra { padding: 1rem; }
  .paso-label { display: none; }
  .confirmacion-grid { flex-direction: column; }
  .paso-navegacion { flex-direction: column; align-items: stretch; }
  .btn-atras, .btn-siguiente, .btn-confirmar { width: 100%; justify-content: center; }
  .carga-resumen-grid { grid-template-columns: 1fr 1fr; }
  .modal-carga-footer { flex-direction: column; align-items: stretch; }
  .btn-cerrar-carga-footer { width: 100%; }
  .alumno-acciones { width: 100%; }
  .btn-carga-academica, .btn-cambiar, .btn-siguiente { width: 100%; justify-content: center; }
  .seleccion-multiple-resumen { flex-direction: column; align-items: stretch; }
  .btn-limpiar-seleccion { width: 100%; }
  .materias-confirmacion-header { flex-direction: column; align-items: flex-start; }
}
</style>