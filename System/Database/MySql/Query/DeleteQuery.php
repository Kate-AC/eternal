<?php

/**
 * DELETEクエリ
 */

namespace System\Database\MySql\Query;

use System\Exception\DatabaseException;
use System\Util\StringOperator;

class DeleteQuery extends BaseQuery
{
	/**
	 * クエリを組み立てる
	 * 
	 * @return string
	 */
	public function create()
	{
		$query = sprintf('%s DELETE FROM %s %s',
			$this->getExplainLine(),
			$this->tableName,
			$this->getConditionLine()
	);

		return $query;
	}

	/**
	 * デリートする
	 * 
	 * @return int
	 */
	public function delete()
	{
		if (true !== $this->connection->get('master')->inTransaction()) {
			throw new DatabaseException('DELETE文を使用する場合はトランザクションを開始して下さい');
		}

		$query    = $this->create();
		$prepared = $this->connection->get('master')->prepare($query);
		$prepared->execute($this->placeholder);
		return $prepared->rowCount();
	}
}
