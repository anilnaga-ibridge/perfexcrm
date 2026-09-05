<template>
  <div class="vuexy-page-container">
    <!-- Breadcrumb & Page Header -->
    <div class="vuexy-page-header flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="text-xl font-bold text-[#4B465C] m-0 dark:text-[#E6E5E8]">Data Tables</h2>
        <div class="flex items-center gap-2 text-xs text-[#A8AAAE] mt-1">
          <router-link to="/admin/dashboard" class="hover:text-[#7367F0]">Dashboard</router-link>
          <span>/</span>
          <span>Tables</span>
          <span>/</span>
          <span class="text-[#4B465C] dark:text-[#CFCCE4] font-medium">Data Table Template</span>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <button 
          class="vuexy-btn-primary flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg shadow-sm border-0 cursor-pointer"
          :style="{ backgroundColor: themeStore.primaryColor, color: '#fff' }"
          @click="showCodeModal = true"
        >
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
          <span>View Template Code</span>
        </button>
      </div>
    </div>

    <!-- Quick Stats Bar (Optional metric cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="vuexy-card p-4">
        <div class="flex items-center justify-between">
          <div>
            <span class="text-xs text-[#A8AAAE] font-medium block">Total Employees</span>
            <span class="text-xl font-bold text-[#4B465C] dark:text-[#E6E5E8] mt-1 block">{{ totalRecords }}</span>
          </div>
          <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-[rgba(115,103,240,0.12)] text-[#7367F0]">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
        </div>
      </div>
      <div class="vuexy-card p-4">
        <div class="flex items-center justify-between">
          <div>
            <span class="text-xs text-[#A8AAAE] font-medium block">Selected Rows</span>
            <span class="text-xl font-bold text-[#7367F0] mt-1 block">{{ selectedRowKeys.length }}</span>
          </div>
          <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-[rgba(40,199,111,0.12)] text-[#28C76F]">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          </div>
        </div>
      </div>
      <div class="vuexy-card p-4">
        <div class="flex items-center justify-between">
          <div>
            <span class="text-xs text-[#A8AAAE] font-medium block">Active (Current)</span>
            <span class="text-xl font-bold text-[#28C76F] mt-1 block">{{ rows.filter(r => r.status === 'Current' || r.status === 'Professional').length }}</span>
          </div>
          <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-[rgba(0,207,232,0.12)] text-[#00CFE8]">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
          </div>
        </div>
      </div>
      <div class="vuexy-card p-4">
        <div class="flex items-center justify-between">
          <div>
            <span class="text-xs text-[#A8AAAE] font-medium block">Average Salary</span>
            <span class="text-xl font-bold text-[#FF9F43] mt-1 block">$18,945</span>
          </div>
          <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-[rgba(255,159,67,0.12)] text-[#FF9F43]">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          </div>
        </div>
      </div>
    </div>

    <!-- MAIN VUEXY TABLE CARD: ROW SELECTION -->
    <div class="vuexy-card vuexy-table-card overflow-hidden">
      <!-- Card Header -->
      <div class="vuexy-table-card__header flex items-center justify-between p-5 border-b border-[#F1F0F2] dark:border-[#3B4056]">
        <div>
          <h3 class="text-base font-semibold text-[#4B465C] dark:text-[#E6E5E8] m-0">Row Selection</h3>
        </div>
        <div class="flex items-center gap-2">
          <!-- Filter Search Bar -->
          <div class="relative">
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Search..." 
              class="vuexy-table-search h-9 pl-9 pr-3 text-xs bg-white dark:bg-[#2F3349] border border-[#DBDADE] dark:border-[#4B465C] rounded-lg text-[#4B465C] dark:text-[#E6E5E8] focus:outline-none focus:border-[#7367F0]"
            />
            <svg class="absolute left-3 top-2.5 text-[#A8AAAE]" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </div>

          <!-- Code View Toggle -->
          <button 
            class="vuexy-card__action-btn p-2 rounded-lg text-[#A8AAAE] hover:text-[#7367F0] hover:bg-[#F1F0F2] dark:hover:bg-[#3B4056] border-0 bg-transparent cursor-pointer transition-colors"
            title="View Code"
            @click="showCodeModal = true"
          >
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
          </button>
        </div>
      </div>

      <!-- Table Element Responsive Wrapper -->
      <div class="vuexy-table-responsive overflow-x-auto">
        <table class="vuexy-table w-full border-collapse text-left">
          <thead>
            <tr class="border-b border-[#F1F0F2] dark:border-[#3B4056]">
              <!-- Checkbox Header -->
              <th class="vuexy-table__th vuexy-table__th--checkbox w-12 px-4 py-3.5">
                <input 
                  type="checkbox" 
                  class="vuexy-checkbox"
                  :checked="isAllSelected"
                  :indeterminate.prop="isIndeterminate"
                  @change="toggleSelectAll"
                />
              </th>
              <th class="vuexy-table__th px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-[#6F6B7D] dark:text-[#A8AAAE]">NAME</th>
              <th class="vuexy-table__th px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-[#6F6B7D] dark:text-[#A8AAAE]">EMAIL</th>
              <th class="vuexy-table__th px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-[#6F6B7D] dark:text-[#A8AAAE]">DATE</th>
              <th class="vuexy-table__th px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-[#6F6B7D] dark:text-[#A8AAAE]">SALARY</th>
              <th class="vuexy-table__th px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-[#6F6B7D] dark:text-[#A8AAAE]">AGE</th>
              <th class="vuexy-table__th px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-[#6F6B7D] dark:text-[#A8AAAE]">STATUS</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#F1F0F2] dark:divide-[#3B4056]">
            <tr 
              v-for="row in paginatedRows" 
              :key="row.id"
              :class="['vuexy-table__row transition-colors hover:bg-[#F8F7FA] dark:hover:bg-[#34384E]', { 'bg-[#F4F3FB] dark:bg-[#383262]': selectedRowKeys.includes(row.id) }]"
            >
              <!-- Checkbox Cell -->
              <td class="vuexy-table__td px-4 py-3.5 w-12">
                <input 
                  type="checkbox" 
                  class="vuexy-checkbox"
                  :checked="selectedRowKeys.includes(row.id)"
                  @change="toggleSelectRow(row.id)"
                />
              </td>

              <!-- Name & Avatar Column -->
              <td class="vuexy-table__td px-4 py-3.5">
                <div class="flex items-center gap-3">
                  <!-- Image Avatar or Initials Avatar Badge -->
                  <div v-if="row.avatar" class="vuexy-avatar w-8 h-8 rounded-full overflow-hidden shrink-0 border border-[#DBDADE] dark:border-[#4B465C]">
                    <img :src="row.avatar" :alt="row.name" class="w-full h-full object-cover" />
                  </div>
                  <div 
                    v-else 
                    class="vuexy-avatar-initials w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shrink-0"
                    :style="{ backgroundColor: row.initialsBg, color: row.initialsColor }"
                  >
                    {{ row.initials }}
                  </div>

                  <!-- Text Stack -->
                  <div class="flex flex-col">
                    <span class="text-sm font-semibold text-[#4B465C] dark:text-[#E6E5E8] leading-tight">{{ row.name }}</span>
                    <span class="text-xs text-[#A8AAAE] mt-0.5">{{ row.role }}</span>
                  </div>
                </div>
              </td>

              <!-- Email Column -->
              <td class="vuexy-table__td px-4 py-3.5 text-xs text-[#6F6B7D] dark:text-[#B6B1D0]">
                {{ row.email }}
              </td>

              <!-- Date Column -->
              <td class="vuexy-table__td px-4 py-3.5 text-xs text-[#6F6B7D] dark:text-[#B6B1D0]">
                {{ row.date }}
              </td>

              <!-- Salary Column -->
              <td class="vuexy-table__td px-4 py-3.5 text-xs text-[#6F6B7D] dark:text-[#B6B1D0]">
                {{ row.salary }}
              </td>

              <!-- Age Column -->
              <td class="vuexy-table__td px-4 py-3.5 text-xs text-[#6F6B7D] dark:text-[#B6B1D0]">
                {{ row.age }}
              </td>

              <!-- Status Pill Badge Column -->
              <td class="vuexy-table__td px-4 py-3.5">
                <span :class="['vuexy-badge-pill', statusBadgeClass(row.status)]">
                  {{ row.status }}
                </span>
              </td>
            </tr>

            <!-- Empty state if no records match -->
            <tr v-if="paginatedRows.length === 0">
              <td colspan="7" class="text-center py-8 text-xs text-[#A8AAAE]">
                No matching records found
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Table Footer / Pagination Controls -->
      <div class="vuexy-table-card__footer flex flex-col sm:flex-row items-center justify-end gap-4 p-4 border-t border-[#F1F0F2] dark:border-[#3B4056] text-xs text-[#6F6B7D] dark:text-[#A8AAAE]">
        <!-- Items per page dropdown -->
        <div class="flex items-center gap-2">
          <span>Items per page:</span>
          <select 
            v-model="pageSize" 
            class="vuexy-select-page-size h-8 px-2.5 text-xs bg-white dark:bg-[#2F3349] border border-[#DBDADE] dark:border-[#4B465C] rounded-md text-[#4B465C] dark:text-[#E6E5E8] focus:outline-none focus:border-[#7367F0] cursor-pointer"
          >
            <option :value="5">5</option>
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
        </div>

        <!-- Range indicator -->
        <div class="text-xs font-medium text-[#6F6B7D] dark:text-[#A8AAAE]">
          {{ startRecordIndex }}-{{ endRecordIndex }} of {{ filteredRows.length }}
        </div>

        <!-- Pagination Arrows: First, Prev, Next, Last -->
        <div class="flex items-center gap-1">
          <!-- First Page -->
          <button 
            class="vuexy-page-nav-btn w-8 h-8 rounded-md flex items-center justify-center border-0 bg-transparent text-[#6F6B7D] dark:text-[#A8AAAE] hover:bg-[#F1F0F2] dark:hover:bg-[#3B4056] disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer transition-colors"
            :disabled="currentPage === 1"
            @click="currentPage = 1"
            title="First Page"
          >
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="11 17 6 12 11 7"/><polyline points="18 17 13 12 18 7"/><line x1="6" y1="6" x2="6" y2="18"/></svg>
          </button>

          <!-- Prev Page -->
          <button 
            class="vuexy-page-nav-btn w-8 h-8 rounded-md flex items-center justify-center border-0 bg-transparent text-[#6F6B7D] dark:text-[#A8AAAE] hover:bg-[#F1F0F2] dark:hover:bg-[#3B4056] disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer transition-colors"
            :disabled="currentPage === 1"
            @click="currentPage--"
            title="Previous Page"
          >
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          </button>

          <!-- Next Page -->
          <button 
            class="vuexy-page-nav-btn w-8 h-8 rounded-md flex items-center justify-center border-0 bg-transparent text-[#6F6B7D] dark:text-[#A8AAAE] hover:bg-[#F1F0F2] dark:hover:bg-[#3B4056] disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer transition-colors"
            :disabled="currentPage === totalPages"
            @click="currentPage++"
            title="Next Page"
          >
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          </button>

          <!-- Last Page -->
          <button 
            class="vuexy-page-nav-btn w-8 h-8 rounded-md flex items-center justify-center border-0 bg-transparent text-[#6F6B7D] dark:text-[#A8AAAE] hover:bg-[#F1F0F2] dark:hover:bg-[#3B4056] disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer transition-colors"
            :disabled="currentPage === totalPages"
            @click="currentPage = totalPages"
            title="Last Page"
          >
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="13 17 18 12 13 6"/><polyline points="6 17 11 12 6 6"/><line x1="18" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- CODE SNIPPET MODAL -->
    <a-modal
      v-model:visible="showCodeModal"
      title="Vuexy Data Table Template Code (Vue 3 / HTML)"
      width="750px"
      :footer="null"
    >
      <div class="space-y-4">
        <p class="text-xs text-[#6F6B7D]">Copy and paste this reusable snippet to add a Vuexy Row Selection table anywhere in the CRM:</p>
        <pre class="p-4 bg-[#282C34] text-[#ABB2BF] rounded-xl text-xs overflow-x-auto font-mono max-h-[450px]"><code>&lt;!-- Vuexy Row Selection Data Table Template --&gt;
&lt;div class="vuexy-card vuexy-table-card"&gt;
  &lt;div class="vuexy-table-card__header flex items-center justify-between p-5 border-b"&gt;
    &lt;h3 class="text-base font-semibold"&gt;Row Selection&lt;/h3&gt;
  &lt;/div&gt;

  &lt;div class="vuexy-table-responsive"&gt;
    &lt;table class="vuexy-table w-full border-collapse"&gt;
      &lt;thead&gt;
        &lt;tr&gt;
          &lt;th class="vuexy-table__th"&gt;&lt;input type="checkbox" class="vuexy-checkbox" /&gt;&lt;/th&gt;
          &lt;th class="vuexy-table__th"&gt;NAME&lt;/th&gt;
          &lt;th class="vuexy-table__th"&gt;EMAIL&lt;/th&gt;
          &lt;th class="vuexy-table__th"&gt;DATE&lt;/th&gt;
          &lt;th class="vuexy-table__th"&gt;SALARY&lt;/th&gt;
          &lt;th class="vuexy-table__th"&gt;AGE&lt;/th&gt;
          &lt;th class="vuexy-table__th"&gt;STATUS&lt;/th&gt;
        &lt;/tr&gt;
      &lt;/thead&gt;
      &lt;tbody&gt;
        &lt;tr v-for="row in rows" :key="row.id" class="vuexy-table__row"&gt;
          &lt;td class="vuexy-table__td"&gt;
            &lt;input type="checkbox" class="vuexy-checkbox" v-model="selected" :value="row.id" /&gt;
          &lt;/td&gt;
          &lt;td class="vuexy-table__td"&gt;
            &lt;div class="flex items-center gap-3"&gt;
              &lt;img :src="row.avatar" class="vuexy-avatar w-8 h-8 rounded-full" /&gt;
              &lt;div class="flex flex-col"&gt;
                &lt;span class="font-semibold text-sm"&gt;&#123;&#123; row.name &#125;&#125;&lt;/span&gt;
                &lt;span class="text-xs text-muted"&gt;&#123;&#123; row.role &#125;&#125;&lt;/span&gt;
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/td&gt;
          &lt;td class="vuexy-table__td"&gt;&#123;&#123; row.email &#125;&#125;&lt;/td&gt;
          &lt;td class="vuexy-table__td"&gt;&#123;&#123; row.date &#125;&#125;&lt;/td&gt;
          &lt;td class="vuexy-table__td"&gt;&#123;&#123; row.salary &#125;&#125;&lt;/td&gt;
          &lt;td class="vuexy-table__td"&gt;&#123;&#123; row.age &#125;&#125;&lt;/td&gt;
          &lt;td class="vuexy-table__td"&gt;
            &lt;span class="vuexy-badge-pill vuexy-badge-pill--current"&gt;&#123;&#123; row.status &#125;&#125;&lt;/span&gt;
          &lt;/td&gt;
        &lt;/tr&gt;
      &lt;/tbody&gt;
    &lt;/table&gt;
  &lt;/div&gt;
&lt;/div&gt;</code></pre>
      </div>
    </a-modal>
  </div>
</template>

<script>
import { defineComponent, ref, computed } from 'vue';
import { useThemeStore } from '../../store/themeStore';

export default defineComponent({
  name: 'VuexyDataTable',
  setup() {
    const themeStore = useThemeStore();
    const showCodeModal = ref(false);
    const searchQuery = ref('');
    const currentPage = ref(1);
    const pageSize = ref(5);
    const selectedRowKeys = ref([]);

    // Sample data matching user reference image
    const rows = ref([
      {
        id: 1,
        name: 'Edwina Ebsworth',
        role: 'Human Resources Assistant',
        avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80',
        initials: '',
        email: 'eebsworth2m@sbwire.com',
        date: '09/27/2018',
        salary: '19586.23',
        age: 27,
        status: 'Current'
      },
      {
        id: 2,
        name: "Korrie O'Crevy",
        role: 'Nuclear Power Engineer',
        avatar: 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=100&auto=format&fit=crop&q=80',
        initials: '',
        email: 'kocrevy0@thetimes.co.uk',
        date: '09/23/2016',
        salary: '23896.35',
        age: 61,
        status: 'Professional'
      },
      {
        id: 3,
        name: 'Eileen Diehn',
        role: 'Environmental Specialist',
        avatar: '',
        initials: 'ED',
        initialsBg: '#EFEAFF',
        initialsColor: '#7367F0',
        email: 'ediehn6@163.com',
        date: '10/15/2017',
        salary: '18991.67',
        age: 59,
        status: 'Rejected'
      },
      {
        id: 4,
        name: 'De Falloon',
        role: 'Sales Representative',
        avatar: '',
        initials: 'DF',
        initialsBg: '#E8F4FD',
        initialsColor: '#00CFE8',
        email: 'dfalloona@ifeng.com',
        date: '06/12/2018',
        salary: '19252.12',
        age: 30,
        status: 'Resigned'
      },
      {
        id: 5,
        name: 'Stella Ganderton',
        role: 'Operator',
        avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop&q=80',
        initials: '',
        email: 'sganderton2@tuttocitta.it',
        date: '03/24/2018',
        salary: '13076.28',
        age: 66,
        status: 'Applied'
      },
      {
        id: 6,
        name: 'Grover Clucas',
        role: 'Senior Quality Engineer',
        avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&auto=format&fit=crop&q=80',
        initials: '',
        email: 'gclucas@yandex.ru',
        date: '05/19/2019',
        salary: '21500.00',
        age: 38,
        status: 'Current'
      },
      {
        id: 7,
        name: 'Bettina Alldred',
        role: 'Financial Analyst',
        avatar: '',
        initials: 'BA',
        initialsBg: '#E8F9EE',
        initialsColor: '#28C76F',
        email: 'balldred@google.com',
        date: '11/04/2020',
        salary: '25400.50',
        age: 32,
        status: 'Professional'
      },
      {
        id: 8,
        name: 'Harmon Kelsall',
        role: 'Automation Specialist',
        avatar: '',
        initials: 'HK',
        initialsBg: '#FFF3E8',
        initialsColor: '#FF9F43',
        email: 'hkelsall@bing.com',
        date: '08/14/2021',
        salary: '16800.00',
        age: 45,
        status: 'Resigned'
      }
    ]);

    const totalRecords = 100;

    // Filtered by search
    const filteredRows = computed(() => {
      if (!searchQuery.value.trim()) return rows.value;
      const q = searchQuery.value.toLowerCase();
      return rows.value.filter(r => 
        r.name.toLowerCase().includes(q) || 
        r.email.toLowerCase().includes(q) || 
        r.role.toLowerCase().includes(q) ||
        r.status.toLowerCase().includes(q)
      );
    });

    const totalPages = computed(() => {
      return Math.ceil(filteredRows.value.length / pageSize.value) || 1;
    });

    const paginatedRows = computed(() => {
      const start = (currentPage.value - 1) * pageSize.value;
      return filteredRows.value.slice(start, start + pageSize.value);
    });

    const startRecordIndex = computed(() => {
      if (filteredRows.value.length === 0) return 0;
      return (currentPage.value - 1) * pageSize.value + 1;
    });

    const endRecordIndex = computed(() => {
      const end = currentPage.value * pageSize.value;
      return end > filteredRows.value.length ? filteredRows.value.length : end;
    });

    // Row selection logic
    const isAllSelected = computed(() => {
      if (paginatedRows.value.length === 0) return false;
      return paginatedRows.value.every(r => selectedRowKeys.value.includes(r.id));
    });

    const isIndeterminate = computed(() => {
      const selectedCountInPage = paginatedRows.value.filter(r => selectedRowKeys.value.includes(r.id)).length;
      return selectedCountInPage > 0 && selectedCountInPage < paginatedRows.value.length;
    });

    const toggleSelectAll = (e) => {
      if (e.target.checked) {
        const pageIds = paginatedRows.value.map(r => r.id);
        selectedRowKeys.value = Array.from(new Set([...selectedRowKeys.value, ...pageIds]));
      } else {
        const pageIds = new Set(paginatedRows.value.map(r => r.id));
        selectedRowKeys.value = selectedRowKeys.value.filter(id => !pageIds.has(id));
      }
    };

    const toggleSelectRow = (id) => {
      const idx = selectedRowKeys.value.indexOf(id);
      if (idx > -1) {
        selectedRowKeys.value.splice(idx, 1);
      } else {
        selectedRowKeys.value.push(id);
      }
    };

    // Status pill badge helper
    const statusBadgeClass = (status) => {
      switch (status) {
        case 'Current':
          return 'vuexy-badge-pill--current';
        case 'Professional':
          return 'vuexy-badge-pill--professional';
        case 'Rejected':
          return 'vuexy-badge-pill--rejected';
        case 'Resigned':
          return 'vuexy-badge-pill--resigned';
        case 'Applied':
          return 'vuexy-badge-pill--applied';
        default:
          return 'vuexy-badge-pill--secondary';
      }
    };

    return {
      themeStore,
      showCodeModal,
      searchQuery,
      rows,
      totalRecords,
      pageSize,
      currentPage,
      totalPages,
      filteredRows,
      paginatedRows,
      startRecordIndex,
      endRecordIndex,
      selectedRowKeys,
      isAllSelected,
      isIndeterminate,
      toggleSelectAll,
      toggleSelectRow,
      statusBadgeClass
    };
  }
});
</script>

<style scoped>
.vuexy-table__th {
  font-size: 11.5px;
  letter-spacing: 0.8px;
}
.vuexy-table__td {
  font-size: 13.5px;
}
</style>
