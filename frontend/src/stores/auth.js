import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '../services/authService'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || '')
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value)

  async function register(userData) {
    loading.value = true
    error.value = null
    try {
      const data = await authService.register(userData)
      token.value = data.access_token
      user.value = data.user
      authService.setToken(data.access_token)
      return data
    } catch (err) {
      error.value = err.message || 'Registration failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function login(credentials) {
    loading.value = true
    error.value = null
    try {
      const data = await authService.login(credentials)
      token.value = data.access_token
      user.value = data.user
      authService.setToken(data.access_token)
      return data
    } catch (err) {
      error.value = err.message || 'Login failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchUser() {
    if (!token.value) return null
    loading.value = true
    try {
      const userData = await authService.fetchUser()
      user.value = userData
      return userData
    } catch (err) {
      logout()
      throw err
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await authService.logout()
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      token.value = ''
      user.value = null
      authService.removeToken()
    }
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    register,
    login,
    fetchUser,
    logout,
  }
})
