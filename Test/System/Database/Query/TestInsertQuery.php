<?php

/**
 * INSERTクエリテスト
 */

namespace Test\System\Database\Query;

use Phantom\Phantom;
use System\Database\Query\InsertQuery;
use System\Exception\DatabaseException;
use Test\TestHelper;

use Test\TestInsertModel;

class TestInsertQuery extends TestHelper
{
    /**
     * create
     */
    public function createTest()
    {
        $insertQuery = Phantom::m('System\Database\Query\InsertQuery')
            ->setMethod('getExplainLine')
            ->setArgs()
            ->setReturn('A')
            ->exec();

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
        $insertQuery = Phantom::m('System\Database\Query\InsertQuery');
        $query       = 'query';
        $placeholder = 'placeholder';

        $classList   = [
            new TestInsertModel(),
            new TestInsertModel()
        ];
        $expectList = $classList;

        $insertQuery
            ->setMethod('createInsertParts')
            ->setArgs($classList)
            ->setReturn(null)
            ->exec();

        $keyName = 'id';
        $insertQuery->primaryKeys = [$keyName];

        $insertQuery
            ->setMethod('create')
            ->setArgs()
            ->setReturn($query)
            ->exec();

        $insertQuery->placeholder = $placeholder;

        $prepare = Phantom::m()
            ->setMethod('execute')
            ->setArgs($placeholder)
            ->setReturn(null)
            ->exec();

        $prepare
            ->setMethod('rowCount')
            ->setArgs()
            ->setReturn(100)
            ->exec();

        $pdo = Phantom::m()
            ->setMethod('inTransaction')
            ->setArgs()
            ->setReturn(true)
            ->exec();

        $pdo->setMethod('prepare')
            ->setArgs($query)
            ->setReturn($prepare)
            ->exec();

        $lastInsertId = 4;
        $pdo
            ->setMethod('lastInsertId')
            ->setArgs($keyName)
            ->setReturn($lastInsertId)
            ->exec();

        $connection = Phantom::m('System\Database\Connection')
            ->setMethod('get')
            ->setArgs('master')
            ->setReturn($pdo)
            ->exec();

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
        $insertQuery = Phantom::m('System\Database\Query\InsertQuery');

        $pdo = Phantom::m()
            ->setMethod('inTransaction')
            ->setArgs()
            ->setReturn(false)
            ->exec();

        $connection = Phantom::m('System\Database\Connection')
            ->setMethod('get')
            ->setArgs('master')
            ->setReturn($pdo)
            ->exec();

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
        $insertQuery = Phantom::m('System\Database\Query\InsertQuery');
        $insertQuery->calledModel = 'Test\TestInsertModel';
        $testModel = TestInsertModel::make([
            'id'       => 1,
            'name'     => 'test',
            'datetime' => '2017-10-10 00:00:00',
            'point'    => 'POINT(0 0)'
        ]);

        $insertQuery->createInsertParts($testModel);
        $this->compareValue('(id, name, datetime, point)', $insertQuery->insertColumn, 'カラム名');
        $this->compareValue("(1, 'test', '2017-10-10 00:00:00', ST_GeomFromText('POINT(0 0)'))", $insertQuery->insertValue, '値');
    }

    /**
     * getQuery
     */
    public function getQueryTest()
    {
        $query = 'A = ?, B = ?, C = ?';

        $insertQuery = Phantom::m('System\Database\Query\InsertQuery')
            ->setMethod('createInsertParts')
            ->setArgs([])
            ->setReturn(null)
            ->exec();
        $insertQuery
            ->setMethod('create')
            ->setArgs()
            ->setReturn($query)
            ->exec();
        $insertQuery->placeholder = [1, 2, 3];
        $this->compareValue('A = 1, B = 2, C = 3', $insertQuery->getQuery([]));
    }
}

namespace Test;

use System\Database\Model;
use System\Type\Other\Point;

class TestInsertModel extends Model
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
