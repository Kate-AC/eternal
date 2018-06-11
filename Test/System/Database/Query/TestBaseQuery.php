<?php

/**
 * Baseクエリテスト
 */

namespace Test\System\Database\Query;

use System\Core\Di\Container;
use System\Database\Connection;
use System\Database\Query\BaseQuery;
use System\Exception\DatabaseException;
use Test\Mock;
use Test\TestHelper;

use Test\ChildQuery;

class TestBaseQuery extends TestHelper
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
	 * __construct
	 */
	public function __constructTest()
	{
		$childQuery = new ChildQuery($this->connection, $this->container, 'Test\ChildQuery');
		$this->compareInstance('Test\ChildQuery', $childQuery, 'インスタンス生成');
		$property = new \ReflectionProperty($childQuery, 'tableName');
		$property->setAccessible(true);
		$this->compareValue('tbl_child', $property->getValue($childQuery), 'テーブル名');

		$property = new \ReflectionProperty($childQuery, 'primaryKeys');
		$property->setAccessible(true);
		$this->compareValue(['id'], $property->getValue($childQuery), 'キー名');
	}

	/**
	 * __call
	 */
	public function __callTest()
	{
		$childQuery = new ChildQuery($this->connection, $this->container, 'Test\ChildQuery');

		try {
			$childQuery->notExistMethod();
			$this->throwError('例外が発生すべき場所で発生していない');
		} catch (DatabaseException $e) {
			$this->compareException('存在しないメソッド(notExistMethod)が呼ばれた(Class: Test\ChildQuery)', $e);
		}
	}

	/**
	 * indexHint
	 * getIndexHint
	 */
	public function indexHintAndGetIndexHintLineTest()
	{
		$reflection = new \ReflectionClass('Test\ChildQuery');
		$childQuery = $reflection->newInstanceWithoutConstructor();
		$childQuery->indexHint('tbl_hoge', 'FORCE', ['id', 'PRIMARY']);
		$childQuery->indexHint('tbl_fuga', 'USE', ['id', 'PRIMARY']);
		$childQuery->indexHint('tbl_piyo', 'IGNORE', ['id', 'PRIMARY']);

		$method = new \ReflectionMethod($childQuery, 'getIndexHintLine');
		$method->setAccessible(true);

		$this->compareValue(null, $method->invoke($childQuery), 'NULL');
		$this->compareValue('FORCE INDEX (id, PRIMARY)', $method->invoke($childQuery, 'tbl_hoge'), 'FORCE');
		$this->compareValue('USE INDEX (id, PRIMARY)', $method->invoke($childQuery, 'tbl_fuga'), 'USE');
		$this->compareValue('IGNORE INDEX (id, PRIMARY)', $method->invoke($childQuery, 'tbl_piyo'), 'IGNORE');

		$childQuery->indexHint('tbl_piyo', 'aaa', ['id', 'PRIMARY']);
		try {
			$method->invoke($childQuery, 'tbl_piyo');
			$this->throwError('例外が発生すべき箇所で発生していない');
		} catch (DatabaseException $e) {
			$this->compareException('存在しないインデックスタイプ', $e, '存在しないインデックスタイプ');
		}
	}

	/**
	 * explain
	 * getExplainLine
	 * forUpdate
	 * getForUpdateLine
	 */
	public function explainAndGetExplainLineAndForUpdateAndGetForUpdateLineTest()
	{
		$reflection = new \ReflectionClass('Test\ChildQuery');
		$childQuery = $reflection->newInstanceWithoutConstructor();

		$method = new \ReflectionMethod($childQuery, 'getForUpdateLine');
		$method->setAccessible(true);
		$this->compareValue(null, $method->invoke($childQuery), 'forUpdateが未指定の場合');
		$childQuery->forUpdate();
		$this->compareValue('FOR UPDATE', $method->invoke($childQuery), 'forUpdate');

		$method = new \ReflectionMethod($childQuery, 'getExplainLine');
		$method->setAccessible(true);
		$this->compareValue(null, $method->invoke($childQuery), 'explainが未指定の場合');
		$childQuery->explain();
		$this->compareValue('EXPLAIN', $method->invoke($childQuery), 'explain');
	}

	/**
	 * getQuery
	 * getBeforeQuery
	 */
	public function getQueryGetBeforeQueryTest()
	{
		$query = 'SELECT hoge    FROM sss   WHERE id =    ?  AND  fuga = ? ';
		$childQuery = Mock::m('Test\ChildQuery');
		$childQuery
			->_setMethod('create')
			->_setArgs()
			->_setReturn($query)
			->e();
		$childQuery->placeholder = ['"hoge"', 100];

		$this->compareValue('SELECT hoge FROM sss WHERE id = "hoge" AND fuga = 100 ', $childQuery->getQuery(), 'getQuery');
		$this->compareValue('SELECT hoge FROM sss WHERE id = ? AND fuga = ? ', $childQuery->getBeforeQuery(), 'getBeforeQuery');
	}
}

namespace Test;

use System\Database\Query\BaseQuery;

class ChildQuery extends BaseQuery
{
	public function create()
	{
	}

	public static function getPrimaryKeys()
	{
		return ['id'];
	}

	public static function getTableName()
	{
		return 'tbl_child';
	}
}

