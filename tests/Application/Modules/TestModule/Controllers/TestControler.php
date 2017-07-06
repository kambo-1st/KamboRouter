<?php

namespace Kambo\Tests\Router\Application\Modules\TestModule\Controllers;

/**
 * Testing controler
 *
 * @package Kambo\Tests\Router\Application\Modules\TestModule\Controllers
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class TestControler
{
    /**
     * Testing action
     *
     * @param int $id Id of the item
     *
     * @return int return value
     */
    public function actionview(int $id) : int
    {
        return $id;
    }
}
