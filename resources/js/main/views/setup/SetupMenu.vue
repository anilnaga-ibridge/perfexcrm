<template>
  <div class="setup-menu-page p-6 max-w-[1400px] mx-auto min-h-screen bg-[#F8F7FA] font-['Public_Sans',sans-serif]">
    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2">
          <span class="p-2 bg-[#7367F0]/10 text-[#7367F0] rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
          </span>
          <div>
            <h1 class="text-2xl font-bold text-[#4B465C] tracking-tight m-0">Setup Menu Configuration</h1>
            <span class="text-xs text-[#82868B] font-medium">Configure administrative setup navigation items, collapsible categories, and visibility</span>
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
          <h4 class="text-xs font-bold text-[#4B465C] m-0">Setup Sidebar Visibility Controls</h4>
          <p class="text-xs text-[#82868B] m-0 mt-0.5">Toggle visibility switches for setup administrative sections. Hidden sections will not be shown to non-admin staff in the setup drawer.</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <button type="button" class="text-xs font-bold text-[#7367F0] hover:underline cursor-pointer bg-transparent border-none" @click="setAllVisibility(true)">Enable All</button>
        <span class="text-[#DBDADE]">|</span>
        <button type="button" class="text-xs font-bold text-[#82868B] hover:underline cursor-pointer bg-transparent border-none" @click="setAllVisibility(false)">Disable All</button>
      </div>
    </div>

    <!-- MAIN SETTINGS CARD -->
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
        <span class="text-xs text-[#82868B]">Changes will take effect across the setup menu navigation immediately upon saving.</span>
        <button 
          type="button" 
          class="flex items-center gap-2 px-6 py-2.5 bg-[#7367F0] hover:bg-[#685dd8] text-white rounded-lg text-xs font-bold transition-all shadow-sm shadow-[#7367F0]/30 cursor-pointer border-none"
          :disabled="saving"
          @click="saveMenuSettings"
        >
          <svg v-if="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
          <div v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          {{ saving ? 'Saving Changes...' : 'Save Setup Menu Configuration' }}
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
  { key: 'staff', label: 'Staff Members', iconText: '👤', visible: true },
  {
    key: 'customers',
    label: 'Customers & Groups',
    iconText: '👥',
    visible: true,
    collapsed: false,
    children: [
      { key: 'groups', label: 'Customer Groups', visible: true }
    ]
  },
  {
    key: 'support',
    label: 'Support & Tickets',
    iconText: '🎫',
    visible: true,
    collapsed: false,
    children: [
      { key: 'departments', label: 'Departments', visible: true },
      { key: 'predefined_replies', label: 'Predefined Replies', visible: true },
      { key: 'ticket_priority', label: 'Ticket Priority', visible: true },
      { key: 'ticket_statuses', label: 'Ticket Statuses', visible: true },
      { key: 'services', label: 'Services', visible: true },
      { key: 'spam_filters', label: 'Spam Filters', visible: true }
    ]
  },
  {
    key: 'leads',
    label: 'Leads Configuration',
    iconText: '🔍',
    visible: true,
    collapsed: false,
    children: [
      { key: 'sources', label: 'Lead Sources', visible: true },
      { key: 'statuses', label: 'Lead Statuses', visible: true },
      { key: 'email_integration', label: 'Email Integration', visible: true },
      { key: 'web_to_lead', label: 'Web to Lead Forms', visible: true }
    ]
  },
  {
    key: 'finance',
    label: 'Finance & Tax Settings',
    iconText: '💳',
    visible: true,
    collapsed: false,
    children: [
      { key: 'tax_rates', label: 'Tax Rates', visible: true },
      { key: 'currencies', label: 'Currencies', visible: true },
      { key: 'payment_modes', label: 'Payment Modes', visible: true },
      { key: 'expenses_categories', label: 'Expense Categories', visible: true }
    ]
  },
  {
    key: 'contracts',
    label: 'Contracts',
    iconText: '📜',
    visible: true,
    collapsed: false,
    children: [
      { key: 'contract_types', label: 'Contract Types', visible: true }
    ]
  },
  {
    key: 'estimate_request',
    label: 'Estimate Requests',
    iconText: '📑',
    visible: true,
    collapsed: false,
    children: [
      { key: 'forms', label: 'Request Forms', visible: true },
      { key: 'statuses', label: 'Request Statuses', visible: true }
    ]
  },
  { key: 'plugins', label: 'Plugins & Modules', iconText: '🧩', visible: true },
  { key: 'email_templates', label: 'Email Templates', iconText: '✉️', visible: true },
  { key: 'custom_fields', label: 'Custom Fields', iconText: '📝', visible: true },
  { key: 'gdpr', label: 'GDPR Compliance', iconText: '🛡️', visible: true },
  { key: 'roles', label: 'Roles & Permissions', iconText: '🔑', visible: true },
  {
    key: 'menu_setup',
    label: 'Menu Setup',
    iconText: '☰',
    visible: true,
    collapsed: false,
    children: [
      { key: 'main_menu', label: 'Main Menu', visible: true },
      { key: 'setup_menu', label: 'Setup Menu', visible: true }
    ]
  },
  { key: 'theme_style', label: 'Theme Style & Branding', iconText: '🎨', visible: true },
  { key: 'settings', label: 'System Settings', iconText: '⚙️', visible: true }
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
      localStorage.setItem('crm_setup_menu_settings', JSON.stringify(menuItems.value))
    } catch (e) {}
    message.success('Setup Menu Configuration saved successfully!')
  }, 500)
}
</script>

<style scoped>
:deep(.ant-switch-checked) {
  background-color: #7367F0 !important;
}
</style>
