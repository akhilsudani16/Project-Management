<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptInvitationRequest;
use App\Http\Requests\InviteRequest;
use App\Http\Resources\UserResource;
use App\Services\InvitationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitationService,
    ) {}

    /**
     * Simplified invitation endpoint - creates user, stores reference, sends email.
     * Does NOT assign to organization/project yet - that happens via separate APIs.
     */
    public function invite(InviteRequest $request): JsonResponse
    {
        try {
            $result = $this->invitationService->invite(
                inviter: $request->user(),
                email: $request->input('email'),
                name: $request->input('name'),
                role: $request->input('role'),
                orgAdminId: $request->input('org_admin_id'),
                projectManagerId: $request->input('project_manager_id')
            );

            return ApiResponse::success(
                data: [
                    'user' => (new UserResource($result['user']))->getData($request),
                    'assigned_under' => $result['assigned_under'],
                    'invitation' => $result['invitation'],
                ],
                message: __('organization.invitation_sent_successfully'),
                statusCode: 201
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::fail(
                message: $e->getMessage(),
                statusCode: 422
            );
        } catch (\Throwable $e) {
            logger()->error('Invitation error: '.$e->getMessage(), [
                'exception' => $e,
                'email' => $request->input('email'),
            ]);

            return ApiResponse::fail(
                message: 'Failed to send invitation. Please try again.',
                statusCode: 500
            );
        }
    }

    /**
     * Verify invitation token before accepting.
     * This is a public endpoint (no auth required).
     * Allows UI to validate token and show user info before password setup.
     */
    public function verifyToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        try {
            $result = $this->invitationService->verifyToken(
                token: $request->input('token')
            );

            return ApiResponse::success(
                data: $result,
                message: 'Invitation token is valid',
                statusCode: 200
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::fail(
                message: $e->getMessage(),
                statusCode: 422
            );
        }
    }

    /**
     * Accept organization invitation and set password.
     * This is a public endpoint (no auth required).
     * Token contains all necessary information including email.
     */
    public function acceptInvitation(AcceptInvitationRequest $request): JsonResponse
    {
        try {
            $result = $this->invitationService->acceptInvitation(
                token: $request->input('token'),
                password: $request->input('password'),
            );

            return ApiResponse::success(
                data: [
                    'user' => (new UserResource($result['user']))->getData($request),
                ],
                message: __('organization.invitation_accepted_successfully'),
                statusCode: 200
            );
        } catch (ModelNotFoundException $e) {
            return ApiResponse::fail(
                message: __('organization.no_pending_invitations'),
                statusCode: 404
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::fail(
                message: $e->getMessage(),
                statusCode: 422
            );
        } catch (\Throwable $e) {
            logger()->error('Accept invitation error: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return ApiResponse::fail(
                message: 'Failed to accept invitation. Please try again.',
                statusCode: 500
            );
        }
    }
}
