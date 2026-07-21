<?php

namespace Wotz\MediaLibrary\Tables\Columns;

use Closure;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Concerns;
use Illuminate\Support\Collection;
use Wotz\MediaLibrary\Models\Attachment;

class AttachmentColumn extends Column
{
    use Concerns\HasDescription;

    protected string $view = 'filament-media-library::components.columns.attachments-column';

    public int|Closure $limit = 3;

    public function getState(): mixed
    {
        $state = parent::getState();

        if (is_string($state)) {
            $state = Attachment::find($state);
        }

        $state = Collection::wrap($state)->pluck('id');

        if ($state->isEmpty()) {
            return $state;
        }

        // Fetch the items again, otherwise we'll not have access to all our data
        return Attachment::whereIn('id', $state)
            ->orderByRaw('FIELD(id, ' . $state->map(fn ($id) => "'{$id}'")->implode(',') . ')')
            ->get();
    }

    public function limit(int|Closure $limit): self
    {
        $this->limit = ($limit === -1 ? PHP_INT_MAX : $limit);

        return $this;
    }

    public function getLimit(): int
    {
        return $this->evaluate($this->limit);
    }
}
