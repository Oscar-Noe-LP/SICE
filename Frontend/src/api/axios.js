// src/api/axios.js
import axios from 'axios'

const api = axios.create({
  baseURL: `${import.meta.env.VITE_API_URL}/api`,
  // 30 s — Railway free tier puede tardar 20-40 s en cold start.
  // Antes estaba en 10 s, lo que causaba timeouts al primer request
  // después de inactividad.
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json'
  }
})

// Interceptor de request: adjunta el token Bearer si existe
api.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Interceptor para manejar errores comunes
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      window.location.href = '/login'
    }
    console.error('Error en la petición:', error.response?.data || error.message)
    return Promise.reject(error)
  }
)

export default api