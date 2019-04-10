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
                'auditions_id'=>$item->id
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
                    'url' => $this->faker->url(),
                    'name' => $this->faker->word(),
                ]);
            });

        });






    }
}
