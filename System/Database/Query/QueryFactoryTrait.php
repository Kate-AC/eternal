<?php

/**
 * クエリを生成するトレイト
 */

namespace System\Database\Query;

use System\Database\Collect;
use System\Exception\DatabaseException;
use System\Util\FilePathSearcher;
use System\Util\Kit;
use System\Util\Str;

trait QueryFactoryTrait
{
    /**
     * @var string
     */
    private $dependencyList = [];

    /**
     * エンティティの依存関係を生成
     */
    private function makeDependencyList()
    {
        foreach ($this->tableAsName as $as => $table) {
            if (!isset($this->dependencyList[$as])) {
                $this->dependencyList[$as] = [];
            }
            foreach ($this->formatedJoin as $join) {
                $left  = $join['on'][0]['a']['table'];
                $right = $join['on'][0]['b']['table'];
                //テーブル名が一致して尚且つ依存リストに無いもの
                if ($as === $left && !isset($this->dependencyList[$right])) {
                    $this->dependencyList[$as][] = $right;
                }
                if ($as === $right && !isset($this->dependencyList[$left])) {
                    $this->dependencyList[$as][] = $left;
                }
            }
        }
    }

    /**
     * エンティティの依存関係を考慮して再帰的に結合する
     *
     * @param string[] $dependencyList
     * @param object[] $entityList
     * @param object[] $createdList
     * @return object[]
     */
    private function union($dependencyList, $entityList, $createdList = [])
    {
        $copyDependencyList = $dependencyList;
        foreach ($copyDependencyList as $table => $dependency) {
            if (empty($dependency)) {
                $createdList[$table] = $entityList[$table];
                unset($dependencyList[$table]);
            } else {
                foreach ($dependency as $d) {
                    if (!isset($createdList[$d])) {
                        continue 2;
                    }
                    $class = ucfirst(Str::snakeToCamel($d));
                    $entityList[$table]->$class = $entityList[$d];
                    $createdList[$table] = $entityList[$table];
                    unset($dependencyList[$table]);
                }
            }
        }

        if (!empty($dependencyList)) {
            return $this->union($dependencyList, $entityList, $createdList);
        }

        return $createdList[$this->asSelf];
    }

    /**
     * クエリの結果をオブジェクトにまとめる
     *
     * @param string[] $resultList クエリの結果の配列
     * @param string   $keyName    配列のキーとして取得したいカラム名
     * @return object[]
     */
    private function unite(array $resultList, $keyName = null)
    {
        if (empty($resultList)) {
            return [];
        }

        $this->makeDependencyList();

        $findList = [];
        foreach ($resultList as $result) {
            $array = [];
            //テーブルとカラムの配列を作る
            foreach ($result as $key => $value) {
                $list = explode('___', $key);

                if (!isset($list[1])) {
                    $list[0] = '_collect';
                    $list[1] = $key;
                }

                if ('_collect' !== $list[0] && false !== strpos($list[1], '.')) {
                    $list[1] = ltrim(strstr($list[1], '.'), '.');
                }
                $array[$list[0]][$list[1]] = $value;
            }

            //SELECTしていないエンティティは生成されないので、初期値として生成する
            foreach ($this->tableAsName as $as => $table) {
                if (!isset($array[$as])) {
                    $array[$as] = [];
                }
            }

            $entityList = [];
            //実クラスからエンティティを生成する
            foreach ($array as $table => $propertyList) {
                if ('_collect' !== $table) {
                    $model = $this->container->getByTable($this->tableAsName[$table]);
                    $maked = $model::make($propertyList);
                } else {
                    $maked = new Collect($propertyList);
                }

                //AS句と同じ文字列で変数を取得できるようにする
                foreach ($this->propertyAsName as $as => $list) {
                    if ($table === $list['table'] && '_collect' !== $table) {
                        $setter = Str::columnToSetter($list['column']);
                        if (isset($result[$as]) && method_exists($maked, $setter)) {
                            $maked->$setter(Kit::autoConvert($result[$as]));
                        }
                        $maked->setExtendProperty($list['column'], $as);
                    }
                }
                $entityList[$table] = $maked;
            }

            if (is_null($keyName)) {
                $find = $this->union($this->dependencyList, $entityList);
            } else {
                $find   = $this->union($this->dependencyList, $entityList);
                $getter = Str::columnToGetter($keyName);

                if (!method_exists($find, $getter)) {
                    throw new DatabaseException('fetchAllByKeyを選択しましたが、メソッドが存在しません');
                }

                if (is_null($find->$getter())) {
                    throw new DatabaseException('fetchAllByKeyを選択しましたが、添え字に使用できないnullの値が存在します');
                }
            }

            if (true === array_key_exists('_collect', $entityList)) {
                $find->Collect = $entityList['_collect'];
            }

            if (is_null($keyName)) {
                $findList[] = $find;
            } else {
                $findList[$find->$getter()] = $find;
            }
        }

        return $findList;
    }

