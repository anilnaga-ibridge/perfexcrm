<template>
  <div class="setup-page">
    <!-- Page Header -->
    <div class="setup-header">
      <h1 class="setup-title">Setup</h1>
    </div>

    <div class="setup-layout">
      <!-- Left Side Menu -->
      <aside class="setup-sidebar">
        <nav class="setup-nav">
          <a
            v-for="sec in sections"
            :key="sec.id"
            @click="activeSection = sec.id"
            :class="['setup-nav-item', { 'setup-nav-item--active': activeSection === sec.id }]"
          >
            <span class="setup-nav-icon" v-html="sec.icon"></span>
            <span>{{ sec.label }}</span>
          </a>
        </nav>
      </aside>

      <!-- Right Content Panel -->
      <div class="setup-content">

        <!-- ── STAFF ── -->
        <div v-if="activeSection === 'staff'">
          <div class="section-toolbar">
            <h2 class="section-title">Staff Members</h2>
            <button class="btn-primary" @click="openAddStaff = true">
              <span>+</span> Add Staff Member
            </button>
          </div>

          <div class="data-table-wrap">
            <div class="data-table-controls">
              <a-input-search
                v-model:value="staffSearch"
                placeholder="Search staff..."
                @search="loadStaff"
                style="width:280px"
                size="small"
              />
            </div>
            <a-table
              :dataSource="staffList"
              :columns="staffColumns"
              :loading="staffLoading"
              :pagination="{ pageSize: 15, total: staffTotal }"
              row-key="id"
              size="small"
            >
              <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'name'">
                  <div class="flex-name">
                    <div class="avatar-circle">{{ initials(record.name) }}</div>
                    <div>
                      <div class="name-main">{{ record.name }}</div>
                      <div class="name-sub">{{ record.email }}</div>
                    </div>
                  </div>
                </template>
                <template v-if="column.key === 'role'">
                  <a-tag :color="record.role === 'admin' ? 'blue' : 'default'" style="text-transform:capitalize">{{ record.role || 'staff' }}</a-tag>
                </template>
                <template v-if="column.key === 'active'">
                  <a-badge :status="record.active ? 'success' : 'default'" :text="record.active ? 'Active' : 'Inactive'" />
                </template>
                <template v-if="column.key === 'actions'">
                  <div class="row-actions">
                    <a-button size="small" type="link" @click="editStaffMember(record)">Edit</a-button>
                    <a-button size="small" type="link" danger @click="deleteStaff(record.id)">Delete</a-button>
                  </div>
                </template>
              </template>
            </a-table>
          </div>

          <!-- Add Staff Drawer -->
          <a-drawer
            v-model:open="openAddStaff"
            title="Add Staff Member"
            placement="right"
            :width="440"
            @close="resetStaffForm"
          >
            <a-form layout="vertical" :model="staffForm" @finish="saveStaff">
              <a-form-item label="Full Name" name="name" :rules="[{required:true,message:'Name required'}]">
                <a-input v-model:value="staffForm.name" placeholder="Enter full name" />
              </a-form-item>
              <a-form-item label="Email Address" name="email" :rules="[{required:true,type:'email',message:'Valid email required'}]">
                <a-input v-model:value="staffForm.email" type="email" placeholder="staff@example.com" />
              </a-form-item>
              <a-form-item label="Role">
                <a-select v-model:value="staffForm.role" style="width:100%">
                  <a-select-option value="admin">Administrator</a-select-option>
                  <a-select-option value="staff">Staff</a-select-option>
                  <a-select-option value="manager">Manager</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Phone Number">
                <a-input v-model:value="staffForm.phone" placeholder="+1 234 567 8900" />
              </a-form-item>
              <a-form-item label="Department">
                <a-input v-model:value="staffForm.department" placeholder="e.g. Sales, Development" />
              </a-form-item>
              <a-form-item label="Password" name="password" :rules="[{required:!staffForm.id,message:'Password required for new staff'}]">
                <a-input-password v-model:value="staffForm.password" placeholder="Set password" />
              </a-form-item>
              <a-form-item label="Confirm Password">
                <a-input-password v-model:value="staffForm.password_confirmation" placeholder="Confirm password" />
              </a-form-item>
              <div class="drawer-footer">
                <a-button @click="openAddStaff = false">Cancel</a-button>
                <a-button type="primary" html-type="submit" :loading="staffSaving">
                  {{ staffForm.id ? 'Update Staff' : 'Add Staff Member' }}
                </a-button>
              </div>
            </a-form>
          </a-drawer>
        </div>

        <!-- ── CUSTOMERS (Settings) ── -->
        <div v-if="activeSection === 'customers'">
          <h2 class="section-title">Customer Settings</h2>
          <div class="settings-card">
            <div class="settings-group">
              <h3 class="settings-group-title">General</h3>
              <div class="settings-row">
                <div class="settings-label">
                  <span>Allow customer registration</span>
                  <p class="settings-hint">Allow customers to register their own account from the client portal login area</p>
                </div>
                <a-switch v-model:checked="customerSettings.allow_registration" />
              </div>
              <div class="settings-row">
                <div class="settings-label">
                  <span>Require email confirmation on registration</span>
                  <p class="settings-hint">Customers must confirm their email before gaining access</p>
                </div>
                <a-switch v-model:checked="customerSettings.email_confirmation" />
              </div>
              <div class="settings-row">
                <div class="settings-label">
                  <span>Allow customer to login</span>
                </div>
                <a-switch v-model:checked="customerSettings.allow_login" />
              </div>
            </div>
            <div class="settings-group">
              <h3 class="settings-group-title">Groups</h3>
              <div class="settings-row">
                <div class="settings-label">
                  <span>Default customer groups</span>
                </div>
                <a-select mode="tags" style="width:300px" placeholder="Add groups..." v-model:value="customerSettings.groups">
                  <a-select-option value="VIP">VIP</a-select-option>
                  <a-select-option value="Wholesaler">Wholesaler</a-select-option>
                  <a-select-option value="Low Budget">Low Budget</a-select-option>
                  <a-select-option value="High Budget">High Budget</a-select-option>
                </a-select>
              </div>
            </div>
            <div class="settings-actions">
              <a-button type="primary" @click="saveSettings('customer')">Save Settings</a-button>
            </div>
          </div>
        </div>

        <!-- ── SUPPORT (Ticket Settings) ── -->
        <div v-if="activeSection === 'support'">
          <h2 class="section-title">Support Settings</h2>
          <div class="settings-card">
            <div class="settings-group">
              <h3 class="settings-group-title">Tickets</h3>
              <div class="settings-row">
                <div class="settings-label">
                  <span>Allow non-customers to submit tickets</span>
                </div>
                <a-switch v-model:checked="supportSettings.allow_non_customer_tickets" />
              </div>
              <div class="settings-row">
                <div class="settings-label">
                  <span>Auto-close tickets after (days)</span>
                </div>
                <a-input-number v-model:value="supportSettings.auto_close_days" :min="1" :max="365" />
              </div>
              <div class="settings-row">
                <div class="settings-label">
                  <span>Default ticket priority</span>
                </div>
                <a-select v-model:value="supportSettings.default_priority" style="width:200px">
                  <a-select-option value="low">Low</a-select-option>
                  <a-select-option value="medium">Medium</a-select-option>
                  <a-select-option value="high">High</a-select-option>
                  <a-select-option value="critical">Critical</a-select-option>
                </a-select>
              </div>
            </div>
            <div class="settings-group">
              <h3 class="settings-group-title">Departments</h3>
              <div class="settings-hint-block">Configure ticket routing by department. Departments allow you to organize your support tickets.</div>
              <div class="dept-list">
                <div v-for="dept in departments" :key="dept" class="dept-item">
                  <span>{{ dept }}</span>
                  <button class="dept-remove" @click="removeDept(dept)">×</button>
                </div>
                <a-input
                  v-model:value="newDept"
                  placeholder="Add department..."
                  @pressEnter="addDept"
                  style="width:200px"
                  size="small"
                />
              </div>
            </div>
            <div class="settings-actions">
              <a-button type="primary" @click="saveSettings('support')">Save Settings</a-button>
            </div>
          </div>
        </div>

        <!-- ── LEADS (Settings) ── -->
        <div v-if="activeSection === 'leads'">
          <h2 class="section-title">Leads Settings</h2>
          <div class="settings-card">
            <div class="settings-group">
              <h3 class="settings-group-title">Lead Pipeline</h3>
              <div class="settings-row">
                <div class="settings-label">
                  <span>Allow contact form integration</span>
                  <p class="settings-hint">Enable web form embed to capture leads automatically</p>
                </div>
                <a-switch v-model:checked="leadsSettings.contact_form" />
              </div>
              <div class="settings-row">
                <div class="settings-label">
                  <span>Auto assign leads to staff</span>
                </div>
                <a-switch v-model:checked="leadsSettings.auto_assign" />
              </div>
            </div>
            <div class="settings-group">
              <h3 class="settings-group-title">Statuses</h3>
              <div class="status-list">
                <div v-for="s in leadStatuses" :key="s.id" class="status-item">
                  <span class="status-dot" :style="{background: s.color}"></span>
                  <span>{{ s.name }}</span>
                </div>
              </div>
            </div>
            <div class="settings-actions">
              <a-button type="primary" @click="saveSettings('leads')">Save Settings</a-button>
            </div>
          </div>
        </div>

        <!-- ── FINANCE ── -->
        <div v-if="activeSection === 'finance'">
          <h2 class="section-title">Finance Settings</h2>
          <div class="settings-card">
            <div class="settings-group">
              <h3 class="settings-group-title">Currency & Tax</h3>
              <div class="settings-row">
                <div class="settings-label"><span>Default Currency</span></div>
                <a-select v-model:value="financeSettings.currency" style="width:200px">
                  <a-select-option value="USD">USD - US Dollar</a-select-option>
                  <a-select-option value="EUR">EUR - Euro</a-select-option>
                  <a-select-option value="GBP">GBP - British Pound</a-select-option>
                  <a-select-option value="INR">INR - Indian Rupee</a-select-option>
                </a-select>
              </div>
              <div class="settings-row">
                <div class="settings-label"><span>Default Tax Rate (%)</span></div>
                <a-input-number v-model:value="financeSettings.tax_rate" :min="0" :max="100" :step="0.5" />
              </div>
              <div class="settings-row">
                <div class="settings-label"><span>Invoice Prefix</span></div>
                <a-input v-model:value="financeSettings.invoice_prefix" style="width:120px" />
              </div>
              <div class="settings-row">
                <div class="settings-label"><span>Estimate Prefix</span></div>
                <a-input v-model:value="financeSettings.estimate_prefix" style="width:120px" />
              </div>
            </div>
            <div class="settings-group">
              <h3 class="settings-group-title">Payment Methods</h3>
              <div class="payment-methods">
                <div v-for="pm in paymentMethods" :key="pm.key" class="payment-method-item">
                  <div class="pm-info">
                    <span class="pm-name">{{ pm.name }}</span>
                    <span class="pm-badge" :class="{'pm-badge--active': pm.active}">{{ pm.active ? 'Active' : 'Inactive' }}</span>
                  </div>
                  <a-switch v-model:checked="pm.active" size="small" />
                </div>
              </div>
            </div>
            <div class="settings-actions">
              <a-button type="primary" @click="saveSettings('finance')">Save Settings</a-button>
            </div>
          </div>
        </div>

        <!-- ── CONTRACTS ── -->
        <div v-if="activeSection === 'contracts'">
          <h2 class="section-title">Contracts Settings</h2>
          <div class="settings-card">
            <div class="settings-group">
              <h3 class="settings-group-title">Contract Types</h3>
              <div class="type-list">
                <div v-for="t in contractTypes" :key="t" class="type-item">
                  <span>{{ t }}</span>
                  <button class="dept-remove" @click="removeContractType(t)">×</button>
                </div>
                <a-input
                  v-model:value="newContractType"
                  placeholder="Add type..."
                  @pressEnter="addContractType"
                  style="width:200px"
                  size="small"
                />
              </div>
            </div>
            <div class="settings-actions">
              <a-button type="primary" @click="saveSettings('contracts')">Save Settings</a-button>
            </div>
          </div>
        </div>

        <!-- ── ESTIMATE REQUEST ── -->
        <div v-if="activeSection === 'estimate-request'">
          <h2 class="section-title">Estimate Request Settings</h2>
          <div class="settings-card">
            <div class="settings-group">
              <h3 class="settings-group-title">Form Configuration</h3>
              <div class="settings-row">
                <div class="settings-label">
                  <span>Enable estimate request form</span>
                  <p class="settings-hint">Allow visitors to submit estimate requests from your website</p>
                </div>
                <a-switch v-model:checked="estimateRequestSettings.enabled" />
              </div>
              <div class="settings-row">
                <div class="settings-label"><span>Notify staff on new request</span></div>
                <a-switch v-model:checked="estimateRequestSettings.notify_staff" />
              </div>
            </div>
            <div class="settings-actions">
              <a-button type="primary" @click="saveSettings('estimate-request')">Save Settings</a-button>
            </div>
          </div>
        </div>

        <!-- ── MODULES ── -->
        <div v-if="activeSection === 'modules'">
          <h2 class="section-title">Modules</h2>

          <!-- Upload Module Card -->
          <div class="mod-upload-card">
            <div class="mod-upload-title">Upload Module</div>
            <p class="mod-upload-desc">If you have a module in a <code>.zip</code> format, you may install it by uploading it here.</p>
            <div class="mod-upload-row">
              <label class="mod-file-label">
                <input type="file" accept=".zip" @change="onModuleFileChange" style="display:none" />
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="14" height="14"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                {{ moduleFileName || 'No file chosen' }}
              </label>
              <button class="btn-primary btn-sm" @click="uploadModule" :disabled="!moduleFileName">
                Upload Module
              </button>
            </div>
          </div>

          <!-- Table Controls -->
          <div class="mod-table-controls">
            <div class="mod-table-left">
              <a-select v-model:value="modPerPage" size="small" style="width:65px">
                <a-select-option :value="10">10</a-select-option>
                <a-select-option :value="25">25</a-select-option>
                <a-select-option :value="50">50</a-select-option>
              </a-select>
            </div>
            <div class="mod-table-right">
              <a-input
                v-model:value="modSearch"
                placeholder="Search..."
                size="small"
                style="width:200px"
                allow-clear
              />
            </div>
          </div>

          <!-- Modules Table -->
          <div class="mod-table-wrap">
            <table class="mod-table">
              <thead>
                <tr>
                  <th>Module</th>
                  <th>Description</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="mod in filteredModules" :key="mod.key">
                  <td class="mod-name-cell">
                    <div class="mod-name-link">{{ mod.name }}</div>
                    <div class="mod-actions-row">
                      <template v-for="(action, idx) in mod.actions" :key="idx">
                        <span v-if="idx > 0" class="mod-action-sep">|</span>
                        <a
                          class="mod-action-link"
                          :class="{ 'mod-action-link--deactivate': action === 'Deactivate', 'mod-action-link--danger': action === 'Activate' }"
                          @click="handleModuleAction(mod, action)"
                        >{{ action }}</a>
                      </template>
                    </div>
                  </td>
                  <td class="mod-desc-cell">
                    <div class="mod-desc-text">{{ mod.description }}</div>
                    <div class="mod-version">Version {{ mod.version }}</div>
                  </td>
                </tr>
                <tr v-if="filteredModules.length === 0">
                  <td colspan="2" style="text-align:center;padding:40px;color:#94a3b8">No modules found</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Table Footer -->
          <div class="mod-table-footer">
            Showing 1 to {{ filteredModules.length }} of {{ filteredModules.length }} entries
          </div>
        </div>

        <!-- ── EMAIL TEMPLATES ── -->
        <div v-if="activeSection === 'email-templates'">
          <h2 class="section-title">Email Templates</h2>
          <div class="data-table-wrap">
            <a-table
              :dataSource="emailTemplates"
              :columns="templateColumns"
              size="small"
              :pagination="false"
              row-key="id"
            >
              <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'name'">
                  <a class="link-blue">{{ record.name }}</a>
                </template>
                <template v-if="column.key === 'subject'">
                  <span class="text-muted">{{ record.subject }}</span>
                </template>
                <template v-if="column.key === 'active'">
                  <a-switch v-model:checked="record.active" size="small" />
                </template>
                <template v-if="column.key === 'actions'">
                  <a-button size="small" type="link">Edit</a-button>
                </template>
              </template>
            </a-table>
          </div>
        </div>

        <!-- ── CUSTOM FIELDS ── -->
        <div v-if="activeSection === 'custom-fields'">
          <div class="section-toolbar">
            <h2 class="section-title">Custom Fields</h2>
            <button class="btn-primary">+ Add Custom Field</button>
          </div>
          <div class="settings-card" style="text-align:center;padding:60px 20px">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin:0 auto 12px">
              <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
            </svg>
            <p style="color:#64748b;font-size:14px">No custom fields configured yet.</p>
            <p style="color:#94a3b8;font-size:13px;margin-top:6px">Create custom fields to capture additional information for clients, contacts, leads, invoices and more.</p>
          </div>
        </div>

        <!-- ── GDPR ── -->
        <div v-if="activeSection === 'gdpr'">
          <h2 class="section-title">GDPR Settings</h2>
          <div class="settings-card">
            <div class="settings-group">
              <div class="settings-row">
                <div class="settings-label">
                  <span>Enable GDPR features</span>
                  <p class="settings-hint">Enables GDPR compliance tools including consent tracking and data export/deletion requests</p>
                </div>
                <a-switch v-model:checked="gdprSettings.enabled" />
              </div>
              <div class="settings-row">
                <div class="settings-label">
                  <span>Show GDPR notice on registration</span>
                </div>
                <a-switch v-model:checked="gdprSettings.show_notice" />
              </div>
              <div class="settings-row">
                <div class="settings-label">
                  <span>Allow customers to download their data</span>
                </div>
                <a-switch v-model:checked="gdprSettings.allow_data_download" />
              </div>
            </div>
            <div class="settings-actions">
              <a-button type="primary" @click="saveSettings('gdpr')">Save Settings</a-button>
            </div>
          </div>
        </div>

        <!-- ── ROLES ── -->
        <div v-if="activeSection === 'roles'">
          <div class="section-toolbar">
            <h2 class="section-title">Roles & Permissions</h2>
            <button class="btn-primary">+ Add Role</button>
          </div>
          <div class="roles-list">
            <div v-for="role in roles" :key="role.name" class="role-card">
              <div class="role-info">
                <div class="role-name">{{ role.name }}</div>
                <div class="role-desc">{{ role.description }}</div>
              </div>
              <div class="role-count">{{ role.count }} members</div>
              <div class="role-actions">
                <a-button size="small" type="link">Edit Permissions</a-button>
              </div>
            </div>
          </div>
        </div>

        <!-- ── MENU SETUP ── -->
        <div v-if="activeSection === 'menu-setup'">
          <h2 class="section-title">Menu Setup</h2>
          <div class="settings-card">
            <p class="settings-hint-block">Configure which navigation items are visible in the sidebar. Drag to reorder.</p>
            <div class="menu-items-list">
              <div v-for="item in menuSetupItems" :key="item.key" class="menu-setup-item">
                <span class="drag-handle">⠿</span>
                <span class="menu-item-label">{{ item.label }}</span>
                <a-switch v-model:checked="item.visible" size="small" />
              </div>
            </div>
            <div class="settings-actions">
              <a-button type="primary" @click="saveSettings('menu-setup')">Save Menu</a-button>
            </div>
          </div>
        </div>

        <!-- ── THEME STYLE ── -->
        <div v-if="activeSection === 'theme-style'">
          <h2 class="section-title">Theme Style</h2>
          <div class="settings-card">
            <div class="settings-group">
              <h3 class="settings-group-title">Color Scheme</h3>
              <div class="theme-swatches">
                <div
                  v-for="theme in themes"
                  :key="theme.value"
                  @click="financeSettings.theme = theme.value"
                  :class="['theme-swatch', { 'theme-swatch--active': financeSettings.theme === theme.value }]"
                  :style="{ background: theme.color }"
                  :title="theme.label"
                ></div>
              </div>
            </div>
            <div class="settings-actions">
              <a-button type="primary" @click="saveSettings('theme')">Apply Theme</a-button>
            </div>
          </div>
        </div>

        <!-- ── SETTINGS (General) ── -->
        <div v-if="activeSection === 'settings'">
          <h2 class="section-title">General Settings</h2>
          <div class="settings-card">
            <div class="settings-group">
              <h3 class="settings-group-title">Company Information</h3>
              <a-form layout="vertical">
                <div class="settings-form-grid">
                  <a-form-item label="Company Name">
                    <a-input v-model:value="generalSettings.company_name" />
                  </a-form-item>
                  <a-form-item label="Company Phone">
                    <a-input v-model:value="generalSettings.company_phone" />
                  </a-form-item>
                  <a-form-item label="Company Email">
                    <a-input v-model:value="generalSettings.company_email" type="email" />
                  </a-form-item>
                  <a-form-item label="Company Website">
                    <a-input v-model:value="generalSettings.company_website" />
                  </a-form-item>
                  <a-form-item label="Address" :span="2">
                    <a-textarea v-model:value="generalSettings.address" :rows="3" />
                  </a-form-item>
                  <a-form-item label="City">
                    <a-input v-model:value="generalSettings.city" />
                  </a-form-item>
                  <a-form-item label="Country">
                    <a-input v-model:value="generalSettings.country" />
                  </a-form-item>
                </div>
              </a-form>
            </div>
            <div class="settings-group">
              <h3 class="settings-group-title">Localization</h3>
              <div class="settings-form-grid">
                <a-form-item label="Default Language">
                  <a-select v-model:value="generalSettings.language" style="width:100%">
                    <a-select-option value="english">English</a-select-option>
                    <a-select-option value="spanish">Spanish</a-select-option>
                    <a-select-option value="french">French</a-select-option>
                    <a-select-option value="german">German</a-select-option>
                  </a-select>
                </a-form-item>
                <a-form-item label="Timezone">
                  <a-select v-model:value="generalSettings.timezone" style="width:100%">
                    <a-select-option value="UTC">UTC</a-select-option>
                    <a-select-option value="America/New_York">America/New_York</a-select-option>
                    <a-select-option value="Europe/London">Europe/London</a-select-option>
                    <a-select-option value="Asia/Kolkata">Asia/Kolkata</a-select-option>
                  </a-select>
                </a-form-item>
              </div>
            </div>
            <div class="settings-actions">
              <a-button type="primary" @click="saveSettings('general')">Save Settings</a-button>
            </div>
          </div>
        </div>

        <!-- ── HELP ── -->
        <div v-if="activeSection === 'help'">
          <h2 class="section-title">Help & Support</h2>
          <div class="help-grid">
            <div v-for="h in helpItems" :key="h.title" class="help-card">
              <div class="help-icon" v-html="h.icon"></div>
              <div class="help-title">{{ h.title }}</div>
              <div class="help-desc">{{ h.desc }}</div>
              <a :href="h.link" target="_blank" class="help-link">Learn More →</a>
            </div>
          </div>
        </div>

      </div><!-- .setup-content -->
    </div><!-- .setup-layout -->
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'SetupPage',
  setup() {
    const activeSection = ref('staff');

    // ── Section nav ────────────────────────────────
    const sections = [
      { id: 'staff',            label: 'Staff',            icon: iconSvg('users') },
      { id: 'customers',        label: 'Customers',        icon: iconSvg('customer') },
      { id: 'support',          label: 'Support',          icon: iconSvg('support') },
      { id: 'leads',            label: 'Leads',            icon: iconSvg('leads') },
      { id: 'finance',          label: 'Finance',          icon: iconSvg('finance') },
      { id: 'contracts',        label: 'Contracts',        icon: iconSvg('contracts') },
      { id: 'estimate-request', label: 'Estimate Request', icon: iconSvg('estimate') },
      { id: 'modules',          label: 'Modules',          icon: iconSvg('modules') },
      { id: 'email-templates',  label: 'Email Templates',  icon: iconSvg('email') },
      { id: 'custom-fields',    label: 'Custom Fields',    icon: iconSvg('fields') },
      { id: 'gdpr',             label: 'GDPR',             icon: iconSvg('shield') },
      { id: 'roles',            label: 'Roles',            icon: iconSvg('roles') },
      { id: 'menu-setup',       label: 'Menu Setup',       icon: iconSvg('menu') },
      { id: 'theme-style',      label: 'Theme Style',      icon: iconSvg('theme') },
      { id: 'settings',         label: 'Settings',         icon: iconSvg('settings') },
      { id: 'help',             label: 'Help',             icon: iconSvg('help') },
    ];

    // ── Staff ──────────────────────────────────────
    const staffList = ref([]);
    const staffTotal = ref(0);
    const staffLoading = ref(false);
    const staffSearch = ref('');
    const openAddStaff = ref(false);
    const staffSaving = ref(false);
    const staffForm = reactive({ id: null, name: '', email: '', role: 'staff', phone: '', department: '', password: '', password_confirmation: '' });

    const staffColumns = [
      { title: '#',         key: 'id',      dataIndex: 'id',    width: 50 },
      { title: 'Name',      key: 'name',    dataIndex: 'name' },
      { title: 'Role',      key: 'role',    dataIndex: 'role',  width: 120 },
      { title: 'Status',    key: 'active',  dataIndex: 'active',width: 110 },
      { title: 'Joined',    key: 'created_at', dataIndex: 'created_at', width: 150,
        customRender: ({value}) => value ? new Date(value).toLocaleDateString() : '—' },
      { title: 'Actions',   key: 'actions', width: 120 },
    ];

    const loadStaff = async () => {
      staffLoading.value = true;
      try {
        const res = await axios.get('/api/staff', { params: { search: staffSearch.value, per_page: 25 } });
        staffList.value = res.data.staff?.data || [];
        staffTotal.value = res.data.total || 0;
      } catch (e) {
        message.error('Failed to load staff');
      } finally {
        staffLoading.value = false;
      }
    };

    const initials = (name) => (name || '').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();

    const editStaffMember = (record) => {
      Object.assign(staffForm, { ...record, password: '', password_confirmation: '' });
      openAddStaff.value = true;
    };

    const saveStaff = async () => {
      staffSaving.value = true;
      try {
        if (staffForm.id) {
          await axios.put(`/api/staff/${staffForm.id}`, staffForm);
          message.success('Staff member updated');
        } else {
          await axios.post('/api/staff', staffForm);
          message.success('Staff member added');
        }
        openAddStaff.value = false;
        resetStaffForm();
        loadStaff();
      } catch (e) {
        const errors = e.response?.data?.errors;
        if (errors) {
          message.error(Object.values(errors).flat().join(', '));
        } else {
          message.error('Failed to save staff member');
        }
      } finally {
        staffSaving.value = false;
      }
    };

    const deleteStaff = async (id) => {
      try {
        await axios.delete(`/api/staff/${id}`);
        message.success('Staff member deleted');
        loadStaff();
      } catch (e) {
        message.error('Failed to delete staff member');
      }
    };

    const resetStaffForm = () => {
      Object.assign(staffForm, { id: null, name: '', email: '', role: 'staff', phone: '', department: '', password: '', password_confirmation: '' });
    };

    // ── Settings data ──────────────────────────────
    const customerSettings = reactive({ allow_registration: true, email_confirmation: false, allow_login: true, groups: ['VIP', 'Wholesaler'] });
    const supportSettings   = reactive({ allow_non_customer_tickets: true, auto_close_days: 7, default_priority: 'medium' });
    const leadsSettings     = reactive({ contact_form: true, auto_assign: false });
    const financeSettings   = reactive({ currency: 'USD', tax_rate: 10, invoice_prefix: 'INV-', estimate_prefix: 'EST-', theme: 'blue' });
    const gdprSettings      = reactive({ enabled: false, show_notice: true, allow_data_download: true });
    const estimateRequestSettings = reactive({ enabled: true, notify_staff: true });

    const generalSettings = reactive({
      company_name: 'My Company', company_phone: '', company_email: '',
      company_website: '', address: '', city: '', country: '', language: 'english', timezone: 'UTC',
    });

    const departments = ref(['General Support', 'Technical', 'Billing']);
    const newDept = ref('');
    const addDept = () => { if (newDept.value.trim()) { departments.value.push(newDept.value.trim()); newDept.value = ''; } };
    const removeDept = (d) => { departments.value = departments.value.filter(x => x !== d); };

    const contractTypes = ref(['Ongoing', 'One-time', 'Retainer', 'Custom']);
    const newContractType = ref('');
    const addContractType = () => { if (newContractType.value.trim()) { contractTypes.value.push(newContractType.value.trim()); newContractType.value = ''; } };
    const removeContractType = (t) => { contractTypes.value = contractTypes.value.filter(x => x !== t); };

    const leadStatuses = ref([
      { id: 1, name: 'New',           color: '#6366f1' },
      { id: 2, name: 'Contacted',     color: '#0ea5e9' },
      { id: 3, name: 'Working',       color: '#f59e0b' },
      { id: 4, name: 'Qualified',     color: '#10b981' },
      { id: 5, name: 'Proposal Sent', color: '#8b5cf6' },
      { id: 6, name: 'Customer',      color: '#22c55e' },
      { id: 7, name: 'Lost',          color: '#ef4444' },
    ]);

    const paymentMethods = ref([
      { key: 'stripe',   name: 'Stripe',    active: true  },
      { key: 'paypal',   name: 'PayPal',    active: false },
      { key: 'bank',     name: 'Bank Transfer', active: true },
      { key: 'mollie',   name: 'Mollie',    active: false },
      { key: 'offline',  name: 'Offline',   active: true  },
    ]);

    // ── Modules (exact Perfex CRM demo data) ──
    const modules = ref([
      {
        key: 'database-backup', name: 'Database Backup',
        description: 'Default module to perform database backup',
        version: '2.3.0', active: true,
        actions: ['Deactivate', 'Database Backup'],
      },
      {
        key: 'einvoice', name: 'e-Invoice',
        description: 'Default module for e-Invoice',
        version: '1.0.0', active: true,
        actions: ['Deactivate', 'Settings'],
      },
      {
        key: 'csv-export', name: 'CSV Export Manager',
        description: 'Default module for Exporting data in CSV',
        version: '1.0.0', active: true,
        actions: ['Deactivate'],
      },
      {
        key: 'goals', name: 'Goals',
        description: 'Default module for defining goals',
        version: '2.3.0', active: true,
        actions: ['Deactivate'],
      },
      {
        key: 'menu-setup', name: 'Menu Setup',
        description: 'Default module to apply changes to the menus',
        version: '2.3.0', active: true,
        actions: ['Deactivate', 'Main Menu', 'Setup Menu'],
      },
      {
        key: 'openai', name: 'OpenAi Integration',
        description: 'Default module for Open AI integration',
        version: '1.0.0', active: true,
        actions: ['Deactivate', 'Settings', 'AI Integration'],
      },
      {
        key: 'surveys', name: 'Surveys',
        description: 'Default module for sending surveys',
        version: '2.3.0', active: true,
        actions: ['Deactivate'],
      },
      {
        key: 'theme-style', name: 'Theme Style',
        description: 'Default module to apply additional CSS styles',
        version: '2.3.0', active: true,
        actions: ['Deactivate', 'Settings'],
      },
    ]);

    // Module upload & search
    const modSearch    = ref('');
    const modPerPage   = ref(25);
    const moduleFileName = ref('');

    const filteredModules = computed(() => {
      if (!modSearch.value.trim()) return modules.value;
      const q = modSearch.value.toLowerCase();
      return modules.value.filter(m =>
        m.name.toLowerCase().includes(q) || m.description.toLowerCase().includes(q)
      );
    });

    const onModuleFileChange = (e) => {
      const file = e.target.files[0];
      moduleFileName.value = file ? file.name : '';
    };

    const uploadModule = () => {
      message.info(`Uploading ${moduleFileName.value}... (demo — no actual upload)`);
    };

    const handleModuleAction = (mod, action) => {
      if (action === 'Deactivate') {
        mod.active = false;
        message.success(`${mod.name} deactivated`);
      } else if (action === 'Activate') {
        mod.active = true;
        message.success(`${mod.name} activated`);
      } else {
        message.info(`${action} — ${mod.name}`);
      }
    };

    const emailTemplates = ref([
      { id: 1, name: 'New Invoice',            subject: 'Invoice #{id} from {company_name}', active: true },
      { id: 2, name: 'Invoice Overdue',        subject: 'Invoice #{id} is past due',         active: true },
      { id: 3, name: 'Payment Received',       subject: 'Payment received - Thank you!',     active: true },
      { id: 4, name: 'New Estimate',           subject: 'Estimate #{id} from {company_name}',active: true },
      { id: 5, name: 'Estimate Accepted',      subject: 'Your estimate has been accepted',   active: true },
      { id: 6, name: 'New Support Ticket',     subject: 'Support Ticket #{id} Created',      active: true },
      { id: 7, name: 'Ticket Reply',           subject: 'Reply to your support ticket #{id}',active: true },
      { id: 8, name: 'Lead Assigned',          subject: 'New lead assigned to you',          active: false },
      { id: 9, name: 'Staff Welcome',          subject: 'Welcome to {company_name}',         active: true },
    ]);

    const templateColumns = [
      { title: 'Template Name', key: 'name',    dataIndex: 'name' },
      { title: 'Subject',       key: 'subject', dataIndex: 'subject' },
      { title: 'Active',        key: 'active',  dataIndex: 'active', width: 80 },
      { title: '',              key: 'actions', width: 80 },
    ];

    const roles = ref([
      { name: 'Administrator', description: 'Full access to all features and settings',         count: 1 },
      { name: 'Manager',       description: 'Can manage clients, projects, leads and reports',  count: 0 },
      { name: 'Staff',         description: 'Standard staff access — tasks, time logs, tickets',count: 2 },
    ]);

    const menuSetupItems = ref([
      { key: 'dashboard',   label: 'Dashboard',        visible: true },
      { key: 'customers',   label: 'Customers',        visible: true },
      { key: 'sales',       label: 'Sales',            visible: true },
      { key: 'subscriptions',label: 'Subscriptions',   visible: true },
      { key: 'expenses',    label: 'Expenses',         visible: true },
      { key: 'contracts',   label: 'Contracts',        visible: true },
      { key: 'projects',    label: 'Projects',         visible: true },
      { key: 'tasks',       label: 'Tasks',            visible: true },
      { key: 'support',     label: 'Support',          visible: true },
      { key: 'leads',       label: 'Leads',            visible: true },
      { key: 'kb',          label: 'Knowledge Base',   visible: false },
      { key: 'utilities',   label: 'Utilities',        visible: true },
      { key: 'reports',     label: 'Reports',          visible: true },
      { key: 'setup',       label: 'Setup',            visible: true },
    ]);

    const themes = [
      { value: 'blue',   label: 'Blue',   color: '#0d6efd' },
      { value: 'indigo', label: 'Indigo', color: '#6366f1' },
      { value: 'green',  label: 'Green',  color: '#22c55e' },
      { value: 'teal',   label: 'Teal',   color: '#14b8a6' },
      { value: 'orange', label: 'Orange', color: '#f97316' },
      { value: 'red',    label: 'Red',    color: '#ef4444' },
      { value: 'dark',   label: 'Dark',   color: '#1e293b' },
    ];

    const helpItems = [
      { title: 'Documentation', desc: 'Read the full documentation for Perfex CRM setup and usage guides.', link: '#', icon: iconSvg('help') },
      { title: 'Video Tutorials', desc: 'Watch step-by-step video tutorials for key features.', link: '#', icon: iconSvg('theme') },
      { title: 'Support Forum', desc: 'Join the community forum to get help from other users and developers.', link: '#', icon: iconSvg('support') },
      { title: 'Submit Ticket', desc: 'Open a support ticket for technical issues or feature requests.', link: '#', icon: iconSvg('email') },
    ];

    const saveSettings = (section) => {
      message.success(`${section.charAt(0).toUpperCase() + section.slice(1)} settings saved`);
    };

    onMounted(() => {
      loadStaff();
    });

    return {
      activeSection, sections,
      staffList, staffTotal, staffLoading, staffSearch, staffColumns,
      openAddStaff, staffForm, staffSaving,
      loadStaff, editStaffMember, saveStaff, deleteStaff, resetStaffForm, initials,
      customerSettings, supportSettings, leadsSettings, financeSettings, gdprSettings,
      estimateRequestSettings, generalSettings,
      departments, newDept, addDept, removeDept,
      contractTypes, newContractType, addContractType, removeContractType,
      leadStatuses, paymentMethods, modules, filteredModules,
      modSearch, modPerPage, moduleFileName, onModuleFileChange, uploadModule, handleModuleAction,
      emailTemplates, templateColumns,
      roles, menuSetupItems, themes, helpItems,
      saveSettings,
    };
  }
});

