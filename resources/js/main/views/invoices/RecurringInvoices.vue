<template>
  <div class="ri-page">

    <!-- ── Page Header ── -->
    <div class="page-header">
      <div class="header-left">
        <button class="back-btn" @click="$router.push({ name: 'admin.invoices' })">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
          Invoices
        </button>
        <div class="title-group">
          <h1 class="page-title">Recurring Invoices</h1>
          <span class="page-subtitle">Automatically generated invoice templates</span>
        </div>
      </div>
      <button class="btn-create" @click="openCreateDrawer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Recurring Invoice
      </button>
    </div>

    <!-- ── Stats Cards ── -->
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-icon stat-total"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
        <div class="stat-body"><div class="stat-num">{{ stats.total }}</div><div class="stat-label">Total</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div class="stat-body"><div class="stat-num active">{{ stats.active }}</div><div class="stat-label">Active</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-paused"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg></div>
        <div class="stat-body"><div class="stat-num paused">{{ stats.paused }}</div><div class="stat-label">Paused</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon stat-stopped"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="3" y="3" width="18" height="18" rx="2"/></svg></div>
        <div class="stat-body"><div class="stat-num stopped">{{ stats.stopped }}</div><div class="stat-label">Stopped</div></div>
      </div>
    </div>

    <!-- ── Toolbar ── -->
    <div class="toolbar">
      <div class="toolbar-left">
        <div class="filter-tabs">
          <button
            v-for="f in filterOptions"
            :key="f.value"
            class="filter-tab"
            :class="{ active: filters.status === f.value }"
            @click="filters.status = f.value; loadData()"
          >{{ f.label }}</button>
        </div>
      </div>
      <div class="toolbar-right">
        <a-input-search
          v-model:value="filters.search"
          placeholder="Search client..."
          size="small"
          style="width:220px"
          @search="loadData"
          allow-clear
        />
      </div>
    </div>

    <!-- ── Table ── -->
    <div class="table-card">
      <table class="ri-table" v-if="!loading">
        <thead>
          <tr>
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
          <tr v-for="ri in list" :key="ri.id" class="ri-row">
            <td>
              <div class="client-name">{{ ri.client?.company || '—' }}</div>
            </td>
            <td>
              <span v-if="ri.project" class="project-chip">{{ ri.project.name }}</span>
              <span v-else class="muted">—</span>
            </td>
            <td>
              <span class="freq-badge" :class="'freq-' + ri.frequency">
                {{ freqLabel(ri.frequency) }}
              </span>
            </td>
            <td class="amount-cell">{{ formatCurrency(ri.total) }}</td>
            <td class="muted">
              <span v-if="ri.cycles === 0">Unlimited</span>
              <span v-else>{{ ri.cycles_run }} / {{ ri.cycles }}</span>
            </td>
            <td class="muted">{{ formatDate(ri.last_sent_at) }}</td>
            <td :class="{ 'next-due': ri.status === 'active' }">{{ formatDate(ri.next_invoice_date) }}</td>
            <td>
              <span class="status-badge" :class="'status-' + ri.status">{{ ri.status }}</span>
            </td>
            <td class="action-td" @click.stop>
              <a-dropdown :trigger="['click']" placement="bottomRight">
                <button class="three-dots-btn">
                  <svg viewBox="0 0 24 24" fill="currentColor" width="15" height="15"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
                </button>
                <template #overlay>
                  <a-menu>
                    <a-menu-item key="edit" @click="editRI(ri)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="margin-right:6px;vertical-align:middle"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                      Edit
                    </a-menu-item>
                    <a-menu-item key="pause" v-if="ri.status === 'active'" @click="setStatus(ri, 'paused')">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="margin-right:6px;vertical-align:middle"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                      Pause
                    </a-menu-item>
                    <a-menu-item key="resume" v-if="ri.status === 'paused'" @click="setStatus(ri, 'active')">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="margin-right:6px;vertical-align:middle"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                      Resume
                    </a-menu-item>
                    <a-menu-item key="stop" v-if="ri.status !== 'stopped'" @click="setStatus(ri, 'stopped')">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="margin-right:6px;vertical-align:middle"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                      Stop
                    </a-menu-item>
                    <a-menu-divider />
                    <a-menu-item key="delete" @click="deleteRI(ri.id)" style="color:#ef4444">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="margin-right:6px;vertical-align:middle"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                      Delete
                    </a-menu-item>
                  </a-menu>
                </template>
              </a-dropdown>
            </td>
          </tr>
          <tr v-if="!list.length">
            <td colspan="9">
              <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" width="48" height="48"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <p>No recurring invoices found</p>
                <button class="btn-create" style="margin-top:8px" @click="openCreateDrawer">Create one now</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="loading" class="table-loading">
        <a-spin />
        <span>Loading recurring invoices...</span>
      </div>

      <!-- Pagination -->
      <div class="table-footer" v-if="pagination.total > filters.perPage">
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
         Create / Edit Drawer
    ══════════════════════════════════════════════════ -->
    <a-drawer
      v-model:open="showDrawer"
      :title="editingId ? 'Edit Recurring Invoice' : 'New Recurring Invoice'"
      placement="right"
      :width="1100"
      :destroyOnClose="true"
    >
      <a-form layout="vertical" :model="form" @finish="submitForm">

        <!-- ─── Two-column settings grid ─── -->
        <div class="form-settings-grid">

          <!-- Column 1 -->
          <div class="settings-col">
            <a-form-item label="Client" name="client_id" :rules="[{required:true,message:'Select a client'}]">
              <a-select v-model:value="form.client_id" placeholder="Select client..." style="width:100%" showSearch optionFilterProp="children">
                <a-select-option v-for="c in clients" :key="c.id" :value="c.id">{{ c.company }}</a-select-option>
              </a-select>
            </a-form-item>

            <!-- Billing / Shipping address overrides -->
            <div class="address-overrides-wrap">
              <div class="address-toggle" @click="showAddressOverrides = !showAddressOverrides">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" :class="{ rotated: showAddressOverrides }">
                  <polyline points="9 18 15 12 9 6"/>
                </svg>
                <span>Edit Billing &amp; Shipping Address Override</span>
              </div>
              <transition name="slide-fade">
                <div v-show="showAddressOverrides" class="addresses-fields">
                  <!-- Bill To -->
                  <div class="address-section">
                    <div class="address-title">Bill To</div>
                    <a-form-item label="Street">
                      <a-textarea v-model:value="form.billing_street" :rows="2" placeholder="Street Address" />
                    </a-form-item>
                    <div class="address-grid-row">
                      <a-form-item label="City"><a-input v-model:value="form.billing_city" /></a-form-item>
                      <a-form-item label="State"><a-input v-model:value="form.billing_state" /></a-form-item>
                    </div>
                    <div class="address-grid-row">
                      <a-form-item label="Zip Code"><a-input v-model:value="form.billing_zip" /></a-form-item>
                      <a-form-item label="Country"><a-input v-model:value="form.billing_country" /></a-form-item>
                    </div>
                  </div>
                  <!-- Ship To -->
                  <div class="address-section">
                    <div class="address-title">Ship To</div>
                    <a-form-item label="Street">
                      <a-textarea v-model:value="form.shipping_street" :rows="2" placeholder="Street Address" />
                    </a-form-item>
                    <div class="address-grid-row">
                      <a-form-item label="City"><a-input v-model:value="form.shipping_city" /></a-form-item>
                      <a-form-item label="State"><a-input v-model:value="form.shipping_state" /></a-form-item>
                    </div>
                    <div class="address-grid-row">
                      <a-form-item label="Zip Code"><a-input v-model:value="form.shipping_zip" /></a-form-item>
                      <a-form-item label="Country"><a-input v-model:value="form.shipping_country" /></a-form-item>
                    </div>
                  </div>
                </div>
              </transition>
            </div>

            <a-form-item label="Project" name="project_id">
              <a-select v-model:value="form.project_id" placeholder="Select project..." style="width:100%" allowClear>
                <a-select-option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</a-select-option>
              </a-select>
            </a-form-item>

            <a-form-item label="Allowed Payment Modes">
              <a-checkbox-group v-model:value="form.allowed_payment_modes_list">
                <a-checkbox value="Bank">Bank Transfer</a-checkbox>
                <a-checkbox value="Stripe Checkout">Stripe Checkout</a-checkbox>
                <a-checkbox value="PayPal">PayPal</a-checkbox>
                <a-checkbox value="Cash">Cash</a-checkbox>
              </a-checkbox-group>
            </a-form-item>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
              <a-form-item label="Currency">
                <a-select v-model:value="form.currency" style="width:100%">
                  <a-select-option value="USD">USD</a-select-option>
                  <a-select-option value="EUR">EUR</a-select-option>
                  <a-select-option value="GBP">GBP</a-select-option>
                  <a-select-option value="CAD">CAD</a-select-option>
                  <a-select-option value="INR">INR</a-select-option>
                  <a-select-option value="AUD">AUD</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Sale Agent">
                <a-select v-model:value="form.sale_agent" style="width:100%" allowClear>
                  <a-select-option v-for="s in staff" :key="s.id" :value="s.name">{{ s.name }}</a-select-option>
                </a-select>
              </a-form-item>
            </div>
          </div>

          <!-- Column 2 -->
          <div class="settings-col">
            <a-form-item label="Frequency" name="frequency" :rules="[{required:true}]">
              <a-select v-model:value="form.frequency" style="width:100%">
                <a-select-option value="daily">Daily</a-select-option>
                <a-select-option value="weekly">Weekly</a-select-option>
                <a-select-option value="monthly">Monthly</a-select-option>
                <a-select-option value="quarterly">Quarterly (every 3 months)</a-select-option>
                <a-select-option value="yearly">Yearly</a-select-option>
              </a-select>
            </a-form-item>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
              <a-form-item label="Start Date" name="date_from" :rules="[{required:true,message:'Required'}]">
                <a-date-picker v-model:value="form.date_from" style="width:100%" value-format="YYYY-MM-DD" />
              </a-form-item>
              <a-form-item label="End Date (optional)">
                <a-date-picker v-model:value="form.date_to" style="width:100%" value-format="YYYY-MM-DD" />
              </a-form-item>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
              <a-form-item label="Cycles (0 = unlimited)">
                <a-input-number v-model:value="form.cycles" :min="0" style="width:100%" />
              </a-form-item>
              <a-form-item label="Discount Type">
                <a-select v-model:value="form.discount_type" style="width:100%" @change="recalc">
                  <a-select-option value="no_discount">No Discount</a-select-option>
                  <a-select-option value="before_tax">Before Tax</a-select-option>
                  <a-select-option value="after_tax">After Tax</a-select-option>
                </a-select>
              </a-form-item>
            </div>

            <a-form-item label="Tags">
              <a-input v-model:value="form.tags" placeholder="Comma separated tags..." />
            </a-form-item>

            <a-form-item label="Admin Note">
              <a-textarea v-model:value="form.admin_note" :rows="3" placeholder="Admin notes (not visible to client)..." />
            </a-form-item>
          </div>
        </div>

        <a-divider style="margin: 16px 0" />

        <!-- ─── Line Items Section ─── -->
        <div class="line-items-section">
          <div class="line-items-header">
            <div class="qty-mode-group">
              <span style="font-weight:600;margin-right:12px;font-size:13px">Show quantity as:</span>
              <a-radio-group v-model:value="form.qty_display_mode">
                <a-radio value="qty">Qty</a-radio>
                <a-radio value="hours">Hours</a-radio>
                <a-radio value="qty_hours">Qty/Hours</a-radio>
              </a-radio-group>
            </div>
            <div class="add-predefined-wrap">
              <span style="font-size:12px;color:#64748b;margin-right:8px;font-weight:500">Add catalog item:</span>
              <a-select placeholder="Select item..." style="width:220px" @change="addCatalogItem" :value="null" showSearch optionFilterProp="children">
                <a-select-option v-for="item in catalogItems" :key="item.id" :value="item.id">
                  {{ item.name }} ({{ formatCurrency(item.rate) }})
                </a-select-option>
              </a-select>
            </div>
          </div>

          <!-- Dynamic Line Items Table -->
          <div class="items-table-wrapper">
            <table class="items-form-table">
              <thead>
                <tr>
                  <th style="width:240px">Item</th>
                  <th style="width:280px">Description</th>
                  <th style="width:90px">{{ qtyLabel }}</th>
                  <th style="width:70px">Unit</th>
                  <th style="width:110px">Rate</th>
                  <th style="width:120px">Tax</th>
                  <th style="width:110px;text-align:right">Amount</th>
                  <th style="width:44px"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in form.items" :key="index">
                  <td>
                    <a-input v-model:value="item.description" placeholder="Item name..." />
                  </td>
                  <td>
                    <a-textarea v-model:value="item.long_description" placeholder="Long description..." :rows="1" />
                  </td>
                  <td>
                    <a-input-number v-model:value="item.qty" :min="0.01" style="width:100%" @change="recalc" />
                  </td>
                  <td>
                    <a-input v-model:value="item.unit" placeholder="Unit" />
                  </td>
                  <td>
                    <a-input-number v-model:value="item.rate" :min="0" style="width:100%" @change="recalc" />
                  </td>
                  <td>
                    <a-select v-model:value="item.tax_rate" style="width:100%" @change="recalc">
                      <a-select-option :value="0">No Tax</a-select-option>
                      <a-select-option :value="5">5.00%</a-select-option>
                      <a-select-option :value="10">10.00%</a-select-option>
                      <a-select-option :value="18">18.00%</a-select-option>
                    </a-select>
                  </td>
                  <td style="text-align:right;font-weight:600;padding-right:8px;font-size:13px;color:#1e293b">
                    {{ formatCurrency((item.qty || 0) * (item.rate || 0)) }}
                  </td>
                  <td style="text-align:center">
                    <button type="button" class="btn-delete-row" @click="removeItem(index)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                    </button>
                  </td>
                </tr>
                <tr v-if="!form.items.length">
                  <td colspan="8" class="empty-items-row">
                    No items added. Click "Add Item Row" or select a catalog item above.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <button type="button" class="btn-add-row" @click="addItem">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Item Row
          </button>
        </div>

        <a-divider style="margin: 16px 0" />

        <!-- ─── Totals & Notes ─── -->
        <div class="totals-notes-grid">
          <!-- Notes column -->
          <div class="notes-col">
            <a-form-item label="Client Note">
              <a-textarea v-model:value="form.client_note" :rows="3" placeholder="Visible to client..." />
            </a-form-item>
            <a-form-item label="Terms &amp; Conditions">
              <a-textarea v-model:value="form.terms_conditions" :rows="5" placeholder="Terms and conditions..." />
            </a-form-item>
          </div>

          <!-- Totals column -->
          <div class="totals-col">
            <div class="summary-line">
              <span>Sub Total :</span>
              <span class="val">{{ formatCurrency(form.subtotal) }}</span>
            </div>

            <div class="summary-line inline-input-line" v-if="form.discount_type !== 'no_discount'">
              <div class="label-group">
                <span>Discount :</span>
                <a-input-number v-model:value="form.discount_value_input" :min="0" size="small" style="width:75px;margin:0 6px" @change="recalc" />
                <a-select v-model:value="form.discount_symbol" size="small" style="width:55px" @change="recalc">
                  <a-select-option value="%">%</a-select-option>
                  <a-select-option value="$">$</a-select-option>
                </a-select>
              </div>
              <span class="val text-danger">-{{ formatCurrency(form.discount_val) }}</span>
            </div>

            <div class="summary-line inline-input-line">
              <div class="label-group">
                <span>Adjustment :</span>
                <a-input-number v-model:value="form.adjustment" size="small" style="width:90px;margin-left:6px" @change="recalc" />
              </div>
              <span class="val">{{ formatCurrency(form.adjustment) }}</span>
            </div>

            <div class="summary-line tax-summary-line" v-if="form.tax > 0">
              <span>Tax :</span>
              <span class="val">{{ formatCurrency(form.tax) }}</span>
            </div>

            <div class="summary-line grand-total-line">
              <span>Total :</span>
              <span class="val">{{ formatCurrency(form.total) }}</span>
            </div>

            <div class="billing-note">
              Billed <strong>{{ freqLabel(form.frequency) }}</strong>
            </div>
          </div>
        </div>

        <div class="drawer-footer">
          <a-button @click="showDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">
            {{ editingId ? 'Save Changes' : 'Create Recurring Invoice' }}
          </a-button>
        </div>
      </a-form>
    </a-drawer>

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
  tags: '',
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
    const showAddressOverrides = ref(false);
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
      // line-item subtotal
      let rawSub = form.items.reduce((s, it) => s + (it.qty || 0) * (it.rate || 0), 0);

      // discount before tax
      let discountAmt = 0;
      if (form.discount_type === 'before_tax') {
        discountAmt = form.discount_symbol === '%'
          ? rawSub * (form.discount_value_input || 0) / 100
          : (form.discount_value_input || 0);
        rawSub -= discountAmt;
      }

      // tax (per-line)
      let taxAmt = form.items.reduce((s, it) => {
        let base = (it.qty || 0) * (it.rate || 0);
        if (form.discount_type === 'before_tax' && form.subtotal > 0) {
          base -= base * (discountAmt / (rawSub + discountAmt || 1));
        }
        return s + base * (it.tax_rate || 0) / 100;
      }, 0);

      // discount after tax
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
      showAddressOverrides.value = false;
      Object.assign(form, defaultForm());
      showDrawer.value = true;
    };

    const editRI = async (ri) => {
      editingId.value = ri.id;
      showAddressOverrides.value = false;
      // load full detail
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
          tags:        d.tags || '',
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
          tags:                   form.tags,
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
      pagination, filters, filterOptions, form, showDrawer, showAddressOverrides, editingId,
      qtyLabel, recalc, addItem, removeItem, addCatalogItem,
      loadData, openCreateDrawer, editRI, submitForm, setStatus, deleteRI,
      freqLabel, formatCurrency, formatDate,
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

/* ── Header ── */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  gap: 12px;
  flex-wrap: wrap;
}
.header-left { display: flex; align-items: center; gap: 14px; }
.back-btn {
  background: none;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 12.5px;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  font-family: inherit;
  transition: all 0.12s;
  white-space: nowrap;
}
.back-btn:hover { background: #f1f5f9; border-color: #cbd5e1; }
.page-title { font-size: 20px; font-weight: 700; color: #1e293b; margin: 0 0 2px; }
.page-subtitle { font-size: 12px; color: #94a3b8; }
.btn-create {
  background: #1e293b;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 9px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: inherit;
  transition: background 0.15s;
  white-space: nowrap;
}
.btn-create:hover { background: #0f172a; }

/* ── Stats ── */
.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  margin-bottom: 16px;
}
.stat-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 14px 16px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.stat-icon {
  width: 40px; height: 40px;
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.stat-total   { background: #f1f5f9; color: #64748b; }
.stat-active  { background: #dcfce7; color: #16a34a; }
.stat-paused  { background: #fef3c7; color: #d97706; }
.stat-stopped { background: #fee2e2; color: #dc2626; }
.stat-num { font-size: 22px; font-weight: 800; color: #1e293b; line-height: 1; }
.stat-num.active  { color: #16a34a; }
.stat-num.paused  { color: #d97706; }
.stat-num.stopped { color: #dc2626; }
.stat-label { font-size: 11.5px; color: #94a3b8; font-weight: 500; margin-top: 3px; }

/* ── Toolbar ── */
.toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
  gap: 10px;
  flex-wrap: wrap;
}
.toolbar-left, .toolbar-right { display: flex; align-items: center; gap: 8px; }

.filter-tabs { display: flex; background: #fff; border: 1px solid #e2e8f0; border-radius: 7px; overflow: hidden; }
.filter-tab {
  background: none;
  border: none;
  padding: 7px 16px;
  font-size: 12.5px;
  font-weight: 500;
  color: #64748b;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.12s;
  border-right: 1px solid #e2e8f0;
}
.filter-tab:last-child { border-right: none; }
.filter-tab:hover  { background: #f8fafc; color: #334155; }
.filter-tab.active { background: #1e293b; color: #fff; }

/* ── Table ── */
.table-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.ri-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.ri-table th {
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  padding: 10px 14px;
  text-align: left;
  font-size: 11.5px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  white-space: nowrap;
}
.ri-table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.ri-row { cursor: default; transition: background 0.1s; }
.ri-row:hover { background: #f8fafc; }
.ri-row:last-child td { border-bottom: none; }

.client-name { font-weight: 600; color: #1e293b; font-size: 13px; }
.amount-cell { font-weight: 700; color: #1e293b; white-space: nowrap; }
.muted { color: #94a3b8; font-size: 12px; }
.next-due { color: #3b82f6; font-weight: 600; font-size: 12.5px; }
.action-td { width: 40px; text-align: center; }

.project-chip {
  display: inline-block;
  background: #eff6ff; color: #3b82f6;
  border: 1px solid #bfdbfe;
  border-radius: 4px; padding: 2px 7px;
  font-size: 11px; font-weight: 600; white-space: nowrap;
}
.freq-badge {
  display: inline-block;
  padding: 3px 9px;
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

.status-badge {
  display: inline-block;
  padding: 3px 9px;
  border-radius: 5px;
  font-size: 11px;
  font-weight: 700;
  text-transform: capitalize;
}
.status-active  { background: #dcfce7; color: #16a34a; }
.status-paused  { background: #fef3c7; color: #d97706; }
.status-stopped { background: #fee2e2; color: #dc2626; }

.three-dots-btn {
  background: none;
  border: 1px solid transparent;
  border-radius: 5px;
  padding: 4px 6px;
  cursor: pointer;
  color: #94a3b8;
  display: flex; align-items: center;
  transition: all 0.12s;
}
.three-dots-btn:hover { background: #f1f5f9; border-color: #e2e8f0; color: #475569; }

.empty-state {
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  padding: 60px 20px; gap: 8px;
  color: #94a3b8; font-size: 13px;
}
.empty-state p { margin: 0; }
.table-loading {
  display: flex; align-items: center; justify-content: center;
  gap: 10px; padding: 60px 0; color: #94a3b8; font-size: 13px;
}
.table-footer {
  display: flex; justify-content: flex-end;
  padding: 12px 16px; border-top: 1px solid #f1f5f9;
}

/* ═══════════════════════════════════════════
   Drawer Form
═══════════════════════════════════════════ */
.form-settings-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px 32px;
}
.settings-col {}

/* Address overrides */
.address-overrides-wrap {
  margin-bottom: 16px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
}
.address-toggle {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 10px 14px;
  cursor: pointer;
  font-size: 12.5px;
  font-weight: 600;
  color: #475569;
  background: #f8fafc;
  user-select: none;
  transition: background 0.12s;
}
.address-toggle:hover { background: #f1f5f9; }
.address-toggle svg { transition: transform 0.2s; flex-shrink: 0; }
.address-toggle svg.rotated { transform: rotate(90deg); }

.addresses-fields { padding: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.address-section {}
.address-title {
  font-size: 11.5px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 12px;
}
.address-grid-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

/* Line items */
.line-items-section {}
.line-items-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 12px;
}
.qty-mode-group { display: flex; align-items: center; }
.add-predefined-wrap { display: flex; align-items: center; }

.items-table-wrapper { overflow-x: auto; margin-bottom: 10px; }
.items-form-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
  min-width: 900px;
}
.items-form-table th {
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
  padding: 8px 8px;
  font-size: 11.5px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  white-space: nowrap;
}
.items-form-table td {
  padding: 6px 6px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: top;
}
.empty-items-row {
  color: #94a3b8;
  font-size: 13px;
  text-align: center;
  padding: 20px;
  font-style: italic;
}
.btn-delete-row {
  background: none;
  border: 1px solid transparent;
  border-radius: 5px;
  padding: 5px 6px;
  cursor: pointer;
  color: #ef4444;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.12s;
}
.btn-delete-row:hover { background: #fef2f2; border-color: #fecaca; }
.btn-add-row {
  background: none;
  border: 1px dashed #cbd5e1;
  border-radius: 6px;
  padding: 7px 14px;
  font-size: 12.5px;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  font-family: inherit;
  transition: all 0.12s;
  margin-top: 4px;
}
.btn-add-row:hover { background: #f8fafc; border-color: #94a3b8; }

/* Totals & Notes */
.totals-notes-grid {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 24px;
  align-items: start;
}
.notes-col {}
.totals-col {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.summary-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13.5px;
  color: #475569;
}
.summary-line .val { font-weight: 600; color: #1e293b; min-width: 90px; text-align: right; }
.text-danger { color: #ef4444 !important; }
.inline-input-line { align-items: center; }
.label-group { display: flex; align-items: center; gap: 4px; }
.tax-summary-line { color: #d97706; }
.tax-summary-line .val { color: #d97706 !important; }
.grand-total-line {
  font-size: 16px;
  font-weight: 800;
  color: #1e293b;
  border-top: 2px solid #1e293b;
  padding-top: 10px;
  margin-top: 4px;
}
.grand-total-line .val { color: #1e293b; font-size: 17px; }
.billing-note {
  text-align: right;
  font-size: 11.5px;
  color: #94a3b8;
  margin-top: 4px;
}

/* Footer */
.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
}

/* Transitions */
.slide-fade-enter-active, .slide-fade-leave-active { transition: all 0.25s ease; }
.slide-fade-enter-from, .slide-fade-leave-to { opacity: 0; transform: translateY(-8px); }

/* Responsive */
@media (max-width: 900px) {
  .form-settings-grid { grid-template-columns: 1fr; }
  .totals-notes-grid  { grid-template-columns: 1fr; }
  .stats-row { grid-template-columns: repeat(2, 1fr); }
}
</style>
