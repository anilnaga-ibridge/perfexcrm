# Legacy Module Migration Report

**Module:** HR Payroll
**Source:** hr-payroll
**Version:** 1.0.9
**Author:** GreenTech Solutions
**Migrated on:** 2026-07-09 12:21:54

## Compatibility Score

███████░░░░░░░░░░░░░░░░░░░░░░░ 24%

| Component | Score |
|-----------|-------|
| Permissions | ████████████ 100% |
| Menus | ████████████ 100% |
| Controllers | █░░░░░░░░░░░ 10% |
| Views | █░░░░░░░░░░░ 5% |
| Models | ░░░░░░░░░░░░ 0% |
| Helpers | ░░░░░░░░░░░░ 0% |
| Hooks / Events | ░░░░░░░░░░░░ 0% |
| CI APIs | ░░░░░░░░░░░░ 4% |

## Migration Summary

| Category | Total | Auto-migrated | Manual Required |
|----------|-------|---------------|-----------------|
| Permissions | 11 | 11 | 0 |
| Menu Items | 11 | 11 | 0 |
| Controllers | 2 | 0 | 2 |
| Views | 54 | 0 | 54 |
| Helpers | 1 | 0 | 1 |
| Models | 1 | 0 | 1 |
| Libraries | 0 | 0 | 0 |

**Total:** 80 items, **22** auto-migrated, **58** require manual migration.

## Estimated Migration Effort

**Developer time:** 30 – 49 hours

| Difficulty | Hours | Items |
|------------|-------|-------|
| 🟢 Easy     | 2h | Permissions, Menus |
| 🟡 Medium   | 5h | Hooks, Helpers, Models |
| 🔴 Hard     | 31h | Controllers, Views |

🟢 Easy — ██████████ 100%
🟡 Medium — ██████████ 100%
🔴 Hard — ██████████ 100%

## Blocking Issues

| Issue | Occurrences | Impact | Suggestion |
|-------|-------------|--------|------------|
| 🔴 $CI global | 15 | high | Inject dependencies via constructor |
| 🔴 get_instance() | 6 | high | Inject via Laravel service container |
| 🔴 hooks() helper | 12 | high | Use Laravel events or middleware |
| 🟡 module_dir_path() | 4 | medium | Use Laravel storage or resource paths |
| 🟡 module_dir_url() | 46 | medium | Use asset() or Storage facade |
| 🟡 register_activation_hook() | 1 | medium | Handled by activate/deactivate in ModuleManager |
| 🟡 register_language_files() | 1 | medium | Use Laravel lang files |
| 🟡 register_merge_fields() | 1 | medium | Requires reimplementation |
| 🔴 Legacy hooks() system | 9 | high | Replace with Laravel event system or remove if no longer needed |
| 🔴 CodeIgniter models (CI database abstraction) | 1 | high | Rewrite as Eloquent models |
| 🟡 Large number of PHP views (54) | 54 | medium | Port to Vue components — see skeleton/ directory |

## Generated Files

- `module.json` — Module manifest (✅ auto)
- `menu.json` — Sidebar menu definition (✅ auto)
- `permissions.json` — Permission definitions (✅ auto)
- `skeleton/app/Http/Controllers/Module/*.php` — Skeleton Laravel controllers (📝 skeleton)
- `skeleton/resources/js/views/module/*.vue` — Skeleton Vue pages (📝 skeleton)

## Extracted Metadata

```json
{
    "name": "HR Payroll",
    "description": "This module encompasses everything that goes into onboarding and paying your employees.",
    "version": "1.0.9",
    "author": "GreenTech Solutions",
    "author_uri": "https://codecanyon.net/user/greentech_solutions",
    "requires_at_least": "2.3.*",
    "module_name_constant": {
        "constant": "HR_PAYROLL_MODULE_NAME",
        "value": "hr_payroll"
    },
    "original_dir": "hr-payroll"
}
```

## Extracted Permissions

