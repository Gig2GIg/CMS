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
        		'stripe_plan' => 'price_1Gt5eJIoqeSHJzJwdXvYuHOo',
	            'allowed_performers'=> 100,
	            'description' => 'Store Upto 100 Performers in the Talent Database',
	            'amount' => 75,
	            'type' => 'monthly',
	            'user_type' => 1,
	            'is_custom' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'Tier 2',
        		'stripe_plan' => 'price_1Gt5eJIoqeSHJzJwbBkiEF41',
	            'allowed_performers'=> 1000,
	            'description' => 'Store Upto 1000 Performers in the Talent Database',
	            'amount' => 125,
	            'type' => 'monthly',
	            'user_type' => 1,
	            'is_custom' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'Tier 3',
        		'stripe_plan' => 'price_1Gt5eKIoqeSHJzJwajgI5s0j',
	            'allowed_performers'=> 2000,
	            'description' => 'Store Upto 2000 Performers in the Talent Database',
	            'amount' => 250,
	            'type' => 'monthly',
	            'user_type' => 1,
	            'is_custom' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'Tier 4',
        		'stripe_plan' => 'price_1Gt5eKIoqeSHJzJwAwZxEi85',
	            'allowed_performers'=> 3500,
	            'description' => 'Store Upto 3500 Performers in the Talent Database',
	            'amount' => 350,
	            'type' => 'monthly',
	            'user_type' => 1,
	            'is_custom' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'Tier 5',
        		'stripe_plan' => 'price_1Gt5eKIoqeSHJzJwczT4TzPO',
	            'allowed_performers'=> 5000,
	            'description' => 'Store Upto 5000 Performers in the Talent Database',
	            'amount' => 725,
	            'type' => 'monthly',
	            'user_type' => 1,
	            'is_custom' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'Tier 6',
        		'stripe_plan' => 'price_1Gt5eKIoqeSHJzJw5X2HQscH',
	            'allowed_performers'=> 10000,
	            'description' => 'Store Upto 10000 Performers in the Talent Database',
	            'amount' => 950,
	            'type' => 'monthly',
	            'user_type' => 1,
	            'is_custom' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'FREE',
        		'stripe_plan' => NULL,
	            'allowed_performers'=> 0,
	            'description' => 'Scan into Auditions, receive audition updates, and store all your music, photos, video reets, sides audition packets and calendar',
	            'amount' => 0,
	            'type' => NULL,
	            'user_type' => 2,
	            'is_custom' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'Monthly',
        		'stripe_plan' => NULL,
	            'allowed_performers'=> 0,
	            'description' => 'Access all free features + Casting Feedback, Push Notification, and Marketplace',
	            'amount' => 12.99,
	            'type' => 'monthly',
	            'user_type' => 2,
	            'is_custom' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'Annual',
        		'stripe_plan' => NULL,
	            'allowed_performers'=> 0,
	            'description' => 'Access all free features + Casting Feedback, Push Notification, and Marketplace',
	            'amount' => 120,
	            'type' => 'annual',
	            'user_type' => 2,
	            'is_custom' => 0,
	            'created_at' => $now,
	            'updated_at' => $now
        	],
        	[
        		'name' => 'Custom',
        		'stripe_plan' => NULL,
	            'allowed_performers'=> 10000,
	            'description' => 'Custom plan',
	            'amount' => NULL,
	            'type' => 'monthly',
	            'user_type' => 1,
	            'is_custom' => 1,
	            'created_at' => $now,
	            'updated_at' => $now
        	]    
        ]);
    }
}
