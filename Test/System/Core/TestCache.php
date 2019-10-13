<?php

/**
 * Cacheのテスト
 */

namespace Test\System\Core;

use Test\Dummy;
use Test\Mock;
use Test\Parse;
use Test\TestHelper;
use System\Core\Cache;
use System\Exception\SystemException;

class TestCache extends TestHelper
{
    /**
     * __construct
     */
    public function __constructTest()
    {
        $this->compareInstance('System\Core\Cache', new Cache(Cache::NO_CACHE), 'キャッシュを使用しない場合');
        $cache = Mock::m('System\Core\Cache');

        $mock = Mock::m()
            ->_setMethod('connect')
            ->_setArgs(MEMCACHE_HOST, MEMCACHE_PORT)
            ->_setReturn(true)
            ->e();

        $cache->_setMethod('getInstance')
            ->_setArgs(Cache::MEMCACHE)
            ->_setReturn($mock)
            ->e();

        $cache->_setMethod('getInstance')
            ->_setArgs(Cache::MEMCACHED)
            ->_setReturn($mock)
            ->e();

        $this->compareValue(null, $cache->__construct(Cache::MEMCACHE), 'Memcacheを使用する場合');
        $this->compareValue(null, $cache->__construct(Cache::MEMCACHED), 'Memcachedを使用する場合');

        try {
            $cache->__construct(Cache::MEMCACHE);
            $this->throwError('例外が発生すべき箇所で発生していない');
        } catch (SystemException $e) {
            $this->compareException('Memcacheは存在しません', $e, 'Memcacheが存在しない場合');
        }

        try {
            $cache->__construct(Cache::MEMCACHED);
            $this->throwError('例外が発生すべき箇所で発生していない');
        } catch (SystemException $e) {
            $this->compareException('Memcachedは存在しません', $e, 'Memcachedが存在しない場合');
        }

        $mock = Mock::m()
            ->_setMethod('connect')
            ->_setArgs(MEMCACHE_HOST, MEMCACHE_PORT)
            ->_setReturn(false)
            ->e();

        $cache = Mock::m('System\Core\Cache');
        $cache->_setMethod('getInstance')
            ->_setArgs(Cache::MEMCACHE)
            ->_setReturn($mock)
            ->e();

        $cache->_setMethod('getInstance')
            ->_setArgs(Cache::MEMCACHED)
            ->_setReturn($mock)
            ->e();

        try {
            $cache->__construct(Cache::MEMCACHE);
            $this->throwError('例外が発生すべき箇所で発生していない');
        } catch (SystemException $e) {
            $this->compareException('Memcacheに接続できません', $e, 'Memcacheに接続できない場合');
        }

        try {
            $cache->__construct(Cache::MEMCACHED);
            $this->throwError('例外が発生すべき箇所で発生していない');
        } catch (SystemException $e) {
            $this->compareException('Memcachedに接続できません', $e, 'Memcachedに接続できない場合');
        }

        try {
            $cache = new Cache(-1);
        } catch (SystemException $e) {
                $this->compareException('存在しないキャッシュタイプを指定した', $e, '存在しないキャッシュタイプを指定した場合');
        }
    }
}

namespace System\Core;

$GLOBALS['memcache']  = 0;
$GLOBALS['memcached'] = 0;

/**
 * class_existsのオーバーライド
 */
function class_exists($cacheName, $useAutoLoad)
{
    if ('Memcache' === $cacheName) {
        if (0 === $GLOBALS['memcache']) {
            $GLOBALS['memcache']++;
            return true;
        }

        if (1 === $GLOBALS['memcache']) {
            $GLOBALS['memcache']++;
            return false;
        }

        if (2 === $GLOBALS['memcache']) {
            $GLOBALS['memcache']++;
            return true;
        }
    }

    if ('Memcached' === $cacheName) {
        if (0 === $GLOBALS['memcached']) {
            $GLOBALS['memcached']++;
            return true;
        }

        if (1 === $GLOBALS['memcached']) {
            $GLOBALS['memcached']++;
            return false;
        }

        if (2 === $GLOBALS['memcached']) {
            $GLOBALS['memcached']++;
            return true;
        }
    }
}

