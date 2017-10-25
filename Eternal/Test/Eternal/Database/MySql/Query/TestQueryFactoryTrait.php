<?php

/**
 * QueryFactoryTraitのテスト
 */

namespace Test\System\Database\MySql\Query;

use System\Database\MySql\Collect;
use System\Exception\DatabaseException;
use Test\Mock;
use Test\TestHelper;

use Test\TestModel;

class TestQueryFactoryTrait extends TestHelper
{
	/**
	 * makeTableList
	 * makeDependencyList
	 */
	public function makeTableListAndMakeDependencyListTest()
	{
		$reflection = new \ReflectionClass('System\Database\MySql\Query\SelectQuery');
		$instance   = $reflection->newInstanceWithoutConstructor();

		$property = new \ReflectionProperty($instance, 'tableName');
		$property->setAccessible(true);
		$property->setValue($instance, 'hoge_tbl');

		$property = new \ReflectionProperty($instance, 'formatedJoin');
		$property->setAccessible(true);

		$formatedJoin = [
			[
				'join' => [
					'table' => 'fuga_tbl',
					'as'    => null
				],
				'on' => [
					[
						'a' => ['table' => 'fuga_tbl', 'column' => 'id'],
						'b' => ['table' => 'hoge_tbl', 'column' => 'id']
					]
				]
			],
			[
				'join' => [
					'table' => 'piyo_tbl',
					'as'    => null
				],
				'on' => [
					[
						'a' => ['table' => 'piyo_tbl', 'column' => 'id'],
						'b' => ['table' => 'hoge_tbl', 'column' => 'id']
					]
				]
			]
		];

		$property->setValue($instance, $formatedJoin);

		$tableList = [
			'hoge_tbl',
			'fuga_tbl',
			'piyo_tbl'
		];

		$method = new \ReflectionMethod($instance, 'makeTableList');
		$method->setAccessible(true);
		$method->invoke($instance);

		$property = new \ReflectionProperty($instance, 'tableList');
		$property->setAccessible(true);

		$this->compareValue($tableList, $property->getValue($instance), 'makeTableList');

		$method = new \ReflectionMethod($instance, 'makeDependencyList');
		$method->setAccessible(true);
		$method->invoke($instance);

		$property = new \ReflectionProperty($instance, 'dependencyList');
		$property->setAccessible(true);

		$dependencyList = [
			'hoge_tbl' => [
				'fuga_tbl',
				'piyo_tbl'
			],
			'fuga_tbl' => [],
			'piyo_tbl' => []
		];

		$this->compareValue($dependencyList, $property->getValue($instance), 'makeDependencyList');
	}

	/**
	 * union
	 */
	public function unionTest()
	{
		$query = Mock::m('System\Database\MySql\Query\SelectQuery');
		$query->tableName = 'hoge_tbl';

		$dependencyList = [
			'hoge_tbl' => ['fuga_tbl', 'piyo_tbl'],
			'fuga_tbl' => ['mosu_tbl'],
			'mosu_tbl' => [],
			'piyo_tbl' => []
		];

		$entityList = [
			'hoge_tbl' => new \stdClass(),
			'fuga_tbl' => new \stdClass(),
			'mosu_tbl' => new \stdClass(),
			'piyo_tbl' => new \stdClass()
		];

		$entityList['hoge_tbl']->name = 'Hoge';
		$entityList['fuga_tbl']->name = 'Fuga';
		$entityList['mosu_tbl']->name = 'Mosu';
		$entityList['piyo_tbl']->name = 'Piyo';

		$created = $entityList['hoge_tbl'];
		$created->Fuga = $entityList['fuga_tbl'];
		$created->Piyo = $entityList['piyo_tbl'];
		$created->Fuga->Mosu = $entityList['mosu_tbl'];

		$this->compareValue($created, $query->union($dependencyList, $entityList));
	}

	/**
	 * unite
	 */
	public function uniteTest()
	{
		$query = Mock::m('System\Database\MySql\Query\SelectQuery');
		$query->tableName = 'hoge_tbl';

		$query->_setMethod('makeTableList')
			->_setArgs()
			->_setReturn()
			->e();
		$query->_setMethod('makeDependencyList')
			->_setArgs()
			->_setReturn()
			->e();

		$container = Mock::m('System\Core\Di\Container');
		$container->_setMethod('getByTable')
			->_setArgs('hoge_tbl')
			->_setReturn('\Test\Query\TestModel')
			->e();
		$container->_setMethod('getByTable')
			->_setArgs('fuga_tbl')
			->_setReturn('\Test\Query\TestModel')
			->e();
		$container->_setMethod('getByTable')
			->_setArgs('piyo_tbl')
			->_setReturn('\Test\Query\TestModel')
			->e();
		$query->container = $container;

		$query->tableList = [
			'hoge_tbl',
			'fuga_tbl',
			'piyo_tbl'
		];

		$query->dependencyList = [
			'hoge_tbl' => [
				'fuga_tbl',
				'piyo_tbl'
			],
			'fuga_tbl' => [],
			'piyo_tbl' => []
		];

		$resultList = [
			[
				'COUNT(piyo_tbl.id)' => 100,
				'hoge_tbl___id'      => '2',
				'fuga_tbl___id'      => '22',
				'fuga_tbl___name'    => 'AAA',
				'piyo_tbl___name'    => 'aaa'
			],
			[
				'COUNT(piyo_tbl.id)' => 200,
				'hoge_tbl___id'      => '3',
				'fuga_tbl___id'      => '33',
				'fuga_tbl___name'    => 'BBB',
				'piyo_tbl___name'    => 'bbb'
			]
		];

		$hogeEntity = \Test\Query\TestModel::make([]);
		$fugaEntity = \Test\Query\TestModel::make([]);
		$piyoEntity = \Test\Query\TestModel::make([]);

		$created = $hogeEntity;
		$created->Fuga = $fugaEntity;
		$created->Piyo = $piyoEntity;
		$created2 = clone $created;
		$created->Collect  = new Collect(['COUNT(piyo_tbl.id)' => 100]);
		$created2->Collect = new Collect(['COUNT(piyo_tbl.id)' => 200]);

		$this->compareValueLax([$created, $created2], $query->unite($resultList));

		try {
			$query->unite($resultList, 'notExistKey');
			$this->throwError('例外が発生すべき箇所で発生していない');
		} catch (DatabaseException $e) {
			$this->compareException('メソッドが存在しません', $e, '存在しないkeyを指定');
		}

		try {
			$query->unite($resultList, 'id');
			$this->throwError('例外が発生すべき箇所で発生していない');
		} catch (DatabaseException $e) {
			$this->compareException('添え字に使用できない', $e, 'nullの結果を返すkeyを指定');
		}
	}

