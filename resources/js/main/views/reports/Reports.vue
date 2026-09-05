<template>
  <div class="reports-page p-6 max-w-[1600px] mx-auto min-h-screen bg-[#F8F7FA] font-['Public_Sans',sans-serif]">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2">
          <span class="p-2 bg-[#7367F0]/10 text-[#7367F0] rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
          </span>
          <div>
            <h1 class="text-2xl font-bold text-[#4B465C] tracking-tight m-0">Reports &amp; Analytics</h1>
            <span class="text-xs text-[#82868B] font-medium">Business insights, conversion funnels &amp; performance metrics</span>
          </div>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-[#EBE9F1] shadow-sm">
          <span class="text-xs font-semibold text-[#82868B]">Fiscal Year:</span>
          <select v-model="selectedYear" @change="loadAll" class="border-0 bg-transparent text-sm font-bold text-[#4B465C] focus:outline-none cursor-pointer">
            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>
        <button class="flex items-center gap-2 px-4 py-2 bg-white border border-[#DBDADE] hover:bg-[#F8F7FA] text-[#4B465C] rounded-lg text-sm font-semibold transition-all shadow-sm" @click="exportReport">
          <svg class="w-4 h-4 text-[#7367F0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
          Export Report
        </button>
      </div>
    </div>

    <!-- Finance Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6" v-if="finance">
      <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm transition-all hover:shadow-md flex items-center justify-between border-l-4 border-l-[#28C76F]">
        <div>
          <div class="text-[11px] font-bold uppercase tracking-wider text-[#82868B] mb-1">Total Income</div>
          <div class="text-2xl font-bold text-[#4B465C]">${{ formatMoney(finance.income) }}</div>
          <div class="text-xs text-[#82868B] mt-1 font-medium">Invoiced in {{ selectedYear }}</div>
        </div>
        <div class="w-12 h-12 rounded-xl bg-[#28C76F]/10 text-[#28C76F] flex items-center justify-center font-bold text-lg">
          $
        </div>
      </div>

      <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm transition-all hover:shadow-md flex items-center justify-between border-l-4 border-l-[#EA5455]">
        <div>
          <div class="text-[11px] font-bold uppercase tracking-wider text-[#82868B] mb-1">Total Expenses</div>
          <div class="text-2xl font-bold text-[#4B465C]">${{ formatMoney(finance.expenses) }}</div>
          <div class="text-xs text-[#82868B] mt-1 font-medium">Spent in {{ selectedYear }}</div>
        </div>
        <div class="w-12 h-12 rounded-xl bg-[#EA5455]/10 text-[#EA5455] flex items-center justify-center font-bold text-lg">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
      </div>

      <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm transition-all hover:shadow-md flex items-center justify-between border-l-4 border-l-[#7367F0]">
        <div>
          <div class="text-[11px] font-bold uppercase tracking-wider text-[#82868B] mb-1">Payments Received</div>
          <div class="text-2xl font-bold text-[#4B465C]">${{ formatMoney(finance.payments) }}</div>
          <div class="text-xs text-[#82868B] mt-1 font-medium">Collected in {{ selectedYear }}</div>
        </div>
        <div class="w-12 h-12 rounded-xl bg-[#7367F0]/10 text-[#7367F0] flex items-center justify-center font-bold text-lg">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
      </div>

      <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm transition-all hover:shadow-md flex items-center justify-between border-l-4" :class="finance.profit >= 0 ? 'border-l-[#28C76F]' : 'border-l-[#EA5455]'">
        <div>
          <div class="text-[11px] font-bold uppercase tracking-wider text-[#82868B] mb-1">Net Profit</div>
          <div class="text-2xl font-bold" :class="finance.profit >= 0 ? 'text-[#28C76F]' : 'text-[#EA5455]'">${{ formatMoney(finance.profit) }}</div>
          <div class="text-xs text-[#82868B] mt-1 font-medium">{{ finance.profit >= 0 ? 'Profitable Operations' : 'Operating Deficit' }}</div>
        </div>
        <div class="w-12 h-12 rounded-xl flex items-center justify-center font-bold text-lg" :class="finance.profit >= 0 ? 'bg-[#28C76F]/10 text-[#28C76F]' : 'bg-[#EA5455]/10 text-[#EA5455]'">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs Bar -->
    <div class="bg-white border border-[#EBE9F1] rounded-lg p-1.5 mb-6 shadow-sm flex flex-wrap gap-1">
      <button 
        v-for="tab in tabs" 
        :key="tab.key" 
        class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all cursor-pointer"
        :class="activeTab === tab.key ? 'bg-[#7367F0] text-white shadow-sm shadow-[#7367F0]/40' : 'text-[#82868B] hover:text-[#4B465C] hover:bg-[#F8F7FA]'"
        @click="activeTab = tab.key"
      >
        <span>{{ tab.icon }}</span>
        <span>{{ tab.label }}</span>
      </button>
    </div>

    <!-- ========================================== -->
    <!-- 1. LEADS REPORT (PURE WHITE UPGRADED UI/UX) -->
    <!-- ========================================== -->
    <div v-if="activeTab === 'leads'" class="space-y-6">
      <!-- 5 Leads KPI Metric Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Card 1: Total Leads -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm transition-all hover:shadow-md flex items-center justify-between border-t-2 border-t-[#7367F0]">
          <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-[#82868B] mb-1">Total Leads</div>
            <div class="text-2xl font-bold text-[#4B465C]">{{ leadsTotals.total_leads }}</div>
            <div class="text-xs text-[#82868B] mt-1 font-medium">Registered in {{ selectedYear }}</div>
          </div>
          <div class="w-11 h-11 rounded-lg bg-[#7367F0]/10 text-[#7367F0] flex items-center justify-center font-bold">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
          </div>
        </div>

        <!-- Card 2: Converted to Customer -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm transition-all hover:shadow-md flex items-center justify-between border-t-2 border-t-[#28C76F]">
          <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-[#82868B] mb-1">Converted Leads</div>
            <div class="text-2xl font-bold text-[#28C76F]">{{ leadsTotals.total_converted }}</div>
            <div class="text-xs text-[#82868B] mt-1 font-medium">Converted to clients</div>
          </div>
          <div class="w-11 h-11 rounded-lg bg-[#28C76F]/10 text-[#28C76F] flex items-center justify-center font-bold">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
          </div>
        </div>

        <!-- Card 3: Conversion Rate -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm transition-all hover:shadow-md flex items-center justify-between border-t-2 border-t-[#00CFE8]">
          <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-[#82868B] mb-1">Conversion Rate</div>
            <div class="text-2xl font-bold text-[#00CFE8]">{{ leadsTotals.conversion_rate }}%</div>
            <div class="text-xs text-[#82868B] mt-1 font-medium">Conversion velocity</div>
          </div>
          <div class="w-11 h-11 rounded-lg bg-[#00CFE8]/10 text-[#00CFE8] flex items-center justify-center font-bold">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
          </div>
        </div>

        <!-- Card 4: Total Lead Value -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm transition-all hover:shadow-md flex items-center justify-between border-t-2 border-t-[#FF9F43]">
          <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-[#82868B] mb-1">Pipeline Value</div>
            <div class="text-2xl font-bold text-[#FF9F43]">${{ formatMoney(leadsTotals.total_value) }}</div>
            <div class="text-xs text-[#82868B] mt-1 font-medium">Potential deal volume</div>
          </div>
          <div class="w-11 h-11 rounded-lg bg-[#FF9F43]/10 text-[#FF9F43] flex items-center justify-center font-bold">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
        </div>

        <!-- Card 5: Sources Count -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm transition-all hover:shadow-md flex items-center justify-between border-t-2 border-t-[#EA5455]">
          <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-[#82868B] mb-1">Lead Channels</div>
            <div class="text-2xl font-bold text-[#EA5455]">{{ leadsTotals.sources_count }}</div>
            <div class="text-xs text-[#82868B] mt-1 font-medium">Active acquisition sources</div>
          </div>
          <div class="w-11 h-11 rounded-lg bg-[#EA5455]/10 text-[#EA5455] flex items-center justify-center font-bold">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
          </div>
        </div>
      </div>

      <!-- Charts Section (Monthly Trend + Sources Breakdown + Status Distribution) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Monthly Trend (2 Cols) -->
        <div class="lg:col-span-2 bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm">
          <div class="flex items-center justify-between mb-4 pb-3 border-b border-[#EBE9F1]">
            <div>
              <h3 class="text-base font-bold text-[#4B465C] m-0">Monthly Leads Created vs Converted</h3>
              <p class="text-xs text-[#82868B] m-0">Comparison of incoming lead volume and customer conversions in {{ selectedYear }}</p>
            </div>
            <div class="flex items-center gap-3 text-xs font-semibold">
              <span class="flex items-center gap-1.5 text-[#7367F0]"><span class="w-3 h-3 rounded-full bg-[#7367F0]"></span> Total Leads</span>
              <span class="flex items-center gap-1.5 text-[#28C76F]"><span class="w-3 h-3 rounded-full bg-[#28C76F]"></span> Converted</span>
            </div>
          </div>
          <div v-if="leadsLoading" class="flex items-center justify-center py-20 text-[#82868B] gap-2">
            <div class="w-5 h-5 border-2 border-[#7367F0] border-t-transparent rounded-full animate-spin"></div> Loading charts...
          </div>
          <div v-else>
            <VueApexCharts type="bar" height="320" :options="leadsChartOptions" :series="leadsChartSeries"></VueApexCharts>
          </div>
        </div>

        <!-- Right: Leads by Source (1 Col) -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm">
          <div class="flex items-center justify-between mb-4 pb-3 border-b border-[#EBE9F1]">
            <div>
              <h3 class="text-base font-bold text-[#4B465C] m-0">Leads by Source</h3>
              <p class="text-xs text-[#82868B] m-0">Channel acquisition distribution</p>
            </div>
          </div>
          <div v-if="leadsLoading" class="flex items-center justify-center py-20 text-[#82868B] gap-2">
            <div class="w-5 h-5 border-2 border-[#7367F0] border-t-transparent rounded-full animate-spin"></div> Loading...
          </div>
          <div v-else-if="leadsBySource.length">
            <VueApexCharts type="donut" height="240" :options="leadsSourceDonutOptions" :series="leadsSourceDonutSeries"></VueApexCharts>
            <div class="mt-4 space-y-2 max-h-[140px] overflow-y-auto pr-1">
              <div v-for="src in leadsBySource" :key="src.id" class="flex items-center justify-between text-xs py-1 px-2 rounded hover:bg-[#F8F7FA]">
                <span class="font-medium text-[#4B465C]">{{ src.name }}</span>
                <div class="flex items-center gap-2">
                  <span class="font-bold text-[#7367F0]">{{ src.count }} leads</span>
                  <span class="text-[10px] font-semibold bg-[#28C76F]/10 text-[#28C76F] px-1.5 py-0.5 rounded">{{ src.conversion_rate }}% conv</span>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-12 text-xs text-[#82868B]">No source data recorded</div>
        </div>
      </div>

      <!-- Month by Month Breakdown Table & Staff Leaderboard -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Monthly Performance Table (2 Cols) -->
        <div class="lg:col-span-2 bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm">
          <div class="flex items-center justify-between mb-4 pb-3 border-b border-[#EBE9F1]">
            <h3 class="text-base font-bold text-[#4B465C] m-0">Monthly Breakdown Performance</h3>
            <span class="text-xs text-[#82868B] font-medium">{{ selectedYear }} Full Calendar Year</span>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#F8F7FA] border-b border-[#EBE9F1] text-[11px] font-bold uppercase tracking-wider text-[#82868B]">
                  <th class="py-3 px-4">Month</th>
                  <th class="py-3 px-4">Leads Created</th>
                  <th class="py-3 px-4">Converted</th>
                  <th class="py-3 px-4">Conversion Rate</th>
                  <th class="py-3 px-4">Pipeline Value</th>
                  <th class="py-3 px-4">Progress Ratio</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EBE9F1] text-xs">
                <tr v-for="row in leadsData" :key="row.month" class="hover:bg-[#F8F7FA]/70 transition-colors">
                  <td class="py-3 px-4 font-bold text-[#4B465C]">{{ row.name || monthName(row.month) }}</td>
                  <td class="py-3 px-4 font-semibold text-[#7367F0]">{{ row.count }}</td>
                  <td class="py-3 px-4 font-semibold text-[#28C76F]">{{ row.converted }}</td>
                  <td class="py-3 px-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold" :class="row.conversion_rate >= 20 ? 'bg-[#28C76F]/10 text-[#28C76F]' : 'bg-[#FF9F43]/10 text-[#FF9F43]'">
                      {{ row.conversion_rate }}%
                    </span>
                  </td>
                  <td class="py-3 px-4 font-bold text-[#4B465C]">${{ formatMoney(row.value) }}</td>
                  <td class="py-3 px-4 min-w-[120px]">
                    <div class="w-full bg-[#EBE9F1] rounded-full h-2 overflow-hidden flex">
                      <div class="bg-[#7367F0] h-2" :style="{ width: compPct(row.count, Math.max(...leadsData.map(r => r.count), 1)) + '%' }"></div>
                    </div>
                  </td>
                </tr>
                <!-- Total Row -->
                <tr class="bg-[#F8F7FA] font-bold text-[#4B465C] border-t-2 border-[#DBDADE]">
                  <td class="py-3 px-4">Total ({{ selectedYear }})</td>
                  <td class="py-3 px-4 text-[#7367F0]">{{ leadsTotals.total_leads }}</td>
                  <td class="py-3 px-4 text-[#28C76F]">{{ leadsTotals.total_converted }}</td>
                  <td class="py-3 px-4 text-[#00CFE8]">{{ leadsTotals.conversion_rate }}%</td>
                  <td class="py-3 px-4 text-[#FF9F43]">${{ formatMoney(leadsTotals.total_value) }}</td>
                  <td class="py-3 px-4">100% Volume</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Staff / Agent Performance (1 Col) -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm">
          <div class="flex items-center justify-between mb-4 pb-3 border-b border-[#EBE9F1]">
            <h3 class="text-base font-bold text-[#4B465C] m-0">Staff Lead Conversion</h3>
            <span class="text-xs text-[#82868B]">By Assignee</span>
          </div>
          <div v-if="leadsByStaff.length" class="space-y-3">
            <div v-for="st in leadsByStaff" :key="st.id" class="p-3 rounded-lg border border-[#EBE9F1] hover:border-[#7367F0]/40 transition-colors">
              <div class="flex items-center justify-between mb-1.5">
                <span class="font-bold text-xs text-[#4B465C]">{{ st.name }}</span>
                <span class="text-[11px] font-semibold text-[#7367F0]">{{ st.count }} leads</span>
              </div>
              <div class="flex items-center justify-between text-[11px] text-[#82868B] mb-2">
                <span>Converted: <strong class="text-[#28C76F]">{{ st.converted }}</strong></span>
                <span>Rate: <strong class="text-[#00CFE8]">{{ st.conversion_rate }}%</strong></span>
                <span>Val: <strong class="text-[#FF9F43]">${{ formatMoney(st.value) }}</strong></span>
              </div>
              <div class="w-full bg-[#EBE9F1] rounded-full h-1.5 overflow-hidden">
                <div class="bg-[#28C76F] h-1.5 rounded-full" :style="{ width: st.conversion_rate + '%' }"></div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-12 text-xs text-[#82868B]">No staff assigned to leads yet</div>
        </div>
      </div>

      <!-- Detailed Leads Records Table with Filters -->
      <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4 pb-3 border-b border-[#EBE9F1]">
          <div>
            <h3 class="text-base font-bold text-[#4B465C] m-0">Leads Audit Log &amp; Detailed Explorer</h3>
            <p class="text-xs text-[#82868B] m-0">Filter and analyze individual lead records for {{ selectedYear }}</p>
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <input 
              v-model="leadsFilters.search" 
              type="text" 
              placeholder="Search leads..." 
              class="px-3 py-1.5 text-xs bg-white border border-[#DBDADE] rounded-md focus:outline-none focus:border-[#7367F0] w-48"
            />
            <select v-model="leadsFilters.status" class="px-3 py-1.5 text-xs bg-white border border-[#DBDADE] rounded-md focus:outline-none focus:border-[#7367F0] text-[#4B465C]">
              <option value="">All Statuses</option>
              <option v-for="st in leadsByStatus" :key="st.id" :value="st.name">{{ st.name }}</option>
            </select>
            <select v-model="leadsFilters.source" class="px-3 py-1.5 text-xs bg-white border border-[#DBDADE] rounded-md focus:outline-none focus:border-[#7367F0] text-[#4B465C]">
              <option value="">All Sources</option>
              <option v-for="src in leadsBySource" :key="src.id" :value="src.name">{{ src.name }}</option>
            </select>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-[#F8F7FA] border-b border-[#EBE9F1] text-[11px] font-bold uppercase tracking-wider text-[#82868B]">
                <th class="py-3 px-4">#</th>
                <th class="py-3 px-4">Lead Name &amp; Company</th>
                <th class="py-3 px-4">Contact Info</th>
                <th class="py-3 px-4">Lead Value</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4">Source</th>
                <th class="py-3 px-4">Assigned Staff</th>
                <th class="py-3 px-4">Date Added</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EBE9F1] text-xs">
              <tr v-for="(lead, idx) in filteredLeadsList" :key="lead.id" class="hover:bg-[#F8F7FA]/70 transition-colors">
                <td class="py-3 px-4 font-semibold text-[#82868B]">{{ idx + 1 }}</td>
                <td class="py-3 px-4">
                  <div class="font-bold text-[#4B465C]">{{ lead.name }}</div>
                  <div class="text-[11px] text-[#82868B]">{{ lead.company }}</div>
                </td>
                <td class="py-3 px-4">
                  <div class="text-[#4B465C]">{{ lead.email }}</div>
                  <div class="text-[11px] text-[#82868B]">{{ lead.phonenumber }}</div>
                </td>
                <td class="py-3 px-4 font-bold text-[#4B465C]">
                  ${{ formatMoney(lead.lead_value) }}
                </td>
                <td class="py-3 px-4">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold" :style="{ backgroundColor: (lead.status_color || '#7367F0') + '20', color: lead.status_color || '#7367F0' }">
                    {{ lead.status }}
                  </span>
                </td>
                <td class="py-3 px-4">
                  <span class="inline-flex items-center px-2 py-0.5 rounded bg-[#F8F7FA] border border-[#EBE9F1] text-[11px] font-semibold text-[#4B465C]">
                    {{ lead.source }}
                  </span>
                </td>
                <td class="py-3 px-4 font-medium text-[#4B465C]">
                  {{ lead.assigned }}
                </td>
                <td class="py-3 px-4 text-[#82868B]">
                  {{ lead.created_at }}
                </td>
              </tr>
              <tr v-if="!filteredLeadsList.length">
                <td colspan="8" class="text-center py-10 text-xs text-[#82868B]">No leads matching filter criteria</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- 2. SALES REPORT TAB                        -->
    <!-- ========================================== -->
    <div v-if="activeTab === 'sales'" class="bg-white border border-[#EBE9F1] rounded-lg p-6 shadow-sm">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-4 border-b border-[#EBE9F1]">
        <div>
          <h2 class="text-lg font-bold text-[#4B465C] m-0">Sales Report</h2>
          <span class="text-xs text-[#82868B]">Detailed breakdown across invoices, items, proposals and payments</span>
        </div>
        <div class="flex items-center gap-2">
          <select v-model="srPeriod" @change="loadSalesReport" class="px-3 py-1.5 text-xs bg-white border border-[#DBDADE] rounded-md focus:outline-none focus:border-[#7367F0] text-[#4B465C] font-medium">
            <option value="this_month">This Month</option>
            <option value="last_month">Last Month</option>
            <option value="this_year">This Year</option>
            <option value="last_year">Last Year</option>
            <option value="custom">Custom</option>
          </select>
          <select v-model="selectedYear" @change="loadSalesReport" class="px-3 py-1.5 text-xs bg-white border border-[#DBDADE] rounded-md focus:outline-none focus:border-[#7367F0] text-[#4B465C] font-medium">
            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>
      </div>

      <!-- Sales Sub-tabs -->
      <div class="flex flex-wrap gap-1 bg-[#F8F7FA] p-1 rounded-lg border border-[#EBE9F1] mb-6">
        <button 
          v-for="st in salesSubTabs" 
          :key="st.key" 
          class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all"
          :class="salesSubTab === st.key ? 'bg-white text-[#7367F0] shadow-sm font-bold' : 'text-[#82868B] hover:text-[#4B465C]'"
          @click="salesSubTab = st.key"
        >
          {{ st.label }}
        </button>
      </div>

      <!-- Invoices -->
      <div v-if="salesSubTab === 'invoices'">
        <div class="flex flex-wrap items-center gap-3 mb-4">
          <a-select v-model:value="srFilters.perPage" size="small" style="width:70px" @change="loadSalesReport">
            <a-select-option :value="10">10</a-select-option>
            <a-select-option :value="25">25</a-select-option>
            <a-select-option :value="50">50</a-select-option>
          </a-select>
          <a-input-search v-model:value="srFilters.search" placeholder="Search invoices..." size="small" style="width:220px" @search="loadSalesReport" />
          <a-select v-model:value="srFilters.status" mode="multiple" :max-tag-count="1" size="small" style="width:160px" placeholder="Status" @change="loadSalesReport" allow-clear>
            <a-select-option v-for="s in ['Unpaid','Paid','Partially Paid','Overdue','Draft']" :key="s" :value="s">{{ s }}</a-select-option>
          </a-select>
          <a-select v-model:value="srFilters.sale_agent" size="small" style="width:180px" placeholder="Sale Agent" @change="loadSalesReport" allow-clear>
            <a-select-option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</a-select-option>
          </a-select>
        </div>
        <div v-if="srLoading" class="text-center py-12 text-[#82868B] text-xs">Loading Invoices...</div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-[#F8F7FA] border-b border-[#EBE9F1] text-[11px] font-bold uppercase tracking-wider text-[#82868B]">
                <th class="py-2.5 px-3">Invoice #</th><th>Customer</th><th>Date</th><th>Due Date</th><th>Amount</th><th>With Tax</th><th>Total Tax</th><th>Discount</th><th>Open Amount</th><th>Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EBE9F1]">
              <tr v-for="r in srInvoices" :key="r.id" class="hover:bg-[#F8F7FA]/70">
                <td class="py-2.5 px-3 font-bold text-[#7367F0]">{{ r.number }}</td>
                <td class="py-2.5 px-3 font-medium text-[#4B465C]">{{ r.customer }}</td>
                <td class="py-2.5 px-3 text-[#82868B]">{{ r.date }}</td>
                <td class="py-2.5 px-3 text-[#82868B]">{{ r.duedate }}</td>
                <td class="py-2.5 px-3 font-semibold">${{ fm(r.amount) }}</td>
                <td class="py-2.5 px-3 font-bold text-[#4B465C]">${{ fm(r.amount_with_tax) }}</td>
                <td class="py-2.5 px-3 text-[#82868B]">${{ fm(r.total_tax) }}</td>
                <td class="py-2.5 px-3 text-[#82868B]">${{ fm(r.discount) }}</td>
                <td class="py-2.5 px-3 font-bold text-[#EA5455]">${{ fm(r.amount_open) }}</td>
                <td class="py-2.5 px-3">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold" :class="statusClass(r.status)">
                    {{ r.status }}
                  </span>
                </td>
              </tr>
              <tr v-if="!srInvoices.length"><td colspan="10" class="text-center py-8 text-[#82868B]">No invoices found</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Items Sub-tab -->
      <div v-if="salesSubTab === 'items'">
        <div class="mb-4">
          <a-input-search v-model:value="srFilters.search" placeholder="Search items..." size="small" style="width:220px" @search="loadSalesReport" />
        </div>
        <div v-if="srLoading" class="text-center py-12 text-[#82868B] text-xs">Loading items...</div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-[#F8F7FA] border-b border-[#EBE9F1] text-[11px] font-bold uppercase tracking-wider text-[#82868B]">
                <th class="py-2.5 px-3">Item #</th><th>Name</th><th>Description</th><th>Rate</th><th>Tax Rate</th><th>Unit</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EBE9F1]">
              <tr v-for="r in srItems" :key="r.id" class="hover:bg-[#F8F7FA]/70">
                <td class="py-2.5 px-3 font-bold text-[#7367F0]">{{ r.id }}</td>
                <td class="py-2.5 px-3 font-semibold text-[#4B465C]">{{ r.name }}</td>
                <td class="py-2.5 px-3 text-[#82868B]">{{ r.description || '—' }}</td>
                <td class="py-2.5 px-3 font-bold text-[#4B465C]">${{ fm(r.rate) }}</td>
                <td class="py-2.5 px-3 text-[#82868B]">{{ r.tax_rate }}%</td>
                <td class="py-2.5 px-3 text-[#82868B]">{{ r.unit || '—' }}</td>
              </tr>
              <tr v-if="!srItems.length"><td colspan="6" class="text-center py-8 text-[#82868B]">No items found</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Payments Sub-tab -->
      <div v-if="salesSubTab === 'payments'">
        <div class="mb-4">
          <a-input-search v-model:value="srFilters.search" placeholder="Search payments..." size="small" style="width:220px" @search="loadSalesReport" />
        </div>
        <div v-if="srLoading" class="text-center py-12 text-[#82868B] text-xs">Loading payments...</div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-[#F8F7FA] border-b border-[#EBE9F1] text-[11px] font-bold uppercase tracking-wider text-[#82868B]">
                <th class="py-2.5 px-3">Payment #</th><th>Date</th><th>Invoice #</th><th>Customer</th><th>Payment Mode</th><th>Transaction ID</th><th>Note</th><th>Amount</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EBE9F1]">
              <tr v-for="r in srPayments" :key="r.id" class="hover:bg-[#F8F7FA]/70">
                <td class="py-2.5 px-3 font-bold text-[#7367F0]">{{ r.number }}</td>
                <td class="py-2.5 px-3 text-[#82868B]">{{ r.date }}</td>
                <td class="py-2.5 px-3 font-medium text-[#4B465C]">{{ r.invoice_number }}</td>
                <td class="py-2.5 px-3 font-semibold text-[#4B465C]">{{ r.customer }}</td>
                <td class="py-2.5 px-3"><span class="px-2 py-0.5 rounded bg-[#F8F7FA] border border-[#EBE9F1] text-[11px] font-semibold">{{ r.payment_mode }}</span></td>
                <td class="py-2.5 px-3 font-mono text-[11px] text-[#82868B]">{{ r.transaction_id || '—' }}</td>
                <td class="py-2.5 px-3 text-[#82868B]">{{ trunc(r.note, 40) }}</td>
                <td class="py-2.5 px-3 font-bold text-[#28C76F]">${{ fm(r.amount) }}</td>
              </tr>
              <tr v-if="!srPayments.length"><td colspan="8" class="text-center py-8 text-[#82868B]">No payments found</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Credit Notes Sub-tab -->
      <div v-if="salesSubTab === 'credit-notes'">
        <div class="mb-4">
          <a-input-search v-model:value="srFilters.search" placeholder="Search credit notes..." size="small" style="width:220px" @search="loadSalesReport" />
        </div>
        <div v-if="srLoading" class="text-center py-12 text-[#82868B] text-xs">Loading credit notes...</div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-[#F8F7FA] border-b border-[#EBE9F1] text-[11px] font-bold uppercase tracking-wider text-[#82868B]">
                <th class="py-2.5 px-3">Credit Note #</th><th>Date</th><th>Customer</th><th>Reference</th><th>Amount</th><th>With Tax</th><th>Remaining</th><th>Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EBE9F1]">
              <tr v-for="r in srCreditNotes" :key="r.id" class="hover:bg-[#F8F7FA]/70">
                <td class="py-2.5 px-3 font-bold text-[#7367F0]">{{ r.number }}</td>
                <td class="py-2.5 px-3 text-[#82868B]">{{ r.date }}</td>
                <td class="py-2.5 px-3 font-semibold text-[#4B465C]">{{ r.customer }}</td>
                <td class="py-2.5 px-3 text-[#82868B]">{{ r.reference || '—' }}</td>
                <td class="py-2.5 px-3 font-semibold">${{ fm(r.amount) }}</td>
                <td class="py-2.5 px-3 font-bold text-[#4B465C]">${{ fm(r.amount_with_tax) }}</td>
                <td class="py-2.5 px-3 font-bold text-[#28C76F]">${{ fm(r.remaining_amount) }}</td>
                <td class="py-2.5 px-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="statusClass(r.status)">{{ r.status }}</span></td>
              </tr>
              <tr v-if="!srCreditNotes.length"><td colspan="8" class="text-center py-8 text-[#82868B]">No credit notes found</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Proposals Sub-tab -->
      <div v-if="salesSubTab === 'proposals'">
        <div class="mb-4">
          <a-input-search v-model:value="srFilters.search" placeholder="Search proposals..." size="small" style="width:220px" @search="loadSalesReport" />
        </div>
        <div v-if="srLoading" class="text-center py-12 text-[#82868B] text-xs">Loading proposals...</div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-[#F8F7FA] border-b border-[#EBE9F1] text-[11px] font-bold uppercase tracking-wider text-[#82868B]">
                <th class="py-2.5 px-3">Proposal #</th><th>Subject</th><th>To</th><th>Date</th><th>Open Till</th><th>Amount</th><th>Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EBE9F1]">
              <tr v-for="r in srProposals" :key="r.number" class="hover:bg-[#F8F7FA]/70">
                <td class="py-2.5 px-3 font-bold text-[#7367F0]">{{ r.number }}</td>
                <td class="py-2.5 px-3 font-semibold text-[#4B465C]">{{ r.subject }}</td>
                <td class="py-2.5 px-3 text-[#4B465C]">{{ r.to }}</td>
                <td class="py-2.5 px-3 text-[#82868B]">{{ r.date }}</td>
                <td class="py-2.5 px-3 text-[#82868B]">{{ r.open_till }}</td>
                <td class="py-2.5 px-3 font-bold text-[#4B465C]">${{ fm(r.amount) }}</td>
                <td class="py-2.5 px-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="statusClass(r.status)">{{ r.status }}</span></td>
              </tr>
              <tr v-if="!srProposals.length"><td colspan="7" class="text-center py-8 text-[#82868B]">No proposals found</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Estimates Sub-tab -->
      <div v-if="salesSubTab === 'estimates'">
        <div class="mb-4">
          <a-input-search v-model:value="srFilters.search" placeholder="Search estimates..." size="small" style="width:220px" @search="loadSalesReport" />
        </div>
        <div v-if="srLoading" class="text-center py-12 text-[#82868B] text-xs">Loading estimates...</div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-[#F8F7FA] border-b border-[#EBE9F1] text-[11px] font-bold uppercase tracking-wider text-[#82868B]">
                <th class="py-2.5 px-3">Estimate #</th><th>Subject</th><th>To</th><th>Date</th><th>Open Till</th><th>Amount</th><th>Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EBE9F1]">
              <tr v-for="r in srEstimates" :key="r.number" class="hover:bg-[#F8F7FA]/70">
                <td class="py-2.5 px-3 font-bold text-[#7367F0]">{{ r.number }}</td>
                <td class="py-2.5 px-3 font-semibold text-[#4B465C]">{{ r.subject }}</td>
                <td class="py-2.5 px-3 text-[#4B465C]">{{ r.to }}</td>
                <td class="py-2.5 px-3 text-[#82868B]">{{ r.date }}</td>
                <td class="py-2.5 px-3 text-[#82868B]">{{ r.open_till }}</td>
                <td class="py-2.5 px-3 font-bold text-[#4B465C]">${{ fm(r.amount) }}</td>
                <td class="py-2.5 px-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="statusClass(r.status)">{{ r.status }}</span></td>
              </tr>
              <tr v-if="!srEstimates.length"><td colspan="7" class="text-center py-8 text-[#82868B]">No estimates found</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Customers Sub-tab -->
      <div v-if="salesSubTab === 'customers'">
        <div class="mb-4">
          <a-input-search v-model:value="srFilters.search" placeholder="Search customers..." size="small" style="width:220px" @search="loadSalesReport" />
        </div>
        <div v-if="srLoading" class="text-center py-12 text-[#82868B] text-xs">Loading customers...</div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-[#F8F7FA] border-b border-[#EBE9F1] text-[11px] font-bold uppercase tracking-wider text-[#82868B]">
                <th class="py-2.5 px-3">Customer</th><th>Total Invoices</th><th>Amount</th><th>Amount with Tax</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EBE9F1]">
              <tr v-for="r in srCustomers" :key="r.id" class="hover:bg-[#F8F7FA]/70">
                <td class="py-2.5 px-3 font-bold text-[#4B465C]">{{ r.company }}</td>
                <td class="py-2.5 px-3 font-semibold text-[#7367F0]">{{ r.total_invoices }}</td>
                <td class="py-2.5 px-3 font-semibold text-[#4B465C]">${{ fm(r.amount) }}</td>
                <td class="py-2.5 px-3 font-bold text-[#28C76F]">${{ fm(r.amount_with_tax) }}</td>
              </tr>
              <tr v-if="!srCustomers.length"><td colspan="4" class="text-center py-8 text-[#82868B]">No customers found</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Charts Sub-tab -->
      <div v-if="salesSubTab === 'charts'">
        <div v-if="srLoading" class="text-center py-12 text-[#82868B] text-xs">Loading charts...</div>
        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="bg-[#F8F7FA] border border-[#EBE9F1] rounded-lg p-4">
            <h4 class="text-xs font-bold text-[#82868B] uppercase mb-4">Monthly Invoices vs Payments ($)</h4>
            <div class="flex items-end gap-2 h-64 border-b border-[#DBDADE] pb-2">
              <div v-for="row in srCharts" :key="row.month" class="flex-1 flex flex-col items-center gap-1 h-full justify-end">
                <div class="w-full bg-[#7367F0] rounded-t transition-all" :style="{ height: srChartMax ? Math.max(4, Math.round((row.invoice_total / srChartMax) * 200)) + 'px' : '4px' }"></div>
                <span class="text-[10px] text-[#82868B]">{{ monthName(row.month) }}</span>
              </div>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-[#F8F7FA] border-b border-[#EBE9F1] text-[11px] font-bold uppercase text-[#82868B]">
                  <th class="py-2 px-3">Month</th><th>Invoices</th><th>Invoice $</th><th>Payments</th><th>Payment $</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EBE9F1]">
                <tr v-for="row in srCharts" :key="row.month" class="hover:bg-[#F8F7FA]/70">
                  <td class="py-2 px-3 font-semibold text-[#4B465C]">{{ monthName(row.month) }}</td>
                  <td class="py-2 px-3 text-[#7367F0] font-bold">{{ row.invoices }}</td>
                  <td class="py-2 px-3 font-bold">${{ fm(row.invoice_total) }}</td>
                  <td class="py-2 px-3 text-[#28C76F] font-bold">{{ row.payments }}</td>
                  <td class="py-2 px-3 font-bold text-[#28C76F]">${{ fm(row.payment_total) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- 3. EXPENSES REPORT TAB                     -->
    <!-- ========================================== -->
    <div v-if="activeTab === 'expenses'" class="bg-white border border-[#EBE9F1] rounded-lg p-6 shadow-sm">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-4 border-b border-[#EBE9F1]">
        <div>
          <h2 class="text-lg font-bold text-[#4B465C] m-0">Expenses Report — {{ selectedYear }}</h2>
          <span class="text-xs text-[#82868B]">Category breakdowns, billable vs non-billable matrix</span>
        </div>
        <div class="flex items-center gap-4">
          <label class="flex items-center gap-2 text-xs font-semibold text-[#4B465C] cursor-pointer">
            <input type="checkbox" v-model="excludeBillable" @change="loadExpensesDetailed" class="rounded text-[#7367F0] focus:ring-0" />
            Exclude Billable Expenses
          </label>
          <select v-model="selectedYear" @change="loadExpensesDetailed" class="px-3 py-1.5 text-xs bg-white border border-[#DBDADE] rounded-md focus:outline-none focus:border-[#7367F0] text-[#4B465C] font-medium">
            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>
      </div>

      <div v-if="expLoading" class="text-center py-16 text-xs text-[#82868B]">Loading Expenses Report...</div>
      <div v-else class="space-y-6">
        <!-- Expenses Charts -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-[#F8F7FA] border border-[#EBE9F1] rounded-lg p-4">
            <h4 class="text-xs font-bold text-[#82868B] uppercase mb-2">Monthly Expenses — Not Billable ($)</h4>
            <VueApexCharts type="bar" height="280" :options="expNotBillableBarOptions" :series="expNotBillableBarSeries"></VueApexCharts>
          </div>
          <div v-if="!excludeBillable" class="bg-[#F8F7FA] border border-[#EBE9F1] rounded-lg p-4">
            <h4 class="text-xs font-bold text-[#82868B] uppercase mb-2">Monthly Expenses — Billable ($)</h4>
            <VueApexCharts type="bar" height="280" :options="expBillableBarOptions" :series="expBillableBarSeries"></VueApexCharts>
          </div>
        </div>

        <!-- Not Billable Table -->
        <div>
          <h4 class="text-sm font-bold text-[#4B465C] mb-3">Not Billable Expenses by Category</h4>
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-[#F8F7FA] border-b border-[#EBE9F1] text-[11px] font-bold uppercase text-[#82868B]">
                  <th class="py-2.5 px-3 sticky left-0 bg-[#F8F7FA] z-10 min-w-[140px]">Category</th>
                  <th v-for="m in SHORT_MONTHS" :key="m" class="py-2.5 px-2">{{ m }}</th>
                  <th class="py-2.5 px-3">Total ({{ selectedYear }})</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EBE9F1]">
                <tr v-for="r in expNotBillable" :key="r.category" :class="r.is_total ? 'bg-[#F8F7FA] font-bold text-[#4B465C]' : 'hover:bg-[#F8F7FA]/70'">
                  <td class="py-2.5 px-3 sticky left-0 bg-white z-10 font-medium" :class="r.is_total ? 'bg-[#F8F7FA] font-bold' : ''">{{ r.category }}</td>
                  <td v-for="(v, i) in r.monthly" :key="i" class="py-2.5 px-2 text-[#82868B]">${{ fm(v) }}</td>
                  <td class="py-2.5 px-3 font-bold text-[#EA5455]">${{ fm(r.total) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- 4. FINANCE (EXPENSES VS INCOME) TAB        -->
    <!-- ========================================== -->
    <div v-if="activeTab === 'finance'" class="bg-white border border-[#EBE9F1] rounded-lg p-6 shadow-sm">
      <div class="flex items-center justify-between mb-6 pb-4 border-b border-[#EBE9F1]">
        <div>
          <h2 class="text-lg font-bold text-[#4B465C] m-0">Finance Overview — {{ selectedYear }}</h2>
          <span class="text-xs text-[#82868B]">Net margin, revenue inflow vs operating outflow</span>
        </div>
      </div>

      <div v-if="!finance" class="text-center py-16 text-xs text-[#82868B]">Loading Finance Report...</div>
      <div v-else class="space-y-6">
        <div class="bg-[#F8F7FA] border border-[#EBE9F1] rounded-lg p-4">
          <VueApexCharts type="bar" height="300" :options="financeChartOptions" :series="financeChartSeries"></VueApexCharts>
        </div>

        <div class="p-5 rounded-lg border flex items-center gap-4" :class="finance.profit >= 0 ? 'bg-[#28C76F]/10 border-[#28C76F]/30 text-[#28C76F]' : 'bg-[#EA5455]/10 border-[#EA5455]/30 text-[#EA5455]'">
          <div class="text-3xl font-bold">{{ finance.profit >= 0 ? '📈' : '📉' }}</div>
          <div>
            <h4 class="text-base font-bold m-0" :class="finance.profit >= 0 ? 'text-[#28C76F]' : 'text-[#EA5455]'">
              {{ finance.profit >= 0 ? 'Business is Operating with Net Profit' : 'Business is Operating at a Loss' }}
            </h4>
            <p class="text-xs text-[#82868B] m-0 mt-0.5">Net Profit: <strong class="text-[#4B465C]">${{ formatMoney(finance.profit) }}</strong> for calendar year {{ selectedYear }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- 5. TIMESHEETS TAB                          -->
    <!-- ========================================== -->
    <div v-if="activeTab === 'timesheets'" class="bg-white border border-[#EBE9F1] rounded-lg p-6 shadow-sm">
      <div class="flex items-center justify-between mb-6 pb-4 border-b border-[#EBE9F1]">
        <div>
          <h2 class="text-lg font-bold text-[#4B465C] m-0">Timesheets Overview — {{ selectedYear }}</h2>
          <span class="text-xs text-[#82868B]">Monthly logged hours and completed task volume</span>
        </div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-[#F8F7FA] border border-[#EBE9F1] rounded-lg p-4">
          <VueApexCharts type="bar" height="300" :options="timesheetsChartOptions" :series="timesheetsChartSeries"></VueApexCharts>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-[#F8F7FA] border-b border-[#EBE9F1] text-[11px] font-bold uppercase text-[#82868B]">
                <th class="py-2.5 px-3">Month</th><th>Hours</th><th>Tasks</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EBE9F1]">
              <tr v-for="row in timesheetsData" :key="row.month" class="hover:bg-[#F8F7FA]/70">
                <td class="py-2.5 px-3 font-semibold text-[#4B465C]">{{ monthName(row.month) }}</td>
                <td class="py-2.5 px-3 font-bold text-[#7367F0]">{{ row.hours }}h</td>
                <td class="py-2.5 px-3 font-semibold text-[#28C76F]">{{ row.tasks }}</td>
              </tr>
              <tr class="bg-[#F8F7FA] font-bold text-[#4B465C]">
                <td class="py-2.5 px-3">Total</td>
                <td class="py-2.5 px-3 text-[#7367F0]">{{ timesheetsData.reduce((a,r) => a+r.hours, 0) }}h</td>
                <td class="py-2.5 px-3 text-[#28C76F]">{{ timesheetsData.reduce((a,r) => a+r.tasks, 0) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- 6. KB ARTICLES TAB                         -->
    <!-- ========================================== -->
    <div v-if="activeTab === 'kb'" class="bg-white border border-[#EBE9F1] rounded-lg p-6 shadow-sm">
      <div class="flex items-center justify-between mb-6 pb-4 border-b border-[#EBE9F1]">
        <div>
          <h2 class="text-lg font-bold text-[#4B465C] m-0">KB Articles — Voting Report</h2>
          <span class="text-xs text-[#82868B]">Article feedback, helpfulness ratings and reader satisfaction</span>
        </div>
        <select v-model="kbCategoryId" @change="loadKbReport" class="px-3 py-1.5 text-xs bg-white border border-[#DBDADE] rounded-md focus:outline-none focus:border-[#7367F0] text-[#4B465C]">
          <option value="">All Groups</option>
          <option v-for="c in kbCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>

      <div v-if="kbLoading" class="text-center py-16 text-xs text-[#82868B]">Loading KB ratings...</div>
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="a in kbArticles" :key="a.id" class="p-4 rounded-lg border border-[#EBE9F1] bg-[#F8F7FA]">
          <div class="font-bold text-xs text-[#4B465C] mb-2">{{ a.title }} <span class="text-[#82868B] font-normal">({{ a.total }} votes)</span></div>
          <div v-if="a.total === 0" class="text-center py-6 text-xs text-[#82868B] italic">No votes yet</div>
          <div v-else>
            <VueApexCharts type="donut" height="180" :options="kbDonutOptions(a)" :series="kbDonutSeries(a)"></VueApexCharts>
          </div>
        </div>
        <div v-if="!kbArticles.length" class="col-span-3 text-center py-12 text-xs text-[#82868B]">No articles found</div>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- 7. TEAM REPORT TAB                         -->
    <!-- ========================================== -->
    <div v-if="activeTab === 'team'" class="bg-white border border-[#EBE9F1] rounded-lg p-6 shadow-sm">
      <div class="flex items-center justify-between mb-6 pb-4 border-b border-[#EBE9F1]">
        <div>
          <h2 class="text-lg font-bold text-[#4B465C] m-0">Team Performance</h2>
          <span class="text-xs text-[#82868B]">Workload distribution across team members</span>
        </div>
      </div>

      <div v-if="loadingTeam" class="text-center py-16 text-xs text-[#82868B]">Loading team data...</div>
      <div v-else>
        <div class="bg-[#F8F7FA] border border-[#EBE9F1] rounded-lg p-4 mb-4">
          <VueApexCharts type="bar" height="300" :options="teamChartOptions" :series="teamChartSeries"></VueApexCharts>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { useRoute } from 'vue-router'
import VueApexCharts from 'vue3-apexcharts'

const BASE = '/api'
const route = useRoute()

const selectedYear   = ref(new Date().getFullYear())
const currentMonth   = new Date().getMonth() + 1
const activeTab      = ref('leads')
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
const kbArticles    = ref([])
const kbCategories  = ref([])
const kbCategoryId  = ref('')
const kbLoading     = ref(false)

// Leads specific reactive state
const leadsLoading   = ref(false)
const leadsTotals    = ref({ total_leads: 0, total_converted: 0, conversion_rate: 0, total_value: 0, sources_count: 0 })
const leadsBySource  = ref([])
const leadsByStatus  = ref([])
const leadsByStaff   = ref([])
const leadsList      = ref([])
const leadsFilters   = ref({ search: '', status: '', source: '' })

// Sales report state
const salesSubTab    = ref('invoices')
const srPeriod       = ref('this_year')
const srLoading      = ref(false)
const srFilters      = ref({ search: '', status: [], sale_agent: null, perPage: 10 })
const srInvoices     = ref([])
const srItems        = ref([])
const srPayments     = ref([])
const srCreditNotes  = ref([])
const srProposals    = ref([])
const srEstimates    = ref([])
const srCustomers    = ref([])
const srCharts       = ref([])
const srIncome       = ref(null)
const srModes        = ref([])
const srGroups       = ref([])
const agents         = ref([])

const SHORT_MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']
const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December']
function monthName(m) { return SHORT_MONTHS[m - 1] || '' }

function formatMoney(v) {
  if (!v) return '0.00'
  return Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
const fm = formatMoney

function compPct(val, total) {
  if (!total) return 0
  return Math.min(100, Math.round((val / total) * 100))
}

function statusClass(s) {
  if (!s) return 'bg-[#F8F7FA] text-[#82868B]'
  const lower = s.toLowerCase().replace(/\s+/g, '')
  if (['paid', 'accepted', 'active'].includes(lower)) return 'bg-[#28C76F]/10 text-[#28C76F]'
  if (['unpaid', 'declined', 'overdue', 'lost'].includes(lower)) return 'bg-[#EA5455]/10 text-[#EA5455]'
  if (['partiallypaid', 'revised', 'draft'].includes(lower)) return 'bg-[#FF9F43]/10 text-[#FF9F43]'
  if (['open', 'sent', 'new'].includes(lower)) return 'bg-[#7367F0]/10 text-[#7367F0]'
  return 'bg-[#00CFE8]/10 text-[#00CFE8]'
}

function trunc(s, n) {
  if (!s) return '—'
  return s.length > n ? s.slice(0, n) + '...' : s
}

const years = computed(() => {
  const y = new Date().getFullYear()
  return [y, y-1, y-2, y-3]
})

const tabs = [
  { key: 'leads',      label: 'Leads',              icon: '🔍' },
  { key: 'sales',      label: 'Sales',              icon: '💰' },
  { key: 'expenses',   label: 'Expenses',           icon: '💸' },
  { key: 'finance',    label: 'Expenses vs Income', icon: '📊' },
  { key: 'timesheets', label: 'Timesheets',         icon: '⏱' },
  { key: 'kb',         label: 'KB Articles',        icon: '📚' },
  { key: 'team',       label: 'Team',               icon: '👥' },
]

const salesSubTabs = [
  { key: 'invoices',      label: 'Invoices' },
  { key: 'items',         label: 'Items' },
  { key: 'payments',      label: 'Payments Received' },
  { key: 'credit-notes',  label: 'Credit Notes' },
  { key: 'proposals',     label: 'Proposals' },
  { key: 'estimates',     label: 'Estimates' },
  { key: 'customers',     label: 'Customers' },
  { key: 'charts',        label: 'Sales Charts' },
]

// Filtered leads explorer list
const filteredLeadsList = computed(() => {
  return leadsList.value.filter(l => {
    if (leadsFilters.value.search) {
      const q = leadsFilters.value.search.toLowerCase()
      const match = (l.name || '').toLowerCase().includes(q) ||
                    (l.company || '').toLowerCase().includes(q) ||
                    (l.email || '').toLowerCase().includes(q)
      if (!match) return false
    }
    if (leadsFilters.value.status && l.status !== leadsFilters.value.status) return false
    if (leadsFilters.value.source && l.source !== leadsFilters.value.source) return false
    return true
  })
})

// ApexCharts computed configs for Leads
const leadsChartOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false }, stacked: false },
  plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
  xaxis: { categories: SHORT_MONTHS, labels: { style: { fontSize: '11px', fontWeight: 600, colors: '#82868B' } } },
  yaxis: { labels: { style: { fontSize: '11px', colors: '#82868B' } } },
  colors: ['#7367F0', '#28C76F'],
  dataLabels: { enabled: false },
  grid: { borderColor: '#EBE9F1', strokeDashArray: 4 },
  legend: { show: false },
  tooltip: { theme: 'light', y: { formatter: v => v + ' leads' } }
}))

const leadsChartSeries = computed(() => [
  { name: 'Total Leads Created', data: leadsData.value.map(r => r.count) },
  { name: 'Converted to Customer', data: leadsData.value.map(r => r.converted) }
])

const leadsSourceDonutOptions = computed(() => ({
  chart: { type: 'donut' },
  labels: leadsBySource.value.map(s => s.name),
  colors: ['#7367F0', '#28C76F', '#00CFE8', '#FF9F43', '#EA5455', '#A8AAAE'],
  legend: { show: false },
  plotOptions: {
    pie: {
      donut: {
        size: '72%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total Leads',
            fontSize: '11px',
            color: '#82868B',
            formatter: () => leadsTotals.value.total_leads
          }
        }
      }
    }
  },
  dataLabels: { enabled: false },
  stroke: { width: 2, colors: ['#fff'] }
}))

const leadsSourceDonutSeries = computed(() => leadsBySource.value.map(s => s.count))

// Finance chart
const financeChartOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false } },
  xaxis: { categories: ['Total Income', 'Total Expenses', 'Payments Received', 'Net Profit'], labels: { style: { fontSize: '12px', fontWeight: 600 } } },
  colors: ['#28C76F', '#EA5455', '#7367F0', finance.value?.profit >= 0 ? '#28C76F' : '#EA5455'],
  plotOptions: { bar: { distributed: true, columnWidth: '45%', borderRadius: 6 } },
  dataLabels: { enabled: true, formatter: v => '$' + (v / 1000).toFixed(1) + 'k' },
  legend: { show: false },
  grid: { borderColor: '#EBE9F1' }
}))

const financeChartSeries = computed(() => [{
  name: 'Amount ($)',
  data: [
    finance.value?.income || 0,
    finance.value?.expenses || 0,
    finance.value?.payments || 0,
    finance.value?.profit || 0
  ]
}])

// Timesheets chart
const timesheetsChartOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false } },
  xaxis: { categories: SHORT_MONTHS, labels: { style: { fontSize: '11px', fontWeight: 600 } } },
  yaxis: { labels: { formatter: v => v + 'h' } },
  colors: ['#7367F0'],
  plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
  grid: { borderColor: '#EBE9F1' }
}))
const timesheetsChartSeries = computed(() => [{ name: 'Hours Logged', data: timesheetsData.value.map(r => r.hours) }])

// Team chart
const teamChartOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false } },
  xaxis: { categories: teamData.value.map(m => m.name), labels: { style: { fontSize: '11px', fontWeight: 600 } } },
  colors: ['#7367F0'],
  plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
  grid: { borderColor: '#EBE9F1' }
}))
const teamChartSeries = computed(() => [{ name: 'Completed Tasks', data: teamData.value.map(m => m.task_count) }])

// Expenses Chart options
const expNotBillableBarOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false } },
  xaxis: { categories: SHORT_MONTHS, labels: { style: { fontSize: '11px', fontWeight: 600 } } },
  yaxis: { labels: { formatter: v => '$' + v.toLocaleString() } },
  colors: ['#EA5455'],
  plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
  grid: { borderColor: '#EBE9F1' }
}))
const expNotBillableBarSeries = computed(() => {
  const totalRow = expNotBillable.value.find(r => r.is_total)
  return [{ name: 'Not Billable', data: totalRow?.monthly ?? Array(12).fill(0) }]
})

const expBillableBarOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false } },
  xaxis: { categories: SHORT_MONTHS, labels: { style: { fontSize: '11px', fontWeight: 600 } } },
  yaxis: { labels: { formatter: v => '$' + v.toLocaleString() } },
  colors: ['#28C76F'],
  plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
  grid: { borderColor: '#EBE9F1' }
}))
const expBillableBarSeries = computed(() => {
  const totalRow = expBillable.value.find(r => r.is_total)
  return [{ name: 'Billable', data: totalRow?.monthly ?? Array(12).fill(0) }]
})

function kbDonutOptions(a) {
  return {
    chart: { type: 'donut' },
    labels: ['Helpful', 'Not Helpful'],
    colors: ['#28C76F', '#EA5455'],
    legend: { show: false },
    dataLabels: { enabled: false }
  }
}
function kbDonutSeries(a) {
  return [a.helpful || 0, a.not_helpful || 0]
}

const srChartMax = computed(() => Math.max(...srCharts.value.map(r => r.invoice_total), 1))

// API Loaders
async function loadFinance() {
  try {
    const res = await axios.get(`${BASE}/reports/finance`, { params: { year: selectedYear.value } })
    finance.value = res.data
  } catch {
    finance.value = { income: 0, expenses: 0, payments: 0, profit: 0 }
  }
}

async function loadLeads() {
  leadsLoading.value = true
  try {
    const res = await axios.get(`${BASE}/reports/leads`, { params: { year: selectedYear.value } })
    leadsData.value = res.data.monthly || []
    leadsTotals.value = res.data.totals || { total_leads: 0, total_converted: 0, conversion_rate: 0, total_value: 0, sources_count: 0 }
    leadsBySource.value = res.data.by_source || []
    leadsByStatus.value = res.data.by_status || []
    leadsByStaff.value = res.data.by_staff || []
    leadsList.value = res.data.leads || []
  } catch (err) {
    console.error('Failed to load leads report:', err)
  } finally {
    leadsLoading.value = false
  }
}

async function loadExpensesDetailed() {
  expLoading.value = true
  try {
    const res = await axios.get(`${BASE}/reports/expenses-detailed`, {
      params: { year: selectedYear.value, exclude_billable: excludeBillable.value ? 1 : 0 }
    })
    expNotBillable.value = res.data.not_billable ?? []
    expBillable.value = res.data.billable ?? []
  } catch {
    expNotBillable.value = []
    expBillable.value = []
  } finally {
    expLoading.value = false
  }
}

async function loadAgents() {
  try {
    const res = await axios.get(`${BASE}/staff`)
    agents.value = res.data.data ?? res.data ?? []
  } catch {
    agents.value = []
  }
}

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
  }
  try {
    const res = await axios.get(`${BASE}/${endpoints[tab]}`, { params })
    const d = res.data
    let items = []
    switch (tab) {
      case 'invoices': items = d.invoices?.data ?? d.invoices ?? []; break
      case 'items': items = d.items?.data ?? d.items ?? []; break
      case 'payments': items = d.payments?.data ?? d.payments ?? []; break
      case 'credit-notes': items = d.credit_notes?.data ?? d.credit_notes ?? []; break
      case 'proposals': items = d.proposals?.data ?? d.proposals ?? []; break
      case 'estimates': items = d.estimates?.data ?? d.estimates ?? []; break
      case 'customers': items = d.customers?.data ?? d.customers ?? []; break
      case 'charts': items = d.monthly ?? []; break
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
    }
  } catch {
    // fallback
  } finally {
    srLoading.value = false
  }
}

