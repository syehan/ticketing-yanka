<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->autofocus(),
                Select::make('priority')
                    ->options(self::$model::PRIORITY)
                    ->required()
                    ->in(self::$model::PRIORITY),
                Select::make('assigned_to')
                    ->options(
                        User::whereHas('roles', function (Builder $query) {
                            $query->where('name', Role::ROLES['Agent']);
                        })->get()->pluck('name', 'id')
                    )
                    ->required(),
                Forms\Components\Textarea::make('description'),
                Forms\Components\Textarea::make('comment')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->description(fn(Ticket $record): string => $record?->description ?? '')
                    ->wrap()
                    ->sortable(),
                TextColumn::make('priority')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Low' => 'gray',
                        'Medium' => 'warning',
                        'High' => 'danger',
                    })
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->disabled(!auth()->user()->hasPermission('ticket_edit'))
                    ->options(self::$model::STATUS)
                ->selectablePlaceholder(false),
                TextInputColumn::make('comment')
                    ->disabled(!auth()->user()->hasPermission('ticket_edit')),
                TextColumn::make('created_at')->dateTime()
                    ->sortable()

            ])->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(self::$model::STATUS),
                SelectFilter::make('priority')
                    ->options(self::$model::PRIORITY)
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CategoriesRelationManager::class,
            RelationManagers\LabelsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
