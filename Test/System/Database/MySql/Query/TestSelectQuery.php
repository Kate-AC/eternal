<?php

/**
 * SELECTクエリテスト
 */

namespace Test\System\Database\MySql\Query;

use System\Core\Di\Container;
use System\Database\MySql\Connection;
use System\Database\MySql\Query\SelectQuery;
use System\Exception\DatabaseException;
use Test\Mock;
use Test\TestHelper;

use Test\HogeModel;

class TestSelectQuery extends TestHelper
{
	protected $connection;

	protected $container;

	public function __construct(
		Connection $connection,
		Container $container
	) {
		$this->connection = $connection;
		$this->container  = $container;
	}

	/**
	 * select
	 * getSelectLine
	 */
	public function selectAndGetSelectLineTestWhenSelected()
	{
		$reflection = new \ReflectionClass('System\Database\MySql\Query\SelectQuery');

		$selectQuery = $reflection->newInstanceWithoutConstructor();
		$proprety = new \ReflectionProperty($selectQuery, 'tableName');
		$proprety->setAccessible(true);
		$proprety->setValue($selectQuery, 'a_tbl');

		$selectSubQuery = $reflection->newInstanceWithoutConstructor();
		$proprety = new \ReflectionProperty($selectSubQuery, 'tableName');
		$proprety->setAccessible(true);
		$proprety->setValue($selectSubQuery, 'b_tbl');

		$selectSubQuery->select(['id']);

		$selectQuery->select([
			'hoge' => 'a_tbl.id',
			'COUNT(a_tbl.name)',
			$selectSubQuery
		], 'a');

		$method = new \ReflectionMethod($selectQuery, 'getSelectLine');
		$method->setAccessible(true);
		$expected = 'a_tbl.id AS "a_tbl___a_tbl.id", COUNT(a_tbl.name) AS "_collect___COUNT(a_tbl.name)", ( SELECT b_tbl.id FROM b_tbl AS b_tbl ) AS "_collect___( SELECT b_tbl.id FROM b_tbl AS b_tbl )"';
		$this->compareValue($expected, $method->invoke($selectQuery), 'select句を使用した場合');

		$expected = [
			'hoge' => [
				'table'  => 'a_tbl',
				'column' => 'id'
			]
		];

		$property = new \ReflectionProperty($selectQuery, 'propertyAsName');
		$property->setAccessible(true);
		$this->compareValue($expected, $property->getValue($selectQuery), 'propertyAsName');
	}

	/**
	 * getSelectLine
	 */
	public function getSelectLineTest()
	{
		$selectQuery = Mock::m('System\Database\MySql\Query\SelectQuery');
		$selectQuery->tableName = 'tbl_test_main_model';
		$selectQuery->tableAsName = [
			'tbl_test_main_model' => 'tbl_test_main_model',
			'tbl_test_join_model' => 'tbl_test_join_model'
		];
		$selectQuery->join = [['join' => ['table' => 'tbl_test_join_model']]];

		$selectQuery->container = Mock::m('System\Core\Di\Container')
			->_setMethod('getByTable')
			->_setArgs('tbl_test_main_model')
			->_setReturn('Test\TestMainModel')
			->e();

		$selectQuery->container
			->_setMethod('getByTable')
			->_setArgs('tbl_test_join_model')
			->_setReturn('Test\TestJoinModel')
			->e();

		$this->compareValue(
			'tbl_test_main_model.hoge AS tbl_test_main_model___hoge, ASTEXT(tbl_test_join_model.mosu) AS tbl_test_join_model___mosu',
			$selectQuery->getSelectLine(),
			'SELECT句が空の場合'
		);
	}

