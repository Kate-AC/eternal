<?php

/**
 * CallPrimitiveFunctionのテスト
 */

namespace Test\System\NotClass;

use System\Type\Primitive\Boolean;
use System\Type\Primitive\Float;
use System\Type\Primitive\Int;
use System\Type\Primitive\String;

use Test\Mock;
use Test\TestHelper;

class TestCallPrimitiveFunction extends TestHelper
{
	/**
	 * Int
	 */
	public function IntTest()
	{
		$this->compareValueLax(new Int(1), Int(1));
	}

	/**
	 * String
	 */
	public function StringTest()
	{
		$this->compareValueLax(new String('hoge'), String('hoge'));
	}

	/**
	 * Float
	 */
	public function FloatTest()
	{
		$this->compareValueLax(new Float(1.23), Float(1.23));
	}

	/**
	 * Boolean
	 */
	public function BooleanTest()
	{
		$this->compareValueLax(new Boolean(true), Boolean(true));
	}
}
