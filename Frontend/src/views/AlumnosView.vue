<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="alumnos-page">
      <h1 class="page-title">Lista de Alumnos</h1>

      <div class="filters-bar">
        <div class="search-group">
          <input
            type="text"
            placeholder="Buscar alumno..."
            v-model="busquedaAlumno"
            class="search-input"
          >
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 01-14 0 7 7 0 0114 0z" />
          </svg>
        </div>

        <select v-model="filtroCarrera" class="filter-select">
          <option value="">Carrera</option>
          <option value="Contador Publico">Contador Publico</option>
          <option value="Ingenieria Civil">Ingenieria Civil</option>
          <option value="Ingenieria en Gestion empresarial">Ingenieria en Gestion empresarial</option>
          <option value="Ingenieria en Sistemas Computacionales">Ingenieria en Sistemas Computacionales</option>
          <option value="Ingenieria Industrial">Ingenieria Industrial</option>
        </select>
        <select v-model="filtroSemestre" class="filter-select">
          <option value="">Semestre</option>
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="3">3</option>
          <option value="4">4</option>
          <option value="5">5</option>
          <option value="6">6</option>
          <option value="7">7</option>
          <option value="8">8</option>
        </select>
        <select v-model="filtroEstatus" class="filter-select">
          <option value="">Estatus</option>
          <option value="Activo">Activo</option>
          <option value="Baja Temporal">Baja Temporal</option>
          <option value="Baja Definitiva">Baja Definitiva</option>
        </select>

        <button class="btn-limpiar" @click="resetFilters">
          <svg xmlns="http://www.w3.org/2000/svg" class="reset-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6h12v12" />
          </svg>
          Limpiar filtros
        </button>

        <button class="btn-nuevo" @click="nuevoAlumno">
          + Nuevo Alumno
        </button>
      </div>

      <div class="table-container">
        <table class="alumnos-table" v-if="paginatedAlumnos.length > 0">
          <thead>
            <tr>
              <th>No. Control</th>
              <th>Nombre</th>
              <th>Carrera</th>
              <th>Semestre</th>
              <th>Estatus</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="alumno in paginatedAlumnos" :key="alumno.id">
              <td>{{ alumno.noControl }}</td>
              <td>{{ alumno.nombre }}</td>
              <td>{{ alumno.carrera }}</td>
              <td>{{ alumno.semestre }}</td>
              <td>
                <span class="status-badge" :class="alumno.estatus.toLowerCase().replace(/\s/g, '-')">
                  {{ alumno.estatus }}
                </span>
              </td>
              <td class="actions">
                <button class="btn-action ver" @click="abrirModalVer(alumno)">Ver</button>
                <button class="btn-action editar" @click="abrirModalEditar(alumno)">Editar</button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="empty-state">
          <h3>No hay resultados</h3>
          <p>No se encontraron alumnos con los filtros aplicados.<br>
             Intenta cambiar los criterios de búsqueda.</p>
          <button class="btn-reset" @click="resetFilters">
            Limpiar filtros
          </button>
        </div>
      </div>

      <div class="pagination">
        <div class="pagination-left">
          Filas por página: 
          <select v-model="filasPorPagina" @change="currentPage = 1">
            <option>10</option>
            <option>20</option>
            <option>50</option>
          </select>
        </div>
        <div class="pagination-center">
          Página {{ currentPage }} de {{ totalPages }}
        </div>
        <div class="pagination-right">
          <button @click="prevPage" :disabled="currentPage === 1">‹</button>
          <button v-for="page in visiblePages" :key="page" @click="goToPage(page)" :class="{ active: page === currentPage }">{{ page }}</button>
          <button @click="nextPage" :disabled="currentPage === totalPages">›</button>
        </div>
      </div>
    </div>


    <div v-if="showViewModal" class="modal-overlay" @click.self="cerrarModalVer">
      <div class="modal-content view-modal">
        <div class="modal-header">
          <h3>Detalles del Alumno</h3>
          <button @click="cerrarModalVer" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <div class="detail-row"><strong>No. Control:</strong> <span>{{ alumnoVer.noControl }}</span></div>
          <div class="detail-row"><strong>Nombre completo:</strong> <span>{{ alumnoVer.nombre }}</span></div>
          <div class="detail-row"><strong>Carrera:</strong> <span>{{ alumnoVer.carrera }}</span></div>
          <div class="detail-row"><strong>Semestre:</strong> <span>{{ alumnoVer.semestre }}</span></div>
          <div class="detail-row"><strong>Estatus:</strong> 
            <span class="status-badge" :class="alumnoVer.estatus.toLowerCase().replace(/\s/g, '-')">
              {{ alumnoVer.estatus }}
            </span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-cerrar" @click="cerrarModalVer">Cerrar</button>
        </div>
      </div>
    </div>


    <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ alumnoEditar.id ? 'Editar Alumno' : 'Nuevo Alumno' }}</h3>
          <button @click="cerrarModal" class="close-btn">×</button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label>Número de Control</label>
            <input v-model="alumnoEditar.noControl" type="text" readonly class="modal-input disabled" />
          </div>

          <div class="form-group">
            <label>Nombre completo</label>
            <input v-model="alumnoEditar.nombre" type="text" class="modal-input" required />
          </div>

          <div class="form-group">
            <label>Carrera</label>
            <select v-model="alumnoEditar.carrera" class="modal-select">
              <option value="Contador Publico">Contador Publico</option>
              <option value="Ingenieria Civil">Ingenieria Civil</option>
              <option value="Ingenieria en Gestion empresarial">Ingenieria en Gestion empresarial</option>
              <option value="Ingenieria en Sistemas Computacionales">Ingenieria en Sistemas Computacionales</option>
              <option value="Ingenieria Industrial">Ingenieria Industrial</option>
            </select>
          </div>

          <div class="form-group">
            <label>Semestre</label>
            <select v-model="alumnoEditar.semestre" class="modal-select">
              <option v-for="n in 8" :key="n" :value="n">{{ n }}</option>
            </select>
          </div>

          <div class="form-group">
            <label>Estatus</label>
            <select v-model="alumnoEditar.estatus" class="modal-select">
              <option value="Activo">Activo</option>
              <option value="Baja Temporal">Baja Temporal</option>
              <option value="Baja Definitiva">Baja Definitiva</option>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-cancelar" @click="cerrarModal">Cancelar</button>
          <button v-if="alumnoEditar.id" class="btn-eliminar" @click="eliminarAlumno">Eliminar</button>
          <button class="btn-guardar" @click="guardarCambios">Guardar Cambios</button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>

