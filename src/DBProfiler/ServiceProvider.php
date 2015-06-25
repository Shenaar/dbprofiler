<?php

namespace DBProfiler;

use Illuminate\Contracts\Events\Dispatcher;

class ServiceProvider extends \Illuminate\Support\ServiceProvider {

    public function register() {
        return $this->app->singleton('DBProfiler', function($app) {
            return new DBProfiler();
        });
    }

    public function provides() {
        return ['DBProfiler'];
    }

    public function boot(Dispatcher $events) {
        $events->listen('illuminate.query', '\DBProfiler\EventsHandler@onQuery');
        $events->listen('kernel.handled', '\DBProfiler\EventsHandler@onFinish');
    }

}
