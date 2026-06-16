import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('auth_user') || 'null'),
    token: localStorage.getItem('auth_token') || null,
  }),
  
  getters: {
    isLoggedIn: (state) => !!state.token,
  },
  
  actions: {
    async loginAction(credentials) {
      try {
        const response = await axios.post('/auth/login', credentials);
        const { token, user } = response.data;
        
        this.token = token;
        this.user = user;
        
        localStorage.setItem('auth_token', token);
        localStorage.setItem('auth_user', JSON.stringify(user));
        
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        return { success: true };
      } catch (error) {
        console.error('Login error:', error);
        return {
          success: false,
          message: error.response?.data?.message || 'Invalid email or password.'
        };
      }
    },
    
    async logoutAction() {
      try {
        await axios.post('/auth/logout');
      } catch (e) {
        // Continue even if network request fails
      }
      
      this.token = null;
      this.user = null;
      
      localStorage.removeItem('auth_token');
      localStorage.removeItem('auth_user');
      
      delete axios.defaults.headers.common['Authorization'];
    },
    
    async updateUserAction() {
      if (!this.token) return;
      try {
        const response = await axios.get('/auth/user');
        this.user = response.data.user;
        localStorage.setItem('auth_user', JSON.stringify(this.user));
      } catch (error) {
        if (error.response?.status === 401) {
          this.logoutAction();
        }
      }
    }
  }
});
