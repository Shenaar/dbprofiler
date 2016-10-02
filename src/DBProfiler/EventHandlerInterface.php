<?php

namespace Shenaar\DBProfiler;

use Illuminate\Database\Events\QueryExecuted;

interface EventHandlerInterface
{

    public function handle(QueryExecuted $event);

    public function onFinish();
}
