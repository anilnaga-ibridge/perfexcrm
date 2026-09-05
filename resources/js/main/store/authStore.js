import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('auth_user') || 'null'),
    token: localStorage.getItem('auth_token') || null,
  }),
  
  getters: {
    isLoggedIn: (state) => !!state.token,
    hasPermission: (state) => (feature, action) => {
      if (!state.user) return false;
      const u = state.user;

      // Only explicit Super Admin gets unconditional bypass
      const isAdmin = (
        u.admin == 1 ||
        u.admin === "1" ||
        u.is_admin == 1 ||
        u.is_admin === true ||
        u.role_data?.slug === "admin" ||
        u.role_data?.name?.toLowerCase() === "admin" ||
        u.permissions?.all === true
      );
      if (isAdmin) return true;

      let featName = feature;
      let actName = action;
      if (featName && featName.includes('.')) {
        const parts = featName.split('.');
        featName = parts[0];
        actName = parts[1];
      }

      const perms = u.permissions || u.role?.permissions;
      if (!perms || typeof perms !== "object" || Object.keys(perms).length === 0) {
        return false;
      }

      const norm = (s) => String(s || '').toLowerCase().replace(/[_-\s]+/g, ' ').trim();
      const targetFeat = norm(featName);
      const targetAct  = actName ? norm(actName) : null;

      const getAliases = (f) => {
        if (['customers', 'customer', 'clients', 'client'].includes(f)) return ['customers', 'customer', 'clients', 'client'];
        if (['staff roles', 'staff role', 'roles', 'role'].includes(f)) return ['staff roles', 'staff role', 'roles', 'role'];
        if (['staff', 'users', 'user'].includes(f)) return ['staff', 'users', 'user'];
        if (['knowledge base', 'knowledgebase', 'kb'].includes(f)) return ['knowledge base', 'knowledgebase', 'kb'];
        if (['credit notes', 'creditnotes', 'credit note'].includes(f)) return ['credit notes', 'creditnotes', 'credit note'];
        if (['estimate request', 'estimaterequest', 'estimate requests'].includes(f)) return ['estimate request', 'estimaterequest', 'estimate requests'];
        if (['items', 'item', 'predefined items'].includes(f)) return ['items', 'item', 'predefined items'];
        if (['task checklist templates', 'checklist templates', 'task checklist'].includes(f)) return ['task checklist templates', 'checklist templates', 'task checklist'];
        if (['e-invoice', 'einvoice', 'e invoice'].includes(f)) return ['e-invoice', 'einvoice', 'e invoice'];
        
        const plural = f + 's';
        const singular = f.endsWith('s') ? f.slice(0, -1) : f;
        return Array.from(new Set([f, plural, singular]));
      };

      const targetAliases = getAliases(targetFeat);
      const matchingFeatKeys = Object.keys(perms).filter(k => targetAliases.includes(norm(k)));
      if (matchingFeatKeys.length === 0) return false;

      let foundFeatMatch = false;

      for (const featKey of matchingFeatKeys) {
        const actions = perms[featKey];
        if (!actions) continue;

        if (Array.isArray(actions)) {
          if (!targetAct) {
            if (actions.length > 0) return true;
          } else {
            if (actions.some(a => norm(a) === targetAct || (targetAct === 'view' && (norm(a) === 'view global' || norm(a) === 'view own')))) {
              return true;
            }
          }
          foundFeatMatch = true;
          continue;
        }

        if (typeof actions === 'object' && actions !== null) {
          if (targetAct) {
            let actKey = Object.keys(actions).find(a => norm(a) === targetAct);
            if (actKey && !!actions[actKey]) return true;

            if (targetAct === 'view' && (actions.view_global || actions.view_own || actions.view)) {
              return true;
            }
            foundFeatMatch = true;
            continue;
          }
          if (Object.values(actions).some(v => v === true || v === 1 || v === '1')) {
            return true;
          }
          foundFeatMatch = true;
          continue;
        }

        if (typeof actions === 'boolean') {
          if (actions) return true;
          foundFeatMatch = true;
          continue;
        }
      }

      return false;
    },
  },
  
  actions: {
    async registerAction(data) {
      try {
        const response = await axios.post('/auth/register', data);
        const { token, user } = response.data;
        
        this.token = token;
        this.user = user;
        
        localStorage.setItem('auth_token', token);
        localStorage.setItem('auth_user', JSON.stringify(user));
        
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        return { success: true };
      } catch (error) {
        console.error('Register error:', error);
        return {
          success: false,
          message: error.response?.data?.message || 'Registration failed.'
        };
      }
    },

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

    async socialExchangeAction() {
      try {
        const response = await axios.post('/auth/social/exchange');
        const { token, user } = response.data;
        
        this.token = token;
        this.user = user;
        
        localStorage.setItem('auth_token', token);
        localStorage.setItem('auth_user', JSON.stringify(user));
        
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        return { success: true };
      } catch (error) {
        console.error('Social exchange error:', error);
        return {
          success: false,
          message: error.response?.data?.message || 'Social login failed.'
        };
      }
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
