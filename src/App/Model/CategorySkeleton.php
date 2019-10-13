<?php

/**
 * Categoryモデルのスケルトン
 */

namespace App\Model;

use System\Database\Model;

class CategorySkeleton extends Model
{
    /**
     * @model int
     */
    protected $id;

    /**
     * @model string
     */
    protected $name;

    /**
     * @param mixed[] $properties
     * @return Category
     */
    public static function make(array $properties)
    {
        if (isset($properties['id'])) {
            if (is_numeric($properties['id'])) {
                $properties['id'] = intval($properties['id']);
            }
        } else {
            $properties['id'] = null;
        }

        if (isset($properties['name'])) {
            $properties['name'] = strval($properties['name']);
        } else {
            $properties['name'] = null;
        }

        $instance = new static();
        return $instance($properties);
    }

    /**
     * @return string
     */
    public static function getTableName()
    {
        return 'category';
    }

    /**
     * @return string[]
     */
    public static function getPrimaryKeys()
    {
        return ['id'];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $id
     * @return Category
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $name
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
}