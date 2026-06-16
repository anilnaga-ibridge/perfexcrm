<template>
  <div class="space-y-6">
    <!-- Breadcrumbs / Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-800">Leads</h1>
        <p class="text-xs text-slate-500 mt-0.5">Track and convert business opportunities</p>
      </div>
      <div class="flex items-center space-x-2">
        <!-- View Toggle -->
        <a-radio-group v-model:value="viewType" size="large" class="shadow-sm">
          <a-radio-button value="kanban">
            <span class="flex items-center">
              <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
              </svg>
              Kanban
            </span>
          </a-radio-button>
          <a-radio-button value="list">
            <span class="flex items-center">
              <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              List
            </span>
          </a-radio-button>
        </a-radio-group>

        <a-button type="primary" size="large" class="bg-indigo-600 border-indigo-600 hover:bg-indigo-700 font-semibold" @click="openCreateDrawer">
          <template #icon>
            <svg class="h-4 w-4 inline-block mr-1.5 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
          </template>
          New Lead
        </a-button>
      </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm grid grid-cols-1 sm:grid-cols-4 gap-4 items-center">
      <div>
        <a-input-search
          v-model:value="searchQuery"
          placeholder="Search leads..."
          size="small"
          @search="fetchLeads"
          allow-clear
          class="w-full"
        />
      </div>
      <div>
        <a-select v-model:value="filterSource" placeholder="Filter Source" size="small" class="w-full" @change="fetchLeads" allow-clear>
          <a-select-option v-for="source in metadata.sources" :key="source.id" :value="source.id">
            {{ source.name }}
          </a-select-option>
        </a-select>
      </div>
      <div>
        <a-select v-model:value="filterAssigned" placeholder="Filter Assigned" size="small" class="w-full" @change="fetchLeads" allow-clear>
          <a-select-option v-for="staff in metadata.staff" :key="staff.id" :value="staff.id">
            {{ staff.name }}
          </a-select-option>
        </a-select>
      </div>
      <div class="text-right">
        <a-button size="small" @click="resetFilters">Reset Filters</a-button>
      </div>
    </div>

    <!-- Kanban Board View -->
    <div v-if="viewType === 'kanban'" class="flex space-x-4 overflow-x-auto pb-4 scrollbar">
      <div
        v-for="column in kanbanColumns"
        :key="column.status_id"
        class="bg-[#eef2f6] p-3 rounded-lg w-72 shrink-0 border border-slate-200/60 flex flex-col max-h-[calc(100vh-250px)]"
      >
        <!-- Column Header -->
        <div class="flex justify-between items-center mb-3 pb-2 border-b border-slate-300/60">
          <div class="flex items-center space-x-1.5">
            <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: column.status_color }"></span>
            <span class="font-bold text-slate-800 text-xs tracking-tight truncate max-w-40">{{ column.status_name }}</span>
          </div>
          <div class="flex items-center space-x-2 text-[10px] text-slate-400 font-semibold">
            <span>{{ column.leads.length }} Leads</span>
            <span>|</span>
            <span class="text-slate-600 font-bold">${{ getColumnTotalValue(column.leads) }}</span>
          </div>
        </div>

        <!-- Cards Container -->
        <div class="flex-1 overflow-y-auto space-y-3 pr-1 scrollbar">
          <div
            v-for="lead in column.leads"
            :key="lead.id"
            class="bg-white p-4 rounded-md border border-slate-200 shadow-sm hover:shadow-md transition-shadow space-y-3.5 relative group"
          >
            <!-- Card Details -->
            <div>
              <div class="flex justify-between items-start">
                <span class="font-bold text-slate-800 text-xs hover:text-indigo-600 cursor-pointer" @click="editLead(lead)">
                  {{ lead.name }}
                </span>
                <span class="text-[10px] font-bold text-slate-700">${{ lead.lead_value || '0.00' }}</span>
              </div>
              <p class="text-[10px] text-slate-400 font-semibold mt-0.5" v-if="lead.company">{{ lead.company }}</p>
            </div>

            <!-- Meta attributes -->
            <div class="text-[10px] space-y-1 text-slate-500 border-t border-slate-100 pt-2.5">
              <div class="flex justify-between">
                <span>Source:</span>
                <span class="font-semibold text-slate-600">{{ lead.source?.name || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span>Assigned:</span>
                <span class="font-semibold text-slate-600">{{ lead.assigned?.name || 'Unassigned' }}</span>
              </div>
            </div>

            <!-- Quick Status Transition Dropdown -->
            <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-100">
              <span class="text-[9px] font-bold text-slate-400 uppercase">Move:</span>
              <a-select
                :value="lead.status_id"
                size="small"
                style="width: 120px; font-size: 10px;"
                @change="(val) => moveLeadStatus(lead.id, val)"
                class="text-[10px]"
              >
                <a-select-option v-for="status in metadata.statuses" :key="status.id" :value="status.id">
                  {{ status.name }}
                </a-select-option>
              </a-select>
            </div>
            
            <!-- Quick Actions -->
            <div class="absolute top-2 right-2 flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
              <button @click="editLead(lead)" class="text-slate-400 hover:text-slate-700">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
              </button>
            </div>
          </div>

          <div v-if="!column.leads.length" class="text-center py-8 text-slate-400 text-[10px] border border-dashed border-slate-300 rounded-md">
            No leads in this stage
          </div>
        </div>
      </div>
    </div>

    <!-- List View -->
    <div v-else class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
      <a-table
        :columns="tableColumns"
        :data-source="leads"
        :row-key="record => record.id"
        :pagination="pagination"
        :loading="loading"
        @change="handleTableChange"
        size="middle"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'name'">
            <div class="flex flex-col">
              <span class="font-semibold text-slate-800 hover:text-indigo-600 cursor-pointer" @click="editLead(record)">
                {{ record.name }}
              </span>
              <span class="text-[10px] text-slate-400 font-semibold mt-0.5" v-if="record.title">
                {{ record.title }}
              </span>
            </div>
          </template>

          <template v-if="column.key === 'status'">
            <span
              v-if="record.status"
              class="px-2.5 py-0.5 rounded text-[10px] font-semibold"
              :style="{ backgroundColor: record.status.color + '15', color: record.status.color, border: '1px solid ' + record.status.color + '40' }"
            >
              {{ record.status.name }}
            </span>
          </template>

          <template v-if="column.key === 'source'">
            <span class="text-slate-600">{{ record.source?.name || '-' }}</span>
          </template>

          <template v-if="column.key === 'assigned'">
            <span class="text-slate-600 font-medium">{{ record.assigned?.name || 'Unassigned' }}</span>
          </template>

          <template v-if="column.key === 'lead_value'">
            <span class="font-semibold text-slate-700">${{ record.lead_value }}</span>
          </template>

          <template v-if="column.key === 'actions'">
            <div class="flex items-center space-x-3">
              <a-button type="link" size="small" @click="editLead(record)" class="text-indigo-600 p-0 hover:text-indigo-800 font-medium">Edit</a-button>
              <a-popconfirm
                title="Are you sure you want to delete this lead?"
                ok-text="Yes"
                cancel-text="No"
                @confirm="deleteLead(record.id)"
              >
                <a-button type="link" size="small" danger class="p-0 font-medium">Delete</a-button>
              </a-popconfirm>
            </div>
          </template>
        </template>
      </a-table>
    </div>

    <!-- Lead Form Drawer -->
    <a-drawer
      v-model:open="drawerVisible"
      :title="editMode ? 'Edit Lead' : 'Create New Lead'"
      placement="right"
      width="600"
      @close="closeDrawer"
    >
      <a-form :model="form" layout="vertical" ref="formRef">
        <!-- Lead Basics -->
        <h3 class="text-xs uppercase font-bold text-slate-500 tracking-wider mb-4 pb-1.5 border-b border-slate-100">Lead Basics</h3>
        <div class="grid grid-cols-2 gap-4">
          <a-form-item label="Contact Name" name="name" :rules="[{ required: true, message: 'Please input contact name!' }]">
            <a-input v-model:value="form.name" placeholder="Jane Doe" />
          </a-form-item>
          <a-form-item label="Job Title" name="title">
            <a-input v-model:value="form.title" placeholder="e.g. Sales Director" />
          </a-form-item>
          <a-form-item label="Company Name" name="company">
            <a-input v-model:value="form.company" placeholder="e.g. Acme Corp" />
          </a-form-item>
          <a-form-item label="Lead Value ($)" name="lead_value">
            <a-input-number v-model:value="form.lead_value" style="width: 100%" :min="0" />
          </a-form-item>
        </div>

        <div class="grid grid-cols-3 gap-4 mt-2">
          <a-form-item label="Email Address" name="email" :rules="[{ type: 'email', message: 'Please input valid email!' }]">
            <a-input v-model:value="form.email" placeholder="jane@acme.org" />
          </a-form-item>
          <a-form-item label="Phone Number" name="phonenumber">
            <a-input v-model:value="form.phonenumber" placeholder="+1 555-0199" />
          </a-form-item>
          <a-form-item label="Website" name="website">
            <a-input v-model:value="form.website" placeholder="acme.org" />
          </a-form-item>
        </div>

        <!-- Pipeline fields -->
        <h3 class="text-xs uppercase font-bold text-slate-500 tracking-wider mt-6 mb-4 pb-1.5 border-b border-slate-100">Pipeline Details</h3>
        <div class="grid grid-cols-3 gap-4">
          <a-form-item label="Status" name="status_id" :rules="[{ required: true, message: 'Please select status!' }]">
            <a-select v-model:value="form.status_id" placeholder="Select Status">
              <a-select-option v-for="status in metadata.statuses" :key="status.id" :value="status.id">
                {{ status.name }}
              </a-select-option>
            </a-select>
          </a-form-item>
          
          <a-form-item label="Source" name="source_id" :rules="[{ required: true, message: 'Please select source!' }]">
            <a-select v-model:value="form.source_id" placeholder="Select Source">
              <a-select-option v-for="source in metadata.sources" :key="source.id" :value="source.id">
                {{ source.name }}
              </a-select-option>
            </a-select>
          </a-form-item>

          <a-form-item label="Assigned Staff" name="assigned_id">
            <a-select v-model:value="form.assigned_id" placeholder="Select Staff" allow-clear>
              <a-select-option v-for="staff in metadata.staff" :key="staff.id" :value="staff.id">
                {{ staff.name }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </div>

        <!-- Address and details -->
        <h3 class="text-xs uppercase font-bold text-slate-500 tracking-wider mt-6 mb-4 pb-1.5 border-b border-slate-100">Location Details</h3>
        <a-form-item label="Street Address" name="address">
          <a-textarea v-model:value="form.address" :rows="2" />
        </a-form-item>
        
        <div class="grid grid-cols-4 gap-4">
          <a-form-item label="City" name="city">
            <a-input v-model:value="form.city" />
          </a-form-item>
          <a-form-item label="State" name="state">
            <a-input v-model:value="form.state" />
          </a-form-item>
          <a-form-item label="Zip" name="zip">
            <a-input v-model:value="form.zip" />
          </a-form-item>
          <a-form-item label="Country" name="country">
            <a-input v-model:value="form.country" />
          </a-form-item>
        </div>

        <a-form-item label="Lead Description" name="description" class="mt-2">
          <a-textarea v-model:value="form.description" :rows="3" />
        </a-form-item>
      </a-form>

      <template #extra>
        <a-space>
          <a-button @click="closeDrawer">Cancel</a-button>
          <a-button type="primary" :loading="submitLoading" @click="submitForm" class="bg-indigo-600 border-indigo-600">
            Save Lead
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
  name: 'LeadsBoard',
  setup() {
    const viewType = ref('kanban');
    const leads = ref([]);
    const kanbanColumns = ref([]);
    const loading = ref(false);
    const submitLoading = ref(false);

    // Filters
    const searchQuery = ref('');
    const filterSource = ref(undefined);
    const filterAssigned = ref(undefined);

    // Metadata
    const metadata = reactive({
      statuses: [],
      sources: [],
      staff: [],
    });

    // Drawer / Form
    const drawerVisible = ref(false);
    const editMode = ref(false);
    const currentEditId = ref(null);
    const formRef = ref(null);

    const form = reactive({
      name: '',
      title: '',
      company: '',
      email: '',
      website: '',
      phonenumber: '',
      lead_value: 0,
      status_id: undefined,
      source_id: undefined,
      assigned_id: undefined,
      address: '',
      city: '',
      state: '',
      zip: '',
      country: '',
      description: '',
    });

    const pagination = reactive({
      current: 1,
      pageSize: 10,
      total: 0,
    });

    const tableColumns = [
      { title: 'Contact Name', key: 'name' },
      { title: 'Company', dataIndex: 'company', key: 'company' },
      { title: 'Email', dataIndex: 'email', key: 'email' },
      { title: 'Phone', dataIndex: 'phonenumber', key: 'phonenumber' },
      { title: 'Value', key: 'lead_value' },
      { title: 'Assigned', key: 'assigned' },
      { title: 'Status', key: 'status' },
      { title: 'Source', key: 'source' },
      { title: 'Actions', key: 'actions', width: '150px' },
    ];

    const fetchMetadata = async () => {
      try {
        const response = await axios.get('/lead-metadata');
        metadata.statuses = response.data.statuses;
        metadata.sources = response.data.sources;
        metadata.staff = response.data.staff;
      } catch (err) {
        message.error('Failed to load filters metadata.');
      }
    };

    const fetchLeads = async () => {
      loading.value = true;
      try {
        // Fetch Kanban grouped leads
        if (viewType.value === 'kanban') {
          const params = { kanban: 1 };
          if (searchQuery.value) params.search = searchQuery.value;
          if (filterSource.value) params.source_id = filterSource.value;
          if (filterAssigned.value) params.assigned_id = filterAssigned.value;

          const response = await axios.get('/leads', { params });
          kanbanColumns.value = response.data;
        } else {
          // Fetch paginated leads
          const params = {
            page: pagination.current,
            per_page: pagination.pageSize,
          };
          if (searchQuery.value) params.search = searchQuery.value;
          if (filterSource.value) params.source_id = filterSource.value;
          if (filterAssigned.value) params.assigned_id = filterAssigned.value;

          const response = await axios.get('/leads', { params });
          leads.value = response.data.data;
          pagination.total = response.data.total;
        }
      } catch (err) {
        message.error('Failed to load leads list.');
      } finally {
        loading.value = false;
      }
    };

    const handleTableChange = (paginationEvent) => {
      pagination.current = paginationEvent.current;
      pagination.pageSize = paginationEvent.pageSize;
      fetchLeads();
    };

    const getColumnTotalValue = (leadsList) => {
      const sum = leadsList.reduce((acc, curr) => acc + parseFloat(curr.lead_value || 0), 0);
      return sum.toFixed(2);
    };

    const moveLeadStatus = async (leadId, newStatusId) => {
      try {
        await axios.put(`/leads/${leadId}/status`, { status_id: newStatusId });
        message.success('Lead status updated.');
        fetchLeads();
      } catch (err) {
        message.error('Failed to update status.');
      }
    };

    const openCreateDrawer = () => {
      editMode.value = false;
      currentEditId.value = null;

      Object.assign(form, {
        name: '',
        title: '',
        company: '',
        email: '',
        website: '',
        phonenumber: '',
        lead_value: 0,
        status_id: metadata.statuses[0]?.id || undefined,
        source_id: metadata.sources[0]?.id || undefined,
        assigned_id: undefined,
        address: '',
        city: '',
        state: '',
        zip: '',
        country: '',
        description: '',
      });

      drawerVisible.value = true;
    };

    const editLead = (record) => {
      editMode.value = true;
      currentEditId.value = record.id;

      Object.assign(form, {
        name: record.name,
        title: record.title,
        company: record.company,
        email: record.email,
        website: record.website,
        phonenumber: record.phonenumber,
        lead_value: parseFloat(record.lead_value || 0),
        status_id: record.status_id,
        source_id: record.source_id,
        assigned_id: record.assigned_id || undefined,
        address: record.address,
        city: record.city,
        state: record.state,
        zip: record.zip,
        country: record.country,
        description: record.description,
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
          if (editMode.value) {
            await axios.put(`/leads/${currentEditId.value}`, form);
            message.success('Lead updated successfully.');
          } else {
            await axios.post('/leads', form);
            message.success('Lead created successfully.');
          }
          drawerVisible.value = false;
          fetchLeads();
        } catch (err) {
          message.error(err.response?.data?.message || 'Failed to save lead details.');
        } finally {
          submitLoading.value = false;
        }
      });
    };

    const deleteLead = async (id) => {
      try {
        await axios.delete(`/leads/${id}`);
        message.success('Lead deleted successfully.');
        fetchLeads();
      } catch (err) {
        message.error('Failed to delete lead.');
      }
    };

    const resetFilters = () => {
      searchQuery.value = '';
      filterSource.value = undefined;
      filterAssigned.value = undefined;
      fetchLeads();
    };

    onMounted(async () => {
      await fetchMetadata();
      await fetchLeads();
    });

    return {
      viewType,
      leads,
      kanbanColumns,
      loading,
      submitLoading,
      searchQuery,
      filterSource,
      filterAssigned,
      metadata,
      drawerVisible,
      editMode,
      form,
      formRef,
      pagination,
      tableColumns,
      fetchLeads,
      handleTableChange,
      getColumnTotalValue,
      moveLeadStatus,
      openCreateDrawer,
      editLead,
      closeDrawer,
      submitForm,
      deleteLead,
      resetFilters,
    };
  },
});
</script>

<style scoped>
.scrollbar::-webkit-scrollbar {
  height: 6px;
  width: 5px;
}
.scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 6px;
}
.scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
