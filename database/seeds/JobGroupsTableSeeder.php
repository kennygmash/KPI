<?php

use Illuminate\Database\Seeder;

class JobGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    /*public function run()
    {
        for ($i = 1; $i <= 14; $i++) {
            $name = 'KSG ' . $i;

            \DB::table('job_groups')->insert([
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }*/

    public function run()
    {
        $csvFile = file_get_contents(storage_path('files/jobgroups.csv'));

        $jobgroups = str_getcsv($csvFile, "\n");

        foreach ($jobgroups as $jobgroup) {
            try {
                \DB::table('job_groups')->insert([
                    'name' => ucwords($jobgroup),
                    'slug' => \Illuminate\Support\Str::slug($jobgroup),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (Exception $exception) {
                continue;
            }
        }
    }
}
