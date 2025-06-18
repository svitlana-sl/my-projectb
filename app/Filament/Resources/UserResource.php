<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'Users';
    
    protected static ?string $modelLabel = 'User';
    
    protected static ?string $pluralModelLabel = 'Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                    
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255),
                    
                Forms\Components\Select::make('role')
                    ->options([
                        'owner' => 'Owner',
                        'sitter' => 'Sitter',
                        'both' => 'Both',
                        'admin' => 'Admin',
                    ])
                    ->required()
                    ->default('owner'),
                    
                Forms\Components\Toggle::make('is_admin')
                    ->label('Admin Access'),
                    
                Forms\Components\FileUpload::make('avatar_file')
                    ->label('Avatar')
                    ->image()
                    ->directory('avatars')
                    ->disk(config('image.storage.disk', 'public'))
                    ->maxSize(config('image.validation.max_file_size') / 1024)
                    ->acceptedFileTypes(config('image.validation.allowed_mime_types'))
                    ->visibility('public')
                    ->dehydrated(true)
                    ->helperText('Максимальний розмір: ' . config('image.validation.max_file_size') / 1024 / 1024 . 'MB. Підтримувані формати: ' . implode(', ', config('image.validation.allowed_extensions'))),
                    
                Forms\Components\TextInput::make('avatar_url')
                    ->label('Avatar URL (Alternative)')
                    ->url()
                    ->maxLength(255)
                    ->helperText('You can either upload a file above or provide a URL here'),
                    
                Forms\Components\Fieldset::make('Address')
                    ->schema([
                        Forms\Components\TextInput::make('address_line')
                            ->label('Address')
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('postal_code')
                            ->label('Postal Code')
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('country')
                            ->maxLength(255),
                    ]),
                    
                Forms\Components\Fieldset::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.0000001),
                            
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.0000001),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_display')
                    ->label('Avatar')
                    ->circular()
                    ->getStateUsing(fn ($record) => $record->getDisplayAvatarUrl())
                    ->defaultImageUrl(fn ($record) => $record->getDefaultImage()),
                    
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('role')
                    ->colors([
                        'primary' => 'owner',
                        'success' => 'sitter',
                        'warning' => 'both',
                        'danger' => 'admin',
                    ]),
                    
                Tables\Columns\IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'owner' => 'Owner',
                        'sitter' => 'Sitter',
                        'both' => 'Both',
                        'admin' => 'Admin',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_admin')
                    ->label('Admin Access'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
