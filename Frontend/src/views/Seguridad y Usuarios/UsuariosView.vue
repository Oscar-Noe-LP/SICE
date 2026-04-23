<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="usuarios-page">

      <!-- ══ Encabezado ══ -->
      <div class="page-header">
        <div class="header-texto">
          <h1 class="page-title">Usuarios del Sistema</h1>
          <span class="page-subtitle">{{ usuariosFiltrados.length }} registro(s) encontrado(s)</span>
        </div>
      </div>

      <!-- ══ Barra de carga global ══ -->
      <div class="barra-carga-global" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- ══ Notificación UI ══ -->
      <transition name="notif-fade">
        <div v-if="notificacion.visible" class="notificacion-ui" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo === 'exito'" xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- ══ Barra de filtros ══ -->
      <div class="filters-bar">
        <div class="search-group">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Buscar por usuario o nombre completo..."
            v-model="busqueda"
            class="search-input"
            @keydown.escape="busqueda = ''"
          >
          <span v-if="cargandoBusqueda" class="spinner-busqueda"></span>
        </div>

        <select v-model="filtroEstatus" class="filter-select" @change="currentPage = 1">
          <option value="">Todos los estatus</option>
          <option value="Activo">Activo</option>
          <option value="Inactivo">Inactivo</option>
        </select>

        <button class="btn-limpiar" @click="resetFiltros">
          <svg xmlns="http://www.w3.org/2000/svg" class="reset-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Limpiar
        </button>

        <button class="btn-nuevo" @click="abrirModalNuevo">
          + Nuevo Usuario
        </button>
      </div>

      <!-- ══ Tabla ══ -->
      <div class="table-container">

        <div v-if="cargando && usuarios.length === 0" class="estado-cargando">
          <div class="spinner-tabla"></div>
          <p>Cargando registros...</p>
        </div>

        <table
          v-else-if="paginatedUsuarios.length > 0"
          class="alumnos-table"
          ref="tablaRef"
          @keydown="navegarTeclado"
          tabindex="0"
        >
          <thead>
            <tr>
              <th>Nombre de Usuario</th>
              <th>Nombre Completo</th>
              <th>Roles</th>
              <th>Estatus</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(usuario, index) in paginatedUsuarios"
              :key="usuario.id_usuario || usuario.id"
              :class="{ 'fila-seleccionada': filaActiva === index }"
              @click="filaActiva = index"
            >
              <!-- Nombre de usuario con avatar inicial -->
              <td>
                <div class="celda-usuario">
                  <div class="avatar-inicial">{{ inicialUsuario(usuario.nombre_usuario) }}</div>
                  <span class="nombre-usuario-texto">{{ usuario.nombre_usuario }}</span>
                </div>
              </td>

              <td class="celda-nombre-completo">
                {{ usuario.nombre_completo || usuario.persona?.nombre_completo || '—' }}
              </td>

              <!-- Roles como badges -->
              <td>
                <div class="roles-celda">
                  <span
                    v-if="usuario.roles && usuario.roles.length > 0"
                    v-for="(rol, i) in usuario.roles.slice(0, 2)"
                    :key="i"
                    class="rol-badge"
                  >{{ rol }}</span>
                  <span v-if="usuario.roles && usuario.roles.length > 2" class="rol-badge rol-extra">
                    +{{ usuario.roles.length - 2 }}
                  </span>
                  <span v-if="!usuario.roles || usuario.roles.length === 0" class="sin-roles">
                    Sin roles
                  </span>
                </div>
              </td>

              <td>
                <span class="estatus-badge" :class="claseEstatus(usuario.estatus)">
                  {{ usuario.estatus }}
                </span>
              </td>

              <td class="celda-acciones">
                <button class="btn-accion ver" @click.stop="abrirModalVer(usuario)" title="Ver detalles">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Ver
                </button>
                <button class="btn-accion editar" @click.stop="abrirModalEditar(usuario)" title="Editar usuario">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Editar
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <h3>Sin resultados</h3>
          <p>No se encontraron usuarios con los criterios aplicados.</p>
          <button class="btn-limpiar-vacio" @click="resetFiltros">Limpiar filtros</button>
        </div>
      </div>

      <!-- ══ Paginación ══ -->
      <div class="paginacion">
        <div class="paginacion-izquierda">
          Filas por página:
          <select v-model="filasPorPagina" @change="currentPage = 1" class="select-filas">
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
        </div>
        <div class="paginacion-centro">Página {{ currentPage }} de {{ totalPages }}</div>
        <div class="paginacion-derecha">
          <button class="btn-pag" @click="prevPage" :disabled="currentPage === 1">‹</button>
          <button
            v-for="page in visiblePages" :key="page"
            class="btn-pag" :class="{ activo: page === currentPage }"
            @click="goToPage(page)"
          >{{ page }}</button>
          <button class="btn-pag" @click="nextPage" :disabled="currentPage === totalPages">›</button>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════
         MODAL: VER USUARIO (solo lectura)
    ═══════════════════════════════════════ -->
    <div v-if="showModalVer" class="modal-overlay" @click.self="cerrarModalVer">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Detalles del Usuario</h3>
          <button @click="cerrarModalVer" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">

          <!-- Avatar grande en el modal -->
          <div class="avatar-modal-wrap">
            <div class="avatar-modal">{{ inicialUsuario(usuarioVer.nombre_usuario) }}</div>
            <span class="estatus-badge" :class="claseEstatus(usuarioVer.estatus)">
              {{ usuarioVer.estatus }}
            </span>
          </div>

          <div class="detalle-fila">
            <span class="detalle-label">Nombre de Usuario</span>
            <span class="detalle-valor">{{ usuarioVer.nombre_usuario }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Nombre Completo</span>
            <span class="detalle-valor">{{ usuarioVer.nombre_completo || '—' }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Roles asignados</span>
            <div class="roles-celda roles-modal">
              <span v-if="usuarioVer.roles && usuarioVer.roles.length > 0"
                    v-for="(rol, i) in usuarioVer.roles" :key="i" class="rol-badge">
                {{ rol }}
              </span>
              <span v-else class="sin-roles">Sin roles asignados</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModalVer">Cerrar</button>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════
         MODAL: CREAR / EDITAR USUARIO
    ═══════════════════════════════════════ -->
    <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-content modal-editar">
        <div class="modal-header">
          <h3>{{ usuarioEditar.id_usuario ? 'Editar Usuario' : 'Nuevo Usuario' }}</h3>
          <button @click="cerrarModal" class="btn-cerrar-modal">×</button>
        </div>

        <div class="modal-body">

          <!-- Datos del usuario -->
          <div class="form-grupo">
            <label>Nombre de Usuario <span class="obligatorio">*</span></label>
            <input
              v-model="usuarioEditar.nombre_usuario"
              type="text"
              class="modal-input"
              :class="{ 'input-error': erroresModal.nombre_usuario }"
              placeholder="Ej. jgarcia26"
              @input="validarCampoModal('nombre_usuario')"
            >
            <small v-if="erroresModal.nombre_usuario" class="mensaje-error">
              {{ erroresModal.nombre_usuario }}
            </small>
          </div>

          <!-- Solo en creación: campo de contraseña inicial -->
          <div v-if="!usuarioEditar.id_usuario" class="form-grupo">
            <label>Contraseña inicial <span class="obligatorio">*</span></label>
            <div class="input-password-wrap">
              <input
                v-model="usuarioEditar.contrasena"
                :type="verContrasena ? 'text' : 'password'"
                class="modal-input"
                :class="{ 'input-error': erroresModal.contrasena }"
                placeholder="Mínimo 8 caracteres"
                @input="validarCampoModal('contrasena')"
              >
              <button type="button" class="btn-ver-pass" @click="verContrasena = !verContrasena">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path v-if="!verContrasena" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
              </button>
            </div>
            <small v-if="erroresModal.contrasena" class="mensaje-error">{{ erroresModal.contrasena }}</small>
          </div>

          <div class="form-grupo">
            <label>Estatus <span class="obligatorio">*</span></label>
            <select v-model="usuarioEditar.estatus" class="modal-select">
              <option value="Activo">Activo</option>
              <option value="Inactivo">Inactivo</option>
            </select>
          </div>

          <!-- Roles asignados con checkboxes (igual al modal de permisos en RolesView) -->
          <div class="form-grupo">
            <label class="label-seccion">
              <svg xmlns="http://www.w3.org/2000/svg" class="label-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
              </svg>
              Roles Asignados
            </label>
            <div class="grilla-roles">
              <label
                v-for="(rol, i) in rolesDisponibles" :key="i"
                class="rol-check-item"
                :class="{ 'rol-check-activo': rolesSeleccionados[rol.nombre] }"
              >
                <input
                  type="checkbox"
                  class="rol-checkbox"
                  v-model="rolesSeleccionados[rol.nombre]"
                >
                <div class="rol-check-info">
                  <span class="rol-check-nombre">{{ rol.nombre }}</span>
                  <span class="rol-check-desc">{{ rol.descripcion }}</span>
                </div>
              </label>
            </div>
          </div>
        </div>

        <div class="modal-footer modal-footer-usuario">
          <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">Cancelar</button>

          <!-- Botón cambiar contraseña (solo en edición) -->
          <button
            v-if="usuarioEditar.id_usuario"
            class="btn-cambiar-pass"
            @click="abrirModalContrasena"
            :disabled="guardando"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="pass-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
            Cambiar contraseña
          </button>

          <button class="btn-guardar" @click="guardarUsuario" :disabled="guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════
         MODAL: CAMBIAR CONTRASEÑA (segundo modal)
    ═══════════════════════════════════════ -->
    <div v-if="showModalContrasena" class="modal-overlay modal-overlay-top" @click.self="cerrarModalContrasena">
      <div class="modal-content modal-contrasena">
        <div class="modal-header">
          <h3>Cambiar Contraseña</h3>
          <button @click="cerrarModalContrasena" class="btn-cerrar-modal">×</button>
        </div>

        <div class="modal-body">
          <p class="contrasena-info">
            Ingresa la nueva contraseña para el usuario
            <strong>{{ usuarioEditar.nombre_usuario }}</strong>.
          </p>

          <div class="form-grupo">
            <label>Nueva contraseña <span class="obligatorio">*</span></label>
            <div class="input-password-wrap">
              <input
                v-model="datosContrasena.nueva"
                :type="verNuevaPass ? 'text' : 'password'"
                class="modal-input"
                :class="{ 'input-error': erroresContrasena.nueva }"
                placeholder="Mínimo 8 caracteres"
                @input="validarContrasena('nueva')"
              >
              <button type="button" class="btn-ver-pass" @click="verNuevaPass = !verNuevaPass">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </div>
            <small v-if="erroresContrasena.nueva" class="mensaje-error">{{ erroresContrasena.nueva }}</small>
          </div>

          <div class="form-grupo">
            <label>Confirmar contraseña <span class="obligatorio">*</span></label>
            <div class="input-password-wrap">
              <input
                v-model="datosContrasena.confirmar"
                :type="verConfirmarPass ? 'text' : 'password'"
                class="modal-input"
                :class="{ 'input-error': erroresContrasena.confirmar }"
                placeholder="Repite la nueva contraseña"
                @input="validarContrasena('confirmar')"
              >
              <button type="button" class="btn-ver-pass" @click="verConfirmarPass = !verConfirmarPass">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </div>
            <small v-if="erroresContrasena.confirmar" class="mensaje-error">{{ erroresContrasena.confirmar }}</small>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModalContrasena" :disabled="guardandoPass">Cancelar</button>
          <button class="btn-guardar" @click="guardarContrasena" :disabled="guardandoPass">
            <span v-if="guardandoPass" class="spinner-btn"></span>
            {{ guardandoPass ? 'Guardando...' : 'Guardar' }}
          </button>
        </div>
      </div>
    </div>

  </MainLayout>
</template>



<script setup>
import { ref, computed, onMounted } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

// ── URL base del backend (variable de entorno) ──────────────────────
const API_URL = import.meta.env.VITE_API_URL

const usuarios        = ref([])
const rolesDisponibles = ref([])

const cargando        = ref(false)
const cargandoBusqueda = ref(false)
const guardando       = ref(false)
const guardandoPass   = ref(false)

const busqueda       = ref('')
const filtroEstatus  = ref('')
const filasPorPagina = ref(10)
const currentPage    = ref(1)
const filaActiva     = ref(-1)
const tablaRef       = ref(null)

// Modales
const showModalVer       = ref(false)
const showModal          = ref(false)
const showModalContrasena = ref(false)

const usuarioVer    = ref({})
const usuarioEditar = ref({})
const erroresModal  = ref({})
const datosContrasena  = ref({ nueva: '', confirmar: '' })
const erroresContrasena = ref({})

const verContrasena    = ref(false)
const verNuevaPass     = ref(false)
const verConfirmarPass = ref(false)

const rolesSeleccionados = ref({})

// Notificación
const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  notificacion.value = { visible: true, mensaje, tipo }
  setTimeout(() => { notificacion.value.visible = false }, 3500)
}

// ── Cargar usuarios ───────────────────────────────────────────────────
// Endpoint: GET /api/usuarios
const cargarUsuarios = async () => {
  cargando.value = true
  try {
    const res = await fetch(`${API_URL}/api/usuarios`)
    if (!res.ok) throw new Error('Error del servidor')
    usuarios.value = await res.json()
  } catch (e) {
    console.error(e)
    mostrarNotificacion('No se pudo cargar usuarios', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Cargar roles disponibles ──────────────────────────────────────────
// Endpoint: GET /api/roles-simple
const cargarRoles = async () => {
  try {
    const res = await fetch(`${API_URL}/api/roles-simple`)
    if (res.ok) {
      rolesDisponibles.value = await res.json()
    } else {
      console.error('Error al cargar roles')
    }
  } catch (e) {
    console.error('Error cargando roles:', e)
  }
}

onMounted(() => {
  cargarUsuarios()
  cargarRoles()
})

// ── Modales ───────────────────────────────────────────────────────────
const abrirModalNuevo = () => {
  usuarioEditar.value = { nombre_usuario: '', contrasena: '', estatus: 'Activo' }
  rolesSeleccionados.value = {}
  erroresModal.value = {}
  showModal.value = true
}

const abrirModalEditar = (usuario) => {
  usuarioEditar.value = {
    id_usuario:     usuario.id_usuario,
    nombre_usuario: usuario.nombre_usuario,
    estatus:        usuario.estatus || 'Activo'
  }
  erroresModal.value = {}

  rolesSeleccionados.value = {}
  rolesDisponibles.value.forEach(rol => {
    rolesSeleccionados.value[rol.nombre] = (usuario.roles || []).includes(rol.nombre)
  })

  showModal.value = true
}

const abrirModalVer = (usuario) => {
  usuarioVer.value = { ...usuario }
  showModalVer.value = true
}

const cerrarModal           = () => { showModal.value = false }
const cerrarModalVer        = () => { showModalVer.value = false }
const cerrarModalContrasena = () => { showModalContrasena.value = false }

const abrirModalContrasena = () => {
  datosContrasena.value  = { nueva: '', confirmar: '' }
  erroresContrasena.value = {}
  verNuevaPass.value     = false
  verConfirmarPass.value = false
  showModalContrasena.value = true
}

// ── Guardar usuario ───────────────────────────────────────────────────
// Endpoints: POST /api/usuarios  |  PUT /api/usuarios/{id}
const guardarUsuario = async () => {
  if (!usuarioEditar.value.nombre_usuario?.trim()) {
    mostrarNotificacion('El nombre de usuario es obligatorio', 'error')
    return
  }

  const esEdicion = !!usuarioEditar.value.id_usuario
  const url = esEdicion
    ? `${API_URL}/api/usuarios/${usuarioEditar.value.id_usuario}`
    : `${API_URL}/api/usuarios`

  const rolesActivos = Object.keys(rolesSeleccionados.value)
    .filter(key => rolesSeleccionados.value[key])

  const payload = {
    nombre_usuario: usuarioEditar.value.nombre_usuario.trim(),
    estatus:        usuarioEditar.value.estatus,
    roles:          rolesActivos
  }

  if (!esEdicion && usuarioEditar.value.contrasena) {
    payload.contrasena = usuarioEditar.value.contrasena
  }

  guardando.value = true
  try {
    const response = await fetch(url, {
      method:  esEdicion ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(payload)
    })

    if (response.ok) {
      await cargarUsuarios()
      cerrarModal()
      mostrarNotificacion(
        esEdicion ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente',
        'exito'
      )
    } else {
      const data = await response.json().catch(() => ({}))
      mostrarNotificacion(data.error || 'Error al guardar', 'error')
    }
  } catch (error) {
    console.error(error)
    mostrarNotificacion('Error de conexión con el servidor', 'error')
  } finally {
    guardando.value = false
  }
}

// ── Cambiar contraseña ────────────────────────────────────────────────
// Endpoint: PUT /api/usuarios/{id}/contrasena
const guardarContrasena = async () => {
  if (!datosContrasena.value.nueva || datosContrasena.value.nueva.length < 8) {
    mostrarNotificacion('La contraseña debe tener al menos 8 caracteres', 'error')
    return
  }
  if (datosContrasena.value.nueva !== datosContrasena.value.confirmar) {
    mostrarNotificacion('Las contraseñas no coinciden', 'error')
    return
  }

  const id = usuarioEditar.value.id_usuario
  if (!id) return

  guardandoPass.value = true
  try {
    const response = await fetch(`${API_URL}/api/usuarios/${id}/contrasena`, {
      method:  'PUT',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ contrasena: datosContrasena.value.nueva })
    })

    if (response.ok) {
      cerrarModalContrasena()
      mostrarNotificacion('Contraseña actualizada correctamente', 'exito')
    } else {
      mostrarNotificacion('Error al cambiar la contraseña', 'error')
    }
  } catch (error) {
    console.error(error)
    mostrarNotificacion('Error de conexión', 'error')
  } finally {
    guardandoPass.value = false
  }
}

// ── Helpers ───────────────────────────────────────────────────────────
const claseEstatus   = (estatus) => String(estatus || '').toLowerCase()
const inicialUsuario = (nombre)  => nombre ? nombre.charAt(0).toUpperCase() : '?'

// ── Filtros y paginación ──────────────────────────────────────────────
const usuariosFiltrados = computed(() => {
  return usuarios.value.filter(u => {
    const coincideBusqueda = !busqueda.value ||
      (u.nombre_usuario || '').toLowerCase().includes(busqueda.value.toLowerCase()) ||
      (u.nombre_completo || '').toLowerCase().includes(busqueda.value.toLowerCase())

    const coincideEstatus = !filtroEstatus.value || u.estatus === filtroEstatus.value

    return coincideBusqueda && coincideEstatus
  })
})

const totalPages = computed(() =>
  Math.ceil(usuariosFiltrados.value.length / filasPorPagina.value) || 1
)
const paginatedUsuarios = computed(() => {
  const start = (currentPage.value - 1) * filasPorPagina.value
  return usuariosFiltrados.value.slice(start, start + filasPorPagina.value)
})
const visiblePages = computed(() => {
  const total = totalPages.value, current = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  return [1, current - 1, current, current + 1, total].filter(p => p >= 1 && p <= total)
})

const goToPage = (page) => { currentPage.value = page }
const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

const resetFiltros = () => {
  busqueda.value      = ''
  filtroEstatus.value = ''
  currentPage.value   = 1
}

// ── Navegación por teclado ────────────────────────────────────────────
const navegarTeclado = (e) => {
  const total = paginatedUsuarios.value.length
  if (total === 0) return
  if (e.key === 'ArrowDown') { e.preventDefault(); filaActiva.value = Math.min(filaActiva.value + 1, total - 1) }
  else if (e.key === 'ArrowUp') { e.preventDefault(); filaActiva.value = Math.max(filaActiva.value - 1, 0) }
  else if (e.key === 'Enter' && filaActiva.value >= 0) { e.preventDefault(); abrirModalVer(paginatedUsuarios.value[filaActiva.value]) }
}

// ── Validaciones inline (para los inputs del modal) ───────────────────
const validarCampoModal = (campo) => {
  if (campo === 'nombre_usuario') {
    if (!usuarioEditar.value.nombre_usuario?.trim())
      erroresModal.value.nombre_usuario = 'Obligatorio'
    else
      delete erroresModal.value.nombre_usuario
  }
  if (campo === 'contrasena') {
    if (!usuarioEditar.value.contrasena || usuarioEditar.value.contrasena.length < 8)
      erroresModal.value.contrasena = 'Mínimo 8 caracteres'
    else
      delete erroresModal.value.contrasena
  }
}

const validarContrasena = (campo) => {
  if (campo === 'nueva') {
    if (!datosContrasena.value.nueva || datosContrasena.value.nueva.length < 8)
      erroresContrasena.value.nueva = 'Mínimo 8 caracteres'
    else
      delete erroresContrasena.value.nueva
  }
  if (campo === 'confirmar') {
    if (datosContrasena.value.confirmar !== datosContrasena.value.nueva)
      erroresContrasena.value.confirmar = 'No coinciden'
    else
      delete erroresContrasena.value.confirmar
  }
}
</script>




<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.usuarios-page {
  --azul:        #1B396A;
  --azul-hover:  #1D4ED8;
  --azul-suave:  #DBEAFE;
  --borde:       #E5E7EB;
  --fondo:       #F5F5F5;
  --texto:       #1A1A1A;
  --gris:        #6B7280;

  max-width: 100%;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
}

/* ══ Encabezado ══ */
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.2rem; }
.page-title  { color: var(--texto); font-size: 1.75rem; font-weight: 700; letter-spacing: -0.02em; margin: 0; }
.page-subtitle { font-size: 0.9rem; color: var(--gris); font-weight: 500; display: block; margin-top: 2px; }

/* ══ Barra de carga ══ */
.barra-carga-global { height: 3px; background: transparent; border-radius: 2px; margin-bottom: 1rem; overflow: hidden; opacity: 0; transition: opacity 0.3s; }
.barra-carga-global.visible { opacity: 1; }
.barra-progreso { height: 100%; width: 40%; background: #1B396A; border-radius: 2px; animation: deslizar 1.2s ease-in-out infinite; }
@keyframes deslizar { 0% { transform: translateX(-100%); } 100% { transform: translateX(350%); } }

/* ══ Notificación ══ */
.notificacion-ui { display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 10px; font-size: 0.93rem; font-weight: 500; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.notificacion-ui.exito { background: #DCFCE7; color: #16A34A; border: 1px solid #86EFAC; }
.notificacion-ui.error { background: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5; }
.notif-icono { width: 20px; height: 20px; flex-shrink: 0; }
.notif-fade-enter-active, .notif-fade-leave-active { transition: all 0.35s ease; }
.notif-fade-enter-from, .notif-fade-leave-to { opacity: 0; transform: translateY(-8px); }

/* ══ Filtros ══ */
.filters-bar { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.2rem; flex-wrap: wrap; }
.search-group { position: relative; flex: 0 0 320px; min-width: 220px; }
.search-input { width: 100%; padding: 10px 14px 10px 42px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.93rem; background: #FFFFFF; color: var(--texto); font-family: 'Montserrat', sans-serif; outline: none; transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box; }
.search-input:focus { border-color: #1B396A; box-shadow: 0 0 0 3px #DBEAFE; }
.search-input::placeholder { color: #9CA3AF; }
.search-icon-svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; stroke: var(--gris); pointer-events: none; }
.spinner-busqueda { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; border: 2px solid #E5E7EB; border-top-color: #1B396A; border-radius: 50%; animation: girar 0.7s linear infinite; }
.filter-select { padding: 10px 12px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.92rem; background: #FFFFFF; color: var(--texto); font-family: 'Montserrat', sans-serif; cursor: pointer; outline: none; }
.filter-select:focus { border-color: #1B396A; }
.btn-limpiar { display: flex; align-items: center; gap: 6px; background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); padding: 10px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.92rem; white-space: nowrap; font-family: 'Montserrat', sans-serif; transition: background 0.15s; }
.btn-limpiar:hover { background: var(--fondo); }
.reset-icon { width: 16px; height: 16px; stroke: var(--gris); }
.btn-nuevo { background: #1B396A; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; white-space: nowrap; font-family: 'Montserrat', sans-serif; font-size: 0.92rem; transition: background 0.2s; margin-left: auto; }
.btn-nuevo:hover { background: #1D4ED8; }

/* ══ Tabla ══ */
.table-container { background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid var(--borde); }
.alumnos-table { width: 100%; border-collapse: collapse; outline: none; }
.alumnos-table th { background: var(--fondo); padding: 12px 16px; text-align: left; font-weight: 600; font-size: 0.88rem; color: var(--texto); border-bottom: 1px solid var(--borde); font-family: 'Montserrat', sans-serif; white-space: nowrap; }
.alumnos-table td { padding: 11px 16px; border-bottom: 1px solid var(--borde); color: var(--texto); font-size: 0.93rem; font-family: 'Montserrat', sans-serif; }
.alumnos-table tbody tr { transition: background 0.15s; cursor: pointer; }
.alumnos-table tbody tr:hover { background: #F8FAFC; }
.alumnos-table tbody tr:last-child td { border-bottom: none; }
.fila-seleccionada { background: #DBEAFE !important; }

/* Celda de usuario con avatar ══ */
.celda-usuario { display: flex; align-items: center; gap: 10px; }
.avatar-inicial { width: 34px; height: 34px; border-radius: 50%; background: #1B396A; color: white; font-size: 0.95rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-family: 'Montserrat', sans-serif; }
.nombre-usuario-texto { font-weight: 600; color: #1B396A; font-size: 0.92rem; }
.celda-nombre-completo { color: var(--gris); font-size: 0.88rem; }

/* Roles en celda ══ */
.roles-celda { display: flex; flex-wrap: wrap; gap: 5px; }
.roles-modal  { margin-top: 4px; }
.rol-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; background: #DBEAFE; color: #1B396A; font-family: 'Montserrat', sans-serif; white-space: nowrap; }
.rol-extra { background: #F5F5F5; color: var(--gris); }
.sin-roles { font-size: 0.82rem; color: #9CA3AF; font-style: italic; }

/* Estatus ══ */
.estatus-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.83rem; font-weight: 600; font-family: 'Montserrat', sans-serif; }
.estatus-badge.activo   { background: #DCFCE7; color: #16A34A; }
.estatus-badge.inactivo { background: #FEE2E2; color: #DC2626; }

/* Acciones ══ */
.celda-acciones { display: flex; gap: 6px; align-items: center; }
.btn-accion { display: flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 6px; font-size: 0.83rem; cursor: pointer; font-weight: 600; font-family: 'Montserrat', sans-serif; transition: background 0.15s; white-space: nowrap; }
.btn-accion svg { width: 14px; height: 14px; }
.btn-accion.ver    { background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); }
.btn-accion.ver:hover { background: var(--fondo); }
.btn-accion.editar { background: #1B396A; color: white; border: 1px solid #1B396A; }
.btn-accion.editar:hover { background: #1D4ED8; border-color: #1D4ED8; }

/* Estados vacío y cargando ══ */
.estado-vacio, .estado-cargando { text-align: center; padding: 3.5rem 2rem; color: var(--gris); }
.icono-vacio { width: 56px; height: 56px; stroke: #9CA3AF; margin-bottom: 12px; }
.estado-vacio h3 { font-size: 1.2rem; color: var(--texto); margin: 0 0 6px; }
.estado-vacio p  { font-size: 0.93rem; margin: 0 0 1.2rem; }
.btn-limpiar-vacio { background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); padding: 9px 20px; border-radius: 8px; font-weight: 500; cursor: pointer; font-family: 'Montserrat', sans-serif; }
.spinner-tabla { display: inline-block; width: 36px; height: 36px; border: 3px solid #E5E7EB; border-top-color: #1B396A; border-radius: 50%; animation: girar 0.8s linear infinite; margin-bottom: 12px; }

/* ══ Paginación ══ */
.paginacion { margin-top: 1.2rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; color: var(--gris); font-family: 'Montserrat', sans-serif; flex-wrap: wrap; gap: 0.5rem; }
.paginacion-izquierda, .paginacion-centro, .paginacion-derecha { display: flex; align-items: center; gap: 8px; }
.select-filas { border: 1px solid var(--borde); border-radius: 6px; padding: 4px 8px; font-size: 0.9rem; background: #FFFFFF; font-family: 'Montserrat', sans-serif; }
.btn-pag { padding: 5px 11px; border: 1px solid var(--borde); background: #FFFFFF; border-radius: 6px; cursor: pointer; color: var(--texto); font-family: 'Montserrat', sans-serif; font-size: 0.9rem; transition: background 0.15s; }
.btn-pag:hover:not(:disabled) { background: var(--fondo); }
.btn-pag:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-pag.activo { background: #1B396A; color: white; border-color: #1B396A; }

/* ══ Modales ══ */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.modal-overlay-top { z-index: 2100; }
.modal-content { background: #FFFFFF; width: 520px; max-width: 92%; border-radius: 14px; box-shadow: 0 20px 50px rgba(0,0,0,0.18); overflow: hidden; border: 1px solid var(--borde); max-height: 90vh; display: flex; flex-direction: column; }
.modal-editar { width: 560px; }
.modal-contrasena { width: 460px; }

.modal-header { background: #1B396A; color: white; padding: 1.1rem 1.6rem; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
.modal-header h3 { margin: 0; font-size: 1.2rem; font-weight: 700; font-family: 'Montserrat', sans-serif; }
.btn-cerrar-modal { background: none; border: none; color: white; font-size: 1.7rem; cursor: pointer; line-height: 1; opacity: 0.85; transition: opacity 0.15s; }
.btn-cerrar-modal:hover { opacity: 1; }

.modal-body { padding: 1.6rem; overflow-y: auto; flex: 1; }

/* Avatar en modal ver ══ */
.avatar-modal-wrap { display: flex; flex-direction: column; align-items: center; gap: 10px; padding-bottom: 1.2rem; margin-bottom: 0.4rem; border-bottom: 1px solid var(--borde); }
.avatar-modal { width: 64px; height: 64px; border-radius: 50%; background: #1B396A; color: white; font-size: 1.6rem; font-weight: 700; display: flex; align-items: center; justify-content: center; font-family: 'Montserrat', sans-serif; }

/* Detalle solo lectura ══ */
.detalle-fila { display: flex; justify-content: space-between; align-items: center; padding: 11px 0; border-bottom: 1px solid var(--borde); font-size: 0.95rem; color: var(--texto); font-family: 'Montserrat', sans-serif; gap: 1rem; }
.detalle-fila:last-child { border-bottom: none; }
.detalle-label { font-weight: 600; color: var(--gris); white-space: nowrap; }
.detalle-valor { font-weight: 500; text-align: right; }

/* Formulario en modales ══ */
.form-grupo { margin-bottom: 1.2rem; }
.form-grupo label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: var(--texto); font-family: 'Montserrat', sans-serif; }
.obligatorio { color: #DC2626; }
.modal-input, .modal-select { width: 100%; padding: 10px 14px; border: 1.5px solid var(--borde); border-radius: 8px; font-size: 0.95rem; background: #FFFFFF; color: var(--texto); font-family: 'Montserrat', sans-serif; outline: none; transition: border-color 0.2s; box-sizing: border-box; }
.modal-input:focus, .modal-select:focus { border-color: #1B396A; }
.input-error { border-color: #DC2626 !important; }
.mensaje-error { color: #DC2626; font-size: 0.82rem; margin-top: 4px; display: block; font-family: 'Montserrat', sans-serif; }

/* Input con botón mostrar/ocultar contraseña ══ */
.input-password-wrap { position: relative; }
.input-password-wrap .modal-input { padding-right: 44px; }
.btn-ver-pass { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 0; display: flex; align-items: center; }
.btn-ver-pass svg { width: 18px; height: 18px; stroke: var(--gris); }

/* Sección de roles ══ */
.label-seccion { display: flex; align-items: center; gap: 6px; font-weight: 600; font-size: 0.9rem; color: var(--texto); font-family: 'Montserrat', sans-serif; margin-bottom: 10px !important; }
.label-icono { width: 16px; height: 16px; stroke: #1B396A; }
.grilla-roles { display: grid; grid-template-columns: 1fr 1fr; gap: 0.55rem; }
.rol-check-item { display: flex; align-items: flex-start; gap: 9px; padding: 9px 11px; border-radius: 8px; border: 1px solid var(--borde); cursor: pointer; transition: background 0.15s, border-color 0.15s; background: #FFFFFF; }
.rol-check-item:hover { background: #F8FAFC; }
.rol-check-activo { background: #EFF6FF !important; border-color: #BFDBFE !important; }
.rol-checkbox { width: 15px; height: 15px; margin-top: 2px; cursor: pointer; accent-color: #1B396A; flex-shrink: 0; }
.rol-check-info { display: flex; flex-direction: column; gap: 2px; }
.rol-check-nombre { font-size: 0.86rem; font-weight: 600; color: var(--texto); font-family: 'Montserrat', sans-serif; }
.rol-check-desc   { font-size: 0.76rem; color: var(--gris); font-family: 'Montserrat', sans-serif; }

/* Aviso en modal contraseña ══ */
.contrasena-info { font-size: 0.9rem; color: var(--gris); margin: 0 0 1.2rem; font-family: 'Montserrat', sans-serif; line-height: 1.5; }
.contrasena-info strong { color: var(--texto); }

/* Footer de modales ══ */
.modal-footer { padding: 1rem 1.6rem; background: var(--fondo); display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid var(--borde); flex-shrink: 0; }
.modal-footer-usuario { justify-content: space-between; align-items: center; }

.btn-secundario { padding: 10px 22px; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); transition: background 0.15s; }
.btn-secundario:hover { background: var(--fondo); }
.btn-secundario:disabled { opacity: 0.5; cursor: not-allowed; }

/* Botón cambiar contraseña ══ */
.btn-cambiar-pass { display: flex; align-items: center; gap: 7px; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #DBEAFE; color: #1B396A; border: 1px solid #BFDBFE; font-size: 0.88rem; transition: background 0.15s; }
.btn-cambiar-pass:hover { background: #BFDBFE; }
.btn-cambiar-pass:disabled { opacity: 0.5; cursor: not-allowed; }
.pass-icono { width: 16px; height: 16px; stroke: #1B396A; flex-shrink: 0; }

.btn-guardar { display: flex; align-items: center; gap: 8px; padding: 10px 22px; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; background: #1B396A; color: white; border: none; transition: background 0.15s; }
.btn-guardar:hover:not(:disabled) { background: #1D4ED8; }
.btn-guardar:disabled { opacity: 0.65; cursor: not-allowed; }

.spinner-btn { display: inline-block; width: 15px; height: 15px; border: 2px solid rgba(255,255,255,0.4); border-top-color: white; border-radius: 50%; animation: girar 0.7s linear infinite; flex-shrink: 0; }

@keyframes girar { to { transform: rotate(360deg); } }
</style>
