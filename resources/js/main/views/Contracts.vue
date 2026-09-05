<template>
  <div class="contracts-page space-y-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-[#4B465C] m-0">Contracts</h1>
        <p class="text-xs text-[#A8AAAE] mt-1 mb-0">Manage client contracts, SLAs and legal agreements</p>
      </div>
      <div class="flex items-center gap-2.5">
        <button class="btn-outline text-xs font-semibold flex items-center gap-1.5 py-2 px-3.5" @click="exportPDF">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Bulk PDF
        </button>
        <button v-if="canCreateContract" class="btn-primary text-xs font-bold flex items-center gap-1.5 py-2 px-4" @click="openCreate">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          New Contract
        </button>
      </div>
    </div>

    <!-- Summary Cards (Pure White with Vuexy Soft Accent Badges) -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
      <div
        v-for="card in summaryCards"
        :key="card.label"
        class="bg-white border border-[#EBE9F1] rounded-lg p-3.5 shadow-[0_2px_4px_rgba(47,43,61,0.04)] hover:shadow-[0_4px_12px_rgba(47,43,61,0.08)] transition-all flex items-center gap-3"
      >
        <div
          class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
          :style="{ background: card.bg, color: card.color }"
          v-html="card.icon"
        ></div>
        <div class="min-w-0">
          <div class="text-base font-bold text-[#4B465C] tracking-tight truncate">{{ card.value }}</div>
          <div class="text-[10px] font-semibold text-[#A8AAAE] uppercase tracking-wider mt-0.5 truncate">{{ card.label }}</div>
        </div>
      </div>
    </div>

    <!-- Charts Row (ApexCharts) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm">
        <div class="flex items-center space-x-2 pb-3 mb-2 border-b border-[#F1F0F2]">
          <div class="w-2 h-4 rounded-full bg-[#7367F0]"></div>
          <h3 class="text-xs font-bold uppercase tracking-wider text-[#4B465C] m-0">Contracts by Type</h3>
        </div>
        <VueApexCharts type="bar" height="260" :options="contractsBarOptions" :series="contractsBarSeries"></VueApexCharts>
      </div>
      <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm">
        <div class="flex items-center space-x-2 pb-3 mb-2 border-b border-[#F1F0F2]">
          <div class="w-2 h-4 rounded-full bg-[#28C76F]"></div>
          <h3 class="text-xs font-bold uppercase tracking-wider text-[#4B465C] m-0">Contracts Value by Type (USD)</h3>
        </div>
        <VueApexCharts type="area" height="260" :options="contractsValueOptions" :series="contractsValueSeries"></VueApexCharts>
      </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white p-3 border border-[#EBE9F1] rounded-lg shadow-sm">
      <div class="flex items-center gap-2">
        <select class="form-ctrl text-xs h-[34px] px-2.5 bg-white border border-[#DBDADE] rounded-md text-[#4B465C] font-medium" v-model="perPage" @change="load">
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
        </select>
      </div>
      <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
        <select class="form-ctrl text-xs h-[34px] px-3 bg-white border border-[#DBDADE] rounded-md text-[#4B465C] font-medium" v-model="statusFilter" @change="load">
          <option value="">All Statuses</option>
          <option value="Active">Active</option>
          <option value="In Progress">In Progress</option>
          <option value="Expired">Expired</option>
          <option value="Finished">Finished</option>
          <option value="Not Started">Not Started</option>
        </select>
        <div class="relative flex-1 sm:w-60">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-[#A8AAAE] pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input
            v-model="search"
            placeholder="Search contracts..."
            class="form-ctrl text-xs h-[34px] pl-8 pr-3 bg-white border border-[#DBDADE] rounded-md text-[#4B465C] w-full"
            @input="onSearch"
          />
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white border border-[#EBE9F1] rounded-lg shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse">
          <thead>
            <tr class="bg-[#F8F7FA] text-[#6F6B7D] border-b border-[#EBE9F1]">
              <th class="w-10 text-center py-3 px-3"><input type="checkbox" v-model="selectAll" @change="toggleAll" class="rounded text-[#7367F0]" /></th>
              <th class="py-3 px-3.5 text-left font-semibold uppercase text-[11px] tracking-wider">Subject</th>
              <th class="py-3 px-3.5 text-left font-semibold uppercase text-[11px] tracking-wider">Customer</th>
              <th class="py-3 px-3.5 text-left font-semibold uppercase text-[11px] tracking-wider">Contract Type</th>
              <th class="py-3 px-3.5 text-right font-semibold uppercase text-[11px] tracking-wider w-28">Value</th>
              <th class="py-3 px-3.5 text-left font-semibold uppercase text-[11px] tracking-wider w-28">Start Date</th>
              <th class="py-3 px-3.5 text-left font-semibold uppercase text-[11px] tracking-wider w-28">End Date</th>
              <th class="py-3 px-3 text-center font-semibold uppercase text-[11px] tracking-wider w-20">Signed</th>
              <th class="py-3 px-3 text-center font-semibold uppercase text-[11px] tracking-wider w-24">Status</th>
              <th class="w-16 text-center py-3 px-2 font-semibold uppercase text-[11px] tracking-wider no-print"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F1F0F2]">
            <tr v-if="loading">
              <td colspan="10" class="text-center py-12 text-[#A8AAAE]">
                <svg class="animate-spin h-6 w-6 text-[#7367F0] mx-auto mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <p class="text-xs m-0">Loading contracts...</p>
              </td>
            </tr>

            <tr
              v-for="con in contracts"
              :key="con.id"
              class="hover:bg-[#FAF9FB] transition-colors group"
            >
              <td class="text-center py-3 px-3">
                <input type="checkbox" v-model="selected" :value="con.id" class="rounded text-[#7367F0]" />
              </td>
              <td class="py-3 px-3.5">
                <div class="flex flex-col gap-0.5 max-w-xs">
                  <span class="font-bold text-[#4B465C] text-xs leading-snug cursor-pointer hover:text-[#7367F0]" @click="canEditContract ? editContract(con) : null">{{ con.subject }}</span>
                  <span v-if="con.description" class="text-[11px] text-[#A8AAAE] truncate" :title="con.description">{{ con.description }}</span>
                </div>
              </td>
              <td class="py-3 px-3.5 text-[#6F6B7D]">
                <span v-if="con.client?.company" class="font-semibold text-[#7367F0] hover:underline cursor-pointer">
                  {{ con.client.company }}
                </span>
                <span v-else class="text-[#DBDADE]">—</span>
              </td>
              <td class="py-3 px-3.5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-semibold bg-[#7367F0]/10 text-[#7367F0] whitespace-nowrap">
                  {{ contractTypeName(con.contract_type_id) }}
                </span>
              </td>
              <td class="py-3 px-3.5 text-right font-bold text-[#4B465C] text-sm tabular-nums whitespace-nowrap">
                ${{ fmt(con.value) }}
              </td>
              <td class="py-3 px-3.5 text-[#6F6B7D] font-medium whitespace-nowrap">
                {{ fmtDate(con.start_date) }}
              </td>
              <td class="py-3 px-3.5 whitespace-nowrap">
                <span :class="isExpired(con.end_date) ? 'text-rose-500 font-bold' : 'text-[#6F6B7D] font-medium'">
                  {{ fmtDate(con.end_date) || '—' }}
                </span>
              </td>
              <td class="py-3 px-3 text-center">
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold"
                  :class="con.signed ? 'bg-[#28C76F]/10 text-[#28C76F]' : 'bg-[#F8F7FA] text-[#A8AAAE] border border-[#DBDADE]'"
                >
                  {{ con.signed ? 'Signed' : 'No' }}
                </span>
              </td>
              <td class="py-3 px-3 text-center">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-semibold"
                  :class="[
                    con.status === 'Active' ? 'bg-[#28C76F]/10 text-[#28C76F]' :
                    con.status === 'In Progress' ? 'bg-[#7367F0]/10 text-[#7367F0]' :
                    con.status === 'Expired' ? 'bg-[#EA5455]/10 text-[#EA5455]' :
                    con.status === 'Finished' ? 'bg-[#00CFE8]/10 text-[#00CFE8]' :
                    'bg-[#F8F7FA] text-[#6F6B7D] border border-[#EBE9F1]'
                  ]"
                >
                  {{ con.status }}
                </span>
              </td>
              <td class="py-3 px-2 text-center no-print">
                <div class="flex items-center justify-center gap-1">
                  <button
                    v-if="canEditContract"
                    @click="editContract(con)"
                    class="w-7 h-7 rounded border border-transparent hover:border-[#DBDADE] hover:bg-[#F8F7FA] text-[#A8AAAE] hover:text-[#7367F0] flex items-center justify-center transition-all cursor-pointer bg-transparent"
                    title="Edit Contract"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"/></svg>
                  </button>
                  <button
                    v-if="canDeleteContract"
                    @click="deleteContract(con)"
                    class="w-7 h-7 rounded border border-transparent hover:border-rose-200 hover:bg-rose-50 text-[#A8AAAE] hover:text-rose-600 flex items-center justify-center transition-all cursor-pointer bg-transparent"
                    title="Delete Contract"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!loading && !contracts.length">
              <td colspan="10" class="text-center py-12 text-[#A8AAAE]">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" class="mx-auto mb-2 opacity-50"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <p class="text-xs font-semibold m-0">No contracts found</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between px-5 py-3 border-t border-[#F1F0F2] text-xs text-[#6F6B7D]" v-if="totalPages > 1">
        <span class="text-[#A8AAAE]">Page {{ page }} of {{ totalPages }}</span>
        <div class="flex items-center space-x-2">
          <button class="btn-outline px-3 py-1.5 text-xs font-semibold" :disabled="page <= 1" @click="page--; load()">Previous</button>
          <button class="btn-outline px-3 py-1.5 text-xs font-semibold" :disabled="page >= totalPages" @click="page++; load()">Next</button>
        </div>
      </div>
    </div>

    <!-- Contracts Insights Section -->
    <div class="space-y-3">
      <div class="flex items-center space-x-2">
        <div class="w-2 h-4 rounded-full bg-gradient-to-b from-[#7367F0] to-[#9F8ED6]"></div>
        <h3 class="text-sm font-bold text-[#4B465C] m-0">Contracts Insights</h3>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm">
          <h4 class="text-xs font-bold uppercase tracking-wider text-[#6F6B7D] mb-3">Status Distribution</h4>
          <VueApexCharts type="donut" height="240" :options="statusDonutOptions" :series="statusDonutSeries"></VueApexCharts>
        </div>
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm">
          <h4 class="text-xs font-bold uppercase tracking-wider text-[#6F6B7D] mb-3">Monthly Value Trend</h4>
          <VueApexCharts type="bar" height="240" :options="monthlyTrendOptions" :series="monthlyTrendSeries"></VueApexCharts>
        </div>
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm">
          <h4 class="text-xs font-bold uppercase tracking-wider text-[#6F6B7D] mb-3">Expiry Timeline</h4>
          <VueApexCharts type="bar" height="240" :options="expiryTimelineOptions" :series="expiryTimelineSeries"></VueApexCharts>
        </div>
      </div>
    </div>

    <!-- Create/Edit Right-Side Drawer -->
    <a-drawer
      v-model:open="showDrawer"
      placement="right"
      :width="580"
      :destroyOnClose="true"
      class="vuexy-contract-drawer"
    >
      <template #title>
        <div class="flex items-center space-x-3 py-1">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#7367F0] to-[#9F8ED6]"></div>
          <div>
            <h2 class="text-base font-bold text-[#4B465C] m-0">
              {{ editing ? 'Edit Contract' : 'Contract Information' }}
            </h2>
            <p class="text-xs text-[#A8AAAE] m-0 mt-0.5">
              {{ editing ? 'Update contract details and client scope' : 'Enter the details for the new client contract' }}
            </p>
          </div>
        </div>
      </template>

      <div class="p-1 space-y-6">
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 space-y-4 shadow-sm">
          <div class="flex items-center space-x-2 pb-2 border-b border-[#F1F0F2]">
            <span class="text-[11px] font-bold text-[#7367F0] bg-[#7367F0]/10 px-2.5 py-0.5 rounded">01</span>
            <span class="text-xs font-bold uppercase tracking-wider text-[#4B465C]">General Details</span>
          </div>

          <div class="flex items-center gap-6 pt-1">
            <label class="flex items-center gap-2 text-xs font-semibold text-[#4B465C] cursor-pointer select-none">
              <input type="checkbox" v-model="form.trash" class="rounded text-[#7367F0]" />
              <span>Trash</span>
            </label>
            <label class="flex items-center gap-2 text-xs font-semibold text-[#4B465C] cursor-pointer select-none">
              <input type="checkbox" v-model="form.hide_from_customer" class="rounded text-[#7367F0]" />
              <span>Hide from customer</span>
            </label>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
            <!-- Customer -->
            <div class="md:col-span-2">
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                Customer <span class="text-rose-500">*</span>
              </label>
              <div class="relative">
                <select
                  v-model="form.client_id"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                >
                  <option value="">Select customer...</option>
                  <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company }}</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
              </div>
            </div>

            <!-- Subject -->
            <div class="md:col-span-2">
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                Subject <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.subject"
                type="text"
                placeholder="e.g. Website Maintenance SLA"
                class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
              />
            </div>

            <!-- Contract Value -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Contract Value ($)</label>
              <input
                v-model="form.value"
                type="number"
                min="0"
                step="0.01"
                placeholder="0.00"
                class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full font-semibold"
              />
            </div>

            <!-- Contract Type -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Contract Type</label>
              <div class="relative">
                <select
                  v-model="form.contract_type_id"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                >
                  <option value="">Select type...</option>
                  <option v-for="t in contractTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
              </div>
            </div>

            <!-- Start Date -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                Start Date <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.start_date"
                type="date"
                class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
              />
            </div>

            <!-- End Date -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">End Date</label>
              <input
                v-model="form.end_date"
                type="date"
                class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
              />
            </div>

            <!-- Status -->
            <div class="md:col-span-2">
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Status</label>
              <div class="relative">
                <select
                  v-model="form.status"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                >
                  <option value="Not Started">Not Started</option>
                  <option value="Active">Active</option>
                  <option value="In Progress">In Progress</option>
                  <option value="Finished">Finished</option>
                  <option value="Expired">Expired</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
              </div>
            </div>

            <!-- Description -->
            <div class="md:col-span-2">
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Description</label>
              <textarea
                v-model="form.description"
                rows="4"
                placeholder="Contract terms, scope of work, additional notes..."
                class="form-ctrl text-xs p-3 bg-white border-[#DBDADE] rounded-md transition-all min-h-[90px] w-full resize-none"
              ></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Drawer Footer -->
      <template #footer>
        <div class="flex items-center justify-end space-x-3 py-2 px-1">
          <button
            type="button"
            class="btn-outline px-5 py-2.5 text-xs font-semibold"
            @click="closeDrawer"
          >
            Cancel
          </button>
          <button
            type="button"
            class="btn-primary px-6 py-2.5 text-xs font-bold flex items-center gap-2"
            :disabled="saving"
            @click="save"
          >
            <svg v-if="saving" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="animate-spin"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            {{ saving ? 'Saving...' : (editing ? 'Save Changes' : 'Create Contract') }}
          </button>
        </div>
      </template>
    </a-drawer>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'
