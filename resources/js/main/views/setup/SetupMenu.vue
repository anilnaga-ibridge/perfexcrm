<template>
  <div class="settings-card">
    <div class="section-toolbar-sub">
      <p class="settings-hint-block">Configure which items are visible in the Setup sidebar. Drag to reorder.</p>
    </div>

    <!-- SETUP MENU LIST -->
    <div class="menu-items-list">
      <div v-for="item in menuItems" :key="item.key" class="menu-item-wrapper">
        
        <!-- Parent Group / Normal Item Header -->
        <div :class="['menu-setup-item', { 'parent-group': item.children }]">
          <div class="item-left">
            <span class="drag-handle">⠿</span>
            <span class="menu-item-label">{{ item.label }}</span>
          </div>
          
          <div class="item-right">
            <!-- Collapse / Expand Button for Collapsible Groups -->
            <button
              v-if="item.children"
              type="button"
              class="btn-collapse-expand"
              @click="toggleCollapse(item)"
            >
              {{ item.collapsed ? 'Expand' : 'Collapse' }}
            </button>
            <a-switch v-model:checked="item.visible" size="small" />
          </div>
        </div>

        <!-- Group Nested Sub-items -->
        <div v-if="item.children && !item.collapsed" class="sub-items-container">
          <div v-for="sub in item.children" :key="sub.key" class="menu-setup-item sub-item">
            <div class="item-left">
              <span class="drag-handle">⠿</span>
              <span class="menu-item-label sub-label">{{ sub.label }}</span>
            </div>
            <div class="item-right">
              <a-switch v-model:checked="sub.visible" size="small" />
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Save Actions -->
    <div class="settings-actions">
      <a-button type="primary" :loading="saving" @click="saveMenuSettings">
        Save Setup Menu
      </a-button>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref } from 'vue';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'SetupMenuView',
  setup() {
    const saving = ref(false);
    
    const menuItems = ref([
      { key: 'staff', label: 'Staff', visible: true },
      {
        key: 'customers',
        label: 'Customers',
        visible: true,
        collapsed: false,
        children: [
          { key: 'groups', label: 'Groups', visible: true }
        ]
      },
      {
        key: 'support',
        label: 'Support',
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
        label: 'Leads',
        visible: true,
        collapsed: false,
        children: [
          { key: 'sources', label: 'Sources', visible: true },
          { key: 'statuses', label: 'Statuses', visible: true },
          { key: 'email_integration', label: 'Email Integration', visible: true },
          { key: 'web_to_lead', label: 'Web to Lead', visible: true }
        ]
      },
      {
        key: 'finance',
        label: 'Finance',
        visible: true,
        collapsed: false,
        children: [
          { key: 'tax_rates', label: 'Tax Rates', visible: true },
          { key: 'currencies', label: 'Currencies', visible: true },
          { key: 'payment_modes', label: 'Payment Modes', visible: true },
          { key: 'expenses_categories', label: 'Expenses Categories', visible: true }
        ]
      },
      {
        key: 'contracts',
        label: 'Contracts',
        visible: true,
        collapsed: false,
        children: [
          { key: 'contract_types', label: 'Contract Types', visible: true }
        ]
      },
      {
        key: 'estimate_request',
        label: 'Estimate Request',
        visible: true,
        collapsed: false,
        children: [
          { key: 'forms', label: 'Forms', visible: true },
          { key: 'statuses', label: 'Statuses', visible: true }
        ]
      },
      { key: 'modules', label: 'Modules', visible: true },
      { key: 'email_templates', label: 'Email Templates', visible: true },
      { key: 'custom_fields', label: 'Custom Fields', visible: true },
      { key: 'gdpr', label: 'GDPR', visible: true },
      { key: 'roles', label: 'Roles', visible: true },
      {
        key: 'menu_setup',
        label: 'Menu Setup',
        visible: true,
        collapsed: false,
        children: [
          { key: 'main_menu', label: 'Main Menu', visible: true },
          { key: 'setup_menu', label: 'Setup Menu', visible: true }
        ]
      },
      { key: 'theme_style', label: 'Theme Style', visible: true },
      { key: 'settings', label: 'Settings', visible: true }
    ]);

    const toggleCollapse = (item) => {
      item.collapsed = !item.collapsed;
    };

    const saveMenuSettings = () => {
      saving.value = true;
      setTimeout(() => {
        saving.value = false;
        message.success('Setup Menu settings saved successfully');
      }, 500);
    };

    return {
      saving,
      menuItems,
      toggleCollapse,
      saveMenuSettings
    };
  }
});
</script>

<style scoped>
.settings-card {
  background: #fff;
  border-radius: 12px;
  border: 1px solid #f1f5f9;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.settings-hint-block {
  font-size: 13px;
  color: #64748b;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 12px 16px;
  margin-bottom: 20px;
}
.menu-items-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.menu-item-wrapper {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.menu-setup-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 16px;
  border-radius: 8px;
  background: #fff;
  border: 1px solid #e2e8f0;
  transition: all 0.15s ease;
}
.menu-setup-item:hover {
  border-color: #cbd5e1;
  background: #f8fafc;
}
.parent-group {
  background: #f1f5f9;
  border-color: #cbd5e1;
}
.parent-group .menu-item-label {
  font-weight: 700;
  color: #0f172a;
}
.sub-items-container {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-left: 28px;
  padding-left: 12px;
  border-left: 2px dashed #cbd5e1;
}
.sub-item {
  background: #fff;
}
.sub-label {
  color: #475569;
}
.item-left {
  display: flex;
  align-items: center;
  gap: 12px;
}
.item-right {
  display: flex;
  align-items: center;
  gap: 12px;
}
.drag-handle {
  color: #cbd5e1;
  font-size: 16px;
  cursor: grab;
  user-select: none;
}
.menu-item-label {
  font-size: 13.5px;
  color: #334155;
}
.btn-collapse-expand {
  font-size: 12px;
  font-weight: 600;
  color: #4f46e5;
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.15s;
}
.btn-collapse-expand:hover {
  background: #e0e7ff;
  color: #4338ca;
}
.settings-actions {
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
}
</style>
