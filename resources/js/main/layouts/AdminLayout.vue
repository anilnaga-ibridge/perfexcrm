<template>
  <div class="crm-app-shell">
    <!-- Sidebar -->
    <aside :class="['crm-sidebar', { 'crm-sidebar--collapsed': sidebarCollapsed }]">
      <!-- Logo Header -->
      <div class="crm-sidebar__logo">
        <div class="crm-sidebar__logo-inner">
          <!-- Perfex "R" Logo SVG -->
          <svg class="crm-logo-icon" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M8 5h10a8 8 0 018 8 8 8 0 01-5 7.4L28 35H18l-6-13H8v13H0V5h8zm0 7v8h9a4 4 0 000-8H8z" fill="#2563eb"/>
            <path d="M30 5h10v30H30z" fill="#e11d48"/>
          </svg>
          <span v-if="!sidebarCollapsed" class="crm-logo-text">Perfex</span>
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
      <div v-if="!sidebarCollapsed" class="crm-sidebar__profile">
        <div class="crm-profile-card">
          <img
            src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
            alt="User Avatar"
            class="crm-profile-avatar"
          />
          <div class="crm-profile-info">
            <span class="crm-profile-name">{{ user?.name || 'Armando Turcotte' }}</span>
            <span class="crm-profile-email">{{ user?.email || 'admin@test.com' }}</span>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="crm-sidebar__nav">
        <template v-for="item in menuItems" :key="item.name">
          <!-- Single link item -->
          <router-link
            v-if="!item.children"
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

      <!-- Pinned Project Footer -->
      <div v-if="!sidebarCollapsed" class="crm-sidebar__footer">
        <div class="crm-pinned-project">
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
          <router-link to="/admin/setup" class="crm-header-link">Settings</router-link>

          <div class="crm-header-actions">
            <!-- Share icon -->
            <button class="crm-action-btn" title="Share">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
              </svg>
            </button>

            <!-- Tasks badge -->
            <a-badge :count="3" :offset="[6, -2]" color="#0d6efd">
              <button class="crm-action-btn" title="My Tasks">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9 11l3 3L22 4"/>
                  <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                </svg>
              </button>
            </a-badge>

            <!-- Timelog -->
            <button class="crm-action-btn" title="Time Logs">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
              </svg>
            </button>

            <!-- Notifications -->
            <a-badge :count="1" :offset="[6, -2]" color="#e11d48">
              <button class="crm-action-btn" title="Notifications">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                  <path d="M13.73 21a2 2 0 01-3.46 0"/>
                </svg>
              </button>
            </a-badge>

            <!-- Avatar / Logout -->
            <a-dropdown :trigger="['click']">
              <img
                src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                alt="Profile"
                class="crm-header-avatar"
              />
              <template #overlay>
                <a-menu>
                  <a-menu-item key="profile">My Profile</a-menu-item>
                  <a-menu-divider />
                  <a-menu-item key="logout" @click="handleLogout">Logout</a-menu-item>
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
import { defineComponent, ref, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../store/authStore';
import { message } from 'ant-design-vue';

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
  setup: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>`,
};

export default defineComponent({
  name: 'AdminLayout',
  setup() {
    const router = useRouter();
    const authStore = useAuthStore();
    const sidebarCollapsed = ref(false);

    const expandedGroups = reactive({
      Sales: false,
      Utilities: false,
      Reports: false,
    });

    const user = computed(() => authStore.user);

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
          { name: 'Media Library',   path: '/admin/media' },
          { name: 'Calendar',        path: '/admin/calendar' },
          { name: 'Announcements',   path: '/admin/announcements' },
          { name: 'Activity Log',    path: '/admin/activity' },
          { name: 'Goals',           path: '/admin/goals' },
        ],
      },
      {
        name: 'Reports', icon: icons.reports,
        children: [
          { name: 'Sales',           path: '/admin/reports/sales' },
          { name: 'Expenses',        path: '/admin/reports/expenses' },
          { name: 'Timesheets',      path: '/admin/reports/timesheets' },
          { name: 'Finance Report',  path: '/admin/reports/finance' },
          { name: 'Team Report',     path: '/admin/reports/team' },
        ],
      },
      { name: 'Setup', path: '/admin/setup', icon: icons.setup },
    ];

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

    return { sidebarCollapsed, expandedGroups, menuItems, toggleSidebar, toggleGroup, handleLogout, user };
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
  color: #475569;
  background: #f1f5f9;
}

/* ── Sidebar ──────────────────────────────────────────────────────── */
.crm-sidebar {
  width: 210px;
  min-width: 210px;
  background: #fff;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
  transition: width 0.25s ease, min-width 0.25s ease;
  flex-shrink: 0;
  z-index: 30;
}
.crm-sidebar--collapsed {
  width: 58px;
  min-width: 58px;
}

/* Logo */
.crm-sidebar__logo {
  height: 57px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 12px;
  border-bottom: 1px solid #e2e8f0;
  flex-shrink: 0;
}
.crm-sidebar__logo-inner {
  display: flex;
  align-items: center;
  gap: 8px;
  overflow: hidden;
}
.crm-logo-icon {
  width: 32px;
  height: 32px;
  flex-shrink: 0;
}
.crm-logo-text {
  font-size: 18px;
  font-weight: 700;
  color: #1e293b;
  white-space: nowrap;
  letter-spacing: -0.3px;
}
.crm-hamburger {
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  padding: 4px;
  display: flex;
  align-items: center;
  border-radius: 4px;
  flex-shrink: 0;
}
.crm-hamburger svg { width: 20px; height: 20px; }
.crm-hamburger:hover { color: #475569; background: #f1f5f9; }

/* Profile */
.crm-sidebar__profile {
  padding: 10px 10px;
  border-bottom: 1px solid #e2e8f0;
  flex-shrink: 0;
}
.crm-profile-card {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 8px 10px;
}
.crm-profile-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
  border: 1px solid #e2e8f0;
}
.crm-profile-info {
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.crm-profile-name {
  font-size: 12px;
  font-weight: 700;
  color: #1e293b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.3;
}
.crm-profile-email {
  font-size: 10px;
  color: #94a3b8;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.3;
  margin-top: 1px;
}

/* Navigation */
.crm-sidebar__nav {
  flex: 1;
  overflow-y: auto;
  padding: 6px 0;
  scrollbar-width: thin;
  scrollbar-color: #e2e8f0 transparent;
}
.crm-sidebar__nav::-webkit-scrollbar { width: 3px; }
.crm-sidebar__nav::-webkit-scrollbar-track { background: transparent; }
.crm-sidebar__nav::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 3px; }

/* Nav item */
.crm-nav-item {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 7px 12px;
  gap: 10px;
  font-size: 13.5px;
  font-weight: 500;
  color: #475569;
  text-decoration: none;
  border: none;
  background: none;
  cursor: pointer;
  text-align: left;
  border-left: 3px solid transparent;
  transition: background 0.12s, color 0.12s;
  border-radius: 0;
  line-height: 1.4;
}
.crm-nav-item:hover {
  background: #f1f5f9;
  color: #1e293b;
}
.crm-nav-item--active {
  background: #f8fafc;
  color: #1e293b;
  font-weight: 600;
  border-left-color: #0d6efd;
}
.crm-nav-item--active .crm-nav-icon :deep(svg) { color: #0d6efd; stroke: #0d6efd; }
.crm-nav-item--active .crm-nav-icon svg { color: #0d6efd; stroke: #0d6efd; }

/* Icon */
.crm-nav-icon {
  width: 17px;
  height: 17px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: #94a3b8;
}
.crm-nav-item--active .crm-nav-icon { color: #0d6efd; }
.crm-nav-item:hover .crm-nav-icon { color: #475569; }
.crm-nav-icon :deep(svg) { width: 17px; height: 17px; }

/* Chevron */
.crm-nav-chevron {
  width: 14px;
  height: 14px;
  margin-left: auto;
  color: #94a3b8;
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
  background: #f8fafc;
}
.crm-submenu-item {
  display: block;
  padding: 6px 12px 6px 40px;
  font-size: 13px;
  color: #64748b;
  text-decoration: none;
  cursor: pointer;
  transition: background 0.12s, color 0.12s;
}
.crm-submenu-item:hover {
  background: #f1f5f9;
  color: #1e293b;
}
.crm-submenu-item--active {
  color: #0d6efd;
  font-weight: 600;
}

/* Sidebar Footer */
.crm-sidebar__footer {
  border-top: 1px solid #e2e8f0;
  padding: 10px 12px;
  flex-shrink: 0;
}
.crm-pinned-project { display: flex; flex-direction: column; gap: 2px; }
.crm-pinned-name { font-size: 12px; font-weight: 600; color: #1e293b; }
.crm-pinned-client { font-size: 11px; color: #94a3b8; }
.crm-pinned-bar {
  width: 100%;
  height: 3px;
  background: #e2e8f0;
  border-radius: 99px;
  margin-top: 4px;
  overflow: hidden;
}
.crm-pinned-bar__fill {
  height: 100%;
  background: #0d6efd;
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
  height: 57px;
  background: #fff;
  border-bottom: 1px solid #e2e8f0;
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
  border-radius: 20px;
  padding: 0 12px;
  gap: 7px;
  max-width: 280px;
  flex: 1;
  transition: background 0.12s, border-color 0.12s;
}
.crm-search:focus-within {
  background: #fff;
  border-color: #0d6efd;
}
.crm-search__icon {
  width: 15px;
  height: 15px;
  color: #94a3b8;
  flex-shrink: 0;
}
.crm-search__input {
  border: none;
  background: none;
  outline: none;
  font-size: 13px;
  color: #475569;
  width: 100%;
  padding: 6px 0;
  font-family: inherit;
}
.crm-search__input::placeholder { color: #94a3b8; }

/* Quick Create */
.crm-quick-create {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: #0d6efd;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  flex-shrink: 0;
  transition: background 0.12s;
}
.crm-quick-create:hover { background: #0b5ed7; }
.crm-quick-create svg { width: 14px; height: 14px; }

/* Header links */
.crm-header-link {
  font-size: 13px;
  color: #475569;
  text-decoration: none;
  padding: 4px 8px;
  border-radius: 4px;
  white-space: nowrap;
  transition: color 0.12s;
}
.crm-header-link:hover { color: #0d6efd; }
@media (max-width: 900px) { .crm-header-link { display: none; } }

/* Header Actions */
.crm-header-actions {
  display: flex;
  align-items: center;
  gap: 4px;
  border-left: 1px solid #e2e8f0;
  padding-left: 12px;
  margin-left: 8px;
}
.crm-action-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: color 0.12s, background 0.12s;
}
.crm-action-btn svg { width: 17px; height: 17px; }
.crm-action-btn:hover { color: #475569; background: #f1f5f9; }
.crm-header-avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  object-fit: cover;
  cursor: pointer;
  border: 2px solid #e2e8f0;
  margin-left: 4px;
  transition: border-color 0.12s;
}
.crm-header-avatar:hover { border-color: #0d6efd; }

/* ── Demo Bar ─────────────────────────────────────────────────────── */
.crm-demo-bar {
  background: #f0fdf4;
  border-bottom: 1px solid #dcfce7;
  padding: 8px 20px;
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
  padding: 20px 24px;
  background: #f1f5f9;
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
</style>
