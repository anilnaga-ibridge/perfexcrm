<template>
  <div class="invoice-form-page space-y-6">

    <!-- Premium Breadcrumb -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-2 text-xs text-slate-400">
        <router-link to="/admin/invoices" class="hover:text-indigo-600 font-semibold">Invoices</router-link>
        <span>/</span>
        <span class="text-slate-600 font-semibold">Create New Invoice</span>
      </div>
      <router-link to="/admin/invoices" class="text-xs text-indigo-600 hover:underline font-semibold flex items-center gap-1">
        &larr; Back to List
      </router-link>
    </div>

    <!-- Main Premium Card -->
    <div class="premium-form-card bg-white border border-slate-100 rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05),_0_2px_4px_-1px_rgba(0,0,0,0.03)] overflow-hidden">
      <!-- Title Header -->
      <div class="px-8 py-5 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-slate-50/80 to-white">
        <div class="flex items-center space-x-3">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#d35400] via-[#7e1e8e] to-[#0b579f]"></div>
          <h2 class="text-base font-extrabold text-slate-800 tracking-tight">Create New Invoice</h2>
        </div>
      </div>

      <div class="p-8 space-y-8">

        <!-- Merge Candidates Warning -->
        <div v-if="mergeCandidates.length > 0" class="merge-section px-5 py-4 bg-gradient-to-r from-amber-50 to-amber-50/40 border border-amber-200 rounded-xl">
          <div class="flex items-center gap-2 mb-3">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="text-amber-600"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span class="text-xs font-bold text-amber-800 uppercase tracking-wider">Invoices Available for Merging</span>
          </div>
          <div class="flex flex-wrap gap-2 mb-3">
            <label v-for="inv in mergeCandidates" :key="inv.id" class="merge-item flex items-center gap-2 px-3 py-1.5 bg-white border border-amber-200/60 rounded-lg text-xs font-medium text-slate-700 hover:border-amber-300 cursor-pointer transition-all">
              <input type="checkbox" v-model="selectedMergeIds" :value="inv.id" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500 w-3.5 h-3.5" />
              <span>{{ inv.number }} - {{ formatCurrency(inv.total) }}</span>
            </label>
          </div>
          <label class="flex items-center gap-2 text-[11px] font-semibold text-slate-500 cursor-pointer">
            <input type="checkbox" v-model="markMergedAsCancelled" class="rounded border-slate-300 text-slate-600 focus:ring-slate-500 w-3.5 h-3.5" />
            Mark merged invoices as cancelled instead of deleting
          </label>
        </div>

        <!-- Two-Column Fields Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

          <!-- Left Column: Customer & Addresses -->
          <div class="p-6 bg-slate-50/30 rounded-xl border border-slate-100/80 space-y-5">
            <div class="flex items-center space-x-2 pb-2 border-b border-slate-100">
              <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">01</span>
              <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Customer &amp; Address</span>
            </div>

            <!-- Customer Select -->
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                <span class="text-rose-500">*</span> Customer
              </label>
              <a-select
                v-model:value="form.client_id"
                show-search
                :filter-option="(input, option) => option.label.toLowerCase().includes(input.toLowerCase())"
                placeholder="Search and select a customer..."
                style="width: 100%"
                size="large"
                @change="onClientChange"
                class="premium-customer-select"
              >
                <a-select-option v-for="c in clientOptions" :key="c.id" :value="c.id" :label="c.company">
                  <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-[10px] font-bold flex-shrink-0">
                      {{ c.company?.charAt(0) }}
                    </div>
                    <div>
                      <div class="text-xs font-semibold text-slate-800">{{ c.company }}</div>
                      <div class="text-[10px] text-slate-400">{{ c.city || '' }}{{ c.country ? ', ' + c.country : '' }}</div>
                    </div>
                  </div>
                </a-select-option>
              </a-select>
            </div>

            <!-- Client Info Badge -->
            <div v-if="selectedClient" class="flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-indigo-50/60 to-white border border-indigo-100 rounded-xl">
              <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold flex-shrink-0">
                {{ selectedClient.company?.charAt(0) }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-xs font-bold text-slate-800 truncate">{{ selectedClient.company }}</div>
                <div class="flex items-center gap-3 text-[10px] text-slate-400 mt-0.5">
                  <span v-if="selectedClient.email" class="flex items-center gap-1">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="10" height="10"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    {{ selectedClient.email }}
                  </span>
                  <span v-if="selectedClient.phone" class="flex items-center gap-1">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="10" height="10"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    {{ selectedClient.phone }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Address Block -->
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Address Details</label>
                <button
                  type="button"
                  class="text-indigo-600 hover:text-indigo-800 cursor-pointer flex items-center gap-1 text-[10.5px] font-bold transition-colors"
                  @click="openAddressModal"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                  Edit billing &amp; shipping
                </button>
              </div>

              <div class="grid grid-cols-2 gap-6 text-xs text-slate-500 py-3.5 px-4 bg-slate-50/70 rounded-xl border border-slate-100 shadow-inner">
                <div>
                  <div class="font-bold text-slate-700 mb-1.5 flex items-center gap-1">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11" class="text-indigo-600"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    Bill To
                  </div>
                  <div class="font-medium text-slate-600 space-y-0.5 leading-relaxed">
                    <div>{{ form.billing_street || '--' }}</div>
                    <div>{{ form.billing_city || '--' }}{{ form.billing_state ? ', ' + form.billing_state : '' }}</div>
                    <div>{{ form.billing_country || '--' }}{{ form.billing_zip ? ', ' + form.billing_zip : '' }}</div>
                  </div>
                </div>
                <div class="border-l border-slate-200/60 pl-6">
                  <div class="font-bold text-slate-700 mb-1.5 flex items-center gap-1">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11" class="text-indigo-600"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    Ship to
                  </div>
                  <div class="font-medium text-slate-600 space-y-0.5 leading-relaxed">
                    <div>{{ form.shipping_street || '--' }}</div>
                    <div>{{ form.shipping_city || '--' }}{{ form.shipping_state ? ', ' + form.shipping_state : '' }}</div>
                    <div>{{ form.shipping_country || '--' }}{{ form.shipping_zip ? ', ' + form.shipping_zip : '' }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Payment Modes -->
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Payment Modes</label>
              <div class="flex flex-wrap gap-2">
                <label v-for="mode in paymentModes" :key="mode" class="payment-chip"
                  :class="{ 'payment-chip-active': form.allowed_payment_modes_list.includes(mode) }">
                  <input type="checkbox" v-model="form.allowed_payment_modes_list" :value="mode" style="display:none" />
                  <svg v-if="form.allowed_payment_modes_list.includes(mode)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="10" height="10"><polyline points="20 6 9 17 4 12"/></svg>
                  <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="10" height="10"><circle cx="12" cy="12" r="10"/></svg>
                  <span>{{ mode }}</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Right Column: Invoice Info -->
          <div class="p-6 bg-slate-50/30 rounded-xl border border-slate-100/80 space-y-5">
            <div class="flex items-center space-x-2 pb-2 border-b border-slate-100">
              <span class="text-[10px] font-bold text-violet-600 bg-violet-50 px-2 py-0.5 rounded">02</span>
              <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Invoice Details</span>
            </div>

            <!-- Invoice Number -->
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                <span class="text-rose-500">*</span> Invoice Number
              </label>
              <div class="flex shadow-sm rounded-lg overflow-hidden border border-slate-200 focus-within:border-indigo-500 focus-within:ring-3 focus-within:ring-indigo-500/12 transition-all">
                <span class="inline-flex items-center px-3.5 bg-slate-50 text-slate-400 text-xs font-bold border-r border-slate-200">
                  INV-
                </span>
                <input
                  type="text"
                  class="w-full text-xs h-10 px-3.5 bg-white border-0 outline-none text-slate-700 font-semibold"
                  v-model="invoiceNumberSuffix"
                  placeholder="000001"
                />
              </div>
            </div>

            <!-- Date Pickers -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                  <span class="text-rose-500">*</span> Invoice Date
                </label>
                <a-date-picker
                  v-model:value="form.date"
                  :value-format="'YYYY-MM-DD'"
                  format="DD/MM/YYYY"
                  style="width: 100%"
                  size="large"
                  class="premium-datepicker"
                  placeholder="Select date"
                />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Due Date</label>
                <a-date-picker
                  v-model:value="form.duedate"
                  :value-format="'YYYY-MM-DD'"
                  format="DD/MM/YYYY"
                  style="width: 100%"
                  size="large"
                  class="premium-datepicker"
                  placeholder="Select date"
                />
              </div>
            </div>

            <!-- Prevent Overdue -->
            <label class="flex items-center gap-2 text-[11px] font-semibold text-slate-500 cursor-pointer pt-1">
              <input type="checkbox" v-model="form.prevent_overdue_reminders" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5" />
              Prevent sending overdue reminders for this invoice
            </label>

            <!-- Tags -->
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Tags</label>
              <a-select
                v-model:value="form.tags"
                mode="tags"
                placeholder="Add tags..."
                style="width: 100%"
                size="large"
                class="premium-tags-select"
              >
              </a-select>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                  <span class="text-rose-500">*</span> Currency
                </label>
                <div class="relative">
                  <select class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all appearance-none cursor-pointer pr-10" v-model="form.currency">
                    <option value="USD">USD $</option>
                    <option value="EUR">EUR €</option>
                    <option value="GBP">GBP £</option>
                    <option value="CAD">CAD $</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Sale Agent</label>
                <div class="relative">
                  <select class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all appearance-none cursor-pointer pr-10" v-model="form.sale_agent">
                    <option value="">Select agent...</option>
                    <option v-for="s in staffOptions" :key="s.id" :value="s.name">{{ s.name }}</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Recurring Invoice?</label>
                <div class="relative">
                  <select class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all appearance-none cursor-pointer pr-10" v-model="form.recurring_type">
                    <option value="no">No</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="yearly">Yearly</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Discount Type</label>
                <div class="relative">
                  <select class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all appearance-none cursor-pointer pr-10" v-model="form.discount_type" @change="recalc">
                    <option value="no_discount">No discount</option>
                    <option value="before_tax">Before Tax</option>
                    <option value="after_tax">After Tax</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
            </div>

            <!-- Admin Note -->
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Admin Note</label>
              <textarea
                class="form-ctrl text-xs p-3 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all min-h-[72px]"
                rows="3"
                v-model="form.admin_note"
                placeholder="Admin note"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Items Section -->
        <div class="space-y-6 pt-6 border-t border-slate-100">

          <div class="flex items-center space-x-2 pb-2">
            <span class="text-[10px] font-bold text-sky-600 bg-sky-50 px-2 py-0.5 rounded">03</span>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Invoice Items</span>
          </div>

          <!-- Qty Mode -->
          <div class="flex items-center space-x-3 bg-slate-100/80 p-1 rounded-xl w-fit shadow-inner">
            <span class="text-[10px] font-bold text-slate-400 px-2.5 uppercase tracking-wider">Show qty as:</span>
            <div class="flex space-x-1">
              <button
                type="button"
                class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                :class="form.qty_display_mode === 'qty' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                @click="form.qty_display_mode = 'qty'"
              >Qty</button>
              <button
                type="button"
                class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                :class="form.qty_display_mode === 'hours' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                @click="form.qty_display_mode = 'hours'"
              >Hours</button>
              <button
                type="button"
                class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                :class="form.qty_display_mode === 'qty_hours' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                @click="form.qty_display_mode = 'qty_hours'"
              >Qty/Hours</button>
            </div>
          </div>

          <!-- Items Table -->
          <div class="overflow-x-auto rounded-xl border border-slate-100 shadow-sm">
            <table class="items-table text-xs">
              <thead>
                <tr class="bg-gradient-to-r from-slate-50 to-white">
                  <th class="w-56">Item</th>
                  <th>Description</th>
                  <th class="w-20 text-center">{{ qtyLabel }}</th>
                  <th class="w-28 text-right">Rate</th>
                  <th class="w-28 text-center">Tax</th>
                  <th class="w-32 text-right pr-5">Amount</th>
                  <th class="w-12 text-center"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, idx) in form.items" :key="idx" class="added-item-row group hover:bg-gradient-to-r hover:from-slate-50 hover:to-white transition-all duration-150">
                  <td class="p-3">
                    <input type="text" class="item-ctrl text-xs p-2 bg-white border-slate-200 focus:border-indigo-500 rounded-lg w-full" v-model="item.description" placeholder="Description" />
                    <input type="text" class="item-ctrl text-[11px] p-2 bg-white border-slate-200 focus:border-indigo-500 rounded-lg w-full mt-1.5 text-slate-400" v-model="item.long_description" placeholder="Long description (optional)" />
                  </td>
                  <td class="p-3"></td>
                  <td class="p-3">
                    <input type="number" class="item-ctrl text-xs text-center h-8 bg-white border-slate-200 rounded-md w-full" v-model.number="item.qty" min="0.01" step="0.01" @input="recalc" />
                  </td>
                  <td class="p-3">
                    <input type="number" class="item-ctrl text-xs text-right h-8 bg-white border-slate-200 rounded-md font-semibold w-full" v-model.number="item.rate" min="0" step="0.01" @input="recalc" placeholder="0.00" />
                  </td>
                  <td class="p-3">
                    <div class="relative">
                      <select class="item-ctrl text-xs bg-white h-8 border-slate-200 rounded-md appearance-none cursor-pointer pr-7 font-medium w-full" v-model="item.tax_rate" @change="recalc">
                        <option :value="null">—</option>
                        <option :value="5">5.00%</option>
                        <option :value="10">10.00%</option>
                        <option :value="18">18.00%</option>
                      </select>
                      <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="6 9 12 15 18 9"/></svg>
                      </div>
                    </div>
                  </td>
                  <td class="p-3 text-right font-bold text-slate-800 pr-4">{{ formatCurrency(itemAmount(item)) }}</td>
                  <td class="p-3 text-center">
                    <button class="text-slate-300 hover:text-rose-500 hover:bg-rose-50 rounded-lg w-7 h-7 flex items-center justify-center border border-transparent hover:border-rose-100 cursor-pointer transition-all mx-auto opacity-0 group-hover:opacity-100" @click="removeItem(idx)" v-if="form.items.length > 1">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Add Item Button -->
          <button type="button" class="inline-flex items-center gap-2 px-5 py-2.5 border-2 border-dashed border-slate-200 rounded-xl text-xs font-bold text-slate-500 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50/30 cursor-pointer transition-all" @click="addItem">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Item
          </button>

          <!-- Totals Card -->
          <div class="flex justify-end pt-4">
            <div class="w-80 bg-gradient-to-br from-slate-50 to-white border border-slate-100 rounded-2xl p-5 space-y-3.5 shadow-sm">
              <div class="flex justify-between items-center text-xs pb-2.5 border-b border-slate-200/50">
                <span class="text-slate-500 font-semibold">Sub Total</span>
                <span class="font-bold text-slate-800 text-sm">{{ formatCurrency(subtotal) }}</span>
              </div>

              <div v-if="form.discount_type !== 'no_discount'" class="flex justify-between items-center text-xs pb-2.5 border-b border-slate-200/50">
                <span class="text-slate-500 font-semibold">Discount</span>
                <div class="flex items-center space-x-1.5">
                  <input
                    type="number"
                    min="0"
                    max="100"
                    class="border border-slate-200 rounded-md text-xs text-right w-14 h-7 pr-1 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                    v-model.number="form.discount_percent"
                    @input="recalc"
                  />
                  <span class="text-xs font-semibold text-slate-500 bg-slate-200/50 rounded px-1.5 py-0.5">%</span>
                </div>
                <span class="font-bold text-rose-500">-{{ formatCurrency(discountVal) }}</span>
              </div>

              <div class="flex justify-between items-center text-xs pb-2.5 border-b border-slate-200/50">
                <span class="text-slate-500 font-semibold">Adjustment</span>
                <input
                  type="number"
                  class="border border-slate-200 rounded-md text-xs text-right w-20 h-7 pr-1 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                  v-model.number="form.adjustment"
                  @input="recalc"
                />
                <span class="font-bold text-slate-800">{{ formatCurrency(form.adjustment || 0) }}</span>
              </div>

              <div class="flex justify-between items-center pt-1.5">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Total</span>
                <span class="text-xl text-slate-900 font-extrabold tracking-tight">{{ formatCurrency(totalAmount) }}</span>
              </div>
            </div>
          </div>

          <!-- Client Note & Terms Gradient Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-slate-100">
            <div class="p-5 bg-gradient-to-br from-slate-50/50 to-white border border-slate-100 rounded-xl space-y-2">
              <div class="flex items-center gap-2 pb-1">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" class="text-indigo-500"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Client Note</label>
              </div>
              <textarea
                class="form-ctrl text-xs p-3 bg-white hover:bg-white border-slate-200 focus:bg-white rounded-lg transition-all min-h-[100px] resize-none"
                rows="4"
                v-model="form.client_note"
                placeholder="Write a note visible to the recipient..."
              ></textarea>
            </div>
            <div class="p-5 bg-gradient-to-br from-slate-50/50 to-white border border-slate-100 rounded-xl space-y-2">
              <div class="flex items-center gap-2 pb-1">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" class="text-indigo-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Terms &amp; Conditions</label>
              </div>
              <textarea
                class="form-ctrl text-xs p-3 bg-white hover:bg-white border-slate-200 focus:bg-white rounded-lg transition-all min-h-[100px] resize-none text-slate-700"
                rows="4"
                v-model="form.terms_conditions"
                placeholder="Standard contract terms and conditions..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Premium Action Bar Footer -->
        <div class="border-t border-slate-200 bg-gradient-to-r from-slate-50 to-white px-8 py-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 text-xs text-slate-400 rounded-b-2xl -mx-8 -mb-8 mt-8">
          <div class="flex items-center space-x-2">
            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm1 14h-2v-4h2zm0-6h-2V7h2z"/></svg>
            </span>
            <span class="text-slate-500 font-medium">Generate and save invoices for clients</span>
          </div>
          <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
            <button type="button" class="px-5 py-2.5 border border-slate-200 rounded-xl text-xs font-bold bg-white text-slate-600 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-800 cursor-pointer transition-all hover:-translate-y-0.5 active:translate-y-0 shadow-sm" @click="router.push('/admin/invoices')">
              <span class="flex items-center gap-2">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Cancel
              </span>
            </button>
            <button type="button" class="px-6 py-2.5 bg-gradient-to-br from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white rounded-xl text-xs font-bold cursor-pointer transition-all hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 shadow-md disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0" :disabled="saving" @click="submitInvoice">
              <span class="flex items-center gap-2">
                <svg v-if="saving" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="animate-spin"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                {{ saving ? 'Saving...' : 'Save Invoice' }}
              </span>
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- Address Edit Modal -->
    <transition name="fade">
      <div v-if="showAddressModal" class="modal-overlay" @click.self="closeAddressModal">
        <div class="modal-card border border-slate-100 rounded-2xl shadow-[0_20px_25px_-5px_rgba(0,0,0,0.08)]">
          <div class="modal-head px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
            <div class="flex items-center space-x-2">
              <div class="w-1.5 h-4 rounded-full bg-indigo-600"></div>
              <span class="modal-title font-bold text-slate-800 text-sm">Billing & Shipping Address</span>
            </div>
            <button class="modal-close text-slate-400 hover:text-slate-600 font-bold text-xl cursor-pointer" @click="closeAddressModal">×</button>
          </div>

          <div class="modal-body p-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-xs overflow-y-auto">
            <!-- Billing Address -->
            <div class="space-y-4">
              <div class="border-b border-slate-100 pb-2 flex items-center space-x-2">
                <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">BILLING</span>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Street</label>
                <textarea class="form-ctrl text-xs p-2.5 bg-slate-50/30 focus:bg-white rounded-lg border-slate-200" rows="2" v-model="addressForm.billing_street" placeholder="Street Address"></textarea>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">City</label>
                <input type="text" class="form-ctrl text-xs h-9 px-3 bg-slate-50/30 focus:bg-white rounded-lg border-slate-200" v-model="addressForm.billing_city" placeholder="City" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">State</label>
                <input type="text" class="form-ctrl text-xs h-9 px-3 bg-slate-50/30 focus:bg-white rounded-lg border-slate-200" v-model="addressForm.billing_state" placeholder="State" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Zip Code</label>
                <input type="text" class="form-ctrl text-xs h-9 px-3 bg-slate-50/30 focus:bg-white rounded-lg border-slate-200" v-model="addressForm.billing_zip" placeholder="Zip Code" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Country</label>
                <input type="text" class="form-ctrl text-xs h-9 px-3 bg-slate-50/30 focus:bg-white rounded-lg border-slate-200" v-model="addressForm.billing_country" placeholder="Country" />
              </div>
            </div>

            <!-- Shipping Address -->
            <div class="space-y-4">
              <div class="border-b border-slate-100 pb-1.5 flex items-center space-x-2">
                <span class="text-[10px] font-bold text-violet-600 bg-violet-50 px-2 py-0.5 rounded">SHIPPING</span>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Street</label>
                <textarea class="form-ctrl text-xs p-2.5 bg-slate-50/30 focus:bg-white rounded-lg border-slate-200" rows="2" v-model="addressForm.shipping_street" placeholder="Street Address"></textarea>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">City</label>
                <input type="text" class="form-ctrl text-xs h-9 px-3 bg-slate-50/30 focus:bg-white rounded-lg border-slate-200" v-model="addressForm.shipping_city" placeholder="City" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">State</label>
                <input type="text" class="form-ctrl text-xs h-9 px-3 bg-slate-50/30 focus:bg-white rounded-lg border-slate-200" v-model="addressForm.shipping_state" placeholder="State" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Zip Code</label>
                <input type="text" class="form-ctrl text-xs h-9 px-3 bg-slate-50/30 focus:bg-white rounded-lg border-slate-200" v-model="addressForm.shipping_zip" placeholder="Zip Code" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Country</label>
                <input type="text" class="form-ctrl text-xs h-9 px-3 bg-slate-50/30 focus:bg-white rounded-lg border-slate-200" v-model="addressForm.shipping_country" placeholder="Country" />
              </div>
            </div>
          </div>

          <div class="modal-foot px-6 py-4.5 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/80">
            <button class="btn-outline-sm border border-slate-200 rounded-xl px-4.5 py-2 font-bold hover:bg-slate-100/50 hover:border-slate-300 hover:text-slate-700 bg-white cursor-pointer transition-all" @click="closeAddressModal">
              Cancel
            </button>
            <button class="btn-primary-sm bg-slate-950 hover:bg-black text-white rounded-xl px-5 py-2 font-bold cursor-pointer transition-all hover:shadow-sm" @click="saveAddresses">
              Apply Address
            </button>
          </div>
        </div>
      </div>
    </transition>

  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

export default defineComponent({
  name: 'InvoiceForm',
  setup() {
    const route  = useRoute();
    const router = useRouter();

    const saving = ref(false);
    const clientOptions  = ref([]);
    const staffOptions   = ref([]);
    const selectedClient = ref(null);
    const showAddressModal = ref(false);
    const mergeCandidates   = ref([]);
    const selectedMergeIds  = ref([]);
    const markMergedAsCancelled = ref(false);
    const invoiceNumberSuffix = ref('000001');
    const paymentModes = ['Bank', 'Stripe Checkout'];

    const today = new Date().toISOString().split('T')[0];
    const in30  = new Date(Date.now() + 30 * 86400000).toISOString().split('T')[0];

    const form = reactive({
      client_id: route.query.client_id ? Number(route.query.client_id) : '',
      billing_street: '',
      billing_city: '',
      billing_state: '',
      billing_zip: '',
      billing_country: '',
      shipping_street: '',
      shipping_city: '',
      shipping_state: '',
      shipping_zip: '',
      shipping_country: '',
      date: today,
      duedate: in30,
      prevent_overdue_reminders: false,
      allowed_payment_modes_list: ['Bank', 'Stripe Checkout'],
      currency: 'USD',
      sale_agent: '',
      recurring_type: 'no',
      discount_type: 'no_discount',
      discount_percent: 0,
      adjustment: 0,
      admin_note: '',
      client_note: '',
      terms_conditions: 'Alice knew it was too small, but at last she stretched her arms folded, quietly smoking a long tail, certainly,\' said Alice as he came, \'Oh! the Duchess, the Duchess! Oh! won\'t she be savage if I\'ve kept her eyes filled with cupboards and book-shelves; here and there stood the Queen of Hearts, she.',
      qty_display_mode: 'qty',
      status: 'unpaid',
      tags: [],
      items: [
        { description: '', long_description: '', qty: 1, unit: '', rate: 0, tax_rate: null }
      ],
    });

    // ── Computed ─────────────────────────────────────────────────────
    const qtyLabel = computed(() => {
      const m = form.qty_display_mode;
      return m === 'hours' ? 'Hours' : m === 'qty_hours' ? 'Qty/Hours' : 'Qty';
    });

    const itemAmount = (item) => {
      const base = (item.qty || 0) * (item.rate || 0);
      const tax  = item.tax_rate ? base * item.tax_rate / 100 : 0;
      return base + tax;
    };

    const subtotal = computed(() =>
      form.items.reduce((s, i) => s + (i.qty || 0) * (i.rate || 0), 0)
    );

    const discountVal = computed(() => {
      if (form.discount_type === 'no_discount') return 0;
      return subtotal.value * (form.discount_percent || 0) / 100;
    });

    const totalAmount = computed(() => {
      const taxTotal = form.items.reduce((s, i) => {
        const base = (i.qty || 0) * (i.rate || 0);
        return s + (i.tax_rate ? base * i.tax_rate / 100 : 0);
      }, 0);
      return subtotal.value - discountVal.value + taxTotal + (form.adjustment || 0);
    });

    const formatCurrency = (val) =>
      '$' + parseFloat(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });

    const recalc = () => {};

    // ── Methods ──────────────────────────────────────────────────────
    const addItem = () => {
      form.items.push({ description: '', long_description: '', qty: 1, unit: '', rate: 0, tax_rate: null });
    };

    const removeItem = (idx) => { form.items.splice(idx, 1); };

    const onClientChange = async (value) => {
      const id = value || form.client_id;
      if (!id) { selectedClient.value = null; return; }
      const client = clientOptions.value.find(c => c.id === id);
      selectedClient.value = client || null;
      if (client) {
        form.billing_street  = client.address || '';
        form.billing_city    = client.city || '';
        form.billing_state   = client.state || '';
        form.billing_zip     = client.zip || '';
        form.billing_country = client.country || '';
        form.shipping_street  = client.address || '';
        form.shipping_city    = client.city || '';
        form.shipping_state   = client.state || '';
        form.shipping_zip     = client.zip || '';
        form.shipping_country = client.country || '';
      }
      // Load unpaid invoices for merging
      try {
        const res = await axios.get('/invoices', { params: { client_id: id, status: 'unpaid', per_page: 50 } });
        mergeCandidates.value = res.data.invoices?.data || [];
      } catch { mergeCandidates.value = []; }
    };

    // Address modal
    const addressForm = ref({
      billing_street: '',
      billing_city: '',
      billing_state: '',
      billing_zip: '',
      billing_country: '',
      shipping_street: '',
      shipping_city: '',
      shipping_state: '',
      shipping_zip: '',
      shipping_country: ''
    });

    const openAddressModal = () => {
      addressForm.value = {
        billing_street: form.billing_street || '',
        billing_city: form.billing_city || '',
        billing_state: form.billing_state || '',
        billing_zip: form.billing_zip || '',
        billing_country: form.billing_country || '',
        shipping_street: form.shipping_street || '',
        shipping_city: form.shipping_city || '',
        shipping_state: form.shipping_state || '',
        shipping_zip: form.shipping_zip || '',
        shipping_country: form.shipping_country || ''
      };
      showAddressModal.value = true;
    };

    const closeAddressModal = () => {
      showAddressModal.value = false;
    };

    const saveAddresses = () => {
      form.billing_street = addressForm.value.billing_street;
      form.billing_city = addressForm.value.billing_city;
      form.billing_state = addressForm.value.billing_state;
      form.billing_zip = addressForm.value.billing_zip;
      form.billing_country = addressForm.value.billing_country;
      form.shipping_street = addressForm.value.shipping_street;
      form.shipping_city = addressForm.value.shipping_city;
      form.shipping_state = addressForm.value.shipping_state;
      form.shipping_zip = addressForm.value.shipping_zip;
      form.shipping_country = addressForm.value.shipping_country;
      showAddressModal.value = false;
    };

    const loadData = async () => {
      try {
        const [clientsRes, staffRes, lastInvRes] = await Promise.all([
          axios.get('/clients', { params: { per_page: 500 } }),
          axios.get('/staff'),
          axios.get('/invoices', { params: { per_page: 1, sort: '-id' } }),
        ]);
        clientOptions.value = clientsRes.data.clients?.data || clientsRes.data || [];
        staffOptions.value  = staffRes.data || [];
        // Auto-generate next invoice number
        const lastInvoices = lastInvRes.data.invoices?.data || [];
        if (lastInvoices.length) {
          const lastNum = lastInvoices[0].number || '';
          const numPart = parseInt(lastNum.replace('INV-', '')) || 0;
          invoiceNumberSuffix.value = String(numPart + 1).padStart(6, '0');
        }
        // Pre-select client if passed as query param
        if (form.client_id) {
          await onClientChange();
        }
      } catch (err) {
        console.error('Failed to load form data', err);
      }
    };

    const submitInvoice = async () => {
      if (!form.client_id) { alert('Please select a customer.'); return; }
      if (form.items.some(i => !i.description)) { alert('All items must have a description.'); return; }
      saving.value = true;
      try {
        const payload = {
          ...form,
          number: 'INV-' + invoiceNumberSuffix.value,
          tags: form.tags.join(','),
          allowed_payment_modes: form.allowed_payment_modes_list.join(','),
          subtotal: subtotal.value,
          discount_val: discountVal.value,
          total: totalAmount.value,
          status: 'unpaid',
        };
        delete payload.allowed_payment_modes_list;
        const res = await axios.post('/invoices', payload);
        router.push({ name: 'admin.invoices.view', params: { id: res.data.id } });
      } catch (err) {
        alert(err.response?.data?.message || 'Failed to create invoice.');
      } finally {
        saving.value = false;
      }
    };

    onMounted(loadData);

    return {
      form,
      router,
      saving,
      clientOptions,
      staffOptions,
      selectedClient,
      showAddressModal,
      addressForm,
      mergeCandidates,
      selectedMergeIds,
      markMergedAsCancelled,
      invoiceNumberSuffix,
      paymentModes,
      qtyLabel,
      itemAmount,
      subtotal,
      discountVal,
      totalAmount,
      formatCurrency,
      recalc,
      addItem,
      removeItem,
      onClientChange,
      openAddressModal,
      closeAddressModal,
      saveAddresses,
      submitInvoice,
    };
  },
});
</script>

<style scoped>
@import '@/main/module-shared.css';

.invoice-form-page {
  font-family: 'Inter', -apple-system, sans-serif;
  color: #334155;
}

/* Premium Form Field and Container Styles */
.form-ctrl {
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 8px 14px;
  width: 100%;
  color: #334155;
  outline: none;
  font-family: inherit;
  font-size: 13px;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.form-ctrl:focus {
  border-color: #6366f1;
  background-color: #fff;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
}

.form-ctrl::placeholder {
  color: #94a3b8;
}

/* Premium Customer Select Overrides */
:deep(.premium-customer-select .ant-select-selector) {
  height: 44px !important;
  border-radius: 10px !important;
  padding: 4px 12px !important;
  border-color: #e2e8f0 !important;
  background: #f8fafc !important;
}
:deep(.premium-customer-select:hover .ant-select-selector) {
  border-color: #cbd5e1 !important;
  background: #f8fafc !important;
}
:deep(.premium-customer-select.ant-select-focused .ant-select-selector) {
  border-color: #6366f1 !important;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
  background: #fff !important;
}

/* Premium Datepicker Overrides */
:deep(.premium-datepicker .ant-picker) {
  height: 40px;
  border-radius: 10px;
  border-color: #e2e8f0;
  background: #f8fafc;
  padding: 4px 12px;
  transition: all 0.2s;
}
:deep(.premium-datepicker .ant-picker:hover) {
  border-color: #cbd5e1;
  background: #f8fafc;
}
:deep(.premium-datepicker .ant-picker-focused) {
  border-color: #6366f1 !important;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
  background: #fff !important;
}
:deep(.premium-datepicker .ant-picker input) {
  font-size: 13px;
  color: #334155;
}

/* Premium Tags Select Overrides */
:deep(.premium-tags-select .ant-select-selector) {
  min-height: 40px !important;
  border-radius: 10px !important;
  border-color: #e2e8f0 !important;
  background: #f8fafc !important;
  padding: 2px 8px !important;
}
:deep(.premium-tags-select.ant-select-focused .ant-select-selector) {
  border-color: #6366f1 !important;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
  background: #fff !important;
}
:deep(.premium-tags-select .ant-select-selection-placeholder) {
  font-size: 12px;
  color: #94a3b8;
}
:deep(.premium-tags-select .ant-tag) {
  font-size: 11px;
  font-weight: 600;
  border-radius: 6px;
  padding: 1px 8px;
  margin: 2px;
}

/* Ant-Design Select premium overrides (global within component) */
:deep(.ant-select-selector) {
  border-radius: 8px !important;
  border-color: #e2e8f0 !important;
  height: 40px !important;
  display: flex !important;
  align-items: center !important;
  padding: 0 12px !important;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
}
:deep(.ant-select:hover .ant-select-selector) {
  border-color: #cbd5e1 !important;
}
:deep(.ant-select-focused .ant-select-selector) {
  border-color: #6366f1 !important;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
}
:deep(.ant-select-selection-item) {
  font-size: 12px !important;
  font-weight: 500 !important;
  color: #334155 !important;
}
:deep(.ant-select-selection-placeholder) {
  font-size: 12px !important;
  color: #94a3b8 !important;
}

/* Payment Mode Chips Premium */
.payment-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11.5px;
  font-weight: 600;
  padding: 5px 12px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  cursor: pointer;
  user-select: none;
  transition: all 0.15s;
  color: #64748b;
  background: #fff;
}
.payment-chip:hover {
  border-color: #cbd5e1;
  background: #f8fafc;
}
.payment-chip-active {
  background: #eef2ff;
  border-color: #a5b4fc;
  color: #4f46e5;
}

/* Items Table */
.items-table {
  width: 100%;
  border-collapse: collapse;
}

.items-table th {
  padding: 12px 16px;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: .05em;
  text-align: left;
  border-bottom: 1px solid #f1f5f9;
}

.items-table td {
  border-bottom: 1px solid #f1f5f9;
  vertical-align: top;
  padding: 12px 16px;
}

.items-table tbody tr:last-child td {
  border-bottom: none;
}

.item-ctrl {
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  outline: none;
  font-family: inherit;
  transition: border-color 0.15s;
}

.item-ctrl:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
}

