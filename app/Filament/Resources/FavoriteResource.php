<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FavoriteResource\Pages;
use App\Filament\Resources\FavoriteResource\RelationManagers;
use App\Models\Favorite;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FavoriteResource extends Resource
{
    protected static ?string $model = Favorite::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';
    
    protected static ?string $navigationLabel = 'Favorites';
    
    protected static ?string $modelLabel = 'Favorite';
    
    protected static ?string $pluralModelLabel = 'Favorites';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('owner_id')
                    ->label('Owner')
                    ->options(User::where('role', 'owner')->orWhere('role', 'both')->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                $sitterId = request()->input('sitter_id');
                                if ($sitterId && Favorite::where('owner_id', $value)->where('sitter_id', $sitterId)->exists()) {
                                    $fail('This combination of owner and sitter already exists in favorites.');
                                }
                            };
                        },
                    ]),
                    
                Forms\Components\Select::make('sitter_id')
                    ->label('Sitter')
                    ->options(User::where('role', 'sitter')->orWhere('role', 'both')->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                $ownerId = request()->input('owner_id');
                                if ($ownerId && Favorite::where('owner_id', $ownerId)->where('sitter_id', $value)->exists()) {
                                    $fail('This combination of owner and sitter already exists in favorites.');
                                }
                            };
                        },
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('sitter.name')
                    ->label('Favorite Sitter')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Added to Favorites')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label('Remove from Favorites'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Remove Selected from Favorites'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Get the table record key for composite primary key
     */
    public static function getTableRecordKey($record): string
    {
        return $record->owner_id . ',' . $record->sitter_id;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFavorites::route('/'),
            'create' => Pages\CreateFavorite::route('/create'),
        ];
    }
    
    public static function canEdit($record): bool
    {
        return false; // Favorites don't need editing, only adding/removing
    }
}
