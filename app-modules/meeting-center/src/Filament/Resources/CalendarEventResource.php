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

namespace Assist\MeetingCenter\Filament\Resources;

use Filament\Resources\Resource;
use Assist\MeetingCenter\Models\CalendarEvent;
use Assist\MeetingCenter\Filament\Resources\CalendarEventResource\Pages\EditCalendarEvent;
use Assist\MeetingCenter\Filament\Resources\CalendarEventResource\Pages\ViewCalendarEvent;
use Assist\MeetingCenter\Filament\Resources\CalendarEventResource\Pages\ListCalendarEvents;
use Assist\MeetingCenter\Filament\Resources\CalendarEventResource\Pages\CreateCalendarEvent;

class CalendarEventResource extends Resource
{
    protected static ?string $model = CalendarEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Meeting Center';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Schedule';

    protected static ?string $modelLabel = 'Event';

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCalendarEvents::route('/'),
            'create' => CreateCalendarEvent::route('/create'),
            'view' => ViewCalendarEvent::route('/{record}'),
            'edit' => EditCalendarEvent::route('/{record}/edit'),
        ];
    }
}