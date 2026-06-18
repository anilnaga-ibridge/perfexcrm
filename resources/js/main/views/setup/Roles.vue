<template>
  <div>
    <!-- HEADER & TOOLBAR -->
    <div class="section-toolbar">
      <h2 class="section-title">Roles & Permissions</h2>
      <button class="btn-primary" @click="openNewRoleDrawer">Add New Role</button>
    </div>

    <!-- MAIN CARD / DATA TABLE -->
    <div class="settings-card">
      <div class="data-table-wrap">
        <div class="data-table-controls">
          <div class="page-size-select">
            <a-select v-model:value="pageSize" size="small" style="width: 70px">
              <a-select-option :value="10">10</a-select-option>
              <a-select-option :value="25">25</a-select-option>
              <a-select-option :value="50">50</a-select-option>
            </a-select>
          </div>
          <a-input-search
            v-model:value="search"
            placeholder="Search..."
            style="width: 280px"
            size="small"
          />
        </div>

        <a-table
          :dataSource="filteredRoles"
          :columns="roleColumns"
          :pagination="{ pageSize: pageSize, total: filteredRoles.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
          row-key="id"
          size="small"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'name'">
              <div class="role-cell">
                <span class="role-name-text">{{ record.name }}</span>
                <span class="role-users-count">Total Users: {{ record.users_count || 0 }}</span>
              </div>
            </template>
            <template v-if="column.key === 'options'">
              <div class="row-actions">
                <a-button size="small" type="link" @click="editRole(record)">Edit</a-button>
                <a-button size="small" type="link" danger @click="deleteRole(record.id)">Delete</a-button>
              </div>
            </template>
          </template>
        </a-table>
      </div>
    </div>

    <!-- Add/Edit Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      :title="editingId ? 'Edit Role' : 'Add New Role'"
      placement="right"
      :width="780"
      @close="resetForm"
    >
      <a-form layout="vertical" :model="form" @finish="saveRole">
        <a-form-item label="* Role Name" name="name" :rules="[{ required: true, message: 'Role name is required' }]">
          <a-input v-model:value="form.name" placeholder="Enter role name" />
        </a-form-item>

        <!-- Permissions Grid Table -->
        <div class="permissions-section">
          <h4 class="permissions-title">Permissions</h4>
          
          <table class="permissions-table">
            <thead>
              <tr>
                <th style="width: 200px;">features</th>
                <th>Capabilities</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="feat in featuresList" :key="feat.name">
                <td class="feature-col">
                  <strong>{{ feat.name }}</strong>
                </td>
                <td class="caps-col">
                  <div class="checkbox-group">
                    <a-checkbox
                      v-for="cap in feat.caps"
                      :key="cap"
                      :checked="!!form.permissions[feat.name]?.[cap]"
                      @change="(e) => setPermission(feat.name, cap, e.target.checked)"
                    >
                      {{ getCapLabel(cap) }}
                    </a-checkbox>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="drawer-footer">
          <a-button @click="openDrawer = false" style="margin-right: 8px;">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">
            {{ editingId ? 'Update Role' : 'Save' }}
          </a-button>
        </div>
      </a-form>
    </a-drawer>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed } from 'vue';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'RolesView',
  setup() {
    const search = ref('');
    const pageSize = ref(25);
    const openDrawer = ref(false);
    const saving = ref(false);
    const editingId = ref(null);

    const rolesList = ref([
      {
        id: 1,
        name: 'Employee',
        users_count: 0,
        permissions: {
          'Bulk PDF Export': { view_global: true },
          'Contracts': { view_own: true, view_global: true, create: true },
          'Tasks': { view_own: true, view_global: true, create: true, edit: true }
        }
      }
    ]);

    const form = reactive({
      name: '',
      permissions: {}
    });

    const featuresList = [
      { name: 'Bulk PDF Export', caps: ['view_global'] },
      { name: 'Contracts', caps: ['view_own', 'view_global', 'create', 'edit', 'delete', 'view_all_templates'] },
      { name: 'Credit Notes', caps: ['view_own', 'view_global', 'create', 'edit', 'delete'] },
      { name: 'Customers', caps: ['view_own', 'view_global', 'create', 'edit', 'delete'] },
      { name: 'Email Templates', caps: ['view_global', 'edit'] },
      { name: 'Estimates', caps: ['view_own', 'view_global', 'create', 'edit', 'delete'] },
      { name: 'Expenses', caps: ['view_own', 'view_global', 'create', 'edit', 'delete'] },
      { name: 'Invoices', caps: ['view_own', 'view_global', 'create', 'edit', 'delete'] },
      { name: 'Items', caps: ['view_global', 'create', 'edit', 'delete'] },
      { name: 'Knowledge Base', caps: ['view_global', 'create', 'edit', 'delete'] },
      { name: 'Payments', caps: ['view_own', 'view_global', 'create', 'edit', 'delete'] },
      { name: 'Projects', caps: ['view_own', 'view_global', 'create', 'edit', 'delete', 'create_timesheets', 'edit_milestones', 'delete_milestones'] },
      { name: 'Proposals', caps: ['view_own', 'view_global', 'create', 'edit', 'delete', 'view_all_templates'] },
      { name: 'Reports', caps: ['view_global', 'view_timesheets_report'] },
      { name: 'Staff Roles', caps: ['view_global', 'create', 'edit', 'delete'] },
      { name: 'Settings', caps: ['view_global', 'edit'] },
      { name: 'Staff', caps: ['view_global', 'create', 'edit', 'delete'] },
      { name: 'Subscriptions', caps: ['view_own', 'view_global', 'create', 'edit', 'delete'] },
      { name: 'Tasks', caps: ['view_own', 'view_global', 'create', 'edit', 'delete', 'edit_timesheets_global', 'edit_own_timesheets', 'delete_timesheets_global', 'delete_own_timesheets'] },
      { name: 'Task Checklist Templates', caps: ['create', 'delete'] },
      { name: 'Estimate Request', caps: ['view_own', 'view_global', 'create', 'edit', 'delete'] },
      { name: 'Leads', caps: ['view_global', 'delete'] },
      { name: 'Surveys', caps: ['view_global', 'create', 'edit', 'delete'] },
      { name: 'e-Invoice', caps: ['bulk_export'] },
      { name: 'Goals', caps: ['view_global', 'create', 'edit', 'delete'] }
    ];

    const capLabels = {
      view_own: 'View (Own)',
      view_global: 'View (Global)',
      create: 'Create',
      edit: 'Edit',
      delete: 'Delete',
      view_all_templates: 'View All Templates',
      create_timesheets: 'Create Timesheets',
      edit_milestones: 'Edit Milestones',
      delete_milestones: 'Delete Milestones',
      view_timesheets_report: 'View Timesheets Report',
      edit_timesheets_global: 'Edit Timesheets (Global)',
      edit_own_timesheets: 'Edit Own Timesheets',
      delete_timesheets_global: 'Delete Timesheets (Global)',
      delete_own_timesheets: 'Delete own Timesheets',
      bulk_export: 'Bulk export'
    };

    const getCapLabel = (cap) => capLabels[cap] || cap;

    const roleColumns = [
      { title: 'Role Name', key: 'name', dataIndex: 'name' },
      { title: 'Options', key: 'options', width: 140 }
    ];

    const filteredRoles = computed(() => {
      const sorted = [...rolesList.value].sort((a, b) => b.id - a.id);
      if (!search.value) return sorted;
      const q = search.value.toLowerCase();
      return sorted.filter(r => r.name.toLowerCase().includes(q));
    });

    const openNewRoleDrawer = () => {
      resetForm();
      openDrawer.value = true;
    };

    const editRole = (record) => {
      editingId.value = record.id;
      form.name = record.name;
      form.permissions = JSON.parse(JSON.stringify(record.permissions || {}));
      openDrawer.value = true;
    };

    const deleteRole = (id) => {
      rolesList.value = rolesList.value.filter(r => r.id !== id);
      message.success('Role deleted successfully');
    };

    const setPermission = (feature, cap, checked) => {
      if (!form.permissions[feature]) {
        form.permissions[feature] = {};
      }
      form.permissions[feature][cap] = checked;
    };

    const saveRole = () => {
      if (!form.name.trim()) return;
      saving.value = true;

      try {
        if (editingId.value) {
          const item = rolesList.value.find(r => r.id === editingId.value);
          if (item) {
            item.name = form.name.trim();
            item.permissions = JSON.parse(JSON.stringify(form.permissions));
          }
          message.success('Role updated successfully');
        } else {
          const maxId = rolesList.value.reduce((max, r) => r.id > max ? r.id : max, 0);
          rolesList.value.push({
            id: maxId + 1,
            name: form.name.trim(),
            users_count: 0,
            permissions: JSON.parse(JSON.stringify(form.permissions))
          });
          message.success('Role added successfully');
        }
        openDrawer.value = false;
        resetForm();
      } catch (e) {
        message.error('Error saving role');
      } finally {
        saving.value = false;
      }
    };

    const resetForm = () => {
      editingId.value = null;
      form.name = '';
      form.permissions = {};
      // Initialize permissions object with false/empty states for all features
      featuresList.forEach(f => {
        form.permissions[f.name] = {};
        f.caps.forEach(c => {
          form.permissions[f.name][c] = false;
        });
      });
    };

    return {
      search,
      pageSize,
      openDrawer,
      saving,
      editingId,
      rolesList,
      form,
      featuresList,
      getCapLabel,
      roleColumns,
      filteredRoles,
      openNewRoleDrawer,
      editRole,
      deleteRole,
      setPermission,
      saveRole,
      resetForm
    };
  }
});
</script>

