<?php

namespace Uzsoftic\LaravelTelegramBot\Providers;

use Illuminate\Support\ServiceProvider;

class TelegramProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the service provider.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function register()
    {
        $this->registerConfig();
    }

    /**
     * Register the config path.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function registerConfig()
    {
        $packageConfigFile = __DIR__.'/../config/telegram.php';
        $this->app->make('config')->set(
            'generators.config',
            $this->app->make('files')->getRequire($packageConfigFile)
        );
    }

}
