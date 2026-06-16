<template>
  <div class="media-page">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Media Library</h1>
        <p class="text-xs text-slate-500 mt-0.5">Manage and organize uploaded CRM attachments and documents</p>
      </div>
      <div class="header-actions">
        <button class="btn-outline" @click="showNewFolderModal = true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="inline mr-1"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
          New Folder
        </button>
        <button class="btn-primary" @click="triggerFileUpload">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Upload Files
        </button>
        <input type="file" ref="fileInput" @change="handleFileSelected" style="display:none" multiple />
      </div>
    </div>

    <!-- Layout Grid: Explorer on Left, Details Panel on Right -->
    <div class="layout-grid">
      <div class="explorer-pane">
        <!-- Breadcrumbs Navigation -->
        <div class="breadcrumbs-bar">
          <span class="breadcrumb-item" @click="navigateToFolder(null)">Root</span>
          <template v-for="b in breadcrumbs" :key="b.id">
            <span class="separator">/</span>
            <span class="breadcrumb-item" @click="navigateToFolder(b.id)">{{ b.name }}</span>
          </template>
        </div>

        <!-- Toolbar -->
        <div class="toolbar">
          <div class="search-box">
            <a-input-search
              v-model:value="searchQuery"
              placeholder="Search in current folder..."
              size="small"
              allowClear
            />
          </div>
        </div>

        <!-- Drag & Drop Upload Overlay / Dropzone -->
        <div 
          class="dropzone-area" 
          @dragover.prevent="dragOver = true" 
          @dragleave.prevent="dragOver = false" 
          @drop.prevent="handleFileDrop"
          :class="{ active: dragOver }"
        >
          <div class="upload-hint" v-if="loadingFiles">
            <a-spin />
            <span>Uploading your files...</span>
          </div>
          <div class="upload-hint" v-else-if="items.length === 0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48" class="text-slate-300">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
            </svg>
            <p class="text-sm font-semibold text-slate-400 mt-2">Drag and drop files here to upload, or click Upload Files</p>
          </div>

          <!-- Items Grid -->
          <div class="items-grid" v-else>
            <div 
              v-for="item in filteredItems" 
              :key="item.id" 
              class="grid-item"
              :class="{ selected: selectedItem && selectedItem.id === item.id }"
              @click="selectItem(item)"
              @dblclick="handleDoubleClick(item)"
            >
              <div class="item-icon-wrapper">
                <!-- Directory Icon -->
                <svg v-if="item.is_directory" viewBox="0 0 24 24" fill="#fef08a" stroke="#ca8a04" stroke-width="1.5" width="48" height="48">
                  <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                </svg>
                <!-- Image File Icon -->
                <div v-else-if="isImage(item)" class="image-thumbnail" :style="{ backgroundImage: `url(/${item.file_path})` }"></div>
                <!-- General File Icon -->
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.5" width="48" height="48">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                </svg>
              </div>
              <div class="item-meta">
                <span class="item-name" :title="item.name">{{ item.name }}</span>
                <span class="item-sub">{{ item.is_directory ? 'Folder' : formatSize(item.size) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Pane: Details Sidebar -->
      <div class="details-pane" v-if="selectedItem">
        <div class="details-header">
          <h3 class="text-sm font-bold text-slate-700 truncate" :title="selectedItem.name">{{ selectedItem.name }}</h3>
          <button class="close-btn" @click="selectedItem = null">&times;</button>
        </div>
        <div class="details-body">
          <div class="details-preview" v-if="!selectedItem.is_directory">
            <img v-if="isImage(selectedItem)" :src="`/${selectedItem.file_path}`" class="preview-img" />
            <div v-else class="preview-doc">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="64" height="64" class="text-slate-400">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
              </svg>
            </div>
          </div>

          <div class="details-info-table">
            <div class="info-row">
              <span class="info-lbl">Type</span>
              <span class="info-val">{{ selectedItem.is_directory ? 'Folder' : selectedItem.mime_type }}</span>
            </div>
            <div class="info-row" v-if="!selectedItem.is_directory">
              <span class="info-lbl">Size</span>
              <span class="info-val">{{ formatSize(selectedItem.size) }}</span>
            </div>
            <div class="info-row">
              <span class="info-lbl">Created At</span>
              <span class="info-val">{{ formatDate(selectedItem.created_at) }}</span>
            </div>
          </div>

          <div class="details-actions">
            <a 
              v-if="!selectedItem.is_directory" 
              :href="`/${selectedItem.file_path}`" 
              target="_blank" 
              download 
              class="btn-primary flex items-center justify-center w-100"
              style="text-decoration:none"
            >
              Download File
            </a>
            <button class="btn-danger w-100 mt-2" @click="deleteItem(selectedItem.id)">
              Delete {{ selectedItem.is_directory ? 'Folder' : 'File' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Folder Modal -->
    <a-modal
      v-model:open="showNewFolderModal"
      title="Create New Folder"
      @ok="createFolder"
      ok-text="Create"
      cancel-text="Cancel"
    >
      <a-form layout="vertical">
        <a-form-item label="Folder Name" :rules="[{required:true}]">
          <a-input v-model:value="newFolderName" placeholder="e.g. Design Assets" />
        </a-form-item>
      </a-form>
    </a-modal>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'MediaLibraryPage',
  setup() {
    const items = ref([]);
    const breadcrumbs = ref([]);
    const currentFolderId = ref(null);
    const searchQuery = ref('');
    const dragOver = ref(false);
    const loadingFiles = ref(false);
    const selectedItem = ref(null);

    const showNewFolderModal = ref(false);
    const newFolderName = ref('');
    const fileInput = ref(null);

    const loadMedia = async () => {
      try {
        const res = await axios.get('/api/media', {
          params: { parent_id: currentFolderId.value }
        });
        items.value = res.data.items || [];
        breadcrumbs.value = res.data.breadcrumbs || [];
      } catch (e) {
        message.error('Failed to load media files');
      }
    };

    const filteredItems = computed(() => {
      if (!searchQuery.value) return items.value;
      const q = searchQuery.value.toLowerCase();
      return items.value.filter(item => item.name.toLowerCase().includes(q));
    });

    const navigateToFolder = (folderId) => {
      currentFolderId.value = folderId;
      selectedItem.value = null;
      loadMedia();
    };

    const handleDoubleClick = (item) => {
      if (item.is_directory) {
        navigateToFolder(item.id);
      }
    };

    const selectItem = (item) => {
      selectedItem.value = item;
    };

    const triggerFileUpload = () => {
      fileInput.value.click();
    };

    const handleFileSelected = async (e) => {
      const files = e.target.files;
      if (!files.length) return;
      uploadFiles(files);
    };

    const handleFileDrop = (e) => {
      dragOver.value = false;
      const files = e.dataTransfer.files;
      if (!files.length) return;
      uploadFiles(files);
    };

    const uploadFiles = async (files) => {
      loadingFiles.value = true;
      try {
        for (let i = 0; i < files.length; i++) {
          const formData = new FormData();
          formData.append('file', files[i]);
          if (currentFolderId.value) {
            formData.append('parent_id', currentFolderId.value);
          }
          await axios.post('/api/media', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
          });
        }
        message.success('Files uploaded successfully');
        loadMedia();
      } catch (err) {
        message.error('Error uploading one or more files');
      } finally {
        loadingFiles.value = false;
      }
    };

    const createFolder = async () => {
      if (!newFolderName.value) return message.warning('Please enter folder name');
      try {
        await axios.post('/api/media', {
          name: newFolderName.value,
          parent_id: currentFolderId.value
        });
        message.success('Folder created successfully');
        showNewFolderModal.value = false;
        newFolderName.value = '';
        loadMedia();
      } catch {
        message.error('Failed to create folder');
      }
    };

    const deleteItem = async (id) => {
      try {
        await axios.delete(`/api/media/${id}`);
        message.success('Media item deleted');
        selectedItem.value = null;
        loadMedia();
      } catch {
        message.error('Failed to delete item');
      }
    };

    const isImage = (item) => {
      return item.mime_type && item.mime_type.startsWith('image/');
    };

    const formatSize = (bytes) => {
      if (bytes === 0) return '0 B';
      const k = 1024;
      const sizes = ['B', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };

    const formatDate = (dateStr) => {
      if (!dateStr) return '—';
      return new Date(dateStr).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    };

    onMounted(() => {
      loadMedia();
    });

    return {
      items, breadcrumbs, searchQuery, dragOver, loadingFiles, selectedItem,
      showNewFolderModal, newFolderName, fileInput, filteredItems,
      navigateToFolder, handleDoubleClick, selectItem, triggerFileUpload,
      handleFileSelected, handleFileDrop, createFolder, deleteItem,
      isImage, formatSize, formatDate
    };
  }
});
</script>

<style scoped>
.media-page {
  font-family: 'Inter', -apple-system, sans-serif;
  height: calc(100vh - 120px);
  display: flex;
  flex-direction: column;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}
.page-title {
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
.header-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background: #0d6efd;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 7px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-family: inherit;
}
.btn-primary:hover { background: #0b5ed7; }
.btn-outline {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 5px;
  padding: 6px 14px;
  font-size: 13px;
  color: #475569;
  cursor: pointer;
  font-family: inherit;
}
.btn-outline:hover { background: #f8fafc; }

/* Layout Grid */
.layout-grid {
  display: flex;
  flex: 1;
  border: 1px solid #e2e8f0;
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  height: 0; /* allows flex box to determine actual height */
}

/* Explorer Pane */
.explorer-pane {
  flex: 1;
  display: flex;
  flex-direction: column;
  border-right: 1px solid #e2e8f0;
}
.breadcrumbs-bar {
  padding: 10px 16px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  font-size: 13px;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 6px;
}
.breadcrumb-item {
  color: #0d6efd;
  font-weight: 600;
  cursor: pointer;
}
.breadcrumb-item:hover {
  text-decoration: underline;
}
.separator {
  color: #cbd5e1;
}

.toolbar {
  padding: 10px 16px;
  border-bottom: 1px solid #f1f5f9;
}
.search-box {
  max-width: 300px;
}

.dropzone-area {
  flex: 1;
  padding: 16px;
  overflow-y: auto;
  position: relative;
  transition: background 0.15s;
}
.dropzone-area.active {
  background: #f0fdf4;
}

.upload-hint {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #94a3b8;
  gap: 12px;
}

/* Items Grid */
.items-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 16px;
}
.grid-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 10px;
  border-radius: 6px;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.12s;
  text-align: center;
}
.grid-item:hover {
  background: #f8fafc;
  border-color: #e2e8f0;
}
.grid-item.selected {
  background: #eff6ff;
  border-color: #bfdbfe;
}
.item-icon-wrapper {
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 8px;
}
.image-thumbnail {
  width: 52px;
  height: 52px;
  border-radius: 4px;
  background-size: cover;
  background-position: center;
  border: 1px solid #e2e8f0;
}

.item-meta {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.item-name {
  font-size: 12px;
  font-weight: 500;
  color: #334155;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.item-sub {
  font-size: 10px;
  color: #94a3b8;
}

/* Details Pane */
.details-pane {
  width: 280px;
  display: flex;
  flex-direction: column;
  background: #f8fafc;
}
.details-header {
  padding: 12px 16px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.close-btn {
  background: none;
  border: none;
  font-size: 18px;
  color: #94a3b8;
  cursor: pointer;
}
.close-btn:hover { color: #64748b; }

.details-body {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  overflow-y: auto;
}
.details-preview {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  overflow: hidden;
  height: 150px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.preview-img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}
.preview-doc {
  color: #94a3b8;
}

.details-info-table {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.info-row {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
}
.info-lbl {
  color: #94a3b8;
}
.info-val {
  color: #475569;
  font-weight: 500;
  word-break: break-all;
  max-width: 150px;
  text-align: right;
}

.details-actions {
  margin-top: auto;
}
.w-100 { width: 100%; }
.btn-danger {
  background: #fecaca;
  color: #b91c1c;
  border: 1px solid #fca5a5;
  border-radius: 5px;
  padding: 7px 16px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}
.btn-danger:hover {
  background: #fee2e2;
}
</style>