import { ref, computed, onMounted } from 'vue' // Añadimos onMounted
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const router = useRouter()

const busquedaAlumno = ref('')
const filtroCarrera = ref('')
const filtroSemestre = ref('')
const filtroEstatus = ref('')
const filasPorPagina = ref(10)
const currentPage = ref(1)

const props = defineProps({
  busquedaGlobal: { type: String, default: '' }
})

const normalize = (text) => {
  if (!text) return ''
  return text
    .toString()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
}

// 1. Iniciamos con el array vacío
const alumnos = ref([])

// 2. Función para traer los datos reales de tu Laravel
const cargarAlumnosDesdeBD = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/alumnos-full');
    if (!response.ok) throw new Error('Error al conectar con el servidor');
    const data = await response.json();
    alumnos.value = data; 
  } catch (error) {
    console.error("Error cargando alumnos:", error);
  }
};

// 3. Ejecutar la carga al abrir la vista
onMounted(() => {
  cargarAlumnosDesdeBD();
});

const alumnosFiltrados = computed(() => {
  return alumnos.value.filter(alumno => {
<<<<<<< HEAD
    // Filtro Global (del Layout)
    const coincideGlobal = !props.busquedaGlobal ||
=======
    const coincideGlobal = !props.busquedaGlobal || 
>>>>>>> 0ab94e5bd893f1a6ccc1faaeab464b871f381f9c
      normalize(alumno.nombre).includes(normalize(props.busquedaGlobal)) ||
      alumno.noControl.toString().includes(props.busquedaGlobal)

<<<<<<< HEAD
    // Filtro Local (Busqueda arriba de la tabla)
    const coincideLocal = !busquedaAlumno.value ||
=======
    const coincideLocal = !busquedaAlumno.value || 
>>>>>>> 0ab94e5bd893f1a6ccc1faaeab464b871f381f9c
      normalize(alumno.nombre).includes(normalize(busquedaAlumno.value)) ||
      alumno.noControl.toString().includes(busquedaAlumno.value)

    // Filtros de Select (Normalizados para evitar fallos por acentos o mayúsculas)
    const coincideCarrera = !filtroCarrera.value || 
                            normalize(alumno.carrera) === normalize(filtroCarrera.value)
    
    const coincideSemestre = !filtroSemestre.value || 
                             alumno.semestre.toString() === filtroSemestre.value.toString()
    
    const coincideEstatus = !filtroEstatus.value || 
                            normalize(alumno.estatus) === normalize(filtroEstatus.value)

    return coincideGlobal && coincideLocal && coincideCarrera && coincideSemestre && coincideEstatus
  })
})

