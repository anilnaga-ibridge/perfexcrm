<template>
  <div class="invoice-view-page" :class="{ 'is-printing': isPrinting }">

    <!-- ── Sticky Top Action Bar (hidden when printing) ── -->
    <div class="top-bar no-print" v-if="invoice">
      <div class="top-bar-left">
        <button class="back-btn" @click="$router.push({ name: 'admin.invoices' })">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><polyline points="15 18 9 12 15 6"/></svg>
          Back to Invoices
        </button>
        <div class="inv-title-group">
          <span class="inv-heading">{{ invoice.number }}</span>
          <span class="status-badge" :class="'status-' + invoice.status">{{ statusLabel(invoice.status) }}</span>
          <span v-if="invoice.project" class="project-ref">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            {{ invoice.project.name }}
          </span>
        </div>
      </div>
      <div class="top-bar-right">
        <!-- Edit -->
        <button class="action-btn" @click="openEditDrawer" title="Edit Invoice">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit
        </button>

        <!-- Download PDF -->
        <button class="action-btn" @click="downloadPDF" title="Download as PDF">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Download PDF
        </button>

        <!-- Send Email -->
        <button class="action-btn" @click="sendEmail" title="Send to client">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          Send
        </button>

        <!-- More dropdown -->
        <a-dropdown :trigger="['click']">
          <button class="action-btn">
            More
            <svg viewBox="0 0 24 24" fill="currentColor" width="11" height="11"><path d="M7 10l5 5 5-5z"/></svg>
          </button>
          <template #overlay>
            <a-menu>
              <a-menu-item key="mark_paid" @click="markAsPaid" :disabled="invoice.status === 'paid'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="margin-right:6px;vertical-align:middle"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Mark as Paid
              </a-menu-item>
              <a-menu-item key="duplicate">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="margin-right:6px;vertical-align:middle"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                Duplicate Invoice
              </a-menu-item>
              <a-menu-item key="credit">Credit Invoice</a-menu-item>
              <a-menu-divider />
              <a-menu-item key="delete" @click="confirmDelete" style="color:#ef4444">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="margin-right:6px;vertical-align:middle"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                Delete Invoice
              </a-menu-item>
            </a-menu>
          </template>
        </a-dropdown>

        <!-- PAY Button — primary CTA -->
        <button
          class="pay-btn"
          @click="showPayModal = true"
          :disabled="invoice.status === 'paid'"
          :title="invoice.status === 'paid' ? 'Already paid' : 'Record a payment'"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
          {{ invoice.status === 'paid' ? 'Paid ✓' : 'Pay Invoice' }}
        </button>
      </div>
    </div>

    <!-- ── Loading state ── -->
    <div v-if="loading" class="page-loading">
      <a-spin size="large" />
      <p>Loading invoice...</p>
    </div>

    <!-- ── Main content ── -->
    <div v-else-if="invoice" class="page-body">

      <!-- LEFT: Invoice Document -->
      <div class="doc-column">
        <div class="invoice-doc" id="invoice-printable">

          <!-- Print header only visible on print -->
          <div class="print-actions print-only">
            <p style="font-size:12px;color:#64748b;text-align:right;margin:0 0 8px">
              Generated on {{ new Date().toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' }) }}
            </p>
          </div>

          <!-- Invoice Document Header -->
          <div class="doc-header">
            <div class="doc-from">
              <div class="company-logo">
                <svg viewBox="0 0 40 40" fill="none" width="36" height="36"><rect width="40" height="40" rx="8" fill="#3b82f6"/><text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle" fill="white" font-size="18" font-weight="800" font-family="Inter,sans-serif">P</text></svg>
              </div>
              <div class="company-name">Perfex Inc</div>
              <div class="company-addr">172 Ivy Club Gottliebfurt<br>New Heaven, Canada [CA] 2364</div>
            </div>
            <div class="doc-to">
              <div class="doc-to-label">Bill To</div>
              <div class="doc-to-name">
                <router-link
                  v-if="invoice.client?.id"
                  :to="{ name: 'admin.customers.view', params: { id: invoice.client.id } }"
                  class="client-link"
                >
                  {{ invoice.client.company }}
                </router-link>
                <span v-else>{{ invoice.client?.company || '—' }}</span>
              </div>
              <div class="doc-to-addr">{{ invoice.client?.city || '' }}{{ invoice.client?.country ? ', ' + invoice.client?.country : '' }}</div>
            </div>
          </div>

          <!-- Invoice Meta -->
          <div class="doc-meta-row">
            <div class="doc-num">{{ invoice.number }}</div>
            <div class="doc-meta-right">
              <div class="meta-item"><span class="meta-label">Invoice Date:</span> {{ formatDate(invoice.date) }}</div>
              <div class="meta-item"><span class="meta-label">Due Date:</span>
                <span :class="{ 'due-overdue': isOverdue(invoice) }">{{ formatDate(invoice.duedate) }}</span>
              </div>
              <div class="meta-item" v-if="invoice.project"><span class="meta-label">Project:</span> {{ invoice.project.name }}</div>
              <div class="meta-item" v-if="invoice.tags">
                <span class="meta-label">Tags:</span>
                <span v-for="tag in String(invoice.tags).split(',')" :key="tag" class="tag-pill">{{ tag.trim() }}</span>
              </div>
            </div>
          </div>

          <!-- Project Reference -->
          <div class="doc-project-ref" v-if="invoice.project">
            <svg viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" width="14" height="14"><rect x="2" y="3" width="20" height="14" rx="2"/></svg>
            This invoice is related to project:
            <strong style="color:#3b82f6">{{ invoice.project.name }}</strong>
          </div>

          <!-- Line Items Table -->
          <table class="items-table">
            <thead>
              <tr>
                <th style="width:32px">#</th>
                <th>Item &amp; Description</th>
                <th style="width:60px;text-align:right">Qty</th>
                <th style="width:90px;text-align:right">Rate</th>
                <th style="width:60px;text-align:right">Tax</th>
                <th style="width:90px;text-align:right">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in (invoice.items || [])" :key="item.id" class="item-row">
                <td class="item-idx">{{ idx + 1 }}</td>
                <td>
                  <div class="item-name">{{ item.description }}</div>
                  <div v-if="item.long_description" class="item-desc">{{ item.long_description }}</div>
                </td>
                <td style="text-align:right">{{ parseFloat(item.qty || 1).toFixed(0) }}</td>
                <td style="text-align:right">{{ formatCurrency(item.rate) }}</td>
                <td style="text-align:right">0%</td>
                <td style="text-align:right;font-weight:600">{{ formatCurrency((item.qty || 1) * (item.rate || 0)) }}</td>
              </tr>
              <tr v-if="!(invoice.items && invoice.items.length)">
                <td colspan="6" style="padding:20px;color:#94a3b8;text-align:center;font-size:13px">No line items</td>
              </tr>
            </tbody>
          </table>

          <!-- Totals Block -->
          <div class="totals-block">
            <div class="total-line"><span>Sub Total</span><span>{{ formatCurrency(invoice.subtotal) }}</span></div>
            <div class="total-line" v-if="parseFloat(invoice.discount_val) > 0">
              <span>Discount</span><span style="color:#16a34a">-{{ formatCurrency(invoice.discount_val) }}</span>
            </div>
            <div class="total-line" v-if="parseFloat(invoice.tax) > 0">
              <span>Tax</span><span>{{ formatCurrency(invoice.tax) }}</span>
            </div>
            <div class="total-line total-grand"><span>Total</span><span>{{ formatCurrency(invoice.total) }}</span></div>
            <div class="total-line total-due">
              <span>Amount Due</span>
              <span :style="{ color: amountDue > 0 ? '#dc2626' : '#16a34a' }">{{ formatCurrency(amountDue) }}</span>
            </div>
          </div>

          <!-- Notes / Terms -->
          <div v-if="invoice.notes" class="doc-notes">
            <div class="doc-notes-title">Terms &amp; Conditions</div>
            <p>{{ invoice.notes }}</p>
          </div>

          <!-- Signature area -->
          <div class="doc-signature">
            <div class="sig-block">
              <div class="sig-line"></div>
              <div class="sig-label">Authorized Signature</div>
            </div>
            <div class="sig-block">
              <div class="sig-line"></div>
              <div class="sig-label">Client Signature</div>
            </div>
          </div>

          <!-- Footer -->
          <div class="doc-footer">
            Thank you for your business!
          </div>
        </div>
      </div>

      <!-- RIGHT: Sidebar -->
      <div class="sidebar-column no-print">

        <!-- Payment Summary Card -->
        <div class="sidebar-card">
          <div class="sidebar-card-title">Payment Summary</div>
          <div class="payment-summary">
            <div class="ps-row">
              <span>Invoice Total</span>
              <span class="ps-val">{{ formatCurrency(invoice.total) }}</span>
            </div>
            <div class="ps-row">
              <span>Amount Paid</span>
              <span class="ps-val paid">{{ formatCurrency(totalPaid) }}</span>
            </div>
            <div class="ps-row ps-row-due">
              <span>Amount Due</span>
              <span class="ps-val" :style="{ color: amountDue > 0 ? '#dc2626' : '#16a34a' }">
                {{ formatCurrency(amountDue) }}
              </span>
            </div>
          </div>
          <!-- Progress bar -->
          <div class="pay-progress-wrap">
            <div class="pay-progress-bar">
              <div class="pay-progress-fill" :style="{ width: payPercent + '%' }"></div>
            </div>
            <span class="pay-progress-pct">{{ payPercent }}% paid</span>
          </div>
          <button
            v-if="invoice.status !== 'paid'"
            class="record-pay-btn"
            @click="showPayModal = true"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Record Payment
          </button>
          <div v-else class="paid-stamp">
            <svg viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" width="18" height="18"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Fully Paid
          </div>
        </div>

        <!-- Sidebar Tabs -->
        <div class="sidebar-card sidebar-tabs-card">
          <div class="stabs">
            <button v-for="t in sideTabs" :key="t.key" class="stab" :class="{ active: sideTab === t.key }" @click="sideTab = t.key">{{ t.label }}</button>
          </div>

          <!-- Payments Tab -->
          <div v-if="sideTab === 'payments'">
            <div v-if="!(invoice.payments && invoice.payments.length)" class="side-empty">
              <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" width="32" height="32"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
              <p>No payments recorded</p>
            </div>
            <div v-else class="pay-history">
              <div v-for="p in invoice.payments" :key="p.id" class="pay-item">
                <div class="pay-item-left">
                  <div class="pay-item-mode">{{ p.paymentmode || 'Manual' }}</div>
                  <div class="pay-item-date">{{ formatDate(p.date) }}</div>
                  <div v-if="p.transactionid" class="pay-item-txn">TXN: {{ p.transactionid }}</div>
                </div>
                <div class="pay-item-amount">{{ formatCurrency(p.amount) }}</div>
              </div>
            </div>
          </div>

          <!-- Activity Tab -->
          <div v-if="sideTab === 'activity'">
            <div class="side-empty">
              <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" width="32" height="32"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <p>Activity will appear here</p>
            </div>
          </div>

          <!-- Notes Tab -->
          <div v-if="sideTab === 'notes'">
            <div v-if="invoice.notes" class="note-content">{{ invoice.notes }}</div>
            <div v-else class="side-empty">
              <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" width="32" height="32"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/></svg>
              <p>No notes added</p>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- 404 state -->
    <div v-else class="page-loading">
      <p style="color:#ef4444;font-size:16px">Invoice not found.</p>
      <button class="back-btn" @click="$router.push({ name: 'admin.invoices' })">← Back to Invoices</button>
    </div>

    <!-- ═══════════════════════════════════════════
         PAYMENT MODAL
    ════════════════════════════════════════════ -->
    <a-modal
      v-model:open="showPayModal"
      :title="'Record Payment — ' + (invoice?.number || '')"
      :footer="null"
      width="520"
      :destroyOnClose="true"
    >
      <div class="modal-pay-body">
        <div class="pay-amount-hero">
          <div class="pah-label">Amount Due</div>
          <div class="pah-value">{{ formatCurrency(amountDue) }}</div>
        </div>

        <div class="pay-form-grid">
          <div class="pay-form-field">
            <label class="pff-label"><span class="req">*</span> Amount Received</label>
            <input type="number" class="pff-input" v-model="payForm.amount"
              :placeholder="formatCurrency(amountDue)" min="0.01" step="0.01" />
          </div>
          <div class="pay-form-field">
            <label class="pff-label">Transaction ID</label>
            <input type="text" class="pff-input" v-model="payForm.transactionid" placeholder="Optional" />
          </div>
          <div class="pay-form-field">
            <label class="pff-label"><span class="req">*</span> Payment Date</label>
            <input type="date" class="pff-input" v-model="payForm.date" />
          </div>
          <div class="pay-form-field">
            <label class="pff-label"><span class="req">*</span> Payment Mode</label>
            <select class="pff-select" v-model="payForm.paymentmode">
              <option value="">— Select Mode —</option>
              <option value="Bank">Bank Transfer</option>
              <option value="Cash">Cash</option>
              <option value="PayPal">PayPal</option>
              <option value="Stripe">Stripe</option>
              <option value="Cheque">Cheque</option>
              <option value="Credit Card">Credit Card</option>
              <option value="Crypto">Cryptocurrency</option>
            </select>
          </div>
          <div class="pay-form-field pay-full-col">
            <label class="pff-label">Note</label>
            <textarea class="pff-input pff-textarea" v-model="payForm.note" placeholder="Optional admin note..." rows="3"></textarea>
          </div>
        </div>

        <label class="pff-checkbox-label">
          <input type="checkbox" v-model="payForm.noEmail" />
          Do not send invoice payment recorded email to customer contacts
        </label>

        <div class="pay-modal-footer">
          <a-button @click="showPayModal = false">Cancel</a-button>
          <a-button type="primary" :loading="savingPay" @click="submitPayment" style="background:#16a34a;border-color:#16a34a">
            Save Payment
          </a-button>
        </div>
      </div>
    </a-modal>

    <!-- ── Edit Invoice Drawer ── -->
    <a-drawer
      v-model:open="showEditModal"
      title="Edit Invoice"
      placement="right"
      :width="1100"
    >
      <a-form layout="vertical" :model="editForm" @finish="submitEdit">
        <!-- Two-column settings section -->
        <div class="form-settings-grid">
          
          <!-- Column 1 -->
          <div class="settings-col">
            <a-form-item label="Customer" name="client_id" :rules="[{required:true,message:'Select a customer'}]">
              <a-select v-model:value="editForm.client_id" placeholder="Select customer..." style="width:100%" showSearch optionFilterProp="children" @change="onEditClientChange">
                <a-select-option v-for="c in clientOptions" :key="c.id" :value="c.id">{{ c.company }}</a-select-option>
              </a-select>
            </a-form-item>

            <!-- Addresses override collapsible -->
            <div class="address-overrides-wrap">
              <div class="address-toggle" @click="showAddressOverrides = !showAddressOverrides">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" :class="{ rotated: showAddressOverrides }">
                  <polyline points="9 18 15 12 9 6"/>
                </svg>
                <span>Edit Billing & Shipping Address Override</span>
              </div>
              <transition name="slide-fade">
                <div v-show="showAddressOverrides" class="addresses-fields">
                  <!-- Billing Address -->
                  <div class="address-section">
                    <div class="address-title">Bill To</div>
                    <a-form-item label="Street">
                      <a-textarea v-model:value="editForm.billing_street" :rows="2" placeholder="Street Address" />
                    </a-form-item>
                    <div class="address-grid-row">
                      <a-form-item label="City"><a-input v-model:value="editForm.billing_city" /></a-form-item>
                      <a-form-item label="State"><a-input v-model:value="editForm.billing_state" /></a-form-item>
                    </div>
                    <div class="address-grid-row">
                      <a-form-item label="Zip Code"><a-input v-model:value="editForm.billing_zip" /></a-form-item>
                      <a-form-item label="Country"><a-input v-model:value="editForm.billing_country" /></a-form-item>
                    </div>
                  </div>
                  <!-- Shipping Address -->
                  <div class="address-section">
                    <div class="address-title">Ship To</div>
                    <a-form-item label="Street">
                      <a-textarea v-model:value="editForm.shipping_street" :rows="2" placeholder="Street Address" />
                    </a-form-item>
                    <div class="address-grid-row">
                      <a-form-item label="City"><a-input v-model:value="editForm.shipping_city" /></a-form-item>
                      <a-form-item label="State"><a-input v-model:value="editForm.shipping_state" /></a-form-item>
                    </div>
                    <div class="address-grid-row">
                      <a-form-item label="Zip Code"><a-input v-model:value="editForm.shipping_zip" /></a-form-item>
                      <a-form-item label="Country"><a-input v-model:value="editForm.shipping_country" /></a-form-item>
                    </div>
                  </div>
                </div>
              </transition>
            </div>

            <a-form-item label="Project" name="project_id">
              <a-select v-model:value="editForm.project_id" placeholder="Select project..." style="width:100%" allowClear>
                <a-select-option v-for="p in projectOptions" :key="p.id" :value="p.id">{{ p.name }}</a-select-option>
              </a-select>
            </a-form-item>

            <a-form-item label="Allowed Payment Modes">
              <a-checkbox-group v-model:value="editForm.allowed_payment_modes_list">
                <a-checkbox value="Bank">Bank Transfer</a-checkbox>
                <a-checkbox value="Stripe Checkout">Stripe Checkout</a-checkbox>
              </a-checkbox-group>
            </a-form-item>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
              <a-form-item label="Currency" name="currency" :rules="[{required:true}]">
                <a-select v-model:value="editForm.currency" style="width:100%">
                  <a-select-option value="USD">USD</a-select-option>
                  <a-select-option value="EUR">EUR</a-select-option>
                  <a-select-option value="GBP">GBP</a-select-option>
                  <a-select-option value="CAD">CAD</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Sale Agent">
                <a-select v-model:value="editForm.sale_agent" style="width:100%" allowClear>
                  <a-select-option v-for="s in staffOptions" :key="s.id" :value="s.name">{{ s.name }}</a-select-option>
                </a-select>
              </a-form-item>
            </div>
          </div>

          <!-- Column 2 -->
          <div class="settings-col">
            <a-form-item label="Invoice Number" name="number" :rules="[{required:true,message:'Invoice Number is required'}]">
              <a-input v-model:value="editForm.number" placeholder="INV-000001" style="width:100%" />
            </a-form-item>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
              <a-form-item label="Invoice Date" name="date" :rules="[{required:true,message:'Required'}]">
                <a-date-picker v-model:value="editForm.date" style="width:100%" value-format="YYYY-MM-DD" />
              </a-form-item>
              <a-form-item label="Due Date" name="duedate" :rules="[{required:true,message:'Required'}]">
                <a-date-picker v-model:value="editForm.duedate" style="width:100%" value-format="YYYY-MM-DD" />
              </a-form-item>
            </div>

            <a-form-item style="margin-bottom:8px">
              <a-checkbox v-model:checked="editForm.prevent_overdue_reminders">
                Prevent sending overdue reminders for this invoice
              </a-checkbox>
            </a-form-item>

            <a-form-item label="Tags">
              <a-input v-model:value="editForm.tags" placeholder="Comma separated tags..." />
            </a-form-item>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
              <a-form-item label="Recurring Invoice?">
                <a-select v-model:value="editForm.recurring_type" style="width:100%">
                  <a-select-option value="no">No</a-select-option>
                  <a-select-option value="daily">Daily</a-select-option>
                  <a-select-option value="weekly">Weekly</a-select-option>
                  <a-select-option value="monthly">Monthly</a-select-option>
                  <a-select-option value="quarterly">Quarterly</a-select-option>
                  <a-select-option value="yearly">Yearly</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Discount Type">
                <a-select v-model:value="editForm.discount_type" style="width:100%" @change="recalcEdit">
                  <a-select-option value="no_discount">No discount</a-select-option>
                  <a-select-option value="before_tax">Before Tax</a-select-option>
                  <a-select-option value="after_tax">After Tax</a-select-option>
                </a-select>
              </a-form-item>
            </div>

            <a-form-item label="Admin Note" style="margin-bottom:0">
              <a-textarea v-model:value="editForm.admin_note" :rows="3" placeholder="Admin notes..." />
            </a-form-item>
          </div>

        </div>

        <a-divider style="margin: 16px 0" />

        <!-- Line Items Section -->
        <div class="line-items-section">
          <div class="line-items-header">
            <div class="qty-mode-group">
              <span style="font-weight:600;margin-right:12px;font-size:13px">Show quantity as:</span>
              <a-radio-group v-model:value="editForm.qty_display_mode">
                <a-radio value="qty">Qty</a-radio>
                <a-radio value="hours">Hours</a-radio>
                <a-radio value="qty_hours">Qty/Hours</a-radio>
              </a-radio-group>
            </div>
            
            <div class="add-predefined-wrap">
              <span style="font-size:12px;color:#64748b;margin-right:8px;font-weight:500">Add catalog item:</span>
              <a-select placeholder="Select item..." style="width:220px" @change="addEditPredefinedItem" :value="null" showSearch optionFilterProp="children">
                <a-select-option v-for="item in catalogItems" :key="item.id" :value="item.id">{{ item.name }} ({{ formatCurrency(item.rate) }})</a-select-option>
              </a-select>
            </div>
          </div>

          <!-- Dynamic Line Items Table -->
          <div class="items-table-wrapper">
            <table class="items-form-table">
              <thead>
                <tr>
                  <th style="width:250px">Item</th>
                  <th style="width:300px">Long Description</th>
                  <th style="width:100px">{{ qtyEditLabel }}</th>
                  <th style="width:80px">Unit</th>
                  <th style="width:110px">Rate</th>
                  <th style="width:120px">Tax</th>
                  <th style="width:110px;text-align:right">Amount</th>
                  <th style="width:50px"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in editForm.items" :key="index">
                  <td>
                    <a-input v-model:value="item.description" placeholder="Item Name..." />
                  </td>
                  <td>
                    <a-textarea v-model:value="item.long_description" placeholder="Description details..." :rows="1" />
                  </td>
                  <td>
                    <a-input-number v-model:value="item.qty" :min="0.01" style="width:100%" @change="recalcEdit" />
                  </td>
                  <td>
                    <a-input v-model:value="item.unit" placeholder="Unit" />
                  </td>
                  <td>
                    <a-input-number v-model:value="item.rate" :min="0" style="width:100%" @change="recalcEdit" />
                  </td>
                  <td>
                    <a-select v-model:value="item.tax_rate" style="width:100%" @change="recalcEdit">
                      <a-select-option :value="0">No Tax</a-select-option>
                      <a-select-option :value="5">5.00%</a-select-option>
                      <a-select-option :value="10">10.00%</a-select-option>
                      <a-select-option :value="18">18.00%</a-select-option>
                    </a-select>
                  </td>
                  <td style="text-align:right;font-weight:600;padding-right:8px;font-size:13px;color:#1e293b">
                    {{ formatCurrency(item.qty * item.rate) }}
                  </td>
                  <td style="text-align:center">
                    <button type="button" class="btn-delete-row" @click="removeEditItemRow(index)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                    </button>
                  </td>
                </tr>
                <tr v-if="!editForm.items.length">
                  <td colspan="8" class="empty-items-row">
                    No items added yet. Click Add Row or select a catalog item.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <button type="button" class="btn-add-row" @click="addEditItemRow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Item Row
          </button>
        </div>

        <a-divider style="margin: 16px 0" />

        <!-- Totals & Notes Row -->
        <div class="totals-notes-grid">
          
          <!-- Notes Column -->
          <div class="notes-col">
            <a-form-item label="Client Note">
              <a-textarea v-model:value="editForm.client_note" :rows="3" />
            </a-form-item>
            <a-form-item label="Terms & Conditions">
              <a-textarea v-model:value="editForm.terms_conditions" :rows="5" />
            </a-form-item>
          </div>

          <!-- Totals Column -->
          <div class="totals-col">
            <div class="summary-line">
              <span>Sub Total :</span>
              <span class="val">{{ formatCurrency(editForm.subtotal) }}</span>
            </div>
            
            <div class="summary-line inline-input-line" v-if="editForm.discount_type !== 'no_discount'">
              <div class="label-group">
                <span>Discount :</span>
                <a-input-number v-model:value="editForm.discount_value_input" :min="0" size="small" style="width:75px;margin:0 6px" @change="recalcEdit" />
                <a-select v-model:value="editForm.discount_symbol" size="small" style="width:55px" @change="recalcEdit">
                  <a-select-option value="%">%</a-select-option>
                  <a-select-option value="$">$</a-select-option>
                </a-select>
              </div>
              <span class="val text-danger">-{{ formatCurrency(editForm.discount_val) }}</span>
            </div>

            <div class="summary-line inline-input-line">
              <div class="label-group">
                <span>Adjustment :</span>
                <a-input-number v-model:value="editForm.adjustment" size="small" style="width:90px;margin-left:6px" @change="recalcEdit" />
              </div>
              <span class="val">{{ formatCurrency(editForm.adjustment) }}</span>
            </div>

            <div class="summary-line tax-summary-line" v-if="editForm.tax > 0">
              <span>Tax :</span>
              <span class="val">{{ formatCurrency(editForm.tax) }}</span>
            </div>

            <div class="summary-line grand-total-line">
              <span>Total :</span>
              <span class="val">{{ formatCurrency(editForm.total) }}</span>
            </div>
          </div>

        </div>

        <div class="drawer-footer">
          <a-button @click="showEditDrawer = false">Cancel</a-button>
          <a-button type="primary" @click="submitEdit">Save Changes</a-button>
        </div>
      </a-form>
    </a-drawer>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { message, Modal } from 'ant-design-vue';
import { getFinanceSettings, formatMoney, numberToWords } from '../../utils';

export default defineComponent({
  name: 'InvoiceViewPage',
  setup() {
    const route  = useRoute();
    const router = useRouter();
    const settings = getFinanceSettings();

    const loading    = ref(true);
    const savingPay  = ref(false);
    const isPrinting = ref(false);
    const invoice    = ref(null);
    const showPayModal  = ref(false);
    const showEditModal = ref(false);
    const sideTab = ref('payments');

    const showAddressOverrides = ref(false);
    const clientOptions = ref([]);
    const projectOptions = ref([]);
    const staffOptions = ref([]);
    const catalogItems = ref([]);

    const editForm = reactive({
      id: null,
      client_id: null,
      project_id: null,
      number: '',
      status: 'unpaid',
      date: null,
      duedate: null,
      prevent_overdue_reminders: false,
      allowed_payment_modes_list: ['Bank', 'Stripe Checkout'],
      allowed_payment_modes: 'Bank,Stripe Checkout',
      currency: 'USD',
      sale_agent: 'Merl Wintheiser',
      recurring_type: 'no',
      discount_type: 'no_discount',
      admin_note: '',
      qty_display_mode: 'qty',
      client_note: '',
      terms_conditions: '',
      subtotal: 0,
      discount_value_input: 0,
      discount_symbol: '%',
      discount_percent: 0,
      discount_val: 0,
      tax: 0,
      adjustment: 0,
      total: 0,
      notes: '',
      tags: '',
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
      items: [],
    });

    const qtyEditLabel = computed(() => {
      if (editForm.qty_display_mode === 'hours') return 'Hours';
      if (editForm.qty_display_mode === 'qty_hours') return 'Qty/Hours';
      return 'Qty';
    });

    const addEditItemRow = () => {
      const defaultTax = settings.finance_show_tax_per_item ? (parseFloat(settings.finance_default_tax) || 0) : 0;
      editForm.items.push({
        description: '',
        long_description: '',
        qty: 1,
        unit: 'Unit',
        rate: 0,
        tax_rate: defaultTax
      });
      recalcEdit();
    };

    const removeEditItemRow = (index) => {
      editForm.items.splice(index, 1);
      recalcEdit();
    };

    const addEditPredefinedItem = (itemId) => {
      const item = catalogItems.value.find(i => i.id === itemId);
      if (item) {
        const itemTax = settings.finance_show_tax_per_item ? (parseFloat(item.tax_rate) || 0) : 0;
        if (editForm.items.length === 1 && !editForm.items[0].description && editForm.items[0].rate === 0) {
          editForm.items[0] = {
            description: item.name,
            long_description: item.description || '',
            qty: 1,
            unit: item.unit || 'Unit',
            rate: parseFloat(item.rate) || 0,
            tax_rate: itemTax
          };
        } else {
          editForm.items.push({
            description: item.name,
            long_description: item.description || '',
            qty: 1,
            unit: item.unit || 'Unit',
            rate: parseFloat(item.rate) || 0,
            tax_rate: itemTax
          });
        }
        recalcEdit();
      }
    };

    const onEditClientChange = (clientId) => {
      const client = clientOptions.value.find(c => c.id === clientId);
      if (client) {
        editForm.billing_street = client.billing_street || '';
        editForm.billing_city = client.billing_city || '';
        editForm.billing_state = client.billing_state || '';
        editForm.billing_zip = client.billing_zip || '';
        editForm.billing_country = client.billing_country || '';

        editForm.shipping_street = client.shipping_street || '';
        editForm.shipping_city = client.shipping_city || '';
        editForm.shipping_state = client.shipping_state || '';
        editForm.shipping_zip = client.shipping_zip || '';
        editForm.shipping_country = client.shipping_country || '';
      }
    };

    const openEditDrawer = () => {
      if (!invoice.value) return;
      
      const inv = invoice.value;
      
      let modesList = ['Bank', 'Stripe Checkout'];
      if (inv.allowed_payment_modes !== undefined && inv.allowed_payment_modes !== null) {
        modesList = inv.allowed_payment_modes ? inv.allowed_payment_modes.split(',') : [];
      }

      const itemsList = (inv.items || []).map(item => ({
        description: item.description || '',
        long_description: item.long_description || '',
        qty: parseFloat(item.qty) || 1,
        unit: item.unit || 'Unit',
        rate: parseFloat(item.rate) || 0,
        tax_rate: parseFloat(item.tax_rate) || 0
      }));

      let discSymbol = '%';
      let discValInput = parseFloat(inv.discount_percent) || 0;
      if (parseFloat(inv.discount_percent) === 0 && parseFloat(inv.discount_val) > 0) {
        discSymbol = '$';
        discValInput = parseFloat(inv.discount_val);
      }

      Object.assign(editForm, {
        id: inv.id,
        client_id: inv.client_id,
        project_id: inv.project_id,
        number: inv.number,
        status: inv.status,
        date: inv.date ? new Date(inv.date).toISOString().split('T')[0] : null,
        duedate: inv.duedate ? new Date(inv.duedate).toISOString().split('T')[0] : null,
        prevent_overdue_reminders: !!inv.prevent_overdue_reminders,
        allowed_payment_modes_list: modesList,
        allowed_payment_modes: inv.allowed_payment_modes || '',
        currency: inv.currency || 'USD',
        sale_agent: inv.sale_agent || 'Merl Wintheiser',
        recurring_type: inv.recurring_type || 'no',
        discount_type: inv.discount_type || 'no_discount',
        admin_note: inv.admin_note || '',
        qty_display_mode: inv.qty_display_mode || 'qty',
        client_note: inv.client_note || '',
        terms_conditions: inv.terms_conditions || '',
        subtotal: parseFloat(inv.subtotal) || 0,
        discount_value_input: discValInput,
        discount_symbol: discSymbol,
        discount_percent: parseFloat(inv.discount_percent) || 0,
        discount_val: parseFloat(inv.discount_val) || 0,
        tax: parseFloat(inv.tax) || 0,
        adjustment: parseFloat(inv.adjustment) || 0,
        total: parseFloat(inv.total) || 0,
        notes: inv.notes || '',
        tags: inv.tags || '',
        billing_street: inv.billing_street || '',
        billing_city: inv.billing_city || '',
        billing_state: inv.billing_state || '',
        billing_zip: inv.billing_zip || '',
        billing_country: inv.billing_country || '',
        shipping_street: inv.shipping_street || '',
        shipping_city: inv.shipping_city || '',
        shipping_state: inv.shipping_state || '',
        shipping_zip: inv.shipping_zip || '',
        shipping_country: inv.shipping_country || '',
        items: itemsList.length ? itemsList : [{ description: '', long_description: '', qty: 1, unit: 'Unit', rate: 0, tax_rate: 0 }],
      });

      showEditModal.value = true;
    };

    const recalcEdit = () => {
      let sub = 0;
      editForm.items.forEach(item => {
        sub += (item.qty || 0) * (item.rate || 0);
      });
      editForm.subtotal = parseFloat(sub.toFixed(2));

      let discountVal = 0;
      let discountPercent = 0;
      const discInput = parseFloat(editForm.discount_value_input) || 0;
      
      if (editForm.discount_type !== 'no_discount') {
        if (editForm.discount_symbol === '%') {
          discountPercent = discInput;
          discountVal = editForm.subtotal * (discountPercent / 100);
        } else {
          discountVal = discInput;
          discountPercent = editForm.subtotal > 0 ? (discountVal / editForm.subtotal) * 100 : 0;
        }
      }
      editForm.discount_percent = parseFloat(discountPercent.toFixed(2));
      editForm.discount_val = parseFloat(discountVal.toFixed(2));

      let taxAmount = 0;
      if (editForm.discount_type === 'before_tax') {
        const discountFactor = editForm.subtotal > 0 ? 1 - (discountVal / editForm.subtotal) : 1;
        editForm.items.forEach(item => {
          const itemSub = (item.qty || 0) * (item.rate || 0);
          const itemDiscounted = itemSub * discountFactor;
          taxAmount += itemDiscounted * ((item.tax_rate || 0) / 100);
        });
      } else {
        editForm.items.forEach(item => {
          const itemSub = (item.qty || 0) * (item.rate || 0);
          taxAmount += itemSub * ((item.tax_rate || 0) / 100);
        });
      }
      editForm.tax = parseFloat(taxAmount.toFixed(2));

      let tot = 0;
      const adj = parseFloat(editForm.adjustment) || 0;
      if (editForm.discount_type === 'before_tax') {
        tot = (editForm.subtotal - editForm.discount_val) + editForm.tax + adj;
      } else if (editForm.discount_type === 'after_tax') {
        tot = (editForm.subtotal + editForm.tax) - editForm.discount_val + adj;
      } else {
        tot = editForm.subtotal + editForm.tax + adj;
      }
      editForm.total = parseFloat(Math.max(0, tot).toFixed(2));
    };

    const submitEdit = async () => {
      savingPay.value = true;
      try {
        recalcEdit();
        editForm.allowed_payment_modes = editForm.allowed_payment_modes_list.join(',');
        
        await axios.put(`/api/invoices/${editForm.id}`, editForm);
        message.success('Invoice updated successfully');
        showEditModal.value = false;
        loadInvoice();
      } catch (e) {
        const errors = e.response?.data?.errors;
        if (errors) {
          message.error(Object.values(errors).flat().join(', '));
        } else {
          message.error('Failed to update invoice');
        }
      } finally {
        savingPay.value = false;
      }
    };

    const loadClients = async () => {
      try {
        const res = await axios.get('/api/clients', { params: { per_page: 200 } });
        clientOptions.value = res.data.clients?.data || [];
      } catch {}
    };

    const loadProjects = async () => {
      try {
        const res = await axios.get('/api/projects', { params: { per_page: 200 } });
        projectOptions.value = res.data.projects?.data || [];
      } catch {}
    };

    const loadStaff = async () => {
      try {
        const res = await axios.get('/api/staff', { params: { per_page: 200 } });
        staffOptions.value = res.data.staff?.data || [];
      } catch {}
    };

    const loadPredefinedItems = async () => {
      try {
        const res = await axios.get('/api/predefined-items', { params: { per_page: 200 } });
        catalogItems.value = res.data.items?.data || [];
      } catch {}
    };

    const sideTabs = [
      { key: 'payments', label: 'Payments' },
      { key: 'activity', label: 'Activity' },
      { key: 'notes',    label: 'Notes' },
    ];

    const payForm = reactive({
      amount: '',
      transactionid: '',
      date: new Date().toISOString().split('T')[0],
      paymentmode: '',
      note: '',
      noEmail: false,
    });

    // Computed
    const totalPaid = computed(() => {
      if (!invoice.value?.payments) return 0;
      return invoice.value.payments.reduce((s, p) => s + parseFloat(p.amount || 0), 0);
    });

    const amountDue = computed(() => {
      if (!invoice.value) return 0;
      return Math.max(0, parseFloat(invoice.value.total || 0) - totalPaid.value);
    });

    const payPercent = computed(() => {
      if (!invoice.value?.total || parseFloat(invoice.value.total) === 0) return 0;
      return Math.min(100, Math.round((totalPaid.value / parseFloat(invoice.value.total)) * 100));
    });

    // Load invoice
    const loadInvoice = async () => {
      loading.value = true;
      try {
        const res = await axios.get(`/api/invoices/${route.params.id}`);
        invoice.value = res.data;
        payForm.amount = amountDue.value || '';
      } catch (e) {
        message.error('Failed to load invoice');
        invoice.value = null;
      } finally {
        loading.value = false;
      }
    };

    // Download PDF via print dialog
    const downloadPDF = () => {
      isPrinting.value = true;
      setTimeout(() => {
        window.print();
        setTimeout(() => { isPrinting.value = false; }, 1000);
      }, 150);
    };

    const sendEmail = () => {
      message.info(`Sending ${invoice.value?.number} to ${invoice.value?.client?.company}...`);
    };

    const markAsPaid = async () => {
      try {
        await axios.put(`/api/invoices/${invoice.value.id}`, { status: 'paid' });
        message.success('Invoice marked as paid');
        loadInvoice();
      } catch {
        message.error('Failed to update status');
      }
    };

    const confirmDelete = () => {
      Modal.confirm({
        title: 'Delete Invoice',
        content: `Are you sure you want to permanently delete ${invoice.value?.number}? This cannot be undone.`,
        okText: 'Delete',
        okType: 'danger',
        cancelText: 'Cancel',
        async onOk() {
          try {
            await axios.delete(`/api/invoices/${invoice.value.id}`);
            message.success('Invoice deleted');
            router.push({ name: 'admin.invoices' });
          } catch {
            message.error('Failed to delete invoice');
          }
        }
      });
    };

    const submitPayment = async () => {
      if (!payForm.amount || parseFloat(payForm.amount) <= 0) {
        message.warning('Enter a valid amount');
        return;
      }
      if (!payForm.date) {
        message.warning('Payment date is required');
        return;
      }
      savingPay.value = true;
      try {
        await axios.post('/api/payments', {
          invoice_id:    invoice.value.id,
          amount:        payForm.amount,
          date:          payForm.date,
          paymentmode:   payForm.paymentmode,
          transactionid: payForm.transactionid,
          note:          payForm.note,
        });
        message.success('Payment recorded successfully!');
        showPayModal.value = false;
        Object.assign(payForm, { amount: '', transactionid: '', paymentmode: '', note: '', noEmail: false });
        loadInvoice();
      } catch (e) {
        message.error(e.response?.data?.message || 'Failed to record payment');
      } finally {
        savingPay.value = false;
      }
    };

    const isOverdue = (inv) => {
      if (!inv) return false;
      if (['paid', 'cancelled'].includes(inv.status)) return false;
      return inv.duedate && new Date(inv.duedate) < new Date();
    };

    const statusLabel = (s) => ({
      draft: 'Draft', unpaid: 'Unpaid', paid: 'Paid',
      overdue: 'Overdue', partially_paid: 'Partially Paid', cancelled: 'Cancelled',
    }[s] || s);

    const formatCurrency = (val) => {
      if (val === undefined || val === null) return '$0.00';
      return '$' + parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2 });
    };

    const formatDate = (d) => {
      if (!d) return '—';
      return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    };

    onMounted(() => {
      loadInvoice();
      loadClients();
      loadProjects();
      loadStaff();
      loadPredefinedItems();
    });

    return {
      loading, savingPay, isPrinting,
      invoice, showPayModal, showEditModal,
      sideTab, sideTabs, payForm,
      totalPaid, amountDue, payPercent,
      loadInvoice, downloadPDF, sendEmail, markAsPaid, confirmDelete, submitPayment,
      isOverdue, statusLabel, formatCurrency, formatDate,
      
      showAddressOverrides,
      clientOptions,
      projectOptions,
      staffOptions,
      catalogItems,
      editForm,
      qtyEditLabel,
      
      openEditDrawer,
      submitEdit,
      recalcEdit,
      onEditClientChange,
      addEditItemRow,
      removeEditItemRow,
      addEditPredefinedItem,
    };
  }
});
</script>

