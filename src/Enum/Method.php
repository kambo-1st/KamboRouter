<?php

namespace Kambo\Router\Enum;

/**
 * Enum for HTTP methods
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license Apache-2.0
 * @package Kambo\Router\Enum
 */
use Kambo\Router\Enum\Enum;

class Method extends Enum
{
    const GET    = 'GET';
    const POST   = 'POST';
    const DELETE = 'DELETE';
    const PUT    = 'PUT';
    const PATCH  = 'PATCH';
    const ANY    = 'ANY';
}
