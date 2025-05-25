<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RatingResource\Pages;
use App\Filament\Resources\RatingResource\RelationManagers;
use App\Models\Rating;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RatingResource extends Resource
{
    protected static ?string $model = Rating::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';
    
    protected static ?string $navigationLabel = 'Ratings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('owner_id')
                    ->label('Owner')
                    ->options(User::where('role', 'owner')->orWhere('role', 'both')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                    
                Forms\Components\Select::make('sitter_id')
                    ->label('Sitter')
                    ->options(User::where('role', 'sitter')->orWhere('role', 'both')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                    
                Forms\Components\Select::make('score')
                    ->options([
                        1 => '1 Star - Poor',
                        2 => '2 Stars - Fair',
                        3 => '3 Stars - Good',
                        4 => '4 Stars - Very Good',
                        5 => '5 Stars - Excellent',
                    ])
                    ->required(),
                    
                Forms\Components\Textarea::make('comment')
                    ->maxLength(500)
                    ->rows(3),
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
                    ->label('Sitter')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('score')
                    ->badge()
                    ->colors([
                        'danger' => 1,
                        'warning' => 2,
                        'primary' => 3,
                        'success' => [4, 5],
                    ])
                    ->formatStateUsing(fn ($state) => str_repeat('⭐', $state)),
                    
                Tables\Columns\TextColumn::make('comment')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('score')
                    ->options([
                        1 => '1 Star',
                        2 => '2 Stars',
                        3 => '3 Stars',
                        4 => '4 Stars',
                        5 => '5 Stars',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListRatings::route('/'),
            'create' => Pages\CreateRating::route('/create'),
            'edit' => Pages\EditRating::route('/{record}/edit'),
        ];
    }
}
