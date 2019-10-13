<?php

/**
 * AbstractLoggerのテスト
 */

namespace Test\System\Log;

use Test\Mock;
use Test\TestHelper;

use Test\TestLogger;

class TestAbstractLogger extends TestHelper
{
    /**
     * write
     */
    public function writeTest()
    {
        $logger = new TestLogger();
        $this->compareValue(100, $logger->write('message', 'path'));
    }
}

namespace Test;

use System\Log\AbstractLogger;

class TestLogger extends AbstractLogger
{
    public function write($message, $path)
    {
        return 100;
    }
}
