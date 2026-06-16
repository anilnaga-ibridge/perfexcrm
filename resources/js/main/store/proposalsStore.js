import { defineStore } from 'pinia';

const defaultProposals = [
  { 
    id: 1, 
    number: 'PRO-00001', 
    subject: 'Consulting & Strategy Services', 
    to: 'Nader-Abernathy', 
    status: 'accepted', 
    amount: 4500, 
    date: 'Jun 01, 2026', 
    open_till: 'Jun 15, 2026',
    recipient_name: 'Nader-Abernathy',
    address: '123 Business Rd',
    city: 'New York',
    state: 'NY',
    country: 'United States',
    zip: '10001',
    email: 'billing@nader.example.com',
    phone: '+1-650-555-0101',
    currency: 'USD',
    discount_type: 'No discount',
    discount_percent: 0,
    adjustment: 0,
    tags: 'Strategy',
    allow_comments: true,
    assigned: 'Armando Turcotte',
    items: [
      { name: 'Consulting & Strategy Services', long_description: 'Strategic review and roadmap compilation', qty: 1, unit: 'hrs', rate: 4500, tax: 0, amount: 4500 }
    ]
  },
  { 
    id: 2, 
    number: 'PRO-00002', 
    subject: 'Custom App Development', 
    to: 'Mertz-Bergnaum', 
    status: 'sent', 
    amount: 18500, 
    date: 'Jun 05, 2026', 
    open_till: 'Jun 25, 2026',
    recipient_name: 'Mertz-Bergnaum',
    address: '456 Industrial Way',
    city: 'Los Angeles',
    state: 'CA',
    country: 'United States',
    zip: '90001',
    email: 'dev@mertz.example.com',
    phone: '+1-650-555-0102',
    currency: 'USD',
    discount_type: 'No discount',
    discount_percent: 0,
    adjustment: 0,
    tags: 'Dev',
    allow_comments: true,
    assigned: 'Marcus Lesch',
    items: [
      { name: 'Custom App Development', long_description: 'Building custom app features', qty: 1, unit: 'hrs', rate: 18500, tax: 0, amount: 18500 }
    ]
  },
  { 
    id: 3, 
    number: 'PRO-00003', 
    subject: 'SEO & Marketing Campaign', 
    to: 'Sarah Connor (Lead)', 
    status: 'open', 
    amount: 2400, 
    date: 'Jun 08, 2026', 
    open_till: 'Jun 22, 2026',
    recipient_name: 'Sarah Connor',
    address: '99 Destiny Drive',
    city: 'San Francisco',
    state: 'CA',
    country: 'United States',
    zip: '94103',
    email: 'sarah.conner@leads.example.com',
    phone: '+1-555-0201',
    currency: 'USD',
    discount_type: 'No discount',
    discount_percent: 0,
    adjustment: 0,
    tags: 'SEO',
    allow_comments: true,
    assigned: 'Elias Konopelski',
    items: [
      { name: 'SEO & Marketing Campaign', long_description: 'Organic search marketing optimization', qty: 1, unit: 'hrs', rate: 2400, tax: 0, amount: 2400 }
    ]
  },
  { 
    id: 4, 
    number: 'PRO-00004', 
    subject: 'Website Redesign Proposal', 
    to: 'Bayer Group', 
    status: 'declined', 
    amount: 3800, 
    date: 'May 20, 2026', 
    open_till: 'Jun 10, 2026',
    recipient_name: 'Bayer Group',
    address: 'Otto-Suhr-Allee 120',
    city: 'Berlin',
    state: 'Berlin',
    country: 'Germany',
    zip: '10585',
    email: 'hello@bayer.example.de',
    phone: '+49-30-555-0104',
    currency: 'USD',
    discount_type: 'No discount',
    discount_percent: 0,
    adjustment: 0,
    tags: 'Web',
    allow_comments: true,
    assigned: 'Rosie Trantow',
    items: [
      { name: 'Website Redesign Proposal', long_description: 'UX wireframes and mockups development', qty: 1, unit: 'pcs', rate: 3800, tax: 0, amount: 3800 }
    ]
  },
  { 
    id: 5, 
    number: 'PRO-00005', 
    subject: 'Cloud Infrastructure Upgrade', 
    to: 'Halvorson LLC', 
    status: 'accepted', 
    amount: 12000, 
    date: 'May 28, 2026', 
    open_till: 'Jun 18, 2026',
    recipient_name: 'Halvorson LLC',
    address: '888 Wacker Dr',
    city: 'Chicago',
    state: 'IL',
    country: 'United States',
    zip: '60601',
    email: 'info@halvorson.example.com',
    phone: '+1-650-555-0105',
    currency: 'USD',
    discount_type: 'No discount',
    discount_percent: 0,
    adjustment: 0,
    tags: 'Cloud',
    allow_comments: true,
    assigned: 'Armando Turcotte',
    items: [
      { name: 'Cloud Infrastructure Upgrade', long_description: 'Server migration to AWS cloud environment', qty: 1, unit: 'hrs', rate: 12000, tax: 0, amount: 12000 }
    ]
  },
  { 
    id: 6, 
    number: 'PRO-00006', 
    subject: 'Technical Support SLA', 
    to: 'Kub Group', 
    status: 'draft', 
    amount: 1500, 
    date: 'Jun 12, 2026', 
    open_till: 'Jul 12, 2026',
    recipient_name: 'Kub Group',
    address: '777 Texas Ave',
    city: 'Houston',
    state: 'TX',
    country: 'United States',
    zip: '77002',
    email: 'admin@kubgroup.example.com',
    phone: '+1-650-555-0106',
    currency: 'USD',
    discount_type: 'No discount',
    discount_percent: 0,
    adjustment: 0,
    tags: 'Support',
    allow_comments: true,
    assigned: 'Tamara Howell',
    items: [
      { name: 'Technical Support SLA', long_description: 'Support SLA monthly subscription fee', qty: 1, unit: 'month', rate: 1500, tax: 0, amount: 1500 }
    ]
  },
  { 
    id: 7, 
    number: 'PRO-00007', 
    subject: 'Corporate Branding Pack', 
    to: 'Bruce Wayne (Lead)', 
    status: 'open', 
    amount: 6000, 
    date: 'Jun 11, 2026', 
    open_till: 'Jun 30, 2026',
    recipient_name: 'Bruce Wayne',
    address: '1007 Mountain Drive',
    city: 'Gotham City',
    state: 'NJ',
    country: 'United States',
    zip: '07001',
    email: 'bruce@waynecorp.example.com',
    phone: '+1-555-0992',
    currency: 'USD',
    discount_type: 'No discount',
    discount_percent: 0,
    adjustment: 0,
    tags: 'Design',
    allow_comments: true,
    assigned: 'Tom Kunze',
    items: [
      { name: 'Corporate Branding Pack', long_description: 'Logo design, typography guideline book', qty: 1, unit: 'pcs', rate: 6000, tax: 0, amount: 6000 }
    ]
  }
];

export const useProposalsStore = defineStore('proposals', {
  state: () => {
    const stored = localStorage.getItem('perfex_proposals');
    return {
      proposals: stored ? JSON.parse(stored) : defaultProposals,
    };
  },
  
  actions: {
    saveToStorage() {
      localStorage.setItem('perfex_proposals', JSON.stringify(this.proposals));
    },
    
    addProposal(proposal) {
      const newId = this.proposals.length > 0 ? Math.max(...this.proposals.map(p => p.id)) + 1 : 1;
      const number = `PRO-${String(newId).padStart(5, '0')}`;
      
      const newProp = {
        ...proposal,
        id: newId,
        number
      };
      
      this.proposals.unshift(newProp);
      this.saveToStorage();
      return newProp;
    },
    
    updateProposal(id, updatedData) {
      const index = this.proposals.findIndex(p => p.id === id);
      if (index !== -1) {
        this.proposals[index] = {
          ...this.proposals[index],
          ...updatedData
        };
        this.saveToStorage();
      }
    },
    
    deleteProposal(id) {
      this.proposals = this.proposals.filter(p => p.id !== id);
      this.saveToStorage();
    }
  }
});
