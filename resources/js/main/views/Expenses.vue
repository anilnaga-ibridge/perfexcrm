<template>
  <div class="expenses-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Expenses</h1>
        <p class="page-subtitle">Track and manage business expenses</p>
      </div>
      <button v-if="canCreate" class="btn-primary" @click="openCreate">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Record Expense
      </button>
    </div>

    <!-- Summary Cards (Pure White with Vuexy Accent Badges) -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3.5 mb-5">
      <div
        v-for="card in summaryCards"
        :key="card.label"
        class="bg-white border border-[#EBE9F1] rounded-lg p-4 shadow-[0_2px_4px_rgba(47,43,61,0.04)] hover:shadow-[0_4px_12px_rgba(47,43,61,0.08)] transition-all flex items-center gap-3.5"
      >
        <div
          class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
          :style="{ background: card.bg, color: card.color }"
          v-html="card.icon"
        ></div>
        <div class="min-w-0">
          <div class="text-base font-bold text-[#4B465C] tracking-tight truncate">{{ card.value }}</div>
          <div class="text-[11px] font-semibold text-[#A8AAAE] uppercase tracking-wider mt-0.5 truncate">{{ card.label }}</div>
        </div>
      </div>
    </div>

    <!-- Filters & Actions Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mb-4 bg-white p-3 border border-[#EBE9F1] rounded-lg shadow-sm">
      <div class="flex items-center gap-2">
        <select class="form-ctrl text-xs h-[34px] px-2.5 bg-white border border-[#DBDADE] rounded-md text-[#4B465C] font-medium" v-model="perPage" @change="load">
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
        </select>
        <button class="btn-outline h-[34px] px-3 text-xs font-semibold flex items-center gap-1.5" @click="exportPDF">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Export CSV
        </button>
      </div>
      <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
        <select class="form-ctrl text-xs h-[34px] px-3 bg-white border border-[#DBDADE] rounded-md text-[#4B465C] font-medium" v-model="statusFilter" @change="load">
          <option value="">All Status</option>
          <option value="billed">Billed</option>
          <option value="unbilled">Unbilled</option>
        </select>
        <div class="relative flex-1 sm:w-60">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-[#A8AAAE] pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input
            v-model="search"
            placeholder="Search expenses..."
            class="form-ctrl text-xs h-[34px] pl-8 pr-3 bg-white border border-[#DBDADE] rounded-md text-[#4B465C] w-full"
            @input="onSearch"
          />
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white border border-[#EBE9F1] rounded-lg shadow-sm overflow-hidden">
      <!-- Desktop Table View -->
      <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse">
          <thead>
            <tr class="bg-[#F8F7FA] text-[#6F6B7D] border-b border-[#EBE9F1]">
              <th class="w-12 text-center py-3 px-3 font-semibold uppercase text-[11px] tracking-wider">#</th>
              <th class="py-3 px-3.5 text-left font-semibold uppercase text-[11px] tracking-wider">Category</th>
              <th class="py-3 px-3.5 text-right font-semibold uppercase text-[11px] tracking-wider w-28">Amount</th>
              <th class="py-3 px-3.5 text-left font-semibold uppercase text-[11px] tracking-wider">Name</th>
              <th class="py-3 px-2 text-center font-semibold uppercase text-[11px] tracking-wider w-16">Receipt</th>
              <th class="py-3 px-3.5 text-left font-semibold uppercase text-[11px] tracking-wider w-28">Date</th>
              <th class="py-3 px-3.5 text-left font-semibold uppercase text-[11px] tracking-wider">Project</th>
              <th class="py-3 px-3.5 text-left font-semibold uppercase text-[11px] tracking-wider">Customer</th>
              <th class="py-3 px-3.5 text-left font-semibold uppercase text-[11px] tracking-wider w-24">Invoice</th>
              <th class="py-3 px-3.5 text-left font-semibold uppercase text-[11px] tracking-wider">Reference #</th>
              <th class="py-3 px-3 text-center font-semibold uppercase text-[11px] tracking-wider">Payment Mode</th>
              <th class="w-20 text-center py-3 px-3 font-semibold uppercase text-[11px] tracking-wider no-print">Options</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F1F0F2]">
            <tr v-if="loading">
              <td colspan="12" class="text-center py-12 text-[#A8AAAE]">
                <svg class="animate-spin h-6 w-6 text-[#7367F0] mx-auto mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <p class="text-xs m-0">Loading expenses...</p>
              </td>
            </tr>

            <tr
              v-for="(exp, idx) in expenses"
              :key="exp.id"
              class="hover:bg-[#FAF9FB] transition-colors group"
            >
              <td class="text-center text-[#A8AAAE] font-semibold py-3 px-3">
                {{ idx + 1 + (page - 1) * perPage }}
              </td>
              <td class="py-3 px-3.5">
                <span class="inline-flex items-center px-2.5 py-1 rounded text-[11px] font-semibold bg-[#7367F0]/10 text-[#7367F0] whitespace-nowrap">
                  {{ categoryName(exp.category_id) }}
                </span>
              </td>
              <td class="py-3 px-3.5 text-right font-bold text-[#4B465C] text-sm tabular-nums whitespace-nowrap">
                ${{ fmt(exp.amount) }}
              </td>
              <td class="py-3 px-3.5">
                <div class="flex flex-col gap-0.5 max-w-xs">
                  <span class="font-bold text-[#4B465C] text-xs leading-snug">{{ exp.name }}</span>
                  <span v-if="exp.note" class="text-[11px] text-[#A8AAAE] truncate" :title="exp.note">{{ exp.note }}</span>
                </div>
              </td>
              <td class="py-3 px-2 text-center">
                <span v-if="exp.receipt" class="inline-flex items-center justify-center w-7 h-7 rounded bg-[#7367F0]/10 text-[#7367F0] cursor-pointer hover:bg-[#7367F0]/20 transition-colors mx-auto" title="View receipt">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                </span>
                <span v-else class="text-[#DBDADE] font-medium">—</span>
              </td>
              <td class="py-3 px-3.5 text-[#6F6B7D] font-medium whitespace-nowrap">
                {{ fmtDate(exp.date) }}
              </td>
              <td class="py-3 px-3.5 text-[#6F6B7D]">
                <span v-if="exp.project?.name" class="font-semibold text-[#4B465C]">{{ exp.project.name }}</span>
                <span v-else class="text-[#DBDADE]">—</span>
              </td>
              <td class="py-3 px-3.5 text-[#6F6B7D]">
                <span v-if="exp.client?.company" class="font-semibold text-[#7367F0] hover:underline cursor-pointer">
                  {{ exp.client.company }}
                </span>
                <span v-else class="text-[#DBDADE]">—</span>
              </td>
              <td class="py-3 px-3.5">
                <span v-if="exp.invoice?.number" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-[#28C76F]/10 text-[#28C76F]">
                  {{ exp.invoice.number }}
                </span>
                <span v-else class="text-[#DBDADE]">—</span>
              </td>
              <td class="py-3 px-3.5">
                <span v-if="exp.reference" class="font-mono text-[11px] bg-[#F8F7FA] border border-[#DBDADE] text-[#6F6B7D] px-2 py-0.5 rounded font-medium">
                  {{ exp.reference }}
                </span>
                <span v-else class="text-[#DBDADE]">—</span>
              </td>
              <td class="py-3 px-3 text-center">
                <span v-if="exp.payment_mode" class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-semibold bg-[#F8F7FA] border border-[#EBE9F1] text-[#6F6B7D] whitespace-nowrap">
                  {{ exp.payment_mode }}
                </span>
                <span v-else class="text-[#DBDADE]">—</span>
              </td>
              <td class="py-3 px-3 text-center no-print">
                <div class="flex items-center justify-center gap-1">
                  <button
                    v-if="canEdit"
                    @click="editExpense(exp)"
                    class="w-7 h-7 rounded border border-transparent hover:border-[#DBDADE] hover:bg-[#F8F7FA] text-[#A8AAAE] hover:text-[#7367F0] flex items-center justify-center transition-all cursor-pointer bg-transparent"
                    title="Edit Expense"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"/></svg>
                  </button>
                  <button
                    v-if="canDelete"
                    @click="deleteExpense(exp)"
                    class="w-7 h-7 rounded border border-transparent hover:border-rose-200 hover:bg-rose-50 text-[#A8AAAE] hover:text-rose-600 flex items-center justify-center transition-all cursor-pointer bg-transparent"
                    title="Delete Expense"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!loading && !expenses.length">
              <td colspan="12" class="text-center py-12 text-[#A8AAAE]">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" class="mx-auto mb-2 opacity-50"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                <p class="text-xs font-semibold m-0">No expenses found</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Responsive Card View -->
      <div class="mobile-cards-list md:hidden p-3 space-y-3" v-if="!loading">
        <div
          v-for="exp in expenses"
          :key="'m-exp-' + exp.id"
          class="bg-[#F8F7FA] border border-[#EBE9F1] rounded-lg p-3.5 space-y-2 cursor-pointer"
          @click="canEdit ? editExpense(exp) : null"
        >
          <div class="flex items-center justify-between">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-[#7367F0]/10 text-[#7367F0]">
              {{ categoryName(exp.category_id) }}
            </span>
            <span class="font-bold text-[#4B465C] text-sm">${{ fmt(exp.amount) }}</span>
          </div>

          <div class="font-bold text-xs text-[#4B465C] pt-0.5">
            {{ exp.name }}
          </div>
          <div v-if="exp.note" class="text-[11px] text-[#A8AAAE] line-clamp-2">
            {{ exp.note }}
          </div>

          <div class="grid grid-cols-2 gap-2 text-[11px] pt-2 border-t border-[#EBE9F1]">
            <div class="flex items-center gap-1 text-[#6F6B7D]">
              <span class="text-[#A8AAAE]">Date:</span>
              <span class="font-medium">{{ fmtDate(exp.date) }}</span>
            </div>
            <div class="flex items-center justify-end text-[#6F6B7D]">
              <span class="font-semibold">{{ exp.payment_mode || '—' }}</span>
            </div>
            <div class="flex items-center gap-1 text-[#6F6B7D] col-span-2">
              <span class="text-[#A8AAAE]">Customer:</span>
              <span class="font-semibold text-[#7367F0]">{{ exp.client?.company || '—' }}</span>
            </div>
          </div>
        </div>

        <div v-if="!expenses.length" class="text-center p-6 text-[#A8AAAE] text-xs font-semibold">
          No expenses found
        </div>
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

    <!-- Create/Edit Drawer -->
    <a-drawer
      v-model:open="showDrawer"
      placement="right"
      :width="580"
      :destroyOnClose="true"
      class="vuexy-expense-drawer"
    >
      <template #title>
        <div class="flex items-center space-x-3 py-1">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#7367F0] to-[#9F8ED6]"></div>
          <div>
            <h2 class="text-base font-bold text-[#4B465C] m-0">
              {{ editing ? 'Edit Expense' : 'Record Expense' }}
            </h2>
            <p class="text-xs text-[#A8AAAE] m-0 mt-0.5">
              {{ editing ? 'Update existing expense details' : 'Record a new business expense transaction' }}
            </p>
          </div>
        </div>
      </template>

      <div class="p-1 space-y-6">
        <!-- Main Form Card -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 space-y-4 shadow-sm">
          <div class="flex items-center space-x-2 pb-2 border-b border-[#F1F0F2]">
            <span class="text-[11px] font-bold text-[#7367F0] bg-[#7367F0]/10 px-2.5 py-0.5 rounded">01</span>
            <span class="text-xs font-bold uppercase tracking-wider text-[#4B465C]">Expense Information</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
            <!-- Expense Name (Full width) -->
            <div class="md:col-span-2">
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                Expense Name / Description <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                placeholder="e.g. AWS Monthly Invoice"
                class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
              />
            </div>

            <!-- Amount -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                Amount ($) <span class="text-rose-500">*</span>
              </label>
              <div class="relative">
                <input
                  v-model="form.amount"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="0.00"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full font-semibold"
                />
              </div>
            </div>

            <!-- Date -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                Date <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.date"
                type="date"
                class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
              />
            </div>

            <!-- Category -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Category</label>
              <div class="relative">
                <select
                  v-model="form.category_id"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                >
                  <option value="">General</option>
                  <option value="1">Hosting Services</option>
                  <option value="2">Office Rent</option>
                  <option value="3">Travel & Fuel</option>
                  <option value="4">Marketing</option>
                  <option value="5">Consulting Fees</option>
                  <option value="6">Software Licenses</option>
                  <option value="7">Utilities</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
              </div>
            </div>

            <!-- Payment Mode -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Payment Mode</label>
              <div class="relative">
                <select
                  v-model="form.payment_mode"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                >
                  <option value="Credit Card">Credit Card</option>
                  <option value="Bank Transfer">Bank Transfer</option>
                  <option value="Cash">Cash</option>
                  <option value="PayPal">PayPal</option>
                  <option value="Cheque">Cheque</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
              </div>
            </div>

            <!-- Customer (Optional) -->
            <div class="md:col-span-2">
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Customer (Optional)</label>
              <div class="relative">
                <select
                  v-model="form.client_id"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                >
                  <option value="">-- No Customer Associated --</option>
                  <option v-for="c in clients" :key="c.id" :value="c.id">
                    {{ c.company || c.name || ('Client #' + c.id) }}
                  </option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
              </div>
            </div>

            <!-- Reference # -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Reference #</label>
              <input
                v-model="form.reference"
                type="text"
                placeholder="e.g. INV-9901"
                class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
              />
            </div>

            <!-- Status -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Status</label>
              <div class="relative">
                <select
                  v-model="form.status"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                >
                  <option value="unbilled">Unbilled</option>
                  <option value="billed">Billed</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
              </div>
            </div>

            <!-- Note -->
            <div class="md:col-span-2">
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Note</label>
              <textarea
                v-model="form.note"
                rows="3"
                placeholder="Additional notes..."
                class="form-ctrl text-xs p-3 bg-white border-[#DBDADE] rounded-md transition-all min-h-[85px] w-full resize-none"
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
            {{ saving ? 'Saving...' : (editing ? 'Save Changes' : 'Record Expense') }}
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
import { useAuthStore } from '../store/authStore'

const authStore = useAuthStore()
const canCreate = computed(() => authStore.hasPermission('Expenses', 'create'))
const canEdit   = computed(() => authStore.hasPermission('Expenses', 'edit'))
const canDelete = computed(() => authStore.hasPermission('Expenses', 'delete'))

const BASE = '/api'
const expenses   = ref([])
const stats      = ref({})
const loading    = ref(false)
const saving     = ref(false)
const search     = ref('')
const statusFilter = ref('')
const perPage    = ref('25')
const page       = ref(1)
const totalPages = ref(1)
const showDrawer = ref(false)
const editing    = ref(null)
const clients    = ref([])

const form = reactive({
  name: '', amount: '', date: '', category_id: '', client_id: '',
  payment_mode: 'Credit Card', status: 'unbilled', reference: '', note: '',
})

const summaryCards = computed(() => [
  {
    label: 'Total',
    value: '$' + fmt(stats.value.total_amount || 0),
    color: '#7367F0',
    bg: 'rgba(115, 103, 240, 0.1)',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><path d="M12 6v12"/><path d="M8 10c0-1.1.9-2 2-2h4a2 2 0 0 1 2 2v.5"/><path d="M16 14c0 1.1-.9 2-2 2h-4a2 2 0 0 1-2-2v-.5"/></svg>',
  },
  {
    label: 'Billable',
    value: '$' + fmt(stats.value.billable || 0),
    color: '#28C76F',
    bg: 'rgba(40, 199, 111, 0.1)',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polyline points="20 6 9 17 4 12"/></svg>',
  },
  {
    label: 'Non Billable',
    value: '$' + fmt(stats.value.non_billable || 0),
    color: '#6F6B7D',
    bg: 'rgba(111, 107, 125, 0.1)',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
  },
  {
    label: 'Not Invoiced',
    value: '$' + fmt(stats.value.not_invoiced || 0),
    color: '#FF9F43',
    bg: 'rgba(255, 159, 67, 0.1)',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
  },
  {
    label: 'Billed',
    value: '$' + fmt(stats.value.billed || 0),
    color: '#00CFE8',
    bg: 'rgba(0, 207, 232, 0.1)',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
  },
])

const expenseCategories = {
  1: 'Hosting Services',
  2: 'Office Rent',
  3: 'Travel & Fuel',
  4: 'Marketing',
  5: 'Consulting Fees',
  6: 'Software Licenses',
  7: 'Utilities',
}

function categoryName(id) {
  return id ? (expenseCategories[id] || `Category #${id}`) : 'General'
}

async function loadClients() {
  try {
    const res = await axios.get(`${BASE}/clients?per_page=1000`)
    clients.value = res.data.clients?.data || res.data.data || res.data || []
  } catch {}
}

async function load() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: perPage.value, search: search.value }
    if (statusFilter.value) params.status = statusFilter.value
    const res = await axios.get(`${BASE}/expenses`, { params })
    expenses.value  = res.data.expenses?.data || []
    totalPages.value = res.data.expenses?.last_page || 1
    stats.value     = res.data.stats || {}
  } catch {
    expenses.value = []
    stats.value = { total_amount: 0, billable: 0, non_billable: 0, not_invoiced: 0, billed: 0, total: 0 }
  } finally { loading.value = false }
}

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; load() }, 350)
}

