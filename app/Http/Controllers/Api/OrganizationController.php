<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\OrganizationUserStatus;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
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
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth:sanctum')]
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
    public function show(Organization $organization): JsonResponse
    {
        $this->authorize('view', $organization);

        $organization = $this->organizationService->getById($organization->id);

        return (new OrganizationResource($organization))
            ->withMessage(__('organization.retrieved_successfully'))
            ->toResponse(request());
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

            return ApiResponse::success(
                message: __('organization.deleted_successfully')
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::fail(
                message: $e->getMessage(),
                statusCode: 422
            );
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
            user: $request->user(),
            perPage: (int) $request->input('per_page', 15),
            status: $request->input('status'),
            search: $request->input('search'),
        );

        return (new ApiResourceCollection($members))
            ->setResourceClass(UserResource::class)
            ->withMessage(__('organization.members_retrieved'));
    }

    /**
     * Update organization member (role or status).
     */
    public function updateMember(
        Request $request,
        Organization $organization,
        User $user
    ): JsonResponse {
        $this->authorize('updateMember', [$organization, $user]);

        $request->validate([
            'role' => ['nullable', 'string', 'in:organization_admin,project_manager,member'],
            'status' => ['nullable', 'string', 'in:active,pending,rejected'],
        ]);

        // Update role if provided
        if ($request->has('role')) {
            $user = $this->organizationService->updateMemberRole(
                organization: $organization,
                user: $user,
                newRoleName: $request->input('role'),
            );
        }

        // Update organization membership status if provided
        if ($request->has('status')) {
            $statusValue = match ($request->input('status')) {
                'active' => OrganizationUserStatus::ACTIVE->value,
                'pending' => OrganizationUserStatus::PENDING->value,
                'rejected' => OrganizationUserStatus::REJECTED->value,
            };

            $organization->members()->updateExistingPivot($user->id, [
                'status' => $statusValue,
                'accepted_at' => $statusValue === OrganizationUserStatus::ACTIVE->value ? now() : null,
            ]);
        }

        return (new UserResource($user->fresh()))
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

        return ApiResponse::success(
            message: __('organization.member_removed_successfully')
        );
    }
}
