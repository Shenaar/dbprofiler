<?php

namespace Shenaar\DBProfiler;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Grammars\Grammar;

/**
 * Used for converting a query to a string.
 */
class QueryFormatter
{
    /**
     * @var Grammar
     */
    private $grammar = [];

    /**
     * @param DatabaseManager $manager
     */
    public function __construct(DatabaseManager $manager)
    {
        $this->grammar = $manager->connection()->getQueryGrammar();
    }

    /**
     * @param string $query
     * @param array  $bindings
     *
     * @return mixed|string
     */
    public function format($query, $bindings)
    {
        try {
            foreach ($bindings as $index => $value) {
                if (is_scalar($value)) {
                    $bindings[$index] = (string)$value;
                } else if ($value instanceof \DateTime) {
                    $bindings[$index] = $value->format(
                        $this->grammar->getDateFormat()
                    );
                } else {
                    $bindings[$index] = '?';
                }
            }

            $res = str_replace('?', '"%s"', $query);
            $res = vsprintf($res, $bindings);
        } catch (\Exception $e) {
            return $query;
        }

        return $res;
    }

}
