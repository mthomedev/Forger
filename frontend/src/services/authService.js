const API_URL = 'http://localhost:8000/api';

export const authService = {
  async register(userData) {
    const response = await fetch(`${API_URL}/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(userData),
    });
    
    if (!response.ok) throw await response.json();
    return response.json();
  },

  async login(credentials) {
    const response = await fetch(`${API_URL}/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(credentials),
    });

    if (!response.ok) throw await response.json();
    return response.json();
  },

  async logout() {
    const token = this.getToken();
    await fetch(`${API_URL}/logout`, {
      method: 'POST',
      headers: { 
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json' 
      },
    });
    localStorage.removeItem('token');
  },

  setToken(token) {
    localStorage.setItem('token', token);
  },

  getToken() {
    return localStorage.getItem('token');
  }
};
