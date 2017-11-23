<?php

/**
 * ビューを加工するモジュール
 */

namespace System\Core\Extend\Module;

class RenderModule extends AbstractModule
{
	/**
	 * @var RenderModule
	 */
	protected static $instance;

	/**
	 * @var string[]
	 */
	private $classList = [];

	/**
	 * ビュー内で使えるクラスをセットする
	 *
	 * @param string $shortName
	 * @param string $namespace
	 */
	public function setClassForView($shortName, $namespace)
	{
		$this->classList[$shortName] = $namespace;
	}

	/**
	 * ビュー内の独自コードをPHPコードに変換する
	 *
	 * @param string $path
	 * @param string $data
	 * @return string
	 */
	public function run($path, $data)
	{
		if (false === strpos($path, TEMPLATE_DIR)) {
			return $data;
		}

		$list = [];
		if (false !== preg_match_all('/\{\{([^\{\}]*)\}\}/', $data, $match)) {
			$data = preg_replace('/(\{\{)[^\{\}]*(\}\})/', '<#_#>', $data);

			foreach ($match[1] as $value) {
				if (false !== preg_match_all('/([a-zA-Z0-9]+)\:\:/u', $value, $classMatch)) {
					$classMatch = array_unique($classMatch[1]);
					foreach ($classMatch as $class) {
						$condition = sprintf('/%s(\:\:)/', $class);
						$replace   = $this->classList[$class] . '$1';
						$value     = preg_replace($condition, $replace, $value);
					}
				}

				$value = preg_replace('/escape\(([^\(\)]*)\)/', 'htmlspecialchars($1, ENT_QUOTES, "UTF-8")', $value);

				//コロンやセミコロンがあるものは条件式なのでechoしない
				if (1 === preg_match('/^.*(\;|\:)\ *$/', $value)) {
					$list[] = sprintf('<?php %s ?>', $value);
				} else {
					$list[] = sprintf('<?php echo %s; ?>', $value);
				}
			}
		}

		foreach ($list as $l) {
			$data = preg_replace('/\<\#\_\#\>/', $l, $data, 1);
		}

		return $data;
	}
}