    /**
     * 配列の依存関係を考慮して再帰的に結合する
     *
     * @param string[] $dependencyList
     * @param string[] $classList
     * @param string[] $createdList
     * @return string[]
     */
    private function unionArray($dependencyList, $classList, $createdList = [])
    {
        $copyDependencyList = $dependencyList;
        foreach ($copyDependencyList as $table => $dependency) {
            if (empty($dependency)) {
                $createdList[$table] = $classList[$table];
                unset($dependencyList[$table]);
            } else {
                foreach ($dependency as $d) {
                    if (!isset($createdList[$d])) {
                        continue 2;
                    }

                    $myKey = key($classList[$table]);
                    $key   = key($classList[$d]);

                    $classList[$table][$myKey][$key] = $classList[$d][$key];
                    $createdList[$table] = $classList[$table];

                    unset($dependencyList[$table]);
                }
            }
        }

        if (!empty($dependencyList)) {
            return $this->unionArray($dependencyList, $classList, $createdList);
        }

        return $createdList[$this->asSelf];
    }

    /**
     * クエリの結果を配列にまとめる
     *
     * @param string[] $resultList クエリの結果の配列
     * @param string   $keyName    配列のキーとして取得したいカラム名
     * @return string[]
     */
    private function uniteArray(array $resultList, $keyName = null)
    {
        if (empty($resultList)) {
            return [];
        }

        $this->makeDependencyList();

        $findList = [];
        foreach ($resultList as $result) {
            $array = [];
            //テーブルとカラムの配列を作る
            foreach ($result as $key => $value) {
                $list = explode('___', $key);
                if (1 === preg_match('/^POINT\((.+)\ (.+)\)$/', $value, $match)) {
                    $value = [
                        'Point' => [
                            'lng' => $match[1],
                            'lat' => $match[2]
                        ]
                    ];
                } else {
                    if ('_collect' !== $list[0] && false !== strpos($list[1], '.')) {
                        $list[1] = ltrim(strstr($list[1], '.'), '.');
                    }
                }
                $array[$list[0]][$list[1]] = $value;
            }

            //SELECTしていないエンティティは生成されないので、初期値として生成する
            foreach ($this->tableAsName as $as => $table) {
                if (!isset($array[$as])) {
                    $array[$as] = [];
                }
            }

            foreach ($array as $table => $propertyList) {
                $className = ucfirst(Str::snakeToCamel($table));
                //AS句と同じ文字列で変数を取得できるようにする
                foreach ($this->propertyAsName as $as => $list) {
                    if ($table === $list['table']) {
                        $propertyList[$as] = $propertyList[$list['column']];
                    }
                }
                $classList[$table] = [$className => $propertyList];
            }

            if (is_null($keyName)) {
                $find = $this->unionArray($this->dependencyList, $classList);
            } else {
                $find = $this->unionArray($this->dependencyList, $classList);
                if (!array_key_exists($keyName, $find[key($find)])) {
                    throw new DatabaseException('fetchAllAsArrayByKeyを選択しましたが、配列の添え字が存在しません');
                }

                if (is_null($find[key($find)][$keyName])) {
                    throw new DatabaseException('fetchAllAsArrayByKeyを選択しましたが、添え字に使用できないnullの値が存在します');
                }
            }

            if (true === array_key_exists('_collect', $classList)) {
                $find[key($find)]['Collect'] = $classList['_collect'];
            }

            if (is_null($keyName)) {
                $findList[] = $find;
            } else {
                $findList[$find[key($find)][$keyName]] = $find;
            }
        }

        return $findList;
    }
}

