<?php

/**
 * QueryConditionTraitテスト
 */

namespace Test\System\Database\MySql\Query;

use System\Core\Di\Container;
use System\Database\MySql\Query\QueryConditionTrait;
use System\Database\MySql\Connection;
use System\Exception\DatabaseException;
use Test\Mock;
use Test\TestHelper;

class TestQueryConditionTrait extends TestHelper
{
	use QueryConditionTrait;

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
	 * where
	 */
	public function whereTest()
	{
		try {
			$this->where('id', null);
			$this->throwError('例外が発生すべき個所で発生していない');
		} catch (DatabaseException $e) {
			$this->compareException('Conditionクラスを', $e, '第2引数がnullの場合');
		}

		try {
			$this->where('id', '=', new \stdClass());
			$this->throwError('例外が発生すべき個所で発生していない');
		} catch (DatabaseException $e) {
			$this->compareException('SelectQuery', $e, '第3引数がSelectQuery以外のオブジェクトの場合');
		}
	}

	/**
	 * otherwise
	 */
	public function otherwiseTest()
	{
		try {
			$this->where('id', null);
			$this->throwError('例外が発生すべき個所で発生していない');
		} catch (DatabaseException $e) {
			$this->compareException('Conditionクラスを', $e, '第2引数がnullの場合');
		}

		try {
			$this->where('id', '=', new \stdClass());
			$this->throwError('例外が発生すべき個所で発生していない');
		} catch (DatabaseException $e) {
			$this->compareException('SelectQuery', $e, '第3引数がSelectQuery以外のオブジェクトの場合');
		}
	}

	/**
	 * getConditionLine
	 * getPlaceholder
	 */
	public function getConditionLineAndGetPlaceholder()
	{
		$this->compareValue(null, $this->getConditionLine(), 'where句が無い場合');

		$conditionQuery = new ConditionQuery($this->connection, $this->container, 'Test\ConditionQuery');
		$conditionQuery->selectQuery()
			->where('a', '=', 1)
			->where('b', '=', 'hoge')
			->where('c', 'IN', [2, 'fuga'])
			->otherwise('d', '=', 3)
			->otherwise('e', '=', 'piyo')
			->otherwise('f', 'IN', [4, 'mosu'])
			->where($conditionQuery
				->getCondition()
				->where('g', '=', 5)
				->otherwise('h', '=', 6)
			)
			->where('i', '=', $conditionQuery
				->selectQuery()
				->where('j', '=', 7)
				->otherwise('k', '=', 8)
			);

		$method = new \ReflectionMethod($conditionQuery, 'getConditionLine');
		$method->setAccessible(true);
		$expect  = 'WHERE a = ? AND b = ? AND c IN (?, ?) OR d = ? OR e = ? OR f = (?, ?) ';
		$expect .= 'AND (g = ? OR h = ?) AND i = ( SELECT tbl_condition___id FROM tbl_condition WHERE j = ? OR k = ? )';
		$this->compareValue($expect, $method->invoke($conditionQuery), 'getConditionLine');

		$method = new \ReflectionMethod($conditionQuery, 'getPlaceholder');
		$method->setAccessible(true);
		$expect = [1, '"hoge"', 2, '"fuga"', 3, '"piyo"', 4, '"mosu"', 5, 6, 7, 8];
		$this->compareValue($expect, $method->invoke($conditionQuery), 'getPlaceholder');
	}
}

namespace Test;

use System\Database\MySql\Query\BaseQuery;

class ConditionQuery extends BaseQuery
{
	/**
	 * @model int
	 */
	public function id()
	{

	}

	public function create()
	{
	}

	public static function getPrimaryKeys()
	{
		return ['id'];
	}

	public static function getTableName()
	{
		return 'tbl_condition';
	}
}