- `hrp_employee` (hr_payroll_employee) — ✅ Auto-migrated
- `hrp_attendance` (hr_payroll_attendance) — ✅ Auto-migrated
- `hrp_commission` (hr_payroll_commission) — ✅ Auto-migrated
- `hrp_deduction` (hr_payroll_deduction) — ✅ Auto-migrated
- `hrp_bonus_kpi` (hr_payroll_bonus_kpi) — ✅ Auto-migrated
- `hrp_insurrance` (hr_payroll_insurrance) — ✅ Auto-migrated
- `hrp_payslip` (hr_payroll_payslip) — ✅ Auto-migrated
- `hrp_payslip_template` (hr_payroll_payslip_template) — ✅ Auto-migrated
- `hrp_income_tax` (hr_payroll_income_tax) — ✅ Auto-migrated
- `hrp_report` (hr_payroll_report) — ✅ Auto-migrated
- `hrp_setting` (hr_payroll_setting) — ✅ Auto-migrated

## Extracted Menu Items

- **Root:** `hr_payroll` → hr_payroll
  - `hr_manage_employees` → hr_manage_employees (route: /manage_employees)
  - `hr_manage_attendance` → hr_manage_attendance (route: /manage_attendance)
  - `hr_manage_commissions` → hrp_commission_manage (route: /manage_commissions)
  - `hr_manage_deductions` → hrp_deduction_manage (route: /manage_deductions)
  - `hr_bonus_kpi` → hr_bonus_kpi (route: /manage_bonus)
  - `hrp_insurrance` → hrp_insurrance (route: /manage_insurances)
  - `hr_pay_slips` → hr_pay_slips (route: /payslip_manage)
  - `hrp_payslip_template` → hr_pay_slip_templates (route: /payslip_templates_manage)
  - `hrp_income_tax` → hrp_income_tax (route: /income_taxs_manage)
  - `hr_payroll_reports` → hrp_reports (route: /reports)
  - `hrp_settings` → settings (route: /setting?group=income_tax_rates)

## Controllers — Manual Migration Required

### Gtsverify (extends AdminController)

File: `Gtsverify.php`

- `index()`
- `activate()`

Skeleton generated at: `skeleton/app/Http/Controllers/Module/Gtsverify.php`

### hr_payroll (extends AdminController)

File: `Hr_payroll.php`

- `setting()`
- `setting_incometax_rates()`
- `setting_incometax_rebates()`
- `setting_earnings_list()`
- `setting_salary_deductions_list()`
- `setting_insurance_list()`
- `setting_company_contributions_list()`
- `data_integration()`
- `timesheet_integration_type_change()`
- `setting_earnings_list_hr_records()`
- `hr_payroll_permission_table()`
- `permission_modal()`
- `hr_payroll_update_permissions()`
- `staff_id_changed()`
- `delete_hr_payroll_permission()`
- `manage_employees()`
- `employees_filter()`
- `add_manage_employees()`
- `render_filter_query()`
- `manage_attendance()`
- `add_attendance()`
- `import_xlsx_employees()`
- `create_employees_sample_file()`
- `import_employees_excel()`
- `attendance_filter()`
- `import_xlsx_attendance()`
- `create_attendance_sample_file()`
- `import_attendance_excel()`
- `attendance_calculation()`
- `manage_deductions()`
- `add_manage_deductions()`
- `deductions_filter()`
- `manage_commissions()`
- `add_manage_commissions()`
- `commissions_filter()`
- `import_xlsx_commissions()`
- `create_commissions_sample_file()`
- `import_commissions_excel()`
- `income_taxs_manage()`
- `income_taxs_filter()`
- `manage_insurances()`
- `add_manage_insurances()`
- `insurances_filter()`
- `delete_error_file_day_before()`
- `payslip_manage()`
- `payslip_table()`
- `delete_payslip()`
- `payslip_templates_manage()`
- `payslip_template_table()`
- `get_payroll_column_method_html_add()`
- `get_payroll_column_function_name_html()`
- `payroll_column()`
- `get_payroll_column()`
- `delete_payroll_column_setting()`
- `get_payslip_template()`
- `payslip_template()`
- `delete_payslip_template()`
- `view_payslip_templates_detail()`
- `view_payslip_detail()`
- `view_payslip_detail_v2()`
- `manage_bonus()`
- `add_bonus_kpi()`
- `bonus_kpi_filter()`
- `payslip()`
- `payslip_closing()`
- `payslip_update_status()`
- `table_staff_payslip()`
- `view_staff_payslip_modal()`
- `reports()`
- `payslip_report()`
- `income_summary_report()`
- `insurance_cost_summary_report()`
- `payslip_chart()`
- `department_payslip_chart()`
- `payslip_template_checked()`
- `payslip_checked()`
- `create_payslip_file()`
- `employees_copy()`
- `reset_data()`
- `employee_export_pdf()`
- `payslip_manage_export_pdf()`
- `manage_attendance_timesheet_leaves()`
- `add_timesheets_leave()`
- `timesheets_leave_filter()`
- `timesheet_leave_calculation()`
- `payslip_pdf_template()`
- `delete_payslip_pdf_template_()`
- `save_pdf_payslip_data()`
- `get_pdf_payslip_template()`
- `edit_payslip()`
- `new_employee_export_pdf()`
- `currency_rate_table()`
- `update_setting_currency_rate()`
- `get_all_currency_rate_online()`
- `update_currency_rate()`
- `get_currency_rate_online()`
- `delete_currency_rate()`
- `currency_rate_modal()`
- `currency_rate_logs_table()`
- `get_currency_rate()`

