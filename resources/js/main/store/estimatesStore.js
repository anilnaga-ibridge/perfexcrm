import { defineStore } from 'pinia';

const defaultEstimates = [
  { 
    id: 1, 
    number: 'EST-00001', 
    client: 'Nader-Abernathy', 
    status: 'draft', 
    amount: 3200, 
    date: 'Jun 01, 2026', 
    expiry: 'Jul 01, 2026',
    currency: 'USD',
    discount_type: 'No discount',
    discount_percent: 0,
    adjustment: 0,
    tags: 'Consulting',
    reference_no: 'REF-001',
    sale_agent: 'Tom Kunze',
    admin_note: 'Monthly review estimate',
    client_note: 'Thank you for your business.',
    terms: 'Mouse had changed his mind, and was immediately suppressed by the time they were playing the Queen ordering off her knowledge, as there was the Rabbit in a great letter, nearly as large as the other. As soon as there was enough of me left to make myself useful, and looking anxiously round to see.',
    items: [
      { name: 'Consulting SLA Services', long_description: 'Standard system strategy consulting package.', qty: 2, unit: 'Unit', rate: 1600, tax: 0, amount: 3200, optional: false }
    ],
    billing_street: '123 Business Rd',
    billing_city: 'New York',
    billing_state: 'NY',
    billing_zip: '10001',
    billing_country: 'United States',
    shipping_street: '123 Business Rd',
    shipping_city: 'New York',
    shipping_state: 'NY',
    shipping_zip: '10001',
    shipping_country: 'United States'
  },
  { 
    id: 2, 
    number: 'EST-00002', 
    client: 'Mertz-Bergnaum', 
    status: 'sent', 
    amount: 5800, 
    date: 'Jun 03, 2026', 
    expiry: 'Jul 03, 2026',
    currency: 'USD',
    discount_type: 'No discount',
    discount_percent: 0,
    adjustment: 0,
    tags: 'Development',
    reference_no: 'REF-002',
    sale_agent: 'Armando Turcotte',
    admin_note: 'App features estimate',
    client_note: 'Estimated delivery: 3 weeks.',
    terms: 'Mouse had changed his mind, and was immediately suppressed by the time they were playing the Queen ordering off her knowledge...',
    items: [
      { name: 'App Development Module v1.0', long_description: 'Custom module dev & deployment integration.', qty: 1, unit: 'Unit', rate: 5800, tax: 0, amount: 5800, optional: false }
    ],
    billing_street: '456 Industrial Way',
    billing_city: 'Los Angeles',
    billing_state: 'CA',
    billing_zip: '90001',
    billing_country: 'United States',
    shipping_street: '456 Industrial Way',
    shipping_city: 'Los Angeles',
    shipping_state: 'CA',
    shipping_zip: '90001',
    shipping_country: 'United States'
  },
  { 
    id: 3, 
    number: 'EST-00003', 
    client: 'Schroeder and Sons', 
    status: 'accepted', 
    amount: 12400, 
    date: 'May 20, 2026', 
    expiry: 'Jun 20, 2026',
    currency: 'USD',
    discount_type: 'No discount',
    discount_percent: 0,
    adjustment: 0,
    tags: 'Infrastructure',
    reference_no: 'REF-003',
    sale_agent: 'Marcus Lesch',
    admin_note: 'Cloud migration setup proposal',
    client_note: '',
    terms: 'Standard legal terms of Schroeder and Sons partnership.',
    items: [
      { name: 'Cloud Infrastructure Audit Pack', long_description: 'AWS environment optimization strategy.', qty: 1, unit: 'Unit', rate: 12400, tax: 0, amount: 12400, optional: false }
    ],
    billing_street: '789 Cloud Heights',
    billing_city: 'Seattle',
    billing_state: 'WA',
    billing_zip: '98101',
    billing_country: 'United States',
    shipping_street: '789 Cloud Heights',
    shipping_city: 'Seattle',
    shipping_state: 'WA',
    shipping_zip: '98101',
    shipping_country: 'United States'
  },
  { 
    id: 4, 
    number: 'EST-00004', 
    client: 'Bayer Group', 
    status: 'declined', 
    amount: 2100, 
    date: 'May 15, 2026', 
    expiry: 'Jun 15, 2026',
    currency: 'USD',
    discount_type: 'No discount',
    discount_percent: 0,
    adjustment: 0,
    tags: 'Marketing',
    reference_no: 'REF-004',
    sale_agent: 'Rosie Trantow',
    admin_note: 'Declined due to budget constraints',
    client_note: '',
    terms: 'Standard terms.',
    items: [
      { name: 'UI/UX Interactive Mockup Designs', long_description: 'Bayer portal user interaction wireframes.', qty: 1, unit: 'Unit', rate: 2100, tax: 0, amount: 2100, optional: false }
    ],
    billing_street: 'Otto-Suhr-Allee 120',
    billing_city: 'Berlin',
    billing_state: 'Berlin',
    billing_zip: '10585',
    billing_country: 'Germany',
    shipping_street: 'Otto-Suhr-Allee 120',
    shipping_city: 'Berlin',
    shipping_state: 'Berlin',
    shipping_zip: '10585',
    shipping_country: 'Germany'
  },
  { 
    id: 5, 
    number: 'EST-00005', 
    client: 'Halvorson LLC', 
    status: 'sent', 
    amount: 7650, 
    date: 'Jun 05, 2026', 
    expiry: 'Jul 05, 2026',
    currency: 'USD',
    discount_type: 'No discount',
    discount_percent: 0,
    adjustment: 0,
    tags: 'Development',
    reference_no: '',
    sale_agent: 'Tamara Howell',
    admin_note: '',
    client_note: '',
    terms: '',
    items: [
      { name: 'Custom App Development Module', qty: 1, unit: 'Unit', rate: 7650, tax: 0, amount: 7650, optional: false }
    ],
    billing_street: '888 Wacker Dr',
    billing_city: 'Chicago',
    billing_state: 'IL',
    billing_zip: '60601',
    billing_country: 'United States',
    shipping_street: '888 Wacker Dr',
    shipping_city: 'Chicago',
    shipping_state: 'IL',
    shipping_zip: '60601',
    shipping_country: 'United States'
  },
  { 
    id: 6, 
    number: 'EST-00006', 
    client: 'Kub Group', 
    status: 'accepted', 
    amount: 4300, 
    date: 'Apr 28, 2026', 
    expiry: 'May 28, 2026',
    currency: 'USD',
    discount_type: 'No discount',
    discount_percent: 0,
    adjustment: 0,
    tags: '',
    reference_no: '',
    sale_agent: 'Tom Kunze',
    admin_note: '',
    client_note: '',
    terms: '',
    items: [
      { name: 'SLA Support retainer', qty: 1, unit: 'Unit', rate: 4300, tax: 0, amount: 4300, optional: false }
    ],
    billing_street: '777 Texas Ave',
    billing_city: 'Houston',
    billing_state: 'TX',
    billing_zip: '77002',
    billing_country: 'United States',
    shipping_street: '777 Texas Ave',
    shipping_city: 'Houston',
    shipping_state: 'TX',
    shipping_zip: '77002',
    shipping_country: 'United States'
  },
  { 
    id: 7, 
    number: 'EST-00007', 
    client: 'Morar-Runte', 
    status: 'expired', 
    amount: 1800, 
    date: 'Mar 10, 2026', 
    expiry: 'Apr 10, 2026',
    currency: 'USD',
    discount_type: 'No discount',
    discount_percent: 0,
    adjustment: 0,
    tags: '',
    reference_no: '',
    sale_agent: 'Elias Konopelski',
    admin_note: '',
    client_note: '',
    terms: '',
    items: [
      { name: 'Basic Audit pack', qty: 1, unit: 'Unit', rate: 1800, tax: 0, amount: 1800, optional: false }
    ],
    billing_street: '',
    billing_city: '',
    billing_state: '',
    billing_zip: '',
    billing_country: '',
    shipping_street: '',
    shipping_city: '',
    shipping_state: '',
    shipping_zip: '',
    shipping_country: ''
  },
  { 
    id: 8, 
    number: 'EST-00008', 
    client: 'Torphy and Partners', 
    status: 'sent', 
    amount: 9200, 
    date: 'Jun 08, 2026', 
    expiry: 'Jul 08, 2026',
    currency: 'USD',
    discount_type: 'No discount',
    discount_percent: 0,
    adjustment: 0,
    tags: '',
    reference_no: '',
    sale_agent: 'Rosie Trantow',
    admin_note: '',
    client_note: '',
    terms: '',
    items: [
      { name: 'App Development module v1.0', qty: 1, unit: 'Unit', rate: 9200, tax: 0, amount: 9200, optional: false }
    ],
    billing_street: '',
    billing_city: '',
    billing_state: '',
    billing_zip: '',
    billing_country: '',
    shipping_street: '',
    shipping_city: '',
    shipping_state: '',
    shipping_zip: '',
    shipping_country: ''
  }
];

export const useEstimatesStore = defineStore('estimates', {
  state: () => {
    const stored = localStorage.getItem('perfex_estimates');
    return {
      estimates: stored ? JSON.parse(stored) : defaultEstimates,
    };
  },
  
  actions: {
    saveToStorage() {
      localStorage.setItem('perfex_estimates', JSON.stringify(this.estimates));
    },
    
    addEstimate(estimate) {
      const newId = this.estimates.length > 0 ? Math.max(...this.estimates.map(e => e.id)) + 1 : 1;
      const number = `EST-${String(newId).padStart(6, '0')}`;
      
      const newEst = {
        ...estimate,
        id: newId,
        number
      };
      
      this.estimates.unshift(newEst);
      this.saveToStorage();
      return newEst;
    },
    
    updateEstimate(id, updatedData) {
      const index = this.estimates.findIndex(e => e.id === id);
      if (index !== -1) {
        this.estimates[index] = {
          ...this.estimates[index],
          ...updatedData
        };
        this.saveToStorage();
      }
    },
    
    deleteEstimate(id) {
      this.estimates = this.estimates.filter(e => e.id !== id);
      this.saveToStorage();
    }
  }
});