import { message } from 'ant-design-vue'
import VueApexCharts from 'vue3-apexcharts'
import { useAuthStore } from '../store/authStore'

const authStore = useAuthStore()
const canCreateContract = computed(() => authStore.hasPermission('Contracts', 'create'))
const canEditContract   = computed(() => authStore.hasPermission('Contracts', 'edit'))
const canDeleteContract = computed(() => authStore.hasPermission('Contracts', 'delete'))

const BASE = '/api'
const contracts = ref([])
const stats     = ref({})
const clients   = ref([])
const contractTypes = ref([])
const loading   = ref(false)
const saving    = ref(false)
const search    = ref('')
const statusFilter = ref('')
const perPage   = ref('25')
const page      = ref(1)
const totalPages = ref(1)
const selectAll = ref(false)
const selected  = ref([])
const showDrawer = ref(false)
const editing   = ref(null)

const form = reactive({
  subject: '', client_id: '', contract_type_id: '', value: '',
  start_date: '', end_date: '', status: 'Not Started',
  description: '', signed: false, trash: false, hide_from_customer: false,
})

const CHART_COLORS = ['#7367F0', '#28C76F', '#FF9F43', '#EA5455', '#8F85F3', '#00CFE8', '#E83E8C']

const summaryCards = computed(() => [
  {
    label: 'Contracts',
    value: stats.value.total || 0,
    color: '#7367F0',
    bg: 'rgba(115, 103, 240, 0.1)',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>'
  },
  {
    label: 'Active',
    value: stats.value.active || 0,
    color: '#28C76F',
    bg: 'rgba(40, 199, 111, 0.1)',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polyline points="20 6 9 17 4 12"/></svg>'
  },
  {
    label: 'Expired',
    value: stats.value.expired || 0,
    color: '#EA5455',
    bg: 'rgba(234, 84, 85, 0.1)',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>'
  },
  {
    label: 'About to Expire',
    value: stats.value.about_to_expire || 0,
    color: '#FF9F43',
    bg: 'rgba(255, 159, 67, 0.1)',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'
  },
  {
    label: 'Recently Added',
    value: stats.value.recently_added || 0,
    color: '#00CFE8',
    bg: 'rgba(0, 207, 232, 0.1)',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>'
  },
  {
    label: 'Trash',
    value: stats.value.trash || 0,
    color: '#6F6B7D',
    bg: 'rgba(111, 107, 125, 0.1)',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>'
  },
])

