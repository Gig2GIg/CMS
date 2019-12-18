<?php

use App\Models\Credits;
use App\Models\Educations;
use App\Models\Performers;
use App\Models\User;
use App\Models\UserAparence;
use App\Models\UserDetails;
use Faker\Generator as Faker;
use Illuminate\Database\Seeder;

class TalentDataBaseSeeder extends Seeder
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
        $users = factory(User::class,15)->create();
        $director = factory(User::class)->create([
            'email'=>'directortalen@gmail.com'
        ]);
        factory(UserDetails::class)->create([
            'user_id'=>$director->id,
            'type'=>1
        ]);
        $users->each(function ($item) use ($director){
            $item->image()->create(['type'=>'cover','url'=>$this->faker->imageUrl(),'name'=>$this->faker->word()]);
            factory(\App\Models\UserDetails::class)->create([
                'user_id'=>$item->id,
                'type'=>2
            ]);
            factory(Educations::class,3)->create(['user_id'=>$item->id]);
            factory(Credits::class,4)->create(['user_id'=>$item->id]);
            factory(UserAparence::class)->create(['user_id'=>$item->id]);
            factory(Performers::class)->create([
                'performer_id' => $item->id,
                'director_id' => $director->id,
                'uuid' => $this->faker->uuid,
            ]);

        });

    }
}
