<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Label;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        return [
            Stat::make('Total Ticket', Ticket::count()),
            Stat::make('Total Agent', User::whereHas('roles', function ($query) {
                $query->where('title', Role::ROLES['Agent']);
            })->count()),
            Stat::make('Total Category', Category::count()),
            Stat::make('Total Label', Label::count()),
        ];
    }
}
