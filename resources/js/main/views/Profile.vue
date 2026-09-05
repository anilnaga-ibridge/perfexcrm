<template>
  <div class="pl-page">
    <div class="pl-content">
      <div class="pl-sidebar">
        <!-- Profile Card -->
        <div class="pl-profile-card">
          <div class="pl-profile-bg"></div>
          <div class="pl-profile-body">
            <div class="pl-avatar-ring">
              <div class="pl-avatar-wrapper">
                <img
                  v-if="user.profile_image && !imageError"
                  :src="getProfileImageUrl(user.profile_image)"
                  :alt="user.name"
                  @error="imageError = true"
                  class="pl-avatar"
                />
                <div
                  v-else
                  class="w-20 h-20 rounded-2xl relative overflow-hidden flex items-center justify-center shadow-lg animated-avatar-wrap"
                >
                  <img :src="clayAvatarUrl" alt="Avatar" class="w-16 h-16 object-contain animate-avatar-float drop-shadow-md" />
                </div>
              </div>
            </div>
            <h2 class="pl-name">{{ user.name }}</h2>
            <p class="pl-email">{{ user.email }}</p>
            <span v-if="user.role" class="pl-role">{{ getRoleName(user) }}</span>
          </div>
        </div>

        <!-- Navigation Menu -->
        <div class="pl-menu">
          <router-link
            v-for="item in profileMenuItems"
            :key="item.key"
            :to="item.path"
            :class="['pl-menu-item', { 'pl-menu-item--active': isActive(item.path) }]"
          >
            <span class="pl-menu-icon" v-html="item.icon"></span>
            <span class="pl-menu-label">{{ item.label }}</span>
            <span class="pl-menu-chevron">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </span>
          </router-link>
        </div>
      </div>

      <div class="pl-main">
        <router-view />
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../store/authStore';
import ProfileOverview from './profile/ProfileOverview.vue';
import ProfileEdit from './profile/ProfileEdit.vue';
import clayAvatarUrl from '../assets/clay_avatar.png';
import { getRoleName } from '../utils/permissions';

