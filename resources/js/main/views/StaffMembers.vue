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
      <!-- Desktop Table View -->
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
            <div class="flex-name cursor-pointer" @click="viewStaff(record)">
              <div class="avatar-circle" :style="{ background: avatarColor(record.name) }">
                {{ initials(record.name) }}
              </div>
              <div class="name-main hover:text-blue-600 font-semibold">{{ record.name }}</div>
            </div>
          </template>
          <template v-if="column.key === 'email'">
            <span class="name-sub">{{ record.email }}</span>
          </template>
          <template v-if="column.key === 'role'">
            <a-tag :color="roleColor(record.role)" style="text-transform: capitalize">
              {{ getRoleName(record) }}
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

      <!-- Mobile Responsive Card View -->
      <div class="mobile-cards-list" v-if="!loading">
        <div 
          v-for="s in staffList" 
          :key="'m-s-' + s.id"
          class="mobile-row-card"
          @click="viewStaff(s)"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <a-badge :status="s.active ? 'success' : 'default'" :text="s.active ? 'Active' : 'Inactive'" />
            </div>
            <a-tag :color="roleColor(s.role)" style="text-transform: capitalize">
              {{ getRoleName(s) }}
            </a-tag>
          </div>

          <div class="flex items-center gap-2.5 pt-1">
            <div class="avatar-circle" :style="{ background: avatarColor(s.name) }">
              {{ initials(s.name) }}
            </div>
            <div>
              <div class="font-bold text-sm text-slate-800">{{ s.name }}</div>
              <div class="text-xs text-slate-500">{{ s.email }}</div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-100">
            <div class="flex items-center gap-1.5 text-slate-500" v-if="s.phonenumber || s.phone">
              <span class="text-slate-400">📞 Phone:</span>
              <span>{{ s.phonenumber || s.phone }}</span>
            </div>
            <div class="flex items-center justify-end gap-1.5 text-slate-500">
              <span class="text-slate-400">Last Login:</span>
              <span>{{ formatLastLogin(s.last_login) }}</span>
            </div>
          </div>
        </div>

        <div v-if="!staffList.length" class="text-center p-6 text-slate-400 text-xs font-semibold">
          No staff members found
        </div>
      </div>
    </div>

    <!-- Permissions Explained Modal -->
    <a-modal v-model:open="showPermissionsInfo" title="Staff Permissions Explained" :width="760" :footer="null">
      <div class="permissions-guide-content space-y-4 text-xs text-slate-600 max-h-[65vh] overflow-y-auto pr-2">
        <div class="p-3 bg-blue-50 border border-blue-200 rounded text-blue-900 font-medium mb-3">
          Permissions control what features and data staff members can view, create, edit, or delete across iBridge CRM.
        </div>

        <div class="perm-guide-item border-b border-slate-100 pb-3">
          <h4 class="font-bold text-slate-800 text-sm mb-1">Invoices</h4>
          <ul class="list-disc pl-4 space-y-1">
            <li><strong>View (Global):</strong> All invoices.</li>
            <li><strong>View (Own):</strong> Only invoices created by a staff member.</li>
            <li><strong>Create:</strong> Create invoices.</li>
            <li><strong>Edit:</strong> All (if View Global permission) and own.</li>
            <li><strong>Delete:</strong> All (if View Global permission) and own.</li>
          </ul>
          <p class="text-[11px] text-slate-400 mt-1">Staff members can also view assigned invoices if allowed in Setup &rarr; Settings &rarr; Finance &rarr; Invoices.</p>
        </div>

        <div class="perm-guide-item border-b border-slate-100 pb-3">
          <h4 class="font-bold text-slate-800 text-sm mb-1">Estimates</h4>
          <ul class="list-disc pl-4 space-y-1">
            <li><strong>View (Global):</strong> All estimates.</li>
            <li><strong>View (Own):</strong> Only estimates created by the staff member.</li>
            <li><strong>Create:</strong> Create estimates.</li>
            <li><strong>Edit / Delete:</strong> All (if View Global permission) and own.</li>
          </ul>
        </div>

        <div class="perm-guide-item border-b border-slate-100 pb-3">
          <h4 class="font-bold text-slate-800 text-sm mb-1">Proposals</h4>
          <ul class="list-disc pl-4 space-y-1">
            <li><strong>View (Global):</strong> All proposals.</li>
            <li><strong>View (Own):</strong> Only proposals created by staff member.</li>
            <li><strong>Create / Edit / Delete:</strong> Standard scope based on global vs own permissions.</li>
          </ul>
        </div>

        <div class="perm-guide-item border-b border-slate-100 pb-3">
          <h4 class="font-bold text-slate-800 text-sm mb-1">Expenses &amp; Contracts</h4>
          <ul class="list-disc pl-4 space-y-1">
            <li><strong>View (Global):</strong> All expenses / contracts.</li>
            <li><strong>View (Own):</strong> Only expenses / contracts created by staff member.</li>
            <li><strong>Create / Edit / Delete:</strong> Scoped permissions.</li>
          </ul>
        </div>

        <div class="perm-guide-item border-b border-slate-100 pb-3">
          <h4 class="font-bold text-slate-800 text-sm mb-1">Payments</h4>
          <ul class="list-disc pl-4 space-y-1">
            <li><strong>View (Global):</strong> All payments.</li>
            <li><strong>View (Own):</strong> Based on invoices View (Own) permissions.</li>
            <li><strong>Create / Edit / Delete:</strong> Scoped to invoice permissions.</li>
          </ul>
        </div>

        <div class="perm-guide-item border-b border-slate-100 pb-3">
          <h4 class="font-bold text-slate-800 text-sm mb-1">Projects &amp; Tasks</h4>
          <ul class="list-disc pl-4 space-y-1">
            <li><strong>Projects View (Own):</strong> Allows viewing projects where staff member is added as project member.</li>
            <li><strong>Tasks View (Own):</strong> Allows viewing tasks assigned, followed, or public tasks.</li>
          </ul>
        </div>

        <div class="perm-guide-item border-b border-slate-100 pb-3">
          <h4 class="font-bold text-slate-800 text-sm mb-1">Customers</h4>
          <ul class="list-disc pl-4 space-y-1">
            <li><strong>View (Own):</strong> Based on Customer Admin assignment.</li>
            <li><strong>Create:</strong> Auto-adds staff as Customer Admin if lacking View Global.</li>
          </ul>
        </div>

        <div class="perm-guide-item border-b border-slate-100 pb-3">
          <h4 class="font-bold text-slate-800 text-sm mb-1">Leads</h4>
          <ul class="list-disc pl-4 space-y-1">
            <li><strong>View (Global):</strong> If unchecked, staff only view assigned, created, or public leads.</li>
            <li><strong>Create / Edit:</strong> All staff members can create &amp; edit accessible leads.</li>
            <li><strong>Delete:</strong> Staff members delete own leads only.</li>
          </ul>
        </div>

        <div class="perm-guide-item pb-1">
          <h4 class="font-bold text-slate-800 text-sm mb-1">Other Modules &amp; Permissions</h4>
          <p class="text-xs text-slate-600">Items, Knowledge Base, Goals, Email Templates, Reports, Roles, Settings, Staff, Surveys, Bulk PDF Exporter, and Support Tickets function with standard View (Global/Own), Create, Edit, and Delete capability levels.</p>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { message } from 'ant-design-vue';
