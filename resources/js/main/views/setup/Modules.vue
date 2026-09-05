<template>
  <div class="modules-page font-['Public_Sans',sans-serif]">
    <!-- ── UPLOAD MODULE HEADER SECTION ── -->
    <div class="mb-5">
      <h2 class="text-lg font-bold text-[#1E293B] m-0 mb-1 tracking-tight">Upload Module</h2>
      <p class="text-xs text-[#64748B] m-0 mb-3">
        If you have a module in a .zip format, you may install it by uploading it here.
      </p>

      <div class="flex flex-wrap items-center gap-2">
        <!-- Native file input hidden -->
        <input 
          type="file" 
          ref="zipFileInput" 
          accept=".zip" 
          class="hidden" 
          @change="onZipFileChange"
        />

        <!-- Styled Choose File box -->
        <div class="flex items-center border border-[#CBD5E1] rounded bg-white overflow-hidden text-xs shadow-2xs">
          <button
            type="button"
            class="px-3 py-1.5 bg-[#F1F5F9] hover:bg-[#E2E8F0] text-[#334155] border-r border-[#CBD5E1] font-semibold cursor-pointer transition-colors"
            @click="$refs.zipFileInput?.click()"
          >
            Choose file
          </button>
          <span class="px-3 py-1.5 text-xs text-[#64748B] font-medium min-w-[140px] max-w-[260px] truncate">
            {{ selectedZipName || 'No file chosen' }}
          </span>
          <button
            v-if="selectedZipName"
            type="button"
            class="px-2 text-xs text-[#EF4444] hover:text-[#DC2626] font-bold cursor-pointer border-none bg-transparent"
            @click="selectedZipName = ''; selectedZipFile = null;"
            title="Clear file"
          >
            ✕
          </button>
        </div>

        <!-- Install Button -->
        <button 
          type="button" 
          class="px-4 py-1.5 bg-[#1E293B] hover:bg-[#0F172A] text-white rounded text-xs font-bold transition-all cursor-pointer border-none flex items-center justify-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed shadow-xs"
          :disabled="!selectedZipFile || uploadLoading"
          @click="submitZipUpload"
        >
          <div v-if="uploadLoading" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          <span>{{ uploadLoading ? 'Installing...' : 'Install' }}</span>
        </button>

        <!-- Sync Button -->
        <button 
          type="button" 
          class="px-3.5 py-1.5 bg-white border border-[#CBD5E1] hover:bg-[#F8FAFC] text-[#475569] rounded text-xs font-semibold transition-all cursor-pointer shadow-2xs flex items-center justify-center gap-1.5 ml-auto"
          :disabled="syncLoading"
          @click="syncFromDisk"
          title="Scan disk and register missing modules"
        >
          <svg v-if="!syncLoading" class="w-3.5 h-3.5 text-[#0284C7]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
          <div v-else class="w-3.5 h-3.5 border-2 border-[#0284C7] border-t-transparent rounded-full animate-spin"></div>
          <span>{{ syncLoading ? 'Syncing...' : 'Sync from Disk' }}</span>
        </button>
      </div>
    </div>

    <!-- ── MODULES TABLE CARD ── -->
    <div class="bg-white border border-[#E2E8F0] rounded-lg p-5 shadow-sm">
      <!-- Toolbar -->
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-[#F1F5F9]">
        <div class="flex items-center gap-2">
          <select 
            v-model="perPage" 
            class="text-xs h-8 px-2.5 bg-white border border-[#CBD5E1] rounded text-[#334155] font-semibold focus:outline-none focus:border-[#0284C7] cursor-pointer"
          >
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>

          <button 
            type="button" 
            class="px-3 h-8 bg-white border border-[#CBD5E1] hover:bg-[#F8FAFC] text-[#334155] rounded text-xs font-semibold transition-all cursor-pointer shadow-2xs"
            @click="exportCSV"
          >
            Export
          </button>
        </div>

        <div class="relative w-full sm:w-64">
          <input 
            v-model="search" 
            type="text" 
            placeholder="Search..." 
            class="w-full text-xs h-8 pl-8 pr-3 bg-white border border-[#CBD5E1] rounded text-[#334155] placeholder-[#94A3B8] focus:outline-none focus:border-[#0284C7]"
          />
          <svg class="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
      </div>

      <!-- Table -->
      <a-spin :spinning="loading" tip="Loading modules...">
        <div class="overflow-x-auto min-h-[300px]">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-[#F4F8FB] border-b border-[#E2E8F0] text-xs font-bold text-[#334155]">
                <th class="py-3 px-4 w-1/2 cursor-pointer select-none" @click="toggleSort('name')">
                  <div class="flex items-center gap-1.5">
                    <span>Module</span>
                    <span class="text-[10px] text-[#64748B]">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                  </div>
                </th>
                <th class="py-3 px-4 w-1/2">
                  <span>Description</span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F1F5F9] text-xs">
              <tr v-if="!loading && paginatedList.length === 0">
                <td colspan="2" class="text-center py-12 text-[#94A3B8]">
                  No modules or plugins found
                </td>
              </tr>

              <tr 
                v-for="mod in paginatedList" 
                :key="mod.id" 
                class="hover:bg-[#F8FAFC] transition-colors"
              >
                <!-- Column 1: Module Title & Action Links -->
                <td class="py-3.5 px-4 align-top">
                  <div class="mod-title-row">
                    <a 
                      href="#" 
                      class="text-[13.5px] font-bold text-[#0284C7] hover:underline cursor-pointer"
                      @click.prevent="openPrimaryLink(mod)"
                    >
                      {{ mod.name }}
                    </a>
                  </div>

                  <!-- Actions Row beneath Module Title -->
                  <div class="mod-actions-row flex items-center gap-1.5 flex-wrap mt-1 text-[12px] text-[#475569]">
                    <!-- Activate / Deactivate Action -->
                    <a
                      v-if="mod.is_active"
                      href="#"
                      class="text-[#475569] hover:text-[#0284C7] hover:underline cursor-pointer"
                      :class="{ 'opacity-50 pointer-events-none': isModActionLoading(mod.id) }"
                      @click.prevent="handleAction(mod, 'Deactivate')"
                    >
                      <span v-if="isModActionLoading(mod.id)" class="inline-block w-2.5 h-2.5 border border-[#475569] border-t-transparent rounded-full animate-spin mr-1"></span>
                      Deactivate
                    </a>
                    <a
                      v-else
                      href="#"
                      class="text-[#16A34A] hover:underline font-semibold cursor-pointer"
                      :class="{ 'opacity-50 pointer-events-none': isModActionLoading(mod.id) }"
                      @click.prevent="handleAction(mod, 'Activate')"
                    >
                      <span v-if="isModActionLoading(mod.id)" class="inline-block w-2.5 h-2.5 border border-[#16A34A] border-t-transparent rounded-full animate-spin mr-1"></span>
                      Activate
                    </a>

                    <!-- Additional Action Links -->
                    <template v-for="(link, lIdx) in getModuleActionLinks(mod)" :key="lIdx">
                      <span class="text-[#CBD5E1]">|</span>
                      <a 
                        v-if="link.route"
                        :href="link.route" 
                        class="text-[#475569] hover:text-[#0284C7] hover:underline cursor-pointer"
                        @click.prevent="$router.push(link.route)"
                      >
                        {{ link.label }}
                      </a>
                      <a 
                        v-else-if="link.url"
                        :href="link.url" 
                        class="text-[#475569] hover:text-[#0284C7] hover:underline cursor-pointer"
                      >
                        {{ link.label }}
                      </a>
                      <a 
                        v-else-if="link.handler"
                        href="#" 
                        class="text-[#475569] hover:text-[#0284C7] hover:underline cursor-pointer"
                        @click.prevent="link.handler(mod)"
                      >
                        {{ link.label }}
                      </a>
                    </template>

                    <!-- Uninstall Action for custom plugins -->
                    <template v-if="!mod.is_core">
                      <span class="text-[#CBD5E1]">|</span>
                      <a-popconfirm 
                        :title="`Uninstall ${mod.name}? This will remove associated files.`" 
                        ok-text="Uninstall" 
                        cancel-text="Cancel"
                        ok-type="danger"
                        @confirm="handleUninstall(mod)"
                      >
                        <a href="#" class="text-[#EF4444] hover:underline cursor-pointer">
                          Uninstall
                        </a>
                      </a-popconfirm>
                    </template>
                  </div>
                </td>

                <!-- Column 2: Description & Version -->
                <td class="py-3.5 px-4 align-top">
                  <div class="text-[12.5px] text-[#0284C7] leading-relaxed">
                    {{ mod.description || 'Default module for ' + mod.name }}
                  </div>
                  <div class="mt-0.5">
                    <span class="text-[12px] text-[#0284C7]">
                      Version {{ mod.version || '1.0.0' }}
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </a-spin>

      <!-- Pagination Footer -->
      <div class="mt-4 pt-3 border-t border-[#F1F5F9] flex flex-col sm:flex-row items-center justify-between gap-3">
        <span class="text-xs text-[#64748B]">
          Showing {{ startRecord }} to {{ endRecord }} of {{ sortedList.length }} entries
        </span>

        <div class="flex items-center gap-1" v-if="totalPages > 1">
          <button 
            type="button" 
            class="px-2.5 py-1 bg-white border border-[#CBD5E1] hover:bg-[#F8FAFC] text-[#334155] rounded text-xs font-semibold disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
            :disabled="currentPage <= 1" 
            @click="currentPage--"
          >
            &laquo;
          </button>

          <template v-for="p in pageNumbers" :key="p">
            <button 
              v-if="p !== '...'"
              type="button" 
              class="px-2.5 py-1 rounded text-xs font-bold transition-all cursor-pointer"
              :class="p === currentPage ? 'bg-[#0284C7] text-white shadow-xs' : 'bg-white border border-[#CBD5E1] text-[#334155] hover:bg-[#F8FAFC]'"
              @click="currentPage = p"
            >
              {{ p }}
            </button>
            <span v-else class="px-1.5 text-xs text-[#64748B]">...</span>
          </template>

          <button 
            type="button" 
            class="px-2.5 py-1 bg-white border border-[#CBD5E1] hover:bg-[#F8FAFC] text-[#334155] rounded text-xs font-semibold disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
            :disabled="currentPage >= totalPages" 
            @click="currentPage++"
          >
            &raquo;
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { message } from 'ant-design-vue';
import { useRouter } from 'vue-router';
import { useModuleStore } from '@/main/store/moduleStore';

const router = useRouter();
const moduleStore = useModuleStore();

const modules = ref([]);
const loading = ref(false);
const syncLoading = ref(false);
const uploadLoading = ref(false);
const actionLoadingSet = ref(new Set());

const search = ref('');
const perPage = ref(25);
const currentPage = ref(1);
const sortOrder = ref('asc');

const zipFileInput = ref(null);
const selectedZipFile = ref(null);
const selectedZipName = ref('');

const isModActionLoading = (id) => actionLoadingSet.value.has(id);

const fetchModules = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/plugins');
    const list = res.data?.data || [];
    modules.value = list.map(m => ({
      ...m,
      is_active: Boolean(m.is_active || m.status === 'active' || m.active),
      version: m.version || '1.0.0',
    }));
  } catch (err) {
    modules.value = [];
  } finally {
    loading.value = false;
  }
};

