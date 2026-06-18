<template>
  <div>
    <!-- HEADER & TOOLBAR -->
    <div class="section-toolbar">
      <h2 class="section-title">Settings</h2>
    </div>

    <!-- MAIN SETTINGS INTERFACE -->
    <div class="settings-layout-wrap">
      
      <!-- Left Settings Navigation Sidebar -->
      <div class="settings-nav-sidebar">
        <div v-for="grp in groupedCategories" :key="grp.group" class="nav-group-container">
          <div class="nav-group-header">{{ grp.group }}</div>
          <div class="nav-group-items">
            <div
              v-for="item in grp.items"
              :key="item.key"
              :class="['settings-nav-item', { 'settings-nav-item--active': activeCategory === item.key }]"
              @click="activeCategory = item.key"
            >
              <span class="nav-label">{{ item.label }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Settings Content Panel -->
      <div class="settings-content-panel">
        <a-form layout="vertical">
          
          <!-- ── GROUP 1: GENERAL ── -->
          <!-- General -->
          <div v-if="activeCategory === 'general'">
            <h3 class="panel-section-title">General Settings</h3>
            <div class="settings-form-grid">
              <a-form-item label="Company Name">
                <a-input v-model:value="settings.site_name" />
              </a-form-item>
              <a-form-item label="Company Main Domain">
                <a-input v-model:value="settings.company_domain" />
              </a-form-item>
              <a-form-item label="RTL Admin Area (Right to Left)">
                <a-radio-group v-model:value="settings.rtl_admin" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </a-form-item>
              <a-form-item label="RTL Customers Area (Right to Left)">
                <a-radio-group v-model:value="settings.rtl_cust" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </a-form-item>
              <a-form-item label="Allowed file types" style="grid-column: span 2;">
                <a-input v-model:value="settings.allowed_file_types" />
              </a-form-item>
            </div>
          </div>

          <!-- Company Information -->
          <div v-else-if="activeCategory === 'company'">
            <h3 class="panel-section-title">Company Information</h3>
            <p class="settings-hint-text">These information will be displayed on invoices/estimates/payments and other PDF documents where company info is required</p>
            <div class="settings-form-grid">
              <a-form-item label="Company Name">
                <a-input v-model:value="settings.company_info_name" />
              </a-form-item>
              <a-form-item label="Address" style="grid-column: span 2;">
                <a-input v-model:value="settings.company_info_address" />
              </a-form-item>
              <a-form-item label="City">
                <a-input v-model:value="settings.company_info_city" />
              </a-form-item>
              <a-form-item label="State">
                <a-input v-model:value="settings.company_info_state" />
              </a-form-item>
              <a-form-item label="Country Code">
                <a-select v-model:value="settings.company_info_country" style="width: 100%">
                  <a-select-option value="CA">Canada [CA]</a-select-option>
                  <a-select-option value="US">United States [US]</a-select-option>
                  <a-select-option value="DE">Germany [DE]</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Zip Code">
                <a-input v-model:value="settings.company_info_zip" />
              </a-form-item>
              <a-form-item label="Phone">
                <a-input v-model:value="settings.company_info_phone" />
              </a-form-item>
              <a-form-item label="VAT Number">
                <a-input v-model:value="settings.company_info_vat" />
              </a-form-item>
              <a-form-item label="Company Information Format (PDF and HTML)" style="grid-column: span 2;">
                <a-textarea v-model:value="settings.company_info_format" :rows="6" />
                <div class="merge-fields-block" style="margin-top:8px; font-size:12px; color:#64748b; line-height: 1.4;">
                  Available merge fields:<br/>
                  <code>{company_name} {address}, {city}, {state}, {zip_code}, {country_code}, {phone}, {vat_number}, {vat_number_with_label}</code>
                </div>
              </a-form-item>
            </div>
          </div>

          <!-- Localization -->
          <div v-else-if="activeCategory === 'localization'">
            <h3 class="panel-section-title">Localization</h3>
            <div class="settings-form-grid">
              <a-form-item label="Date Format">
                <a-select v-model:value="settings.date_format" style="width: 100%">
                  <a-select-option value="Y-m-d">Y-m-d</a-select-option>
                  <a-select-option value="d/m/Y">d/m/Y</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Time Format">
                <a-select v-model:value="settings.time_format" style="width: 100%">
                  <a-select-option value="24">24 hours</a-select-option>
                  <a-select-option value="12">12 hours (AM/PM)</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Default Timezone">
                <a-select v-model:value="settings.timezone" style="width: 100%">
                  <a-select-option value="Europe/Berlin">Europe/Berlin</a-select-option>
                  <a-select-option value="America/New_York">America/New_York</a-select-option>
                  <a-select-option value="Asia/Kolkata">Asia/Kolkata</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Default Language">
                <a-select v-model:value="settings.language" style="width: 100%">
                  <a-select-option value="english">English</a-select-option>
                  <a-select-option value="french">French</a-select-option>
                </a-select>
              </a-form-item>
            </div>

            <!-- Enabled Languages -->
            <div class="language-selection-block" style="margin-top:20px;">
              <span class="sub-label" style="font-weight:600; font-size:13px; color:#334155;">Enabled Languages</span>
              <div class="languages-grid-card">
                <a-checkbox-group v-model:value="settings.enabled_languages" style="width: 100%">
                  <div class="languages-grid-cols">
                    <a-checkbox v-for="lang in availableLanguages" :key="lang" :value="lang">{{ lang }}</a-checkbox>
                  </div>
                </a-checkbox-group>
              </div>
              
              <div class="settings-form-grid" style="margin-top: 16px;">
                <a-form-item label="Disable Languages">
                  <a-radio-group v-model:value="settings.disable_languages" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </a-form-item>
                <a-form-item label="Output client PDF documents from admin area in client language">
                  <a-radio-group v-model:value="settings.pdf_in_client_lang" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </a-form-item>
              </div>
            </div>
          </div>

          <!-- Email -->
          <div v-else-if="activeCategory === 'email'">
            <h3 class="panel-section-title">Email Settings</h3>
            
            <a-tabs v-model:activeKey="settings.email_tab" class="email-sub-tabs">
              <!-- Tab 1: SMTP Settings -->
              <a-tab-pane key="smtp" tab="SMTP Settings">
                <div style="margin-top: 16px;">
                  <h4 class="settings-sub-section-title">SMTP Settings Setup main email</h4>
                  
                  <div class="settings-form-grid">
                    <a-form-item label="Mail Engine">
                      <a-radio-group v-model:value="settings.mail_engine" button-style="solid">
                        <a-radio-button value="PHPMailer">PHPMailer</a-radio-button>
                        <a-radio-button value="CodeIgniter">CodeIgniter</a-radio-button>
                      </a-radio-group>
                    </a-form-item>
                    <a-form-item label="Email Protocol">
                      <a-select v-model:value="settings.email_protocol" style="width: 100%">
                        <a-select-option value="SMTP">SMTP</a-select-option>
                        <a-select-option value="Microsoft OAuth 2.0">Microsoft OAuth 2.0</a-select-option>
                        <a-select-option value="Gmail OAuth 2.0">Gmail OAuth 2.0</a-select-option>
                        <a-select-option value="Sendmail">Sendmail</a-select-option>
                        <a-select-option value="Mail">Mail</a-select-option>
                      </a-select>
                    </a-form-item>
                    <a-form-item label="Email Encryption">
                      <a-select v-model:value="settings.email_encryption" style="width: 100%">
                        <a-select-option value="None">None</a-select-option>
                        <a-select-option value="SSL">SSL</a-select-option>
                        <a-select-option value="TLS">TLS</a-select-option>
                      </a-select>
                    </a-form-item>
                    <a-form-item label="SMTP Host">
                      <a-input v-model:value="settings.smtp_host" />
                    </a-form-item>
                    <a-form-item label="SMTP Port">
                      <a-input-number v-model:value="settings.smtp_port" style="width:100%" />
                    </a-form-item>
                    <a-form-item label="Email">
                      <a-input v-model:value="settings.from_email" />
                    </a-form-item>
                    <a-form-item label="SMTP Username">
                      <a-input v-model:value="settings.smtp_user" />
                    </a-form-item>
                    <a-form-item label="SMTP Password">
                      <a-input v-model:value="settings.smtp_pass" type="password" />
                    </a-form-item>
                    <a-form-item label="Email Charset">
                      <a-input v-model:value="settings.email_charset" />
                    </a-form-item>
                    <a-form-item label="BCC All Emails To">
                      <a-input v-model:value="settings.bcc_emails" />
                    </a-form-item>
                  </div>
                  
                  <a-form-item label="Email Signature" style="margin-top:16px;">
                    <a-textarea v-model:value="settings.email_signature" :rows="3" />
                  </a-form-item>
                  
                  <a-form-item label="Predefined Header" style="margin-top:16px;">
                    <a-textarea v-model:value="settings.email_predefined_header" :rows="8" class="code-textarea" />
                  </a-form-item>
                  
                  <a-form-item label="Predefined Footer" style="margin-top:16px;">
                    <a-textarea v-model:value="settings.email_predefined_footer" :rows="8" class="code-textarea" />
                  </a-form-item>

                  <!-- Send Test Email -->
                  <div class="test-email-card">
                    <h5 class="test-email-title">Send Test Email</h5>
                    <p class="test-email-desc">Send test email to make sure that your SMTP settings is set correctly.</p>
                    <div class="test-email-controls">
                      <a-form-item label="Email Address" style="margin-bottom:0; flex:1;">
                        <a-input v-model:value="settings.test_email_address" placeholder="Enter recipient email..." />
                      </a-form-item>
                      <a-button type="default" @click="sendTestEmail">Send Test Email</a-button>
                    </div>
                  </div>
                </div>
              </a-tab-pane>

              <!-- Tab 2: Email Queue -->
              <a-tab-pane key="queue" tab="Email Queue">
                <div style="margin-top: 16px; display:flex; flex-direction:column; gap:16px;">
                  <div class="switch-row">
                    <div class="switch-labels">
                      <span class="switch-title">Enable Email Queue</span>
                    </div>
                    <a-radio-group v-model:value="settings.enable_queue" button-style="solid">
                      <a-radio-button :value="true">Yes</a-radio-button>
                      <a-radio-button :value="false">No</a-radio-button>
                    </a-radio-group>
                  </div>

                  <div class="switch-row">
                    <div class="switch-labels">
                      <span class="switch-title">Do not add emails with attachments in the queue?</span>
                    </div>
                    <a-radio-group v-model:value="settings.queue_no_attachments" button-style="solid">
                      <a-radio-button :value="true">Yes</a-radio-button>
                      <a-radio-button :value="false">No</a-radio-button>
                    </a-radio-group>
                  </div>

                  <!-- Queue Table -->
                  <div class="queue-table-section" style="margin-top:16px;">
                    <h5 style="margin-bottom:12px; font-weight:700;">Email Queue</h5>
                    <div class="data-table-controls">
                      <div class="page-size-select">
                        <a-select v-model:value="queuePageSize" size="small" style="width: 70px">
                          <a-select-option :value="10">10</a-select-option>
                          <a-select-option :value="25">25</a-select-option>
                        </a-select>
                      </div>
                      <a-input-search
                        v-model:value="queueSearch"
                        placeholder="Search..."
                        style="width: 200px"
                        size="small"
                      />
                    </div>
                    <a-table
                      :dataSource="filteredQueue"
                      :columns="queueColumns"
                      size="small"
                      row-key="id"
                      :pagination="{ pageSize: queuePageSize }"
                    >
                    </a-table>
                  </div>
                </div>
              </a-tab-pane>
            </a-tabs>
          </div>

          <!-- System Update -->
          <div v-else-if="activeCategory === 'update'">
            <h3 class="panel-section-title">System Update</h3>
            <div style="display:flex; flex-direction:column; gap:20px;">
              <a-form-item label="Purchase Key">
                <a-input v-model:value="settings.license_key" type="password" placeholder="Enter Envato Purchase Key" />
              </a-form-item>
              
              <div class="version-block" style="display:flex; gap:48px;">
                <div>
                  <span style="color:#64748b; font-size:12px; text-transform:uppercase; font-weight:700;">Your Version</span>
                  <h3 style="margin:4px 0 0 0; font-size:22px; font-weight:700; color:#0f172a;">3.4.0</h3>
                </div>
                <div>
                  <span style="color:#64748b; font-size:12px; text-transform:uppercase; font-weight:700;">Latest Version</span>
                  <h3 style="margin:4px 0 0 0; font-size:22px; font-weight:700; color:#ef4444;">3.4.1</h3>
                </div>
              </div>

              <div style="background:#fff7ed; border:1px solid #ffedd5; border-radius:8px; padding:16px; color:#c2410c; font-size:13px; line-height:1.5;">
                Before performing an update, it is strongly recommended to create a full backup of your current installation (files and database) and review the changelog.
              </div>

              <div style="color:#10b981; font-weight:700; font-size:14px; display:flex; align-items:center; gap:8px;">
                <span class="pulse-indicator"></span> An update is available
              </div>

              <a-form-item label="Upgrade Function">
                <a-radio-group v-model:value="settings.upgrade_function" button-style="solid">
                  <a-radio-button value="new">New (from v2.3.2)</a-radio-button>
                  <a-radio-button value="old">Old (prior to v2.3.2)</a-radio-button>
                </a-radio-group>
              </a-form-item>
            </div>
          </div>

          <!-- System/Server Info -->
          <div v-else-if="activeCategory === 'system_info'">
            <h3 class="panel-section-title">System/Server Info</h3>
            <table class="info-table">
              <tr><td>PHP Version</td><td><strong>8.2.16</strong></td></tr>
              <tr><td>Memory Limit</td><td>512M</td></tr>
              <tr><td>Max Upload Size</td><td>64M</td></tr>
              <tr><td>Database Version</td><td>MySQL 8.0.35</td></tr>
              <tr><td>OS</td><td>Darwin / MacOS</td></tr>
            </table>
          </div>


          <!-- ── GROUP 2: FINANCE ── -->
          <!-- Finance General -->
          <div v-else-if="activeCategory === 'finance_general'">
            <h3 class="panel-section-title">Finance General Settings</h3>
            
            <div class="settings-form-grid">
              <a-form-item label="Decimal Separator">
                <a-input v-model:value="settings.finance_decimal_separator" style="width: 80px; text-align: center;" />
              </a-form-item>
              
              <a-form-item label="Thousand Separator">
                <a-input v-model:value="settings.finance_thousand_separator" style="width: 80px; text-align: center;" />
              </a-form-item>

              <a-form-item label="Number padding zero's for prefix formats" style="grid-column: span 2;">
                <template #extra>eq. If this value is 3 the number will be formatted: 005 or 025</template>
                <a-input-number v-model:value="settings.finance_number_padding" :min="1" style="width: 120px" />
              </a-form-item>
            </div>

            <div class="switches-grid" style="margin-top: 20px;">
              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Automatically assign logged in staff as sale agent</span>
                </div>
                <a-radio-group v-model:value="settings.finance_auto_sale_agent" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Show TAX per item</span>
                </div>
                <a-radio-group v-model:value="settings.finance_show_tax_per_item" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Remove the tax name from item table row</span>
                </div>
                <a-radio-group v-model:value="settings.finance_remove_tax_name" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Exclude currency symbol from items table Amount</span>
                </div>
                <a-radio-group v-model:value="settings.finance_exclude_currency_symbol" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
            </div>

            <div class="settings-form-grid" style="margin-top: 20px;">
              <a-form-item label="Default Tax">
                <a-select v-model:value="settings.finance_default_tax" style="width: 100%">
                  <a-select-option value="5.00">5.00%</a-select-option>
                  <a-select-option value="10.00">10.00%</a-select-option>
                  <a-select-option value="18.00">18.00%</a-select-option>
                </a-select>
              </a-form-item>
            </div>

            <div class="switches-grid" style="margin-top: 20px;">
              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Remove decimals on numbers/money with zero decimals (2.00 will become 2, 2.25 will stay 2.25)</span>
                </div>
                <a-radio-group v-model:value="settings.finance_remove_decimals_zero" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
            </div>

            <!-- Amount to Words -->
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-top:24px;">
              <h5 style="font-weight:700; margin: 0 0 4px 0; font-size:13.5px; color:#1e293b;">Amount to words</h5>
              <p style="font-size:12.5px; color:#64748b; margin-bottom:16px;">Output total amount to words in invoice/estimate/proposal</p>
              
              <div class="switches-grid">
                <div class="switch-row" style="border:none; padding-bottom:0;">
                  <div class="switch-labels">
                    <span class="switch-title" style="font-size:13px;">Enable</span>
                  </div>
                  <a-radio-group v-model:value="settings.finance_amount_to_words_enabled" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>

                <div class="switch-row" style="border:none; padding-bottom:0; margin-top:10px;" v-if="settings.finance_amount_to_words_enabled">
                  <div class="switch-labels">
                    <span class="switch-title" style="font-size:13px;">Number words into lowercase</span>
                  </div>
                  <a-radio-group v-model:value="settings.finance_amount_to_words_lowercase" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>
          </div>

          <!-- Invoices -->
          <div v-else-if="activeCategory === 'invoices'">
            <h3 class="panel-section-title">Invoice Settings</h3>
            <div class="settings-form-grid">
              <a-form-item label="Invoice Number Prefix">
                <a-input v-model:value="settings.invoice_prefix" />
              </a-form-item>
              <a-form-item label="Next Invoice Number">
                <a-input-number v-model:value="settings.next_invoice_number" style="width: 100%" />
              </a-form-item>
              <a-form-item label="Invoice due after (days)">
                <a-input-number v-model:value="settings.invoice_due_days" style="width: 100%" />
              </a-form-item>
            </div>

            <div class="switches-grid" style="margin-top: 20px;">
              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Allow staff members to view invoices where they are assigned to</span>
                </div>
                <a-radio-group v-model:value="settings.invoice_staff_view" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Require client to be logged in to view invoice</span>
                </div>
                <a-radio-group v-model:value="settings.invoice_require_login" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Delete invoice allowed only on last invoice</span>
                </div>
                <a-radio-group v-model:value="settings.invoice_delete_last_only" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Decrement invoice number on delete</span>
                </div>
                <a-radio-group v-model:value="settings.invoice_decrement_on_delete" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Exclude invoices with draft status from customers area</span>
                </div>
                <a-radio-group v-model:value="settings.invoice_exclude_draft" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Show Sale Agent On Invoice</span>
                </div>
                <a-radio-group v-model:value="settings.invoice_show_sale_agent" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Show Project Name On Invoice</span>
                </div>
                <a-radio-group v-model:value="settings.invoice_show_project" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Show Total Paid On Invoice</span>
                </div>
                <a-radio-group v-model:value="settings.invoice_show_total_paid" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Show Credits Applied On Invoice</span>
                </div>
                <a-radio-group v-model:value="settings.invoice_show_credits" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Show Amount Due On Invoice</span>
                </div>
                <a-radio-group v-model:value="settings.invoice_show_amount_due" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Attach invoice PDF when sending payment receipt to email</span>
                </div>
                <a-radio-group v-model:value="settings.invoice_attach_pdf_receipt" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
            </div>

            <div class="settings-form-grid" style="margin-top: 20px;">
              <a-form-item label="Invoice Number Format" style="grid-column: span 2;">
                <a-select v-model:value="settings.invoice_number_format" style="width: 100%">
                  <a-select-option value="number_based">Number Based (000001)</a-select-option>
                  <a-select-option value="year_based">Year Based (YYYY/000001)</a-select-option>
                  <a-select-option value="yy_format">000001-YY</a-select-option>
                  <a-select-option value="date_format">000001/MM/YYYY</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Predefined Client Note" style="grid-column: span 2;">
                <a-textarea v-model:value="settings.invoice_predefined_note" :rows="3" />
              </a-form-item>
              <a-form-item label="Predefined Terms & Conditions" style="grid-column: span 2;">
                <a-textarea v-model:value="settings.invoice_predefined_terms" :rows="3" />
              </a-form-item>
            </div>
          </div>

          <!-- Proposals -->
          <div v-else-if="activeCategory === 'proposals'">
            <h3 class="panel-section-title">Proposal Settings</h3>
            <div class="settings-form-grid">
              <a-form-item label="Proposal Number Prefix">
                <a-input v-model:value="settings.proposal_prefix" />
              </a-form-item>
              <a-form-item label="Proposal Due After (days)">
                <a-input-number v-model:value="settings.proposal_due" style="width: 100%" />
              </a-form-item>
              <a-form-item label="Pipeline limit per status">
                <a-input-number v-model:value="settings.proposal_pipeline_limit" style="width: 100%" />
              </a-form-item>
              <a-form-item label="Default pipeline sort">
                <a-select v-model:value="settings.proposal_pipeline_sort" style="width: 100%">
                  <a-select-option value="pipeline_order">Pipeline Order</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Pipeline Order">
                <a-select v-model:value="settings.proposal_pipeline_order" style="width: 100%">
                  <a-select-option value="ascending">Ascending</a-select-option>
                  <a-select-option value="descending">Descending</a-select-option>
                </a-select>
              </a-form-item>
            </div>

            <div class="switches-grid" style="margin-top: 20px;">
              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Show Project Name On Proposal</span>
                </div>
                <a-radio-group v-model:value="settings.proposal_show_project" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Exclude proposals with draft status from customers area</span>
                </div>
                <a-radio-group v-model:value="settings.proposal_exclude_draft" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Auto convert the proposal to invoice after client accept (only customers related proposals)</span>
                </div>
                <a-radio-group v-model:value="settings.proposal_auto_convert" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Allow staff members to view proposals where they are assigned to</span>
                </div>
                <a-radio-group v-model:value="settings.proposal_staff_view" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
            </div>

            <div class="settings-form-grid" style="margin-top: 20px;">
              <a-form-item label="Proposal Info Format (PDF and HTML)" style="grid-column: span 2;">
                <a-textarea v-model:value="settings.proposal_info_format" :rows="4" />
                <template #extra>{proposal_to}, {address}, {city}, {state}, {zip_code}, {country_code}, {country_name}, {phone}, {email}</template>
              </a-form-item>
            </div>
          </div>

          <!-- Estimates -->
          <div v-else-if="activeCategory === 'estimates'">
            <h3 class="panel-section-title">Estimate Settings</h3>
            <div class="settings-form-grid">
              <a-form-item label="Estimate Number Prefix">
                <a-input v-model:value="settings.estimate_prefix" />
              </a-form-item>
              <a-form-item label="Next estimate Number">
                <a-input-number v-model:value="settings.next_estimate_number" style="width: 100%" />
              </a-form-item>
              <a-form-item label="Estimate Due After (days)">
                <a-input-number v-model:value="settings.estimate_due" style="width: 100%" />
              </a-form-item>
            </div>

            <div class="switches-grid" style="margin-top: 20px;">
              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Delete estimate allowed only on last invoice</span>
                </div>
                <a-radio-group v-model:value="settings.estimate_delete_last_only" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Decrement estimate number on delete</span>
                </div>
                <a-radio-group v-model:value="settings.estimate_decrement_on_delete" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Allow staff members to view estimates where they are assigned to</span>
                </div>
                <a-radio-group v-model:value="settings.estimate_staff_view" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Require client to be logged in to view estimate</span>
                </div>
                <a-radio-group v-model:value="settings.estimate_require_login" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Show Sale Agent On Estimate</span>
                </div>
                <a-radio-group v-model:value="settings.estimate_show_sale_agent" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Show Project Name On Estimate</span>
                </div>
                <a-radio-group v-model:value="settings.estimate_show_project" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Auto convert the estimate to invoice after client accept</span>
                </div>
                <a-radio-group v-model:value="settings.estimate_auto_convert" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Exclude estimates with draft status from customers area</span>
                </div>
                <a-radio-group v-model:value="settings.estimate_exclude_draft" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
            </div>

            <div class="settings-form-grid" style="margin-top: 20px;">
              <a-form-item label="Estimate Number Format" style="grid-column: span 2;">
                <a-select v-model:value="settings.estimate_number_format" style="width: 100%">
                  <a-select-option value="number_based">Number Based (000001)</a-select-option>
                  <a-select-option value="year_based">Year Based (YYYY/000001)</a-select-option>
                  <a-select-option value="yy_format">000001-YY</a-select-option>
                  <a-select-option value="date_format">000001/MM/YYYY</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Pipeline limit per status">
                <a-input-number v-model:value="settings.estimate_pipeline_limit" style="width: 100%" />
              </a-form-item>
              <a-form-item label="Default pipeline sort">
                <a-select v-model:value="settings.estimate_pipeline_sort" style="width: 100%">
                  <a-select-option value="pipeline_order">Pipeline Order</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Pipeline Order">
                <a-select v-model:value="settings.estimate_pipeline_order" style="width: 100%">
                  <a-select-option value="ascending">Ascending</a-select-option>
                  <a-select-option value="descending">Descending</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Predefined Client Note" style="grid-column: span 2;">
                <a-textarea v-model:value="settings.estimate_predefined_note" :rows="3" />
              </a-form-item>
              <a-form-item label="Predefined Terms & Conditions" style="grid-column: span 2;">
                <a-textarea v-model:value="settings.estimate_predefined_terms" :rows="3" />
              </a-form-item>
            </div>
          </div>

          <!-- Credit Notes -->
          <div v-else-if="activeCategory === 'credit_notes'">
            <h3 class="panel-section-title">Credit Notes</h3>
            <div class="settings-form-grid">
              <a-form-item label="Credit Note Number Prefix">
                <a-input v-model:value="settings.credit_prefix" />
              </a-form-item>
              <a-form-item label="Next Credit Note Number">
                <a-input-number v-model:value="settings.next_credit" style="width: 100%" />
              </a-form-item>
              <a-form-item label="Credit Note Number Format">
                <a-select v-model:value="settings.credit_number_format" style="width: 100%">
                  <a-select-option value="number_based">Number Based (000001)</a-select-option>
                  <a-select-option value="year_based">Year Based (YYYY/000001)</a-select-option>
                  <a-select-option value="yy_format">000001-YY</a-select-option>
                  <a-select-option value="date_format">000001/MM/YYYY</a-select-option>
                </a-select>
              </a-form-item>
            </div>

            <div class="switches-grid" style="margin-top: 20px;">
              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Decrement credit note number on delete</span>
                </div>
                <a-radio-group v-model:value="settings.credit_decrement_on_delete" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>

              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Show Project Name On Credit Note</span>
                </div>
                <a-radio-group v-model:value="settings.credit_show_project" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
            </div>

            <div class="settings-form-grid" style="margin-top: 20px;">
              <a-form-item label="Predefined Client Note" style="grid-column: span 2;">
                <a-textarea v-model:value="settings.credit_predefined_note" :rows="3" />
              </a-form-item>
              <a-form-item label="Predefined Terms & Conditions" style="grid-column: span 2;">
                <a-textarea v-model:value="settings.credit_predefined_terms" :rows="3" />
              </a-form-item>
            </div>
          </div>

          <!-- Subscriptions -->
          <div v-else-if="activeCategory === 'subscriptions'">
            <h3 class="panel-section-title">Subscriptions</h3>
            <div class="switches-grid">
              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Show subscriptions in customers area?</span>
                </div>
                <a-radio-group v-model:value="settings.subscription_show_customers" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
            </div>

            <div class="settings-form-grid" style="margin-top: 20px;">
              <a-form-item label="After subscription payment is succeeded" style="grid-column: span 2;">
                <a-select v-model:value="settings.subscription_payment_action" style="width: 100%">
                  <a-select-option value="send_invoice_receipt">Send Invoice and Payment Receipt</a-select-option>
                  <a-select-option value="send_invoice">Send Invoice</a-select-option>
                  <a-select-option value="send_receipt">Send Payment Receipt</a-select-option>
                  <a-select-option value="do_nothing">Do Nothing</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Email Template: Subscription Payment Succeeded" style="grid-column: span 2;">
                <a-select v-model:value="settings.subscription_email_template" style="width: 100%">
                  <a-select-option value="default">Default Template</a-select-option>
                </a-select>
              </a-form-item>
            </div>
          </div>

          <!-- Payment Gateways -->
          <div v-else-if="activeCategory === 'payment_gateways'">
            <h3 class="panel-section-title">Payment Gateways</h3>
            
            <!-- Gateway Tabs -->
            <div style="display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
              <a-button 
                v-for="gw in paymentGateways" 
                :key="gw.key"
                :type="activeGateway === gw.key ? 'primary' : 'default'"
                size="small"
                @click="activeGateway = gw.key"
              >{{ gw.label }}</a-button>
            </div>

            <!-- ── General ── -->
            <div v-if="activeGateway === 'general'">
              <div class="switches-grid">
                <div class="switch-row">
                  <div class="switch-labels">
                    <span class="switch-title">Receive notification when customer pay invoice (built-in)</span>
                  </div>
                  <a-radio-group v-model:value="settings.payment_notify_invoice" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels">
                    <span class="switch-title">Allow customer to modify the amount to pay (for online payments)</span>
                  </div>
                  <a-radio-group v-model:value="settings.payment_allow_modify_amount" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>

            <!-- ── Authorize.net Accept.js ── -->
            <div v-if="activeGateway === 'authorize_net'">
              <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:14px; margin-bottom:20px; font-size:12.5px; color:#64748b; line-height:1.6;">
                SSL is required if you're using the Authorize.Net AIM payment API. Authorize.net only supports 1 currency per account. Make sure you add only 1 currency associated with your Authorize account in the currencies field.<br><br>
                If you are enabling test mode, make sure to set test credentials from https://sandbox.authorize.net<br><br>
                Currently supported currencies: USD, CAD, CHF, DKK, EUR, GBP, NOK, PLN, SEK, AUD, NZD
              </div>
              <div class="switches-grid">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Active</span></div>
                  <a-radio-group v-model:value="settings.gw_authorize_active" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
              <div class="settings-form-grid" style="margin-top:16px;">
                <a-form-item label="Label"><a-input v-model:value="settings.gw_authorize_label" /></a-form-item>
                <a-form-item label="Public Key"><a-input v-model:value="settings.gw_authorize_public_key" /></a-form-item>
                <a-form-item label="API Login ID"><a-input v-model:value="settings.gw_authorize_api_login" /></a-form-item>
                <a-form-item label="API Transaction ID"><a-input v-model:value="settings.gw_authorize_txn_id" /></a-form-item>
                <a-form-item label="Gateway Dashboard Payment Description" style="grid-column:span 2"><a-input v-model:value="settings.gw_authorize_desc" /></a-form-item>
                <a-form-item label="Currency">
                  <a-select v-model:value="settings.gw_authorize_currency" style="width:100%">
                    <a-select-option value="USD">USD</a-select-option>
                    <a-select-option value="CAD">CAD</a-select-option>
                    <a-select-option value="CHF">CHF</a-select-option>
                    <a-select-option value="DKK">DKK</a-select-option>
                    <a-select-option value="EUR">EUR</a-select-option>
                    <a-select-option value="GBP">GBP</a-select-option>
                    <a-select-option value="NOK">NOK</a-select-option>
                    <a-select-option value="PLN">PLN</a-select-option>
                    <a-select-option value="SEK">SEK</a-select-option>
                    <a-select-option value="AUD">AUD</a-select-option>
                    <a-select-option value="NZD">NZD</a-select-option>
                  </a-select>
                </a-form-item>
              </div>
              <div class="switches-grid" style="margin-top:16px;">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Enable Test Mode</span></div>
                  <a-radio-group v-model:value="settings.gw_authorize_test" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Selected by default on invoice</span></div>
                  <a-radio-group v-model:value="settings.gw_authorize_default" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>

            <!-- ── Instamojo ── -->
            <div v-if="activeGateway === 'instamojo'">
              <div class="switches-grid">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Active</span></div>
                  <a-radio-group v-model:value="settings.gw_instamojo_active" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
              <div class="settings-form-grid" style="margin-top:16px;">
                <a-form-item label="Label"><a-input v-model:value="settings.gw_instamojo_label" /></a-form-item>
                <a-form-item label="Fixed Fee"><a-input-number v-model:value="settings.gw_instamojo_fixed_fee" :min="0" style="width:100%" /></a-form-item>
                <a-form-item label="Percentage Fee"><a-input-number v-model:value="settings.gw_instamojo_pct_fee" :min="0" style="width:100%" /></a-form-item>
                <a-form-item label="Private API Key"><a-input v-model:value="settings.gw_instamojo_api_key" /></a-form-item>
                <a-form-item label="Private Auth Token"><a-input v-model:value="settings.gw_instamojo_auth_token" /></a-form-item>
                <a-form-item label="Gateway Dashboard Payment Description" style="grid-column:span 2"><a-input v-model:value="settings.gw_instamojo_desc" /></a-form-item>
                <a-form-item label="Currencies (comma separated)"><a-input v-model:value="settings.gw_instamojo_currencies" /></a-form-item>
              </div>
              <div class="switches-grid" style="margin-top:16px;">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Enable Test Mode</span></div>
                  <a-radio-group v-model:value="settings.gw_instamojo_test" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Selected by default on invoice</span></div>
                  <a-radio-group v-model:value="settings.gw_instamojo_default" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>

            <!-- ── Mollie ── -->
            <div v-if="activeGateway === 'mollie'">
              <div class="switches-grid">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Active</span></div>
                  <a-radio-group v-model:value="settings.gw_mollie_active" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
              <div class="settings-form-grid" style="margin-top:16px;">
                <a-form-item label="Label"><a-input v-model:value="settings.gw_mollie_label" /></a-form-item>
                <a-form-item label="API Key"><a-input v-model:value="settings.gw_mollie_api_key" /></a-form-item>
                <a-form-item label="Gateway Dashboard Payment Description" style="grid-column:span 2"><a-input v-model:value="settings.gw_mollie_desc" /></a-form-item>
                <a-form-item label="Currency">
                  <a-select v-model:value="settings.gw_mollie_currency" style="width:100%">
                    <a-select-option value="EUR">EUR</a-select-option>
                  </a-select>
                </a-form-item>
              </div>
              <div class="switches-grid" style="margin-top:16px;">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Enable Test Mode</span></div>
                  <a-radio-group v-model:value="settings.gw_mollie_test" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Selected by default on invoice</span></div>
                  <a-radio-group v-model:value="settings.gw_mollie_default" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>

            <!-- ── Braintree ── -->
            <div v-if="activeGateway === 'braintree'">
              <div class="switches-grid">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Active</span></div>
                  <a-radio-group v-model:value="settings.gw_braintree_active" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
              <div class="settings-form-grid" style="margin-top:16px;">
                <a-form-item label="Label"><a-input v-model:value="settings.gw_braintree_label" /></a-form-item>
                <a-form-item label="Merchant ID"><a-input v-model:value="settings.gw_braintree_merchant_id" /></a-form-item>
                <a-form-item label="Public Key"><a-input v-model:value="settings.gw_braintree_public_key" /></a-form-item>
                <a-form-item label="Private Key"><a-input v-model:value="settings.gw_braintree_private_key" type="password" /></a-form-item>
                <a-form-item label="Currencies (comma separated)"><a-input v-model:value="settings.gw_braintree_currencies" /></a-form-item>
              </div>
              <div class="switches-grid" style="margin-top:16px;">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Enable PayPal Payments</span></div>
                  <a-radio-group v-model:value="settings.gw_braintree_paypal" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Enable Test Mode</span></div>
                  <a-radio-group v-model:value="settings.gw_braintree_test" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Selected by default on invoice</span></div>
                  <a-radio-group v-model:value="settings.gw_braintree_default" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>

            <!-- ── Paypal Smart Checkout ── -->
            <div v-if="activeGateway === 'paypal_smart'">
              <div class="switches-grid">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Active</span></div>
                  <a-radio-group v-model:value="settings.gw_paypal_smart_active" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
              <div class="settings-form-grid" style="margin-top:16px;">
                <a-form-item label="Label"><a-input v-model:value="settings.gw_paypal_smart_label" /></a-form-item>
                <a-form-item label="Fixed Fee"><a-input-number v-model:value="settings.gw_paypal_smart_fixed_fee" :min="0" style="width:100%" /></a-form-item>
                <a-form-item label="Percentage Fee"><a-input-number v-model:value="settings.gw_paypal_smart_pct_fee" :min="0" style="width:100%" /></a-form-item>
                <a-form-item label="Client ID"><a-input v-model:value="settings.gw_paypal_smart_client_id" /></a-form-item>
                <a-form-item label="Secret"><a-input v-model:value="settings.gw_paypal_smart_secret" type="password" /></a-form-item>
                <a-form-item label="Gateway Dashboard Payment Description" style="grid-column:span 2"><a-input v-model:value="settings.gw_paypal_smart_desc" /></a-form-item>
                <a-form-item label="Currencies (comma separated)"><a-input v-model:value="settings.gw_paypal_smart_currencies" /></a-form-item>
              </div>
              <div class="switches-grid" style="margin-top:16px;">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Enable Test Mode</span></div>
                  <a-radio-group v-model:value="settings.gw_paypal_smart_test" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Selected by default on invoice</span></div>
                  <a-radio-group v-model:value="settings.gw_paypal_smart_default" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>

            <!-- ── Paypal ── -->
            <div v-if="activeGateway === 'paypal'">
              <div class="switches-grid">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Active</span></div>
                  <a-radio-group v-model:value="settings.gw_paypal_active" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
              <div class="settings-form-grid" style="margin-top:16px;">
                <a-form-item label="Label"><a-input v-model:value="settings.gw_paypal_label" /></a-form-item>
                <a-form-item label="Fixed Fee"><a-input-number v-model:value="settings.gw_paypal_fixed_fee" :min="0" style="width:100%" /></a-form-item>
                <a-form-item label="Percentage Fee"><a-input-number v-model:value="settings.gw_paypal_pct_fee" :min="0" style="width:100%" /></a-form-item>
                <a-form-item label="PayPal API Username"><a-input v-model:value="settings.gw_paypal_api_user" /></a-form-item>
                <a-form-item label="PayPal API Password"><a-input v-model:value="settings.gw_paypal_api_pass" type="password" /></a-form-item>
                <a-form-item label="API Signature"><a-input v-model:value="settings.gw_paypal_api_sig" /></a-form-item>
                <a-form-item label="Gateway Dashboard Payment Description" style="grid-column:span 2"><a-input v-model:value="settings.gw_paypal_desc" /></a-form-item>
                <a-form-item label="Currencies (comma separated)"><a-input v-model:value="settings.gw_paypal_currencies" /></a-form-item>
              </div>
              <div class="switches-grid" style="margin-top:16px;">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Enable Test Mode</span></div>
                  <a-radio-group v-model:value="settings.gw_paypal_test" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Selected by default on invoice</span></div>
                  <a-radio-group v-model:value="settings.gw_paypal_default" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>

            <!-- ── PayU Money ── -->
            <div v-if="activeGateway === 'payu'">
              <div class="switches-grid">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Active</span></div>
                  <a-radio-group v-model:value="settings.gw_payu_active" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
              <div class="settings-form-grid" style="margin-top:16px;">
                <a-form-item label="Label"><a-input v-model:value="settings.gw_payu_label" /></a-form-item>
                <a-form-item label="Fixed Fee"><a-input-number v-model:value="settings.gw_payu_fixed_fee" :min="0" style="width:100%" /></a-form-item>
                <a-form-item label="Percentage Fee"><a-input-number v-model:value="settings.gw_payu_pct_fee" :min="0" style="width:100%" /></a-form-item>
                <a-form-item label="PayU Money Key"><a-input v-model:value="settings.gw_payu_key" /></a-form-item>
                <a-form-item label="PayU Money Salt"><a-input v-model:value="settings.gw_payu_salt" type="password" /></a-form-item>
                <a-form-item label="Gateway Dashboard Payment Description" style="grid-column:span 2"><a-input v-model:value="settings.gw_payu_desc" /></a-form-item>
                <a-form-item label="Currency">
                  <a-select v-model:value="settings.gw_payu_currency" style="width:100%">
                    <a-select-option value="INR">INR</a-select-option>
                  </a-select>
                </a-form-item>
              </div>
              <div class="switches-grid" style="margin-top:16px;">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Enable Test Mode</span></div>
                  <a-radio-group v-model:value="settings.gw_payu_test" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Selected by default on invoice</span></div>
                  <a-radio-group v-model:value="settings.gw_payu_default" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>

            <!-- ── Stripe Checkout ── -->
            <div v-if="activeGateway === 'stripe_checkout'">
              <div class="switches-grid">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Active</span></div>
                  <a-radio-group v-model:value="settings.gw_stripe_active" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
              <div class="settings-form-grid" style="margin-top:16px;">
                <a-form-item label="Label"><a-input v-model:value="settings.gw_stripe_label" /></a-form-item>
                <a-form-item label="Fixed Fee"><a-input-number v-model:value="settings.gw_stripe_fixed_fee" :min="0" style="width:100%" /></a-form-item>
                <a-form-item label="Percentage Fee"><a-input-number v-model:value="settings.gw_stripe_pct_fee" :min="0" style="width:100%" /></a-form-item>
                <a-form-item label="Stripe Publishable Key"><a-input v-model:value="settings.gw_stripe_pub_key" /></a-form-item>
                <a-form-item label="Stripe API Secret Key"><a-input v-model:value="settings.gw_stripe_secret" type="password" /></a-form-item>
                <a-form-item label="Gateway Dashboard Payment Description" style="grid-column:span 2"><a-input v-model:value="settings.gw_stripe_desc" /></a-form-item>
                <a-form-item label="Currencies (comma separated)"><a-input v-model:value="settings.gw_stripe_currencies" /></a-form-item>
              </div>
              <div class="switches-grid" style="margin-top:16px;">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Allow primary contact to update stored credit card token?</span></div>
                  <a-radio-group v-model:value="settings.gw_stripe_allow_card_update" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Selected by default on invoice</span></div>
                  <a-radio-group v-model:value="settings.gw_stripe_default" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>

            <!-- ── Stripe iDEAL ── -->
            <div v-if="activeGateway === 'stripe_ideal'">
              <div style="background:#fef3c7; border:1px solid #f59e0b; border-radius:8px; padding:14px; margin-bottom:20px; font-size:12.5px; color:#92400e; line-height:1.6;">
                The Stripe iDEAL gateway is deprecated, and Stripe no longer supports its API. We recommend migrating to the new gateway (Stripe iDEAL V2), available as a module. Be sure to activate it in Setup &gt; Modules
              </div>
              <div class="switches-grid">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Active</span></div>
                  <a-radio-group v-model:value="settings.gw_stripe_ideal_active" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
              <div class="settings-form-grid" style="margin-top:16px;">
                <a-form-item label="Label"><a-input v-model:value="settings.gw_stripe_ideal_label" /></a-form-item>
                <a-form-item label="Stripe API Secret Key"><a-input v-model:value="settings.gw_stripe_ideal_secret" type="password" /></a-form-item>
                <a-form-item label="Stripe Publishable Key"><a-input v-model:value="settings.gw_stripe_ideal_pub" /></a-form-item>
                <a-form-item label="Gateway Dashboard Payment Description" style="grid-column:span 2"><a-input v-model:value="settings.gw_stripe_ideal_desc" /></a-form-item>
                <a-form-item label="Statement Descriptor (shown in customer bank statement)" style="grid-column:span 2"><a-input v-model:value="settings.gw_stripe_ideal_statement" :maxlength="22" />
                  <template #extra>Statement descriptors are limited to 22 characters, cannot use the special characters &lt; &gt; ' " or *, and must not consist solely of numbers.</template>
                </a-form-item>
                <a-form-item label="Currencies (comma separated)"><a-input v-model:value="settings.gw_stripe_ideal_currencies" /></a-form-item>
              </div>
              <div class="switches-grid" style="margin-top:16px;">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Selected by default on invoice</span></div>
                  <a-radio-group v-model:value="settings.gw_stripe_ideal_default" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>

            <!-- ── 2Checkout ── -->
            <div v-if="activeGateway === '2checkout'">
              <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:14px; margin-bottom:20px; font-size:12.5px; color:#64748b; line-height:1.6;">
                The IPN Endpoint for 2Checkout is ( https://perfexcrm.com/demo/gateways/two_checkout/webhook )
              </div>
              <div class="switches-grid">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Active</span></div>
                  <a-radio-group v-model:value="settings.gw_2checkout_active" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
              <div class="settings-form-grid" style="margin-top:16px;">
                <a-form-item label="Label"><a-input v-model:value="settings.gw_2checkout_label" /></a-form-item>
                <a-form-item label="Fixed Fee"><a-input-number v-model:value="settings.gw_2checkout_fixed_fee" :min="0" style="width:100%" /></a-form-item>
                <a-form-item label="Percentage Fee"><a-input-number v-model:value="settings.gw_2checkout_pct_fee" :min="0" style="width:100%" /></a-form-item>
                <a-form-item label="Merchant Code"><a-input v-model:value="settings.gw_2checkout_merchant" /></a-form-item>
                <a-form-item label="Secret Code"><a-input v-model:value="settings.gw_2checkout_secret" type="password" /></a-form-item>
                <a-form-item label="Gateway Dashboard Payment Description" style="grid-column:span 2"><a-input v-model:value="settings.gw_2checkout_desc" /></a-form-item>
                <a-form-item label="Currencies (comma separated)"><a-input v-model:value="settings.gw_2checkout_currencies" /></a-form-item>
              </div>
              <div class="switches-grid" style="margin-top:16px;">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Enable Test Mode</span></div>
                  <a-radio-group v-model:value="settings.gw_2checkout_test" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Selected by default on invoice</span></div>
                  <a-radio-group v-model:value="settings.gw_2checkout_default" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>
          </div>

          <!-- e-Invoice -->
          <div v-else-if="activeCategory === 'e_invoice'">
            <h3 class="panel-section-title">e-Invoice Settings</h3>
            <div class="settings-form-grid">
              <a-form-item label="Default e-Invoice template" style="grid-column: span 2;">
                <a-select v-model:value="settings.einvoice_default_template" style="width: 100%">
                  <a-select-option value="">Select Template</a-select-option>
                </a-select>
              </a-form-item>
            </div>

            <div class="switches-grid" style="margin-top: 20px;">
              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Send e-Invoice as invoice attachment with email</span>
                </div>
                <a-radio-group v-model:value="settings.einvoice_attach_invoice" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
            </div>

            <div class="settings-form-grid" style="margin-top: 20px;">
              <a-form-item label="Default e-Invoice template for credit note" style="grid-column: span 2;">
                <a-select v-model:value="settings.einvoice_credit_template" style="width: 100%">
                  <a-select-option value="">Select Template</a-select-option>
                </a-select>
              </a-form-item>
            </div>

            <div class="switches-grid" style="margin-top: 20px;">
              <div class="switch-row">
                <div class="switch-labels">
                  <span class="switch-title">Send e-Invoice as credit note attachment with email</span>
                </div>
                <a-radio-group v-model:value="settings.einvoice_attach_credit" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
            </div>

            <!-- e-Invoice Templates Table -->
            <div style="margin-top: 24px;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h4 style="margin: 0; font-weight: 600;">e-Invoice Templates</h4>
                <a-button type="primary" size="small">New e-Invoice template</a-button>
              </div>
              
              <a-table 
                :columns="[
                  { title: 'Template Title', dataIndex: 'title', key: 'title' },
                  { title: 'Type', dataIndex: 'type', key: 'type' },
                  { title: 'Options', key: 'options' }
                ]"
                :data-source="einvoiceTemplates"
                :pagination="{ pageSize: 25 }"
                size="small"
              >
                <template #bodyCell="{ column, record }">
                  <template v-if="column.key === 'options'">
                    <a-space>
                      <a-button size="small" type="link">Edit</a-button>
                      <a-button size="small" type="link" danger>Delete</a-button>
                    </a-space>
                  </template>
                </template>
              </a-table>
            </div>

            <!-- Template Variables Reference -->
            <div v-pre style="margin-top: 24px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px;">
              <h4 style="margin: 0 0 12px 0; font-weight: 600;">Template variables</h4>
              
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div>
                  <h5 style="font-weight: 600; margin: 0 0 8px 0;">Invoice:</h5>
                  <div style="font-size: 12px; color: #475569; line-height: 1.8;">
                    <div>{{INVOICE_ID}}</div>
                    <div>{{INVOICE_NUMBER}}</div>
                    <div>{{INVOICE_DATE}}</div>
                    <div>{{INVOICE_DUE_DATE}}</div>
                    <div>{{INVOICE_STATUS}}</div>
                    <div>{{INVOICE_SUBTOTAL}}</div>
                    <div>{{INVOICE_TOTAL_TAX}}</div>
                    <div>{{INVOICE_ADJUSTMENT}}</div>
                    <div>{{INVOICE_DISCOUNT_TOTAL}}</div>
                    <div>{{INVOICE_TOTAL}}</div>
                    <div>{{INVOICE_AMOUNT_PAID}}</div>
                    <div>{{INVOICE_BALANCE_DUE}}</div>
                    <div>{{CURRENCY_CODE}}</div>
                    <div>{{INVOICE_BILLING_ADRESS}}</div>
                    <div>{{INVOICE_BILLING_CITY}}</div>
                    <div>{{INVOICE_BILLING_STATE}}</div>
                    <div>{{INVOICE_BILLING_ZIP}}</div>
                    <div>{{INVOICE_BILLING_COUNTRY_NAME}}</div>
                    <div>{{INVOICE_BILLING_COUNTRY_ISO2}}</div>
                    <div>{{INVOICE_BILLING_COUNTRY_ISO3}}</div>
                    <div>{{INVOICE_SHIPPING_ADRESS}}</div>
                    <div>{{INVOICE_SHIPPING_CITY}}</div>
                    <div>{{INVOICE_SHIPPING_STATE}}</div>
                    <div>{{INVOICE_SHIPPING_ZIP}}</div>
                    <div>{{INVOICE_SHIPPING_COUNTRY_NAME}}</div>
                    <div>{{INVOICE_SHIPPING_COUNTRY_ISO2}}</div>
                    <div>{{INVOICE_SHIPPING_COUNTRY_ISO3}}</div>
                  </div>
                  
                  <h5 style="font-weight: 600; margin: 12px 0 8px 0;">Invoice Items:</h5>
                  <div style="font-size: 12px; color: #475569; line-height: 1.8;">
                    <div>{{# LINE_ITEMS }}</div>
                    <div style="padding-left: 16px;">{{LINE_ITEM_ID}}</div>
                    <div style="padding-left: 16px;">{{LINE_ITEM_ORDER}}</div>
                    <div style="padding-left: 16px;">{{LINE_ITEM_NAME}}</div>
                    <div style="padding-left: 16px;">{{LINE_ITEM_DESCRIPTION}}</div>
                    <div style="padding-left: 16px;">{{LINE_ITEM_QUANTITY_NUMBER}}</div>
                    <div style="padding-left: 16px;">{{LINE_ITEM_QUANTITY_UNIT}}</div>
                    <div style="padding-left: 16px;">{{LINE_ITEM_UNIT_PRICE}}</div>
                    <div style="padding-left: 16px;">{{LINE_ITEM_TOTAL}}</div>
                    <div style="padding-left: 16px;">{{# LINE_ITEM_TAXES }}</div>
                    <div style="padding-left: 32px;">{{TAX_NAME}}</div>
                    <div style="padding-left: 32px;">{{TAX_RATE}}</div>
                    <div style="padding-left: 32px;">{{TAX_TOTAL}}</div>
                    <div style="padding-left: 16px;">{{/ LINE_ITEM_TAXES }}</div>
                    <div>{{/ LINE_ITEMS }}</div>
                  </div>
                </div>
                
                <div>
                  <h5 style="font-weight: 600; margin: 0 0 8px 0;">Company:</h5>
                  <div style="font-size: 12px; color: #475569; line-height: 1.8;">
                    <div>{{COMPANY_NAME}}</div>
                    <div>{{COMPANY_ADDRESS}}</div>
                    <div>{{COMPANY_CITY}}</div>
                    <div>{{COMPANY_STATE}}</div>
                    <div>{{COMPANY_COUNTRY_NAME}}</div>
                    <div>{{COMPANY_COUNTRY_ISO2}}</div>
                    <div>{{COMPANY_COUNTRY_ISO3}}</div>
                    <div>{{COMPANY_ZIP_CODE}}</div>
                    <div>{{COMPANY_PHONE}}</div>
                    <div>{{COMPANY_VAT_NUMBER}}</div>
                  </div>
                  
                  <h5 style="font-weight: 600; margin: 12px 0 8px 0;">Customer:</h5>
                  <div style="font-size: 12px; color: #475569; line-height: 1.8;">
                    <div>{{CONTACT_FIRST_NAME}}</div>
                    <div>{{CONTACT_LAST_NAME}}</div>
                    <div>{{CONTACT_PHONE_NUMBER}}</div>
                    <div>{{CONTACT_EMAIL}}</div>
                    <div>{{CUSTOMER_NAME}}</div>
                    <div>{{CUSTOMER_PHONE}}</div>
                    <div>{{CUSTOMER_COUNTRY_NAME}}</div>
                    <div>{{CUSTOMER_COUNTRY_ISO2}}</div>
                    <div>{{CUSTOMER_COUNTRY_ISO3}}</div>
                    <div>{{CUSTOMER_CITY}}</div>
                    <div>{{CUSTOMER_ZIP}}</div>
                    <div>{{CUSTOMER_STATE}}</div>
                    <div>{{CUSTOMER_ADDRESS}}</div>
                    <div>{{CUSTOMER_VAT_NUMBER}}</div>
                    <div>{{CUSTOMER_ID}}</div>
                  </div>
                  
                  <h5 style="font-weight: 600; margin: 12px 0 8px 0;">Credit Note:</h5>
                  <div style="font-size: 12px; color: #475569; line-height: 1.8;">
                    <div>{{CREDIT_NOTE_ID}}</div>
                    <div>{{CREDIT_NOTE_NUMBER}}</div>
                    <div>{{CREDIT_NOTE_DATE}}</div>
                    <div>{{CREDIT_NOTE_STATUS}}</div>
                    <div>{{CREDIT_NOTE_SUBTOTAL}}</div>
                    <div>{{CREDIT_NOTE_TOTAL_TAX}}</div>
                    <div>{{CREDIT_NOTE_ADJUSTMENT}}</div>
                    <div>{{CREDIT_NOTE_DISCOUNT_TOTAL}}</div>
                    <div>{{CREDIT_NOTE_TOTAL}}</div>
                    <div>{{CURRENCY_CODE}}</div>
                    <div>{{CREDIT_NOTE_BILLING_ADRESS}}</div>
                    <div>{{CREDIT_NOTE_BILLING_CITY}}</div>
                    <div>{{CREDIT_NOTE_BILLING_STATE}}</div>
                    <div>{{CREDIT_NOTE_BILLING_ZIP}}</div>
                    <div>{{CREDIT_NOTE_BILLING_COUNTRY}}</div>
                    <div>{{CREDIT_NOTE_SHIPPING_ADRESS}}</div>
                    <div>{{CREDIT_NOTE_SHIPPING_CITY}}</div>
                    <div>{{CREDIT_NOTE_SHIPPING_STATE}}</div>
                    <div>{{CREDIT_NOTE_SHIPPING_ZIP}}</div>
                    <div>{{CREDIT_NOTE_SHIPPING_COUNTRY}}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>


          <!-- ── GROUP 3: CONFIGURE FEATURES ── -->
          <!-- Customers -->
          <div v-else-if="activeCategory === 'customers'">
            <h3 class="panel-section-title">Customers</h3>
            <div class="settings-form-grid">
              <a-form-item label="Default customers theme">
                <a-select v-model:value="settings.cust_theme" style="width: 100%">
                  <a-select-option value="perfex">Perfex</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Default Country">
                <a-select v-model:value="settings.cust_default_country" style="width: 100%" showSearch>
                  <a-select-option value="">Select Country</a-select-option>
                </a-select>
              </a-form-item>
            </div>

            <div class="settings-form-grid" style="margin-top: 20px;">
              <a-form-item label="Visible Tabs (Profile)" style="grid-column: span 2;">
                <a-select v-model:value="settings.cust_visible_tabs" mode="multiple" style="width: 100%">
                  <a-select-option value="notes">Notes</a-select-option>
                  <a-select-option value="statements">Statements</a-select-option>
                  <a-select-option value="invoices">Invoices</a-select-option>
                  <a-select-option value="payments">Payments</a-select-option>
                  <a-select-option value="proposals">Proposals</a-select-option>
                  <a-select-option value="credit_notes">Credit Notes</a-select-option>
                  <a-select-option value="estimates">Estimates</a-select-option>
                  <a-select-option value="subscriptions">Subscriptions</a-select-option>
                  <a-select-option value="expenses">Expenses</a-select-option>
                  <a-select-option value="contracts">Contracts</a-select-option>
                  <a-select-option value="projects">Projects</a-select-option>
                  <a-select-option value="tasks">Tasks</a-select-option>
                  <a-select-option value="tickets">Tickets</a-select-option>
                  <a-select-option value="files">Files</a-select-option>
                  <a-select-option value="vault">Vault</a-select-option>
                  <a-select-option value="reminders">Reminders</a-select-option>
                  <a-select-option value="map">Map</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Required fields for registration (customers area)" style="grid-column: span 2;">
                <a-select v-model:value="settings.cust_required_fields" mode="multiple" style="width: 100%">
                  <a-select-option value="first_name">First Name - Contact</a-select-option>
                  <a-select-option value="last_name">Last Name - Contact</a-select-option>
                  <a-select-option value="email">Email Address - Contact</a-select-option>
                  <a-select-option value="phone">Phone - Contact</a-select-option>
                  <a-select-option value="website">Website - Contact</a-select-option>
                  <a-select-option value="position">Position - Contact</a-select-option>
                  <a-select-option value="company">Company - Company</a-select-option>
                  <a-select-option value="vat_number">VAT Number - Company</a-select-option>
                  <a-select-option value="company_phone">Phone - Company</a-select-option>
                  <a-select-option value="company_country">Country - Company</a-select-option>
                  <a-select-option value="company_city">City - Company</a-select-option>
                  <a-select-option value="company_address">Address - Company</a-select-option>
                  <a-select-option value="company_zip">Zip Code - Company</a-select-option>
                  <a-select-option value="company_state">State - Company</a-select-option>
                </a-select>
              </a-form-item>
            </div>

            <div class="switches-grid" style="margin-top: 20px;">
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Company field is required?</span></div>
                <a-radio-group v-model:value="settings.cust_company_required" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Company requires the usage of the VAT Number field</span></div>
                <a-radio-group v-model:value="settings.cust_company_vat_required" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Allow customers to register</span></div>
                <a-radio-group v-model:value="settings.cust_allow_register" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Require registration confirmation from administrator after customer register</span></div>
                <a-radio-group v-model:value="settings.cust_require_admin_confirm" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Allow primary contact to manage other customer contacts</span></div>
                <a-radio-group v-model:value="settings.cust_primary_manage_contacts" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Enable Honeypot spam validation</span></div>
                <a-radio-group v-model:value="settings.cust_honeypot" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Allow primary contact to view/edit billing & shipping details</span></div>
                <a-radio-group v-model:value="settings.cust_primary_billing" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Contacts see only own files uploaded in customer area (files uploaded in customer profile)</span></div>
                <a-radio-group v-model:value="settings.cust_contacts_own_files" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Allow contacts to delete own files uploaded from customers area</span></div>
                <a-radio-group v-model:value="settings.cust_contacts_delete_files" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Use Knowledge Base</span></div>
                <a-radio-group v-model:value="settings.cust_use_kb" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Allow knowledge base to be viewed without registration</span></div>
                <a-radio-group v-model:value="settings.cust_kb_no_register" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Show Estimate request link in customers area?</span></div>
                <a-radio-group v-model:value="settings.cust_estimate_request" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
            </div>

            <div class="settings-form-grid" style="margin-top: 20px;">
              <a-form-item label="Default contact permissions" style="grid-column: span 2;">
                <a-select v-model:value="settings.cust_default_permissions" mode="multiple" style="width: 100%">
                  <a-select-option value="invoices">Invoices</a-select-option>
                  <a-select-option value="estimates">Estimates</a-select-option>
                  <a-select-option value="contracts">Contracts</a-select-option>
                  <a-select-option value="proposals">Proposals</a-select-option>
                  <a-select-option value="support">Support</a-select-option>
                  <a-select-option value="projects">Projects</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Customer Information Format (PDF and HTML)" style="grid-column: span 2;">
                <a-textarea v-model:value="settings.cust_info_format" :rows="4" />
                <template #extra>{company_name}, {customer_id}, {street}, {city}, {state}, {zip_code}, {country_code}, {country_name}, {phone}, {vat_number}, {vat_number_with_label}</template>
              </a-form-item>
            </div>
          </div>

          <!-- Tasks -->
          <div v-else-if="activeCategory === 'tasks'">
            <h3 class="panel-section-title">Tasks Settings</h3>
            <div class="settings-form-grid">
              <a-form-item label="Limit tasks kanban rows per status">
                <a-input-number v-model:value="settings.task_kanban_limit" :min="1" style="width: 100%" />
              </a-form-item>
            </div>

            <div class="switches-grid" style="margin-top: 20px;">
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Allow all staff to see all tasks related to projects (includes non-staff)</span></div>
                <a-radio-group v-model:value="settings.task_all_staff_view" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Allow customer/staff to add/edit task comments only in the first hour (administrators not applied)</span></div>
                <a-radio-group v-model:value="settings.task_comment_first_hour" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Auto assign task creator when new task is created</span></div>
                <a-radio-group v-model:value="settings.task_auto_assign_creator" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Auto add task creator as task follower when new task is created</span></div>
                <a-radio-group v-model:value="settings.task_auto_follower_creator" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Stop all other started timers when starting new timer</span></div>
                <a-radio-group v-model:value="settings.task_stop_other_timers" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Change task status to In Progress on timer started (valid only if task status is Not Started)</span></div>
                <a-radio-group v-model:value="settings.task_status_on_timer" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Billable option is by default checked when new task is created? (only from admin area)</span></div>
                <a-radio-group v-model:value="settings.task_billable_default" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
            </div>

            <div class="settings-form-grid" style="margin-top: 20px;">
              <a-form-item label="Round off task timer">
                <a-select v-model:value="settings.task_round_mode" style="width: 100%">
                  <a-select-option value="none">Don't round off</a-select-option>
                  <a-select-option value="multiplies">Multiplies of</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Minutes" v-if="settings.task_round_mode === 'multiplies'">
                <a-input-number v-model:value="settings.task_round_minutes" :min="1" style="width: 100%" />
              </a-form-item>
              <a-form-item label="Default status when new task is created">
                <a-select v-model:value="settings.task_default_status" style="width: 100%">
                  <a-select-option value="auto">Auto</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Default Priority">
                <a-select v-model:value="settings.task_priority" style="width: 100%">
                  <a-select-option value="low">Low</a-select-option>
                  <a-select-option value="medium">Medium</a-select-option>
                  <a-select-option value="high">High</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Modal Width Class (modal-lg, modal-xl, modal-xxl)">
                <a-select v-model:value="settings.task_modal_width" style="width: 100%">
                  <a-select-option value="modal-lg">modal-lg</a-select-option>
                  <a-select-option value="modal-xl">modal-xl</a-select-option>
                  <a-select-option value="modal-xxl">modal-xxl</a-select-option>
                </a-select>
              </a-form-item>
            </div>
            <p style="font-size:12px; color:#64748b; margin-top:4px;">Applied to the Timesheets overview report and when invoicing a task/project.</p>
          </div>

          <!-- Support -->
          <div v-else-if="activeCategory === 'support'">
            <h3 class="panel-section-title">Support Settings</h3>
            
            <!-- Support Tabs -->
            <div style="display: flex; gap: 4px; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
              <a-button :type="activeSupportTab === 'general' ? 'primary' : 'default'" size="small" @click="activeSupportTab = 'general'">General</a-button>
              <a-button :type="activeSupportTab === 'email_piping' ? 'primary' : 'default'" size="small" @click="activeSupportTab = 'email_piping'">Email Piping</a-button>
              <a-button :type="activeSupportTab === 'ticket_form' ? 'primary' : 'default'" size="small" @click="activeSupportTab = 'ticket_form'">Ticket Form</a-button>
            </div>

            <!-- General Tab -->
            <div v-if="activeSupportTab === 'general'">
              <div class="switches-grid">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Use services</span></div>
                  <a-radio-group v-model:value="settings.support_use_services" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Disable Ticket Public URL</span></div>
                  <a-radio-group v-model:value="settings.support_disable_public_url" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Allow staff to access only ticket that belongs to staff departments</span></div>
                  <a-radio-group v-model:value="settings.support_staff_dept_only" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Send staff-related ticket notifications to the ticket assignee only</span></div>
                  <a-radio-group v-model:value="settings.support_notify_assignee_only" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Receive notification on new ticket opened</span></div>
                  <a-radio-group v-model:value="settings.support_notify_new_ticket" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Receive notification when customer reply to a ticket</span></div>
                  <a-radio-group v-model:value="settings.support_notify_customer_reply" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Allow staff members to open tickets to all contacts?</span></div>
                  <a-radio-group v-model:value="settings.support_staff_all_contacts" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Automatically assign the ticket to the first staff that post a reply?</span></div>
                  <a-radio-group v-model:value="settings.support_auto_assign_first_reply" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Allow access to tickets for non staff members</span></div>
                  <a-radio-group v-model:value="settings.support_non_staff_access" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Allow non-admin staff members to delete ticket attachments</span></div>
                  <a-radio-group v-model:value="settings.support_non_admin_delete_attachments" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Allow non-admin staff members to delete tickets and replies</span></div>
                  <a-radio-group v-model:value="settings.support_non_admin_delete_tickets" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Allow customer to change ticket status from customers area</span></div>
                  <a-radio-group v-model:value="settings.support_customer_change_status" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">In customers area only show tickets related to the logged in contact (Primary contact not applied)</span></div>
                  <a-radio-group v-model:value="settings.support_customer_own_tickets" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Enable support menu item badge</span></div>
                  <a-radio-group v-model:value="settings.support_menu_badge" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>

              <div class="settings-form-grid" style="margin-top: 20px;">
                <a-form-item label="Ticket Replies Order">
                  <a-select v-model:value="settings.support_replies_order" style="width: 100%">
                    <a-select-option value="ascending">Ascending</a-select-option>
                    <a-select-option value="descending">Descending</a-select-option>
                  </a-select>
                </a-form-item>
                <a-form-item label="Default status selected when replying to ticket">
                  <a-select v-model:value="settings.support_default_reply_status" style="width: 100%">
                    <a-select-option value="answered">Answered</a-select-option>
                  </a-select>
                </a-form-item>
                <a-form-item label="Maximum ticket attachments">
                  <a-input-number v-model:value="settings.support_max_attachments" :min="0" style="width: 100%" />
                </a-form-item>
                <a-form-item label="Allowed attachments file extensions" style="grid-column: span 2;">
                  <a-input v-model:value="settings.support_allowed_extensions" />
                </a-form-item>
              </div>
            </div>

            <!-- Email Piping Tab -->
            <div v-if="activeSupportTab === 'email_piping'">
              <div class="settings-form-grid">
                <a-form-item label="cPanel forwarder path" style="grid-column: span 2;">
                  <a-input v-model:value="settings.support_cpanel_path" />
                </a-form-item>
              </div>
              <div class="switches-grid" style="margin-top: 16px;">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Pipe Only on Registered Users</span></div>
                  <a-radio-group v-model:value="settings.support_pipe_registered_only" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Only Replies Allowed by Email</span></div>
                  <a-radio-group v-model:value="settings.support_email_replies_only" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Try to import only the actual ticket reply (without quoted/forwarded message)</span></div>
                  <a-radio-group v-model:value="settings.support_import_reply_only" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
              <div class="settings-form-grid" style="margin-top: 16px;">
                <a-form-item label="Default priority on piped ticket">
                  <a-select v-model:value="settings.support_piped_priority" style="width: 100%">
                    <a-select-option value="low">Low</a-select-option>
                    <a-select-option value="medium">Medium</a-select-option>
                    <a-select-option value="high">High</a-select-option>
                  </a-select>
                </a-form-item>
              </div>
            </div>

            <!-- Ticket Form Tab -->
            <div v-if="activeSupportTab === 'ticket_form'">
              <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px;">
                <h4 style="margin:0 0 12px 0; font-weight:600;">Form Info</h4>
                <div class="settings-form-grid">
                  <a-form-item label="Form url:"><a-input :value="settings.support_form_url" readOnly /></a-form-item>
                  <a-form-item label="Form file location:"><a-input :value="settings.support_form_file" readOnly /></a-form-item>
                </div>
                
                <h4 style="margin:16px 0 8px 0; font-weight:600;">Embed form</h4>
                <p style="font-size:12.5px; color:#64748b; margin-bottom:8px;">Copy &amp; Paste the code anywhere in your site to show the form, additionally you can adjust the width and height px to fit for your website.</p>
                <a-textarea :value="settings.support_form_embed" :rows="2" readOnly style="font-family:monospace; font-size:12px;" />
                
                <h4 style="margin:16px 0 8px 0; font-weight:600;">Share direct link</h4>
                <a-input :value="settings.support_form_direct_link" readOnly style="margin-bottom:8px;" />
                <a-input :value="settings.support_form_direct_link_styled" readOnly />

                <div style="margin-top:16px; font-size:12.5px; color:#64748b; line-height:1.6;">
                  <p style="margin:0 0 8px 0;">When placing the iframe snippet code consider the following:</p>
                  <ol style="margin:0; padding-left:20px;">
                    <li>If the protocol of your installation is http use a http page inside the iframe.</li>
                    <li>If the protocol of your installation is https use a https page inside the iframe.</li>
                  </ol>
                  <p style="margin:8px 0 0 0;">None SSL installation will need to place the link in non ssl eq. landing page and backwards.</p>
                </div>

                <h4 style="margin:16px 0 8px 0; font-weight:600;">Change form container column (Bootstrap)</h4>
                <a-input :value="settings.support_form_col_default" readOnly style="margin-bottom:8px;" />
                <a-input :value="settings.support_form_col_offset" readOnly style="margin-bottom:8px;" />
                <a-input :value="settings.support_form_col_narrow" readOnly />

                <h4 style="margin:16px 0 8px 0; font-weight:600;">Specify language</h4>
                <a-input :value="settings.support_form_language" readOnly />
              </div>
            </div>
          </div>

          <!-- Leads -->
          <div v-else-if="activeCategory === 'leads'">
            <h3 class="panel-section-title">Leads Settings</h3>
            <div class="settings-form-grid">
              <a-form-item label="Limit leads kanban rows per status">
                <a-input-number v-model:value="settings.leads_kanban_limit" :min="1" style="width: 100%" />
              </a-form-item>
              <a-form-item label="Default status">
                <a-select v-model:value="settings.leads_default_status" style="width: 100%" showSearch>
                  <a-select-option value="">Select Status</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Default source">
                <a-select v-model:value="settings.leads_default_source" style="width: 100%" showSearch>
                  <a-select-option value="">Select Source</a-select-option>
                </a-select>
              </a-form-item>
            </div>

            <div class="settings-form-grid" style="margin-top: 16px;">
              <a-form-item label="Perform validation for duplicate lead on the following fields:" style="grid-column: span 2;">
                <a-select v-model:value="settings.leads_duplicate_fields" mode="multiple" style="width: 100%">
                  <a-select-option value="email">Email Address</a-select-option>
                  <a-select-option value="phone">Phone</a-select-option>
                  <a-select-option value="website">Website</a-select-option>
                  <a-select-option value="company">Company</a-select-option>
                </a-select>
              </a-form-item>
            </div>

            <div class="switches-grid" style="margin-top: 20px;">
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Auto assign as admin to customer after convert</span></div>
                <a-radio-group v-model:value="settings.leads_auto_assign_admin" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Allow non-admin staff members to import leads</span></div>
                <a-radio-group v-model:value="settings.leads_non_admin_import" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Do not allow leads to be edited after they are converted to customers (administrators not applied)</span></div>
                <a-radio-group v-model:value="settings.leads_no_edit_after_convert" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
            </div>

            <div class="settings-form-grid" style="margin-top: 20px;">
              <a-form-item label="Default leads kanban sort">
                <a-select v-model:value="settings.leads_kanban_sort" style="width: 100%">
                  <a-select-option value="kanban_order">Kanban Order</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Sort Order">
                <a-select v-model:value="settings.leads_kanban_order" style="width: 100%">
                  <a-select-option value="ascending">Ascending</a-select-option>
                  <a-select-option value="descending">Descending</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Modal Width Class (modal-lg, modal-xl, modal-xxl)">
                <a-select v-model:value="settings.leads_modal_width" style="width: 100%">
                  <a-select-option value="modal-lg">modal-lg</a-select-option>
                  <a-select-option value="modal-xl">modal-xl</a-select-option>
                  <a-select-option value="modal-xxl">modal-xxl</a-select-option>
                </a-select>
              </a-form-item>
            </div>
          </div>


          <!-- ── GROUP 4: INTEGRATIONS ── -->
          <!-- Google -->
          <div v-else-if="activeCategory === 'google'">
            <h3 class="panel-section-title">Google Integrations</h3>
            <div class="settings-form-grid">
              <a-form-item label="Google API Key" style="grid-column: span 2;">
                <a-input v-model:value="settings.google_api_key" />
              </a-form-item>
              <a-form-item label="Google API Client ID" style="grid-column: span 2;">
                <a-input v-model:value="settings.google_client_id" />
              </a-form-item>
            </div>

            <!-- reCAPTCHA -->
            <div style="margin-top: 24px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px;">
              <h4 style="font-weight:700; margin: 0 0 12px 0; font-size:13.5px; color:#1e293b;">reCAPTCHA</h4>
              <div class="settings-form-grid">
                <a-form-item label="Site key"><a-input v-model:value="settings.recaptcha_site_key" /></a-form-item>
                <a-form-item label="Secret key"><a-input v-model:value="settings.recaptcha_secret_key" type="password" /></a-form-item>
              </div>
              <div class="switches-grid" style="margin-top: 12px;">
                <div class="switch-row" style="border:none; padding-bottom:0;">
                  <div class="switch-labels"><span class="switch-title" style="font-size:13px;">Enable reCAPTCHA on customers area (Login/Register)</span></div>
                  <a-radio-group v-model:value="settings.recaptcha_enable_customer" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
              <div class="settings-form-grid" style="margin-top: 12px;">
                <a-form-item label="Ignored IP Addresses" style="grid-column: span 2;">
                  <a-textarea v-model:value="settings.recaptcha_ignored_ips" :rows="2" />
                  <template #extra>Enter comma separated IP addresses that you want the reCaptcha to skip validation.</template>
                </a-form-item>
              </div>
            </div>

            <!-- Calendar -->
            <div style="margin-top: 24px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px;">
              <h4 style="font-weight:700; margin: 0 0 12px 0; font-size:13.5px; color:#1e293b;">Calendar</h4>
              <div class="settings-form-grid">
                <a-form-item label="Google Calendar ID" style="grid-column: span 2;">
                  <a-input v-model:value="settings.google_calendar_id" />
                </a-form-item>
              </div>
            </div>

            <!-- Google Picker -->
            <div style="margin-top: 24px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px;">
              <h4 style="font-weight:700; margin: 0 0 12px 0; font-size:13.5px; color:#1e293b;">Google Picker</h4>
              <div class="switches-grid">
                <div class="switch-row" style="border:none; padding-bottom:0;">
                  <div class="switch-labels"><span class="switch-title" style="font-size:13px;">Enable Google Picker</span></div>
                  <a-radio-group v-model:value="settings.google_picker_enabled" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>
          </div>

          <!-- Pusher.com -->
          <div v-else-if="activeCategory === 'pusher'">
            <h3 class="panel-section-title">Pusher.com Real-time Settings</h3>
            <div class="settings-form-grid">
              <a-form-item label="APP ID"><a-input v-model:value="settings.pusher_app_id" /></a-form-item>
              <a-form-item label="APP Key"><a-input v-model:value="settings.pusher_key" /></a-form-item>
              <a-form-item label="APP Secret"><a-input v-model:value="settings.pusher_secret" type="password" /></a-form-item>
              <a-form-item label="Cluster">
                <a-input v-model:value="settings.pusher_cluster" />
                <template #extra><a href="https://pusher.com/docs/clusters" target="_blank" style="font-size:12px;">https://pusher.com/docs/clusters</a></template>
              </a-form-item>
            </div>
            <div class="switches-grid" style="margin-top: 20px;">
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Enable Real Time Notifications</span></div>
                <a-radio-group v-model:value="settings.pusher_realtime" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Enable Desktop Notifications</span></div>
                <a-radio-group v-model:value="settings.pusher_desktop" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
            </div>
            <div class="settings-form-grid" style="margin-top: 16px;">
              <a-form-item label="Auto Dismiss Desktop Notifications After X Seconds (0 to disable)">
                <a-input-number v-model:value="settings.pusher_dismiss_seconds" :min="0" style="width: 120px" />
              </a-form-item>
            </div>
          </div>


          <!-- ── GROUP 5: AI INTEGRATION ── -->
          <!-- AI General -->
          <div v-else-if="activeCategory === 'ai_general'">
            <h3 class="panel-section-title">AI General</h3>
            <div class="settings-form-grid">
              <a-form-item label="Provider">
                <a-select v-model:value="settings.ai_provider" style="width: 100%">
                  <a-select-option value="system">System</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="System Prompt" style="grid-column: span 2;">
                <a-textarea v-model:value="settings.ai_system_prompt" :rows="3" />
              </a-form-item>
            </div>
            <div class="switches-grid" style="margin-top: 16px;">
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Enable Ticket Summarization</span></div>
                <a-radio-group v-model:value="settings.ai_ticket_summary" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
              <div class="switch-row">
                <div class="switch-labels"><span class="switch-title">Enable Ticket Reply Suggestion</span></div>
                <a-radio-group v-model:value="settings.ai_ticket_reply" button-style="solid">
                  <a-radio-button :value="true">Yes</a-radio-button>
                  <a-radio-button :value="false">No</a-radio-button>
                </a-radio-group>
              </div>
            </div>
          </div>

          <!-- OpenAI -->
          <div v-else-if="activeCategory === 'openai'">
            <h3 class="panel-section-title">OpenAI</h3>
            <div class="settings-form-grid">
              <a-form-item label="OpenAI API Key" style="grid-column: span 2;">
                <a-input v-model:value="settings.openai_key" type="password" />
              </a-form-item>
              <a-form-item label="OpenAI Model">
                <a-select v-model:value="settings.openai_model" style="width:100%">
                  <a-select-option value="gpt-4o">GPT-4o</a-select-option>
                  <a-select-option value="gpt-4o-mini">GPT-4o Mini</a-select-option>
                  <a-select-option value="gpt-3.5-turbo">GPT-3.5 Turbo</a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="Max Output Tokens">
                <a-input-number v-model:value="settings.openai_max_tokens" :min="100" :max="4096" style="width: 120px" />
              </a-form-item>
            </div>

            <!-- Advanced Features -->
            <div style="margin-top: 24px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px;">
              <h4 style="font-weight:700; margin: 0 0 4px 0; font-size:13.5px; color:#1e293b;">Advanced Features</h4>
              <p style="font-size:12.5px; color:#64748b; margin-bottom:16px;">Fine-tune OpenAI models with your knowledge base and predefined replies content for more accurate responses.</p>
              
              <a-button type="primary" @click="showOpenAIFineTune = true">OpenAI Fine-Tuning</a-button>

              <!-- Fine-Tuning Modal -->
              <a-modal v-model:open="showOpenAIFineTune" title="OpenAI Fine-Tuning" :width="700" :footer="null">
                <div class="settings-form-grid">
                  <a-form-item label="Source Data for Fine-Tuning" style="grid-column: span 2;">
                    <div style="display: flex; gap: 24px; font-size: 13px; color: #475569;">
                      <span>Available Articles: <strong>30</strong></span>
                      <span>Predefined Replies: <strong>5</strong></span>
                    </div>
                  </a-form-item>
                  <a-form-item label="Fine-Tuning Base Model" style="grid-column: span 2;">
                    <a-select v-model:value="settings.openai_finetune_model" style="width: 100%">
                      <a-select-option value="gpt-4o-mini">GPT-4o Mini (2024-07-18)</a-select-option>
                    </a-select>
                    <template #extra>This is the base model that will be used for fine-tuning. Different models have different capabilities and price points.</template>
                  </a-form-item>
                </div>

                <div style="margin-top: 16px;">
                  <h5 style="font-weight: 600; margin-bottom: 8px;">Last Fine-Tuning Job</h5>
                  <p style="font-size: 13px; color: #94a3b8;">...</p>
                </div>

                <div style="margin-top: 16px;">
                  <h5 style="font-weight: 600; margin-bottom: 8px;">Fine-Tuned Models</h5>
                  <p style="font-size: 13px; color: #94a3b8;">No fine-tuned models available yet.</p>
                  <a-button type="primary" style="margin-top: 8px;">Start Fine-Tuning</a-button>
                </div>
              </a-modal>
            </div>
          </div>


          <!-- ── GROUP 6: OTHER ── -->
          <!-- Calendar -->
          <div v-else-if="activeCategory === 'calendar'">
            <h3 class="panel-section-title">Calendar settings</h3>

            <!-- Tabs -->
            <div style="display: flex; gap: 4px; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
              <a-button :type="activeCalendarTab === 'general' ? 'primary' : 'default'" size="small" @click="activeCalendarTab = 'general'">General</a-button>
              <a-button :type="activeCalendarTab === 'styling' ? 'primary' : 'default'" size="small" @click="activeCalendarTab = 'styling'">Styling</a-button>
            </div>

            <!-- General Tab -->
            <div v-if="activeCalendarTab === 'general'">
              <div class="settings-form-grid">
                <a-form-item label="Calendar Events Limit (Month and Week View)">
                  <a-input-number v-model:value="settings.cal_events_limit" :min="1" style="width: 100%" />
                </a-form-item>
                <a-form-item label="Default View">
                  <a-select v-model:value="settings.cal_view" style="width: 100%">
                    <a-select-option value="month">Month</a-select-option>
                    <a-select-option value="week">Week</a-select-option>
                  </a-select>
                </a-form-item>
                <a-form-item label="First Day">
                  <a-select v-model:value="settings.cal_first_day" style="width: 100%">
                    <a-select-option value="sunday">Sunday</a-select-option>
                    <a-select-option value="monday">Monday</a-select-option>
                  </a-select>
                </a-form-item>
              </div>

              <h4 style="margin: 20px 0 12px 0; font-weight: 600; font-size: 13px;">Show on Calendar</h4>
              <div class="switches-grid">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Hide notified reminders from calendar</span></div>
                  <a-radio-group v-model:value="settings.cal_hide_notified" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Lead Reminders</span></div>
                  <a-radio-group v-model:value="settings.cal_lead_reminders" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Customer Reminders</span></div>
                  <a-radio-group v-model:value="settings.cal_customer_reminders" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Estimate Reminders</span></div>
                  <a-radio-group v-model:value="settings.cal_estimate_reminders" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Invoice Reminders</span></div>
                  <a-radio-group v-model:value="settings.cal_invoice_reminders" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Proposal Reminders</span></div>
                  <a-radio-group v-model:value="settings.cal_proposal_reminders" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Expense Reminders</span></div>
                  <a-radio-group v-model:value="settings.cal_expense_reminders" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Task Reminders</span></div>
                  <a-radio-group v-model:value="settings.cal_task_reminders" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Credit Note Reminders</span></div>
                  <a-radio-group v-model:value="settings.cal_credit_reminders" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Ticket Reminders</span></div>
                  <a-radio-group v-model:value="settings.cal_ticket_reminders" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>

              <div class="switches-grid" style="margin-top: 12px;">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Invoices</span></div>
                  <a-radio-group v-model:value="settings.cal_invoices" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Estimates</span></div>
                  <a-radio-group v-model:value="settings.cal_estimates" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Proposals</span></div>
                  <a-radio-group v-model:value="settings.cal_proposals" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Contracts</span></div>
                  <a-radio-group v-model:value="settings.cal_contracts" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Tasks</span></div>
                  <a-radio-group v-model:value="settings.cal_tasks" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Show only tasks assigned to the logged in staff member</span></div>
                  <a-radio-group v-model:value="settings.cal_tasks_own" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Projects</span></div>
                  <a-radio-group v-model:value="settings.cal_projects" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>

            <!-- Styling Tab -->
            <div v-if="activeCalendarTab === 'styling'">
              <p style="font-size: 13px; color: #64748b;">Calendar styling options will appear here.</p>
            </div>
          </div>

          <!-- PDF -->
          <div v-else-if="activeCategory === 'pdf'">
            <h3 class="panel-section-title">PDF settings</h3>

            <!-- Tabs -->
            <div style="display: flex; gap: 4px; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
              <a-button :type="activePdfTab === 'general' ? 'primary' : 'default'" size="small" @click="activePdfTab = 'general'">General</a-button>
              <a-button :type="activePdfTab === 'signature' ? 'primary' : 'default'" size="small" @click="activePdfTab = 'signature'">Signature</a-button>
              <a-button :type="activePdfTab === 'doc_formats' ? 'primary' : 'default'" size="small" @click="activePdfTab = 'doc_formats'">Document formats</a-button>
            </div>

            <!-- General Tab -->
            <div v-if="activePdfTab === 'general'">
              <div class="settings-form-grid">
                <a-form-item label="PDF Font">
                  <a-select v-model:value="settings.pdf_font" style="width: 100%">
                    <a-select-option value="freesans">freesans</a-select-option>
                  </a-select>
                </a-form-item>
              </div>
              <div class="switches-grid" style="margin-top: 16px;">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Swap Company/Customer Details (company details to right side, customer details to left side)</span></div>
                  <a-radio-group v-model:value="settings.pdf_swap_details" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
              <div class="settings-form-grid" style="margin-top: 16px;">
                <a-form-item label="Default font size">
                  <a-input-number v-model:value="settings.pdf_font_size" :min="6" :max="20" style="width: 100%" />
                </a-form-item>
                <a-form-item label="Items table heading color">
                  <a-input v-model:value="settings.pdf_heading_color" />
                </a-form-item>
                <a-form-item label="Items table heading text color">
                  <a-input v-model:value="settings.pdf_heading_text_color" />
                </a-form-item>
                <a-form-item label="Custom PDF Company Logo URL" style="grid-column: span 2;">
                  <a-input v-model:value="settings.pdf_logo_url" />
                </a-form-item>
                <a-form-item label="Logo Width (PX)">
                  <a-input-number v-model:value="settings.pdf_logo_width" :min="50" :max="400" style="width: 100%" />
                </a-form-item>
              </div>
              <div class="switches-grid" style="margin-top: 16px;">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Show Invoice/Estimate/Credit Note status on PDF documents</span></div>
                  <a-radio-group v-model:value="settings.pdf_show_status" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Show Pay Invoice link to PDF (Not applied if invoice status is Cancelled)</span></div>
                  <a-radio-group v-model:value="settings.pdf_show_pay_link" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Show invoice payments (transactions) on PDF</span></div>
                  <a-radio-group v-model:value="settings.pdf_show_payments" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Show page number on PDF</span></div>
                  <a-radio-group v-model:value="settings.pdf_show_page_num" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>

            <!-- Signature Tab -->
            <div v-if="activePdfTab === 'signature'">
              <div class="switches-grid">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Show PDF Signature on Invoice</span></div>
                  <a-radio-group v-model:value="settings.pdf_sig_invoice" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Show PDF Signature on Estimate</span></div>
                  <a-radio-group v-model:value="settings.pdf_sig_estimate" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Show PDF Signature on Credit Note</span></div>
                  <a-radio-group v-model:value="settings.pdf_sig_credit" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Show PDF Signature on Contract</span></div>
                  <a-radio-group v-model:value="settings.pdf_sig_contract" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Show PDF Signature on Proposal</span></div>
                  <a-radio-group v-model:value="settings.pdf_sig_proposal" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
              <div class="settings-form-grid" style="margin-top: 16px;">
                <a-form-item label="Signature Image" style="grid-column: span 2;">
                  <a-upload :before-upload="() => false" :max-count="1">
                    <a-button><upload-outlined /> No file chosen</a-button>
                  </a-upload>
                </a-form-item>
              </div>
            </div>

            <!-- Document formats Tab -->
            <div v-if="activePdfTab === 'doc_formats'">
              <div class="settings-form-grid">
                <a-form-item label="Invoice">
                  <a-select v-model:value="settings.pdf_fmt_invoice" style="width: 100%">
                    <a-select-option value="a4_portrait">A4 Portrait</a-select-option>
                  </a-select>
                </a-form-item>
                <a-form-item label="Estimate">
                  <a-select v-model:value="settings.pdf_fmt_estimate" style="width: 100%">
                    <a-select-option value="a4_portrait">A4 Portrait</a-select-option>
                  </a-select>
                </a-form-item>
                <a-form-item label="Proposal">
                  <a-select v-model:value="settings.pdf_fmt_proposal" style="width: 100%">
                    <a-select-option value="a4_portrait">A4 Portrait</a-select-option>
                  </a-select>
                </a-form-item>
                <a-form-item label="Payment">
                  <a-select v-model:value="settings.pdf_fmt_payment" style="width: 100%">
                    <a-select-option value="a4_portrait">A4 Portrait</a-select-option>
                  </a-select>
                </a-form-item>
                <a-form-item label="Credit Note">
                  <a-select v-model:value="settings.pdf_fmt_credit" style="width: 100%">
                    <a-select-option value="a4_portrait">A4 Portrait</a-select-option>
                  </a-select>
                </a-form-item>
                <a-form-item label="Contract">
                  <a-select v-model:value="settings.pdf_fmt_contract" style="width: 100%">
                    <a-select-option value="a4_portrait">A4 Portrait</a-select-option>
                  </a-select>
                </a-form-item>
                <a-form-item label="Statement">
                  <a-select v-model:value="settings.pdf_fmt_statement" style="width: 100%">
                    <a-select-option value="a4_portrait">A4 Portrait</a-select-option>
                  </a-select>
                </a-form-item>
              </div>
            </div>
          </div>

          <!-- E-Sign -->
          <div v-else-if="activeCategory === 'esign'">
            <h3 class="panel-section-title">E-Sign settings</h3>

            <!-- Proposal -->
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-bottom:16px;">
              <h4 style="font-weight:700; margin: 0 0 12px 0; font-size:13.5px; color:#1e293b;">Proposal</h4>
              <div class="switches-grid">
                <div class="switch-row" style="border:none; padding-bottom:0;">
                  <div class="switch-labels"><span class="switch-title" style="font-size:13px;">Require digital signature and identity confirmation on accept</span></div>
                  <a-radio-group v-model:value="settings.esign_proposal" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>

            <!-- Estimate -->
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-bottom:16px;">
              <h4 style="font-weight:700; margin: 0 0 12px 0; font-size:13.5px; color:#1e293b;">Estimate</h4>
              <div class="switches-grid">
                <div class="switch-row" style="border:none; padding-bottom:0;">
                  <div class="switch-labels"><span class="switch-title" style="font-size:13px;">Require digital signature and identity confirmation on accept</span></div>
                  <a-radio-group v-model:value="settings.esign_estimate" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>

            <!-- Legal Bound Text -->
            <div class="settings-form-grid" style="margin-top: 16px;">
              <a-form-item label="Legal Bound Text" style="grid-column: span 2;">
                <a-textarea v-model:value="settings.esign_legal_text" :rows="3" />
              </a-form-item>
            </div>
          </div>

          <!-- Tags -->
          <div v-else-if="activeCategory === 'tags'">
            <h3 class="panel-section-title">Tags settings</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
              <a-tag v-for="tag in existingTags" :key="tag.name" :color="tag.color" style="font-size: 13px; padding: 4px 10px; cursor: default;">
                {{ tag.name }} <span style="margin-left: 4px; opacity: 0.7;">{{ tag.count }}</span>
              </a-tag>
            </div>
          </div>

          <!-- SMS -->
          <div v-else-if="activeCategory === 'sms'">
            <h3 class="panel-section-title">SMS settings</h3>
            <p style="font-size: 12.5px; color: #64748b; margin-bottom: 16px;">Only 1 active SMS gateway is allowed</p>

            <!-- Gateway Tabs -->
            <div style="display: flex; gap: 4px; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
              <a-button :type="activeSmsTab === 'clickatell' ? 'primary' : 'default'" size="small" @click="activeSmsTab = 'clickatell'">Clickatell</a-button>
              <a-button :type="activeSmsTab === 'msg91' ? 'primary' : 'default'" size="small" @click="activeSmsTab = 'msg91'">MSG91</a-button>
              <a-button :type="activeSmsTab === 'twilio' ? 'primary' : 'default'" size="small" @click="activeSmsTab = 'twilio'">Twilio</a-button>
            </div>

            <!-- Clickatell -->
            <div v-if="activeSmsTab === 'clickatell'">
              <div class="settings-form-grid">
                <a-form-item label="Bitly Access Token"><a-input v-model:value="settings.sms_clickatell_bitly" /></a-form-item>
              </div>
            </div>

            <!-- MSG91 -->
            <div v-if="activeSmsTab === 'msg91'">
              <div class="settings-form-grid">
                <a-form-item label="Auth Token"><a-input v-model:value="settings.sms_msg91_token" type="password" /></a-form-item>
                <a-form-item label="Template ID"><a-input v-model:value="settings.sms_msg91_template" /></a-form-item>
              </div>
            </div>

            <!-- Twilio -->
            <div v-if="activeSmsTab === 'twilio'">
              <div class="settings-form-grid">
                <a-form-item label="Twilio Account SID"><a-input v-model:value="settings.sms_twilio_sid" /></a-form-item>
                <a-form-item label="Twilio Auth Token"><a-input v-model:value="settings.sms_twilio_token" type="password" /></a-form-item>
                <a-form-item label="Twilio Phone Number"><a-input v-model:value="settings.sms_twilio_phone" /></a-form-item>
              </div>
            </div>

            <!-- Triggers -->
            <div style="margin-top: 24px;">
              <h4 style="font-weight: 600; margin-bottom: 12px;">Triggers</h4>
              <div style="display: grid; gap: 8px;">
                <div v-for="trigger in smsTriggers" :key="trigger.name" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:12px;">
                  <div style="font-weight: 600; font-size: 13px; margin-bottom: 4px;">{{ trigger.name }}</div>
                  <div style="font-size: 12px; color: #64748b; margin-bottom: 6px;">{{ trigger.description }}</div>
                  <a-button size="small" type="link" style="padding: 0; font-size: 12px;">Available merge fields</a-button>
                </div>
              </div>
            </div>
          </div>

          <!-- Cron Job -->
          <div v-else-if="activeCategory === 'cron'">
            <h3 class="panel-section-title">Cron Job</h3>

            <!-- Tabs -->
            <div style="display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
              <a-button :type="activeCronTab === 'command' ? 'primary' : 'default'" size="small" @click="activeCronTab = 'command'">Command</a-button>
              <a-button :type="activeCronTab === 'invoice' ? 'primary' : 'default'" size="small" @click="activeCronTab = 'invoice'">Invoice</a-button>
              <a-button :type="activeCronTab === 'estimates' ? 'primary' : 'default'" size="small" @click="activeCronTab = 'estimates'">Estimates</a-button>
              <a-button :type="activeCronTab === 'proposals' ? 'primary' : 'default'" size="small" @click="activeCronTab = 'proposals'">Proposals</a-button>
              <a-button :type="activeCronTab === 'expenses' ? 'primary' : 'default'" size="small" @click="activeCronTab = 'expenses'">Expenses</a-button>
              <a-button :type="activeCronTab === 'contracts' ? 'primary' : 'default'" size="small" @click="activeCronTab = 'contracts'">Contracts</a-button>
              <a-button :type="activeCronTab === 'tasks' ? 'primary' : 'default'" size="small" @click="activeCronTab = 'tasks'">Tasks</a-button>
              <a-button :type="activeCronTab === 'tickets' ? 'primary' : 'default'" size="small" @click="activeCronTab = 'tickets'">Tickets</a-button>
              <a-button :type="activeCronTab === 'surveys' ? 'primary' : 'default'" size="small" @click="activeCronTab = 'surveys'">Surveys</a-button>
            </div>

            <!-- Command Tab -->
            <div v-if="activeCronTab === 'command'">
              <div class="cron-instructions">
                <p style="margin-bottom: 8px;">CRON COMMAND:</p>
                <pre class="cron-command-block">wget -q -O- https://perfexcrm.com/demo/cron/index</pre>
              </div>
              <a-button type="primary" style="margin-top: 12px;">Run Cron Manually</a-button>
            </div>

            <!-- Invoice Tab -->
            <div v-if="activeCronTab === 'invoice'">
              <div class="settings-form-grid">
                <a-form-item label="Hour of day to perform automatic operations">
                  <a-input-number v-model:value="settings.cron_invoice_hour" :min="0" :max="23" style="width: 100%" />
                </a-form-item>
              </div>

              <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-top:16px;">
                <h4 style="font-weight:700; margin: 0 0 8px 0; font-size:13.5px; color:#1e293b;">Overdue Notices</h4>
                <p style="font-size:12.5px; color:#64748b; margin-bottom:12px;">Overdue notices are sent when the invoice becomes overdue.</p>
                <div class="settings-form-grid">
                  <a-form-item label="Auto send reminder after (days)"><a-input-number v-model:value="settings.cron_overdue_send" :min="0" style="width: 100%" /></a-form-item>
                  <a-form-item label="Auto re-send reminder after (days)"><a-input-number v-model:value="settings.cron_overdue_resend" :min="0" style="width: 100%" /></a-form-item>
                </div>
              </div>

              <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-top:16px;">
                <h4 style="font-weight:700; margin: 0 0 8px 0; font-size:13.5px; color:#1e293b;">Due Reminders</h4>
                <p style="font-size:12.5px; color:#64748b; margin-bottom:12px;">Due reminders are sent to unpaid and partially paid invoices as reminder to the customer to pay the invoice before is due.</p>
                <div class="settings-form-grid">
                  <a-form-item label="Send due reminder X days before due date"><a-input-number v-model:value="settings.cron_due_before" :min="0" style="width: 100%" /></a-form-item>
                  <a-form-item label="Auto re-send reminder after (days)"><a-input-number v-model:value="settings.cron_due_resend" :min="0" style="width: 100%" /></a-form-item>
                </div>
              </div>

              <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-top:16px;">
                <h4 style="font-weight:700; margin: 0 0 8px 0; font-size:13.5px; color:#1e293b;">Recurring Invoices</h4>
                <div class="settings-form-grid">
                  <a-form-item label="Generate and autosend the renewed invoice to the customer">
                    <a-select v-model:value="settings.cron_recurring_action" style="width: 100%">
                      <a-select-option value="paid">Generate a Unpaid Invoice</a-select-option>
                      <a-select-option value="draft">Generate a Draft Invoice</a-select-option>
                    </a-select>
                  </a-form-item>
                </div>
                <div class="switches-grid" style="margin-top: 12px;">
                  <div class="switch-row" style="border:none; padding-bottom:0;">
                    <div class="switch-labels"><span class="switch-title" style="font-size:13px;">Create new invoice from recurring invoice only if the invoice is with status paid?</span></div>
                    <a-radio-group v-model:value="settings.cron_recurring_only_paid" button-style="solid">
                      <a-radio-button :value="true">Yes</a-radio-button>
                      <a-radio-button :value="false">No</a-radio-button>
                    </a-radio-group>
                  </div>
                </div>
              </div>
            </div>

            <!-- Estimates Tab -->
            <div v-if="activeCronTab === 'estimates'">
              <div class="settings-form-grid">
                <a-form-item label="Hour of day to perform automatic operations"><a-input-number v-model:value="settings.cron_estimate_hour" :min="0" :max="23" style="width: 100%" /></a-form-item>
                <a-form-item label="Send expiration reminder before (DAYS)"><a-input-number v-model:value="settings.cron_estimate_expire_before" :min="0" style="width: 100%" /></a-form-item>
              </div>
            </div>

            <!-- Proposals Tab -->
            <div v-if="activeCronTab === 'proposals'">
              <div class="settings-form-grid">
                <a-form-item label="Hour of day to perform automatic operations"><a-input-number v-model:value="settings.cron_proposal_hour" :min="0" :max="23" style="width: 100%" /></a-form-item>
                <a-form-item label="Send expiration reminder before (DAYS)"><a-input-number v-model:value="settings.cron_proposal_expire_before" :min="0" style="width: 100%" /></a-form-item>
              </div>
            </div>

            <!-- Expenses Tab -->
            <div v-if="activeCronTab === 'expenses'">
              <div class="settings-form-grid">
                <a-form-item label="Hour of day to perform automatic operations"><a-input-number v-model:value="settings.cron_expense_hour" :min="0" :max="23" style="width: 100%" /></a-form-item>
              </div>
            </div>

            <!-- Contracts Tab -->
            <div v-if="activeCronTab === 'contracts'">
              <div class="settings-form-grid">
                <a-form-item label="Hour of day to perform automatic operations"><a-input-number v-model:value="settings.cron_contract_hour" :min="0" :max="23" style="width: 100%" /></a-form-item>
                <a-form-item label="Send expiration reminder before (DAYS)"><a-input-number v-model:value="settings.cron_contract_expire_before" :min="0" style="width: 100%" /></a-form-item>
              </div>
              <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-top:16px;">
                <h4 style="font-weight:700; margin: 0 0 8px 0; font-size:13.5px; color:#1e293b;">Sign Reminders</h4>
                <p style="font-size:12.5px; color:#64748b; margin-bottom:12px;">Sign reminders are sent to the customer contacts after the contract is first time sent to the customer and they are automatically stopped when the contract is signed.</p>
                <div class="settings-form-grid">
                  <a-form-item label="Send sign reminder every (days)"><a-input-number v-model:value="settings.cron_contract_sign_days" :min="0" style="width: 100%" /></a-form-item>
                </div>
              </div>
            </div>

            <!-- Tasks Tab -->
            <div v-if="activeCronTab === 'tasks'">
              <div class="settings-form-grid">
                <a-form-item label="Hour of day to perform automatic operations"><a-input-number v-model:value="settings.cron_task_hour" :min="0" :max="23" style="width: 100%" /></a-form-item>
                <a-form-item label="Task deadline reminder before (Days)"><a-input-number v-model:value="settings.cron_task_deadline_before" :min="0" style="width: 100%" /></a-form-item>
                <a-form-item label="Automatically stop task timers after (hours)"><a-input-number v-model:value="settings.cron_task_stop_timers" :min="0" style="width: 100%" /></a-form-item>
              </div>
              <div class="switches-grid" style="margin-top: 16px;">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Send an email reminder of billable tasks completed but not billed</span></div>
                  <a-radio-group v-model:value="settings.cron_task_billable_reminder" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>

            <!-- Tickets Tab -->
            <div v-if="activeCronTab === 'tickets'">
              <div class="settings-form-grid">
                <a-form-item label="Hour of day to perform automatic operations"><a-input-number v-model:value="settings.cron_ticket_hour" :min="0" :max="23" style="width: 100%" /></a-form-item>
                <a-form-item label="Auto close ticket after (Hours)"><a-input-number v-model:value="settings.cron_ticket_close_hours" :min="0" style="width: 100%" /></a-form-item>
              </div>
            </div>

            <!-- Surveys Tab -->
            <div v-if="activeCronTab === 'surveys'">
              <div class="settings-form-grid">
                <a-form-item label="Hour of day to perform automatic operations"><a-input-number v-model:value="settings.cron_survey_hour" :min="0" :max="23" style="width: 100%" /></a-form-item>
                <a-form-item label="How much emails to sent per hour"><a-input-number v-model:value="settings.cron_survey_emails_per_hour" :min="1" style="width: 100%" /></a-form-item>
              </div>
            </div>
          </div>

          <!-- Misc -->
          <div v-else-if="activeCategory === 'misc'">
            <h3 class="panel-section-title">Misc settings</h3>

            <!-- Tabs -->
            <div style="display: flex; gap: 4px; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
              <a-button :type="activeMiscTab === 'misc' ? 'primary' : 'default'" size="small" @click="activeMiscTab = 'misc'">Misc</a-button>
              <a-button :type="activeMiscTab === 'tables' ? 'primary' : 'default'" size="small" @click="activeMiscTab = 'tables'">Tables</a-button>
              <a-button :type="activeMiscTab === 'inline_create' ? 'primary' : 'default'" size="small" @click="activeMiscTab = 'inline_create'">Inline Create</a-button>
            </div>

            <!-- Misc Tab -->
            <div v-if="activeMiscTab === 'misc'">
              <div class="switches-grid">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Require client to be logged in to view contract</span></div>
                  <a-radio-group v-model:value="settings.misc_contract_login" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
              <div class="settings-form-grid" style="margin-top: 16px;">
                <a-form-item label="Dropbox APP Key"><a-input v-model:value="settings.misc_dropbox_key" /></a-form-item>
                <a-form-item label="Max file size upload in Media (MB)"><a-input-number v-model:value="settings.misc_max_upload_mb" :min="1" style="width: 100%" /></a-form-item>
                <a-form-item label="Maximum files upload on post"><a-input-number v-model:value="settings.misc_max_files_post" :min="1" style="width: 100%" /></a-form-item>
                <a-form-item label="Limit Top Search Bar Results to"><a-input-number v-model:value="settings.misc_search_limit" :min="1" style="width: 100%" /></a-form-item>
                <a-form-item label="Default Staff Role">
                  <a-select v-model:value="settings.misc_default_role" style="width: 100%">
                    <a-select-option value="employee">Employee</a-select-option>
                  </a-select>
                </a-form-item>
                <a-form-item label="Delete system activity log older then X months"><a-input-number v-model:value="settings.misc_log_delete_months" :min="0" style="width: 100%" /></a-form-item>
              </div>
              <div class="switches-grid" style="margin-top: 16px;">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Show setup menu item only when hover with mouse on main sidebar area</span></div>
                  <a-radio-group v-model:value="settings.misc_sidebar_hover" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Show help menu item on setup menu</span></div>
                  <a-radio-group v-model:value="settings.misc_show_help" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Use minified files version for css and js (only system files)</span></div>
                  <a-radio-group v-model:value="settings.misc_use_minified" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>

            <!-- Tables Tab -->
            <div v-if="activeMiscTab === 'tables'">
              <div class="switches-grid">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Save last order for tables</span></div>
                  <a-radio-group v-model:value="settings.misc_table_save_order" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
              <div class="settings-form-grid" style="margin-top: 16px;">
                <a-form-item label="Show table export button">
                  <a-select v-model:value="settings.misc_table_export" style="width: 100%">
                    <a-select-option value="all">To all staff members</a-select-option>
                    <a-select-option value="admin">Only to administrators</a-select-option>
                    <a-select-option value="none">Hide export button for all staff members</a-select-option>
                  </a-select>
                </a-form-item>
                <a-form-item label="Tables Pagination Limit"><a-input-number v-model:value="settings.misc_table_pagination" :min="5" style="width: 100%" /></a-form-item>
              </div>
            </div>

            <!-- Inline Create Tab -->
            <div v-if="activeMiscTab === 'inline_create'">
              <div class="switches-grid">
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Allow non-admin staff members to create Lead Status in Lead create/edit area?</span></div>
                  <a-radio-group v-model:value="settings.misc_inline_lead_status" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Allow non-admin staff members to create Lead Source in Lead create/edit area?</span></div>
                  <a-radio-group v-model:value="settings.misc_inline_lead_source" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Allow non-admin staff members to create Customer Group in Customer create/edit area?</span></div>
                  <a-radio-group v-model:value="settings.misc_inline_cust_group" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Allow non-admin staff members to create Service in Ticket create/edit area?</span></div>
                  <a-radio-group v-model:value="settings.misc_inline_service" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Allow non-admin staff members to save predefined replies from ticket message</span></div>
                  <a-radio-group v-model:value="settings.misc_inline_predefined" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Allow non-admin staff members to create Contract type in Contract create/edit area?</span></div>
                  <a-radio-group v-model:value="settings.misc_inline_contract_type" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
                <div class="switch-row">
                  <div class="switch-labels"><span class="switch-title">Allow non-admin staff members to create Expense Category in Expense create/edit area?</span></div>
                  <a-radio-group v-model:value="settings.misc_inline_expense_cat" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>
            </div>
          </div>

          <!-- SAVE ACTION -->
          <div class="settings-actions">
            <a-button type="primary" :loading="saving" @click="saveAllSettings">
              Save Settings
            </a-button>
          </div>

        </a-form>
      </div>

    </div>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed } from 'vue';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'SettingsView',
  setup() {
    const activeCategory = ref('general');
    const activeGateway = ref('general');
    const activeSupportTab = ref('general');
    const activeCalendarTab = ref('general');
    const activePdfTab = ref('general');
    const activeSmsTab = ref('clickatell');
    const activeCronTab = ref('command');
    const activeMiscTab = ref('misc');
    const showOpenAIFineTune = ref(false);
    const saving = ref(false);

    const paymentGateways = [
      { key: 'general', label: 'General' },
      { key: 'authorize_net', label: 'Authorize.net Accept.js' },
      { key: 'instamojo', label: 'Instamojo' },
      { key: 'mollie', label: 'Mollie' },
      { key: 'braintree', label: 'Braintree' },
      { key: 'paypal_smart', label: 'Paypal Smart Checkout' },
      { key: 'paypal', label: 'Paypal' },
      { key: 'payu', label: 'PayU Money' },
      { key: 'stripe_checkout', label: 'Stripe Checkout' },
      { key: 'stripe_ideal', label: 'Stripe iDEAL' },
      { key: '2checkout', label: '2Checkout' },
    ];

    // Email Queue states
    const queuePageSize = ref(25);
    const queueSearch = ref('');
    const queueList = ref([]);

    const groupedCategories = [
      {
        group: 'General',
        items: [
          { key: 'general', label: 'General' },
          { key: 'company', label: 'Company Information' },
          { key: 'localization', label: 'Localization' },
          { key: 'email', label: 'Email' },
          { key: 'update', label: 'System Update' },
          { key: 'system_info', label: 'System/Server Info' }
        ]
      },
      {
        group: 'Finance',
        items: [
          { key: 'finance_general', label: 'General' },
          { key: 'invoices', label: 'Invoices' },
          { key: 'proposals', label: 'Proposals' },
          { key: 'estimates', label: 'Estimates' },
          { key: 'credit_notes', label: 'Credit Notes' },
          { key: 'subscriptions', label: 'Subscriptions' },
          { key: 'payment_gateways', label: 'Payment Gateways' },
          { key: 'e_invoice', label: 'e-Invoice' }
        ]
      },
      {
        group: 'Configure Features',
        items: [
          { key: 'customers', label: 'Customers' },
          { key: 'tasks', label: 'Tasks' },
          { key: 'support', label: 'Support' },
          { key: 'leads', label: 'Leads' }
        ]
      },
      {
        group: 'Integrations',
        items: [
          { key: 'google', label: 'Google' },
          { key: 'pusher', label: 'Pusher.com' }
        ]
      },
      {
        group: 'AI Integration',
        items: [
          { key: 'ai_general', label: 'General' },
          { key: 'openai', label: 'OpenAI' }
        ]
      },
      {
        group: 'Other',
        items: [
          { key: 'calendar', label: 'Calendar' },
          { key: 'pdf', label: 'PDF' },
          { key: 'esign', label: 'E-Sign' },
          { key: 'tags', label: 'Tags' },
          { key: 'sms', label: 'SMS' },
          { key: 'cron', label: 'Cron Job' },
          { key: 'misc', label: 'Misc' }
        ]
      }
    ];

    const availableLanguages = [
      'Norwegian', 'Portuguese_br', 'Bulgarian', 'Italian', 'Czech', 'Persian',
      'French', 'English', 'Finnish', 'Francais_canada', 'Indonesia', 'Portuguese',
      'Japanese', 'Spanish', 'Dutch', 'Swedish', 'Ukrainian', 'Vietnamese',
      'Turkish', 'Chinese', 'Romanian', 'Slovak', 'Russian', 'German',
      'Greek', 'Polish', 'Catalan'
    ];

    const settings = reactive({
      // General Settings
      site_name: 'Perfex CRM',
      company_domain: 'https://perfexcrm.com',
      rtl_admin: false,
      rtl_cust: false,
      allowed_file_types: '.png,.jpg,.pdf,.doc,.docx,.xls,.xlsx,.zip,.rar,.txt',

      // Company Info
      company_info_name: 'Perfex INC',
      company_info_address: '172 Ivy Club Gottliebfurt',
      company_info_city: 'New Heaven',
      company_info_state: '',
      company_info_country: 'CA',
      company_info_zip: '2364',
      company_info_phone: '',
      company_info_vat: '',
      company_info_format: `{company_name}\n{address}\n{city} {state}\n{country_code} {zip_code}\n{vat_number_with_label}`,

      // Localization
      date_format: 'Y-m-d',
      time_format: '24',
      timezone: 'Europe/Berlin',
      language: 'english',
      enabled_languages: ['English', 'French', 'Spanish', 'German'],
      disable_languages: false,
      pdf_in_client_lang: true,

      // Email SMTP
      email_tab: 'smtp',
      mail_engine: 'PHPMailer',
      email_protocol: 'SMTP',
      email_encryption: 'TLS',
      smtp_host: 'smtp.mailtrap.io',
      smtp_port: 2525,
      from_email: 'noreply@perfexcrm.com',
      smtp_user: 'smtp_username_placeholder',
      smtp_pass: '••••••••••••••••',
      email_charset: 'utf-8',
      bcc_emails: '',
      email_signature: '',
      test_email_address: '',
      email_predefined_header: `<!doctype html>
<html>
<head>
<meta name="viewport" content="width=device-width" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style>
body {
  background-color: #f6f6f6;
  font-family: sans-serif;
  -webkit-font-smoothing: antialiased;
  font-size: 14px;
  line-height: 1.4;
  margin: 0;
  padding: 0;
  -ms-text-size-adjust: 100%;
  -webkit-text-size-adjust: 100%;
}
table {
  border-collapse: separate;
  mso-table-lspace: 0pt;
  mso-table-rspace: 0pt;
  width: 100%;
}
table td {
  font-family: sans-serif;
  font-size: 14px;
  vertical-align: top;
}
.body {
  background-color: #f6f6f6;
  width: 100%;
}
.container {
  display: block;
  margin: 0 auto !important;
  max-width: 680px;
  padding: 10px;
  width: 680px;
}
.content {
  box-sizing: border-box;
  display: block;
  margin: 0 auto;
  max-width: 680px;
  padding: 10px;
}
.main {
  background: #fff;
  border-radius: 3px;
  width: 100%;
}
.wrapper {
  box-sizing: border-box;
  padding: 20px;
}
.footer {
  clear: both;
  padding-top: 10px;
  text-align: center;
  width: 100%;
}
.footer td,
.footer p,
.footer span,
.footer a {
  color: #999999;
  font-size: 12px;
  text-align: center;
}
hr {
  border: 0;
  border-bottom: 1px solid #f6f6f6;
  margin: 20px 0;
}
@media only screen and (max-width: 620px) {
  table[class=body] .content {
    padding: 0 !important;
  }
  table[class=body] .container {
    padding: 0 !important;
    width: 100% !important;
  }
  table[class=body] .main {
    border-left-width: 0 !important;
    border-radius: 0 !important;
    border-right-width: 0 !important;
  }
}
</style>
</head>
<body class="">
<table border="0" cellpadding="0" cellspacing="0" class="body">
<tr>
<td>&nbsp;</td>
<td class="container">
<div class="content">
<table class="main">
<tr>
<td class="wrapper">
<table border="0" cellpadding="0" cellspacing="0">
<tr>
<td>`,
      email_predefined_footer: `</td>
</tr>
</table>
</td>
</tr>
</table>
<div class="footer">
<table border="0" cellpadding="0" cellspacing="0">
<tr>
<td class="content-block">
<span>{companyname}</span>
</td>
</tr>
</table>
</div>
</div>
</td>
<td>&nbsp;</td>
</tr>
</table>
</body>
</html>`,

      // Email Queue Config
      enable_queue: false,
      queue_no_attachments: true,

      // System Update
      license_key: '••••-••••-••••-••••',
      upgrade_function: 'new',

      // General Finance
      finance_decimal_separator: '.',
      finance_thousand_separator: ',',
      finance_number_padding: 6,
      finance_auto_sale_agent: false,
      finance_show_tax_per_item: true,
      finance_remove_tax_name: false,
      finance_exclude_currency_symbol: false,
      finance_default_tax: '18.00',
      finance_remove_decimals_zero: true,
      finance_amount_to_words_enabled: true,
      finance_amount_to_words_lowercase: false,
      tax_default: 'TAX1',
      decimal_places: 2,

      // Invoice Settings
      invoice_prefix: 'INV-',
      next_invoice_number: 18,
      invoice_due_days: 30,
      invoice_staff_view: false,
      invoice_require_login: false,
      invoice_delete_last_only: false,
      invoice_decrement_on_delete: false,
      invoice_exclude_draft: false,
      invoice_show_sale_agent: false,
      invoice_show_project: false,
      invoice_show_total_paid: false,
      invoice_show_credits: false,
      invoice_show_amount_due: false,
      invoice_attach_pdf_receipt: false,
      invoice_number_format: 'number_based',
      invoice_predefined_note: '',
      invoice_predefined_terms: '',

      // Proposal Settings
      proposal_prefix: 'PROP-',
      proposal_due: 7,
      proposal_pipeline_limit: 50,
      proposal_pipeline_sort: 'pipeline_order',
      proposal_pipeline_order: 'ascending',
      proposal_show_project: false,
      proposal_exclude_draft: false,
      proposal_auto_convert: false,
      proposal_staff_view: false,
      proposal_info_format: `{proposal_to}\n{address}\n{city} {state}\n{country_code} {zip_code}\n{phone}\n{email}`,

      // Estimate Settings
      estimate_prefix: 'EST-',
      next_estimate_number: 14,
      estimate_due: 7,
      estimate_delete_last_only: false,
      estimate_decrement_on_delete: false,
      estimate_staff_view: false,
      estimate_require_login: false,
      estimate_show_sale_agent: false,
      estimate_show_project: false,
      estimate_auto_convert: false,
      estimate_exclude_draft: false,
      estimate_number_format: 'number_based',
      estimate_pipeline_limit: 50,
      estimate_pipeline_sort: 'pipeline_order',
      estimate_pipeline_order: 'ascending',
      estimate_predefined_note: '',
      estimate_predefined_terms: '',

      // Credit Note Settings
      credit_prefix: 'CN-',
      next_credit: 1,
      credit_number_format: 'number_based',
      credit_decrement_on_delete: false,
      credit_show_project: false,
      credit_predefined_note: '',
      credit_predefined_terms: '',

      // Subscription Settings
      subscription_show_customers: false,
      subscription_payment_action: 'send_invoice_receipt',
      subscription_email_template: 'default',

      // Payment Gateway Settings
      payment_notify_invoice: false,
      payment_allow_modify_amount: false,

      // Authorize.net
      gw_authorize_active: false, gw_authorize_label: 'Authorize.net Accept.js', gw_authorize_public_key: '', gw_authorize_api_login: '', gw_authorize_txn_id: '', gw_authorize_desc: 'Payment for Invoice {invoice_number}', gw_authorize_currency: 'USD', gw_authorize_test: false, gw_authorize_default: false,
      // Instamojo
      gw_instamojo_active: false, gw_instamojo_label: 'Instamojo', gw_instamojo_fixed_fee: 0, gw_instamojo_pct_fee: 0, gw_instamojo_api_key: '', gw_instamojo_auth_token: '', gw_instamojo_desc: 'Payment for Invoice {invoice_number}', gw_instamojo_currencies: 'INR', gw_instamojo_test: false, gw_instamojo_default: false,
      // Mollie
      gw_mollie_active: false, gw_mollie_label: 'Mollie', gw_mollie_api_key: '', gw_mollie_desc: 'Payment for Invoice {invoice_number}', gw_mollie_currency: 'EUR', gw_mollie_test: false, gw_mollie_default: false,
      // Braintree
      gw_braintree_active: false, gw_braintree_label: 'Braintree', gw_braintree_merchant_id: '', gw_braintree_public_key: '', gw_braintree_private_key: '', gw_braintree_currencies: 'USD', gw_braintree_paypal: false, gw_braintree_test: false, gw_braintree_default: false,
      // Paypal Smart Checkout
      gw_paypal_smart_active: false, gw_paypal_smart_label: 'Paypal Smart Checkout', gw_paypal_smart_fixed_fee: 0, gw_paypal_smart_pct_fee: 0, gw_paypal_smart_client_id: '', gw_paypal_smart_secret: '', gw_paypal_smart_desc: 'Payment for Invoice {invoice_number}', gw_paypal_smart_currencies: 'USD,CAD,EUR', gw_paypal_smart_test: false, gw_paypal_smart_default: false,
      // Paypal
      gw_paypal_active: false, gw_paypal_label: 'Paypal', gw_paypal_fixed_fee: 0, gw_paypal_pct_fee: 0, gw_paypal_api_user: '', gw_paypal_api_pass: '', gw_paypal_api_sig: '', gw_paypal_desc: 'Payment for Invoice {invoice_number}', gw_paypal_currencies: 'EUR,USD', gw_paypal_test: false, gw_paypal_default: false,
      // PayU Money
      gw_payu_active: false, gw_payu_label: 'PayU Money', gw_payu_fixed_fee: 0, gw_payu_pct_fee: 0, gw_payu_key: '', gw_payu_salt: '', gw_payu_desc: 'Payment for Invoice {invoice_number}', gw_payu_currency: 'INR', gw_payu_test: false, gw_payu_default: false,
      // Stripe Checkout
      gw_stripe_active: false, gw_stripe_label: 'Stripe Checkout', gw_stripe_fixed_fee: 0, gw_stripe_pct_fee: 0, gw_stripe_pub_key: '', gw_stripe_secret: '', gw_stripe_desc: 'Payment for Invoice {invoice_number}', gw_stripe_currencies: 'USD,CAD', gw_stripe_allow_card_update: false, gw_stripe_default: false,
      // Stripe iDEAL
      gw_stripe_ideal_active: false, gw_stripe_ideal_label: 'Stripe iDEAL', gw_stripe_ideal_secret: '', gw_stripe_ideal_pub: '', gw_stripe_ideal_desc: 'Payment for Invoice {invoice_number}', gw_stripe_ideal_statement: 'Payment for Invoice {invoice_number}', gw_stripe_ideal_currencies: 'EUR', gw_stripe_ideal_default: false,
      // 2Checkout
      gw_2checkout_active: false, gw_2checkout_label: '2Checkout', gw_2checkout_fixed_fee: 0, gw_2checkout_pct_fee: 0, gw_2checkout_merchant: '', gw_2checkout_secret: '', gw_2checkout_desc: 'Payment for Invoice {invoice_number}', gw_2checkout_currencies: 'USD, EUR, GBP', gw_2checkout_test: false, gw_2checkout_default: false,

      // e-Invoice Settings
      einvoice_default_template: '',
      einvoice_attach_invoice: false,
      einvoice_credit_template: '',
      einvoice_attach_credit: false,

      // Configure Features - Customers
      cust_theme: 'perfex',
      cust_default_country: '',
      cust_visible_tabs: ['notes', 'statements', 'invoices', 'payments', 'proposals', 'credit_notes', 'estimates', 'subscriptions', 'expenses', 'contracts', 'projects', 'tasks', 'tickets', 'files', 'vault', 'reminders', 'map'],
      cust_required_fields: ['first_name', 'last_name', 'email'],
      cust_company_required: false,
      cust_company_vat_required: false,
      cust_allow_register: false,
      cust_require_admin_confirm: false,
      cust_primary_manage_contacts: false,
      cust_honeypot: false,
      cust_primary_billing: false,
      cust_contacts_own_files: false,
      cust_contacts_delete_files: false,
      cust_use_kb: false,
      cust_kb_no_register: false,
      cust_estimate_request: false,
      cust_default_permissions: [],
      cust_info_format: '{company_name}\n{street}\n{city} {state}\n{country_code} {zip_code}\n{vat_number_with_label}',

      // Configure Features - Tasks
      task_kanban_limit: 50,
      task_all_staff_view: false,
      task_comment_first_hour: false,
      task_auto_assign_creator: false,
      task_auto_follower_creator: false,
      task_stop_other_timers: false,
      task_status_on_timer: false,
      task_billable_default: false,
      task_round_mode: 'none',
      task_round_minutes: 5,
      task_default_status: 'auto',
      task_priority: 'medium',
      task_modal_width: 'modal-lg',

      // Configure Features - Support
      support_use_services: false,
      support_disable_public_url: false,
      support_staff_dept_only: false,
      support_notify_assignee_only: false,
      support_notify_new_ticket: false,
      support_notify_customer_reply: false,
      support_staff_all_contacts: false,
      support_auto_assign_first_reply: false,
      support_non_staff_access: false,
      support_non_admin_delete_attachments: false,
      support_non_admin_delete_tickets: false,
      support_customer_change_status: false,
      support_customer_own_tickets: false,
      support_menu_badge: false,
      support_replies_order: 'ascending',
      support_default_reply_status: 'answered',
      support_max_attachments: 4,
      support_allowed_extensions: '.jpg,.jpeg,.png,.pdf,.doc,.zip,.rar',
      support_cpanel_path: '',
      support_pipe_registered_only: false,
      support_email_replies_only: false,
      support_import_reply_only: false,
      support_piped_priority: 'medium',
      support_form_url: 'https://perfexcrm.com/demo/forms/ticket',
      support_form_file: '',
      support_form_embed: '<iframe width="600" height="850" src="https://perfexcrm.com/demo/forms/ticket" frameborder="0" allowfullscreen></iframe>',
      support_form_direct_link: 'https://perfexcrm.com/demo/forms/ticket?styled=1',
      support_form_direct_link_styled: 'https://perfexcrm.com/demo/forms/ticket?styled=1&with_logo=1',
      support_form_col_default: 'https://perfexcrm.com/demo/forms/ticket?col=col-md-8',
      support_form_col_offset: 'https://perfexcrm.com/demo/forms/ticket?col=col-md-8+col-md-offset-2',
      support_form_col_narrow: 'https://perfexcrm.com/demo/forms/ticket?col=col-md-5',
      support_form_language: 'https://perfexcrm.com/demo/forms/ticket?language=english',

      // Configure Features - Leads
      leads_kanban_limit: 50,
      leads_default_status: '',
      leads_default_source: '',
      leads_duplicate_fields: [],
      leads_auto_assign_admin: false,
      leads_non_admin_import: false,
      leads_no_edit_after_convert: false,
      leads_kanban_sort: 'kanban_order',
      leads_kanban_order: 'ascending',
      leads_modal_width: 'modal-lg',

      // Integrations - Google
      google_api_key: 'AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
      google_client_id: 'XXXXXXXXXXXX-XXXXXXXXXXXXXXXX.apps.googleusercontent.com',
      recaptcha_site_key: '',
      recaptcha_secret_key: '',
      recaptcha_enable_customer: false,
      recaptcha_ignored_ips: '',
      google_calendar_id: '',
      google_picker_enabled: false,

      // Integrations - Pusher
      pusher_app_id: '123456',
      pusher_key: 'PUSHER_KEY_SAMPLE',
      pusher_secret: 'PUSHER_SECRET_SAMPLE',
      pusher_cluster: 'us2',
      pusher_realtime: false,
      pusher_desktop: false,
      pusher_dismiss_seconds: 0,

      // AI Integration - General
      ai_provider: 'system',
      ai_system_prompt: 'You are a support representative',
      ai_ticket_summary: false,
      ai_ticket_reply: false,

      // AI Integration - OpenAI
      openai_key: 'sk-proj-XXXXXXXXXXXXXXXXXXXXXXXX',
      openai_model: 'gpt-4o',
      openai_max_tokens: 500,
      openai_finetune_model: 'gpt-4o-mini',

      // Calendar
      cal_events_limit: 4,
      cal_view: 'month',
      cal_first_day: 'sunday',
      cal_hide_notified: false,
      cal_lead_reminders: false,
      cal_customer_reminders: false,
      cal_estimate_reminders: false,
      cal_invoice_reminders: false,
      cal_proposal_reminders: false,
      cal_expense_reminders: false,
      cal_task_reminders: false,
      cal_credit_reminders: false,
      cal_ticket_reminders: false,
      cal_invoices: false,
      cal_estimates: false,
      cal_proposals: false,
      cal_contracts: false,
      cal_tasks: false,
      cal_tasks_own: false,
      cal_projects: false,

      // PDF - General
      pdf_font: 'freesans',
      pdf_swap_details: false,
      pdf_font_size: 10,
      pdf_heading_color: '#e5e7eb',
      pdf_heading_text_color: '#030712',
      pdf_logo_url: 'https://perfexcrm.com/demo/pdflogo.jpg',
      pdf_logo_width: 150,
      pdf_show_status: false,
      pdf_show_pay_link: false,
      pdf_show_payments: false,
      pdf_show_page_num: false,
      // PDF - Signature
      pdf_sig_invoice: false,
      pdf_sig_estimate: false,
      pdf_sig_credit: false,
      pdf_sig_contract: false,
      pdf_sig_proposal: false,
      // PDF - Document formats
      pdf_fmt_invoice: 'a4_portrait',
      pdf_fmt_estimate: 'a4_portrait',
      pdf_fmt_proposal: 'a4_portrait',
      pdf_fmt_payment: 'a4_portrait',
      pdf_fmt_credit: 'a4_portrait',
      pdf_fmt_contract: 'a4_portrait',
      pdf_fmt_statement: 'a4_portrait',

      // E-Sign
      esign_proposal: false,
      esign_estimate: false,
      esign_legal_text: 'By clicking on "Sign", I consent to be legally bound by this electronic representation of my signature.',

      // SMS
      sms_clickatell_bitly: '',
      sms_msg91_token: '',
      sms_msg91_template: '',
      sms_twilio_sid: '',
      sms_twilio_token: '',
      sms_twilio_phone: '',

      // Cron - Invoice
      cron_invoice_hour: 21,
      cron_overdue_send: 1,
      cron_overdue_resend: 3,
      cron_due_before: 2,
      cron_due_resend: 0,
      cron_recurring_action: 'paid',
      cron_recurring_only_paid: false,
      // Cron - Estimates
      cron_estimate_hour: 9,
      cron_estimate_expire_before: 4,
      // Cron - Proposals
      cron_proposal_hour: 9,
      cron_proposal_expire_before: 4,
      // Cron - Expenses
      cron_expense_hour: 9,
      // Cron - Contracts
      cron_contract_hour: 9,
      cron_contract_expire_before: 4,
      cron_contract_sign_days: 0,
      // Cron - Tasks
      cron_task_hour: 9,
      cron_task_deadline_before: 2,
      cron_task_stop_timers: 8,
      cron_task_billable_reminder: false,
      // Cron - Tickets
      cron_ticket_hour: 9,
      cron_ticket_close_hours: 0,
      // Cron - Surveys
      cron_survey_hour: 9,
      cron_survey_emails_per_hour: 100,

      // Misc - Misc
      misc_contract_login: false,
      misc_dropbox_key: '',
      misc_max_upload_mb: 10,
      misc_max_files_post: 10,
      misc_search_limit: 10,
      misc_default_role: 'employee',
      misc_log_delete_months: 1,
      misc_sidebar_hover: false,
      misc_show_help: false,
      misc_use_minified: false,
      // Misc - Tables
      misc_table_save_order: false,
      misc_table_export: 'all',
      misc_table_pagination: 25,
      // Misc - Inline Create
      misc_inline_lead_status: false,
      misc_inline_lead_source: false,
      misc_inline_cust_group: false,
      misc_inline_service: false,
      misc_inline_predefined: false,
      misc_inline_contract_type: false,
      misc_inline_expense_cat: false,
    });

    // Load from localStorage if present
    try {
      const savedSettings = localStorage.getItem('perfex_settings');
      if (savedSettings) {
        Object.assign(settings, JSON.parse(savedSettings));
      }
    } catch (e) {
      console.error('Failed to load settings from localStorage', e);
    }

    const einvoiceTemplates = ref([]);

    const existingTags = [
      { name: 'bug', count: 4, color: 'red' },
      { name: 'follow up', count: 21, color: 'orange' },
      { name: 'important', count: 9, color: 'gold' },
      { name: 'logo', count: 8, color: 'green' },
      { name: 'review', count: 5, color: 'blue' },
      { name: 'todo', count: 4, color: 'purple' },
      { name: 'tomorrow', count: 15, color: 'cyan' },
      { name: 'wordpress', count: 10, color: 'geekblue' },
    ];

    const smsTriggers = [
      { name: 'Invoice Overdue Notice', description: 'Trigger when invoice overdue notice is sent to customer contacts.' },
      { name: 'Invoice Due Notice', description: 'Trigger when invoice due notice is sent to customer contacts.' },
      { name: 'Invoice Payment Recorded', description: 'Trigger when invoice payment is recorded.' },
      { name: 'Estimate Expiration Reminder', description: 'Trigger when expiration reminder should be sent to customer contacts.' },
      { name: 'Proposal Expiration Reminder', description: 'Trigger when expiration reminder should be sent to proposal.' },
      { name: 'New Comment on Proposal (to customer)', description: 'Trigger when staff member comments on proposal, SMS will be sent to proposal number (customer/lead).' },
      { name: 'New Comment on Proposal (to staff)', description: 'Trigger when customer/lead comments on proposal, SMS will be sent to proposal creator and assigned staff member.' },
      { name: 'New Comment on Contract (to customer)', description: 'Trigger when staff member add comment to contract, SMS will be sent customer contacts.' },
      { name: 'New Comment on Contract (to staff)', description: 'Trigger when customer add comment to contract, SMS will be sent to contract creator.' },
      { name: 'Contract Expiration Reminder', description: 'Trigger when expiration reminder should be sent via Cron Job to customer contacts.' },
      { name: 'Contract Sign Reminder', description: 'Trigger when the contract is first time sent to the customer and automatically stopped when the contract is signed.' },
      { name: 'Staff Reminder', description: 'Trigger when staff is notified for a specific custom reminder.' },
    ];

    const queueColumns = [
      { title: 'Subject', dataIndex: 'subject', key: 'subject' },
      { title: 'To', dataIndex: 'to', key: 'to', width: 180 },
      { title: 'Status', dataIndex: 'status', key: 'status', width: 120 }
    ];

    const filteredQueue = computed(() => {
      if (!queueSearch.value) return queueList.value;
      const q = queueSearch.value.toLowerCase();
      return queueList.value.filter(item => 
        item.subject.toLowerCase().includes(q) || 
        item.to.toLowerCase().includes(q)
      );
    });

    const sendTestEmail = () => {
      if (!settings.test_email_address) {
        message.warning('Please enter a test email address recipient');
        return;
      }
      message.loading({ content: 'Sending test email...', key: 'sendtest' });
      setTimeout(() => {
        message.success({ content: `Test email sent successfully to ${settings.test_email_address}`, key: 'sendtest', duration: 3 });
      }, 1000);
    };

    const saveAllSettings = () => {
      saving.value = true;
      try {
        localStorage.setItem('perfex_settings', JSON.stringify(settings));
      } catch (e) {
        console.error('Failed to save settings to localStorage', e);
      }
      setTimeout(() => {
        saving.value = false;
        message.success('General Settings saved successfully');
      }, 500);
    };

    return {
      activeCategory,
      activeGateway,
      activeSupportTab,
      activeCalendarTab,
      activePdfTab,
      activeSmsTab,
      activeCronTab,
      activeMiscTab,
      showOpenAIFineTune,
      saving,
      groupedCategories,
      availableLanguages,
      settings,
      einvoiceTemplates,
      paymentGateways,
      existingTags,
      smsTriggers,
      queuePageSize,
      queueSearch,
      queueColumns,
      filteredQueue,
      sendTestEmail,
      saveAllSettings
    };
  }
});
</script>

<style scoped>
.section-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}
.section-title {
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
.settings-layout-wrap {
  display: flex;
  gap: 24px;
  background: #fff;
  border-radius: 12px;
  border: 1px solid #f1f5f9;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.settings-nav-sidebar {
  width: 265px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 16px;
  border-right: 1px solid #f1f5f9;
  padding-right: 16px;
  max-height: 800px;
  overflow-y: auto;
}
.nav-group-container {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.nav-group-header {
  font-size: 11px;
  font-weight: 800;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 6px;
  padding-left: 10px;
}
.nav-group-items {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.settings-nav-item {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  color: #64748b;
  transition: all 0.15s ease;
}
.settings-nav-item:hover {
  background: #f8fafc;
  color: #0f172a;
}
.settings-nav-item--active {
  background: #e0e7ff;
  color: #4f46e5;
  font-weight: 600;
}
.nav-label {
  font-size: 13px;
}
.settings-content-panel {
  flex: 1;
  min-height: 520px;
  padding-left: 8px;
}
.panel-section-title {
  font-size: 15px;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 24px;
  padding-bottom: 8px;
  border-bottom: 1px solid #f8fafc;
}
.settings-sub-section-title {
  font-size: 13px;
  font-weight: 700;
  color: #334155;
  margin-bottom: 16px;
}
.settings-form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px 24px;
}
.settings-hint-text {
  font-size: 12.5px;
  color: #64748b;
  margin-bottom: 20px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  padding: 10px 14px;
  border-radius: 6px;
}
.languages-grid-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
  margin-top: 8px;
  max-height: 200px;
  overflow-y: auto;
}
.languages-grid-cols {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}
.email-sub-tabs :deep(.ant-tabs-nav) {
  margin-bottom: 16px;
}
.code-textarea {
  font-family: monospace;
  font-size: 12px;
  line-height: 1.5;
  background: #0f172a;
  color: #e2e8f0;
  border-radius: 8px;
  padding: 16px;
  border: none;
}
.code-textarea:focus {
  background: #0f172a;
  color: #e2e8f0;
  box-shadow: none;
}
.test-email-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
  margin-top: 24px;
}
.test-email-title {
  font-size: 13px;
  font-weight: 700;
  margin: 0 0 6px 0;
  color: #1e293b;
}
.test-email-desc {
  font-size: 12.5px;
  color: #64748b;
  margin: 0 0 16px 0;
}
.test-email-controls {
  display: flex;
  gap: 12px;
  align-items: flex-end;
}
.switches-grid {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.switch-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 14px;
  border-bottom: 1px solid #f8fafc;
}
.switch-labels {
  display: flex;
  flex-direction: column;
  gap: 3px;
  max-width: 80%;
}
.switch-title {
  font-size: 13.5px;
  font-weight: 600;
  color: #1e293b;
}
.switch-hint {
  font-size: 12px;
  color: #64748b;
  margin: 0;
}
.data-table-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}
.info-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
}
.info-table td {
  padding: 12px 16px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 13px;
  color: #475569;
}
.info-table tr:hover td {
  background: #f8fafc;
}
.pulse-indicator {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #10b981;
  box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
  animation: pulse 1.6s infinite;
}
@keyframes pulse {
  0% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
  }
  70% {
    transform: scale(1);
    box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
  }
  100% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
  }
}
.settings-actions {
  margin-top: 32px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
}

