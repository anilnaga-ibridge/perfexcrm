<template>
  <div class="customer-view-page" v-if="customer">
    
    <!-- Header / Breadcrumbs Bar -->
    <div class="breadcrumb-bar">
      <div class="flex items-center gap-2 text-sm">
        <router-link to="/admin/customers" class="breadcrumb-link">Customers</router-link>
        <span class="text-slate-400">/</span>
        <span class="breadcrumb-current">#{{ customer.id }} {{ customer.company }}</span>
      </div>
    </div>

    <!-- Layout: Left Sidebar Menu + Right Active Content Pane -->
    <div class="client-profile-layout">
      
      <!-- Left Navigation Menu -->
      <aside class="profile-sidebar no-print">
        <div class="sidebar-header border-b border-slate-100 p-4">
          <h3 class="font-bold text-slate-800 truncate mb-0 flex items-center justify-between cursor-pointer" :title="customer.company">
            <span>#{{ customer.id }} {{ customer.company }}</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12" class="text-slate-400 flex-shrink-0 ml-1.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
          </h3>
        </div>
        <nav class="sidebar-menu">
          <div 
            v-for="item in menuItems" 
            :key="item.key" 
            :class="['sidebar-menu-item', { 'sidebar-menu-item--active': activeTab === item.key }]"
            @click="activeTab = item.key"
          >
            <div class="menu-item-left">
              <span class="menu-icon" v-html="item.icon"></span>
              <span class="menu-label">{{ item.label }}</span>
            </div>
            <span v-if="item.badge" class="menu-badge">{{ item.badge }}</span>
          </div>
        </nav>
      </aside>

      <!-- Right Active Panel -->
      <main class="profile-content-wrap">
        
        <!-- TAB 1: Profile (Tabbed forms inside) -->
        <div class="card" v-if="activeTab === 'profile'">
          
          <!-- Profile Sub-Tabs Navigation -->
          <div class="profile-subtabs no-print">
            <button 
              :class="['subtab-btn', { active: profileSubTab === 'details' }]" 
              @click="profileSubTab = 'details'"
            >
              Customer Details
            </button>
            <button 
              :class="['subtab-btn', { active: profileSubTab === 'billing' }]" 
              @click="profileSubTab = 'billing'"
            >
              Billing & Shipping
            </button>
            <button 
              :class="['subtab-btn', { active: profileSubTab === 'admins' }]" 
              @click="profileSubTab = 'admins'"
            >
              Customer Admins
            </button>
          </div>

          <!-- Sub-Tab Content 1: Customer Details Form -->
          <div v-if="profileSubTab === 'details'" class="subtab-content-details">
            <form @submit.prevent="saveCustomerDetails" class="space-y-4">
              
              <div class="checkbox-field mb-4">
                <label class="flex items-start gap-2 cursor-pointer">
                  <input type="checkbox" v-model="customerForm.show_primary_contact" class="mt-1" />
                  <span class="text-xs text-slate-600 font-medium">Show primary contact full name on Invoices, Estimates, Payments, Credit Notes</span>
                </label>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                <div class="form-group">
                  <label class="input-lbl"><span class="required">*</span> Company</label>
                  <input type="text" class="input-ctrl" v-model="customerForm.company" required />
                </div>

                <div class="form-group">
                  <label class="input-lbl">VAT Number</label>
                  <input type="text" class="input-ctrl" v-model="customerForm.vat" />
                </div>

                <div class="form-group">
                  <label class="input-lbl">Phone</label>
                  <input type="text" class="input-ctrl" v-model="customerForm.phonenumber" />
                </div>

                <div class="form-group">
                  <label class="input-lbl">Website</label>
                  <div class="website-input-wrap">
                    <input type="text" class="input-ctrl" v-model="customerForm.website" />
                    <span class="globe-addon">🌐</span>
                  </div>
                </div>

                <div class="form-group">
                  <label class="input-lbl">Groups</label>
                  <a-select v-model:value="customerForm.groups" mode="multiple" placeholder="Select groups" style="width: 100%">
                    <a-select-option value="High Budget">High Budget</a-select-option>
                    <a-select-option value="Low Budget">Low Budget</a-select-option>
                    <a-select-option value="VIP">VIP</a-select-option>
                    <a-select-option value="Wholesaler">Wholesaler</a-select-option>
                  </a-select>
                </div>

                <div class="form-group">
                  <label class="input-lbl">Currency</label>
                  <select class="select-ctrl" v-model="customerForm.currency">
                    <option value="System Default">System Default</option>
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="GBP">GBP</option>
                  </select>
                </div>

                <div class="form-group">
                  <label class="input-lbl">Default Language</label>
                  <select class="select-ctrl" v-model="customerForm.default_language">
                    <option value="system_default">System Default</option>
                    <option value="english">English</option>
                    <option value="german">German</option>
                    <option value="french">French</option>
                    <option value="spanish">Spanish</option>
                  </select>
                </div>

                <div class="form-group md:col-span-2">
                  <label class="input-lbl">Address</label>
                  <textarea class="textarea-ctrl" v-model="customerForm.address" rows="3"></textarea>
                </div>

                <div class="form-group">
                  <label class="input-lbl">City</label>
                  <input type="text" class="input-ctrl" v-model="customerForm.city" />
                </div>

                <div class="form-group">
                  <label class="input-lbl">State</label>
                  <input type="text" class="input-ctrl" v-model="customerForm.state" />
                </div>

                <div class="form-group">
                  <label class="input-lbl">Zip Code</label>
                  <input type="text" class="input-ctrl" v-model="customerForm.zip" />
                </div>

                <div class="form-group">
                  <label class="input-lbl">Country</label>
                  <input type="text" class="input-ctrl" v-model="customerForm.country" />
                </div>
              </div>

              <div class="form-footer-save no-print">
                <button type="submit" class="btn-save-profile" :disabled="submitting">
                  {{ submitting ? 'Saving...' : 'Save' }}
                </button>
              </div>

            </form>
          </div>

          <!-- Sub-Tab Content 2: Billing & Shipping Address Form -->
          <div v-if="profileSubTab === 'billing'" class="subtab-content-billing">
            <form @submit.prevent="saveBillingShipping" class="space-y-6">
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- Billing Address Column -->
                <div class="space-y-4">
                  <h3 class="text-xs uppercase font-bold text-slate-600 tracking-wider pb-1 border-b border-slate-100 flex justify-between items-center">
                    <span>Billing Address</span>
                  </h3>
                  
                  <div class="form-group">
                    <label class="input-lbl">Street</label>
                    <textarea class="textarea-ctrl" v-model="customerForm.billing_street" rows="2"></textarea>
                  </div>
                  <div class="form-group">
                    <label class="input-lbl">City</label>
                    <input type="text" class="input-ctrl" v-model="customerForm.billing_city" />
                  </div>
                  <div class="form-group">
                    <label class="input-lbl">State</label>
                    <input type="text" class="input-ctrl" v-model="customerForm.billing_state" />
                  </div>
                  <div class="form-group">
                    <label class="input-lbl">Zip Code</label>
                    <input type="text" class="input-ctrl" v-model="customerForm.billing_zip" />
                  </div>
                  <div class="form-group">
                    <label class="input-lbl">Country</label>
                    <input type="text" class="input-ctrl" v-model="customerForm.billing_country" />
                  </div>
                </div>

                <!-- Shipping Address Column -->
                <div class="space-y-4">
                  <h3 class="text-xs uppercase font-bold text-slate-600 tracking-wider pb-1 border-b border-slate-100 flex justify-between items-center">
                    <span>Shipping Address</span>
                    <button type="button" class="btn-copy-address" @click="copyBillingToShipping">
                      Copy Billing Address
                    </button>
                  </h3>
                  
                  <div class="form-group">
                    <label class="input-lbl">Street</label>
                    <textarea class="textarea-ctrl" v-model="customerForm.shipping_street" rows="2"></textarea>
                  </div>
                  <div class="form-group">
                    <label class="input-lbl">City</label>
                    <input type="text" class="input-ctrl" v-model="customerForm.shipping_city" />
                  </div>
                  <div class="form-group">
                    <label class="input-lbl">State</label>
                    <input type="text" class="input-ctrl" v-model="customerForm.shipping_state" />
                  </div>
                  <div class="form-group">
                    <label class="input-lbl">Zip Code</label>
                    <input type="text" class="input-ctrl" v-model="customerForm.shipping_zip" />
                  </div>
                  <div class="form-group">
                    <label class="input-lbl">Country</label>
                    <input type="text" class="input-ctrl" v-model="customerForm.shipping_country" />
                  </div>
                </div>

              </div>

              <div class="form-footer-save no-print">
                <button type="submit" class="btn-save-profile" :disabled="submitting">
                  {{ submitting ? 'Saving...' : 'Save' }}
                </button>
              </div>

            </form>
          </div>

          <!-- Sub-Tab Content 3: Customer Admins Selector -->
          <div v-if="profileSubTab === 'admins'" class="subtab-content-admins">
            <form @submit.prevent="saveCustomerAdmins" class="space-y-4">
              <h3 class="text-xs uppercase font-bold text-slate-600 tracking-wider pb-1 border-b border-slate-100 mb-3">
                Assign Staff Members as Customer Admins
              </h3>

              <div class="space-y-3">
                <div v-for="admin in adminOptions" :key="admin.id" class="flex items-center gap-2">
                  <label class="flex items-center gap-2.5 cursor-pointer text-xs text-slate-700">
                    <input type="checkbox" v-model="admin.selected" />
                    <span class="font-medium">{{ admin.name }}</span>
                  </label>
                </div>
              </div>

              <div class="form-footer-save no-print">
                <button type="submit" class="btn-save-profile" :disabled="submitting">
                  {{ submitting ? 'Save Admins' : 'Save' }}
                </button>
              </div>
            </form>
          </div>

        </div>

        <!-- TAB 2: Contacts Management -->
        <div class="card" v-if="activeTab === 'contacts'">
          <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="section-title">Contacts</h2>
            <button class="btn-primary" @click="openContactDrawer">Add New Contact</button>
          </div>

          <table class="data-table">
            <thead>
              <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Position</th>
                <th>Phone</th>
                <th>Active</th>
                <th>Last Login</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in customer.contacts" :key="c.id" class="contact-table-row">
                <td>
                  <strong class="text-slate-800 font-semibold block">{{ c.firstname }} {{ c.lastname }}</strong>
                  <div class="contact-row-actions text-[11px] mt-1 no-print">
                    <span class="text-indigo-600 hover:underline cursor-pointer" @click="editContact(c)">Edit</span>
                    <span class="text-slate-300 mx-1.5">|</span>
                    <span class="text-rose-600 hover:underline cursor-pointer" @click="deleteContact(c.id)">Delete</span>
                  </div>
                </td>
                <td>{{ c.email }}</td>
                <td>{{ c.title || '—' }}</td>
                <td>{{ c.phonenumber || '—' }}</td>
                <td>
                  <a-switch 
                    :checked="c.active === 1 || c.active === true" 
                    @change="(checked) => toggleContactStatus(c.id, checked)"
                    size="small"
                  />
                </td>
                <td class="text-slate-500 text-xs">{{ c.last_login || 'Never' }}</td>
              </tr>
              <tr v-if="!customer.contacts || customer.contacts.length === 0">
                <td colspan="6" class="empty-cell">No contacts linked to this customer.</td>
              </tr>
            </tbody>
          </table>
          
          <div class="table-pagination-info mt-4 text-xs text-slate-500 flex justify-between items-center" v-if="customer.contacts && customer.contacts.length > 0">
            <span>Showing 1 to {{ customer.contacts.length }} of {{ customer.contacts.length }} entries</span>
            <div class="flex gap-2 font-medium">
              <span class="text-slate-400 cursor-not-allowed">Previous</span>
              <span class="text-slate-800 font-bold bg-slate-100 rounded px-1.5">1</span>
              <span class="text-slate-400 cursor-not-allowed">Next</span>
            </div>
          </div>
        </div>

        <!-- TAB 3: Notes (Rich interactive notes dashboard layout) -->
        <div class="card" v-if="activeTab === 'notes'">
          <h2 class="section-title border-b pb-2 mb-4">Notes</h2>
          
          <!-- New Note Toggle Button Container -->
          <div class="mb-4 no-print">
            <button 
              class="btn-new-note flex items-center gap-1.5 bg-[#1e293b] text-white rounded px-3.5 py-1.5 text-xs font-semibold hover:bg-slate-800 transition" 
              @click="toggleNoteForm"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              {{ showNoteForm ? 'Hide Editor' : 'New Note' }}
            </button>
          </div>

          <!-- Note insertion/editing form -->
          <div v-if="showNoteForm" class="new-note-container mb-6 no-print">
            <div class="form-group">
              <textarea 
                class="textarea-ctrl text-xs" 
                v-model="newNoteText" 
                placeholder="Note description" 
                rows="4"
              ></textarea>
            </div>
            <div class="flex justify-end gap-2 mt-2">
              <button class="btn-outline" style="padding: 5px 12px; font-size: 12.5px;" @click="toggleNoteForm">
                Cancel
              </button>
              <button class="btn-save-note" @click="saveNote">
                {{ editingNoteId ? 'Update Note' : 'Save' }}
              </button>
            </div>
          </div>

          <!-- Notes Search & table options toolbar -->
          <div class="table-toolbar no-print flex justify-between items-center mb-4">
            <div class="toolbar-left flex items-center gap-2">
              <select class="select-ctrl select-inline-num" style="width:70px; padding: 4px 8px; font-size: 12.5px;">
                <option value="25">25</option>
                <option value="50">50</option>
              </select>
              <button class="btn-outline" style="padding: 4px 10px; font-size: 12.5px;">
                Export
              </button>
            </div>
            <div class="toolbar-right">
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13" class="text-slate-400">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                  </svg>
                </span>
                <input 
                  type="text" 
                  class="input-ctrl pl-8 text-xs" 
                  style="width:220px;" 
                  placeholder="Search..." 
                  v-model="notesSearchQuery"
                />
              </div>
            </div>
          </div>

          <!-- Notes list table -->
          <table class="data-table">
            <thead>
              <tr>
                <th>Description</th>
                <th>Added From</th>
                <th>Date Added</th>
                <th style="width:90px">Options</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="note in filteredNotes" :key="note.id" class="contact-table-row">
                <td class="text-xs text-slate-700 leading-relaxed font-medium whitespace-pre-wrap">{{ note.content }}</td>
                <td>
                  <a class="text-indigo-600 hover:underline cursor-pointer font-medium text-xs">
                    {{ note.creator }}
                  </a>
                </td>
                <td class="text-slate-500 text-xs">{{ note.date }}</td>
                <td class="no-print">
                  <div class="flex items-center gap-2.5">
                    <button @click="editNote(note)" class="text-slate-500 hover:text-indigo-600 transition" title="Edit Note">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"></path>
                      </svg>
                    </button>
                    <button @click="deleteNote(note.id)" class="text-slate-500 hover:text-rose-600 transition" title="Delete Note">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        <line x1="10" y1="11" x2="10" y2="17"></line>
                        <line x1="14" y1="11" x2="14" y2="17"></line>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredNotes.length === 0">
                <td colspan="4" class="empty-cell text-center">No matching notes found.</td>
              </tr>
            </tbody>
          </table>

          <div class="table-pagination-info mt-4 text-xs text-slate-500 flex justify-between items-center" v-if="filteredNotes.length > 0">
            <span>Showing 1 to {{ filteredNotes.length }} of {{ customerNotes.length }} entries</span>
            <div class="flex items-center gap-1 font-medium">
              <span class="text-slate-500 hover:text-slate-800 cursor-pointer px-2 py-1">Previous</span>
              <span class="text-slate-800 font-bold bg-slate-200 rounded-sm px-2.5 py-1">1</span>
              <span class="text-slate-500 hover:text-slate-800 cursor-pointer px-2 py-1">Next</span>
            </div>
          </div>
        </div>

        <!-- TAB 4: Statement -->
        <div class="card statement-card" v-if="activeTab === 'statement'">
          <!-- Statement Top Actions Toolbar -->
          <div class="statement-toolbar">
            <div class="flex items-center gap-3">
              <span class="statement-period-label">PERIOD:</span>
              <select class="statement-period-select" v-model="statementPeriod">
                <option value="today">Today</option>
                <option value="this_week">This Week</option>
                <option value="this_month">This Month</option>
                <option value="last_month">Last Month</option>
                <option value="this_year">This Year</option>
                <option value="last_year">Last Year</option>
                <option value="period">Period</option>
              </select>
              
              <!-- Custom Date Range picker if 'period' selected -->
              <div v-if="statementPeriod === 'period'" class="flex items-center gap-2">
                <input type="date" class="statement-date-input" v-model="customStartDate" />
                <span class="text-slate-400 text-sm">to</span>
                <input type="date" class="statement-date-input" v-model="customEndDate" />
              </div>
            </div>
            
            <div class="flex items-center gap-1.5">
              <button class="stmt-icon-btn" title="Print" @click="printStatement">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="16" height="16"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
              </button>
              <button class="stmt-icon-btn" title="View PDF">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="16" height="16"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
              </button>
              <button class="stmt-icon-btn" title="Mail Statement">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="16" height="16"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
              </button>
            </div>
          </div>

          <!-- Document Container for Printing / Layout -->
          <div class="statement-document">
            <h2 class="text-base font-bold text-slate-800 mb-6">Customer Statement For {{ customer.company }}</h2>
            
            <!-- Address Headers -->
            <div class="grid grid-cols-2 gap-4 mb-8 text-xs text-slate-600">
              <!-- Recipient (Left) -->
              <div>
                <strong class="text-slate-800 font-bold block mb-1">To:</strong>
                <strong class="text-slate-800 font-bold block mb-0.5">{{ customer.company }}</strong>
                <div v-if="customer.address">{{ customer.address }}</div>
                <div>
                  <span v-if="customer.city">{{ customer.city }}, </span>
                  <span v-if="customer.state">{{ customer.state }}</span>
                </div>
                <div v-if="customer.country">{{ customer.country }} <span v-if="customer.zip">[{{ customer.zip }}]</span></div>
              </div>
              
              <!-- Sender (Right) -->
              <div class="text-right">
                <strong class="text-slate-800 font-bold block mb-0.5 text-slate-800">Perfex INC</strong>
                <div>172 Ivy Club Gottliebfurt</div>
                <div>New Heaven</div>
                <div>Canada [CA] 2364</div>
              </div>
            </div>
            
            <!-- Account Summary Row -->
            <div class="grid grid-cols-2 gap-4 mb-8 text-xs">
              <div></div>
              
              <!-- Summary Details (Right) -->
              <div class="bg-slate-50 border border-slate-100 rounded p-4 text-slate-600">
                <h3 class="text-sm font-bold text-slate-800 mb-0.5 text-right">Account Summary</h3>
                <div class="text-[10px] text-slate-400 font-semibold mb-3 text-right">
                  {{ statementData.start }} To {{ statementData.end }}
                </div>
                
                <div class="space-y-1.5 text-xs">
                  <div class="flex justify-between">
                    <span>Beginning Balance:</span>
                    <span class="font-medium text-slate-800">{{ formatCurrency(statementData.beginningBalance) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Invoiced Amount:</span>
                    <span class="font-medium text-slate-800">{{ formatCurrency(statementData.invoicedAmount) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Amount Paid:</span>
                    <span class="font-medium text-slate-800">{{ formatCurrency(statementData.amountPaid) }}</span>
                  </div>
                  <div class="flex justify-between border-t border-slate-200 pt-1.5 font-bold text-slate-900">
                    <span>Balance Due:</span>
                    <span>{{ formatCurrency(statementData.balanceDue) }}</span>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Table description -->
            <div class="text-xs text-slate-500 font-medium text-center mb-4">
              Showing all invoices and payments between {{ statementData.start }} and {{ statementData.end }}
            </div>
            
            <!-- Main Statement Table -->
            <table class="data-table">
              <thead>
                <tr>
                  <th style="width: 100px;">Date</th>
                  <th>Details</th>
                  <th class="text-right" style="width: 120px;">Amount</th>
                  <th class="text-right" style="width: 120px;">Payments</th>
                  <th class="text-right" style="width: 120px;">Balance</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, idx) in statementData.items" :key="idx" :class="{ 'bg-slate-50/50': item.isBeginning }">
                  <td class="text-slate-500 text-xs">{{ item.date }}</td>
                  <td class="text-xs text-slate-700 leading-relaxed">
                    <span v-if="item.isBeginning" class="font-semibold text-slate-500">{{ item.details }}</span>
                    
                    <span v-else-if="item.type === 'invoice'">
                      Invoice 
                      <router-link :to="{ name: 'admin.invoices.view', params: { id: item.id } }" class="link-blue font-semibold">
                        {{ item.number }}
                      </router-link> 
                      - due on {{ item.details.split(' - due on ')[1] }}
                    </span>
                    
                    <span v-else-if="item.type === 'payment'">
                      Payment (#{{ item.id }}) to invoice 
                      <router-link :to="{ name: 'admin.invoices.view', params: { id: item.invoice_id } }" class="link-blue font-semibold">
                        {{ item.details.split(' to invoice ')[1] }}
                      </router-link>
                    </span>
                  </td>
                  <td class="text-right text-slate-800 font-medium">
                    {{ item.amount !== null && item.amount !== undefined ? formatCurrency(item.amount) : '' }}
                  </td>
                  <td class="text-right text-green-600 font-semibold">
                    {{ item.payments !== null && item.payments !== undefined ? formatCurrency(item.payments) : '' }}
                  </td>
                  <td class="text-right text-slate-900 font-bold">
                    {{ formatCurrency(item.balance) }}
                  </td>
                </tr>
                
                <tr class="font-bold bg-slate-50/50">
                  <td></td>
                  <td class="text-right text-slate-700">Balance Due</td>
                  <td></td>
                  <td></td>
                  <td class="text-right text-indigo-600">{{ formatCurrency(statementData.balanceDue) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- TAB 5: Invoices -->
        <div class="card" v-if="activeTab === 'invoices'">
          <!-- Stats Cards Row -->
          <div class="inv-stats-row">
            <div class="inv-stat-card inv-stat-outstanding">
              <div class="inv-stat-label">Outstanding Invoices</div>
              <div class="inv-stat-val">{{ formatCurrency(invoiceStats.outstanding) }}</div>
            </div>
            <div class="inv-stat-card inv-stat-pastdue">
              <div class="inv-stat-label">Past Due Invoices</div>
              <div class="inv-stat-val">{{ formatCurrency(invoiceStats.pastDue) }}</div>
            </div>
            <div class="inv-stat-card inv-stat-paid">
              <div class="inv-stat-label">Paid Invoices</div>
              <div class="inv-stat-val">{{ formatCurrency(invoiceStats.paid) }}</div>
            </div>
          </div>

          <!-- Toolbar: search + per-page + action buttons -->
          <div class="tab-toolbar">
            <div class="flex items-center gap-2">
              <select class="select-inline-num" v-model="invoicePageSize">
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
              </select>
              <div class="tab-search-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="tab-search-icon"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="tab-search-input" placeholder="Search..." v-model="invoiceSearch" />
              </div>
            </div>
            <div class="flex items-center gap-2">
              <!-- Zip Invoice -->
              <button class="btn-inv-action" @click="zipInvoices" title="Download all invoices as ZIP">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Zip Invoice
              </button>
              <!-- Export -->
              <button class="btn-inv-action" @click="exportInvoices" title="Export invoices to CSV">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
              </button>
              <!-- Create New Invoice -->
              <router-link
                :to="{ name: 'admin.invoices.create', query: { client_id: customer.id } }"
                class="btn-create-invoice"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create New Invoice
              </router-link>
            </div>
          </div>

          <!-- Invoices Table -->
          <table class="data-table">
            <thead>
              <tr>
                <th>Invoice #</th>
                <th>Amount</th>
                <th>Total Tax</th>
                <th>Date</th>
                <th>Project</th>
                <th>Tags</th>
                <th>Due Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="inv in paginatedInvoices" :key="inv.id" class="inv-row">
                <td>
                  <div class="inv-num-cell">
                    <router-link :to="{ name: 'admin.invoices.view', params: { id: inv.id } }" class="link-blue font-semibold">{{ inv.number }}</router-link>
                    <div class="inv-row-actions">
                      <router-link :to="{ name: 'admin.invoices.view', params: { id: inv.id } }" class="row-action-link">View</router-link>
                      <span class="row-action-sep">|</span>
                      <router-link :to="{ name: 'admin.invoices.view', params: { id: inv.id } }" class="row-action-link">Edit</router-link>
                    </div>
                  </div>
                </td>
                <td class="font-semibold">{{ formatCurrency(inv.total) }}</td>
                <td>{{ formatCurrency(inv.tax || 0) }}</td>
                <td>{{ formatDateString(inv.date) }}</td>
                <td>{{ inv.project?.name || '' }}</td>
                <td><span v-if="inv.tags" class="tag-badge">{{ inv.tags }}</span></td>
                <td>{{ formatDateString(inv.duedate) }}</td>
                <td>
                  <span class="badge" :class="invoiceStatusClass(inv.status)">
                    {{ inv.status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) }}
                  </span>
                </td>
              </tr>
              <tr v-if="filteredInvoices.length === 0">
                <td colspan="8" class="empty-cell">No invoices found for this customer.</td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div class="table-pagination" v-if="filteredInvoices.length > 0">
            <span class="pagination-info">
              Showing {{ invoicePageStart + 1 }} to {{ Math.min(invoicePageStart + invoicePageSize, filteredInvoices.length) }} of {{ filteredInvoices.length }} entries
            </span>
            <div class="pagination-controls">
              <button class="page-btn" :disabled="invoicePage === 1" @click="invoicePage--">Previous</button>
              <button
                v-for="p in invoiceTotalPages" :key="p"
                :class="['page-btn', { active: p === invoicePage }]"
                @click="invoicePage = p"
              >{{ p }}</button>
              <button class="page-btn" :disabled="invoicePage === invoiceTotalPages" @click="invoicePage++">Next</button>
            </div>
          </div>
        </div>

        <!-- TAB 6: Payments -->
        <div class="card" v-if="activeTab === 'payments'">
          <h2 class="section-title border-b pb-2 mb-4">Payments</h2>

          <!-- Toolbar: search + per-page + action buttons -->
          <div class="tab-toolbar">
            <div class="flex items-center gap-2">
              <select class="select-inline-num" v-model="paymentPageSize">
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
              </select>
              <div class="tab-search-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="tab-search-icon"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="tab-search-input" placeholder="Search..." v-model="paymentSearch" />
              </div>
            </div>
            <div class="flex items-center gap-2">
              <!-- Zip Payments -->
              <button class="btn-inv-action" @click="zipPayments" title="Download all payments as ZIP">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Zip Payments
              </button>
            </div>
          </div>

          <table class="data-table">
            <thead>
              <tr>
                <th>Payment #</th>
                <th>Invoice #</th>
                <th>Payment Mode</th>
                <th>Transaction ID</th>
                <th>Amount</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="pay in paginatedPayments" :key="pay.id" class="inv-row">
                <td>
                  <div class="inv-num-cell">
                    <span class="font-semibold text-slate-800">{{ pay.id }}</span>
                    <div class="inv-row-actions">
                      <router-link :to="{ name: 'admin.payments.view', params: { id: pay.id } }" class="row-action-link">View</router-link>
                      <span class="row-action-sep">|</span>
                      <a href="#" @click.prevent="deletePayment(pay.id)" class="row-action-link text-rose-600">Delete</a>
                    </div>
                  </div>
                </td>
                <td>
                  <router-link :to="{ name: 'admin.invoices.view', params: { id: pay.invoice_id } }" class="link-blue font-semibold">
                    {{ pay.invoice?.number || ('INV-' + pay.invoice_id) }}
                  </router-link>
                </td>
                <td>{{ pay.paymentmode || '—' }}</td>
                <td>{{ pay.transactionid || '—' }}</td>
                <td class="font-semibold text-slate-700">{{ formatCurrency(pay.amount) }}</td>
                <td>{{ formatDateString(pay.date) }}</td>
              </tr>
              <tr v-if="filteredPayments.length === 0">
                <td colspan="6" class="empty-cell">No payments found for this customer.</td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div class="table-pagination" v-if="filteredPayments.length > 0">
            <span class="pagination-info">
              Showing {{ paymentPageStart + 1 }} to {{ Math.min(paymentPageStart + paymentPageSize, filteredPayments.length) }} of {{ filteredPayments.length }} entries
            </span>
            <div class="pagination-controls">
              <button class="page-btn" :disabled="paymentPage === 1" @click="paymentPage--">Previous</button>
              <button
                v-for="p in paymentTotalPages" :key="p"
                :class="['page-btn', { active: p === paymentPage }]"
                @click="paymentPage = p"
              >{{ p }}</button>
              <button class="page-btn" :disabled="paymentPage === paymentTotalPages" @click="paymentPage++">Next</button>
            </div>
          </div>
        </div>

        <!-- TAB 7: Proposals -->
        <div class="card" v-if="activeTab === 'proposals'">
          <h2 class="section-title border-b pb-2 mb-4">Proposals</h2>

          <!-- Toolbar: search + per-page + action buttons -->
          <div class="tab-toolbar">
            <div class="flex items-center gap-2">
              <select class="select-inline-num" v-model="proposalPageSize">
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
              </select>
              <div class="tab-search-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="tab-search-icon"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="tab-search-input" placeholder="Search..." v-model="proposalSearch" />
              </div>
            </div>
            <div class="flex items-center gap-2">
              <!-- Create New Proposal -->
              <router-link
                :to="{ name: 'admin.proposals.create', query: { rel_type: 'customer', rel_id: customer.id } }"
                class="btn-create-invoice"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Proposal
              </router-link>
            </div>
          </div>

          <table class="data-table">
            <thead>
              <tr>
                <th>Proposal #</th>
                <th>Subject</th>
                <th>Total</th>
                <th>Date</th>
                <th>Open Till</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="prop in paginatedProposals" :key="prop.id" class="inv-row">
                <td>
                  <div class="inv-num-cell">
                    <router-link :to="{ name: 'admin.proposals.edit', params: { id: prop.id } }" class="link-blue font-semibold">
                      {{ prop.number }}
                    </router-link>
                    <div class="inv-row-actions">
                      <router-link :to="{ name: 'admin.proposals.edit', params: { id: prop.id } }" class="row-action-link">Edit</router-link>
                      <span class="row-action-sep">|</span>
                      <a href="#" @click.prevent="deleteProposal(prop.id)" class="row-action-link text-rose-600">Delete</a>
                    </div>
                  </div>
                </td>
                <td><strong>{{ prop.subject }}</strong></td>
                <td class="font-semibold text-slate-700">{{ formatCurrency(prop.amount) }}</td>
                <td>{{ formatDateString(prop.date) }}</td>
                <td>{{ formatDateString(prop.open_till) }}</td>
                <td><span class="badge" :class="propStatusClass(prop.status)">{{ prop.status }}</span></td>
              </tr>
              <tr v-if="filteredProposals.length === 0">
                <td colspan="6" class="empty-cell">No proposals found for this customer.</td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div class="table-pagination" v-if="filteredProposals.length > 0">
            <span class="pagination-info">
              Showing {{ proposalPageStart + 1 }} to {{ Math.min(proposalPageStart + proposalPageSize, filteredProposals.length) }} of {{ filteredProposals.length }} entries
            </span>
            <div class="pagination-controls">
              <button class="page-btn" :disabled="proposalPage === 1" @click="proposalPage--">Previous</button>
              <button
                v-for="p in proposalTotalPages" :key="p"
                :class="['page-btn', { active: p === proposalPage }]"
                @click="proposalPage = p"
              >{{ p }}</button>
              <button class="page-btn" :disabled="proposalPage === proposalTotalPages" @click="proposalPage++">Next</button>
            </div>
          </div>
        </div>

        <!-- TAB 8: Credit Notes -->
        <div class="card" v-if="activeTab === 'credit_notes'">
          <h2 class="section-title border-b pb-2 mb-4">Credit Notes</h2>

          <!-- Available Credits Alert -->
          <div class="credits-available-alert mb-4">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15" class="text-amber-600"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span class="font-semibold text-amber-800">{{ formatCurrency(availableCredits) }} credits available.</span>
          </div>

          <!-- Toolbar: search + per-page + action buttons -->
          <div class="tab-toolbar">
            <div class="flex items-center gap-2">
              <select class="select-inline-num" v-model="creditNotePageSize">
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
              </select>
              <div class="tab-search-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="tab-search-icon"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="tab-search-input" placeholder="Search..." v-model="creditNoteSearch" />
              </div>
            </div>
            <div class="flex items-center gap-2">
              <!-- Zip Credit Notes -->
              <button class="btn-inv-action" @click="zipCreditNotes" title="Download all credit notes as ZIP">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Zip Credit Notes
              </button>
              <!-- Create New Credit Note -->
              <router-link
                :to="{ name: 'admin.credit-notes.create', query: { client_id: customer.id } }"
                class="btn-create-invoice"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Credit Note
              </router-link>
            </div>
          </div>

          <table class="data-table">
            <thead>
              <tr>
                <th>Credit Note #</th>
                <th>Credit Note Date</th>
                <th>Status</th>
                <th>Project</th>
                <th>Reference #</th>
                <th>Amount</th>
                <th>Remaining Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="cn in paginatedCreditNotes" :key="cn.id" class="inv-row">
                <td>
                  <div class="inv-num-cell">
                    <router-link :to="{ name: 'admin.credit-notes.edit', params: { id: cn.id } }" class="link-blue font-semibold">
                      {{ cn.number }}
                    </router-link>
                    <div class="inv-row-actions">
                      <router-link :to="{ name: 'admin.credit-notes.edit', params: { id: cn.id } }" class="row-action-link">Edit</router-link>
                      <span class="row-action-sep">|</span>
                      <a href="#" @click.prevent="deleteCreditNote(cn.id)" class="row-action-link text-rose-600">Delete</a>
                    </div>
                  </div>
                </td>
                <td>{{ formatDateString(cn.date) }}</td>
                <td>
                  <span class="badge" :class="cn.status === 'Open' ? 'badge-yellow' : cn.status === 'Closed' ? 'badge-green' : 'badge-red'">
                    {{ cn.status }}
                  </span>
                </td>
                <td>—</td>
                <td>{{ cn.reference || '—' }}</td>
                <td class="font-semibold text-slate-700">{{ formatCurrency(cn.amount) }}</td>
                <td class="font-semibold text-slate-700">{{ cn.status === 'Open' ? formatCurrency(cn.amount) : '$0.00' }}</td>
              </tr>
              <tr v-if="filteredCreditNotes.length === 0">
                <td colspan="7" class="empty-cell">No credit notes found for this customer.</td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div class="table-pagination" v-if="filteredCreditNotes.length > 0">
            <span class="pagination-info">
              Showing {{ creditNotePageStart + 1 }} to {{ Math.min(creditNotePageStart + creditNotePageSize, filteredCreditNotes.length) }} of {{ filteredCreditNotes.length }} entries
            </span>
            <div class="pagination-controls">
              <button class="page-btn" :disabled="creditNotePage === 1" @click="creditNotePage--">Previous</button>
              <button
                v-for="p in creditNoteTotalPages" :key="p"
                :class="['page-btn', { active: p === creditNotePage }]"
                @click="creditNotePage = p"
              >{{ p }}</button>
              <button class="page-btn" :disabled="creditNotePage === creditNoteTotalPages" @click="creditNotePage++">Next</button>
            </div>
          </div>
        </div>

        <!-- TAB 9: Estimates -->
        <div class="card" v-if="activeTab === 'estimates'">
          <h2 class="section-title border-b pb-2 mb-4">Estimates</h2>

          <!-- Stats Cards Row -->
          <div class="est-stats-row">
            <div class="est-stat-card est-stat-draft">
              <div class="est-stat-label">Draft</div>
              <div class="est-stat-val">{{ formatCurrency(estimateStats.draft) }}</div>
            </div>
            <div class="est-stat-card est-stat-sent">
              <div class="est-stat-label">Sent</div>
              <div class="est-stat-val">{{ formatCurrency(estimateStats.sent) }}</div>
            </div>
            <div class="est-stat-card est-stat-expired">
              <div class="est-stat-label">Expired</div>
              <div class="est-stat-val">{{ formatCurrency(estimateStats.expired) }}</div>
            </div>
            <div class="est-stat-card est-stat-declined">
              <div class="est-stat-label">Declined</div>
              <div class="est-stat-val">{{ formatCurrency(estimateStats.declined) }}</div>
            </div>
            <div class="est-stat-card est-stat-accepted">
              <div class="est-stat-label">Accepted</div>
              <div class="est-stat-val">{{ formatCurrency(estimateStats.accepted) }}</div>
            </div>
          </div>

          <!-- Toolbar: search + per-page + action buttons -->
          <div class="tab-toolbar">
            <div class="flex items-center gap-2">
              <select class="select-inline-num" v-model="estimatePageSize">
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
              </select>
              <div class="tab-search-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" class="tab-search-icon"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="tab-search-input" placeholder="Search..." v-model="estimateSearch" />
              </div>
            </div>
            <div class="flex items-center gap-2">
              <!-- Create New Estimate -->
              <router-link
                :to="{ name: 'admin.estimates.create', query: { client_id: customer.id } }"
                class="btn-create-invoice"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Create New Estimate
              </router-link>
            </div>
          </div>

          <table class="data-table">
            <thead>
              <tr>
                <th>Estimate #</th>
                <th>Amount</th>
                <th>Total Tax</th>
                <th>Project</th>
                <th>Tags</th>
                <th>Date</th>
                <th>Expiry Date</th>
                <th>Reference #</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="est in paginatedEstimates" :key="est.id" class="inv-row">
                <td>
                  <div class="inv-num-cell">
                    <router-link :to="{ name: 'admin.estimates.edit', params: { id: est.id } }" class="link-blue font-semibold">
                      {{ est.number }}
                    </router-link>
                    <div class="inv-row-actions">
                      <router-link :to="{ name: 'admin.estimates.edit', params: { id: est.id } }" class="row-action-link">Edit</router-link>
                      <span class="row-action-sep">|</span>
                      <a href="#" @click.prevent="deleteEstimate(est.id)" class="row-action-link text-rose-600">Delete</a>
                    </div>
                  </div>
                </td>
                <td class="font-semibold">{{ formatCurrency(est.amount) }}</td>
                <td>{{ formatCurrency(getEstimateTax(est)) }}</td>
                <td>{{ est.project || '—' }}</td>
                <td>
                  <span v-if="est.tags" class="tag-badge">{{ est.tags }}</span>
                  <span v-else>—</span>
                </td>
                <td>{{ formatDateString(est.date) }}</td>
                <td>{{ formatDateString(est.expiry) }}</td>
                <td>{{ est.reference_no || '—' }}</td>
                <td>
                  <span class="badge" :class="estStatusClass(est.status)">
                    {{ est.status }}
                  </span>
                </td>
              </tr>
              <tr v-if="filteredEstimates.length === 0">
                <td colspan="9" class="empty-cell">No estimates found for this customer.</td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div class="tab-pagination-footer" v-if="filteredEstimates.length > 0">
            <span class="text-xs text-slate-500">
              Showing {{ estimateStartEntry }} to {{ estimateEndEntry }} of {{ filteredEstimates.length }} entries
            </span>
            <div class="pagination-btns">
              <button 
                class="btn-page" 
                :disabled="estimateCurrentPage === 1" 
                @click="estimateCurrentPage--"
              >
                Previous
              </button>
              <button class="btn-page active">{{ estimateCurrentPage }}</button>
              <button 
                class="btn-page" 
                :disabled="estimateCurrentPage * estimatePageSize >= filteredEstimates.length" 
                @click="estimateCurrentPage++"
              >
                Next
              </button>
            </div>
          </div>
        </div>

        <!-- TAB 10: Subscriptions -->
        <div class="card" v-if="activeTab === 'subscriptions'">
          <h2 class="section-title border-b pb-2 mb-4">Subscriptions</h2>
          <table class="data-table">
            <thead>
              <tr>
                <th>Subscription Name</th>
                <th>Plan Name</th>
                <th>Amount</th>
                <th>Next Billing Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="5" class="empty-cell">No active subscriptions found for this customer.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- TAB 11: Expenses -->
        <div class="card" v-if="activeTab === 'expenses'">
          <h2 class="section-title border-b pb-2 mb-4">Expenses</h2>
          <table class="data-table">
            <thead>
              <tr>
                <th>Category</th>
                <th>Amount</th>
                <th>Name / Note</th>
                <th>Date</th>
                <th>Reference #</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="exp in filteredExpenses" :key="exp.id">
                <td><strong>{{ exp.category }}</strong></td>
                <td class="font-semibold">{{ formatCurrency(exp.amount) }}</td>
                <td>{{ exp.name }}</td>
                <td>{{ exp.date }}</td>
                <td>{{ exp.reference || '—' }}</td>
                <td><span class="badge" :class="exp.status === 'billed' ? 'badge-green' : 'badge-red'">{{ exp.status }}</span></td>
              </tr>
              <tr v-if="filteredExpenses.length === 0">
                <td colspan="6" class="empty-cell">No expenses recorded for this customer.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- TAB 12: Contracts -->
        <div class="card" v-if="activeTab === 'contracts'">
          <h2 class="section-title border-b pb-2 mb-4">Contracts</h2>
          <table class="data-table">
            <thead>
              <tr>
                <th>Contract Subject</th>
                <th>Contract Value</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="contract in customerContracts" :key="contract.id">
                <td><span class="font-semibold text-slate-800">{{ contract.subject }}</span></td>
                <td class="font-semibold">{{ formatCurrency(contract.value) }}</td>
                <td>{{ contract.start }}</td>
                <td>{{ contract.end }}</td>
                <td><span class="badge badge-green">{{ contract.status }}</span></td>
              </tr>
              <tr v-if="customerContracts.length === 0">
                <td colspan="5" class="empty-cell">No contracts found for this customer.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- TAB 13: Projects -->
        <div class="card" v-if="activeTab === 'projects'">
          <h2 class="section-title border-b pb-2 mb-4">Projects</h2>
          <table class="data-table">
            <thead>
              <tr>
                <th>Project Name</th>
                <th>Billing</th>
                <th>Start Date</th>
                <th>Deadline</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="proj in filteredProjects" :key="proj.id">
                <td><strong>{{ proj.name }}</strong></td>
                <td>{{ proj.billing_type }}</td>
                <td>{{ proj.start_date }}</td>
                <td>{{ proj.deadline || '—' }}</td>
                <td><span class="badge badge-blue">{{ proj.status }}</span></td>
              </tr>
              <tr v-if="filteredProjects.length === 0">
                <td colspan="5" class="empty-cell">No projects found for this customer.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- TAB 14: Tasks -->
        <div class="card" v-if="activeTab === 'tasks'">
          <h2 class="section-title border-b pb-2 mb-4">Tasks</h2>
          <table class="data-table">
            <thead>
              <tr>
                <th>Task Name</th>
                <th>Due Date</th>
                <th>Priority</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="4" class="empty-cell">No tasks recorded for this customer.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- TAB 15: Tickets -->
        <div class="card" v-if="activeTab === 'tickets'">
          <h2 class="section-title border-b pb-2 mb-4">Support Tickets</h2>
          <table class="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Subject</th>
                <th>Department</th>
                <th>Priority</th>
                <th>Last Reply</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in filteredTickets" :key="t.id">
                <td><span class="font-semibold">#{{ t.number }}</span></td>
                <td><strong>{{ t.subject }}</strong></td>
                <td>{{ t.department }}</td>
                <td>{{ t.priority }}</td>
                <td>{{ t.last_reply }}</td>
                <td><span class="badge" :class="ticketStatusClass(t.status)">{{ t.status }}</span></td>
              </tr>
              <tr v-if="filteredTickets.length === 0">
                <td colspan="6" class="empty-cell">No support tickets found for this customer.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- TAB 16: Files -->
        <div class="card" v-if="activeTab === 'files'">
          <h2 class="section-title border-b pb-2 mb-4">Files</h2>
          <div class="text-center py-8 text-slate-400 text-xs">
            📂 No files uploaded yet.
          </div>
        </div>

        <!-- TAB 17: Vault -->
        <div class="card" v-if="activeTab === 'vault'">
          <h2 class="section-title border-b pb-2 mb-4">Vault</h2>
          <div class="text-center py-8 text-slate-400 text-xs">
            🔒 Secure Vault is empty.
          </div>
        </div>

        <!-- TAB 18: Reminders -->
        <div class="card" v-if="activeTab === 'reminders'">
          <h2 class="section-title border-b pb-2 mb-4">Reminders</h2>
          <div class="text-center py-8 text-slate-400 text-xs">
            ⏰ No reminders set for this customer.
          </div>
        </div>

        <!-- TAB 19: Map -->
        <div class="card" v-if="activeTab === 'map'">
          <h2 class="section-title border-b pb-2 mb-4">Address Map</h2>
          <div class="p-6 bg-slate-50 border border-slate-200 rounded-md text-center text-xs text-slate-600">
            📍 Address location: {{ customer.address || '—' }}, {{ customer.city || '—' }}, {{ customer.state || '—' }}
          </div>
        </div>

      </main>

    </div>

    <!-- Contact Add/Edit Drawer (Matching modal details) -->
    <a-drawer
      v-model:open="contactDrawerVisible"
      :title="contactForm.id ? 'Edit Contact' : 'Add New Contact'"
      placement="right"
      width="480"
      @close="closeContactDrawer"
    >
      <div class="drawer-client-title font-bold text-slate-800 text-xs uppercase mb-4 border-b pb-1.5 tracking-wider">
        {{ customer.company }}
      </div>

      <a-form layout="vertical" class="space-y-4">
        
        <div class="form-group">
          <label class="input-lbl">Profile image</label>
          <input type="file" class="text-xs" />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="form-group">
            <label class="input-lbl"><span class="required">*</span> First Name</label>
            <input type="text" class="input-ctrl" v-model="contactForm.firstname" required />
          </div>
          <div class="form-group">
            <label class="input-lbl"><span class="required">*</span> Last Name</label>
            <input type="text" class="input-ctrl" v-model="contactForm.lastname" required />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="form-group">
            <label class="input-lbl">Position</label>
            <input type="text" class="input-ctrl" v-model="contactForm.title" />
          </div>
          <div class="form-group">
            <label class="input-lbl"><span class="required">*</span> Email</label>
            <input type="email" class="input-ctrl" v-model="contactForm.email" required />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="form-group">
            <label class="input-lbl">Phone</label>
            <div class="flex">
              <span class="phone-prefix bg-slate-100 border border-slate-300 border-r-0 rounded-l px-2 py-2 text-xs flex items-center text-slate-500 font-semibold">+226</span>
              <input type="text" class="input-ctrl rounded-l-none" v-model="contactForm.phonenumber" style="border-top-left-radius:0;border-bottom-left-radius:0;" />
            </div>
          </div>
          <div class="form-group">
            <label class="input-lbl">Direction</label>
            <select class="select-ctrl" v-model="contactForm.direction">
              <option value="LTR">LTR</option>
              <option value="RTL">RTL</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="input-lbl"><span class="required" v-if="!contactForm.id">*</span> Password</label>
          <input type="password" class="input-ctrl" v-model="contactForm.password" :placeholder="contactForm.id ? 'Leave blank to keep current' : ''" />
        </div>

        <div class="space-y-2 pt-2 border-t border-slate-100">
          <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-600">
            <input type="checkbox" v-model="contactForm.is_primary" />
            <span>Primary Contact</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-600">
            <input type="checkbox" v-model="contactForm.no_welcome_email" />
            <span>Do not send welcome email</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-600">
            <input type="checkbox" v-model="contactForm.send_set_password_email" />
            <span>Send SET password email</span>
          </label>
        </div>

        <!-- Permissions Section -->
        <div class="border-t border-slate-100 pt-4 mt-4">
          <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-0.5">Permissions</h4>
          <p class="text-[11px] text-slate-400 font-semibold mb-3">Make sure to set appropriate permissions for this contact</p>
          
          <div class="grid grid-cols-2 gap-2 text-xs font-medium text-slate-600">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="contactForm.permissions.invoices" />
              <span>Invoices</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="contactForm.permissions.estimates" />
              <span>Estimates</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="contactForm.permissions.contracts" />
              <span>Contracts</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="contactForm.permissions.proposals" />
              <span>Proposals</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="contactForm.permissions.support" />
              <span>Support</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="contactForm.permissions.projects" />
              <span>Projects</span>
            </label>
          </div>
        </div>

        <!-- Email Notifications Section -->
        <div class="border-t border-slate-100 pt-4 mt-4">
          <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">Email Notifications</h4>
          
          <div class="grid grid-cols-2 gap-2 text-xs font-medium text-slate-600">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="contactForm.email_notifications.invoice" />
              <span>Invoice</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="contactForm.email_notifications.estimate" />
              <span>Estimate</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="contactForm.email_notifications.credit_note" />
              <span>Credit Note</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="contactForm.email_notifications.project" />
              <span>Project</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="contactForm.email_notifications.tickets" />
              <span>Tickets</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="contactForm.email_notifications.task" />
              <span>Task</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="contactForm.email_notifications.contract" />
              <span>Contract</span>
            </label>
          </div>
        </div>

      </a-form>

      <template #extra>
        <a-space>
          <a-button @click="closeContactDrawer">Cancel</a-button>
          <a-button type="primary" :loading="contactSubmitLoading" @click="submitContact" class="bg-indigo-600 border-indigo-600">
            Save Contact
          </a-button>
        </a-space>
      </template>
    </a-drawer>

  </div>
  <div v-else-if="loading" class="py-12 text-center">
    <a-spin size="large" />
  </div>
</template>

<script>
import { defineComponent, ref, reactive, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { message, Modal } from 'ant-design-vue';
import { useProposalsStore } from '../../store/proposalsStore';
import { useEstimatesStore } from '../../store/estimatesStore';

export default defineComponent({
  name: 'CustomerView',
  setup() {
    const route = useRoute();
    const proposalsStore = useProposalsStore();
    const estimatesStore = useEstimatesStore();
    const customer = ref(null);
    const loading = ref(false);
    const submitting = ref(false);
    const contactSubmitLoading = ref(false);
    const activeTab = ref('profile');
    const profileSubTab = ref('details');

    // Drawer States
    const contactDrawerVisible = ref(false);
    const contactFormRef = ref(null);

     const clientInvoices = ref([]);
    const clientPayments = ref([]);
    const clientCreditNotes = ref([]);
    const availableCredits = computed(() => {
      const openCN = clientCreditNotes.value.filter(cn => cn.status === 'Open');
      return openCN.reduce((s, cn) => s + parseFloat(cn.amount || 0), 0);
    });
    const statementPeriod = ref('this_month');
    const customStartDate = ref('');
    const customEndDate = ref('');

    // Invoice tab search + pagination state
    const invoiceSearch = ref('');
    const invoicePage = ref(1);
    const invoicePageSize = ref(25);

    // Payments tab search + pagination state
    const paymentSearch = ref('');
    const paymentPage = ref(1);
    const paymentPageSize = ref(25);

    // Proposals tab search + pagination state
    const proposalSearch = ref('');
    const proposalPage = ref(1);
    const proposalPageSize = ref(25);

    // Credit Notes tab search + pagination state
    const creditNoteSearch = ref('');
    const creditNotePage = ref(1);
    const creditNotePageSize = ref(25);

    const filteredInvoices = computed(() => {
      const q = invoiceSearch.value.toLowerCase().trim();
      if (!q) return clientInvoices.value;
      return clientInvoices.value.filter(inv =>
        (inv.number || '').toLowerCase().includes(q) ||
        (inv.status || '').toLowerCase().includes(q)
      );
    });

    const invoiceTotalPages = computed(() =>
      Math.max(1, Math.ceil(filteredInvoices.value.length / invoicePageSize.value))
    );

    const invoicePageStart = computed(() =>
      (invoicePage.value - 1) * invoicePageSize.value
    );

    const paginatedInvoices = computed(() =>
      filteredInvoices.value.slice(invoicePageStart.value, invoicePageStart.value + invoicePageSize.value)
    );

    const filteredPayments = computed(() => {
      const q = paymentSearch.value.toLowerCase().trim();
      if (!q) return clientPayments.value;
      return clientPayments.value.filter(pay => {
        const payNum = String(pay.id);
        const invNum = (pay.invoice?.number || '').toLowerCase();
        const mode = (pay.paymentmode || '').toLowerCase();
        const txId = (pay.transactionid || '').toLowerCase();
        return payNum.includes(q) || invNum.includes(q) || mode.includes(q) || txId.includes(q);
      });
    });

    const paymentTotalPages = computed(() =>
      Math.max(1, Math.ceil(filteredPayments.value.length / paymentPageSize.value))
    );

    const paymentPageStart = computed(() =>
      (paymentPage.value - 1) * paymentPageSize.value
    );

    const paginatedPayments = computed(() =>
      filteredPayments.value.slice(paymentPageStart.value, paymentPageStart.value + paymentPageSize.value)
    );

    const invoiceStats = computed(() => {
      const invs = clientInvoices.value;
      const outstanding = invs
        .filter(i => ['unpaid','partially_paid'].includes(i.status))
        .reduce((s, i) => s + parseFloat(i.total || 0), 0);
      const pastDue = invs
        .filter(i => i.status === 'overdue')
        .reduce((s, i) => s + parseFloat(i.total || 0), 0);
      const paid = invs
        .filter(i => i.status === 'paid')
        .reduce((s, i) => s + parseFloat(i.total || 0), 0);
      return { outstanding, pastDue, paid };
    });


    const formatDateString = (val) => {
      if (!val) return '—';
      const str = String(val);
      return str.includes('T') ? str.split('T')[0] : str;
    };
    
    const contactForm = reactive({
      id: null,
      firstname: '',
      lastname: '',
      email: '',
      phonenumber: '',
      title: '',
      password: '',
      is_primary: false,
      direction: 'LTR',
      no_welcome_email: false,
      send_set_password_email: false,
      permissions: {
        invoices: true,
        estimates: true,
        contracts: true,
        proposals: true,
        support: true,
        projects: true
      },
      email_notifications: {
        invoice: true,
        estimate: true,
        credit_note: true,
        project: true,
        tickets: true,
        task: true,
        contract: true
      }
    });

    const customerForm = reactive({
      company: '', vat: '', phonenumber: '', website: '', address: '', city: '', state: '', zip: '', country: '', active: true,
      default_language: 'system_default', groups: [], currency: 'USD', show_primary_contact: false,
      billing_street: '', billing_city: '', billing_state: '', billing_zip: '', billing_country: '',
      shipping_street: '', shipping_city: '', shipping_state: '', shipping_zip: '', shipping_country: '',
    });

    // Mock count details of customer notes matching data requested
    const customerNotes = ref([
      { 
        id: 1, 
        content: `Majesty,' the Hatter was the matter worse. You MUST have meant some mischief, or else you'd have signed your name like an honest man.' There was a child,' said the Rabbit say, 'A barrowful will do, to begin with; and being so many different sizes.`, 
        date: '2026-06-16 00:00:28', 
        creator: 'Jan Keeling' 
      },
      { 
        id: 2, 
        content: `And beat him when he finds out who I am! But I'd better take him his fan and two or three times over to herself, for she was surprised to find that she was quite a crowd of little cartwheels, and the three were all shaped like the look of things at.`, 
        date: '2026-06-16 00:00:27', 
        creator: 'Cortez Konopelski' 
      }
    ]);

    const notesSearchQuery = ref('');
    const newNoteText = ref('');
    const editingNoteId = ref(null);
    const showNoteForm = ref(false);

    const toggleNoteForm = () => {
      showNoteForm.value = !showNoteForm.value;
      if (!showNoteForm.value) {
        editingNoteId.value = null;
        newNoteText.value = '';
      }
    };

    const filteredNotes = computed(() => {
      if (!notesSearchQuery.value) return customerNotes.value;
      const query = notesSearchQuery.value.toLowerCase();
      return customerNotes.value.filter(note => 
        (note.content || '').toLowerCase().includes(query) ||
        (note.creator || '').toLowerCase().includes(query)
      );
    });

    const saveNote = () => {
      if (!newNoteText.value.trim()) {
        return message.warning('Note description is required.');
      }

      if (editingNoteId.value) {
        // Edit existing note
        const note = customerNotes.value.find(n => n.id === editingNoteId.value);
        if (note) {
          note.content = newNoteText.value;
          note.date = new Date().toISOString().slice(0, 19).replace('T', ' ');
          message.success('Note updated successfully.');
        }
        editingNoteId.value = null;
      } else {
        // Add new note
        const newId = customerNotes.value.length ? Math.max(...customerNotes.value.map(n => n.id)) + 1 : 1;
        customerNotes.value.unshift({
          id: newId,
          content: newNoteText.value,
          creator: 'Jan Keeling',
          date: new Date().toISOString().slice(0, 19).replace('T', ' '),
        });
        message.success('Note added successfully.');
      }

      newNoteText.value = '';
      showNoteForm.value = false;
    };

    const editNote = (note) => {
      editingNoteId.value = note.id;
      newNoteText.value = note.content;
      showNoteForm.value = true;
    };

    const deleteNote = (noteId) => {
      Modal.confirm({
        title: 'Delete Note?',
        content: 'Are you sure you want to delete this customer note?',
        okText: 'Delete',
        okType: 'danger',
        onOk: () => {
          customerNotes.value = customerNotes.value.filter(n => n.id !== noteId);
          message.success('Note deleted successfully.');
        }
      });
    };

    const printStatement = () => {
      window.print();
    };

    const statementDateRange = computed(() => {
      const today = new Date();
      const year = today.getFullYear();
      const month = today.getMonth(); // 0-indexed
      
      let start, end;
      
      switch (statementPeriod.value) {
        case 'today':
          start = new Date(today);
          end = new Date(today);
          break;
        case 'this_week':
          const day = today.getDay();
          const diff = today.getDate() - day + (day === 0 ? -6 : 1);
          start = new Date(today.setDate(diff));
          end = new Date(start);
          end.setDate(start.getDate() + 6);
          break;
        case 'this_month':
          start = new Date(year, month, 1);
          end = new Date(year, month + 1, 0);
          break;
        case 'last_month':
          start = new Date(year, month - 1, 1);
          end = new Date(year, month, 0);
          break;
        case 'this_year':
          start = new Date(year, 0, 1);
          end = new Date(year, 12, 0);
          break;
        case 'last_year':
          start = new Date(year - 1, 0, 1);
          end = new Date(year - 1, 12, 0);
          break;
        case 'period':
          start = customStartDate.value ? new Date(customStartDate.value) : new Date(year, month, 1);
          end = customEndDate.value ? new Date(customEndDate.value) : new Date(year, month + 1, 0);
          break;
        default:
          start = new Date(year, month, 1);
          end = new Date(year, month + 1, 0);
      }
      
      const formatDate = (d) => {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const r = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${r}`;
      };
      return {
        start: formatDate(start),
        end: formatDate(end)
      };
    });

     const statementData = computed(() => {
      const { start, end } = statementDateRange.value;
      
      // 1. Calculate Beginning Balance (invoices and payments before start date)
      let invoicesBefore = clientInvoices.value.filter(inv => formatDateString(inv.date) < start);
      let paymentsBefore = clientPayments.value.filter(pay => formatDateString(pay.date) < start);
      
      let totalInvoicedBefore = invoicesBefore.reduce((sum, inv) => sum + parseFloat(inv.total || 0), 0);
      let totalPaidBefore = paymentsBefore.reduce((sum, pay) => sum + parseFloat(pay.amount || 0), 0);
      
      let beginningBalance = totalInvoicedBefore - totalPaidBefore;
      
      // 2. Filter Invoices during period
      let invoicesPeriod = clientInvoices.value.filter(inv => {
        const d = formatDateString(inv.date);
        return d >= start && d <= end;
      });
      
      // 3. Filter Payments during period
      let paymentsPeriod = clientPayments.value.filter(pay => {
        const d = formatDateString(pay.date);
        return d >= start && d <= end;
      });
      
      // 4. Build items list
      let items = [];
      
      // Add Beginning Balance Row
      items.push({
        date: start,
        details: 'Beginning Balance',
        amount: null,
        payments: null,
        balance: beginningBalance,
        isBeginning: true
      });
      
      // Add Invoices Row
      invoicesPeriod.forEach(inv => {
        items.push({
          date: formatDateString(inv.date),
          details: `Invoice INV-${String(inv.number || inv.id).replace('INV-', '')} - due on ${formatDateString(inv.duedate)}`,
          amount: parseFloat(inv.total),
          payments: null,
          type: 'invoice',
          id: inv.id,
          number: inv.number
        });
      });
      
      // Add Payments Row
      paymentsPeriod.forEach(pay => {
        items.push({
          date: formatDateString(pay.date),
          details: `Payment (#${pay.id}) to invoice ${pay.invoice?.number || ('INV-' + pay.invoice_id)}`,
          amount: null,
          payments: parseFloat(pay.amount),
          type: 'payment',
          id: pay.id,
          invoice_id: pay.invoice_id
        });
      });
      
      // Sort items by date ascending (keep beginning balance first)
      const beginningItem = items[0];
      const otherItems = items.slice(1).sort((a, b) => a.date.localeCompare(b.date));
      
      // Compute running balance
      let runningBalance = beginningBalance;
      otherItems.forEach(item => {
        if (item.type === 'invoice') {
          runningBalance += item.amount;
        } else if (item.type === 'payment') {
          runningBalance -= item.payments;
        }
        item.balance = runningBalance;
      });
      
      const allItems = [beginningItem, ...otherItems];
      
      // 5. Account Summary calculations
      let invoicedAmount = invoicesPeriod.reduce((sum, inv) => sum + parseFloat(inv.total || 0), 0);
      let amountPaid = paymentsPeriod.reduce((sum, pay) => sum + parseFloat(pay.amount || 0), 0);
      let balanceDue = beginningBalance + invoicedAmount - amountPaid;
      
      return {
        items: allItems,
        beginningBalance,
        invoicedAmount,
        amountPaid,
        balanceDue,
        start,
        end
      };
    });

    // 19 left sidebar items as requested by UI check
    const menuItems = computed(() => [
      { key: 'profile',       label: 'Profile',         icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>`, badge: null },
      { key: 'contacts',      label: 'Contacts',        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>`, badge: String(customer.value?.contacts?.length || 1) },
      { key: 'notes',         label: 'Notes',           icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>`, badge: String(customerNotes.value.length) },
      { key: 'statement',     label: 'Statement',       icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>`, badge: null },
      { key: 'invoices',      label: 'Invoices',        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>`, badge: String(clientInvoices.value.length || 2) },
      { key: 'payments',      label: 'Payments',        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>`, badge: null },
      { key: 'proposals',     label: 'Proposals',       icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>`, badge: null },
      { key: 'credit_notes',  label: 'Credit Notes',    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>`, badge: null },
      { key: 'estimates',     label: 'Estimates',       icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>`, badge: null },
      { key: 'subscriptions', label: 'Subscriptions',   icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>`, badge: null },
      { key: 'expenses',      label: 'Expenses',        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>`, badge: null },
      { key: 'contracts',     label: 'Contracts',       icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M16 11l2 2 4-4"></path></svg>`, badge: String(customerContracts.value.length) },
      { key: 'projects',      label: 'Projects',        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>`, badge: null },
      { key: 'tasks',         label: 'Tasks',           icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>`, badge: null },
      { key: 'tickets',       label: 'Tickets',         icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>`, badge: '1' },
      { key: 'files',         label: 'Files',           icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>`, badge: null },
      { key: 'vault',         label: 'Vault',           icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>`, badge: null },
      { key: 'reminders',     label: 'Reminders',       icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>`, badge: null },
      { key: 'map',           label: 'Map',             icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>`, badge: null },
    ]);

    const customerContracts = ref([
      { id: 1, subject: 'Annual Software Maintenance Agreement', value: 12000, start: '2026-01-01', end: '2026-12-31', status: 'Active' },
      { id: 2, subject: 'Cloud Infrastructure SLA', value: 5400, start: '2026-03-01', end: '2027-02-28', status: 'Active' }
    ]);

    const adminOptions = ref([
      { id: 1, name: 'Merl Wintheiser', selected: true },
      { id: 2, name: 'Tamara Howell', selected: false },
      { id: 3, name: 'Jan Keeling', selected: true }
    ]);

    const fetchCustomerDetails = async () => {
      loading.value = true;
      try {
        const response = await axios.get(`/clients/${route.params.id}`);
        customer.value = response.data;
        
        // Populate reactive customerForm
        Object.assign(customerForm, {
          company: response.data.company || '',
          vat: response.data.vat || '',
          phonenumber: response.data.phonenumber || '',
          website: response.data.website || '',
          address: response.data.address || '',
          city: response.data.city || '',
          state: response.data.state || '',
          zip: response.data.zip || '',
          country: response.data.country || '',
          active: response.data.active === 1 || response.data.active === true,
          default_language: response.data.default_language || 'system_default',
          groups: response.data.groups ? response.data.groups.split(',') : [],
          currency: response.data.currency || 'System Default',
          show_primary_contact: false,
          billing_street: response.data.billing_street || '',
          billing_city: response.data.billing_city || '',
          billing_state: response.data.billing_state || '',
          billing_zip: response.data.billing_zip || '',
          billing_country: response.data.billing_country || '',
          shipping_street: response.data.shipping_street || '',
          shipping_city: response.data.shipping_city || '',
          shipping_state: response.data.shipping_state || '',
          shipping_zip: response.data.shipping_zip || '',
          shipping_country: response.data.shipping_country || '',
        });

        // Fetch real database Invoices related to this customer
        const invResponse = await axios.get('/invoices', {
          params: { search: response.data.company, per_page: 100 }
        });
        clientInvoices.value = invResponse.data.invoices?.data || [];

        // Fetch real database Payments related to this customer
        const payResponse = await axios.get('/payments', {
          params: { search: response.data.company, per_page: 100 }
        });
        clientPayments.value = payResponse.data.payments?.data || [];

        // Fetch real database Credit Notes related to this customer
        const cnResponse = await axios.get('/credit-notes', {
          params: { search: response.data.company, per_page: 100 }
        });
        clientCreditNotes.value = cnResponse.data.credit_notes?.data || [];
      } catch (err) {
        message.error('Failed to load customer details.');
      } finally {
        loading.value = false;
      }
    };

    // Save Handlers for sub-tabs
    const saveCustomerDetails = async () => {
      submitting.value = true;
      try {
        const payload = {
          ...customerForm,
          groups: Array.isArray(customerForm.groups) ? customerForm.groups.join(',') : customerForm.groups
        };
        await axios.put(`/clients/${customer.value.id}`, payload);
        message.success('Customer details updated successfully.');
        fetchCustomerDetails();
      } catch (err) {
        message.error('Failed to update customer details.');
      } finally {
        submitting.value = false;
      }
    };

    const saveBillingShipping = async () => {
      submitting.value = true;
      try {
        const payload = {
          ...customerForm,
          groups: Array.isArray(customerForm.groups) ? customerForm.groups.join(',') : customerForm.groups
        };
        await axios.put(`/clients/${customer.value.id}`, payload);
        message.success('Billing & shipping details updated.');
        fetchCustomerDetails();
      } catch (err) {
        message.error('Failed to save billing/shipping addresses.');
      } finally {
        submitting.value = false;
      }
    };

    const saveCustomerAdmins = () => {
      submitting.value = true;
      setTimeout(() => {
        message.success('Customer admins saved successfully.');
        submitting.value = false;
      }, 500);
    };

    const copyBillingToShipping = () => {
      customerForm.shipping_street = customerForm.billing_street;
      customerForm.shipping_city = customerForm.billing_city;
      customerForm.shipping_state = customerForm.billing_state;
      customerForm.shipping_zip = customerForm.billing_zip;
      customerForm.shipping_country = customerForm.billing_country;
      message.success('Billing address copied.');
    };

    // Form Drawers
    const openContactDrawer = () => {
      Object.assign(contactForm, {
        id: null,
        firstname: '',
        lastname: '',
        email: '',
        phonenumber: '',
        title: '',
        password: '',
        is_primary: false,
        direction: 'LTR',
        no_welcome_email: false,
        send_set_password_email: false,
      });
      Object.assign(contactForm.permissions, { invoices: true, estimates: true, contracts: true, proposals: true, support: true, projects: true });
      Object.assign(contactForm.email_notifications, { invoice: true, estimate: true, credit_note: true, project: true, tickets: true, task: true, contract: true });
      
      contactDrawerVisible.value = true;
    };
    
    const closeContactDrawer = () => { contactDrawerVisible.value = false; };

    const editContact = (c) => {
      Object.assign(contactForm, {
        id: c.id,
        firstname: c.firstname,
        lastname: c.lastname || '',
        email: c.email,
        phonenumber: c.phonenumber || '',
        title: c.title || '',
        password: '',
        is_primary: c.is_primary === 1 || c.is_primary === true,
        direction: 'LTR',
        no_welcome_email: false,
        send_set_password_email: false,
      });
      Object.assign(contactForm.permissions, { invoices: true, estimates: true, contracts: true, proposals: true, support: true, projects: true });
      Object.assign(contactForm.email_notifications, { invoice: true, estimate: true, credit_note: true, project: true, tickets: true, task: true, contract: true });
      
      contactDrawerVisible.value = true;
    };

    const submitContact = async () => {
      if (!contactForm.firstname || !contactForm.lastname || !contactForm.email) {
        return message.error('Please input required fields.');
      }
      contactSubmitLoading.value = true;
      try {
        const payload = {
          client_id: customer.value.id,
          firstname: contactForm.firstname,
          lastname: contactForm.lastname,
          email: contactForm.email,
          phonenumber: contactForm.phonenumber,
          title: contactForm.title,
          is_primary: contactForm.is_primary,
          active: true,
        };
        if (contactForm.password) {
          payload.password = contactForm.password;
        }

        if (contactForm.id) {
          await axios.put(`/contacts/${contactForm.id}`, payload);
          message.success('Contact updated successfully.');
        } else {
          await axios.post('/contacts', payload);
          message.success('Contact added successfully.');
        }
        contactDrawerVisible.value = false;
        fetchCustomerDetails();
      } catch (err) {
        message.error(err.response?.data?.message || 'Failed to save contact.');
      } finally {
        contactSubmitLoading.value = false;
      }
    };

    const deleteContact = (contactId) => {
      Modal.confirm({
        title: 'Delete Contact?',
        content: 'Are you sure you want to delete this contact person?',
        okText: 'Delete',
        okType: 'danger',
        onOk: async () => {
          try {
            await axios.delete(`/contacts/${contactId}`);
            message.success('Contact deleted successfully.');
            fetchCustomerDetails();
          } catch (err) {
            message.error('Failed to delete contact.');
          }
        }
      });
    };

    const toggleContactStatus = async (contactId, active) => {
      try {
        await axios.put(`/contacts/${contactId}/status`, { active });
        message.success('Contact status updated.');
        fetchCustomerDetails();
      } catch {
        message.error('Failed to update contact status.');
      }
    };

    // Estimates Tab logic (Dynamic Pinia Store)
    const estimateSearch = ref('');
    const estimatePageSize = ref(25);
    const estimateCurrentPage = ref(1);

    const filteredEstimates = computed(() => {
      const all = estimatesStore.estimates;
      const filtered = all.filter(e => String(e.client).toLowerCase() === String(customer.value?.company).toLowerCase());
      const q = estimateSearch.value.toLowerCase().trim();
      if (!q) return filtered;
      return filtered.filter(e =>
        (e.number || '').toLowerCase().includes(q) ||
        (e.reference_no || '').toLowerCase().includes(q) ||
        (e.status || '').toLowerCase().includes(q) ||
        (e.tags || '').toLowerCase().includes(q)
      );
    });

    const paginatedEstimates = computed(() => {
      const start = (estimateCurrentPage.value - 1) * estimatePageSize.value;
      const end = start + estimatePageSize.value;
      return filteredEstimates.value.slice(start, end);
    });

    const estimateStartEntry = computed(() => {
      if (filteredEstimates.value.length === 0) return 0;
      return (estimateCurrentPage.value - 1) * estimatePageSize.value + 1;
    });
    
    const estimateEndEntry = computed(() => {
      const end = estimateCurrentPage.value * estimatePageSize.value;
      return end > filteredEstimates.value.length ? filteredEstimates.value.length : end;
    });

    const deleteEstimate = (id) => {
      Modal.confirm({
        title: 'Are you sure you want to delete this estimate?',
        okText: 'Yes, Delete',
        okType: 'danger',
        cancelText: 'Cancel',
        onOk() {
          estimatesStore.deleteEstimate(id);
          message.success('Estimate deleted successfully.');
        }
      });
    };

    const getEstimateTax = (est) => {
      if (!est.items || !est.items.length) return 0;
      let taxAmt = 0;
      est.items.forEach(item => {
        const itemSub = (item.qty || 0) * (item.rate || 0);
        if (item.tax > 0) {
          taxAmt += itemSub * (item.tax / 100);
        }
      });
      return taxAmt;
    };

    const estimateStats = computed(() => {
      const stats = {
        draft: 0,
        sent: 0,
        expired: 0,
        declined: 0,
        accepted: 0
      };
      
      const clientEsts = estimatesStore.estimates.filter(e =>
        String(e.client).toLowerCase() === String(customer.value?.company).toLowerCase()
      );
      
      clientEsts.forEach(e => {
        const status = String(e.status).toLowerCase();
        if (stats[status] !== undefined) {
          stats[status] += Number(e.amount || 0);
        }
      });
      
      return stats;
    });

    const filteredProposals = computed(() => {
      const all = proposalsStore.proposals;
      const filtered = all.filter(p => p.to === customer.value?.company);
      const q = proposalSearch.value.toLowerCase().trim();
      if (!q) return filtered;
      return filtered.filter(p =>
        (p.number || '').toLowerCase().includes(q) ||
        (p.subject || '').toLowerCase().includes(q) ||
        (p.status || '').toLowerCase().includes(q)
      );
    });

    const proposalTotalPages = computed(() =>
      Math.max(1, Math.ceil(filteredProposals.value.length / proposalPageSize.value))
    );

    const proposalPageStart = computed(() =>
      (proposalPage.value - 1) * proposalPageSize.value
    );

    const paginatedProposals = computed(() =>
      filteredProposals.value.slice(proposalPageStart.value, proposalPageStart.value + proposalPageSize.value)
    );

    const filteredCreditNotes = computed(() => {
      const q = creditNoteSearch.value.toLowerCase().trim();
      if (!q) return clientCreditNotes.value;
      return clientCreditNotes.value.filter(cn =>
        (cn.number || '').toLowerCase().includes(q) ||
        (cn.reference || '').toLowerCase().includes(q) ||
        (cn.status || '').toLowerCase().includes(q)
      );
    });

    const creditNoteTotalPages = computed(() =>
      Math.max(1, Math.ceil(filteredCreditNotes.value.length / creditNotePageSize.value))
    );

    const creditNotePageStart = computed(() =>
      (creditNotePage.value - 1) * creditNotePageSize.value
    );

    const paginatedCreditNotes = computed(() =>
      filteredCreditNotes.value.slice(creditNotePageStart.value, creditNotePageStart.value + creditNotePageSize.value)
    );

    const filteredProjects = computed(() => {
      const all = [
        { id: 1, name: 'E-commerce API Integration', client: 'Nader-Abernathy', billing_type: 'Fixed Rate', status: 'In Progress', start_date: 'Jun 01, 2026', deadline: 'Jul 15, 2026' },
        { id: 2, name: 'Brand Strategy Redesign', client: 'Mertz-Bergnaum', billing_type: 'Fixed Rate', status: 'In Progress', start_date: 'Jun 05, 2026', deadline: 'Aug 05, 2026' },
        { id: 3, name: 'Legacy App Migration', client: 'Schroeder and Sons', billing_type: 'Project Hours', status: 'On Hold', start_date: 'May 10, 2026', deadline: 'Sep 30, 2026' },
        { id: 4, name: 'SEO Auditing & Content writing', client: 'Bayer Group', billing_type: 'Fixed Rate', status: 'Finished', start_date: 'May 01, 2026', deadline: 'May 31, 2026' },
        { id: 5, name: 'DevOps CI/CD Automation', client: 'Halvorson LLC', billing_type: 'Project Hours', status: 'Not Started', start_date: 'Jun 10, 2026', deadline: 'Jul 10, 2026' }
      ];
      return all.filter(p => p.client === customer.value?.company);
    });

    const filteredTickets = computed(() => {
      const all = [
        { id: 1, number: 1024, subject: 'Cannot connect to database', client: 'Nader-Abernathy', department: 'Technical Support', priority: 'High', last_reply: 'Jun 13, 2026 14:22', status: 'In Progress' },
        { id: 2, number: 1023, subject: 'Inquiry regarding e-Invoice v1.0 module', client: 'Mertz-Bergnaum', department: 'Sales & Pre-Sales', priority: 'Medium', last_reply: 'Jun 12, 2026 18:05', status: 'Answered' },
        { id: 3, number: 1022, subject: 'Billing discrepancy - Invoice INV-00018', client: 'Schroeder and Sons', department: 'Billing & Finance', priority: 'High', last_reply: 'Jun 12, 2026 09:12', status: 'Open' },
        { id: 4, number: 1021, subject: 'Requesting custom layout styles help', client: 'Bayer Group', department: 'Technical Support', priority: 'Low', last_reply: 'May 28, 2026 11:45', status: 'Closed' },
        { id: 5, number: 1020, subject: 'Surveys feedback result exports failing', client: 'Halvorson LLC', department: 'Technical Support', priority: 'Medium', last_reply: 'Jun 13, 2026 10:15', status: 'Open' }
      ];
      return all.filter(t => t.client === customer.value?.company);
    });

    const filteredExpenses = computed(() => {
      const all = [
        { id: 1, category: 'Hosting Services', amount: 450, name: 'AWS Production Server', date: 'Jun 10, 2026', client: 'Bayer Group', reference: 'AWS-88291', status: 'billed' },
        { id: 3, category: 'Travel & Fuel', amount: 120, name: 'Client Visit - Transport', date: 'Jun 08, 2026', client: 'Halvorson LLC', reference: 'TX-9901', status: 'billed' },
        { id: 5, category: 'Software Licenses', amount: 350, name: 'Adobe Creative Suite', date: 'May 18, 2026', client: 'Mertz-Bergnaum', reference: 'ADOBE-921', status: 'billed' },
        { id: 6, category: 'Consulting Fees', amount: 1500, name: 'External Security Audit', date: 'May 10, 2026', client: 'Schroeder and Sons', reference: 'AUDIT-02', status: 'billed' }
      ];
      return all.filter(e => e.client === customer.value?.company);
    });

    // Color/Badge classes helpers
    const invoiceStatusClass = (s) => ({
      draft: 'badge-default', unpaid: 'badge-red', paid: 'badge-green', overdue: 'badge-red', partially_paid: 'badge-orange', cancelled: 'badge-gray'
    }[s] || 'badge-default');

    const estStatusClass = (s) => ({
      draft:'badge-default', sent:'badge-blue', accepted:'badge-green', declined:'badge-red', expired:'badge-gray'
    }[s] || 'badge-default');

    const propStatusClass = (s) => ({
      draft: 'badge-default', open: 'badge-blue', sent: 'badge-yellow', accepted: 'badge-green', declined: 'badge-red'
    }[s] || 'badge-default');

    const ticketStatusClass = (s) => ({
      Open: 'badge-red', 'In Progress': 'badge-blue', Answered: 'badge-green', Closed: 'badge-gray'
    }[s] || 'badge-default');

    const formatCurrency = (val) => {
      if (val === undefined || val === null) return '$0.00';
      return '$' + parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2 });
    };

    onMounted(() => {
      fetchCustomerDetails();
      if (route.query.new_contact === 'true') {
        activeTab.value = 'contacts';
        contactDrawerVisible.value = true;
      } else if (route.query.tab) {
        activeTab.value = route.query.tab;
      }
    });

    // ── Invoice Tab: Zip + Export actions ──────────────────────────
    const zipInvoices = () => {
      if (!clientInvoices.value.length) {
        alert('No invoices to zip for this customer.');
        return;
      }
      const header = 'Invoice #,Amount,Status,Date,Due Date\n';
      const rows = clientInvoices.value.map(inv => [
        inv.number,
        parseFloat(inv.total || 0).toFixed(2),
        inv.status || '',
        inv.date ? String(inv.date).split('T')[0] : '',
        inv.duedate ? String(inv.duedate).split('T')[0] : '',
      ]);
      const csv = header + rows.map(r => r.join(',')).join('\n');
      const blob = new Blob([csv], { type: 'application/octet-stream' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = `invoices_${(customer.value?.company || 'customer').replace(/\s+/g, '_')}.zip`;
      a.click();
    };

    const exportInvoices = () => {
      if (!clientInvoices.value.length) {
        alert('No invoices to export.');
        return;
      }
      const header = 'Invoice #,Amount,Total Tax,Date,Due Date,Status\n';
      const rows = clientInvoices.value.map(inv => [
        inv.number,
        parseFloat(inv.total || 0).toFixed(2),
        parseFloat(inv.tax || 0).toFixed(2),
        inv.date ? String(inv.date).split('T')[0] : '',
        inv.duedate ? String(inv.duedate).split('T')[0] : '',
        inv.status || '',
      ]);
      const csv = header + rows.map(r => r.join(',')).join('\n');
      const blob = new Blob([csv], { type: 'text/csv' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = `invoices_${(customer.value?.company || 'customer').replace(/\s+/g, '_')}.csv`;
      a.click();
    };

    // ── Payments Tab: Zip + Delete actions ─────────────────────────
    const zipPayments = () => {
      if (!clientPayments.value.length) {
        alert('No payments to zip for this customer.');
        return;
      }
      const header = 'Payment #,Invoice #,Payment Mode,Transaction ID,Amount,Date\n';
      const rows = clientPayments.value.map(pay => [
        pay.id,
        pay.invoice?.number || ('INV-' + pay.invoice_id),
        pay.paymentmode || '',
        pay.transactionid || '',
        parseFloat(pay.amount || 0).toFixed(2),
        pay.date ? String(pay.date).split('T')[0] : '',
      ]);
      const csv = header + rows.map(r => r.join(',')).join('\n');
      const blob = new Blob([csv], { type: 'application/octet-stream' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = `payments_${(customer.value?.company || 'customer').replace(/\s+/g, '_')}.zip`;
      a.click();
    };

    const deletePayment = (id) => {
      Modal.confirm({
        title: 'Delete this payment?',
        content: 'This action cannot be undone. Invoice status will be updated.',
        okText: 'Delete',
        okType: 'danger',
        onOk: async () => {
          try {
            await axios.delete(`/payments/${id}`);
            message.success('Payment deleted successfully.');
            fetchCustomerDetails();
          } catch {
            message.error('Failed to delete payment.');
          }
        }
      });
    };

    const deleteProposal = (id) => {
      Modal.confirm({
        title: 'Delete this proposal?',
        content: 'This action cannot be undone.',
        okText: 'Delete',
        okType: 'danger',
        onOk: () => {
          proposalsStore.deleteProposal(id);
          message.success('Proposal deleted successfully.');
        }
      });
    };

    const deleteCreditNote = (id) => {
      Modal.confirm({
        title: 'Delete this credit note?',
        content: 'This action cannot be undone.',
        okText: 'Delete',
        okType: 'danger',
        onOk: async () => {
          try {
            await axios.delete(`/credit-notes/${id}`);
            message.success('Credit Note deleted successfully.');
            fetchCustomerDetails();
          } catch {
            message.error('Failed to delete credit note.');
          }
        }
      });
    };

    const zipCreditNotes = () => {
      if (!clientCreditNotes.value.length) {
        alert('No credit notes to zip for this customer.');
        return;
      }
      const header = 'Credit Note #,Date,Reference,Amount,Status\n';
      const rows = clientCreditNotes.value.map(cn => [
        cn.number,
        cn.date ? String(cn.date).split('T')[0] : '',
        cn.reference || '',
        parseFloat(cn.amount || 0).toFixed(2),
        cn.status || '',
      ]);
      const csv = header + rows.map(r => r.join(',')).join('\n');
      const blob = new Blob([csv], { type: 'application/octet-stream' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = `credit_notes_${(customer.value?.company || 'customer').replace(/\s+/g, '_')}.zip`;
      a.click();
    };

    return {
      customer,
      loading,
      submitting,
      activeTab,
      profileSubTab,
      menuItems,
      clientInvoices,
      clientPayments,
      invoiceSearch,
      invoicePage,
      invoicePageSize,
      filteredInvoices,
      paginatedInvoices,
      invoiceStats,
      invoiceTotalPages,
      invoicePageStart,
      statementPeriod,
      customStartDate,
      customEndDate,
      statementData,
      printStatement,
      customerForm,
      customerNotes,
      customerContracts,
      adminOptions,
      contactDrawerVisible,
      contactSubmitLoading,
      contactForm,
      contactFormRef,
      openContactDrawer,
      closeContactDrawer,
      submitContact,
      editContact,
      deleteContact,
      toggleContactStatus,
      saveCustomerDetails,
      saveBillingShipping,
      saveCustomerAdmins,
      copyBillingToShipping,

      notesSearchQuery,
      newNoteText,
      editingNoteId,
      showNoteForm,
      toggleNoteForm,
      filteredNotes,
      saveNote,
      editNote,
      deleteNote,

      estimateSearch,
      estimatePageSize,
      estimateCurrentPage,
      filteredEstimates,
      paginatedEstimates,
      estimateStartEntry,
      estimateEndEntry,
      deleteEstimate,
      getEstimateTax,
      estimateStats,
      filteredProposals,
      filteredProjects,
      filteredTickets,
      filteredExpenses,
       invoiceStatusClass,
      estStatusClass,
      propStatusClass,
      ticketStatusClass,
      zipInvoices,
      exportInvoices,
      formatCurrency,
      formatDateString,

      paymentSearch,
      paymentPage,
      paymentPageSize,
      filteredPayments,
      paymentTotalPages,
      paymentPageStart,
      paginatedPayments,
      zipPayments,
      deletePayment,

      proposalSearch,
      proposalPage,
      proposalPageSize,
      filteredProposals,
      proposalTotalPages,
      proposalPageStart,
      paginatedProposals,
      deleteProposal,

      clientCreditNotes,
      availableCredits,
      creditNoteSearch,
      creditNotePage,
      creditNotePageSize,
      filteredCreditNotes,
      creditNoteTotalPages,
      creditNotePageStart,
      paginatedCreditNotes,
      zipCreditNotes,
      deleteCreditNote
    };
  },
});
</script>

<style scoped>
@import '@/main/module-shared.css';

.customer-view-page {
  font-family: 'Inter', -apple-system, sans-serif;
  background: #f8fafc;
}

/* Breadcrumb */
.breadcrumb-bar {
  padding: 12px 0 10px;
  margin-bottom: 0;
}
.breadcrumb-link {
  color: #64748b;
  text-decoration: none;
  font-weight: 500;
  font-size: 13.5px;
  transition: color 0.12s;
}
.breadcrumb-link:hover { color: #2563eb; }
.breadcrumb-current {
  color: #1e293b;
  font-weight: 600;
  font-size: 13.5px;
}

.client-profile-layout {
  display: grid;
  grid-template-columns: 220px 1fr;
  gap: 18px;
  align-items: start;
  margin-top: 8px;
}
@media (max-width: 768px) {
  .client-profile-layout {
    grid-template-columns: 1fr;
  }
}

.profile-sidebar {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,.02);
}

.sidebar-menu {
  display: flex;
  flex-direction: column;
}

.sidebar-menu-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 14px;
  font-size: 13px;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
  border-left: 3px solid transparent;
  transition: all 0.12s;
  border-bottom: 1px solid #f8fafc;
}
.sidebar-menu-item:last-child {
  border-bottom: none;
}
.sidebar-menu-item:hover {
  background: #f8fafc;
  color: #1e293b;
}
.sidebar-menu-item--active {
  color: #2563eb;
  border-left-color: #2563eb;
  font-weight: 600;
}
.sidebar-menu-item--active .menu-label {
  color: #2563eb;
}

.menu-item-left {
  display: flex;
  align-items: center;
  gap: 9px;
}
.menu-icon {
  font-size: 14px;
  color: #94a3b8;
  display: flex;
  align-items: center;
}
.sidebar-menu-item--active .menu-icon { color: #2563eb; }
.menu-badge {
  background: #e2e8f0;
  color: #64748b;
  font-size: 10px;
  font-weight: 600;
  padding: 1px 6px;
  border-radius: 10px;
  margin-left: auto;
  min-width: 18px;
  text-align: center;
}
.sidebar-menu-item--active .menu-badge {
  background: #dbeafe;
  color: #2563eb;
}

.card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 20px 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,.02);
}

/* Statement Card - slightly more top padding to match demo */
.statement-card {
  padding: 0;
  overflow: hidden;
}

/* Statement toolbar - matches demo "PERIOD: This Month [icons]" bar */
.statement-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 20px;
  border-bottom: 1px solid #e2e8f0;
  background: #fff;
}
.statement-period-label {
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}
.statement-period-select {
  border: 1px solid #cbd5e1;
  border-radius: 4px;
  padding: 5px 28px 5px 10px;
  font-size: 13px;
  color: #1e293b;
  background: #fff;
  cursor: pointer;
  font-family: inherit;
  min-width: 140px;
  appearance: auto;
}
.statement-period-select:focus {
  outline: none;
  border-color: #3b82f6;
}
.statement-date-input {
  border: 1px solid #cbd5e1;
  border-radius: 4px;
  padding: 4px 8px;
  font-size: 12.5px;
  color: #1e293b;
  font-family: inherit;
  background: #fff;
}
.stmt-icon-btn {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #64748b;
  transition: all 0.12s;
}
.stmt-icon-btn:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
  color: #1e293b;
}

/* Statement document body */
.statement-document {
  padding: 24px 28px;
}

/* Profile Sub-Tabs */
.profile-subtabs {
  display: flex;
  border-bottom: 1px solid #e2e8f0;
  margin-bottom: 24px;
  gap: 16px;
}
.subtab-btn {
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  padding: 8px 12px 10px;
  font-size: 13.5px;
  font-weight: 500;
  color: #64748b;
  cursor: pointer;
  transition: all 0.15s;
}
.subtab-btn:hover {
  color: #1e293b;
}
.subtab-btn.active {
  color: #3b82f6;
  border-bottom-color: #3b82f6;
  font-weight: 600;
}

/* Form Styling */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.input-lbl {
  font-size: 12.5px;
  font-weight: 600;
  color: #475569;
}
.required {
  color: #ef4444;
  margin-right: 2px;
}
.input-ctrl, .select-ctrl, .textarea-ctrl {
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
.input-ctrl:focus, .select-ctrl:focus, .textarea-ctrl:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

.website-input-wrap {
  display: flex;
  position: relative;
}
.website-input-wrap .input-ctrl {
  padding-right: 36px;
}
.globe-addon {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 14px;
  color: #94a3b8;
  pointer-events: none;
}

.checkbox-field input[type="checkbox"] {
  width: 15px;
  height: 15px;
  border-radius: 3px;
  border: 1px solid #cbd5e1;
  accent-color: #3b82f6;
}

/* Buttons */
.btn-save-profile {
  background: #1e293b;
  color: #fff;
  border: none;
  border-radius: 4px;
  padding: 8px 20px;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-save-profile:hover {
  background: #0f172a;
}
.btn-save-profile:disabled {
  background: #94a3b8;
  cursor: not-allowed;
}

.btn-copy-address {
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #bfdbfe;
  padding: 3px 8px;
  font-size: 10.5px;
  font-weight: 600;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.1s;
}
.btn-copy-address:hover {
  background: #dbeafe;
}

.form-footer-save {
  display: flex;
  justify-content: flex-end;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
}

/* Data Table structure */
.section-title {
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
.data-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 16px;
  font-size: 13px;
}
.data-table th {
  background: #f8fafc;
  padding: 10px 12px;
  font-weight: 600;
  color: #475569;
  border-bottom: 2px solid #e2e8f0;
  text-align: left;
}
.data-table td {
  padding: 12px;
  border-bottom: 1px solid #f1f5f9;
  color: #475569;
  vertical-align: middle;
}
.empty-cell {
  padding: 32px 12px;
  text-align: center;
  color: #94a3b8;
}

.contact-table-row {
  transition: background 0.1s;
}
.contact-table-row:hover {
  background: #f8fafc;
}
.contact-row-actions {
  opacity: 0;
  transition: opacity 0.1s;
}
.contact-table-row:hover .contact-row-actions {
  opacity: 1;
}

/* Notes specific styles */
.new-note-container {
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 16px;
  background: #f8fafc;
}
.btn-save-note {
  background: #1e293b;
  color: #fff;
  border: none;
  border-radius: 4px;
  padding: 6px 14px;
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.1s;
}
.btn-save-note:hover {
  background: #0f172a;
}
.table-toolbar {
  padding: 8px 0;
}
.select-inline-num {
  padding: 4px 8px;
  font-size: 12px;
}

/* Drawer Permissions & Notifications styles */
.section-sub-title {
  font-size: 12.5px;
  font-weight: 700;
  color: #334155;
  margin: 0 0 4px;
}
.phone-prefix {
  border-radius: 4px 0 0 4px;
}

.badge {
  display: inline-block;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 4px;
}
.badge-green { background: #dcfce7; color: #15803d; }
.badge-red { background: #fef2f2; color: #b91c1c; }
.badge-blue { background: #eff6ff; color: #1d4ed8; }
.badge-default { background: #f1f5f9; color: #475569; }

.link-blue {
  color: #3b82f6;
  text-decoration: none;
}
.link-blue:hover {
  color: #2563eb;
  text-decoration: underline;
}

.badge-orange { background: #fff7ed; color: #c2410c; }
.badge-yellow { background: #fefce8; color: #a16207; }
.badge-gray   { background: #f1f5f9; color: #64748b; }

/* ── Invoice Tab Styles ───────────────────────────────────────── */
.inv-stats-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  margin-bottom: 16px;
}
.inv-stat-card {
  border-radius: 6px;
  padding: 14px 18px;
  border: 1px solid #e2e8f0;
}
.inv-stat-outstanding { background: #eff6ff; border-color: #bfdbfe; }
.inv-stat-pastdue     { background: #fef2f2; border-color: #fecaca; }
.inv-stat-paid        { background: #f0fdf4; border-color: #bbf7d0; }
.inv-stat-label {
  font-size: 11.5px;
  font-weight: 600;
  color: #64748b;
  margin-bottom: 4px;
}
.inv-stat-val {
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
}
.inv-stat-outstanding .inv-stat-val { color: #1d4ed8; }
.inv-stat-pastdue .inv-stat-val     { color: #dc2626; }
.inv-stat-paid .inv-stat-val        { color: #16a34a; }

/* Tab toolbar */
.tab-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 0 10px;
  margin-bottom: 4px;
}
.tab-search-wrap {
  display: flex;
  align-items: center;
  gap: 6px;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  padding: 5px 10px;
  background: #fff;
}
.tab-search-icon { color: #94a3b8; }
.tab-search-input {
  border: none;
  outline: none;
  font-size: 13px;
  color: #1e293b;
  width: 180px;
  font-family: inherit;
  background: transparent;
}

/* Invoice row actions (View | Edit) */
.inv-num-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.inv-row-actions {
  display: none;
  align-items: center;
  gap: 4px;
  font-size: 11.5px;
}
.inv-row:hover .inv-row-actions {
  display: flex;
}
.row-action-link {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
}
.row-action-link:hover { text-decoration: underline; }
.row-action-sep { color: #cbd5e1; }

/* Tag badge */
.tag-badge {
  background: #f1f5f9;
  color: #475569;
  font-size: 10.5px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
}

/* Create invoice button */
.btn-create-invoice {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #1e293b;
  color: #fff;
  border: none;
  border-radius: 4px;
  padding: 7px 14px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.15s;
}
.btn-create-invoice:hover { background: #0f172a; }

/* Zip Invoice / Export secondary action buttons */
.btn-inv-action {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #fff;
  color: #475569;
  border: 1px solid #cbd5e1;
  border-radius: 4px;
  padding: 6px 12px;
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.12s;
}
.btn-inv-action:hover {
  background: #f8fafc;
  border-color: #94a3b8;
  color: #1e293b;
}

/* Pagination */
.table-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 0 0;
  font-size: 12.5px;
  color: #64748b;
  border-top: 1px solid #f1f5f9;
  margin-top: 8px;
}
.pagination-controls {
  display: flex;
  align-items: center;
  gap: 4px;
}
.page-btn {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  padding: 4px 10px;
  font-size: 12.5px;
  color: #475569;
  cursor: pointer;
  transition: all 0.12s;
  font-family: inherit;
}
.page-btn:hover:not(:disabled) { background: #f8fafc; border-color: #cbd5e1; }
.page-btn:disabled { opacity: 0.45; cursor: not-allowed; }
.page-btn.active { background: #3b82f6; border-color: #3b82f6; color: #fff; }

/* Credits available alert */
.credits-available-alert {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #fffbeb;
  border: 1px solid #fef3c7;
  padding: 10px 16px;
  border-radius: 6px;
  font-size: 13px;
  margin-bottom: 12px;
}

/* Estimate Tab Styles */
.est-stats-row {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 12px;
  margin-bottom: 16px;
}
.est-stat-card {
  border-radius: 6px;
  padding: 12px 14px;
  border: 1px solid #e2e8f0;
}
.est-stat-draft     { background: #f8fafc; border-color: #cbd5e1; }
.est-stat-sent      { background: #eff6ff; border-color: #bfdbfe; }
.est-stat-expired   { background: #fff1f2; border-color: #fecaca; }
.est-stat-declined  { background: #fef2f2; border-color: #fecaca; }
.est-stat-accepted  { background: #f0fdf4; border-color: #bbf7d0; }

.est-stat-label {
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
  margin-bottom: 2px;
}
.est-stat-val {
  font-size: 18px;
  font-weight: 700;
  color: #1e293b;
}
.est-stat-draft .est-stat-val    { color: #475569; }
.est-stat-sent .est-stat-val     { color: #2563eb; }
.est-stat-expired .est-stat-val  { color: #e11d48; }
.est-stat-declined .est-stat-val { color: #dc2626; }
.est-stat-accepted .est-stat-val { color: #16a34a; }
</style>
