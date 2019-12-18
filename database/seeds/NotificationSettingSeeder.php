<?php

use Illuminate\Database\Seeder;

class NotificationSettingSeeder extends Seeder
{
    public function run()
    {
        DB::table('notification_settings')->insert([
            'code' => 'audition_update',
            'label'=> 'Audition Updates',
            'status' => 'on'
        ]);
//        DB::table('notification_settings')->insert([
//            'code' => 'autidion_add_contribuidor',
//            'status' => 'on'
//        ]);

        DB::table('notification_settings')->insert([
            'code' => 'upcoming_audition',
            'label'=>'Upcoming Auditions',
            'status' => 'on'
        ]);

        DB::table('notification_settings')->insert([
            'code' => 'representation_email',
            'label'=> 'Representation Email',
            'status' => 'on'
        ]);

        DB::table('notification_settings')->insert([
            'code' => 'document_upload',
            'label'=>'Document Upload',
            'status' => 'on'
        ]);

        DB::table('notification_settings')->insert([
            'code' => 'check_in',
            'label' =>'Audition Check In',
            'status' => 'on'
        ]);

        DB::table('notification_settings')->insert([
            'code' => 'audition_request',
            'label'=>'Audition Request',
            'status' => 'on'
        ]);

        DB::table('notification_settings')->insert([
            'code' => 'custom',
            'label'=>'Gig2Gig Updates',
            'status' => 'on'
        ]);

    }
}
