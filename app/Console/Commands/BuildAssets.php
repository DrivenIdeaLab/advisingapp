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

namespace App\Console\Commands;

use App\Settings\BrandSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Process;

class BuildAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:build-assets {script?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Builds all assets, including custom CSS from the database.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (Schema::hasTable('settings')) {
            file_put_contents(
                resource_path('css/filament/admin/custom.css'),
                app(BrandSettings::class)->custom_css ?? '',
            );
        }

        $script = $this->argument('script');
        $script = filled($script) ? "build:{$script}" : 'build';

        $process = Process::run(
            <<<BASH
                #!/bin/bash
                [ -s "/usr/local/nvm/nvm.sh" ] && \. "/usr/local/nvm/nvm.sh"
                npm run {$script}
            BASH
        )->throw();

        $this->line($process->output());

        $this->info('Assets have been built.');

        return static::SUCCESS;
    }
}
