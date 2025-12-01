<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create User Groups
        $groups = [
            ['group_level' => 1, 'slug' => 'admin', 'group_status' => 'A'],
            ['group_level' => 2, 'slug' => 'doctor', 'group_status' => 'A'],
            ['group_level' => 3, 'slug' => 'secretary', 'group_status' => 'A'],
            ['group_level' => 4, 'slug' => 'patient', 'group_status' => 'A'],
        ];
        foreach ($groups as $group) {
            \App\Models\UserGroup::firstOrCreate(['group_level' => $group['group_level']], $group);
        }

        // 2. Create Specializations
        \App\Models\Specialization::factory(10)->create();

        // 3. Create Users
        // Admin
        \App\Models\User::factory()->create([
            'names' => 'Admin',
            'surnames' => 'System',
            'email' => 'admin@recisa.com',
            'user_level' => 1,
        ]);

        // Doctors
        $doctors = \App\Models\User::factory(5)->create([
            'user_level' => 2,
        ]);

        // Secretaries
        \App\Models\User::factory(2)->create([
            'user_level' => 3,
        ]);

        // 4. Assign Specializations to Doctors (UserSpecialization)
        $specializations = \App\Models\Specialization::all();
        foreach ($doctors as $doctor) {
            // Assign 1 to 3 random specializations
            $randomSpecs = $specializations->random(rand(1, 3));
            foreach ($randomSpecs as $spec) {
                \App\Models\UserSpecialization::create([
                    'id_user' => $doctor->id,
                    'id_specialization' => $spec->id,
                    'cupo_doctor' => rand(10, 20),
                ]);
            }
        }

        // 5. Create Patients
        \App\Models\Patient::factory(50)->create();

        // 6. Create Appointments
        // We need existing UserSpecializations (quotas) and Patients
        \App\Models\Appointment::factory(100)->create();

        // 7. Create Clinical Histories
        \App\Models\ClinicalHistories::factory(20)->create();
    }
}
