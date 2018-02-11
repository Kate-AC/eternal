<?php

/**
 * DiExceptionのテスト
 */

namespace Test\System\Exception;

use Test\Dummy;
use Test\Mock;
use Test\Parse;
use Test\TestHelper;
use System\Exception\DiException;

class TestDiException extends TestHelper
{
	/**
	 * __construct
	 */
	public function __constructTest()
	{
		$this->compareInstance('System\Exception\DiException', new DiException('hoge'));
	}
}
