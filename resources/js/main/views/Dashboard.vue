<template>
  <div class="dashboard">
    <div v-if="loading" class="loading-wrap"><div class="loader"></div> Loading...</div>
    <template v-else>
      <!-- ========================================================================= -->
      <!-- VUEXY MODERN ENTERPRISE DASHBOARD (Real CRM Data & Backend APIs) -->
      <!-- ========================================================================= -->
      <template v-if="themeStore.template === 'vuexy'">
        <div class="vuexy-dashboard-container">
          <!-- ROW 1: TOP 4 CRM METRIC CARDS -->
          <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
            <div 
              v-for="stat in topStats" 
              :key="stat.title" 
              class="vuexy-card vuexy-stat-card p-5 hover:shadow-md transition-shadow"
            >
              <div class="flex items-center justify-between">
                <div>
                  <span class="text-xs font-semibold text-[#A8AAAE] uppercase tracking-wider block">{{ stat.title }}</span>
                  <div class="text-2xl font-bold text-[#4B465C] dark:text-[#E6E5E8] mt-1">{{ stat.display }}</div>
                </div>
                <div 
                  class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                  :class="{
                    'bg-rose-50 text-rose-500 dark:bg-rose-950/40': stat.barClass === 'bar-red',
                    'bg-emerald-50 text-emerald-500 dark:bg-emerald-950/40': stat.barClass === 'bar-green',
                    'bg-indigo-50 text-indigo-500 dark:bg-indigo-950/40': stat.barClass === 'bar-blue',
                    'bg-amber-50 text-amber-500 dark:bg-amber-950/40': stat.barClass === 'bar-dark'
                  }"
                  v-html="stat.icon"
                ></div>
              </div>
              <div class="mt-4">
                <div class="flex items-center justify-between text-xs mb-1.5">
                  <span class="text-[#A8AAAE] font-medium">Completion Rate</span>
                  <span class="font-bold" :class="{
                    'text-rose-500': stat.barClass === 'bar-red',
                    'text-emerald-500': stat.barClass === 'bar-green',
                    'text-indigo-500': stat.barClass === 'bar-blue',
                    'text-amber-500': stat.barClass === 'bar-dark'
                  }">{{ stat.pct }}%</span>
                </div>
                <div class="w-full h-2 bg-[#F1F0F2] dark:bg-[#3B4056] rounded-full overflow-hidden">
                  <div 
                    class="h-full rounded-full transition-all duration-500"
                    :style="{ 
                      width: stat.pct + '%',
                      backgroundColor: stat.barClass === 'bar-red' ? '#EA5455' : stat.barClass === 'bar-green' ? '#28C76F' : stat.barClass === 'bar-blue' ? (themeStore.primaryColor || '#7367F0') : '#FF9F43'
                    }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <!-- ROW 2: DOCUMENT & FINANCIAL OVERVIEW DONUTS (3 CARDS) -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- 1. Invoice Overview Card -->
            <div class="vuexy-card vuexy-overview-card p-5">
              <div class="vuexy-card__header flex items-center justify-between pb-3 border-b border-[#F1F0F2] dark:border-[#3B4056] mb-3">
                <div class="flex items-center gap-2">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" class="text-[#7367F0]"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                  <h3 class="text-sm font-bold text-[#4B465C] dark:text-[#E6E5E8] m-0">Invoice Overview</h3>
                </div>
                <router-link :to="{ name: 'admin.invoices' }" class="text-xs font-semibold text-[#7367F0] hover:underline">View All</router-link>
              </div>
              <div class="py-2">
                <apexchart type="donut" height="200" :options="invoiceDonutOptions" :series="invoiceDonutSeries"></apexchart>
              </div>
              <!-- Live Status Pill Legend -->
              <div class="grid grid-cols-2 gap-2 mt-2 pt-3 border-t border-[#F1F0F2] dark:border-[#3B4056]">
                <div 
                  v-for="row in invoiceOverview" 
                  :key="row.status"
                  class="flex items-center justify-between p-2 rounded-lg bg-[#F8F7FA] dark:bg-[#34384E] text-xs"
                >
                  <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" :style="{ background: invoiceDonutColors[row.status] || '#94a3b8' }"></span>
                    <span class="font-medium text-[#4B465C] dark:text-[#CFCCE4] capitalize">{{ row.label }}</span>
                  </div>
                  <span class="font-bold text-[#6F6B7D] dark:text-[#A8AAAE]">{{ row.count }} ({{ (Number(row.percentage) || 0).toFixed(0) }}%)</span>
                </div>
              </div>
              <!-- Financial Total Chips -->
              <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-[#F1F0F2] dark:border-[#3B4056] text-center">
                <div class="p-2 rounded-lg bg-rose-50/70 dark:bg-rose-950/30">
                  <div class="text-[10px] text-rose-600 font-bold uppercase">Outstanding</div>
                  <div class="text-xs font-extrabold text-rose-700 dark:text-rose-400 mt-0.5">{{ formatCurrency(metrics.outstanding_amount) }}</div>
                </div>
                <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800/40">
                  <div class="text-[10px] text-slate-500 font-bold uppercase">Past Due</div>
                  <div class="text-xs font-extrabold text-slate-700 dark:text-slate-300 mt-0.5">{{ formatCurrency(metrics.past_due_amount) }}</div>
                </div>
                <div class="p-2 rounded-lg bg-emerald-50/70 dark:bg-emerald-950/30">
                  <div class="text-[10px] text-emerald-600 font-bold uppercase">Paid</div>
                  <div class="text-xs font-extrabold text-emerald-700 dark:text-emerald-400 mt-0.5">{{ formatCurrency(metrics.paid_amount) }}</div>
                </div>
              </div>
            </div>

            <!-- 2. Estimate Overview Card -->
            <div class="vuexy-card vuexy-overview-card p-5">
              <div class="vuexy-card__header flex items-center justify-between pb-3 border-b border-[#F1F0F2] dark:border-[#3B4056] mb-3">
                <div class="flex items-center gap-2">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" class="text-[#00CFE8]"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  <h3 class="text-sm font-bold text-[#4B465C] dark:text-[#E6E5E8] m-0">Estimate Overview</h3>
                </div>
                <router-link :to="{ name: 'admin.estimates' }" class="text-xs font-semibold text-[#00CFE8] hover:underline">View All</router-link>
              </div>
              <div class="py-2">
                <apexchart type="donut" height="200" :options="estimateDonutOptions" :series="estimateDonutSeries"></apexchart>
              </div>
              <!-- Live Status Pill Legend -->
              <div class="grid grid-cols-2 gap-2 mt-2 pt-3 border-t border-[#F1F0F2] dark:border-[#3B4056]">
                <div 
                  v-for="row in estimateOverview" 
                  :key="row.label"
                  class="flex items-center justify-between p-2 rounded-lg bg-[#F8F7FA] dark:bg-[#34384E] text-xs"
                >
                  <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" :style="{ background: estimateDonutColors[row.label] || '#94a3b8' }"></span>
                    <span class="font-medium text-[#4B465C] dark:text-[#CFCCE4]">{{ row.label }}</span>
                  </div>
                  <span class="font-bold text-[#6F6B7D] dark:text-[#A8AAAE]">{{ row.count }} ({{ (Number(row.percentage) || 0).toFixed(0) }}%)</span>
                </div>
              </div>
            </div>

            <!-- 3. Proposal Overview Card -->
            <div class="vuexy-card vuexy-overview-card p-5">
              <div class="vuexy-card__header flex items-center justify-between pb-3 border-b border-[#F1F0F2] dark:border-[#3B4056] mb-3">
                <div class="flex items-center gap-2">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" class="text-[#FF9F43]"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                  <h3 class="text-sm font-bold text-[#4B465C] dark:text-[#E6E5E8] m-0">Proposal Overview</h3>
                </div>
                <router-link :to="{ name: 'admin.proposals' }" class="text-xs font-semibold text-[#FF9F43] hover:underline">View All</router-link>
              </div>
              <div class="py-2">
                <apexchart type="donut" height="200" :options="proposalDonutOptions" :series="proposalDonutSeries"></apexchart>
              </div>
              <!-- Live Status Pill Legend -->
              <div class="grid grid-cols-2 gap-2 mt-2 pt-3 border-t border-[#F1F0F2] dark:border-[#3B4056]">
                <div 
                  v-for="row in proposalOverview" 
                  :key="row.label"
                  class="flex items-center justify-between p-2 rounded-lg bg-[#F8F7FA] dark:bg-[#34384E] text-xs"
                >
                  <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" :style="{ background: proposalDonutColors[row.label] || '#94a3b8' }"></span>
                    <span class="font-medium text-[#4B465C] dark:text-[#CFCCE4]">{{ row.label }}</span>
                  </div>
                  <span class="font-bold text-[#6F6B7D] dark:text-[#A8AAAE]">{{ row.count }} ({{ (Number(row.percentage) || 0).toFixed(0) }}%)</span>
                </div>
              </div>
            </div>
          </div>

          <!-- ROW 3: MAIN TWO-COLUMN DASHBOARD GRID -->
          <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            
            <!-- LEFT COLUMN (8 COLS ON XL) -->
            <div class="xl:col-span-8 space-y-6">
              
              <!-- 1. TABS WIDGET (My Tasks | My Projects | My Reminders | Tickets | Announcements | Latest Activity) -->
              <div class="vuexy-card vuexy-table-card overflow-hidden">
                <!-- Tab Buttons Header -->
                <div class="p-4 border-b border-[#F1F0F2] dark:border-[#3B4056] flex flex-col md:flex-row md:items-center justify-between gap-4">
                  <div class="flex items-center gap-1.5 flex-wrap">
                    <button 
                      v-for="t in ['Tasks', 'Projects', 'Reminders', 'Tickets', 'Announcements', 'Latest Activity']" 
                      :key="t" 
                      class="px-3.5 py-2 text-xs font-semibold rounded-lg transition-all border-0 cursor-pointer"
                      :class="activeTab === t ? 'bg-[#7367F0] text-white shadow-sm' : 'bg-transparent text-[#6F6B7D] dark:text-[#A8AAAE] hover:bg-[#F8F7FA] dark:hover:bg-[#34384E]'"
                      @click="activeTab = t"
                    >
                      {{ t === 'Latest Activity' ? 'Latest Activity' : 'My ' + t }}
                    </button>
                  </div>
                  <!-- Search Filter -->
                  <div class="relative w-full md:w-48">
                    <input 
                      type="text" 
                      v-model="tabSearch" 
                      placeholder="Search items..." 
                      class="w-full h-8.5 pl-8 pr-3 text-xs bg-white dark:bg-[#2F3349] border border-[#DBDADE] dark:border-[#4B465C] rounded-lg text-[#4B465C] dark:text-[#E6E5E8] focus:outline-none focus:border-[#7367F0]"
                    />
                    <svg class="absolute left-2.5 top-2 text-[#A8AAAE]" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                  </div>
                </div>

                <!-- Tab Content: My Tasks -->
                <div v-if="activeTab === 'Tasks'" class="vuexy-table-responsive overflow-x-auto">
                  <table class="vuexy-table w-full border-collapse">
                    <thead>
                      <tr>
                        <th class="w-12 text-center">#</th>
                        <th>Task Name &amp; Project</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>Priority</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="task in filteredTasks" :key="task.name" class="hover:bg-[#F8F7FA] dark:hover:bg-[#34384E]">
                        <td class="text-center font-mono font-bold text-xs text-[#7367F0]">
                          <router-link :to="{ name: 'admin.tasks' }">#{{ task.id || 10 }}</router-link>
                        </td>
                        <td>
                          <div class="flex flex-col">
                            <router-link :to="{ name: 'admin.tasks' }" class="font-semibold text-xs text-[#4B465C] dark:text-[#E6E5E8] hover:text-[#7367F0]">
                              {{ task.name }}
                            </router-link>
                            <span class="text-[11px] text-[#7367F0] mt-0.5" v-if="task.project">
                              {{ task.project }}
                            </span>
                            <div class="flex items-center gap-2 mt-1 text-[11px] text-[#A8AAAE]">
                              <button type="button" class="text-emerald-600 font-semibold hover:underline bg-transparent border-0 p-0 cursor-pointer">Start Timer</button>
                              <span>|</span>
                              <button type="button" class="text-[#6F6B7D] hover:underline bg-transparent border-0 p-0 cursor-pointer">Edit</button>
                              <span>|</span>
                              <button type="button" class="text-rose-500 hover:underline bg-transparent border-0 p-0 cursor-pointer">Delete</button>
                            </div>
                          </div>
                        </td>
                        <td>
                          <span :class="['vuexy-badge-pill', task.status === 'In Progress' ? 'vuexy-badge-pill--info' : task.status === 'Testing' ? 'vuexy-badge-pill--warning' : task.status === 'Awaiting Feedback' ? 'vuexy-badge-pill--secondary' : 'vuexy-badge-pill--professional']">
                            {{ task.status }}
                          </span>
                        </td>
                        <td class="text-xs text-[#6F6B7D] dark:text-[#B6B1D0]">{{ task.start || '2026-09-04' }}</td>
                        <td>
                          <span :class="['vuexy-badge-pill', task.priority === 'Urgent' ? 'vuexy-badge-pill--danger' : task.priority === 'High' ? 'vuexy-badge-pill--warning' : task.priority === 'Medium' ? 'vuexy-badge-pill--primary' : 'vuexy-badge-pill--secondary']">
                            {{ task.priority || 'Low' }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Tab Content: My Projects -->
                <div v-if="activeTab === 'Projects'" class="vuexy-table-responsive overflow-x-auto">
                  <table class="vuexy-table w-full border-collapse">
                    <thead>
                      <tr>
                        <th>Project Name</th>
                        <th>Client</th>
                        <th>Billing Type</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="proj in projectsMock" :key="proj.name" class="hover:bg-[#F8F7FA] dark:hover:bg-[#34384E]">
                        <td>
                          <router-link :to="{ name: 'admin.projects' }" class="font-semibold text-xs text-[#7367F0] hover:underline">
                            {{ proj.name }}
                          </router-link>
                        </td>
                        <td class="text-xs text-[#6F6B7D] dark:text-[#B6B1D0]">{{ proj.client }}</td>
                        <td class="text-xs text-[#6F6B7D] dark:text-[#B6B1D0]">{{ proj.billing }}</td>
                        <td>
                          <span class="vuexy-badge-pill vuexy-badge-pill--professional">{{ proj.status }}</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Tab Content: Reminders -->
                <div v-if="activeTab === 'Reminders'">
                  <div v-if="metrics.reminders && metrics.reminders.length" class="vuexy-table-responsive overflow-x-auto">
                    <table class="vuexy-table w-full border-collapse">
                      <thead>
                        <tr>
                          <th>Description</th>
                          <th>Date</th>
                          <th>Client</th>
                          <th>Remind To</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="r in metrics.reminders" :key="r.id" class="hover:bg-[#F8F7FA] dark:hover:bg-[#34384E]">
                          <td class="text-xs font-semibold text-[#4B465C] dark:text-[#E6E5E8]">{{ r.description }}</td>
                          <td class="text-xs text-[#6F6B7D] dark:text-[#B6B1D0]">{{ r.date }}</td>
                          <td class="text-xs text-[#7367F0]">{{ r.client || 'N/A' }}</td>
                          <td class="text-xs text-[#6F6B7D] dark:text-[#B6B1D0]">{{ r.remind_to }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div v-else class="p-8 text-center text-xs text-[#A8AAAE]">
                    <svg class="mx-auto mb-2 opacity-50" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    No pending reminders found
                  </div>
                </div>

                <!-- Tab Content: Tickets -->
                <div v-if="activeTab === 'Tickets'">
                  <div v-if="metrics.tickets && metrics.tickets.length" class="vuexy-table-responsive overflow-x-auto">
                    <table class="vuexy-table w-full border-collapse">
                      <thead>
                        <tr>
                          <th>Subject</th>
                          <th>Client</th>
                          <th>Department</th>
                          <th>Status</th>
                          <th>Last Reply</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="t in metrics.tickets" :key="t.id" class="hover:bg-[#F8F7FA] dark:hover:bg-[#34384E]">
                          <td>
                            <router-link :to="{ name: 'admin.support' }" class="font-semibold text-xs text-[#4B465C] dark:text-[#E6E5E8] hover:text-[#7367F0]">
                              #{{ t.number }} - {{ t.subject }}
                            </router-link>
                          </td>
                          <td class="text-xs text-[#6F6B7D] dark:text-[#B6B1D0]">{{ t.client }}</td>
                          <td class="text-xs text-[#6F6B7D] dark:text-[#B6B1D0]">{{ t.department }}</td>
                          <td>
                            <span :class="['vuexy-badge-pill', t.status === 'Open' ? 'vuexy-badge-pill--danger' : t.status === 'Closed' ? 'vuexy-badge-pill--success' : 'vuexy-badge-pill--primary']">
                              {{ t.status }}
                            </span>
                          </td>
                          <td class="text-xs text-[#A8AAAE]">{{ t.last_reply }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div v-else class="p-8 text-center text-xs text-[#A8AAAE]">
                    <svg class="mx-auto mb-2 opacity-50" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    No pending support tickets assigned to you
                  </div>
                </div>

                <!-- Tab Content: Announcements -->
                <div v-if="activeTab === 'Announcements'">
                  <div v-if="metrics.announcements && metrics.announcements.length" class="p-4 space-y-3">
                    <div v-for="a in metrics.announcements" :key="a.id" class="p-3 rounded-lg bg-[#F8F7FA] dark:bg-[#34384E] border border-[#F1F0F2] dark:border-[#3B4056]">
                      <div class="flex items-center justify-between mb-1">
                        <h4 class="text-xs font-bold text-[#4B465C] dark:text-[#E6E5E8] m-0">{{ a.subject }}</h4>
                        <span class="text-[11px] text-[#A8AAAE]">{{ a.date }}</span>
                      </div>
                      <p class="text-xs text-[#6F6B7D] dark:text-[#B6B1D0] m-0">{{ a.message }}</p>
                    </div>
                  </div>
                  <div v-else class="p-8 text-center text-xs text-[#A8AAAE]">
                    <svg class="mx-auto mb-2 opacity-50" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
                    No recent global announcements
                  </div>
                </div>

                <!-- Tab Content: Latest Activity -->
                <div v-if="activeTab === 'Latest Activity'" class="p-5 space-y-4">
                  <div v-for="(act, aIdx) in latestActivity" :key="aIdx" class="flex items-start gap-3">
                    <span class="w-2.5 h-2.5 rounded-full mt-1.5 shrink-0" :class="act.colorClass === 'activity-dot--green' ? 'bg-emerald-500' : act.colorClass === 'activity-dot--blue' ? 'bg-blue-500' : 'bg-purple-500'"></span>
                    <div class="flex-1">
                      <div class="text-xs text-[#4B465C] dark:text-[#E6E5E8]">
                        <strong>{{ act.user }}</strong> - {{ act.action }}
                        <span v-if="act.project" class="text-[#7367F0] font-medium"> — {{ act.project }}</span>
                      </div>
                      <span class="text-[11px] text-[#A8AAAE] mt-0.5 block">{{ act.time }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- 2. PAYMENT RECORDS / SALES OVERVIEW CHART CARD -->
              <div class="vuexy-card p-5">
                <div class="flex items-center justify-between pb-3 border-b border-[#F1F0F2] dark:border-[#3B4056] mb-4">
                  <div class="flex items-center gap-2">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-500"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="12" y1="10" x2="12" y2="10"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                    <h3 class="text-sm font-bold text-[#4B465C] dark:text-[#E6E5E8] m-0">Payment Records &amp; Sales Summary</h3>
                  </div>
                  <router-link :to="{ name: 'admin.payments' }" class="text-xs font-semibold text-[#7367F0] hover:underline">Full Report</router-link>
                </div>
                <apexchart type="bar" height="260" :options="paymentChartOptions" :series="paymentChartSeries"></apexchart>
              </div>

              <!-- 3. INTERACTIVE CALENDAR WIDGET CARD -->
              <div class="vuexy-card p-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#F1F0F2] dark:border-[#3B4056] mb-4">
                  <div class="flex items-center gap-2">
                    <button class="px-2 py-1 bg-transparent hover:bg-[#F1F0F2] dark:hover:bg-[#34384E] rounded-md border-0 cursor-pointer text-xs" @click="calPrev">◀</button>
                    <h3 class="text-sm font-bold text-[#4B465C] dark:text-[#E6E5E8] m-0">{{ calMonthLabel }}</h3>
                    <button class="px-2 py-1 bg-transparent hover:bg-[#F1F0F2] dark:hover:bg-[#34384E] rounded-md border-0 cursor-pointer text-xs" @click="calNext">▶</button>
                  </div>
                  <div class="flex items-center gap-1">
                    <button 
                      v-for="m in ['Month', 'Week', 'Day']" 
                      :key="m" 
                      class="px-2.5 py-1 text-xs rounded-md border-0 cursor-pointer font-medium"
                      :class="calViewMode === m ? 'bg-[#7367F0] text-white' : 'bg-[#F8F7FA] dark:bg-[#34384E] text-[#6F6B7D] dark:text-[#A8AAAE]'"
                      @click="calSetView(m)"
                    >
                      {{ m }}
                    </button>
                  </div>
                </div>
                <div v-if="calViewMode !== 'Day'" class="grid grid-cols-7 text-center text-[11px] font-bold text-[#A8AAAE] mb-2 uppercase">
                  <span v-for="d in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="d">{{ d }}</span>
                </div>
                <div class="grid grid-cols-7 gap-1">
                  <div 
                    v-for="(cell, cIdx) in calCells" 
                    :key="cIdx" 
                    class="p-2 min-h-[50px] rounded-lg border border-[#F1F0F2] dark:border-[#3B4056] text-xs transition-colors"
                    :class="[
                      cell.isToday ? 'bg-purple-50 dark:bg-purple-950/40 border-[#7367F0] font-bold text-[#7367F0]' : cell.currentMonth ? 'bg-white dark:bg-[#2F3349] text-[#4B465C] dark:text-[#E6E5E8]' : 'bg-[#F8F7FA] dark:bg-[#25293C] text-[#A8AAAE] opacity-60'
                    ]"
                  >
                    <span>{{ cell.day }}</span>
                    <div v-if="cell.currentMonth && calMonthEvents[cell.day]" class="mt-1">
                      <span class="text-[9px] px-1.5 py-0.5 rounded bg-[rgba(115,103,240,0.15)] text-[#7367F0] font-semibold block truncate">
                        {{ calMonthEvents[cell.day].text }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <!-- RIGHT COLUMN (4 COLS ON XL) -->
            <div class="xl:col-span-4 space-y-6">
              
              <!-- 1. CONTRACTS EXPIRING SOON CARD -->
              <div class="vuexy-card vuexy-table-card overflow-hidden">
                <div class="p-4 border-b border-[#F1F0F2] dark:border-[#3B4056] flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" class="text-[#FF9F43]"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <h3 class="text-sm font-bold text-[#4B465C] dark:text-[#E6E5E8] m-0">Contracts Expiring Soon</h3>
                  </div>
                  <router-link :to="{ name: 'admin.contracts' }" class="text-xs font-semibold text-[#7367F0] hover:underline">View All</router-link>
                </div>
                <div class="vuexy-table-responsive overflow-x-auto max-h-[300px]">
                  <table class="vuexy-table w-full border-collapse">
                    <thead>
                      <tr>
                        <th>Subject #</th>
                        <th>Customer</th>
                        <th class="text-right">End Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="c in filteredContracts" :key="c.subject" class="hover:bg-[#F8F7FA] dark:hover:bg-[#34384E]">
                        <td>
                          <router-link :to="{ name: 'admin.contracts' }" class="font-semibold text-xs text-[#4B465C] dark:text-[#E6E5E8] hover:text-[#7367F0] line-clamp-1">
                            {{ c.subject }}
                          </router-link>
                        </td>
                        <td class="text-xs text-[#6F6B7D] dark:text-[#B6B1D0]">{{ c.customer }}</td>
                        <td class="text-xs text-right font-mono text-rose-500 font-semibold">{{ c.end }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- 2. STAFF TICKETS REPORT CARD -->
              <div class="vuexy-card vuexy-table-card overflow-hidden">
                <div class="p-4 border-b border-[#F1F0F2] dark:border-[#3B4056] flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" class="text-indigo-500"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <h3 class="text-sm font-bold text-[#4B465C] dark:text-[#E6E5E8] m-0">Staff Tickets Report</h3>
                  </div>
                  <span class="text-[11px] text-[#A8AAAE] font-medium">This Month</span>
                </div>
                <div class="vuexy-table-responsive overflow-x-auto max-h-[300px]">
                  <table class="vuexy-table w-full border-collapse">
                    <thead>
                      <tr>
                        <th>Staff</th>
                        <th>Assigned</th>
                        <th>Open</th>
                        <th>Closed</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="s in filteredStaff" :key="s.name" class="hover:bg-[#F8F7FA] dark:hover:bg-[#34384E]">
                        <td class="font-semibold text-xs text-[#4B465C] dark:text-[#E6E5E8]">{{ s.name }}</td>
                        <td class="text-xs text-[#6F6B7D]">{{ s.assigned }}</td>
                        <td class="text-xs font-bold text-rose-500">{{ s.open }}</td>
                        <td class="text-xs font-bold text-emerald-500">{{ s.closed }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- 3. MY TO-DO ITEMS CARD -->
              <div class="vuexy-card p-5">
                <div class="flex items-center justify-between pb-3 border-b border-[#F1F0F2] dark:border-[#3B4056] mb-3">
                  <div class="flex items-center gap-2">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" class="text-[#7367F0]"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    <h3 class="text-sm font-bold text-[#4B465C] dark:text-[#E6E5E8] m-0">My To Do Items</h3>
                  </div>
                  <button 
                    class="text-xs font-bold text-[#7367F0] hover:underline bg-transparent border-0 cursor-pointer"
                    @click="todoAddMode = !todoAddMode"
                  >
                    + New To Do
                  </button>
                </div>

                <!-- Add inline to-do -->
                <div v-if="todoAddMode" class="flex items-center gap-2 mb-3">
                  <input 
                    type="text" 
                    v-model="todoNewText" 
                    placeholder="Enter new task..." 
                    class="flex-1 h-8 px-2.5 text-xs bg-white dark:bg-[#2F3349] border border-[#DBDADE] dark:border-[#4B465C] rounded-md text-[#4B465C] dark:text-[#E6E5E8] focus:outline-none"
                    @keyup.enter="addTodo"
                  />
                  <button class="px-3 py-1 bg-[#7367F0] text-white text-xs font-semibold rounded-md border-0 cursor-pointer" @click="addTodo">Save</button>
                </div>

                <!-- Active To Dos -->
                <div class="space-y-2">
                  <div 
                    v-for="t in pendingTodos" 
                    :key="t.id"
                    class="flex items-center justify-between p-2 rounded-lg bg-[#F8F7FA] dark:bg-[#34384E] text-xs"
                  >
                    <div class="flex items-center gap-2.5">
                      <input 
                        type="checkbox" 
                        class="vuexy-checkbox"
                        :checked="false"
                        @change="toggleTodo(t.id)"
                      />
                      <span class="text-[#4B465C] dark:text-[#E6E5E8] font-medium">{{ t.title }}</span>
                    </div>
                    <button class="text-rose-400 hover:text-rose-600 bg-transparent border-0 cursor-pointer text-xs" @click="deleteTodo(t.id)">×</button>
                  </div>
                </div>
              </div>

              <!-- 4. GOALS & ACHIEVEMENTS CARD -->
              <div class="vuexy-card p-5">
                <div class="flex items-center justify-between pb-3 border-b border-[#F1F0F2] dark:border-[#3B4056] mb-3">
                  <div class="flex items-center gap-2">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-500"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    <h3 class="text-sm font-bold text-[#4B465C] dark:text-[#E6E5E8] m-0">Goals &amp; Achievements</h3>
                  </div>
                  <router-link :to="{ name: 'admin.goals' }" class="text-xs font-semibold text-[#7367F0] hover:underline">View All</router-link>
                </div>
                <div class="space-y-3 max-h-[320px] overflow-y-auto">
                  <div v-for="g in goalList" :key="g.title" class="p-2.5 rounded-lg bg-[#F8F7FA] dark:bg-[#34384E]">
                    <div class="flex items-center justify-between text-xs font-semibold text-[#4B465C] dark:text-[#E6E5E8]">
                      <span>{{ g.title }}</span>
                      <span :style="{ color: g.progressColor }">{{ g.progressPct }}%</span>
                    </div>
                    <div class="text-[11px] text-[#A8AAAE] mt-0.5 truncate">{{ g.subtitle }}</div>
                    <div class="w-full h-1.5 bg-[#EBE9F1] dark:bg-[#3B4056] rounded-full mt-2 overflow-hidden">
                      <div class="h-full rounded-full" :style="{ width: g.progressPct + '%', backgroundColor: g.progressColor }"></div>
                    </div>
                  </div>
                </div>
              </div>

            </div>

          </div>
        </div>
      </template>

      <!-- ========================================================================= -->
      <!-- ORGANIC CLAYMORPHIC DASHBOARD (Preserved Original) -->
      <!-- ========================================================================= -->
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
        
        <!-- Overviews Panel — Liquid Glass -->
        <div class="overviews-wrapper">
          <div class="overviews-panel">
            <div class="overview-grid">
            <!-- Invoice Overview -->
            <div class="overview-col">
              <h3 class="overview-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="17" height="17"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Invoice overview
              </h3>
              <apexchart type="donut" height="220" :options="invoiceDonutOptions" :series="invoiceDonutSeries"></apexchart>
              <div class="overview-legend">
                <div 
                  v-for="row in invoiceOverview" 
                  :key="row.status" 
                  :class="['overview-legend-item', { 'ov-btn-red': row.status === 'unpaid', 'ov-btn-green': row.status === 'paid', 'ov-btn-orange': row.status === 'overdue' || row.status === 'partially_paid', 'ov-btn-grey': row.status === 'draft' || row.status === 'cancelled' }]"
                >
                  <span class="overview-legend-dot" :style="{ background: invoiceDonutColors[row.status] || '#94a3b8' }"></span>
                  <span class="overview-legend-label">{{ row.count }} {{ row.label }}</span>
                  <span class="overview-legend-pct">{{ (Number(row.percentage) || 0).toFixed(1) }}%</span>
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="17" height="17"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Estimate overview
              </h3>
              <apexchart type="donut" height="220" :options="estimateDonutOptions" :series="estimateDonutSeries"></apexchart>
              <div class="overview-legend">
                <div 
                  v-for="row in estimateOverview" 
                  :key="row.label" 
                  :class="['overview-legend-item', { 'ov-btn-grey': row.label === 'Draft' || row.label === 'Not Sent', 'ov-btn-blue': row.label === 'Sent', 'ov-btn-orange': row.label === 'Expired', 'ov-btn-red': row.label === 'Declined', 'ov-btn-green': row.label === 'Accepted' }]"
                >
                  <span class="overview-legend-dot" :style="{ background: estimateDonutColors[row.label] || '#94a3b8' }"></span>
                  <span class="overview-legend-label">{{ row.count }} {{ row.label }}</span>
                  <span class="overview-legend-pct">{{ (Number(row.percentage) || 0).toFixed(1) }}%</span>
                </div>
              </div>
            </div>

            <!-- Proposal Overview -->
            <div class="overview-col">
              <h3 class="overview-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="17" height="17"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                Proposal overview
              </h3>
              <apexchart type="donut" height="220" :options="proposalDonutOptions" :series="proposalDonutSeries"></apexchart>
              <div class="overview-legend">
                <div 
                  v-for="row in proposalOverview" 
                  :key="row.label" 
                  :class="['overview-legend-item', { 'ov-btn-grey': row.label === 'Draft', 'ov-btn-blue': row.label === 'Sent' || row.label === 'Open', 'ov-btn-orange': row.label === 'Revised', 'ov-btn-red': row.label === 'Declined', 'ov-btn-green': row.label === 'Accepted' }]"
                >
                  <span class="overview-legend-dot" :style="{ background: proposalDonutColors[row.label] || '#94a3b8' }"></span>
                  <span class="overview-legend-label">{{ row.count }} {{ row.label }}</span>
                  <span class="overview-legend-pct">{{ (Number(row.percentage) || 0).toFixed(1) }}%</span>
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
              v-for="t in ['Tasks', 'Projects', 'Reminders', 'Tickets', 'Announcements', 'Latest Activity']" 
              :key="t" 
              :class="['tab-btn', { 'tab-btn--active': activeTab === t }]"
              @click="activeTab = t"
            >
              {{ t === 'Latest Activity' ? 'Latest Activity' : 'My ' + t }}
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

            <div class="table-wrap">
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
            </div>

            <div class="table-wrap">
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
            </div>

            <div v-if="activeTab === 'Reminders'" class="empty-cell">
              No reminders found
            </div>
            
            <div v-if="activeTab === 'Tickets'" class="empty-cell">
              No pending support tickets assigned to you
            </div>

            <div v-if="activeTab === 'Announcements'" class="empty-cell">
              No recent announcements
            </div>

            <div v-if="activeTab === 'Latest Activity'" class="activity-feed">
              <div v-for="act in latestActivity" :key="act.time" class="activity-item">
                <span class="activity-dot" :class="act.colorClass"></span>
                <div class="activity-content">
                  <div class="activity-text">
                    <strong>{{ act.user }}</strong> - {{ act.action }}
                    <span v-if="act.project" class="activity-project"> — {{ act.project }}</span>
                  </div>
                  <div class="activity-time">{{ act.time }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Calendar Widget -->
        <div class="card calendar-panel">
          <div class="calendar-header">
            <button class="cal-nav-btn" @click="calPrev">◀</button>
            <h3 class="calendar-title">{{ calMonthLabel }}</h3>
            <button class="cal-nav-btn" @click="calNext">▶</button>
            <div class="cal-view-modes">
              <button :class="['btn-outline', { active: calViewMode === 'Month' }]" @click="calSetView('Month')">Month</button>
              <button :class="['btn-outline', { active: calViewMode === 'Week' }]" @click="calSetView('Week')">Week</button>
              <button :class="['btn-outline', { active: calViewMode === 'Day' }]" @click="calSetView('Day')">Day</button>
            </div>
          </div>
          <div v-if="calViewMode !== 'Day'" class="calendar-weekdays">
            <span v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day">{{ day }}</span>
          </div>
          <div :class="['calendar-grid', { 'calendar-grid--week': calViewMode === 'Week', 'calendar-grid--day': calViewMode === 'Day' }]">
            <div
              v-for="(cell, i) in calCells"
              :key="i"
              :class="['calendar-day', { 'calendar-day--other': !cell.currentMonth, 'calendar-day--today': cell.isToday }]"
            >
              <span class="day-num">{{ cell.day }}</span>
              <div class="day-events" v-if="cell.currentMonth && calMonthEvents[cell.day]">
                <div :class="['cal-event', 'cal-event--' + calMonthEvents[cell.day].color]">{{ calMonthEvents[cell.day].text }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- This Week Events -->
        <div class="card events-panel">
          <div class="panel-header">
            <h3 class="panel-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="17" height="17" style="vertical-align:middle;margin-right:4px"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              This Week events
            </h3>
            <span style="font-size:12px;color:#94a3b8">Upcoming events Next Week: {{ nextWeekEvents }}</span>
          </div>
          <div class="events-list">
            <div v-for="ev in thisWeekEvents" :key="ev.title" class="event-item">
              <div class="event-date-badge">
                <span class="event-date-day">{{ ev.day }}</span>
                <span class="event-date-month">{{ ev.month }}</span>
              </div>
              <div class="event-info">
                <div class="event-title">{{ ev.title }}</div>
                <div class="event-meta">{{ ev.date }} — {{ ev.type }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Records Chart -->
        <div class="card payment-records-panel">
          <div class="panel-header">
            <h3 class="panel-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="18" height="18" style="vertical-align: middle; margin-right: 4px;"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="12" y1="10" x2="12" y2="10"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
              Payment Records
            </h3>
            <span style="font-size:12.5px;color:#94a3b8">Billed vs Received</span>
          </div>
          <apexchart type="bar" height="260" :options="paymentChartOptions" :series="paymentChartSeries"></apexchart>
        </div>

      </div>
      
      <!-- Right Column (Sidebar Content) -->
      <div class="grid-right">
        
        <!-- Leads Overview (Apex Donut) -->
        <div class="card donut-card">
          <h3 class="panel-title">Leads Overview</h3>
          <apexchart type="donut" height="260" :options="leadsChartOptions" :series="leadsChartSeries"></apexchart>
          <div class="overview-action-btn-wrap">
            <router-link :to="{ name: 'admin.leads' }" class="btn-liquid-glass">
              <span class="shimmer-glare"></span>
              View Leads Overview
            </router-link>
          </div>
        </div>

        <!-- Project Status Chart (Apex Donut) -->
        <div class="card donut-card">
          <h3 class="panel-title">Projects Status Chart</h3>
          <apexchart type="donut" height="260" :options="projectsChartOptions" :series="projectsChartSeries"></apexchart>
          <div class="overview-action-btn-wrap">
            <router-link :to="{ name: 'admin.projects' }" class="btn-liquid-glass">
              <span class="shimmer-glare"></span>
              View Projects Status
            </router-link>
          </div>
        </div>

        <!-- Tickets Status Chart (Apex Donut) -->
        <div class="card donut-card">
          <h3 class="panel-title">Tickets Status Chart</h3>
          <apexchart type="donut" height="260" :options="ticketsChartOptions" :series="ticketsChartSeries"></apexchart>
          <div class="overview-action-btn-wrap">
            <router-link :to="{ name: 'admin.support' }" class="btn-liquid-glass">
              <span class="shimmer-glare"></span>
              View Tickets Status
            </router-link>
          </div>
        </div>

        <!-- Tickets Awaiting Reply by Department (Apex Donut) -->
        <div class="card donut-card">
          <h3 class="panel-title">Tickets Awaiting Reply by Department</h3>
          <apexchart type="donut" height="260" :options="departmentChartOptions" :series="departmentChartSeries"></apexchart>
          <div class="overview-action-btn-wrap">
            <router-link :to="{ name: 'admin.support' }" class="btn-liquid-glass">
              <span class="shimmer-glare"></span>
              View Department Tickets
            </router-link>
          </div>
        </div>

        <!-- Contracts by Type (Apex Bar) -->
        <div class="card chart-card">
          <h3 class="panel-title">Contracts by Type</h3>
          <apexchart type="bar" height="240" :options="contractsTypeChartOptions" :series="contractsTypeChartSeries"></apexchart>
        </div>

        <!-- Contracts Value by Type (Apex Area) -->
        <div class="card chart-card">
          <h3 class="panel-title">Contracts Value by Type (USD)</h3>
          <apexchart type="area" height="240" :options="contractsValueChartOptions" :series="contractsValueChartSeries"></apexchart>
        </div>

      </div>

    </div>

    <!-- ── Full Width Tables Section: Contracts Expiring Soon & Support Tickets Activity ──────────────── -->
    <div class="card contracts-panel" style="margin-top: 16px; width: 100%;">
      <div class="panel-header">
        <h3 class="panel-title">Contracts Expiring Soon</h3>
        <router-link :to="{ name: 'admin.contracts' }" class="link-blue" style="font-size:13px">View All</router-link>
      </div>
      <div class="table-toolbar">
        <div class="toolbar-left">
          <select class="select-sm"><option>10</option><option>25</option></select>
        </div>
        <div class="toolbar-right">
          <input class="input-sm" v-model="contractSearch" placeholder="Search..." />
        </div>
      </div>
      <div class="table-wrap" style="width:100%;">
        <table class="data-table" style="width:100%;table-layout:fixed;">
          <thead>
            <tr>
              <th style="width:40%;">Subject #</th>
              <th style="width:25%;">Customer</th>
              <th style="width:17.5%;">Start Date</th>
              <th style="width:17.5%;">End Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in filteredContracts" :key="c.subject">
              <td style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><a class="link-blue font-semibold" :title="c.subject">{{ c.subject }}</a></td>
              <td style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ c.customer }}</td>
              <td>{{ c.start }}</td>
              <td><span class="text-danger font-semibold">{{ c.end }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Responsive Card View -->
      <div class="mobile-cards-list">
        <div 
          v-for="c in filteredContracts" 
          :key="'m-dash-c-' + c.subject"
          class="mobile-row-card"
        >
          <div class="font-bold text-sm text-indigo-600">
            {{ c.subject }}
          </div>
          <div class="text-xs text-slate-600">
            🏢 {{ c.customer }}
          </div>
          <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-100 text-slate-500">
            <div>Start: {{ c.start }}</div>
            <div class="text-right text-rose-600 font-bold">End: {{ c.end }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Support Tickets Table -->
    <div class="card tickets-panel" style="margin-top: 16px; width: 100%;">
      <div class="panel-header">
        <h3 class="panel-title">Support Tickets activity</h3>
        <router-link :to="{ name: 'admin.support' }" class="link-blue" style="font-size:13px">View All</router-link>
      </div>
      <div class="table-wrap" style="width:100%;">
        <table class="data-table" style="width:100%;table-layout:fixed;">
          <thead>
            <tr>
              <th style="width:45%;">Subject</th>
              <th style="width:22%;">Client</th>
              <th style="width:18%;">Last Reply</th>
              <th style="width:15%;">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in ticketsMock" :key="t.id">
              <td style="overflow:hidden;text-overflow:ellipsis;">
                <a class="link-blue font-semibold">#{{ t.number }} - {{ t.subject }}</a>
                <div style="font-size:12px;color:#64748b;margin-top:3px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ t.excerpt }}</div>
              </td>
              <td style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ t.client }}</td>
              <td>{{ t.last_reply }}</td>
              <td><span class="badge" :class="t.statusClass">{{ t.status }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Responsive Card View -->
      <div class="mobile-cards-list">
        <div 
          v-for="t in ticketsMock" 
          :key="'m-dash-t-' + t.id"
          class="mobile-row-card"
        >
          <div class="flex items-center justify-between">
            <span class="badge" :class="t.statusClass">{{ t.status }}</span>
            <span class="text-[11px] text-slate-400">🕒 {{ t.last_reply }}</span>
          </div>
          <div class="font-bold text-sm text-indigo-600 pt-1">
            #{{ t.number }} - {{ t.subject }}
          </div>
          <div class="text-xs text-slate-500 line-clamp-2" v-if="t.excerpt">
            {{ t.excerpt }}
          </div>
          <div class="text-xs text-slate-600 pt-1 border-t border-slate-100">
            🏢 {{ t.client }}
          </div>
        </div>
      </div>
    </div>
    <div class="card staff-report-panel" style="margin-top: 12px;">
      <div class="panel-header">
        <h3 class="panel-title">Staff Tickets Report</h3>
        <span style="font-size:12px;color:#94a3b8">This Month</span>
      </div>
      <div class="table-toolbar">
        <div class="toolbar-left">
          <select class="select-sm"><option>10</option><option>25</option></select>
        </div>
        <div class="toolbar-right">
          <input class="input-sm" v-model="staffSearch" placeholder="Search..." />
        </div>
      </div>
      <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Staff Member</th>
            <th>Total Assigned</th>
            <th>Open Tickets</th>
            <th>Closed Tickets</th>
            <th>Replies To Tickets</th>
            <th class="text-right">Avg Reply Time</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in filteredStaff" :key="s.name">
            <td><strong>{{ s.name }}</strong></td>
            <td>{{ s.assigned }}</td>
            <td><span class="text-danger font-semibold">{{ s.open }}</span></td>
            <td><span class="text-success font-semibold">{{ s.closed }}</span></td>
            <td>{{ s.replies }}</td>
            <td class="text-right font-semibold" :class="s.replyTime === '-' ? 'text-muted' : 'text-success'">{{ s.replyTime }}</td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>

    <!-- ── Charts Section (full width below grid) ──────────── -->
    <div class="charts-section">
      <div class="charts-grid">

        <!-- Project Progress Tracker (Apex Bar) -->
        <div class="card progress-card">
          <h3 class="panel-title">Project Progress Tracker</h3>
          <apexchart type="bar" height="280" :options="progressChartOptions" :series="progressChartSeries"></apexchart>
        </div>

      </div>
    </div>

    <!-- ── Bottom Sections (Latest Activity + Goals) ──────────── -->
    <div class="bottom-grid">
      <!-- Latest Project Activity -->
      <div class="card activity-panel">
        <div class="panel-header">
          <h3 class="panel-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="17" height="17" style="vertical-align:middle;margin-right:4px"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Latest Project Activity
          </h3>
        </div>
        <div class="activity-feed-list">
          <div v-for="(act, i) in projectActivity" :key="i" class="feed-item">
            <div class="feed-dot" :class="act.dotClass"></div>
            <div class="feed-content">
              <div class="feed-time">{{ act.time }}</div>
              <div class="feed-text">
                <strong>{{ act.user }}</strong> - {{ act.action }}
              </div>
              <div v-if="act.detail" class="feed-detail">{{ act.detail }}</div>
              <div class="feed-project">{{ act.project }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Goals / Achievements -->
      <div class="card goals-panel">
        <div class="panel-header">
          <h3 class="panel-title">Goals</h3>
        </div>
        <div class="goals-list">
          <div v-for="g in goalList" :key="g.title" class="goal-item">
            <div class="goal-info">
              <div class="goal-title">{{ g.title }}</div>
              <div class="goal-subtitle">{{ g.subtitle }}</div>
            </div>
            <div class="goal-metrics">
              <div class="goal-achieved">{{ g.achieved }}</div>
              <div class="goal-progress-wrap">
                <div class="goal-progress-track">
                  <div class="goal-progress-fill" :style="{ width: g.progressPct + '%', background: g.progressColor }"></div>
                </div>
                <div class="goal-progress-label">{{ g.progressPct }}%</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    </template>
  </template>
  </div>
</template>

<script>
import { defineComponent, ref, computed, onMounted, nextTick } from 'vue';
import axios from 'axios';
import VueApexCharts from 'vue3-apexcharts';
import { VueDraggable } from 'vue-draggable-plus';
import { useThemeStore } from '../store/themeStore';

export default defineComponent({
  name: 'Dashboard',
  components: { apexchart: VueApexCharts, VueDraggable },
  setup() {
    const themeStore = useThemeStore();
    const metrics   = ref({});
    const loading   = ref(true);
    const activeTab = ref('Tasks');
    const tabSearch = ref('');
    const now       = new Date();

    // ── Calendar State ──────────────────────────────────────
    const calYear = ref(now.getFullYear());
    const calMonth = ref(now.getMonth());
    const calDay = ref(now.getDate());
    const calViewMode = ref('Month');
    const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const shortMonthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

    const calDaysInMonth = computed(() => new Date(calYear.value, calMonth.value + 1, 0).getDate());
    const calFirstDay = computed(() => new Date(calYear.value, calMonth.value, 1).getDay());

    const calMonthLabel = computed(() => {
      if (calViewMode.value === 'Day') {
        return dayNames[new Date(calYear.value, calMonth.value, calDay.value).getDay()] + ', ' + monthNames[calMonth.value] + ' ' + calDay.value + ', ' + calYear.value;
      }
      if (calViewMode.value === 'Week') {
        const startOfWeek = calDay.value - new Date(calYear.value, calMonth.value, calDay.value).getDay();
        const endOfWeek = startOfWeek + 6;
        const startMonth = startOfWeek < 1 ? monthNames[calMonth.value === 0 ? 11 : calMonth.value - 1] : monthNames[calMonth.value];
        const startDay = startOfWeek < 1 ? new Date(calYear.value, calMonth.value, 0).getDate() + startOfWeek : startOfWeek;
        return shortMonthNames[calMonth.value] + ' ' + Math.max(1, startOfWeek) + ' – ' + shortMonthNames[calMonth.value] + ' ' + Math.min(endOfWeek, calDaysInMonth.value) + ', ' + calYear.value;
      }
      return monthNames[calMonth.value] + ' ' + calYear.value;
    });

    const calCells = computed(() => {
      if (calViewMode.value === 'Day') {
        return [{ day: calDay.value, currentMonth: true, isToday: calDay.value === now.getDate() && calMonth.value === now.getMonth() && calYear.value === now.getFullYear() }];
      }
      if (calViewMode.value === 'Week') {
        const cells = [];
        const dayOfWeek = new Date(calYear.value, calMonth.value, calDay.value).getDay();
        const startOfWeek = calDay.value - dayOfWeek;
        for (let i = 0; i < 7; i++) {
          const d = startOfWeek + i;
          if (d < 1) {
            const prevMonthDays = new Date(calYear.value, calMonth.value, 0).getDate();
            cells.push({ day: prevMonthDays + d, currentMonth: false, isToday: false });
          } else if (d > calDaysInMonth.value) {
            cells.push({ day: d - calDaysInMonth.value, currentMonth: false, isToday: false });
          } else {
            cells.push({ day: d, currentMonth: true, isToday: d === now.getDate() && calMonth.value === now.getMonth() && calYear.value === now.getFullYear() });
          }
        }
        return cells;
      }
      // Month view
      const cells = [];
      for (let i = 0; i < calFirstDay.value; i++) {
        const d = new Date(calYear.value, calMonth.value, -(calFirstDay.value - 1 - i));
        cells.push({ day: d.getDate(), currentMonth: false, isToday: false });
      }
      for (let d = 1; d <= calDaysInMonth.value; d++) {
        cells.push({ day: d, currentMonth: true, isToday: d === now.getDate() && calMonth.value === now.getMonth() && calYear.value === now.getFullYear() });
      }
      const remaining = 7 - (cells.length % 7);
      if (remaining < 7) {
        for (let i = 1; i <= remaining; i++) {
          cells.push({ day: i, currentMonth: false, isToday: false });
        }
      }
      return cells;
    });

    const calMonthEvents = computed(() => {
      const events = {
        3:  { text: 'E-commerce API', color: 'blue' },
        8:  { text: 'Server Audit', color: 'red' },
        11: { text: 'Site Design', color: 'green' },
        15: { text: 'SLA Review', color: 'purple' },
        22: { text: 'AWS Migration', color: 'orange' },
      };
      return events;
    });

    const calPrev = () => {
      if (calViewMode.value === 'Day') {
        if (calDay.value === 1) {
          calMonth.value = calMonth.value === 0 ? 11 : calMonth.value - 1;
          if (calMonth.value === 11) calYear.value--;
          calDay.value = new Date(calYear.value, calMonth.value + 1, 0).getDate();
        } else {
          calDay.value--;
        }
      } else if (calViewMode.value === 'Week') {
        calDay.value -= 7;
        if (calDay.value < 1) {
          calMonth.value = calMonth.value === 0 ? 11 : calMonth.value - 1;
          if (calMonth.value === 11) calYear.value--;
          calDay.value = new Date(calYear.value, calMonth.value + 1, 0).getDate() + calDay.value;
        }
      } else {
        if (calMonth.value === 0) {
          calMonth.value = 11;
          calYear.value--;
        } else {
          calMonth.value--;
        }
      }
    };
    const calNext = () => {
      if (calViewMode.value === 'Day') {
        const maxDay = new Date(calYear.value, calMonth.value + 1, 0).getDate();
        if (calDay.value === maxDay) {
          calDay.value = 1;
          calMonth.value = calMonth.value === 11 ? 0 : calMonth.value + 1;
          if (calMonth.value === 0) calYear.value++;
        } else {
          calDay.value++;
        }
      } else if (calViewMode.value === 'Week') {
        const maxDay = new Date(calYear.value, calMonth.value + 1, 0).getDate();
        calDay.value += 7;
        if (calDay.value > maxDay) {
          calDay.value -= maxDay;
          calMonth.value = calMonth.value === 11 ? 0 : calMonth.value + 1;
          if (calMonth.value === 0) calYear.value++;
        }
      } else {
        if (calMonth.value === 11) {
          calMonth.value = 0;
          calYear.value++;
        } else {
          calMonth.value++;
        }
      }
    };
    const calSetView = (mode) => {
      calViewMode.value = mode;
    };

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

    // ── Estimate overview (live from API / computed) ────────
    const defaultEstimates = [
      { label: 'Draft',    count: 0, percentage: 0, colorClass: '',            barClass: 'ov-bar-slate' },
      { label: 'Not Sent', count: 0, percentage: 0, colorClass: '',            barClass: 'ov-bar-slate' },
      { label: 'Sent',     count: 0, percentage: 0, colorClass: 'text-info',   barClass: 'ov-bar-blue' },
      { label: 'Expired',  count: 0, percentage: 0, colorClass: 'text-muted',  barClass: 'ov-bar-slate' },
      { label: 'Declined', count: 0, percentage: 0, colorClass: 'text-danger', barClass: 'ov-bar-red' },
      { label: 'Accepted', count: 0, percentage: 0, colorClass: 'text-success',barClass: 'ov-bar-green' },
    ];
    const estimateOverview = computed(() => {
      const raw = metrics.value.estimate_overview;
      return (raw && raw.length) ? raw : defaultEstimates;
    });

    // ── Proposal overview (live from API / computed) ────────
    const defaultProposals = [
      { label: 'Draft',    count: 0, percentage: 0, colorClass: '',             barClass: 'ov-bar-slate' },
      { label: 'Sent',     count: 0, percentage: 0, colorClass: 'text-muted',   barClass: 'ov-bar-slate' },
      { label: 'Open',     count: 0, percentage: 0, colorClass: '',             barClass: 'ov-bar-slate' },
      { label: 'Revised',  count: 0, percentage: 0, colorClass: 'text-muted',   barClass: 'ov-bar-slate' },
      { label: 'Declined', count: 0, percentage: 0, colorClass: 'text-danger',  barClass: 'ov-bar-red' },
      { label: 'Accepted', count: 0, percentage: 0, colorClass: 'text-success', barClass: 'ov-bar-green' },
    ];
    const proposalOverview = computed(() => {
      const raw = metrics.value.proposal_overview;
      return (raw && raw.length) ? raw : defaultProposals;
    });

    // ── To-Do ─────────────────────────────────────────────────
    const allTodos = ref([]);
    const todoAddMode = ref(false);
    const todoNewText = ref('');
    const todoAddInput = ref(null);
    const editingTodoId = ref(null);
    const editText = ref('');
    const editInputEl = ref(null);

    const pendingTodos = computed({
      get: () => allTodos.value.filter(t => !t.done),
      set: (val) => {
        const doneItems = allTodos.value.filter(t => t.done);
        allTodos.value = [...val, ...doneItems];
      },
    });
    const doneTodos = computed(() => allTodos.value.filter(t => t.done));

    const fetchTodos = async () => {
      try {
        const res = await axios.get('/api/todos');
        allTodos.value = res.data;
      } catch (e) {
        console.error('Failed to load todos', e);
      }
    };

    const notifyTodosChanged = () => {
      if (typeof window !== 'undefined' && window.dispatchEvent) {
        window.dispatchEvent(new CustomEvent('refresh-todos'));
      }
    };

    const addTodo = async () => {
      if (!todoNewText.value.trim()) return;
      try {
        const res = await axios.post('/api/todos', { description: todoNewText.value.trim() });
        allTodos.value.push(res.data);
        todoNewText.value = '';
        todoAddMode.value = false;
        notifyTodosChanged();
      } catch (e) {
        console.error('Failed to add todo', e);
      }
    };

    const cancelAddTodo = () => {
      todoNewText.value = '';
      todoAddMode.value = false;
    };

    const toggleTodo = async (item) => {
      try {
        await axios.put('/api/todos/' + item.id, { done: !item.done });
        item.done = !item.done;
        notifyTodosChanged();
      } catch (e) {
        console.error('Failed to toggle todo', e);
      }
    };

    const deleteTodo = async (item) => {
      try {
        await axios.delete('/api/todos/' + item.id);
        allTodos.value = allTodos.value.filter(t => t.id !== item.id);
        notifyTodosChanged();
      } catch (e) {
        console.error('Failed to delete todo', e);
      }
    };

    const startEditTodo = (item) => {
      editingTodoId.value = item.id;
      editText.value = item.text;
      nextTick(() => {
        if (editInputEl.value) editInputEl.value.focus();
      });
    };

    const saveEditTodo = async (item) => {
      if (!editText.value.trim()) { cancelEditTodo(); return; }
      if (editText.value.trim() === item.text) { cancelEditTodo(); return; }
      try {
        await axios.put('/api/todos/' + item.id, { description: editText.value.trim() });
        item.text = editText.value.trim();
        cancelEditTodo();
      } catch (e) {
        console.error('Failed to update todo', e);
      }
    };

    const cancelEditTodo = () => {
      editingTodoId.value = null;
      editText.value = '';
    };

    const onDragEnd = async () => {
      const order = pendingTodos.value.map(t => t.id);
      try {
        await axios.put('/api/todos-reorder', { order });
      } catch (e) {
        console.error('Failed to reorder todos', e);
      }
    };

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
      const apiList = metrics.value.project_status_overview;
      const list = (apiList && apiList.length) ? apiList : [
        { name: 'Finished',    count: 1, color: '#22c55e', percentage: 20 },
        { name: 'In Progress', count: 2, color: '#3b82f6', percentage: 40 },
        { name: 'On Hold',     count: 1, color: '#f59e0b', percentage: 20 },
        { name: 'Not Started', count: 1, color: '#94a3b8', percentage: 20 },
      ];
      let accum = 0;
      return list.map(item => {
        const offset = 100 - accum + 25;
        accum += (item.percentage || 0);
        return { ...item, color: item.color || '#7367F0', offset: offset % 100 };
      });
    });

    const ticketDonutSlices = computed(() => {
      const apiList = metrics.value.ticket_status_overview;
      const list = (apiList && apiList.length) ? apiList : [
        { name: 'Open',        count: 2, color: '#ef4444', percentage: 40 },
        { name: 'In Progress', count: 1, color: '#3b82f6', percentage: 20 },
        { name: 'Answered',    count: 1, color: '#22c55e', percentage: 20 },
        { name: 'Closed',      count: 1, color: '#94a3b8', percentage: 20 },
      ];
      let accum = 0;
      return list.map(item => {
        const offset = 100 - accum + 25;
        accum += (item.percentage || 0);
        return { ...item, color: item.color || '#7367F0', offset: offset % 100 };
      });
    });

    const departmentDonutSlices = computed(() => {
      const apiList = metrics.value.department_tickets;
      const list = (apiList && apiList.length) ? apiList : [
        { name: 'Support',        count: 4, color: '#3b82f6', percentage: 40 },
        { name: 'Sales',          count: 2, color: '#22c55e', percentage: 20 },
        { name: 'Development',    count: 2, color: '#f59e0b', percentage: 20 },
        { name: 'Billing',        count: 2, color: '#8b5cf6', percentage: 20 },
      ];
      let accum = 0;
      return list.map(item => {
        const offset = 100 - accum + 25;
        accum += (item.percentage || 0);
        return { ...item, color: item.color || '#7367F0', offset: offset % 100 };
      });
    });
    const totalDepartmentTickets = computed(() =>
      departmentDonutSlices.value.reduce((s, i) => s + (i.count || 0), 0)
    );

    // ── Payment Records Columns ──────────────────────────────
    const paymentColumns = [
      { month: 'Jan', billed: 42000, billedPct: 28,  received: 38000, receivedPct: 25 },
      { month: 'Feb', billed: 78000, billedPct: 52,  received: 62000, receivedPct: 41 },
      { month: 'Mar', billed: 110000,billedPct: 73,  received: 95000, receivedPct: 63 },
      { month: 'Apr', billed: 64000, billedPct: 42,  received: 64000, receivedPct: 42 },
      { month: 'May', billed: 95000, billedPct: 63,  received: 80000, receivedPct: 53 },
      { month: 'Jun', billed: 145000,billedPct: 96,  received: 120000,receivedPct: 80 },
    ];

    // ── ApexCharts Options ───────────────────────────────────
    const chartColors = ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#f97316', '#06b6d4', '#94a3b8'];

    const leadsChartOptions = computed(() => ({
      chart: { type: 'donut', animations: { enabled: true } },
      labels: leadDonutSlices.value.map(s => s.name),
      colors: leadDonutSlices.value.map(s => s.color),
      legend: { show: true, position: 'bottom', fontSize: '13px', offsetY: 0 },
      dataLabels: { enabled: true, style: { fontSize: '13px', fontWeight: 600 }, dropShadow: { enabled: false } },
      plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'Leads', fontSize: '16px', fontWeight: 700, color: '#1e293b' } } } } },
      stroke: { show: false },
      responsive: [{ breakpoint: 480, options: { chart: { width: 220 } } }],
    }));
    const leadsChartSeries = computed(() => leadDonutSlices.value.map(s => s.count));

    const projectsChartOptions = computed(() => ({
      chart: { type: 'donut', animations: { enabled: true } },
      labels: projectDonutSlices.value.map(s => s.name),
      colors: projectDonutSlices.value.map(s => s.color || '#7367F0'),
      legend: { show: true, position: 'bottom', fontSize: '13px' },
      dataLabels: { enabled: true, style: { fontSize: '13px', fontWeight: 600 }, dropShadow: { enabled: false } },
      plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'Projects', fontSize: '16px', fontWeight: 700, color: '#1e293b' } } } } },
      stroke: { show: false },
      responsive: [{ breakpoint: 480, options: { chart: { width: 220 } } }],
    }));
    const projectsChartSeries = computed(() => projectDonutSlices.value.map(s => s.count));

    const ticketsChartOptions = computed(() => ({
      chart: { type: 'donut', animations: { enabled: true } },
      labels: ticketDonutSlices.value.map(s => s.name),
      colors: ticketDonutSlices.value.map(s => s.color || '#7367F0'),
      legend: { show: true, position: 'bottom', fontSize: '13px' },
      dataLabels: { enabled: true, style: { fontSize: '13px', fontWeight: 600 }, dropShadow: { enabled: false } },
      plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'Tickets', fontSize: '16px', fontWeight: 700, color: '#1e293b' } } } } },
      stroke: { show: false },
      responsive: [{ breakpoint: 480, options: { chart: { width: 220 } } }],
    }));
    const ticketsChartSeries = computed(() => ticketDonutSlices.value.map(s => s.count));

    const departmentChartOptions = computed(() => ({
      chart: { type: 'donut', animations: { enabled: true } },
      labels: departmentDonutSlices.value.map(s => s.name),
      colors: departmentDonutSlices.value.map(s => s.color || '#7367F0'),
      legend: { show: true, position: 'bottom', fontSize: '13px' },
      dataLabels: { enabled: true, style: { fontSize: '13px', fontWeight: 600 }, dropShadow: { enabled: false } },
      plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'Tickets', fontSize: '16px', fontWeight: 700, color: '#1e293b' } } } } },
      stroke: { show: false },
      responsive: [{ breakpoint: 480, options: { chart: { width: 220 } } }],
    }));
    const departmentChartSeries = computed(() => departmentDonutSlices.value.map(s => s.count));

    // ── Overview Donut Charts (Invoice / Estimate / Proposal) ────
    const invoiceDonutColors = {
      draft: '#94a3b8', unpaid: '#ef4444', paid: '#22c55e',
      overdue: '#f59e0b', partially_paid: '#f97316', cancelled: '#cbd5e1',
    };
    const invoiceDonutOptions = computed(() => ({
      chart: { type: 'donut', animations: { enabled: true } },
      labels: invoiceOverview.value.map(r => r.label),
      colors: invoiceOverview.value.map(r => invoiceDonutColors[r.status] || '#94a3b8'),
      legend: { show: false },
      dataLabels: { enabled: false },
      plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Invoices', fontSize: '14px', fontWeight: 700, color: '#1e293b' } } } } },
      stroke: { show: false },
      responsive: [{ breakpoint: 480, options: { chart: { width: 180 } } }],
    }));
    const invoiceDonutSeries = computed(() => invoiceOverview.value.map(r => r.count));

    const estimateDonutColors = {
      Draft: '#94a3b8', 'Not Sent': '#cbd5e1', Sent: '#3b82f6',
      Expired: '#f59e0b', Declined: '#ef4444', Accepted: '#22c55e',
    };
    const estimateDonutOptions = computed(() => ({
      chart: { type: 'donut', animations: { enabled: true } },
      labels: estimateOverview.value.map(r => r.label),
      colors: estimateOverview.value.map(r => estimateDonutColors[r.label] || '#94a3b8'),
      legend: { show: false },
      dataLabels: { enabled: false },
      plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Estimates', fontSize: '14px', fontWeight: 700, color: '#1e293b' } } } } },
      stroke: { show: false },
      responsive: [{ breakpoint: 480, options: { chart: { width: 180 } } }],
    }));
    const estimateDonutSeries = computed(() => estimateOverview.value.map(r => r.count));

    const proposalDonutColors = {
      Draft: '#94a3b8', Sent: '#3b82f6', Open: '#06b6d4',
      Revised: '#f59e0b', Declined: '#ef4444', Accepted: '#22c55e',
    };
    const proposalDonutOptions = computed(() => ({
      chart: { type: 'donut', animations: { enabled: true } },
      labels: proposalOverview.value.map(r => r.label),
      colors: proposalOverview.value.map(r => proposalDonutColors[r.label] || '#94a3b8'),
      legend: { show: false },
      dataLabels: { enabled: false },
      plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Proposals', fontSize: '14px', fontWeight: 700, color: '#1e293b' } } } } },
      stroke: { show: false },
      responsive: [{ breakpoint: 480, options: { chart: { width: 180 } } }],
    }));
    const proposalDonutSeries = computed(() => proposalOverview.value.map(r => r.count));

    const paymentChartOptions = computed(() => ({
      chart: { type: 'bar', toolbar: { show: false }, animations: { enabled: true } },
      responsive: [{ breakpoint: 480, options: { chart: { height: 220 }, plotOptions: { bar: { columnWidth: '45%' } } } }],
      xaxis: { categories: paymentColumns.map(c => c.month), labels: { style: { fontSize: '12px' } } },
      yaxis: { labels: { formatter: v => '$' + (v / 1000).toFixed(0) + 'k', style: { fontSize: '12px' } } },
      colors: ['#86efac', '#fbcfe8'],
      legend: { show: true, position: 'bottom', fontSize: '13px' },
      dataLabels: { enabled: false },
      plotOptions: { bar: { horizontal: false, columnWidth: '60%', borderRadius: 4 } },
      grid: { borderColor: '#f1f5f9' },
    }));
    const paymentChartSeries = computed(() => [
      { name: 'Billed', data: paymentColumns.map(c => c.billed) },
      { name: 'Received', data: paymentColumns.map(c => c.received) },
    ]);

    const progressChartOptions = computed(() => ({
      chart: { type: 'bar', toolbar: { show: false }, animations: { enabled: true } },
      responsive: [{ breakpoint: 480, options: { chart: { height: 240 }, plotOptions: { bar: { columnWidth: '40%' } } } }],
      xaxis: { categories: projectProgressList.map(p => p.name), labels: { style: { fontSize: '13px', fontWeight: 600 } } },
      yaxis: { max: 100, labels: { formatter: v => v + '%', style: { fontSize: '12px' } } },
      colors: ['#3b82f6'],
      plotOptions: { bar: { horizontal: false, columnWidth: '30%', borderRadius: 4, dataLabels: { position: 'top' } } },
      dataLabels: { enabled: true, formatter: v => v + '%', style: { fontSize: '13px', fontWeight: 700, colors: ['#1e293b'] }, offsetY: -20 },
      grid: { borderColor: '#f1f5f9' },
      tooltip: { y: { formatter: v => v + '%' } },
    }));
    const progressChartSeries = computed(() => [
      { name: 'Progress', data: projectProgressList.map(p => p.percentage) },
    ]);

    // ── Contracts Charts ──────────────────────────────────────
    const contractsByType = [
      { name: 'General', count: 1, value: 1500 },
      { name: 'Software License Agreement', count: 1, value: 0 },
      { name: 'Service Level Agreement (SLA)', count: 2, value: 17000 },
    ];

    const contractsTypeChartOptions = computed(() => ({
      chart: { type: 'bar', toolbar: { show: false }, animations: { enabled: true } },
      responsive: [{ breakpoint: 480, options: { chart: { height: 200 }, plotOptions: { bar: { columnWidth: '40%' } } } }],
      xaxis: { categories: contractsByType.map(c => c.name), labels: { style: { fontSize: '12px', fontWeight: 600 } } },
      yaxis: { labels: { style: { fontSize: '12px' } } },
      colors: ['#3b82f6'],
      plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 4, dataLabels: { position: 'top' } } },
      dataLabels: { enabled: true, style: { fontSize: '13px', fontWeight: 700, colors: ['#1e293b'] }, offsetY: -20 },
      grid: { borderColor: '#f1f5f9' },
    }));
    const contractsTypeChartSeries = computed(() => [
      { name: 'Contracts', data: contractsByType.map(c => c.count) },
    ]);

    const contractsValueChartOptions = computed(() => ({
      chart: { type: 'area', toolbar: { show: false }, animations: { enabled: true } },
      responsive: [{ breakpoint: 480, options: { chart: { height: 200 } } }],
      xaxis: { categories: contractsByType.map(c => c.name), labels: { style: { fontSize: '12px', fontWeight: 600 } } },
      yaxis: { labels: { formatter: v => '$' + v.toLocaleString(), style: { fontSize: '12px' } } },
      colors: ['#8b5cf6'],
      dataLabels: { enabled: true, formatter: v => '$' + v.toLocaleString(), style: { fontSize: '12px', fontWeight: 700, colors: ['#8b5cf6'] } },
      fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.2 } },
      stroke: { curve: 'smooth', width: 2 },
      markers: { size: 4, colors: ['#fff'], strokeColors: '#8b5cf6', strokeWidth: 2 },
      grid: { borderColor: '#f1f5f9' },
      tooltip: { y: { formatter: v => '$' + v.toLocaleString() } },
    }));
    const contractsValueChartSeries = computed(() => [
      { name: 'Value (USD)', data: contractsByType.map(c => c.value) },
    ]);

    // ── Live Tables & Fallback Collections ───────────────────
    const tasksMock = [
      { name: 'Configure Stripe Webhook triggers', status: 'In Progress', start: 'Jun 10, 2026', due: 'Jun 20, 2026', assigned: 'Marcus Lesch', statusClass: 'badge-blue' },
      { name: 'Database migrations to MAMP MySQL', status: 'Completed', start: 'Jun 01, 2026', due: 'Jun 05, 2026', assigned: 'Alexander', statusClass: 'badge-green' },
      { name: 'Client portal responsive layout fixes', status: 'Testing', start: 'Jun 12, 2026', due: 'Jun 15, 2026', assigned: 'Tamara Howell', statusClass: 'badge-yellow' },
      { name: 'Theme style builder customizations', status: 'Not Started', start: 'Jun 14, 2026', due: 'Jun 25, 2026', assigned: 'Elias Konopelski', statusClass: 'badge-default' },
    ];

    const projectsMock = computed(() => {
      const list = metrics.value.projects;
      return (list && list.length) ? list : [
        { name: 'E-commerce API Integration', client: 'Nader-Abernathy', billing: 'Fixed Rate', status: 'In Progress' },
        { name: 'Brand Strategy Redesign', client: 'Mertz-Bergnaum', billing: 'Fixed Rate', status: 'In Progress' },
      ];
    });

    const ticketsMock = computed(() => {
      const list = metrics.value.tickets;
      return (list && list.length) ? list : [
        { id: 1, number: 1024, subject: 'Cannot connect to database', excerpt: 'Getting access denied errors for user crm_db_user from 10.0.3...', client: 'Nader-Abernathy', last_reply: 'Jun 13, 2026 14:22', status: 'In Progress', statusClass: 'badge-blue' },
        { id: 2, number: 1022, subject: 'Billing discrepancy - Invoice INV-00018', excerpt: 'Double charge on credit card for the maintenance SLA billing cycle...', client: 'Schroeder and Sons', last_reply: 'Jun 12, 2026 09:12', status: 'Open', statusClass: 'badge-red' },
        { id: 3, number: 1020, subject: 'Surveys feedback result exports failing', excerpt: 'Getting 500 error when clicking CSV exports for goals v2.3...', client: 'Halvorson LLC', last_reply: 'Jun 13, 2026 10:15', status: 'Open', statusClass: 'badge-red' }
      ];
    });

    const projectProgressList = [
      { name: 'E-commerce API Integration', percentage: 84, colorClass: 'bg-info' },
      { name: 'Brand Strategy Redesign', percentage: 100, colorClass: 'bg-success' },
      { name: 'Legacy App Migration', percentage: 50, colorClass: 'bg-warning' },
      { name: 'SEO Auditing & Content writing', percentage: 100, colorClass: 'bg-success' },
      { name: 'DevOps CI/CD Automation', percentage: 0, colorClass: 'bg-default' }
    ];

    // ── Live Latest Activity Tab ─────────────────────────────
    const latestActivity = computed(() => {
      const list = metrics.value.activity_logs;
      return (list && list.length) ? list : [
        { user: 'Lance Little', action: 'Added new task assignee', project: 'Brochure Design', time: '56 minutes ago', colorClass: 'dot-blue' },
        { user: 'Lance Little', action: 'Added new task assignee', project: 'Brochure Design', time: '56 minutes ago', colorClass: 'dot-blue' },
        { user: 'Lance Little', action: 'Created discussion', project: 'Website Redesign', time: '12 hrs ago', colorClass: 'dot-green' },
        { user: 'Lance Little', action: 'Created new milestone', project: 'Website Redesign', time: '12 hrs ago', colorClass: 'dot-green' },
        { user: 'Lance Little', action: 'Created the project', project: 'Website Redesign', time: '12 hrs ago', colorClass: 'dot-purple' },
      ];
    });

    // ── This Week Events ─────────────────────────────────────
    const thisWeekEvents = [
      { title: 'King said, with a sigh: \'he taught Laughing and Grief, they.', date: '2026-06-20 00:00:30', day: '20', month: 'Jun', type: 'Public event' },
      { title: 'Then followed the Knave was standing before...', date: '2026-06-20 00:00:00', day: '20', month: 'Jun', type: 'Public event' },
      { title: 'I haven\'t been invited yet.', date: '2026-06-21 00:00:00', day: '21', month: 'Jun', type: 'Event' },
    ];
    const nextWeekEvents = 2;

    // ── Contracts Expiring Soon ──────────────────────────────
    const contractSearch = ref('');
    const contractsMock = computed(() => {
      const list = metrics.value.expiring_contracts;
      return (list && list.length) ? list : [
        { subject: 'Rabbit\'s voice; and Alice was not a regular rule: you.', customer: 'Stamm, Jast and Collins', start: '2026-06-19', end: '2026-06-26' },
        { subject: 'March Hare. The Hatter was the first sentence in her head.', customer: 'Brakus-Funk', start: '2026-06-19', end: '2026-06-25' },
        { subject: 'I shan\'t grow any more--As it is, I can\'t quite follow it.', customer: 'Strosin-Mueller', start: '2026-06-19', end: '2026-06-24' },
      ];
    });
    const filteredContracts = computed(() => {
      const list = contractsMock.value;
      if (!contractSearch.value) return list;
      const q = contractSearch.value.toLowerCase();
      return list.filter(c => (c.subject || '').toLowerCase().includes(q) || (c.customer || '').toLowerCase().includes(q));
    });

    // ── Staff Search & Report ────────────────────────────────
    const staffSearch = ref('');
    const staffPerformance = computed(() => {
      const list = metrics.value.staff_report;
      return (list && list.length) ? list : [
        { name: 'Armando Turcotte', assigned: 15, open: 2, closed: 13, replies: 0, replyTime: '-' },
        { name: 'Elias Konopelski', assigned: 10, open: 1, closed: 9, replies: 0, replyTime: '-' },
        { name: 'Tamara Howell', assigned: 12, open: 0, closed: 12, replies: 0, replyTime: '-' },
        { name: 'Marcus Lesch', assigned: 8, open: 3, closed: 5, replies: 0, replyTime: '-' },
      ];
    });
    const filteredStaff = computed(() => {
      const list = staffPerformance.value;
      if (!staffSearch.value) return list;
      const q = staffSearch.value.toLowerCase();
      return list.filter(s => (s.name || '').toLowerCase().includes(q));
    });

    // ── Project Activity Feed ────────────────────────────────
    const projectActivity = computed(() => {
      const list = metrics.value.activity_logs;
      return (list && list.length) ? list : [
        { user: 'Lance Little', action: 'Added new task assignee', project: 'Brochure Design', detail: 'test - Lance Little', time: '56 minutes ago', dotClass: 'feed-dot--blue' },
        { user: 'Lance Little', action: 'Created discussion', project: 'Website Redesign', detail: 'Should we add blue color?', time: '12 hrs ago', dotClass: 'feed-dot--green' },
        { user: 'Lance Little', action: 'Created the project', project: 'Website Redesign', time: '12 hrs ago', dotClass: 'feed-dot--orange' },
      ];
    });

    // ── Goals / Achievements ─────────────────────────────────
    const goalList = computed(() => {
      const list = metrics.value.goals;
      return (list && list.length) ? list : [
        { title: 'Achieve Total Income', subtitle: 'While the Owl had the best thing to.', achieved: '4170', progressPct: 100, progressColor: '#22c55e' },
        { title: 'Convert X Leads', subtitle: 'He trusts to you never to.', achieved: '0', progressPct: 0, progressColor: '#94a3b8' },
        { title: 'Increase Customer Number', subtitle: 'LITTLE larger, sir, if you wouldn\'t have come.', achieved: '10', progressPct: 34.48, progressColor: '#f59e0b' },
        { title: 'Invoiced Amount', subtitle: 'She soon got it out to sea.', achieved: '29491', progressPct: 100, progressColor: '#22c55e' },
      ];
    });

    const liveTasksList = computed(() => {
      const list = metrics.value.tasks;
      return (list && list.length) ? list : tasksMock;
    });

    const filteredTasks = computed(() => {
      const list = liveTasksList.value;
      if (!tabSearch.value) return list;
      const q = tabSearch.value.toLowerCase();
      return list.filter(t => (t.name || '').toLowerCase().includes(q) || (t.assigned || '').toLowerCase().includes(q));
    });

    // ── Load Metrics ──────────────────────────────────────────
    const loadMetrics = async () => {
      loading.value = true;
      try {
        const res = await axios.get('/api/dashboard-metrics');
        metrics.value = res.data;
        if (res.data.todo_items) {
          allTodos.value = res.data.todo_items;
        }
      } catch (e) {
        console.error('Failed to load metrics', e);
      } finally {
        loading.value = false;
      }
    };

    // ════════════════════════════════════════════════════════════════
    // VUEXY MODERN ENTERPRISE DASHBOARD STATE & CHART OPTIONS
    // ════════════════════════════════════════════════════════════════
    const earningActiveTab = ref('Orders');

    // 1. Orders Mini Chart
    const vuexyOrdersChartOptions = computed(() => ({
      chart: { type: 'bar', sparkline: { enabled: true }, toolbar: { show: false } },
      colors: [themeStore.primaryColor || '#7367F0'],
      plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } },
      tooltip: { enabled: false },
    }));
    const vuexyOrdersSeries = [{ name: 'Orders', data: [28, 40, 36, 52, 38, 60, 55] }];

    // 2. Sales Sparkline
    const vuexySalesSparkOptions = {
      chart: { type: 'area', sparkline: { enabled: true }, toolbar: { show: false } },
      colors: ['#28C76F'],
      stroke: { curve: 'smooth', width: 2.5 },
      fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
      tooltip: { enabled: false },
    };
    const vuexySalesSparkSeries = [{ name: 'Sales', data: [10, 25, 45, 30, 60, 48, 70] }];

    // 3. Revenue Growth Weekly Chart
    const vuexyRevenueGrowthOptions = {
      chart: { type: 'bar', sparkline: { enabled: true }, toolbar: { show: false } },
      colors: ['#28C76F'],
      plotOptions: { bar: { columnWidth: '40%', borderRadius: 4 } },
      tooltip: { enabled: false },
    };
    const vuexyRevenueGrowthSeries = [{ name: 'Growth', data: [30, 45, 55, 70, 90, 65, 50] }];

    // 4. Earning Reports Chart
    const vuexyEarningsChartOptions = computed(() => ({
      chart: { type: 'bar', toolbar: { show: false }, parentHeightOffset: 0 },
      plotOptions: {
        bar: {
          columnWidth: '32%',
          borderRadius: 6,
          dataLabels: { position: 'top' },
          colors: {
            ranges: [{ from: 40, to: 100, color: themeStore.primaryColor || '#7367F0' }]
          }
        }
      },
      colors: ['#E8E7FD'],
      dataLabels: {
        enabled: true,
        formatter: (val) => val + 'k',
        offsetY: -20,
        style: { fontSize: '11px', colors: ['#5D596C'], fontWeight: 600 }
      },
      grid: { show: false, padding: { top: 10, left: 0, right: 0, bottom: 0 } },
      xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: { style: { colors: '#A8AAAE', fontSize: '12px' } }
      },
      yaxis: { show: false },
      tooltip: { enabled: true }
    }));

    const vuexyEarningsSeries = computed(() => {
      const map = {
        Orders: [28, 10, 45, 38, 15, 30, 35, 30, 8],
        Sales:  [35, 25, 50, 40, 28, 45, 40, 35, 20],
        Profit: [15, 8, 30, 25, 10, 20, 22, 18, 5],
        Income: [40, 30, 60, 50, 35, 55, 50, 45, 25],
      };
      return [{ name: earningActiveTab.value, data: map[earningActiveTab.value] || map.Orders }];
    });

    // 5. Radar Chart (Last 6 Months)
    const vuexyRadarOptions = computed(() => ({
      chart: { type: 'radar', toolbar: { show: false }, parentHeightOffset: 0 },
      colors: [themeStore.primaryColor || '#7367F0', '#00CFE8'],
      stroke: { width: 1.5 },
      fill: { opacity: [0.75, 0.4] },
      markers: { size: 0 },
      legend: { show: true, position: 'bottom', markers: { width: 8, height: 8, radius: 12 } },
      xaxis: { categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'] },
      yaxis: { show: false }
    }));
    const vuexyRadarSeries = [
      { name: 'Sales', data: [70, 85, 90, 65, 80, 75] },
      { name: 'Visits', data: [50, 60, 70, 45, 60, 55] }
    ];

    // 6. Sales by Countries List
    const vuexyCountries = [
      { flag: '🇺🇸', name: 'United States', val: '$8,567k', pct: '25.8%', up: true },
      { flag: '🇧🇷', name: 'Brazil', val: '$2,415k', pct: '6.2%', up: false },
      { flag: '🇮🇳', name: 'India', val: '$865k', pct: '12.4%', up: true },
      { flag: '🇦🇺', name: 'Australia', val: '$745k', pct: '11.9%', up: false },
      { flag: '🇫🇷', name: 'France', val: '$45', pct: '16.2%', up: true },
      { flag: '🇨🇳', name: 'China', val: '$12k', pct: '14.8%', up: true },
    ];

    // 7. Project Status Area Sparkline
    const vuexyProjectStatusOptions = {
      chart: { type: 'area', sparkline: { enabled: true }, toolbar: { show: false } },
      colors: ['#FF9F43'],
      stroke: { curve: 'stepline', width: 2 },
      fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
      tooltip: { enabled: false },
    };
    const vuexyProjectStatusSeries = [{ name: 'Earnings', data: [30, 50, 40, 30, 45, 60, 45, 80] }];

    // 8. Active Projects List
    const vuexyActiveProjects = [
      { title: 'Laravel', sub: 'Ecommerce', pct: 65, color: '#EA5455', bgClass: 'bg-rose-50 text-rose-600', icon: 'L' },
      { title: 'Figma', sub: 'App UI Kit', pct: 86, color: '#7367F0', bgClass: 'bg-indigo-50 text-indigo-600', icon: 'F' },
      { title: 'VueJs', sub: 'Calendar App', pct: 90, color: '#28C76F', bgClass: 'bg-emerald-50 text-emerald-600', icon: 'V' },
      { title: 'React', sub: 'Dashboard', pct: 37, color: '#00CFE8', bgClass: 'bg-cyan-50 text-cyan-600', icon: 'R' },
      { title: 'Bootstrap', sub: 'Website', pct: 22, color: '#6366F1', bgClass: 'bg-violet-50 text-violet-600', icon: 'B' },
      { title: 'Sketch', sub: 'Website Design', pct: 29, color: '#FF9F43', bgClass: 'bg-amber-50 text-amber-600', icon: 'S' },
    ];

    // 9. Last Transactions
    const vuexyTransactions = [
      { brand: 'visa', brandLabel: 'VISA', num: '*4230', date: '17 Mar 2022', status: 'Verified', statusClass: 'vuexy-badge-pill--success', trend: '+$1,678' },
      { brand: 'mastercard', brandLabel: 'MC', num: '*5578', date: '12 Feb 2022', status: 'Rejected', statusClass: 'vuexy-badge-pill--danger', trend: '-$839' },
      { brand: 'amex', brandLabel: 'AMEX', num: '*4567', date: '28 Feb 2022', status: 'Verified', statusClass: 'vuexy-badge-pill--success', trend: '+$435' },
      { brand: 'visa', brandLabel: 'VISA', num: '*5699', date: '8 Jan 2022', status: 'Pending', statusClass: 'vuexy-badge-pill--secondary', trend: '+$2,345' },
      { brand: 'visa', brandLabel: 'VISA', num: '*5699', date: '6 Jan 2022', status: 'Rejected', statusClass: 'vuexy-badge-pill--danger', trend: '-$234' },
    ];

    onMounted(() => {
      loadMetrics();
      fetchTodos();
    });

    return {
      metrics, loading, topStats,
      invoiceOverview, estimateOverview, proposalOverview,
      invoiceDonutColors, estimateDonutColors, proposalDonutColors,
      invoiceDonutOptions, invoiceDonutSeries,
      estimateDonutOptions, estimateDonutSeries,
      proposalDonutOptions, proposalDonutSeries,
      pendingTodos, doneTodos, formatCurrency,
      todoAddMode, todoNewText, todoAddInput,
      editingTodoId, editText, editInputEl,
      addTodo, cancelAddTodo, toggleTodo, deleteTodo,
      startEditTodo, saveEditTodo, cancelEditTodo, onDragEnd,
      activeTab, tabSearch, filteredTasks,
      leadDonutSlices, projectDonutSlices, ticketDonutSlices,
      departmentDonutSlices, totalDepartmentTickets,
      leadsChartOptions, leadsChartSeries,
      projectsChartOptions, projectsChartSeries,
      ticketsChartOptions, ticketsChartSeries,
      departmentChartOptions, departmentChartSeries,
      paymentChartOptions, paymentChartSeries,
      progressChartOptions, progressChartSeries,
      contractsTypeChartOptions, contractsTypeChartSeries,
      contractsValueChartOptions, contractsValueChartSeries,
      tasksMock, projectsMock, ticketsMock,
      staffPerformance, filteredStaff, staffSearch,
      projectProgressList, latestActivity, projectActivity, goalList,
      thisWeekEvents, nextWeekEvents,
      contractsMock, contractSearch, filteredContracts,
      calYear, calMonth, calViewMode, calMonthLabel, calCells, calMonthEvents,
      calPrev, calNext, calSetView,
      themeStore,
      earningActiveTab,
      vuexyOrdersChartOptions, vuexyOrdersSeries,
      vuexySalesSparkOptions, vuexySalesSparkSeries,
      vuexyRevenueGrowthOptions, vuexyRevenueGrowthSeries,
      vuexyEarningsChartOptions, vuexyEarningsSeries,
      vuexyRadarOptions, vuexyRadarSeries,
      vuexyCountries,
      vuexyProjectStatusOptions, vuexyProjectStatusSeries,
      vuexyActiveProjects,
      vuexyTransactions,
    };
  }
});
</script>

