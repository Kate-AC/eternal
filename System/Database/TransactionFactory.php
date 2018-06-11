<?php

/**
 * トランザクションクラス
 */

namespace System\Database;

class TransactionFactory
{
	/**
	 * @var Connection
	 */
	private $connection;

	/**
	 * コンストラクタ
	 *
	 * @param Connection $connection
	 */
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
	}

	/**
	 * トランザクションを開始する
	 */
	public function beginTransaction()
	{
		return $this->connection->get('master')->beginTransaction();
	}

	/**
	 * コミットする
	 */
	public function commit()
	{
		return $this->connection->get('master')->commit();
	}

	/**
	 * ロールバックする
	 */
	public function rollBack()
	{
		return $this->connection->get('master')->rollback();
	}
}