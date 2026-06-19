<template>
  <div class="premium-profile-edit">
    <!-- Page Header -->
    <div class="pe-header">
      <div class="pe-header-left">
        <h2 class="pe-title">Edit Profile</h2>
        <p class="pe-subtitle">Manage your personal information and preferences</p>
      </div>
      <div class="pe-header-right">
        <router-link to="/admin/profile" class="pe-btn pe-btn-ghost">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          Back
        </router-link>
      </div>
    </div>

    <a-form layout="vertical" :model="form" ref="formRef" @finish="handleSubmit" class="pe-form">
      <!-- ===== Profile Picture Section ===== -->
      <div class="pe-card">
        <div class="pe-card-body pe-avatar-section">
          <div class="pe-avatar-wrapper">
            <div class="pe-avatar-container">
              <img
                v-if="getProfileImageUrl(form.profile_image)"
                :src="getProfileImageUrl(form.profile_image)"
                class="pe-avatar-img"
              />
              <div v-else class="pe-avatar-fallback" :style="{ background: avatarColor(form.name) }">
                {{ initials(form.name) }}
              </div>
              <button type="button" class="pe-avatar-edit-btn" @click="triggerUpload" :title="uploading ? 'Uploading...' : 'Change photo'">
                <svg v-if="!uploading" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/></svg>
                <a-spin v-else :spinning="true" />
              </button>
            </div>
            <input type="file" ref="fileInput" accept="image/*" style="display:none" @change="onFileChange" />
            <div class="pe-avatar-info">
              <span class="pe-avatar-label">{{ user?.name }}</span>
              <span class="pe-avatar-hint">JPEG, PNG, JPG or GIF. Max 2MB.</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== Personal Information ===== -->
      <div class="pe-card">
        <div class="pe-card-header">
          <div class="pe-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
          <div>
            <h3 class="pe-card-title">Personal Information</h3>
            <p class="pe-card-desc">Update your name, email, and contact details</p>
          </div>
        </div>
        <div class="pe-card-body">
          <div class="pe-grid-2">
            <a-form-item label="Full Name" name="name" :rules="[{ required: true, message: 'Full name is required' }]">
              <a-input v-model:value="form.name" placeholder="Enter your full name" size="large">
                <template #prefix><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></template>
              </a-input>
            </a-form-item>
            <a-form-item label="Email Address" name="email" :rules="[{ required: true, type: 'email', message: 'Valid email is required' }]">
              <a-input v-model:value="form.email" placeholder="Enter your email" size="large">
                <template #prefix><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></template>
              </a-input>
            </a-form-item>
          </div>
          <div class="pe-grid-2">
            <a-form-item label="Phone Number" name="phone">
              <a-input v-model:value="form.phone" placeholder="Enter your phone number" size="large">
                <template #prefix><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg></template>
              </a-input>
            </a-form-item>
            <a-form-item label="Department" name="department">
              <a-input v-model:value="form.department" placeholder="Department" disabled size="large">
                <template #prefix><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/></svg></template>
              </a-input>
            </a-form-item>
          </div>
        </div>
      </div>

      <!-- ===== Contact & Social ===== -->
      <div class="pe-card">
        <div class="pe-card-header">
          <div class="pe-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg></div>
          <div>
            <h3 class="pe-card-title">Contact & Social</h3>
            <p class="pe-card-desc">Link your social profiles for better collaboration</p>
          </div>
        </div>
        <div class="pe-card-body">
          <div class="pe-grid-2">
            <a-form-item label="Facebook" name="facebook">
              <a-input v-model:value="form.facebook" placeholder="https://facebook.com/yourprofile" size="large">
                <template #prefix><svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg></template>
              </a-input>
            </a-form-item>
            <a-form-item label="LinkedIn" name="linkedin">
              <a-input v-model:value="form.linkedin" placeholder="https://linkedin.com/in/yourprofile" size="large">
                <template #prefix><svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2zM4 6a2 2 0 100-4 2 2 0 000 4z"/></svg></template>
              </a-input>
            </a-form-item>
          </div>
          <div class="pe-grid-2">
            <a-form-item label="Skype" name="skype">
              <a-input v-model:value="form.skype" placeholder="live:your.skype.id" size="large">
                <template #prefix><svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M12 2a9.88 9.88 0 00-3.82.76A5.52 5.52 0 002.76 8.18 9.88 9.88 0 002 12a9.88 9.88 0 00.76 3.82 5.52 5.52 0 005.42 5.42A9.88 9.88 0 0012 22a9.88 9.88 0 003.82-.76 5.52 5.52 0 005.42-5.42A9.88 9.88 0 0022 12a9.88 9.88 0 00-.76-3.82 5.52 5.52 0 00-5.42-5.42A9.88 9.88 0 0012 2z"/></svg></template>
              </a-input>
            </a-form-item>
            <div></div>
          </div>
        </div>
      </div>

      <!-- ===== Preferences ===== -->
      <div class="pe-card">
        <div class="pe-card-header">
          <div class="pe-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg></div>
          <div>
            <h3 class="pe-card-title">Preferences</h3>
            <p class="pe-card-desc">Customize your language and interface settings</p>
          </div>
        </div>
        <div class="pe-card-body">
          <div class="pe-grid-2">
            <a-form-item label="Default Language" name="default_language">
              <a-select v-model:value="form.default_language" placeholder="Select language" size="large">
                <a-select-option value="">System Default</a-select-option>
                <a-select-option value="en">English</a-select-option>
                <a-select-option value="es">Spanish</a-select-option>
                <a-select-option value="fr">French</a-select-option>
                <a-select-option value="de">German</a-select-option>
              </a-select>
            </a-form-item>
            <a-form-item label="Direction" name="direction">
              <a-select v-model:value="form.direction" placeholder="Select direction" size="large">
                <a-select-option value="">LTR (Left to Right)</a-select-option>
                <a-select-option value="rtl">RTL (Right to Left)</a-select-option>
              </a-select>
            </a-form-item>
          </div>
        </div>
      </div>

      <!-- ===== Change Password ===== -->
      <div class="pe-card">
        <div class="pe-card-header">
          <div class="pe-card-icon pe-card-icon--warning"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></div>
          <div>
            <h3 class="pe-card-title">Change Password</h3>
            <p class="pe-card-desc">Leave blank to keep your current password</p>
          </div>
        </div>
        <div class="pe-card-body">
          <div class="pe-grid-2">
            <a-form-item label="New Password" name="password">
              <a-input-password v-model:value="form.password" placeholder="Enter new password" size="large">
                <template #prefix><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></template>
              </a-input-password>
            </a-form-item>
            <a-form-item label="Confirm New Password" name="password_confirmation">
              <a-input-password v-model:value="form.password_confirmation" placeholder="Confirm new password" size="large">
                <template #prefix><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></template>
              </a-input-password>
            </a-form-item>
          </div>
        </div>
      </div>

      <!-- ===== Submit ===== -->
      <div class="pe-actions">
        <router-link to="/admin/profile" class="pe-btn pe-btn-ghost pe-btn-lg">Cancel</router-link>
        <a-button type="primary" html-type="submit" :loading="saving" size="large" class="pe-btn-save">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Save Changes
        </a-button>
      </div>
    </a-form>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../store/authStore';
