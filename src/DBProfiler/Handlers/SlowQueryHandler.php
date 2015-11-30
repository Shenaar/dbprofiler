<?php

namespace DBProfiler\Handlers;

use Illuminate\Config\Repository as ConfigRepository;
use DBProfiler\QueryFormatter as QueryFormatter;

class SlowQueryHandler {

    private $_queries = [];

    private $_defer = true;

    private $_time;

    private $_formatter = null;

    private $_filename;

    public function __construct(ConfigRepository $config, QueryFormatter $formatter) {
        $this->_defer     = $config->get('dbprofiler.slow.defer', true);
        $this->_formatter = $formatter;
        $this->_filename  = storage_path('/logs/query.' . date('d.m.y') . '.slow.log');
        $this->_time      = $config->get('dbprofiler.slow.time', 500);
    }

    public function handle($sql, $bindings, $time) {
        if ($time < $this->_time) {
            return;
        }

        $item = [
            'query'     => $this->_formatter->format($sql, $bindings),
            'time'      => $time,
            'backtrace' => $this->_getBacktrace(),
        ];

        if ($this->_defer) {
            $this->_queries[] = $item;
        } else {
            $this->_writeQuery($item);
        }
    }

    public function onFinish() {
        foreach ($this->_queries as $item) {
            $this->_writeQuery($item);
        }
    }

    private function _writeQuery($query) {
        $string = '[' . date('H:i:s') . '] ' .
            ' (' . $query['time'] . 'ms) ' .
            $query['query'] . PHP_EOL .
            $query['backtrace'] . PHP_EOL . str_repeat('=', 50) . PHP_EOL . PHP_EOL;

        \File::append($this->_filename, $string);
    }

    private function _getBacktrace() {
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
                $res .= ($file ? : $class) . '::' . $function . (isset($item['line']) ? ':' . $item['line'] : '');
            }
        }

        return $res;
    }
}
