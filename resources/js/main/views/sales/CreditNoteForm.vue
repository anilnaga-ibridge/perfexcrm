<template>
  <div class="invoice-form-page space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-2 text-xs text-slate-400">
        <router-link :to="{ name: 'admin.credit-notes' }" class="hover:text-indigo-600 font-semibold">Credit Notes</router-link>
        <span>/</span>
        <span class="text-slate-600 font-semibold">{{ isEdit ? 'Edit Credit Note' : 'New Credit Note' }}</span>
      </div>
      <router-link :to="{ name: 'admin.credit-notes' }" class="text-xs text-indigo-600 hover:underline">
        &larr; Back to List
      </router-link>
    </div>

    <!-- Form wrapper -->
    <div class="form-container">
      <div class="form-main">
        <div class="card p-6 space-y-6">
          <!-- Customer Selection & Basic details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left panel -->
            <div class="space-y-4">
              <div class="form-group">
                <label class="form-label font-bold text-slate-700 mb-1"><span class="text-rose-500">*</span> Customer</label>
                <select class="form-ctrl text-xs h-9 bg-white" v-model="form.client_id" @change="onClientChange" :disabled="isEdit">
                  <option value="" disabled>Select client...</option>
                  <option v-for="c in clientOptions" :key="c.id" :value="c.id">{{ c.company }}</option>
                </select>
              </div>

              <!-- Address panel -->
              <div class="address-pane bg-slate-50/50 p-4 border rounded-md text-xs space-y-2">
                <div class="flex justify-between items-center mb-1">
                  <span class="font-bold text-slate-700">Bill To</span>
                  <button type="button" class="btn-copy-address" @click="showAddressOverrides = !showAddressOverrides">
                    {{ showAddressOverrides ? 'Hide details' : 'Edit Bill To / Ship To details' }}
                  </button>
                </div>
                <p v-if="!showAddressOverrides" class="text-slate-600 leading-relaxed font-medium">
                  {{ form.billing_street || '--' }}<br />
                  {{ form.billing_city ? form.billing_city + ', ' : '' }}{{ form.billing_state || '' }}<br />
                  {{ form.billing_country || '' }}{{ form.billing_zip ? ', ' + form.billing_zip : '' }}
                </p>

                <div v-if="showAddressOverrides" class="grid grid-cols-2 gap-3 pt-2">
                  <div class="col-span-2">
                    <label class="text-[10px] font-bold text-slate-500 block mb-0.5">Street</label>
                    <textarea class="form-ctrl text-xs p-1.5" rows="2" v-model="form.billing_street"></textarea>
                  </div>
                  <div>
                    <label class="text-[10px] font-bold text-slate-500 block mb-0.5">City</label>
                    <input type="text" class="form-ctrl text-xs h-8" v-model="form.billing_city" />
                  </div>
                  <div>
                    <label class="text-[10px] font-bold text-slate-500 block mb-0.5">State</label>
                    <input type="text" class="form-ctrl text-xs h-8" v-model="form.billing_state" />
                  </div>
                  <div>
                    <label class="text-[10px] font-bold text-slate-500 block mb-0.5">Zip</label>
                    <input type="text" class="form-ctrl text-xs h-8" v-model="form.billing_zip" />
                  </div>
                  <div>
                    <label class="text-[10px] font-bold text-slate-500 block mb-0.5">Country</label>
                    <input type="text" class="form-ctrl text-xs h-8" v-model="form.billing_country" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Right panel -->
            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                  <label class="form-label font-bold text-slate-700 mb-1"><span class="text-rose-500">*</span> Credit Note Date</label>
                  <input type="date" class="form-ctrl text-xs h-9" v-model="form.date" />
                </div>
                <div class="form-group">
                  <label class="form-label font-bold text-slate-700 mb-1"><span class="text-rose-500">*</span> Credit Note #</label>
                  <div class="flex items-center">
                    <span class="prefix bg-slate-100 border-y border-l px-3 py-2 text-xs rounded-l font-bold text-slate-500 h-9 flex items-center">CN-</span>
                    <input type="text" class="form-ctrl text-xs h-9 rounded-l-none" v-model="form.number" placeholder="000001" :disabled="isEdit" />
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                  <label class="form-label font-bold text-slate-700 mb-1"><span class="text-rose-500">*</span> Currency</label>
                  <select class="form-ctrl text-xs h-9 bg-white" v-model="form.currency">
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label font-bold text-slate-700 mb-1">Discount Type</label>
                  <select class="form-ctrl text-xs h-9 bg-white" v-model="form.discount_type">
                    <option value="no_discount">No discount</option>
                    <option value="before_tax">Before Tax</option>
                    <option value="after_tax">After Tax</option>
                  </select>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                  <label class="form-label font-bold text-slate-700 mb-1">Reference #</label>
                  <input type="text" class="form-ctrl text-xs h-9" v-model="form.reference" placeholder="PO Number / Ref" />
                </div>
                <div class="form-group">
                  <label class="form-label font-bold text-slate-700 mb-1">Status</label>
                  <select class="form-ctrl text-xs h-9 bg-white" v-model="form.status">
                    <option value="Open">Open</option>
                    <option value="Closed">Closed</option>
                    <option value="Void">Void</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label font-bold text-slate-700 mb-1">Admin Note</label>
                <textarea class="form-ctrl text-xs p-2.5 min-h-[48px]" rows="2" v-model="form.admin_note" placeholder="Admin note (internal)..."></textarea>
              </div>
            </div>
          </div>

          <!-- Add Item Row & Type Selector -->
          <div class="border-t pt-6 space-y-4">
            <div class="flex justify-between items-center pb-2 border-b">
              <div class="flex items-center space-x-2 w-72">
                <a-select 
                  placeholder="Add Item" 
                  style="width: 100%" 
                  size="large"
                  allow-clear
                  show-search
                  @change="handlePredefinedItemSelect"
                >
                  <a-select-option v-for="item in predefinedItems" :key="item.name" :value="item.name">
                    {{ item.name }} ({{ formatCurrency(item.rate) }})
                  </a-select-option>
                </a-select>
                <button type="button" class="btn-add-row-plus flex items-center justify-center font-bold text-base cursor-pointer" @click="addPredefinedItem">+</button>
              </div>

              <div class="flex items-center gap-4 text-xs font-semibold text-slate-600">
                <span>Show quantity as:</span>
                <label class="flex items-center gap-1 cursor-pointer"><input type="radio" value="Qty" v-model="form.qty_show" /> Qty</label>
                <label class="flex items-center gap-1 cursor-pointer"><input type="radio" value="Hours" v-model="form.qty_show" /> Hours</label>
                <label class="flex items-center gap-1 cursor-pointer"><input type="radio" value="Qty/Hours" v-model="form.qty_show" /> Qty/Hours</label>
              </div>
            </div>

            <!-- Items Table -->
            <div class="items-table-wrap">
              <table class="items-tbl">
                <thead>
                  <tr>
                    <th class="th-item">Item</th>
                    <th class="th-desc">Description</th>
                    <th class="th-qty text-center">Qty</th>
                    <th class="th-rate text-right">Rate</th>
                    <th class="th-tax">Tax</th>
                    <th class="th-amount text-right">Amount</th>
                    <th class="th-del"></th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Input Row to add items -->
                  <tr class="bg-slate-50/50">
                    <td class="p-2">
                      <textarea class="item-ctrl text-xs min-h-[58px]" rows="2" v-model="newItem.description" placeholder="Description"></textarea>
                    </td>
                    <td class="p-2">
                      <textarea class="item-ctrl text-xs min-h-[58px]" rows="2" v-model="newItem.long_description" placeholder="Long description"></textarea>
                    </td>
                    <td class="p-2">
                      <input type="number" class="item-ctrl text-xs text-center w-20" v-model="newItem.qty" />
                      <input type="text" class="item-ctrl text-[10px] text-center mt-1 placeholder-slate-300 w-20" v-model="newItem.unit" placeholder="Unit" />
                    </td>
                    <td class="p-2">
                      <input type="number" class="item-ctrl text-xs text-right w-24" v-model="newItem.rate" placeholder="0.00" />
                    </td>
                    <td class="p-2">
                      <select class="item-ctrl text-xs bg-white h-8" v-model="newItem.tax_rate">
                        <option :value="null">No Tax</option>
                        <option :value="5">5.00%</option>
                        <option :value="10">10.00%</option>
                        <option :value="18">18.00%</option>
                      </select>
                    </td>
                    <td class="p-2 text-right font-semibold text-slate-400">
                      {{ formatCurrency((newItem.qty || 1) * (newItem.rate || 0)) }}
                    </td>
                    <td class="p-2 text-center">
                      <button type="button" class="add-check-btn bg-slate-800 hover:bg-slate-900 text-white rounded flex items-center justify-center w-8 h-8" @click="addItem">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="12" height="12"><polyline points="20 6 9 17 4 12"/></svg>
                      </button>
                    </td>
                  </tr>

                  <!-- Render items list -->
                  <tr v-for="(item, idx) in form.items" :key="idx">
                    <td class="p-2 align-top">
                      <div class="font-bold text-slate-800">{{ item.description }}</div>
                    </td>
                    <td class="p-2 align-top text-slate-500">
                      {{ item.long_description || '—' }}
                    </td>
                    <td class="p-2 align-top text-center font-semibold">
                      {{ item.qty }} <span class="text-[10px] text-slate-400 font-normal">({{ item.unit }})</span>
                    </td>
                    <td class="p-2 align-top text-right">
                      {{ formatCurrency(item.rate) }}
                    </td>
                    <td class="p-2 align-top text-slate-500">
                      {{ item.tax_rate > 0 ? item.tax_rate + '%' : 'None' }}
                    </td>
                    <td class="p-2 align-top text-right font-bold text-slate-700">
                      {{ formatCurrency(itemAmount(item)) }}
                    </td>
                    <td class="p-2 align-top text-center">
                      <button type="button" class="del-item-btn" @click="removeItem(idx)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Subtotal / Calculations Grid -->
          <div class="totals-section border-t pt-6 flex justify-end">
            <div class="w-80 space-y-2 text-xs">
              <div class="flex justify-between py-1 border-b border-slate-100">
                <span class="text-slate-500 font-semibold">Sub Total :</span>
                <span class="font-bold text-slate-800">{{ formatCurrency(subtotal) }}</span>
              </div>
              <div class="flex justify-between items-center py-1 border-b border-slate-100">
                <span class="text-slate-500 font-semibold">Discount (%)</span>
                <input type="number" class="discount-input" v-model.number="form.discount_percent" min="0" max="100" step="0.01" />
                <span class="font-bold text-rose-500">-{{ formatCurrency(discountVal) }}</span>
              </div>
              <div class="flex justify-between items-center py-1 border-b border-slate-100">
                <span class="text-slate-500 font-semibold">Adjustment</span>
                <input type="number" class="adjustment-input" v-model.number="form.adjustment" step="0.01" />
                <span class="font-bold text-slate-800">{{ formatCurrency(form.adjustment || 0) }}</span>
              </div>
              <div class="flex justify-between py-1 text-sm font-bold text-slate-800 pt-2">
                <span>Total :</span>
                <span class="text-lg text-slate-800 font-extrabold">{{ formatCurrency(totalAmount) }}</span>
              </div>
            </div>
          </div>

          <!-- Notes / Terms fields -->
          <div class="footer-fields border-t pt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="form-group">
              <label class="form-label font-bold text-slate-700 mb-1">Client Note</label>
              <textarea class="form-ctrl text-xs p-2.5" rows="3" v-model="form.client_note" placeholder="Note displayed to the client..."></textarea>
            </div>
            <div class="form-group">
              <label class="form-label font-bold text-slate-700 mb-1">Terms &amp; Conditions</label>
              <textarea class="form-ctrl text-xs p-2.5" rows="3" v-model="form.terms_conditions" placeholder="Credit note terms and conditions..."></textarea>
            </div>
          </div>

          <!-- Save and Cancel Footer -->
          <div class="form-save-bar">
            <button type="button" class="btn-save-invoice" :disabled="saving" @click="submitCreditNote">
              <svg v-if="saving" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="animate-spin"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
              {{ saving ? 'Saving...' : 'Save Credit Note' }}
            </button>
            <router-link :to="cancelLink" class="btn-cancel-invoice">Cancel</router-link>
          </div>
        </div>
      </div>
    </div>
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
    const showAddressOverrides = ref(false);
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
      // reset newItem fields
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
          
          // Deserialize items list stored inside terms field
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
          // Auto-generate next credit note number suffix
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
      showAddressOverrides,
      predefinedItems,
      form,
      newItem,
      cancelLink,
      formatCurrency,
      itemAmount,
      subtotal,
      discountVal,
      totalAmount,
      handlePredefinedItemSelect,
      addPredefinedItem,
      addItem,
      removeItem,
      onClientChange,
      submitCreditNote
    };
  }
});
</script>

