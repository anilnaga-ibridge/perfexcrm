<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HrPayrollController extends Controller
{
    protected function initCI()
    {
        if (!defined('BASEPATH')) { define('BASEPATH', true); }
        if (!defined('FCPATH')) { define('FCPATH', base_path('public/') . '/'); }

        if (!function_exists('get_instance')) {
            require_once base_path('app/Services/CICompatLayer.php');
        }

        $CI = get_instance();

        static $loaded = false;
        if (!$loaded) {
            $helpersDir = base_path('Modules/hr-payroll/helpers');
            if (is_dir($helpersDir)) {
                foreach (glob($helpersDir . '/*.php') as $f) {
                    try { include_once $f; } catch (\Throwable $e) {}
                }
            }

            $bootstrapFile = base_path('Modules/hr-payroll/hr_payroll.php');
            if (file_exists($bootstrapFile)) {
                try { include_once $bootstrapFile; } catch (\Throwable $e) {}
            }

            $loaded = true;
        }

        if (!isset($CI->hr_payroll_model)) {
            require_once base_path('Modules/hr-payroll/models/Hr_payroll_model.php');
            $CI->hr_payroll_model = new \Hr_payroll_model();
        }

        return $CI;
    }

    // =========================================================================
    // 1. SETTINGS
    // =========================================================================

    public function settings(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = [
                'payroll_columns' => $CI->hr_payroll_model->get_hrp_payroll_columns(),
                'earnings_list' => $CI->hr_payroll_model->get_earnings_list(),
                'deductions_list' => $CI->hr_payroll_model->get_salary_deductions_list(),
                'insurance_list' => $CI->hr_payroll_model->get_insurance_list(),
                'income_tax_rates' => $CI->hr_payroll_model->get_income_tax_rate(),
                'income_tax_rebates' => $CI->hr_payroll_model->get_income_tax_rebates(),
            ];
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Payroll Columns ──────────────────────────────────────────────────────

    public function getPayrollColumns(Request $request)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->get_hrp_payroll_columns();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storePayrollColumn(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->only(['code', 'description', 'function_name', 'taking_method', 'order_display']);
            $CI->hr_payroll_model->add_payroll_column($data);
            $id = $CI->db->insert_id();
            return response()->json(['success' => true, 'message' => 'Created', 'data' => ['id' => $id]], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updatePayrollColumn(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $data = $request->only(['code', 'description', 'function_name', 'taking_method', 'order_display']);
            $CI->hr_payroll_model->update_payroll_column($data, $id);
            return response()->json(['success' => true, 'message' => 'Updated']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deletePayrollColumn(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $CI->hr_payroll_model->delete_payroll_column($id);
            return response()->json(['success' => true, 'message' => 'Deleted']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Earnings List ────────────────────────────────────────────────────────

    public function getEarnings(Request $request)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->get_earnings_list();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeEarning(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            $CI->db->insert('hrp_earnings_list', $data);
            $id = $CI->db->insert_id();
            return response()->json(['success' => true, 'message' => 'Created', 'data' => ['id' => $id]], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateEarning(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            $CI->db->where('id', $id)->update('hrp_earnings_list', $data);
            return response()->json(['success' => true, 'message' => 'Updated']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteEarning(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $CI->db->where('id', $id)->delete('hrp_earnings_list');
            return response()->json(['success' => true, 'message' => 'Deleted']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Deductions List ──────────────────────────────────────────────────────

    public function getDeductionsList(Request $request)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->get_salary_deductions_list();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeDeductionList(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            $CI->db->insert('hrp_salary_deductions_list', $data);
            $id = $CI->db->insert_id();
            return response()->json(['success' => true, 'message' => 'Created', 'data' => ['id' => $id]], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateDeductionList(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            $CI->db->where('id', $id)->update('hrp_salary_deductions_list', $data);
            return response()->json(['success' => true, 'message' => 'Updated']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteDeductionList(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $CI->db->where('id', $id)->delete('hrp_salary_deductions_list');
            return response()->json(['success' => true, 'message' => 'Deleted']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Insurance List ───────────────────────────────────────────────────────

    public function getInsuranceList(Request $request)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->get_insurance_list();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeInsuranceListItem(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            $CI->db->insert('hrp_insurance_list', $data);
            $id = $CI->db->insert_id();
            return response()->json(['success' => true, 'message' => 'Created', 'data' => ['id' => $id]], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateInsuranceListItem(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            $CI->db->where('id', $id)->update('hrp_insurance_list', $data);
            return response()->json(['success' => true, 'message' => 'Updated']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteInsuranceListItem(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $CI->db->where('id', $id)->delete('hrp_insurance_list');
            return response()->json(['success' => true, 'message' => 'Deleted']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Income Tax Rates ─────────────────────────────────────────────────────

    public function getIncomeTaxRates(Request $request)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->get_income_tax_rate();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeIncomeTaxRate(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            $CI->db->insert('hrp_income_tax_rates', $data);
            $id = $CI->db->insert_id();
            return response()->json(['success' => true, 'message' => 'Created', 'data' => ['id' => $id]], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateIncomeTaxRate(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            $CI->db->where('id', $id)->update('hrp_income_tax_rates', $data);
            return response()->json(['success' => true, 'message' => 'Updated']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteIncomeTaxRate(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $CI->db->where('id', $id)->delete('hrp_income_tax_rates');
            return response()->json(['success' => true, 'message' => 'Deleted']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Income Tax Rebates ───────────────────────────────────────────────────

    public function getIncomeTaxRebates(Request $request)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->get_income_tax_rebates();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeIncomeTaxRebate(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            $CI->db->insert('hrp_income_tax_rebates', $data);
            $id = $CI->db->insert_id();
            return response()->json(['success' => true, 'message' => 'Created', 'data' => ['id' => $id]], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateIncomeTaxRebate(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            $CI->db->where('id', $id)->update('hrp_income_tax_rebates', $data);
            return response()->json(['success' => true, 'message' => 'Updated']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteIncomeTaxRebate(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $CI->db->where('id', $id)->delete('hrp_income_tax_rebates');
            return response()->json(['success' => true, 'message' => 'Deleted']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 2. EMPLOYEES
    // =========================================================================

    public function getEmployees(Request $request)
    {
        try {
            $CI = $this->initCI();
            $month = $request->query('month');
            if (!$month) {
                return response()->json(['success' => false, 'message' => 'month parameter is required'], 400);
            }
            $employees = $CI->hr_payroll_model->get_employees_data($month);
            $columns = $CI->hr_payroll_model->get_format_employees_data('');
            $staff = $CI->hr_payroll_model->get_staff_timekeeping_applicable_object();
            return response()->json([
                'success' => true,
                'data' => [
                    'employees' => $employees,
                    'columns' => $columns,
                    'staff' => $staff,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeEmployees(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->only(['month', 'hrp_employees_value', 'employees_fill_month']);
            $first_load = $request->boolean('first_load', false);
            if ($first_load) {
                $result = $CI->hr_payroll_model->employees_synchronization($data);
            } else {
                $result = $CI->hr_payroll_model->employees_update($data);
            }
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function filterEmployees(Request $request)
    {
        try {
            $CI = $this->initCI();
            $month = $request->input('month');
            $staff_ids = $request->input('staff_employees', []);
            $role_ids = $request->input('role_employees', []);
            $department_ids = $request->input('department_employees', []);
            $where = [];
            if (!empty($staff_ids)) $where['staff_id'] = $staff_ids;
            if (!empty($role_ids)) $where['role'] = $role_ids;
            if (!empty($department_ids)) $where['department'] = $department_ids;
            $result = $CI->hr_payroll_model->get_employees_data($month, '', $where);
            $columns = $CI->hr_payroll_model->get_format_employees_data('');
            return response()->json(['success' => true, 'data' => ['employees' => $result, 'columns' => $columns]]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function copyEmployees(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->only(['month']);
            $result = $CI->hr_payroll_model->employees_copy($data);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 3. ATTENDANCE
    // =========================================================================

    public function getAttendance(Request $request)
    {
        try {
            $CI = $this->initCI();
            $month = $request->query('month');
            if (!$month) {
                return response()->json(['success' => false, 'message' => 'month parameter is required'], 400);
            }
            $result = $CI->hr_payroll_model->get_hrp_attendance($month);
            $dayHeaders = $CI->hr_payroll_model->get_day_header_in_month($month);
            return response()->json(['success' => true, 'data' => $result, 'day_headers' => $dayHeaders]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeAttendance(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->only(['month', 'hrp_attendance_value']);
            $synchronize = $request->boolean('synchronize', false);
            if ($synchronize) {
                $result = $CI->hr_payroll_model->synchronization_attendance($data);
            } else {
                $result = $CI->hr_payroll_model->add_update_attendance($data);
            }
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function filterAttendance(Request $request)
    {
        try {
            $CI = $this->initCI();
            $month = $request->input('month');
            $where = $request->except(['_token', '_method', 'month']);
            $result = $CI->hr_payroll_model->get_hrp_attendance($month, $where);
            $dayHeaders = $CI->hr_payroll_model->get_day_header_in_month($month);
            return response()->json(['success' => true, 'data' => $result, 'day_headers' => $dayHeaders]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function calculateAttendance(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->only(['month']);
            $result = $CI->hr_payroll_model->attendance_calculation($data);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 4. DEDUCTIONS
    // =========================================================================

    public function getDeductions(Request $request)
    {
        try {
            $CI = $this->initCI();
            $month = $request->query('month');
            if (!$month) {
                return response()->json(['success' => false, 'message' => 'month parameter is required'], 400);
            }
            $deductions = $CI->hr_payroll_model->get_deductions_data($month);
            $deductions_list = $CI->hr_payroll_model->get_salary_deductions_list();
            return response()->json([
                'success' => true,
                'data' => $deductions,
                'deductions_list' => $deductions_list,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeDeductions(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->only(['month', 'salary_deduction_value']);
            $CI->hr_payroll_model->deductions_update($data);
            return response()->json(['success' => true, 'message' => 'Updated']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function filterDeductions(Request $request)
    {
        try {
            $CI = $this->initCI();
            $month = $request->input('month');
            $where = $request->except(['_token', '_method', 'month']);
            $result = $CI->hr_payroll_model->get_deductions_data($month, $where);
            $deductions_list = $CI->hr_payroll_model->get_salary_deductions_list();
            return response()->json([
                'success' => true,
                'data' => $result,
                'deductions_list' => $deductions_list,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 5. COMMISSIONS
    // =========================================================================

    public function getCommissions(Request $request)
    {
        try {
            $CI = $this->initCI();
            $month = $request->query('month');
            if (!$month) {
                return response()->json(['success' => false, 'message' => 'month parameter is required'], 400);
            }
            $result = $CI->hr_payroll_model->get_commissions_data($month);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeCommissions(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->only(['month', 'commission_value']);
            $CI->hr_payroll_model->commissions_update($data);
            return response()->json(['success' => true, 'message' => 'Updated']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function filterCommissions(Request $request)
    {
        try {
            $CI = $this->initCI();
            $month = $request->input('month');
            $where = $request->except(['_token', '_method', 'month']);
            $result = $CI->hr_payroll_model->get_commissions_data($month, $where);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 6. INSURANCE
    // =========================================================================

    public function getInsurances(Request $request)
    {
        try {
            $CI = $this->initCI();
            $month = $request->query('month');
            if (!$month) {
                return response()->json(['success' => false, 'message' => 'month parameter is required'], 400);
            }
            $insurances = $CI->hr_payroll_model->get_insurances_data($month);
            $insurance_list = $CI->hr_payroll_model->get_insurance_list();
            return response()->json([
                'success' => true,
                'data' => $insurances,
                'insurance_list' => $insurance_list,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeInsurances(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->only(['month', 'insurance_value']);
            $CI->hr_payroll_model->insurances_update($data);
            return response()->json(['success' => true, 'message' => 'Updated']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function filterInsurances(Request $request)
    {
        try {
            $CI = $this->initCI();
            $month = $request->input('month');
            $where = $request->except(['_token', '_method', 'month']);
            $result = $CI->hr_payroll_model->get_insurances_data($month, $where);
            $insurance_list = $CI->hr_payroll_model->get_insurance_list();
            return response()->json([
                'success' => true,
                'data' => $result,
                'insurance_list' => $insurance_list,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 7. BONUS / KPI
    // =========================================================================

    public function getBonuses(Request $request)
    {
        try {
            $CI = $this->initCI();
            $month = $request->query('month');
            if (!$month) {
                return response()->json(['success' => false, 'message' => 'month parameter is required'], 400);
            }
            $result = $CI->hr_payroll_model->get_bonus_kpi($month);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeBonuses(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->only(['month', 'bonus_kpi_value']);
            $result = $CI->hr_payroll_model->add_bonus_kpi($data);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function filterBonuses(Request $request)
    {
        try {
            $CI = $this->initCI();
            $month = $request->input('month');
            $where = $request->except(['_token', '_method', 'month']);
            $result = $CI->hr_payroll_model->get_bonus_kpi($month, $where);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 8. INCOME TAX
    // =========================================================================

    public function getIncomeTax(Request $request)
    {
        try {
            $CI = $this->initCI();
            $month = $request->query('month');
            if (!$month) {
                return response()->json(['success' => false, 'message' => 'month parameter is required'], 400);
            }
            $result = $CI->hr_payroll_model->get_income_tax_data($month);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 9. PAYSLIPS
    // =========================================================================

    public function getPayslips(Request $request)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->get_hrp_payslip();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getPayslip(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->get_hrp_payslip($id);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storePayslip(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->only([
                'payslip_month', 'payslip_template_id', 'payslip_name',
                'to_currency_id', 'pdf_template_id', 'payslip_range'
            ]);
            $result = $CI->hr_payroll_model->add_payslip($data);
            return response()->json(['success' => true, 'message' => 'Created', 'data' => $result], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updatePayslip(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $data = $request->only([
                'payslip_month', 'payslip_template_id', 'payslip_name',
                'to_currency_id', 'pdf_template_id', 'payslip_range'
            ]);
            $result = $CI->hr_payroll_model->update_payslip($data, $id);
            return response()->json(['success' => true, 'message' => 'Updated', 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deletePayslip(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->delete_payslip($id);
            return response()->json(['success' => true, 'message' => 'Deleted', 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function closePayslip(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $data = array_merge($request->all(), ['payslip_id' => $id]);
            $result = $CI->hr_payroll_model->payslip_close($data);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updatePayslipStatus(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $status = $request->input('status');
            $result = $CI->hr_payroll_model->update_payslip_status($id, $status);
            return response()->json(['success' => true, 'message' => 'Updated', 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getPayslipDetails(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->get_payslip_detail_by_payslip_id($id);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 10. PAYSLIP TEMPLATES
    // =========================================================================

    public function getPayslipTemplates(Request $request)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->get_hrp_payslip_templates();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getPayslipTemplate(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->get_hrp_payslip_templates($id);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storePayslipTemplate(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            $result = $CI->hr_payroll_model->add_payslip_template($data);
            return response()->json(['success' => true, 'message' => 'Created', 'data' => $result], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updatePayslipTemplate(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            $result = $CI->hr_payroll_model->update_payslip_template($data, $id);
            return response()->json(['success' => true, 'message' => 'Updated', 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deletePayslipTemplate(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->delete_payslip_template($id);
            return response()->json(['success' => true, 'message' => 'Deleted', 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 11. CURRENCY RATES
    // =========================================================================

    public function getCurrencyRates(Request $request)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->get_currency_rate();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeCurrencyRate(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            $CI->db->insert('currency_rates', $data);
            $id = $CI->db->insert_id();
            return response()->json(['success' => true, 'message' => 'Created', 'data' => ['id' => $id]], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateCurrencyRate(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            $CI->hr_payroll_model->update_currency_rate($data, $id);
            return response()->json(['success' => true, 'message' => 'Updated']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteCurrencyRate(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $CI->hr_payroll_model->delete_currency_rate($id);
            return response()->json(['success' => true, 'message' => 'Deleted']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getOnlineCurrencyRates(Request $request)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->get_all_currency_rate_online();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 12. REPORTS
    // =========================================================================

    public function getReports(Request $request)
    {
        try {
            $CI = $this->initCI();
            return response()->json(['success' => true, 'data' => [
                'available_reports' => [
                    'income_summary',
                    'insurance_summary',
                    'payslip_chart',
                    'department_chart',
                ]
            ]]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function incomeSummaryReport(Request $request)
    {
        try {
            $CI = $this->initCI();
            $sql_where = $request->input('sql_where', '');
            $result = $CI->hr_payroll_model->get_income_summary_report($sql_where);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function insuranceSummaryReport(Request $request)
    {
        try {
            $CI = $this->initCI();
            $sql_where = $request->input('sql_where', '');
            $result = $CI->hr_payroll_model->get_insurance_summary_report($sql_where);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function payslipChartReport(Request $request)
    {
        try {
            $CI = $this->initCI();
            $year = $request->input('year', date('Y'));
            $staff_id = $request->input('staff_id', '');
            $result = $CI->hr_payroll_model->payslip_chart($year, $staff_id);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function departmentChartReport(Request $request)
    {
        try {
            $CI = $this->initCI();
            $from = $request->input('from');
            $to = $request->input('to');
            $result = $CI->hr_payroll_model->get_department_payslip_chart($from, $to);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 13. TIMESHEET LEAVES
    // =========================================================================

    public function getTimesheetLeaves(Request $request)
    {
        try {
            $CI = $this->initCI();
            $month = $request->query('month');
            if (!$month) {
                return response()->json(['success' => false, 'message' => 'month parameter is required'], 400);
            }
            $result = $CI->hr_payroll_model->get_hrp_attendance_timesheet_leave($month);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeTimesheetLeaves(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            $result = $CI->hr_payroll_model->add_update_attendance_timesheets_leave($data);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function filterTimesheetLeaves(Request $request)
    {
        try {
            $CI = $this->initCI();
            $month = $request->input('month');
            $where = $request->except(['_token', '_method', 'month']);
            $result = $CI->hr_payroll_model->get_hrp_attendance_timesheet_leave($month, $where);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function calculateTimesheetLeaves(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            $result = $CI->hr_payroll_model->timesheet_leave_calculation($data);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 14. PERMISSIONS
    // =========================================================================

    public function getPermissions(Request $request)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->db->get('staff_permissions')->result_array();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storePermissions(Request $request)
    {
        try {
            $CI = $this->initCI();
            $staff_id = $request->input('staff_id');
            $permissions = $request->input('permissions', []);

            $CI->db->where('staff_id', $staff_id)->delete('staff_permissions');

            if (!empty($permissions)) {
                $rows = [];
                foreach ($permissions as $perm) {
                    $rows[] = [
                        'staff_id' => $staff_id,
                        'permission' => $perm,
                        'capability' => $request->input('capability', 'view'),
                    ];
                }
                $CI->db->insert_batch('staff_permissions', $rows);
            }

            return response()->json(['success' => true, 'message' => 'Updated']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deletePermission(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $CI->hr_payroll_model->delete_hr_payroll_permission($id);
            return response()->json(['success' => true, 'message' => 'Deleted']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 15. STAFF / DEPARTMENTS / ROLES (Reference Data)
    // =========================================================================

    public function getStaffList(Request $request)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->get_staff_timekeeping_applicable_object();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getStaffMember(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->getStaff($id);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getDepartments(Request $request)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->db->get('departments')->result_array();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getRoles(Request $request)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->db->get('roles')->result_array();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 16. DATA INTEGRATION
    // =========================================================================

    public function getDataIntegration(Request $request)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->db->get('hr_payroll_option')->result_array();
            $settings = [];
            foreach ($result as $row) {
                $settings[$row['option_name']] = $row['option_val'];
            }
            return response()->json(['success' => true, 'data' => $settings]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeDataIntegration(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            foreach ($data as $key => $value) {
                $exists = $CI->db->where('option_name', $key)->get('hr_payroll_option')->row();
                if ($exists) {
                    $CI->db->where('option_name', $key)->update('hr_payroll_option', ['option_val' => $value]);
                } else {
                    $CI->db->insert('hr_payroll_option', ['option_name' => $key, 'option_val' => $value]);
                }
            }
            return response()->json(['success' => true, 'message' => 'Updated']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 17. PDF TEMPLATES
    // =========================================================================

    public function getPdfTemplates(Request $request)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->get_pdf_payslip_template();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getPdfTemplate(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $result = $CI->hr_payroll_model->get_pdf_payslip_template($id);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storePdfTemplate(Request $request)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            $payslip_template_id = $request->input('payslip_template_id');
            $result = $CI->hr_payroll_model->add_pdf_payslip_template($data);
            return response()->json(['success' => true, 'message' => 'Created', 'data' => $result], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updatePdfTemplate(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $data = $request->except(['_token', '_method']);
            $CI->hr_payroll_model->update_pdf_payslip_template($data, $id);
            return response()->json(['success' => true, 'message' => 'Updated']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deletePdfTemplate(Request $request, $id)
    {
        try {
            $CI = $this->initCI();
            $CI->hr_payroll_model->delete_pdf_payslip_template($id);
            return response()->json(['success' => true, 'message' => 'Deleted']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
