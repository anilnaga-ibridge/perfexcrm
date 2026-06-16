<template>
  <div class="calendar-page">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Calendar</h1>
        <p class="text-xs text-slate-500 mt-0.5">Unified dashboard view of all scheduled CRM deadlines, expiries, and tasks</p>
      </div>
      <div class="header-actions">
        <div class="month-selector">
          <button class="nav-btn" @click="prevMonth">&larr;</button>
          <span class="current-month">{{ monthName }} {{ currentYear }}</span>
          <button class="nav-btn" @click="nextMonth">&rarr;</button>
        </div>
      </div>
    </div>

    <!-- Main Calendar Area -->
    <div class="layout-grid">
      <!-- Calendar Grid Left -->
      <div class="calendar-pane">
        <!-- Weekdays Header -->
        <div class="weekdays-grid">
          <div v-for="d in daysOfWeek" :key="d" class="weekday-lbl">{{ d }}</div>
        </div>

        <!-- Month Days Grid -->
        <div class="days-grid">
          <!-- Padding Days -->
          <div v-for="pad in paddingDays" :key="'pad-' + pad" class="day-cell padded"></div>

          <!-- Actual Month Days -->
          <div 
            v-for="day in monthDays" 
            :key="day.dateStr" 
            class="day-cell"
            :class="{ today: isToday(day.dateStr), selected: selectedDateStr === day.dateStr }"
            @click="selectDay(day.dateStr)"
          >
            <div class="day-number">{{ day.dayNum }}</div>
            <div class="day-events">
              <div 
                v-for="evt in getEventsForDay(day.dateStr)" 
                :key="evt.id" 
                class="event-badge" 
                :class="evt.type"
                :title="evt.title"
                @click.stop="viewEventDetails(evt)"
              >
                {{ evt.title }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Pane: Event Filtering & Selection Details -->
      <div class="details-pane">
        <!-- Filters checklist -->
        <div class="filters-card">
          <h3 class="card-title">Filter Events</h3>
          <div class="filter-options">
            <label class="filter-item">
              <input type="checkbox" v-model="filters.task" />
              <span class="checkbox-custom task"></span>
              Tasks
            </label>
            <label class="filter-item">
              <input type="checkbox" v-model="filters.invoice" />
              <span class="checkbox-custom invoice"></span>
              Invoices (Due)
            </label>
            <label class="filter-item">
              <input type="checkbox" v-model="filters.subscription" />
              <span class="checkbox-custom subscription"></span>
              Subscriptions
            </label>
            <label class="filter-item">
              <input type="checkbox" v-model="filters.estimate" />
              <span class="checkbox-custom estimate"></span>
              Estimates (Expiry)
            </label>
            <label class="filter-item">
              <input type="checkbox" v-model="filters.proposal" />
              <span class="checkbox-custom proposal"></span>
              Proposals
            </label>
          </div>
        </div>

        <!-- Selection Details Panel -->
        <div class="selected-day-card">
          <h3 class="card-title" v-if="selectedDateStr">
            Events for {{ formatSelectedDate(selectedDateStr) }}
          </h3>
          <h3 class="card-title" v-else>
            Select a day to view events
          </h3>

          <div class="day-events-list" v-if="selectedDateEvents.length > 0">
            <div 
              v-for="evt in selectedDateEvents" 
              :key="evt.id" 
              class="event-detail-item"
              :class="evt.type"
              @click="viewEventDetails(evt)"
            >
              <div class="detail-header-row">
                <span class="evt-type-badge">{{ evt.type.toUpperCase() }}</span>
                <span class="evt-amount" v-if="evt.amount">{{ formatCurrency(evt.amount) }}</span>
              </div>
              <h4 class="evt-title">{{ evt.title }}</h4>
              <p class="evt-desc" v-if="evt.description">{{ evt.description }}</p>
            </div>
          </div>
          <div class="empty-events" v-else-if="selectedDateStr">
            <p>No events scheduled for this day</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Event Detail Drawer -->
    <a-drawer
      v-model:open="showEventDrawer"
      title="Event Details"
      placement="right"
      :width="380"
    >
      <div v-if="activeEvent" class="event-details-content">
        <div class="event-type-banner" :class="activeEvent.type">
          {{ activeEvent.type.toUpperCase() }}
        </div>
        <h2 class="event-title-large">{{ activeEvent.title }}</h2>

        <div class="details-rows mt-4">
          <div class="detail-row">
            <span class="label">Scheduled Date</span>
            <span class="value">{{ formatSelectedDate(activeEvent.date) }}</span>
          </div>
          <div class="detail-row" v-if="activeEvent.amount">
            <span class="label">Value</span>
            <span class="value font-bold text-slate-800">{{ formatCurrency(activeEvent.amount) }}</span>
          </div>
          <div class="detail-row" v-if="activeEvent.status">
            <span class="label">Status</span>
            <span class="value text-capitalize">{{ activeEvent.status }}</span>
          </div>
          <div class="detail-row" v-if="activeEvent.description">
            <span class="label">Description</span>
            <span class="value text-slate-500">{{ activeEvent.description }}</span>
          </div>
        </div>

        <div class="drawer-footer-actions">
          <a-button @click="showEventDrawer = false" class="w-100">Close</a-button>
        </div>
      </div>
    </a-drawer>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { message } from 'ant-design-vue';
import { useEstimatesStore } from '../../store/estimatesStore';
import { useProposalsStore } from '../../store/proposalsStore';

export default defineComponent({
  name: 'CalendarPage',
  setup() {
    const estimatesStore = useEstimatesStore();
    const proposalsStore = useProposalsStore();

    const currentYear = ref(new Date().getFullYear());
    const currentMonth = ref(new Date().getMonth()); // 0-indexed
    const selectedDateStr = ref(new Date().toISOString().split('T')[0]);
    const showEventDrawer = ref(false);
    const activeEvent = ref(null);

    // API-loaded items
    const tasks = ref([]);
    const invoices = ref([]);
    const subscriptions = ref([]);

    const filters = reactive({
      task: true,
      invoice: true,
      subscription: true,
      estimate: true,
      proposal: true,
    });

    const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    const monthNames = [
      'January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'
    ];

    const monthName = computed(() => monthNames[currentMonth.value]);

    const paddingDays = computed(() => {
      // First day of current month
      const firstDay = new Date(currentYear.value, currentMonth.value, 1);
      return firstDay.getDay(); // 0 is Sunday, so if Sunday, 0 padding days
    });

    const monthDays = computed(() => {
      const days = [];
      const numDays = new Date(currentYear.value, currentMonth.value + 1, 0).getDate();
      for (let i = 1; i <= numDays; i++) {
        const d = new Date(currentYear.value, currentMonth.value, i);
        // Normalize time zone offset for correct date conversion
        const offset = d.getTimezoneOffset();
        const localDate = new Date(d.getTime() - (offset*60*1000));
        const dateStr = localDate.toISOString().split('T')[0];
        days.push({
          dayNum: i,
          dateStr
        });
      }
      return days;
    });

    // Unified events list
    const allEvents = computed(() => {
      const list = [];

      // Add Tasks
      if (filters.task) {
        tasks.value.forEach(t => {
          if (t.due_date) {
            list.push({
              id: 'task-' + t.id,
              title: t.name,
              date: t.due_date.split('T')[0],
              type: 'task',
              description: t.description || 'No description',
              status: t.status,
            });
          }
        });
      }

      // Add Invoices
      if (filters.invoice) {
        invoices.value.forEach(inv => {
          if (inv.duedate) {
            list.push({
              id: 'invoice-' + inv.id,
              title: `${inv.number} - ${inv.client?.company || 'No Company'}`,
              date: inv.duedate,
              type: 'invoice',
              amount: inv.total,
              status: inv.status,
            });
          }
        });
      }

      // Add Subscriptions
      if (filters.subscription) {
        subscriptions.value.forEach(sub => {
          if (sub.start_date) {
            list.push({
              id: 'sub-' + sub.id,
              title: `Sub: ${sub.name} - ${sub.client?.company || 'No Company'}`,
              date: sub.start_date.split('T')[0],
              type: 'subscription',
              amount: sub.amount,
              status: sub.status,
            });
          }
        });
      }

      // Add Estimates (from Pinia store)
      if (filters.estimate) {
        estimatesStore.estimates.forEach(est => {
          if (est.expiry) {
            // Check if expiry is a word (like Jul 08, 2026) and convert it
            const expiryDateStr = convertToIsoDate(est.expiry);
            list.push({
              id: 'estimate-' + est.id,
              title: `${est.number} (Expiry) - ${est.client}`,
              date: expiryDateStr,
              type: 'estimate',
              amount: est.amount,
              status: est.status,
              description: est.admin_note,
            });
          }
        });
      }

      // Add Proposals (from Pinia store)
      if (filters.proposal) {
        proposalsStore.proposals.forEach(prop => {
          if (prop.open_till) {
            const openTillIso = convertToIsoDate(prop.open_till);
            list.push({
              id: 'proposal-' + prop.id,
              title: `${prop.number} (Open Till) - ${prop.subject}`,
              date: openTillIso,
              type: 'proposal',
              amount: prop.amount,
              status: prop.status,
            });
          }
        });
      }

      return list;
    });

    const convertToIsoDate = (dateStr) => {
      if (!dateStr) return '';
      if (dateStr.includes('-')) return dateStr.split('T')[0]; // already YYYY-MM-DD
      const d = new Date(dateStr);
      if (isNaN(d.getTime())) return dateStr;
      return d.toISOString().split('T')[0];
    };

    const getEventsForDay = (dateStr) => {
      return allEvents.value.filter(evt => evt.date === dateStr);
    };

    const selectedDateEvents = computed(() => {
      if (!selectedDateStr.value) return [];
      return getEventsForDay(selectedDateStr.value);
    });

    const selectDay = (dateStr) => {
      selectedDateStr.value = dateStr;
    };

    const viewEventDetails = (evt) => {
      activeEvent.value = evt;
      showEventDrawer.value = true;
    };

    const prevMonth = () => {
      if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value -= 1;
      } else {
        currentMonth.value -= 1;
      }
    };

    const nextMonth = () => {
      if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value += 1;
      } else {
        currentMonth.value += 1;
      }
    };

    const isToday = (dateStr) => {
      const todayStr = new Date().toISOString().split('T')[0];
      return todayStr === dateStr;
    };

    const loadData = async () => {
      try {
        // Load Invoices
        const resInv = await axios.get('/api/invoices', { params: { per_page: 200 } });
        invoices.value = resInv.data.invoices?.data || [];

        // Load Tasks
        const resTasks = await axios.get('/api/tasks', { params: { all: true } });
        tasks.value = resTasks.data.tasks || [];

        // Load Subscriptions
        const resSubs = await axios.get('/api/subscriptions', { params: { per_page: 200 } });
        subscriptions.value = resSubs.data.subscriptions?.data || [];
      } catch (e) {
        console.error('Failed to load calendar data sources', e);
      }
    };

    const formatSelectedDate = (dateStr) => {
      if (!dateStr) return '';
      return new Date(dateStr).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    };

    const formatCurrency = (val) => {
      if (val === undefined || val === null) return '$0.00';
      return '$' + parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2 });
    };

    onMounted(() => {
      loadData();
    });

    return {
      currentYear, currentMonth, selectedDateStr, showEventDrawer, activeEvent,
      filters, daysOfWeek, monthName, paddingDays, monthDays, getEventsForDay,
      selectedDateEvents, selectDay, viewEventDetails, prevMonth, nextMonth,
      isToday, formatSelectedDate, formatCurrency
    };
  }
});
</script>

