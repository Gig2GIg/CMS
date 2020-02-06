<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\LogManger;
use App\Http\Repositories\AppointmentRepository;
use App\Http\Repositories\AuditionRepository;
use App\Http\Repositories\InstantFeedbackRepository;
use App\Http\Repositories\PerformerRepository;
use App\Http\Repositories\UserAuditionsRepository;
use App\Http\Repositories\UserRepository;
use App\Http\Resources\AuditionResponseInstantFeedback;
use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\InstantFeedback;
use App\Models\InstantFeedbackSettings;
use App\Models\Performers;
use App\Models\User;
use App\Models\UserAuditions;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

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
                'comment' => $request->comment,
                'suggested_appointment_id' => $request->suggested_appointment_id,
            ];

            $repo = new InstantFeedbackRepository(new InstantFeedback());
            $dataCreated = $repo->create($data);
            if ($dataCreated->id) {

                $userRepo = new UserRepository(new User());
                $user = $userRepo->find($request->user);

                $appointmentRepo = new AppointmentRepository(new Appointments());
                $appoinmentData = $appointmentRepo->find($request->appointment_id);

                $auditionsRepo = new AuditionRepository(new Auditions());
                $audition = $auditionsRepo->find($appoinmentData->auditions_id);

                // if ($request->accepted == 0) {
                //     // remove that performer from group
                //     $repoUserAuditions = new UserAuditionsRepository(new UserAuditions());
                //     $dataUserAuditions = $repoUserAuditions->all()
                //         ->where('user_id', $request->user)
                //         ->where('appointment_id', $request->appointment_id);

                //     if ($dataUserAuditions->count() > 0) {
                //         $data = $repoUserAuditions->findbyparams(
                //             [
                //                 'user_id' => $request->user,
                //                 'appointment_id' => $request->appointment_id
                //             ]
                //         );
                //         $updateAuditionsData = $data->update(['group_no' => 0]);
                //     }
                // }

                if ($request->accepted == 0) {
                    // remove that performer from group
                    $repoUserAuditions = new UserAuditionsRepository(new UserAuditions());
                    $dataUserAuditions = $repoUserAuditions->all()
                        ->where('user_id', $request->user)
                        ->where('appointment_id', $request->appointment_id);

                    if ($dataUserAuditions->count() > 0) {
                        $updateAuditionsData = DB::table('user_auditions')
                            ->where('user_id', $request->user)
                            ->where('appointment_id', $request->appointment_id)
                            ->update([
                                'group_no' => 0,
                                'rejected' => 1,
                            ]);
                    }
                }  else {
                    // send notification
                    $this->sendStoreNotificationToUser($user, $audition);
                    $this->saveStoreNotificationToUser($user, $audition);
                }

                $this->addTalenteToDatabase($request->user);

                $dataResponse = ['data' => trans('messages.feedback_save_success'), 'feedback_id' => $dataCreated->id];
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
                ->where('title', 'like', "%{$request->value}%")
                ->where('status', 1)
                ->where('user_id', $this->getUserLogging())
                ->get();
            return response()->json(['data' => $data], 200);
        } else {
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }

    public function getDefaultInstantFeedback(Request $request)
    {
        if (!is_null($request->user_id)) {
            $instant_feedback_settings = new InstantFeedbackSettings();
            $data = $instant_feedback_settings->where('user_id', $request->user_id)->get()->first();

            if ($data == null) {
                $response = ['comment' => trans('messages.default_instant_feedback_message')];
            } else {
                $response = ['comment' => $data->comment];
            }
            return response()->json(['data' => $response], 200);
        } else {
            return response()->json(['data' => trans('messages.data_not_found')], 404);
        }
    }

    public function updatDefaultInstantFeedback(Request $request)
    {
        if (!is_null($request->feedback)) {
            $instant_feedback_settings = new InstantFeedbackSettings();

            $data = $instant_feedback_settings->where('user_id', $this->getUserLogging())->get()->toArray();
            if (empty($data)) {
                // add if no feedback added for caster
                $data = [
                    'comment' => $request->feedback,
                    'user_id' => $this->getUserLogging(),
                ];

                $feedbackUpdated = $instant_feedback_settings->insert($data);
            } else {
                // update if caster's feedbacke already exist
                $update_feedback = $instant_feedback_settings
                    ->where('user_id', $this->getUserLogging())
                    ->update(['comment' => $request->feedback]);
            }

            $response = $instant_feedback_settings->where('user_id', $this->getUserLogging())->get()->toArray();
            return response()->json(['data' => $response], 200);
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

            if ($feedbacks == null) {
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

            $data = new stdClass();
            $feedbacks = $repoFeedback->all()
                ->where('appointment_id', $request->id)
                ->where('user_id', '=', $request->user_id)
                ->first();

            if ($feedbacks == null) {
                throw new \Exception('Data not found');
            }

            if ($feedbacks == null) {
                throw new \Exception('Data not found');
            }
            $appointmentRepo = new AppointmentRepository(new Appointments());
            $auditionsRepo = new AuditionRepository(new Auditions());

            $appoinmentData = $appointmentRepo->find($request->id);
            $auditionData = $auditionsRepo->all()->where('id', $appoinmentData->auditions_id);
            $responseDataAudition = AuditionResponseInstantFeedback::collection($auditionData);

            $data->feedback = $feedbacks;
            $data->audition = $responseDataAudition;

            if ($feedbacks->suggested_appointment_id != null) {
//                $suggestedAppoinmentData = $appointmentRepo->find($feedbacks->suggested_appointment_id);
                $suggestedAuditionData = $auditionsRepo->all()->where('id', $feedbacks->suggested_appointment_id);
                $responseDataSuggestedAudition = AuditionResponseInstantFeedback::collection($suggestedAuditionData);
                $data->suggested_audition = $responseDataSuggestedAudition;
            }
            // $data->suggested_audition = isset($responseDataSuggestedAudition) ? $responseDataSuggestedAudition : array();

            $dataResponse = ['data' => $data];
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
                    'message' => 'You have received new instant feedback for ' . $audition->title,
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
            $this->pushNotifications('You have received new instant feedback for ' . $audition->title, $user, $audition->title);
        } catch (NotFoundException $exception) {
            $this->log->error($exception->getMessage());
        }
    }

    public function addTalenteToDatabase($performer_id)
    {
        try {
            $hasid = new Hashids('g2g');
            $dateHash = new \DateTime();
            $dataTime = $dateHash->getTimestamp();
            $repo = new PerformerRepository(new Performers());
            $dataRepo = $repo->findbyparam('director_id', $this->getUserLogging())->get();
            $count = $dataRepo->where('performer_id', $performer_id)->count();
            if ($count > 0) {
                throw new \Exception("User exists in your database");
            }
            $register = [
                'performer_id' => $performer_id,
                'director_id' => $this->getUserLogging(),
                'uuid' => $hasid->encode($performer_id, $dataTime),
            ];

            $repo->create($register);
            $this->log->info('Talent add');
        } catch (\Exception $exception) {
            $this->log->error($exception->getMessage());
        }
    }

}
