<?php

use Illuminate\Database\Seeder;

class NotificationSettingSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('notification_settings')->insert([
            'code' => 'Wendding',
         
        ]);

    }
}
