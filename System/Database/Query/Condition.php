<?php

/**
 * Condition
 */

namespace System\Database\Query;

class Condition
{
	use QueryConditionTrait;

	/**
	 * conditionを取得する
	 *
	 * @return string
	 */
	public function get()
	{
		$list = [];
		foreach ($this->condition as $condition) {
			if (!empty($list)) {
				$list[] = 'WHERE' === $condition['type'] ? 'AND' : 'OR';
			}

			if (is_string($condition['column']) && is_null($condition['comparison'])) {
				$list[] = $condition['column'];
			} elseif (is_array($condition['value'])) {
				$in = [];
				foreach ($condition['value'] as $v) {
					$this->placeholder[] = $v;
					$in[] = '?';
				}
				$list[] = sprintf('%s %s (%s)', $condition['column'], $condition['comparison'], implode(', ', $in)); 
			} else {
				$this->placeholder[] = $condition['value'];
				$list[] = sprintf('%s %s ?', $condition['column'], $condition['comparison']); 
			}
		}
		return sprintf('(%s)', implode(' ', $list));
	}
}

