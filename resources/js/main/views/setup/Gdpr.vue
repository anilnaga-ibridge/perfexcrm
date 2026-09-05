<template>
  <div class="gdpr-page p-6 max-w-[1500px] mx-auto min-h-screen bg-[#F8F7FA] font-['Public_Sans',sans-serif]">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
      <div>
        <div class="flex items-center gap-2">
          <span class="p-2 bg-[#7367F0]/10 text-[#7367F0] rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
          </span>
          <div>
            <h1 class="text-2xl font-bold text-[#4B465C] tracking-tight m-0">GDPR Compliance &amp; Privacy Controls</h1>
            <span class="text-xs text-[#82868B] font-medium">Manage data protection policies, subject access rights, consent logs, and data portability</span>
          </div>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <a href="https://gdpr-info.eu/" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-white border border-[#DBDADE] hover:bg-[#F8F7FA] text-[#4B465C] rounded-lg text-sm font-semibold transition-all shadow-sm">
          <svg class="w-4 h-4 text-[#7367F0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
          Official GDPR Guide
        </a>
        <button class="flex items-center gap-2 px-5 py-2 bg-[#7367F0] hover:bg-[#685dd8] text-white rounded-lg text-sm font-semibold transition-all shadow-sm shadow-[#7367F0]/30" :disabled="saving" @click="saveGdprSettings">
          <svg v-if="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
          <div v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          {{ saving ? 'Saving...' : 'Save Settings' }}
        </button>
      </div>
    </div>

    <!-- GDPR OFFICIAL NOTICE BANNER -->
    <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-l-4 border-l-[#7367F0]">
      <div class="flex items-start gap-3.5">
        <div class="w-10 h-10 rounded-lg bg-[#7367F0]/10 text-[#7367F0] flex items-center justify-center font-bold shrink-0 mt-0.5">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
          <h3 class="text-sm font-bold text-[#4B465C] m-0">General Data Protection Regulation (Regulation (EU) 2016/679)</h3>
          <p class="text-xs text-[#82868B] m-0 mt-1">
            Ensure your organization adheres to European data protection standards, explicit customer consent requirements, and statutory deletion/portability mandates.
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold" :class="settings.enabled ? 'bg-[#28C76F]/10 text-[#28C76F]' : 'bg-[#EA5455]/10 text-[#EA5455]'">
          {{ settings.enabled ? '● GDPR Active' : '○ GDPR Disabled' }}
        </span>
      </div>
    </div>

    <!-- MAIN TABS CONTAINER -->
    <div class="bg-white border border-[#EBE9F1] rounded-lg shadow-sm overflow-hidden">
      <!-- Tabs Header Navigation -->
      <div class="flex flex-wrap border-b border-[#EBE9F1] bg-[#F8F7FA] px-2 pt-2 gap-1">
        <button 
          v-for="tab in tabList" 
          :key="tab.key" 
          class="flex items-center gap-2 px-4 py-3 rounded-t-lg text-xs font-bold transition-all border-b-2 cursor-pointer"
          :class="activeTab === tab.key ? 'bg-white text-[#7367F0] border-[#7367F0] shadow-sm' : 'border-transparent text-[#82868B] hover:text-[#4B465C] hover:bg-white/60'"
          @click="activeTab = tab.key"
        >
          <span>{{ tab.icon }}</span>
          <span>{{ tab.label }}</span>
        </button>
      </div>

      <div class="p-6">
        <!-- ========================================== -->
        <!-- 1. GENERAL TAB                             -->
        <!-- ========================================== -->
        <div v-if="activeTab === 'general'" class="space-y-6">
          <div class="pb-4 border-b border-[#EBE9F1]">
            <h3 class="text-base font-bold text-[#4B465C] m-0">General GDPR Configuration</h3>
            <p class="text-xs text-[#82868B] m-0 mt-1">Control global visibility, public portal menu links, and introductory notice blocks.</p>
          </div>

          <div class="space-y-4">
            <!-- Setting: Enable GDPR -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-[#7367F0]/40 transition-colors">
              <div>
                <div class="font-bold text-sm text-[#4B465C]">Enable GDPR Module</div>
                <div class="text-xs text-[#82868B] mt-0.5">Toggle the display of GDPR options and privacy preference screens across the customer portal.</div>
              </div>
              <div class="inline-flex rounded-md shadow-sm" role="group">
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-l-md border transition-all"
                  :class="settings.enabled ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.enabled = true"
                >
                  Yes
                </button>
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-r-md border border-l-0 transition-all"
                  :class="!settings.enabled ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.enabled = false"
                >
                  No
                </button>
              </div>
            </div>

            <!-- Setting: Show in Navigation -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-[#7367F0]/40 transition-colors">
              <div>
                <div class="font-bold text-sm text-[#4B465C]">Show GDPR link in customer area navigation</div>
                <div class="text-xs text-[#82868B] mt-0.5">Display a dedicated "GDPR / Privacy" shortcut in the top header navbar of the client portal.</div>
              </div>
              <div class="inline-flex rounded-md shadow-sm" role="group">
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-l-md border transition-all"
                  :class="settings.show_nav_link ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.show_nav_link = true"
                >
                  Yes
                </button>
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-r-md border border-l-0 transition-all"
                  :class="!settings.show_nav_link ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.show_nav_link = false"
                >
                  No
                </button>
              </div>
            </div>

            <!-- Setting: Show in Footer -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-[#7367F0]/40 transition-colors">
              <div>
                <div class="font-bold text-sm text-[#4B465C]">Show GDPR link in customer area footer</div>
                <div class="text-xs text-[#82868B] mt-0.5">Display privacy and data rights link in the customer portal footer.</div>
              </div>
              <div class="inline-flex rounded-md shadow-sm" role="group">
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-l-md border transition-all"
                  :class="settings.show_footer_link ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.show_footer_link = true"
                >
                  Yes
                </button>
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-r-md border border-l-0 transition-all"
                  :class="!settings.show_footer_link ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.show_footer_link = false"
                >
                  No
                </button>
              </div>
            </div>

            <!-- Setting: Top Info Block -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white space-y-2">
              <label class="block text-sm font-bold text-[#4B465C]">GDPR page top information notice block</label>
              <span class="block text-xs text-[#82868B]">This information message is displayed at the top of the GDPR compliance screen in the client portal.</span>
              <textarea 
                v-model="settings.top_info_block" 
                rows="4" 
                class="w-full px-3 py-2 text-xs bg-white border border-[#DBDADE] rounded-md focus:outline-none focus:border-[#7367F0] text-[#4B465C] transition-colors"
                placeholder="Enter privacy disclosure text..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- 2. RIGHT TO DATA PORTABILITY TAB          -->
        <!-- ========================================== -->
        <div v-if="activeTab === 'portability'" class="space-y-6">
          <div class="pb-4 border-b border-[#EBE9F1]">
            <h3 class="text-base font-bold text-[#4B465C] m-0">Right to Data Portability (Article 20)</h3>
            <p class="text-xs text-[#82868B] m-0 mt-1">Allows contacts and leads to export their complete personal records in structured JSON / CSV machine-readable format.</p>
          </div>

          <div class="space-y-4">
            <!-- Portability Enable -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-[#7367F0]/40 transition-colors">
              <div>
                <div class="font-bold text-sm text-[#4B465C]">Enable Right to Data Portability</div>
                <div class="text-xs text-[#82868B] mt-0.5">Permits users to request and download their profile data directly from their dashboard.</div>
              </div>
              <div class="inline-flex rounded-md shadow-sm" role="group">
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-l-md border transition-all"
                  :class="settings.portability_enabled ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.portability_enabled = true"
                >
                  Yes
                </button>
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-r-md border border-l-0 transition-all"
                  :class="!settings.portability_enabled ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.portability_enabled = false"
                >
                  No
                </button>
              </div>
            </div>

            <!-- Export Roles -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white space-y-3">
              <div class="font-bold text-sm text-[#4B465C]">Allowed User Roles for Self-Service Export</div>
              <div class="flex flex-wrap gap-6 text-xs text-[#4B465C]">
                <label class="flex items-center gap-2 cursor-pointer font-medium">
                  <input type="checkbox" v-model="settings.allow_contacts_export" class="rounded text-[#7367F0] focus:ring-0" />
                  Allow Contacts to export profile data
                </label>
                <label class="flex items-center gap-2 cursor-pointer font-medium">
                  <input type="checkbox" v-model="settings.allow_leads_export" class="rounded text-[#7367F0] focus:ring-0" />
                  Allow Leads to export lead record data
                </label>
              </div>
            </div>

            <!-- Field Selection Checkbox Grid -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white space-y-3">
              <div class="font-bold text-sm text-[#4B465C]">Included Data Components in Export Package</div>
              <p class="text-xs text-[#82868B] m-0">Select which entities are packaged when a user triggers an Article 20 data portability export.</p>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                <div v-for="field in exportFieldOptions" :key="field.key" class="p-3 rounded-lg border border-[#EBE9F1] bg-[#F8F7FA] flex items-center gap-2 hover:border-[#7367F0]/40 transition-colors">
                  <input 
                    type="checkbox" 
                    :id="'exp_' + field.key" 
                    :value="field.key" 
                    v-model="settings.export_fields" 
                    class="rounded text-[#7367F0] focus:ring-0"
                  />
                  <label :for="'exp_' + field.key" class="text-xs font-semibold text-[#4B465C] cursor-pointer">{{ field.label }}</label>
                </div>
              </div>
            </div>

            <!-- Notice Textarea -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white space-y-2">
              <label class="block text-sm font-bold text-[#4B465C]">Notice on Data Portability Screen</label>
              <textarea 
                v-model="settings.portability_notice" 
                rows="3" 
                class="w-full px-3 py-2 text-xs bg-white border border-[#DBDADE] rounded-md focus:outline-none focus:border-[#7367F0] text-[#4B465C] transition-colors"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- 3. RIGHT TO ERASURE (RIGHT TO BE FORGOTTEN)-->
        <!-- ========================================== -->
        <div v-if="activeTab === 'erasure'" class="space-y-6">
          <div class="pb-4 border-b border-[#EBE9F1]">
            <h3 class="text-base font-bold text-[#4B465C] m-0">Right to Erasure / Right to be Forgotten (Article 17)</h3>
            <p class="text-xs text-[#82868B] m-0 mt-1">Configure account deletion requests, automatic record purging, and audit logging.</p>
          </div>

          <div class="space-y-4">
            <!-- Enable Erasure -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-[#7367F0]/40 transition-colors">
              <div>
                <div class="font-bold text-sm text-[#4B465C]">Enable Right to Erasure Requests</div>
                <div class="text-xs text-[#82868B] mt-0.5">Enables customers and leads to submit statutory deletion requests through the portal.</div>
              </div>
              <div class="inline-flex rounded-md shadow-sm" role="group">
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-l-md border transition-all"
                  :class="settings.erasure_enabled ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.erasure_enabled = true"
                >
                  Yes
                </button>
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-r-md border border-l-0 transition-all"
                  :class="!settings.erasure_enabled ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.erasure_enabled = false"
                >
                  No
                </button>
              </div>
            </div>

            <!-- Auto Delete vs Admin Review -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-[#7367F0]/40 transition-colors">
              <div>
                <div class="font-bold text-sm text-[#4B465C]">Automatically Delete Data Without Manual Staff Approval</div>
                <div class="text-xs text-[#82868B] mt-0.5">If enabled, data is wiped immediately upon user submission without waiting for admin sign-off.</div>
              </div>
              <div class="inline-flex rounded-md shadow-sm" role="group">
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-l-md border transition-all"
                  :class="settings.auto_delete ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.auto_delete = true"
                >
                  Yes
                </button>
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-r-md border border-l-0 transition-all"
                  :class="!settings.auto_delete ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.auto_delete = false"
                >
                  No (Manual Review)
                </button>
              </div>
            </div>

            <!-- Keep Record in DB -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-[#7367F0]/40 transition-colors">
              <div>
                <div class="font-bold text-sm text-[#4B465C]">Retain Anonymized Audit Log of Deletion Requests</div>
                <div class="text-xs text-[#82868B] mt-0.5">Keeps a secure timestamp and hashed identifier log for regulatory compliance proof.</div>
              </div>
              <div class="inline-flex rounded-md shadow-sm" role="group">
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-l-md border transition-all"
                  :class="settings.keep_record ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.keep_record = true"
                >
                  Yes
                </button>
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-r-md border border-l-0 transition-all"
                  :class="!settings.keep_record ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.keep_record = false"
                >
                  No
                </button>
              </div>
            </div>

            <!-- Notice Textarea -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white space-y-2">
              <label class="block text-sm font-bold text-[#4B465C]">Notice on Erasure Request Screen</label>
              <textarea 
                v-model="settings.erasure_notice" 
                rows="3" 
                class="w-full px-3 py-2 text-xs bg-white border border-[#DBDADE] rounded-md focus:outline-none focus:border-[#7367F0] text-[#4B465C] transition-colors"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- 4. RIGHT TO BE INFORMED TAB                -->
        <!-- ========================================== -->
        <div v-if="activeTab === 'informed'" class="space-y-6">
          <div class="pb-4 border-b border-[#EBE9F1]">
            <h3 class="text-base font-bold text-[#4B465C] m-0">Right to be Informed (Articles 13 &amp; 14)</h3>
            <p class="text-xs text-[#82868B] m-0 mt-1">Enforce mandatory privacy policy and terms agreements across customer registration, tickets, and lead forms.</p>
          </div>

          <div class="space-y-4">
            <!-- Require Agreement on Registration -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-[#7367F0]/40 transition-colors">
              <div>
                <div class="font-bold text-sm text-[#4B465C]">Require Privacy Agreement on Client Registration</div>
                <div class="text-xs text-[#82868B] mt-0.5">Show mandatory privacy policy checkbox during new customer account creation.</div>
              </div>
              <div class="inline-flex rounded-md shadow-sm" role="group">
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-l-md border transition-all"
                  :class="settings.require_terms_registration ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.require_terms_registration = true"
                >
                  Yes
                </button>
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-r-md border border-l-0 transition-all"
                  :class="!settings.require_terms_registration ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.require_terms_registration = false"
                >
                  No
                </button>
              </div>
            </div>

            <!-- Require Agreement on Tickets -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-[#7367F0]/40 transition-colors">
              <div>
                <div class="font-bold text-sm text-[#4B465C]">Require Privacy Agreement on Ticket Submission</div>
                <div class="text-xs text-[#82868B] mt-0.5">Prompt users to accept terms before creating a new support ticket.</div>
              </div>
              <div class="inline-flex rounded-md shadow-sm" role="group">
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-l-md border transition-all"
                  :class="settings.require_terms_tickets ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.require_terms_tickets = true"
                >
                  Yes
                </button>
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-r-md border border-l-0 transition-all"
                  :class="!settings.require_terms_tickets ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.require_terms_tickets = false"
                >
                  No
                </button>
              </div>
            </div>

            <!-- Require Agreement on Leads -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-[#7367F0]/40 transition-colors">
              <div>
                <div class="font-bold text-sm text-[#4B465C]">Require Privacy Agreement on Public Web-to-Lead Forms</div>
                <div class="text-xs text-[#82868B] mt-0.5">Show mandatory consent checkbox on embedded marketing and lead capture forms.</div>
              </div>
              <div class="inline-flex rounded-md shadow-sm" role="group">
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-l-md border transition-all"
                  :class="settings.require_terms_leads ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.require_terms_leads = true"
                >
                  Yes
                </button>
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-r-md border border-l-0 transition-all"
                  :class="!settings.require_terms_leads ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.require_terms_leads = false"
                >
                  No
                </button>
              </div>
            </div>

            <!-- URLs -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white space-y-2">
                <label class="block text-sm font-bold text-[#4B465C]">Privacy Policy URL</label>
                <input 
                  type="url" 
                  v-model="settings.privacy_policy_url" 
                  class="w-full px-3 py-2 text-xs bg-white border border-[#DBDADE] rounded-md focus:outline-none focus:border-[#7367F0] text-[#4B465C]"
                  placeholder="https://yourcompany.com/privacy"
                />
              </div>

              <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white space-y-2">
                <label class="block text-sm font-bold text-[#4B465C]">Terms &amp; Conditions URL</label>
                <input 
                  type="url" 
                  v-model="settings.terms_url" 
                  class="w-full px-3 py-2 text-xs bg-white border border-[#DBDADE] rounded-md focus:outline-none focus:border-[#7367F0] text-[#4B465C]"
                  placeholder="https://yourcompany.com/terms"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- 5. RIGHT OF ACCESS / RECTIFICATION         -->
        <!-- ========================================== -->
        <div v-if="activeTab === 'rectification'" class="space-y-6">
          <div class="pb-4 border-b border-[#EBE9F1]">
            <h3 class="text-base font-bold text-[#4B465C] m-0">Right of Access &amp; Rectification (Articles 15 &amp; 16)</h3>
            <p class="text-xs text-[#82868B] m-0 mt-1">Configure client self-service profile modification permissions and allowed editable fields.</p>
          </div>

          <div class="space-y-4">
            <!-- Allow Contacts to Edit -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-[#7367F0]/40 transition-colors">
              <div>
                <div class="font-bold text-sm text-[#4B465C]">Allow Contacts to Edit Profile Data</div>
                <div class="text-xs text-[#82868B] mt-0.5">Contacts can update their personal information in the customer portal directly.</div>
              </div>
              <div class="inline-flex rounded-md shadow-sm" role="group">
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-l-md border transition-all"
                  :class="settings.allow_contacts_edit ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.allow_contacts_edit = true"
                >
                  Yes
                </button>
                <button 
                  type="button" 
                  class="px-4 py-1.5 text-xs font-bold rounded-r-md border border-l-0 transition-all"
                  :class="!settings.allow_contacts_edit ? 'bg-[#7367F0] text-white border-[#7367F0]' : 'bg-white text-[#82868B] border-[#DBDADE] hover:bg-[#F8F7FA]'"
                  @click="settings.allow_contacts_edit = false"
                >
                  No
                </button>
              </div>
            </div>

            <!-- Allowed Fields Selection -->
            <div class="p-4 rounded-lg border border-[#EBE9F1] bg-white space-y-3">
              <div class="font-bold text-sm text-[#4B465C]">Allowed Editable Fields for Rectification</div>
              <p class="text-xs text-[#82868B] m-0">Select which fields the user can modify without requiring admin review.</p>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                <div v-for="field in rectificationFieldOptions" :key="field.key" class="p-3 rounded-lg border border-[#EBE9F1] bg-[#F8F7FA] flex items-center gap-2 hover:border-[#7367F0]/40 transition-colors">
                  <input 
                    type="checkbox" 
                    :id="'rec_' + field.key" 
                    :value="field.key" 
                    v-model="settings.rectification_fields" 
                    class="rounded text-[#7367F0] focus:ring-0"
                  />
                  <label :for="'rec_' + field.key" class="text-xs font-semibold text-[#4B465C] cursor-pointer">{{ field.label }}</label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ========================================== -->
        <!-- 6. CONSENT MANAGEMENT TAB                  -->
        <!-- ========================================== -->
        <div v-if="activeTab === 'consent'" class="space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#EBE9F1]">
            <div>
              <h3 class="text-base font-bold text-[#4B465C] m-0">Consent Purposes Management (Article 7)</h3>
              <p class="text-xs text-[#82868B] m-0 mt-1">Define specific tracking purposes, marketing opt-ins, and mandatory consent triggers.</p>
            </div>
            <button class="flex items-center gap-2 px-4 py-2 bg-[#7367F0] hover:bg-[#685dd8] text-white rounded-lg text-xs font-bold transition-all shadow-sm" @click="openNewPurposeDrawer">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
              Add New Purpose
            </button>
          </div>

          <!-- Table of Consent Purposes -->
          <div class="overflow-x-auto border border-[#EBE9F1] rounded-lg">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-[#F8F7FA] border-b border-[#EBE9F1] text-[11px] font-bold uppercase tracking-wider text-[#82868B]">
                  <th class="py-3 px-4 w-16">ID</th>
                  <th class="py-3 px-4">Purpose Name</th>
                  <th class="py-3 px-4">Description</th>
                  <th class="py-3 px-4 w-28">Type</th>
                  <th class="py-3 px-4 w-24">Status</th>
                  <th class="py-3 px-4 w-28 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EBE9F1]">
                <tr v-for="record in consentPurposes" :key="record.id" class="hover:bg-[#F8F7FA]/70">
                  <td class="py-3 px-4 font-mono font-bold text-[#82868B]">#{{ record.id }}</td>
                  <td class="py-3 px-4 font-bold text-[#4B465C]">{{ record.name }}</td>
                  <td class="py-3 px-4 text-[#82868B] max-w-xs truncate">{{ record.description || '—' }}</td>
                  <td class="py-3 px-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold" :class="record.required ? 'bg-[#EA5455]/10 text-[#EA5455]' : 'bg-[#7367F0]/10 text-[#7367F0]'">
                      {{ record.required ? 'Mandatory' : 'Optional' }}
                    </span>
                  </td>
                  <td class="py-3 px-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold" :class="record.active ? 'bg-[#28C76F]/10 text-[#28C76F]' : 'bg-[#82868B]/10 text-[#82868B]'">
                      {{ record.active ? 'Active' : 'Disabled' }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                      <button class="p-1 text-[#7367F0] hover:bg-[#7367F0]/10 rounded" title="Edit" @click="editPurpose(record)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                      </button>
                      <button class="p-1 text-[#EA5455] hover:bg-[#EA5455]/10 rounded" title="Delete" @click="deletePurpose(record.id)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!consentPurposes.length">
                  <td colspan="6" class="text-center py-10 text-xs text-[#82868B]">No consent purposes defined yet</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- CONSENT PURPOSE RIGHT-SIDE DRAWER -->
    <a-drawer
      v-model:open="openPurposeDrawer"
      :title="editingPurposeId ? 'Edit Consent Purpose' : 'Add New Consent Purpose'"
      placement="right"
      :width="520"
      @close="resetPurposeForm"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-xs font-bold text-[#4B465C] mb-1">Purpose Name *</label>
          <input 
            v-model="purposeForm.name" 
            type="text" 
            placeholder="e.g. Email Newsletter / Analytics Cookies" 
            class="w-full px-3 py-2 text-xs bg-white border border-[#DBDADE] rounded-md focus:outline-none focus:border-[#7367F0] text-[#4B465C]"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-[#4B465C] mb-1">Purpose Description</label>
          <textarea 
            v-model="purposeForm.description" 
            rows="4" 
            placeholder="Explain what user data is processed and why..." 
            class="w-full px-3 py-2 text-xs bg-white border border-[#DBDADE] rounded-md focus:outline-none focus:border-[#7367F0] text-[#4B465C]"
          ></textarea>
        </div>

        <div class="p-3 bg-[#F8F7FA] border border-[#EBE9F1] rounded-lg space-y-3">
          <label class="flex items-center gap-2 text-xs font-semibold text-[#4B465C] cursor-pointer">
            <input type="checkbox" v-model="purposeForm.required" class="rounded text-[#7367F0] focus:ring-0" />
            Mandatory Requirement (Users must accept to proceed)
          </label>
          <label class="flex items-center gap-2 text-xs font-semibold text-[#4B465C] cursor-pointer">
            <input type="checkbox" v-model="purposeForm.active" class="rounded text-[#7367F0] focus:ring-0" />
            Active (Display on customer/lead consent forms)
          </label>
        </div>
      </div>

      <template #footer>
        <div class="flex items-center justify-end gap-2">
          <button class="px-4 py-2 border border-[#DBDADE] text-[#4B465C] hover:bg-[#F8F7FA] text-xs font-semibold rounded-md transition-all" @click="openPurposeDrawer = false">
            Cancel
          </button>
          <button class="px-5 py-2 bg-[#7367F0] hover:bg-[#685dd8] text-white text-xs font-bold rounded-md transition-all shadow-sm shadow-[#7367F0]/30" @click="savePurpose">
            {{ editingPurposeId ? 'Update Purpose' : 'Save Purpose' }}
          </button>
        </div>
      </template>
    </a-drawer>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { message } from 'ant-design-vue'

const activeTab = ref('general')
const saving = ref(false)
const openPurposeDrawer = ref(false)
const editingPurposeId = ref(null)

const tabList = [
  { key: 'general',       label: 'General',                           icon: '🛡️' },
  { key: 'portability',   label: 'Right to Data Portability',         icon: '📦' },
  { key: 'erasure',       label: 'Right to Erasure (Forgotten)',      icon: '🗑️' },
  { key: 'informed',      label: 'Right to be Informed',              icon: '📜' },
  { key: 'rectification', label: 'Right of Access / Rectification',   icon: '✏️' },
  { key: 'consent',       label: 'Consent Management',                icon: '✍️' },
]

const settings = reactive({
  enabled: true,
  show_nav_link: true,
  show_footer_link: true,
  top_info_block: 'General Data Protection Regulation (GDPR) compliance is enabled. Manage your personal data, portability archives, deletion requests, and explicit marketing consents directly from this panel.',

  portability_enabled: true,
  allow_contacts_export: true,
  allow_leads_export: true,
  export_fields: ['contact_info', 'tickets', 'invoices', 'estimates', 'proposals', 'projects', 'consent_history'],
  portability_notice: 'In accordance with Article 20 of the GDPR, you have the right to receive personal data concerning you in a structured, commonly used and machine-readable format.',

  erasure_enabled: true,
  auto_delete: false,
  keep_record: true,
  delete_completely: false,
  erasure_notice: 'In accordance with Article 17 of the GDPR, you may request the erasure of personal data concerning you without undue delay.',

  require_terms_registration: true,
  require_terms_tickets: false,
  require_terms_leads: true,
  privacy_policy_url: 'https://example.com/privacy-policy',
  terms_url: 'https://example.com/terms-and-conditions',

  allow_contacts_edit: true,
  allow_leads_edit: false,
  rectification_fields: ['first_name', 'last_name', 'email', 'phone', 'company', 'address', 'city', 'zip'],

  consent_enabled: true
})

const exportFieldOptions = [
  { key: 'contact_info',    label: 'Contact Details' },
  { key: 'tickets',         label: 'Support Tickets' },
  { key: 'invoices',        label: 'Invoices & Bills' },
  { key: 'estimates',       label: 'Estimates' },
  { key: 'proposals',       label: 'Proposals' },
  { key: 'projects',        label: 'Projects' },
  { key: 'tasks',           label: 'Assigned Tasks' },
  { key: 'consent_history', label: 'Consent History' },
]

const rectificationFieldOptions = [
  { key: 'first_name', label: 'First Name' },
  { key: 'last_name',  label: 'Last Name' },
  { key: 'email',      label: 'Email Address' },
  { key: 'phone',      label: 'Phone Number' },
  { key: 'company',    label: 'Company Name' },
  { key: 'address',    label: 'Address' },
  { key: 'city',       label: 'City' },
  { key: 'zip',        label: 'ZIP / Postal Code' },
]

const consentPurposes = ref([
  { id: 1, name: 'Email Marketing & Newsletters', description: 'Receive regular newsletter updates, promotional discounts, and product announcements.', required: false, active: true },
  { id: 2, name: 'Transactional SMS Notifications', description: 'Receive instant critical alerts, invoice notifications, and task status updates via SMS.', required: false, active: true },
  { id: 3, name: 'Third-Party Analytics & Tracking', description: 'Permit anonymized behavioral tracking to improve software performance.', required: false, active: true },
  { id: 4, name: 'Terms of Service & Core Processing', description: 'Essential data processing required to deliver purchased CRM subscriptions.', required: true, active: true }
])

const purposeForm = reactive({
  name: '',
  description: '',
  required: false,
  active: true
})

const saveGdprSettings = () => {
  saving.value = true
  setTimeout(() => {
    saving.value = false
    message.success('GDPR Compliance Settings saved successfully!')
  }, 600)
}

const openNewPurposeDrawer = () => {
  resetPurposeForm()
  openPurposeDrawer.value = true
}

const editPurpose = (record) => {
  editingPurposeId.value = record.id
  purposeForm.name = record.name
  purposeForm.description = record.description || ''
  purposeForm.required = record.required || false
  purposeForm.active = record.active !== undefined ? record.active : true
  openPurposeDrawer.value = true
}

const deletePurpose = (id) => {
  consentPurposes.value = consentPurposes.value.filter(p => p.id !== id)
  message.success('Consent purpose deleted')
}

const savePurpose = () => {
  if (!purposeForm.name.trim()) {
    message.error('Please enter a purpose name')
    return
  }

  if (editingPurposeId.value) {
    const item = consentPurposes.value.find(p => p.id === editingPurposeId.value)
    if (item) {
      item.name = purposeForm.name.trim()
      item.description = purposeForm.description
      item.required = purposeForm.required
      item.active = purposeForm.active
    }
    message.success('Consent purpose updated successfully')
  } else {
    const maxId = consentPurposes.value.reduce((max, p) => (p.id > max ? p.id : max), 0)
    consentPurposes.value.push({
      id: maxId + 1,
      name: purposeForm.name.trim(),
      description: purposeForm.description,
      required: purposeForm.required,
      active: purposeForm.active
    })
    message.success('New consent purpose added')
  }
  openPurposeDrawer.value = false
  resetPurposeForm()
}

const resetPurposeForm = () => {
  editingPurposeId.value = null
  purposeForm.name = ''
  purposeForm.description = ''
  purposeForm.required = false
  purposeForm.active = true
}
</script>

<style scoped>
:deep(.ant-drawer-header) {
  border-bottom: 1px solid #EBE9F1 !important;
}
:deep(.ant-drawer-footer) {
  border-top: 1px solid #EBE9F1 !important;
  padding: 12px 16px !important;
}
</style>
