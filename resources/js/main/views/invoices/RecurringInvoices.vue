<template>
  <div class="ri-page">

    <!-- ── Premium Page Header ── -->
    <div class="page-header">
      <div class="header-left">
        <button class="back-btn-premium" @click="$router.push({ name: 'admin.invoices' })">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><polyline points="15 18 9 12 15 6"/></svg>
          <span>Invoices</span>
        </button>
        <div class="header-brand">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#d35400] via-[#7e1e8e] to-[#0b579f]"></div>
          <div class="title-group">
            <h1 class="page-title">Recurring Invoices</h1>
            <span class="page-subtitle">Automatically generated invoice templates</span>
          </div>
        </div>
      </div>
      <button class="btn-create-premium" @click="openCreateDrawer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Recurring Invoice
      </button>
    </div>

    <!-- ── Premium Stats Cards ── -->
    <div class="stats-row">
      <div class="stat-card-premium">
        <div class="stat-icon-wrap stat-total">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div class="stat-body-premium">
          <div class="stat-num-premium">{{ stats.total }}</div>
          <div class="stat-label-premium">Total</div>
        </div>
      </div>
      <div class="stat-card-premium">
        <div class="stat-icon-wrap stat-active-bg">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="stat-body-premium">
          <div class="stat-num-premium text-emerald-600">{{ stats.active }}</div>
          <div class="stat-label-premium">Active</div>
        </div>
      </div>
      <div class="stat-card-premium">
        <div class="stat-icon-wrap stat-paused-bg">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
        </div>
        <div class="stat-body-premium">
          <div class="stat-num-premium text-amber-600">{{ stats.paused }}</div>
          <div class="stat-label-premium">Paused</div>
        </div>
      </div>
      <div class="stat-card-premium">
        <div class="stat-icon-wrap stat-stopped-bg">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
        </div>
        <div class="stat-body-premium">
          <div class="stat-num-premium text-rose-600">{{ stats.stopped }}</div>
          <div class="stat-label-premium">Stopped</div>
        </div>
      </div>
    </div>

    <!-- ── Premium Toolbar ── -->
    <div class="toolbar-premium">
      <div class="filter-tabs-premium">
        <button
          v-for="f in filterOptions"
          :key="f.value"
          class="filter-tab-premium"
          :class="{ 'filter-tab-active': filters.status === f.value }"
          @click="filters.status = f.value; loadData()"
        >{{ f.label }}</button>
      </div>
      <a-input-search
        v-model:value="filters.search"
        placeholder="Search client..."
        size="small"
        style="width:220px"
        @search="loadData"
        allow-clear
      />
    </div>

    <!-- ── Premium Table ── -->
    <div class="table-card-premium">
      <table class="ri-table-premium" v-if="!loading">
        <thead>
          <tr class="table-header-premium">
            <th>Client</th>
            <th>Project</th>
            <th>Frequency</th>
            <th>Amount</th>
            <th>Cycles</th>
            <th>Last Sent</th>
            <th>Next Invoice</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="ri in list" :key="ri.id" class="ri-row-premium">
            <td>
              <div class="client-name-premium">{{ ri.client?.company || '—' }}</div>
            </td>
            <td>
              <span v-if="ri.project" class="project-chip-premium">{{ ri.project.name }}</span>
              <span v-else class="text-slate-300 text-xs font-medium">—</span>
            </td>
            <td>
              <span class="freq-badge-premium" :class="'freq-' + ri.frequency">
                {{ freqLabel(ri.frequency) }}
              </span>
            </td>
            <td class="amount-cell-premium">{{ formatCurrency(ri.total) }}</td>
            <td class="text-slate-400 text-xs font-medium">
              <span v-if="ri.cycles === 0">Unlimited</span>
              <span v-else>{{ ri.cycles_run }} / {{ ri.cycles }}</span>
            </td>
            <td class="text-slate-400 text-xs font-medium">{{ formatDate(ri.last_sent_at) }}</td>
            <td :class="ri.status === 'active' ? 'next-due-premium' : 'text-slate-400 text-xs font-medium'">{{ formatDate(ri.next_invoice_date) }}</td>
            <td>
              <span class="status-badge-premium" :class="'status-' + ri.status">{{ ri.status }}</span>
            </td>
            <td class="action-td-premium" @click.stop>
              <a-dropdown :trigger="['click']" placement="bottomRight">
                <button class="dots-btn-premium">
                  <svg viewBox="0 0 24 24" fill="currentColor" width="15" height="15"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
                </button>
                <template #overlay>
                  <a-menu>
                    <a-menu-item key="edit" @click="editRI(ri)">
                      <div class="flex items-center gap-2 text-xs font-semibold text-slate-700 py-0.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit
                      </div>
                    </a-menu-item>
                    <a-menu-item key="pause" v-if="ri.status === 'active'" @click="setStatus(ri, 'paused')">
                      <div class="flex items-center gap-2 text-xs font-semibold text-amber-600 py-0.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                        Pause
                      </div>
                    </a-menu-item>
                    <a-menu-item key="resume" v-if="ri.status === 'paused'" @click="setStatus(ri, 'active')">
                      <div class="flex items-center gap-2 text-xs font-semibold text-emerald-600 py-0.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        Resume
                      </div>
                    </a-menu-item>
                    <a-menu-item key="stop" v-if="ri.status !== 'stopped'" @click="setStatus(ri, 'stopped')">
                      <div class="flex items-center gap-2 text-xs font-semibold text-rose-600 py-0.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                        Stop
                      </div>
                    </a-menu-item>
                    <a-menu-divider />
                    <a-menu-item key="delete" @click="deleteRI(ri.id)">
                      <div class="flex items-center gap-2 text-xs font-semibold text-rose-500 py-0.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                        Delete
                      </div>
                    </a-menu-item>
                  </a-menu>
                </template>
              </a-dropdown>
            </td>
          </tr>
          <tr v-if="!list.length">
            <td colspan="9">
              <div class="empty-state-premium">
                <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" width="48" height="48"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <p>No recurring invoices found</p>
                <button class="btn-create-premium mt-2" @click="openCreateDrawer">Create one now</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="loading" class="table-loading-premium">
        <a-spin />
        <span>Loading recurring invoices...</span>
      </div>

      <div class="table-footer-premium" v-if="pagination.total > filters.perPage">
        <a-pagination
          v-model:current="pagination.current"
          :pageSize="pagination.pageSize"
          :total="pagination.total"
          size="small"
          :show-size-changer="false"
          @change="(page) => { pagination.current = page; loadData(); }"
        />
      </div>
    </div>

    <!-- ══════════════════════════════════════════════════
         Vuexy Create / Edit Drawer
    ══════════════════════════════════════════════════ -->
    <a-drawer
      v-model:open="showDrawer"
      placement="right"
      :width="1100"
      :destroyOnClose="true"
      :footer-style="{ display: 'none' }"
      class="vuexy-recurring-drawer"
    >
      <template #title>
        <div class="flex items-center space-x-3 py-1">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#7367F0] to-[#9F8ED6]"></div>
          <h2 class="text-base font-bold text-[#4B465C] m-0">
            {{ editingId ? 'Edit Recurring Invoice' : 'New Recurring Invoice' }}
          </h2>
        </div>
      </template>

      <a-form layout="vertical" :model="form" @finish="submitForm" class="space-y-6">

        <!-- ─── Section 01: Customer & Configuration ─── -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-6 space-y-6 shadow-sm">
          <div class="flex items-center justify-between pb-3 border-b border-[#F1F0F2]">
            <div class="flex items-center space-x-2">
              <span class="text-[11px] font-bold text-[#7367F0] bg-[#7367F0]/10 px-2.5 py-0.5 rounded">01</span>
              <span class="text-xs font-bold uppercase tracking-wider text-[#4B465C]">Customer &amp; Configuration</span>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                  <span class="text-rose-500">*</span> Client
                </label>
                <a-select
                  v-model:value="form.client_id"
                  placeholder="Select client..."
                  style="width: 100%"
                  show-search
                  :filter-option="(input, option) => (option.label || '').toLowerCase().includes(input.toLowerCase())"
                  @change="handleClientChange"
                  class="vuexy-select"
                >
                  <a-select-option v-for="c in clients" :key="c.id" :value="c.id" :label="c.company">
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
                    Edit billing &amp; shipping
                  </button>
                </div>
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
                  <div class="border-l border-[#DBDADE] pl-4">
                    <div class="font-bold text-[#4B465C] mb-1 flex items-center gap-1.5">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" class="text-[#7367F0]"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                      Ship to
                    </div>
                    <div class="font-medium text-[#5D596C] space-y-0.5 leading-relaxed">
                      <div>{{ form.shipping_street || '--' }}</div>
                      <div>{{ form.shipping_city || '--' }}{{ form.shipping_state ? ', ' + form.shipping_state : '' }}</div>
                      <div>{{ form.shipping_country || '--' }}{{ form.shipping_zip ? ', ' + form.shipping_zip : '' }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Project</label>
                <a-select
                  v-model:value="form.project_id"
                  placeholder="Select project..."
                  style="width: 100%"
                  allow-clear
                  class="vuexy-select"
                >
                  <a-select-option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</a-select-option>
                </a-select>
              </div>

              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Allowed Payment Modes</label>
                <div class="flex flex-wrap gap-2">
                  <label v-for="mode in ['Bank','Stripe Checkout','PayPal','Cash']" :key="mode"
                    class="payment-chip-vuexy"
                    :class="{ 'payment-chip-active-vuexy': form.allowed_payment_modes_list.includes(mode) }">
                    <input type="checkbox" v-model="form.allowed_payment_modes_list" :value="mode" style="display:none" />
                    <svg v-if="form.allowed_payment_modes_list.includes(mode)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="12" height="12"><polyline points="20 6 9 17 4 12"/></svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><circle cx="12" cy="12" r="10"/></svg>
                    <span>{{ mode }}</span>
                  </label>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Currency</label>
                  <div class="relative">
                    <select class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10" v-model="form.currency">
                      <option value="USD">USD ($)</option>
                      <option value="EUR">EUR (€)</option>
                      <option value="GBP">GBP (£)</option>
                      <option value="CAD">CAD ($)</option>
                      <option value="INR">INR (₹)</option>
                      <option value="AUD">AUD ($)</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Sale Agent</label>
                  <div class="relative">
                    <select class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10" v-model="form.sale_agent">
                      <option :value="null">Select agent...</option>
                      <option v-for="s in staff" :key="s.id" :value="s.name">{{ s.name }}</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                  <span class="text-rose-500">*</span> Frequency
                </label>
                <div class="relative">
                  <select class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 capitalize" v-model="form.frequency">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly (every 3 months)</option>
                    <option value="yearly">Yearly</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                    <span class="text-rose-500">*</span> Start Date
                  </label>
                  <a-date-picker
                    v-model:value="form.date_from"
                    style="width: 100%"
                    value-format="YYYY-MM-DD"
                    format="DD/MM/YYYY"
                    class="vuexy-datepicker"
                    placeholder="Select start date"
                  />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">End Date (optional)</label>
                  <a-date-picker
                    v-model:value="form.date_to"
                    style="width: 100%"
                    value-format="YYYY-MM-DD"
                    format="DD/MM/YYYY"
                    class="vuexy-datepicker"
                    placeholder="Select end date"
                  />
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Cycles (0 = unlimited)</label>
                  <input type="number" class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md font-semibold" v-model.number="form.cycles" :min="0" placeholder="0" />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Discount Type</label>
                  <div class="relative">
                    <select class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10" v-model="form.discount_type" @change="recalc">
                      <option value="no_discount">No Discount</option>
                      <option value="before_tax">Before Tax</option>
                      <option value="after_tax">After Tax</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Tags</label>
                <a-select
                  v-model:value="form.tags"
                  mode="tags"
                  placeholder="Add tags..."
                  style="width: 100%"
                  class="vuexy-tags-select"
                >
                </a-select>
              </div>

              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Admin Note</label>
                <textarea
                  class="form-ctrl text-xs p-3 bg-white border-[#DBDADE] rounded-md transition-all min-h-[64px]"
                  rows="2"
                  v-model="form.admin_note"
                  placeholder="Admin notes (not visible to client)..."
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- ─── Section 02: Invoice Items ─── -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-6 space-y-5 shadow-sm">
          <div class="flex items-center justify-between pb-3 border-b border-[#F1F0F2]">
            <div class="flex items-center space-x-2">
              <span class="text-[11px] font-bold text-[#7367F0] bg-[#7367F0]/10 px-2.5 py-0.5 rounded">02</span>
              <span class="text-xs font-bold uppercase tracking-wider text-[#4B465C]">Invoice Items</span>
            </div>
          </div>

          <div class="flex items-center justify-between flex-wrap gap-4 pb-2">
            <div class="flex items-center space-x-2 bg-[#F8F7FA] p-1 rounded-md border border-[#DBDADE]">
              <span class="text-[11px] font-bold text-[#A8AAAE] px-2.5 uppercase tracking-wider">Show qty as:</span>
              <div class="flex space-x-1">
                <button type="button"
                  class="px-3 py-1 rounded text-xs font-bold transition-all cursor-pointer border-none"
                  :class="form.qty_display_mode === 'qty' ? 'bg-[#7367F0] text-white shadow-sm' : 'bg-transparent text-[#6F6B7D] hover:text-[#4B465C]'"
                  @click="form.qty_display_mode = 'qty'">Qty</button>
                <button type="button"
                  class="px-3 py-1 rounded text-xs font-bold transition-all cursor-pointer border-none"
                  :class="form.qty_display_mode === 'hours' ? 'bg-[#7367F0] text-white shadow-sm' : 'bg-transparent text-[#6F6B7D] hover:text-[#4B465C]'"
                  @click="form.qty_display_mode = 'hours'">Hours</button>
                <button type="button"
                  class="px-3 py-1 rounded text-xs font-bold transition-all cursor-pointer border-none"
                  :class="form.qty_display_mode === 'qty_hours' ? 'bg-[#7367F0] text-white shadow-sm' : 'bg-transparent text-[#6F6B7D] hover:text-[#4B465C]'"
                  @click="form.qty_display_mode = 'qty_hours'">Qty/Hours</button>
              </div>
            </div>
            <div class="flex items-center gap-2 max-w-sm w-full">
              <span class="text-[11px] font-bold text-[#A8AAAE] uppercase tracking-wider whitespace-nowrap">Add predefined item:</span>
              <a-select 
                placeholder="Choose catalog item..." 
                style="width: 100%" 
                @change="addCatalogItem" 
                :value="null" 
                show-search 
                :filter-option="(input, option) => (option.label || '').toLowerCase().includes(input.toLowerCase())"
                class="vuexy-select"
              >
                <a-select-option v-for="item in catalogItems" :key="item.id" :value="item.id" :label="item.name">
                  <div class="flex items-center justify-between w-full">
                    <span class="text-xs font-medium text-[#4B465C]">{{ item.name }}</span>
                    <span class="text-[11px] font-bold text-[#7367F0] bg-[#7367F0]/10 px-2 py-0.5 rounded ml-4">{{ formatCurrency(item.rate) }}</span>
                  </div>
                </a-select-option>
              </a-select>
            </div>
          </div>

          <!-- Items Table -->
          <div class="overflow-x-auto rounded-lg border border-[#EBE9F1] shadow-sm">
            <table class="items-table-ri text-xs w-full">
              <thead>
                <tr class="bg-[#F8F7FA] text-[#6F6B7D] border-b border-[#EBE9F1]">
                  <th class="w-56 py-3 px-3 text-left font-semibold uppercase text-[11px] tracking-wider">Item</th>
                  <th class="w-60 py-3 px-3 text-left font-semibold uppercase text-[11px] tracking-wider">Description</th>
                  <th class="w-20 text-center py-3 px-2 font-semibold uppercase text-[11px] tracking-wider">{{ qtyLabel }}</th>
                  <th class="w-20 text-center py-3 px-2 font-semibold uppercase text-[11px] tracking-wider">Unit</th>
                  <th class="w-28 text-right py-3 px-3 font-semibold uppercase text-[11px] tracking-wider">Rate</th>
                  <th class="w-28 text-center py-3 px-2 font-semibold uppercase text-[11px] tracking-wider">Tax</th>
                  <th class="w-32 text-right pr-4 py-3 px-3 font-semibold uppercase text-[11px] tracking-wider">Amount</th>
                  <th class="w-12 text-center py-3 px-2"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in form.items" :key="index" class="item-row-ri group hover:bg-[#F8F7FA] border-b border-[#F1F0F2] transition-colors">
                  <td class="p-2.5">
                    <input type="text" class="form-ctrl text-xs h-[34px] px-3 bg-white border-[#DBDADE] rounded-md font-semibold" v-model="item.description" placeholder="Item name..." />
                  </td>
                  <td class="p-2.5">
                    <textarea class="form-ctrl text-xs p-2 min-h-[34px] h-[34px] bg-white border-[#DBDADE] rounded-md resize-none" rows="1" v-model="item.long_description" placeholder="Long description..."></textarea>
                  </td>
                  <td class="p-2.5">
                    <input type="number" class="form-ctrl text-xs text-center h-[34px] bg-white border-[#DBDADE] rounded-md" v-model.number="item.qty" :min="0.01" @input="recalc" />
                  </td>
                  <td class="p-2.5">
                    <input type="text" class="form-ctrl text-xs text-center h-[34px] bg-white border-[#DBDADE] rounded-md" v-model="item.unit" placeholder="Unit" />
                  </td>
                  <td class="p-2.5">
                    <input type="number" class="form-ctrl text-xs text-right h-[34px] bg-white border-[#DBDADE] rounded-md font-semibold" v-model.number="item.rate" :min="0" @input="recalc" placeholder="0.00" />
                  </td>
                  <td class="p-2.5">
                    <div class="relative">
                      <select class="form-ctrl text-xs h-[34px] bg-white border-[#DBDADE] rounded-md appearance-none cursor-pointer pr-7 font-medium" v-model="item.tax_rate" @change="recalc">
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
                  <td class="p-2.5 text-right font-bold text-[#4B465C] pr-4 text-sm">{{ formatCurrency((item.qty || 0) * (item.rate || 0)) }}</td>
                  <td class="p-2.5 text-center">
                    <button type="button" class="text-[#A8AAAE] hover:text-rose-500 hover:bg-rose-50 rounded w-7 h-7 flex items-center justify-center border border-transparent hover:border-rose-100 cursor-pointer transition-all mx-auto" @click="removeItem(index)" title="Remove item">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                  </td>
                </tr>
                <tr v-if="!form.items.length">
                  <td colspan="8" class="text-[#A8AAAE] text-xs text-center italic py-8">
                    No items added. Click "Add Item Row" or select a predefined item above.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <button type="button" class="btn-outline text-xs font-semibold py-2 px-4 rounded-md flex items-center gap-1.5 cursor-pointer" @click="addItem">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Item Row
          </button>
        </div>

        <!-- ─── Section 03: Totals & Notes ─── -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-6 space-y-6 shadow-sm">
          <div class="flex items-center justify-between pb-3 border-b border-[#F1F0F2]">
            <div class="flex items-center space-x-2">
              <span class="text-[11px] font-bold text-[#7367F0] bg-[#7367F0]/10 px-2.5 py-0.5 rounded">03</span>
              <span class="text-xs font-bold uppercase tracking-wider text-[#4B465C]">Totals &amp; Notes</span>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Notes Column -->
            <div class="space-y-4">
              <div class="p-4 bg-[#FFFFFF] border border-[#EBE9F1] rounded-lg space-y-2">
                <div class="flex items-center gap-2 pb-1">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="text-[#7367F0]"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  <label class="text-xs font-bold uppercase tracking-wider text-[#4B465C]">Client Note</label>
                </div>
                <textarea class="form-ctrl text-xs p-3 bg-white border-[#DBDADE] rounded-md transition-all min-h-[80px] resize-none" rows="3" v-model="form.client_note" placeholder="Write a note visible to client..."></textarea>
              </div>
              <div class="p-4 bg-[#FFFFFF] border border-[#EBE9F1] rounded-lg space-y-2">
                <div class="flex items-center gap-2 pb-1">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="text-[#7367F0]"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                  <label class="text-xs font-bold uppercase tracking-wider text-[#4B465C]">Terms &amp; Conditions</label>
                </div>
                <textarea class="form-ctrl text-xs p-3 bg-white border-[#DBDADE] rounded-md transition-all min-h-[80px] resize-none text-[#5D596C]" rows="3" v-model="form.terms_conditions" placeholder="Terms and conditions..."></textarea>
              </div>
            </div>

            <!-- Totals Column -->
            <div>
              <div class="bg-[#FFFFFF] border border-[#EBE9F1] rounded-lg p-5 space-y-3.5 shadow-sm">
                <div class="flex justify-between items-center text-xs pb-2.5 border-b border-[#F1F0F2]">
                  <span class="text-[#6F6B7D] font-semibold">Sub Total</span>
                  <span class="font-bold text-[#4B465C] text-sm">{{ formatCurrency(form.subtotal) }}</span>
                </div>

                <div v-if="form.discount_type !== 'no_discount'" class="flex justify-between items-center text-xs pb-2.5 border-b border-[#F1F0F2]">
                  <span class="text-[#6F6B7D] font-semibold">Discount</span>
                  <div class="flex items-center space-x-1.5">
                    <input type="number" :min="0"
                      class="border border-[#DBDADE] rounded text-xs text-right w-14 h-7 pr-1 focus:outline-none focus:border-[#7367F0]"
                      v-model.number="form.discount_value_input" @input="recalc" />
                    <select
                      class="border border-[#DBDADE] rounded text-xs h-7 w-14 focus:outline-none focus:border-[#7367F0] bg-white appearance-none px-1 font-semibold text-[#6F6B7D]"
                      v-model="form.discount_symbol" @change="recalc">
                      <option value="%">%</option>
                      <option value="$">$</option>
                    </select>
                  </div>
                  <span class="font-bold text-rose-500">-{{ formatCurrency(form.discount_val) }}</span>
                </div>

                <div class="flex justify-between items-center text-xs pb-2.5 border-b border-[#F1F0F2]">
                  <span class="text-[#6F6B7D] font-semibold">Adjustment</span>
                  <input type="number"
                    class="border border-[#DBDADE] rounded text-xs text-right w-20 h-7 pr-1 focus:outline-none focus:border-[#7367F0]"
                    v-model.number="form.adjustment" @input="recalc" />
                  <span class="font-bold text-[#4B465C]">{{ formatCurrency(form.adjustment) }}</span>
                </div>

                <div v-if="form.tax > 0" class="flex justify-between items-center text-xs pb-2.5 border-b border-[#F1F0F2]">
                  <span class="text-[#6F6B7D] font-semibold">Tax Total</span>
                  <span class="font-bold text-[#4B465C]">{{ formatCurrency(form.tax) }}</span>
                </div>

                <div class="flex justify-between items-center pt-1.5">
                  <span class="text-xs font-bold text-[#4B465C] uppercase tracking-wider">Total</span>
                  <span class="text-xl text-[#7367F0] font-extrabold tracking-tight">{{ formatCurrency(form.total) }}</span>
                </div>

                <div class="text-right text-[11px] font-semibold text-[#A8AAAE] pt-2 border-t border-[#F1F0F2]">
                  Billed <span class="font-bold text-[#4B465C]">{{ freqLabel(form.frequency) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ─── Drawer Action Footer ─── -->
        <div class="border-t border-[#F1F0F2] bg-[#F8F7FA] px-7 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 text-xs text-[#6F6B7D] rounded-b-[10px] -mx-6 -mb-6 mt-6">
          <div class="flex items-center space-x-2">
            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-[#7367F0]/10 text-[#7367F0]">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm1 14h-2v-4h2zm0-6h-2V7h2z"/></svg>
            </span>
            <span class="text-[#6F6B7D] font-medium">{{ editingId ? 'Update recurring invoice details' : 'Create a new recurring invoice template' }}</span>
          </div>
          <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
            <button type="button" class="btn-outline px-5 py-2 text-xs font-semibold" @click="showDrawer = false">
              <span class="flex items-center gap-1.5">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Cancel
              </span>
            </button>
            <button type="submit" class="btn-primary px-6 py-2 text-xs font-bold" :disabled="saving">
              <span class="flex items-center gap-1.5">
                <svg v-if="saving" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="animate-spin"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                {{ editingId ? 'Save Changes' : 'Create Recurring Invoice' }}
              </span>
            </button>
          </div>
        </div>
      </a-form>
    </a-drawer>

    <!-- ─── Address Edit Modal ─── -->
    <transition name="fade">
      <div v-if="showAddressModal" class="modal-overlay" @click.self="closeAddressModal">
        <div class="modal-card border border-[#EBE9F1] rounded-xl shadow-[0_20px_25px_-5px_rgba(0,0,0,0.1)]">
          <div class="modal-head px-6 py-4 border-b border-[#F1F0F2] flex items-center justify-between bg-[#FFFFFF]">
            <div class="flex items-center space-x-2">
              <div class="w-1.5 h-4 rounded-full bg-[#7367F0]"></div>
              <span class="modal-title font-bold text-[#4B465C] text-sm">Billing &amp; Shipping Address</span>
            </div>
            <button class="modal-close text-[#A8AAAE] hover:text-[#4B465C] font-bold text-xl cursor-pointer bg-transparent border-none" @click="closeAddressModal">×</button>
          </div>
          <div class="modal-body p-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-xs overflow-y-auto">
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
            <div class="space-y-3.5">
              <div class="border-b border-[#F1F0F2] pb-2 flex items-center space-x-2">
                <span class="text-[10px] font-bold text-[#7367F0] bg-[#7367F0]/10 px-2 py-0.5 rounded">SHIPPING</span>
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">Street</label>
                <textarea class="form-ctrl text-xs p-2.5 bg-white rounded-md border-[#DBDADE]" rows="2" v-model="addressForm.shipping_street" placeholder="Street Address"></textarea>
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">City</label>
                <input type="text" class="form-ctrl text-xs h-[36px] px-3 bg-white rounded-md border-[#DBDADE]" v-model="addressForm.shipping_city" placeholder="City" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">State</label>
                <input type="text" class="form-ctrl text-xs h-[36px] px-3 bg-white rounded-md border-[#DBDADE]" v-model="addressForm.shipping_state" placeholder="State" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">Zip Code</label>
                <input type="text" class="form-ctrl text-xs h-[36px] px-3 bg-white rounded-md border-[#DBDADE]" v-model="addressForm.shipping_zip" placeholder="Zip Code" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1">Country</label>
                <input type="text" class="form-ctrl text-xs h-[36px] px-3 bg-white rounded-md border-[#DBDADE]" v-model="addressForm.shipping_country" placeholder="Country" />
              </div>
            </div>
          </div>
          <div class="modal-foot px-6 py-3.5 border-t border-[#F1F0F2] flex justify-end gap-2.5 bg-[#FFFFFF]">
            <button type="button" class="btn-ghost px-4 py-2 text-xs font-semibold" @click="closeAddressModal">Cancel</button>
            <button type="button" class="btn-primary px-5 py-2 text-xs font-bold" @click="saveAddresses">Apply Address</button>
          </div>
        </div>
      </div>
    </transition>

  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { message } from 'ant-design-vue';

const defaultForm = () => ({
  client_id: null,
  project_id: null,
  frequency: 'monthly',
  date_from: null,
  date_to: null,
  cycles: 0,
  discount_type: 'no_discount',
  discount_value_input: 0,
  discount_symbol: '%',
  discount_percent: 0,
  discount_val: 0,
  adjustment: 0,
  currency: 'USD',
  sale_agent: null,
  allowed_payment_modes_list: ['Bank', 'Stripe Checkout'],
  qty_display_mode: 'qty',
  tags: [],
  admin_note: '',
  client_note: '',
  terms_conditions: '',
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
  subtotal: 0,
  tax: 0,
  total: 0,
  items: [
    { description: '', long_description: '', qty: 1, unit: 'Unit', rate: 0, tax_rate: 0 }
  ],
});

export default defineComponent({
  name: 'RecurringInvoicesPage',
  setup() {
    const router = useRouter();
    const loading  = ref(false);
    const saving   = ref(false);
    const list     = ref([]);
    const stats    = reactive({ total: 0, active: 0, paused: 0, stopped: 0 });
    const clients  = ref([]);
    const projects = ref([]);
    const staff    = ref([]);
    const catalogItems = ref([]);

    const showDrawer          = ref(false);
    const showAddressModal    = ref(false);
    const editingId           = ref(null);

    const pagination = reactive({ current: 1, pageSize: 25, total: 0 });
    const filters    = reactive({ status: '', search: '', perPage: 25 });

    const filterOptions = [
      { label: 'All',     value: '' },
      { label: 'Active',  value: 'active' },
      { label: 'Paused',  value: 'paused' },
      { label: 'Stopped', value: 'stopped' },
    ];

    const form = reactive(defaultForm());

    /* ── helpers ── */
    const qtyLabel = computed(() => {
      if (form.qty_display_mode === 'hours')    return 'Hours';
      if (form.qty_display_mode === 'qty_hours') return 'Qty/Hours';
      return 'Qty';
    });

    const recalc = () => {
      let rawSub = form.items.reduce((s, it) => s + (it.qty || 0) * (it.rate || 0), 0);

      let discountAmt = 0;
      if (form.discount_type === 'before_tax') {
        discountAmt = form.discount_symbol === '%'
          ? rawSub * (form.discount_value_input || 0) / 100
          : (form.discount_value_input || 0);
        rawSub -= discountAmt;
      }

      let taxAmt = form.items.reduce((s, it) => {
        let base = (it.qty || 0) * (it.rate || 0);
        if (form.discount_type === 'before_tax' && form.subtotal > 0) {
          base -= base * (discountAmt / (rawSub + discountAmt || 1));
        }
        return s + base * (it.tax_rate || 0) / 100;
      }, 0);

      if (form.discount_type === 'after_tax') {
        const subPlusTax = rawSub + taxAmt;
        discountAmt = form.discount_symbol === '%'
          ? subPlusTax * (form.discount_value_input || 0) / 100
          : (form.discount_value_input || 0);
      }

      form.subtotal      = parseFloat(rawSub.toFixed(2));
      form.tax           = parseFloat(taxAmt.toFixed(2));
      form.discount_val  = parseFloat(discountAmt.toFixed(2));
      form.discount_percent = form.discount_symbol === '%' ? (form.discount_value_input || 0) : 0;
      form.total = parseFloat(
        (rawSub + taxAmt - (form.discount_type === 'after_tax' ? discountAmt : 0) + (form.adjustment || 0)).toFixed(2)
      );
    };

    /* ── item rows ── */
    const addItem = () => {
      form.items.push({ description: '', long_description: '', qty: 1, unit: 'Unit', rate: 0, tax_rate: 0 });
      recalc();
    };

    const removeItem = (idx) => {
      form.items.splice(idx, 1);
      recalc();
    };

    const addCatalogItem = (id) => {
      const item = catalogItems.value.find(c => c.id === id);
      if (!item) return;
      form.items.push({ description: item.name, long_description: item.description || '', qty: 1, unit: 'Unit', rate: parseFloat(item.rate || 0), tax_rate: 0 });
      recalc();
    };

    /* ── data loading ── */
    const loadData = async () => {
      loading.value = true;
      try {
        const res = await axios.get('/api/recurring-invoices', {
          params: { status: filters.status, search: filters.search, per_page: filters.perPage, page: pagination.current }
        });
        list.value          = res.data.recurring_invoices?.data || [];
        pagination.total    = res.data.recurring_invoices?.total || 0;
        pagination.pageSize = filters.perPage;
        Object.assign(stats, res.data.stats || {});
      } catch {
        message.error('Failed to load recurring invoices');
      } finally {
        loading.value = false;
      }
    };

    const loadDropdowns = async () => {
      try {
        const [cRes, pRes, sRes, catRes] = await Promise.all([
          axios.get('/api/clients', { params: { per_page: 500 } }),
          axios.get('/api/projects', { params: { per_page: 500 } }),
          axios.get('/api/staff', { params: { per_page: 200 } }).catch(() => ({ data: { staff: { data: [] } } })),
          axios.get('/api/catalog-items', { params: { per_page: 200 } }).catch(() => ({ data: { items: [] } })),
        ]);
        clients.value      = cRes.data.clients?.data || [];
        projects.value     = pRes.data.projects?.data || pRes.data.data || [];
        staff.value        = sRes.data.staff?.data || [];
        catalogItems.value = catRes.data.items || [];
      } catch {}
    };

    /* ── drawer open ── */
    const openCreateDrawer = () => {
      editingId.value = null;
      Object.assign(form, defaultForm());
      showDrawer.value = true;
    };

    const editRI = async (ri) => {
      editingId.value = ri.id;
      try {
        const res = await axios.get(`/api/recurring-invoices/${ri.id}`);
        const d   = res.data;
        Object.assign(form, {
          client_id:   d.client_id,
          project_id:  d.project_id,
          frequency:   d.frequency,
          date_from:   d.date_from ? String(d.date_from).slice(0, 10) : null,
          date_to:     d.date_to   ? String(d.date_to).slice(0, 10)   : null,
          cycles:      d.cycles || 0,
          discount_type: d.discount_type || 'no_discount',
          discount_value_input: d.discount_percent || 0,
          discount_symbol: '%',
          discount_percent: d.discount_percent || 0,
          discount_val: d.discount_val || 0,
          adjustment:  d.adjustment || 0,
          currency:    d.currency || 'USD',
          sale_agent:  d.sale_agent || null,
          allowed_payment_modes_list: d.allowed_payment_modes ? d.allowed_payment_modes.split(',') : ['Bank', 'Stripe Checkout'],
          qty_display_mode: d.qty_display_mode || 'qty',
          tags:        d.tags ? d.tags.split(',').map(t => t.trim()).filter(Boolean) : [],
          admin_note:  d.admin_note || '',
          client_note: d.client_note || '',
          terms_conditions: d.terms_conditions || '',
          billing_street:  d.billing_street  || '',
          billing_city:    d.billing_city    || '',
          billing_state:   d.billing_state   || '',
          billing_zip:     d.billing_zip     || '',
          billing_country: d.billing_country || '',
          shipping_street:  d.shipping_street  || '',
          shipping_city:    d.shipping_city    || '',
          shipping_state:   d.shipping_state   || '',
          shipping_zip:     d.shipping_zip     || '',
          shipping_country: d.shipping_country || '',
          subtotal: parseFloat(d.subtotal || 0),
          tax:      parseFloat(d.tax || 0),
          total:    parseFloat(d.total || 0),
          items: (d.items || []).map(it => ({
            description: it.description || '',
            long_description: it.long_description || '',
            qty: parseFloat(it.qty || 1),
            unit: it.unit || 'Unit',
            rate: parseFloat(it.rate || 0),
            tax_rate: parseFloat(it.tax_rate || 0),
          })),
        });
        if (!form.items.length) {
          form.items.push({ description: '', long_description: '', qty: 1, unit: 'Unit', rate: 0, tax_rate: 0 });
        }
      } catch {
        message.error('Failed to load recurring invoice details');
        return;
      }
      showDrawer.value = true;
    };

    /* ── submit ── */
    const submitForm = async () => {
      recalc();
      saving.value = true;
      try {
        const payload = {
          client_id:              form.client_id,
          project_id:             form.project_id,
          frequency:              form.frequency,
          date_from:              form.date_from,
          date_to:                form.date_to,
          cycles:                 form.cycles,
          subtotal:               form.subtotal,
          tax:                    form.tax,
          total:                  form.total,
          discount_type:          form.discount_type,
          discount_percent:       form.discount_percent,
          discount_val:           form.discount_val,
          adjustment:             form.adjustment,
          currency:               form.currency,
          sale_agent:             form.sale_agent,
          allowed_payment_modes:  form.allowed_payment_modes_list.join(','),
          qty_display_mode:       form.qty_display_mode,
          tags:                   Array.isArray(form.tags) ? form.tags.join(',') : form.tags,
          admin_note:             form.admin_note,
          client_note:            form.client_note,
          terms_conditions:       form.terms_conditions,
          billing_street:         form.billing_street,
          billing_city:           form.billing_city,
          billing_state:          form.billing_state,
          billing_zip:            form.billing_zip,
          billing_country:        form.billing_country,
          shipping_street:        form.shipping_street,
          shipping_city:          form.shipping_city,
          shipping_state:         form.shipping_state,
          shipping_zip:           form.shipping_zip,
          shipping_country:       form.shipping_country,
          items: form.items,
        };

        if (editingId.value) {
          await axios.put(`/api/recurring-invoices/${editingId.value}`, payload);
          message.success('Recurring invoice updated!');
        } else {
          await axios.post('/api/recurring-invoices', payload);
          message.success('Recurring invoice created!');
        }

        showDrawer.value = false;
        loadData();
      } catch (e) {
        const errs = e.response?.data?.errors;
        message.error(errs ? Object.values(errs).flat().join(', ') : 'Failed to save');
      } finally {
        saving.value = false;
      }
    };

    /* ── address modal ── */
    const addressForm = ref({
      billing_street: '', billing_city: '', billing_state: '', billing_zip: '', billing_country: '',
      shipping_street: '', shipping_city: '', shipping_state: '', shipping_zip: '', shipping_country: ''
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
      message.success('Billing & Shipping addresses updated.');
    };

    /* ── status & delete ── */
    const setStatus = async (ri, status) => {
      try {
        await axios.put(`/api/recurring-invoices/${ri.id}`, { status });
        message.success(`Recurring invoice ${status}`);
        loadData();
      } catch {
        message.error('Failed to update status');
      }
    };

    const deleteRI = async (id) => {
      try {
        await axios.delete(`/api/recurring-invoices/${id}`);
        message.success('Deleted');
        loadData();
      } catch {
        message.error('Failed to delete');
      }
    };

    /* ── formatters ── */
    const freqLabel = (f) => ({
      daily: 'Daily', weekly: 'Weekly', monthly: 'Monthly',
      quarterly: 'Quarterly', yearly: 'Yearly',
    }[f] || f);

    const formatCurrency = (val) => {
      if (val === undefined || val === null) return '$0.00';
      return '$' + parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2 });
    };

    const handleClientChange = (clientId) => {
      const c = clients.value.find(cl => cl.id === clientId);
      if (c) {
        form.billing_street = c.address || '';
        form.billing_city = c.city || '';
        form.billing_state = c.state || '';
        form.billing_zip = c.zip || '';
        form.billing_country = c.country || '';
        
        form.shipping_street = c.address || '';
        form.shipping_city = c.city || '';
        form.shipping_state = c.state || '';
        form.shipping_zip = c.zip || '';
        form.shipping_country = c.country || '';
      }
    };

    onMounted(() => { loadData(); loadDropdowns(); });

    return {
      loading, saving, list, stats, clients, projects, staff, catalogItems,
      pagination, filters, filterOptions, form, showDrawer, showAddressModal, editingId,
      qtyLabel, recalc, addItem, removeItem, addCatalogItem,
      loadData, openCreateDrawer, editRI, submitForm, setStatus, deleteRI,
      freqLabel, formatCurrency, formatDate,
      addressForm, openAddressModal, closeAddressModal, saveAddresses,
      handleClientChange
    };
  }
});
</script>

<style scoped>
/* ── Base ── */
.ri-page {
  font-family: 'Public Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background: #F8F7FA;
  min-height: 100vh;
  padding: 20px 24px;
  box-sizing: border-box;
}

/* ── Universal Form Field Styles ── */
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

/* ── Ant-Design Select & Picker overrides ── */
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

/* ── Datepicker Overrides ── */
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

/* ── Payment Mode Chips ── */
.payment-chip-vuexy {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 600;
  padding: 6px 12px;
  border-radius: 6px;
  border: 1px solid #DBDADE;
  cursor: pointer;
  user-select: none;
  transition: all 0.15s;
  color: #6F6B7D;
  background: #FFFFFF;
}
.payment-chip-vuexy:hover { border-color: #7367F0; color: #7367F0; background: rgba(115, 103, 240, 0.04); }
.payment-chip-active-vuexy { background: rgba(115, 103, 240, 0.1); border-color: #7367F0; color: #7367F0; }

/* ── Page Header ── */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  gap: 12px;
  flex-wrap: wrap;
}
.header-left { display: flex; align-items: center; gap: 14px; }
.header-brand { display: flex; align-items: center; gap: 12px; }
.title-group { display: flex; flex-direction: column; gap: 1px; }
.page-title { font-size: 20px; font-weight: 700; color: #4B465C; margin: 0; line-height: 1.3; }
.page-subtitle { font-size: 12px; color: #A8AAAE; }

.back-btn-premium {
  background: #FFFFFF;
  border: 1px solid #DBDADE;
  border-radius: 6px;
  padding: 7px 14px;
  font-size: 12.5px;
  font-weight: 600;
  color: #6F6B7D;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: inherit;
  transition: all 0.15s;
}
.back-btn-premium:hover { background: #F8F7FA; border-color: #7367F0; color: #7367F0; }

.btn-create-premium {
  background: #7367F0;
  color: #FFFFFF;
  border: none;
  border-radius: 6px;
  padding: 9px 18px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 7px;
  font-family: inherit;
  transition: all 0.2s;
  box-shadow: 0 2px 6px rgba(115, 103, 240, 0.4);
}
.btn-create-premium:hover {
  background: #685DD8;
  transform: translateY(-1px);
}

/* ── Stats Cards ── */
.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 20px;
}
.stat-card-premium {
  background: #FFFFFF;
  border: 1px solid #EBE9F1;
  border-radius: 8px;
  padding: 16px 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 2px 9px rgba(47, 43, 61, 0.06);
  transition: all 0.2s;
}
.stat-card-premium:hover {
  box-shadow: 0 4px 16px rgba(47, 43, 61, 0.09);
  transform: translateY(-1px);
}
.stat-icon-wrap {
  width: 42px; height: 42px;
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.stat-total    { background: rgba(115, 103, 240, 0.12); color: #7367F0; }
.stat-active-bg  { background: rgba(40, 199, 111, 0.12); color: #28C76F; }
.stat-paused-bg  { background: rgba(255, 159, 67, 0.12); color: #FF9F43; }
.stat-stopped-bg { background: rgba(234, 84, 85, 0.12); color: #EA5455; }
.stat-num-premium { font-size: 24px; font-weight: 800; color: #4B465C; line-height: 1; }
.stat-label-premium { font-size: 11.5px; color: #A8AAAE; font-weight: 600; margin-top: 3px; text-transform: uppercase; letter-spacing: 0.04em; }

/* ── Toolbar ── */
.toolbar-premium {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
  gap: 10px;
  flex-wrap: wrap;
}
.filter-tabs-premium {
  display: flex;
  background: #FFFFFF;
  border: 1px solid #DBDADE;
  border-radius: 6px;
  overflow: hidden;
}
.filter-tab-premium {
  background: none;
  border: none;
  padding: 8px 18px;
  font-size: 12.5px;
  font-weight: 600;
  color: #6F6B7D;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s;
  border-right: 1px solid #DBDADE;
}
.filter-tab-premium:last-child { border-right: none; }
.filter-tab-premium:hover  { background: #F8F7FA; color: #4B465C; }
.filter-tab-premium.filter-tab-active {
  background: #7367F0;
  color: #FFFFFF;
}

/* ── Table ── */
.table-card-premium {
  background: #FFFFFF;
  border: 1px solid #EBE9F1;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 9px rgba(47, 43, 61, 0.06);
}
.ri-table-premium {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.ri-table-premium th {
  padding: 12px 18px;
  text-align: left;
  font-size: 11.5px;
  font-weight: 600;
  color: #6F6B7D;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  white-space: nowrap;
  background: #F8F7FA;
  border-bottom: 1px solid #EBE9F1;
}
.ri-table-premium td { padding: 14px 18px; border-bottom: 1px solid #F1F0F2; vertical-align: middle; }
.ri-row-premium:hover { background: #F8F7FA; }
.ri-row-premium:last-child td { border-bottom: none; }

.client-name-premium { font-weight: 600; color: #4B465C; font-size: 13px; }
.amount-cell-premium { font-weight: 700; color: #4B465C; white-space: nowrap; font-size: 13px; }
.next-due-premium { color: #7367F0; font-weight: 600; font-size: 12.5px; }
.action-td-premium { width: 40px; text-align: center; }

.project-chip-premium {
  display: inline-block;
  background: rgba(115, 103, 240, 0.08);
  color: #7367F0;
  border: 1px solid rgba(115, 103, 240, 0.16);
  border-radius: 4px; padding: 2px 8px;
  font-size: 11px; font-weight: 600; white-space: nowrap;
}
.freq-badge-premium {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  text-transform: capitalize;
}
.freq-daily     { background: rgba(115, 103, 240, 0.12); color: #7367F0; }
.freq-weekly    { background: rgba(0, 207, 232, 0.12); color: #00CFE8; }
.freq-monthly   { background: rgba(40, 199, 111, 0.12); color: #28C76F; }
.freq-quarterly { background: rgba(255, 159, 67, 0.12); color: #FF9F43; }
.freq-yearly    { background: rgba(234, 84, 85, 0.12); color: #EA5455; }

.status-badge-premium {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  text-transform: capitalize;
}
.status-active  { background: rgba(40, 199, 111, 0.12); color: #28C76F; }
.status-paused  { background: rgba(255, 159, 67, 0.12); color: #FF9F43; }
.status-stopped { background: rgba(234, 84, 85, 0.12); color: #EA5455; }

.dots-btn-premium {
  background: none;
  border: 1px solid transparent;
  border-radius: 6px;
  padding: 4px 6px;
  cursor: pointer;
  color: #A8AAAE;
  display: flex; align-items: center;
  transition: all 0.12s;
}
.dots-btn-premium:hover { background: #F8F7FA; border-color: #DBDADE; color: #4B465C; }

.empty-state-premium {
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  padding: 60px 20px; gap: 8px;
  color: #A8AAAE; font-size: 13px;
}
.empty-state-premium p { margin: 0; }
.table-loading-premium {
  display: flex; align-items: center; justify-content: center;
  gap: 10px; padding: 60px 0; color: #A8AAAE; font-size: 13px;
}
.table-footer-premium {
  display: flex; justify-content: flex-end;
  padding: 12px 16px; border-top: 1px solid #F1F0F2;
}

/* ── Drawer Styling ── */
:deep(.vuexy-recurring-drawer .ant-drawer-header) {
  padding: 18px 24px !important;
  border-bottom: 1px solid #F1F0F2 !important;
  background: #FFFFFF !important;
}
:deep(.vuexy-recurring-drawer .ant-drawer-title) {
  font-size: 0 !important;
}
:deep(.vuexy-recurring-drawer .ant-drawer-body) {
  padding: 24px !important;
  background: #F8F7FA !important;
}

/* ── Items Table ── */
.items-table-ri {
  width: 100%;
  border-collapse: collapse;
}
.items-table-ri th {
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
.items-table-ri td {
  border-bottom: 1px solid #F1F0F2;
  vertical-align: middle;
  padding: 8px 10px;
}
.items-table-ri tbody tr:last-child td {
  border-bottom: none;
}

/* ── Modal ── */
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
  width: 100%; max-width: 680px;
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

/* ── Fade transition for modal ── */
.fade-enter-active,
.fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from,
.fade-leave-to { opacity: 0; }

/* ── Remove spinner arrows from number inputs ── */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button { opacity: 0.3; }
input[type="number"]:focus::-webkit-inner-spin-button,
input[type="number"]:focus::-webkit-outer-spin-button { opacity: 0.6; }

/* ── Responsive ── */
@media (max-width: 900px) {
  .stats-row { grid-template-columns: repeat(2, 1fr); }
}
</style>
