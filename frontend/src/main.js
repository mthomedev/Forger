import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'

// Global error handler
const handleError = (err, instance, info) => {
  console.error('Global error:', err)
  console.error('Component:', instance)
  console.error('Info:', info)

  // In production, you might want to send to error tracking service
  if (import.meta.env.PROD) {
    // Example: send to Sentry, LogRocket, etc.
    // errorTrackingService.captureException(err, { extra: { component: instance, info } })
  }
}

// Pinia store error handler
const pinia = createPinia()
pinia.use(({ store }) => {
  store.$onAction(({ name, store, after, onError }) => {
    const startTime = Date.now()
    after(() => {
      console.debug(`[Pinia] ${store.$id}.${name} completed in ${Date.now() - startTime}ms`)
    })
    onError((error) => {
      console.error(`[Pinia] ${store.$id}.${name} failed:`, error)
    })
  })
})

const app = createApp(App)

app.use(pinia)
app.use(router)

// Global error handler for Vue
app.config.errorHandler = handleError

// Warn on deprecation in development
if (!import.meta.env.PROD) {
  app.config.warnHandler = (msg, instance, trace) => {
    console.warn(`[Vue Warning]: ${msg}`)
    console.warn('Component trace:', trace)
  }
}

// Mount app
app.mount('#app')

// Handle unhandled promise rejections
window.addEventListener('unhandledrejection', (event) => {
  console.error('Unhandled promise rejection:', event.reason)
  event.preventDefault()
})

// Handle global errors
window.addEventListener('error', (event) => {
  console.error('Global error:', event.error)
})