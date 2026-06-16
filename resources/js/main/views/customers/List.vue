<template>
  <div class="customers-list-page space-y-6">
    
    <!-- Action Bar -->
    <div class="page-header flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="page-title text-xl font-bold text-slate-800">Customers</h1>
        <p class="text-xs text-slate-500 mt-0.5">Manage your client database and contact profiles</p>
      </div>
      <div class="flex items-center gap-2">
        <button class="btn-outline" @click="showSummary = !showSummary">
          {{ showSummary ? 'Hide Summary' : 'Show Summary' }}
        </button>
        <router-link to="/admin/customers/all-contacts" class="btn-outline">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="mr-1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Contacts
        </router-link>
        <button class="btn-primary" @click="goToCreatePage">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13" class="mr-1.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          New Customer
        </button>
      </div>
    </div>

    <!-- Summary Cards (Matching Perfex) -->
    <div class="summary-cards" v-if="showSummary">
      <div class="summary-card">
        <div class="summary-label">Total Customers</div>
        <div class="summary-value">{{ summary.total_customers || 0 }}</div>
      </div>
      <div class="summary-card">
        <div class="summary-label">Active Customers</div>
        <div class="summary-value text-success">{{ summary.active_customers || 0 }}</div>
      </div>
      <div class="summary-card">
        <div class="summary-label">Inactive Customers</div>
        <div class="summary-value text-danger">{{ summary.inactive_customers || 0 }}</div>
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
          <select class="select-sm" v-model="pagination.pageSize" @change="fetchCustomers">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
          <button class="btn-outline">Export</button>
          
          <div class="flex items-center space-x-2 border-l pl-3 ml-2">
            <span class="text-xs font-semibold text-slate-500 uppercase">Status:</span>
            <select class="select-sm" v-model="filterActive" @change="fetchCustomers">
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
            placeholder="Search company/vat/phone..." 
            @input="debounceSearch"
          />
        </div>
      </div>

      <!-- Data Table -->
      <a-table
        :columns="columns"
        :data-source="customers"
        :row-key="record => record.id"
        :pagination="pagination"
        :loading="loading"
        @change="handleTableChange"
        size="small"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'company'">
            <div class="flex flex-col">
              <span class="font-semibold text-slate-800 hover:text-indigo-600 cursor-pointer link-blue" @click="viewCustomer(record.id)">
                {{ record.company }}
              </span>
              <span class="text-[10px] text-slate-400 mt-0.5" v-if="record.website">
                <a :href="record.website" target="_blank" class="hover:underline">{{ record.website }}</a>
              </span>
            </div>
          </template>

          <template v-if="column.key === 'primary_contact'">
            <div v-if="record.contacts && record.contacts.length" class="text-slate-600">
              <div>{{ record.contacts[0].firstname }} {{ record.contacts[0].lastname }}</div>
              <div class="text-[10px] text-slate-400">{{ record.contacts[0].email }}</div>
            </div>
            <span v-else class="text-slate-300">—</span>
          </template>

          <template v-if="column.key === 'active'">
            <span class="badge" :class="record.active ? 'badge-green' : 'badge-red'">
              {{ record.active ? 'Active' : 'Inactive' }}
            </span>
          </template>

          <template v-if="column.key === 'actions'">
            <div class="flex items-center space-x-3">
              <a-button type="link" size="small" @click="viewCustomer(record.id)" class="text-indigo-600 p-0 hover:text-indigo-800 font-medium">View</a-button>
              <a-button type="link" size="small" @click="editCustomer(record)" class="text-slate-600 p-0 hover:text-slate-800 font-medium">Edit</a-button>
              <a-popconfirm
                title="Are you sure you want to delete this customer?"
                ok-text="Yes"
                cancel-text="No"
                @confirm="deleteCustomer(record.id)"
              >
                <a-button type="link" size="small" danger class="p-0 font-medium">Delete</a-button>
              </a-popconfirm>
            </div>
          </template>
        </template>
      </a-table>
    </div>

    <!-- Creation/Edit Drawer -->
    <a-drawer
      v-model:open="drawerVisible"
      :title="editMode ? 'Edit Customer' : 'Create New Customer'"
      placement="right"
      :width="560"
      :footer-style="{ textAlign: 'right' }"
      @close="closeDrawer"
    >
      <a-form :model="form" layout="vertical" ref="formRef">
        <!-- Section: Company Details -->
        <h3 class="text-xs uppercase font-bold text-slate-500 tracking-wider mb-4 pb-1.5 border-b border-slate-100">Company Details</h3>
        <div class="grid grid-cols-2 gap-4">
          <a-form-item label="Company Name" name="company" :rules="[{ required: true, message: 'Please input company name!' }]">
            <a-input v-model:value="form.company" placeholder="e.g. Acme Corp" />
          </a-form-item>
          <a-form-item label="VAT Number" name="vat">
            <a-input v-model:value="form.vat" placeholder="e.g. GB123456789" />
          </a-form-item>
          <a-form-item label="Phone Number" name="phonenumber">
            <a-input v-model:value="form.phonenumber" placeholder="e.g. +1 555-0199" />
          </a-form-item>
          <a-form-item label="Website" name="website">
            <a-input v-model:value="form.website" placeholder="e.g. https://acme.org" />
          </a-form-item>
        </div>

        <!-- Groups, Currency, Default Language -->
        <div class="grid grid-cols-3 gap-4 mt-2">
          <a-form-item label="Groups" name="groups">
            <a-select v-model:value="form.groups" mode="multiple" placeholder="Select groups" style="width: 100%">
              <a-select-option value="High Budget">High Budget</a-select-option>
              <a-select-option value="Low Budget">Low Budget</a-select-option>
              <a-select-option value="VIP">VIP</a-select-option>
              <a-select-option value="Wholesaler">Wholesaler</a-select-option>
            </a-select>
          </a-form-item>
          <a-form-item label="Currency" name="currency">
            <a-select v-model:value="form.currency" placeholder="USD" style="width: 100%">
              <a-select-option value="USD">USD</a-select-option>
              <a-select-option value="EUR">EUR</a-select-option>
              <a-select-option value="GBP">GBP</a-select-option>
              <a-select-option value="System Default">System Default</a-select-option>
            </a-select>
          </a-form-item>
          <a-form-item label="Default Language" name="default_language">
            <a-select v-model:value="form.default_language" placeholder="English" style="width: 100%">
              <a-select-option value="english">English</a-select-option>
              <a-select-option value="german">German</a-select-option>
              <a-select-option value="french">French</a-select-option>
              <a-select-option value="spanish">Spanish</a-select-option>
              <a-select-option value="system_default">System Default</a-select-option>
            </a-select>
          </a-form-item>
        </div>

        <a-form-item label="Billing Address" name="address" class="mt-2">
          <a-textarea v-model:value="form.address" :rows="3" placeholder="Street Address" />
        </a-form-item>

        <div class="grid grid-cols-4 gap-4">
          <a-form-item label="City" name="city">
            <a-input v-model:value="form.city" />
          </a-form-item>
          <a-form-item label="State" name="state">
            <a-input v-model:value="form.state" />
          </a-form-item>
          <a-form-item label="Zip Code" name="zip">
            <a-input v-model:value="form.zip" />
          </a-form-item>
          <a-form-item label="Country" name="country">
            <a-input v-model:value="form.country" placeholder="e.g. USA" />
          </a-form-item>
        </div>

        <div class="mt-4">
          <a-checkbox v-model:checked="form.active">Active Customer</a-checkbox>
        </div>

        <!-- Section: Primary Contact (Only shown during creation) -->
        <div v-if="!editMode" class="mt-8">
          <h3 class="text-xs uppercase font-bold text-slate-500 tracking-wider mb-4 pb-1.5 border-b border-slate-100">Primary Contact Details</h3>
          <div class="grid grid-cols-2 gap-4">
            <a-form-item label="First Name" name="contact_firstname" :rules="[{ required: !editMode, message: 'Please input first name!' }]">
              <a-input v-model:value="form.contact_firstname" />
            </a-form-item>
            <a-form-item label="Last Name" name="contact_lastname">
              <a-input v-model:value="form.contact_lastname" />
            </a-form-item>
            <a-form-item label="Email Address" name="contact_email" :rules="[{ required: !editMode, type: 'email', message: 'Please input a valid email!' }]">
              <a-input v-model:value="form.contact_email" placeholder="contact@company.com" />
            </a-form-item>
            <a-form-item label="Phone Number" name="contact_phone">
              <a-input v-model:value="form.contact_phone" />
            </a-form-item>
            <a-form-item label="Job Title" name="contact_title">
              <a-input v-model:value="form.contact_title" placeholder="e.g. CEO, Manager" />
            </a-form-item>
            <a-form-item label="Password" name="contact_password">
              <a-input-password v-model:value="form.contact_password" placeholder="For Portal Login" />
            </a-form-item>
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
import { useRouter } from 'vue-router';
import axios from 'axios';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'CustomersList',
  setup() {
    const router = useRouter();
    const customers = ref([]);
    const summary = ref({});
    const loading = ref(false);
    const submitLoading = ref(false);
    const searchQuery = ref('');
    const filterActive = ref('all');
    const showSummary = ref(true);
    let searchTimeout = null;
    
    // Drawer states
    const drawerVisible = ref(false);
    const editMode = ref(false);
    const currentEditId = ref(null);
    const formRef = ref(null);

    const form = reactive({
      company: '',
      vat: '',
      phonenumber: '',
      website: '',
      address: '',
      city: '',
      state: '',
      zip: '',
      country: '',
      active: true,
      default_language: 'system_default',
      groups: [],
      currency: 'USD',
      
      // Primary Contact
      contact_firstname: '',
      contact_lastname: '',
      contact_email: '',
      contact_phone: '',
      contact_title: '',
      contact_password: '',
    });

    const pagination = reactive({
      current: 1,
      pageSize: 10,
      total: 0,
      showSizeChanger: true,
      pageSizeOptions: ['10', '25', '50'],
      showTotal: (total) => `Total ${total} customers`,
    });

    const columns = [
      { title: 'Company Name', dataIndex: 'company', key: 'company' },
      { title: 'Primary Contact', key: 'primary_contact' },
      { title: 'Phone', dataIndex: 'phonenumber', key: 'phonenumber' },
      { title: 'Status', dataIndex: 'active', key: 'active', width: '100px' },
      { title: 'Actions', key: 'actions', width: '160px' },
    ];

    const fetchCustomers = async () => {
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

        const response = await axios.get('/clients', { params });
        
        // Parse nested API response
        customers.value = response.data.clients?.data || [];
        pagination.total = response.data.clients?.total || 0;
        summary.value = response.data.summary || {};
      } catch (err) {
        message.error('Failed to load customers.');
      } finally {
        loading.value = false;
      }
    };

    const handleTableChange = (paginationEvent) => {
      pagination.current = paginationEvent.current;
      pagination.pageSize = paginationEvent.pageSize;
      fetchCustomers();
    };

    const debounceSearch = () => {
      if (searchTimeout) clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        pagination.current = 1;
        fetchCustomers();
      }, 300);
    };

    const openCreateDrawer = () => {
      editMode.value = false;
      currentEditId.value = null;
      
      // Reset Form Fields
      Object.assign(form, {
        company: '',
        vat: '',
        phonenumber: '',
        website: '',
        address: '',
        city: '',
        state: '',
        zip: '',
        country: '',
        active: true,
        default_language: 'system_default',
        groups: [],
        currency: 'USD',
        
        contact_firstname: '',
        contact_lastname: '',
        contact_email: '',
        contact_phone: '',
        contact_title: '',
        contact_password: '',
      });
      
      drawerVisible.value = true;
    };

    const editCustomer = (record) => {
      editMode.value = true;
      currentEditId.value = record.id;
      
      Object.assign(form, {
        company: record.company,
        vat: record.vat,
        phonenumber: record.phonenumber,
        website: record.website,
        address: record.address,
        city: record.city,
        state: record.state,
        zip: record.zip,
        country: record.country,
        active: record.active === 1 || record.active === true,
        default_language: record.default_language || 'system_default',
        groups: record.groups ? record.groups.split(',') : [],
        currency: record.currency || 'USD',
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
          const payload = {
            ...form,
            groups: Array.isArray(form.groups) ? form.groups.join(',') : form.groups
          };
          if (editMode.value) {
            await axios.put(`/clients/${currentEditId.value}`, payload);
            message.success('Customer updated successfully.');
          } else {
            await axios.post('/clients', payload);
            message.success('Customer and primary contact created successfully.');
          }
          drawerVisible.value = false;
          fetchCustomers();
        } catch (err) {
          const errMsg = err.response?.data?.message || 'Error occurred while saving customer.';
          message.error(errMsg);
        } finally {
          submitLoading.value = false;
        }
      });
    };

    const deleteCustomer = async (id) => {
      try {
        await axios.delete(`/clients/${id}`);
        message.success('Customer deleted successfully.');
        fetchCustomers();
      } catch (err) {
        message.error('Failed to delete customer.');
      }
    };

    const viewCustomer = (id) => {
      router.push(`/admin/customers/${id}`);
    };

    const goToCreatePage = () => {
      router.push('/admin/customers/client');
    };

    onMounted(() => {
      fetchCustomers();
    });

    return {
      customers,
      summary,
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
      fetchCustomers,
      handleTableChange,
      debounceSearch,
      openCreateDrawer,
      editCustomer,
      closeDrawer,
      submitForm,
      deleteCustomer,
      viewCustomer,
      goToCreatePage,
    };
  },
});
</script>

<style scoped>
@import '@/main/module-shared.css';

.customers-list-page {
  font-family: 'Inter', -apple-system, sans-serif;
}
</style>
