<template>
  <div class="invoice-form-page space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-2 text-xs text-slate-400">
        <router-link :to="{ name: 'admin.credit-notes' }" class="hover:text-indigo-600 font-semibold">Credit Notes</router-link>
        <span>/</span>
        <span class="text-slate-600 font-semibold">{{ isEdit ? 'Edit Credit Note' : 'New Credit Note' }}</span>
      </div>
      <router-link :to="{ name: 'admin.credit-notes' }" class="text-xs text-indigo-600 hover:underline font-semibold flex items-center gap-1">
        &larr; Back to List
      </router-link>
    </div>

    <!-- Main Premium Card -->
    <div class="premium-form-card bg-white border border-slate-100 rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05),_0_2px_4px_-1px_rgba(0,0,0,0.03)] overflow-hidden">
      <!-- Title Header -->
      <div class="px-8 py-5 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-slate-50/80 to-white">
        <div class="flex items-center space-x-3">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#d35400] via-[#7e1e8e] to-[#0b579f]"></div>
          <h2 class="text-base font-extrabold text-slate-800 tracking-tight">{{ isEdit ? 'Edit Credit Note' : 'New Credit Note' }}</h2>
        </div>
      </div>

      <div class="p-8 space-y-8">
        <!-- Two-Column Fields Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Left Column: Customer & Address -->
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
                :disabled="isEdit"
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
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Billing Address</label>
                <button
                  type="button"
                  class="text-indigo-600 hover:text-indigo-800 cursor-pointer flex items-center gap-1 text-[10.5px] font-bold transition-colors"
                  @click="openAddressModal"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                  Edit billing address
                </button>
              </div>

              <div class="text-xs text-slate-500 py-3.5 px-4 bg-slate-50/70 rounded-xl border border-slate-100 shadow-inner">
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
            </div>
          </div>

          <!-- Right Column: Credit Note Details -->
          <div class="p-6 bg-slate-50/30 rounded-xl border border-slate-100/80 space-y-5">
            <div class="flex items-center space-x-2 pb-2 border-b border-slate-100">
              <span class="text-[10px] font-bold text-violet-600 bg-violet-50 px-2 py-0.5 rounded">02</span>
              <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Credit Note Details</span>
            </div>

            <!-- Credit Note Number -->
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                <span class="text-rose-500">*</span> Credit Note Number
              </label>
              <div class="flex shadow-sm rounded-lg overflow-hidden border border-slate-200 focus-within:border-indigo-500 focus-within:ring-3 focus-within:ring-indigo-500/12 transition-all">
                <span class="inline-flex items-center px-3.5 bg-slate-50 text-slate-400 text-xs font-bold border-r border-slate-200">CN-</span>
                <input
                  type="text"
                  class="w-full text-xs h-10 px-3.5 bg-white border-0 outline-none text-slate-700 font-semibold"
                  v-model="form.number"
                  placeholder="000001"
                  :disabled="isEdit"
                />
              </div>
            </div>

            <!-- Date -->
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                <span class="text-rose-500">*</span> Credit Note Date
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

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                  <span class="text-rose-500">*</span> Currency
                </label>
                <div class="relative">
                  <select class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all appearance-none cursor-pointer pr-10" v-model="form.currency">
                    <option value="USD">USD $</option>
                    <option value="EUR">EUR €</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Reference #</label>
                <input
                  type="text"
                  class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all"
                  v-model="form.reference"
                  placeholder="PO Number / Ref"
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Discount Type</label>
                <div class="relative">
                  <select class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all appearance-none cursor-pointer pr-10" v-model="form.discount_type">
                    <option value="no_discount">No discount</option>
                    <option value="before_tax">Before Tax</option>
                    <option value="after_tax">After Tax</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Status</label>
                <div class="relative">
                  <select class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all appearance-none cursor-pointer pr-10" v-model="form.status">
                    <option value="Open">Open</option>
                    <option value="Closed">Closed</option>
                    <option value="Void">Void</option>
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
                placeholder="Admin note (internal)..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Items Section -->
        <div class="space-y-6 pt-6 border-t border-slate-100">
          <div class="flex items-center space-x-2 pb-2">
            <span class="text-[10px] font-bold text-sky-600 bg-sky-50 px-2 py-0.5 rounded">03</span>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Credit Items</span>
          </div>

          <!-- Add Item Row & Qty Mode -->
          <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2 flex-1 max-w-md">
              <a-select
                v-model:value="selectedPredefined"
                placeholder="Add predefined item..."
                style="width: 100%"
                size="large"
                allow-clear
                show-search
                @change="handlePredefinedItemSelect"
                class="predefined-item-select"
              >
                <a-select-option v-for="item in predefinedItems" :key="item.name" :value="item.name">
                  {{ item.name }} ({{ formatCurrency(item.rate) }})
                </a-select-option>
              </a-select>
              <button
                type="button"
                class="w-9 h-9 flex items-center justify-center rounded-lg border-2 border-dashed border-slate-300 text-slate-500 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50/30 cursor-pointer transition-all text-lg font-bold flex-shrink-0"
                @click="addPredefinedItem"
              >+</button>
            </div>

            <div class="flex items-center space-x-3 bg-slate-100/80 p-1 rounded-xl shadow-inner">
              <span class="text-[10px] font-bold text-slate-400 px-2 uppercase tracking-wider">Show qty as:</span>
              <div class="flex space-x-1">
                <button
                  type="button"
                  class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                  :class="form.qty_show === 'Qty' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                  @click="form.qty_show = 'Qty'"
                >Qty</button>
                <button
                  type="button"
                  class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                  :class="form.qty_show === 'Hours' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                  @click="form.qty_show = 'Hours'"
                >Hours</button>
                <button
                  type="button"
                  class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                  :class="form.qty_show === 'Qty/Hours' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                  @click="form.qty_show = 'Qty/Hours'"
                >Qty/Hours</button>
              </div>
            </div>
          </div>

          <!-- Items Table + Add Row -->
          <div class="overflow-x-auto rounded-xl border border-slate-100 shadow-sm">
            <table class="items-table text-xs">
              <thead>
                <tr class="bg-gradient-to-r from-slate-50 to-white">
                  <th class="w-56">Item</th>
                  <th>Description</th>
                  <th class="w-20 text-center">Qty</th>
                  <th class="w-28 text-right">Rate</th>
                  <th class="w-28 text-center">Tax</th>
                  <th class="w-32 text-right pr-5">Amount</th>
                  <th class="w-12 text-center"></th>
                </tr>
              </thead>
              <tbody>
                <!-- Add Item Row -->
                <tr class="bg-slate-50/40">
                  <td class="p-2.5">
                    <textarea class="item-ctrl text-xs min-h-[60px] p-2 bg-white border-slate-200 focus:border-indigo-500 rounded-lg w-full resize-none" rows="2" v-model="newItem.description" placeholder="Description"></textarea>
                  </td>
                  <td class="p-2.5">
                    <textarea class="item-ctrl text-xs min-h-[60px] p-2 bg-white border-slate-200 focus:border-indigo-500 rounded-lg w-full resize-none text-slate-400" rows="2" v-model="newItem.long_description" placeholder="Long description (optional)"></textarea>
                  </td>
                  <td class="p-2.5 text-center">
                    <input type="number" class="item-ctrl text-xs text-center h-8 bg-white border-slate-200 rounded-md w-full" v-model="newItem.qty" min="0.01" step="0.01" />
                    <input type="text" class="item-ctrl text-[10px] text-center mt-1.5 h-7 bg-white border-slate-200 rounded-md w-full placeholder-slate-300" v-model="newItem.unit" placeholder="Unit" />
                  </td>
                  <td class="p-2.5">
                    <input type="number" class="item-ctrl text-xs text-right h-8 bg-white border-slate-200 rounded-md font-semibold w-full" v-model="newItem.rate" placeholder="0.00" />
                  </td>
                  <td class="p-2.5">
                    <select class="item-ctrl text-xs h-8 bg-white border-slate-200 rounded-md appearance-none cursor-pointer font-medium w-full" v-model="newItem.tax_rate">
                      <option :value="null">—</option>
                      <option :value="5">5.00%</option>
                      <option :value="10">10.00%</option>
                      <option :value="18">18.00%</option>
                    </select>
                  </td>
                  <td class="p-2.5 text-right font-semibold text-slate-400 pr-4">{{ formatCurrency((newItem.qty || 1) * (newItem.rate || 0)) }}</td>
                  <td class="p-2.5 text-center">
                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gradient-to-br from-indigo-600 to-purple-700 hover:from-indigo-700 hover:to-purple-800 text-white shadow-sm hover:shadow-md transition-all cursor-pointer mx-auto" @click="addItem">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg>
                    </button>
                  </td>
                </tr>

                <!-- Rendered Items -->
                <tr v-for="(item, idx) in form.items" :key="idx" class="added-item-row group hover:bg-gradient-to-r hover:from-slate-50 hover:to-white transition-all duration-150">
                  <td class="p-3 align-top">
                    <div class="font-bold text-slate-800">{{ item.description }}</div>
                  </td>
                  <td class="p-3 align-top text-slate-500">
                    {{ item.long_description || '—' }}
                  </td>
                  <td class="p-3 align-top text-center font-semibold">
                    {{ item.qty }} <span class="text-[10px] text-slate-400 font-normal">({{ item.unit }})</span>
                  </td>
                  <td class="p-3 align-top text-right font-semibold">
                    {{ formatCurrency(item.rate) }}
                  </td>
                  <td class="p-3 align-top text-center text-slate-500">
                    {{ item.tax_rate > 0 ? item.tax_rate + '%' : '—' }}
                  </td>
                  <td class="p-3 align-top text-right font-bold text-slate-800 pr-4">
                    {{ formatCurrency(itemAmount(item)) }}
                  </td>
                  <td class="p-3 align-top text-center">
                    <button class="text-slate-300 hover:text-rose-500 hover:bg-rose-50 rounded-lg w-7 h-7 flex items-center justify-center border border-transparent hover:border-rose-100 cursor-pointer transition-all mx-auto opacity-0 group-hover:opacity-100" @click="removeItem(idx)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

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
            <span class="text-slate-500 font-medium">Create and manage credit notes for clients</span>
          </div>
          <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
            <button type="button" class="px-5 py-2.5 border border-slate-200 rounded-xl text-xs font-bold bg-white text-slate-600 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-800 cursor-pointer transition-all hover:-translate-y-0.5 active:translate-y-0 shadow-sm" @click="router.push(cancelLink)">
              <span class="flex items-center gap-2">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Cancel
              </span>
            </button>
            <button type="button" class="px-6 py-2.5 bg-gradient-to-br from-indigo-600 to-purple-700 hover:from-indigo-700 hover:to-purple-800 text-white rounded-xl text-xs font-bold cursor-pointer transition-all hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 shadow-md disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0" :disabled="saving" @click="submitCreditNote">
              <span class="flex items-center gap-2">
                <svg v-if="saving" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="animate-spin"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                {{ saving ? 'Saving...' : 'Save Credit Note' }}
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
              <span class="modal-title font-bold text-slate-800 text-sm">Billing Address</span>
            </div>
            <button class="modal-close text-slate-400 hover:text-slate-600 font-bold text-xl cursor-pointer" @click="closeAddressModal">×</button>
          </div>

          <div class="modal-body p-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-xs overflow-y-auto">
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Street</label>
              <textarea class="form-ctrl text-xs p-2.5 bg-white border-slate-200 focus:border-indigo-500 rounded-lg resize-none" rows="3" v-model="form.billing_street"></textarea>
            </div>
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">City</label>
              <input type="text" class="form-ctrl text-xs h-9 px-3 bg-white border-slate-200 focus:border-indigo-500 rounded-lg" v-model="form.billing_city" />
            </div>
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">State</label>
              <input type="text" class="form-ctrl text-xs h-9 px-3 bg-white border-slate-200 focus:border-indigo-500 rounded-lg" v-model="form.billing_state" />
            </div>
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Zip</label>
              <input type="text" class="form-ctrl text-xs h-9 px-3 bg-white border-slate-200 focus:border-indigo-500 rounded-lg" v-model="form.billing_zip" />
            </div>
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Country</label>
              <input type="text" class="form-ctrl text-xs h-9 px-3 bg-white border-slate-200 focus:border-indigo-500 rounded-lg" v-model="form.billing_country" />
            </div>
          </div>

          <div class="modal-foot px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/40 rounded-b-2xl">
            <button class="px-4 py-2 border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-50 cursor-pointer transition-all" @click="closeAddressModal">Cancel</button>
            <button class="px-4 py-2 bg-gradient-to-br from-indigo-600 to-purple-700 text-white rounded-lg text-xs font-bold hover:from-indigo-700 hover:to-purple-800 cursor-pointer transition-all shadow-sm" @click="closeAddressModal">Save Address</button>
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
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'CreditNoteForm',
  setup() {
    const route = useRoute();
    const router = useRouter();

    const isEdit = ref(false);
    const saving = ref(false);
    const clientOptions = ref([]);
    const selectedClient = ref(null);
    const showAddressModal = ref(false);
    const selectedPredefined = ref('');

    // Predefined items options
    const predefinedItems = ref([
      { name: 'Consulting SLA Services', rate: 1500 },
      { name: 'App Development Module v1.0', rate: 8500 },
      { name: 'Cloud Infrastructure Audit Pack', rate: 3200 },
      { name: 'UI/UX Interactive Mockup Designs', rate: 2400 }
    ]);

    const today = new Date().toISOString().split('T')[0];

    const form = reactive({
      client_id: route.query.client_id ? Number(route.query.client_id) : '',
      billing_street: '',
      billing_city: '',
      billing_state: '',
      billing_zip: '',
      billing_country: '',
      date: today,
      number: '',
      currency: 'USD',
      discount_type: 'no_discount',
      discount_percent: 0,
      adjustment: 0,
      reference: '',
      status: 'Open',
      admin_note: '',
      client_note: '',
      terms_conditions: '',
      qty_show: 'Qty',
      items: []
    });

    const newItem = ref({
      description: '',
      long_description: '',
      qty: 1,
      unit: 'Unit',
      rate: 0,
      tax_rate: null
    });

    // computed cancellation routing link
    const cancelLink = computed(() => {
      const cId = route.query.client_id || form.client_id;
      if (cId) {
        return { name: 'admin.customers.view', params: { id: cId }, query: { tab: 'credit_notes' } };
      }
      return { name: 'admin.credit-notes' };
    });

    const formatCurrency = (val) =>
      '$' + parseFloat(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });

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

    const openAddressModal = () => {
      showAddressModal.value = true;
    };

    const closeAddressModal = () => {
      showAddressModal.value = false;
    };

    const handlePredefinedItemSelect = (val) => {
      selectedPredefined.value = val;
    };

    const addPredefinedItem = () => {
      if (!selectedPredefined.value) {
        message.warning('Select a predefined item first.');
        return;
      }
      const match = predefinedItems.value.find(i => i.name === selectedPredefined.value);
      if (match) {
        form.items.push({
          description: match.name,
          long_description: 'Predefined system service item',
          qty: 1,
          unit: 'Unit',
          rate: match.rate,
          tax_rate: null
        });
        message.success('Predefined item loaded.');
      }
    };

    const addItem = () => {
      if (!newItem.value.description) {
        message.warning('Item description is required.');
        return;
      }
      form.items.push({
        ...newItem.value
      });
      newItem.value = {
        description: '',
        long_description: '',
        qty: 1,
        unit: 'Unit',
        rate: 0,
        tax_rate: null
      };
      message.success('Item added.');
    };

    const removeItem = (idx) => {
      form.items.splice(idx, 1);
    };

    const onClientChange = () => {
      const c = clientOptions.value.find(cl => cl.id === form.client_id);
      selectedClient.value = c || null;
      if (c) {
        form.billing_street = c.address || '';
        form.billing_city = c.city || '';
        form.billing_state = c.state || '';
        form.billing_zip = c.zip || '';
        form.billing_country = c.country || '';
      }
    };

    const loadData = async () => {
      try {
        const clientsRes = await axios.get('/clients', { params: { per_page: 500 } });
        clientOptions.value = clientsRes.data.clients?.data || clientsRes.data || [];

        if (form.client_id) {
          onClientChange();
        }

        if (route.params.id) {
          isEdit.value = true;
          const cnRes = await axios.get(`/credit-notes/${route.params.id}`);
          const cn = cnRes.data;

          form.client_id = cn.client_id;
          form.date = cn.date ? String(cn.date).split('T')[0] : today;
          form.number = cn.number.replace('CN-', '');
          form.reference = cn.reference || '';
          form.status = cn.status || 'Open';
          form.admin_note = cn.admin_note || '';

          if (cn.terms) {
            try {
              const parsed = JSON.parse(cn.terms);
              form.terms_conditions = parsed.client_terms || '';
              form.items = parsed.items || [];
            } catch {
              form.terms_conditions = cn.terms || '';
              form.items = [];
            }
          }

          onClientChange();
        } else {
          const cnListRes = await axios.get('/credit-notes', { params: { per_page: 1 } });
          const cnList = cnListRes.data.credit_notes?.data || [];
          if (cnList.length) {
            const lastNum = cnList[0].number || '';
            const numPart = parseInt(lastNum.replace('CN-', '')) || 0;
            form.number = String(numPart + 1).padStart(6, '0');
          } else {
            form.number = '000001';
          }
        }
      } catch (e) {
        console.error('Failed to load data', e);
      }
    };

    const submitCreditNote = async () => {
      if (!form.client_id) {
        message.warning('Select a customer first.');
        return;
      }
      saving.value = true;
      try {
        const payload = {
          client_id: form.client_id,
          number: form.number ? 'CN-' + form.number : undefined,
          amount: totalAmount.value,
          date: form.date,
          status: form.status,
          reference: form.reference,
          admin_note: form.admin_note,
          terms: JSON.stringify({
            client_terms: form.terms_conditions,
            items: form.items
          })
        };

        if (isEdit.value) {
          await axios.put(`/credit-notes/${route.params.id}`, payload);
          message.success('Credit Note updated successfully.');
        } else {
          await axios.post('/credit-notes', payload);
          message.success('Credit Note created successfully.');
        }

        const cId = route.query.client_id || form.client_id;
        if (cId) {
          router.push({ name: 'admin.customers.view', params: { id: cId }, query: { tab: 'credit_notes' } });
        } else {
          router.push({ name: 'admin.credit-notes' });
        }
      } catch (err) {
        const errMsg = err.response?.data?.message || 'Failed to save Credit Note';
        message.error(errMsg);
      } finally {
        saving.value = false;
      }
    };

    onMounted(() => {
      loadData();
    });

    return {
      isEdit,
      saving,
      clientOptions,
      selectedClient,
      showAddressModal,
      predefinedItems,
      form,
      newItem,
      selectedPredefined,
      cancelLink,
      formatCurrency,
      itemAmount,
      subtotal,
      discountVal,
      totalAmount,
      openAddressModal,
      closeAddressModal,
      handlePredefinedItemSelect,
      addPredefinedItem,
      addItem,
      removeItem,
      onClientChange,
      submitCreditNote,
      router
    };
  }
});
</script>

