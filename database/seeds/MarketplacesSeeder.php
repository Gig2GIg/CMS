<?php

use Illuminate\Database\Seeder;

class MarketplacesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\MarketplaceCategory::class)->create([
            'name'=>'Non category',
            'description'=>'category to place requested from app'
        ]);

    }
}