async function loadTimesheets() {
  timesheetsData.value = Array.from({ length: 12 }, (_, i) => ({
    month: i + 1,
    hours: Math.floor(Math.random() * 200 + 50),
    tasks: Math.floor(Math.random() * 40 + 10),
  }))
}

async function loadTeam() {
  loadingTeam.value = true
  try {
    const res = await axios.get(`${BASE}/reports/team`)
    teamData.value = res.data.team
  } catch {
    teamData.value = [
      { id: 1, name: 'Tom Kunze', task_count: 24 },
      { id: 2, name: 'Alice Brown', task_count: 18 },
      { id: 3, name: 'Bob Smith', task_count: 13 },
      { id: 4, name: 'Carol Lee', task_count: 7 },
    ]
  } finally {
    loadingTeam.value = false
  }
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
  } finally {
    kbLoading.value = false
  }
}

async function loadAll() {
  await Promise.all([loadFinance(), loadLeads(), loadExpensesDetailed(), loadTimesheets(), loadTeam(), loadAgents()])
  if (activeTab.value === 'sales') {
    loadSalesReport()
  } else if (activeTab.value === 'kb') {
    loadKbReport()
  }
}

watch(salesSubTab, () => {
  srFilters.value = { search: '', status: [], sale_agent: null, perPage: 10 }
  loadSalesReport()
})

