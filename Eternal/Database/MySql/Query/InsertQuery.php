<?php

/**
 * INSERTクエリ
 */

namespace System\Database\MySql\Query;

use System\Exception\DatabaseException;
use System\Util\StringOperator;
use System\Type\Other\Point;

class InsertQuery extends BaseQuery
{
	/**
	 * @var string
	 */
	private $insertColumn;

	/**
	 * @var string
	 */
	private $insertValue;

	/**
	 * クエリを組み立てる
	 * 
	 * @return string
	 */
	public function create()
	{
		return sprintf('%s INSERT INTO %s %s VALUES %s',
			$this->getExplainLine(),
			$this->tableName,
			$this->insertColumn,
			$this->insertValue
		);
	}

	/**
	 * インサートクエリの一部分を作成する
	 *
	 * @param obj|obj[] $classList
	 * @return int 挿入行数
	 */
	private function createInsertParts(&$classList)
	{
		$isArray = true;
		if (!is_array($classList)) {
			$isArray = false;
			$classList = [$classList];
		}

		$columnList   = [];
		$getterList   = [];
		$valueSetList = [];

		$propertyList = (new \ReflectionClass($this->calledModel))->getProperties();
		foreach ($propertyList as $property) {
			if (is_null($type = StringOperator::getDocCommentByModelProperty($property->getDocComment()))) {
				continue;
			}
			$getterList[] = StringOperator::columnToGetter($property->getName());
			$columnList[] = $property->getName();
		}
		$this->insertColumn = sprintf('(%s)', implode(', ', $columnList));

		foreach ($classList as $class) {
			$valueList = [];
			foreach ($getterList as $getter) {
				if (is_null($class->$getter())) {
					$valueList[] = 'NULL';
				} else if ($class->$getter() instanceof \DateTime || $class->$getter() instanceof \DateTimeImmutable) {
					$valueList[] = sprintf('"%s"', $class->$getter()->format('Y-m-d H:i:s'));
				} elseif ($class->$getter() instanceof Point) {
					$valueList[] = sprintf('ST_GeomFromText("POINT(%s %s)")', $class->$getter()->getLng(), $class->$getter()->getLat());
				} elseif (is_string($class->$getter())) {
					$valueList[] = sprintf('"%s"', $class->$getter());
				} else {
					$valueList[] = $class->$getter();
				}
			}

			$valueSetList[] = sprintf('(%s)', implode(', ', $valueList));
		}
		$this->insertValue = implode(', ', $valueSetList);
	}

	/**
	 * インサートする
	 *
	 * @param obj|obj[] $classList
	 * @return int 挿入行数
	 */
	public function insert(&$classList, $keyName = null)
	{
		if (true !== $this->connection->get('master')->inTransaction()) {
			throw new DatabaseException('INSERT文を使用する場合はトランザクションを開始して下さい');
		}

		if (is_null($classList)) {
			throw new DatabaseException('insertにnullが渡されました');
		}

		$isArray = true;
		if (!is_array($classList)) {
			$isArray   = false;
			$classList = [$classList];
		}

		$this->createInsertParts($classList);
		$query    = $this->create();
		$prepared = $this->connection->get('master')->prepare($query);
		$prepared->execute($this->placeholder);

		if (is_null($keyName) ) {
			if (!empty($this->primaryKeys)) {
				$keyName = $this->primaryKeys[0];
			}
		}

		if (!is_null($keyName)) {
			$lastInsertId = (int)$this->connection->get('master')->lastInsertId($keyName);
			foreach ($classList as $key => $class) {
				$classList[$key]->setProperty($keyName, $lastInsertId);
				$lastInsertId++;
			}
		}

		if (false === $isArray) {
			$classList = $classList[0];
		}

		return $prepared->rowCount();
	}

	/**
	 * 実際に流れるクエリを返す(オーバーライド)
	 * 
	 * @return string[]
	 */
	public function getQuery($classList)
	{
		$this->createInsertParts($classList);
		$query = $this->create();
		$query = str_replace('?', '%s', $query);

		return vsprintf($query, $this->placeholder);
	}
}
