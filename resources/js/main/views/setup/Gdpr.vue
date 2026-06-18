<template>
  <div>
    <!-- HEADER & TOOLBAR -->
    <div class="section-toolbar">
      <h2 class="section-title">GDPR Settings</h2>
    </div>

    <!-- GDPR READ MORE BANNER -->
    <div class="gdpr-info-banner">
      <span class="info-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 18px; height: 18px; color: #4f46e5;">
          <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 111.083 1.083l-.04.041a.75.75 0 11-1.083-1.083zM12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5z" />
        </svg>
      </span>
      <a href="https://gdpr-info.eu/" target="_blank" class="gdpr-read-more-link">
        Click here to read more about GDPR
      </a>
    </div>

    <!-- MAIN CARD / TABS CONTAINER -->
    <div class="settings-card">
      <a-tabs v-model:activeKey="activeTab" class="gdpr-tabs">
        
        <!-- 1. GENERAL TAB -->
        <a-tab-pane key="general" tab="General">
          <div class="tab-content-wrap">
            <h3 class="tab-title">General GDPR Settings</h3>
            
            <div class="settings-grid">
              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Enable GDPR</span>
                  <span class="field-desc">Toggle the display of GDPR options and preferences in public and customer portal.</span>
                </div>
                <div class="field-input-wrap">
                  <a-radio-group v-model:value="settings.enabled" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>

              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Show GDPR link in customers area navigation</span>
                  <span class="field-desc">Display GDPR link on customer dashboard main menu header.</span>
                </div>
                <div class="field-input-wrap">
                  <a-radio-group v-model:value="settings.show_nav_link" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>

              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Show GDPR link in customers area footer</span>
                  <span class="field-desc">Display GDPR link on customer dashboard footer.</span>
                </div>
                <div class="field-input-wrap">
                  <a-radio-group v-model:value="settings.show_footer_link" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>

              <div class="settings-textarea-row">
                <span class="field-label">GDPR page top information block</span>
                <span class="field-desc">This text block will be shown at the top of the GDPR page in the customers area.</span>
                <a-textarea v-model:value="settings.top_info_block" :rows="5" placeholder="Enter top information text block..." />
              </div>
            </div>
          </div>
        </a-tab-pane>

        <!-- 2. RIGHT TO DATA PORTABILITY -->
        <a-tab-pane key="portability" tab="Right to data portability">
          <div class="tab-content-wrap">
            <h3 class="tab-title">Right to Data Portability</h3>
            
            <div class="settings-grid">
              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Enable Right to data portability</span>
                  <span class="field-desc">Allows contacts and leads to export their data in JSON format.</span>
                </div>
                <div class="field-input-wrap">
                  <a-radio-group v-model:value="settings.portability_enabled" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>

              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Allow Contacts to Export</span>
                  <span class="field-desc">Enables profile data export directly inside customer portal.</span>
                </div>
                <div class="field-input-wrap">
                  <a-checkbox v-model:checked="settings.allow_contacts_export">Yes, allow contacts</a-checkbox>
                </div>
              </div>

              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Allow Leads to Export</span>
                  <span class="field-desc">Enables lead details export.</span>
                </div>
                <div class="field-input-wrap">
                  <a-checkbox v-model:checked="settings.allow_leads_export">Yes, allow leads</a-checkbox>
                </div>
              </div>

              <div class="settings-checkbox-group-row">
                <span class="field-label">Select fields to include in export</span>
                <span class="field-desc">Choose which data components are extracted into the portability bundle.</span>
                <div class="checkbox-grid">
                  <a-checkbox-group v-model:value="settings.export_fields" style="width: 100%">
                    <div class="grid-columns-3">
                      <a-checkbox value="contact_info">Contact Details</a-checkbox>
                      <a-checkbox value="tickets">Support Tickets</a-checkbox>
                      <a-checkbox value="invoices">Invoices & Bills</a-checkbox>
                      <a-checkbox value="estimates">Estimates</a-checkbox>
                      <a-checkbox value="proposals">Proposals</a-checkbox>
                      <a-checkbox value="projects">Projects</a-checkbox>
                      <a-checkbox value="tasks">Assigned Tasks</a-checkbox>
                      <a-checkbox value="consent_history">Consent History</a-checkbox>
                    </div>
                  </a-checkbox-group>
                </div>
              </div>

              <div class="settings-textarea-row">
                <span class="field-label">Notice on right to data portability page</span>
                <a-textarea v-model:value="settings.portability_notice" :rows="4" />
              </div>
            </div>
          </div>
        </a-tab-pane>

        <!-- 3. RIGHT TO ERASURE -->
        <a-tab-pane key="erasure" tab="Right to erasure">
          <div class="tab-content-wrap">
            <h3 class="tab-title">Right to Erasure (Right to be Forgotten)</h3>
            
            <div class="settings-grid">
              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Enable Right to erasure</span>
                  <span class="field-desc">Enable erasure requests from customer area.</span>
                </div>
                <div class="field-input-wrap">
                  <a-radio-group v-model:value="settings.erasure_enabled" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>

              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Allowed request sources</span>
                  <span class="field-desc">Select where the request link is available.</span>
                </div>
                <div class="field-input-wrap">
                  <a-checkbox-group v-model:value="settings.erasure_sources">
                    <a-checkbox value="portal">Customer Portal</a-checkbox>
                    <a-checkbox value="lead_form">Public Lead Form</a-checkbox>
                  </a-checkbox-group>
                </div>
              </div>

              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Automatically delete data request</span>
                  <span class="field-desc">If yes, data is deleted immediately upon submission without staff approval.</span>
                </div>
                <div class="field-input-wrap">
                  <a-radio-group v-model:value="settings.auto_delete" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>

              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Keep record of deletion request in database</span>
                  <span class="field-desc">Maintains anonymized log of delete request timestamps.</span>
                </div>
                <div class="field-input-wrap">
                  <a-radio-group v-model:value="settings.keep_record" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>

              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Delete customer data completely</span>
                  <span class="field-desc">Includes removing all associated estimates, contracts, proposals, and logs.</span>
                </div>
                <div class="field-input-wrap">
                  <a-radio-group v-model:value="settings.delete_completely" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>

              <div class="settings-textarea-row">
                <span class="field-label">Notice on right to erasure page</span>
                <a-textarea v-model:value="settings.erasure_notice" :rows="4" />
              </div>
            </div>
          </div>
        </a-tab-pane>

        <!-- 4. RIGHT TO BE INFORMED -->
        <a-tab-pane key="informed" tab="Right to be informed">
          <div class="tab-content-wrap">
            <h3 class="tab-title">Right to be Informed</h3>
            
            <div class="settings-grid">
              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Enable terms and conditions agreement</span>
                  <span class="field-desc">Requires users to explicitly agree to terms & conditions and privacy policies.</span>
                </div>
                <div class="field-input-wrap">
                  <a-radio-group v-model:value="settings.informed_enabled" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>

              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Require agreement on Registration</span>
                  <span class="field-desc">Show checkbox on client registration screen.</span>
                </div>
                <div class="field-input-wrap">
                  <a-radio-group v-model:value="settings.require_terms_registration" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>

              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Require agreement on Ticket Submission</span>
                  <span class="field-desc">Show checkbox before submitting new tickets.</span>
                </div>
                <div class="field-input-wrap">
                  <a-radio-group v-model:value="settings.require_terms_tickets" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>

              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Require agreement on Lead Form Submission</span>
                  <span class="field-desc">Show checkbox on public Web to Lead capture forms.</span>
                </div>
                <div class="field-input-wrap">
                  <a-radio-group v-model:value="settings.require_terms_leads" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>

              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Privacy Policy URL</span>
                  <span class="field-desc">Link to your company Privacy Policy.</span>
                </div>
                <div class="field-input-wrap">
                  <a-input v-model:value="settings.privacy_policy_url" placeholder="https://..." />
                </div>
              </div>

              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Terms & Conditions URL</span>
                  <span class="field-desc">Link to your company Terms and Conditions.</span>
                </div>
                <div class="field-input-wrap">
                  <a-input v-model:value="settings.terms_url" placeholder="https://..." />
                </div>
              </div>

              <div class="settings-textarea-row">
                <span class="field-label">Notice on right to be informed page</span>
                <a-textarea v-model:value="settings.informed_notice" :rows="4" />
              </div>
            </div>
          </div>
        </a-tab-pane>

        <!-- 5. RIGHT OF ACCESS/RECTIFICATION -->
        <a-tab-pane key="access_rectification" tab="Right of access/Right to rectification">
          <div class="tab-content-wrap">
            <h3 class="tab-title">Right of Access / Right to Rectification</h3>
            
            <div class="settings-grid">
              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Enable Right of access/Right to rectification</span>
                  <span class="field-desc">Enables access details panel in customer portals.</span>
                </div>
                <div class="field-input-wrap">
                  <a-radio-group v-model:value="settings.rectification_enabled" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>

              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Allow contacts to edit their personal data</span>
                  <span class="field-desc">Allows updating profile fields without administrative review.</span>
                </div>
                <div class="field-input-wrap">
                  <a-radio-group v-model:value="settings.allow_contacts_edit" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>

              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Allow leads to edit their personal data</span>
                  <span class="field-desc">Allows public profile updates.</span>
                </div>
                <div class="field-input-wrap">
                  <a-radio-group v-model:value="settings.allow_leads_edit" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>

              <div class="settings-checkbox-group-row">
                <span class="field-label">Select allowed fields for rectification</span>
                <span class="field-desc">Choose which fields contacts/leads can modify.</span>
                <div class="checkbox-grid">
                  <a-checkbox-group v-model:value="settings.rectification_fields" style="width: 100%">
                    <div class="grid-columns-3">
                      <a-checkbox value="first_name">First Name</a-checkbox>
                      <a-checkbox value="last_name">Last Name</a-checkbox>
                      <a-checkbox value="email">Email Address</a-checkbox>
                      <a-checkbox value="phone">Phone Number</a-checkbox>
                      <a-checkbox value="company">Company Name</a-checkbox>
                      <a-checkbox value="address">Address</a-checkbox>
                      <a-checkbox value="city">City</a-checkbox>
                      <a-checkbox value="zip">ZIP / Postal Code</a-checkbox>
                    </div>
                  </a-checkbox-group>
                </div>
              </div>

              <div class="settings-textarea-row">
                <span class="field-label">Notice on right of access page</span>
                <a-textarea v-model:value="settings.rectification_notice" :rows="4" />
              </div>
            </div>
          </div>
        </a-tab-pane>

        <!-- 6. CONSENT -->
        <a-tab-pane key="consent" tab="Consent">
          <div class="tab-content-wrap">
            <div class="consent-tab-header">
              <h3 class="tab-title">Consent Management</h3>
              <a-button type="primary" size="small" @click="openNewPurposeDrawer">Add New Consent Purpose</a-button>
            </div>

            <div class="settings-grid">
              <div class="settings-field-row">
                <div class="field-label-desc">
                  <span class="field-label">Enable Consent purposes</span>
                  <span class="field-desc">Shows tracking and choices on registration/lead screens.</span>
                </div>
                <div class="field-input-wrap">
                  <a-radio-group v-model:value="settings.consent_enabled" button-style="solid">
                    <a-radio-button :value="true">Yes</a-radio-button>
                    <a-radio-button :value="false">No</a-radio-button>
                  </a-radio-group>
                </div>
              </div>

              <!-- CONSENT PURPOSES LIST TABLE -->
              <div class="consent-purposes-table-wrap">
                <span class="sub-label">Purposes of Consent</span>
                <a-table
                  :dataSource="consentPurposes"
                  :columns="purposeColumns"
                  row-key="id"
                  size="small"
                  :pagination="false"
                >
                  <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'id'">
                      <span class="purpose-id">{{ record.id }}</span>
                    </template>
                    <template v-if="column.key === 'name'">
                      <span class="purpose-name">{{ record.name }}</span>
                    </template>
                    <template v-if="column.key === 'required'">
                      <a-tag :color="record.required ? 'red' : 'blue'">
                        {{ record.required ? 'Required' : 'Optional' }}
                      </a-tag>
                    </template>
                    <template v-if="column.key === 'active'">
                      <a-switch v-model:checked="record.active" size="small" />
                    </template>
                    <template v-if="column.key === 'options'">
                      <div class="row-actions">
                        <a-button size="small" type="link" @click="editPurpose(record)">Edit</a-button>
                        <a-button size="small" type="link" danger @click="deletePurpose(record.id)">Delete</a-button>
                      </div>
                    </template>
                  </template>
                </a-table>
              </div>

              <div class="settings-textarea-row" style="margin-top: 16px;">
                <span class="field-label">Notice on consent page</span>
                <a-textarea v-model:value="settings.consent_notice" :rows="4" />
              </div>
            </div>
          </div>
        </a-tab-pane>
      </a-tabs>

      <!-- SAVE FOOTER -->
      <div class="settings-actions">
        <a-button type="primary" :loading="saving" @click="saveGdprSettings">
          Save Settings
        </a-button>
      </div>
    </div>

    <!-- CONSENT PURPOSE ADD/EDIT DRAWER -->
    <a-drawer
      v-model:open="openPurposeDrawer"
      :title="editingPurposeId ? 'Edit Consent Purpose' : 'Add New Consent Purpose'"
      placement="right"
      :width="400"
      @close="resetPurposeForm"
    >
      <a-form layout="vertical" :model="purposeForm" @finish="savePurpose">
        <a-form-item label="* Purpose Name" name="name" :rules="[{ required: true, message: 'Purpose name is required' }]">
          <a-input v-model:value="purposeForm.name" placeholder="e.g. Email Newsletter" />
        </a-form-item>

        <a-form-item label="Description" name="description">
          <a-textarea v-model:value="purposeForm.description" :rows="4" placeholder="Describe what consent is being given for..." />
        </a-form-item>

        <a-form-item name="required" style="margin-bottom: 8px;">
          <a-checkbox v-model:checked="purposeForm.required">
            Required (Users cannot submit/register without checking this)
          </a-checkbox>
        </a-form-item>

        <a-form-item name="active" style="margin-bottom: 24px;">
          <a-checkbox v-model:checked="purposeForm.active">
            Active
          </a-checkbox>
        </a-form-item>

        <div class="drawer-footer">
          <a-button @click="openPurposeDrawer = false" style="margin-right: 8px;">Cancel</a-button>
          <a-button type="primary" html-type="submit">
            {{ editingPurposeId ? 'Update Purpose' : 'Add Purpose' }}
          </a-button>
        </div>
      </a-form>
    </a-drawer>
  </div>
