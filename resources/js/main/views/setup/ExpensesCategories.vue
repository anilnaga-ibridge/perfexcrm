<template>
  <div>
    <!-- HEADER & TOOLBAR -->
    <div class="section-toolbar">
      <h2 class="section-title">Expenses Categories</h2>
      <button class="btn-primary" @click="openNewCategoryDrawer">New Category</button>
    </div>

    <!-- MAIN CARD / DATA TABLE -->
    <div class="settings-card">
      <div class="data-table-wrap">
        <div class="data-table-controls">
          <div class="page-size-select">
            <a-select v-model:value="pageSize" size="small" style="width: 70px">
              <a-select-option :value="10">10</a-select-option>
              <a-select-option :value="25">25</a-select-option>
              <a-select-option :value="50">50</a-select-option>
            </a-select>
          </div>
          <a-input-search
            v-model:value="search"
            placeholder="Search..."
            style="width: 280px"
            size="small"
          />
        </div>

        <a-table
          :dataSource="filteredCategories"
          :columns="categoryColumns"
          :pagination="{ pageSize: pageSize, total: filteredCategories.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
          row-key="id"
          size="small"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'id'">
              <span class="category-id">{{ record.id }}</span>
            </template>
            <template v-if="column.key === 'name'">
              <span class="category-name">{{ record.name }}</span>
            </template>
            <template v-if="column.key === 'description'">
              <span class="text-muted">{{ record.description }}</span>
            </template>
            <template v-if="column.key === 'options'">
              <div class="row-actions">
                <a-button size="small" type="link" @click="editCategory(record)">Edit</a-button>
                <a-button size="small" type="link" danger @click="deleteCategory(record.id)">Delete</a-button>
              </div>
            </template>
          </template>
        </a-table>
      </div>
    </div>

    <!-- Add/Edit Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      :title="editingId ? 'Edit Category' : 'New Category'"
      placement="right"
      :width="400"
      @close="resetForm"
    >
      <a-form layout="vertical" :model="form" @finish="saveCategory">
        <a-form-item label="* Category Name" name="name" :rules="[{ required: true, message: 'Category name required' }]">
          <a-input v-model:value="form.name" placeholder="Enter category name" />
        </a-form-item>
        <a-form-item label="Category Description" name="description">
          <a-textarea v-model:value="form.description" :rows="4" placeholder="Enter category description" />
        </a-form-item>

        <div class="drawer-footer">
          <a-button @click="openDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">
            {{ editingId ? 'Update Category' : 'Add Category' }}
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
  name: 'ExpensesCategoriesView',
  setup() {
    const search = ref('');
    const pageSize = ref(25);
    const openDrawer = ref(false);
    const saving = ref(false);
    const editingId = ref(null);

    const expenseCategories = ref([
      { id: 2, name: 'Automobile Expense', description: 'Cat, and vanished again. Alice waited till she too began dreaming after a few yards off. The Cat.' },
      { id: 5, name: 'Insurance', description: 'King very decidedly, and the little dears came jumping merrily along hand in hand with Dinah, and.' },
      { id: 8, name: 'IT and Internet Expenses', description: 'I eat or drink something or other; but the Hatter said, tossing his head contemptuously. \'I dare.' },
      { id: 6, name: 'Meals', description: 'Dodo had paused as if he had a little scream, half of fright and half believed herself in the.' },
      { id: 3, name: 'Parking', description: 'Mock Turtle, capering wildly about. \'Change lobsters again!\' yelled the Gryphon answered, very.' },
      { id: 4, name: 'Telephone', description: 'Alice. \'Nothing WHATEVER?\' persisted the King. \'Then it wasn\'t very civil of you to get in?\'.' },
      { id: 7, name: 'Travel Expense', description: 'I\'m angry. Therefore I\'m mad.\' \'I call it sad?\' And she began nursing her child again, singing a.' },
      { id: 1, name: 'Trips', description: 'Five, \'and I\'ll tell you what year it is?\' \'Of course twinkling begins with an M, such as.' }
    ]);

    const form = reactive({
      name: '',
      description: ''
    });

    const categoryColumns = [
      { title: 'ID', dataIndex: 'id', key: 'id', width: 80 },
      { title: 'Name', dataIndex: 'name', key: 'name' },
      { title: 'Description', dataIndex: 'description', key: 'description' },
      { title: 'Options', key: 'options', width: 140 }
    ];

    const filteredCategories = computed(() => {
      // Sort alphabetically by Name
      const sorted = [...expenseCategories.value].sort((a, b) => a.name.localeCompare(b.name));
      if (!search.value) return sorted;
      return sorted.filter(c => 
        c.name.toLowerCase().includes(search.value.toLowerCase()) ||
        c.description.toLowerCase().includes(search.value.toLowerCase())
      );
    });

    const openNewCategoryDrawer = () => {
      resetForm();
      openDrawer.value = true;
    };

    const editCategory = (record) => {
      editingId.value = record.id;
      form.name = record.name;
      form.description = record.description || '';
      openDrawer.value = true;
    };

    const deleteCategory = (id) => {
      expenseCategories.value = expenseCategories.value.filter(c => c.id !== id);
      message.success('Expense category deleted');
    };

    const saveCategory = () => {
      if (!form.name.trim()) return;
      saving.value = true;

      try {
        if (editingId.value) {
          const item = expenseCategories.value.find(x => x.id === editingId.value);
          if (item) {
            item.name = form.name.trim();
            item.description = form.description.trim();
          }
          message.success('Expense category updated');
        } else {
          const maxId = expenseCategories.value.reduce((max, c) => c.id > max ? c.id : max, 0);
          expenseCategories.value.push({
            id: maxId + 1,
            name: form.name.trim(),
            description: form.description.trim()
          });
          message.success('Expense category added');
        }
        openDrawer.value = false;
        resetForm();
      } catch (err) {
        message.error('Error saving expense category');
      } finally {
        saving.value = false;
      }
    };

    const resetForm = () => {
      editingId.value = null;
      form.name = '';
      form.description = '';
    };

    return {
      search,
      pageSize,
      openDrawer,
      saving,
      editingId,
      expenseCategories,
      form,
      categoryColumns,
      filteredCategories,
      openNewCategoryDrawer,
      editCategory,
      deleteCategory,
      saveCategory,
      resetForm
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
.section-title {
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
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
.settings-card {
  background: #fff;
}
.data-table-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}
.category-id {
  color: #64748b;
  font-family: monospace;
}
.category-name {
  font-weight: 600;
  color: #1e293b;
}
.text-muted {
  color: #64748b;
}
.row-actions {
  display: flex;
  gap: 4px;
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