const contractsByType = computed(() => {
  const groups = {}
  contracts.value.forEach(c => {
    const name = contractTypeName(c.contract_type_id)
    if (!groups[name]) groups[name] = 0
    groups[name]++
  })
  const entries = Object.entries(groups).map(([name, count]) => ({ name, count }))
  const max = Math.max(...entries.map(e => e.count), 1)
  return entries.map((e, i) => ({ ...e, pct: (e.count / max) * 100, color: CHART_COLORS[i % CHART_COLORS.length] }))
})

const contractsValueByType = computed(() => {
  const groups = {}
  contracts.value.forEach(c => {
    const name = contractTypeName(c.contract_type_id)
    if (!groups[name]) groups[name] = 0
    groups[name] += Number(c.value || 0)
  })
  const entries = Object.entries(groups).map(([name, value]) => ({ name, value }))
  const max = Math.max(...entries.map(e => e.value), 1)
  return entries.map((e, i) => ({ ...e, pct: (e.value / max) * 100, color: CHART_COLORS[(i + 3) % CHART_COLORS.length] }))
})

const statusDistribution = computed(() => {
  const counts = { Active: 0, 'In Progress': 0, Expired: 0, Finished: 0, 'Not Started': 0 }
  contracts.value.forEach(c => { if (counts[c.status] !== undefined) counts[c.status]++ })
  return Object.entries(counts).filter(([, v]) => v > 0).map(([s, v]) => ({ status: s, count: v }))
})

