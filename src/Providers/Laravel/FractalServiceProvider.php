<?php
namespace Xaamin\Fractal\Providers\Laravel;

use Xaamin\Fractal\Transformer;
use Illuminate\Support\ServiceProvider;

class FractalServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Xaamin\Fractal\Transformer', function ($app) {
            return new Transformer;
        });
    }
}