import api from './api'

export const notificationService = {
  getNotifications() {
    return api.get('/notifications')
  }
}
