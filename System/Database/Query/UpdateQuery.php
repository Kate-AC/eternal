<?php

/**
 * UPDATEクエリ
 */

namespace System\Database\Query;

use System\Exception\DatabaseException;

class UpdateQuery extends BaseQuery
{
    /**
     * @var string[]
     */
    protected $set = [];

    /**
     * SET
     *
     * @param string[] $strings
     */
    public function set(array $args)
    {
        foreach ($args as $key => $arg) {
            $this->set[$key] = $arg;
        }

        return $this;
    }

    /**
     * SET句にして返す
     *
     * @return string
     */
    private function getSetLine()
    {
        if (empty($this->set)) {
            return null;
        }

        $setList = [];
        foreach ($this->set as $key => $arg) {
            if (is_null($arg)) {
                $setList[] = sprintf('%s = NULL', $key);
            } elseif ($arg instanceof \DateTime || $arg instanceof \DateTimeImmutable) {
                $setList[] = sprintf("%s = '%s'", $key, $arg->format('Y-m-d H:i:s'));
            } elseif (is_string($arg)) {
                $arg = str_replace("'", "''", $arg);
                $setList[] = sprintf("%s = '%s'", $key, $arg);
            } else {
                $setList[] = sprintf('%s = %s', $key, $arg);
            }
        }

        return implode(', ', $setList);
    }

    /**
     * クエリを組み立てる
     *
     * @return string
     */
    public function create()
    {
        return sprintf('%s UPDATE %s SET %s %s',
            $this->getExplainLine(),
            $this->tableName,
            $this->getSetLine(),
            $this->getWhereLine()
        );
    }

    /**
     * アップデートする
     */
    public function update()
    {
        if (true !== $this->connection->get('master')->inTransaction()) {
            throw new DatabaseException('UPDATE文を使用する場合はトランザクションを開始して下さい');
        }

        $query    = $this->create();
        $prepared = $this->connection->get('master')->prepare($query);
        $prepared->execute($this->placeholder);
        return $prepared->rowCount();
    }
}
