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
     * @parem  string $type
     * @return string
     */
    public function get($type)
    {
        $list = [];
        foreach ($this->$type as $condition) {
            if (!empty($list)) {
                $list[] = $condition['type2'];
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

