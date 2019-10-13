<?php

/**
 * SELECTクエリ
 */

namespace System\Database\Query;

use System\Util\Kit;

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
    private $asSelf;

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
            $this->asSelf = $asName;
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
                $str->isSubQuery();
                $table  = '_collect';
                $column = $str;
            } elseif (false !== strpos($str, '(')) {
                $table  = '_collect';
                $column = sprintf('(%s)', $str);
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
    $from->isSubQuery();
        $this->from = [
            'as'   => $as,
            'from' => $from
        ];
        return $this;
    }

    /**
     * JOIN
     *
     * @param SelectQuery|string[]|string $join [AS => join先のテーブル] or [join先のテーブル]
     * @param string[]                    $on   [テーブル.カラム, テーブル.カラム]
     * @return SelectQuery
     */
    public function join($join, array $onList)
    {
        if ($join instanceof SelectQuery) {
            $join = $join->getBeforeQuery();
        }

        if (is_array($join)) {
            $tableName = current($join);
            if (false !== strpos($tableName, '\\')) {
                $tableName = $tableName::getTableName();
            }
            $as    = is_int(key($join)) ? $tableName : key($join);
            $table = $tableName;
        } else {
            $table = $join::getTableName();
            $as    = $join::getTableName();
        }
        $this->tableAsName[$as] = $table;

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
     * @param string $column
     * @param string $type
     * @return SelectQuery
     */
    public function orderBy($column, $type)
    {
        $this->orderBy[] = [
            'column' => $column,
            'type'   => $type
        ];
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
        if (is_null($this->asSelf)) {
            $this->tableAsName = array_merge([$this->tableName => $this->tableName], $this->tableAsName);
        }

        if (empty($this->select)) {
            foreach ($this->tableAsName as $useTable) {
                $useModel     = $this->container->getByTable($useTable);
                $propertyList = (new \ReflectionClass($useModel))->getProperties();
                foreach ($propertyList as $property) {
                    if (is_null($type = Kit::getDocCommentByModelProperty($property->getDocComment()))) {
                        continue;
                    }

                    $this->select[] = [
                        'table'  => $useTable,
                        'column' => $property->name,
                        'as'     => null
                    ];
                }
            }
        }

        $columnList = [];
        foreach ($this->select as $i => $select) {
            if ($select['column'] instanceof SelectQuery) {
                $subQuery          = sprintf('(%s)', $select['column']->getBeforeQuery());
                $this->placeholder = array_merge($this->placeholder, $select['column']->getPlaceholder());
                $select['as']      = sprintf('`%s`', $select['column']->getQuery());
                $select['column']  = $subQuery;
            }

            if (!is_null($select['as'])) {
                //元々のAS句を保持しておく
                $this->propertyAsName[$select['as']] = [
                    'table'  => $select['table'],
                    'column' => $select['column']
                ];
            }

            if ('_collect' !== $select['table']) {
                $select['column'] = sprintf('%s.%s', $select['table'], $select['column']);
            }

            if (false === $this->isSubQuery) {
                if (!is_null($select['as'])) {
                    $select['as'] = sprintf('AS %s', $select['as']);
                } else {
                    $select['as'] = sprintf('AS `%s___%s`', $select['table'], $select['column']);
                }
            }

            $columnList[] = sprintf('%s %s', $select['column'], $select['as']);
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
            if (is_null($this->asSelf)) {
                $this->asSelf = $this->tableName;
            }
            return sprintf('%s AS %s', $this->tableName, $this->asSelf);
        }

        $this->asSelf = $this->from['as'];
        $subQuery = $this->from['from']->getBeforeQuery();
        $this->placeholder = array_merge($this->placeholder, $this->from['from']->getPlaceholder());
        return  sprintf('(%s) AS %s', $subQuery, $this->from['as']);
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
            $joinArray = [];
            $joinArray[] = 'LEFT JOIN';
            if ($value['join']['table'] instanceof SelectQuery) {
                $joinArray[] = sprintf('(%s)', $value['join']['table']->getBeforeQuery());
            } else {
                $joinArray[] = $value['join']['table'];
            }

            if (!is_null($value['join']['as'])) {
                $joinArray[] = 'AS ' . $value['join']['as'];
            }
            $joinArray[] = $this->getIndexHintLine($value['join']['table']);

            $array[] = implode($joinArray, ' ');

            $onList = [];
            foreach ($value['on'] as $key => $on) {
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
        $orderByArray = [];
        foreach ($this->orderBy as $i => $array) {
            if (isset($this->propertyAsName[$array['column']])) {
                $column = $this->propertyAsName[$array['column']];
                $array['column'] = sprintf('`%s___%s`', $column['table'], $column['column']);
            }
            $orderByArray[] = sprintf('%s %s', $array['column'], $array['type']);
        }

        return !empty($orderByArray) ? sprintf('ORDER BY %s', implode(', ', $orderByArray)) : null;
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
        $query = sprintf('%s SELECT %s FROM %s %s %s %s %s %s %s %s %s %s',
            $this->getExplainLine(),
            $this->getSelectLine(),
            $this->getFromLine(),
            $this->getIndexHintLine($this->tableName),
            $this->getJoinLine(),
            $this->getWhereLine(),
            $this->getGroupByLine(),
            $this->getHavingLine(),
            $this->getOrderByLine(),
            $this->getLimitLine(),
            $this->getForUpdateLine(),
            $this->getOffsetLine()
        );

        if (USE_DB === DB_POSTGRES) {
            $query = preg_replace('/\`/', '"', $query);
        }

        return $query;
    }
}
