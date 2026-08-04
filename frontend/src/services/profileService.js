import { authService } from './authService';

const API_URL = 'http://localhost:8000/api';

export const profileService = {
  async getProfile() {
    const token = authService.getToken();
    const response = await fetch(`${API_URL}/profile`, {
      method: 'GET',
      headers: { 
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json' 
      },
    });
    if (!response.ok) throw await response.json();
    return response.json();
  },

  async updateProfile(profileData) {
    const token = authService.getToken();
    const response = await fetch(`${API_URL}/profile`, {
      method: 'PUT',
      headers: { 
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(profileData),
    });
    if (!response.ok) throw await response.json();
    return response.json();
  },

  async uploadAvatar(file) {
    const token = authService.getToken();
    const formData = new FormData();
    formData.append('avatar', file);

    const response = await fetch(`${API_URL}/profile/avatar`, {
      method: 'POST',
      headers: { 
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json' 
      },
      body: formData,
    });
    if (!response.ok) throw await response.json();
    return response.json();
  }
};
