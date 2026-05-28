import { createApp } from 'vue'
import App from './App.vue'
import router from './router'           // ← esta línea es crítica
import './assets/main.css'

const app = createApp(App)
app.use(router)                         // ← y esta
app.mount('#app')