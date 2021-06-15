<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostSidelineController;
use App\Http\Controllers\ReviewController;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {


    Route::prefix('zone')->group(function () {
        Route::get('/', [ApiController::class, 'zone']);
        Route::get('/hot', [ApiController::class, 'zoneHot']);
    });

    Route::get('search', [ApiController::class, 'search']);


    // Profile Function
    Route::get('profile/{name}', [ApiController::class, 'profile']);


    // Login, Register Function
    Route::get('login', function () {
        abort(403);
    })->name('login');
    Route::post('login', [ApiController::class, 'login']);
    Route::post('register', [ApiController::class, 'register']);
    Route::get('checkuser', [ApiController::class, 'checkUser']);

    // Sideline Function
    Route::get('/member/sideline', [PostSidelineController::class, 'index']);
    Route::get('/member/sideline/{id}', [PostSidelineController::class, 'show']);

    // Review Function
    Route::get('/member/review', [ReviewController::class, 'index']);
    Route::get('/member/review/{id}', [ReviewController::class, 'show']);
});






Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::prefix('v1')->group(function () {

        // Get User
        Route::get('/user/me', [UserController::class, 'user']);

        // User Function
        Route::put('change/name', [UserController::class, 'changeName']);
        Route::put('change/image', [UserController::class, 'changeImage']);

        // Comment (Review)
        Route::post('/member/review/', [ReviewController::class, 'store']);
        Route::put('/member/review/{id}', [ReviewController::class, 'update']);
        Route::delete('/member/review/{id}', [ReviewController::class, 'destroy']);
        // Like Review
        Route::post('/member/like/review/', [ReviewController::class, 'likeReview']);


        // Profile
        Route::get('/member/mypost', [UserController::class, 'myPost']);

        // Sideline Function
        Route::post('/member/sideline', [PostSidelineController::class, 'store']);
        Route::put('/member/sideline/{id}', [PostSidelineController::class, 'update']);
        Route::delete('/member/sideline/{id}', [PostSidelineController::class, 'destroy']);
        Route::post('/member/sideline/time/enable', [PostSidelineController::class, 'stopPost']);
        Route::get('/member/all/sideline/', [PostSidelineController::class, 'postSidelineUser']);
        Route::put('member/update/post/enable', [PostSidelineController::class, 'updateEnablePost']);

        // Favorite Function
        Route::get('/member/favorite/sideline', [PostSidelineController::class, 'favorite']); // Get All
        Route::post('/member/favorite/sideline', [PostSidelineController::class, 'favoriteStore']); // Store and Delete
        Route::get('/member/favorite/sideline/{id}', [PostSidelineController::class, 'favoriteCheck']); // Store and Delete

    });
});
