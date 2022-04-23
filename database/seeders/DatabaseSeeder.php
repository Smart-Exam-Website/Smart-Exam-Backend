<?php

namespace Database\Seeders;

use App\Models\FormulaQuestion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        Model::unguard();
        $this->call(RoleSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(PermissionRoleSeeder::class);
        $this->call(SchoolSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(DepartmentInstructorSeeder::class);
        $this->call(QuestionSeeder::class);
        $this->call(TagSeeder::class);
        $this->call(ExamSeeder::class);
        $this->call(QuestionTagSeeder::class);
        $this->call(CheatingActionSeeder::class);
    }
}
