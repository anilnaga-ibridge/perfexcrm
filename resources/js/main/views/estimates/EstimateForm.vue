<template>
  <div class="estimate-form-page space-y-6">
    <!-- Breadcrumb / Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-2 text-xs text-slate-400">
        <router-link :to="cancelLink" class="hover:text-indigo-600 font-semibold">Estimates</router-link>
        <span>/</span>
        <span class="text-slate-600 font-semibold">{{ editMode ? 'Edit Estimate' : 'New Estimate' }}</span>
      </div>
      <router-link :to="cancelLink" class="text-xs text-indigo-600 hover:underline font-semibold flex items-center gap-1">
        &larr; Back to List
      </router-link>
    </div>

    <!-- Main Card -->
    <div class="premium-form-card bg-white border border-slate-100 rounded-2xl shadow-[0_4px_20px_-2px_rgba(0,0,0,0.05),_0_2px_4px_-1px_rgba(0,0,0,0.03)] overflow-hidden">
      <!-- Title Header -->
      <div class="px-8 py-5 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-slate-50/80 to-white">
        <div class="flex items-center space-x-3">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#d35400] via-[#7e1e8e] to-[#0b579f]"></div>
          <h2 class="text-base font-extrabold text-slate-800 tracking-tight">
            {{ editMode ? 'Edit Estimate #' + (form.number || '') : 'Create New Estimate' }}
          </h2>
        </div>
      </div>

      <div class="p-8 space-y-8">
        <!-- Two-Column Fields Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          
          <!-- Left Column -->
          <div class="p-6 bg-slate-50/30 rounded-xl border border-slate-100/80 space-y-5">
            <div class="flex items-center space-x-2 pb-2 border-b border-slate-100">
              <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">01</span>
              <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Estimate Details</span>
            </div>

            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                <span class="text-rose-500">*</span> Customer
              </label>
              <a-select
                v-model:value="form.client"
                show-search
                :filter-option="(input, option) => option.label.toLowerCase().includes(input.toLowerCase())"
                placeholder="Search and select a customer..."
                style="width: 100%"
                size="large"
                @change="handleClientChange"
                class="premium-customer-select"
              >
                <a-select-option v-for="c in clients" :key="c.company" :value="c.company" :label="c.company">
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
            <div v-if="selectedClientInfo" class="flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-indigo-50/60 to-white border border-indigo-100 rounded-xl">
              <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold flex-shrink-0">
                {{ selectedClientInfo.company?.charAt(0) }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-xs font-bold text-slate-800 truncate">{{ selectedClientInfo.company }}</div>
                <div class="flex items-center gap-3 text-[10px] text-slate-400 mt-0.5">
                  <span v-if="selectedClientInfo.email" class="flex items-center gap-1">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="10" height="10"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    {{ selectedClientInfo.email }}
                  </span>
                  <span v-if="selectedClientInfo.phone" class="flex items-center gap-1">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="10" height="10"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    {{ selectedClientInfo.phone }}
                  </span>
                </div>
              </div>
              <div v-if="selectedClientInfo.total_balance" class="text-right flex-shrink-0">
                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Balance</div>
                <div class="text-xs font-extrabold text-amber-600">${{ Number(selectedClientInfo.total_balance).toLocaleString() }}</div>
              </div>
            </div>

            <!-- Address Details Heading & Edit Trigger -->
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Address Details</label>
                <button 
                  type="button" 
                  class="text-indigo-600 hover:text-indigo-800 cursor-pointer flex items-center gap-1 text-[10.5px] font-bold transition-colors" 
                  @click="openAddressModal"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                  Edit billing & shipping
                </button>
              </div>

              <!-- Bill To / Ship To Addresses -->
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
                <div v-if="form.show_shipping_details" class="border-l border-slate-200/60 pl-6">
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
                <div v-else class="text-slate-400 italic flex items-center justify-center border-l pl-6 text-center text-[11px] font-medium leading-relaxed">
                  Shipping details not shown in estimate.
                </div>
              </div>
            </div>

            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                <span class="text-rose-500">*</span> Estimate Number
              </label>
              <div class="flex shadow-sm rounded-lg overflow-hidden border border-slate-200 focus-within:border-indigo-500 focus-within:ring-3 focus-within:ring-indigo-500/12 transition-all">
                <span class="inline-flex items-center px-3.5 bg-slate-50 text-slate-400 text-xs font-bold border-r border-slate-200">
                  EST-
                </span>
                <input 
                  type="text" 
                  class="w-full text-xs h-10 px-3.5 bg-white border-0 outline-none color-[#334155] font-semibold" 
                  v-model="estimateNumberSuffix" 
                  placeholder="000011" 
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                  <span class="text-rose-500">*</span> Estimate Date
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
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Expiry Date</label>
                <a-date-picker
                  v-model:value="form.expiry"
                  :value-format="'YYYY-MM-DD'"
                  format="DD/MM/YYYY"
                  style="width: 100%"
                  size="large"
                  class="premium-datepicker"
                  placeholder="Select date"
                />
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="p-6 bg-slate-50/30 rounded-xl border border-slate-100/80 space-y-5">
            <div class="flex items-center space-x-2 pb-2 border-b border-slate-100">
              <span class="text-[10px] font-bold text-violet-600 bg-violet-50 px-2 py-0.5 rounded">02</span>
              <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Sales & Notes</span>
            </div>

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
                    <option value="draft">Draft</option>
                    <option value="sent">Sent</option>
                    <option value="accepted">Accepted</option>
                    <option value="declined">Declined</option>
                    <option value="expired">Expired</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Reference #</label>
              <input 
                type="text" 
                class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all" 
                v-model="form.reference_no" 
                placeholder="Reference number" 
              />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Sale Agent</label>
                <div class="relative">
                  <select class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all appearance-none cursor-pointer pr-10" v-model="form.sale_agent">
                    <option v-for="agent in agentOptions" :key="agent" :value="agent">{{ agent }}</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Discount Type</label>
                <div class="relative">
                  <select class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all appearance-none cursor-pointer pr-10" v-model="form.discount_type">
                    <option value="No discount">No discount</option>
                    <option value="Before Tax">Before Tax</option>
                    <option value="After Tax">After Tax</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
            </div>

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
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Estimate Items</span>
          </div>

          <!-- Add Item header toolbar -->
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div class="flex items-center space-x-2.5 max-w-md w-full">
              <div class="flex-1 select-wrapper">
                <a-select 
                  placeholder="Choose Predefined Item to Add" 
                  style="width: 100%" 
                  size="large"
                  allow-clear
                  show-search
                  @change="handlePredefinedItemSelect"
                  class="predefined-item-select"
                >
                  <a-select-option v-for="item in predefinedItems" :key="item.name" :value="item.name">
                    <div class="flex items-center justify-between w-full">
                      <span class="text-xs font-medium">{{ item.name }}</span>
                      <span class="text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md ml-4">{{ fmtCur(item.rate) }}</span>
                    </div>
                  </a-select-option>
                </a-select>
              </div>
              <button class="flex items-center justify-center bg-gradient-to-br from-indigo-600 to-purple-700 hover:from-indigo-700 hover:to-purple-800 text-white font-bold rounded-xl w-10 h-10 transition-all cursor-pointer shadow-md hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0" @click="addPredefinedItem">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              </button>
            </div>
            
            <div class="flex items-center space-x-3 bg-slate-100/80 p-1 rounded-xl w-fit self-end md:self-auto shadow-inner">
              <span class="text-[10px] font-bold text-slate-400 px-2.5 uppercase tracking-wider">Show qty as:</span>
              <div class="flex space-x-1">
                <button 
                  type="button"
                  class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                  :class="form.qty_show === 'Qty' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                  @click="form.qty_show = 'Qty'"
                >
                  Qty
                </button>
                <button 
                  type="button"
                  class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                  :class="form.qty_show === 'Hours' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                  @click="form.qty_show = 'Hours'"
                >
                  Hours
                </button>
                <button 
                  type="button"
                  class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                  :class="form.qty_show === 'Qty/Hours' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                  @click="form.qty_show = 'Qty/Hours'"
                >
                  Qty/Hours
                </button>
              </div>
            </div>
          </div>

          <!-- Items Table Wrapper -->
          <div class="overflow-x-auto rounded-xl border border-slate-100 shadow-sm">
            <table class="items-table text-xs">
              <thead>
                <tr class="bg-gradient-to-r from-slate-50 to-white">
                  <th class="w-8 text-center"></th>
                  <th>Item</th>
                  <th>Description</th>
                  <th class="w-20 text-center">Qty</th>
                  <th class="w-28 text-right">Rate</th>
                  <th class="w-28 text-center">Tax</th>
                  <th class="w-32 text-right pr-5">Amount</th>
                  <th class="w-12 text-center"></th>
                </tr>
              </thead>
              <tbody>
                <!-- Row to add new items -->
                <tr class="add-item-row bg-gradient-to-r from-indigo-50/20 to-white">
                  <td class="text-center text-indigo-400 font-bold text-sm">+</td>
                  <td class="p-3">
                    <textarea 
                      class="form-ctrl text-xs p-2.5 min-h-[52px] bg-white border-slate-200 focus:border-indigo-500 rounded-lg" 
                      rows="2" 
                      v-model="newItem.name" 
                      placeholder="Item description..."
                    ></textarea>
                    <label class="flex items-center gap-2 mt-2 text-[10px] text-slate-400 font-semibold cursor-pointer hover:text-slate-600 transition-colors">
                      <input type="checkbox" v-model="newItem.optional" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                      <span>This item is optional</span>
                    </label>
                  </td>
                  <td class="p-3">
                    <textarea 
                      class="form-ctrl text-xs p-2.5 min-h-[52px] bg-white border-slate-200 focus:border-indigo-500 rounded-lg" 
                      rows="2" 
                      v-model="newItem.long_description" 
                      placeholder="Long description (optional)"
                    ></textarea>
                  </td>
                  <td class="p-3">
                    <input type="number" class="form-ctrl text-xs text-center h-8 bg-white border-slate-200 rounded-md w-full" v-model="newItem.qty" />
                    <input type="text" class="form-ctrl text-[10px] text-center mt-1.5 placeholder-slate-300 h-7 bg-white border-slate-200 rounded-md font-medium w-full" v-model="newItem.unit" placeholder="Unit" />
                  </td>
                  <td class="p-3">
                    <input type="number" class="form-ctrl text-xs text-right h-8 bg-white border-slate-200 rounded-md font-semibold w-full" v-model="newItem.rate" placeholder="0.00" />
                  </td>
                  <td class="p-3">
                    <div class="relative">
                      <select class="form-ctrl text-xs bg-white h-8 border-slate-200 rounded-md appearance-none cursor-pointer pr-7 font-medium w-full" v-model="newItem.tax">
                        <option :value="0">No Tax</option>
                        <option :value="5">5.00%</option>
                        <option :value="10">10.00%</option>
                        <option :value="18">18.00%</option>
                      </select>
                      <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="6 9 12 15 18 9"/></svg>
                      </div>
                    </div>
                  </td>
                  <td class="p-3 text-right font-bold text-slate-500 pr-4 text-sm">
                    {{ fmtCur(newItem.qty * newItem.rate) }}
                  </td>
                  <td class="p-3 text-center">
                    <button class="w-8 h-8 bg-gradient-to-br from-indigo-600 to-purple-700 hover:from-indigo-700 hover:to-purple-800 text-white rounded-xl flex items-center justify-center cursor-pointer shadow-sm hover:shadow-md hover:scale-105 active:scale-95 transition-all mx-auto" @click="addItem">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg>
                    </button>
                  </td>
                </tr>

                <!-- Listed, added estimate items -->
                <tr v-for="(item, idx) in form.items" :key="idx" class="added-item-row group hover:bg-gradient-to-r hover:from-slate-50 hover:to-white transition-all duration-150">
                  <td class="text-center font-semibold text-slate-400 text-sm">{{ idx + 1 }}</td>
                  <td class="p-3">
                    <div class="font-bold text-slate-800 text-sm">{{ item.name }}</div>
                    <span v-if="item.optional" class="text-[9px] font-bold bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-full px-2.5 py-0.5 mt-1.5 inline-block">Optional</span>
                  </td>
                  <td class="p-3 text-slate-500 leading-relaxed text-xs">{{ item.long_description || '—' }}</td>
                  <td class="p-3 text-center font-semibold text-slate-700">
                    {{ item.qty }} 
                    <span class="text-[10px] text-slate-400 font-normal block mt-0.5">({{ item.unit }})</span>
                  </td>
                  <td class="p-3 text-right font-medium text-slate-600">{{ fmtCur(item.rate) }}</td>
                  <td class="p-3 text-center">
                    <span v-if="item.tax > 0" class="inline-flex items-center gap-1 bg-gradient-to-r from-amber-50 to-amber-100/50 text-amber-700 border border-amber-200/60 px-2.5 py-0.5 rounded-full text-[10px] font-bold">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="9" height="9"><path d="M12 2v20M2 12h20"/></svg>
                      {{ item.tax }}%
                    </span>
                    <span v-else class="text-slate-400 text-[10px] font-medium">None</span>
                  </td>
                  <td class="p-3 text-right font-bold text-slate-800 pr-4">{{ fmtCur(item.amount) }}</td>
                  <td class="p-3 text-center">
                    <button class="text-slate-300 hover:text-rose-500 hover:bg-rose-50 rounded-lg w-7 h-7 flex items-center justify-center border border-transparent hover:border-rose-100 cursor-pointer transition-all mx-auto opacity-0 group-hover:opacity-100" @click="removeItem(idx)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Subtotal / Adjustment / Totals Aligned right -->
          <div class="flex justify-end pt-4">
            <div class="w-80 bg-gradient-to-br from-slate-50 to-white border border-slate-100 rounded-2xl p-5 space-y-3.5 shadow-sm">
              <div class="flex justify-between items-center text-xs pb-2.5 border-b border-slate-200/50">
                <span class="text-slate-500 font-semibold">Sub Total</span>
                <span class="font-bold text-slate-800 text-sm">{{ fmtCur(totals.subtotal) }}</span>
              </div>
              
              <div class="flex justify-between items-center text-xs pb-2.5 border-b border-slate-200/50">
                <span class="text-slate-500 font-semibold">Discount</span>
                <div class="flex items-center space-x-1.5">
                  <input 
                    type="number" 
                    min="0" 
                    max="100" 
                    class="border border-slate-200 rounded-md text-xs text-right w-14 h-7 pr-1 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" 
                    v-model="form.discount_percent" 
                  />
                  <span class="text-xs font-semibold text-slate-500 bg-slate-200/50 rounded px-1.5 py-0.5">%</span>
                </div>
                <span class="font-bold text-rose-500">-{{ fmtCur(totals.discount) }}</span>
              </div>

              <div class="flex justify-between items-center text-xs pb-2.5 border-b border-slate-200/50">
                <span class="text-slate-500 font-semibold">Adjustment</span>
                <input 
                  type="number" 
                  class="border border-slate-200 rounded-md text-xs text-right w-20 h-7 pr-1 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" 
                  v-model="form.adjustment" 
                />
                <span class="font-bold text-slate-800">{{ fmtCur(form.adjustment) }}</span>
              </div>

              <div v-if="totals.tax > 0" class="flex justify-between items-center text-xs pb-2.5 border-b border-slate-200/50">
                <span class="text-slate-500 font-semibold">Tax Total</span>
                <span class="font-bold text-slate-800">{{ fmtCur(totals.tax) }}</span>
              </div>

              <div class="flex justify-between items-center pt-1.5">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Total</span>
                <span class="text-xl text-slate-900 font-extrabold tracking-tight">{{ fmtCur(totals.total) }}</span>
              </div>
            </div>
          </div>

          <!-- Bottom Textareas: Client Note & Terms -->
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
                <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Terms & Conditions</label>
              </div>
              <textarea 
                class="form-ctrl text-xs p-3 bg-white hover:bg-white border-slate-200 focus:bg-white rounded-lg transition-all min-h-[100px] resize-none text-slate-700" 
                rows="4" 
                v-model="form.terms"
                placeholder="Standard contract terms and conditions..."
              ></textarea>
            </div>
          </div>

        </div>

        <!-- Action Bar Footer -->
        <div class="border-t border-slate-200 bg-gradient-to-r from-slate-50 to-white px-8 py-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 text-xs text-slate-400 rounded-b-2xl -mx-8 -mb-8 mt-8">
          <div class="flex items-center space-x-2">
            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm1 14h-2v-4h2zm0-6h-2V7h2z"/></svg>
            </span>
            <span class="text-slate-500 font-medium">Generate and save estimates for clients</span>
          </div>
          <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
            <button class="px-5 py-2.5 border border-slate-200 rounded-xl text-xs font-bold bg-white text-slate-600 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-800 cursor-pointer transition-all hover:-translate-y-0.5 active:translate-y-0 shadow-sm" @click="saveAndSend">
              <span class="flex items-center gap-2">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><path d="M22 2L11 13"/><path d="M22 2L15 22l-4-9-9-4z"/></svg>
                Save & Send
              </span>
            </button>
            <button class="px-6 py-2.5 bg-gradient-to-br from-slate-800 to-slate-900 hover:from-slate-900 hover:to-black text-white rounded-xl text-xs font-bold cursor-pointer transition-all hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 shadow-md" @click="save">
              <span class="flex items-center gap-2">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save
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
            <!-- Billing Address Column -->
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

            <!-- Shipping Address Column -->
            <div class="space-y-4">
              <div class="border-b border-slate-100 pb-1.5 flex justify-between items-center">
                <span class="text-[10px] font-bold text-violet-600 bg-violet-50 px-2 py-0.5 rounded">SHIPPING</span>
                <label class="flex items-center gap-1.5 cursor-pointer text-[10.5px] font-semibold text-slate-500">
                  <input type="checkbox" v-model="addressForm.show_shipping_details" class="rounded border-slate-300 text-violet-600 focus:ring-violet-500" />
                  <span>Show in estimate</span>
                </label>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Street</label>
                <textarea class="form-ctrl text-xs p-2.5 bg-slate-50/30 focus:bg-white rounded-lg border-slate-200" rows="2" v-model="addressForm.shipping_street" :disabled="!addressForm.show_shipping_details" placeholder="Street Address"></textarea>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">City</label>
                <input type="text" class="form-ctrl text-xs h-9 px-3 bg-slate-50/30 focus:bg-white rounded-lg border-slate-200" v-model="addressForm.shipping_city" :disabled="!addressForm.show_shipping_details" placeholder="City" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">State</label>
                <input type="text" class="form-ctrl text-xs h-9 px-3 bg-slate-50/30 focus:bg-white rounded-lg border-slate-200" v-model="addressForm.shipping_state" :disabled="!addressForm.show_shipping_details" placeholder="State" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Zip Code</label>
                <input type="text" class="form-ctrl text-xs h-9 px-3 bg-slate-50/30 focus:bg-white rounded-lg border-slate-200" v-model="addressForm.shipping_zip" :disabled="!addressForm.show_shipping_details" placeholder="Zip Code" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Country</label>
                <input type="text" class="form-ctrl text-xs h-9 px-3 bg-slate-50/30 focus:bg-white rounded-lg border-slate-200" v-model="addressForm.shipping_country" :disabled="!addressForm.show_shipping_details" placeholder="Country" />
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
import { defineComponent, ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useEstimatesStore } from '../../store/estimatesStore';
import { message } from 'ant-design-vue';
import axios from 'axios';

export default defineComponent({
  name: 'EstimateForm',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const estimatesStore = useEstimatesStore();

    const editMode = ref(false);
    const estimateId = ref(null);
    const estimateNumberSuffix = ref('');
    const discountUnit = ref('%');

    const clients = ref([]);

    const loadClients = async () => {
      try {
        const res = await axios.get('/clients', { params: { per_page: 500 } });
        clients.value = res.data.clients?.data || res.data || [];
        
        // If client_id is passed as query parameter, auto pre-fill
        if (route.query.client_id) {
          const matchedClient = clients.value.find(c => c.id === Number(route.query.client_id));
          if (matchedClient) {
            form.value.client = matchedClient.company;
            handleClientChange(matchedClient.company);
          }
        }
      } catch (err) {
        console.error('Failed to load clients list', err);
      }
    };

    const agentOptions = [
      'Tom Kunze',
      'Armando Turcotte',
      'Elias Konopelski',
      'Tamara Howell',
      'Marcus Lesch',
      'Rosie Trantow'
    ];

    const predefinedItems = [
      { name: 'Consulting SLA Services', rate: 1600 },
      { name: 'App Development Module v1.0', rate: 5800 },
      { name: 'Cloud Infrastructure Audit Pack', rate: 12400 },
      { name: 'UI/UX Interactive Mockup Designs', rate: 2100 }
    ];

    const selectedPredefined = ref('');

    const form = ref({
      client: '',
      date: '2026-06-15',
      expiry: '2026-06-22',
      currency: 'USD',
      discount_type: 'No discount',
      tags: [],
      status: 'draft',
      sale_agent: 'Tom Kunze',
      reference_no: '',
      admin_note: '',
      client_note: '',
      terms: 'Mouse had changed his mind, and was immediately suppressed by the time they were playing the Queen ordering off her knowledge, as there was the Rabbit in a great letter, nearly as large as the other. As soon as there was enough of me left to make myself useful, and looking anxiously round to see.',
      qty_show: 'Qty',
      items: [],
      discount_percent: 0,
      adjustment: 0,
      billing_street: '',
      billing_city: '',
      billing_state: '',
      billing_zip: '',
      billing_country: '',
      show_shipping_details: true,
      shipping_street: '',
      shipping_city: '',
      shipping_state: '',
      shipping_zip: '',
      shipping_country: ''
    });

    const newItem = ref({
      name: '',
      long_description: '',
      qty: 1,
      unit: 'Unit',
      rate: 0,
      tax: 0,
      optional: false
    });

    const selectedClientInfo = computed(() => {
      if (!form.value.client) return null
      return clients.value.find(cl => cl.company === form.value.client) || null
    })

    const handleClientChange = (companyName) => {
      const c = clients.value.find(cl => cl.company === companyName);
      if (c) {
        form.value.billing_street = c.address || '';
        form.value.billing_city = c.city || '';
        form.value.billing_state = c.state || '';
        form.value.billing_zip = c.zip || '';
        form.value.billing_country = c.country || '';
        
        form.value.shipping_street = c.address || '';
        form.value.shipping_city = c.city || '';
        form.value.shipping_state = c.state || '';
        form.value.shipping_zip = c.zip || '';
        form.value.shipping_country = c.country || '';
      }
    };

    const addItem = () => {
      if (!newItem.value.name) {
        message.warning('Please input the Item name.');
        return;
      }
      
      const qty = Number(newItem.value.qty || 1);
      const rate = Number(newItem.value.rate || 0);
      const amount = qty * rate;

      form.value.items.push({
        name: newItem.value.name,
        long_description: newItem.value.long_description,
        qty,
        unit: newItem.value.unit || 'Unit',
        rate,
        tax: Number(newItem.value.tax || 0),
        amount,
        optional: newItem.value.optional
      });

      newItem.value = {
        name: '',
        long_description: '',
        qty: 1,
        unit: 'Unit',
        rate: 0,
        tax: 0,
        optional: false
      };
      message.success('Item added to estimate.');
    };

    const removeItem = (idx) => {
      form.value.items.splice(idx, 1);
      message.info('Item removed.');
    };

    const handlePredefinedItemSelect = (val) => {
      selectedPredefined.value = val;
    };

    const addPredefinedItem = () => {
      if (!selectedPredefined.value) {
        message.warning('Select a predefined item first.');
        return;
      }
      const match = predefinedItems.find(i => i.name === selectedPredefined.value);
      if (match) {
        form.value.items.push({
          name: match.name,
          long_description: 'Predefined system service item',
          qty: 1,
          unit: 'Unit',
          rate: match.rate,
          tax: 0,
          amount: match.rate,
          optional: false
        });
        message.success('Predefined item loaded.');
      }
    };

    const totals = computed(() => {
      let subtotal = 0;
      let taxTotal = 0;

      form.value.items.forEach(item => {
        const itemSub = item.qty * item.rate;
        subtotal += itemSub;
        if (item.tax > 0) {
          taxTotal += itemSub * (item.tax / 100);
        }
      });

      const discPct = Number(form.value.discount_percent || 0);
      const discountAmt = subtotal * (discPct / 100);
      const adjustmentAmt = Number(form.value.adjustment || 0);

      const grandTotal = Math.max(0, subtotal - discountAmt + taxTotal + adjustmentAmt);

      return {
        subtotal,
        discount: discountAmt,
        tax: taxTotal,
        total: grandTotal
      };
    });

    const validateForm = () => {
      if (!form.value.client) {
        message.error('Client selection is required.');
        return false;
      }
      if (!estimateNumberSuffix.value) {
        message.error('Estimate Number is required.');
        return false;
      }
      return true;
    };

    const parseDate = (dStr) => {
      if (!dStr) return '';
      const d = new Date(dStr);
      if (isNaN(d.getTime())) return '';
      return d.toISOString().split('T')[0];
    };

    const formatDate = (dStr) => {
      if (!dStr) return '';
      const d = new Date(dStr);
      if (isNaN(d.getTime())) return dStr;
      return d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
    };

    const cancelLink = computed(() => {
      const matchedClient = clients.value.find(c => String(c.company).toLowerCase() === String(form.value.client).toLowerCase());
      const cId = route.query.client_id || matchedClient?.id;
      if (cId) {
        return { name: 'admin.customers.view', params: { id: cId }, query: { tab: 'estimates' } };
      }
      return '/admin/estimates';
    });

    const save = () => {
      if (!validateForm()) return;

      const payload = {
        ...form.value,
        tags: Array.isArray(form.value.tags) ? form.value.tags.join(', ') : form.value.tags,
        number: `EST-${estimateNumberSuffix.value}`,
        amount: totals.value.total,
        date: formatDate(form.value.date),
        expiry: formatDate(form.value.expiry)
      };

      if (editMode.value) {
        estimatesStore.updateEstimate(estimateId.value, payload);
        message.success('Estimate updated successfully.');
      } else {
        estimatesStore.addEstimate(payload);
        message.success('Estimate created successfully.');
      }
      
      const matchedClient = clients.value.find(c => String(c.company).toLowerCase() === String(form.value.client).toLowerCase());
      const cId = route.query.client_id || matchedClient?.id;
      if (cId) {
        router.push({ name: 'admin.customers.view', params: { id: cId }, query: { tab: 'estimates' } });
      } else {
        router.push('/admin/estimates');
      }
    };

    const saveAndSend = () => {
      if (!validateForm()) return;
      
      const payload = {
        ...form.value,
        tags: Array.isArray(form.value.tags) ? form.value.tags.join(', ') : form.value.tags,
        number: `EST-${estimateNumberSuffix.value}`,
        amount: totals.value.total,
        status: 'sent',
        date: formatDate(form.value.date),
        expiry: formatDate(form.value.expiry)
      };

      if (editMode.value) {
        estimatesStore.updateEstimate(estimateId.value, payload);
      } else {
        estimatesStore.addEstimate(payload);
      }
      message.loading('Sending email to recipient...', 1.5).then(() => {
        message.success('Estimate saved & successfully emailed to client.');
        
        const matchedClient = clients.value.find(c => String(c.company).toLowerCase() === String(form.value.client).toLowerCase());
        const cId = route.query.client_id || matchedClient?.id;
        if (cId) {
          router.push({ name: 'admin.customers.view', params: { id: cId }, query: { tab: 'estimates' } });
        } else {
          router.push('/admin/estimates');
        }
      });
    };

    const fmtCur = (v) => '$' + Number(v).toLocaleString('en-US', { minimumFractionDigits: 2 });

    const showAddressModal = ref(false);
    const addressForm = ref({
      billing_street: '',
      billing_city: '',
      billing_state: '',
      billing_zip: '',
      billing_country: '',
      show_shipping_details: true,
      shipping_street: '',
      shipping_city: '',
      shipping_state: '',
      shipping_zip: '',
      shipping_country: ''
    });

    const openAddressModal = () => {
      addressForm.value = {
        billing_street: form.value.billing_street || '',
        billing_city: form.value.billing_city || '',
        billing_state: form.value.billing_state || '',
        billing_zip: form.value.billing_zip || '',
        billing_country: form.value.billing_country || '',
        show_shipping_details: form.value.show_shipping_details !== undefined ? form.value.show_shipping_details : true,
        shipping_street: form.value.shipping_street || '',
        shipping_city: form.value.shipping_city || '',
        shipping_state: form.value.shipping_state || '',
        shipping_zip: form.value.shipping_zip || '',
        shipping_country: form.value.shipping_country || ''
      };
      showAddressModal.value = true;
    };

    const closeAddressModal = () => {
      showAddressModal.value = false;
    };

    const saveAddresses = () => {
      form.value.billing_street = addressForm.value.billing_street;
      form.value.billing_city = addressForm.value.billing_city;
      form.value.billing_state = addressForm.value.billing_state;
      form.value.billing_zip = addressForm.value.billing_zip;
      form.value.billing_country = addressForm.value.billing_country;
      form.value.show_shipping_details = addressForm.value.show_shipping_details;
      form.value.shipping_street = addressForm.value.shipping_street;
      form.value.shipping_city = addressForm.value.shipping_city;
      form.value.shipping_state = addressForm.value.shipping_state;
      form.value.shipping_zip = addressForm.value.shipping_zip;
      form.value.shipping_country = addressForm.value.shipping_country;
      showAddressModal.value = false;
      message.success('Billing & Shipping addresses updated.');
    };

    onMounted(async () => {
      await loadClients();
      if (route.params.id) {
        editMode.value = true;
        estimateId.value = parseInt(route.params.id);
        
        const match = estimatesStore.estimates.find(e => e.id === estimateId.value);
        if (match) {
          form.value = JSON.parse(JSON.stringify(match));
          if (form.value.show_shipping_details === undefined) {
            form.value.show_shipping_details = true;
          }
          form.value.date = parseDate(match.date);
          form.value.expiry = parseDate(match.expiry);
          if (match.number && match.number.startsWith('EST-')) {
            estimateNumberSuffix.value = match.number.replace('EST-', '');
          } else {
            estimateNumberSuffix.value = match.number || '';
          }
        } else {
          message.error('Estimate details not found.');
          router.push('/admin/estimates');
        }
      } else {
        if (!route.query.client_id && clients.value.length > 0) {
          form.value.client = clients.value[0].company;
          handleClientChange(clients.value[0].company);
        }
        const nextId = estimatesStore.estimates.length > 0 ? Math.max(...estimatesStore.estimates.map(e => e.id)) + 1 : 1;
        estimateNumberSuffix.value = String(nextId).padStart(6, '0');
      }
    });

    return {
      form,
      newItem,
      addItem,
      removeItem,
      predefinedItems,
      handlePredefinedItemSelect,
      addPredefinedItem,
      totals,
      clients,
      selectedClientInfo,
      agentOptions,
      save,
      saveAndSend,
      editMode,
      fmtCur,
      discountUnit,
      handleClientChange,
      estimateNumberSuffix,
      showAddressModal,
      addressForm,
      openAddressModal,
      closeAddressModal,
      saveAddresses,
      cancelLink
    };
  }
});
</script>

