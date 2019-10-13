<?php

/**
 * Postモデルのスケルトン
 */

namespace App\Model;

use System\Database\Model;

class PostSkeleton extends Model
{
    /**
     * @model int
     */
    protected $id;

    /**
     * @model int
     */
    protected $user_id;

    /**
     * @model int
     */
    protected $category_id;

    /**
     * @model string
     */
    protected $image_path;

    /**
     * @model string
     */
    protected $name;

    /**
     * @model \DateTime
     */
    protected $updated_at;

    /**
     * @model \DateTime
     */
    protected $time_at;

    /**
     * @model \DateTime
     */
    protected $date_at;

    /**
     * @param mixed[] $properties
     * @return Post
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

        if (isset($properties['user_id'])) {
            if (is_numeric($properties['user_id'])) {
                $properties['user_id'] = intval($properties['user_id']);
            }
        } else {
            $properties['user_id'] = null;
        }

        if (isset($properties['category_id'])) {
            if (is_numeric($properties['category_id'])) {
                $properties['category_id'] = intval($properties['category_id']);
            }
        } else {
            $properties['category_id'] = null;
        }

        if (isset($properties['image_path'])) {
            $properties['image_path'] = strval($properties['image_path']);
        } else {
            $properties['image_path'] = null;
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

        if (isset($properties['time_at']) && '' !== $properties['time_at']) {
            if (!($properties['time_at'] instanceof \DateTime)) {
                $properties['time_at'] = new \DateTime($properties['time_at']);
            }
        } else {
            $properties['time_at'] = null;
        }

        if (isset($properties['date_at']) && '' !== $properties['date_at']) {
            if (!($properties['date_at'] instanceof \DateTime)) {
                $properties['date_at'] = new \DateTime($properties['date_at']);
            }
        } else {
            $properties['date_at'] = null;
        }

        $instance = new static();
        return $instance($properties);
    }

    /**
     * @return string
     */
    public static function getTableName()
    {
        return 'post';
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
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->category_id;
    }

    /**
     * @return string
     */
    public function getImagePath()
    {
        return $this->image_path;
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
     * @return \DateTime
     */
    public function getTimeAt()
    {
        return $this->time_at;
    }

    /**
     * @return \DateTime
     */
    public function getDateAt()
    {
        return $this->date_at;
    }

    /**
     * @param int $id
     * @return Post
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param int $userId
     * @return Post
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;
        return $this;
    }

    /**
     * @param int $categoryId
     * @return Post
     */
    public function setCategoryId($categoryId)
    {
        $this->category_id = $categoryId;
        return $this;
    }

    /**
     * @param string $imagePath
     * @return Post
     */
    public function setImagePath($imagePath)
    {
        $this->image_path = $imagePath;
        return $this;
    }

    /**
     * @param string $name
     * @return Post
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param \DateTime $updatedAt
     * @return Post
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
        return $this;
    }

    /**
     * @param \DateTime $timeAt
     * @return Post
     */
    public function setTimeAt($timeAt)
    {
        $this->time_at = $timeAt;
        return $this;
    }

    /**
     * @param \DateTime $dateAt
     * @return Post
     */
    public function setDateAt($dateAt)
    {
        $this->date_at = $dateAt;
        return $this;
    }
}