// Lógica de paginación (Se mantiene igual)
const totalPages = computed(() => Math.ceil(alumnosFiltrados.value.length / filasPorPagina.value) || 1)
const paginatedAlumnos = computed(() => {
  const start = (currentPage.value - 1) * filasPorPagina.value
  return alumnosFiltrados.value.slice(start, start + filasPorPagina.value)
})
const visiblePages = computed(() => {
  const pages = []
  for (let i = 1; i <= totalPages.value; i++) pages.push(i)
  return pages
})

const goToPage = (page) => { currentPage.value = page }
const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

const resetFilters = () => {
  busquedaAlumno.value = ''
  filtroCarrera.value = ''
  filtroSemestre.value = ''
  filtroEstatus.value = ''
  currentPage.value = 1
}

const nuevoAlumno = () => router.push('/formulario-alumno')


const showViewModal = ref(false)
const alumnoVer = ref({})

const abrirModalVer = (alumno) => {
  alumnoVer.value = { ...alumno }
  showViewModal.value = true
}

const cerrarModalVer = () => {
  showViewModal.value = false
}


const showModal = ref(false)
const alumnoEditar = ref(null)

const abrirModalEditar = (alumno) => {
  alumnoEditar.value = { ...alumno }
  showModal.value = true
}

const cerrarModal = () => {
  showModal.value = false
  alumnoEditar.value = null
}

const guardarCambios = () => {
  if (!alumnoEditar.value) return
  const index = alumnos.value.findIndex(a => a.id === alumnoEditar.value.id)
  if (index !== -1) alumnos.value[index] = { ...alumnoEditar.value }
  cerrarModal()
  alert(' Cambios guardados correctamente')
}

const eliminarAlumno = () => {
  if (confirm(`¿Seguro que deseas eliminar a ${alumnoEditar.value.nombre}?`)) {
    const index = alumnos.value.findIndex(a => a.id === alumnoEditar.value.id)
    if (index !== -1) alumnos.value.splice(index, 1)
    cerrarModal()
    alert('🗑️ Alumno eliminado correctamente')
  }
}
</script>


<style scoped>