Skeleton generated at: `skeleton/app/Http/Controllers/Module/hr_payroll.php`

## Views — Manual Migration Required

- `activate.php` (2221 bytes)
- `attendances/attendance_manage.php` (5458 bytes)
- `attendances/import_attendance.php` (4013 bytes)
- `bonus/bonus_kpi.php` (3319 bytes)
- `commissions/commissions_manage.php` (4991 bytes)
- `commissions/import_commissions.php` (4126 bytes)
- `deductions/deductions_manage.php` (4483 bytes)
- `deductions/import_deductions.php` (0 bytes)
- `employee_payslip/export_employee_payslip.php` (7060 bytes)
- `employee_payslip/export_employee_pdf.php` (275 bytes)
- `employee_payslip/new_export_employee_pdf.php` (313 bytes)
- `employee_payslip/staff_payslip_modal_view.php` (8577 bytes)
- `employee_payslip/staff_payslip_tab_content.php` (783 bytes)
- `employee_payslip/table_staff_payslip.php` (8049 bytes)
- `employees/employees_manage.php` (5030 bytes)
- `employees/import_employees.php` (4293 bytes)
- `includes/company_contributions_list.php` (1090 bytes)
- `includes/currencies/currency_rate_logs_table.php` (1330 bytes)
- `includes/currencies/currency_rate_modal.php` (2522 bytes)
- `includes/currencies/currency_rate_table.php` (1589 bytes)
- `includes/currencies/general.php` (2944 bytes)
- `includes/currencies/logs.php` (1321 bytes)
- `includes/currency_rates.php` (1223 bytes)
- `includes/data_integration.php` (10840 bytes)
- `includes/earnings_list.php` (1010 bytes)
- `includes/hr_records_earnings_list.php` (1242 bytes)
- `includes/income_tax_rates.php` (1025 bytes)
- `includes/income_tax_rebates.php` (1048 bytes)
- `includes/insurance_list.php` (1027 bytes)
- `includes/manage_setting.php` (5594 bytes)
- `includes/payroll_columns.php` (6044 bytes)
- `includes/pdf_payslip_template.php` (1688 bytes)
- `includes/pdf_payslip_template_detail.php` (5383 bytes)
- `includes/permission_modal.php` (6164 bytes)
- `includes/permissions.php` (837 bytes)
- `includes/reset_data.php` (836 bytes)
- `includes/salary_deductions_list.php` (1064 bytes)
- `income_tax/income_tax_manage.php` (4309 bytes)
- `insurances/insurances_manage.php` (4468 bytes)
- `payslip_templates/add_payslip_template.php` (879 bytes)
- `payslip_templates/payslip_template_manage.php` (6919 bytes)
- `payslip_templates/payslip_template_table.php` (2889 bytes)
- `payslips/payslip.php` (972 bytes)
- `payslips/payslip_manage.php` (7031 bytes)
- `payslips/payslip_table.php` (4552 bytes)
- `payslips/payslip_view_own.php` (981 bytes)
- `reports/department_payslip_chart.php` (192 bytes)
- `reports/income_summary_report.php` (1268 bytes)
- `reports/insurance_cost_summary_report.php` (475 bytes)
- `reports/manage_reports.php` (9155 bytes)
- `reports/payslip_chart.php` (169 bytes)
- `reports/payslip_report.php` (1210 bytes)
- `timesheet_leaves/import_timesheet_leave.php` (4013 bytes)
- `timesheet_leaves/timesheet_leave_manage.php` (5533 bytes)

