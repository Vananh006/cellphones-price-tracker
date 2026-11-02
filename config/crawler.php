<?php

return [
    'timeout' => env('CRAWLER_TIMEOUT', 25),
    'retry_times' => env('CRAWLER_RETRY_TIMES', 2),
    'retry_sleep_ms' => env('CRAWLER_RETRY_SLEEP_MS', 600),
    'request_delay_ms' => env('CRAWLER_REQUEST_DELAY_MS', 800),
    'verify_ssl' => env('CRAWLER_VERIFY_SSL', false),
];