function openCreate() {
  if (!canCreate.value) return
  editing.value = null
  Object.assign(form, {
    name: '',
    amount: '',
    date: new Date().toISOString().slice(0,10),
    category_id: '',
    client_id: '',
    payment_mode: 'Credit Card',
    status: 'unbilled',
    reference: '',
    note: ''
  })
  showDrawer.value = true
}

function editExpense(exp) {
  if (!canEdit.value) return
  editing.value = exp
  Object.assign(form, {
    name: exp.name,
    amount: exp.amount,
    date: exp.date?.slice?.(0,10) || exp.date,
    category_id: exp.category_id || '',
    client_id: exp.client_id || '',
    payment_mode: exp.payment_mode || 'Credit Card',
    status: exp.status || 'unbilled',
    reference: exp.reference || '',
    note: exp.note || '',
  })
  showDrawer.value = true
}

async function save() {
  if (!form.name || !form.amount || !form.date) return alert('Name, amount and date are required')
  saving.value = true
  try {
    if (editing.value) {
      await axios.put(`${BASE}/expenses/${editing.value.id}`, form)
      message.success('Expense updated')
    } else {
      await axios.post(`${BASE}/expenses`, form)
      message.success('Expense recorded successfully')
    }
    closeDrawer()
    load()
  } catch {
    alert('Failed to save expense')
  } finally { saving.value = false }
}

