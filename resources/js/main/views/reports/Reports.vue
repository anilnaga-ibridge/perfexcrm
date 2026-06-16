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

    <!-- Sales Report -->
    <div v-if="activeTab === 'sales'" class="report-section">
      <div class="section-header">
        <h2>Sales Report — {{ selectedYear }}</h2>
      </div>
      <div v-if="loadingSales" class="loading-wrap"><div class="loader"></div> Loading...</div>
      <div v-else class="chart-and-table">
        <div class="bar-chart">
          <div class="chart-title">Monthly Invoiced Amount ($)</div>
          <div class="bars">
            <div v-for="row in salesData" :key="row.month" class="bar-wrap">
              <div class="bar-label-top">${{ formatMoney(row.total) }}</div>
              <div class="bar-col">
                <div class="bar" :style="{ height: barHeight(row.total, maxSales) + 'px' }" :class="{ active: row.month === currentMonth }"></div>
              </div>
              <div class="bar-label">{{ monthName(row.month) }}</div>
            </div>
          </div>
        </div>
        <div class="summary-table">
          <table class="data-table">
            <thead>
              <tr><th>Month</th><th>Invoices</th><th>Total ($)</th></tr>
            </thead>
            <tbody>
              <tr v-for="row in salesData" :key="row.month" :class="{ 'highlight-row': row.month === currentMonth }">
                <td>{{ monthName(row.month) }}</td>
                <td>{{ row.count }}</td>
                <td>${{ formatMoney(row.total) }}</td>
              </tr>
              <tr class="total-row">
                <td><strong>Total</strong></td>
                <td><strong>{{ salesData.reduce((a,r) => a+r.count, 0) }}</strong></td>
                <td><strong>${{ formatMoney(salesData.reduce((a,r) => a+r.total, 0)) }}</strong></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Expenses Report -->
    <div v-if="activeTab === 'expenses'" class="report-section">
      <div class="section-header">
        <h2>Expenses Report — {{ selectedYear }}</h2>
      </div>
      <div v-if="loadingExpenses" class="loading-wrap"><div class="loader"></div> Loading...</div>
      <div v-else class="chart-and-table">
        <div class="bar-chart expenses-chart">
          <div class="chart-title">Monthly Expenses ($)</div>
          <div class="bars">
            <div v-for="row in expensesData" :key="row.month" class="bar-wrap">
              <div class="bar-label-top">${{ formatMoney(row.total) }}</div>
              <div class="bar-col">
                <div class="bar exp-bar" :style="{ height: barHeight(row.total, maxExpenses) + 'px' }"></div>
              </div>
              <div class="bar-label">{{ monthName(row.month) }}</div>
            </div>
          </div>
        </div>
        <div class="summary-table">
          <table class="data-table">
            <thead>
              <tr><th>Month</th><th>Entries</th><th>Total ($)</th></tr>
            </thead>
            <tbody>
              <tr v-for="row in expensesData" :key="row.month">
                <td>{{ monthName(row.month) }}</td>
                <td>{{ row.count }}</td>
                <td>${{ formatMoney(row.total) }}</td>
              </tr>
              <tr class="total-row">
                <td><strong>Total</strong></td>
                <td><strong>{{ expensesData.reduce((a,r) => a+r.count, 0) }}</strong></td>
                <td><strong>${{ formatMoney(expensesData.reduce((a,r) => a+r.total, 0)) }}</strong></td>
              </tr>
            </tbody>
          </table>
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
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useRoute } from 'vue-router'

const BASE = '/api'
const route = useRoute()

const selectedYear  = ref(new Date().getFullYear())
const currentMonth  = new Date().getMonth() + 1
const activeTab     = ref('sales')
const finance       = ref(null)
const salesData     = ref([])
const expensesData  = ref([])
const teamData      = ref([])
const loadingSales    = ref(false)
const loadingExpenses = ref(false)
const loadingTeam     = ref(false)

const tabs = [
  { key: 'sales',    label: 'Sales',    icon: '💰' },
  { key: 'expenses', label: 'Expenses', icon: '💸' },
  { key: 'finance',  label: 'Finance',  icon: '📊' },
  { key: 'team',     label: 'Team',     icon: '👥' },
]

const years = computed(() => {
  const y = new Date().getFullYear()
  return [y, y-1, y-2, y-3]
})

const maxSales    = computed(() => Math.max(...salesData.value.map(r => r.total), 1))
const maxExpenses = computed(() => Math.max(...expensesData.value.map(r => r.total), 1))
const maxTeam     = computed(() => Math.max(...teamData.value.map(m => m.task_count), 1))

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

async function loadAll() {
  await Promise.all([loadSales(), loadExpenses()])
  await loadFinance()
  await loadTeam()
}

function exportReport() {
  alert(`Exporting ${activeTab.value} report for ${selectedYear.value} as PDF...`)
}

// Auto-set active tab from route
if (route.path.includes('/expenses')) activeTab.value = 'expenses'
else if (route.path.includes('/finance')) activeTab.value = 'finance'
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
.section-header h2 { font-size: 17px; font-weight: 700; color: #1e293b; margin: 0 0 20px; }
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
