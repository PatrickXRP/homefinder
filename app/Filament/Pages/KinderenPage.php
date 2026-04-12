<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class KinderenPage extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-face-smile';
    protected static string | \UnitEnum | null $navigationGroup = 'Gezin';
    protected static ?string $navigationLabel = 'Kids Swiper';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Kids Swiper';
    protected static ?string $slug = 'kinderen';

    protected string $view = 'filament.pages.kinderen-page';

    public function mount(): void
    {
        redirect('/kids');
    }
}
