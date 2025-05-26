<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestTicket extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected function getTableQuery(): Builder
    {
        return Ticket::query()->latest()->take(5);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('title')
                ->description(fn(Ticket $record): string => $record?->description ?? '')
                ->wrap()
                ->sortable(),
            TextColumn::make('priority')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'Low' => 'gray',
                    'Medium' => 'warning',
                    'High' => 'danger',
                })
                ->sortable(),
            TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'Archived' => 'gray',
                    'Open' => 'success',
                    'Close' => 'danger',
                })
                ->sortable(),
            TextInputColumn::make('comment'),
            TextColumn::make('created_at')->dateTime()
                ->sortable()
        ];
    }
    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
