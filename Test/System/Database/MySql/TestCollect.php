<?php

/**
 * Collectのテスト
 */

namespace Test\System\Database\MySql;

use System\Database\MySql\Collect;
use Test\Mock;
use Test\Parse;
use Test\TestHelper;

class TestCollect extends TestHelper
{
	/**
	 * __construct
	 */
	public function __constructTest()
	{
		$list    = ['hoge' => 99, 'fuga' => 100];
		$collect = new Collect($list);
		$this->compareInstance('System\Database\MySql\Collect', $collect, 'インスタンス生成');

		$reflectionProperty = new \ReflectionProperty($collect, 'properties');
		$reflectionProperty->setAccessible(true);
		$this->compareValue($list, $reflectionProperty->getValue($collect), 'プロパティの確認');
	}

	/**
	 * __call
	 */
	public function __callTest()
	{
		$list    = ['hoge' => 99, 'fuga' => 100];
		$collect = new Collect($list);
		$this->compareValue(99, $collect->hoge(), 'プロパティが存在する場合');
		$this->compareValue(null, $collect->aaa(), 'プロパティが存在しない場合');
	}
}