function exportReport() {
  const currentTabObj = tabs.find(t => t.key === activeTab.value)
  const tabName = currentTabObj ? currentTabObj.label : activeTab.value
  alert(`Exporting ${tabName} Report for fiscal year ${selectedYear.value}...`)
}

// Auto-set active tab from route
if (route.path.includes('/expenses')) activeTab.value = 'expenses'
else if (route.path.includes('/finance')) activeTab.value = 'finance'
else if (route.path.includes('/leads')) activeTab.value = 'leads'
else if (route.path.includes('/timesheets')) activeTab.value = 'timesheets'
else if (route.path.includes('/kb-articles')) activeTab.value = 'kb'
else if (route.path.includes('/team')) activeTab.value = 'team'
else if (route.path.includes('/sales')) activeTab.value = 'sales'

onMounted(loadAll)
</script>

<style scoped>
:deep(.ant-select-selector),
:deep(.ant-input),
:deep(.ant-input-search) {
  border-radius: 6px !important;
  border-color: #DBDADE !important;
}
:deep(.ant-select-focused:not(.ant-select-disabled).ant-select:not(.ant-select-customize-input) .ant-select-selector) {
  border-color: #7367F0 !important;
  box-shadow: 0 0 0 2px rgba(115, 103, 240, 0.2) !important;
}
</style>
