<template>
  <div class="staff-page">
    <div class="page-header">
      <div class="header-left">
        <h1>Staff Members</h1>
        <span class="subtitle">Manage your team members and their permissions</span>
      </div>
      <div class="header-actions">
        <a-input-search
          v-model:value="search"
          placeholder="Search staff..."
          @search="loadStaff"
          @input="onSearchInput"
          style="width: 240px"
          size="small"
        />
        <button class="btn-primary" @click="openAdd">
          <span>+</span> Add Staff Member
        </button>
      </div>
    </div>

    <div class="data-table-wrap">
      <a-table
        :dataSource="staffList"
        :columns="columns"
        :loading="loading"
        :pagination="{ pageSize: 15, total, showSizeChanger: true, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
        row-key="id"
        size="small"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'name'">
            <div class="flex-name">
              <div class="avatar-circle" :style="{ background: avatarColor(record.name) }">
                {{ initials(record.name) }}
              </div>
              <div class="name-main">{{ record.name }}</div>
            </div>
          </template>
          <template v-if="column.key === 'email'">
            <span class="name-sub">{{ record.email }}</span>
          </template>
          <template v-if="column.key === 'role'">
            <a-tag :color="roleColor(record.role)" style="text-transform: capitalize">
              {{ record.role_data?.name || record.role || 'Employee' }}
            </a-tag>
          </template>
          <template v-if="column.key === 'last_login'">
            {{ formatLastLogin(record.last_login) }}
          </template>
          <template v-if="column.key === 'active'">
            <a-badge :status="record.active ? 'success' : 'default'" :text="record.active ? 'Active' : 'Inactive'" />
          </template>
          <template v-if="column.key === 'actions'">
            <div class="row-actions">
              <a-button size="small" type="link" @click="viewStaff(record)">View</a-button>
              <a-button size="small" type="link" danger @click="deleteStaff(record)">Delete</a-button>
            </div>
          </template>
        </template>
      </a-table>
    </div>

    <!-- Add/Edit Modal -->
    <a-modal
      v-model:open="modalOpen"
      :title="viewing ? 'View Staff Member' : editing ? 'Edit Staff Member' : 'Add New Staff Member'"
      :width="720"
      :footer="null"
      :destroyOnClose="true"
      @cancel="closeModal"
    >
      <a-tabs v-model:activeKey="activeTab">
        <a-tab-pane key="profile" tab="Profile">
          <a-form layout="vertical" :model="form" ref="profileFormRef">
            <div class="profile-image-row">
              <div class="profile-image-upload">
                <div class="avatar-circle large" v-if="!form.profile_image" :style="{ background: avatarColor(form.first_name + ' ' + form.last_name) }">
                  {{ initials(form.first_name + ' ' + form.last_name) || '?' }}
                </div>
                <img v-else :src="form.profile_image" class="profile-preview" />
                <div class="upload-actions">
                  <a-button size="small" @click="triggerUpload" :disabled="viewing">Choose File</a-button>
                  <span class="file-hint">No file chosen</span>
                </div>
                <input type="file" ref="fileInput" accept="image/*" style="display:none" @change="onFileChange" />
              </div>
              <div class="staff-type-badge">
                <a-checkbox :checked="isAdminRole" @change="toggleAdmin" :disabled="viewing">Administrator</a-checkbox>
                <a-checkbox v-model:value="form.not_staff" :disabled="viewing">Not Staff Member</a-checkbox>
              </div>
            </div>

            <div class="form-row">
              <a-form-item label="* First Name" name="first_name" :rules="[{ required: true, message: 'First name required' }]">
                <a-input v-model:value="form.first_name" placeholder="First Name" :disabled="viewing" />
              </a-form-item>
              <a-form-item label="* Last Name" name="last_name" :rules="[{ required: true, message: 'Last name required' }]">
                <a-input v-model:value="form.last_name" placeholder="Last Name" :disabled="viewing" />
              </a-form-item>
            </div>

            <div class="form-row">
              <a-form-item label="* Email" name="email" :rules="[{ required: true, type: 'email', message: 'Valid email required' }]">
                <a-input v-model:value="form.email" placeholder="Email" :disabled="viewing" />
              </a-form-item>
              <a-form-item label="Hourly Rate">
                <a-input-number v-model:value="form.hourly_rate" :min="0" :precision="2" style="width: 100%" :disabled="viewing">
                  <template #addonBefore>$</template>
                </a-input-number>
              </a-form-item>
            </div>

            <div class="form-row">
              <a-form-item label="Phone">
                <a-input v-model:value="form.phone" placeholder="Phone" :disabled="viewing" />
              </a-form-item>
              <a-form-item label="Facebook">
                <a-input v-model:value="form.facebook" placeholder="Facebook" :disabled="viewing" />
              </a-form-item>
            </div>

            <div class="form-row">
              <a-form-item label="LinkedIn">
                <a-input v-model:value="form.linkedin" placeholder="LinkedIn" :disabled="viewing" />
              </a-form-item>
              <a-form-item label="Skype">
                <a-input v-model:value="form.skype" placeholder="Skype" :disabled="viewing" />
              </a-form-item>
            </div>

            <div class="form-row">
              <a-form-item label="Default Language">
                <a-select v-model:value="form.default_language" style="width: 100%" :disabled="viewing">
                  <a-select-option value="">System Default</a-select-option>
                  <a-select-option value="en">English</a-select-option>
                  <a-select-option value="es">Spanish</a-select-option>
                  <a-select-option value="fr">French</a-select-option>
                  <a-select-option value="de">German</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Direction">
                <a-select v-model:value="form.direction" style="width: 100%" :disabled="viewing">
                  <a-select-option value="">LTR</a-select-option>
                  <a-select-option value="rtl">RTL</a-select-option>
                </a-select>
              </a-form-item>
            </div>

            <a-form-item label="Email Signature">
              <a-textarea v-model:value="form.email_signature" :rows="3" placeholder="Email signature..." :disabled="viewing" />
            </a-form-item>

            <a-form-item label="Member departments">
              <a-checkbox-group v-model:value="form.departments" :disabled="viewing">
                <a-checkbox value="Marketing">Marketing</a-checkbox>
                <a-checkbox value="Sales">Sales</a-checkbox>
                <a-checkbox value="Abuse">Abuse</a-checkbox>
              </a-checkbox-group>
            </a-form-item>

            <a-form-item>
              <a-checkbox v-model:value="form.send_welcome_email" :disabled="viewing">Send welcome email</a-checkbox>
            </a-form-item>

            <div class="form-row">
              <a-form-item
                label="* Password"
                name="password"
                :rules="editing ? [] : [{ required: true, message: 'Password required' }]"
              >
                <a-input-password v-model:value="form.password" placeholder="Password" :disabled="viewing" />
              </a-form-item>
              <a-form-item label="Confirm Password">
                <a-input-password v-model:value="form.password_confirmation" placeholder="Confirm password" :disabled="viewing" />
              </a-form-item>
            </div>
          </a-form>
        </a-tab-pane>

        <a-tab-pane key="permissions" tab="Permissions">
          <div class="permissions-section">
            <div class="perm-info">
              <a-typography-link href="#" @click.prevent>Click here to read more about permissions</a-typography-link>
            </div>

            <a-form-item label="Role">
              <a-select v-model:value="form.role_id" style="width: 280px" @change="onRoleChange" :disabled="viewing">
                <a-select-option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</a-select-option>
              </a-select>
            </a-form-item>

            <div v-if="isAdminRole" class="admin-notice">
              <a-checkbox checked disabled>Administrator</a-checkbox>
              <span class="admin-hint">Administrator has full access to all features</span>
            </div>

            <div v-else class="permissions-grid">
              <div class="perm-table">
                <div class="perm-table-header">
                  <div class="perm-cell feature-col">Features</div>
                  <div class="perm-cell cap-col" v-for="cap in allCapabilities" :key="cap.key">{{ cap.label }}</div>
                </div>
                <div v-for="feat in featureList" :key="feat.key" class="perm-table-row">
                  <div class="perm-cell feature-col">{{ feat.label }}</div>
                  <div class="perm-cell cap-col" v-for="cap in feat.caps" :key="cap.key">
                    <a-checkbox
                      v-if="cap.type === 'checkbox'"
                      :checked="getPerm(feat.key, cap.key)"
                      @change="e => setPerm(feat.key, cap.key, e.target.checked)"
                      :disabled="viewing"
                    />
                    <span v-else-if="cap.type === 'label'" class="cap-label">{{ cap.label }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </a-tab-pane>
      </a-tabs>

      <div class="modal-footer" v-if="!viewing">
        <a-button @click="closeModal">Cancel</a-button>
        <a-button type="primary" :loading="saving" @click="saveStaff">{{ editing ? 'Update' : 'Add Staff Member' }}</a-button>
      </div>
      <div class="modal-footer" v-else>
        <a-button @click="closeModal">Close</a-button>
        <a-button type="primary" @click="viewing = false; editing = true">Edit</a-button>
      </div>
    </a-modal>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import axios from 'axios';

export default {
  name: 'StaffMembers',
  setup() {
    const staffList = ref([]);
    const total = ref(0);
    const loading = ref(false);
    const search = ref('');
    const modalOpen = ref(false);
    const editing = ref(false);
    const viewing = ref(false);
    const saving = ref(false);
    const activeTab = ref('profile');
    const roles = ref([]);
    const fileInput = ref(null);
    const profileFormRef = ref(null);

    const form = reactive({
      first_name: '', last_name: '', email: '', password: '', password_confirmation: '',
      role_id: null, hourly_rate: 0, phone: '', facebook: '', linkedin: '', skype: '',
      default_language: '', email_signature: '', direction: '', departments: [],
      profile_image: '', send_welcome_email: false, not_staff: false,
    });

    const allCapabilities = [
      { key: 'view_own', label: 'View (Own)' },
      { key: 'view_global', label: 'View (Global)' },
      { key: 'create', label: 'Create' },
      { key: 'edit', label: 'Edit' },
      { key: 'delete', label: 'Delete' },
    ];

    const featureList = [
      { key: 'Bulk PDF Export', label: 'Bulk PDF Export', caps: [{ key: 'view_global', type: 'checkbox' }] },
      { key: 'Contracts', label: 'Contracts', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
        { key: 'view_all_templates', type: 'label', label: 'View All Templates' },
      ]},
      { key: 'Credit Notes', label: 'Credit Notes', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Customers', label: 'Customers', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Email Templates', label: 'Email Templates', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'edit', type: 'checkbox' },
      ]},
      { key: 'Estimates', label: 'Estimates', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Expenses', label: 'Expenses', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Invoices', label: 'Invoices', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Items', label: 'Items', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'create', type: 'checkbox' },
        { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Knowledge Base', label: 'Knowledge Base', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'create', type: 'checkbox' },
        { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Payments', label: 'Payments', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Projects', label: 'Projects', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
        { key: 'create_timesheets', type: 'label', label: 'Create Timesheets' },
        { key: 'edit_milestones', type: 'label', label: 'Edit Milestones' },
        { key: 'delete_milestones', type: 'label', label: 'Delete Milestones' },
      ]},
      { key: 'Proposals', label: 'Proposals', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
        { key: 'view_all_templates', type: 'label', label: 'View All Templates' },
      ]},
      { key: 'Reports', label: 'Reports', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'view_timesheets', type: 'label', label: 'View Timesheets Report' },
      ]},
      { key: 'Staff Roles', label: 'Staff Roles', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'create', type: 'checkbox' },
        { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Settings', label: 'Settings', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'edit', type: 'checkbox' },
      ]},
      { key: 'Staff', label: 'Staff', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'create', type: 'checkbox' },
        { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Subscriptions', label: 'Subscriptions', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Tasks', label: 'Tasks', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
        { key: 'edit_timesheets_global', type: 'label', label: 'Edit Timesheets (Global)' },
        { key: 'edit_own_timesheets', type: 'label', label: 'Edit Own Timesheets' },
        { key: 'delete_timesheets_global', type: 'label', label: 'Delete Timesheets (Global)' },
        { key: 'delete_own_timesheets', type: 'label', label: 'Delete own Timesheets' },
      ]},
      { key: 'Task Checklist Templates', label: 'Task Checklist Templates', caps: [
        { key: 'create', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Estimate Request', label: 'Estimate Request', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Leads', label: 'Leads', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Surveys', label: 'Surveys', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'create', type: 'checkbox' },
        { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'e-Invoice', label: 'e-Invoice', caps: [
        { key: 'bulk_export', type: 'label', label: 'Bulk export' },
      ]},
      { key: 'Goals', label: 'Goals', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'create', type: 'checkbox' },
        { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
    ];

    const isAdminRole = computed(() => {
      const r = roles.value.find(x => x.id === form.role_id);
      return r?.slug === 'admin';
    });

    const columns = [
      { title: 'Full Name', key: 'name', dataIndex: 'name' },
      { title: 'Email', key: 'email', dataIndex: 'email' },
      { title: 'Role', key: 'role', dataIndex: 'role', width: 140 },
      { title: 'Last Login', key: 'last_login', dataIndex: 'last_login', width: 150 },
      { title: 'Active', key: 'active', dataIndex: 'active', width: 100 },
      { title: 'Actions', key: 'actions', width: 130 },
    ];

    const initials = (name) => {
      if (!name) return '?';
      return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
    };

    const avatarColor = (name) => {
      const colors = ['#2563eb', '#e11d48', '#f59e0b', '#10b981', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316'];
      let hash = 0;
      for (let i = 0; i < (name || '').length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
      return colors[Math.abs(hash) % colors.length];
    };

    const roleColor = (role) => {
      if (!role) return 'default';
      if (role === 'admin') return 'blue';
      if (role === 'manager' || role === 'employee') return 'orange';
      return 'default';
    };

    const formatDate = (d) => {
      if (!d) return '';
      return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
    };

    const formatLastLogin = (d) => {
      if (!d) return 'Never';
      const date = new Date(d);
      const now = new Date();
      const diff = Math.floor((now - date) / 1000);
      if (diff < 60) return 'Just now';
      if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
      if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
      if (diff < 604800) return Math.floor(diff / 86400) + ' days ago';
      return date.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' });
    };

    const loadStaff = async () => {
      loading.value = true;
      try {
        const { data } = await axios.get('/api/staff', { params: { search: search.value, per_page: 25 } });
        staffList.value = data.staff.data || data.staff || [];
        total.value = data.total || staffList.value.length;
      } catch (e) {
        message.error('Failed to load staff');
      } finally {
        loading.value = false;
      }
    };

    // Reload when search changes (with debounce)
    let searchTimer;
    const onSearchInput = () => {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(loadStaff, 300);
    };

    const loadRoles = async () => {
      try {
        const { data } = await axios.get('/api/roles');
        roles.value = data;
      } catch (e) {
        console.error('Failed to load roles', e);
      }
    };

    const getPerm = (feature, cap) => {
      const perms = form.permissions || {};
      return perms[feature]?.[cap] ?? false;
    };

    const setPerm = (feature, cap, val) => {
      if (!form.permissions) form.permissions = {};
      if (!form.permissions[feature]) form.permissions[feature] = {};
      form.permissions[feature][cap] = val;
    };

    const toggleAdmin = (e) => {
      if (e.target.checked) {
        const adminRole = roles.value.find(r => r.slug === 'admin');
        if (adminRole) {
          form.role_id = adminRole.id;
          form.permissions = adminRole.permissions ? JSON.parse(JSON.stringify(adminRole.permissions)) : {};
        }
      } else {
        const empRole = roles.value.find(r => r.slug === 'employee');
        form.role_id = empRole ? empRole.id : null;
        if (empRole?.permissions) {
          form.permissions = JSON.parse(JSON.stringify(empRole.permissions));
        } else {
          form.permissions = {};
        }
      }
    };

    const onRoleChange = () => {
      const r = roles.value.find(x => x.id === form.role_id);
      if (r?.permissions) {
        form.permissions = JSON.parse(JSON.stringify(r.permissions));
      } else {
        form.permissions = {};
      }
    };

    const openAdd = () => {
      editing.value = false;
      viewing.value = false;
      activeTab.value = 'profile';
      Object.assign(form, {
        first_name: '', last_name: '', email: '', password: '', password_confirmation: '',
        role_id: null, hourly_rate: 0, phone: '', facebook: '', linkedin: '', skype: '',
        default_language: '', email_signature: '', direction: '', departments: [],
        profile_image: '', send_welcome_email: false, not_staff: false, permissions: {},
      });
      modalOpen.value = true;
    };

    const editStaff = (record) => {
      editing.value = true;
      viewing.value = false;
      activeTab.value = 'profile';
      const names = (record.name || '').split(' ');
      const first = names.shift() || '';
      const last = names.join(' ') || '';
      Object.assign(form, {
        id: record.id,
        first_name: first,
        last_name: last,
        email: record.email || '',
        password: '',
        password_confirmation: '',
        role_id: record.role_id || null,
        hourly_rate: record.hourly_rate ?? 0,
        phone: record.phone || '',
        facebook: record.facebook || '',
        linkedin: record.linkedin || '',
        skype: record.skype || '',
        default_language: record.default_language || '',
        email_signature: record.email_signature || '',
        direction: record.direction || '',
        departments: record.department ? record.department.split(', ') : [],
        profile_image: record.profile_image || '',
        send_welcome_email: false,
        not_staff: !record.active,
      });
      if (record.permissions) {
        form.permissions = JSON.parse(JSON.stringify(record.permissions));
      } else if (record.role_id) {
        const r = roles.value.find(x => x.id === record.role_id);
        form.permissions = r?.permissions ? JSON.parse(JSON.stringify(r.permissions)) : {};
      } else {
        form.permissions = {};
      }
      modalOpen.value = true;
    };

    const viewStaff = (record) => {
      viewing.value = true;
      editing.value = false;
      activeTab.value = 'profile';
      const names = (record.name || '').split(' ');
      const first = names.shift() || '';
      const last = names.join(' ') || '';
      Object.assign(form, {
        id: record.id,
        first_name: first,
        last_name: last,
        email: record.email || '',
        password: '',
        password_confirmation: '',
        role_id: record.role_id || null,
        hourly_rate: record.hourly_rate ?? 0,
        phone: record.phone || '',
        facebook: record.facebook || '',
        linkedin: record.linkedin || '',
        skype: record.skype || '',
        default_language: record.default_language || '',
        email_signature: record.email_signature || '',
        direction: record.direction || '',
        departments: record.department ? record.department.split(', ') : [],
        profile_image: record.profile_image || '',
        send_welcome_email: false,
        not_staff: !record.active,
      });
      if (record.permissions) {
        form.permissions = JSON.parse(JSON.stringify(record.permissions));
      } else if (record.role_id) {
        const r = roles.value.find(x => x.id === record.role_id);
        form.permissions = r?.permissions ? JSON.parse(JSON.stringify(r.permissions)) : {};
      } else {
        form.permissions = {};
      }
      modalOpen.value = true;
    };

    const saveStaff = async () => {
      saving.value = true;
      try {
        const payload = {
          name: (form.first_name + ' ' + form.last_name).trim(),
          email: form.email,
          password: form.password || undefined,
          password_confirmation: form.password_confirmation || undefined,
          role_id: form.role_id,
          hourly_rate: form.hourly_rate,
          phone: form.phone,
          facebook: form.facebook,
          linkedin: form.linkedin,
          skype: form.skype,
          default_language: form.default_language,
          email_signature: form.email_signature,
          direction: form.direction,
          department: form.departments.join(', '),
          active: !form.not_staff,
          permissions: form.permissions,
        };

        if (editing.value) {
          await axios.put(`/api/staff/${form.id}`, payload);
          message.success('Staff member updated');
        } else {
          await axios.post('/api/staff', payload);
          message.success('Staff member added');
        }

        modalOpen.value = false;
        await loadStaff();
      } catch (e) {
        const msg = e.response?.data?.message || 'An error occurred';
        message.error(msg);
      } finally {
        saving.value = false;
      }
    };

    const deleteStaff = (record) => {
      if (!confirm(`Delete ${record.name}?`)) return;
      axios.delete(`/api/staff/${record.id}`).then(() => {
        message.success('Staff member deleted');
        loadStaff();
      }).catch(() => {
        message.error('Failed to delete');
      });
    };

    const closeModal = () => {
      modalOpen.value = false;
      viewing.value = false;
    };

    const triggerUpload = () => {
      fileInput.value?.click();
    };

    const onFileChange = (e) => {
      const file = e.target.files?.[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (ev) => {
        form.profile_image = ev.target.result;
      };
      reader.readAsDataURL(file);
    };

    onMounted(() => {
      loadStaff();
      loadRoles();
    });

    return {
      staffList, total, loading, search, columns, modalOpen, editing, viewing, saving, activeTab,
      form, roles, fileInput, isAdminRole,
      allCapabilities, featureList,
      initials, avatarColor, roleColor, formatDate, formatLastLogin,
      loadStaff, openAdd, editStaff, viewStaff, saveStaff, deleteStaff, closeModal,
      getPerm, setPerm, onRoleChange, toggleAdmin, onSearchInput,
      triggerUpload, onFileChange,
    };
  },
};
</script>

<style scoped>
.staff-page {
  font-family: 'Inter', -apple-system, sans-serif;
  font-size: 13px;
  color: #334155;
}
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 18px;
  flex-wrap: wrap;
  gap: 12px;
}
.header-left h1 {
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
.subtitle {
  font-size: 12px;
  color: #94a3b8;
}
.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}
.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 6px 14px;
  background: #2563eb;
  color: #fff;
  border: none;
  border-radius: 4px;
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
}
.btn-primary:hover { background: #1d4ed8; }
.data-table-wrap {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  overflow: hidden;
}
.flex-name {
  display: flex;
  align-items: center;
  gap: 10px;
}
.avatar-circle {
  width: 32px; height: 32px;
  border-radius: 50%;
  background: #2563eb;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  flex-shrink: 0;
}
.avatar-circle.large { width: 48px; height: 48px; font-size: 16px; }
.name-main { font-weight: 600; color: #1e293b; font-size: 13px; }
.name-sub { color: #94a3b8; font-size: 11.5px; }
.row-actions { display: flex; gap: 4px; }

/* Modal form */
.profile-image-row {
  display: flex;
  align-items: flex-start;
  gap: 20px;
  margin-bottom: 16px;
}
.profile-image-upload { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.profile-preview { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; }
.upload-actions { display: flex; align-items: center; gap: 8px; }
.file-hint { font-size: 11px; color: #94a3b8; }
.staff-type-badge { padding-top: 6px; display: flex; flex-direction: column; gap: 6px; }
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0 16px;
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
  margin-top: 16px;
}

/* Permissions */
.permissions-section { padding: 0; }
.perm-info { margin-bottom: 12px; }
.admin-notice {
  padding: 12px 16px;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 4px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.admin-hint { font-size: 12px; color: #166534; }
.perm-table {
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
  margin-top: 12px;
}
.perm-table-header {
  display: flex;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  font-weight: 600;
  font-size: 11.5px;
  color: #475569;
}
.perm-table-row {
  display: flex;
  border-bottom: 1px solid #f1f5f9;
}
.perm-table-row:last-child { border-bottom: none; }
.perm-cell { padding: 6px 10px; font-size: 12px; }
.feature-col { width: 180px; flex-shrink: 0; font-weight: 500; color: #334155; }
.cap-col {
  width: 120px;
  flex-shrink: 0;
  text-align: center;
  border-left: 1px solid #f1f5f9;
}
.cap-label { font-size: 11px; color: #64748b; }
</style>
