<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Str as StrAlias;

class CampusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Here we get all the campuses
        $campuses = [
            'Headquarter / Corporate', 'Nairobi', 'Embu', 'Matuga', 'Mombasa', 'Baringo', 'ELDI'
        ];

        // Loop through the campuses while inserting them to the DB
        foreach ($campuses as $campus) {
            DB::table('campuses')->insert([
                'name' => ucwords($campus),
                'slug' => StrAlias::slug($campus),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
