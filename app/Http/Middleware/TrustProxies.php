<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Скільки проксі довіряти (’*’ — усі).
     *
     * @var array|string|null
     */
    protected $proxies = '*';

    /**
     * Які заголовки використовувати для виявлення реального протоколу/IP.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;
}