<style scoped>
/* ── Base ── */
.invoice-view-page {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background: #f1f5f9;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* ── Top Action Bar ── */
.top-bar {
  position: sticky;
  top: 0;
  z-index: 100;
  background: #fff;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 24px;
  gap: 12px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}
.top-bar-left {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
}
.top-bar-right {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
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
  display: flex;
  align-items: center;
  gap: 5px;
  font-family: inherit;
  transition: all 0.12s;
  white-space: nowrap;
}
.back-btn:hover { background: #f8fafc; border-color: #cbd5e1; }

.inv-title-group { display: flex; align-items: center; gap: 10px; }
.inv-heading { font-size: 16px; font-weight: 700; color: #1e293b; }
.project-ref {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 11.5px;
  color: #64748b;
  background: #f1f5f9;
  padding: 3px 8px;
  border-radius: 4px;
}

/* Action Buttons */
.action-btn {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 7px 13px;
  font-size: 12.5px;
  font-weight: 500;
  color: #374151;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  font-family: inherit;
  transition: all 0.12s;
  white-space: nowrap;
}
.action-btn:hover { background: #f8fafc; border-color: #cbd5e1; }

/* PAY Button */
.pay-btn {
  background: linear-gradient(135deg, #16a34a, #15803d);
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 8px 18px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: inherit;
  transition: all 0.15s;
  white-space: nowrap;
  box-shadow: 0 2px 6px rgba(22,163,74,0.3);
}
.pay-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #15803d, #166534);
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(22,163,74,0.35);
}
.pay-btn:disabled {
  background: #bbf7d0;
  color: #16a34a;
  cursor: not-allowed;
  box-shadow: none;
  transform: none;
}

/* Status badges */
.status-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 5px;
  font-size: 11.5px;
  font-weight: 700;
  text-transform: capitalize;
}
.status-unpaid         { background: #fee2e2; color: #dc2626; }
.status-paid           { background: #dcfce7; color: #16a34a; }
.status-partially_paid { background: #fef3c7; color: #d97706; }
.status-overdue        { background: #ffedd5; color: #ea580c; }
.status-draft          { background: #f1f5f9; color: #64748b; }
.status-cancelled      { background: #f1f5f9; color: #94a3b8; }

/* ── Page Body ── */
.page-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
  gap: 12px;
  padding: 80px 20px;
  color: #64748b;
  font-size: 14px;
}
.page-body {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 20px;
  padding: 24px;
  align-items: start;
}

/* ── Invoice Document ── */
.doc-column {}
.invoice-doc {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 40px 48px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.06);
  max-width: 900px;
  margin: 0 auto;
}

/* Header */
.doc-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 32px;
  padding-bottom: 24px;
  border-bottom: 2px solid #f1f5f9;
}
.doc-from {}
.company-logo { margin-bottom: 10px; }
.company-name { font-size: 18px; font-weight: 800; color: #1e293b; margin-bottom: 6px; }
.company-addr { font-size: 12px; color: #64748b; line-height: 1.6; }
.doc-to { text-align: right; }
.doc-to-label { font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
.doc-to-name { font-size: 15px; font-weight: 700; color: #3b82f6; margin-bottom: 4px; }
.client-link {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 700;
  transition: color 0.12s;
}
.client-link:hover {
  color: #2563eb;
  text-decoration: underline;
}
.doc-to-addr { font-size: 12px; color: #64748b; }

/* Meta row */
.doc-meta-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}
.doc-num { font-size: 26px; font-weight: 800; color: #3b82f6; letter-spacing: -0.5px; }
.doc-meta-right { text-align: right; }
.meta-item { font-size: 12.5px; color: #64748b; margin-bottom: 4px; }
.meta-label { font-weight: 600; color: #374151; }
.due-overdue { color: #dc2626; font-weight: 700; }
.tag-pill {
  display: inline-block;
  background: #f1f5f9;
  color: #475569;
  border-radius: 999px;
  padding: 1px 7px;
  font-size: 10.5px;
  margin-left: 4px;
  border: 1px solid #e2e8f0;
}

/* Project ref */
.doc-project-ref {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  color: #64748b;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 6px;
  padding: 8px 12px;
  margin-bottom: 20px;
}

/* Items table */
.items-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 4px;
  font-size: 13px;
}
.items-table th {
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
  border-bottom: 1px solid #e2e8f0;
  padding: 9px 10px;
  text-align: left;
  font-size: 11.5px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.item-row td { padding: 13px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
.item-row:last-child td { border-bottom: 1px solid #e2e8f0; }
.item-idx { color: #94a3b8; font-size: 12px; }
.item-name { font-weight: 600; color: #1e293b; margin-bottom: 3px; }
.item-desc { font-size: 11.5px; color: #64748b; line-height: 1.5; }

/* Totals */
.totals-block {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0;
  margin-bottom: 28px;
  padding-top: 4px;
}
.total-line {
  display: flex;
  justify-content: space-between;
  width: 260px;
  padding: 7px 12px;
  font-size: 13px;
  color: #475569;
  border-bottom: 1px solid #f1f5f9;
}
.total-line span:last-child { font-weight: 600; }
.total-grand {
  background: #f8fafc;
  border-top: 2px solid #e2e8f0;
  border-bottom: 2px solid #e2e8f0;
  font-weight: 700;
  font-size: 14px;
  color: #1e293b;
}
.total-due {
  background: #fff;
  font-weight: 800;
  font-size: 15px;
  border: none;
  padding-top: 10px;
}

/* Notes */
.doc-notes {
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 8px;
  padding: 14px 16px;
  margin-bottom: 28px;
}
.doc-notes-title { font-weight: 700; font-size: 12px; color: #92400e; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
.doc-notes p { font-size: 13px; color: #374151; margin: 0; line-height: 1.6; }

/* Signature */
.doc-signature {
  display: flex;
  gap: 40px;
  margin-bottom: 28px;
  padding-top: 16px;
}
.sig-block { flex: 1; }
.sig-line { border-bottom: 1px solid #334155; margin-bottom: 8px; height: 40px; }
.sig-label { font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }

/* Footer */
.doc-footer {
  text-align: center;
  font-size: 12.5px;
  color: #94a3b8;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
}

/* ── Sidebar ── */
.sidebar-column { display: flex; flex-direction: column; gap: 16px; }
.sidebar-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 18px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.sidebar-card-title {
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 14px;
}

/* Payment summary */
.payment-summary { margin-bottom: 14px; }
.ps-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 7px 0;
  font-size: 13px;
  color: #475569;
  border-bottom: 1px solid #f1f5f9;
}
.ps-row:last-child { border-bottom: none; }
.ps-row-due { font-weight: 700; font-size: 14px; color: #1e293b; }
.ps-val { font-weight: 700; }
.ps-val.paid { color: #16a34a; }

/* Progress bar */
.pay-progress-wrap { margin-bottom: 14px; }
.pay-progress-bar {
  height: 8px;
  background: #e2e8f0;
  border-radius: 999px;
  overflow: hidden;
  margin-bottom: 5px;
}
.pay-progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #16a34a, #22c55e);
  border-radius: 999px;
  transition: width 0.5s ease;
}
.pay-progress-pct { font-size: 11px; color: #64748b; font-weight: 500; }

.record-pay-btn {
  width: 100%;
  background: linear-gradient(135deg, #16a34a, #15803d);
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 10px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-family: inherit;
  transition: all 0.15s;
  box-shadow: 0 2px 8px rgba(22,163,74,0.25);
}
.record-pay-btn:hover {
  background: linear-gradient(135deg, #15803d, #166534);
  box-shadow: 0 4px 12px rgba(22,163,74,0.35);
}
.paid-stamp {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  color: #16a34a;
  font-weight: 700;
  font-size: 13px;
  padding: 8px;
  background: #f0fdf4;
  border-radius: 6px;
}

/* Sidebar tabs */
.sidebar-tabs-card { padding: 0; overflow: hidden; }
.stabs {
  display: flex;
  border-bottom: 1px solid #e2e8f0;
}
.stab {
  flex: 1;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  padding: 11px 8px;
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.12s;
  margin-bottom: -1px;
}
.stab:hover { color: #334155; }
.stab.active { color: #3b82f6; border-bottom-color: #3b82f6; }

.side-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 30px 16px;
  gap: 8px;
  color: #94a3b8;
  font-size: 12.5px;
}
.side-empty p { margin: 0; }

/* Payment history */
.pay-history { padding: 12px; display: flex; flex-direction: column; gap: 10px; }
.pay-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 7px;
  padding: 10px 12px;
}
.pay-item-mode { font-weight: 700; color: #1e293b; font-size: 12.5px; margin-bottom: 2px; }
.pay-item-date { font-size: 11px; color: #64748b; }
.pay-item-txn  { font-size: 10.5px; color: #94a3b8; margin-top: 2px; }
.pay-item-amount { font-size: 14px; font-weight: 800; color: #16a34a; white-space: nowrap; }

/* Notes */
.note-content { padding: 14px 16px; font-size: 13px; color: #374151; line-height: 1.6; }

/* ── Payment Modal ── */
.modal-pay-body { padding-top: 4px; }
.pay-amount-hero {
  background: linear-gradient(135deg, #f0fdf4, #dcfce7);
  border: 1px solid #bbf7d0;
  border-radius: 8px;
  padding: 14px 18px;
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.pah-label { font-size: 12px; font-weight: 600; color: #166534; text-transform: uppercase; letter-spacing: 0.05em; }
.pah-value { font-size: 22px; font-weight: 800; color: #166534; }

.pay-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
  margin-bottom: 14px;
}
.pay-form-field { display: flex; flex-direction: column; gap: 5px; }
.pay-full-col { grid-column: 1 / -1; }
.pff-label { font-size: 12px; font-weight: 600; color: #374151; }
.pff-label .req { color: #ef4444; margin-right: 2px; }
.pff-input {
  border: 1px solid #d1d5db;
  border-radius: 6px;
  padding: 8px 10px;
  font-size: 13px;
  font-family: inherit;
  outline: none;
  transition: border-color 0.15s;
  background: #fff;
  width: 100%;
  box-sizing: border-box;
}
.pff-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
.pff-select {
  border: 1px solid #d1d5db;
  border-radius: 6px;
  padding: 8px 10px;
  font-size: 13px;
  font-family: inherit;
  outline: none;
  background: #fff;
  cursor: pointer;
  width: 100%;
}
.pff-textarea { min-height: 72px; resize: vertical; }
.pff-checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: #475569;
  margin-bottom: 16px;
  cursor: pointer;
}
.pay-modal-footer { display: flex; justify-content: flex-end; gap: 8px; }
/* ── Edit Invoice Drawer ── */
.form-settings-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}
.settings-col {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

/* Address collapsible override */
.address-overrides-wrap {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 8px 12px;
  margin-bottom: 12px;
}
.address-toggle {
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  color: #475569;
  user-select: none;
}
.address-toggle svg {
  transition: transform 0.2s;
}
.address-toggle svg.rotated {
  transform: rotate(90deg);
}
.addresses-fields {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-top: 10px;
  border-top: 1px dashed #cbd5e1;
  padding-top: 10px;
}
.address-section {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.address-section :deep(.ant-form-item) {
  margin-bottom: 6px !important;
}
.address-title {
  font-size: 11px;
  font-weight: 700;
  color: #3b82f6;
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.address-grid-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

/* Line Items */
.line-items-section {
  margin-top: 12px;
}
.line-items-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
  flex-wrap: wrap;
  gap: 10px;
}
.qty-mode-group, .add-predefined-wrap {
  display: flex;
  align-items: center;
}
.items-table-wrapper {
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  overflow: hidden;
  margin-bottom: 12px;
  background: #fff;
}
.items-form-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
}
.items-form-table th {
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  padding: 8px 10px;
  text-align: left;
  color: #475569;
  font-weight: 700;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 0.02em;
}
.items-form-table td {
  padding: 8px 10px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}
.items-form-table tr:last-child td {
  border-bottom: none;
}
.empty-items-row {
  text-align: center;
  padding: 30px;
  color: #94a3b8;
  font-style: italic;
}
.btn-delete-row {
  background: none;
  border: 1px solid #fee2e2;
  border-radius: 4px;
  padding: 5px;
  color: #ef4444;
  cursor: pointer;
  transition: all 0.12s;
  display: inline-flex;
  align-items: center;
}
.btn-delete-row:hover {
  background: #fee2e2;
  color: #b91c1c;
}
.btn-add-row {
  background: #fff;
  border: 1px dashed #cbd5e1;
  border-radius: 6px;
  padding: 7px 14px;
  font-size: 12px;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  transition: all 0.12s;
}
.btn-add-row:hover {
  background: #f8fafc;
  border-color: #94a3b8;
  color: #1e293b;
}

/* Totals & Notes */
.totals-notes-grid {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 24px;
  margin-top: 12px;
  align-items: start;
}
.notes-col {
  display: flex;
  flex-direction: column;
}
.totals-col {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 14px 18px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.summary-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13px;
  color: #475569;
}
.summary-line .val {
  font-weight: 700;
  color: #1e293b;
}
.inline-input-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.inline-input-line .label-group {
  display: flex;
  align-items: center;
}
.tax-summary-line {
  color: #0d9488;
}
.text-danger {
  color: #ef4444;
}
.summary-line.grand-total-line {
  border-top: 2px solid #e2e8f0;
  padding-top: 10px;
  font-size: 14px;
  font-weight: 700;
  color: #1e293b;
}
.summary-line.grand-total-line .val {
  font-size: 17px;
  font-weight: 800;
  color: #3b82f6;
}

.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
}

/* ═══════════ PRINT STYLES ═══════════ */
.print-only { display: none; }

@media print {
  .no-print { display: none !important; }
  .print-only { display: block !important; }

  .invoice-view-page {
    background: #fff !important;
    min-height: unset;
  }
  .page-body {
    display: block !important;
    padding: 0 !important;
  }
  .doc-column {
    width: 100%;
  }
  .sidebar-column { display: none !important; }
  .invoice-doc {
    border: none !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    padding: 20px 30px !important;
    max-width: 100% !important;
    margin: 0 !important;
  }
  @page {
    size: A4;
    margin: 10mm 15mm;
  }
}

/* Responsive */
@media (max-width: 900px) {
  .page-body { grid-template-columns: 1fr; }
  .sidebar-column { order: -1; }
  .invoice-doc { padding: 24px 20px; }
  .pay-form-grid { grid-template-columns: 1fr; }
}
</style>
