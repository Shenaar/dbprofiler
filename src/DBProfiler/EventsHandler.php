<?php

namespace DBProfiler;

class EventsHandler {

    private $_profiler;

    public function __construct(\Illuminate\Contracts\Foundation\Application $app) {
        $this->_profiler = $app->make('DBProfiler');
    }

    public function onQuery($sql, $bindings, $time) {
        $this->_profiler->handle($sql, $bindings, $time);
    }

    public function onFinish() {
        $this->_profiler->onFinish();
    }

}
