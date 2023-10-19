<?php

namespace Assist\MeetingCenter\Providers;

use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Assist\MeetingCenter\Models\Calendar;
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