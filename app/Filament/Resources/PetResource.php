<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PetResource\Pages;
use App\Filament\Resources\PetResource\RelationManagers;
use App\Models\Pet;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PetResource extends Resource
{
    protected static ?string $model = Pet::class;

    protected static ?string $navigationIcon = 'heroicon-o-face-smile';
    
    protected static ?string $navigationLabel = 'Pets';
    
    protected static ?string $modelLabel = 'Pet';
    
    protected static ?string $pluralModelLabel = 'Pets';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('owner_id')
                    ->label('Owner')
                    ->options(User::where('role', 'owner')->orWhere('role', 'both')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                    
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('breed')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('age')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(30),
                    
                Forms\Components\TextInput::make('weight')
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0.1)
                    ->suffix('kg'),
                    
                Forms\Components\TextInput::make('photo_url')
                    ->label('Photo URL')
                    ->url()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Owner')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('breed')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('age')
                    ->sortable()
                    ->suffix(' years'),
                    
                Tables\Columns\TextColumn::make('weight')
                    ->sortable()
                    ->suffix(' kg'),
                    
                Tables\Columns\ImageColumn::make('photo_url')
                    ->label('Photo')
                    ->circular(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('owner_id')
                    ->label('Owner')
                    ->options(User::where('role', 'owner')->orWhere('role', 'both')->pluck('name', 'id')),
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
            'index' => Pages\ListPets::route('/'),
            'create' => Pages\CreatePet::route('/create'),
            'edit' => Pages\EditPet::route('/{record}/edit'),
        ];
    }
}