</template>

<script>
import { defineComponent, ref, reactive } from 'vue';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'GdprView',
  setup() {
    const activeTab = ref('general');
    const saving = ref(false);
    const openPurposeDrawer = ref(false);
    const editingPurposeId = ref(null);

    const settings = reactive({
      enabled: true,
      show_nav_link: true,
      show_footer_link: true,
      top_info_block: 'General Data Protection Regulation (GDPR) settings are configured here. Enable compliance functions, manage user portability parameters, data erasure settings, and handle explicit tracking consents.',

      portability_enabled: true,
      allow_contacts_export: true,
      allow_leads_export: false,
      export_fields: ['contact_info', 'tickets', 'invoices', 'consent_history'],
      portability_notice: 'In accordance with Article 20 of the GDPR, you have the right to request a copy of your personal data in a structured, machine-readable JSON format.',

      erasure_enabled: true,
      erasure_sources: ['portal'],
      auto_delete: false,
      keep_record: true,
      delete_completely: false,
      erasure_notice: 'In accordance with Article 17 of the GDPR, you can request the deletion of your personal data. Your request will be reviewed by our data protection officer.',

      informed_enabled: true,
      require_terms_registration: true,
      require_terms_tickets: false,
      require_terms_leads: true,
      privacy_policy_url: 'https://example.com/privacy-policy',
      terms_url: 'https://example.com/terms-and-conditions',
      informed_notice: 'In accordance with Articles 13 and 14 of the GDPR, we inform you about how your personal data is collected and processed.',

      rectification_enabled: true,
      allow_contacts_edit: true,
      allow_leads_edit: false,
      rectification_fields: ['first_name', 'last_name', 'email', 'phone', 'address'],
      rectification_notice: 'In accordance with Articles 15 and 16 of the GDPR, you have the right to access and rectify your personal data if it is inaccurate or incomplete.',

      consent_enabled: true,
      consent_notice: 'We require your explicit consent to process your personal data for specific purposes. You can withdraw your consent at any time.'
    });

    const consentPurposes = ref([
      { id: 1, name: 'Email Marketing', description: 'Receive newsletter and marketing updates via email.', required: false, active: true },
      { id: 2, name: 'SMS Notifications', description: 'Receive transactional and informational SMS messages.', required: false, active: true },
      { id: 3, name: 'Third-Party Data Sharing', description: 'Allow sharing contact details with verified partners for service delivery.', required: true, active: false }
    ]);

    const purposeForm = reactive({
      name: '',
      description: '',
      required: false,
      active: true
    });

    const purposeColumns = [
      { title: 'ID', dataIndex: 'id', key: 'id', width: 60 },
      { title: 'Purpose', dataIndex: 'name', key: 'name' },
      { title: 'Required', key: 'required', width: 100 },
      { title: 'Active', key: 'active', width: 90 },
      { title: 'Options', key: 'options', width: 120 }
    ];

    const saveGdprSettings = () => {
      saving.value = true;
      setTimeout(() => {
        saving.value = false;
        message.success('GDPR settings updated successfully');
      }, 600);
    };

    const openNewPurposeDrawer = () => {
      resetPurposeForm();
      openPurposeDrawer.value = true;
    };

    const editPurpose = (record) => {
      editingPurposeId.value = record.id;
      purposeForm.name = record.name;
      purposeForm.description = record.description || '';
      purposeForm.required = record.required || false;
      purposeForm.active = record.active !== undefined ? record.active : true;
      openPurposeDrawer.value = true;
    };

    const deletePurpose = (id) => {
      consentPurposes.value = consentPurposes.value.filter(p => p.id !== id);
      message.success('Consent purpose deleted');
    };

    const savePurpose = () => {
      if (!purposeForm.name.trim()) return;

      if (editingPurposeId.value) {
        const item = consentPurposes.value.find(p => p.id === editingPurposeId.value);
        if (item) {
          item.name = purposeForm.name.trim();
          item.description = purposeForm.description;
          item.required = purposeForm.required;
          item.active = purposeForm.active;
        }
        message.success('Consent purpose updated');
      } else {
        const maxId = consentPurposes.value.reduce((max, p) => p.id > max ? p.id : max, 0);
        consentPurposes.value.push({
          id: maxId + 1,
          name: purposeForm.name.trim(),
          description: purposeForm.description,
          required: purposeForm.required,
          active: purposeForm.active
        });
        message.success('Consent purpose added');
      }
      openPurposeDrawer.value = false;
      resetPurposeForm();
    };

    const resetPurposeForm = () => {
      editingPurposeId.value = null;
      Object.assign(purposeForm, {
        name: '',
        description: '',
        required: false,
        active: true
      });
    };

    return {
      activeTab,
      saving,
      settings,
      consentPurposes,
      openPurposeDrawer,
      editingPurposeId,
      purposeForm,
      purposeColumns,
      saveGdprSettings,
      openNewPurposeDrawer,
      editPurpose,
      deletePurpose,
      savePurpose,
      resetPurposeForm
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
.gdpr-info-banner {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f0f5ff;
  border: 1px solid #d6e4ff;
  border-radius: 8px;
  padding: 10px 16px;
  margin-bottom: 16px;
}
.gdpr-read-more-link {
  font-size: 13px;
  font-weight: 600;
  color: #4f46e5;
  text-decoration: none;
  transition: color 0.15s;
}
.gdpr-read-more-link:hover {
  color: #6366f1;
  text-decoration: underline;
}
.settings-card {
  background: #fff;
  border-radius: 12px;
  border: 1px solid #f1f5f9;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.gdpr-tabs :deep(.ant-tabs-nav) {
  margin-bottom: 24px;
}
.tab-content-wrap {
  min-height: 320px;
}
.tab-title {
  font-size: 15px;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 20px;
}
.settings-grid {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.settings-field-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 16px;
  border-bottom: 1px solid #f8fafc;
}
.field-label-desc {
  display: flex;
  flex-direction: column;
  gap: 4px;
  max-width: 60%;
}
.field-label {
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
}
.field-desc {
  font-size: 12px;
  color: #64748b;
}
.field-input-wrap {
  display: flex;
  align-items: center;
}
.settings-textarea-row {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.settings-checkbox-group-row {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.checkbox-grid {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
}
.grid-columns-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}
.consent-tab-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}
.consent-purposes-table-wrap {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 8px;
}
.sub-label {
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
}
.purpose-id {
  font-family: monospace;
  color: #64748b;
}
.purpose-name {
  font-weight: 600;
  color: #1e293b;
}
.row-actions {
  display: flex;
  gap: 4px;
}
.settings-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
}
.drawer-footer {
  display: flex;
  justify-content: flex-end;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  margin-top: 16px;
}
</style>
