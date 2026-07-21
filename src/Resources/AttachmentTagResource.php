<?php

namespace Wotz\MediaLibrary\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Wotz\MediaLibrary\Models\AttachmentTag;
use Wotz\MediaLibrary\Resources\AttachmentTagResource\Pages\ManageAttachmentTags;

class AttachmentTagResource extends Resource
{
    protected static ?string $model = AttachmentTag::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label(__('filament-media-library::admin.title'))
                ->required(),

            Select::make('parent')
                ->label(__('filament-media-library::admin.parent'))
                ->relationship('parent', 'title'),

            Toggle::make('is_hidden')
                ->label(__('filament-media-library::admin.is hidden'))
                ->helperText(__('filament-media-library::admin.is hidden help text'))
                ->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('filament-media-library::admin.title')),

                Tables\Columns\TextColumn::make('parent.title')
                    ->label(__('filament-media-library::admin.parent')),

                Tables\Columns\IconColumn::make('is_hidden')
                    ->label(__('filament-media-library::admin.is hidden'))
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAttachmentTags::route('/'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-media-library::admin.attachment tags title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-media-library::admin.attachment tags title');
    }

    public static function getModelLabel(): string
    {
        return __('filament-media-library::admin.attachment tags title singular');
    }
}
