<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceRequestResource\Pages;
use App\Filament\Resources\ServiceRequestResource\RelationManagers;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Pet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceRequestResource extends Resource
{
    protected static ?string $model = ServiceRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationLabel = 'Service Requests';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('owner_id')
                    ->label('Owner')
                    ->options(User::where('role', 'owner')->orWhere('role', 'both')->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function (callable $set) {
                        $set('pet_id', null); // Reset pet when owner changes
                    }),
                    
                Forms\Components\Select::make('sitter_id')
                    ->label('Sitter')
                    ->options(User::where('role', 'sitter')->orWhere('role', 'both')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                    
                Forms\Components\Select::make('pet_id')
                    ->label('Pet')
                    ->options(function (callable $get) {
                        $ownerId = $get('owner_id');
                        if (!$ownerId) {
                            return [];
                        }
                        
                        return Pet::where('owner_id', $ownerId)
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->required()
                    ->searchable()
                    ->disabled(fn (callable $get) => !$get('owner_id'))
                    ->helperText('Select an owner first to see their pets'),
                    
                Forms\Components\DateTimePicker::make('date_from')
                    ->required(),
                    
                Forms\Components\DateTimePicker::make('date_to')
                    ->required(),
                    
                Forms\Components\Textarea::make('message')
                    ->maxLength(1000),
                    
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                    ])
                    ->required()
                    ->default('pending'),
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
                    
                Tables\Columns\TextColumn::make('sitter.name')
                    ->label('Sitter')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('pet.name')
                    ->label('Pet')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('date_from')
                    ->dateTime()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('date_to')
                    ->dateTime()
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'accepted',
                        'danger' => 'rejected',
                        'info' => 'completed',
                    ]),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
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
            'index' => Pages\ListServiceRequests::route('/'),
            'create' => Pages\CreateServiceRequest::route('/create'),
            'edit' => Pages\EditServiceRequest::route('/{record}/edit'),
        ];
    }
}
