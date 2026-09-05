<template>
  <div class="credit-note-stepper-page space-y-6">
    <!-- Breadcrumbs / Top Bar -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-2 text-xs text-slate-400">
        <router-link :to="cancelLink" class="hover:text-indigo-600 font-semibold">Credit Notes</router-link>
        <span>/</span>
        <span class="text-slate-600 font-semibold">{{ isEdit ? 'Edit Credit Note' : 'New Credit Note' }}</span>
      </div>
      <router-link :to="cancelLink" class="btn-outline text-xs font-semibold flex items-center gap-1.5 py-1.5 px-3">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Back to List
      </router-link>
    </div>

    <!-- Main Stepper Card -->
    <div class="vuexy-form-card bg-white border border-[#EBE9F1] rounded-[10px] shadow-[0_2px_9px_rgba(47,43,61,0.06)] overflow-hidden">
      <!-- Title Header -->
      <div class="px-7 py-4.5 border-b border-[#F1F0F2] flex justify-between items-center bg-[#FFFFFF]">
        <div class="flex items-center space-x-3">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#7367F0] to-[#9F8ED6]"></div>
          <h2 class="text-base font-bold text-[#4B465C] m-0">
            {{ isEdit ? 'Edit Credit Note #' + (form.number || '') : 'Create New Credit Note' }}
          </h2>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 min-h-[580px]">
        <!-- ── Left Column: Vuexy Vertical Stepper ── -->
        <div class="lg:col-span-4 p-6 lg:p-8 border-b lg:border-b-0 lg:border-r border-[#F1F0F2] bg-[#FFFFFF]">
          <div class="space-y-6">
            <div
              v-for="(step, index) in numberedSteps"
              :key="index"
              class="relative flex items-start gap-4 cursor-pointer group select-none"
              @click="currentStep = index"
            >
              <!-- Connecting Line -->
              <div
                v-if="index < numberedSteps.length - 1"
                class="absolute left-5 top-11 w-0.5 h-12 transition-all"
                :class="currentStep > index ? 'bg-[#7367F0]' : 'bg-[#DBDADE]'"
              ></div>

              <!-- Step Bullet -->
              <div
                class="w-10 h-10 rounded-lg flex items-center justify-center font-bold text-sm transition-all shrink-0 z-10"
                :class="[
                  currentStep === index
                    ? 'bg-[#7367F0] text-white shadow-[0_2px_6px_rgba(115,103,240,0.48)] scale-105'
                    : currentStep > index
                    ? 'bg-[#7367F0]/15 text-[#7367F0]'
                    : 'bg-[#F8F7FA] text-[#6F6B7D] border border-[#DBDADE] group-hover:border-[#7367F0]'
                ]"
              >
                <svg v-if="currentStep > index" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="16" height="16">
                  <polyline points="20 6 9 17 4 12" />
                </svg>
                <span v-else>{{ String(index + 1).padStart(2, '0') }}</span>
              </div>

              <!-- Step Text -->
              <div class="pt-0.5 flex-1 min-w-0">
                <div
                  class="text-sm font-semibold transition-colors truncate"
                  :class="currentStep === index ? 'text-[#7367F0]' : 'text-[#4B465C] group-hover:text-[#7367F0]'"
                >
                  {{ step.title }}
                </div>
                <div class="text-xs text-[#A8AAAE] mt-0.5 truncate">
                  {{ step.subtitle }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── Right Column: Step Content Panels ── -->
        <div class="lg:col-span-8 p-6 lg:p-8 flex flex-col justify-between bg-[#FFFFFF]">
          <div>
            <!-- Step 0: Customer & Address -->
            <div v-show="currentStep === 0" class="space-y-6">
              <div>
                <h3 class="text-base font-bold text-[#4B465C] m-0">Customer &amp; Address</h3>
                <p class="text-xs text-[#A8AAAE] mt-1 mb-0">Select the customer and review billing details</p>
              </div>

              <div class="space-y-4 pt-2">
                <div>
                  <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                    <span class="text-rose-500">*</span> Customer
                  </label>
                  <a-select
                    v-model:value="form.client_id"
                    show-search
                    :filter-option="(input, option) => (option.label || '').toLowerCase().includes(input.toLowerCase())"
                    placeholder="Search and select a customer..."
                    style="width: 100%"
                    @change="onClientChange"
                    :disabled="isEdit"
                    class="vuexy-select"
                  >
                    <a-select-option v-for="c in clientOptions" :key="c.id" :value="c.id" :label="c.company">
                      {{ c.company }} <span v-if="c.city" class="text-slate-400 text-xs">({{ c.city }})</span>
                    </a-select-option>
                  </a-select>
                </div>

                <!-- Client Info Card -->
                <div v-if="selectedClient" class="flex items-center gap-3 p-3.5 bg-[#F8F7FA] border border-[#EBE9F1] rounded-md">
                  <div class="w-9 h-9 rounded-md bg-[#7367F0]/10 text-[#7367F0] flex items-center justify-center text-sm font-bold shrink-0">
                    {{ selectedClient.company?.charAt(0) }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-[#4B465C] truncate">{{ selectedClient.company }}</div>
                    <div class="flex items-center gap-3 text-[11px] text-[#A8AAAE] mt-0.5">
                      <span v-if="selectedClient.email" class="truncate">{{ selectedClient.email }}</span>
                      <span v-if="selectedClient.phone">{{ selectedClient.phone }}</span>
                    </div>
                  </div>
                </div>

                <!-- Address Details Box -->
                <div class="space-y-2 pt-1">
                  <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-[#4B465C]">Billing Address</label>
                    <button
                      type="button"
                      class="text-[#7367F0] hover:text-[#5E50EE] cursor-pointer flex items-center gap-1 text-xs font-semibold transition-colors bg-transparent border-none p-0"
                      @click="openAddressModal"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                      Edit billing address
                    </button>
                  </div>

                  <div class="text-xs text-[#5D596C] p-4 bg-[#F8F7FA] rounded-md border border-[#EBE9F1]">
                    <div class="font-bold text-[#4B465C] mb-1.5 flex items-center gap-1.5">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="text-[#7367F0]"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                      Bill To
                    </div>
                    <div class="font-medium text-[#5D596C] space-y-1 leading-relaxed">
                      <div>{{ form.billing_street || '--' }}</div>
                      <div>{{ form.billing_city || '--' }}{{ form.billing_state ? ', ' + form.billing_state : '' }}</div>
                      <div>{{ form.billing_country || '--' }}{{ form.billing_zip ? ', ' + form.billing_zip : '' }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 1: Credit Note Details -->
            <div v-show="currentStep === 1" class="space-y-6">
              <div>
                <h3 class="text-base font-bold text-[#4B465C] m-0">Credit Note Details</h3>
                <p class="text-xs text-[#A8AAAE] mt-1 mb-0">Configure numbers, dates and administrative notes</p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <!-- Credit Note Number -->
                <div>
                  <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                    <span class="text-rose-500">*</span> Credit Note Number
                  </label>
                  <div class="flex rounded-md overflow-hidden border border-[#DBDADE] focus-within:border-[#7367F0] focus-within:ring-2 focus-within:ring-[#7367F0]/15 transition-all">
                    <span class="inline-flex items-center px-3.5 bg-[#F8F7FA] text-[#6F6B7D] text-xs font-bold border-r border-[#DBDADE]">CN-</span>
                    <input
                      type="text"
                      class="w-full text-xs h-[38px] px-3.5 bg-white border-0 outline-none text-[#4B465C] font-semibold"
                      v-model="form.number"
                      placeholder="000001"
                      :disabled="isEdit"
                    />
                  </div>
                </div>

                <!-- Date -->
                <div>
                  <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                    <span class="text-rose-500">*</span> Credit Note Date
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

                <!-- Currency -->
                <div>
                  <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                    <span class="text-rose-500">*</span> Currency
                  </label>
                  <div class="relative">
                    <select class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10" v-model="form.currency">
                      <option value="USD">USD ($)</option>
                      <option value="EUR">EUR (€)</option>
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
                    type="text"
                    class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all"
                    v-model="form.reference"
                    placeholder="PO Number / Ref"
                  />
                </div>

                <!-- Discount Type -->
                <div>
                  <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Discount Type</label>
                  <div class="relative">
                    <select class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10" v-model="form.discount_type">
                      <option value="no_discount">No discount</option>
                      <option value="before_tax">Before Tax</option>
                      <option value="after_tax">After Tax</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                  </div>
                </div>

                <!-- Status -->
                <div>
                  <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Status</label>
                  <div class="relative">
                    <select class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10" v-model="form.status">
                      <option value="Open">Open</option>
                      <option value="Closed">Closed</option>
                      <option value="Void">Void</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                  </div>
                </div>

                <!-- Admin Note (Full Width) -->
                <div class="md:col-span-2">
                  <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Admin Note</label>
                  <textarea
                    class="form-ctrl text-xs p-3 bg-white border-[#DBDADE] rounded-md transition-all min-h-[70px]"
                    rows="2"
                    v-model="form.admin_note"
                    placeholder="Internal admin notes..."
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- Step 2: Items & Totals -->
            <div v-show="currentStep === 2" class="space-y-6">
              <div>
                <h3 class="text-base font-bold text-[#4B465C] m-0">Items &amp; Totals</h3>
                <p class="text-xs text-[#A8AAAE] mt-1 mb-0">Add invoice items and calculate final credit amount</p>
              </div>

              <!-- Add Predefined Item & Show Qty As Toolbar -->
              <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-1">
                <div class="flex items-center space-x-2.5 max-w-md w-full">
                  <div class="flex-1">
                    <a-select
                      v-model:value="selectedPredefined"
                      placeholder="Choose predefined item to add..."
                      style="width: 100%"
                      allow-clear
                      show-search
                      @change="handlePredefinedItemSelect"
                      class="vuexy-select"
                    >
                      <a-select-option v-for="item in predefinedItems" :key="item.name" :value="item.name">
                        <div class="flex items-center justify-between w-full">
                          <span class="text-xs font-medium text-[#4B465C]">{{ item.name }}</span>
                          <span class="text-[11px] font-bold text-[#7367F0] bg-[#7367F0]/10 px-2 py-0.5 rounded ml-4">{{ formatCurrency(item.rate) }}</span>
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
                    >Qty</button>
                    <button
                      type="button"
                      class="px-3 py-1 rounded text-xs font-bold transition-all cursor-pointer border-none"
                      :class="form.qty_show === 'Hours' ? 'bg-[#7367F0] text-white shadow-sm' : 'bg-transparent text-[#6F6B7D] hover:text-[#4B465C]'"
                      @click="form.qty_show = 'Hours'"
                    >Hours</button>
                    <button
                      type="button"
                      class="px-3 py-1 rounded text-xs font-bold transition-all cursor-pointer border-none"
                      :class="form.qty_show === 'Qty/Hours' ? 'bg-[#7367F0] text-white shadow-sm' : 'bg-transparent text-[#6F6B7D] hover:text-[#4B465C]'"
                      @click="form.qty_show = 'Qty/Hours'"
                    >Qty/Hours</button>
                  </div>
                </div>
              </div>

              <!-- Items Table -->
              <div class="overflow-x-auto rounded-lg border border-[#EBE9F1] shadow-sm">
                <table class="items-table text-xs w-full">
                  <thead>
                    <tr class="bg-[#F8F7FA] text-[#6F6B7D] border-b border-[#EBE9F1]">
                      <th class="w-8 text-center py-3 px-2">#</th>
                      <th class="w-52 py-3 px-3 text-left font-semibold uppercase text-[11px] tracking-wider">Item</th>
                      <th class="py-3 px-3 text-left font-semibold uppercase text-[11px] tracking-wider">Description</th>
                      <th class="w-24 text-center py-3 px-2 font-semibold uppercase text-[11px] tracking-wider">Qty</th>
                      <th class="w-28 text-right py-3 px-3 font-semibold uppercase text-[11px] tracking-wider">Rate</th>
                      <th class="w-28 text-center py-3 px-2 font-semibold uppercase text-[11px] tracking-wider">Tax</th>
                      <th class="w-32 text-right pr-4 py-3 px-3 font-semibold uppercase text-[11px] tracking-wider">Amount</th>
                      <th class="w-12 text-center py-3 px-2"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Add Item Row -->
                    <tr class="bg-[#F8F7FA]/60 border-b border-[#EBE9F1]">
                      <td class="text-center text-[#7367F0] font-bold text-sm">+</td>
                      <td class="p-2.5">
                        <textarea class="form-ctrl text-xs p-2 min-h-[44px] bg-white border-[#DBDADE] rounded-md resize-none" rows="2" v-model="newItem.description" placeholder="Item description"></textarea>
                      </td>
                      <td class="p-2.5">
                        <textarea class="form-ctrl text-xs p-2 min-h-[44px] bg-white border-[#DBDADE] rounded-md resize-none" rows="2" v-model="newItem.long_description" placeholder="Long description (optional)"></textarea>
                      </td>
                      <td class="p-2.5">
                        <input type="number" class="form-ctrl text-xs text-center h-[34px] bg-white border-[#DBDADE] rounded-md w-full" v-model="newItem.qty" min="0.01" step="0.01" />
                        <input type="text" class="form-ctrl text-[11px] text-center mt-1 placeholder-[#A8AAAE] h-[28px] bg-white border-[#DBDADE] rounded-md font-medium w-full" v-model="newItem.unit" placeholder="Unit" />
                      </td>
                      <td class="p-2.5">
                        <input type="number" class="form-ctrl text-xs text-right h-[34px] bg-white border-[#DBDADE] rounded-md font-semibold w-full" v-model="newItem.rate" placeholder="0.00" />
                      </td>
                      <td class="p-2.5">
                        <div class="relative">
                          <select class="form-ctrl text-xs bg-white h-[34px] border-[#DBDADE] rounded-md appearance-none cursor-pointer pr-7 font-medium w-full" v-model="newItem.tax_rate">
                            <option :value="null">No Tax</option>
                            <option :value="5">5.00%</option>
                            <option :value="10">10.00%</option>
                            <option :value="18">18.00%</option>
                          </select>
                          <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-[#A8AAAE]">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="6 9 12 15 18 9"/></svg>
                          </div>
                        </div>
                      </td>
                      <td class="p-2.5 text-right font-bold text-[#4B465C] pr-4 text-sm">{{ formatCurrency((newItem.qty || 1) * (newItem.rate || 0)) }}</td>
                      <td class="p-2.5 text-center">
                        <button type="button" class="w-8 h-8 bg-[#7367F0] hover:bg-[#685DD8] text-white rounded-md flex items-center justify-center cursor-pointer shadow-sm transition-all mx-auto border-none" @click="addItem" title="Add line item">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg>
                        </button>
                      </td>
                    </tr>

                    <!-- Listed Items -->
                    <tr v-for="(item, idx) in form.items" :key="idx" class="added-item-row group hover:bg-[#F8F7FA] border-b border-[#F1F0F2] transition-colors">
                      <td class="text-center font-semibold text-[#A8AAAE] text-xs p-3">{{ idx + 1 }}</td>
                      <td class="p-3">
                        <div class="font-bold text-[#4B465C] text-sm">{{ item.description }}</div>
                      </td>
                      <td class="p-3 text-[#6F6B7D] leading-relaxed text-xs">
                        {{ item.long_description || '—' }}
                      </td>
                      <td class="p-3 text-center font-semibold text-[#4B465C]">
                        {{ item.qty }} <span class="text-[10px] text-[#A8AAAE] font-normal block mt-0.5">({{ item.unit }})</span>
                      </td>
                      <td class="p-3 text-right font-medium text-[#4B465C]">
                        {{ formatCurrency(item.rate) }}
                      </td>
                      <td class="p-3 text-center">
                        <span v-if="item.tax_rate > 0" class="inline-flex items-center gap-1 bg-[#FF9F43]/10 text-[#FF9F43] border border-[#FF9F43]/20 px-2 py-0.5 rounded text-[11px] font-bold">
                          {{ item.tax_rate }}%
                        </span>
                        <span v-else class="text-[#A8AAAE] text-xs font-medium">None</span>
                      </td>
                      <td class="p-3 text-right font-bold text-[#4B465C] pr-4">
                        {{ formatCurrency(itemAmount(item)) }}
                      </td>
                      <td class="p-3 text-center">
                        <button type="button" class="text-[#A8AAAE] hover:text-rose-500 hover:bg-rose-50 rounded w-7 h-7 flex items-center justify-center border border-transparent hover:border-rose-100 cursor-pointer transition-all mx-auto" @click="removeItem(idx)" title="Remove item">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                      </td>
                    </tr>

                    <tr v-if="!form.items.length">
                      <td colspan="8" class="text-[#A8AAAE] text-xs text-center italic py-6">
                        No credit items added yet. Enter an item above and click '+' or choose a predefined item.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Subtotal / Adjustment / Totals Aligned right -->
              <div class="flex justify-end pt-2">
                <div class="w-80 bg-[#FFFFFF] border border-[#EBE9F1] rounded-lg p-5 space-y-3 shadow-sm">
                  <div class="flex justify-between items-center text-xs pb-2 border-b border-[#F1F0F2]">
                    <span class="text-[#6F6B7D] font-semibold">Sub Total</span>
                    <span class="font-bold text-[#4B465C] text-sm">{{ formatCurrency(subtotal) }}</span>
                  </div>

                  <div v-if="form.discount_type !== 'no_discount'" class="flex justify-between items-center text-xs pb-2 border-b border-[#F1F0F2]">
                    <span class="text-[#6F6B7D] font-semibold">Discount</span>
                    <div class="flex items-center space-x-1.5">
                      <input
                        type="number"
                        min="0"
                        max="100"
                        class="border border-[#DBDADE] rounded text-xs text-right w-14 h-7 pr-1 focus:outline-none focus:border-[#7367F0]"
                        v-model.number="form.discount_percent"
                      />
                      <span class="text-xs font-semibold text-[#6F6B7D] bg-[#F8F7FA] border border-[#DBDADE] rounded px-1.5 py-0.5">%</span>
                    </div>
                    <span class="font-bold text-rose-500">-{{ formatCurrency(discountVal) }}</span>
                  </div>

                  <div class="flex justify-between items-center text-xs pb-2 border-b border-[#F1F0F2]">
                    <span class="text-[#6F6B7D] font-semibold">Adjustment</span>
                    <input
                      type="number"
                      class="border border-[#DBDADE] rounded text-xs text-right w-20 h-7 pr-1 focus:outline-none focus:border-[#7367F0]"
                      v-model.number="form.adjustment"
                    />
                    <span class="font-bold text-[#4B465C]">{{ formatCurrency(form.adjustment || 0) }}</span>
                  </div>

                  <div class="flex justify-between items-center pt-1">
                    <span class="text-xs font-bold text-[#4B465C] uppercase tracking-wider">Total</span>
                    <span class="text-xl text-[#7367F0] font-extrabold tracking-tight">{{ formatCurrency(totalAmount) }}</span>
                  </div>
                </div>
              </div>

              <!-- Notes & Terms -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-[#F1F0F2]">
                <div class="p-4 bg-[#FFFFFF] border border-[#EBE9F1] rounded-lg space-y-2">
                  <div class="flex items-center gap-2 pb-1">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="text-[#7367F0]"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <label class="text-xs font-bold uppercase tracking-wider text-[#4B465C]">Client Note</label>
                  </div>
                  <textarea
                    class="form-ctrl text-xs p-3 bg-white border-[#DBDADE] rounded-md transition-all min-h-[80px] resize-none"
                    rows="3"
                    v-model="form.client_note"
                    placeholder="Write a note visible to recipient..."
                  ></textarea>
                </div>
                <div class="p-4 bg-[#FFFFFF] border border-[#EBE9F1] rounded-lg space-y-2">
                  <div class="flex items-center gap-2 pb-1">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="text-[#7367F0]"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <label class="text-xs font-bold uppercase tracking-wider text-[#4B465C]">Terms &amp; Conditions</label>
                  </div>
                  <textarea
                    class="form-ctrl text-xs p-3 bg-white border-[#DBDADE] rounded-md transition-all min-h-[80px] resize-none text-[#5D596C]"
                    rows="3"
                    v-model="form.terms_conditions"
                    placeholder="Standard terms and conditions..."
                  ></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- ── Stepper Navigation Buttons Footer ── -->
          <div class="flex items-center justify-between pt-8 border-t border-[#F1F0F2] mt-8">
            <button
              type="button"
              class="btn-outline px-5 py-2.5 text-xs font-semibold flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed"
              :disabled="currentStep === 0"
              @click="currentStep--"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
              Previous
            </button>

            <div class="flex items-center space-x-3">
              <button
                v-if="currentStep < numberedSteps.length - 1"
                type="button"
                class="btn-primary px-6 py-2.5 text-xs font-bold flex items-center gap-2"
                @click="currentStep++"
              >
                Next Step
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
              </button>

              <button
                v-if="currentStep === numberedSteps.length - 1"
                type="button"
                class="btn-primary px-7 py-2.5 text-xs font-bold flex items-center gap-2"
                :disabled="saving"
                @click="submitCreditNote"
              >
                <svg v-if="saving" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="animate-spin"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                {{ saving ? 'Saving...' : (isEdit ? 'Save Changes' : 'Submit Credit Note') }}
              </button>
            </div>
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
              <span class="modal-title font-bold text-[#4B465C] text-sm">Billing Address</span>
            </div>
            <button class="modal-close text-[#A8AAAE] hover:text-[#4B465C] font-bold text-xl cursor-pointer bg-transparent border-none" @click="closeAddressModal">×</button>
          </div>

          <div class="modal-body p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs overflow-y-auto">
            <div class="md:col-span-2">
              <label class="block text-xs font-semibold text-[#4B465C] mb-1">Street</label>
              <textarea class="form-ctrl text-xs p-2.5 bg-white border-[#DBDADE] rounded-md resize-none" rows="2" v-model="form.billing_street" placeholder="Street address"></textarea>
            </div>
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1">City</label>
              <input type="text" class="form-ctrl text-xs h-[36px] px-3 bg-white border-[#DBDADE] rounded-md" v-model="form.billing_city" placeholder="City" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1">State</label>
              <input type="text" class="form-ctrl text-xs h-[36px] px-3 bg-white border-[#DBDADE] rounded-md" v-model="form.billing_state" placeholder="State" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1">Zip Code</label>
              <input type="text" class="form-ctrl text-xs h-[36px] px-3 bg-white border-[#DBDADE] rounded-md" v-model="form.billing_zip" placeholder="Zip Code" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1">Country</label>
              <input type="text" class="form-ctrl text-xs h-[36px] px-3 bg-white border-[#DBDADE] rounded-md" v-model="form.billing_country" placeholder="Country" />
            </div>
          </div>

          <div class="modal-foot px-6 py-3.5 border-t border-[#F1F0F2] flex justify-end gap-2.5 bg-[#FFFFFF]">
            <button type="button" class="btn-ghost px-4 py-2 text-xs font-semibold" @click="closeAddressModal">Cancel</button>
            <button type="button" class="btn-primary px-5 py-2 text-xs font-bold" @click="closeAddressModal">Save Address</button>
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

    const currentStep = ref(0);
    const isEdit = ref(false);
    const saving = ref(false);
    const clientOptions = ref([]);
    const selectedClient = ref(null);
    const showAddressModal = ref(false);
    const selectedPredefined = ref('');

    const numberedSteps = [
      {
        title: 'Customer & Address',
        subtitle: 'Select client & billing info',
      },
      {
        title: 'Credit Note Details',
        subtitle: 'Number, dates & status',
      },
      {
        title: 'Items & Totals',
        subtitle: 'Add items & calculate total',
      },
    ];

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
        currentStep.value = 0;
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
      currentStep,
      numberedSteps,
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
.credit-note-stepper-page {
  font-family: 'Public Sans', 'Inter', -apple-system, sans-serif;
  color: #5D596C;
}

/* Universal Rectangular Form Control */
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

/* Datepicker Overrides */
:deep(.vuexy-datepicker.ant-picker),
:deep(.ant-picker) {
  height: 38px !important;
  border-radius: 6px !important;
  border: 1px solid #DBDADE !important;
  background: #FFFFFF !important;
  padding: 4px 12px !important;
  box-shadow: none !important;
  transition: all 0.2s !important;
}

:deep(.ant-picker-focused),
:deep(.ant-picker:hover) {
  border-color: #7367F0 !important;
}

:deep(.ant-picker-focused) {
  box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.16) !important;
}

:deep(.ant-picker input) {
  font-size: 13px !important;
  color: #4B465C !important;
}

/* Items Table */
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

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: rgba(47, 43, 61, 0.45);
  backdrop-filter: blur(2px);
  display: flex; align-items: center; justify-content: center;
  z-index: 1000; padding: 1rem;
}

.modal-card {
  background: #FFFFFF;
  border-radius: 12px;
  width: 100%; max-width: 620px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  display: flex; flex-direction: column;
  max-height: calc(100vh - 2rem); overflow: hidden;
}

.modal-head {
  padding: 18px 24px;
  border-bottom: 1px solid #F1F0F2;
  display: flex; align-items: center; justify-content: space-between;
  background: #FFFFFF;
}

.modal-title { font-size: 14px; font-weight: 700; color: #4B465C; }
.modal-close {
  background: transparent; border: none; font-size: 20px; font-weight: 700;
  color: #A8AAAE; cursor: pointer; line-height: 1;
}
.modal-close:hover { color: #4B465C; }
.modal-body { padding: 24px; overflow-y: auto; }
.modal-foot {
  padding: 16px 24px; border-top: 1px solid #F1F0F2;
  display: flex; justify-content: flex-end; gap: 12px; background: #FFFFFF;
}

/* Fade transition for modal */
.fade-enter-active,
.fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from,
.fade-leave-to { opacity: 0; }

/* Remove spinner arrows from number inputs */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button { opacity: 0.3; }
input[type="number"]:focus::-webkit-inner-spin-button,
input[type="number"]:focus::-webkit-outer-spin-button { opacity: 0.6; }
</style>
