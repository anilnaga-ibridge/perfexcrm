<template>
  <div class="invoice-form-page">

    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
      <div class="flex items-center gap-2 text-sm">
        <router-link to="/admin/invoices" class="breadcrumb-link">Invoices</router-link>
        <span class="text-slate-400">/</span>
        <span class="breadcrumb-current">Create New Invoice</span>
      </div>
    </div>

    <div class="form-page-layout">

      <!-- LEFT: Main Form -->
      <div class="form-main">

        <!-- ZIP INVOICE - Merging Section -->
        <div class="merge-section" v-if="mergeCandidates.length > 0">
          <div class="merge-header">
            <h3 class="merge-title">Invoices Available for Merging</h3>
          </div>
          <div class="merge-list">
            <label v-for="inv in mergeCandidates" :key="inv.id" class="merge-item">
              <input type="checkbox" v-model="selectedMergeIds" :value="inv.id" class="merge-checkbox" />
              <span class="merge-inv-label">{{ inv.number }} - {{ formatCurrency(inv.total) }}</span>
            </label>
          </div>
          <label class="merge-cancel-opt">
            <input type="checkbox" v-model="markMergedAsCancelled" />
            <span>Mark merged invoices as cancelled instead of deleting</span>
          </label>
          <a href="#" class="how-calculated-link" @click.prevent>How total is calculated?</a>
        </div>

        <!-- Customer + Address Section -->
        <div class="form-section">
          <div class="form-settings-grid">

            <!-- Left Column -->
            <div class="settings-col">
              <div class="form-group">
                <label class="form-label required-label">Customer</label>
                <select class="form-ctrl" v-model="form.client_id" @change="onClientChange">
                  <option value="" disabled>Select customer...</option>
                  <option v-for="c in clientOptions" :key="c.id" :value="c.id">{{ c.company }}</option>
                </select>
              </div>

              <!-- Addresses Display -->
              <div class="address-pair" v-if="selectedClient">
                <div class="address-block">
                  <div class="address-block-title">Bill To</div>
                  <div class="address-block-content">
                    <div v-if="form.billing_street">{{ form.billing_street }}</div>
                    <div v-if="form.billing_city || form.billing_state">
                      {{ form.billing_city }}<span v-if="form.billing_city && form.billing_state">, </span>{{ form.billing_state }}
                    </div>
                    <div v-if="form.billing_country || form.billing_zip">
                      {{ form.billing_country }}<span v-if="form.billing_zip"> {{ form.billing_zip }}</span>
                    </div>
                    <div class="no-addr-text" v-if="!form.billing_street && !form.billing_city">—</div>
                  </div>
                </div>
                <div class="address-block">
                  <div class="address-block-title">Ship to</div>
                  <div class="address-block-content">
                    <div v-if="form.shipping_street">{{ form.shipping_street }}</div>
                    <div v-if="form.shipping_city || form.shipping_state">
                      {{ form.shipping_city }}<span v-if="form.shipping_city && form.shipping_state">, </span>{{ form.shipping_state }}
                    </div>
                    <div v-if="form.shipping_country || form.shipping_zip">
                      {{ form.shipping_country }}<span v-if="form.shipping_zip"> {{ form.shipping_zip }}</span>
                    </div>
                    <div class="no-addr-text" v-if="!form.shipping_street && !form.shipping_city">--<br/>--, --<br/>--, --</div>
                  </div>
                </div>
              </div>

              <!-- Address Override Collapsible -->
              <div class="addr-override-toggle" @click="showAddressOverrides = !showAddressOverrides">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"
                  :style="{ transform: showAddressOverrides ? 'rotate(90deg)' : 'rotate(0deg)', transition: 'transform 0.15s' }">
                  <polyline points="9 18 15 12 9 6"/>
                </svg>
                Edit Billing &amp; Shipping Address Override
              </div>
              <div v-show="showAddressOverrides" class="addr-fields-grid">
                <div>
                  <div class="addr-section-title">Bill To</div>
                  <div class="form-group"><label class="form-label">Street</label><textarea class="form-ctrl" rows="2" v-model="form.billing_street" placeholder="Street Address"></textarea></div>
                  <div class="inline-pair">
                    <div class="form-group"><label class="form-label">City</label><input type="text" class="form-ctrl" v-model="form.billing_city" /></div>
                    <div class="form-group"><label class="form-label">State</label><input type="text" class="form-ctrl" v-model="form.billing_state" /></div>
                  </div>
                  <div class="inline-pair">
                    <div class="form-group"><label class="form-label">Zip</label><input type="text" class="form-ctrl" v-model="form.billing_zip" /></div>
                    <div class="form-group"><label class="form-label">Country</label><input type="text" class="form-ctrl" v-model="form.billing_country" /></div>
                  </div>
                </div>
                <div>
                  <div class="addr-section-title">Ship To</div>
                  <div class="form-group"><label class="form-label">Street</label><textarea class="form-ctrl" rows="2" v-model="form.shipping_street" placeholder="Street Address"></textarea></div>
                  <div class="inline-pair">
                    <div class="form-group"><label class="form-label">City</label><input type="text" class="form-ctrl" v-model="form.shipping_city" /></div>
                    <div class="form-group"><label class="form-label">State</label><input type="text" class="form-ctrl" v-model="form.shipping_state" /></div>
                  </div>
                  <div class="inline-pair">
                    <div class="form-group"><label class="form-label">Zip</label><input type="text" class="form-ctrl" v-model="form.shipping_zip" /></div>
                    <div class="form-group"><label class="form-label">Country</label><input type="text" class="form-ctrl" v-model="form.shipping_country" /></div>
                  </div>
                </div>
              </div>

              <!-- Payment Modes -->
              <div class="form-group mt-3">
                <label class="form-label">Allowed payment modes for this invoice</label>
                <div class="payment-modes-row">
                  <label v-for="mode in paymentModes" :key="mode" class="mode-chip" :class="{ active: form.allowed_payment_modes_list.includes(mode) }">
                    <input type="checkbox" v-model="form.allowed_payment_modes_list" :value="mode" style="display:none" />
                    {{ mode }}
                  </label>
                </div>
              </div>

              <!-- Currency + Sale Agent -->
              <div class="inline-pair mt-2">
                <div class="form-group">
                  <label class="form-label required-label">Currency</label>
                  <select class="form-ctrl" v-model="form.currency">
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="GBP">GBP</option>
                    <option value="CAD">CAD</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Sale Agent</label>
                  <select class="form-ctrl" v-model="form.sale_agent">
                    <option value="">Select agent...</option>
                    <option v-for="s in staffOptions" :key="s.id" :value="s.name">{{ s.name }}</option>
                  </select>
                </div>
              </div>

              <!-- Recurring + Discount Type -->
              <div class="inline-pair">
                <div class="form-group">
                  <label class="form-label">Recurring Invoice?</label>
                  <select class="form-ctrl" v-model="form.recurring_type">
                    <option value="no">No</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="yearly">Yearly</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Discount Type</label>
                  <select class="form-ctrl" v-model="form.discount_type" @change="recalc">
                    <option value="no_discount">No discount</option>
                    <option value="before_tax">Before Tax</option>
                    <option value="after_tax">After Tax</option>
                  </select>
                </div>
              </div>

              <!-- Admin Note -->
              <div class="form-group">
                <label class="form-label">Admin Note</label>
                <textarea class="form-ctrl" rows="3" v-model="form.admin_note" placeholder="Admin notes..."></textarea>
              </div>
            </div>

            <!-- Right Column -->
            <div class="settings-col">
              <!-- Invoice Number -->
              <div class="form-group">
                <label class="form-label required-label">Invoice Number</label>
                <div class="inv-num-display">
                  <span class="inv-prefix">INV-</span>
                  <input type="text" class="form-ctrl inv-num-suffix" v-model="invoiceNumberSuffix" />
                </div>
              </div>

              <!-- Invoice Date + Due Date -->
              <div class="inline-pair">
                <div class="form-group">
                  <label class="form-label required-label">Invoice Date</label>
                  <input type="date" class="form-ctrl" v-model="form.date" />
                </div>
                <div class="form-group">
                  <label class="form-label">Due Date</label>
                  <input type="date" class="form-ctrl" v-model="form.duedate" />
                </div>
              </div>

              <!-- Prevent overdue -->
              <div class="form-group">
                <label class="checkbox-row">
                  <input type="checkbox" v-model="form.prevent_overdue_reminders" />
                  <span>Prevent sending overdue reminders for this invoice</span>
                </label>
              </div>

              <!-- Tags -->
              <div class="form-group">
                <label class="form-label">Tags</label>
                <div class="tags-input-wrap">
                  <span v-for="tag in tagsList" :key="tag" class="tag-chip">
                    {{ tag }}
                    <button type="button" class="tag-remove" @click="removeTag(tag)">×</button>
                  </span>
                  <input type="text" class="tag-input" placeholder="Tag" v-model="tagInput" @keydown.enter.prevent="addTag" @keydown.comma.prevent="addTag" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Divider -->
        <div class="section-divider"></div>

        <!-- Line Items Section -->
        <div class="line-items-section">
          <!-- Qty mode toggle -->
          <div class="line-items-header">
            <div class="qty-mode-wrap">
              <span class="qty-mode-label">Show quantity as:</span>
              <div class="qty-mode-group">
                <label v-for="mode in ['Qty','Hours','Qty/Hours']" :key="mode" class="qty-radio"
                  :class="{ active: form.qty_display_mode === mode.toLowerCase().replace('/', '_') }">
                  <input type="radio" :value="mode.toLowerCase().replace('/', '_')" v-model="form.qty_display_mode" style="display:none" />
                  {{ mode }}
                </label>
              </div>
            </div>
          </div>

          <!-- Items Table -->
          <div class="items-table-wrap">
            <table class="items-tbl">
              <thead>
                <tr>
                  <th class="th-item">Item</th>
                  <th class="th-desc">Description</th>
                  <th class="th-qty">{{ qtyLabel }}</th>
                  <th class="th-rate">Rate</th>
                  <th class="th-tax">Tax</th>
                  <th class="th-amount">Amount</th>
                  <th class="th-del"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, idx) in form.items" :key="idx" class="item-row">
                  <td class="td-item">
                    <input type="text" class="item-ctrl" v-model="item.description" placeholder="Description" />
                    <input type="text" class="item-ctrl item-long-desc" v-model="item.long_description" placeholder="Long description" />
                  </td>
                  <td class="td-desc">
                    <!-- merge of description + long desc in one cell on small screens -->
                  </td>
                  <td class="td-qty">
                    <input type="number" class="item-ctrl" v-model.number="item.qty" min="0.01" step="0.01" @input="recalc" />
                  </td>
                  <td class="td-rate">
                    <input type="number" class="item-ctrl" v-model.number="item.rate" min="0" step="0.01" @input="recalc" />
                  </td>
                  <td class="td-tax">
                    <select class="item-ctrl item-tax-select" v-model="item.tax_rate" @change="recalc">
                      <option :value="null">—</option>
                      <option :value="5">5.00%</option>
                      <option :value="10">10.00%</option>
                      <option :value="18">18.00%</option>
                    </select>
                  </td>
                  <td class="td-amount">{{ formatCurrency(itemAmount(item)) }}</td>
                  <td class="td-del">
                    <button type="button" class="del-item-btn" @click="removeItem(idx)" v-if="form.items.length > 1">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Add Row -->
          <button type="button" class="btn-add-row" @click="addItem">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Item
          </button>

          <!-- Totals Section -->
          <div class="totals-section">
            <table class="totals-tbl">
              <tbody>
                <tr>
                  <td class="total-lbl">Sub Total :</td>
                  <td class="total-val">{{ formatCurrency(subtotal) }}</td>
                </tr>
                <tr v-if="form.discount_type !== 'no_discount'">
                  <td class="total-lbl">
                    <span>Discount</span>
                    <input type="number" class="discount-input" v-model.number="form.discount_percent" min="0" max="100" step="0.01" @input="recalc" />
                    <span>%</span>
                  </td>
                  <td class="total-val total-discount">-{{ formatCurrency(discountVal) }}</td>
                </tr>
                <tr>
                  <td class="total-lbl">
                    <span>Adjustment</span>
                    <input type="number" class="adjustment-input" v-model.number="form.adjustment" step="0.01" @input="recalc" />
                  </td>
                  <td class="total-val">{{ formatCurrency(form.adjustment || 0) }}</td>
                </tr>
                <tr class="total-row-final">
                  <td class="total-lbl">Total :</td>
                  <td class="total-val total-final">{{ formatCurrency(totalAmount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Client Note + Terms -->
          <div class="footer-fields">
            <div class="form-group">
              <label class="form-label">Client Note</label>
              <textarea class="form-ctrl" rows="3" v-model="form.client_note" placeholder="Client note..."></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Terms &amp; Conditions</label>
              <textarea class="form-ctrl" rows="4" v-model="form.terms_conditions"></textarea>
            </div>
          </div>
        </div>

        <!-- Save / Cancel Bar (inside form) -->
        <div class="form-save-bar">
          <button type="button" class="btn-save-invoice" :disabled="saving" @click="submitInvoice">
            <svg v-if="saving" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            {{ saving ? 'Saving...' : 'Save Invoice' }}
          </button>
          <router-link :to="{ name: 'admin.invoices' }" class="btn-cancel-invoice">Cancel</router-link>
        </div>

      </div><!-- /form-main -->

    </div>

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
    const showAddressOverrides = ref(false);
    const mergeCandidates   = ref([]);
    const selectedMergeIds  = ref([]);
    const markMergedAsCancelled = ref(false);
    const tagInput  = ref('');
    const tagsList  = ref([]);
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

    const recalc = () => { /* Vue reactivity handles it */ };

    // ── Methods ──────────────────────────────────────────────────────
    const addItem = () => {
      form.items.push({ description: '', long_description: '', qty: 1, unit: '', rate: 0, tax_rate: null });
    };

    const removeItem = (idx) => { form.items.splice(idx, 1); };

    const addTag = () => {
      const t = tagInput.value.replace(',', '').trim();
      if (t && !tagsList.value.includes(t)) tagsList.value.push(t);
      tagInput.value = '';
    };

    const removeTag = (tag) => {
      tagsList.value = tagsList.value.filter(t => t !== tag);
    };

    const onClientChange = async () => {
      const id = form.client_id;
      if (!id) { selectedClient.value = null; return; }
      const client = clientOptions.value.find(c => c.id === id);
      selectedClient.value = client || null;
      if (client) {
        form.billing_street  = client.address || '';
        form.billing_city    = client.city || '';
        form.billing_state   = client.state || '';
        form.billing_zip     = client.zip || '';
        form.billing_country = client.country || '';
      }
      // Load unpaid invoices for merging
      try {
        const res = await axios.get('/invoices', { params: { client_id: id, status: 'unpaid', per_page: 50 } });
        mergeCandidates.value = res.data.invoices?.data || [];
      } catch { mergeCandidates.value = []; }
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
          tags: tagsList.value.join(','),
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
      saving,
      clientOptions,
      staffOptions,
      selectedClient,
      showAddressOverrides,
      mergeCandidates,
      selectedMergeIds,
      markMergedAsCancelled,
      tagInput,
      tagsList,
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
      addTag,
      removeTag,
      onClientChange,
      submitInvoice,
    };
  },
});
</script>

