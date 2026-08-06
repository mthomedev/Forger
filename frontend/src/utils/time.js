export function timeAgo(dateStr) {
  const date = new Date(dateStr)
  if (isNaN(date)) return ''
  const now = new Date()
  const seconds = Math.floor((now - date) / 1000)
  if (seconds < 60) return 'Just now'
  const minutes = Math.floor(seconds / 60)
  if (minutes < 60) return `${minutes}m ago`
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `${hours}h ago`
  const days = Math.floor(hours / 24)
  if (days < 30) return `${days}d ago`
  return date.toLocaleDateString()
}

export function formatDateTime(dateStr) {
  const date = new Date(dateStr)
  if (isNaN(date)) return ''
  return date.toISOString()
}
