<?php

/**
 * AbstractExceptionのテスト
 */

namespace Test\System\Exception;

use Test\Dummy;
use Test\Mock;
use Test\Parse;
use Test\TestHelper;
use System\Exception\AbstractException;
use System\Exception\HogeException;

class TestAbstractException extends TestHelper
{
	/**
	 * __construct
	 */
	public function __constructTest()
	{
		$errorType     = 'エラー';
		$hogeException = new HogeException('hoge');
		$this->compareInstance('System\Exception\HogeException', $hogeException, 'コンストラクタ');
		$this->compareValue($errorType, $hogeException->getErrorType(), '継承先のエラータイプの取得');
	}
}

namespace System\Exception;

class HogeException extends AbstractException
{
	protected $errorType = 'エラー';

	public function __construct($message)
	{
		parent::__construct($message);
	}
}
