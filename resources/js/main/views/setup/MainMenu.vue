<template>
  <div class="main-menu-setup-page p-6 max-w-[1400px] mx-auto min-h-screen bg-[#F8F7FA] font-['Public_Sans',sans-serif]">
    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2">
          <span class="p-2 bg-[#7367F0]/10 text-[#7367F0] rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
          </span>
          <div>
            <h1 class="text-2xl font-bold text-[#4B465C] tracking-tight m-0">Main Menu Configuration</h1>
            <span class="text-xs text-[#82868B] font-medium">Customize sidebar navigation hierarchy, collapsible categories, and item visibility</span>
          </div>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <button 
          type="button" 
          class="px-3.5 py-2 bg-white border border-[#DBDADE] hover:bg-[#F8F7FA] text-[#4B465C] rounded-lg text-xs font-bold transition-all shadow-sm cursor-pointer"
          @click="toggleAll(false)"
        >
          Expand All
        </button>
        <button 
          type="button" 
          class="px-3.5 py-2 bg-white border border-[#DBDADE] hover:bg-[#F8F7FA] text-[#4B465C] rounded-lg text-xs font-bold transition-all shadow-sm cursor-pointer"
          @click="toggleAll(true)"
        >
          Collapse All
        </button>
        <button 
          type="button" 
          class="flex items-center gap-2 px-5 py-2 bg-[#7367F0] hover:bg-[#685dd8] text-white rounded-lg text-xs font-bold transition-all shadow-sm shadow-[#7367F0]/30 cursor-pointer border-none"
          :disabled="saving"
          @click="saveMenuSettings"
        >
          <svg v-if="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
          <div v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          {{ saving ? 'Saving Changes...' : 'Save Configuration' }}
        </button>
      </div>
    </div>

    <!-- HINT BANNER -->
    <div class="bg-white border border-[#EBE9F1] rounded-lg p-4 shadow-sm mb-6 flex items-center justify-between border-l-4 border-l-[#7367F0]">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-[#7367F0]/10 text-[#7367F0] flex items-center justify-center font-bold shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
          <h4 class="text-xs font-bold text-[#4B465C] m-0">Menu Visibility &amp; Ordering Controls</h4>
          <p class="text-xs text-[#82868B] m-0 mt-0.5">Toggle visibility switches to show or hide modules from staff members. All settings apply dynamically to the left sidebar navigation.</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button type="button" class="text-xs font-bold text-[#7367F0] hover:underline cursor-pointer bg-transparent border-none" @click="setAllVisibility(true)">Enable All</button>
        <span class="text-[#DBDADE]">|</span>
        <button type="button" class="text-xs font-bold text-[#82868B] hover:underline cursor-pointer bg-transparent border-none" @click="setAllVisibility(false)">Disable All</button>
      </div>
    </div>

    <!-- MAIN MENU LIST CONTAINER -->
    <div class="bg-white border border-[#EBE9F1] rounded-lg p-6 shadow-sm">
      <div class="space-y-3">
        <div 
          v-for="item in menuItems" 
          :key="item.key" 
          class="border border-[#EBE9F1] rounded-lg overflow-hidden transition-all hover:border-[#7367F0]/40 bg-white"
        >
          <!-- Parent Row -->
          <div class="flex items-center justify-between p-3.5 bg-white select-none" :class="{ 'bg-[#F8F7FA]/70': item.children }">
            <div class="flex items-center gap-3">
              <span class="text-sm text-[#82868B] font-mono cursor-grab hover:text-[#4B465C]" title="Drag to reorder">⠿</span>
              <div class="w-7 h-7 rounded bg-[#7367F0]/10 text-[#7367F0] flex items-center justify-center font-bold text-xs">
                <span>{{ item.iconText || item.label.charAt(0) }}</span>
              </div>
              <span class="text-xs font-bold text-[#4B465C]">{{ item.label }}</span>
              <span v-if="item.children" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-[#7367F0]/10 text-[#7367F0]">
                {{ item.children.length }} Sub-items
              </span>
            </div>

            <div class="flex items-center gap-3">
              <!-- Expand / Collapse Button -->
              <button
                v-if="item.children"
                type="button"
                class="px-2.5 py-1 text-[11px] font-bold bg-white border border-[#DBDADE] hover:bg-[#F8F7FA] text-[#4B465C] rounded transition-all cursor-pointer"
                @click="toggleCollapse(item)"
              >
                {{ item.collapsed ? 'Expand' : 'Collapse' }}
              </button>
              <a-switch v-model:checked="item.visible" size="small" />
            </div>
          </div>

          <!-- Sub-items Nested Container -->
          <div v-if="item.children && !item.collapsed" class="border-t border-[#EBE9F1] bg-[#F8F7FA]/40 divide-y divide-[#EBE9F1]">
            <div 
              v-for="sub in item.children" 
              :key="sub.key" 
              class="flex items-center justify-between py-2.5 px-4 pl-12 hover:bg-white transition-colors"
            >
              <div class="flex items-center gap-3">
                <span class="text-xs text-[#82868B] font-mono cursor-grab" title="Drag to reorder">⠿</span>
                <span class="w-1.5 h-1.5 rounded-full bg-[#7367F0]"></span>
                <span class="text-xs font-semibold text-[#4B465C]">{{ sub.label }}</span>
              </div>
              <div class="flex items-center gap-2">
                <a-switch v-model:checked="sub.visible" size="small" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Save Bottom Actions -->
      <div class="mt-6 pt-4 border-t border-[#EBE9F1] flex items-center justify-between">
        <span class="text-xs text-[#82868B]">Changes will take effect across the navigation sidebar immediately upon saving.</span>
        <button 
          type="button" 
          class="flex items-center gap-2 px-6 py-2.5 bg-[#7367F0] hover:bg-[#685dd8] text-white rounded-lg text-xs font-bold transition-all shadow-sm shadow-[#7367F0]/30 cursor-pointer border-none"
          :disabled="saving"
          @click="saveMenuSettings"
        >
          <svg v-if="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
          <div v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          {{ saving ? 'Saving Changes...' : 'Save Main Menu Configuration' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { message } from 'ant-design-vue'

const saving = ref(false)

const menuItems = ref([
  { key: 'dashboard', label: 'Dashboard', iconText: '📊', visible: true },
  { key: 'customers', label: 'Customers', iconText: '👥', visible: true },
  {
    key: 'sales',
    label: 'Sales & Billing',
    iconText: '💰',
    visible: true,
    collapsed: false,
    children: [
      { key: 'proposals', label: 'Proposals', visible: true },
      { key: 'estimates', label: 'Estimates', visible: true },
      { key: 'invoices', label: 'Invoices', visible: true },
      { key: 'payments', label: 'Payments', visible: true },
      { key: 'credit_notes', label: 'Credit Notes', visible: true },
      { key: 'items', label: 'Items & Products', visible: true },
      { key: 'subscriptions', label: 'Subscriptions', visible: true }
    ]
  },
  { key: 'expenses', label: 'Expenses', iconText: '💸', visible: true },
  { key: 'contracts', label: 'Contracts', iconText: '📜', visible: true },
  { key: 'projects', label: 'Projects', iconText: '📁', visible: true },
  { key: 'tasks', label: 'Tasks', iconText: '✓', visible: true },
  { key: 'support', label: 'Support & Tickets', iconText: '🎫', visible: true },
  { key: 'leads', label: 'Leads Management', iconText: '🔍', visible: true },
  { key: 'estimate_request', label: 'Estimate Requests', iconText: '📑', visible: true },
  { key: 'kb', label: 'Knowledge Base', iconText: '📚', visible: true },
  {
    key: 'utilities',
    label: 'Utilities',
    iconText: '🛠️',
    visible: true,
    collapsed: false,
    children: [
      { key: 'media', label: 'Media Library', visible: true },
      { key: 'bulk_exports', label: 'Data Export Center (3-in-1)', visible: true },
      { key: 'calendar', label: 'Calendar', visible: true },
      { key: 'announcements', label: 'Announcements', visible: true },
      { key: 'goals', label: 'Goals', visible: true },
      { key: 'activity_log', label: 'Activity Log', visible: true },
      { key: 'surveys', label: 'Surveys', visible: true },
      { key: 'db_backup', label: 'Database Backup', visible: true },
      { key: 'ticket_pipe', label: 'Ticket Pipe Log', visible: true }
    ]
  },
  {
    key: 'reports',
    label: 'Reports & Analytics',
    iconText: '📈',
    visible: true,
    collapsed: false,
    children: [
      { key: 'report_leads', label: 'Leads Report', visible: true },
      { key: 'report_sales', label: 'Sales Report', visible: true },
      { key: 'report_expenses', label: 'Expenses Report', visible: true },
      { key: 'report_exp_inc', label: 'Expenses vs Income', visible: true },
      { key: 'report_timesheets', label: 'Timesheets Overview', visible: true },
      { key: 'report_kb', label: 'KB Articles Report', visible: true },
      { key: 'report_team', label: 'Team Report', visible: true }
    ]
  }
])

const toggleCollapse = (item) => {
  item.collapsed = !item.collapsed
}

const toggleAll = (collapseState) => {
  menuItems.value.forEach(item => {
    if (item.children) {
      item.collapsed = collapseState
    }
  })
}

const setAllVisibility = (visibilityState) => {
  menuItems.value.forEach(item => {
    item.visible = visibilityState
    if (item.children) {
      item.children.forEach(sub => {
        sub.visible = visibilityState
      })
    }
  })
}

const saveMenuSettings = () => {
  saving.value = true
  setTimeout(() => {
    saving.value = false
    try {
      localStorage.setItem('crm_main_menu_settings', JSON.stringify(menuItems.value))
    } catch (e) {}
    message.success('Main Menu Configuration saved successfully!')
  }, 500)
}
</script>

<style scoped>
:deep(.ant-switch-checked) {
  background-color: #7367F0 !important;
}
</style>
