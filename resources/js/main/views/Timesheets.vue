<template>
  <div class="ts-page">
    <!-- Header -->
    <div class="ts-header">
      <div class="ts-header-left">
        <h1 class="ts-title">Today</h1>
        <span class="ts-subtitle">{{ new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }) }}</span>
      </div>
      <div class="ts-header-right">
        <select class="ts-select" v-model="staffFilter">
          <option value="">All Staff Members</option>
          <option v-for="m in allMembers" :key="m" :value="m">{{ m }}</option>
        </select>
        <div class="search-wrap" style="max-width:200px">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="search-icon"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input type="text" class="search-input" v-model="search" placeholder="Search..." />
        </div>
      </div>
    </div>

    <!-- Group by Task toggle -->
    <div class="ts-toolbar">
      <label class="ts-check-label">
        <input type="checkbox" v-model="groupByTask" />
        <span>Group by Task</span>
      </label>
    </div>

    <!-- ApexChart -->
    <div class="ts-chart-card">
      <VueApexCharts type="bar" height="240" :options="chartOptions" :series="chartSeries"></VueApexCharts>
    </div>

    <!-- Table -->
    <div class="ts-table-card">
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Staff Member</th>
              <th>Task</th>
              <th class="th-tags">Timesheet Tags</th>
              <th class="th-date">Start Time</th>
              <th class="th-date">End Time</th>
              <th>Note</th>
              <th>Related</th>
              <th class="th-num">Time (h)</th>
              <th class="th-num">Time (decimal)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!filteredEntries.length">
              <td colspan="9">
                <div class="ts-empty">
                  <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" width="32" height="32"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  <span>No entries found</span>
                </div>
              </td>
            </tr>
            <tr v-for="(e, i) in filteredEntries" :key="i">
              <td class="ts-staff">
                <span class="ts-avatar">{{ e.staff.charAt(0) }}</span>
                <span class="ts-staff-name">{{ e.staff }}</span>
              </td>
              <td class="ts-task-name">{{ e.task }}</td>
              <td class="td-tags">
                <span v-for="tag in e.tags" :key="tag" class="mini-tag">{{ tag }}</span>
                <span v-if="!e.tags.length">—</span>
              </td>
              <td class="td-date">{{ e.start_time }}</td>
              <td class="td-date">{{ e.end_time }}</td>
              <td class="ts-note">{{ e.note || '—' }}</td>
              <td class="ts-related">{{ e.related || '—' }}</td>
              <td class="td-num">{{ e.time_h }}</td>
              <td class="td-num">{{ e.time_dec }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

export default defineComponent({
  name: 'TimesheetsPage',
  components: { VueApexCharts },
  setup() {
    const staffFilter = ref('');
    const groupByTask = ref(false);
    const search = ref('');

    const allMembers = ['Llewellyn Heaney', 'Scot Cremin', 'Ralph Luelwitz', 'Santino Hoppe', 'Reyes Spencer', 'Mike Bogan'];

    const timesheetTags = ['development', 'seo', 'design', 'meeting', 'review', 'bug-fix'];

    const entries = ref([]);

    const filteredEntries = computed(() => {
      let list = entries.value;
      if (staffFilter.value) {
        list = list.filter(e => e.staff === staffFilter.value);
      }
      if (search.value) {
        const q = search.value.toLowerCase();
        list = list.filter(e =>
          e.task.toLowerCase().includes(q) ||
          e.staff.toLowerCase().includes(q) ||
          e.tags.some(t => t.toLowerCase().includes(q)) ||
          (e.note && e.note.toLowerCase().includes(q))
        );
      }
      if (groupByTask.value) {
        list = [...list].sort((a, b) => a.task.localeCompare(b.task));
      }
      return list;
    });

    const chartSeries = computed(() => [
      {
        name: 'Hours',
        data: allMembers.map(m => {
          const total = entries.value
            .filter(e => e.staff === m)
            .reduce((sum, e) => sum + parseFloat(e.time_dec || 0), 0);
          return { x: m, y: Math.round(total * 100) / 100 };
        }),
      },
    ]);

    const chartOptions = computed(() => ({
      chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'inherit' },
      plotOptions: {
        bar: { borderRadius: 4, columnWidth: '50%', distributed: true },
      },
      colors: ['#6366f1', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'],
      dataLabels: { enabled: false },
      xaxis: {
        labels: { style: { fontSize: '10px', fontWeight: 600, colors: '#64748b' } },
      },
      yaxis: {
        labels: { style: { fontSize: '10px', fontWeight: 600, colors: '#64748b' }, formatter: (v) => v.toFixed(1) },
        title: { text: 'Hours', style: { fontSize: '10px', fontWeight: 600, color: '#94a3b8' } },
      },
      grid: { borderColor: '#f1f5f9' },
      tooltip: { y: { formatter: (v) => v + ' hrs' } },
      legend: { show: false },
    }));

    return {
      staffFilter, groupByTask, search,
      allMembers, entries, filteredEntries,
      chartSeries, chartOptions,
    };
  },
});
</script>

<style scoped>
.ts-page { font-family: inherit; color: #1e293b; }
.ts-header {
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 12px; margin-bottom: 16px;
}
.ts-header-left { display: flex; flex-direction: column; gap: 2px; }
.ts-title { font-size: 20px; font-weight: 800; color: #0f172a; margin: 0; }
.ts-subtitle { font-size: 12px; font-weight: 600; color: #94a3b8; }
.ts-header-right { display: flex; align-items: center; gap: 10px; }
.ts-select {
  padding: 7px 28px 7px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  color: #334155;
  background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M1 1l4 4 4-4' fill='none' stroke='%2394a3b8' stroke-width='1.5'/%3E%3C/svg%3E") no-repeat right 10px center;
  appearance: none;
  cursor: pointer;
  font-family: inherit;
  min-width: 170px;
}
.ts-select:hover { border-color: #cbd5e1; }
.ts-toolbar {
  display: flex; align-items: center; gap: 12px; margin-bottom: 14px;
}
.ts-check-label {
  display: flex; align-items: center; gap: 6px;
  font-size: 12px; font-weight: 600; color: #475569; cursor: pointer;
}
.ts-check-label input { margin: 0; }

/* Chart card */
.ts-chart-card {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
  padding: 16px; margin-bottom: 16px;
}
.ts-chart-card :deep(.apexcharts-canvas) { font-family: inherit; }

/* Table card */
.ts-table-card {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;
}
.ts-empty {
  display: flex; flex-direction: column; align-items: center;
  gap: 8px; padding: 40px 0; font-size: 13px; font-weight: 600; color: #94a3b8;
}
.ts-staff { display: flex; align-items: center; gap: 8px; }
.ts-avatar {
  width: 26px; height: 26px; border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff; font-size: 10px; font-weight: 700;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.ts-staff-name { font-size: 11px; font-weight: 600; color: #1e293b; }
.ts-task-name { font-size: 11px; font-weight: 600; color: #334155; }
.ts-note { font-size: 11px; color: #64748b; max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ts-related { font-size: 11px; color: #64748b; }

/* Inherited table styles */
.table-wrap { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.data-table thead th {
  padding: 10px 12px; text-align: left; font-size: 10px; font-weight: 700;
  color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;
  background: #f8fafc; border-bottom: 2px solid #e2e8f0; white-space: nowrap;
}
.data-table tbody td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.data-table tbody tr:last-child td { border-bottom: none; }
.th-tags { min-width: 100px; }
.th-date { white-space: nowrap; }
.th-num { text-align: center; }
.td-num { text-align: center; font-size: 12px; font-weight: 600; color: #1e293b; }
.td-date { font-size: 11px; color: #475569; white-space: nowrap; }
.td-tags { display: flex; gap: 3px; flex-wrap: wrap; }
.mini-tag {
  display: inline-block; padding: 2px 7px; border-radius: 4px;
  font-size: 9px; font-weight: 700; color: #475569;
  background: #f1f5f9; white-space: nowrap;
}

/* Search */
.search-wrap { position: relative; }
.search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; }
.search-input {
  width: 100%; padding: 7px 10px 7px 30px; border: 1px solid #e2e8f0;
  border-radius: 8px; font-size: 12px; font-weight: 500; color: #1e293b;
  background: #fff; outline: none; font-family: inherit;
}
.search-input::placeholder { color: #94a3b8; font-weight: 500; }
.search-input:focus { border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99,102,241,0.1); }
</style>
