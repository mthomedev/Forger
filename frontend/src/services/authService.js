import api from './api'

export const authService = {
  register(userData) {
    return api.post('/register', userData)
  },

  login(credentials) {
    return api.post('/login', credentials)
  },

  fetchUser() {
    return api.get('/user')
  },

  async logout() {
    try {
      await api.post('/logout')
    } catch (err) {
      console.error('Logout error:', err)
    }
  },

  setToken(token) {
    localStorage.setItem('token', token)
  },

  getToken() {
    return localStorage.getItem('token')
  },

  removeToken() {
    localStorage.removeItem('token')
  }
}
