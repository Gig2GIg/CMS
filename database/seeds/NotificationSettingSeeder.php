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
            'code' => 'autidion_update',
            'status' => 'on'
        ]);
        DB::table('notification_settings')->insert([
            'code' => 'autidion_add_contribuidor',
            'status' => 'on'
        ]);

        DB::table('notification_settings')->insert([
            'code' => 'upcoming_audition',
            'status' => 'on'
        ]);

        DB::table('notification_settings')->insert([
            'code' => 'representation_email',
            'status' => 'on'
        ]);

        DB::table('notification_settings')->insert([
            'code' => 'document_upload',
            'status' => 'on'
        ]);

        DB::table('notification_settings')->insert([
            'code' => 'check_in',
            'status' => 'on'
        ]);

        DB::table('notification_settings')->insert([
            'code' => 'autidion_request',
            'status' => 'on'
        ]);

        DB::table('notification_settings')->insert([
            'code' => 'custom',
            'status' => 'on'
        ]);

    }
}