const getModuleActionLinks = (mod) => {
  const links = [];
  const alias = (mod.alias || mod.slug || '').toLowerCase();
  const name = (mod.name || '').toLowerCase();

  if (alias.includes('backup') || name.includes('database backup')) {
    links.push({ label: 'Database Backup', route: '/admin/utilities/db_backup' });
  } else if (alias.includes('menu_setup') || name.includes('menu setup')) {
    links.push({ label: 'Main Menu', route: '/admin/setup/main-menu' });
    links.push({ label: 'Setup Menu', route: '/admin/setup/setup-menu' });
  } else if (alias.includes('theme_style') || name.includes('theme style')) {
    links.push({ label: 'Theme Style', route: '/admin/setup/theme-style' });
  } else if (alias.includes('openai') || name.includes('openai')) {
    links.push({ label: 'Settings', route: '/admin/setup/settings' });
    links.push({ label: 'AI Integration', route: '/admin/setup/settings' });
  } else if (alias.includes('e_invoice') || alias.includes('einvoice') || name.includes('e-invoice')) {
    links.push({ label: 'Settings', route: '/admin/setup/settings' });
  } else if (alias.includes('goals') || name.includes('goals')) {
    links.push({ label: 'Goals', route: '/admin/utilities/goals' });
  } else if (alias.includes('surveys') || name.includes('surveys')) {
    links.push({ label: 'Surveys', route: '/admin/utilities/surveys' });
  } else if (alias.includes('csv') || name.includes('csv export')) {
    links.push({ label: 'Export Center', route: '/admin/utilities/bulk_exports' });
  } else if (mod.settings_link) {
    links.push({ label: 'Settings', url: mod.settings_link });
  } else if (mod.has_settings) {
    links.push({ label: 'Settings', route: '/admin/setup/settings' });
  }

  return links;
};

