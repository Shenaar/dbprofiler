<?php

namespace Shenaar\DBProfiler\Handlers;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Database\Events\QueryExecuted;

use Shenaar\DBProfiler\EventHandlerInterface;
use Shenaar\DBProfiler\QueryFormatter as QueryFormatter;

/**
 * Logs slow queries.
 */
class SlowQueryHandler implements EventHandlerInterface
{
    /**
     * @var array
     */
    private $queries = [];

    /**
     * @var bool
     */
    private $defer;

    /**
     * @var int
     */
    private $time;

    /**
     * @var QueryFormatter
     */
    private $formatter;

    /**
     * @var string
     */
    private $filename;

    /**
     * @param ConfigRepository $config
     * @param QueryFormatter $formatter
     */
    public function __construct(ConfigRepository $config, QueryFormatter $formatter) {

        $this->defer     = (bool) $config->get('dbprofiler.slow.defer', true);
        $this->formatter = $formatter;
        $this->filename  = storage_path(
            '/logs/query.' . date('d.m.y') . '.slow.log'
        );
        $this->time      = $config->get('dbprofiler.slow.time', 500);
    }

    /**
     * @inheritdoc
     */
    public function handle(QueryExecuted $event)
    {
        $sql = $event->sql;
        $time = $event->time;
        $bindings = $event->bindings;

        if ($time < $this->time) {
            return;
        }

        $item = [
            'query'     => $this->formatter->format($sql, $bindings),
            'time'      => $time,
            'backtrace' => $this->getBacktrace(),
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
            $query['query'] . PHP_EOL .
            $query['backtrace'] . PHP_EOL . str_repeat('=', 50)
            . PHP_EOL . PHP_EOL;

        \File::append($this->filename, $string);
    }

    /**
     * @return string
     */
    private function getBacktrace()
    {
        $res = '';
        $backtrace = debug_backtrace();
        array_splice($backtrace, 0, 10);
        array_splice($backtrace, 10);

        foreach ($backtrace as $item) {
            $class = array_get($item, 'class');
            $function = array_get($item, 'function');
            $file  = array_get($item, 'file');

            if (($file) && $function) {
                $res .= $res ? PHP_EOL : '';
                $res .= ($file ? : $class) . '::' . $function
                    . (isset($item['line']) ? ':' . $item['line'] : '');
            }
        }

        return $res;
    }
}
