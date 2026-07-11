<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleMenu;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate first to prevent duplicates
        \DB::table('module_menus')->delete();
        \DB::table('module_permissions')->delete();
        \DB::table('modules')->delete();

        $modules = [];

        foreach ($modules as $mod) {
            $menu = $mod['menu'] ?? null;
            unset($mod['menu']);

            $module = Module::create($mod);

            if ($menu) {
                ModuleMenu::create([
                    'module_id' => $module->id,
                    'title' => $menu['title'],
                    'route' => $menu['route'],
                    'icon' => $menu['icon'] ?? null,
                ]);
            }
        }

        $this->command->info('Seeded ' . count($modules) . ' default modules.');
    }
}
