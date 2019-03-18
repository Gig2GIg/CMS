<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditionControllerTest extends TestCase
{

    use RefreshDatabase;
    use WithFaker;

    public function test_create_audition_201()
    {
        $data = [];

        $response = $this->json('POST',
            'api/auditions/create?token=' . $this->token,
            $data);
        $response->assertStatus(201);

    }

}
