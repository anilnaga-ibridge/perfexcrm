import { defineStore } from 'pinia';
import axios from 'axios';

export const useModuleStore = defineStore('module', {
  state: () => ({
    modules: [],
    menus: [],
    loaded: false,
  }),

  getters: {
    activeModules: (state) => state.modules.filter(m => m.is_active),
    isActive: (state) => (slug) => {
      const mod = state.modules.find(m => m.slug === slug);
      return mod ? mod.is_active : false;
    },
  },

  actions: {
    async fetchActiveModules(force = false) {
      if (this.loaded && !force) return;
      try {
        const { data } = await axios.get('/plugins/active');
        this.modules = data.data || [];
        this.loaded = true;
      } catch (e) {
        console.error('Failed to fetch active modules:', e);
        this.modules = [];
        this.loaded = true;
      }
    },
    async fetchActiveMenus(force = false) {
      if (this.menus.length && !force) return;
      try {
        const { data } = await axios.get('/plugins/menus');
        this.menus = data.data || [];
      } catch (e) {
        console.error('Failed to fetch active menus:', e);
        this.menus = [];
      }
    },
  },
});
