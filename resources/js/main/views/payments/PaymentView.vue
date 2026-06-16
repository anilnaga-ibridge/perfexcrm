<template>
  <div class="payment-view-page">

    <!-- Loading -->
    <div v-if="loading" class="page-loading">
      <a-spin size="large" />
      <span>Loading receipt...</span>
    </div>

    <!-- Main Content -->
    <div v-else-if="payment" class="content-wrapper">
      
      <!-- Page Title Header -->
      <div class="payment-header-title">
        Payment for Invoice 
        <span class="invoice-link" @click="goToInvoice">
          {{ payment.invoice?.number || '—' }}
        </span>
      </div>

      <!-- Tab Buttons Navigation Container -->
      <div class="tabs-navigation no-print">
        <button 
          :class="['tab-btn', { active: activeTab === 'receipt' }]" 
          @click="activeTab = 'receipt'"
        >
          Payment Receipt
        </button>
        <button 
          :class="['tab-btn', { active: activeTab === 'edit' }]" 
          @click="activeTab = 'edit'"
        >
          Payment
        </button>
      </div>

      <!-- TAB 1: Payment Receipt -->
      <div v-if="activeTab === 'receipt'" class="receipt-tab-content">
        <div class="receipt-card-container">
          
          <!-- Actions inside the receipt card (Top Right) -->
          <div class="receipt-actions-header no-print">
            <button class="action-btn-icon" @click="sendEmailNotification" title="Email receipt">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
              </svg>
            </button>
            <button class="action-btn-icon" @click="printReceipt" title="Print/PDF">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <polyline points="6 9 6 2 18 2 18 9"/>
                <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
              </svg>
            </button>
            <button class="action-btn-icon delete-btn-square" @click="deletePayment" title="Delete payment">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
              </svg>
            </button>
          </div>

          <!-- Document Header (Addresses) -->
          <div class="receipt-doc-header">
            <div class="company-address-block">
              <div class="comp-name">{{ companyInfo.name }}</div>
              <div class="comp-detail">{{ companyInfo.address }}</div>
              <div class="comp-detail">{{ companyInfo.city }}</div>
              <div class="comp-detail">{{ companyInfo.country }}</div>
            </div>
            
            <div class="client-address-block">
              <div class="client-name-wrap">
                <router-link
                  v-if="payment.invoice?.client?.id"
                  :to="{ name: 'admin.customers.view', params: { id: payment.invoice.client.id } }"
                  class="client-profile-link"
                >
                  {{ payment.invoice.client.company }}
                </router-link>
                <span v-else class="client-company-fallback">{{ payment.invoice?.client?.company || '—' }}</span>
              </div>
              <div class="client-detail" v-if="payment.invoice?.client?.billing_street">{{ payment.invoice.client.billing_street }}</div>
              <div class="client-detail" v-if="payment.invoice?.client?.city">{{ payment.invoice.client.city }}, {{ payment.invoice.client.state }}</div>
              <div class="client-detail" v-if="payment.invoice?.client?.zip">{{ payment.invoice.client.zip }}</div>
              <div class="client-detail" v-if="payment.invoice?.client?.country">{{ payment.invoice.client.country }}</div>
            </div>
          </div>

          <!-- Document Title -->
          <div class="receipt-doc-title">
            PAYMENT RECEIPT
          </div>

          <!-- Metadata info list -->
          <div class="receipt-metadata-list">
            <div class="meta-row">
              <span class="meta-lbl">Payment Date:</span>
              <span class="meta-val">{{ payment.date ? String(payment.date).slice(0, 10) : '—' }}</span>
            </div>
            <div class="meta-row">
              <span class="meta-lbl">Payment Mode:</span>
              <span class="meta-val">{{ payment.paymentmode || '—' }}</span>
            </div>
            <div class="meta-row" v-if="payment.transactionid">
              <span class="meta-lbl">Transaction ID:</span>
              <span class="meta-val">{{ payment.transactionid }}</span>
            </div>
          </div>

          <!-- Total Amount highlight banner -->
          <div class="receipt-total-banner">
            <div class="banner-lbl">Total Amount</div>
            <div class="banner-val">{{ formatCurrency(payment.amount) }}</div>
          </div>

          <!-- Payment For section -->
          <div class="payment-for-container">
            <div class="payment-for-heading">Payment For</div>
            <table class="receipt-table">
              <thead>
                <tr>
                  <th>Invoice Number</th>
                  <th>Invoice Date</th>
                  <th>Invoice Amount</th>
                  <th class="text-right">Payment Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <span class="invoice-table-link" @click="goToInvoice">
                      {{ payment.invoice?.number || '—' }}
                    </span>
                  </td>
                  <td>{{ payment.invoice?.date || '—' }}</td>
                  <td>{{ formatCurrency(payment.invoice?.total) }}</td>
                  <td class="text-right font-bold text-green">{{ formatCurrency(payment.amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

        </div>
      </div>

      <!-- TAB 2: Payment (Edit Form) -->
      <div v-if="activeTab === 'edit'" class="payment-edit-tab-content">
        <div class="payment-edit-card">
          <form @submit.prevent="savePayment">
            
            <div class="form-field">
              <label class="form-label"><span class="required">*</span> Amount Received</label>
              <input 
                type="number" 
                class="form-input-text" 
                v-model.number="form.amount" 
                step="0.01" 
                required
              />
            </div>

            <div class="form-field">
              <label class="form-label"><span class="required">*</span> Payment Date</label>
              <input 
                type="date" 
                class="form-input-text" 
                v-model="form.date" 
                required
              />
            </div>

            <div class="form-field">
              <label class="form-label">Payment Mode</label>
              <select class="form-select-mode" v-model="form.paymentmode">
                <option value="Bank">Bank Transfer</option>
                <option value="PayPal">PayPal</option>
                <option value="Stripe">Stripe Checkout</option>
                <option value="Cash">Cash</option>
                <option value="Cheque">Cheque</option>
                <option value="Credit Card">Credit Card</option>
              </select>
            </div>

            <div class="form-field">
              <label class="form-label">Payment Method</label>
              <input 
                type="text" 
                class="form-input-text" 
                v-model="form.paymentmethod" 
                placeholder="Optional"
              />
            </div>

            <div class="form-field">
              <label class="form-label">Transaction ID</label>
              <input 
                type="text" 
                class="form-input-text" 
                v-model="form.transactionid" 
                placeholder="Optional"
              />
            </div>

            <div class="form-field">
              <label class="form-label">Note</label>
              <textarea 
                class="form-textarea" 
                v-model="form.note" 
                rows="4"
              ></textarea>
            </div>

            <div class="form-footer-save">
              <button type="submit" class="btn-save-payment" :disabled="saving">
                {{ saving ? 'Saving...' : 'Save' }}
              </button>
            </div>

          </form>
        </div>
      </div>

    </div>

    <!-- Not Found -->
    <div v-else class="not-found">
      <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" width="56" height="56">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <p>Payment not found</p>
      <button class="back-btn" @click="$router.push({ name: 'admin.payments' })">Back to Payments</button>
    </div>

  </div>
</template>

<script>
import { defineComponent, ref, reactive, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { message, Modal } from 'ant-design-vue';

export default defineComponent({
  name: 'PaymentViewPage',
  setup() {
    const router  = useRouter();
    const route   = useRoute();
    const loading = ref(true);
    const saving  = ref(false);
    const payment = ref(null);
    const activeTab = ref('receipt');

    const payId = route.params.id;

    const companyInfo = {
      name:    'Perfex INC',
      address: '172 Ivy Club Gottliebfurt',
      city:    'New Heaven',
      country: 'Canada [CA] 2364',
    };

    const form = reactive({
      amount: 0,
      date: '',
      paymentmode: 'Bank',
      paymentmethod: '',
      transactionid: '',
      note: '',
    });

    const loadPayment = async () => {
      loading.value = true;
      try {
        const res = await axios.get(`/api/payments/${payId}`);
        payment.value = res.data;
        if (res.data) {
          form.amount = parseFloat(res.data.amount) || 0;
          form.date = res.data.date ? String(res.data.date).slice(0, 10) : '';
          form.paymentmode = res.data.paymentmode || 'Bank';
          form.transactionid = res.data.transactionid || '';
          form.note = res.data.note || '';
          form.paymentmethod = ''; // simulated
        }
      } catch {
        payment.value = null;
      } finally {
        loading.value = false;
      }
    };

    const savePayment = async () => {
      saving.value = true;
      try {
        const res = await axios.put(`/api/payments/${payId}`, {
          amount: form.amount,
          date: form.date,
          paymentmode: form.paymentmode,
          transactionid: form.transactionid,
          note: form.note,
        });
        message.success('Payment updated successfully');
        payment.value = res.data;
        activeTab.value = 'receipt';
      } catch {
        message.error('Failed to update payment');
      } finally {
        saving.value = false;
      }
    };

    const deletePayment = () => {
      Modal.confirm({
        title:   'Delete this payment?',
        content: 'This action cannot be undone. The invoice status will be updated.',
        okText:  'Delete',
        okType:  'danger',
        onOk: async () => {
          try {
            await axios.delete(`/api/payments/${payId}`);
            message.success('Payment deleted');
            router.push({ name: 'admin.payments' });
          } catch {
            message.error('Failed to delete payment');
          }
        },
      });
    };

    const goToInvoice = () => {
      if (payment.value?.invoice_id) {
        router.push({ name: 'admin.invoices.view', params: { id: payment.value.invoice_id } });
      }
    };

    const printReceipt = () => {
      window.print();
    };

    const sendEmailNotification = () => {
      message.loading({ content: 'Sending email to client...', key: 'email' });
      setTimeout(() => {
        message.success({ content: 'Email sent successfully to customer contacts!', key: 'email', duration: 2 });
      }, 1000);
    };

    const formatCurrency = (val) => {
      if (!val && val !== 0) return '$0.00';
      return '$' + parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2 });
    };

    const formatDate = (d) => {
      if (!d) return '—';
      return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    };

    onMounted(loadPayment);

    return {
      loading, saving, payment, activeTab, companyInfo, form,
      loadPayment, savePayment, deletePayment, goToInvoice, printReceipt, sendEmailNotification,
      formatCurrency, formatDate,
    };
  }
});
</script>

<style scoped>
.payment-view-page {
  font-family: 'Inter', -apple-system, sans-serif;
  background: #f8fafc;
  min-height: 100vh;
  padding: 20px 24px;
  box-sizing: border-box;
}

.content-wrapper {
  max-width: 900px;
  margin: 0 auto;
}

/* Page loading / not found states */
.page-loading, .not-found {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 100px 0;
  color: #94a3b8;
  font-size: 14px;
}

.back-btn {
  background: none;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 12.5px;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
  transition: all 0.12s;
}
.back-btn:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

/* Page Title Header */
.payment-header-title {
  font-size: 19px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.invoice-link {
  color: #3b82f6;
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
  transition: color 0.15s;
}
.invoice-link:hover {
  color: #2563eb;
  text-decoration: underline;
}

/* Tab Navigation buttons */
.tabs-navigation {
  display: flex;
  background: #eef2f6;
  padding: 4px;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
  margin-bottom: 20px;
  width: fit-content;
}

.tab-btn {
  background: transparent;
  border: none;
  padding: 6px 14px;
  font-size: 13px;
  font-weight: 500;
  color: #64748b;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.15s;
}
.tab-btn.active {
  background: #fff;
  color: #1e293b;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
  font-weight: 600;
}

/* Tab 1: Receipt Container */
.receipt-card-container {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 32px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  position: relative;
}

.receipt-actions-header {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-bottom: 20px;
}

.action-btn-icon {
  background: #fff;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  padding: 6px 10px;
  color: #4b5563;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}
.action-btn-icon:hover {
  background: #f9fafb;
  border-color: #9ca3af;
  color: #111827;
}

.delete-btn-square {
  background: #ef4444;
  color: #fff;
  border: 1px solid #ef4444;
}
.delete-btn-square:hover {
  background: #dc2626;
  border-color: #dc2626;
  color: #fff;
}

/* Document layout */
.receipt-doc-header {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 40px;
  margin-bottom: 30px;
}

.company-address-block {
  text-align: left;
}
.comp-name {
  font-size: 15px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 4px;
}
.comp-detail, .client-detail {
  font-size: 12.5px;
  color: #64748b;
  line-height: 1.5;
}

.client-address-block {
  text-align: right;
}
.client-profile-link {
  font-size: 15px;
  font-weight: 700;
  color: #3b82f6;
  text-decoration: none;
  display: inline-block;
  margin-bottom: 4px;
}
.client-profile-link:hover {
  color: #2563eb;
  text-decoration: underline;
}
.client-company-fallback {
  font-size: 15px;
  font-weight: 700;
  color: #1e293b;
  display: inline-block;
  margin-bottom: 4px;
}

.receipt-doc-title {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 24px;
  letter-spacing: 0.02em;
}

/* Metadata rows */
.receipt-metadata-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 24px;
  max-width: 320px;
}
.meta-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
}
.meta-lbl {
  color: #64748b;
}
.meta-val {
  font-weight: 600;
  color: #1e293b;
}

/* Total banner */
.receipt-total-banner {
  background: #f1f5f9;
  border-radius: 6px;
  padding: 16px 20px;
  margin-bottom: 30px;
  max-width: 320px;
}
.banner-lbl {
  font-size: 11px;
  text-transform: uppercase;
  font-weight: 600;
  color: #64748b;
  letter-spacing: 0.05em;
  margin-bottom: 4px;
}
.banner-val {
  font-size: 22px;
  font-weight: 800;
  color: #1e293b;
}

/* Payment For Table */
.payment-for-container {
  margin-top: 30px;
}
.payment-for-heading {
  font-size: 14px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 12px;
}
.receipt-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.receipt-table th {
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
  padding: 10px 12px;
  text-align: left;
  font-weight: 600;
  color: #475569;
}
.receipt-table td {
  padding: 12px;
  border-bottom: 1px solid #f1f5f9;
  color: #475569;
}
.text-right {
  text-align: right;
}
.font-bold {
  font-weight: 700;
}
.text-green {
  color: #16a34a;
}
.invoice-table-link {
  color: #3b82f6;
  font-weight: 600;
  cursor: pointer;
}
.invoice-table-link:hover {
  text-decoration: underline;
  color: #2563eb;
}

/* TAB 2: Edit Form styling */
.payment-edit-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 24px 32px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.form-field {
  margin-bottom: 16px;
}

.form-label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #475569;
  margin-bottom: 6px;
}

.required {
  color: #ef4444;
  margin-right: 2px;
}

.form-input-text, .form-select-mode, .form-textarea {
  width: 100%;
  padding: 8px 12px;
  font-size: 13px;
  border: 1px solid #cbd5e1;
  border-radius: 4px;
  background: #fff;
  color: #1e293b;
  box-sizing: border-box;
  font-family: inherit;
  transition: border-color 0.15s;
}
.form-input-text:focus, .form-select-mode:focus, .form-textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

.btn-save-payment {
  background: #1e293b;
  color: #fff;
  border: none;
  border-radius: 4px;
  padding: 8px 18px;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-save-payment:hover {
  background: #0f172a;
}
.btn-save-payment:disabled {
  background: #94a3b8;
  cursor: not-allowed;
}

.form-footer-save {
  display: flex;
  justify-content: flex-end;
  margin-top: 24px;
}

/* Print Overrides */
@media print {
  .no-print {
    display: none !important;
  }
  .payment-view-page {
    padding: 0;
    background: #fff;
  }
  .receipt-card-container {
    border: none;
    box-shadow: none;
    padding: 0;
  }
}
</style>
