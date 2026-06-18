<template>
  <div>
    <div class="section-toolbar">
      <h2 class="section-title">Spam Filters</h2>
      <button class="btn-primary" @click="openDrawer = true">
        <span>+</span> Add Spam Filter
      </button>
    </div>

    <div class="data-table-wrap">
      <a-tabs v-model:activeKey="activeTab" @change="onTabChange">
        <a-tab-pane key="email" tab="Blocked Senders" />
        <a-tab-pane key="subject" tab="Blocked Subjects" />
        <a-tab-pane key="phrase" tab="Blocked Phrases" />
      </a-tabs>

      <div class="data-table-controls">
        <a-input-search
          v-model:value="search"
          placeholder="Search..."
          style="width: 280px"
          size="small"
        />
      </div>
      <a-table
        :dataSource="filteredFilters"
        :columns="columns"
        :pagination="{ pageSize: 25, total: filteredFilters.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
        row-key="id"
        size="small"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'content'">
            <span class="filter-content">{{ record.value }}</span>
          </template>
          <template v-if="column.key === 'options'">
            <div class="row-actions">
              <a-button size="small" type="link" danger @click="deleteFilter(record.id)">Delete</a-button>
            </div>
          </template>
        </template>
        <template #emptyText>
          <div class="empty-state">No entries found</div>
        </template>
      </a-table>
    </div>

    <!-- Add Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      title="Add Spam Filter"
      placement="right"
      :width="440"
      @close="resetForm"
    >
      <a-form layout="vertical" :model="form" @finish="saveFilter">
        <a-form-item label="* Type" name="type" :rules="[{ required: true, message: 'Type required' }]">
          <a-select v-model:value="form.type" style="width: 100%">
            <a-select-option value="email">Blocked Sender (Email)</a-select-option>
            <a-select-option value="subject">Blocked Subject</a-select-option>
            <a-select-option value="phrase">Blocked Phrase</a-select-option>
          </a-select>
        </a-form-item>

        <a-form-item label="* Content" name="value" :rules="[{ required: true, message: 'Content required' }]">
          <a-input v-model:value="form.value" :placeholder="placeholderText" />
        </a-form-item>

        <div class="drawer-footer">
          <a-button @click="openDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">
            Add Filter
          </a-button>
        </div>
      </a-form>
    </a-drawer>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed } from 'vue';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'SpamFiltersView',
  setup() {
    const search = ref('');
    const openDrawer = ref(false);
    const saving = ref(false);
    const activeTab = ref('email');

    const filters = ref([
      { id: 1, type: 'email', value: 'spam@bad-domain.com' },
      { id: 2, type: 'subject', value: 'viagra' },
      { id: 3, type: 'phrase', value: 'lottery winner' },
    ]);

    const form = reactive({ type: 'email', value: '' });

    const columns = [
      { title: 'Content', key: 'content', dataIndex: 'value' },
      { title: 'Options', key: 'options', width: 120 },
    ];

    const filteredFilters = computed(() => {
      let list = filters.value.filter(f => f.type === activeTab.value);
      if (search.value) {
        list = list.filter(f => f.value.toLowerCase().includes(search.value.toLowerCase()));
      }
      return list;
    });

    const placeholderText = computed(() => {
      if (form.type === 'email') return 'e.g. spam@domain.com';
      if (form.type === 'subject') return 'e.g. free money';
      return 'e.g. lottery winner';
    });

    const onTabChange = (key) => {
      activeTab.value = key;
      form.type = key;
    };

    const saveFilter = () => {
      if (!form.value.trim()) return;
      filters.value.push({ id: Date.now(), type: form.type, value: form.value.trim() });
      openDrawer.value = false;
      resetForm();
      message.success('Spam filter added');
    };

    const deleteFilter = (id) => {
      filters.value = filters.value.filter(f => f.id !== id);
      message.success('Spam filter deleted');
    };

    const resetForm = () => {
      form.type = activeTab.value;
      form.value = '';
    };

    return {
      search, openDrawer, saving, activeTab,
      filters, form, columns, filteredFilters, placeholderText,
      onTabChange, saveFilter, deleteFilter, resetForm,
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
.section-toolbar .section-title { margin: 0; }
.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 20px;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;
}
.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
}
.btn-primary span { font-size: 16px; font-weight: 700; line-height: 1; }
.data-table-controls {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 12px;
}
.row-actions { display: flex; gap: 4px; }
.filter-content { font-weight: 500; color: #1e293b; }
.empty-state {
  padding: 40px 0;
  color: #94a3b8;
  font-size: 14px;
}
.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  margin-top: 16px;
}
</style>
