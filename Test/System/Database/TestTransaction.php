<?php

/**
 * Transactionのテスト
 */

namespace Test\System\Database;

use System\Database\Transaction;
use System\Database\Connection;
use Phantom\Phantom;
use Test\TestHelper;

class TestTransaction extends TestHelper
{
    /**
     * @var Parse
     */
    private $transaction;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->common();
    }

    /**
     * 共通処理
     */
    private function common()
    {
        $transaction = Phantom::m('System\Database\Transaction');

        $pdo = Phantom::m()
            ->setMethod('beginTransaction')
            ->setArgs()
            ->setReturn(1)
            ->exec();

        $pdo->setMethod('commit')
            ->setArgs()
            ->setReturn(1)
            ->exec();

        $pdo->setMethod('rollback')
            ->setArgs()
            ->setReturn(1)
            ->exec();

        $connection = Phantom::m()
            ->setMethod('get')
            ->setArgs('master')
            ->setReturn($pdo)
            ->exec();

        $transaction->connection = $connection;

        $this->transaction = $transaction;
    }

    /**
     * __construct
     */
    public function __constructTest()
    {
        $this->compareInstance('System\Database\Transaction', new Transaction(new Connection()));
    }

    /**
     * beginTransaction
     */
    public function beginTransactionTest()
    {
        $this->compareValue(1, $this->transaction->beginTransaction());
    }

    /**
     * commit
     */
    public function commitTest()
    {
        $this->compareValue(1, $this->transaction->commit());
    }

    /**
     * rollBack
     */
    public function rollBackTest()
    {
        $this->compareValue(1, $this->transaction->rollBack());
    }
}
