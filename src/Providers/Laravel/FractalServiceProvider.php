<?php
namespace Xaamin\Fractal\Providers\Laravel;

use Xaamin\Fractal\Fractal;
use League\Fractal\Manager;
use Illuminate\Support\ServiceProvider;
use Xaamin\Fractal\Serializer\ArraySerializer;

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
        $this->app->bind('Xaamin\Fractal\Fractal', function ($app) {
            return new Fractal(new Manager(), new ArraySerializer);
        });
    }
}