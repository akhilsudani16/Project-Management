<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    /**
     * List tasks based on user access and filters.
     */
    public function list(
        User $user,
        int $perPage = 15,
        ?string $projectId = null,
        ?string $status = null,
        ?string $priority = null,
        ?string $assignedTo = null,
        ?bool $assignedToMe = null,
        ?string $search = null
    ): LengthAwarePaginator {
        $query = Task::query()->with(['project', 'user', 'creator']);

        // Apply access control
        if (! $user->isSuperAdmin()) {
            if ($user->isOrgAdmin()) {
                // Org Admins see tasks in their organizations' projects
                $organizationIds = $user->organizations()->pluck('organizations.id');
                $query->whereHas('project', function ($q) use ($organizationIds): void {
                    $q->whereIn('organization_id', $organizationIds);
                });
            } else {
                // PMs and Members see tasks in their assigned projects
                $projectIds = $user->projects()->pluck('projects.id');
                $query->whereIn('project_id', $projectIds);
            }
        }

        // Apply filters
        if ($projectId !== null) {
            $query->where('project_id', $projectId);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($priority !== null) {
            $query->where('priority', $priority);
        }

        if ($assignedTo !== null) {
            $query->where('user_id', $assignedTo);
        }

        if ($assignedToMe === true) {
            $query->where('user_id', $user->id);
        }

        if ($search !== null) {
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'ILIKE', "%{$search}%")
                    ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get task by ID with relationships.
     */
    public function getById(string $id): Task
    {
        return Task::with([
            'project.organization',
            'user',
            'creator',
            'comments.user',
            'tags',
            'attachments',
        ])->findOrFail($id);
    }

    /**
     * Create a new task.
     */
    public function create(array $data, User $creator): Task
    {
        $project = Project::findOrFail($data['project_id']);

        // Verify creator has access to the project
        if (! $creator->hasAccessToProject($project)) {
            throw new \RuntimeException(__('task.no_access_to_project'));
        }

        // If assigning to someone, verify they have access to the project
        if (isset($data['user_id'])) {
            $assignee = User::findOrFail($data['user_id']);
            if (! $assignee->hasAccessToProject($project)) {
                throw new \RuntimeException(__('task.assignee_no_access_to_project'));
            }
        }

        return Task::create([
            'project_id' => $data['project_id'],
            'user_id' => $data['user_id'] ?? null,
            'created_by' => $creator->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? TaskStatus::TODO->value,
            'priority' => $data['priority'] ?? null,
            'due_date' => $data['due_date'] ?? null,
        ]);
    }

    /**
     * Update an existing task.
     */
    public function update(Task $task, array $data, User $updater): Task
    {
        // Members can only update status and progress
        $allowedFields = ['title', 'description', 'status', 'priority', 'due_date', 'user_id'];

        if ($updater->isMember() && $task->user_id === $updater->id) {
            // Members can only update limited fields
            $allowedFields = ['status'];
        }

        $updateData = [];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        // If changing assignment, verify new assignee has access
        if (isset($updateData['user_id']) && $updateData['user_id'] !== $task->user_id) {
            $project = $task->project;
            if (! $project instanceof Project) {
                throw new \RuntimeException(__('task.invalid_project'));
            }

            if ($updateData['user_id'] !== '') {
                $newAssignee = User::findOrFail($updateData['user_id']);
                if (! $newAssignee->hasAccessToProject($project)) {
                    throw new \RuntimeException(__('task.assignee_no_access_to_project'));
                }
            }
        }

        if (! empty($updateData)) {
            $task->update($updateData);
        }

        return $task->fresh();
    }

    /**
     * Assign task to user.
     */
    public function assign(Task $task, User $assignee): Task
    {
        $project = $task->project;
        if (! $project instanceof Project) {
            throw new \RuntimeException(__('task.invalid_project'));
        }

        // Verify assignee has access to project
        if (! $assignee->hasAccessToProject($project)) {
            throw new \RuntimeException(__('task.assignee_no_access_to_project'));
        }

        $task->update(['user_id' => $assignee->id]);

        return $task->fresh();
    }

    /**
     * Unassign task from current user.
     */
    public function unassign(Task $task): Task
    {
        $task->update(['user_id' => null]);

        return $task->fresh();
    }

    /**
     * Update task status.
     */
    public function updateStatus(Task $task, string $status): Task
    {
        $task->update(['status' => $status]);

        return $task->fresh();
    }

    /**
     * Soft delete (archive) a task.
     */
    public function delete(Task $task, User $deletedBy): bool
    {
        $task->deleted_by = $deletedBy->id;
        $task->save();

        return $task->delete();
    }

    /**
     * Restore a soft-deleted task.
     */
    public function restore(Task $task): bool
    {
        $task->deleted_by = null;
        $task->save();

        return $task->restore();
    }

    /**
     * Get tasks assigned to a specific user in a project.
     */
    public function getProjectTasksForUser(Project $project, User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Task::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->with(['creator', 'project'])
            ->orderBy('due_date')
            ->orderBy('priority')
            ->paginate($perPage);
    }

    /**
     * Get tasks by status for a project.
     */
    public function getTasksByStatus(Project $project): array
    {
        $statuses = TaskStatus::cases();
        $result = [];

        foreach ($statuses as $status) {
            $result[$status->value] = Task::where('project_id', $project->id)
                ->where('status', $status->value)
                ->count();
        }

        return $result;
    }
}
