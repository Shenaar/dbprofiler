<?php

namespace Shenaar\DBProfiler\Handlers;

use Illuminate\Config\Repository as ConfigRepository;
use Shenaar\DBProfiler\QueryFormatter as QueryFormatter;
use Shenaar\DBProfiler\EventHandlerInterface;
use Illuminate\Database\Events\QueryExecuted;

class AllQueryHandler implements EventHandlerInterface
{

    private $_queries = [];

    private $_defer = true;

    private $_formatter = null;

    private $_filename;

    public function __construct(ConfigRepository $config, QueryFormatter $formatter)
    {
        $this->_defer     = $config->get('dbprofiler.all.defer', true);
        $this->_formatter = $formatter;
        $this->_filename  = storage_path(
            '/logs/query.' . date('d.m.y') . '.all.log'
        );
    }

    public function handle(QueryExecuted $event)
    {
        $sql = $event->sql;
        $time = $event->time;
        $bindings = $event->bindings;

        $item = [
            'query' => $this->_formatter->format($sql, $bindings),
            'time'  => $time,
        ];

        if ($this->_defer) {
            $this->_queries[] = $item;
        } else {
            $this->_writeQuery($item);
        }
    }

    public function onFinish()
    {
        foreach ($this->_queries as $item) {
            $this->_writeQuery($item);
        }
    }

    private function _writeQuery($query)
    {
        $string = '[' . date('H:i:s') . '] ' .
            ' (' . $query['time'] . 'ms) ' .
            $query['query'] . PHP_EOL;

        \File::append($this->_filename, $string);
    }

}
