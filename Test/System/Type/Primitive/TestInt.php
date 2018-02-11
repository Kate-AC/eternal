<?php

/**
 * Intのテスト
 */

namespace Test\System\Type\Primitive;

use System\Type\Primitive\Int;
use System\Type\Primitive\Boolean;
use System\Type\Primitive\String;
use System\Type\Primitive\Float;
use System\Exception\IncorrectTypeException;
use Test\Mock;
use Test\TestHelper;

class TestInt extends TestHelper
{
	/**
	 * check
	 * format
	 */
	public function checkAndFormatTest()
	{
		$value = 1;
		$int   = new Int($value);
		$this->compareValue($value, $int->getValue(), '数値を渡した場合');

		$value = null;
		$int   = new Int($value);
		$this->compareValue($value, $int->getValue(), 'nullの場合を渡した場合');

		$value = 1;
		$int   = new Int(new Int($value));
		$this->compareValue($value, $int->getValue(), 'Int型を渡した場合');

		try {
			new Int(new \stdClass());
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('Int型ではないオブジェクトが渡された', $e, 'Int型ではないオブジェクトを渡した場合');
		}

		try {
			new Int([1]);
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('配列が渡された', $e, '配列を渡した場合');
		}

		try {
			new Int('hoge');
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('Intではない値が渡された', $e, 'Intではない値を渡した場合');
		}
	}

	/**
	 * toBoolean
	 */
	public function toBooleanTest()
	{
		$int    = new Int(0);
		$result = $int->toBoolean();
		$this->compareInstance('System\Type\Primitive\Boolean', $result, '型チェック');
		$this->compareValue(false, $result->getValue(), '数値が0のとき');

		$int    = new Int(1);
		$result = $int->toBoolean();
		$this->compareValue(true, $result->getValue(), '数値が1のとき');

		try {
			(new Int(8))->toBoolean();
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('Boolean型に変換できる値は0か1のみです', $e, '数値が0か1以外の場合');
		}
	}

	/**
	 * toString
	 */
	public function toStringTest()
	{
		$int    = new Int(0);
		$result = $int->toString();
		$this->compareInstance('System\Type\Primitive\String', $result, '型チェック');
		$this->compareValue("0", $result->getValue());
	}

	/**
	 * toFloat
	 */
	public function toFloatTest()
	{
		$int    = new Int(0);
		$result = $int->toFloat();
		$this->compareInstance('System\Type\Primitive\Float', $result, '型チェック');
		$this->compareValue((float)0, $result->getValue());
	}
}
