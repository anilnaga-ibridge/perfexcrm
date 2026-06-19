<template>
  <div class="proposal-form-page space-y-6">
    <!-- Breadcrumb / Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-2 text-xs text-slate-400">
        <router-link to="/admin/proposals" class="hover:text-indigo-600 font-semibold">Proposals</router-link>
        <span>/</span>
        <span class="text-slate-600 font-semibold">{{ editMode ? 'Edit Proposal' : 'New Proposal' }}</span>
      </div>
      <router-link to="/admin/proposals" class="text-xs text-indigo-600 hover:underline flex items-center gap-1 font-semibold">
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
            {{ editMode ? 'Edit Proposal #' + (form.number || '') : 'New Proposal' }}
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
              <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Proposal Details</span>
            </div>

            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                <span class="text-rose-500">*</span> Subject
              </label>
              <input 
                type="text" 
                class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all" 
                v-model="form.subject" 
                placeholder="Proposal Title/Subject" 
              />
            </div>

            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                <span class="text-rose-500">*</span> Related
              </label>
              <div class="relative">
                <select class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all appearance-none cursor-pointer pr-10" v-model="form.to">
                  <option value="" disabled>Nothing selected</option>
                  <option v-for="target in targets" :key="target" :value="target">{{ target }}</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                  <span class="text-rose-500">*</span> Date
                </label>
                <input type="date" class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all" v-model="form.date" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Open Till</label>
                <input type="date" class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all" v-model="form.open_till" />
              </div>
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
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Tags</label>
              <input 
                type="text" 
                class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all" 
                v-model="form.tags" 
                placeholder="Tag" 
              />
            </div>

            <div class="flex items-center justify-between pt-2">
              <div>
                <label class="block text-xs font-semibold text-slate-700">Allow Comments</label>
                <span class="text-[10px] text-slate-400">Permit clients to comment directly on proposal</span>
              </div>
              <a-switch v-model:checked="form.allow_comments" size="default" />
            </div>
          </div>

          <!-- Right Column -->
          <div class="p-6 bg-slate-50/30 rounded-xl border border-slate-100/80 space-y-5">
            <div class="flex items-center space-x-2 pb-2 border-b border-slate-100">
              <span class="text-[10px] font-bold text-violet-600 bg-violet-50 px-2 py-0.5 rounded">02</span>
              <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Recipient & Status</span>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Status</label>
                <div class="relative">
                  <select class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all appearance-none cursor-pointer pr-10" v-model="form.status">
                    <option value="draft">Draft</option>
                    <option value="open">Open</option>
                    <option value="sent">Sent</option>
                    <option value="accepted">Accepted</option>
                    <option value="declined">Declined</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Assigned</label>
                <div class="relative">
                  <select class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all appearance-none cursor-pointer pr-10" v-model="form.assigned">
                    <option v-for="staff in staffOptions" :key="staff" :value="staff">{{ staff }}</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                <span class="text-rose-500">*</span> To
              </label>
              <input 
                type="text" 
                class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all" 
                v-model="form.recipient_name" 
                placeholder="Customer or recipient name"
              />
            </div>

            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Address</label>
              <textarea 
                class="form-ctrl text-xs p-3 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all min-h-[96px]" 
                rows="4" 
                v-model="form.address"
                placeholder="Recipient address..."
              ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">City</label>
                <input type="text" class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all" v-model="form.city" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">State</label>
                <input type="text" class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all" v-model="form.state" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Country</label>
                <div class="relative">
                  <select class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all appearance-none cursor-pointer pr-10" v-model="form.country">
                    <option value="">Nothing selected</option>
                    <option value="United States">United States</option>
                    <option value="United Kingdom">United Kingdom</option>
                    <option value="Germany">Germany</option>
                    <option value="France">France</option>
                    <option value="Japan">Japan</option>
                    <option value="Australia">Australia</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Zip Code</label>
                <input type="text" class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all" v-model="form.zip" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                  <span class="text-rose-500">*</span> Email
                </label>
                <input type="email" class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all" v-model="form.email" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Phone</label>
                <input type="text" class="form-ctrl text-xs h-10 px-3.5 bg-slate-50/50 hover:bg-slate-50 border-slate-200 focus:bg-white rounded-lg transition-all" v-model="form.phone" />
              </div>
            </div>
          </div>

        </div>

        <!-- Items Section -->
        <div class="space-y-6 pt-6 border-t border-slate-100">
          
          <div class="flex items-center space-x-2 pb-2">
            <span class="text-[10px] font-bold text-sky-600 bg-sky-50 px-2 py-0.5 rounded">03</span>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Proposal Items</span>
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
                >
                  <a-select-option v-for="item in predefinedItems" :key="item.name" :value="item.name">
                    {{ item.name }} ({{ fmtCur(item.rate) }})
                  </a-select-option>
                </a-select>
              </div>
              <button class="flex items-center justify-center bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-lg w-10 h-10 transition-colors cursor-pointer shadow-sm hover:scale-102" @click="addPredefinedItem">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              </button>
            </div>
            
            <div class="flex items-center space-x-3 bg-slate-100 p-1 rounded-xl w-fit self-end md:self-auto shadow-inner">
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
                <tr class="bg-slate-50/50">
                  <th class="w-10 text-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13" class="text-slate-400 mx-auto"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg></th>
                  <th>Item</th>
                  <th>Description</th>
                  <th class="w-24 text-center">Qty / Unit</th>
                  <th class="w-28 text-right">Rate</th>
                  <th class="w-32 text-center">Tax</th>
                  <th class="w-32 text-right pr-6">Amount</th>
                  <th class="w-14 text-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="text-slate-400 mx-auto"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></th>
                </tr>
              </thead>
              <tbody>
                <!-- Row to add new items (matches image layout) -->
                <tr class="add-item-row bg-indigo-50/10">
                  <td class="text-center font-bold text-indigo-400">+</td>
                  <td class="p-4">
                    <textarea 
                      class="form-ctrl text-xs p-2.5 min-h-[58px] bg-white border-slate-200 focus:border-indigo-500 rounded-lg" 
                      rows="2" 
                      v-model="newItem.name" 
                      placeholder="Description"
                    ></textarea>
                    <label class="flex items-center gap-2 mt-2 text-[10px] text-slate-500 font-semibold cursor-pointer">
                      <input type="checkbox" v-model="newItem.optional" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                      <span>This item is optional</span>
                    </label>
                  </td>
                  <td class="p-4">
                    <textarea 
                      class="form-ctrl text-xs p-2.5 min-h-[58px] bg-white border-slate-200 focus:border-indigo-500 rounded-lg" 
                      rows="2" 
                      v-model="newItem.long_description" 
                      placeholder="Long description"
                    ></textarea>
                  </td>
                  <td class="p-4">
                    <input type="number" class="form-ctrl text-xs text-center h-8 bg-white border-slate-200 rounded-md" v-model="newItem.qty" />
                    <input type="text" class="form-ctrl text-[10px] text-center mt-2 placeholder-slate-300 h-8 bg-white border-slate-200 rounded-md font-medium" v-model="newItem.unit" placeholder="Unit" />
                  </td>
                  <td class="p-4">
                    <input type="number" class="form-ctrl text-xs text-right h-8 bg-white border-slate-200 rounded-md font-semibold" v-model="newItem.rate" placeholder="Rate" />
                  </td>
                  <td class="p-4">
                    <div class="relative">
                      <select class="form-ctrl text-xs bg-white h-8 border-slate-200 rounded-md appearance-none cursor-pointer pr-8 font-medium" v-model="newItem.tax">
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
                  <td class="p-4 text-right font-bold text-slate-500 pr-6">
                    {{ fmtCur(newItem.qty * newItem.rate) }}
                  </td>
                  <td class="p-4 text-center">
                    <button class="add-check-btn bg-slate-800 hover:bg-slate-900 text-white rounded-lg flex items-center justify-center cursor-pointer shadow-sm hover:scale-105 transition-transform" @click="addItem">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg>
                    </button>
                  </td>
                </tr>

                <!-- Listed, added proposal items -->
                <tr v-for="(item, idx) in form.items" :key="idx" class="added-item-row group hover:bg-slate-50/60 transition-colors">
                  <td class="text-center font-semibold text-slate-400">{{ idx + 1 }}</td>
                  <td class="p-4">
                    <div class="font-bold text-slate-800 text-sm">{{ item.name }}</div>
                    <span v-if="item.optional" class="text-[9px] font-bold bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-full px-2.5 py-0.5 mt-1.5 inline-block">Optional</span>
                  </td>
                  <td class="p-4 text-slate-500 leading-relaxed">{{ item.long_description || '—' }}</td>
                  <td class="p-4 text-center font-semibold text-slate-700">
                    {{ item.qty }} 
                    <span class="text-[10px] text-slate-400 font-normal block mt-0.5">({{ item.unit }})</span>
                  </td>
                  <td class="p-4 text-right font-medium text-slate-600">{{ fmtCur(item.rate) }}</td>
                  <td class="p-4 text-center text-slate-500 font-medium">
                    <span :class="item.tax > 0 ? 'bg-amber-50 text-amber-700 border border-amber-100 px-2 py-0.5 rounded-full text-[10px] font-bold' : ''">
                      {{ item.tax > 0 ? item.tax + '%' : 'None' }}
                    </span>
                  </td>
                  <td class="p-4 text-right font-bold text-slate-800 pr-6">{{ fmtCur(item.amount) }}</td>
                  <td class="p-4 text-center">
                    <button class="text-rose-500 hover:text-rose-700 font-bold text-base hover:bg-rose-50 rounded-lg w-8 h-8 flex items-center justify-center border border-transparent hover:border-rose-100 cursor-pointer transition-colors mx-auto" @click="removeItem(idx)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Subtotal / Adjustment / Totals Aligned right -->
          <div class="flex justify-end pt-4">
            <div class="w-80 bg-slate-50/80 border border-slate-100 rounded-2xl p-5 space-y-3.5 shadow-sm">
              <div class="flex justify-between items-center text-xs pb-2 border-b border-slate-200/50">
                <span class="text-slate-500 font-semibold">Sub Total</span>
                <span class="font-bold text-slate-800 text-sm">{{ fmtCur(totals.subtotal) }}</span>
              </div>
              
              <div class="flex justify-between items-center text-xs pb-2 border-b border-slate-200/50">
                <span class="text-slate-500 font-semibold">Discount</span>
                <div class="flex items-center space-x-1.5">
                  <input 
                    type="number" 
                    min="0" 
                    max="100" 
                    class="border border-slate-200 rounded-md text-xs text-right w-14 h-7 pr-1 focus:outline-none focus:border-indigo-500" 
                    v-model="form.discount_percent" 
                  />
                  <span class="text-xs font-semibold text-slate-500 bg-slate-200/50 rounded px-1.5 py-0.5">%</span>
                </div>
                <span class="font-bold text-rose-500">-{{ fmtCur(totals.discount) }}</span>
              </div>

              <div class="flex justify-between items-center text-xs pb-2 border-b border-slate-200/50">
                <span class="text-slate-500 font-semibold">Adjustment</span>
                <input 
                  type="number" 
                  class="border border-slate-200 rounded-md text-xs text-right w-20 h-7 pr-1 focus:outline-none focus:border-indigo-500" 
                  v-model="form.adjustment" 
                />
                <span class="font-bold text-slate-800">{{ fmtCur(form.adjustment) }}</span>
              </div>

              <div v-if="totals.tax > 0" class="flex justify-between items-center text-xs pb-2 border-b border-slate-200/50">
                <span class="text-slate-500 font-semibold">Tax Total</span>
                <span class="font-bold text-slate-800">{{ fmtCur(totals.tax) }}</span>
              </div>

              <div class="flex justify-between items-center pt-2">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Total</span>
                <span class="text-xl text-slate-900 font-extrabold tracking-tight">{{ fmtCur(totals.total) }}</span>
              </div>
            </div>
          </div>

        </div>

        <!-- Action Bar Footer -->
        <div class="border-t border-slate-100 bg-slate-50/50 px-8 py-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 text-xs text-slate-400 rounded-b-2xl -mx-8 -mb-8 mt-8">
          <div class="italic flex items-center space-x-1.5">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="text-indigo-400"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm1 14h-2v-4h2zm0-6h-2V7h2z"/></svg>
            <span>Include proposal items with merge field anywhere in proposal content as <strong class="text-slate-600 font-semibold">{proposal_items}</strong></span>
          </div>
          <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
            <button class="btn-outline-sm border border-slate-200 rounded-xl px-5 py-2.5 font-bold hover:bg-slate-100/50 hover:border-slate-300 hover:text-slate-700 bg-white cursor-pointer transition-all hover:-translate-y-0.5" @click="saveAndSend">
              Save & Send
            </button>
            <button class="btn-primary-sm bg-slate-900 hover:bg-black text-white rounded-xl px-6 py-2.5 font-bold cursor-pointer transition-all hover:shadow-md hover:-translate-y-0.5" @click="save">
              Save
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useProposalsStore } from '../../store/proposalsStore';
import { message } from 'ant-design-vue';
import axios from 'axios';

