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
          <template v-for="sec in sections" :key="sec.id">
            <a
              @click="navigateToSection(sec.id, sec.children ? (sec.children[0]?.id || '') : '')"
              :class="['setup-nav-item', { 'setup-nav-item--active': activeSection === sec.id && !sec.children }]"
            >
              <span class="setup-nav-icon" v-html="sec.icon"></span>
              <span>{{ sec.label }}</span>
              <span v-if="sec.children" class="setup-nav-arrow" :class="{ 'setup-nav-arrow--rotated': activeSection === sec.id }">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                  <polyline points="9 18 15 12 9 6"/>
                </svg>
              </span>
            </a>
            <div v-if="sec.children && activeSection === sec.id" class="setup-nav-children">
              <a
                v-for="child in sec.children"
                :key="child.id"
                @click="navigateToSection(sec.id, child.id)"
                :class="['setup-nav-child', { 'setup-nav-child--active': activeSubSection === child.id }]"
              >
                {{ child.label }}
              </a>
            </div>
          </template>
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
              :pagination="{ pageSize: 25, total: staffTotal, showSizeChanger: true, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
              row-key="id"
              size="small"
            >
              <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'name'">
                  <div class="flex-name">
                    <div class="avatar-circle">{{ initials(record.name) }}</div>
                    <div class="name-main">{{ record.name }}</div>
                  </div>
                </template>
                <template v-if="column.key === 'role'">
                  <a-tag :color="record.role === 'admin' ? 'blue' : 'default'" style="text-transform:capitalize">{{ record.role || 'Employee' }}</a-tag>
                </template>
                <template v-if="column.key === 'last_login'">
                  {{ formatLastLogin(record.last_login) }}
                </template>
                <template v-if="column.key === 'active'">
                  <a-badge :status="record.active ? 'success' : 'default'" :text="record.active ? 'Active' : 'Inactive'" />
                </template>
                <template v-if="column.key === 'actions'">
                  <div class="row-actions">
                    <a-button size="small" type="link" @click="viewStaff(record)">View</a-button>
                    <a-button size="small" type="link" danger @click="deleteStaff(record.id)">Delete</a-button>
                  </div>
                </template>
              </template>
            </a-table>
          </div>

          <!-- Add/Edit Staff Modal -->
          <a-modal
            v-model:open="openAddStaff"
            :title="editingStaff ? 'Edit Staff Member' : 'Add New Staff Member'"
            :width="720"
            :footer="null"
            :destroyOnClose="true"
            @cancel="resetStaffForm"
          >
            <a-tabs v-model:activeKey="staffActiveTab">
              <a-tab-pane key="profile" tab="Profile">
                <a-form layout="vertical" :model="staffForm">
                  <div class="profile-image-row">
                    <div class="profile-image-upload">
                      <div class="avatar-circle large" v-if="!staffForm.profile_image" :style="{ background: avatarColor(staffForm.first_name + ' ' + staffForm.last_name) }">
                        {{ initials(staffForm.first_name + ' ' + staffForm.last_name) || '?' }}
                      </div>
                      <img v-else :src="staffForm.profile_image" class="profile-preview" />
                      <div class="upload-actions">
                        <a-button size="small" @click="$refs.staffFileInput?.click()">Choose File</a-button>
                        <span class="file-hint">No file chosen</span>
                      </div>
                      <input type="file" ref="staffFileInput" accept="image/*" style="display:none" @change="onStaffFileChange" />
                    </div>
                    <div class="staff-type-badge">
                      <a-checkbox :checked="isStaffAdminRole" @change="toggleStaffAdmin">Administrator</a-checkbox>
                    </div>
                  </div>

                  <div class="form-row">
                    <a-form-item label="* First Name" :rules="[{ required: true, message: 'First name required' }]">
                      <a-input v-model:value="staffForm.first_name" placeholder="First Name" />
                    </a-form-item>
                    <a-form-item label="* Last Name" :rules="[{ required: true, message: 'Last name required' }]">
                      <a-input v-model:value="staffForm.last_name" placeholder="Last Name" />
                    </a-form-item>
                  </div>

                  <div class="form-row">
                    <a-form-item label="* Email" :rules="[{ required: true, type: 'email', message: 'Valid email required' }]">
                      <a-input v-model:value="staffForm.email" placeholder="Email" />
                    </a-form-item>
                    <a-form-item label="Hourly Rate">
                      <a-input-number v-model:value="staffForm.hourly_rate" :min="0" :precision="2" style="width: 100%">
                        <template #addonBefore>$</template>
                      </a-input-number>
                    </a-form-item>
                  </div>

                  <div class="form-row">
                    <a-form-item label="Phone">
                      <a-input v-model:value="staffForm.phone" placeholder="Phone" />
                    </a-form-item>
                    <a-form-item label="Facebook">
                      <a-input v-model:value="staffForm.facebook" placeholder="Facebook" />
                    </a-form-item>
                  </div>

                  <div class="form-row">
                    <a-form-item label="LinkedIn">
                      <a-input v-model:value="staffForm.linkedin" placeholder="LinkedIn" />
                    </a-form-item>
                    <a-form-item label="Skype">
                      <a-input v-model:value="staffForm.skype" placeholder="Skype" />
                    </a-form-item>
                  </div>

                  <div class="form-row">
                    <a-form-item label="Default Language">
                      <a-select v-model:value="staffForm.default_language" style="width: 100%">
                        <a-select-option value="">System Default</a-select-option>
                        <a-select-option value="en">English</a-select-option>
                        <a-select-option value="es">Spanish</a-select-option>
                        <a-select-option value="fr">French</a-select-option>
                        <a-select-option value="de">German</a-select-option>
                      </a-select>
                    </a-form-item>
                    <a-form-item label="Direction">
                      <a-select v-model:value="staffForm.direction" style="width: 100%">
                        <a-select-option value="">LTR</a-select-option>
                        <a-select-option value="rtl">RTL</a-select-option>
                      </a-select>
                    </a-form-item>
                  </div>

                  <a-form-item label="Email Signature">
                    <a-textarea v-model:value="staffForm.email_signature" :rows="3" placeholder="Email signature..." />
                  </a-form-item>

                  <a-form-item label="Member departments">
                    <a-checkbox-group v-model:value="staffForm.departments">
                      <a-checkbox value="Marketing">Marketing</a-checkbox>
                      <a-checkbox value="Sales">Sales</a-checkbox>
                      <a-checkbox value="Abuse">Abuse</a-checkbox>
                    </a-checkbox-group>
                  </a-form-item>

                  <a-form-item>
                    <a-checkbox v-model:checked="staffForm.send_welcome_email">Send welcome email</a-checkbox>
                  </a-form-item>

                  <div class="form-row">
                    <a-form-item
                      label="* Password"
                      :rules="editingStaff ? [] : [{ required: true, message: 'Password required' }]"
                    >
                      <a-input-password v-model:value="staffForm.password" placeholder="Password" />
                    </a-form-item>
                    <a-form-item label="Confirm Password">
                      <a-input-password v-model:value="staffForm.password_confirmation" placeholder="Confirm password" />
                    </a-form-item>
                  </div>
                </a-form>
              </a-tab-pane>

              <a-tab-pane key="permissions" tab="Permissions">
                <div class="permissions-section">
                  <div class="perm-info">
                    <a-typography-link href="#" @click.prevent>Click here to read more about permissions</a-typography-link>
                  </div>

                  <a-form-item label="Role">
                    <a-select v-model:value="staffForm.role_id" style="width: 280px" @change="onStaffRoleChange">
                      <a-select-option v-for="r in staffRoles" :key="r.id" :value="r.id">{{ r.name }}</a-select-option>
                    </a-select>
                  </a-form-item>

                  <div v-if="isStaffAdminRole" class="admin-notice">
                    <a-checkbox checked disabled>Administrator</a-checkbox>
                    <span class="admin-hint">Administrator has full access to all features</span>
                  </div>

                  <div class="permissions-grid">
                    <div class="perm-table">
                      <div class="perm-table-header">
                        <div class="perm-cell feature-col">Features</div>
                        <div class="perm-cell cap-col" v-for="cap in allCapabilities" :key="cap.key">{{ cap.label }}</div>
                      </div>
                      <div v-for="feat in featureList" :key="feat.key" class="perm-table-row">
                        <div class="perm-cell feature-col">{{ feat.label }}</div>
                        <div class="perm-cell cap-col" v-for="cap in feat.caps" :key="cap.key">
                          <a-checkbox
                            v-if="cap.type === 'checkbox'"
                            :checked="getStaffPerm(feat.key, cap.key)"
                            @change="e => setStaffPerm(feat.key, cap.key, e.target.checked)"
                          />
                          <span v-else-if="cap.type === 'label'" class="cap-label">{{ cap.label }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </a-tab-pane>
            </a-tabs>

            <div class="modal-footer">
              <a-button @click="openAddStaff = false">Cancel</a-button>
              <a-button type="primary" :loading="staffSaving" @click="saveStaff">{{ editingStaff ? 'Update' : 'Add Staff Member' }}</a-button>
            </div>
          </a-modal>

          <!-- View Staff Modal -->
          <a-modal
            v-model:open="viewStaffModal"
            title="View Staff Member"
            :footer="null"
            :width="560"
          >
            <div v-if="viewingStaff" class="view-staff-modal">
              <div class="view-field">
                <label>Full Name</label>
                <a-input :value="viewingStaff.name" disabled />
              </div>
              <div class="view-field">
                <label>Email Address</label>
                <a-input :value="viewingStaff.email" disabled />
              </div>
              <div class="view-field">
                <label>Role</label>
                <a-input :value="viewingStaff.role || 'Employee'" disabled />
              </div>
              <div class="view-field">
                <label>Last Login</label>
                <a-input :value="formatLastLogin(viewingStaff.last_login)" disabled />
              </div>
              <div class="view-field">
                <label>Status</label>
                <a-input :value="viewingStaff.active ? 'Active' : 'Inactive'" disabled />
              </div>
            </div>
          </a-modal>
        </div>

        <!-- ── CUSTOMERS (Settings) ── -->
        <div v-if="activeSection === 'customers'">
          <div v-if="activeSubSection === 'customers-general'">
            <CustomerGeneralView />
          </div>
          <div v-if="activeSubSection === 'customers-groups'">
            <GroupsView />
          </div>
          <div v-if="activeSubSection === 'customers-support'">
            <CustomerSupportView />
          </div>
        </div>

        <!-- ── SUPPORT (Ticket Settings) ── -->
        <div v-if="activeSection === 'support'">
          <div v-if="activeSubSection === 'support-departments'">
            <DepartmentsView />
          </div>

          <div v-if="activeSubSection === 'support-predefined-replies'">
            <PredefinedRepliesView />
          </div>

          <div v-if="activeSubSection === 'support-ticket-priority'">
            <TicketPriorityView />
          </div>

          <div v-if="activeSubSection === 'support-ticket-statuses'">
            <TicketStatusesView />
          </div>

          <div v-if="activeSubSection === 'support-services'">
            <ServicesView />
          </div>

          <div v-if="activeSubSection === 'support-spam-filters'">
            <SpamFiltersView />
          </div>
        </div>

        <!-- ── LEADS (Settings) ── -->
        <div v-if="activeSection === 'leads'">
          <div v-if="activeSubSection === 'leads-sources'">
            <SourcesView />
          </div>

          <div v-if="activeSubSection === 'leads-statuses'">
            <StatusesView />
          </div>

          <div v-if="activeSubSection === 'leads-email-integration'">
            <EmailIntegrationView />
          </div>

          <div v-if="activeSubSection === 'leads-web-to-lead'">
            <WebToLeadView />
          </div>
        </div>

        <!-- ── FINANCE ── -->
        <div v-if="activeSection === 'finance'">
          <div v-if="activeSubSection === 'finance-tax-rates'">
            <TaxRatesView />
          </div>

          <div v-if="activeSubSection === 'finance-currencies'">
            <CurrenciesView />
          </div>

          <div v-if="activeSubSection === 'finance-payment-modes'">
            <PaymentModesView />
          </div>

          <div v-if="activeSubSection === 'finance-expenses-categories'">
            <ExpensesCategoriesView />
          </div>
        </div>

        <!-- ── CONTRACTS ── -->
        <div v-if="activeSection === 'contracts'">
          <div v-if="activeSubSection === 'contracts-types'">
            <ContractTypesView />
          </div>
        </div>

        <!-- ── ESTIMATE REQUEST ── -->
        <div v-if="activeSection === 'estimate-request'">
          <div v-if="activeSubSection === 'estimate-request-forms'">
            <FormsView />
          </div>

          <div v-if="activeSubSection === 'estimate-request-statuses'">
            <EstimateStatusesView />
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
          <EmailTemplatesView />
        </div>

        <!-- ── CUSTOM FIELDS ── -->
        <div v-if="activeSection === 'custom-fields'">
          <CustomFieldsView />
        </div>

        <!-- ── GDPR ── -->
        <div v-if="activeSection === 'gdpr'">
          <GdprView />
        </div>

        <!-- ── ROLES ── -->
        <div v-if="activeSection === 'roles'">
          <RolesView />
        </div>

        <!-- ── MENU SETUP ── -->
        <div v-if="activeSection === 'menu-setup'">
          <div v-if="activeSubSection === 'menu-setup-main'">
            <MainMenuView />
          </div>

          <div v-if="activeSubSection === 'menu-setup-setup'">
            <SetupMenuView />
          </div>
        </div>

        <!-- ── THEME STYLE ── -->
        <div v-if="activeSection === 'theme-style'">
          <ThemeStyleView />
        </div>

        <!-- ── SETTINGS (General) ── -->
        <div v-if="activeSection === 'settings'">
          <SettingsView />
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
import { defineComponent, ref, reactive, computed, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { message } from 'ant-design-vue';

// Import Modular Setup Views
import GroupsView from './setup/Groups.vue';
import CustomerGeneralView from './setup/CustomerGeneral.vue';
import CustomerSupportView from './setup/CustomerSupport.vue';
import DepartmentsView from './setup/Departments.vue';
import PredefinedRepliesView from './setup/PredefinedReplies.vue';
import TicketPriorityView from './setup/TicketPriority.vue';
import TicketStatusesView from './setup/TicketStatuses.vue';
import ServicesView from './setup/Services.vue';
import SpamFiltersView from './setup/SpamFilters.vue';
import SourcesView from './setup/Sources.vue';
import StatusesView from './setup/Statuses.vue';
import EmailIntegrationView from './setup/EmailIntegration.vue';
import WebToLeadView from './setup/WebToLead.vue';
import TaxRatesView from './setup/TaxRates.vue';
import CurrenciesView from './setup/Currencies.vue';
import PaymentModesView from './setup/PaymentModes.vue';
import ExpensesCategoriesView from './setup/ExpensesCategories.vue';
import ContractTypesView from './setup/ContractTypes.vue';
import FormsView from './setup/Forms.vue';
import EstimateStatusesView from './setup/EstimateStatuses.vue';
import MainMenuView from './setup/MainMenu.vue';
import SetupMenuView from './setup/SetupMenu.vue';
import EmailTemplatesView from './setup/EmailTemplates.vue';
import CustomFieldsView from './setup/CustomFields.vue';
import GdprView from './setup/Gdpr.vue';
import RolesView from './setup/Roles.vue';
import ThemeStyleView from './setup/ThemeStyle.vue';
import SettingsView from './setup/Settings.vue';

export default defineComponent({
  name: 'SetupPage',
  components: {
    GroupsView,
    CustomerGeneralView,
    CustomerSupportView,
    DepartmentsView,
    PredefinedRepliesView,
    TicketPriorityView,
    TicketStatusesView,
    ServicesView,
    SpamFiltersView,
    SourcesView,
    StatusesView,
    EmailIntegrationView,
    WebToLeadView,
    TaxRatesView,
    CurrenciesView,
    PaymentModesView,
    ExpensesCategoriesView,
    ContractTypesView,
    FormsView,
    EstimateStatusesView,
    MainMenuView,
    SetupMenuView,
    EmailTemplatesView,
    CustomFieldsView,
    GdprView,
    RolesView,
    ThemeStyleView,
    SettingsView,
  },
  setup() {
    const router = useRouter();
    const route = useRoute();

    const activeSection = ref('staff');
    const activeSubSection = ref('');

    const mapUrlToSection = (section) => {
      if (!section) {
        return { activeSection: 'staff', activeSubSection: '' };
      }

      const mappings = {
        'staff': { activeSection: 'staff', activeSubSection: '' },
        'modules': { activeSection: 'modules', activeSubSection: '' },
        'email-templates': { activeSection: 'email-templates', activeSubSection: '' },
        'custom-fields': { activeSection: 'custom-fields', activeSubSection: '' },
        'gdpr': { activeSection: 'gdpr', activeSubSection: '' },
        'roles': { activeSection: 'roles', activeSubSection: '' },
        'theme-style': { activeSection: 'theme-style', activeSubSection: '' },
        'settings': { activeSection: 'settings', activeSubSection: '' },
        'help': { activeSection: 'help', activeSubSection: '' },

        'groups': { activeSection: 'customers', activeSubSection: 'customers-groups' },
        'departments': { activeSection: 'support', activeSubSection: 'support-departments' },
        'predefined-replies': { activeSection: 'support', activeSubSection: 'support-predefined-replies' },
        'ticket-priority': { activeSection: 'support', activeSubSection: 'support-ticket-priority' },
        'ticket-statuses': { activeSection: 'support', activeSubSection: 'support-ticket-statuses' },
        'services': { activeSection: 'support', activeSubSection: 'support-services' },
        'spam-filters': { activeSection: 'support', activeSubSection: 'support-spam-filters' },
        'sources': { activeSection: 'leads', activeSubSection: 'leads-sources' },
        'statuses': { activeSection: 'leads', activeSubSection: 'leads-statuses' },
        'email-integration': { activeSection: 'leads', activeSubSection: 'leads-email-integration' },
        'web-to-lead': { activeSection: 'leads', activeSubSection: 'leads-web-to-lead' },
        'tax-rates': { activeSection: 'finance', activeSubSection: 'finance-tax-rates' },
        'currencies': { activeSection: 'finance', activeSubSection: 'finance-currencies' },
        'payment-modes': { activeSection: 'finance', activeSubSection: 'finance-payment-modes' },
        'expenses-categories': { activeSection: 'finance', activeSubSection: 'finance-expenses-categories' },
        'contract-types': { activeSection: 'contracts', activeSubSection: 'contracts-types' },
        'forms': { activeSection: 'estimate-request', activeSubSection: 'estimate-request-forms' },
        'estimate-statuses': { activeSection: 'estimate-request', activeSubSection: 'estimate-request-statuses' },
        'main-menu': { activeSection: 'menu-setup', activeSubSection: 'menu-setup-main' },
        'setup-menu': { activeSection: 'menu-setup', activeSubSection: 'menu-setup-setup' },
      };

      return mappings[section] || { activeSection: 'staff', activeSubSection: '' };
    };

    const mapSectionToUrl = (activeSection, activeSubSection) => {
      const reverseMappings = {
        'staff': { section: 'staff' },
        'modules': { section: 'modules' },
        'email-templates': { section: 'email-templates' },
        'custom-fields': { section: 'custom-fields' },
        'gdpr': { section: 'gdpr' },
        'roles': { section: 'roles' },
        'theme-style': { section: 'theme-style' },
        'settings': { section: 'settings' },
        'help': { section: 'help' },

        'customers-groups': { section: 'groups' },
        'support-departments': { section: 'departments' },
        'support-predefined-replies': { section: 'predefined-replies' },
        'support-ticket-priority': { section: 'ticket-priority' },
        'support-ticket-statuses': { section: 'ticket-statuses' },
        'support-services': { section: 'services' },
        'support-spam-filters': { section: 'spam-filters' },
        'leads-sources': { section: 'sources' },
        'leads-statuses': { section: 'statuses' },
        'leads-email-integration': { section: 'email-integration' },
        'leads-web-to-lead': { section: 'web-to-lead' },
        'finance-tax-rates': { section: 'tax-rates' },
        'finance-currencies': { section: 'currencies' },
        'finance-payment-modes': { section: 'payment-modes' },
        'finance-expenses-categories': { section: 'expenses-categories' },
        'contracts-types': { section: 'contract-types' },
        'estimate-request-forms': { section: 'forms' },
        'estimate-request-statuses': { section: 'estimate-statuses' },
        'menu-setup-main': { section: 'main-menu' },
        'menu-setup-setup': { section: 'setup-menu' },
      };

      if (activeSubSection) {
        return reverseMappings[activeSubSection] || { section: activeSection };
      }
      return reverseMappings[activeSection] || { section: activeSection };
    };

    const navigateToSection = (sectionId, subSectionId = '') => {
      const { section } = mapSectionToUrl(sectionId, subSectionId);
      router.push({ name: 'admin.setup', params: { section } });
    };

    watch(() => route.params, (newParams) => {
      const { activeSection: aSec, activeSubSection: aSub } = mapUrlToSection(newParams.section);
      activeSection.value = aSec;
      activeSubSection.value = aSub;
    }, { immediate: true });

    // ── Section nav ────────────────────────────────
    const sections = [
      { id: 'staff',            label: 'Staff',            icon: iconSvg('users') },
      { id: 'customers',        label: 'Customers',        icon: iconSvg('customer'),
        children: [
          { id: 'customers-groups',  label: 'Groups' },
        ]
      },
      { id: 'support',          label: 'Support',          icon: iconSvg('support'),
        children: [
          { id: 'support-departments',        label: 'Departments' },
          { id: 'support-predefined-replies',  label: 'Predefined Replies' },
          { id: 'support-ticket-priority',     label: 'Ticket Priority' },
          { id: 'support-ticket-statuses',     label: 'Ticket Statuses' },
          { id: 'support-services',            label: 'Services' },
          { id: 'support-spam-filters',        label: 'Spam Filters' },
        ]
      },
      { id: 'leads',            label: 'Leads',            icon: iconSvg('leads'),
        children: [
          { id: 'leads-sources',           label: 'Sources' },
          { id: 'leads-statuses',          label: 'Statuses' },
          { id: 'leads-email-integration', label: 'Email Integration' },
          { id: 'leads-web-to-lead',       label: 'Web to Lead' },
        ]
      },
      { id: 'finance',          label: 'Finance',          icon: iconSvg('finance'),
        children: [
          { id: 'finance-tax-rates',          label: 'Tax Rates' },
          { id: 'finance-currencies',         label: 'Currencies' },
          { id: 'finance-payment-modes',      label: 'Payment Modes' },
          { id: 'finance-expenses-categories',label: 'Expenses Categories' },
        ]
      },
      { id: 'contracts',        label: 'Contracts',        icon: iconSvg('contracts'),
        children: [
          { id: 'contracts-types', label: 'Contract Types' },
        ]
      },
      { id: 'estimate-request', label: 'Estimate Request', icon: iconSvg('estimate'),
        children: [
          { id: 'estimate-request-forms',    label: 'Forms' },
          { id: 'estimate-request-statuses', label: 'Statuses' },
        ]
      },
      { id: 'modules',          label: 'Modules',          icon: iconSvg('modules') },
      { id: 'email-templates',  label: 'Email Templates',  icon: iconSvg('email') },
      { id: 'custom-fields',    label: 'Custom Fields',    icon: iconSvg('fields') },
      { id: 'gdpr',             label: 'GDPR',             icon: iconSvg('shield') },
      { id: 'roles',            label: 'Roles',            icon: iconSvg('roles') },
      { id: 'menu-setup',       label: 'Menu Setup',       icon: iconSvg('menu'),
        children: [
          { id: 'menu-setup-main',  label: 'Main Menu' },
          { id: 'menu-setup-setup', label: 'Setup Menu' },
        ]
      },
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
    const editingStaff = ref(false);
    const staffActiveTab = ref('profile');
    const staffRoles = ref([]);

    const staffForm = reactive({
      id: null, first_name: '', last_name: '', email: '', password: '', password_confirmation: '',
      role_id: null, hourly_rate: 0, phone: '', facebook: '', linkedin: '', skype: '',
      default_language: '', email_signature: '', direction: '', departments: [],
      profile_image: '', send_welcome_email: false, not_staff: false, permissions: {},
    });

    const allCapabilities = [
      { key: 'view_own', label: 'View (Own)' },
      { key: 'view_global', label: 'View (Global)' },
      { key: 'create', label: 'Create' },
      { key: 'edit', label: 'Edit' },
      { key: 'delete', label: 'Delete' },
    ];

    const featureList = [
      { key: 'Bulk PDF Export', label: 'Bulk PDF Export', caps: [{ key: 'view_global', type: 'checkbox' }] },
      { key: 'Contracts', label: 'Contracts', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
        { key: 'view_all_templates', type: 'label', label: 'View All Templates' },
      ]},
      { key: 'Credit Notes', label: 'Credit Notes', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Customers', label: 'Customers', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Email Templates', label: 'Email Templates', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'edit', type: 'checkbox' },
      ]},
      { key: 'Estimates', label: 'Estimates', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Expenses', label: 'Expenses', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Invoices', label: 'Invoices', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Items', label: 'Items', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'create', type: 'checkbox' },
        { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Knowledge Base', label: 'Knowledge Base', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'create', type: 'checkbox' },
        { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Payments', label: 'Payments', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Projects', label: 'Projects', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
        { key: 'create_timesheets', type: 'label', label: 'Create Timesheets' },
        { key: 'edit_milestones', type: 'label', label: 'Edit Milestones' },
        { key: 'delete_milestones', type: 'label', label: 'Delete Milestones' },
      ]},
      { key: 'Proposals', label: 'Proposals', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
        { key: 'view_all_templates', type: 'label', label: 'View All Templates' },
      ]},
      { key: 'Reports', label: 'Reports', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'view_timesheets', type: 'label', label: 'View Timesheets Report' },
      ]},
      { key: 'Staff Roles', label: 'Staff Roles', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'create', type: 'checkbox' },
        { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Settings', label: 'Settings', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'edit', type: 'checkbox' },
      ]},
      { key: 'Staff', label: 'Staff', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'create', type: 'checkbox' },
        { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Subscriptions', label: 'Subscriptions', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Tasks', label: 'Tasks', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
        { key: 'edit_timesheets_global', type: 'label', label: 'Edit Timesheets (Global)' },
        { key: 'edit_own_timesheets', type: 'label', label: 'Edit Own Timesheets' },
        { key: 'delete_timesheets_global', type: 'label', label: 'Delete Timesheets (Global)' },
        { key: 'delete_own_timesheets', type: 'label', label: 'Delete own Timesheets' },
      ]},
      { key: 'Task Checklist Templates', label: 'Task Checklist Templates', caps: [
        { key: 'create', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Estimate Request', label: 'Estimate Request', caps: [
        { key: 'view_own', type: 'checkbox' }, { key: 'view_global', type: 'checkbox' },
        { key: 'create', type: 'checkbox' }, { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Leads', label: 'Leads', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'Surveys', label: 'Surveys', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'create', type: 'checkbox' },
        { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
      { key: 'e-Invoice', label: 'e-Invoice', caps: [
        { key: 'bulk_export', type: 'label', label: 'Bulk export' },
      ]},
      { key: 'Goals', label: 'Goals', caps: [
        { key: 'view_global', type: 'checkbox' }, { key: 'create', type: 'checkbox' },
        { key: 'edit', type: 'checkbox' }, { key: 'delete', type: 'checkbox' },
      ]},
    ];

    const isStaffAdminRole = computed(() => {
      const r = staffRoles.value.find(x => x.id === staffForm.role_id);
      return r?.slug === 'admin';
    });

    const getStaffPerm = (feature, cap) => {
      const perms = staffForm.permissions || {};
      return perms[feature]?.[cap] ?? false;
    };

    const setStaffPerm = (feature, cap, val) => {
      if (!staffForm.permissions) staffForm.permissions = {};
      if (!staffForm.permissions[feature]) staffForm.permissions[feature] = {};
      staffForm.permissions[feature][cap] = val;
    };

    const toggleStaffAdmin = (e) => {
      if (e.target.checked) {
        const adminRole = staffRoles.value.find(r => r.slug === 'admin');
        if (adminRole) {
          staffForm.role_id = adminRole.id;
          staffForm.permissions = adminRole.permissions ? JSON.parse(JSON.stringify(adminRole.permissions)) : {};
        }
      } else {
        const empRole = staffRoles.value.find(r => r.slug === 'employee');
        staffForm.role_id = empRole ? empRole.id : null;
        staffForm.permissions = empRole?.permissions ? JSON.parse(JSON.stringify(empRole.permissions)) : {};
      }
    };

    const onStaffRoleChange = () => {
      const r = staffRoles.value.find(x => x.id === staffForm.role_id);
      staffForm.permissions = r?.permissions ? JSON.parse(JSON.stringify(r.permissions)) : {};
    };

    const onStaffFileChange = (e) => {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (ev) => { staffForm.profile_image = ev.target.result; };
        reader.readAsDataURL(file);
      }
    };

    const avatarColor = (name) => {
      const colors = ['#2563eb', '#e11d48', '#f59e0b', '#10b981', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316'];
      let hash = 0;
      for (let i = 0; i < (name || '').length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
      return colors[Math.abs(hash) % colors.length];
    };

    const staffColumns = [
      { title: 'Full Name',    key: 'name',       dataIndex: 'name' },
      { title: 'Email',        key: 'email',      dataIndex: 'email' },
      { title: 'Role',         key: 'role',       dataIndex: 'role',       width: 140 },
      { title: 'Last Login',   key: 'last_login', dataIndex: 'last_login', width: 160 },
      { title: 'Active',       key: 'active',     dataIndex: 'active',     width: 100 },
      { title: 'Actions',      key: 'actions',    width: 150 },
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

    const loadRoles = async () => {
      try {
        const { data } = await axios.get('/api/roles');
        staffRoles.value = data;
      } catch (e) {
        console.error('Failed to load roles', e);
      }
    };

    const initials = (name) => (name || '').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();

    const editStaffMember = (record) => {
      editingStaff.value = true;
      staffActiveTab.value = 'profile';
      const names = (record.name || '').split(' ');
      const first = names.shift() || '';
      const last = names.join(' ') || '';
      Object.assign(staffForm, {
        id: record.id,
        first_name: first,
        last_name: last,
        email: record.email || '',
        password: '',
        password_confirmation: '',
        role_id: record.role_id || null,
        hourly_rate: record.hourly_rate || 0,
        phone: record.phone || '',
        facebook: record.facebook || '',
        linkedin: record.linkedin || '',
        skype: record.skype || '',
        default_language: record.default_language || '',
        email_signature: record.email_signature || '',
        direction: record.direction || '',
        departments: record.departments || [],
        profile_image: record.profile_image || '',
        send_welcome_email: record.send_welcome_email || false,
        not_staff: record.not_staff || false,
        permissions: record.permissions ? JSON.parse(JSON.stringify(record.permissions)) : {},
      });
      openAddStaff.value = true;
    };

    const saveStaff = async () => {
      staffSaving.value = true;
      try {
        const payload = {
          first_name: staffForm.first_name,
          last_name: staffForm.last_name,
          name: staffForm.first_name + ' ' + staffForm.last_name,
          email: staffForm.email,
          role_id: staffForm.role_id,
          hourly_rate: staffForm.hourly_rate,
          phone: staffForm.phone,
          facebook: staffForm.facebook,
          linkedin: staffForm.linkedin,
          skype: staffForm.skype,
          default_language: staffForm.default_language,
          email_signature: staffForm.email_signature,
          direction: staffForm.direction,
          departments: staffForm.departments,
          profile_image: staffForm.profile_image,
          send_welcome_email: staffForm.send_welcome_email,
          not_staff: staffForm.not_staff,
          permissions: staffForm.permissions,
        };
        if (staffForm.password) {
          payload.password = staffForm.password;
          payload.password_confirmation = staffForm.password_confirmation;
        }
        if (staffForm.id) {
          await axios.put(`/api/staff/${staffForm.id}`, payload);
          message.success('Staff member updated');
        } else {
          await axios.post('/api/staff', payload);
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
      editingStaff.value = false;
      staffActiveTab.value = 'profile';
      Object.assign(staffForm, {
        id: null, first_name: '', last_name: '', email: '', password: '', password_confirmation: '',
        role_id: null, hourly_rate: 0, phone: '', facebook: '', linkedin: '', skype: '',
        default_language: '', email_signature: '', direction: '', departments: [],
        profile_image: '', send_welcome_email: false, not_staff: false, permissions: {},
      });
    };

    const formatLastLogin = (date) => {
      if (!date) return 'Never';
      const diff = Math.floor((Date.now() - new Date(date)) / 1000);
      if (diff < 60) return 'Just now';
      if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
      if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
      if (diff < 2592000) return Math.floor(diff / 86400) + ' days ago';
      return new Date(date).toLocaleDateString();
    };

    const viewingStaff = ref(null);
    const viewStaffModal = ref(false);

    const viewStaff = (record) => {
      viewingStaff.value = record;
      viewStaffModal.value = true;
    };

    // ── Settings data ──────────────────────────────


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







    const helpItems = [
      { title: 'Documentation', desc: 'Read the full documentation for Perfex CRM setup and usage guides.', link: '#', icon: iconSvg('help') },
      { title: 'Video Tutorials', desc: 'Watch step-by-step video tutorials for key features.', link: '#', icon: iconSvg('theme') },
      { title: 'Submit Ticket', desc: 'Open a support ticket for technical issues or feature requests.', link: '#', icon: iconSvg('email') },
    ];

    const saveSettings = (section) => {
      message.success(`${section.charAt(0).toUpperCase() + section.slice(1)} settings saved`);
    };

    onMounted(() => {
      loadStaff();
      loadRoles();
    });

    return {
      activeSection, activeSubSection, sections,
      staffList, staffTotal, staffLoading, staffSearch, staffColumns,
      openAddStaff, staffForm, staffSaving, editingStaff, staffActiveTab, staffRoles,
      allCapabilities, featureList, isStaffAdminRole,
      getStaffPerm, setStaffPerm, toggleStaffAdmin, onStaffRoleChange, onStaffFileChange, avatarColor,
      loadStaff, loadRoles, editStaffMember, saveStaff, deleteStaff, resetStaffForm, initials,
      formatLastLogin, viewingStaff, viewStaffModal, viewStaff,
      modules, filteredModules,
      modSearch, modPerPage, moduleFileName, onModuleFileChange, uploadModule, handleModuleAction,
      helpItems,
      saveSettings, navigateToSection,
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
  border-right: none;
  background: linear-gradient(135deg, #d35400 0%, #7e1e8e 50%, #0b579f 100%);
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
  color: rgba(255, 255, 255, 0.85);
  cursor: pointer;
  border-left: 3px solid transparent;
  text-decoration: none;
  transition: background 0.12s, color 0.12s;
  user-select: none;
}
.setup-nav-item:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #ffffff;
}
.setup-nav-item--active {
  background: rgba(255, 255, 255, 0.18);
  color: #ffffff;
  font-weight: 600;
  border-left-color: #ffffff;
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
  stroke: currentColor;
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

/* ── Modules Grid ─────────────────────────────────────── */
.module-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
}
.module-name { font-size: 13px; font-weight: 600; color: #1e293b; }

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
.view-staff-modal .view-field {
  margin-bottom: 16px;
}
.view-staff-modal .view-field:last-child {
  margin-bottom: 0;
}
.view-staff-modal label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: #475569;
  margin-bottom: 4px;
}
.row-actions {
  display: flex;
  gap: 4px;
}
.profile-image-row {
  display: flex;
  align-items: flex-start;
  gap: 20px;
  margin-bottom: 20px;
}
.profile-image-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}
.profile-preview {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
}
.upload-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}
.file-hint {
  font-size: 12px;
  color: #94a3b8;
}
.staff-type-badge {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding-top: 10px;
}
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  margin-top: 16px;
}
.permissions-section {
  padding: 8px 0;
}
.perm-info {
  margin-bottom: 16px;
}
.admin-notice {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px;
  background: #f0f5ff;
  border-radius: 6px;
  border: 1px solid #d6e4ff;
}
.admin-hint {
  font-size: 13px;
  color: #475569;
}
.permissions-grid {
  overflow-x: auto;
}
.perm-table {
  min-width: 600px;
}
.perm-table-header,
.perm-table-row {
  display: flex;
  align-items: center;
  border-bottom: 1px solid #f1f5f9;
}
.perm-table-header {
  background: #f8fafc;
  font-weight: 600;
  font-size: 12px;
  color: #64748b;
}
.perm-cell {
  padding: 8px 10px;
  font-size: 13px;
}
.feature-col {
  width: 180px;
  flex-shrink: 0;
}
.cap-col {
  width: 100px;
  text-align: center;
}
.cap-label {
  font-size: 11px;
  color: #64748b;
}
.avatar-circle.large {
  width: 80px;
  height: 80px;
  font-size: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  color: #fff;
  font-weight: 600;
}
/* Sidebar Nav Children */
.setup-nav-children {
  background: linear-gradient(135deg, #d35400 0%, #7e1e8e 50%, #0b579f 100%);
  padding: 2px 0 6px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}
.setup-nav-child {
  display: block;
  padding: 6px 14px 6px 39px;
  font-size: 12.5px;
  color: rgba(255, 255, 255, 0.85);
  cursor: pointer;
  text-decoration: none;
  transition: color 0.12s, background 0.12s;
}
.setup-nav-child:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #ffffff;
}
.setup-nav-child--active {
  color: #ffffff !important;
  font-weight: 600;
  background: rgba(255, 255, 255, 0.18);
  border-right: 2px solid #ffffff;
}
.setup-nav-arrow {
  margin-left: auto;
  display: flex;
  align-items: center;
  color: #94a3b8;
  transition: transform 0.2s ease;
}
.setup-nav-arrow--rotated {
  transform: rotate(90deg);
}
</style>
