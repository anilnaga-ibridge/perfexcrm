<template>
  <div>
    <!-- HEADER & TOOLBAR -->
    <div class="section-toolbar">
      <h2 class="section-title">Custom Fields</h2>
      <button class="btn-primary" @click="openNewFieldDrawer">Add New Custom field</button>
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
          :dataSource="filteredFields"
          :columns="fieldColumns"
          :pagination="{ pageSize: pageSize, total: filteredFields.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
          row-key="id"
          size="small"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'id'">
              <span class="field-id">{{ record.id }}</span>
            </template>
            <template v-if="column.key === 'name'">
              <span class="field-name">{{ record.name }}</span>
            </template>
            <template v-if="column.key === 'belongs_to'">
              <span style="text-transform: capitalize;">{{ record.belongs_to }}</span>
            </template>
            <template v-if="column.key === 'type'">
              <a-tag color="blue">{{ record.type }}</a-tag>
            </template>
            <template v-if="column.key === 'slug'">
              <code>{{ record.slug }}</code>
            </template>
            <template v-if="column.key === 'active'">
              <a-tag :color="record.active ? 'success' : 'default'">
                {{ record.active ? 'Active' : 'Inactive' }}
              </a-tag>
            </template>
            <template v-if="column.key === 'options'">
              <div class="row-actions">
                <a-button size="small" type="link" @click="editField(record)">Edit</a-button>
                <a-button size="small" type="link" danger @click="deleteField(record.id)">Delete</a-button>
              </div>
            </template>
          </template>
        </a-table>
      </div>
    </div>

    <!-- Add/Edit Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      :title="editingId ? 'Edit Custom Field' : 'Add New Custom field'"
      placement="right"
      :width="460"
      @close="resetForm"
    >
      <a-form layout="vertical" :model="form" @finish="saveField">
        <a-form-item label="* Field Belongs to" name="belongs_to" :rules="[{ required: true, message: 'Select what the field belongs to' }]">
          <a-select v-model:value="form.belongs_to" placeholder="Select context">
            <a-select-option value="clients">Clients</a-select-option>
            <a-select-option value="contacts">Contacts</a-select-option>
            <a-select-option value="leads">Leads</a-select-option>
            <a-select-option value="invoices">Invoices</a-select-option>
            <a-select-option value="tasks">Tasks</a-select-option>
            <a-select-option value="projects">Projects</a-select-option>
          </a-select>
        </a-form-item>

        <a-form-item label="* Field Name" name="name" :rules="[{ required: true, message: 'Field name required' }]">
          <a-input v-model:value="form.name" placeholder="Enter custom field name" />
        </a-form-item>

        <a-form-item label="* Type" name="type" :rules="[{ required: true, message: 'Select field type' }]">
          <a-select v-model:value="form.type" placeholder="Select type">
            <a-select-option value="input">Input (Text)</a-select-option>
            <a-select-option value="number">Number</a-select-option>
            <a-select-option value="textarea">Textarea</a-select-option>
            <a-select-option value="select">Select Dropdown</a-select-option>
            <a-select-option value="checkbox">Checkbox</a-select-option>
            <a-select-option value="radio">Radio Group</a-select-option>
            <a-select-option value="date">Date</a-select-option>
          </a-select>
        </a-form-item>

        <a-form-item label="Default Value" name="default_value">
          <a-textarea v-model:value="form.default_value" :rows="3" placeholder="Enter default value" />
        </a-form-item>

        <a-form-item label="Order" name="order">
          <a-input-number v-model:value="form.order" :min="0" style="width: 100%" placeholder="0" />
        </a-form-item>

        <a-form-item label="* Grid (Bootstrap Column eq. 12) - Max is 12" name="grid" :rules="[{ required: true, message: 'Grid is required' }]">
          <a-input v-model:value="form.grid" placeholder="12">
            <template #addonBefore>col-md-</template>
          </a-input>
        </a-form-item>

        <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 8px;">
          <a-form-item name="disabled" style="margin-bottom: 0;">
            <a-checkbox v-model:checked="form.disabled">
              Disabled
            </a-checkbox>
          </a-form-item>

          <a-form-item name="admin_only" style="margin-bottom: 0;">
            <a-checkbox v-model:checked="form.admin_only">
              Restrict visibility for administrators only
            </a-checkbox>
          </a-form-item>

          <a-form-item name="required" style="margin-bottom: 0;">
            <a-checkbox v-model:checked="form.required">
              Required
            </a-checkbox>
          </a-form-item>

          <a-form-item name="visibility" style="margin-bottom: 0;">
            <a-checkbox v-model:checked="form.visibility">
              Visibility
            </a-checkbox>
          </a-form-item>

          <a-form-item name="show_on_table" style="margin-bottom: 0;">
            <a-checkbox v-model:checked="form.show_on_table">
              Show on table
            </a-checkbox>
          </a-form-item>
        </div>

        <div class="drawer-footer">
          <a-button @click="openDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">
            {{ editingId ? 'Update Field' : 'Add Field' }}
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
  name: 'CustomFieldsView',
  setup() {
    const search = ref('');
    const pageSize = ref(25);
    const openDrawer = ref(false);
    const saving = ref(false);
    const editingId = ref(null);

    const customFields = ref([]);

    const form = reactive({
      belongs_to: undefined,
      name: '',
      type: undefined,
      default_value: '',
      order: 0,
      grid: '12',
      disabled: false,
      admin_only: false,
      required: false,
      visibility: true,
      show_on_table: true
    });

    const fieldColumns = [
      { title: 'ID', dataIndex: 'id', key: 'id', width: 80 },
      { title: 'Name', dataIndex: 'name', key: 'name' },
      { title: 'Belongs to', dataIndex: 'belongs_to', key: 'belongs_to', width: 140 },
      { title: 'Type', dataIndex: 'type', key: 'type', width: 140 },
      { title: 'Slug', dataIndex: 'slug', key: 'slug', width: 160 },
      { title: 'Active', key: 'active', width: 110 },
      { title: 'Options', key: 'options', width: 140 }
    ];

    const filteredFields = computed(() => {
      const sorted = [...customFields.value].sort((a, b) => b.id - a.id);
      if (!search.value) return sorted;
      const q = search.value.toLowerCase();
      return sorted.filter(f => 
        f.name.toLowerCase().includes(q) ||
        f.belongs_to.toLowerCase().includes(q) ||
        f.type.toLowerCase().includes(q)
      );
    });

    const openNewFieldDrawer = () => {
      resetForm();
      openDrawer.value = true;
    };

    const editField = (record) => {
      editingId.value = record.id;
      form.belongs_to = record.belongs_to;
      form.name = record.name;
      form.type = record.type;
      form.default_value = record.default_value || '';
      form.order = record.order || 0;
      form.grid = record.grid || '12';
      form.disabled = record.disabled || false;
      form.admin_only = record.admin_only || false;
      form.required = record.required || false;
      form.visibility = record.visibility !== undefined ? record.visibility : true;
      form.show_on_table = record.show_on_table !== undefined ? record.show_on_table : true;
      openDrawer.value = true;
    };

    const deleteField = (id) => {
      customFields.value = customFields.value.filter(f => f.id !== id);
      message.success('Custom field deleted');
    };

    const saveField = () => {
      if (!form.name.trim() || !form.belongs_to || !form.type) return;
      saving.value = true;

      try {
        const slug = form.name.trim().toLowerCase().replace(/[^a-z0-9]+/g, '_');
        if (editingId.value) {
          const item = customFields.value.find(x => x.id === editingId.value);
          if (item) {
            item.belongs_to = form.belongs_to;
            item.name = form.name.trim();
            item.type = form.type;
            item.slug = 'cf_' + slug;
            item.default_value = form.default_value;
            item.order = form.order;
            item.grid = form.grid;
            item.disabled = form.disabled;
            item.admin_only = form.admin_only;
            item.required = form.required;
            item.visibility = form.visibility;
            item.show_on_table = form.show_on_table;
            item.active = !form.disabled;
          }
          message.success('Custom field updated');
        } else {
          const maxId = customFields.value.reduce((max, f) => f.id > max ? f.id : max, 0);
          customFields.value.push({
            id: maxId + 1,
            belongs_to: form.belongs_to,
            name: form.name.trim(),
            type: form.type,
            slug: 'cf_' + slug,
            default_value: form.default_value,
            order: form.order,
            grid: form.grid,
            disabled: form.disabled,
            admin_only: form.admin_only,
            required: form.required,
            visibility: form.visibility,
            show_on_table: form.show_on_table,
            active: !form.disabled
          });
          message.success('Custom field added');
        }
        openDrawer.value = false;
        resetForm();
      } catch (err) {
        message.error('Error saving custom field');
      } finally {
        saving.value = false;
      }
    };

    const resetForm = () => {
      editingId.value = null;
      Object.assign(form, {
        belongs_to: undefined,
        name: '',
        type: undefined,
        default_value: '',
        order: 0,
        grid: '12',
        disabled: false,
        admin_only: false,
        required: false,
        visibility: true,
        show_on_table: true
      });
    };

    return {
      search,
      pageSize,
      openDrawer,
      saving,
      editingId,
      customFields,
      form,
      fieldColumns,
      filteredFields,
      openNewFieldDrawer,
      editField,
      deleteField,
      saveField,
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
.field-id {
  color: #64748b;
  font-family: monospace;
}
.field-name {
  font-weight: 600;
  color: #1e293b;
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
