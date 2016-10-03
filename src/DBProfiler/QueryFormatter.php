<?php

namespace Shenaar\DBProfiler;

use \Illuminate\Database\DatabaseManager as DatabaseManager;

class QueryFormatter
{

    private $_grammar = [];

    public function __construct(DatabaseManager $manager)
    {
        $this->_grammar = $manager->connection()->getQueryGrammar();
    }

    public function format($query, $bindings)
    {
        $res = $query;

        try {
            foreach ($bindings as $index => $value) {
                if (is_scalar($value)) {
                    $bindings[$index] = (string)$value;
                } else if ($value instanceof \DateTime) {
                    $bindings[$index] = $value->format(
                        $this->_grammar->getDateFormat()
                    );
                } else {
                    $bindings[$index] = '?';
                }
            }

            $res = str_replace('?', '"%s"', $query);
            $res = vsprintf($res, $bindings);
        } catch (Exception $e) {
        }

        return $res;
    }

}
