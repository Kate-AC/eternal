<?php

/**
 * ControllerExceptionのテスト
 */

namespace Test\System\Exception;

use Test\TestHelper;
use System\Exception\ControllerException;

class TestControllerException extends TestHelper
{
    /**
     * __construct
     */
    public function __constructTest()
    {
        $this->compareInstance('System\Exception\ControllerException', new ControllerException('hoge'));
    }
}
