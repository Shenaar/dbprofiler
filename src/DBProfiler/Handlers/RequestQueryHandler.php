<?php

namespace Shenaar\DBProfiler\Handlers;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Collection;

use Shenaar\DBProfiler\EventHandlerInterface;

/**
 * Logs amount and time of queries per request.
 */
class RequestQueryHandler implements EventHandlerInterface
{
    /**
     * @var int
     */
    private $queriesCount = 0;

    /**
     * @var int
     */
    private $totalTime = 0;

    /**
     * @var int
     */
    private $limit = 0;

    /**
     * @param ConfigRepository $config
     */
    public function __construct(ConfigRepository $config)
    {
        $this->limit = (int) $config->get('dbprofiler.request.limit', 0);
    }

    /**
     * @inheritdoc
     */
    public function handle(QueryExecuted $event)
    {
        $time = $event->time;

        ++$this->queriesCount;
        $this->totalTime += $time;
    }

    /**
     * @inheritdoc
     */
    public function onFinish()
    {
        if ($this->queriesCount < $this->limit) {
            return;
        }

        $filename = storage_path(
            '/logs/query.' . date('d.m.y') . '.request.log'
        );

        $string = '[' . date('H:i:s') . '] ' .
            $this->getUrl() . ': ' .
            $this->queriesCount . ' queries in ' .
            $this->totalTime . 'ms.' . PHP_EOL;

        \File::append($filename, $string);
    }

    /**
     * Returns URL or running artisan command.
     *
     * @return string
     */
    protected function getUrl()
    {
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return (new Collection(\Request::server('argv', [])))->implode(' ');
        }

        return \Request::fullUrl();
    }
}
