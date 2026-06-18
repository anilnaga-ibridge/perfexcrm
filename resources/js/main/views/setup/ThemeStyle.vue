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
            
            <div class="color-pickers-grid">
              <div class="color-picker-item">
                <span class="picker-label">Sidebar Menu/Setup Menu Background Color</span>
                <div class="picker-controls">
                  <input type="color" v-model="settings.admin_sidebar_bg" class="color-picker-box" />
                  <a-input v-model:value="settings.admin_sidebar_bg" size="small" style="width: 110px" />
                </div>
              </div>

              <div class="color-picker-item">
                <span class="picker-label">Sidebar Menu/Setup Menu Links Color</span>
                <div class="picker-controls">
                  <input type="color" v-model="settings.admin_sidebar_link" class="color-picker-box" />
                  <a-input v-model:value="settings.admin_sidebar_link" size="small" style="width: 110px" />
                </div>
              </div>

              <div class="color-picker-item">
                <span class="picker-label">Sidebar Menu/Setup Active Item Background Color</span>
                <div class="picker-controls">
                  <input type="color" v-model="settings.admin_sidebar_active_bg" class="color-picker-box" />
                  <a-input v-model:value="settings.admin_sidebar_active_bg" size="small" style="width: 110px" />
                </div>
              </div>

              <div class="color-picker-item">
                <span class="picker-label">Sidebar Menu/Setup Active Item Color</span>
                <div class="picker-controls">
                  <input type="color" v-model="settings.admin_sidebar_active_link" class="color-picker-box" />
                  <a-input v-model:value="settings.admin_sidebar_active_link" size="small" style="width: 110px" />
                </div>
              </div>

              <div class="color-picker-item">
                <span class="picker-label">Top Header Background Color</span>
                <div class="picker-controls">
                  <input type="color" v-model="settings.admin_header_bg" class="color-picker-box" />
                  <a-input v-model:value="settings.admin_header_bg" size="small" style="width: 110px" />
                </div>
              </div>

              <div class="color-picker-item">
                <span class="picker-label">Top Header Links Color</span>
                <div class="picker-controls">
                  <input type="color" v-model="settings.admin_header_link" class="color-picker-box" />
                  <a-input v-model:value="settings.admin_header_link" size="small" style="width: 110px" />
                </div>
              </div>

              <div class="color-picker-item">
                <span class="picker-label">Main Content Background Color</span>
                <div class="picker-controls">
                  <input type="color" v-model="settings.admin_content_bg" class="color-picker-box" />
                  <a-input v-model:value="settings.admin_content_bg" size="small" style="width: 110px" />
                </div>
              </div>
            </div>
          </div>
        </a-tab-pane>

        <!-- 2. CUSTOMERS AREA -->
        <a-tab-pane key="customers_area" tab="Customers Area">
          <div class="tab-content-wrap">
            <h3 class="tab-title">Customers Area Styling</h3>
            
            <div class="color-pickers-grid">
              <div class="color-picker-item">
                <span class="picker-label">Navigation Background Color</span>
                <div class="picker-controls">
                  <input type="color" v-model="settings.cust_nav_bg" class="color-picker-box" />
                  <a-input v-model:value="settings.cust_nav_bg" size="small" style="width: 110px" />
                </div>
              </div>

              <div class="color-picker-item">
                <span class="picker-label">Navigation Links Color</span>
                <div class="picker-controls">
                  <input type="color" v-model="settings.cust_nav_link" class="color-picker-box" />
                  <a-input v-model:value="settings.cust_nav_link" size="small" style="width: 110px" />
                </div>
              </div>

              <div class="color-picker-item">
                <span class="picker-label">Footer Background</span>
                <div class="picker-controls">
                  <input type="color" v-model="settings.cust_footer_bg" class="color-picker-box" />
                  <a-input v-model:value="settings.cust_footer_bg" size="small" style="width: 110px" />
                </div>
              </div>

              <div class="color-picker-item">
                <span class="picker-label">Footer Text Color</span>
                <div class="picker-controls">
                  <input type="color" v-model="settings.cust_footer_text" class="color-picker-box" />
                  <a-input v-model:value="settings.cust_footer_text" size="small" style="width: 110px" />
                </div>
              </div>
            </div>
          </div>
        </a-tab-pane>

        <!-- 3. BUTTONS -->
        <a-tab-pane key="buttons" tab="Buttons">
          <div class="tab-content-wrap">
            <h3 class="tab-title">Button Styling</h3>
            
            <div class="color-pickers-grid">
              <div class="color-picker-item">
                <span class="picker-label">Button Default</span>
                <div class="picker-controls">
                  <input type="color" v-model="settings.btn_default" class="color-picker-box" />
                  <a-input v-model:value="settings.btn_default" size="small" style="width: 110px" />
                </div>
                <button class="preview-btn" :style="{ backgroundColor: settings.btn_default, color: '#333', border: '1px solid #ccc' }">Default Button</button>
              </div>

              <div class="color-picker-item">
                <span class="picker-label">Button Primary</span>
                <div class="picker-controls">
                  <input type="color" v-model="settings.btn_primary" class="color-picker-box" />
                  <a-input v-model:value="settings.btn_primary" size="small" style="width: 110px" />
                </div>
                <button class="preview-btn" :style="{ backgroundColor: settings.btn_primary, color: '#fff' }">Primary Button</button>
              </div>

              <div class="color-picker-item">
                <span class="picker-label">Button Info</span>
                <div class="picker-controls">
                  <input type="color" v-model="settings.btn_info" class="color-picker-box" />
                  <a-input v-model:value="settings.btn_info" size="small" style="width: 110px" />
                </div>
                <button class="preview-btn" :style="{ backgroundColor: settings.btn_info, color: '#fff' }">Info Button</button>
              </div>

              <div class="color-picker-item">
                <span class="picker-label">Button Success</span>
                <div class="picker-controls">
                  <input type="color" v-model="settings.btn_success" class="color-picker-box" />
                  <a-input v-model:value="settings.btn_success" size="small" style="width: 110px" />
                </div>
                <button class="preview-btn" :style="{ backgroundColor: settings.btn_success, color: '#fff' }">Success Button</button>
              </div>

              <div class="color-picker-item">
                <span class="picker-label">Button Danger</span>
                <div class="picker-controls">
                  <input type="color" v-model="settings.btn_danger" class="color-picker-box" />
                  <a-input v-model:value="settings.btn_danger" size="small" style="width: 110px" />
                </div>
                <button class="preview-btn" :style="{ backgroundColor: settings.btn_danger, color: '#fff' }">Danger Button</button>
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
                    <input type="color" v-model="settings.modal_heading_bg" class="color-picker-box" />
                    <a-input v-model:value="settings.modal_heading_bg" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Heading Color</span>
                  <div class="picker-controls">
                    <input type="color" v-model="settings.modal_heading_color" class="color-picker-box" />
                    <a-input v-model:value="settings.modal_heading_color" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Close Button Color</span>
                  <div class="picker-controls">
                    <input type="color" v-model="settings.modal_close_color" class="color-picker-box" />
                    <a-input v-model:value="settings.modal_close_color" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Modal Header Text Color</span>
                  <div class="picker-controls">
                    <input type="color" v-model="settings.modal_header_text_color" class="color-picker-box" />
                    <a-input v-model:value="settings.modal_header_text_color" size="small" style="width: 110px" />
                  </div>
                </div>
              </div>

              <!-- Modal Preview Frame -->
              <div class="preview-panel" style="flex: 1;">
                <span class="preview-title">Visual Preview</span>
                <div class="modal-preview-box">
                  <div class="modal-preview-header" :style="{ backgroundColor: settings.modal_heading_bg }">
                    <span class="modal-preview-heading-text" :style="{ color: settings.modal_header_text_color }">Example Modal Heading</span>
                    <span class="modal-preview-close" :style="{ color: settings.modal_close_color }">×</span>
                  </div>
                  <div class="modal-preview-body">
                    <span class="sample-text-header" :style="{ color: settings.modal_heading_color }">Sample Text</span>
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
                    <input type="color" v-model="settings.table_link" class="color-picker-box" />
                    <a-input v-model:value="settings.table_link" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Table Links Hover/Focus Color</span>
                  <div class="picker-controls">
                    <input type="color" v-model="settings.table_link_hover" class="color-picker-box" />
                    <a-input v-model:value="settings.table_link_hover" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Table Headings Color</span>
                  <div class="picker-controls">
                    <input type="color" v-model="settings.table_heading" class="color-picker-box" />
                    <a-input v-model:value="settings.table_heading" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Items Table Headings Background Color</span>
                  <div class="picker-controls">
                    <input type="color" v-model="settings.table_items_heading_bg" class="color-picker-box" />
                    <a-input v-model:value="settings.table_items_heading_bg" size="small" style="width: 110px" />
                  </div>
                </div>

                <div class="color-picker-item">
                  <span class="picker-label">Items Table Headings Text Color</span>
                  <div class="picker-controls">
                    <input type="color" v-model="settings.table_items_heading_text" class="color-picker-box" />
                    <a-input v-model:value="settings.table_items_heading_text" size="small" style="width: 110px" />
                  </div>
                </div>
              </div>

              <!-- Table Preview Frame -->
              <div class="preview-panel" style="flex: 1;">
                <span class="preview-title">Visual Preview</span>
                <table class="table-preview-control">
                  <thead :style="{ backgroundColor: settings.table_items_heading_bg, color: settings.table_items_heading_text }">
                    <tr>
                      <th>Example Heading 1</th>
                      <th>Example Heading 2</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>
                        <a href="#" :style="{ color: settings.table_link }">Table Link</a>
                      </td>
                      <td :style="{ color: settings.table_heading }">Heading Text</td>
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
import { defineComponent, ref, reactive } from 'vue';
import { message } from 'ant-design-vue';

