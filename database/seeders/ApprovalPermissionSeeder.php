<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ApprovalPermissionSeeder extends Seeder
{
    public function run()
    {
        $approvalPermissions = [
            'approvals.view',
            'approvals.approve',
            'approvals.reject',
            'approvals.request'
        ];

        foreach($approvalPermissions as $permission) {
            if (!Permission::where('name', $permission)->exists()) {
                Permission::create([
                    'name' => $permission,
                    'guard_name' => 'web'
                ]);
            }
        }
    }
}
