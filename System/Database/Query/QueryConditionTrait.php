<?php

/**
 * where句を使用するトレイト
 */

namespace System\Database\Query;

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
    protected $where = [];

    /**
     * @var string[]
     */
    protected $having = [];

    /**
     * WHEREをセットする
     *
     * @param string     $column
     * @param string     $comparison
     * @param string|int $value
     * @return SelectQuery
     */
    public function where($column, $comparison = null, $value = null)
    {
        $this->setCondition($column, $comparison, $value, 'where', 'AND');
        return $this;
    }

    /**
     * ORをセットする
     *
     * @param string     $column
     * @param string     $comparison
     * @param string|int $value
     * @return SelectQuery
     */
    public function whereOr($column, $comparison = null, $value = null)
    {
        $this->setCondition($column, $comparison, $value, 'where', 'OR');
        return $this;
    }

    /**
     * HAVINGをセットする
     *
     * @param string     $column
     * @param string     $comparison
     * @param string|int $value
     * @return SelectQuery
     */
    public function having($column, $comparison = null, $value = null)
    {
        $this->setCondition($column, $comparison, $value, 'having', 'AND');
        return $this;
    }

    /**
     * ORをセットする
     *
     * @param string     $column
     * @param string     $comparison
     * @param string|int $value
     * @return SelectQuery
     */
    public function havingOr($column, $comparison = null, $value = null)
    {
        $this->setCondition($column, $comparison, $value, 'having', 'OR');
        return $this;
    }

    /**
     * WHERE句をまとめて返す
     *
     * @return string
     */
    public function getWhereLine()
    {
        return $this->getCondition('where');
    }

    /**
     * HAVING句をまとめて返す
     *
     * @return string
     */
    public function getHavingLine()
    {
        return $this->getCondition('having');
    }

    /**
     * WHERE句とHAVING句をセットする
     *
     * @param string     $column
     * @param string     $comparison
     * @param string|int $value
     * @param string     $type
     * @param string     $type2
     */
    private function setCondition($column, $comparison = null, $value = null, $type, $type2)
    {
        if (!($column instanceof Condition) && !is_string($column) && is_null($comparison)) {
            throw new DatabaseException('第2引数がnullの場合は第1引数に文字列型かConditionクラスを渡す必要があります');
        }

        if (!($value instanceof SelectQuery) && is_object($value)) {
            throw new DatabaseException('第3引数に渡せるクラスはSelectQueryのみです');
        }

        $this->$type[] = [
            'type'       => $type,
            'type2'      => $type2,
            'column'     => $column,
            'comparison' => $comparison,
            'value'      => $value
        ];
    }

    private function getCondition($type)
    {
        if (empty($this->$type)) {
            return null;
        }

        $list = [];
        foreach ($this->$type as $condition) {
            if (!empty($list)) {
                $list[] = $condition['type2'];
            }

            if (is_string($condition['column']) && is_null($condition['comparison'])) {
                $list[] = $condition['column'];
            } elseif ($condition['column'] instanceof Condition) {
                $list[] = $condition['column']->get($type);
                $this->placeholder = array_merge($this->placeholder, $condition['column']->getPlaceholder());
            } else {
                if (is_array($condition['column'])) {
                    $column = [];
                    foreach ($condition['column'] as $c) {
                        $column[] = $c;
                    }
                    $condition['column'] = sprintf('(%s)', implode(', ', $column));
                }

                if (is_array($condition['value'])) {
                    $in = [];
                    foreach ($condition['value'] as $v) {
                        $this->placeholder[] = $v;
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
                        $this->placeholder[] = $condition['value'];
                        $list[] = sprintf('%s %s ?', $condition['column'], $condition['comparison']); 
                    }
                }
            }
        }

        return sprintf('%s %s', strtoupper($type), implode(' ', $list));
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
