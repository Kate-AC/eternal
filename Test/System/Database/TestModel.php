<?php

/**
 * BaseModelのテスト
 */

namespace Test\System\Database;

use System\Database\TransactionFactory;
use System\Database\Connection;
use System\Database\Model;
use System\Database\Query\DeleteQuery;
use System\Database\Query\InsertQuery;
use System\Database\Query\SelectQuery;
use System\Database\Query\UpdateQuery;
use System\Core\Di\Container;
use Test\TestHelper;

use Test\TestHogeModel;

class TestModel extends TestHelper
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
        $this->compareInstance('System\Database\Model', new Model($this->connection, $this->container));
    }

    /**
     * setProperty
     */
    public function setPropertyTest()
    {
        $testHogeModel = new TestHogeModel();
        $testHogeModel->setProperty('id', 99);
        $this->compareValue(99, $testHogeModel->getId());
    }

    /**
     * setExtendProperty
     * __call
     */
    public function __callAndSetPropertyTest()
    {
        $testHogeModel = new TestHogeModel();
        $testHogeModel->setProperty('id', 99);
        $testHogeModel->setExtendProperty('id', 'hoge');
        $this->compareValue(99, $testHogeModel->hoge);
    }

    /**
     * toArray
     */
    public function toArrayTest()
    {
        $testHogeModel = new TestHogeModel();
        $testHogeModel->setProperty('id', 99);
        $array = $testHogeModel->toArray();
        $this->compareValue(['id' => 99], $array);
    }

    /**
     * __invoke
     */
    public function __invokeTest()
    {
        $testHogeModel = new TestHogeModel();
        $instance  = $testHogeModel(['id' => 99]);
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

        $testHogeModel = new TestHogeModel($this->connection, $this->container);
        $this->compareValueLax(new SelectQuery($this->connection, $this->container, 'Test\TestHogeModel'), $testHogeModel->selectQuery());
    }

    /**
     * insertQuery
     */
    public function insertQueryTest()
    {
        $reflection = new \ReflectionProperty($this->connection, 'useConnection');
        $reflection->setAccessible(true);
        $reflection->setValue($this->connection, 'master');

        $testHogeModel = new TestHogeModel($this->connection, $this->container);
        $this->compareValueLax(new InsertQuery($this->connection, $this->container, 'Test\TestHogeModel'), $testHogeModel->insertQuery());
    }

    /**
     * updateQuery
     */
    public function updateQueryTest()
    {
        $reflection = new \ReflectionProperty($this->connection, 'useConnection');
        $reflection->setAccessible(true);
        $reflection->setValue($this->connection, 'master');

        $testHogeModel = new TestHogeModel($this->connection, $this->container);
        $this->compareValueLax(new UpdateQuery($this->connection, $this->container, 'Test\TestHogeModel'), $testHogeModel->updateQuery());
    }

    /**
     * deleteQuery
     */
    public function deleteQueryTest()
    {
        $reflection = new \ReflectionProperty($this->connection, 'useConnection');
        $reflection->setAccessible(true);
        $reflection->setValue($this->connection, 'master');

        $testHogeModel = new TestHogeModel($this->connection, $this->container);
        $this->compareValueLax(new DeleteQuery($this->connection, $this->container, 'Test\TestHogeModel'), $testHogeModel->deleteQuery());
    }

}

namespace Test;

use System\Database\Model;

class TestHogeModel extends Model
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
