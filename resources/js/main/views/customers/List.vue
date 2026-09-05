<template>
  <div class="customers-list-page space-y-6">
    
    <!-- Action Bar -->
    <div class="page-header flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="page-title text-xl font-bold text-slate-800">Customers</h1>
        <p class="text-xs text-slate-500 mt-0.5">Manage your client database and contact profiles</p>
      </div>
      <div class="flex items-center gap-2">
        <button class="btn-summary-premium" @click="showSummary = !showSummary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          {{ showSummary ? 'Hide Summary' : 'Show Summary' }}
        </button>
        <router-link to="/admin/customers/all-contacts" class="btn-contacts-premium">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Contacts
        </router-link>
        <button v-if="canCreate" class="btn-primary" @click="openCreateDrawer">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13" class="mr-1.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          New Customer
        </button>
      </div>
    </div>

    <!-- Summary Cards -->
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
              <a-button v-if="canEdit" type="link" size="small" @click="editCustomer(record)" class="text-slate-600 p-0 hover:text-slate-800 font-medium">Edit</a-button>
              <a-popconfirm
                v-if="canDelete"
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

      <!-- Mobile Responsive Card View -->
      <div class="mobile-cards-list" v-if="!loading">
        <div 
          v-for="c in customers" 
          :key="'m-c-' + c.id"
          class="mobile-row-card"
          @click="viewCustomer(c.id)"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="badge" :class="c.active ? 'badge-green' : 'badge-red'">
                {{ c.active ? 'Active' : 'Inactive' }}
              </span>
              <span class="font-extrabold text-xs text-indigo-600">#{{ c.id }}</span>
            </div>
            <div class="flex items-center gap-2">
              <button @click.stop="editCustomer(c)" class="text-xs font-semibold text-slate-600 hover:text-slate-900">Edit</button>
            </div>
          </div>

          <div class="font-bold text-sm text-slate-800 pt-1">
            {{ c.company }}
          </div>
          <div v-if="c.vat" class="text-xs text-slate-500">
            VAT: {{ c.vat }}
          </div>

          <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-100">
            <div class="flex items-center gap-1.5 text-slate-500 truncate col-span-2" v-if="c.contacts && c.contacts.length">
              <span class="text-slate-400">👤 Contact:</span>
              <span class="font-semibold text-slate-700 truncate">{{ c.contacts[0].firstname }} {{ c.contacts[0].lastname }} ({{ c.contacts[0].email }})</span>
            </div>
            <div class="flex items-center gap-1.5 text-slate-500" v-if="c.phonenumber">
              <span class="text-slate-400">📞 Phone:</span>
              <span>{{ c.phonenumber }}</span>
            </div>
            <div class="flex items-center justify-end gap-1.5 text-slate-500" v-if="c.city">
              <span class="text-slate-400">📍</span>
              <span>{{ c.city }}</span>
            </div>
          </div>
        </div>

        <div v-if="!customers.length" class="text-center p-6 text-slate-400 text-xs font-semibold">
          No customers found
        </div>
      </div>
    </div>

    <!-- Creation/Edit Drawer -->
    <a-drawer
      v-model:open="drawerVisible"
      :title="editMode ? 'Edit Customer' : 'Create New Customer'"
      placement="right"
      :width="600"
      :footer-style="{ textAlign: 'right' }"
      class="vuexy-customer-drawer"
      @close="closeDrawer"
    >
      <a-form :model="form" layout="vertical" ref="formRef" class="customer-vform">
        <!-- Section: Company Details -->
        <div class="drawer-section-header mb-4">
          <h6 class="text-base font-semibold text-slate-800 dark:text-slate-200 m-0">Company Details</h6>
          <p class="text-xs text-slate-400 m-0 mt-0.5">Enter organization and business profile</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-1">
          <a-form-item label="Company Name" name="company" :rules="[{ required: true, message: 'Please input company name!' }]">
            <a-input v-model:value="form.company" placeholder="e.g. Acme Corporation" />
          </a-form-item>
          <a-form-item label="VAT Number" name="vat">
            <a-input v-model:value="form.vat" placeholder="e.g. GB123456789" />
          </a-form-item>
          <a-form-item label="Phone Number" name="phonenumber">
            <a-input v-model:value="form.phonenumber" placeholder="e.g. +1 (555) 019-2834" />
          </a-form-item>
          <a-form-item label="Website" name="website">
            <a-input v-model:value="form.website" placeholder="e.g. https://acme.org" />
          </a-form-item>
        </div>

        <!-- Groups, Currency, Default Language -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-1 mt-1">
          <a-form-item label="Groups" name="groups">
            <a-select v-model:value="form.groups" mode="multiple" placeholder="Select groups" style="width: 100%">
              <a-select-option value="High Budget">High Budget</a-select-option>
              <a-select-option value="Low Budget">Low Budget</a-select-option>
              <a-select-option value="VIP">VIP</a-select-option>
              <a-select-option value="Wholesaler">Wholesaler</a-select-option>
            </a-select>
          </a-form-item>
          <a-form-item label="Currency" name="currency">
            <div class="flex items-center gap-1.5">
              <a-select v-model:value="form.currency" placeholder="USD" style="width: 100%">
                <a-select-option v-for="c in currencies" :key="c.code" :value="c.code">
                  {{ c.code }} ({{ c.symbol }})
                </a-select-option>
              </a-select>
              <button
                type="button"
                class="h-[38px] w-[38px] flex items-center justify-center bg-slate-50 hover:bg-indigo-50 text-indigo-600 border border-slate-200 hover:border-indigo-300 rounded-[6px] transition-all shrink-0 cursor-pointer"
                @click="showAddCurrencyModal = true"
                title="Add New Currency"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="15" height="15"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              </button>
            </div>
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

        <!-- Section: Billing Address -->
        <div class="drawer-section-header mb-4 mt-6">
          <h6 class="text-base font-semibold text-slate-800 dark:text-slate-200 m-0">Billing Address</h6>
          <p class="text-xs text-slate-400 m-0 mt-0.5">Enter primary billing and physical address</p>
        </div>

        <a-form-item label="Billing Address" name="address">
          <a-textarea v-model:value="form.address" :rows="3" placeholder="Street Address, Suite / Unit #" />
        </a-form-item>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-1">
          <a-form-item label="City" name="city">
            <a-input v-model:value="form.city" placeholder="City" />
          </a-form-item>
          <a-form-item label="State" name="state">
            <a-input v-model:value="form.state" placeholder="State / Province" />
          </a-form-item>
          <a-form-item label="Zip Code" name="zip">
            <a-input v-model:value="form.zip" placeholder="Zip / Postal" />
          </a-form-item>
          <a-form-item label="Country" name="country">
            <a-input v-model:value="form.country" placeholder="e.g. USA" />
          </a-form-item>
        </div>

        <div class="mt-2 mb-4">
          <a-checkbox v-model:checked="form.active">Active Customer</a-checkbox>
        </div>

        <!-- Section: Primary Contact (Only shown during creation) -->
        <div v-if="!editMode" class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700">
          <div class="drawer-section-header mb-4">
            <h6 class="text-base font-semibold text-slate-800 dark:text-slate-200 m-0">Primary Contact Details</h6>
            <p class="text-xs text-slate-400 m-0 mt-0.5">Add primary contact for customer portal login</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-1">
            <a-form-item label="First Name" name="contact_firstname" :rules="[{ required: !editMode, message: 'Please input first name!' }]">
              <a-input v-model:value="form.contact_firstname" placeholder="Carter" />
            </a-form-item>
            <a-form-item label="Last Name" name="contact_lastname">
              <a-input v-model:value="form.contact_lastname" placeholder="Leonardo" />
            </a-form-item>
            <a-form-item label="Email Address" name="contact_email" :rules="[{ required: !editMode, type: 'email', message: 'Please input a valid email!' }]">
              <a-input v-model:value="form.contact_email" placeholder="admin@test.com" />
            </a-form-item>
            <a-form-item label="Phone Number" name="contact_phone">
              <a-input v-model:value="form.contact_phone" placeholder="e.g. +1 (555) 019-2834" />
            </a-form-item>
            <a-form-item label="Job Title" name="contact_title">
              <a-input v-model:value="form.contact_title" placeholder="e.g. CEO, Manager" />
            </a-form-item>
            <a-form-item label="Password" name="contact_password">
              <div class="relative flex items-center">
                <input
                  :type="isPasswordVisible ? 'text' : 'password'"
                  v-model="form.contact_password"
                  placeholder="············"
                  class="ant-input w-full pr-10"
                />
                <button
                  type="button"
                  @click="isPasswordVisible = !isPasswordVisible"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 bg-transparent border-none p-0 cursor-pointer flex items-center justify-center transition-colors"
                  tabindex="-1"
                >
                  <svg v-if="!isPasswordVisible" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                  <svg v-else viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                </button>
              </div>
            </a-form-item>
          </div>
        </div>
      </a-form>

      <template #extra>
        <a-space>
          <a-button @click="closeDrawer" class="btn-ghost">Cancel</a-button>
          <a-button type="primary" :loading="submitLoading" @click="submitForm" class="btn-primary">
            {{ editMode ? 'Update Customer' : 'Create Customer' }}
          </a-button>
        </a-space>
      </template>
    </a-drawer>

    <!-- Add Currency Modal -->
    <a-modal
      v-model:open="showAddCurrencyModal"
      title="Add New Currency"
      :footer="null"
      :width="420"
      centered
      destroy-on-close
    >
      <div class="space-y-4 py-2">
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Currency Code (ISO) <span class="text-rose-500">*</span></label>
          <input
            type="text"
            v-model="newCurrency.code"
            placeholder="e.g. INR, AUD, AED, JPY"
            class="w-full h-10 px-3.5 text-xs font-semibold uppercase bg-slate-50 border border-slate-200 rounded-md focus:bg-white focus:border-indigo-500 outline-none"
          />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Symbol <span class="text-rose-500">*</span></label>
          <input
            type="text"
            v-model="newCurrency.symbol"
            placeholder="e.g. ₹, A$, AED, ¥"
            class="w-full h-10 px-3.5 text-xs font-semibold bg-slate-50 border border-slate-200 rounded-md focus:bg-white focus:border-indigo-500 outline-none"
          />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Decimal Separator</label>
            <input
              type="text"
              v-model="newCurrency.decimal_sep"
              placeholder="."
              class="w-full h-10 px-3.5 text-xs font-semibold bg-slate-50 border border-slate-200 rounded-md focus:bg-white focus:border-indigo-500 outline-none text-center"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Thousand Separator</label>
            <input
              type="text"
              v-model="newCurrency.thousand_sep"
              placeholder=","
              class="w-full h-10 px-3.5 text-xs font-semibold bg-slate-50 border border-slate-200 rounded-md focus:bg-white focus:border-indigo-500 outline-none text-center"
            />
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
          <button
            type="button"
            class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-md cursor-pointer transition-colors"
            @click="showAddCurrencyModal = false"
          >
            Cancel
          </button>
          <button
            type="button"
            class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-md cursor-pointer shadow-md shadow-indigo-500/20 transition-all"
            @click="saveNewCurrency"
          >
            Add Currency
          </button>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { message } from 'ant-design-vue';
