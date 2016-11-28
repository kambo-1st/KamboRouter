<?php
namespace Kambo\Router\Enum;

/**
 * Enum for route mode
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license Apache-2.0
 * @package Kambo\Router\Enum
 */
use Kambo\Router\Enum\Enum;

class RouteMode extends Enum
{
    const GET_FORMAT  = 'get';
    const PATH_FORMAT = 'path';
}
