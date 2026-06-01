<script setup lang="ts">
import { computed } from 'vue'
import { 
  User, 
  GraduationCap, 
  BookOpen, 
  Hash, 
  Building2, 
  FileText, 
  CreditCard, 
  Calendar,
  ChevronRight,
  UserCheck
} from 'lucide-vue-next'
import Card from '@/components/ui/Card.vue'

// Reutilizamos el tipo de datos que maneja tu BuscadorGlobal
export type TipoResultado = 'ALUMNO' | 'DOCENTE'

export interface UsuarioSeleccionado {
  tipo: TipoResultado
  nombre_completo: string
  identificador: string // Número de control o Clave docente
  area?: string         // Carrera o Departamento
  estatus?: string       // activo, irregular, baja, etc.
  id?: number | string
}

interface Props {
  usuario: UsuarioSeleccionado | null
  cargando?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  usuario: null,
  cargando: false
})

const emit = defineEmits<{
  verHistorial: [id: string | number]
  verAdeudos: [id: string | number]
  editarPerfil: [id: string | number]
}>()

// Normalizar el estatus para que encaje con los badges del Card.vue
const estatusNormalizado = computed(() => {
  if (!props.usuario?.estatus) return null
  const st = props.usuario.estatus.toLowerCase()
  if (st.includes('act')) return 'activo'
  if (st.includes('inact')) return 'inactivo'
  if (st.includes('baj')) return 'baja'
  if (st.includes('egr')) return 'egresado'
  if (st.includes('irreg')) return 'irregular'
  return null
})

const esAlumno = computed(() => props.usuario?.tipo === 'ALUMNO')
</script>

