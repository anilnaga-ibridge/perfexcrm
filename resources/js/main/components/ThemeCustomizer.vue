<template>
  <div>
    <!-- Floating Gear Button pinned on the right edge -->
    <button 
      class="vuexy-customizer-toggle" 
      @click="isOpen = true" 
      title="Customize Theme & Layout"
    >
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="3"></circle>
        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
      </svg>
    </button>

    <!-- Customizer Slide-out Drawer -->
    <a-drawer
      v-model:open="isOpen"
      title="THEME CUSTOMIZER"
      placement="right"
      :width="360"
      :closable="true"
      :bodyStyle="{ padding: '20px', background: '#ffffff', color: '#5d596c' }"
      :headerStyle="{ borderBottom: '1px solid #dbdade', padding: '16px 20px' }"
    >
      <div class="customizer-content">
        <p class="customizer-sub">Customize & Preview UI styles in Real Time</p>

        <!-- 1. THEME TEMPLATE SELECTION -->
        <div class="customizer-section">
          <label class="customizer-label">UI TEMPLATE STYLE</label>
          <div class="template-grid">
            <!-- Vuexy Card -->
            <div 
              class="template-card" 
              :class="{ 'template-card--active': themeStore.template === 'vuexy' }"
              @click="themeStore.setTemplate('vuexy')"
            >
              <div class="template-card__preview vuexy-preview">
                <div class="preview-sidebar"></div>
                <div class="preview-body">
                  <div class="preview-nav"></div>
                  <div class="preview-content"></div>
                </div>
              </div>
              <div class="template-card__info">
                <span class="template-card__title">Vuexy Enterprise</span>
                <span class="template-card__desc">Modern Clean Dashboard</span>
              </div>
              <div class="template-card__radio" v-if="themeStore.template === 'vuexy'">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
            </div>

            <!-- Organic Claymorphic Card -->
            <div 
              class="template-card" 
              :class="{ 'template-card--active': themeStore.template === 'organic' }"
              @click="themeStore.setTemplate('organic')"
            >
              <div class="template-card__preview organic-preview">
                <div class="preview-sidebar-org"></div>
                <div class="preview-body-org">
                  <div class="preview-wave"></div>
                </div>
              </div>
              <div class="template-card__info">
                <span class="template-card__title">Claymorphic Organic</span>
                <span class="template-card__desc">Wave & Artistic CRM</span>
              </div>
              <div class="template-card__radio" v-if="themeStore.template === 'organic'">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
            </div>
          </div>
        </div>

        <div class="customizer-divider"></div>

        <!-- 2. SKIN MODE (For Vuexy) -->
        <div v-if="themeStore.template === 'vuexy'" class="customizer-section">
          <label class="customizer-label">THEME SKIN</label>
          <div class="skin-options">
            <div 
              v-for="s in skins" 
              :key="s.id"
              class="skin-btn"
              :class="{ 'skin-btn--active': themeStore.skin === s.id }"
              @click="themeStore.setSkin(s.id)"
            >
              <span class="skin-btn__icon" v-html="s.icon"></span>
              <span class="skin-btn__label">{{ s.label }}</span>
            </div>
          </div>
        </div>

        <div v-if="themeStore.template === 'vuexy'" class="customizer-divider"></div>

        <!-- 3. PRIMARY COLOR PALETTES -->
        <div class="customizer-section">
          <label class="customizer-label">PRIMARY ACCENT COLOR</label>
          <div class="color-swatches">
            <button
              v-for="c in themeStore.colorPresets"
              :key="c.hex"
              class="color-dot"
              :style="{ background: c.hex }"
              :class="{ 'color-dot--active': themeStore.primaryColor.toLowerCase() === c.hex.toLowerCase() }"
              @click="themeStore.setPrimaryColor(c.hex)"
              :title="c.name"
            >
              <svg v-if="themeStore.primaryColor.toLowerCase() === c.hex.toLowerCase()" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#fff" stroke-width="3">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </button>
          </div>
        </div>

        <div v-if="themeStore.template === 'vuexy'" class="customizer-divider"></div>

        <!-- 4. NAVBAR TYPE -->
        <div v-if="themeStore.template === 'vuexy'" class="customizer-section">
          <label class="customizer-label">NAVBAR TYPE</label>
          <div class="navbar-types">
            <button 
              class="nav-type-btn"
              :class="{ 'nav-type-btn--active': themeStore.navbarType === 'floating' }"
              @click="themeStore.setNavbarType('floating')"
            >
              Floating
            </button>
            <button 
              class="nav-type-btn"
              :class="{ 'nav-type-btn--active': themeStore.navbarType === 'sticky' }"
              @click="themeStore.setNavbarType('sticky')"
            >
              Sticky
            </button>
            <button 
              class="nav-type-btn"
              :class="{ 'nav-type-btn--active': themeStore.navbarType === 'static' }"
              @click="themeStore.setNavbarType('static')"
            >
              Static
            </button>
          </div>
        </div>

        <div class="customizer-divider"></div>

        <!-- Action: Reset to Defaults -->
        <div class="customizer-footer">
          <button class="reset-btn" @click="resetDefaults">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
            Reset All Defaults
          </button>
        </div>
      </div>
    </a-drawer>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useThemeStore } from '../store/themeStore';

