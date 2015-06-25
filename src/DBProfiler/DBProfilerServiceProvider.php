<?php

namespace DBProfiler;

use Illuminate\Support\ServiceProvider;

class DBProfilerServiceProvider extends ServiceProvider {

    public function register() {
        return $this->app->singleton('DBProfiler', function($app) {
            return new DBProfiler();
        });
    }

    public function provides() {
        return ['DBProfiler'];
    }

    public function boot() {
        \Event::listen('illuminate.query', '\DBProfiler\QueryLogger@onQuery');
        \Event::listen('kernel.handled', '\DBProfiler\QueryLogger@onFinish');
    }

}
