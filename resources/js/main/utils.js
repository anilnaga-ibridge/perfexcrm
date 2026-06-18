export function getFinanceSettings() {
  try {
    const stored = localStorage.getItem('perfex_settings');
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
