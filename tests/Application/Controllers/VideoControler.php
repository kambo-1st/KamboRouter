<?php

namespace Kambo\Tests\Router\Application\Controllers;

/**
 * Testing video controler
 *
 * @package Kambo\Tests\Router\Application\Controllers
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license MIT
 */
class VideoControler
{
    /**
     * Testing action
     *
     * @param int $id Id of the video
     *
     * @return int return value
     */
    public function actionView(int $id) : int
    {
        return $id;
    }

    /**
     * Not found action
     *
     * @return string
     */
    public function actionNotFound() : string
    {
        return 'not found';
    }
}
