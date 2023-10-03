<?php

use Illuminate\Database\Seeder;

class DesignationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create the designations available
        $csvFile = file_get_contents(storage_path('files/designations.csv'));

        $designations = str_getcsv($csvFile, "\n");

        foreach ($designations as $designation) {
            try {
                \DB::table('designations')->insert([
                    'name' => ucwords($designation),
                    'slug' => \Illuminate\Support\Str::slug($designation),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (Exception $exception) {
                continue;
            }
        }
    }
}
