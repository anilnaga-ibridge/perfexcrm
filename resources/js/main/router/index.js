import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../store/authStore';
import { h, defineComponent } from 'vue';

// Layouts & Views
import AdminLayout from '../layouts/AdminLayout.vue';
import Login      from '../views/auth/Login.vue';
import Dashboard  from '../views/Dashboard.vue';
import SetupPage  from '../views/Setup.vue';
import InvoicesPage from '../views/Invoices.vue';
import InvoiceView  from '../views/invoices/InvoiceView.vue';
import InvoiceForm  from '../views/invoices/InvoiceForm.vue';
import RecurringInvoicesPage from '../views/invoices/RecurringInvoices.vue';
import EstimatesPage from '../views/Estimates.vue';
import EstimateForm  from '../views/estimates/EstimateForm.vue';
import ProposalsPage from '../views/Proposals.vue';
import ProposalForm  from '../views/proposals/ProposalForm.vue';
import ExpensesPage from '../views/Expenses.vue';
import ProjectsPage from '../views/Projects.vue';
import ContractsPage from '../views/Contracts.vue';
import SupportPage from '../views/Support.vue';

// Real Modules Views
import CustomersList from '../views/customers/List.vue';
import CustomerCreate from '../views/customers/Create.vue';
import CustomerView  from '../views/customers/View.vue';
import ContactsList  from '../views/customers/AllContacts.vue';
import LeadsBoard    from '../views/leads/Kanban.vue';
import PaymentsPage  from '../views/sales/Payments.vue';
import PaymentView   from '../views/payments/PaymentView.vue';
import PaymentForm   from '../views/payments/PaymentForm.vue';
import CreditNotesPage from '../views/sales/CreditNotes.vue';
import CreditNoteForm  from '../views/sales/CreditNoteForm.vue';
import ItemsCatalogPage from '../views/sales/ItemsCatalog.vue';
import SubscriptionsPage from '../views/sales/Subscriptions.vue';
import TasksPage from '../views/tasks/Tasks.vue';
import TasksOverviewPage from '../views/tasks/TasksOverview.vue';

// Phase 3: Utilities
import MediaPage from '../views/utilities/Media.vue';
import CalendarPage from '../views/utilities/Calendar.vue';
import AnnouncementsPage from '../views/utilities/Announcements.vue';
import GoalsPage from '../views/utilities/Goals.vue';
import ActivityLogPage from '../views/utilities/ActivityLog.vue';
import BulkPdfExportPage from '../views/utilities/BulkPdfExport.vue';
import EInvoiceExportPage from '../views/utilities/EInvoiceExport.vue';
import CsvExportPage from '../views/utilities/CsvExport.vue';
import SurveysPage from '../views/utilities/Surveys.vue';
import MailListsPage from '../views/utilities/MailLists.vue';
import DatabaseBackupPage from '../views/utilities/DatabaseBackup.vue';
import TicketPipeLogPage from '../views/utilities/TicketPipeLog.vue';


// Phase 4: Support
import EstimateRequestsPage from '../views/support/EstimateRequests.vue';
import KnowledgeBasePage from '../views/support/KnowledgeBase.vue';
import KnowledgeBaseGroupsPage from '../views/support/Groups.vue';
import TicketsPage from '../views/support/Tickets.vue';
import SeoOptimisation from '../views/SeoOptimisation.vue';

// Staff Members
import StaffMembersPage from '../views/StaffMembers.vue';

// Profile
import ProfileLayout from '../views/Profile.vue';
import ProfileOverview from '../views/profile/ProfileOverview.vue';
import ProfileEdit from '../views/profile/ProfileEdit.vue';

// Timesheets
import TimesheetsPage from '../views/Timesheets.vue';

// My Todos
import MyTodosPage from '../views/MyTodos.vue';

// Notifications
import NotificationsPage from '../views/Notifications.vue';

// Phase 5: Reports
import ReportsPage from '../views/reports/Reports.vue';

