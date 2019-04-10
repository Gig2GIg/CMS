<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

         $this->call(userSeeder::class);
         $this->call(AuditionSeeder::class);
        $this->call(NotificationSettingSeeder::class);
        $this->call(ContentSettingSeeder::class);
    }
}