<style scoped>
.invoice-form-page {
  font-family: 'Inter', -apple-system, sans-serif;
  color: #334155;
}

.form-container {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-label {
  font-size: 12.5px;
  color: #475569;
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

.address-pane {
  background: #f8fafc;
}

.btn-copy-address {
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #bfdbfe;
  padding: 4px 10px;
  font-size: 11px;
  font-weight: 600;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.1s;
}
.btn-copy-address:hover {
  background: #dbeafe;
}

.prefix {
  border-right: none;
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
  padding: 10px 12px;
  font-size: 12px;
  font-weight: 600;
  color: #475569;
  border-bottom: 2px solid #e2e8f0;
  text-align: left;
}
.items-tbl td {
  padding: 10px 12px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: top;
}
.th-item  { width: 250px; }
.th-desc  { width: auto; }
.th-qty   { width: 100px; }
.th-rate  { width: 120px; }
.th-tax   { width: 130px; }
.th-amount{ width: 110px; }
.th-del   { width: 40px; }

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

.btn-add-row-plus {
  background: #fff;
  border: 1px solid #cbd5e1;
  color: #475569;
  border-radius: 4px;
  width: 36px;
  height: 36px;
  font-size: 16px;
  cursor: pointer;
}
.btn-add-row-plus:hover {
  border-color: #3b82f6;
  color: #2563eb;
}

.del-item-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  padding: 6px;
  border-radius: 4px;
}
.del-item-btn:hover { color: #ef4444; background: #fef2f2; }

/* Totals */
.totals-section {
  display: flex;
  justify-content: flex-end;
}
.discount-input, .adjustment-input {
  width: 80px;
  padding: 3px 6px;
  font-size: 12.5px;
  border: 1px solid #e2e8f0;
  border-radius: 3px;
  text-align: right;
  font-family: inherit;
}

/* Save Bar */
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
