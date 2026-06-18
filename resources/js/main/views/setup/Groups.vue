<template>
  <div>
    <div class="section-toolbar">
      <h2 class="section-title">Customer Groups</h2>
      <button class="btn-primary" @click="openDrawer = true">
        <span>+</span> Add New Customer Group
      </button>
    </div>

    <div class="data-table-wrap">
      <div class="data-table-controls">
        <a-input-search
          v-model:value="search"
          placeholder="Search groups..."
          style="width: 280px"
          size="small"
        />
      </div>
      <a-table
        :dataSource="filteredGroups"
        :columns="columns"
        :pagination="{ pageSize: 25, total: filteredGroups.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
        row-key="id"
        size="small"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'name'">
            <span class="group-name">{{ record.name }}</span>
          </template>
          <template v-if="column.key === 'options'">
            <div class="row-actions">
              <a-button size="small" type="link" @click="editGroup(record)">Edit</a-button>
              <a-button size="small" type="link" danger @click="deleteGroup(record.id)">Delete</a-button>
            </div>
          </template>
        </template>
      </a-table>
    </div>

    <!-- Add/Edit Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      :title="editingId ? 'Edit Customer Group' : 'Add New Customer Group'"
      placement="right"
      :width="400"
      @close="resetForm"
    >
      <a-form layout="vertical" :model="form" @finish="saveGroup">
        <a-form-item label="* Name" name="name" :rules="[{ required: true, message: 'Group name required' }]">
          <a-input v-model:value="form.name" placeholder="Enter group name" />
        </a-form-item>
        <div class="drawer-footer">
          <a-button @click="openDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">
            {{ editingId ? 'Update Group' : 'Add Group' }}
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
  name: 'GroupsView',
  setup() {
    const search = ref('');
    const openDrawer = ref(false);
    const saving = ref(false);
    const editingId = ref(null);

    const groups = ref([
      { id: 1, name: 'High Budget' },
      { id: 2, name: 'Low Budget' },
      { id: 3, name: 'VIP' },
      { id: 4, name: 'Wholesaler' },
    ]);

    const form = reactive({ name: '' });

    const columns = [
      { title: 'Name',    key: 'name',    dataIndex: 'name' },
      { title: 'Options', key: 'options', width: 150 },
    ];

    const filteredGroups = computed(() => {
      if (!search.value) return groups.value;
      return groups.value.filter(g => g.name.toLowerCase().includes(search.value.toLowerCase()));
    });

    const editGroup = (record) => {
      editingId.value = record.id;
      form.name = record.name;
      openDrawer.value = true;
    };

    const deleteGroup = (id) => {
      groups.value = groups.value.filter(g => g.id !== id);
      message.success('Group deleted');
    };

    const saveGroup = () => {
      if (!form.name.trim()) return;
      if (editingId.value) {
        const g = groups.value.find(x => x.id === editingId.value);
        if (g) g.name = form.name.trim();
        message.success('Group updated');
      } else {
        groups.value.push({ id: Date.now(), name: form.name.trim() });
        message.success('Group added');
      }
      openDrawer.value = false;
      resetForm();
    };

    const resetForm = () => {
      editingId.value = null;
      form.name = '';
    };

    return {
      search, openDrawer, saving, editingId,
      groups, form, columns, filteredGroups,
      editGroup, deleteGroup, saveGroup, resetForm,
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
.btn-primary span {
  font-size: 16px;
  font-weight: 700;
  line-height: 1;
}
.data-table-wrap {}
.data-table-controls {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 12px;
}
.row-actions {
  display: flex;
  gap: 4px;
}
.group-name {
  font-weight: 600;
  color: #1e293b;
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
