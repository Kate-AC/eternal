<?php

/**
 * DELETEクエリテスト
 */

namespace Test\System\Database\Query;

use Phantom\Phantom;
use System\Database\Query\DeleteQuery;
use System\Exception\DatabaseException;
use Test\TestHelper;

class TestDeleteQuery extends TestHelper
{
    /**
     * create
     */
    public function createTest()
    {
        $deleteQuery = Phantom::m('System\Database\Query\DeleteQuery')
            ->setMethod('getExplainLine')
            ->setArgs()
            ->setReturn('A')
            ->exec();

        $deleteQuery->tableName = 'B';

        $deleteQuery
            ->setMethod('getWhereLine')
            ->setArgs()
            ->setReturn('C')
            ->exec();

        $this->compareValue('A DELETE FROM B C', $deleteQuery->create());
    }

    /**
     * delete
     */
    public function deleteTest()
    {
        $deleteQuery = Phantom::m('System\Database\Query\DeleteQuery');
        $query       = 'query';
        $placeholder = 'placeholder';

        $deleteQuery
            ->setMethod('create')
            ->setArgs()
            ->setReturn($query)
            ->exec();

        $deleteQuery->placeholder = $placeholder;

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

        $deleteQuery->connection = $connection;

        $this->compareValue(100, $deleteQuery->delete());
    }

    /**
     * delete
     */
    public function deleteTestWhenExcepion()
    {
        $deleteQuery = Phantom::m('System\Database\Query\DeleteQuery');

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

        $deleteQuery->connection = $connection;

        try {
            $deleteQuery->delete();
            $this->throwError('例外が発生すべき箇所で発生していない');
        } catch (DatabaseException $e) {
            $this->compareException('DELETE文を使用する場合はトランザクションを開始して下さい', $e);
        }
    }
}
