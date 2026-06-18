<template>
  <div class="dashboard">
    <div v-if="loading" class="loading-wrap"><div class="loader"></div> Loading...</div>
    <template v-else>



    <!-- Top Stat Cards -->
    <div class="stats-row">
      <div v-for="stat in topStats" :key="stat.title" class="stat-card">
        <div class="stat-card-inner">
          <div class="stat-icon-wrap">
            <span class="stat-icon" v-html="stat.icon"></span>
          </div>
          <div class="stat-label">{{ stat.title }}</div>
          <div class="stat-value">{{ stat.display }}</div>
        </div>
        <div class="stat-bar-wrap">
          <div class="stat-bar" :class="stat.barClass" :style="{ width: stat.pct + '%' }"></div>
        </div>
      </div>
    </div>

    <!-- Main Dashboard Two-Column Grid -->
    <div class="dashboard-grid">
      
      <!-- Left Column (Wider Content) -->
      <div class="grid-left">
        
        <!-- Overviews Panel -->
        <div class="card overviews-panel">
          <div class="overview-grid">
            <!-- Invoice Overview -->
            <div class="overview-col">
              <h3 class="overview-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="15" height="15"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Invoice overview
              </h3>
              <div class="overview-rows">
                <div v-for="row in invoiceOverview" :key="row.status" class="overview-row">
                  <div class="overview-row-label">
                    <span :class="['ov-count', row.colorClass]">{{ row.count }} {{ row.label }}</span>
                    <span class="ov-pct">{{ row.percentage.toFixed(2) }}%</span>
                  </div>
                  <div class="ov-bar-track">
                    <div class="ov-bar" :class="row.barClass" :style="{ width: Math.min(row.percentage, 100) + '%' }"></div>
                  </div>
                </div>
              </div>
              <div class="overview-footer">
                <div>
                  <div class="ov-foot-label">Outstanding Invoices</div>
                  <div class="ov-foot-value">{{ formatCurrency(metrics.outstanding_amount) }}</div>
                </div>
                <div>
                  <div class="ov-foot-label">Past Due Invoices</div>
                  <div class="ov-foot-value">{{ formatCurrency(metrics.past_due_amount) }}</div>
                </div>
                <div>
                  <div class="ov-foot-label">Paid Invoices</div>
                  <div class="ov-foot-value text-success">{{ formatCurrency(metrics.paid_amount) }}</div>
                </div>
              </div>
            </div>

            <!-- Estimate Overview -->
            <div class="overview-col">
              <h3 class="overview-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="15" height="15"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Estimate overview
              </h3>
              <div class="overview-rows">
                <div v-for="row in estimateOverview" :key="row.label" class="overview-row">
                  <div class="overview-row-label">
                    <span :class="['ov-count', row.colorClass]">{{ row.count }} {{ row.label }}</span>
                    <span class="ov-pct">{{ row.percentage.toFixed(2) }}%</span>
                  </div>
                  <div class="ov-bar-track">
                    <div class="ov-bar" :class="row.barClass" :style="{ width: row.percentage + '%' }"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Proposal Overview -->
            <div class="overview-col">
              <h3 class="overview-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="15" height="15"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                Proposal overview
              </h3>
              <div class="overview-rows">
                <div v-for="row in proposalOverview" :key="row.label" class="overview-row">
                  <div class="overview-row-label">
                    <span :class="['ov-count', row.colorClass]">{{ row.count }} {{ row.label }}</span>
                    <span class="ov-pct">{{ row.percentage.toFixed(2) }}%</span>
                  </div>
                  <div class="ov-bar-track">
                    <div class="ov-bar" :class="row.barClass" :style="{ width: row.percentage + '%' }"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabs Widget: My Tasks | My Projects | My Reminders | Tickets | Announcements -->
        <div class="card tabs-panel">
          <div class="tabs-header">
            <button 
              v-for="t in ['Tasks', 'Projects', 'Reminders', 'Tickets', 'Announcements']" 
              :key="t" 
              :class="['tab-btn', { 'tab-btn--active': activeTab === t }]"
              @click="activeTab = t"
            >
              My {{ t }}
            </button>
          </div>
          
          <div class="tabs-content">
            <div class="table-toolbar">
              <div class="toolbar-left">
                <select class="select-sm"><option value="10">10</option><option value="25">25</option></select>
              </div>
              <div class="toolbar-right">
                <input class="input-sm" v-model="tabSearch" placeholder="Search..." />
              </div>
            </div>

            <table class="data-table" v-if="activeTab === 'Tasks'">
              <thead>
                <tr>
                  <th>Task Name</th>
                  <th>Status</th>
                  <th>Start Date</th>
                  <th>Due Date</th>
                  <th>Assigned To</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="task in filteredTasks" :key="task.name">
                  <td><a class="link-blue font-semibold">{{ task.name }}</a></td>
                  <td><span class="badge" :class="task.statusClass">{{ task.status }}</span></td>
                  <td>{{ task.start }}</td>
                  <td>{{ task.due }}</td>
                  <td><strong>{{ task.assigned }}</strong></td>
                </tr>
              </tbody>
            </table>

            <table class="data-table" v-if="activeTab === 'Projects'">
              <thead>
                <tr>
                  <th>Project Name</th>
                  <th>Client</th>
                  <th>Billing</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="proj in projectsMock" :key="proj.name">
                  <td><a class="link-blue font-semibold">{{ proj.name }}</a></td>
                  <td>{{ proj.client }}</td>
                  <td>{{ proj.billing }}</td>
                  <td><span class="badge badge-blue">{{ proj.status }}</span></td>
                </tr>
              </tbody>
            </table>

            <div v-if="activeTab === 'Reminders'" class="empty-cell">
              No reminders found
            </div>
            
            <div v-if="activeTab === 'Tickets'" class="empty-cell">
              No pending support tickets assigned to you
            </div>

            <div v-if="activeTab === 'Announcements'" class="empty-cell">
              No recent announcements
            </div>
          </div>
        </div>

        <!-- June 2026 Calendar Widget -->
        <div class="card calendar-panel">
          <div class="calendar-header">
            <button class="cal-nav-btn">◀</button>
            <h3 class="calendar-title">{{ calendarMonth }}</h3>
            <button class="cal-nav-btn">▶</button>
            <div class="cal-view-modes">
              <button class="btn-outline active">Month</button>
              <button class="btn-outline">Week</button>
              <button class="btn-outline">Day</button>
            </div>
          </div>
          <div class="calendar-weekdays">
            <span v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day">{{ day }}</span>
          </div>
          <div class="calendar-grid">
            <!-- Empty days at the start of June 2026 (June 1st is a Monday, so 1 empty slot for Sunday) -->
            <div class="calendar-day calendar-day--other">
              <span class="day-num">31</span>
            </div>
            <div class="calendar-day" v-for="dayNum in 30" :key="dayNum">
              <span class="day-num">{{ dayNum }}</span>
              <div class="day-events">
                <div v-if="dayNum === 3" class="cal-event cal-event--blue">E-commerce API</div>
                <div v-if="dayNum === 8" class="cal-event cal-event--red">Server Audit</div>
                <div v-if="dayNum === 11" class="cal-event cal-event--green">Site Design</div>
                <div v-if="dayNum === 15" class="cal-event cal-event--purple">SLA Review</div>
                <div v-if="dayNum === 22" class="cal-event cal-event--orange">AWS Migration</div>
              </div>
            </div>
            <!-- Empty days at the end of the calendar grid (June ends on a Tuesday, fill remaining slots up to 35 total) -->
            <div class="calendar-day calendar-day--other" v-for="nextDayNum in 4" :key="'next-'+nextDayNum">
              <span class="day-num">{{ nextDayNum }}</span>
            </div>
          </div>
        </div>

        <!-- Payment Records Chart -->
        <div class="card payment-records-panel">
          <div class="panel-header">
            <h3 class="panel-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="16" height="16" style="vertical-align: middle; margin-right: 4px;"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="12" y1="10" x2="12" y2="10"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
              Payment Records
            </h3>
            <span style="font-size:11px;color:#94a3b8">Billed vs Received</span>
          </div>
          <div class="bar-chart-container">
            <div class="chart-y-axis">
              <span>$150k</span>
              <span>$100k</span>
              <span>$50k</span>
              <span>$0</span>
            </div>
            <div class="chart-bars-wrap">
              <div class="chart-column" v-for="c in paymentColumns" :key="c.month">
                <div class="column-bars">
                  <div class="col-bar col-bar--billed" :style="{ height: c.billedPct + '%' }" :title="`Billed: ${formatCurrency(c.billed)}`"></div>
                  <div class="col-bar col-bar--received" :style="{ height: c.receivedPct + '%' }" :title="`Received: ${formatCurrency(c.received)}`"></div>
                </div>
                <div class="column-label">{{ c.month }}</div>
              </div>
            </div>
          </div>
          <div class="chart-legend">
            <div class="legend-item"><span class="legend-dot bg-billed"></span> Billed</div>
            <div class="legend-item"><span class="legend-dot bg-received"></span> Received</div>
          </div>
        </div>

        <!-- Recent Support Tickets Table -->
        <div class="card tickets-panel">
          <div class="panel-header">
            <h3 class="panel-title">Support Tickets activity</h3>
            <router-link to="/admin/support" class="link-blue" style="font-size:11.5px">View All</router-link>
          </div>
          <table class="data-table">
            <thead>
              <tr>
                <th>Subject</th>
                <th>Client</th>
                <th>Last Reply</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in ticketsMock" :key="t.id">
                <td>
                  <a class="link-blue font-semibold">#{{ t.number }} - {{ t.subject }}</a>
                  <div style="font-size:11px;color:#64748b;margin-top:2px">{{ t.excerpt }}</div>
                </td>
                <td>{{ t.client }}</td>
                <td>{{ t.last_reply }}</td>
                <td><span class="badge" :class="t.statusClass">{{ t.status }}</span></td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Staff Tickets Report Table -->
        <div class="card staff-report-panel">
          <h3 class="panel-title">Staff ticket performance report</h3>
          <table class="data-table">
            <thead>
              <tr>
                <th>Staff Member</th>
                <th>Total Assigned</th>
                <th>Open Tickets</th>
                <th>Closed Tickets</th>
                <th class="text-right">Resolve Rate</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="s in staffPerformance" :key="s.name">
                <td><strong>{{ s.name }}</strong></td>
                <td>{{ s.assigned }}</td>
                <td><span class="text-danger font-semibold">{{ s.open }}</span></td>
                <td><span class="text-success font-semibold">{{ s.closed }}</span></td>
                <td class="text-right font-semibold text-success">{{ s.rate }}%</td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
      
      <!-- Right Column (Sidebar Content) -->
      <div class="grid-right">
        
        <!-- My To Do Items Panel -->
        <div class="card todo-panel">
          <div class="todo-header">
            <h3 class="todo-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="15" height="15"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              My To Do Items
            </h3>
            <div class="todo-header-actions">
              <a class="todo-link">View All</a>
              <a class="todo-btn">New To Do</a>
            </div>
          </div>

          <div class="todo-section-label todo-section-label--pending">⚠ Latest to do's</div>
          <div class="todo-list">
            <div v-for="(item, i) in pendingTodos" :key="i" class="todo-item">
              <div class="todo-drag">⠿</div>
              <div class="todo-check-wrap">
                <input type="checkbox" class="todo-checkbox" :checked="item.done" />
              </div>
              <div class="todo-text-wrap">
                <div class="todo-text">{{ item.text }}</div>
                <div class="todo-date">{{ item.date }}</div>
              </div>
              <div class="todo-actions">
                <button class="todo-action-btn">✎</button>
                <button class="todo-action-btn todo-action-btn--del">×</button>
              </div>
            </div>
          </div>

          <div class="todo-section-label todo-section-label--done">✓ Latest finished to do's</div>
          <div class="todo-list">
            <div v-for="(item, i) in doneTodos" :key="i" class="todo-item todo-item--done">
              <div class="todo-drag">⠿</div>
              <div class="todo-check-wrap">
                <input type="checkbox" class="todo-checkbox" checked />
              </div>
              <div class="todo-text-wrap">
                <div class="todo-text todo-text--done">{{ item.text }}</div>
                <div class="todo-date">{{ item.date }}</div>
              </div>
              <div class="todo-actions">
                <button class="todo-action-btn">✎</button>
                <button class="todo-action-btn todo-action-btn--del">×</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Leads Overview (Donut Chart) -->
        <div class="card donut-card">
          <h3 class="panel-title">Leads Overview</h3>
          <div class="donut-chart-wrap">
            <div class="donut-svg-container">
              <svg width="120" height="120" viewBox="0 0 36 36" class="donut-svg">
                <circle cx="18" cy="18" r="15.915" fill="none" stroke="#f1f5f9" stroke-width="4.5"></circle>
                <circle
                  v-for="(slice, idx) in leadDonutSlices"
                  :key="idx"
                  cx="18"
                  cy="18"
                  r="15.915"
                  fill="none"
                  :stroke="slice.color"
                  stroke-width="4.5"
                  :stroke-dasharray="`${slice.percentage} ${100 - slice.percentage}`"
                  :stroke-dashoffset="slice.offset"
                ></circle>
              </svg>
              <div class="donut-center">
                <div class="donut-center-val">{{ metrics.total_leads || 53 }}</div>
                <div class="donut-center-lbl">Leads</div>
              </div>
            </div>
            <div class="donut-legend">
              <div v-for="item in leadDonutSlices" :key="item.name" class="legend-item">
                <span class="legend-dot" :style="{ background: item.color }"></span>
                <span class="legend-label">{{ item.name }}: <strong>{{ item.count }}</strong></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Project Status Chart (Donut Chart) -->
        <div class="card donut-card">
          <h3 class="panel-title">Projects Status Chart</h3>
          <div class="donut-chart-wrap">
            <div class="donut-svg-container">
              <svg width="120" height="120" viewBox="0 0 36 36" class="donut-svg">
                <circle cx="18" cy="18" r="15.915" fill="none" stroke="#f1f5f9" stroke-width="4.5"></circle>
                <circle
                  v-for="(slice, idx) in projectDonutSlices"
                  :key="idx"
                  cx="18"
                  cy="18"
                  r="15.915"
                  fill="none"
                  :stroke="slice.color"
                  stroke-width="4.5"
                  :stroke-dasharray="`${slice.percentage} ${100 - slice.percentage}`"
                  :stroke-dashoffset="slice.offset"
                ></circle>
              </svg>
              <div class="donut-center">
                <div class="donut-center-val">5</div>
                <div class="donut-center-lbl">Projects</div>
              </div>
            </div>
            <div class="donut-legend">
              <div v-for="item in projectDonutSlices" :key="item.name" class="legend-item">
                <span class="legend-dot" :style="{ background: item.color }"></span>
                <span class="legend-label">{{ item.name }}: <strong>{{ item.count }}</strong></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Tickets by Status Chart (Donut Chart) -->
        <div class="card donut-card">
          <h3 class="panel-title">Tickets Status Chart</h3>
          <div class="donut-chart-wrap">
            <div class="donut-svg-container">
              <svg width="120" height="120" viewBox="0 0 36 36" class="donut-svg">
                <circle cx="18" cy="18" r="15.915" fill="none" stroke="#f1f5f9" stroke-width="4.5"></circle>
                <circle
                  v-for="(slice, idx) in ticketDonutSlices"
                  :key="idx"
                  cx="18"
                  cy="18"
                  r="15.915"
                  fill="none"
                  :stroke="slice.color"
                  stroke-width="4.5"
                  :stroke-dasharray="`${slice.percentage} ${100 - slice.percentage}`"
                  :stroke-dashoffset="slice.offset"
                ></circle>
              </svg>
              <div class="donut-center">
                <div class="donut-center-val">5</div>
                <div class="donut-center-lbl">Tickets</div>
              </div>
            </div>
            <div class="donut-legend">
              <div v-for="item in ticketDonutSlices" :key="item.name" class="legend-item">
                <span class="legend-dot" :style="{ background: item.color }"></span>
                <span class="legend-label">{{ item.name }}: <strong>{{ item.count }}</strong></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Contracts Expiring Soon Chart (Donut Chart) -->
        <div class="card donut-card">
          <h3 class="panel-title">Contracts Status Chart</h3>
          <div class="donut-chart-wrap">
            <div class="donut-svg-container">
              <svg width="120" height="120" viewBox="0 0 36 36" class="donut-svg">
                <circle cx="18" cy="18" r="15.915" fill="none" stroke="#f1f5f9" stroke-width="4.5"></circle>
                <circle
                  v-for="(slice, idx) in contractDonutSlices"
                  :key="idx"
                  cx="18"
                  cy="18"
                  r="15.915"
                  fill="none"
                  :stroke="slice.color"
                  stroke-width="4.5"
                  :stroke-dasharray="`${slice.percentage} ${100 - slice.percentage}`"
                  :stroke-dashoffset="slice.offset"
                ></circle>
              </svg>
              <div class="donut-center">
                <div class="donut-center-val">5</div>
                <div class="donut-center-lbl">Contracts</div>
              </div>
            </div>
            <div class="donut-legend">
              <div v-for="item in contractDonutSlices" :key="item.name" class="legend-item">
                <span class="legend-dot" :style="{ background: item.color }"></span>
                <span class="legend-label">{{ item.name }}: <strong>{{ item.count }}</strong></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Project Performance Metrics -->
        <div class="card progress-card">
          <h3 class="panel-title">Project Progress Tracker</h3>
          <div class="progress-bars-list">
            <div v-for="p in projectProgressList" :key="p.name" class="progress-bar-row">
              <div class="progress-bar-info">
                <span class="progress-name">{{ p.name }}</span>
                <span class="progress-val">{{ p.percentage }}%</span>
              </div>
              <div class="progress-track">
                <div class="progress-fill" :class="p.colorClass" :style="{ width: p.percentage + '%' }"></div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>

  </template>
  </div>
