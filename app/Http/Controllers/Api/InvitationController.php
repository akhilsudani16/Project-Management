<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptInvitationRequest;
use App\Http\Requests\InviteRequest;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\UserResource;
use App\Services\InvitationService;
use App\Services\OrganizationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class InvitationController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitationService,
        private readonly OrganizationService $organizationService,
    ) {}

    /**
     * Unified invitation endpoint.
     * Supports role-based smart context detection.
     *
     * Super Admin: Must provide role, organization_id, optional project_id
     * Org Admin: Must provide role (pm/member), optional project_id, org auto-detected
     * PM: project_id auto-detected if single project, role auto-set to member
     */
    public function invite(InviteRequest $request): JsonResponse
    {
        try {
            $result = $this->invitationService->invite(
                inviter: $request->user(),
                email: $request->input('email'),
                name: $request->input('name'),
                role: $request->input('role'),
                organizationId: $request->input('organization_id'),
                projectId: $request->input('project_id'),
            );

            return response()->json([
                'data' => [
                    'user' => new UserResource($result['user']),
                    'organization' => $result['organization'],
                    'project' => $result['project'],
                    'invitation' => $result['invitation'],
                ],
                'message' => __('organization.invitation_sent_successfully'),
            ], Response::HTTP_CREATED);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Accept organization invitation and set password.
     * This is a public endpoint (no auth required).
     */
    public function acceptInvitation(AcceptInvitationRequest $request): JsonResponse
    {
        try {
            $result = $this->organizationService->acceptInvitationWithPassword(
                email: $request->input('email'),
                token: $request->input('token'),
                password: $request->input('password'),
            );

            return ApiResponse::success(
                data: [
                    'user' => (new UserResource($result['user']))->getData($request),
                    'organization' => (new OrganizationResource($result['organization']))->getData($request),
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
                'email' => $request->input('email'),
            ]);

            return ApiResponse::fail(
                message: 'Failed to accept invitation. Please try again.',
                statusCode: 500
            );
        }
    }
}
