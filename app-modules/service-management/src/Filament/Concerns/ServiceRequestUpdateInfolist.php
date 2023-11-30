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

namespace Assist\ServiceManagement\Filament\Concerns;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Assist\ServiceManagement\Models\ServiceRequestUpdate;
use Assist\ServiceManagement\Enums\ServiceRequestUpdateDirection;
use Assist\ServiceManagement\Filament\Resources\ServiceRequestResource;

// TODO Re-use this trait across other places where infolist is rendered
trait ServiceRequestUpdateInfolist
{
    public function serviceRequestUpdateInfolist(): array
    {
        return [
            TextEntry::make('serviceRequest.service_request_number')
                ->label('Service Request')
                ->translateLabel()
                ->url(fn (ServiceRequestUpdate $serviceRequestUpdate): string => ServiceRequestResource::getUrl('view', ['record' => $serviceRequestUpdate->serviceRequest]))
                ->color('primary'),
            IconEntry::make('internal')
                ->boolean(),
            TextEntry::make('direction')
                ->icon(fn (ServiceRequestUpdateDirection $state): string => $state->getIcon())
                ->formatStateUsing(fn (ServiceRequestUpdateDirection $state): string => $state->getLabel()),
            TextEntry::make('update')
                ->columnSpanFull(),
        ];
    }
}