<style scoped>
.section-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}
.section-title {
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 20px;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;
}
.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
}
.settings-card {
  background: #fff;
  border-radius: 12px;
  border: 1px solid #f1f5f9;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.data-table-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}
.role-cell {
  display: flex;
  flex-direction: column;
}
.role-name-text {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
}
.role-users-count {
  font-size: 12px;
  color: #64748b;
  margin-top: 2px;
}
.row-actions {
  display: flex;
  gap: 4px;
}
.permissions-section {
  margin-top: 20px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
}
.permissions-title {
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
  background: #f8fafc;
  padding: 12px 16px;
  margin: 0;
  border-bottom: 1px solid #e2e8f0;
}
.permissions-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.permissions-table th {
  background: #f1f5f9;
  color: #475569;
  font-weight: 600;
  text-align: left;
  padding: 10px 16px;
  border-bottom: 1px solid #e2e8f0;
}
.permissions-table td {
  padding: 12px 16px;
  border-bottom: 1px solid #e2e8f0;
  vertical-align: top;
}
.permissions-table tr:last-child td {
  border-bottom: none;
}
.feature-col {
  color: #1e293b;
}
.caps-col {
  padding-left: 24px !important;
}
.checkbox-group {
  display: flex;
  flex-wrap: wrap;
  gap: 12px 16px;
}
.drawer-footer {
  display: flex;
  justify-content: flex-end;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  margin-top: 24px;
}
</style>
