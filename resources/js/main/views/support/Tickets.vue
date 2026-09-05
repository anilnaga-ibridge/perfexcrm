<template>
  <div class="p-4 md:p-6 space-y-6 max-w-[1600px] mx-auto min-h-screen bg-[#F8F7FA]">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <div class="flex items-center space-x-2">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#7367F0] to-[#9F8ED6]"></div>
          <h1 class="text-xl md:text-2xl font-bold text-[#4B465C] tracking-tight m-0">Support Tickets</h1>
        </div>
        <p class="text-xs text-[#A8AAAE] mt-1 pl-4.5 mb-0">Manage and resolve customer support requests, inquiries, and issues</p>
      </div>

      <div class="flex items-center gap-3">
        <!-- View Toggle Buttons -->
        <div class="flex items-center bg-white p-1 border border-[#DBDADE] rounded-md shadow-2xs">
          <button
            class="px-2.5 py-1.5 rounded text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer"
            :class="currentView === 'kanban' ? 'bg-[#7367F0] text-white shadow-xs' : 'text-[#6F6B7D] hover:text-[#4B465C]'"
            @click="currentView = 'kanban'"
            title="Kanban View"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
            <span>Kanban</span>
          </button>
          <button
            class="px-2.5 py-1.5 rounded text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer"
            :class="currentView === 'table' ? 'bg-[#7367F0] text-white shadow-xs' : 'text-[#6F6B7D] hover:text-[#4B465C]'"
            @click="currentView = 'table'"
            title="Table View"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            <span>Table</span>
          </button>
        </div>

        <!-- Weekly Analytics Button -->
        <router-link
          :to="{ name: 'admin.support.weekly_analytics' }"
          class="btn-outline px-3.5 py-2 text-xs font-bold flex items-center gap-2 shadow-2xs cursor-pointer"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          <span>Weekly Analytics</span>
        </router-link>

        <!-- New Ticket Button -->
        <button
          @click="openCreateModal"
          class="btn-primary px-4 py-2 text-xs font-bold flex items-center gap-2 shadow-md cursor-pointer"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>New Ticket</span>
        </button>
      </div>
    </div>

    <!-- Summary KPI Stat Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
      <div
        v-for="card in summaryCards"
        :key="card.label"
        @click="filterBy(card.statusValue)"
        class="bg-white border border-[#EBE9F1] rounded-lg p-4 shadow-sm hover:shadow-md transition-all cursor-pointer flex items-center justify-between group"
        :class="{ 'ring-2 ring-[#7367F0] border-transparent': statusFilter === card.statusValue }"
      >
        <div class="space-y-1">
          <span class="text-[11px] font-bold uppercase tracking-wider text-[#A8AAAE] block">{{ card.label }}</span>
          <div class="text-xl font-extrabold" :style="{ color: card.textColor }">
            {{ card.value }}
          </div>
          <span class="text-[10px] text-[#A8AAAE] font-semibold">Tickets</span>
        </div>
        <div
          class="w-11 h-11 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110 flex-shrink-0"
          :style="{ backgroundColor: card.bgLight, color: card.color }"
          v-html="card.icon"
        ></div>
      </div>
    </div>

    <!-- ====== KANBAN VIEW ====== -->
    <div v-if="currentView === 'kanban'" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
      <div
        v-for="col in kanbanColumns"
        :key="col.status"
        class="bg-white border border-[#EBE9F1] rounded-lg p-3.5 shadow-sm flex flex-col space-y-3"
      >
        <!-- Column Header -->
        <div class="flex items-center justify-between pb-2 border-b border-[#F1F0F2]">
          <div class="flex items-center space-x-2">
            <div class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: col.color }"></div>
            <span class="text-xs font-bold text-[#4B465C]">{{ col.title }}</span>
          </div>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#F8F7FA] text-[#6F6B7D] border border-[#DBDADE]">
            {{ ticketsByStatus(col.status).length }}
          </span>
        </div>

        <!-- Column Cards Drop Area -->
        <div
          class="space-y-2.5 flex-1 min-h-[260px] transition-colors rounded-lg p-1"
          :class="{ 'bg-[#7367F0]/5 border-2 border-dashed border-[#7367F0]': dragCol === col.status }"
          @dragover.prevent="dragCol = col.status"
          @dragenter.prevent
          @dragleave="dragCol = null"
          @drop="onDrop(col.status)"
        >
          <div
            v-for="t in ticketsByStatus(col.status)"
            :key="t.id"
            class="bg-[#F8F7FA] border border-[#EBE9F1] hover:border-[#7367F0]/40 rounded-lg p-3 shadow-2xs hover:shadow-sm transition-all space-y-2 cursor-grab active:cursor-grabbing group"
            draggable="true"
            @dragstart="onDragStart(t)"
            @dragend="dragCol = null"
          >
            <!-- Header Badges -->
            <div class="flex items-center justify-between">
              <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="priClass(t.priority)">
                {{ t.priority }}
              </span>
              <span class="text-[10px] font-mono font-bold text-[#A8AAAE]">#{{ t.id }}</span>
            </div>

            <!-- Subject -->
            <div
              class="text-xs font-bold text-[#4B465C] group-hover:text-[#7367F0] transition-colors cursor-pointer line-clamp-2 leading-snug"
              @click="viewTicket(t)"
            >
              {{ t.subject }}
            </div>

            <!-- Contact & Department -->
            <div class="text-[11px] text-[#6F6B7D] flex items-center gap-1 truncate" v-if="t.contact">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <span class="truncate">{{ t.contact.firstname }} {{ t.contact.lastname }}</span>
            </div>

            <div v-if="t.department" class="text-[10px] font-semibold text-[#A8AAAE]">
              {{ t.department.name }}
            </div>

            <!-- Footer Meta -->
            <div class="flex items-center justify-between pt-1.5 border-t border-[#DBDADE]/50 text-[10px]">
              <span class="flex items-center gap-1 text-[#A8AAAE]">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                {{ fmtDate(t.created_at) }}
              </span>

              <div
                class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-bold text-white shadow-2xs"
                :class="t.assignee ? 'bg-gradient-to-tr from-[#7367F0] to-[#9F8ED6]' : 'bg-[#DBDADE] text-[#6F6B7D]'"
                :title="t.assignee?.name || 'Unassigned'"
              >
                {{ t.assignee ? t.assignee.name.charAt(0).toUpperCase() : '?' }}
              </div>
            </div>
          </div>

          <!-- Empty Column State -->
          <div
            v-if="!ticketsByStatus(col.status).length"
            class="h-28 border-2 border-dashed border-[#DBDADE]/60 rounded-lg flex items-center justify-center text-[11px] text-[#A8AAAE] font-semibold"
          >
            Drop here
          </div>
        </div>

        <!-- Quick Add Button -->
        <button
          @click="openCreateForStatus(col.status)"
          class="w-full py-2 rounded-md border border-dashed border-[#DBDADE] hover:border-[#7367F0] hover:bg-[#7367F0]/5 text-[#6F6B7D] hover:text-[#7367F0] text-xs font-semibold transition-all flex items-center justify-center gap-1.5 cursor-pointer"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span>Add Ticket</span>
        </button>
      </div>
    </div>

    <!-- ====== TABLE VIEW ====== -->
    <div v-else class="space-y-4">
      <!-- Filter Bar -->
      <div class="bg-white border border-[#EBE9F1] rounded-lg p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
          <!-- Per Page Select -->
          <div class="flex items-center space-x-2">
            <span class="text-xs text-[#A8AAAE] font-medium">Show</span>
            <div class="relative">
              <select
                v-model="perPage"
                @change="load"
                class="form-ctrl text-xs h-[36px] pl-3 pr-7 bg-[#F8F7FA] border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer"
              >
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-[#A8AAAE]">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="6 9 12 15 18 9"/></svg>
              </div>
            </div>
          </div>

          <!-- Priority Filter -->
          <div class="relative">
            <select
              v-model="priorityFilter"
              @change="load"
              class="form-ctrl text-xs h-[36px] pl-3 pr-8 bg-[#F8F7FA] border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer"
            >
              <option value="">All Priority</option>
              <option value="Low">Low</option>
              <option value="Medium">Medium</option>
              <option value="High">High</option>
              <option value="Urgent">Urgent</option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-[#A8AAAE]">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
          </div>

          <!-- Department Filter -->
          <div class="relative">
            <select
              v-model="deptFilter"
              @change="load"
              class="form-ctrl text-xs h-[36px] pl-3 pr-8 bg-[#F8F7FA] border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer"
            >
              <option value="">All Departments</option>
              <option v-for="d in metadata.departments" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-[#A8AAAE]">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
          </div>
        </div>

        <!-- Search input -->
        <div class="relative w-full md:w-64">
          <input
            v-model="search"
            @input="onSearch"
            type="text"
            placeholder="Search tickets..."
            class="form-ctrl text-xs h-[36px] pl-9 pr-3.5 bg-[#F8F7FA] border-[#DBDADE] rounded-md transition-all w-full"
          />
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#A8AAAE]">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </div>
        </div>
      </div>

      <!-- Table Card -->
      <div class="bg-white border border-[#EBE9F1] rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto min-h-[320px]">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-[#F8F7FA] border-b border-[#EBE9F1] text-[11px] font-bold uppercase tracking-wider text-[#6F6B7D]">
                <th class="py-3 px-3.5 text-center w-12">#</th>
                <th class="py-3 px-3.5 min-w-[220px]">Subject</th>
                <th class="py-3 px-3.5">Tags</th>
                <th class="py-3 px-3.5">Department</th>
                <th class="py-3 px-3.5">Service</th>
                <th class="py-3 px-3.5 min-w-[140px]">Contact</th>
                <th class="py-3 px-3.5">Status</th>
                <th class="py-3 px-3.5">Priority</th>
                <th class="py-3 px-3.5 whitespace-nowrap">Last Reply</th>
                <th class="py-3 px-3.5 whitespace-nowrap">Created</th>
                <th class="py-3 px-3.5 text-center w-28">Options</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F1F0F2] text-xs text-[#6F6B7D]">
              <tr v-if="loading">
                <td colspan="11" class="text-center py-16 text-[#A8AAAE]">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24" class="animate-spin text-[#7367F0]"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                    <span class="text-xs font-semibold">Loading tickets...</span>
                  </div>
                </td>
              </tr>

              <tr
                v-for="(t, idx) in tickets"
                :key="t.id"
                class="hover:bg-[#F8F7FA]/70 transition-colors group"
              >
                <!-- Index -->
                <td class="py-3.5 px-3.5 text-center text-[#A8AAAE] font-mono text-[11px]">
                  {{ idx + 1 + (page - 1) * (+perPage) }}
                </td>

                <!-- Subject -->
                <td class="py-3.5 px-3.5">
                  <span
                    class="font-bold text-[#4B465C] hover:text-[#7367F0] transition-colors cursor-pointer"
                    @click="viewTicket(t)"
                  >
                    {{ t.subject }}
                  </span>
                </td>

                <!-- Tags -->
                <td class="py-3.5 px-3.5">
                  <div class="flex items-center gap-1 flex-wrap">
                    <span v-if="!t.tags || !parseTags(t.tags).length" class="text-[#A8AAAE]">—</span>
                    <span
                      v-for="tag in parseTags(t.tags)"
                      :key="tag"
                      class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#7367F0]/10 text-[#7367F0] border border-[#7367F0]/20"
                    >
                      {{ tag }}
                    </span>
                  </div>
                </td>

                <!-- Department -->
                <td class="py-3.5 px-3.5 whitespace-nowrap text-[#6F6B7D]">
                  {{ t.department?.name || '—' }}
                </td>

                <!-- Service -->
                <td class="py-3.5 px-3.5 whitespace-nowrap text-[#6F6B7D]">
                  {{ getServiceName(t.service_id) }}
                </td>

                <!-- Contact -->
                <td class="py-3.5 px-3.5">
                  <span class="font-medium text-[#4B465C]">
                    {{ t.contact ? (t.contact.firstname + ' ' + (t.contact.lastname || '')) : '—' }}
                  </span>
                </td>

                <!-- Status -->
                <td class="py-3.5 px-3.5 whitespace-nowrap">
                  <span
                    class="px-2.5 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 shadow-2xs"
                    :class="statusBadgeClass(t.status)"
                  >
                    <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(t.status)"></span>
                    {{ t.status }}
                  </span>
                </td>

                <!-- Priority -->
                <td class="py-3.5 px-3.5 whitespace-nowrap">
                  <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" :class="priClass(t.priority)">
                    {{ t.priority }}
                  </span>
                </td>

                <!-- Last Reply -->
                <td class="py-3.5 px-3.5 whitespace-nowrap text-[#6F6B7D]">
                  {{ fmtDate(t.last_reply_at) }}
                </td>

                <!-- Created Date -->
                <td class="py-3.5 px-3.5 whitespace-nowrap text-[#6F6B7D]">
                  {{ fmtDate(t.created_at) }}
                </td>

                <!-- Actions -->
                <td class="py-3.5 px-3.5 text-center">
                  <div class="flex items-center justify-center gap-1">
                    <button
                      @click="viewTicket(t)"
                      class="w-7 h-7 rounded border border-transparent hover:border-[#DBDADE] hover:bg-[#F8F7FA] text-[#A8AAAE] hover:text-[#7367F0] flex items-center justify-center transition-all cursor-pointer bg-transparent"
                      title="View Conversation"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                    <button
                      @click="editTicket(t)"
                      class="w-7 h-7 rounded border border-transparent hover:border-[#DBDADE] hover:bg-[#F8F7FA] text-[#A8AAAE] hover:text-[#7367F0] flex items-center justify-center transition-all cursor-pointer bg-transparent"
                      title="Edit Ticket"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"/></svg>
                    </button>
                    <button
                      @click="deleteTicket(t)"
                      class="w-7 h-7 rounded border border-transparent hover:border-rose-200 hover:bg-rose-50 text-[#A8AAAE] hover:text-rose-600 flex items-center justify-center transition-all cursor-pointer bg-transparent"
                      title="Delete Ticket"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!loading && !tickets.length">
                <td colspan="11" class="text-center py-12 text-[#A8AAAE]">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="36" height="36" class="mx-auto mb-2 opacity-50"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                  <p class="text-xs font-semibold m-0">No support tickets found</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between px-5 py-3 border-t border-[#F1F0F2] text-xs text-[#6F6B7D]" v-if="totalPages > 1">
          <span class="text-[#A8AAAE]">Showing {{ tickets.length }} of {{ totalPages * (+perPage) }} entries</span>
          <div class="flex items-center space-x-2">
            <button class="btn-outline px-3 py-1.5 text-xs font-semibold cursor-pointer" :disabled="page <= 1" @click="page--; load()">Previous</button>
            <button class="btn-outline px-3 py-1.5 text-xs font-semibold cursor-pointer" :disabled="page >= totalPages" @click="page++; load()">Next</button>
          </div>
        </div>
      </div>
    </div>

    <!-- CREATE / EDIT TICKET RIGHT-SIDE DRAWER -->
    <a-drawer
      v-model:open="showModal"
      placement="right"
      :width="680"
      :destroyOnClose="true"
      class="vuexy-ticket-drawer"
      @close="closeModal"
    >
      <template #title>
        <div class="flex items-center space-x-3 py-1">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#7367F0] to-[#9F8ED6]"></div>
          <div>
            <h2 class="text-base font-bold text-[#4B465C] m-0">
              {{ editingTicket ? 'Edit Support Ticket' : 'Create New Ticket' }}
            </h2>
            <p class="text-xs text-[#A8AAAE] m-0 mt-0.5">
              {{ editingTicket ? 'Update ticket properties and message payload' : 'Fill in the customer support ticket details below' }}
            </p>
          </div>
        </div>
      </template>

      <div class="p-1 space-y-6">
        <!-- Mode Switcher Pill -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-1.5 flex items-center gap-1 shadow-2xs">
          <button
            type="button"
            class="flex-1 py-2 px-3 text-xs font-bold rounded-md transition-all cursor-pointer text-center"
            :class="!ticketWithoutContact ? 'bg-[#7367F0] text-white shadow-xs' : 'text-[#6F6B7D] hover:bg-[#F8F7FA]'"
            @click="ticketWithoutContact = false"
          >
            Ticket with Customer Contact
          </button>
          <button
            type="button"
            class="flex-1 py-2 px-3 text-xs font-bold rounded-md transition-all cursor-pointer text-center"
            :class="ticketWithoutContact ? 'bg-[#7367F0] text-white shadow-xs' : 'text-[#6F6B7D] hover:bg-[#F8F7FA]'"
            @click="ticketWithoutContact = true"
          >
            Direct / Unlinked Ticket
          </button>
        </div>

        <!-- 1. Primary Ticket Info Card -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 space-y-4 shadow-sm">
          <div class="flex items-center space-x-2 pb-2 border-b border-[#F1F0F2]">
            <span class="w-2 h-2 rounded-full bg-[#7367F0]"></span>
            <span class="text-xs font-bold text-[#4B465C] uppercase tracking-wider">Ticket Details</span>
          </div>

          <div class="space-y-4">
            <!-- Subject -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                Subject <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="form.subject"
                placeholder="e.g. Server connection timeout error"
                class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
              />
            </div>

            <!-- Department & Priority -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Department <span class="text-rose-500">*</span></label>
                <div class="relative">
                  <select
                    v-model="form.department_id"
                    class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                  >
                    <option :value="null">Select Department</option>
                    <option v-for="d in metadata.departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Priority</label>
                <div class="relative">
                  <select
                    v-model="form.priority"
                    class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                  >
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                    <option value="Urgent">Urgent</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
            </div>

            <!-- Service & CC -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Service</label>
                <div class="relative">
                  <select
                    v-model="form.service_id"
                    class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                  >
                    <option :value="null">Select Service</option>
                    <option v-for="s in metadata.services" :key="s.id" :value="s.id">{{ s.name }}</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">CC</label>
                <input
                  v-model="form.cc"
                  placeholder="cc@example.com"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Customer & Contact Details Card -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 space-y-4 shadow-sm">
          <div class="flex items-center space-x-2 pb-2 border-b border-[#F1F0F2]">
            <span class="w-2 h-2 rounded-full bg-[#28C76F]"></span>
            <span class="text-xs font-bold text-[#4B465C] uppercase tracking-wider">Contact & Customer</span>
          </div>

          <!-- If Without Contact Mode -->
          <template v-if="ticketWithoutContact">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Contact Name</label>
                <input
                  v-model="form.contact_name"
                  placeholder="e.g. John Doe"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Email Address</label>
                <input
                  v-model="form.email"
                  placeholder="john@example.com"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
                />
              </div>
              <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Contact Number</label>
                <input
                  v-model="form.contact_number"
                  placeholder="+1 555-0199"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all w-full"
                />
              </div>
            </div>
          </template>

          <!-- If Standard Customer Link Mode -->
          <template v-else>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Contact <span class="text-rose-500">*</span></label>
                <div class="relative">
                  <select
                    v-model="form.contact_id"
                    class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                  >
                    <option :value="null">Select Contact</option>
                    <option v-for="c in metadata.contacts" :key="c.id" :value="c.id">
                      {{ c.firstname }} {{ c.lastname }}
                    </option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Customer (Auto-filled)</label>
                <input
                  :value="selectedClient ? selectedClient.company : ''"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-[#F8F7FA] border-[#DBDADE] text-[#6F6B7D] rounded-md w-full"
                  readonly
                  placeholder="Auto-filled from contact"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Contact Email</label>
                <input
                  :value="selectedContact ? selectedContact.email : ''"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-[#F8F7FA] border-[#DBDADE] text-[#6F6B7D] rounded-md w-full"
                  readonly
                  placeholder="Auto-filled from contact"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Project Link</label>
                <div class="relative">
                  <select
                    v-model="form.project_id"
                    class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                    :disabled="!form.client_id"
                  >
                    <option :value="null">Select Project</option>
                    <option v-for="p in filteredProjects" :key="p.id" :value="p.id">{{ p.name }}</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>

        <!-- 3. Assignment & Tags Card -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 space-y-4 shadow-sm">
          <div class="flex items-center space-x-2 pb-2 border-b border-[#F1F0F2]">
            <span class="w-2 h-2 rounded-full bg-[#FF9F43]"></span>
            <span class="text-xs font-bold text-[#4B465C] uppercase tracking-wider">Staff Assignment & Tags</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Assign Staff</label>
              <div class="relative">
                <select
                  v-model="form.assigned_to"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                >
                  <option :value="null">Select Staff (default: you)</option>
                  <option v-for="s in metadata.staff" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
              </div>
            </div>

            <div v-if="editingTicket">
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Status</label>
              <div class="relative">
                <select
                  v-model="form.status"
                  class="form-ctrl text-xs h-[38px] px-3.5 bg-white border-[#DBDADE] rounded-md transition-all appearance-none cursor-pointer pr-10 w-full"
                >
                  <option value="Open">Open</option>
                  <option value="In Progress">In Progress</option>
                  <option value="Answered">Answered</option>
                  <option value="On Hold">On Hold</option>
                  <option value="Closed">Closed</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-[#A8AAAE]">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
              </div>
            </div>

            <!-- Tags Field -->
            <div class="sm:col-span-2">
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Tags</label>
              <div class="p-2 bg-white border border-[#DBDADE] rounded-md flex flex-wrap items-center gap-1.5 min-h-[38px] focus-within:border-[#7367F0]">
                <span
                  v-for="(tag, i) in form.tagList"
                  :key="i"
                  class="px-2 py-0.5 rounded-full text-xs font-bold bg-[#7367F0]/10 text-[#7367F0] flex items-center gap-1"
                >
                  {{ tag }}
                  <button type="button" @click="form.tagList.splice(i, 1); form.tags = form.tagList.join(',')" class="text-[#7367F0] hover:text-rose-600 cursor-pointer font-bold">&times;</button>
                </span>
                <input
                  v-model="tagInput"
                  @keydown.enter.prevent="addTag"
                  @keydown.,.prevent="addTag"
                  placeholder="Type tag and press Enter..."
                  class="text-xs border-none outline-none flex-1 min-w-[140px] bg-transparent py-0.5"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- 4. Ticket Body & Quick Solutions Card -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 space-y-4 shadow-sm">
          <div class="flex items-center justify-between pb-2 border-b border-[#F1F0F2]">
            <div class="flex items-center space-x-2">
              <span class="w-2 h-2 rounded-full bg-[#00CFE8]"></span>
              <span class="text-xs font-bold text-[#4B465C] uppercase tracking-wider">Ticket Message & Solution</span>
            </div>
            <div class="flex items-center gap-2">
              <select
                v-model="form.predefined_reply"
                @change="handlePredefinedReplyChange"
                class="text-[11px] font-semibold text-[#6F6B7D] bg-[#F8F7FA] border border-[#DBDADE] rounded-md px-2 py-1 outline-none cursor-pointer"
              >
                <option value="">Insert predefined reply</option>
                <option v-for="r in predefinedReplies" :key="r.id" :value="r.id">{{ r.title }}</option>
              </select>
              <select
                v-model="form.kb_link"
                @change="handleKbLinkChange"
                class="text-[11px] font-semibold text-[#6F6B7D] bg-[#F8F7FA] border border-[#DBDADE] rounded-md px-2 py-1 outline-none cursor-pointer"
              >
                <option value="">Insert KB article link</option>
                <option v-for="a in kbArticles" :key="a.id" :value="a.id">{{ a.title }}</option>
              </select>
            </div>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">
                Ticket Description / Message <span class="text-rose-500">*</span>
              </label>
              <textarea
                v-model="form.message"
                rows="5"
                placeholder="Describe the issue in detail..."
                class="form-ctrl text-xs p-3 bg-white border-[#DBDADE] rounded-md transition-all min-h-[100px] w-full resize-none leading-relaxed"
              ></textarea>
            </div>

            <!-- Attachment File -->
            <div>
              <label class="block text-xs font-semibold text-[#4B465C] mb-1.5">Attachments</label>
              <div class="p-3 border border-[#DBDADE] rounded-md bg-[#F8F7FA] flex items-center gap-3">
                <input type="file" @change="handleFileChange" class="hidden" id="tk-file-input" />
                <label for="tk-file-input" class="btn-primary px-3.5 py-1.5 text-xs font-bold cursor-pointer inline-flex items-center gap-1.5">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                  <span>Choose File</span>
                </label>
                <span class="text-xs font-medium text-[#6F6B7D] truncate">{{ attachmentFileName || 'No file selected' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Drawer Footer -->
      <template #footer>
        <div class="flex items-center justify-end space-x-3 py-2 px-1">
          <button
            type="button"
            class="btn-outline px-5 py-2.5 text-xs font-semibold cursor-pointer"
            @click="closeModal"
          >
            Cancel
          </button>
          <button
            type="button"
            class="btn-primary px-6 py-2.5 text-xs font-bold flex items-center gap-2 cursor-pointer"
            :disabled="saving"
            @click="saveTicket"
          >
            <svg v-if="saving" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" class="animate-spin"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            {{ saving ? 'Saving...' : (editingTicket ? 'Save Changes' : 'Create Ticket') }}
          </button>
        </div>
      </template>
    </a-drawer>

    <!-- VIEW TICKET CONVERSATION DRAWER -->
    <a-drawer
      v-model:open="showViewDrawer"
      placement="right"
      :width="640"
      :destroyOnClose="true"
      class="vuexy-ticket-view-drawer"
    >
      <template #title>
        <div v-if="viewingTicket" class="flex items-center space-x-3 py-1">
          <div class="w-2.5 h-6 rounded-full bg-gradient-to-b from-[#7367F0] to-[#9F8ED6]"></div>
          <div>
            <h2 class="text-base font-bold text-[#4B465C] m-0">Ticket #{{ viewingTicket.id }}</h2>
            <p class="text-xs text-[#A8AAAE] m-0 mt-0.5 truncate max-w-sm">{{ viewingTicket.subject }}</p>
          </div>
        </div>
      </template>

      <div v-if="viewingTicket" class="p-1 space-y-5">
        <!-- Ticket Meta Badges Card -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-4 shadow-sm space-y-3">
          <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" :class="priClass(viewingTicket.priority)">
                {{ viewingTicket.priority }}
              </span>
              <span class="px-2.5 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 shadow-2xs" :class="statusBadgeClass(viewingTicket.status)">
                <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(viewingTicket.status)"></span>
                {{ viewingTicket.status }}
              </span>
            </div>
            <span class="text-xs text-[#A8AAAE] font-medium">{{ fmtDate(viewingTicket.created_at) }}</span>
          </div>

          <div class="grid grid-cols-2 gap-3 pt-2 border-t border-[#F1F0F2] text-xs">
            <div>
              <span class="text-[#A8AAAE] font-medium block">Contact</span>
              <span class="font-semibold text-[#4B465C]">{{ viewingTicket.contact ? (viewingTicket.contact.firstname + ' ' + (viewingTicket.contact.lastname || '')) : '—' }}</span>
            </div>
            <div>
              <span class="text-[#A8AAAE] font-medium block">Department</span>
              <span class="font-semibold text-[#4B465C]">{{ viewingTicket.department?.name || '—' }}</span>
            </div>
            <div>
              <span class="text-[#A8AAAE] font-medium block">Customer</span>
              <span class="font-semibold text-[#4B465C]">{{ viewingTicket.client?.company || 'N/A' }}</span>
            </div>
            <div>
              <span class="text-[#A8AAAE] font-medium block">Assigned Staff</span>
              <span class="font-semibold text-[#4B465C]">{{ viewingTicket.assignee?.name || 'Unassigned' }}</span>
            </div>
          </div>
        </div>

        <!-- Original Message Bubble -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-5 shadow-sm space-y-2">
          <div class="flex items-center space-x-2 text-xs font-bold text-[#7367F0] pb-2 border-b border-[#F1F0F2]">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            <span>Original Message</span>
          </div>
          <p class="text-xs text-[#4B465C] leading-relaxed whitespace-pre-wrap m-0">
            {{ viewingTicket.message }}
          </p>
        </div>

        <!-- Conversation Replies Thread -->
        <div class="space-y-3">
          <h4 class="text-xs font-bold text-[#4B465C] uppercase tracking-wider m-0">
            Replies ({{ viewingTicket.replies?.length || 0 }})
          </h4>

          <div
            v-for="reply in viewingTicket.replies"
            :key="reply.id"
            class="p-4 rounded-lg border space-y-1.5 shadow-2xs"
            :class="reply.is_admin_reply ? 'bg-white border-[#7367F0]/30' : 'bg-[#F8F7FA] border-[#EBE9F1]'"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-[#4B465C]">{{ reply.name || reply.user?.name || 'Support' }}</span>
                <span v-if="reply.is_admin_reply" class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-[#7367F0]/10 text-[#7367F0]">Staff</span>
              </div>
              <span class="text-[10px] text-[#A8AAAE]">{{ fmtDate(reply.created_at) }}</span>
            </div>
            <p class="text-xs text-[#6F6B7D] leading-relaxed whitespace-pre-wrap m-0">{{ reply.message }}</p>
          </div>
        </div>

        <!-- Reply Input Box -->
        <div class="bg-white border border-[#EBE9F1] rounded-lg p-4 shadow-sm space-y-3">
          <label class="block text-xs font-bold text-[#4B465C]">Send a Reply</label>
          <textarea
            v-model="replyMessage"
            rows="3"
            placeholder="Type your response to the customer..."
            class="form-ctrl text-xs p-3 bg-[#F8F7FA] border-[#DBDADE] rounded-md transition-all min-h-[80px] w-full resize-none leading-relaxed"
          ></textarea>
          <div class="flex justify-end">
            <button
              class="btn-primary px-5 py-2 text-xs font-bold flex items-center gap-1.5 cursor-pointer shadow-sm"
              @click="sendReply"
              :disabled="sendingReply"
            >
              <svg v-if="sendingReply" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" class="animate-spin"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
              <span>{{ sendingReply ? 'Sending...' : 'Post Reply' }}</span>
            </button>
          </div>
        </div>
      </div>
    </a-drawer>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import { message } from 'ant-design-vue'
import { useAuthStore } from '../../store/authStore'

const BASE = '/api'
const authStore = useAuthStore()

const tickets = ref([])
const stats = ref({})
const loading = ref(false)
const saving = ref(false)
const search = ref('')
const perPage = ref('25')
const page = ref(1)
const totalPages = ref(1)
const priorityFilter = ref('')
const deptFilter = ref('')
const statusFilter = ref('')
const showModal = ref(false)
const showViewDrawer = ref(false)
const editingTicket = ref(null)
const viewingTicket = ref(null)
const replyMessage = ref('')
const sendingReply = ref(false)
const currentView = ref('kanban')
const tagInput = ref('')
const attachmentFile = ref(null)
const attachmentFileName = ref('')
const selectedContact = ref(null)
const selectedClient = ref(null)
const dragCol = ref(null)
const dragTicketId = ref(null)
const ticketWithoutContact = ref(false)

const metadata = ref({
  departments: [],
  staff: [],
  clients: [],
  contacts: [],
  projects: [],
  services: [],
})

const form = reactive({
  subject: '',
  contact_id: null,
  client_id: null,
  priority: 'Medium',
  status: 'Open',
  department_id: null,
  message: '',
  assigned_to: null,
  tags: '',
  service_id: null,
  project_id: null,
  cc: '',
  predefined_reply: '',
  kb_link: '',
  tagList: [],
  email: '',
  contact_name: '',
  contact_number: '',
})

const kanbanColumns = [
  { title: 'Open', status: 'Open', color: '#7367F0' },
  { title: 'In Progress', status: 'In Progress', color: '#00CFE8' },
  { title: 'Answered', status: 'Answered', color: '#28C76F' },
  { title: 'On Hold', status: 'On Hold', color: '#FF9F43' },
  { title: 'Closed', status: 'Closed', color: '#A8AAAE' },
]

const summaryCards = computed(() => [
  {
    label: 'Open',
    value: stats.value.open || 0,
    color: '#7367F0',
    textColor: '#7367F0',
    bgLight: 'rgba(115, 103, 240, 0.12)',
    statusValue: 'Open',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="10"/></svg>',
  },
  {
    label: 'In Progress',
    value: stats.value.in_progress || 0,
    color: '#00CFE8',
    textColor: '#00CFE8',
    bgLight: 'rgba(0, 207, 232, 0.12)',
    statusValue: 'In Progress',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  },
  {
    label: 'Answered',
    value: stats.value.answered || 0,
    color: '#28C76F',
    textColor: '#28C76F',
    bgLight: 'rgba(40, 199, 111, 0.12)',
    statusValue: 'Answered',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><polyline points="20 6 9 17 4 12"/></svg>',
  },
  {
    label: 'On Hold',
    value: stats.value.on_hold || 0,
    color: '#FF9F43',
    textColor: '#FF9F43',
    bgLight: 'rgba(255, 159, 67, 0.12)',
    statusValue: 'On Hold',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>',
  },
  {
    label: 'Closed',
    value: stats.value.closed || 0,
    color: '#A8AAAE',
    textColor: '#4B465C',
    bgLight: 'rgba(168, 170, 174, 0.12)',
    statusValue: 'Closed',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><polyline points="9 18 15 12 9 6"/></svg>',
  },
])

watch(() => form.contact_id, (newVal) => {
  if (newVal) {
    const contact = metadata.value.contacts.find(c => c.id === newVal)
    if (contact) {
      selectedContact.value = contact
      form.client_id = contact.client_id
      const client = metadata.value.clients.find(c => c.id === contact.client_id)
      selectedClient.value = client
    }
  } else {
    selectedContact.value = null
    selectedClient.value = null
    form.client_id = null
  }
})

const filteredProjects = computed(() => {
  if (!form.client_id) return []
  return metadata.value.projects.filter(p => p.client_id === form.client_id)
})

const kbArticles = ref([])

const predefinedReplies = [
  { id: 'reply1', title: 'Thank you for contacting us...', content: 'Thank you for contacting us. We have received your ticket and our support team is looking into it. We will get back to you as soon as possible.' },
  { id: 'reply2', title: 'We have received your request...', content: 'We have received your request. A support representative will review your ticket shortly. Thank you for your patience.' },
  { id: 'reply3', title: 'SLA / Urgency update', content: 'We are prioritizing your request as per our Service Level Agreement (SLA). Our team is working on a resolution and will provide an update within the next hour.' },
]

async function loadKbArticles() {
  try {
    const res = await axios.get(`${BASE}/kb-articles`, { params: { status: 'published', per_page: 100 } })
    kbArticles.value = res.data.articles?.data || []
  } catch {}
}

function handlePredefinedReplyChange(e) {
  const val = e.target.value
  if (!val) return
  const reply = predefinedReplies.find(r => r.id === val)
  if (reply) form.message = form.message ? form.message + '\n' + reply.content : reply.content
  form.predefined_reply = ''
}

function handleKbLinkChange(e) {
  const articleId = e.target.value
  if (!articleId) return
  const article = kbArticles.value.find(a => a.id == articleId)
  if (article) {
    const path = window.location.origin + '/knowledge-base/article/' + article.id
    const link = `\nKnowledge Base Article: ${article.title} - ${path}`
    form.message = form.message ? form.message + link : link.trim()
  }
  form.kb_link = ''
}

async function loadMetadata() {
  try {
    const res = await axios.get(`${BASE}/tickets/metadata`)
    metadata.value = res.data
  } catch {}
}

async function load() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: perPage.value, search: search.value }
    if (statusFilter.value) params.status = statusFilter.value
    if (priorityFilter.value) params.priority = priorityFilter.value
    if (deptFilter.value) params.department_id = deptFilter.value
    const res = await axios.get(`${BASE}/tickets`, { params })
    tickets.value = res.data.tickets?.data || []
    totalPages.value = res.data.tickets?.last_page || 1
    stats.value = res.data.stats || {}
  } catch {
    tickets.value = []
    stats.value = { total: 0, open: 0, in_progress: 0, answered: 0, on_hold: 0, closed: 0 }
  } finally {
    loading.value = false
  }
}

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; load() }, 350)
}

function handleFileChange(event) {
  const file = event.target.files[0]
  if (file) {
    attachmentFile.value = file
    attachmentFileName.value = file.name
  }
}

function parseTags(str) {
  if (!str) return []
  try {
    const parsed = JSON.parse(str)
    return Array.isArray(parsed) ? parsed : [parsed]
  } catch {
    return str.split(',').map(t => t.trim()).filter(Boolean)
  }
}

function ticketsByStatus(status) {
  return tickets.value.filter(t => t.status === status)
}

function statusBadgeClass(s) {
  return {
    'Open': 'bg-[#7367F0]/10 text-[#7367F0] border border-[#7367F0]/20',
    'In Progress': 'bg-[#00CFE8]/10 text-[#00CFE8] border border-[#00CFE8]/20',
    'Answered': 'bg-[#28C76F]/10 text-[#28C76F] border border-[#28C76F]/20',
    'On Hold': 'bg-[#FF9F43]/10 text-[#FF9F43] border border-[#FF9F43]/20',
    'Closed': 'bg-[#A8AAAE]/10 text-[#6F6B7D] border border-[#A8AAAE]/20',
  }[s] || 'bg-[#F8F7FA] text-[#6F6B7D] border border-[#DBDADE]'
}

function statusDotClass(s) {
  return {
    'Open': 'bg-[#7367F0]',
    'In Progress': 'bg-[#00CFE8]',
    'Answered': 'bg-[#28C76F]',
    'On Hold': 'bg-[#FF9F43]',
    'Closed': 'bg-[#A8AAAE]',
  }[s] || 'bg-[#6F6B7D]'
}

function priClass(p) {
  return {
    Low: 'bg-[#F1F0F2] text-[#6F6B7D]',
    Medium: 'bg-[#7367F0]/10 text-[#7367F0] border border-[#7367F0]/20',
    High: 'bg-[#FF9F43]/10 text-[#FF9F43] border border-[#FF9F43]/20',
    Urgent: 'bg-rose-50 text-rose-600 border border-rose-200',
  }[p] || 'bg-[#7367F0]/10 text-[#7367F0]'
}

function fmtDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

function getServiceName(serviceId) {
  if (!serviceId) return '—'
  const service = metadata.value.services?.find(s => s.id === serviceId)
  return service?.name || '—'
}

function addTag() {
  const val = tagInput.value.replace(/,/g, '').trim()
  if (val && !form.tagList.includes(val)) {
    form.tagList.push(val)
    form.tags = form.tagList.join(',')
  }
  tagInput.value = ''
}

function filterBy(status) {
  statusFilter.value = (statusFilter.value === status) ? '' : status
  page.value = 1
  load()
}

function onDragStart(t) {
  dragTicketId.value = t.id
}

async function onDrop(newStatus) {
  const id = dragTicketId.value
  const ticket = tickets.value.find(t => t.id === id)
  if (id && ticket && ticket.status !== newStatus) {
    try {
      await axios.put(`${BASE}/tickets/${id}`, { status: newStatus })
      ticket.status = newStatus
      message.success(`Ticket #${id} moved to ${newStatus}`)
    } catch {
      message.error('Failed to update ticket status')
    }
  }
  dragTicketId.value = null
  dragCol.value = null
}

function openCreateModal() {
  editingTicket.value = null
  Object.assign(form, {
    subject: '', contact_id: null, client_id: null, priority: 'Medium', status: 'Open',
    department_id: null, message: '', assigned_to: null, tags: '', service_id: null,
    project_id: null, cc: '', predefined_reply: '', kb_link: '', tagList: [],
    email: '', contact_name: '', contact_number: '',
  })
  tagInput.value = ''
  attachmentFile.value = null
  attachmentFileName.value = ''
  selectedContact.value = null
  selectedClient.value = null
  ticketWithoutContact.value = false
  showModal.value = true
}

function openCreateForStatus(status) {
  openCreateModal()
  form.status = status
}

function editTicket(t) {
  editingTicket.value = t
  const tagList = parseTags(t.tags)
  Object.assign(form, {
    subject: t.subject,
    contact_id: t.contact_id || null,
    client_id: t.client_id || null,
    priority: t.priority || 'Medium',
    status: t.status || 'Open',
    department_id: t.department_id || null,
    message: t.message || '',
    assigned_to: t.assigned_to || null,
    tags: t.tags || '',
    service_id: t.service_id || null,
    project_id: t.project_id || null,
    cc: t.cc || '',
    predefined_reply: '',
    kb_link: '',
    tagList,
    email: t.email || '',
    contact_name: t.contact_name || '',
    contact_number: t.contact_number || '',
  })
  tagInput.value = ''
  attachmentFile.value = null
  attachmentFileName.value = ''
  ticketWithoutContact.value = !t.contact_id
  showModal.value = true
}

function viewTicket(t) {
  viewingTicket.value = t
  showViewDrawer.value = true
}

async function sendReply() {
  if (!replyMessage.value.trim()) return message.warning('Please enter a reply message')
  sendingReply.value = true
  try {
    const res = await axios.post(`${BASE}/tickets/${viewingTicket.value.id}/replies`, {
      message: replyMessage.value,
      is_admin_reply: true,
    })
    if (!viewingTicket.value.replies) viewingTicket.value.replies = []
    viewingTicket.value.replies.push(res.data.reply || {
      id: Date.now(),
      message: replyMessage.value,
      created_at: new Date().toISOString(),
      is_admin_reply: true,
      name: authStore.user?.name || 'Staff Support',
    })
    replyMessage.value = ''
    message.success('Reply posted successfully')
  } catch {
    message.error('Failed to post reply')
  } finally {
    sendingReply.value = false
  }
}

async function saveTicket() {
  if (!form.subject) return message.error('Subject is required')
  if (!form.department_id) return message.error('Department is required')
  if (!form.message) return message.error('Message is required')

  saving.value = true
  try {
    const payload = { ...form }
    if (editingTicket.value) {
      await axios.put(`${BASE}/tickets/${editingTicket.value.id}`, payload)
      message.success('Ticket updated successfully')
    } else {
      await axios.post(`${BASE}/tickets`, payload)
      message.success('Ticket created successfully')
    }
    closeModal()
    load()
  } catch (err) {
    message.error('Failed to save ticket')
  } finally {
    saving.value = false
  }
}

async function deleteTicket(t) {
  if (!confirm(`Delete ticket "${t.subject}"?`)) return
  try {
    await axios.delete(`${BASE}/tickets/${t.id}`)
    message.success('Ticket deleted successfully')
    load()
  } catch {
    message.error('Failed to delete ticket')
  }
}

function closeModal() {
  showModal.value = false
  editingTicket.value = null
}

onMounted(() => {
  load()
  loadMetadata()
  loadKbArticles()
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
.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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

:deep(.vuexy-ticket-drawer .ant-drawer-header),
:deep(.vuexy-ticket-view-drawer .ant-drawer-header) {
  padding: 16px 24px;
  border-bottom: 1px solid #F1F0F2;
  background-color: #FFFFFF;
}
:deep(.vuexy-ticket-drawer .ant-drawer-body),
:deep(.vuexy-ticket-view-drawer .ant-drawer-body) {
  padding: 24px;
  background-color: #F8F7FA;
}
:deep(.vuexy-ticket-drawer .ant-drawer-footer),
:deep(.vuexy-ticket-view-drawer .ant-drawer-footer) {
  padding: 12px 24px;
  border-top: 1px solid #F1F0F2;
  background-color: #FFFFFF;
}
</style>
