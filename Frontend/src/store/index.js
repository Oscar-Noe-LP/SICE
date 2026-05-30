import { createStore } from 'vuex'

const store = createStore({
  state() {
    return {
      // ==================== DATOS GLOBALES ====================
      usuarioActual: null,
      authToken: null,

      // Alumnos generales del sistema
      alumnos: [
        { 
          noControl: '21456987', 
          nombre: 'Sara Pérez López', 
          carrera: 'Ingeniería en Sistemas Computacionales', 
          semestre: 6, 
          estado: 'Activo',
          correo: 'sara.perez@email.com'
        },
        { 
          noControl: '21463254', 
          nombre: 'Juan García Morales', 
          carrera: 'Ingeniería Industrial', 
          semestre: 4, 
          estado: 'Activo',
          correo: 'juan.garcia@email.com'
        },
        { 
          noControl: '21454128', 
          nombre: 'Mariela Gómez Ruiz', 
          carrera: 'Ingeniería Civil', 
          semestre: 8, 
          estado: 'Activo',
          correo: 'mariela.gomez@email.com'
        },
        { 
          noControl: '21454321', 
          nombre: 'Ana Rodríguez Torres', 
          carrera: 'Licenciatura en Administración', 
          semestre: 2, 
          estado: 'Activo',
          correo: 'ana.rodriguez@email.com'
        }
      ],

      // ==================== MÓDULO ASPIRANTES ====================
      aspirantes: [
        { 
          folio: 'ASP-001', 
          nombre: 'KARLA MENDOZA RUIZ', 
          curp: 'MERK020315MSLNDRA4', 
          carrera: 'Ingeniería en Sistemas Computacionales', 
          estatus: 'ACEPTADO', 
          fechaReg: '2026-03-10',
          promedio: 9.2,
          correo: 'karla@email.com'
        },
        { 
          folio: 'ASP-002', 
          nombre: 'LUIS HERNÁNDEZ TORRES', 
          curp: 'HETL031120HSLRRSA8', 
          carrera: 'Ingeniería Industrial', 
          estatus: 'EN REVISIÓN', 
          fechaReg: '2026-03-12',
          promedio: 8.5,
          correo: 'luis@email.com'
        },
        { 
          folio: 'ASP-003', 
          nombre: 'DANIELA CASTILLO VEGA', 
          curp: 'CAVD040820MSLSGA1', 
          carrera: 'Ingeniería Civil', 
          estatus: 'REGISTRADO', 
          fechaReg: '2026-03-15',
          promedio: 7.8,
          correo: 'daniela@email.com'
        }
      ],

      // Fichas de aspirantes
      fichas: [],

      // Solicitudes de aspirantes
      solicitudes: [],

      // ==================== OTROS MÓDULOS ====================
      grupos: [],
      calificaciones: {},
      configuracionAdmision: {
        nombreProceso: 'Admisión 2026-2',
        ciclo: '2026-2',
        institucion: 'TecNM Campus San Luis Potosí',
        costoFicha: '$250.00 MXN',
        fechas: {
          inicioRegistro: '2026-06-01',
          finRegistro: '2026-07-15',
          fechaExamen: '2026-07-25'
        }
      }
    }
  },

  mutations: {
    // Usuario
    setUsuario(state, usuario) {
      state.usuarioActual = usuario
    },
    logout(state) {
      state.usuarioActual = null
      state.authToken = null
    },

    // Alumnos
    agregarAlumno(state, alumno) {
      state.alumnos.push(alumno)
    },
    actualizarAlumno(state, alumnoActualizado) {
      const index = state.alumnos.findIndex(a => a.noControl === alumnoActualizado.noControl)
      if (index !== -1) {
        state.alumnos.splice(index, 1, alumnoActualizado)
      }
    },

    // Aspirantes
    agregarAspirante(state, aspirante) {
      state.aspirantes.push(aspirante)
    },
    actualizarAspirante(state, aspiranteActualizado) {
      const index = state.aspirantes.findIndex(a => a.folio === aspiranteActualizado.folio)
      if (index !== -1) {
        state.aspirantes.splice(index, 1, aspiranteActualizado)
      }
    },
    cambiarEstatusAspirante(state, { folio, estatus }) {
      const aspirante = state.aspirantes.find(a => a.folio === folio)
      if (aspirante) aspirante.estatus = estatus
    },

    // Fichas
    agregarFicha(state, ficha) {
      state.fichas.push(ficha)
    },

    // Configuración
    actualizarConfigAdmision(state, config) {
      state.configuracionAdmision = { ...state.configuracionAdmision, ...config }
    }
  },

  getters: {
    alumnosActivos: state => state.alumnos.filter(a => a.estado === 'Activo'),
    
    aspirantesPorEstatus: (state) => (estatus) => {
      return state.aspirantes.filter(a => a.estatus === estatus)
    },
    
    totalAspirantes: state => state.aspirantes.length,
    totalAceptados: state => state.aspirantes.filter(a => a.estatus === 'ACEPTADO').length,
    
    getAspiranteByFolio: (state) => (folio) => {
      return state.aspirantes.find(a => a.folio === folio)
    }
  },

  actions: {
    // Puedes agregar acciones asíncronas aquí en el futuro (API calls)
    async cargarDatosIniciales({ commit }) {
      // Simulación de carga desde API
      console.log('Cargando datos iniciales...')
    }
  }
})

export default store