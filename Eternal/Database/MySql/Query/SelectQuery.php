<?php

/**
 * SELECTクエリ
 */

namespace System\Database\MySql\Query;

use System\Util\StringOperator;

class SelectQuery extends BaseQuery
{
	use QueryFactoryTrait;

	use QueryFetchTrait;

	/**
	 * @var string[]
	 */
	private $select = [];

	/**
	 * @var string
	 */
	private $from = [];

	/**
	 * @var string[]
	 */
	private $formatedJoin = [];

	/**
	 * @var string[]
	 */
	private $join = [];

	/**
	 * @var string[]
	 */
	private $groupBy = [];

	/**
	 * @var string[]
	 */
	private $orderBy = [];

	/**
	 * @var int
	 */
	private $offset;

	/**
	 * @var int
	 */
	private $limit;

	/**
	 * @var string[]
	 */
	private $tableAsName = [];

	/**
	 * @var string[]
	 */
	private $propertyAsName = [];

	/**
	 * @var string
	 */
	protected $isSubQuery = false;

	/**
	 * サブクエリ扱いにする
	 *
	 * @return SelectQuery
	 */
	public function isSubQuery()
	{
		$this->isSubQuery = true;
		return $this;
	}

	/**
	 * SELECT
	 *
	 * @param string[]
	 * @param string
	 * @return SelectQuery
	 */
	public function select(array $argList, $asName = null)
	{
		if (!is_null($asName)) {
			$this->tableAsName[$asName] = $this->tableName;
		}

		foreach ($argList as $key => $arg) {
			//intの場合はAS句がない
			if (is_int($key)) {
				$str = $arg;
				$as  = null;
			} else {
				$str = $arg;
				$as  = $key;
			}

			if ($str instanceof SelectQuery) {
				$table  = null;
				$column = $str->isSubQuery();
			} elseif (false !== strpos($str, '(')) {
				$table  = null;
				$column = $str;
			} else {
				if (false !== strpos($str, '.')) {
					$list   = explode('.', $str);
					$table  = $list[0];
					$column = $list[1];
				} else {
					$table  = $this->tableName;
					$column = $str;
				}
			}

			$this->select[] = [
				'table'  => $table,
				'column' => $column,
				'as'     => $as 
			];
		}

		return $this;
	}

	/**
	 * FROM
	 *
	 * @param string      $as
	 * @param SelectQuery $from
	 * @return SelectQuery
	 */
	public function from($as, SelectQuery $from)
	{
		$this->from = [
			'as'   => $as,
			'from' => $from->isSubQuery()
		];
		return $this;
	}

	/**
	 * JOIN
	 *
	 * @param string[] $join [join先のテーブル => AS] or [join先のテーブル]
	 * @param string[] $on   [テーブル.カラム, テーブル.カラム]
	 * @return SelectQuery
	 */
	public function join(array $join, array $onList)
	{
		if (is_int(key($join))) {
			$as    = null;
			$table = current($join);
		} else {
			$as    = key($join);
			$table = current($join);
		}

		if (!is_null($as)) {
			$this->tableAsName[$as] = $table;
		}

		$on = [];
		foreach ($onList as $left => $right) {
			$listA = explode('.', $left);
			$listB = explode('.', $right);

			$on[] = [
				'a' => ['table' => $listA[0], 'column' => $listA[1]],
				'b' => ['table' => $listB[0], 'column' => $listB[1]]
			];
		}

		$this->join[] = [
			'join' => [
				'table' => $table,
				'as'    => $as
			],
			'on' => $on
		];

		return $this;
	}

	/**
	 * GROUP BY
	 *
	 * @param string $groupBy
	 * @return SelectQuery
	 */
	public function groupBy($groupBy)
	{
		$this->groupBy[] = $groupBy;
		return $this;
	}

	/**
	 * ORDER BY
	 *
	 * @param string $orderBy
	 * @return SelectQuery
	 */
	public function orderBy($orderBy)
	{
		$this->orderBy[] = $orderBy;
		return $this;
	}

	/**
	 * OFFSET
	 *
	 * @param int $offset
	 * @return SelectQuery
	 */
	public function offset($offset)
	{
		$this->offset = $offset;
		return $this;
	}

