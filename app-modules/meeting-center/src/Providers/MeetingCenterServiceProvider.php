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

namespace Assist\MeetingCenter\Providers;

use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Assist\MeetingCenter\Models\Calendar;
use Illuminate\Console\Scheduling\Schedule;
use Assist\MeetingCenter\Jobs\SyncCalendars;
use Assist\MeetingCenter\MeetingCenterPlugin;
use Assist\MeetingCenter\Models\CalendarEvent;
use Assist\Authorization\AuthorizationRoleRegistry;
use Illuminate\Database\Eloquent\Relations\Relation;
use Assist\Authorization\AuthorizationPermissionRegistry;
use Assist\MeetingCenter\Observers\CalendarEventObserver;

class MeetingCenterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(fn (Panel $panel) => $panel->plugin(new MeetingCenterPlugin()));
    }

    public function boot(): void
    {
        Relation::morphMap([
            'calendar' => Calendar::class,
            'calendar_event' => CalendarEvent::class,
        ]);

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            // TODO Ensure we are locking entities that have already been picked up for processing to avoid overlap
            $schedule->job(SyncCalendars::class)
                ->everyMinute()
                ->withoutOverlapping();
        });

        $this->registerRolesAndPermissions();

        $this->registerObservers();
    }

    protected function registerRolesAndPermissions(): void
    {
        $permissionRegistry = app(AuthorizationPermissionRegistry::class);

        $permissionRegistry->registerApiPermissions(
            module: 'meeting-center',
            path: 'permissions/api/custom'
        );

        $permissionRegistry->registerWebPermissions(
            module: 'meeting-center',
            path: 'permissions/web/custom'
        );

        $roleRegistry = app(AuthorizationRoleRegistry::class);

        $roleRegistry->registerApiRoles(
            module: 'meeting-center',
            path: 'roles/api'
        );

        $roleRegistry->registerWebRoles(
            module: 'meeting-center',
            path: 'roles/web'
        );
    }

    protected function registerObservers(): void
    {
        CalendarEvent::observe(CalendarEventObserver::class);
    }
}
