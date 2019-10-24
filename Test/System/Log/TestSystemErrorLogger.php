<?php

/**
 * SystemErrorLoggerのテスト
 */

namespace Test\System\Log;

use Test\TestHelper;
use System\Log\SystemErrorLogger;

class TestSystemErrorLogger extends TestHelper
{
    /**
     * write
     */
    public function writeTest()
    {
        $message = 'LogTest';
        $path    = '/tmp/SystemErrorLoggerTest';
        $logger  = new SystemErrorLogger();

        $this->compareValue(null, $logger->write($message, $path));
    }
}

namespace System\Log;

/**
 * error_logのオーバーライド
 */
function error_log($message, $type, $path)
{
    return null;
}
