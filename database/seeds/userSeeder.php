<?php

use App\Models\Credits;
use App\Models\Educations;
use App\Models\UserAparence;
use Illuminate\Database\Seeder;
Use Faker\Generator as Faker;
class userSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        $admin = factory(\App\Models\User::class)->create([
            'email'=>'admin@g2g.com',
            'password'=>bcrypt('123456'),
        ]);
        $admin->image()->create(['type'=>'cover','url'=>$faker->imageUrl(),'name'=>$faker->word()]);
        $adminDetail = factory(\App\Models\UserDetails::class)->create([
            'user_id'=>$admin->id,
            'type'=>1,
            'agency_name'=>$faker->company()
        ]);

        $admin2 = factory(\App\Models\User::class)->create([
            'email'=>'admin2@g2g.com',
            'password'=>bcrypt('123456'),
        ]);
        $admin2->image()->create(['type'=>'cover','url'=>$faker->imageUrl(),'name'=>$faker->word()]);
        $adminDetail2 = factory(\App\Models\UserDetails::class)->create([
            'user_id'=>$admin2->id,
            'type'=>1,
            'agency_name'=>$faker->company()
        ]);

        $user = factory(\App\Models\User::class)->create([
            'email'=>'user@g2g.com',
            'password'=>bcrypt('123456'),
        ]);
        $user->image()->create(['type'=>'cover','url'=>$faker->imageUrl(),'name'=>$faker->word()]);
        $userDetail = factory(\App\Models\UserDetails::class)->create([
            'user_id'=>$user->id,
            'type'=>2
        ]);

        $user2 = factory(\App\Models\User::class)->create([
            'email'=>'user2@g2g.com',
            'password'=>bcrypt('123456'),
        ]);
        $user2->image()->create(['type'=>'cover','url'=>$faker->imageUrl(),'name'=>$faker->word()]);
        $user2Detail = factory(\App\Models\UserDetails::class)->create([
            'user_id'=>$user2->id,
            'type'=>2
        ]);
        factory(Educations::class,3)->create(['user_id'=>$user2->id]);
        factory(Credits::class,4)->create(['user_id'=>$user2->id]);
        factory(UserAparence::class)->create(['user_id'=>$user2->id]);

        $cms = factory(\App\Models\User::class)->create([
            'email'=>'cms@g2g.com',
            'password'=>bcrypt('123456'),
        ]);
        $cms->image()->create(['type'=>'cover','url'=>$faker->imageUrl(),'name'=>$faker->word()]);
        $adminDetail2 = factory(\App\Models\UserDetails::class)->create([
            'user_id'=>$cms->id,
            'type'=>3,
            'agency_name'=>$faker->company()
        ]);

    }
}
