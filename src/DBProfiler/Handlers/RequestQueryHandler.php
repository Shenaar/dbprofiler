<?php

namespace DBProfiler\Handlers;

use Illuminate\Config\Repository as ConfigRepository;

class RequestQueryHandler {

    private $_queriesCount = 0;

    private $_totalTime = 0;

    private $_limit = 0;

    public function __construct(ConfigRepository $config) {
        $this->_limit = $config->get('dbprofiler.request.limit', 0);
    }

    public function handle($sql, $bindings, $time) {
        ++$this->_queriesCount;
        $this->_totalTime += $time;
    }

    public function onFinish() {
        if ($this->_queriesCount < $this->_limit) {
            return;
        }

        $filename = storage_path('/logs/query.' . date('d.m.y') . '.request.log');

        $string = '[' . date('H:i:s') . '] ' .
            \Request::fullUrl() . ': ' .
            $this->_queriesCount . ' queries in ' .
            $this->_totalTime . 'ms.' . PHP_EOL;

        \File::append($filename, $string);
    }

}