const isOpen = ref(false);
const themeStore = useThemeStore();

const skins = [
  {
    id: 'light',
    label: 'Light',
    icon: `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>`
  },
  {
    id: 'dark',
    label: 'Dark',
    icon: `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>`
  },
  {
    id: 'semi-dark',
    label: 'Semi Dark',
    icon: `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/></svg>`
  },
  {
    id: 'bordered',
    label: 'Bordered',
    icon: `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="3" stroke-dasharray="4 4"/></svg>`
  }
];

function resetDefaults() {
  themeStore.setTemplate('vuexy');
  themeStore.setSkin('light');
  themeStore.setPrimaryColor('#7367F0');
  themeStore.setNavbarType('floating');
}
</script>

<style scoped>
.customizer-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.customizer-sub {
  font-size: 13px;
  color: #82808c;
  margin-top: -8px;
  margin-bottom: 4px;
}

.customizer-section {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.customizer-label {
  font-size: 11.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: #a8aaae;
}

.customizer-divider {
  height: 1px;
  background: #dbdade;
  margin: 4px 0;
}

/* Template Cards Grid */
.template-grid {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.template-card {
  border: 1.5px solid #dbdade;
  border-radius: 8px;
  padding: 10px 14px;
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  background: #ffffff;
}

.template-card:hover {
  border-color: var(--vuexy-primary, #7367F0);
}

.template-card--active {
  border-color: var(--vuexy-primary, #7367F0) !important;
  background: rgba(115, 103, 240, 0.05);
}

.template-card__preview {
  width: 48px;
  height: 38px;
  border-radius: 4px;
  border: 1px solid #dbdade;
  display: flex;
  overflow: hidden;
  flex-shrink: 0;
}

.vuexy-preview .preview-sidebar {
  width: 14px;
  background: #7367F0;
}
.vuexy-preview .preview-body {
  flex: 1;
  background: #f8f7fa;
  padding: 3px;
  display: flex;
  flex-direction: column;
  gap: 3px;
}
.vuexy-preview .preview-nav {
  height: 6px;
  background: #ffffff;
  border-radius: 2px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.vuexy-preview .preview-content {
  flex: 1;
  background: #ffffff;
  border-radius: 2px;
}

.organic-preview .preview-sidebar-org {
  width: 14px;
  background: #7e1e8e;
}
.organic-preview .preview-body-org {
  flex: 1;
  background: #bcb3e2;
  position: relative;
  overflow: hidden;
}
.organic-preview .preview-wave {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 12px;
  background: #9f8ed6;
  border-radius: 8px 8px 0 0;
}

.template-card__info {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.template-card__title {
  font-size: 13.5px;
  font-weight: 600;
  color: #4b465c;
}

.template-card__desc {
  font-size: 11.5px;
  color: #82808c;
}

.template-card__radio {
  color: var(--vuexy-primary, #7367F0);
}

/* Skin Options Grid */
.skin-options {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
}

.skin-btn {
  border: 1.5px solid #dbdade;
  border-radius: 6px;
  padding: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  background: #ffffff;
  color: #5d596c;
  transition: all 0.2s;
}

.skin-btn:hover {
  border-color: var(--vuexy-primary, #7367F0);
  color: var(--vuexy-primary, #7367F0);
}

.skin-btn--active {
  border-color: var(--vuexy-primary, #7367F0);
  color: var(--vuexy-primary, #7367F0);
  background: rgba(115, 103, 240, 0.08);
}

.skin-btn__label {
  font-size: 12px;
  font-weight: 500;
}

/* Color Swatches */
.color-swatches {
  display: flex;
  align-items: center;
  gap: 12px;
}

.color-dot {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 2px solid transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.color-dot:hover {
  transform: scale(1.1);
}

.color-dot--active {
  border-color: #ffffff;
  box-shadow: 0 0 0 2px var(--vuexy-primary, #7367F0), 0 3px 8px rgba(0,0,0,0.25);
}

/* Navbar Types */
.navbar-types {
  display: flex;
  gap: 8px;
}

.nav-type-btn {
  flex: 1;
  padding: 8px;
  border: 1.5px solid #dbdade;
  background: #ffffff;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 500;
  color: #5d596c;
  cursor: pointer;
  transition: all 0.2s;
}

.nav-type-btn:hover {
  border-color: var(--vuexy-primary, #7367F0);
  color: var(--vuexy-primary, #7367F0);
}

.nav-type-btn--active {
  border-color: var(--vuexy-primary, #7367F0);
  background: var(--vuexy-primary, #7367F0);
  color: #ffffff;
}

/* Reset Button */
.reset-btn {
  width: 100%;
  padding: 10px;
  border-radius: 6px;
  border: 1px dashed #dbdade;
  background: #f8f7fa;
  color: #5d596c;
  font-size: 13px;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.reset-btn:hover {
  border-color: #EA5455;
  color: #EA5455;
  background: rgba(234, 84, 85, 0.05);
}
</style>
