<template>
  <div class="email-templates-wrapper font-sans">
    <!-- DYNAMIC THEME HERO BRAND HEADER BANNER -->
    <div
      class="theme-hero-banner mb-5 p-6 rounded-2xl text-white shadow-md relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-5 transition-all"
      style="background: linear-gradient(135deg, var(--theme-text-dark, #5f4f8d) 0%, var(--theme-primary, #9f8ed6) 100%); border: 1px solid rgba(255, 255, 255, 0.2);"
    >
      <!-- Glow Background Accents -->
      <div class="absolute -top-12 -right-12 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -bottom-12 -left-12 w-64 h-64 bg-black/10 rounded-full blur-3xl pointer-events-none"></div>

      <div class="flex items-center gap-4 relative z-10">
        <div class="w-13 h-13 rounded-2xl bg-white/20 backdrop-blur-md text-white flex items-center justify-center font-bold text-2xl shadow-sm border border-white/30 shrink-0">
          ✉️
        </div>
        <div>
          <h2 class="text-xl font-extrabold text-white m-0 tracking-tight flex items-center gap-2">
            <span>Email Templates Studio</span>
            <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider bg-white/20 text-white border border-white/30 rounded-full shadow-2xs">WYSIWYG Studio</span>
          </h2>
          <p class="text-xs text-white/90 m-0 mt-1 max-w-2xl font-medium leading-relaxed">
            Manage automated notification email templates for Company, Staff, HR, Customers, Contracts & Custom templates with live Quill rich-text editor and custom merge tags.
          </p>
        </div>
      </div>

      <div class="relative z-10 shrink-0">
        <button
          type="button"
          class="px-5 py-2.5 bg-white font-extrabold text-xs rounded-xl shadow-md transition-all cursor-pointer flex items-center gap-2 border border-white/40 hover:opacity-90 active:scale-95"
          style="color: var(--theme-text-dark, #5f4f8d);"
          @click="openCreateModal()"
        >
          <span class="w-5 h-5 rounded-lg flex items-center justify-center text-sm font-black text-white" style="background: var(--theme-primary, #9f8ed6);">+</span>
          <span>Create Custom Template</span>
        </button>
      </div>
    </div>

    <!-- DYNAMIC THEME FILTER TABS & SEARCH TOOLBAR PANEL -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
      <!-- Dynamic Audience Filter Tabs -->
      <div class="flex items-center gap-1.5 flex-wrap">
        <button
          type="button"
          v-for="tab in filterTabs"
          :key="tab.id"
          class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all cursor-pointer flex items-center gap-2 border-none"
          :style="audienceFilter === tab.id ? { background: 'var(--theme-primary, #9f8ed6)', color: '#ffffff', boxShadow: '0 2px 8px rgba(0,0,0,0.12)' } : { background: '#f1f5f9', color: '#475569' }"
          @click="audienceFilter = tab.id"
        >
          <span>{{ tab.label }}</span>
          <span
            class="px-1.5 py-0.5 text-[10px] font-black rounded-md transition-all"
            :style="audienceFilter === tab.id ? { background: 'var(--theme-text-dark, #5f4f8d)', color: '#ffffff' } : { background: '#e2e8f0', color: '#64748b' }"
          >
            {{ audienceCounts[tab.id] || 0 }}
          </span>
        </button>
      </div>

      <!-- Search Input Box -->
      <div class="relative flex items-center w-full lg:w-72">
        <input
          v-model="search"
          type="text"
          placeholder="Search template or event name..."
          class="w-full h-10 pl-10 pr-9 text-xs font-semibold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:bg-white transition-all theme-search-input"
        />
        <svg class="absolute left-3.5 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <button
          v-if="search"
          type="button"
          class="absolute right-3 text-slate-400 hover:text-slate-600 text-xs font-bold border-none bg-transparent cursor-pointer"
          @click="search = ''"
        >
          ✕
        </button>
      </div>
    </div>

    <!-- MAIN TEMPLATES LIST -->
    <div class="settings-card bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
      <div v-if="filteredGroups.length === 0" class="text-center py-12">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48" class="text-slate-300 mx-auto mb-3">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <h4 class="text-sm font-semibold text-slate-700">No email templates found</h4>
        <p class="text-xs text-slate-400 mt-1 mb-4">Try matching another search term or create a custom email template.</p>
        <button
          type="button"
          class="px-4 py-2 text-white font-bold text-xs rounded-xl shadow-sm transition-all cursor-pointer border-none inline-flex items-center gap-1.5"
          style="background: var(--theme-primary, #9f8ed6);"
          @click="openCreateModal()"
        >
          <span class="text-sm font-black">+</span> Create Custom Template
        </button>
      </div>

      <div v-else class="groups-container flex flex-col gap-6">
        <div v-for="group in filteredGroups" :key="group.name" class="template-group-card bg-slate-50/70 border border-slate-200/80 rounded-2xl p-5">
          <div class="group-header flex items-center justify-between pb-3 mb-3 border-b border-slate-200/80">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full inline-block" style="background: var(--theme-primary, #9f8ed6);"></span>
              <h3 class="group-title text-sm font-bold text-slate-800 margin-0">{{ group.name }}</h3>
              <span class="text-xs text-slate-400 font-medium ml-1">({{ group.templates.length }} templates)</span>
            </div>
            <div class="group-actions flex items-center gap-3 text-xs font-semibold">
              <a href="#" @click.prevent="openCreateModal(group.name)" class="font-bold transition-colors flex items-center gap-1 no-underline" style="color: var(--theme-text-dark, #5f4f8d)">
                <span>+ Add Template</span>
              </a>
              <span class="text-slate-300">|</span>
              <a href="#" @click.prevent="disableAll(group)" class="text-slate-500 hover:text-rose-600 transition-colors no-underline">Disable All</a>
              <span class="text-slate-300">|</span>
              <a href="#" @click.prevent="enableAll(group)" class="font-bold transition-colors no-underline" style="color: var(--theme-text-dark, #5f4f8d)">Enable All</a>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="templates-table w-full text-left">
              <thead>
                <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/60">
                  <th class="py-2.5 px-3">Template Name</th>
                  <th class="py-2.5 px-3">Subject / Event</th>
                  <th class="py-2.5 px-3 text-right">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="tmpl in group.templates" :key="tmpl.id" class="hover:bg-white/80 transition-colors">
                  <td class="py-3 px-3">
                    <div class="flex items-center gap-2">
                      <a href="#" @click.prevent="openEditDrawer(tmpl)" class="template-name-link font-semibold text-xs text-slate-800 flex items-center gap-1.5 no-underline hover:opacity-80">
                        <span>{{ tmpl.name }}</span>
                      </a>
                      <span v-if="tmpl.is_custom" class="px-2 py-0.5 text-[10px] font-bold rounded-md" style="background: rgba(159, 142, 214, 0.15); color: var(--theme-text-dark, #5f4f8d); border: 1px solid rgba(159, 142, 214, 0.3);">Custom</span>
                    </div>
                  </td>
                  <td class="py-3 px-3 text-xs text-slate-500 font-mono">
                    {{ tmpl.subject }}
                  </td>
                  <td class="py-3 px-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                      <button
                        :class="['status-toggle-btn px-3 py-1 text-xs font-semibold rounded-lg transition-all border cursor-pointer', tmpl.active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200']"
                        @click="toggleTemplate(tmpl)"
                      >
                        {{ tmpl.active ? 'Enabled' : 'Disabled' }}
                      </button>
                      <button
                        type="button"
                        class="px-3 py-1 text-xs font-semibold rounded-lg transition-all flex items-center gap-1 cursor-pointer border"
                        style="background: rgba(159, 142, 214, 0.12); color: var(--theme-text-dark, #5f4f8d); border-color: rgba(159, 142, 214, 0.3);"
                        @click="openEditDrawer(tmpl)"
                      >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit
                      </button>
                      <button
                        v-if="tmpl.is_custom"
                        type="button"
                        class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-100 transition-all flex items-center gap-1 cursor-pointer"
                        @click="deleteCustomTemplate(group, tmpl)"
                        title="Delete custom template"
                      >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- CREATE CUSTOM EMAIL TEMPLATE MODAL -->
    <a-modal
      v-model:open="showCreateModal"
      title="Create Custom Email Template"
      :footer="null"
      centered
      width="560px"
    >
      <div class="space-y-4 pt-2 font-sans">
        <div>
          <label class="block text-xs font-bold text-slate-800 mb-1"><span class="required-star">*</span> Template Name</label>
          <a-input v-model:value="createForm.name" placeholder="e.g. Client Contract Renewal Notice" class="h-10 text-xs font-semibold rounded-lg" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-slate-800 mb-1">Target Group / Category</label>
            <select v-model="createForm.groupName" class="w-full h-10 px-3 text-xs font-semibold theme-input-ctrl rounded-lg border border-slate-200 bg-slate-50 cursor-pointer">
              <option v-for="g in templateGroups" :key="g.name" :value="g.name">{{ g.name }}</option>
              <option value="__NEW__">+ Create New Category / Group...</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-800 mb-1">Recipient Audience Filter</label>
            <select v-model="createForm.audience" class="w-full h-10 px-3 text-xs font-semibold theme-input-ctrl rounded-lg border border-slate-200 bg-slate-50 cursor-pointer">
              <option value="custom">Contracts & Custom</option>
              <option value="customer">Customer / Client</option>
              <option value="employee">Employee / Staff</option>
              <option value="company">Company / Admin</option>
            </select>
          </div>
        </div>

        <div v-if="createForm.groupName === '__NEW__'">
          <label class="block text-xs font-bold text-slate-800 mb-1"><span class="required-star">*</span> New Category Name</label>
          <a-input v-model:value="createForm.customGroupName" placeholder="e.g. Contracts & Legal Agreements" class="h-10 text-xs font-semibold rounded-lg" />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-800 mb-1"><span class="required-star">*</span> Email Subject Line</label>
          <a-input v-model:value="createForm.subject" placeholder="e.g. Contract Renewal Notice for ##CLIENT_NAME## - ##CONTRACT_NAME##" class="h-10 text-xs font-semibold rounded-lg" />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-800 mb-1">Initial Email Body Content</label>
          <a-textarea v-model:value="createForm.body" :rows="4" placeholder="Enter initial body HTML or plain text... (e.g. Dear ##CLIENT_NAME##, your contract ##CONTRACT_NAME## is expiring on ##EXPIRY_DATE##...)" class="text-xs font-semibold rounded-lg" />
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
          <button type="button" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg border-none cursor-pointer transition-all" @click="showCreateModal = false">Cancel</button>
          <button type="button" class="px-5 py-2 text-white text-xs font-bold rounded-lg border-none cursor-pointer transition-all shadow-sm" style="background: var(--theme-primary, #9f8ed6);" @click="submitCreateCustomTemplate">Create Template</button>
        </div>
      </div>
    </a-modal>

    <!-- Edit Email Template Drawer (Exact 1:1 Match to Screenshot) -->
    <a-modal
      v-model:open="openDrawer"
      title="Edit Email Template"
      placement="right"
      :width="680"
      :footer-style="{ padding: '16px 24px', background: '#f8fafc', borderTop: '1px solid #e2e8f0' }"
      @close="resetForm"
    >
      <div v-if="selectedTemplate" class="edit-drawer-content font-sans">
        <a-form layout="vertical" :model="form" @finish="saveTemplate" class="space-y-5">
          <!-- Email Subject -->
          <a-form-item label="Email Subject" name="subject" :rules="[{ required: true, message: 'Email subject required' }]" class="drawer-form-item">
            <template #label>
              <span class="form-label-title"><span class="required-star">*</span> Email Subject</span>
            </template>
            <a-input 
              v-model:value="form.subject" 
              placeholder="e.g. Welcome to ##COMPANY_NAME##!" 
              class="subject-input-box" 
            />
          </a-form-item>

          <!-- Company Logo Management Card -->
          <div class="logo-uploader-card p-3.5 bg-indigo-50/50 border border-indigo-100 rounded-xl mb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-white border border-slate-200 rounded-lg flex items-center justify-center overflow-hidden p-1 shadow-2xs shrink-0">
                <img :src="companyLogoUrl" alt="Company Logo Preview" class="max-h-full max-w-full object-contain" />
              </div>
              <div>
                <h5 class="text-xs font-bold text-slate-800 m-0">Company Logo</h5>
                <p class="text-[11px] text-slate-500 m-0">Upload image to replace <code class="text-indigo-600 font-mono">##COMPANY_LOGO##</code> in emails</p>
              </div>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
              <label class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg cursor-pointer transition-all flex items-center gap-1 shadow-2xs">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <span>Upload Logo</span>
                <input type="file" accept="image/*" class="hidden" @change="uploadCompanyLogo($event)" />
              </label>
              <button type="button" class="px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg transition-all cursor-pointer" @click="insertVariable('COMPANY_LOGO')">
                + Insert Tag
              </button>
            </div>
          </div>

          <!-- Email Body (Quill Rich Text Editor) -->
          <a-form-item label="Email Body" name="body" class="drawer-form-item">
            <template #label>
              <span class="form-label-title"><span class="required-star">*</span> Email Body</span>
            </template>

            <div class="quill-editor-container">
              <QuillEditor
                ref="quillRef"
                v-model:content="form.body"
                content-type="html"
                :options="editorOptions"
                class="custom-quill-editor"
              />
            </div>
          </a-form-item>

          <!-- Available Variables Section -->
          <div class="variables-section mt-6">
            <h4 class="variables-title text-sm font-bold text-slate-900 mb-3">Available Variables:</h4>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <button 
                v-for="varTag in availableVariables" 
                :key="varTag" 
                type="button"
                class="var-chip-btn"
                @click="insertVariable(varTag)"
              >
                {{ varTag.replace(/##/g, '').replace(/[\{\}]/g, '') }}
              </button>
            </div>
          </div>
        </a-form>
      </div>

      <!-- Drawer Footer Action Bar -->
      <template #footer>
        <div class="drawer-footer-row flex items-center justify-end gap-3">
          <button 
            type="button" 
            class="btn-drawer-cancel" 
            @click="openDrawer = false"
          >
            Cancel
          </button>
          <button 
            type="button" 
            class="btn-drawer-update flex items-center justify-center gap-2"
            style="background: var(--theme-text-dark, #5f4f8d);"
            @click="saveTemplate"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            <span>Update</span>
          </button>
        </div>
      </template>
    </a-modal>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import { QuillEditor } from '@vueup/vue-quill';
import { useThemeStore } from '../../store/themeStore';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

export default defineComponent({
  name: 'EmailTemplates',
  components: { QuillEditor },
  setup() {
    const search = ref('');
    const audienceFilter = ref('all');
    const openDrawer = ref(false);
    const showCreateModal = ref(false);
    const selectedTemplate = ref(null);
    const quillRef = ref(null);
    const companyLogoUrl = ref('/images/logo.png');

    const uploadCompanyLogo = async (event) => {
      const file = event.target.files[0];
      if (!file) return;

      const formData = new FormData();
      formData.append('logo', file);

      try {
        const response = await axios.post('/api/email-templates/upload-logo', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data && response.data.url) {
          companyLogoUrl.value = response.data.url;
          message.success('Company logo uploaded successfully!');
        }
      } catch (e) {
        message.error('Failed to upload company logo image.');
      }
    };

    const themeStore = useThemeStore();

    onMounted(() => {
      themeStore.applyTheme();
    });

    const filterTabs = [
      { id: 'all', label: 'All Templates' },
      { id: 'company', label: 'Company' },
      { id: 'employee', label: 'Employee' },
      { id: 'customer', label: 'Customer' },
      { id: 'custom', label: 'Contracts & Custom' }
    ];

    const availableVariables = [
      'COMPANY_LOGO',
      'EMPLOYEE_NAME',
      'STAFF_EMAIL',
      'ROLE_NAME',
      'COMPANY_NAME',
      'SET_PASSWORD_URL',
      'LOGIN_URL',
      'CLIENT_NAME',
      'CONTRACT_NAME',
      'CONTRACT_VALUE',
      'EXPIRY_DATE',
      'LEAVE_TYPE',
      'START_DATE',
      'END_DATE',
      'REASON',
      'EXPENSE_AMOUNT',
      'ASSET_NAME',
      'STATUS',
      'NEWS_TITLE'
    ];

    const defaultGroups = [
      {
        name: 'Contracts & Custom Templates',
        audience: 'custom',
        templates: [
          { id: 401, name: 'Client Contract Expiration Notice', type: 'Contracts', subject: 'Contract Renewal Notice for ##CLIENT_NAME## - ##CONTRACT_NAME##', active: true, from_name: '{companyname} | Legal & Contracts', plain_text: false, body: '<p>Dear <strong>##CLIENT_NAME##</strong>,</p><p>This is a formal notification regarding the upcoming expiration and renewal of contract <strong>##CONTRACT_NAME##</strong> valued at <strong>##CONTRACT_VALUE##</strong> on <strong>##EXPIRY_DATE##</strong>.</p><p>Please review and sign the attached renewal document.</p><p>Best regards,<br>##COMPANY_NAME## Team</p>', is_custom: true },
          { id: 402, name: 'Non-Disclosure Agreement Expiration Alert', type: 'Contracts', subject: 'NDA Expiration Alert - ##COMPANY_NAME##', active: true, from_name: '{companyname} | Contracts', plain_text: false, body: '<p>Hello <strong>##CLIENT_NAME##</strong>,</p><p>Your NDA contract <strong>##CONTRACT_NAME##</strong> will expire on <strong>##EXPIRY_DATE##</strong>. Please reach out to extend terms.</p><p>Regards,<br>Legal Dept</p>', is_custom: true },
          { id: 403, name: 'Service Level Agreement (SLA) Review', type: 'Contracts', subject: 'SLA Review Notice - ##CONTRACT_NAME##', active: true, from_name: '{companyname} | Operations', plain_text: false, body: '<p>Dear <strong>##CLIENT_NAME##</strong>,</p><p>We are initiating the annual SLA review for <strong>##CONTRACT_NAME##</strong>.</p>', is_custom: true }
        ]
      },
      {
        name: 'Company Notifications (Admin Alerts)',
        audience: 'company',
        templates: [
          { id: 101, name: 'On Employee Leave Apply', type: 'Leave', subject: 'New Leave Request Submitted by ##EMPLOYEE_NAME##', active: true, from_name: '{companyname} | HR', plain_text: false, body: '<p>Hello,</p><p>New leave request submitted by <strong>##EMPLOYEE_NAME##</strong> for <strong>##LEAVE_TYPE##</strong> from ##START_DATE## to ##END_DATE##.</p><p>Reason: ##REASON##</p><p>Best regards,<br>HR Team</p>' },
          { id: 102, name: 'On Employee Clock In', type: 'Attendance', subject: 'Employee Clock-In Notification - ##EMPLOYEE_NAME##', active: true, from_name: '{companyname} | Attendance', plain_text: false, body: '<p>Hello,</p><p><strong>##EMPLOYEE_NAME##</strong> clocked in at ##START_DATE##.</p>' },
          { id: 103, name: 'On Employee Clock Out', type: 'Attendance', subject: 'Employee Clock-Out Notification - ##EMPLOYEE_NAME##', active: true, from_name: '{companyname} | Attendance', plain_text: false, body: '<p>Hello,</p><p><strong>##EMPLOYEE_NAME##</strong> clocked out at ##END_DATE##.</p>' },
          { id: 104, name: 'On Employee Resignation Apply', type: 'Resignation', subject: 'Resignation Submitted by ##EMPLOYEE_NAME##', active: true, from_name: '{companyname} | HR', plain_text: false, body: '<p>Hello,</p><p><strong>##EMPLOYEE_NAME##</strong> has submitted a resignation request.</p>' },
          { id: 105, name: 'On Employee Complaint Apply', type: 'Complaint', subject: 'New Complaint Submitted by ##EMPLOYEE_NAME##', active: true, from_name: '{companyname} | HR', plain_text: false, body: '<p>Hello,</p><p>A new complaint has been filed by <strong>##EMPLOYEE_NAME##</strong>.</p>' },
          { id: 106, name: 'On Employee Expense Apply', type: 'Expense', subject: 'New Expense Request Submitted by ##EMPLOYEE_NAME##', active: true, from_name: '{companyname} | Finance', plain_text: false, body: '<p>Hello,</p><p><strong>##EMPLOYEE_NAME##</strong> submitted an expense claim of ##EXPENSE_AMOUNT##.</p>' },
          { id: 107, name: 'On Employee Survey Submit', type: 'Survey', subject: 'Survey Feedback Submitted by ##EMPLOYEE_NAME##', active: true, from_name: '{companyname} | Surveys', plain_text: false, body: '<p>Hello,</p><p><strong>##EMPLOYEE_NAME##</strong> has submitted survey feedback.</p>' }
        ]
      },
      {
        name: 'Employee & Staff Notifications',
        audience: 'employee',
        templates: [
          { id: 201, name: 'On Birthday Reminder', type: 'Celebration', subject: 'Birthday Reminder - ##EMPLOYEE_NAME##', active: true, from_name: '{companyname} | HR', plain_text: false, body: '<p>Hello,</p><p>This is to remind you that <strong>##EMPLOYEE_NAME##</strong> is celebrating their birthday today! Let\'s take a moment to wish them and make their day special.</p><p>Join us in sending warm birthday wishes to <strong>##EMPLOYEE_NAME##</strong>.</p><p>Best regards,<br>HR Team</p>' },
          { id: 202, name: 'On Birthday Wish', type: 'Celebration', subject: 'Happy Birthday!', active: true, from_name: '{companyname} | Team', plain_text: false, body: '<p>Happy Birthday <strong>##EMPLOYEE_NAME##</strong>!</p><p>Wishing you a fantastic day filled with joy and success!</p><p>Best regards,<br>##COMPANY_NAME## Team</p>' },
          { id: 203, name: 'Employee Welcome Mail', type: 'Onboarding', subject: 'Welcome to ##COMPANY_NAME##!', active: true, from_name: '{companyname} | HR', plain_text: false, body: '<p>Dear <strong>##EMPLOYEE_NAME##</strong>,</p><p>Welcome to <strong>##COMPANY_NAME##</strong>! We are thrilled to have you join our team as <strong>##ROLE_NAME##</strong>.</p><p>An account has been created for your email: <strong>##STAFF_EMAIL##</strong>.</p><p>To activate your access and set up your password, please click below:</p><p><a href="##SET_PASSWORD_URL##" target="_blank" style="display:inline-block; padding:12px 28px; background:linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color:#ffffff; font-weight:bold; text-decoration:none; border-radius:99px;">Set Your Password &rarr;</a></p><p style="font-size:12px; color:#64748b; margin-top:20px;">If the button above does not work, copy and paste this link into your browser:<br><a href="##SET_PASSWORD_URL##">##SET_PASSWORD_URL##</a></p>' },
          { id: 204, name: 'On Leave Approve', type: 'Leave', subject: 'Your Leave Request Has Been Approved', active: true, from_name: '{companyname} | HR', plain_text: false, body: '<p>Dear <strong>##EMPLOYEE_NAME##</strong>,</p><p>Your leave request for <strong>##LEAVE_TYPE##</strong> (##START_DATE## to ##END_DATE##) has been approved.</p>' },
          { id: 205, name: 'On Leave Reject', type: 'Leave', subject: 'Your Leave Request Has Been Rejected', active: true, from_name: '{companyname} | HR', plain_text: false, body: '<p>Dear <strong>##EMPLOYEE_NAME##</strong>,</p><p>Your leave request for <strong>##LEAVE_TYPE##</strong> has been rejected.</p>' },
          { id: 206, name: 'On Resignation Approve', type: 'Resignation', subject: 'Resignation Acceptance Confirmation', active: true, from_name: '{companyname} | HR', plain_text: false, body: '<p>Dear <strong>##EMPLOYEE_NAME##</strong>,</p><p>Your resignation request has been accepted.</p>' },
          { id: 207, name: 'On Resignation Reject', type: 'Resignation', subject: 'Resignation Request Rejected', active: true, from_name: '{companyname} | HR', plain_text: false, body: '<p>Dear <strong>##EMPLOYEE_NAME##</strong>,</p><p>Your resignation request has been declined.</p>' },
          { id: 208, name: 'On Expense Approve', type: 'Expense', subject: 'Your Expense Request Has Been Approved', active: true, from_name: '{companyname} | Finance', plain_text: false, body: '<p>Dear <strong>##EMPLOYEE_NAME##</strong>,</p><p>Your expense claim of <strong>##EXPENSE_AMOUNT##</strong> has been approved.</p>' },
          { id: 209, name: 'On Expense Reject', type: 'Expense', subject: 'Your Expense Request Has Been Rejected', active: true, from_name: '{companyname} | Finance', plain_text: false, body: '<p>Dear <strong>##EMPLOYEE_NAME##</strong>,</p><p>Your expense claim has been rejected.</p>' },
          { id: 210, name: 'On Complaint Approve', type: 'Complaint', subject: 'Your Complaint Has Been Reviewed and Approved for Resolution', active: true, from_name: '{companyname} | HR', plain_text: false, body: '<p>Dear <strong>##EMPLOYEE_NAME##</strong>,</p><p>Your complaint has been reviewed and resolution action is underway.</p>' },
          { id: 211, name: 'On Complaint Reject', type: 'Complaint', subject: 'Complaint Request Rejected', active: true, from_name: '{companyname} | HR', plain_text: false, body: '<p>Dear <strong>##EMPLOYEE_NAME##</strong>,</p><p>Your complaint request has been reviewed.</p>' },
          { id: 212, name: 'On Task Assigned', type: 'Tasks', subject: 'New Task Assigned to You', active: true, from_name: '{companyname} | Projects', plain_text: false, body: '<p>Dear <strong>##EMPLOYEE_NAME##</strong>,</p><p>A new task has been assigned to you in the CRM.</p>' },
          { id: 213, name: 'On Survey Forms Assign', type: 'Surveys', subject: 'New Survey Now Available', active: true, from_name: '{companyname} | HR', plain_text: false, body: '<p>Dear <strong>##EMPLOYEE_NAME##</strong>,</p><p>A new survey is available for your feedback.</p>' }
        ]
      },
      {
        name: 'Customer & Client Notifications',
        audience: 'customer',
        templates: [
          { id: 301, name: 'New Contact Welcome Email', type: 'Customers', subject: 'Welcome to {companyname}', active: true, from_name: '{companyname} | Customer Success', plain_text: false, body: '<p>Dear {contact_firstname},</p><p>Welcome to {companyname}! We look forward to serving you.</p>' },
          { id: 302, name: 'Forgot Password (Customer)', type: 'Customers', subject: 'Password Reset Request', active: true, from_name: '{companyname} | Security', plain_text: false, body: '<p>Dear {contact_firstname},</p><p>Please click to reset password: {reset_url}</p>' }
        ]
      }
    ];

    const savedTemplates = localStorage.getItem('crm_email_templates_settings');
    const templateGroups = ref(savedTemplates ? JSON.parse(savedTemplates) : defaultGroups);

    onMounted(async () => {
      try {
        const response = await axios.get('/api/email-templates');
        if (response.data && response.data.data && Array.isArray(response.data.data)) {
          const dbList = response.data.data;
          dbList.forEach(dbItem => {
            templateGroups.value.forEach(group => {
              group.templates.forEach(t => {
                if (
                  (dbItem.key === 'welcome_staff' && (t.id === 203 || t.name.includes('Employee Welcome'))) ||
                  t.key === dbItem.key
                ) {
                  t.subject = dbItem.subject;
                  t.body = dbItem.body;
                  t.from_name = dbItem.from_name || t.from_name;
                  t.active = dbItem.active;
                }
              });
            });
          });
        }
      } catch (e) {
        console.warn('Could not fetch backend email templates:', e);
      }
    });

    const audienceCounts = computed(() => {
      const counts = { all: 0, company: 0, employee: 0, customer: 0, custom: 0 };
      templateGroups.value.forEach(group => {
        const len = group.templates ? group.templates.length : 0;
        counts.all += len;
        if (group.audience && counts[group.audience] !== undefined) {
          counts[group.audience] += len;
        }
      });
      return counts;
    });

    const filteredGroups = computed(() => {
      return templateGroups.value.map(group => {
        if (audienceFilter.value !== 'all' && group.audience && group.audience !== audienceFilter.value) {
          return null;
        }

        if (!search.value.trim()) {
          return group;
        }

        const query = search.value.toLowerCase();
        const matching = group.templates.filter(t => 
          t.name.toLowerCase().includes(query) || 
          t.subject.toLowerCase().includes(query) ||
          (t.type && t.type.toLowerCase().includes(query))
        );

        if (matching.length === 0) return null;

        return {
          ...group,
          templates: matching
        };
      }).filter(Boolean);
    });

    const form = reactive({
      name: '',
      subject: '',
      from_name: '',
      plain_text: false,
      body: ''
    });

    const createForm = reactive({
      name: '',
      groupName: 'Contracts & Custom Templates',
      customGroupName: '',
      audience: 'custom',
      subject: '',
      body: ''
    });

    const editorOptions = {
      theme: 'snow',
      modules: {
        toolbar: [
          [{ header: [1, 2, 3, false] }],
          [{ font: [] }],
          ['bold', 'italic', 'underline', 'strike'],
          ['image', 'video'],
          [{ list: 'ordered' }, { list: 'bullet' }],
          [{ script: 'sub' }, { script: 'super' }],
          [{ color: [] }, { background: [] }],
          [{ align: [] }]
        ]
      }
    };

    const disableAll = (group) => {
      group.templates.forEach(t => t.active = false);
      localStorage.setItem('crm_email_templates_settings', JSON.stringify(templateGroups.value));
      message.success(`All templates under ${group.name} disabled`);
    };

    const enableAll = (group) => {
      group.templates.forEach(t => t.active = true);
      localStorage.setItem('crm_email_templates_settings', JSON.stringify(templateGroups.value));
      message.success(`All templates under ${group.name} enabled`);
    };

    const toggleTemplate = (tmpl) => {
      tmpl.active = !tmpl.active;
      localStorage.setItem('crm_email_templates_settings', JSON.stringify(templateGroups.value));
      message.success(`${tmpl.name} has been ${tmpl.active ? 'enabled' : 'disabled'}`);
    };

    const openCreateModal = (targetGroupName) => {
      createForm.name = '';
      createForm.groupName = targetGroupName || (templateGroups.value.length > 0 ? templateGroups.value[0].name : 'Contracts & Custom Templates');
      createForm.customGroupName = '';
      createForm.audience = 'custom';
      createForm.subject = '';
      createForm.body = '<p>Hello <strong>##CLIENT_NAME##</strong>,</p><p>Enter your custom email template body content here...</p>';
      showCreateModal.value = true;
    };

    const submitCreateCustomTemplate = () => {
      if (!createForm.name.trim()) {
        message.error('Please enter a template name');
        return;
      }
      if (!createForm.subject.trim()) {
        message.error('Please enter an email subject line');
        return;
      }

      let finalGroupName = createForm.groupName;
      if (createForm.groupName === '__NEW__') {
        if (!createForm.customGroupName.trim()) {
          message.error('Please enter a name for the new category');
          return;
        }
        finalGroupName = createForm.customGroupName.trim();
      }

      let targetGroup = templateGroups.value.find(g => g.name === finalGroupName);
      if (!targetGroup) {
        targetGroup = {
          name: finalGroupName,
          audience: createForm.audience,
          templates: []
        };
        templateGroups.value.unshift(targetGroup);
      }

      const newTmpl = {
        id: Date.now(),
        name: createForm.name.trim(),
        type: 'Custom',
        subject: createForm.subject.trim(),
        active: true,
        from_name: '{companyname} | Custom',
        plain_text: false,
        body: createForm.body || '<p>Hello,</p><p>Custom template content...</p>',
        is_custom: true
      };

      targetGroup.templates.push(newTmpl);
      localStorage.setItem('crm_email_templates_settings', JSON.stringify(templateGroups.value));
      message.success(`Custom template "${newTmpl.name}" created successfully!`);
      showCreateModal.value = false;

      // Automatically open in edit drawer so user can refine in Quill
      openEditDrawer(newTmpl);
    };

    const deleteCustomTemplate = (group, tmpl) => {
      if (confirm(`Are you sure you want to delete the custom template "${tmpl.name}"?`)) {
        group.templates = group.templates.filter(t => t.id !== tmpl.id);
        localStorage.setItem('crm_email_templates_settings', JSON.stringify(templateGroups.value));
        message.success(`Custom template "${tmpl.name}" removed`);
      }
    };

    const openEditDrawer = (tmpl) => {
      selectedTemplate.value = tmpl;
      form.name = tmpl.name;
      form.subject = tmpl.subject;
      form.from_name = tmpl.from_name || '{companyname} | CRM';
      form.plain_text = tmpl.plain_text || false;
      form.body = tmpl.body || '';
      openDrawer.value = true;
    };

    const insertVariable = (varName) => {
      const tag = `##${varName}##`;
      
      if (quillRef.value) {
        try {
          const quill = quillRef.value.getQuill();
          if (quill) {
            const range = quill.getSelection(true);
            const index = range ? range.index : quill.getLength();
            quill.insertText(index, tag);
            quill.setSelection(index + tag.length);
            message.info(`Inserted ${tag}`);
            return;
          }
        } catch(e) {}
      }

      form.body = (form.body || '') + ' ' + tag;
      message.info(`Inserted ${tag}`);
    };

    const saveTemplate = async () => {
      if (selectedTemplate.value) {
        selectedTemplate.value.name = form.name;
        selectedTemplate.value.subject = form.subject;
        selectedTemplate.value.from_name = form.from_name;
        selectedTemplate.value.plain_text = form.plain_text;
        selectedTemplate.value.body = form.body;
        localStorage.setItem('crm_email_templates_settings', JSON.stringify(templateGroups.value));

        try {
          const tmplKey = (selectedTemplate.value.id === 203 || selectedTemplate.value.name.includes('Employee Welcome'))
            ? 'welcome_staff'
            : (selectedTemplate.value.key || `template_${selectedTemplate.value.id}`);

          await axios.post('/api/email-templates', {
            key: tmplKey,
            name: form.name,
            subject: form.subject,
            body: form.body,
            type: selectedTemplate.value.type || 'Onboarding',
            audience: selectedTemplate.value.audience || 'employee',
            from_name: form.from_name,
            active: selectedTemplate.value.active !== false,
          });
        } catch (e) {
          console.warn('Backend template sync error:', e);
        }

        message.success('Email template updated successfully');
      }
      openDrawer.value = false;
      resetForm();
    };

    const resetForm = () => {
      selectedTemplate.value = null;
      form.name = '';
      form.subject = '';
      form.from_name = '';
      form.plain_text = false;
      form.body = '';
    };

    return {
      search,
      audienceFilter,
      filterTabs,
      audienceCounts,
      openDrawer,
      showCreateModal,
      selectedTemplate,
      quillRef,
      companyLogoUrl,
      uploadCompanyLogo,
      editorOptions,
      availableVariables,
      templateGroups,
      form,
      createForm,
      filteredGroups,
      disableAll,
      enableAll,
      toggleTemplate,
      openCreateModal,
      submitCreateCustomTemplate,
      deleteCustomTemplate,
      openEditDrawer,
      insertVariable,
      saveTemplate,
      resetForm
    };
  }
});
</script>

<style scoped>
.email-templates-wrapper {
  font-family: 'Outfit', 'Inter', sans-serif;
}

.margin-0 {
  margin: 0 !important;
}

.required-star {
  color: #ef4444;
  font-weight: 700;
  margin-right: 2px;
}

.form-label-title {
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
}

.subject-input-box {
  border-radius: 10px !important;
  height: 42px !important;
  background: #f8fafc !important;
  border: 1px solid #e2e8f0 !important;
  font-size: 13.5px !important;
  font-weight: 500 !important;
  color: #1e293b !important;
}

.subject-input-box:focus {
  background: #ffffff !important;
  border-color: var(--theme-primary, #9f8ed6) !important;
  box-shadow: 0 0 0 3px rgba(159, 142, 214, 0.2) !important;
}

.theme-search-input:focus {
  border-color: var(--theme-primary, #9f8ed6) !important;
  box-shadow: 0 0 0 3px rgba(159, 142, 214, 0.2) !important;
}

.quill-editor-container {
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid var(--theme-primary, #9f8ed6);
  box-shadow: 0 0 0 3px rgba(159, 142, 214, 0.15);
}

:deep(.ql-toolbar.ql-snow) {
  background: #f8fafc;
  border: none !important;
  border-bottom: 1px solid #e2e8f0 !important;
  padding: 10px 12px;
}

:deep(.ql-container.ql-snow) {
  border: none !important;
  min-height: 220px;
  font-family: 'Inter', sans-serif;
  font-size: 13.5px;
  color: #1e293b;
}

.var-chip-btn {
  background: transparent;
  border: none;
  color: var(--theme-text-dark, #5f4f8d);
  font-weight: 600;
  font-size: 13px;
  font-family: inherit;
  cursor: pointer;
  text-align: left;
  padding: 2px 0;
  transition: color 0.15s ease;
}

.var-chip-btn:hover {
  color: var(--theme-primary-hover, #8d7bc8);
  text-decoration: underline;
}

/* Footer Action Buttons */
.btn-drawer-cancel {
  background: #f1f5f9;
  color: #475569;
  font-weight: 600;
  font-size: 13.5px;
  border-radius: 12px;
  height: 40px;
  border: none;
  padding: 0 22px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-drawer-cancel:hover {
  background: #e2e8f0;
  color: #1e293b;
}

.btn-drawer-update {
  color: #ffffff;
  font-weight: 700;
  font-size: 13.5px;
  border-radius: 12px;
  height: 40px;
  border: none;
  padding: 0 24px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(95, 79, 141, 0.25);
  transition: all 0.2s ease;
}

.btn-drawer-update:hover {
  box-shadow: 0 6px 16px rgba(95, 79, 141, 0.35);
  transform: translateY(-1px);
}
</style>
