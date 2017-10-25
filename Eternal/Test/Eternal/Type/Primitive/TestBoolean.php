<?php

/**
 * Booleanのテスト
 */

namespace Test\System\Type\Primitive;

use System\Type\Primitive\Int;
use System\Type\Primitive\Boolean;
use System\Type\Primitive\String;
use System\Type\Primitive\Float;
use System\Exception\IncorrectTypeException;
use Test\Mock;
use Test\TestHelper;

class TestBoolean extends TestHelper
{
	/**
	 * check
	 * format
	 */
	public function checkAndFormatTest()
	{
		$value   = true;
		$boolean = new Boolean($value);
		$this->compareValue($value, $boolean->getValue(), '真偽値を渡した場合');

		$value   = null;
		$boolean = new Boolean($value);
		$this->compareValue($value, $boolean->getValue(), 'nullの場合を渡した場合');

		$value   = false;
		$boolean = new Boolean(new Boolean($value));
		$this->compareValue($value, $boolean->getValue(), 'Boolean型を渡した場合');

		try {
			new Boolean(new \stdClass());
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('Boolean型ではないオブジェクトが渡された', $e, 'Boolean型ではないオブジェクトを渡した場合');
		}

		try {
			new Boolean([true]);
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('配列が渡された', $e, '配列を渡した場合');
		}

		try {
			new Boolean('hoge');
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('Booleanではない値が渡された', $e, 'Booleanではない値を渡した場合');
		}
	}

	/**
	 * toInt
	 */
	public function toIntTest()
	{
		$boolean = new Boolean(false);
		$result  = $boolean->toInt();
		$this->compareInstance('System\Type\Primitive\Int', $result, '型チェック');
		$this->compareValue(0, $result->getValue(), '値がfalseのとき');

		$boolean = new Boolean(true);
		$result  = $boolean->toInt();
		$this->compareValue(1, $result->getValue(), '値がtrueのとき');
	}

	/**
	 * toString
	 */
	public function toStringTest()
	{
		$boolean = new Boolean(false);
		$result  = $boolean->toString();
		$this->compareInstance('System\Type\Primitive\String', $result, '型チェック');
		$this->compareValue("0", $result->getValue(), '値がfalseのとき');

		$boolean = new Boolean(true);
		$result  = $boolean->toString();
		$this->compareValue("1", $result->getValue(), '値がtrueのとき');
	}

	/**
	 * toFloat
	 */
	public function toFloatTest()
	{
		$boolean = new Boolean(false);
		$result  = $boolean->toFloat();
		$this->compareInstance('System\Type\Primitive\Float', $result, '型チェック');
		$this->compareValue((float)0, $result->getValue(), '値がfalseのとき');

		$boolean = new Boolean(true);
		$result  = $boolean->toFloat();
		$this->compareValue((float)1, $result->getValue(), '値がtrueのとき');
	}
}
