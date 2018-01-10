<?php

namespace Shenaar\DBProfiler;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Http\Events\RequestHandled;
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

            $this->app->terminating(function () use ($requestHandler) {
                $requestHandler->onFinish();
            });
        }

        if ($config->get('dbprofiler.all.enabled')) {
            $allHandler = new Handlers\AllQueryHandler(
                $config,
                $formatter
            );

            $events->listen(QueryExecuted::class, [$allHandler, 'handle']);

            $this->app->terminating(function () use ($allHandler) {
                $allHandler->onFinish();
            });
        }

        if ($config->get('dbprofiler.slow.enabled')) {
            $slowHandler = new Handlers\SlowQueryHandler(
                $config,
                $formatter
            );

            $events->listen(QueryExecuted::class, [$slowHandler, 'handle']);

            $this->app->terminating(function () use ($slowHandler) {
                $slowHandler->onFinish();
            });
        }
    }

}
