<template>
  <div class="tk-page">
    <!-- HEADER -->
    <div class="tk-header">
      <div>
        <h1 class="tk-title">Support Tickets</h1>
        <p class="tk-subtitle">Manage customer support requests</p>
      </div>
      <div class="tk-header-actions">
        <div class="tk-view-toggle">
          <button class="tk-view-btn" :class="{ active: currentView === 'kanban' }" @click="currentView = 'kanban'" title="Kanban">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
          </button>
          <button class="tk-view-btn" :class="{ active: currentView === 'table' }" @click="currentView = 'table'" title="Table">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
          </button>
        </div>
        <button class="tk-btn-primary" @click="openCreateModal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          New Ticket
        </button>
        <button class="tk-btn-analytics" @click="showWeeklyAnalytics = true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          Weekly Analytics
        </button>
      </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="tk-stats-row">
      <div v-for="card in summaryCards" :key="card.label" class="tk-stat-card" :style="{ borderLeftColor: card.color }" @click="filterBy(card.statusValue)">
        <div class="tk-stat-icon" :style="{ background: card.bg }" v-html="card.icon"></div>
        <div class="tk-stat-info">
          <div class="tk-stat-val" :style="{ color: card.color }">{{ card.value }}</div>
          <div class="tk-stat-label">{{ card.label }}</div>
        </div>
      </div>
    </div>

    <!-- ====== KANBAN VIEW ====== -->
    <div v-if="currentView === 'kanban'" class="tk-kanban">
      <div v-for="col in kanbanColumns" :key="col.status" class="tk-kanban-col">
        <div class="tk-kanban-hd" :style="{ borderTopColor: col.color }">
          <span class="tk-kanban-icon" v-html="col.icon"></span>
          <span class="tk-kanban-title">{{ col.title }}</span>
          <span class="tk-kanban-count">{{ ticketsByStatus(col.status).length }}</span>
        </div>
        <div class="tk-kanban-cards" :class="{ 'tk-drag-over': dragCol === col.status }"
          @dragover.prevent="dragCol = col.status"
          @dragenter.prevent
          @dragleave="dragCol = null"
          @drop="onDrop(col.status)">
          <div v-for="t in ticketsByStatus(col.status)" :key="t.id" class="tk-kanban-card"
            draggable="true"
            @dragstart="onDragStart(t)"
            @dragend="dragCol = null">
            <div class="tk-kc-hd">
              <span class="tk-pri" :class="priClass(t.priority)">{{ t.priority }}</span>
              <span class="tk-kc-id">#{{ t.id }}</span>
            </div>
            <div class="tk-kc-title" @click="viewTicket(t)">{{ t.subject }}</div>
            <div class="tk-kc-meta" v-if="t.contact">{{ t.contact.firstname }} {{ t.contact.lastname }}</div>
            <div v-if="t.department" class="tk-kc-dept">{{ t.department.name }}</div>
            <div class="tk-kc-ft">
              <span class="tk-kc-date">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                {{ fmtDate(t.created_at) }}
              </span>
              <div class="tk-kc-avatar" :title="t.assignee?.name || 'Unassigned'">
                {{ t.assignee ? t.assignee.name.charAt(0).toUpperCase() : '?' }}
              </div>
            </div>
          </div>
          <div v-if="!ticketsByStatus(col.status).length" class="tk-kanban-empty"
            @dragover.prevent="dragCol = col.status"
            @dragenter.prevent
            @dragleave="dragCol = null"
            @drop="onDrop(col.status)">Drop here</div>
        </div>
        <button class="tk-kanban-add" @click="openCreateForStatus(col.status)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add
        </button>
      </div>
    </div>

    <!-- ====== TABLE VIEW ====== -->
    <div v-else>
      <div class="tk-filters">
        <div class="tk-filters-left">
          <select class="tk-filter-select" v-model="perPage" @change="load">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
          <select class="tk-filter-select" v-model="priorityFilter" @change="load">
            <option value="">All Priority</option>
            <option value="Low">Low</option>
            <option value="Medium">Medium</option>
            <option value="High">High</option>
            <option value="Urgent">Urgent</option>
          </select>
          <select class="tk-filter-select" v-model="deptFilter" @change="load">
            <option value="">All Departments</option>
            <option v-for="d in metadata.departments" :key="d.id" :value="d.id">{{ d.name }}</option>
          </select>
        </div>
        <div class="tk-filters-right">
          <div class="tk-search-wrap">
            <svg class="tk-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input v-model="search" placeholder="Search tickets..." class="tk-search-input" @input="onSearch" />
          </div>
        </div>
      </div>

      <div class="tk-table-wrap">
        <table class="tk-table">
          <thead>
            <tr>
              <th style="width:44px;">#</th>
              <th>Subject</th>
              <th>Tags</th>
              <th>Department</th>
              <th>Service</th>
              <th>Contact</th>
              <th style="width:100px;">Status</th>
              <th style="width:80px;">Priority</th>
              <th style="width:100px;">Last Reply</th>
              <th style="width:100px;">Created</th>
              <th style="width:140px;"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="11" class="tk-empty-cell">
                <svg class="animate-spin" fill="none" viewBox="0 0 24 24" width="18" height="18"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              </td>
            </tr>
            <tr v-for="(t, idx) in tickets" :key="t.id" class="tk-row">
              <td class="tk-cell-muted">{{ idx + 1 + (page - 1) * (+perPage) }}</td>
              <td>
                <div class="tk-name-cell">
                  <span class="tk-name">{{ t.subject }}</span>
                </div>
              </td>
              <td>
                <div class="tk-tags">
                  <span v-if="!t.tags" class="text-slate-300">—</span>
                  <span v-for="tag in parseTags(t.tags)" :key="tag" class="tk-tag">{{ tag }}</span>
                </div>
              </td>
              <td class="tk-cell-muted">{{ t.department?.name || '—' }}</td>
              <td class="tk-cell-muted">{{ getServiceName(t.service_id) }}</td>
              <td class="tk-cell-muted">{{ t.contact ? (t.contact.firstname + ' ' + (t.contact.lastname || '')) : '—' }}</td>
              <td><span class="tk-status-badge" :class="statusClass(t.status)">{{ t.status }}</span></td>
              <td><span class="tk-pri" :class="priClass(t.priority)">{{ t.priority }}</span></td>
              <td class="tk-cell-muted">{{ fmtDate(t.last_reply_at) }}</td>
              <td class="tk-cell-muted">{{ fmtDate(t.created_at) }}</td>
              <td>
                <div class="tk-row-actions">
                  <button class="tk-act-btn-icon" @click="viewTicket(t)" title="View">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </button>
                  <button class="tk-act-btn-icon" @click="editTicket(t)" title="Edit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"/></svg>
                  </button>
                  <button class="tk-act-btn-icon hover:text-rose-600" @click="deleteTicket(t)" title="Delete">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!loading && !tickets.length">
              <td colspan="11" class="tk-empty-cell">
                <svg viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" width="32" height="32"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                <p class="text-slate-400 text-sm mt-2">No tickets found</p>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="tk-pagination" v-if="totalPages > 1">
          <span class="tk-pg-info">Showing {{ tickets.length }} of {{ totalPages * (+perPage) }} entries</span>
          <div class="tk-pg-btns">
            <button class="tk-pg-btn" :disabled="page <= 1" @click="page--; load()">Previous</button>
            <button class="tk-pg-btn" :disabled="page >= totalPages" @click="page++; load()">Next</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="tk-modal-overlay" @click.self="closeModal">
        <div class="tk-modal-box tk-modal-wide">
          <div class="tk-modal-hd">
            <div class="tk-modal-hd-left">
              <div class="tk-modal-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
              </div>
              <div>
                <div class="tk-modal-title">{{ editingTicket ? 'Edit Ticket' : 'Ticket Information' }}</div>
                <div class="tk-modal-sub">Fill in the details below</div>
              </div>
            </div>
            <button class="tk-modal-close" @click="closeModal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>

          <div class="tk-modal-body">
            <div class="tk-form-grid">
              <div class="tk-form-group span-2">
                <label class="tk-radio-group-label">Ticket Information</label>
                <div class="tk-radio-group">
                  <label class="tk-radio-btn" :class="{ active: !ticketWithoutContact }">
                    <input type="radio" name="ticketMode" :value="false" v-model="ticketWithoutContact" />
                    <span class="tk-radio-dot"></span>
                    Ticket to contact
                  </label>
                  <label class="tk-radio-btn" :class="{ active: ticketWithoutContact }">
                    <input type="radio" name="ticketMode" :value="true" v-model="ticketWithoutContact" />
                    <span class="tk-radio-dot"></span>
                    Ticket without contact
                  </label>
                </div>
              </div>

              <div class="tk-form-group span-2">
                <label>Subject <span class="tk-req">*</span></label>
                <input v-model="form.subject" placeholder="Enter ticket subject" class="tk-input" />
              </div>

              <template v-if="ticketWithoutContact">
                <div class="tk-form-group">
                  <label>Contact <span class="tk-req">*</span></label>
                  <select v-model="form.contact_id" class="tk-input">
                    <option :value="null">Select Contact</option>
                    <option v-for="c in metadata.contacts" :key="c.id" :value="c.id">
                      {{ c.firstname }} {{ c.lastname }}
                    </option>
                  </select>
                </div>
                <div class="tk-form-group">
                  <label>Email address</label>
                  <input v-model="form.email" placeholder="contact@example.com" class="tk-input" />
                </div>
                <div class="tk-form-group">
                  <label>Name</label>
                  <input v-model="form.contact_name" placeholder="Contact name" class="tk-input" />
                </div>
                <div class="tk-form-group">
                  <label>Contact number</label>
                  <input v-model="form.contact_number" placeholder="Phone number" class="tk-input" />
                </div>
              </template>
              <template v-else>
                <div class="tk-form-group">
                  <label>Name</label>
                  <input :value="selectedContact ? selectedContact.firstname + ' ' + (selectedContact.lastname || '') : ''" class="tk-input tk-input-readonly" readonly placeholder="Auto-filled from contact" />
                </div>
                <div class="tk-form-group">
                  <label>Email address</label>
                  <input :value="selectedContact ? selectedContact.email : ''" class="tk-input tk-input-readonly" readonly placeholder="Auto-filled from contact" />
                </div>
                <div class="tk-form-group">
                  <label>Contact</label>
                  <select v-model="form.contact_id" class="tk-input">
                    <option :value="null">Select Contact</option>
                    <option v-for="c in metadata.contacts" :key="c.id" :value="c.id">
                      {{ c.firstname }} {{ c.lastname }}
                    </option>
                  </select>
                </div>
                <div class="tk-form-group">
                  <label>Customer (Auto-filled)</label>
                  <input :value="selectedClient ? selectedClient.company : ''" class="tk-input tk-input-readonly" readonly placeholder="Auto-filled from contact" />
                </div>
              </template>

              <div class="tk-form-group">
                <label>Department <span class="tk-req">*</span></label>
                <select v-model="form.department_id" class="tk-input">
                  <option :value="null">Select Department</option>
                  <option v-for="d in metadata.departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                </select>
              </div>

              <div class="tk-form-group">
                <label>CC</label>
                <input v-model="form.cc" placeholder="cc@example.com" class="tk-input" />
              </div>

              <div class="tk-form-group span-2">
                <label>Tags</label>
                <div class="tk-tag-input-wrap">
                  <div class="tk-tag-list">
                    <span v-for="(tag, i) in form.tagList" :key="i" class="tk-tag-pill">
                      {{ tag }}
                      <button class="tk-tag-pill-del" @click="form.tagList.splice(i, 1); form.tags = form.tagList.join(',')">&times;</button>
                    </span>
                    <input v-model="tagInput" placeholder="Type tag and press Enter" class="tk-tag-field" @keydown.enter.prevent="addTag" @keydown.,.prevent="addTag" />
                  </div>
                </div>
              </div>

              <div class="tk-form-group">
                <label>Assign ticket</label>
                <select v-model="form.assigned_to" class="tk-input">
                  <option :value="null">Select Staff (default: you)</option>
                  <option v-for="s in metadata.staff" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>

              <div class="tk-form-group">
                <label>Priority</label>
                <select v-model="form.priority" class="tk-input">
                  <option value="Low">Low</option>
                  <option value="Medium">Medium</option>
                  <option value="High">High</option>
                  <option value="Urgent">Urgent</option>
                </select>
              </div>

              <div class="tk-form-group" v-if="editingTicket">
                <label>Status</label>
                <select v-model="form.status" class="tk-input">
                  <option value="Open">Open</option>
                  <option value="In Progress">In Progress</option>
                  <option value="Answered">Answered</option>
                  <option value="On Hold">On Hold</option>
                  <option value="Closed">Closed</option>
                </select>
              </div>

              <div class="tk-form-group">
                <label>Service</label>
                <select v-model="form.service_id" class="tk-input">
                  <option :value="null">Select Service</option>
                  <option v-for="s in metadata.services" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>

              <div class="tk-form-group" v-if="!editingTicket">
                <label>Project</label>
                <select v-model="form.project_id" class="tk-input" :disabled="!form.client_id">
                  <option :value="null">Select Project</option>
                  <option v-for="p in filteredProjects" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
              </div>

              <div class="tk-form-group span-2">
                <label>Ticket Body <span class="tk-req">*</span></label>
                <div class="tk-editor-toolbar">
                  <select v-model="form.predefined_reply" @change="handlePredefinedReplyChange">
                    <option value="">Insert predefined reply</option>
                    <option v-for="r in predefinedReplies" :key="r.id" :value="r.id">{{ r.title }}</option>
                  </select>
                  <select v-model="form.kb_link" @change="handleKbLinkChange">
                    <option value="">Insert knowledge base link</option>
                    <option v-for="a in kbArticles" :key="a.id" :value="a.id">{{ a.title }}</option>
                  </select>
                </div>
                <textarea v-model="form.message" rows="6" placeholder="Describe the issue in detail..." class="tk-input tk-textarea"></textarea>
              </div>

              <div class="tk-form-group span-2">
                <label>Attachments</label>
                <div class="tk-file-upload">
                  <input type="file" @change="handleFileChange" class="tk-file-input" id="tk-file-input" />
                  <label for="tk-file-input" class="tk-file-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Choose File
                  </label>
                  <span class="tk-file-name">{{ attachmentFileName || 'No file chosen' }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="tk-modal-ft">
            <button class="tk-btn-secondary" @click="closeModal">Cancel</button>
            <button class="tk-btn-primary" @click="saveTicket" :disabled="saving">
              {{ saving ? 'Saving...' : (editingTicket ? 'Save Changes' : 'Create Ticket') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- View Ticket Modal -->
    <Teleport to="body">
      <div v-if="viewingTicket" class="tk-modal-overlay" @click.self="viewingTicket = null">
        <div class="tk-modal-box tk-modal-wide">
          <div class="tk-modal-hd">
            <div class="tk-modal-hd-left">
              <div class="tk-modal-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
              </div>
              <div>
                <div class="tk-modal-title">Ticket #{{ viewingTicket.id }}</div>
                <div class="tk-modal-sub">{{ viewingTicket.subject }}</div>
              </div>
            </div>
            <button class="tk-modal-close" @click="viewingTicket = null">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>

          <div class="tk-modal-body">
            <div class="tk-view-meta">
              <span class="tk-pri" :class="priClass(viewingTicket.priority)">{{ viewingTicket.priority }}</span>
              <span class="tk-status-badge" :class="statusClass(viewingTicket.status)">{{ viewingTicket.status }}</span>
              <span class="tk-view-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                {{ fmtDate(viewingTicket.created_at) }}
              </span>
              <span class="tk-view-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                {{ viewingTicket.client?.company || 'N/A' }}
              </span>
            </div>

            <div class="tk-view-grid">
              <div class="tk-view-item">
                <span class="tk-view-lbl">Contact</span>
                <span class="tk-view-val">{{ viewingTicket.contact ? (viewingTicket.contact.firstname + ' ' + (viewingTicket.contact.lastname || '')) : '—' }}</span>
              </div>
              <div class="tk-view-item">
                <span class="tk-view-lbl">Email</span>
                <span class="tk-view-val">{{ viewingTicket.contact?.email || '—' }}</span>
              </div>
              <div class="tk-view-item">
                <span class="tk-view-lbl">Department</span>
                <span class="tk-view-val">{{ viewingTicket.department?.name || '—' }}</span>
              </div>
              <div class="tk-view-item">
                <span class="tk-view-lbl">Assigned To</span>
                <span class="tk-view-val">{{ viewingTicket.assignee?.name || '—' }}</span>
              </div>
              <div class="tk-view-item">
                <span class="tk-view-lbl">Service</span>
                <span class="tk-view-val">{{ getServiceName(viewingTicket.service_id) }}</span>
              </div>
              <div class="tk-view-item">
                <span class="tk-view-lbl">CC</span>
                <span class="tk-view-val">{{ viewingTicket.cc || '—' }}</span>
              </div>
              <div class="tk-view-item" v-if="viewingTicket.tags">
                <span class="tk-view-lbl">Tags</span>
                <span class="tk-view-val">
                  <span v-for="tag in parseTags(viewingTicket.tags)" :key="tag" class="tk-tag" style="margin:0 3px 3px 0">{{ tag }}</span>
                </span>
              </div>
            </div>

            <div class="tk-message-bubble tk-message-original">
              <div class="tk-bubble-hd">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                Original Message
              </div>
              <div class="tk-bubble-body">{{ viewingTicket.message }}</div>
            </div>

            <div class="tk-replies-section">
              <div class="tk-replies-hd">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
                Replies ({{ viewingTicket.replies?.length || 0 }})
              </div>
              <div v-for="reply in viewingTicket.replies" :key="reply.id"
                   class="tk-message-bubble" :class="reply.is_admin_reply ? 'tk-message-admin' : 'tk-message-client'">
                <div class="tk-bubble-hd">
                  <span class="tk-bubble-author">{{ reply.name || reply.user?.name || 'Support' }}</span>
                  <span class="tk-bubble-time">{{ fmtDate(reply.created_at) }}</span>
                  <span v-if="reply.is_admin_reply" class="tk-bubble-tag">Staff</span>
                </div>
                <div class="tk-bubble-body">{{ reply.message }}</div>
              </div>

              <div class="tk-reply-form">
                <textarea v-model="replyMessage" rows="3" placeholder="Type your reply..." class="tk-input tk-textarea"></textarea>
                <button class="tk-btn-primary" @click="sendReply" :disabled="sendingReply">
                  {{ sendingReply ? 'Sending...' : 'Send Reply' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Weekly Analytics Modal -->
    <Teleport to="body">
      <div v-if="showWeeklyAnalytics" class="tk-modal-overlay" @click.self="showWeeklyAnalytics = false">
        <div class="tk-modal-box tk-modal-narrow">
          <div class="tk-modal-hd">
            <div class="tk-modal-hd-left">
              <div class="tk-modal-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
              </div>
              <div>
                <div class="tk-modal-title">Weekly Tickets Analytics</div>
                <div class="tk-modal-sub">Tickets created per day over the last 7 days</div>
              </div>
            </div>
            <button class="tk-modal-close" @click="showWeeklyAnalytics = false">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <div class="tk-modal-body">
            <div class="tk-weekly-chart">
              <div class="tk-chart-yaxis">
                <span>{{ maxWeeklyCount }}</span>
                <span>{{ Math.round(maxWeeklyCount / 2) }}</span>
                <span>0</span>
              </div>
              <div class="tk-chart-bars">
                <div v-for="day in weeklyStats" :key="day.date" class="tk-chart-col">
                  <div class="tk-chart-bar-wrap">
                    <div class="tk-chart-bar" :style="{ height: barHeight(day.count) }" :title="day.count + ' tickets'"></div>
                  </div>
                  <div class="tk-chart-lbl">{{ day.label }}</div>
                  <div class="tk-chart-val">{{ day.count }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../../store/authStore'

const BASE = '/api'
const route = useRoute()
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
const showWeeklyAnalytics = ref(false)
const weeklyStats = ref([])

const metadata = ref({
  departments: [], staff: [], clients: [], contacts: [], projects: [], services: [],
})

const form = reactive({
  subject: '', contact_id: null, client_id: null, priority: 'Medium', status: 'Open',
  department_id: null, message: '', assigned_to: null, tags: '', service_id: null,
  project_id: null, cc: '', predefined_reply: '', kb_link: '', tagList: [],
  email: '', contact_name: '', contact_number: '',
})

const kanbanColumns = [
  { title: 'Open', status: 'Open', color: '#3b82f6', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/></svg>' },
  { title: 'In Progress', status: 'In Progress', color: '#f59e0b', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" width="14" height="14"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>' },
  { title: 'Answered', status: 'Answered', color: '#10b981', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>' },
  { title: 'On Hold', status: 'On Hold', color: '#6366f1', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" width="14" height="14"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>' },
  { title: 'Closed', status: 'Closed', color: '#94a3b8', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>' },
]

const summaryCards = computed(() => [
  { label: 'Open', value: stats.value.open || 0, color: '#3b82f6', bg: '#eff6ff', statusValue: 'Open',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/></svg>' },
  { label: 'In Progress', value: stats.value.in_progress || 0, color: '#f59e0b', bg: '#fffbeb', statusValue: 'In Progress',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>' },
  { label: 'Answered', value: stats.value.answered || 0, color: '#10b981', bg: '#f0fdf4', statusValue: 'Answered',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polyline points="20 6 9 17 4 12"/></svg>' },
  { label: 'On Hold', value: stats.value.on_hold || 0, color: '#6366f1', bg: '#f5f3ff', statusValue: 'On Hold',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>' },
  { label: 'Closed', value: stats.value.closed || 0, color: '#94a3b8', bg: '#f8fafc', statusValue: 'Closed',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polyline points="9 18 15 12 9 6"/></svg>' },
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
  try { const res = await axios.get(`${BASE}/tickets/metadata`); metadata.value = res.data } catch {}
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
    const departments = [{ id: 1, name: 'Sales' }, { id: 2, name: 'Abuse' }, { id: 3, name: 'Marketing' }]
    const contacts = [
      { id: 1, firstname: 'Marcel', lastname: 'Hills', email: 'marcel@example.com', client_id: 1 },
      { id: 2, firstname: 'Nicholas', lastname: 'Treutel', email: 'nicholas@example.com', client_id: 2 },
      { id: 3, firstname: 'Justus', lastname: 'Lindgren', email: 'justus@example.com', client_id: 3 },
    ]
    const staff = [{ id: 1, name: 'Tre Stamm' }]
    const deptMap = { 1: departments[0], 2: departments[1], 3: departments[2] }
    tickets.value = [
      { id: 1, subject: 'March Hare. Alice was soon.', priority: 'High', status: 'Open', created_at: '2026-06-17T12:00:17', last_reply_at: null, tags: '', cc: '', message: 'Need help with account access.', department: departments[0], department_id: 1, contact: contacts[0], contact_id: 1, client_id: 1, assignee: staff[0], assigned_to: 1, service_id: null, project_id: null, client: { company: 'Beer-Wehner' } },
      { id: 2, subject: 'But the snail replied "Too far, too far!".', priority: 'Medium', status: 'In Progress', created_at: '2026-06-17T12:00:17', last_reply_at: null, tags: 'bug', cc: '', message: 'Having trouble with the interface.', department: departments[1], department_id: 1, contact: contacts[1], contact_id: 2, client_id: 2, assignee: staff[0], assigned_to: 1, service_id: null, project_id: null, client: { company: 'Beahan Ltd' } },
      { id: 3, subject: 'So Bill\'s got the other--Bill!.', priority: 'Medium', status: 'Answered', created_at: '2026-06-17T12:00:17', last_reply_at: null, tags: 'feature', cc: '', message: 'Request for new functionality.', department: departments[2], department_id: 3, contact: contacts[2], contact_id: 3, client_id: 3, assignee: staff[0], assigned_to: 1, service_id: null, project_id: null, client: { company: 'Lind-Walsh' } },
      { id: 4, subject: 'Alice, and she had hoped a fan and.', priority: 'High', status: 'On Hold', created_at: '2026-06-17T12:00:17', last_reply_at: null, tags: '', cc: '', message: 'Waiting for customer response.', department: departments[0], department_id: 1, contact: { id: 4, firstname: 'Vance', lastname: 'Leffler', email: 'vance@example.com' }, contact_id: 4, client_id: 1, assignee: null, assigned_to: null, service_id: null, project_id: null, client: { company: 'McCullough-Hudson' } },
      { id: 5, subject: 'Alice thought to herself, Now, what.', priority: 'High', status: 'Closed', created_at: '2026-06-17T12:00:17', last_reply_at: null, tags: 'done', cc: '', message: 'Issue has been resolved.', department: departments[0], department_id: 1, contact: { id: 5, firstname: 'Nestor', lastname: 'Steuber', email: 'nestor@example.com' }, contact_id: 5, client_id: 1, assignee: null, assigned_to: null, service_id: null, project_id: null, client: { company: 'Hodkiewicz PLC' } },
    ]
    stats.value = { total: 5, open: 1, in_progress: 1, answered: 1, on_hold: 1, closed: 1 }
    totalPages.value = 1
  } finally { loading.value = false }
}

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; load() }, 350)
}

function handleFileChange(event) {
  const file = event.target.files[0]
  if (file) { attachmentFile.value = file; attachmentFileName.value = file.name }
  else { attachmentFile.value = null; attachmentFileName.value = '' }
}

function parseTags(str) {
  if (!str) return []
  try { return JSON.parse(str) } catch { return str.split(',').map(t => t.trim()).filter(Boolean) }
}

function addTag() {
  const val = tagInput.value.replace(/,/g, '').trim()
  if (val && !form.tagList.includes(val)) { form.tagList.push(val); form.tags = form.tagList.join(',') }
  tagInput.value = ''
}

function filterBy(status) {
  if (currentView.value !== 'kanban') {
    statusFilter.value = status; priorityFilter.value = ''; deptFilter.value = ''
    page.value = 1
    load()
  }
}

function ticketsByStatus(status) { return tickets.value.filter(t => t.status === status) }
function statusClass(s) { return { Open: 'open', 'In Progress': 'in-progress', Answered: 'answered', 'On Hold': 'on-hold', Closed: 'closed' }[s] || '' }
function priClass(p) { return { Low: 'low', Medium: 'med', High: 'high', Urgent: 'urg' }[p] || 'med' }
function fmtDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }
function getServiceName(id) { if (!id) return '—'; const s = metadata.value.services.find(s => s.id === id); return s ? s.name : '—' }

const maxWeeklyCount = computed(() => {
  const counts = weeklyStats.value.map(d => d.count)
  return Math.max(...counts, 1)
})
function barHeight(count) {
  return Math.max((count / maxWeeklyCount.value) * 100, 4) + '%'
}
async function loadWeeklyStats() {
  try { const r = await axios.get(`${BASE}/tickets/weekly-stats`); weeklyStats.value = r.data.days || [] }
  catch {
    const days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']
    const today = new Date()
    weeklyStats.value = Array.from({length:7}, (_, i) => {
      const d = new Date(today); d.setDate(d.getDate() - (6 - i))
      return { date: d.toISOString().slice(0,10), label: days[d.getDay()], count: Math.floor(Math.random() * 5) + 1 }
    })
  }
}

watch(showWeeklyAnalytics, (v) => { if (v) loadWeeklyStats() })

function onDragStart(t) { dragTicketId.value = t.id }
function onDrop(newStatus) {
  const id = dragTicketId.value
  const ticket = tickets.value.find(t => t.id === id)
  if (id && ticket && ticket.status !== newStatus) {
    axios.put(`${BASE}/tickets/${id}`, { status: newStatus }).then(() => load()).catch(() => {})
  }
  dragTicketId.value = null; dragCol.value = null
}

function openCreateModal() {
  editingTicket.value = null
  Object.assign(form, {
    subject: '', contact_id: null, client_id: null, priority: 'Medium', status: 'Open',
    department_id: null, message: '', assigned_to: authStore.user?.id || null,
    tags: '', service_id: null, project_id: null, cc: '', tagList: [],
    email: '', contact_name: '', contact_number: '',
  })
  selectedContact.value = null; selectedClient.value = null
  attachmentFile.value = null; attachmentFileName.value = ''
  ticketWithoutContact.value = false
  showModal.value = true
}

function openCreateForStatus(status) { openCreateModal(); form.status = status }

function editTicket(ticket) {
  editingTicket.value = ticket
  Object.assign(form, {
    subject: ticket.subject, contact_id: ticket.contact_id || null, client_id: ticket.client_id || null,
    priority: ticket.priority, status: ticket.status, department_id: ticket.department_id || null,
    message: ticket.message, assigned_to: ticket.assigned_to || null, tags: ticket.tags || '',
    service_id: ticket.service_id || null, project_id: ticket.project_id || null, cc: ticket.cc || '',
    tagList: parseTags(ticket.tags), email: ticket.email || '', contact_name: ticket.contact_name || '', contact_number: ticket.contact_number || '',
  })
  if (ticket.contact_id) {
    const contact = metadata.value.contacts.find(c => c.id === ticket.contact_id)
    selectedContact.value = contact || null
    if (contact) {
      const client = metadata.value.clients.find(c => c.id === contact.client_id)
      selectedClient.value = client || null
    }
  } else { selectedContact.value = null; selectedClient.value = null }
  attachmentFile.value = null; attachmentFileName.value = ''
  ticketWithoutContact.value = false
  showModal.value = true
}

async function viewTicket(ticket) {
  try {
    const res = await axios.get(`${BASE}/tickets/${ticket.id}`)
    viewingTicket.value = res.data
  } catch { viewingTicket.value = { ...ticket, replies: [], message: ticket.message || 'No message.' } }
}

async function saveTicket() {
  if (!form.subject || !form.message || (!form.contact_id && !ticketWithoutContact.value) || !form.department_id) {
    return alert('Subject, Contact (or ticket without contact), Department, and Message are required')
  }
  saving.value = true
  try {
    if (editingTicket.value) { await axios.put(`${BASE}/tickets/${editingTicket.value.id}`, form) }
    else { await axios.post(`${BASE}/tickets`, form) }
    closeModal(); load()
  } catch { alert('Failed to save ticket') }
  finally { saving.value = false }
}

async function deleteTicket(ticket) {
  if (!confirm(`Delete ticket "${ticket.subject}"?`)) return
  try { await axios.delete(`${BASE}/tickets/${ticket.id}`); load() }
  catch { alert('Failed to delete ticket') }
}

async function sendReply() {
  if (!replyMessage.value.trim()) return
  sendingReply.value = true
  try {
    const res = await axios.post(`${BASE}/tickets/${viewingTicket.value.id}/reply`, {
      message: replyMessage.value, is_admin_reply: true,
    })
    viewingTicket.value.replies = [...(viewingTicket.value.replies || []), res.data]
    viewingTicket.value.status = 'Answered'
    replyMessage.value = ''
    load()
  } catch { alert('Failed to send reply') }
  finally { sendingReply.value = false }
}

function closeModal() { showModal.value = false; editingTicket.value = null }

onMounted(async () => {
  await loadMetadata(); await loadKbArticles(); await load()
  if (route.query.contact_id || route.query.userid) {
    openCreateModal()
    if (route.query.contact_id) form.contact_id = parseInt(route.query.contact_id)
    if (route.query.userid) form.client_id = parseInt(route.query.userid)
  }
})

watch(currentView, () => { if (currentView.value === 'kanban') statusFilter.value = ''; load() })
</script>

<style scoped>
.tk-page { padding: 24px; font-family: 'Inter', sans-serif; max-width: 1400px; margin: 0 auto; }

/* ── HEADER ── */
.tk-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.tk-title { font-size: 20px; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.3px; }
.tk-subtitle { font-size: 12.5px; color: #64748b; margin: 2px 0 0; }
.tk-header-actions { display: flex; align-items: center; gap: 10px; }
.tk-view-toggle { display: flex; background: #f1f5f9; border-radius: 8px; padding: 3px; gap: 2px; }
.tk-view-btn { background: none; border: none; padding: 6px 8px; border-radius: 6px; cursor: pointer; color: #94a3b8; transition: all .15s; display: flex; align-items: center; }
.tk-view-btn:hover { color: #475569; }
.tk-view-btn.active { background: #fff; color: #6366f1; box-shadow: 0 1px 3px rgba(0,0,0,.08); }

/* ── BUTTONS ── */
.tk-btn-primary { display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg,#6366f1,#4f46e5); color: #fff; border: none; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: opacity .15s; white-space: nowrap; }
.tk-btn-primary:hover { opacity: .9; }
.tk-btn-primary:disabled { opacity: .5; cursor: not-allowed; }
.tk-btn-secondary { padding: 9px 18px; border: 1.5px solid #e2e8f0; border-radius: 8px; background: #fff; color: #475569; font-size: 13px; cursor: pointer; }
.tk-btn-secondary:hover { background: #f8fafc; }
.tk-btn-analytics { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border: 1.5px solid #e2e8f0; border-radius: 8px; background: #fff; color: #475569; font-size: 12.5px; font-weight: 500; cursor: pointer; transition: all .15s; }
.tk-btn-analytics:hover { border-color: #6366f1; color: #6366f1; background: #f8fafc; }

/* ── STATS ── */
.tk-stats-row { display: grid; grid-template-columns: repeat(5,1fr); gap: 10px; margin-bottom: 20px; }
.tk-stat-card { background: #fff; border: 1px solid #f1f5f9; border-left: 3px solid; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; cursor: pointer; transition: all .2s; }
.tk-stat-card:hover { border-color: #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,.04); }
.tk-stat-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.tk-stat-info { flex: 1; min-width: 0; }
.tk-stat-val { font-size: 22px; font-weight: 800; line-height: 1.1; }
.tk-stat-label { font-size: 11px; color: #64748b; font-weight: 500; margin-top: 1px; }

/* ── KANBAN ── */
.tk-kanban { display: grid; grid-template-columns: repeat(5,1fr); gap: 12px; }
.tk-kanban-col { background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px; display: flex; flex-direction: column; gap: 8px; min-height: 300px; }
.tk-kanban-hd { display: flex; align-items: center; gap: 6px; padding-bottom: 8px; border-bottom: 1px solid #e2e8f0; border-top: 3px solid transparent; margin-top: -12px; padding-top: 12px; border-radius: 12px 12px 0 0; }
.tk-kanban-icon { display: flex; }
.tk-kanban-title { font-weight: 700; font-size: 11.5px; color: #1e293b; flex: 1; text-transform: uppercase; letter-spacing: .3px; }
.tk-kanban-count { background: #e2e8f0; color: #64748b; font-size: 10px; font-weight: 700; padding: 1px 7px; border-radius: 10px; }
.tk-kanban-cards { display: flex; flex-direction: column; gap: 6px; flex: 1; min-height: 60px; }
.tk-kanban-cards.tk-drag-over { background: #eef2ff; border-radius: 8px; outline: 2px dashed #6366f1; outline-offset: -2px; }
.tk-kanban-empty { text-align: center; color: #cbd5e1; font-size: 11px; padding: 16px; min-height: 40px; display: flex; align-items: center; justify-content: center; cursor: default; }

.tk-kanban-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px; display: flex; flex-direction: column; gap: 5px; transition: all .2s; cursor: grab; }
.tk-kanban-card:active { cursor: grabbing; }
.tk-kanban-card:hover { border-color: #6366f1; box-shadow: 0 2px 8px rgba(99,102,241,.08); }
.tk-kc-hd { display: flex; justify-content: space-between; align-items: center; }
.tk-kc-id { font-size: 10px; font-weight: 600; color: #94a3b8; }
.tk-kc-title { font-weight: 600; font-size: 12px; color: #0f172a; cursor: pointer; line-height: 1.4; }
.tk-kc-title:hover { color: #6366f1; }
.tk-kc-meta { font-size: 11px; color: #64748b; }
.tk-kc-dept { font-size: 10px; color: #6366f1; font-weight: 500; }
.tk-kc-ft { display: flex; justify-content: space-between; align-items: center; margin-top: 2px; }
.tk-kc-date { display: flex; align-items: center; gap: 3px; font-size: 10px; color: #94a3b8; }
.tk-kc-avatar { width: 22px; height: 22px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 700; color: #64748b; }
.tk-kanban-add { display: flex; align-items: center; justify-content: center; gap: 4px; padding: 6px; border: 1px dashed #d1d5db; border-radius: 8px; background: none; color: #94a3b8; font-size: 11px; font-weight: 600; cursor: pointer; transition: all .2s; }
.tk-kanban-add:hover { border-color: #6366f1; color: #6366f1; background: #eef2ff; }

/* ── FILTERS ── */
.tk-filters { display: flex; gap: 10px; margin-bottom: 14px; align-items: center; flex-wrap: wrap; }
.tk-filters-left { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.tk-filters-right { margin-left: auto; }
.tk-search-wrap { position: relative; min-width: 220px; }
.tk-search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 13px; height: 13px; color: #94a3b8; }
.tk-search-input { width: 100%; padding: 8px 12px 8px 32px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; box-sizing: border-box; }
.tk-search-input:focus { border-color: #6366f1; }
.tk-filter-select { padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 12.5px; cursor: pointer; background: #fff; outline: none; }

/* ── TABLE ── */
.tk-table-wrap { background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; overflow: hidden; }
.tk-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.tk-table th { padding: 10px 12px; text-align: left; background: #f8fafc; color: #475569; font-weight: 600; font-size: 11px; border-bottom: 1.5px solid #e2e8f0; white-space: nowrap; }
.tk-row { transition: background .15s; }
.tk-row:hover { background: #f8fafc; }
.tk-table td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.tk-cell-muted { color: #64748b; }
.tk-name-cell { display: flex; flex-direction: column; }
.tk-name { font-weight: 600; color: #0f172a; }
.tk-tags { display: flex; gap: 3px; flex-wrap: wrap; }
.tk-tag { background: #f1f5f9; color: #475569; font-size: 10px; padding: 1px 6px; border-radius: 4px; font-weight: 500; }
.tk-status-badge { display: inline-block; padding: 3px 9px; border-radius: 20px; font-size: 10.5px; font-weight: 600; }
.tk-status-badge.open       { background: #eff6ff; color: #3b82f6; }
.tk-status-badge.in-progress { background: #fffbeb; color: #d97706; }
.tk-status-badge.answered   { background: #f0fdf4; color: #16a34a; }
.tk-status-badge.on-hold    { background: #f5f3ff; color: #7c3aed; }
.tk-status-badge.closed     { background: #f1f5f9; color: #64748b; }
.tk-pri { display: inline-block; padding: 2px 7px; border-radius: 4px; font-size: 10px; font-weight: 600; }
.tk-pri.urg { background: #fef2f2; color: #dc2626; }
.tk-pri.high { background: #fff7ed; color: #ea580c; }
.tk-pri.med  { background: #fefce8; color: #ca8a04; }
.tk-pri.low  { background: #f0fdf4; color: #16a34a; }
.tk-empty-cell { text-align: center; padding: 40px; color: #94a3b8; }
.tk-row-actions { display: flex; gap: 4px; }
.tk-act-btn-icon { background: none; border: none; cursor: pointer; padding: 5px; border-radius: 6px; color: #94a3b8; transition: all .15s; display: flex; }
.tk-act-btn-icon:hover { background: #f1f5f9; color: #475569; }

/* ── PAGINATION ── */
.tk-pagination { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; font-size: 12px; color: #64748b; }
.tk-pg-info { color: #94a3b8; }
.tk-pg-btns { display: flex; gap: 6px; }
.tk-pg-btn { padding: 5px 14px; border: 1.5px solid #e2e8f0; border-radius: 6px; background: #fff; cursor: pointer; font-size: 12px; color: #475569; transition: all .15s; }
.tk-pg-btn:hover:not(:disabled) { border-color: #6366f1; color: #6366f1; }
.tk-pg-btn:disabled { opacity: .4; cursor: not-allowed; }

/* ── MODAL ── */
.tk-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9000; display: flex; align-items: flex-start; justify-content: center; padding: 40px 20px; overflow-y: auto; }
.tk-modal-box { background: #fff; border-radius: 14px; width: 100%; max-width: 520px; box-shadow: 0 20px 60px rgba(0,0,0,.25); margin: auto; }
.tk-modal-wide { max-width: 760px; }
.tk-modal-hd { display: flex; justify-content: space-between; align-items: flex-start; padding: 20px 24px 14px; }
.tk-modal-hd-left { display: flex; gap: 12px; align-items: flex-start; }
.tk-modal-icon { width: 38px; height: 38px; background: #eef2ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #6366f1; flex-shrink: 0; }
.tk-modal-title { font-size: 16px; font-weight: 700; color: #0f172a; }
.tk-modal-sub { font-size: 12px; color: #64748b; margin-top: 1px; }
.tk-modal-close { background: none; border: none; cursor: pointer; color: #94a3b8; padding: 4px; border-radius: 6px; }
.tk-modal-close:hover { background: #f1f5f9; color: #475569; }
.tk-modal-body { padding: 0 24px 16px; }
.tk-modal-ft { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 24px 20px; border-top: 1.5px solid #f1f5f9; }

/* ── FORM ── */
.tk-radio-group-label { font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 2px; }
.tk-radio-group { display: flex; gap: 0; border-radius: 8px; overflow: hidden; border: 1.5px solid #e2e8f0; }
.tk-radio-btn { display: flex; align-items: center; gap: 6px; padding: 9px 16px; cursor: pointer; font-size: 12.5px; color: #64748b; background: #fff; transition: all .15s; flex: 1; justify-content: center; }
.tk-radio-btn:not(:last-child) { border-right: 1px solid #e2e8f0; }
.tk-radio-btn.active { background: #eef2ff; color: #6366f1; font-weight: 600; }
.tk-radio-btn input { display: none; }
.tk-radio-dot { width: 16px; height: 16px; border: 2px solid #d1d5db; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; }
.tk-radio-btn.active .tk-radio-dot { border-color: #6366f1; }
.tk-radio-btn.active .tk-radio-dot::after { content: ''; width: 8px; height: 8px; background: #6366f1; border-radius: 50%; }
.tk-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.tk-form-group { display: flex; flex-direction: column; gap: 4px; }
.tk-form-group.span-2 { grid-column: span 2; }
.tk-form-group label { font-size: 12px; font-weight: 600; color: #374151; }
.tk-req { color: #ef4444; }
.tk-input { width: 100%; padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; box-sizing: border-box; transition: border-color .15s; }
.tk-input:focus { border-color: #6366f1; }
.tk-input-readonly { background: #f8fafc; color: #64748b; cursor: not-allowed; }
.tk-textarea { resize: vertical; min-height: 100px; font-family: inherit; line-height: 1.5; }

/* ── TAG INPUT ── */
.tk-tag-input-wrap { border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 6px 8px; display: flex; flex-wrap: wrap; gap: 4px; min-height: 36px; }
.tk-tag-list { display: flex; flex-wrap: wrap; gap: 4px; width: 100%; }
.tk-tag-pill { background: #eef2ff; color: #6366f1; font-size: 11px; font-weight: 600; padding: 2px 7px; border-radius: 5px; display: inline-flex; align-items: center; gap: 4px; }
.tk-tag-pill-del { background: none; border: none; cursor: pointer; color: #6366f1; font-size: 13px; line-height: 1; padding: 0; }
.tk-tag-field { border: none; outline: none; font-size: 12.5px; flex: 1; min-width: 80px; padding: 2px 0; }

/* ── FILE UPLOAD ── */
.tk-file-upload { display: flex; align-items: center; gap: 10px; border: 1.5px dashed #d1d5db; padding: 12px; border-radius: 8px; background: #f8fafc; }
.tk-file-input { display: none; }
.tk-file-btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 14px; border: 1.5px solid #e2e8f0; border-radius: 6px; font-size: 12px; font-weight: 500; cursor: pointer; background: #fff; transition: background .15s; }
.tk-file-btn:hover { background: #f1f5f9; }
.tk-file-name { font-size: 12px; color: #64748b; }

/* ── EDITOR TOOLBAR ── */
.tk-editor-toolbar { display: flex; gap: 8px; margin-bottom: 6px; }
.tk-editor-toolbar select { padding: 5px 10px; border: 1.5px solid #e2e8f0; border-radius: 6px; font-size: 12px; cursor: pointer; background: #fff; outline: none; }

/* ── VIEW MODAL ── */
.tk-view-meta { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-bottom: 14px; }
.tk-view-meta-item { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: #64748b; }
.tk-view-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; padding: 12px 14px; background: #f8fafc; border-radius: 10px; margin-bottom: 16px; border: 1px solid #e2e8f0; }
.tk-view-item { display: flex; gap: 8px; font-size: 12.5px; }
.tk-view-lbl { font-weight: 600; color: #64748b; min-width: 90px; white-space: nowrap; }
.tk-view-val { color: #1e293b; }

/* ── MESSAGE BUBBLES ── */
.tk-message-bubble { border-radius: 10px; padding: 12px 16px; margin-bottom: 10px; }
.tk-message-original { background: #f8fafc; border-left: 3px solid #6366f1; }
.tk-message-admin { background: #eff6ff; border-left: 3px solid #3b82f6; }
.tk-message-client { background: #f0fdf4; border-left: 3px solid #10b981; }
.tk-bubble-hd { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 6px; flex-wrap: wrap; }
.tk-bubble-author { color: #334155; }
.tk-bubble-time { font-weight: 400; color: #94a3b8; }
.tk-bubble-tag { background: #e2e8f0; color: #475569; font-size: 9px; font-weight: 700; padding: 1px 6px; border-radius: 4px; }
.tk-bubble-body { font-size: 13px; color: #334155; line-height: 1.6; white-space: pre-wrap; }

/* ── REPLIES ── */
.tk-replies-section { margin-top: 20px; }
.tk-replies-hd { display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 12px; }
.tk-reply-form { margin-top: 14px; display: flex; flex-direction: column; gap: 10px; }

/* ── WEEKLY CHART ── */
.tk-modal-narrow { max-width: 520px; }
.tk-weekly-chart { display: flex; gap: 0; padding: 16px 0 8px; align-items: stretch; }
.tk-chart-yaxis { display: flex; flex-direction: column; justify-content: space-between; padding-right: 10px; font-size: 10px; color: #94a3b8; font-weight: 500; min-width: 30px; text-align: right; padding-bottom: 36px; }
.tk-chart-bars { display: flex; flex: 1; gap: 8px; align-items: flex-end; justify-content: space-around; border-left: 1.5px solid #e2e8f0; border-bottom: 1.5px solid #e2e8f0; padding-left: 8px; }
.tk-chart-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; }
.tk-chart-bar-wrap { flex: 1; width: 100%; display: flex; align-items: flex-end; justify-content: center; min-height: 120px; }
.tk-chart-bar { width: 28px; background: linear-gradient(180deg, #6366f1, #818cf8); border-radius: 5px 5px 0 0; min-height: 4px; transition: height .4s ease; }
.tk-chart-lbl { font-size: 10px; font-weight: 600; color: #64748b; }
.tk-chart-val { font-size: 11px; font-weight: 700; color: #6366f1; }

/* ── UTILITY ── */
.text-slate-300 { color: #cbd5e1; }
.text-sm { font-size: 12px; }
.mt-2 { margin-top: 6px; }
.animate-spin { animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.opacity-25 { opacity: .25; }
.opacity-75 { opacity: .75; }
.hover\:text-rose-600:hover { color: #e11d48; }

@media (max-width: 1200px) { .tk-kanban { grid-template-columns: repeat(3,1fr); } .tk-stats-row { grid-template-columns: repeat(3,1fr); } }
@media (max-width: 768px) { .tk-kanban { grid-template-columns: repeat(2,1fr); } .tk-stats-row { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 640px) { .tk-kanban { grid-template-columns: 1fr; } .tk-stats-row { grid-template-columns: 1fr 1fr; } .tk-filters { flex-direction: column; } }
</style>