const STATUS_COLORS = { Active: '#28C76F', 'In Progress': '#7367F0', Expired: '#EA5455', Finished: '#00CFE8', 'Not Started': '#A8AAAE' }

const contractsBarOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false }, animations: { enabled: true }, fontFamily: 'Public Sans, sans-serif' },
  xaxis: { categories: contractsByType.value.map(c => c.name), labels: { style: { fontSize: '11px', fontWeight: 600, colors: '#6F6B7D' } } },
  yaxis: { labels: { style: { fontSize: '11px', colors: '#6F6B7D' } } },
  colors: ['#7367F0'],
  plotOptions: { bar: { horizontal: true, columnWidth: '50%', borderRadius: 4 } },
  dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 700, colors: ['#4B465C'] } },
  grid: { borderColor: '#F1F0F2' },
}))
const contractsBarSeries = computed(() => [{ name: 'Contracts', data: contractsByType.value.map(c => c.count) }])

const contractsValueOptions = computed(() => ({
  chart: { type: 'area', toolbar: { show: false }, animations: { enabled: true }, fontFamily: 'Public Sans, sans-serif' },
  xaxis: { categories: contractsValueByType.value.map(c => c.name), labels: { style: { fontSize: '11px', fontWeight: 600, colors: '#6F6B7D' } } },
  yaxis: { labels: { formatter: v => '$' + v.toLocaleString(), style: { fontSize: '11px', colors: '#6F6B7D' } } },
  colors: ['#28C76F'],
  fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.6, opacityTo: 0.1 } },
  stroke: { curve: 'smooth', width: 2 },
  markers: { size: 4, colors: ['#fff'], strokeColors: '#28C76F', strokeWidth: 2 },
  dataLabels: { enabled: true, formatter: v => '$' + v.toLocaleString(), style: { fontSize: '10px', fontWeight: 700, colors: ['#28C76F'] } },
  grid: { borderColor: '#F1F0F2' },
  tooltip: { y: { formatter: v => '$' + v.toLocaleString() } },
}))
const contractsValueSeries = computed(() => [{ name: 'Value (USD)', data: contractsValueByType.value.map(c => c.value) }])

