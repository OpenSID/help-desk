<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;


class Report extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.report';

    public static function getNavigationLabel(): string
    {
        return __('Report');
    }

    public $activeTab = 1;

}
