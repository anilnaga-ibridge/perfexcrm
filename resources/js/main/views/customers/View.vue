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

          <!-- ========== LIST VIEW ========== -->
          <template v-if="subView === 'list'">
            <h2 class="section-title mb-4">Subscriptions</h2>

            <!-- New Subscription Button -->
            <div class="mb-4 no-print">
              <button class="btn-new-subscription" @click="openSubscriptionForm()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Subscription
              </button>
            </div>

            <!-- Toolbar -->
            <div class="table-toolbar no-print flex justify-between items-center mb-4">
              <div class="toolbar-left flex items-center gap-2">
                <select class="select-ctrl select-inline-num" style="width:70px; padding:4px 8px; font-size:12.5px;" v-model.number="subPageSize">
                  <option :value="10">10</option>
                  <option :value="25">25</option>
                  <option :value="50">50</option>
                </select>
                <button class="btn-outline" style="padding:4px 12px; font-size:12.5px;" @click="exportSubscriptions">Export</button>
                <button class="btn-outline sub-refresh-btn" style="padding:5px 9px;" title="Refresh" @click="loadSubscriptions">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                </button>
              </div>
              <div class="toolbar-right">
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13" class="text-slate-400"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                  </span>
                  <input type="text" class="input-ctrl pl-8 text-xs" style="width:220px;" placeholder="Search..." v-model="subSearch" />
                </div>
              </div>
            </div>

            <!-- Table -->
            <table class="data-table">
              <thead>
                <tr>
                  <th style="width:50px;">#</th>
                  <th>Subscription Name</th>
                  <th>Project</th>
                  <th>Status</th>
                  <th>Next Billing Cycle</th>
                  <th>Date Subscribed</th>
                  <th>Last Sent</th>
                  <th style="width:80px;" class="no-print">Options</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(sub, idx) in paginatedSubscriptions" :key="sub.id" class="inv-row">
                  <td class="text-slate-500">{{ subPageStart + idx + 1 }}</td>
                  <td>
                    <div class="inv-num-cell">
                      <a class="link-blue font-semibold cursor-pointer" @click="openSubscriptionForm(sub)">{{ sub.name }}</a>
                      <div class="inv-row-actions">
                        <a class="row-action-link cursor-pointer" @click="openSubscriptionForm(sub)">Edit</a>
                        <span class="row-action-sep">|</span>
                        <a class="row-action-link text-rose-600 cursor-pointer" @click="deleteSubscription(sub.id)">Delete</a>
                      </div>
                    </div>
                  </td>
                  <td>{{ sub.project?.name || '—' }}</td>
                  <td><span class="badge" :class="subStatusClass(sub.status)">{{ subStatusLabel(sub.status) }}</span></td>
                  <td>{{ nextBillingCycle(sub) }}</td>
                  <td>{{ formatDateString(sub.created_at) }}</td>
                  <td class="text-slate-500">{{ sub.last_sent ? formatDateString(sub.last_sent) : 'Never' }}</td>
                  <td class="no-print">
                    <div class="flex items-center gap-2.5">
                      <button @click="openSubscriptionForm(sub)" class="text-slate-500 hover:text-indigo-600 transition" title="Edit Subscription">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"></path></svg>
                      </button>
                      <button @click="deleteSubscription(sub.id)" class="text-slate-500 hover:text-rose-600 transition" title="Delete Subscription">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredSubscriptions.length === 0">
                  <td colspan="8" class="empty-cell">No entries found</td>
                </tr>
              </tbody>
            </table>

            <!-- Pagination -->
            <div class="table-pagination" v-if="filteredSubscriptions.length > 0">
              <span class="pagination-info">
                Showing {{ subPageStart + 1 }} to {{ Math.min(subPageStart + subPageSize, filteredSubscriptions.length) }} of {{ filteredSubscriptions.length }} entries
              </span>
              <div class="pagination-controls">
                <button class="page-btn" :disabled="subPage === 1" @click="subPage--">Previous</button>
                <button
                  v-for="p in subTotalPages" :key="p"
                  :class="['page-btn', { active: p === subPage }]"
                  @click="subPage = p"
                >{{ p }}</button>
                <button class="page-btn" :disabled="subPage === subTotalPages" @click="subPage++">Next</button>
              </div>
            </div>
          </template>

          <!-- ========== CREATE / EDIT FORM VIEW ========== -->
          <template v-else>
            <h2 class="section-title mb-6">{{ editingSubId ? 'Edit Subscription' : 'New Subscription' }}</h2>

            <form @submit.prevent="saveSubscription" class="subscription-form-wrap">

              <!-- Top group: plan / quantity / first billing date -->
              <div class="sub-form-group-box">
                <div class="form-group">
                  <label class="input-lbl"><span class="required">*</span> Billing Plan</label>
                  <select class="select-ctrl" v-model="subForm.stripe_plan" @change="onBillingPlanChange">
                    <option value="">Select Stripe plan</option>
                    <option v-for="p in billingPlans" :key="p.code" :value="p.code">{{ p.name }}</option>
                  </select>
                </div>

                <div class="form-group">
                  <label class="input-lbl"><span class="required">*</span> Quantity</label>
                  <input type="number" min="1" class="input-ctrl" v-model.number="subForm.quantity" />
                </div>

                <div class="form-group">
                  <label class="input-lbl">
                    <span class="hint-icon" title="The date the first invoice will be generated for this subscription">?</span>
                    First Billing Date
                  </label>
                  <input type="date" class="input-ctrl" v-model="subForm.start_date" />
                </div>
              </div>

              <!-- Main fields -->
              <div class="form-group">
                <label class="input-lbl"><span class="required">*</span> Subscription Name</label>
                <input type="text" class="input-ctrl" v-model="subForm.name" required />
              </div>

              <div class="form-group">
                <label class="input-lbl">Description</label>
                <textarea class="textarea-ctrl" rows="4" v-model="subForm.description"></textarea>
              </div>

              <div class="checkbox-field">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="checkbox" v-model="subForm.include_description" />
                  <span class="text-xs text-slate-600 font-medium">
                    <span class="hint-icon" title="If enabled the description will be shown in the invoice item">?</span>
                    Include description in invoice item
                  </span>
                </label>
              </div>

              <div class="form-group">
                <label class="input-lbl"><span class="required">*</span> Customer</label>
                <select class="select-ctrl" v-model="subForm.client_id">
                  <option v-for="c in customerSelectOptions" :key="c.id" :value="c.id">{{ c.company }}</option>
                </select>
              </div>

              <div class="form-group">
                <label class="input-lbl"><span class="required">*</span> Currency</label>
                <select class="select-ctrl" v-model="subForm.currency">
                  <option value="USD">USD $</option>
                  <option value="EUR">EUR €</option>
                  <option value="GBP">GBP £</option>
                  <option value="INR">INR ₹</option>
                  <option value="AUD">AUD $</option>
                </select>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                <div class="form-group">
                  <label class="input-lbl">Tax 1 (Stripe)</label>
                  <select class="select-ctrl" v-model="subForm.tax_1">
                    <option value="">No Tax</option>
                    <option value="txr_vat20">VAT 20%</option>
                    <option value="txr_gst10">GST 10%</option>
                    <option value="txr_sales7">Sales Tax 7%</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="input-lbl">Tax 2 (Stripe)</label>
                  <select class="select-ctrl" v-model="subForm.tax_2">
                    <option value="">No Tax</option>
                    <option value="txr_vat20">VAT 20%</option>
                    <option value="txr_gst10">GST 10%</option>
                    <option value="txr_sales7">Sales Tax 7%</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="input-lbl">Terms &amp; Conditions</label>
                <textarea class="textarea-ctrl" rows="4" v-model="subForm.terms" placeholder="Enter customer terms & conditions to be displayed to the customer before subscribe to the subscription."></textarea>
              </div>

              <div class="form-footer-save no-print flex justify-end gap-2">
                <button type="button" class="btn-outline" style="padding:7px 16px;font-size:13px;" @click="subView = 'list'">Cancel</button>
                <button type="submit" class="btn-save-profile" :disabled="subSaving">
                  {{ subSaving ? 'Saving...' : 'Save' }}
                </button>
              </div>
            </form>
          </template>
        </div>

        <!-- TAB 11: Expenses -->
        <div class="card" v-if="activeTab === 'expenses'">

          <!-- ========== LIST VIEW ========== -->
          <template v-if="expView === 'list'">
            <h2 class="section-title mb-4">Expenses</h2>

            <!-- Record Expense Button -->
            <div class="mb-4 no-print">
              <button class="btn-new-subscription" @click="openExpenseForm()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Record Expense
              </button>
            </div>

            <!-- Summary Cards -->
            <div class="exp-stats-row">
              <div class="exp-stat-card">
                <div class="exp-stat-label exp-lbl-total">Total</div>
                <div class="exp-stat-val">{{ formatCurrency(expenseSummary.total) }}</div>
              </div>
              <div class="exp-stat-card">
                <div class="exp-stat-label exp-lbl-billable">Billable</div>
                <div class="exp-stat-val">{{ formatCurrency(expenseSummary.billable) }}</div>
              </div>
              <div class="exp-stat-card">
                <div class="exp-stat-label exp-lbl-nonbillable">Non Billable</div>
                <div class="exp-stat-val">{{ formatCurrency(expenseSummary.nonBillable) }}</div>
              </div>
              <div class="exp-stat-card">
                <div class="exp-stat-label exp-lbl-notinvoiced">Not Invoiced</div>
                <div class="exp-stat-val">{{ formatCurrency(expenseSummary.notInvoiced) }}</div>
              </div>
              <div class="exp-stat-card">
                <div class="exp-stat-label exp-lbl-billed">Billed</div>
                <div class="exp-stat-val">{{ formatCurrency(expenseSummary.billed) }}</div>
              </div>
            </div>

            <!-- Toolbar -->
            <div class="table-toolbar no-print flex justify-between items-center mb-4">
              <div class="toolbar-left flex items-center gap-2">
                <select class="select-ctrl select-inline-num" style="width:70px; padding:4px 8px; font-size:12.5px;" v-model.number="expPageSize">
                  <option :value="10">10</option>
                  <option :value="25">25</option>
                  <option :value="50">50</option>
                </select>
                <button class="btn-outline" style="padding:4px 12px; font-size:12.5px;" @click="exportExpenses">Export</button>
                <button class="btn-outline sub-refresh-btn" style="padding:5px 9px;" title="Refresh" @click="loadExpenses">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                </button>
              </div>
              <div class="toolbar-right">
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13" class="text-slate-400"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                  </span>
                  <input type="text" class="input-ctrl pl-8 text-xs" style="width:220px;" placeholder="Search..." v-model="expSearch" />
                </div>
              </div>
            </div>

            <!-- Table -->
            <table class="data-table">
              <thead>
                <tr>
                  <th style="width:50px;">#</th>
                  <th>Category</th>
                  <th>Amount</th>
                  <th>Name</th>
                  <th>Receipt</th>
                  <th>Date</th>
                  <th>Project</th>
                  <th>Invoice</th>
                  <th>Reference #</th>
                  <th>Payment Mode</th>
                  <th style="width:80px;" class="no-print">Options</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(exp, idx) in paginatedExpenses" :key="exp.id" class="inv-row">
                  <td class="text-slate-500">{{ expPageStart + idx + 1 }}</td>
                  <td>
                    <div class="inv-num-cell">
                      <a class="link-blue font-semibold cursor-pointer" @click="openExpenseForm(exp)">{{ expenseCategoryName(exp.category_id) }}</a>
                      <div class="inv-row-actions">
                        <a class="row-action-link cursor-pointer" @click="openExpenseForm(exp)">Edit</a>
                        <span class="row-action-sep">|</span>
                        <a class="row-action-link text-rose-600 cursor-pointer" @click="deleteExpense(exp.id)">Delete</a>
                      </div>
                    </div>
                  </td>
                  <td class="font-semibold">{{ formatCurrency(exp.amount) }}</td>
                  <td>{{ exp.name || '—' }}</td>
                  <td class="text-slate-400">—</td>
                  <td>{{ formatDateString(exp.date) }}</td>
                  <td class="text-slate-400">—</td>
                  <td class="text-slate-400">—</td>
                  <td>{{ exp.reference || '—' }}</td>
                  <td>{{ exp.payment_mode || '—' }}</td>
                  <td class="no-print">
                    <div class="flex items-center gap-2.5">
                      <button @click="openExpenseForm(exp)" class="text-slate-500 hover:text-indigo-600 transition" title="Edit Expense">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"></path></svg>
                      </button>
                      <button @click="deleteExpense(exp.id)" class="text-slate-500 hover:text-rose-600 transition" title="Delete Expense">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredExpenses.length === 0">
                  <td colspan="11" class="empty-cell">No entries found</td>
                </tr>
              </tbody>
            </table>

            <!-- Pagination -->
            <div class="table-pagination" v-if="filteredExpenses.length > 0">
              <span class="pagination-info">
                Showing {{ expPageStart + 1 }} to {{ Math.min(expPageStart + expPageSize, filteredExpenses.length) }} of {{ filteredExpenses.length }} entries
              </span>
              <div class="pagination-controls">
                <button class="page-btn" :disabled="expPage === 1" @click="expPage--">Previous</button>
                <button
                  v-for="p in expTotalPages" :key="p"
                  :class="['page-btn', { active: p === expPage }]"
                  @click="expPage = p"
                >{{ p }}</button>
                <button class="page-btn" :disabled="expPage === expTotalPages" @click="expPage++">Next</button>
              </div>
            </div>
          </template>

          <!-- ========== ADD / EDIT EXPENSE FORM ========== -->
          <template v-else>
            <h2 class="section-title mb-6">{{ editingExpId ? 'Edit Expense' : 'Add New Expense' }}</h2>

            <form @submit.prevent="saveExpense" class="subscription-form-wrap">

              <!-- Attach Receipt dropzone -->
              <div class="receipt-dropzone">Attach Receipt</div>

              <!-- Expandable field toggles -->
              <div class="exp-toggles">
                <a class="exp-toggle-link" :class="{ active: expFields.recurring }" @click="expFields.recurring = !expFields.recurring">+ Recurring</a>
                <a class="exp-toggle-link" :class="{ active: expFields.name }" @click="expFields.name = !expFields.name">+ Name</a>
                <a class="exp-toggle-link" :class="{ active: expFields.reference }" @click="expFields.reference = !expFields.reference">+ Reference #</a>
                <a class="exp-toggle-link" :class="{ active: expFields.note }" @click="expFields.note = !expFields.note">+ Note</a>
              </div>

              <!-- Recurring (visual) -->
              <div class="form-group" v-if="expFields.recurring">
                <label class="input-lbl">Recurring</label>
                <select class="select-ctrl" v-model="expForm.recurring">
                  <option value="">Not recurring</option>
                  <option value="1-month">Every 1 month</option>
                  <option value="3-month">Every 3 months</option>
                  <option value="6-month">Every 6 months</option>
                  <option value="12-month">Every 12 months</option>
                </select>
              </div>

              <!-- Name -->
              <div class="form-group" v-if="expFields.name">
                <label class="input-lbl">Name</label>
                <input type="text" class="input-ctrl" v-model="expForm.name" />
              </div>

              <!-- Reference # -->
              <div class="form-group" v-if="expFields.reference">
                <label class="input-lbl">Reference #</label>
                <input type="text" class="input-ctrl" v-model="expForm.reference" />
              </div>

              <!-- Note -->
              <div class="form-group" v-if="expFields.note">
                <label class="input-lbl">Note</label>
                <textarea class="textarea-ctrl" rows="3" v-model="expForm.note"></textarea>
              </div>

              <!-- Expense Category -->
              <div class="form-group">
                <label class="input-lbl"><span class="required">*</span> Expense Category</label>
                <div class="flex items-center gap-2">
                  <select class="select-ctrl" v-model.number="expForm.category_id" style="flex:1">
                    <option :value="null">Nothing selected</option>
                    <option v-for="c in expenseCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                  </select>
                  <button type="button" class="exp-add-btn" title="Add new category" @click="addExpenseCategory">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                  </button>
                </div>
              </div>

              <!-- Expense Date -->
              <div class="form-group">
                <label class="input-lbl"><span class="required">*</span> Expense Date</label>
                <input type="date" class="input-ctrl" v-model="expForm.date" />
              </div>

              <!-- Amount group box -->
              <div class="sub-form-group-box">
                <div class="form-group">
                  <label class="input-lbl"><span class="required">*</span> Amount</label>
                  <div class="flex items-center gap-2">
                    <input type="number" step="0.01" min="0" class="input-ctrl" v-model.number="expForm.amount" style="flex:1" placeholder="0.00" />
                    <select class="select-ctrl" v-model="expForm.currency" style="width:110px">
                      <option value="USD">USD $</option>
                      <option value="EUR">EUR €</option>
                      <option value="GBP">GBP £</option>
                      <option value="INR">INR ₹</option>
                    </select>
                  </div>
                </div>

                <div class="exp-toggles" style="margin-bottom:0;">
                  <a class="exp-toggle-link" :class="{ active: expFields.tax }" @click="expFields.tax = !expFields.tax">+ Tax</a>
                  <a class="exp-toggle-link" :class="{ active: expFields.paymentMode }" @click="expFields.paymentMode = !expFields.paymentMode">+ Payment Mode</a>
                </div>

                <div class="form-group" v-if="expFields.tax" style="margin-top:14px;">
                  <label class="input-lbl">Tax</label>
                  <select class="select-ctrl" v-model="expForm.tax">
                    <option value="">No Tax</option>
                    <option value="vat20">VAT 20%</option>
                    <option value="gst10">GST 10%</option>
                    <option value="sales7">Sales Tax 7%</option>
                  </select>
                </div>

                <div class="form-group" v-if="expFields.paymentMode" style="margin-top:14px;">
                  <label class="input-lbl">Payment Mode</label>
                  <select class="select-ctrl" v-model="expForm.payment_mode">
                    <option value="">Select payment mode</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Cash">Cash</option>
                    <option value="PayPal">PayPal</option>
                    <option value="Stripe">Stripe</option>
                  </select>
                </div>
              </div>

              <!-- Customer -->
              <div class="form-group">
                <label class="input-lbl">Customer</label>
                <select class="select-ctrl" v-model="expForm.client_id">
                  <option v-for="c in customerSelectOptions" :key="c.id" :value="c.id">{{ c.company }}</option>
                </select>
              </div>

              <!-- Billable -->
              <div class="checkbox-field">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="checkbox" v-model="expForm.billable" />
                  <span class="text-xs text-slate-600 font-medium">Billable</span>
                </label>
              </div>

              <!-- Project (visual only) -->
              <div class="form-group">
                <label class="input-lbl">Project</label>
                <select class="select-ctrl" v-model="expForm.project">
                  <option value="">Select and begin typing</option>
                </select>
              </div>

              <div class="form-footer-save no-print flex justify-end gap-2">
                <button type="button" class="btn-outline" style="padding:7px 16px;font-size:13px;" @click="expView = 'list'">Cancel</button>
                <button type="submit" class="btn-save-profile" :disabled="expSaving">
                  {{ expSaving ? 'Saving...' : 'Save' }}
                </button>
              </div>
            </form>
          </template>
        </div>

        <!-- TAB 12: Contracts -->
        <div class="card" v-if="activeTab === 'contracts'">

          <!-- ========== LIST VIEW ========== -->
          <template v-if="contractView === 'list'">
            <h2 class="section-title mb-4">Contracts</h2>

            <!-- New Contract Button -->
            <div class="mb-4 no-print">
              <button class="btn-new-subscription" @click="openContractForm()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Contract
              </button>
            </div>

            <!-- Toolbar -->
            <div class="table-toolbar no-print flex justify-between items-center mb-4">
              <div class="toolbar-left flex items-center gap-2">
                <select class="select-ctrl select-inline-num" style="width:70px; padding:4px 8px; font-size:12.5px;" v-model.number="contractPageSize">
                  <option :value="10">10</option>
                  <option :value="25">25</option>
                  <option :value="50">50</option>
                </select>
                <button class="btn-outline" style="padding:4px 12px; font-size:12.5px;" @click="exportContracts">Export</button>
                <button class="btn-outline sub-refresh-btn" style="padding:5px 9px;" title="Refresh" @click="loadContracts">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                </button>
              </div>
              <div class="toolbar-right">
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13" class="text-slate-400"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                  </span>
                  <input type="text" class="input-ctrl pl-8 text-xs" style="width:220px;" placeholder="Search..." v-model="contractSearch" />
                </div>
              </div>
            </div>

            <!-- Table -->
            <table class="data-table">
              <thead>
                <tr>
                  <th style="width:50px;">#</th>
                  <th>Subject</th>
                  <th>Contract Type</th>
                  <th>Contract Value</th>
                  <th>Start Date</th>
                  <th>End Date</th>
                  <th>Project</th>
                  <th>Signature</th>
                  <th style="width:80px;" class="no-print">Options</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(contract, idx) in paginatedContracts" :key="contract.id" class="inv-row">
                  <td class="text-slate-500">{{ contractPageStart + idx + 1 }}</td>
                  <td>
                    <div class="inv-num-cell">
                      <a class="link-blue font-semibold cursor-pointer" @click="openContractForm(contract)">{{ contract.subject }}</a>
                      <div class="inv-row-actions">
                        <a class="row-action-link cursor-pointer" @click="openContractForm(contract)">Edit</a>
                        <span class="row-action-sep">|</span>
                        <a class="row-action-link text-rose-600 cursor-pointer" @click="deleteContract(contract.id)">Delete</a>
                      </div>
                    </div>
                  </td>
                  <td>{{ contractTypeName(contract.contract_type_id) }}</td>
                  <td class="font-semibold">{{ formatCurrency(contract.value) }}</td>
                  <td>{{ formatDateString(contract.start_date) }}</td>
                  <td>{{ formatDateString(contract.end_date) }}</td>
                  <td class="text-slate-400">—</td>
                  <td>
                    <span class="badge" :class="contract.signed ? 'badge-green' : 'badge-red'">
                      {{ contract.signed ? 'Signed' : 'Not Signed' }}
                    </span>
                  </td>
                  <td class="no-print">
                    <div class="flex items-center gap-2.5">
                      <button @click="openContractForm(contract)" class="text-slate-500 hover:text-indigo-600 transition" title="Edit Contract">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"></path></svg>
                      </button>
                      <button @click="deleteContract(contract.id)" class="text-slate-500 hover:text-rose-600 transition" title="Delete Contract">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="customerContracts.length === 0">
                  <td colspan="9" class="empty-cell">No entries found</td>
                </tr>
              </tbody>
            </table>

            <!-- Pagination -->
            <div class="table-pagination" v-if="customerContracts.length > 0">
              <span class="pagination-info">
                Showing {{ contractPageStart + 1 }} to {{ Math.min(contractPageStart + contractPageSize, customerContracts.length) }} of {{ customerContracts.length }} entries
              </span>
              <div class="pagination-controls">
                <button class="page-btn" :disabled="contractPage === 1" @click="contractPage--">Previous</button>
                <button
                  v-for="p in contractTotalPages" :key="p"
                  :class="['page-btn', { active: p === contractPage }]"
                  @click="contractPage = p"
                >{{ p }}</button>
                <button class="page-btn" :disabled="contractPage === contractTotalPages" @click="contractPage++">Next</button>
              </div>
            </div>
          </template>

          <!-- ========== CREATE / EDIT FORM VIEW ========== -->
          <template v-else>
            <h2 class="section-title mb-6">{{ editingContractId ? 'Edit Contract' : 'New Contract' }}</h2>

            <form @submit.prevent="saveContract" class="subscription-form-wrap">

              <!-- Checkbox Options: Trash / Hide from customer -->
              <div class="flex gap-6 mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="checkbox" v-model="contractForm.trash" />
                  <span class="text-xs text-slate-600 font-medium">Trash</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="checkbox" v-model="contractForm.hide_from_customer" />
                  <span class="text-xs text-slate-600 font-medium">Hide from customer</span>
                </label>
              </div>

              <!-- Customer Selection -->
              <div class="form-group">
                <label class="input-lbl"><span class="required">*</span> Customer</label>
                <select class="select-ctrl" v-model="contractForm.client_id">
                  <option v-for="c in customerSelectOptions" :key="c.id" :value="c.id">{{ c.company }}</option>
                </select>
              </div>

              <!-- Subject -->
              <div class="form-group">
                <label class="input-lbl"><span class="required">*</span> Subject</label>
                <input type="text" class="input-ctrl" v-model="contractForm.subject" required />
              </div>

              <!-- Contract Value -->
              <div class="form-group">
                <label class="input-lbl">Contract Value</label>
                <div class="flex items-center">
                  <span class="input-prefix" style="padding: 8px 12px; background: #e2e8f0; border: 1.5px solid #cbd5e1; border-right: none; border-top-left-radius: 6px; border-bottom-left-radius: 6px; font-size: 13px; font-weight: 600; color: #475569;">$</span>
                  <input type="number" step="0.01" min="0" class="input-ctrl" v-model.number="contractForm.value" style="border-top-left-radius:0; border-bottom-left-radius:0;" placeholder="0.00" />
                </div>
              </div>

              <!-- Contract Type -->
              <div class="form-group">
                <label class="input-lbl">Contract Type</label>
                <select class="select-ctrl" v-model="contractForm.contract_type_id">
                  <option :value="null">Select contract type</option>
                  <option v-for="t in contractTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
                </select>
              </div>

              <!-- Start Date & End Date -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                <div class="form-group">
                  <label class="input-lbl"><span class="required">*</span> Start Date</label>
                  <input type="date" class="input-ctrl" v-model="contractForm.start_date" required />
                </div>
                <div class="form-group">
                  <label class="input-lbl">End Date</label>
                  <input type="date" class="input-ctrl" v-model="contractForm.end_date" />
                </div>
              </div>

              <!-- Description -->
              <div class="form-group">
                <label class="input-lbl">Description</label>
                <textarea class="textarea-ctrl" rows="6" v-model="contractForm.description" placeholder="Description"></textarea>
              </div>

              <div class="form-footer-save no-print flex justify-end gap-2">
                <button type="button" class="btn-outline" style="padding:7px 16px;font-size:13px;" @click="contractView = 'list'">Cancel</button>
                <button type="submit" class="btn-save-profile" :disabled="contractSaving">
                  {{ contractSaving ? 'Saving...' : 'Save' }}
                </button>
              </div>
            </form>
          </template>
        </div>

        <!-- TAB 13: Projects -->
        <div class="card" v-if="activeTab === 'projects'">
          <template v-if="projectView === 'list'">
            <h2 class="section-title border-b pb-2 mb-4">Projects</h2>

            <!-- Stats Cards Row -->
            <div class="est-stats-row mb-4">
              <div class="est-stat-card est-stat-draft">
                <div class="est-stat-label">Not Started</div>
                <div class="est-stat-val">{{ projectStats.not_started || 0 }}</div>
              </div>
              <div class="est-stat-card est-stat-sent">
                <div class="est-stat-label">In Progress</div>
                <div class="est-stat-val">{{ projectStats.in_progress || 0 }}</div>
              </div>
              <div class="est-stat-card est-stat-expired">
                <div class="est-stat-label">On Hold</div>
                <div class="est-stat-val">{{ projectStats.on_hold || 0 }}</div>
              </div>
              <div class="est-stat-card est-stat-declined">
                <div class="est-stat-label">Cancelled</div>
                <div class="est-stat-val">{{ projectStats.cancelled || 0 }}</div>
              </div>
              <div class="est-stat-card est-stat-accepted">
                <div class="est-stat-label">Finished</div>
                <div class="est-stat-val">{{ projectStats.finished || 0 }}</div>
              </div>
            </div>

            <!-- Toolbar: search + per-page + action buttons -->
            <div class="tab-toolbar no-print flex justify-between items-center mb-4">
              <div class="toolbar-left flex items-center gap-2">
                <select class="select-ctrl select-inline-num" style="width:70px; padding:4px 8px; font-size:12.5px;" v-model.number="projectPageSize">
                  <option :value="10">10</option>
                  <option :value="25">25</option>
                  <option :value="50">50</option>
                </select>
                <button class="btn-outline" style="padding:4px 12px; font-size:12.5px;" @click="exportProjects">Export</button>
                <button class="btn-outline sub-refresh-btn" style="padding:5px 9px;" title="Refresh" @click="loadProjects">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                </button>
              </div>
              <div class="toolbar-right flex items-center gap-2">
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13" class="text-slate-400"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                  </span>
                  <input type="text" class="input-ctrl pl-8 text-xs" style="width:220px;" placeholder="Search..." v-model="projectSearch" />
                </div>
                <button class="btn-new-subscription ml-2" @click="openProjectForm()">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                  New Project
                </button>
              </div>
            </div>

            <!-- Table -->
            <table class="data-table">
              <thead>
                <tr>
                  <th style="width:50px;">#</th>
                  <th>Project Name</th>
                  <th>Tags</th>
                  <th>Start Date</th>
                  <th>Deadline</th>
                  <th>Members</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(proj, idx) in paginatedProjects" :key="proj.id" class="inv-row">
                  <td class="text-slate-500">{{ projectPageStart + idx + 1 }}</td>
                  <td>
                    <div class="inv-num-cell">
                      <a class="link-blue font-semibold cursor-pointer" @click="openProjectForm(proj)">{{ proj.name }}</a>
                      <div class="inv-row-actions">
                        <a class="row-action-link cursor-pointer" @click="openProjectForm(proj)">Edit</a>
                        <span class="row-action-sep">|</span>
                        <a class="row-action-link text-rose-600 cursor-pointer" @click="deleteProject(proj.id)">Delete</a>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="flex flex-wrap gap-1" v-if="proj.tags">
                      <span v-for="tag in proj.tags.split(',')" :key="tag" class="tag-badge text-xs px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                        {{ tag.trim() }}
                      </span>
                    </div>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                  <td>{{ formatDateString(proj.start_date) }}</td>
                  <td>{{ proj.deadline ? formatDateString(proj.deadline) : '—' }}</td>
                  <td>
                    <span v-if="proj.members && proj.members.length">
                      {{ proj.members.map(m => m.name).join(', ') }}
                    </span>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                  <td>
                    <span class="badge" :class="projectStatusClass(proj.status)">{{ proj.status }}</span>
                  </td>
                </tr>
                <tr v-if="customerProjects.length === 0">
                  <td colspan="7" class="empty-cell">No projects found for this customer.</td>
                </tr>
              </tbody>
            </table>

            <!-- Pagination -->
            <div class="table-pagination" v-if="customerProjects.length > 0">
              <span class="pagination-info">
                Showing {{ projectPageStart + 1 }} to {{ Math.min(projectPageStart + projectPageSize, customerProjects.length) }} of {{ customerProjects.length }} entries
              </span>
              <div class="pagination-controls">
                <button class="page-btn" :disabled="projectPage === 1" @click="projectPage--">Previous</button>
                <button
                  v-for="p in projectTotalPages" :key="p"
                  :class="['page-btn', { active: p === projectPage }]"
                  @click="projectPage = p"
                >{{ p }}</button>
                <button class="page-btn" :disabled="projectPage === projectTotalPages" @click="projectPage++">Next</button>
              </div>
            </div>
          </template>

          <template v-else>
            <!-- FORM VIEW -->
            <h2 class="section-title mb-6">{{ editingProjectId ? 'Edit Project' : 'Add New Project' }}</h2>

            <!-- Form Subtabs Navigation -->
            <div class="subtabs-nav mb-6 border-b pb-2 flex gap-4">
              <button 
                type="button" 
                :class="['subtab-btn', { active: projectFormTab === 'project' }]" 
                @click="projectFormTab = 'project'"
              >
                Project
              </button>
              <button 
                type="button" 
                :class="['subtab-btn', { active: projectFormTab === 'settings' }]" 
                @click="projectFormTab = 'settings'"
              >
                Project Settings
              </button>
            </div>

            <form @submit.prevent="saveProject" class="subscription-form-wrap">
              <!-- SUBTAB 1: Project Details -->
              <div v-if="projectFormTab === 'project'">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  
                  <!-- Left column -->
                  <div class="flex flex-col gap-4">
                    <!-- Project Name -->
                    <div class="form-group">
                      <label class="input-lbl">* Project Name</label>
                      <input type="text" class="input-ctrl" v-model="projectForm.name" required />
                    </div>

                    <!-- Customer selection -->
                    <div class="form-group">
                      <label class="input-lbl">* Customer</label>
                      <select class="select-ctrl" v-model="projectForm.client_id" required>
                        <option :value="customer.id">{{ customer.company }}</option>
                      </select>
                    </div>

                    <!-- Calculate progress through tasks checkbox -->
                    <div class="flex items-center gap-2 mt-2">
                      <input type="checkbox" v-model="projectForm.progress_from_tasks" id="progress_from_tasks" />
                      <label for="progress_from_tasks" class="cursor-pointer text-sm text-slate-700">Calculate progress through tasks</label>
                    </div>

                    <!-- Progress slider (visible/editable when progress_from_tasks is false) -->
                    <div class="form-group" v-if="!projectForm.progress_from_tasks">
                      <label class="input-lbl">Progress {{ projectForm.progress }}%</label>
                      <input type="range" min="0" max="100" class="w-full" v-model.number="projectForm.progress" />
                    </div>

                    <!-- Billing Type -->
                    <div class="form-group">
                      <label class="input-lbl">* Billing Type</label>
                      <select class="select-ctrl" v-model="projectForm.billing_type" required>
                        <option value="Fixed Rate">Fixed Rate</option>
                        <option value="Project Hours">Project Hours</option>
                        <option value="Task Hours">Task Hours</option>
                      </select>
                    </div>

                    <!-- Status select -->
                    <div class="form-group">
                      <label class="input-lbl">Status</label>
                      <select class="select-ctrl" v-model="projectForm.status">
                        <option value="Not Started">Not Started</option>
                        <option value="In Progress">In Progress</option>
                        <option value="On Hold">On Hold</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="Finished">Finished</option>
                      </select>
                    </div>

                    <!-- Budget / Estimated Hours -->
                    <div class="form-group">
                      <label class="input-lbl">Estimated Hours</label>
                      <input type="number" step="0.1" class="input-ctrl" v-model.number="projectForm.estimated_hours" placeholder="Estimated Hours" />
                    </div>
                  </div>

                  <!-- Right column -->
                  <div class="flex flex-col gap-4">
                    <!-- Start Date -->
                    <div class="form-group">
                      <label class="input-lbl">* Start Date</label>
                      <input type="date" class="input-ctrl" v-model="projectForm.start_date" required />
                    </div>

                    <!-- Deadline -->
                    <div class="form-group">
                      <label class="input-lbl">Deadline</label>
                      <input type="date" class="input-ctrl" v-model="projectForm.deadline" />
                    </div>

                    <!-- Tags -->
                    <div class="form-group">
                      <label class="input-lbl">Tags</label>
                      <input type="text" class="input-ctrl" v-model="projectForm.tags" placeholder="Tag" />
                    </div>

                    <!-- Members -->
                    <div class="form-group">
                      <label class="input-lbl">Members</label>
                      <div class="members-checkbox-grid border border-slate-200 rounded p-3 bg-slate-50 max-h-[160px] overflow-y-auto flex flex-col gap-2">
                        <label v-for="staff in staffList" :key="staff.id" class="flex items-center gap-2 cursor-pointer text-xs text-slate-700">
                          <input type="checkbox" :value="staff.id" v-model="projectForm.member_ids" />
                          <span>{{ staff.name }}</span>
                        </label>
                        <div v-if="!staffList.length" class="text-slate-400 text-xs">No staff members found.</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Description (Full width) -->
                <div class="form-group mt-6">
                  <label class="input-lbl">Description</label>
                  <textarea rows="4" class="textarea-ctrl" v-model="projectForm.description"></textarea>
                </div>

                <!-- Send project created email checkbox -->
                <div class="flex items-center gap-2 mt-4">
                  <input type="checkbox" v-model="projectForm.send_created_email" id="send_created_email" />
                  <label for="send_created_email" class="cursor-pointer text-sm text-slate-700">Send project created email</label>
                </div>
              </div>

              <!-- SUBTAB 2: Project Settings -->
              <div v-else-if="projectFormTab === 'settings'" class="flex flex-col gap-6">
                <!-- Notifications -->
                <div class="form-group max-w-md">
                  <label class="input-lbl">* Send contacts notifications</label>
                  <select class="select-ctrl" v-model="projectForm.settings.send_contacts_notifications">
                    <option value="all">To all contacts with notifications for projects enabled</option>
                    <option value="none">Do not send notifications</option>
                  </select>
                </div>

                <!-- Visible Tabs Checkboxes -->
                <div class="border-t pt-4">
                  <h3 class="text-sm font-semibold text-slate-700 mb-3">Visible Tabs</h3>
                  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                    <label v-for="tab in ['Overview', 'Tasks', 'Timesheets', 'Milestones', 'Files', 'Discussions', 'Gantt', 'Tickets', 'Contracts']" :key="tab" class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" :value="tab.toLowerCase()" v-model="projectForm.settings.visible_tabs" />
                      <span>{{ tab }}</span>
                    </label>
                  </div>
                </div>

                <!-- Sales Checkboxes -->
                <div class="border-t pt-4">
                  <h3 class="text-sm font-semibold text-slate-700 mb-3">Sales</h3>
                  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    <label v-for="sales in ['Proposals', 'Estimates', 'Invoices', 'Subscriptions', 'Expenses', 'Credit Notes', 'Notes', 'Activity']" :key="sales" class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" :value="sales.toLowerCase().replace(' ', '_')" v-model="projectForm.settings.sales_tabs" />
                      <span>{{ sales }}</span>
                    </label>
                  </div>
                </div>

                <!-- Other Settings Options -->
                <div class="border-t pt-4">
                  <h3 class="text-sm font-semibold text-slate-700 mb-3">General Settings</h3>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" v-model="projectForm.settings.allow_customer_view_tasks" />
                      <span>Allow customer to view tasks</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" v-model="projectForm.settings.allow_customer_create_tasks" />
                      <span>Allow customer to create tasks</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" v-model="projectForm.settings.allow_customer_edit_tasks" />
                      <span>Allow customer to edit tasks (only tasks created from contact)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" v-model="projectForm.settings.allow_customer_comment_tasks" />
                      <span>Allow customer to comment on project tasks</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" v-model="projectForm.settings.allow_customer_view_task_comments" />
                      <span>Allow customer to view task comments</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" v-model="projectForm.settings.allow_customer_view_task_attachments" />
                      <span>Allow customer to view task attachments</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" v-model="projectForm.settings.allow_customer_view_task_checklist_items" />
                      <span>Allow customer to view task checklist items</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" v-model="projectForm.settings.allow_customer_upload_attachments_on_tasks" />
                      <span>Allow customer to upload attachments on tasks</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" v-model="projectForm.settings.allow_customer_view_task_total_logged_time" />
                      <span>Allow customer to view task total logged time</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" v-model="projectForm.settings.allow_customer_view_finance_overview" />
                      <span>Allow customer to view finance overview</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" v-model="projectForm.settings.allow_customer_upload_files" />
                      <span>Allow customer to upload files</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" v-model="projectForm.settings.allow_customer_open_discussions" />
                      <span>Allow customer to open discussions</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" v-model="projectForm.settings.allow_customer_view_milestones" />
                      <span>Allow customer to view milestones</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" v-model="projectForm.settings.allow_customer_view_gantt" />
                      <span>Allow customer to view Gantt</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" v-model="projectForm.settings.allow_customer_view_timesheets" />
                      <span>Allow customer to view timesheets</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" v-model="projectForm.settings.allow_customer_view_activity_log" />
                      <span>Allow customer to view activity log</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" v-model="projectForm.settings.allow_customer_view_team_members" />
                      <span>Allow customer to view team members</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                      <input type="checkbox" v-model="projectForm.settings.hide_tasks_on_main_tasks_table" />
                      <span>Hide project tasks on main tasks table (admin area)</span>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Action Bar / Buttons -->
              <div class="flex items-center gap-2 mt-8 pt-4 border-t no-print">
                <button type="submit" class="btn-new-subscription flex items-center gap-1.5" :disabled="projectSaving">
                  <svg v-if="projectSaving" class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                  Save Project
                </button>
                <button type="button" class="btn-outline" @click="projectView = 'list'" :disabled="projectSaving">Cancel</button>
              </div>
            </form>
          </template>
        </div>

        <!-- TAB 14: Tasks -->
        <div class="card" v-if="activeTab === 'tasks'">
          <template v-if="taskView === 'list'">
            <!-- Section Title -->
            <div class="section-title-bar border-b pb-3 mb-0">
              <h2 class="section-title mb-0">Tasks</h2>
            </div>

            <!-- Related To Filter Bar -->
            <div class="tasks-related-bar no-print">
              <span class="related-label">Related To:</span>
              <div class="related-checkboxes">
                <label
                  v-for="rt in taskRelatedTypes"
                  :key="rt.key"
                  class="related-checkbox-item"
                >
                  <input
                    type="checkbox"
                    :checked="taskRelatedFilter === rt.key"
                    @change="taskRelatedFilter = (taskRelatedFilter === rt.key ? '' : rt.key); taskPage = 1"
                    class="related-cb"
                  />
                  <span>{{ rt.label }}</span>
                </label>
              </div>
            </div>

            <!-- Toolbar -->
            <div class="tab-toolbar no-print">
              <div class="toolbar-left">
                <select class="per-page-select" v-model="taskPageSize" @change="taskPage = 1">
                  <option :value="10">10</option>
                  <option :value="25">25</option>
                  <option :value="50">50</option>
                  <option :value="100">100</option>
                </select>
                <button class="btn-icon-toolbar" @click="exportTasks" title="Export CSV">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </button>
                <button class="btn-icon-toolbar" @click="loadTasks" title="Refresh">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                </button>
              </div>
              <div class="toolbar-right">
                <div class="search-wrap">
                  <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                  <input type="text" class="search-input" placeholder="Search..." v-model="taskSearch" @input="taskPage = 1" />
                </div>
                <button class="btn-new-subscription" @click="openTaskForm()">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                  Add New Task
                </button>
              </div>
            </div>

            <!-- Tasks Table -->
            <table class="data-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Status</th>
                  <th>Start Date</th>
                  <th>Due Date</th>
                  <th>Assigned to</th>
                  <th>Tags</th>
                  <th>Priority</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="taskLoading">
                  <td colspan="8" class="empty-cell">
                    <svg class="animate-spin h-4 w-4 text-slate-400 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                  </td>
                </tr>
                <template v-else-if="paginatedTasks.length > 0">
                  <tr v-for="task in paginatedTasks" :key="task.id" class="data-row">
                    <td class="font-semibold text-slate-500">#{{ task.id }}</td>
                    <td>
                      <div class="row-with-actions">
                        <span class="font-medium text-slate-800">{{ task.name }}</span>
                        <div class="row-actions">
                          <a class="row-action-link" @click.prevent="openTaskForm(task)">Edit</a>
                          <span class="row-sep">|</span>
                          <a class="row-action-link text-red-500" @click.prevent="deleteTask(task.id)">Delete</a>
                        </div>
                      </div>
                    </td>
                    <td><span class="badge" :class="taskStatusClass(task.status)">{{ task.status || '—' }}</span></td>
                    <td>{{ formatDateString(task.start_date) }}</td>
                    <td>{{ formatDateString(task.due_date) }}</td>
                    <td>{{ task.assignee?.name || '—' }}</td>
                    <td>
                      <span v-if="task.tags" class="tag-pill">{{ task.tags }}</span>
                      <span v-else>—</span>
                    </td>
                    <td><span class="badge" :class="taskPriorityClass(task.priority)">{{ task.priority || '—' }}</span></td>
                  </tr>
                </template>
                <tr v-else>
                  <td colspan="8" class="empty-cell">No entries found</td>
                </tr>
              </tbody>
            </table>

            <!-- Pagination -->
            <div class="table-pagination" v-if="filteredTasks.length > 0">
              <span class="pagination-info">
                Showing {{ taskPageStart + 1 }} to {{ Math.min(taskPageStart + taskPageSize, filteredTasks.length) }} of {{ filteredTasks.length }} entries
              </span>
              <div class="pagination-btns">
                <button class="pg-btn" :disabled="taskPage === 1" @click="taskPage--">Previous</button>
                <button
                  v-for="p in taskTotalPages" :key="p"
                  :class="['pg-btn', { active: taskPage === p }]"
                  @click="taskPage = p"
                >{{ p }}</button>
                <button class="pg-btn" :disabled="taskPage === taskTotalPages" @click="taskPage++">Next</button>
              </div>
            </div>
          </template>

          <!-- Add / Edit Task Form -->
          <template v-if="taskView === 'form'">
            <div class="form-section-header no-print">
              <h3 class="section-title-sm">{{ editingTaskId ? 'Edit Task' : 'Add New Task' }}</h3>
            </div>
            <form @submit.prevent="saveTask" class="task-form-wrap">
              <!-- Public / Billable / Attach Files toggles -->
              <div class="task-toggles-row">
                <label class="toggle-pill" :class="{ active: taskForm.is_public }">
                  <input type="checkbox" v-model="taskForm.is_public" class="sr-only" />
                  Public
                </label>
                <label class="toggle-pill" :class="{ active: taskForm.billable }">
                  <input type="checkbox" v-model="taskForm.billable" class="sr-only" />
                  Billable
                </label>
                <button type="button" class="toggle-pill">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                  Attach Files
                </button>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 mt-4">
                <!-- Subject -->
                <div class="form-group md:col-span-2">
                  <label class="input-lbl"><span class="required">*</span> Subject</label>
                  <input type="text" class="input-ctrl" v-model="taskForm.name" required placeholder="Task subject" />
                </div>

                <!-- Hourly Rate -->
                <div class="form-group" v-if="taskForm.billable">
                  <label class="input-lbl">Hourly Rate</label>
                  <input type="number" class="input-ctrl" v-model.number="taskForm.hourly_rate" min="0" step="0.01" placeholder="0" />
                </div>

                <!-- Start Date -->
                <div class="form-group">
                  <label class="input-lbl"><span class="required">*</span> Start Date</label>
                  <input type="date" class="input-ctrl" v-model="taskForm.start_date" required />
                </div>

                <!-- Due Date -->
                <div class="form-group">
                  <label class="input-lbl">Due Date</label>
                  <input type="date" class="input-ctrl" v-model="taskForm.due_date" />
                </div>

                <!-- Priority -->
                <div class="form-group">
                  <label class="input-lbl">Priority</label>
                  <select class="input-ctrl" v-model="taskForm.priority">
                    <option>Low</option>
                    <option>Medium</option>
                    <option>High</option>
                    <option>Urgent</option>
                  </select>
                </div>

                <!-- Repeat Every -->
                <div class="form-group">
                  <label class="input-lbl">Repeat every</label>
                  <select class="input-ctrl" v-model="taskForm.repeat_every">
                    <option value="">— None —</option>
                    <option value="week">1 week</option>
                    <option value="2weeks">2 weeks</option>
                    <option value="month">1 month</option>
                    <option value="2months">2 months</option>
                    <option value="3months">3 months</option>
                    <option value="6months">6 months</option>
                    <option value="year">1 year</option>
                  </select>
                </div>

                <!-- Related To -->
                <div class="form-group">
                  <label class="input-lbl">Related To</label>
                  <select class="input-ctrl" v-model="taskForm.related_to_type">
                    <option value="Customer">Customer</option>
                    <option value="Project">Project</option>
                    <option value="Invoice">Invoice</option>
                    <option value="Estimate">Estimate</option>
                    <option value="Contract">Contract</option>
                    <option value="Ticket">Ticket</option>
                    <option value="Expense">Expense</option>
                    <option value="Proposal">Proposal</option>
                  </select>
                </div>

                <!-- Customer (auto-filled, disabled) -->
                <div class="form-group">
                  <label class="input-lbl">Customer</label>
                  <input type="text" class="input-ctrl bg-slate-50" :value="customer?.company" readonly />
                </div>

                <!-- Assignees -->
                <div class="form-group">
                  <label class="input-lbl">Assignees</label>
                  <div class="multi-select-wrap">
                    <select
                      multiple
                      class="input-ctrl multi-select"
                      :value="taskForm.assignees"
                      @change="taskForm.assignees = Array.from($event.target.selectedOptions).map(o => parseInt(o.value))"
                    >
                      <option
                        v-for="staff in staffList"
                        :key="staff.id"
                        :value="staff.id"
                      >{{ staff.name }}</option>
                    </select>
                    <div class="multi-select-hint" v-if="staffList.length === 0">
                      <span class="text-xs text-slate-400">No staff available</span>
                    </div>
                    <div class="multi-select-selected" v-if="taskForm.assignees.length > 0">
                      <span v-for="id in taskForm.assignees" :key="id" class="selected-tag">
                        {{ staffList.find(s => s.id === id)?.name || id }}
                        <button type="button" class="remove-tag" @click="toggleTaskAssignee(id)">×</button>
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Followers -->
                <div class="form-group">
                  <label class="input-lbl">Followers</label>
                  <div class="multi-select-wrap">
                    <select
                      multiple
                      class="input-ctrl multi-select"
                      :value="taskForm.followers"
                      @change="taskForm.followers = Array.from($event.target.selectedOptions).map(o => parseInt(o.value))"
                    >
                      <option
                        v-for="staff in staffList"
                        :key="staff.id"
                        :value="staff.id"
                      >{{ staff.name }}</option>
                    </select>
                    <div class="multi-select-hint" v-if="staffList.length === 0">
                      <span class="text-xs text-slate-400">No staff available</span>
                    </div>
                    <div class="multi-select-selected" v-if="taskForm.followers.length > 0">
                      <span v-for="id in taskForm.followers" :key="id" class="selected-tag">
                        {{ staffList.find(s => s.id === id)?.name || id }}
                        <button type="button" class="remove-tag" @click="toggleTaskFollower(id)">×</button>
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Tags -->
                <div class="form-group">
                  <label class="input-lbl">Tags</label>
                  <input type="text" class="input-ctrl" v-model="taskForm.tags" placeholder="Tag" />
                </div>

                <!-- Task Description -->
                <div class="form-group md:col-span-2">
                  <label class="input-lbl">Task Description</label>
                  <div class="rich-editor-wrap">
                    <div class="rich-toolbar">
                      <span class="rich-tool">B</span>
                      <span class="rich-tool"><i>I</i></span>
                      <span class="rich-tool"><u>U</u></span>
                      <span class="rich-sep"/>
                      <span class="rich-tool">≡</span>
                      <span class="rich-tool">🔗</span>
                    </div>
                    <textarea class="input-ctrl min-h-24" v-model="taskForm.description" placeholder="Add Description"></textarea>
                  </div>
                </div>
              </div>

              <!-- Form Actions -->
              <div class="flex items-center gap-2 mt-6 pt-4 border-t no-print">
                <button type="submit" class="btn-new-subscription flex items-center gap-1.5" :disabled="taskSaving">
                  <svg v-if="taskSaving" class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                  {{ editingTaskId ? 'Save Changes' : 'Add Task' }}
                </button>
                <button type="button" class="btn-outline" @click="taskView = 'list'" :disabled="taskSaving">Cancel</button>
              </div>
            </form>
          </template>
        </div>


        <!-- TAB 15: Tickets -->
        <div class="card" v-if="activeTab === 'tickets'">
          <template v-if="ticketView === 'list'">
            <!-- Section Title -->
            <div class="section-title-bar border-b pb-3 mb-0">
              <h2 class="section-title mb-0">Tickets</h2>
            </div>

            <!-- Toolbar -->
            <div class="tab-toolbar no-print">
              <div class="toolbar-left">
                <select class="per-page-select" v-model="ticketPageSize" @change="ticketPage = 1">
                  <option :value="10">10</option>
                  <option :value="25">25</option>
                  <option :value="50">50</option>
                  <option :value="100">100</option>
                </select>
                <button class="btn-icon-toolbar" @click="exportTickets" title="Export CSV">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </button>
                <button class="btn-icon-toolbar" @click="loadTickets" title="Refresh">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                </button>
              </div>
              <div class="toolbar-right">
                <div class="search-wrap">
                  <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                  <input type="text" class="search-input" placeholder="Search..." v-model="ticketSearch" @input="ticketPage = 1" />
                </div>
                <button class="btn-new-subscription" @click="openTicketForm()">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                  New Ticket
                </button>
              </div>
            </div>

            <!-- Tickets Table -->
            <table class="data-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Subject</th>
                  <th>Tags</th>
                  <th>Department</th>
                  <th>Service</th>
                  <th>Contact</th>
                  <th>Status</th>
                  <th>Priority</th>
                  <th>Last Reply</th>
                  <th>Created</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="ticketLoading">
                  <td colspan="10" class="empty-cell">
                    <svg class="animate-spin h-4 w-4 text-slate-400 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                  </td>
                </tr>
                <template v-else-if="paginatedTickets.length > 0">
                  <tr v-for="t in paginatedTickets" :key="t.id" class="data-row">
                    <td class="font-semibold text-slate-500">#{{ t.id }}</td>
                    <td>
                      <div class="row-with-actions">
                        <span class="font-medium text-slate-800">{{ t.subject }}</span>
                        <div class="row-actions">
                          <a class="row-action-link" @click.prevent="openTicketForm(t)">Edit</a>
                          <span class="row-sep">|</span>
                          <a class="row-action-link text-red-500" @click.prevent="deleteTicket(t.id)">Delete</a>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span v-if="t.tags" class="tag-pill">{{ t.tags }}</span>
                      <span v-else>—</span>
                    </td>
                    <td>{{ ticketDepartmentName(t.department_id) }}</td>
                    <td>{{ ticketServiceName(t.service_id) }}</td>
                    <td>{{ t.contact ? `${t.contact.firstname} ${t.contact.lastname || ''}`.trim() : '—' }}</td>
                    <td><span class="badge" :class="ticketStatusClass(t.status)">{{ t.status || '—' }}</span></td>
                    <td><span class="badge" :class="ticketPriorityClass(t.priority)">{{ t.priority || '—' }}</span></td>
                    <td>{{ t.last_reply_at ? formatDateString(t.last_reply_at) : '—' }}</td>
                    <td>{{ formatDateString(t.created_at) }}</td>
                  </tr>
                </template>
                <tr v-else>
                  <td colspan="10" class="empty-cell">No entries found</td>
                </tr>
              </tbody>
            </table>

            <!-- Pagination -->
            <div class="table-pagination" v-if="filteredCustomerTickets.length > 0">
              <span class="pagination-info">
                Showing {{ ticketPageStart + 1 }} to {{ Math.min(ticketPageStart + ticketPageSize, filteredCustomerTickets.length) }} of {{ filteredCustomerTickets.length }} entries
              </span>
              <div class="pagination-btns">
                <button class="pg-btn" :disabled="ticketPage === 1" @click="ticketPage--">Previous</button>
                <button
                  v-for="p in ticketTotalPages" :key="p"
                  :class="['pg-btn', { active: ticketPage === p }]"
                  @click="ticketPage = p"
                >{{ p }}</button>
                <button class="pg-btn" :disabled="ticketPage === ticketTotalPages" @click="ticketPage++">Next</button>
              </div>
            </div>
          </template>

          <!-- New / Edit Ticket Form -->
          <template v-if="ticketView === 'form'">
            <div class="section-title-bar border-b pb-3 mb-4">
              <h2 class="section-title mb-0">Ticket Information</h2>
            </div>

            <form @submit.prevent="saveTicket" class="ticket-form-wrap">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-5">

                <!-- LEFT COLUMN -->
                <div class="flex flex-col gap-5">
                  <!-- Subject -->
                  <div class="form-group">
                    <label class="input-lbl"><span class="required">*</span> Subject</label>
                    <input type="text" class="input-ctrl" v-model="ticketForm.subject" required placeholder="Ticket subject" />
                  </div>

                  <!-- Contact -->
                  <div class="form-group">
                    <label class="input-lbl">Contact</label>
                    <select class="input-ctrl" v-model="ticketForm.contact_id" @change="onTicketContactChange">
                      <option :value="null">— Select Contact —</option>
                      <option v-for="c in customerContacts" :key="c.id" :value="c.id">
                        {{ c.firstname }} {{ c.lastname }}
                      </option>
                    </select>
                  </div>

                  <!-- Name + Email (auto-filled) -->
                  <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                      <label class="input-lbl">Name</label>
                      <input type="text" class="input-ctrl bg-slate-50" v-model="ticketForm.contact_name" readonly placeholder="Auto-filled" />
                    </div>
                    <div class="form-group">
                      <label class="input-lbl">Email address</label>
                      <input type="email" class="input-ctrl bg-slate-50" v-model="ticketForm.contact_email" readonly placeholder="Auto-filled" />
                    </div>
                  </div>

                  <!-- Department -->
                  <div class="form-group">
                    <label class="input-lbl">Department</label>
                    <select class="input-ctrl" v-model="ticketForm.department_id">
                      <option :value="null">Nothing selected</option>
                      <option v-for="d in ticketDepartments" :key="d.id" :value="d.id">{{ d.name }}</option>
                    </select>
                  </div>

                  <!-- CC -->
                  <div class="form-group">
                    <label class="input-lbl">CC</label>
                    <input type="text" class="input-ctrl" v-model="ticketForm.cc" placeholder="email@example.com, ..." />
                  </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="flex flex-col gap-5">
                  <!-- Tags -->
                  <div class="form-group">
                    <label class="input-lbl">Tags</label>
                    <input type="text" class="input-ctrl" v-model="ticketForm.tags" placeholder="Tag" />
                  </div>

                  <!-- Assign Ticket -->
                  <div class="form-group">
                    <label class="input-lbl">Assign ticket <span class="text-slate-400 text-xs font-normal">(default is current user)</span></label>
                    <select class="input-ctrl" v-model="ticketForm.assigned_to">
                      <option :value="null">— Unassigned —</option>
                      <option v-for="s in staffList" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                  </div>

                  <!-- Priority + Service row -->
                  <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                      <label class="input-lbl">Priority</label>
                      <select class="input-ctrl" v-model="ticketForm.priority">
                        <option>Low</option>
                        <option>Medium</option>
                        <option>High</option>
                        <option>Urgent</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="input-lbl">Service</label>
                      <select class="input-ctrl" v-model="ticketForm.service_id">
                        <option :value="null">Nothing selected</option>
                        <option v-for="s in ticketServices" :key="s.id" :value="s.id">{{ s.name }}</option>
                      </select>
                    </div>
                  </div>

                  <!-- Status (edit only) -->
                  <div class="form-group" v-if="editingTicketId">
                    <label class="input-lbl">Status</label>
                    <select class="input-ctrl" v-model="ticketForm.status">
                      <option>Open</option>
                      <option>In Progress</option>
                      <option>Answered</option>
                      <option>On Hold</option>
                      <option>Closed</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Ticket Body -->
              <div class="form-group mt-6">
                <label class="input-lbl font-semibold text-slate-700 text-sm">Ticket Body</label>
                <div class="ticket-body-toolbar">
                  <select class="ticket-body-select" v-model="ticketForm.predefined_reply" @change="handlePredefinedReplyChange">
                    <option value="">Insert predefined reply</option>
                    <option v-for="r in predefinedReplies" :key="r.id" :value="r.id">{{ r.title }}</option>
                  </select>
                  <select class="ticket-body-select" v-model="ticketForm.kb_link" @change="handleKbLinkChange">
                    <option value="">Insert knowledge base link</option>
                    <option v-for="a in kbArticles" :key="a.id" :value="a.id">{{ a.title }}</option>
                  </select>
                </div>
                <div class="rich-editor-wrap mt-2">
                  <div class="rich-toolbar">
                    <span class="rich-tool font-bold">B</span>
                    <span class="rich-tool"><i>I</i></span>
                    <span class="rich-tool"><u>U</u></span>
                    <span class="rich-sep"/>
                    <span class="rich-tool">≡</span>
                    <span class="rich-tool">🔗</span>
                    <span class="rich-tool">🖼</span>
                  </div>
                  <textarea
                    class="input-ctrl"
                    style="min-height: 140px; border-radius: 0 0 6px 6px; border-top: none;"
                    v-model="ticketForm.message"
                    placeholder="Write your ticket message here..."
                    required
                  ></textarea>
                </div>
              </div>

              <!-- Attachments -->
              <div class="form-group mt-4">
                <label class="input-lbl">Attachments</label>
                <div class="ticket-attachments-row">
                  <input type="file" class="text-xs text-slate-600" multiple />
                  <button type="button" class="ticket-attach-add">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                  </button>
                </div>
              </div>

              <!-- Form Actions -->
              <div class="flex items-center gap-2 mt-6 pt-4 border-t no-print">
                <button type="submit" class="btn-new-subscription flex items-center gap-1.5" :disabled="ticketSaving">
                  <svg v-if="ticketSaving" class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                  {{ editingTicketId ? 'Save Changes' : 'Submit Ticket' }}
                </button>
                <button type="button" class="btn-outline" @click="ticketView = 'list'" :disabled="ticketSaving">Cancel</button>
              </div>
            </form>
          </template>
        </div>


        <!-- TAB 16: Files -->
        <div class="card" v-if="activeTab === 'files'">
          <!-- Section Title -->
          <div class="section-title-bar border-b pb-3 mb-0">
            <h2 class="section-title mb-0">Files</h2>
          </div>

          <!-- Alert/Notice Callout -->
          <div class="alert-info-box mt-4 mb-4 flex items-start gap-2.5">
            <svg class="h-4 w-4 text-blue-500 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span class="text-xs text-blue-800 leading-relaxed font-medium">
              Files from projects and tasks linked to the customer are not shown on this table.
            </span>
          </div>

          <!-- Drag and Drop upload zone -->
          <div
            :class="['file-dropzone mb-6', { 'file-dropzone-dragover': dragover }]"
            @dragover.prevent="dragover = true"
            @dragleave.prevent="dragover = false"
            @drop.prevent="handleFileDrop"
          >
            <div class="dropzone-content flex flex-col items-center">
              <svg class="h-10 w-10 text-slate-300 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <p class="text-sm font-semibold text-slate-600 mb-1">Drop files here to upload</p>
              <p class="text-xs text-slate-400 mb-3">or</p>
              <div class="flex items-center gap-2">
                <input type="file" ref="fileInput" class="hidden" @change="handleFileSelect" />
                <button class="btn-outline-sm" @click="$refs.fileInput.click()">Choose File</button>
                <button class="btn-outline-sm flex items-center gap-1" @click="chooseFromGoogleDrive">
                  <span class="text-red-500">▲</span> Choose from Google Drive
                </button>
                <button class="btn-outline-sm flex items-center gap-1" @click="chooseFromDropbox">
                  <span class="text-blue-500">■</span> Choose from Dropbox
                </button>
              </div>
            </div>
          </div>

          <!-- Toolbar -->
          <div class="tab-toolbar no-print mb-4">
            <div class="toolbar-left">
              <select class="per-page-select" v-model="filesPageSize" @change="filesPage = 1">
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
              </select>
              <button class="btn-icon-toolbar" @click="loadClientFiles" title="Refresh">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
              </button>
            </div>
            <div class="toolbar-right">
              <div class="search-wrap">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="search-input" placeholder="Search..." v-model="filesSearch" @input="filesPage = 1" />
              </div>
            </div>
          </div>

          <!-- Files Table -->
          <table class="data-table">
            <thead>
              <tr>
                <th>File</th>
                <th>Show to customers area</th>
                <th>Date Uploaded</th>
                <th>Options</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filesLoading">
                <td colspan="4" class="empty-cell text-center">
                  <svg class="animate-spin h-4 w-4 text-slate-400 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                </td>
              </tr>
              <template v-else-if="paginatedFiles.length > 0">
                <tr v-for="f in paginatedFiles" :key="f.id" class="data-row">
                  <td>
                    <div class="flex items-center gap-2">
                      <span class="text-xl">📄</span>
                      <div class="flex flex-col">
                        <a :href="f.file_path" target="_blank" class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                          {{ f.file_name }}
                        </a>
                        <span class="text-[10px] text-slate-400">{{ formatFileSize(f.file_size) }}</span>
                      </div>
                    </div>
                  </td>
                  <td>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" class="sr-only peer" :checked="f.visible_to_customer" @change="(e) => toggleFileVisibility(f.id, e.target.checked)" />
                      <div class="w-8 h-4 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                  </td>
                  <td class="text-xs text-slate-500">
                    {{ formatDateString(f.created_at) }}
                  </td>
                  <td>
                    <div class="flex items-center gap-2">
                      <a :href="f.file_path" download class="btn-outline-sm-icon" title="Download">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                      </a>
                      <button class="btn-outline-sm-icon text-red-500 hover:bg-red-50 border-red-200" @click="deleteClientFile(f.id)" title="Delete">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </template>
              <tr v-else>
                <td colspan="4" class="empty-cell text-center">No entries found</td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div class="table-pagination" v-if="filteredFiles.length > 0">
            <span class="pagination-info">
              Showing {{ filesPageStart + 1 }} to {{ Math.min(filesPageStart + filesPageSize, filteredFiles.length) }} of {{ filteredFiles.length }} entries
            </span>
            <div class="pagination-btns">
              <button class="pg-btn" :disabled="filesPage === 1" @click="filesPage--">Previous</button>
              <button
                v-for="p in filesTotalPages" :key="p"
                :class="['pg-btn', { active: filesPage === p }]"
                @click="filesPage = p"
              >{{ p }}</button>
              <button class="pg-btn" :disabled="filesPage === filesTotalPages" @click="filesPage++">Next</button>
            </div>
          </div>
        </div>

        <!-- TAB 17: Vault -->
        <div class="card" v-if="activeTab === 'vault'">
          <div class="flex justify-between items-center border-b pb-2 mb-4">
            <h2 class="section-title mb-0">Vault</h2>
            <button class="btn-primary" @click="openVaultModal()">New Vault Entry</button>
          </div>

          <!-- Vault Table / List -->
          <div v-if="vaultLoading" class="text-center py-8">
            <svg class="animate-spin h-6 w-6 text-slate-400 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
          </div>
          <template v-else>
            <div v-if="clientVaults.length === 0" class="text-center py-8 text-slate-400 text-xs">
              Vault entries not found for this customer.
            </div>
            <div v-else class="overflow-x-auto">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Server Address</th>
                    <th>Port</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Short Description</th>
                    <th>Visibility</th>
                    <th>Creator</th>
                    <th>Options</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="entry in clientVaults" :key="entry.id" class="data-row text-xs">
                    <td class="font-medium text-slate-700">{{ entry.server_address }}</td>
                    <td class="text-slate-500">{{ entry.port || '—' }}</td>
                    <td class="text-slate-600 font-mono">{{ entry.username }}</td>
                    <td>
                      <div class="flex items-center gap-1.5 font-mono">
                        <span>{{ decryptedPasswords[entry.id] || '••••••••' }}</span>
                        <button class="btn-outline-sm-icon px-1 py-0.5" @click="toggleDecryptPassword(entry.id)" :title="decryptedPasswords[entry.id] ? 'Hide' : 'Decrypt'">
                          <span v-if="decryptedPasswords[entry.id]">🙈</span>
                          <span v-else>👁️</span>
                        </button>
                        <button v-if="decryptedPasswords[entry.id]" class="btn-outline-sm-icon px-1.5 py-0.5 text-indigo-600 border border-slate-200 rounded hover:bg-slate-50 flex items-center justify-center" @click="copyToClipboard(decryptedPasswords[entry.id])" title="Copy">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        </button>
                      </div>
                    </td>
                    <td class="text-slate-500 max-w-[200px] truncate" :title="entry.short_description">{{ entry.short_description || '—' }}</td>
                    <td>
                      <span v-if="entry.visibility === 'all_staff'" class="px-2 py-0.5 bg-green-50 text-green-700 border border-green-200 rounded text-[10px] font-medium">All Staff</span>
                      <span v-else-if="entry.visibility === 'admins'" class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[10px] font-medium">Admins Only</span>
                      <span v-else class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded text-[10px] font-medium">Creator & Admins</span>
                    </td>
                    <td class="text-slate-500">{{ entry.creator?.name || 'Unknown' }}</td>
                    <td>
                      <div class="flex items-center gap-2">
                        <button class="btn-outline-sm-icon" @click="openVaultModal(entry)" title="Edit">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button class="btn-outline-sm-icon text-red-500 hover:bg-red-50 border-red-200" @click="deleteVaultEntry(entry.id)" title="Delete">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </template>
        </div>

        <!-- TAB 18: Reminders -->
        <div class="card" v-if="activeTab === 'reminders'">
          <h2 class="section-title border-b pb-2 mb-4">Reminders</h2>

          <!-- Toolbar -->
          <div class="tab-toolbar no-print mb-4">
            <div class="toolbar-left">
              <select class="per-page-select" v-model="reminderPageSize" @change="reminderPage = 1">
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
              </select>
            </div>
            <div class="toolbar-right">
              <div class="search-wrap">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="search-input" placeholder="Search..." v-model="reminderSearch" @input="reminderPage = 1" />
              </div>
            </div>
          </div>

          <!-- Reminders Table -->
          <table class="data-table mb-6">
            <thead>
              <tr>
                <th>Description</th>
                <th>Date</th>
                <th>Remind</th>
                <th>Is notified?</th>
                <th>Options</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="reminderLoading">
                <td colspan="5" class="empty-cell text-center">
                  <svg class="animate-spin h-4 w-4 text-slate-400 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                </td>
              </tr>
              <template v-else-if="paginatedReminders.length > 0">
                <tr v-for="r in paginatedReminders" :key="r.id" class="data-row text-xs">
                  <td class="text-slate-700">{{ r.description }}</td>
                  <td class="text-slate-500">{{ r.date }}</td>
                  <td class="text-slate-600">{{ r.remind_to_user?.name || '—' }}</td>
                  <td>
                    <span v-if="r.is_notified" class="px-2 py-0.5 bg-green-50 text-green-700 border border-green-200 rounded text-[10px] font-medium">Yes</span>
                    <span v-else class="px-2 py-0.5 bg-slate-50 text-slate-500 border border-slate-200 rounded text-[10px] font-medium">No</span>
                  </td>
                  <td>
                    <button class="btn-outline-sm-icon text-red-500 hover:bg-red-50 border-red-200" @click="deleteReminder(r.id)" title="Delete">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    </button>
                  </td>
                </tr>
              </template>
              <tr v-else>
                <td colspan="5" class="empty-cell text-center">No entries found</td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div class="table-pagination" v-if="filteredReminders.length > reminderPageSize">
            <span class="pagination-info">Showing {{ reminderPageStart + 1 }} to {{ Math.min(reminderPageStart + reminderPageSize, filteredReminders.length) }} of {{ filteredReminders.length }} entries</span>
            <div class="pagination-btns">
              <button class="pg-btn" :disabled="reminderPage === 1" @click="reminderPage--">Previous</button>
              <button v-for="p in reminderTotalPages" :key="p" :class="['pg-btn', { active: reminderPage === p }]" @click="reminderPage = p">{{ p }}</button>
              <button class="pg-btn" :disabled="reminderPage === reminderTotalPages" @click="reminderPage++">Next</button>
            </div>
          </div>

          <!-- Set Reminder Form -->
          <div class="border-t pt-5 mt-2">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Set Reminder</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div class="form-group">
                <label class="form-label"><span class="text-red-500">*</span> Date to be notified</label>
                <input type="date" class="form-control" v-model="reminderForm.date" />
              </div>
              <div class="form-group">
                <label class="form-label"><span class="text-red-500">*</span> Set reminder to</label>
                <select class="form-control" v-model="reminderForm.remind_to">
                  <option value="">— Select staff member —</option>
                  <option v-for="s in staffList" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>
            </div>
            <div class="form-group mb-4">
              <label class="form-label"><span class="text-red-500">*</span> Description</label>
              <textarea class="form-control" rows="3" v-model="reminderForm.description" placeholder="Enter reminder description..."></textarea>
            </div>
            <div class="flex items-center gap-3 mb-4">
              <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer select-none">
                <input type="checkbox" v-model="reminderForm.send_email" class="w-3.5 h-3.5 rounded border-slate-300 text-indigo-600" />
                Send also an email for this reminder
              </label>
            </div>
            <button class="btn-primary" :disabled="reminderSaving" @click="saveReminder">
              <svg v-if="reminderSaving" class="animate-spin h-3 w-3 mr-1 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              Set reminder
            </button>
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

    <!-- Vault Entry Add/Edit Modal -->
    <a-modal
      v-model:open="vaultModalVisible"
      :title="vaultForm.id ? 'Edit Vault Entry' : 'New Vault Entry'"
      :footer="null"
      width="600"
      :destroyOnClose="true"
    >
      <a-form layout="vertical" @finish="saveVaultEntry" class="space-y-4 pt-3">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="col-span-2">
            <a-form-item label="Server Address" :rules="[{ required: true, message: 'Server Address is required' }]">
              <a-input v-model:value="vaultForm.server_address" placeholder="e.g. 192.168.1.100" />
            </a-form-item>
          </div>
          <div>
            <a-form-item label="Port">
              <a-input-number v-model:value="vaultForm.port" placeholder="e.g. 22" style="width: 100%" />
            </a-form-item>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <a-form-item label="Username" :rules="[{ required: true, message: 'Username is required' }]">
            <a-input v-model:value="vaultForm.username" placeholder="Username" />
          </a-form-item>
          <a-form-item label="Password" :rules="[{ required: true, message: 'Password is required' }]">
            <a-input-password v-model:value="vaultForm.password" placeholder="Password" />
          </a-form-item>
        </div>

        <a-form-item label="Short Description">
          <a-textarea v-model:value="vaultForm.short_description" placeholder="Short description..." :rows="3" />
        </a-form-item>

        <a-form-item label="Visibility Settings">
          <a-radio-group v-model:value="vaultForm.visibility" class="flex flex-col gap-2">
            <a-radio value="all_staff">
              <div class="inline-flex flex-col text-xs leading-normal">
                <span class="font-medium text-slate-700">Visible to all staff member who have access to this customer</span>
              </div>
            </a-radio>
            <a-radio value="admins">
              <div class="inline-flex flex-col text-xs leading-normal">
                <span class="font-medium text-slate-700">Visible only to administrators</span>
              </div>
            </a-radio>
            <a-radio value="me">
              <div class="inline-flex flex-col text-xs leading-normal">
                <span class="font-medium text-slate-700">Visible only to me (administrators are not excluded)</span>
              </div>
            </a-radio>
          </a-radio-group>
        </a-form-item>

        <a-form-item style="margin-bottom: 8px">
          <a-checkbox v-model:checked="vaultForm.share_in_projects">
            Share this vault entry in projects with project members
          </a-checkbox>
        </a-form-item>

        <div class="border-t pt-4 flex justify-end gap-2">
          <a-button @click="vaultModalVisible = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="vaultSaving" class="bg-indigo-600 border-indigo-600">
            Save Entry
          </a-button>
        </div>
      </a-form>
    </a-modal>

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
import { useAuthStore } from '../../store/authStore';

