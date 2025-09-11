<?php

namespace ashisharya\ImageShapes;

use Illuminate\Support\ServiceProvider;

class ImageShapesServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ShapeGenerator::class, function () {
            return new ShapeGenerator();
        });
    }

    public function boot()
    {
        // Later: publish config, assets if needed
    }
}