import { useAuthStore } from '../../store/authStore';

export default defineComponent({
  name: 'CustomersList',
  setup() {
    const router = useRouter();
    const authStore = useAuthStore();
    const canCreate = computed(() => authStore.hasPermission('Customers', 'create'));
    const canEdit   = computed(() => authStore.hasPermission('Customers', 'edit'));
    const canDelete = computed(() => authStore.hasPermission('Customers', 'delete'));
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
    const isPasswordVisible = ref(false);

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

        const response = await axios.get('/api/clients', { params });
        
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
            await axios.put(`/api/clients/${currentEditId.value}`, payload);
            message.success('Customer updated successfully.');
          } else {
            await axios.post('/api/clients', payload);
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
        await axios.delete(`/api/clients/${id}`);
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
      openCreateDrawer();
    };

    // ── Currency Management ─────────────────────────────────────────
    const showAddCurrencyModal = ref(false);
    const currencies = ref([
      { code: 'USD', symbol: '$' },
      { code: 'EUR', symbol: '€' },
      { code: 'GBP', symbol: '£' },
      { code: 'CAD', symbol: '$' },
      { code: 'AUD', symbol: 'A$' },
      { code: 'INR', symbol: '₹' },
      { code: 'AED', symbol: 'AED' },
      { code: 'JPY', symbol: '¥' },
    ]);
    const newCurrency = reactive({
      code: '',
      symbol: '',
      decimal_sep: '.',
      thousand_sep: ',',
    });

    const saveNewCurrency = () => {
      if (!newCurrency.code || !newCurrency.symbol) {
        message.warning('Currency Code and Symbol are required.');
        return;
      }
      const codeUpper = newCurrency.code.trim().toUpperCase();
      const existing = currencies.value.find(c => c.code === codeUpper);
      if (!existing) {
        currencies.value.push({
          code: codeUpper,
          symbol: newCurrency.symbol.trim(),
          decimal_sep: newCurrency.decimal_sep || '.',
          thousand_sep: newCurrency.thousand_sep || ',',
        });
      }
      form.currency = codeUpper;
      showAddCurrencyModal.value = false;
      message.success(`Currency ${codeUpper} added!`);
      Object.assign(newCurrency, { code: '', symbol: '', decimal_sep: '.', thousand_sep: ',' });
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
      canCreate,
      canEdit,
      canDelete,
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
      
      showAddCurrencyModal,
      currencies,
      newCurrency,
      saveNewCurrency,
      isPasswordVisible,
    };
  },
});
</script>

