<template>
  <div>
    <div class="section-toolbar">
      <h2 class="section-title">Predefined Replies</h2>
      <button class="btn-primary" @click="openDrawer = true">
        <span>+</span> Add New Predefined Reply
      </button>
    </div>

    <div class="data-table-wrap">
      <div class="data-table-controls">
        <a-input-search
          v-model:value="search"
          placeholder="Search replies..."
          style="width: 280px"
          size="small"
        />
      </div>
      <a-table
        :dataSource="filteredReplies"
        :columns="columns"
        :pagination="{ pageSize: 25, total: filteredReplies.length, showTotal: (total, range) => `Showing ${range[0]} to ${range[1]} of ${total} entries` }"
        row-key="id"
        size="small"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'name'">
            <span class="reply-name">{{ record.name }}</span>
          </template>
          <template v-if="column.key === 'options'">
            <div class="row-actions">
              <a-button size="small" type="link" @click="editReply(record)">Edit</a-button>
              <a-button size="small" type="link" danger @click="deleteReply(record.id)">Delete</a-button>
            </div>
          </template>
        </template>
      </a-table>
    </div>

    <!-- Add/Edit Drawer -->
    <a-drawer
      v-model:open="openDrawer"
      :title="editingId ? 'Edit Predefined Reply' : 'Add New Predefined Reply'"
      placement="right"
      :width="600"
      @close="resetForm"
    >
      <a-form layout="vertical" :model="form" @finish="saveReply">
        <a-form-item label="* Predefined Reply Name" name="name" :rules="[{ required: true, message: 'Reply name required' }]">
          <QuillEditor
            ref="quillRef"
            v-model:content="form.name"
            content-type="html"
            :options="editorOptions"
            style="height: 300px"
          />
        </a-form-item>
        <div class="drawer-footer">
          <a-button @click="openDrawer = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="saving">
            {{ editingId ? 'Update Reply' : 'Add Reply' }}
          </a-button>
        </div>
      </a-form>
    </a-drawer>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed } from 'vue';
import { message } from 'ant-design-vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

export default defineComponent({
  name: 'PredefinedRepliesView',
  components: { QuillEditor },
  setup() {
    const search = ref('');
    const openDrawer = ref(false);
    const saving = ref(false);
    const editingId = ref(null);
    const quillRef = ref(null);

    const replies = ref([
      { id: 1, name: 'Alice, very loudly and.' },
      { id: 2, name: 'Bill, I fancy--Who\'s to.' },
      { id: 3, name: 'Do come back with the next.' },
      { id: 4, name: 'Normans--" How.' },
      { id: 5, name: 'Queen. \'Well, I.' },
    ]);

    const form = reactive({ name: '' });

    const editorOptions = {
      theme: 'snow',
      modules: {
        toolbar: [
          [{ header: [1, 2, 3, 4, 5, 6, false] }],
          [{ font: [] }],
          [{ size: ['small', false, 'large', 'huge'] }],
          ['bold', 'italic', 'underline', 'strike'],
          [{ color: [] }, { background: [] }],
          [{ script: 'sub' }, { script: 'super' }],
          ['blockquote', 'code-block'],
          [{ list: 'ordered' }, { list: 'bullet' }],
          [{ indent: '-1' }, { indent: '+1' }],
          [{ direction: 'rtl' }],
          [{ align: [] }],
          ['link', 'image', 'video'],
          ['clean']
        ]
      },
      placeholder: 'Enter predefined reply content...'
    };

    const columns = [
      { title: 'Predefined Reply Name', key: 'name', dataIndex: 'name' },
      { title: 'Options', key: 'options', width: 150 },
    ];

    const filteredReplies = computed(() => {
      if (!search.value) return replies.value;
      return replies.value.filter(r => r.name.toLowerCase().includes(search.value.toLowerCase()));
    });

    const editReply = (record) => {
      editingId.value = record.id;
      form.name = record.name;
      openDrawer.value = true;
    };

    const deleteReply = (id) => {
      replies.value = replies.value.filter(r => r.id !== id);
      message.success('Predefined reply deleted');
    };

    const saveReply = () => {
      const plainText = form.name?.replace(/<[^>]*>/g, '').trim();
      if (!plainText) {
        message.error('Reply name required');
        return;
      }
      if (editingId.value) {
        const r = replies.value.find(x => x.id === editingId.value);
        if (r) r.name = form.name;
        message.success('Predefined reply updated');
      } else {
        replies.value.push({ id: Date.now(), name: form.name });
        message.success('Predefined reply added');
      }
      openDrawer.value = false;
      resetForm();
    };

    const resetForm = () => {
      editingId.value = null;
      form.name = '';
      if (quillRef.value) {
        quillRef.value.setContents([]);
      }
    };

    return {
      search, openDrawer, saving, editingId, quillRef,
      replies, form, editorOptions, columns, filteredReplies,
      editReply, deleteReply, saveReply, resetForm,
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
.reply-name { font-weight: 600; color: #1e293b; }
.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  margin-top: 16px;
}
</style>

<style>
.ql-toolbar.ql-snow {
  border-radius: 8px 8px 0 0;
  background: #f8fafc;
}
.ql-container.ql-snow {
  border-radius: 0 0 8px 8px;
}
.ql-editor {
  font-size: 14px;
  line-height: 1.6;
}
</style>
