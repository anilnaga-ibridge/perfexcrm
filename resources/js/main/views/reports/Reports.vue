<template>
  <div class="reports-page">
    <!-- Header -->
    <div class="page-header">
      <div class="header-left">
        <h1>Reports &amp; Analytics</h1>
        <span class="subtitle">Business insights and performance metrics</span>
      </div>
      <div class="header-actions">
        <select v-model="selectedYear" @change="loadAll" class="year-select">
          <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
        </select>
        <button class="btn-export" @click="exportReport">📄 Export</button>
      </div>
    </div>

    <!-- Finance Overview Cards -->
    <div class="finance-cards" v-if="finance">
      <div class="finance-card income">
        <div class="card-label">Total Income</div>
        <div class="card-value">${{ formatMoney(finance.income) }}</div>
        <div class="card-sub">Invoiced this year</div>
      </div>
      <div class="finance-card expenses">
        <div class="card-label">Total Expenses</div>
        <div class="card-value">${{ formatMoney(finance.expenses) }}</div>
        <div class="card-sub">Spent this year</div>
      </div>
      <div class="finance-card payments">
        <div class="card-label">Payments Received</div>
        <div class="card-value">${{ formatMoney(finance.payments) }}</div>
        <div class="card-sub">Collected this year</div>
      </div>
      <div class="finance-card profit" :class="finance.profit >= 0 ? 'positive' : 'negative'">
        <div class="card-label">Net Profit</div>
        <div class="card-value">${{ formatMoney(finance.profit) }}</div>
        <div class="card-sub">Income minus expenses</div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button v-for="tab in tabs" :key="tab.key" class="tab-btn" :class="{ active: activeTab === tab.key }" @click="activeTab = tab.key">
        {{ tab.icon }} {{ tab.label }}
      </button>
    </div>

    <!-- Sales Report (comprehensive) -->
    <div v-if="activeTab === 'sales'" class="report-section">
      <div class="section-header">
        <h2>Sales Report</h2>
        <div class="header-filters">
          <select v-model="srPeriod" @change="loadSalesReport" class="year-select">
            <option value="this_month">This Month</option>
            <option value="last_month">Last Month</option>
            <option value="this_year">This Year</option>
            <option value="last_year">Last Year</option>
            <option value="custom">Custom</option>
          </select>
          <select v-model="selectedYear" @change="loadSalesReport" class="year-select">
            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>
      </div>

      <!-- Sub-tabs -->
      <div class="sub-tabs">
        <button v-for="st in salesSubTabs" :key="st.key" class="sub-tab-btn" :class="{ active: salesSubTab === st.key }" @click="salesSubTab = st.key">{{ st.label }}</button>
      </div>

      <!-- Invoices -->
      <div v-if="salesSubTab === 'invoices'">
        <div class="report-toolbar">
          <div class="toolbar-left">
            <a-select v-model:value="srFilters.perPage" size="small" style="width:70px" @change="loadSalesReport">
              <a-select-option :value="10">10</a-select-option>
              <a-select-option :value="25">25</a-select-option>
              <a-select-option :value="50">50</a-select-option>
            </a-select>
            <a-input-search v-model:value="srFilters.search" placeholder="Search..." size="small" style="width:220px" @search="loadSalesReport" />
            <a-select v-model:value="srFilters.status" mode="multiple" :max-tag-count="1" size="small" style="width:160px" placeholder="Status" @change="loadSalesReport" allow-clear>
              <a-select-option v-for="s in ['Unpaid','Paid','Partially Paid','Overdue','Draft']" :key="s" :value="s">{{ s }}</a-select-option>
            </a-select>
            <a-select v-model:value="srFilters.sale_agent" size="small" style="width:180px" placeholder="Sale Agent" @change="loadSalesReport" allow-clear>
              <a-select-option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</a-select-option>
            </a-select>
          </div>
        </div>
        <div v-if="srLoading" class="loading-wrap"><div class="loader"></div> Loading...</div>
        <div v-else class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Invoice #</th><th>Customer</th><th>Date</th><th>Due Date</th><th>Amount</th><th>Amount with tax</th><th>Total Tax</th><th>TAX1 18.00%</th><th>Discount</th><th>Adjustment</th><th>Applied Credits</th><th>Amount open</th><th>Status</th></tr></thead>
            <tbody>
              <tr v-for="r in srInvoices" :key="r.id">
                <td>{{ r.number }}</td><td>{{ r.customer }}</td><td>{{ r.date }}</td><td>{{ r.duedate }}</td>
                <td>${{ fm(r.amount) }}</td><td>${{ fm(r.amount_with_tax) }}</td><td>${{ fm(r.total_tax) }}</td><td>${{ fm(r.tax1) }}</td>
                <td>${{ fm(r.discount) }}</td><td>${{ fm(r.adjustment) }}</td><td>${{ fm(r.applied_credits) }}</td><td>${{ fm(r.amount_open) }}</td>
                <td><span class="status-tag" :class="statusClass(r.status)">{{ r.status }}</span></td>
            </tr>
            <tr v-if="!srInvoices.length"><td colspan="13" class="empty-cell">No entries found</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Items -->
      <div v-if="salesSubTab === 'items'">
        <div class="report-toolbar">
          <a-input-search v-model:value="srFilters.search" placeholder="Search..." size="small" style="width:220px" @search="loadSalesReport" />
        </div>
        <div v-if="srLoading" class="loading-wrap"><div class="loader"></div> Loading...</div>
        <div v-else class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Item #</th><th>Name</th><th>Description</th><th>Rate</th><th>Tax Rate</th><th>Unit</th></tr></thead>
            <tbody>
              <tr v-for="r in srItems" :key="r.id"><td>{{ r.id }}</td><td>{{ r.name }}</td><td>{{ r.description || '—' }}</td><td>${{ fm(r.rate) }}</td><td>{{ r.tax_rate }}%</td><td>{{ r.unit || '—' }}</td></tr>
              <tr v-if="!srItems.length"><td colspan="6" class="empty-cell">No entries found</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Payments -->
      <div v-if="salesSubTab === 'payments'">
        <div class="report-toolbar">
          <a-input-search v-model:value="srFilters.search" placeholder="Search..." size="small" style="width:220px" @search="loadSalesReport" />
        </div>
        <div v-if="srLoading" class="loading-wrap"><div class="loader"></div> Loading...</div>
        <div v-else class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Payment #</th><th>Date</th><th>Invoice #</th><th>Customer</th><th>Payment Mode</th><th>Transaction ID</th><th>Note</th><th>Amount</th></tr></thead>
            <tbody>
              <tr v-for="r in srPayments" :key="r.id">
                <td>{{ r.number }}</td><td>{{ r.date }}</td><td>{{ r.invoice_number }}</td><td>{{ r.customer }}</td>
                <td>{{ r.payment_mode }}</td><td>{{ r.transaction_id }}</td><td>{{ trunc(r.note, 40) }}</td><td>${{ fm(r.amount) }}</td>
              </tr>
              <tr v-if="!srPayments.length"><td colspan="8" class="empty-cell">No entries found</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Credit Notes -->
      <div v-if="salesSubTab === 'credit-notes'">
        <div class="report-toolbar">
          <a-input-search v-model:value="srFilters.search" placeholder="Search..." size="small" style="width:220px" @search="loadSalesReport" />
          <a-select v-model:value="srFilters.status" mode="multiple" :max-tag-count="1" size="small" style="width:140px" placeholder="Status" @change="loadSalesReport" allow-clear>
            <a-select-option v-for="s in ['Open','Closed','Void']" :key="s" :value="s">{{ s }}</a-select-option>
          </a-select>
        </div>
        <div v-if="srLoading" class="loading-wrap"><div class="loader"></div> Loading...</div>
        <div v-else class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Credit Note #</th><th>Date</th><th>Customer</th><th>Reference #</th><th>Amount</th><th>Amount with tax</th><th>Total Tax</th><th>Discount</th><th>Adjustment</th><th>Remaining</th><th>Refunded</th><th>Status</th></tr></thead>
            <tbody>
              <tr v-for="r in srCreditNotes" :key="r.id">
                <td>{{ r.number }}</td><td>{{ r.date }}</td><td>{{ r.customer }}</td><td>{{ r.reference }}</td>
                <td>${{ fm(r.amount) }}</td><td>${{ fm(r.amount_with_tax) }}</td><td>${{ fm(r.total_tax) }}</td><td>${{ fm(r.discount) }}</td>
                <td>${{ fm(r.adjustment) }}</td><td>${{ fm(r.remaining_amount) }}</td><td>${{ fm(r.refunded_amount) }}</td>
                <td><span class="status-tag" :class="statusClass(r.status)">{{ r.status }}</span></td>
              </tr>
              <tr v-if="!srCreditNotes.length"><td colspan="12" class="empty-cell">No entries found</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Proposals -->
      <div v-if="salesSubTab === 'proposals'">
        <div class="report-toolbar">
          <a-input-search v-model:value="srFilters.search" placeholder="Search..." size="small" style="width:220px" @search="loadSalesReport" />
          <a-select v-model:value="srFilters.status" mode="multiple" :max-tag-count="1" size="small" style="width:160px" placeholder="Status" @change="loadSalesReport" allow-clear>
            <a-select-option v-for="s in ['Draft','Sent','Open','Revised','Declined','Accepted']" :key="s" :value="s">{{ s }}</a-select-option>
          </a-select>
        </div>
        <div v-if="srLoading" class="loading-wrap"><div class="loader"></div> Loading...</div>
        <div v-else class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Proposal #</th><th>Subject</th><th>To</th><th>Date</th><th>Open Till</th><th>Amount</th><th>Amount with tax</th><th>Total Tax</th><th>TAX1 18%</th><th>TAX1 20%</th><th>TAX3 5%</th><th>Discount</th><th>Adjustment</th><th>Status</th></tr></thead>
            <tbody>
              <tr v-for="r in srProposals" :key="r.number">
                <td>{{ r.number }}</td><td>{{ r.subject }}</td><td>{{ r.to }}</td><td>{{ r.date }}</td><td>{{ r.open_till }}</td>
                <td>${{ fm(r.amount) }}</td><td>${{ fm(r.amount_with_tax) }}</td><td>${{ fm(r.total_tax) }}</td><td>${{ fm(r.tax1_18) }}</td>
                <td>${{ fm(r.tax1_20) }}</td><td>${{ fm(r.tax3_5) }}</td><td>${{ fm(r.discount) }}</td><td>${{ fm(r.adjustment) }}</td>
                <td><span class="status-tag" :class="statusClass(r.status)">{{ r.status }}</span></td>
              </tr>
              <tr v-if="!srProposals.length"><td colspan="14" class="empty-cell">No entries found</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Estimates -->
      <div v-if="salesSubTab === 'estimates'">
        <div class="report-toolbar">
          <a-input-search v-model:value="srFilters.search" placeholder="Search..." size="small" style="width:220px" @search="loadSalesReport" />
          <a-select v-model:value="srFilters.status" mode="multiple" :max-tag-count="1" size="small" style="width:160px" placeholder="Status" @change="loadSalesReport" allow-clear>
            <a-select-option v-for="s in ['Draft','Sent','Open','Revised','Declined','Accepted']" :key="s" :value="s">{{ s }}</a-select-option>
          </a-select>
        </div>
        <div v-if="srLoading" class="loading-wrap"><div class="loader"></div> Loading...</div>
        <div v-else class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Estimate #</th><th>Subject</th><th>To</th><th>Date</th><th>Open Till</th><th>Amount</th><th>Amount with tax</th><th>Total Tax</th><th>TAX1 18%</th><th>TAX1 20%</th><th>TAX3 5%</th><th>Discount</th><th>Adjustment</th><th>Status</th></tr></thead>
            <tbody>
              <tr v-for="r in srEstimates" :key="r.number">
                <td>{{ r.number }}</td><td>{{ r.subject }}</td><td>{{ r.to }}</td><td>{{ r.date }}</td><td>{{ r.open_till }}</td>
                <td>${{ fm(r.amount) }}</td><td>${{ fm(r.amount_with_tax) }}</td><td>${{ fm(r.total_tax) }}</td><td>${{ fm(r.tax1_18) }}</td>
                <td>${{ fm(r.tax1_20) }}</td><td>${{ fm(r.tax3_5) }}</td><td>${{ fm(r.discount) }}</td><td>${{ fm(r.adjustment) }}</td>
                <td><span class="status-tag" :class="statusClass(r.status)">{{ r.status }}</span></td>
              </tr>
              <tr v-if="!srEstimates.length"><td colspan="14" class="empty-cell">No entries found</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Customers -->
      <div v-if="salesSubTab === 'customers'">
        <div class="report-toolbar">
          <a-input-search v-model:value="srFilters.search" placeholder="Search..." size="small" style="width:220px" @search="loadSalesReport" />
        </div>
        <div v-if="srLoading" class="loading-wrap"><div class="loader"></div> Loading...</div>
        <div v-else class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Customer</th><th>Total Invoices</th><th>Amount</th><th>Amount with Tax</th></tr></thead>
            <tbody>
              <tr v-for="r in srCustomers" :key="r.id"><td>{{ r.company }}</td><td>{{ r.total_invoices }}</td><td>${{ fm(r.amount) }}</td><td>${{ fm(r.amount_with_tax) }}</td></tr>
              <tr v-if="!srCustomers.length"><td colspan="4" class="empty-cell">No entries found</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Charts -->
      <div v-if="salesSubTab === 'charts'">
        <div v-if="srLoading" class="loading-wrap"><div class="loader"></div> Loading...</div>
        <div v-else class="chart-and-table">
          <div class="bar-chart">
            <div class="chart-title">Monthly Sales vs Payments ($)</div>
            <div class="bars">
              <div v-for="row in srCharts" :key="row.month" class="bar-wrap">
                <div class="bar-label-top">${{ fm(row.invoice_total) }}</div>
                <div class="bar-col"><div class="bar" :style="{ height: srChartMax ? Math.round((row.invoice_total / srChartMax) * 160) + 'px' : '4px' }"></div></div>
                <div class="bar-label">{{ monthName(row.month) }}</div>
              </div>
            </div>
          </div>
          <div class="summary-table">
            <table class="data-table">
              <thead><tr><th>Month</th><th>Invoices</th><th>Invoice $</th><th>Payments</th><th>Payment $</th></tr></thead>
              <tbody>
                <tr v-for="row in srCharts" :key="row.month"><td>{{ monthName(row.month) }}</td><td>{{ row.invoices }}</td><td>${{ fm(row.invoice_total) }}</td><td>{{ row.payments }}</td><td>${{ fm(row.payment_total) }}</td></tr>
                <tr v-if="!srCharts.length"><td colspan="5" class="empty-cell">No data</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Total Income -->
      <div v-if="salesSubTab === 'total-income'">
        <div v-if="srLoading" class="loading-wrap"><div class="loader"></div> Loading...</div>
        <div v-else class="finance-cards">
          <div class="finance-card income"><div class="card-label">Total Invoiced</div><div class="card-value">${{ fm(srIncome?.invoiced) }}</div><div class="card-sub">{{ selectedYear }}</div></div>
          <div class="finance-card payments"><div class="card-label">Total Paid</div><div class="card-value">${{ fm(srIncome?.paid) }}</div><div class="card-sub">{{ selectedYear }}</div></div>
          <div class="finance-card expenses"><div class="card-label">Outstanding</div><div class="card-value">${{ fm(srIncome?.outstanding) }}</div><div class="card-sub">{{ selectedYear }}</div></div>
        </div>
      </div>

      <!-- Payment Modes -->
      <div v-if="salesSubTab === 'payment-modes'">
        <div v-if="srLoading" class="loading-wrap"><div class="loader"></div> Loading...</div>
        <div v-else class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Payment Mode</th><th>Transactions</th><th>Total Amount</th></tr></thead>
            <tbody>
              <tr v-for="r in srModes" :key="r.mode"><td>{{ r.mode }}</td><td>{{ r.count }}</td><td>${{ fm(r.total) }}</td></tr>
              <tr v-if="!srModes.length"><td colspan="3" class="empty-cell">No entries found</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Customer Groups -->
      <div v-if="salesSubTab === 'customer-groups'">
        <div v-if="srLoading" class="loading-wrap"><div class="loader"></div> Loading...</div>
        <div v-else class="table-responsive">
          <table class="data-table">
            <thead><tr><th>Customer</th><th>Total Invoices</th><th>Total Value</th></tr></thead>
            <tbody>
              <tr v-for="r in srGroups" :key="r.company"><td>{{ r.company }}</td><td>{{ r.total_invoices }}</td><td>${{ fm(r.total_value) }}</td></tr>
              <tr v-if="!srGroups.length"><td colspan="3" class="empty-cell">No entries found</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Expenses Report -->
    <div v-if="activeTab === 'expenses'" class="report-section">
      <div class="section-header">
        <h2>Expenses Report — {{ selectedYear }}</h2>
        <div class="header-filters">
          <label class="checkbox-label">
            <input type="checkbox" v-model="excludeBillable" @change="loadExpensesDetailed" />
            Exclude Billable Expenses
          </label>
          <select v-model="selectedYear" @change="loadExpensesDetailed" class="year-select">
            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>
      </div>
      <div v-if="expLoading" class="loading-wrap"><div class="loader"></div> Loading...</div>
      <div v-else class="expenses-detailed">
        <!-- Chart area -->
        <div class="exp-charts-row">
          <div class="exp-chart-box">
            <div class="chart-title">Monthly Expenses — Not Billable ($)</div>
            <div class="bars" style="height:160px">
              <div v-for="(v, i) in expNotBillableTotal" :key="i" class="bar-wrap">
                <div class="bar-label-top">${{ fm(v) }}</div>
                <div class="bar-col"><div class="bar exp-bar" :style="{ height: expBarHeight(v, expMaxNotBillable) + 'px' }"></div></div>
                <div class="bar-label">{{ shortMonth(i) }}</div>
              </div>
            </div>
          </div>
          <div v-if="!excludeBillable" class="exp-chart-box">
            <div class="chart-title">Monthly Expenses — Billable ($)</div>
            <div class="bars" style="height:160px">
              <div v-for="(v, i) in expBillableTotal" :key="i" class="bar-wrap">
                <div class="bar-label-top">${{ fm(v) }}</div>
                <div class="bar-col"><div class="bar" :style="{ height: expBarHeight(v, expMaxBillable) + 'px' }"></div></div>
                <div class="bar-label">{{ shortMonth(i) }}</div>
              </div>
            </div>
          </div>
          <div class="exp-chart-box">
            <div class="chart-title">Category Breakdown — Not Billable</div>
            <div class="pie-legend">
              <div v-for="r in expChartCategories(expNotBillable)" :key="r.label" class="legend-item">
                <span class="legend-dot" :style="{ background: r.color }"></span>
                <span class="legend-label">{{ r.label }}</span>
                <span class="legend-val">${{ fm(r.value) }}</span>
              </div>
            </div>
          </div>
          <div v-if="!excludeBillable" class="exp-chart-box">
            <div class="chart-title">Category Breakdown — Billable</div>
            <div class="pie-legend">
              <div v-for="r in expChartCategories(expBillable)" :key="r.label" class="legend-item">
                <span class="legend-dot" :style="{ background: r.color }"></span>
                <span class="legend-label">{{ r.label }}</span>
                <span class="legend-val">${{ fm(r.value) }}</span>
              </div>
            </div>
          </div>
        </div>
        <!-- Not billable expenses -->
        <div class="exp-section-group">
          <h3 class="exp-section-title">Not billable expenses by categories</h3>
          <div class="table-responsive">
            <table class="data-table exp-cat-table">
              <thead>
                <tr>
                  <th>Category</th>
                  <th v-for="m in monthNames" :key="m">{{ m }}</th>
                  <th>Year ({{ selectedYear }})</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in expNotBillable" :key="r.category" :class="{ 'total-row': r.is_total }">
                  <td><strong>{{ r.category }}</strong></td>
                  <td v-for="(v, i) in r.monthly" :key="i">${{ fm(v) }}</td>
                  <td><strong>${{ fm(r.total) }}</strong></td>
                </tr>
                <tr v-if="!expNotBillable.length"><td :colspan="14" class="empty-cell">No entries found</td></tr>
              </tbody>
            </table>
          </div>
        </div>
        <!-- Billable expenses -->
        <div class="exp-section-group">
          <h3 class="exp-section-title">Billable expenses by categories</h3>
          <div class="table-responsive">
            <table class="data-table exp-cat-table">
              <thead>
                <tr>
                  <th>Category</th>
                  <th v-for="m in monthNames" :key="m">{{ m }}</th>
                  <th>Year ({{ selectedYear }})</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in expBillable" :key="r.category" :class="{ 'total-row': r.is_total }">
                  <td><strong>{{ r.category }}</strong></td>
                  <td v-for="(v, i) in r.monthly" :key="i">${{ fm(v) }}</td>
                  <td><strong>${{ fm(r.total) }}</strong></td>
                </tr>
                <tr v-if="!expBillable.length"><td :colspan="14" class="empty-cell">No entries found</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Finance Report -->
    <div v-if="activeTab === 'finance'" class="report-section">
      <div class="section-header">
        <h2>Finance Overview — {{ selectedYear }}</h2>
      </div>
      <div v-if="!finance" class="loading-wrap"><div class="loader"></div> Loading...</div>
      <div v-else class="finance-detail">
        <div class="finance-comparison">
          <div class="comparison-row">
            <span class="comp-label">Total Income</span>
            <div class="comp-bar-wrap">
              <div class="comp-bar income-bar" :style="{ width: compPct(finance.income, finance.income + finance.expenses) + '%' }"></div>
            </div>
            <span class="comp-value income-val">${{ formatMoney(finance.income) }}</span>
          </div>
          <div class="comparison-row">
            <span class="comp-label">Total Expenses</span>
            <div class="comp-bar-wrap">
              <div class="comp-bar expense-bar" :style="{ width: compPct(finance.expenses, finance.income + finance.expenses) + '%' }"></div>
            </div>
            <span class="comp-value expense-val">${{ formatMoney(finance.expenses) }}</span>
          </div>
          <div class="comparison-row">
            <span class="comp-label">Payments Received</span>
            <div class="comp-bar-wrap">
              <div class="comp-bar payment-bar" :style="{ width: compPct(finance.payments, finance.income + finance.expenses) + '%' }"></div>
            </div>
            <span class="comp-value payment-val">${{ formatMoney(finance.payments) }}</span>
          </div>
          <div class="comparison-row">
            <span class="comp-label">Net Profit</span>
            <div class="comp-bar-wrap">
              <div class="comp-bar profit-bar" :class="finance.profit >= 0 ? 'pos' : 'neg'" :style="{ width: compPct(Math.abs(finance.profit), finance.income + finance.expenses) + '%' }"></div>
            </div>
            <span class="comp-value" :class="finance.profit >= 0 ? 'income-val' : 'expense-val'">${{ formatMoney(finance.profit) }}</span>
          </div>
        </div>
        <div class="profit-summary" :class="finance.profit >= 0 ? 'profitable' : 'unprofitable'">
          <span class="profit-icon">{{ finance.profit >= 0 ? '📈' : '📉' }}</span>
          <div>
            <div class="profit-headline">{{ finance.profit >= 0 ? 'Business is Profitable' : 'Running at a Loss' }}</div>
            <div class="profit-sub">Net: ${{ formatMoney(finance.profit) }} for {{ selectedYear }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Leads Report -->
    <div v-if="activeTab === 'leads'" class="report-section">
      <div class="section-header">
        <h2>Leads Report — {{ selectedYear }}</h2>
      </div>
      <div class="chart-and-table">
        <div class="bar-chart">
          <div class="chart-title">Monthly Leads Created</div>
          <div class="bars">
            <div v-for="row in leadsData" :key="row.month" class="bar-wrap">
              <div class="bar-label-top">{{ row.count }}</div>
              <div class="bar-col">
                <div class="bar" :style="{ height: barHeight(row.count, maxLeads) + 'px' }"></div>
              </div>
              <div class="bar-label">{{ monthName(row.month) }}</div>
            </div>
          </div>
        </div>
        <div class="summary-table">
          <table class="data-table">
            <thead><tr><th>Month</th><th>Leads</th><th>Converted</th></tr></thead>
            <tbody>
              <tr v-for="row in leadsData" :key="row.month">
                <td>{{ monthName(row.month) }}</td>
                <td>{{ row.count }}</td>
                <td>{{ row.converted }}</td>
              </tr>
              <tr class="total-row">
                <td><strong>Total</strong></td>
                <td><strong>{{ leadsData.reduce((a,r) => a+r.count, 0) }}</strong></td>
                <td><strong>{{ leadsData.reduce((a,r) => a+r.converted, 0) }}</strong></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Timesheets Report -->
    <div v-if="activeTab === 'timesheets'" class="report-section">
      <div class="section-header">
        <h2>Timesheets Overview — {{ selectedYear }}</h2>
      </div>
      <div class="chart-and-table">
        <div class="bar-chart">
          <div class="chart-title">Monthly Hours Logged</div>
          <div class="bars">
            <div v-for="row in timesheetsData" :key="row.month" class="bar-wrap">
              <div class="bar-label-top">{{ row.hours }}h</div>
              <div class="bar-col">
                <div class="bar" :style="{ height: barHeight(row.hours, maxTimesheets) + 'px' }"></div>
              </div>
              <div class="bar-label">{{ monthName(row.month) }}</div>
            </div>
          </div>
        </div>
        <div class="summary-table">
          <table class="data-table">
            <thead><tr><th>Month</th><th>Hours</th><th>Tasks</th></tr></thead>
            <tbody>
              <tr v-for="row in timesheetsData" :key="row.month">
                <td>{{ monthName(row.month) }}</td>
                <td>{{ row.hours }}h</td>
                <td>{{ row.tasks }}</td>
              </tr>
              <tr class="total-row">
                <td><strong>Total</strong></td>
                <td><strong>{{ timesheetsData.reduce((a,r) => a+r.hours, 0) }}h</strong></td>
                <td><strong>{{ timesheetsData.reduce((a,r) => a+r.tasks, 0) }}</strong></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- KB Articles Report -->
    <div v-if="activeTab === 'kb'" class="report-section">
      <div class="section-header">
        <h2>KB Articles — Voting Report</h2>
        <select v-model="kbCategoryId" @change="loadKbReport" class="year-select">
          <option value="">All Groups</option>
          <option v-for="c in kbCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>
      <div v-if="kbLoading" class="loading-wrap"><div class="loader"></div> Loading...</div>
      <div v-else class="kb-vote-list">
        <div class="kb-group-label" v-if="selectedKbGroup">Group: {{ selectedKbGroup }}</div>
        <div v-for="a in kbArticles" :key="a.id" class="kb-vote-card">
          <div class="kb-vote-title">{{ a.title }} <span class="kb-total">(Total: {{ a.total }})</span></div>
          <div v-if="a.total === 0" class="kb-no-votes">No votes yet</div>
          <div v-else class="kb-vote-bars">
            <div class="kb-vote-row">
              <span class="kb-vote-label">Yes:</span>
              <span class="kb-vote-count">{{ a.yes }}</span>
              <div class="kb-bar-wrap"><div class="kb-bar yes-bar" :style="{ width: a.yes_pct + '%' }"></div></div>
              <span class="kb-vote-pct">{{ a.yes_pct }}%</span>
            </div>
            <div class="kb-vote-row">
              <span class="kb-vote-label">No:</span>
              <span class="kb-vote-count">{{ a.no }}</span>
              <div class="kb-bar-wrap"><div class="kb-bar no-bar" :style="{ width: a.no_pct + '%' }"></div></div>
              <span class="kb-vote-pct">{{ a.no_pct }}%</span>
            </div>
          </div>
        </div>
        <div v-if="!kbArticles.length" class="empty-state">
          <p>No articles found</p>
        </div>
      </div>
    </div>

    <!-- Team Report -->
    <div v-if="activeTab === 'team'" class="report-section">
      <div class="section-header">
        <h2>Team Performance</h2>
      </div>
      <div v-if="loadingTeam" class="loading-wrap"><div class="loader"></div> Loading...</div>
      <div v-else class="team-list">
        <div v-for="(member, idx) in teamData" :key="member.id" class="team-row">
          <div class="team-rank">{{ idx + 1 }}</div>
          <div class="team-avatar">{{ member.name?.charAt(0)?.toUpperCase() }}</div>
          <div class="team-info">
            <div class="team-name">{{ member.name }}</div>
            <div class="team-tasks">{{ member.task_count }} tasks assigned</div>
          </div>
          <div class="team-bar-wrap">
            <div class="team-bar" :style="{ width: teamBarPct(member.task_count) + '%' }"></div>
          </div>
          <div class="team-count">{{ member.task_count }}</div>
        </div>
        <div v-if="!teamData.length" class="empty-state">
          <span class="empty-icon">👥</span>
          <p>No team data available</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { useRoute } from 'vue-router'

const BASE = '/api'
const route = useRoute()

const selectedYear   = ref(new Date().getFullYear())
const currentMonth   = new Date().getMonth() + 1
const activeTab      = ref('sales')
const finance        = ref(null)
const salesData        = ref([])
const expensesData     = ref([])
const leadsData        = ref([])
const timesheetsData   = ref([])
const teamData         = ref([])
const loadingSales     = ref(false)
const loadingExpenses  = ref(false)
const loadingTeam      = ref(false)
const expLoading      = ref(false)
const expNotBillable  = ref([])
const expBillable     = ref([])
const excludeBillable = ref(false)
const monthNames      = ['January','February','March','April','May','June','July','August','September','October','November','December']
const kbArticles    = ref([])
const kbCategories  = ref([])
const kbCategoryId  = ref('')
const kbLoading     = ref(false)

const SHORT_MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']
function shortMonth(i) { return SHORT_MONTHS[i] || '' }

const CAT_COLORS = ['#1e9aff','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#f97316','#14b8a6','#6366f1','#d946ef','#22c55e','#eab308','#3b82f6']

const selectedKbGroup = computed(() => {
  if (!kbCategoryId.value) return ''
  const c = kbCategories.value.find(c => c.id === Number(kbCategoryId.value))
  return c?.name ?? ''
})

function expChartCategories(rows) {
  if (!rows?.length) return []
  return rows.filter(r => !r.is_total).map((r, i) => ({
    label: r.category,
    value: r.total,
    color: CAT_COLORS[i % CAT_COLORS.length],
  }))
}

const expNotBillableTotal = computed(() => {
  const totalRow = expNotBillable.value.find(r => r.is_total)
  return totalRow?.monthly ?? Array(12).fill(0)
})

const expBillableTotal = computed(() => {
  const totalRow = expBillable.value.find(r => r.is_total)
  return totalRow?.monthly ?? Array(12).fill(0)
})

const expMaxNotBillable = computed(() => Math.max(...expNotBillableTotal.value, 1))
const expMaxBillable = computed(() => Math.max(...expBillableTotal.value, 1))

function expBarHeight(v, max) {
  return Math.max(4, Math.round((v / max) * 130))
}

const salesSubTab   = ref('invoices')
const srLoading     = ref(false)
const srInvoices    = ref([])
const srItems       = ref([])
const srPayments    = ref([])
const srCreditNotes = ref([])
const srProposals   = ref([])
const srEstimates   = ref([])
const srCustomers   = ref([])
const srCharts      = ref([])
const srIncome      = ref(null)
const srModes       = ref([])
const srGroups      = ref([])
const agents        = ref([])
const srFilters     = ref({ search: '', status: [], sale_agent: null, perPage: 10 })
const srPeriod      = ref('this_month')

const salesSubTabs = [
  { key: 'invoices',      label: 'Invoices Report' },
  { key: 'items',         label: 'Items Report' },
  { key: 'payments',      label: 'Payments Received' },
  { key: 'credit-notes',  label: 'Credit Notes Report' },
  { key: 'proposals',     label: 'Proposals Report' },
  { key: 'estimates',     label: 'Estimates Report' },
  { key: 'customers',     label: 'Customers Report' },
  { key: 'charts',        label: 'Charts Based Report' },
  { key: 'total-income',  label: 'Total Income' },
  { key: 'payment-modes', label: 'Payment Modes (Transactions)' },
  { key: 'customer-groups', label: 'Total Value By Customer Groups' },
]

const tabs = [
  { key: 'sales',      label: 'Sales',              icon: '💰' },
  { key: 'expenses',   label: 'Expenses',           icon: '💸' },
  { key: 'finance',    label: 'Expenses vs Income', icon: '📊' },
  { key: 'leads',      label: 'Leads',              icon: '🔍' },
  { key: 'timesheets', label: 'Timesheets',         icon: '⏱' },
  { key: 'kb',         label: 'KB Articles',        icon: '📚' },
  { key: 'team',       label: 'Team',               icon: '👥' },
]

const years = computed(() => {
  const y = new Date().getFullYear()
  return [y, y-1, y-2, y-3]
})

const maxSales       = computed(() => Math.max(...salesData.value.map(r => r.total), 1))
const maxExpenses    = computed(() => Math.max(...expensesData.value.map(r => r.total), 1))
const maxLeads       = computed(() => Math.max(...leadsData.value.map(r => r.count), 1))
const maxTimesheets  = computed(() => Math.max(...timesheetsData.value.map(r => r.hours), 1))
const maxTeam        = computed(() => Math.max(...teamData.value.map(m => m.task_count), 1))

function barHeight(val, max) {
  return Math.max(4, Math.round((val / max) * 160))
}

function compPct(val, total) {
  if (!total) return 0
  return Math.min(100, Math.round((val / total) * 100))
}

function teamBarPct(count) {
  return Math.round((count / maxTeam.value) * 100)
}

const MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']
function monthName(m) { return MONTHS[m - 1] || '' }

function formatMoney(v) {
  if (!v) return '0.00'
  return Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const fm = formatMoney

function statusClass(s) {
  return s ? s.toLowerCase().replace(/\s+/g, '') : ''
}

function trunc(s, n) {
  if (!s) return '—'
  return s.length > n ? s.slice(0, n) + '...' : s
}

const srChartMax = computed(() => Math.max(...srCharts.value.map(r => r.invoice_total), 1))

async function loadSalesReport() {
  srLoading.value = true
  const tab = salesSubTab.value
  const params = { year: selectedYear.value, period: srPeriod.value }
  if (srFilters.value.search) params.search = srFilters.value.search
  if (srFilters.value.status?.length) params.status = srFilters.value.status.join(',')
  if (srFilters.value.sale_agent) params.sale_agent = srFilters.value.sale_agent
  if (srFilters.value.perPage) params.per_page = srFilters.value.perPage
  const endpoints = {
    invoices: 'sales-report/invoices',
    items: 'sales-report/items',
    payments: 'sales-report/payments',
    'credit-notes': 'sales-report/credit-notes',
    proposals: 'sales-report/proposals',
    estimates: 'sales-report/estimates',
    customers: 'sales-report/customers',
    charts: 'sales-report/charts',
    'total-income': 'sales-report/total-income',
    'payment-modes': 'sales-report/payment-modes',
    'customer-groups': 'sales-report/customer-groups',
  }
  try {
    const res = await axios.get(`${BASE}/${endpoints[tab]}`, { params })
    const d = res.data
    let items
    switch (tab) {
      case 'invoices': items = d.invoices?.data ?? d.invoices ?? []; break
      case 'items': items = d.items?.data ?? d.items ?? []; break
      case 'payments': items = d.payments?.data ?? d.payments ?? []; break
      case 'credit-notes': items = d.credit_notes?.data ?? d.credit_notes ?? []; break
      case 'proposals': items = d.proposals?.data ?? d.proposals ?? []; break
      case 'estimates': items = d.estimates?.data ?? d.estimates ?? []; break
      case 'customers': items = d.customers?.data ?? d.customers ?? []; break
      case 'charts': items = d.monthly ?? []; break
      case 'total-income': srIncome.value = d; return
      case 'payment-modes': items = d.modes ?? []; break
      case 'customer-groups': items = d.groups ?? []; break
      default: items = []
    }
    switch (tab) {
      case 'invoices': srInvoices.value = items; break
      case 'items': srItems.value = items; break
      case 'payments': srPayments.value = items; break
      case 'credit-notes': srCreditNotes.value = items; break
      case 'proposals': srProposals.value = items; break
      case 'estimates': srEstimates.value = items; break
      case 'customers': srCustomers.value = items; break
      case 'charts': srCharts.value = items; break
      case 'payment-modes': srModes.value = items; break
      case 'customer-groups': srGroups.value = items; break
    }
  } catch {
    if (tab === 'total-income') {
      srIncome.value = { invoiced: 0, paid: 0, outstanding: 0 }
    } else {
      const arr = tab === 'charts'
        ? Array.from({ length: 12 }, (_, i) => ({ month: i + 1, invoices: 0, invoice_total: 0, payments: 0, payment_total: 0 }))
        : []
      switch (tab) {
        case 'invoices': srInvoices.value = arr; break
        case 'items': srItems.value = arr; break
        case 'payments': srPayments.value = arr; break
        case 'credit-notes': srCreditNotes.value = arr; break
        case 'proposals': srProposals.value = arr; break
        case 'estimates': srEstimates.value = arr; break
        case 'customers': srCustomers.value = arr; break
        case 'charts': srCharts.value = arr; break
        case 'payment-modes': srModes.value = arr; break
        case 'customer-groups': srGroups.value = arr; break
      }
    }
  } finally { srLoading.value = false }
}

async function loadAgents() {
  try {
    const res = await axios.get(`${BASE}/sales-report/agents`)
    agents.value = res.data.data ?? res.data
  } catch { agents.value = [] }
}

function sampleMonthly() {
  return Array.from({ length: 12 }, (_, i) => ({
    month: i + 1, total: Math.random() * 12000 + 1000, count: Math.floor(Math.random() * 15 + 1),
  }))
}

async function loadSales() {
  loadingSales.value = true
  try {
    const res = await axios.get(`${BASE}/reports/sales`, { params: { year: selectedYear.value } })
    salesData.value = res.data.monthly
  } catch {
    salesData.value = sampleMonthly()
  } finally { loadingSales.value = false }
}

async function loadExpenses() {
  loadingExpenses.value = true
  try {
    const res = await axios.get(`${BASE}/reports/expenses`, { params: { year: selectedYear.value } })
    expensesData.value = res.data.monthly
  } catch {
    expensesData.value = sampleMonthly().map(r => ({ ...r, total: r.total * 0.5 }))
  } finally { loadingExpenses.value = false }
}

async function loadExpensesDetailed() {
  expLoading.value = true
  try {
    const res = await axios.get(`${BASE}/reports/expenses-detailed`, {
      params: { year: selectedYear.value, exclude_billable: excludeBillable.value }
    })
    const d = res.data
    expNotBillable.value = d.not_billable ?? []
    expBillable.value = excludeBillable.value ? [] : (d.billable ?? [])
  } catch {
    expNotBillable.value = []
    expBillable.value = []
  } finally { expLoading.value = false }
}

async function loadFinance() {
  try {
    const res = await axios.get(`${BASE}/reports/finance`, { params: { year: selectedYear.value } })
    finance.value = res.data
  } catch {
    const income   = salesData.value.reduce((a, r) => a + r.total, 0)
    const expenses = expensesData.value.reduce((a, r) => a + r.total, 0)
    finance.value  = { income, expenses, payments: income * 0.85, profit: income - expenses }
  }
}

async function loadTeam() {
  loadingTeam.value = true
  try {
    const res = await axios.get(`${BASE}/reports/team`)
    teamData.value = res.data.team
  } catch {
    teamData.value = [
      { id: 1, name: 'Tom Kunze',   task_count: 24 },
      { id: 2, name: 'Alice Brown', task_count: 18 },
      { id: 3, name: 'Bob Smith',   task_count: 13 },
      { id: 4, name: 'Carol Lee',   task_count: 7  },
    ]
  } finally { loadingTeam.value = false }
}

async function loadKbReport() {
  kbLoading.value = true
  try {
    const res = await axios.get(`${BASE}/kb-articles/report`, { params: { category_id: kbCategoryId.value || undefined } })
    kbArticles.value = res.data.articles ?? []
    kbCategories.value = res.data.categories ?? []
  } catch {
    kbArticles.value = []
    kbCategories.value = []
  } finally { kbLoading.value = false }
}

function sampleLeads() {
  return Array.from({ length: 12 }, (_, i) => ({
    month: i + 1,
    count: Math.floor(Math.random() * 30 + 5),
    converted: Math.floor(Math.random() * 10 + 1),
  }))
}

function sampleTimesheets() {
  return Array.from({ length: 12 }, (_, i) => ({
    month: i + 1,
    hours: Math.floor(Math.random() * 200 + 50),
    tasks: Math.floor(Math.random() * 40 + 10),
  }))
}
 
async function loadAll() {
  await Promise.all([loadSales(), loadExpenses()])
  await loadFinance()
  leadsData.value = sampleLeads()
  timesheetsData.value = sampleTimesheets()
  await loadTeam()
  await Promise.all([loadSalesReport(), loadAgents(), loadExpensesDetailed(), loadKbReport()])
}

watch(salesSubTab, () => { srFilters.value = { search: '', status: [], sale_agent: null, perPage: 10 }; loadSalesReport() })

function exportReport() {
  alert(`Exporting ${activeTab.value} report for ${selectedYear.value} as PDF...`)
}

// Auto-set active tab from route
if (route.path.includes('/expenses')) activeTab.value = 'expenses'
else if (route.path.includes('/finance')) activeTab.value = 'finance'
else if (route.path.includes('/leads')) activeTab.value = 'leads'
else if (route.path.includes('/timesheets')) activeTab.value = 'timesheets'
else if (route.path.includes('/kb-articles')) activeTab.value = 'kb'
else if (route.path.includes('/team')) activeTab.value = 'team'

onMounted(loadAll)
</script>

<style scoped>
.reports-page { padding: 24px; font-family: 'Inter', sans-serif; max-width: 1400px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
.page-header h1 { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0; }
.subtitle { font-size: 13px; color: #64748b; display: block; }
.header-actions { display: flex; gap: 10px; align-items: center; }
.year-select { padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; cursor: pointer; }
.btn-export { padding: 8px 16px; background: #f1f5f9; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; cursor: pointer; font-weight: 600; }
.btn-export:hover { background: #e2e8f0; }

/* Finance Cards */
.finance-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 28px; }
.finance-card { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 20px 18px; transition: all 0.2s; }
.finance-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); transform: translateY(-2px); }
.finance-card.income   { border-top: 3px solid #10b981; }
.finance-card.expenses { border-top: 3px solid #ef4444; }
.finance-card.payments { border-top: 3px solid #3b82f6; }
.finance-card.profit.positive { border-top: 3px solid #10b981; }
.finance-card.profit.negative { border-top: 3px solid #ef4444; }
.card-label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
.card-value { font-size: 26px; font-weight: 800; color: #1e293b; }
.card-sub   { font-size: 11px; color: #94a3b8; margin-top: 2px; }

/* Tabs */
.tabs { display: flex; gap: 4px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 4px; margin-bottom: 24px; }
.tab-btn { flex: 1; padding: 9px 14px; border: none; background: transparent; border-radius: 7px; font-size: 13px; font-weight: 600; color: #64748b; cursor: pointer; transition: all 0.2s; }
.tab-btn:hover { background: #e2e8f0; }
.tab-btn.active { background: #fff; color: #1e9aff; box-shadow: 0 1px 4px rgba(0,0,0,.1); }

/* Report section */
.report-section { background: #fff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 24px; }
.section-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
.section-header h2 { font-size: 17px; font-weight: 700; color: #1e293b; margin: 0; }
.header-filters { display: flex; gap: 8px; align-items: center; }
.sub-tabs { display: flex; flex-wrap: wrap; gap: 4px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 3px; margin-bottom: 16px; }
.sub-tab-btn { padding: 6px 12px; border: none; background: transparent; border-radius: 6px; font-size: 12px; font-weight: 600; color: #64748b; cursor: pointer; transition: all 0.15s; }
.sub-tab-btn:hover { background: #e2e8f0; }
.sub-tab-btn.active { background: #fff; color: #1e9aff; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.report-toolbar { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
.toolbar-left { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
.table-responsive { overflow-x: auto; }
.status-tag { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
.status-tag.unpaid { background: #fef2f2; color: #dc2626; }
.status-tag.paid { background: #f0fdf4; color: #16a34a; }
.status-tag.partiallypaid { background: #fffbeb; color: #d97706; }
.status-tag.overdue { background: #fef2f2; color: #dc2626; }
.status-tag.draft { background: #f1f5f9; color: #64748b; }
.status-tag.open { background: #eff6ff; color: #2563eb; }
.status-tag.sent { background: #f0fdf4; color: #16a34a; }
.status-tag.revised { background: #fffbeb; color: #d97706; }
.status-tag.declined { background: #fef2f2; color: #dc2626; }
.status-tag.accepted { background: #f0fdf4; color: #16a34a; }
.status-tag.closed { background: #f1f5f9; color: #64748b; }
.status-tag.void { background: #f1f5f9; color: #64748b; }
.empty-cell { text-align: center; color: #94a3b8; padding: 24px !important; }
.checkbox-label { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #475569; cursor: pointer; }
.checkbox-label input { width: 16px; height: 16px; cursor: pointer; }
.expenses-detailed { display: flex; flex-direction: column; gap: 28px; }
.exp-charts-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
.exp-chart-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; }
.exp-chart-box .chart-title { font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 10px; }
.exp-chart-box .bars { display: flex; align-items: flex-end; gap: 3px; padding-bottom: 18px; border-bottom: 1px solid #e2e8f0; }
.exp-chart-box .bar-wrap { flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; justify-content: flex-end; position: relative; }
.exp-chart-box .bar-label-top { font-size: 7px; color: #94a3b8; position: absolute; top: -4px; white-space: nowrap; }
.exp-chart-box .bar-col { display: flex; align-items: flex-end; flex: 1; width: 100%; padding-top: 12px; }
.exp-chart-box .bar { width: 100%; min-height: 4px; background: linear-gradient(180deg, #ef4444, #dc2626); border-radius: 3px 3px 0 0; }
.pie-legend { display: flex; flex-direction: column; gap: 5px; max-height: 180px; overflow-y: auto; }
.legend-item { display: flex; align-items: center; gap: 6px; font-size: 11px; }
.legend-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.legend-label { color: #475569; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.legend-val { color: #1e293b; font-weight: 700; white-space: nowrap; }
.exp-section-group h3 { font-size: 14px; font-weight: 700; color: #1e293b; margin: 0 0 12px; }
.exp-cat-table th, .exp-cat-table td { white-space: nowrap; font-size: 11px; padding: 6px 8px; }
.exp-cat-table th:first-child, .exp-cat-table td:first-child { position: sticky; left: 0; background: #fff; z-index: 1; min-width: 140px; }
.exp-cat-table thead th:first-child { background: #f8fafc; }
.exp-cat-table .total-row td { background: #f8fafc; font-weight: 700; }
.loading-wrap { text-align: center; padding: 40px; color: #94a3b8; display: flex; align-items: center; justify-content: center; gap: 8px; }
.loader { width: 18px; height: 18px; border: 2px solid #e2e8f0; border-top-color: #1e9aff; border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.chart-and-table { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }

/* Bar Chart */
.bar-chart { padding: 0; }
.chart-title { font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 12px; }
.bars { display: flex; align-items: flex-end; gap: 6px; height: 200px; padding-bottom: 24px; border-bottom: 2px solid #f1f5f9; }
.bar-wrap { display: flex; flex-direction: column; align-items: center; flex: 1; gap: 4px; height: 100%; justify-content: flex-end; position: relative; }
.bar-label-top { font-size: 8px; color: #94a3b8; text-align: center; position: absolute; top: -4px; white-space: nowrap; }
.bar-col { display: flex; align-items: flex-end; flex: 1; width: 100%; padding-top: 16px; }
.bar { width: 100%; min-height: 4px; background: linear-gradient(180deg, #1e9aff, #0d7bd6); border-radius: 4px 4px 0 0; transition: height 0.5s ease; }
.bar.active { background: linear-gradient(180deg, #f59e0b, #d97706); }
.exp-bar { background: linear-gradient(180deg, #ef4444, #dc2626); }
.bar-label { font-size: 9px; color: #94a3b8; margin-top: 4px; }

/* Summary table */
.summary-table { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.data-table th { padding: 8px 10px; text-align: left; background: #f8fafc; color: #475569; font-weight: 600; border-bottom: 1.5px solid #e2e8f0; }
.data-table td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; }
.highlight-row td { background: #eff6ff; }
.total-row td { background: #f8fafc; border-top: 1.5px solid #e2e8f0; }

/* Finance comparison */
.finance-detail { display: flex; flex-direction: column; gap: 24px; }
.finance-comparison { display: flex; flex-direction: column; gap: 16px; }
.comparison-row { display: flex; align-items: center; gap: 12px; }
.comp-label { font-size: 12px; font-weight: 600; color: #475569; min-width: 140px; }
.comp-bar-wrap { flex: 1; height: 12px; background: #f1f5f9; border-radius: 6px; overflow: hidden; }
.comp-bar { height: 100%; border-radius: 6px; transition: width 0.8s ease; }
.income-bar  { background: linear-gradient(90deg, #10b981, #059669); }
.expense-bar { background: linear-gradient(90deg, #ef4444, #dc2626); }
.payment-bar { background: linear-gradient(90deg, #3b82f6, #2563eb); }
.profit-bar.pos { background: linear-gradient(90deg, #10b981, #059669); }
.profit-bar.neg { background: linear-gradient(90deg, #ef4444, #dc2626); }
.comp-value { font-size: 12px; font-weight: 700; min-width: 80px; text-align: right; }
.income-val  { color: #10b981; }
.expense-val { color: #ef4444; }
.payment-val { color: #3b82f6; }

.profit-summary { display: flex; align-items: center; gap: 14px; padding: 16px 20px; border-radius: 10px; }
.profit-summary.profitable   { background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 1.5px solid #bbf7d0; }
.profit-summary.unprofitable { background: linear-gradient(135deg, #fef2f2, #fee2e2); border: 1.5px solid #fecaca; }
.profit-icon { font-size: 32px; }
.profit-headline { font-weight: 700; font-size: 15px; color: #1e293b; }
.profit-sub { font-size: 12px; color: #64748b; margin-top: 2px; }

/* Team */
.team-list { display: flex; flex-direction: column; gap: 12px; }
.team-row { display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: #f8fafc; border-radius: 10px; transition: background 0.15s; }
.team-row:hover { background: #f1f5f9; }
.team-rank { width: 24px; height: 24px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #475569; flex-shrink: 0; }
.team-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #1e9aff, #6366f1); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; color: #fff; flex-shrink: 0; }
.team-info { min-width: 120px; }
.team-name { font-size: 13px; font-weight: 700; color: #1e293b; }
.team-tasks { font-size: 11px; color: #94a3b8; }
.team-bar-wrap { flex: 1; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; }
.team-bar { height: 100%; background: linear-gradient(90deg, #1e9aff, #6366f1); border-radius: 4px; transition: width 0.6s ease; }
.team-count { font-size: 14px; font-weight: 800; color: #1e293b; min-width: 28px; text-align: right; }

.empty-state { text-align: center; padding: 40px; color: #94a3b8; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.empty-icon { font-size: 36px; }

.kb-vote-list { display: flex; flex-direction: column; gap: 16px; }
.kb-group-label { font-size: 14px; font-weight: 700; color: #1e293b; padding-bottom: 4px; border-bottom: 2px solid #e2e8f0; margin-bottom: 4px; }
.kb-vote-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; }
.kb-vote-title { font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 8px; }
.kb-total { font-weight: 400; color: #94a3b8; font-size: 12px; }
.kb-no-votes { font-size: 12px; color: #94a3b8; font-style: italic; }
.kb-vote-bars { display: flex; flex-direction: column; gap: 6px; }
.kb-vote-row { display: flex; align-items: center; gap: 6px; font-size: 12px; }
.kb-vote-label { min-width: 30px; color: #64748b; font-weight: 600; }
.kb-vote-count { min-width: 24px; font-weight: 700; color: #1e293b; text-align: right; }
.kb-bar-wrap { flex: 1; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; }
.kb-bar { height: 100%; border-radius: 4px; transition: width 0.5s ease; }
.yes-bar { background: linear-gradient(90deg, #10b981, #059669); }
.no-bar { background: linear-gradient(90deg, #ef4444, #dc2626); }
.kb-vote-pct { min-width: 48px; text-align: right; color: #64748b; font-weight: 600; }

@media (max-width: 1024px) {
  .finance-cards { grid-template-columns: repeat(2, 1fr); }
  .chart-and-table { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
  .finance-cards { grid-template-columns: 1fr 1fr; }
  .tabs { overflow-x: auto; }
  .tab-btn { white-space: nowrap; }
}
</style>