async function deleteExpense(exp) {
  if (!canDelete.value) return
  if (!confirm(`Delete "${exp.name}"?`)) return
  try {
    await axios.delete(`${BASE}/expenses/${exp.id}`)
    message.success('Expense deleted')
    load()
  } catch {
    expenses.value = expenses.value.filter(e => e.id !== exp.id)
  }
}

function exportPDF() {
  if (!expenses.value.length) return alert('No expenses to export')
  const headers = ['#', 'Category', 'Amount', 'Name', 'Date', 'Project', 'Customer', 'Invoice', 'Reference #', 'Payment Mode']
  const rows = expenses.value.map((e, i) => [
    i + 1, categoryName(e.category_id), e.amount, e.name,
    e.date, e.project?.name || '', e.client?.company || '',
    e.invoice?.number || '', e.reference || '', e.payment_mode || '',
  ])
  const csv = 'data:text/csv;charset=utf-8,' +
    [headers.join(','), ...rows.map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(','))].join('\n')
  const link = document.createElement('a')
  link.setAttribute('href', encodeURI(csv))
  link.setAttribute('download', 'expenses_export.csv')
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

function closeDrawer() {
  showDrawer.value = false
  editing.value = null
}

function fmt(v) { return Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }
function fmtDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }
function truncate(str, n) { return str?.length > n ? str.slice(0, n) + '...' : str }

