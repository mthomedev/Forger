import api from './api'

export default {
  search(query = '', page = 1) {
    return api.get(`/users?search=${encodeURIComponent(query)}&page=${page}`)
  },
  getProfile(userId) {
    return api.get(`/users/${userId}`)
  },
  getSuggestions() {
    return api.get(`/users/suggestions`)
  },
  toggleFollow(userId) {
    return api.post(`/users/${userId}/follow`)
  }
}

