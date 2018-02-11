<?php

/**
 * DELETEクエリテスト
 */

namespace Test\System\Database\MySql\Query;

use System\Database\MySql\Query\DeleteQuery;
use System\Exception\DatabaseException;
use Test\Mock;
use Test\TestHelper;

class TestDeleteQuery extends TestHelper
{
	/**
	 * create
	 */
	public function createTest()
	{
		$deleteQuery = Mock::m('System\Database\MySql\Query\DeleteQuery');
		$deleteQuery
			->_setMethod('getExplainLine')
			->_setArgs()
			->_setReturn('A')
			->e();

		$deleteQuery->tableName = 'B';

		$deleteQuery
			->_setMethod('getConditionLine')
			->_setArgs()
			->_setReturn('C')
			->e();

		$this->compareValue('A DELETE FROM B C', $deleteQuery->create());
	}

	/**
	 * delete
	 */
	public function deleteTest()
	{
		$deleteQuery = Mock::m('System\Database\MySql\Query\DeleteQuery');
		$query       = 'query';
		$placeholder = 'placeholder';

		$deleteQuery
			->_setMethod('create')
			->_setArgs()
			->_setReturn($query)
			->e();

		$deleteQuery->placeholder = $placeholder;

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

		$deleteQuery->connection = $connection;

		$this->compareValue(100, $deleteQuery->delete());
	}

	/**
	 * delete
	 */
	public function deleteTestWhenExcepion()
	{
		$deleteQuery = Mock::m('System\Database\MySql\Query\DeleteQuery');

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

		$deleteQuery->connection = $connection;

		try {
			$deleteQuery->delete();
			$this->throwError('例外が発生すべき箇所で発生していない');
		} catch (DatabaseException $e) {
			$this->compareException('DELETE文を使用する場合はトランザクションを開始して下さい', $e);
		}
	}
}