<style scoped>
.calendar-page {
  font-family: 'Inter', -apple-system, sans-serif;
  display: flex;
  flex-direction: column;
  height: calc(100vh - 120px);
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}
.page-title {
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.month-selector {
  display: flex;
  align-items: center;
  gap: 12px;
}
.nav-btn {
  background: #fff;
  border: 1px solid #e2e8f0;
  width: 32px;
  height: 32px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  color: #475569;
}
.nav-btn:hover { background: #f8fafc; }
.current-month {
  font-size: 14px;
  font-weight: 700;
  color: #1e293b;
  min-width: 120px;
  text-align: center;
}

/* Layout Grid */
.layout-grid {
  display: flex;
  flex: 1;
  gap: 16px;
  height: 0;
}

/* Calendar Pane */
.calendar-pane {
  flex: 1;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.weekdays-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}
.weekday-lbl {
  padding: 10px 0;
  text-align: center;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
}

.days-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  grid-template-rows: repeat(6, 1fr);
  flex: 1;
}
.day-cell {
  border-right: 1px solid #f1f5f9;
  border-bottom: 1px solid #f1f5f9;
  padding: 6px;
  display: flex;
  flex-direction: column;
  cursor: pointer;
  background: #fff;
  transition: background 0.12s;
  min-height: 0;
}
.day-cell:nth-child(7n) {
  border-right: none;
}
.day-cell:hover {
  background: #f8fafc;
}
.day-cell.padded {
  background: #fafafc;
  cursor: default;
}
.day-cell.today {
  background: #eff6ff;
}
.day-cell.selected {
  border: 1.5px solid #0d6efd;
  background: #f0f7ff;
}

.day-number {
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  margin-bottom: 4px;
}
.day-cell.today .day-number {
  color: #0d6efd;
}

.day-events {
  display: flex;
  flex-direction: column;
  gap: 2px;
  overflow-y: auto;
  flex: 1;
}

.event-badge {
  font-size: 9px;
  font-weight: 600;
  padding: 1px 4px;
  border-radius: 3px;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: pointer;
}
.event-badge.task { background: #3b82f6; }
.event-badge.invoice { background: #10b981; }
.event-badge.subscription { background: #8b5cf6; }
.event-badge.estimate { background: #f59e0b; }
.event-badge.proposal { background: #ec4899; }

/* Details Sidebar */
.details-pane {
  width: 280px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.filters-card, .selected-day-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 14px;
}
.card-title {
  font-size: 13px;
  font-weight: 700;
  color: #334155;
  margin-bottom: 12px;
  text-transform: uppercase;
  border-bottom: 1px solid #f1f5f9;
  padding-bottom: 6px;
}

.filter-options {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.filter-item {
  display: flex;
  align-items: center;
  font-size: 12px;
  color: #475569;
  cursor: pointer;
  gap: 8px;
}
.checkbox-custom {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
}
.checkbox-custom.task { background: #3b82f6; }
.checkbox-custom.invoice { background: #10b981; }
.checkbox-custom.subscription { background: #8b5cf6; }
.checkbox-custom.estimate { background: #f59e0b; }
.checkbox-custom.proposal { background: #ec4899; }

.selected-day-card {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.day-events-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.event-detail-item {
  padding: 8px;
  border-radius: 6px;
  border-left: 3px solid #cbd5e1;
  background: #f8fafc;
  cursor: pointer;
}
.event-detail-item.task { border-left-color: #3b82f6; }
.event-detail-item.invoice { border-left-color: #10b981; }
.event-detail-item.subscription { border-left-color: #8b5cf6; }
.event-detail-item.estimate { border-left-color: #f59e0b; }
.event-detail-item.proposal { border-left-color: #ec4899; }

.detail-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}
.evt-type-badge {
  font-size: 8px;
  font-weight: 700;
  padding: 1px 4px;
  background: #e2e8f0;
  color: #475569;
  border-radius: 3px;
}
.evt-amount {
  font-size: 11px;
  font-weight: 600;
  color: #334155;
}
.evt-title {
  font-size: 11.5px;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 2px 0;
}
.evt-desc {
  font-size: 10px;
  color: #64748b;
  margin: 0;
}

.empty-events {
  text-align: center;
  color: #94a3b8;
  font-size: 12px;
  padding-top: 20px;
}

/* Event Detail Drawer */
.event-type-banner {
  padding: 4px 10px;
  font-size: 10px;
  font-weight: 700;
  color: #fff;
  border-radius: 4px;
  display: inline-block;
  margin-bottom: 8px;
}
.event-type-banner.task { background: #3b82f6; }
.event-type-banner.invoice { background: #10b981; }
.event-type-banner.subscription { background: #8b5cf6; }
.event-type-banner.estimate { background: #f59e0b; }
.event-type-banner.proposal { background: #ec4899; }

.event-title-large {
  font-size: 15px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.details-rows {
  display: flex;
  flex-direction: column;
  gap: 12px;
  border-top: 1px solid #f1f5f9;
  padding-top: 12px;
}
.detail-row {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.detail-row .label {
  font-size: 10px;
  color: #94a3b8;
  font-weight: 500;
  text-transform: uppercase;
}
.detail-row .value {
  font-size: 12px;
  color: #334155;
}

.drawer-footer-actions {
  margin-top: 24px;
  border-top: 1px solid #e2e8f0;
  padding-top: 16px;
}
.w-100 { width: 100%; }
</style>
