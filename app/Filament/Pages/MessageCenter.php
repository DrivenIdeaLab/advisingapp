<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class MessageCenter extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'assist.filament.pages.coming-soon';

    protected static ?string $navigationGroup = 'Productivity Tools';

    protected static ?int $navigationSort = 2;
}