const statusDonutOptions = computed(() => ({
  chart: { type: 'donut', toolbar: { show: false }, fontFamily: 'Public Sans, sans-serif' },
  labels: statusDistribution.value.map(s => s.status),
  colors: statusDistribution.value.map(s => STATUS_COLORS[s.status] || '#7367F0'),
  plotOptions: { pie: { donut: { size: '68%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '13px', fontWeight: 700, color: '#4B465C', formatter: () => String(statusDistribution.value.reduce((a, b) => a + b.count, 0)) } } } } },
  dataLabels: { enabled: false },
  legend: { position: 'bottom', fontSize: '11px', fontWeight: 600, labels: { colors: '#6F6B7D' }, itemMargin: { horizontal: 8 } },
  responsive: [{ breakpoint: 480, options: { legend: { position: 'bottom' } } }],
}))
const statusDonutSeries = computed(() => statusDistribution.value.map(s => s.count))

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
const monthlyTrendOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false }, animations: { enabled: true }, fontFamily: 'Public Sans, sans-serif' },
  xaxis: { categories: MONTHS, labels: { style: { fontSize: '10px', fontWeight: 600, colors: '#6F6B7D' } } },
  yaxis: { labels: { formatter: v => '$' + v.toLocaleString(), style: { fontSize: '10px', colors: '#6F6B7D' } } },
  colors: ['#7367F0'],
  plotOptions: { bar: { columnWidth: '55%', borderRadius: 4, dataLabels: { position: 'top' } } },
  dataLabels: { enabled: true, formatter: v => '$' + (v / 1000).toFixed(1) + 'k', style: { fontSize: '9px', fontWeight: 700, colors: ['#7367F0'] }, offsetY: -16 },
  grid: { borderColor: '#F1F0F2' },
  tooltip: { y: { formatter: v => '$' + v.toLocaleString() } },
}))
const monthlyTrendSeries = computed(() => [
  { name: 'Contract Value', data: [45000, 52000, 38000, 61000, 48000, 72000, 55000, 68000, 59000, 81000, 74000, 92000] },
])

const expiryTimelineOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false }, animations: { enabled: true }, fontFamily: 'Public Sans, sans-serif' },
  xaxis: { categories: ['This Week', 'Next Week', 'In 2 Wks', 'In 3 Wks', 'Next Mo', 'Beyond'], labels: { style: { fontSize: '10px', fontWeight: 600, colors: '#6F6B7D' } } },
  yaxis: { labels: { style: { fontSize: '10px', colors: '#6F6B7D' } } },
  colors: ['#FF9F43'],
  plotOptions: { bar: { horizontal: true, columnWidth: '50%', borderRadius: 4 } },
  dataLabels: { enabled: true, style: { fontSize: '10px', fontWeight: 700, colors: ['#4B465C'] } },
  grid: { borderColor: '#F1F0F2' },
}))
const expiryTimelineSeries = computed(() => [
  { name: 'Expiring Contracts', data: [3, 5, 2, 4, 8, 12] },
])

function contractTypeName(id) {
  if (!id) return 'General'
  const t = contractTypes.value.find(ct => ct.id == id)
  return t ? t.name : `Type #${id}`
}

async function loadContractTypes() {
  try {
    const res = await axios.get(`${BASE}/contract-types`)
    contractTypes.value = res.data || []
  } catch { contractTypes.value = [] }
}

async function loadClients() {
  try {
    const res = await axios.get(`${BASE}/clients?per_page=1000`)
    clients.value = res.data.clients?.data || []
  } catch { clients.value = [] }
}

async function load() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: perPage.value, search: search.value }
    if (statusFilter.value) params.status = statusFilter.value
    const res = await axios.get(`${BASE}/contracts`, { params })
    contracts.value = res.data.contracts?.data || []
    totalPages.value = res.data.contracts?.last_page || 1
    stats.value = res.data.stats || {}
  } catch {
    contracts.value = []
    stats.value = { total: 0, active: 0, expired: 0, about_to_expire: 0, recently_added: 0, trash: 0, total_value: 0 }
  } finally { loading.value = false }
}

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; load() }, 350)
}

