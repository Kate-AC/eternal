<?php

/**
 * Typeモデルのスケルトン
 */

namespace App\Model;

use System\Database\Model;

class TypeSkeleton extends Model
{
    /**
     * @model int
     */
    protected $id;

    /**
     * @model string
     */
    protected $coordinate;

    /**
     * @model \DateTime
     */
    protected $created_at;

    /**
     * @param mixed[] $properties
     * @return Type
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

        if (isset($properties['coordinate'])) {
            $properties['coordinate'] = strval($properties['coordinate']);
        } else {
            $properties['coordinate'] = null;
        }

        if (isset($properties['created_at']) && '' !== $properties['created_at']) {
            if (!($properties['created_at'] instanceof \DateTime)) {
                $properties['created_at'] = new \DateTime($properties['created_at']);
            }
        } else {
            $properties['created_at'] = new \DateTime();
        }

        $instance = new static();
        return $instance($properties);
    }

    /**
     * @return string
     */
    public static function getTableName()
    {
        return 'type';
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
    public function getCoordinate()
    {
        return $this->coordinate;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param int $id
     * @return Type
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $coordinate
     * @return Type
     */
    public function setCoordinate($coordinate)
    {
        $this->coordinate = $coordinate;
        return $this;
    }

    /**
     * @param \DateTime $createdAt
     * @return Type
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
        return $this;
    }
}