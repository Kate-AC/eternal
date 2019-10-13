<?php

/**
 * 生のクエリを実行する
 */

namespace System\Database\Query;

use System\Core\Di\Container;
use System\Database\Connection;

class DirectQuery
{
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
    protected $queryArray = [];

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
        $this->connection    = $connection;
        $this->container     = $container;

        return $this;
    }

    /**
     * クエリを組み立てる
     *
     * @return string
     */
    private function create()
    {
        return implode(' ', $this->queryArray);
    }

    /**
     * SQL文を追加する
     *
     * @param string $sql クエリ
     * @return self
     */
    public function sql($sql)
    {
        $this->queryArray[] = $sql;
        return $this;
    }

    /**
     * 実行する
     *
     * @return \stdClass
     */
    public function execute()
    {
        $prepare = $this->connection->get('master')->query($this->create());
        $prepare->setFetchMode(\PDO::FETCH_CLASS, 'stdClass');
        return $prepare->fetchAll();
    }
}
