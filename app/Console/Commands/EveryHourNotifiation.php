<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserSubscription;
use Exception;

/**
 * TO Send Notifications to users on Every Minute
 *
 */

class EveryHourNotifiation extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:sendEveryHour';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check expired users every Hour';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->handleExpiredUsers();
    }

    private function handleExpiredUsers()
    {
        try {
            $userRepo = new User();
            $subscriptionRepo = new UserSubscription;
            $now = Carbon::now('UTC')->format('Y-m-d H:i:s');
            $subscription = $subscriptionRepo->whereDate('ends_at', '<', $now)->get();

            if($subscription && $subscription->count() != 0){
                $subscriptionRepo->whereIn('id', $subscription->pluck('id'))->update(array('stripe_status' => 'canceled'));
                $userRepo->whereIn('id', $subscription->pluck('user_id'))->update(array('is_premium' => 0));
            }                         
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
        }
    }
}
