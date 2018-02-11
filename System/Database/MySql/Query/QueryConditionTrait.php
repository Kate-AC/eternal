<?php

/**
 * where句を使用するトレイト
 */

namespace System\Database\MySql\Query;

use System\Exception\DatabaseException;

trait QueryConditionTrait
{
	/**
	 * @var string[]
	 */
	protected $placeholder = [];

	/**
	 * @var string[]
	 */
	protected $condition = [];

	/**
	 * WHERE
	 *
	 * @param string     $column
	 * @param string     $comparison
	 * @param string|int $value
	 */
	public function where($column, $comparison = null, $value = null)
	{
		if (!($column instanceof Condition) && is_null($comparison)) {
			throw new DatabaseException('第2引数がnullの場合は第1引数にConditionクラスを渡す必要があります');
		}

		if (!($value instanceof SelectQuery) && is_object($value)) {
			throw new DatabaseException('第3引数に渡せるクラスはSelectQueryのみです');
		}

		$this->condition[] = [
			'type'       => 'WHERE',
			'column'     => $column,
			'comparison' => $comparison,
			'value'      => $value
		];
		return $this;
	}

	/**
	 * OR
	 *
	 * @param string     $column
	 * @param string     $comparison
	 * @param string|int $value
	 */
	public function otherwise($column, $comparison = null, $value = null)
	{
		if (!($column instanceof Condition) && is_null($comparison)) {
			throw new DatabaseException('第2引数がnullの場合は第1引数にConditionクラスを渡す必要があります');
		}

		if (!($value instanceof SelectQuery) && is_object($value)) {
			throw new DatabaseException('第3引数に渡せるクラスはSelectQueryのみです');
		}

		$this->condition[] = [
			'type'       => 'OR',
			'column'     => $column,
			'comparison' => $comparison,
			'value'      => $value
		];
		return $this;
	}

	/**
	 * WHERE句とOR句をまとめて返す
	 *
	 * @return string
	 */
	public function getConditionLine()
	{
		if (empty($this->condition)) {
			return null;
		}

		$list = [];
		foreach ($this->condition as $condition) {
			if (!empty($list)) {
				$list[] = 'WHERE' === $condition['type'] ? 'AND' : 'OR';
			}

			if ($condition['column'] instanceof Condition) {
				$list[] = $condition['column']->get();
				$this->placeholder = array_merge($this->placeholder, $condition['column']->getPlaceholder());
			} else {
				if (is_array($condition['value'])) {
					$in = [];
					foreach ($condition['value'] as $v) {
						$this->placeholder[] = is_string($v) ? sprintf('"%s"', $v) : $v;
						$in[] = '?';
					}
					$list[] = sprintf('%s %s (%s)', $condition['column'], $condition['comparison'], implode(', ', $in)); 
				} else {
					if ($condition['value'] instanceof SelectQuery) {
						$list[] = sprintf('%s %s (%s)',
							$condition['column'],
							$condition['comparison'],
							$condition['value']->getBeforeQuery()
						); 
						$this->placeholder = array_merge($this->placeholder, $condition['value']->getPlaceholder());
					} else {
						$this->placeholder[] = is_string($condition['value']) ? sprintf('"%s"', $condition['value']) : $condition['value'];
						$list[] = sprintf('%s %s ?', $condition['column'], $condition['comparison']); 
					}
				}
			}
		}
		return sprintf('WHERE %s', implode(' ', $list));
	}

	/**
	 * placeholderの値を返す
	 *
	 * @return string[]
	 */
	public function getPlaceholder()
	{
		return $this->placeholder;
	}
}
