<?php

namespace Kambo\Router\Enum;

/**
 * XXX
 *
 * @author   Bohuslav Simek <bohuslav@simek.si>
 * @version  GIT $Id$
 * @license  Apache-2.0
 * @category Enum
 * @package  Router
 * 
 */

use Kambo\Router\Enum\Enum;

class Methods extends Enum
{
    CONST GET    = 'GET';
    CONST POST   = 'POST';
    CONST DELETE = 'DELETE';
    CONST PUT    = 'PUT';
    CONST PATCH  = 'PATCH';
    CONST ANY    = 'ANY';
}