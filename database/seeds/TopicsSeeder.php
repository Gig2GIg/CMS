<?php

use Illuminate\Database\Seeder;
Use Faker\Generator as Faker;

class TopicsSeeder extends Seeder
{
    protected $faker;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        $this->faker = $faker;

        $audition = factory(\App\Models\Topics::class, 10)->create();

    }
}
