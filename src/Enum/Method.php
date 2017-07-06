<?php

namespace Kambo\Router\Enum;

use Kambo\Enum\Enum;

/**
 * Enum for HTTP methods
 *
 * @package Kambo\Router\Enum
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class Method extends Enum
{
    const GET    = 'GET';
    const POST   = 'POST';
    const DELETE = 'DELETE';
    const PUT    = 'PUT';
    const PATCH  = 'PATCH';
    const ANY    = 'ANY';
}
