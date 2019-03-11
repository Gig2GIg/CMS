<?php

namespace Tests\Unit;

use App\Http\Controllers\Utils\SendMail;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendMailTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_send_mail()
    {
        $mail = new SendMail();
        $password = $this->faker->word(12);
        $user = factory(User::class)->create();
        $this->assertTrue($mail->send($password, $user));
    }

    public function test_send_mail_fail(){
        $mail = new SendMail();
        $password = $this->faker->word(12);
        $this->assertFalse($mail->send($password, new User()));
    }
    public function test_send_mail_exception_error_type()
    {
        $this->expectException(\ErrorException::class);
        $mail = new SendMail();
        $mail->send('',[]);
    }

}