// ── Nice "Coming Soon" placeholder ──────────────────────────
const ComingSoon = (title, icon = '🔧') => defineComponent({
  name: 'ComingSoon_' + title.replace(/\s/g, ''),
  render() {
    return h('div', { style: 'padding:0' }, [
      h('div', {
        style: 'display:flex;align-items:center;justify-content:space-between;margin-bottom:18px'
      }, [
        h('h1', { style: 'font-size:20px;font-weight:700;color:#1e293b;margin:0;font-family:Inter,sans-serif' }, title),
      ]),
      h('div', {
        style: `
          background:#fff;border:1px solid #e2e8f0;border-radius:8px;
          display:flex;flex-direction:column;align-items:center;justify-content:center;
          padding:80px 24px;gap:12px;
        `
      }, [
        h('div', { style: 'font-size:48px;line-height:1' }, icon),
        h('div', { style: 'font-size:18px;font-weight:700;color:#1e293b;font-family:Inter,sans-serif' }, title),
        h('div', { style: 'font-size:13px;color:#64748b;text-align:center;max-width:400px;font-family:Inter,sans-serif' },
          `The ${title} module is being built. Full functionality including data management, filters, and reporting will be available soon.`
        ),
        h('div', {
          style: 'margin-top:8px;padding:6px 18px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:20px;font-size:12px;color:#64748b;font-family:Inter,sans-serif'
        }, '🚧 Under Active Development'),
      ])
    ]);
  }
});