function iconSvg(type) {
  const icons = {
    users:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>`,
    customer:  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`,
    support:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
    leads:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>`,
    finance:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>`,
    contracts: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>`,
    estimate:  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>`,
    modules:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>`,
    email:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>`,
    fields:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>`,
    shield:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>`,
    roles:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="23 11 21 13 19 11"/></svg>`,
    menu:      `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>`,
    theme:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>`,
    settings:  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>`,
    help:      `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>`,
  };
  return icons[type] || icons.settings;
}
</script>

<style scoped>
/* ── Page Layout ──────────────────────────────────────── */
.setup-page {
  font-family: 'Inter', -apple-system, sans-serif;
  font-size: 13.5px;
  color: #334155;
}
.setup-header {
  margin-bottom: 20px;
}
.setup-title {
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.setup-layout {
  display: flex;
  gap: 0;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
  min-height: 600px;
}

/* ── Setup Sidebar ────────────────────────────────────── */
.setup-sidebar {
  width: 200px;
  min-width: 200px;
  border-right: 1px solid #e2e8f0;
  background: #f8fafc;
}
.setup-nav {
  padding: 8px 0;
}
.setup-nav-item {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 8px 14px;
  font-size: 13px;
  color: #475569;
  cursor: pointer;
  border-left: 3px solid transparent;
  text-decoration: none;
  transition: background 0.12s, color 0.12s;
  user-select: none;
}
.setup-nav-item:hover {
  background: #f1f5f9;
  color: #1e293b;
}
.setup-nav-item--active {
  background: #fff;
  color: #0d6efd;
  font-weight: 600;
  border-left-color: #0d6efd;
}
.setup-nav-icon {
  width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  flex-shrink: 0;
}
.setup-nav-icon :deep(svg) {
  width: 16px;
  height: 16px;
}

/* ── Right Content ────────────────────────────────────── */
.setup-content {
  flex: 1;
  padding: 24px;
  overflow-y: auto;
}

.section-title {
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 16px;
}

.section-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}
.section-toolbar .section-title { margin: 0; }

/* ── Buttons ──────────────────────────────────────────── */
.btn-primary {
  background: #0d6efd;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 7px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  font-family: inherit;
  transition: background 0.12s;
}
.btn-primary:hover { background: #0b5ed7; }

/* ── Table ────────────────────────────────────────────── */
.data-table-wrap { }
.data-table-controls {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 12px;
}

.flex-name {
  display: flex;
  align-items: center;
  gap: 10px;
}
.avatar-circle {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #e0e7ff;
  color: #4338ca;
  font-size: 11px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.name-main { font-size: 13px; font-weight: 600; color: #1e293b; }
.name-sub  { font-size: 11px; color: #94a3b8; }
.row-actions { display: flex; gap: 2px; }

/* ── Drawer Footer ────────────────────────────────────── */
.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
}

/* ── Settings Card ────────────────────────────────────── */
.settings-card {
  background: #fff;
}
.settings-group {
  margin-bottom: 28px;
}
.settings-group-title {
  font-size: 13px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid #f1f5f9;
}
.settings-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #f8fafc;
  gap: 20px;
}
.settings-label {
  flex: 1;
}
.settings-label span {
  font-size: 13px;
  font-weight: 500;
  color: #334155;
}
.settings-hint {
  font-size: 11.5px;
  color: #94a3b8;
  margin: 2px 0 0;
}
.settings-hint-block {
  font-size: 12.5px;
  color: #64748b;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 10px 14px;
  margin-bottom: 12px;
}
.settings-actions {
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
}
.settings-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0 20px;
}

/* ── Departments ──────────────────────────────────────── */
.dept-list, .type-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
  margin-top: 8px;
}
.dept-item, .type-item {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  padding: 4px 10px;
  font-size: 12px;
  color: #334155;
}
.dept-remove {
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  font-size: 14px;
  line-height: 1;
  padding: 0 2px;
}
.dept-remove:hover { color: #ef4444; }

/* ── Lead Statuses ────────────────────────────────────── */
.status-list { display: flex; flex-direction: column; gap: 8px; margin-top: 8px; }
.status-item { display: flex; align-items: center; gap: 8px; font-size: 13px; }
.status-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

/* ── Payment Methods ──────────────────────────────────── */
.payment-methods { display: flex; flex-direction: column; gap: 10px; margin-top: 8px; }
.payment-method-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
}
.pm-info { display: flex; align-items: center; gap: 10px; }
.pm-name { font-weight: 500; font-size: 13px; color: #1e293b; }
.pm-badge { font-size: 11px; padding: 2px 7px; border-radius: 3px; background: #f1f5f9; color: #94a3b8; }
.pm-badge--active { background: #dcfce7; color: #16a34a; }

/* ── Modules Grid ─────────────────────────────────────── */
.modules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 12px;
}
.module-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
}
.module-icon {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  color: #6366f1;
  flex-shrink: 0;
}
.module-icon :deep(svg) { width: 20px; height: 20px; }
.module-info { flex: 1; }
.module-name { font-size: 13px; font-weight: 600; color: #1e293b; }
.module-desc { font-size: 11.5px; color: #94a3b8; margin-top: 2px; }

/* ── Email Templates ──────────────────────────────────── */
.link-blue { color: #0d6efd; cursor: pointer; }
.text-muted { color: #64748b; }

/* ── Roles ────────────────────────────────────────────── */
.roles-list { display: flex; flex-direction: column; gap: 10px; }
.role-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 14px 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
}
.role-info { flex: 1; }
.role-name { font-size: 13px; font-weight: 600; color: #1e293b; }
.role-desc { font-size: 12px; color: #64748b; margin-top: 2px; }
.role-count { font-size: 12px; color: #94a3b8; white-space: nowrap; }
.role-actions { }

/* ── Menu Setup ───────────────────────────────────────── */
.menu-items-list { display: flex; flex-direction: column; gap: 2px; }
.menu-setup-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  border-radius: 6px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
}
.drag-handle { color: #cbd5e1; font-size: 16px; cursor: grab; }
.menu-item-label { flex: 1; font-size: 13px; color: #334155; }

/* ── Theme ────────────────────────────────────────────── */
.theme-swatches {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 10px;
}
.theme-swatch {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  cursor: pointer;
  border: 3px solid transparent;
  transition: transform 0.12s, border-color 0.12s;
}
.theme-swatch:hover { transform: scale(1.15); }
.theme-swatch--active { border-color: #1e293b; transform: scale(1.15); }

/* ── Help ─────────────────────────────────────────────── */
.help-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 16px;
}
.help-card {
  padding: 20px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.help-icon {
  width: 28px;
  height: 28px;
  color: #0d6efd;
}
.help-icon :deep(svg) { width: 28px; height: 28px; }
.help-title { font-size: 14px; font-weight: 700; color: #1e293b; }
.help-desc { font-size: 12.5px; color: #64748b; flex: 1; }
.help-link { font-size: 12px; color: #0d6efd; text-decoration: none; font-weight: 600; }
.help-link:hover { text-decoration: underline; }
/* ── Module Upload Card ─────────────────────────────────────── */
.mod-upload-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 18px 20px;
  margin-bottom: 20px;
}
.mod-upload-title {
  font-size: 14px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 6px;
}
.mod-upload-desc {
  font-size: 12.5px;
  color: #64748b;
  margin-bottom: 12px;
}
.mod-upload-desc code {
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 3px;
  padding: 1px 5px;
  font-family: monospace;
  font-size: 12px;
  color: #334155;
}
.mod-upload-row {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}
.mod-file-label {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 5px;
  padding: 6px 14px;
  font-size: 12.5px;
  color: #475569;
  cursor: pointer;
  transition: background 0.12s;
}
.mod-file-label:hover { background: #f1f5f9; }
.btn-sm { padding: 5px 14px; font-size: 12.5px; }

/* Module Table Controls */
.mod-table-controls {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}
.mod-table-left, .mod-table-right {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Module Table */
.mod-table-wrap {
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
}
.mod-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
  background: #fff;
}
.mod-table thead tr {
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
}
.mod-table th {
  padding: 10px 16px;
  text-align: left;
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
}
.mod-table th:first-child { width: 260px; }
.mod-table tbody tr {
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.1s;
}
.mod-table tbody tr:last-child { border-bottom: none; }
.mod-table tbody tr:hover { background: #f8fafc; }
.mod-table td {
  padding: 12px 16px;
  vertical-align: top;
}
.mod-name-cell { width: 260px; }
.mod-name-link {
  font-size: 13.5px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 5px;
}
.mod-actions-row {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-wrap: wrap;
}
.mod-action-link {
  font-size: 12px;
  color: #0d6efd;
  cursor: pointer;
  text-decoration: none;
  transition: color 0.12s;
}
.mod-action-link:hover { color: #0b5ed7; text-decoration: underline; }
.mod-action-link--deactivate { color: #ef4444; }
.mod-action-link--deactivate:hover { color: #dc2626; }
.mod-action-sep {
  color: #94a3b8;
  font-size: 11px;
  padding: 0 1px;
}
.mod-desc-cell {}
.mod-desc-text {
  font-size: 13px;
  color: #475569;
  line-height: 1.5;
}
.mod-version {
  font-size: 11.5px;
  color: #94a3b8;
  margin-top: 4px;
  font-style: italic;
}
.mod-table-footer {
  font-size: 12px;
  color: #64748b;
  margin-top: 12px;
  padding: 6px 0;
}
</style>
