<?php

namespace Shenaar\DBProfiler;

use Illuminate\Database\Events\QueryExecuted;

/**
 * Basic interface for events handling.
 */
interface EventHandlerInterface
{
    /**
     * Handles a query event.
     *
     * @param QueryExecuted $event
     *
     * @return mixed
     */
    public function handle(QueryExecuted $event);

    /**
     * Called when the application finishes it's work.
     *
     * @return mixed
     */
    public function onFinish();
}
