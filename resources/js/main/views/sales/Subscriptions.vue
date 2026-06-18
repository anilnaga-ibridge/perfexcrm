<template>
  <div class="subscriptions-page">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">Subscriptions</h1>
      <button class="btn-primary" @click="openCreateDrawer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Subscription
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
      <div class="summary-card" v-for="s in summaryCards" :key="s.label">
        <div class="summary-label">{{ s.label }}</div>
        <div class="summary-value" :class="s.class">{{ s.value }}</div>
      </div>
    </div>

    <!-- Filters & Table Card -->
    <div class="table-card">
      <div class="table-toolbar">
        <div class="toolbar-left">
          <a-select v-model:value="filters.perPage" size="small" style="width:70px" @change="loadSubscriptions">
            <a-select-option :value="10">10</a-select-option>
            <a-select-option :value="25">25</a-select-option>
            <a-select-option :value="50">50</a-select-option>
          </a-select>
          <button class="btn-outline" @click="exportCSV">Export CSV</button>
        </div>
        <div class="toolbar-right">
          <a-select
            v-model:value="filters.status"
            size="small"
            style="width:140px"
            placeholder="Filter Status"
            allowClear
            @change="loadSubscriptions"
          >
            <a-select-option value="">All Statuses</a-select-option>
            <a-select-option value="active">Active</a-select-option>
            <a-select-option value="inactive">Inactive</a-select-option>
            <a-select-option value="cancelled">Cancelled</a-select-option>
          </a-select>
          <a-input-search
            v-model:value="filters.search"
            placeholder="Search by name, plan or client..."
            size="small"
            style="width:260px"
            @search="loadSubscriptions"
          />
        </div>
      </div>

      <!-- Table -->
      <a-table
        :dataSource="subscriptions"
        :columns="columns"
        :loading="loading"
        :pagination="pagination"
        @change="handleTableChange"
        row-key="id"
        size="small"
        :scroll="{ x: 1000 }"
      >
        <template #bodyCell="{ column, record, index }">
          <template v-if="column.key === 'index'">
            <span class="text-slate-400 text-xs">{{ pagination.pageSize * (pagination.current - 1) + index + 1 }}</span>
          </template>

          <template v-if="column.key === 'name'">
            <a class="sub-name" @click="openEditDrawer(record)">{{ record.name }}</a>
          </template>

          <template v-if="column.key === 'client'">
            <span>{{ record.client?.company || '—' }}</span>
          </template>

          <template v-if="column.key === 'project'">
            <span>{{ record.project?.name || '—' }}</span>
          </template>

          <template v-if="column.key === 'status'">
            <a-tag :color="statusColor(record.status)">
              {{ record.status }}
            </a-tag>
          </template>

          <template v-if="column.key === 'next_billing'">
            <span>{{ nextBillingCycle(record) }}</span>
          </template>

          <template v-if="column.key === 'start_date'">
            <span>{{ formatDate(record.start_date) }}</span>
          </template>

          <template v-if="column.key === 'last_sent'">
            <span>{{ record.last_sent ? formatDate(record.last_sent) : '—' }}</span>
          </template>

          <template v-if="column.key === 'actions'">
            <div class="row-actions">
              <a-dropdown :trigger="['click']">
                <button class="action-btn">
                  <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                </button>
                <template #overlay>
                  <a-menu>
                    <a-menu-item key="edit" @click="openEditDrawer(record)">Edit Subscription</a-menu-item>
                    <a-menu-item key="mark_active" v-if="record.status !== 'active'" @click="updateStatus(record, 'active')">Mark Active</a-menu-item>
                    <a-menu-item key="mark_inactive" v-if="record.status !== 'inactive'" @click="updateStatus(record, 'inactive')">Mark Inactive</a-menu-item>
                    <a-menu-item key="mark_cancelled" v-if="record.status !== 'cancelled'" @click="updateStatus(record, 'cancelled')">Cancel Subscription</a-menu-item>
                    <a-menu-divider />
                    <a-menu-item key="delete" @click="deleteSubscription(record.id)" danger>Delete</a-menu-item>
                  </a-menu>
                </template>
              </a-dropdown>
            </div>
          </template>
        </template>

        <template #emptyText>
          <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" width="48" height="48">
              <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 11-.57-8.38l5.67-5.67"/>
            </svg>
            <p>No subscriptions found</p>
          </div>
        </template>
      </a-table>
    </div>

    <!-- Subscription Drawer -->
    <a-drawer
      v-model:open="showDrawer"
      :title="isEdit ? 'Edit Subscription' : 'New Subscription'"
      placement="right"
      :width="460"
    >
      <a-form layout="vertical" :model="subForm" @finish="saveSubscription">
        <a-form-item label="Billing Plan" name="stripe_plan">
          <a-select
            v-model:value="subForm.stripe_plan"
            placeholder="Select a billing plan..."
            style="width:100%"
            allowClear
            @change="onBillingPlanChange"
          >
            <a-select-option v-for="p in billingPlans" :key="p.code" :value="p.code">{{ p.name }}</a-select-option>
          </a-select>
        </a-form-item>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <a-form-item label="Quantity" name="quantity">
            <a-input-number v-model:value="subForm.quantity" :min="1" style="width:100%" placeholder="1" />
          </a-form-item>

          <a-form-item label="First Billing Date" name="start_date" :rules="[{required:true,message:'Required'}]">
            <a-date-picker v-model:value="subForm.start_date" style="width:100%" value-format="YYYY-MM-DD" />
          </a-form-item>
        </div>

        <a-form-item label="Subscription Name" name="name" :rules="[{required:true,message:'Required'}]">
          <a-input v-model:value="subForm.name" placeholder="e.g. Monthly Retainer" />
        </a-form-item>

        <a-form-item label="Description" name="description">
          <a-textarea v-model:value="subForm.description" :rows="3" placeholder="Subscription description..." />
        </a-form-item>

        <a-form-item name="include_description">
          <a-checkbox v-model:checked="subForm.include_description">Include description in invoice item</a-checkbox>
        </a-form-item>

        <a-form-item label="Customer" name="client_id" :rules="[{required:true,message:'Select a client'}]">
          <a-select
            v-model:value="subForm.client_id"
            placeholder="Select customer..."
            style="width:100%"
            :disabled="isEdit"
            show-search
            :filter-option="filterClientOption"
          >
            <a-select-option v-for="c in clientOptions" :key="c.id" :value="c.id">{{ c.company }}</a-select-option>
          </a-select>
        </a-form-item>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <a-form-item label="Billing Amount" name="amount" :rules="[{required:true,message:'Required'}]">
            <a-input-number v-model:value="subForm.amount" :min="0" style="width:100%" placeholder="0.00" />
          </a-form-item>

          <a-form-item label="Currency" name="currency">
            <a-select v-model:value="subForm.currency" style="width:100%">
              <a-select-option value="USD">USD</a-select-option>
              <a-select-option value="EUR">EUR</a-select-option>
              <a-select-option value="GBP">GBP</a-select-option>
              <a-select-option value="INR">INR</a-select-option>
              <a-select-option value="AUD">AUD</a-select-option>
            </a-select>
          </a-form-item>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <a-form-item label="Billing Period" name="billing_period" :rules="[{required:true,message:'Required'}]">
            <a-select v-model:value="subForm.billing_period" style="width:100%">
              <a-select-option value="monthly">Monthly</a-select-option>
              <a-select-option value="yearly">Yearly</a-select-option>
              <a-select-option value="bi-weekly">Bi-weekly</a-select-option>
            </a-select>
          </a-form-item>

          <a-form-item label="Status" name="status" :rules="[{required:true,message:'Required'}]">
            <a-select v-model:value="subForm.status" style="width:100%">
              <a-select-option value="active">Active</a-select-option>
              <a-select-option value="inactive">Inactive</a-select-option>
              <a-select-option value="cancelled">Cancelled</a-select-option>
            </a-select>
          </a-form-item>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <a-form-item label="Tax 1 (Stripe)" name="tax_1">
            <a-input v-model:value="subForm.tax_1" placeholder="e.g. txr_1Hh..." />
          </a-form-item>

          <a-form-item label="Tax 2 (Stripe)" name="tax_2">
            <a-input v-model:value="subForm.tax_2" placeholder="e.g. txr_1Hh..." />
          </a-form-item>
        </div>

        <a-form-item label="Terms & Conditions" name="terms">
          <a-textarea v-model:value="subForm.terms" :rows="3" placeholder="Terms & conditions..." />
        </a-form-item>

        <div class="drawer-footer">
          <a-button @click="showDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">Save</a-button>
        </div>
      </a-form>
    </a-drawer>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'SubscriptionsPage',
  setup() {
    const loading = ref(false);
    const saving = ref(false);
    const subscriptions = ref([]);
    const stats = ref({});
    const showDrawer = ref(false);
    const isEdit = ref(false);
    const editingId = ref(null);
    const clientOptions = ref([]);

    const filters = reactive({ search: '', status: '', perPage: 25 });
    const pagination = reactive({ current: 1, pageSize: 25, total: 0, showTotal: (t) => `${t} subscriptions` });

    const subForm = reactive({
      client_id: null,
      name: '',
      amount: null,
      billing_period: 'monthly',
      status: 'active',
      start_date: null,
      stripe_plan: '',
    });

    const columns = [
      { title: '#',                  key: 'index',          width: 50 },
      { title: 'Subscription Name', key: 'name' },
      { title: 'Customer',           key: 'client' },
      { title: 'Project',            key: 'project',        width: 140 },
      { title: 'Status',             key: 'status',         width: 120 },
      { title: 'Next Billing Cycle', key: 'next_billing',   width: 150 },
      { title: 'Date Subscribed',    key: 'start_date',     width: 130 },
      { title: 'Last Sent',          key: 'last_sent',      width: 120 },
      { title: '',                   key: 'actions',        width: 60, align: 'center' },
    ];

    const summaryCards = computed(() => [
      { label: 'Total Subscriptions', value: stats.value.total || 0 },
      { label: 'Active',               value: stats.value.active || 0, class: 'text-success' },
      { label: 'Inactive',             value: stats.value.inactive || 0, class: 'text-warning' },
      { label: 'Cancelled',            value: stats.value.cancelled || 0, class: 'text-danger' },
      { label: 'Total Value / mo',     value: formatCurrency(stats.value.total_amount || 0) },
      { label: 'Active Value / mo',    value: formatCurrency(stats.value.active_amount || 0), class: 'text-success' },
    ]);

    const loadSubscriptions = async () => {
      loading.value = true;
      try {
        const res = await axios.get('/api/subscriptions', {
          params: {
            search: filters.search,
            status: filters.status,
            per_page: filters.perPage,
            page: pagination.current,
          }
        });
        subscriptions.value = res.data.subscriptions?.data || [];
        pagination.total = res.data.subscriptions?.total || 0;
        stats.value = res.data.stats || {};
      } catch (e) {
        message.error('Failed to load subscriptions');
      } finally {
        loading.value = false;
      }
    };

    const loadClients = async () => {
      try {
        const res = await axios.get('/api/clients', { params: { per_page: 200 } });
        clientOptions.value = res.data.clients?.data || [];
      } catch (e) {
        console.error('Failed to load clients', e);
      }
    };

    const openCreateDrawer = () => {
      isEdit.value = false;
      editingId.value = null;
      Object.assign(subForm, {
        client_id: null,
        name: '',
        amount: null,
        billing_period: 'monthly',
        status: 'active',
        start_date: new Date().toISOString().split('T')[0],
        stripe_plan: '',
      });
      loadClients();
      showDrawer.value = true;
    };

    const openEditDrawer = (record) => {
      isEdit.value = true;
      editingId.value = record.id;
      Object.assign(subForm, {
        client_id: record.client_id,
        name: record.name,
        amount: record.amount,
        billing_period: record.billing_period || 'monthly',
        status: record.status || 'active',
        start_date: record.start_date,
        stripe_plan: record.stripe_plan || '',
      });
      loadClients();
      showDrawer.value = true;
    };

    const saveSubscription = async () => {
      saving.value = true;
      try {
        if (isEdit.value) {
          await axios.put(`/api/subscriptions/${editingId.value}`, {
            name: subForm.name,
            amount: subForm.amount,
            billing_period: subForm.billing_period,
            status: subForm.status,
            start_date: subForm.start_date,
            stripe_plan: subForm.stripe_plan,
          });
          message.success('Subscription updated successfully');
        } else {
          await axios.post('/api/subscriptions', subForm);
          message.success('Subscription created successfully');
        }
        showDrawer.value = false;
        loadSubscriptions();
      } catch (e) {
        const errors = e.response?.data?.errors;
        if (errors) {
          message.error(Object.values(errors).flat().join(', '));
        } else {
          message.error('Failed to save subscription');
        }
      } finally {
        saving.value = false;
      }
    };

    const updateStatus = async (record, status) => {
      try {
        await axios.put(`/api/subscriptions/${record.id}`, { status });
        message.success(`Status updated to ${status}`);
        loadSubscriptions();
      } catch {
        message.error('Failed to update status');
      }
    };

    const deleteSubscription = async (id) => {
      try {
        await axios.delete(`/api/subscriptions/${id}`);
        message.success('Subscription deleted');
        loadSubscriptions();
      } catch {
        message.error('Failed to delete subscription');
      }
    };

    const handleTableChange = (pag) => {
      pagination.current = pag.current;
      loadSubscriptions();
    };

    const statusColor = (s) => ({
      active:    'green',
      inactive:  'orange',
      cancelled: 'red',
    }[s] || 'default');

    const formatCurrency = (val) => {
      if (val === undefined || val === null) return '$0.00';
      return '$' + parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2 });
    };

    const formatDate = (d) => {
      if (!d) return '—';
      return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    };

    const nextBillingCycle = (sub) => {
      if (!sub.start_date) return '—';
      const d = new Date(sub.start_date);
      if (isNaN(d.getTime())) return '—';
      const period = sub.billing_period || 'monthly';
      if (period === 'yearly') d.setFullYear(d.getFullYear() + 1);
      else if (period === 'bi-weekly') d.setDate(d.getDate() + 14);
      else d.setMonth(d.getMonth() + 1);
      return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    };

    const exportCSV = () => {
      if (!subscriptions.value.length) return message.warning('No subscriptions to export');
      const headers = ['#', 'Subscription Name', 'Customer', 'Project', 'Status', 'Next Billing Cycle', 'Date Subscribed', 'Last Sent'];
      const rows = subscriptions.value.map((s, i) => [
        i + 1,
        s.name || '',
        s.client?.company || '',
        s.project?.name || '',
        s.status || '',
        nextBillingCycle(s),
        formatDate(s.start_date),
        s.last_sent ? formatDate(s.last_sent) : 'Never',
      ]);
      const csvContent = "data:text/csv;charset=utf-8," 
        + [headers.join(','), ...rows.map(e => e.map(val => `"${String(val).replace(/"/g, '""')}"`).join(','))].join('\n');
      
      const encodedUri = encodeURI(csvContent);
      const link = document.createElement("a");
      link.setAttribute("href", encodedUri);
      link.setAttribute("download", "subscriptions_export.csv");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    };

    onMounted(() => {
      loadSubscriptions();
    });

    return {
      loading, saving, subscriptions, stats, filters, pagination, columns,
      summaryCards, subForm, showDrawer, isEdit, clientOptions,
      openCreateDrawer, openEditDrawer, saveSubscription, updateStatus, deleteSubscription, handleTableChange,
      statusColor, formatCurrency, formatDate, nextBillingCycle, exportCSV
    };
  }
});
</script>

