<template>
  <div class="notif-page">
    <div class="notif-header">
      <div class="notif-header-left">
        <h1 class="notif-title">Notifications</h1>
        <p class="notif-subtitle">Stay updated on role changes, permissions, leads, and system events</p>
      </div>
      <button class="btn-ghost" @click="markAllRead" :disabled="loading || notifications.length === 0">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
        Mark all as read
      </button>
    </div>

    <div class="notif-list-card">
      <div v-if="loading" class="notif-loading">
        <div class="spinner"></div>
        <span>Loading notifications...</span>
      </div>
      <div v-else-if="notifications.length === 0" class="notif-empty-box">
        <div class="notif-empty-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
        </div>
        <p class="notif-empty-title">No notifications yet</p>
        <p class="notif-empty-sub">When you get new updates or permission changes, they will appear here.</p>
      </div>
      <div 
        v-else 
        v-for="n in notifications" 
        :key="n.id" 
        class="notif-list-item" 
        :class="{ 'notif-list-item--unread': !n.read }"
        @click="markSingleRead(n)"
      >
        <!-- Icon -->
        <div class="notif-icon-box" :class="parseNotification(n).category">
          <svg v-if="parseNotification(n).category === 'security'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          <svg v-else-if="parseNotification(n).category === 'lead'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
          <svg v-else-if="parseNotification(n).category === 'task'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
          <svg v-else-if="parseNotification(n).category === 'contract'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          <svg v-else-if="parseNotification(n).category === 'invoice'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
          <svg v-else-if="parseNotification(n).category === 'project'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
          <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </div>

        <div class="notif-list-body">
          <div class="notif-header-line">
            <h3 class="notif-title-text">{{ parseNotification(n).title }}</h3>
            <span class="notif-time-text">{{ n.time }}</span>
          </div>

          <!-- Summary Chips for Permission Changes -->
          <div class="notif-chips-row" v-if="parseNotification(n).category === 'security'">
            <span class="notif-chip role" v-if="parseNotification(n).role">
              Role: {{ parseNotification(n).role }}
            </span>
            <span class="notif-chip granted" v-if="parseNotification(n).granted.length">
              + {{ parseNotification(n).granted.length }} Granted
            </span>
            <span class="notif-chip revoked" v-if="parseNotification(n).revoked.length">
              - {{ parseNotification(n).revoked.length }} Revoked
            </span>
          </div>

          <!-- Toggle Details -->
          <button
            v-if="parseNotification(n).rawDetails"
            class="notif-toggle-btn"
            @click.stop="toggleNotifDetails(n.id)"
          >
            <span>{{ isNotifExpanded(n.id) ? 'Hide details' : 'Show details' }}</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" :style="{ transform: isNotifExpanded(n.id) ? 'rotate(180deg)' : 'none', transition: 'transform 0.15s' }">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </button>

          <!-- Expanded Details Box -->
          <div v-if="isNotifExpanded(n.id) && parseNotification(n).rawDetails" class="notif-details-box" @click.stop>
            <div class="notif-detail-group" v-if="parseNotification(n).role">
              <span class="notif-detail-lbl role">New Role</span>
              <span class="notif-detail-val">{{ parseNotification(n).role }}</span>
            </div>
            <div class="notif-detail-group" v-if="parseNotification(n).granted.length">
              <span class="notif-detail-lbl granted">Granted Permissions</span>
              <span class="notif-detail-val">{{ parseNotification(n).granted.join(', ') }}</span>
            </div>
            <div class="notif-detail-group" v-if="parseNotification(n).revoked.length">
              <span class="notif-detail-lbl revoked">Revoked Permissions</span>
              <span class="notif-detail-val">{{ parseNotification(n).revoked.join(', ') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import axios from 'axios';

export default defineComponent({
  name: 'NotificationsPage',
  setup() {
    const notifications = ref([]);
    const loading = ref(false);
    const expandedNotifIds = ref(new Set());

    const toggleNotifDetails = (id) => {
      const newSet = new Set(expandedNotifIds.value);
      if (newSet.has(id)) {
        newSet.delete(id);
      } else {
        newSet.add(id);
      }
      expandedNotifIds.value = newSet;
    };

    const isNotifExpanded = (id) => expandedNotifIds.value.has(id);

    const parseNotification = (item) => {
      const text = item.text || item.description || '';
      let category = 'system';
      let title = text;
      let actor = '';
      let role = '';
      let granted = [];
      let revoked = [];
      let rawDetails = '';

      const lowerText = text.toLowerCase();

      if (lowerText.includes('staff permissions have been updated') || lowerText.includes('permission')) {
        category = 'security';
        
        const actorMatch = text.match(/updated by\s+([^.]+)/i);
        if (actorMatch && actorMatch[1]) {
          actor = actorMatch[1].trim();
        }

        title = actor ? `Staff permissions updated by ${actor}` : 'Staff permissions updated';

        const detailsIdx = text.indexOf('Details:');
        if (detailsIdx !== -1) {
          rawDetails = text.substring(detailsIdx + 8).trim();
          const parts = rawDetails.split('|').map(p => p.trim());

          parts.forEach(part => {
            if (part.toLowerCase().startsWith('new role:')) {
              role = part.substring(9).trim();
            } else if (part.toLowerCase().startsWith('granted:')) {
              const content = part.substring(8).trim();
              granted = content.split(';').map(s => s.trim()).filter(Boolean);
            } else if (part.toLowerCase().startsWith('revoked:')) {
              const content = part.substring(8).trim();
              revoked = content.split(';').map(s => s.trim()).filter(Boolean);
            }
          });
        }
      } else if (lowerText.includes('lead')) {
        category = 'lead';
      } else if (lowerText.includes('task')) {
        category = 'task';
      } else if (lowerText.includes('contract')) {
        category = 'contract';
      } else if (lowerText.includes('invoice') || lowerText.includes('payment')) {
        category = 'invoice';
      } else if (lowerText.includes('project')) {
        category = 'project';
      }

      return {
        category,
        title,
        actor,
        role,
        granted,
        revoked,
        rawDetails
      };
    };

    const fetchNotifications = async () => {
      loading.value = true;
      try {
        const res = await axios.get('/api/notifications');
        notifications.value = res.data.data || [];
      } catch (e) {
        message.error('Failed to load notifications');
      } finally {
        loading.value = false;
      }
    };

    const markAllRead = async () => {
      try {
        await axios.post('/api/notifications/mark-all-read');
        notifications.value.forEach(n => {
          n.read = true;
          n.isread = true;
        });
        message.success('All notifications marked as read');
        if (typeof window !== 'undefined' && window.dispatchEvent) {
          window.dispatchEvent(new CustomEvent('refresh-notifications'));
        }
      } catch (e) {
        message.error('Failed to update notifications');
      }
    };

    const markSingleRead = async (n) => {
      if (n.read) return;
      try {
        await axios.post(`/api/notifications/${n.id}/read`);
        n.read = true;
      } catch (e) {}
    };

    onMounted(() => {
      fetchNotifications();
    });

    return {
      notifications,
      loading,
      markAllRead,
      markSingleRead,
      parseNotification,
      toggleNotifDetails,
      isNotifExpanded
    };
  },
});
</script>

<style scoped>
.notif-page { font-family: inherit; color: #1e293b; max-width: 840px; margin: 0 auto; }
.notif-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 24px;
}
.notif-title { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 4px 0; letter-spacing: -0.01em; }
.notif-subtitle { font-size: 13px; color: #64748b; margin: 0; }
.btn-ghost {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 16px; border: 1px solid #e2e8f0; border-radius: 10px;
  font-size: 13px; font-weight: 600; color: #475569; background: #fff;
  cursor: pointer; font-family: inherit; transition: all 0.15s ease;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.btn-ghost:hover:not(:disabled) { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
.btn-ghost:disabled { opacity: 0.5; cursor: not-allowed; }

.notif-list-card {
  background: #fff; border: 1px solid #e2e8f0;
  border-radius: 16px; overflow: hidden;
  box-shadow: 0 4px 20px -4px rgba(0,0,0,0.04);
}
.notif-loading {
  padding: 40px 24px; text-align: center; color: #64748b; font-size: 13px; font-weight: 500;
  display: flex; flex-direction: column; align-items: center; gap: 12px;
}
.spinner {
  width: 24px; height: 24px; border: 3px solid #e2e8f0; border-top-color: #6366f1;
  border-radius: 50%; animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.notif-empty-box {
  padding: 48px 24px; text-align: center; display: flex; flex-direction: column; align-items: center;
}
.notif-empty-icon {
  width: 60px; height: 60px; border-radius: 50%; background: #f8fafc; color: #94a3b8;
  display: flex; align-items: center; justify-content: center; margin-bottom: 14px;
}
.notif-empty-title { font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 4px 0; }
.notif-empty-sub { font-size: 13px; color: #94a3b8; margin: 0; }

.notif-list-item {
  display: flex; align-items: flex-start; gap: 14px;
  padding: 16px 20px; border-bottom: 1px solid #f1f5f9; cursor: pointer;
  position: relative; transition: background 0.15s ease;
}
.notif-list-item:last-child { border-bottom: none; }
.notif-list-item:hover { background: #f8fafc; }
.notif-list-item--unread { background: #f8faff; }
.notif-list-item--unread::before {
  content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
  background: #6366f1; border-top-right-radius: 4px; border-bottom-right-radius: 4px;
}

/* Category Icon Boxes */
.notif-icon-box {
  width: 40px; height: 40px; border-radius: 11px; display: flex;
  align-items: center; justify-content: center; flex-shrink: 0;
}
.notif-icon-box.security { background: #fef3c7; color: #d97706; }
.notif-icon-box.lead { background: #dbeafe; color: #2563eb; }
.notif-icon-box.task { background: #e0e7ff; color: #4f46e5; }
.notif-icon-box.contract { background: #cffafe; color: #0891b2; }
.notif-icon-box.invoice { background: #dcfce7; color: #16a34a; }
.notif-icon-box.project { background: #f3e8ff; color: #9333ea; }
.notif-icon-box.system { background: #f1f5f9; color: #64748b; }

.notif-list-body { flex: 1; min-width: 0; }
.notif-header-line { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; margin-bottom: 4px; }
.notif-title-text { font-size: 13.5px; font-weight: 600; color: #334155; margin: 0; line-height: 1.4; }
.notif-list-item--unread .notif-title-text { font-weight: 700; color: #0f172a; }
.notif-time-text { font-size: 11.5px; font-weight: 500; color: #94a3b8; white-space: nowrap; flex-shrink: 0; }

/* Chips */
.notif-chips-row { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
.notif-chip {
  display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600;
  padding: 3px 9px; border-radius: 6px; line-height: 1.3;
}
.notif-chip.role { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.notif-chip.granted { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.notif-chip.revoked { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }

.notif-toggle-btn {
  background: transparent; border: none; padding: 0; margin-top: 8px;
  font-size: 11.5px; font-weight: 600; color: #6366f1; cursor: pointer;
  display: inline-flex; align-items: center; gap: 4px; font-family: inherit;
}
.notif-toggle-btn:hover { text-decoration: underline; }

.notif-details-box {
  margin-top: 10px; padding: 12px 14px; background: #f8fafc;
  border: 1px solid #e2e8f0; border-radius: 10px; font-size: 12px; color: #334155;
  display: flex; flex-direction: column; gap: 8px;
}
.notif-detail-group { display: flex; flex-direction: column; gap: 3px; }
.notif-detail-lbl { font-weight: 700; font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.04em; }
.notif-detail-lbl.granted { color: #047857; }
.notif-detail-lbl.revoked { color: #be123c; }
.notif-detail-lbl.role { color: #4338ca; }
.notif-detail-val { color: #475569; line-height: 1.45; word-break: break-word; }
</style>
