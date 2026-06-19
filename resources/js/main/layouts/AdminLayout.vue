<template>
  <div class="crm-app-shell">
    <!-- Sidebar -->
    <aside :class="['crm-sidebar', { 'crm-sidebar--collapsed': sidebarCollapsed }]">
      <!-- Logo Header -->
      <div class="crm-sidebar__logo">
        <div class="crm-sidebar__logo-inner">
          <img :src="resolvedLogoUrl" alt="iBRIDGE Logo" class="crm-logo-img" />
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
              v-if="user?.profile_image"
              :src="getProfileImageUrl(user.profile_image)"
              :alt="user.name"
              class="crm-profile-avatar"
            />
            <img
              v-else
              src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
              alt="User Avatar"
              class="crm-profile-avatar"
            />
            <div class="crm-profile-info">
              <span class="crm-profile-name">{{ user?.name || 'Armando Turcotte' }}</span>
              <span class="crm-profile-email">{{ user?.email || 'admin@test.com' }}</span>
            </div>
          </div>
          <template #overlay>
            <a-menu class="crm-dropdown-menu">
              <a-menu-item key="profile">
                <router-link to="/admin/profile">My Profile</router-link>
              </a-menu-item>
              <a-menu-item key="timesheets">
                <router-link to="/admin/timesheets">My Timesheets</router-link>
              </a-menu-item>
              <a-menu-item key="edit-profile">
                <router-link to="/admin/profile/edit">Edit Profile</router-link>
              </a-menu-item>
              <a-sub-menu key="language" title="Language">
                <a-menu-item key="lang-en" @click="changeLanguage('en')">English</a-menu-item>
                <a-menu-item key="lang-es" @click="changeLanguage('es')">Spanish</a-menu-item>
                <a-menu-item key="lang-fr" @click="changeLanguage('fr')">French</a-menu-item>
                <a-menu-item key="lang-de" @click="changeLanguage('de')">German</a-menu-item>
              </a-sub-menu>
              <a-menu-divider />
              <a-menu-item key="logout" @click="handleLogout">
                <a>Logout</a>
              </a-menu-item>
            </a-menu>
          </template>
        </a-dropdown>
      </div>

      <!-- ========== MAIN NAVIGATION ========== -->
      <nav v-if="!setupMode" class="crm-sidebar__nav">
        <template v-for="item in menuItems" :key="item.name">
          <!-- Setup item — special click handler -->
          <a
            v-if="item.name === 'Setup' && !item.children"
            @click.prevent="enterSetupMode"
            :class="['crm-nav-item', { 'crm-nav-item--active': $route.path.startsWith('/admin/setup') }]"
          >
            <span class="crm-nav-icon" v-html="item.icon"></span>
            <span v-if="!sidebarCollapsed" class="crm-nav-label">{{ item.name }}</span>
          </a>

          <!-- Single link item (non-Setup) -->
          <router-link
            v-else-if="!item.children"
            :to="item.path"
            custom
            v-slot="{ navigate, isActive }"
          >
            <a
              @click="navigate"
              :class="['crm-nav-item', { 'crm-nav-item--active': isActive }]"
            >
              <span class="crm-nav-icon" v-html="item.icon"></span>
              <span v-if="!sidebarCollapsed" class="crm-nav-label">{{ item.name }}</span>
            </a>
          </router-link>

          <!-- Expandable group -->
          <div v-else class="crm-nav-group">
            <button
              :class="['crm-nav-item crm-nav-item--group', { 'crm-nav-item--expanded': expandedGroups[item.name] }]"
              @click="toggleGroup(item.name)"
            >
              <span class="crm-nav-icon" v-html="item.icon"></span>
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
                custom
                v-slot="{ navigate, isActive }"
              >
                <a
                  @click="navigate"
                  :class="['crm-submenu-item', { 'crm-submenu-item--active': isActive }]"
                >
                  {{ sub.name }}
                </a>
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

          <div class="crm-header-actions">
            <!-- Share icon -->
            <button class="crm-action-btn" title="Share">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
              </svg>
            </button>

            <!-- Tasks badge → My Todos -->
            <a-badge :count="2" :offset="[6, -2]" color="#0d6efd">
              <router-link to="/admin/my-todos" class="crm-action-btn" title="My To Do's">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9 11l3 3L22 4"/>
                  <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                </svg>
              </router-link>
            </a-badge>

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
              <a-badge :count="1" :offset="[6, -2]" color="#e11d48">
                <button class="crm-action-btn" title="Notifications">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 01-3.46 0"/>
                  </svg>
                </button>
              </a-badge>
              <template #overlay>
                <div class="header-timer-card notif-card" @click.stop>
                  <div class="notif-card-head">
                    <span class="notif-card-title">Notifications</span>
                    <button class="notif-mark-read" @click="markNotifsRead">Mark all as read</button>
                  </div>
                  <div class="notif-item">
                    <div class="notif-dot"></div>
                    <div class="notif-body">
                      <p class="notif-text">Contract with subject <strong>There was a dead silence. Alice noticed with some surprise.</strong> has been signed by the customer</p>
                      <span class="notif-time">3 hrs ago</span>
                    </div>
                  </div>
                  <div class="timer-card-footer">
                    <router-link to="/admin/notifications" class="view-all-link" @click="notifDropdownOpen = false">View all notifications</router-link>
                  </div>
                </div>
              </template>
            </a-dropdown>

            <!-- Avatar / Logout -->
            <a-dropdown :trigger="['click']">
              <img
                v-if="user?.profile_image"
                :src="getProfileImageUrl(user.profile_image)"
                :alt="user.name"
                class="crm-header-avatar"
              />
              <img
                v-else
                src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                alt="Profile"
                class="crm-header-avatar"
              />
              <template #overlay>
                <a-menu class="crm-dropdown-menu">
                  <a-menu-item key="profile">
                    <router-link to="/admin/profile">My Profile</router-link>
                  </a-menu-item>
                  <a-menu-item key="timesheets">
                    <router-link to="/admin/timesheets">My Timesheets</router-link>
                  </a-menu-item>
                  <a-menu-item key="edit-profile">
                    <router-link to="/admin/profile/edit">Edit Profile</router-link>
                  </a-menu-item>
                  <a-sub-menu key="language" title="Language">
                    <a-menu-item key="lang-en" @click="changeLanguage('en')">English</a-menu-item>
                    <a-menu-item key="lang-es" @click="changeLanguage('es')">Spanish</a-menu-item>
                    <a-menu-item key="lang-fr" @click="changeLanguage('fr')">French</a-menu-item>
                    <a-menu-item key="lang-de" @click="changeLanguage('de')">German</a-menu-item>
                  </a-sub-menu>
                  <a-menu-divider />
                  <a-menu-item key="logout" @click="handleLogout">
                    <a>Logout</a>
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
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../store/authStore';
import { message } from 'ant-design-vue';
import axios from 'axios';
import logoUrl from '../assets/logo.png';

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