## Vue Pages Generated (102)

- `GtsverifyIndex.vue` ← Gtsverify::index()
- `GtsverifyActivate.vue` ← Gtsverify::activate()
- `HrPayrollSetting.vue` ← hr_payroll::setting()
- `HrPayrollSettingIncometaxRates.vue` ← hr_payroll::setting_incometax_rates()
- `HrPayrollSettingIncometaxRebates.vue` ← hr_payroll::setting_incometax_rebates()
- `HrPayrollSettingEarningsList.vue` ← hr_payroll::setting_earnings_list()
- `HrPayrollSettingSalaryDeductionsList.vue` ← hr_payroll::setting_salary_deductions_list()
- `HrPayrollSettingInsuranceList.vue` ← hr_payroll::setting_insurance_list()
- `HrPayrollSettingCompanyContributionsList.vue` ← hr_payroll::setting_company_contributions_list()
- `HrPayrollDataIntegration.vue` ← hr_payroll::data_integration()
- `HrPayrollTimesheetIntegrationTypeChange.vue` ← hr_payroll::timesheet_integration_type_change()
- `HrPayrollSettingEarningsListHrRecords.vue` ← hr_payroll::setting_earnings_list_hr_records()
- `HrPayrollHrPayrollPermissionTable.vue` ← hr_payroll::hr_payroll_permission_table()
- `HrPayrollPermissionModal.vue` ← hr_payroll::permission_modal()
- `HrPayrollHrPayrollUpdatePermissions.vue` ← hr_payroll::hr_payroll_update_permissions()
- `HrPayrollStaffIdChanged.vue` ← hr_payroll::staff_id_changed()
- `HrPayrollDeleteHrPayrollPermission.vue` ← hr_payroll::delete_hr_payroll_permission()
- `HrPayrollManageEmployees.vue` ← hr_payroll::manage_employees()
- `HrPayrollEmployeesFilter.vue` ← hr_payroll::employees_filter()
- `HrPayrollAddManageEmployees.vue` ← hr_payroll::add_manage_employees()
- `HrPayrollRenderFilterQuery.vue` ← hr_payroll::render_filter_query()
- `HrPayrollManageAttendance.vue` ← hr_payroll::manage_attendance()
- `HrPayrollAddAttendance.vue` ← hr_payroll::add_attendance()
- `HrPayrollImportXlsxEmployees.vue` ← hr_payroll::import_xlsx_employees()
- `HrPayrollCreateEmployeesSampleFile.vue` ← hr_payroll::create_employees_sample_file()
- `HrPayrollImportEmployeesExcel.vue` ← hr_payroll::import_employees_excel()
- `HrPayrollAttendanceFilter.vue` ← hr_payroll::attendance_filter()
- `HrPayrollImportXlsxAttendance.vue` ← hr_payroll::import_xlsx_attendance()
- `HrPayrollCreateAttendanceSampleFile.vue` ← hr_payroll::create_attendance_sample_file()
- `HrPayrollImportAttendanceExcel.vue` ← hr_payroll::import_attendance_excel()
- `HrPayrollAttendanceCalculation.vue` ← hr_payroll::attendance_calculation()
- `HrPayrollManageDeductions.vue` ← hr_payroll::manage_deductions()
- `HrPayrollAddManageDeductions.vue` ← hr_payroll::add_manage_deductions()
- `HrPayrollDeductionsFilter.vue` ← hr_payroll::deductions_filter()
- `HrPayrollManageCommissions.vue` ← hr_payroll::manage_commissions()
- `HrPayrollAddManageCommissions.vue` ← hr_payroll::add_manage_commissions()
- `HrPayrollCommissionsFilter.vue` ← hr_payroll::commissions_filter()
- `HrPayrollImportXlsxCommissions.vue` ← hr_payroll::import_xlsx_commissions()
- `HrPayrollCreateCommissionsSampleFile.vue` ← hr_payroll::create_commissions_sample_file()
- `HrPayrollImportCommissionsExcel.vue` ← hr_payroll::import_commissions_excel()
- `HrPayrollIncomeTaxsManage.vue` ← hr_payroll::income_taxs_manage()
- `HrPayrollIncomeTaxsFilter.vue` ← hr_payroll::income_taxs_filter()
- `HrPayrollManageInsurances.vue` ← hr_payroll::manage_insurances()
- `HrPayrollAddManageInsurances.vue` ← hr_payroll::add_manage_insurances()
- `HrPayrollInsurancesFilter.vue` ← hr_payroll::insurances_filter()
- `HrPayrollDeleteErrorFileDayBefore.vue` ← hr_payroll::delete_error_file_day_before()
- `HrPayrollPayslipManage.vue` ← hr_payroll::payslip_manage()
- `HrPayrollPayslipTable.vue` ← hr_payroll::payslip_table()
- `HrPayrollDeletePayslip.vue` ← hr_payroll::delete_payslip()
- `HrPayrollPayslipTemplatesManage.vue` ← hr_payroll::payslip_templates_manage()
- `HrPayrollPayslipTemplateTable.vue` ← hr_payroll::payslip_template_table()
- `HrPayrollGetPayrollColumnMethodHtmlAdd.vue` ← hr_payroll::get_payroll_column_method_html_add()
- `HrPayrollGetPayrollColumnFunctionNameHtml.vue` ← hr_payroll::get_payroll_column_function_name_html()
- `HrPayrollPayrollColumn.vue` ← hr_payroll::payroll_column()
- `HrPayrollGetPayrollColumn.vue` ← hr_payroll::get_payroll_column()
- `HrPayrollDeletePayrollColumnSetting.vue` ← hr_payroll::delete_payroll_column_setting()
- `HrPayrollGetPayslipTemplate.vue` ← hr_payroll::get_payslip_template()
- `HrPayrollPayslipTemplate.vue` ← hr_payroll::payslip_template()
- `HrPayrollDeletePayslipTemplate.vue` ← hr_payroll::delete_payslip_template()
- `HrPayrollViewPayslipTemplatesDetail.vue` ← hr_payroll::view_payslip_templates_detail()
- `HrPayrollViewPayslipDetail.vue` ← hr_payroll::view_payslip_detail()
- `HrPayrollViewPayslipDetailV2.vue` ← hr_payroll::view_payslip_detail_v2()
- `HrPayrollManageBonus.vue` ← hr_payroll::manage_bonus()
- `HrPayrollAddBonusKpi.vue` ← hr_payroll::add_bonus_kpi()
- `HrPayrollBonusKpiFilter.vue` ← hr_payroll::bonus_kpi_filter()
- `HrPayrollPayslip.vue` ← hr_payroll::payslip()
- `HrPayrollPayslipClosing.vue` ← hr_payroll::payslip_closing()
- `HrPayrollPayslipUpdateStatus.vue` ← hr_payroll::payslip_update_status()
- `HrPayrollTableStaffPayslip.vue` ← hr_payroll::table_staff_payslip()
- `HrPayrollViewStaffPayslipModal.vue` ← hr_payroll::view_staff_payslip_modal()
- `HrPayrollReports.vue` ← hr_payroll::reports()
- `HrPayrollPayslipReport.vue` ← hr_payroll::payslip_report()
- `HrPayrollIncomeSummaryReport.vue` ← hr_payroll::income_summary_report()
- `HrPayrollInsuranceCostSummaryReport.vue` ← hr_payroll::insurance_cost_summary_report()
- `HrPayrollPayslipChart.vue` ← hr_payroll::payslip_chart()
- `HrPayrollDepartmentPayslipChart.vue` ← hr_payroll::department_payslip_chart()
- `HrPayrollPayslipTemplateChecked.vue` ← hr_payroll::payslip_template_checked()
- `HrPayrollPayslipChecked.vue` ← hr_payroll::payslip_checked()
- `HrPayrollCreatePayslipFile.vue` ← hr_payroll::create_payslip_file()
- `HrPayrollEmployeesCopy.vue` ← hr_payroll::employees_copy()
- `HrPayrollResetData.vue` ← hr_payroll::reset_data()
- `HrPayrollEmployeeExportPdf.vue` ← hr_payroll::employee_export_pdf()
- `HrPayrollPayslipManageExportPdf.vue` ← hr_payroll::payslip_manage_export_pdf()
- `HrPayrollManageAttendanceTimesheetLeaves.vue` ← hr_payroll::manage_attendance_timesheet_leaves()
- `HrPayrollAddTimesheetsLeave.vue` ← hr_payroll::add_timesheets_leave()
- `HrPayrollTimesheetsLeaveFilter.vue` ← hr_payroll::timesheets_leave_filter()
- `HrPayrollTimesheetLeaveCalculation.vue` ← hr_payroll::timesheet_leave_calculation()
- `HrPayrollPayslipPdfTemplate.vue` ← hr_payroll::payslip_pdf_template()
- `HrPayrollDeletePayslipPdfTemplate.vue` ← hr_payroll::delete_payslip_pdf_template_()
- `HrPayrollSavePdfPayslipData.vue` ← hr_payroll::save_pdf_payslip_data()
- `HrPayrollGetPdfPayslipTemplate.vue` ← hr_payroll::get_pdf_payslip_template()
- `HrPayrollEditPayslip.vue` ← hr_payroll::edit_payslip()
- `HrPayrollNewEmployeeExportPdf.vue` ← hr_payroll::new_employee_export_pdf()
- `HrPayrollCurrencyRateTable.vue` ← hr_payroll::currency_rate_table()
- `HrPayrollUpdateSettingCurrencyRate.vue` ← hr_payroll::update_setting_currency_rate()
- `HrPayrollGetAllCurrencyRateOnline.vue` ← hr_payroll::get_all_currency_rate_online()
- `HrPayrollUpdateCurrencyRate.vue` ← hr_payroll::update_currency_rate()
- `HrPayrollGetCurrencyRateOnline.vue` ← hr_payroll::get_currency_rate_online()
- `HrPayrollDeleteCurrencyRate.vue` ← hr_payroll::delete_currency_rate()
- `HrPayrollCurrencyRateModal.vue` ← hr_payroll::currency_rate_modal()
- `HrPayrollCurrencyRateLogsTable.vue` ← hr_payroll::currency_rate_logs_table()
- `HrPayrollGetCurrencyRate.vue` ← hr_payroll::get_currency_rate()