	/**
	 * groupBy
	 * getGroupByLine
	 */
	public function groupByAndGetGroupByLineTest()
	{
		$reflection  = new \ReflectionClass('System\Database\MySql\Query\SelectQuery');
		$selectQuery = $reflection->newInstanceWithoutConstructor();

		$property = new \ReflectionProperty($selectQuery, 'groupBy');
		$property->setAccessible(true);

		$selectQuery->groupBy('hoge');
		$selectQuery->groupBy('fuga');
		$this->compareValue(['hoge', 'fuga'], $property->getValue($selectQuery));

		$method = new \ReflectionMethod($selectQuery, 'getGroupByLine');
		$method->setAccessible(true);
		$this->compareValue('GROUP BY hoge, fuga', $method->invoke($selectQuery), 'get');

		$property->setValue($selectQuery, []);
		$this->compareValue(null, $method->invoke($selectQuery), 'get時に値がない場合');
	}

	/**
	 * orderBy
	 * getOrderByLine
	 */
	public function orderByAndGetOrderByLineTest()
	{
		$reflection  = new \ReflectionClass('System\Database\MySql\Query\SelectQuery');
		$selectQuery = $reflection->newInstanceWithoutConstructor();

		$property = new \ReflectionProperty($selectQuery, 'orderBy');
		$property->setAccessible(true);

		$selectQuery->orderBy('hoge', 'DESC');
		$selectQuery->orderBy('fuga', 'ASC');
		$orderByArray = [
			['column' => 'hoge', 'type' => 'DESC'],
			['column' => 'fuga', 'type' => 'ASC']
		];
		$this->compareValue($orderByArray, $property->getValue($selectQuery));

		$method = new \ReflectionMethod($selectQuery, 'getOrderByLine');
		$method->setAccessible(true);
		$this->compareValue('ORDER BY hoge DESC, fuga ASC', $method->invoke($selectQuery), 'get');

		$property->setValue($selectQuery, []);
		$this->compareValue(null, $method->invoke($selectQuery), 'get時に値がない場合');
	}

	/**
	 * offset
	 * getOffsetLine
	 */
	public function offsetAndGetOffsetLineTest()
	{
		$reflection  = new \ReflectionClass('System\Database\MySql\Query\SelectQuery');
		$selectQuery = $reflection->newInstanceWithoutConstructor();

		$property = new \ReflectionProperty($selectQuery, 'offset');
		$property->setAccessible(true);

		$selectQuery->offset('hoge');
		$this->compareValue('hoge', $property->getValue($selectQuery));

		$method = new \ReflectionMethod($selectQuery, 'getOffsetLine');
		$method->setAccessible(true);
		$this->compareValue('OFFSET hoge', $method->invoke($selectQuery), 'get');

		$property->setValue($selectQuery, []);
		$this->compareValue(null, $method->invoke($selectQuery), 'get時に値がない場合');
	}

	/**
	 * limit
	 * getLimitLine
	 */
	public function limitGetLimitLineTest()
	{
		$reflection  = new \ReflectionClass('System\Database\MySql\Query\SelectQuery');
		$selectQuery = $reflection->newInstanceWithoutConstructor();

		$property = new \ReflectionProperty($selectQuery, 'limit');
		$property->setAccessible(true);

		$selectQuery->limit('hoge');
		$this->compareValue('hoge', $property->getValue($selectQuery));

		$method = new \ReflectionMethod($selectQuery, 'getLimitLine');
		$method->setAccessible(true);
		$this->compareValue('LIMIT hoge', $method->invoke($selectQuery), 'get');

		$property->setValue($selectQuery, []);
		$this->compareValue(null, $method->invoke($selectQuery), 'get時に値がない場合');
	}

	/**
	 * from
	 */
	public function fromTest()
	{
		$selectQuery = (new HogeModel($this->connection, $this->container))->selectQuery();
		$from = (new HogeModel($this->connection, $this->container))->selectQuery();

		$selectQuery->from('hoge', $from);
		$property = new \ReflectionProperty($selectQuery, 'from');
		$property->setAccessible(true);
		$this->compareValue(['as' => 'hoge', 'from' => $from], $property->getValue($selectQuery));
	}