import { message } from 'ant-design-vue';
import axios from 'axios';

export default defineComponent({
  name: 'ProfileEdit',
  setup() {
    const authStore = useAuthStore();
    const router = useRouter();
    const formRef = ref(null);
    const fileInput = ref(null);
    const saving = ref(false);
    const uploading = ref(false);

    const user = computed(() => authStore.user || {});

    const form = reactive({
      name: '',
      email: '',
      phone: '',
      department: '',
      facebook: '',
      linkedin: '',
      skype: '',
      default_language: '',
      direction: '',
      password: '',
      password_confirmation: '',
      profile_image: '',
    });

    const initForm = () => {
      if (user.value) {
        Object.assign(form, {
          name: user.value.name || '',
          email: user.value.email || '',
          phone: user.value.phone || '',
          department: user.value.department || '',
          facebook: user.value.facebook || '',
          linkedin: user.value.linkedin || '',
          skype: user.value.skype || '',
          default_language: user.value.default_language || '',
          direction: user.value.direction || '',
          password: '',
          password_confirmation: '',
          profile_image: user.value.profile_image || '',
        });
      }
    };

    onMounted(() => {
      initForm();
    });

    const initials = (name) => {
      if (!name) return '?';
      return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
    };

    const avatarColor = (name) => {
      const colors = ['#6366f1', '#ec4899', '#f59e0b', '#10b981', '#8b5cf6', '#ef4444', '#06b6d4', '#f97316', '#14b8a6', '#84cc16'];
      let hash = 0;
      for (let i = 0; i < (name || '').length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
      return colors[Math.abs(hash) % colors.length];
    };

    const getProfileImageUrl = (imagePath) => {
      if (!imagePath) return '';
      const basePath = window.config?.path?.replace(/\/$/, '') || '';
      if (imagePath.startsWith('http') || imagePath.startsWith('/')) return imagePath;
      return `${basePath}/${imagePath}`;
    };

    const triggerUpload = () => {
      fileInput.value?.click();
    };

    const onFileChange = async (e) => {
      const file = e.target.files?.[0];
      if (!file) return;

      if (file.size > 2 * 1024 * 1024) {
        message.error('File size exceeds 2MB limit.');
        return;
      }

      uploading.value = true;
      const formData = new FormData();
      formData.append('profile_image', file);

      try {
        const response = await axios.post(`/api/staff/${user.value.id}/image`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        form.profile_image = response.data.profile_image;
        await authStore.updateUserAction();
        message.success({ content: 'Profile picture updated successfully.', duration: 3 });
      } catch (err) {
        message.error('Failed to upload profile picture.');
      } finally {
        uploading.value = false;
      }
    };

    const handleSubmit = async () => {
      if (form.password && form.password !== form.password_confirmation) {
        message.error('Passwords do not match.');
        return;
      }

      saving.value = true;
      const payload = {
        name: form.name,
        email: form.email,
        phone: form.phone,
        facebook: form.facebook,
        linkedin: form.linkedin,
        skype: form.skype,
        default_language: form.default_language,
        direction: form.direction,
      };

      if (form.password) {
        payload.password = form.password;
        payload.password_confirmation = form.password_confirmation;
      }

      try {
        await axios.put(`/api/staff/${user.value.id}`, payload);
        await authStore.updateUserAction();
        message.success({ content: 'Profile updated successfully.', duration: 3 });
        router.push('/admin/profile');
      } catch (err) {
        const errorMsg = err.response?.data?.message || 'Failed to update profile.';
        message.error(errorMsg);
      } finally {
        saving.value = false;
      }
    };

    return {
      form, formRef, fileInput, saving, uploading, user,
      initials, avatarColor, getProfileImageUrl,
      triggerUpload, onFileChange, handleSubmit,
    };
  },
});
</script>

<style scoped>
/* ── Container ───────────────────────────────────── */
.premium-profile-edit {
  max-width: 820px;
  margin: 0 auto;
}

/* ── Header ──────────────────────────────────────── */
.pe-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 28px;
  gap: 16px;
}
.pe-header-left {}
.pe-title {
  font-size: 22px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  letter-spacing: -0.3px;
  line-height: 1.3;
}
.pe-subtitle {
  font-size: 14px;
  color: #64748b;
  margin: 4px 0 0;
}
.pe-header-right {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

/* ── Buttons ─────────────────────────────────────── */
.pe-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 600;
  border-radius: 10px;
  border: none;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.2s;
  font-family: inherit;
  line-height: 1;
}
.pe-btn svg { width: 16px; height: 16px; }
.pe-btn-ghost {
  background: transparent;
  color: #64748b;
  border: 1px solid #e2e8f0;
}
.pe-btn-ghost:hover {
  background: #f8fafc;
  color: #0f172a;
  border-color: #cbd5e1;
}
.pe-btn-lg {
  padding: 10px 24px;
  font-size: 14px;
}
.pe-btn-save {
  display: inline-flex !important;
  align-items: center;
  gap: 8px;
  padding: 10px 28px !important;
  font-size: 14px !important;
  font-weight: 600 !important;
  border-radius: 10px !important;
  height: auto !important;
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.25) !important;
  background: #6366f1 !important;
  border-color: #6366f1 !important;
}
.pe-btn-save:hover {
  background: #4f46e5 !important;
  border-color: #4f46e5 !important;
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35) !important;
}

