import { defineStore } from 'pinia';

export const useThemeStore = defineStore('theme', {
  state: () => ({
    // Template: 'vuexy' (Modern Vuexy Dashboard) or 'organic' (Claymorphic Organic)
    template: localStorage.getItem('crm_active_theme_template') || 'vuexy',
    
    // Skin: 'light' | 'dark' | 'semi-dark' | 'bordered'
    skin: localStorage.getItem('crm_active_theme_skin') || 'light',
    
    // Primary Color: default Vuexy purple #7367F0
    primaryColor: localStorage.getItem('crm_active_primary_color') || '#7367F0',
    
    // Navbar type: 'floating' | 'sticky' | 'static'
    navbarType: localStorage.getItem('crm_active_navbar_type') || 'floating',

    // Content Layout: 'compact' | 'fluid'
    contentLayout: localStorage.getItem('crm_active_content_layout') || 'fluid',

    // Predefined Primary Color Swatches
    colorPresets: [
      { name: 'Vuexy Purple', hex: '#7367F0' },
      { name: 'Ocean Cyan',   hex: '#00CFE8' },
      { name: 'Forest Emerald', hex: '#28C76F' },
      { name: 'Sunset Amber', hex: '#FF9F43' },
      { name: 'Crimson Red',  hex: '#EA5455' }
    ],

    // Legacy Organic Themes Palette
    currentTheme: localStorage.getItem('crm_theme') || 'lavender',
    customTheme: {
      bg: '#bcb3e2',
      primary: '#9f8ed6',
      primaryHover: '#8d7bc8',
      textDark: '#5f4f8d',
      accent: '#e8a7b0',
      bgImage: '',
      headerBg: '',
      headerImage: '',
      sidebarLogo: '',
      shadowDark: 'transparent',
      shadowLight: 'transparent',
      ...(JSON.parse(localStorage.getItem('crm_custom_theme_colors') || '{}'))
    },
    themes: {
      sage: { bg: '#b6c8bf', primary: '#cc805c', primaryHover: '#b36f4f', textDark: '#3d4d46', accent: '#e6c8a8' },
      lavender: { bg: '#bcb3e2', primary: '#9f8ed6', primaryHover: '#8d7bc8', textDark: '#5f4f8d', accent: '#e8a7b0' },
      mint: { bg: '#a9d5c4', primary: '#579b82', primaryHover: '#4a8871', textDark: '#2e5c4a', accent: '#ecd278' },
      peach: { bg: '#f4c7c3', primary: '#d67b74', primaryHover: '#c96b64', textDark: '#80433e', accent: '#88a37b' },
      blue: { bg: '#b5d1e8', primary: '#6ca0cc', primaryHover: '#5c8fbb', textDark: '#37546e', accent: '#e9b2b8' },
      custom: { bg: '#f1f5f9', primary: '#7c3aed', primaryHover: '#6d28d9', textDark: '#4c1d95', accent: '#c084fc' }
    }
  }),

  actions: {
    setTemplate(templateName) {
      this.template = templateName;
      localStorage.setItem('crm_active_theme_template', templateName);
      this.applyAllStyles();
    },

    setSkin(skinName) {
      this.skin = skinName;
      localStorage.setItem('crm_active_theme_skin', skinName);
      this.applyAllStyles();
    },

    setPrimaryColor(hex) {
      this.primaryColor = hex;
      localStorage.setItem('crm_active_primary_color', hex);
      this.applyAllStyles();
    },

    setNavbarType(type) {
      this.navbarType = type;
      localStorage.setItem('crm_active_navbar_type', type);
      this.applyAllStyles();
    },

    setContentLayout(layout) {
      this.contentLayout = layout;
      localStorage.setItem('crm_active_content_layout', layout);
      this.applyAllStyles();
    },

    // Legacy Theme setting for organic mode
    setTheme(themeName) {
      if (themeName === 'custom') {
        this.currentTheme = 'custom';
        this.themes.custom = { ...this.customTheme };
      } else if (this.themes[themeName]) {
        this.currentTheme = themeName;
      }
      localStorage.setItem('crm_theme', this.currentTheme);
      this.applyLegacyOrganicTheme();
    },

    saveCustomTheme(customColors) {
      this.customTheme = { ...this.customTheme, ...customColors };
      this.themes.custom = { ...this.customTheme };
      this.currentTheme = 'custom';
      localStorage.setItem('crm_custom_theme_colors', JSON.stringify(this.customTheme));
      localStorage.setItem('crm_theme', 'custom');
      this.applyLegacyOrganicTheme();
    },

    applyAllStyles() {
      const root = document.documentElement;
      const body = document.body;

      // Update Template Class
      body.classList.remove('theme-template-vuexy', 'theme-template-organic');
      body.classList.add(`theme-template-${this.template}`);

      // Update Skin Class
      body.classList.remove('skin-light', 'skin-dark', 'skin-semi-dark', 'skin-bordered');
      body.classList.add(`skin-${this.skin}`);

      if (this.template === 'vuexy') {
        // Vuexy Color Properties
        root.style.setProperty('--vuexy-primary', this.primaryColor);
        // Convert hex to rgb for opacity mixing
        const hex = this.primaryColor.replace('#', '');
        const r = parseInt(hex.substring(0, 2), 16) || 115;
        const g = parseInt(hex.substring(2, 4), 16) || 103;
        const b = parseInt(hex.substring(4, 6), 16) || 240;
        root.style.setProperty('--vuexy-primary-rgb', `${r}, ${g}, ${b}`);
        
        // Dark mode variables
        if (this.skin === 'dark') {
          root.style.setProperty('--vuexy-bg', '#25293c');
          root.style.setProperty('--vuexy-card-bg', '#2f3349');
          root.style.setProperty('--vuexy-sidebar-bg', '#2f3349');
          root.style.setProperty('--vuexy-text', '#b6bee3');
          root.style.setProperty('--vuexy-text-heading', '#cfd3ec');
          root.style.setProperty('--vuexy-border', '#434968');
        } else {
          root.style.setProperty('--vuexy-bg', '#f8f7fa');
          root.style.setProperty('--vuexy-card-bg', '#ffffff');
          root.style.setProperty('--vuexy-sidebar-bg', this.skin === 'semi-dark' ? '#2f3349' : '#ffffff');
          root.style.setProperty('--vuexy-text', '#5d596c');
          root.style.setProperty('--vuexy-text-heading', '#4b465c');
          root.style.setProperty('--vuexy-border', '#dbdade');
        }
      } else {
        this.applyLegacyOrganicTheme();
      }
    },

    applyLegacyOrganicTheme() {
      let theme = this.themes[this.currentTheme] || this.themes.lavender;
      if (this.currentTheme === 'custom') {
        theme = this.customTheme;
      }
      const root = document.documentElement;
      root.style.setProperty('--theme-bg', theme.bg || '#bcb3e2');
      root.style.setProperty('--theme-primary', theme.primary || '#9f8ed6');
      root.style.setProperty('--theme-primary-hover', theme.primaryHover || '#8d7bc8');
      root.style.setProperty('--theme-text-dark', theme.textDark || '#5f4f8d');
      root.style.setProperty('--theme-accent', theme.accent || '#e8a7b0');
    }
  }
});