<style scoped>
.invoice-form-page {
  font-family: 'Inter', -apple-system, sans-serif;
  color: #334155;
}

.form-ctrl {
  border: 1px solid #cbd5e1;
  border-radius: 4px;
  padding: 6px 12px;
  width: 100%;
  color: #1e293b;
  outline: none;
  font-family: inherit;
  font-size: 13px;
  box-sizing: border-box;
}
.form-ctrl:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.2);
}

/* Items Table */
.items-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.items-table th {
  padding: 10px 12px;
  font-size: 12px;
  font-weight: 600;
  color: #475569;
  border-bottom: 2px solid #e2e8f0;
  text-align: left;
}
.items-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: top;
}
.items-table tbody tr:last-child td {
  border-bottom: none;
}

.item-ctrl {
  width: 100%;
  padding: 6px 8px;
  font-size: 12.5px;
  border: 1px solid #e2e8f0;
  border-radius: 3px;
  background: #fff;
  font-family: inherit;
  color: #1e293b;
  box-sizing: border-box;
}
.item-ctrl:focus { outline: none; border-color: #93c5fd; }

/* Fade transition for modals */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Modal overlay + card */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 1000;
  background: rgba(15, 23, 42, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(2px);
}
.modal-card {
  background: #fff;
  width: 90%;
  max-width: 640px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.modal-head {
  flex-shrink: 0;
}
.modal-body {
  flex: 1;
}
.modal-foot {
  flex-shrink: 0;
}

/* Ant Design overrides */
:deep(.premium-customer-select .ant-select-selector) {
  border: 1px solid #e2e8f0 !important;
  border-radius: 8px !important;
  height: 40px !important;
  padding: 4px 12px !important;
  box-shadow: none !important;
  transition: all 0.15s ease !important;
}
:deep(.premium-customer-select .ant-select-selector:hover) {
  border-color: #93c5fd !important;
}
:deep(.premium-customer-select.ant-select-focused .ant-select-selector) {
  border-color: #6366f1 !important;
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15) !important;
}
:deep(.premium-customer-select .ant-select-selection-placeholder) {
  font-size: 12.5px !important;
  color: #94a3b8 !important;
}
:deep(.premium-customer-select .ant-select-selection-item) {
  font-size: 12.5px !important;
  font-weight: 600 !important;
  color: #1e293b !important;
}

:deep(.premium-datepicker .ant-picker) {
  border: 1px solid #e2e8f0 !important;
  border-radius: 8px !important;
  height: 40px !important;
  width: 100% !important;
  box-shadow: none !important;
  transition: all 0.15s ease !important;
}
:deep(.premium-datepicker .ant-picker:hover) {
  border-color: #93c5fd !important;
}
:deep(.premium-datepicker .ant-picker-focused) {
  border-color: #6366f1 !important;
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15) !important;
}
:deep(.premium-datepicker .ant-picker input) {
  font-size: 12.5px !important;
  color: #1e293b !important;
}

:deep(.predefined-item-select .ant-select-selector) {
  border: 1px solid #e2e8f0 !important;
  border-radius: 8px !important;
  height: 40px !important;
  padding: 4px 12px !important;
  box-shadow: none !important;
  transition: all 0.15s ease !important;
}
:deep(.predefined-item-select .ant-select-selector:hover) {
  border-color: #93c5fd !important;
}
:deep(.predefined-item-select.ant-select-focused .ant-select-selector) {
  border-color: #6366f1 !important;
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15) !important;
}
</style>