/* ── Card ────────────────────────────────────────── */
.pe-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  margin-bottom: 20px;
  overflow: hidden;
  transition: box-shadow 0.2s;
}
.pe-card:hover {
  box-shadow: 0 4px 16px rgba(0,0,0,0.04);
}
.pe-card-header {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 20px 24px 0;
}
.pe-card-icon {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: #eef2ff;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: #6366f1;
}
.pe-card-icon svg { width: 20px; height: 20px; }
.pe-card-icon--warning {
  background: #fffbeb;
  color: #f59e0b;
}
.pe-card-title {
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  line-height: 1.3;
}
.pe-card-desc {
  font-size: 13px;
  color: #94a3b8;
  margin: 2px 0 0;
}
.pe-card-body {
  padding: 16px 24px 24px;
}

/* ── Avatar Section ──────────────────────────────── */
.pe-avatar-section {
  padding-top: 20px !important;
}
.pe-avatar-wrapper {
  display: flex;
  align-items: center;
  gap: 20px;
}
.pe-avatar-container {
  position: relative;
  width: 80px;
  height: 80px;
  flex-shrink: 0;
}
.pe-avatar-img {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #fff;
  box-shadow: 0 4px 14px rgba(0,0,0,0.1);
}
.pe-avatar-fallback {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 26px;
  font-weight: 700;
  color: #fff;
  border: 3px solid #fff;
  box-shadow: 0 4px 14px rgba(0,0,0,0.1);
}
.pe-avatar-edit-btn {
  position: absolute;
  bottom: 0;
  right: -2px;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #6366f1;
  border: 2px solid #fff;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 2px 8px rgba(99,102,241,0.3);
  padding: 0;
}
.pe-avatar-edit-btn:hover {
  background: #4f46e5;
  transform: scale(1.08);
}
.pe-avatar-edit-btn svg { width: 14px; height: 14px; }
.pe-avatar-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.pe-avatar-label {
  font-size: 15px;
  font-weight: 600;
  color: #0f172a;
}
.pe-avatar-hint {
  font-size: 12px;
  color: #94a3b8;
}