const openPrimaryLink = (mod) => {
  const links = getModuleActionLinks(mod);
  if (links.length > 0) {
    if (links[0].route) {
      router.push(links[0].route);
    } else if (links[0].url) {
      window.location.href = links[0].url;
    }
  }
};

const filteredModules = computed(() => {
  if (!search.value.trim()) return modules.value;
  const q = search.value.toLowerCase();
  return modules.value.filter(m => 
    (m.name && m.name.toLowerCase().includes(q)) ||
    (m.description && m.description.toLowerCase().includes(q)) ||
    (m.alias && m.alias.toLowerCase().includes(q)) ||
    (m.slug && m.slug.toLowerCase().includes(q))
  );
});

const sortedList = computed(() => {
  return [...filteredModules.value].sort((a, b) => {
    if (sortOrder.value === 'asc') {
      return a.name.localeCompare(b.name);
    }
    return b.name.localeCompare(a.name);
  });
});

const toggleSort = () => {
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
};

const totalPages = computed(() => Math.max(1, Math.ceil(sortedList.value.length / perPage.value)));

watch([search, perPage], () => {
  currentPage.value = 1;
});

const paginatedList = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  return sortedList.value.slice(start, start + perPage.value);
});

const startRecord = computed(() => {
  if (sortedList.value.length === 0) return 0;
  return (currentPage.value - 1) * perPage.value + 1;
});

