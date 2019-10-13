<?php

/**
 * 文字列操作に関するクラス
 */

namespace System\Util;


use System\Exception\SystemException;

class Str
{
    /**
     * スネークケースからキャメルケースに変換
     *
     * @param string $snake
     * @return string
     */
    public static function snakeToCamel($snake)
    {
        return lcfirst(strtr(ucwords(strtr($snake, ['_' => ' '])), [' ' => '']));
    }

    /**
     * キャメルケースからスネークケースに変換
     *
     * @param string $camel
     * @return string
     */
    public static function camelToSnake($camel)
    {
        return strtolower(preg_replace('/[A-Z]/', '_\0', lcfirst($camel)));
    }

    /**
     * カラム名からゲッター名に変換
     *
     * @param string $column
     * @return string
     */
    public static function columnToGetter($column)
    {
        $camel = self::snakeToCamel($column);
        return sprintf('get%s', ucfirst($camel));
    }

    /**
     * カラム名からセッター名に変換
     *
     * @param string $column
     * @return string
     */
    public static function columnToSetter($column)
    {
        $camel = self::snakeToCamel($column);
        return sprintf('set%s', ucfirst($camel));
    }

    /**
     * ファイルパスから名前空間に変換する
     *
     * @param string $filePath
     * @return string
     */
    public static function pathToNameSpace($filePath)
    {
        $nameSpace = str_replace(['.php', SRC_DIR], '', $filePath);
        return str_replace('/', '\\', $nameSpace);
    }
}

