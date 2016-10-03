<?php

return [

    /*
     * Включен ли профайлер. Выключение имеет БОЛЕЕ ВЫСОКИЙ приоритет, чем идущие ниже настройки
     * enabled (boolean) - включен или нет
     */
    'enabled' => true,

    /*
     * Записывает для каждого запроса количество и общее время запросов
     * enabled (boolean) - включен или нет
     * limit (integer) - минимальное количество запросов для внесения в лог. По умолчанию - 0
     */
    'request' => [
        'enabled' => true,
        'limit'   => 0,
    ],

    /*
     * Записывает медленные запросы
     * enabled (boolean) - включен или нет
     * time (integer) - минимальное время в МС для включения в медленные. По умолчанию - 500мс
     * defer (boolean)   - использовать ли отложенную запись: запросы записываются один раз, в конце работы приложения
     */
    'slow' => [
        'enabled' => true,
        'time'    => 500,
        'defer'   => true,
    ],

    /*
     * Записывает все запросы
     * enabled (boolean) - включен или нет
     * defer (boolean)   - использовать ли отложенную запись: запросы записываются один раз, в конце работы приложения
     */
    'all' => [
        'enabled' => false,
        'defer'   => true,
    ]

];