/* Modal overlay and card styling */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(15, 23, 42, 0.45);
  backdrop-filter: blur(2px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 1rem;
}

.modal-card {
  background: #ffffff;
  border-radius: 16px;
  width: 100%;
  max-width: 680px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
  max-height: calc(100vh - 2rem);
  overflow: hidden;
}

.modal-head {
  padding: 18px 24px;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #f8fafc;
}

.modal-title {
  font-size: 14px;
  font-weight: 700;
  color: #1e293b;
}

.modal-close {
  background: transparent;
  border: none;
  font-size: 20px;
  font-weight: 700;
  color: #94a3b8;
  cursor: pointer;
  line-height: 1;
}
.modal-close:hover {
  color: #475569;
}

.modal-body {
  padding: 24px;
  overflow-y: auto;
}

.modal-foot {
  padding: 16px 24px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  background: #f8fafc;
}

.btn-primary-sm {
  background: #1e293b;
  color: #fff;
  border: 1px solid #1e293b;
  border-radius: 8px;
  padding: 10px 24px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-outline-sm {
  background: #fff;
  color: #475569;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 10px 22px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

/* Fade transition for modal */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Remove spinner arrows from number inputs */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  opacity: 0.3;
}
input[type="number"]:focus::-webkit-inner-spin-button,
input[type="number"]:focus::-webkit-outer-spin-button {
  opacity: 0.6;
}

/* Disabled styling */
.form-ctrl:disabled {
  background-color: #f1f5f9;
  border-color: #e2e8f0;
  color: #94a3b8;
  cursor: not-allowed;
}
</style>
