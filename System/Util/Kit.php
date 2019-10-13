<?php

/**
 * 文字列操作に関するクラス
 */

namespace System\Util;


use System\Exception\SystemException;

class Kit
{
    /**
     * ランダム文字列を生成する
     *
     * @return string
     */
    public static function getRandomName()
    {
        $name = substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 10);
        $time = substr(str_replace('.', '', microtime(true)), 0, 10);
        return $time . '_' . $name;
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

    /**
     * 名前空間からファイルパスに変換する
     *
     * @param string $nameSpace
     * @return string
     */
    public static function nameSpaceToPath($nameSpace)
    {
        $path = str_replace('\\', '/', $nameSpace) . '.php';

        if (0 === strpos($path, str_replace(CURRENT_DIR, '', SYSTEM_DIR))) {
            return CURRENT_DIR . $path;
        }

        if (0 === strpos($path, str_replace(CURRENT_DIR, '', TEST_DIR))) {
            return CURRENT_DIR . $path;
        }

        return SRC_DIR . $path;
    }

    /**
     * モデルプロパティのDocコメントを返す 
     *
     * @param string $docComment
     * @return string|null
     */
    public static function getDocCommentByModelProperty($docComment)
    {
        $rowList = explode("\n", $docComment);
        foreach ($rowList as $r) {
            if (false !== ($hit = strstr($r, '@model'))) {
                preg_match('/@model\ ([\\\\a-zA-Z]+)/', $r, $result);
                return $result[1];
            }
        }
        return null;
    }

    /**
     * 返す値の型を自動で判別する
     *
     * @param  string $value
     * @return mixed
     */
    public static function autoConvert($value)
    {
        if (1 === preg_match('/^-?[0-9]+$/', $value)) {
            return (int)$value;
        }

        if (1 === preg_match('/^-?[0-9]+\.{1}[0-9]+$/', $value)) {
            return (float)$value;
        }

        return $value;
    }
}

