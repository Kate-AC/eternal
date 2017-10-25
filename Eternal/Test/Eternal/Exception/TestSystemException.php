<?php

/**
 * SystemExceptionのテスト
 */

namespace Test\System\Exception;

use Test\Dummy;
use Test\Mock;
use Test\Parse;
use Test\TestHelper;
use System\Exception\SystemException;

class TestSystemException extends TestHelper
{
	/**
	 * __construct
	 */
	public function __constructTest()
	{
		$this->compareInstance('System\Exception\SystemException', new SystemException('hoge'));
	}
}
