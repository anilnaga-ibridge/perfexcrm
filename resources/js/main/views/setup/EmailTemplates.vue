<template>
  <div>
    <!-- HEADER & TOOLBAR -->
    <div class="section-toolbar">
      <h2 class="section-title">Email Templates</h2>
      <div class="header-search-bar">
        <a-input-search
          v-model:value="search"
          placeholder="Search templates..."
          style="width: 280px"
          size="small"
        />
      </div>
    </div>

    <!-- MAIN TEMPLATES LIST -->
    <div class="settings-card">
      <div class="groups-container">
        <div v-for="group in filteredGroups" :key="group.name" class="template-group-card">
          <div class="group-header">
            <h3 class="group-title">{{ group.name }}</h3>
            <div class="group-actions">
              <a href="#" @click.prevent="disableAll(group)" class="group-action-link disable-link">Disable All</a>
              <span class="action-divider">|</span>
              <a href="#" @click.prevent="enableAll(group)" class="group-action-link enable-link">Enable All</a>
            </div>
          </div>

          <table class="templates-table">
            <thead>
              <tr>
                <th style="width: 60%">Template Name</th>
                <th style="width: 40%; text-align: right;">Options</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="tmpl in group.templates" :key="tmpl.id">
                <td>
                  <a href="#" @click.prevent="openEditDrawer(tmpl)" class="template-name-link">
                    {{ tmpl.name }}
                  </a>
                </td>
                <td style="text-align: right;">
                  <button
                    :class="['status-toggle-btn', tmpl.active ? 'btn-active' : 'btn-inactive']"
                    @click="toggleTemplate(tmpl)"
                  >
                    {{ tmpl.active ? 'Disable' : 'Enable' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Edit Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      :title="selectedTemplate ? selectedTemplate.name : 'Edit Email Template'"
      placement="right"
      :width="650"
      @close="resetForm"
    >
      <div v-if="selectedTemplate">
        <a-form layout="vertical" :model="form" @finish="saveTemplate">
          <a-form-item label="* Template Title" name="name" :rules="[{ required: true, message: 'Template title required' }]">
            <a-input v-model:value="form.name" />
          </a-form-item>

          <a-form-item label="Subject" name="subject">
            <a-input v-model:value="form.subject" />
          </a-form-item>

          <a-form-item label="* From Name" name="from_name" :rules="[{ required: true, message: 'From name required' }]">
            <a-input v-model:value="form.from_name" />
          </a-form-item>

          <a-form-item label="Send as Plaintext" name="plain_text">
            <a-radio-group v-model:value="form.plain_text">
              <a-radio-button :value="false">Disabled</a-radio-button>
              <a-radio-button :value="true">Enabled</a-radio-button>
            </a-radio-group>
          </a-form-item>

          <!-- LANGUAGES TABS -->
          <div class="languages-section">
            <div style="font-weight: 500; font-size: 13px; color: #475569; margin-bottom: 8px;">Languages</div>
            <div class="lang-tabs-wrapper">
              <a-tabs v-model:activeKey="activeLangTab" size="small" type="card" class="lang-tabs">
                <a-tab-pane key="english" tab="English">
                  <div style="padding-top: 10px;">
                    <a-form-item label="Email Message Body">
                      <a-textarea v-model:value="form.body" :rows="8" placeholder="Type email content here..." />
                    </a-form-item>
                  </div>
                </a-tab-pane>
                <a-tab-pane v-for="lang in otherLanguages" :key="lang" :tab="lang">
                  <div style="padding-top: 10px;">
                    <a-form-item :label="lang + ' Email Message Body'">
                      <a-textarea v-model:value="form.translations[lang]" :rows="8" placeholder="Type translated email content here..." />
                    </a-form-item>
                  </div>
                </a-tab-pane>
              </a-tabs>
            </div>
          </div>

          <!-- MERGE FIELDS -->
          <div class="merge-fields-card">
            <div class="merge-fields-title">Available merge fields</div>
            <p class="merge-fields-hint">If ticket is imported with email piping and the contact does not exists in the CRM the fields won't be replaced.</p>

            <a-collapse size="small" ghost expand-icon-position="right">
              <a-collapse-panel key="client" header="Client">
                <div class="merge-fields-grid">
                  <div class="merge-field-row"><span>Contact Firstname</span><code>{contact_firstname}</code></div>
                  <div class="merge-field-row"><span>Contact Lastname</span><code>{contact_lastname}</code></div>
                  <div class="merge-field-row"><span>Contact Phone Number</span><code>{contact_phonenumber}</code></div>
                  <div class="merge-field-row"><span>Contact Title</span><code>{contact_title}</code></div>
                  <div class="merge-field-row"><span>Contact Email</span><code>{contact_email}</code></div>
                  <div class="merge-field-row"><span>Client Company</span><code>{client_company}</code></div>
                  <div class="merge-field-row"><span>Client Phone Number</span><code>{client_phonenumber}</code></div>
                  <div class="merge-field-row"><span>Client Country</span><code>{client_country}</code></div>
                  <div class="merge-field-row"><span>Client City</span><code>{client_city}</code></div>
                  <div class="merge-field-row"><span>Client Zip</span><code>{client_zip}</code></div>
                  <div class="merge-field-row"><span>Client State</span><code>{client_state}</code></div>
                  <div class="merge-field-row"><span>Client Address</span><code>{client_address}</code></div>
                  <div class="merge-field-row"><span>Client Vat Number</span><code>{client_vat_number}</code></div>
                  <div class="merge-field-row"><span>Client ID</span><code>{client_id}</code></div>
                  <div class="merge-field-row"><span>Client Website</span><code>{client_website}</code></div>
                </div>
              </a-collapse-panel>

              <a-collapse-panel key="ticket" header="Ticket">
                <div class="merge-fields-grid">
                  <div class="merge-field-row"><span>Ticket ID</span><code>{ticket_id}</code></div>
                  <div class="merge-field-row"><span>Ticket URL</span><code>{ticket_url}</code></div>
                  <div class="merge-field-row"><span>Ticket Public URL</span><code>{ticket_public_url}</code></div>
                  <div class="merge-field-row"><span>Department</span><code>{ticket_department}</code></div>
                  <div class="merge-field-row"><span>Department Email</span><code>{ticket_department_email}</code></div>
                  <div class="merge-field-row"><span>Date Opened</span><code>{ticket_date}</code></div>
                  <div class="merge-field-row"><span>Ticket Subject</span><code>{ticket_subject}</code></div>
                  <div class="merge-field-row"><span>Ticket Message</span><code>{ticket_message}</code></div>
                  <div class="merge-field-row"><span>Ticket Status</span><code>{ticket_status}</code></div>
                  <div class="merge-field-row"><span>Ticket Priority</span><code>{ticket_priority}</code></div>
                  <div class="merge-field-row"><span>Ticket Service</span><code>{ticket_service}</code></div>
                  <div class="merge-field-row"><span>Project name</span><code>{project_name}</code></div>
                </div>
              </a-collapse-panel>

              <a-collapse-panel key="other" header="Other">
                <div class="merge-fields-grid">
                  <div class="merge-field-row"><span>Logo URL</span><code>{logo_url}</code></div>
                  <div class="merge-field-row"><span>Logo image with URL</span><code>{logo_image_with_url}</code></div>
                  <div class="merge-field-row"><span>Dark logo image with URL</span><code>{dark_logo_image_with_url}</code></div>
                  <div class="merge-field-row"><span>CRM URL</span><code>{crm_url}</code></div>
                  <div class="merge-field-row"><span>Admin URL</span><code>{admin_url}</code></div>
                  <div class="merge-field-row"><span>Main Domain</span><code>{main_domain}</code></div>
                  <div class="merge-field-row"><span>Company Name</span><code>{companyname}</code></div>
                  <div class="merge-field-row"><span>Email Signature</span><code>{email_signature}</code></div>
                  <div class="merge-field-row"><span>(GDPR) Terms & Conditions URL</span><code>{terms_and_conditions_url}</code></div>
                </div>
              </a-collapse-panel>
            </a-collapse>
          </div>

          <div class="drawer-footer">
            <a-button @click="openDrawer = false">Cancel</a-button>
            <a-button type="primary" html-type="submit">
              Save Template
            </a-button>
          </div>
        </a-form>
      </div>
    </a-drawer>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed } from 'vue';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'EmailTemplatesView',
  setup() {
    const search = ref('');
    const openDrawer = ref(false);
    const selectedTemplate = ref(null);
    const activeLangTab = ref('english');

    const otherLanguages = [
      'Norwegian', 'Portuguese_br', 'Bulgarian', 'Italian', 'Czech', 'Persian', 'French',
      'Finnish', 'Francais_canada', 'Indonesia', 'Portuguese', 'Japanese', 'Spanish',
      'Dutch', 'Swedish', 'Ukrainian', 'Vietnamese', 'Turkish', 'Chinese', 'Romanian',
      'Slovak', 'Russian', 'German', 'Greek', 'Polish', 'Catalan'
    ];

    const templateGroups = ref([
      {
        name: 'Tickets',
        templates: [
          { id: 1, name: 'New Ticket Opened (Opened by Staff, Sent to Customer)', subject: 'New Support Ticket Opened', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nA new ticket has been opened for you.' },
          { id: 2, name: 'Ticket Reply (Sent to Customer)', subject: 'Ticket Reply', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nWe have replied to your support ticket.' },
          { id: 3, name: 'New Ticket Opened - Autoresponse', subject: 'Ticket Received', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nWe have received your support ticket.' },
          { id: 4, name: 'New Ticket Created (Opened by Customer, Sent to Staff Members)', subject: 'New Ticket Created', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nA new ticket has been created by a client.' },
          { id: 5, name: 'Ticket Reply (Sent to Staff)', subject: 'Ticket Reply', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nA reply has been submitted on ticket #{ticket_id}.' },
          { id: 6, name: 'Auto Close Ticket', subject: 'Ticket Auto Closed', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nYour support ticket has been closed due to inactivity.' },
          { id: 7, name: 'New Ticket Assigned (Sent to Staff)', subject: 'Ticket Assigned', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nSupport ticket #{ticket_id} has been assigned to you.' }
        ]
      },
      {
        name: 'Estimates',
        templates: [
          { id: 8, name: 'Send Estimate to Customer', subject: 'Estimate from {companyname}', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nPlease find attached our estimate.' },
          { id: 9, name: 'Estimate Already Sent to Customer', subject: 'Estimate Reminder', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nThis is a reminder regarding the estimate.' },
          { id: 10, name: 'Estimate Declined (Sent to Staff)', subject: 'Estimate Declined', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nEstimate #{estimate_id} has been declined by the customer.' },
          { id: 11, name: 'Estimate Accepted (Sent to Staff)', subject: 'Estimate Accepted', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nEstimate #{estimate_id} has been accepted by the customer.' },
          { id: 12, name: 'Thank You Email (Sent to Customer After Accept)', subject: 'Thank you for accepting estimate', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nThank you for accepting our estimate.' },
          { id: 13, name: 'Estimate Expiration Reminder', subject: 'Estimate Expiring Soon', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nYour estimate will expire soon.' }
        ]
      },
      {
        name: 'Contracts',
        templates: [
          { id: 14, name: 'Contract Expiration Reminder (Sent to Customer Contacts)', subject: 'Contract Expiring', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nYour contract is expiring soon.' },
          { id: 15, name: 'Send Contract to Customer', subject: 'Contract from {companyname}', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nPlease review our contract.' },
          { id: 16, name: 'New Comment  (Sent to Customer Contacts)', subject: 'New Comment on Contract', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nA comment has been added to your contract.' },
          { id: 17, name: 'New Comment (Sent to Staff)', subject: 'New Comment on Contract', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nA new comment was added to the contract.' },
          { id: 18, name: 'Contract Expiration Reminder (Sent to Staff)', subject: 'Contract Expiring', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nA contract will expire soon.' },
          { id: 19, name: 'Contract Signed (Sent to Staff)', subject: 'Contract Signed', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nA contract has been signed by the client.' },
          { id: 20, name: 'Contract Sign Reminder (Sent to Customer)', subject: 'Contract Sign Reminder', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nPlease sign the contract.' }
        ]
      },
      {
        name: 'Invoices',
        templates: [
          { id: 21, name: 'Send Invoice to Customer', subject: 'Invoice #{id} from {company_name}', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nPlease find attached your invoice.' },
          { id: 22, name: 'Invoice Payment Recorded (Sent to Customer)', subject: 'Payment Received', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nWe have recorded your payment.' },
          { id: 23, name: 'Invoice Overdue Notice', subject: 'Invoice #{id} is past due', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nYour invoice is now overdue.' },
          { id: 24, name: 'Invoice Already Sent to Customer', subject: 'Invoice Reminder', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nThis is a reminder regarding your invoice.' },
          { id: 25, name: 'Invoice Payment Recorded (Sent to Staff)', subject: 'Payment Received', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nA payment has been recorded for invoice #{invoice_id}.' },
          { id: 26, name: 'Invoice Due Notice', subject: 'Invoice #{id} is due soon', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nYour invoice is due soon.' },
          { id: 27, name: 'Invoices Payments Recorded in Batch (Sent to Customer)', subject: 'Payments Recorded', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nBatch payments have been recorded.' }
        ]
      },
      {
        name: 'Subscriptions',
        templates: [
          { id: 28, name: 'Send Subscription to Customer', subject: 'Subscription details', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nSubscription details.' },
          { id: 29, name: 'Subscription Payment Failed', subject: 'Payment Failed', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nSubscription payment failed.' },
          { id: 30, name: 'Subscription Canceled (Sent to customer primary contact)', subject: 'Subscription Canceled', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nSubscription canceled.' },
          { id: 31, name: 'Subscription Payment Succeeded (Sent to customer primary contact)', subject: 'Payment Succeeded', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nSubscription payment succeeded.' },
          { id: 32, name: 'Customer Subscribed to a Subscription (Sent to administrators and subscription creator)', subject: 'New Subscription Created', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nA customer has subscribed.' },
          { id: 33, name: 'Credit Card Authorization Required - SCA', subject: 'Authorization Required', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nCredit card authorization is required.' }
        ]
      },
      {
        name: 'Credit Note',
        templates: [
          { id: 34, name: 'Send Credit Note To Email', subject: 'Credit Note details', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nCredit note details.' }
        ]
      },
      {
        name: 'Tasks',
        templates: [
          { id: 35, name: 'New Task Assigned (Sent to Staff)', subject: 'Task Assigned', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nA new task has been assigned.' },
          { id: 36, name: 'Staff Member Added as Follower on Task (Sent to Staff)', subject: 'Added as Follower', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nYou have been added as follower.' },
          { id: 37, name: 'New Comment on Task (Sent to Staff)', subject: 'New Comment', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nA comment was added.' },
          { id: 38, name: 'New Attachment(s) on Task (Sent to Staff)', subject: 'New Attachment', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nAttachment was uploaded.' },
          { id: 39, name: 'Task Deadline Reminder - Sent to Assigned Members', subject: 'Deadline Reminder', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nTask deadline reminder.' },
          { id: 40, name: 'New Attachment(s) on Task (Sent to Customer Contacts)', subject: 'New Attachment', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nAttachment uploaded.' },
          { id: 41, name: 'New Comment on Task (Sent to Customer Contacts)', subject: 'New Comment', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nComment added.' },
          { id: 42, name: 'Task Status Changed (Sent to Staff)', subject: 'Task Status Changed', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nTask status changed.' },
          { id: 43, name: 'Task Status Changed (Sent to Customer Contacts)', subject: 'Task Status Changed', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nTask status changed.' }
        ]
      },
      {
        name: 'Customers',
        templates: [
          { id: 44, name: 'New Contact Added/Registered (Welcome Email)', subject: 'Welcome', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nWelcome.' },
          { id: 45, name: 'Forgot Password', subject: 'Password Reset', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nForgot password link.' },
          { id: 46, name: 'Password Reset - Confirmation', subject: 'Password Reset Confirmed', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nPassword reset confirmed.' },
          { id: 47, name: 'Set New Password', subject: 'Set New Password', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nSet new password.' },
          { id: 48, name: 'Statement - Account Summary', subject: 'Account Statement', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nAccount statement.' },
          { id: 49, name: 'New Customer Registration (Sent to admins)', subject: 'New Customer Registered', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nNew customer registered.' },
          { id: 50, name: 'Email Verification (Sent to Contact After Registration)', subject: 'Verify Email', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nPlease verify email.' },
          { id: 51, name: 'New Customer Profile File(s) Uploaded (Sent to Staff)', subject: 'File Uploaded', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nFiles uploaded.' }
        ]
      },
      {
        name: 'Proposals',
        templates: [
          { id: 52, name: 'Customer Action - Accepted (Sent to Staff)', subject: 'Proposal Accepted', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nProposal accepted.' },
          { id: 53, name: 'Send Proposal to Customer', subject: 'Proposal from {companyname}', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nProposal details.' },
          { id: 54, name: 'Customer Action - Declined (Sent to Staff)', subject: 'Proposal Declined', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nProposal declined.' },
          { id: 55, name: 'Thank You Email (Sent to Customer After Accept)', subject: 'Thank you', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nThank you.' },
          { id: 56, name: 'New Comment  (Sent to Customer/Lead)', subject: 'New Comment', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nComment added.' },
          { id: 57, name: 'New Comment (Sent to Staff)', subject: 'New Comment', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nComment added.' },
          { id: 58, name: 'Proposal Expiration Reminder', subject: 'Proposal Expiring', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nProposal expiring.' }
        ]
      },
      {
        name: 'Projects',
        templates: [
          { id: 59, name: 'New Project Discussion (Sent to Project Members)', subject: 'New Discussion', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nNew discussion.' },
          { id: 60, name: 'New Project Discussion (Sent to Customer Contacts)', subject: 'New Discussion', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nNew discussion.' },
          { id: 61, name: 'New Project File(s) Uploaded (Sent to Customer Contacts)', subject: 'Files Uploaded', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nFiles uploaded.' },
          { id: 62, name: 'New Project File(s) Uploaded (Sent to Project Members)', subject: 'Files Uploaded', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nFiles uploaded.' },
          { id: 63, name: 'New Discussion Comment (Sent to Customer Contacts)', subject: 'New Comment', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nComment added.' },
          { id: 64, name: 'New Discussion Comment (Sent to Project Members)', subject: 'New Comment', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nComment added.' },
          { id: 65, name: 'Staff Added as Project Member', subject: 'Project Assigned', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nProject assigned.' },
          { id: 66, name: 'New Project Created (Sent to Customer Contacts)', subject: 'Project Created', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nProject created.' },
          { id: 67, name: 'Project Marked as Finished (Sent to Customer Contacts)', subject: 'Project Completed', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Dear {contact_firstname},\n\nProject completed.' }
        ]
      },
      {
        name: 'Staff Members',
        templates: [
          { id: 68, name: 'New Staff Created (Welcome Email)', subject: 'Welcome', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nWelcome to CRM.' },
          { id: 69, name: 'Forgot Password', subject: 'Password Reset', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nPassword reset.' },
          { id: 70, name: 'Password Reset - Confirmation', subject: 'Password Reset Confirmed', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nPassword reset confirmed.' },
          { id: 71, name: 'Two Factor Authentication', subject: '2FA Code', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nYour 2FA code is here.' },
          { id: 72, name: 'Staff Reminder Email', subject: 'Reminder', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nReminder.' },
          { id: 73, name: 'Event Notification (Calendar)', subject: 'Event Notification', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nEvent notification.' }
        ]
      },
      {
        name: 'Leads',
        templates: [
          { id: 74, name: 'New Lead Assigned to Staff Member', subject: 'Lead Assigned', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nNew lead assigned.' },
          { id: 75, name: 'Web to lead form submitted - Sent to lead', subject: 'Form Received', active: true, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nWeb form received.' }
        ]
      },
      {
        name: 'Estimate Request',
        templates: [
          { id: 76, name: 'Estimate Request Submitted (Sent to Staff)', subject: 'Request Submitted', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nRequest submitted.' },
          { id: 77, name: 'Estimate Request Assigned (Sent to Staff)', subject: 'Request Assigned', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nRequest assigned.' },
          { id: 78, name: 'Estimate Request Received (Sent to User)', subject: 'Request Received', active: true, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nRequest received.' }
        ]
      },
      {
        name: 'Notifications',
        templates: [
          { id: 79, name: 'Non-billed tasks reminder (sent to selected staff members)', subject: 'Tasks Reminder', active: false, from_name: '{companyname} | CRM', plain_text: false, body: 'Hello,\n\nNon-billed tasks reminder.' }
        ]
      }
    ]);

    const form = reactive({
      name: '',
      subject: '',
      from_name: '',
      plain_text: false,
      body: '',
      translations: {}
    });

    const filteredGroups = computed(() => {
      if (!search.value) return templateGroups.value;
      const q = search.value.toLowerCase();
      return templateGroups.value.map(g => {
        const matched = g.templates.filter(t => 
          t.name.toLowerCase().includes(q) || 
          t.subject.toLowerCase().includes(q)
        );
        return { ...g, templates: matched };
      }).filter(g => g.templates.length > 0);
    });

    const disableAll = (group) => {
      group.templates.forEach(t => t.active = false);
      message.success(`All templates under ${group.name} disabled`);
    };

    const enableAll = (group) => {
      group.templates.forEach(t => t.active = true);
      message.success(`All templates under ${group.name} enabled`);
    };

    const toggleTemplate = (tmpl) => {
      tmpl.active = !tmpl.active;
      message.success(`${tmpl.name} has been ${tmpl.active ? 'enabled' : 'disabled'}`);
    };

    const openEditDrawer = (tmpl) => {
      selectedTemplate.value = tmpl;
      form.name = tmpl.name;
      form.subject = tmpl.subject;
      form.from_name = tmpl.from_name || '{companyname} | CRM';
      form.plain_text = tmpl.plain_text || false;
      form.body = tmpl.body || '';
      
      const translationsObj = {};
      otherLanguages.forEach(l => {
        translationsObj[l] = tmpl.translations ? tmpl.translations[l] || '' : '';
      });
      form.translations = reactive(translationsObj);
      
      activeLangTab.value = 'english';
      openDrawer.value = true;
    };

    const saveTemplate = () => {
      if (selectedTemplate.value) {
        selectedTemplate.value.name = form.name;
        selectedTemplate.value.subject = form.subject;
        selectedTemplate.value.from_name = form.from_name;
        selectedTemplate.value.plain_text = form.plain_text;
        selectedTemplate.value.body = form.body;
        selectedTemplate.value.translations = { ...form.translations };
        message.success('Email template updated successfully');
      }
      openDrawer.value = false;
      resetForm();
    };

    const resetForm = () => {
      selectedTemplate.value = null;
      activeLangTab.value = 'english';
      form.name = '';
      form.subject = '';
      form.from_name = '';
      form.plain_text = false;
      form.body = '';
      form.translations = {};
    };

    return {
      search,
      openDrawer,
      selectedTemplate,
      activeLangTab,
      otherLanguages,
      templateGroups,
      form,
      filteredGroups,
      disableAll,
      enableAll,
      toggleTemplate,
      openEditDrawer,
      saveTemplate,
      resetForm
    };
  }
});
</script>

<style scoped>
.section-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}
.section-title {
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
.settings-card {
  background: #fff;
  padding: 24px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.groups-container {
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.template-group-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px 20px;
}
.group-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 12px;
  margin-bottom: 12px;
}
.group-title {
  font-size: 14.5px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
.group-actions {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
}
.group-action-link {
  font-weight: 600;
  text-decoration: none;
}
.group-action-link:hover {
  text-decoration: underline;
}
.disable-link {
  color: #ef4444;
}
.enable-link {
  color: #10b981;
}
.action-divider {
  color: #cbd5e1;
}
.templates-table {
  width: 100%;
  border-collapse: collapse;
}
.templates-table th {
  font-size: 11px;
  text-transform: uppercase;
  color: #64748b;
  font-weight: 700;
  padding-bottom: 8px;
  border-bottom: 1px solid #e2e8f0;
}
.templates-table td {
  padding: 10px 0;
  border-bottom: 1px solid #f1f5f9;
}
.templates-table tr:last-child td {
  border-bottom: none;
  padding-bottom: 0;
}
.template-name-link {
  font-weight: 500;
  color: #4f46e5;
  text-decoration: none;
}
.template-name-link:hover {
  text-decoration: underline;
}
.status-toggle-btn {
  font-size: 11.5px;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 6px;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.15s;
}
.btn-active {
  background: #fecaca;
  color: #b91c1c;
}
.btn-active:hover {
  background: #fca5a5;
}
.btn-inactive {
  background: #d1fae5;
  color: #065f46;
}
.btn-inactive:hover {
  background: #a7f3d0;
}
.languages-section {
  border-top: 1px solid #e2e8f0;
  padding-top: 20px;
  margin-top: 20px;
}
.lang-tabs-wrapper {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 12px;
}
.merge-fields-card {
  margin-top: 24px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
}
.merge-fields-title {
  font-size: 13.5px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 4px;
}
.merge-fields-hint {
  font-size: 12px;
  color: #64748b;
  margin-bottom: 12px;
}
.merge-fields-grid {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 250px;
  overflow-y: auto;
  padding-right: 8px;
}
.merge-field-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
  padding: 4px 0;
  border-bottom: 1px solid #f1f5f9;
}
.merge-field-row span {
  color: #334155;
  font-weight: 500;
}
.merge-field-row code {
  color: #be185d;
  background: #fdf2f8;
  border: 1px solid #fbcfe8;
  padding: 1px 6px;
  border-radius: 4px;
  font-family: monospace;
}
.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  margin-top: 20px;
}
</style>
