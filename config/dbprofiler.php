<?php

return [

    /*
     * Включен ли профайлер. Выключение имеет БОЛЕЕ ВЫСОКИЙ приоритет, чем идущие ниже настройки.
     * enabled (boolean) - включен или нет
     */
    'enabled' => true,

    /*
     * Записывает для каждого запроса количество и общее время запросов
     * enabled (boolean) - включен или нет
     */
    'request' => [
        'enabled' => true,
    ],

    /*
     * Записывает медленные запросы
     * enabled (boolean) - включен или нет
     * time (integer) - минимальное время в МС для включения в медленные. По умолчанию - 500мс
     *
     */
    'slow' => [
        'enabled' => true,
        'time'    => 500,
    ],

    /*
     * Записывает все запросы
     * enabled (boolean) - включен или нет
     */

    'all' => [
        'enabled' => true,
    ]

];