export default defineComponent({
  name: 'ThemeStyleView',
  setup() {
    const activeTab = ref('admin_area');
    const saving = ref(false);

    const settings = reactive({
      admin_sidebar_bg: '#1e293b',
      admin_sidebar_link: '#94a3b8',
      admin_sidebar_active_bg: '#334155',
      admin_sidebar_active_link: '#ffffff',
      admin_header_bg: '#ffffff',
      admin_header_link: '#475569',
      admin_content_bg: '#f8fafc',

      cust_nav_bg: '#4f46e5',
      cust_nav_link: '#ffffff',
      cust_footer_bg: '#1e293b',
      cust_footer_text: '#94a3b8',

      btn_default: '#f1f5f9',
      btn_primary: '#4f46e5',
      btn_info: '#06b6d4',
      btn_success: '#10b981',
      btn_danger: '#ef4444',

      modal_heading_bg: '#f1f5f9',
      modal_heading_color: '#475569',
      modal_close_color: '#94a3b8',
      modal_header_text_color: '#0f172a',

      table_link: '#4f46e5',
      table_link_hover: '#4338ca',
      table_heading: '#0f172a',
      table_items_heading_bg: '#f8fafc',
      table_items_heading_text: '#475569',

      gen_link: '#4f46e5',
      gen_link_hover: '#4338ca',
      gen_login_bg: '#f8fafc',
      gen_text_muted: '#64748b',
      gen_text_danger: '#ef4444',
      gen_text_warning: '#f59e0b',
      gen_text_info: '#0b99ff',
      gen_text_success: '#10b981',

      custom_css_tab: 'admin',
      custom_css_admin: '/* Custom Admin CSS Rules */\nbody {\n  letter-spacing: -0.01em;\n}',
      custom_css_customer: '/* Custom Customers CSS Rules */\n.btn-custom {\n  border-radius: 9999px;\n}'
    });

    const tags = ref([
      { name: 'bug', color: '#ef4444' },
      { name: 'follow up', color: '#3b82f6' },
      { name: 'important', color: '#f59e0b' },
      { name: 'logo', color: '#06b6d4' },
      { name: 'review', color: '#8b5cf6' },
      { name: 'todo', color: '#64748b' },
      { name: 'tomorrow', color: '#10b981' },
      { name: 'wordpress', color: '#14b8a6' }
    ]);

    const saveThemeSettings = () => {
      saving.value = true;
      setTimeout(() => {
        saving.value = false;
        message.success('Theme Style settings saved successfully');
      }, 600);
    };

    return {
      activeTab,
      saving,
      settings,
      tags,
      saveThemeSettings
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