export default defineComponent({
  name: 'ProfileLayout',
  components: {
    ProfileOverview,
    ProfileEdit,
  },
  setup() {
    const route = useRoute();
    const authStore = useAuthStore();
    const user = computed(() => authStore.user || {});
    const imageError = ref(false);

    watch(() => user.value?.profile_image, () => {
      imageError.value = false;
    });

    const isActive = (path) => route.path === path;

    const getProfileImageUrl = (imagePath) => {
      if (!imagePath) return '';
      const basePath = window.config?.path?.replace(/\/$/, '') || '';
      if (imagePath.startsWith('http') || imagePath.startsWith('/')) return imagePath;
      return `${basePath}/${imagePath}`;
    };

    const initials = (name) => {
      if (!name) return '?';
      return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
    };

    const avatarColor = (name) => {
      const colors = ['#6366f1', '#ec4899', '#f59e0b', '#10b981', '#8b5cf6', '#ef4444'];
      let hash = 0;
      for (let i = 0; i < (name || '').length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
      return colors[Math.abs(hash) % colors.length];
    };

    const profileMenuItems = [
      { key: 'overview', label: 'Overview', path: '/admin/profile', icon: overviewIcon },
      { key: 'edit', label: 'Edit Profile', path: '/admin/profile/edit', icon: editIcon },
    ];

    return {
      user, profileMenuItems, isActive, getProfileImageUrl, initials, avatarColor, clayAvatarUrl, imageError, getRoleName,
    };
  },
});

const overviewIcon = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`;

const editIcon = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`;
</script>

<style scoped>
.pl-page {
  height: 100%;
  display: flex;
  flex-direction: column;
}
.pl-content {
  display: flex;
  flex: 1;
  gap: 24px;
}

/* ── Sidebar ──────────────────────────────────── */
.pl-sidebar {
  width: 290px;
  flex-shrink: 0;
}
@media (max-width: 900px) {
  .pl-content { flex-direction: column; }
  .pl-sidebar { width: 100%; }
}

/* ── Profile Card ─────────────────────────────── */
.pl-profile-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  overflow: hidden;
  margin-bottom: 20px;
}
.pl-profile-bg {
  height: 90px;
  background: linear-gradient(135deg, #d35400 0%, #7e1e8e 50%, #0b579f 100%);
}
.pl-profile-body {
  text-align: center;
  padding: 0 24px 24px;
  margin-top: -40px;
}
.pl-avatar-ring {
  width: 88px;
  height: 88px;
  margin: 0 auto 14px;
  border-radius: 50%;
  padding: 4px;
  background: linear-gradient(135deg, #d35400, #7e1e8e, #0b579f);
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}
.pl-avatar-wrapper {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  overflow: hidden;
  background: #fff;
}
.pl-avatar {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.pl-avatar-fallback {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  font-weight: 700;
  color: #fff;
}
.pl-name {
  font-size: 18px;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 2px;
  letter-spacing: -0.2px;
}
.pl-email {
  font-size: 13px;
  color: #64748b;
  margin: 0 0 14px;
}
.pl-role {
  display: inline-block;
  padding: 6px 18px;
  background: linear-gradient(135deg, #eef2ff, #e0e7ff);
  border: 1px solid #c7d2fe;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  color: #4f46e5;
  text-transform: capitalize;
}

/* ── Menu ─────────────────────────────────────── */
.pl-menu {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  overflow: hidden;
}
.pl-menu-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 22px;
  color: #475569;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  border-bottom: 1px solid #f1f5f9;
  transition: all 0.2s;
  position: relative;
}
.pl-menu-item:last-child {
  border-bottom: none;
}
.pl-menu-item:hover {
  background: #f8fafc;
  color: #0f172a;
  padding-left: 26px;
}
.pl-menu-item--active {
  background: linear-gradient(135deg, #eef2ff, #e0e7ff);
  color: #4f46e5;
  font-weight: 600;
  padding-left: 26px;
}
.pl-menu-item--active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 8px;
  bottom: 8px;
  width: 3px;
  background: #6366f1;
  border-radius: 0 3px 3px 0;
}
.pl-menu-icon {
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  flex-shrink: 0;
  transition: color 0.2s;
}
.pl-menu-icon :deep(svg) { width: 20px; height: 20px; }
.pl-menu-item:hover .pl-menu-icon { color: #64748b; }
.pl-menu-item--active .pl-menu-icon { color: #4f46e5; }
.pl-menu-label { flex: 1; }
.pl-menu-chevron {
  color: #cbd5e1;
  display: flex;
  align-items: center;
  opacity: 0;
  transition: opacity 0.2s, transform 0.2s;
}
.pl-menu-chevron svg { width: 14px; height: 14px; }
.pl-menu-item:hover .pl-menu-chevron,
.pl-menu-item--active .pl-menu-chevron {
  opacity: 1;
}
.pl-menu-item--active .pl-menu-chevron {
  transform: translateX(2px);
}

/* ── Main Content ─────────────────────────────── */
.pl-main {
  flex: 1;
  min-width: 0;
}

.animated-avatar-wrap {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 50%, #3730a3 100%);
  box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
  animation: avatarPulseGlow 4s ease-in-out infinite alternate;
}

.animate-avatar-float {
  animation: avatarFloat 3.5s ease-in-out infinite alternate;
}

@keyframes avatarFloat {
  0% { transform: translateY(0px) scale(1); }
  50% { transform: translateY(-3px) scale(1.04); }
  100% { transform: translateY(0px) scale(1); }
}

@keyframes avatarPulseGlow {
  0% { box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3); }
  100% { box-shadow: 0 6px 20px rgba(99, 102, 241, 0.55); }
}
</style>