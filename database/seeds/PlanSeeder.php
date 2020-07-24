<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$now = Carbon::now()->format('Y-m-d H:i:s');

    	DB::table('plans')->delete();
        
        DB::table('plans')->insert([
        	[
        		'name' => 'Tier 1',
        		'header' => 'Up to 100 Performers',
        		'stripe_plan' => 'price_1GxSOtIoqeSHJzJwL56bvEtK',
	            'allowed_performers'=> 100,
	            'description' => 'Store up to 100 Performer Profiles in the Talent Database.',
	            'amount' => 1,
	            'type' => 'monthly',
	            'user_type' => 1,
	            'is_custom' => 0,
	            'is_discounted' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'University',
        		'header' => 'University (Up to 500 Performers)',
        		'stripe_plan' => 'price_1H8JQSIoqeSHJzJwvMHv9vJO',
	            'allowed_performers'=> 500,
	            'description' => 'Store up to 500 Performer Profiles in the Talent Database.',
	            'amount' => 52.50,
	            'type' => 'monthly',
	            'user_type' => 1,
	            'is_custom' => 0,
	            'is_discounted' => 1,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'Tier 2',
        		'header' => 'Up to 500 Performers',
        		'stripe_plan' => 'price_1Gt5eJIoqeSHJzJwdXvYuHOo',
	            'allowed_performers'=> 500,
	            'description' => 'Store up to 500 Performer Profiles in the Talent Database.',
	            'amount' => 75,
	            'type' => 'monthly',
	            'user_type' => 1,
	            'is_custom' => 0,
	            'is_discounted' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'Tier 3',
        		'header' => 'Up to 1,000 Performers',
        		'stripe_plan' => 'price_1Gt5eJIoqeSHJzJwbBkiEF41',
	            'allowed_performers'=> 1000,
	            'description' => 'Store up to 1,000 Performer Profiles in the Talent Database.',
	            'amount' => 125,
	            'type' => 'monthly',
	            'user_type' => 1,
	            'is_custom' => 0,
	            'is_discounted' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'Tier 4',
        		'header' => 'Up to 2,000 Performers',
        		'stripe_plan' => 'price_1Gt5eKIoqeSHJzJwajgI5s0j',
	            'allowed_performers'=> 2000,
	            'description' => 'Store up to 2,000 Performer Profiles in the Talent Database.',
	            'amount' => 250,
	            'type' => 'monthly',
	            'user_type' => 1,
	            'is_custom' => 0,
	            'is_discounted' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'Tier 5',
        		'header' => 'Up to 3,500 Performers',
        		'stripe_plan' => 'price_1Gt5eKIoqeSHJzJwAwZxEi85',
	            'allowed_performers'=> 3500,
	            'description' => 'Store up to 3,500 Performer Profiles in the Talent Database.',
	            'amount' => 350,
	            'type' => 'monthly',
	            'user_type' => 1,
	            'is_custom' => 0,
	            'is_discounted' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'Tier 6',
        		'header' => 'Up to 5,000 Performers',
        		'stripe_plan' => 'price_1Gt5eKIoqeSHJzJwczT4TzPO',
	            'allowed_performers'=> 5000,
	            'description' => 'Store up to 5,000 Performer Profiles in the Talent Database.',
	            'amount' => 725,
	            'type' => 'monthly',
	            'user_type' => 1,
	            'is_custom' => 0,
	            'is_discounted' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'Tier 7',
        		'header' => 'Up to 10,000 Performers',
        		'stripe_plan' => 'price_1Gt5eKIoqeSHJzJw5X2HQscH',
	            'allowed_performers'=> 10000,
	            'description' => 'Store up to 10,000 Performer Profiles in the Talent Database.',
	            'amount' => 950,
	            'type' => 'monthly',
	            'user_type' => 1,
	            'is_custom' => 0,
	            'is_discounted' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'FREE',
        		'header' => NULL,
        		'stripe_plan' => NULL,
	            'allowed_performers'=> 0,
	            'description' => 'Scan into Auditions, receive audition updates, and store all your music, photos, video reets, sides audition packets and calendar',
	            'amount' => 0,
	            'type' => NULL,
	            'user_type' => 2,
	            'is_custom' => 0,
	            'is_discounted' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'Monthly',
        		'header' => NULL,
        		'stripe_plan' => NULL,
	            'allowed_performers'=> 0,
	            'description' => 'Access all free features + Casting Feedback, Push Notification, and Marketplace',
	            'amount' => 12.99,
	            'type' => 'monthly',
	            'user_type' => 2,
	            'is_custom' => 0,
	            'is_discounted' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'Annual',
        		'header' => NULL,
        		'stripe_plan' => NULL,
	            'allowed_performers'=> 0,
	            'description' => 'Access all free features + Casting Feedback, Push Notification, and Marketplace',
	            'amount' => 119.99,
	            'type' => 'annual',
	            'user_type' => 2,
	            'is_custom' => 0,
	            'is_discounted' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'Custom',
        		'header' => NULL,
        		'stripe_plan' => NULL,
	            'allowed_performers'=> 10000,
	            'description' => 'Store 10,000+ Performer Profiles in the Talent Database.',
	            'amount' => NULL,
	            'type' => 'monthly',
	            'user_type' => 1,
	            'is_custom' => 1,
	            'is_discounted' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	]    
        ]);
    }
}
