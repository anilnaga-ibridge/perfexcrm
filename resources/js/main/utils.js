export function getFinanceSettings() {
  try {
    const stored = localStorage.getItem('ibridge_settings');
    if (stored) {
      return JSON.parse(stored);
    }
  } catch (e) {
    console.error('Failed to parse settings', e);
  }
  
  // Return standard defaults
  return {
    finance_decimal_separator: '.',
    finance_thousand_separator: ',',
    finance_number_padding: 6,
    finance_auto_sale_agent: false,
    finance_show_tax_per_item: true,
    finance_remove_tax_name: false,
    finance_exclude_currency_symbol: false,
    finance_default_tax: '18.00',
    finance_remove_decimals_zero: true,
    finance_amount_to_words_enabled: true,
    finance_amount_to_words_lowercase: false,
  };
}

export function formatMoney(val, customSettings = null) {
  const settings = customSettings || getFinanceSettings();
  
  const decSep = settings.finance_decimal_separator || '.';
  const thouSep = settings.finance_thousand_separator || ',';
  const removeZeroDec = settings.finance_remove_decimals_zero ?? true;
  const excludeSymbol = settings.finance_exclude_currency_symbol ?? false;
  
  let num = parseFloat(val);
  if (isNaN(num)) num = 0;
  
  // Format with 2 decimal places
  let formatted = num.toFixed(2);
  
  if (removeZeroDec && parseFloat(formatted.split('.')[1]) === 0) {
    formatted = num.toFixed(0);
  }
  
  let parts = formatted.split('.');
  // Add thousands separator
  parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thouSep);
  
  const formattedNumber = parts.join(decSep);
  
  return excludeSymbol ? formattedNumber : '$' + formattedNumber;
}

export function numberToWords(num, isLowercase = false) {
  const ones = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
  const tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
  const scales = ['', 'thousand', 'million', 'billion', 'trillion'];

  let n = parseFloat(num);
  if (isNaN(n) || n <= 0) return isLowercase ? 'zero dollars' : 'Zero dollars';

  // Extract dollars and cents
  const dollars = Math.floor(n);
  const cents = Math.round((n - dollars) * 100);

  function convertChunk(chunk) {
    let result = '';
    if (chunk >= 100) {
      result += ones[Math.floor(chunk / 100)] + ' hundred';
      chunk %= 100;
      if (chunk > 0) result += ' ';
    }
    if (chunk >= 20) {
      result += tens[Math.floor(chunk / 10)];
      if (chunk % 10 > 0) {
        result += '-' + ones[chunk % 10];
      }
    } else if (chunk > 0) {
      result += ones[chunk];
    }
    return result;
  }

  let words = '';
  if (dollars === 0) {
    words = 'zero dollars';
  } else {
    let temp = dollars;
    let scaleIdx = 0;
    while (temp > 0) {
      const chunk = temp % 1000;
      if (chunk > 0) {
        const chunkStr = convertChunk(chunk);
        words = chunkStr + (scales[scaleIdx] ? ' ' + scales[scaleIdx] : '') + (words ? ' ' + words : '');
      }
      temp = Math.floor(temp / 1000);
      scaleIdx++;
    }
    words = words.trim();
    words += (dollars === 1 ? ' dollar' : ' dollars');
  }

  if (cents > 0) {
    let centsStr = convertChunk(cents);
    words += ' and ' + centsStr + (cents === 1 ? ' cent' : ' cents');
  }

  if (isLowercase) {
    return words.toLowerCase();
  } else {
    return words.charAt(0).toUpperCase() + words.slice(1);
  }
}

