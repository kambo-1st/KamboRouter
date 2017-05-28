<?php
namespace Kambo\Router\Enum;

use Kambo\Enum\Enum;

/**
 * Enum for route mode
 *
 * @package Kambo\Router\Enum
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class RouteMode extends Enum
{
    const GET_FORMAT  = 'get';
    const PATH_FORMAT = 'path';
}
