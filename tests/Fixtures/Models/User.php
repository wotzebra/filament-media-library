<?php

namespace Wotz\MediaLibrary\Tests\Fixtures\Models;

use Wotz\MediaLibrary\Tests\Fixtures\Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function canAccessFilament(): bool
    {
        return true;
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
