<?php

/**
 * AbstractPrimitiveのテスト
 */

namespace Test\System\Type\Primitive;

use System\Type\Primitive\Boolean;
use System\Type\Primitive\Int;
use System\Type\Primitive\String;
use System\Exception\IncorrectTypeException;
use Test\Mock;
use Test\TestHelper;

class TestAbstractPrimitive extends TestHelper
{
	/**
	 * __constract
	 * setValue
	 * getValue
	 */
	public function __constractAndSetValueAndGetValueTest()
	{
		$int = new Int(77);
		$this->compareInstance('System\Type\Primitive\Int', $int, 'インスタンスの生成');
		$this->compareValue(77, $int->getValue());
	}

	/**
	 * compare
	 */
	public function compareTest()
	{
		$int = new Int(33);
		$this->compareValueLax(new Boolean(true), $int->compare('<>', new Int(4)), '<>');
		$this->compareValueLax(new Boolean(true), $int->compare('!==', new Int(4)), '!==');

		$this->compareValueLax(new Boolean(true), $int->compare('===', new Int(33)), '===');
		$this->compareValueLax(new Boolean(true), $int->compare('<=', new Int(33)), '<=');
		$this->compareValueLax(new Boolean(true), $int->compare('>=', new Int(33)), '>=');

		$this->compareValueLax(new Boolean(true), $int->compare('<', new Int(34)), '<');
		$this->compareValueLax(new Boolean(true), $int->compare('>', new Int(32)), '>');

		try {
			$int->compare('hoge', new Int(4));
			$this->throwError('例外が発生すべき個所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('compareメソッド', $e, 'hoge');
		}

		try {
			$int->compare('hoge', new String('a'));
			$this->throwError('例外が発生すべき個所で発生していない');
		} catch (IncorrectTypeException $e) {
			$this->compareException('同じインスタンス同士', $e, 'IntとStringを比較した場合');
		}
	}

	/**
	 * isNull
	 */
	public function isNullTest()
	{
		$int = new Int(null);
		$this->compareValueLax(new Boolean(true), $int->isNull(), 'nullの場合');

		$int = new Int(2);
		$this->compareValueLax(new Boolean(false), $int->isNull(), 'nullではない場合');
	}
}
