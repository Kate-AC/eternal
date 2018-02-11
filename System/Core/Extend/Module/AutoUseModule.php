<?php

/**
 * クラスに自動でuseを追加するモジュール
 */

namespace System\Core\Extend\Module;

class AutoUseModule extends AbstractModule
{
	/**
	 * @var RenderModule
	 */
	protected static $instance;

	/**
	 * クラスにuse句を追加する
	 *
	 * @param string $path
	 * @param string $data
	 * @return string
	 */
	public function run($path, $data)
	{
		if (false !== strpos($path, PUBLIC_DIR)) {
			$autoUseList = [
				'use System\\Type\\Primitive\\Boolean;',
				'use System\\Type\\Primitive\\Float;',
				'use System\\Type\\Primitive\\Int;',
				'use System\\Type\\Primitive\\String;'
			];

			$data = preg_replace('/(\nnamespace\ .+\;(\r|\n))/',
				sprintf('$1%s%s', "\n\n", implode("\n", $autoUseList)),
				$data
			);
		}

		return $data;
	}
}