<style scoped>
@import '@/main/module-shared.css';

.customers-list-page {
  font-family: 'Inter', -apple-system, sans-serif;
}

.btn-summary-premium {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: #fff;
  color: #1e293b;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  text-decoration: none;
  white-space: nowrap;
  transition: all 0.2s;
  line-height: 1.4;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.btn-summary-premium:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.btn-summary-premium:active {
  transform: translateY(0);
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.btn-summary-premium svg {
  flex-shrink: 0;
}

.btn-contacts-premium {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: linear-gradient(135deg, #1e293b, #0f172a);
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 9px 18px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  text-decoration: none;
  white-space: nowrap;
  box-shadow: 0 2px 8px rgba(15, 23, 42, 0.15);
  transition: all 0.2s;
  line-height: 1.4;
}
.btn-contacts-premium:hover {
  background: linear-gradient(135deg, #0f172a, #000);
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(15, 23, 42, 0.25);
  color: #fff;
}
.btn-contacts-premium:active {
  transform: translateY(0);
  box-shadow: 0 1px 4px rgba(15, 23, 42, 0.15);
}
.btn-contacts-premium svg {
  flex-shrink: 0;
}

/* Mobile Cards List Hidden by Default on Desktop */
.mobile-cards-list {
  display: none;
}

/* ==========================================================================
   VUEXY CUSTOMER DRAWER & INPUT OVERRIDES
   ========================================================================== */
:deep(.ant-drawer-content) {
  background-color: #FFFFFF !important;
}

:deep(.ant-drawer-header) {
  background-color: #FFFFFF !important;
  border-bottom: 1px solid #F1F0F2 !important;
  padding: 18px 24px !important;
}

:deep(.ant-drawer-title) {
  font-size: 16px !important;
  font-weight: 700 !important;
  color: #4B465C !important;
}

:deep(.ant-drawer-body) {
  padding: 24px !important;
  background-color: #FFFFFF !important;
}

.drawer-section-header h6 {
  color: #4B465C !important;
  font-family: 'Public Sans', 'Inter', -apple-system, sans-serif !important;
  font-size: 15px !important;
  font-weight: 600 !important;
}

.drawer-section-header p {
  color: #A8AAAE !important;
  font-size: 12px !important;
}

:deep(.ant-form-item) {
  margin-bottom: 16px !important;
}

:deep(.ant-form-item-label > label) {
  font-size: 12.5px !important;
  font-weight: 500 !important;
  color: #4B465C !important;
  margin-bottom: 4px !important;
  height: auto !important;
}

:deep(.ant-input),
:deep(.ant-input-password),
:deep(.ant-input-affix-wrapper),
:deep(.ant-select:not(.ant-select-customize-input) .ant-select-selector) {
  background-color: #FFFFFF !important;
  border: 1px solid #DBDADE !important;
  border-radius: 6px !important;
  color: #4B465C !important;
  font-size: 13.5px !important;
  font-family: inherit !important;
  box-shadow: none !important;
  transition: all 0.2s ease !important;
}

:deep(.ant-input:not(textarea)),
:deep(.ant-input-password),
:deep(.ant-input-affix-wrapper),
:deep(.ant-select-single:not(.ant-select-customize-input) .ant-select-selector) {
  min-height: 38px !important;
  height: 38px !important;
}

:deep(.ant-input) {
  padding: 7px 12px !important;
}

:deep(.ant-input:focus),
:deep(.ant-input-focused),
:deep(.ant-input-password:focus),
:deep(.ant-input-affix-wrapper-focused),
:deep(.ant-select-focused:not(.ant-select-disabled).ant-select:not(.ant-select-customize-input) .ant-select-selector) {
  border-color: #7367F0 !important;
  box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.16) !important;
  outline: 0 !important;
}

:deep(textarea.ant-input) {
  min-height: 72px !important;
  padding: 8px 12px !important;
}

:deep(.ant-checkbox-inner) {
  border-radius: 4px !important;
  border: 1.5px solid #DBDADE !important;
  width: 18px !important;
  height: 18px !important;
}

:deep(.ant-checkbox-checked .ant-checkbox-inner) {
  background-color: #7367F0 !important;
  border-color: #7367F0 !important;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 12px !important;
  }
  .summary-cards {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 10px !important;
  }
  .table-toolbar {
    flex-direction: column !important;
    align-items: stretch !important;
    gap: 12px !important;
  }
  .toolbar-left, .toolbar-right {
    width: 100% !important;
    flex-wrap: wrap !important;
  }
  .input-sm {
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
}
</style>

<style>
/* Un-scoped Global Drawer Overrides (Teleported to body) */
.vuexy-customer-drawer .ant-drawer-content,
.ant-drawer .vuexy-customer-drawer {
  background-color: #FFFFFF !important;
}

.vuexy-customer-drawer .ant-input,
.vuexy-customer-drawer .ant-input-affix-wrapper,
.vuexy-customer-drawer .ant-input-password,
.vuexy-customer-drawer .ant-select-selector,
.vuexy-customer-drawer input,
.vuexy-customer-drawer select {
  border-radius: 6px !important;
  border: 1px solid #DBDADE !important;
  box-shadow: none !important;
  background: #FFFFFF !important;
  color: #4B465C !important;
  font-size: 13.5px !important;
  min-height: 38px !important;
  height: 38px !important;
  padding: 0 12px !important;
  transition: all 0.2s ease !important;
}

.vuexy-customer-drawer .ant-input:focus,
.vuexy-customer-drawer .ant-input-focused,
.vuexy-customer-drawer .ant-input-affix-wrapper:focus,
.vuexy-customer-drawer .ant-input-affix-wrapper-focused,
.vuexy-customer-drawer .ant-select-focused .ant-select-selector,
.vuexy-customer-drawer input:focus,
.vuexy-customer-drawer select:focus {
  border-color: #7367F0 !important;
  box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.16) !important;
  outline: 0 !important;
}

.vuexy-customer-drawer textarea.ant-input,
.vuexy-customer-drawer textarea {
  border-radius: 6px !important;
  border: 1px solid #DBDADE !important;
  box-shadow: none !important;
  background: #FFFFFF !important;
  min-height: 72px !important;
  height: auto !important;
  padding: 8px 12px !important;
}
</style>
