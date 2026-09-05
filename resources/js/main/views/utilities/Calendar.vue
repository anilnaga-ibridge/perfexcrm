<template>
  <div class="p-4 md:p-6 space-y-6 max-w-[1600px] mx-auto min-h-screen bg-[#F8F7FA]">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <div class="flex items-center space-x-2">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#7367F0] to-[#9F8ED6]"></div>
          <h1 class="text-xl md:text-2xl font-bold text-[#4B465C] tracking-tight m-0">Calendar</h1>
        </div>
        <p class="text-xs text-[#A8AAAE] mt-1 pl-4.5 mb-0">Unified schedule of all CRM tasks, invoice deadlines, estimates, and expiries</p>
      </div>

      <!-- Month Navigation Controls -->
      <div class="flex items-center gap-3">
        <div class="flex items-center bg-white border border-[#DBDADE] rounded-md shadow-2xs p-1">
          <button
            class="w-8 h-8 rounded hover:bg-[#F8F7FA] flex items-center justify-center text-[#6F6B7D] hover:text-[#7367F0] transition-colors cursor-pointer"
            @click="prevMonth"
            title="Previous Month"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
          </button>
          <span class="text-xs md:text-sm font-bold text-[#4B465C] min-w-[130px] text-center select-none">
            {{ monthName }} {{ currentYear }}
          </span>
          <button
            class="w-8 h-8 rounded hover:bg-[#F8F7FA] flex items-center justify-center text-[#6F6B7D] hover:text-[#7367F0] transition-colors cursor-pointer"
            @click="nextMonth"
            title="Next Month"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </div>

        <button
          @click="goToToday"
          class="btn-outline px-3.5 py-1.5 text-xs font-bold shadow-2xs cursor-pointer"
        >
          Today
        </button>
      </div>
    </div>

    <!-- Main Calendar Grid & Right Sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
      <!-- Calendar Pane (Span 3 cols) -->
      <div class="lg:col-span-3 bg-white border border-[#EBE9F1] rounded-lg shadow-sm overflow-hidden flex flex-col">
        <!-- Weekdays Header -->
        <div class="grid grid-cols-7 bg-[#F8F7FA] border-b border-[#EBE9F1]">
          <div
            v-for="d in daysOfWeek"
            :key="d"
            class="py-3 text-center text-[11px] font-bold uppercase tracking-wider text-[#6F6B7D]"
          >
            {{ d }}
          </div>
        </div>

        <!-- Month Days Grid -->
        <div class="grid grid-cols-7 auto-rows-fr min-h-[640px]">
          <!-- Padding Days From Prev Month -->
          <div
            v-for="pad in paddingDays"
            :key="'pad-' + pad"
            class="min-h-[105px] p-2 bg-[#FAFAFC] border-r border-b border-[#F1F0F2] last:border-r-0 select-none opacity-40"
          ></div>

          <!-- Month Days -->
          <div
            v-for="day in monthDays"
            :key="day.dateStr"
            class="min-h-[105px] p-2 border-r border-b border-[#F1F0F2] last:border-r-0 transition-all flex flex-col cursor-pointer group"
            :class="[
              isToday(day.dateStr) ? 'bg-[#7367F0]/5' : 'bg-white hover:bg-[#F8F7FA]/70',
              selectedDateStr === day.dateStr ? 'ring-2 ring-inset ring-[#7367F0] bg-[#7367F0]/10' : ''
            ]"
            @click="selectDay(day.dateStr)"
          >
            <!-- Day Number -->
            <div class="flex items-center justify-between mb-1.5">
              <span
                class="w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center transition-all"
                :class="isToday(day.dateStr) ? 'bg-[#7367F0] text-white shadow-xs' : 'text-[#6F6B7D] group-hover:text-[#4B465C]'"
              >
                {{ day.dayNum }}
              </span>
              <span v-if="getEventsForDay(day.dateStr).length > 2" class="text-[10px] font-bold text-[#A8AAAE]">
                +{{ getEventsForDay(day.dateStr).length - 2 }}
              </span>
            </div>

            <!-- Day Events Stack -->
            <div class="space-y-1 overflow-y-auto max-h-[75px] flex-1">
              <div
                v-for="evt in getEventsForDay(day.dateStr)"
                :key="evt.id"
                class="px-2 py-0.5 rounded text-[10px] font-bold truncate transition-transform hover:scale-[1.02] cursor-pointer shadow-2xs"
                :class="getEventBadgeClass(evt.type)"
                :title="evt.title"
                @click.stop="viewEventDetails(evt)"
              >
                {{ evt.title }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Pane: Event Filtering & Selected Day Details (Span 1 col) -->
      <div class="space-y-6">
        <!-- Filters Card -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm space-y-4">
          <div class="flex items-center space-x-2 pb-2 border-b border-[#F1F0F2]">
            <span class="w-2 h-2 rounded-full bg-[#7367F0]"></span>
            <h3 class="text-xs font-bold text-[#4B465C] uppercase tracking-wider m-0">Event Categories</h3>
          </div>

          <div class="space-y-2.5">
            <label class="flex items-center justify-between p-2 rounded-md hover:bg-[#F8F7FA] cursor-pointer transition-colors select-none">
              <div class="flex items-center space-x-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[#7367F0]"></span>
                <span class="text-xs font-semibold text-[#4B465C]">Tasks</span>
              </div>
              <input type="checkbox" v-model="filters.task" class="rounded border-[#DBDADE] text-[#7367F0] focus:ring-[#7367F0] w-4 h-4 cursor-pointer" />
            </label>

            <label class="flex items-center justify-between p-2 rounded-md hover:bg-[#F8F7FA] cursor-pointer transition-colors select-none">
              <div class="flex items-center space-x-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[#28C76F]"></span>
                <span class="text-xs font-semibold text-[#4B465C]">Invoices (Due)</span>
              </div>
              <input type="checkbox" v-model="filters.invoice" class="rounded border-[#DBDADE] text-[#28C76F] focus:ring-[#28C76F] w-4 h-4 cursor-pointer" />
            </label>

            <label class="flex items-center justify-between p-2 rounded-md hover:bg-[#F8F7FA] cursor-pointer transition-colors select-none">
              <div class="flex items-center space-x-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[#8B5CF6]"></span>
                <span class="text-xs font-semibold text-[#4B465C]">Subscriptions</span>
              </div>
              <input type="checkbox" v-model="filters.subscription" class="rounded border-[#DBDADE] text-[#8B5CF6] focus:ring-[#8B5CF6] w-4 h-4 cursor-pointer" />
            </label>

            <label class="flex items-center justify-between p-2 rounded-md hover:bg-[#F8F7FA] cursor-pointer transition-colors select-none">
              <div class="flex items-center space-x-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[#FF9F43]"></span>
                <span class="text-xs font-semibold text-[#4B465C]">Estimates (Expiry)</span>
              </div>
              <input type="checkbox" v-model="filters.estimate" class="rounded border-[#DBDADE] text-[#FF9F43] focus:ring-[#FF9F43] w-4 h-4 cursor-pointer" />
            </label>

            <label class="flex items-center justify-between p-2 rounded-md hover:bg-[#F8F7FA] cursor-pointer transition-colors select-none">
              <div class="flex items-center space-x-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[#EA5455]"></span>
                <span class="text-xs font-semibold text-[#4B465C]">Proposals</span>
              </div>
              <input type="checkbox" v-model="filters.proposal" class="rounded border-[#DBDADE] text-[#EA5455] focus:ring-[#EA5455] w-4 h-4 cursor-pointer" />
            </label>
          </div>
        </div>

        <!-- Selected Day Events Card -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm space-y-4">
          <div class="flex items-center justify-between pb-2 border-b border-[#F1F0F2]">
            <div class="flex items-center space-x-2">
              <span class="w-2 h-2 rounded-full bg-[#28C76F]"></span>
              <h3 class="text-xs font-bold text-[#4B465C] uppercase tracking-wider m-0">
                {{ selectedDateStr ? formatSelectedDate(selectedDateStr) : 'Selected Day' }}
              </h3>
            </div>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#F8F7FA] text-[#6F6B7D] border border-[#DBDADE]">
              {{ selectedDateEvents.length }} Events
            </span>
          </div>

          <div class="space-y-3 max-h-[380px] overflow-y-auto pr-1">
            <div
              v-for="evt in selectedDateEvents"
              :key="evt.id"
              @click="viewEventDetails(evt)"
              class="p-3 bg-[#F8F7FA] border border-[#EBE9F1] hover:border-[#7367F0]/40 rounded-lg transition-all cursor-pointer space-y-1.5 group shadow-2xs"
            >
              <div class="flex items-center justify-between">
                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider" :class="getEventTypeBadgeClass(evt.type)">
                  {{ evt.type }}
                </span>
                <span v-if="evt.amount" class="text-xs font-bold text-[#4B465C]">
                  {{ formatCurrency(evt.amount) }}
                </span>
              </div>
              <h4 class="text-xs font-bold text-[#4B465C] group-hover:text-[#7367F0] transition-colors m-0 line-clamp-2">
                {{ evt.title }}
              </h4>
              <p v-if="evt.description" class="text-[11px] text-[#A8AAAE] line-clamp-1 m-0">
                {{ evt.description }}
              </p>
            </div>

            <div v-if="!selectedDateEvents.length" class="text-center py-8 text-[#A8AAAE]">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32" class="mx-auto mb-2 opacity-50"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              <p class="text-xs font-semibold m-0">No scheduled events for this date</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Event Detail Drawer -->
    <a-drawer
      v-model:open="showEventDrawer"
      placement="right"
      :width="420"
      :destroyOnClose="true"
      class="vuexy-calendar-drawer"
    >
      <template #title>
        <div class="flex items-center space-x-3 py-1">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#7367F0] to-[#9F8ED6]"></div>
          <div>
            <h2 class="text-base font-bold text-[#4B465C] m-0">Event Details</h2>
            <p class="text-xs text-[#A8AAAE] m-0 mt-0.5">Schedule breakdown and associated entity details</p>
          </div>
        </div>
      </template>

      <div v-if="activeEvent" class="p-1 space-y-4">
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 space-y-4 shadow-sm">
          <div class="flex items-center justify-between pb-2 border-b border-[#F1F0F2]">
            <span class="px-2.5 py-1 rounded text-xs font-bold uppercase tracking-wider" :class="getEventTypeBadgeClass(activeEvent.type)">
              {{ activeEvent.type }}
            </span>
            <span v-if="activeEvent.status" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#F8F7FA] text-[#6F6B7D] border border-[#DBDADE]">
              {{ activeEvent.status }}
            </span>
          </div>

          <h3 class="text-sm font-bold text-[#4B465C] m-0 leading-snug">
            {{ activeEvent.title }}
          </h3>

          <div class="space-y-3 pt-2 border-t border-[#F1F0F2] text-xs">
            <div class="flex items-center justify-between">
              <span class="text-[#A8AAAE] font-medium">Scheduled Date</span>
              <span class="font-semibold text-[#4B465C]">{{ formatSelectedDate(activeEvent.date) }}</span>
            </div>

            <div v-if="activeEvent.amount" class="flex items-center justify-between">
              <span class="text-[#A8AAAE] font-medium">Value</span>
              <span class="font-bold text-[#7367F0]">{{ formatCurrency(activeEvent.amount) }}</span>
            </div>

            <div v-if="activeEvent.description" class="pt-2">
              <span class="text-[#A8AAAE] font-medium block mb-1">Description</span>
              <div class="p-3 bg-[#F8F7FA] border border-[#EBE9F1] rounded-md text-[#6F6B7D] leading-relaxed">
                {{ activeEvent.description }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex items-center justify-end py-2 px-1">
          <button
            type="button"
            class="btn-outline px-5 py-2.5 text-xs font-semibold cursor-pointer"
            @click="showEventDrawer = false"
          >
            Close
          </button>
        </div>
      </template>
    </a-drawer>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'
import { message } from 'ant-design-vue'
import { useEstimatesStore } from '../../store/estimatesStore'
import { useProposalsStore } from '../../store/proposalsStore'

export default defineComponent({
  name: 'CalendarPage',
  setup() {
    const estimatesStore = useEstimatesStore()
    const proposalsStore = useProposalsStore()

    const currentYear = ref(new Date().getFullYear())
    const currentMonth = ref(new Date().getMonth())
    const selectedDateStr = ref(new Date().toISOString().split('T')[0])
    const showEventDrawer = ref(false)
    const activeEvent = ref(null)

    const tasks = ref([])
    const invoices = ref([])
    const subscriptions = ref([])

    const filters = reactive({
      task: true,
      invoice: true,
      subscription: true,
      estimate: true,
      proposal: true,
    })

    const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
    const monthNames = [
      'January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'
    ]

    const monthName = computed(() => monthNames[currentMonth.value])

    const paddingDays = computed(() => {
      const firstDay = new Date(currentYear.value, currentMonth.value, 1)
      return firstDay.getDay()
    })

    const monthDays = computed(() => {
      const days = []
      const numDays = new Date(currentYear.value, currentMonth.value + 1, 0).getDate()
      for (let i = 1; i <= numDays; i++) {
        const d = new Date(currentYear.value, currentMonth.value, i)
        const offset = d.getTimezoneOffset()
        const localDate = new Date(d.getTime() - (offset * 60 * 1000))
        const dateStr = localDate.toISOString().split('T')[0]
        days.push({ dayNum: i, dateStr })
      }
      return days
    })

    const allEvents = computed(() => {
      const list = []

      // Tasks
      if (filters.task) {
        tasks.value.forEach(t => {
          if (t.due_date) {
            list.push({
              id: 'task-' + t.id,
              title: t.name,
              date: t.due_date.split('T')[0],
              type: 'task',
              description: t.description || '',
              status: t.status,
            })
          }
        })
      }

      // Invoices
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
            })
          }
        })
      }

      // Subscriptions
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
            })
          }
        })
      }

      // Estimates
      if (filters.estimate) {
        estimatesStore.estimates.forEach(est => {
          if (est.expiry) {
            const expiryDateStr = convertToIsoDate(est.expiry)
            list.push({
              id: 'estimate-' + est.id,
              title: `${est.number} (Expiry) - ${est.client}`,
              date: expiryDateStr,
              type: 'estimate',
              amount: est.amount,
              status: est.status,
              description: est.admin_note,
            })
          }
        })
      }

      // Proposals
      if (filters.proposal) {
        proposalsStore.proposals.forEach(prop => {
          if (prop.open_till) {
            const openTillIso = convertToIsoDate(prop.open_till)
            list.push({
              id: 'proposal-' + prop.id,
              title: `${prop.number} (Open Till) - ${prop.subject}`,
              date: openTillIso,
              type: 'proposal',
              amount: prop.amount,
              status: prop.status,
            })
          }
        })
      }

      return list
    })

    const convertToIsoDate = (dateStr) => {
      if (!dateStr) return ''
      if (dateStr.includes('-')) return dateStr.split('T')[0]
      const d = new Date(dateStr)
      if (isNaN(d.getTime())) return dateStr
      return d.toISOString().split('T')[0]
    }

    const getEventsForDay = (dateStr) => {
      return allEvents.value.filter(evt => evt.date === dateStr)
    }

    const selectedDateEvents = computed(() => {
      if (!selectedDateStr.value) return []
      return getEventsForDay(selectedDateStr.value)
    })

    const selectDay = (dateStr) => {
      selectedDateStr.value = dateStr
    }

    const viewEventDetails = (evt) => {
      activeEvent.value = evt
      showEventDrawer.value = true
    }

    const prevMonth = () => {
      if (currentMonth.value === 0) {
        currentMonth.value = 11
        currentYear.value -= 1
      } else {
        currentMonth.value -= 1
      }
    }

    const nextMonth = () => {
      if (currentMonth.value === 11) {
        currentMonth.value = 0
        currentYear.value += 1
      } else {
        currentMonth.value += 1
      }
    }

    const goToToday = () => {
      const today = new Date()
      currentYear.value = today.getFullYear()
      currentMonth.value = today.getMonth()
      selectedDateStr.value = today.toISOString().split('T')[0]
    }

    const isToday = (dateStr) => {
      const todayStr = new Date().toISOString().split('T')[0]
      return todayStr === dateStr
    }

    const getEventBadgeClass = (type) => {
      return {
        task: 'bg-[#7367F0] text-white',
        invoice: 'bg-[#28C76F] text-white',
        subscription: 'bg-[#8B5CF6] text-white',
        estimate: 'bg-[#FF9F43] text-white',
        proposal: 'bg-[#EA5455] text-white',
      }[type] || 'bg-[#7367F0] text-white'
    }

    const getEventTypeBadgeClass = (type) => {
      return {
        task: 'bg-[#7367F0]/10 text-[#7367F0] border border-[#7367F0]/20',
        invoice: 'bg-[#28C76F]/10 text-[#28C76F] border border-[#28C76F]/20',
        subscription: 'bg-[#8B5CF6]/10 text-[#8B5CF6] border border-[#8B5CF6]/20',
        estimate: 'bg-[#FF9F43]/10 text-[#FF9F43] border border-[#FF9F43]/20',
        proposal: 'bg-[#EA5455]/10 text-[#EA5455] border border-[#EA5455]/20',
      }[type] || 'bg-[#7367F0]/10 text-[#7367F0]'
    }

    const loadData = async () => {
      try {
        const resInv = await axios.get('/api/invoices', { params: { per_page: 200 } })
        invoices.value = resInv.data.invoices?.data || []

        const resTasks = await axios.get('/api/tasks', { params: { all: true } })
        tasks.value = resTasks.data.tasks || []

        const resSubs = await axios.get('/api/subscriptions', { params: { per_page: 200 } })
        subscriptions.value = resSubs.data.subscriptions?.data || []
      } catch (e) {
        console.error('Failed to load calendar data', e)
      }
    }

    const formatSelectedDate = (dateStr) => {
      if (!dateStr) return ''
      return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    }

    const formatCurrency = (val) => {
      if (val === undefined || val === null) return '$0.00'
      return '$' + parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2 })
    }

    onMounted(() => {
      loadData()
    })

    return {
      currentYear,
      currentMonth,
      selectedDateStr,
      showEventDrawer,
      activeEvent,
      filters,
      daysOfWeek,
      monthName,
      paddingDays,
      monthDays,
      getEventsForDay,
      selectedDateEvents,
      selectDay,
      viewEventDetails,
      prevMonth,
      nextMonth,
      goToToday,
      isToday,
      getEventBadgeClass,
      getEventTypeBadgeClass,
      formatSelectedDate,
      formatCurrency,
    }
  },
})
</script>

<style scoped>
.btn-outline {
  background-color: #FFFFFF;
  color: #6F6B7D;
  border: 1px solid #DBDADE;
  border-radius: 6px !important;
  transition: all 0.15s ease-in-out;
}
.btn-outline:hover:not(:disabled) {
  background-color: #F8F7FA;
  border-color: #C4C2C7;
  color: #4B465C;
}
.btn-outline:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

:deep(.vuexy-calendar-drawer .ant-drawer-header) {
  padding: 16px 24px;
  border-bottom: 1px solid #F1F0F2;
  background-color: #FFFFFF;
}
:deep(.vuexy-calendar-drawer .ant-drawer-body) {
  padding: 24px;
  background-color: #F8F7FA;
}
:deep(.vuexy-calendar-drawer .ant-drawer-footer) {
  padding: 12px 24px;
  border-top: 1px solid #F1F0F2;
  background-color: #FFFFFF;
}
</style>
