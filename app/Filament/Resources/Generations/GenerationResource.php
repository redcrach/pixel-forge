<?php

namespace App\Filament\Resources\Generations;

use App\Actions\Pricing\FormatMoney;
use App\Enums\GenerationStatus;
use App\Filament\Resources\Generations\Pages\ManageGenerations;
use App\Models\Generation;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Read-only list of every user's generations, for operators.
 */
class GenerationResource extends Resource
{
    protected static ?string $model = Generation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $recordTitleAttribute = 'prompt';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Request')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.name')->label('User'),
                        TextEntry::make('user.email')->label('Email'),
                        TextEntry::make('prompt')->columnSpanFull(),
                        TextEntry::make('size'),
                        TextEntry::make('quality'),
                        TextEntry::make('reference_images')
                            ->label('Reference images')
                            ->listWithLineBreaks()
                            ->placeholder('None')
                            ->columnSpanFull(),
                    ]),
                Section::make('Result')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('status')->badge(),
                        TextEntry::make('pixelforge_id')->label('Pixelforge ID')->placeholder('—')->copyable(),
                        TextEntry::make('created_at')->label('Created (UTC)')->dateTime(),
                        TextEntry::make('completed_at')->label('Completed (UTC)')->dateTime()->placeholder('—'),
                        TextEntry::make('meta_data.error.message')->label('Error')->placeholder('—')->columnSpanFull(),
                    ]),
                Section::make('Usage & billing')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('input_text_tokens')->label('Input text')->numeric()->placeholder('—'),
                        TextEntry::make('input_image_tokens')->label('Input images')->numeric()->placeholder('—'),
                        TextEntry::make('output_tokens')->label('Output')->numeric()->placeholder('—'),
                        TextEntry::make('reasoning_tokens')->label('Reasoning')->numeric()->placeholder('—'),
                        TextEntry::make('provider_cost_nano')
                            ->label('Provider cost')
                            ->formatStateUsing(fn (int $state): string => self::formatMoney($state))
                            ->placeholder('—'),
                        TextEntry::make('final_price_nano')
                            ->label('User price')
                            ->formatStateUsing(fn (int $state): string => self::formatMoney($state))
                            ->placeholder('—'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('user'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Created (UTC)')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('prompt')
                    ->limit(60)
                    ->tooltip(fn (Generation $record): string => $record->prompt)
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('size'),
                TextColumn::make('quality'),
                TextColumn::make('provider_cost_nano')
                    ->label('Provider cost')
                    ->formatStateUsing(fn (int $state): string => self::formatMoney($state))
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('final_price_nano')
                    ->label('User price')
                    ->formatStateUsing(fn (int $state): string => self::formatMoney($state))
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('Completed (UTC)')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->multiple()
                    ->options(GenerationStatus::class),
                SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('created_at')
                    ->label('Created')
                    ->schema([
                        DatePicker::make('from')->label('Created from (UTC)'),
                        DatePicker::make('until')->label('Created until (UTC)'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageGenerations::route('/'),
        ];
    }

    private static function formatMoney(int $nano): string
    {
        return app(FormatMoney::class)($nano);
    }
}
