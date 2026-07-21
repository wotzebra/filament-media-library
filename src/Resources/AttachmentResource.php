<?php

namespace Codedor\MediaLibrary\Resources;

use Codedor\MediaLibrary\Facades\Formats;
use Codedor\MediaLibrary\Formats\Format;
use Codedor\MediaLibrary\Jobs\GenerateAttachmentFormat;
use Codedor\MediaLibrary\Models\Attachment;
use Codedor\MediaLibrary\Resources\AttachmentResource\Pages;
use Codedor\TranslatableTabs\Forms\TranslatableTabs;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Component;

class AttachmentResource extends Resource
{
    protected static ?string $model = Attachment::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';

    public static function getNavigationLabel(): string
    {
        return __('filament-media-library::attachment.dashboard navigation title');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TranslatableTabs::make()
                ->icon('heroicon-o-signal')
                ->columnSpan(['lg' => 2])
                ->defaultFields([
                    Grid::make(2)->schema([
                        Placeholder::make('name')
                            ->label(__('filament-media-library::admin.name'))
                            ->content(fn (Attachment $record) => $record->name),

                        Placeholder::make('created_at')
                            ->label(__('filament-media-library::admin.created at'))
                            ->content(fn (Attachment $record) => $record->created_at->format('Y-m-d H:i:s')),

                        Placeholder::make('extension')
                            ->label(__('filament-media-library::admin.extension'))
                            ->content(fn (Attachment $record) => $record->extension),

                        Placeholder::make('mime_type')
                            ->label(__('filament-media-library::admin.mime type'))
                            ->content(fn (Attachment $record) => $record->mime_type),

                        Placeholder::make('type')
                            ->label(__('filament-media-library::admin.type'))
                            ->content(fn (Attachment $record) => $record->type),

                        Placeholder::make('size')
                            ->label(__('filament-media-library::admin.size'))
                            ->content(fn (Attachment $record) => "{$record->formattedInMbSize} MB"),

                        Placeholder::make('width')
                            ->label(__('filament-media-library::admin.width'))
                            ->content(fn (Attachment $record) => $record->width)
                            ->hidden(fn (Attachment $record) => ! $record->isImage()),

                        Placeholder::make('height')
                            ->label(__('filament-media-library::admin.height'))
                            ->content(fn (Attachment $record) => $record->height)
                            ->hidden(fn (Attachment $record) => ! $record->isImage()),
                    ]),

                    Select::make('tags')
                        ->label(__('filament-media-library::admin.tags'))
                        ->relationship('tags', 'title')
                        ->preload()
                        ->multiple(),

                    Section::make('Preview')
                        ->schema([
                            Placeholder::make('image')
                                ->label(__('filament-media-library::admin.image'))
                                ->hidden(fn (Attachment $record) => ! $record->isImage())
                                ->content(fn (Attachment $record) => new HtmlString(
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
            ->actions([
                Tables\Actions\Action::make('format')
                    ->label(__('filament-media-library::admin.format'))
                    ->icon('heroicon-o-scissors')
                    ->hidden(fn (Attachment $record) => ! is_convertible_image($record->extension))
                    ->action(function (Tables\Actions\Action $action) {
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

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\BulkAction::make('generate-formats')
                    ->label(__('filament-media-library::admin.generate formats'))
                    ->icon('heroicon-o-scissors')
                    ->deselectRecordsAfterCompletion()
                    ->hidden(fn () => ! config('filament-media-library.enable-format-generate-action', false))
                    ->form([
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
                    ->action(function (Tables\Actions\BulkAction $action, array $data) {
                        $formats = Formats::mapToKebab()->when(
                            ! ($data['generate_all'] ?? false),
                            fn ($formats) => $formats->only($data['formats'] ?? [])
                        );

                        $action->getRecords()->each(function (Attachment $attachment) use ($formats, $data) {
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