</template>

<script>
import { defineComponent, ref, computed, onMounted } from 'vue';
import axios from 'axios';

export default defineComponent({
  name: 'Dashboard',
  setup() {
    const metrics   = ref({});
    const loading   = ref(true);
    const activeTab = ref('Tasks');
    const tabSearch = ref('');
    const now       = new Date();
    const calendarMonth = computed(() => {
      const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
      return months[now.getMonth()] + ' ' + now.getFullYear();
    });

    // ── Helpers ──────────────────────────────────────────────
    const formatCurrency = (v) => {
      if (v === undefined || v === null) return '$0.00';
      return '$' + Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    const parseRatio = (str = '0 / 0') => {
      const [a, b] = (str + '').split('/').map(s => parseInt(s.trim()) || 0);
      return { num: a, den: b, pct: b > 0 ? Math.round((a / b) * 100) : 0 };
    };

    // ── Top stat cards ────────────────────────────────────────
    const topStats = computed(() => {
      const inv  = parseRatio(metrics.value.invoices_awaiting_payment);
      const lead = { num: metrics.value.converted_leads || 0, den: metrics.value.total_leads || 0 };
      lead.pct   = lead.den > 0 ? Math.round(lead.num / lead.den * 100) : 0;
      const proj = parseRatio(metrics.value.projects_in_progress);
      const task = parseRatio(metrics.value.tasks_not_finished);

      return [
        {
          title: 'Invoices Awaiting Payment',
          display: `${inv.num} / ${inv.den}`,
          pct: inv.pct,
          barClass: 'bar-red',
          icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="18" height="18"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>`,
        },
        {
          title: 'Converted Leads',
          display: `${lead.num} / ${lead.den}`,
          pct: lead.pct,
          barClass: 'bar-green',
          icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="18" height="18"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>`,
        },
        {
          title: 'Projects In Progress',
          display: `${proj.num} / ${proj.den}`,
          pct: proj.pct,
          barClass: 'bar-blue',
          icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="18" height="18"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>`,
        },
        {
          title: 'Tasks Not Finished',
          display: `${task.num} / ${task.den}`,
          pct: task.pct,
          barClass: 'bar-dark',
          icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="18" height="18"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>`,
        },
      ];
    });

    // ── Invoice overview (live from API) ──────────────────────
    const invoiceOverview = computed(() => {
      const raw = metrics.value.invoice_overview || [];
      const colorMap = {
        draft:          { colorClass: '',           barClass: 'ov-bar-slate' },
        unpaid:         { colorClass: 'text-danger', barClass: 'ov-bar-red' },
        paid:           { colorClass: 'text-success',barClass: 'ov-bar-green' },
        overdue:        { colorClass: 'text-danger', barClass: 'ov-bar-red' },
        partially_paid: { colorClass: 'text-warning',barClass: 'ov-bar-orange' },
        cancelled:      { colorClass: 'text-muted',  barClass: 'ov-bar-slate' },
      };
      return raw.map(r => ({ ...r, ...(colorMap[r.status] || {}) }));
    });

    // ── Estimate overview (static demo data) ─────────────────
    const estimateOverview = [
      { label: 'Draft',    count: 4,  percentage: 30.77, colorClass: '',            barClass: 'ov-bar-slate' },
      { label: 'Not Sent', count: 4,  percentage: 30.77, colorClass: '',            barClass: 'ov-bar-slate' },
      { label: 'Sent',     count: 6,  percentage: 46.15, colorClass: 'text-info',   barClass: 'ov-bar-blue' },
      { label: 'Expired',  count: 0,  percentage: 0,     colorClass: 'text-muted',  barClass: 'ov-bar-slate' },
      { label: 'Declined', count: 3,  percentage: 23.08, colorClass: 'text-danger', barClass: 'ov-bar-red' },
      { label: 'Accepted', count: 0,  percentage: 0,     colorClass: 'text-muted',  barClass: 'ov-bar-slate' },
    ];

    // ── Proposal overview (static demo data) ─────────────────
    const proposalOverview = [
      { label: 'Draft',    count: 0,  percentage: 0,   colorClass: '',             barClass: 'ov-bar-slate' },
      { label: 'Sent',     count: 0,  percentage: 0,   colorClass: 'text-muted',   barClass: 'ov-bar-slate' },
      { label: 'Open',     count: 1,  percentage: 50,  colorClass: '',             barClass: 'ov-bar-slate' },
      { label: 'Revised',  count: 0,  percentage: 0,   colorClass: 'text-muted',   barClass: 'ov-bar-slate' },
      { label: 'Declined', count: 0,  percentage: 0,   colorClass: 'text-danger',  barClass: 'ov-bar-red' },
      { label: 'Accepted', count: 1,  percentage: 50,  colorClass: 'text-success', barClass: 'ov-bar-green' },
    ];

    // ── To-Do ─────────────────────────────────────────────────
    const pendingTodos = computed(() =>
      (metrics.value.todo_items || []).filter(t => !t.done)
    );
    const doneTodos = computed(() =>
      (metrics.value.todo_items || []).filter(t => t.done)
    );

    // ── SVGs Donut Chart Slices Calculations ─────────────────
    const leadDonutSlices = computed(() => {
      const overview = metrics.value.leads_overview || [];
      let accum = 0;
      return overview.map(item => {
        const percentage = item.percentage || 0;
        const offset = 100 - accum + 25; // 25 to start at top (12 o'clock)
        accum += percentage;
        return {
          ...item,
          percentage,
          offset: offset % 100
        };
      });
    });

    const projectDonutSlices = computed(() => {
      const list = [
        { name: 'Finished',    count: 1, color: '#22c55e', percentage: 20 },
        { name: 'In Progress', count: 2, color: '#3b82f6', percentage: 40 },
        { name: 'On Hold',     count: 1, color: '#f59e0b', percentage: 20 },
        { name: 'Not Started', count: 1, color: '#94a3b8', percentage: 20 },
      ];
      let accum = 0;
      return list.map(item => {
        const offset = 100 - accum + 25;
        accum += item.percentage;
        return { ...item, offset: offset % 100 };
      });
    });

    const ticketDonutSlices = computed(() => {
      const list = [
        { name: 'Open',        count: 2, color: '#ef4444', percentage: 40 },
        { name: 'In Progress', count: 1, color: '#3b82f6', percentage: 20 },
        { name: 'Answered',    count: 1, color: '#22c55e', percentage: 20 },
        { name: 'Closed',      count: 1, color: '#94a3b8', percentage: 20 },
      ];
      let accum = 0;
      return list.map(item => {
        const offset = 100 - accum + 25;
        accum += item.percentage;
        return { ...item, offset: offset % 100 };
      });
    });

    const contractDonutSlices = computed(() => {
      const list = [
        { name: 'Signed',  count: 2, color: '#22c55e', percentage: 40 },
        { name: 'Sent',    count: 1, color: '#f59e0b', percentage: 20 },
        { name: 'Draft',   count: 1, color: '#94a3b8', percentage: 20 },
        { name: 'Expired', count: 1, color: '#ef4444', percentage: 20 },
      ];
      let accum = 0;
      return list.map(item => {
        const offset = 100 - accum + 25;
        accum += item.percentage;
        return { ...item, offset: offset % 100 };
      });
    });

    // ── Payment Records Columns ──────────────────────────────
    const paymentColumns = [
      { month: 'Jan', billed: 42000, billedPct: 28,  received: 38000, receivedPct: 25 },
      { month: 'Feb', billed: 78000, billedPct: 52,  received: 62000, receivedPct: 41 },
      { month: 'Mar', billed: 110000,billedPct: 73,  received: 95000, receivedPct: 63 },
      { month: 'Apr', billed: 64000, billedPct: 42,  received: 64000, receivedPct: 42 },
      { month: 'May', billed: 95000, billedPct: 63,  received: 80000, receivedPct: 53 },
      { month: 'Jun', billed: 145000,billedPct: 96,  received: 120000,receivedPct: 80 },
    ];

    // ── Mocks for Tables ─────────────────────────────────────
    const tasksMock = [
      { name: 'Configure Stripe Webhook triggers', status: 'In Progress', start: 'Jun 10, 2026', due: 'Jun 20, 2026', assigned: 'Marcus Lesch', statusClass: 'badge-blue' },
      { name: 'Database migrations to MAMP MySQL', status: 'Completed', start: 'Jun 01, 2026', due: 'Jun 05, 2026', assigned: 'Alexander', statusClass: 'badge-green' },
      { name: 'Client portal responsive layout fixes', status: 'Testing', start: 'Jun 12, 2026', due: 'Jun 15, 2026', assigned: 'Tamara Howell', statusClass: 'badge-yellow' },
      { name: 'Theme style builder customizations', status: 'Not Started', start: 'Jun 14, 2026', due: 'Jun 25, 2026', assigned: 'Elias Konopelski', statusClass: 'badge-default' },
    ];

    const projectsMock = [
      { name: 'E-commerce API Integration', client: 'Nader-Abernathy', billing: 'Fixed Rate', status: 'In Progress' },
      { name: 'Brand Strategy Redesign', client: 'Mertz-Bergnaum', billing: 'Fixed Rate', status: 'In Progress' },
    ];

    const ticketsMock = [
      { id: 1, number: 1024, subject: 'Cannot connect to database', excerpt: 'Getting access denied errors for user crm_db_user from 10.0.3...', client: 'Nader-Abernathy', last_reply: 'Jun 13, 2026 14:22', status: 'In Progress', statusClass: 'badge-blue' },
      { id: 2, number: 1022, subject: 'Billing discrepancy - Invoice INV-00018', excerpt: 'Double charge on credit card for the maintenance SLA billing cycle...', client: 'Schroeder and Sons', last_reply: 'Jun 12, 2026 09:12', status: 'Open', statusClass: 'badge-red' },
      { id: 3, number: 1020, subject: 'Surveys feedback result exports failing', excerpt: 'Getting 500 error when clicking CSV exports for goals v2.3...', client: 'Halvorson LLC', last_reply: 'Jun 13, 2026 10:15', status: 'Open', statusClass: 'badge-red' }
    ];

    const staffPerformance = [
      { name: 'Armando Turcotte', assigned: 15, open: 2, closed: 13, rate: 86 },
      { name: 'Elias Konopelski', assigned: 10, open: 1, closed: 9, rate: 90 },
      { name: 'Tamara Howell', assigned: 12, open: 0, closed: 12, rate: 100 },
      { name: 'Marcus Lesch', assigned: 8, open: 3, closed: 5, rate: 62 },
      { name: 'Rosie Trantow', assigned: 6, open: 1, closed: 5, rate: 83 }
    ];

    const projectProgressList = [
      { name: 'E-commerce API Integration', percentage: 84, colorClass: 'bg-info' },
      { name: 'Brand Strategy Redesign', percentage: 100, colorClass: 'bg-success' },
      { name: 'Legacy App Migration', percentage: 50, colorClass: 'bg-warning' },
      { name: 'SEO Auditing & Content writing', percentage: 100, colorClass: 'bg-success' },
      { name: 'DevOps CI/CD Automation', percentage: 0, colorClass: 'bg-default' }
    ];

    const filteredTasks = computed(() => {
      if (!tabSearch.value) return tasksMock;
      const q = tabSearch.value.toLowerCase();
      return tasksMock.filter(t => t.name.toLowerCase().includes(q) || t.assigned.toLowerCase().includes(q));
    });

    // ── Load Metrics ──────────────────────────────────────────
    const loadMetrics = async () => {
      loading.value = true;
      try {
        const res = await axios.get('/api/dashboard-metrics');
        metrics.value = res.data;
      } catch (e) {
        console.error('Failed to load metrics', e);
      } finally {
        loading.value = false;
      }
    };

    onMounted(loadMetrics);

    return {
      metrics, loading, topStats,
      invoiceOverview, estimateOverview, proposalOverview,
      pendingTodos, doneTodos, formatCurrency,
      activeTab, tabSearch, filteredTasks,
      leadDonutSlices, projectDonutSlices, ticketDonutSlices, contractDonutSlices,
      paymentColumns, tasksMock, projectsMock, ticketsMock, staffPerformance, projectProgressList
    };
  }
});
</script>

<style scoped>
.dashboard {
  font-family: 'Inter', -apple-system, sans-serif;
  font-size: 13px;
  color: #334155;
}
.loading-wrap { text-align: center; padding: 80px 40px; color: #94a3b8; display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 14px; }
.loader { width: 20px; height: 20px; border: 2.5px solid #e2e8f0; border-top-color: #1e9aff; border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Top Stats ──────────────────────────────────────────── */
.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 20px;
}
@media (max-width: 900px) { .stats-row { grid-template-columns: 1fr 1fr; } }
@media (max-width: 560px) { .stats-row { grid-template-columns: 1fr; } }

.stat-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.stat-card-inner {
  padding: 14px 16px 10px;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 6px;
}
.stat-icon-wrap {
  color: #94a3b8;
  display: flex;
  align-items: center;
}
.stat-label {
  font-size: 11.5px;
  font-weight: 600;
  color: #64748b;
  flex: 1;
  min-width: 100px;
}
.stat-value {
  font-size: 16px;
  font-weight: 800;
  color: #1e293b;
  white-space: nowrap;
}
.stat-bar-wrap {
  height: 5px;
  background: #f1f5f9;
  border-radius: 0 0 8px 8px;
}
.stat-bar {
  height: 100%;
  border-radius: 0;
  transition: width 1s ease;
}
.bar-red   { background: #ef4444; }
.bar-green { background: #22c55e; }
.bar-blue  { background: #3b82f6; }
.bar-dark  { background: #334155; }

/* ── Dashboard Two-Column Grid ────────────────────────── */
.dashboard-grid {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 16px;
  align-items: start;
}
@media (max-width: 1000px) { .dashboard-grid { grid-template-columns: 1fr; } }

/* Shared Card Layout */
.card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 16px;
  box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  border-bottom: 1px solid #f1f5f9;
  padding-bottom: 8px;
}
.panel-title {
  font-size: 13px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

/* ── Left Column Elements ────────────────────────────── */

/* Overviews Panel */
.overviews-panel {
  padding: 16px;
}
.overview-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0;
}
@media (max-width: 768px) { .overview-grid { grid-template-columns: 1fr; gap: 16px; } }

.overview-col {
  padding: 0 16px;
  border-right: 1px solid #f1f5f9;
}
.overview-col:first-child { padding-left: 0; }
.overview-col:last-child  { padding-right: 0; border-right: none; }

.overview-title {
  font-size: 12px;
  font-weight: 700;
  color: #475569;
  margin: 0 0 12px;
  display: flex;
  align-items: center;
  gap: 6px;
  padding-bottom: 8px;
  border-bottom: 1px solid #f1f5f9;
}
.overview-rows { display: flex; flex-direction: column; gap: 8px; margin-bottom: 14px; }
.overview-row-label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 3px;
}
.ov-count      { font-size: 11px; font-weight: 600; color: #334155; }
.ov-pct        { font-size: 11px; color: #94a3b8; }
.ov-bar-track  { height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden; }
.ov-bar        { height: 100%; border-radius: 3px; transition: width 0.8s ease; }
.ov-bar-red    { background: #ef4444; }
.ov-bar-green  { background: #22c55e; }
.ov-bar-blue   { background: #3b82f6; }
.ov-bar-orange { background: #f97316; }
.ov-bar-slate  { background: #334155; }

.text-danger  { color: #ef4444 !important; }
.text-success { color: #22c55e !important; }
.text-warning { color: #f97316 !important; }
.text-info    { color: #0ea5e9 !important; }
.text-muted   { color: #94a3b8 !important; }

.overview-footer {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  border-top: 1px solid #f1f5f9;
  padding-top: 12px;
}
.ov-foot-label {
  font-size: 10px;
  color: #94a3b8;
  font-weight: 500;
  margin-bottom: 2px;
}
.ov-foot-value {
  font-size: 13px;
  font-weight: 700;
  color: #1e293b;
}

/* Tabs Panel */
.tabs-panel { padding: 0; overflow: hidden; }
.tabs-header {
  display: flex;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}
.tab-btn {
  background: none;
  border: none;
  border-right: 1px solid #e2e8f0;
  padding: 12px 18px;
  font-size: 12.5px;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  outline: none;
  font-family: inherit;
  transition: all 0.12s;
}
.tab-btn:hover { background: #f1f5f9; }
.tab-btn--active {
  background: #fff;
  color: #0d6efd;
  border-bottom: 2px solid #0d6efd;
}
.tabs-content { padding: 16px; }

/* Table styling */
.table-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}
.toolbar-left, .toolbar-right { display: flex; gap: 8px; }
.select-sm { border:1px solid #e2e8f0; border-radius:5px; padding:4px 8px; font-size:12px; outline:none; background:#fff; }
.input-sm  { border:1px solid #e2e8f0; border-radius:5px; padding:4px 8px; font-size:12px; outline:none; width:180px; }
.data-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.data-table th { background: #f8fafc; padding: 8px 12px; text-align: left; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0; }
.data-table td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; }
.data-table tbody tr:hover { background: #f8fafc; }
.link-blue { color: #0d6efd; text-decoration: none; font-weight: 600; cursor: pointer; }
.link-blue:hover { text-decoration: underline; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 10.5px; font-weight: 600; }
.badge-blue { background: #dbeafe; color: #1d4ed8; }
.badge-green { background: #dcfce7; color: #15803d; }
.badge-red { background: #fee2e2; color: #b91c1c; }
.badge-yellow { background: #fef9c3; color: #854d0e; }
.badge-default { background: #f1f5f9; color: #475569; }
.empty-cell { text-align: center; color: #94a3b8; padding: 30px 10px; }

/* Calendar Widget */
.calendar-panel {}
.calendar-header {
  display: flex;
  align-items: center;
  margin-bottom: 14px;
  gap: 12px;
}
.cal-nav-btn {
  background: none;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  width: 28px;
  height: 28px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #475569;
}
.cal-nav-btn:hover { background: #f1f5f9; }
.calendar-title { font-size: 15px; font-weight: 700; color: #1e293b; margin: 0; }
.cal-view-modes { margin-left: auto; display: flex; gap: 4px; }
.btn-outline {
  border: 1px solid #e2e8f0;
  background: #fff;
  border-radius: 4px;
  padding: 4px 10px;
  font-size: 12px;
  color: #475569;
  cursor: pointer;
}
.btn-outline.active, .btn-outline:hover { background: #f1f5f9; color: #1e293b; }

.calendar-weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  text-align: center;
  font-weight: 600;
  color: #64748b;
  font-size: 11px;
  margin-bottom: 6px;
  border-bottom: 1px solid #f1f5f9;
  padding-bottom: 6px;
}
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 2px;
  background: #e2e8f0;
  border: 1px solid #e2e8f0;
}
.calendar-day {
  background: #fff;
  min-height: 80px;
  padding: 4px;
  display: flex;
  flex-direction: column;
  gap: 3px;
}
.calendar-day--other { background: #f8fafc; color: #94a3b8; }
.day-num { font-size: 11px; font-weight: 600; color: #64748b; }
.day-events { display: flex; flex-direction: column; gap: 2px; }
.cal-event {
  font-size: 10px;
  padding: 2px 4px;
  border-radius: 3px;
  color: #fff;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.cal-event--blue   { background: #3b82f6; }
.cal-event--red    { background: #ef4444; }
.cal-event--green  { background: #22c55e; }
.cal-event--purple { background: #8b5cf6; }
.cal-event--orange { background: #f97316; }

/* Payment Records Chart */
.payment-records-panel {}
.bar-chart-container {
  display: flex;
  gap: 12px;
  height: 200px;
  margin: 16px 0;
}
.chart-y-axis {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  font-size: 11px;
  color: #94a3b8;
  text-align: right;
  width: 35px;
  padding-bottom: 18px;
}
.chart-bars-wrap {
  flex: 1;
  display: flex;
  justify-content: space-between;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 4px;
}
.chart-column {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  gap: 6px;
  max-width: 60px;
}
.column-bars {
  display: flex;
  align-items: flex-end;
  gap: 6px;
  height: 100%;
  width: 100%;
}
.col-bar {
  flex: 1;
  border-radius: 3px 3px 0 0;
  transition: height 0.5s ease;
  min-height: 2px;
}
.col-bar--billed   { background: #86efac; } /* light green */
.col-bar--received { background: #fbcfe8; } /* light pink */
.column-label { font-size: 11px; color: #64748b; font-weight: 500; }
.chart-legend {
  display: flex;
  gap: 16px;
  justify-content: center;
  font-size: 11px;
}
.bg-billed { background: #86efac; }
.bg-received { background: #fbcfe8; }

/* Staff Ticket Report */
.staff-report-panel {}

/* ── Right Column Sidebar Elements ────────────────────────── */

/* To Do List */
.todo-panel {}
.todo-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.todo-title { font-size: 12px; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 5px; }
.todo-header-actions { display: flex; align-items: center; gap: 10px; }
.todo-link { font-size: 11.5px; color: #0d6efd; cursor: pointer; text-decoration: none; }
.todo-btn { font-size: 11px; font-weight: 600; color: #0d6efd; cursor: pointer; }
.todo-section-label { font-size: 11px; font-weight: 700; margin: 8px 0 6px; padding: 3px 0; }
.todo-section-label--pending { color: #f59e0b; }
.todo-section-label--done    { color: #22c55e; }
.todo-list { display: flex; flex-direction: column; gap: 4px; }
.todo-item { display: flex; align-items: flex-start; gap: 6px; padding: 6px 4px; border-radius: 4px; }
.todo-item:hover { background: #f8fafc; }
.todo-item--done { opacity: 0.7; }
.todo-drag { color: #cbd5e1; font-size: 14px; cursor: grab; line-height: 1.4; }
.todo-check-wrap { padding-top: 1px; }
.todo-checkbox { width: 13px; height: 13px; cursor: pointer; accent-color: #0d6efd; }
.todo-text-wrap { flex: 1; min-width: 0; }
.todo-text { font-size: 12px; color: #334155; line-height: 1.4; }
.todo-text--done { text-decoration: line-through; color: #94a3b8; }
.todo-date { font-size: 10.5px; color: #94a3b8; margin-top: 2px; }
.todo-actions { display: flex; gap: 2px; opacity: 0; transition: opacity 0.12s; }
.todo-item:hover .todo-actions { opacity: 1; }
.todo-action-btn { background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 13px; padding: 2px 4px; border-radius: 3px; line-height: 1; }
.todo-action-btn:hover { background: #f1f5f9; color: #475569; }
.todo-action-btn--del:hover { color: #ef4444; }

/* Donut Charts */
.donut-card { padding: 14px 16px; }
.donut-chart-wrap {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-top: 10px;
}
.donut-svg-container {
  position: relative;
  width: 90px;
  height: 90px;
  flex-shrink: 0;
}
.donut-svg {
  transform: rotate(-90deg);
  width: 100%;
  height: 100%;
}
.donut-center {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.donut-center-val { font-size: 15px; font-weight: 800; color: #1e293b; line-height: 1.1; }
.donut-center-lbl { font-size: 9.5px; color: #64748b; text-transform: uppercase; font-weight: 500; }

.donut-legend {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.legend-item { display: flex; align-items: center; gap: 6px; font-size: 11px; }
.legend-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
.legend-label { color: #475569; }

/* Project Progress Card */
.progress-card { padding: 14px 16px; }
.progress-bars-list { display: flex; flex-direction: column; gap: 10px; margin-top: 10px; }
.progress-bar-row {}
.progress-bar-info { display: flex; justify-content: space-between; font-size: 11px; font-weight: 500; margin-bottom: 3px; }
.progress-name { color: #475569; }
.progress-val { color: #1e293b; font-weight: 700; }
.progress-track { height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden; }
.progress-fill { height: 100%; border-radius: 3px; transition: width 0.5s ease; }

.bg-info    { background: #3b82f6; }
.bg-success { background: #22c55e; }
.bg-warning { background: #f59e0b; }
.bg-default { background: #cbd5e1; }
</style>
