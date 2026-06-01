
import { onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

export function useKeyboardShortcuts() {
  const router = useRouter()

  const enfocarElemento = (selector) => {
    const el = document.querySelector(selector)
    if (el) {
      el.focus()

      if (el.tagName === 'INPUT') el.select()
    }
  }

  const atajos = [



    {
      ctrl: true, shift: true, alt: false, tecla: 'i',
      desc: 'Ir a Inicio',
      accion: () => router.push('/inicio')
    },
    {
      ctrl: true, shift: true, alt: false, tecla: 'a',
      desc: 'Ir a Alumnos',
      accion: () => router.push('/alumnos')
    },
    {
      ctrl: true, shift: true, alt: false, tecla: 'e',
      desc: 'Ir a Evaluaciones',
      accion: () => router.push('/evaluaciones')
    },
    {
      ctrl: true, shift: true, alt: false, tecla: 'c',
      desc: 'Ir a Calificaciones',
      accion: () => router.push('/calificaciones')
    },
    {
      ctrl: true, shift: true, alt: false, tecla: 'g',
      desc: 'Ir a Gestión de Grupos',
      accion: () => router.push('/gestion-grupos')
    },
    {
      ctrl: true, shift: true, alt: false, tecla: 'n',
      desc: 'Ir a Nueva Inscripción',
      accion: () => router.push('/inscripcion')
    },
    {
      ctrl: true, shift: true, alt: false, tecla: 's',
      desc: 'Ir a Servicios Escolares',
      accion: () => router.push('/servicios-escolares')
    },



    {
      ctrl: true, shift: false, alt: false, tecla: 'k',
      desc: 'Enfocar búsqueda global del encabezado',
      accion: () => enfocarElemento('.search-group input')
    },
    {
      ctrl: true, shift: false, alt: false, tecla: 'f',
      desc: 'Enfocar búsqueda (global o local, el primero disponible)',
      accion: () => {

        const busquedaLocal  = document.querySelector('.search-input')
        const busquedaGlobal = document.querySelector('.search-group input')
        const objetivo = busquedaLocal || busquedaGlobal
        if (objetivo) {
          objetivo.focus()
          objetivo.select()
        }
      }
    },


  ]


  const manejarTeclado = (evento) => {

    const etiquetaActiva = document.activeElement?.tagName
    const esAreaDeTexto  = etiquetaActiva === 'TEXTAREA'
    if (esAreaDeTexto) return


    const teclaPresionada = evento.key.toLowerCase()

    for (const atajo of atajos) {
      const coincideCtrl  = atajo.ctrl  === evento.ctrlKey
      const coincideShift = atajo.shift === evento.shiftKey
      const coincideAlt   = atajo.alt   === evento.altKey
      const coincideTecla = atajo.tecla === teclaPresionada

      if (coincideCtrl && coincideShift && coincideAlt && coincideTecla) {

        evento.preventDefault()


        atajo.accion()

        break
      }
    }
  }


  onMounted(() => {
    window.addEventListener('keydown', manejarTeclado)
  })

  onUnmounted(() => {
    window.removeEventListener('keydown', manejarTeclado)
  })


  return {
    atajos
  }
}