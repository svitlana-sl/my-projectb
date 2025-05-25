<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SitterProfileResource\Pages;
use App\Filament\Resources\SitterProfileResource\RelationManagers;
use App\Models\SitterProfile;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SitterProfileResource extends Resource
{
    protected static ?string $model = SitterProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    
    protected static ?string $navigationLabel = 'Sitter Profiles';
    
    protected static ?string $modelLabel = 'Sitter Profile';
    
    protected static ?string $pluralModelLabel = 'Sitter Profiles';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Sitter')
                    ->options(
                        User::where('role', 'sitter')
                            ->orWhere('role', 'both')
                            ->whereDoesntHave('sitterProfile')
                            ->pluck('name', 'id')
                    )
                    ->required()
                    ->searchable()
                    ->helperText('Only users with sitter role who don\'t have a profile yet'),
                    
                Forms\Components\Textarea::make('bio')
                    ->label('Biography')
                    ->maxLength(1000)
                    ->rows(4)
                    ->helperText('Tell potential clients about yourself and your experience'),
                    
                Forms\Components\TextInput::make('default_hourly_rate')
                    ->label('Default Hourly Rate')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->suffix('€')
                    ->helperText('Your standard rate per hour'),
                    
                Forms\Components\Fieldset::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.0000001)
                            ->helperText('Latitude for location-based searches'),
                            
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.0000001)
                            ->helperText('Longitude for location-based searches'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('user.avatar_url')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=Sitter&background=random'),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Sitter Name')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('user.city')
                    ->label('City')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('default_hourly_rate')
                    ->label('Rate')
                    ->money('EUR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('bio')
                    ->label('Bio')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                    
                Tables\Columns\IconColumn::make('has_location')
                    ->label('Location Set')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->latitude && $record->longitude),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_location')
                    ->label('Has Location')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('latitude')->whereNotNull('longitude')),
                    
                Tables\Filters\Filter::make('has_rate')
                    ->label('Has Rate Set')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('default_hourly_rate')),
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
            'index' => Pages\ListSitterProfiles::route('/'),
            'create' => Pages\CreateSitterProfile::route('/create'),
            'edit' => Pages\EditSitterProfile::route('/{record}/edit'),
        ];
    }
}
