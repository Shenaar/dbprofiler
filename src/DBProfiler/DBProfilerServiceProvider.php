<?php

namespace Shenaar\DBProfiler;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\ServiceProvider;

/**
 * Registers handlers.
 */
class DBProfilerServiceProvider extends ServiceProvider
{
    /**
     *
     */
    public function register()
    {
        $this->app->singleton(
            QueryFormatter::class, function ($app) {
                return new QueryFormatter($app['db']);
            }
        );
    }

    /**
     * @return string
     */
    public function provides()
    {
        return QueryFormatter::class;
    }

    /**
     * @param Dispatcher $events
     * @param ConfigRepository $config
     * @param QueryFormatter $formatter
     */
    public function boot(Dispatcher $events, ConfigRepository $config, QueryFormatter $formatter)
    {
        $configPath = __DIR__ . '/../../config/dbprofiler.php';

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

            $events->listen(QueryExecuted::class, [$requestHandler, 'handle']);
            $events->listen('kernel.handled', [$requestHandler, 'onFinish']);
        }

        if ($config->get('dbprofiler.all.enabled')) {
            $allHandler = new Handlers\AllQueryHandler(
                $config,
                $formatter
            );

            $events->listen(QueryExecuted::class, [$allHandler, 'handle']);
            $events->listen('kernel.handled', [$allHandler, 'onFinish']);
        }

        if ($config->get('dbprofiler.slow.enabled')) {
            $slowHandler = new Handlers\SlowQueryHandler(
                $config,
                $formatter
            );

            $events->listen(QueryExecuted::class, [$slowHandler, 'handle']);
            $events->listen('kernel.handled', [$slowHandler, 'onFinish']);
        }
    }

}