<style scoped>
@import '@/main/module-shared.css';

.invoice-form-page {
  font-family: 'Inter', -apple-system, sans-serif;
  background: #f1f5f9;
}

/* Breadcrumb */
.breadcrumb-bar {
  padding: 14px 0 10px;
}
.breadcrumb-link {
  color: #64748b;
  text-decoration: none;
  font-weight: 500;
  font-size: 13.5px;
}
.breadcrumb-link:hover { color: #2563eb; }
.breadcrumb-current {
  color: #1e293b;
  font-weight: 600;
  font-size: 13.5px;
}

.form-page-layout {
  max-width: 1200px;
}

.form-main {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  box-shadow: 0 1px 3px rgba(0,0,0,.02);
}

/* ── Merge Section ──────────────────────────────────────────────── */
.merge-section {
  padding: 16px 24px;
  border-bottom: 1px solid #e2e8f0;
  background: #fffbf0;
}
.merge-title {
  font-size: 13.5px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 10px;
}
.merge-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 10px;
}
.merge-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  cursor: pointer;
}
.merge-checkbox { width: 14px; height: 14px; accent-color: #3b82f6; }
.merge-inv-label { color: #334155; }
.merge-cancel-opt {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
  color: #475569;
  margin-top: 8px;
  cursor: pointer;
}
.how-calculated-link {
  display: inline-block;
  font-size: 12px;
  color: #3b82f6;
  margin-top: 6px;
  text-decoration: none;
}
.how-calculated-link:hover { text-decoration: underline; }

/* ── Form Settings Grid ─────────────────────────────────────────── */
.form-section {
  padding: 24px;
}
.form-settings-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 32px;
}
.settings-col {
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 5px;
}
.form-label {
  font-size: 12.5px;
  font-weight: 600;
  color: #475569;
}
.required-label::before {
  content: '* ';
  color: #ef4444;
}
.form-ctrl {
  width: 100%;
  padding: 8px 10px;
  font-size: 13px;
  border: 1px solid #cbd5e1;
  border-radius: 4px;
  background: #fff;
  color: #1e293b;
  box-sizing: border-box;
  font-family: inherit;
  transition: border-color 0.15s;
}
.form-ctrl:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59,130,246,.1);
}
.mt-2 { margin-top: 8px; }
.mt-3 { margin-top: 12px; }
.inline-pair {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

/* ── Addresses ──────────────────────────────────────────────────── */
.address-pair {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  padding: 12px;
  background: #f8fafc;
  font-size: 12.5px;
  color: #475569;
}
.address-block-title {
  font-size: 11px;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: .04em;
  margin-bottom: 4px;
}
.address-block-content { line-height: 1.65; color: #334155; }
.no-addr-text { color: #94a3b8; }
.addr-override-toggle {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  color: #3b82f6;
  cursor: pointer;
  font-weight: 500;
  user-select: none;
}
.addr-override-toggle:hover { color: #2563eb; }
.addr-fields-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-top: 10px;
  padding: 16px;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  background: #f8fafc;
}
.addr-section-title {
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: .04em;
}

/* ── Payment Modes Chips ────────────────────────────────────────── */
.payment-modes-row {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}
.mode-chip {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12.5px;
  font-weight: 600;
  padding: 4px 12px;
  border-radius: 4px;
  border: 1px solid #cbd5e1;
  cursor: pointer;
  user-select: none;
  transition: all 0.12s;
  color: #475569;
  background: #f8fafc;
}
.mode-chip.active {
  background: #eff6ff;
  border-color: #93c5fd;
  color: #1d4ed8;
}

/* ── Invoice Number Display ─────────────────────────────────────── */
.inv-num-display {
  display: flex;
  align-items: center;
  border: 1px solid #cbd5e1;
  border-radius: 4px;
  overflow: hidden;
}
.inv-prefix {
  background: #f8fafc;
  border-right: 1px solid #cbd5e1;
  padding: 8px 12px;
  font-size: 13px;
  font-weight: 600;
  color: #64748b;
  white-space: nowrap;
}
.inv-num-suffix {
  border: none;
  border-radius: 0;
  box-shadow: none;
}
.inv-num-suffix:focus { box-shadow: none; border: none; }

/* ── Checkbox Row ───────────────────────────────────────────────── */
.checkbox-row {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
  color: #475569;
  cursor: pointer;
}
.checkbox-row input[type="checkbox"] {
  width: 14px;
  height: 14px;
  accent-color: #3b82f6;
}

/* ── Tags Input ─────────────────────────────────────────────────── */
.tags-input-wrap {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  border: 1px solid #cbd5e1;
  border-radius: 4px;
  padding: 5px 8px;
  background: #fff;
  min-height: 38px;
  align-items: center;
}
.tag-chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: #eff6ff;
  color: #1d4ed8;
  border-radius: 4px;
  padding: 2px 8px;
  font-size: 12px;
  font-weight: 600;
}
.tag-remove {
  background: none;
  border: none;
  cursor: pointer;
  color: #93c5fd;
  font-size: 14px;
  line-height: 1;
  padding: 0;
}
.tag-input {
  border: none;
  outline: none;
  font-size: 13px;
  font-family: inherit;
  color: #1e293b;
  min-width: 80px;
  background: transparent;
}

/* ── Divider ────────────────────────────────────────────────────── */
.section-divider {
  height: 1px;
  background: #e2e8f0;
  margin: 0 0;
}

/* ── Line Items ─────────────────────────────────────────────────── */
.line-items-section {
  padding: 20px 24px;
}
.line-items-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
}
.qty-mode-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
}
.qty-mode-label {
  font-size: 13px;
  font-weight: 600;
  color: #475569;
}
.qty-mode-group {
  display: flex;
  gap: 4px;
}
.qty-radio {
  padding: 4px 10px;
  font-size: 12.5px;
  font-weight: 500;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  cursor: pointer;
  color: #64748b;
  transition: all 0.12s;
  user-select: none;
}
.qty-radio.active {
  background: #3b82f6;
  border-color: #3b82f6;
  color: #fff;
}

