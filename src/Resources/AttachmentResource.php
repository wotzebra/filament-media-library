<?php

namespace Wotz\MediaLibrary\Resources;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Component;
use Wotz\MediaLibrary\Facades\Formats;
use Wotz\MediaLibrary\Formats\Format;
use Wotz\MediaLibrary\Jobs\GenerateAttachmentFormat;
use Wotz\MediaLibrary\Models\Attachment;
use Wotz\MediaLibrary\Resources\AttachmentResource\Pages;
use Wotz\TranslatableTabs\Forms\TranslatableTabs;

class AttachmentResource extends Resource
{
    protected static ?string $model = Attachment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-paper-clip';

    public static function getNavigationLabel(): string
    {
        return __('filament-media-library::attachment.dashboard navigation title');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TranslatableTabs::make()
                ->icon('heroicon-o-signal')
                ->columnSpan(['lg' => 2])
                ->defaultFields([
                    Grid::make(2)->schema([
                        TextEntry::make('name')
                            ->label(__('filament-media-library::admin.name'))
                            ->state(fn (Attachment $record) => $record->name),

                        TextEntry::make('created_at')
                            ->label(__('filament-media-library::admin.created at'))
                            ->state(fn (Attachment $record) => $record->created_at->format('Y-m-d H:i:s')),

                        TextEntry::make('extension')
                            ->label(__('filament-media-library::admin.extension'))
                            ->state(fn (Attachment $record) => $record->extension),

                        TextEntry::make('mime_type')
                            ->label(__('filament-media-library::admin.mime type'))
                            ->state(fn (Attachment $record) => $record->mime_type),

                        TextEntry::make('type')
                            ->label(__('filament-media-library::admin.type'))
                            ->state(fn (Attachment $record) => $record->type),

                        TextEntry::make('size')
                            ->label(__('filament-media-library::admin.size'))
                            ->state(fn (Attachment $record) => "{$record->formattedInMbSize} MB"),

                        TextEntry::make('width')
                            ->label(__('filament-media-library::admin.width'))
                            ->state(fn (Attachment $record) => $record->width)
                            ->hidden(fn (Attachment $record) => ! $record->isImage()),

                        TextEntry::make('height')
                            ->label(__('filament-media-library::admin.height'))
                            ->state(fn (Attachment $record) => $record->height)
                            ->hidden(fn (Attachment $record) => ! $record->isImage()),
                    ]),

                    Select::make('tags')
                        ->label(__('filament-media-library::admin.tags'))
                        ->relationship('tags', 'title')
                        ->preload()
                        ->multiple(),

                    Section::make('Preview')
                        ->schema([
                            TextEntry::make('image')
                                ->label(__('filament-media-library::admin.image'))
                                ->hidden(fn (Attachment $record) => ! $record->isImage())
                                ->state(fn (Attachment $record) => new HtmlString(
                                    "<a href=\"$record->url\" target=\"_blank\"><img src=\"{$record->url}\" /></a>"
                                )),
                        ]),
                ])
                ->translatableFields(fn () => [
                    // TextInput::make('translated_name'),
                    TextInput::make('alt')
                        ->label(__('filament-media-library::admin.alt text')),
                    TextInput::make('caption')
                        ->label(__('filament-media-library::admin.caption')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->contentGrid([
                'md' => 2,
                'xl' => 4,
            ])
            ->columns([
                Tables\Columns\Layout\Grid::make([
                    'lg' => 2,
                ])->schema([]),

                TextColumn::make('name')
                    ->label(__('filament-media-library::admin.name'))
                    ->sortable()
                    ->searchable(query: function ($query, string $search) {
                        return $query->search($search);
                    })
                    ->getStateUsing(fn (Attachment $record) => new HtmlString(
                        '<strong>' . Str::limit($record->name, 20) . '</strong>',
                    )),

                TextColumn::make('tags')
                    ->label(__('filament-media-library::admin.tags'))
                    ->searchable(query: function ($query, string $search) {
                        return $query->whereHas('tags', fn ($query) => $query->where('title', 'like', "%$search%"));
                    })
                    ->sortable()
                    ->getStateUsing(fn (Attachment $record) => $record->tags->implode('title', ', ')),

                TextColumn::make('image')
                    ->label(__('filament-media-library::admin.image'))
                    ->view('filament-media-library::components.attachment-list'),
            ])
            ->filters([
                Filters\SelectFilter::make('disk')
                    ->label(__('filament-media-library::admin.disk'))
                    ->options(fn () => Attachment::select('disk')
                        ->groupBy('disk')
                        ->orderBy('disk')
                        ->pluck('disk')
                        ->mapWithKeys(fn (string $disk) => [$disk => Str::headline($disk)])
                    )
                    ->multiple(),

                Filters\SelectFilter::make('type')
                    ->label(__('filament-media-library::admin.type'))
                    ->options(fn () => Attachment::select('type')
                        ->groupBy('type')
                        ->orderBy('type')
                        ->pluck('type')
                        ->mapWithKeys(fn ($type) => [$type => Str::headline($type)])
                    )
                    ->multiple(),

                Filters\SelectFilter::make('tags')
                    ->label(__('filament-media-library::admin.tags'))
                    ->relationship('tags', 'title')
                    ->preload()
                    ->multiple(),

                Filters\SelectFilter::make('mime_type')
                    ->label(__('filament-media-library::admin.mime type'))
                    ->options(fn () => Attachment::select('mime_type')
                        ->groupBy('mime_type')
                        ->orderBy('mime_type')
                        ->pluck('mime_type')
                        ->mapWithKeys(fn ($mime) => [$mime => $mime])
                    )
                    ->multiple(),
            ])
            ->recordActions([
                Action::make('format')
                    ->label(__('filament-media-library::admin.format'))
                    ->icon('heroicon-o-scissors')
                    ->hidden(fn (Attachment $record) => ! is_convertible_image($record->extension))
                    ->action(function (Action $action) {
                        /** @var Component&Tables\Contracts\HasTable $livewire */
                        $livewire = $action->getTable()->getLivewire();

                        $livewire->dispatch(
                            'filament-media-library::open-formatter-attachment-modal',
                            $action->getRecord()->id
                        );

                        $livewire->dispatch(
                            'open-modal',
                            id: 'filament-media-library::formatter-attachment-modal',
                        );
                    }),

                EditAction::make(),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
                BulkAction::make('generate-formats')
                    ->label(__('filament-media-library::admin.generate formats'))
                    ->icon('heroicon-o-scissors')
                    ->deselectRecordsAfterCompletion()
                    ->hidden(fn () => ! config('filament-media-library.enable-format-generate-action', false))
                    ->schema([
                        Checkbox::make('generate_all')
                            ->label(__('filament-media-library::formatter.generate all'))
                            ->helperText('This will generate all formats but will take longer.')
                            ->default(true)
                            ->reactive(),

                        Select::make('formats')
                            ->label(__('filament-media-library::formatter.formats to generate'))
                            ->hidden(fn (Get $get) => $get('generate_all'))
                            ->multiple()
                            ->options(fn () => Formats::mapToKebab()->mapWithKeys(fn (Format $format, $key) => [
                                $key => $format->name(),
                            ])),

                        Checkbox::make('force')
                            ->label(__('filament-media-library::formatter.force generate'))
                            ->helperText(__('filament-media-library::formatter.force generate help'))
                            ->default(true),
                    ])
                    ->action(function (BulkAction $action, array $data, Collection $selectedRecords) {
                        $formats = Formats::mapToKebab()->when(
                            ! ($data['generate_all'] ?? false),
                            fn ($formats) => $formats->only($data['formats'] ?? [])
                        );

                        $selectedRecords->each(function (Attachment $attachment) use ($formats, $data) {
                            $formats->each(fn (Format $format) => dispatch(new GenerateAttachmentFormat(
                                attachment: $attachment,
                                format: $format,
                                force: ($data['force'] ?? false),
                            )));
                        });

                        Notification::make()
                            ->title(__('filament-media-library::formatter.queue started'))
                            ->success()
                            ->send();
                    }),
            ])
            ->paginated([12, 24, 48, 96]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttachments::route('/'),
            'edit' => Pages\EditAttachment::route('/{record}/edit'),
        ];
    }

    public static function resourcePickerQuery(Builder $query, ?string $search = null): Builder
    {
        return $query
            ->search($search)
            ->whereDisk('public')
            ->when(empty($search), fn ($q) => $q->whereDoesntHave(
                'tags',
                fn ($q) => $q->where('is_hidden', true)
            ));
    }
}
