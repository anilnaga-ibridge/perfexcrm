<template>
  <div>
    <!-- HEADER & TOOLBAR -->
    <div class="section-toolbar">
      <h2 class="section-title">Theme Style</h2>
    </div>

    <!-- MAIN CARD / TABS CONTAINER -->
    <div class="settings-card">
      <a-tabs v-model:activeKey="activeTab" class="theme-tabs">
        
        <!-- 1. ADMIN AREA -->
        <a-tab-pane key="admin_area" tab="Admin Area">
          <div class="tab-content-wrap">
            <h3 class="tab-title">Admin Area Styling</h3>
            
            <div class="split-preview-layout">
              <div class="color-pickers-grid" style="flex: 1;">
                <div class="color-picker-item">
                  <span class="picker-label">Sidebar Menu/Setup Menu Background Color</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('admin_sidebar_bg')" v-model="settings.admin_sidebar_bg" class="color-picker-box" />
                    <a-input v-model:value="settings.admin_sidebar_bg" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Sidebar Menu/Setup Menu Links Color</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('admin_sidebar_link')" v-model="settings.admin_sidebar_link" class="color-picker-box" />
                    <a-input v-model:value="settings.admin_sidebar_link" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Sidebar Menu/Setup Active Item Background Color</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('admin_sidebar_active_bg')" v-model="settings.admin_sidebar_active_bg" class="color-picker-box" />
                    <a-input v-model:value="settings.admin_sidebar_active_bg" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Sidebar Menu/Setup Active Item Color</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('admin_sidebar_active_link')" v-model="settings.admin_sidebar_active_link" class="color-picker-box" />
                    <a-input v-model:value="settings.admin_sidebar_active_link" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Top Header Background Color</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('admin_header_bg')" v-model="settings.admin_header_bg" class="color-picker-box" />
                    <a-input v-model:value="settings.admin_header_bg" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Top Header Links Color</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('admin_header_link')" v-model="settings.admin_header_link" class="color-picker-box" />
                    <a-input v-model:value="settings.admin_header_link" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Main Content Background Color</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('admin_content_bg')" v-model="settings.admin_content_bg" class="color-picker-box" />
                    <a-input v-model:value="settings.admin_content_bg" size="small" style="width: 110px" />
                  </div>
                </div>
              </div>

              <!-- Sidebar Preview Panel -->
              <div class="preview-panel" style="flex: 1;">
                <span class="preview-title">Sidebar Visual Preview (Click to Customize)</span>
                <div class="sidebar-preview-box" :style="{ background: `linear-gradient(135deg, #d35400 0%, ${settings.admin_sidebar_bg} 50%, #0b579f 100%)` }" style="cursor: pointer" @click.self="triggerPicker('admin_sidebar_bg')">
                  <div class="sidebar-preview-logo" style="cursor: pointer" @click="triggerPicker('admin_sidebar_bg')">
                    <span style="color:#fff;font-weight:700;letter-spacing:0.5px">iBRIDGE CRM</span>
                  </div>
                  <div class="sidebar-preview-menu">
                    <div class="sidebar-preview-item active" :style="{ backgroundColor: settings.admin_sidebar_active_bg, color: settings.admin_sidebar_active_link }" style="cursor: pointer" @click.stop="triggerPicker('admin_sidebar_active_bg')">
                      <span>Dashboard</span>
                    </div>
                    <div class="sidebar-preview-item" :style="{ color: settings.admin_sidebar_link }" style="cursor: pointer" @click.stop="triggerPicker('admin_sidebar_link')">
                      <span>Customers</span>
                    </div>
                    <div class="sidebar-preview-item" :style="{ color: settings.admin_sidebar_link }" style="cursor: pointer" @click.stop="triggerPicker('admin_sidebar_link')">
                      <span>Invoices</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </a-tab-pane>

        <!-- 2. CUSTOMERS AREA -->
        <a-tab-pane key="customers_area" tab="Customers Area">
          <div class="tab-content-wrap">
            <h3 class="tab-title">Customers Area Styling</h3>
            
            <div class="split-preview-layout">
              <div class="color-pickers-grid" style="flex: 1;">
                <div class="color-picker-item">
                  <span class="picker-label">Navigation Background Color</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('cust_nav_bg')" v-model="settings.cust_nav_bg" class="color-picker-box" />
                    <a-input v-model:value="settings.cust_nav_bg" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Navigation Links Color</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('cust_nav_link')" v-model="settings.cust_nav_link" class="color-picker-box" />
                    <a-input v-model:value="settings.cust_nav_link" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Footer Background</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('cust_footer_bg')" v-model="settings.cust_footer_bg" class="color-picker-box" />
                    <a-input v-model:value="settings.cust_footer_bg" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Footer Text Color</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('cust_footer_text')" v-model="settings.cust_footer_text" class="color-picker-box" />
                    <a-input v-model:value="settings.cust_footer_text" size="small" style="width: 110px" />
                  </div>
                </div>
              </div>

              <!-- Portal Navigation Preview -->
              <div class="preview-panel" style="flex: 1;">
                <span class="preview-title">Portal Navigation Preview (Click to Customize)</span>
                <div class="nav-preview-box" :style="{ background: `linear-gradient(135deg, #d35400 0%, ${settings.cust_nav_bg} 50%, #0b579f 100%)` }" style="cursor: pointer" @click.self="triggerPicker('cust_nav_bg')">
                  <span class="nav-preview-brand" style="color: #fff; font-weight: 600; cursor: pointer" @click.stop="triggerPicker('cust_nav_bg')">Customer Portal</span>
                  <div class="nav-preview-links" :style="{ color: settings.cust_nav_link }">
                    <span style="border-bottom: 2px solid #fff; padding-bottom: 2px; cursor: pointer" @click.stop="triggerPicker('cust_nav_link')">Invoices</span>
                    <span style="opacity: 0.8; margin-left: 12px; cursor: pointer" @click.stop="triggerPicker('cust_nav_link')">Estimates</span>
                  </div>
                </div>
                <div class="footer-preview-box" :style="{ backgroundColor: settings.cust_footer_bg, color: settings.cust_footer_text }" style="cursor: pointer" @click="triggerPicker('cust_footer_bg')">
                  <span style="cursor: pointer" @click.stop="triggerPicker('cust_footer_text')">© 2026 iBRIDGE CRM. All rights reserved.</span>
                </div>
              </div>
            </div>
          </div>
        </a-tab-pane>

        <!-- 3. BUTTONS -->
        <a-tab-pane key="buttons" tab="Buttons">
          <div class="tab-content-wrap">
            <h3 class="tab-title">Button Styling</h3>
            
            <div class="split-preview-layout">
              <div class="color-pickers-grid" style="flex: 1;">
                <div class="color-picker-item">
                  <span class="picker-label">Button Default</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('btn_default')" v-model="settings.btn_default" class="color-picker-box" />
                    <a-input v-model:value="settings.btn_default" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Button Primary</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('btn_primary')" v-model="settings.btn_primary" class="color-picker-box" />
                    <a-input v-model:value="settings.btn_primary" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Button Info</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('btn_info')" v-model="settings.btn_info" class="color-picker-box" />
                    <a-input v-model:value="settings.btn_info" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Button Success</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('btn_success')" v-model="settings.btn_success" class="color-picker-box" />
                    <a-input v-model:value="settings.btn_success" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Button Danger</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('btn_danger')" v-model="settings.btn_danger" class="color-picker-box" />
                    <a-input v-model:value="settings.btn_danger" size="small" style="width: 110px" />
                  </div>
                </div>
              </div>

              <!-- Buttons Preview Panel -->
              <div class="preview-panel" style="flex: 1;">
                <span class="preview-title">Buttons Visual Preview (Click to Customize)</span>
                <div class="buttons-preview-list">
                  <button type="button" class="preview-btn" :style="{ background: `linear-gradient(135deg, #d35400 0%, ${settings.btn_default} 50%, #0b579f 100%)`, color: '#fff', border: 'none' }" style="cursor: pointer" @click="triggerPicker('btn_default')">Default Button</button>
                  <button type="button" class="preview-btn" :style="{ background: `linear-gradient(135deg, #d35400 0%, ${settings.btn_primary} 50%, #0b579f 100%)`, color: '#fff', border: 'none', boxShadow: `0px 4px 14px 0px rgba(126, 30, 142, 0.2)` }" style="cursor: pointer" @click="triggerPicker('btn_primary')">Primary Button</button>
                  <button type="button" class="preview-btn" :style="{ background: `linear-gradient(135deg, #d35400 0%, ${settings.btn_info} 50%, #0b579f 100%)`, color: '#fff', border: 'none' }" style="cursor: pointer" @click="triggerPicker('btn_info')">Info Button</button>
                  <button type="button" class="preview-btn" :style="{ background: `linear-gradient(135deg, #d35400 0%, ${settings.btn_success} 50%, #0b579f 100%)`, color: '#fff', border: 'none' }" style="cursor: pointer" @click="triggerPicker('btn_success')">Success Button</button>
                  <button type="button" class="preview-btn" :style="{ background: `linear-gradient(135deg, #d35400 0%, ${settings.btn_danger} 50%, #0b579f 100%)`, color: '#fff', border: 'none' }" style="cursor: pointer" @click="triggerPicker('btn_danger')">Danger Button</button>
                </div>
              </div>
            </div>
          </div>
        </a-tab-pane>

        <!-- 4. MODALS -->
        <a-tab-pane key="modals" tab="Modals">
          <div class="tab-content-wrap">
            <h3 class="tab-title">Modal Styling</h3>
            
            <div class="split-preview-layout">
              <div class="color-pickers-grid" style="flex: 1;">
                <div class="color-picker-item">
                  <span class="picker-label">Heading Background</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('modal_heading_bg')" v-model="settings.modal_heading_bg" class="color-picker-box" />
                    <a-input v-model:value="settings.modal_heading_bg" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Heading Color</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('modal_heading_color')" v-model="settings.modal_heading_color" class="color-picker-box" />
                    <a-input v-model:value="settings.modal_heading_color" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Close Button Color</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('modal_close_color')" v-model="settings.modal_close_color" class="color-picker-box" />
                    <a-input v-model:value="settings.modal_close_color" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Modal Header Text Color</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('modal_header_text_color')" v-model="settings.modal_header_text_color" class="color-picker-box" />
                    <a-input v-model:value="settings.modal_header_text_color" size="small" style="width: 110px" />
                  </div>
                </div>
              </div>

              <!-- Modal Preview Frame -->
              <div class="preview-panel" style="flex: 1;">
                <span class="preview-title">Visual Preview (Click to Customize)</span>
                <div class="modal-preview-box">
                  <div class="modal-preview-header" :style="{ background: `linear-gradient(135deg, #d35400 0%, ${settings.modal_heading_bg} 50%, #0b579f 100%)` }" style="cursor: pointer" @click.self="triggerPicker('modal_heading_bg')">
                    <span class="modal-preview-heading-text" :style="{ color: '#fff' }" style="cursor: pointer" @click.stop="triggerPicker('modal_heading_bg')">Example Modal Heading</span>
                    <span class="modal-preview-close" :style="{ color: settings.modal_close_color }" style="cursor: pointer" @click.stop="triggerPicker('modal_close_color')">×</span>
                  </div>
                  <div class="modal-preview-body">
                    <span class="sample-text-header" :style="{ color: settings.modal_heading_color }" style="cursor: pointer" @click="triggerPicker('modal_heading_color')">Sample Text</span>
                    <p class="sample-body-text">Modal Body</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </a-tab-pane>

        <!-- 5. TABLES -->
        <a-tab-pane key="tables" tab="Tables">
          <div class="tab-content-wrap">
            <h3 class="tab-title">Table Styling</h3>
            
            <div class="split-preview-layout">
              <div class="color-pickers-grid" style="flex: 1;">
                <div class="color-picker-item">
                  <span class="picker-label">Table Links Color</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('table_link')" v-model="settings.table_link" class="color-picker-box" />
                    <a-input v-model:value="settings.table_link" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Table Links Hover/Focus Color</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('table_link_hover')" v-model="settings.table_link_hover" class="color-picker-box" />
                    <a-input v-model:value="settings.table_link_hover" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Table Headings Color</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('table_heading')" v-model="settings.table_heading" class="color-picker-box" />
                    <a-input v-model:value="settings.table_heading" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Items Table Headings Background Color</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('table_items_heading_bg')" v-model="settings.table_items_heading_bg" class="color-picker-box" />
                    <a-input v-model:value="settings.table_items_heading_bg" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Items Table Headings Text Color</span>
                  <div class="picker-controls">
                    <input type="color" :ref="setPickerRef('table_items_heading_text')" v-model="settings.table_items_heading_text" class="color-picker-box" />
                    <a-input v-model:value="settings.table_items_heading_text" size="small" style="width: 110px" />
                  </div>
                </div>
              </div>

              <!-- Table Preview Frame -->
              <div class="preview-panel" style="flex: 1;">
                <span class="preview-title">Visual Preview (Click to Customize)</span>
                <table class="table-preview-control">
                  <thead :style="{ background: `linear-gradient(135deg, #d35400 0%, ${settings.table_items_heading_bg} 50%, #0b579f 100%)`, color: settings.table_items_heading_text }" style="cursor: pointer" @click="triggerPicker('table_items_heading_bg')">
                    <tr>
                      <th style="color:#fff; cursor: pointer" @click.stop="triggerPicker('table_items_heading_text')">Example Heading 1</th>
                      <th style="color:#fff; cursor: pointer" @click.stop="triggerPicker('table_items_heading_text')">Example Heading 2</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>
                        <a href="#" :style="{ color: settings.table_link }" style="cursor: pointer" @click.stop.prevent="triggerPicker('table_link')">Table Link</a>
                      </td>
                      <td :style="{ color: settings.table_heading }" style="cursor: pointer" @click.stop="triggerPicker('table_heading')">Heading Text</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </a-tab-pane>

        <!-- 6. GENERAL -->
        <a-tab-pane key="general" tab="General">
          <div class="tab-content-wrap">
            <h3 class="tab-title">General Layout Styling</h3>
            
            <div class="split-preview-layout">
              <div class="color-pickers-grid" style="flex: 1;">
                <div class="color-picker-item">
                  <span class="picker-label">Links Color (href)</span>
                  <div class="picker-controls">
                    <input type="color" v-model="settings.gen_link" class="color-picker-box" />
                    <a-input v-model:value="settings.gen_link" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Links Hover/Focus Color</span>
                  <div class="picker-controls">
                    <input type="color" v-model="settings.gen_link_hover" class="color-picker-box" />
                    <a-input v-model:value="settings.gen_link_hover" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Admin Login Background</span>
                  <div class="picker-controls">
                    <input type="color" v-model="settings.gen_login_bg" class="color-picker-box" />
                    <a-input v-model:value="settings.gen_login_bg" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Text Muted</span>
                  <div class="picker-controls">
                    <input type="color" v-model="settings.gen_text_muted" class="color-picker-box" />
                    <a-input v-model:value="settings.gen_text_muted" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Text Danger</span>
                  <div class="picker-controls">
                    <input type="color" v-model="settings.gen_text_danger" class="color-picker-box" />
                    <a-input v-model:value="settings.gen_text_danger" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Text Warning</span>
                  <div class="picker-controls">
                    <input type="color" v-model="settings.gen_text_warning" class="color-picker-box" />
                    <a-input v-model:value="settings.gen_text_warning" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Text Info</span>
                  <div class="picker-controls">
                    <input type="color" v-model="settings.gen_text_info" class="color-picker-box" />
                    <a-input v-model:value="settings.gen_text_info" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Text Success</span>
                  <div class="picker-controls">
                    <input type="color" v-model="settings.gen_text_success" class="color-picker-box" />
                    <a-input v-model:value="settings.gen_text_success" size="small" style="width: 110px" />
                  </div>
                </div>
              </div>

              <!-- General Text Preview Panel -->
              <div class="preview-panel" style="flex: 1;">
                <span class="preview-title">Alert Text Previews</span>
                <div class="alert-previews">
                  <div class="alert-preview-row">
                    <a href="#" :style="{ color: settings.gen_link }">General Link</a>
                  </div>
                  <div class="alert-preview-row">
                    <span :style="{ color: settings.gen_text_muted }">Example Text Muted</span>
                  </div>
                  <div class="alert-preview-row">
                    <span :style="{ color: settings.gen_text_danger }">Example Text Danger</span>
                  </div>
                  <div class="alert-preview-row">
                    <span :style="{ color: settings.gen_text_warning }">Example Text Warning</span>
                  </div>
                  <div class="alert-preview-row">
                    <span :style="{ color: settings.gen_text_info }">Example Text Info</span>
                  </div>
                  <div class="alert-preview-row">
                    <span :style="{ color: settings.gen_text_success }">Example Text Success</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </a-tab-pane>

        <!-- 7. TAGS -->
        <a-tab-pane key="tags" tab="Tags">
          <div class="tab-content-wrap">
            <h3 class="tab-title">Tag Badge Colors</h3>
            
            <div class="tags-picker-grid">
              <div v-for="tag in tags" :key="tag.name" class="tag-picker-item">
                <span class="tag-name">{{ tag.name }}</span>
                <div class="tag-color-controls">
                  <input type="color" v-model="tag.color" class="color-picker-box" />
                  <span
                    class="tag-badge-preview"
                    :style="{ backgroundColor: tag.color + '18', color: tag.color, borderColor: tag.color }"
                  >
                    {{ tag.name }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </a-tab-pane>

        <!-- 8. CUSTOM CSS -->
        <a-tab-pane key="custom_css" tab="Custom CSS">
          <div class="tab-content-wrap">
            <h3 class="tab-title">Custom CSS Styling</h3>
            
            <div class="css-tab-selector">
              <a-radio-group v-model:value="settings.custom_css_tab" button-style="solid" style="margin-bottom: 16px;">
                <a-radio-button value="admin">Admin Area</a-radio-button>
                <a-radio-button value="customers">Customers Area</a-radio-button>
              </a-radio-group>
            </div>

            <div v-if="settings.custom_css_tab === 'admin'">
              <span class="picker-label" style="display: block; margin-bottom: 6px;">Admin Area Custom CSS</span>
              <a-textarea v-model:value="settings.custom_css_admin" :rows="12" class="code-textarea" />
            </div>

            <div v-if="settings.custom_css_tab === 'customers'">
              <span class="picker-label" style="display: block; margin-bottom: 6px;">Customers Area Custom CSS</span>
              <a-textarea v-model:value="settings.custom_css_customer" :rows="12" class="code-textarea" />
            </div>
          </div>
        </a-tab-pane>

      </a-tabs>

      <!-- SAVE FOOTER -->
      <div class="settings-actions">
        <a-button type="primary" :loading="saving" @click="saveThemeSettings">
          Save Settings
        </a-button>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent, ref, reactive, watch } from 'vue';
