<?php

/**
 * Connectionのテスト
 */

namespace Test\System\Database;

use System\Database\Connection;
use System\Exception\DatabaseException;
use Phantom\Phantom;
use Test\TestHelper;

class TestConnection extends TestHelper
{
    /**
     * @var string[]
     */
    private $configList = [
        'master' => [
            'use'      => true,
            'host'     => 'localhost',
            'port'     => 3306,
            'database' => 'vega',
            'user'     => 'root',
            'password' => 'root'
        ],
        'slave1' => [
            'use'      => true,
            'host'     => 'localhost',
            'port'     => 3306,
            'database' => 'vega',
            'user'     => 'slave',
            'password' => 'slave'
        ],
        'slave2' => [
            'use'      => true,
            'host'     => 'localhost',
            'port'     => 3306,
            'database' => 'vega',
            'user'     => 'slave',
            'password' => 'slave'
        ],
        'slave3' => [
            'use'      => false,
            'host'     => 'localhost',
            'port'     => 3306,
            'database' => 'vega',
            'user'     => 'slave',
            'password' => 'slave'
        ]
    ];

    /**
     * 共通処理
     */
    public function getConnection()
    {
        $connection = Phantom::m('System\Database\Connection')
            ->setMethod('getConnectionList')
            ->setArgs()
            ->setReturn($this->configList)
            ->exec();

        $connection->pdo = [];

        return $connection;
    }

    /**
     * start
     * getUseConnection
     */
    public function startAndGetUseConnectionTest()
    {
        $match = 0;
        for ($i = 0; $i < 100; $i++) {
            $connection = $this->getConnection();
            $connection->start();
            if ('master' === $connection->getUseConnection()) {
                $match++;
            }
        }
        $this->compareValue(0, $match, 'slaveが存在する場合');

        $configList = $this->configList;
        foreach ($configList as $key => $config) {
            if ('master' !== $key) {
                $configList[$key]['use'] = false;
            }
        }

        $connection = Phantom::m('System\Database\Connection')
            ->setMethod('getConnectionList')
            ->setArgs()
            ->setReturn($configList)
            ->exec();
        $connection->pdo = [];
        $connection->start();
        $this->compareValue('master', $connection->getUseConnection(), 'masterのみの場合');

        $configList = $this->configList;
        foreach ($configList as $key => $config) {
            $configList[$key]['use'] = false;
        }

        $connection = Phantom::m('System\Database\Connection')
            ->setMethod('getConnectionList')
            ->setArgs()
            ->setReturn($configList)
            ->exec();
        $connection->pdo = [];

        try {
            $connection->start();
            $this->throwError('例外が発生すべき箇所で発生していない');
        } catch (DatabaseException $e) {
            $this->compareException('接続が存在しません', $e, '接続が存在しない場合');
        }
    }

    /**
     * getConnectionList
     */
    public function getConnectionListTest()
    {
        $connection = Phantom::m('System\Database\Connection');
        $configList = $connection->getConnectionList();
        $this->compareValue(true, isset($configList['master']), 'マスタ設定の存在確認のみ');
    }

    /**
     * get
     */
    public function getTest()
    {
        $connection = $this->getConnection()
            ->setMethod('getConnectionList')
            ->setArgs()
            ->setReturn($this->configList)
            ->exec();
        $connection->pdo = [];
        $connection->start();

        $this->compareValue(true, $connection->get('master') instanceof \PDO, 'masterのコネクション');
        $this->compareValue(true, $connection->get('slave1') instanceof \PDO, 'slaveのコネクション');

        try {
            $connection->get('aaa');
            $this->throwError('例外が発生すべき箇所で発生していない');
        } catch (DatabaseException $e) {
            $this->compareException('存在しない接続を指定しました', $e, '存在しない接続を指定した場合');
        }
    }

    /**
     * getAuto
     */
    public function getAutoTest()
    {
        $connection = $this->getConnection()
            ->setMethod('getConnectionList')
            ->setArgs()
            ->setReturn($this->configList)
            ->exec();
        $connection->pdo = [];
        $connection->start();

        $property = new \ReflectionProperty('System\Database\Connection', 'pdo');
        $property->setAccessible(true);
        $pdoList = $property->getValue();

        $connection->get('master');
        $this->compareValue(true, $connection->getAuto() instanceof \PDO, 'masterのコネクション');

        $connection->get('slave1');
        $this->compareValue(true, $connection->getAuto() instanceof \PDO, 'slaveのコネクション');
    }
}
