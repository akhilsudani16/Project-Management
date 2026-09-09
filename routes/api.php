<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\Auth\ChangePasswordController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutAllController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResendVerificationController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\SessionController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\InvitationController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\RestoreController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [RegisterController::class, 'register'])->name('register');
    Route::post('login', [LoginController::class, 'login'])->name('login');
    Route::post('forgot-password', [ForgotPasswordController::class, 'send'])->name('password.email');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::get('email/verify', [VerifyEmailController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::prefix('profile')->group(function () {
        Route::post('change-password', [ChangePasswordController::class, 'change'])->name('password.change');
        Route::post('resend-verification', [ResendVerificationController::class, 'resend'])->name('verification.send');
    });

    Route::prefix('sessions')->group(function () {
        Route::get('/', [SessionController::class, 'index'])->name('sessions.index');
        Route::delete('{tokenId}', [SessionController::class, 'destroy'])->name('sessions.destroy');
    });

    Route::post('logout', [LogoutController::class, 'logout'])->name('logout');
    Route::post('logout-all', [LogoutAllController::class, 'logoutAll'])->name('logout.all');
    Route::post('invitations', [InvitationController::class, 'invite'])->name('invitations.invite');
    Route::post('restore', [RestoreController::class, 'restore'])->name('restore');

    Route::apiResource('organizations', OrganizationController::class);
    Route::prefix('organizations/{organization}')->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('/', [OrganizationController::class, 'members'])->name('organizations.users');
            Route::post('invite', [OrganizationController::class, 'inviteUser'])->name('organizations.users.invite');
            Route::patch('{user}', [OrganizationController::class, 'updateMember'])->name('organizations.users.update');
            Route::delete('{user}', [OrganizationController::class, 'removeMember'])->name('organizations.users.remove');
        });
        Route::get('tags', [TagController::class, 'index'])->name('organizations.tags');
    });

    Route::apiResource('projects', ProjectController::class);
    Route::prefix('projects/{project}')->group(function () {
        Route::prefix('members')->group(function () {
            Route::get('/', [ProjectController::class, 'members'])->name('projects.members');
            Route::post('/', [ProjectController::class, 'assignUser'])->name('projects.members.assign');
            Route::delete('{user}', [ProjectController::class, 'removeUser'])->name('projects.members.remove');
        });
        Route::prefix('comments')->group(function () {
            Route::get('/', [CommentController::class, 'projectComments'])->name('projects.comments');
            Route::post('/', [CommentController::class, 'storeProjectComment'])->name('projects.comments.store');
        });
    });

    Route::apiResource('tasks', TaskController::class);
    Route::prefix('tasks/{task}')->group(function () {
        Route::post('assign', [TaskController::class, 'assign'])->name('tasks.assign');
        Route::post('unassign', [TaskController::class, 'unassign'])->name('tasks.unassign');
        Route::prefix('comments')->group(function () {
            Route::get('/', [CommentController::class, 'taskComments'])->name('tasks.comments');
            Route::post('/', [CommentController::class, 'storeTaskComment'])->name('tasks.comments.store');
        });
    });

    Route::prefix('comments/{comment}')->group(function () {
        Route::patch('/', [CommentController::class, 'update'])->name('comments.update');
        Route::delete('/', [CommentController::class, 'destroy'])->name('comments.destroy');
    });

    Route::prefix('tags')->group(function () {
        Route::post('/', [TagController::class, 'store'])->name('tags.store');
        Route::patch('{tag}', [TagController::class, 'update'])->name('tags.update');
        Route::delete('{tag}', [TagController::class, 'destroy'])->name('tags.destroy');
    });

    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    Route::prefix('attachments')->group(function () {
        Route::post('/', [AttachmentController::class, 'store'])->name('attachments.store');
        Route::get('{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
        Route::delete('{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
    });
});
