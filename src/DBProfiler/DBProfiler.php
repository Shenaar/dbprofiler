<?php

namespace DBProfiler;

class DBProfiler {

    private $_queriesCount = 0;
    private $_totalTime = 0;

    public function handle($sql, $bindings, $time) {

        ++$this->_queriesCount;
        $this->_totalTime += $time;

        try {
            $grammar = \DB::connection()->getQueryGrammar();
            foreach ($bindings as $index => $value) {
                if (is_scalar($value)) {
                    $bindings[$index] = (string)$value;
                } else if ($value instanceof \DateTime) {
                    $bindings[$index] = $value->format($grammar->getDateFormat());
                } else {
                    $bindings[$index] = '?';
                }
            }

            $res = str_replace('?', '"%s"', $sql);
            $res = vsprintf($res, $bindings);
        } catch (Exception $e) {
            $res = $sql;
        }

        /*$filename = storage_path() . '/logs/query.log';
        $this->_storeQuery($filename, $res, $time, false);*/

        if ($time > 500) {
            $filename = storage_path() . '/logs/slow.log';
            $this->_storeQuery($filename, $res, $time, false);
        }
    }

    public function onFinish() {
        $filename = storage_path() . '/logs/query.' . date('d.m.y') . '.log';
        \File::append($filename, '[' . date('H:i:s') . ']' . \Request::fullUrl() . ': ' . $this->_queriesCount . ' queries in ' . $this->_totalTime . 'ms.' . PHP_EOL);
    }

    private function _storeQuery($filename, $sql, $time, $backtrace = true) {
        $log = (new \DateTime)->format('Y-m-d H:i:s') . ' | ' . $time . 'ms | ' . $sql . PHP_EOL;

        \File::append($filename, str_repeat('-=', 30) . PHP_EOL);
        \File::append($filename, $log);
        \File::append($filename, \Request::url() . PHP_EOL);

        if ($backtrace) {
            $res = '';
            $backtrace = debug_backtrace();
            foreach ($backtrace as $entry) {
                $res .= (($res) ? PHP_EOL : '');

                if (isset($entry['class']) && isset($entry['function'])) {
                    $res .= $entry['class'] . '::' . $entry['function'] . (isset($entry['line']) ? ':' . $entry['line'] : '') . '(...)';
                } else if (isset($entry['function']) && isset($entry['line'])) {
                    $res .= $entry['function'] . '():' . $entry['line'];
                } else if (isset($entry['function']) && ($entry['function'] == '{closure}')) {
                    //..
                } else {
                    $res .= var_export($entry, TRUE);
                }
            }
            \File::append($filename, $res . PHP_EOL);
        }
    }
}
