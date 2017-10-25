<?php

/**
 * 文字列操作に関するクラス
 */

namespace System\Util;


use System\Exception\SystemException;

class StringOperator
{
	/**
	 * ランダム文字列を生成する
	 *
	 * @return string
	 */
	public static function getRandomName()
	{
		$name   = substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 10);
		$time   = substr(str_replace('.', '', microtime(true)), 0, 10);
		return $time . '_' . $name;
	}

	/**
	 * スネークケースからキャメルケースに変換
	 *
	 * @param string $snake
	 * @return string
	 */
	public static function snakeToCamel($snake)
	{
		$snakeList = explode('_', $snake);
		$camel     = '';
		foreach ($snakeList as $key => $snake) {
			$camel .= (0 === $key) ? $snake : ucfirst($snake);
		}

		return $camel;
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
		if (1 === preg_match('/^.+\/src\/.+$/', $filePath)) {
			$nameSpace = preg_replace('/(^.+\/)src\/(.+$)/', '$1$2', $filePath);
		} else if (1 === preg_match('/^.+\/Eternal\/.+$/', $filePath)) {
			if (1 === preg_match('/^.+\/Test\/.+$/', $filePath)) {
				$nameSpace = preg_replace('/(^.+\/)Eternal\/Test\/Eternal(\/.+$)/', '$1Test\System$2', $filePath);
			} else {
				$nameSpace = preg_replace('/(^.+\/)Eternal(\/.+$)/', '$1System$2', $filePath);
			}
		}

		$nameSpace = str_replace(['.php', PUBLIC_DIR], '', $nameSpace);
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
		$nameSpace = str_replace('\\', '/', $nameSpace) . '.php';

		if (1 === preg_match('/^System\/.+$/', $nameSpace)) {
			$nameSpace = preg_replace('/^System(\/.+$)/', 'Eternal$1', $nameSpace);
			return PUBLIC_DIR . $nameSpace;
		}

		if (1 === preg_match('/^Test\/.+$/', $nameSpace)) {
			$nameSpace = preg_replace('/^Test(\/.+$)/', 'Eternal/Test$1', $nameSpace);
			$nameSpace = preg_replace('/(^.+\/)System(.+$)/', '$1Eternal$2', $nameSpace);
			return PUBLIC_DIR . $nameSpace;
		}

		return SRC_DIR . $nameSpace;
	}

	/**
	 * テーブル名からクラス名に変換する
	 *
	 * @param string $table
	 * @return string
	 */
	public static function tableToClassName($table)
	{
		$table = self::snakeToCamel($table);
		return ucfirst(str_ireplace(getTablePrefix(), '', $table));
	}

	/**
	 * モデルプロパティのDocコメントを返す 
	 *
	 * @param string $docComment
	 * @return string
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
}
