<template>
  <div class="payments-page">

    <!-- ── Page Header ── -->
    <div class="page-header">
      <div class="header-left">
        <h1 class="page-title">Payments Received</h1>
        <span class="page-subtitle">All recorded invoice payments</span>
      </div>
      <div class="header-right">
        <button class="btn-outline" @click="exportCSV">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Export CSV
        </button>
        <button class="btn-primary" @click="$router.push({ name: 'admin.payments.create' })">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Record Payment
        </button>
      </div>
    </div>

    <!-- ── Summary Cards ── -->
    <div class="summary-cards">
      <div class="sum-card">
        <div class="sum-icon sum-total"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg></div>
        <div>
          <div class="sum-val">{{ stats.total || 0 }}</div>
          <div class="sum-label">Total Payments</div>
        </div>
      </div>
      <div class="sum-card">
        <div class="sum-icon sum-collected"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div>
        <div>
          <div class="sum-val green">{{ formatCurrency(stats.total_amount || 0) }}</div>
          <div class="sum-label">Total Collected</div>
        </div>
      </div>
      <div class="sum-card">
        <div class="sum-icon sum-bank"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="3" y="2" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg></div>
        <div>
          <div class="sum-val">{{ formatCurrency(stats.bank_amount || 0) }}</div>
          <div class="sum-label">Bank Transfer</div>
        </div>
      </div>
      <div class="sum-card">
        <div class="sum-icon sum-stripe"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><circle cx="7" cy="15" r="1"/></svg></div>
        <div>
          <div class="sum-val">{{ formatCurrency(stats.stripe_amount || 0) }}</div>
          <div class="sum-label">Stripe</div>
        </div>
      </div>
      <div class="sum-card">
        <div class="sum-icon sum-paypal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M7 11l-2 9h3l1-4h4c4 0 6-2 6-6 0-3-2-5-6-5H7l-1 5z"/></svg></div>
        <div>
          <div class="sum-val">{{ formatCurrency(stats.paypal_amount || 0) }}</div>
          <div class="sum-label">PayPal</div>
        </div>
      </div>
      <div class="sum-card">
        <div class="sum-icon sum-cash"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/></svg></div>
        <div>
          <div class="sum-val">{{ formatCurrency(stats.cash_amount || 0) }}</div>
          <div class="sum-label">Cash</div>
        </div>
      </div>
    </div>

    <!-- ── Table Card ── -->
    <div class="table-card">
      <div class="table-toolbar">
        <div class="toolbar-left">
          <a-select v-model:value="filters.perPage" size="small" style="width:70px" @change="loadPayments">
            <a-select-option :value="10">10</a-select-option>
            <a-select-option :value="25">25</a-select-option>
            <a-select-option :value="50">50</a-select-option>
            <a-select-option :value="100">100</a-select-option>
          </a-select>
          <span class="entry-count">{{ pagination.total }} payments</span>
        </div>
        <div class="toolbar-right">
          <a-select
            v-model:value="filters.paymentmode"
            size="small"
            style="width:150px"
            placeholder="All Modes"
            allowClear
            @change="loadPayments"
          >
            <a-select-option value="">All Modes</a-select-option>
            <a-select-option value="Bank">Bank Transfer</a-select-option>
            <a-select-option value="PayPal">PayPal</a-select-option>
            <a-select-option value="Stripe">Stripe</a-select-option>
            <a-select-option value="Cash">Cash</a-select-option>
            <a-select-option value="Cheque">Cheque</a-select-option>
            <a-select-option value="Credit Card">Credit Card</a-select-option>
          </a-select>
          <a-input-search
            v-model:value="filters.search"
            placeholder="Search transaction, invoice, client..."
            size="small"
            style="width:280px"
            @search="loadPayments"
            allow-clear
          />
        </div>
      </div>

      <!-- Table -->
      <div class="table-wrapper" v-if="!loading">
        <table class="pay-table">
          <thead>
            <tr>
              <th>Payment #</th>
              <th>Invoice</th>
              <th>Client</th>
              <th>Payment Mode</th>
              <th>Transaction ID</th>
              <th class="text-right">Amount</th>
              <th>Date</th>
              <th style="width:50px"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in payments" :key="p.id" class="pay-row" @click="viewPayment(p)">
              <td>
                <span class="pay-num">PAY-{{ String(p.id).padStart(5,'0') }}</span>
              </td>
              <td>
                <span v-if="p.invoice" class="inv-link" @click.stop="viewPayment(p)">{{ p.invoice.number }}</span>
                <span v-else class="muted">—</span>
              </td>
              <td @click.stop>
                <span class="client-name">
                  <router-link
                    v-if="p.invoice?.client?.id"
                    :to="{ name: 'admin.customers.view', params: { id: p.invoice.client.id } }"
                    class="client-link"
                  >
                    {{ p.invoice.client.company }}
                  </router-link>
                  <span v-else>{{ p.invoice?.client?.company || '—' }}</span>
                </span>
              </td>
              <td>
                <span class="mode-badge" :class="'mode-' + modeKey(p.paymentmode)">{{ p.paymentmode || 'Other' }}</span>
              </td>
              <td>
                <span class="txn-id">{{ p.transactionid || '—' }}</span>
              </td>
              <td class="text-right">
                <span class="amount-val">{{ formatCurrency(p.amount) }}</span>
              </td>
              <td class="muted-date">{{ formatDate(p.date) }}</td>
              <td class="action-td" @click.stop>
                <a-dropdown :trigger="['click']" placement="bottomRight">
                  <button class="three-dots-btn">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="15" height="15"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
                  </button>
                  <template #overlay>
                    <a-menu>
                      <a-menu-item key="view" @click="viewPayment(p)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="margin-right:6px;vertical-align:middle"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        View Receipt
                      </a-menu-item>
                      <a-menu-item key="edit" @click="editPayment(p)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="margin-right:6px;vertical-align:middle"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit
                      </a-menu-item>
                      <a-menu-divider />
                      <a-menu-item key="delete" @click="deletePayment(p.id)" style="color:#ef4444">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="margin-right:6px;vertical-align:middle"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                        Delete
                      </a-menu-item>
                    </a-menu>
                  </template>
                </a-dropdown>
              </td>
            </tr>
            <tr v-if="!payments.length">
              <td colspan="8">
                <div class="empty-state">
                  <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" width="48" height="48"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                  <p>No payments recorded yet</p>
                  <button class="btn-primary" @click="$router.push({ name: 'admin.payments.create' })">Record First Payment</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="loading" class="table-loading">
        <a-spin />
        <span>Loading payments...</span>
      </div>

      <!-- Pagination -->
      <div class="table-footer" v-if="pagination.total > filters.perPage">
        <a-pagination
          v-model:current="pagination.current"
          :pageSize="pagination.pageSize"
          :total="pagination.total"
          size="small"
          :show-size-changer="false"
          @change="(page) => { pagination.current = page; loadPayments(); }"
        />
      </div>
    </div>

  </div>
