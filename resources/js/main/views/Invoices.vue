<template>
  <div class="invoices-page">

    <!-- ── Page Header ── -->
    <div class="page-header">
      <div class="header-left">
        <h1 class="page-title">Invoices</h1>
        <router-link :to="{ name: 'admin.invoices.recurring' }" class="recurring-link">Recurring Invoices →</router-link>
      </div>
      <div class="header-badges">
        <div class="hdr-badge paid-badge">
          <span class="badge-label">Paid Invoices</span>
          <span class="badge-val">{{ formatCurrency(stats.paid_amount || 0) }}</span>
        </div>
        <div class="hdr-badge pastdue-badge">
          <span class="badge-label">Past Due Invoices</span>
          <span class="badge-val">{{ formatCurrency(stats.unpaid_amount || 0) }}</span>
        </div>
        <div class="hdr-badge outstanding-badge">
          <span class="badge-label">Outstanding Invoices</span>
          <span class="badge-val">{{ formatCurrency(stats.total_amount || 0) }}</span>
        </div>
      </div>
    </div>

    <!-- ── 5 Status Widgets ── -->
    <div class="status-widgets">
      <div v-for="w in statusWidgets" :key="w.key" class="status-widget" :style="{ borderTopColor: w.color }">
        <div class="widget-header">
          <span class="widget-label" :style="{ color: w.color }">{{ w.label }}</span>
          <span class="widget-pct">{{ w.pct }}%</span>
        </div>
        <div class="widget-count">{{ w.count }} / {{ stats.total || 0 }}</div>
      </div>
    </div>

    <!-- ── Toolbar ── -->
    <div class="toolbar">
      <div class="toolbar-left">
        <button class="btn-create" @click="showCreateDrawer = true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Create New Invoice
        </button>
        <button class="btn-batch" @click="showBatchModal = true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Batch Payments
        </button>
        <button class="btn-icon-toolbar" title="Export"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></button>
      </div>
      <div class="toolbar-right">
        <button class="btn-filters" :class="{ active: showFilters }" @click="showFilters = !showFilters">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
          Filters
        </button>
      </div>
    </div>

    <!-- ── Filters Row ── -->
    <transition name="slide-down">
      <div class="filters-row" v-if="showFilters">
        <a-select v-model:value="filters.status" placeholder="All statuses" allowClear size="small" style="width:160px" @change="loadInvoices">
          <a-select-option value="">All</a-select-option>
          <a-select-option value="draft">Draft</a-select-option>
          <a-select-option value="unpaid">Unpaid</a-select-option>
          <a-select-option value="paid">Paid</a-select-option>
          <a-select-option value="overdue">Overdue</a-select-option>
          <a-select-option value="partially_paid">Partially Paid</a-select-option>
          <a-select-option value="cancelled">Cancelled</a-select-option>
        </a-select>
        <a-input-search v-model:value="filters.search" placeholder="Search invoices..." size="small" style="width:240px" @search="loadInvoices" allow-clear />
        <a-select v-model:value="filters.perPage" size="small" style="width:70px" @change="loadInvoices">
          <a-select-option :value="10">10</a-select-option>
          <a-select-option :value="25">25</a-select-option>
          <a-select-option :value="50">50</a-select-option>
          <a-select-option :value="100">100</a-select-option>
        </a-select>
      </div>
    </transition>

    <!-- ── Split Pane ── -->
    <div class="split-pane" :class="{ 'has-panel': activeInvoice }">

      <!-- LEFT: Invoice List -->
      <div class="list-pane">
        <div class="list-table-wrapper">
          <table class="inv-table">
            <thead>
              <tr>
                <th class="th-num">Invoice #</th>
                <th class="th-amount">Amount</th>
                <th class="th-tax">Total Tax</th>
                <th class="th-date">Date</th>
                <th class="th-customer">Customer</th>
                <th class="th-project">Project</th>
                <th class="th-tags">Tags</th>
                <th class="th-duedate">Due Date</th>
                <th class="th-status">Status</th>
                <th class="th-actions"></th>
              </tr>
            </thead>
            <tbody v-if="!loading">
              <tr
                v-for="inv in invoices"
                :key="inv.id"
                class="inv-row"
                @click="viewInvoice(inv)"
              >
                <td class="td-num">
                  <span class="inv-number-link" @click.stop="viewInvoice(inv)">{{ inv.number }}</span>
                </td>
                <td class="td-amount">{{ formatCurrency(inv.total) }}</td>
                <td class="td-tax">{{ formatCurrency(inv.tax) }}</td>
                <td class="td-date">{{ formatDate(inv.date) }}</td>
                <td class="td-customer" @click.stop>
                  <router-link
                    v-if="inv.client?.id"
                    :to="{ name: 'admin.customers.view', params: { id: inv.client.id } }"
                    class="client-link"
                  >
                    {{ inv.client.company }}
                  </router-link>
                  <span v-else>{{ inv.client?.company || '—' }}</span>
                </td>
                <td class="td-project">
                  <span v-if="inv.project" class="project-chip">{{ inv.project.name }}</span>
                  <span v-else class="text-muted">—</span>
                </td>
                <td class="td-tags">
                  <span v-if="inv.tags" class="tags-cell">
                    <span v-for="tag in String(inv.tags).split(',')" :key="tag" class="tag-chip">{{ tag.trim() }}</span>
                  </span>
                  <span v-else class="text-muted">—</span>
                </td>
                <td class="td-duedate" :class="{ 'text-overdue': isOverdue(inv) }">{{ formatDate(inv.duedate) }}</td>
                <td class="td-status">
                  <span class="status-badge" :class="'status-' + inv.status">
                    {{ statusLabel(inv.status) }}
                  </span>
                </td>
                <td class="td-actions" @click.stop>
                  <a-dropdown :trigger="['click']" placement="bottomRight">
                    <button class="three-dots-btn" title="Actions">
                      <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
                    </button>
                    <template #overlay>
                      <a-menu>
                        <a-menu-item key="view" @click="viewInvoice(inv)">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="margin-right:6px;vertical-align:middle"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                          View Invoice
                        </a-menu-item>
                        <a-menu-item key="edit" @click="viewInvoice(inv)">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="margin-right:6px;vertical-align:middle"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                          Edit Invoice
                        </a-menu-item>
                        <a-menu-divider />
                        <a-menu-item key="delete" @click="deleteInvoice(inv.id)" style="color:#ef4444">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" style="margin-right:6px;vertical-align:middle"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                          Delete
                        </a-menu-item>
                      </a-menu>
                    </template>
                  </a-dropdown>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="loading" class="list-loading">
            <a-spin />
            <span>Loading invoices...</span>
          </div>

          <div v-if="!loading && !invoices.length" class="list-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" width="48" height="48"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <p>No invoices found</p>
          </div>
        </div>

        <!-- Pagination -->
        <div class="list-pagination" v-if="pagination.total > filters.perPage">
          <a-pagination
            v-model:current="pagination.current"
            :pageSize="pagination.pageSize"
            :total="pagination.total"
            size="small"
            :show-size-changer="false"
            @change="(page) => { pagination.current = page; loadInvoices(); }"
          />
        </div>
      </div>

      <!-- RIGHT: Invoice Details Panel -->
      <transition name="slide-right">
        <div class="detail-pane" v-if="activeInvoice">

          <!-- Detail Tabs + icons -->
          <div class="detail-tabs-bar">
            <div class="detail-tabs">
              <button v-for="tab in detailTabs" :key="tab.key"
                class="d-tab" :class="{ active: activeTab === tab.key }"
                @click="activeTab = tab.key">
                {{ tab.label }}
              </button>
            </div>
            <div class="detail-tab-icons">
              <button class="d-icon-btn" title="Send email"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></button>
              <button class="d-icon-btn" title="View invoice"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
              <button class="d-icon-btn" title="Close panel" @click="activeInvoice = null"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="15 18 9 12 15 6"/></svg></button>
            </div>
          </div>

          <!-- Detail Tab Content -->
          <div class="detail-content" v-if="!detailLoading">

            <!-- INVOICE TAB -->
            <div v-if="activeTab === 'invoice'" class="tab-invoice">
              <!-- Status + Actions Row -->
              <div class="inv-actions-row">
                <span class="status-badge" :class="'status-' + activeInvoice.status">
                  {{ statusLabel(activeInvoice.status) }}
                </span>
                <div class="action-btns">
                  <button class="action-icon-btn" title="Edit invoice" @click="editInvoice(activeInvoice)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </button>
                  <div class="action-dropdown-wrap">
                    <a-dropdown :trigger="['click']">
                      <button class="action-icon-btn" title="PDF options">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                        <svg viewBox="0 0 24 24" fill="currentColor" width="10" height="10"><path d="M7 10l5 5 5-5z"/></svg>
                      </button>
                      <template #overlay>
                        <a-menu>
                          <a-menu-item key="pdf">Download PDF</a-menu-item>
                          <a-menu-item key="print">Print</a-menu-item>
                        </a-menu>
                      </template>
                    </a-dropdown>
                  </div>
                  <button class="action-icon-btn" title="Send to client" @click="sendInvoice(activeInvoice)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                  </button>
                  <div class="action-dropdown-wrap">
                    <a-dropdown :trigger="['click']">
                      <button class="action-icon-btn action-einvoice">
                        e-Invoice
                        <svg viewBox="0 0 24 24" fill="currentColor" width="10" height="10"><path d="M7 10l5 5 5-5z"/></svg>
                      </button>
                      <template #overlay>
                        <a-menu>
                          <a-menu-item key="einv-send">Send e-Invoice</a-menu-item>
                          <a-menu-item key="einv-download">Download e-Invoice</a-menu-item>
                        </a-menu>
                      </template>
                    </a-dropdown>
                  </div>
                  <div class="action-dropdown-wrap">
                    <a-dropdown :trigger="['click']">
                      <button class="action-icon-btn action-more">
                        More
                        <svg viewBox="0 0 24 24" fill="currentColor" width="10" height="10"><path d="M7 10l5 5 5-5z"/></svg>
                      </button>
                      <template #overlay>
                        <a-menu>
                          <a-menu-item key="mark_paid" @click="markPaid(activeInvoice)">Mark as Paid</a-menu-item>
                          <a-menu-item key="copy">Copy Invoice</a-menu-item>
                          <a-menu-item key="credit">Credit Invoice</a-menu-item>
                          <a-menu-divider />
                          <a-menu-item key="delete" @click="deleteInvoice(activeInvoice.id)" style="color: #ef4444">Delete Invoice</a-menu-item>
                        </a-menu>
                      </template>
                    </a-dropdown>
                  </div>
                  <button class="btn-payment" @click="showPaymentPanel = !showPaymentPanel">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Payment
                  </button>
                </div>
              </div>

              <!-- Payment Recording Panel -->
              <transition name="fade">
                <div v-if="showPaymentPanel" class="payment-panel">
                  <h3 class="pay-title">Record Payment for {{ activeInvoice.number }}</h3>
                  <div class="pay-grid">
                    <div class="pay-field">
                      <label class="pay-label"><span class="req">*</span> Amount Received</label>
                      <input type="number" class="pay-input" v-model="paymentForm.amount" :placeholder="formatCurrency(activeInvoice.total)" min="0.01" step="0.01" />
                    </div>
                    <div class="pay-field">
                      <label class="pay-label">Transaction ID</label>
                      <input type="text" class="pay-input" v-model="paymentForm.transactionid" placeholder="Optional" />
                    </div>
                    <div class="pay-field">
                      <label class="pay-label"><span class="req">*</span> Payment Date</label>
                      <input type="date" class="pay-input" v-model="paymentForm.date" />
                    </div>
                    <div class="pay-field">
                      <label class="pay-label">Leave a note</label>
                      <textarea class="pay-input pay-textarea" v-model="paymentForm.note" placeholder="Admin Note" rows="3"></textarea>
                    </div>
                    <div class="pay-field pay-field-full">
                      <label class="pay-label"><span class="req">*</span> Payment Mode</label>
                      <select class="pay-select" v-model="paymentForm.paymentmode">
                        <option value="">Nothing selected</option>
                        <option value="Bank">Bank Transfer</option>
                        <option value="Cash">Cash</option>
                        <option value="PayPal">PayPal</option>
                        <option value="Stripe">Stripe</option>
                        <option value="Cheque">Cheque</option>
                        <option value="Credit Card">Credit Card</option>
                      </select>
                    </div>
                  </div>
                  <label class="pay-checkbox-label">
                    <input type="checkbox" v-model="paymentForm.noEmail" />
                    Do not send invoice payment recorded email to customer contacts
                  </label>
                  <div class="pay-actions">
                    <button class="pay-cancel-btn" @click="showPaymentPanel = false">Cancel</button>
                    <button class="pay-save-btn" :disabled="savingPayment" @click="recordPayment">
                      <a-spin v-if="savingPayment" size="small" />
                      <span v-else>Save</span>
                    </button>
                  </div>
                </div>
              </transition>

              <!-- Invoice Document -->
              <div class="inv-document" v-if="activeInvoiceDetail">
                <div v-if="activeInvoiceDetail.project_id" class="inv-project-link">
                  This invoice is related to project: <a href="#">{{ activeInvoiceDetail.project?.name || 'Project #' + activeInvoiceDetail.project_id }}</a>
                </div>

                <div class="inv-header-doc">
                  <div class="inv-from">
                    <div class="inv-from-name">{{ companyName }}</div>
                    <div class="inv-from-addr">{{ companyAddress }}</div>
                  </div>
                  <div class="inv-to">
                    <div class="inv-to-label">Bill To</div>
                    <router-link
                      v-if="activeInvoiceDetail.client?.id"
                      :to="{ name: 'admin.customers.view', params: { id: activeInvoiceDetail.client.id } }"
                      class="client-link"
                    >
                      {{ activeInvoiceDetail.client.company }}
                    </router-link>
                    <a v-else class="inv-to-name">{{ activeInvoiceDetail.client?.company || '—' }}</a>
                  </div>
                </div>

                <div class="inv-number-display">
                  <span class="inv-num-big">{{ activeInvoiceDetail.number }}</span>
                  <div class="inv-meta">
                    <div class="inv-meta-row"><span>Invoice Date:</span> {{ formatDate(activeInvoiceDetail.date) }}</div>
                    <div class="inv-meta-row"><span>Due Date:</span> {{ formatDate(activeInvoiceDetail.duedate) }}</div>
                    <div v-if="activeInvoiceDetail.project" class="inv-meta-row"><span>Project:</span> {{ activeInvoiceDetail.project?.name }}</div>
                  </div>
                </div>

                <!-- Items Table -->
                <table class="items-table" v-if="activeInvoiceDetail.items && activeInvoiceDetail.items.length">
                  <thead>
                    <tr>
                      <th style="width:30px">#</th>
                      <th>Item</th>
                      <th style="width:60px;text-align:right">Qty</th>
                      <th style="width:80px;text-align:right">Rate</th>
                      <th style="width:60px;text-align:right">Tax</th>
                      <th style="width:80px;text-align:right">Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, idx) in activeInvoiceDetail.items" :key="item.id">
                      <td>{{ idx + 1 }}</td>
                      <td>
                        <div class="item-name">{{ item.description }}</div>
                        <div v-if="item.long_description" class="item-desc">{{ item.long_description }}</div>
                      </td>
                      <td style="text-align:right">{{ item.qty || 1 }}</td>
                      <td style="text-align:right">{{ formatCurrency(item.rate) }}</td>
                      <td style="text-align:right">{{ item.tax || 0 }}%</td>
                      <td style="text-align:right">{{ formatCurrency((item.qty || 1) * (item.rate || 0)) }}</td>
                    </tr>
                  </tbody>
                </table>

                <!-- No items fallback -->
                <div v-else class="no-items-notice">
                  <div style="color:#64748b;font-size:13px;padding:16px 0">No line items added to this invoice.</div>
                </div>

                <!-- Totals -->
                <div class="inv-totals">
                  <div class="total-row"><span>Sub Total</span><span>{{ formatCurrency(activeInvoiceDetail.subtotal) }}</span></div>
                  <div class="total-row" v-if="activeInvoiceDetail.tax"><span>Tax</span><span>{{ formatCurrency(activeInvoiceDetail.tax) }}</span></div>
                  <div class="total-row" v-if="activeInvoiceDetail.discount_val"><span>Discount</span><span>-{{ formatCurrency(activeInvoiceDetail.discount_val) }}</span></div>
                  <div class="total-row total-bold"><span>Total</span><span>{{ formatCurrency(activeInvoiceDetail.total) }}</span></div>
                  <div class="total-row total-due"><span>Amount Due</span><span>{{ formatCurrency(activeInvoiceDetail.total) }}</span></div>
                </div>

                <!-- Notes/Terms -->
                <div v-if="activeInvoiceDetail.notes" class="inv-notes">
                  <div class="inv-notes-title">Terms &amp; Conditions</div>
                  <p>{{ activeInvoiceDetail.notes }}</p>
                </div>

                <!-- Payments Made -->
                <div v-if="activeInvoiceDetail.payments && activeInvoiceDetail.payments.length" class="inv-payments-section">
                  <div class="section-divider">Payments</div>
                  <table class="payments-mini-table">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>Mode</th>
                        <th>Transaction ID</th>
                        <th style="text-align:right">Amount</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="p in activeInvoiceDetail.payments" :key="p.id">
                        <td>{{ formatDate(p.date) }}</td>
                        <td>{{ p.paymentmode || '—' }}</td>
                        <td>{{ p.transactionid || '—' }}</td>
                        <td style="text-align:right;color:#22c55e;font-weight:600">{{ formatCurrency(p.amount) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div v-else-if="detailLoading" class="detail-loading">
                <a-spin />
                <span>Loading invoice details...</span>
              </div>
            </div>

            <!-- TASKS TAB -->
            <div v-if="activeTab === 'tasks'" class="tab-tasks">
              <div v-if="!relatedTasks.length" class="tab-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" width="40" height="40"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                <p>No tasks linked to this invoice</p>
              </div>
              <div v-else class="task-list">
                <div v-for="t in relatedTasks" :key="t.id" class="task-item">
                  <span class="task-priority" :class="'priority-' + t.priority?.toLowerCase()">{{ t.priority }}</span>
                  <span class="task-name">{{ t.name }}</span>
                  <span class="task-status-badge">{{ t.status }}</span>
                </div>
              </div>
            </div>

            <!-- ACTIVITY LOG TAB -->
            <div v-if="activeTab === 'activity'" class="tab-activity">
              <div v-if="!activityLogs.length" class="tab-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" width="40" height="40"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <p>No activity logged yet</p>
              </div>
              <div v-else class="activity-list">
                <div v-for="log in activityLogs" :key="log.id" class="activity-item">
                  <div class="activity-dot"></div>
                  <div class="activity-body">
                    <div class="activity-desc">{{ log.description }}</div>
                    <div class="activity-meta">{{ log.user_name }} · {{ formatDate(log.created_at) }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- REMINDERS TAB -->
            <div v-if="activeTab === 'reminders'" class="tab-reminders">
              <div class="tab-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" width="40" height="40"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                <p>No reminders set</p>
              </div>
            </div>

            <!-- NOTES TAB -->
            <div v-if="activeTab === 'notes'" class="tab-notes">
              <div v-if="activeInvoiceDetail && activeInvoiceDetail.notes">
                <div class="note-card">{{ activeInvoiceDetail.notes }}</div>
              </div>
              <div v-else class="tab-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" width="40" height="40"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <p>No notes added</p>
              </div>
            </div>

          </div>

          <div v-else class="detail-loading">
            <a-spin />
            <span>Loading...</span>
          </div>
        </div>
      </transition>
    </div>

    <!-- ── Create Invoice Drawer ── -->
    <a-drawer
      v-model:open="showCreateDrawer"
      title="Create New Invoice"
      placement="right"
      :width="1100"
    >
      <a-form layout="vertical" :model="invoiceForm" @finish="createInvoice">
        <!-- Two-column settings section -->
        <div class="form-settings-grid">
          
          <!-- Column 1 -->
          <div class="settings-col">
            <a-form-item label="Customer" name="client_id" :rules="[{required:true,message:'Select a customer'}]">
              <a-select v-model:value="invoiceForm.client_id" placeholder="Select customer..." style="width:100%" showSearch optionFilterProp="children" @change="onClientChange">
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
                      <a-textarea v-model:value="invoiceForm.billing_street" :rows="2" placeholder="Street Address" />
                    </a-form-item>
                    <div class="address-grid-row">
                      <a-form-item label="City"><a-input v-model:value="invoiceForm.billing_city" /></a-form-item>
                      <a-form-item label="State"><a-input v-model:value="invoiceForm.billing_state" /></a-form-item>
                    </div>
                    <div class="address-grid-row">
                      <a-form-item label="Zip Code"><a-input v-model:value="invoiceForm.billing_zip" /></a-form-item>
                      <a-form-item label="Country"><a-input v-model:value="invoiceForm.billing_country" /></a-form-item>
                    </div>
                  </div>
                  <!-- Shipping Address -->
                  <div class="address-section">
                    <div class="address-title">Ship To</div>
                    <a-form-item label="Street">
                      <a-textarea v-model:value="invoiceForm.shipping_street" :rows="2" placeholder="Street Address" />
                    </a-form-item>
                    <div class="address-grid-row">
                      <a-form-item label="City"><a-input v-model:value="invoiceForm.shipping_city" /></a-form-item>
                      <a-form-item label="State"><a-input v-model:value="invoiceForm.shipping_state" /></a-form-item>
                    </div>
                    <div class="address-grid-row">
                      <a-form-item label="Zip Code"><a-input v-model:value="invoiceForm.shipping_zip" /></a-form-item>
                      <a-form-item label="Country"><a-input v-model:value="invoiceForm.shipping_country" /></a-form-item>
                    </div>
                  </div>
                </div>
              </transition>
            </div>

            <a-form-item label="Project" name="project_id">
              <a-select v-model:value="invoiceForm.project_id" placeholder="Select project..." style="width:100%" allowClear>
                <a-select-option v-for="p in projectOptions" :key="p.id" :value="p.id">{{ p.name }}</a-select-option>
              </a-select>
            </a-form-item>

            <a-form-item label="Allowed Payment Modes">
              <a-checkbox-group v-model:value="invoiceForm.allowed_payment_modes_list">
                <a-checkbox value="Bank">Bank Transfer</a-checkbox>
                <a-checkbox value="Stripe Checkout">Stripe Checkout</a-checkbox>
              </a-checkbox-group>
            </a-form-item>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
              <a-form-item label="Currency" name="currency" :rules="[{required:true}]">
                <a-select v-model:value="invoiceForm.currency" style="width:100%">
                  <a-select-option value="USD">USD</a-select-option>
                  <a-select-option value="EUR">EUR</a-select-option>
                  <a-select-option value="GBP">GBP</a-select-option>
                  <a-select-option value="CAD">CAD</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Sale Agent">
                <a-select v-model:value="invoiceForm.sale_agent" style="width:100%" allowClear>
                  <a-select-option v-for="s in staffOptions" :key="s.id" :value="s.name">{{ s.name }}</a-select-option>
                </a-select>
              </a-form-item>
            </div>
          </div>

          <!-- Column 2 -->
          <div class="settings-col">
            <a-form-item label="Invoice Number" name="number" :rules="[{required:true,message:'Invoice Number is required'}]">
              <a-input v-model:value="invoiceForm.number" placeholder="INV-000001" style="width:100%" />
            </a-form-item>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
              <a-form-item label="Invoice Date" name="date" :rules="[{required:true,message:'Required'}]">
                <a-date-picker v-model:value="invoiceForm.date" style="width:100%" value-format="YYYY-MM-DD" />
              </a-form-item>
              <a-form-item label="Due Date" name="duedate" :rules="[{required:true,message:'Required'}]">
                <a-date-picker v-model:value="invoiceForm.duedate" style="width:100%" value-format="YYYY-MM-DD" />
              </a-form-item>
            </div>

            <a-form-item style="margin-bottom:8px">
              <a-checkbox v-model:checked="invoiceForm.prevent_overdue_reminders">
                Prevent sending overdue reminders for this invoice
              </a-checkbox>
            </a-form-item>

            <a-form-item label="Tags">
              <a-input v-model:value="invoiceForm.tags" placeholder="Comma separated tags..." />
            </a-form-item>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
              <a-form-item label="Recurring Invoice?">
                <a-select v-model:value="invoiceForm.recurring_type" style="width:100%">
                  <a-select-option value="no">No</a-select-option>
                  <a-select-option value="daily">Daily</a-select-option>
                  <a-select-option value="weekly">Weekly</a-select-option>
                  <a-select-option value="monthly">Monthly</a-select-option>
                  <a-select-option value="quarterly">Quarterly</a-select-option>
                  <a-select-option value="yearly">Yearly</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Discount Type">
                <a-select v-model:value="invoiceForm.discount_type" style="width:100%" @change="recalc">
                  <a-select-option value="no_discount">No discount</a-select-option>
                  <a-select-option value="before_tax">Before Tax</a-select-option>
                  <a-select-option value="after_tax">After Tax</a-select-option>
                </a-select>
              </a-form-item>
            </div>

            <a-form-item label="Admin Note" style="margin-bottom:0">
              <a-textarea v-model:value="invoiceForm.admin_note" :rows="3" placeholder="Admin notes..." />
            </a-form-item>
          </div>

        </div>

        <a-divider style="margin: 16px 0" />

        <!-- Line Items Section -->
        <div class="line-items-section">
          <div class="line-items-header">
            <div class="qty-mode-group">
              <span style="font-weight:600;margin-right:12px;font-size:13px">Show quantity as:</span>
              <a-radio-group v-model:value="invoiceForm.qty_display_mode">
                <a-radio value="qty">Qty</a-radio>
                <a-radio value="hours">Hours</a-radio>
                <a-radio value="qty_hours">Qty/Hours</a-radio>
              </a-radio-group>
            </div>
            
            <div class="add-predefined-wrap">
              <span style="font-size:12px;color:#64748b;margin-right:8px;font-weight:500">Add catalog item:</span>
              <a-select placeholder="Select item..." style="width:220px" @change="addPredefinedItem" :value="null" showSearch optionFilterProp="children">
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
                  <th style="width:100px">{{ qtyLabel }}</th>
                  <th style="width:80px">Unit</th>
                  <th style="width:110px">Rate</th>
                  <th style="width:120px">Tax</th>
                  <th style="width:110px;text-align:right">Amount</th>
                  <th style="width:50px"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in invoiceForm.items" :key="index">
                  <td>
                    <a-input v-model:value="item.description" placeholder="Item Name..." />
                  </td>
                  <td>
                    <a-textarea v-model:value="item.long_description" placeholder="Description details..." :rows="1" />
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
                    {{ formatCurrency(item.qty * item.rate) }}
                  </td>
                  <td style="text-align:center">
                    <button type="button" class="btn-delete-row" @click="removeItemRow(index)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                    </button>
                  </td>
                </tr>
                <tr v-if="!invoiceForm.items.length">
                  <td colspan="8" class="empty-items-row">
                    No items added yet. Click Add Row or select a catalog item.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <button type="button" class="btn-add-row" @click="addItemRow">
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
              <a-textarea v-model:value="invoiceForm.client_note" :rows="3" />
            </a-form-item>
            <a-form-item label="Terms & Conditions">
              <a-textarea v-model:value="invoiceForm.terms_conditions" :rows="5" />
            </a-form-item>
          </div>

          <!-- Totals Column -->
          <div class="totals-col">
            <div class="summary-line">
              <span>Sub Total :</span>
              <span class="val">{{ formatCurrency(invoiceForm.subtotal) }}</span>
            </div>
            
            <div class="summary-line inline-input-line" v-if="invoiceForm.discount_type !== 'no_discount'">
              <div class="label-group">
                <span>Discount :</span>
                <a-input-number v-model:value="invoiceForm.discount_value_input" :min="0" size="small" style="width:75px;margin:0 6px" @change="recalc" />
                <a-select v-model:value="invoiceForm.discount_symbol" size="small" style="width:55px" @change="recalc">
                  <a-select-option value="%">%</a-select-option>
                  <a-select-option value="$">$</a-select-option>
                </a-select>
              </div>
              <span class="val text-danger">-{{ formatCurrency(invoiceForm.discount_val) }}</span>
            </div>

            <div class="summary-line inline-input-line">
              <div class="label-group">
                <span>Adjustment :</span>
                <a-input-number v-model:value="invoiceForm.adjustment" size="small" style="width:90px;margin-left:6px" @change="recalc" />
              </div>
              <span class="val">{{ formatCurrency(invoiceForm.adjustment) }}</span>
            </div>

            <div class="summary-line tax-summary-line" v-if="invoiceForm.tax > 0">
              <span>Tax :</span>
              <span class="val">{{ formatCurrency(invoiceForm.tax) }}</span>
            </div>

            <div class="summary-line grand-total-line">
              <span>Total :</span>
              <span class="val">{{ formatCurrency(invoiceForm.total) }}</span>
            </div>
          </div>

        </div>

        <div class="drawer-footer">
          <a-button @click="showCreateDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">Create Invoice</a-button>
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

export default defineComponent({
  name: 'InvoicesPage',
  setup() {
    const router  = useRouter();
    const loading = ref(false);
    const saving = ref(false);
    const savingPayment = ref(false);
    const detailLoading = ref(false);
    const invoices = ref([]);
    const stats = ref({});
    const showCreateDrawer = ref(false);
    const showBatchModal = ref(false);
    const showFilters = ref(false);
    const showPaymentPanel = ref(false);
    const clientOptions = ref([]);
    const activeInvoice = ref(null);
    const activeInvoiceDetail = ref(null);
    const activeTab = ref('invoice');
    const relatedTasks = ref([]);
    const activityLogs = ref([]);

    const companyName = ref('Company Name');
    const companyAddress = ref('');

    const detailTabs = [
      { key: 'invoice', label: 'Invoice' },
      { key: 'tasks', label: 'Tasks' },
      { key: 'activity', label: 'Activity Log' },
      { key: 'reminders', label: 'Reminders' },
      { key: 'notes', label: 'Notes' },
    ];

    const filters = reactive({ search: '', status: '', perPage: 25 });
    const pagination = reactive({ current: 1, pageSize: 25, total: 0 });

    const showAddressOverrides = ref(false);
    const projectOptions = ref([]);
    const staffOptions = ref([]);
    const catalogItems = ref([]);

    const invoiceForm = reactive({
      client_id: null,
      project_id: null,
      number: '',
      status: 'unpaid',
      date: new Date().toISOString().split('T')[0],
      duedate: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
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
      terms_conditions: `Caterpillar; and it put the Lizard as she could, and waited to see if she did not like to drop the jar for fear of killing somebody, so managed to swallow a morsel of the Lobster Quadrille, that she was as steady as ever; Yet you balanced an eel on the slate. 'Herald, read the accusation!' said.`,
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
      items: [
        { description: '', long_description: '', qty: 1, unit: 'Unit', rate: 0, tax_rate: 0 }
      ],
    });

    const paymentForm = reactive({
      amount: '',
      transactionid: '',
      date: new Date().toISOString().split('T')[0],
      paymentmode: '',
      note: '',
      noEmail: false,
    });

    const qtyLabel = computed(() => {
      if (invoiceForm.qty_display_mode === 'hours') return 'Hours';
      if (invoiceForm.qty_display_mode === 'qty_hours') return 'Qty/Hours';
      return 'Qty';
    });

    const addItemRow = () => {
      invoiceForm.items.push({
        description: '',
        long_description: '',
        qty: 1,
        unit: 'Unit',
        rate: 0,
        tax_rate: 0
      });
      recalc();
    };

    const removeItemRow = (index) => {
      invoiceForm.items.splice(index, 1);
      recalc();
    };

    const addPredefinedItem = (itemId) => {
      const item = catalogItems.value.find(i => i.id === itemId);
      if (item) {
        if (invoiceForm.items.length === 1 && !invoiceForm.items[0].description && invoiceForm.items[0].rate === 0) {
          invoiceForm.items[0] = {
            description: item.name,
            long_description: item.description || '',
            qty: 1,
            unit: item.unit || 'Unit',
            rate: parseFloat(item.rate) || 0,
            tax_rate: parseFloat(item.tax_rate) || 0
          };
        } else {
          invoiceForm.items.push({
            description: item.name,
            long_description: item.description || '',
            qty: 1,
            unit: item.unit || 'Unit',
            rate: parseFloat(item.rate) || 0,
            tax_rate: parseFloat(item.tax_rate) || 0
          });
        }
        recalc();
      }
    };

    const onClientChange = (clientId) => {
      const client = clientOptions.value.find(c => c.id === clientId);
      if (client) {
        invoiceForm.billing_street = client.billing_street || '';
        invoiceForm.billing_city = client.billing_city || '';
        invoiceForm.billing_state = client.billing_state || '';
        invoiceForm.billing_zip = client.billing_zip || '';
        invoiceForm.billing_country = client.billing_country || '';

        invoiceForm.shipping_street = client.shipping_street || '';
        invoiceForm.shipping_city = client.shipping_city || '';
        invoiceForm.shipping_state = client.shipping_state || '';
        invoiceForm.shipping_zip = client.shipping_zip || '';
        invoiceForm.shipping_country = client.shipping_country || '';
      }
    };

    const recalc = () => {
      let sub = 0;
      invoiceForm.items.forEach(item => {
        sub += (item.qty || 0) * (item.rate || 0);
      });
      invoiceForm.subtotal = parseFloat(sub.toFixed(2));

      let discountVal = 0;
      let discountPercent = 0;
      const discInput = parseFloat(invoiceForm.discount_value_input) || 0;
      
      if (invoiceForm.discount_type !== 'no_discount') {
        if (invoiceForm.discount_symbol === '%') {
          discountPercent = discInput;
          discountVal = invoiceForm.subtotal * (discountPercent / 100);
        } else {
          discountVal = discInput;
          discountPercent = invoiceForm.subtotal > 0 ? (discountVal / invoiceForm.subtotal) * 100 : 0;
        }
      }
      invoiceForm.discount_percent = parseFloat(discountPercent.toFixed(2));
      invoiceForm.discount_val = parseFloat(discountVal.toFixed(2));

      let taxAmount = 0;
      if (invoiceForm.discount_type === 'before_tax') {
        const discountFactor = invoiceForm.subtotal > 0 ? 1 - (discountVal / invoiceForm.subtotal) : 1;
        invoiceForm.items.forEach(item => {
          const itemSub = (item.qty || 0) * (item.rate || 0);
          const itemDiscounted = itemSub * discountFactor;
          taxAmount += itemDiscounted * ((item.tax_rate || 0) / 100);
        });
      } else {
        invoiceForm.items.forEach(item => {
          const itemSub = (item.qty || 0) * (item.rate || 0);
          taxAmount += itemSub * ((item.tax_rate || 0) / 100);
        });
      }
      invoiceForm.tax = parseFloat(taxAmount.toFixed(2));

      let tot = 0;
      const adj = parseFloat(invoiceForm.adjustment) || 0;
      if (invoiceForm.discount_type === 'before_tax') {
        tot = (invoiceForm.subtotal - invoiceForm.discount_val) + invoiceForm.tax + adj;
      } else if (invoiceForm.discount_type === 'after_tax') {
        tot = (invoiceForm.subtotal + invoiceForm.tax) - invoiceForm.discount_val + adj;
      } else {
        tot = invoiceForm.subtotal + invoiceForm.tax + adj;
      }
      invoiceForm.total = parseFloat(Math.max(0, tot).toFixed(2));
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

    // Status widgets matching official Perfex layout
    const statusWidgets = computed(() => {
      const total = stats.value.total || 1;
      const pct = (n) => total > 0 ? Math.round((n / total) * 10000) / 100 : 0;
      return [
        { key: 'unpaid',         label: 'Unpaid',         color: '#ef4444', count: stats.value.unpaid || 0,         pct: pct(stats.value.unpaid || 0) },
        { key: 'paid',           label: 'Paid',           color: '#22c55e', count: stats.value.paid || 0,           pct: pct(stats.value.paid || 0) },
        { key: 'partially_paid', label: 'Partially Paid', color: '#f59e0b', count: stats.value.partially_paid || 0, pct: pct(stats.value.partially_paid || 0) },
        { key: 'overdue',        label: 'Overdue',        color: '#f97316', count: stats.value.overdue || 0,        pct: pct(stats.value.overdue || 0) },
        { key: 'draft',          label: 'Draft',          color: '#94a3b8', count: stats.value.draft || 0,          pct: pct(stats.value.draft || 0) },
      ];
    });

    const loadInvoices = async () => {
      loading.value = true;
      try {
        const res = await axios.get('/api/invoices', {
          params: {
            search:   filters.search,
            status:   filters.status,
            per_page: filters.perPage,
            page:     pagination.current,
          }
        });
        invoices.value      = res.data.invoices?.data || [];
        pagination.total    = res.data.invoices?.total || 0;
        pagination.pageSize = filters.perPage;
        stats.value         = res.data.stats || {};
      } catch (e) {
        message.error('Failed to load invoices');
      } finally {
        loading.value = false;
      }
    };

    const loadClients = async () => {
      try {
        const res = await axios.get('/api/clients', { params: { per_page: 200 } });
        clientOptions.value = res.data.clients?.data || [];
      } catch {}
    };

    const selectInvoice = async (inv) => {
      activeInvoice.value = inv;
      activeTab.value = 'invoice';
      showPaymentPanel.value = false;
      detailLoading.value = true;
      activeInvoiceDetail.value = null;
      relatedTasks.value = [];
      activityLogs.value = [];

      try {
        const res = await axios.get(`/api/invoices/${inv.id}`);
        activeInvoiceDetail.value = res.data;
        // pre-fill payment amount
        paymentForm.amount = res.data.total;
      } catch {
        message.error('Failed to load invoice details');
      } finally {
        detailLoading.value = false;
      }

      // Load recent activity logs for display
      try {
        const logRes = await axios.get('/api/activity-logs', { params: { per_page: 10 } });
        activityLogs.value = logRes.data.logs?.data || [];
      } catch {}
    };

    const createInvoice = async () => {
      saving.value = true;
      try {
        recalc();
        invoiceForm.allowed_payment_modes = invoiceForm.allowed_payment_modes_list.join(',');
        
        await axios.post('/api/invoices', invoiceForm);
        message.success('Invoice created successfully');
        showCreateDrawer.value = false;
        
        // Reset form
        Object.assign(invoiceForm, {
          client_id: null,
          project_id: null,
          number: '',
          status: 'unpaid',
          date: new Date().toISOString().split('T')[0],
          duedate: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
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
          terms_conditions: `Caterpillar; and it put the Lizard as she could, and waited to see if she did not like to drop the jar for fear of killing somebody, so managed to swallow a morsel of the Lobster Quadrille, that she was as steady as ever; Yet you balanced an eel on the slate. 'Herald, read the accusation!' said.`,
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
          items: [
            { description: '', long_description: '', qty: 1, unit: 'Unit', rate: 0, tax_rate: 0 }
          ],
        });
        
        loadInvoices();
      } catch (e) {
        const errors = e.response?.data?.errors;
        if (errors) {
          message.error(Object.values(errors).flat().join(', '));
        } else {
          message.error('Failed to create invoice');
        }
      } finally {
        saving.value = false;
      }
    };

    const markPaid = async (inv) => {
      try {
        await axios.put(`/api/invoices/${inv.id}`, { status: 'paid' });
        message.success('Invoice marked as paid');
        loadInvoices();
        if (activeInvoice.value?.id === inv.id) {
          await selectInvoice(inv);
        }
      } catch {
        message.error('Failed to update invoice');
      }
    };

    const deleteInvoice = async (id) => {
      try {
        await axios.delete(`/api/invoices/${id}`);
        message.success('Invoice deleted');
        if (activeInvoice.value?.id === id) activeInvoice.value = null;
        loadInvoices();
      } catch {
        message.error('Failed to delete invoice');
      }
    };

    const viewInvoice = (inv) => {
      router.push({ name: 'admin.invoices.view', params: { id: inv.id } });
    };

    const editInvoice = (inv) => {
      router.push({ name: 'admin.invoices.view', params: { id: inv.id } });
    };

    const sendInvoice = (inv) => {
      message.info('Sending invoice ' + inv.number + ' to client...');
    };

    const recordPayment = async () => {
      if (!paymentForm.amount || !paymentForm.date) {
        message.warning('Amount and date are required');
        return;
      }
      savingPayment.value = true;
      try {
        await axios.post('/api/payments', {
          invoice_id:    activeInvoice.value.id,
          amount:        paymentForm.amount,
          date:          paymentForm.date,
          paymentmode:   paymentForm.paymentmode,
          transactionid: paymentForm.transactionid,
          note:          paymentForm.note,
        });
        message.success('Payment recorded successfully');
        showPaymentPanel.value = false;
        // Refresh the invoice details to show new payment + updated status
        await selectInvoice(activeInvoice.value);
        loadInvoices();
        // Reset form
        Object.assign(paymentForm, { amount: '', transactionid: '', date: new Date().toISOString().split('T')[0], paymentmode: '', note: '', noEmail: false });
      } catch (e) {
        message.error(e.response?.data?.message || 'Failed to record payment');
      } finally {
        savingPayment.value = false;
      }
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

    const isOverdue = (inv) => {
      if (['paid', 'cancelled'].includes(inv.status)) return false;
      return inv.duedate && new Date(inv.duedate) < new Date();
    };

    onMounted(() => {
      loadInvoices();
      loadClients();
      loadProjects();
      loadStaff();
      loadPredefinedItems();
    });

    return {
      loading, saving, savingPayment, detailLoading,
      invoices, stats, filters, pagination,
      showCreateDrawer, showBatchModal, showFilters, showPaymentPanel,
      clientOptions, activeInvoice, activeInvoiceDetail,
      activeTab, detailTabs, relatedTasks, activityLogs,
      statusWidgets, invoiceForm, paymentForm,
      companyName, companyAddress,
      
      showAddressOverrides,
      projectOptions,
      staffOptions,
      catalogItems,
      qtyLabel,
      
      loadInvoices, createInvoice, markPaid, deleteInvoice,
      editInvoice, sendInvoice, recordPayment,
      selectInvoice, recalc, statusLabel, formatCurrency, formatDate, isOverdue,
      viewInvoice, editInvoice,
      onClientChange,
      addItemRow,
      removeItemRow,
      addPredefinedItem,
    };
  }
});
</script>

<style scoped>
/* ── Base ── */
.invoices-page {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background: #f8fafc;
  min-height: 100vh;
  padding: 20px 24px;
  box-sizing: border-box;
}

/* ── Page Header ── */
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 16px;
  gap: 16px;
  flex-wrap: wrap;
}
.header-left {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.page-title {
  font-size: 22px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
  line-height: 1.2;
}
.recurring-link {
  font-size: 12.5px;
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
}
.recurring-link:hover { text-decoration: underline; }

/* Header Badges */
.header-badges {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}
.hdr-badge {
  display: flex;
  gap: 6px;
  align-items: center;
  padding: 5px 12px;
  border-radius: 5px;
  font-size: 12.5px;
  font-weight: 500;
  white-space: nowrap;
  border: 1px solid transparent;
}
.hdr-badge .badge-label { color: inherit; opacity: 0.8; }
.hdr-badge .badge-val   { font-weight: 700; }
.paid-badge        { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }
.pastdue-badge     { background: #fff7ed; color: #ea580c; border-color: #fed7aa; }
.outstanding-badge { background: #fffff0; color: #ca8a04; border-color: #fef08a; }

/* ── Status Widgets ── */
.status-widgets {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 10px;
  margin-bottom: 14px;
}
.status-widget {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-top-width: 3px;
  border-radius: 6px;
  padding: 12px 14px;
  cursor: pointer;
  transition: box-shadow 0.15s;
}
.status-widget:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
.widget-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 6px;
}
.widget-label { font-size: 12px; font-weight: 700; }
.widget-pct   { font-size: 11px; color: #94a3b8; font-weight: 500; }
.widget-count { font-size: 17px; font-weight: 600; color: #1e293b; }

/* ── Toolbar ── */
.toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
  gap: 8px;
}
.toolbar-left, .toolbar-right { display: flex; align-items: center; gap: 8px; }
.btn-create {
  background: #1e293b;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 8px 14px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: inherit;
  transition: background 0.15s;
}
.btn-create:hover { background: #0f172a; }
.btn-batch {
  background: #fff;
  color: #334155;
  border: 1px solid #e2e8f0;
  border-radius: 5px;
  padding: 7px 12px;
  font-size: 12.5px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: inherit;
  transition: background 0.12s;
}
.btn-batch:hover { background: #f8fafc; }
.btn-icon-toolbar {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 5px;
  padding: 7px 9px;
  cursor: pointer;
  display: flex;
  align-items: center;
  color: #64748b;
  transition: background 0.12s;
}
.btn-icon-toolbar:hover { background: #f1f5f9; }
.btn-filters {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 5px;
  padding: 7px 12px;
  font-size: 12.5px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: inherit;
  color: #475569;
  transition: all 0.12s;
}
.btn-filters:hover, .btn-filters.active { background: #f1f5f9; border-color: #cbd5e1; }

/* ── Filters Row ── */
.filters-row {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 10px 14px;
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 10px;
}
.slide-down-enter-active, .slide-down-leave-active { transition: all 0.2s ease; max-height: 200px; overflow: hidden; }
.slide-down-enter-from, .slide-down-leave-to { max-height: 0; opacity: 0; padding-top: 0; padding-bottom: 0; margin-bottom: 0; }

/* ── Split Pane ── */
.split-pane {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
}
.split-pane.has-panel {
  grid-template-columns: 1fr 1fr;
}

/* ── List Pane ── */
.list-pane {
  display: flex;
  flex-direction: column;
  border-right: 1px solid #e2e8f0;
  min-height: 500px;
}
.list-table-wrapper { flex: 1; overflow-x: auto; }

/* Invoice Table */
.inv-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.inv-table th {
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  padding: 9px 12px;
  text-align: left;
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  white-space: nowrap;
}
.inv-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}
.inv-row {
  cursor: pointer;
  transition: background 0.1s;
}
.inv-row:hover { background: #f8fafc; }
.inv-row.active { background: #eff6ff; }
.inv-number-link {
  color: #3b82f6;
  font-weight: 600;
  font-size: 12.5px;
}
.td-amount   { font-weight: 600; color: #1e293b; white-space: nowrap; }
.td-tax      { color: #64748b; font-size: 12px; white-space: nowrap; }
.td-date     { color: #64748b; white-space: nowrap; font-size: 12px; }
.td-duedate  { color: #64748b; white-space: nowrap; font-size: 12px; }
.td-customer { color: #334155; font-size: 12.5px; }
.td-project  { font-size: 12px; }
.td-tags     { font-size: 12px; }
.td-actions  { width: 40px; text-align: center; }
.text-muted  { color: #94a3b8; }
.text-overdue { color: #ef4444 !important; font-weight: 600; }

/* Project chip */
.project-chip {
  display: inline-block;
  background: #eff6ff;
  color: #3b82f6;
  border: 1px solid #bfdbfe;
  border-radius: 4px;
  padding: 2px 7px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Tag chips */
.tags-cell {
  display: flex;
  flex-wrap: wrap;
  gap: 3px;
}
.tag-chip {
  display: inline-block;
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #e2e8f0;
  border-radius: 999px;
  padding: 1px 7px;
  font-size: 10.5px;
  font-weight: 500;
  white-space: nowrap;
}

/* Three-dots button */
.three-dots-btn {
  background: none;
  border: 1px solid transparent;
  border-radius: 5px;
  padding: 4px 6px;
  cursor: pointer;
  color: #94a3b8;
  display: flex;
  align-items: center;
  transition: all 0.12s;
}
.three-dots-btn:hover {
  background: #f1f5f9;
  border-color: #e2e8f0;
  color: #475569;
}

/* Status Badges */
.status-badge {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
}
.status-unpaid         { background: #fee2e2; color: #dc2626; }
.status-paid           { background: #dcfce7; color: #16a34a; }
.status-partially_paid { background: #fef3c7; color: #d97706; }
.status-overdue        { background: #ffedd5; color: #ea580c; }
.status-draft          { background: #f1f5f9; color: #64748b; }
.status-cancelled      { background: #f1f5f9; color: #94a3b8; }

/* Empty + Loading states */
.list-loading, .list-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 60px 0;
  color: #94a3b8;
  font-size: 13px;
}
.list-pagination {
  padding: 12px 14px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
}

/* ── Detail Pane ── */
.detail-pane {
  display: flex;
  flex-direction: column;
  max-height: calc(100vh - 200px);
  overflow: hidden;
}
.slide-right-enter-active, .slide-right-leave-active { transition: all 0.25s ease; }
.slide-right-enter-from { opacity: 0; transform: translateX(40px); }
.slide-right-leave-to   { opacity: 0; transform: translateX(40px); }

/* Detail Tabs Bar */
.detail-tabs-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #e2e8f0;
  padding: 0 14px;
  background: #fff;
  flex-shrink: 0;
}
.detail-tabs { display: flex; gap: 0; }
.d-tab {
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  padding: 12px 14px;
  font-size: 12.5px;
  font-weight: 500;
  color: #64748b;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s;
  white-space: nowrap;
  margin-bottom: -1px;
}
.d-tab:hover  { color: #334155; }
.d-tab.active { color: #3b82f6; border-bottom-color: #3b82f6; font-weight: 600; }
.detail-tab-icons { display: flex; gap: 4px; }
.d-icon-btn {
  background: none;
  border: 1px solid #e2e8f0;
  border-radius: 5px;
  padding: 5px 7px;
  cursor: pointer;
  color: #64748b;
  display: flex;
  align-items: center;
  transition: all 0.12s;
}
.d-icon-btn:hover { background: #f1f5f9; border-color: #cbd5e1; color: #334155; }

/* Detail Content */
.detail-content {
  flex: 1;
  overflow-y: auto;
  padding: 0;
}

/* Invoice Tab */
.tab-invoice { padding-bottom: 24px; }

/* Actions Row */
.inv-actions-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 14px;
  border-bottom: 1px solid #f1f5f9;
  flex-wrap: wrap;
  gap: 8px;
}
.action-btns {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}
.action-icon-btn {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 5px;
  padding: 6px 8px;
  cursor: pointer;
  color: #475569;
  display: flex;
  align-items: center;
  gap: 3px;
  font-size: 12px;
  font-weight: 500;
  font-family: inherit;
  transition: all 0.12s;
}
.action-icon-btn:hover    { background: #f8fafc; border-color: #cbd5e1; }
.action-einvoice, .action-more { padding: 6px 10px; }
.action-dropdown-wrap { display: flex; }

.btn-payment {
  background: #16a34a;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 7px 14px;
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  font-family: inherit;
  transition: background 0.12s;
}
.btn-payment:hover { background: #15803d; }

/* Payment Panel */
.payment-panel {
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  padding: 16px 16px 12px;
}
.pay-title {
  font-size: 14px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 14px 0;
}
.pay-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-bottom: 12px;
}
.pay-field { display: flex; flex-direction: column; gap: 5px; }
.pay-field-full { grid-column: 1 / -1; }
.pay-label {
  font-size: 12px;
  font-weight: 600;
  color: #374151;
}
.pay-label .req { color: #ef4444; margin-right: 3px; }
.pay-input {
  border: 1px solid #d1d5db;
  border-radius: 5px;
  padding: 7px 10px;
  font-size: 13px;
  font-family: inherit;
  outline: none;
  background: #fff;
  transition: border-color 0.15s;
  width: 100%;
  box-sizing: border-box;
}
.pay-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,0.1); }
.pay-textarea { resize: vertical; min-height: 72px; }
.pay-select {
  border: 1px solid #d1d5db;
  border-radius: 5px;
  padding: 7px 10px;
  font-size: 13px;
  font-family: inherit;
  outline: none;
  background: #fff;
  width: 100%;
  cursor: pointer;
}
.pay-checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: #475569;
  margin-bottom: 12px;
  cursor: pointer;
}
.pay-actions { display: flex; justify-content: flex-end; gap: 8px; }
.pay-cancel-btn {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 5px;
  padding: 7px 16px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  font-family: inherit;
  color: #475569;
  transition: background 0.12s;
}
.pay-cancel-btn:hover { background: #f8fafc; }
.pay-save-btn {
  background: #16a34a;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 7px 20px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.12s;
  min-width: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.pay-save-btn:hover:not(:disabled) { background: #15803d; }
.pay-save-btn:disabled { opacity: 0.6; cursor: not-allowed; }

/* Invoice Document */
.inv-document { padding: 16px; }
.inv-project-link {
  font-size: 12px;
  color: #64748b;
  margin-bottom: 12px;
}
.inv-project-link a { color: #3b82f6; font-weight: 600; text-decoration: none; }
.inv-header-doc {
  display: flex;
  justify-content: space-between;
  margin-bottom: 16px;
  gap: 12px;
}
.inv-from-name { font-weight: 700; color: #1e293b; font-size: 13px; margin-bottom: 4px; }
.inv-from-addr { font-size: 11.5px; color: #64748b; line-height: 1.5; white-space: pre-line; }
.inv-to { text-align: right; }
.inv-to-label { font-size: 11px; color: #94a3b8; font-weight: 500; margin-bottom: 4px; }
.inv-to-name { color: #3b82f6; font-weight: 700; font-size: 13px; }
.client-link {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.12s;
}
.client-link:hover {
  color: #2563eb;
  text-decoration: underline;
}

.inv-number-display {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 14px;
  border-top: 1px solid #f1f5f9;
  padding-top: 12px;
}
.inv-num-big { font-size: 20px; font-weight: 700; color: #3b82f6; }
.inv-meta { text-align: right; }
.inv-meta-row { font-size: 12px; color: #64748b; }
.inv-meta-row span { font-weight: 600; color: #374151; }

/* Items Table */
.items-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12.5px;
  margin-bottom: 10px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  overflow: hidden;
}
.items-table th {
  background: #f8fafc;
  padding: 8px 10px;
  text-align: left;
  font-size: 11.5px;
  font-weight: 600;
  color: #64748b;
  border-bottom: 1px solid #e2e8f0;
}
.items-table td {
  padding: 10px 10px;
  border-bottom: 1px solid #f1f5f9;
  color: #334155;
  vertical-align: top;
}
.items-table tr:last-child td { border-bottom: none; }
.item-name { font-weight: 600; color: #1e293b; }
.item-desc { font-size: 11px; color: #64748b; margin-top: 2px; }

/* Totals */
.inv-totals {
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  overflow: hidden;
  margin-bottom: 14px;
}
.total-row {
  display: flex;
  justify-content: space-between;
  padding: 7px 12px;
  font-size: 12.5px;
  color: #334155;
  border-bottom: 1px solid #f1f5f9;
}
.total-row:last-child { border-bottom: none; }
.total-bold { font-weight: 700; background: #f8fafc; font-size: 13px; color: #1e293b; }
.total-due  { font-weight: 700; background: #fff7ed; color: #dc2626; }
.total-due span:last-child { color: #dc2626; font-size: 14px; }

/* Notes */
.inv-notes {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 12px;
  font-size: 12px;
  color: #475569;
  margin-bottom: 14px;
}
.inv-notes-title { font-weight: 700; color: #374151; margin-bottom: 6px; font-size: 12.5px; }

/* Payments Section */
.inv-payments-section { margin-top: 14px; }
.section-divider {
  font-weight: 700;
  font-size: 12px;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 8px;
  padding-bottom: 6px;
  border-bottom: 1px solid #e2e8f0;
}
.payments-mini-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
}
.payments-mini-table th {
  color: #64748b;
  font-weight: 600;
  padding: 6px 8px;
  border-bottom: 1px solid #e2e8f0;
  text-align: left;
}
.payments-mini-table td {
  padding: 8px 8px;
  border-bottom: 1px solid #f1f5f9;
  color: #334155;
}

/* ── Tabs: Tasks / Activity / etc. ── */
.tab-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 50px 20px;
  gap: 10px;
  color: #94a3b8;
  font-size: 13px;
}

/* Tasks */
.tab-tasks { padding: 12px; }
.task-list { display: flex; flex-direction: column; gap: 8px; }
.task-item {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 10px 12px;
  font-size: 12.5px;
}
.task-priority {
  font-size: 10.5px;
  font-weight: 600;
  padding: 2px 7px;
  border-radius: 4px;
  text-transform: uppercase;
}
.priority-low    { background: #f0fdf4; color: #16a34a; }
.priority-medium { background: #fef3c7; color: #d97706; }
.priority-high   { background: #fee2e2; color: #dc2626; }
.priority-urgent { background: #fdf4ff; color: #9333ea; }
.task-name { flex: 1; color: #334155; font-weight: 500; }
.task-status-badge { font-size: 11px; color: #64748b; }

/* Activity */
.tab-activity { padding: 16px; }
.activity-list { display: flex; flex-direction: column; gap: 0; }
.activity-item { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
.activity-dot { width: 8px; height: 8px; border-radius: 50%; background: #3b82f6; margin-top: 5px; flex-shrink: 0; }
.activity-body { flex: 1; }
.activity-desc { font-size: 12.5px; color: #334155; margin-bottom: 3px; }
.activity-meta { font-size: 11px; color: #94a3b8; }

/* Reminders */
.tab-reminders { padding: 12px; }

/* Notes */
.tab-notes { padding: 12px; }
.note-card { background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 12px; font-size: 13px; color: #374151; line-height: 1.6; }

/* Detail loading */
.detail-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 0;
  gap: 10px;
  color: #94a3b8;
  font-size: 13px;
}

/* Fade transition */
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ── Create Invoice Drawer ── */
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

/* Responsive */
@media (max-width: 900px) {
  .status-widgets { grid-template-columns: repeat(3, 1fr); }
  .split-pane.has-panel { grid-template-columns: 1fr; }
  .detail-pane { border-top: 1px solid #e2e8f0; }
  .header-badges { flex-direction: column; gap: 6px; align-items: flex-end; }
}
@media (max-width: 600px) {
  .status-widgets { grid-template-columns: repeat(2, 1fr); }
  .pay-grid { grid-template-columns: 1fr; }
  .inv-actions-row { flex-direction: column; align-items: flex-start; }
}
</style>
