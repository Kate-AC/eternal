<?php

/**
 * IncorrectTypeExceptionのテスト
 */

namespace Test\System\Exception;

use Test\TestHelper;
use System\Exception\IncorrectTypeException;

class TestIncorrectTypeException extends TestHelper
{
    /**
     * __construct
     */
    public function __constructTest()
    {
        $this->compareInstance('System\Exception\IncorrectTypeException', new IncorrectTypeException('hoge'));
    }
}
