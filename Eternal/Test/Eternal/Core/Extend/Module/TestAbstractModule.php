<?php

/**
 * AbstractModuleのテスト
 */

namespace Test\System\Core\Extend\Module;

use Test\Mock;
use Test\TestHelper;
use System\Exception\SystemException;

use Test\ChildModule;

class TestAbstractModule extends TestHelper
{
	/**
	 * get
	 * getName
	 * run
	 */
	public function getAndGetNameAndRunTest()
	{
		$childModule = new ChildModule();
		$this->compareValueLax($childModule, ChildModule::get(), 'get');
		$this->compareValue('Test\ChildModule', $childModule::getName(), 'getName');
		$this->compareValue('data', $childModule->run('path', 'data'), 'run');
	}
}

namespace Test;

use System\Core\Extend\Module\AbstractModule;

class ChildModule extends AbstractModule
{
	protected static $instance;

	public function run($path, $data)
	{
		return 'data';
	}
}