export function applyThemeStyles(settings) {
  if (!settings) return;

  // 0. Dynamic Favicon
  if (settings.favicon_url) {
    let faviconUrl = settings.favicon_url;
    if (!faviconUrl.startsWith('data:') && !faviconUrl.startsWith('http://') && !faviconUrl.startsWith('https://')) {
      const basePath = (typeof window !== 'undefined' && window.config && window.config.path) ? window.config.path : '';
      if (faviconUrl.startsWith('/')) {
        faviconUrl = basePath ? (basePath + faviconUrl) : faviconUrl;
      } else {
        faviconUrl = basePath ? (basePath + '/' + faviconUrl) : faviconUrl;
      }
    }

    // Remove existing favicon link elements to force browser tab icon update
    const existingIcons = document.querySelectorAll("link[rel*='icon']");
    existingIcons.forEach(icon => {
      if (icon.parentNode) {
        icon.parentNode.removeChild(icon);
      }
    });

    // Create a new icon link element
    const newLink = document.createElement('link');
    newLink.id = 'crm-dynamic-favicon';
    newLink.rel = 'icon';

    if (faviconUrl.startsWith('data:image/svg+xml')) {
      newLink.type = 'image/svg+xml';
      newLink.href = faviconUrl;
    } else if (faviconUrl.startsWith('data:image/png')) {
      newLink.type = 'image/png';
      newLink.href = faviconUrl;
    } else if (faviconUrl.startsWith('data:image/x-icon') || faviconUrl.startsWith('data:image/vnd.microsoft.icon')) {
      newLink.type = 'image/x-icon';
      newLink.href = faviconUrl;
    } else if (faviconUrl.startsWith('data:')) {
      newLink.type = 'image/png';
      newLink.href = faviconUrl;
    } else {
      newLink.type = faviconUrl.endsWith('.png') ? 'image/png' : faviconUrl.endsWith('.svg') ? 'image/svg+xml' : 'image/x-icon';
      newLink.href = faviconUrl + (faviconUrl.includes('?') ? '&' : '?') + '_t=' + Date.now();
    }

    document.getElementsByTagName('head')[0].appendChild(newLink);
  }

  // 0.1 Dynamic Page Title Suffix
  if (settings.app_page_title) {
    const currentTitle = document.title || '';
    const titleParts = currentTitle.split(' - ');
    const pagePrefix = titleParts.length > 0 && titleParts[0].trim() ? titleParts[0].trim() : 'Dashboard';
    document.title = `${pagePrefix} - ${settings.app_page_title}`;
  }

  let styleEl = document.getElementById('crm-dynamic-theme-styles');
  if (!styleEl) {
    styleEl = document.createElement('style');
    styleEl.id = 'crm-dynamic-theme-styles';
    document.head.appendChild(styleEl);
  }

  // If in Vuexy theme template mode, do not inject legacy organic styles
  const activeTemplate = localStorage.getItem('crm_active_theme_template') || 'vuexy';
  if (activeTemplate === 'vuexy') {
    styleEl.textContent = '';
    return;
  }

  let css = '';

  // 0.2 Typography & Font Controls
  if (settings.font_family) {
    css += `
      body, html, #app, .theme-style-page, .crm-main {
        font-family: ${settings.font_family} !important;
      }
    `;
  }
  if (settings.font_base_size) {
    css += `
      body, p, td, .crm-main {
        font-size: ${settings.font_base_size};
      }
    `;
  }
  if (settings.heading_weight) {
    css += `
      h1, h2, h3, h4, h5, h6, .page-title, .tab-title, .welcome-title {
        font-weight: ${settings.heading_weight} !important;
      }
    `;
  }
  if (settings.text_heading_color) {
    css += `
      h1, h2, h3, h4, h5, h6, .page-title, .tab-title {
        color: ${settings.text_heading_color} !important;
      }
    `;
  }
  if (settings.page_title_color) {
    css += `
      .page-title, h1.page-title {
        color: ${settings.page_title_color} !important;
      }
    `;
  }
  if (settings.page_title_size) {
    css += `
      .page-title, h1.page-title {
        font-size: ${settings.page_title_size} !important;
      }
    `;
  }
  if (settings.page_title_weight) {
    css += `
      .page-title, h1.page-title {
        font-weight: ${settings.page_title_weight} !important;
      }
    `;
  }
  if (settings.text_body_color) {
    css += `
      body, p, td {
        color: ${settings.text_body_color} !important;
      }
    `;
  }
  if (settings.text_muted_color) {
    css += `
      .text-muted, .tab-subtitle {
        color: ${settings.text_muted_color} !important;
      }
    `;
  }
  if (settings.text_link_color) {
    css += `
      a, .crm-header-link {
        color: ${settings.text_link_color} !important;
      }
    `;
  }

  // 1. Admin Area
  if (settings.admin_sidebar_bg) {
    css += `
      .crm-sidebar {
        background: linear-gradient(135deg, #d35400 0%, \${settings.admin_sidebar_bg} 50%, #0b579f 100%) !important;
      }
    `;
  }
  if (settings.admin_sidebar_link) {
    css += `
      .crm-nav-item, .crm-submenu-item {
        color: \${settings.admin_sidebar_link} !important;
      }
    `;
  }
  if (settings.admin_sidebar_active_bg) {
    css += `
      .crm-nav-item--active, .crm-submenu-item--active {
        background: \${settings.admin_sidebar_active_bg} !important;
      }
      .crm-nav-item--active::after {
        background: \${settings.admin_sidebar_active_link || '#ffffff'} !important;
      }
    `;
  }
  if (settings.admin_sidebar_active_link) {
    css += `
      .crm-nav-item--active, .crm-submenu-item--active {
        color: \${settings.admin_sidebar_active_link} !important;
      }
    `;
  }
  if (settings.admin_header_bg) {
    css += `
      .crm-header {
        background: \${settings.admin_header_bg} !important;
      }
    `;
  }
  if (settings.admin_header_link) {
    css += `
      .crm-header-link {
        color: \${settings.admin_header_link} !important;
      }
    `;
  }
  if (settings.admin_content_bg) {
    css += `
      .crm-content-body, .crm-main, .settings-card {
        background-color: \${settings.admin_content_bg} !important;
      }
    `;
  }

  // 2. Customers Area (Portal Nav)
  if (settings.cust_nav_bg) {
    css += `
      .portal-navbar {
        background: linear-gradient(135deg, #d35400 0%, \${settings.cust_nav_bg} 50%, #0b579f 100%) !important;
      }
    `;
  }
  if (settings.cust_nav_link) {
    css += `
      .portal-navbar-link {
        color: \${settings.cust_nav_link} !important;
      }
    `;
  }
  if (settings.cust_footer_bg) {
    css += `
      .portal-footer {
        background-color: \${settings.cust_footer_bg} !important;
      }
    `;
  }
  if (settings.cust_footer_text) {
    css += `
      .portal-footer-text {
        color: \${settings.cust_footer_text} !important;
      }
    `;
  }

  // 3. Buttons
  if (settings.btn_primary) {
    css += `
      :root, body {
        --ant-primary-color: \${settings.btn_primary} !important;
        --ant-primary-color-hover: \${settings.btn_primary}cc !important;
        --ant-primary-color-active: \${settings.btn_primary}e6 !important;
        --ant-primary-color-outline: \${settings.btn_primary}22 !important;
        --primary-color: \${settings.btn_primary} !important;
        --primary-color-hover: \${settings.btn_primary}cc !important;
        --primary-color-active: \${settings.btn_primary}e6 !important;
      }
      .ant-btn-primary,
      button.ant-btn-primary,
      .btn-primary,
      button.btn-primary,
      input[type="button"].btn-primary,
      input[type="submit"].btn-primary {
        background: linear-gradient(135deg, #d35400 0%, \${settings.btn_primary} 50%, #0b579f 100%) !important;
        background-image: linear-gradient(135deg, #d35400 0%, \${settings.btn_primary} 50%, #0b579f 100%) !important;
        border-color: transparent !important;
        border: none !important;
        color: #ffffff !important;
        box-shadow: 0px 4px 14px 0px rgba(126, 30, 142, 0.2) !important;
      }
      .ant-btn-primary:hover,
      .ant-btn-primary:focus,
      .ant-btn-primary:active,
      button.ant-btn-primary:hover,
      .btn-primary:hover,
      .btn-primary:focus,
      .btn-primary:active,
      button.btn-primary:hover {
        background: linear-gradient(135deg, #d35400 0%, \${settings.btn_primary} 50%, #0b579f 100%) !important;
        background-image: linear-gradient(135deg, #d35400 0%, \${settings.btn_primary} 50%, #0b579f 100%) !important;
        opacity: 0.9 !important;
        color: #ffffff !important;
      }
      .ant-btn-primary span,
      .btn-primary span {
        color: #ffffff !important;
      }
    `;
  }
  if (settings.btn_default) {
    css += `
      .ant-btn-default,
      button.ant-btn-default,
      .btn-default,
      button.btn-default {
        background: linear-gradient(135deg, #d35400 0%, \${settings.btn_default} 50%, #0b579f 100%) !important;
        background-image: linear-gradient(135deg, #d35400 0%, \${settings.btn_default} 50%, #0b579f 100%) !important;
        border-color: transparent !important;
        border: none !important;
        color: #ffffff !important;
      }
      .ant-btn-default:hover,
      .ant-btn-default:focus,
      .ant-btn-default:active,
      .btn-default:hover,
      .btn-default:focus,
      .btn-default:active {
        background: linear-gradient(135deg, #d35400 0%, \${settings.btn_default} 50%, #0b579f 100%) !important;
        background-image: linear-gradient(135deg, #d35400 0%, \${settings.btn_default} 50%, #0b579f 100%) !important;
        opacity: 0.9 !important;
        color: #ffffff !important;
      }
      .ant-btn-default span,
      .btn-default span {
        color: #ffffff !important;
      }
    `;
  }
  if (settings.btn_success) {
    css += `
      :root, body {
        --ant-success-color: \${settings.btn_success} !important;
        --ant-success-color-hover: \${settings.btn_success}cc !important;
        --ant-success-color-active: \${settings.btn_success}e6 !important;
      }
      .ant-btn-success,
      button.ant-btn-success,
      .btn-success,
      button.btn-success {
        background: linear-gradient(135deg, #d35400 0%, \${settings.btn_success} 50%, #0b579f 100%) !important;
        background-image: linear-gradient(135deg, #d35400 0%, \${settings.btn_success} 50%, #0b579f 100%) !important;
        border-color: transparent !important;
        border: none !important;
        color: #ffffff !important;
      }
      .ant-btn-success:hover,
      .ant-btn-success:focus,
      .ant-btn-success:active,
      .btn-success:hover,
      .btn-success:focus,
      .btn-success:active {
        background: linear-gradient(135deg, #d35400 0%, \${settings.btn_success} 50%, #0b579f 100%) !important;
        background-image: linear-gradient(135deg, #d35400 0%, \${settings.btn_success} 50%, #0b579f 100%) !important;
        opacity: 0.9 !important;
        color: #ffffff !important;
      }
      .ant-btn-success span,
      .btn-success span {
        color: #ffffff !important;
      }
    `;
  }
  if (settings.btn_danger) {
    css += `
      :root, body {
        --ant-error-color: \${settings.btn_danger} !important;
        --ant-error-color-hover: \${settings.btn_danger}cc !important;
        --ant-error-color-active: \${settings.btn_danger}e6 !important;
      }
      .ant-btn-danger,
      button.ant-btn-danger,
      .btn-danger,
      button.btn-danger {
        background: linear-gradient(135deg, #d35400 0%, \${settings.btn_danger} 50%, #0b579f 100%) !important;
        background-image: linear-gradient(135deg, #d35400 0%, \${settings.btn_danger} 50%, #0b579f 100%) !important;
        border-color: transparent !important;
        border: none !important;
        color: #ffffff !important;
      }
      .ant-btn-danger:hover,
      .ant-btn-danger:focus,
      .ant-btn-danger:active,
      .btn-danger:hover,
      .btn-danger:focus,
      .btn-danger:active {
        background: linear-gradient(135deg, #d35400 0%, \${settings.btn_danger} 50%, #0b579f 100%) !important;
        background-image: linear-gradient(135deg, #d35400 0%, \${settings.btn_danger} 50%, #0b579f 100%) !important;
        opacity: 0.9 !important;
        color: #ffffff !important;
      }
      .ant-btn-danger span,
      .btn-danger span {
        color: #ffffff !important;
      }
    `;
  }
  if (settings.btn_info) {
    css += `
      :root, body {
        --ant-info-color: \${settings.btn_info} !important;
        --ant-info-color-hover: \${settings.btn_info}cc !important;
        --ant-info-color-active: \${settings.btn_info}e6 !important;
      }
      .ant-btn-info,
      button.ant-btn-info,
      .btn-info,
      button.btn-info {
        background: linear-gradient(135deg, #d35400 0%, \${settings.btn_info} 50%, #0b579f 100%) !important;
        background-image: linear-gradient(135deg, #d35400 0%, \${settings.btn_info} 50%, #0b579f 100%) !important;
        border-color: transparent !important;
        border: none !important;
        color: #ffffff !important;
      }
      .ant-btn-info:hover,
      .ant-btn-info:focus,
      .ant-btn-info:active,
      .btn-info:hover,
      .btn-info:focus,
      .btn-info:active {
        background: linear-gradient(135deg, #d35400 0%, \${settings.btn_info} 50%, #0b579f 100%) !important;
        background-image: linear-gradient(135deg, #d35400 0%, \${settings.btn_info} 50%, #0b579f 100%) !important;
        opacity: 0.9 !important;
        color: #ffffff !important;
      }
      .ant-btn-info span,
      .btn-info span {
        color: #ffffff !important;
      }
    `;
  }

  // 4. Modals
  if (settings.modal_heading_bg) {
    css += `
      .ant-modal-header, .modal-header {
        background: linear-gradient(135deg, #d35400 0%, \${settings.modal_heading_bg} 50%, #0b579f 100%) !important;
      }
      .ant-modal-title, .modal-title {
        color: #ffffff !important;
      }
    `;
  }
  if (settings.modal_heading_color) {
    css += `
      .ant-modal-body h4, .modal-body h4 {
        color: \${settings.modal_heading_color} !important;
      }
    `;
  }
  if (settings.modal_close_color) {
    css += `
      .ant-modal-close, .modal-close {
        color: \${settings.modal_close_color} !important;
      }
    `;
  }

  // 5. Tables
  if (settings.table_items_heading_bg) {
    css += `
      .ant-table-thead > tr > th, .table thead th {
        background: linear-gradient(135deg, #d35400 0%, \${settings.table_items_heading_bg} 50%, #0b579f 100%) !important;
        color: \${settings.table_items_heading_text || '#ffffff'} !important;
      }
    `;
  }
  if (settings.table_link) {
    css += `
      .ant-table-cell a, .table a {
        color: \${settings.table_link} !important;
      }
    `;
  }
  if (settings.table_link_hover) {
    css += `
      .ant-table-cell a:hover, .table a:hover {
        color: \${settings.table_link_hover} !important;
      }
    `;
  }

  // 6. General Links & Muted Text
  if (settings.gen_link) {
    css += `
      a {
        color: \${settings.gen_link} !important;
      }
    `;
  }
  if (settings.gen_link_hover) {
    css += `
      a:hover {
        color: \${settings.gen_link_hover} !important;
      }
    `;
  }
  if (settings.gen_text_muted) {
    css += `
      .text-muted, .ant-typography-secondary {
        color: \${settings.gen_text_muted} !important;
      }
    `;
  }
  if (settings.gen_text_danger) {
    css += `
      .text-danger {
        color: \${settings.gen_text_danger} !important;
      }
    `;
  }
  if (settings.gen_text_warning) {
    css += `
      .text-warning {
        color: \${settings.gen_text_warning} !important;
      }
    `;
  }
  if (settings.gen_text_info) {
    css += `
      .text-info {
        color: \${settings.gen_text_info} !important;
      }
    `;
  }
  if (settings.gen_text_success) {
    css += `
      .text-success {
        color: \${settings.gen_text_success} !important;
      }
    `;
  }

  styleEl.textContent = css;
}
