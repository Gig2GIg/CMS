<?php

use App\Http\Controllers\NotificationManagementController;
use App\Http\Controllers\UserController;

return [

    /*
    |--------------------------------------------------------------------------
    | API Response Message Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are the default lines which match reasons
    | that are given by API Responses.
    |
    */
    // General
    'data_not_found' => 'Data Not Found',
    'error' => 'ERROR',
    'success' => 'Success',
    'not_processable' => 'Not processable',
    'unprocesable_entity' => 'Unprocesable Entity',
    'data_not_update' => 'Data Not Update',
    'unauthorized' => 'Unauthorized',

    // Feedback Controller
    'feedback_not_add' => 'Feedback not add',
    'feedback_not_update' => 'Feedback not update',

    // Aparences Controller
    'error_not_processable' => 'Error not Processable',

    // Appointments Auditions Controller
    'appointment_not_assigned' => 'Appointment not assigned',

    // Appointment Controller
    'round_closed_successfully' => 'Round closed successfully',
    // 'round_close_successful' => 'Round closed successfully',
    'round_not_close' => 'Round not close',
    'list_by_slots' => 'List by slots',
    'round_create' => 'Round Create',
    'round_not_create' => 'Round not Create',

    // Audition Management Controller
    'audition_not_update' => 'Audition not update',
    'error_to_open_audition' => 'error to open audition',
    'error_to_close_audition' => 'error to close audition',
    'audition_banned' => 'Audition Banned',
    'you_already_registered' => 'You already registered',
    'audition_saved' => 'Audition Saved',
    'group_not_creaed' => 'Group not Created',
    'group_creaed' => 'Group Created',
    'group_already_open' => 'Group is already Open',
    'group_open' => 'Group is Open',
    'group_close' => 'Group is Close',
    'group_close_success' => 'Group Closed successfully',
    'group_not_closed' => 'Group not Closed',
    
    'video_saved' => 'Video saved',
    'video_not_saved' => 'Video not saved',
    
    // Auditions Controller
    'contruibuitors_add' => 'Contruibuitors Add',

    // Auth Controller
    'successfully_logged_out' => 'Successfully logged out',

    // Calender Controller
    'event_deleted' => 'Event deleted',
    'cant_use_past_dates' => "Can't use past dates",
    'end_date_must_be_greater_than_start_date' => "End date must be greater than start date",
    'date_range_is_occupied' => "Date range is occupied",
    'error_process_event' => 'Error process event',

    // Final Cast Controller 
    'fail_to_add_performer' => 'fail to add performer',
    'add_performer_to_final_cast' => 'Add performer to final cast',

    // Market Place Controller
    'error_created_marketplace' => 'Error created Marketplace',

    // MarketplaceFeaturedListingController
    'error_created_marketplace_featured_listing' => 'Error created Marketplace Featured Listing',

    // Monitor manager controller
    'update_not_publised' => 'Update Not Publised',

    // Notification Controller
    'record_not_created' => 'record_not_created',

    // OnlineMediaAuditionController
    'media_created' => 'Media created',
    'media_not_created' => 'Media not created',
    'media_not_found' => 'Media not found',

    // PerformersController
    'error_add_performer' => 'Error add performer',
    'code_share' => 'Code share',
    'error_send_code' => 'Error Send Code',
    'tag_by_user' => 'Tag by User',
    'comment_by_user' => 'Comment by User',
    'contracts_by_user' => 'contracts by user',


    // UserController
    'user_deleted' => 'User Deleted',
    'email_not_found' => 'Email not Found',
    'unions_update' => 'Unions Update',


    // Users Settying controller
    'setting_updated' => 'Setting Updated',
    'setting_not_updated' => 'Setting Not Updated',
    












    // Market Place Categories Controller
    // Managers Controller
    // NotificationManagementController
    // Audition videos Controller
    // Comments Controller 
    // Content Setting Controller
    // Controller
    // Credits Controller
    // Educations Controller
];
