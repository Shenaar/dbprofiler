<?php

namespace Shenaar\DBProfiler\Handlers;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Database\Events\QueryExecuted;

use Shenaar\DBProfiler\EventHandlerInterface;
use Shenaar\DBProfiler\QueryFormatter as QueryFormatter;

/**
 * Logs all executed queries.
 */
class AllQueryHandler implements EventHandlerInterface
{
    /**
     * @var array
     */
    private $queries;

    /**
     * @var bool|mixed
     */
    private $defer;

    /**
     * @var QueryFormatter
     */
    private $formatter;

    /**
     * @var
     */
    private $filename;

    public function __construct(ConfigRepository $config, QueryFormatter $formatter)
    {
        $this->defer     = $config->get('dbprofiler.all.defer', true);
        $this->formatter = $formatter;
        $this->queries   = [];
        $this->filename  = storage_path(
            '/logs/query.' . date('d.m.y') . '.all.log'
        );
    }

    /**
     * @inheritdoc
     */
    public function handle(QueryExecuted $event)
    {
        $sql = $event->sql;
        $time = $event->time;
        $bindings = $event->bindings;

        $item = [
            'query' => $this->formatter->format($sql, $bindings),
            'time'  => $time,
        ];

        if ($this->defer) {
            $this->queries[] = $item;
        } else {
            $this->writeQuery($item);
        }
    }

    /**
     * @inheritdoc
     */
    public function onFinish()
    {
        foreach ($this->queries as $item) {
            $this->writeQuery($item);
        }
    }

    /**
     * @param array $query
     */
    private function writeQuery($query)
    {
        $string = '[' . date('H:i:s') . '] ' .
            ' (' . $query['time'] . 'ms) ' .
            $query['query'] . PHP_EOL;

        \File::append($this->filename, $string);
    }

}