	/**
	 * LIMIT
	 *
	 * @param int $limit
	 * @return SelectQuery
	 */
	public function limit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * SELECT句にして返す
	 *
	 * @return string
	 */
	private function getSelectLine()
	{
		$columnList = [];
		if (empty($this->select)) {
			$useTableList = [$this->tableName];
			foreach ($this->join as $join) {
				$useTableList[] = $join['join']['table'];
			}

			foreach ($useTableList as $useTable) {
				$useModel = $this->container->getByTable($useTable);
				$propertyList = (new \ReflectionClass($useModel))->getProperties();
				foreach ($propertyList as $property) {
					if (is_null($type = StringOperator::getDocCommentByModelProperty($property->getDocComment()))) {
						continue;
					}

					$as = null;
					if (false === $this->isSubQuery) {
						$as = sprintf('AS %s___%s', $useModel::getTableName(), $property->name);
					}
					$column = sprintf('%s.%s', $useModel::getTableName(), $property->name);
					$columnList[] = 'Point' === $type ? sprintf('ASTEXT(%s) %s', $column, $as) : sprintf('%s %s', $column, $as);
				}
			}
		} else {
			foreach ($this->select as $i => $select) {
				if ($select['column'] instanceof SelectQuery) {
					$this->placeholder = array_merge($this->placeholder, $select['column']->getPlaceholder());
					$select['column'] = sprintf('(%s)', $select['column']->getBeforeQuery());
				}

				//SQL関数ではない場合
				if (!is_null($select['table'])) {
					//テーブル名に別名がついているものをすべて本来の名前に直す
					foreach ($this->tableAsName as $as => $original) {
						if ($select['table'] === $as) {
							$select['table'] = $original;
							break;
						}
					}

					if (!is_null($select['as'])) {
						//元々のAS句を保持しておく
						$this->propertyAsName[$select['as']] = [
							'table'  => $select['table'],
							'column' => $select['column']
						];
					}

					//JOIN時に同名のカラムがあると区別できないので、AS句がない場合は生成する
					if (false === $this->isSubQuery) {
						$select['as'] = sprintf('AS %s___%s', $select['table'], $select['column']);
					}
					$columnList[] = sprintf('%s.%s %s', $select['table'], $select['column'], $select['as']);
				} else {
					foreach ($this->tableAsName as $as => $original) {
						$condition        = sprintf('/([\,\ \(]{1})%s(\.{1})/i', $as);
						$replace          = sprintf('$1%s$2', $original);
						$select['column'] = preg_replace($condition, $replace, $select['column']);
					}

					if (is_null($select['as'])) {
						$select['as'] = $this->select[$i]['column'];
						$columnList[] = $select['column'];
					} else {
						$columnList[] = sprintf('%s AS %s', $select['column'], $select['as']);
					}

					//元々のAS句を保持しておく
					$this->propertyAsName[$select['as']] = [
						'table'  => '_collect',
						'column' => $select['column']
					];
				}
			}
		}

		return implode(', ', $columnList);
	}

	/**
	 * FROM句にして返す
	 *
	 * @return string
	 */
	private function getFromLine()
	{
		if (empty($this->from)) {
			return $this->tableName;
		}
		$this->placeholder = array_merge($this->placeholder, $this->from['from']->getPlaceholder());
		return  sprintf('(%s) AS %s', $this->from['from']->getBeforeQuery(), $this->from['as']);
	}

	/**
	 * JOIN句にして返す
	 *
	 * @return string
	 */
	private function getJoinLine()
	{
		if (empty($this->join)) {
			return null;
		}

		$array  = [];
		foreach ($this->join as $i => $value) {
			//内部的に元のテーブル名で解釈されるため、AS句は不要
			$array[] = sprintf('LEFT JOIN %s %s', 
				$value['join']['table'],
				$this->getIndexHintLine($value['join']['table'])
			);

			$onList = [];
			foreach ($value['on'] as $key => $on) {
				foreach ($this->tableAsName as $as => $original) {
					if ($value['on'][$key]['a']['table'] === $as) {
						$value['on'][$key]['a']['table'] = $original;
					}

					if ($value['on'][$key]['b']['table'] === $as) {
						$value['on'][$key]['b']['table'] = $original;
					}
				}

				$onList[] = empty($onList) ? 'ON' : 'AND';
				$onList[] = sprintf('%s.%s = %s.%s',
					$value['on'][$key]['a']['table'],
					$value['on'][$key]['a']['column'],
					$value['on'][$key]['b']['table'],
					$value['on'][$key]['b']['column']
				);
			}
			$array = array_merge($array, $onList);
			$this->formatedJoin[] = $value;
		}

		return implode(' ', $array);
	}

	/**
	 * GROUP_BY句にして返す
	 *
	 * @return string|null
	 */
	private function getGroupByLine()
	{
		return !empty($this->groupBy) ? sprintf('GROUP BY %s', implode(', ', $this->groupBy)) : null;
	}

	/**
	 * ORDER BY句にして返す
	 *
	 * @return string|null
	 */
	private function getOrderByLine()
	{
		return !empty($this->orderBy) ? sprintf('ORDER BY %s', implode(', ', $this->orderBy)) : null;
	}

	/**
	 * OFFSET句にして返す
	 *
	 * @return string|null
	 */
	private function getOffsetLine()
	{
		return !empty($this->offset) ? sprintf('OFFSET %s', $this->offset) : null;
	}

	/**
	 * LIMIT句にして返す
	 *
	 * @return string|null
	 */
	private function getLimitLine()
	{
		return !empty($this->limit) ? sprintf('LIMIT %s', $this->limit) : null;
	}

	/**
	 * クエリを組み立てる
	 * 
	 * @return string
	 */
	public function create()
	{
		$query = sprintf('%s SELECT %s FROM %s %s %s %s %s %s %s %s %s',
			$this->getExplainLine(),
			$this->getSelectLine(),
			$this->getFromLine(),
			$this->getIndexHintLine($this->tableName),
			$this->getJoinLine(),
			$this->getConditionLine(),
			$this->getGroupByLine(),
			$this->getOrderByLine(),
			$this->getLimitLine(),
			$this->getForUpdateLine(),
			$this->getOffsetLine()
		);

		foreach ($this->tableAsName as $as => $table) {
			$condition = sprintf('/(\ |\()?%s(\.{1})/i', $as);
			$replace   = sprintf('$1%s$2', $table);
			$query     = preg_replace($condition, $replace, $query);
		}

		return $query;
	}
}