/* Items Table */
.items-table-wrap {
  overflow-x: auto;
}
.items-tbl {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.items-tbl th {
  background: #f8fafc;
  padding: 8px 10px;
  font-size: 12px;
  font-weight: 600;
  color: #475569;
  border-bottom: 2px solid #e2e8f0;
  text-align: left;
  white-space: nowrap;
}
.items-tbl td {
  padding: 8px 6px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: top;
}
.th-item  { width: 250px; }
.th-desc  { width: 0; display: none; }
.td-desc  { display: none; }
.th-qty   { width: 80px; }
.th-rate  { width: 100px; }
.th-tax   { width: 130px; }
.th-amount{ width: 100px; text-align: right; }
.th-del   { width: 40px; }
.td-amount { text-align: right; font-weight: 600; color: #1e293b; padding-top: 12px; }
.td-del    { vertical-align: middle; }

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
.item-long-desc { margin-top: 4px; color: #64748b; }
.item-tax-select { appearance: auto; }
.del-item-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  padding: 6px;
  border-radius: 4px;
  display: flex;
  align-items: center;
}
.del-item-btn:hover { color: #ef4444; background: #fef2f2; }

.btn-add-row {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: 10px;
  background: none;
  border: 1px dashed #cbd5e1;
  border-radius: 4px;
  padding: 6px 14px;
  font-size: 12.5px;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  transition: all 0.12s;
}
.btn-add-row:hover { border-color: #3b82f6; color: #2563eb; }

/* Totals */
.totals-section {
  display: flex;
  justify-content: flex-end;
  margin-top: 16px;
  border-top: 1px solid #e2e8f0;
  padding-top: 16px;
}
.totals-tbl {
  min-width: 320px;
  border-collapse: collapse;
  font-size: 13px;
}
.totals-tbl td {
  padding: 5px 12px;
  color: #475569;
}
.total-lbl {
  font-weight: 500;
  text-align: right;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
}
.total-val { text-align: right; font-weight: 600; color: #1e293b; }
.total-discount { color: #dc2626; }
.total-row-final td { border-top: 2px solid #e2e8f0; padding-top: 10px; }
.total-final { font-size: 16px; color: #1e293b; }
.discount-input, .adjustment-input {
  width: 70px;
  padding: 3px 6px;
  font-size: 12.5px;
  border: 1px solid #e2e8f0;
  border-radius: 3px;
  text-align: right;
  font-family: inherit;
}

/* Footer Fields */
.footer-fields {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-top: 24px;
}

/* ── Save Bar ───────────────────────────────────────────────────── */
.form-save-bar {
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
  padding: 16px 24px;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
  border-bottom-left-radius: 4px;
  border-bottom-right-radius: 4px;
}
.btn-save-invoice {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: #1e293b;
  color: #fff;
  border: none;
  border-radius: 4px;
  padding: 9px 22px;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
  font-family: inherit;
}
.btn-save-invoice:hover { background: #0f172a; }
.btn-save-invoice:disabled { background: #94a3b8; cursor: not-allowed; }
.btn-cancel-invoice {
  display: inline-flex;
  align-items: center;
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  padding: 8px 18px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.12s;
}
.btn-cancel-invoice:hover { background: #e2e8f0; }
</style>
