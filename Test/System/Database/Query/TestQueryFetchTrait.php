<?php

/**
 * QueryFetchTraitのテスト
 */

namespace Test\System\Database\Query;

use System\Exception\DatabaseException;
use Test\Mock;
use Test\TestHelper;

class TestQueryFetchTrait extends TestHelper
{
	/**
	 * fetch
	 * fetchAsArray
	 * fetchAll
	 * fetchAllAsArray
	 */
	public function fetchAndFetchAsArrayAndFetchAllAndFetchAllAsArrayTest()
	{
		$query = Mock::m('System\Database\Query\SelectQuery');
		$query->_setMethod('create')
			->_setArgs()
			->_setReturn('query')
			->e();

		$result   = ['A', 'B'];
		$prepared = Mock::m();

		$prepared->_setMethod('fetch')
			->_setArgs()
			->_setReturn($result[0])
			->e();
		$prepared->_setMethod('fetchAll')
			->_setArgs()
			->_setReturn($result)
			->e();

		$query->_setMethod('escape')
			->_setArgs('query')
			->_setReturn($prepared)
			->e();

		$query->_setMethod('unite')
			->_setArgs([$result[0]])
			->_setReturn([$result[0]])
			->e();

		$query->_setMethod('uniteArray')
			->_setArgs([$result[0]])
			->_setReturn([$result[0]])
			->e();

		$query->_setMethod('unite')
			->_setArgs($result)
			->_setReturn($result)
			->e();

		$query->_setMethod('uniteArray')
			->_setArgs($result)
			->_setReturn($result)
			->e();

		$this->compareValue('A', $query->fetch(), 'fetch');
		$this->compareValue('A', $query->fetchAsArray(), 'fetchAsArray');
		$this->compareValue($result, $query->fetchAll(), 'fetchAll');
		$this->compareValue($result, $query->fetchAllAsArray(), 'fetchAllAsArray');
	}

	/**
	 * fetch
	 */
	public function fetchTestWhenNull()
	{
		$query = Mock::m('System\Database\Query\SelectQuery');
		$query->_setMethod('create')
			->_setArgs()
			->_setReturn('query')
			->e();

		$prepared = Mock::m();

		$prepared->_setMethod('fetch')
			->_setArgs()
			->_setReturn(false)
			->e();

		$query->_setMethod('escape')
			->_setArgs('query')
			->_setReturn($prepared)
			->e();

		$this->compareValue(null, $query->fetch(), 'fetch');
	}

	/**
	 * fetchAllByKey
	 * fetchAllAsArrayByKey
	 */
	public function fetchAllByKeyAndFetchAllAsArrayByKeyTest()
	{
		$query = Mock::m('System\Database\Query\SelectQuery');
		$query->_setMethod('create')
			->_setArgs()
			->_setReturn('query')
			->e();

		$result   = ['A', 'B'];
		$prepared = Mock::m();

		$prepared->_setMethod('fetchAll')
			->_setArgs()
			->_setReturn($result)
			->e();

		$query->_setMethod('escape')
			->_setArgs('query')
			->_setReturn($prepared)
			->e();

		$query->_setMethod('unite')
			->_setArgs($result, 'hoge')
			->_setReturn($result)
			->e();

		$query->_setMethod('uniteArray')
			->_setArgs($result, 'hoge')
			->_setReturn($result)
			->e();

		$query->primaryKeys = ['hoge', 'fuga'];

		$this->compareValue($result, $query->fetchAllByKey('hoge'), 'fetchAllByKey 存在するキー');
		$this->compareValue($result, $query->fetchAllAsArrayByKey('hoge'), 'fatchAllAsArrayByKey 存在するキー');
		$this->compareValue($result, $query->fetchAllByKey(), 'キーを指定しない');

		$query->primaryKeys = [];
		try {
			$query->fetchAllByKey();
			$this->throwError('例外が発生すべき箇所で発生していない');
		} catch (DatabaseException $e) {
			$this->compareException('fetchAllByKey', $e, 'fetchAllByKey 存在しないキー');
		}

		try {
			$query->fetchAllAsArrayByKey();
			$this->throwError('例外が発生すべき箇所で発生していない');
		} catch (DatabaseException $e) {
			$this->compareException('fetchAllAsArrayByKey', $e, 'fetchAllAsArrayByKey 存在しないキー');
		}
	}

	/**
	 * count
	 */
	public function countTest()
	{
		$query = Mock::m('System\Database\Query\SelectQuery');
		$query->_setMethod('create')
			->_setArgs()
			->_setReturn('query')
			->e();

		$prepared = Mock::m()
			->_setMethod('rowCount')
			->_setArgs()
			->_setReturn(99)
			->e();

		$query->_setMethod('escape')
			->_setArgs('query')
			->_setReturn($prepared)
			->e();

		$this->compareValue(99, $query->count());
	}

	/**
	 * escape
	 */
	public function escapeTest()
	{
		$query = Mock::m('System\Database\Query\SelectQuery');
		$placeholder = ['hoge', 'fuga'];
		$query->placeholder = $placeholder;

		$prepare = Mock::m()
			->_setMethod('setFetchMode')
			->_setArgs(\PDO::FETCH_NAMED)
			->_setReturn()
			->e();

		$prepare->_setMethod('execute')
			->_setArgs($placeholder)
			->_setReturn()
			->e();

		$pdo = Mock::m()
			->_setMethod('prepare')
			->_setArgs('query')
			->_setReturn($prepare)
			->e();

		$connection = Mock::m('System\Database\Connection')
			->_setMethod('getAuto')
			->_setArgs()
			->_setReturn($pdo)
			->e();

		$query->connection = $connection;

		$this->compareValue($prepare, $query->escape('query'));
	}
}

