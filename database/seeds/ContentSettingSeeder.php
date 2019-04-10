<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
class ContentSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {

        DB::table('content_settings')->insert([
            'term_of_use' => $faker->randomHtml(2,3),
            'privacy_policy' => $faker->randomHtml(2,3),
            'app_info' => $faker->randomHtml(2,3),
            'contact_us' => $faker->randomHtml(2,3)
        ]);
    }
}
