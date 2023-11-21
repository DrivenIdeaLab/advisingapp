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

namespace Assist\Campaign\Filament\Blocks;

use Assist\Alert\Enums\AlertStatus;
use Assist\Alert\Enums\AlertSeverity;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;

class ProactiveAlertBlock extends CampaignActionBlock
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Proactive Alert');

        $this->schema($this->createFields());
    }

    public function generateFields(string $fieldPrefix = ''): array
    {
        return [
            Textarea::make($fieldPrefix . 'description')
                ->required()
                ->string(),
            Select::make($fieldPrefix . 'severity')
                ->options(AlertSeverity::class)
                ->selectablePlaceholder(false)
                ->default(AlertSeverity::default())
                ->required()
                ->enum(AlertSeverity::class),
            Textarea::make($fieldPrefix . 'suggested_intervention')
                ->required()
                ->string(),
            Select::make($fieldPrefix . 'status')
                ->options(AlertStatus::class)
                ->selectablePlaceholder(false)
                ->default(AlertStatus::default())
                ->required()
                ->enum(AlertStatus::class),
            DateTimePicker::make($fieldPrefix . 'execute_at')
                ->label('When should the journey step be executed?')
                ->required()
                ->minDate(now(auth()->user()->timezone))
                ->closeOnDateSelection(),
        ];
    }

    public static function type(): string
    {
        return 'proactive_alert';
    }
}
