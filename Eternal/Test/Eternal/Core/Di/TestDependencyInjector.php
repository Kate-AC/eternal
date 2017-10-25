<?php

/**
 * DependencyInjectorのテスト
 */

namespace Test\System\Core\Di;

use System\Core\Cache;
use System\Core\Di\DependencyInjector;
use System\Core\Di\DependencyDto;
use System\Exception\DiException;
use Test\Mock;
use Test\TestHelper;

use App\Model\Mosu;
use Test\Piyo;

class TestDependencyInjector extends TestHelper
{
	/**
	 * @var DependencyInjector
	 */
	private $dependencyInjector;

	/**
	 * @var DependencyDtoList
	 */
	private $dependencyDtoList;

	/**
	 * __construct
	 */
	public function __constructTest()
	{
		$this->compareInstance('System\Core\Di\DependencyInjector', new DependencyInjector(new Cache()));
	}

	/**
	 * 共通部品を生成する
	 */
	private function common()
	{
		$mosu = new Mosu();
		$mock = Mock::m()->_setMethod('setCache')
			->_setArgs('App\Model\Mosu', $mosu)
			->_setReturn(null)
			->e();

		$piyo = new Piyo($mosu);
		$mock->_setMethod('setCache')
			->_setArgs('Test\Piyo', $piyo)
			->_setReturn(null)
			->e();

		$this->dependencyInjector = Mock::m('System\Core\Di\DependencyInjector');
		$this->dependencyInjector->cache = $mock;

		$this->dependencyDtoList = [
			'App\Model\Mosu' => new DependencyDto('App\Model\Mosu', []),
			'Test\Piyo'      => new DependencyDto('Test\Piyo', ['App\Model\Mosu'])
		];
	}

	/**
	 * create
	 */
	public function createTest()
	{
		$this->common();
		$dependencyInjector = Mock::m('System\Core\Di\DependencyInjector');
		$dependencyInjector->_setMethod('createRecursive')
			->_setArgs($this->dependencyDtoList)
			->_setReturn(null)
			->e();

		$this->compareValue($dependencyInjector, $dependencyInjector->create($this->dependencyDtoList));
	}

	/**
	 * createRecursive
	 */
	public function createRecursiveAndGetResolvedClassListTest()
	{
		$this->common();
		$this->dependencyInjector->createRecursive($this->dependencyDtoList);

		$mosu = new Mosu();
		$piyo = new Piyo($mosu);
		$expected = [
			'App\Model\Mosu' => $mosu,
			'Test\Piyo'      => $piyo
		];
		$resolvedClassList = $this->dependencyInjector->getResolvedClassList();

		$this->compareValueLax($mosu, $resolvedClassList['App\Model\Mosu']);
		$this->compareValueLax($piyo, $resolvedClassList['Test\Piyo']);
	}

	/**
	 * checkFlushDependency
	 */
	public function checkFlushDependencyTest()
	{
		$this->common();
		$this->dependencyInjector->createRecursive($this->dependencyDtoList);

		$dependencyClassList = [
			'App\Model\Mosu' => 'App\Model\Mosu',
			'Test\Piyo'      => 'Test\Piyo'
		];

		$this->compareValue(true, $this->dependencyInjector->checkFlushDependency($dependencyClassList), '必要なクラスが全て揃っている場合');
		$this->compareValue(false, $this->dependencyInjector->checkFlushDependency(['App\Bbb']), '必要なクラスが揃っていない場合');
	}

	/**
	 * getResolvedClass
	 */
	public function getResolvedClassTest()
	{
		$this->common();
		$this->dependencyInjector->createRecursive($this->dependencyDtoList);

		$this->compareValueLax(new Mosu(), $this->dependencyInjector->getResolvedClass('App\Model\Mosu'), '指定した名前空間が存在する場合');

		$notExistNamespace = 'Aaa\Bbb';
		try {
			$this->dependencyInjector->getResolvedClass($notExistNamespace);
			$this->throwError('発生すべき箇所で例外が発生していない');
		} catch (DiException $e) {
			$this->compareException(sprintf('存在しない名前空間(%s)を指定した', $notExistNamespace), $e, '指定した名前空間が存在しない場合');
		}
	}
}

namespace Test;

use App\Model\Mosu;

class Piyo
{
	private $mosu;

	public function __construct(Mosu $mosu)
	{
		$this->mosu = $mosu;
	}
}

namespace App\Model;

class Mosu
{
}

