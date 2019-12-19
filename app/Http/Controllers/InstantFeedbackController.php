<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Models\InstantFeedback;
use App\Http\Repositories\InstantFeedbackRepository;
use Illuminate\Http\Request;
use App\Models\Auditions;
use App\Models\Appointments;
use App\Http\Repositories\UserRepository;
use App\Models\User;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\AuditionRepository;

class InstantFeedbackController extends Controller
{
    protected $log;

    public function __construct()
    {
        $this->middleware('jwt', ['except' => []]);
        $this->log = new LogManger();
    }

    public function store(Request $request)
    {
        try {
            $data = [
                'appointment_id' => $request->appointment_id,
                'user_id' => $request->user,
                'evaluator_id' => $request->evaluator,
                'comment' => $request->comment
            ];

            $repo = new InstantFeedbackRepository(new InstantFeedback());
            $data = $repo->create($data);
            if ($data->id) {

                // send notification
                $userRepo = new UserRepository(new User());
                $user = $userRepo->find($request->user);

                $appointmentRepo = new AppointmentRepository(new Appointments());
                $appoinmentData = $appointmentRepo->find($request->appointment_id);

                $auditionsRepo = new AuditionRepository(new Auditions());
                $audition = $auditionsRepo->find($appoinmentData->auditions_id);

                $this->sendStoreNotificationToUser($user, $audition);
                $this->saveStoreNotificationToUser($user, $audition);

                $dataResponse = ['data' => trans('messages.feedback_save_success'), 'feedback_id' => $data->id];
                $code = 201;
            } else {
                $dataResponse = ['data' => 'Feedback already submitted'];
                $code = 406;
            }
            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            return response()->json(['data' => trans('messages.feedback_not_add')], 406);
            // return response()->json(['data' => 'Feedback not add'], 406);
        }
    }


    public function search_with_upcoming_audition(Request $request)
    {
        if (!is_null($request->value)) {
            $auditionRepo = new Auditions();
            $data = $auditionRepo
                // ->select('id')
                ->where('title', 'like', "%{$request->value}%")
                ->where('status', 1)
                ->get();
            return response()->json(['data' => $data], 200);
        } else {
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }



    public function instantFeedbackDetailsCaster(Request $request)
    {
        try {
            $repoFeedback = new InstantFeedbackRepository(new InstantFeedback());

            $feedbacks = $repoFeedback->all()->where('appointment_id', $request->id)
                ->where('evaluator_id', '=', $this->getUserLogging())->where('user_id', '=', $request->user_id)->first();

            if ($feedbacks == NULL) {
                throw new \Exception('Data not found');
            }

            $dataResponse = ['data' => $feedbacks];
            $code = 200;

            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            // return response()->json(['data' => 'Data Not Found'], 404);
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }


    public function instantFeedbackDetailsPerformer(Request $request)
    {
        try {
            $repoFeedback = new InstantFeedbackRepository(new InstantFeedback());

            $feedbacks = $repoFeedback->all()->where('appointment_id', $request->id)
                ->where('user_id', '=', $request->user_id)->first();

            if ($feedbacks == NULL) {
                throw new \Exception('Data not found');
            }

            $dataResponse = ['data' => $feedbacks];
            $code = 200;

            return response()->json($dataResponse, $code);
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
            // return response()->json(['data' => 'Data Not Found'], 404);
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }




    public function saveStoreNotificationToUser($user, $audition): void
    {
        try {
            if ($user instanceof User) {
                $history = $user->notification_history()->create([
                    'title' => $audition->title,
                    'code' => 'instant_feedback',
                    'status' => 'unread',
                    'message' => 'You have received new instant feedback for ' . $audition->title
                ]);
                $this->log->info('saveStoreNotificationToUser:: ', $history);
            }
        } catch (NotFoundException $exception) {
            $this->log->error($exception->getMessage());
        }
    }

    public function sendStoreNotificationToUser($user, $audition): void
    {
        try {
            $this->pushNotifications('You have received new instant feedback for ' . $audition->title, $user);
        } catch (NotFoundException $exception) {
            $this->log->error($exception->getMessage());
        }
    }
}
