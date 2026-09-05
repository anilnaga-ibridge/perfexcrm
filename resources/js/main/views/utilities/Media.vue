<template>
  <div class="p-4 md:p-6 space-y-6 max-w-[1600px] mx-auto min-h-screen bg-[#F8F7FA]">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <div class="flex items-center space-x-2">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#7367F0] to-[#9F8ED6]"></div>
          <h1 class="text-xl md:text-2xl font-bold text-[#4B465C] tracking-tight m-0">Media Library</h1>
        </div>
        <p class="text-xs text-[#A8AAAE] mt-1 pl-4.5 mb-0">Manage, organize, and preview your uploaded CRM files and attachments</p>
      </div>

      <div class="flex items-center gap-3">
        <!-- New Folder Button -->
        <button
          @click="showNewFolderModal = true"
          class="btn-outline px-4 py-2 text-xs font-bold flex items-center gap-2 shadow-2xs cursor-pointer"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
          <span>New Folder</span>
        </button>

        <!-- Upload Files Button -->
        <button
          @click="triggerFileUpload"
          class="btn-primary px-4 py-2 text-xs font-bold flex items-center gap-2 shadow-md cursor-pointer"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>Upload Files</span>
        </button>
        <input type="file" ref="fileInput" @change="handleFileSelected" style="display:none" multiple />
      </div>
    </div>

    <!-- Main Explorer Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start min-h-[620px]">
      <!-- Explorer Left Pane (Span 3 cols or 4 cols when no item selected) -->
      <div
        class="bg-white border border-[#EBE9F1] rounded-lg shadow-sm overflow-hidden flex flex-col transition-all"
        :class="selectedItem ? 'lg:col-span-3' : 'lg:col-span-4'"
      >
        <!-- Breadcrumbs & Search Toolbar -->
        <div class="p-4 border-b border-[#F1F0F2] flex flex-col sm:flex-row items-center justify-between gap-3 bg-[#F8F7FA]/70">
          <!-- Breadcrumbs Navigation -->
          <div class="flex items-center space-x-2 text-xs font-semibold overflow-x-auto w-full sm:w-auto py-1">
            <span
              class="text-[#7367F0] hover:underline cursor-pointer flex items-center gap-1"
              @click="navigateToFolder(null)"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              Root
            </span>
            <template v-for="b in breadcrumbs" :key="b.id">
              <span class="text-[#DBDADE]">/</span>
              <span
                class="text-[#7367F0] hover:underline cursor-pointer"
                @click="navigateToFolder(b.id)"
              >
                {{ b.name }}
              </span>
            </template>
          </div>

          <!-- Search Input -->
          <div class="relative w-full sm:w-64">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search current folder..."
              class="form-ctrl text-xs h-[34px] pl-8 pr-3 bg-white border-[#DBDADE] rounded-md transition-all w-full"
            />
            <div class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-[#A8AAAE]">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
          </div>
        </div>

        <!-- Dropzone / Items Canvas -->
        <div
          class="p-6 flex-1 min-h-[500px] overflow-y-auto transition-colors relative"
          :class="{ 'bg-[#7367F0]/5 border-2 border-dashed border-[#7367F0]': dragOver }"
          @dragover.prevent="dragOver = true"
          @dragleave.prevent="dragOver = false"
          @drop.prevent="handleFileDrop"
        >
          <!-- Uploading Spinner -->
          <div v-if="loadingFiles" class="flex flex-col items-center justify-center py-20 text-[#7367F0] space-y-3">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32" class="animate-spin"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            <span class="text-xs font-bold text-[#4B465C]">Uploading your files...</span>
          </div>

          <!-- Empty Canvas State -->
          <div
            v-else-if="filteredItems.length === 0"
            class="flex flex-col items-center justify-center py-24 text-[#A8AAAE] space-y-3"
          >
            <div class="w-16 h-16 rounded-full bg-[#F8F7FA] border border-[#EBE9F1] flex items-center justify-center text-[#A8AAAE]">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
            </div>
            <div class="text-center">
              <p class="text-xs font-bold text-[#4B465C] m-0">No media files in this folder</p>
              <p class="text-[11px] text-[#A8AAAE] m-0 mt-1">Drag and drop files here to upload, or click "Upload Files"</p>
            </div>
          </div>

          <!-- Items Grid -->
          <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-4">
            <div
              v-for="item in filteredItems"
              :key="item.id"
              class="p-3.5 bg-white border rounded-lg transition-all cursor-pointer flex flex-col items-center text-center space-y-2 group shadow-2xs hover:shadow-sm"
              :class="selectedItem && selectedItem.id === item.id ? 'border-[#7367F0] ring-2 ring-[#7367F0]/30 bg-[#7367F0]/5' : 'border-[#EBE9F1] hover:border-[#7367F0]/50 hover:bg-[#F8F7FA]'"
              @click="selectItem(item)"
              @dblclick="handleDoubleClick(item)"
            >
              <!-- Item Icon / Thumbnail -->
              <div class="w-16 h-16 rounded-lg flex items-center justify-center overflow-hidden transition-transform group-hover:scale-105">
                <!-- Folder Icon -->
                <div v-if="item.is_directory" class="w-12 h-12 rounded-lg bg-[#FF9F43]/10 text-[#FF9F43] flex items-center justify-center shadow-xs">
                  <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                </div>

                <!-- Image Thumbnail -->
                <img
                  v-else-if="isImage(item)"
                  :src="`/${item.file_path}`"
                  class="w-full h-full object-cover rounded-md border border-[#EBE9F1]"
                  loading="lazy"
                />

                <!-- Generic File Icon -->
                <div v-else class="w-12 h-12 rounded-lg bg-[#7367F0]/10 text-[#7367F0] flex items-center justify-center shadow-xs">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="26" height="26"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
              </div>

              <!-- Item Name & Size -->
              <div class="w-full">
                <p class="text-xs font-bold text-[#4B465C] truncate m-0 group-hover:text-[#7367F0] transition-colors" :title="item.name">
                  {{ item.name }}
                </p>
                <span class="text-[10px] text-[#A8AAAE] font-medium block mt-0.5">
                  {{ item.is_directory ? 'Folder' : formatSize(item.size) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Details Pane (Span 1 col) -->
      <div v-if="selectedItem" class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-[#F1F0F2]">
          <div class="flex items-center space-x-2 truncate">
            <span class="w-2 h-2 rounded-full bg-[#7367F0] flex-shrink-0"></span>
            <h3 class="text-xs font-bold text-[#4B465C] uppercase tracking-wider truncate m-0">
              File Details
            </h3>
          </div>
          <button
            @click="selectedItem = null"
            class="text-[#A8AAAE] hover:text-[#4B465C] cursor-pointer text-base font-bold"
          >
            &times;
          </button>
        </div>

        <!-- Preview -->
        <div class="space-y-3">
          <div v-if="!selectedItem.is_directory" class="w-full h-36 bg-[#F8F7FA] border border-[#EBE9F1] rounded-lg overflow-hidden flex items-center justify-center p-2">
            <img v-if="isImage(selectedItem)" :src="`/${selectedItem.file_path}`" class="max-h-full max-w-full object-contain rounded" />
            <div v-else class="text-[#A8AAAE] flex flex-col items-center">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
          </div>

          <div v-else class="w-full h-28 bg-[#FF9F43]/10 border border-[#FF9F43]/20 rounded-lg flex items-center justify-center text-[#FF9F43]">
            <svg viewBox="0 0 24 24" fill="currentColor" width="48" height="48"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
          </div>

          <h4 class="text-xs font-bold text-[#4B465C] break-all m-0">
            {{ selectedItem.name }}
          </h4>
        </div>

        <!-- Info Table -->
        <div class="space-y-2.5 pt-2 border-t border-[#F1F0F2] text-xs">
          <div class="flex items-center justify-between">
            <span class="text-[#A8AAAE] font-medium">Type</span>
            <span class="font-semibold text-[#4B465C] truncate max-w-[140px]">{{ selectedItem.is_directory ? 'Folder' : (selectedItem.mime_type || 'File') }}</span>
          </div>

          <div v-if="!selectedItem.is_directory" class="flex items-center justify-between">
            <span class="text-[#A8AAAE] font-medium">File Size</span>
            <span class="font-semibold text-[#4B465C]">{{ formatSize(selectedItem.size) }}</span>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-[#A8AAAE] font-medium">Created</span>
            <span class="font-semibold text-[#4B465C]">{{ formatDate(selectedItem.created_at) }}</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-2 pt-3 border-t border-[#F1F0F2]">
          <a
            v-if="!selectedItem.is_directory"
            :href="`/${selectedItem.file_path}`"
            target="_blank"
            download
            class="btn-primary w-full py-2 text-xs font-bold flex items-center justify-center gap-2 cursor-pointer shadow-sm text-center"
            style="text-decoration: none;"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>Download File</span>
          </a>

          <button
            @click="deleteItem(selectedItem.id)"
            class="w-full py-2 rounded-md border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            <span>Delete {{ selectedItem.is_directory ? 'Folder' : 'File' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Create Folder Modal -->
    <a-modal
      v-model:open="showNewFolderModal"
      title="Create New Folder"
      @ok="createFolder"
      ok-text="Create Folder"
      cancel-text="Cancel"
    >
      <div class="py-2 space-y-3">
        <label class="block text-xs font-semibold text-[#4B465C]">Folder Name <span class="text-rose-500">*</span></label>
        <input
          v-model="newFolderName"
          type="text"
          placeholder="e.g. Design Assets"
          class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
          @keydown.enter="createFolder"
        />
      </div>
    </a-modal>
  </div>
</template>

<script>
import { defineComponent, ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { message } from 'ant-design-vue'

export default defineComponent({
  name: 'MediaLibraryPage',
  setup() {
    const items = ref([])
    const breadcrumbs = ref([])
    const currentFolderId = ref(null)
    const searchQuery = ref('')
    const dragOver = ref(false)
    const loadingFiles = ref(false)
    const selectedItem = ref(null)

    const showNewFolderModal = ref(false)
    const newFolderName = ref('')
    const fileInput = ref(null)

    const loadMedia = async () => {
      try {
        const res = await axios.get('/api/media', {
          params: { parent_id: currentFolderId.value }
        })
        items.value = res.data.items || []
        breadcrumbs.value = res.data.breadcrumbs || []
      } catch (e) {
        message.error('Failed to load media files')
      }
    }

    const filteredItems = computed(() => {
      if (!searchQuery.value) return items.value
      const q = searchQuery.value.toLowerCase()
      return items.value.filter(item => item.name.toLowerCase().includes(q))
    })

    const navigateToFolder = (folderId) => {
      currentFolderId.value = folderId
      selectedItem.value = null
      loadMedia()
    }

    const handleDoubleClick = (item) => {
      if (item.is_directory) {
        navigateToFolder(item.id)
      }
    }

    const selectItem = (item) => {
      selectedItem.value = item
    }

    const triggerFileUpload = () => {
      fileInput.value.click()
    }

    const handleFileSelected = async (e) => {
      const files = e.target.files
      if (!files.length) return
      uploadFiles(files)
    }

    const handleFileDrop = (e) => {
      dragOver.value = false
      const files = e.dataTransfer.files
      if (!files.length) return
      uploadFiles(files)
    }

    const uploadFiles = async (files) => {
      loadingFiles.value = true
      try {
        for (let i = 0; i < files.length; i++) {
          const formData = new FormData()
          formData.append('file', files[i])
          if (currentFolderId.value) {
            formData.append('parent_id', currentFolderId.value)
          }
          await axios.post('/api/media', formData)
        }
        message.success('Files uploaded successfully')
        loadMedia()
      } catch (err) {
        message.error('Error uploading one or more files')
      } finally {
        loadingFiles.value = false
      }
    }

    const createFolder = async () => {
      if (!newFolderName.value) return message.warning('Please enter folder name')
      try {
        await axios.post('/api/media', {
          name: newFolderName.value,
          parent_id: currentFolderId.value
        })
        message.success('Folder created successfully')
        showNewFolderModal.value = false
        newFolderName.value = ''
        loadMedia()
      } catch {
        message.error('Failed to create folder')
      }
    }

    const deleteItem = async (id) => {
      try {
        await axios.delete(`/api/media/${id}`)
        message.success('Media item deleted successfully')
        selectedItem.value = null
        loadMedia()
      } catch {
        message.error('Failed to delete item')
      }
    }

    const isImage = (item) => {
      return item.mime_type && item.mime_type.startsWith('image/')
    }

    const formatSize = (bytes) => {
      if (bytes === 0) return '0 B'
      const k = 1024
      const sizes = ['B', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    }

    const formatDate = (dateStr) => {
      if (!dateStr) return '—'
      return new Date(dateStr).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    onMounted(() => {
      loadMedia()
    })

    return {
      items,
      breadcrumbs,
      searchQuery,
      dragOver,
      loadingFiles,
      selectedItem,
      showNewFolderModal,
      newFolderName,
      fileInput,
      filteredItems,
      navigateToFolder,
      handleDoubleClick,
      selectItem,
      triggerFileUpload,
      handleFileSelected,
      handleFileDrop,
      createFolder,
      deleteItem,
      isImage,
      formatSize,
      formatDate
    }
  }
})
</script>

<style scoped>
.form-ctrl {
  border: 1px solid #DBDADE;
  border-radius: 6px !important;
  color: #4B465C;
  font-family: inherit;
  outline: none;
}
.form-ctrl:focus {
  border-color: #7367F0 !important;
  box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.12) !important;
}

.btn-primary {
  background-color: #7367F0;
  color: #FFFFFF;
  border-radius: 6px !important;
  border: none;
  transition: all 0.2s ease-in-out;
}
.btn-primary:hover:not(:disabled) {
  background-color: #685DD8;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(115, 103, 240, 0.35);
}

.btn-outline {
  background-color: #FFFFFF;
  color: #6F6B7D;
  border: 1px solid #DBDADE;
  border-radius: 6px !important;
  transition: all 0.15s ease-in-out;
}
.btn-outline:hover:not(:disabled) {
  background-color: #F8F7FA;
  border-color: #C4C2C7;
  color: #4B465C;
}
</style>
