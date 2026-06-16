<template>
  <div class="contacts-list-page space-y-6">
    
    <!-- Action Bar -->
    <div class="page-header flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <div class="flex items-center space-x-2 text-xs text-slate-400 mb-1">
          <router-link to="/admin/customers" class="hover:text-indigo-600">Customers</router-link>
          <span>/</span>
          <span class="text-slate-600">All Contacts</span>
        </div>
        <h1 class="page-title text-xl font-bold text-slate-800">Contacts</h1>
        <p class="text-xs text-slate-500 mt-0.5">Manage contacts across all your customer accounts</p>
      </div>
      <div class="flex items-center gap-2">
        <button class="btn-outline" @click="showSummary = !showSummary">
          {{ showSummary ? 'Hide Summary' : 'Show Summary' }}
        </button>
        <button class="btn-primary" @click="openCreateDrawer">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13" class="mr-1.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          New Contact
        </button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards" v-if="showSummary">
      <div class="summary-card">
        <div class="summary-label">Total Contacts</div>
        <div class="summary-value">{{ summary.total_contacts || 0 }}</div>
      </div>
      <div class="summary-card">
        <div class="summary-label">Active Contacts</div>
        <div class="summary-value text-success">{{ summary.active_contacts || 0 }}</div>
      </div>
      <div class="summary-card">
        <div class="summary-label">Inactive Contacts</div>
        <div class="summary-value text-danger">{{ summary.inactive_contacts || 0 }}</div>
      </div>
      <div class="summary-card">
        <div class="summary-label">Logged In Today</div>
        <div class="summary-value text-info">{{ summary.contacts_logged_in_today || 0 }}</div>
      </div>
    </div>

    <!-- Search / Filter toolbar card -->
    <div class="table-card bg-white border border-slate-200 rounded-lg shadow-sm">
      <div class="table-toolbar p-4 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="toolbar-left flex items-center space-x-3">
          <select class="select-sm" v-model="pagination.pageSize" @change="fetchContacts">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
          
          <div class="flex items-center space-x-2 border-l pl-3 ml-2">
            <span class="text-xs font-semibold text-slate-500 uppercase">Status:</span>
            <select class="select-sm" v-model="filterActive" @change="fetchContacts">
              <option value="all">All</option>
              <option value="true">Active</option>
              <option value="false">Inactive</option>
            </select>
          </div>
        </div>
        
        <div class="toolbar-right">
          <input 
            class="input-sm" 
            v-model="searchQuery" 
            placeholder="Search name, email, phone, position..." 
            @input="debounceSearch"
          />
        </div>
      </div>

      <!-- Data Table -->
      <a-table
        :columns="columns"
        :data-source="contacts"
        :row-key="record => record.id"
        :pagination="pagination"
        :loading="loading"
        @change="handleTableChange"
        size="small"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'firstname'">
            <div class="flex flex-col group">
              <span class="font-semibold text-slate-800">{{ record.firstname }}</span>
              <div class="flex items-center space-x-1.5 text-[10px] text-slate-400 mt-0.5">
                <span class="hover:text-indigo-600 cursor-pointer link-action font-semibold" @click="editContact(record)">Edit</span>
                <span>|</span>
                <a-popconfirm
                  title="Are you sure you want to delete this contact?"
                  ok-text="Yes"
                  cancel-text="No"
                  @confirm="deleteContact(record.id)"
                >
                  <span class="text-rose-500 hover:text-rose-700 cursor-pointer font-semibold">Delete</span>
                </a-popconfirm>
              </div>
            </div>
          </template>

          <template v-if="column.key === 'lastname'">
            <span class="text-slate-700 font-medium">{{ record.lastname || '—' }}</span>
          </template>

          <template v-if="column.key === 'email'">
            <a :href="'mailto:' + record.email" class="text-indigo-600 hover:underline">{{ record.email }}</a>
          </template>

          <template v-if="column.key === 'company'">
            <router-link 
              v-if="record.client" 
              :to="'/admin/customers/' + record.client_id" 
              class="font-semibold text-slate-800 hover:text-indigo-600 cursor-pointer link-blue"
            >
              {{ record.client.company }}
            </router-link>
            <span v-else class="text-slate-300">—</span>
          </template>

          <template v-if="column.key === 'phonenumber'">
            <span class="text-slate-600">{{ record.phonenumber || '—' }}</span>
          </template>

          <template v-if="column.key === 'title'">
            <span class="text-slate-600">{{ record.title || '—' }}</span>
          </template>

          <template v-if="column.key === 'last_login'">
            <span class="text-slate-500 text-xs">{{ formatDateTime(record.last_login) }}</span>
          </template>

          <template v-if="column.key === 'active'">
            <span 
              class="badge cursor-pointer select-none" 
              :class="record.active ? 'badge-green' : 'badge-red'"
              @click="toggleContactStatus(record)"
              title="Click to toggle status"
            >
              {{ record.active ? 'Active' : 'Inactive' }}
            </span>
          </template>
        </template>
      </a-table>
    </div>

    <!-- Creation/Edit Drawer -->
    <a-drawer
      v-model:open="drawerVisible"
      :title="editMode ? 'Edit Contact' : 'Create New Contact'"
      placement="right"
      :width="480"
      :footer-style="{ textAlign: 'right' }"
      @close="closeDrawer"
    >
      <a-form :model="form" layout="vertical" ref="formRef">
        <div class="grid grid-cols-2 gap-4">
          <a-form-item label="First Name" name="firstname" :rules="[{ required: true, message: 'Please input first name!' }]">
            <a-input v-model:value="form.firstname" placeholder="First Name" />
          </a-form-item>
          <a-form-item label="Last Name" name="lastname">
            <a-input v-model:value="form.lastname" placeholder="Last Name" />
          </a-form-item>
        </div>

        <a-form-item 
          label="Email Address" 
          name="email" 
          :rules="[{ required: true, type: 'email', message: 'Please input a valid email!' }]"
        >
          <a-input v-model:value="form.email" placeholder="email@example.com" />
        </a-form-item>

        <div class="grid grid-cols-2 gap-4">
          <a-form-item label="Phone Number" name="phonenumber">
            <a-input v-model:value="form.phonenumber" placeholder="+1 555-0100" />
          </a-form-item>
          <a-form-item label="Position / Job Title" name="title">
            <a-input v-model:value="form.title" placeholder="e.g. Computer Specialist" />
          </a-form-item>
        </div>

        <a-form-item 
          label="Company / Account" 
          name="client_id" 
          :rules="[{ required: true, message: 'Please select a company!' }]"
        >
          <a-select 
            v-model:value="form.client_id" 
            placeholder="Select customer company" 
            show-search
            option-filter-prop="children"
            style="width: 100%"
          >
            <a-select-option v-for="c in clientOptions" :key="c.id" :value="c.id">
              {{ c.company }}
            </a-select-option>
          </a-select>
        </a-form-item>

        <a-form-item label="Password" name="password" extra="For portal client login. Leave empty if you don't want to change it.">
          <a-input-password v-model:value="form.password" placeholder="Password" />
        </a-form-item>

        <div class="space-y-3 mt-4 p-3 bg-slate-50 border border-slate-100 rounded-md">
          <div>
            <a-checkbox v-model:checked="form.active">Active Contact</a-checkbox>
          </div>
          <div>
            <a-checkbox v-model:checked="form.is_primary">Set as Primary Contact</a-checkbox>
          </div>
        </div>
      </a-form>

      <template #extra>
        <a-space>
          <a-button @click="closeDrawer">Cancel</a-button>
          <a-button type="primary" :loading="submitLoading" @click="submitForm" class="bg-indigo-600 border-indigo-600">
            Save
          </a-button>
        </a-space>
      </template>
    </a-drawer>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'ContactsList',
  setup() {
    const contacts = ref([]);
    const summary = ref({});
    const clientOptions = ref([]);
    const loading = ref(false);
    const submitLoading = ref(false);
    const searchQuery = ref('');
    const filterActive = ref('all');
    const showSummary = ref(true);
    let searchTimeout = null;

    // Drawer state
    const drawerVisible = ref(false);
    const editMode = ref(false);
    const currentEditId = ref(null);
    const formRef = ref(null);

    const form = reactive({
      firstname: '',
      lastname: '',
      email: '',
      phonenumber: '',
      title: '',
      client_id: undefined,
      password: '',
      active: true,
      is_primary: false,
    });

    const pagination = reactive({
      current: 1,
      pageSize: 10,
      total: 0,
      showSizeChanger: true,
      pageSizeOptions: ['10', '25', '50'],
      showTotal: (total) => `Showing 1 to ${contacts.value.length} of ${total} entries`,
    });

    const columns = [
      { title: 'First Name', key: 'firstname', width: '180px' },
      { title: 'Last Name', key: 'lastname' },
      { title: 'Email', key: 'email' },
      { title: 'Company', key: 'company' },
      { title: 'Phone', key: 'phonenumber' },
      { title: 'Position', key: 'title' },
      { title: 'Last Login', key: 'last_login', width: '130px' },
      { title: 'Active', key: 'active', width: '90px' },
    ];

    const fetchContacts = async () => {
      loading.value = true;
      try {
        const params = {
          page: pagination.current,
          per_page: pagination.pageSize,
        };

        if (searchQuery.value) {
          params.search = searchQuery.value;
        }

        if (filterActive.value !== 'all') {
          params.active = filterActive.value;
        }

        const response = await axios.get('/contacts', { params });
        contacts.value = response.data.contacts?.data || [];
        pagination.total = response.data.contacts?.total || 0;
        summary.value = response.data.summary || {};
      } catch (err) {
        message.error('Failed to load contacts.');
      } finally {
        loading.value = false;
      }
    };

    const loadClients = async () => {
      try {
        const res = await axios.get('/clients', { params: { per_page: 200 } });
        clientOptions.value = res.data.clients?.data || [];
      } catch (err) {
        console.error('Failed to load client options', err);
      }
    };

    const handleTableChange = (paginationEvent) => {
      pagination.current = paginationEvent.current;
      pagination.pageSize = paginationEvent.pageSize;
      fetchContacts();
    };

    const debounceSearch = () => {
      if (searchTimeout) clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        pagination.current = 1;
        fetchContacts();
      }, 300);
    };

    const openCreateDrawer = () => {
      editMode.value = false;
      currentEditId.value = null;

      Object.assign(form, {
        firstname: '',
        lastname: '',
        email: '',
        phonenumber: '',
        title: '',
        client_id: undefined,
        password: '',
        active: true,
        is_primary: false,
      });

      drawerVisible.value = true;
    };

    const editContact = (record) => {
      editMode.value = true;
      currentEditId.value = record.id;

      Object.assign(form, {
        firstname: record.firstname,
        lastname: record.lastname,
        email: record.email,
        phonenumber: record.phonenumber,
        title: record.title,
        client_id: record.client_id,
        password: '',
        active: record.active === 1 || record.active === true,
        is_primary: record.is_primary === 1 || record.is_primary === true,
      });

      drawerVisible.value = true;
    };

    const closeDrawer = () => {
      drawerVisible.value = false;
    };

    const submitForm = () => {
      formRef.value.validate().then(async () => {
        submitLoading.value = true;
        try {
          const payload = { ...form };
          if (!payload.password) delete payload.password;

          if (editMode.value) {
            await axios.put(`/contacts/${currentEditId.value}`, payload);
            message.success('Contact updated successfully.');
          } else {
            await axios.post('/contacts', payload);
            message.success('Contact created successfully.');
          }
          drawerVisible.value = false;
          fetchContacts();
        } catch (err) {
          const errMsg = err.response?.data?.message || 'Error occurred while saving contact.';
          message.error(errMsg);
        } finally {
          submitLoading.value = false;
        }
      });
    };

    const deleteContact = async (id) => {
      try {
        await axios.delete(`/contacts/${id}`);
        message.success('Contact deleted successfully.');
        fetchContacts();
      } catch (err) {
        message.error('Failed to delete contact.');
      }
    };

    const toggleContactStatus = async (record) => {
      const newStatus = !record.active;
      try {
        await axios.put(`/contacts/${record.id}/status`, { active: newStatus });
        record.active = newStatus;
        message.success(`Contact status changed to ${newStatus ? 'Active' : 'Inactive'}.`);
        fetchContacts(); // Reload summaries
      } catch (err) {
        message.error('Failed to update contact status.');
      }
    };

    const formatDateTime = (val) => {
      if (!val) return 'Never';
      const date = new Date(val);
      if (isNaN(date.getTime())) return val;
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
    };

    onMounted(() => {
      fetchContacts();
      loadClients();
    });

    return {
      contacts,
      summary,
      clientOptions,
      loading,
      submitLoading,
      searchQuery,
      filterActive,
      showSummary,
      columns,
      pagination,
      drawerVisible,
      editMode,
      form,
      formRef,
      fetchContacts,
      handleTableChange,
      debounceSearch,
      openCreateDrawer,
      editContact,
      closeDrawer,
      submitForm,
      deleteContact,
      toggleContactStatus,
      formatDateTime,
    };
  },
});
</script>

<style scoped>
@import '@/main/module-shared.css';

.contacts-list-page {
  font-family: 'Inter', -apple-system, sans-serif;
}

.link-action {
  transition: color 0.1s;
}
</style>