<template>
  <div class="space-y-4">
    <div 
      v-if="!usuario && !cargando" 
      class="flex flex-col items-center justify-center text-center p-12 border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-2xl bg-surface-50/50 dark:bg-surface-900/10 min-h-[350px]"
    >
      <div class="p-4 rounded-full bg-primary-50 dark:bg-primary-950 text-primary-500 mb-4 animate-pulse">
        <User class="w-12 h-12" />
      </div>
      <h3 class="text-base font-display font-semibold text-surface-700 dark:text-surface-200 uppercase tracking-wider">
        Expediente del Usuario
      </h3>
      <p class="text-xs text-surface-400 dark:text-surface-500 max-w-xs mt-1">
        Busca y selecciona un alumno o docente en el panel superior para visualizar su información detallada e historial.
      </p>
    </div>

    <Card v-else-if="cargando" cargando />

    <div v-else-if="usuario" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      
      <Card 
        variante="default" 
        :estatus="estatusNormalizado"
        class="lg:col-span-1 overflow-hidden relative flex flex-col justify-between"
      >
        <div class="absolute top-0 left-0 w-full h-1.5" :class="esAlumno ? 'bg-blue-500' : 'bg-violet-500'"></div>
        
        <div class="flex flex-col items-center text-center pt-4 pb-2">
          <div 
            class="w-20 h-20 rounded-2xl flex items-center justify-center mb-4 border shadow-sm transition-transform duration-300 hover:scale-105"
            :class="esAlumno ? 'bg-blue-50 dark:bg-blue-950 border-blue-200 text-blue-600' : 'bg-violet-50 dark:bg-violet-950 border-violet-200 text-violet-600'"
          >
            <GraduationCap v-if="esAlumno" class="w-10 h-10" />
            <BookOpen v-else class="w-10 h-10" />
          </div>

          <h2 class="text-base font-display font-bold text-surface-900 dark:text-surface-50 line-clamp-2 px-2 uppercase tracking-wide">
            {{ usuario.nombre_completo }}
          </h2>
          
          <span 
            class="text-[9px] font-bold uppercase tracking-widest px-2.5 py-0.5 rounded-full mt-2 border"
            :class="esAlumno ? 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950' : 'bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-950'"
          >
            {{ usuario.tipo }}
          </span>
        </div>

        <div class="border-t border-surface-100 dark:border-surface-700 mt-4 pt-4 space-y-3 text-xs text-surface-600 dark:text-surface-300">
          <div class="flex items-center gap-2.5 px-1">
            <Hash class="w-4 h-4 text-surface-400 shrink-0" />
            <div class="truncate">
              <p class="text-[10px] text-surface-400 uppercase font-medium tracking-wider">Identificador</p>
              <p class="font-mono font-bold text-surface-800 dark:text-surface-100">{{ usuario.identificador }}</p>
            </div>
          </div>

          <div class="flex items-center gap-2.5 px-1">
            <Building2 class="w-4 h-4 text-surface-400 shrink-0" />
            <div class="truncate">
              <p class="text-[10px] text-surface-400 uppercase font-medium tracking-wider">
                {{ esAlumno ? 'Carrera Universitaria' : 'Departamento' }}
              </p>
              <p class="font-medium text-surface-800 dark:text-surface-100 truncate uppercase">{{ usuario.area || 'No Asignado' }}</p>
            </div>
          </div>
        </div>
      </Card>

      <div class="lg:col-span-2 space-y-4">
        
        <Card titulo="Detalles de Gestión Operativa" divisor>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-3 rounded-xl bg-surface-50 dark:bg-surface-900/40 border border-surface-100 dark:border-surface-800">
              <span class="text-[10px] text-surface-400 font-bold uppercase tracking-wider block mb-1">Entidad del Sistema</span>
              <p class="font-medium text-surface-800 dark:text-surface-100">Instituto Tecnológico de Matehuala</p>
            </div>
            <div class="p-3 rounded-xl bg-surface-50 dark:bg-surface-900/40 border border-surface-100 dark:border-surface-800">
              <span class="text-[10px] text-surface-400 font-bold uppercase tracking-wider block mb-1">ID Único de Base de Datos</span>
              <p class="font-mono font-semibold text-surface-700 dark:text-surface-300">#{{ usuario.id || 'N/A' }}</p>
            </div>
          </div>
        </Card>

        <Card titulo="Acciones Disponibles y Módulos">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
            
            <button 
              v-if="esAlumno"
              @click="usuario.id && emit('verHistorial', usuario.id)"
              class="flex items-center justify-between p-3 rounded-xl border border-surface-200 dark:border-surface-700 hover:border-primary-300 dark:hover:border-primary-800 hover:bg-primary-50/40 dark:hover:bg-primary-950/20 text-left transition-all duration-200 group"
            >
              <div class="flex items-center gap-3 truncate">
                <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-950 text-blue-600 shrink-0">
                  <FileText class="w-4 h-4" />
                </div>
                <div class="truncate">
                  <p class="text-xs font-semibold text-surface-800 dark:text-surface-200">Kardex / Inscripciones</p>
                  <p class="text-[10px] text-surface-400 truncate">Exportar historial de reingresos</p>
                </div>
              </div>
              <ChevronRight class="w-4 h-4 text-surface-400 transition-transform group-hover:translate-x-0.5" />
            </button>

            <button 
              v-if="esAlumno"
              @click="usuario.id && emit('verAdeudos', usuario.id)"
              class="flex items-center justify-between p-3 rounded-xl border border-surface-200 dark:border-surface-700 hover:border-amber-300 dark:hover:border-amber-800 hover:bg-amber-50/40 dark:hover:bg-amber-950/20 text-left transition-all duration-200 group"
            >
              <div class="flex items-center gap-3 truncate">
                <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-950 text-amber-600 shrink-0">
                  <CreditCard class="w-4 h-4" />
                </div>
                <div class="truncate">
                  <p class="text-xs font-semibold text-surface-800 dark:text-surface-200">Finanzas y Adeudos</p>
                  <p class="text-[10px] text-surface-400 truncate">Verificar estados de pago</p>
                </div>
              </div>
              <ChevronRight class="w-4 h-4 text-surface-400 transition-transform group-hover:translate-x-0.5" />
            </button>

            <button 
              @click="usuario.id && emit('editarPerfil', usuario.id)"
              class="flex items-center justify-between p-3 rounded-xl border border-surface-200 dark:border-surface-700 hover:border-surface-400 dark:hover:border-surface-500 hover:bg-surface-50 dark:hover:bg-surface-800 text-left transition-all duration-200 group sm:col-span-2"
            >
              <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300 shrink-0">
                  <UserCheck class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-xs font-semibold text-surface-800 dark:text-surface-200">Actualizar Estatus / Datos</p>
                  <p class="text-[10px] text-surface-400">Modificar información general en el panel escolar</p>
                </div>
              </div>
              <ChevronRight class="w-4 h-4 text-surface-400 transition-transform group-hover:translate-x-0.5" />
            </button>

          </div>
        </Card>

      </div>
    </div>
  </div>
</template>