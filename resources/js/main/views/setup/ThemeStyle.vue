<template>
  <div class="theme-style-page p-6 max-w-[1600px] mx-auto min-h-screen bg-[#F8F7FA] font-['Public_Sans',sans-serif]">
    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2">
          <span class="p-2 bg-[#7367F0]/10 text-[#7367F0] rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path></svg>
          </span>
          <div>
            <h1 class="text-2xl font-bold text-[#4B465C] tracking-tight m-0">Theme Style &amp; Brand Customizer</h1>
            <span class="text-xs text-[#82868B] font-medium">Customize global CRM branding, palettes, sidebar, buttons, logos, and custom CSS</span>
          </div>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <button 
          type="button" 
          class="px-4 py-2 bg-white border border-[#DBDADE] hover:bg-[#F8F7FA] text-[#4B465C] rounded-lg text-xs font-bold transition-all shadow-sm cursor-pointer"
          @click="resetDynamicThemeDefaults"
        >
          Reset Defaults
        </button>
        <button 
          type="button" 
          class="flex items-center gap-2 px-5 py-2 bg-[#7367F0] hover:bg-[#685dd8] text-white rounded-lg text-xs font-bold transition-all shadow-sm shadow-[#7367F0]/30 cursor-pointer border-none"
          :disabled="saving"
          @click="saveThemeSettings"
        >
          <svg v-if="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
          <div v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          {{ saving ? 'Saving Changes...' : 'Save & Apply Theme' }}
        </button>
      </div>
    </div>

    <!-- MAIN TABS CONTAINER -->
    <div class="bg-white border border-[#EBE9F1] rounded-lg shadow-sm overflow-hidden mb-6">
      <!-- Tabs Header Navigation -->
      <div class="flex flex-wrap border-b border-[#EBE9F1] bg-[#F8F7FA] px-2 pt-2 gap-1 overflow-x-auto">
        <button 
          v-for="tab in tabList" 
          :key="tab.key" 
          class="flex items-center gap-2 px-4 py-3 rounded-t-lg text-xs font-bold transition-all border-b-2 whitespace-nowrap cursor-pointer"
          :class="activeTab === tab.key ? 'bg-white text-[#7367F0] border-[#7367F0] shadow-sm' : 'border-transparent text-[#82868B] hover:text-[#4B465C] hover:bg-white/60'"
          @click="activeTab = tab.key"
        >
          <span>{{ tab.icon }}</span>
          <span>{{ tab.label }}</span>
        </button>
      </div>

      <div class="p-6">
        <!-- ========================================== -->
        <!-- 0. DYNAMIC THEME & COLOR PALETTE           -->
        <!-- ========================================== -->
        <div v-if="activeTab === 'dynamic_theme'" class="space-y-6">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-[#EBE9F1]">
            <div>
              <h3 class="text-base font-bold text-[#4B465C] m-0">Admin Dynamic Theme &amp; Branding Customizer</h3>
              <p class="text-xs text-[#82868B] m-0 mt-1">Manage live colors, page backgrounds, primary buttons, navbar gradients, and branding logos across the CRM.</p>
            </div>
            <button
              type="button"
              class="px-4 py-2 bg-[#7367F0] hover:bg-[#685dd8] text-white text-xs font-bold rounded-lg shadow-sm transition-all cursor-pointer border-none flex items-center gap-1.5"
              @click="saveDynamicTheme"
            >
              <span>Save &amp; Apply Dynamic Theme</span>
            </button>
          </div>

          <!-- Preset Color Theme Picker -->
          <div class="p-5 rounded-lg border border-[#EBE9F1] bg-white">
            <h4 class="text-xs font-bold text-[#82868B] uppercase tracking-wider mb-3">Preset Color Themes</h4>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
              <button
                v-for="(tObj, tKey) in themeStore.themes"
                :key="tKey"
                type="button"
                class="p-3 rounded-lg text-xs font-bold transition-all border cursor-pointer flex flex-col items-center gap-2"
                :class="themeStore.currentTheme === tKey ? 'bg-[#7367F0]/10 border-[#7367F0] text-[#7367F0] ring-2 ring-[#7367F0]/20' : 'bg-[#F8F7FA] border-[#EBE9F1] text-[#4B465C] hover:bg-[#F8F7FA]/70'"
                @click="applyThemePreset(tKey)"
              >
                <div class="w-8 h-8 rounded-full border border-[#DBDADE] shadow-xs" :style="{ backgroundColor: tObj.primary }"></div>
                <span class="capitalize text-xs font-bold">{{ tKey }}</span>
              </button>
            </div>
          </div>

          <!-- Custom Color Pickers Grid -->
          <div class="p-5 rounded-lg border border-[#EBE9F1] bg-white space-y-4">
            <div class="flex items-center justify-between border-b border-[#EBE9F1] pb-3">
              <h4 class="text-xs font-bold text-[#82868B] uppercase tracking-wider m-0">Custom Color Palette (Hex &amp; Pickers)</h4>
              <span class="text-xs text-[#82868B] font-medium">Real-time CSS variable injection</span>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <!-- Primary Theme Color -->
              <div class="p-3 rounded-lg border border-[#EBE9F1] bg-[#F8F7FA]">
                <label class="block text-xs font-bold text-[#4B465C] mb-2">Primary Color (Buttons &amp; Active Items)</label>
                <div class="flex items-center gap-2">
                  <input type="color" v-model="customThemeForm.primary" class="w-9 h-9 rounded border border-[#DBDADE] cursor-pointer p-0 bg-transparent overflow-hidden shrink-0" />
                  <input type="text" v-model="customThemeForm.primary" class="w-full h-9 px-2 text-xs font-mono font-bold bg-white border border-[#DBDADE] rounded text-[#4B465C]" />
                </div>
              </div>

              <!-- Primary Hover Color -->
              <div class="p-3 rounded-lg border border-[#EBE9F1] bg-[#F8F7FA]">
                <label class="block text-xs font-bold text-[#4B465C] mb-2">Primary Hover State Color</label>
                <div class="flex items-center gap-2">
                  <input type="color" v-model="customThemeForm.primaryHover" class="w-9 h-9 rounded border border-[#DBDADE] cursor-pointer p-0 bg-transparent overflow-hidden shrink-0" />
                  <input type="text" v-model="customThemeForm.primaryHover" class="w-full h-9 px-2 text-xs font-mono font-bold bg-white border border-[#DBDADE] rounded text-[#4B465C]" />
                </div>
              </div>

              <!-- Dark Text / Header Dark Color -->
              <div class="p-3 rounded-lg border border-[#EBE9F1] bg-[#F8F7FA]">
                <label class="block text-xs font-bold text-[#4B465C] mb-2">Navbar &amp; Heading Dark Text</label>
                <div class="flex items-center gap-2">
                  <input type="color" v-model="customThemeForm.textDark" class="w-9 h-9 rounded border border-[#DBDADE] cursor-pointer p-0 bg-transparent overflow-hidden shrink-0" />
                  <input type="text" v-model="customThemeForm.textDark" class="w-full h-9 px-2 text-xs font-mono font-bold bg-white border border-[#DBDADE] rounded text-[#4B465C]" />
                </div>
              </div>

              <!-- Outer Page Background Color -->
              <div class="p-3 rounded-lg border border-[#EBE9F1] bg-[#F8F7FA]">
                <label class="block text-xs font-bold text-[#4B465C] mb-2">Outer Page Background</label>
                <div class="flex items-center gap-2">
                  <input type="color" v-model="customThemeForm.bg" class="w-9 h-9 rounded border border-[#DBDADE] cursor-pointer p-0 bg-transparent overflow-hidden shrink-0" />
                  <input type="text" v-model="customThemeForm.bg" class="w-full h-9 px-2 text-xs font-mono font-bold bg-white border border-[#DBDADE] rounded text-[#4B465C]" />
                </div>
              </div>
            </div>
          </div>

          <!-- Custom Branding & Background Images -->
          <div class="p-5 rounded-lg border border-[#EBE9F1] bg-white space-y-4">
            <h4 class="text-xs font-bold text-[#82868B] uppercase tracking-wider border-b border-[#EBE9F1] pb-3 m-0">Application Background &amp; Branding Image Uploads</h4>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- 1. Main Background Image -->
              <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white flex flex-col justify-between">
                <div>
                  <label class="block text-xs font-bold text-[#4B465C] mb-1">Outer Application Background</label>
                  <p class="text-[11px] text-[#82868B] mb-3">Wallpaper image applied behind CRM workspace</p>
                </div>

                <div class="border-2 border-dashed border-[#DBDADE] hover:border-[#7367F0] rounded-lg p-4 text-center flex flex-col items-center justify-center min-h-[120px] bg-[#F8F7FA]">
                  <div v-if="customThemeForm.bgImage" class="mb-3 p-2 bg-white rounded border border-[#EBE9F1] flex items-center justify-center max-h-16">
                    <img :src="customThemeForm.bgImage" alt="App Background Preview" class="max-h-12 object-contain" />
                  </div>
                  
                  <div class="flex items-center gap-2 flex-wrap justify-center">
                    <label class="px-3 py-1.5 bg-[#7367F0] hover:bg-[#685dd8] text-white text-xs font-bold rounded cursor-pointer transition-all flex items-center gap-1.5 shadow-sm">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                      <span>{{ customThemeForm.bgImage ? 'Change Image' : 'Upload Image' }}</span>
                      <input type="file" accept="image/*" class="hidden" @change="handleDynamicFileUpload($event, 'bgImage')" />
                    </label>

                    <button v-if="customThemeForm.bgImage" type="button" class="px-2.5 py-1.5 text-xs font-bold text-[#EA5455] hover:bg-[#EA5455]/10 rounded border border-[#EA5455]/30 cursor-pointer" @click="removeDynamicFile('bgImage')">
                      Remove
                    </button>
                  </div>
                </div>
              </div>

              <!-- 2. Header Image -->
              <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white flex flex-col justify-between">
                <div>
                  <label class="block text-xs font-bold text-[#4B465C] mb-1">Header Navbar Pattern</label>
                  <p class="text-[11px] text-[#82868B] mb-3">Pattern or banner for the top navbar</p>
                </div>

                <div class="border-2 border-dashed border-[#DBDADE] hover:border-[#7367F0] rounded-lg p-4 text-center flex flex-col items-center justify-center min-h-[120px] bg-[#F8F7FA]">
                  <div v-if="customThemeForm.headerImage" class="mb-3 p-2 bg-white rounded border border-[#EBE9F1] flex items-center justify-center max-h-16">
                    <img :src="customThemeForm.headerImage" alt="Header Preview" class="max-h-12 object-contain" />
                  </div>
                  
                  <div class="flex items-center gap-2 flex-wrap justify-center">
                    <label class="px-3 py-1.5 bg-[#7367F0] hover:bg-[#685dd8] text-white text-xs font-bold rounded cursor-pointer transition-all flex items-center gap-1.5 shadow-sm">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                      <span>{{ customThemeForm.headerImage ? 'Change Image' : 'Upload Pattern' }}</span>
                      <input type="file" accept="image/*" class="hidden" @change="handleDynamicFileUpload($event, 'headerImage')" />
                    </label>

                    <button v-if="customThemeForm.headerImage" type="button" class="px-2.5 py-1.5 text-xs font-bold text-[#EA5455] hover:bg-[#EA5455]/10 rounded border border-[#EA5455]/30 cursor-pointer" @click="removeDynamicFile('headerImage')">
                      Remove
                    </button>
                  </div>
                </div>
              </div>

              <!-- 3. Sidebar Logo -->
              <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white flex flex-col justify-between">
                <div>
                  <label class="block text-xs font-bold text-[#4B465C] mb-1">Custom Sidebar Branding Logo</label>
                  <p class="text-[11px] text-[#82868B] mb-3">Replaces top-left sidebar brand logo</p>
                </div>

                <div class="border-2 border-dashed border-[#DBDADE] hover:border-[#7367F0] rounded-lg p-4 text-center flex flex-col items-center justify-center min-h-[120px] bg-[#F8F7FA]">
                  <div v-if="customThemeForm.sidebarLogo" class="mb-3 p-2 bg-white rounded border border-[#EBE9F1] flex items-center justify-center max-h-16">
                    <img :src="customThemeForm.sidebarLogo" alt="Sidebar Logo Preview" class="max-h-12 object-contain" />
                  </div>

                  <div class="flex items-center gap-2 flex-wrap justify-center">
                    <label class="px-3 py-1.5 bg-[#7367F0] hover:bg-[#685dd8] text-white text-xs font-bold rounded cursor-pointer transition-all flex items-center gap-1.5 shadow-sm">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                      <span>{{ customThemeForm.sidebarLogo ? 'Change Logo' : 'Upload Logo' }}</span>
                      <input type="file" accept="image/*" class="hidden" @change="handleDynamicFileUpload($event, 'sidebarLogo')" />
                    </label>

                    <button v-if="customThemeForm.sidebarLogo" type="button" class="px-2.5 py-1.5 text-xs font-bold text-[#EA5455] hover:bg-[#EA5455]/10 rounded border border-[#EA5455]/30 cursor-pointer" @click="removeDynamicFile('sidebarLogo')">
                      Remove
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Live Theme Preview Canvas -->
          <div class="p-5 rounded-lg border border-[#EBE9F1] bg-white space-y-3">
            <div class="flex items-center justify-between border-b border-[#EBE9F1] pb-3">
              <span class="text-xs font-bold text-[#82868B] uppercase tracking-wider">Live Dynamic Theme Canvas Preview</span>
              <span class="px-2.5 py-0.5 text-[10px] font-bold bg-[#7367F0]/10 text-[#7367F0] rounded">Real-Time</span>
            </div>
            <div class="p-6 rounded-lg text-white shadow-sm transition-all" :style="{ background: 'linear-gradient(135deg, ' + (customThemeForm.textDark || '#2F3349') + ' 0%, ' + (customThemeForm.primary || '#7367F0') + ' 100%)' }">
              <h4 class="text-sm font-bold text-white m-0 mb-1">Interactive Theme Preview</h4>
              <p class="text-xs text-white/80 m-0 max-w-lg mb-4">Demonstrating active header gradient, contrast levels, and primary button hover states.</p>
              <div class="flex items-center gap-3">
                <button type="button" class="px-4 py-2 rounded-md font-bold text-xs shadow-sm border-none cursor-pointer" :style="{ background: customThemeForm.primary || '#7367F0', color: '#fff' }">Primary Action</button>
                <button type="button" class="px-4 py-2 rounded-md font-bold text-xs bg-white/20 hover:bg-white/30 text-white border-none cursor-pointer">Secondary Action</button>
              </div>
            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- 1. LOGO & BRAND IDENTITY                   -->
        <!-- ========================================== -->
        <div v-if="activeTab === 'logo_management'" class="space-y-6">
          <div class="pb-4 border-b border-[#EBE9F1]">
            <h3 class="text-base font-bold text-[#4B465C] m-0">Brand Logo &amp; Favicon Studio</h3>
            <p class="text-xs text-[#82868B] m-0 mt-1">Manage brand identity, sidebar logos, login page graphics, mascot controls, and browser favicon.</p>
          </div>

          <!-- Company Brand Name -->
          <div class="p-5 rounded-lg border border-[#EBE9F1] bg-white space-y-4">
            <h4 class="text-xs font-bold text-[#82868B] uppercase tracking-wider border-b border-[#EBE9F1] pb-2 m-0">Company Brand Identity</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-[#4B465C] mb-1">Company Name *</label>
                <input type="text" v-model="settings.company_name" class="w-full px-3 py-2 text-xs bg-white border border-[#DBDADE] rounded-md focus:outline-none focus:border-[#7367F0] text-[#4B465C] font-semibold" placeholder="e.g. Ibridge Digital" />
              </div>
              <div>
                <label class="block text-xs font-bold text-[#4B465C] mb-1">Page Title Prefix</label>
                <input type="text" v-model="settings.app_page_title" class="w-full px-3 py-2 text-xs bg-white border border-[#DBDADE] rounded-md focus:outline-none focus:border-[#7367F0] text-[#4B465C] font-semibold" placeholder="e.g. Ibridge Digital CRM" />
              </div>
              <div>
                <label class="block text-xs font-bold text-[#4B465C] mb-1">Sidebar Brand Text</label>
                <input type="text" v-model="settings.sidebar_logo_text" class="w-full px-3 py-2 text-xs bg-white border border-[#DBDADE] rounded-md focus:outline-none focus:border-[#7367F0] text-[#4B465C] font-semibold" placeholder="e.g. Ibridge Digital" />
              </div>
              <div>
                <label class="block text-xs font-bold text-[#4B465C] mb-1">Login Screen Title</label>
                <input type="text" v-model="settings.login_logo_text" class="w-full px-3 py-2 text-xs bg-white border border-[#DBDADE] rounded-md focus:outline-none focus:border-[#7367F0] text-[#4B465C] font-semibold" placeholder="e.g. Ibridge Digital CRM" />
              </div>
            </div>
          </div>

          <!-- Logo Dropzones Grid -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Sidebar Logo -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white space-y-3">
              <h4 class="text-xs font-bold text-[#4B465C] m-0">Sidebar Navigation Logo</h4>
              <div class="border-2 border-dashed border-[#DBDADE] rounded-lg p-4 text-center bg-[#F8F7FA]">
                <div v-if="settings.sidebar_logo_url" class="mb-3 p-2 bg-white rounded border border-[#EBE9F1] flex items-center justify-center max-h-16">
                  <img :src="settings.sidebar_logo_url" alt="Sidebar Logo" class="max-h-12 object-contain" />
                </div>
                <div class="flex items-center justify-center gap-2">
                  <label class="px-3 py-1.5 bg-[#7367F0] hover:bg-[#685dd8] text-white text-xs font-bold rounded cursor-pointer transition-all flex items-center gap-1">
                    <span>Upload Logo</span>
                    <input type="file" accept="image/*" class="hidden" @change="handleFileUpload($event, 'sidebar_logo_url')" />
                  </label>
                  <button v-if="settings.sidebar_logo_url" type="button" class="px-2.5 py-1.5 text-xs text-[#82868B] hover:text-[#4B465C] bg-white border border-[#DBDADE] rounded" @click="resetSidebarLogo">Reset</button>
                </div>
              </div>
            </div>

            <!-- Login Logo -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white space-y-3">
              <h4 class="text-xs font-bold text-[#4B465C] m-0">Login Screen Brand Logo</h4>
              <div class="border-2 border-dashed border-[#DBDADE] rounded-lg p-4 text-center bg-[#F8F7FA]">
                <div v-if="settings.login_logo_url" class="mb-3 p-2 bg-white rounded border border-[#EBE9F1] flex items-center justify-center max-h-16">
                  <img :src="settings.login_logo_url" alt="Login Logo" class="max-h-12 object-contain" />
                </div>
                <div class="flex items-center justify-center gap-2">
                  <label class="px-3 py-1.5 bg-[#7367F0] hover:bg-[#685dd8] text-white text-xs font-bold rounded cursor-pointer transition-all flex items-center gap-1">
                    <span>Upload Logo</span>
                    <input type="file" accept="image/*" class="hidden" @change="handleFileUpload($event, 'login_logo_url')" />
                  </label>
                  <button v-if="settings.login_logo_url" type="button" class="px-2.5 py-1.5 text-xs text-[#82868B] hover:text-[#4B465C] bg-white border border-[#DBDADE] rounded" @click="resetLoginLogo">Reset</button>
                </div>
              </div>
            </div>

            <!-- Favicon -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white space-y-3">
              <h4 class="text-xs font-bold text-[#4B465C] m-0">Website Favicon (.ico / .png)</h4>
              <div class="border-2 border-dashed border-[#DBDADE] rounded-lg p-4 text-center bg-[#F8F7FA]">
                <div v-if="settings.favicon_url" class="mb-3 p-2 bg-white rounded border border-[#EBE9F1] flex items-center justify-center max-h-16">
                  <img :src="resolveFaviconUrl(settings.favicon_url)" alt="Favicon" class="w-8 h-8 object-contain" />
                </div>
                <div class="flex items-center justify-center gap-2">
                  <label class="px-3 py-1.5 bg-[#7367F0] hover:bg-[#685dd8] text-white text-xs font-bold rounded cursor-pointer transition-all flex items-center gap-1">
                    <span>Upload Favicon</span>
                    <input type="file" accept="image/*" class="hidden" @change="handleFileUpload($event, 'favicon_url')" />
                  </label>
                  <button v-if="settings.favicon_url" type="button" class="px-2.5 py-1.5 text-xs text-[#82868B] hover:text-[#4B465C] bg-white border border-[#DBDADE] rounded" @click="resetFavicon">Reset</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- 2. ADMIN MENU & NAVBAR                     -->
        <!-- ========================================== -->
        <div v-if="activeTab === 'admin_menu'" class="space-y-6">
          <div class="pb-4 border-b border-[#EBE9F1]">
            <h3 class="text-base font-bold text-[#4B465C] m-0">Admin Menu &amp; Navbar Styling</h3>
            <p class="text-xs text-[#82868B] m-0 mt-1">Configure sidebar menu background, active pills, text colors, and navbar styling.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="p-3 rounded-lg border border-[#EBE9F1] bg-[#F8F7FA]">
              <label class="block text-xs font-bold text-[#4B465C] mb-2">Admin Sidebar Background</label>
              <div class="flex items-center gap-2">
                <input type="color" v-model="settings.admin_sidebar_bg" class="w-9 h-9 rounded border border-[#DBDADE] cursor-pointer p-0 bg-transparent shrink-0" />
                <input type="text" v-model="settings.admin_sidebar_bg" class="w-full h-9 px-2 text-xs font-mono font-bold bg-white border border-[#DBDADE] rounded text-[#4B465C]" />
              </div>
            </div>

            <div class="p-3 rounded-lg border border-[#EBE9F1] bg-[#F8F7FA]">
              <label class="block text-xs font-bold text-[#4B465C] mb-2">Sidebar Links Text Color</label>
              <div class="flex items-center gap-2">
                <input type="color" v-model="settings.admin_sidebar_link" class="w-9 h-9 rounded border border-[#DBDADE] cursor-pointer p-0 bg-transparent shrink-0" />
                <input type="text" v-model="settings.admin_sidebar_link" class="w-full h-9 px-2 text-xs font-mono font-bold bg-white border border-[#DBDADE] rounded text-[#4B465C]" />
              </div>
            </div>

            <div class="p-3 rounded-lg border border-[#EBE9F1] bg-[#F8F7FA]">
              <label class="block text-xs font-bold text-[#4B465C] mb-2">Sidebar Active Item Background</label>
              <div class="flex items-center gap-2">
                <input type="color" v-model="settings.admin_sidebar_active_bg" class="w-9 h-9 rounded border border-[#DBDADE] cursor-pointer p-0 bg-transparent shrink-0" />
                <input type="text" v-model="settings.admin_sidebar_active_bg" class="w-full h-9 px-2 text-xs font-mono font-bold bg-white border border-[#DBDADE] rounded text-[#4B465C]" />
              </div>
            </div>

            <div class="p-3 rounded-lg border border-[#EBE9F1] bg-[#F8F7FA]">
              <label class="block text-xs font-bold text-[#4B465C] mb-2">Sidebar Active Link Text Color</label>
              <div class="flex items-center gap-2">
                <input type="color" v-model="settings.admin_sidebar_active_link" class="w-9 h-9 rounded border border-[#DBDADE] cursor-pointer p-0 bg-transparent shrink-0" />
                <input type="text" v-model="settings.admin_sidebar_active_link" class="w-full h-9 px-2 text-xs font-mono font-bold bg-white border border-[#DBDADE] rounded text-[#4B465C]" />
              </div>
            </div>

            <div class="p-3 rounded-lg border border-[#EBE9F1] bg-[#F8F7FA]">
              <label class="block text-xs font-bold text-[#4B465C] mb-2">Top Navbar Background</label>
              <div class="flex items-center gap-2">
                <input type="color" v-model="settings.admin_header_bg" class="w-9 h-9 rounded border border-[#DBDADE] cursor-pointer p-0 bg-transparent shrink-0" />
                <input type="text" v-model="settings.admin_header_bg" class="w-full h-9 px-2 text-xs font-mono font-bold bg-white border border-[#DBDADE] rounded text-[#4B465C]" />
              </div>
            </div>

            <div class="p-3 rounded-lg border border-[#EBE9F1] bg-[#F8F7FA]">
              <label class="block text-xs font-bold text-[#4B465C] mb-2">Workspace Content Background</label>
              <div class="flex items-center gap-2">
                <input type="color" v-model="settings.admin_content_bg" class="w-9 h-9 rounded border border-[#DBDADE] cursor-pointer p-0 bg-transparent shrink-0" />
                <input type="text" v-model="settings.admin_content_bg" class="w-full h-9 px-2 text-xs font-mono font-bold bg-white border border-[#DBDADE] rounded text-[#4B465C]" />
              </div>
            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- 3. BUTTON STYLES & ACCENTS                 -->
        <!-- ========================================== -->
        <div v-if="activeTab === 'buttons'" class="space-y-6">
          <div class="pb-4 border-b border-[#EBE9F1]">
            <h3 class="text-base font-bold text-[#4B465C] m-0">Buttons &amp; UI Accent Styling</h3>
            <p class="text-xs text-[#82868B] m-0 mt-1">Configure action buttons and live feedback pills across all modules.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white space-y-3">
              <label class="block text-xs font-bold text-[#4B465C]">Primary Action Button</label>
              <div class="flex items-center gap-2">
                <input type="color" v-model="settings.btn_primary" class="w-9 h-9 rounded border border-[#DBDADE] cursor-pointer p-0 bg-transparent shrink-0" />
                <input type="text" v-model="settings.btn_primary" class="w-full h-9 px-2 text-xs font-mono font-bold bg-[#F8F7FA] border border-[#DBDADE] rounded text-[#4B465C]" />
              </div>
              <button type="button" class="w-full py-2 text-white text-xs font-bold rounded-md shadow-sm border-none cursor-pointer" :style="{ background: settings.btn_primary }">
                Primary Button Sample
              </button>
            </div>

            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white space-y-3">
              <label class="block text-xs font-bold text-[#4B465C]">Success Action Button</label>
              <div class="flex items-center gap-2">
                <input type="color" v-model="settings.btn_success" class="w-9 h-9 rounded border border-[#DBDADE] cursor-pointer p-0 bg-transparent shrink-0" />
                <input type="text" v-model="settings.btn_success" class="w-full h-9 px-2 text-xs font-mono font-bold bg-[#F8F7FA] border border-[#DBDADE] rounded text-[#4B465C]" />
              </div>
              <button type="button" class="w-full py-2 text-white text-xs font-bold rounded-md shadow-sm border-none cursor-pointer" :style="{ background: settings.btn_success }">
                Success Button Sample
              </button>
            </div>

            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white space-y-3">
              <label class="block text-xs font-bold text-[#4B465C]">Info Action Button</label>
              <div class="flex items-center gap-2">
                <input type="color" v-model="settings.btn_info" class="w-9 h-9 rounded border border-[#DBDADE] cursor-pointer p-0 bg-transparent shrink-0" />
                <input type="text" v-model="settings.btn_info" class="w-full h-9 px-2 text-xs font-mono font-bold bg-[#F8F7FA] border border-[#DBDADE] rounded text-[#4B465C]" />
              </div>
              <button type="button" class="w-full py-2 text-white text-xs font-bold rounded-md shadow-sm border-none cursor-pointer" :style="{ background: settings.btn_info }">
                Info Button Sample
              </button>
            </div>

            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white space-y-3">
              <label class="block text-xs font-bold text-[#4B465C]">Danger Action Button</label>
              <div class="flex items-center gap-2">
                <input type="color" v-model="settings.btn_danger" class="w-9 h-9 rounded border border-[#DBDADE] cursor-pointer p-0 bg-transparent shrink-0" />
                <input type="text" v-model="settings.btn_danger" class="w-full h-9 px-2 text-xs font-mono font-bold bg-[#F8F7FA] border border-[#DBDADE] rounded text-[#4B465C]" />
              </div>
              <button type="button" class="w-full py-2 text-white text-xs font-bold rounded-md shadow-sm border-none cursor-pointer" :style="{ background: settings.btn_danger }">
                Danger Button Sample
              </button>
            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- 4. CUSTOM CSS EDITOR                       -->
        <!-- ========================================== -->
        <div v-if="activeTab === 'custom_css'" class="space-y-6">
          <div class="pb-4 border-b border-[#EBE9F1]">
            <h3 class="text-base font-bold text-[#4B465C] m-0">Custom CSS Code Editor</h3>
            <p class="text-xs text-[#82868B] m-0 mt-1">Inject custom CSS overrides directly into Admin and Customer portals.</p>
          </div>

          <div class="space-y-4">
            <div class="flex items-center gap-2 bg-[#F8F7FA] p-1 rounded-lg border border-[#EBE9F1] w-fit">
              <button 
                type="button" 
                class="px-3 py-1.5 rounded-md text-xs font-bold transition-all border-none cursor-pointer"
                :class="settings.custom_css_tab === 'admin' ? 'bg-white text-[#7367F0] shadow-sm' : 'text-[#82868B] hover:text-[#4B465C]'"
                @click="settings.custom_css_tab = 'admin'"
              >
                Admin Area CSS
              </button>
              <button 
                type="button" 
                class="px-3 py-1.5 rounded-md text-xs font-bold transition-all border-none cursor-pointer"
                :class="settings.custom_css_tab === 'customer' ? 'bg-white text-[#7367F0] shadow-sm' : 'text-[#82868B] hover:text-[#4B465C]'"
                @click="settings.custom_css_tab = 'customer'"
              >
                Customers Area CSS
              </button>
            </div>

            <div class="rounded-lg overflow-hidden border border-[#2F3349]">
              <div class="bg-[#2F3349] px-4 py-2 text-xs text-[#A5A2AD] font-mono flex items-center justify-between">
                <span>{{ settings.custom_css_tab === 'admin' ? 'admin-custom.css' : 'customers-custom.css' }}</span>
                <span>CSS</span>
              </div>
              <textarea 
                v-if="settings.custom_css_tab === 'admin'"
                v-model="settings.custom_css_admin"
                rows="14"
                class="w-full p-4 bg-[#1E1E2D] text-[#56CA00] font-mono text-xs focus:outline-none leading-relaxed resize-y border-none"
                placeholder="/* Add custom admin CSS rules here */"
              ></textarea>
              <textarea 
                v-else
                v-model="settings.custom_css_customer"
                rows="14"
                class="w-full p-4 bg-[#1E1E2D] text-[#56CA00] font-mono text-xs focus:outline-none leading-relaxed resize-y border-none"
                placeholder="/* Add custom customers portal CSS rules here */"
              ></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import defaultLogoUrl from '@/main/assets/logo.png';
import defaultMascotUrl from '@/main/assets/robot_hologram.png';
import { useThemeStore } from '@/main/store/themeStore';
import { applyThemeStyles } from '@/main/utils';

export default defineComponent({
  name: 'ThemeStyleView',
  setup() {
    const activeTab = ref('dynamic_theme');
    const saving = ref(false);
    const themeStore = useThemeStore();

    const tabList = [
      { key: 'dynamic_theme',   label: 'Dynamic Theme & Colors',  icon: '🎨' },
      { key: 'logo_management', label: 'Logo & Brand Identity',    icon: '🖼️' },
      { key: 'admin_menu',      label: 'Admin Menu & Navbar',     icon: '🖥️' },
      { key: 'buttons',         label: 'Buttons & Accents',        icon: '🔘' },
      { key: 'custom_css',      label: 'Custom CSS Editor',        icon: '💻' },
    ];

    const customThemeForm = reactive({
      bg: '',
      primary: '',
      primaryHover: '',
      textDark: '',
      accent: '',
      bgImage: '',
      headerImage: '',
      sidebarLogo: '',
    });

    const initDynamicTheme = () => {
      const activeObj = themeStore.customTheme || themeStore.themes[themeStore.currentTheme] || themeStore.themes['purple'] || {};
      customThemeForm.bg = activeObj.bg || '#F8F7FA';
      customThemeForm.primary = activeObj.primary || '#7367F0';
      customThemeForm.primaryHover = activeObj.primaryHover || '#685dd8';
      customThemeForm.textDark = activeObj.textDark || '#2F3349';
      customThemeForm.accent = activeObj.accent || '#00CFE8';
      customThemeForm.bgImage = activeObj.bgImage || '';
      customThemeForm.headerImage = activeObj.headerImage || '';
      customThemeForm.sidebarLogo = activeObj.sidebarLogo || '';
    };

    onMounted(() => {
      themeStore.applyTheme();
      initDynamicTheme();
    });

    const applyThemePreset = (presetKey) => {
      themeStore.setTheme(presetKey);
      const activeObj = themeStore.themes[presetKey];
      if (activeObj) {
        customThemeForm.bg = activeObj.bg || '#F8F7FA';
        customThemeForm.primary = activeObj.primary || '#7367F0';
        customThemeForm.primaryHover = activeObj.primaryHover || '#685dd8';
        customThemeForm.textDark = activeObj.textDark || '#2F3349';
        customThemeForm.accent = activeObj.accent || '#00CFE8';
      }
    };

    const saveDynamicTheme = () => {
      themeStore.saveCustomTheme({ ...customThemeForm });
      message.success('Dynamic theme, colors, and branding applied across the CRM!');
    };

    const resetDynamicThemeDefaults = () => {
      applyThemePreset('purple');
      message.info('Reset theme colors to default');
    };

    const handleDynamicFileUpload = (e, key) => {
      const file = e.target.files[0];
      if (!file) return;
      if (file.size > 50 * 1024 * 1024) {
        message.error('File size must be less than 50MB');
        return;
      }
      const reader = new FileReader();
      reader.onload = (evt) => {
        customThemeForm[key] = evt.target.result;
        themeStore.saveCustomTheme({ ...customThemeForm });
        message.success(`Uploaded ${key === 'sidebarLogo' ? 'Sidebar Logo' : 'Background image'}!`);
      };
      reader.readAsDataURL(file);
    };

    const removeDynamicFile = (key) => {
      customThemeForm[key] = '';
      themeStore.saveCustomTheme({ ...customThemeForm });
      message.info(`Cleared ${key === 'sidebarLogo' ? 'Sidebar Logo' : 'Header Background'}`);
    };

    const getAppBasePath = () => {
      return (typeof window !== 'undefined' && window.config && window.config.path) ? window.config.path : '';
    };

    const defaultFaviconUrl = getAppBasePath() ? `${getAppBasePath()}/favicon.ico` : '/favicon.ico';

    const defaultSettings = {
      company_name: 'Ibridge Digital',
      sidebar_logo_url: defaultLogoUrl,
      sidebar_logo_text: 'Ibridge Digital',
      login_logo_show: true,
      login_logo_url: defaultLogoUrl,
      login_logo_text: 'Ibridge Digital CRM',
      favicon_url: defaultFaviconUrl,
      app_page_title: 'Ibridge Digital CRM',

      admin_sidebar_bg: '#FFFFFF',
      admin_sidebar_link: '#82868B',
      admin_sidebar_active_bg: '#7367F0',
      admin_sidebar_active_link: '#FFFFFF',
      admin_header_bg: '#FFFFFF',
      admin_content_bg: '#F8F7FA',

      btn_primary: '#7367F0',
      btn_success: '#28C76F',
      btn_info: '#00CFE8',
      btn_danger: '#EA5455',

      custom_css_tab: 'admin',
      custom_css_admin: '/* Custom Admin CSS Rules */\nbody {\n  font-family: "Public Sans", sans-serif;\n}',
      custom_css_customer: '/* Custom Customers CSS Rules */'
    };

    const savedSettings = localStorage.getItem('crm_theme_style_settings');
    const settings = reactive(savedSettings ? { ...defaultSettings, ...JSON.parse(savedSettings) } : defaultSettings);

    const handleFileUpload = (e, key) => {
      const file = e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (evt) => {
        settings[key] = evt.target.result;
        message.success('Image uploaded! Click Save to persist settings.');
      };
      reader.readAsDataURL(file);
    };

    const resetSidebarLogo = () => {
      settings.sidebar_logo_url = defaultLogoUrl;
      settings.sidebar_logo_text = 'Ibridge Digital';
    };

    const resetLoginLogo = () => {
      settings.login_logo_url = defaultLogoUrl;
      settings.login_logo_text = 'Ibridge Digital CRM';
    };

    const resetFavicon = () => {
      settings.favicon_url = defaultFaviconUrl;
    };

    const saveThemeSettings = () => {
      saving.value = true;
      setTimeout(() => {
        saving.value = false;
        localStorage.setItem('crm_theme_style_settings', JSON.stringify(settings));
        applyThemeStyles(settings);
        if (typeof window !== 'undefined' && window.dispatchEvent) {
          window.dispatchEvent(new CustomEvent('crm-theme-settings-updated', { detail: settings }));
        }
        message.success('Theme styles and branding saved successfully!');
      }, 600);
    };

    const resolveFaviconUrl = (url) => {
      if (!url) return defaultFaviconUrl;
      if (url.startsWith('data:') || url.startsWith('http://') || url.startsWith('https://')) {
        return url;
      }
      const basePath = getAppBasePath();
      return basePath ? (basePath + '/' + url.replace(/^\//, '')) : url;
    };

    return {
      activeTab,
      saving,
      tabList,
      settings,
      themeStore,
      customThemeForm,
      defaultFaviconUrl,
      defaultMascotUrl,
      resolveFaviconUrl,
      applyThemePreset,
      saveDynamicTheme,
      resetDynamicThemeDefaults,
      handleDynamicFileUpload,
      removeDynamicFile,
      handleFileUpload,
      resetSidebarLogo,
      resetLoginLogo,
      resetFavicon,
      saveThemeSettings
    };
  }
});
</script>

<style scoped>
:deep(.ant-input) {
  border-radius: 6px !important;
  border-color: #DBDADE !important;
}
:deep(.ant-input:focus) {
  border-color: #7367F0 !important;
  box-shadow: 0 0 0 2px rgba(115, 103, 240, 0.2) !important;
}
</style>
