<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ManageMonitorModeAuditionsTest extends TestCase
{
  public function test_create_update_monitor_audition(){

      $response = $this->json('POST','api/t/monitor/updates',[
         'audition'=>1,
         'title'=>'Checking open',
         'time'=>$this->faker->time()
      ]);
  }
}