<style scoped>
.subscriptions-page {
  font-family: 'Inter', -apple-system, sans-serif;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 18px;
}
.page-title {
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
.btn-primary {
  background: #0d6efd;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 7px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: inherit;
  transition: background 0.12s;
}
.btn-primary:hover { background: #0b5ed7; }
.btn-primary svg { width: 14px; height: 14px; }

/* Summary Cards */
.summary-cards {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 18px;
}
.summary-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 12px 18px;
  min-width: 110px;
  flex: 1;
}
.summary-label {
  font-size: 11px;
  color: #94a3b8;
  font-weight: 500;
  margin-bottom: 4px;
}
.summary-value {
  font-size: 18px;
  font-weight: 700;
  color: #1e293b;
}
.text-danger  { color: #ef4444; }
.text-warning { color: #f59e0b; }
.text-success { color: #22c55e; }

/* Table */
.table-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
}
.table-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid #f1f5f9;
  flex-wrap: wrap;
  gap: 10px;
}
.toolbar-left, .toolbar-right {
  display: flex;
  align-items: center;
  gap: 8px;
}
.btn-outline {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 5px;
  padding: 4px 12px;
  font-size: 12.5px;
  color: #475569;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.12s;
}
.btn-outline:hover { background: #f8fafc; }

.sub-name {
  color: #0d6efd;
  font-weight: 600;
  cursor: pointer;
}
.stripe-plan {
  font-family: monospace;
  font-size: 12px;
  color: #64748b;
}
.amount {
  font-weight: 600;
  color: #1e293b;
}
.action-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  display: flex;
  align-items: center;
  padding: 4px;
  border-radius: 4px;
}
.action-btn:hover { background: #f1f5f9; color: #475569; }
.row-actions { display: flex; justify-content: center; }

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40px 0;
  gap: 8px;
  color: #94a3b8;
  font-size: 13px;
}

.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
}
</style>
