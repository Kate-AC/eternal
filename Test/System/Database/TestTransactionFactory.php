<?php

/**
 * TransactionFactoryのテスト
 */

namespace Test\System\Database;

use System\Database\TransactionFactory;
use System\Database\Connection;
use Test\Mock;
use Test\TestHelper;

class TestTransactionFactory extends TestHelper
{
	/**
	 * @var Parse
	 */
	private $transactionFactory;

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
		$transactionFactory = Mock::m('System\Database\TransactionFactory');
		
		$pdo = Mock::m()
			->_setMethod('beginTransaction')
			->_setArgs()
			->_setReturn(1)
			->e();

		$pdo->_setMethod('commit')
			->_setArgs()
			->_setReturn(1)
			->e();

		$pdo->_setMethod('rollback')
			->_setArgs()
			->_setReturn(1)
			->e();

		$connection = Mock::m()->_setMethod('get')
			->_setArgs('master')
			->_setReturn($pdo)
			->e();

		$transactionFactory->connection = $connection;

		$this->transactionFactory = $transactionFactory;
	}

	/**
	 * __construct
	 */
	public function __constructTest()
	{
		$this->compareInstance('System\Database\TransactionFactory', new TransactionFactory(new Connection()));
	}

	/**
	 * beginTransaction
	 */
	public function beginTransactionTest()
	{
		$this->compareValue(1, $this->transactionFactory->beginTransaction());
	}

	/**
	 * commit
	 */
	public function commitTest()
	{
		$this->compareValue(1, $this->transactionFactory->commit());
	}

	/**
	 * rollBack
	 */
	public function rollBackTest()
	{
		$this->compareValue(1, $this->transactionFactory->rollBack());
	}


}