## Hooks / Events

| Hook | Callback | Type | Status |
|------|----------|------|--------|
| `admin_init` | `hr_payroll_permissions` | action | ⚠️ Manual |
| `app_admin_head` | `hr_payroll_add_head_components` | action | ⚠️ Manual |
| `app_admin_footer` | `hr_payroll_load_js` | action | ⚠️ Manual |
| `admin_init` | `hr_payroll_module_init_menu_items` | action | ⚠️ Manual |
| `hr_profile_load_js_file` | `hr_payroll_load_js_file` | action | ⚠️ Manual |
| `after_cron_run` | `hrp_cronjob_currency_rates` | action | ⚠️ Manual |
| `hr_profile_tab_name` | `hr_payroll_add_tab_name` | filter | ⚠️ Manual |
| `hr_profile_tab_content` | `hr_payroll_add_tab_content` | filter | ⚠️ Manual |
| `other_merge_fields_available_for` | `hrp_pdf_payslip_register_other_merge_fields` | filter | ⚠️ Manual |

## Unsupported Legacy APIs Detected

| API | Occurrences | Status |
|-----|-------------|--------|
| $CI global | 15 | ⚠️ manual |
| get_instance() | 6 | ⚠️ manual |
| hooks() helper | 12 | ⚠️ manual |
| _l() translation | 39 | ✅ auto-detectable |
| admin_url() | 11 | ✅ auto-detectable |
| module_dir_path() | 4 | ⚠️ manual |
| module_dir_url() | 46 | ⚠️ manual |
| has_permission() | 40 | ✅ auto-detectable |
| register_staff_capabilities() | 11 | ✅ auto-detectable |
| register_activation_hook() | 1 | ⚠️ manual |
| register_language_files() | 1 | ⚠️ manual |
| register_merge_fields() | 1 | ⚠️ manual |

## Next Steps

1. Review `module.json`, `menu.json`, and `permissions.json` and adjust as needed.
2. Implement each skeleton controller in `skeleton/app/Http/Controllers/Module/`.
3. Implement each skeleton Vue page in `skeleton/resources/js/views/module/`.
4. Create API routes in `routes/api.php` for each controller method.
5. Register Vue page components in the router.
6. Port views (PHP/CI) to Vue template syntax.
7. Replace `_l()` calls with `i18n` or inline text.
8. Replace `admin_url()` with Vue Router links.
9. Replace CodeIgniter database queries with Eloquent models.