const routes = [
  { path: '/', redirect: '/admin/dashboard' },
  {
    path: '/admin/login',
    name: 'admin.login',
    component: Login,
    meta: { requireUnauth: true }
  },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requireAuth: true },
    children: [
      { path: '', redirect: '/admin/dashboard' },
      { path: 'dashboard',        name: 'admin.dashboard',        component: Dashboard },
      { path: 'customers',        name: 'admin.customers',        component: CustomersList },
      { path: 'customers/client', name: 'admin.customers.create', component: CustomerCreate },
      { path: 'customers/all-contacts', name: 'admin.customers.all_contacts', component: ContactsList },
      { path: 'customers/:id',    name: 'admin.customers.view',   component: CustomerView },
      { path: 'leads',            name: 'admin.leads',            component: LeadsBoard },
      { path: 'invoices',            name: 'admin.invoices',           component: InvoicesPage },
      { path: 'invoices/recurring',  name: 'admin.invoices.recurring', component: RecurringInvoicesPage },
      { path: 'invoices/create',     name: 'admin.invoices.create',    component: InvoiceForm },
      { path: 'invoices/:id',        name: 'admin.invoices.view',      component: InvoiceView  },
      { path: 'invoices/:id/edit',   name: 'admin.invoices.edit',      component: InvoiceForm  },
      { path: 'setup/:section?/:subsection?', name: 'admin.setup',            component: SetupPage },
      { path: 'staff',            name: 'admin.staff',            component: StaffMembersPage },

      // Profile
      {
        path: 'profile',
        component: ProfileLayout,
        children: [
          { path: '', name: 'admin.profile', component: ProfileOverview },
          { path: 'edit', name: 'admin.profile.edit', component: ProfileEdit },
        ]
      },

      // Timesheets
      { path: 'timesheets', name: 'admin.timesheets', component: TimesheetsPage },
      { path: 'my-todos', name: 'admin.my-todos', component: MyTodosPage },
      { path: 'notifications', name: 'admin.notifications', component: NotificationsPage },

      // Sales sub-items
      { path: 'estimates',        name: 'admin.estimates',        component: EstimatesPage },
      { path: 'estimates/estimate', name: 'admin.estimates.create', component: EstimateForm },
      { path: 'estimates/estimate/:id', name: 'admin.estimates.edit', component: EstimateForm },
      { path: 'proposals',        name: 'admin.proposals',        component: ProposalsPage },
      { path: 'proposals/proposal', name: 'admin.proposals.create', component: ProposalForm },
      { path: 'proposals/proposal/:id', name: 'admin.proposals.edit', component: ProposalForm },
      { path: 'payments',           name: 'admin.payments',        component: PaymentsPage },
      { path: 'payments/create',     name: 'admin.payments.create', component: PaymentForm  },
      { path: 'payments/:id',        name: 'admin.payments.view',   component: PaymentView  },
      { path: 'payments/:id/edit',   name: 'admin.payments.edit',   component: PaymentForm  },
      { path: 'credit-notes',     name: 'admin.credit-notes',     component: CreditNotesPage },
      { path: 'credit-notes/create', name: 'admin.credit-notes.create', component: CreditNoteForm },
      { path: 'credit-notes/:id/edit', name: 'admin.credit-notes.edit', component: CreditNoteForm },
      { path: 'items',            name: 'admin.items',            component: ItemsCatalogPage },

      // Main modules
      { path: 'subscriptions',    name: 'admin.subscriptions',    component: SubscriptionsPage },
      { path: 'expenses',         name: 'admin.expenses',         component: ExpensesPage },
      { path: 'contracts',        name: 'admin.contracts',        component: ContractsPage },
      { path: 'projects',         name: 'admin.projects',         component: ProjectsPage },
      { path: 'tasks',            name: 'admin.tasks',            component: TasksPage },
      { path: 'tasks/overview',   name: 'admin.tasks.overview',   component: TasksOverviewPage },
      { path: 'support',          name: 'admin.support',          component: TicketsPage },
      { path: 'estimate-request', name: 'admin.estimate-request', component: EstimateRequestsPage },
      { path: 'knowledge-base',         name: 'admin.knowledge-base',        component: KnowledgeBasePage },
      { path: 'knowledge-base/groups',  name: 'admin.knowledge-base.groups', component: KnowledgeBaseGroupsPage },
      { path: 'seo', name: 'admin.seo', component: SeoOptimisation },

      // Utilities sub-items
      { path: 'media',            name: 'admin.media',            component: MediaPage },
      { path: 'utilities/bulk-pdf-export',    name: 'admin.utilities.bulk-pdf-export',    component: BulkPdfExportPage },
      { path: 'utilities/e-invoice-export',   name: 'admin.utilities.e-invoice-export',   component: EInvoiceExportPage },
      { path: 'utilities/csv-export',         name: 'admin.utilities.csv-export',          component: CsvExportPage },
      { path: 'calendar',         name: 'admin.calendar',         component: CalendarPage },
      { path: 'announcements',    name: 'admin.announcements',    component: AnnouncementsPage },
      { path: 'goals',            name: 'admin.goals',            component: GoalsPage },
      { path: 'activity',         name: 'admin.activity',         component: ActivityLogPage },
      { path: 'utilities/surveys',            name: 'admin.utilities.surveys',            component: SurveysPage },
      { path: 'utilities/mail-lists',        name: 'admin.utilities.mail-lists',        component: MailListsPage },
      { path: 'utilities/database-backup',    name: 'admin.utilities.database-backup',    component: DatabaseBackupPage },
      { path: 'utilities/ticket-pipe-log',    name: 'admin.utilities.ticket-pipe-log',    component: TicketPipeLogPage },

      // Reports
      { path: 'reports',                name: 'admin.reports',             component: ReportsPage },
      { path: 'reports/sales',          name: 'admin.reports.sales',       component: ReportsPage },
      { path: 'reports/expenses',       name: 'admin.reports.expenses',    component: ReportsPage },
      { path: 'reports/timesheets',     name: 'admin.reports.timesheets',  component: ReportsPage },
      { path: 'reports/finance',        name: 'admin.reports.finance',     component: ReportsPage },
      { path: 'reports/leads',          name: 'admin.reports.leads',       component: ReportsPage },
      { path: 'reports/kb-articles',    name: 'admin.reports.kb-articles', component: ReportsPage },
      { path: 'reports/team',           name: 'admin.reports.team',        component: ReportsPage },


    ]
  },
  // Catch-all
  { path: '/:catchAll(.*)', redirect: '/admin/dashboard' }
];

const basePath = window.config.path.startsWith('http')
  ? new URL(window.config.path).pathname
  : window.config.path;

const router = createRouter({
  history: createWebHistory(basePath),
  routes,
  scrollBehavior: () => ({ left: 0, top: 0 })
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  if (to.meta.requireAuth && !authStore.isLoggedIn) {
    return next({ name: 'admin.login' });
  }
  if (to.meta.requireUnauth && authStore.isLoggedIn) {
    return next({ name: 'admin.dashboard' });
  }
  next();
});

export default router;
