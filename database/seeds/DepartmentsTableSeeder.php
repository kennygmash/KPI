<?php

use Illuminate\Database\Seeder;

class DepartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Departments available
        $csvFile = file_get_contents(storage_path('files/departments.csv'));

        $departments = str_getcsv($csvFile, "\n");

        // Loop through the departments while inserting them to the DB
        foreach ($departments as $department) {
            try {
                DB::table('departments')->insert([
                    'name' => ucwords($department),
                    'slug' => \Illuminate\Support\Str::slug($department),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (Exception $exception) {
                continue;
            }
        }
    }
}
