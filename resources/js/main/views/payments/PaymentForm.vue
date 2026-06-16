<template>
  <div class="payment-form-page">

    <!-- ── Header ── -->
    <div class="page-header">
      <div class="header-left">
        <button class="back-btn" @click="$router.push({ name: 'admin.payments' })">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="15 18 9 12 15 6"/></svg>
          Payments
        </button>
        <div class="title-group">
          <h1 class="page-title">{{ isEdit ? 'Edit Payment' : 'Record New Payment' }}</h1>
          <span class="page-subtitle">{{ isEdit ? `Editing PAY-${payId}` : 'Record a payment against an invoice' }}</span>
        </div>
      </div>
    </div>

    <!-- ── Loading ── -->
    <div v-if="pageLoading" class="page-loading">
      <a-spin size="large" />
      <span>Loading...</span>
    </div>

    <!-- ── Form Card ── -->
    <div v-else class="form-card">
      <a-form layout="vertical" :model="form" @finish="submit">

        <div class="form-two-col">

          <!-- Left Column -->
          <div class="form-col">

            <!-- Invoice selector (create only) -->
            <a-form-item v-if="!isEdit" label="Invoice" name="invoice_id" :rules="[{required:true,message:'Please select an invoice'}]">
              <a-select
                v-model:value="form.invoice_id"
                placeholder="Select invoice..."
                style="width:100%"
                showSearch
                optionFilterProp="children"
                @change="onInvoiceChange"
              >
                <a-select-option v-for="inv in invoices" :key="inv.id" :value="inv.id">
                  {{ inv.number }} — {{ inv.client?.company || 'Unknown' }} ({{ formatCurrency(inv.total) }})
                </a-select-option>
              </a-select>
            </a-form-item>

            <!-- Invoice info card (edit mode) -->
            <div v-if="isEdit && invoiceInfo" class="invoice-info-card">
              <div class="info-row">
                <span class="info-label">Invoice</span>
                <span class="info-val inv-number">{{ invoiceInfo.number }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Client</span>
                <span class="info-val">
                  <router-link
                    v-if="invoiceInfo.client?.id"
                    :to="{ name: 'admin.customers.view', params: { id: invoiceInfo.client.id } }"
                    class="client-link"
                  >
                    {{ invoiceInfo.client.company }}
                  </router-link>
                  <span v-else>{{ invoiceInfo.client?.company || '—' }}</span>
                </span>
              </div>
              <div class="info-row">
                <span class="info-label">Invoice Total</span>
                <span class="info-val amount-val">{{ formatCurrency(invoiceInfo.total) }}</span>
              </div>
            </div>

            <!-- Selected invoice preview (create mode) -->
            <div v-if="!isEdit && selectedInvoice" class="invoice-info-card">
              <div class="info-row">
                <span class="info-label">Client</span>
                <span class="info-val">
                  <router-link
                    v-if="selectedInvoice.client?.id"
                    :to="{ name: 'admin.customers.view', params: { id: selectedInvoice.client.id } }"
                    class="client-link"
                  >
                    {{ selectedInvoice.client.company }}
                  </router-link>
                  <span v-else>{{ selectedInvoice.client?.company || '—' }}</span>
                </span>
              </div>
              <div class="info-row">
                <span class="info-label">Invoice Total</span>
                <span class="info-val amount-val">{{ formatCurrency(selectedInvoice.total) }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">Invoice Date</span>
                <span class="info-val">{{ formatDate(selectedInvoice.date) }}</span>
              </div>
            </div>

            <a-form-item label="Amount ($)" name="amount" :rules="[{required:true,message:'Enter payment amount'},{type:'number',min:0.01,message:'Must be > 0'}]">
              <a-input-number
                v-model:value="form.amount"
                :min="0.01"
                style="width:100%"
                :disabled="isEdit"
                placeholder="0.00"
                :precision="2"
              />
              <div v-if="isEdit" class="field-note">Amount cannot be changed. Delete and re-record to adjust.</div>
            </a-form-item>

            <a-form-item label="Leave a Note">
              <a-textarea v-model:value="form.note" :rows="4" placeholder="Optional internal note..." />
            </a-form-item>
          </div>

          <!-- Right Column -->
          <div class="form-col">
            <a-form-item label="Payment Date" name="date" :rules="[{required:true,message:'Select payment date'}]">
              <a-date-picker v-model:value="form.date" style="width:100%" value-format="YYYY-MM-DD" />
            </a-form-item>

            <a-form-item label="Payment Mode" name="paymentmode" :rules="[{required:true,message:'Select a payment mode'}]">
              <a-select v-model:value="form.paymentmode" style="width:100%">
                <a-select-option value="Bank">
                  <span class="mode-option">
                    <span class="mode-dot mode-bank"></span>
                    Bank Transfer
                  </span>
                </a-select-option>
                <a-select-option value="PayPal">
                  <span class="mode-option">
                    <span class="mode-dot mode-paypal"></span>
                    PayPal
                  </span>
                </a-select-option>
                <a-select-option value="Stripe">
                  <span class="mode-option">
                    <span class="mode-dot mode-stripe"></span>
                    Stripe Checkout
                  </span>
                </a-select-option>
                <a-select-option value="Cash">
                  <span class="mode-option">
                    <span class="mode-dot mode-cash"></span>
                    Cash
                  </span>
                </a-select-option>
                <a-select-option value="Cheque">
                  <span class="mode-option">
                    <span class="mode-dot mode-cheque"></span>
                    Cheque
                  </span>
                </a-select-option>
                <a-select-option value="Credit Card">
                  <span class="mode-option">
                    <span class="mode-dot mode-card"></span>
                    Credit Card
                  </span>
                </a-select-option>
              </a-select>
            </a-form-item>

            <a-form-item label="Transaction ID">
              <a-input v-model:value="form.transactionid" placeholder="txn_xxxxxxxxxxxxxxxx (optional)" />
            </a-form-item>

            <!-- Summary box -->
            <div class="summary-box">
              <div class="sum-row">
                <span>Payment Mode</span>
                <span class="sum-val">
                  <span class="mode-badge" :class="'badge-' + (form.paymentmode||'').toLowerCase().replace(' ','')">{{ form.paymentmode || '—' }}</span>
                </span>
              </div>
              <div class="sum-row">
                <span>Payment Date</span>
                <span class="sum-val">{{ form.date ? formatDate(form.date) : '—' }}</span>
              </div>
              <div class="sum-divider"></div>
              <div class="sum-row total-row">
                <span>Total Amount</span>
                <span class="sum-total">{{ formatCurrency(form.amount || 0) }}</span>
              </div>
            </div>

            <div class="email-notify">
              <label class="notify-label">
                <input type="checkbox" v-model="form.noEmail" />
                <span>Do not send payment confirmation email to client</span>
              </label>
            </div>
          </div>
        </div>

        <div class="form-footer">
          <a-button @click="$router.push({ name: 'admin.payments' })">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">
            {{ isEdit ? 'Save Changes' : 'Record Payment' }}
          </a-button>
        </div>
      </a-form>
    </div>

  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'PaymentFormPage',
  setup() {
    const router     = useRouter();
    const route      = useRoute();
    const pageLoading = ref(false);
    const saving      = ref(false);
    const invoices    = ref([]);
    const invoiceInfo = ref(null);

    const payId  = route.params.id || null;
    const isEdit = !!payId;

    const form = reactive({
      invoice_id:    null,
      amount:        null,
      paymentmode:   'Bank',
      date:          new Date().toISOString().split('T')[0],
      transactionid: '',
      note:          '',
      noEmail:       false,
    });

    const selectedInvoice = computed(() =>
      invoices.value.find(i => i.id === form.invoice_id) || null
    );

    const onInvoiceChange = () => {};

    const loadInvoices = async () => {
      try {
        const res = await axios.get('/api/invoices', { params: { per_page: 500 } });
        const all = res.data.invoices?.data || [];
        invoices.value = all.filter(inv =>
          ['unpaid', 'partially_paid', 'draft', 'overdue'].includes((inv.status || '').toLowerCase())
        );
      } catch {}
    };

    const loadPayment = async () => {
      pageLoading.value = true;
      try {
        const res = await axios.get(`/api/payments/${payId}`);
        const p = res.data;
        Object.assign(form, {
          invoice_id:    p.invoice_id,
          amount:        parseFloat(p.amount),
          paymentmode:   p.paymentmode || 'Bank',
          date:          p.date ? String(p.date).slice(0, 10) : null,
          transactionid: p.transactionid || '',
          note:          p.note || '',
        });
        invoiceInfo.value = p.invoice || null;
      } catch {
        message.error('Failed to load payment');
        router.push({ name: 'admin.payments' });
      } finally {
        pageLoading.value = false;
      }
    };

    const submit = async () => {
      saving.value = true;
      try {
        if (isEdit) {
          await axios.put(`/api/payments/${payId}`, {
            paymentmode:   form.paymentmode,
            date:          form.date,
            transactionid: form.transactionid,
            note:          form.note,
          });
          message.success('Payment updated successfully');
        } else {
          await axios.post('/api/payments', {
            invoice_id:    form.invoice_id,
            amount:        form.amount,
            paymentmode:   form.paymentmode,
            date:          form.date,
            transactionid: form.transactionid,
            note:          form.note,
          });
          message.success('Payment recorded successfully');
        }
        router.push({ name: 'admin.payments' });
      } catch (e) {
        const errs = e.response?.data?.errors;
        message.error(errs ? Object.values(errs).flat().join(', ') : 'Failed to save payment');
      } finally {
        saving.value = false;
      }
    };

    const formatCurrency = (val) => {
      if (!val && val !== 0) return '$0.00';
      return '$' + parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2 });
    };

    const formatDate = (d) => {
      if (!d) return '—';
      return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    };

    onMounted(() => {
      if (isEdit) {
        loadPayment();
      } else {
        loadInvoices();
      }
    });

    return {
      form, pageLoading, saving, invoices, invoiceInfo,
      isEdit, payId, selectedInvoice,
      onInvoiceChange, submit, formatCurrency, formatDate,
    };
  }
});
</script>

