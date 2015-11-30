<?php

namespace DBProfiler;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Config\Repository as ConfigRepository;

class ServiceProvider extends \Illuminate\Support\ServiceProvider {

    public function register() {
        return $this->app->singleton('QueryFormatter', function($app) {
            return new QueryFormatter($app['db']);
        });
    }

    public function provides() {
        return ['QueryFormatter'];
    }

    public function boot(Dispatcher $events, ConfigRepository $config) {
        $configPath = __DIR__ . '/../config/dbprofiler.php';

        if (function_exists('config_path')) {
            $publishPath = config_path('dbprofiler.php');
        } else {
            $publishPath = base_path('config/dbprofiler.php');
        }

        $this->publishes([$configPath => $publishPath], 'config');

        if (!$config->get('dbprofiler.enabled')) {
            return;
        }

        if ($config->get('dbprofiler.request.enabled')) {
            $requestHandler = new Handlers\RequestQueryHandler($config);
            $events->listen('illuminate.query', [$requestHandler, 'handle']);
            $events->listen('kernel.handled', [$requestHandler, 'onFinish']);
        }

        if ($config->get('dbprofiler.all.enabled')) {
            $allHandler = new Handlers\AllQueryHandler($config, app('QueryFormatter'));
            $events->listen('illuminate.query', [$allHandler, 'handle']);
            $events->listen('kernel.handled', [$allHandler, 'onFinish']);
        }

        if ($config->get('dbprofiler.slow.enabled')) {
            $slowHandler = new Handlers\SlowQueryHandler($config, app('QueryFormatter'));
            $events->listen('illuminate.query', [$slowHandler, 'handle']);
            $events->listen('kernel.handled', [$slowHandler, 'onFinish']);
        }
    }

}
