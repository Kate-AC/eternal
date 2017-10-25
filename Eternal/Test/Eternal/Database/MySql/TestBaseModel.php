<?php

/**
 * BaseModelのテスト
 */

namespace Test\System\Database\MySql;

use System\Database\MySql\TransactionFactory;
use System\Database\MySql\Connection;
use System\Database\MySql\Query\DeleteQuery;
use System\Database\MySql\Query\InsertQuery;
use System\Database\MySql\Query\SelectQuery;
use System\Database\MySql\Query\UpdateQuery;
use System\Core\Di\Container;
use Test\Mock;
use Test\Parse;
use Test\TestHelper;

use Test\TestModel;

class TestBaseModel extends TestHelper
{
	/**
	 * @var Connection
	 */
	protected $connection;

	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * コンストラクタ
	 */
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
		$this->compareInstance('System\Database\MySql\BaseModel', new TestModel($this->connection, $this->container));
	}

	/**
	 * setProperty
	 */
	public function setPropertyTest()
	{
		$testModel = new TestModel();
		$testModel->setProperty('id', 99);
		$this->compareValue(99, $testModel->getId());
	}

	/**
	 * setExtendProperty
	 * __call
	 */
	public function __callAndSetPropertyTest()
	{
		$testModel = new TestModel();
		$testModel->setProperty('id', 99);
		$testModel->setExtendProperty('id', 'hoge');
		$this->compareValue(99, $testModel->hoge());
	}

	/**
	 * toArray
	 */
	public function toArrayTest()
	{
		$testModel = new TestModel();
		$testModel->setProperty('id', 99);
		$array = $testModel->toArray();
		$this->compareValue(['id' => 99], $array);
	}

	/**
	 * __invoke
	 */
	public function __invokeTest()
	{
		$testModel = new TestModel();
		$instance  = $testModel(['id' => 99]);
		$this->compareValue(99, $instance->getId());
	}

	/**
	 * selectQuery
	 */
	public function selectQueryTest()
	{
		$reflection = new \ReflectionProperty($this->connection, 'useConnection');
		$reflection->setAccessible(true);
		$reflection->setValue($this->connection, 'slave1');

		$testModel = new TestModel($this->connection, $this->container);
		$this->compareValueLax(new SelectQuery($this->connection, $this->container, 'Test\TestModel'), $testModel->selectQuery());
	}

	/**
	 * insertQuery
	 */
	public function insertQueryTest()
	{
		$reflection = new \ReflectionProperty($this->connection, 'useConnection');
		$reflection->setAccessible(true);
		$reflection->setValue($this->connection, 'master');

		$testModel = new TestModel($this->connection, $this->container);
		$this->compareValueLax(new InsertQuery($this->connection, $this->container, 'Test\TestModel'), $testModel->insertQuery());
	}

	/**
	 * updateQuery
	 */
	public function updateQueryTest()
	{
		$reflection = new \ReflectionProperty($this->connection, 'useConnection');
		$reflection->setAccessible(true);
		$reflection->setValue($this->connection, 'master');

		$testModel = new TestModel($this->connection, $this->container);
		$this->compareValueLax(new UpdateQuery($this->connection, $this->container, 'Test\TestModel'), $testModel->updateQuery());
	}

	/**
	 * deleteQuery
	 */
	public function deleteQueryTest()
	{
		$reflection = new \ReflectionProperty($this->connection, 'useConnection');
		$reflection->setAccessible(true);
		$reflection->setValue($this->connection, 'master');

		$testModel = new TestModel($this->connection, $this->container);
		$this->compareValueLax(new DeleteQuery($this->connection, $this->container, 'Test\TestModel'), $testModel->deleteQuery());
	}

}

namespace Test;

use System\Database\MySql\BaseModel;

class TestModel extends BaseModel
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
		return 'test_tbl';
	}

	public static function getPrimaryKeys()
	{
		return [];
	}
}
