<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */
$router->group(['prefix' => 'chat'], function () use ($router) {
    $router->post('/test_pusher', 'Api\ChatController@test_pusher');
});

$router->get('/email/verify', function ()  {
    return view('verification');
});
$router->post('/email/request-verification', ['as' => 'email.request.verification', 'uses' => 'Api\AuthController@emailRequestVerification']);

$router->post('/email/verify', ['as' => 'email.verify', 'uses' => 'Api\AuthController@emailVerify']);


$router->group(['prefix' => 'api/v1'], function () use ($router) {
    //auth
    $router->post('register', 'Api\AuthController@register');
    $router->post('registerPsikolog', 'Api\AuthController@registerPsikolog');
    $router->post('login', 'Api\AuthController@login');
    $router->post('logout', 'Api\AuthController@logout');
    $router->post('refresh', 'Api\AuthController@refresh');
    //attendance
    $router->get('attendance', 'Api\AttendanceController@index');
    $router->post('attendance', 'Api\AttendanceController@post');

    $router->post('resetAttendance', 'Api\AttendanceController@resetAttendance');

    //reimbursement
    $router->get('reimbursement', 'Api\ReimbursementController@index');
    $router->post('reimbursement', 'Api\ReimbursementController@post');
    
    $router->post('status', 'Api\UserController@status');
    //user
    $router->get('profile', 'Api\UserController@profile');
    $router->put('user/{id}', 'Api\UserController@update');
    $router->post('user/deviceToken', 'Api\UserController@setDeviceToken');
    $router->post('updatePhoto', 'Api\UserController@updatePhoto');
    $router->post('updatePassword', 'Api\UserController@updatePassword');
    //chat
    $router->get('getChatMessage', 'Api\ChatController@index');
    $router->get('getChatRoom', 'Api\ChatController@getChatRoom');
    $router->post('startSession', 'Api\ChatController@startSession');
    $router->post('acceptSession', 'Api\ChatController@acceptConsultation');
    $router->post('storeChat', 'Api\ChatController@store');
    $router->post('endChat', 'Api\ChatController@endChat');

    //meeting
    $router->get('meeting', 'Api\MeetingController@index');
    $router->post('meeting', 'Api\MeetingController@store');
    $router->post('meeting/{meeting_id}/join', 'Api\MeetingController@join');

    $router->get('psikolog', 'Api\PsikologController@index');
    $router->post('approvePsikolog', 'Api\PsikologController@approvePsikolog');
    $router->get('schedule', 'Api\ScheduleController@index');

    $router->get('article', 'Api\ArticleController@index');
    $router->get('categoryArticle', 'Api\ArticleController@getCategoryArticle');
    $router->post('likeOrDislike', 'Api\ArticleController@likeOrDislike');

    
    $router->get('constultation', 'Api\ConsultationController@index');
    $router->get('topic', 'Api\ConsultationController@getTopic');
    
    //comment
    $router->get('comment', 'Api\CommentController@index');
    $router->post('comment', 'Api\CommentController@store');
    $router->put('comment/{id}', 'Api\CommentController@update');
    $router->delete('comment/{id}', 'Api\CommentController@delete');
    $router->post('likeComment', 'Api\CommentController@addOrRemoveLike');

    $router->post('getSnapToken', 'Api\MidtransController@getSnapToken');
    $router->post('paymentRequest', 'Api\TransactionController@paymentRequest');
    $router->post('paymentRequestProduct', 'Api\TransactionController@paymentRequestProduct');

    $router->post('paymentNotification', 'Api\TransactionController@paymentNotification');
    $router->get('getTransactionProduct', 'Api\TransactionController@getTransactionProduct');
    $router->get('transactionProduct', 'Api\TransactionController@transactionProduct');
    $router->get('transactionPsikolog', 'Api\TransactionController@transactionPsikolog');

    $router->get('product', 'Api\ProductController@index');
    $router->get('categoryProduct', 'Api\ProductController@categoryProduct');
    $router->get('searchProduct', 'Api\ProductController@search');
    
    $router->get('mood', 'Api\MoodController@index');
    $router->post('mood', 'Api\MoodController@postMood');

    $router->get('feedback', 'Api\FeedbackController@index');
    $router->post('feedback', 'Api\FeedbackController@postFeedback');

    $router->get('diagnose', 'Api\DiagnoseController@index');
    $router->post('diagnose', 'Api\DiagnoseController@postDiagnose');
});
