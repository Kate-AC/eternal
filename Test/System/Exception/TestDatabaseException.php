<?php

/**
 * DatabaseExceptionのテスト
 */

namespace Test\System\Exception;

use Test\TestHelper;
use System\Exception\DatabaseException;

class TestDatabaseException extends TestHelper
{
    /**
     * __construct
     */
    public function __constructTest()
    {
        $this->compareInstance('System\Exception\DatabaseException', new DatabaseException('hoge'));
    }
}
