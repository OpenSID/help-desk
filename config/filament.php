<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Broadcasting
    |--------------------------------------------------------------------------
    |
    | By uncommenting the Laravel Echo configuration, you may connect Filament
    | to any Pusher-compatible websockets server.
    |
    | This will allow your users to receive real-time notifications.
    |
    */

    'broadcasting' => [

        // 'echo' => [
        //     'broadcaster' => 'pusher',
        //     'key' => env('VITE_PUSHER_APP_KEY'),
        //     'cluster' => env('VITE_PUSHER_APP_CLUSTER'),
        //     'wsHost' => env('VITE_PUSHER_HOST'),
        //     'wsPort' => env('VITE_PUSHER_PORT'),
        //     'wssPort' => env('VITE_PUSHER_PORT'),
        //     'authEndpoint' => '/broadcasting/auth',
        //     'disableStats' => true,
        //     'encrypted' => true,
        //     'forceTLS' => true,
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | This is the storage disk Filament will use to store files. You may use
    | any of the disks defined in the `config/filesystems.php`.
    |
    */

    'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Assets Path
    |--------------------------------------------------------------------------
    |
    | This is the directory where Filament's assets will be published to. It
    | is relative to the `public` directory of your Laravel application.
    |
    | After changing the path, you should run `php artisan filament:assets`.
    |
    */

    'assets_path' => null,

    /*
    |--------------------------------------------------------------------------
    | Cache Path
    |--------------------------------------------------------------------------
    |
    | This is the directory that Filament will use to store cache files that
    | are used to optimize the registration of components.
    |
    | After changing the path, you should run `php artisan filament:cache-components`.
    |
    */

    'cache_path' => base_path('bootstrap/cache/filament'),

    /*
    |--------------------------------------------------------------------------
    | Livewire Loading Delay
    |--------------------------------------------------------------------------
    |
    | This sets the delay before loading indicators appear.
    |
    | Setting this to 'none' makes indicators appear immediately, which can be
    | desirable for high-latency connections. Setting it to 'default' applies
    | Livewire's standard 200ms delay.
    |
    */

    'livewire_loading_delay' => 'default',

    /*
    |--------------------------------------------------------------------------
    | System Route Prefix
    |--------------------------------------------------------------------------
    |
    | This is the prefix used for the system routes that Filament registers,
    | such as the routes for downloading exports and failed import rows.
    |
    */

    'system_route_prefix' => 'filament',

    /*
    |--------------------------------------------------------------------------
    | Theme Colors (Buttons, Links, etc.)
    |--------------------------------------------------------------------------
    */
    'colors' => [
        'primary' => [
            '50' => '#eff6ff',
            '100' => '#dbeafe',
            '200' => '#bfdbfe',
            '300' => '#93c5fd',
            '400' => '#60a5fa',
            '500' => '#3b82f6',
            '600' => '#2563eb',
            '700' => '#1d4ed8',
            '800' => '#1e40af',
            '900' => '#1e3a8a',
        ],
        'secondary' => [
            '50' => '#f9fafb',
            '100' => '#f3f4f6',
            '200' => '#e5e7eb',
            '300' => '#d1d5db',
            '400' => '#9ca3af',
            '500' => '#6b7280',
            '600' => '#4b5563',
            '700' => '#374151',
            '800' => '#1f2937',
            '900' => '#111827',
        ],
        'success' => [
            '50' => '#ecfdf5',
            '100' => '#d1fae5',
            '200' => '#a7f3d0',
            '300' => '#6ee7b7',
            '400' => '#34d399',
            '500' => '#10b981',
            '600' => '#059669',
            '700' => '#047857',
            '800' => '#065f46',
            '900' => '#064e3b',
        ],
        'warning' => [
            '50' => '#fffbeb',
            '100' => '#fef3c7',
            '200' => '#fde68a',
            '300' => '#fcd34d',
            '400' => '#fbbf24',
            '500' => '#f59e0b',
            '600' => '#d97706',
            '700' => '#b45309',
            '800' => '#92400e',
            '900' => '#78350f',
        ],
        'danger' => [
            '50' => '#fef2f2',
            '100' => '#fee2e2',
            '200' => '#fecaca',
            '300' => '#fca5a5',
            '400' => '#f87171',
            '500' => '#ef4444',
            '600' => '#dc2626',
            '700' => '#b91c1c',
            '800' => '#991b1b',
            '900' => '#7f1d1d',
        ],
    ],

];
