<?php

namespace AdvisingApp\ServiceManagement\Filament\Resources\ChangeRequestResource\Pages;

use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Components\DateTimePicker;
use AdvisingApp\ServiceManagement\Filament\Resources\ChangeRequestResource;

class CreateChangeRequest extends CreateRecord
{
    protected static string $resource = ChangeRequestResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Change Request Details')
                    ->aside()
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('description')
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('reason')
                            ->rows(5)
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('backout_strategy')
                            ->rows(5)
                            ->required()
                            ->columnSpanFull(),
                        DateTimePicker::make('start_time')
                            ->required()
                            ->columnSpan(1),
                        DateTimePicker::make('end_time')
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                Section::make('Risk Management')
                    ->aside()
                    ->schema([
                        TextInput::make('impact')
                            ->reactive()
                            ->helperText('Please enter a number between 1 and 5.')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->columnSpan(1),
                        TextInput::make('likelihood')
                            ->reactive()
                            ->helperText('Please enter a number between 1 and 5.')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->columnSpan(1),
                        ViewField::make('risk_score')
                            ->view('filament.forms.components.change-request.calculated-risk-score'),
                    ])
                    ->columns(3),
            ]);
    }
}