export default defineComponent({
  name: 'CustomerView',
  setup() {
    const route = useRoute();
    const proposalsStore = useProposalsStore();
    const estimatesStore = useEstimatesStore();
    const authStore = useAuthStore();
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
      { key: 'projects',      label: 'Projects',        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>`, badge: String(customerProjects.value.length) },
      { key: 'tasks',         label: 'Tasks',           icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>`, badge: null },
      { key: 'tickets',       label: 'Tickets',         icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>`, badge: String(customerTicketList.value.length) },
      { key: 'files',         label: 'Files',           icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>`, badge: null },
      { key: 'vault',         label: 'Vault',           icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>`, badge: null },
      { key: 'reminders',     label: 'Reminders',       icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>`, badge: null },
      { key: 'map',           label: 'Map',             icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>`, badge: null },
    ]);

    const customerContracts = ref([]);
    const customerProjects = ref([]);
    const projectView = ref('list');        // 'list' | 'form'
    const projectLoading = ref(false);
    const projectSaving = ref(false);
    const editingProjectId = ref(null);
    const projectSearch = ref('');
    const projectPage = ref(1);
    const projectPageSize = ref(25);
    const staffList = ref([]);
    const projectFormTab = ref('project');  // 'project' | 'settings'

    const projectStats = reactive({
      total: 0,
      not_started: 0,
      in_progress: 0,
      on_hold: 0,
      cancelled: 0,
      finished: 0,
    });

    const projectForm = reactive({
      name: '',
      client_id: null,
      progress_from_tasks: false,
      progress: 0,
      billing_type: 'Fixed Rate',
      status: 'In Progress',
      estimated_hours: null,
      budget: null,
      member_ids: [],
      start_date: '',
      deadline: '',
      tags: '',
      description: '',
      send_created_email: false,
      settings: {
        send_contacts_notifications: 'all',
        visible_tabs: ['overview', 'tasks', 'timesheets', 'milestones', 'files', 'discussions', 'gantt', 'tickets', 'contracts'],
        sales_tabs: ['proposals', 'estimates', 'invoices', 'subscriptions', 'expenses', 'credit_notes', 'notes', 'activity'],
        allow_customer_view_tasks: true,
        allow_customer_create_tasks: true,
        allow_customer_edit_tasks: false,
        allow_customer_comment_tasks: true,
        allow_customer_view_task_comments: true,
        allow_customer_view_task_attachments: true,
        allow_customer_view_task_checklist_items: true,
        allow_customer_upload_attachments_on_tasks: true,
        allow_customer_view_task_total_logged_time: true,
        allow_customer_view_finance_overview: true,
        allow_customer_upload_files: true,
        allow_customer_open_discussions: true,
        allow_customer_view_milestones: true,
        allow_customer_view_gantt: true,
        allow_customer_view_timesheets: true,
        allow_customer_view_activity_log: true,
        allow_customer_view_team_members: true,
        hide_tasks_on_main_tasks_table: false,
      }
    });

    // ── Contracts Tab: state, CRUD, helpers ───────────────────────
    const contractView = ref('list');        // 'list' | 'form'
    const contractTypes = ref([]);
    const contractLoading = ref(false);
    const contractSaving = ref(false);
    const editingContractId = ref(null);
    const contractSearch = ref('');
    const contractPage = ref(1);
    const contractPageSize = ref(25);

    const contractForm = reactive({
      client_id: null,
      subject: '',
      contract_type_id: null,
      value: null,
      start_date: '',
      end_date: '',
      description: '',
      trash: false,
      hide_from_customer: false,
    });

    const loadContracts = async () => {
      if (!customer.value) return;
      contractLoading.value = true;
      try {
        const res = await axios.get('/contracts', {
          params: { client_id: customer.value.id, per_page: 200 }
        });
        customerContracts.value = res.data.contracts?.data || [];
      } catch (e) {
        message.error('Failed to load contracts');
      } finally {
        contractLoading.value = false;
      }
    };

    const loadContractTypes = async () => {
      try {
        const res = await axios.get('/contract-types');
        contractTypes.value = res.data || [];
      } catch (e) {
        console.error('Failed to load contract types', e);
      }
    };

    const resetContractForm = () => {
      Object.assign(contractForm, {
        client_id: customer.value?.id || null,
        subject: '',
        contract_type_id: null,
        value: null,
        start_date: new Date().toISOString().split('T')[0],
        end_date: '',
        description: '',
        trash: false,
        hide_from_customer: false,
      });
    };

    const openContractForm = (contract = null) => {
      loadContractTypes();
      if (contract) {
        editingContractId.value = contract.id;
        const sd = formatDateString(contract.start_date);
        const ed = formatDateString(contract.end_date);
        Object.assign(contractForm, {
          client_id: contract.client_id,
          subject: contract.subject || '',
          contract_type_id: contract.contract_type_id || null,
          value: contract.value != null ? parseFloat(contract.value) : null,
          start_date: sd === '—' ? '' : sd,
          end_date: ed === '—' ? '' : ed,
          description: contract.description || '',
          trash: !!contract.trash,
          hide_from_customer: !!contract.hide_from_customer,
        });
      } else {
        editingContractId.value = null;
        resetContractForm();
      }
      contractView.value = 'form';
    };

    const saveContract = async () => {
      if (!contractForm.subject || !contractForm.client_id) {
        message.error('Subject and Customer are required');
        return;
      }
      contractSaving.value = true;
      try {
        const payload = {
          subject: contractForm.subject,
          client_id: contractForm.client_id || customer.value?.id || null,
          contract_type_id: contractForm.contract_type_id || null,
          value: contractForm.value || 0,
          start_date: contractForm.start_date || new Date().toISOString().split('T')[0],
          end_date: contractForm.end_date || null,
          description: contractForm.description || null,
          trash: !!contractForm.trash,
          hide_from_customer: !!contractForm.hide_from_customer,
          status: editingContractId.value ? undefined : 'Not Started',
        };
        if (editingContractId.value) {
          await axios.put(`/contracts/${editingContractId.value}`, payload);
          message.success('Contract updated successfully');
        } else {
          await axios.post('/contracts', payload);
          message.success('Contract created successfully');
        }
        contractView.value = 'list';
        loadContracts();
      } catch (e) {
        const errors = e.response?.data?.errors;
        message.error(errors ? Object.values(errors).flat().join(', ') : 'Failed to save contract');
      } finally {
        contractSaving.value = false;
      }
    };

    const deleteContract = (id) => {
      Modal.confirm({
        title: 'Delete Contract',
        content: 'Are you sure you want to delete this contract?',
        okText: 'Delete',
        okType: 'danger',
        cancelText: 'Cancel',
        onOk: async () => {
          try {
            await axios.delete(`/contracts/${id}`);
            message.success('Contract deleted');
            loadContracts();
          } catch {
            message.error('Failed to delete contract');
          }
        }
      });
    };

    const contractTypeName = (id) => {
      const t = contractTypes.value.find(x => x.id === id);
      return t ? t.name : '—';
    };

    const filteredContracts = computed(() => {
      const q = contractSearch.value.toLowerCase().trim();
      if (!q) return customerContracts.value;
      return customerContracts.value.filter(c =>
        (c.subject || '').toLowerCase().includes(q) ||
        (c.description || '').toLowerCase().includes(q) ||
        contractTypeName(c.contract_type_id).toLowerCase().includes(q)
      );
    });

    const contractTotalPages = computed(() =>
      Math.max(1, Math.ceil(filteredContracts.value.length / contractPageSize.value))
    );
    const contractPageStart = computed(() => (contractPage.value - 1) * contractPageSize.value);
    const paginatedContracts = computed(() =>
      filteredContracts.value.slice(contractPageStart.value, contractPageStart.value + contractPageSize.value)
    );

    const exportContracts = () => {
      if (!customerContracts.value.length) { message.warning('No contracts to export'); return; }
      const headers = ['#', 'Subject', 'Contract Type', 'Contract Value', 'Start Date', 'End Date', 'Signature'];
      const rows = customerContracts.value.map((c, i) => [
        i + 1, c.subject || '', contractTypeName(c.contract_type_id), c.value || 0,
        formatDateString(c.start_date), formatDateString(c.end_date), c.signed ? 'Signed' : 'Not Signed',
      ]);
      const csv = 'data:text/csv;charset=utf-8,' +
        [headers.join(','), ...rows.map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(','))].join('\n');
      const link = document.createElement('a');
      link.setAttribute('href', encodeURI(csv));
      link.setAttribute('download', `contracts_${(customer.value?.company || 'customer').replace(/\s+/g, '_')}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    };

    // ── Projects Tab: state, CRUD, helpers ─────────────────────────
    const loadProjects = async () => {
      if (!customer.value) return;
      projectLoading.value = true;
      try {
        const res = await axios.get('/projects', {
          params: { client_id: customer.value.id, per_page: 200 }
        });
        customerProjects.value = res.data.projects?.data || [];
        if (res.data.stats) {
          Object.assign(projectStats, res.data.stats);
        }
      } catch (e) {
        message.error('Failed to load projects');
      } finally {
        projectLoading.value = false;
      }
    };

    const loadStaffList = async () => {
      try {
        const res = await axios.get('/staff', { params: { per_page: 200 } });
        staffList.value = res.data.staff?.data || [];
      } catch (e) {
        console.error('Failed to load staff list', e);
      }
    };

    const resetProjectForm = () => {
      Object.assign(projectForm, {
        name: '',
        client_id: customer.value?.id || null,
        progress_from_tasks: false,
        progress: 0,
        billing_type: 'Fixed Rate',
        status: 'In Progress',
        estimated_hours: null,
        budget: null,
        member_ids: [],
        start_date: new Date().toISOString().split('T')[0],
        deadline: '',
        tags: '',
        description: '',
        send_created_email: false,
        settings: {
          send_contacts_notifications: 'all',
          visible_tabs: ['overview', 'tasks', 'timesheets', 'milestones', 'files', 'discussions', 'gantt', 'tickets', 'contracts'],
          sales_tabs: ['proposals', 'estimates', 'invoices', 'subscriptions', 'expenses', 'credit_notes', 'notes', 'activity'],
          allow_customer_view_tasks: true,
          allow_customer_create_tasks: true,
          allow_customer_edit_tasks: false,
          allow_customer_comment_tasks: true,
          allow_customer_view_task_comments: true,
          allow_customer_view_task_attachments: true,
          allow_customer_view_task_checklist_items: true,
          allow_customer_upload_attachments_on_tasks: true,
          allow_customer_view_task_total_logged_time: true,
          allow_customer_view_finance_overview: true,
          allow_customer_upload_files: true,
          allow_customer_open_discussions: true,
          allow_customer_view_milestones: true,
          allow_customer_view_gantt: true,
          allow_customer_view_timesheets: true,
          allow_customer_view_activity_log: true,
          allow_customer_view_team_members: true,
          hide_tasks_on_main_tasks_table: false,
        }
      });
      projectFormTab.value = 'project';
    };

    const openProjectForm = (project = null) => {
      if (project) {
        editingProjectId.value = project.id;
        
        let parsedSettings = {};
        if (project.settings) {
          parsedSettings = typeof project.settings === 'string' ? JSON.parse(project.settings) : project.settings;
        }
        
        const mergedSettings = Object.assign({
          send_contacts_notifications: 'all',
          visible_tabs: ['overview', 'tasks', 'timesheets', 'milestones', 'files', 'discussions', 'gantt', 'tickets', 'contracts'],
          sales_tabs: ['proposals', 'estimates', 'invoices', 'subscriptions', 'expenses', 'credit_notes', 'notes', 'activity'],
          allow_customer_view_tasks: true,
          allow_customer_create_tasks: true,
          allow_customer_edit_tasks: false,
          allow_customer_comment_tasks: true,
          allow_customer_view_task_comments: true,
          allow_customer_view_task_attachments: true,
          allow_customer_view_task_checklist_items: true,
          allow_customer_upload_attachments_on_tasks: true,
          allow_customer_view_task_total_logged_time: true,
          allow_customer_view_finance_overview: true,
          allow_customer_upload_files: true,
          allow_customer_open_discussions: true,
          allow_customer_view_milestones: true,
          allow_customer_view_gantt: true,
          allow_customer_view_timesheets: true,
          allow_customer_view_activity_log: true,
          allow_customer_view_team_members: true,
          hide_tasks_on_main_tasks_table: false,
        }, parsedSettings);

        Object.assign(projectForm, {
          name: project.name || '',
          client_id: project.client_id || customer.value?.id,
          progress_from_tasks: !!project.progress_from_tasks,
          progress: project.progress || 0,
          billing_type: project.billing_type || 'Fixed Rate',
          status: project.status || 'In Progress',
          estimated_hours: project.estimated_hours || null,
          budget: project.budget || null,
          member_ids: project.members ? project.members.map(m => m.id) : [],
          start_date: project.start_date ? String(project.start_date).split('T')[0] : '',
          deadline: project.deadline ? String(project.deadline).split('T')[0] : '',
          tags: project.tags || '',
          description: project.description || '',
          send_created_email: !!project.send_created_email,
          settings: mergedSettings,
        });
      } else {
        editingProjectId.value = null;
        resetProjectForm();
      }
      projectView.value = 'form';
    };

    const saveProject = async () => {
      if (!projectForm.name) {
        message.error('Project Name is required');
        return;
      }
      projectSaving.value = true;
      try {
        const payload = {
          name: projectForm.name,
          client_id: projectForm.client_id,
          progress_from_tasks: !!projectForm.progress_from_tasks,
          progress: projectForm.progress || 0,
          billing_type: projectForm.billing_type,
          status: projectForm.status,
          estimated_hours: projectForm.estimated_hours || null,
          budget: projectForm.budget || null,
          member_ids: projectForm.member_ids,
          start_date: projectForm.start_date || new Date().toISOString().split('T')[0],
          deadline: projectForm.deadline || null,
          tags: projectForm.tags || null,
          description: projectForm.description || null,
          send_created_email: !!projectForm.send_created_email,
          settings: projectForm.settings,
        };

        if (editingProjectId.value) {
          await axios.put(`/projects/${editingProjectId.value}`, payload);
          message.success('Project updated successfully');
        } else {
          await axios.post('/projects', payload);
          message.success('Project created successfully');
        }
        projectView.value = 'list';
        loadProjects();
      } catch (e) {
        const errors = e.response?.data?.errors;
        message.error(errors ? Object.values(errors).flat().join(', ') : 'Failed to save project');
      } finally {
        projectSaving.value = false;
      }
    };

    const deleteProject = (id) => {
      Modal.confirm({
        title: 'Delete Project?',
        content: 'Are you sure you want to delete this project? This action cannot be undone.',
        okText: 'Delete',
        okType: 'danger',
        onOk: async () => {
          try {
            await axios.delete(`/projects/${id}`);
            message.success('Project deleted successfully');
            loadProjects();
          } catch (e) {
            message.error('Failed to delete project');
          }
        }
      });
    };

    const exportProjects = () => {
      if (!customerProjects.value.length) {
        message.warning('No projects to export');
        return;
      }
      const headers = ['#', 'Project Name', 'Billing Type', 'Start Date', 'Deadline', 'Status'];
      const rows = customerProjects.value.map((p, i) => [
        i + 1,
        p.name || '',
        p.billing_type || '',
        p.start_date ? String(p.start_date).split('T')[0] : '',
        p.deadline ? String(p.deadline).split('T')[0] : '—',
        p.status || ''
      ]);
      const csvContent = 'data:text/csv;charset=utf-8,' +
        [headers.join(','), ...rows.map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(','))].join('\n');
      const link = document.createElement('a');
      link.setAttribute('href', encodeURI(csvContent));
      link.setAttribute('download', `projects_${(customer.value?.company || 'customer').replace(/\s+/g, '_')}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    };

    const projectStatusClass = (s) => {
      return {
        'Not Started': 'badge-default',
        'In Progress': 'badge-blue',
        'On Hold': 'badge-yellow',
        'Cancelled': 'badge-red',
        'Finished': 'badge-green'
      }[s] || 'badge-default';
    };

    const adminOptions = ref([
      { id: 1, name: 'Merl Wintheiser', selected: true },
      { id: 2, name: 'Tamara Howell', selected: false },
      { id: 3, name: 'Jan Keeling', selected: true }
    ]);

    // ── Tasks Tab: state, CRUD, helpers ──────────────────────────
    const taskView = ref('list');           // 'list' | 'form'
    const customerTasks = ref([]);
    const taskLoading = ref(false);
    const taskSaving = ref(false);
    const editingTaskId = ref(null);
    const taskSearch = ref('');
    const taskPage = ref(1);
    const taskPageSize = ref(25);
    const taskRelatedFilter = ref('');     // '' = All

    const taskRelatedTypes = [
      { key: 'Customer',  label: 'Customer' },
      { key: 'Project',   label: 'Projects' },
      { key: 'Invoice',   label: 'Invoices' },
      { key: 'Estimate',  label: 'Estimates' },
      { key: 'Contract',  label: 'Contracts' },
      { key: 'Ticket',    label: 'Tickets' },
      { key: 'Expense',   label: 'Expenses' },
      { key: 'Proposal',  label: 'Proposals' },
    ];

    const taskForm = reactive({
      name: '',
      description: '',
      priority: 'Medium',
      status: 'Not Started',
      start_date: new Date().toISOString().split('T')[0],
      due_date: '',
      assigned_to: null,
      related_to_type: 'Customer',
      related_to_id: null,
      client_id: null,
      billable: false,
      is_public: false,
      hourly_rate: 0,
      repeat_every: '',
      tags: '',
      assignees: [],
      followers: [],
    });

    const loadTasks = async () => {
      if (!customer.value) return;
      taskLoading.value = true;
      try {
        const res = await axios.get('/tasks', {
          params: { client_id: customer.value.id, per_page: 200 }
        });
        customerTasks.value = res.data.tasks?.data || [];
      } catch (e) {
        message.error('Failed to load tasks');
      } finally {
        taskLoading.value = false;
      }
    };

    const resetTaskForm = () => {
      Object.assign(taskForm, {
        name: '',
        description: '',
        priority: 'Medium',
        status: 'Not Started',
        start_date: new Date().toISOString().split('T')[0],
        due_date: '',
        assigned_to: null,
        related_to_type: 'Customer',
        related_to_id: customer.value?.id || null,
        client_id: customer.value?.id || null,
        billable: false,
        is_public: false,
        hourly_rate: 0,
        repeat_every: '',
        tags: '',
        assignees: [],
        followers: [],
      });
    };

    const openTaskForm = (task = null) => {
      if (task) {
        editingTaskId.value = task.id;
        Object.assign(taskForm, {
          name: task.name || '',
          description: task.description || '',
          priority: task.priority || 'Medium',
          status: task.status || 'Not Started',
          start_date: task.start_date ? String(task.start_date).split('T')[0] : '',
          due_date: task.due_date ? String(task.due_date).split('T')[0] : '',
          assigned_to: task.assigned_to || null,
          related_to_type: task.related_to_type || 'Customer',
          related_to_id: task.related_to_id || customer.value?.id || null,
          client_id: task.client_id || customer.value?.id || null,
          billable: !!task.billable,
          is_public: !!task.is_public,
          hourly_rate: task.hourly_rate || 0,
          repeat_every: task.repeat_every || '',
          tags: task.tags || '',
          assignees: Array.isArray(task.assignees) ? [...task.assignees] : [],
          followers: Array.isArray(task.followers) ? [...task.followers] : [],
        });
      } else {
        editingTaskId.value = null;
        resetTaskForm();
      }
      taskView.value = 'form';
    };

    const toggleTaskAssignee = (staffId) => {
      const idx = taskForm.assignees.indexOf(staffId);
      if (idx === -1) taskForm.assignees.push(staffId);
      else taskForm.assignees.splice(idx, 1);
    };

    const toggleTaskFollower = (staffId) => {
      const idx = taskForm.followers.indexOf(staffId);
      if (idx === -1) taskForm.followers.push(staffId);
      else taskForm.followers.splice(idx, 1);
    };

    const saveTask = async () => {
      if (!taskForm.name) { message.error('Task Subject is required'); return; }
      if (!taskForm.start_date) { message.error('Start Date is required'); return; }
      taskSaving.value = true;
      try {
        const payload = {
          name: taskForm.name,
          description: taskForm.description || null,
          priority: taskForm.priority,
          status: taskForm.status,
          start_date: taskForm.start_date,
          due_date: taskForm.due_date || null,
          assigned_to: taskForm.assigned_to || null,
          related_to_type: taskForm.related_to_type || 'Customer',
          related_to_id: taskForm.client_id,
          client_id: taskForm.client_id || customer.value?.id,
          billable: !!taskForm.billable,
          is_public: !!taskForm.is_public,
          hourly_rate: taskForm.billable ? (taskForm.hourly_rate || 0) : 0,
          repeat_every: taskForm.repeat_every || null,
          tags: taskForm.tags || null,
          assignees: taskForm.assignees,
          followers: taskForm.followers,
        };
        if (editingTaskId.value) {
          await axios.put(`/tasks/${editingTaskId.value}`, payload);
          message.success('Task updated successfully');
        } else {
          await axios.post('/tasks', payload);
          message.success('Task created successfully');
        }
        taskView.value = 'list';
        loadTasks();
      } catch (e) {
        const errors = e.response?.data?.errors;
        message.error(errors ? Object.values(errors).flat().join(', ') : 'Failed to save task');
      } finally {
        taskSaving.value = false;
      }
    };

    const deleteTask = (id) => {
      Modal.confirm({
        title: 'Delete Task',
        content: 'Are you sure you want to delete this task?',
        okText: 'Delete',
        okType: 'danger',
        cancelText: 'Cancel',
        onOk: async () => {
          try {
            await axios.delete(`/tasks/${id}`);
            message.success('Task deleted');
            loadTasks();
          } catch {
            message.error('Failed to delete task');
          }
        }
      });
    };

    const filteredTasks = computed(() => {
      let list = customerTasks.value;
      // Related-to type filter
      if (taskRelatedFilter.value) {
        list = list.filter(t => t.related_to_type === taskRelatedFilter.value);
      }
      // Text search
      const q = taskSearch.value.toLowerCase().trim();
      if (!q) return list;
      return list.filter(t =>
        (t.name || '').toLowerCase().includes(q) ||
        (t.description || '').toLowerCase().includes(q) ||
        (t.status || '').toLowerCase().includes(q) ||
        (t.priority || '').toLowerCase().includes(q) ||
        (t.tags || '').toLowerCase().includes(q) ||
        (t.assignee?.name || '').toLowerCase().includes(q)
      );
    });

    const taskTotalPages = computed(() =>
      Math.max(1, Math.ceil(filteredTasks.value.length / taskPageSize.value))
    );
    const taskPageStart = computed(() => (taskPage.value - 1) * taskPageSize.value);
    const paginatedTasks = computed(() =>
      filteredTasks.value.slice(taskPageStart.value, taskPageStart.value + taskPageSize.value)
    );

    const taskStatusClass = (s) => ({
      'Not Started':       'badge-default',
      'In Progress':       'badge-blue',
      'Testing':           'badge-yellow',
      'Awaiting Feedback': 'badge-orange',
      'Complete':          'badge-green',
    }[s] || 'badge-default');

    const taskPriorityClass = (p) => ({
      'Low':    'badge-default',
      'Medium': 'badge-yellow',
      'High':   'badge-red',
      'Urgent': 'badge-red',
    }[p] || 'badge-default');

    const exportTasks = () => {
      if (!customerTasks.value.length) { message.warning('No tasks to export'); return; }
      const headers = ['#', 'Name', 'Status', 'Priority', 'Start Date', 'Due Date', 'Assigned To', 'Tags', 'Billable'];
      const rows = customerTasks.value.map((t, i) => [
        i + 1, t.name || '', t.status || '', t.priority || '',
        formatDateString(t.start_date), formatDateString(t.due_date),
        t.assignee?.name || '', t.tags || '', t.billable ? 'Yes' : 'No',
      ]);
      const csv = 'data:text/csv;charset=utf-8,' +
        [headers.join(','), ...rows.map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`)
          .join(','))].join('\n');
      const link = document.createElement('a');
      link.setAttribute('href', encodeURI(csv));
      link.setAttribute('download', `tasks_${(customer.value?.company || 'customer').replace(/\s+/g, '_')}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    };

    // ── Subscriptions Tab: state, CRUD, helpers ───────────────────
    const subView = ref('list');            // 'list' | 'form'
    const subscriptions = ref([]);
    const subLoading = ref(false);
    const subSaving = ref(false);
    const editingSubId = ref(null);
    const subSearch = ref('');
    const subPage = ref(1);
    const subPageSize = ref(25);

    const billingPlans = [
      { code: 'plan_basic_monthly', name: 'Basic Plan — $29.00 / monthly',    amount: 29,  period: 'monthly' },
      { code: 'plan_pro_monthly',   name: 'Pro Plan — $99.00 / monthly',      amount: 99,  period: 'monthly' },
      { code: 'plan_pro_yearly',    name: 'Pro Plan — $999.00 / yearly',      amount: 999, period: 'yearly'  },
      { code: 'plan_enterprise',    name: 'Enterprise — $499.00 / monthly',   amount: 499, period: 'monthly' },
    ];

    const subForm = reactive({
      client_id: null,
      name: '',
      stripe_plan: '',
      quantity: 1,
      start_date: '',
      description: '',
      include_description: false,
      currency: 'USD',
      tax_1: '',
      tax_2: '',
      terms: '',
      amount: 0,
      billing_period: 'monthly',
    });

    const customerSelectOptions = computed(() =>
      customer.value ? [{ id: customer.value.id, company: customer.value.company }] : []
    );

    const filteredSubscriptions = computed(() => {
      const q = subSearch.value.toLowerCase().trim();
      if (!q) return subscriptions.value;
      return subscriptions.value.filter(s =>
        (s.name || '').toLowerCase().includes(q) ||
        (s.status || '').toLowerCase().includes(q) ||
        (s.project?.name || '').toLowerCase().includes(q)
      );
    });
    const subTotalPages = computed(() =>
      Math.max(1, Math.ceil(filteredSubscriptions.value.length / subPageSize.value))
    );
    const subPageStart = computed(() => (subPage.value - 1) * subPageSize.value);
    const paginatedSubscriptions = computed(() =>
      filteredSubscriptions.value.slice(subPageStart.value, subPageStart.value + subPageSize.value)
    );

    const loadSubscriptions = async () => {
      if (!customer.value) return;
      subLoading.value = true;
      try {
        const res = await axios.get('/subscriptions', {
          params: { client_id: customer.value.id, per_page: 200 }
        });
        subscriptions.value = res.data.subscriptions?.data || [];
      } catch (e) {
        message.error('Failed to load subscriptions');
      } finally {
        subLoading.value = false;
      }
    };

    const resetSubForm = () => {
      Object.assign(subForm, {
        client_id: customer.value?.id || null,
        name: '', stripe_plan: '', quantity: 1, start_date: '',
        description: '', include_description: false, currency: 'USD',
        tax_1: '', tax_2: '', terms: '', amount: 0, billing_period: 'monthly',
      });
    };

    const openSubscriptionForm = (sub = null) => {
      if (sub) {
        editingSubId.value = sub.id;
        const sd = formatDateString(sub.start_date);
        Object.assign(subForm, {
          client_id: sub.client_id,
          name: sub.name || '',
          stripe_plan: sub.stripe_plan || '',
          quantity: sub.quantity || 1,
          start_date: sd === '—' ? '' : sd,
          description: sub.description || '',
          include_description: !!sub.include_description,
          currency: sub.currency || 'USD',
          tax_1: sub.tax_1 || '',
          tax_2: sub.tax_2 || '',
          terms: sub.terms || '',
          amount: sub.amount || 0,
          billing_period: sub.billing_period || 'monthly',
        });
      } else {
        editingSubId.value = null;
        resetSubForm();
      }
      subView.value = 'form';
    };

    const onBillingPlanChange = () => {
      const plan = billingPlans.find(p => p.code === subForm.stripe_plan);
      if (plan) {
        subForm.amount = plan.amount;
        subForm.billing_period = plan.period;
        if (!subForm.name) subForm.name = plan.name.split(' — ')[0];
      }
    };

    const saveSubscription = async () => {
      if (!subForm.name || !subForm.client_id) {
        message.error('Subscription Name and Customer are required');
        return;
      }
      subSaving.value = true;
      try {
        const payload = {
          name: subForm.name,
          client_id: subForm.client_id,
          amount: subForm.amount || 0,
          billing_period: subForm.billing_period || 'monthly',
          status: editingSubId.value ? undefined : 'active',
          start_date: subForm.start_date || new Date().toISOString().split('T')[0],
          stripe_plan: subForm.stripe_plan || null,
          quantity: subForm.quantity || 1,
          currency: subForm.currency || 'USD',
          tax_1: subForm.tax_1 || null,
          tax_2: subForm.tax_2 || null,
          terms: subForm.terms || null,
          description: subForm.description || null,
          include_description: !!subForm.include_description,
        };
        if (editingSubId.value) {
          await axios.put(`/subscriptions/${editingSubId.value}`, payload);
          message.success('Subscription updated successfully');
        } else {
          await axios.post('/subscriptions', payload);
          message.success('Subscription created successfully');
        }
        subView.value = 'list';
        loadSubscriptions();
      } catch (e) {
        const errors = e.response?.data?.errors;
        message.error(errors ? Object.values(errors).flat().join(', ') : 'Failed to save subscription');
      } finally {
        subSaving.value = false;
      }
    };

    const deleteSubscription = (id) => {
      Modal.confirm({
        title: 'Delete Subscription',
        content: 'Are you sure you want to delete this subscription?',
        okText: 'Delete',
        okType: 'danger',
        cancelText: 'Cancel',
        onOk: async () => {
          try {
            await axios.delete(`/subscriptions/${id}`);
            message.success('Subscription deleted');
            loadSubscriptions();
          } catch {
            message.error('Failed to delete subscription');
          }
        }
      });
    };

    const subStatusClass = (s) => ({
      active:    'badge-green',
      inactive:  'badge-gray',
      cancelled: 'badge-red',
    }[s] || 'badge-gray');
    const subStatusLabel = (s) => (s ? s.charAt(0).toUpperCase() + s.slice(1) : '—');

    const nextBillingCycle = (sub) => {
      const base = formatDateString(sub.start_date);
      if (base === '—') return '—';
      const d = new Date(base);
      if (isNaN(d.getTime())) return '—';
      const period = sub.billing_period || 'monthly';
      if (period === 'yearly') d.setFullYear(d.getFullYear() + 1);
      else if (period === 'bi-weekly') d.setDate(d.getDate() + 14);
      else d.setMonth(d.getMonth() + 1);
      return d.toISOString().split('T')[0];
    };

    const exportSubscriptions = () => {
      if (!subscriptions.value.length) { message.warning('No subscriptions to export'); return; }
      const headers = ['#', 'Subscription Name', 'Project', 'Status', 'Next Billing Cycle', 'Date Subscribed', 'Last Sent'];
      const rows = subscriptions.value.map((s, i) => [
        i + 1, s.name || '', s.project?.name || '', s.status || '',
        nextBillingCycle(s), formatDateString(s.created_at), s.last_sent || 'Never',
      ]);
      const csv = 'data:text/csv;charset=utf-8,' +
        [headers.join(','), ...rows.map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(','))].join('\n');
      const link = document.createElement('a');
      link.setAttribute('href', encodeURI(csv));
      link.setAttribute('download', `subscriptions_${(customer.value?.company || 'customer').replace(/\s+/g, '_')}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    };

    // ── Expenses Tab: state, CRUD, helpers ────────────────────────
    const expView = ref('list');            // 'list' | 'form'
    const clientExpenses = ref([]);
    const expLoading = ref(false);
    const expSaving = ref(false);
    const editingExpId = ref(null);
    const expSearch = ref('');
    const expPage = ref(1);
    const expPageSize = ref(25);

    const expenseCategories = ref([
      { id: 1, name: 'Bank Fees' },
      { id: 2, name: 'Advertising' },
      { id: 3, name: 'Office Supplies' },
      { id: 4, name: 'Software Licenses' },
      { id: 5, name: 'Travel & Fuel' },
      { id: 6, name: 'Meals & Entertainment' },
      { id: 7, name: 'Utilities' },
      { id: 8, name: 'Rent' },
      { id: 9, name: 'Hosting Services' },
      { id: 10, name: 'Consulting Fees' },
    ]);

    const expFields = reactive({
      recurring: false,
      name: false,
      reference: false,
      note: false,
      tax: false,
      paymentMode: false,
    });

    const expForm = reactive({
      category_id: null,
      date: '',
      amount: null,
      currency: 'USD',
      tax: '',
      payment_mode: '',
      client_id: null,
      billable: false,
      project: '',
      recurring: '',
      name: '',
      reference: '',
      note: '',
    });

    const expenseCategoryName = (id) => {
      const c = expenseCategories.value.find(x => x.id === id);
      return c ? c.name : '—';
    };

    const filteredExpenses = computed(() => {
      const q = expSearch.value.toLowerCase().trim();
      if (!q) return clientExpenses.value;
      return clientExpenses.value.filter(e =>
        (e.name || '').toLowerCase().includes(q) ||
        (e.reference || '').toLowerCase().includes(q) ||
        (e.payment_mode || '').toLowerCase().includes(q) ||
        expenseCategoryName(e.category_id).toLowerCase().includes(q)
      );
    });
    const expTotalPages = computed(() =>
      Math.max(1, Math.ceil(filteredExpenses.value.length / expPageSize.value))
    );
    const expPageStart = computed(() => (expPage.value - 1) * expPageSize.value);
    const paginatedExpenses = computed(() =>
      filteredExpenses.value.slice(expPageStart.value, expPageStart.value + expPageSize.value)
    );

    const expenseSummary = computed(() => {
      const list = clientExpenses.value;
      const sum = (arr) => arr.reduce((s, e) => s + parseFloat(e.amount || 0), 0);
      return {
        total:       sum(list),
        billable:    sum(list.filter(e => e.billable)),
        nonBillable: sum(list.filter(e => !e.billable)),
        notInvoiced: sum(list.filter(e => e.status !== 'billed')),
        billed:      sum(list.filter(e => e.status === 'billed')),
      };
    });

    const loadExpenses = async () => {
      if (!customer.value) return;
      expLoading.value = true;
      try {
        const res = await axios.get('/expenses', {
          params: { client_id: customer.value.id, per_page: 200 }
        });
        clientExpenses.value = res.data.expenses?.data || [];
      } catch (e) {
        message.error('Failed to load expenses');
      } finally {
        expLoading.value = false;
      }
    };

    const resetExpForm = () => {
      Object.assign(expForm, {
        category_id: null,
        date: new Date().toISOString().split('T')[0],
        amount: null,
        currency: 'USD',
        tax: '',
        payment_mode: '',
        client_id: customer.value?.id || null,
        billable: false,
        project: '',
        recurring: '',
        name: '',
        reference: '',
        note: '',
      });
      Object.assign(expFields, { recurring: false, name: false, reference: false, note: false, tax: false, paymentMode: false });
    };

    const openExpenseForm = (exp = null) => {
      if (exp) {
        editingExpId.value = exp.id;
        const d = formatDateString(exp.date);
        Object.assign(expForm, {
          category_id: exp.category_id || null,
          date: d === '—' ? '' : d,
          amount: exp.amount != null ? parseFloat(exp.amount) : null,
          currency: exp.currency || 'USD',
          tax: '',
          payment_mode: exp.payment_mode || '',
          client_id: exp.client_id,
          billable: !!exp.billable,
          project: '',
          recurring: '',
          name: exp.name || '',
          reference: exp.reference || '',
          note: exp.note || '',
        });
        // reveal sections that hold data
        Object.assign(expFields, {
          recurring: false,
          name: !!exp.name,
          reference: !!exp.reference,
          note: !!exp.note,
          tax: false,
          paymentMode: !!exp.payment_mode,
        });
      } else {
        editingExpId.value = null;
        resetExpForm();
      }
      expView.value = 'form';
    };

    const addExpenseCategory = () => {
      const name = window.prompt('New expense category name:');
      if (name && name.trim()) {
        const id = Math.max(0, ...expenseCategories.value.map(c => c.id)) + 1;
        expenseCategories.value.push({ id, name: name.trim() });
        expForm.category_id = id;
      }
    };

    const saveExpense = async () => {
      if (!expForm.category_id) { message.error('Expense Category is required'); return; }
      if (!expForm.date) { message.error('Expense Date is required'); return; }
      if (expForm.amount == null || expForm.amount === '') { message.error('Amount is required'); return; }
      expSaving.value = true;
      try {
        // API requires a name; fall back to the category label when not provided
        const resolvedName = (expForm.name && expForm.name.trim())
          ? expForm.name.trim()
          : expenseCategoryName(expForm.category_id);
        const payload = {
          name: resolvedName,
          amount: expForm.amount || 0,
          date: expForm.date,
          category_id: expForm.category_id,
          client_id: expForm.client_id || customer.value?.id || null,
          payment_mode: expForm.payment_mode || null,
          reference: expForm.reference || null,
          note: expForm.note || null,
          billable: !!expForm.billable,
        };
        if (editingExpId.value) {
          await axios.put(`/expenses/${editingExpId.value}`, payload);
          message.success('Expense updated successfully');
        } else {
          await axios.post('/expenses', payload);
          message.success('Expense recorded successfully');
        }
        expView.value = 'list';
        loadExpenses();
      } catch (e) {
        const errors = e.response?.data?.errors;
        message.error(errors ? Object.values(errors).flat().join(', ') : 'Failed to save expense');
      } finally {
        expSaving.value = false;
      }
    };

    const deleteExpense = (id) => {
      Modal.confirm({
        title: 'Delete Expense',
        content: 'Are you sure you want to delete this expense?',
        okText: 'Delete',
        okType: 'danger',
        cancelText: 'Cancel',
        onOk: async () => {
          try {
            await axios.delete(`/expenses/${id}`);
            message.success('Expense deleted');
            loadExpenses();
          } catch {
            message.error('Failed to delete expense');
          }
        }
      });
    };

    const exportExpenses = () => {
      if (!clientExpenses.value.length) { message.warning('No expenses to export'); return; }
      const headers = ['#', 'Category', 'Amount', 'Name', 'Date', 'Reference #', 'Payment Mode'];
      const rows = clientExpenses.value.map((e, i) => [
        i + 1, expenseCategoryName(e.category_id), e.amount || 0, e.name || '',
        formatDateString(e.date), e.reference || '', e.payment_mode || '',
      ]);
      const csv = 'data:text/csv;charset=utf-8,' +
        [headers.join(','), ...rows.map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(','))].join('\n');
      const link = document.createElement('a');
      link.setAttribute('href', encodeURI(csv));
      link.setAttribute('download', `expenses_${(customer.value?.company || 'customer').replace(/\s+/g, '_')}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    };

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

        // Load this customer's subscriptions, expenses, contracts, projects, and tasks
        loadSubscriptions();
        loadExpenses();
        loadContracts();
        loadProjects();
        loadTasks();
        loadStaffList();
        loadTickets();
        loadClientFiles();
        loadClientVaults();
        loadClientReminders();
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
      let list = customerProjects.value;
      const q = projectSearch.value.trim().toLowerCase();
      if (q) {
        list = list.filter(p => 
          (p.name || '').toLowerCase().includes(q) ||
          (p.description || '').toLowerCase().includes(q) ||
          (p.tags || '').toLowerCase().includes(q)
        );
      }
      return list;
    });

    const projectTotalPages = computed(() => 
      Math.max(1, Math.ceil(filteredProjects.value.length / projectPageSize.value))
    );

    const projectPageStart = computed(() => 
      (projectPage.value - 1) * projectPageSize.value
    );

    const paginatedProjects = computed(() => 
      filteredProjects.value.slice(projectPageStart.value, projectPageStart.value + projectPageSize.value)
    );

    // ── Tickets Tab: state, CRUD, helpers ────────────────────────
    const customerContacts = computed(() => customer.value?.contacts || []);
    const ticketView      = ref('list');
    const customerTicketList = ref([]);
    const ticketLoading   = ref(false);
    const ticketSaving    = ref(false);
    const editingTicketId = ref(null);
    const ticketSearch    = ref('');
    const ticketPage      = ref(1);
    const ticketPageSize  = ref(25);

    // Static department / service lists (extend as needed from your backend)
    const ticketDepartments = ref([
      { id: 1, name: 'Technical Support' },
      { id: 2, name: 'Sales & Pre-Sales' },
      { id: 3, name: 'Billing & Finance' },
      { id: 4, name: 'General Enquiries' },
    ]);
    const ticketServices = ref([
      { id: 1, name: 'Starter Plan' },
      { id: 2, name: 'Professional Plan' },
      { id: 3, name: 'Enterprise Plan' },
    ]);

    const ticketForm = reactive({
      subject: '',
      message: '',
      priority: 'Medium',
      status: 'Open',
      department_id: null,
      assigned_to: null,
      tags: '',
      service_id: null,
      contact_id: null,
      cc: '',
      contact_name: '',
      contact_email: '',
      predefined_reply: '',
      kb_link: '',
    });

    const loadTickets = async () => {
      if (!customer.value) return;
      ticketLoading.value = true;
      try {
        const res = await axios.get('/tickets', {
          params: { client_id: customer.value.id, per_page: 200 }
        });
        customerTicketList.value = res.data.tickets?.data || [];
      } catch {
        message.error('Failed to load tickets');
      } finally {
        ticketLoading.value = false;
      }
    };

    const resetTicketForm = () => {
      Object.assign(ticketForm, {
        subject: '', message: '', priority: 'Medium', status: 'Open',
        department_id: null, assigned_to: null, tags: '', service_id: null,
        contact_id: null, cc: '', contact_name: '', contact_email: '',
        predefined_reply: '', kb_link: '',
      });
    };

    const openTicketForm = (ticket = null) => {
      if (ticket) {
        editingTicketId.value = ticket.id;
        Object.assign(ticketForm, {
          subject:       ticket.subject || '',
          message:       ticket.message || '',
          priority:      ticket.priority || 'Medium',
          status:        ticket.status || 'Open',
          department_id: ticket.department_id || null,
          assigned_to:   ticket.assigned_to || null,
          tags:          ticket.tags || '',
          service_id:    ticket.service_id || null,
          contact_id:    ticket.contact_id || null,
          cc:            ticket.cc || '',
          contact_name:  ticket.contact ? `${ticket.contact.firstname} ${ticket.contact.lastname || ''}`.trim() : '',
          contact_email: ticket.contact?.email || '',
          predefined_reply: '',
          kb_link: '',
        });
      } else {
        editingTicketId.value = null;
        resetTicketForm();
        // Pre-select first contact if available
        if (customerContacts.value.length > 0) {
          const first = customerContacts.value[0];
          ticketForm.contact_id    = first.id;
          ticketForm.contact_name  = `${first.firstname} ${first.lastname || ''}`.trim();
          ticketForm.contact_email = first.email || '';
        }
      }
      ticketView.value = 'form';
    };

    const onTicketContactChange = () => {
      const c = customerContacts.value.find(x => x.id === ticketForm.contact_id);
      if (c) {
        ticketForm.contact_name  = `${c.firstname} ${c.lastname || ''}`.trim();
        ticketForm.contact_email = c.email || '';
      } else {
        ticketForm.contact_name  = '';
        ticketForm.contact_email = '';
      }
    };

    const saveTicket = async () => {
      if (!ticketForm.subject)  { message.error('Subject is required');      return; }
      if (!ticketForm.message)  { message.error('Ticket body is required');   return; }
      ticketSaving.value = true;
      try {
        const payload = {
          subject:       ticketForm.subject,
          message:       ticketForm.message,
          priority:      ticketForm.priority,
          status:        ticketForm.status,
          client_id:     customer.value?.id,
          department_id: ticketForm.department_id || null,
          assigned_to:   ticketForm.assigned_to   || null,
          tags:          ticketForm.tags           || null,
          service_id:    ticketForm.service_id     || null,
          contact_id:    ticketForm.contact_id     || null,
          cc:            ticketForm.cc             || null,
        };
        if (editingTicketId.value) {
          await axios.put(`/tickets/${editingTicketId.value}`, payload);
          message.success('Ticket updated successfully');
        } else {
          await axios.post('/tickets', payload);
          message.success('Ticket submitted successfully');
        }
        ticketView.value = 'list';
        loadTickets();
      } catch (e) {
        const errs = e.response?.data?.errors;
        message.error(errs ? Object.values(errs).flat().join(', ') : 'Failed to save ticket');
      } finally {
        ticketSaving.value = false;
      }
    };

    const deleteTicket = (id) => {
      Modal.confirm({
        title: 'Delete Ticket',
        content: 'Are you sure you want to delete this ticket?',
        okText: 'Delete', okType: 'danger', cancelText: 'Cancel',
        onOk: async () => {
          try {
            await axios.delete(`/tickets/${id}`);
            message.success('Ticket deleted');
            loadTickets();
          } catch { message.error('Failed to delete ticket'); }
        }
      });
    };

    const filteredCustomerTickets = computed(() => {
      const q = ticketSearch.value.toLowerCase().trim();
      if (!q) return customerTicketList.value;
      return customerTicketList.value.filter(t =>
        (t.subject   || '').toLowerCase().includes(q) ||
        (t.status    || '').toLowerCase().includes(q) ||
        (t.priority  || '').toLowerCase().includes(q) ||
        (t.tags      || '').toLowerCase().includes(q) ||
        (t.contact?.firstname || '').toLowerCase().includes(q) ||
        (t.contact?.lastname  || '').toLowerCase().includes(q)
      );
    });

    const ticketTotalPages = computed(() =>
      Math.max(1, Math.ceil(filteredCustomerTickets.value.length / ticketPageSize.value))
    );
    const ticketPageStart = computed(() => (ticketPage.value - 1) * ticketPageSize.value);
    const paginatedTickets = computed(() =>
      filteredCustomerTickets.value.slice(ticketPageStart.value, ticketPageStart.value + ticketPageSize.value)
    );

    const ticketDepartmentName = (id) =>
      ticketDepartments.value.find(d => d.id === id)?.name || '—';

    const ticketServiceName = (id) =>
      ticketServices.value.find(s => s.id === id)?.name || '—';

    const ticketPriorityClass = (p) => ({
      Low:    'badge-default',
      Medium: 'badge-yellow',
      High:   'badge-red',
      Urgent: 'badge-red',
    }[p] || 'badge-default');

    const exportTickets = () => {
      if (!customerTicketList.value.length) { message.warning('No tickets to export'); return; }
      const headers = ['#', 'Subject', 'Status', 'Priority', 'Department', 'Service', 'Contact', 'Tags', 'Last Reply', 'Created'];
      const rows = customerTicketList.value.map((t, i) => [
        i + 1,
        t.subject    || '',
        t.status     || '',
        t.priority   || '',
        ticketDepartmentName(t.department_id),
        ticketServiceName(t.service_id),
        t.contact ? `${t.contact.firstname} ${t.contact.lastname || ''}`.trim() : '',
        t.tags       || '',
        t.last_reply_at ? formatDateString(t.last_reply_at) : '',
        formatDateString(t.created_at),
      ]);
      const csv = 'data:text/csv;charset=utf-8,' +
        [headers.join(','), ...rows.map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`)
          .join(','))].join('\n');
      const link = document.createElement('a');
      link.setAttribute('href', encodeURI(csv));
      link.setAttribute('download', `tickets_${(customer.value?.company || 'customer').replace(/\s+/g, '_')}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    };

    // Predefined replies static data
    const predefinedReplies = [
      { id: 'reply1', title: 'Thank you for contacting us...', content: 'Thank you for contacting us. We have received your ticket and our support team is looking into it. We will get back to you as soon as possible.' },
      { id: 'reply2', title: 'We have received your request...', content: 'We have received your request. A support representative will review your ticket shortly. Thank you for your patience.' },
      { id: 'reply3', title: 'SLA / Urgency update', content: 'We are prioritizing your request as per our Service Level Agreement (SLA). Our team is working on a resolution and will provide an update within the next hour.' }
    ];

    // Knowledge base articles dynamic loading
    const kbArticles = ref([]);
    const loadKbArticles = async () => {
      try {
        const res = await axios.get('/kb-articles', { params: { status: 'published', per_page: 100 } });
        kbArticles.value = res.data.articles?.data || [];
      } catch (err) {
        console.error('Failed to load KB articles', err);
      }
    };

    const handlePredefinedReplyChange = (e) => {
      const val = e.target.value;
      if (!val) return;
      const reply = predefinedReplies.find(r => r.id === val);
      if (reply) {
        if (ticketForm.message) {
          ticketForm.message += "\n" + reply.content;
        } else {
          ticketForm.message = reply.content;
        }
      }
      ticketForm.predefined_reply = '';
    };

    const handleKbLinkChange = (e) => {
      const articleId = e.target.value;
      if (!articleId) return;
      const article = kbArticles.value.find(a => a.id == articleId);
      if (article) {
        const path = window.location.origin + window.config.path + '/knowledge-base/article/' + article.id;
        const linkText = `\nKnowledge Base Article: ${article.title} - ${path}`;
        if (ticketForm.message) {
          ticketForm.message += linkText;
        } else {
          ticketForm.message = linkText.trim();
        }
      }
      ticketForm.kb_link = '';
    };

    // filteredExpenses is defined in the Expenses tab state block below.

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
      Open: 'badge-red', 'In Progress': 'badge-blue', Answered: 'badge-green', 'On Hold': 'badge-orange', Closed: 'badge-gray'
    }[s] || 'badge-default');

    const formatCurrency = (val) => {
      if (val === undefined || val === null) return '$0.00';
      return '$' + parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2 });
    };

    onMounted(() => {
      fetchCustomerDetails();
      loadKbArticles();
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

    // ── Files Tab: state, CRUD, upload, helpers ───────────────────────
    const clientFiles = ref([]);
    const filesLoading = ref(false);
    const filesSaving = ref(false);
    const filesSearch = ref('');
    const filesPage = ref(1);
    const filesPageSize = ref(25);
    const dragover = ref(false);

    const loadClientFiles = async () => {
      if (!customer.value) return;
      filesLoading.value = true;
      try {
        const res = await axios.get(`/clients/${customer.value.id}/files`, {
          params: { search: filesSearch.value }
        });
        clientFiles.value = res.data || [];
      } catch (err) {
        message.error('Failed to load client files.');
      } finally {
        filesLoading.value = false;
      }
    };

    const uploadClientFile = async (file) => {
      if (!customer.value) return;
      filesSaving.value = true;
      filesLoading.value = true;
      const formData = new FormData();
      formData.append('file', file);
      formData.append('visible_to_customer', false);

      try {
        await axios.post(`/clients/${customer.value.id}/files`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        message.success('File uploaded successfully.');
        loadClientFiles();
      } catch (err) {
        const errMsg = err.response?.data?.message || 'Failed to upload file.';
        message.error(errMsg);
      } finally {
        filesSaving.value = false;
        filesLoading.value = false;
      }
    };

    const handleFileSelect = (e) => {
      const files = e.target.files;
      if (files.length > 0) {
        uploadClientFile(files[0]);
      }
    };

    const handleFileDrop = (e) => {
      dragover.value = false;
      const files = e.dataTransfer.files;
      if (files.length > 0) {
        uploadClientFile(files[0]);
      }
    };

    const deleteClientFile = (id) => {
      Modal.confirm({
        title: 'Delete File',
        content: 'Are you sure you want to delete this file?',
        okText: 'Delete',
        okType: 'danger',
        cancelText: 'Cancel',
        onOk: async () => {
          try {
            await axios.delete(`/client-files/${id}`);
            message.success('File deleted successfully.');
            loadClientFiles();
          } catch {
            message.error('Failed to delete file.');
          }
        }
      });
    };

    const toggleFileVisibility = async (id, visible) => {
      try {
        await axios.put(`/client-files/${id}/status`, {
          visible_to_customer: visible
        });
        message.success('Visibility status updated.');
        // Update local item visibility
        const file = clientFiles.value.find(f => f.id === id);
        if (file) file.visible_to_customer = visible;
      } catch {
        message.error('Failed to update visibility.');
      }
    };

    const chooseFromGoogleDrive = () => {
      message.info('Integration with Google Drive is a mockup in this demo.');
    };

    const chooseFromDropbox = () => {
      message.info('Integration with Dropbox is a mockup in this demo.');
    };

    const formatFileSize = (bytes) => {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };

    // Computeds for searching & paging client files
    const filteredFiles = computed(() => {
      const q = filesSearch.value.toLowerCase().trim();
      if (!q) return clientFiles.value;
      return clientFiles.value.filter(f =>
        (f.file_name || '').toLowerCase().includes(q)
      );
    });

    const filesTotalPages = computed(() =>
      Math.max(1, Math.ceil(filteredFiles.value.length / filesPageSize.value))
    );
    const filesPageStart = computed(() => (filesPage.value - 1) * filesPageSize.value);
    const paginatedFiles = computed(() =>
      filteredFiles.value.slice(filesPageStart.value, filesPageStart.value + filesPageSize.value)
    );

    // Vault States and Functions
    const clientVaults = ref([]);
    const vaultLoading = ref(false);
    const vaultSaving = ref(false);
    const vaultModalVisible = ref(false);
    const decryptedPasswords = reactive({});

    const vaultForm = reactive({
      id: null,
      server_address: '',
      port: null,
      username: '',
      password: '',
      short_description: '',
      visibility: 'all_staff',
      share_in_projects: false,
    });

    const loadClientVaults = async () => {
      if (!customer.value) return;
      vaultLoading.value = true;
      try {
        const res = await axios.get(`/clients/${customer.value.id}/vaults`);
        clientVaults.value = res.data || [];
      } catch (err) {
        message.error('Failed to load client vault entries.');
      } finally {
        vaultLoading.value = false;
      }
    };

    const openVaultModal = async (entry = null) => {
      if (entry) {
        try {
          let password = '';
          if (decryptedPasswords[entry.id]) {
            password = decryptedPasswords[entry.id];
          } else {
            const decryptRes = await axios.get(`/vault-entries/${entry.id}/decrypt`);
            password = decryptRes.data.password;
            decryptedPasswords[entry.id] = password;
          }

          Object.assign(vaultForm, {
            id: entry.id,
            server_address: entry.server_address,
            port: entry.port,
            username: entry.username,
            password: password,
            short_description: entry.short_description || '',
            visibility: entry.visibility,
            share_in_projects: entry.share_in_projects === 1 || entry.share_in_projects === true,
          });
        } catch (err) {
          message.error('Failed to retrieve entry details/password. Check your permissions.');
          return;
        }
      } else {
        Object.assign(vaultForm, {
          id: null,
          server_address: '',
          port: null,
          username: '',
          password: '',
          short_description: '',
          visibility: 'all_staff',
          share_in_projects: false,
        });
      }
      vaultModalVisible.value = true;
    };

    const saveVaultEntry = async () => {
      if (!customer.value) return;

      if (!vaultForm.server_address) {
        message.error('Server Address is required.');
        return;
      }
      if (!vaultForm.username) {
        message.error('Username is required.');
        return;
      }
      if (!vaultForm.password) {
        message.error('Password is required.');
        return;
      }

      vaultSaving.value = true;
      try {
        const payload = {
          server_address: vaultForm.server_address,
          port: vaultForm.port,
          username: vaultForm.username,
          password: vaultForm.password,
          short_description: vaultForm.short_description,
          visibility: vaultForm.visibility,
          share_in_projects: vaultForm.share_in_projects,
        };

        if (vaultForm.id) {
          await axios.put(`/vault-entries/${vaultForm.id}`, payload);
          message.success('Vault entry updated successfully.');
        } else {
          await axios.post(`/clients/${customer.value.id}/vaults`, payload);
          message.success('Vault entry added successfully.');
        }
        vaultModalVisible.value = false;
        loadClientVaults();
      } catch (err) {
        const errMsg = err.response?.data?.message || 'Failed to save vault entry.';
        message.error(errMsg);
      } finally {
        vaultSaving.value = false;
      }
    };

    const deleteVaultEntry = (id) => {
      Modal.confirm({
        title: 'Are you sure you want to delete this vault entry?',
        okText: 'Yes, Delete',
        okType: 'danger',
        cancelText: 'No',
        onOk: async () => {
          try {
            await axios.delete(`/vault-entries/${id}`);
            message.success('Vault entry deleted successfully.');
            loadClientVaults();
          } catch (err) {
            message.error('Failed to delete vault entry.');
          }
        }
      });
    };

    const toggleDecryptPassword = async (id) => {
      if (decryptedPasswords[id]) {
        delete decryptedPasswords[id];
      } else {
        try {
          const res = await axios.get(`/vault-entries/${id}/decrypt`);
          decryptedPasswords[id] = res.data.password;
        } catch (err) {
          message.error('Failed to decrypt password. Check your permissions.');
        }
      }
    };

    const copyToClipboard = (text) => {
      navigator.clipboard.writeText(text);
      message.success('Copied to clipboard!');
    };

    // ─── Reminders State & Functions ────────────────────────────────────────
    const clientReminders = ref([]);
    const reminderLoading  = ref(false);
    const reminderSaving   = ref(false);
    const reminderSearch   = ref('');
    const reminderPage     = ref(1);
    const reminderPageSize = ref(25);

    const reminderForm = reactive({
      description: '',
      date: '',
      remind_to: '',
      send_email: false,
    });

    const loadClientReminders = async () => {
      if (!customer.value) return;
      reminderLoading.value = true;
      try {
        const res = await axios.get(`/clients/${customer.value.id}/reminders`);
        clientReminders.value = res.data || [];
      } catch (err) {
        message.error('Failed to load reminders.');
      } finally {
        reminderLoading.value = false;
      }
    };

    const filteredReminders = computed(() => {
      const q = reminderSearch.value.toLowerCase().trim();
      if (!q) return clientReminders.value;
      return clientReminders.value.filter(r =>
        (r.description || '').toLowerCase().includes(q) ||
        (r.remind_to_user?.name || '').toLowerCase().includes(q)
      );
    });

    const reminderTotalPages = computed(() =>
      Math.max(1, Math.ceil(filteredReminders.value.length / reminderPageSize.value))
    );
    const reminderPageStart = computed(() => (reminderPage.value - 1) * reminderPageSize.value);
    const paginatedReminders = computed(() =>
      filteredReminders.value.slice(reminderPageStart.value, reminderPageStart.value + reminderPageSize.value)
    );

    const saveReminder = async () => {
      if (!reminderForm.description.trim()) {
        message.error('Description is required.');
        return;
      }
      if (!reminderForm.date) {
        message.error('Date to be notified is required.');
        return;
      }
      if (!reminderForm.remind_to) {
        message.error('Please select a staff member to remind.');
        return;
      }

      reminderSaving.value = true;
      try {
        await axios.post(`/clients/${customer.value.id}/reminders`, {
          description: reminderForm.description,
          date:        reminderForm.date,
          remind_to:   reminderForm.remind_to,
          send_email:  reminderForm.send_email,
        });
        message.success('Reminder set successfully.');
        // reset form
        Object.assign(reminderForm, { description: '', date: '', remind_to: '', send_email: false });
        loadClientReminders();
      } catch (err) {
        const errMsg = err.response?.data?.message || 'Failed to set reminder.';
        message.error(errMsg);
      } finally {
        reminderSaving.value = false;
      }
    };

    const deleteReminder = (id) => {
      Modal.confirm({
        title: 'Delete this reminder?',
        okText: 'Yes, Delete',
        okType: 'danger',
        cancelText: 'Cancel',
        onOk: async () => {
          try {
            await axios.delete(`/reminders/${id}`);
            message.success('Reminder deleted.');
            loadClientReminders();
          } catch (err) {
            message.error('Failed to delete reminder.');
          }
        },
      });
    };
    // ────────────────────────────────────────────────────────────────────────

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
      filteredCustomerTickets,
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
      deleteCreditNote,

      // Subscriptions tab
      subView,
      subscriptions,
      subLoading,
      subSaving,
      editingSubId,
      subSearch,
      subPage,
      subPageSize,
      subForm,
      billingPlans,
      customerSelectOptions,
      filteredSubscriptions,
      paginatedSubscriptions,
      subTotalPages,
      subPageStart,
      loadSubscriptions,
      openSubscriptionForm,
      onBillingPlanChange,
      saveSubscription,
      deleteSubscription,
      subStatusClass,
      subStatusLabel,
      nextBillingCycle,
      exportSubscriptions,

      // Expenses tab
      expView,
      clientExpenses,
      expLoading,
      expSaving,
      editingExpId,
      expSearch,
      expPage,
      expPageSize,
      expenseCategories,
      expFields,
      expForm,
      expenseCategoryName,
      filteredExpenses,
      expTotalPages,
      expPageStart,
      paginatedExpenses,
      expenseSummary,
      loadExpenses,
      openExpenseForm,
      addExpenseCategory,
      saveExpense,
      deleteExpense,
      exportExpenses,

      // Contracts tab
      contractView,
      contractTypes,
      contractLoading,
      contractSaving,
      editingContractId,
      contractSearch,
      contractPage,
      contractPageSize,
      contractForm,
      loadContracts,
      openContractForm,
      saveContract,
      deleteContract,
      contractTypeName,
      filteredContracts,
      paginatedContracts,
      contractTotalPages,
      contractPageStart,
      exportContracts,

      // Projects tab
      customerProjects,
      projectView,
      projectLoading,
      projectSaving,
      editingProjectId,
      projectSearch,
      projectPage,
      projectPageSize,
      staffList,
      projectFormTab,
      projectStats,
      projectForm,
      loadProjects,
      loadStaffList,
      resetProjectForm,
      openProjectForm,
      saveProject,
      deleteProject,
      exportProjects,
      projectStatusClass,
      paginatedProjects,
      projectTotalPages,
      projectPageStart,

      // Tasks tab
      taskView,
      customerTasks,
      taskLoading,
      taskSaving,
      editingTaskId,
      taskSearch,
      taskPage,
      taskPageSize,
      taskRelatedFilter,
      taskRelatedTypes,
      taskForm,
      loadTasks,
      openTaskForm,
      toggleTaskAssignee,
      toggleTaskFollower,
      saveTask,
      deleteTask,
      filteredTasks,
      paginatedTasks,
      taskTotalPages,
      taskPageStart,
      taskStatusClass,
      taskPriorityClass,
      exportTasks,

      // Tickets Tab exports
      ticketView,
      customerTicketList,
      ticketLoading,
      ticketSaving,
      ticketSearch,
      ticketPage,
      ticketPageSize,
      ticketDepartments,
      ticketServices,
      ticketForm,
      loadTickets,
      openTicketForm,
      onTicketContactChange,
      saveTicket,
      deleteTicket,
      ticketTotalPages,
      ticketPageStart,
      paginatedTickets,
      ticketDepartmentName,
      ticketServiceName,
      ticketPriorityClass,
      customerContacts,
      exportTickets,
      predefinedReplies,
      kbArticles,
      handlePredefinedReplyChange,
      handleKbLinkChange,

      // Files Tab exports
      clientFiles,
      filesLoading,
      filesSaving,
      filesSearch,
      filesPage,
      filesPageSize,
      dragover,
      loadClientFiles,
      uploadClientFile,
      handleFileSelect,
      handleFileDrop,
      deleteClientFile,
      toggleFileVisibility,
      chooseFromGoogleDrive,
      chooseFromDropbox,
      formatFileSize,
      filteredFiles,
      filesTotalPages,
      filesPageStart,
      paginatedFiles,

      // Vault tab exports
      clientVaults,
      vaultLoading,
      vaultSaving,
      vaultModalVisible,
      decryptedPasswords,
      vaultForm,
      loadClientVaults,
      openVaultModal,
      saveVaultEntry,
      deleteVaultEntry,
      toggleDecryptPassword,
      copyToClipboard,

      // Reminders tab exports
      clientReminders,
      reminderLoading,
      reminderSaving,
      reminderSearch,
      reminderPage,
      reminderPageSize,
      reminderForm,
      filteredReminders,
      paginatedReminders,
      reminderTotalPages,
      reminderPageStart,
      loadClientReminders,
      saveReminder,
      deleteReminder,
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

/* ── Subscriptions tab ── */
.btn-new-subscription {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: #1e293b;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 9px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-new-subscription:hover { background: #0f172a; }

.sub-refresh-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #475569;
}

.subscription-form-wrap {
  max-width: 760px;
}
.subscription-form-wrap .form-group { margin-bottom: 18px; }

.sub-form-group-box {
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 18px 20px 2px;
  margin-bottom: 22px;
  background: #fcfcfd;
}

.hint-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  border: 1px solid #cbd5e1;
  color: #94a3b8;
  font-size: 10px;
  font-weight: 700;
  line-height: 1;
  cursor: help;
  margin-right: 2px;
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

/* Expenses Tab Styles */
.exp-stats-row {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 12px;
  margin-bottom: 16px;
}
.exp-stat-card {
  border-radius: 6px;
  padding: 12px 14px;
  border: 1px solid #e2e8f0;
  background: #fff;
}
.exp-stat-label {
  font-size: 11px;
  font-weight: 600;
  margin-bottom: 2px;
}
.exp-lbl-total { color: #64748b; }
.exp-lbl-billable { color: #16a34a; }
.exp-lbl-nonbillable { color: #64748b; }
.exp-lbl-notinvoiced { color: #dc2626; }
.exp-lbl-billed { color: #16a34a; }

.exp-stat-val {
  font-size: 18px;
  font-weight: 700;
  color: #1e293b;
}

.receipt-dropzone {
  border: 1.5px dashed #cbd5e1;
  background: #f8fafc;
  padding: 32px;
  color: #64748b;
  border-radius: 8px;
  text-align: center;
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 22px;
  cursor: pointer;
  transition: border-color 0.15s, background 0.15s;
}
.receipt-dropzone:hover {
  border-color: #2563eb;
  background: #eff6ff;
}

.exp-toggles {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 18px;
}
.exp-toggle-link {
  color: #2563eb;
  cursor: pointer;
  font-size: 12.5px;
  font-weight: 600;
  user-select: none;
}
.exp-toggle-link:hover {
  color: #1d4ed8;
  text-decoration: underline;
}
.exp-toggle-link.active {
  color: #1e293b;
  text-decoration: line-through;
}

.exp-add-btn {
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #bfdbfe;
  padding: 5px;
  font-size: 12px;
  font-weight: 600;
  border-radius: 4px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  transition: background 0.1s;
}
.exp-add-btn:hover {
  background: #dbeafe;
}

/* ── Tasks Tab Styles ──────────────────────────────────────────── */
.tasks-related-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 0 14px;
  flex-wrap: wrap;
}
.related-label {
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  white-space: nowrap;
}
.related-checkboxes {
  display: flex;
  gap: 18px;
  flex-wrap: wrap;
  align-items: center;
}
.related-checkbox-item {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  cursor: pointer;
  font-size: 12.5px;
  font-weight: 500;
  color: #475569;
  user-select: none;
}
.related-checkbox-item:hover {
  color: #1d4ed8;
}
.related-cb {
  width: 14px;
  height: 14px;
  accent-color: #2563eb;
  cursor: pointer;
  flex-shrink: 0;
}

/* Toggle Pills (Public / Billable) */
.task-toggles-row {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items: center;
  padding-bottom: 4px;
}
.toggle-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 14px;
  border-radius: 4px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  font-size: 12px;
  font-weight: 500;
  color: #64748b;
  cursor: pointer;
  transition: all 0.12s;
  user-select: none;
}
.toggle-pill:hover { background: #f1f5f9; border-color: #cbd5e1; }
.toggle-pill.active { background: #2563eb; color: #fff; border-color: #2563eb; }

/* Multi-select dropdown */
.multi-select-wrap {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.multi-select {
  min-height: 110px !important;
  height: auto !important;
  padding: 4px !important;
  resize: vertical;
  cursor: pointer;
}
.multi-select option {
  padding: 5px 8px;
  border-radius: 3px;
  font-size: 13px;
  cursor: pointer;
}
.multi-select option:checked {
  background: #2563eb;
  color: #fff;
}
.multi-select-selected {
  display: flex;
  gap: 5px;
  flex-wrap: wrap;
}
.selected-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 8px;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 20px;
  font-size: 11.5px;
  font-weight: 500;
  color: #1d4ed8;
}
.remove-tag {
  background: none;
  border: none;
  padding: 0;
  font-size: 14px;
  line-height: 1;
  color: #93c5fd;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
}
.remove-tag:hover {
  color: #1d4ed8;
}

/* Rich Text Editor Stub */
.rich-editor-wrap {
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  overflow: hidden;
}
.rich-toolbar {
  display: flex;
  align-items: center;
  gap: 2px;
  padding: 5px 8px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}
.rich-tool {
  padding: 3px 7px;
  border-radius: 3px;
  font-size: 12px;
  color: #475569;
  cursor: pointer;
  transition: background 0.1s;
}
.rich-tool:hover { background: #e2e8f0; }
.rich-sep {
  width: 1px;
  height: 16px;
  background: #e2e8f0;
  margin: 0 4px;
  display: inline-block;
}
.rich-editor-wrap .input-ctrl {
  border: none;
  border-radius: 0;
  box-shadow: none;
}
.rich-editor-wrap .input-ctrl:focus {
  box-shadow: none;
  border: none;
}

/* Tag Pill */
.tag-pill {
  display: inline-flex;
  align-items: center;
  padding: 2px 8px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 500;
  color: #64748b;
}

/* Task form wrap */
.task-form-wrap {
  padding-top: 8px;
}

/* Ticket body toolbar & select styling */
.ticket-body-toolbar {
  display: flex;
  gap: 10px;
  margin-bottom: 8px;
}
.ticket-body-select {
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 13px;
  color: #334155;
  background-color: #ffffff;
  cursor: pointer;
  outline: none;
  min-width: 200px;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}
.ticket-body-select:hover {
  border-color: #94a3b8;
  background-color: #f8fafc;
}
.ticket-body-select:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
}

/* File drag-and-drop zone */
.file-dropzone {
  border: 2px dashed #cbd5e1;
  border-radius: 8px;
  background-color: #f8fafc;
  padding: 30px 20px;
  text-align: center;
  transition: all 0.2s ease;
  cursor: pointer;
}
.file-dropzone:hover, .file-dropzone-dragover {
  border-color: #6366f1;
  background-color: #f0f2ff;
}
.btn-outline-sm {
  background-color: #ffffff;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  padding: 6px 12px;
  font-size: 12px;
  font-weight: 500;
  color: #374151;
  cursor: pointer;
  transition: all 0.15s ease;
}
.btn-outline-sm:hover {
  background-color: #f3f4f6;
  border-color: #9ca3af;
}
.btn-outline-sm-icon {
  background-color: #ffffff;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  padding: 5px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.15s ease;
}
.btn-outline-sm-icon:hover {
  background-color: #f3f4f6;
  border-color: #9ca3af;
}
.alert-info-box {
  background-color: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 6px;
  padding: 10px 14px;
}

</style>
