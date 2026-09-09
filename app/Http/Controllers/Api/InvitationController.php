<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InviteRequest;
use App\Http\Resources\UserResource;
use App\Services\InvitationService;
use Illuminate\Http\JsonResponse;

class InvitationController extends Controller
{
    public function __construct(
        private readonly InvitationService $invitationService,
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
                'message' => 'User invited successfully.',
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
