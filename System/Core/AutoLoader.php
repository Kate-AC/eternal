<?php

/**
 * オートローダー
 */

namespace System\Core;

use System\Core\Extend\ExtendProtocol;
use System\Exception\SystemException;
use System\Util\FilePathSearcher;
use System\Util\StringOperator;

class AutoLoader
{
	/**
	 * @var FilePathSearcher
	 */
	private $filePathSearcher;

	/**
	 * コンストラクタ
	 *
	 * @param FilePathSearcher $filePathSearcher
	 */
	public function __construct(FilePathSearcher $filePathSearcher)
	{
		$this->filePathSearcher = $filePathSearcher;
		spl_autoload_register([$this, 'autoLoad']);
	}

	/**
	 * 存在しないクラスがコールされた時に呼ばれる
	 * 先にspl_autoload_registerでこのメソッドをコールバックとして登録しなければならない
	 *
	 * @param string $callNameSpace 名前空間
	 */
	public function autoLoad($nameSpace)
	{
		if (empty($allFilePathList = $this->filePathSearcher->getAllFilePathList())) {
			$allFilePathList = $this->filePathSearcher->search();
		}

		$filePath = StringOperator::nameSpaceToPath($nameSpace);
		if (isset($allFilePathList[$filePath])) {
			require_once($filePath);
			return;
		}

		throw new SystemException(sprintf('存在しないクラス(%s)が呼ばれた', $nameSpace));
	}
}