<?php

/**
 * Userモデルのスケルトン
 */

namespace App\Model;

use System\Database\Model;

class UserSkeleton extends Model
{
    /**
     * @model int
     */
    protected $id;

    /**
     * @model int
     */
    protected $type_id;

    /**
     * @model string
     */
    protected $name;

    /**
     * @model \DateTime
     */
    protected $updated_at;

    /**
     * @param mixed[] $properties
     * @return User
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

        if (isset($properties['type_id'])) {
            if (is_numeric($properties['type_id'])) {
                $properties['type_id'] = intval($properties['type_id']);
            }
        } else {
            $properties['type_id'] = null;
        }

        if (isset($properties['name'])) {
            $properties['name'] = strval($properties['name']);
        } else {
            $properties['name'] = null;
        }

        if (isset($properties['updated_at']) && '' !== $properties['updated_at']) {
            if (!($properties['updated_at'] instanceof \DateTime)) {
                $properties['updated_at'] = new \DateTime($properties['updated_at']);
            }
        } else {
            $properties['updated_at'] = new \DateTime();
        }

        $instance = new static();
        return $instance($properties);
    }

    /**
     * @return string
     */
    public static function getTableName()
    {
        return 'user';
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
     * @return int
     */
    public function getTypeId()
    {
        return $this->type_id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param int $id
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param int $typeId
     * @return User
     */
    public function setTypeId($typeId)
    {
        $this->type_id = $typeId;
        return $this;
    }

    /**
     * @param string $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param \DateTime $updatedAt
     * @return User
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
        return $this;
    }
}