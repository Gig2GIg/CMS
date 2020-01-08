<?php

use Illuminate\Database\Seeder;

class UpdateTopicsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('topics')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('topics')->insert([
            [
                'title' => 'Health',
                'status' => 'on',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Wellness',
                'status' => 'on',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Encouragement',
                'status' => 'on',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Motivation',
                'status' => 'on',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Opportunities',
                'status' => 'on',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Inspiration',
                'status' => 'on',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