export default defineComponent({
  name: 'ProposalForm',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const proposalsStore = useProposalsStore();

    const editMode = ref(false);
    const proposalId = ref(null);
    const discountUnit = ref('%');

    // Default Client Options
    const targets = ref([
      'Nader-Abernathy', 'Mertz-Bergnaum', 'Schroeder and Sons', 'Bayer Group',
      'Halvorson LLC', 'Kub Group', 'Morar-Runte', 'Torphy and Partners',
      'John Doe (Lead)', 'Sarah Connor (Lead)', 'Bruce Wayne (Lead)'
    ]);

    // Staff Dropdown Options
    const staffOptions = [
      'Tom Kunze',
      'Armando Turcotte',
      'Elias Konopelski',
      'Tamara Howell',
      'Marcus Lesch',
      'Rosie Trantow'
    ];

    // Predefined items dropdown template values
    const predefinedItems = [
      { name: 'Consulting SLA Services', rate: 1500 },
      { name: 'App Development Module v1.0', rate: 8500 },
      { name: 'Cloud Infrastructure Audit Pack', rate: 3200 },
      { name: 'UI/UX Interactive Mockup Designs', rate: 2400 }
    ];

    const selectedPredefined = ref('');

    // Master Form state matching screenshot pre-fills
    const form = ref({
      subject: '',
      to: '',
      date: '2026-06-15',
      open_till: '2026-06-22',
      currency: 'USD',
      discount_type: 'No discount',
      tags: '',
      allow_comments: true,
      status: 'draft',
      assigned: 'Tom Kunze',
      recipient_name: '',
      address: '',
      city: '',
      state: '',
      country: '',
      zip: '',
      email: '',
      phone: '',
      qty_show: 'Qty',
      items: [],
      discount_percent: 0,
      adjustment: 0
    });

    // Sub-item template state
    const newItem = ref({
      name: '',
      long_description: '',
      qty: 1,
      unit: 'Unit',
      rate: 0,
      tax: 0,
      optional: false
    });

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

      // Clear new item state
      newItem.value = {
        name: '',
        long_description: '',
        qty: 1,
        unit: 'Unit',
        rate: 0,
        tax: 0,
        optional: false
      };
      message.success('Item added to proposal.');
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

    // Live Totals calculation
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
      if (!form.value.subject) {
        message.error('Subject is required.');
        return false;
      }
      if (!form.value.to) {
        message.error('Related Client/Lead must be selected.');
        return false;
      }
      if (!form.value.recipient_name) {
        message.error('Recipient Name is required.');
        return false;
      }
      if (!form.value.email) {
        message.error('Recipient Email is required.');
        return false;
      }
      return true;
    };

    const save = () => {
      if (!validateForm()) return;

      const payload = {
        ...form.value,
        amount: totals.value.total
      };

      if (editMode.value) {
        proposalsStore.updateProposal(proposalId.value, payload);
        message.success('Proposal updated successfully.');
      } else {
        proposalsStore.addProposal(payload);
        message.success('Proposal created successfully.');
      }

      const relId = route.query.rel_id;
      if (relId) {
        router.push({ name: 'admin.customers.view', params: { id: relId }, query: { tab: 'proposals' } });
      } else {
        router.push('/admin/proposals');
      }
    };

    const saveAndSend = () => {
      if (!validateForm()) return;
      
      const payload = {
        ...form.value,
        amount: totals.value.total,
        status: 'sent'
      };

      if (editMode.value) {
        proposalsStore.updateProposal(proposalId.value, payload);
      } else {
        proposalsStore.addProposal(payload);
      }
      message.loading('Sending email to recipient...', 1.5).then(() => {
        message.success('Proposal saved & successfully emailed to recipient.');
        const relId = route.query.rel_id;
        if (relId) {
          router.push({ name: 'admin.customers.view', params: { id: relId }, query: { tab: 'proposals' } });
        } else {
          router.push('/admin/proposals');
        }
      });
    };

    const fmtCur = (v) => '$' + Number(v).toLocaleString('en-US', { minimumFractionDigits: 2 });

    onMounted(async () => {
      if (route.params.id) {
        editMode.value = true;
        proposalId.value = parseInt(route.params.id);
        
        const match = proposalsStore.proposals.find(p => p.id === proposalId.value);
        if (match) {
          form.value = JSON.parse(JSON.stringify(match));
        } else {
          message.error('Proposal details not found.');
          router.push('/admin/proposals');
        }
      } else {
        const relId = route.query.rel_id;
        const relType = route.query.rel_type || 'customer';
        if (relId && relType === 'customer') {
          try {
            const res = await axios.get(`/clients/${relId}`);
            const client = res.data;
            if (client) {
              if (!targets.value.includes(client.company)) {
                targets.value.push(client.company);
              }
              form.value.to = client.company;
              form.value.recipient_name = client.company;
              form.value.address = client.address || '';
              form.value.city = client.city || '';
              form.value.state = client.state || '';
              form.value.country = client.country || '';
              form.value.zip = client.zip || '';
              form.value.email = client.email || '';
              form.value.phone = client.phone || '';
            }
          } catch (err) {
            console.error('Failed to load client details for prefill', err);
          }
        } else {
          // Pre-fill related values when related select changes
          form.value.to = targets.value[0];
        }
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
      targets,
      staffOptions,
      save,
      saveAndSend,
      editMode,
      fmtCur,
      discountUnit
    };
  }
});
</script>

<style scoped>
.proposal-form-page {
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
</style>
