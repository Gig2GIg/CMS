<?php

use Illuminate\Database\Seeder;
Use Faker\Generator as Faker;

class AuditionSeeder extends Seeder
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

        $audition = factory(\App\Models\Auditions::class, 10)->create(
            ['user_id' => \App\Models\UserDetails::all()->where('type', '=', '1')->random()->user_id]
        );

        $audition->each(function ($item) {
            $item->media()->create(['url' => App::make('url')->to('/').'/images/coveraudition.jpg', 'type' => 4, 'name' => $this->faker->word()]);
            factory(\App\Models\Dates::class)->create([
                'date_type' => 'App\Models\\'.class_basename($item),
                'date_id' => $item->id,
                'type'=>1
            ]);
            factory(\App\Models\Dates::class)->create([
                'date_type' => 'App\Models\\' . class_basename($item),
                'date_id' => $item->id,
                'type'=>2
            ]);

            $apointment = factory(\App\Models\Appointments::class)->create([
                'auditions_id'=>$item->id,
                'status'=>true,
                'round'=>1,
            ]);

            $apointment->each(function ($item){
               factory(\App\Models\Slots::class,$item->slots)->create([
                   'appointment_id'=>$item->id
               ]);
            });
            $roles = factory(\App\Models\Roles::class, random_int(3,8))->create([
                'auditions_id' => $item->id
            ]);
            $roles->each(function ($itemrol) {
                $itemrol->image()->create([
                    'type' => 4,
                    'url' => App::make('url')->to('/').'/images/roles.png',
                    'name' => $this->faker->word(),
                ]);
            });

        });






    }
}
