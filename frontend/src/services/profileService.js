import api from './api'

export const profileService = {
  getProfile() {
    return api.get('/profile')
  },

  updateProfile(profileData) {
    return api.put('/profile', profileData)
  },

  uploadAvatar(file) {
    const formData = new FormData()
    formData.append('avatar', file)
    return api.postForm('/profile/avatar', formData)
  }
}
