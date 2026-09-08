<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\Auth\ChangePasswordController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutAllController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResendVerificationController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\SessionController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::post('/register', [RegisterController::class, 'register'])->name('register');
Route::post('/login', [LoginController::class, 'login'])->name('login');

// Password reset (public)
Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])->name('password.email');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// Email verification (public - uses signed URL for security)
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Current user
    Route::get('/user', [MeController::class, 'me'])->name('user');

    // Resend email verification
    Route::post('/email/resend', [ResendVerificationController::class, 'resend'])->name('verification.send');

    // Password management
    Route::post('/change-password', [ChangePasswordController::class, 'change'])->name('password.change');

    // Session management
    Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.index');
    Route::delete('/sessions/{tokenId}', [SessionController::class, 'destroy'])->name('sessions.destroy');

    // Logout
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
    Route::post('/logout-all', [LogoutAllController::class, 'logoutAll'])->name('logout.all');

    // Organization Management
    Route::apiResource('organizations', OrganizationController::class);
    Route::post('/organizations/{id}/restore', [OrganizationController::class, 'restore'])->name('organizations.restore');
    Route::get('/organizations/{organization}/members', [OrganizationController::class, 'members'])->name('organizations.members');
    Route::post('/organizations/{organization}/members/invite', [OrganizationController::class, 'inviteUser'])->name('organizations.members.invite');
    Route::patch('/organizations/{organization}/members/{user}', [OrganizationController::class, 'updateMember'])->name('organizations.members.update');
    Route::delete('/organizations/{organization}/members/{user}', [OrganizationController::class, 'removeMember'])->name('organizations.members.remove');

    // Project Management
    Route::apiResource('projects', ProjectController::class);
    Route::post('/projects/{id}/restore', [ProjectController::class, 'restore'])->name('projects.restore');
    Route::get('/projects/{project}/members', [ProjectController::class, 'members'])->name('projects.members');
    Route::post('/projects/{project}/members', [ProjectController::class, 'assignUser'])->name('projects.members.assign');
    Route::delete('/projects/{project}/members/{user}', [ProjectController::class, 'removeUser'])->name('projects.members.remove');

    // Task Management
    Route::apiResource('tasks', TaskController::class);
    Route::post('/tasks/{id}/restore', [TaskController::class, 'restore'])->name('tasks.restore');
    Route::post('/tasks/{task}/assign', [TaskController::class, 'assign'])->name('tasks.assign');
    Route::post('/tasks/{task}/unassign', [TaskController::class, 'unassign'])->name('tasks.unassign');
    Route::get('/tasks/overdue/list', [TaskController::class, 'overdue'])->name('tasks.overdue');

    // Comment Management
    Route::get('/projects/{project}/comments', [CommentController::class, 'projectComments'])->name('projects.comments');
    Route::post('/projects/{project}/comments', [CommentController::class, 'storeProjectComment'])->name('projects.comments.store');
    Route::get('/tasks/{task}/comments', [CommentController::class, 'taskComments'])->name('tasks.comments');
    Route::post('/tasks/{task}/comments', [CommentController::class, 'storeTaskComment'])->name('tasks.comments.store');
    Route::patch('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Tag Management
    Route::get('/organizations/{organization}/tags', [TagController::class, 'index'])->name('organizations.tags');
    Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
    Route::patch('/tags/{tag}', [TagController::class, 'update'])->name('tags.update');
    Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');
    Route::post('/tags/{tag}/tasks/{task}/attach', [TagController::class, 'attachToTask'])->name('tags.attach');
    Route::delete('/tags/{tag}/tasks/{task}/detach', [TagController::class, 'detachFromTask'])->name('tags.detach');

    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
});