</template>

<script>
import { defineComponent, ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { message, Modal } from 'ant-design-vue';

export default defineComponent({
  name: 'PaymentsPage',
  setup() {
    const router  = useRouter();
    const loading = ref(false);
    const payments = ref([]);
    const stats    = ref({});

    const filters    = reactive({ search: '', paymentmode: '', perPage: 25 });
    const pagination = reactive({ current: 1, pageSize: 25, total: 0 });

    const loadPayments = async () => {
      loading.value = true;
      try {
        const res = await axios.get('/api/payments', {
          params: {
            search: filters.search,
            paymentmode: filters.paymentmode,
            per_page: filters.perPage,
            page: pagination.current,
          }
        });
        payments.value      = res.data.payments?.data || [];
        pagination.total    = res.data.payments?.total || 0;
        pagination.pageSize = filters.perPage;
        stats.value         = res.data.stats || {};
      } catch {
        message.error('Failed to load payments');
      } finally {
        loading.value = false;
      }
    };

    const viewPayment = (p) => {
      router.push({ name: 'admin.payments.view', params: { id: p.id } });
    };

    const editPayment = (p) => {
      router.push({ name: 'admin.payments.edit', params: { id: p.id } });
    };

    const deletePayment = (id) => {
      Modal.confirm({
        title:   'Delete this payment?',
        content: 'This action cannot be undone. Invoice status will be updated.',
        okText:  'Delete',
        okType:  'danger',
        onOk: async () => {
          try {
            await axios.delete(`/api/payments/${id}`);
            message.success('Payment deleted');
            loadPayments();
          } catch {
            message.error('Failed to delete');
          }
        }
      });
    };

    const exportCSV = () => {
      if (!payments.value.length) return message.warning('No payments to export');
      const headers = ['Payment #', 'Invoice #', 'Client', 'Payment Mode', 'Transaction ID', 'Amount', 'Date'];
      const rows = payments.value.map(p => [
        `PAY-${String(p.id).padStart(5,'0')}`,
        p.invoice?.number || '',
        p.invoice?.client?.company || '',
        p.paymentmode || '',
        p.transactionid || '',
        p.amount || 0,
        p.date || ''
      ]);
      const csv = "data:text/csv;charset=utf-8,"
        + [headers.join(','), ...rows.map(r => r.map(v => `"${String(v).replace(/"/g,'""')}"`).join(','))].join('\n');
      const link = document.createElement('a');
      link.href = encodeURI(csv);
      link.download = 'payments_export.csv';
      link.click();
    };

    const modeKey  = (m) => (m || '').toLowerCase().replace(/\s/g, '');
    const formatCurrency = (v) => !v && v !== 0 ? '$0.00' : '$' + parseFloat(v).toLocaleString('en-US', { minimumFractionDigits: 2 });
    const formatDate = (d) => !d ? '—' : new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

    onMounted(loadPayments);

    return {
      loading, payments, stats, filters, pagination,
      loadPayments, viewPayment, editPayment, deletePayment, exportCSV,
      modeKey, formatCurrency, formatDate,
    };
  }
});
</script>

<style scoped>
.payments-page {
  font-family: 'Inter', -apple-system, sans-serif;
  background: #f8fafc;
  min-height: 100vh;
  padding: 20px 24px;
  box-sizing: border-box;
}

/* Header */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 12px;
}
.header-left {}
.page-title { font-size: 20px; font-weight: 700; color: #1e293b; margin: 0 0 2px; }
.page-subtitle { font-size: 12px; color: #94a3b8; }
.header-right { display: flex; align-items: center; gap: 8px; }
.btn-primary {
  background: #1e293b; color: #fff;
  border: none; border-radius: 6px;
  padding: 9px 16px; font-size: 13px; font-weight: 600;
  cursor: pointer; display: flex; align-items: center; gap: 6px;
  font-family: inherit; transition: background 0.15s; white-space: nowrap;
}
.btn-primary:hover { background: #0f172a; }
.btn-outline {
  background: #fff; border: 1px solid #e2e8f0;
  border-radius: 6px; padding: 8px 14px;
  font-size: 12.5px; font-weight: 600;
  color: #475569; cursor: pointer;
  display: flex; align-items: center; gap: 6px;
  font-family: inherit; transition: all 0.12s;
}
.btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }

/* Summary cards */
.summary-cards {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 12px;
  margin-bottom: 18px;
}
.sum-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 14px 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.sum-icon {
  width: 36px; height: 36px;
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.sum-total     { background: #f1f5f9; color: #64748b; }
.sum-collected { background: #dcfce7; color: #16a34a; }
.sum-bank      { background: #dbeafe; color: #2563eb; }
.sum-stripe    { background: #ede9fe; color: #7c3aed; }
.sum-paypal    { background: #cffafe; color: #0891b2; }
.sum-cash      { background: #f0fdf4; color: #16a34a; }
.sum-val { font-size: 17px; font-weight: 800; color: #1e293b; line-height: 1; }
.sum-val.green { color: #16a34a; }
.sum-label { font-size: 10.5px; color: #94a3b8; font-weight: 500; margin-top: 3px; }

/* Table card */
.table-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
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
.toolbar-left, .toolbar-right { display: flex; align-items: center; gap: 8px; }
.entry-count { font-size: 12px; color: #94a3b8; }

.table-wrapper { overflow-x: auto; }
.pay-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
  min-width: 800px;
}
.pay-table th {
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  padding: 10px 14px;
  text-align: left;
  font-size: 11.5px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  white-space: nowrap;
}
.pay-table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.pay-row { cursor: pointer; transition: background 0.1s; }
.pay-row:hover { background: #f8fafc; }
.pay-row:last-child td { border-bottom: none; }
.text-right { text-align: right !important; }

.pay-num { font-weight: 700; color: #475569; font-size: 12px; font-family: monospace; }
.inv-link { color: #3b82f6; font-weight: 600; cursor: pointer; }
.inv-link:hover { text-decoration: underline; }
.client-name { font-weight: 600; color: #1e293b; }
.client-link {
  color: #3b82f6;
  text-decoration: none;
  transition: color 0.12s;
}
.client-link:hover {
  color: #2563eb;
  text-decoration: underline;
}
.txn-id { font-family: monospace; color: #64748b; font-size: 11.5px; }
.amount-val { font-weight: 700; color: #16a34a; font-size: 13.5px; }
.muted-date { color: #64748b; font-size: 12.5px; }
.muted { color: #94a3b8; }

/* Mode badges */
.mode-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
}
.mode-bank        { background: #dbeafe; color: #1d4ed8; }
.mode-paypal      { background: #cffafe; color: #0e7490; }
.mode-stripe,
.mode-stripecheckout { background: #ede9fe; color: #6d28d9; }
.mode-cash        { background: #dcfce7; color: #15803d; }
.mode-cheque      { background: #fef3c7; color: #b45309; }
.mode-creditcard  { background: #fce7f3; color: #be185d; }

.action-td { width: 44px; text-align: center; }
.three-dots-btn {
  background: none; border: 1px solid transparent; border-radius: 5px;
  padding: 4px 6px; cursor: pointer; color: #94a3b8;
  display: flex; align-items: center; transition: all 0.12s;
}
.three-dots-btn:hover { background: #f1f5f9; border-color: #e2e8f0; color: #475569; }

.empty-state {
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  padding: 60px 20px; gap: 10px;
  color: #94a3b8; font-size: 13px;
}
.empty-state p { margin: 0; }
.table-loading {
  display: flex; align-items: center; justify-content: center;
  gap: 10px; padding: 60px 0; color: #94a3b8; font-size: 13px;
}
.table-footer {
  display: flex; justify-content: flex-end;
  padding: 12px 16px; border-top: 1px solid #f1f5f9;
}

@media (max-width: 1200px) {
  .summary-cards { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 768px) {
  .summary-cards { grid-template-columns: repeat(2, 1fr); }
}
</style>
