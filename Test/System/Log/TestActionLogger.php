<?php

/**
 * ActionLoggerのテスト
 */

namespace Test\System\Log;

use Test\Mock;
use Test\TestHelper;
use System\Log\ActionLogger;

class TestActionLogger extends TestHelper
{
	/**
	 * write
	 */
	public function writeTest()
	{
		$message      = 'LogTest';
		$path         = '/tmp/ActionLoggerTest';
		$actionLogger = new ActionLogger();

		$this->compareValue(null, $actionLogger->write($message, $path));
	}
}

