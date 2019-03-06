<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserUnitTest extends TestCase
{
  /**
   * @test
  */
  public function test_create_user_admin(){
      $data = [
        'email' => $this->faker->email,
        'password'=>$this->faker->word,
        'type' =>$this->faker->numberBetween($min=1, $max=3),
      ];
  }
}
