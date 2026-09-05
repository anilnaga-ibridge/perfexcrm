import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../store/authStore';
import { h, defineComponent } from 'vue';

// Layouts & Views
import AdminLayout from '../layouts/AdminLayout.vue';
import Login      from '../views/auth/Login.vue';
import Register   from '../views/auth/Register.vue';
import ForgotPassword from '../views/auth/ForgotPassword.vue';
import ResetPassword from '../views/auth/ResetPassword.vue';
import Dashboard  from '../views/Dashboard.vue';
import SetupPage  from '../views/Setup.vue';
import ModuleView from '../views/ModuleView.vue';
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
import WeeklyTicketsAnalyticsPage from '../views/support/WeeklyAnalytics.vue';

// Vuexy Data Tables
import VuexyDataTable from '../views/tables/VuexyDataTable.vue';
import SeoOptimisation from '../views/SeoOptimisation.vue';

// Staff Members
import StaffMembersPage from '../views/StaffMembers.vue';
import StaffDetailPage from '../views/StaffDetail.vue';

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
import FeatureUpdatesPage from '../views/FeatureUpdates.vue';

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
    path: '/admin/register',
    name: 'admin.register',
    component: Register,
    meta: { requireUnauth: true }
  },
  {
    path: '/admin/forgot-password',
    name: 'admin.forgot-password',
    component: ForgotPassword,
    meta: { requireUnauth: true }
  },
  {
    path: '/admin/reset-password/:token',
    name: 'admin.reset-password',
    component: ResetPassword,
    meta: { requireUnauth: true }
  },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requireAuth: true },
    children: [
      { path: '', redirect: '/admin/dashboard' },
      { path: 'dashboard',        name: 'admin.dashboard',        component: Dashboard },
      { path: 'customers',        name: 'admin.customers',        component: CustomersList, meta: { permission: 'Customers' } },
      { path: 'customers/client', name: 'admin.customers.create', component: CustomerCreate, meta: { permission: 'Customers' } },
      { path: 'customers/all-contacts', name: 'admin.customers.all_contacts', component: ContactsList, meta: { permission: 'Customers' } },
      { path: 'customers/:id',    name: 'admin.customers.view',   component: CustomerView, meta: { permission: 'Customers' } },
      { path: 'leads',            name: 'admin.leads',            component: LeadsBoard, meta: { permission: 'Leads' } },
      { path: 'invoices',            name: 'admin.invoices',           component: InvoicesPage, meta: { permission: 'Invoices' } },
      { path: 'invoices/recurring',  name: 'admin.invoices.recurring', component: RecurringInvoicesPage, meta: { permission: 'Invoices' } },
      { path: 'invoices/create',     name: 'admin.invoices.create',    component: InvoiceForm, meta: { permission: 'Invoices' } },
      { path: 'invoices/:id',        name: 'admin.invoices.view',      component: InvoiceView, meta: { permission: 'Invoices' }  },
      { path: 'invoices/:id/edit',   name: 'admin.invoices.edit',      component: InvoiceForm, meta: { permission: 'Invoices' }  },
      { path: 'setup/:section?/:subsection?', name: 'admin.setup',            component: SetupPage, meta: { permission: 'Settings' } },
      { path: 'settings/email', redirect: '/admin/setup/email-templates' },
      { path: 'settings/email-templates', redirect: '/admin/setup/email-templates' },
      { path: 'modules',          name: 'admin.modules',          component: SetupPage, beforeEnter: (to, from, next) => { to.params.section = 'plugins'; next(); }, meta: { permission: 'Settings' } },
      { path: 'staff',            name: 'admin.staff',            component: StaffMembersPage, meta: { permission: 'Staff' } },
      { path: 'staff/create',     name: 'admin.staff.create',     component: StaffDetailPage, meta: { permission: 'Staff' } },
      { path: 'staff/member',     name: 'admin.staff.member.create', component: StaffDetailPage, meta: { permission: 'Staff' } },
      { path: 'staff/:id',        name: 'admin.staff.view',       component: StaffDetailPage, meta: { permission: 'Staff' } },
      { path: 'staff/member/:id', name: 'admin.staff.member.view',component: StaffDetailPage, meta: { permission: 'Staff' } },
      { path: 'setup/staff/create', name: 'admin.setup.staff.create', component: StaffDetailPage, meta: { permission: 'Staff' } },
      { path: 'setup/staff/member', name: 'admin.setup.staff.member.create', component: StaffDetailPage, meta: { permission: 'Staff' } },
      { path: 'setup/staff/:id',  name: 'admin.setup.staff.view', component: StaffDetailPage, meta: { permission: 'Staff' } },
      { path: 'setup/staff/member/:id', name: 'admin.setup.staff.member.view', component: StaffDetailPage, meta: { permission: 'Staff' } },

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
      { path: 'timesheets', name: 'admin.timesheets', component: TimesheetsPage, meta: { permission: 'Projects' } },
      { path: 'my-todos', name: 'admin.my-todos', component: MyTodosPage },
      { path: 'notifications', name: 'admin.notifications', component: NotificationsPage },
      { path: 'feature-updates', name: 'admin.feature-updates', component: FeatureUpdatesPage },

      // Sales sub-items
      { path: 'estimates',        name: 'admin.estimates',        component: EstimatesPage, meta: { permission: 'Estimates' } },
      { path: 'estimates/estimate', name: 'admin.estimates.create', component: EstimateForm, meta: { permission: 'Estimates' } },
      { path: 'estimates/estimate/:id', name: 'admin.estimates.edit', component: EstimateForm, meta: { permission: 'Estimates' } },
      { path: 'proposals',        name: 'admin.proposals',        component: ProposalsPage, meta: { permission: 'Proposals' } },
      { path: 'proposals/proposal', name: 'admin.proposals.create', component: ProposalForm, meta: { permission: 'Proposals' } },
      { path: 'proposals/proposal/:id', name: 'admin.proposals.edit', component: ProposalForm, meta: { permission: 'Proposals' } },
      { path: 'payments',           name: 'admin.payments',        component: PaymentsPage, meta: { permission: 'Payments' } },
      { path: 'payments/create',     name: 'admin.payments.create', component: PaymentForm, meta: { permission: 'Payments' }  },
      { path: 'payments/:id',        name: 'admin.payments.view',   component: PaymentView, meta: { permission: 'Payments' }  },
      { path: 'payments/:id/edit',   name: 'admin.payments.edit',   component: PaymentForm, meta: { permission: 'Payments' }  },
      { path: 'credit-notes',     name: 'admin.credit-notes',     component: CreditNotesPage, meta: { permission: 'Credit Notes' } },
      { path: 'credit-notes/create', name: 'admin.credit-notes.create', component: CreditNoteForm, meta: { permission: 'Credit Notes' } },
      { path: 'credit-notes/:id/edit', name: 'admin.credit-notes.edit', component: CreditNoteForm, meta: { permission: 'Credit Notes' } },
      { path: 'items',            name: 'admin.items',            component: ItemsCatalogPage, meta: { permission: 'Items' } },

      // Main modules
      { path: 'subscriptions',    name: 'admin.subscriptions',    component: SubscriptionsPage, meta: { permission: 'Subscriptions' } },
      { path: 'expenses',         name: 'admin.expenses',         component: ExpensesPage, meta: { permission: 'Expenses' } },
      { path: 'contracts',        name: 'admin.contracts',        component: ContractsPage, meta: { permission: 'Contracts' } },
      { path: 'projects',         name: 'admin.projects',         component: ProjectsPage, meta: { permission: 'Projects' } },
      { path: 'tasks',            name: 'admin.tasks',            component: TasksPage, meta: { permission: 'Tasks' } },
      { path: 'tasks/overview',   name: 'admin.tasks.overview',   component: TasksOverviewPage, meta: { permission: 'Tasks' } },
      { path: 'support',          name: 'admin.support',          component: TicketsPage, meta: { permission: 'Support' } },
      { path: 'support/weekly-analytics', name: 'admin.support.weekly_analytics', component: WeeklyTicketsAnalyticsPage, meta: { permission: 'Support' } },
      { path: 'estimate-request', name: 'admin.estimate-request', component: EstimateRequestsPage, meta: { permission: 'Estimate Request' } },
      { path: 'knowledge-base',         name: 'admin.knowledge-base',        component: KnowledgeBasePage, meta: { permission: 'Knowledge Base' } },
      { path: 'knowledge-base/groups',  name: 'admin.knowledge-base.groups', component: KnowledgeBaseGroupsPage, meta: { permission: 'Knowledge Base' } },
      { path: 'seo', name: 'admin.seo', component: SeoOptimisation, meta: { permission: 'Settings' } },

      // Vuexy Data Tables
      { path: 'tables', name: 'admin.tables', component: VuexyDataTable },
      { path: 'tables/data-tables', name: 'admin.tables.data_tables', component: VuexyDataTable },

      // Utilities sub-items
      { path: 'media',            name: 'admin.media',            component: MediaPage, meta: { permission: 'Media' } },
      { path: 'utilities/bulk-pdf-export',    name: 'admin.utilities.bulk-pdf-export',    component: BulkPdfExportPage, meta: { permission: 'Bulk PDF Export' } },
      { path: 'utilities/e-invoice-export',   name: 'admin.utilities.e-invoice-export',   component: EInvoiceExportPage, meta: { permission: 'e-Invoice' } },
      { path: 'utilities/csv-export',         name: 'admin.utilities.csv-export',          component: CsvExportPage, meta: { permission: 'CSV Export' } },
      { path: 'utilities/exports',            name: 'admin.utilities.exports',             component: BulkPdfExportPage, meta: { permission: 'Bulk PDF Export' } },
      { path: 'bulk-export',                  name: 'admin.bulk-export',                   component: BulkPdfExportPage, meta: { permission: 'Bulk PDF Export' } },
      { path: 'calendar',         name: 'admin.calendar',         component: CalendarPage, meta: { permission: 'Calendar' } },
      { path: 'announcements',    name: 'admin.announcements',    component: AnnouncementsPage, meta: { permission: 'Announcements' } },
      { path: 'goals',            name: 'admin.goals',            component: GoalsPage, meta: { permission: 'Goals' } },
      { path: 'activity',         name: 'admin.activity',         component: ActivityLogPage, meta: { permission: 'Activity Log' } },
      { path: 'utilities/surveys',            name: 'admin.utilities.surveys',            component: SurveysPage, meta: { permission: 'Surveys' } },
      { path: 'utilities/mail-lists',        name: 'admin.utilities.mail-lists',        component: MailListsPage, meta: { permission: 'Mail Lists' } },
      { path: 'utilities/database-backup',    name: 'admin.utilities.database-backup',    component: DatabaseBackupPage, meta: { permission: 'Settings' } },
      { path: 'utilities/ticket-pipe-log',    name: 'admin.utilities.ticket-pipe-log',    component: TicketPipeLogPage, meta: { permission: 'Support' } },

      // Reports
      { path: 'reports',                name: 'admin.reports',             component: ReportsPage, meta: { permission: 'Reports' } },
      { path: 'reports/sales',          name: 'admin.reports.sales',       component: ReportsPage, meta: { permission: 'Reports' } },
      { path: 'reports/expenses',       name: 'admin.reports.expenses',    component: ReportsPage, meta: { permission: 'Reports' } },
      { path: 'reports/timesheets',     name: 'admin.reports.timesheets',  component: ReportsPage, meta: { permission: 'Reports' } },
      { path: 'reports/finance',        name: 'admin.reports.finance',     component: ReportsPage, meta: { permission: 'Reports' } },
      { path: 'reports/leads',          name: 'admin.reports.leads',       component: ReportsPage, meta: { permission: 'Reports' } },
      { path: 'reports/kb-articles',    name: 'admin.reports.kb-articles', component: ReportsPage, meta: { permission: 'Knowledge Base' } },
      { path: 'reports/team',           name: 'admin.reports.team',        component: ReportsPage, meta: { permission: 'Reports' } },

      // Dynamic module pages (captures full path for native Vue rendering)
      { path: 'module/:slug/:pathMatch(.*)*', name: 'admin.module.dynamic', component: ModuleView },
    ]
  },
  // Catch-all
  { path: '/:catchAll(.*)', redirect: '/admin/dashboard' }
];

const rawConfigPath = window.config?.path || '/';
const basePath = rawConfigPath.startsWith('http')
  ? new URL(rawConfigPath).pathname
  : rawConfigPath;

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

  if (to.meta.permission && authStore.isLoggedIn) {
    const hasViewPerm = authStore.hasPermission(to.meta.permission, 'view') || authStore.hasPermission(to.meta.permission, 'view_global') || authStore.hasPermission(to.meta.permission, 'view_own');
    if (!hasViewPerm) {
      return next({ name: 'admin.dashboard' });
    }
  }

  next();
});

router.afterEach((to) => {
  let pageTitleStr = (to.meta && to.meta.title) ? to.meta.title : '';
  if (!pageTitleStr) {
    let rawName = to.name ? String(to.name).replace('admin.', '').replace(/\./g, ' ') : 'Dashboard';
    pageTitleStr = rawName.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
  }
  
  let appName = 'iBRIDGE CRM';
  const savedSettings = localStorage.getItem('crm_theme_style_settings');
  if (savedSettings) {
    try {
      const parsed = JSON.parse(savedSettings);
      if (parsed.app_page_title) appName = parsed.app_page_title;
    } catch(e) {}
  }
  document.title = `${pageTitleStr} - ${appName}`;
});

export default router;
