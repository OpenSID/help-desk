<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FavoriteProjects;
use App\Filament\Widgets\LatestActivities;
use App\Filament\Widgets\LatestComments;
use App\Filament\Widgets\LatestProjects;
use App\Filament\Widgets\LatestTickets;
use App\Filament\Widgets\TicketsByPriority;
use App\Filament\Widgets\TicketsByType;
use App\Filament\Widgets\TicketTimeLogged;
use App\Filament\Widgets\UserTimeLogged;
use Filament\Pages\Page;


class Dashboard extends Page
{
    public static ?string $route = '/dashboard';
    public static ?string $routeName = 'filament.pages.dashboard'; // Tambahkan baris ini

    // protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament::pages.dashboard';

    protected function getColumns(): int
    {
        return 6;
    }
 

    protected function getWidgets(): array
    {
        return [
            FavoriteProjects::class,
            LatestActivities::class,
            LatestComments::class,
            LatestProjects::class,
            LatestTickets::class,
            TicketsByPriority::class,
            TicketsByType::class,
            TicketTimeLogged::class,
            UserTimeLogged::class
        ];
    }
}
