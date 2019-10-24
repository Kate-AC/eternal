<?php

/**
 * QueryFetchTraitのテスト
 */

namespace Test\System\Database\Query;

use System\Exception\DatabaseException;
use Phantom\Phantom;
use Test\TestHelper;

class TestQueryFetchTrait extends TestHelper
{
    /**
     * escape
     */
    public function escapeTest()
    {
        $query = Phantom::m('System\Database\Query\SelectQuery');
        $placeholder = ['hoge', 'fuga'];
        $query->placeholder = $placeholder;

        $prepare = Phantom::m()
            ->setMethod('setFetchMode')
            ->setArgs(\PDO::FETCH_NAMED)
            ->setReturn()
            ->exec();

        $prepare->setMethod('execute')
            ->setArgs($placeholder)
            ->setReturn()
            ->exec();

        $pdo = Phantom::m()
            ->setMethod('prepare')
            ->setArgs('query')
            ->setReturn($prepare)
            ->exec();

        $connection = Phantom::m('System\Database\Connection')
            ->setMethod('getAuto')
            ->setArgs()
            ->setReturn($pdo)
            ->exec();

        $query->connection = $connection;

        $this->compareValue($prepare, $query->escape('query'));
    }

    /**
     * fetch
     * fetchAsArray
     * fetchAll
     * fetchAllAsArray
     */
    public function fetchAndFetchAsArrayAndFetchAllAndFetchAllAsArrayTest()
    {
    /*
        $query = Phantom::m('System\Database\Query\SelectQuery')
            ->setMethod('create')
            ->setArgs()
            ->setReturn('query')
            ->exec();

        $placeholder = ["hoge", "fuga"];
        $result      = ['A', 'B'];

        $query->placeholder = $placeholder;

        $prepare = Phantom::m()
            ->setMethod('setFetchMode')
            ->setArgs(\PDO::FETCH_NAMED)
            ->setReturn()
            ->exec();

        $prepare->setMethod('execute')
            ->setArgs($placeholder)
            ->setReturn()
            ->exec();

        $prepare->setMethod('fetch')
            ->setArgs()
            ->setReturn($result[0])
            ->exec();

        $prepare->setMethod('fetchAll')
            ->setArgs()
            ->setReturn($result)
            ->exec();

        $pdo = Phantom::m()
            ->setMethod('prepare')
            ->setArgs('query')
            ->setReturn($prepare)
            ->exec();

        $connection = Phantom::m('System\Database\Connection')
            ->setMethod('getAuto')
            ->setArgs()
            ->setReturn($pdo)
            ->exec();

        $query->connection = $connection;

        $query->setMethod('unite')
            ->setArgs([$result[0]])
            ->setReturn([$result[0]])
            ->exec();

        $query->setMethod('uniteArray')
            ->setArgs([$result[0]])
            ->setReturn([$result[0]])
            ->exec();

        $query->setMethod('unite')
            ->setArgs($result)
            ->setReturn($result)
            ->exec();

        $query->setMethod('uniteArray')
            ->setArgs($result)
            ->setReturn($result)
            ->exec();

        $this->compareValue('A', $query->fetch(), 'fetch');
        $this->compareValue('A', $query->fetchAsArray(), 'fetchAsArray');
        $this->compareValue($result, $query->fetchAll(), 'fetchAll');
        $this->compareValue($result, $query->fetchAllAsArray(), 'fetchAllAsArray');
    */
    }

    /**
     * fetchAllByKey
     * fetchAllAsArrayByKey
     */
    public function fetchAllByKeyAndFetchAllAsArrayByKeyTest()
    {
    /*
        $query = Phantom::m('System\Database\Query\SelectQuery')
            ->setMethod('create')
            ->setArgs()
            ->setReturn('query')
            ->exec();

        $result   = ['A', 'B'];
        $prepared = Phantom::m();

        $prepared->setMethod('fetchAll')
            ->setArgs()
            ->setReturn($result)
            ->exec();

        $query->setMethod('escape')
            ->setArgs('query')
            ->setReturn($prepared)
            ->exec();

        $query->setMethod('unite')
            ->setArgs($result, 'hoge')
            ->setReturn($result)
            ->exec();

        $query->setMethod('uniteArray')
            ->setArgs($result, 'hoge')
            ->setReturn($result)
            ->exec();

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
    */
    }

    /**
     * count
     */
    public function countTest()
    {
        $query = Phantom::m('System\Database\Query\SelectQuery')
            ->setMethod('create')
            ->setArgs()
            ->setReturn('query')
            ->exec();

        $placeholder = ['hoge', 'fuga'];
        $query->placeholder = $placeholder;

        $prepare = Phantom::m()
            ->setMethod('setFetchMode')
            ->setArgs(\PDO::FETCH_NAMED)
            ->setReturn()
            ->exec();

        $prepare->setMethod('rowCount')
            ->setArgs()
            ->setReturn(99)
            ->exec();

        $prepare->setMethod('execute')
            ->setArgs($placeholder)
            ->setReturn()
            ->exec();

        $pdo = Phantom::m()
            ->setMethod('prepare')
            ->setArgs('query')
            ->setReturn($prepare)
            ->exec();

        $connection = Phantom::m('System\Database\Connection')
            ->setMethod('getAuto')
            ->setArgs()
            ->setReturn($pdo)
            ->exec();

        $query->connection = $connection;

        $this->compareValue(99, $query->count());
    }
}

