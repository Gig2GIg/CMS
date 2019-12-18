<?php

use Illuminate\Database\Seeder;
Use Faker\Generator as Faker;

class MarkerplaceFeaturedListingSeeder extends Seeder
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

        $audition = factory(\App\Models\MarketplaceFeaturedListing::class, 10)->create();

    }
}
