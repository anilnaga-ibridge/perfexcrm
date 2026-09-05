<template>
  <div class="estimate-form-page space-y-6">
    <!-- Breadcrumb / Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-2 text-xs text-slate-400">
        <router-link :to="cancelLink" class="hover:text-indigo-600 font-semibold">Estimates</router-link>
        <span>/</span>
        <span class="text-slate-600 font-semibold">{{ editMode ? 'Edit Estimate' : 'New Estimate' }}</span>
      </div>
      <router-link :to="cancelLink" class="btn-outline text-xs font-semibold flex items-center gap-1.5 py-1.5 px-3">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Back to List
      </router-link>
    </div>

    <!-- Main Card -->
    <div class="vuexy-form-card bg-white border border-[#EBE9F1] rounded-[10px] shadow-[0_2px_9px_rgba(47,43,61,0.06)] overflow-hidden">
      <!-- Title Header -->
      <div class="px-7 py-4.5 border-b border-[#F1F0F2] flex justify-between items-center bg-[#FFFFFF]">
        <div class="flex items-center space-x-3">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#7367F0] to-[#9F8ED6]"></div>
          <h2 class="text-base font-bold text-[#4B465C] m-0">
            {{ editMode ? 'Edit Estimate #' + (form.number || '') : 'Create New Estimate' }}
          </h2>
        </div>
      </div>

      <div class="p-7 space-y-7">
        <!-- Two-Column Fields Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          
          <!-- Left Column: Estimate Details -->
          <div class="p-5 bg-[#FFFFFF] rounded-lg border border-[#EBE9F1] space-y-4 shadow-sm">
            <div class="flex items-center justify-between pb-2 border-b border-[#F1F0F2]">
              <div class="flex items-center space-x-2">
                <span class="text-[11px] font-bold text-[#7367F0] bg-[#7367F0]/10 px-2 py-0.5 rounded">01</span>
                <span class="text-xs font-bold uppercase tracking-wider text-[#4B465C]">Estimate Details</span>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1">
                <span class="text-rose-500">*</span> Customer
              </label>
              <a-select
                v-model:value="form.client"
                show-search
                :filter-option="(input, option) => (option.value || '').toLowerCase().includes(input.toLowerCase())"
                placeholder="Search and select a customer..."
                style="width: 100%"
                @change="handleClientChange"
                class="vuexy-select"
              >
                <a-select-option v-for="c in clients" :key="c.company" :value="c.company">
                  {{ c.company }} <span v-if="c.city" class="text-slate-400 text-xs">({{ c.city }})</span>
                </a-select-option>
              </a-select>
            </div>

            <!-- Address Details Heading & Edit Trigger -->
            <div class="space-y-2 pt-1">
              <div class="flex items-center justify-between">
                <label class="block text-xs font-semibold text-[#4B465C]">Address Details</label>
                <button 
                  type="button" 
                  class="text-[#7367F0] hover:text-[#5E50EE] cursor-pointer flex items-center gap-1 text-xs font-semibold transition-colors bg-transparent border-none p-0" 
                  @click="openAddressModal"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                  Edit billing & shipping
                </button>
              </div>

              <!-- Bill To / Ship To Addresses -->
              <div class="grid grid-cols-2 gap-4 text-xs text-[#5D596C] p-3.5 bg-[#F8F7FA] rounded-md border border-[#EBE9F1]">
                <div>
                  <div class="font-bold text-[#4B465C] mb-1 flex items-center gap-1.5">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" class="text-[#7367F0]"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    Bill To
                  </div>
                  <div class="font-medium text-[#5D596C] space-y-0.5 leading-relaxed">
                    <div>{{ form.billing_street || '--' }}</div>
                    <div>{{ form.billing_city || '--' }}{{ form.billing_state ? ', ' + form.billing_state : '' }}</div>
                    <div>{{ form.billing_country || '--' }}{{ form.billing_zip ? ', ' + form.billing_zip : '' }}</div>
                  </div>
                </div>
                <div v-if="form.show_shipping_details" class="border-l border-[#DBDADE] pl-4">
                  <div class="font-bold text-[#4B465C] mb-1 flex items-center gap-1.5">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" class="text-[#7367F0]"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    Ship To
                  </div>
                  <div class="font-medium text-[#5D596C] space-y-0.5 leading-relaxed">
                    <div>{{ form.shipping_street || '--' }}</div>
                    <div>{{ form.shipping_city || '--' }}{{ form.shipping_state ? ', ' + form.shipping_state : '' }}</div>
                    <div>{{ form.shipping_country || '--' }}{{ form.shipping_zip ? ', ' + form.shipping_zip : '' }}</div>
                  </div>
                </div>
                <div v-else class="text-[#A8AAAE] italic flex items-center justify-center border-l border-[#DBDADE] pl-4 text-center text-[11px] leading-relaxed">
                  Shipping details not shown in estimate.
                </div>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1">
                <span class="text-rose-500">*</span> Estimate Number
              </label>
              <div class="flex rounded-md overflow-hidden border border-[#DBDADE] focus-within:border-[#7367F0] focus-within:ring-2 focus-within:ring-[#7367F0]/15 transition-all">
                <span class="inline-flex items-center px-3.5 bg-[#F8F7FA] text-[#6F6B7D] text-xs font-bold border-r border-[#DBDADE]">
                  EST-
                </span>
                <input 
                  type="text" 
                  class="w-full text-xs h-[38px] px-3.5 bg-white border-0 outline-none text-[#4B465C] font-semibold" 
                  v-model="estimateNumberSuffix" 
                  placeholder="000001" 
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">
                  <span class="text-rose-500">*</span> Estimate Date
                </label>
                <a-date-picker
                  v-model:value="form.date"
                  :value-format="'YYYY-MM-DD'"
                  format="DD/MM/YYYY"
                  style="width: 100%"
                  class="vuexy-datepicker"
                  placeholder="Select date"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">Expiry Date</label>
                <a-date-picker
                  v-model:value="form.expiry"
                  :value-format="'YYYY-MM-DD'"
                  format="DD/MM/YYYY"
                  style="width: 100%"
                  class="vuexy-datepicker"
                  placeholder="Select date"
                />
              </div>
            </div>
          </div>

          <!-- Right Column: Sales & Notes -->
          <div class="p-5 bg-[#FFFFFF] rounded-lg border border-[#EBE9F1] space-y-4 shadow-sm">
            <div class="flex items-center justify-between pb-2 border-b border-[#F1F0F2]">
              <div class="flex items-center space-x-2">
                <span class="text-[11px] font-bold text-[#7367F0] bg-[#7367F0]/10 px-2 py-0.5 rounded">02</span>
                <span class="text-xs font-bold uppercase tracking-wider text-[#4B465C]">Sales & Notes</span>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1">Tags</label>
              <a-select
                v-model:value="form.tags"
                mode="tags"
                placeholder="Add tags..."
                style="width: 100%"
                class="vuexy-tags-select"
              >
              </a-select>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">
                  <span class="text-rose-500">*</span> Currency
                </label>
                <div class="relative">
                  <select class="form-ctrl text-xs h-[38px] px-3.5 bg-[#FFFFFF] border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10" v-model="form.currency">
                    <option value="USD">USD ($)</option>
                    <option value="EUR">EUR (€)</option>
                    <option value="GBP">GBP (£)</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">Status</label>
                <div class="relative">
                  <select class="form-ctrl text-xs h-[38px] px-3.5 bg-[#FFFFFF] border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 capitalize" v-model="form.status">
                    <option value="draft">Draft</option>
                    <option value="sent">Sent</option>
                    <option value="accepted">Accepted</option>
                    <option value="declined">Declined</option>
                    <option value="expired">Expired</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1">Reference #</label>
              <input 
                type="text" 
                class="form-ctrl text-xs h-[38px] px-3.5 bg-[#FFFFFF] border-[#DBDADE] rounded-md transition-all" 
                v-model="form.reference_no" 
                placeholder="e.g. REF-001" 
              />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">Sale Agent</label>
                <div class="relative">
                  <select class="form-ctrl text-xs h-[38px] px-3.5 bg-[#FFFFFF] border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10" v-model="form.sale_agent">
                    <option v-for="agent in agentOptions" :key="agent" :value="agent">{{ agent }}</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">Discount Type</label>
                <div class="relative">
                  <select class="form-ctrl text-xs h-[38px] px-3.5 bg-[#FFFFFF] border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10" v-model="form.discount_type">
                    <option value="No discount">No discount</option>
                    <option value="Before Tax">Before Tax</option>
                    <option value="After Tax">After Tax</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1">Admin Note</label>
              <textarea 
                class="form-ctrl text-xs p-3 bg-[#FFFFFF] border-[#DBDADE] rounded-md transition-all min-h-[64px]" 
                rows="2" 
                v-model="form.admin_note"
                placeholder="Internal notes for staff..."
              ></textarea>
            </div>
          </div>

        </div>

        <!-- 03 Estimate Items Section -->
        <div class="space-y-4 pt-4 border-t border-[#F1F0F2]">
          
          <div class="flex items-center space-x-2 pb-1">
            <span class="text-[11px] font-bold text-[#7367F0] bg-[#7367F0]/10 px-2 py-0.5 rounded">03</span>
            <span class="text-xs font-bold uppercase tracking-wider text-[#4B465C]">Estimate Items</span>
          </div>

          <!-- Add Item header toolbar -->
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-3">
            <div class="flex items-center space-x-2.5 max-w-md w-full">
              <div class="flex-1 select-wrapper">
                <a-select 
                  placeholder="Choose Predefined Item to Add" 
                  style="width: 100%" 
                  allow-clear
                  show-search
                  @change="handlePredefinedItemSelect"
                  class="vuexy-select"
                >
                  <a-select-option v-for="item in predefinedItems" :key="item.name" :value="item.name">
                    <div class="flex items-center justify-between w-full">
                      <span class="text-xs font-medium text-[#4B465C]">{{ item.name }}</span>
                      <span class="text-[11px] font-bold text-[#7367F0] bg-[#7367F0]/10 px-2 py-0.5 rounded ml-4">{{ fmtCur(item.rate) }}</span>
                    </div>
                  </a-select-option>
                </a-select>
              </div>
              <button 
                type="button"
                class="btn-primary h-[38px] px-3.5 rounded-md flex items-center justify-center shrink-0 cursor-pointer" 
                @click="addPredefinedItem"
                title="Add Predefined Item"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              </button>
            </div>
            
            <div class="flex items-center space-x-2 bg-[#F8F7FA] p-1 rounded-md border border-[#DBDADE] w-fit self-end md:self-auto">
              <span class="text-[11px] font-bold text-[#A8AAAE] px-2 uppercase tracking-wider">Show qty as:</span>
              <div class="flex space-x-1">
                <button 
                  type="button"
                  class="px-3 py-1 rounded text-xs font-bold transition-all cursor-pointer border-none"
                  :class="form.qty_show === 'Qty' ? 'bg-[#7367F0] text-white shadow-sm' : 'bg-transparent text-[#6F6B7D] hover:text-[#4B465C]'"
                  @click="form.qty_show = 'Qty'"
                >
                  Qty
                </button>
                <button 
                  type="button"
                  class="px-3 py-1 rounded text-xs font-bold transition-all cursor-pointer border-none"
                  :class="form.qty_show === 'Hours' ? 'bg-[#7367F0] text-white shadow-sm' : 'bg-transparent text-[#6F6B7D] hover:text-[#4B465C]'"
                  @click="form.qty_show = 'Hours'"
                >
                  Hours
                </button>
                <button 
                  type="button"
                  class="px-3 py-1 rounded text-xs font-bold transition-all cursor-pointer border-none"
                  :class="form.qty_show === 'Qty/Hours' ? 'bg-[#7367F0] text-white shadow-sm' : 'bg-transparent text-[#6F6B7D] hover:text-[#4B465C]'"
                  @click="form.qty_show = 'Qty/Hours'"
                >
                  Qty/Hours
                </button>
              </div>
            </div>
          </div>

          <!-- Items Table Wrapper -->
          <div class="overflow-x-auto rounded-lg border border-[#EBE9F1] shadow-sm">
            <table class="items-table text-xs w-full">
              <thead>
                <tr class="bg-[#F8F7FA] text-[#6F6B7D] border-b border-[#EBE9F1]">
                  <th class="w-8 text-center py-3 px-2">#</th>
                  <th class="py-3 px-3 text-left font-semibold uppercase text-[11px] tracking-wider">Item</th>
                  <th class="py-3 px-3 text-left font-semibold uppercase text-[11px] tracking-wider">Description</th>
                  <th class="w-24 text-center py-3 px-2 font-semibold uppercase text-[11px] tracking-wider">Qty</th>
                  <th class="w-28 text-right py-3 px-3 font-semibold uppercase text-[11px] tracking-wider">Rate</th>
                  <th class="w-28 text-center py-3 px-2 font-semibold uppercase text-[11px] tracking-wider">Tax</th>
                  <th class="w-32 text-right pr-4 py-3 px-3 font-semibold uppercase text-[11px] tracking-wider">Amount</th>
                  <th class="w-12 text-center py-3 px-2"></th>
                </tr>
              </thead>
              <tbody>
                <!-- Row to add new items -->
                <tr class="add-item-row bg-[#F8F7FA]/60 border-b border-[#EBE9F1]">
                  <td class="text-center text-[#7367F0] font-bold text-sm">+</td>
                  <td class="p-2.5">
                    <textarea 
                      class="form-ctrl text-xs p-2 min-h-[48px] bg-white border-[#DBDADE] focus:border-[#7367F0] rounded-md" 
                      rows="2" 
                      v-model="newItem.name" 
                      placeholder="Item name / title..."
                    ></textarea>
                    <label class="flex items-center gap-1.5 mt-1.5 text-[11px] text-[#A8AAAE] font-semibold cursor-pointer hover:text-[#4B465C] transition-colors">
                      <input type="checkbox" v-model="newItem.optional" class="rounded border-[#DBDADE] text-[#7367F0] focus:ring-[#7367F0]" />
                      <span>This item is optional</span>
                    </label>
                  </td>
                  <td class="p-2.5">
                    <textarea 
                      class="form-ctrl text-xs p-2 min-h-[48px] bg-white border-[#DBDADE] focus:border-[#7367F0] rounded-md" 
                      rows="2" 
                      v-model="newItem.long_description" 
                      placeholder="Long description (optional)"
                    ></textarea>
                  </td>
                  <td class="p-2.5">
                    <input type="number" class="form-ctrl text-xs text-center h-[34px] bg-white border-[#DBDADE] rounded-md w-full" v-model="newItem.qty" />
                    <input type="text" class="form-ctrl text-[11px] text-center mt-1 placeholder-[#A8AAAE] h-[28px] bg-white border-[#DBDADE] rounded-md font-medium w-full" v-model="newItem.unit" placeholder="Unit" />
                  </td>
                  <td class="p-2.5">
                    <input type="number" class="form-ctrl text-xs text-right h-[34px] bg-white border-[#DBDADE] rounded-md font-semibold w-full" v-model="newItem.rate" placeholder="0.00" />
                  </td>
                  <td class="p-2.5">
                    <div class="relative">
                      <select class="form-ctrl text-xs bg-white h-[34px] border-[#DBDADE] rounded-md appearance-none cursor-pointer pr-7 font-medium w-full" v-model="newItem.tax">
                        <option :value="0">No Tax</option>
                        <option :value="5">5.00%</option>
                        <option :value="10">10.00%</option>
                        <option :value="18">18.00%</option>
                      </select>
                      <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-[#A8AAAE]">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="6 9 12 15 18 9"/></svg>
                      </div>
                    </div>
                  </td>
                  <td class="p-2.5 text-right font-bold text-[#4B465C] pr-4 text-sm">
                    {{ fmtCur(newItem.qty * newItem.rate) }}
                  </td>
                  <td class="p-2.5 text-center">
                    <button 
                      type="button"
                      class="w-8 h-8 bg-[#7367F0] hover:bg-[#685DD8] text-white rounded-md flex items-center justify-center cursor-pointer shadow-sm hover:scale-105 active:scale-95 transition-all mx-auto border-none" 
                      @click="addItem"
                      title="Add Line Item"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg>
                    </button>
                  </td>
                </tr>

                <!-- Listed, added estimate items -->
                <tr v-for="(item, idx) in form.items" :key="idx" class="added-item-row group hover:bg-[#F8F7FA] border-b border-[#F1F0F2] transition-colors">
                  <td class="text-center font-semibold text-[#A8AAAE] text-xs p-3">{{ idx + 1 }}</td>
                  <td class="p-3">
                    <div class="font-bold text-[#4B465C] text-sm">{{ item.name }}</div>
                    <span v-if="item.optional" class="text-[9px] font-bold bg-[#7367F0]/10 text-[#7367F0] border border-[#7367F0]/20 rounded px-2 py-0.5 mt-1 inline-block">Optional</span>
                  </td>
                  <td class="p-3 text-[#6F6B7D] leading-relaxed text-xs">{{ item.long_description || '—' }}</td>
                  <td class="p-3 text-center font-semibold text-[#4B465C]">
                    {{ item.qty }} 
                    <span class="text-[10px] text-[#A8AAAE] font-normal block mt-0.5">({{ item.unit }})</span>
                  </td>
                  <td class="p-3 text-right font-medium text-[#4B465C]">{{ fmtCur(item.rate) }}</td>
                  <td class="p-3 text-center">
                    <span v-if="item.tax > 0" class="inline-flex items-center gap-1 bg-[#FF9F43]/10 text-[#FF9F43] border border-[#FF9F43]/20 px-2 py-0.5 rounded text-[11px] font-bold">
                      {{ item.tax }}%
                    </span>
                    <span v-else class="text-[#A8AAAE] text-xs font-medium">None</span>
                  </td>
                  <td class="p-3 text-right font-bold text-[#4B465C] pr-4">{{ fmtCur(item.amount) }}</td>
                  <td class="p-3 text-center">
                    <button 
                      type="button"
                      class="text-[#A8AAAE] hover:text-rose-500 hover:bg-rose-50 rounded w-7 h-7 flex items-center justify-center border border-transparent hover:border-rose-100 cursor-pointer transition-all mx-auto" 
                      @click="removeItem(idx)"
                      title="Remove Item"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Subtotal / Adjustment / Totals Aligned right -->
          <div class="flex justify-end pt-3">
            <div class="w-80 bg-[#FFFFFF] border border-[#EBE9F1] rounded-lg p-5 space-y-3 shadow-sm">
              <div class="flex justify-between items-center text-xs pb-2 border-b border-[#F1F0F2]">
                <span class="text-[#6F6B7D] font-semibold">Sub Total</span>
                <span class="font-bold text-[#4B465C] text-sm">{{ fmtCur(totals.subtotal) }}</span>
              </div>
              
              <div class="flex justify-between items-center text-xs pb-2 border-b border-[#F1F0F2]">
                <span class="text-[#6F6B7D] font-semibold">Discount</span>
                <div class="flex items-center space-x-1.5">
                  <input 
                    type="number" 
                    min="0" 
                    max="100" 
                    class="border border-[#DBDADE] rounded text-xs text-right w-14 h-7 pr-1 focus:outline-none focus:border-[#7367F0]" 
                    v-model="form.discount_percent" 
                  />
                  <span class="text-xs font-semibold text-[#6F6B7D] bg-[#F8F7FA] border border-[#DBDADE] rounded px-1.5 py-0.5">%</span>
                </div>
                <span class="font-bold text-rose-500">-{{ fmtCur(totals.discount) }}</span>
              </div>

              <div class="flex justify-between items-center text-xs pb-2 border-b border-[#F1F0F2]">
                <span class="text-[#6F6B7D] font-semibold">Adjustment</span>
                <input 
                  type="number" 
                  class="border border-[#DBDADE] rounded text-xs text-right w-20 h-7 pr-1 focus:outline-none focus:border-[#7367F0]" 
                  v-model="form.adjustment" 
                />
                <span class="font-bold text-[#4B465C]">{{ fmtCur(form.adjustment) }}</span>
              </div>

              <div v-if="totals.tax > 0" class="flex justify-between items-center text-xs pb-2 border-b border-[#F1F0F2]">
                <span class="text-[#6F6B7D] font-semibold">Tax Total</span>
                <span class="font-bold text-[#4B465C]">{{ fmtCur(totals.tax) }}</span>
              </div>

              <div class="flex justify-between items-center pt-1">
                <span class="text-xs font-bold text-[#4B465C] uppercase tracking-wider">Total</span>
                <span class="text-xl text-[#7367F0] font-extrabold tracking-tight">{{ fmtCur(totals.total) }}</span>
              </div>
            </div>
          </div>

          <!-- Bottom Textareas: Client Note & Terms -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-[#F1F0F2]">
            <div class="p-4 bg-[#FFFFFF] border border-[#EBE9F1] rounded-lg space-y-2">
              <div class="flex items-center gap-2 pb-1">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="text-[#7367F0]"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <label class="text-xs font-bold uppercase tracking-wider text-[#4B465C]">Client Note</label>
              </div>
              <textarea 
                class="form-ctrl text-xs p-3 bg-[#FFFFFF] border-[#DBDADE] rounded-md transition-all min-h-[90px] resize-none" 
                rows="3" 
                v-model="form.client_note"
                placeholder="Write a note visible to the recipient..."
              ></textarea>
            </div>
            <div class="p-4 bg-[#FFFFFF] border border-[#EBE9F1] rounded-lg space-y-2">
              <div class="flex items-center gap-2 pb-1">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="text-[#7367F0]"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <label class="text-xs font-bold uppercase tracking-wider text-[#4B465C]">Terms & Conditions</label>
              </div>
              <textarea 
                class="form-ctrl text-xs p-3 bg-[#FFFFFF] border-[#DBDADE] rounded-md transition-all min-h-[90px] resize-none text-[#5D596C]" 
                rows="3" 
                v-model="form.terms"
                placeholder="Standard contract terms and conditions..."
              ></textarea>
            </div>
          </div>

        </div>

        <!-- Action Bar Footer -->
        <div class="border-t border-[#F1F0F2] bg-[#F8F7FA] px-7 py-4.5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 text-xs text-[#6F6B7D] rounded-b-[10px] -mx-7 -mb-7 mt-7">
          <div class="flex items-center space-x-2">
            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-[#7367F0]/10 text-[#7367F0]">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm1 14h-2v-4h2zm0-6h-2V7h2z"/></svg>
            </span>
            <span class="text-[#6F6B7D] font-medium">Generate and save estimates for clients</span>
          </div>
          <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
            <button class="btn-outline px-5 py-2 text-xs font-semibold" @click="saveAndSend">
              <span class="flex items-center gap-1.5">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M22 2L11 13"/><path d="M22 2L15 22l-4-9-9-4z"/></svg>
                Save & Send
              </span>
            </button>
            <button class="btn-primary px-6 py-2 text-xs font-bold" @click="save">
              <span class="flex items-center gap-1.5">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Estimate
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Address Edit Modal -->
    <transition name="fade">
      <div v-if="showAddressModal" class="modal-overlay" @click.self="closeAddressModal">
        <div class="modal-card border border-[#EBE9F1] rounded-xl shadow-[0_20px_25px_-5px_rgba(0,0,0,0.1)]">
          <div class="modal-head px-6 py-4 border-b border-[#F1F0F2] flex items-center justify-between bg-[#FFFFFF]">
            <div class="flex items-center space-x-2">
              <div class="w-1.5 h-4 rounded-full bg-[#7367F0]"></div>
              <span class="modal-title font-bold text-[#4B465C] text-sm">Billing & Shipping Address</span>
            </div>
            <button class="modal-close text-[#A8AAAE] hover:text-[#4B465C] font-bold text-xl cursor-pointer bg-transparent border-none" @click="closeAddressModal">×</button>
          </div>
          
          <div class="modal-body p-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-xs overflow-y-auto">
            <!-- Billing Address Column -->
            <div class="space-y-3.5">
              <div class="border-b border-[#F1F0F2] pb-2 flex items-center space-x-2">
                <span class="text-[10px] font-bold text-[#7367F0] bg-[#7367F0]/10 px-2 py-0.5 rounded">BILLING</span>
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">Street</label>
                <textarea class="form-ctrl text-xs p-2.5 bg-white rounded-md border-[#DBDADE]" rows="2" v-model="addressForm.billing_street" placeholder="Street Address"></textarea>
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">City</label>
                <input type="text" class="form-ctrl text-xs h-[36px] px-3 bg-white rounded-md border-[#DBDADE]" v-model="addressForm.billing_city" placeholder="City" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">State</label>
                <input type="text" class="form-ctrl text-xs h-[36px] px-3 bg-white rounded-md border-[#DBDADE]" v-model="addressForm.billing_state" placeholder="State" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">Zip Code</label>
                <input type="text" class="form-ctrl text-xs h-[36px] px-3 bg-white rounded-md border-[#DBDADE]" v-model="addressForm.billing_zip" placeholder="Zip Code" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">Country</label>
                <input type="text" class="form-ctrl text-xs h-[36px] px-3 bg-white rounded-md border-[#DBDADE]" v-model="addressForm.billing_country" placeholder="Country" />
              </div>
            </div>

            <!-- Shipping Address Column -->
            <div class="space-y-3.5">
              <div class="border-b border-[#F1F0F2] pb-2 flex justify-between items-center">
                <span class="text-[10px] font-bold text-[#7367F0] bg-[#7367F0]/10 px-2 py-0.5 rounded">SHIPPING</span>
                <label class="flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-[#6F6B7D]">
                  <input type="checkbox" v-model="addressForm.show_shipping_details" class="rounded border-[#DBDADE] text-[#7367F0] focus:ring-[#7367F0]" />
                  <span>Show in estimate</span>
                </label>
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">Street</label>
                <textarea class="form-ctrl text-xs p-2.5 bg-white rounded-md border-[#DBDADE]" rows="2" v-model="addressForm.shipping_street" :disabled="!addressForm.show_shipping_details" placeholder="Street Address"></textarea>
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">City</label>
                <input type="text" class="form-ctrl text-xs h-[36px] px-3 bg-white rounded-md border-[#DBDADE]" v-model="addressForm.shipping_city" :disabled="!addressForm.show_shipping_details" placeholder="City" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">State</label>
                <input type="text" class="form-ctrl text-xs h-[36px] px-3 bg-white rounded-md border-[#DBDADE]" v-model="addressForm.shipping_state" :disabled="!addressForm.show_shipping_details" placeholder="State" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">Zip Code</label>
                <input type="text" class="form-ctrl text-xs h-[36px] px-3 bg-white rounded-md border-[#DBDADE]" v-model="addressForm.shipping_zip" :disabled="!addressForm.show_shipping_details" placeholder="Zip Code" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">Country</label>
                <input type="text" class="form-ctrl text-xs h-[36px] px-3 bg-white rounded-md border-[#DBDADE]" v-model="addressForm.shipping_country" :disabled="!addressForm.show_shipping_details" placeholder="Country" />
              </div>
            </div>
          </div>
          
          <div class="modal-foot px-6 py-3.5 border-t border-[#F1F0F2] flex justify-end gap-2.5 bg-[#FFFFFF]">
            <button class="btn-ghost px-4 py-2 text-xs font-semibold" @click="closeAddressModal">
              Cancel
            </button>
            <button class="btn-primary px-5 py-2 text-xs font-bold" @click="saveAddresses">
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
  font-family: 'Public Sans', 'Inter', -apple-system, sans-serif;
  color: #5D596C;
}

/* Universal Rectangular Form Control Styles */
.form-ctrl {
  border: 1px solid #DBDADE;
  border-radius: 6px;
  padding: 8px 12px;
  width: 100%;
  color: #4B465C;
  outline: none;
  font-family: inherit;
  font-size: 13px;
  background-color: #FFFFFF;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.form-ctrl:focus {
  border-color: #7367F0;
  background-color: #FFFFFF;
  box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.16);
}

/* Ant-Design Select & Picker overrides */
:deep(.ant-select-single:not(.ant-select-customize-input) .ant-select-selector) {
  border-radius: 6px !important;
  border: 1px solid #DBDADE !important;
  min-height: 38px !important;
  height: 38px !important;
  display: flex !important;
  align-items: center !important;
  padding: 0 12px !important;
  background-color: #FFFFFF !important;
  box-shadow: none !important;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

:deep(.ant-select-multiple:not(.ant-select-customize-input) .ant-select-selector) {
  border-radius: 6px !important;
  border: 1px solid #DBDADE !important;
  min-height: 38px !important;
  height: auto !important;
  display: flex !important;
  align-items: center !important;
  flex-wrap: wrap !important;
  padding: 3px 8px !important;
  background-color: #FFFFFF !important;
  box-shadow: none !important;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

:deep(.ant-select-focused:not(.ant-select-disabled).ant-select:not(.ant-select-customize-input) .ant-select-selector),
:deep(.ant-select:hover .ant-select-selector) {
  border-color: #7367F0 !important;
}

:deep(.ant-select-focused:not(.ant-select-disabled).ant-select:not(.ant-select-customize-input) .ant-select-selector) {
  box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.16) !important;
}

:deep(.ant-select-single .ant-select-selection-item) {
  font-size: 13px !important;
  font-weight: 500 !important;
  color: #4B465C !important;
  line-height: 36px !important;
}

:deep(.ant-select-single .ant-select-selection-placeholder) {
  font-size: 13px !important;
  color: #A8AAAE !important;
  line-height: 36px !important;
}

:deep(.ant-select-multiple .ant-select-selection-item) {
  height: 26px !important;
  line-height: 24px !important;
  font-size: 12px !important;
  font-weight: 500 !important;
  background: rgba(115, 103, 240, 0.1) !important;
  color: #7367F0 !important;
  border: 1px solid rgba(115, 103, 240, 0.2) !important;
  border-radius: 4px !important;
  padding: 0 8px !important;
  margin: 2px 4px 2px 0 !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 4px !important;
}

:deep(.ant-select-multiple .ant-select-selection-item-content) {
  font-size: 12px !important;
  line-height: 24px !important;
  color: #7367F0 !important;
  display: inline-block !important;
}

:deep(.ant-select-multiple .ant-select-selection-item-remove) {
  display: inline-flex !important;
  align-items: center !important;
  color: #7367F0 !important;
  font-size: 12px !important;
  margin-left: 2px !important;
  cursor: pointer !important;
}

:deep(.ant-select-multiple .ant-select-selection-placeholder) {
  line-height: 30px !important;
  padding: 0 4px !important;
  font-size: 13px !important;
  color: #A8AAAE !important;
}

/* Disabled styling */
.form-ctrl:disabled {
  background-color: #F8F7FA;
  border-color: #DBDADE;
  color: #A8AAAE;
  cursor: not-allowed;
}

.form-ctrl::placeholder {
  color: #A8AAAE;
}

.items-table {
  width: 100%;
  border-collapse: collapse;
}

.items-table th {
  background: #F8F7FA;
  padding: 12px 14px;
  font-size: 11px;
  font-weight: 700;
  color: #6F6B7D;
  text-transform: uppercase;
  letter-spacing: .05em;
  text-align: left;
  border-bottom: 1px solid #EBE9F1;
}

.items-table td {
  border-bottom: 1px solid #F1F0F2;
  vertical-align: top;
  padding: 10px 14px;
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
