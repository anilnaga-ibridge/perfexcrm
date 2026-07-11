<?php

use App\Models\Module;
use App\Models\ModuleMenu;
use App\Models\ModulePermission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $coreAliases = [
            'database-backup',
            'einvoice',
            'csv-export',
            'goals',
            'menu-setup',
            'openai',
            'surveys',
            'theme-style',
        ];

        foreach ($coreAliases as $alias) {
            $module = Module::where('alias', $alias)->first();
            if ($module) {
                ModuleMenu::where('module_id', $module->id)->delete();
                ModulePermission::where('module_id', $module->id)->delete();
                $module->delete();
            }
        }
    }

    public function down(): void
    {
        // Re-insertion is handled by ModuleSeeder
    }
};
