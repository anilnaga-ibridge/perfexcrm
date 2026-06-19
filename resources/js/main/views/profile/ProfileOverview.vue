<template>
  <div class="premium-profile-overview">
    <!-- Page Header -->
    <div class="po-header">
      <div class="po-header-left">
        <h2 class="po-title">Profile</h2>
        <p class="po-subtitle">Your personal information and account details</p>
      </div>
      <div class="po-header-right">
        <router-link to="/admin/profile/edit" class="po-btn po-btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit Profile
        </router-link>
      </div>
    </div>

    <!-- ===== Personal Information ===== -->
    <div class="po-card">
      <div class="po-card-header">
        <div class="po-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
        <div>
          <h3 class="po-card-title">Personal Information</h3>
          <p class="po-card-desc">Your basic details and contact information</p>
        </div>
      </div>
      <div class="po-card-body">
        <div class="po-grid-4">
          <div class="po-field">
            <span class="po-field-label">Full Name</span>
            <span class="po-field-value">{{ user?.name || '-' }}</span>
          </div>
          <div class="po-field">
            <span class="po-field-label">Email Address</span>
            <span class="po-field-value">{{ user?.email || '-' }}</span>
          </div>
          <div class="po-field">
            <span class="po-field-label">Phone</span>
            <span class="po-field-value">{{ user?.phone || 'Not set' }}</span>
          </div>
          <div class="po-field">
            <span class="po-field-label">Department</span>
            <span class="po-field-value">{{ user?.department || 'Not set' }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== Contact & Social ===== -->
    <div class="po-card">
      <div class="po-card-header">
        <div class="po-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg></div>
        <div>
          <h3 class="po-card-title">Contact & Social</h3>
          <p class="po-card-desc">Your linked social profiles</p>
        </div>
      </div>
      <div class="po-card-body">
        <div class="po-grid-3">
          <div class="po-field">
            <span class="po-field-label">Facebook</span>
            <span class="po-field-value">{{ user?.facebook || 'Not set' }}</span>
          </div>
          <div class="po-field">
            <span class="po-field-label">LinkedIn</span>
            <span class="po-field-value">{{ user?.linkedin || 'Not set' }}</span>
          </div>
          <div class="po-field">
            <span class="po-field-label">Skype</span>
            <span class="po-field-value">{{ user?.skype || 'Not set' }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== Account ===== -->
    <div class="po-card">
      <div class="po-card-header">
        <div class="po-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></div>
        <div>
          <h3 class="po-card-title">Account</h3>
          <p class="po-card-desc">Security and account activity</p>
        </div>
      </div>
      <div class="po-card-body">
        <div class="po-grid-4">
          <div class="po-field">
            <span class="po-field-label">Role</span>
            <span class="po-field-value">
              <span class="po-badge">{{ user?.role || '-' }}</span>
            </span>
          </div>
          <div class="po-field">
            <span class="po-field-label">Status</span>
            <span class="po-field-value">
              <span :class="['po-status', user?.active ? 'po-status--active' : 'po-status--inactive']">
                <span class="po-status-dot"></span>
                {{ user?.active ? 'Active' : 'Inactive' }}
              </span>
            </span>
          </div>
          <div class="po-field">
            <span class="po-field-label">Last Login</span>
            <span class="po-field-value">{{ formatDate(user?.last_login) }}</span>
          </div>
          <div class="po-field">
            <span class="po-field-label">Default Language</span>
            <span class="po-field-value">{{ user?.default_language || 'English' }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, computed } from 'vue';
import { useAuthStore } from '../../store/authStore';

export default defineComponent({
  name: 'ProfileOverview',
  setup() {
    const authStore = useAuthStore();
    const user = computed(() => authStore.user || {});

    const formatDate = (dateString) => {
      if (!dateString) return 'Never';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    return { user, formatDate };
  },
});
</script>

<style scoped>
/* ── Container ──────────────────────────────────── */
.premium-profile-overview {
  max-width: 820px;
  margin: 0 auto;
}

/* ── Header ─────────────────────────────────────── */
.po-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 28px;
  gap: 16px;
}
.po-title {
  font-size: 22px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  letter-spacing: -0.3px;
  line-height: 1.3;
}
.po-subtitle {
  font-size: 14px;
  color: #64748b;
  margin: 4px 0 0;
}
.po-header-right {
  flex-shrink: 0;
}

/* ── Button ─────────────────────────────────────── */
.po-btn {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 10px 22px;
  font-size: 14px;
  font-weight: 600;
  border-radius: 10px;
  text-decoration: none;
  transition: all 0.2s;
  font-family: inherit;
  line-height: 1;
}
.po-btn svg { width: 16px; height: 16px; }
.po-btn-primary {
  background: #6366f1;
  color: #fff;
  box-shadow: 0 2px 8px rgba(99,102,241,0.25);
}
.po-btn-primary:hover {
  background: #4f46e5;
  color: #fff;
  box-shadow: 0 4px 14px rgba(99,102,241,0.35);
}

/* ── Card ───────────────────────────────────────── */
.po-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  margin-bottom: 20px;
  overflow: hidden;
  transition: box-shadow 0.2s;
}
.po-card:hover {
  box-shadow: 0 4px 16px rgba(0,0,0,0.04);
}
.po-card-header {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 20px 24px 0;
}
.po-card-icon {
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
.po-card-icon svg { width: 20px; height: 20px; }
.po-card-title {
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  line-height: 1.3;
}
.po-card-desc {
  font-size: 13px;
  color: #94a3b8;
  margin: 2px 0 0;
}
.po-card-body {
  padding: 16px 24px 24px;
}

/* ── Fields Grid ────────────────────────────────── */
.po-grid-4 {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}
.po-grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}
@media (max-width: 768px) {
  .po-grid-4 { grid-template-columns: repeat(2, 1fr); }
  .po-grid-3 { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
  .po-grid-4, .po-grid-3 { grid-template-columns: 1fr; }
}

.po-field {
  background: #f8fafc;
  border: 1px solid #f1f5f9;
  border-radius: 10px;
  padding: 14px 16px;
  transition: border-color 0.2s, background 0.2s;
}
.po-field:hover {
  border-color: #e2e8f0;
  background: #fff;
}
.po-field-label {
  display: block;
  font-size: 11px;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 6px;
}
.po-field-value {
  font-size: 14px;
  color: #0f172a;
  font-weight: 500;
  display: block;
  word-break: break-word;
}

/* ── Badge / Status ─────────────────────────────── */
.po-badge {
  display: inline-block;
  padding: 3px 10px;
  background: #eef2ff;
  color: #6366f1;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  text-transform: capitalize;
}
.po-status {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 600;
}
.po-status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}
.po-status--active {
  color: #059669;
}
.po-status--active .po-status-dot {
  background: #10b981;
  box-shadow: 0 0 6px rgba(16,185,129,0.4);
}
.po-status--inactive {
  color: #dc2626;
}
.po-status--inactive .po-status-dot {
  background: #ef4444;
}
</style>