/* ── Form Grid ───────────────────────────────────── */
.pe-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0 20px;
}
@media (max-width: 640px) {
  .pe-grid-2 { grid-template-columns: 1fr; }
}
.pe-form :deep(.ant-form-item) {
  margin-bottom: 20px;
}
.pe-form :deep(.ant-form-item-label) {
  padding-bottom: 4px;
}
.pe-form :deep(.ant-form-item-label label) {
  font-size: 13px;
  font-weight: 600;
  color: #334155;
  height: auto;
}
.pe-form :deep(.ant-input),
.pe-form :deep(.ant-select-selector),
.pe-form :deep(.ant-input-password) {
  border-radius: 10px !important;
  border-color: #e2e8f0;
  transition: all 0.2s;
  background: #f8fafc;
}
.pe-form :deep(.ant-input):hover,
.pe-form :deep(.ant-select-selector):hover,
.pe-form :deep(.ant-input-password):hover {
  border-color: #6366f1;
  background: #fff;
}
.pe-form :deep(.ant-input):focus,
.pe-form :deep(.ant-select-selector):focus,
.pe-form :deep(.ant-input-password):focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
  background: #fff;
}
.pe-form :deep(.ant-input-affix-wrapper) {
  border-radius: 10px !important;
  border-color: #e2e8f0;
  background: #f8fafc;
  padding: 4px 12px;
}
.pe-form :deep(.ant-input-affix-wrapper:hover) {
  border-color: #6366f1;
  background: #fff;
}
.pe-form :deep(.ant-input-affix-wrapper-focused) {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
  background: #fff;
}
.pe-form :deep(.ant-input-prefix) {
  margin-right: 8px;
  color: #94a3b8;
}
.pe-form :deep(.ant-input-lg),
.pe-form :deep(.ant-input-affix-wrapper-lg) {
  padding: 8px 14px;
  font-size: 14px;
}
.pe-form :deep(.ant-select-lg .ant-select-selector) {
  padding: 0 14px;
  height: 44px !important;
}
.pe-form :deep(.ant-select-lg .ant-select-selection-item) {
  line-height: 42px !important;
}
.pe-form :deep(.ant-input-disabled) {
  background: #f1f5f9 !important;
  color: #94a3b8 !important;
  cursor: not-allowed;
}

/* ── Actions ─────────────────────────────────────── */
.pe-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 8px;
}
</style>