<style scoped>
.payment-form-page {
  font-family: 'Inter', -apple-system, sans-serif;
  background: #f8fafc;
  min-height: 100vh;
  padding: 20px 24px;
  box-sizing: border-box;
}

/* Header */
.page-header {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
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

.page-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 100px 0;
  color: #94a3b8;
  font-size: 14px;
}

/* Form card */
.form-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 28px 32px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  max-width: 1000px;
}
.form-two-col {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px 40px;
}
.form-col {}

/* Invoice info card */
.invoice-info-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 14px 16px;
  margin-bottom: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.info-row { display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
.info-label { color: #64748b; font-weight: 500; }
.info-val { color: #1e293b; font-weight: 600; }
.inv-number { color: #3b82f6; font-size: 14px; }
.client-link {
  color: #3b82f6;
  text-decoration: none;
  transition: color 0.12s;
}
.client-link:hover {
  color: #2563eb;
  text-decoration: underline;
}
.amount-val { color: #16a34a; font-size: 15px; font-weight: 700; }

.field-note {
  font-size: 11px;
  color: #94a3b8;
  margin-top: 4px;
  font-style: italic;
}

/* Mode options */
.mode-option { display: flex; align-items: center; gap: 7px; }
.mode-dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}
.mode-bank   { background: #3b82f6; }
.mode-paypal { background: #06b6d4; }
.mode-stripe { background: #8b5cf6; }
.mode-cash   { background: #22c55e; }
.mode-cheque { background: #f59e0b; }
.mode-card   { background: #ec4899; }

/* Summary box */
.summary-box {
  background: #1e293b;
  border-radius: 10px;
  padding: 18px 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 14px;
}
.sum-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13px;
  color: #94a3b8;
}
.sum-val { font-weight: 600; color: #e2e8f0; }
.sum-divider { height: 1px; background: rgba(255,255,255,0.1); margin: 2px 0; }
.total-row { margin-top: 2px; }
.sum-total { font-size: 22px; font-weight: 800; color: #4ade80; }

/* Mode badges */
.mode-badge {
  display: inline-block;
  padding: 2px 10px;
  border-radius: 999px;
  font-size: 11.5px;
  font-weight: 700;
  text-transform: capitalize;
}
.badge-bank        { background: #dbeafe; color: #2563eb; }
.badge-paypal      { background: #cffafe; color: #0891b2; }
.badge-stripe,
.badge-stripecheckout { background: #ede9fe; color: #7c3aed; }
.badge-cash        { background: #dcfce7; color: #16a34a; }
.badge-cheque      { background: #fef3c7; color: #d97706; }
.badge-creditcard  { background: #fce7f3; color: #be185d; }

/* Email notify */
.email-notify { margin-bottom: 8px; }
.notify-label {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  font-size: 12.5px;
  color: #64748b;
  cursor: pointer;
  line-height: 1.4;
}
.notify-label input { margin-top: 2px; flex-shrink: 0; accent-color: #1e293b; }

/* Footer */
.form-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 28px;
  padding-top: 20px;
  border-top: 1px solid #e2e8f0;
}

@media (max-width: 768px) {
  .form-two-col { grid-template-columns: 1fr; }
}
</style>
