<?php

namespace Assist\Form\Filament\Blocks;

use Assist\Form\Models\FormItem;
use Filament\Forms\Components\TextInput as FilamentTextInput;

class TextInput extends Block
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Text Input');
    }

    public static function make(string $name = 'text_input'): static
    {
        return parent::make($name);
    }

    public static function display(FormItem $item): FilamentTextInput
    {
        return FilamentTextInput::make($item->key)
            ->label($item->label)
            ->required($item->required);
    }

    public function fields(): array
    {
        return [];
    }
}
