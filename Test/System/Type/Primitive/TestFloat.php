<?php

/**
 * Floatのテスト
 */

namespace Test\System\Type\Primitive;

use System\Type\Primitive\Int;
use System\Type\Primitive\Boolean;
use System\Type\Primitive\String;
use System\Type\Primitive\Float;
use System\Exception\IncorrectTypeException;
use Test\Mock;
use Test\TestHelper;

class TestFloat extends TestHelper
{
	/**
	 * check
	 * format
	 */
	public function checkAndFormatTest()
	{
		$value = 1.0;
		$float = new Float($value);
		$this->compareValue($value, $float->getValue(), '数値を渡した場合');

		$value = null;
		$float = new Float($value);
		$this->compareValue($value, $float->getValue(), 'nullの場合を渡した場合');

		$value = 1.0;
		$float = new Float(new Float($value));
		$this->compareValue($value, $float->getValue(), 'Float型を渡した場合');

		try {
			new Float(new \stdClass());
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('Float型ではないオブジェクトが渡された', $e, 'Float型ではないオブジェクトを渡した場合');
		}

		try {
			new Float([1.0]);
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('配列が渡された', $e, '配列を渡した場合');
		}

		try {
			new Float('hoge');
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('Floatではない値が渡された', $e, 'Floatではない値を渡した場合');
		}
	}

	/**
	 * toBoolean
	 */
	public function toBooleanTest()
	{
		$float  = new Float(0.0);
		$result = $float->toBoolean();
		$this->compareInstance('System\Type\Primitive\Boolean', $result, '型チェック');
		$this->compareValue(false, $result->getValue(), '数値が0.0のとき');

		$float  = new Float(1.0);
		$result = $float->toBoolean();
		$this->compareValue(true, $result->getValue(), '数値が1.0のとき');

		try {
			(new Float(8.0))->toBoolean();
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('Boolean型に変換できる値は0か1のみです', $e, '数値が0か1以外の場合');
		}
	}

	/**
	 * toInt
	 */
	public function toFloatTest()
	{
		$float  = new Float(2.1);
		$result = $float->toInt();
		$this->compareInstance('System\Type\Primitive\Int', $result, '型チェック');
		$this->compareValue(2, $result->getValue());
	}

	/**
	 * toString
	 */
	public function toStringTest()
	{
		$float  = new Float(-1.1);
		$result = $float->toString();
		$this->compareInstance('System\Type\Primitive\String', $result, '型チェック');
		$this->compareValue("-1.1", $result->getValue());
	}
}
