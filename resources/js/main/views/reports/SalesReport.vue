<template>
  <div class="sr-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">{{ reportTitle }}</h1>
      </div>
      <div class="header-actions">
        <select v-model="selectedYear" @change="loadData" class="year-select" v-if="showYearFilter">
          <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
        </select>
      </div>
    </div>

    <div class="table-card" v-if="activeReport !== 'charts' && activeReport !== 'total-income' && activeReport !== 'payment-modes' && activeReport !== 'customer-groups'">
      <div class="table-toolbar">
        <div class="toolbar-left">
          <a-select v-model:value="filters.perPage" size="small" style="width:70px" @change="loadData">
            <a-select-option :value="10">10</a-select-option>
            <a-select-option :value="25">25</a-select-option>
            <a-select-option :value="50">50</a-select-option>
          </a-select>
          <a-input-search
            v-model:value="filters.search"
            placeholder="Search..."
            size="small"
            style="width:240px"
            @search="loadData"
          />
          <a-select v-if="statusFilterOptions.length" v-model:value="filters.status" size="small" style="width:160px" mode="multiple" :max-tag-count="1" placeholder="Status" @change="loadData" allow-clear>
            <a-select-option v-for="s in statusFilterOptions" :key="s" :value="s">{{ s }}</a-select-option>
          </a-select>
          <a-select v-if="showAgentFilter" v-model:value="filters.sale_agent" size="small" style="width:180px" placeholder="Sale Agent" @change="loadData" allow-clear>
            <a-select-option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</a-select-option>
          </a-select>
        </div>
        <div class="toolbar-right">
          <a-button size="small" @click="loadData">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="inline mr-1"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
            Refresh
          </a-button>
        </div>
      </div>

      <!-- Invoices Report -->
      <a-table v-if="activeReport === 'invoices'" :dataSource="tableData" :columns="invoiceColumns" :loading="loading" :pagination="pagination" @change="handleTableChange" row-key="id" size="small" :scroll="{ x: 1400 }">
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'status'"><a-tag :color="statusColor(record.status)">{{ record.status }}</a-tag></template>
          <template v-if="column.key === 'amount' || column.key === 'amount_with_tax' || column.key === 'total_tax' || column.key === 'tax1' || column.key === 'discount' || column.key === 'adjustment' || column.key === 'applied_credits' || column.key === 'amount_open'">${{ formatNum(record[column.key]) }}</template>
        </template>
        <template #emptyText><div class="empty-state"><p>No entries found</p></div></template>
      </a-table>

      <!-- Items Report -->
      <a-table v-if="activeReport === 'items'" :dataSource="tableData" :columns="itemColumns" :loading="loading" :pagination="pagination" @change="handleTableChange" row-key="id" size="small" :scroll="{ x: 600 }">
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'rate'">${{ formatNum(record.rate) }}</template>
          <template v-if="column.key === 'tax_rate'">{{ record.tax_rate }}%</template>
        </template>
        <template #emptyText><div class="empty-state"><p>No entries found</p></div></template>
      </a-table>

      <!-- Payments Report -->
      <a-table v-if="activeReport === 'payments'" :dataSource="tableData" :columns="paymentColumns" :loading="loading" :pagination="pagination" @change="handleTableChange" row-key="id" size="small" :scroll="{ x: 1000 }">
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'amount'">${{ formatNum(record.amount) }}</template>
        </template>
        <template #emptyText><div class="empty-state"><p>No entries found</p></div></template>
      </a-table>

      <!-- Credit Notes Report -->
      <a-table v-if="activeReport === 'credit-notes'" :dataSource="tableData" :columns="cnColumns" :loading="loading" :pagination="pagination" @change="handleTableChange" row-key="id" size="small" :scroll="{ x: 1200 }">
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'status'"><a-tag :color="cnStatusColor(record.status)">{{ record.status }}</a-tag></template>
          <template v-if="column.key === 'amount' || column.key === 'amount_with_tax' || column.key === 'total_tax' || column.key === 'discount' || column.key === 'adjustment' || column.key === 'remaining_amount' || column.key === 'refunded_amount'">${{ formatNum(record[column.key]) }}</template>
        </template>
        <template #emptyText><div class="empty-state"><p>No entries found</p></div></template>
      </a-table>

      <!-- Proposals Report -->
      <a-table v-if="activeReport === 'proposals'" :dataSource="tableData" :columns="proposalColumns" :loading="loading" :pagination="pagination" @change="handleTableChange" row-key="id" size="small" :scroll="{ x: 1400 }">
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'status'"><a-tag :color="estStatusColor(record.status)">{{ record.status }}</a-tag></template>
          <template v-if="column.key === 'amount' || column.key === 'amount_with_tax' || column.key === 'total_tax' || column.key === 'tax1_18' || column.key === 'tax1_20' || column.key === 'tax3_5' || column.key === 'discount' || column.key === 'adjustment'">${{ formatNum(record[column.key]) }}</template>
        </template>
        <template #emptyText><div class="empty-state"><p>No entries found</p></div></template>
      </a-table>

      <!-- Estimates Report -->
      <a-table v-if="activeReport === 'estimates'" :dataSource="tableData" :columns="estColumns" :loading="loading" :pagination="pagination" @change="handleTableChange" row-key="id" size="small" :scroll="{ x: 1400 }">
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'status'"><a-tag :color="estStatusColor(record.status)">{{ record.status }}</a-tag></template>
          <template v-if="column.key === 'amount' || column.key === 'amount_with_tax' || column.key === 'total_tax' || column.key === 'tax1_18' || column.key === 'tax1_20' || column.key === 'tax3_5' || column.key === 'discount' || column.key === 'adjustment'">${{ formatNum(record[column.key]) }}</template>
        </template>
        <template #emptyText><div class="empty-state"><p>No entries found</p></div></template>
      </a-table>

      <!-- Customers Report -->
      <a-table v-if="activeReport === 'customers'" :dataSource="tableData" :columns="customerColumns" :loading="loading" :pagination="pagination" @change="handleTableChange" row-key="id" size="small" :scroll="{ x: 600 }">
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'amount' || column.key === 'amount_with_tax'">${{ formatNum(record[column.key]) }}</template>
        </template>
        <template #emptyText><div class="empty-state"><p>No entries found</p></div></template>
      </a-table>
    </div>

    <!-- Charts Report -->
    <div v-if="activeReport === 'charts'" class="report-section">
      <div class="chart-and-table">
        <div class="bar-chart">
          <div class="chart-title">Monthly Sales vs Payments ({{ selectedYear }})</div>
          <div class="bars">
            <div v-for="row in chartData" :key="row.month" class="bar-wrap">
              <div class="bar-label-top">${{ formatNum(row.invoice_total) }}</div>
              <div class="bar-col"><div class="bar" :style="{ height: barHeight(row.invoice_total, maxChartVal) + 'px' }"></div></div>
              <div class="bar-label">{{ monthName(row.month) }}</div>
            </div>
          </div>
        </div>
        <div class="summary-table">
          <table class="data-table">
            <thead><tr><th>Month</th><th>Invoices</th><th>Invoice $</th><th>Payments</th><th>Payment $</th></tr></thead>
            <tbody>
              <tr v-for="row in chartData" :key="row.month">
                <td>{{ monthName(row.month) }}</td><td>{{ row.invoices }}</td><td>${{ formatNum(row.invoice_total) }}</td><td>{{ row.payments }}</td><td>${{ formatNum(row.payment_total) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Total Income -->
    <div v-if="activeReport === 'total-income'" class="report-section">
      <div class="finance-cards" v-if="incomeData">
        <div class="finance-card income"><div class="card-label">Total Invoiced</div><div class="card-value">${{ formatNum(incomeData.invoiced) }}</div><div class="card-sub">{{ selectedYear }}</div></div>
        <div class="finance-card payments"><div class="card-label">Total Paid</div><div class="card-value">${{ formatNum(incomeData.paid) }}</div><div class="card-sub">{{ selectedYear }}</div></div>
        <div class="finance-card expenses"><div class="card-label">Outstanding</div><div class="card-value">${{ formatNum(incomeData.outstanding) }}</div><div class="card-sub">{{ selectedYear }}</div></div>
      </div>
    </div>

    <!-- Payment Modes -->
    <div v-if="activeReport === 'payment-modes'" class="report-section">
      <a-table :dataSource="modeData" :columns="modeColumns" row-key="mode" size="small" :pagination="false">
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'total'">${{ formatNum(record.total) }}</template>
        </template>
        <template #emptyText><EmptyState /></template>
      </a-table>
    </div>

    <!-- Customer Groups -->
    <div v-if="activeReport === 'customer-groups'" class="report-section">
      <a-table :dataSource="groupData" :columns="groupColumns" row-key="company" size="small" :pagination="false">
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'total_value'">${{ formatNum(record.total_value) }}</template>
        </template>
        <template #emptyText><EmptyState /></template>
      </a-table>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'SalesReportPage',
  setup() {
    const route = useRoute();
    const loading = ref(false);
    const tableData = ref([]);
    const chartData = ref([]);
    const incomeData = ref(null);
    const modeData = ref([]);
    const groupData = ref([]);
    const selectedYear = ref(new Date().getFullYear());
    const pagination = reactive({ current: 1, pageSize: 25, total: 0 });
    const filters = reactive({ search: '', perPage: 25, status: [], sale_agent: null });
    const agents = ref([]);

    const activeReport = computed(() => {
      const p = route.path;
      if (p.includes('/invoices')) return 'invoices';
      if (p.includes('/items')) return 'items';
      if (p.includes('/payments')) return 'payments';
      if (p.includes('/credit-notes')) return 'credit-notes';
      if (p.includes('/proposals')) return 'proposals';
      if (p.includes('/estimates')) return 'estimates';
      if (p.includes('/customers')) return 'customers';
      if (p.includes('/charts')) return 'charts';
      if (p.includes('/total-income')) return 'total-income';
      if (p.includes('/payment-modes')) return 'payment-modes';
      if (p.includes('/customer-groups')) return 'customer-groups';
      return 'invoices';
    });

    const reportTitle = computed(() => {
      const titles = {
        invoices: 'Invoices Report', items: 'Items Report', payments: 'Payments Received',
        'credit-notes': 'Credit Notes Report', proposals: 'Proposals Report',
        estimates: 'Estimates Report', customers: 'Customers Report',
        charts: 'Charts Based Report', 'total-income': 'Total Income',
        'payment-modes': 'Payment Modes (Transactions)', 'customer-groups': 'Total Value By Customer Groups',
      };
      return titles[activeReport.value] || 'Sales Report';
    });

    const showYearFilter = computed(() => ['charts', 'total-income'].includes(activeReport.value));
    const showAgentFilter = computed(() => ['invoices', 'proposals', 'estimates'].includes(activeReport.value));
    const statusFilterOptions = computed(() => {
      const map = {
        invoices: ['Unpaid', 'Paid', 'Partially Paid', 'Overdue', 'Draft'],
        'credit-notes': ['Open', 'Closed', 'Void'],
        proposals: ['Draft', 'Sent', 'Open', 'Revised', 'Declined', 'Accepted'],
        estimates: ['Draft', 'Sent', 'Open', 'Revised', 'Declined', 'Accepted'],
      };
      return map[activeReport.value] || [];
    });

    const years = [new Date().getFullYear(), new Date().getFullYear()-1, new Date().getFullYear()-2, new Date().getFullYear()-3];

    const invoiceColumns = [
      { title: 'Invoice #', key: 'number', width: 110 },
      { title: 'Customer', key: 'customer', width: 180 },
      { title: 'Date', key: 'date', width: 100 },
      { title: 'Due Date', key: 'duedate', width: 100 },
      { title: 'Amount', key: 'amount', width: 100 },
      { title: 'Amount with tax', key: 'amount_with_tax', width: 110 },
      { title: 'Total Tax', key: 'total_tax', width: 90 },
      { title: 'TAX1 18.00%', key: 'tax1', width: 90 },
      { title: 'Discount', key: 'discount', width: 90 },
      { title: 'Adjustment', key: 'adjustment', width: 90 },
      { title: 'Applied Credits', key: 'applied_credits', width: 100 },
      { title: 'Amount open', key: 'amount_open', width: 100 },
      { title: 'Status', key: 'status', width: 110 },
    ];

    const itemColumns = [
      { title: 'Item #', key: 'id', width: 70 },
      { title: 'Name', key: 'name' },
      { title: 'Description', key: 'description' },
      { title: 'Rate', key: 'rate', width: 100 },
      { title: 'Tax Rate', key: 'tax_rate', width: 90 },
      { title: 'Unit', key: 'unit', width: 80 },
    ];

    const paymentColumns = [
      { title: 'Payment #', key: 'number', width: 90 },
      { title: 'Date', key: 'date', width: 100 },
      { title: 'Invoice #', key: 'invoice_number', width: 110 },
      { title: 'Customer', key: 'customer', width: 180 },
      { title: 'Payment Mode', key: 'payment_mode', width: 120 },
      { title: 'Transaction ID', key: 'transaction_id', width: 130 },
      { title: 'Note', key: 'note' },
      { title: 'Amount', key: 'amount', width: 100 },
    ];

    const cnColumns = [
      { title: 'Credit Note #', key: 'number', width: 110 },
      { title: 'Date', key: 'date', width: 100 },
      { title: 'Customer', key: 'customer', width: 180 },
      { title: 'Reference #', key: 'reference', width: 110 },
      { title: 'Amount', key: 'amount', width: 100 },
      { title: 'Amount with tax', key: 'amount_with_tax', width: 110 },
      { title: 'Total Tax', key: 'total_tax', width: 90 },
      { title: 'Discount', key: 'discount', width: 90 },
      { title: 'Adjustment', key: 'adjustment', width: 90 },
      { title: 'Remaining Amount', key: 'remaining_amount', width: 110 },
      { title: 'Refunded Amount', key: 'refunded_amount', width: 110 },
      { title: 'Status', key: 'status', width: 90 },
    ];

    const proposalColumns = [
      { title: 'Proposal #', key: 'number', width: 110 },
      { title: 'Subject', key: 'subject' },
      { title: 'To', key: 'to', width: 180 },
      { title: 'Date', key: 'date', width: 100 },
      { title: 'Open Till', key: 'open_till', width: 100 },
      { title: 'Amount', key: 'amount', width: 100 },
      { title: 'Amount with tax', key: 'amount_with_tax', width: 110 },
      { title: 'Total Tax', key: 'total_tax', width: 90 },
      { title: 'TAX1 18.00%', key: 'tax1_18', width: 95 },
      { title: 'TAX1 20.00%', key: 'tax1_20', width: 95 },
      { title: 'TAX3 5.00%', key: 'tax3_5', width: 90 },
      { title: 'Discount', key: 'discount', width: 90 },
      { title: 'Adjustment', key: 'adjustment', width: 90 },
      { title: 'Status', key: 'status', width: 100 },
    ];

    const estColumns = JSON.parse(JSON.stringify(proposalColumns));
    estColumns[0].title = 'Estimate #';

    const customerColumns = [
      { title: 'Customer', key: 'company' },
      { title: 'Total Invoices', key: 'total_invoices', width: 110 },
      { title: 'Amount', key: 'amount', width: 120 },
      { title: 'Amount with Tax', key: 'amount_with_tax', width: 120 },
    ];

    const modeColumns = [
      { title: 'Payment Mode', key: 'mode' },
      { title: 'Transactions', key: 'count', width: 120 },
      { title: 'Total Amount', key: 'total', width: 150 },
    ];

    const groupColumns = [
      { title: 'Customer', key: 'company' },
      { title: 'Total Invoices', key: 'total_invoices', width: 120 },
      { title: 'Total Value', key: 'total_value', width: 150 },
    ];

    const loadData = async () => {
      loading.value = true;
      try {
        const params = { search: filters.search, per_page: filters.perPage, page: pagination.current, year: selectedYear.value };
        if (filters.status.length) params.status = filters.status;
        if (filters.sale_agent) params.sale_agent = filters.sale_agent;

        const endpoints = {
          invoices: 'sales-report/invoices', items: 'sales-report/items', payments: 'sales-report/payments',
          'credit-notes': 'sales-report/credit-notes', proposals: 'sales-report/proposals',
          estimates: 'sales-report/estimates', customers: 'sales-report/customers',
          charts: 'sales-report/charts', 'total-income': 'sales-report/total-income',
          'payment-modes': 'sales-report/payment-modes', 'customer-groups': 'sales-report/customer-groups',
        };

        const ep = endpoints[activeReport.value];
        if (!ep) return;

        const res = await axios.get(`/api/${ep}`, { params });

        if (activeReport.value === 'charts') { chartData.value = res.data.monthly || []; }
        else if (activeReport.value === 'total-income') { incomeData.value = res.data; }
        else if (activeReport.value === 'payment-modes') { modeData.value = res.data.modes || []; }
        else if (activeReport.value === 'customer-groups') { groupData.value = res.data.groups || []; }
        else {
          const key = Object.keys(res.data).find(k => k !== 'message');
          tableData.value = res.data[key]?.data || [];
          pagination.total = res.data[key]?.total || 0;
        }
      } catch (e) {
        message.error('Failed to load report data');
        tableData.value = [];
      } finally { loading.value = false; }
    };

    const loadAgents = async () => {
      try {
        const res = await axios.get('/api/staff');
        agents.value = Array.isArray(res.data) ? res.data : (res.data.staff || res.data.data || []);
      } catch { agents.value = []; }
    };

    const handleTableChange = (pag) => { pagination.current = pag.current; loadData(); };

    const formatNum = (v) => { if (!v && v !== 0) return '0.00'; return Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
    const monthName = (m) => ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][m-1] || '';
    const barHeight = (val, max) => Math.max(4, Math.round((val / max) * 160));

    const maxChartVal = computed(() => Math.max(...chartData.value.map(r => r.invoice_total), 1));

    const statusColor = (s) => ({ Paid: 'green', Unpaid: 'orange', 'Partially Paid': 'blue', Overdue: 'red', Draft: 'default' }[s] || 'default');
    const cnStatusColor = (s) => ({ Open: 'blue', Closed: 'default', Void: 'red' }[s] || 'default');
    const estStatusColor = (s) => ({ Draft: 'default', Sent: 'blue', Open: 'orange', Revised: 'purple', Declined: 'red', Accepted: 'green' }[s] || 'default');

    onMounted(() => {
      loadData();
      if (showAgentFilter.value) loadAgents();
    });

    return {
      loading, tableData, chartData, incomeData, modeData, groupData,
      selectedYear, pagination, filters, agents,
      activeReport, reportTitle, showYearFilter, showAgentFilter, statusFilterOptions,
      years, invoiceColumns, itemColumns, paymentColumns, cnColumns,
      proposalColumns, estColumns, customerColumns, modeColumns, groupColumns,
      loadData, handleTableChange, formatNum, monthName, barHeight,
      maxChartVal, statusColor, cnStatusColor, estStatusColor,
    };
  }
});
</script>