/* ── Enhanced UI/UX Styles ── */
:deep(.ant-radio-group) {
  border-radius: 6px;
}
:deep(.ant-radio-button-wrapper) {
  font-size: 12.5px;
  font-weight: 500;
  min-width: 52px;
  text-align: center;
}
:deep(.ant-radio-button-wrapper:first-child) {
  border-radius: 6px 0 0 6px;
}
:deep(.ant-radio-button-wrapper:last-child) {
  border-radius: 0 6px 6px 0;
}
:deep(.ant-form-item-label > label) {
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
}
:deep(.ant-select-selector) {
  border-radius: 6px !important;
}
:deep(.ant-input),
:deep(.ant-input-number),
:deep(.ant-input-affix-wrapper) {
  border-radius: 6px;
}
:deep(.ant-btn) {
  border-radius: 6px;
  font-weight: 500;
}
:deep(.ant-btn-primary) {
  box-shadow: 0 1px 2px rgba(79, 70, 229, 0.2);
}
:deep(.ant-table) {
  border-radius: 8px;
  overflow: hidden;
}
:deep(.ant-modal-content) {
  border-radius: 12px;
}
:deep(.ant-upload) {
  width: 100%;
}
:deep(.ant-upload .ant-btn) {
  width: 100%;
  text-align: left;
}
:deep(.ant-form-item-extra) {
  font-size: 12px;
  color: #64748b;
  margin-top: 4px;
}
.cron-instructions {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
}
.cron-command-block {
  font-family: 'SF Mono', 'Fira Code', 'Fira Mono', Menlo, Consolas, monospace;
  font-size: 12.5px;
  background: #0f172a;
  color: #a5f3fc;
  padding: 14px 18px;
  border-radius: 8px;
  margin: 8px 0;
  overflow-x: auto;
  white-space: nowrap;
  border: none;
}
.cron-command-block:hover {
  background: #1e293b;
}
</style>
