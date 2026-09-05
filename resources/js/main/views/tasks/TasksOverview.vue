<template>
  <div class="p-4 md:p-6 space-y-6 max-w-[1600px] mx-auto min-h-screen bg-[#F8F7FA]">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <div class="flex items-center space-x-2">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#7367F0] to-[#9F8ED6]"></div>
          <h1 class="text-xl md:text-2xl font-bold text-[#4B465C] tracking-tight m-0">Tasks Overview</h1>
        </div>
        <p class="text-xs text-[#A8AAAE] mt-1 pl-4.5 mb-0">
          <router-link :to="{ name: 'admin.tasks' }" class="text-[#7367F0] font-semibold hover:underline inline-flex items-center gap-1">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><polyline points="15 18 9 12 15 6"/></svg>
            Back to tasks list
          </router-link>
        </p>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white border border-[#EBE9F1] rounded-lg p-4 shadow-sm flex flex-wrap items-center gap-3">
      <!-- Staff -->
      <div class="relative min-w-[150px]">
        <select
          v-model="filters.staff"
          @change="load"
          class="form-ctrl text-xs h-[36px] pl-3 pr-8 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer w-full"
        >
          <option value="">All Staff</option>
          <option v-for="s in staff" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
        <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-[#A8AAAE]">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
      </div>

      <!-- Month -->
      <div class="relative min-w-[140px]">
        <select
          v-model="filters.month"
          @change="load"
          class="form-ctrl text-xs h-[36px] pl-3 pr-8 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer w-full"
        >
          <option value="">All Months</option>
          <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
        </select>
        <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-[#A8AAAE]">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
      </div>

      <!-- Year -->
      <div class="relative min-w-[120px]">
        <select
          v-model="filters.year"
          @change="load"
          class="form-ctrl text-xs h-[36px] pl-3 pr-8 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer w-full"
        >
          <option value="">All Years</option>
          <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
        </select>
        <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-[#A8AAAE]">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
      </div>

      <!-- Progress / Status -->
      <div class="relative min-w-[160px]">
        <select
          v-model="filters.progress"
          @change="load"
          class="form-ctrl text-xs h-[36px] pl-3 pr-8 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer w-full"
        >
          <option value="">All Statuses</option>
          <option value="Not Started">Not Started</option>
          <option value="In Progress">In Progress</option>
          <option value="Testing">Testing</option>
          <option value="Awaiting Feedback">Awaiting Feedback</option>
          <option value="Complete">Complete</option>
        </select>
        <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-[#A8AAAE]">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
      </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white border border-[#EBE9F1] rounded-lg shadow-sm overflow-hidden">
      <div class="overflow-x-auto min-h-[300px]">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#F8F7FA] border-b border-[#EBE9F1] text-[11px] font-bold uppercase tracking-wider text-[#6F6B7D]">
              <th class="py-3 px-3.5 min-w-[220px]">Name</th>
              <th class="py-3 px-3.5">Start Date</th>
              <th class="py-3 px-3.5">Due Date</th>
              <th class="py-3 px-3.5">Status</th>
              <th class="py-3 px-3.5 text-center">Attachments</th>
              <th class="py-3 px-3.5 text-center">Comments</th>
              <th class="py-3 px-3.5 text-center">Checklist</th>
              <th class="py-3 px-3.5 text-center">Logged</th>
              <th class="py-3 px-3.5 text-center">On Time?</th>
              <th class="py-3 px-3.5">Assigned To</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F1F0F2] text-xs text-[#6F6B7D]">
            <tr v-if="loading">
              <td colspan="10" class="text-center py-16 text-[#A8AAAE]">
                <div class="flex flex-col items-center justify-center space-y-2">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24" class="animate-spin text-[#7367F0]"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                  <span class="text-xs font-semibold">Loading overview...</span>
                </div>
              </td>
            </tr>

            <tr v-for="t in tasks" :key="t.id" class="hover:bg-[#F8F7FA]/70 transition-colors">
              <td class="py-3.5 px-3.5">
                <div class="flex flex-col">
                  <span class="font-bold text-[#4B465C]">{{ t.name }}</span>
                  <span v-if="t.related_to_type" class="text-[11px] text-[#A8AAAE] mt-0.5">
                    Related To: {{ t.related_to_type }} #{{ t.related_to_id }}
                  </span>
                </div>
              </td>
              <td class="py-3.5 px-3.5 whitespace-nowrap text-[#6F6B7D]">{{ fmtDate(t.start_date) }}</td>
              <td class="py-3.5 px-3.5 whitespace-nowrap">
                <span :class="isOverdue(t) ? 'text-rose-600 bg-rose-50 px-2 py-0.5 rounded font-semibold text-[11px]' : 'text-[#6F6B7D]'">
                  {{ fmtDate(t.due_date) }}
                </span>
              </td>
              <td class="py-3.5 px-3.5 whitespace-nowrap">
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 shadow-2xs" :class="statusBadgeClass(t.status)">
                  <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(t.status)"></span>
                  {{ t.status }}
                </span>
              </td>
              <td class="py-3.5 px-3.5 text-center text-[#A8AAAE]">0</td>
              <td class="py-3.5 px-3.5 text-center text-[#A8AAAE]">0</td>
              <td class="py-3.5 px-3.5 text-center font-bold text-[#6F6B7D]">0/0</td>
              <td class="py-3.5 px-3.5 text-center text-[#A8AAAE]">00:00</td>
              <td class="py-3.5 px-3.5 text-center text-[#A8AAAE]">—</td>
              <td class="py-3.5 px-3.5">
                <div v-if="t.assignee" class="flex items-center space-x-2">
                  <div class="w-6 h-6 rounded-full bg-[#7367F0]/10 text-[#7367F0] flex items-center justify-center text-[10px] font-bold">
                    {{ t.assignee.name.charAt(0).toUpperCase() }}
                  </div>
                  <span class="font-semibold text-[#4B465C]">{{ t.assignee.name }}</span>
                </div>
                <span v-else class="text-[#A8AAAE]">—</span>
              </td>
            </tr>

            <tr v-if="!loading && !tasks.length">
              <td colspan="10" class="text-center py-12 text-[#A8AAAE]">
                <p class="text-xs font-semibold m-0">No tasks found</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'

const BASE = '/api'
const tasks = ref([])
const staff = ref([])
const loading = ref(false)

const filters = reactive({ staff: '', month: '', year: '', progress: '' })

const months = [
  { label: 'January', value: '01' }, { label: 'February', value: '02' },
  { label: 'March', value: '03' }, { label: 'April', value: '04' },
  { label: 'May', value: '05' }, { label: 'June', value: '06' },
  { label: 'July', value: '07' }, { label: 'August', value: '08' },
  { label: 'September', value: '09' }, { label: 'October', value: '10' },
  { label: 'November', value: '11' }, { label: 'December', value: '12' },
]

const years = computed(() => {
  const y = new Date().getFullYear()
  return [y - 2, y - 1, y, y + 1, y + 2]
})

function isOverdue(t) {
  if (t.status === 'Complete') return false
  return t.due_date && new Date(t.due_date) < new Date()
}

function fmtDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

function statusBadgeClass(s) {
  return {
    'Not Started': 'bg-[#A8AAAE]/10 text-[#6F6B7D] border border-[#A8AAAE]/20',
    'In Progress': 'bg-[#7367F0]/10 text-[#7367F0] border border-[#7367F0]/20',
    'Testing': 'bg-[#FF9F43]/10 text-[#FF9F43] border border-[#FF9F43]/20',
    'Awaiting Feedback': 'bg-[#F97316]/10 text-[#F97316] border border-[#F97316]/20',
    'Complete': 'bg-[#28C76F]/10 text-[#28C76F] border border-[#28C76F]/20',
  }[s] || 'bg-[#F8F7FA] text-[#6F6B7D] border border-[#DBDADE]'
}

function statusDotClass(s) {
  return {
    'Not Started': 'bg-[#A8AAAE]',
    'In Progress': 'bg-[#7367F0]',
    'Testing': 'bg-[#FF9F43]',
    'Awaiting Feedback': 'bg-[#F97316]',
    'Complete': 'bg-[#28C76F]',
  }[s] || 'bg-[#6F6B7D]'
}

async function load() {
  loading.value = true
  try {
    const params = {}
    if (filters.staff) params.assigned_to = filters.staff
    if (filters.month) params.month = filters.month
    if (filters.year) params.year = filters.year
    if (filters.progress) params.status = filters.progress
    const r = await axios.get(`${BASE}/tasks/overview`, { params })
    tasks.value = r.data.tasks || []
  } catch {
    tasks.value = []
  } finally {
    loading.value = false
  }
}

async function loadStaff() {
  try {
    const r = await axios.get(`${BASE}/staff?per_page=500`)
    staff.value = r.data.staff?.data || []
  } catch {
    staff.value = []
  }
}

onMounted(() => {
  load()
  loadStaff()
})
</script>

<style scoped>
.form-ctrl {
  border: 1px solid #DBDADE;
  border-radius: 6px !important;
  color: #4B465C;
  font-family: inherit;
  outline: none;
}
.form-ctrl:focus {
  border-color: #7367F0 !important;
  box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.12) !important;
}
</style>
