<template>
  <div :class="['crm-app-shell', `theme-template-${themeStore.template}`, `skin-${themeStore.skin}`]">
    <!-- Dynamic Theme Customizer Drawer & Floating Gear -->
    <ThemeCustomizer />

    <!-- ========================================================================= -->
    <!-- MODE 1: VUEXY MODERN ENTERPRISE TEMPLATE -->
    <!-- ========================================================================= -->
    <template v-if="themeStore.template === 'vuexy'">
      <!-- Mobile Sidebar Backdrop Overlay -->
      <div 
        v-if="!sidebarCollapsed" 
        class="crm-sidebar-backdrop" 
        @click="toggleSidebar"
      ></div>

      <!-- Vuexy Sidebar -->
      <aside :class="['vuexy-sidebar', { 'vuexy-sidebar--collapsed': sidebarCollapsed }]">
        <!-- Logo Header -->
        <div class="vuexy-sidebar__header">
          <router-link to="/admin/dashboard" class="vuexy-brand">
            <template v-if="resolvedLogoUrl && !vuexyLogoImgError">
              <img 
                :src="resolvedLogoUrl" 
                :alt="resolvedLogoText" 
                class="vuexy-brand__logo-img" 
                :style="{ 
                  maxWidth: sidebarCollapsed ? '32px' : (resolvedLogoWidth || '140px'), 
                  maxHeight: sidebarCollapsed ? '32px' : (resolvedLogoHeight || '38px') 
                }"
                @error="vuexyLogoImgError = true"
              />
            </template>
            <template v-else>
              <div class="vuexy-brand__logo">
                <!-- Vuexy Angular Diamond Logo -->
                <svg width="32" height="24" viewBox="0 0 32 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M0 0H8.00002L16 12.5532L24 0H32L16 24L0 0Z" :fill="themeStore.primaryColor" />
                  <path opacity="0.35" fill-rule="evenodd" clip-rule="evenodd" d="M8 0H16L24 12.5532L16 24L8 0Z" :fill="themeStore.primaryColor" />
                </svg>
              </div>
              <span v-if="!sidebarCollapsed" class="vuexy-brand__text">{{ resolvedLogoText }}</span>
            </template>
          </router-link>

          <!-- Sidebar Pin Toggle Circle -->
          <button class="vuexy-sidebar__toggle" @click="toggleSidebar" :title="sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'">
            <svg v-if="!sidebarCollapsed" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="9"/>
              <circle cx="12" cy="12" r="3" fill="currentColor"/>
            </svg>
            <svg v-else viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="9"/>
            </svg>
          </button>
        </div>

        <!-- Vuexy Navigation Area -->
        <div class="vuexy-sidebar__nav">
          <!-- Setup Navigation (If in Setup Mode) -->
          <template v-if="setupMode">
            <div class="vuexy-menu-header">
              <span class="vuexy-menu-header__text">SYSTEM SETUP</span>
              <div class="vuexy-menu-header__line"></div>
            </div>
            <button class="vuexy-menu-item" @click="exitSetupMode">
              <div class="vuexy-menu-item__inner">
                <span class="vuexy-menu-item__icon">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                </span>
                <span v-if="!sidebarCollapsed" class="vuexy-menu-item__label">Back to Menu</span>
              </div>
            </button>

            <template v-for="sec in setupSections" :key="sec.id">
              <button
                @click="onSetupNavClick(sec)"
                :class="['vuexy-menu-item', {
                  'vuexy-menu-item--active': setupActiveSection === sec.id && !sec.children
                }]"
              >
                <div class="vuexy-menu-item__inner">
                  <span class="vuexy-menu-item__icon" v-html="sec.icon"></span>
                  <span v-if="!sidebarCollapsed" class="vuexy-menu-item__label">{{ sec.label }}</span>
                </div>
                <svg
                  v-if="sec.children && !sidebarCollapsed"
                  class="vuexy-menu-item__chevron"
                  :class="{ 'vuexy-menu-item__chevron--open': setupExpandedGroups[sec.id] }"
                  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                >
                  <polyline points="9 18 15 12 9 6"/>
                </svg>
              </button>
              <!-- Children -->
              <div
                v-if="sec.children && setupExpandedGroups[sec.id] && !sidebarCollapsed"
                class="vuexy-submenu"
              >
                <a
                  v-for="child in sec.children"
                  :key="child.id"
                  @click="onSetupChildClick(sec.id, child.id)"
                  :class="['vuexy-submenu-item', { 'vuexy-submenu-item--active': setupActiveSubSection === child.id }]"
                >
                  <span class="vuexy-submenu-bullet"></span>
                  <span>{{ child.label }}</span>
                </a>
              </div>
            </template>
          </template>

          <!-- Standard Grouped Sections (Matching Reference Image) -->
          <template v-else>
            <template v-for="(sec, sIdx) in vuexyMenuSections" :key="sIdx">
              <!-- Category Header -->
              <div v-if="sec.header" class="vuexy-menu-header">
                <span class="vuexy-menu-header__text">{{ sec.header }}</span>
                <div class="vuexy-menu-header__line"></div>
              </div>

              <!-- Menu Items in Category -->
              <template v-for="item in sec.items" :key="item.name">
                <!-- If item triggers setup mode -->
                <button
                  v-if="item.isSetupTrigger"
                  @click.prevent="enterSetupMode"
                  :class="['vuexy-menu-item', { 'vuexy-menu-item--active': $route.path.startsWith('/admin/setup') }]"
                >
                  <div class="vuexy-menu-item__inner">
                    <span class="vuexy-menu-item__icon" v-html="item.icon"></span>
                    <span v-if="!sidebarCollapsed" class="vuexy-menu-item__label">{{ item.name }}</span>
                  </div>
                </button>

                <!-- Single Link Item -->
                <router-link
                  v-else-if="!item.children"
                  :to="item.path"
                  class="vuexy-menu-item"
                  active-class="vuexy-menu-item--active"
                >
                  <div class="vuexy-menu-item__inner">
                    <span class="vuexy-menu-item__icon" v-html="item.icon"></span>
                    <span v-if="!sidebarCollapsed" class="vuexy-menu-item__label">{{ item.name }}</span>
                  </div>
                  <span v-if="item.badge && !sidebarCollapsed" class="vuexy-menu-item__badge">{{ item.badge }}</span>
                </router-link>

                <!-- Expandable Group Item -->
                <div v-else class="vuexy-menu-group">
                  <button
                    class="vuexy-menu-item"
                    @click="toggleVuexyGroup(item.name)"
                  >
                    <div class="vuexy-menu-item__inner">
                      <span class="vuexy-menu-item__icon" v-html="item.icon"></span>
                      <span v-if="!sidebarCollapsed" class="vuexy-menu-item__label">{{ item.name }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <span v-if="item.badge && !sidebarCollapsed" class="vuexy-menu-item__badge">{{ item.badge }}</span>
                      <svg
                        v-if="!sidebarCollapsed"
                        class="vuexy-menu-item__chevron"
                        :class="{ 'vuexy-menu-item__chevron--open': vuexyExpandedGroups[item.name] }"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                      >
                        <polyline points="9 18 15 12 9 6"/>
                      </svg>
                    </div>
                  </button>

                  <!-- Submenu -->
                  <div v-show="vuexyExpandedGroups[item.name] && !sidebarCollapsed" class="vuexy-submenu">
                    <router-link
                      v-for="sub in item.children"
                      :key="sub.name"
                      :to="sub.path"
                      class="vuexy-submenu-item"
                      active-class="vuexy-submenu-item--active"
                    >
                      <span class="vuexy-submenu-bullet"></span>
                      <span>{{ sub.name }}</span>
                    </router-link>
                  </div>
                </div>
              </template>
            </template>
          </template>
        </div>
      </aside>

      <!-- Vuexy Main Wrapper -->
      <div :class="['vuexy-main-wrapper', { 'vuexy-main-wrapper--collapsed': sidebarCollapsed }]">
        <!-- Floating Navbar -->
        <div class="vuexy-navbar-container">
          <header class="vuexy-navbar">
            <div class="vuexy-navbar__left">
              <!-- Mobile Hamburger -->
              <button class="vuexy-nav-icon-btn lg:hidden" @click="toggleSidebar">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
              </button>

              <!-- Search Bar with Shortcut -->
              <div class="vuexy-search-btn">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" placeholder="Search (Ctrl+/)" class="bg-transparent border-none outline-none text-sm w-full text-slate-700" />
                <span class="vuexy-search-btn__key">⌘K</span>
              </div>

              <!-- Quick Create -->
              <a-dropdown :trigger="['click']">
                <button class="vuexy-nav-icon-btn" title="Quick Create">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
                <template #overlay>
                  <a-menu class="crm-dropdown-menu">
                    <a-menu-item key="task"><router-link to="/admin/tasks">New Task</router-link></a-menu-item>
                    <a-menu-item key="project"><router-link to="/admin/projects">New Project</router-link></a-menu-item>
                    <a-menu-item key="invoice"><router-link to="/admin/invoices">New Invoice</router-link></a-menu-item>
                    <a-menu-item key="customer"><router-link to="/admin/customers">New Customer</router-link></a-menu-item>
                    <a-menu-item key="lead"><router-link to="/admin/leads">New Lead</router-link></a-menu-item>
                  </a-menu>
                </template>
              </a-dropdown>
            </div>

            <!-- Navbar Right Items -->
            <div class="vuexy-navbar__right">
              <!-- Language Switcher -->
              <a-dropdown :trigger="['click']">
                <button class="vuexy-nav-icon-btn" title="Language">
                  <span style="font-size: 15px; font-weight: 700;">文A</span>
                </button>
                <template #overlay>
                  <a-menu>
                    <a-menu-item key="en" @click="changeLanguage('en')">English</a-menu-item>
                    <a-menu-item key="es" @click="changeLanguage('es')">Spanish</a-menu-item>
                    <a-menu-item key="fr" @click="changeLanguage('fr')">French</a-menu-item>
                    <a-menu-item key="de" @click="changeLanguage('de')">German</a-menu-item>
                  </a-menu>
                </template>
              </a-dropdown>

              <!-- Dark / Light Mode Toggle -->
              <button class="vuexy-nav-icon-btn" @click="toggleSkinMode" title="Toggle Light / Dark Mode">
                <svg v-if="themeStore.skin === 'dark'" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                <svg v-else viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
              </button>

              <!-- Shortcuts App Grid -->
              <button class="vuexy-nav-icon-btn" @click="$router.push('/admin/setup')" title="Settings & Shortcuts">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
              </button>

              <!-- Tasks Badge Button -->
              <a-dropdown :trigger="['click']" v-model:visible="todoDropdownOpen">
                <button class="vuexy-nav-icon-btn" title="Pending Tasks">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                  <span v-if="pendingTodosCount > 0" class="vuexy-nav-icon-btn__badge"></span>
                </button>
                <template #overlay>
                  <div class="tasks-popover-card" @click.stop>
                    <div class="tasks-popover-head">
                      <span class="tasks-popover-title">Pending Tasks ({{ pendingTodosList.length }})</span>
                      <button class="btn-quick-add-task" @click="showQuickAddTask = !showQuickAddTask">+ Add Task</button>
                    </div>
                    <div class="tasks-popover-body">
                      <div v-if="pendingTodosList.length === 0" class="tasks-popover-empty">All caught up!</div>
                      <div v-for="task in pendingTodosList" :key="task.id" class="task-popover-item">
                        <input type="checkbox" :checked="task.done" @change="toggleHeaderTodo(task)" />
                        <span class="task-item-desc">{{ task.description }}</span>
                      </div>
                    </div>
                    <div class="tasks-popover-footer">
                      <router-link to="/admin/my-todos" @click="todoDropdownOpen = false">View All Tasks →</router-link>
                    </div>
                  </div>
                </template>
              </a-dropdown>

              <!-- Notifications Bell -->
              <button class="vuexy-nav-icon-btn" @click="fetchHeaderNotifs" title="Notifications">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span v-if="notifUnreadCount > 0" class="vuexy-nav-icon-btn__badge"></span>
              </button>

              <!-- User Profile Avatar with Online Status Indicator -->
              <a-dropdown :trigger="['click']" placement="bottomRight">
                <div class="vuexy-avatar-wrap">
                  <img
                    v-if="user?.profile_image && !userHeaderImageError"
                    :src="getProfileImageUrl(user.profile_image)"
                    :alt="user.name"
                    class="vuexy-avatar"
                    @error="userHeaderImageError = true"
                  />
                  <img v-else :src="clayAvatarUrl" alt="Avatar" class="vuexy-avatar" />
                  <span class="vuexy-avatar-status"></span>
                </div>
                <template #overlay>
                  <a-menu class="crm-dropdown-menu prf-dropdown">
                    <div class="prf-dd-head">
                      <div class="prf-dd-avatar">
                        <img
                          v-if="user?.profile_image && !userHeaderImageError"
                          :src="getProfileImageUrl(user.profile_image)"
                          :alt="profileDisplayName"
                          class="prf-dd-avatar-img"
                          @error="userHeaderImageError = true"
                        />
                        <img v-else :src="clayAvatarUrl" alt="Avatar" class="prf-dd-avatar-img" />
                        <span class="prf-dd-online"></span>
                      </div>
                      <div class="prf-dd-meta">
                        <div class="prf-dd-name">{{ profileDisplayName }}</div>
                        <div class="prf-dd-email">{{ user?.email || 'admin@test.com' }}</div>
                      </div>
                    </div>
                    <a-menu-item key="profile">
                      <router-link to="/admin/profile" class="prf-dd-item">
                        <span class="prf-dd-ico" v-html="ddIcons.profile"></span>
                        <span>My Profile</span>
                      </router-link>
                    </a-menu-item>
                    <a-menu-item key="timesheets">
                      <router-link to="/admin/timesheets" class="prf-dd-item">
                        <span class="prf-dd-ico" v-html="ddIcons.timesheets"></span>
                        <span>My Timesheets</span>
                      </router-link>
                    </a-menu-item>
                    <a-menu-item key="edit-profile">
                      <router-link to="/admin/profile/edit" class="prf-dd-item">
                        <span class="prf-dd-ico" v-html="ddIcons.editProfile"></span>
                        <span>Edit Profile</span>
                      </router-link>
                    </a-menu-item>
                    <a-menu-divider />
                    <a-menu-item key="logout" class="prf-dd-logout" @click="handleLogout">
                      <a class="prf-dd-item">
                        <span class="prf-dd-ico" v-html="ddIcons.logout"></span>
                        <span>Logout</span>
                      </a>
                    </a-menu-item>
                  </a-menu>
                </template>
              </a-dropdown>
            </div>
          </header>
        </div>

        <!-- Vuexy Content Router View -->
        <main class="vuexy-content">
          <router-view />
        </main>

        <!-- Vuexy Footer -->
        <footer class="vuexy-footer">
          <div class="vuexy-footer__copy">
            © 2026 Made With ❤️ By <a href="https://ibridge.digital" target="_blank">iBRIDGE</a>
          </div>
          <div class="vuexy-footer__links">
            <a href="#">License</a>
            <a href="#" @click.prevent="themeStore.setTemplate('organic')">Claymorphic Theme</a>
            <a href="#">Documentation</a>
            <a href="#">Support</a>
          </div>
        </footer>

        <!-- Scroll to top floating button -->
        <button class="vuexy-scroll-top" @click="scrollToTop" title="Scroll to Top">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
        </button>
      </div>
    </template>

    <!-- ========================================================================= -->
    <!-- MODE 2: CLAYMORPHIC ORGANIC TEMPLATE (Preserved Original) -->
    <!-- ========================================================================= -->
    <template v-else>
      <!-- Organic Claymorphic Wave/Mountain Decorators -->
      <div class="organic-bg-decor wave-1"></div>
      <div class="organic-bg-decor wave-2"></div>
      <div class="organic-bg-decor wave-3"></div>
      <div class="organic-bg-decor wave-4"></div>

      <!-- Mobile Sidebar Backdrop Overlay -->
      <div 
        v-if="!sidebarCollapsed" 
        class="crm-sidebar-backdrop" 
        @click="toggleSidebar"
      ></div>

    <!-- Sidebar -->
    <aside :class="['crm-sidebar', { 'crm-sidebar--collapsed': sidebarCollapsed }]">
      <!-- Logo Header -->
      <div class="crm-sidebar__logo">
        <div class="crm-sidebar__logo-inner">
          <template v-if="resolvedLogoUrl">
            <img :src="resolvedLogoUrl" alt="iBRIDGE Logo" class="crm-logo-img" :style="{ maxWidth: resolvedLogoWidth, maxHeight: resolvedLogoHeight }" />
          </template>
          <template v-else>
            <span class="crm-logo-text-fallback" style="color: #fff; font-weight: 700; font-size: 15px; letter-spacing: 0.5px;">{{ resolvedLogoText }}</span>
          </template>
        </div>
        <button class="crm-hamburger" @click="toggleSidebar" title="Toggle Sidebar">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
        </button>
      </div>

      <!-- User Profile -->
      <div v-if="!sidebarCollapsed && !setupMode" class="crm-sidebar__profile">
        <a-dropdown :trigger="['click']" placement="bottomRight">
          <div class="crm-profile-card" style="cursor: pointer;">
            <img
              v-if="user?.profile_image && !userHeaderImageError"
              :src="getProfileImageUrl(user.profile_image)"
              :alt="user.name"
              class="crm-profile-avatar"
              @error="userHeaderImageError = true"
            />
            <div v-else class="crm-profile-avatar relative overflow-hidden flex items-center justify-center animated-avatar-wrap">
              <img :src="clayAvatarUrl" alt="User Avatar" class="w-full h-full object-cover animate-avatar-float" />
            </div>
            <div class="crm-profile-info">
              <span class="crm-profile-name">{{ profileDisplayName }}</span>
              <span class="crm-profile-email">{{ user?.email || 'admin@test.com' }}</span>
            </div>
          </div>
          <template #overlay>
            <a-menu class="crm-dropdown-menu prf-dropdown">
              <div class="prf-dd-head">
                <div class="prf-dd-avatar">
                  <img
                    v-if="user?.profile_image && !userHeaderImageError"
                    :src="getProfileImageUrl(user.profile_image)"
                    :alt="profileDisplayName"
                    class="prf-dd-avatar-img"
                    @error="userHeaderImageError = true"
                  />
                  <img v-else :src="clayAvatarUrl" alt="Avatar" class="prf-dd-avatar-img" />
                  <span class="prf-dd-online"></span>
                </div>
                <div class="prf-dd-meta">
                  <div class="prf-dd-name">{{ profileDisplayName }}</div>
                  <div class="prf-dd-email">{{ user?.email || 'admin@test.com' }}</div>
                </div>
              </div>
              <a-menu-item key="profile">
                <router-link to="/admin/profile" class="prf-dd-item">
                  <span class="prf-dd-ico" v-html="ddIcons.profile"></span>
                  <span>My Profile</span>
                </router-link>
              </a-menu-item>
              <a-menu-item key="timesheets">
                <router-link to="/admin/timesheets" class="prf-dd-item">
                  <span class="prf-dd-ico" v-html="ddIcons.timesheets"></span>
                  <span>My Timesheets</span>
                </router-link>
              </a-menu-item>
              <a-menu-item key="edit-profile">
                <router-link to="/admin/profile/edit" class="prf-dd-item">
                  <span class="prf-dd-ico" v-html="ddIcons.editProfile"></span>
                  <span>Edit Profile</span>
                </router-link>
              </a-menu-item>
              <a-menu-divider />
              <a-menu-item key="logout" class="prf-dd-logout" @click="handleLogout">
                <a class="prf-dd-item">
                  <span class="prf-dd-ico" v-html="ddIcons.logout"></span>
                  <span>Logout</span>
                </a>
              </a-menu-item>
            </a-menu>
          </template>
        </a-dropdown>
      </div>

      <!-- ========== MAIN NAVIGATION ========== -->
      <nav v-if="!setupMode" class="crm-sidebar__nav">
        <template v-for="item in filteredMenuItems" :key="item.name">
          <!-- Setup item — special click handler -->
          <a
            v-if="item.name === 'Setup' && !item.children"
            @click.prevent="enterSetupMode"
            :class="['crm-nav-item', { 'crm-nav-item--active': $route.path.startsWith('/admin/setup') }]"
          >
            <span class="crm-nav-icon">
              <span v-if="item.icon && item.icon.includes('<svg')" v-html="item.icon"></span>
              <i v-else-if="item.icon" :class="item.icon"></i>
            </span>
            <span v-if="!sidebarCollapsed" class="crm-nav-label">{{ item.name }}</span>
          </a>

          <!-- Single link item (non-Setup) -->
          <router-link
            v-else-if="!item.children"
            :to="item.path"
            class="crm-nav-item"
            active-class="crm-nav-item--active"
          >
            <span class="crm-nav-icon">
              <span v-if="item.icon && item.icon.includes('<svg')" v-html="item.icon"></span>
              <i v-else-if="item.icon" :class="item.icon"></i>
            </span>
            <span v-if="!sidebarCollapsed" class="crm-nav-label">{{ item.name }}</span>
          </router-link>

          <!-- Expandable group -->
          <div v-else class="crm-nav-group">
            <button
              :class="['crm-nav-item crm-nav-item--group', { 'crm-nav-item--expanded': expandedGroups[item.name] }]"
              @click="toggleGroup(item.name)"
            >
              <span class="crm-nav-icon">
                <span v-if="item.icon && item.icon.includes('<svg')" v-html="item.icon"></span>
                <i v-else-if="item.icon" :class="item.icon"></i>
              </span>
              <span v-if="!sidebarCollapsed" class="crm-nav-label">{{ item.name }}</span>
              <svg
                v-if="!sidebarCollapsed"
                class="crm-nav-chevron"
                :class="{ 'crm-nav-chevron--open': expandedGroups[item.name] }"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              >
                <polyline points="15,18 9,12 15,6"/>
              </svg>
            </button>
            <!-- Sub menu -->
            <div
              v-show="expandedGroups[item.name] && !sidebarCollapsed"
              class="crm-submenu"
            >
              <router-link
                v-for="sub in item.children"
                :key="sub.name"
                :to="sub.path"
                class="crm-submenu-item"
                active-class="crm-submenu-item--active"
              >
                {{ sub.name }}
              </router-link>
            </div>
          </div>
        </template>
      </nav>

      <!-- ========== SETUP NAVIGATION ========== -->
      <nav v-if="setupMode" class="crm-sidebar__nav crm-sidebar__nav--setup">
        <!-- Back to main menu -->
        <a class="crm-nav-item crm-nav-back" @click="exitSetupMode">
          <span class="crm-nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="19" y1="12" x2="5" y2="12"/>
              <polyline points="12 19 5 12 12 5"/>
            </svg>
          </span>
          <span v-if="!sidebarCollapsed" class="crm-nav-label">Back to Menu</span>
        </a>

        <div v-if="!sidebarCollapsed" class="crm-setup-divider"></div>

        <!-- Setup section label -->
        <div v-if="!sidebarCollapsed" class="crm-setup-heading">Setup</div>

        <!-- Setup navigation items -->
        <template v-for="sec in setupSections" :key="sec.id">
          <!-- Section with children -->
          <a
            @click="onSetupNavClick(sec)"
            :class="['crm-nav-item', {
              'crm-nav-item--active': setupActiveSection === sec.id && !sec.children
            }]"
          >
            <span class="crm-nav-icon" v-html="sec.icon"></span>
            <span v-if="!sidebarCollapsed" class="crm-nav-label">{{ sec.label }}</span>
            <svg
              v-if="sec.children && !sidebarCollapsed"
              class="crm-nav-chevron"
              :class="{ 'crm-nav-chevron--open': setupExpandedGroups[sec.id] }"
              viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            >
              <polyline points="15,18 9,12 15,6"/>
            </svg>
          </a>
          <!-- Children -->
          <div
            v-if="sec.children && setupExpandedGroups[sec.id] && !sidebarCollapsed"
            class="crm-submenu"
          >
            <a
              v-for="child in sec.children"
              :key="child.id"
              @click="onSetupChildClick(sec.id, child.id)"
              :class="['crm-submenu-item', { 'crm-submenu-item--active': setupActiveSubSection === child.id }]"
            >
              {{ child.label }}
            </a>
          </div>
        </template>
      </nav>

      <!-- Pinned Project Footer -->
      <div v-if="!sidebarCollapsed && !setupMode" class="crm-sidebar__footer">
        <div class="crm-pinned-project cursor-pointer" @click="goToSeo">
          <span class="crm-pinned-name">SEO Optimization</span>
          <span class="crm-pinned-client">Kub Group</span>
          <div class="crm-pinned-bar">
            <div class="crm-pinned-bar__fill" style="width:75%"></div>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="crm-main">
      <!-- Top Header -->
      <header class="crm-header">
        <div class="crm-header__left">
          <!-- Mobile hamburger -->
          <button class="crm-hamburger crm-hamburger--mobile" @click="toggleSidebar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="3" y1="6" x2="21" y2="6"/>
              <line x1="3" y1="12" x2="21" y2="12"/>
              <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
          </button>

          <!-- Search Bar -->
          <div class="crm-search">
            <svg class="crm-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/>
              <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" placeholder="Search..." class="crm-search__input" />
          </div>

          <!-- Quick Create -->
          <a-dropdown :trigger="['click']">
            <button class="crm-quick-create" title="Quick Create">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
              </svg>
            </button>
            <template #overlay>
              <a-menu class="crm-dropdown-menu">
                <a-menu-item key="task"><router-link to="/admin/tasks">New Task</router-link></a-menu-item>
                <a-menu-item key="project"><router-link to="/admin/projects">New Project</router-link></a-menu-item>
                <a-menu-item key="invoice"><router-link to="/admin/invoices">New Invoice</router-link></a-menu-item>
                <a-menu-item key="customer"><router-link to="/admin/customers">New Customer</router-link></a-menu-item>
                <a-menu-item key="lead"><router-link to="/admin/leads">New Lead</router-link></a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
        </div>

        <div class="crm-header__right">
          <router-link to="/admin/customers" class="crm-header-link">Customers area</router-link>
          <router-link :to="{ name: 'admin.setup', params: { section: 'settings' } }" class="crm-header-link">Settings</router-link>
          <router-link :to="{ name: 'admin.feature-updates' }" class="crm-header-link crm-header-link--new">What's New</router-link>

          <div class="crm-header-actions">
            <!-- Dynamic Theme Selector & Customizer Studio -->
            <div class="theme-selector-container">
              <button 
                v-for="(t, name) in themeStore.themes" 
                :key="name"
                :class="['theme-orb', name, { active: themeStore.currentTheme === name }]"
                @click="themeStore.setTheme(name)"
                :title="'Switch to ' + name + ' theme'"
              ></button>
              
              <button
                type="button"
                class="theme-customizer-btn"
                :class="{ active: themeStore.currentTheme === 'custom' }"
                @click="$router.push('/admin/setup/theme-style')"
                title="Admin Custom Colors & Theme Studio Page"
              >
                🎨
              </button>
            </div>

            <!-- Share icon -->
            <button class="crm-action-btn" title="Share">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
              </svg>
            </button>

            <!-- Tasks badge → Pending Tasks Popover Dropdown -->
            <a-dropdown :trigger="['click']" v-model:visible="todoDropdownOpen">
              <button class="crm-action-btn" title="My To-Do Tasks">
                <a-badge :count="pendingTodosCount" :offset="[6, -2]" color="#0d6efd">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                  </svg>
                </a-badge>
              </button>
              <template #overlay>
                <div class="tasks-popover-card" @click.stop>
                  <!-- Header -->
                  <div class="tasks-popover-head">
                    <div class="flex items-center gap-2">
                      <svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" width="16" height="16"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                      <span class="tasks-popover-title">Pending Tasks ({{ pendingTodosList.length }})</span>
                    </div>
                    <button class="btn-quick-add-task" @click="showQuickAddTask = !showQuickAddTask">
                      + Add Task
                    </button>
                  </div>

                  <!-- Quick Add Task Bar -->
                  <div v-if="showQuickAddTask" class="quick-add-task-row">
                    <input
                      v-model="quickTaskText"
                      placeholder="Enter new task..."
                      class="quick-task-input"
                      @keyup.enter="saveQuickHeaderTask"
                    />
                    <button class="btn-quick-save" @click="saveQuickHeaderTask">Save</button>
                  </div>

                  <!-- Tasks List -->
                  <div class="tasks-popover-body">
                    <div v-if="pendingTodosList.length === 0" class="tasks-popover-empty">
                      <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" width="24" height="24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                      <p class="empty-text">All caught up! No pending tasks.</p>
                    </div>

                    <div
                      v-for="task in pendingTodosList"
                      :key="task.id"
                      class="task-popover-item"
                    >
                      <div class="task-item-main">
                        <input
                          type="checkbox"
                          :checked="task.done"
                          class="task-item-checkbox"
                          @change="toggleHeaderTodo(task)"
                        />
                        <div class="task-item-content">
                          <input
                            v-if="editingHeaderTaskId === task.id"
                            v-model="editingHeaderTaskText"
                            class="task-item-edit-input"
                            @keyup.enter="saveHeaderTaskEdit(task)"
                            @keyup.esc="cancelHeaderTaskEdit"
                          />
                          <p v-else class="task-item-desc">{{ task.description }}</p>

                          <div class="task-item-meta-row">
                            <span class="task-date-tag">{{ task.date || 'Today' }}</span>
                            <div class="task-assignee-wrap">
                              <span class="assignee-lbl">Assignee:</span>
                              <select
                                :value="task.assigned_to || user?.id"
                                class="task-assignee-select"
                                @change="assignHeaderTodo(task, $event.target.value)"
                              >
                                <option :value="user?.id">Me ({{ user?.name || 'Admin' }})</option>
                                <option v-for="s in staffMembersList" :key="s.id" :value="s.id">
                                  {{ s.name || s.full_name || s.email }}
                                </option>
                              </select>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="task-item-actions">
                        <button
                          v-if="editingHeaderTaskId !== task.id"
                          class="task-icon-act edit"
                          title="Edit Task"
                          @click="startHeaderTaskEdit(task)"
                        >
                          ✏️
                        </button>
                        <button
                          v-else
                          class="task-icon-act save"
                          title="Save Edit"
                          @click="saveHeaderTaskEdit(task)"
                        >
                          ✓
                        </button>
                        <button
                          class="task-icon-act delete"
                          title="Delete Task"
                          @click="deleteHeaderTodo(task)"
                        >
                          🗑️
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Footer -->
                  <div class="tasks-popover-footer">
                    <router-link to="/admin/my-todos" class="tasks-view-all" @click="todoDropdownOpen = false">
                      View All Tasks Studio →
                    </router-link>
                  </div>
                </div>
              </template>
            </a-dropdown>

            <!-- Timelog Dropdown -->
            <a-dropdown :trigger="['click']" v-model:visible="timerDropdownOpen">
              <button class="crm-action-btn" title="Time Logs" :class="{ 'crm-timer-active': headerTimer.running }">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"/>
                  <polyline points="12 6 12 12 16 14"/>
                </svg>
              </button>
              <template #overlay>
                <div class="header-timer-card" @click.stop>
                  <div v-if="!headerTimer.running" class="timer-card-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" width="28" height="28"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span class="timer-empty-text">No started timers found</span>
                    <button class="btn-primary timer-start-btn" @click="startHeaderTimer">Start Timer</button>
                  </div>
                  <div v-else class="timer-card-running">
                    <div class="timer-card-row">
                      <span class="timer-running-dot"></span>
                      <span class="timer-running-label">Started at {{ headerTimerStartStr }}</span>
                    </div>
                    <div class="timer-card-row">
                      <span class="timer-label">Total logged time:</span>
                      <span class="timer-value">{{ formatHeaderDuration(headerTimer.seconds) }}</span>
                    </div>
                    <button class="btn-stop-timer" @click="stopHeaderTimer">Stop Timer</button>
                  </div>
                  <div class="timer-card-footer">
                    <router-link to="/admin/timesheets" class="view-all-link" @click="timerDropdownOpen = false">View all timesheets</router-link>
                  </div>
                </div>
              </template>
            </a-dropdown>

            <!-- Notifications Dropdown -->
            <a-dropdown :trigger="['click']" v-model:visible="notifDropdownOpen">
              <a-badge :count="notifUnreadCount" :offset="[6, -2]" color="#e11d48">
                <button class="crm-action-btn" title="Notifications" @click="fetchHeaderNotifs">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 01-3.46 0"/>
                  </svg>
                </button>
              </a-badge>
              <template #overlay>
                <div class="notif-card" @click.stop>
                  <div class="notif-card-head">
                    <div class="notif-card-title-group">
                      <span class="notif-card-title">Notifications</span>
                      <span class="notif-count-badge" v-if="notifUnreadCount > 0">{{ notifUnreadCount }} unread</span>
                    </div>
                    <button class="notif-mark-read" @click="markNotifsRead" v-if="headerNotifs.length > 0">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><polyline points="20 6 9 17 4 12"/></svg>
                      Mark all as read
                    </button>
                  </div>

                  <div v-if="headerNotifs.length === 0" class="notif-empty-box">
                    <div class="notif-empty-icon">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="28" height="28"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                    </div>
                    <p class="notif-empty-title">All caught up!</p>
                    <p class="notif-empty-sub">No new notifications at this time.</p>
                  </div>

                  <div v-else class="notif-list-scroll">
                    <div
                      v-for="item in headerNotifs.slice(0, 10)"
                      :key="item.id"
                      class="notif-item"
                      :class="{ 'notif-item--unread': !item.read }"
                      @click="markNotifItemRead(item)"
                    >
                      <!-- Icon -->
                      <div class="notif-icon-box" :class="parseNotification(item).category">
                        <svg v-if="parseNotification(item).category === 'security'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <svg v-else-if="parseNotification(item).category === 'lead'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                        <svg v-else-if="parseNotification(item).category === 'task'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                        <svg v-else-if="parseNotification(item).category === 'contract'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <svg v-else-if="parseNotification(item).category === 'invoice'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        <svg v-else-if="parseNotification(item).category === 'project'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                      </div>

                      <!-- Body -->
                      <div class="notif-body">
                        <div class="notif-header-line">
                          <span class="notif-title-text">{{ parseNotification(item).title }}</span>
                          <span class="notif-time-text">{{ item.time }}</span>
                        </div>

                        <!-- Summary Chips for Permission Changes -->
                        <div class="notif-chips-row" v-if="parseNotification(item).category === 'security'">
                          <span class="notif-chip role" v-if="parseNotification(item).role">
                            Role: {{ parseNotification(item).role }}
                          </span>
                          <span class="notif-chip granted" v-if="parseNotification(item).granted.length">
                            + {{ parseNotification(item).granted.length }} Granted
                          </span>
                          <span class="notif-chip revoked" v-if="parseNotification(item).revoked.length">
                            - {{ parseNotification(item).revoked.length }} Revoked
                          </span>
                        </div>

                        <!-- Expand/Collapse Details Toggle -->
                        <button
                          v-if="parseNotification(item).rawDetails"
                          class="notif-toggle-btn"
                          @click.stop="toggleNotifDetails(item.id)"
                        >
                          <span>{{ isNotifExpanded(item.id) ? 'Hide details' : 'Show details' }}</span>
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="10" height="10" :style="{ transform: isNotifExpanded(item.id) ? 'rotate(180deg)' : 'none', transition: 'transform 0.15s' }">
                            <polyline points="6 9 12 15 18 9"/>
                          </svg>
                        </button>

                        <!-- Expanded Details Box -->
                        <div v-if="isNotifExpanded(item.id) && parseNotification(item).rawDetails" class="notif-details-box" @click.stop>
                          <div class="notif-detail-group" v-if="parseNotification(item).role">
                            <span class="notif-detail-lbl role">New Role</span>
                            <span class="notif-detail-val">{{ parseNotification(item).role }}</span>
                          </div>
                          <div class="notif-detail-group" v-if="parseNotification(item).granted.length">
                            <span class="notif-detail-lbl granted">Granted Permissions</span>
                            <span class="notif-detail-val">{{ parseNotification(item).granted.join(', ') }}</span>
                          </div>
                          <div class="notif-detail-group" v-if="parseNotification(item).revoked.length">
                            <span class="notif-detail-lbl revoked">Revoked Permissions</span>
                            <span class="notif-detail-val">{{ parseNotification(item).revoked.join(', ') }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="notif-footer">
                    <router-link to="/admin/notifications" class="view-all-link" @click="notifDropdownOpen = false">
                      <span>View all notifications</span>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </router-link>
                  </div>
                </div>
              </template>
            </a-dropdown>

            <!-- Avatar / Logout -->
            <a-dropdown :trigger="['click']">
              <img
                v-if="user?.profile_image && !userHeaderImageError"
                :src="getProfileImageUrl(user.profile_image)"
                :alt="user.name"
                class="crm-header-avatar"
                @error="userHeaderImageError = true"
              />
              <div v-else class="crm-header-avatar relative overflow-hidden flex items-center justify-center animated-avatar-wrap">
                <img :src="clayAvatarUrl" alt="Profile" class="w-full h-full object-cover animate-avatar-float" />
              </div>
              <template #overlay>
                <a-menu class="crm-dropdown-menu prf-dropdown">
                  <div class="prf-dd-head">
                    <div class="prf-dd-avatar">
                      <img
                        v-if="user?.profile_image && !userHeaderImageError"
                        :src="getProfileImageUrl(user.profile_image)"
                        :alt="profileDisplayName"
                        class="prf-dd-avatar-img"
                        @error="userHeaderImageError = true"
                      />
                      <img v-else :src="clayAvatarUrl" alt="Avatar" class="prf-dd-avatar-img" />
                      <span class="prf-dd-online"></span>
                    </div>
                    <div class="prf-dd-meta">
                      <div class="prf-dd-name">{{ profileDisplayName }}</div>
                      <div class="prf-dd-email">{{ user?.email || 'admin@test.com' }}</div>
                    </div>
                  </div>
                  <a-menu-item key="profile">
                    <router-link to="/admin/profile" class="prf-dd-item">
                      <span class="prf-dd-ico" v-html="ddIcons.profile"></span>
                      <span>My Profile</span>
                    </router-link>
                  </a-menu-item>
                  <a-menu-item key="timesheets">
                    <router-link to="/admin/timesheets" class="prf-dd-item">
                      <span class="prf-dd-ico" v-html="ddIcons.timesheets"></span>
                      <span>My Timesheets</span>
                    </router-link>
                  </a-menu-item>
                  <a-menu-item key="edit-profile">
                    <router-link to="/admin/profile/edit" class="prf-dd-item">
                      <span class="prf-dd-ico" v-html="ddIcons.editProfile"></span>
                      <span>Edit Profile</span>
                    </router-link>
                  </a-menu-item>
                  <a-sub-menu key="language" title="Language">
                    <a-menu-item key="lang-en" @click="changeLanguage('en')">English</a-menu-item>
                    <a-menu-item key="lang-es" @click="changeLanguage('es')">Spanish</a-menu-item>
                    <a-menu-item key="lang-fr" @click="changeLanguage('fr')">French</a-menu-item>
                    <a-menu-item key="lang-de" @click="changeLanguage('de')">German</a-menu-item>
                  </a-sub-menu>
                  <a-menu-divider />
                  <a-menu-item key="logout" class="prf-dd-logout" @click="handleLogout">
                    <a class="prf-dd-item">
                      <span class="prf-dd-ico" v-html="ddIcons.logout"></span>
                      <span>Logout</span>
                    </a>
                  </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </div>
        </div>
      </header>



      <!-- Page Content -->
      <main class="crm-page-content">
        <router-view />
      </main>

    </div>
    </template>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed, watch, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../store/authStore';
import { useThemeStore } from '../store/themeStore';
import { useModuleStore } from '../store/moduleStore';
import { message } from 'ant-design-vue';
import axios from 'axios';
import logoUrl from '../assets/logo.png';
import clayAvatarUrl from '../assets/clay_avatar.png';
import ThemeCustomizer from '../components/ThemeCustomizer.vue';

// ── Icon SVG strings ────────────────────────────────────────────────
const icons = {
  dashboard: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>`,
  customers: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>`,
  sales: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>`,
  subscriptions: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 002 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>`,
  expenses: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>`,
  contracts: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>`,
  projects: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>`,
  tasks: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>`,
  support: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
  leads: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>`,
  estimateRequest: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>`,
  knowledgeBase: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>`,
  utilities: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 010 14.14M4.93 4.93a10 10 0 000 14.14"/></svg>`,
  reports: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>`,
  staff: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>`,
  setup: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>`,
};

// ── Profile dropdown icon SVGs ──────────────────────────────────────
const ddIcons = {
  profile: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`,
  timesheets: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>`,
  editProfile: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`,
  logout: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>`,
};

// ── Setup section icon SVGs ─────────────────────────────────────────
const setupIcons = {
  users:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>`,
  customer:  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`,
  support:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
  leads:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>`,
  finance:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>`,
  contracts: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>`,
  estimate:  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>`,
  modules:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>`,
  email:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>`,
  fields:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>`,
  shield:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>`,
  roles:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="23 11 21 13 19 11"/></svg>`,
  menu:      `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>`,
  theme:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>`,
  settings:  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>`,
  help:      `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>`,
};

const resolveMenuIcon = (rawIcon) => {
  if (!rawIcon) return icons.modules;
  if (icons[rawIcon]) return icons[rawIcon];
  if (typeof rawIcon === 'string' && rawIcon.trim().startsWith('<svg')) return rawIcon;
  if (typeof rawIcon === 'string' && (rawIcon.includes('fa ') || rawIcon.includes('fa-') || rawIcon.startsWith('fa'))) {
    return `<i class="${rawIcon}" style="font-size: 16px; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center;"></i>`;
  }
  const iconMap = {
    'store': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>`,
    'payroll': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M7 15h0M2 10h20"/></svg>`,
    'hr': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/></svg>`,
    'users': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>`,
    'money': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>`,
    'book': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>`,
    'cart': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>`,
  };
  const lower = typeof rawIcon === 'string' ? rawIcon.toLowerCase().trim() : '';
  if (iconMap[lower]) return iconMap[lower];

  return icons.modules;
};

export default defineComponent({
  name: 'AdminLayout',
  components: {
    ThemeCustomizer,
  },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const authStore = useAuthStore();
    const themeStore = useThemeStore();
    const moduleStore = useModuleStore();
    const sidebarCollapsed = ref(false);
    const setupMode = ref(false);

    onMounted(() => {
      themeStore.applyAllStyles();
      moduleStore.fetchActiveMenus();
      if (window.innerWidth <= 768) {
        sidebarCollapsed.value = true;
      }
    });

    const expandedGroups = reactive({
      Sales: false,
      Utilities: false,
      Reports: false,
    });

    const vuexyExpandedGroups = reactive({
      Dashboards: true,
      Sales: false,
      Utilities: false,
      Reports: false,
    });

    const toggleVuexyGroup = (groupName) => {
      vuexyExpandedGroups[groupName] = !vuexyExpandedGroups[groupName];
    };

    const toggleSkinMode = () => {
      const nextSkin = themeStore.skin === 'dark' ? 'light' : 'dark';
      themeStore.setSkin(nextSkin);
    };

    const scrollToTop = () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const user = computed(() => authStore.user);

    const profileDisplayName = computed(() => {
      const uName = user.value?.name;
      if (uName && uName !== 'Ibridge Digital' && uName !== 'iBRIDGE') {
        return uName;
      }
      if (user.value?.firstname || user.value?.lastname) {
        return `${user.value.firstname || ''} ${user.value.lastname || ''}`.trim();
      }
      return 'Armando Turcotte';
    });

    // ── Main menu items ─────────────────────────────────────────────
    const menuItems = [
      { name: 'Dashboard',        path: '/admin/dashboard',      icon: icons.dashboard },
      { name: 'Customers',        path: '/admin/customers',       icon: icons.customers },
      {
        name: 'Sales', icon: icons.sales,
        children: [
          { name: 'Proposals',    path: '/admin/proposals' },
          { name: 'Estimates',    path: '/admin/estimates' },
          { name: 'Invoices',     path: '/admin/invoices' },
          { name: 'Payments',     path: '/admin/payments' },
          { name: 'Credit Notes', path: '/admin/credit-notes' },
          { name: 'Items',        path: '/admin/items' },
        ],
      },
      { name: 'Subscriptions',    path: '/admin/subscriptions',   icon: icons.subscriptions },
      { name: 'Expenses',         path: '/admin/expenses',        icon: icons.expenses },
      { name: 'Contracts',        path: '/admin/contracts',       icon: icons.contracts },
      { name: 'Projects',         path: '/admin/projects',        icon: icons.projects },
      { name: 'Tasks',            path: '/admin/tasks',           icon: icons.tasks },
      { name: 'Support',          path: '/admin/support',         icon: icons.support },
      { name: 'Leads',            path: '/admin/leads',           icon: icons.leads },
      { name: 'Estimate Request', path: '/admin/estimate-request',icon: icons.estimateRequest },
      { name: 'Knowledge Base',   path: '/admin/knowledge-base',  icon: icons.knowledgeBase },
      { name: 'Data Tables',      path: '/admin/tables',          icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/></svg>` },

      {
        name: 'Utilities', icon: icons.utilities,
        children: [
          { name: 'Media',              path: '/admin/media' },
          { name: 'Bulk PDF Export',    path: '/admin/utilities/bulk-pdf-export' },
          { name: 'e-Invoice Export',   path: '/admin/utilities/e-invoice-export' },
          { name: 'CSV Export',         path: '/admin/utilities/csv-export' },
          { name: 'Calendar',           path: '/admin/calendar' },
          { name: 'Announcements',      path: '/admin/announcements' },
          { name: 'Goals',              path: '/admin/goals' },
          { name: 'Activity Log',       path: '/admin/activity' },
          { name: 'Surveys',            path: '/admin/utilities/surveys' },
          { name: 'Database Backup',    path: '/admin/utilities/database-backup' },
          { name: 'Ticket Pipe Log',    path: '/admin/utilities/ticket-pipe-log' },
        ],
      },
      {
        name: 'Reports', icon: icons.reports,
        children: [
          { name: 'Sales',              path: '/admin/reports/sales' },
          { name: 'Expenses',           path: '/admin/reports/expenses' },
          { name: 'Expenses vs Income', path: '/admin/reports/finance' },
          { name: 'Leads',              path: '/admin/reports/leads' },
          { name: 'Timesheets overview', path: '/admin/reports/timesheets' },
          { name: 'KB Articles',        path: '/admin/reports/kb-articles' },
        ],
      },
      { name: 'Setup', path: '/admin/setup', icon: icons.setup, permission: 'Settings' },
    ];

    const allMenuItems = computed(() => {
      const items = [...menuItems];
      if (moduleStore.menus && moduleStore.menus.length > 0) {
        const dynamicItems = moduleStore.menus.map(m => {
          // Resolve icon name through the icons map, or use the raw string (SVG / FA class)
          const resolvedIcon = m.icon
            ? (icons[m.icon] ?? m.icon)
            : icons.modules;
          return {
            name: m.name,
            icon: resolvedIcon,
            path: m.path,
            children: m.children ? m.children.map(c => ({ name: c.name, path: c.path })) : null,
          };
        });
        items.push(...dynamicItems);
      }
      return items;
    });

    const vuexyMenuSections = computed(() => {
      const sections = [
        {
          header: null,
          items: [
            {
              name: 'Dashboard',
              path: '/admin/dashboard',
              icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="18" height="18"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>`,
            }
          ]
        },
        {
          header: 'CRM & SALES',
          items: [
            { name: 'Customers', path: '/admin/customers', icon: icons.customers, permission: 'Customers' },
            { name: 'Leads', path: '/admin/leads', icon: icons.leads, permission: 'Leads' },
            {
              name: 'Sales',
              icon: icons.sales,
              permission: 'Invoices',
              children: [
                { name: 'Proposals', path: '/admin/proposals' },
                { name: 'Estimates', path: '/admin/estimates' },
                { name: 'Invoices', path: '/admin/invoices' },
                { name: 'Payments', path: '/admin/payments' },
                { name: 'Credit Notes', path: '/admin/credit-notes' },
                { name: 'Items Catalog', path: '/admin/items' },
              ]
            },
            { name: 'Subscriptions', path: '/admin/subscriptions', icon: icons.subscriptions, permission: 'Subscriptions' },
            { name: 'Expenses', path: '/admin/expenses', icon: icons.expenses, permission: 'Expenses' },
            { name: 'Contracts', path: '/admin/contracts', icon: icons.contracts, permission: 'Contracts' },
          ]
        },
        {
          header: 'APPS & PAGES',
          items: [
            { name: 'Projects', path: '/admin/projects', icon: icons.projects, permission: 'Projects' },
            { name: 'Tasks', path: '/admin/tasks', icon: icons.tasks, permission: 'Tasks' },
            { name: 'Data Tables', path: '/admin/tables', icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="18" height="18"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/></svg>` },
            { name: 'Calendar', path: '/admin/calendar', icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="18" height="18"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>` },
            { name: 'Media Library', path: '/admin/media', icon: icons.utilities },
            { name: 'Announcements', path: '/admin/announcements', icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="18" height="18"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>` },
            {
              name: 'Utilities',
              icon: icons.utilities,
              children: [
                { name: 'Goals', path: '/admin/goals' },
                { name: 'Activity Log', path: '/admin/activity' },
                { name: 'Surveys', path: '/admin/utilities/surveys' },
                { name: 'Bulk PDF Export', path: '/admin/utilities/bulk-pdf-export' },
                { name: 'e-Invoice Export', path: '/admin/utilities/e-invoice-export' },
                { name: 'CSV Export', path: '/admin/utilities/csv-export' },
                { name: 'Database Backup', path: '/admin/utilities/database-backup' },
                { name: 'Ticket Pipe Log', path: '/admin/utilities/ticket-pipe-log' },
              ]
            }
          ]
        },
        {
          header: 'SUPPORT & TICKETS',
          items: [
            { name: 'Tickets', path: '/admin/support', icon: icons.support, permission: 'Support' },
            { name: 'Knowledge Base', path: '/admin/knowledge-base', icon: icons.knowledgeBase, permission: 'Knowledge Base' },
            { name: 'Estimate Requests', path: '/admin/estimate-request', icon: icons.estimateRequest },
          ]
        },
        {
          header: 'SYSTEM & SETTINGS',
          items: [
            { name: 'Staff Members', path: '/admin/staff', icon: icons.staff, permission: 'Staff' },
            {
              name: 'Reports',
              icon: icons.reports,
              permission: 'Reports',
              children: [
                { name: 'Sales Reports', path: '/admin/reports/sales' },
                { name: 'Expenses Reports', path: '/admin/reports/expenses' },
                { name: 'Income vs Expense', path: '/admin/reports/finance' },
                { name: 'Leads Reports', path: '/admin/reports/leads' },
                { name: 'Timesheets Report', path: '/admin/reports/timesheets' },
                { name: 'KB Articles', path: '/admin/reports/kb-articles' },
              ]
            },
            { name: 'Setup', path: '/admin/setup', icon: icons.setup, permission: 'Settings', isSetupTrigger: true },
          ]
        }
      ];

      // Add active dynamic modules if any
      if (moduleStore.menus && moduleStore.menus.length > 0) {
        sections.push({
          header: 'CUSTOM MODULES',
          items: moduleStore.menus.map(m => ({
            name: m.name,
            icon: resolveMenuIcon(m.icon),
            path: m.path,
            children: m.children ? m.children.map(c => ({ name: c.name, path: c.path })) : null,
          }))
        });
      }

      return sections;
    });


    // ── Setup sidebar sections (moved from Setup.vue) ───────────────
    const setupSections = [
      { id: 'staff',            label: 'Staff',            icon: setupIcons.users },
      { id: 'customers',        label: 'Customers',        icon: setupIcons.customer,
        children: [
          { id: 'customers-groups',  label: 'Groups' },
        ]
      },
      { id: 'support',          label: 'Support',          icon: setupIcons.support,
        children: [
          { id: 'support-departments',        label: 'Departments' },
          { id: 'support-predefined-replies',  label: 'Predefined Replies' },
          { id: 'support-ticket-priority',     label: 'Ticket Priority' },
          { id: 'support-ticket-statuses',     label: 'Ticket Statuses' },
          { id: 'support-services',            label: 'Services' },
          { id: 'support-spam-filters',        label: 'Spam Filters' },
        ]
      },
      { id: 'leads',            label: 'Leads',            icon: setupIcons.leads,
        children: [
          { id: 'leads-sources',           label: 'Sources' },
          { id: 'leads-statuses',          label: 'Statuses' },
          { id: 'leads-email-integration', label: 'Email Integration' },
          { id: 'leads-web-to-lead',       label: 'Web to Lead' },
        ]
      },
      { id: 'finance',          label: 'Finance',          icon: setupIcons.finance,
        children: [
          { id: 'finance-tax-rates',          label: 'Tax Rates' },
          { id: 'finance-currencies',         label: 'Currencies' },
          { id: 'finance-payment-modes',      label: 'Payment Modes' },
          { id: 'finance-expenses-categories',label: 'Expenses Categories' },
        ]
      },
      { id: 'contracts',        label: 'Contracts',        icon: setupIcons.contracts,
        children: [
          { id: 'contracts-types', label: 'Contract Types' },
        ]
      },
      { id: 'estimate-request', label: 'Estimate Request', icon: setupIcons.estimate,
        children: [
          { id: 'estimate-request-forms',    label: 'Forms' },
          { id: 'estimate-request-statuses', label: 'Statuses' },
        ]
      },
      { id: 'modules',          label: 'Modules',          icon: setupIcons.modules },
      { id: 'email-templates',  label: 'Email Templates',  icon: setupIcons.email },
      { id: 'custom-fields',    label: 'Custom Fields',    icon: setupIcons.fields },
      { id: 'gdpr',             label: 'GDPR',             icon: setupIcons.shield },
      { id: 'roles',            label: 'Roles',            icon: setupIcons.roles },
      { id: 'menu-setup',       label: 'Menu Setup',       icon: setupIcons.menu,
        children: [
          { id: 'menu-setup-main',  label: 'Main Menu' },
          { id: 'menu-setup-setup', label: 'Setup Menu' },
        ]
      },
      { id: 'theme-style',      label: 'Theme Style',      icon: setupIcons.theme },
      { id: 'settings',         label: 'Settings',         icon: setupIcons.settings },
      { id: 'help',             label: 'Help',             icon: setupIcons.help },
    ];

    const setupExpandedGroups = reactive({});
    const setupActiveSection = ref('staff');
    const setupActiveSubSection = ref('');

    // ── Setup URL <-> section mapping (same as Setup.vue) ───────────
    const mapUrlToSetupSection = (section) => {
      if (!section) return { sec: 'staff', sub: '' };
      const mappings = {
        'staff': { sec: 'staff', sub: '' },
        'modules': { sec: 'modules', sub: '' },
        'email-templates': { sec: 'email-templates', sub: '' },
        'custom-fields': { sec: 'custom-fields', sub: '' },
        'gdpr': { sec: 'gdpr', sub: '' },
        'roles': { sec: 'roles', sub: '' },
        'theme-style': { sec: 'theme-style', sub: '' },
        'settings': { sec: 'settings', sub: '' },
        'help': { sec: 'help', sub: '' },
        'groups': { sec: 'customers', sub: 'customers-groups' },
        'departments': { sec: 'support', sub: 'support-departments' },
        'predefined-replies': { sec: 'support', sub: 'support-predefined-replies' },
        'ticket-priority': { sec: 'support', sub: 'support-ticket-priority' },
        'ticket-statuses': { sec: 'support', sub: 'support-ticket-statuses' },
        'services': { sec: 'support', sub: 'support-services' },
        'spam-filters': { sec: 'support', sub: 'support-spam-filters' },
        'sources': { sec: 'leads', sub: 'leads-sources' },
        'statuses': { sec: 'leads', sub: 'leads-statuses' },
        'email-integration': { sec: 'leads', sub: 'leads-email-integration' },
        'web-to-lead': { sec: 'leads', sub: 'leads-web-to-lead' },
        'tax-rates': { sec: 'finance', sub: 'finance-tax-rates' },
        'currencies': { sec: 'finance', sub: 'finance-currencies' },
        'payment-modes': { sec: 'finance', sub: 'finance-payment-modes' },
        'expenses-categories': { sec: 'finance', sub: 'finance-expenses-categories' },
        'contract-types': { sec: 'contracts', sub: 'contracts-types' },
        'forms': { sec: 'estimate-request', sub: 'estimate-request-forms' },
        'estimate-statuses': { sec: 'estimate-request', sub: 'estimate-request-statuses' },
        'main-menu': { sec: 'menu-setup', sub: 'menu-setup-main' },
        'setup-menu': { sec: 'menu-setup', sub: 'menu-setup-setup' },
      };
      return mappings[section] || { sec: 'staff', sub: '' };
    };

    const mapSetupSectionToUrl = (secId, subId) => {
      const reverseMap = {
        'staff': 'staff', 'modules': 'modules', 'email-templates': 'email-templates',
        'custom-fields': 'custom-fields', 'gdpr': 'gdpr', 'roles': 'roles',
        'theme-style': 'theme-style', 'settings': 'settings', 'help': 'help',
        'customers-groups': 'groups',
        'support-departments': 'departments', 'support-predefined-replies': 'predefined-replies',
        'support-ticket-priority': 'ticket-priority', 'support-ticket-statuses': 'ticket-statuses',
        'support-services': 'services', 'support-spam-filters': 'spam-filters',
        'leads-sources': 'sources', 'leads-statuses': 'statuses',
        'leads-email-integration': 'email-integration', 'leads-web-to-lead': 'web-to-lead',
        'finance-tax-rates': 'tax-rates', 'finance-currencies': 'currencies',
        'finance-payment-modes': 'payment-modes', 'finance-expenses-categories': 'expenses-categories',
        'contracts-types': 'contract-types',
        'estimate-request-forms': 'forms', 'estimate-request-statuses': 'estimate-statuses',
        'menu-setup-main': 'main-menu', 'menu-setup-setup': 'setup-menu',
      };
      const urlSection = subId ? reverseMap[subId] : reverseMap[secId];
      return urlSection || secId;
    };

    // ── Setup mode enter/exit ────────────────────────────────────────
    const enterSetupMode = () => {
      setupMode.value = true;
      if (sidebarCollapsed.value) sidebarCollapsed.value = false;
      // Navigate to setup if not already there
      if (!route.path.startsWith('/admin/setup')) {
        router.push({ name: 'admin.setup', params: { section: 'staff' } });
      }
    };

    const exitSetupMode = () => {
      setupMode.value = false;
      router.push({ name: 'admin.dashboard' });
    };

    // ── Setup nav click handlers ────────────────────────────────────
    const onSetupNavClick = (sec) => {
      if (sec.children) {
        // Toggle expand/collapse
        setupExpandedGroups[sec.id] = !setupExpandedGroups[sec.id];
        // If expanding, navigate to first child
        if (setupExpandedGroups[sec.id]) {
          const firstChild = sec.children[0];
          if (firstChild) {
            const urlSection = mapSetupSectionToUrl(sec.id, firstChild.id);
            router.push({ name: 'admin.setup', params: { section: urlSection } });
          }
        }
      } else {
        // Navigate to section
        const urlSection = mapSetupSectionToUrl(sec.id, '');
        router.push({ name: 'admin.setup', params: { section: urlSection } });
      }
    };

    const onSetupChildClick = (parentId, childId) => {
      const urlSection = mapSetupSectionToUrl(parentId, childId);
      router.push({ name: 'admin.setup', params: { section: urlSection } });
    };

    // ── Watch route to sync setup mode and active sections ──────────
    watch(() => route.path, (newPath) => {
      if (newPath.startsWith('/admin/setup')) {
        setupMode.value = true;
        const section = route.params.section;
        const { sec, sub } = mapUrlToSetupSection(section);
        setupActiveSection.value = sec;
        setupActiveSubSection.value = sub;
        // Auto-expand parent group if navigating to a subsection
        if (sub) {
          setupExpandedGroups[sec] = true;
        }
      } else {
        setupMode.value = false;
      }
      if (window.innerWidth <= 768) {
        sidebarCollapsed.value = true;
      }
    }, { immediate: true });

    const toggleSidebar = () => { sidebarCollapsed.value = !sidebarCollapsed.value; };

    const toggleGroup = (name) => {
      if (sidebarCollapsed.value) sidebarCollapsed.value = false;
      expandedGroups[name] = !expandedGroups[name];
    };

    const handleLogout = async () => {
      await authStore.logoutAction();
      message.success('Logged out successfully.');
      router.push({ name: 'admin.login' });
    };

    const goToSeo = () => router.push('/admin/seo');

    const changeLanguage = async (lang) => {
      if (!user.value?.id) return;
      try {
        const payload = {
          name: user.value.name,
          email: user.value.email,
          default_language: lang
        };
        await axios.put(`/api/staff/${user.value.id}`, payload);
        await authStore.updateUserAction();
        message.success(`Language changed to ${lang === 'en' ? 'English' : lang === 'es' ? 'Spanish' : lang === 'fr' ? 'French' : 'German'}`);
      } catch (err) {
        message.error('Failed to change language');
      }
    };

    const getThemeSettingsFromStorage = () => {
      const saved = localStorage.getItem('crm_theme_style_settings');
      if (saved) {
        try { return JSON.parse(saved); } catch (e) {}
      }
      return {};
    };

    const currentThemeSettings = ref(getThemeSettingsFromStorage());

    onMounted(() => {
      if (typeof window !== 'undefined' && window.addEventListener) {
        window.addEventListener('crm-theme-settings-updated', (evt) => {
          currentThemeSettings.value = evt.detail || getThemeSettingsFromStorage();
        });
      }
    });

    const vuexyLogoImgError = ref(false);

    const resolvedLogoUrl = computed(() => {
      if (themeStore.currentTheme === 'custom' && themeStore.customTheme?.sidebarLogo) {
        return themeStore.customTheme.sidebarLogo;
      }
      return currentThemeSettings.value.sidebar_logo_url || logoUrl;
    });

    const resolvedLogoText = computed(() => {
      return currentThemeSettings.value.sidebar_logo_text || currentThemeSettings.value.company_name || 'iBRIDGE CRM';
    });

    const resolvedLogoWidth = computed(() => {
      return currentThemeSettings.value.sidebar_logo_width || '140px';
    });

    const resolvedLogoHeight = computed(() => {
      return currentThemeSettings.value.sidebar_logo_height || '38px';
    });

    watch(resolvedLogoUrl, () => {
      vuexyLogoImgError.value = false;
    });

    // ── Notifications ──────────────────────────────────────────────
    const notifDropdownOpen = ref(false);
    const headerNotifs = ref([]);
    const notifUnreadCount = ref(0);
    const expandedNotifIds = ref(new Set());

    const toggleNotifDetails = (id) => {
      const newSet = new Set(expandedNotifIds.value);
      if (newSet.has(id)) {
        newSet.delete(id);
      } else {
        newSet.add(id);
      }
      expandedNotifIds.value = newSet;
    };

    const isNotifExpanded = (id) => expandedNotifIds.value.has(id);

    const parseNotification = (item) => {
      const text = item.text || item.description || '';
      let category = 'system';
      let title = text;
      let actor = '';
      let role = '';
      let granted = [];
      let revoked = [];
      let rawDetails = '';

      const lowerText = text.toLowerCase();

      if (lowerText.includes('staff permissions have been updated') || lowerText.includes('permission')) {
        category = 'security';
        
        const actorMatch = text.match(/updated by\s+([^.]+)/i);
        if (actorMatch && actorMatch[1]) {
          actor = actorMatch[1].trim();
        }

        title = actor ? `Staff permissions updated by ${actor}` : 'Staff permissions updated';

        const detailsIdx = text.indexOf('Details:');
        if (detailsIdx !== -1) {
          rawDetails = text.substring(detailsIdx + 8).trim();
          const parts = rawDetails.split('|').map(p => p.trim());

          parts.forEach(part => {
            if (part.toLowerCase().startsWith('new role:')) {
              role = part.substring(9).trim();
            } else if (part.toLowerCase().startsWith('granted:')) {
              const content = part.substring(8).trim();
              granted = content.split(';').map(s => s.trim()).filter(Boolean);
            } else if (part.toLowerCase().startsWith('revoked:')) {
              const content = part.substring(8).trim();
              revoked = content.split(';').map(s => s.trim()).filter(Boolean);
            }
          });
        }
      } else if (lowerText.includes('lead')) {
        category = 'lead';
      } else if (lowerText.includes('task')) {
        category = 'task';
      } else if (lowerText.includes('contract')) {
        category = 'contract';
      } else if (lowerText.includes('invoice') || lowerText.includes('payment')) {
        category = 'invoice';
      } else if (lowerText.includes('project')) {
        category = 'project';
      }

      return {
        category,
        title,
        actor,
        role,
        granted,
        revoked,
        rawDetails
      };
    };

    const fetchHeaderNotifs = async () => {
      try {
        const res = await axios.get('/api/notifications');
        const allNotifs = res.data.data || [];
        headerNotifs.value = allNotifs.filter(n => !n.read && !n.isread);
        notifUnreadCount.value = res.data.unread_count ?? headerNotifs.value.length;
      } catch (e) {}
    };

    const markNotifsRead = async () => {
      try {
        await axios.post('/api/notifications/mark-all-read');
        headerNotifs.value = [];
        notifUnreadCount.value = 0;
        message.success('All notifications marked as read');
        if (typeof window !== 'undefined' && window.dispatchEvent) {
          window.dispatchEvent(new CustomEvent('refresh-notifications'));
        }
      } catch (e) {}
    };

    const markNotifItemRead = async (item) => {
      try {
        await axios.post(`/api/notifications/${item.id}/read`);
        headerNotifs.value = headerNotifs.value.filter(n => n.id !== item.id);
        if (notifUnreadCount.value > 0) notifUnreadCount.value--;
        if (typeof window !== 'undefined' && window.dispatchEvent) {
          window.dispatchEvent(new CustomEvent('refresh-notifications'));
        }
      } catch (e) {}
    };

    // ── My Todos Count & Header Dropdown Panel ───────────────────────
    const todoDropdownOpen = ref(false);
    const pendingTodosCount = ref(0);
    const pendingTodosList = ref([]);
    const staffMembersList = ref([]);
    const showQuickAddTask = ref(false);
    const quickTaskText = ref('');
    const editingHeaderTaskId = ref(null);
    const editingHeaderTaskText = ref('');

    const fetchPendingTodosCount = async () => {
      try {
        const res = await axios.get('/api/todos');
        if (Array.isArray(res.data)) {
          // Filter ONLY incomplete / pending tasks (!t.done)
          pendingTodosList.value = res.data.filter(t => !t.done);
          pendingTodosCount.value = pendingTodosList.value.length;
        }
      } catch (e) {
        console.error('Failed to load pending todos count', e);
      }
    };

    const fetchStaffMembers = async () => {
      try {
        const res = await axios.get('/api/staff', { params: { per_page: 200 } });
        const raw = res.data;
        let list = [];
        if (Array.isArray(raw)) {
          list = raw;
        } else if (raw?.data && Array.isArray(raw.data)) {
          list = raw.data;
        } else if (raw?.staff?.data && Array.isArray(raw.staff.data)) {
          list = raw.staff.data;
        } else if (raw?.result && Array.isArray(raw.result)) {
          list = raw.result;
        }
        staffMembersList.value = list;
      } catch (e) {
        console.error('Failed to load staff list', e);
      }
    };

    const notifyTodosChanged = () => {
      if (typeof window !== 'undefined' && window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('refresh-todos'));
      }
    };

    const toggleHeaderTodo = async (task) => {
      try {
        await axios.put('/api/todos/' + task.id, { done: !task.done });
        task.done = !task.done;
        fetchPendingTodosCount();
        notifyTodosChanged();
      } catch (e) {
        console.error('Failed to toggle todo from header', e);
      }
    };

    const assignHeaderTodo = async (task, val) => {
      const staffId = (val === '' || val === 'null' || val === null || val === 'undefined') ? null : parseInt(val);
      try {
        await axios.put('/api/todos/' + task.id, { assigned_to: staffId });
        task.assigned_to = staffId;
        notifyTodosChanged();
      } catch (e) {
        console.error('Failed to assign todo from header', e);
      }
    };

    const deleteHeaderTodo = async (task) => {
      try {
        await axios.delete('/api/todos/' + task.id);
        fetchPendingTodosCount();
        notifyTodosChanged();
        message.success('Task deleted');
      } catch (e) {
        console.error('Failed to delete todo from header', e);
      }
    };

    const saveQuickHeaderTask = async () => {
      if (!quickTaskText.value.trim()) return;
      try {
        await axios.post('/api/todos', { description: quickTaskText.value.trim() });
        quickTaskText.value = '';
        showQuickAddTask.value = false;
        fetchPendingTodosCount();
        notifyTodosChanged();
        message.success('Task created successfully!');
      } catch (e) {
        console.error('Failed to create quick task', e);
      }
    };

    const startHeaderTaskEdit = (task) => {
      editingHeaderTaskId.value = task.id;
      editingHeaderTaskText.value = task.description;
    };

    const cancelHeaderTaskEdit = () => {
      editingHeaderTaskId.value = null;
      editingHeaderTaskText.value = '';
    };

    const saveHeaderTaskEdit = async (task) => {
      if (!editingHeaderTaskText.value.trim()) { cancelHeaderTaskEdit(); return; }
      try {
        await axios.put('/api/todos/' + task.id, { description: editingHeaderTaskText.value.trim() });
        task.description = editingHeaderTaskText.value.trim();
        cancelHeaderTaskEdit();
        notifyTodosChanged();
        message.success('Task updated');
      } catch (e) {
        console.error('Failed to edit task from header', e);
      }
    };

    const handleSyncState = () => {
      fetchHeaderNotifs();
      fetchPendingTodosCount();
      authStore.updateUserAction();
    };

    onMounted(() => {
      authStore.updateUserAction();
      fetchHeaderNotifs();
      fetchPendingTodosCount();
      fetchStaffMembers();
      if (typeof window !== 'undefined') {
        window.addEventListener('refresh-notifications', handleSyncState);
        window.addEventListener('refresh-todos', fetchPendingTodosCount);
        window.addEventListener('focus', handleSyncState);
      }
    });

    onUnmounted(() => {
      if (typeof window !== 'undefined') {
        window.removeEventListener('refresh-notifications', handleSyncState);
        window.removeEventListener('refresh-todos', fetchPendingTodosCount);
        window.removeEventListener('focus', handleSyncState);
      }
    });

    const userHeaderImageError = ref(false);

    const getProfileImageUrl = (imagePath) => {
      if (!imagePath) return '';
      const basePath = window.config?.path?.replace(/\/$/, '') || '';
      if (imagePath.startsWith('http') || imagePath.startsWith('data:')) {
        return imagePath;
      }
      if (imagePath.startsWith('/')) return `${basePath}${imagePath}`;
      return `${basePath}/${imagePath}`;
    };

    // ── Header Timer ─────────────────────────────────────────────────
    const timerDropdownOpen = ref(false);
    const headerTimer = reactive({ running: false, seconds: 0, startTime: null });
    let headerTimerInterval = null;

    const formatHeaderDuration = (s) => {
      const h = Math.floor(s / 3600);
      const m = Math.floor((s % 3600) / 60);
      const sec = s % 60;
      return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(sec).padStart(2, '0')}`;
    };

    const headerTimerStartStr = computed(() => {
      if (!headerTimer.startTime) return '';
      const d = new Date(headerTimer.startTime);
      const pad = n => String(n).padStart(2, '0');
      return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
    });

    const startHeaderTimer = () => {
      headerTimer.running = true;
      headerTimer.startTime = Date.now();
      headerTimer.seconds = 0;
      headerTimerInterval = setInterval(() => { headerTimer.seconds++; }, 1000);
    };

    const stopHeaderTimer = () => {
      headerTimer.running = false;
      timerDropdownOpen.value = false;
    };

    const filteredMenuItems = computed(() => {
      const items = allMenuItems.value;
      const u = authStore.user;
      if (!u) return [];

      const isAdmin = (
        u.admin == 1 ||
        u.admin === "1" ||
        u.is_admin == 1 ||
        u.is_admin === true ||
        u.role_data?.slug === "admin" ||
        u.role_data?.name?.toLowerCase() === "admin" ||
        u.permissions?.all === true
      );
      if (isAdmin) return items;

      return items.filter(item => {
        if (item.name === 'Dashboard') return true;
        const permFeature = item.permission || item.name;

        if (item.children && item.children.length > 0) {
          const validChildren = item.children.filter(c => authStore.hasPermission(c.permission || c.name, 'view'));
          return validChildren.length > 0;
        }

        return authStore.hasPermission(permFeature, 'view');
      }).map(item => {
        if (item.children && item.children.length > 0) {
          return {
            ...item,
            children: item.children.filter(c => authStore.hasPermission(c.permission || c.name, 'view'))
          };
        }
        return item;
      });
    });

    const showCustomThemeModal = ref(false);
    const customForm = reactive({
      bg: '#f1f5f9',
      primary: '#7c3aed',
      primaryHover: '#6d28d9',
      textDark: '#4c1d95',
      accent: '#c084fc',
      headerBg: '',
      headerImage: '',
      sidebarLogo: ''
    });

    const openCustomThemeModal = () => {
      const activeObj = themeStore.currentTheme === 'custom' ? themeStore.customTheme : (themeStore.themes[themeStore.currentTheme] || themeStore.themes.lavender);
      customForm.bg = activeObj.bg || '#bcb3e2';
      customForm.primary = activeObj.primary || '#9f8ed6';
      customForm.primaryHover = activeObj.primaryHover || '#8d7bc8';
      customForm.textDark = activeObj.textDark || '#5f4f8d';
      customForm.accent = activeObj.accent || '#e8a7b0';
      customForm.headerBg = activeObj.headerBg || '';
      customForm.headerImage = activeObj.headerImage || '';
      customForm.sidebarLogo = activeObj.sidebarLogo || '';
      showCustomThemeModal.value = true;
    };

    const applyPreset = (presetKey) => {
      themeStore.setTheme(presetKey);
      const activeObj = themeStore.themes[presetKey];
      if (activeObj) {
        customForm.bg = activeObj.bg || '#bcb3e2';
        customForm.primary = activeObj.primary || '#9f8ed6';
        customForm.primaryHover = activeObj.primaryHover || '#8d7bc8';
        customForm.textDark = activeObj.textDark || '#5f4f8d';
        customForm.accent = activeObj.accent || '#e8a7b0';
      }
    };

    const saveCustomTheme = () => {
      themeStore.saveCustomTheme({ ...customForm });

      const themeSettings = getThemeSettingsFromStorage();
      if (customForm.sidebarLogo) {
        themeSettings.sidebar_logo_url = customForm.sidebarLogo;
      }
      localStorage.setItem('crm_theme_style_settings', JSON.stringify(themeSettings));
      if (typeof window !== 'undefined') {
        window.dispatchEvent(new CustomEvent('crm-theme-settings-updated', { detail: themeSettings }));
      }

      message.success('Dynamic custom theme, colors, and branding applied!');
      showCustomThemeModal.value = false;
    };

    return {
      sidebarCollapsed, expandedGroups, filteredMenuItems, menuItems: filteredMenuItems, toggleSidebar, toggleGroup,
      handleLogout, user, profileDisplayName, resolvedLogoUrl, resolvedLogoText, resolvedLogoWidth, resolvedLogoHeight, vuexyLogoImgError, getProfileImageUrl, goToSeo, clayAvatarUrl, userHeaderImageError, ddIcons,
      // Setup mode
      setupMode, setupSections, setupExpandedGroups,
      setupActiveSection, setupActiveSubSection,
      enterSetupMode, exitSetupMode, onSetupNavClick, onSetupChildClick,
      changeLanguage,
      // Header timer
      timerDropdownOpen, headerTimer, headerTimerStartStr,
      formatHeaderDuration, startHeaderTimer, stopHeaderTimer,
      // Notifications & Todos
      notifDropdownOpen, markNotifsRead, fetchHeaderNotifs, headerNotifs, notifUnreadCount, markNotifItemRead,
      parseNotification, toggleNotifDetails, isNotifExpanded, pendingTodosCount, fetchPendingTodosCount,
      todoDropdownOpen, pendingTodosList, staffMembersList, showQuickAddTask, quickTaskText,
      editingHeaderTaskId, editingHeaderTaskText, toggleHeaderTodo, assignHeaderTodo, deleteHeaderTodo,
      saveQuickHeaderTask, startHeaderTaskEdit, cancelHeaderTaskEdit, saveHeaderTaskEdit,
      themeStore,
      vuexyMenuSections,
      vuexyExpandedGroups,
      toggleVuexyGroup,
      toggleSkinMode,
      scrollToTop,
      showCustomThemeModal,
      customForm,
      openCustomThemeModal,
      applyPreset,
      saveCustomTheme,
    };
  },
});
</script>

<style scoped>
/* ── Reset & Base ─────────────────────────────────────────────────── */
*,::before,::after { box-sizing: border-box; margin: 0; padding: 0; }

.crm-app-shell {
  display: flex;
  height: 100vh;
  width: 100vw;
  overflow: hidden;
  position: relative;
}

.crm-app-shell.theme-template-vuexy {
  font-family: 'Public Sans', 'Inter', -apple-system, sans-serif !important;
  font-size: 14px;
  color: #5D596C !important;
  background-color: #F8F7FA !important;
  background-image: none !important;
}

.crm-app-shell.theme-template-organic {
  font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  font-size: 14px;
  color: #334155;
  background-color: var(--theme-bg, #bcb3e2) !important;
  background-image: var(--theme-bg-image, none) !important;
  background-size: cover !important;
  background-position: center !important;
  background-attachment: fixed !important;
  transition: background 0.3s ease;
}

.crm-sidebar, .crm-main {
  position: relative;
  z-index: 10;
}

/* ── Organic Wave Decorators ────────────────────────────────────── */
.organic-bg-decor {
  position: absolute;
  pointer-events: none;
  z-index: 1;
  opacity: 0.85;
}

.wave-1 {
  top: -15%;
  left: 10%;
  width: 90vw;
  height: 60vh;
  background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.16) 0%, rgba(255, 255, 255, 0) 70%);
  border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
  filter: blur(50px);
}

.wave-2 {
  bottom: -20vh;
  right: -10vw;
  width: 80vw;
  height: 70vh;
  background: linear-gradient(135deg, rgba(245, 243, 239, 0.35) 0%, rgba(204, 128, 92, 0.08) 100%);
  border-radius: 50% 50% 40% 60% / 50% 60% 40% 50%;
  box-shadow: 
    0 -30px 60px rgba(30, 45, 38, 0.05),
    inset 0 10px 20px rgba(255, 255, 255, 0.4);
}

.wave-3 {
  bottom: -10vh;
  left: -15vw;
  width: 60vw;
  height: 50vh;
  background: linear-gradient(45deg, rgba(87, 128, 112, 0.12) 0%, rgba(255, 255, 255, 0) 100%);
  border-radius: 40% 60% 50% 50% / 60% 40% 60% 40%;
}

.wave-4 {
  top: 25%;
  right: 5%;
  width: 35vw;
  height: 50vh;
  background: radial-gradient(circle, rgba(245, 243, 239, 0.25) 0%, rgba(87, 128, 112, 0.05) 100%);
  border-radius: 70% 30% 50% 50% / 30% 70% 40% 60%;
  filter: blur(30px);
}

/* ── Sidebar ──────────────────────────────────────────────────────── */
.crm-sidebar {
  width: 240px;
  min-width: 240px;
  background: #faf6f0;
  border-radius: 24px;
  margin: 16px 8px 16px 16px;
  border: 1px solid rgba(255, 255, 255, 0.7);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
  height: calc(100vh - 32px);
  overflow: hidden;
  transition: width 0.25s ease, min-width 0.25s ease, box-shadow 0.3s;
  flex-shrink: 0;
  z-index: 30;
}
.crm-sidebar--collapsed {
  width: 72px;
  min-width: 72px;
}

/* Logo */
.crm-sidebar__logo {
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 16px;
  border-bottom: 1px solid rgba(163, 149, 127, 0.12);
  flex-shrink: 0;
}
.crm-sidebar__logo-inner {
  display: flex;
  align-items: center;
  overflow: hidden;
  background: #fff;
  border-radius: 10px;
  padding: 6px 14px;
  border: none;
}
.crm-sidebar--collapsed .crm-sidebar__logo-inner {
  border: none;
  padding: 0;
}
.crm-logo-img {
  height: 28px;
  max-width: 130px;
  object-fit: contain;
  transition: all 0.2s ease;
  display: block;
}
.crm-sidebar--collapsed .crm-logo-img {
  height: 24px;
  max-width: 24px;
}
.crm-hamburger {
  background: none;
  border: none;
  cursor: pointer;
  color: var(--theme-text-dark, #5f4f8d);
  padding: 6px;
  display: flex;
  align-items: center;
  border-radius: 50%;
  flex-shrink: 0;
  transition: all 0.2s;
  box-shadow: 2px 2px 5px rgba(163, 149, 127, 0.15), -2px -2px 5px rgba(255, 255, 255, 0.8);
}
.crm-hamburger svg { width: 18px; height: 18px; }
.crm-hamburger:hover { color: var(--theme-primary, #9f8ed6); background: rgba(163, 149, 127, 0.05); }

/* Profile */
.crm-sidebar__profile {
  padding: 12px 10px;
  border-bottom: 1px solid #eef0f3;
  flex-shrink: 0;
}
.crm-profile-card {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #ffffff !important;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 10px 12px;
  box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02);
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.crm-profile-card:hover {
  background: #ffffff !important;
  border-color: #cbd5e1;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08), 0 2px 4px rgba(15, 23, 42, 0.03);
}
.crm-profile-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
  border: 2px solid #ffffff;
  box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
}
.crm-profile-info {
  display: flex;
  flex-direction: column;
  overflow: hidden;
  min-width: 0;
}
.crm-profile-name {
  font-size: 13.5px;
  font-weight: 700;
  color: #1e293b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.3;
}
.crm-profile-email {
  font-size: 11.5px;
  color: #64748b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.3;
  margin-top: 2px;
}

/* Navigation */
.crm-sidebar__nav {
  flex: 1;
  overflow-y: auto;
  padding: 10px 8px;
  scrollbar-width: thin;
  scrollbar-color: rgba(163, 149, 127, 0.2) transparent;
}
.crm-sidebar__nav::-webkit-scrollbar { width: 4px; }
.crm-sidebar__nav::-webkit-scrollbar-track { background: transparent; }
.crm-sidebar__nav::-webkit-scrollbar-thumb { background: rgba(163, 149, 127, 0.2); border-radius: 4px; }

/* Nav item */
.crm-nav-item {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 10px 14px;
  margin-bottom: 4px;
  gap: 12px;
  font-size: 13.5px;
  font-weight: 700;
  color: var(--theme-text-dark, #5f4f8d);
  opacity: 0.75;
  text-decoration: none;
  border: none;
  background: none;
  cursor: pointer;
  text-align: left;
  border-radius: 999px;
  position: relative;
  transition: all 0.25s ease;
  line-height: 1.5;
}
.crm-nav-item:hover {
  background: rgba(188, 179, 226, 0.15);
  opacity: 1;
}
.crm-nav-item--active {
  background: #ffffff !important;
  color: var(--theme-text-dark, #5f4f8d) !important;
  opacity: 1;
  box-shadow: 
    inset 3px 3px 6px rgba(100, 90, 130, 0.12),
    inset -3px -3px 6px rgba(255, 255, 255, 0.95),
    1px 2px 4px rgba(100, 90, 130, 0.05);
}
.crm-nav-item--active .crm-nav-icon :deep(svg),
.crm-nav-item--active .crm-nav-icon svg { 
  color: var(--theme-primary, #9f8ed6); 
  stroke: var(--theme-primary, #9f8ed6); 
}
.crm-nav-item--active::after {
  content: '';
  position: absolute;
  right: 12px;
  width: 5px;
  height: 5px;
  background: var(--theme-primary, #9f8ed6);
  border-radius: 50%;
}

/* Icon */
.crm-nav-icon {
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: var(--theme-text-dark, #5f4f8d);
  opacity: 0.7;
  transition: all 0.2s;
}
.crm-nav-item--active .crm-nav-icon { opacity: 1; }
.crm-nav-item:hover .crm-nav-icon { opacity: 1; }
.crm-nav-icon :deep(svg) { width: 20px; height: 20px; }

/* Chevron */
.crm-nav-chevron {
  width: 16px;
  height: 16px;
  margin-left: auto;
  color: var(--theme-text-dark, #5f4f8d);
  opacity: 0.6;
  flex-shrink: 0;
  transition: transform 0.2s ease;
}
.crm-nav-chevron--open {
  transform: rotate(-90deg);
}

/* Group */
.crm-nav-group { }

/* Submenu */
.crm-submenu {
  background: rgba(163, 149, 127, 0.06);
  border-radius: 16px;
  margin: 2px 8px;
  overflow: hidden;
  box-shadow: inset 1px 1px 3px rgba(163, 149, 127, 0.1);
}
.crm-submenu-item {
  display: block;
  padding: 9px 14px 9px 46px;
  font-size: 13px;
  color: var(--theme-text-dark, #5f4f8d);
  opacity: 0.7;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;
  border-radius: 999px;
  margin: 2px 4px;
  font-weight: 600;
}
.crm-submenu-item:hover {
  background: rgba(188, 179, 226, 0.12);
  opacity: 1;
}
.crm-submenu-item--active {
  color: var(--theme-text-dark, #5f4f8d);
  opacity: 1;
  font-weight: 700;
  background: #ffffff;
  box-shadow: inset 2px 2px 4px rgba(100, 90, 130, 0.08);
}

/* Sidebar Footer */
.crm-sidebar__footer {
  border-top: 1px solid rgba(163, 149, 127, 0.12);
  padding: 14px 14px;
  flex-shrink: 0;
  background: rgba(163, 149, 127, 0.04);
}
.crm-pinned-project { display: flex; flex-direction: column; gap: 4px; }
.crm-pinned-name { font-size: 13px; font-weight: 700; color: var(--theme-text-dark, #5f4f8d); }
.crm-pinned-client { font-size: 11.5px; color: var(--theme-text-dark, #5f4f8d); opacity: 0.6; }
.crm-pinned-bar {
  width: 100%;
  height: 6px;
  background: #ffffff;
  border-radius: 99px;
  margin-top: 6px;
  overflow: hidden;
  box-shadow: inset 1px 1px 3px rgba(100, 90, 130, 0.1);
}
.crm-pinned-bar__fill {
  height: 100%;
  background: var(--theme-primary, #9f8ed6);
  border-radius: 99px;
}

/* ── Header ───────────────────────────────────────────────────────── */
.crm-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  min-width: 0;
}
.crm-header {
  height: 64px;
  background-color: var(--theme-header-bg, #faf6f0) !important;
  background-image: var(--theme-header-image, none) !important;
  background-size: cover !important;
  background-position: center !important;
  background-repeat: no-repeat !important;
  border-radius: 24px;
  margin: 16px 16px 0 8px;
  border: 1px solid rgba(255, 255, 255, 0.7);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  flex-shrink: 0;
  z-index: 20;
  gap: 16px;
}
.crm-header__left {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
  min-width: 0;
}
.crm-header__right {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}
.crm-hamburger--mobile {
  display: none;
}
@media (max-width: 768px) {
  .crm-hamburger--mobile { display: flex; }
}

/* Search */
.crm-search {
  display: flex;
  align-items: center;
  background: #ffffff;
  border: none;
  border-radius: 999px;
  padding: 0 16px;
  gap: 8px;
  max-width: 300px;
  flex: 1;
  box-shadow: 
    inset 3px 3px 6px rgba(100, 90, 130, 0.12),
    inset -3px -3px 6px rgba(255, 255, 255, 0.95);
  transition: all 0.25s ease;
}
.crm-search:focus-within {
  box-shadow: 
    inset 3px 3px 6px rgba(100, 90, 130, 0.15),
    inset -3px -3px 6px rgba(255, 255, 255, 0.95),
    0 0 0 3px rgba(159, 142, 214, 0.3);
}
.crm-search__icon {
  width: 16px;
  height: 16px;
  color: var(--theme-text-dark, #5f4f8d);
  opacity: 0.6;
  flex-shrink: 0;
}
.crm-search__input {
  border: none;
  background: none;
  outline: none;
  font-size: 13.5px;
  color: var(--theme-text-dark, #5f4f8d);
  width: 100%;
  padding: 8px 0;
  font-family: inherit;
  font-weight: 600;
}
.crm-search__input::placeholder { color: var(--theme-text-dark, #5f4f8d); opacity: 0.45; }

/* Quick Create */
.crm-quick-create {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #faf6f0;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--theme-text-dark, #5f4f8d);
  flex-shrink: 0;
  box-shadow: 
    3px 3px 6px rgba(163, 149, 127, 0.15),
    -3px -3px 6px rgba(255, 255, 255, 0.9),
    inset 1px 1px 2px rgba(255, 255, 255, 0.5);
  transition: all 0.25s ease;
}
.crm-quick-create:hover {
  transform: translateY(-1px);
  color: var(--theme-primary, #9f8ed6);
}
.crm-quick-create:active {
  transform: translateY(1px);
  box-shadow: inset 2px 2px 4px rgba(100, 90, 130, 0.15);
}
.crm-quick-create svg { width: 16px; height: 16px; }

/* Header links */
.crm-header-link {
  font-size: 13px;
  color: var(--theme-text-dark, #5f4f8d);
  text-decoration: none;
  padding: 6px 12px;
  border-radius: 999px;
  white-space: nowrap;
  transition: all 0.2s;
  font-weight: 700;
  opacity: 0.8;
}
.crm-header-link:hover { 
  opacity: 1;
  background: rgba(188, 179, 226, 0.15);
}
.crm-header-link--new {
  background: var(--theme-primary, #9f8ed6);
  color: #fff;
  opacity: 1;
  padding: 6px 14px;
  font-weight: 800;
  box-shadow: 0 2px 12px rgba(159, 142, 214, 0.3);
  transition: all 0.25s ease;
}
.crm-header-link--new:hover {
  background: var(--theme-text-dark, #5f4f8d);
  box-shadow: 0 4px 20px rgba(95, 79, 141, 0.4);
  transform: translateY(-1px);
}
@media (max-width: 900px) { .crm-header-link { display: none; } }

/* Header Actions */
.crm-header-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  border-left: 1.5px solid rgba(163, 149, 127, 0.15);
  padding-left: 16px;
  margin-left: 8px;
}
.crm-action-btn {
  background: #faf6f0;
  border: none;
  cursor: pointer;
  color: var(--theme-text-dark, #5f4f8d);
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  box-shadow: 
    3px 3px 6px rgba(163, 149, 127, 0.15),
    -3px -3px 6px rgba(255, 255, 255, 0.9),
    inset 1px 1px 2px rgba(255, 255, 255, 0.5);
  transition: all 0.2s ease;
}
.crm-action-btn svg { width: 18px; height: 18px; }
.crm-action-btn:hover { 
  color: var(--theme-primary, #9f8ed6);
  transform: translateY(-1px);
}
.crm-action-btn:active {
  transform: translateY(1px);
  box-shadow: inset 2px 2px 4px rgba(100, 90, 130, 0.15);
}

.crm-header-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  object-fit: cover;
  cursor: pointer;
  border: 2px solid #ffffff;
  margin-left: 6px;
  box-shadow: 2px 2px 5px rgba(163, 149, 127, 0.2);
  transition: all 0.2s;
}
.crm-header-avatar:hover { 
  transform: scale(1.05);
  border-color: var(--theme-primary, #9f8ed6);
}

/* Theme Selector Styles */
.theme-selector-container {
  display: flex;
  gap: 8px;
  margin-right: 8px;
  align-items: center;
}
.theme-orb {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 2px solid #ffffff;
  cursor: pointer;
  box-shadow: 
    2px 2px 4px rgba(163, 149, 127, 0.25),
    inset 1px 1px 2px rgba(255, 255, 255, 0.5);
  transition: all 0.2s ease;
  outline: none;
}
.theme-orb:hover {
  transform: scale(1.2);
}
.theme-orb.active {
  box-shadow: 
    inset 2px 2px 4px rgba(0, 0, 0, 0.15),
    inset -2px -2px 4px rgba(255, 255, 255, 0.8);
  transform: scale(0.95);
}
.theme-orb.sage { background: #cc805c; }
.theme-orb.lavender { background: #9f8ed6; }
.theme-orb.mint { background: #579b82; }
.theme-orb.peach { background: #d67b74; }
.theme-orb.blue { background: #6ca0cc; }
.theme-orb.custom { background: var(--theme-primary, #7c3aed); }

.theme-customizer-btn {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.8);
  background: rgba(255, 255, 255, 0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  padding: 0;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  margin-left: 2px;
}
.theme-customizer-btn:hover,
.theme-customizer-btn.active {
  transform: scale(1.15);
  background: #ffffff;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.18);
  border-color: var(--theme-primary, #7c3aed);
}
.theme-orb.peach { background: #d67b74; }
.theme-orb.blue { background: #6ca0cc; }

/* ── Header Timer Card ────────────────────────────────────────────── */
.crm-timer-active { color: #10b981 !important; }
.header-timer-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.15);
  padding: 16px;
  min-width: 220px;
  font-family: inherit;
}
.timer-card-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 8px 0;
}
.timer-empty-text { font-size: 12px; font-weight: 600; color: #94a3b8; }
.timer-start-btn { margin-top: 4px; }
.timer-card-running { display: flex; flex-direction: column; gap: 8px; }
.timer-card-row { display: flex; align-items: center; gap: 8px; }
.timer-running-dot {
  width: 8px; height: 8px; border-radius: 50%; background: #10b981;
  animation: pulse-dot 1.2s ease-in-out infinite;
}
@keyframes pulse-dot { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }
.timer-running-label { font-size: 11px; font-weight: 600; color: #1e293b; }
.timer-label { font-size: 11px; font-weight: 600; color: #64748b; }
.timer-value { font-size: 16px; font-weight: 800; color: #1e293b; font-variant-numeric: tabular-nums; }
.btn-stop-timer {
  background: #fee2e2; color: #dc2626; border: none; border-radius: 8px;
  padding: 8px 16px; font-size: 12px; font-weight: 700; cursor: pointer;
  font-family: inherit; transition: all 0.1s; width: 100%;
}
.btn-stop-timer:hover { background: #fecaca; }
.timer-card-footer {
  border-top: 1px solid #f1f5f9; margin-top: 12px; padding-top: 10px;
  text-align: center;
}
.view-all-link { font-size: 11px; font-weight: 600; color: #6366f1; text-decoration: none; }
.view-all-link:hover { text-decoration: underline; }

/* ── Notification Card ──────────────────────────────────────────── */
.notif-card {
  width: 390px;
  max-width: calc(100vw - 32px);
  padding: 0;
  border-radius: 16px;
  box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.18), 0 0 0 1px rgba(15, 23, 42, 0.08);
  border: none;
  background: #ffffff;
  overflow: hidden;
  font-family: inherit;
}

/* ── Tasks Popover Card ──────────────────────────────────────────── */
.tasks-popover-card {
  width: 410px;
  max-width: calc(100vw - 32px);
  padding: 0;
  border-radius: 16px;
  box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.18), 0 0 0 1px rgba(15, 23, 42, 0.08);
  border: none;
  background: #ffffff;
  overflow: hidden;
  font-family: inherit;
}

.tasks-popover-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 18px;
  background: #ffffff;
  border-bottom: 1px solid #f1f5f9;
}

.tasks-popover-title {
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
}

.btn-quick-add-task {
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #bfdbfe;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 11.5px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-quick-add-task:hover {
  background: #dbeafe;
}

.quick-add-task-row {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  background: #f8fafc;
  border-bottom: 1px solid #f1f5f9;
}

.quick-task-input {
  flex: 1;
  height: 32px;
  padding: 0 10px;
  font-size: 12px;
  font-weight: 600;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  outline: none;
}
.quick-task-input:focus {
  border-color: #2563eb;
}

.btn-quick-save {
  height: 32px;
  padding: 0 12px;
  background: #2563eb;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
}

.tasks-popover-body {
  max-height: 360px;
  overflow-y: auto;
  padding: 8px 12px;
}

.tasks-popover-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 32px 16px;
  gap: 8px;
  text-align: center;
}
.tasks-popover-empty .empty-text {
  font-size: 12.5px;
  font-weight: 600;
  color: #64748b;
  margin: 0;
}

.task-popover-item {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 12px;
  transition: background 0.15s;
  border-bottom: 1px solid #f8fafc;
}
.task-popover-item:hover {
  background: #f8fafc;
}

.task-item-main {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  flex: 1;
  min-width: 0;
}

.task-item-checkbox {
  width: 17px;
  height: 17px;
  accent-color: #2563eb;
  cursor: pointer;
  margin-top: 2px;
  flex-shrink: 0;
}

.task-item-content {
  flex: 1;
  min-width: 0;
}

.task-item-desc {
  font-size: 12.5px;
  font-weight: 600;
  color: #1e293b;
  line-height: 1.4;
  margin: 0 0 4px 0;
  word-break: break-word;
}

.task-item-edit-input {
  width: 100%;
  padding: 4px 8px;
  font-size: 12px;
  font-weight: 600;
  border: 1px solid #2563eb;
  border-radius: 6px;
  outline: none;
}

.task-item-meta-row {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.task-date-tag {
  font-size: 10.5px;
  font-weight: 600;
  color: #94a3b8;
  background: #f1f5f9;
  padding: 1px 6px;
  border-radius: 6px;
}

.task-assignee-wrap {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.assignee-lbl {
  font-size: 10.5px;
  font-weight: 700;
  color: #64748b;
}

.task-assignee-select {
  font-size: 10.5px;
  font-weight: 700;
  color: #2563eb;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 6px;
  padding: 1px 4px;
  cursor: pointer;
  outline: none;
  max-width: 130px;
}

.task-item-actions {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;
  margin-top: 1px;
}

.task-icon-act {
  background: transparent;
  border: none;
  font-size: 13px;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  transition: background 0.15s;
}
.task-icon-act:hover {
  background: #f1f5f9;
}

.tasks-popover-footer {
  padding: 10px 18px;
  background: #f8fafc;
  border-top: 1px solid #f1f5f9;
  text-align: center;
}

.tasks-view-all {
  font-size: 12px;
  font-weight: 700;
  color: #2563eb;
  text-decoration: none;
}
.tasks-view-all:hover {
  text-decoration: underline;
}

.notif-card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 18px;
  background: #ffffff;
  border-bottom: 1px solid #f1f5f9;
}

.notif-card-title-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.notif-card-title {
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
  letter-spacing: -0.01em;
}

.notif-count-badge {
  background: #eef2ff;
  color: #4f46e5;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 9999px;
  border: 1px solid #e0e7ff;
}

.notif-mark-read {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: transparent;
  border: none;
  font-size: 11.5px;
  font-weight: 600;
  color: #6366f1;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  transition: all 0.15s ease;
  font-family: inherit;
}

.notif-mark-read:hover {
  background: #f1f5f9;
  color: #4338ca;
}

.notif-empty-box {
  padding: 32px 20px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.notif-empty-icon {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: #f8fafc;
  color: #94a3b8;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 12px;
}

.notif-empty-title {
  font-size: 14px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 4px 0;
}

.notif-empty-sub {
  font-size: 12px;
  color: #94a3b8;
  margin: 0;
}

.notif-list-scroll {
  max-height: 380px;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 transparent;
}

.notif-list-scroll::-webkit-scrollbar {
  width: 5px;
}

.notif-list-scroll::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.notif-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 13px 18px;
  border-bottom: 1px solid #f1f5f9;
  cursor: pointer;
  position: relative;
  transition: background 0.15s ease;
}

.notif-item:last-child {
  border-bottom: none;
}

.notif-item:hover {
  background: #f8fafc;
}

.notif-item--unread {
  background: #f8faff;
}

.notif-item--unread::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3.5px;
  background: #6366f1;
  border-top-right-radius: 4px;
  border-bottom-right-radius: 4px;
}

/* Category Icon Boxes */
.notif-icon-box {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.notif-icon-box.security { background: #fef3c7; color: #d97706; }
.notif-icon-box.lead { background: #dbeafe; color: #2563eb; }
.notif-icon-box.task { background: #e0e7ff; color: #4f46e5; }
.notif-icon-box.contract { background: #cffafe; color: #0891b2; }
.notif-icon-box.invoice { background: #dcfce7; color: #16a34a; }
.notif-icon-box.project { background: #f3e8ff; color: #9333ea; }
.notif-icon-box.system { background: #f1f5f9; color: #64748b; }

.notif-body {
  flex: 1;
  min-width: 0;
}

.notif-header-line {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 3px;
}

.notif-title-text {
  font-size: 12.5px;
  font-weight: 600;
  color: #334155;
  line-height: 1.4;
}

.notif-item--unread .notif-title-text {
  font-weight: 700;
  color: #0f172a;
}

.notif-time-text {
  font-size: 11px;
  font-weight: 500;
  color: #94a3b8;
  white-space: nowrap;
  flex-shrink: 0;
}

/* Chip tags */
.notif-chips-row {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  margin-top: 6px;
}

.notif-chip {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  font-size: 10.5px;
  font-weight: 600;
  padding: 2px 7px;
  border-radius: 6px;
  line-height: 1.3;
}

.notif-chip.role { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.notif-chip.granted { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.notif-chip.revoked { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }

.notif-toggle-btn {
  background: transparent;
  border: none;
  padding: 0;
  margin-top: 6px;
  font-size: 11px;
  font-weight: 600;
  color: #6366f1;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 3px;
  font-family: inherit;
}

.notif-toggle-btn:hover {
  text-decoration: underline;
}

.notif-details-box {
  margin-top: 8px;
  padding: 10px 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 11px;
  color: #334155;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.notif-detail-group {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.notif-detail-lbl {
  font-weight: 700;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.notif-detail-lbl.granted { color: #047857; }
.notif-detail-lbl.revoked { color: #be123c; }
.notif-detail-lbl.role { color: #4338ca; }

.notif-detail-val {
  color: #475569;
  line-height: 1.4;
  word-break: break-word;
}

.notif-footer {
  padding: 11px 16px;
  background: #fafafa;
  border-top: 1px solid #f1f5f9;
  text-align: center;
}

.view-all-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 600;
  color: #6366f1;
  text-decoration: none;
  transition: all 0.15s ease;
}

.view-all-link:hover {
  color: #4338ca;
  transform: translateX(2px);
}

/* ── Demo Bar ─────────────────────────────────────────────────────── */
.crm-demo-bar {
  background: #f0fdf4;
  border-bottom: 1px solid #dcfce7;
  padding: 10px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-shrink: 0;
}
.crm-demo-bar__left {
  font-size: 12.5px;
  color: #166534;
  line-height: 1.5;
  display: flex;
  flex-direction: column;
}
.crm-demo-dot {
  display: inline-block;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #22c55e;
  margin-right: 6px;
  animation: ping 1.5s ease-in-out infinite;
}
@keyframes ping {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(1.3); }
}
.crm-demo-version { font-weight: 600; font-size: 12px; color: #15803d; }
.crm-demo-btn {
  background: #1e293b;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 5px 12px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  white-space: nowrap;
  transition: background 0.12s;
  font-family: inherit;
  flex-shrink: 0;
}
.crm-demo-btn svg { width: 13px; height: 13px; }
.crm-demo-btn:hover { background: #0f172a; }

/* ── Page Content ─────────────────────────────────────────────────── */
.crm-page-content {
  flex: 1;
  overflow-y: auto;
  padding: 24px 18px;
  background-color: var(--theme-bg, #bcb3e2) !important;
  background-image: var(--theme-bg-image, none) !important;
  background-size: cover !important;
  background-position: center !important;
  background-attachment: fixed !important;
  transition: background 0.3s ease;
}

/* ── Dropdown menu overrides ──────────────────────────────────────── */
.crm-dropdown-menu :deep(.ant-dropdown-menu-item) {
  font-size: 13px;
  color: #475569;
}
.crm-dropdown-menu :deep(.ant-dropdown-menu-item a) {
  color: #475569;
  text-decoration: none;
}
.crm-dropdown-menu :deep(.ant-dropdown-menu-item:hover) {
  background: #f1f5f9;
}

/* ── Profile dropdown card (white UI) ─────────────────────────────── */
.crm-dropdown-menu.prf-dropdown {
  background: #ffffff !important;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 8px;
  min-width: 240px;
  box-shadow: 0 20px 40px -8px rgba(15, 23, 42, 0.12), 0 4px 12px rgba(15, 23, 42, 0.04);
}
.crm-dropdown-menu.prf-dropdown--compact {
  padding: 6px;
  min-width: 220px;
}
.crm-dropdown-menu.prf-dropdown :deep(.ant-dropdown-menu-item) {
  border-radius: 10px;
  margin: 2px 0;
  padding: 0;
  transition: all 0.15s ease;
}
.crm-dropdown-menu.prf-dropdown :deep(.ant-dropdown-menu-item:hover) {
  background: #f8fafc;
}
.crm-dropdown-menu.prf-dropdown :deep(.ant-dropdown-menu-item-divider) {
  margin: 6px 4px;
  background: #f1f5f9;
}

.prf-dd-head {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 10px 12px;
  margin-bottom: 6px;
  border-bottom: 1px solid #f1f5f9;
}
.prf-dd-avatar {
  position: relative;
  flex-shrink: 0;
}
.prf-dd-avatar-img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #ffffff;
  box-shadow: 0 2px 8px rgba(15, 23, 42, 0.1);
}
.prf-dd-online {
  position: absolute;
  right: 0;
  bottom: 0;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #10b981;
  border: 2px solid #ffffff;
}
.prf-dd-meta {
  display: flex;
  flex-direction: column;
  min-width: 0;
}
.prf-dd-name {
  font-size: 13.5px;
  font-weight: 700;
  color: #0f172a;
  line-height: 1.3;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.prf-dd-email {
  font-size: 11.5px;
  color: #64748b;
  line-height: 1.3;
  margin-top: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.prf-dd-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 8px;
  border-radius: 8px;
  font-size: 12.5px;
  font-weight: 600;
  color: #334155;
  text-decoration: none;
  width: 100%;
}
.crm-dropdown-menu.prf-dropdown :deep(.ant-dropdown-menu-item a.prf-dd-item) {
  color: #334155;
}
.crm-dropdown-menu.prf-dropdown :deep(.ant-dropdown-menu-item:hover) .prf-dd-item {
  color: #4f46e5;
}
.prf-dd-ico {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  border-radius: 6px;
  background: #f1f5f9;
  color: #6366f1;
  transition: all 0.15s ease;
}
.prf-dd-ico svg {
  width: 12px;
  height: 12px;
}
.crm-dropdown-menu.prf-dropdown :deep(.ant-dropdown-menu-item:hover) .prf-dd-ico {
  background: #4f46e5;
  color: #ffffff;
}

.crm-dropdown-menu.prf-dropdown :deep(.ant-dropdown-menu-item.prf-dd-logout a.prf-dd-item),
.crm-dropdown-menu.prf-dropdown :deep(.ant-dropdown-menu-item.prf-dd-logout) .prf-dd-item {
  color: #ef4444;
}
.crm-dropdown-menu.prf-dropdown :deep(.ant-dropdown-menu-item.prf-dd-logout) .prf-dd-ico {
  background: #fef2f2;
  color: #ef4444;
}
.crm-dropdown-menu.prf-dropdown :deep(.ant-dropdown-menu-item.prf-dd-logout:hover) {
  background: #fef2f2;
}
.crm-dropdown-menu.prf-dropdown :deep(.ant-dropdown-menu-item.prf-dd-logout:hover) .prf-dd-ico {
  background: #ef4444;
  color: #ffffff;
}
/* ── Setup Sidebar Styles ─────────────────────────────────────────── */
.crm-sidebar__nav--setup {
  animation: setupSlideIn 0.25s ease-out;
  padding: 8px;
}
@keyframes setupSlideIn {
  from {
    opacity: 0;
    transform: translateX(-12px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.crm-nav-back {
  border-bottom: 1px solid rgba(163, 149, 127, 0.12);
  margin-bottom: 4px;
  font-weight: 600 !important;
  color: var(--theme-text-dark, #5f4f8d);
  opacity: 0.95;
  font-size: 14px !important;
}
.crm-nav-back:hover {
  opacity: 1;
  color: var(--theme-primary, #9f8ed6);
  background: rgba(188, 179, 226, 0.15);
}

.crm-setup-divider {
  height: 1px;
  background: rgba(163, 149, 127, 0.12);
  margin: 4px 0;
}

.crm-setup-heading {
  padding: 12px 14px 8px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: var(--theme-text-dark, #5f4f8d);
  opacity: 0.55;
}

/* ── Mobile Responsive Layout Adjustments ── */
.crm-sidebar-backdrop {
  display: none;
}

@media (max-width: 768px) {
  .crm-sidebar-backdrop {
    display: block;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    z-index: 40;
  }

  .crm-sidebar {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    bottom: 0 !important;
    height: 100vh !important;
    margin: 0 !important;
    border-radius: 0 20px 20px 0 !important;
    z-index: 50 !important;
    transform: translateX(0);
    width: 260px !important;
    min-width: 260px !important;
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
  }

  .crm-sidebar--collapsed {
    transform: translateX(-100%) !important;
  }

  .crm-hamburger--mobile {
    display: flex !important;
  }

  .crm-header {
    margin: 8px 8px 0 8px !important;
    height: auto !important;
    min-height: 52px !important;
    padding: 6px 12px !important;
    border-radius: 16px !important;
  }

  .crm-header__right {
    gap: 6px !important;
  }

  .theme-dot {
    width: 14px !important;
    height: 14px !important;
  }

  .crm-page-content {
    padding: 12px 8px !important;
  }
}

@media (max-width: 480px) {
  .crm-search {
    display: none !important; /* Hide search to save header space on small mobile screen */
  }
  
  .theme-picker-header {
    gap: 3px !important;
  }

  .theme-dot {
    width: 12px !important;
    height: 12px !important;
  }
}

.animated-avatar-wrap {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 50%, #3730a3 100%);
  animation: avatarPulseGlow 4s ease-in-out infinite alternate;
}

.animate-avatar-float {
  animation: avatarFloat 3.5s ease-in-out infinite alternate;
}

@keyframes avatarFloat {
  0% { transform: translateY(0px) scale(1); }
  50% { transform: translateY(-3px) scale(1.05); }
  100% { transform: translateY(0px) scale(1); }
}

@keyframes avatarPulseGlow {
  0% { box-shadow: 0 2px 10px rgba(79, 70, 229, 0.3); }
  100% { box-shadow: 0 4px 16px rgba(99, 102, 241, 0.55); }
}
</style>
