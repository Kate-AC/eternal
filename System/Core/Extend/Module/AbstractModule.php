<?php

/**
 * PHP拡張モジュール
 */

namespace System\Core\Extend\Module;

abstract class AbstractModule
{
	/**
	 * 自身のインスタンスを返す
	 *
	 * @return AbstractModule
	 */
	public static function get()
	{
		if (!is_null(static::$instance)) {
			return static::$instance;
		}

		static::$instance = new static();
		return static::$instance;
	}

	/**
	 * 自身の名前空間を返す
	 *
	 * @return string
	 */
	public static function getName()
	{
		return get_called_class();
	}

	/**
	 * データを加工する
	 *
	 * @param string $path
	 * @param string $data
	 * @return string
	 */
	abstract public function run($path, $data);
}