@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.alumnos-page { max-width: 100%; }
.page-title { color: #005187; font-size: 2rem; font-weight: 700; letter-spacing: -0.02em; }

.filters-bar { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.8rem; flex-wrap: nowrap; width: 100%; }

.search-group { position: relative; flex: 0 0 260px; overflow: hidden; }
.search-input { width: 100%; padding: 12px 14px 12px 48px; border: 1px solid #D1D9E6; border-radius: 8px; font-size: 1rem; }
.search-icon-svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 20px; height: 20px; stroke: #6B7280; }

.btn-limpiar { display: flex; align-items: center; gap: 8px; background: #F5F7FA; color: #1A1A1A; border: 1px solid #D1D9E6; padding: 12px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem; white-space: nowrap; }
.btn-limpiar:hover { background: #EEF2F7; }
.reset-icon { width: 18px; height: 18px; stroke: #6B7280; }

.filter-select { padding: 12px 14px; border: 1px solid #D1D9E6; border-radius: 8px; font-size: 1rem; flex: 0 0 180px; min-width: 180px; background: white; cursor: pointer; }

.btn-nuevo { background: #005187; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; white-space: nowrap; }

.actions { display: flex; gap: 8px; }
.btn-action { padding: 7px 16px; border-radius: 6px; font-size: 0.92rem; cursor: pointer; }
.btn-action.editar {
  background: #4D82BE;
  color: white;
  border: 1px solid #4D82BE;
}
.btn-action.editar:hover {
  background: #3B6EA5;
  border-color: #3B6EA5;
}

.table-container { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 25px rgba(0,0,0,0.07); }
.alumnos-table { width: 100%; border-collapse: collapse; }
.alumnos-table th { background: #F8FAFC; padding: 16px; text-align: left; font-weight: 600; }
.alumnos-table td { padding: 16px; border-bottom: 1px solid #E5E9F0; }

.status-badge { padding: 5px 14px; border-radius: 20px; font-size: 0.88rem; font-weight: 500; }
.status-badge.activo { background: #E8F5E9; color: #2E7D32; }
.status-badge.baja-temporal { background: #FFF3E0; color: #ED6C02; }
.status-badge.baja-definitiva { background: #FDECEA; color: #D32F2F; }

.empty-state { text-align: center; padding: 4rem 2rem; color: #9AA3AF; }
.empty-state h3 { font-size: 1.4rem; color: #1A1A1A; margin-bottom: 0.5rem; }
.empty-state p { margin-bottom: 1.5rem; line-height: 1.5; }
.btn-reset { background: #F5F7FA; color: #1A1A1A; border: 1px solid #D1D9E6; padding: 10px 20px; border-radius: 8px; font-weight: 500; cursor: pointer; }

.pagination { margin-top: 2rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.95rem; }
.pagination-left, .pagination-center, .pagination-right { display: flex; align-items: center; gap: 10px; }
.pagination-right button { padding: 6px 12px; border: 1px solid #D1D9E6; background: white; border-radius: 6px; cursor: pointer; }
.pagination-right .active { background: #005187; color: white; border-color: #005187; }


.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.modal-content { background: white; width: 520px; max-width: 90%; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.3); overflow: hidden; }
.modal-header { background: #005187; color: white; padding: 1.2rem 1.8rem; display: flex; justify-content: space-between; align-items: center; }
.modal-header h3 { margin: 0; font-size: 1.4rem; font-weight: 700; }
.close-btn { background: none; border: none; color: white; font-size: 1.8rem; cursor: pointer; }
.modal-body { padding: 2rem 1.8rem; }
.form-group { margin-bottom: 1.6rem; }
.form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #1A1A1A; }
.modal-input, .modal-select { width: 100%; padding: 12px 16px; border: 1.5px solid #D1D9E6; border-radius: 10px; font-size: 1rem; }
.modal-input.disabled { background: #F5F7FA; cursor: not-allowed; }
.modal-footer { padding: 1.2rem 1.8rem; background: #F8FAFC; display: flex; gap: 12px; justify-content: flex-end; border-top: 1px solid #E5E9F0; }
.btn-cancelar, .btn-eliminar, .btn-guardar { padding: 12px 28px; border-radius: 10px; font-weight: 600; cursor: pointer; }
.btn-cancelar { background: #F5F7FA; color: #1A1A1A; border: 1px solid #D1D9E6; }
.btn-eliminar { background: #D32F2F; color: white; border: none; }
.btn-guardar { background: #005187; color: white; border: none; }


.view-modal .modal-body { padding: 2.2rem 1.8rem; }
.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 14px 0;
  border-bottom: 1px solid #E5E9F0;
  font-size: 1.05rem;
}
.detail-row:last-child { border-bottom: none; }
.detail-row strong { color: #1A1A1A; font-weight: 600; }

.btn-cerrar {
  background: #005187;
  color: white;
  padding: 13px 40px;
  border-radius: 10px;
  font-weight: 600;
  border: none;
  cursor: pointer;
}
</style>