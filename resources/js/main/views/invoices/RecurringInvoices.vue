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
         Premium Create / Edit Drawer
    ══════════════════════════════════════════════════ -->
    <a-drawer
      v-model:open="showDrawer"
      placement="right"
      :width="1100"
      :destroyOnClose="true"
      :footer-style="{ display: 'none' }"
      class="premium-drawer"
    >
      <template #title>
        <div class="drawer-title-premium">
          <div class="w-2 h-5 rounded-full bg-gradient-to-b from-[#d35400] via-[#7e1e8e] to-[#0b579f]"></div>
          <span>{{ editingId ? 'Edit Recurring Invoice' : 'New Recurring Invoice' }}</span>
        </div>
      </template>

      <a-form layout="vertical" :model="form" @finish="submitForm" class="premium-drawer-form">

        <!-- ─── Section 01: Customer & Configuration ─── -->
        <div class="premium-section-card">
          <div class="flex items-center space-x-2 pb-4 border-b border-slate-100 mb-6">
            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">01</span>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Customer &amp; Configuration</span>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column -->
            <div class="space-y-5">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                  <span class="text-rose-500">*</span> Client
                </label>
                <a-select
                  v-model:value="form.client_id"
                  placeholder="Select client..."
                  style="width:100%"
                  show-search
                  :filter-option="(input, option) => option.label.toLowerCase().includes(input.toLowerCase())"
                  size="large"
                  class="premium-client-select"
                >
                  <a-select-option v-for="c in clients" :key="c.id" :value="c.id" :label="c.company">
                    <div class="flex items-center gap-2">
                      <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-[10px] font-bold flex-shrink-0">
                        {{ c.company?.charAt(0) }}
                      </div>
                      <div class="text-xs font-semibold text-slate-800">{{ c.company }}</div>
                    </div>
                  </a-select-option>
                </a-select>
              </div>

              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Address Details</label>
                  <button
                    type="button"
                    class="text-indigo-600 hover:text-indigo-800 cursor-pointer flex items-center gap-1 text-[10.5px] font-bold transition-colors bg-transparent border-none"
                    @click="openAddressModal"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="11" height="11"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    Edit billing &amp; shipping
                  </button>
                </div>
                <div class="grid grid-cols-2 gap-4 text-xs text-slate-500 py-3 px-4 bg-slate-50/70 rounded-xl border border-slate-100 shadow-inner">
                  <div>
                    <div class="font-bold text-slate-700 mb-1 flex items-center gap-1">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="10" height="10" class="text-indigo-600"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                      Bill To
                    </div>
                    <div class="font-medium text-slate-600 space-y-0.5 leading-relaxed">
                      <div>{{ form.billing_street || '--' }}</div>
                      <div>{{ form.billing_city || '--' }}{{ form.billing_state ? ', ' + form.billing_state : '' }}</div>
                      <div>{{ form.billing_country || '--' }}{{ form.billing_zip ? ', ' + form.billing_zip : '' }}</div>
                    </div>
                  </div>
                  <div class="border-l border-slate-200/60 pl-4">
                    <div class="font-bold text-slate-700 mb-1 flex items-center gap-1">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="10" height="10" class="text-indigo-600"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
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

              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Project</label>
                <a-select
                  v-model:value="form.project_id"
                  placeholder="Select project..."
                  style="width:100%"
                  allow-clear
                  size="large"
                  class="premium-client-select"
                >
                  <a-select-option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</a-select-option>
                </a-select>
              </div>

              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Allowed Payment Modes</label>
                <div class="flex flex-wrap gap-2">
                  <label v-for="mode in ['Bank','Stripe Checkout','PayPal','Cash']" :key="mode"
                    class="payment-chip-premium"
                    :class="{ 'payment-chip-active-premium': form.allowed_payment_modes_list.includes(mode) }">
                    <input type="checkbox" v-model="form.allowed_payment_modes_list" :value="mode" style="display:none" />
                    <svg v-if="form.allowed_payment_modes_list.includes(mode)" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="10" height="10"><polyline points="20 6 9 17 4 12"/></svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="10" height="10"><circle cx="12" cy="12" r="10"/></svg>
                    <span>{{ mode }}</span>
                  </label>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Currency</label>
                  <div class="relative">
                    <select class="premium-native-select" v-model="form.currency">
                      <option value="USD">USD $</option>
                      <option value="EUR">EUR €</option>
                      <option value="GBP">GBP £</option>
                      <option value="CAD">CAD $</option>
                      <option value="INR">INR ₹</option>
                      <option value="AUD">AUD $</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                  </div>
                </div>
                <div>
                  <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Sale Agent</label>
                  <div class="relative">
                    <select class="premium-native-select" v-model="form.sale_agent">
                      <option :value="null">Select agent...</option>
                      <option v-for="s in staff" :key="s.id" :value="s.name">{{ s.name }}</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-5">
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                  <span class="text-rose-500">*</span> Frequency
                </label>
                <div class="relative">
                  <select class="premium-native-select" v-model="form.frequency">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly (every 3 months)</option>
                    <option value="yearly">Yearly</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                    <span class="text-rose-500">*</span> Start Date
                  </label>
                  <a-date-picker
                    v-model:value="form.date_from"
                    style="width:100%"
                    value-format="YYYY-MM-DD"
                    format="DD/MM/YYYY"
                    size="large"
                    class="premium-datepicker-ri"
                    placeholder="Select start date"
                  />
                </div>
                <div>
                  <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">End Date (optional)</label>
                  <a-date-picker
                    v-model:value="form.date_to"
                    style="width:100%"
                    value-format="YYYY-MM-DD"
                    format="DD/MM/YYYY"
                    size="large"
                    class="premium-datepicker-ri"
                    placeholder="Select end date"
                  />
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Cycles (0 = unlimited)</label>
                  <input type="number" class="premium-native-input" v-model.number="form.cycles" :min="0" placeholder="0" />
                </div>
                <div>
                  <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Discount Type</label>
                  <div class="relative">
                    <select class="premium-native-select" v-model="form.discount_type" @change="recalc">
                      <option value="no_discount">No Discount</option>
                      <option value="before_tax">Before Tax</option>
                      <option value="after_tax">After Tax</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Tags</label>
                <a-select
                  v-model:value="form.tags"
                  mode="tags"
                  placeholder="Add tags..."
                  style="width: 100%"
                  size="large"
                  class="premium-tags-select-ri"
                >
                </a-select>
              </div>

              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Admin Note</label>
                <textarea
                  class="premium-native-textarea"
                  rows="3"
                  v-model="form.admin_note"
                  placeholder="Admin notes (not visible to client)..."
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- ─── Section 02: Invoice Items ─── -->
        <div class="premium-section-card">
          <div class="flex items-center space-x-2 pb-4 border-b border-slate-100 mb-6">
            <span class="text-[10px] font-bold text-sky-600 bg-sky-50 px-2 py-0.5 rounded">02</span>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Invoice Items</span>
          </div>

          <div class="flex items-center justify-between flex-wrap gap-4 pb-4 border-b border-slate-100 mb-4">
            <div class="flex items-center space-x-3 bg-slate-100/80 p-1 rounded-xl shadow-inner">
              <span class="text-[10px] font-bold text-slate-400 px-2.5 uppercase tracking-wider">Show qty as:</span>
              <div class="flex space-x-1">
                <button type="button"
                  class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                  :class="form.qty_display_mode === 'qty' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                  @click="form.qty_display_mode = 'qty'">Qty</button>
                <button type="button"
                  class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                  :class="form.qty_display_mode === 'hours' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                  @click="form.qty_display_mode = 'hours'">Hours</button>
                <button type="button"
                  class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
                  :class="form.qty_display_mode === 'qty_hours' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                  @click="form.qty_display_mode = 'qty_hours'">Qty/Hours</button>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Add catalog item:</span>
              <a-select placeholder="Select item..." style="width:200px" @change="addCatalogItem" :value="null" show-search :filter-option="(input, option) => option.label.toLowerCase().includes(input.toLowerCase())"
                size="large" class="premium-catalog-select">
                <a-select-option v-for="item in catalogItems" :key="item.id" :value="item.id" :label="item.name">
                  <div class="flex items-center justify-between w-full">
                    <span class="text-xs font-medium">{{ item.name }}</span>
                    <span class="text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md ml-4">{{ formatCurrency(item.rate) }}</span>
                  </div>
                </a-select-option>
              </a-select>
            </div>
          </div>

          <!-- Premium Items Table -->
          <div class="overflow-x-auto rounded-xl border border-slate-100 shadow-sm">
            <table class="items-table-ri text-xs">
              <thead>
                <tr class="bg-gradient-to-r from-slate-50 to-white">
                  <th class="w-56">Item</th>
                  <th class="w-60">Description</th>
                  <th class="w-20 text-center">{{ qtyLabel }}</th>
                  <th class="w-16 text-center">Unit</th>
                  <th class="w-28 text-right">Rate</th>
                  <th class="w-28 text-center">Tax</th>
                  <th class="w-32 text-right pr-5">Amount</th>
                  <th class="w-12 text-center"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in form.items" :key="index" class="item-row-ri group hover:bg-gradient-to-r hover:from-slate-50 hover:to-white transition-all duration-150">
                  <td class="p-2.5">
                    <input type="text" class="item-input-ri" v-model="item.description" placeholder="Item name..." />
                  </td>
                  <td class="p-2.5">
                    <textarea class="item-input-ri min-h-[32px] py-1.5 resize-none" rows="1" v-model="item.long_description" placeholder="Long description..."></textarea>
                  </td>
                  <td class="p-2.5">
                    <input type="number" class="item-input-ri text-center" v-model.number="item.qty" :min="0.01" @input="recalc" />
                  </td>
                  <td class="p-2.5">
                    <input type="text" class="item-input-ri text-center" v-model="item.unit" placeholder="Unit" />
                  </td>
                  <td class="p-2.5">
                    <input type="number" class="item-input-ri text-right font-semibold" v-model.number="item.rate" :min="0" @input="recalc" placeholder="0.00" />
                  </td>
                  <td class="p-2.5">
                    <div class="relative">
                      <select class="item-input-ri appearance-none cursor-pointer pr-7 font-medium" v-model="item.tax_rate" @change="recalc">
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
                  <td class="p-2.5 text-right font-bold text-slate-800 pr-4 text-sm">{{ formatCurrency((item.qty || 0) * (item.rate || 0)) }}</td>
                  <td class="p-2.5 text-center">
                    <button type="button" class="text-slate-300 hover:text-rose-500 hover:bg-rose-50 rounded-lg w-7 h-7 flex items-center justify-center border border-transparent hover:border-rose-100 cursor-pointer transition-all mx-auto opacity-0 group-hover:opacity-100" @click="removeItem(index)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                  </td>
                </tr>
                <tr v-if="!form.items.length">
                  <td colspan="8" class="text-slate-400 text-xs text-center italic py-8">
                    No items added. Click "Add Item Row" or select a catalog item above.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <button type="button" class="inline-flex items-center gap-2 px-5 py-2.5 border-2 border-dashed border-slate-200 rounded-xl text-xs font-bold text-slate-500 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50/30 cursor-pointer transition-all mt-3" @click="addItem">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Item Row
          </button>
        </div>

        <!-- ─── Section 03: Totals & Notes ─── -->
        <div class="premium-section-card">
          <div class="flex items-center space-x-2 pb-4 border-b border-slate-100 mb-6">
            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">03</span>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Totals &amp; Notes</span>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Notes Column -->
            <div class="space-y-4">
              <div class="p-5 bg-gradient-to-br from-slate-50/50 to-white border border-slate-100 rounded-xl space-y-2">
                <div class="flex items-center gap-2 pb-1">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" class="text-indigo-500"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Client Note</label>
                </div>
                <textarea class="premium-note-textarea" rows="3" v-model="form.client_note" placeholder="Visible to client..."></textarea>
              </div>
              <div class="p-5 bg-gradient-to-br from-slate-50/50 to-white border border-slate-100 rounded-xl space-y-2">
                <div class="flex items-center gap-2 pb-1">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" class="text-indigo-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                  <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Terms &amp; Conditions</label>
                </div>
                <textarea class="premium-note-textarea" rows="4" v-model="form.terms_conditions" placeholder="Terms and conditions..."></textarea>
              </div>
            </div>

            <!-- Totals Column -->
            <div>
              <div class="bg-gradient-to-br from-slate-50 to-white border border-slate-100 rounded-2xl p-5 space-y-3.5 shadow-sm">
                <div class="flex justify-between items-center text-xs pb-2.5 border-b border-slate-200/50">
                  <span class="text-slate-500 font-semibold">Sub Total</span>
                  <span class="font-bold text-slate-800 text-sm">{{ formatCurrency(form.subtotal) }}</span>
                </div>

                <div v-if="form.discount_type !== 'no_discount'" class="flex justify-between items-center text-xs pb-2.5 border-b border-slate-200/50">
                  <span class="text-slate-500 font-semibold">Discount</span>
                  <div class="flex items-center space-x-1.5">
                    <input type="number" :min="0"
                      class="border border-slate-200 rounded-md text-xs text-right w-14 h-7 pr-1 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                      v-model.number="form.discount_value_input" @input="recalc" />
                    <select
                      class="border border-slate-200 rounded-md text-xs h-7 w-14 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white appearance-none px-1 font-semibold text-slate-500"
                      v-model="form.discount_symbol" @change="recalc">
                      <option value="%">%</option>
                      <option value="$">$</option>
                    </select>
                  </div>
                  <span class="font-bold text-rose-500">-{{ formatCurrency(form.discount_val) }}</span>
                </div>

                <div class="flex justify-between items-center text-xs pb-2.5 border-b border-slate-200/50">
                  <span class="text-slate-500 font-semibold">Adjustment</span>
                  <input type="number"
                    class="border border-slate-200 rounded-md text-xs text-right w-20 h-7 pr-1 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                    v-model.number="form.adjustment" @input="recalc" />
                  <span class="font-bold text-slate-800">{{ formatCurrency(form.adjustment) }}</span>
                </div>

                <div v-if="form.tax > 0" class="flex justify-between items-center text-xs pb-2.5 border-b border-slate-200/50">
                  <span class="text-slate-500 font-semibold">Tax</span>
                  <span class="font-bold text-amber-600">{{ formatCurrency(form.tax) }}</span>
                </div>

                <div class="flex justify-between items-center pt-1.5">
                  <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Total</span>
                  <span class="text-xl text-slate-900 font-extrabold tracking-tight">{{ formatCurrency(form.total) }}</span>
                </div>

                <div class="text-right text-[11px] font-semibold text-slate-400 pt-1 border-t border-slate-100">
                  Billed <span class="font-bold text-slate-600">{{ freqLabel(form.frequency) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ─── Premium Footer ─── -->
        <div class="premium-drawer-footer">
          <div class="flex items-center space-x-2">
            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm1 14h-2v-4h2zm0-6h-2V7h2z"/></svg>
            </span>
            <span class="text-slate-500 font-medium text-xs">{{ editingId ? 'Update recurring invoice details' : 'Create a new recurring invoice template' }}</span>
          </div>
          <div class="flex items-center space-x-3">
            <button type="button" class="px-5 py-2.5 border border-slate-200 rounded-xl text-xs font-bold bg-white text-slate-600 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-800 cursor-pointer transition-all hover:-translate-y-0.5 active:translate-y-0 shadow-sm" @click="showDrawer = false">
              <span class="flex items-center gap-2">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Cancel
              </span>
            </button>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-br from-indigo-600 to-purple-700 hover:from-indigo-700 hover:to-purple-800 text-white rounded-xl text-xs font-bold cursor-pointer transition-all hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 shadow-md disabled:opacity-50 disabled:cursor-not-allowed" :disabled="saving">
              <span class="flex items-center gap-2">
                <svg v-if="saving" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="animate-spin"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
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
        <div class="modal-card border border-slate-100 rounded-2xl shadow-[0_20px_25px_-5px_rgba(0,0,0,0.08)]">
          <div class="modal-head px-6 py-4.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
            <div class="flex items-center space-x-2">
              <div class="w-1.5 h-4 rounded-full bg-indigo-600"></div>
              <span class="modal-title font-bold text-slate-800 text-sm">Billing & Shipping Address</span>
            </div>
            <button class="modal-close text-slate-400 hover:text-slate-600 font-bold text-xl cursor-pointer" @click="closeAddressModal">×</button>
          </div>
          <div class="modal-body p-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-xs overflow-y-auto">
            <div class="space-y-4">
              <div class="border-b border-slate-100 pb-2 flex items-center space-x-2">
                <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">BILLING</span>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Street</label>
                <textarea class="premium-modal-input" rows="2" v-model="addressForm.billing_street" placeholder="Street Address"></textarea>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">City</label>
                <input type="text" class="premium-modal-input h-9" v-model="addressForm.billing_city" placeholder="City" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">State</label>
                <input type="text" class="premium-modal-input h-9" v-model="addressForm.billing_state" placeholder="State" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Zip Code</label>
                <input type="text" class="premium-modal-input h-9" v-model="addressForm.billing_zip" placeholder="Zip Code" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Country</label>
                <input type="text" class="premium-modal-input h-9" v-model="addressForm.billing_country" placeholder="Country" />
              </div>
            </div>
            <div class="space-y-4">
              <div class="border-b border-slate-100 pb-1.5 flex items-center space-x-2">
                <span class="text-[10px] font-bold text-violet-600 bg-violet-50 px-2 py-0.5 rounded">SHIPPING</span>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Street</label>
                <textarea class="premium-modal-input" rows="2" v-model="addressForm.shipping_street" placeholder="Street Address"></textarea>
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">City</label>
                <input type="text" class="premium-modal-input h-9" v-model="addressForm.shipping_city" placeholder="City" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">State</label>
                <input type="text" class="premium-modal-input h-9" v-model="addressForm.shipping_state" placeholder="State" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Zip Code</label>
                <input type="text" class="premium-modal-input h-9" v-model="addressForm.shipping_zip" placeholder="Zip Code" />
              </div>
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Country</label>
                <input type="text" class="premium-modal-input h-9" v-model="addressForm.shipping_country" placeholder="Country" />
              </div>
            </div>
          </div>
          <div class="modal-foot px-6 py-4.5 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/80">
            <button type="button" class="premium-btn-outline" @click="closeAddressModal">Cancel</button>
            <button type="button" class="premium-btn-primary" @click="saveAddresses">Apply Address</button>
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

    const formatDate = (d) => {
      if (!d) return '—';
      return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    };

    onMounted(() => { loadData(); loadDropdowns(); });

    return {
      loading, saving, list, stats, clients, projects, staff, catalogItems,
      pagination, filters, filterOptions, form, showDrawer, showAddressModal, editingId,
      qtyLabel, recalc, addItem, removeItem, addCatalogItem,
      loadData, openCreateDrawer, editRI, submitForm, setStatus, deleteRI,
      freqLabel, formatCurrency, formatDate,
      addressForm, openAddressModal, closeAddressModal, saveAddresses,
    };
  }
});
</script>

