<?php

/**
 * インスタンスの保管クラス
 */

namespace System\Core\Di;

use System\Core\AutoLoader;
use System\Core\Cache;

class Container
{
    /**
     * @var AutoLoader
     */
    private $autoLoader;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var DependencySearcher
     */
    private $dependencySearcher;

    /**
     * @var DependencyInjector
     */
    private $dependencyInjector;

    /**
     * コンストラクタ
     *
     * @return Container
     */
    public function __construct(
        AutoLoader $autoLoader,
        Cache $cache,
        DependencySearcher $dependencySearcher,
        DependencyInjector $dependencyInjector
    ) {
        $this->autoLoader         = $autoLoader;
        $this->cache              = $cache;
        $this->dependencySearcher = $dependencySearcher;
        $this->dependencyInjector = $dependencyInjector;
    }

    /**
     * インスタンスを取得する
     *
     * @param string $namespace
     */
    public function get($namespace)
    {
        $result = $this->cache->getCache($namespace);
        if (!is_null($result)) {
            return $result;
        }

        $dependencyDtoList = $this->dependencySearcher->search($namespace);
        $this->dependencyInjector->create($dependencyDtoList);
        return $this->dependencyInjector->getResolvedClass($namespace);
    }

    /**
     * テーブル名からモデルインスタンスを取得する
     *
     * @param string $table
     */
    public function getByTable($table)
    {
        $result = $this->cache->getCache($table);
        if (!is_null($result)) {
            return $result;
        }

        $namespace         = $this->dependencySearcher->searchByTable($table);
        $dependencyDtoList = $this->dependencySearcher->getDependencyDtoList();

        $this->dependencyInjector->create($dependencyDtoList);
        $class = $this->dependencyInjector->getResolvedClass($namespace);
        $this->cache->setCache($table, $class);

        return $class;
    }
}