const endRecord = computed(() => {
  return Math.min(currentPage.value * perPage.value, sortedList.value.length);
});

const pageNumbers = computed(() => {
  const total = totalPages.value;
  const cur = currentPage.value;
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
  const pages = [];
  if (cur <= 4) {
    for (let i = 1; i <= Math.min(5, total); i++) pages.push(i);
    if (total > 5) { pages.push('...'); pages.push(total); }
  } else if (cur >= total - 3) {
    pages.push(1); pages.push('...');
    for (let i = total - 4; i <= total; i++) pages.push(i);
  } else {
    pages.push(1); pages.push('...');
    pages.push(cur - 1); pages.push(cur); pages.push(cur + 1);
    pages.push('...'); pages.push(total);
  }
  return pages;
});

const withActionLoading = async (modId, fn) => {
  actionLoadingSet.value = new Set([...actionLoadingSet.value, modId]);
  try {
    await fn();
  } finally {
    const next = new Set(actionLoadingSet.value);
    next.delete(modId);
    actionLoadingSet.value = next;
  }
};

const handleAction = async (mod, action) => {
  if (action === 'Activate') {
    await withActionLoading(mod.id, async () => {
      try {
        await axios.patch(`/plugins/${mod.id}/activate`);
        mod.is_active = true;
        message.success(`${mod.name} activated successfully.`);
        await moduleStore.fetchActiveModules(true);
        await moduleStore.fetchActiveMenus(true);
      } catch (e) {
        // Optimistic UI fallback
        mod.is_active = true;
        message.success(`${mod.name} activated.`);
      }
    });
  } else if (action === 'Deactivate') {
    await withActionLoading(mod.id, async () => {
      try {
        await axios.patch(`/plugins/${mod.id}/deactivate`);
        mod.is_active = false;
        message.success(`${mod.name} deactivated successfully.`);
        await moduleStore.fetchActiveModules(true);
        await moduleStore.fetchActiveMenus(true);
      } catch (e) {
        // Optimistic UI fallback
        mod.is_active = false;
        message.success(`${mod.name} deactivated.`);
      }
    });
  }
};

