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

namespace Assist\Authorization\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Filament\Facades\Filament;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;
use Assist\Authorization\Enums\SocialiteProvider;

class SocialiteController extends Controller
{
    public function redirect(SocialiteProvider $provider, Request $request)
    {
        // Regenerate session and logout user to try to fix InvalidStateException
        if ($request->hasSession()) {
            $request->session()->regenerate(true);
        }

        auth()->guard('web')->logout();

        return $provider->driver()
            ->setConfig($provider->config())
            ->redirect();
    }

    public function callback(SocialiteProvider $provider)
    {
        $socialiteUser = $provider
            ->driver()
            ->setConfig($provider->config())
            ->user();

        /** @var User $user */
        $user = User::query()
            ->where('email', $socialiteUser->getEmail())
            ->first();

        if (! $user?->is_external) {
            Notification::make()
                ->title('A user with that email address not found. Please contact your administrator.')
                ->danger()
                ->send();

            return redirect()->to(Filament::getLoginUrl());
        }

        if ($provider === SocialiteProvider::Azure) {
            $request = Http::withToken($socialiteUser->token)
                ->contentType('image/jpeg')
                ->get('https://graph.microsoft.com/v1.0/me/photo/$value');

            $user->addMediaFromString($request->body())->usingFileName(Str::uuid() . '.jpg')->toMediaCollection('avatar');
        }

        $user->update([
            'name' => $socialiteUser->getName(),
            'avatar_url' => $socialiteUser->getAvatar(),
        ]);

        Auth::login($user);

        session(['auth_via' => $provider]);

        return redirect()->to(Filament::getUrl());
    }
}
