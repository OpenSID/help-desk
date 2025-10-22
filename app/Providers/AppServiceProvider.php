<?php

namespace App\Providers;

use App\Settings\GeneralSettings;
use Filament\Facades\Filament;
use Filament\Assets\FilamentAsset;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentIcon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    // public function boot(): void
    // {
    //     // Configure application settings
    //     $this->configureApp();

    //     // Register Filament theme, meta, and assets
    //     Filament::serving(function () {
    //         // Theme (SCSS compiled via Vite)
    //         Filament::registerTheme(
    //             app(Vite::class)('resources/css/filament.scss')
    //         );

    //         // Favicon / meta
    //         Filament::pushMeta([
    //             new HtmlString('<link rel="icon" type="image/x-icon" href="' . config('app.logo') . '">'),
    //         ]);

    //         // Custom CSS / JS via FilamentAsset
    //         FilamentAsset::registerStyles([
    //             'https://unpkg.com/tippy.js@6/dist/tippy.css',
    //         ]);

    //         FilamentAsset::registerScripts([
    //             app(Vite::class)('resources/js/filament.js'),
    //         ]);
    //     });

    //     // Register navigation groups


    //     // Force HTTPS if enabled
    //     if (env('APP_FORCE_HTTPS', false)) {
    //         URL::forceScheme('https');
    //     }
    // }

    public function boot()
    {
        // Configure general app settings (locale, app name) — aman di CLI
        $this->configureApp();

        Filament::serving(function () {
            // Theme
            Filament::registerTheme(app(Vite::class)('resources/css/filament.scss'));

            // Scripts
            try {
                Filament::registerScripts([
                    app(Vite::class)('resources/js/filament.js'),
                ]);
            } catch (\Exception $e) {
                // Manifest belum dibangun, aman di ignore
            }

            // Meta / favicon
            // Filament::pushMeta([
            //     new HtmlString('<link rel="icon" type="image/x-icon" href="' . config('app.logo') . '">'),
            // ]);

            // Navigation groups
            $defaultPanel = Filament::getPanel();
            $defaultPanel?->navigationGroups([
                __('Management'),
                __('Referential'),
                __('Security'),
                __('Settings'),
            ]);
        });

        FilamentIcon::register([
            'heroicon-o-ban' => 'heroicon-o-x-circle', // fallback pengganti "ban"
            'lucide-ban' => 'heroicon-o-x-circle',      // kalau ada versi lucide
            'default' => 'heroicon-o-circle',           // default icon aman
        ]);
    }


    /**
     * Configure app settings based on GeneralSettings
     */
    private function configureApp(): void
    {
        try {
            $settings = app(GeneralSettings::class);

            Config::set('app.locale', $settings->site_language ?? config('app.fallback_locale'));
            Config::set('app.name', $settings->site_name ?? env('APP_NAME'));
            Config::set('filament.brand', $settings->site_name ?? env('APP_NAME'));
            Config::set(
                'app.logo',
                $settings->site_logo ? asset('storage/' . $settings->site_logo) : asset('favicon.ico')
            );

            Config::set('system.login_form.is_enabled', $settings->enable_login_form ?? false);
            Config::set('filament-socialite.enabled', $settings->enable_social_login ?? false);
            Config::set('services.oidc.is_enabled', $settings->enable_oidc_login ?? false);

        } catch (QueryException $e) {
            // Database belum siap, skip
        }
    }
}
