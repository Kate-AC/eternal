<?php

/**
 * キャッシュクラス
 */

namespace System\Core;

use System\Exception\SystemException;

class Cache
{
    const NO_CACHE  = 0;

    const MEMCACHE  = 1;

    const MEMCACHED = 2;

    /**
     * @var mixed
     */
    private static $cache;

    /**
     * @var mixed[]
     */
    private static $staticCache = [];

    /**
     * @var int
     */
    private $cacheType;

    /**
     * コンストラクタ
     *
     * @param int $cacheType
     * @return Container
     */
    public function __construct($cacheType = SELECT_CACHE_TYPE)
    {
        $this->cacheType = $cacheType;
        switch ($this->cacheType) {
            case self::NO_CACHE:
                break;
            case self::MEMCACHE:
                if (!class_exists('Memcache', false)) {
                    throw new SystemException('Memcacheは存在しません');
                }
                self::$cache = $this->getInstance($this->cacheType);
                if (!self::$cache->connect(MEMCACHE_HOST, MEMCACHE_PORT)) {
                    throw new SystemException('Memcacheに接続できません。ホスト名とポートを確認してください。');
                }
                break;
            case self::MEMCACHED:
                if (!class_exists('Memcached', false)) {
                    throw new SystemException('Memcachedは存在しません');
                }
                self::$cache = $this->getInstance($this->cacheType);
                if (!self::$cache->addServer(MEMCACHE_HOST, MEMCACHE_PORT)) {
                    throw new SystemException('Memcachedに接続できません。ホスト名とポートを確認してください。');
                }
                break;
            default:
                throw new SystemException('存在しないキャッシュタイプを指定した');
                break;
        }
    }

    /**
     * キャッシュタイプからインスタンスを生成する
     *
     * @param int $cacheType
     * @return mixed
     */
    private function getInstance($cacheType)
    {
        switch ($cacheType) {
            case self::MEMCACHE:
                return new \Memcache();
            case self::MEMCACHED:
                return new \Memcached();
        }
    }

    /**
     * キャッシュから取得
     *
     * @param string $key
     * @return mixed
     */
    public function getCache($key)
    {
        switch ($this->cacheType) {
            case self::NO_CACHE:
                if (isset(self::$staticCache[$key])) {
                    $result = self::$staticCache[$key];
                } else {
                    $result = false;
                }
                break;
            case self::MEMCACHE:
            case self::MEMCACHED:
                $result = self::$cache->get($key);
                break;
        }

        return false !== $result ? $result : null;
    }

    /**
     * キャッシュにセット
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $time
     */
    function setCache($key, $value, $time = 604800)
    {
        switch ($this->cacheType) {
            case self::NO_CACHE:
                self::$staticCache[$key] = $value;
                break;
            case self::MEMCACHE:
                self::$cache->set($key, $value, MEMCACHE_COMPRESSED, $time);
                break;
            case self::MEMCACHED:
                self::$cache->set($key, $value, $time);
                break;
        }
    }
}
