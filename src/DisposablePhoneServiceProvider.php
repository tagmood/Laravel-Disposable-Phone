<?php

namespace Tagmood\LaravelDisposablePhone;

use Illuminate\Support\ServiceProvider;
use Tagmood\LaravelDisposablePhone\Console\UpdateDisposableNumbersCommand;
use Tagmood\LaravelDisposablePhone\Validation\Indisposable;

class DisposablePhoneServiceProvider extends ServiceProvider
{
    /**
     * The config source path.
     *
     * @var string
     */
    protected $config = __DIR__.'/../config/disposable-phone.php';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands(UpdateDisposableNumbersCommand::class);
        }

        $this->publishes([
            $this->config => config_path('disposable-phone.php'),
        ], 'laravel-disposable-phone');

        $this->app['validator']->extend('indisposable', Indisposable::class.'@validate', Indisposable::$errorMessage);
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->config, 'disposable-phone');

        $this->app->singleton('disposable_email.domains', function ($app) {
            // Only build and pass the requested cache store if caching is enabled.
            if ($app['config']['disposable-phone.cache.enabled']) {
                $store = $app['config']['disposable-phone.cache.store'];
                $cache = $app['cache']->store($store == 'default' ? $app['config']['cache.default'] : $store);
            }

            $instance = new DisposableNumbers($cache ?? null);

            $instance->setStoragePath($app['config']['disposable-phone.storage']);
            $instance->setCacheKey($app['config']['disposable-phone.cache.key']);

            return $instance->bootstrap();
        });

        $this->app->alias('disposable_email.domains', DisposableNumbers::class);
    }
}