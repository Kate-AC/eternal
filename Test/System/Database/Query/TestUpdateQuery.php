<?php

/**
 * UPDATEクエリテスト
 */

namespace Test\System\Database\Query;

use Phantom\Phantom;
use System\Database\Query\UpdateQuery;
use System\Exception\DatabaseException;
use Test\TestHelper;

class TestUpdateQuery extends TestHelper
{
    /**
     * create
     */
    public function createTest()
    {
        $updateQuery = Phantom::m('System\Database\Query\UpdateQuery')
            ->setMethod('getExplainLine')
            ->setReturn('A')
            ->exec();

        $updateQuery->tableName = 'B';

        $updateQuery
            ->setMethod('getSetLine')
            ->setReturn('C')
            ->exec();

        $updateQuery
            ->setMethod('getWhereLine')
            ->setReturn('D')
            ->exec();

        $this->compareValue('A UPDATE B SET C D', $updateQuery->create());
    }

    /**
     * update
     */
    public function updateTest()
    {
        $updateQuery = Phantom::m('System\Database\Query\UpdateQuery');
        $query       = 'query';
        $placeholder = 'placeholder';

        $updateQuery
            ->setMethod('create')
            ->setArgs()
            ->setReturn($query)
            ->exec();

        $updateQuery->placeholder = $placeholder;

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

        $connection = Phantom::m('System\Database\Connection')
            ->setMethod('get')
            ->setArgs('master')
            ->setReturn($pdo)
            ->exec();

        $updateQuery->connection = $connection;

        $this->compareValue(100, $updateQuery->update());
    }

    /**
     * update
     */
    public function updateTestWhenExcepion()
    {
        $updateQuery = Phantom::m('System\Database\Query\UpdateQuery');

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
        $reflection = new \ReflectionClass('System\Database\Query\UpdateQuery');
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

        $this->compareValue("hoge = 1, fuga = 'aaa', piyo = '2017-10-10 00:00:00', mosu = NULL", $result);
    }
}
