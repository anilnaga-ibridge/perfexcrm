<template>
  <div class="credit-notes-page">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">Credit Notes</h1>
      <router-link :to="{ name: 'admin.credit-notes.create' }" class="btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Credit Note
      </router-link>
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
          <a-select v-model:value="filters.perPage" size="small" style="width:70px" @change="loadCreditNotes">
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
            @change="loadCreditNotes"
          >
            <a-select-option value="">All Statuses</a-select-option>
            <a-select-option value="Open">Open</a-select-option>
            <a-select-option value="Closed">Closed</a-select-option>
            <a-select-option value="Void">Void</a-select-option>
          </a-select>
          <a-input-search
            v-model:value="filters.search"
            placeholder="Search by CN #, reference or client..."
            size="small"
            style="width:260px"
            @search="loadCreditNotes"
          />
        </div>
      </div>

      <!-- Table -->
      <a-table
        :dataSource="creditNotes"
        :columns="columns"
        :loading="loading"
        :pagination="pagination"
        @change="handleTableChange"
        row-key="id"
        size="small"
        :scroll="{ x: 800 }"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'number'">
            <router-link :to="{ name: 'admin.credit-notes.edit', params: { id: record.id } }" class="cn-number">{{ record.number }}</router-link>
          </template>

          <template v-if="column.key === 'client'">
            <span>{{ record.client?.company || '—' }}</span>
          </template>

          <template v-if="column.key === 'status'">
            <a-tag :color="statusColor(record.status)">
              {{ record.status }}
            </a-tag>
          </template>

          <template v-if="column.key === 'amount'">
            <span class="amount">{{ formatCurrency(record.amount) }}</span>
          </template>

          <template v-if="column.key === 'date'">
            <span>{{ formatDate(record.date) }}</span>
          </template>

          <template v-if="column.key === 'actions'">
            <div class="row-actions">
              <a-dropdown :trigger="['click']">
                <button class="action-btn">
                  <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                </button>
                <template #overlay>
                  <a-menu>
                    <a-menu-item key="edit" @click="$router.push({ name: 'admin.credit-notes.edit', params: { id: record.id } })">Edit Credit Note</a-menu-item>
                    <a-menu-item key="mark_open" v-if="record.status !== 'Open'" @click="updateStatus(record, 'Open')">Mark as Open</a-menu-item>
                    <a-menu-item key="mark_closed" v-if="record.status !== 'Closed'" @click="updateStatus(record, 'Closed')">Mark as Closed</a-menu-item>
                    <a-menu-item key="mark_void" v-if="record.status !== 'Void'" @click="updateStatus(record, 'Void')">Mark as Void</a-menu-item>
                    <a-menu-divider />
                    <a-menu-item key="delete" @click="deleteCreditNote(record.id)" danger>Delete</a-menu-item>
                  </a-menu>
                </template>
              </a-dropdown>
            </div>
          </template>
        </template>

        <template #emptyText>
          <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" width="48" height="48">
              <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
            </svg>
            <p>No credit notes found</p>
          </div>
        </template>
      </a-table>
    </div>

    <!-- Credit Note Drawer -->
    <a-drawer
      v-model:open="showDrawer"
      :title="isEdit ? 'Edit Credit Note' : 'New Credit Note'"
      placement="right"
      :width="460"
    >
      <a-form layout="vertical" :model="cnForm" @finish="saveCreditNote">
        <a-form-item label="Client" name="client_id" :rules="[{required:true,message:'Select a client'}]">
          <a-select v-model:value="cnForm.client_id" placeholder="Select client..." style="width:100%" :disabled="isEdit">
            <a-select-option v-for="c in clientOptions" :key="c.id" :value="c.id">{{ c.company }}</a-select-option>
          </a-select>
        </a-form-item>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <a-form-item label="Credit Note Date" name="date" :rules="[{required:true,message:'Required'}]">
            <a-date-picker v-model:value="cnForm.date" style="width:100%" value-format="YYYY-MM-DD" />
          </a-form-item>

          <a-form-item label="Credit Note #" name="number">
            <a-input v-model:value="cnForm.number" placeholder="CN-00001 (Auto-generated if blank)" :disabled="isEdit" />
          </a-form-item>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <a-form-item label="Amount ($)" name="amount" :rules="[{required:true,message:'Required'}]">
            <a-input-number v-model:value="cnForm.amount" :min="0" style="width:100%" placeholder="0.00" />
          </a-form-item>

          <a-form-item label="Status" name="status" :rules="[{required:true,message:'Required'}]">
            <a-select v-model:value="cnForm.status" style="width:100%">
              <a-select-option value="Open">Open</a-select-option>
              <a-select-option value="Closed">Closed</a-select-option>
              <a-select-option value="Void">Void</a-select-option>
            </a-select>
          </a-form-item>
        </div>

        <a-form-item label="Reference" name="reference">
          <a-input v-model:value="cnForm.reference" placeholder="PO Number / Invoice Ref" />
        </a-form-item>

        <a-form-item label="Admin Note" name="admin_note">
          <a-textarea v-model:value="cnForm.admin_note" :rows="3" placeholder="Admin notes (Internal)..." />
        </a-form-item>

        <a-form-item label="Terms & Conditions" name="terms">
          <a-textarea v-model:value="cnForm.terms" :rows="3" placeholder="Credit Note terms and conditions..." />
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
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'CreditNotesPage',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const loading = ref(false);
    const saving = ref(false);
    const creditNotes = ref([]);
    const stats = ref({});
    const showDrawer = ref(false);
    const isEdit = ref(false);
    const editingId = ref(null);
    const clientOptions = ref([]);

    const filters = reactive({ search: '', status: '', perPage: 25 });
    const pagination = reactive({ current: 1, pageSize: 25, total: 0, showTotal: (t) => `${t} credit notes` });

    const cnForm = reactive({
      client_id: null,
      number: '',
      amount: null,
      date: null,
      status: 'Open',
      reference: '',
      admin_note: '',
      terms: '',
    });

    const columns = [
      { title: 'Credit Note #', key: 'number',    width: 130 },
      { title: 'Client',        key: 'client' },
      { title: 'Status',        key: 'status',    width: 120 },
      { title: 'Amount',        key: 'amount',    width: 120, align: 'right' },
      { title: 'Date',          key: 'date',      width: 120 },
      { title: '',              key: 'actions',   width: 60, align: 'center' },
    ];

    const summaryCards = computed(() => [
      { label: 'Total Credit Notes', value: stats.value.total || 0 },
      { label: 'Open Credits',       value: stats.value.open || 0, class: 'text-warning' },
      { label: 'Closed Credits',     value: stats.value.closed || 0, class: 'text-success' },
      { label: 'Void Credits',       value: stats.value.void || 0, class: 'text-danger' },
      { label: 'Total Value',        value: formatCurrency(stats.value.total_amount || 0) },
      { label: 'Available Credit',   value: formatCurrency(stats.value.open_amount || 0), class: 'text-warning' },
    ]);

    const loadCreditNotes = async () => {
      loading.value = true;
      try {
        const res = await axios.get('/api/credit-notes', {
          params: {
            search: filters.search,
            status: filters.status,
            per_page: filters.perPage,
            page: pagination.current,
          }
        });
        creditNotes.value = res.data.credit_notes?.data || [];
        pagination.total = res.data.credit_notes?.total || 0;
        stats.value = res.data.stats || {};
      } catch (e) {
        message.error('Failed to load credit notes');
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
      Object.assign(cnForm, {
        client_id: null,
        number: '',
        amount: null,
        date: new Date().toISOString().split('T')[0],
        status: 'Open',
        reference: '',
        admin_note: '',
        terms: '',
      });
      loadClients();
      showDrawer.value = true;
    };

    const openEditDrawer = (record) => {
      isEdit.value = true;
      editingId.value = record.id;
      Object.assign(cnForm, {
        client_id: record.client_id,
        number: record.number,
        amount: record.amount,
        date: record.date,
        status: record.status || 'Open',
        reference: record.reference || '',
        admin_note: record.admin_note || '',
        terms: record.terms || '',
      });
      loadClients();
      showDrawer.value = true;
    };

    const saveCreditNote = async () => {
      saving.value = true;
      try {
        if (isEdit.value) {
          await axios.put(`/api/credit-notes/${editingId.value}`, {
            status: cnForm.status,
            reference: cnForm.reference,
            admin_note: cnForm.admin_note,
            terms: cnForm.terms,
            amount: cnForm.amount,
          });
          message.success('Credit Note updated successfully');
        } else {
          await axios.post('/api/credit-notes', cnForm);
          message.success('Credit Note created successfully');
        }
        showDrawer.value = false;
        loadCreditNotes();
      } catch (e) {
        const errors = e.response?.data?.errors;
        if (errors) {
          message.error(Object.values(errors).flat().join(', '));
        } else {
          message.error('Failed to save credit note');
        }
      } finally {
        saving.value = false;
      }
    };

    const updateStatus = async (record, status) => {
      try {
        await axios.put(`/api/credit-notes/${record.id}`, { status });
        message.success(`Status updated to ${status}`);
        loadCreditNotes();
      } catch {
        message.error('Failed to update status');
      }
    };

    const deleteCreditNote = async (id) => {
      try {
        await axios.delete(`/api/credit-notes/${id}`);
        message.success('Credit Note deleted');
        loadCreditNotes();
      } catch {
        message.error('Failed to delete credit note');
      }
    };

    const handleTableChange = (pag) => {
      pagination.current = pag.current;
      loadCreditNotes();
    };

    const statusColor = (s) => ({
      Open:   'orange',
      Closed: 'green',
      Void:   'red',
    }[s] || 'default');

    const formatCurrency = (val) => {
      if (val === undefined || val === null) return '$0.00';
      return '$' + parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2 });
    };

    const formatDate = (d) => {
      if (!d) return '—';
      return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    };

    const exportCSV = () => {
      if (!creditNotes.value.length) return message.warning('No credit notes to export');
      const headers = ['Credit Note #', 'Client', 'Status', 'Amount', 'Date', 'Reference'];
      const rows = creditNotes.value.map(c => [
        c.number || '',
        c.client?.company || '',
        c.status || '',
        c.amount || 0,
        c.date || '',
        c.reference || '',
      ]);
      const csvContent = "data:text/csv;charset=utf-8," 
        + [headers.join(','), ...rows.map(e => e.map(val => `"${String(val).replace(/"/g, '""')}"`).join(','))].join('\n');
      
      const encodedUri = encodeURI(csvContent);
      const link = document.createElement("a");
      link.setAttribute("href", encodedUri);
      link.setAttribute("download", "credit_notes_export.csv");
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    };

    onMounted(async () => {
      await loadCreditNotes();
      if (route.query.create === 'true' || route.query.client_id) {
        router.push({
          name: 'admin.credit-notes.create',
          query: { client_id: route.query.client_id }
        });
      }
    });

    return {
      loading, saving, creditNotes, stats, filters, pagination, columns,
      summaryCards, cnForm, showDrawer, isEdit, clientOptions,
      openCreateDrawer, openEditDrawer, saveCreditNote, updateStatus, deleteCreditNote, handleTableChange,
      statusColor, formatCurrency, formatDate, exportCSV
    };
  }
});
</script>

<style scoped>
.credit-notes-page {
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

.cn-number {
  color: #0d6efd;
  font-weight: 600;
  cursor: pointer;
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