import axios from 'axios';
import { getPermission, setPermission, isUserAdminRole, getRoleName } from '../utils/permissions';

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
    const showPermissionsInfo = ref(false);
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
      { key: 'Bulk PDF Export', label: 'Bulk PDF Export', caps: [
        { key: 'view_global', label: 'View(Global)' },
      ]},
      { key: 'Contracts', label: 'Contracts', caps: [
        { key: 'view_own', label: 'View (Own)' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
        { key: 'view_all_templates', label: 'View All Templates' },
      ]},
      { key: 'Credit Notes', label: 'Credit Notes', caps: [
        { key: 'view_own', label: 'View (Own)' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Customers', label: 'Customers', caps: [
        { key: 'view_own', label: 'View (Own)', tooltip: 'Based on customer admin assignment' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Email Templates', label: 'Email Templates', caps: [
        { key: 'view_global', label: 'View(Global)' },
        { key: 'edit', label: 'Edit' },
      ]},
      { key: 'Estimates', label: 'Estimates', caps: [
        { key: 'view_own', label: 'View (Own)' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Expenses', label: 'Expenses', caps: [
        { key: 'view_own', label: 'View (Own)' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Invoices', label: 'Invoices', caps: [
        { key: 'view_own', label: 'View (Own)' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Items', label: 'Items', caps: [
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Knowledge Base', label: 'Knowledge Base', caps: [
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Payments', label: 'Payments', caps: [
        { key: 'view_own', label: 'View (Own)', tooltip: 'Based on invoices View (Own) permissions' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Projects', label: 'Projects', caps: [
        { key: 'view_own', label: 'View (Own)', tooltip: 'Only projects where staff member is added as project member' },
        { key: 'view_global', label: 'View(Global)', tooltip: 'All projects' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
        { key: 'create_timesheets', label: 'Create Timesheets' },
        { key: 'edit_milestones', label: 'Edit Milestones' },
        { key: 'delete_milestones', label: 'Delete Milestones' },
      ]},
      { key: 'Proposals', label: 'Proposals', caps: [
        { key: 'view_own', label: 'View (Own)' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
        { key: 'view_all_templates', label: 'View All Templates' },
      ]},
      { key: 'Reports', label: 'Reports', caps: [
        { key: 'view_global', label: 'View(Global)' },
        { key: 'view_timesheets', label: 'View Timesheets Report' },
      ]},
      { key: 'Staff Roles', label: 'Staff Roles', caps: [
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Settings', label: 'Settings', caps: [
        { key: 'view_global', label: 'View(Global)' },
        { key: 'edit', label: 'Edit' },
      ]},
      { key: 'Staff', label: 'Staff', caps: [
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Subscriptions', label: 'Subscriptions', caps: [
        { key: 'view_own', label: 'View (Own)' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Tasks', label: 'Tasks', caps: [
        { key: 'view_own', label: 'View (Own)', tooltip: 'Only tasks assigned, followed or public' },
        { key: 'view_global', label: 'View(Global)', tooltip: 'All tasks' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
        { key: 'edit_timesheets_global', label: 'Edit Timesheets (Global)' },
        { key: 'edit_own_timesheets', label: 'Edit Own Timesheets' },
        { key: 'delete_timesheets_global', label: 'Delete Timesheets (Global)' },
        { key: 'delete_own_timesheets', label: 'Delete own Timesheets' },
      ]},
      { key: 'Task Checklist Templates', label: 'Task Checklist Templates', caps: [
        { key: 'create', label: 'Create' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Estimate Request', label: 'Estimate Request', caps: [
        { key: 'view_own', label: 'View (Own)' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Leads', label: 'Leads', caps: [
        { key: 'view_global', label: 'View(Global)', tooltip: 'If unchecked, staff member only views assigned, created or public leads' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Surveys', label: 'Surveys', caps: [
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'e-Invoice', label: 'e-Invoice', caps: [
        { key: 'bulk_export', label: 'Bulk export' },
      ]},
      { key: 'Goals', label: 'Goals', caps: [
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
    ];

    const isAdminRole = computed(() => {
      const r = roles.value.find(x => x.id === form.role_id);
      return isUserAdminRole(r);
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
      if (isNaN(date.getTime())) return 'Never';
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
        const res = await axios.get('/api/roles');
        const roleData = res.data?.data || res.data || [];
        roles.value = Array.isArray(roleData) ? roleData : [];
      } catch (e) {
        console.error('Error loading roles:', e);
      }
    };

    const getPerm = (feature, cap) => {
      return getPermission(form.permissions, feature, cap);
    };

    const setPerm = (feature, cap, val) => {
      setPermission(form.permissions, feature, cap, val);
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
        const rolePerms = JSON.parse(JSON.stringify(r.permissions));
        if (editing.value && form.permissions) {
          form.permissions = { ...rolePerms, ...form.permissions };
        } else {
          form.permissions = rolePerms;
        }
      } else if (!editing.value) {
        form.permissions = {};
      }
    };

    const openAdd = () => {
      router.push('/admin/staff/member');
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

    const router = useRouter();

    const viewStaff = (record) => {
      router.push(`/admin/staff/${record.id}`);
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
      form, roles, showPermissionsInfo, fileInput, isAdminRole,
      allCapabilities, featureList,
      initials, avatarColor, roleColor, formatDate, formatLastLogin, getRoleName,
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

/* Mobile Cards List Hidden by Default on Desktop */
.mobile-cards-list {
  display: none;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 12px !important;
  }
  .header-actions {
    width: 100% !important;
    justify-content: space-between !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
  }
  .header-actions .ant-input-search {
    width: 100% !important;
  }
  :deep(.ant-table-wrapper) {
    display: none !important;
  }
  .mobile-cards-list {
    display: flex !important;
    flex-direction: column;
    gap: 12px;
    padding: 12px;
  }
  .form-row {
    grid-template-columns: 1fr !important;
  }
}
</style>
