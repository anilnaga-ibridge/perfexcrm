<template>
  <div class="staff-detail-page max-w-7xl mx-auto px-4 py-6 font-sans text-[#5D596C]" v-if="staff">
    <!-- Breadcrumbs & Top Bar -->
    <div class="flex items-center justify-between mb-5">
      <div class="flex items-center gap-2 text-xs text-[#A8AAAE] font-medium">
        <router-link :to="backLink" class="inline-flex items-center gap-1 hover:text-[#7367F0] transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          Staff Members
        </router-link>
        <span class="text-[#DBDADE]">/</span>
        <span class="text-[#4B465C] font-semibold truncate max-w-[200px]">{{ staff.name || 'Member Details' }}</span>
      </div>

      <div class="flex items-center gap-2" v-if="!isCreateMode">
        <span :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-semibold tracking-wide border', staff.active ? 'bg-emerald-50 text-emerald-600 border-emerald-200 dark:bg-emerald-950/30' : 'bg-rose-50 text-rose-600 border-rose-200 dark:bg-rose-950/30']">
          <span :class="['w-2 h-2 rounded-full', staff.active ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500']"></span>
          {{ staff.active ? 'Active Staff' : 'Inactive' }}
        </span>
      </div>
    </div>

    <!-- Timesheet Summary Dashboard Widget (Edit Mode only) -->
    <div class="bg-white border border-[#EBE9F1] rounded-xl p-5 mb-6 shadow-sm" v-if="!isCreateMode">
      <div class="flex items-center justify-between mb-3 border-b border-[#F1F0F2] pb-3">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-lg bg-[rgba(115,103,240,0.12)] text-[#7367F0] flex items-center justify-center font-bold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <h3 class="font-bold text-sm text-[#4B465C] tracking-tight m-0">Timesheet Summary</h3>
        </div>
        <router-link to="/admin/timesheets" class="inline-flex items-center gap-1 text-xs font-semibold text-[#7367F0] hover:underline">
          Detailed Overview of Logged Timesheets and Hours &rarr;
        </router-link>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
        <div class="bg-[#F8F7FA] border border-[#EBE9F1] rounded-lg p-3">
          <div class="text-[10px] font-bold text-[#A8AAAE] uppercase tracking-wider mb-1">Total Logged Time</div>
          <div class="text-base font-mono font-bold text-[#4B465C]">{{ summaryStats.total_logged || '00:00' }}</div>
        </div>
        <div class="bg-[#F8F7FA] border border-[#EBE9F1] rounded-lg p-3">
          <div class="text-[10px] font-bold text-[#A8AAAE] uppercase tracking-wider mb-1">Last Month</div>
          <div class="text-base font-mono font-bold text-[#4B465C]">{{ summaryStats.last_month || '00:00' }}</div>
        </div>
        <div class="bg-[#F8F7FA] border border-[#EBE9F1] rounded-lg p-3">
          <div class="text-[10px] font-bold text-[#7367F0] uppercase tracking-wider mb-1">This Month</div>
          <div class="text-base font-mono font-bold text-[#7367F0]">{{ summaryStats.this_month || '00:00' }}</div>
        </div>
        <div class="bg-[#F8F7FA] border border-[#EBE9F1] rounded-lg p-3">
          <div class="text-[10px] font-bold text-[#A8AAAE] uppercase tracking-wider mb-1">Last Week</div>
          <div class="text-base font-mono font-bold text-[#4B465C]">{{ summaryStats.last_week || '00:00' }}</div>
        </div>
        <div class="bg-[#F8F7FA] border border-[#EBE9F1] rounded-lg p-3">
          <div class="text-[10px] font-bold text-[#A8AAAE] uppercase tracking-wider mb-1">This Week</div>
          <div class="text-base font-mono font-bold text-[#4B465C]">{{ summaryStats.this_week || '00:00' }}</div>
        </div>
      </div>
    </div>

    <!-- Main Card Container: Vertical Stepper Layout -->
    <div class="bg-white border border-[#EBE9F1] rounded-xl shadow-[0_2px_9px_rgba(47,43,61,0.06)] overflow-hidden">
      <div class="grid grid-cols-1 lg:grid-cols-12 min-h-[640px]">
        
        <!-- ========================================================================= -->
        <!-- LEFT COLUMN: VUEXY VERTICAL STEPPER NAVIGATION & MEMBER HERO -->
        <!-- ========================================================================= -->
        <div class="lg:col-span-4 border-b lg:border-b-0 lg:border-r border-[#EBE9F1] bg-[#FCFCFD] p-6 flex flex-col justify-between">
          
          <div>
            <!-- Member Header Summary in Sidebar (Edit Mode) -->
            <div v-if="!isCreateMode" class="mb-6 pb-6 border-b border-[#EBE9F1]">
              <div class="flex items-center gap-3.5">
                <div class="relative group cursor-pointer" @click="triggerUpload" title="Click to upload/change photo">
                  <div v-if="!form.profile_image || headerImageError" class="w-14 h-14 rounded-xl relative overflow-hidden flex items-center justify-center shadow-sm bg-gradient-to-tr from-[#7367F0] to-[#9E95F5] text-white font-extrabold text-lg">
                    {{ initials(staff.name) }}
                  </div>
                  <img v-else :src="getProfileImageUrl(form.profile_image)" @error="headerImageError = true" class="w-14 h-14 rounded-xl object-cover ring-2 ring-[#7367F0]/20 shadow-sm" />
                  
                  <div class="absolute inset-0 rounded-xl bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  </div>
                  <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full border-2 border-white bg-emerald-500 z-10"></span>
                </div>

                <div class="min-w-0 flex-1">
                  <h3 class="font-bold text-base text-[#4B465C] truncate m-0 leading-snug">{{ staff.name }}</h3>
                  <div class="text-xs text-[#A8AAAE] truncate mt-0.5">{{ staff.email }}</div>
                  <div class="mt-1.5 flex items-center gap-1.5 flex-wrap">
                    <span class="inline-block px-2 py-0.5 rounded bg-[rgba(115,103,240,0.12)] text-[#7367F0] text-[11px] font-semibold capitalize">
                      {{ staff.role_data?.name || staff.role || 'Employee' }}
                    </span>
                    <span class="inline-block px-2 py-0.5 rounded bg-[#F1F0F2] text-[#6F6B7D] text-[11px] font-bold">
                      ${{ (Number(staff.hourly_rate ?? form.hourly_rate) || 0).toFixed(2) }}/hr
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Create Mode Hero Banner -->
            <div v-else class="mb-6 pb-6 border-b border-[#EBE9F1]">
              <div class="w-10 h-10 rounded-xl bg-[rgba(115,103,240,0.12)] text-[#7367F0] flex items-center justify-center mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
              </div>
              <h3 class="font-bold text-base text-[#4B465C] m-0">Add New Staff Member</h3>
              <p class="text-xs text-[#A8AAAE] m-0 mt-1">Configure staff account, role privileges & social links</p>
            </div>

            <!-- VUEXY VERTICAL STEPPER ITEMS -->
            <div class="space-y-3">
              <div 
                v-for="(s, idx) in numberedSteps" 
                :key="s.title"
                class="group relative flex items-start gap-3.5 p-3 rounded-lg cursor-pointer transition-all"
                :class="currentStep === idx && activeExtraTab === 'profile' ? 'bg-white shadow-[0_2px_8px_rgba(115,103,240,0.12)] border border-[#7367F0]/30' : 'hover:bg-[#F1F0F2]/50 border border-transparent'"
                @click="goToStep(idx)"
              >
                <!-- Number Badge -->
                <div 
                  class="w-9 h-9 rounded-lg flex items-center justify-center text-xs font-bold shrink-0 transition-all"
                  :class="[
                    currentStep === idx && activeExtraTab === 'profile'
                      ? 'bg-[#7367F0] text-white shadow-md shadow-[#7367F0]/30'
                      : currentStep > idx && activeExtraTab === 'profile'
                        ? 'bg-[rgba(40,199,111,0.15)] text-[#28C76F]'
                        : 'bg-[#F1F0F2] text-[#6F6B7D]'
                  ]"
                >
                  <span v-if="currentStep > idx && activeExtraTab === 'profile'">✓</span>
                  <span v-else>0{{ idx + 1 }}</span>
                </div>

                <div class="flex-1 min-w-0 pt-0.5">
                  <div class="text-xs font-bold leading-tight" :class="currentStep === idx && activeExtraTab === 'profile' ? 'text-[#7367F0]' : 'text-[#4B465C]'">
                    {{ s.title }}
                  </div>
                  <div class="text-[11px] text-[#A8AAAE] truncate mt-0.5">{{ s.subtitle }}</div>
                </div>
              </div>
            </div>

            <!-- Extra Tabs for Edit Mode (Notes, Timesheets, Projects) -->
            <div v-if="!isCreateMode" class="mt-6 pt-6 border-t border-[#EBE9F1] space-y-2">
              <div class="text-[11px] font-bold text-[#A8AAAE] uppercase tracking-wider mb-2 px-3">Management</div>
              
              <button 
                type="button" 
                v-for="tab in extraTabs" 
                :key="tab.key"
                class="w-full flex items-center justify-between p-2.5 px-3 rounded-lg text-xs font-semibold transition-all border-0 text-left cursor-pointer"
                :class="activeExtraTab === tab.key ? 'bg-[#7367F0] text-white shadow-sm' : 'bg-transparent text-[#5D596C] hover:bg-[#F1F0F2]'"
                @click="activeExtraTab = tab.key"
              >
                <div class="flex items-center gap-2.5">
                  <span v-html="tab.icon"></span>
                  <span>{{ tab.label }}</span>
                </div>
                <span class="text-[10px] opacity-70" v-if="tab.count !== undefined">({{ tab.count }})</span>
              </button>
            </div>

          </div>

          <!-- Bottom Help Notice -->
          <div class="mt-8 pt-4 border-t border-[#EBE9F1] text-[11px] text-[#A8AAAE] flex items-center justify-between">
            <span>Staff ID: #{{ staff.id || 'New' }}</span>
            <a-typography-link href="#" @click.prevent="showPermissionsInfo = true" class="text-[#7367F0] font-semibold hover:underline">
              Permissions Guide
            </a-typography-link>
          </div>

        </div>

        <!-- ========================================================================= -->
        <!-- RIGHT COLUMN: STEPPER CONTENT FORMS & EXTRA PANELS -->
        <!-- ========================================================================= -->
        <div class="lg:col-span-8 p-6 sm:p-8 flex flex-col justify-between">
          
          <!-- Hidden file input for photo upload -->
          <input type="file" ref="fileInput" accept="image/*" style="display: none;" @change="onFileChange" />

          <!-- STEPPER FORM CONTENT (When activeExtraTab is 'profile') -->
          <div v-if="activeExtraTab === 'profile'">
            <a-form layout="vertical" :model="form" ref="profileFormRef" @finish="saveProfile">
              
              <!-- ───────────────────────────────────────────────────────────── -->
              <!-- STEP 0: ACCOUNT DETAILS -->
              <!-- ───────────────────────────────────────────────────────────── -->
              <div v-if="currentStep === 0" class="animate-fadeIn space-y-6">
                <div class="border-b border-[#F1F0F2] pb-4">
                  <h4 class="text-base font-bold text-[#4B465C] m-0">Account Details</h4>
                  <p class="text-xs text-[#A8AAAE] m-0 mt-1">Enter member identity, administrative access, and login credentials</p>
                </div>

                <!-- Account Role & Access Pill Banner -->
                <div class="bg-[#F8F7FA] border border-[#EBE9F1] rounded-xl p-4 flex flex-wrap items-center justify-between gap-4">
                  <div class="text-xs font-bold text-[#4B465C] flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#7367F0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Account Privilege Flags</span>
                  </div>

                  <div class="flex flex-wrap items-center gap-4 bg-white px-4 py-2 border border-[#DBDADE] rounded-lg">
                    <a-checkbox :checked="isAdminRole" @change="toggleAdmin" class="text-xs font-semibold text-[#5D596C]">
                      Administrator (Full Access)
                    </a-checkbox>
                    <a-checkbox v-model:checked="form.not_staff" class="text-xs font-semibold text-[#5D596C]">
                      Not Staff Member
                    </a-checkbox>
                  </div>
                </div>

                <!-- Identity Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <a-form-item label="First Name" name="first_name" :rules="[{ required: true, message: 'First name is required' }]">
                    <a-input v-model:value="form.first_name" placeholder="John" size="large" class="rounded-lg text-xs" />
                  </a-form-item>

                  <a-form-item label="Last Name" name="last_name" :rules="[{ required: true, message: 'Last name is required' }]">
                    <a-input v-model:value="form.last_name" placeholder="Doe" size="large" class="rounded-lg text-xs" />
                  </a-form-item>

                  <a-form-item label="Email Address" name="email" :rules="[{ required: true, type: 'email', message: 'Valid email required' }]">
                    <a-input v-model:value="form.email" placeholder="john.doe@company.com" size="large" class="rounded-lg text-xs" />
                  </a-form-item>

                  <a-form-item label="Phone Number">
                    <a-input v-model:value="form.phone" placeholder="+1 (555) 019-2834" size="large" class="rounded-lg text-xs" />
                  </a-form-item>
                </div>

                <!-- Password Section -->
                <div class="bg-[#F8F7FA] border border-[#EBE9F1] rounded-xl p-4">
                  <div class="text-xs font-bold text-[#4B465C] mb-3">Security & Password</div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a-form-item label="Password" :name="isCreateMode ? 'password' : ''" :rules="isCreateMode ? [{ required: true, message: 'Password is required' }] : []">
                      <div class="relative">
                        <a-input
                          v-model:value="form.password"
                          :type="isPasswordVisible ? 'text' : 'password'"
                          placeholder="············"
                          size="large"
                          class="rounded-lg text-xs pr-10"
                        />
                        <button
                          type="button"
                          class="absolute right-3 top-3 text-[#A8AAAE] hover:text-[#7367F0] bg-transparent border-0 p-0 cursor-pointer text-xs"
                          @click="isPasswordVisible = !isPasswordVisible"
                        >
                          <span v-if="isPasswordVisible">👁️‍🗨️</span>
                          <span v-else>👁️</span>
                        </button>
                      </div>
                      <div class="text-[11px] text-[#A8AAAE] mt-1" v-if="!isCreateMode">Leave blank to keep existing password</div>
                    </a-form-item>

                    <a-form-item label="Confirm Password">
                      <div class="relative">
                        <a-input
                          v-model:value="form.password_confirmation"
                          :type="isCPasswordVisible ? 'text' : 'password'"
                          placeholder="············"
                          size="large"
                          class="rounded-lg text-xs pr-10"
                        />
                        <button
                          type="button"
                          class="absolute right-3 top-3 text-[#A8AAAE] hover:text-[#7367F0] bg-transparent border-0 p-0 cursor-pointer text-xs"
                          @click="isCPasswordVisible = !isCPasswordVisible"
                        >
                          <span v-if="isCPasswordVisible">👁️‍🗨️</span>
                          <span v-else>👁️</span>
                        </button>
                      </div>
                    </a-form-item>
                  </div>
                </div>

                <!-- Photo Upload Area -->
                <div class="border border-dashed border-[#DBDADE] rounded-xl p-4 flex items-center justify-between gap-4 bg-white">
                  <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-[#F8F7FA] border border-[#EBE9F1] overflow-hidden flex items-center justify-center shrink-0">
                      <img v-if="form.profile_image" :src="getProfileImageUrl(form.profile_image)" class="w-full h-full object-cover" />
                      <span v-else class="text-xs font-bold text-[#A8AAAE]">No Pic</span>
                    </div>
                    <div>
                      <div class="text-xs font-bold text-[#4B465C]">Profile Photo</div>
                      <div class="text-[11px] text-[#A8AAAE]">PNG, JPG or GIF up to 2MB</div>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <button type="button" class="btn-outline text-xs" @click="triggerUpload">Choose File</button>
                    <button type="button" class="text-xs text-rose-500 hover:underline bg-transparent border-0 cursor-pointer" v-if="form.profile_image" @click="removePhoto">Remove</button>
                  </div>
                </div>

                <!-- Welcome Email Option -->
                <div class="pt-1">
                  <a-checkbox v-model:checked="form.send_welcome_email" class="text-xs font-semibold text-[#5D596C]">
                    Send welcome email with login credentials upon saving
                  </a-checkbox>
                </div>
              </div>

              <!-- ───────────────────────────────────────────────────────────── -->
              <!-- STEP 1: PERSONAL & WORK INFO -->
              <!-- ───────────────────────────────────────────────────────────── -->
              <div v-if="currentStep === 1" class="animate-fadeIn space-y-6">
                <div class="border-b border-[#F1F0F2] pb-4">
                  <h4 class="text-base font-bold text-[#4B465C] m-0">Personal &amp; Work Info</h4>
                  <p class="text-xs text-[#A8AAAE] m-0 mt-1">Configure staff role, billing rates, departments, and regional preferences</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <a-form-item label="Staff Role" name="role_id">
                    <a-select v-model:value="form.role_id" style="width: 100%" size="large" @change="onRoleChange" class="rounded-lg text-xs">
                      <a-select-option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</a-select-option>
                    </a-select>
                  </a-form-item>

                  <a-form-item label="Hourly Rate ($)" name="hourly_rate">
                    <a-input-number v-model:value="form.hourly_rate" :min="0" :precision="2" style="width: 100%" size="large" class="rounded-lg text-xs">
                      <template #addonBefore>$</template>
                    </a-input-number>
                  </a-form-item>

                  <a-form-item label="Member Departments" class="md:col-span-2">
                    <div class="bg-[#F8F7FA] border border-[#EBE9F1] p-3.5 rounded-xl">
                      <a-checkbox-group v-model:value="form.departments" class="flex flex-wrap gap-4 text-xs font-semibold text-[#5D596C]">
                        <a-checkbox value="Marketing">Marketing</a-checkbox>
                        <a-checkbox value="Sales">Sales</a-checkbox>
                        <a-checkbox value="Abuse">Abuse</a-checkbox>
                      </a-checkbox-group>
                    </div>
                  </a-form-item>

                  <a-form-item label="Default Language">
                    <a-select v-model:value="form.default_language" style="width: 100%" size="large" class="rounded-lg text-xs">
                      <a-select-option value="">System Default</a-select-option>
                      <a-select-option value="en">English</a-select-option>
                      <a-select-option value="es">Spanish</a-select-option>
                      <a-select-option value="fr">French</a-select-option>
                      <a-select-option value="de">German</a-select-option>
                      <a-select-option value="no">Norwegian</a-select-option>
                      <a-select-option value="pt_br">Portuguese_br</a-select-option>
                      <a-select-option value="bg">Bulgarian</a-select-option>
                      <a-select-option value="it">Italian</a-select-option>
                      <a-select-option value="cs">Czech</a-select-option>
                      <a-select-option value="fa">Persian</a-select-option>
                      <a-select-option value="fi">Finnish</a-select-option>
                      <a-select-option value="fr_ca">Francais_canada</a-select-option>
                      <a-select-option value="id">Indonesia</a-select-option>
                      <a-select-option value="pt">Portuguese</a-select-option>
                      <a-select-option value="ja">Japanese</a-select-option>
                      <a-select-option value="nl">Dutch</a-select-option>
                      <a-select-option value="sv">Swedish</a-select-option>
                      <a-select-option value="uk">Ukrainian</a-select-option>
                      <a-select-option value="vi">Vietnamese</a-select-option>
                      <a-select-option value="tr">Turkish</a-select-option>
                      <a-select-option value="zh">Chinese</a-select-option>
                      <a-select-option value="ro">Romanian</a-select-option>
                      <a-select-option value="sk">Slovak</a-select-option>
                      <a-select-option value="ru">Russian</a-select-option>
                      <a-select-option value="pl">Polish</a-select-option>
                      <a-select-option value="ca">Catalan</a-select-option>
                    </a-select>
                  </a-form-item>

                  <a-form-item label="Text Direction">
                    <a-select v-model:value="form.direction" style="width: 100%" size="large" class="rounded-lg text-xs">
                      <a-select-option value="">LTR (Left-to-Right)</a-select-option>
                      <a-select-option value="rtl">RTL (Right-to-Left)</a-select-option>
                    </a-select>
                  </a-form-item>

                  <a-form-item label="Email Signature" class="md:col-span-2">
                    <a-textarea v-model:value="form.email_signature" :rows="3" placeholder="Enter custom email signature block..." class="rounded-lg text-xs" />
                  </a-form-item>
                </div>
              </div>

              <!-- ───────────────────────────────────────────────────────────── -->
              <!-- STEP 2: SOCIAL LINKS -->
              <!-- ───────────────────────────────────────────────────────────── -->
              <div v-if="currentStep === 2" class="animate-fadeIn space-y-6">
                <div class="border-b border-[#F1F0F2] pb-4">
                  <h4 class="text-base font-bold text-[#4B465C] m-0">Social Links</h4>
                  <p class="text-xs text-[#A8AAAE] m-0 mt-1">Add communication handles, social media profiles &amp; channels</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <a-form-item label="Facebook Profile">
                    <a-input v-model:value="form.facebook" placeholder="https://facebook.com/username" size="large" class="rounded-lg text-xs">
                      <template #prefix><span class="text-blue-600 font-bold mr-1">f</span></template>
                    </a-input>
                  </a-form-item>

                  <a-form-item label="LinkedIn Profile">
                    <a-input v-model:value="form.linkedin" placeholder="https://linkedin.com/in/username" size="large" class="rounded-lg text-xs">
                      <template #prefix><span class="text-blue-700 font-bold mr-1">in</span></template>
                    </a-input>
                  </a-form-item>

                  <a-form-item label="Skype Username" class="md:col-span-2">
                    <a-input v-model:value="form.skype" placeholder="live:skype_username" size="large" class="rounded-lg text-xs">
                      <template #prefix><span class="text-cyan-500 font-bold mr-1">S</span></template>
                    </a-input>
                  </a-form-item>
                </div>
              </div>

              <!-- ───────────────────────────────────────────────────────────── -->
              <!-- STEP 3: PERMISSIONS & CAPABILITIES -->
              <!-- ───────────────────────────────────────────────────────────── -->
              <div v-if="currentStep === 3" class="animate-fadeIn space-y-5">
                <div class="border-b border-[#F1F0F2] pb-4 flex items-center justify-between">
                  <div>
                    <h4 class="text-base font-bold text-[#4B465C] m-0">Permissions &amp; Capabilities</h4>
                    <p class="text-xs text-[#A8AAAE] m-0 mt-1">Manage global vs own records access across all CRM modules</p>
                  </div>
                  <span class="text-xs font-bold text-[#7367F0] bg-[rgba(115,103,240,0.12)] px-3 py-1 rounded-md">
                    {{ activePermissionsCount }} Capabilities Active
                  </span>
                </div>

                <!-- Admin Banner Notice -->
                <div v-if="isAdminRole" class="p-4 bg-[rgba(115,103,240,0.08)] border border-[#7367F0]/30 rounded-xl flex items-center gap-3 text-[#4B465C]">
                  <div class="w-8 h-8 rounded-lg bg-[#7367F0] text-white flex items-center justify-center shrink-0 font-bold">✓</div>
                  <div>
                    <div class="font-bold text-xs text-[#7367F0]">Administrator Role Active</div>
                    <div class="text-[11px] text-[#A8AAAE]">Administrators automatically have unrestricted global access to all features and records.</div>
                  </div>
                </div>

                <!-- Permission Controls & Search -->
                <div v-else class="space-y-4">
                  <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-[#F8F7FA] p-3 border border-[#EBE9F1] rounded-xl">
                    <a-input
                      v-model:value="permSearch"
                      placeholder="Search features (e.g. Invoices, Estimates)..."
                      style="width: 280px;"
                      allow-clear
                      class="rounded-lg text-xs bg-white"
                    >
                      <template #prefix>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#A8AAAE]"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                      </template>
                    </a-input>

                    <div class="flex items-center gap-2 text-xs">
                      <button type="button" @click="clearAllPermissions" class="btn-outline text-xs py-1.5">
                        Clear All
                      </button>
                      <button type="button" @click="resetToRolePermissions" class="px-3 py-1.5 text-[#7367F0] bg-[rgba(115,103,240,0.12)] hover:bg-[rgba(115,103,240,0.2)] border border-[#7367F0]/30 rounded-md font-semibold transition-colors cursor-pointer">
                        Reset to Role Defaults
                      </button>
                    </div>
                  </div>

                  <!-- Categories Accordion / Cards -->
                  <div 
                    v-for="cat in filteredGroupedFeatures" 
                    :key="cat.name"
                    class="border border-[#EBE9F1] rounded-xl overflow-hidden bg-white shadow-xs"
                  >
                    <div class="px-4 py-3 bg-[#F8F7FA] border-b border-[#EBE9F1] flex items-center justify-between">
                      <div class="flex items-center gap-2">
                        <span class="font-bold text-xs text-[#4B465C] uppercase tracking-wider">{{ cat.name }}</span>
                        <span class="text-[11px] font-semibold text-[#A8AAAE] bg-white px-2 py-0.5 rounded border border-[#EBE9F1]">
                          {{ cat.activeCount }} active
                        </span>
                      </div>
                      <div class="flex items-center gap-2 text-[11px]">
                        <button type="button" @click="toggleCategoryAll(cat, true)" class="text-[#7367F0] hover:underline font-semibold bg-transparent border-0 cursor-pointer p-0">Select Category</button>
                        <span class="text-[#DBDADE]">|</span>
                        <button type="button" @click="toggleCategoryAll(cat, false)" class="text-[#A8AAAE] hover:underline font-semibold bg-transparent border-0 cursor-pointer p-0">Clear</button>
                      </div>
                    </div>

                    <div class="divide-y divide-[#F1F0F2]">
                      <div v-for="feat in cat.features" :key="feat.key" class="p-3.5 hover:bg-[#F8F7FA]/60 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="md:w-1/3 flex items-center justify-between pr-2">
                          <span class="font-bold text-xs text-[#4B465C]">{{ feat.label }}</span>
                          <button type="button" @click="toggleFeatureAll(feat.key, !isFeatureFullyEnabled(feat.key))" class="text-[11px] font-semibold text-[#A8AAAE] hover:text-[#7367F0] transition-colors bg-transparent border-0 cursor-pointer">
                            {{ isFeatureFullyEnabled(feat.key) ? 'Uncheck' : 'Check all' }}
                          </button>
                        </div>
                        <div class="md:w-2/3 flex flex-wrap gap-2">
                          <div
                            v-for="cap in feat.caps"
                            :key="cap.key"
                            class="px-2.5 py-1 rounded-md border text-xs font-medium cursor-pointer transition-all flex items-center gap-1.5 select-none"
                            :class="Boolean(getPerm(feat.key, cap.key)) ? 'bg-[rgba(115,103,240,0.12)] border-[#7367F0] text-[#7367F0] font-semibold' : 'bg-[#F8F7FA] border-[#DBDADE] text-[#6F6B7D] hover:bg-[#F1F0F2]'"
                            @click="setPerm(feat.key, cap.key, !getPerm(feat.key, cap.key))"
                          >
                            <input 
                              type="checkbox" 
                              :checked="Boolean(getPerm(feat.key, cap.key))" 
                              class="vuexy-checkbox" 
                              @click.stop
                            />
                            <span>{{ cap.label }}</span>
                            <a-tooltip v-if="cap.tooltip" :title="cap.tooltip">
                              <span class="text-[#A8AAAE] text-[10px]" @click.stop>ℹ️</span>
                            </a-tooltip>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ========================================================================= -->
              <!-- STEPPER NAVIGATION BUTTONS -->
              <!-- ========================================================================= -->
              <div class="d-flex flex-wrap gap-4 justify-between items-center mt-8 pt-5 border-t border-[#EBE9F1]">
                <button
                  type="button"
                  class="btn-outline inline-flex items-center gap-2"
                  :disabled="currentStep === 0"
                  @click="currentStep--"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                  Previous
                </button>

                <div class="flex items-center gap-3">
                  <button
                    v-if="currentStep < numberedSteps.length - 1"
                    type="button"
                    class="btn-primary inline-flex items-center gap-2"
                    @click="currentStep++"
                  >
                    Next
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                  </button>

                  <a-button
                    type="primary"
                    :loading="saving"
                    html-type="submit"
                    class="bg-[#7367F0] hover:bg-[#685dd8] h-10 px-6 rounded-lg font-bold text-xs shadow-md shadow-[#7367F0]/30"
                  >
                    {{ isCreateMode ? 'Create Staff Member' : 'Save Staff Profile' }}
                  </a-button>
                </div>
              </div>

            </a-form>
          </div>

          <!-- EXTRA TAB: NOTES -->
          <div v-if="activeExtraTab === 'notes'" class="space-y-4 animate-fadeIn">
            <div class="border-b border-[#F1F0F2] pb-4">
              <h4 class="text-base font-bold text-[#4B465C] m-0">Internal Notes</h4>
              <p class="text-xs text-[#A8AAAE] m-0 mt-1">Private records and admin notes regarding this staff member</p>
            </div>

            <div class="bg-[#F8F7FA] border border-[#EBE9F1] rounded-xl p-4">
              <a-textarea v-model:value="newNote" :rows="3" placeholder="Write an internal note about this staff member..." class="rounded-lg mb-3 text-xs" />
              <div class="flex justify-end">
                <a-button type="primary" size="small" :loading="addingNote" @click="addNote" class="bg-[#7367F0] rounded-md text-xs font-bold">
                  Add Note
                </a-button>
              </div>
            </div>

            <div class="space-y-3">
              <div v-for="n in notesList" :key="n.id" class="p-4 bg-white border border-[#EBE9F1] rounded-xl">
                <div class="text-xs text-[#5D596C] font-medium leading-relaxed">{{ n.content }}</div>
                <div class="text-[10px] text-[#A8AAAE] font-semibold mt-2 flex items-center gap-1">
                  🕒 {{ n.created_at }}
                </div>
              </div>
              <div v-if="!notesList.length" class="text-center py-10 text-[#A8AAAE] text-xs font-semibold bg-[#F8F7FA] rounded-xl border border-dashed border-[#DBDADE]">
                No notes recorded for this staff member yet.
              </div>
            </div>
          </div>

          <!-- EXTRA TAB: TIMESHEETS -->
          <div v-if="activeExtraTab === 'timesheets'" class="space-y-4 animate-fadeIn">
            <div class="border-b border-[#F1F0F2] pb-4 flex items-center justify-between">
              <div>
                <h4 class="text-base font-bold text-[#4B465C] m-0">Logged Timesheets</h4>
                <p class="text-xs text-[#A8AAAE] m-0 mt-1">Detailed record of tasks and hours billed</p>
              </div>
              <router-link to="/admin/timesheets" class="text-xs font-bold text-[#7367F0] hover:underline">
                View All Timesheets &rarr;
              </router-link>
            </div>

            <a-table :dataSource="timesheetsList" :columns="timesheetColumns" row-key="id" size="small" :pagination="{ pageSize: 10 }" class="border border-[#EBE9F1] rounded-xl overflow-hidden">
              <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'hourly_rate'">
                  <span class="font-semibold text-[#5D596C]">${{ (Number(record.hourly_rate ?? form.hourly_rate) || 0).toFixed(2) }}</span>
                </template>
                <template v-if="column.key === 'time_h'">
                  <span class="font-mono text-xs font-bold text-[#7367F0] bg-[rgba(115,103,240,0.12)] px-2 py-0.5 rounded">{{ record.time_h || record.time_spent || '00:00' }}</span>
                </template>
              </template>
            </a-table>
          </div>

          <!-- EXTRA TAB: PROJECTS -->
          <div v-if="activeExtraTab === 'projects'" class="space-y-4 animate-fadeIn">
            <div class="border-b border-[#F1F0F2] pb-4 flex items-center justify-between">
              <div>
                <h4 class="text-base font-bold text-[#4B465C] m-0">Assigned Projects</h4>
                <p class="text-xs text-[#A8AAAE] m-0 mt-1">All active and completed client projects</p>
              </div>
              <router-link to="/admin/projects" class="text-xs font-bold text-[#7367F0] hover:underline">
                View All Projects &rarr;
              </router-link>
            </div>

            <a-table :dataSource="projectsList" :columns="projectColumns" row-key="id" size="small" :pagination="{ pageSize: 10 }" class="border border-[#EBE9F1] rounded-xl overflow-hidden">
              <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'name'">
                  <span class="font-semibold text-[#5D596C]">{{ record.name }}</span>
                </template>
                <template v-if="column.key === 'status'">
                  <span class="vuexy-badge-pill vuexy-badge-pill--info">{{ record.status || 'In Progress' }}</span>
                </template>
              </template>
            </a-table>
          </div>

        </div>

      </div>
    </div>

    <!-- Permissions Explained Modal -->
    <a-modal v-model:open="showPermissionsInfo" title="Staff Permissions Explained" :width="760" :footer="null">
      <div class="permissions-guide-content space-y-4 text-xs text-[#5D596C] max-h-[65vh] overflow-y-auto pr-2">
        <div class="p-3.5 bg-[rgba(115,103,240,0.12)] border border-[#7367F0]/30 rounded-xl text-[#7367F0] font-semibold mb-3">
          Permissions control what features and data staff members can view, create, edit, or delete across iBridge CRM.
        </div>

        <div class="perm-guide-item border-b border-[#F1F0F2] pb-3">
          <h4 class="font-bold text-[#4B465C] text-sm mb-1">Invoices</h4>
          <ul class="list-disc pl-4 space-y-1">
            <li><strong>View (Global):</strong> All invoices across the CRM.</li>
            <li><strong>View (Own):</strong> Only invoices created by a staff member.</li>
            <li><strong>Create:</strong> Create new invoices.</li>
            <li><strong>Edit / Delete:</strong> All (if View Global permission) or own invoices only.</li>
          </ul>
        </div>
      </div>
    </a-modal>

  </div>
</template>

<script>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { message } from 'ant-design-vue';
import axios from 'axios';
import { getPermission, setPermission, isUserAdminRole } from '../utils/permissions';
import clayAvatarUrl from '../assets/clay_avatar.png';

export default {
  name: 'StaffDetail',
  setup() {
    const route = useRoute();
    const router = useRouter();

    const staff = ref(null);
    const loading = ref(false);
    const saving = ref(false);
    const currentStep = ref(0);
    const activeExtraTab = ref('profile'); // 'profile' | 'notes' | 'timesheets' | 'projects'
    const roles = ref([]);
    const showPermissionsInfo = ref(false);
    const fileInput = ref(null);
    const profileFormRef = ref(null);
    const headerImageError = ref(false);

    const isPasswordVisible = ref(false);
    const isCPasswordVisible = ref(false);

    const numberedSteps = [
      {
        title: 'Account Details',
        subtitle: 'Setup Account Details',
      },
      {
        title: 'Personal Info',
        subtitle: 'Add personal info',
      },
      {
        title: 'Social Links',
        subtitle: 'Add social links',
      },
      {
        title: 'Permissions',
        subtitle: 'Capabilities & access',
      },
    ];

    const extraTabs = computed(() => [
      { key: 'notes', label: 'Notes', icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>`, count: notesList.value.length },
      { key: 'timesheets', label: 'Timesheets', icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`, count: timesheetsList.value.length },
      { key: 'projects', label: 'Projects', icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>`, count: projectsList.value.length },
    ]);

    const getProfileImageUrl = (imagePath) => {
      if (!imagePath) return '';
      if (imagePath.startsWith('http') || imagePath.startsWith('data:')) {
        return imagePath;
      }
      const basePath = (window.config?.path || '').replace(/\/$/, '');
      if (imagePath.startsWith('/')) return `${basePath}${imagePath}`;
      return `${basePath}/${imagePath}`;
    };

    const form = reactive({
      id: null,
      first_name: '', last_name: '', email: '', password: '', password_confirmation: '',
      role_id: null, hourly_rate: 0, phone: '', facebook: '', linkedin: '', skype: '',
      default_language: '', email_signature: '', direction: '', departments: [],
      profile_image: '', send_welcome_email: false, not_staff: false, permissions: {},
    });

    watch(() => form.profile_image, () => {
      headerImageError.value = false;
    });

    const summaryStats = reactive({
      total_logged: '00:00',
      last_month: '00:00',
      this_month: '00:00',
      last_week: '00:00',
      this_week: '00:00',
    });

    const isCreateMode = computed(() => {
      const p = route.params.id;
      return !p || p === 'create' || p === 'member';
    });

    const newNote = ref('');
    const addingNote = ref(false);
    const notesList = ref([]);

    const timesheetsList = ref([]);
    const projectsList = ref([]);

    const allCapabilities = [
      { key: 'view_own', label: 'View (Own)' },
      { key: 'view_global', label: 'View (Global)' },
      { key: 'create', label: 'Create' },
      { key: 'edit', label: 'Edit' },
      { key: 'delete', label: 'Delete' },
    ];

    const featureList = [
      { key: 'Bulk PDF Export', label: 'Bulk PDF Export', caps: [
        { key: 'view_global', label: 'View(Global)' },
      ]},
      { key: 'Contracts', label: 'Contracts', caps: [
        { key: 'view_own', label: 'View (Own)' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
        { key: 'view_all_templates', label: 'View All Templates' },
      ]},
      { key: 'Credit Notes', label: 'Credit Notes', caps: [
        { key: 'view_own', label: 'View (Own)' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Customers', label: 'Customers', caps: [
        { key: 'view_own', label: 'View (Own)', tooltip: 'Based on customer admin assignment' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Email Templates', label: 'Email Templates', caps: [
        { key: 'view_global', label: 'View(Global)' },
        { key: 'edit', label: 'Edit' },
      ]},
      { key: 'Estimates', label: 'Estimates', caps: [
        { key: 'view_own', label: 'View (Own)' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Expenses', label: 'Expenses', caps: [
        { key: 'view_own', label: 'View (Own)' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Invoices', label: 'Invoices', caps: [
        { key: 'view_own', label: 'View (Own)' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Items', label: 'Items', caps: [
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Knowledge Base', label: 'Knowledge Base', caps: [
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Payments', label: 'Payments', caps: [
        { key: 'view_own', label: 'View (Own)', tooltip: 'Based on invoices View (Own) permissions' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Projects', label: 'Projects', caps: [
        { key: 'view_own', label: 'View (Own)', tooltip: 'Only projects where staff member is added as project member' },
        { key: 'view_global', label: 'View(Global)', tooltip: 'All projects' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
        { key: 'create_timesheets', label: 'Create Timesheets' },
        { key: 'edit_milestones', label: 'Edit Milestones' },
        { key: 'delete_milestones', label: 'Delete Milestones' },
      ]},
      { key: 'Proposals', label: 'Proposals', caps: [
        { key: 'view_own', label: 'View (Own)' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
        { key: 'view_all_templates', label: 'View All Templates' },
      ]},
      { key: 'Reports', label: 'Reports', caps: [
        { key: 'view_global', label: 'View(Global)' },
        { key: 'view_timesheets', label: 'View Timesheets Report' },
      ]},
      { key: 'Staff Roles', label: 'Staff Roles', caps: [
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Settings', label: 'Settings', caps: [
        { key: 'view_global', label: 'View(Global)' },
        { key: 'edit', label: 'Edit' },
      ]},
      { key: 'Staff', label: 'Staff', caps: [
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Subscriptions', label: 'Subscriptions', caps: [
        { key: 'view_own', label: 'View (Own)' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Tasks', label: 'Tasks', caps: [
        { key: 'view_own', label: 'View (Own)', tooltip: 'Only tasks assigned, followed or public' },
        { key: 'view_global', label: 'View(Global)', tooltip: 'All tasks' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
        { key: 'edit_timesheets_global', label: 'Edit Timesheets (Global)' },
        { key: 'edit_own_timesheets', label: 'Edit Own Timesheets' },
        { key: 'delete_timesheets_global', label: 'Delete Timesheets (Global)' },
        { key: 'delete_own_timesheets', label: 'Delete own Timesheets' },
      ]},
      { key: 'Task Checklist Templates', label: 'Task Checklist Templates', caps: [
        { key: 'create', label: 'Create' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Estimate Request', label: 'Estimate Request', caps: [
        { key: 'view_own', label: 'View (Own)' },
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Leads', label: 'Leads', caps: [
        { key: 'view_global', label: 'View(Global)', tooltip: 'If unchecked, staff member only views assigned, created or public leads' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'Surveys', label: 'Surveys', caps: [
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
      { key: 'e-Invoice', label: 'e-Invoice', caps: [
        { key: 'bulk_export', label: 'Bulk export' },
      ]},
      { key: 'Goals', label: 'Goals', caps: [
        { key: 'view_global', label: 'View(Global)' },
        { key: 'create', label: 'Create' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete' },
      ]},
    ];

    const timesheetColumns = [
      { title: 'Task', dataIndex: 'task_name', key: 'task_name' },
      { title: 'Start Time', dataIndex: 'start_time', key: 'start_time' },
      { title: 'End Time', dataIndex: 'end_time', key: 'end_time' },
      { title: 'Related', dataIndex: 'related', key: 'related' },
      { title: 'Hourly Rate', dataIndex: 'hourly_rate', key: 'hourly_rate' },
      { title: 'Time (h)', dataIndex: 'time_h', key: 'time_h' },
    ];

    const projectColumns = [
      { title: 'Project Name', dataIndex: 'name', key: 'name' },
      { title: 'Start Date', dataIndex: 'start_date', key: 'start_date' },
      { title: 'Deadline', dataIndex: 'deadline', key: 'deadline' },
      { title: 'Status', dataIndex: 'status', key: 'status' },
    ];

    const isAdminRole = computed(() => {
      const r = roles.value.find(x => x.id === form.role_id);
      return isUserAdminRole(r, staff.value?.role);
    });

    const initials = (name) => {
      if (!name) return '?';
      return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
    };

    const loadRoles = async () => {
      try {
        const res = await axios.get('/api/roles');
        const roleData = res.data?.data || res.data || [];
        roles.value = Array.isArray(roleData) ? roleData : [];
      } catch (e) {
        console.error('Error loading roles:', e);
      }
    };

    const fetchStaff = async () => {
      if (isCreateMode.value) {
        staff.value = { name: 'Add New Staff Member', email: '', role: 'employee', active: true };
        const empRole = roles.value.find(r => r.slug === 'employee') || roles.value[0];
        Object.assign(form, {
          id: null,
          first_name: '',
          last_name: '',
          email: '',
          password: '',
          password_confirmation: '',
          role_id: empRole ? empRole.id : null,
          hourly_rate: 0,
          phone: '',
          facebook: '',
          linkedin: '',
          skype: '',
          default_language: '',
          email_signature: '',
          direction: '',
          departments: [],
          profile_image: '',
          send_welcome_email: false,
          not_staff: false,
          permissions: empRole?.permissions ? JSON.parse(JSON.stringify(empRole.permissions)) : {},
        });
        return;
      }

      loading.value = true;
      try {
        const { data } = await axios.get(`/api/staff/${route.params.id}`);
        staff.value = data;
        const names = (data.name || '').split(' ');
        const first = names.shift() || '';
        const last = names.join(' ') || '';

        Object.assign(form, {
          id: data.id,
          first_name: first,
          last_name: last,
          email: data.email || '',
          password: '',
          password_confirmation: '',
          role_id: data.role_id || null,
          hourly_rate: data.hourly_rate ?? 0,
          phone: data.phone || '',
          facebook: data.facebook || '',
          linkedin: data.linkedin || '',
          skype: data.skype || '',
          default_language: data.default_language || '',
          email_signature: data.email_signature || '',
          direction: data.direction || '',
          departments: data.department ? data.department.split(', ') : [],
          profile_image: data.profile_image || '',
          send_welcome_email: false,
          not_staff: !data.active,
        });

        if (data.permissions) {
          form.permissions = JSON.parse(JSON.stringify(data.permissions));
        } else if (data.role_id) {
          const r = roles.value.find(x => x.id === data.role_id);
          form.permissions = r?.permissions ? JSON.parse(JSON.stringify(r.permissions)) : {};
        } else {
          form.permissions = {};
        }
      } catch (e) {
        message.error('Failed to load staff details');
      } finally {
        loading.value = false;
      }
    };

    const goToStep = (stepIndex) => {
      activeExtraTab.value = 'profile';
      currentStep.value = stepIndex;
    };

    const getPerm = (feature, cap) => {
      return getPermission(form.permissions, feature, cap);
    };

    const setPerm = (feature, cap, val) => {
      setPermission(form.permissions, feature, cap, val);
    };

    const permSearch = ref('');

    const groupedCategories = [
      {
        name: 'Sales & Financials',
        featureKeys: ['Invoices', 'Estimates', 'Proposals', 'Payments', 'Credit Notes', 'Contracts', 'Expenses', 'Items', 'Subscriptions', 'Estimate Request']
      },
      {
        name: 'Customer & Lead Management',
        featureKeys: ['Customers', 'Leads', 'Surveys']
      },
      {
        name: 'Projects & Operations',
        featureKeys: ['Projects', 'Tasks', 'Task Checklist Templates']
      },
      {
        name: 'System & Administration',
        featureKeys: ['Reports', 'Staff', 'Staff Roles', 'Settings', 'Knowledge Base', 'Email Templates', 'Bulk PDF Export', 'e-Invoice', 'Goals']
      }
    ];

    const activePermissionsCount = computed(() => {
      if (!form.permissions || typeof form.permissions !== 'object') return 0;
      let count = 0;
      Object.values(form.permissions).forEach(actions => {
        if (typeof actions === 'object' && actions !== null) {
          Object.values(actions).forEach(v => {
            if (v === true || v === 1 || v === '1' || v === 'true') count++;
          });
        }
      });
      return count;
    });

    const groupedFeatures = computed(() => {
      return groupedCategories.map(cat => {
        const catFeatures = featureList.filter(f => cat.featureKeys.includes(f.key));
        let activeCount = 0;
        catFeatures.forEach(f => {
          f.caps.forEach(c => {
            if (getPerm(f.key, c.key)) activeCount++;
          });
        });
        return {
          name: cat.name,
          features: catFeatures,
          activeCount,
        };
      });
    });

    const filteredGroupedFeatures = computed(() => {
      const q = permSearch.value.trim().toLowerCase();
      if (!q) return groupedFeatures.value;

      return groupedFeatures.value.map(cat => {
        const matchingFeatures = cat.features.filter(f => {
          if (f.label.toLowerCase().includes(q)) return true;
          return f.caps.some(c => c.label.toLowerCase().includes(q));
        });
        return {
          ...cat,
          features: matchingFeatures,
        };
      }).filter(cat => cat.features.length > 0);
    });

    const isFeatureFullyEnabled = (featureKey) => {
      const feat = featureList.find(f => f.key === featureKey);
      if (!feat) return false;
      return feat.caps.every(c => getPerm(featureKey, c.key));
    };

    const toggleFeatureAll = (featureKey, enable) => {
      const feat = featureList.find(f => f.key === featureKey);
      if (!feat) return;
      feat.caps.forEach(c => {
        setPerm(featureKey, c.key, enable);
      });
    };

    const toggleCategoryAll = (cat, enable) => {
      cat.features.forEach(f => {
        toggleFeatureAll(f.key, enable);
      });
    };

    const clearAllPermissions = () => {
      form.permissions = {};
    };

    const resetToRolePermissions = () => {
      const r = roles.value.find(x => x.id === form.role_id);
      if (r?.permissions) {
        form.permissions = JSON.parse(JSON.stringify(r.permissions));
      } else {
        form.permissions = {};
      }
      message.info('Reset permissions to role defaults');
    };

    const toggleAdmin = (e) => {
      if (e.target.checked) {
        const adminRole = roles.value.find(r => r.slug === 'admin' || r.name?.toLowerCase() === 'admin');
        if (adminRole) {
          form.role_id = adminRole.id;
          form.permissions = adminRole.permissions ? JSON.parse(JSON.stringify(adminRole.permissions)) : {};
        }
      } else {
        const empRole = roles.value.find(r => r.slug === 'employee');
        form.role_id = empRole ? empRole.id : null;
        form.permissions = empRole?.permissions ? JSON.parse(JSON.stringify(empRole.permissions)) : {};
      }
    };

    const onRoleChange = () => {
      const r = roles.value.find(x => x.id === form.role_id);
      if (r?.permissions) {
        const rolePerms = JSON.parse(JSON.stringify(r.permissions));
        form.permissions = { ...rolePerms, ...form.permissions };
      }
    };

    const saveProfile = async () => {
      const name = (form.first_name + ' ' + form.last_name).trim();
      if (!form.first_name.trim() && !form.last_name.trim()) {
        message.error('First Name is required');
        return;
      }
      if (!form.email) {
        message.error('Email is required');
        return;
      }
      if (isCreateMode.value) {
        if (!form.password) {
          message.error('Password is required when creating a staff member');
          return;
        }
        if (form.password !== form.password_confirmation) {
          message.error('Password confirmation does not match password');
          return;
        }
      }

      saving.value = true;
      try {
        const payload = {
          name: name,
          first_name: form.first_name,
          last_name: form.last_name,
          email: form.email,
          password: form.password || undefined,
          password_confirmation: form.password_confirmation || undefined,
          role_id: form.role_id,
          hourly_rate: form.hourly_rate,
          phone: form.phone,
          facebook: form.facebook,
          linkedin: form.linkedin,
          skype: form.skype,
          default_language: form.default_language,
          email_signature: form.email_signature,
          direction: form.direction,
          department: form.departments.join(', '),
          active: !form.not_staff,
          permissions: form.permissions,
        };

        if (isCreateMode.value) {
          await axios.post('/api/staff', payload);
          message.success('Staff member created successfully');
          router.push(backLink.value);
        } else {
          await axios.put(`/api/staff/${route.params.id}`, payload);
          form.password = '';
          form.password_confirmation = '';
          message.success('Staff details updated successfully');
          await fetchStaff();
        }
      } catch (e) {
        if (e.response?.data?.errors) {
          const errList = Object.values(e.response.data.errors).flat();
          message.error(errList.join(' | '));
        } else {
          const msg = e.response?.data?.message || (isCreateMode.value ? 'Failed to create staff member' : 'Failed to update staff member');
          message.error(msg);
        }
      } finally {
        saving.value = false;
      }
    };

    const addNote = () => {
      if (!newNote.value.trim()) return;
      addingNote.value = true;
      notesList.value.unshift({
        id: Date.now(),
        content: newNote.value.trim(),
        created_at: new Date().toLocaleString(),
      });
      newNote.value = '';
      addingNote.value = false;
      message.success('Note added');
    };

    const removePhoto = () => {
      form.profile_image = '';
      headerImageError.value = false;
      if (fileInput.value) fileInput.value.value = '';
    };

    const triggerUpload = () => {
      fileInput.value?.click();
    };

    const onFileChange = (e) => {
      const file = e.target.files?.[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (ev) => {
        form.profile_image = ev.target.result;
      };
      reader.readAsDataURL(file);
    };

    onMounted(async () => {
      await loadRoles();
      await fetchStaff();
    });

    const backLink = computed(() => route.path.startsWith('/admin/setup') ? '/admin/setup/staff' : '/admin/staff');

    return {
      staff, loading, saving, currentStep, numberedSteps, activeExtraTab, extraTabs,
      form, roles, showPermissionsInfo, summaryStats, isCreateMode,
      isPasswordVisible, isCPasswordVisible,
      allCapabilities, featureList, timesheetColumns, projectColumns,
      timesheetsList, projectsList, notesList, newNote, addingNote,
      isAdminRole, fileInput, backLink,
      initials, clayAvatarUrl, headerImageError, getProfileImageUrl,
      goToStep, getPerm, setPerm, onRoleChange, toggleAdmin, saveProfile, addNote,
      triggerUpload, onFileChange, removePhoto,
      permSearch, groupedCategories, activePermissionsCount, groupedFeatures, filteredGroupedFeatures,
      isFeatureFullyEnabled, toggleFeatureAll, toggleCategoryAll, clearAllPermissions, resetToRolePermissions,
    };
  },
};
</script>

<style scoped>
.staff-detail-page {
  font-family: 'Public Sans', 'Inter', -apple-system, sans-serif;
  color: #5D596C;
}

/* ==========================================================================
   VUEXY FORM CONTROLS & INPUT BOXES OVERRIDES
   ========================================================================== */

/* Form Item & Labels */
:deep(.ant-form-item) {
  margin-bottom: 18px !important;
}

:deep(.ant-form-item-label > label) {
  font-size: 12.5px !important;
  font-weight: 500 !important;
  color: #4B465C !important;
  height: auto !important;
  margin-bottom: 4px !important;
}

:deep(.ant-form-item-required::before) {
  color: #EA5455 !important;
}

/* Base Text Inputs, Passwords, Affixes, Selectors, Pickers */
:deep(.ant-input),
:deep(.ant-input-affix-wrapper),
:deep(.ant-input-password),
:deep(.ant-input-number),
:deep(.ant-select:not(.ant-select-customize-input) .ant-select-selector),
:deep(.ant-picker) {
  background-color: #FFFFFF !important;
  border: 1px solid #DBDADE !important;
  border-radius: 6px !important;
  color: #4B465C !important;
  font-size: 13.5px !important;
  font-family: inherit !important;
  box-shadow: none !important;
  transition: all 0.2s ease !important;
}

:deep(.ant-input) {
  min-height: 38px !important;
  padding: 7px 12px !important;
}

:deep(.ant-input-lg) {
  min-height: 40px !important;
  padding: 8px 14px !important;
  font-size: 13.5px !important;
}

/* Affix Wrappers (e.g. search, password) */
:deep(.ant-input-affix-wrapper) {
  min-height: 40px !important;
  padding: 0 12px !important;
  display: flex !important;
  align-items: center !important;
}

:deep(.ant-input-affix-wrapper input.ant-input) {
  border: none !important;
  box-shadow: none !important;
  padding: 0 !important;
  min-height: 38px !important;
  background: transparent !important;
}

:deep(.ant-input-affix-wrapper:hover),
:deep(.ant-input:hover),
:deep(.ant-input-number:hover),
:deep(.ant-select:not(.ant-select-disabled):hover .ant-select-selector) {
  border-color: #7367F0 !important;
}

/* Focus States */
:deep(.ant-input:focus),
:deep(.ant-input-focused),
:deep(.ant-input-affix-wrapper:focus),
:deep(.ant-input-affix-wrapper-focused),
:deep(.ant-input-number:focus),
:deep(.ant-input-number-focused),
:deep(.ant-select-focused:not(.ant-select-disabled).ant-select:not(.ant-select-customize-input) .ant-select-selector),
:deep(.ant-picker-focused) {
  border-color: #7367F0 !important;
  box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.16) !important;
  outline: 0 !important;
}

/* Placeholders */
:deep(.ant-input::placeholder),
:deep(.ant-select-selection-placeholder),
:deep(.ant-input-password input::placeholder) {
  color: #A8AAAE !important;
  font-size: 13px !important;
}

/* Select Dropdown Selector */
:deep(.ant-select-single:not(.ant-select-customize-input) .ant-select-selector) {
  height: 40px !important;
  padding: 0 12px !important;
  display: flex !important;
  align-items: center !important;
}

:deep(.ant-select-single .ant-select-selector .ant-select-selection-item),
:deep(.ant-select-single .ant-select-selector .ant-select-selection-placeholder) {
  line-height: 38px !important;
  color: #4B465C !important;
}

/* Input Number */
:deep(.ant-input-number) {
  width: 100% !important;
  min-height: 40px !important;
}

:deep(.ant-input-number-input) {
  height: 38px !important;
  line-height: 38px !important;
  padding: 0 12px !important;
  color: #4B465C !important;
}

:deep(.ant-input-number-group-addon) {
  background: #F8F7FA !important;
  border: 1px solid #DBDADE !important;
  border-right: none !important;
  border-radius: 6px 0 0 6px !important;
  color: #6F6B7D !important;
  font-weight: 600 !important;
  padding: 0 12px !important;
}

/* Textarea */
:deep(textarea.ant-input) {
  min-height: 80px !important;
  padding: 10px 12px !important;
  line-height: 1.5 !important;
}

/* Checkbox */
:deep(.ant-checkbox-inner) {
  border-radius: 4px !important;
  border: 1.5px solid #DBDADE !important;
  width: 18px !important;
  height: 18px !important;
}

:deep(.ant-checkbox-checked .ant-checkbox-inner) {
  background-color: #7367F0 !important;
  border-color: #7367F0 !important;
}

/* Buttons */
.btn-primary {
  background: #7367F0 !important;
  color: #FFFFFF !important;
  border: none !important;
  border-radius: 6px !important;
  padding: 8px 18px !important;
  font-size: 13px !important;
  font-weight: 600 !important;
  cursor: pointer !important;
  box-shadow: 0 2px 6px 0 rgba(115, 103, 240, 0.4) !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 6px !important;
  transition: all 0.2s ease !important;
}
.btn-primary:hover {
  background: #685DD8 !important;
  box-shadow: 0 4px 12px 0 rgba(115, 103, 240, 0.5) !important;
}

.btn-outline {
  background: #FFFFFF !important;
  color: #6F6B7D !important;
  border: 1px solid #DBDADE !important;
  border-radius: 6px !important;
  padding: 8px 16px !important;
  font-size: 13px !important;
  font-weight: 600 !important;
  cursor: pointer !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 6px !important;
  box-shadow: none !important;
  transition: all 0.2s ease !important;
}
.btn-outline:hover:not(:disabled) {
  background: #F8F7FA !important;
  border-color: #7367F0 !important;
  color: #7367F0 !important;
}
.btn-outline:disabled {
  opacity: 0.5 !important;
  cursor: not-allowed !important;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeIn {
  animation: fadeIn 0.2s ease-out;
}
</style>
