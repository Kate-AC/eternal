<?php

/**
 * Cacheのテスト
 */

namespace Test\System\Core;

use Phantom\Phantom;
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

        $mock = Phantom::m()
            ->setMethod('connect')
            ->setArgs(MEMCACHE_HOST, MEMCACHE_PORT)
            ->setReturn(true)
            ->exec();

        $mock
            ->setMethod('addServer')
            ->setArgs(MEMCACHE_HOST, MEMCACHE_PORT)
            ->setReturn(true)
            ->exec();

        $cache = Phantom::m('System\Core\Cache')
            ->setMethod('getInstance')
            ->setArgs(Cache::MEMCACHE)
            ->setReturn($mock)
            ->exec();

        $cache
            ->setMethod('getInstance')
            ->setArgs(Cache::MEMCACHED)
            ->setReturn($mock)
            ->exec();

        $this->compareValue(null, $cache->__construct(Cache::MEMCACHE), 'Memcacheを使用する場合');
        $this->compareValue(null, $cache->__construct(Cache::MEMCACHED), 'Memcachedを使用する場合');

        $mock = Phantom::m()
            ->setMethod('connect')
            ->setArgs(MEMCACHE_HOST, MEMCACHE_PORT)
            ->setReturn(false)
            ->exec();
        $mock
            ->setMethod('addServer')
            ->setArgs(MEMCACHE_HOST, MEMCACHE_PORT)
            ->setReturn(false)
            ->exec();

        $cache = Phantom::m('System\Core\Cache')
            ->setMethod('getInstance')
            ->setArgs(Cache::MEMCACHE)
            ->setReturn($mock)
            ->exec();
        $cache
            ->setMethod('getInstance')
            ->setArgs(Cache::MEMCACHED)
            ->setReturn($mock)
            ->exec();

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