	/**
	 * unionArray
	 */
	public function unionArrayTest()
	{
		$query = Mock::m('System\Database\MySql\Query\SelectQuery');
		$query->tableName = 'hoge_tbl';

		$dependencyList = [
			'hoge_tbl' => ['fuga_tbl', 'piyo_tbl'],
			'fuga_tbl' => ['mosu_tbl'],
			'mosu_tbl' => [],
			'piyo_tbl' => []
		];

		$classList = [
			'hoge_tbl' => ['Hoge' => []],
			'fuga_tbl' => ['Fuga' => []],
			'mosu_tbl' => ['Mosu' => []],
			'piyo_tbl' => ['Piyo' => []]
		];

		$created = $classList['hoge_tbl'];

		$created['Hoge']['Fuga']         = $classList['fuga_tbl']['Fuga'];
		$created['Hoge']['Piyo']         = $classList['piyo_tbl']['Piyo'];
		$created['Hoge']['Fuga']['Mosu'] = $classList['mosu_tbl']['Mosu'];

		$this->compareValue($created, $query->unionArray($dependencyList, $classList));
	}



	/**
	 * uniteArray
	 */
	public function uniteArrayTest()
	{
		$query = Mock::m('System\Database\MySql\Query\SelectQuery');
		$query->tableName = 'hoge_tbl';

		$query->_setMethod('makeTableList')
			->_setArgs()
			->_setReturn()
			->e();
		$query->_setMethod('makeDependencyList')
			->_setArgs()
			->_setReturn()
			->e();

		$query->tableList = [
			'hoge_tbl',
			'fuga_tbl',
			'piyo_tbl'
		];

		$query->dependencyList = [
			'hoge_tbl' => [
				'fuga_tbl',
				'piyo_tbl'
			],
			'fuga_tbl' => [],
			'piyo_tbl' => []
		];

		$resultList = [
			[
				'hoge_tbl___id'      => '2',
				'hoge_tbl___user_id' => null,
				'fuga_tbl___id'      => '22',
				'fuga_tbl___name'    => 'AAA',
				'piyo_tbl___name'    => 'aaa',
				'piyo_tbl___POINT(1.0 2.0)' => 'POINT(1.0 2.0)' 
			],
			[
				'hoge_tbl___id'      => '3',
				'hoge_tbl___user_id' => null,
				'fuga_tbl___id'      => '33',
				'fuga_tbl___name'    => 'BBB',
				'piyo_tbl___name'    => 'bbb',
				'piyo_tbl___POINT(3.0 4.0)' => 'POINT(3.0 4.0)' 
			]
		];

		$created = ['Hoge' => ['id' => '2', 'user_id' => null]];
		$created['Hoge']['Fuga'] = ['id' => '22', 'name' => 'AAA'];
		$created['Hoge']['Piyo'] = ['name' => 'aaa'];
		$created['Hoge']['Piyo']['POINT(1.0 2.0)'] = ['Point' => ['lng' => '1.0', 'lat' => '2.0']];

		$created2 = ['Hoge' => ['id' => '3', 'user_id' => null]];
		$created2['Hoge']['Fuga'] = ['id' => '33', 'name' => 'BBB'];
		$created2['Hoge']['Piyo'] = ['name' => 'bbb'];
		$created2['Hoge']['Piyo']['POINT(3.0 4.0)'] = ['Point' => ['lng' => '3.0', 'lat' => '4.0']];

		$this->compareValue([$created, $created2], $query->uniteArray($resultList), 'key未指定');
		$this->compareValue([2 => $created, 3 => $created2], $query->uniteArray($resultList, 'id'), 'key指定');

		try {
			$query->uniteArray($resultList, 'notExistKey');
			$this->throwError('例外が発生すべき箇所で発生していない');
		} catch (DatabaseException $e) {
			$this->compareException('添え字が存在しません', $e, '存在しないkeyを指定');
		}

		try {
			$query->uniteArray($resultList, 'user_id');
			$this->throwError('例外が発生すべき箇所で発生していない');
		} catch (DatabaseException $e) {
			$this->compareException('nullの値が存在します', $e, 'nullの結果を返すkeyを指定');
		}
	}
}

namespace Test\Query;

use System\Database\MySql\BaseModel;

class TestModel extends BaseModel
{
	private $id;

	public function setId($id)
	{
		$this->id = $id;
	}

	public function getId()
	{
		return $this->id;
	}

	public static function make(array $properties)
	{
		return new static();
	}
}
