const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
const STORAGE_URL = API_URL.replace(/\/api\/?$/, '')

export function getImageUrl(path) {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return `${STORAGE_URL}/storage/${path}`
}

export function getAvatarUrl(path, username) {
  if (!path) {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(username || 'User')}&background=282830&color=ff6b1a&bold=true`
  }
  if (path.startsWith('http')) return path
  return `${STORAGE_URL}/storage/${path}`
}
