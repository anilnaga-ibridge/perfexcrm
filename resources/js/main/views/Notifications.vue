<template>
  <div class="notif-page">
    <div class="notif-header">
      <div class="notif-header-left">
        <h1 class="notif-title">Notifications</h1>
      </div>
      <button class="btn-ghost" @click="markAllRead">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
        Mark all as read
      </button>
    </div>

    <div class="notif-list-card">
      <div v-for="(n, i) in notifications" :key="i" class="notif-list-item" :class="{ 'notif-list-item--unread': !n.read }">
        <div class="notif-list-dot" v-if="!n.read"></div>
        <div class="notif-list-body">
          <p class="notif-list-text">{{ n.text }}</p>
          <span class="notif-list-time">{{ n.time }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref } from 'vue';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'NotificationsPage',
  setup() {
    const notifications = ref([
      { text: 'Contract with subject There was a dead silence. Alice noticed with some surprise. has been signed by the customer', time: '4 hrs ago', read: false },
    ]);

    const markAllRead = () => {
      notifications.value.forEach(n => n.read = true);
      message.success('All notifications marked as read');
    };

    return { notifications, markAllRead };
  },
});
</script>

<style scoped>
.notif-page { font-family: inherit; color: #1e293b; }
.notif-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 20px;
}
.notif-title { font-size: 20px; font-weight: 800; color: #0f172a; margin: 0; }
.btn-ghost {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 7px 14px; border: 1px solid #e2e8f0; border-radius: 8px;
  font-size: 12px; font-weight: 600; color: #475569; background: #fff;
  cursor: pointer; font-family: inherit; transition: all 0.1s;
}
.btn-ghost:hover { background: #f8fafc; border-color: #cbd5e1; }

.notif-list-card {
  background: #fff; border: 1px solid #e2e8f0;
  border-radius: 12px; overflow: hidden;
}
.notif-list-item {
  display: flex; align-items: flex-start; gap: 12px;
  padding: 14px 18px; border-bottom: 1px solid #f1f5f9;
}
.notif-list-item:last-child { border-bottom: none; }
.notif-list-item--unread { background: #f8faff; }
.notif-list-dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: #6366f1; flex-shrink: 0; margin-top: 5px;
}
.notif-list-body { flex: 1; min-width: 0; }
.notif-list-text { font-size: 13px; font-weight: 500; color: #334155; margin: 0 0 4px 0; line-height: 1.5; }
.notif-list-time { font-size: 11px; font-weight: 600; color: #94a3b8; }
</style>
