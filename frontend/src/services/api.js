import axios from 'axios'

const baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const client = axios.create({
  baseURL,
})

client.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

client.interceptors.response.use(
  (response) => {
    if (response.status === 204) return null
    return response.data
  },
  (error) => {
    const response = error.response
    const errorData = response?.data || {}
    const err = new Error(errorData.message || response?.statusText || 'Request failed')
    err.status = response?.status
    err.errors = errorData.errors // Laravel validation errors
    return Promise.reject(err)
  }
)

export default {
  get(path) {
    return client.get(path)
  },
  post(path, body) {
    return client.post(path, body)
  },
  put(path, body) {
    return client.put(path, body)
  },
  delete(path) {
    return client.delete(path)
  },
  postForm(path, formData) {
    return client.post(path, formData)
  },
  putForm(path, formData) {
    return client.put(path, formData)
  }
}
