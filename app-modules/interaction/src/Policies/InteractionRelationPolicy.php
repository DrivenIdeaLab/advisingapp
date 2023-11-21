<?php

/*
<COPYRIGHT>

Copyright © 2022-2023, Canyon GBS LLC

All rights reserved.

This file is part of a project developed using Laravel, which is an open-source framework for PHP.
Canyon GBS LLC acknowledges and respects the copyright of Laravel and other open-source
projects used in the development of this solution.

This project is licensed under the Affero General Public License (AGPL) 3.0.
For more details, see https://github.com/canyongbs/assistbycanyongbs/blob/main/LICENSE.

Notice:
- The copyright notice in this file and across all files and applications in this
 repository cannot be removed or altered without violating the terms of the AGPL 3.0 License.
- The software solution, including services, infrastructure, and code, is offered as a
 Software as a Service (SaaS) by Canyon GBS LLC.
- Use of this software implies agreement to the license terms and conditions as stated
 in the AGPL 3.0 License.

For more information or inquiries please visit our website at
https://www.canyongbs.com or contact us via email at legal@canyongbs.com.

</COPYRIGHT>
*/

namespace Assist\Interaction\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Assist\Interaction\Models\InteractionRelation;

class InteractionRelationPolicy
{
    public function viewAny(User $user): Response
    {
        return $user->canOrElse(
            abilities: 'interaction_relation.view-any',
            denyResponse: 'You do not have permission to view interaction relations.'
        );
    }

    public function view(User $user, InteractionRelation $relation): Response
    {
        return $user->canOrElse(
            abilities: ['interaction_relation.*.view', "interaction_relation.{$relation->id}.view"],
            denyResponse: 'You do not have permission to view this interaction relation.'
        );
    }

    public function create(User $user): Response
    {
        return $user->canOrElse(
            abilities: 'interaction_relation.create',
            denyResponse: 'You do not have permission to create interaction relations.'
        );
    }

    public function update(User $user, InteractionRelation $relation): Response
    {
        return $user->canOrElse(
            abilities: ['interaction_relation.*.update', "interaction_relation.{$relation->id}.update"],
            denyResponse: 'You do not have permission to update this interaction relation.'
        );
    }

    public function delete(User $user, InteractionRelation $relation): Response
    {
        return $user->canOrElse(
            abilities: ['interaction_relation.*.delete', "interaction_relation.{$relation->id}.delete"],
            denyResponse: 'You do not have permission to delete this interaction relation.'
        );
    }

    public function restore(User $user, InteractionRelation $relation): Response
    {
        return $user->canOrElse(
            abilities: ['interaction_relation.*.restore', "interaction_relation.{$relation->id}.restore"],
            denyResponse: 'You do not have permission to restore this interaction relation.'
        );
    }

    public function forceDelete(User $user, InteractionRelation $relation): Response
    {
        return $user->canOrElse(
            abilities: ['interaction_relation.*.force-delete', "interaction_relation.{$relation->id}.force-delete"],
            denyResponse: 'You do not have permission to permanently delete this interaction relation.'
        );
    }
}
