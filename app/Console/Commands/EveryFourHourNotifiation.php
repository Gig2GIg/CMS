<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Http\Controllers\Utils\SendMail;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Performers;
use App\Models\Plan;
use App\Traits\StipeTraits;
use Laravel\Cashier\Cashier;
use App\Http\Controllers\Utils\LogManger;

use Exception;

/**
 * TO Send Notifications to users on Every Minute
 *
 */

class EveryFourHourNotifiation extends Command
{

    use StipeTraits;
    protected $log;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:sendEveryFourHour';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check exceeded performer count of users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new LogManger();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->upgradePlan();
    }

    private function upgradePlan()
    {
        try {
            $subscriptionRepo = new UserSubscription;
            $performersRepo = new Performers();

            $subscriptions = $subscriptionRepo->with('plan')->where('stripe_status', '!=', 'canceled')->where('purchase_platform' , 'web')->where('grace_period', 0)->get();

            $subscriptions->each(function ($subscription) use ($performersRepo) {
                $userRepo = new User();
                $planRepo = new Plan();
                
                //it is to fetch logged in user's invited users data if any
                $invitedUserIds = $userRepo->where('invited_by', $subscription->user_id)->get()->pluck('id');

                //pushing own ID into WHERE IN constraint
                $invitedUserIds->push($subscription->user_id); 

                $performerCount = $performersRepo->whereIn('director_id', $invitedUserIds->unique()->values())->get()->count();

                if($subscription['plan']['allowed_performers'] && ($subscription['plan']['allowed_performers'] < $performerCount)){
                    
                    //Get next plan that suits the caster for their respective performer count in the talent DB
                    $upgradedPlan = $planRepo->where('allowed_performers', '>', $performerCount)->where('user_type', 1)->where('is_custom', 0)->where('is_discounted', 0)->where('is_active', 1)->get()->sortBy('allowed_performers')->first();

                    if($upgradedPlan){
                        $userData = $userRepo->find($subscription->user_id);

                        $currentSubscription = $userData->subscriptions()->first();
                        //swapping next upgraded plan for user
                        $currentSubscription->noProrate()->swap($upgradedPlan->stripe_plan);

                        $this->log->info("User subscription upgraded by CRON: From " . $currentSubscription->stripe_plan . " To ". $upgradedPlan->stripe_plan . " @" . Carbon::now('UTC')->format('Y-m-d H:i:s'));

                        //Update new subscription data to user subscription table
                        $subscription->plan_id = $upgradedPlan->id;
                        $subscription->name = $upgradedPlan->name;

                        $subscription->save();

                        //Getting next billing invoice
                        $upcomingInvoice = $this->getUpcomingInvoice($userData);

                        //Sending mail to user about Upgradation
                        $mail = new SendMail(); 
                        $emailData = array();
                        $emailData['name'] = $userData->details ? $userData->details->first_name . ' ' . $userData->details->last_name : '';
                        $emailData['old_sub'] = $planRepo->find($currentSubscription->plan_id)->allowed_performers;
                        $emailData['new_sub'] = $upgradedPlan->description;
                        $emailData['new_amount'] = ($upcomingInvoice->amount_due / 100);
                        $emailData['next_billing_date'] = Carbon::createFromTimeStamp($upcomingInvoice->next_payment_attempt)->format('Y-m-d H:i:s');
                        
                        $mail->sendUpgradeMail($userData->email, $emailData);
                    }
                }
            });                            
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
        }
    }
}
