<template>
  <router-view />
</template>

<script>
import { onMounted } from 'vue';
import { applyThemeStyles } from '../utils.js';

export default {
  name: 'App',
  setup() {
    onMounted(() => {
      try {
        const savedSettings = localStorage.getItem('crm_theme_style_settings');
        if (savedSettings) {
          applyThemeStyles(JSON.parse(savedSettings));
        } else {
          // Apply default settings automatically
          const defaultSettings = {
            admin_sidebar_bg: '#7e1e8e',
            admin_sidebar_link: '#e2e8f0',
            admin_sidebar_active_bg: '#6366f1',
            admin_sidebar_active_link: '#ffffff',
            admin_header_bg: '#ffffff',
            admin_header_link: '#4F4B5E',
            admin_content_bg: '#F8F7FA',

            cust_nav_bg: '#7e1e8e',
            cust_nav_link: '#ffffff',
            cust_footer_bg: '#2F3349',
            cust_footer_text: '#A5A2AD',

            btn_default: '#F1F0F2',
            btn_primary: '#7e1e8e',
            btn_info: '#00CFE8',
            btn_success: '#28C76F',
            btn_danger: '#EA5455',

            modal_heading_bg: '#7e1e8e',
            modal_heading_color: '#4F4B5E',
            modal_close_color: '#A5A2AD',
            modal_header_text_color: '#4F4B5E',

            table_link: '#7e1e8e',
            table_link_hover: '#671675',
            table_heading: '#4F4B5E',
            table_items_heading_bg: '#7e1e8e',
            table_items_heading_text: '#ffffff',

            gen_link: '#7e1e8e',
            gen_link_hover: '#671675',
            gen_login_bg: '#F8F7FA',
            gen_text_muted: '#A5A2AD',
            gen_text_danger: '#EA5455',
            gen_text_warning: '#FF9F43',
            gen_text_info: '#00CFE8',
            gen_text_success: '#28C76F'
          };
          applyThemeStyles(defaultSettings);
        }
      } catch (e) {
        console.error('Failed to load theme settings', e);
      }
    });
  }
}
</script>