export default defineComponent({
  name: 'AdminLayout',
  setup() {
    const router = useRouter();
    const route = useRoute();
    const authStore = useAuthStore();
    const sidebarCollapsed = ref(false);
    const setupMode = ref(false);

    const expandedGroups = reactive({
      Sales: false,
      Utilities: false,
      Reports: false,
    });

    const user = computed(() => authStore.user);

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
      { name: 'Setup', path: '/admin/setup', icon: icons.setup },
    ];

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

    const resolvedLogoUrl = computed(() => {
      if (logoUrl && logoUrl.startsWith('/')) {
        const basePath = window.config?.path?.replace(/\/$/, '') || '';
        return basePath + logoUrl;
      }
      return logoUrl;
    });

    // ── Notifications ──────────────────────────────────────────────
    const notifDropdownOpen = ref(false);
    const markNotifsRead = () => { message.success('All notifications marked as read'); };

    const getProfileImageUrl = (imagePath) => {
      if (!imagePath) return '';
      const basePath = window.config?.path?.replace(/\/$/, '') || '';
      if (imagePath.startsWith('http') || imagePath.startsWith('/')) {
        return imagePath;
      }
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
      if (headerTimerInterval) clearInterval(headerTimerInterval);
      headerTimerInterval = null;
      headerTimer.seconds = 0;
      headerTimer.startTime = null;
      timerDropdownOpen.value = false;
    };

    return {
      sidebarCollapsed, expandedGroups, menuItems, toggleSidebar, toggleGroup,
      handleLogout, user, resolvedLogoUrl, getProfileImageUrl, goToSeo,
      // Setup mode
      setupMode, setupSections, setupExpandedGroups,
      setupActiveSection, setupActiveSubSection,
      enterSetupMode, exitSetupMode, onSetupNavClick, onSetupChildClick,
      changeLanguage,
      // Header timer
      timerDropdownOpen, headerTimer, headerTimerStartStr,
      formatHeaderDuration, startHeaderTimer, stopHeaderTimer,
      // Notifications
      notifDropdownOpen, markNotifsRead,
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
  overflow: hidden;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  font-size: 14px;
  color: #334155;
  background: #f8fafc;
}

/* ── Sidebar ──────────────────────────────────────────────────────── */
.crm-sidebar {
  width: 240px;
  min-width: 240px;
  background: linear-gradient(135deg, #d35400 0%, #7e1e8e 50%, #0b579f 100%);
  border-right: none;
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
  transition: width 0.25s ease, min-width 0.25s ease;
  flex-shrink: 0;
  z-index: 30;
}
.crm-sidebar--collapsed {
  width: 62px;
  min-width: 62px;
}

/* Logo */
.crm-sidebar__logo {
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 14px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
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
  background: rgba(255,255,255,0.06);
  border: none;
  cursor: pointer;
  color: rgba(255, 255, 255, 0.6);
  padding: 6px;
  display: flex;
  align-items: center;
  border-radius: 8px;
  flex-shrink: 0;
  transition: all 0.2s;
}
.crm-hamburger svg { width: 18px; height: 18px; }
.crm-hamburger:hover { color: #ffffff; background: rgba(255, 255, 255, 0.12); }

/* Profile */
.crm-sidebar__profile {
  padding: 14px 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  flex-shrink: 0;
}
.crm-profile-card {
  display: flex;
  align-items: center;
  gap: 12px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  padding: 10px 14px;
  transition: background 0.2s;
}
.crm-profile-card:hover {
  background: rgba(255, 255, 255, 0.08);
}
.crm-profile-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
  border: 2px solid rgba(255, 255, 255, 0.2);
}
.crm-profile-info {
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.crm-profile-name {
  font-size: 14px;
  font-weight: 700;
  color: #ffffff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.3;
  letter-spacing: -0.1px;
}
.crm-profile-email {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.5);
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
  scrollbar-color: rgba(255, 255, 255, 0.1) transparent;
}
.crm-sidebar__nav::-webkit-scrollbar { width: 3px; }
.crm-sidebar__nav::-webkit-scrollbar-track { background: transparent; }
.crm-sidebar__nav::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 3px; }

/* Nav item */
.crm-nav-item {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 10px 14px;
  margin-bottom: 2px;
  gap: 12px;
  font-size: 14px;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.65);
  text-decoration: none;
  border: none;
  background: none;
  cursor: pointer;
  text-align: left;
  border-radius: 10px;
  position: relative;
  transition: all 0.18s;
  line-height: 1.5;
}
.crm-nav-item:hover {
  background: rgba(255, 255, 255, 0.06);
  color: rgba(255, 255, 255, 0.9);
}
.crm-nav-item--active {
  background: rgba(99, 102, 241, 0.15);
  color: #ffffff;
  font-weight: 600;
}
.crm-nav-item--active .crm-nav-icon :deep(svg) { color: #a5b4fc; stroke: #a5b4fc; }
.crm-nav-item--active .crm-nav-icon svg { color: #a5b4fc; stroke: #a5b4fc; }
.crm-nav-item--active::after {
  content: '';
  position: absolute;
  right: 8px;
  width: 4px;
  height: 20px;
  background: #6366f1;
  border-radius: 3px;
}

/* Icon */
.crm-nav-icon {
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: rgba(255, 255, 255, 0.5);
  transition: color 0.18s;
}
.crm-nav-item--active .crm-nav-icon { color: #a5b4fc; }
.crm-nav-item:hover .crm-nav-icon { color: rgba(255, 255, 255, 0.75); }
.crm-nav-icon :deep(svg) { width: 20px; height: 20px; }

/* Chevron */
.crm-nav-chevron {
  width: 16px;
  height: 16px;
  margin-left: auto;
  color: rgba(255, 255, 255, 0.4);
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
  background: rgba(0, 0, 0, 0.2);
  border-radius: 8px;
  margin: 2px 8px;
  overflow: hidden;
}
.crm-submenu-item {
  display: block;
  padding: 9px 14px 9px 46px;
  font-size: 13.5px;
  color: rgba(255, 255, 255, 0.6);
  text-decoration: none;
  cursor: pointer;
  transition: all 0.15s;
  border-radius: 6px;
  margin: 1px 4px;
}
.crm-submenu-item:hover {
  background: rgba(255, 255, 255, 0.06);
  color: #ffffff;
}
.crm-submenu-item--active {
  color: #ffffff;
  font-weight: 600;
  background: rgba(99, 102, 241, 0.12);
}

/* Sidebar Footer */
.crm-sidebar__footer {
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  padding: 14px 14px;
  flex-shrink: 0;
  background: rgba(0, 0, 0, 0.15);
}
.crm-pinned-project { display: flex; flex-direction: column; gap: 4px; }
.crm-pinned-name { font-size: 13px; font-weight: 600; color: #ffffff; }
.crm-pinned-client { font-size: 12px; color: rgba(255, 255, 255, 0.5); }
.crm-pinned-bar {
  width: 100%;
  height: 4px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 99px;
  margin-top: 6px;
  overflow: hidden;
}
.crm-pinned-bar__fill {
  height: 100%;
  background: #6366f1;
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
  background: linear-gradient(135deg, #d35400 0%, #7e1e8e 50%, #0b579f 100%);
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
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
  gap: 4px;
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
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 0 14px;
  gap: 8px;
  max-width: 300px;
  flex: 1;
  transition: all 0.2s;
}
.crm-search:focus-within {
  background: #fff;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}
.crm-search__icon {
  width: 16px;
  height: 16px;
  color: #94a3b8;
  flex-shrink: 0;
}
.crm-search__input {
  border: none;
  background: none;
  outline: none;
  font-size: 14px;
  color: #1e293b;
  width: 100%;
  padding: 8px 0;
  font-family: inherit;
}
.crm-search__input::placeholder { color: #94a3b8; }

/* Quick Create */
.crm-quick-create {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: rgba(255,255,255,0.15);
  border: 1px solid rgba(255,255,255,0.2);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  flex-shrink: 0;
  transition: all 0.2s;
}
.crm-quick-create:hover { background: rgba(255,255,255,0.25); }
.crm-quick-create svg { width: 15px; height: 15px; }

/* Header links */
.crm-header-link {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  padding: 6px 12px;
  border-radius: 8px;
  white-space: nowrap;
  transition: all 0.15s;
  font-weight: 500;
}
.crm-header-link:hover { color: #ffffff; background: rgba(255,255,255,0.1); }
@media (max-width: 900px) { .crm-header-link { display: none; } }

/* Header Actions */
.crm-header-actions {
  display: flex;
  align-items: center;
  gap: 2px;
  border-left: 1px solid rgba(255, 255, 255, 0.15);
  padding-left: 14px;
  margin-left: 10px;
}
.crm-action-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: rgba(255, 255, 255, 0.6);
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  transition: all 0.15s;
}
.crm-action-btn svg { width: 18px; height: 18px; }
.crm-action-btn:hover { color: #ffffff; background: rgba(255,255,255,0.1); }
.crm-header-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  object-fit: cover;
  cursor: pointer;
  border: 2px solid rgba(255, 255, 255, 0.3);
  margin-left: 6px;
  transition: all 0.15s;
}
.crm-header-avatar:hover { border-color: #ffffff; }

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
.notif-card { min-width: 300px; }
.notif-card-head {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 10px; padding-bottom: 8px;
  border-bottom: 1px solid #f1f5f9;
}
.notif-card-title { font-size: 13px; font-weight: 700; color: #0f172a; }
.notif-mark-read {
  background: none; border: none; font-size: 10px; font-weight: 700;
  color: #6366f1; cursor: pointer; font-family: inherit; padding: 0;
}
.notif-mark-read:hover { text-decoration: underline; }
.notif-item {
  display: flex; gap: 10px; padding: 8px 0;
  border-bottom: 1px solid #f8fafc;
}
.notif-item:last-of-type { border-bottom: none; }
.notif-dot {
  width: 7px; height: 7px; border-radius: 50%;
  background: #6366f1; flex-shrink: 0; margin-top: 5px;
}
.notif-body { flex: 1; min-width: 0; }
.notif-text { font-size: 11px; font-weight: 500; color: #334155; margin: 0; line-height: 1.4; }
.notif-text strong { font-weight: 700; color: #1e293b; }
.notif-time { font-size: 10px; font-weight: 600; color: #94a3b8; }

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
  background: #f8fafc;
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
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  margin-bottom: 4px;
  font-weight: 600 !important;
  color: #ffffff !important;
  opacity: 0.9;
  font-size: 14px !important;
}
.crm-nav-back:hover {
  opacity: 1;
  background: rgba(255, 255, 255, 0.08);
}

.crm-setup-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.08);
  margin: 4px 0;
}

.crm-setup-heading {
  padding: 12px 14px 8px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: rgba(255, 255, 255, 0.4);
}
</style>