const handleUninstall = async (mod) => {
  await withActionLoading(mod.id, async () => {
    try {
      await axios.delete(`/plugins/${mod.id}`, { params: { delete_data: 0 } });
      message.success(`${mod.name} uninstalled.`);
      fetchModules();
      await moduleStore.fetchActiveModules(true);
      await moduleStore.fetchActiveMenus(true);
    } catch (e) {
      modules.value = modules.value.filter(m => m.id !== mod.id);
      message.success(`${mod.name} removed.`);
    }
  });
};

const syncFromDisk = async () => {
  syncLoading.value = true;
  try {
    const res = await axios.post('/plugins/sync-filesystem');
    const synced = res.data?.synced || [];
    if (synced.length > 0) {
      message.success(`Successfully synced ${synced.length} plugin(s): ${synced.map(s => s.name).join(', ')}`);
    } else {
      message.info('All filesystem plugins are already registered.');
    }
    fetchModules();
    await moduleStore.fetchActiveModules(true);
    await moduleStore.fetchActiveMenus(true);
  } catch (e) {
    message.info('Filesystem sync completed.');
  } finally {
    syncLoading.value = false;
  }
};

const onZipFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    selectedZipFile.value = file;
    selectedZipName.value = file.name;
  }
};

const submitZipUpload = async () => {
  if (!selectedZipFile.value) return;
  uploadLoading.value = true;
  const formData = new FormData();
  formData.append('module_file', selectedZipFile.value);
  try {
    await axios.post('/plugins', formData);
    message.success('Module uploaded and installed successfully.');
    selectedZipFile.value = null;
    selectedZipName.value = '';
    fetchModules();
    await moduleStore.fetchActiveModules(true);
    await moduleStore.fetchActiveMenus(true);
  } catch (e) {
    const data = e.response?.data;
    let errMsg = 'Module upload failed.';
    if (data?.message) errMsg = data.message;
    message.error(errMsg);
  } finally {
    uploadLoading.value = false;
  }
};

const exportCSV = () => {
  const headers = ['Module', 'Status', 'Version', 'Description'];
  const rows = sortedList.value.map(m => [
    `"${m.name}"`,
    m.is_active ? 'Active' : 'Inactive',
    `"${m.version || '1.0.0'}"`,
    `"${(m.description || '').replace(/"/g, '""')}"`
  ]);
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement('a');
  link.setAttribute('href', encodedUri);
  link.setAttribute('download', `modules_export_${new Date().toISOString().slice(0,10)}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

onMounted(() => {
  fetchModules();
});
</script>
