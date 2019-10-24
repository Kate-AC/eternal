<?php

/**
 * モデルのベース
 */

namespace System\Database;

use System\Core\Di\Container;
use System\Database\Connection;
use System\Database\Query\Condition;
use System\Database\Query\DeleteQuery;
use System\Database\Query\DirectQuery;
use System\Database\Query\InsertQuery;
use System\Database\Query\SelectQuery;
use System\Database\Query\UpdateQuery;
use System\Exception\SystemException;
use System\Util\Kit;
use System\Util\Str;

class Model
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var string[]
     */
    private $extendProperty = [];

    /**
     * コンストラクタ
     *
     * @param Connection
     * @param Container
     */
    public function __construct(
        Connection $connection = null,
        Container $container = null
    ) {
        $this->connection = $connection;
        $this->container  = $container;
    }

    /**
     * Conditionを取得する
     */
    public function getCondition()
    {
        return new Condition();
    }

    /**
     * make時にプロパティに値をセットする
     *
     * @param string $properties
     * @return object
     */
    public function __invoke($properties)
    {
        foreach ($properties as $key => $value) {
            $this->$key = $value;
        }
        return $this;
    }

    /**
     * プロパティの別名をセットする
     *
     * @param string $original
     * @param string $as
     */
    public function setExtendProperty($original, $as)
    {
        $this->extendProperty[$as] = $original;
    }

    /**
     * インサート時にプロパティに値をセットする
     *
     * @param string $property
     * @param string $value
     */
    final public function setProperty($property, $value)
    {
        $this->$property = $value;
    }

    /**
     * 動的にセットしたプロパティの値を取得
     *
     * @param string   $name
     * @param string[] $arguments
     */
    public function __get($name)
    {
        if (isset($this->extendProperty[$name])) {
            $property = $this->extendProperty[$name];
            return $this->$property;
        }
    }

    /**
     * エンティティのプロパティを配列で取得
     *
     * @return string[]
     */
    public function toArray()
    {
        $array        = [];
        $propertyList = (new \ReflectionClass($this))->getProperties();

        foreach ($propertyList as $property) {
            if (is_null($type = Kit::getDocCommentByModelProperty($property->getDocComment()))) {
                continue;
            }
            $getter = Str::columnToGetter($property->getName());
            $array[$property->getName()] = $this->$getter();
        }

        return $array;
    }

    /**
     * SELECTクエリを返す
     *
     * @return SelectQuery
     */
    public function selectQuery()
    {
        $this->connection->getAuto();
        return new SelectQuery($this->connection, $this->container, get_called_class());
    }

    /**
     * INSERTクエリを返す
     *
     * @return InsertQuery
     */
    public function insertQuery()
    {
        $this->connection->get('master');
        return new InsertQuery($this->connection, $this->container, get_called_class());
    }

    /**
     * UPDATEクエリを返す
     *
     * @return UpdateQuery
     */
    public function updateQuery()
    {
        $this->connection->get('master');
        return new UpdateQuery($this->connection, $this->container, get_called_class());
    }

    /**
     * DELETEクエリを返す
     *
     * @return DeleteQuery
     */
    public function deleteQuery()
    {
        $this->connection->get('master');
        return new DeleteQuery($this->connection, $this->container, get_called_class());
    }

    /**
     * 直接クエリを返す
     *
     * @return DirectQuery
     */
    public function directQuery()
    {
        $this->connection->get('master');
        return new DirectQuery($this->connection, $this->container, get_called_class());
    }
}
