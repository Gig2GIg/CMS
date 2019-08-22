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
        $category2 = factory(\App\Models\MarketplaceCategory::class)->create();
        $category1 = factory(\App\Models\MarketplaceCategory::class)->create();

        factory(\App\Models\Marketplace::class, 10)->create(['marketplace_category_id' =>  $category2->id ]);

        factory(\App\Models\Marketplace::class, 10)->create(['marketplace_category_id' =>  $category1->id ]);
    }
}
