<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
class SkillSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        factory(\App\Models\Skills::class,15)->create([
            'name'=>$faker->word()
        ]);
    }
}
