<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\PermissionsRole;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissionRole = PermissionsRole::all()->count();
        $perms = Permission::all();
        if (!$permissionRole) {
            foreach ($perms as $perm) {
                DB::table('permissions_roles')->insert([
                    [
                        'role_id' => '1',
                        'permission_id' => $perm->id,
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                    ],
                ]);
            }

            foreach ($perms as $perm) {
                if ($perm->type == 'view' || $perm->type == 'edit' || $perm->type == 'read') {
                    DB::table('permissions_roles')->insert([
                        [
                            'role_id' => '2',
                            'permission_id' => $perm->id,
                            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                        ],
                    ]);
                }
            }

            foreach ($perms as $perm) {
                if ($perm->type == 'view') {
                    DB::table('permissions_roles')->insert([
                        [
                            'role_id' => '3',
                            'permission_id' => $perm->id,
                            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                        ],
                    ]);
                }
            }
        }
    }
}
