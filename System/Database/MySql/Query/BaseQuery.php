<?php

/**
 * クエリ設定の共通クラス
 */

namespace System\Database\MySql\Query;

use System\Core\Di\Container;
use System\Database\MySql\Connection;
use System\Exception\DatabaseException;
use System\Util\StringOperator;

class BaseQuery
{
	use QueryConditionTrait;

	const INDEX_USE    = 'USE';

	const INDEX_FORCE  = 'FORCE';

	const INDEX_IGNORE = 'IGNORE';

	/**
	 * @var string
	 */
	protected $useConnection;

	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * @var Connection
	 */
	protected $connection;

	/**
	 * @var string
	 */
	protected $calledModel;

	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * @var string[]
	 */
	protected $primaryKeys = [];

	/**
	 * @var mixed[]
	 */
	protected $indexHint = [];

	/**
	 * @var boolean
	 */
	protected $forUpdate = false;

	/**
	 * @var boolean
	 */
	protected $explain = false;

	/**
	 * コンストラクタ
	 *
	 * @param Connection $connection
	 * @param Container  $container
	 * @param string     $calledModel モデルの名前空間
	 * @return self
	 */
	public function __construct(
		Connection $connection,
		Container $container,
		$calledModel
	) {
		$this->calledModel   = $calledModel;
		$this->tableName     = $calledModel::getTableName();
		$this->primaryKeys   = $calledModel::getPrimaryKeys();
		$this->connection    = $connection;
		$this->container     = $container;

		return $this;
	}

	/**
	 * 存在しないメソッドを参照した際に呼ばれる
	 *
	 * @param string  $name
	 * @param mixed[] $argList
	 */
	public function __call($name, $argList)
	{
		throw new DatabaseException(
			sprintf('存在しないメソッド(%s)が呼ばれた(Class: %s)',
				$name,
				get_called_class()
			)
		);
	}

	/**
	 * indexHint
	 *
	 * @param string   $tableName
	 * @param int      $value
	 * @param string[] $index
	 * @return self
	 */
	public function indexHint($tableName, $value, array $index)
	{
		$this->indexHint[$tableName] = ['type' => $value, 'index' => $index];
		return $this;
	}

	/**
	 * FOR UPDATE
	 *
	 * @return self
	 */
	public function forUpdate()
	{
		$this->forUpdate = true;
		return $this;
	}

	/**
	 * EXPLAIN
	 *
	 * @return self
	 */
	public function explain()
	{
		$this->explain = true;
		return $this;
	}

	/**
	 * 実際に流れるクエリを返す
	 * 
	 * @return string
	 */
	public function getQuery()
	{
		$query = $this->create();
		$query = preg_replace('/\ +/', ' ', $query);
		$query = str_replace('?', '%s', $query);
		return vsprintf($query, $this->placeholder);
	}

	/**
	 * プレースホルダ置換前のクエリを返す
	 * 
	 * @return string
	 */
	public function getBeforeQuery()
	{
		$query = $this->create();
		return preg_replace('/\ +/', ' ', $query);
	}

	/**
	 * INDEXを返す
	 *
	 * @param  string $tableName
	 * @return string|null
	 */
	protected function getIndexHintLine($tableName = null)
	{
		if (empty($this->indexHint) || is_null($tableName)) {
			return;
		}
		switch ($this->indexHint[$tableName]['type']) {
			case self::INDEX_USE:
				$indexHint = 'USE INDEX';
				break;
			case self::INDEX_FORCE:
				$indexHint = 'FORCE INDEX';
				break;
			case self::INDEX_IGNORE:
				$indexHint = 'IGNORE INDEX';
				break;
			default:
				throw new DatabaseException(
					sprintf('存在しないインデックスタイプを指定した(%s)', $this->indexHint[$tableName]['type'])
				);
				break;
		}

		return sprintf('%s (%s)', $indexHint, implode(', ', $this->indexHint[$tableName]['index']));
	}

	/**
	 * FOR UPDATEを返す
	 *
	 * @return string|null
	 */
	protected function getForUpdateLine()
	{
		return true === $this->forUpdate ? 'FOR UPDATE' : null;
	}

	/**
	 * EXPLAINを返す
	 *
	 * @return string|null
	 */
	protected function getExplainLine()
	{
		return true === $this->explain ? 'EXPLAIN' : null;
	}
}