<style scoped>
/* ==========================================================================
   VUEXY MODERN DASHBOARD SPECIFIC STYLES
   ========================================================================== */
.vuexy-dashboard-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Row 1: Top Metrics */
.vuexy-top-metrics {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 20px;
}

@media (max-width: 1300px) {
  .vuexy-top-metrics {
    grid-template-columns: repeat(3, 1fr);
  }
}
@media (max-width: 768px) {
  .vuexy-top-metrics {
    grid-template-columns: 1fr;
  }
}

.vuexy-stat-card {
  padding: 18px !important;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  min-height: 140px;
}

.vuexy-stat-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.vuexy-stat-val {
  font-size: 22px;
  font-weight: 700;
  color: #4b465c;
  line-height: 1.2;
}

.vuexy-stat-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 6px;
}

.vuexy-icon-badge {
  width: 38px;
  height: 38px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Row 2: Earning Reports & Radar */
.vuexy-row-2 {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 24px;
}
@media (max-width: 1024px) {
  .vuexy-row-2 {
    grid-template-columns: 1fr;
  }
}

.vuexy-earnings-tabs {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.vuexy-etab {
  flex: 1;
  min-width: 90px;
  border: 1px solid #dbdade;
  border-radius: 6px;
  padding: 10px 14px;
  background: #ffffff;
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  transition: all 0.2s;
}

.vuexy-etab--active {
  border-color: var(--vuexy-primary, #7367F0) !important;
  box-shadow: 0 2px 6px rgba(115, 103, 240, 0.15);
}

.vuexy-etab-icon {
  width: 32px;
  height: 32px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.vuexy-etab-label {
  font-size: 13.5px;
  font-weight: 600;
  color: #4b465c;
}

.vuexy-etab--plus {
  flex: 0 0 42px;
  min-width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #82808c;
  border-style: dashed;
}

/* Row 3: Country, Status, Active Projects */
.vuexy-row-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}
@media (max-width: 1100px) {
  .vuexy-row-3 {
    grid-template-columns: 1fr;
  }
}

.vuexy-country-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.vuexy-country-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.vuexy-country-flag {
  font-size: 24px;
  width: 32px;
}

.vuexy-country-info {
  flex: 1;
  margin-left: 10px;
}

.vuexy-country-val {
  font-size: 14px;
  font-weight: 700;
  color: #4b465c;
}

.vuexy-country-name {
  font-size: 12px;
  color: #82808c;
}

.vuexy-country-trend {
  font-size: 13px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
}

.vuexy-projects-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.vuexy-proj-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.vuexy-proj-logo {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 13px;
  flex-shrink: 0;
}

.vuexy-proj-info {
  flex: 1;
}

.vuexy-proj-title {
  font-size: 13px;
  font-weight: 600;
  color: #4b465c;
}

.vuexy-proj-sub {
  font-size: 11.5px;
  color: #82808c;
}

.vuexy-proj-prog {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100px;
}

.vuexy-proj-track {
  flex: 1;
  height: 6px;
  background: #f1f0f2;
  border-radius: 4px;
  overflow: hidden;
}

.vuexy-proj-fill {
  height: 100%;
  border-radius: 4px;
}

.vuexy-proj-pct {
  font-size: 12px;
  font-weight: 600;
  color: #5d596c;
}

/* Row 4: Last Transactions & Timeline */
.vuexy-row-4 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}
@media (max-width: 900px) {
  .vuexy-row-4 {
    grid-template-columns: 1fr;
  }
}

.vuexy-tx-table {
  width: 100%;
  border-collapse: collapse;
}

.vuexy-tx-table th {
  font-size: 11.5px;
  font-weight: 600;
  color: #a8aaae;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 8px 12px;
  border-bottom: 1px solid #dbdade;
  text-align: left;
}

.vuexy-tx-table td {
  padding: 12px 12px;
  border-bottom: 1px solid #f1f0f2;
  font-size: 13px;
}

.vuexy-card-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 700;
  background: #f1f0f2;
  color: #4b465c;
}
.vuexy-card-badge.visa { background: #e8f0fe; color: #1a73e8; }
.vuexy-card-badge.mastercard { background: #fef0e8; color: #d93025; }
.vuexy-card-badge.amex { background: #e6f4ea; color: #137333; }

/* Timeline */
.vuexy-timeline {
  display: flex;
  flex-direction: column;
  gap: 16px;
  position: relative;
  padding-left: 10px;
}

.vuexy-tl-item {
  display: flex;
  gap: 14px;
  position: relative;
}

.vuexy-tl-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  margin-top: 4px;
  flex-shrink: 0;
  box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.2);
}

.vuexy-tl-content {
  flex: 1;
}

.vuexy-tl-title {
  font-size: 13.5px;
  font-weight: 600;
  color: #4b465c;
}

.vuexy-tl-time {
  font-size: 11.5px;
  color: #a8aaae;
}

.vuexy-tl-desc {
  font-size: 12px;
  color: #82808c;
  margin: 2px 0 6px 0;
}

.vuexy-tl-file {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  background: #f8f7fa;
  border-radius: 4px;
  border: 1px solid #dbdade;
  font-size: 12px;
}

.vuexy-tl-user {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 6px;
}

.vuexy-tl-avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  font-size: 11px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
}

.vuexy-tl-avatars {
  display: flex;
  align-items: center;
  margin-top: 6px;
}

.vuexy-av {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #fff;
  margin-left: -6px;
}
.vuexy-av:first-child { margin-left: 0; }
.vuexy-av-count {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: #f1f0f2;
  color: #5d596c;
  font-size: 10px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #fff;
  margin-left: -6px;
}

/* Existing Organic CRM Dashboard Styles */
body.theme-template-vuexy .dashboard {
  font-family: 'Public Sans', 'Inter', -apple-system, sans-serif !important;
  color: #5d596c !important;
}

body.theme-template-organic .dashboard {
  font-family: 'Outfit', 'Inter', -apple-system, sans-serif;
  font-size: 14px;
  color: var(--theme-text-dark, #5f4f8d);
}
.loading-wrap { text-align: center; padding: 80px 40px; color: var(--theme-text-dark, #5f4f8d); display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 15px; opacity: 0.7; }
.loader { width: 24px; height: 24px; border: 3px solid rgba(163, 149, 127, 0.12); border-top-color: var(--theme-primary, #9f8ed6); border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Top Stats ──────────────────────────────────────────── */
.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 22px;
}
@media (max-width: 900px) { .stats-row { grid-template-columns: 1fr 1fr; } }
@media (max-width: 560px) { .stats-row { grid-template-columns: 1fr; } }

.stat-card {
  background: #ffffff !important;
  border: 1px solid #ebe9f1;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 9px rgba(47, 43, 61, 0.06) !important;
  transition: all 0.25s ease;
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(47, 43, 61, 0.09) !important;
}
.stat-card-inner {
  padding: 22px 20px 16px;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
}
.stat-icon-wrap {
  color: var(--theme-text-dark, #5f4f8d);
  opacity: 0.65;
  display: flex;
  align-items: center;
}
.stat-icon-wrap :deep(svg) { width: 22px; height: 22px; }
.stat-label {
  font-size: 13px;
  font-weight: 700;
  color: var(--theme-text-dark, #5f4f8d);
  opacity: 0.8;
  flex: 1;
  min-width: 100px;
}
.stat-value {
  font-size: 20px;
  font-weight: 800;
  color: var(--theme-text-dark, #5f4f8d);
  white-space: nowrap;
}
.stat-bar-wrap {
  height: 6px;
  background: #ffffff;
  border-radius: 0;
  box-shadow: inset 1px 1px 3px rgba(100, 90, 130, 0.06);
}
.stat-bar {
  height: 100%;
  border-radius: 0;
  transition: width 1s ease;
}
.bar-red   { background: #d67b74; }
.bar-green { background: #579b82; }
.bar-blue  { background: #6ca0cc; }
.bar-dark  { background: var(--theme-primary, #9f8ed6); }

/* ── Dashboard Two-Column Grid ────────────────────────── */
.dashboard-grid {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 20px;
  align-items: start;
}
.grid-right > .card:last-child { margin-bottom: 0; }
@media (max-width: 1000px) { .dashboard-grid { grid-template-columns: 1fr; } }

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  border-bottom: 1px solid rgba(163, 149, 127, 0.12);
  padding-bottom: 10px;
}
.panel-title {
  font-size: 14.5px;
  font-weight: 800;
  color: var(--theme-text-dark, #5f4f8d);
  margin: 0;
}

/* ── Left Column Elements ────────────────────────────── */

/* ── Overview Cards ────────────────────────────────────────────── */
.overviews-wrapper {
  position: relative;
  border-radius: 24px;
  overflow: visible;
  margin-bottom: 20px;
}

.overviews-panel {
  padding: 0;
  background: transparent;
  border: none;
  position: relative;
  z-index: 1;
}

.overview-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  position: relative;
  z-index: 1;
}
@media (max-width: 900px) { .overview-grid { grid-template-columns: 1fr; } }

/* ── Individual White Card ─────────────────────────────────────── */
.overview-col {
  padding: 24px 20px;
  border-radius: 10px;
  border: 1px solid #ebe9f1;
  background: #ffffff !important;
  position: relative;
  overflow: hidden;
  transition: all 0.25s ease;
  box-shadow: 0 2px 9px rgba(47, 43, 61, 0.06) !important;
}
.overview-col > * { position: relative; z-index: 3; }

.overview-col:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(47, 43, 61, 0.09) !important;
}

/* Title inside glass */
.overview-title {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 14px;
  background: #f8f7fa;
  border: 1px solid #ebe9f1;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 700;
  color: #4b465c;
  margin: 0 0 16px;
  transition: all 0.25s ease;
}
.overview-title:hover {
  color: var(--vuexy-primary, #7367f0);
}
.overview-title svg { opacity: 0.8; }

.overview-rows { display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; }
.overview-row-label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}
.ov-count      { font-size: 12.5px; font-weight: 700; color: var(--theme-text-dark, #5f4f8d); }
.ov-pct        { font-size: 12px; color: var(--theme-text-dark, #5f4f8d); opacity: 0.6; }
.ov-bar-track  { height: 8px; background: #ffffff; border-radius: 4px; overflow: hidden; box-shadow: inset 1px 1px 3px rgba(100, 90, 130, 0.1); }
.ov-bar        { height: 100%; border-radius: 4px; transition: width 0.8s ease; }
.ov-bar-red    { background: #d67b74; }
.ov-bar-green  { background: #579b82; }
.ov-bar-blue   { background: #6ca0cc; }
.ov-bar-orange { background: #ecd278; }
.ov-bar-slate  { background: var(--theme-primary, #9f8ed6); }

.overview-legend { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
.overview-legend-item {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 11.5px;
  font-weight: 600;
  color: #5d596c;
  position: relative;
  overflow: hidden;
  border: 1px solid #ebe9f1;
  background: #f8f7fa !important;
  transition: all 0.2s ease;
  cursor: pointer;
}

.overview-legend-item:hover {
  background: #ffffff !important;
  color: var(--vuexy-primary, #7367f0);
}

/* Color Modifier Classes */
.ov-btn-grey { background: #f8f7fa !important; }
.ov-btn-red { background: #f8f7fa !important; }
.ov-btn-green { background: #f8f7fa !important; }
.ov-btn-orange { background: #f8f7fa !important; }
.ov-btn-blue { background: #f8f7fa !important; }

.overview-legend-dot {
  width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
  box-shadow: inset 1px 1px 2px rgba(255, 255, 255, 0.4);
}
.overview-legend-label { font-weight: 700; }
.overview-legend-pct { color: var(--theme-text-dark, #5f4f8d); opacity: 0.6; font-size: 11px; font-weight: 600; }

.text-danger  { color: #d67b74 !important; }
.text-success { color: #579b82 !important; }
.text-warning { color: #ecd278 !important; }
.text-info    { color: #6ca0cc !important; }
.text-muted   { color: var(--theme-text-dark, #5f4f8d) !important; opacity: 0.5; }

.overview-footer {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  border-top: 1px solid rgba(163, 149, 127, 0.12);
  padding-top: 16px;
  margin-top: 16px;
}
.overview-footer > div {
  background: #ffffff;
  border: none;
  border-radius: 16px;
  padding: 8px 10px;
  text-align: center;
  box-shadow: 
    inset 2px 2px 4px rgba(100, 90, 130, 0.08),
    inset -2px -2px 4px rgba(255, 255, 255, 0.95);
  transition: all 0.3s cubic-bezier(.22,1,.36,1);
}
.overview-footer > div:hover {
  transform: translateY(-1px);
}
.ov-foot-label {
  font-size: 11px;
  color: var(--theme-text-dark, #5f4f8d);
  opacity: 0.6;
  font-weight: 700;
  margin-bottom: 3px;
}
.ov-foot-value {
  font-size: 13.5px;
  font-weight: 800;
  color: var(--theme-text-dark, #5f4f8d);
}

/* Tabs Panel */
.tabs-panel { padding: 0; overflow: hidden; }
.tabs-header {
  display: flex;
  background: rgba(163, 149, 127, 0.04);
  border-bottom: 1px solid rgba(163, 149, 127, 0.12);
  padding: 8px 12px;
  gap: 8px;
}
.tab-btn {
  background: none;
  border: none;
  padding: 8px 18px;
  font-size: 13px;
  font-weight: 700;
  color: var(--theme-text-dark, #5f4f8d);
  opacity: 0.7;
  cursor: pointer;
  outline: none;
  font-family: inherit;
  transition: all 0.2s ease;
  border-radius: 999px;
}
.tab-btn:hover { background: rgba(188, 179, 226, 0.12); opacity: 1; }
.tab-btn--active {
  background: #ffffff !important;
  color: var(--theme-text-dark, #5f4f8d) !important;
  opacity: 1;
  box-shadow: 
    inset 2px 2px 4px rgba(100, 90, 130, 0.12),
    inset -2px -2px 4px rgba(255, 255, 255, 0.95),
    1px 2px 4px rgba(100, 90, 130, 0.05);
}
.tabs-content { padding: 16px; }

/* Table styling */
.table-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}
.toolbar-left, .toolbar-right { display: flex; gap: 10px; }
.select-sm { 
  border: none !important; 
  border-radius: 999px !important; 
  padding: 6px 12px !important; 
  font-size: 12.5px; 
  color: var(--theme-text-dark, #5f4f8d) !important; 
  background: #ffffff !important; 
  outline: none; 
  box-shadow: 
    inset 2px 2px 5px rgba(100, 90, 130, 0.12),
    inset -2px -2px 5px rgba(255, 255, 255, 0.95);
  font-weight: 600;
}
.input-sm { 
  border: none !important; 
  border-radius: 999px !important; 
  padding: 7px 14px !important; 
  font-size: 12.5px; 
  color: var(--theme-text-dark, #5f4f8d) !important; 
  background: #ffffff !important;
  outline: none; 
  width: 220px; 
  box-shadow: 
    inset 2px 2px 5px rgba(100, 90, 130, 0.12),
    inset -2px -2px 5px rgba(255, 255, 255, 0.95);
  font-weight: 600;
}
.input-sm::placeholder { color: var(--theme-text-dark, #5f4f8d); opacity: 0.45; }
.input-sm:focus, .select-sm:focus { 
  box-shadow: 
    inset 2px 2px 5px rgba(100, 90, 130, 0.15),
    inset -2px -2px 5px rgba(255, 255, 255, 0.95),
    0 0 0 3px rgba(159, 142, 214, 0.3) !important;
}

.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table th { 
  background: rgba(163, 149, 127, 0.04); 
  padding: 12px 16px; 
  text-align: left; 
  font-size: 11.5px; 
  font-weight: 800; 
  color: var(--theme-text-dark, #5f4f8d); 
  opacity: 0.85;
  text-transform: uppercase; 
  letter-spacing: .06em; 
  white-space: nowrap; 
  border-bottom: 2px solid rgba(163, 149, 127, 0.12); 
}
.data-table td { 
  padding: 12px 16px; 
  border-bottom: 1px solid rgba(163, 149, 127, 0.08); 
  vertical-align: middle; 
  color: var(--theme-text-dark, #5f4f8d);
  font-weight: 600;
}
.data-table tbody tr:hover { background: rgba(188, 179, 226, 0.06); }
.link-blue { 
  color: var(--theme-primary, #9f8ed6); 
  font-weight: 700; 
  cursor: pointer; 
  text-decoration: none; 
}
.link-blue:hover { text-decoration: underline; color: var(--theme-primary-hover, #8d7bc8); }
.badge { 
  display: inline-block; 
  padding: 4px 10px; 
  border-radius: 999px; 
  font-size: 11px; 
  font-weight: 700; 
  text-transform: capitalize; 
  box-shadow: inset 1px 1px 2px rgba(255, 255, 255, 0.4);
}
.badge-blue { background: #dbeafe; color: #1d4ed8; }
.badge-green { background: #dcfce7; color: #15803d; }
.badge-red { background: #fee2e2; color: #b91c1c; }
.badge-yellow { background: #fef9c3; color: #854d0e; }
.badge-default { background: rgba(163, 149, 127, 0.12); color: var(--theme-text-dark, #5f4f8d); }
.empty-cell { text-align: center; color: var(--theme-text-dark, #5f4f8d); opacity: 0.5; padding: 30px 10px; }

/* Calendar Widget */
.calendar-header {
  display: flex;
  align-items: center;
  margin-bottom: 12px;
  gap: 10px;
}
.cal-nav-btn {
  background: #faf6f0;
  border: none;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--theme-text-dark, #5f4f8d);
  box-shadow: 
    2px 2px 4px rgba(163, 149, 127, 0.15),
    -2px -2px 4px rgba(255, 255, 255, 0.8),
    inset 1px 1px 2px rgba(255, 255, 255, 0.5);
  transition: all 0.2s ease;
}
.cal-nav-btn:hover { color: var(--theme-primary, #9f8ed6); transform: scale(1.05); }
.cal-nav-btn:active { box-shadow: inset 1.5px 1.5px 3px rgba(100, 90, 130, 0.15); }
.calendar-title { font-size: 16px; font-weight: 800; color: var(--theme-text-dark, #5f4f8d); margin: 0; }
.cal-view-modes { margin-left: auto; display: flex; gap: 6px; }
.btn-outline {
  background: #faf6f0; 
  border: none; 
  border-radius: 999px;
  padding: 6px 14px; 
  font-size: 12.5px; 
  color: var(--theme-text-dark, #5f4f8d); 
  font-weight: 700;
  cursor: pointer; 
  font-family: inherit;
  box-shadow: 
    2px 2px 5px rgba(163, 149, 127, 0.15),
    -2px -2px 5px rgba(255, 255, 255, 0.9),
    inset 1px 1px 2px rgba(255, 255, 255, 0.5);
  transition: all 0.2s ease;
}
.btn-outline.active, .btn-outline:hover { 
  color: var(--theme-primary, #9f8ed6); 
  transform: scale(1.02);
}

.calendar-weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  text-align: center;
  font-weight: 700;
  color: var(--theme-text-dark, #5f4f8d);
  opacity: 0.85;
  font-size: 12px;
  margin-bottom: 8px;
  border-bottom: 1px solid rgba(163, 149, 127, 0.12);
  padding-bottom: 8px;
}
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 3px;
  background: rgba(163, 149, 127, 0.12);
  border: 2px solid rgba(163, 149, 127, 0.1);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: inset 1px 1px 4px rgba(100, 90, 130, 0.08);
}
.calendar-day {
  background: #fff;
  min-height: 90px;
  padding: 6px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.calendar-day--other { background: rgba(163, 149, 127, 0.04); color: var(--theme-text-dark, #5f4f8d); opacity: 0.5; }
.calendar-day--today { background: rgba(159, 142, 214, 0.12); border: 2px solid var(--theme-primary, #9f8ed6); }
.calendar-day--today .day-num { color: var(--theme-primary, #9f8ed6); opacity: 1; }
.calendar-grid--week { grid-template-columns: repeat(7, 1fr); }
.calendar-grid--day { grid-template-columns: 1fr; }
.calendar-grid--day .calendar-day { min-height: 120px; padding: 12px; }
.day-num { font-size: 12px; font-weight: 700; color: var(--theme-text-dark, #5f4f8d); opacity: 0.7; }
.day-events { display: flex; flex-direction: column; gap: 3px; }
.cal-event {
  font-size: 10.5px;
  padding: 3px 6px;
  border-radius: 999px;
  color: #fff;
  font-weight: 700;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  box-shadow: inset 1px 1px 2px rgba(255, 255, 255, 0.3);
}
.cal-event--blue   { background: #6ca0cc; }
.cal-event--red    { background: #d67b74; }
.cal-event--green  { background: #579b82; }
.cal-event--purple { background: var(--theme-primary, #9f8ed6); }
.cal-event--orange { background: #ecd278; }

/* Payment Records Chart */
.payment-records-panel :deep(.apexcharts-legend-text) { font-size: 13px !important; color: var(--theme-text-dark, #5f4f8d) !important; }

/* Staff Ticket Report */
.staff-report-panel {}

/* ── Right Column Sidebar Elements ────────────────────────── */

/* Card styling override */
.card {
  background: #ffffff !important;
  border: 1px solid #ebe9f1;
  border-radius: 10px;
  padding: 24px 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 9px rgba(47, 43, 61, 0.06) !important;
  transition: all 0.25s ease;
}
.card:hover {
  box-shadow: 0 4px 16px rgba(47, 43, 61, 0.09) !important;
}

/* To Do List */
.todo-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 8px; }
.todo-title { font-size: 14.5px; font-weight: 800; color: var(--theme-text-dark, #5f4f8d); margin: 0; display: flex; align-items: center; gap: 6px; }
.todo-title :deep(svg) { width: 18px; height: 18px; }
.todo-header-actions { display: flex; align-items: center; gap: 10px; }
.todo-link { font-size: 12.5px; color: var(--theme-primary, #9f8ed6); cursor: pointer; text-decoration: none; font-weight: 700; }
.todo-btn { font-size: 12px; font-weight: 700; color: var(--theme-primary, #9f8ed6); cursor: pointer; }
.todo-section-label { font-size: 12px; font-weight: 800; margin: 10px 0 6px; padding: 3px 0; text-transform: uppercase; letter-spacing: 0.05em; }
.todo-section-label--pending { color: #ecd278; }
.todo-section-label--done    { color: #579b82; }
.todo-list { display: flex; flex-direction: column; gap: 6px; }
.todo-list > div { width: 100%; }
.todo-item { display: flex; align-items: flex-start; gap: 8px; padding: 8px 10px; border-radius: 12px; background: #ffffff; box-shadow: inset 1px 1px 3px rgba(100, 90, 130, 0.05); transition: all 0.2s; }
.todo-item:hover { background: rgba(188, 179, 226, 0.08); }
.todo-item--done { opacity: 0.75; }
.todo-drag { color: var(--theme-text-dark, #5f4f8d); opacity: 0.4; font-size: 16px; cursor: grab; line-height: 1.4; }
.todo-check-wrap { padding-top: 2px; }
.todo-checkbox { width: 15px; height: 15px; cursor: pointer; accent-color: var(--theme-primary, #9f8ed6); }
.todo-text-wrap { flex: 1; min-width: 0; overflow: visible; }
.todo-text { font-size: 13px; color: var(--theme-text-dark, #5f4f8d); line-height: 1.4; font-weight: 600; display: block; }
.todo-text--done { text-decoration: line-through; color: var(--theme-text-dark, #5f4f8d); opacity: 0.55; }
.todo-date { font-size: 11px; color: var(--theme-text-dark, #5f4f8d); opacity: 0.5; margin-top: 3px; font-weight: 700; }
.todo-actions { display: flex; gap: 4px; opacity: 0; transition: opacity 0.12s; flex-shrink: 0; }
.todo-item:hover .todo-actions { opacity: 1; }
@media (hover: none) { .todo-actions { opacity: 0.6; } .todo-item:active .todo-actions { opacity: 1; } }
.todo-action-btn { background: none; border: none; cursor: pointer; color: var(--theme-text-dark, #5f4f8d); opacity: 0.6; font-size: 15px; padding: 3px 5px; border-radius: 4px; line-height: 1; }
.todo-action-btn:hover { background: rgba(188, 179, 226, 0.12); opacity: 1; }
.todo-action-btn--del:hover { color: #d67b74; }

.todo-add-form { padding: 8px 10px; margin-bottom: 8px; background: #ffffff; border-radius: 12px; box-shadow: inset 1px 1px 3px rgba(100, 90, 130, 0.05); }
.todo-add-input {
  width: 100%; border: none; outline: none; font-size: 13px; font-weight: 600;
  color: var(--theme-text-dark, #5f4f8d); background: transparent; font-family: inherit;
  padding: 4px 0; border-bottom: 2px solid rgba(159, 142, 214, 0.3);
}
.todo-add-input:focus { border-bottom-color: var(--theme-primary, #9f8ed6); }
.todo-add-actions { display: flex; gap: 6px; margin-top: 8px; justify-content: flex-end; }
.todo-edit-input {
  width: 100%; border: none; outline: none; font-size: 13px; font-weight: 600;
  color: var(--theme-text-dark, #5f4f8d); background: transparent; font-family: inherit;
  padding: 2px 4px; border-radius: 4px; background: rgba(159, 142, 214, 0.08);
}
.todo-ghost { opacity: 0.4; background: rgba(159, 142, 214, 0.1); }

/* Donut Charts - Clay Cards */
.donut-card {
  padding: 24px 20px;
  background: #faf6f0;
  border: 1px solid rgba(255, 255, 255, 0.6);
  border-radius: 24px;
  margin-bottom: 16px;
  position: relative;
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 
    12px 12px 24px var(--shadow-dark-rgb),
    -12px -12px 24px var(--shadow-light-rgb),
    inset 2px 2px 4px rgba(255, 255, 255, 0.5);
}

.donut-card > * {
  position: relative;
  z-index: 3;
}

/* Color thematic indicators */
.donut-card:nth-of-type(1)::before {
  background: radial-gradient(circle, rgba(108, 160, 204, 0.35) 0%, rgba(108, 160, 204, 0) 70%); /* Sky blue leads */
}
.donut-card:nth-of-type(2)::before {
  background: radial-gradient(circle, rgba(159, 142, 214, 0.32) 0%, rgba(159, 142, 214, 0) 70%); /* Purple projects */
}
.donut-card:nth-of-type(3)::before {
  background: radial-gradient(circle, rgba(214, 123, 116, 0.32) 0%, rgba(214, 123, 116, 0) 70%); /* Peach tickets */
}
.donut-card:nth-of-type(4)::before {
  background: radial-gradient(circle, rgba(87, 155, 130, 0.35) 0%, rgba(87, 155, 130, 0) 70%); /* Mint departments */
}

/* Hover scales card */
.donut-card {
  padding: 10px 12px;
  border-radius: 20px;
  background: #faf6f0;
  border: 1px solid rgba(255, 255, 255, 0.7);
  box-shadow: none !important;
  transition: all 0.25s ease;
}
.donut-card:hover {
  transform: translateY(-2px);
  box-shadow: none !important;
}

.donut-card :deep(.apexcharts-legend) { gap: 6px; }
.donut-card :deep(.apexcharts-legend-text) { font-size: 13px !important; color: var(--theme-text-dark, #5f4f8d) !important; }
.donut-card :deep(.apexcharts-datalabel) { font-size: 13px; font-weight: 600; color: var(--theme-text-dark, #5f4f8d) !important; }

.donut-card :deep(.vue-apexcharts),
.chart-card :deep(.vue-apexcharts),
.progress-card :deep(.vue-apexcharts),
.overview-col :deep(.vue-apexcharts) {
  display: flex !important;
  justify-content: center !important;
  align-items: center !important;
  width: 100% !important;
}

.donut-card :deep(.apexcharts-canvas),
.chart-card :deep(.apexcharts-canvas),
.progress-card :deep(.apexcharts-canvas),
.overview-col :deep(.apexcharts-canvas) {
  margin: 0 auto !important;
  display: block !important;
}

/* Neomorphic Action Buttons */
.overview-action-btn-wrap {
  margin-top: 14px;
  position: relative;
  border-radius: 999px;
  padding: 0;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  display: block;
}

.overview-action-btn-wrap:hover {
  transform: translateY(-1px);
}

.btn-liquid-glass {
  position: relative;
  display: block;
  width: 100%;
  padding: 8px 16px;
  border-radius: 999px;
  font-family: inherit;
  font-size: 12.5px;
  font-weight: 700;
  text-align: center;
  color: var(--theme-text-dark, #5f4f8d);
  cursor: pointer;
  outline: none;
  border: none;
  background: #faf6f0;
  transition: all 0.25s ease;
  text-decoration: none;
  box-shadow: 
    3px 3px 6px rgba(163, 149, 127, 0.15),
    -3px -3px 6px rgba(255, 255, 255, 0.9),
    inset 1px 1px 2px rgba(255, 255, 255, 0.5);
}
.btn-liquid-glass:hover {
  color: var(--theme-primary, #9f8ed6);
}
.btn-liquid-glass:active {
  transform: translateY(1px);
  box-shadow: inset 2px 2px 4px rgba(100, 90, 130, 0.15);
}

/* Project Progress Card */
.progress-card { padding: 10px 12px; }
.progress-card :deep(.apexcharts-legend-text) { font-size: 13px !important; color: var(--theme-text-dark, #5f4f8d) !important; }

/* Generic Chart Card */
.chart-card { padding: 10px 12px; }

/* ── Charts Section (full-width below grid) ──────────── */
.charts-section {
  margin-top: 10px;
}
.charts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
}
@media (min-width: 1200px) {
  .charts-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  .progress-card {
    grid-column: 1 / -1;
  }
}
@media (min-width: 1400px) {
  .charts-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

/* ── This Week Events ──────────────────────────────── */
.events-list { display: flex; flex-direction: column; gap: 12px; }
.event-item { display: flex; gap: 14px; align-items: flex-start; }
.event-date-badge {
  display: flex; flex-direction: column; align-items: center;
  background: #ffffff; border-radius: 12px; padding: 6px 10px;
  min-width: 44px; flex-shrink: 0;
  box-shadow: inset 1px 1px 3px rgba(100, 90, 130, 0.08);
}
.event-date-day { font-size: 16px; font-weight: 800; color: var(--theme-text-dark, #5f4f8d); line-height: 1.1; }
.event-date-month { font-size: 10px; font-weight: 700; color: var(--theme-text-dark, #5f4f8d); opacity: 0.6; text-transform: uppercase; }
.event-info { flex: 1; min-width: 0; }
.event-title { font-size: 13px; font-weight: 700; color: var(--theme-text-dark, #5f4f8d); line-height: 1.3; }
.event-meta { font-size: 12px; color: var(--theme-text-dark, #5f4f8d); opacity: 0.55; margin-top: 2px; font-weight: 600; }

/* ── Contracts Panel ───────────────────────────────── */
.contracts-panel {}

/* ── Activity Feed (Latest Activity tab) ───────────── */
.activity-feed { display: flex; flex-direction: column; gap: 12px; }
.activity-item { display: flex; gap: 10px; align-items: flex-start; }
.activity-dot {
  width: 8px; height: 8px; border-radius: 50%; margin-top: 6px; flex-shrink: 0;
  box-shadow: inset 1px 1px 2px rgba(255, 255, 255, 0.4);
}
.dot-blue { background: #6ca0cc; }
.dot-green { background: #579b82; }
.dot-purple { background: var(--theme-primary, #9f8ed6); }
.dot-orange { background: #ecd278; }
.activity-content { flex: 1; min-width: 0; }
.activity-text { font-size: 13px; color: var(--theme-text-dark, #5f4f8d); line-height: 1.4; font-weight: 600; }
.activity-project { font-size: 12px; color: var(--theme-text-dark, #5f4f8d); opacity: 0.6; }
.activity-time { font-size: 11.5px; color: var(--theme-text-dark, #5f4f8d); opacity: 0.5; margin-top: 2px; }

/* ── Bottom grid (Project Activity + Goals) ────────── */
.bottom-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-top: 16px;
}
@media (max-width: 900px) { .bottom-grid { grid-template-columns: 1fr; } }

/* Latest Project Activity */
.activity-panel {}
.activity-feed-list { display: flex; flex-direction: column; gap: 14px; max-height: 500px; overflow-y: auto; }
.feed-item { display: flex; gap: 12px; align-items: flex-start; }
.feed-dot {
  width: 10px; height: 10px; border-radius: 50%; margin-top: 5px; flex-shrink: 0;
  box-shadow: inset 1px 1px 2px rgba(255, 255, 255, 0.4);
}
.feed-dot--blue { background: #6ca0cc; }
.feed-dot--green { background: #579b82; }
.feed-dot--purple { background: var(--theme-primary, #9f8ed6); }
.feed-dot--orange { background: #ecd278; }
.feed-content { flex: 1; min-width: 0; }
.feed-time { font-size: 11px; color: var(--theme-text-dark, #5f4f8d); opacity: 0.5; font-weight: 700; }
.feed-text { font-size: 13px; color: var(--theme-text-dark, #5f4f8d); line-height: 1.4; margin-top: 2px; font-weight: 600; }
.feed-detail { font-size: 12px; color: var(--theme-text-dark, #5f4f8d); opacity: 0.6; margin-top: 1px; padding-left: 0; }
.feed-project { font-size: 11.5px; color: var(--theme-primary, #9f8ed6); font-weight: 700; margin-top: 2px; }

/* Goals */
.goals-panel { max-height: 540px; overflow-y: auto; }
.goals-list { display: flex; flex-direction: column; gap: 14px; }
.goal-item {
  padding: 12px 14px; border: none; border-radius: 16px;
  background: #ffffff;
  box-shadow: 
    3px 3px 6px rgba(163, 149, 127, 0.12),
    -3px -3px 6px rgba(255, 255, 255, 0.8),
    inset 1px 1px 2px rgba(255, 255, 255, 0.5);
  display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;
  transition: all 0.2s ease;
}
.goal-item:hover { transform: translateY(-1px); }
.goal-info { flex: 1; min-width: 0; }
.goal-title { font-size: 13.5px; font-weight: 700; color: var(--theme-text-dark, #5f4f8d); }
.goal-subtitle { font-size: 11.5px; color: var(--theme-text-dark, #5f4f8d); opacity: 0.5; margin-top: 2px; }
.goal-metrics { text-align: right; flex-shrink: 0; }
.goal-achieved { font-size: 16px; font-weight: 800; color: var(--theme-text-dark, #5f4f8d); }
.goal-progress-wrap { display: flex; align-items: center; gap: 6px; margin-top: 4px; justify-content: flex-end; }
.goal-progress-track { width: 60px; height: 6px; background: #faf6f0; border-radius: 3px; overflow: hidden; box-shadow: inset 1px 1px 2px rgba(100, 90, 130, 0.1); }
.goal-progress-fill { height: 100%; border-radius: 3px; transition: width 0.5s ease; background: var(--theme-primary, #9f8ed6); }
.goal-progress-label { font-size: 11px; font-weight: 800; color: var(--theme-text-dark, #5f4f8d); }

/* ── Responsive ───────────────────────────────────────────── */
@media (max-width: 640px) {
  .tabs-header {
    flex-wrap: wrap;
  }
  .tab-btn {
    padding: 6px 12px;
    font-size: 12px;
  }
  .table-toolbar {
    flex-wrap: wrap;
    gap: 8px;
  }
  .input-sm {
    width: 100%;
    max-width: 200px;
  }
  .calendar-header {
    flex-wrap: wrap;
    gap: 8px;
  }
  .cal-view-modes {
    margin-left: 0;
    width: 100%;
    justify-content: center;
    margin-top: 4px;
  }
  .calendar-weekdays {
    display: none;
  }
  .calendar-day {
    min-height: 44px;
    padding: 4px;
  }
  .day-num {
    font-size: 10px;
  }
  .cal-event {
    font-size: 9px;
    padding: 2px 4px;
  }
  .overview-footer {
    grid-template-columns: 1fr;
    gap: 8px;
  }
  .goal-item {
    flex-direction: column;
    align-items: flex-start;
  }
  .goal-metrics {
    text-align: left;
    width: 100%;
  }
  .goal-progress-wrap {
    justify-content: flex-start;
  }
}
.table-wrap {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  width: 100% !important;
  margin: 0 !important;
  padding: 0 !important;
}
.table-wrap::-webkit-scrollbar { height: 6px; }
.table-wrap::-webkit-scrollbar-track { background: transparent; }
.table-wrap::-webkit-scrollbar-thumb { background: rgba(163, 149, 127, 0.2); border-radius: 3px; }

.contracts-panel, .tickets-panel {
  width: 100% !important;
  max-width: 100% !important;
  flex: 1 1 100% !important;
}

.contracts-panel .data-table, .tickets-panel .data-table {
  width: 100% !important;
  min-width: 100% !important;
  table-layout: fixed !important;
}

.contracts-panel .data-table td, .contracts-panel .data-table th,
.tickets-panel .data-table td, .tickets-panel .data-table th {
  padding: 10px 12px !important;
}

/* Mobile Cards List Hidden by Default on Desktop */
.mobile-cards-list {
  display: none;
}

@media (max-width: 992px) {
  .contracts-panel, .tickets-panel {
    width: 100% !important;
    max-width: 100% !important;
  }
  .table-wrap {
    display: none !important;
  }
  .mobile-cards-list {
    display: flex !important;
    flex-direction: column;
    gap: 12px;
    padding: 12px 0;
    width: 100% !important;
  }
  .mobile-row-card {
    width: 100% !important;
  }
}
</style>
