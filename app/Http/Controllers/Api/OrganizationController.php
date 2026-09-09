<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\InviteUserRequest;
use App\Http\Requests\Organization\StoreOrganizationRequest;
use App\Http\Requests\Organization\UpdateOrganizationRequest;
use App\Http\Resources\ApiResourceCollection;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\UserResource;
use App\Models\Organization;
use App\Models\User;
use App\Services\OrganizationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly OrganizationService $organizationService,
    ) {}

    /**
     * Display a listing of organizations.
     */
    public function index(Request $request): ApiResourceCollection
    {
        $this->authorize('viewAny', Organization::class);

        $organizations = $this->organizationService->list(
            user: $request->user(),
            perPage: (int) $request->input('per_page', 15),
            status: $request->input('status'),
            search: $request->input('search'),
        );

        return (new ApiResourceCollection($organizations))
            ->setResourceClass(OrganizationResource::class)
            ->withMessage(__('organization.list_retrieved'));
    }

    /**
     * Store a newly created organization.
     */
    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        $organization = $this->organizationService->create(
            data: $request->validated(),
            creator: $request->user(),
        );

        return (new OrganizationResource($organization->fresh()->load('createdBy')))
            ->withMessage(__('organization.created_successfully'))
            ->withStatusCode(201)
            ->toResponse($request);
    }

    /**
     * Display the specified organization.
     */
    public function show(Organization $organization): OrganizationResource
    {
        $this->authorize('view', $organization);

        $organization = $this->organizationService->getById($organization->id);

        return new OrganizationResource($organization);
    }

    /**
     * Update the specified organization.
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization): JsonResponse
    {
        $updated = $this->organizationService->update(
            organization: $organization,
            data: $request->validated(),
        );

        return (new OrganizationResource($updated->load('createdBy')))
            ->withMessage(__('organization.updated_successfully'))
            ->toResponse($request);
    }

    /**
     * Remove the specified organization from storage.
     */
    public function destroy(Organization $organization, Request $request): JsonResponse
    {
        $this->authorize('delete', $organization);

        try {
            $this->organizationService->delete(
                organization: $organization,
                deletedBy: $request->user(),
            );

            return response()->json([
                'message' => __('organization.deleted_successfully'),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display organization members.
     */
    public function members(Organization $organization, Request $request): ApiResourceCollection
    {
        $this->authorize('viewMembers', $organization);

        $members = $this->organizationService->getMembers(
            organization: $organization,
            perPage: (int) $request->input('per_page', 15),
            status: $request->input('status'),
            search: $request->input('search'),
        );

        return (new ApiResourceCollection($members))
            ->setResourceClass(UserResource::class)
            ->withMessage(__('organization.members_retrieved'));
    }

    /**
     * Invite user to organization.
     */
    public function inviteUser(InviteUserRequest $request, Organization $organization): JsonResponse
    {
        $result = $this->organizationService->inviteUser(
            organization: $organization,
            email: $request->input('email'),
            roleName: $request->input('role'),
            invitedBy: $request->user(),
            name: $request->input('name'),
        );

        return response()->json([
            'data' => [
                'user' => new UserResource($result['user']),
                'invitation' => $result['invitation'],
            ],
            'message' => __('organization.invitation_sent_successfully'),
        ], 201);
    }

    /**
     * Update organization member.
     */
    public function updateMember(
        Request $request,
        Organization $organization,
        User $user
    ): JsonResponse {
        $this->authorize('updateMember', [$organization, $user]);

        $request->validate([
            'role' => ['required', 'string', 'in:organization_admin,project_manager,member'],
        ]);

        $updated = $this->organizationService->updateMemberRole(
            organization: $organization,
            user: $user,
            newRoleName: $request->input('role'),
        );

        return (new UserResource($updated))
            ->withMessage(__('organization.member_updated_successfully'))
            ->toResponse($request);
    }

    /**
     * Remove member from organization.
     */
    public function removeMember(
        Request $request,
        Organization $organization,
        User $user
    ): JsonResponse {
        $this->authorize('removeMember', [$organization, $user]);

        $this->organizationService->removeMember(
            organization: $organization,
            user: $user,
            removedBy: $request->user(),
        );

        return response()->json([
            'message' => __('organization.member_removed_successfully'),
        ]);
    }
}
