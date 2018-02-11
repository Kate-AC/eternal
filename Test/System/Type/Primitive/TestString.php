<?php

/**
 * Stringのテスト
 */

namespace Test\System\Type\Primitive;

use System\Type\Primitive\Int;
use System\Type\Primitive\Boolean;
use System\Type\Primitive\String;
use System\Type\Primitive\Float;
use System\Exception\IncorrectTypeException;
use Test\Mock;
use Test\TestHelper;

class TestString extends TestHelper
{
	/**
	 * check
	 * format
	 */
	public function checkAndFormatTest()
	{
		$value  = 'hoge';
		$string = new String($value);
		$this->compareValue($value, $string->getValue(), '文字列を渡した場合');

		$value  = null;
		$string = new String($value);
		$this->compareValue($value, $string->getValue(), 'nullの場合を渡した場合');

		$value  = 'hoge';
		$string = new String(new String($value));
		$this->compareValue($value, $string->getValue(), 'String型を渡した場合');

		try {
			new String(new \stdClass());
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('String型ではないオブジェクトが渡された', $e, 'String型ではないオブジェクトを渡した場合');
		}

		try {
			new String(['hoge']);
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('配列が渡された', $e, '配列を渡した場合');
		}

		try {
			new String(1);
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('Stringではない値が渡された', $e, 'Stringではない値を渡した場合');
		}
	}

	/**
	 * isNull
	 */
	public function isNullTest()
	{
		$string = new String(null);
		$this->compareValueLax(new Boolean(true), $string->isNull(), 'nullの場合');

		$string = new String('');
		$this->compareValueLax(new Boolean(true), $string->isNull(), '""の場合');

		$string = new String('hoge');
		$this->compareValueLax(new Boolean(false), $string->isNull());
	}

	/**
	 * replace
	 */
	public function replaceTest()
	{
		$string = new String('hogefugapiyofuga');
		$string->replace(new String('fuga'), new String('A'));
		$this->compareValue('hogeApiyoA', $string->getValue(), 'String型を渡した場合');

		$string = new String('hogefugapiyofuga');
		$string->replace('fuga', 'A');
		$this->compareValue('hogeApiyoA', $string->getValue(), '文字列を渡した場合');

		try {
			$string = new String('hogefugapiyofuga');
			$string->replace(1, 'a');
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('第1引数にString以外の値が与えられた', $e, '第1引数にStringではない値を渡した場合');
		}

		try {
			$string = new String('hogefugapiyofuga');
			$string->replace('a', 1);
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('第2引数にString以外の値が与えられた', $e, '第2引数にStringではない値を渡した場合');
		}
	}

	/**
	 * match
	 */
	public function matchTest()
	{
		$string = new String('hogefugapiyofuga');
		$this->compareValueLax(new Boolean(true), $string->match(new String('fuga')), 'String型を渡した場合');

		$string = new String('hogefugapiyofuga');
		$this->compareValueLax(new Boolean(true), $string->match('fuga'), '文字列を渡した場合');

		$this->compareValueLax(new Boolean(false), $string->match('aaaa'), 'マッチしない場合');

		try {
			$string = new String('hogefugapiyofuga');
			$string->match(1);
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('第1引数にString以外の値が与えられた', $e, '第1引数にStringではない値を渡した場合');
		}
	}

	/**
	 * toBoolean
	 */
	public function toBooleanTest()
	{
		$string = new String('0');
		$result = $string->toBoolean();
		$this->compareInstance('System\Type\Primitive\Boolean', $result, '型チェック');
		$this->compareValue(false, $result->getValue(), '文字列が0のとき');

		$string = new String('1');
		$result = $string->toBoolean();
		$this->compareValue(true, $result->getValue(), '文字列が1のとき');

		try {
			(new String('8'))->toBoolean();
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('Boolean型に変換可能な値は0か1の文字のみです', $e, '文字列が0か1以外の場合');
		}
	}

	/**
	 * toInt
	 */
	public function toIntTest()
	{
		$string = new String('0');
		$result = $string->toInt();
		$this->compareInstance('System\Type\Primitive\Int', $result, '型チェック');
		$this->compareValue(0, $result->getValue(), '文字列が0のとき');

		$string = new String('-10');
		$result = $string->toInt();
		$this->compareValue(-10, $result->getValue(), '文字列が-10のとき');

		try {
			(new String('hoge'))->toInt();
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('Int型に変換可能な値は-0～9の文字列のみです', $e, '文字列が-0～9以外の場合');
		}
	}

	/**
	 * toFloat
	 */
	public function toFloatTest()
	{
		$string = new String('-10.5');
		$result = $string->toFloat();
		$this->compareInstance('System\Type\Primitive\Float', $result, '型チェック');

		$this->compareValue((float)-10.5, $result->getValue());

		try {
			(new String('hoge'))->toFloat();
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('Float型に変換可能な値は-.0～9の文字列のみです', $e, '文字列が-.0～9以外の場合');
		}
	}
}
