<?php

/**
 * UPDATEクエリテスト
 */

namespace Test\System\Database\MySql\Query;

use System\Database\MySql\Query\UpdateQuery;
use System\Exception\DatabaseException;
use Test\Mock;
use Test\TestHelper;

class TestUpdateQuery extends TestHelper
{
	/**
	 * create
	 */
	public function createTest()
	{
		$updateQuery = Mock::m('System\Database\MySql\Query\UpdateQuery');
		$updateQuery
			->_setMethod('getExplainLine')
			->_setArgs()
			->_setReturn('A')
			->e();

		$updateQuery->tableName = 'B';

		$updateQuery
			->_setMethod('getSetLine')
			->_setArgs()
			->_setReturn('C')
			->e();


		$updateQuery
			->_setMethod('getConditionLine')
			->_setArgs()
			->_setReturn('D')
			->e();

		$this->compareValue('A UPDATE B SET C D', $updateQuery->create());
	}

	/**
	 * update
	 */
	public function updateTest()
	{
		$updateQuery = Mock::m('System\Database\MySql\Query\UpdateQuery');
		$query       = 'query';
		$placeholder = 'placeholder';

		$updateQuery
			->_setMethod('create')
			->_setArgs()
			->_setReturn($query)
			->e();

		$updateQuery->placeholder = $placeholder;

		$prepare = Mock::m()
			->_setMethod('execute')
			->_setArgs($placeholder)
			->_setReturn(null)
			->e();

		$prepare
			->_setMethod('rowCount')
			->_setArgs()
			->_setReturn(100)
			->e();

		$pdo = Mock::m()
			->_setMethod('inTransaction')
			->_setArgs()
			->_setReturn(true)
			->e();

		$pdo->_setMethod('prepare')
			->_setArgs($query)
			->_setReturn($prepare)
			->e();

		$connection = Mock::m('System\Database\MySql\Connection')
			->_setMethod('get')
			->_setArgs('master')
			->_setReturn($pdo)
			->e();

		$updateQuery->connection = $connection;

		$this->compareValue(100, $updateQuery->update());
	}

	/**
	 * update
	 */
	public function updateTestWhenExcepion()
	{
		$updateQuery = Mock::m('System\Database\MySql\Query\UpdateQuery');

		$pdo = Mock::m()
			->_setMethod('inTransaction')
			->_setArgs()
			->_setReturn(false)
			->e();

		$connection = Mock::m('System\Database\MySql\Connection')
			->_setMethod('get')
			->_setArgs('master')
			->_setReturn($pdo)
			->e();

		$updateQuery->connection = $connection;

		try {
			$updateQuery->update();
			$this->throwError('例外が発生すべき箇所で発生していない');
		} catch (DatabaseException $e) {
			$this->compareException('UPDATE文を使用する場合はトランザクションを開始して下さい', $e);
		}
	}

	/**
	 * set
	 * getSetLine
	 */
	public function setAndGetSetLineTest()
	{
		$reflection = new \ReflectionClass('System\Database\MySql\Query\UpdateQuery');
		$updateQuery = $reflection->newInstanceWithoutConstructor();

		$updateQuery->set([
			'hoge' => 1,
			'fuga' => 'aaa',
			'piyo' => new \DateTime('2017-10-10 00:00:00'),
			'mosu' => null
		]);

		$reflection = new \ReflectionClass($updateQuery);
		$method = $reflection->getMethod('getSetLine');
		$method->setAccessible(true);
		$result = $method->invoke($updateQuery);

		$this->compareValue('hoge = 1, fuga = "aaa", piyo = "2017-10-10 00:00:00", mosu = NULL', $result);
	}
}

