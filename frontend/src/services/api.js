const baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

async function request(endpoint, options = {}) {
  const token = localStorage.getItem('token')
  const headers = {
    'Accept': 'application/json',
    ...options.headers,
  }

  if (token) {
    headers['Authorization'] = `Bearer ${token}`
  }

  // If body is FormData, don't set Content-Type, fetch does it automatically
  if (!(options.body instanceof FormData)) {
    if (options.body && typeof options.body === 'object') {
      options.body = JSON.stringify(options.body)
      headers['Content-Type'] = 'application/json'
    }
  }

  const response = await fetch(`${baseURL}${endpoint}`, {
    ...options,
    headers,
  })

  if (!response.ok) {
    let errorData
    try {
      errorData = await response.json()
    } catch {
      errorData = { message: response.statusText }
    }
    const error = new Error(errorData.message || 'Request failed')
    error.status = response.status
    error.errors = errorData.errors // Laravel validation errors
    throw error
  }

  // Empty response body on 204 or DELETE sometimes
  if (response.status === 204) return null
  try {
    return await response.json()
  } catch {
    return null
  }
}

export default {
  get(path) {
    return request(path, { method: 'GET' })
  },
  post(path, body) {
    return request(path, { method: 'POST', body })
  },
  put(path, body) {
    return request(path, { method: 'PUT', body })
  },
  delete(path) {
    return request(path, { method: 'DELETE' })
  },
  postForm(path, formData) {
    return request(path, { method: 'POST', body: formData })
  }
}