<style scoped>
.estimate-form-page {
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
  border-color: #7e1e8e;
  background-color: #fff;
  box-shadow: 0 0 0 3px rgba(126, 30, 142, 0.12);
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

/* Predefined Item Select Overrides */
:deep(.predefined-item-select .ant-select-selector) {
  border-radius: 10px !important;
  height: 40px !important;
  padding: 0 12px !important;
  border-color: #e2e8f0 !important;
}
:deep(.predefined-item-select.ant-select-focused .ant-select-selector) {
  border-color: #6366f1 !important;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
}

/* Disabled styling inside modal */
.form-ctrl:disabled {
  background-color: #f1f5f9;
  border-color: #e2e8f0;
  color: #94a3b8;
  cursor: not-allowed;
}

/* Custom styled placeholder */
.form-ctrl::placeholder {
  color: #94a3b8;
}

/* Ant-Design Select premium overrides */
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
  border-color: #7e1e8e !important;
  box-shadow: 0 0 0 3px rgba(126, 30, 142, 0.12) !important;
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

.items-table {
  width: 100%;
  border-collapse: collapse;
}

.items-table th {
  background: #f8fafc;
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

.add-check-btn {
  width: 32px;
  height: 32px;
  border: none;
  cursor: pointer;
  margin: 0 auto;
}

/* Save / Action Bar Buttons */
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
</style>
