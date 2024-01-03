<?php

/*
<COPYRIGHT>

    Copyright © 2022-2023, Canyon GBS LLC. All rights reserved.

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

namespace AdvisingApp\Engagement\Policies;

use App\Models\Authenticatable;
use Illuminate\Auth\Access\Response;
use AdvisingApp\Engagement\Models\Engagement;
use AdvisingApp\Authorization\Enums\LicenseType;

class EngagementPolicy
{
    public function viewAny(Authenticatable $authenticatable): Response
    {
        if (! $authenticatable->hasAnyLicense([LicenseType::RetentionCrm, LicenseType::RecruitmentCrm])) {
            return Response::deny('You do not have permission to view engagements.');
        }

        return $authenticatable->canOrElse(
            abilities: 'engagement.view-any',
            denyResponse: 'You do not have permission to view engagements.'
        );
    }

    public function view(Authenticatable $authenticatable, Engagement $engagement): Response
    {
        if (! $authenticatable->hasLicense($engagement->recipient?->getLicenseType())) {
            return Response::deny('You do not have permission to view this engagement.');
        }

        return $authenticatable->canOrElse(
            abilities: ['engagement.*.view', "engagement.{$engagement->id}.view"],
            denyResponse: 'You do not have permission to view this engagement.'
        );
    }

    public function create(Authenticatable $authenticatable): Response
    {
        return $authenticatable->canOrElse(
            abilities: 'engagement.create',
            denyResponse: 'You do not have permission to create engagements.'
        );
    }

    public function update(Authenticatable $authenticatable, Engagement $engagement): Response
    {
        if ($engagement->hasBeenDelivered()) {
            return Response::deny('You do not have permission to update this engagement because it has already been delivered.');
        }

        if (! $authenticatable->hasLicense($engagement->recipient?->getLicenseType())) {
            return Response::deny('You do not have permission to update this engagement.');
        }

        return $authenticatable->canOrElse(
            abilities: ['engagement.*.update', "engagement.{$engagement->id}.update"],
            denyResponse: 'You do not have permission to update this engagement.'
        );
    }

    public function delete(Authenticatable $authenticatable, Engagement $engagement): Response
    {
        if ($engagement->hasBeenDelivered()) {
            return Response::deny('You do not have permission to delete this engagement because it has already been delivered.');
        }

        if (! $authenticatable->hasLicense($engagement->recipient?->getLicenseType())) {
            return Response::deny('You do not have permission to delete this engagement.');
        }

        return $authenticatable->canOrElse(
            abilities: ['engagement.*.delete', "engagement.{$engagement->id}.delete"],
            denyResponse: 'You do not have permission to delete this engagement.'
        );
    }

    public function restore(Authenticatable $authenticatable, Engagement $engagement): Response
    {
        if (! $authenticatable->hasLicense($engagement->recipient?->getLicenseType())) {
            return Response::deny('You do not have permission to restore this engagement.');
        }

        return $authenticatable->canOrElse(
            abilities: ['engagement.*.restore', "engagement.{$engagement->id}.restore"],
            denyResponse: 'You do not have permission to restore this engagement.'
        );
    }

    public function forceDelete(Authenticatable $authenticatable, Engagement $engagement): Response
    {
        if ($engagement->hasBeenDelivered()) {
            return Response::deny('You cannot permanently delete this engagement because it has already been delivered.');
        }

        if (! $authenticatable->hasLicense($engagement->recipient?->getLicenseType())) {
            return Response::deny('You do not have permission to permanently delete this engagement.');
        }

        return $authenticatable->canOrElse(
            abilities: ['engagement.*.force-delete', "engagement.{$engagement->id}.force-delete"],
            denyResponse: 'You do not have permission to permanently delete this engagement.'
        );
    }
}
