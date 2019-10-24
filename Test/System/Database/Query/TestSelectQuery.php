<?php

/**
 * SELECTクエリテスト
 */

namespace Test\System\Database\Query;

use System\Core\Di\Container;
use System\Database\Connection;
use System\Database\Query\SelectQuery;
use System\Exception\DatabaseException;
use Phantom\Phantom;
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
        $reflection = new \ReflectionClass('System\Database\Query\SelectQuery');

        $selectQuery = $reflection->newInstanceWithoutConstructor();
        $proprety = new \ReflectionProperty($selectQuery, 'tableName');
        $proprety->setAccessible(true);
        $proprety->setValue($selectQuery, 'a');

        $selectSubQuery = $reflection->newInstanceWithoutConstructor();
        $proprety = new \ReflectionProperty($selectSubQuery, 'tableName');
        $proprety->setAccessible(true);
        $proprety->setValue($selectSubQuery, 'b');

        $selectSubQuery->select(['id']);

        $selectQuery->select([
            'hoge' => 'a.id',
            'COUNT(a.name)',
            $selectSubQuery
        ], 'a');

        $method = new \ReflectionMethod($selectQuery, 'getSelectLine');
        $method->setAccessible(true);
        $expected = 'a.id AS hoge, (COUNT(a.name)) AS `_collect___(COUNT(a.name))`, ( SELECT b.id FROM b AS b ) AS ` SELECT b.id FROM b AS b `';
        $this->compareValue($expected, $method->invoke($selectQuery), 'select句を使用した場合');

        $expected = [
            'hoge' => [
                'table'  => 'a',
                'column' => 'id'
            ],
            '` SELECT b.id FROM b AS b `' => [
                'table'  => '_collect',
                'column' => '( SELECT b.id FROM b AS b )'
            ],
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
        $selectQuery = Phantom::m('System\Database\Query\SelectQuery');
        $selectQuery->tableName = 'test_main_model';
        $selectQuery->tableAsName = [
            'test_main_model' => 'test_main_model',
            'test_join_model' => 'test_join_model'
        ];
        $selectQuery->join = [['join' => ['table' => 'test_join_model']]];

        $selectQuery->container = Phantom::m('System\Core\Di\Container')
            ->setMethod('getByTable')
            ->setArgs('test_main_model')
            ->setReturn('Test\TestMainModel')
            ->exec();

        $selectQuery->container
            ->setMethod('getByTable')
            ->setArgs('test_join_model')
            ->setReturn('Test\TestJoinModel')
            ->exec();
        $this->compareValue(
            'test_main_model.hoge AS `test_main_model___test_main_model.hoge`, test_join_model.mosu AS `test_join_model___test_join_model.mosu`',
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
        $reflection  = new \ReflectionClass('System\Database\Query\SelectQuery');
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
        $reflection  = new \ReflectionClass('System\Database\Query\SelectQuery');
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
        $reflection  = new \ReflectionClass('System\Database\Query\SelectQuery');
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
        $reflection  = new \ReflectionClass('System\Database\Query\SelectQuery');
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
        $selectQuery = Phantom::m('System\Database\Query\SelectQuery');
        $selectQuery->tableName = 'hoge';
        $this->compareValue('hoge AS hoge', $selectQuery->getFromLine(), 'fromが空の場合');

        $from = Phantom::m('System\Database\Query\SelectQuery');
        $from->placeholder = [2, 3];
        $from->setMethod('getBeforeQuery')
            ->setArgs()
            ->setReturn('SELECT hoge FROM fuga')
            ->exec();

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
        $selectQuery = Phantom::m('System\Database\Query\SelectQuery');
        $selectQuery->tableAsName = ['hoge' => 'A'];

        $selectQuery
            ->setMethod('getExplainLine')
            ->setArgs()
            ->setReturn('COUNT(hoge.fuga)')
            ->exec();
        $selectQuery
            ->setMethod('getSelectLine')
            ->setArgs()
            ->setReturn('B')
            ->exec();
        $selectQuery->tableName = 'C';
        $selectQuery
            ->setMethod('getIndexHintLine')
            ->setArgs('C')
            ->setReturn('D')
            ->exec();
        $selectQuery
            ->setMethod('getJoinLine')
            ->setArgs()
            ->setReturn('E')
            ->exec();
        $selectQuery
            ->setMethod('getWhereLine')
            ->setArgs()
            ->setReturn('F')
            ->exec();
        $selectQuery
            ->setMethod('getGroupByLine')
            ->setArgs()
            ->setReturn('G')
            ->exec();
        $selectQuery
            ->setMethod('getHavingLine')
            ->setArgs()
            ->setReturn('H')
            ->exec();
        $selectQuery
            ->setMethod('getOrderByLine')
            ->setArgs()
            ->setReturn('I')
            ->exec();
        $selectQuery
            ->setMethod('getLimitLine')
            ->setArgs()
            ->setReturn('J')
            ->exec();
        $selectQuery
            ->setMethod('getForUpdateLine')
            ->setArgs()
            ->setReturn('K')
            ->exec();
        $selectQuery
            ->setMethod('getOffsetLine')
            ->setArgs()
            ->setReturn('L')
            ->exec();

        $this->compareValue('COUNT(hoge.fuga) SELECT B FROM C AS C D E F G H I J K L', $selectQuery->create());
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
        return 'test_main_model';
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
        return 'test_join_model';
    }
}

namespace Test;

use System\Database\Model;

class HogeModel extends Model
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
        return 'hoge';
    }

    public static function getPrimaryKeys()
    {
        return [];
    }
}