<style scoped>
.sr-page { font-family: 'Inter', -apple-system, sans-serif; }
.page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; }
.page-title { font-size:20px; font-weight:700; color:#1e293b; margin:0; }
.header-actions { display:flex; gap:10px; align-items:center; }
.year-select { padding:8px 12px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; cursor:pointer; }

.table-card { background:#fff; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; }
.table-toolbar { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-bottom:1px solid #f1f5f9; flex-wrap:wrap; gap:10px; }
.toolbar-left, .toolbar-right { display:flex; align-items:center; gap:8px; }

.report-section { background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:24px; }
.chart-and-table { display:grid; grid-template-columns:2fr 1fr; gap:24px; }
.bar-chart { padding:0; }
.chart-title { font-size:12px; font-weight:600; color:#64748b; margin-bottom:12px; }
.bars { display:flex; align-items:flex-end; gap:6px; height:200px; padding-bottom:24px; border-bottom:2px solid #f1f5f9; }
.bar-wrap { display:flex; flex-direction:column; align-items:center; flex:1; gap:4px; height:100%; justify-content:flex-end; position:relative; }
.bar-label-top { font-size:8px; color:#94a3b8; text-align:center; position:absolute; top:-4px; white-space:nowrap; }
.bar-col { display:flex; align-items:flex-end; flex:1; width:100%; padding-top:16px; }
.bar { width:100%; min-height:4px; background:linear-gradient(180deg,#1e9aff,#0d7bd6); border-radius:4px 4px 0 0; transition:height 0.5s ease; }
.bar-label { font-size:9px; color:#94a3b8; margin-top:4px; }

.summary-table { overflow-x:auto; }
.data-table { width:100%; border-collapse:collapse; font-size:12px; }
.data-table th { padding:8px 10px; text-align:left; background:#f8fafc; color:#475569; font-weight:600; border-bottom:1.5px solid #e2e8f0; }
.data-table td { padding:8px 10px; border-bottom:1px solid #f1f5f9; }
.total-row td { background:#f8fafc; border-top:1.5px solid #e2e8f0; font-weight:700; }

.finance-cards { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
.finance-card { background:#fff; border:1.5px solid #e2e8f0; border-radius:12px; padding:20px 18px; }
.finance-card.income { border-top:3px solid #10b981; }
.finance-card.payments { border-top:3px solid #3b82f6; }
.finance-card.expenses { border-top:3px solid #ef4444; }
.card-label { font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px; }
.card-value { font-size:26px; font-weight:800; color:#1e293b; }
.card-sub { font-size:11px; color:#94a3b8; margin-top:2px; }

.total-row td { background:#f8fafc; border-top:1.5px solid #e2e8f0; font-weight:700; }
.empty-state { display:flex; flex-direction:column; align-items:center; padding:40px 0; gap:8px; color:#94a3b8; font-size:13px; }
</style>