<style scoped>
/* ── Base ── */
.ri-page {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background: #f8fafc;
  min-height: 100vh;
  padding: 20px 24px;
  box-sizing: border-box;
}

/* ── Premium Page Header ── */
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
.page-title { font-size: 20px; font-weight: 700; color: #1e293b; margin: 0 0 0; line-height: 1.3; }
.page-subtitle { font-size: 12px; color: #94a3b8; }

.back-btn-premium {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 7px 14px;
  font-size: 12.5px;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: inherit;
  transition: all 0.15s;
  white-space: nowrap;
}
.back-btn-premium:hover { background: #f8fafc; border-color: #cbd5e1; color: #334155; }

.btn-create-premium {
  background: linear-gradient(135deg, #1e293b, #0f172a);
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 10px 18px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 7px;
  font-family: inherit;
  transition: all 0.2s;
  white-space: nowrap;
  box-shadow: 0 2px 8px rgba(15, 23, 42, 0.15);
}
.btn-create-premium:hover {
  background: linear-gradient(135deg, #0f172a, #000);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
}

/* ── Premium Stats Cards ── */
.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  margin-bottom: 16px;
}
.stat-card-premium {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 16px 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
  transition: all 0.2s;
}
.stat-card-premium:hover {
  border-color: #cbd5e1;
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
  transform: translateY(-1px);
}
.stat-icon-wrap {
  width: 42px; height: 42px;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.stat-total    { background: #f1f5f9; color: #64748b; }
.stat-active-bg  { background: #dcfce7; color: #16a34a; }
.stat-paused-bg  { background: #fef3c7; color: #d97706; }
.stat-stopped-bg { background: #fee2e2; color: #dc2626; }
.stat-body-premium {}
.stat-num-premium { font-size: 24px; font-weight: 800; color: #1e293b; line-height: 1; }
.stat-label-premium { font-size: 11.5px; color: #94a3b8; font-weight: 600; margin-top: 3px; text-transform: uppercase; letter-spacing: 0.04em; }

/* ── Premium Toolbar ── */
.toolbar-premium {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
  gap: 10px;
  flex-wrap: wrap;
}
.filter-tabs-premium {
  display: flex;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.filter-tab-premium {
  background: none;
  border: none;
  padding: 8px 18px;
  font-size: 12.5px;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s;
  border-right: 1px solid #e2e8f0;
}
.filter-tab-premium:last-child { border-right: none; }
.filter-tab-premium:hover  { background: #f8fafc; color: #334155; }
.filter-tab-premium.filter-tab-active {
  background: #1e293b;
  color: #fff;
}

/* ── Premium Table ── */
.table-card-premium {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.ri-table-premium {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.ri-table-premium th {
  padding: 12px 16px;
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
  border-bottom: 1px solid #e2e8f0;
}
.table-header-premium {
  background: linear-gradient(to right, #f8fafc, #fff);
}
.ri-table-premium td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.ri-row-premium { cursor: default; transition: all 0.15s; }
.ri-row-premium:hover { background: linear-gradient(to right, #f8fafc, #fff); }
.ri-row-premium:last-child td { border-bottom: none; }

.client-name-premium { font-weight: 600; color: #1e293b; font-size: 13px; }
.amount-cell-premium { font-weight: 700; color: #1e293b; white-space: nowrap; font-size: 13px; }
.next-due-premium { color: #3b82f6; font-weight: 600; font-size: 12.5px; }
.action-td-premium { width: 40px; text-align: center; }

.project-chip-premium {
  display: inline-block;
  background: #eff6ff; color: #3b82f6;
  border: 1px solid #bfdbfe;
  border-radius: 6px; padding: 2px 8px;
  font-size: 11px; font-weight: 600; white-space: nowrap;
}
.freq-badge-premium {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
  text-transform: capitalize;
  letter-spacing: 0.02em;
}
.freq-daily     { background: #fdf4ff; color: #9333ea; }
.freq-weekly    { background: #eff6ff; color: #3b82f6; }
.freq-monthly   { background: #f0fdf4; color: #16a34a; }
.freq-quarterly { background: #fef3c7; color: #d97706; }
.freq-yearly    { background: #ffedd5; color: #ea580c; }

.status-badge-premium {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  text-transform: capitalize;
}
.status-active  { background: #dcfce7; color: #16a34a; }
.status-paused  { background: #fef3c7; color: #d97706; }
.status-stopped { background: #fee2e2; color: #dc2626; }

.dots-btn-premium {
  background: none;
  border: 1px solid transparent;
  border-radius: 6px;
  padding: 4px 6px;
  cursor: pointer;
  color: #94a3b8;
  display: flex; align-items: center;
  transition: all 0.12s;
}
.dots-btn-premium:hover { background: #f1f5f9; border-color: #e2e8f0; color: #475569; }

.empty-state-premium {
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  padding: 60px 20px; gap: 8px;
  color: #94a3b8; font-size: 13px;
}
.empty-state-premium p { margin: 0; }
.table-loading-premium {
  display: flex; align-items: center; justify-content: center;
  gap: 10px; padding: 60px 0; color: #94a3b8; font-size: 13px;
}
.table-footer-premium {
  display: flex; justify-content: flex-end;
  padding: 12px 16px; border-top: 1px solid #f1f5f9;
}

/* ── Premium Drawer Styling ── */
:deep(.premium-drawer .ant-drawer-header) {
  padding: 18px 24px !important;
  border-bottom: 1px solid #f1f5f9 !important;
  background: linear-gradient(to right, #f8fafc, #fff) !important;
}
:deep(.premium-drawer .ant-drawer-title) {
  font-size: 0 !important;
}
:deep(.premium-drawer .ant-drawer-body) {
  padding: 24px !important;
  background: #f8fafc !important;
}
.drawer-title-premium {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 15px;
  font-weight: 700;
  color: #1e293b;
}

/* ── Premium Section Card ── */
.premium-section-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 20px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.03);
}

/* ── Premium Native Selects ── */
.premium-native-select {
  width: 100%;
  height: 40px;
  padding: 0 32px 0 14px;
  font-size: 12.5px;
  color: #334155;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  outline: none;
  font-family: inherit;
  transition: all 0.2s;
  appearance: none;
  cursor: pointer;
  font-weight: 500;
}
.premium-native-select:hover { border-color: #cbd5e1; background: #f8fafc; }
.premium-native-select:focus { border-color: #6366f1; background: #fff; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12); }

.premium-native-input {
  width: 100%;
  height: 40px;
  padding: 0 14px;
  font-size: 12.5px;
  color: #334155;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  outline: none;
  font-family: inherit;
  transition: all 0.2s;
  box-sizing: border-box;
  font-weight: 500;
}
.premium-native-input:hover { border-color: #cbd5e1; }
.premium-native-input:focus { border-color: #6366f1; background: #fff; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12); }

.premium-native-textarea {
  width: 100%;
  padding: 10px 14px;
  font-size: 12.5px;
  color: #334155;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  outline: none;
  font-family: inherit;
  transition: all 0.2s;
  box-sizing: border-box;
  resize: vertical;
}
.premium-native-textarea:hover { border-color: #cbd5e1; }
.premium-native-textarea:focus { border-color: #6366f1; background: #fff; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12); }
.premium-native-textarea::placeholder { color: #94a3b8; }

/* ── Payment Mode Chips ── */
.payment-chip-premium {
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
.payment-chip-premium:hover { border-color: #cbd5e1; background: #f8fafc; }
.payment-chip-active-premium { background: #eef2ff; border-color: #a5b4fc; color: #4f46e5; }

/* ── Premium Client Select Overrides ── */
:deep(.premium-client-select .ant-select-selector) {
  height: 44px !important;
  border-radius: 10px !important;
  padding: 4px 12px !important;
  border-color: #e2e8f0 !important;
  background: #f8fafc !important;
}
:deep(.premium-client-select:hover .ant-select-selector) {
  border-color: #cbd5e1 !important;
  background: #f8fafc !important;
}
:deep(.premium-client-select.ant-select-focused .ant-select-selector) {
  border-color: #6366f1 !important;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
  background: #fff !important;
}

/* ── Premium Datepicker Overrides ── */
:deep(.premium-datepicker-ri .ant-picker) {
  height: 40px;
  border-radius: 10px;
  border-color: #e2e8f0;
  background: #f8fafc;
  padding: 4px 12px;
  transition: all 0.2s;
}
:deep(.premium-datepicker-ri .ant-picker:hover) {
  border-color: #cbd5e1;
  background: #f8fafc;
}
:deep(.premium-datepicker-ri .ant-picker-focused) {
  border-color: #6366f1 !important;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
  background: #fff !important;
}
:deep(.premium-datepicker-ri .ant-picker input) {
  font-size: 13px;
  color: #334155;
}

/* ── Premium Tags Select Overrides ── */
:deep(.premium-tags-select-ri .ant-select-selector) {
  min-height: 40px !important;
  border-radius: 10px !important;
  border-color: #e2e8f0 !important;
  background: #f8fafc !important;
  padding: 2px 8px !important;
}
:deep(.premium-tags-select-ri.ant-select-focused .ant-select-selector) {
  border-color: #6366f1 !important;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
  background: #fff !important;
}
:deep(.premium-tags-select-ri .ant-select-selection-placeholder) {
  font-size: 12px;
  color: #94a3b8;
}
:deep(.premium-tags-select-ri .ant-tag) {
  font-size: 11px;
  font-weight: 600;
  border-radius: 6px;
  padding: 1px 8px;
  margin: 2px;
}

/* ── Premium Catalog Select Overrides ── */
:deep(.premium-catalog-select .ant-select-selector) {
  border-radius: 10px !important;
  height: 40px !important;
  padding: 0 12px !important;
  border-color: #e2e8f0 !important;
}
:deep(.premium-catalog-select.ant-select-focused .ant-select-selector) {
  border-color: #6366f1 !important;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12) !important;
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

/* ── Items Table ── */
.items-table-ri {
  width: 100%;
  border-collapse: collapse;
}
.items-table-ri th {
  padding: 12px 16px;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: .05em;
  text-align: left;
  border-bottom: 1px solid #f1f5f9;
}
.items-table-ri td {
  border-bottom: 1px solid #f1f5f9;
  vertical-align: top;
  padding: 8px 12px;
}
.items-table-ri tbody tr:last-child td {
  border-bottom: none;
}
.item-row-ri { transition: background 0.15s; }

.item-input-ri {
  width: 100%;
  padding: 6px 10px;
  font-size: 12px;
  color: #334155;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  outline: none;
  font-family: inherit;
  box-sizing: border-box;
  transition: border-color 0.15s;
}
.item-input-ri:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
}
.item-input-ri::placeholder { color: #94a3b8; }

/* ── Premium Note Textarea ── */
.premium-note-textarea {
  width: 100%;
  padding: 10px 14px;
  font-size: 12px;
  color: #334155;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  outline: none;
  font-family: inherit;
  transition: all 0.2s;
  box-sizing: border-box;
  resize: vertical;
  min-height: 80px;
}
.premium-note-textarea:hover { border-color: #cbd5e1; }
.premium-note-textarea:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12); }
.premium-note-textarea::placeholder { color: #94a3b8; }

/* ── Premium Drawer Footer ── */
.premium-drawer-footer {
  border-top: 1px solid #e2e8f0;
  background: linear-gradient(to right, #f8fafc, #fff);
  padding: 18px 0 0;
  margin-top: 12px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
@media (min-width: 640px) {
  .premium-drawer-footer {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
}

/* ── Premium Modal Input ── */
.premium-modal-input {
  width: 100%;
  padding: 8px 12px;
  font-size: 12px;
  color: #334155;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  outline: none;
  font-family: inherit;
  transition: all 0.2s;
  box-sizing: border-box;
}
.premium-modal-input:hover { border-color: #cbd5e1; }
.premium-modal-input:focus { border-color: #6366f1; background: #fff; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12); }
.premium-modal-input::placeholder { color: #94a3b8; }

/* ── Modal ── */
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
  z-index: 1000;
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
.modal-title { font-size: 14px; font-weight: 700; color: #1e293b; }
.modal-close {
  background: transparent; border: none; font-size: 20px; font-weight: 700;
  color: #94a3b8; cursor: pointer; line-height: 1;
}
.modal-close:hover { color: #475569; }
.modal-body { padding: 24px; overflow-y: auto; }
.modal-foot {
  padding: 16px 24px; border-top: 1px solid #f1f5f9;
  display: flex; justify-content: flex-end; gap: 12px; background: #f8fafc;
}

.premium-btn-primary {
  background: #1e293b; color: #fff; border: 1px solid #1e293b;
  border-radius: 10px; padding: 10px 24px; font-size: 12.5px; font-weight: 600;
  cursor: pointer; transition: all 0.2s; font-family: inherit;
}
.premium-btn-primary:hover { background: #0f172a; }
.premium-btn-outline {
  background: #fff; color: #475569; border: 1px solid #e2e8f0;
  border-radius: 10px; padding: 10px 22px; font-size: 12.5px; font-weight: 600;
  cursor: pointer; transition: all 0.2s; font-family: inherit;
}
.premium-btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }

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
