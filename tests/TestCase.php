<?php

namespace Wotz\MediaLibrary\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use Spatie\Image\Manipulations;
use Spatie\Translatable\TranslatableServiceProvider;
use Wotz\MediaLibrary\Filament\MediaLibraryPlugin;
use Wotz\MediaLibrary\Providers\MediaLibraryServiceProvider;
use Wotz\TranslatableTabs\Providers\TranslatableTabsServiceProvider;

class TestCase extends Orchestra
{
    use InteractsWithViews;

    public Manipulations $manipulations;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $panel = new \Filament\Panel;
        $panel
            ->id('resource-test')
            ->default(true)
            ->plugin(MediaLibraryPlugin::make());

        \Filament\Facades\Filament::registerPanel($panel);
    }

    protected function getPackageProviders($app)
    {
        $providers = [
            LivewireServiceProvider::class,
            TranslatableTabsServiceProvider::class,
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            NotificationsServiceProvider::class,
            SchemasServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            MediaLibraryServiceProvider::class,
            TranslatableServiceProvider::class,
        ];

        sort($providers);

        return $providers;
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/2022_08_03_120355_create_attachments_table.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/2022_08_03_120356_create_attachment_tags_table.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/2022_08_03_120357_create_attachment_attachment_tags_table.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/2023_04_27_120359_create_attachment_formats.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/2025_01_30_130345_add_is_hidden_to_attachment_tags.php');
    }
}