	/**
	 * getFromLine
	 */
	public function getFromLineTest()
	{
		$selectQuery = Mock::m('System\Database\MySql\Query\SelectQuery');
		$selectQuery->tableName = 'hoge';
		$this->compareValue('hoge AS hoge', $selectQuery->getFromLine(), 'fromが空の場合');

		$from = Mock::m('System\Database\MySql\Query\SelectQuery');
		$from->placeholder = [2, 3];
		$from->_setMethod('getBeforeQuery')
			->_setArgs()
			->_setReturn('SELECT hoge FROM fuga')
			->e();

		$selectQuery = (new HogeModel($this->connection, $this->container))->selectQuery();
		$property = new \ReflectionProperty($selectQuery, 'from');
		$property->setAccessible(true);
		$property->setValue($selectQuery, [
			'as'   => 'piyo',
			'from' => $from
		]);

		$method = new \ReflectionMethod($selectQuery, 'getFromLine');
		$method->setAccessible(true);

		$expected = '(SELECT hoge FROM fuga) AS piyo';
		$this->compareValue($expected, $method->invoke($selectQuery), 'fromが空ではない場合');
	}

	/**
	 * join
	 * getJoinLine
	 */
	public function joinAndGetJoinLineTest()
	{
		$selectQuery = (new HogeModel($this->connection, $this->container))->selectQuery();
		$selectQuery->join(['b' => 'tbl_b'], [
				'tbl_a.id'   => 'tbl_b.id',
				'tbl_a.name' => 'tbl_b.name'
			]);
		$selectQuery->indexHint('tbl_b', 'FORCE', ['id', 'name']);

		$expected = 'LEFT JOIN tbl_b AS b FORCE INDEX (id, name) ON tbl_a.id = tbl_b.id AND tbl_a.name = tbl_b.name';
		$method = new \ReflectionMethod($selectQuery, 'getJoinLine');
		$method->setAccessible(true);
		$this->compareValue($expected, $method->invoke($selectQuery));
	}

	/**
	 * create
	 */
	public function createTest()
	{
		$selectQuery = Mock::m('System\Database\MySql\Query\SelectQuery');
		$selectQuery->tableAsName = ['hoge' => 'A'];

		$selectQuery->_setMethod('getExplainLine')->_setArgs()->_setReturn('COUNT(hoge.fuga)')->e();
		$selectQuery->_setMethod('getSelectLine')->_setArgs()->_setReturn('B')->e();
		$selectQuery->tableName = 'C';
		$selectQuery->_setMethod('getIndexHintLine')->_setArgs('C')->_setReturn('D')->e();
		$selectQuery->_setMethod('getJoinLine')->_setArgs()->_setReturn('E')->e();
		$selectQuery->_setMethod('getConditionLine')->_setArgs()->_setReturn('F')->e();
		$selectQuery->_setMethod('getGroupByLine')->_setArgs()->_setReturn('G')->e();
		$selectQuery->_setMethod('getOrderByLine')->_setArgs()->_setReturn('H')->e();
		$selectQuery->_setMethod('getLimitLine')->_setArgs()->_setReturn('I')->e();
		$selectQuery->_setMethod('getForUpdateLine')->_setArgs()->_setReturn('J')->e();
		$selectQuery->_setMethod('getOffsetLine')->_setArgs()->_setReturn('K')->e();

		$this->compareValue('COUNT(hoge.fuga) SELECT B FROM C AS C D E F G H I J K', $selectQuery->create());
	}
}

namespace Test;

class TestMainModel
{
	/**
	 * @model int
	 */
	protected $hoge;

	public static function getTableName()
	{
		return 'tbl_test_main_model';
	}
}

namespace Test;

class TestJoinModel
{
	/**
	 * @model Point
	 */
	protected $mosu;

	public static function getTableName()
	{
		return 'tbl_test_join_model';
	}
}

namespace Test;

use System\Database\MySql\BaseModel;

class HogeModel extends BaseModel
{
	/**
	 * @model int
	 */
	protected $id;

	public function getId()
	{
		return $this->id;
	}

	public static function getTableName()
	{
		return 'hoge_tbl';
	}

	public static function getPrimaryKeys()
	{
		return [];
	}
}
