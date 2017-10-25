<?php

/**
 * 独自プロトコルを定義する
 */

namespace System\Core\Extend;

class ExtendProtocol
{
	const PROTOCOL = 'extend';

	/**
	 * @var resource
	 */
	public $context;

	/**
	 * @var string
	 */
	private $data;

	/**
	 * @var int
	 */
	private $position;

	/**
	 * @var int
	 */
	private $status;

	/**
	 * @var mixed[]
	 */
	private static $moduleList = [];

	/**
	 * ストリームラッパーを登録して開始する
	 *
	 * @return ExtendProtocol
	 */
	public function start()
	{
		if (!in_array(self::PROTOCOL, stream_get_wrappers())) {
			stream_wrapper_register(self::PROTOCOL, __CLASS__);
		}
		return $this;
	}

	/**
	 * ストリームラッパーを解除する
	 */
	public function end()
	{
		if (in_array(self::PROTOCOL, stream_get_wrappers())) {
			stream_wrapper_unregister(self::PROTOCOL);
		}
	}

	/**
	 * ファイルの情報を返す(implement)
	 */
	public function stream_stat()
	{
		return $this->status;
	}

	/**
	 * ストリームから読み込む(implement)
	 *
	 * @param int $count
	 * @return int
	 */
	public function stream_read($count)
	{
		$read = substr($this->data, $this->position, $count);
		$this->position += strlen($read);
		return $read;
	}

	/**
	 * ファイルポインタが終端にあるかどうかを調べる(implement)
	 *
	 * @return bool
	 */
	public function stream_eof()
	{
		return $this->position >= strlen($this->data);
	}

	/**
	 * ストリーム開始時に呼ばれる
	 *
	 * @param string $path
	 * @return bool
	 */
	public function stream_open($path, $mode = 'r+', $options = STREAM_REPORT_ERRORS)
	{
		$path = str_replace(self::PROTOCOL . '://', '', $path);

		$this->position = 0;
		$this->status   = stat($path);
		$this->data     = file_get_contents($path);

		//モジュールを実行
		foreach (self::$moduleList as $module) {
			$this->data = $module->run($path, $this->data);
		}

		return true;
	}

	/**
	 * モジュールをセットする
	 *
	 * @param object
	 * @return ExtendProtocol
	 */
	public function setModule($module)
	{
		self::$moduleList[$module::getName()] = $module;
		return $this;
	}
}
