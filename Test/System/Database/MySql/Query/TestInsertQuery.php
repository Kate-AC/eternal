<?php

/**
 * INSERTクエリテスト
 */

namespace Test\System\Database\MySql\Query;

use System\Database\MySql\Query\InsertQuery;
use System\Exception\DatabaseException;
use Test\Mock;
use Test\TestHelper;

use Test\TestInsertModel;

class TestInsertQuery extends TestHelper
{
	/**
	 * create
	 */
	public function createTest()
	{
		$insertQuery = Mock::m('System\Database\MySql\Query\InsertQuery');
		$insertQuery
			->_setMethod('getExplainLine')
			->_setArgs()
			->_setReturn('A')
			->e();

		$insertQuery->tableName    = 'B';
		$insertQuery->insertColumn = 'C';
		$insertQuery->insertValue  = 'D';

		$this->compareValue('A INSERT INTO B C VALUES D', $insertQuery->create());
	}

	/**
	 * insert
	 */
	public function insertTest()
	{
		$insertQuery = Mock::m('System\Database\MySql\Query\InsertQuery');
		$query       = 'query';
		$placeholder = 'placeholder';

		$classList   = [
			new TestInsertModel(),
			new TestInsertModel()
		];
		$expectList = $classList;

		$insertQuery->_setMethod('createInsertParts')
			->_setArgs($classList)
			->_setReturn(null)
			->e();

		$keyName = 'id';
		$insertQuery->primaryKeys = [$keyName];

		$insertQuery
			->_setMethod('create')
			->_setArgs()
			->_setReturn($query)
			->e();

		$insertQuery->placeholder = $placeholder;

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

		$lastInsertId = 4;
		$pdo
			->_setMethod('lastInsertId')
			->_setArgs($keyName)
			->_setReturn($lastInsertId)
			->e();

		$connection = Mock::m('System\Database\MySql\Connection')
			->_setMethod('get')
			->_setArgs('master')
			->_setReturn($pdo)
			->e();

		$insertQuery->connection = $connection;
		$this->compareValue(100, $insertQuery->insert($classList));

		$list = [];
		foreach ($expectList as $expect) {
			$expect->id = $lastInsertId;
			$list[] = $expect;
			$lastInsertId++;
		}
		$insertQuery->insert($classList);

		$this->compareValueLax($list, $classList);

		try {
			$insertQuery->insert(null);
			$this->throwError('例外が発生すべき箇所で発生していない');
		} catch (DatabaseException $e) {
			$this->compareException('insertにnullが渡されました', $e, 'insertにnullが渡された場合');
		}
	}

	/**
	 * insert
	 */
	public function insertTestWhenExcepion()
	{
		$insertQuery = Mock::m('System\Database\MySql\Query\InsertQuery');

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

		$insertQuery->connection = $connection;

		try {
			$insertQuery->insert([]);
			$this->throwError('例外が発生すべき箇所で発生していない');
		} catch (DatabaseException $e) {
			$this->compareException('INSERT文を使用する場合はトランザクションを開始して下さい',
				$e,
				'トランザクションを開始していない場合'
			);
		}
	}

	/**
	 * createInsertParts
	 */
	public function createInsertPartsTest()
	{
		$insertQuery = Mock::m('System\Database\MySql\Query\InsertQuery');
		$insertQuery->calledModel = 'Test\TestInsertModel';
		$testModel = TestInsertModel::make([
			'id'       => 1,
			'name'     => 'test',
			'datetime' => '2017-10-10 00:00:00',
			'point'    => 'POINT(0 0)'
		]);

		$insertQuery->createInsertParts($testModel);
		$this->compareValue('(id, name, datetime, point)', $insertQuery->insertColumn, 'カラム名');
		$this->compareValue('(1, "test", "2017-10-10 00:00:00", ST_GeomFromText("POINT(0 0)"))', $insertQuery->insertValue, '値');
	}

	/**
	 * getQuery
	 */
	public function getQueryTest()
	{
		$query = 'A = ?, B = ?, C = ?';

		$insertQuery = Mock::m('System\Database\MySql\Query\InsertQuery');
		$insertQuery->_setMethod('createInsertParts')
			->_setArgs([])
			->_setReturn(null)
			->e();
		$insertQuery->_setMethod('create')
			->_setArgs()
			->_setReturn($query)
			->e();
		$insertQuery->placeholder = [1, 2, 3];
		$this->compareValue('A = 1, B = 2, C = 3', $insertQuery->getQuery([]));
	}
}

namespace Test;

use System\Database\MySql\BaseModel;
use System\Type\Other\Point;

class TestInsertModel extends BaseModel
{
	/**
	 * @model int
	 */
	public $id;

	/**
	 * @model string
	 */
	public $name;

	/**
	 * @model \DateTime
	 */
	public $datetime;

	/**
	 * @model Point
	 */
	public $point;

	public static function make(array $properties)
	{
		$properties['id'] = isset($properties['id']) ? intval($properties['id']) : null;
		$properties['name'] = isset($properties['name']) ? strval($properties['name']) : null;
		$properties['point'] = isset($properties['point']) ? new Point($properties['point']) : null;
		$properties['datetime'] = isset($properties['datetime']) ? new \DateTime($properties['datetime']) : null;

		$instance = new static();
		return $instance($properties);
	}

	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getDatetime()
	{
		return $this->datetime;
	}

	public function getPoint()
	{
		return $this->point;
	}
}
