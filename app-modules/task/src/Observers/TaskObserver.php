<?php

/*
<COPYRIGHT>

    Copyright © 2016-2024, Canyon GBS LLC. All rights reserved.

    Advising App™ is licensed under the Elastic License 2.0. For more details,
    see https://github.com/canyongbs/advisingapp/blob/main/LICENSE.

    Notice:

    - You may not provide the software to third parties as a hosted or managed
      service, where the service provides users with access to any substantial set of
      the features or functionality of the software.
    - You may not move, change, disable, or circumvent the license key functionality
      in the software, and you may not remove or obscure any functionality in the
      software that is protected by the license key.
    - You may not alter, remove, or obscure any licensing, copyright, or other notices
      of the licensor in the software. Any use of the licensor’s trademarks is subject
      to applicable law.
    - Canyon GBS LLC respects the intellectual property rights of others and expects the
      same in return. Canyon GBS™ and Advising App™ are registered trademarks of
      Canyon GBS LLC, and we are committed to enforcing and protecting our trademarks
      vigorously.
    - The software solution, including services, infrastructure, and code, is offered as a
      Software as a Service (SaaS) by Canyon GBS LLC.
    - Use of this software implies agreement to the license terms and conditions as stated
      in the Elastic License 2.0.

    For more information or inquiries please visit our website at
    https://www.canyongbs.com or contact us via email at legal@canyongbs.com.

</COPYRIGHT>
*/

namespace AdvisingApp\Task\Observers;

use Exception;
use App\Models\User;
use Laravel\Pennant\Feature;
use AdvisingApp\Task\Models\Task;
use Illuminate\Support\Facades\DB;
use AdvisingApp\Authorization\Models\Permission;
use AdvisingApp\Authorization\Actions\GetPermissionGroupId;
use AdvisingApp\Notification\Events\TriggeredAutoSubscription;
use AdvisingApp\Task\Notifications\TaskAssignedToUserNotification;

class TaskObserver
{
    public function saving(Task $task): void
    {
        DB::beginTransaction();

        if (is_null($task->created_by)) {
            $user = auth()->user();

            if ($user instanceof User) {
                $task->created_by = $user->id;
            }
        }

        if (is_null($task->assigned_to)) {
            $task->assigned_to = auth()->id();
        }
    }

    public function creating(Task $task): void
    {
        Permission::create([
            'name' => $permissionName = "task.{$task->id}.update",
            'guard_name' => 'web',
            ...(Feature::active('permission-groups') ? [
                'group_id' => app(GetPermissionGroupId::class)($permissionName),
            ] : []),
        ]);
    }

    public function created(Task $task): void
    {
        try {
            // Add permissions to creator
            $task->createdBy?->givePermissionTo("task.{$task->id}.update");

            // Add permissions to assigned User unless they are the creator
            if ($task->assigned_to !== $task->created_by) {
                $task->assignedTo?->givePermissionTo("task.{$task->id}.update");
            }

            TriggeredAutoSubscription::dispatchIf(! empty($task->createdBy), $task->createdBy, $task);
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function updated(Task $task): void
    {
        try {
            if ($task->isDirty('assigned_to') && $task->assigned_to !== $task->created_by) {
                if ($task->getOriginal('assigned_to') !== $task->created_by) {
                    User::find($task->getOriginal('assigned_to'))?->revokePermissionTo("task.{$task->id}.update");
                }

                // Add permissions to newly assigned User
                $task->assignedTo?->givePermissionTo("task.{$task->id}.update");
            }
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function saved(Task $task): void
    {
        DB::commit();

        if (! empty($task->assignedTo) && ($task->wasChanged('assigned_to') || ($task->wasRecentlyCreated))) {
            $task->assignedTo->notify(new TaskAssignedToUserNotification($task));

            TriggeredAutoSubscription::dispatch($task->assignedTo, $task);
        }
    }

    public function deleted(Task $task): void
    {
        // Remove permissions from creator and assigned User
    }
}
