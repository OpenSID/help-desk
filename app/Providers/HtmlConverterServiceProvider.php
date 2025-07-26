<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use League\HTMLToMarkdown\HtmlConverter;

class HtmlConverterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register HtmlConverter sebagai singleton
        $this->app->singleton(HtmlConverter::class, function ($app) {
            $converter = new HtmlConverter([
                'use_experimental_html_parser' => true,
            ]);
            return $converter;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