onMounted(() => {
  load()
  loadClients()
})
</script>

<style scoped>
.expenses-page {
  font-family: 'Public Sans', 'Inter', -apple-system, sans-serif;
  background: #f8fafc;
  padding: 24px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.page-title {
  font-size: 20px;
  font-weight: 700;
  color: #4B465C;
  margin: 0;
}
.page-subtitle {
  font-size: 13px;
  color: #A8AAAE;
  margin: 2px 0 0;
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

/* Summary Cards */
.exp-stats-row {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 12px;
  margin-bottom: 20px;
}
@media (max-width: 900px) {
  .exp-stats-row { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 600px) {
  .exp-stats-row { grid-template-columns: repeat(2, 1fr); }
}
.exp-stat-card {
  background: #fff;
  border: 1px solid #EBE9F1;
  border-radius: 8px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  transition: all 0.2s;
  box-shadow: 0 2px 4px rgba(47, 43, 61, 0.04);
}
.exp-stat-card:hover {
  border-color: #DBDADE;
  box-shadow: 0 4px 12px rgba(47, 43, 61, 0.08);
  transform: translateY(-1px);
}
.exp-stat-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.exp-stat-info { min-width: 0; }
.exp-stat-val {
  font-size: 17px;
  font-weight: 700;
  line-height: 1.2;
  font-variant-numeric: tabular-nums;
}
.exp-stat-label {
  font-size: 11px;
  font-weight: 600;
  color: #A8AAAE;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-top: 2px;
}

/* Filters */
.exp-filters-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 16px;
}
.exp-filters-left, .exp-filters-right {
  display: flex;
  align-items: center;
  gap: 8px;
}
.exp-filter-select {
  border: 1px solid #DBDADE;
  border-radius: 6px;
  padding: 7px 10px;
  font-size: 12.5px;
  color: #4B465C;
  background: #fff;
  cursor: pointer;
  outline: none;
  font-family: inherit;
}
.exp-filter-select:focus { border-color: #7367F0; ring: 2px rgba(115,103,240,0.15); }
.exp-toolbar-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #fff;
  border: 1px solid #DBDADE;
  border-radius: 6px;
  padding: 7px 12px;
  font-size: 12px;
  font-weight: 600;
  color: #6F6B7D;
  cursor: pointer;
  transition: all 0.12s;
  font-family: inherit;
}
.exp-toolbar-btn:hover { background: #F8F7FA; border-color: #7367F0; color: #7367F0; }
.exp-search-wrap { position: relative; }
.exp-search-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  width: 14px;
  height: 14px;
  color: #A8AAAE;
  pointer-events: none;
}
.exp-search-input {
  border: 1px solid #DBDADE;
  border-radius: 6px;
  padding: 7px 12px 7px 32px;
  font-size: 12.5px;
  color: #4B465C;
  background: #fff;
  width: 220px;
  outline: none;
  transition: border-color 0.12s;
  font-family: inherit;
}
.exp-search-input:focus { border-color: #7367F0; box-shadow: 0 0 0 3px rgba(115,103,240,.12); }

/* Table */
.exp-table-wrap {
  background: #fff;
  border: 1px solid #EBE9F1;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 6px rgba(47,43,61,0.04);
}
.exp-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.exp-table thead th {
  background: #F8F7FA;
  padding: 11px 14px;
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  color: #6F6B7D;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
  border-bottom: 1px solid #EBE9F1;
}
.exp-table tbody td { padding: 12px 14px; border-bottom: 1px solid #F1F0F2; vertical-align: middle; }
.exp-row:last-child td { border-bottom: none; }
.exp-row:hover { background: #FAF9FB; }
.exp-cell-muted { color: #6F6B7D; }

.exp-category-tag {
  background: rgba(115, 103, 240, 0.08);
  color: #7367F0;
  padding: 3px 10px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
}
.exp-amount-cell {
  font-weight: 700;
  color: #4B465C;
  font-size: 13px;
  text-align: right;
  font-variant-numeric: tabular-nums;
}
.exp-name-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.exp-name { font-weight: 600; color: #4B465C; }
.exp-note { font-size: 11px; color: #A8AAAE; }
.exp-receipt-link { cursor: pointer; font-size: 16px; }
.exp-ref { font-family: monospace; font-size: 11.5px; color: #6F6B7D; }
.exp-pm-badge {
  background: #F8F7FA;
  color: #6F6B7D;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
  border: 1px solid #EBE9F1;
}
.exp-action-group {
  display: flex;
  align-items: center;
  gap: 4px;
}
.exp-action-icon {
  background: transparent;
  border: 1px solid #DBDADE;
  border-radius: 6px;
  width: 30px;
  height: 30px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #A8AAAE;
  cursor: pointer;
  transition: all 0.12s;
}
.exp-action-icon:hover { background: #F8F7FA; border-color: #7367F0; color: #7367F0; }

.exp-empty-cell {
  text-align: center;
  padding: 48px 20px;
  color: #A8AAAE;
}

/* Pagination */
.exp-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-top: 1px solid #EBE9F1;
  font-size: 12px;
  color: #6F6B7D;
}
.exp-pagination-info { color: #A8AAAE; }
.exp-pagination-btns { display: flex; gap: 6px; }
.exp-pg-btn {
  background: #fff;
  border: 1px solid #DBDADE;
  border-radius: 6px;
  padding: 5px 12px;
  font-size: 12px;
  color: #6F6B7D;
  cursor: pointer;
  transition: all 0.12s;
  font-family: inherit;
}
.exp-pg-btn:hover:not(:disabled) { background: #F8F7FA; border-color: #7367F0; color: #7367F0; }
.exp-pg-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* Form Controls */
.form-ctrl {
  outline: none;
  font-family: inherit;
}
.form-ctrl:focus {
  border-color: #7367F0 !important;
  box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.15) !important;
}

/* Drawer Custom Styling */
:deep(.vuexy-expense-drawer .ant-drawer-header) {
  border-bottom: 1px solid #F1F0F2;
  padding: 16px 24px;
}
:deep(.vuexy-expense-drawer .ant-drawer-body) {
  padding: 20px 24px;
  background: #FAFAFB;
}
:deep(.vuexy-expense-drawer .ant-drawer-footer) {
  border-top: 1px solid #F1F0F2;
  padding: 12px 24px;
  background: #FFFFFF;
}

/* Mobile Cards List */
.mobile-cards-list {
  display: none;
}

@media (max-width: 1024px) {
  .expenses-page { padding: 16px; }
  .exp-table-wrap { overflow-x: auto; }
}

@media (max-width: 768px) {
  .exp-stats-row {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 10px !important;
  }
  .exp-filters-bar {
    flex-direction: column !important;
    align-items: stretch !important;
    gap: 12px !important;
  }
  .exp-filters-left, .exp-filters-right {
    width: 100% !important;
    justify-content: space-between !important;
    flex-wrap: wrap !important;
  }
  .exp-search-wrap, .exp-search-input {
    width: 100% !important;
  }
  .exp-table {
    display: none !important;
  }
  .mobile-cards-list {
    display: flex !important;
    flex-direction: column;
    gap: 12px;
    padding: 12px;
  }
}

@media (max-width: 560px) {
  .exp-stats-row {
    grid-template-columns: 1fr !important;
  }
}
</style>
