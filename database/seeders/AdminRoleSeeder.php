<?php

namespace Database\Seeders;

use App\Models\AdminPermission;
use App\Models\AdminRole;
use Illuminate\Database\Seeder;

/** Creates all permissions and the 5 default admin roles from config/permissions.php. Safe to re-run. */
class AdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        $ids = [];
        foreach (config('permissions.permissions') as $group => $perms) {
            foreach ($perms as $slug => $name) {
                $ids[$slug] = AdminPermission::updateOrCreate(['slug' => $slug], ['name' => $name, 'group' => $group])->id;
            }
        }

        foreach (config('permissions.roles') as $slug => $role) {
            $model = AdminRole::updateOrCreate(['slug' => $slug], ['name' => $role['name']]);
            $model->permissions()->sync($role['permissions'] === '*' ? array_values($ids) : array_values(array_intersect_key($ids, array_flip($role['permissions']))));
        }
    }
}
