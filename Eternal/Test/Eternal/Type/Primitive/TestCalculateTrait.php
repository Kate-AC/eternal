<?php

/**
 * CalculateTraitのテスト
 */

namespace Test\System\Type\Primitive;

use System\Exception\IncorrectTypeException;
use System\Type\Primitive\Int;
use System\Type\Primitive\Float;
use Test\Mock;
use Test\TestHelper;

class TestCalculateTrait extends TestHelper
{
	/**
	 * add
	 */
	public function addTest()
	{
		$int = new Int(1);
		$this->compareValue(101, $int->add(100)->getValue());
	}

	/**
	 * times
	 */
	public function timesTest()
	{
		$int = new Int(2);
		$this->compareValue(10, $int->times(5)->getValue());
	}

	/**
	 * divided
	 */
	public function dividedTest()
	{
		$int = new Int(7);
		$this->compareValue(3, $int->divided(2)->getValue(), 'Intの場合');

		$float = new Float(7);
		$this->compareValue(3.5, $float->divided(2)->getValue(), 'Floatの場合');
	}
}