import { message } from 'ant-design-vue';
import { applyThemeStyles } from '../../utils.js';

export default defineComponent({
  name: 'ThemeStyleView',
  setup() {
    const activeTab = ref('admin_area');
    const saving = ref(false);

    const defaultSettings = {
      admin_sidebar_bg: '#7e1e8e',
      admin_sidebar_link: '#e2e8f0',
      admin_sidebar_active_bg: '#6366f1',
      admin_sidebar_active_link: '#ffffff',
      admin_header_bg: '#ffffff',
      admin_header_link: '#4F4B5E',
      admin_content_bg: '#F8F7FA',

      cust_nav_bg: '#7e1e8e',
      cust_nav_link: '#ffffff',
      cust_footer_bg: '#2F3349',
      cust_footer_text: '#A5A2AD',

      btn_default: '#F1F0F2',
      btn_primary: '#7e1e8e',
      btn_info: '#00CFE8',
      btn_success: '#28C76F',
      btn_danger: '#EA5455',

      modal_heading_bg: '#7e1e8e',
      modal_heading_color: '#4F4B5E',
      modal_close_color: '#A5A2AD',
      modal_header_text_color: '#4F4B5E',

      table_link: '#7e1e8e',
      table_link_hover: '#671675',
      table_heading: '#4F4B5E',
      table_items_heading_bg: '#7e1e8e',
      table_items_heading_text: '#ffffff',

      gen_link: '#7e1e8e',
      gen_link_hover: '#671675',
      gen_login_bg: '#F8F7FA',
      gen_text_muted: '#A5A2AD',
      gen_text_danger: '#EA5455',
      gen_text_warning: '#FF9F43',
      gen_text_info: '#00CFE8',
      gen_text_success: '#28C76F',

      custom_css_tab: 'admin',
      custom_css_admin: '/* Custom Admin CSS Rules */\nbody {\n  font-family: "Public Sans", sans-serif;\n}',
      custom_css_customer: '/* Custom Customers CSS Rules */\n.btn-custom {\n  border-radius: 6px;\n}'
    };

    const savedSettings = localStorage.getItem('crm_theme_style_settings');
    const settings = reactive(savedSettings ? JSON.parse(savedSettings) : defaultSettings);

    const defaultTags = [
      { name: 'bug', color: '#EA5455' },
      { name: 'follow up', color: '#7e1e8e' },
      { name: 'important', color: '#FF9F43' },
      { name: 'logo', color: '#00CFE8' },
      { name: 'review', color: '#8F00FF' },
      { name: 'todo', color: '#A5A2AD' },
      { name: 'tomorrow', color: '#28C76F' },
      { name: 'wordpress', color: '#00CFE8' }
    ];
    const savedTags = localStorage.getItem('crm_theme_style_tags');
    const tags = ref(savedTags ? JSON.parse(savedTags) : defaultTags);

    const saveThemeSettings = () => {
      saving.value = true;
      setTimeout(() => {
        saving.value = false;
        localStorage.setItem('crm_theme_style_settings', JSON.stringify(settings));
        localStorage.setItem('crm_theme_style_tags', JSON.stringify(tags.value));
        applyThemeStyles(settings);
        message.success('Theme Style settings saved successfully');
      }, 600);
    };

    const pickerRefs = {};
    const setPickerRef = (key) => (el) => {
      if (el) pickerRefs[key] = el;
    };
    const triggerPicker = (key) => {
      const el = pickerRefs[key];
      if (el) {
        el.click();
      }
    };

    // Watcher to sync all button colors together when any button color is updated
    watch(
      () => [settings.btn_primary, settings.btn_default, settings.btn_info, settings.btn_success, settings.btn_danger],
      (newVals, oldVals) => {
        let changedVal = null;
        for (let i = 0; i < newVals.length; i++) {
          if (newVals[i] !== oldVals[i]) {
            changedVal = newVals[i];
            break;
          }
        }
        if (changedVal) {
          if (settings.btn_primary !== changedVal) settings.btn_primary = changedVal;
          if (settings.btn_default !== changedVal) settings.btn_default = changedVal;
          if (settings.btn_info !== changedVal) settings.btn_info = changedVal;
          if (settings.btn_success !== changedVal) settings.btn_success = changedVal;
          if (settings.btn_danger !== changedVal) settings.btn_danger = changedVal;
        }
      }
    );

    return {
      activeTab,
      saving,
      settings,
      tags,
      saveThemeSettings,
      setPickerRef,
      triggerPicker
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
.settings-card {
  background: #fff;
  border-radius: 12px;
  border: 1px solid #f1f5f9;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.theme-tabs :deep(.ant-tabs-nav) {
  margin-bottom: 24px;
}
.tab-content-wrap {
  min-height: 320px;
}
.tab-title {
  font-size: 15px;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 20px;
}
.color-pickers-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px 32px;
}
.color-picker-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.picker-label {
  font-size: 13px;
  font-weight: 600;
  color: #334155;
}
.picker-controls {
  display: flex;
  align-items: center;
  gap: 12px;
}
.color-picker-box {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  width: 38px;
  height: 38px;
  background-color: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
}
.color-picker-box::-webkit-color-swatch {
  border-radius: 6px;
  border: 1px solid #e2e8f0;
}
.preview-btn {
  margin-top: 8px;
  align-self: flex-start;
  padding: 6px 16px;
  border: none;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}
.split-preview-layout {
  display: flex;
  gap: 32px;
}
.preview-panel {
  display: flex;
  flex-direction: column;
  gap: 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
}
.preview-title {
  font-size: 12px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.modal-preview-box {
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  background: #fff;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
.modal-preview-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
}
.modal-preview-heading-text {
  font-weight: 700;
  font-size: 13px;
}
.modal-preview-close {
  font-size: 18px;
  cursor: pointer;
}
.modal-preview-body {
  padding: 16px;
}
.sample-text-header {
  display: block;
  font-weight: 600;
  margin-bottom: 4px;
}
.sample-body-text {
  font-size: 13px;
  color: #475569;
  margin: 0;
}
.table-preview-control {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  overflow: hidden;
}
.table-preview-control th {
  padding: 10px 14px;
  font-size: 12px;
  font-weight: 600;
  text-align: left;
}
.table-preview-control td {
  padding: 10px 14px;
  font-size: 12px;
  border-top: 1px solid #e2e8f0;
}
.alert-previews {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.alert-preview-row {
  font-size: 13px;
  font-weight: 600;
}
.tags-picker-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}
.tag-picker-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 12px 16px;
}
.tag-name {
  font-size: 12.5px;
  font-weight: 700;
  color: #475569;
  text-transform: capitalize;
}
.tag-color-controls {
  display: flex;
  align-items: center;
  gap: 12px;
}
.tag-badge-preview {
  display: inline-flex;
  align-items: center;
  padding: 2px 10px;
  border-radius: 9999px;
  font-size: 11px;
  font-weight: 600;
  border: 1px solid;
}
.code-textarea {
  font-family: monospace;
  font-size: 12px;
  line-height: 1.5;
  background: #0f172a;
  color: #e2e8f0;
  border-radius: 8px;
  padding: 16px;
  border: none;
}
.code-textarea:focus {
  background: #0f172a;
  color: #e2e8f0;
  box-shadow: none;
}
.settings-actions {
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
}
</style>