function openCreate() {
  if (!canCreateContract.value) return
  editing.value = null
  Object.assign(form, {
    subject: '', client_id: '', contract_type_id: '', value: '',
    start_date: new Date().toISOString().slice(0, 10), end_date: '',
    status: 'Not Started', description: '', signed: false, trash: false, hide_from_customer: false,
  })
  showDrawer.value = true
}

function editContract(con) {
  if (!canEditContract.value) return
  editing.value = con
  Object.assign(form, {
    subject: con.subject,
    client_id: con.client_id || '',
    contract_type_id: String(con.contract_type_id || ''),
    value: con.value || '',
    start_date: con.start_date?.slice?.(0, 10) || '',
    end_date: con.end_date?.slice?.(0, 10) || '',
    status: con.status || 'Not Started',
    description: con.description || '',
    signed: !!con.signed,
    trash: !!con.trash,
    hide_from_customer: !!con.hide_from_customer,
  })
  showDrawer.value = true
}

async function save() {
  if (!form.subject) return alert('Subject is required')
  if (!form.client_id) return alert('Customer is required')
  if (!form.start_date) return alert('Start date is required')
  saving.value = true
  try {
    if (editing.value) {
      await axios.put(`${BASE}/contracts/${editing.value.id}`, form)
      message.success('Contract updated')
    } else {
      await axios.post(`${BASE}/contracts`, form)
      message.success('Contract created')
    }
    closeDrawer()
    load()
  } catch {
    alert('Failed to save contract')
  } finally { saving.value = false }
}

async function deleteContract(con) {
  if (!canDeleteContract.value) return
  if (!confirm(`Delete contract "${con.subject}"?`)) return
  try {
    await axios.delete(`${BASE}/contracts/${con.id}`)
    message.success('Contract deleted')
    load()
  } catch {
    contracts.value = contracts.value.filter(c => c.id !== con.id)
  }
}

function exportPDF() {
  if (!selected.value.length) return alert('Select contracts to export')
  alert(`Exporting ${selected.value.length} contract(s) as PDF...`)
}

function toggleAll() { selected.value = selectAll.value ? contracts.value.map(c => c.id) : [] }
function closeDrawer() { showDrawer.value = false; editing.value = null }
function isExpired(d) { return d && new Date(d) < new Date() }
function fmt(v) { return Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }
function fmtDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }
function truncate(s, n) { return s?.length > n ? s.slice(0, n) + '...' : s }

onMounted(() => { load(); loadContractTypes(); loadClients() })
</script>

<style scoped>
.contracts-page {
  font-family: 'Public Sans', 'Inter', -apple-system, sans-serif;
  background: #f8fafc;
  padding: 24px;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: linear-gradient(135deg, #7367F0, #8F85F3);
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 8px 18px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 2px 6px rgba(115, 103, 240, 0.35);
  font-family: inherit;
}
.btn-primary:hover {
  background: linear-gradient(135deg, #685DD8, #8075EC);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(115, 103, 240, 0.45);
}
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

.btn-outline {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #fff;
  border: 1px solid #DBDADE;
  color: #6F6B7D;
  border-radius: 6px;
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
  font-family: inherit;
}
.btn-outline:hover {
  border-color: #7367F0;
  color: #7367F0;
  background: rgba(115, 103, 240, 0.04);
}

/* Form Controls */
.form-ctrl {
  outline: none;
  font-family: inherit;
}
.form-ctrl:focus {
  border-color: #7367F0 !important;
  box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.15) !important;
}

/* Drawer Styling */
:deep(.vuexy-contract-drawer .ant-drawer-header) {
  border-bottom: 1px solid #F1F0F2;
  padding: 16px 24px;
}
:deep(.vuexy-contract-drawer .ant-drawer-body) {
  padding: 20px 24px;
  background: #FAFAFB;
}
:deep(.vuexy-contract-drawer .ant-drawer-footer) {
  border-top: 1px solid #F1F0F2;
  padding: 12px 24px;
  background: #FFFFFF;
}

@media (max-width: 1024px) {
  .contracts-page { padding: 16px; }
}
</style>
